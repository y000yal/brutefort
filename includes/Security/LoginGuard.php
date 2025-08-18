<?php

namespace BruteFort\Security;


use BruteFort\Services\IpSettingsService;
use BruteFort\Services\LogsService;
use BruteFort\Services\RateLimitService;
use BruteFort\Traits\SecurityTraits;

class LoginGuard {
	use SecurityTraits;

	protected string|RateLimitService $rate_limit_service;
	protected string|IpSettingsService $ip_settings_service;
	protected string|LogsService $logs_service;
	private array $settings, $ips;
	private string $current_ip;

	public function __construct(
		RateLimitService $rate_limit_service = null,
		IpSettingsService $ip_settings_service = null,
		LogsService $logs_service = null
	) {
		$this->rate_limit_service  = $rate_limit_service ?? new RateLimitService();
		$this->ip_settings_service = $ip_settings_service ?? new IpSettingsService();
		$this->logs_service        = $logs_service ?? new LogsService();
		$this->init();
	}

	public function init(): void {
		$this->settings   = $this->rate_limit_service->get_rate_limit_settings();
		$this->ips        = $this->ip_settings_service->get_all_ips();
		$ip = '';
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = wp_unslash($_SERVER['REMOTE_ADDR']);
		} else {
			$ip = gethostbyname(gethostname());
		}
		$this->current_ip = sanitize_text_field($ip);

		add_filter( 'authenticate', [ $this, 'check_before_login' ], 30, 3 );
		add_action( 'wp_login_failed', [ $this, 'log_failed_attempt' ] );
		add_action( 'wp_login', [ $this, 'log_success' ], 10, 2 );

	}

	private function isWhitelisted( string $ip ): bool {
		return $this->ip_settings_service->check_ip_exists( $ip, 'whitelist' );
	}

	public function check_before_login( $user, $username, $password ) {
		if ( $this->isWhitelisted( $this->current_ip ) ) {
			return $user;
		}
		if ( $this->logs_service->is_ip_locked( $this->current_ip, $username ) ) {
			return $this->show_locked_error();
		}

		return $user;
	}

	public function show_locked_error(): \WP_Error {
		$lockout_until = $this->logs_service->get_effective_lockout_until( $this->current_ip );

		if ( ! $lockout_until ) {
			$lockout_until = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		}
		// Convert GMT datetime string to timestamp in WP timezone
		$timestamp = get_date_from_gmt( $lockout_until, 'U' );
		$lockout_formatted = date_i18n( 'F j, Y g:i a', $timestamp );

		$message = $this->settings['bf_custom_error_message'] ?? __( "Too many attempts, Please try again in a while!!", "brutefort" );
		$message = str_replace( '{{locked_out_until}}', $lockout_formatted, $message );

		return new \WP_Error( 'brutefort_locked', $message );
	}


	public function log_failed_attempt( $username ): void {
		$this->logs_service->log_fail_attempt( $this->current_ip, $username );
	}

	public function log_success( $user_login, $user ): void {
		$log            = $this->logs_service->get_log_by_ip( $this->current_ip );
		$total_attempts = (int) $log['attempts'] ?? 0;
		$this->logs_service->log_attempt( [
			'log_data'    => [
				'ip_address'  => $this->current_ip,
				'last_status' => 'success',
				'attempts'    => ++ $total_attempts,
			],
			'log_details' => [
				'username' => $user_login,
				'status'   => 'success',
			]
		] );
	}
}

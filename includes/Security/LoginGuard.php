<?php

namespace BruteFort\Security;


use BruteFort\Services\LogsService;
use BruteFort\Services\RateLimitService;
use BruteFort\Traits\SecurityTraits;

class LoginGuard {
	use SecurityTraits;

	protected string|RateLimitService $rate_limit_service;
	protected string|LogsService $logs_service;
	private array $settings;


	public function __construct() {
		$this->init();
	}

	public function init(): void {
		$this->rate_limit_service = new RateLimitService();
		$this->logs_service       = new LogsService();
		$this->settings           = $this->rate_limit_service->get_rate_limit_settings();
		/** These filters and actions are used to inject our logs */
		add_filter( 'authenticate', [ $this, 'check_before_login' ], 30, 3 );
		add_action( 'wp_login_failed', [ $this, 'log_failed_attempt' ] );
		add_action( 'wp_login', [ $this, 'log_success' ], 10, 2 );
	}

	public function check_before_login( $user, $username, $password ) {
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

		if ( $this->logs_service->is_ip_locked( $ip, $username ) ) {
			return $this->show_locked_error();
		}

		return $user;
	}

	public function show_locked_error(): \WP_Error {
		$enable_lockout = $this->settings['bf_enable_lockout'];
		$logs           = $this->logs_service->get_logs( [
			'status' => 'locked',
			'limit'  => 1,
			'offset' => 0
		] );
		$lockout_until  = $logs[0]['lockout_until'];

		if ( ( $lockout_until ) == null ) {
			$lockout_detail = $this->logs_service->get_lockout_detail( $enable_lockout );
			$lockout_until  = $lockout_detail['lockout_timestamp'];
		}
		$lockout_until = date_i18n( 'F j, Y g:i a', strtotime( $lockout_until ) );

		$message = str_replace( '{{locked_out_until}}', $lockout_until, $this->settings['bf_custom_error_message'] );

		return new \WP_Error( 'brutefort_locked', $message );
	}

	public function log_failed_attempt( $username ): void {
		$this->logs_service->log_fail_attempt( $username );
	}

	public function log_success( $user_login, $user ): void {
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

		$this->logs_service->log_attempt( [
			'log_data'    => [
				'ip_address'  => $ip,
				'last_status' => 'success'
			],
			'log_details' => [
				'username' => $user_login,
				'status'   => 'success',
			]
		] );
	}
}

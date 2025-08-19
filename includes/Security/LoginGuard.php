<?php
/**
 * Login Guard for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Security;

use BruteFort\Services\IpSettingsService;
use BruteFort\Services\LogsService;
use BruteFort\Services\RateLimitService;
use BruteFort\Traits\SecurityTraits;

/**
 * Login Guard class for protecting against brute force attacks.
 *
 * @package BruteFort
 */
class LoginGuard {
	use SecurityTraits;

	/**
	 * Rate limit service instance.
	 *
	 * @var RateLimitService
	 */
	protected string|RateLimitService $rate_limit_service;

	/**
	 * IP settings service instance.
	 *
	 * @var IpSettingsService
	 */
	protected string|IpSettingsService $ip_settings_service;

	/**
	 * Logs service instance.
	 *
	 * @var LogsService
	 */
	protected string|LogsService $logs_service;

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private array $settings;

	/**
	 * IP addresses.
	 *
	 * @var array
	 */
	private array $ips;

	/**
	 * Current user IP address.
	 *
	 * @var string
	 */
	private string $current_ip;

	/**
	 * Constructor for LoginGuard.
	 *
	 * @param RateLimitService|null  $rate_limit_service The rate limit service.
	 * @param IpSettingsService|null $ip_settings_service The IP settings service.
	 * @param LogsService|null       $logs_service The logs service.
	 */
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

	/**
	 * Initialize the login guard.
	 */
	public function init(): void {
		$this->settings   = $this->rate_limit_service->get_rate_limit_settings();
		$this->ips        = $this->ip_settings_service->get_all_ips();
		$ip = '';
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		} else {
			$ip = gethostbyname( gethostname() );
		}
		$this->current_ip = $ip;

		add_filter( 'authenticate', array( $this, 'check_before_login' ), 30, 3 );
		add_action( 'wp_login_failed', array( $this, 'log_failed_attempt' ) );
		add_action( 'wp_login', array( $this, 'log_success' ), 10, 2 );
	}

	/**
	 * Check if an IP is whitelisted.
	 *
	 * @param string $ip The IP address to check.
	 * @return bool True if whitelisted, false otherwise.
	 */
	private function is_whitelisted( string $ip ): bool {
		return $this->ip_settings_service->check_ip_exists( $ip, 'whitelist' );
	}

	/**
	 * Check before login to prevent brute force attacks.
	 *
	 * @param \WP_User|\WP_Error|null $user The user object or error.
	 * @param string                  $username The username.
	 * @param string                  $password The password.
	 * @return \WP_User|\WP_Error|null The user object or error.
	 */
	public function check_before_login( $user, $username, $password ) {
		if ( $this->is_whitelisted( $this->current_ip ) ) {
			return $user;
		}
		if ( $this->logs_service->is_ip_locked( $this->current_ip, $username ) ) {
			return $this->show_locked_error();
		}

		return $user;
	}

	/**
	 * Show locked error message.
	 *
	 * @return \WP_Error The error object.
	 */
	public function show_locked_error(): \WP_Error {
		$lockout_until = $this->logs_service->get_effective_lockout_until( $this->current_ip );

		if ( ! $lockout_until ) {
			$lockout_until = date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		}
		// Convert GMT datetime string to timestamp in WP timezone.
		$timestamp = get_date_from_gmt( $lockout_until, 'U' );
		$lockout_formatted = date_i18n( 'F j, Y g:i a', $timestamp );

		$message = $this->settings['bf_custom_error_message'] ?? __( 'Too many attempts, Please try again in a while!!', 'brutefort' );
		$message = str_replace( '{{locked_out_until}}', $lockout_formatted, $message );

		return new \WP_Error( 'brutefort_locked', $message );
	}


	/**
	 * Log a failed login attempt.
	 *
	 * @param string $username The username.
	 */
	public function log_failed_attempt( $username ): void {
		$this->logs_service->log_fail_attempt( $this->current_ip, $username );
	}

	/**
	 * Log a successful login.
	 *
	 * @param string   $user_login The username.
	 * @param \WP_User $user The user object.
	 */
	public function log_success( $user_login, $user ): void {
		$log            = $this->logs_service->get_log_by_ip( $this->current_ip );
		$total_attempts = (int) $log['attempts'] ?? 0;
		$this->logs_service->log_attempt(
			array(
				'log_data'    => array(
					'ip_address'  => $this->current_ip,
					'last_status' => 'success',
					'attempts'    => ++$total_attempts,
				),
				'log_details' => array(
					'username' => $user_login,
					'status'   => 'success',
				),
			)
		);
	}
}

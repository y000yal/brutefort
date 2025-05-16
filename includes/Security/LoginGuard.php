<?php

namespace BruteFort\Security;

use BruteFort\Logs\LogManager;
use BruteFort\Services\RateLimitService;
use BruteFort\Traits\SecurityTraits;

class LoginGuard {
	use SecurityTraits;

	protected string|RateLimitService $rate_limit_service;
	private static array $settings;


	public function __construct() {
		add_filter( 'authenticate', array( $this, 'brutefort_check_login_attempts' ), 30, 3 );
		add_action( 'wp_login_failed', array( $this, 'brutefort_register_failed_attempt' ) );
		$this->rate_limit_service = new RateLimitService();
	}

	public static function init(): void {
		self::$settings = get_option( 'brutefort_settings', [
			'bf_max_attempts'             => 4,
			'bf_time_window'              => 15,
			'bf_lockout_duration'         => 5,
			'bf_enable_lockout_extension' => true,
			'bf_extend_duration'          => 1,
			'bf_custom_error_message'     => 'Too many attempts. Please try again later.',
		] );

		add_filter( 'authenticate', [ __CLASS__, 'checkLoginAttempt' ], 30, 3 );
		add_action( 'wp_login_failed', [ __CLASS__, 'logFailedAttempt' ] );
		add_action( 'wp_login', [ __CLASS__, 'logSuccess' ], 10, 2 );
	}

	public static function checkLoginAttempt( $user, $username, $password ) {
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

		LogManager::init(); // Ensure initialized
		if ( LogManager::isIPLocked( $ip ) ) {
			return new \WP_Error( 'brutefort_locked', self::$settings['bf_custom_error_message'] );
		}

		return $user;
	}

	public static function logFailedAttempt( $username ): void {
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

		LogManager::init(); // Always initialize

		$failed = LogManager::getFailedAttempts( $ip, self::$settings['bf_time_window'] );
		$failed ++;

		$is_locking = $failed >= self::$settings['bf_max_attempts'];

		LogManager::logAttempt( [
			'ip_address'    => $ip,
			'username'      => $username,
			'status'        => $is_locking ? 'locked' : 'fail',
			'lockout_until' => $is_locking
				? gmdate( 'Y-m-d H:i:s', strtotime( '+' . self::$settings['bf_lockout_duration'] . ' minutes' ) )
				: null,
			'attempts'      => $failed,
		] );
	}

	public static function logSuccess( $user_login, $user ): void {
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

		LogManager::init();

		LogManager::logAttempt( [
			'ip_address' => $ip,
			'username'   => $user_login,
			'user_id'    => $user->ID,
			'status'     => 'success',
		] );
	}
}

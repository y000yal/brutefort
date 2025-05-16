<?php

namespace BruteFort\Security;


use BruteFort\Services\RateLimitService;
use BruteFort\Traits\SecurityTraits;

class LoginGuard {
	use SecurityTraits;

	protected string|RateLimitService $rate_limit_service;
	private static array $settings;


	public function __construct() {
		$this->rate_limit_service = new RateLimitService();
		$this->init();
	}

	public function init(): void {
		self::$settings = $this->rate_limit_service->get_rate_limit_settings();

		add_filter( 'authenticate', [ $this, 'maybeBlockLogin' ], 30, 3 );
		add_action( 'wp_login_failed', [ $this, 'logFailedAttempt' ] );
		add_action( 'wp_login', [ $this, 'logSuccess' ], 10, 2 );
	}

	public function maybeBlockLogin( $user, $username, $password ) {
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

		LogManager::init();

		if ( LogManager::isIPLocked( $ip ) ) {
			$logs = LogManager::getLogs( [
				'status' => 'locked',
				'limit'  => 1,
				'offset' => 0
			] );

			$locked_until = date_i18n( 'F j, Y g:i a', strtotime( $logs[0]['lockout_until'] ?? '' ) );
			$message      = str_replace( '{{locked_out_until}}', $locked_until, self::$settings['bf_custom_error_message'] );

			return new \WP_Error( 'brutefort_locked', $message );
		}

		return $user;
	}


	public function logFailedAttempt( $username ): void {

		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

		LogManager::init(); // Always initialize

		$failed = LogManager::getFailedAttempts( $ip, self::$settings['bf_time_window'] );

		$failed ++;

		$is_locking    = $failed > self::$settings['bf_max_attempts'];
		$lockout_until = null;
		if ( $is_locking ) {
			$total_duration = (int) self::$settings['bf_lockout_duration'] * 60; //this is the initial lockout duration converted to seconds

			if ( ! empty( self::$settings['bf_enable_lockout_extension'] ) && self::$settings['bf_extend_lockout_duration'] > 0 ) {
				$total_duration += (int) self::$settings['bf_extend_lockout_duration'] * 60 * 60; // hours → seconds
			}

			$lockout_timestamp = current_time( 'timestamp' ) + $total_duration;
			$lockout_until     = date_i18n( 'Y-m-d H:i:s', $lockout_timestamp );
		}
		LogManager::logAttempt( [
			'ip_address'    => $ip,
			'username'      => $username,
			'status'        => $is_locking ? 'locked' : 'fail',
			'lockout_until' => $is_locking ? $lockout_until : null,
			'attempts'      => $failed,
		] );

	}

	public function logSuccess( $user_login, $user ): void {
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

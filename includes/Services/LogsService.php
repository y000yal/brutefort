<?php
/**
 * LogsService.php
 *
 * LogsService.php
 *
 * @class    LogsService.php
 * @package  butefort
 * @author   Yoyal Limbu
 * @date     5/16/2025 : 4:45 PM
 */


namespace BruteFort\Services;

use BruteFort\Database\TableList;
use BruteFort\Repositories\LogDetailsRepository;
use BruteFort\Repositories\LogsRepository;
use WP_User;
use wpdb;


class LogsService {
	protected ?wpdb $db = null;
	protected string|LogsRepository $logs_repository = '';
	protected string|LogDetailsRepository $log_details_repository = '';
	protected string|RateLimitService $rate_limit_service;
	private array $settings;
	protected string|IpSettingsService $ip_settings_service;

	public function __construct() {
		global $wpdb;
		$this->db                     = $wpdb;
		$this->logs_repository        = new LogsRepository();
		$this->ip_settings_service     = new IpSettingsService();
		$this->rate_limit_service     = new RateLimitService();
		$this->log_details_repository = new LogDetailsRepository();
		$this->settings               = $this->rate_limit_service->get_rate_limit_settings();
	}

	public function log_attempt( array $data ): void {
		$ip = $data['log_data']['ip_address'] ?? $_SERVER['REMOTE_ADDR'];

		$log_exists = $this->logs_repository->get_log_by_ip( $ip );

		$log_data_defaults = [
			'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
			'last_status' => 'fail', // fail, success, locked
			'attempts'    => 1,
		];

		$logs_entry = wp_parse_args( $data['log_data'], $log_data_defaults );
		if ( ! empty( $log_exists ) ) {
			$log_id = $log_exists['ID'];
			$this->logs_repository->update( $log_id, $logs_entry );
		} else {
			$log    = $this->logs_repository->create( $logs_entry );
			$log_id = $log['ID'];
		}

		$log_details_data_defaults = [
			'log_id'        => $log_id,
			'username'      => null,
			'user_id'       => null,
			'status'        => 'fail',
			'lockout_until' => null,
			'is_extended'   => false,
			'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
		];
		$log_details_entry         = wp_parse_args( $data['log_details'], $log_details_data_defaults );

		$this->log_details_repository->create( $log_details_entry );

	}

	public function get_log_by_ip( $ip ): array {
		return $this->logs_repository->get_log_by_ip( $ip );
	}

	public function is_ip_locked( string $ip, string $username = '' ): bool {
		$now = current_time( 'mysql' );
		$log = $this->logs_repository->get_log_by_ip( $ip );
		if ( empty( $log ) ) {
			return false;
		}
		if ( $this->is_temporarily_locked( $log['ID'], $now ) ) {
			return true;
		}

		if ( $this->has_exceeded_failed_attempts( $ip ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Logs a failed login attempt and handles lockout if conditions are met.
	 *
	 * @param string $ip The IP address of the user.
	 * @param string $username The username of the user.
	 */
	public function log_fail_attempt( $ip, $username ): void {
    	$log = $this->logs_repository->get_log_by_ip( $ip );
    	$total_attempts = isset($log['attempts']) ? (int)$log['attempts'] : 0;

	   if ( $this->ip_settings_service->check_ip_exists( $ip, 'whitelist' ) ) {
        $this->log_attempt( [
            'log_data'    => [
                'ip_address'  => $ip,
                'last_status' => 'fail',
                'attempts'    => $total_attempts + 1,
            ],
            'log_details' => [
                'username' => $username,
                'status'   => 'fail',
            ],
        ] );
        return;
    }
  	$log_id      = isset($log['ID']) ? $log['ID'] : null;
    $last_status = isset($log['last_status']) ? $log['last_status'] : null;
    $isLocked    = ($last_status === 'locked');
    $lockoutEnabled = isset($this->settings['bf_enable_lockout']) ? $this->settings['bf_enable_lockout'] : false;
    $latest_locked_log = (!empty($log_id) && is_numeric($log_id)) ? $this->getLockedData((int)$log_id) : null;


		// Skip if lockout is off and currently locked
		if ( $isLocked && ! $lockoutEnabled ) {
			return;
		}

		// Handle lockout extension
		if ( $isLocked && $lockoutEnabled ) {
			if ( $this->should_extend_lockout( $latest_locked_log ) ) {
				$this->extend_lockout( $ip, $username, $total_attempts, $latest_locked_log );

				return;
			}

			if ( $this->is_still_locked( $latest_locked_log ) ) {
				$this->increment_attempts_only( $log_id, $total_attempts );

				return;
			}
		}

		// If not currently locked, check if lockout should now happen
    	$failedAttempts = $this->get_failed_attempts( $ip, isset($this->settings['bf_time_window']) ? $this->settings['bf_time_window'] : 60 );
    	$isLocking      = $failedAttempts >= (isset($this->settings['bf_max_attempts']) ? $this->settings['bf_max_attempts'] : 5);

		if ( $isLocking && $lockoutEnabled ) {
			$this->handle_new_lockout( $ip, $username, $total_attempts, $log );

			return;
		}

		$this->record_failed_attempt( $ip, $username, $total_attempts );
	}

	private function should_extend_lockout( $latest_locked_log ): bool {
		return (
			$this->settings['bf_enable_lockout_extension']
			&& time() < strtotime( $latest_locked_log->lockout_until )
			&& empty( $latest_locked_log->is_extended )
		);
	}

	private function extend_lockout( string $ip, string $username, int $total_attempts, object $locked_log ): void {
		$baseTime        = strtotime( $locked_log->lockout_until );
		$extensionPeriod = (int) $this->settings['bf_extend_lockout_duration'] * 3600;
		$newUntil        = date_i18n( 'Y-m-d H:i:s', $baseTime + $extensionPeriod );

		self::log_attempt( [
			'log_data'    => [
				'ip_address'  => $ip,
				'last_status' => 'locked',
				'attempts'    => $total_attempts + 1,
			],
			'log_details' => [
				'username'      => $username,
				'is_extended'   => true,
				'lockout_until' => $newUntil,
				'status'        => 'locked',
			],
		] );
	}

	private function is_still_locked( object $lock ): bool {
		return $lock->lockout_until > current_time( 'mysql' );
	}

	private function increment_attempts_only( int $log_id, int $total_attempts ): void {
		$this->logs_repository->update( $log_id, [ 'attempts' => $total_attempts + 1 ] );
	}

	private function handle_new_lockout( string $ip, string $username, int $total_attempts, array $log ): void {
		$lockoutDetail = $this->get_lockout_detail( true, $log );

		self::log_attempt( [
			'log_data'    => [
				'ip_address'  => $ip,
				'last_status' => 'locked',
				'attempts'    => $total_attempts + 1,
			],
			'log_details' => [
				'username'      => $username,
				'is_extended'   => $lockoutDetail['is_extended'],
				'lockout_until' => $lockoutDetail['lockout_timestamp'],
				'status'        => 'locked',
			],
		] );
	}

	private function record_failed_attempt( string $ip, string $username, int $total_attempts ): void {
		self::log_attempt( [
			'log_data'    => [
				'ip_address'  => $ip,
				'last_status' => 'fail',
				'attempts'    => $total_attempts + 1,
			],
			'log_details' => [
				'username' => $username,
				'status'   => 'fail',
			],
		] );
	}

	/**
	 * get_lockout_detail
	 *
	 * @param $enable_lockout
	 * @param array|null $log
	 *
	 * @return array
	 */
	public function get_lockout_detail( $enable_lockout, array $log = null ): array {
		$total_duration = (int) ( $enable_lockout ? $this->settings['bf_lockout_duration'] : $this->settings['bf_time_window'] ) * 60; //this is the initial lockout duration converted to seconds
		$is_extended    = 0;
		if ( $this->settings['bf_enable_lockout_extension'] && $this->settings['bf_extend_lockout_duration'] > 0 ) {
			$is_extended    = 1;
			$total_duration += (int) $this->settings['bf_extend_lockout_duration'] * 60 * 60; // hours → seconds
		}

		$lockout_timestamp = current_time( 'timestamp' ) + $total_duration;

		return [
			'lockout_timestamp' => date_i18n( 'Y-m-d H:i:s', $lockout_timestamp ),
			'is_extended'       => $is_extended
		];
	}

	/**
	 * get_failed_attempts
	 *
	 * @param string $ip
	 * @param int $window_minutes Interval for max attempts for e.g. 60 requests per $window_minutes
	 *
	 * @return int
	 */
	public function get_failed_attempts( string $ip, int $window_minutes ): int {
		$now              = current_time( 'timestamp' );
		$cutoff_timestamp = $now - ( $window_minutes * 60 );
		$since            = date( 'Y-m-d H:i:s', $cutoff_timestamp );
		$log              = $this->logs_repository->get_log_by_ip( $ip );
		if ( ! empty( $log ) ) {
			return (int) $this->log_details_repository->index( [
				[
					'log_id'       => $log['ID'],
					'status'       => 'fail',
					'attempt_time' => [
						'operator' => '>',
						'value'    => $since
					]
				]
			], 'ID', 'DESC', '', '', true );
		}

		return 0;
	}

	public function get_logs_with_details(): array {
		$result = $this->logs_repository->index( [
			[
			]
		], 'ID', 'DESC', 50, '', false );

		return $this->restructure_log_data( $result );

	}

	public function restructure_log_data( $logs ): array {
		$grouped = [];

		foreach ( $logs as $log ) {
			$log_id = $log->log_id;

			if ( ! isset( $grouped[ $log_id ] ) ) {
				// Initialize the base object
				$grouped[ $log_id ] = (object) [
					'ID'          => $log->ID,
					'ip_address'  => $log->ip_address,
					'last_status' => $log->last_status,
					'attempts'    => $log->attempts,
					'created_at'  => $log->created_at,
					'updated_at'  => $log->updated_at,
					'is_whitelisted' => $this->ip_settings_service->check_ip_exists( $log->ip_address, 'whitelist' ),
					'log_details' => [],
				];
			}

			// Prepare the details object
			$details = (object) [
				'log_id'       	 => $log->log_id,
				'log_details_id'        => $log->ID,
				'username'      => $log->username,
				'user_id'       => $log->user_id,
				'status'        => $log->status,
				'is_extended'   => $log->is_extended,
				'lockout_until' => $log->lockout_until,
				'user_agent'    => $log->user_agent,
				'attempt_time'  => $log->attempt_time,
			];

			array_unshift( $grouped[ $log_id ]->log_details, $details );
		}
		// Re-index the array
		return array_values( $grouped );
	}


	public function get_log_details( $id ): array {
		return $this->log_details_repository->index( [
			[
				'log_id' => $id
			]
		], 'ID', 'DESC', 50, '', false );
	}

	/**
	 * getArr
	 *
	 * @param int $log_id
	 *
	 * @return array|object|string|null
	 */
	public function getLockedData( int $log_id ): string|array|null|object {
		$data = $this->log_details_repository->index( [
			[
				'log_id' => $log_id,
				'status' => 'locked',
			]
		], 'ID', 'DESC', 1 );

		return ( empty( $data ) ) ? $data : $data[0];
	}

	private function was_last_login_successful( array $log ): bool {
		return isset( $log['last_status'] ) && ! in_array( $log['last_status'], array( 'locked', 'fail' ) );
	}

	private function is_temporarily_locked( int $log_id, string $now ): bool {
		$lockout = $this->getLockedData( $log_id );

		if ( empty( $lockout ) ) {
			return false;
		}

		$is_locked = $lockout->lockout_until > $now;

		if ( ! $is_locked ) {
			$this->logs_repository->update( $log_id,
				[
					'last_status' => 'unlocked',
				] );

		}

		return $is_locked;
	}

	private function has_exceeded_failed_attempts( string $ip ): bool {
		$fail_window  = (int) ( $this->settings['bf_time_window'] ?? 0 );
		$max_attempts = (int) ( $this->settings['bf_max_attempts'] ?? 0 );

		$recent_fails = self::get_failed_attempts( $ip, $fail_window );

		return $recent_fails >= $max_attempts;
	}

	public function get_effective_lockout_until( string $ip ): ?string {
		$log = $this->get_log_by_ip( $ip );
		if ( empty( $log['ID'] ) ) {
			return null;
		}

		$settings = $this->rate_limit_service->get_rate_limit_settings();
		$now = current_time( 'timestamp' );

		// Fetch the latest log detail
		$latest_logs = $this->log_details_repository->index( [
			[ 'log_id' => $log['ID'] ]
		], 'ID', 'DESC', 1, '', false );

		$latest_log = $latest_logs[0] ?? null;

		// Count failed attempts in time window
		$failed_attempts = $this->get_failed_attempts( $ip, (int) $settings['bf_time_window'] );
		$is_locking = $failed_attempts >= (int) $settings['bf_max_attempts'];

		if ( ! $is_locking ) {
			return null;
		}

		// If latest log was 'locked' and lockout is still active
		if (
			$latest_log
			&& $latest_log->status === 'locked'
			&& ! empty( $latest_log->lockout_until )
		) {
			$lockout_ts = strtotime( $latest_log->lockout_until );

			// Case A: Already extended and still locked
			if (
				! empty( $latest_log->is_extended )
				&& $lockout_ts > $now
			) {
				return $latest_log->lockout_until;
			}

			// Case B: Not yet extended and still within lockout window → extend it
			if (
				$settings['bf_enable_lockout_extension']
				&& empty( $latest_log->is_extended )
				&& $lockout_ts > $now
			) {
				$extension = (int) $settings['bf_extend_lockout_duration'] * 3600;
				$extended_ts = $lockout_ts + $extension;

				return date_i18n( 'Y-m-d H:i:s', $extended_ts );
			}
		}

		// Case C: Fresh lockout (user just crossed limit or expired previous lock)
		$base_lockout_duration = (int) $settings['bf_lockout_duration'] * 60;
		$base_lockout_ts = $now + $base_lockout_duration;

		return date_i18n( 'Y-m-d H:i:s', $base_lockout_ts );
	}

}

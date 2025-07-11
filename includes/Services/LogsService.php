<?php
/**
 * LogManager.php
 *
 * LogManager.php
 *
 * @class    LogManager.php
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

	public function __construct() {
		global $wpdb;
		$this->db                     = $wpdb;
		$this->logs_repository        = new LogsRepository();
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

	public function get_logs( array $args = [] ): array {
		$defaults = [
			'status'     => null,
			'ip_address' => null,
			'limit'      => 50,
			'offset'     => 0,
			'orderby'    => 'created_at',
			'order'      => 'DESC',
		];

		$args   = wp_parse_args( $args, $defaults );
		$where  = [];
		$params = [];

		if ( ! empty( $args['status'] ) ) {
			$where[]  = "status = %s";
			$params[] = $args['status'];
		}

		if ( ! empty( $args['ip_address'] ) ) {
			$where[]  = "ip_address = %s";
			$params[] = $args['ip_address'];
		}

		$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';
		$query     = "
            SELECT * FROM " . $this->brutefort_logs_table . "
            $where_sql
            ORDER BY {$args['orderby']} {$args['order']}
            LIMIT %d OFFSET %d
        ";

		$params[] = (int) $args['limit'];
		$params[] = (int) $args['offset'];

		return $this->db->get_results( $this->db->prepare( $query, ...$params ), ARRAY_A );
	}

	public function clear_logs_by_ip( string $ip ): void {
		$this->db->delete( $this->brutefort_logs_table, [ 'ip_address' => $ip ] );
	}

	public function is_ip_locked( string $ip, string $username = '' ): bool {
		$now = current_time( 'mysql' );
		$log = $this->logs_repository->get_log_by_ip( $ip );

		if ( $this->is_temporarily_locked( $log['ID'], $now ) ) {
			return true;
		}

		if ( $this->has_exceeded_failed_attempts( $ip ) ) {
			return true;
		}

		return false;
	}


	public function log_fail_attempt( $ip, $username ): void {
		$log            = $this->logs_repository->get_log_by_ip( $ip );
		$isLocked       = $log['last_status'] === 'locked';
		$lockoutEnabled = $this->settings['bf_enable_lockout'];

		// Skip logging if currently locked but lockout is disabled
		if ( $isLocked && ! $lockoutEnabled ) {
			return;
		}

		$failedAttempts = self::get_failed_attempts( $ip, $this->settings['bf_time_window'] );
		$isLocking      = $failedAttempts >= $this->settings['bf_max_attempts'] && !$isExtended;

		$lockoutUntil = null;
		$isExtended   = 0;

		if ( $isLocking && $lockoutEnabled) {
			$latest_locked_log = $this->getLockedData($log['ID']);

			$lockoutDetail = $this->get_lockout_detail( $lockoutEnabled, $log );
			$lockoutUntil  = $lockoutDetail['lockout_timestamp'];
			$isExtended    = $lockoutDetail['is_extended'];
		}

		// Increment failed attempts for logging
		$failedAttempts ++;

		self::log_attempt( [
			'log_data'    => [
				'ip_address'  => $ip,
				'last_status' => $isLocking ? 'locked' : 'fail',
				'attempts'    => $failedAttempts,
			],
			'log_details' => [
				'username'      => $username,
				'is_extended'   => $isExtended,
				'lockout_until' => $isLocking ? $lockoutUntil : null,
				'status'        => $isLocking ? 'locked' : 'fail',
			],
		] );
	}


	public function get_lockout_detail( $enable_lockout, array $log = null ): array {
		$log_details    = $this->log_details_repository->index( [ '' ] );
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
		], 'ID', 'DESC', 10, '', false );

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
					'log_details' => [],
				];
			}

			// Prepare the details object
			$details = (object) [
				'log_id'        => $log->log_id,
				'username'      => $log->username,
				'user_id'       => $log->user_id,
				'status'        => $log->status,
				'is_extended'   => $log->is_extended,
				'lockout_until' => $log->lockout_until,
				'user_agent'    => $log->user_agent,
				'attempt_time'  => $log->attempt_time,
			];

			$grouped[ $log_id ]->log_details[] = $details;
		}

		// Re-index the array
		return array_values( $grouped );
	}


	public function get_log_details( $id ): array {
		return $this->log_details_repository->index( [
			[
				'log_id' => $id
			]
		], 'ID', 'DESC', 10, '', false );


	}

	/**
	 * getArr
	 *
	 * @param int $log_id
	 *
	 * @return array|object|string|null
	 */
	public function getLockedData( int $log_id ): string|array|null|object {
		$data =  $this->log_details_repository->index( [
			[
				'log_id' => $log_id,
				'status' => 'locked',
			]
		], 'ID', 'DESC', 1 );
		 return (empty($data)) ? $data : $data[0];
	}

	private function was_last_login_successful( array $log ): bool {
		return isset( $log['last_status'] ) && ! in_array( $log['last_status'], array( 'locked', 'fail' ) );
	}

	private function is_temporarily_locked( int $log_id, string $now ): bool {
		$lockout = $this->getLockedData( $log_id);
		if ( empty( $lockout ) ) {
			return false;
		}
	
		$is_locked = $lockout->lockout_until > $now;

		if ( ! $is_locked ) {
			$is_updated = $this->logs_repository->update( $log_id,
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

}

<?php
/**
 * Logs Service for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Services;

use BruteFort\Database\TableList;
use BruteFort\Repositories\LogDetailsRepository;
use BruteFort\Repositories\LogsRepository;
use WP_User;
use wpdb;

/**
 * Logs Service for managing login attempts and lockouts.
 *
 * @package BruteFort
 */
class LogsService {
	/**
	 * WordPress database instance.
	 *
	 * @var wpdb|null
	 */
	protected ?wpdb $db = null;

	/**
	 * Logs repository instance.
	 *
	 * @var LogsRepository
	 */
	protected LogsRepository $logs_repository;

	/**
	 * Log details repository instance.
	 *
	 * @var LogDetailsRepository
	 */
	protected LogDetailsRepository $log_details_repository;

	/**
	 * Rate limit service instance.
	 *
	 * @var RateLimitService
	 */
	protected string|RateLimitService $rate_limit_service;

	/**
	 * Rate limit settings.
	 *
	 * @var array
	 */
	private array $settings;

	/**
	 * IP settings service instance.
	 *
	 * @var IpSettingsService
	 */
	protected IpSettingsService $ip_settings_service;

	/**
	 * Constructor for LogsService.
	 */
	public function __construct() {
		global $wpdb;
		$this->db                     = $wpdb;
		$this->logs_repository        = new LogsRepository();
		$this->ip_settings_service     = new IpSettingsService();
		$this->rate_limit_service     = new RateLimitService();
		$this->log_details_repository = new LogDetailsRepository();
		$this->settings               = $this->rate_limit_service->get_rate_limit_settings();
	}

	/**
	 * Log an attempt for the given IP.
	 *
	 * @param array $data The log data.
	 */
	public function log_attempt( array $data ): void {
		$ip = $data['log_data']['ip_address'] ?? ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown' );

		$log_exists = $this->logs_repository->get_log_by_ip( $ip );

		$log_data_defaults = array(
			'ip_address'  => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown',
			'last_status' => 'fail', // fail, success, locked.
			'attempts'    => 1,
		);

		$logs_entry = wp_parse_args( $data['log_data'], $log_data_defaults );
		if ( ! empty( $log_exists ) ) {
			$log_id = $log_exists['ID'];
			$this->logs_repository->update( $log_id, $logs_entry );
		} else {
			$log    = $this->logs_repository->create( $logs_entry );
			$log_id = $log['ID'];
		}

		$log_details_data_defaults = array(
			'log_id'        => $log_id,
			'username'      => null,
			'user_id'       => null,
			'status'        => 'fail',
			'lockout_until' => null,
			'is_extended'   => false,
			'user_agent'    => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		);
		$log_details_entry         = wp_parse_args( $data['log_details'], $log_details_data_defaults );

		$this->log_details_repository->create( $log_details_entry );
	}

	/**
	 * Get log by IP address.
	 *
	 * @param string $ip The IP address.
	 * @return array The log data.
	 */
	public function get_log_by_ip( $ip ): array {
		return $this->logs_repository->get_log_by_ip( $ip );
	}

	/**
	 * Check if an IP is currently locked.
	 *
	 * @param string $ip The IP address to check.
	 * @param string $username The username (optional).
	 * @return bool True if locked, false otherwise.
	 */
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
		$total_attempts = isset( $log['attempts'] ) ? (int) $log['attempts'] : 0;

		if ( $this->ip_settings_service->check_ip_exists( $ip, 'whitelist' ) ) {
			$this->log_attempt(
				array(
					'log_data'    => array(
						'ip_address'  => $ip,
						'last_status' => 'fail',
						'attempts'    => $total_attempts + 1,
					),
					'log_details' => array(
						'username' => $username,
						'status'   => 'fail',
					),
				)
			);
			return;
		}
		$log_id      = isset( $log['ID'] ) ? $log['ID'] : null;
		$last_status = isset( $log['last_status'] ) ? $log['last_status'] : null;
		$is_locked    = ( 'locked' === $last_status );
		$lockout_enabled = isset( $this->settings['bf_enable_lockout'] ) ? $this->settings['bf_enable_lockout'] : false;
		$latest_locked_log = ( ! empty( $log_id ) && is_numeric( $log_id ) ) ? $this->get_locked_data( (int) $log_id ) : null;

		// Skip if lockout is off and currently locked.
		if ( $is_locked && ! $lockout_enabled ) {
			return;
		}

		// Handle lockout extension.
		if ( $is_locked && $lockout_enabled ) {
			if ( $this->should_extend_lockout( $latest_locked_log ) ) {
				$this->extend_lockout( $ip, $username, $total_attempts, $latest_locked_log );

				return;
			}

			if ( $this->is_still_locked( $latest_locked_log ) ) {
				$this->increment_attempts_only( $log_id, $total_attempts );

				return;
			}
		}

		// If not currently locked, check if lockout should now happen.
		$failed_attempts = $this->get_failed_attempts( $ip, isset( $this->settings['bf_time_window'] ) ? $this->settings['bf_time_window'] : 60 );
		$is_locking      = $failed_attempts >= ( isset( $this->settings['bf_max_attempts'] ) ? $this->settings['bf_max_attempts'] : 5 );

		if ( $is_locking && $lockout_enabled ) {
			$this->handle_new_lockout( $ip, $username, $total_attempts, $log );

			return;
		}

		$this->record_failed_attempt( $ip, $username, $total_attempts );
	}

	/**
	 * Check if lockout should be extended.
	 *
	 * @param object $latest_locked_log The latest locked log entry.
	 * @return bool True if lockout should be extended.
	 */
	private function should_extend_lockout( $latest_locked_log ): bool {
		return (
			$this->settings['bf_enable_lockout_extension']
			&& time() < strtotime( $latest_locked_log->lockout_until )
			&& empty( $latest_locked_log->is_extended )
		);
	}

	/**
	 * Extend the lockout duration.
	 *
	 * @param string $ip             The IP address.
	 * @param string $username       The username.
	 * @param int    $total_attempts Total attempts count.
	 * @param object $locked_log     The locked log entry.
	 */
	private function extend_lockout( string $ip, string $username, int $total_attempts, object $locked_log ): void {
		$base_time        = strtotime( $locked_log->lockout_until );
		$extension_period = (int) $this->settings['bf_extend_lockout_duration'] * 3600;
		$new_until        = date_i18n( 'Y-m-d H:i:s', $base_time + $extension_period );

		self::log_attempt(
			array(
				'log_data'    => array(
					'ip_address'  => $ip,
					'last_status' => 'locked',
					'attempts'    => $total_attempts + 1,
				),
				'log_details' => array(
					'username'      => $username,
					'is_extended'   => true,
					'lockout_until' => $new_until,
					'status'        => 'locked',
				),
			)
		);
	}

	/**
	 * Check if lockout is still active.
	 *
	 * @param object $lock The lock object.
	 * @return bool True if still locked.
	 */
	private function is_still_locked( object $lock ): bool {
		return $lock->lockout_until > current_time( 'mysql' );
	}

	/**
	 * Increment attempts count only.
	 *
	 * @param int $log_id         The log ID.
	 * @param int $total_attempts Total attempts count.
	 */
	private function increment_attempts_only( int $log_id, int $total_attempts ): void {
		$this->logs_repository->update( $log_id, array( 'attempts' => $total_attempts + 1 ) );
	}

	/**
	 * Handle new lockout creation.
	 *
	 * @param string $ip             The IP address.
	 * @param string $username       The username.
	 * @param int    $total_attempts Total attempts count.
	 * @param array  $log            The log entry.
	 */
	private function handle_new_lockout( string $ip, string $username, int $total_attempts, array $log ): void {
		$lockout_detail = $this->get_lockout_detail( true, $log );

		self::log_attempt(
			array(
				'log_data'    => array(
					'ip_address'  => $ip,
					'last_status' => 'locked',
					'attempts'    => $total_attempts + 1,
				),
				'log_details' => array(
					'username'      => $username,
					'is_extended'   => $lockout_detail['is_extended'],
					'lockout_until' => $lockout_detail['lockout_timestamp'],
					'status'        => 'locked',
				),
			)
		);
	}

	/**
	 * Record a failed login attempt.
	 *
	 * @param string $ip             The IP address.
	 * @param string $username       The username.
	 * @param int    $total_attempts Total attempts count.
	 */
	private function record_failed_attempt( string $ip, string $username, int $total_attempts ): void {
		self::log_attempt(
			array(
				'log_data'    => array(
					'ip_address'  => $ip,
					'last_status' => 'fail',
					'attempts'    => $total_attempts + 1,
				),
				'log_details' => array(
					'username' => $username,
					'status'   => 'fail',
				),
			)
		);
	}

	/**
	 * Get lockout detail information.
	 *
	 * @param bool       $enable_lockout Whether lockout is enabled.
	 * @param array|null $log            The log entry.
	 * @return array Lockout detail information.
	 */
	public function get_lockout_detail( $enable_lockout, array $log = null ): array {
		$total_duration = (int) ( $enable_lockout ? $this->settings['bf_lockout_duration'] : $this->settings['bf_time_window'] ) * 60; // This is the initial lockout duration converted to seconds.
		$is_extended    = 0;
		if ( $this->settings['bf_enable_lockout_extension'] && $this->settings['bf_extend_lockout_duration'] > 0 ) {
			$is_extended    = 1;
			$total_duration += (int) $this->settings['bf_extend_lockout_duration'] * 60 * 60; // Hours → seconds.
		}

		$lockout_timestamp = current_time( 'timestamp' ) + $total_duration;

		return array(
			'lockout_timestamp' => date_i18n( 'Y-m-d H:i:s', $lockout_timestamp ),
			'is_extended'       => $is_extended,
		);
	}

	/**
	 * Get failed attempts count within time window.
	 *
	 * @param string $ip             The IP address.
	 * @param int    $window_minutes Time window in minutes.
	 * @return int Number of failed attempts.
	 */
	public function get_failed_attempts( string $ip, int $window_minutes ): int {
		$now              = current_time( 'timestamp' );
		$cutoff_timestamp = $now - ( $window_minutes * 60 );
		$since            = gmdate( 'Y-m-d H:i:s', $cutoff_timestamp );
		$log              = $this->logs_repository->get_log_by_ip( $ip );
		if ( ! empty( $log ) ) {
			return (int) $this->log_details_repository->index(
				array(
					array(
						'log_id'       => $log['ID'],
						'status'       => 'fail',
						'attempt_time' => array(
							'operator' => '>',
							'value'    => $since,
						),
					),
				),
				'ID',
				'DESC',
				'',
				'',
				true
			);
		}

		return 0;
	}

	/**
	 * Get logs with detailed information.
	 *
	 * @return array Array of logs with details.
	 */
	public function get_logs_with_details(): array {
		$result = $this->logs_repository->index(
			array(
				array(),
			),
			'ID',
			'DESC',
			50,
			'',
			false
		);

		return $this->restructure_log_data( $result );
	}

	/**
	 * Restructure log data for better organization.
	 *
	 * @param array $logs Array of log entries.
	 * @return array Restructured log data.
	 */
	public function restructure_log_data( $logs ): array {
		$grouped = array();

		foreach ( $logs as $log ) {
			$log_id = $log->log_id;

			if ( ! isset( $grouped[ $log_id ] ) ) {
				// Initialize the base object.
				$grouped[ $log_id ] = (object) array(
					'ID'          => $log->ID,
					'ip_address'  => $log->ip_address,
					'last_status' => $log->last_status,
					'attempts'    => $log->attempts,
					'created_at'  => $log->created_at,
					'updated_at'  => $log->updated_at,
					'is_whitelisted' => $this->ip_settings_service->check_ip_exists( $log->ip_address, 'whitelist' ),
					'log_details' => array(),
				);
			}

			// Prepare the details object.
			$details = (object) array(
				'log_id'         => $log->log_id,
				'log_details_id'        => $log->ID,
				'username'      => $log->username,
				'user_id'       => $log->user_id,
				'status'        => $log->status,
				'is_extended'   => $log->is_extended,
				'lockout_until' => $log->lockout_until,
				'user_agent'    => $log->user_agent,
				'attempt_time'  => $log->attempt_time,
			);

			array_unshift( $grouped[ $log_id ]->log_details, $details );
		}
		// Re-index the array.
		return array_values( $grouped );
	}


	/**
	 * Get log details by ID.
	 *
	 * @param int $id The log ID.
	 * @return array Array of log details.
	 */
	public function get_log_details( $id ): array {
		return $this->log_details_repository->index(
			array(
				array(
					'log_id' => $id,
				),
			),
			'ID',
			'DESC',
			50,
			'',
			false
		);
	}

	/**
	 * Get locked data for a specific log ID.
	 *
	 * @param int $log_id The log ID to get locked data for.
	 *
	 * @return array|object|string|null The locked data or null if not found.
	 */
	public function get_locked_data( int $log_id ): string|array|null|object {
		$data = $this->log_details_repository->index(
			array(
				array(
					'log_id' => $log_id,
					'status' => 'locked',
				),
			),
			'ID',
			'DESC',
			1
		);

		return ( empty( $data ) ) ? $data : $data[0];
	}

	/**
	 * Check if last login was successful.
	 *
	 * @param array $log The log entry.
	 * @return bool True if successful.
	 */
	private function was_last_login_successful( array $log ): bool {
		return isset( $log['last_status'] ) && ! in_array( $log['last_status'], array( 'locked', 'fail' ) );
	}

	/**
	 * Check if IP is temporarily locked.
	 *
	 * @param int    $log_id The log ID.
	 * @param string $now    Current timestamp.
	 * @return bool True if temporarily locked.
	 */
	private function is_temporarily_locked( int $log_id, string $now ): bool {
		$lockout = $this->get_locked_data( $log_id );

		if ( empty( $lockout ) ) {
			return false;
		}

		$is_locked = $lockout->lockout_until > $now;

		if ( ! $is_locked ) {
			$this->logs_repository->update(
				$log_id,
				array(
					'last_status' => 'unlocked',
				)
			);

		}

		return $is_locked;
	}

	/**
	 * Check if IP has exceeded failed attempts limit.
	 *
	 * @param string $ip The IP address.
	 * @return bool True if limit exceeded.
	 */
	private function has_exceeded_failed_attempts( string $ip ): bool {
		$fail_window  = (int) ( $this->settings['bf_time_window'] ?? 0 );
		$max_attempts = (int) ( $this->settings['bf_max_attempts'] ?? 0 );

		$recent_fails = self::get_failed_attempts( $ip, $fail_window );

		return $recent_fails >= $max_attempts;
	}

	/**
	 * Get effective lockout end time.
	 *
	 * @param string $ip The IP address.
	 * @return string|null Lockout end time or null if not locked.
	 */
	public function get_effective_lockout_until( string $ip ): ?string {
		$log = $this->get_log_by_ip( $ip );
		if ( empty( $log['ID'] ) ) {
			return null;
		}

		$settings = $this->rate_limit_service->get_rate_limit_settings();
		$now = current_time( 'timestamp' );

		// Fetch the latest log detail.
		$latest_logs = $this->log_details_repository->index(
			array(
				array( 'log_id' => $log['ID'] ),
			),
			'ID',
			'DESC',
			1,
			'',
			false
		);

		$latest_log = $latest_logs[0] ?? null;

		// Count failed attempts in time window.
		$failed_attempts = $this->get_failed_attempts( $ip, (int) $settings['bf_time_window'] );
		$is_locking = $failed_attempts >= (int) $settings['bf_max_attempts'];

		if ( ! $is_locking ) {
			return null;
		}

		// If latest log was 'locked' and lockout is still active.
		if (
			$latest_log
			&& 'locked' === $latest_log->status
			&& ! empty( $latest_log->lockout_until )
		) {
			$lockout_ts = strtotime( $latest_log->lockout_until );

			// Case A: Already extended and still locked.
			if (
				! empty( $latest_log->is_extended )
				&& $lockout_ts > $now
			) {
				return $latest_log->lockout_until;
			}

			// Case B: Not yet extended and still within lockout window → extend it.
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

		// Case C: Fresh lockout (user just crossed limit or expired previous lock).
		$base_lockout_duration = (int) $settings['bf_lockout_duration'] * 60;
		$base_lockout_ts = $now + $base_lockout_duration;

		return date_i18n( 'Y-m-d H:i:s', $base_lockout_ts );
	}
}

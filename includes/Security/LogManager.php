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


namespace BruteFort\Security;

use BruteFort\Database\TableList;
use WP_User;
use wpdb;

class LogManager {
	private static ?wpdb $db = null;
	private static string $table = '';

	public static function init(): void {
		global $wpdb;
		self::$db    = $wpdb;
		self::$table = TableList::brute_fort_logs();
	}

	public static function logAttempt( array $data ): void {
		$defaults = [
			'ip_address'    => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
			'username'      => '',
			'user_id'       => null,
			'status'        => 'fail', // fail, success, locked
			'lockout_until' => null,
			'attempts'      => 1,
			'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
		];

		$entry = wp_parse_args( $data, $defaults );

		self::$db->insert( self::$table, $entry );
	}

	public static function getLogs( array $args = [] ): array {
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
            SELECT * FROM " . self::$table . "
            $where_sql
            ORDER BY {$args['orderby']} {$args['order']}
            LIMIT %d OFFSET %d
        ";

		$params[] = (int) $args['limit'];
		$params[] = (int) $args['offset'];

		return self::$db->get_results( self::$db->prepare( $query, ...$params ), ARRAY_A );
	}

	public static function clearLogsByIP( string $ip ): void {
		self::$db->delete( self::$table, [ 'ip_address' => $ip ] );
	}

	public static function isIPLocked( string $ip ): bool {
		LogManager::init();

		$now            = current_time( 'mysql' );
		$lockout_record = self::$db->get_row( self::$db->prepare(
			"SELECT * FROM " . self::$table . " 
         WHERE ip_address = %s AND status = 'locked' 
         ORDER BY id DESC LIMIT 1",
			$ip
		) );

		if ( $lockout_record && $now && $lockout_record->lockout_until > $now ) {
			return true;
		}
		$settings       = ( new \BruteFort\Services\RateLimitService() )->get_rate_limit_settings();
		$fail_window    = (int) $settings['bf_time_window'];
		$max_attempts   = (int) $settings['bf_max_attempts'];

		$recent_fails = self::getFailedAttempts( $ip, $fail_window );
		if ( $recent_fails >= $max_attempts ) {
			return true;
		}
		return false;
	}


	public static function getFailedAttempts( string $ip, int $window_minutes ): int {
		// Get current timestamp in site timezone
		$now = current_time( 'timestamp' );

		// Subtract window to get cutoff
		$cutoff_timestamp = $now - ( $window_minutes * 60 );

		// Format as MySQL datetime string using site timezone
		$since = date( 'Y-m-d H:i:s', $cutoff_timestamp );

		return (int) self::$db->get_var( self::$db->prepare(
			"SELECT COUNT(*) FROM " . self::$table . "
         WHERE ip_address = %s AND created_at > %s AND status = 'fail'",
			$ip, $since
		) );
	}

}

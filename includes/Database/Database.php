<?php
/**
 * Database management for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Database;

/**
 * Database class for managing plugin tables.
 *
 * @package BruteFort
 */
class Database {

	/**
	 * Retrieves an array of tables used in the BruteFort plugin.
	 *
	 * @return array An associative array with table names as keys and their corresponding table names as values.
	 */
	public static function get_tables(): array {
		return array(
			'brute_fort_log_details' => TableList::brute_fort_log_details(),
			'brute_fort_logs' => TableList::brute_fort_logs(),
		);
	}

	/**
	 * Creates the necessary tables for the BruteFort plugin.
	 *
	 * This function creates all tables.
	 * It also handles the creation of foreign key constraints and indexes.
	 *
	 * @return void
	 */
	public static function create_tables(): void {
		$brute_force_log_table         = TableList::brute_fort_logs();
		$brute_force_log_details_table = TableList::brute_fort_log_details();
		$posts_table                   = TableList::posts_table();
		$posts_meta_table              = TableList::posts_meta_table();
		$users_table                   = TableList::users_table();
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$all_sql[] = "CREATE TABLE IF NOT EXISTS $brute_force_log_table (
					    ID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					    ip_address VARCHAR(45) NOT NULL,
					    last_status ENUM('success', 'fail', 'locked', 'unlocked') DEFAULT 'fail',
					    enable_email_notificaiton INT UNSIGNED DEFAULT 0,
					    attempts INT DEFAULT 1,
					    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					    
					    INDEX idx_ip_address (ip_address),
					    INDEX idx_enable_email_notificaiton (enable_email_notificaiton),
					    INDEX idx_last_status (last_status),
					    INDEX idx_created_at (created_at)
					) $collate
                    ";
		$all_sql[] = "CREATE TABLE IF NOT EXISTS $brute_force_log_details_table (
						ID BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					    log_id BIGINT UNSIGNED,
					    username VARCHAR(60),
					    user_id BIGINT UNSIGNED DEFAULT NULL,
					    status ENUM('success', 'fail', 'locked', 'unlocked') DEFAULT 'fail',
					    is_extended INT UNSIGNED DEFAULT 0,
					    lockout_until DATETIME DEFAULT NULL,
					    user_agent TEXT,
					    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					    
					    INDEX idx_log_id (log_id),
					    INDEX idx_username (username),
					    INDEX idx_user_id (user_id),
					    INDEX idx_status (status),
					    INDEX idx_attempt_time (attempt_time),
					    INDEX idx_lockout_until (lockout_until),
                        CONSTRAINT fk_log_id FOREIGN KEY (log_id) REFERENCES $brute_force_log_table(ID) ON DELETE CASCADE
					) $collate
                    ";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( $all_sql as $sql ) {
			dbDelta( $sql );
		}
	}

	/**
	 * Drops all tables used in the BruteFort plugin.
	 *
	 * This function iterates over the tables obtained from the `get_tables` method
	 * and drops each table using the WordPress `$wpdb` global object. The `DROP TABLE IF EXISTS`
	 * SQL statement is used to ensure that the table is only dropped if it exists.
	 *
	 * @return void
	 */
	public static function drop_tables(): void {
		global $wpdb;
		foreach ( self::get_tables() as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore
		}
	}
}

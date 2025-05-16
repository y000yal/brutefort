<?php

namespace BruteFort\Database;


class Database {

	/**
	 * Retrieves an array of tables used in the URMembership plugin.
	 *
	 * @return array An associative array with table names as keys and their corresponding table names as values.
	 */
	public static function get_tables(): array {
		return array(
			'brute_fort_logs' => TableList::brute_fort_logs(),
		);
	}

	/**
	 * Creates the necessary tables for the URMembership plugin.
	 *
	 * This function creates all tables
	 * It also handles the creation of foreign key constraints and indexes.
	 *
	 * @return void
	 */
	public static function create_tables(): void {
		$brute_for_log_table = TableList::brute_fort_logs();
		$posts_table         = TableList::posts_table();
		$posts_meta_table    = TableList::posts_meta_table();
		$users_table         = TableList::users_table();
		global $wpdb;
		$collate = "";
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$all_sql[] = "CREATE TABLE IF NOT EXISTS $brute_for_log_table (
					    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
					    ip_address VARCHAR(45) NOT NULL,
					    username VARCHAR(60),
					    user_id BIGINT UNSIGNED DEFAULT NULL,
					    attempt_time DATETIME NOT NULL,
					    status ENUM('success', 'fail', 'locked') DEFAULT 'fail',
					    lockout_until DATETIME DEFAULT NULL,
					    attempts INT DEFAULT 1,
					    user_agent TEXT,
					    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
					) $collate
                    ";


		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( $all_sql as $sql ) {
			dbDelta( $sql );
		}
	}

	/**
	 * Drops all tables used in the URMembership plugin.
	 *
	 * This function iterates over the tables obtained from the `get_tables` method
	 * and drops each table using the WordPress `$wpdb` global object. The `DROP TABLE IF EXISTS`
	 * SQL statement is used to ensure that the table is only dropped if it exists.
	 *
	 * @return void
	 */
	public static function drop_tables() {
		global $wpdb;
		foreach ( self::get_tables() as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore
		}
	}
}

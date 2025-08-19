<?php
/**
 * Table List for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Database;

/**
 * Class consisting all tables used throughout the plugin.
 *
 * @package BruteFort
 */
class TableList {

	/**
	 * Main table for logging requests.
	 *
	 * @return string The name of the users table.
	 */
	public static function brute_fort_logs(): string {
		global $wpdb;

		return $wpdb->prefix . 'brute_fort_logs';
	}

	/**
	 * Returns the name of the users table.
	 *
	 * @return string The name of the users table.
	 */
	public static function brute_fort_log_details(): string {
		global $wpdb;

		return $wpdb->prefix . 'brute_fort_log_details';
	}
	/**
	 * Returns the name of the users table.
	 *
	 * @return string The name of the users table.
	 */
	public static function users_table(): string {
		global $wpdb;

		return $wpdb->prefix . 'users';
	}

	/**
	 * Returns the name of the users meta table.
	 *
	 * @return string The name of the users meta table.
	 */
	public static function users_meta_table(): string {
		global $wpdb;

		return $wpdb->prefix . 'usermeta';
	}

	/**
	 * Returns the name of the posts table.
	 *
	 * @return string The name of the posts table.
	 */
	public static function posts_table(): string {
		global $wpdb;

		return $wpdb->posts;
	}

	/**
	 * Returns the name of the posts meta table.
	 *
	 * @return string The name of the posts meta table.
	 */
	public static function posts_meta_table(): string {
		global $wpdb;

		return $wpdb->postmeta;
	}
}

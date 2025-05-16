<?php

namespace BruteFort\Database;


/**
 * Class consisting all tables used throughout the plugin.
 */
class TableList {

	/**
	 * Returns the name of the users table.
	 *
	 * @return string The name of the users table.
	 */
	public static function brute_fort_logs() {
		global $wpdb;

		return $wpdb->prefix . 'brute_fort_logs';
	}


	/**
	 * Returns the name of the users table.
	 *
	 * @return string The name of the users table.
	 */
	public static function users_table() {
		global $wpdb;

		return $wpdb->prefix . 'users';
	}

	/**
	 * Returns the name of the users meta table.
	 *
	 * @return string The name of the users meta table.
	 */
	public static function users_meta_table() {
		global $wpdb;

		return $wpdb->prefix . 'usermeta';
	}

	/**
	 * Returns the name of the posts table.
	 *
	 * @return string The name of the posts table.
	 */
	public static function posts_table() {
		global $wpdb;

		return $wpdb->posts;
	}

	/**
	 * Returns the name of the posts meta table.
	 *
	 * @return string The name of the posts meta table.
	 */
	public static function posts_meta_table() {
		global $wpdb;

		return $wpdb->postmeta;
	}
}

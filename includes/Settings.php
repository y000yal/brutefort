<?php
/**
 * Settings management for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort;

use BruteFort\Routes\Routes;
use BruteFort\Database\Database;

/**
 * Settings class for managing BruteFort plugin settings.
 *
 * @package BruteFort
 */
class Settings {

	/**
	 * Constructor for Settings class.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'plugins_loaded', array( $this, 'include_classes' ) );
		register_deactivation_hook( BF_PLUGIN_FILE, array( $this, 'on_deactivation' ) );
		register_activation_hook( BF_PLUGIN_FILE, array( $this, 'on_activation' ) );
	}

	/**
	 * Register the admin menu for BruteFort.
	 */
	public function register_menu(): void {
		add_menu_page(
			__( 'BruteFort', 'brutefort' ),
			__( 'BruteFort', 'brutefort' ),
			'manage_options',
			'brutefort',
			array( $this, 'render_page' ),
			'dashicons-shield',
			60
		);
	}

	/**
	 * Render the admin page content.
	 */
	public function render_page(): void {
		echo '<div id="brutefort-admin-app"></div>';
	}

	/**
	 * Enqueue admin assets for BruteFort.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_assets( $hook ): void {

		if ( 'toplevel_page_brutefort' !== $hook ) {

			return;
		}

		// Check if webpack dev server is running (hot reload mode).
		// Set BRUTEFORT_HOT_RELOAD constant to true in wp-config.php when running 'npm run hot'.
		$is_hot = defined( 'BRUTEFORT_HOT_RELOAD' ) && BRUTEFORT_HOT_RELOAD && defined( 'WP_DEBUG' ) && WP_DEBUG;

		if ( $is_hot ) {
			// Load from webpack dev server for hot reload.
			$dev_server_url = 'http://localhost:5432';

			wp_enqueue_script(
				'brutefort-admin',
				$dev_server_url . '/admin.js',
				array( 'wp-element', 'wp-api-fetch' ),
				time(), // Use timestamp for cache busting in dev mode.
				true
			);
			// Styles are injected by webpack dev server via style-loader.
		} else {
			// Load from built assets - production mode.
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script(
				'brutefort-admin',
				BF()->plugin_url() . '/assets/build/admin' . $suffix . '.js',
				array( 'wp-element', 'wp-api-fetch' ),
				BF_VERSION,
				true
			);

			wp_enqueue_style(
				'brutefort-admin',
				BF()->plugin_url() . '/assets/css/admin.css',
				array(),
				BF_VERSION
			);
		}

		wp_localize_script(
			'brutefort-admin',
			'BruteFortData',
			array(
				'restUrl' => esc_url_raw( rest_url( 'brutefort/v1/' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}


	/**
	 * Include required classes.
	 */
	public function include_classes(): void {}

	/**
	 * Creates the necessary database tables for the plugin.
	 *
	 * This function calls the `create_tables` method of the `Database` class to create the necessary tables for the plugin.
	 *
	 * @return void
	 */
	public static function on_activation(): void {
		Database::create_tables();
		self::add_admin_ip();
	}

	/**
	 * Add the current admin IP to the whitelist.
	 */
	public static function add_admin_ip(): void {
		// Get current server IP with proper sanitization.
		$server_ip = '';
		if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
			$server_ip = sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) );
		} elseif ( isset( $_SERVER['LOCAL_ADDR'] ) ) {
			$server_ip = sanitize_text_field( wp_unslash( $_SERVER['LOCAL_ADDR'] ) );
		} else {
			$server_ip = gethostbyname( gethostname() );
		}

		// Prepare whitelist entry in the same format as IpSettingsController expects.
		$entry = array(
			'bf_ip_address' => $server_ip,
			'bf_list_type'  => 'whitelist',
			'created_at'    => strtotime( 'now' ),
		);

		// Get existing whitelist.
		$whitelist = get_option( 'bf_whitelisted_ips' );
		$whitelist = $whitelist ? json_decode( $whitelist, true ) : array();

		// Prevent duplicates.
		$exists = false;
		foreach ( $whitelist as $item ) {
			if ( isset( $item['bf_ip_address'] ) && $item['bf_ip_address'] === $server_ip ) {
				$exists = true;
				break;
			}
		}

		if ( ! $exists ) {
			$whitelist[] = $entry;
			update_option( 'bf_whitelisted_ips', json_encode( $whitelist ) );
		}
	}
	/**
	 * Deactivates the plugin by dropping the database tables.
	 *
	 * @return void
	 */
	public static function on_deactivation(): void {
		// Check if complete uninstallation is enabled.
		Database::drop_tables();
	}
}

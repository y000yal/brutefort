<?php // phpcs:ignore

/**
 * Plugin Name: BruteFort
 * Plugin URI: https://brutefort.com/
 * Description: BruteForce Protection for WordPress with IP Restriction, Whitelist & Blacklist Management.
 * Version: 0.0.1
 * Author: Y0000el
 * Author URI: https://yoyallimbu.com.np
 * Text Domain: brutefort
 * Domain Path: /languages/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package BruteFort
 */

use BruteFort\Routes\Routes;
use BruteFort\Settings;
use BruteFort\Security\LoginGuard;

defined( 'ABSPATH' ) || exit;

// Autoload composer.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Plugin main class.
 *
 * @package BruteFort
 */
final class BruteFort {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public string $version = '0.0.1';

	/**
	 * Singleton instance.
	 *
	 * @var BruteFort|null
	 */
	protected static ?BruteFort $_instance = null;

	/**
	 * Get singleton instance.
	 *
	 * @return BruteFort
	 */
	public static function instance(): BruteFort {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor for BruteFort.
	 */
	private function __construct() {
		add_filter( 'doing_it_wrong_trigger_error', array( $this, 'filter_doing_it_wrong' ), 10, 4 );
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
		$this->show_notices();
	}

	/**
	 * Prevent cloning.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, 'Cheating; huh?', '1.0' );
	}

	/**
	 * Prevent unserialization.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, 'Cheating; huh?', '1.0' );
	}

	/**
	 * Define plugin constants.
	 */
	private function define_constants(): void {
		$upload_dir = apply_filters( 'brutef_upload_dir', wp_upload_dir() );

		$this->define( 'BRUTEF_UPLOAD_PATH', $upload_dir['basedir'] . '/brutefort/' );
		$this->define( 'BRUTEF_UPLOAD_URL', $upload_dir['baseurl'] . '/brutefort/' );
		$this->define( 'BRUTEF_DS', DIRECTORY_SEPARATOR );
		$this->define( 'BRUTEF_PLUGIN_FILE', __FILE__ );
		$this->define( 'BRUTEF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'BRUTEF_ASSETS_URL', BRUTEF_PLUGIN_URL . 'assets' );
		$this->define( 'BRUTEF_ABSPATH', __DIR__ . BRUTEF_DS );
		$this->define( 'BRUTEF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'BRUTEF_VERSION', $this->version );
		$this->define( 'BRUTEF_FORM_PATH', BRUTEF_ABSPATH . 'includes' . BRUTEF_DS . 'form' . BRUTEF_DS );
		$this->define( 'BRUTEF_SESSION_CACHE_GROUP', 'ur_session_id' );
		$this->define( 'BRUTEF_PRO_ACTIVE', false );
	}

	/**
	 * Define a constant if not already defined.
	 *
	 * @param string      $name  Constant name.
	 * @param bool|string $value Constant value.
	 */
	private function define( string $name, bool|string $value ): void {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Include required files and initialize classes.
	 *
	 * @return void
	 */
	private function includes(): void {
		// Load admin routes.
		new Routes();

		if ( $this->is_request( 'admin' ) ) {
			new Settings();
		}
		if ( $this->is_request( 'frontend' ) ) {
			new LoginGuard();
		}
	}

	/**
	 * Check if the current request is of a specific type.
	 *
	 * @param string $type The type of request to check.
	 * @return bool True if the request matches the type, false otherwise.
	 */
	private function is_request( string $type ): bool {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ! is_admin() && ! defined( 'DOING_CRON' );
			default:
				return false;
		}
	}

	/**
	 * Initialize WordPress hooks and filters.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_filter( 'plugin_action_links_' . BRUTEF_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Load plugin text domain for internationalization.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain(): void {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'brutefort' );

		unload_textdomain( 'brutefort' );
		load_textdomain( 'brutefort', WP_LANG_DIR . '/brutefort/brutefort-' . $locale . '.mo' );
		// WordPress automatically loads translations for plugins hosted on WordPress.org.
		// load_plugin_textdomain('brutefort', false, plugin_basename(__DIR__) . '/languages');.
	}

	/**
	 * Get the AJAX URL for the plugin.
	 *
	 * @return string The AJAX URL.
	 */
	public function ajax_url(): string {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
	 * Get the plugin URL.
	 *
	 * @return string The plugin URL.
	 */
	public function plugin_url(): string {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string The plugin path.
	 */
	public function plugin_path(): string {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Add action links to the plugin row.
	 *
	 * @param array $actions The existing action links.
	 * @return array The modified action links.
	 */
	public static function plugin_action_links( array $actions ): array {
		$new_actions = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=brute-fort-settings' ) . '">' . esc_html__( 'Settings', 'brutefort' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Add meta links to the plugin row.
	 *
	 * @param array  $plugin_meta The existing plugin meta.
	 * @param string $plugin_file The plugin file name.
	 * @return array The modified plugin meta.
	 */
	public static function plugin_row_meta( array $plugin_meta, string $plugin_file ): array {
		if ( BRUTEF_PLUGIN_BASENAME === $plugin_file ) {
			$new_plugin_meta = array(
				'docs'    => '<a href="' . esc_url( 'https://docs.wpuserregistration.com/' ) . '">' . esc_html__( 'Docs', 'brutefort' ) . '</a>',
				'support' => '<a href="' . esc_url( 'https://wpuserregistration.com/support/' ) . '">' . esc_html__( 'Free support', 'brutefort' ) . '</a>',
			);

			return array_merge( $plugin_meta, $new_plugin_meta );
		}

		return $plugin_meta;
	}

	/**
	 * Filter doing_it_wrong notices for specific functions.
	 *
	 * @param mixed  $trigger   Whether to trigger the error.
	 * @param string $function  The function name.
	 * @param string $message   The error message.
	 * @param string $version   The version.
	 * @return bool Whether to trigger the error.
	 */
	public function filter_doing_it_wrong( mixed $trigger, string $function, string $message, string $version ): bool {
		if ( '_load_textdomain_just_in_time' === $function && ( str_contains( $message, 'brutefort' ) || str_contains( $message, 'brute-fort' ) ) ) {
			return false;
		}
		return $trigger;
	}
	/**
	 * Set up admin notices.
	 *
	 * @return void
	 */
	public function show_notices(): void {
		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
	}
	/**
	 * Display admin notices for IP whitelist warnings.
	 *
	 * @return void
	 */
	public function show_admin_notices(): void {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_ip = '';
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$current_ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		} else {
			$current_ip = gethostbyname( gethostname() );
		}
		$whitelist = get_option( 'brutef_whitelisted_ips' );
		$whitelist = $whitelist ? json_decode( $whitelist, true ) : array();

		$is_whitelisted = false;
		foreach ( $whitelist as $entry ) {
			if ( isset( $entry['brutef_ip_address'] ) && $entry['brutef_ip_address'] === $current_ip ) {
				$is_whitelisted = true;
				break;
			}
		}

		if ( ! $is_whitelisted ) {
			$message = sprintf(
				/* translators: %s: Current IP address */
				esc_html__( 'Your current IP (%s) is not whitelisted. For security, please add it to the whitelist in BruteFort settings.', 'brutefort' ),
				esc_html( $current_ip )
			);
			echo '<div class="notice notice-warning"><p>' . wp_kses_post( $message ) . '</p></div>';
		}
	}
}

// Include helper functions.
require_once plugin_dir_path( __FILE__ ) . 'includes/helpers.php';

// Set global.
$GLOBALS['brutefort'] = brutef_get_instance();

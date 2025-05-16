<?php // phpcs:ignore

/**
 * Plugin Name: BruteFort
 * Plugin URI: https://brutefort.com/
 * Description: BruteForce Protection for WordPress with IP Restriction, Whitelist & Blacklist Management.
 * Version: 1.0.0
 * Author: Y0000el
 * Author URI: https://yoyallimbu.com
 * Text Domain: brutefort
 * Domain Path: /languages/
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

// Plugin main class.
final class BruteFort {

	public string $version = '1.0.0';

	protected static ?BruteFort $_instance = null;

	public static function instance(): BruteFort {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		add_filter( 'doing_it_wrong_trigger_error', [ $this, 'filter_doing_it_wrong' ], 10, 4 );
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheating; huh?', 'brutefort' ), '1.0' );
	}

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheating; huh?', 'brutefort' ), '1.0' );
	}

	private function define_constants(): void {
		$upload_dir = apply_filters( 'bf_upload_dir', wp_upload_dir() );

		$this->define( 'BF_LOG_DIR', $upload_dir['basedir'] . '/ur-logs/' );
		$this->define( 'BF_UPLOAD_PATH', $upload_dir['basedir'] . '/brutefort_uploads/' );
		$this->define( 'BF_UPLOAD_URL', $upload_dir['baseurl'] . '/brutefort_uploads/' );
		$this->define( 'BF_DS', DIRECTORY_SEPARATOR );
		$this->define( 'BF_PLUGIN_FILE', __FILE__ );
		$this->define( 'BF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'BF_ASSETS_URL', BF_PLUGIN_URL . 'assets' );
		$this->define( 'BF_ABSPATH', __DIR__ . BF_DS );
		$this->define( 'BF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'BF_VERSION', $this->version );
		$this->define( 'BF_FORM_PATH', BF_ABSPATH . 'includes' . BF_DS . 'form' . BF_DS );
		$this->define( 'BF_SESSION_CACHE_GROUP', 'ur_session_id' );
		$this->define( 'BF_PRO_ACTIVE', false );
	}

	private function define( string $name, bool|string $value ): void {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	private function includes(): void {
		//load admin routes
		new Routes();

		if ( $this->is_request( 'admin' ) ) {
			new Settings();
		}
		if ( $this->is_request( 'frontend' ) ) {
			new LoginGuard();
		}
	}

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

	private function init_hooks(): void {
		// Use if you have setup logic on init
	}

	public function init(): void {
		do_action( 'before_brutefort_init' );
		$this->load_plugin_textdomain();
		do_action( 'brutefort_init' );
	}

	public function load_plugin_textdomain(): void {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'brutefort' );

		unload_textdomain( 'brutefort' );
		load_textdomain( 'brutefort', WP_LANG_DIR . '/brutefort/brutefort-' . $locale . '.mo' );
		load_plugin_textdomain( 'brutefort', false, plugin_basename( __DIR__ ) . '/languages' );
	}

	public function ajax_url(): string {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	public function plugin_url(): string {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	public function plugin_path(): string {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public static function plugin_action_links( array $actions ): array {
		$new_actions = [
			'settings' => '<a href="' . admin_url( 'admin.php?page=brute-fort-settings' ) . '">' . esc_html__( 'Settings', 'brutefort' ) . '</a>',
		];

		return array_merge( $new_actions, $actions );
	}

	public static function plugin_row_meta( array $plugin_meta, string $plugin_file ): array {
		if ( BF_PLUGIN_BASENAME === $plugin_file ) {
			$new_plugin_meta = [
				'docs'    => '<a href="' . esc_url( 'https://docs.wpuserregistration.com/' ) . '">' . esc_html__( 'Docs', 'brutefort' ) . '</a>',
				'support' => '<a href="' . esc_url( 'https://wpuserregistration.com/support/' ) . '">' . esc_html__( 'Free support', 'brutefort' ) . '</a>',
			];

			return array_merge( $plugin_meta, $new_plugin_meta );
		}

		return $plugin_meta;
	}

	public function filter_doing_it_wrong( mixed $trigger, string $function, string $message, string $version ): bool {
		return ( $function === '_load_textdomain_just_in_time' && str_contains( $message, '<code>brute-fort' ) ) ? false : $trigger;
	}
}

// Initialize plugin.
if ( ! function_exists( 'BF' ) ) {
	function BF(): BruteFort {
		return BruteFort::instance();
	}
}

// Activation/Deactivation handling for Free vs Pro conflict.
function brutefort_free_activated(): void {
	set_transient( 'brutefort_free_activated', true );
}

function brutefort_free_deactivated(): void {
	delete_transient( 'brutefort_free_activated' );
}

function brutefort_free_deactivate(): void {
	if ( get_transient( 'brutefort_pro_activated' ) ) {
		deactivate_plugins( 'brutefort/brutefort.php' );
		do_action( 'brutefort_free_deactivate', 'brutefort/brutefort.php' );
		delete_transient( 'brutefort_pro_activated' );
	}
}

add_action( 'activate_brutefort/brutefort.php', 'brutefort_free_activated' );
add_action( 'deactivate_brutefort/brutefort.php', 'brutefort_free_deactivated' );
add_action( 'admin_init', 'brutefort_free_deactivate' );

// Set global.
$GLOBALS['brutefort'] = BF();

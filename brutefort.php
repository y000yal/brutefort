<?php //phpcs:ignore
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
 * @package UserRegistration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! class_exists( 'BruteFort' ) ) :

	/**
	 * Main UserRegistration Class.
	 *
	 * @class   UserRegistration
	 * @version 1.0.0
	 */
	final class BruteFort {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		public string $version = '1.0.0';

		/**
		 * @var object|null
		 */
		protected static ?object $_instance = null;


		/**
		 * instance
		 *
		 * @return object|null
		 */
		public static function instance(): object|null {
			// If the single instance hasn't been set, set it now.
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheating; huh?', 'brutefort' ), '1.0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Serializing instances of this class is forbidden.
		 *
		 * @since 1.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheating; huh?', 'brutefort' ), '1.0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * UserRegistration Constructor.
		 */
		public function __construct() {
			add_filter( 'doing_it_wrong_trigger_error', array(
				$this,
				'ur_filter_doing_it_wrong_trigger_error'
			), 10, 4 );
			$this->define_constants();
			$this->includes();
			$this->init_hooks();
		}

		/**
		 * Hook into actions and filters.
		 */
		private function init_hooks(): void {
//			register_activation_hook( __FILE__, array( 'BF_Install', 'install' ) );
		}

		/**
		 * Define FT Constants.
		 */
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
			$this->define( 'BF_TEMPLATE_DEBUG_MODE', false );
			$this->define( 'BF_FORM_PATH', BF_ABSPATH . 'includes' . BF_DS . 'form' . BF_DS );
			$this->define( 'BF_SESSION_CACHE_GROUP', 'ur_session_id' );
			$this->define( 'BF_PRO_ACTIVE', false );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param string $name Name.
		 * @param bool|string $value Value.
		 */
		private function define( string $name, bool|string $value ): void {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * What type of request is this?
		 *
		 * @param string $type admin, ajax, cron or frontend.
		 *
		 * @return bool
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
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
			}
		}

		/**
		 * Includes.
		 */
		private function includes(): void {

			if ( $this->is_request( 'admin' ) ) {
				$this->admin_includes();
			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend_includes();
			}

		}

		/**
		 * Include required admin files.
		 *
		 * @return void
		 */
		public function admin_includes(): void {
				new \BruteFort\Admin\Settings();
		}

		/**
		 * Include required frontend files.
		 */
		public function frontend_includes(): void {

		}

		/**
		 * Init BruteForce when WordPress Initialises.
		 */
		public function init(): void {
			// Before init action.
			do_action( 'before_brutefort_init' );

			// Set up localisation.
			$this->load_plugin_textdomain();

			// Init action.
			do_action( 'brutefort_init' );
		}

		/**
		 * Load Localisation files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/brutefort/brutefort-LOCALE.mo
		 *      - WP_LANG_DIR/plugins/brutefort-LOCALE.mo
		 */
		public function load_plugin_textdomain(): void {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'brutefort' );

			unload_textdomain( 'brutefort', true );
			load_textdomain( 'brutefort', WP_LANG_DIR . '/brutefort/brutefort-' . $locale . '.mo' );
			load_plugin_textdomain( 'brutefort', false, plugin_basename( __DIR__ ) . '/languages' );
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url(): string {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path(): string {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}


		/**
		 * Get Ajax URL.
		 *
		 * @return string
		 */
		public function ajax_url(): string {
			return admin_url( 'admin-ajax.php', 'relative' );
		}

		/**
		 * Display action links in the Plugins list table.
		 *
		 * @param array $actions Plugin Action links.
		 *
		 * @return array
		 */
		public static function plugin_action_links( array $actions ): array {
			$new_actions = array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=brute-fort-settings' ) . '" aria-label="' . esc_attr__( 'View BruteFort Settings', 'brutefort' ) . '">' . esc_html__( 'Settings', 'brutefort' ) . '</a>',
			);

			return array_merge( $new_actions, $actions );
		}

		/**
		 * Display row meta in the Plugins list table.
		 *
		 * @param array $plugin_meta Plugin Row Meta.
		 * @param string $plugin_file Plugin Row Meta.
		 *
		 * @return array
		 */
		public static function plugin_row_meta( array $plugin_meta, string $plugin_file ): array {
			if ( BF_PLUGIN_BASENAME === $plugin_file ) {
				$new_plugin_meta = array(
					'docs'    => '<a href="' . esc_url( apply_filters( 'brutefort_docs_url', 'https://docs.wpuserregistration.com/' ) ) . '" area-label="' . esc_attr__( 'View User Registration & Membership documentation', 'brutefort' ) . '">' . esc_html__( 'Docs', 'brutefort' ) . '</a>',
					'support' => '<a href="' . esc_url( apply_filters( 'brutefort_support_url', 'https://wpuserregistration.com/support/' ) ) . '" area-label="' . esc_attr__( 'Visit free customer support', 'brutefort' ) . '">' . __( 'Free support', 'brutefort' ) . '</a>',
				);

				return array_merge( $plugin_meta, $new_plugin_meta );
			}

			return (array) $plugin_meta;
		}

		/**
		 * Filter for _doing_it_wrong() calls.
		 *
		 * @param bool|mixed $trigger Whether to trigger the error for _doing_it_wrong() calls. Default true.
		 * @param string $function_name The function that was called.
		 * @param string $message A message explaining what has been done incorrectly.
		 * @param string $version The version of WordPress where the message was added.
		 *
		 * @return bool
		 * @since 3.3.5.2
		 *
		 */
		public function ur_filter_doing_it_wrong_trigger_error( mixed $trigger, string $function_name, string $message, string $version ): bool {

			$trigger       = (bool) $trigger;
			$function_name = (string) $function_name;
			$message       = (string) $message;

			$is_trigger_for_user_registration = $function_name === '_load_textdomain_just_in_time' && str_contains( $message, '<code>brute-fort' );

			return $is_trigger_for_user_registration ? false : $trigger;
		}

	}

endif;

/**
 * Check to see if BF already defined and resolve conflicts while installing PRO version.
 *
 * @since 2.0.4
 */

if ( ! function_exists( 'BF' ) ) {

	/**
	 * Main instance of BruteForce.
	 *
	 * Returns the main instance of FT to prevent the need to use globals.
	 *
	 * @return object|null
	 * @since  1.0.0
	 */
	function BF(): ?object {
		return BruteFort::instance();
	}
} else {

	if ( ! function_exists( 'brutefort_free_activated' ) ) {
		/**
		 * When user activates free version, set the value that is to be used to handle both Free and Pro activation conflict.
		 */
		function brutefort_free_activated(): void {

			set_transient( 'brutefort_free_activated', true );
		}
	}
	add_action( 'activate_brutefort/brutefort.php', 'brutefort_free_activated' );

	if ( ! function_exists( 'brutefort_free_deactivated' ) ) {
		/**
		 * When user deactivates free version, remove the value that was used to handle both Free and Pro activation conflict.
		 */
		function brutefort_free_deactivated(): void {

			global $brutefort_free_activated, $brutefort_free_deactivated;

			$brutefort_free_activated   = (bool) get_transient( 'brutefort_free_activated' );
			$brutefort_free_deactivated = true;

			delete_transient( 'brutefort_free_activated' );
		}
	}
	add_action( 'deactivate_brutefort/brutefort.php', 'brutefort_free_deactivated' );

	if ( ! function_exists( 'brutefort_free_deactivate' ) ) {
		/**
		 * Deactivate Free version if Pro is already activated.
		 *
		 * @since 1.0.0
		 */
		function brutefort_free_deactivate(): void {
			$plugin = 'brutefort/brutefort.php';
			deactivate_plugins( $plugin );
			do_action( 'brutefort_free_deactivate', $plugin );
			delete_transient( 'brutefort_pro_activated' );
		}
	}

	add_action( 'admin_init', 'brutefort_free_deactivate' );


	// Do not process the plugin code further.
	return;
}
// Global for backwards compatibility.
$GLOBALS['brutefort'] = BF();
<?php

namespace BruteFort;


use BruteFort\Routes\Routes;
use BruteFort\Database\Database;

class Settings
{

	public function __construct()
	{
		add_action('admin_menu', [$this, 'register_menu']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
		add_action('plugins_loaded', [$this, 'include_classes']);
		register_deactivation_hook(BF_PLUGIN_FILE, [$this, 'on_deactivation']);
		register_activation_hook(BF_PLUGIN_FILE, [$this, 'on_activation']);
	}

	public function register_menu(): void
	{
		add_menu_page(
			__('BruteFort', 'brutefort'),
			__('BruteFort', 'brutefort'),
			'manage_options',
			'brutefort',
			[$this, 'render_page'],
			'dashicons-shield',
			60
		);
	}

	public function render_page(): void
	{
		echo '<div id="brutefort-admin-app"></div>';
	}

	public function enqueue_assets($hook): void
	{
		if ($hook !== 'toplevel_page_brutefort') {
			return;
		}

		$is_dev = defined('WP_DEBUG') && WP_DEBUG && defined('SCRIPT_DEBUG') && SCRIPT_DEBUG;

		if ($is_dev) {
			// Load from dev server in development
			wp_enqueue_script(
				'brutefort-admin',
				'http://localhost:8080/admin.js',
				['wp-element', 'wp-api-fetch'],
				BF_VERSION,
				true
			);
		} else {
			// Load from built assets in production
			$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_script(
				'brutefort-admin',
				BF()->plugin_url() . '/assets/build/admin' . $suffix . '.js',
				['wp-element', 'wp-api-fetch'],
				BF_VERSION,
				true
			);

			wp_enqueue_style(
				'brutefort-admin',
				BF()->plugin_url() . '/assets/css/admin.css',
				[],
				BF_VERSION
			);
		}

		wp_localize_script('brutefort-admin', 'BruteFortData', [
			'restUrl' => esc_url_raw(rest_url('brutefort/v1/')),
			'nonce'   => wp_create_nonce('wp_rest'),
		]);
	}

	public function include_classes(): void {}

	/**
	 * Creates the necessary database tables for the plugin.
	 *
	 * This function calls the `create_tables` method of the `Database` class to create the necessary tables for the plugin.
	 *
	 * @return void
	 */
	public static function on_activation(): void
	{
		Database::create_tables();
		self::add_admin_ip();
	}

	public static function add_admin_ip(): void
	{
		// Get current server IP with proper sanitization
		$server_ip = '';
		if (isset($_SERVER['SERVER_ADDR'])) {
			$server_ip = wp_unslash($_SERVER['SERVER_ADDR']);
		} elseif (isset($_SERVER['LOCAL_ADDR'])) {
			$server_ip = wp_unslash($_SERVER['LOCAL_ADDR']);
		} else {
			$server_ip = gethostbyname(gethostname());
		}
		$server_ip = sanitize_text_field($server_ip);

		// Prepare whitelist entry in the same format as IpSettingsController expects
		$entry = [
			'bf_ip_address' => $server_ip,
			'bf_list_type'  => 'whitelist',
			'created_at'	=> strtotime('now')
		];

		// Get existing whitelist
		$whitelist = get_option('bf_whitelisted_ips');
		$whitelist = $whitelist ? json_decode($whitelist, true) : [];

		// Prevent duplicates
		$exists = false;
		foreach ($whitelist as $item) {
			if (isset($item['bf_ip_address']) && $item['bf_ip_address'] === $server_ip) {
				$exists = true;
				break;
			}
		}

		if (!$exists) {
			$whitelist[] = $entry;
			update_option('bf_whitelisted_ips', json_encode($whitelist));
		}
	}
	/**
	 * Deactivates the plugin by dropping the database tables.
	 *
	 * @return void
	 */
	public static function on_deactivation(): void
	{
		//		if ( get_option( 'bf_general_setting_enable_complete_uninstallation' ) ) {
		Database::drop_tables();
		//		}
	}
}

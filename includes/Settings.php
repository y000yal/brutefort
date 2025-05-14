<?php

namespace BruteFort;

use BruteFort\Routes\Routes;

class Settings {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'plugins_loaded', [ $this, 'include_classes' ] );
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'BruteFort', 'brutefort' ),
			__( 'BruteFort', 'brutefort' ),
			'manage_options',
			'brutefort',
			[ $this, 'render_page' ],
			'dashicons-shield',
			60
		);
	}

	public function render_page(): void {
		echo '<div id="brutefort-admin-app"></div>';
	}

	public function enqueue_assets( $hook ): void {
		if ( $hook !== 'toplevel_page_brutefort' ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script(
			'brutefort-admin',
			BF()->plugin_url() . '/assets/js/admin' . $suffix . '.js',
			[ 'wp-element', 'wp-api-fetch' ],
			BF_VERSION,
			true
		);

		wp_enqueue_style(
			'brutefort-admin',
			BF()->plugin_url() . '/assets/css/admin.css',
			[],
			BF_VERSION
		);

		wp_localize_script( 'brutefort-admin', 'BruteFortData', [
//			'restUrl' => esc_url_raw( rest_url( 'brutefort/v1/' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		] );
	}

	public function include_classes(): void {
		new Routes();
	}
}

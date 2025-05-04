<?php

namespace BruteFort\Admin;

class Settings {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );
	}

	public function register_menu(): void {
		add_menu_page(
			'BruteFort',
			'BruteFort',
			'manage_options',
			'brutefort',
			[ $this, 'render_page' ],
			'dashicons-shield-alt',
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


		$suffix = defined( 'SCRIPT_DEBUG' ) ? '' : '.min';

		wp_enqueue_script(
			'brutefort-admin',
			BF()->plugin_url().'/assets/js/admin' . $suffix . '.js',
			[ 'wp-element', 'wp-api-fetch' ],
			BF_VERSION,
			true
		);

		wp_enqueue_style(
			'brutefort-admin',
			BF()->plugin_url().'/assets/css/admin.css',
			[],
			BF_VERSION
		);

		wp_localize_script( 'brutefort-admin-js', 'BruteFortData', [
			'restUrl' => esc_url_raw( rest_url( 'brutefort/v1/' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		] );
	}

	public function register_rest_routes(): void {
		register_rest_route( 'brutefort/v1', '/settings', [
			'methods'             => 'GET',
			'callback'            => function () {
				return get_option( 'brutefort_settings', [] );
			},
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		] );

		register_rest_route( 'brutefort/v1', '/settings', [
			'methods'             => 'POST',
			'callback'            => function ( $request ) {
				update_option( 'brutefort_settings', $request->get_json_params() );

				return [ 'success' => true ];
			},
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		] );
	}
}

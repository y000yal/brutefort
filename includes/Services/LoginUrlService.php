<?php
/**
 * Login URL Service for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Services;

/**
 * LoginUrlService class for handling custom login URL.
 *
 * @package BruteFort
 */
class LoginUrlService {

	/**
	 * Initialize the service.
	 */
	public function init(): void {
		$settings = $this->get_settings();
		if ( empty( $settings['enabled'] ) || empty( $settings['slug'] ) ) {
			return;
		}

		add_action( 'init', array( $this, 'add_rewrite_rule' ) );
		add_filter( 'query_vars', array( $this, 'add_query_var' ) );
		add_action( 'parse_request', array( $this, 'handle_parse_request' ) );
		add_filter( 'site_url', array( $this, 'filter_site_url' ), 10, 4 );
		add_filter( 'network_site_url', array( $this, 'filter_site_url' ), 10, 3 );
		add_filter( 'wp_redirect', array( $this, 'filter_wp_redirect' ), 10, 2 );
		add_action( 'login_init', array( $this, 'handle_redirects' ) );
	}

	/**
	 * Add custom query var.
	 *
	 * @param array $vars Existing query vars.
	 * @return array Modified query vars.
	 */
	public function add_query_var( $vars ): array {
		$vars[] = 'brutef_custom_login';
		return $vars;
	}

	/**
	 * Handle parse request to load login page.
	 *
	 * @param WP $wp The WP object.
	 */
	public function handle_parse_request( $wp ) {
		$settings = $this->get_settings();
		$slug     = $settings['slug'];

		if ( isset( $wp->query_vars['brutef_custom_login'] ) || ( isset( $wp->request ) && $wp->request === $slug ) ) {
			// Define a constant to signal we are on the custom login page.
			if ( ! defined( 'BRUTEF_CUSTOM_LOGIN_PAGE' ) ) {
				define( 'BRUTEF_CUSTOM_LOGIN_PAGE', true );
			}

			// Fix variable scope issues for wp-login.php.
			global $error, $interim_login, $action, $user_login, $user_email, $errors;

			// Require wp-login.php.
			$file = ABSPATH . 'wp-login.php';
			if ( file_exists( $file ) ) {
				require_once $file;
				exit;
			}
		}
	}

	/**
	 * Add rewrite rule for custom login URL.
	 */
	public function add_rewrite_rule(): void {
		$settings = $this->get_settings();
		$slug     = $settings['slug'];

		add_rewrite_rule(
			'^' . $slug . '/?$',
			'index.php?brutef_custom_login=1',
			'top'
		);
	}

	/**
	 * Filter site URL to replace wp-login.php with custom slug.
	 *
	 * @param string      $url    The complete URL.
	 * @param string      $path   The path relative to the site URL.
	 * @param string|null $scheme The scheme to use.
	 * @param int|null    $blog_id The blog ID.
	 * @return string The modified URL.
	 */
	public function filter_site_url( $url, $path, $scheme = null, $blog_id = null ): string {
		return $this->replace_login_url( $url );
	}

	/**
	 * Filter wp_redirect to replace wp-login.php with custom slug.
	 *
	 * @param string $location The redirect location.
	 * @param int    $status   The HTTP status code.
	 * @return string The modified location.
	 */
	public function filter_wp_redirect( $location, $status ): string {
		return $this->replace_login_url( $location );
	}

	/**
	 * Replace wp-login.php with custom slug in a URL.
	 *
	 * @param string $url The URL to modify.
	 * @return string The modified URL.
	 */
	private function replace_login_url( string $url ): string {
		$settings = $this->get_settings();
		$slug     = $settings['slug'];

		if ( false !== strpos( $url, 'wp-login.php' ) ) {
			// Handle query args.
			$parts = explode( '?', $url );
			$base  = str_replace( 'wp-login.php', $slug, $parts[0] );

			if ( isset( $parts[1] ) ) {
				return $base . '?' . $parts[1];
			}
			return $base;
		}
		return $url;
	}

	/**
	 * Handle redirects for wp-login.php and wp-admin.
	 */
	public function handle_redirects(): void {
		// Prevent infinite loops.
		if ( defined( 'BRUTEF_LOGIN_REDIRECT_HANDLED' ) ) {
			return;
		}
		define( 'BRUTEF_LOGIN_REDIRECT_HANDLED', true );

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$settings    = $this->get_settings();
		$slug        = $settings['slug'];

		// If accessing wp-login.php directly, redirect or 404.
		// We check if the request URI contains wp-login.php.
		// The custom slug rewrite will NOT have wp-login.php in the REQUEST_URI.
		if ( false !== strpos( $request_uri, 'wp-login.php' ) && ! isset( $_GET['action'] ) ) {
			// If it's a logout action, let it pass (handled by WP, but URL filtered).
			// Actually, WP handles logout via wp-login.php?action=logout.

			// Check if we should block wp-login.php access.
			// For now, let's redirect to 404 or home to be safe.
			global $wp_query;
			if ( $wp_query ) {
				$wp_query->set_404();
			}
			status_header( 404 );
			// Since we are in login_init, get_template_part(404) might not work as expected if theme is not loaded.
			// Simple die or redirect to home is safer.
			wp_safe_redirect( home_url( '404' ) );
			exit;
		}
	}

	/**
	 * Get Login URL Settings.
	 *
	 * @return array
	 */
	public function get_settings(): array {
		return get_option(
			'brutef_login_url_settings',
			array(
				'enabled' => false,
				'slug'    => 'my-login',
			)
		);
	}

	/**
	 * Update Login URL Settings.
	 *
	 * @param array $settings The new settings.
	 * @return bool True on success.
	 */
	public function update_settings( array $settings ): bool {
		$current = $this->get_settings();
		$new     = wp_parse_args( $settings, $current );

		// Flush rewrite rules if slug or enabled status changes.
		if ( $new['enabled'] !== $current['enabled'] || $new['slug'] !== $current['slug'] ) {
			// Remove old rule manually from global $wp_rewrite before flushing.
			if ( isset( $current['slug'] ) ) {
				global $wp_rewrite;
				$old_rule_key = '^' . $current['slug'] . '/?$';
				if ( isset( $wp_rewrite->extra_rules_top[ $old_rule_key ] ) ) {
					unset( $wp_rewrite->extra_rules_top[ $old_rule_key ] );
				}
			}

			update_option( 'brutef_login_url_settings', $new );
			$this->add_rewrite_rule();
			flush_rewrite_rules();
		} else {
			update_option( 'brutef_login_url_settings', $new );
		}

		return true;
	}
}

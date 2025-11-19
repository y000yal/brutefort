<?php
/**
 * Login URL Routes for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Routes;

use BruteFort\Services\LoginUrlService;

/**
 * LoginUrlRoutes class for managing Custom Login URL settings.
 *
 * @package BruteFort
 */
class LoginUrlRoutes {

	/**
	 * Login URL service instance.
	 *
	 * @var LoginUrlService
	 */
	protected $login_url_service;

	/**
	 * Constructor for LoginUrlRoutes.
	 */
	public function __construct() {
		$this->login_url_service = new LoginUrlService();
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			'brutefort/v1',
			'/login-url-settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'check_permission' ),
				),
			)
		);
	}

	/**
	 * Check permission.
	 *
	 * @return bool
	 */
	public function check_permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get Settings.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_settings(): \WP_REST_Response {
		return rest_ensure_response( $this->login_url_service->get_settings() );
	}

	/**
	 * Update Settings.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response
	 */
	public function update_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params();
		$data = isset( $params['formData'] ) ? $params['formData'] : $params;
		$this->login_url_service->update_settings( $data );
		return rest_ensure_response( array( 'success' => true ) );
	}
}

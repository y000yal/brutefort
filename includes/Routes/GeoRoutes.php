<?php
/**
 * Geo Routes for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Routes;

use BruteFort\Services\GeoService;

/**
 * GeoRoutes class for managing Geo Blocking settings.
 *
 * @package BruteFort
 */
class GeoRoutes {

	/**
	 * Geo service instance.
	 *
	 * @var GeoService
	 */
	protected $geo_service;

	/**
	 * Constructor for GeoRoutes.
	 */
	public function __construct() {
		$this->geo_service = new GeoService();
	}

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			'brutefort/v1',
			'/geo-settings',
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
	 * Get Geo Settings.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_settings(): \WP_REST_Response {
		return rest_ensure_response( $this->geo_service->get_settings() );
	}

	/**
	 * Update Geo Settings.
	 *
	 * @param \WP_REST_Request $request The request object.
	 * @return \WP_REST_Response
	 */
	public function update_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params();
		$data = isset( $params['formData'] ) ? $params['formData'] : $params;
		$this->geo_service->update_settings( $data );
		return rest_ensure_response( array( 'success' => true ) );
	}
}

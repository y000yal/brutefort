<?php
/**
 * Rate Limit Controller for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Controllers;

use BruteFort\Services\RateLimitService;
use BruteFort\Controllers\Controller as BaseController;
use WP_Error;
use WP_HTTP_Response;
use WP_Rest_Request;
use WP_REST_Response;

/**
 * Rate Limit Controller for managing rate limit settings.
 *
 * @package BruteFort
 */
class RateLimitController extends BaseController {

	/**
	 * Rate limit service instance.
	 *
	 * @var RateLimitService
	 */
	protected string|RateLimitService $rate_limit_service;

	/**
	 * Constructor for RateLimitController.
	 */
	public function __construct() {
		$this->rate_limit_service = new RateLimitService();
	}

	/**
	 * Get rate limit settings.
	 *
	 * @return WP_REST_Response Response containing rate limit settings.
	 */
	public function index(): WP_REST_Response {
		return $this->response( array( 'data' => $this->rate_limit_service->get_rate_limit_settings() ), 200 );
	}

	/**
	 * Store rate limit settings.
	 *
	 * @param WP_Rest_Request $request The REST request object.
	 * @return WP_HTTP_Response|WP_REST_Response|WP_Error Response with save result.
	 */
	public function store( WP_Rest_Request $request ): WP_HTTP_Response|WP_REST_Response|WP_Error {
		$params = $request->get_json_params();

		$result = $this->rate_limit_service->validate_and_sanitize_settings( $params );
		$result = apply_filters( 'brutef_after_rate_limit_validation', $result );

		if ( ! empty( $result['errors'] ) ) {
			return $this->response(
				array(
					'status'  => false,
					'message' => apply_filters( 'brutef_settings_failed_validation_message', __( 'Form not submitted, please fill all necessary fields.', 'brutefort' ) ),
					'errors'  => $result['errors'],
				),
				422
			);
		}

		$sanitized_values = apply_filters( 'brutef_before_rate_limit_settings_save', $result['sanitized'] );

		update_option( 'brutef_rate_limit_settings', json_encode( $sanitized_values ) );

		return $this->response(
			array(
				'status'  => true,
				'message' => apply_filters( 'brutef_settings_success_save_message', __( 'Settings saved successfully.', 'brutefort' ) ),
			),
			200
		);
	}
}

<?php


namespace BruteFort\Controllers;


use BruteFort\Services\IpSettingsService;
use BruteFort\Controllers\Controller as BaseController;
use WP_Error;
use WP_HTTP_Response;
use WP_Rest_Request;
use WP_REST_Response;

class IpSettingsController extends BaseController {

	protected string|IpSettingsService $ip_settings_service;

	public function __construct() {
		$this->ip_settings_service = new IpSettingsService(); 
	}


	public function index(WP_Rest_Request $request): WP_REST_Response {
		return $this->response( array( 'data' => $this->ip_settings_service->get_all_ips() ), 200 );
	}

	/**
	 * Get item.
	 *
	 * @param WP_Rest_Request $request Full detail about the request.
	 *
	 * @return WP_HTTP_Response|WP_REST_Response|WP_Error
	 * @since 1.0.0
	 *
	 */
	public function store( WP_Rest_Request $request ): WP_HTTP_Response|WP_REST_Response|WP_Error {
		$params = $request->get_json_params();

		$result = $this->rate_limit_service->validate_and_sanitize_settings( $params );
		$result = apply_filters( "bf_after_rate_limit_validation", $result );

		if ( ! empty( $result['errors'] ) ) {
			return $this->response( array(
				'status'  => false,
				'message' => apply_filters( "bf_settings_failed_validation_message", __( "Form not submitted, please fill all necessary fields.", "brutefort" ) ),
				'errors'  => $result['errors']
			),
				422 );
		}


		$sanitized_values = apply_filters( "bf_before_rate_limit_settings_save", $result['sanitized'] );

		update_option( "bf_rate_limit_settings", json_encode( $sanitized_values ) );

		return $this->response( array(
			'status'  => true,
			'message' => apply_filters( "bf_settings_success_save_message", __( "Settings saved successfully.", "brutefort" ) ),
		), 200 );

	}
}
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


	public function index( WP_Rest_Request $request ): WP_REST_Response {
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

		$result = $this->ip_settings_service->validate_and_sanitize_ip_settings( $params );

		$result = apply_filters( "bf_after_ip_settings_validation", $result );

		if ( ! empty( $result['errors'] ) ) {

			$message = $result['errors']['message'] ?? apply_filters( "bf_settings_failed_validation_message", __( "Form not submitted, please fill all necessary fields.", "brutefort" ) );

			return $this->response( array(
				'status'  => false,
				'message' => $message,
				'field'   => $result['errors']['field'],
			),
				422 );
		}

		$sanitized_values = apply_filters( "bf_before_ip_settings_save", $result['sanitized'] );
		$type             = $sanitized_values['bf_list_type'];
		$get_all_option   = $this->ip_settings_service->get_all_ips( $type );

		$get_all_option[] = $sanitized_values;

		update_option( "bf_{$type}ed_ips", json_encode( $get_all_option ) );

		return $this->response( array(
			'status'  => true,
			'message' => apply_filters( "bf_settings_success_save_message", __( "Settings saved successfully.", "brutefort" ) ),
		), 200 );

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
	public function delete( WP_Rest_Request $request ): WP_HTTP_Response|WP_REST_Response|WP_Error {
		$ips = sanitize_text_field( $request->get_param( 'ids' ) );

		if ( empty( $ips ) ) {
			return $this->response( array(
				'status'  => false,
				'message' => __( "No row selected.", "brutefort" )
			),
				422 );
		}
		$ips = json_decode($ips, true);
		$all_ips = $this->ip_settings_service->get_all_ips();

		$grouped = [
			'blacklist' => [],
			'whitelist' => [],
		];

		foreach ($all_ips as $entry) {
			$type = $entry['bf_list_type'];
			$grouped[$type][] = $entry;
		}
		
		 // Prevent deleting the user's own IP from whitelist if it's the only one
		$current_ip = '';
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$current_ip = wp_unslash($_SERVER['REMOTE_ADDR']);
		} else {
			$current_ip = gethostbyname(gethostname());
		}
		$current_ip = sanitize_text_field($current_ip);
		// Remove error_log in production
		// error_log("Current IP: $current_ip");
		$whitelist_ips = array_column($grouped['whitelist'], 'bf_ip_address');
		$is_current_ip_selected = in_array($current_ip, $ips);

		if ($is_current_ip_selected && count($whitelist_ips) === 1) {
			return $this->response(array(
				'status' => false,
				'message' => __("You cannot remove your own IP from the whitelist. At least one IP must remain.", "brutefort")
			), 403);
		}

		$grouped['blacklist'] = array_filter($grouped['blacklist'], function($entry) use ($ips) {
			return !in_array($entry['bf_ip_address'], $ips);
		});

		$grouped['whitelist'] = array_filter($grouped['whitelist'], function($entry) use ($ips, $current_ip) {
			 // Do not remove the user's own IP
			if ($entry['bf_ip_address'] == $current_ip) {
				return true;
			}
			return !in_array($entry['bf_ip_address'], $ips);
		});
		
		foreach($grouped as $type => $group) {
			update_option( "bf_{$type}ed_ips", json_encode( $group ) );
		}
		return $this->response( array(
			'status'  => true,
			'message' => apply_filters( "bf_ip_deleted_message", __( "Records deleted successfully.", "brutefort" ) ),
		), 200 );
	}
}
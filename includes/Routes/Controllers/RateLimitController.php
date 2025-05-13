<?php


namespace BruteFort\Routes\Controllers;


use WP_Rest_Request;

class RateLimitController {
	public function index(  ): void {
		
	}

	/**
	 * Get item.
	 *
	 * @param WP_Rest_Request $request Full detail about the request.
	 *
	 * @return \WP_HTTP_Response|\WP_REST_Response|\WP_Error
	 * @since 1.0.0
	 *
	 */
	public function store( WP_Rest_Request $request ): \WP_HTTP_Response|\WP_REST_Response|\WP_Error {
		$params = $request->get_json_params();

		return rest_ensure_response([
			'success' => true,
			'allData' => $params,
		]);
	}
}
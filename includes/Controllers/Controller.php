<?php
/**
 * Base Controller for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Controllers;

use WP_REST_Response;

/**
 * Base Controller class for REST API responses.
 *
 * @package BruteFort
 */
class Controller {
	/**
	 * Create a REST API response.
	 *
	 * @param mixed $response The response data.
	 * @param int   $code     The HTTP status code.
	 * @return WP_REST_Response The REST response object.
	 */
	protected function response( $response, $code ): WP_REST_Response {
		return new WP_REST_Response(
			$response,
			$code
		);
	}
}

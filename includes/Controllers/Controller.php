<?php
/**
 * Controller.php
 *
 * Controller.php
 *
 * @class    Controller.php
 * @package  BruteFort
 * @author   Yoyal Limbu
 * @date     5/16/2025 : 9:28 AM
 */

namespace BruteFort\Controllers;

use WP_REST_Response;

class Controller {
	protected function response( $response, $code ): WP_REST_Response {
		return new WP_REST_Response(
			$response,
			$code
		);
	}
}
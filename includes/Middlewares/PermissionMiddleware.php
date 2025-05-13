<?php
/**
 * @namespace BruteFort\Middleware
 * @class    PermissionMiddleware
 * @author   Yoyal Limbu
 * @date     13-05-2025 : 10:09 PM
 */
namespace BruteFort\Middlewares;

use WP_Error;
use WP_REST_Request;

/**
 * Handles REST API permission checks.
 */


class PermissionMiddleware {

	/**
	 * Authorizes the incoming REST request.
	 *
	 * @param WP_REST_Request $request
	 * @return true|WP_Error
	 */
	public static function authorize( WP_REST_Request $request ): WP_Error|bool {
		if (
			! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ||
			! current_user_can( 'manage_options' )
		) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You are not allowed to perform this action.', 'brutefort' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}
}

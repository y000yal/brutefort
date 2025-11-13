<?php
/**
 * Permission Middleware for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Middlewares;

use WP_Error;
use WP_REST_Request;

/**
 * Handles REST API permission checks.
 *
 * @package BruteFort
 */
class PermissionMiddleware {

	/**
	 * Authorizes the incoming REST request.
	 *
	 * @param WP_REST_Request $request The REST request object to authorize.
	 * @return true|WP_Error
	 */
	public static function authorize( WP_REST_Request $request ) {
		if (
			! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ||
			! current_user_can( 'manage_options' )
		) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You are not allowed to perform this action.', 'brutefort' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}
}

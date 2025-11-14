<?php
/**
 * Setup Wizard Controller for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Controllers;

use BruteFort\Controllers\Controller as BaseController;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Setup Wizard Controller for managing setup wizard completion.
 *
 * @package BruteFort
 */
class SetupWizardController extends BaseController {

	/**
	 * Mark setup wizard as completed.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function complete( WP_REST_Request $request ): WP_REST_Response {
		update_option( 'brutef_setup_wizard_completed', true );
		return $this->response(
			array(
				'status'  => true,
				'message' => __( 'Setup wizard completed.', 'brutefort' ),
			),
			200
		);
	}

	/**
	 * Get setup wizard completion status.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response Response containing completion status.
	 */
	public function get_status( WP_REST_Request $request ): WP_REST_Response {
		$completed = get_option( 'brutef_setup_wizard_completed', false );
		return $this->response(
			array(
				'completed' => (bool) $completed,
			),
			200
		);
	}

	/**
	 * Reset setup wizard (mark as not completed).
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response Response indicating success or failure.
	 */
	public function reset( WP_REST_Request $request ): WP_REST_Response {
		update_option( 'brutef_setup_wizard_completed', false );
		return $this->response(
			array(
				'status'  => true,
				'message' => __( 'Setup wizard has been reset.', 'brutefort' ),
			),
			200
		);
	}
}

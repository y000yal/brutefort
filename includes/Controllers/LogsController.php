<?php
/**
 * Logs Controller for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Controllers;

use BruteFort\Controllers\Controller as BaseController;
use BruteFort\Repositories\LogsRepository;
use BruteFort\Services\LogsService;
use WP_Error;
use WP_HTTP_Response;
use WP_Rest_Request;
use WP_REST_Response;

/**
 * Logs Controller for managing log entries.
 *
 * @package BruteFort
 */
class LogsController extends BaseController {

	/**
	 * Logs service instance.
	 *
	 * @var LogsService
	 */
	protected $logs_service;

	/**
	 * Logs repository instance.
	 *
	 * @var LogsRepository
	 */
	protected $logs_repository;

	/**
	 * Constructor for LogsController.
	 */
	public function __construct() {
		$this->logs_service    = new LogsService();
		$this->logs_repository = new LogsRepository();
	}

	/**
	 * Get all logs with details.
	 *
	 * @param WP_Rest_Request $request The REST request object.
	 * @return WP_REST_Response Response containing logs data.
	 */
	public function index( WP_Rest_Request $request ): WP_REST_Response {
		$request = $request->get_query_params();
		$result  = $this->logs_service->get_logs_with_details();
		return $this->response( array( 'data' => $result ), 200 );
	}

	/**
	 * Get log details by ID.
	 *
	 * @param WP_Rest_Request $request The REST request object.
	 * @return WP_REST_Response Response containing log details.
	 */
	public function get_log_details_by_id( WP_Rest_Request $request ): WP_REST_Response {
		$id          = (int) absint( $request->get_param( 'id' ) );
		$log_details = $this->logs_service->get_log_details( $id );

		return $this->response(
			array(
				'data' => $log_details,
				'status' => true,
			),
			200
		);
	}

	/**
	 * Unlock an IP address by log ID.
	 *
	 * @param WP_Rest_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error Response containing unlock result.
	 */
	public function unlock( WP_Rest_Request $request ) {
		$id = (int) absint( $request->get_param( 'id' ) );

		if ( empty( $id ) ) {
			return $this->response(
				array(
					'status'  => false,
					'message' => __( 'Invalid log ID.', 'brutefort' ),
				),
				422
			);
		}

		// Get the log entry to retrieve the IP address.
		$log = $this->logs_repository->retrieve( $id );

		if ( empty( $log ) || empty( $log['ip_address'] ) ) {
			return $this->response(
				array(
					'status'  => false,
					'message' => __( 'Log entry not found.', 'brutefort' ),
				),
				404
			);
		}

		$ip_address = sanitize_text_field( $log['ip_address'] );

		// Check if IP is actually locked before attempting to unlock.
		if ( ! $this->logs_service->is_ip_locked( $ip_address ) ) {
			return $this->response(
				array(
					'status'  => false,
					'message' => __( 'IP address is not currently locked.', 'brutefort' ),
				),
				400
			);
		}

		// Unlock the IP address.
		$unlocked = $this->logs_service->unlock_ip( $ip_address );

		if ( ! $unlocked ) {
			return $this->response(
				array(
					'status'  => false,
					'message' => __( 'Failed to unlock IP address.', 'brutefort' ),
				),
				500
			);
		}

		return $this->response(
			array(
				'status'  => true,
				'message' => __( 'IP address unlocked successfully.', 'brutefort' ),
			),
			200
		);
	}
}

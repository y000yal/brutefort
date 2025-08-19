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
	protected string|LogsService $logs_service;

	/**
	 * Logs repository instance.
	 *
	 * @var LogsRepository
	 */
	protected string|LogsRepository $logs_repository;

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
}

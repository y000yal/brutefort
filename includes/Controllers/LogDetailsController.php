<?php
/**
 * Log Details Controller for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Controllers;

use BruteFort\Controllers\Controller as BaseController;
use BruteFort\Repositories\LogDetailsRepository;
use BruteFort\Repositories\LogsRepository;
use BruteFort\Services\LogsService;
use WP_Error;
use WP_HTTP_Response;
use WP_Rest_Request;
use WP_REST_Response;

/**
 * Log Details Controller for managing log detail entries.
 *
 * @package BruteFort
 */
class LogDetailsController extends BaseController {

	/**
	 * Logs service instance.
	 *
	 * @var LogsService
	 */
	protected string|LogsService $logs_service;

	/**
	 * Log details repository instance.
	 *
	 * @var LogDetailsRepository
	 */
	protected string|LogDetailsRepository $log_details_repository;

	/**
	 * Constructor for LogDetailsController.
	 */
	public function __construct() {
		$this->logs_service    = new LogsService();
		$this->log_details_repository = new LogDetailsRepository();
	}

	/**
	 * Delete log details by ID.
	 *
	 * @param WP_Rest_Request $request The REST request object.
	 * @return WP_REST_Response Response with deletion result.
	 */
	public function delete_log_details( WP_Rest_Request $request ): WP_REST_Response {
		$id = (int) absint( $request->get_param( 'id' ) );
		$this->log_details_repository->delete( $id );
		return $this->response( array( 'message' => __( 'Log details deleted successfully.', 'brutefort' ) ), 200 );
	}
}

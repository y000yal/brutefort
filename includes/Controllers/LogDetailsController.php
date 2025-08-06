<?php


namespace BruteFort\Controllers;


use BruteFort\Controllers\Controller as BaseController;
use BruteFort\Repositories\LogDetailsRepository;
use BruteFort\Repositories\LogsRepository;
use BruteFort\Services\LogsService;
use WP_Error;
use WP_HTTP_Response;
use WP_Rest_Request;
use WP_REST_Response;

class LogDetailsController extends BaseController {

	protected string|LogsService $logs_service;
	protected string|LogDetailsRepository $log_details_repository;

	public function __construct() {
		$this->logs_service    = new LogsService();
		$this->log_details_repository = new LogDetailsRepository();
	}

	public function delete_log_details( WP_Rest_Request $request ): WP_REST_Response {
		$id = (int) absint( $request->get_param( 'id' ) );
		$this->log_details_repository->delete( $id );
		return $this->response( array( 'message' => __( 'Log details deleted successfully.', 'brutefort' ) ), 200 );
	}



}
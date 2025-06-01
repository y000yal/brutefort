<?php


namespace BruteFort\Controllers;


use BruteFort\Controllers\Controller as BaseController;
use BruteFort\Repositories\LogsRepository;
use BruteFort\Services\LogsService;
use WP_Error;
use WP_HTTP_Response;
use WP_Rest_Request;
use WP_REST_Response;

class LogsController extends BaseController {

	protected string|LogsService $logs_service;
	protected string|LogsRepository $logs_repository;

	public function __construct() {
		$this->logs_service    = new LogsService();
		$this->logs_repository = new LogsRepository();
	}

	public function index( WP_Rest_Request $request ): WP_REST_Response {
		$request = $request->get_query_params();
		$result  = $this->logs_service->get_logs_with_details();


		return $this->response( array( 'data' => $result ), 200 );
	}

	public function get_log_details_by_id( WP_Rest_Request $request ): WP_REST_Response {
		$id          = (int) absint( $request->get_param( 'id' ) );
		$log_details = $this->logs_service->get_log_details( $id );

		return $this->response( array( 'data' => $log_details, 'status' => true ), 200 );
	}

}
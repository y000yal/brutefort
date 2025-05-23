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
		$result = $this->logs_repository->index( [
			[
			]
		], 'ID', 'DESC', 10 ,'',false);

		return $this->response( array( 'data' => $result ), 200 );
	}

}
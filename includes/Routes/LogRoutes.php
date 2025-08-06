<?php

namespace BruteFort\Routes;

use BruteFort\Controllers\LogsController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class LogRoutes extends AbstractRoutes {

	/**
	 * The base route name.
	 *
	 * @var string
	 */
	protected string $rest_base = 'logs';


	public string $controller = LogsController::class;


	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {

		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base,
			[
				[
					'methods'             => 'GET',
					'permission_callback' => [ $this->middleware, 'authorize' ],
					'callback'            => [ new LogsController(), 'index' ],
				],
			]
		);
		register_rest_route(
			$this->namespace . '/' . $this->version. '/',
			'/' . $this->rest_base . '/(?P<id>\d+)/details',
			[
				[
					'methods'             => 'GET',
					'permission_callback' => [ $this->middleware, 'authorize' ],
					'callback'            => [ new LogsController(), 'get_log_details_by_id' ],
					'args'                => [
						'id' => [
							'required' => true,
							'type'     => 'integer',
						],
					],
				],
			]
		);

	}


}

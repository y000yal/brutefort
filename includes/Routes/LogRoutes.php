<?php
/**
 * Log Routes for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Routes;

use BruteFort\Controllers\LogsController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Log Routes class for managing log REST endpoints.
 *
 * @package BruteFort
 */
class LogRoutes extends AbstractRoutes {

	/**
	 * The base route name.
	 *
	 * @var string
	 */
	protected string $rest_base = 'logs';

	/**
	 * The controller class for this route.
	 *
	 * @var string
	 */
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
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new LogsController(), 'index' ),
				),
			)
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base . '/(?P<id>\d+)/details',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new LogsController(), 'get_log_details_by_id' ),
					'args'                => array(
						'id' => array(
							'required' => true,
							'type'     => 'integer',
						),
					),
				),
			)
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base . '/(?P<id>\d+)/unlock',
			array(
				array(
					'methods'             => 'POST',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new LogsController(), 'unlock' ),
					'args'                => array(
						'id' => array(
							'required' => true,
							'type'     => 'integer',
						),
					),
				),
			)
		);
	}
}

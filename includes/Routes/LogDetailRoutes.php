<?php
/**
 * Log Detail Routes for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Routes;

use BruteFort\Controllers\LogDetailsController;
use BruteFort\Controllers\LogsController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Log Detail Routes class for managing log detail REST endpoints.
 *
 * @package BruteFort
 */
class LogDetailRoutes extends AbstractRoutes {

	/**
	 * The base route name.
	 *
	 * @var string
	 */
	protected string $rest_base = 'log-details';

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
			'/' . $this->rest_base . '/(?P<id>\d+)/delete',
			array(
				array(
					'methods'             => 'DELETE',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new LogDetailsController(), 'delete_log_details' ),
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

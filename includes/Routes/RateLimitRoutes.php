<?php

namespace BruteFort\Routes;

use BruteFort\Routes\Controllers\RateLimitController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class RateLimitRoutes extends AbstractRoutes {

	/**
	 * The base route name.
	 *
	 * @var string
	 */
	protected string $rest_base = 'rate-limit-settings';


	public string $controller = RateLimitController::class;

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
					'callback'            => [ new RateLimitController(), 'index' ],
					'permission_callback' => [ $this->middleware, 'authorize' ],
				],
			]
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base,
			[
				[
					'methods'             => 'POST',
					'callback'            => [ new RateLimitController(), 'store' ],
					'permission_callback' => [ $this->middleware, 'authorize' ],
				],
			]
		);
		
	}

	/**
	 * REST response callback.
	 *
	 * @param WP_REST_Request|null $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function index( WP_REST_Request $request = null ): WP_REST_Response|WP_Error {
		return new WP_REST_Response(
			[
				'success'   => true,
				'changelog' => [ 'hello' ],
			],
			200
		);
	}

	/**
	 * Permissions check for the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public static function check_admin_permissions( WP_REST_Request $request ): bool|WP_Error {
		return current_user_can( 'manage_options' );
	}
}

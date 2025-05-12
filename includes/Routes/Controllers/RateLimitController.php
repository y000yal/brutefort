<?php

namespace BruteFort\Routes\Controllers;

use BruteFort\Routes\AbstractRoutes;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class RateLimitController extends AbstractRoutes {

	/**
	 * The base route name.
	 *
	 * @var string
	 */
	protected string $rest_base = 'rate-limit';

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {

		register_rest_route(
			$this->namespace.'/'.$this->version.'/',
			'/' . $this->rest_base,
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'index' ],
					'permission_callback' => [ $this, 'check_admin_permissions' ],
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

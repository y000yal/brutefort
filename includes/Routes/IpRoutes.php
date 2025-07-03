<?php

namespace BruteFort\Routes;

use BruteFort\Controllers\IpSettingsController;
use BruteFort\Controllers\RateLimitController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class IpRoutes extends AbstractRoutes {

	/**
	 * The base route name.
	 *
	 * @var string
	 */
	protected string $rest_base = 'ip-settings';


	public string $controller = IpSettingsController::class;

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
					'callback'            => [ new IpSettingsController(), 'index' ],
				],
			]
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base,
			[
				[
					'methods'             => 'POST',
					'permission_callback' => [ $this->middleware, 'authorize' ],
					'callback'            => [ new IpSettingsController(), 'store' ],
				],
			]
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base .'/delete',
			[
				[
					'methods'             => 'DELETE',
					'permission_callback' => [ $this->middleware, 'authorize' ],
					'callback'            => [ new IpSettingsController(), 'delete' ],
				],
			]
		);
	}

}

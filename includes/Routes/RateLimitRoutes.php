<?php
/**
 * Rate Limit Routes for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Routes;

use BruteFort\Controllers\RateLimitController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Rate Limit Routes class for managing rate limit REST endpoints.
 *
 * @package BruteFort
 */
class RateLimitRoutes extends AbstractRoutes {

	/**
	 * The base route name.
	 *
	 * @var string
	 */
	protected string $rest_base = 'rate-limit-settings';

	/**
	 * The controller class for this route.
	 *
	 * @var string
	 */
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
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new RateLimitController(), 'index' ),
				),
			)
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'POST',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new RateLimitController(), 'store' ),
				),
			)
		);
	}
}

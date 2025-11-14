<?php
/**
 * IP Routes for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Routes;

use BruteFort\Controllers\IpSettingsController;
use BruteFort\Controllers\RateLimitController;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * IP Routes class for managing IP-related REST endpoints.
 *
 * @package BruteFort
 */
class IpRoutes extends AbstractRoutes {

	/**
	 * The base route name.
	 *
	 * @var string
	 */
	protected string $rest_base = 'ip-settings';

	/**
	 * The controller class for this route.
	 *
	 * @var string
	 */
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
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new IpSettingsController(), 'index' ),
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
					'callback'            => array( new IpSettingsController(), 'store' ),
				),
			)
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base . '/delete',
			array(
				array(
					'methods'             => 'DELETE',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new IpSettingsController(), 'delete' ),
				),
			)
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base . '/current-ip',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new IpSettingsController(), 'get_current_ip' ),
				),
			)
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base . '/setup-wizard-whitelist',
			array(
				array(
					'methods'             => 'POST',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new IpSettingsController(), 'whitelist_from_setup_wizard' ),
				),
			)
		);
	}
}

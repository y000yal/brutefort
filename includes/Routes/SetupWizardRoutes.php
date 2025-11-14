<?php
/**
 * Setup Wizard Routes for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Routes;

use BruteFort\Controllers\SetupWizardController;

/**
 * Setup Wizard Routes class for managing setup wizard REST endpoints.
 *
 * @package BruteFort
 */
class SetupWizardRoutes extends AbstractRoutes {

	/**
	 * The base route name.
	 *
	 * @var string
	 */
	protected string $rest_base = 'setup-wizard';

	/**
	 * The controller class for this route.
	 *
	 * @var string
	 */
	public string $controller = SetupWizardController::class;

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base . '/complete',
			array(
				array(
					'methods'             => 'POST',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new SetupWizardController(), 'complete' ),
				),
			)
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base . '/status',
			array(
				array(
					'methods'             => 'GET',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new SetupWizardController(), 'get_status' ),
				),
			)
		);
		register_rest_route(
			$this->namespace . '/' . $this->version . '/',
			'/' . $this->rest_base . '/reset',
			array(
				array(
					'methods'             => 'POST',
					'permission_callback' => array( $this->middleware, 'authorize' ),
					'callback'            => array( new SetupWizardController(), 'reset' ),
				),
			)
		);
	}
}

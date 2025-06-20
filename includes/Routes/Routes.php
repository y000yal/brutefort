<?php
/**
 * Routes class.
 *
 * @since 1.0.0
 * @package BruteFort\Routes
 */

namespace BruteFort\Routes;

use BruteFort\Routes\RateLimitRoutes;
use JetBrains\PhpStorm\ArrayShape;

class Routes {

	/**
	 * Hook into WordPress REST API init.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	/**
	 * Register all REST API routes by namespace and class.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes(): void {

		foreach ( $this->get_rest_classes() as $namespace => $classes ) {
			foreach ( $classes as $class_name ) {
				if ( class_exists( $class_name ) ) {
					$controller = new $class_name();
					if ( method_exists( $controller, 'register_routes' ) ) {
						$controller->register_routes();
					}
				}
			}
		}
	}

	/**
	 * Get all REST classes grouped by namespace.
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	protected function get_rest_classes(): array {
		return apply_filters(
			'brutefort_api_get_rest_namespaces',
			[
				'brutefort/v1' => $this->get_routes_classes(),
			]
		);
	}

	/**
	 * All controller classes under brutefort/v1.
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	#[ArrayShape( [ 'rate-limit' => "string", "logs" => "string", "ip-settings" => "string" ] )]
	public function get_routes_classes(): array {
		return [
			'rate-limit'  => RateLimitRoutes::class,
			'logs'        => LogRoutes::class,
			'ip-settings' => IpRoutes::class
		];
	}
}

<?php
/**
 * Abstract Routes.
 *
 * @since 1.0.0
 * @package BruteFort\Routes
 */

namespace BruteFort\Routes;

use BruteFort\Middlewares\PermissionMiddleware;

/**
 * Abstract class for defining routes.
 *
 * @since 1.0.0
 */
abstract class AbstractRoutes {

	/**
	 * The namespace of this controller's route.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected string $namespace = 'brutefort';

	/**
	 * The version of the API.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected string $version = 'v1';

	/**
	 * The base of this controller's route.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected string $rest_base;

	/**
	 * Register routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	abstract public function register_routes(): void;

	/**
	 * The middleware class for permission checking.
	 *
	 * @since 1.0.0
	 *
	 * @var string|PermissionMiddleware
	 */
	public string|PermissionMiddleware $middleware = PermissionMiddleware::class;
}

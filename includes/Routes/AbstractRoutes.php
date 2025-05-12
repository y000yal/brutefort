<?php

/**
 * Abstract Routes.
 *
 * @since 1.0.0
 * @package SmartSMTP\Routes
 */

namespace BruteFort\Routes;

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
}
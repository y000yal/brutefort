<?php
/**
 * Helper functions for BruteFort plugin.
 *
 * @package BruteFort
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the main BruteFort instance.
 *
 * @return BruteFort The main plugin instance.
 */
function BF(): BruteFort { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return BruteFort::instance();
}

/**
 * Activation/Deactivation handling for Free vs Pro conflict.
 */
function brutefort_free_activated(): void {
	set_transient( 'brutefort_free_activated', true );
}

/**
 * Handle free plugin deactivation.
 *
 * @return void
 */
function brutefort_free_deactivated(): void {
	delete_transient( 'brutefort_free_activated' );
}

/**
 * Handle free plugin deactivation when pro is active.
 *
 * @return void
 */
function brutefort_free_deactivate(): void {
	if ( get_transient( 'brutefort_pro_activated' ) ) {
		deactivate_plugins( 'brutefort/brutefort.php' );
		do_action( 'brutefort_free_deactivate', 'brutefort/brutefort.php' );
		delete_transient( 'brutefort_pro_activated' );
	}
}

// Hook up the activation/deactivation functions.
add_action( 'activate_brutefort/brutefort.php', 'brutefort_free_activated' );
add_action( 'deactivate_brutefort/brutefort.php', 'brutefort_free_deactivated' );
add_action( 'admin_init', 'brutefort_free_deactivate' );

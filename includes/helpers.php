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
function brutef_get_instance(): BruteFort {
	return BruteFort::instance();
}

/**
 * Activation/Deactivation handling for Free vs Pro conflict.
 */
function brutef_free_activated(): void {
	set_transient( 'brutef_free_activated', true );
}

/**
 * Handle free plugin deactivation.
 *
 * @return void
 */
function brutef_free_deactivated(): void {
	delete_transient( 'brutef_free_activated' );
}

// Hook up the activation/deactivation functions.
add_action( 'activate_brutefort/brutefort.php', 'brutef_free_activated' );
add_action( 'deactivate_brutefort/brutefort.php', 'brutef_free_deactivated' );

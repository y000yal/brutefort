<?php
/**
 * Security traits for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Traits;

trait SecurityTraits {
	/**
	 * Get the current user's IP address safely.
	 *
	 * @return string The sanitized IP address.
	 */
	public function brutefort_get_ip() {
		$ip = '';
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		} else {
			$ip = 'unknown';
		}
		return $ip;
	}
}

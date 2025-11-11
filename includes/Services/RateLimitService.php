<?php
/**
 * Rate Limit Service for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Services;

/**
 * Rate Limit Service for managing rate limiting settings.
 *
 * @package BruteFort
 */
class RateLimitService extends BaseService {


	/**
	 * Get rate limit settings.
	 *
	 * @return array Rate limit settings array.
	 */
	public function get_rate_limit_settings() {
		$default_settings = json_encode(
			array(
				'brutef_max_attempts'             => 5,
				'brutef_time_window'              => 30,
				'brutef_enable_lockout'           => false,
				'brutef_lockout_duration'         => 60,
				'brutef_enable_lockout_extension' => false,
				'brutef_extend_lockout_duration'  => 1,
				'brutef_custom_error_message'     => __( 'Too many attempts, Please try again in a while!!', 'brutefort' ),
			)
		);
		return json_decode( get_option( 'brutef_rate_limit_settings', $default_settings ), true );
	}
}

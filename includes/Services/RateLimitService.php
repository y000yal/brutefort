<?php

namespace BruteFort\Services;

class RateLimitService extends BaseService {


	public function get_rate_limit_settings() {
		$default_settings = json_encode( array(
			"bf_max_attempts"             => 5,
			"bf_time_window"              => 30,
			"bf_enable_lockout"           => false,
			"bf_lockout_duration"         => 60,
			"bf_enable_lockout_extension" => false,
			"bf_extend_lockout_duration"  => 1,
			"bf_custom_error_message"     => __( "Too many attempts, Please try again in a while!!", "brutefort" )
		) );
		return json_decode( get_option( "bf_rate_limit_settings", $default_settings ), true );
	}
}
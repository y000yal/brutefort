<?php

namespace BruteFort\Services;

class RateLimitService {


	/**
	 * Validates and sanitizes an array of settings.
	 *
	 * @param array $data An array of settings, each with 'value', 'type', and 'is_required' keys.
	 *
	 * @return array An associative array containing 'errors' and 'sanitized' data.
	 */
	public function validate_and_sanitize_settings( array $data ): array {
		$errors    = [];
		$sanitized = [];

		foreach ( $data['formData'] as $field => $details ) {
			$value    = $details['value'] ?? null;
			$type     = $details['type'] ?? null;
			$required = $details['required'] ?? false;

			switch ( $type ) {
				case 'number':

					if ( $required && ( $value === '' || ! is_numeric( $value ) || $value < 1 ) ) {
						$errors[ $field ] = 'This field must be a valid number.';
					}
					$sanitized[ $field ] = is_numeric( $value ) ? (int) $value : 0;
					break;

				case 'text':
					$trimmed = trim( (string) $value );

					if ( $required && $trimmed === '' ) {
						$errors[ $field ] = 'This field cannot be empty.';
					}
					$sanitized[ $field ] = sanitize_text_field( $trimmed );
					break;

				case 'checkbox':
					// Checkbox doesn't need validation even if required
					$sanitized[ $field ] = in_array( $value, [ 'on', '1', 1, true ], true );
					break;

				default:
					// fallback: sanitize text
					$sanitized[ $field ] = sanitize_text_field( trim( (string) $value ) );
					break;
			}
		}

		return [
			'errors'    => $errors,
			'sanitized' => $sanitized,
		];
	}

	public function get_rate_limit_settings() {
		$default_settings = json_encode( array(
			"bf_max_attempts"             => 5,
			"bf_time_window"              => 30,
			"bf_lockout_duration"         => 240,
			"bf_enable_lockout_extension" => false,
			"bf_extend_lockout_duration"  => 1,
			"bf_custom_error_message"     => __( "Too many attempts, Please try again in a while!!", "brutefort" )
		) );

		return json_decode( get_option( "bf_rate_limit_settings", $default_settings ), true );
	}
}
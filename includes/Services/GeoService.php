<?php
/**
 * Geo Service for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Services;

/**
 * GeoService class for handling IP-to-Country lookups.
 *
 * @package BruteFort
 */
class GeoService {

	/**
	 * API URL for IP-to-Country lookup.
	 * Using ip-api.com (free for non-commercial use, no API key required).
	 *
	 * @var string
	 */
	private const API_URL = 'http://ip-api.com/json/%s?fields=status,countryCode';

	/**
	 * Get the country code for a given IP address.
	 *
	 * @param string $ip The IP address.
	 * @return string|null The 2-letter country code (e.g., 'US', 'NP') or null if not found.
	 */
	public function get_country_code( string $ip ): ?string {
		// 1. Check for Cloudflare header.
		if ( isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
			return strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) );
		}

		// 2. Check transient cache.
		$cache_key = 'brutef_geo_' . md5( $ip );
		$cached_country = get_transient( $cache_key );
		if ( false !== $cached_country ) {
			return $cached_country;
		}

		// 3. Call external API.
		$response = wp_remote_get( sprintf( self::API_URL, $ip ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['status'] ) && 'success' === $data['status'] && isset( $data['countryCode'] ) ) {
			$country_code = strtoupper( $data['countryCode'] );
			// Cache for 24 hours.
			set_transient( $cache_key, $country_code, DAY_IN_SECONDS );
			return $country_code;
		}

		return null;
	}

	/**
	 * Check if the given IP is blocked based on country settings.
	 *
	 * @param string $ip The IP address.
	 * @return bool True if blocked, false otherwise.
	 */
	public function is_country_blocked( string $ip ): bool {
		$settings = get_option( 'brutef_geo_settings', array() );

		// If geo blocking is not enabled, return false.
		if ( empty( $settings['enabled'] ) ) {
			return false;
		}

		$country_code = $this->get_country_code( $ip );

		if ( ! $country_code ) {
			// If we can't determine the country, we shouldn't block (fail open).
			return false;
		}

		$blocked_countries = $settings['blocked_countries'] ?? array();
		$allowed_countries = $settings['allowed_countries'] ?? array();
		$mode              = $settings['mode'] ?? 'blacklist'; // 'blacklist' or 'whitelist'.

		if ( 'whitelist' === $mode ) {
			// Block if NOT in allowed list.
			return ! in_array( $country_code, $allowed_countries, true );
		} else {
			// Block if IN blocked list.
			return in_array( $country_code, $blocked_countries, true );
		}
	}

	/**
	 * Get Geo Settings.
	 *
	 * @return array
	 */
	public function get_settings(): array {
		return get_option(
			'brutef_geo_settings',
			array(
				'enabled'           => false,
				'mode'              => 'blacklist',
				'blocked_countries' => array(),
				'allowed_countries' => array(),
			)
		);
	}

	/**
	 * Update Geo Settings.
	 *
	 * @param array $settings The new settings.
	 * @return bool True on success.
	 */
	public function update_settings( array $settings ): bool {
		$current = $this->get_settings();
		$new     = wp_parse_args( $settings, $current );
		return update_option( 'brutef_geo_settings', $new );
	}
}

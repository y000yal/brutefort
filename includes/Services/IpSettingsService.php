<?php
/**
 * IP Settings Service for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Services;

/**
 * IP Settings Service for managing IP whitelist and blacklist.
 *
 * @package BruteFort
 */
class IpSettingsService extends BaseService {

	/**
	 * Validate and sanitize IP settings.
	 *
	 * @param array $params The parameters to validate.
	 * @return array Validation result with errors and sanitized data.
	 */
	public function validate_and_sanitize_ip_settings( $params ): array {
		$normal_validation = $this->validate_and_sanitize_settings( $params );
		if ( ! empty( $normal_validation['errors'] ) ) {
			return $normal_validation;
		}
		if ( $this->check_ip_exists( $normal_validation['sanitized']['brutef_ip_address'] ) ) {
			$normal_validation['errors'] = array(
				'field' => 'brutef_ip_address',
				'message' => 'Entry already exists.',
			);
		}
		return $normal_validation;
	}
	/**
	 * Get all IP addresses by type.
	 *
	 * @param string $type The type of IPs to get (whitelist, blacklist, or all).
	 * @return array Array of IP addresses.
	 */
	public function get_all_ips( $type = 'all' ) {
		$types = array(
			'whitelist' => 'brutef_whitelisted_ips',
			'blacklist' => 'brutef_blacklisted_ips',
		);

		if ( 'all' === $type ) {
			$ips = array();
			foreach ( $types as $option ) {
				$ips = array_merge( $ips, json_decode( get_option( $option, '[]' ), true ) );
			}
			return $ips;
		}

		if ( isset( $types[ $type ] ) ) {
			return json_decode( get_option( $types[ $type ], '[]' ), true );
		}

		return array();
	}
	/**
	 * Check if an IP address exists in the lists.
	 *
	 * @param string      $ip   The IP address to check.
	 * @param string|null $type The type to check (whitelist, blacklist, or null for any).
	 * @return bool True if IP exists, false otherwise.
	 */
	public function check_ip_exists( $ip, $type = null ): bool {
		$all_ips = $this->get_all_ips();
		foreach ( $all_ips as $entry ) {
			if ( is_array( $entry ) && isset( $entry['brutef_ip_address'] ) && $entry['brutef_ip_address'] === $ip ) {
				if ( null !== $type ) {
					if ( $entry['brutef_list_type'] !== $type ) {
						return false;
					}
				}
				return true;
			}
		}
		return false;
	}
}

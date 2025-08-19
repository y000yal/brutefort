<?php
/**
 * Logs Interface for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Interfaces;

use BruteFort\Interfaces\BaseInterface;

/**
 * Logs Interface for managing log operations.
 *
 * @package BruteFort
 */
/**
 * Logs Interface for managing log operations.
 *
 * @package BruteFort
 */
interface LogsInterface extends BaseInterface {
	/**
	 * Get log entry by IP address.
	 *
	 * @param string $ip The IP address to search for.
	 * @return array The log entry data.
	 */
	public function get_log_by_ip( $ip ): array;
}

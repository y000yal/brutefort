<?php
/**
 * Log Details Repository for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Repositories;

use BruteFort\Database\TableList;
use BruteFort\Interfaces\LogDetailsInterface;

/**
 * Log Details Repository for managing log detail entries.
 *
 * @package BruteFort
 */
class LogDetailsRepository extends \BruteFort\Repositories\BaseRepository implements LogDetailsInterface {
	/**
	 * Log details table name.
	 *
	 * @var string
	 */
	protected string $table;

	/**
	 * Constructor for LogDetailsRepository.
	 */
	public function __construct() {
		$this->table = TableList::brute_fort_log_details();
	}

	/**
	 * Get the latest locked log detail for a specific log ID.
	 *
	 * @param mixed $id The log ID to search for.
	 *
	 * @return array The latest locked log detail.
	 */
	public function get_latest_locked_detail( $id ): array {
		return (array) $this->wpdb()->get_row(
			$this->wpdb()->prepare(
				'SELECT * FROM ' . $this->table . " 
                    WHERE log_id = %s AND status='locked'
                    ORDER BY attempt_time DESC LIMIT 1",
				sanitize_text_field( $id )
			)
		);
	}
}

<?php

namespace BruteFort\Repositories;


use BruteFort\Database\TableList;
use BruteFort\Interfaces\LogDetailsInterface;

class LogDetailsRepository extends \BruteFort\Repositories\BaseRepository implements LogDetailsInterface {
	protected string $table;

	public function __construct() {
		$this->table = TableList::brute_fort_log_details();
	}

	/**
	 * get_logs_by_status
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function get_latest_locked_detail( $id ): array {
		return (array) $this->wpdb()->get_row( $this->wpdb()->prepare(
			"SELECT * FROM " . $this->table . " 
                    WHERE log_id = %s AND status='locked'
                    ORDER BY attempt_time DESC LIMIT 1",
			sanitize_text_field( $id )
		) );
	}

}

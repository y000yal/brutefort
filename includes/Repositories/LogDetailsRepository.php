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
	 * @param $status
	 *
	 * @return array
	 */
	public function get_logs_by_status( $id, $status ): array {
		return (array) $this->wpdb()->get_row( $this->wpdb()->prepare(
			"SELECT * FROM " . $this->table . " 
                    WHERE ip_address = %s AND status='%s'
                    ORDER BY ID DESC LIMIT 1",
			sanitize_text_field( $id ), sanitize_text_field( $status )
		) );
	}

}

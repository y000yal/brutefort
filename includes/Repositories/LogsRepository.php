<?php

namespace BruteFort\Repositories;


use BruteFort\Database\TableList;
use BruteFort\Interfaces\LogsInterface;

class LogsRepository extends \BruteFort\Repositories\BaseRepository implements LogsInterface {
	protected string $table;

	public function __construct() {
		$this->table = TableList::brute_fort_logs();
	}

	/**
	 * get_log_by_ip
	 *
	 * @param $ip
	 *
	 * @return array
	 */
	public function get_log_by_ip( $ip ): array {

		return (array) $this->wpdb()->get_row( $this->wpdb()->prepare(
			"SELECT * FROM " . $this->table . " 
                    WHERE ip_address = %s
                    ORDER BY ID DESC LIMIT 1",
			sanitize_text_field( $ip )
		) );
	}

}

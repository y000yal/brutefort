<?php

namespace BruteFort\Repositories;


use BruteFort\Database\TableList;
use BruteFort\Interfaces\LogsInterface;

class LogsRepository extends \BruteFort\Repositories\BaseRepository implements LogsInterface {
	protected string $table, $log_details_table;

	public function __construct() {
		$this->table = TableList::brute_fort_logs();
		$this->log_details_table = TableList::brute_fort_log_details();
	}
	/**
	 * index
	 *
	 * @param array $conditions
	 * @param string $order_by
	 * @param string $order
	 * @param $limit
	 * @param $offset
	 * @param bool $get_count
	 *
	 * @return array|object|string|null
	 */
	public function index( array $conditions = [], string $order_by = 'ID', string $order = "DESC", $limit = null, $offset = null, bool $get_count = false ): array|object|string|null {
		$sql           = "SELECT " . ( $get_count ? " COUNT(*) " : " * " ) . " FROM {$this->table}";
		$args          = [];
		$where_clauses = [];

		foreach ( $conditions as $key => $group ) {
			// OR group
			if ( is_array( $group ) && isset( $group['or'] ) && is_array( $group['or'] ) ) {
				$or_clauses = [];
				foreach ( $group['or'] as $cond ) {
					$column       = esc_sql( $cond['column'] );
					$operator     = $cond['operator'] ?? '=';
					$value        = $cond['value'];
					$or_clauses[] = "$column $operator %s";
					$args[]       = $value;
				}
				$where_clauses[] = '(' . implode( ' OR ', $or_clauses ) . ')';
			} // AND group
			else {
				foreach ( $group as $column => $value ) {
					$column = esc_sql( $column );
					if ( is_array( $value ) && isset( $value['operator'], $value['value'] ) ) {
						$where_clauses[] = "$column {$value['operator']} %s";
						$args[]          = $value['value'];
					} else {
						$where_clauses[] = "$column = %s";
						$args[]          = $value;
					}
				}
			}
		}

		if ( ! empty( $where_clauses ) ) {
			$sql .= " WHERE " . implode( ' AND ', $where_clauses );
		}

		$sql .= " JOIN wp_brute_fort_log_details ON wp_brute_fort_logs.ID = wp_brute_fort_log_details.log_id ";
		// ORDER BY

		$sql .= " ORDER BY wp_brute_fort_logs." . esc_sql( $order_by ) . " " . $order;

		
		// LIMIT / OFFSET
		if ( is_numeric( $limit ) ) {
			$sql    .= " LIMIT %d";
			$args[] = $limit;

			if ( is_numeric( $offset ) ) {
				$sql    .= " OFFSET %d";
				$args[] = $offset;
			}
		}


		$prepared_sql = $this->wpdb()->prepare( $sql, ...$args );

		if ( $get_count ) {
			return $this->wpdb()->get_var( $prepared_sql );
		}

		return $this->wpdb()->get_results( $prepared_sql );

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

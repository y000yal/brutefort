<?php

namespace BruteFort\Repositories;

use BruteFort\Interfaces\BaseInterface;
use mysqli_result;
use stdClass;
use wpdb;

class BaseRepository implements BaseInterface {
	protected string $table;

	/**
	 * Return global wpdb.
	 *
	 * @return wpdb
	 */
	public function wpdb(): wpdb {
		global $wpdb;

		return $wpdb;
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
		global $wpdb;

		$sql           = "SELECT " . ( $get_count ? "COUNT(*)" : "*" ) . " FROM {$this->table}";
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

		// ORDER BY

		$sql .= " ORDER BY " . esc_sql( $order_by ) . " " . $order;


		// LIMIT / OFFSET
		if ( is_numeric( $limit ) ) {
			$sql    .= " LIMIT %d";
			$args[] = $limit;

			if ( is_numeric( $offset ) ) {
				$sql    .= " OFFSET %d";
				$args[] = $offset;
			}
		}

		$prepared_sql = $wpdb->prepare( query: $sql, ...$args );
		if ( $get_count ) {
			return $wpdb->get_var( $prepared_sql );
		}

		return $wpdb->get_results( $prepared_sql );

	}

	/**
	 * create
	 *
	 * @param $data
	 *
	 * @return array|false|mixed|object|stdClass|void
	 */
	public function create( $data ): mixed {
		global $wpdb;

		$result    = $wpdb->insert(
			$this->table,
			$data
		);
		$insert_id = $wpdb->insert_id;

		return $this->retrieve( $insert_id );
	}

	/**
	 * Retrieve specific record by id.
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public function retrieve( $id ): array {
		global $wpdb;
		
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $this->table WHERE ID = %d",
				$id
			),
			ARRAY_A
		);

		return ! $result ? [] : $result;
	}

	/**
	 * Update specific record by ID
	 *
	 * @param $id
	 * @param $data
	 *
	 * @return int|bool|mysqli_result|null
	 */
	public function update( $id, $data ): int|bool|null|mysqli_result {
		global $wpdb;
		
		return $wpdb->update(
			$this->table,
			$data,
			array( 'ID' => $id )
		);
	}

	/**
	 * delete_multiple
	 *
	 * @param $ids
	 *
	 * @return int|bool|mysqli_result|null
	 */
	public function delete_multiple( $ids ): int|bool|null|mysqli_result {
		global $wpdb;
		
		// Convert array to comma-separated placeholders
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$prepared_sql = $wpdb->prepare( "DELETE FROM $this->table WHERE ID IN ($placeholders)", $ids );
		
		return $wpdb->query( $prepared_sql );
	}

	/**
	 * Delete single record by ID
	 *
	 * @param $id
	 *
	 * @return int|bool|mysqli_result|null
	 */
	public function delete( $id ): int|bool|null|mysqli_result {
		global $wpdb;
		
		return $wpdb->delete( $this->table, array( 'ID' => $id ) );
	}
}

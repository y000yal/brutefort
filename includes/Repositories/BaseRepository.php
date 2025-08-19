<?php
/**
 * Base Repository for BruteFort plugin.
 *
 * @package BruteFort
 */

namespace BruteFort\Repositories;

use BruteFort\Interfaces\BaseInterface;
use mysqli_result;
use stdClass;
use wpdb;

/**
 * Base Repository class for database operations.
 *
 * @package BruteFort
 */
class BaseRepository implements BaseInterface {
	/**
	 * Database table name.
	 *
	 * @var string
	 */
	protected string $table;

	/**
	 * Return global wpdb instance.
	 *
	 * @return wpdb WordPress database instance.
	 */
	public function wpdb(): wpdb {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * Get records from the database with optional filtering and pagination.
	 *
	 * @param array  $conditions Array of conditions for filtering.
	 * @param string $order_by   Column to order by.
	 * @param string $order      Order direction (ASC/DESC).
	 * @param int    $limit      Maximum number of records to return.
	 * @param int    $offset     Number of records to skip.
	 * @param bool   $get_count  Whether to return count instead of records.
	 *
	 * @return array|object|string|null Records or count.
	 */
	public function index( array $conditions = array(), string $order_by = 'ID', string $order = 'DESC', $limit = null, $offset = null, bool $get_count = false ): array|object|string|null {
		global $wpdb;

		$table_name = esc_sql( $this->table );
		$sql        = 'SELECT ' . ( $get_count ? 'COUNT(*)' : '*' ) . " FROM {$table_name}";
		$args          = array();
		$where_clauses = array();

		foreach ( $conditions as $key => $group ) {
			// OR group.
			if ( is_array( $group ) && isset( $group['or'] ) && is_array( $group['or'] ) ) {
				$or_clauses = array();
				foreach ( $group['or'] as $cond ) {
					$column       = esc_sql( $cond['column'] );
					$operator     = $cond['operator'] ?? '=';
					$value        = $cond['value'];
					$or_clauses[] = "$column $operator %s";
					$args[]       = $value;
				}
				$where_clauses[] = '(' . implode( ' OR ', $or_clauses ) . ')';
			} else { // AND group.
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
			$sql .= ' WHERE ' . implode( ' AND ', $where_clauses );
		}

		// ORDER BY - validate and escape order direction.
		$order_direction = strtoupper( $order ) === 'ASC' ? 'ASC' : 'DESC';
		$sql .= ' ORDER BY ' . esc_sql( $order_by ) . ' ' . $order_direction;

		// LIMIT / OFFSET.
		if ( is_numeric( $limit ) ) {
			$sql    .= ' LIMIT %d';
			$args[] = $limit;

			if ( is_numeric( $offset ) ) {
				$sql    .= ' OFFSET %d';
				$args[] = $offset;
			}
		}

		// Always use prepare for consistency with WordPress standards.
		// Ensure $args is always an array, even if empty.
		$args = empty( $args ) ? array() : $args;

		if ( $get_count ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- SQL is built safely and always prepared.
			$response = $wpdb->get_var( $wpdb->prepare( $sql, ...$args ) );
			return $response;
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- SQL is built safely and always prepared.
		$response = $wpdb->get_results( $wpdb->prepare( $sql, ...$args ) );
		return $response;
	}

	/**
	 * Create a new record in the database.
	 *
	 * @param array $data Data to insert.
	 *
	 * @return array|false|mixed|object|stdClass|void Created record or false on failure.
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
	 * Retrieve specific record by ID.
	 *
	 * @param int $id Record ID.
	 *
	 * @return array Record data or empty array if not found.
	 */
	public function retrieve( $id ): array {
		global $wpdb;

		$result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( $this->table ) . ' WHERE ID = %d', $id ), ARRAY_A );

		return ! $result ? array() : $result;
	}

	/**
	 * Update specific record by ID.
	 *
	 * @param int   $id   Record ID.
	 * @param array $data Data to update.
	 *
	 * @return int|bool|mysqli_result|null Number of affected rows or false on failure.
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
	 * Delete multiple records by IDs.
	 *
	 * @param array $ids Array of record IDs to delete.
	 *
	 * @return int|bool|mysqli_result|null Number of affected rows or false on failure.
	 */
	public function delete_multiple( $ids ): int|bool|null|mysqli_result {
		global $wpdb;

		// Use individual delete operations to avoid interpolation issues.
		$deleted_count = 0;
		foreach ( $ids as $id ) {
			$deleted_count += $wpdb->delete( $this->table, array( 'ID' => $id ) );
		}
		return $deleted_count;
	}

	/**
	 * Delete single record by ID.
	 *
	 * @param int $id Record ID to delete.
	 *
	 * @return int|bool|mysqli_result|null Number of affected rows or false on failure.
	 */
	public function delete( $id ): int|bool|null|mysqli_result {
		global $wpdb;

		return $wpdb->delete( $this->table, array( 'ID' => $id ) );
	}
}

<?php
/**
 * BaseInterface
 * BruteFort\Interfaces
 * @author   Yoyal Limbu
 * @date     18-05-2025 : 10:00 PM
 */
namespace BruteFort\Interfaces;


interface BaseInterface {

	public function index( array $conditions = [], string $order_by = "ID", string $order = "DESC" , $limit = null, $offset = null, bool $get_count = false );
	/**
	 * Create a new entry in the database.
	 *
	 * @param mixed $data The data to create the entry with.
	 *
	 * @return mixed The created entry.
	 */
	public function create( mixed $data ): mixed;

	/**
	 * Retrieve an entry from the database.
	 *
	 * @param integer $id The id for the entry to retrieve with.
	 *
	 * @return mixed The retrieved entry.
	 */
	public function retrieve( int $id ): mixed;

	/**
	 * Update an entry in the database.
	 *
	 * @param integer $id The id for the entry to retrieve with.
	 * @param mixed   $data The data to create the entry with.
	 *
	 * @return mixed The updated entry.
	 */
	public function update( int $id, mixed $data ): mixed;

	/**
	 * Delete an entry from the database.
	 *
	 * @param integer $id The id for the entry to retrieve with.
	 *
	 * @return bool Returns true on success.
	 */
	public function delete( int $id ): mixed;

	/**
	 * Delete multiple entries from the database.
	 *
	 * @param array $ids An array of ids for the entries to delete.
	 *
	 * @return mixed
	 */
	public function delete_multiple( array $ids ): mixed;
}

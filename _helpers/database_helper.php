<?php

class DatabaseHelper {

	/**
	 * Gets the MySQL type for a type name
	 *
	 * @param type_name The type name
	 * @return The MySQL type
	 */
	public static function getDBType($type_name) {
		$db_types_lookup = [
			'int' => 'INT',
			'string' => 'VARCHAR(1000)',
			'text' => 'TEXT',
			'date' => 'DATE'
		];

		return $db_types_lookup[$type_name];
	}

	/**
	 * Gets the names of all overview columns for a given module and table
	 *
	 * @param module_name The name of the module
	 * @param table_name The name of the table
	 * @return An array containing the names of all overview columns
	 */
	public static function getOverviewColunNames($module_name, $table_name) {
		// Generate the name of the meta table
		$meta_table_name = $module_name.'_meta';

		// Get information from meta table
		$data = Database::getInstance()->select(
			$meta_table_name,
			['field_name'],
			[
				'AND' => [
					'table_name' => $table_name,
					'overview' => true,
					'enabled' => true,
				]
			]
		);

		// Extract column names from database result
		$column_names = [];
		foreach($data as $column) {
			$column_names[] = $column['field_name'];
		}

		return $column_names;
	}

	/**
	 * Gets the data of all overview columns for a given module and table
	 *
	 * @param module_name The name of the module
	 * @param table_name The name of the table
	 * @return An array containing the data of all overview columns
	 */
	public static function getOverviewData($module_name, $table_name) {
		// Get overview columns
		$column_names = DatabaseHelper::getOverviewColunNames($module_name, $table_name);

	    // Get overview data
	    $data_table_name = $module_name.'_'.$table_name;
	    $data = Database::getInstance()->select($data_table_name, $column_names);

	    return $data;
	}

	/**
	 * Gets the data of one database object specified by its module, table and id
	 *
	 * @param module_name The module the object belongs to
	 * @param table_name The table the object belongs to
	 * @param id The object's id
	 * @return The specified database object, or 'false'
	 */
	public static function getObject($module_name, $table_name, $id) {
		$data_table_name = $module_name.'_'.$table_name;
		$data = Database::getInstance()->select($data_table_name, '*', ['id' => $id]);

	    return ($data ? $data[0] : false);
	}

	/**
	 * Deletes a database object specified by its module, table and id
	 *
	 * @param module_name The module the object belongs to
	 * @param table_name The table the object belongs to
	 * @param id The object's id
	 * @return A boolean indicating whether the object was deleted
	 */
	public static function deleteObject($module_name, $table_name, $id) {
		$data_table_name = $module_name.'_'.$table_name;
		$num_rows = Database::getInstance()->delete($data_table_name, ['id' => $id]);

	    return ($num_rows > 0 ? true : false);
	}

	/**
	 * Creates a database object specified by its module, table and data
	 *
	 * @param module_name The module the object belongs to
	 * @param table_name The table the object belongs to
	 * @param data The object's data
	 * @return The id of the newly created object, or 'false'
	 */
	public static function createObject($module_name, $table_name, $data) {
		$data_table_name = $module_name.'_'.$table_name;
		$new_id = Database::getInstance()->insert($data_table_name, $data);

	    return ($new_id > 0 ? $new_id : false);
	}

	/**
	 * Update a database object specified by its module, table, id and new data
	 *
	 * @param module_name The module the object belongs to
	 * @param table_name The table the object belongs to
	 * @param id The object's id
	 * @param data The object's new data
	 * @return A boolean indicating whether the object was updated
	 */
	public static function updateObject($module_name, $table_name, $id, $data) {
		$data_table_name = $module_name.'_'.$table_name;
		$num_rows = Database::getInstance()->update($data_table_name, $data, ['id' => $id]);

	    return ($num_rows > 0 ? true : false);
	}


}

?>
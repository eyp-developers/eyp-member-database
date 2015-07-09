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
			'text' => 'TEXT'
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


}

?>
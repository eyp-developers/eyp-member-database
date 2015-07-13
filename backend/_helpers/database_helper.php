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
	 * Gets the data of multiple database object specified by their module and table
	 *
	 * @param module_name The module the object belongs to
	 * @param table_name The table the object belongs to
	 * @param columns An array with the column names
	 * @return The specified database object, or 'false'
	 */
	public static function getObjects($module_name, $table_name, $columns = false, $search = false, $offset = false, $limit = false, $sort = false, $order = false) {
		$data_table_name = $module_name.'_'.$table_name;
		$where = [];

		// Parse parameters
		if($columns === false) $columns = '*';

		if($offset !== false && $limit !== false) {
			$where['LIMIT'] = [$offset, $limit];
		}

		if($sort !== false && strlen($sort) !== 0 && $order !== false && strlen($order) !== 0) {
			$where['ORDER'] = $sort . ' ' . strtoupper($order);
		}

		if($search !== false && strlen($search) !== 0) {
			$searchclause = [];
			foreach($columns as $field) {
				$searchclause[$field.'[~]'] = $search;
			}
			$where['OR'] = $searchclause;
		}

		$data = Database::getInstance()->select($data_table_name, $columns, $where);

	    return $data;
	}

	/**
	 * Counts database object specified by their module and table
	 *
	 * @param module_name The module the object belongs to
	 * @param table_name The table the object belongs to
	 * @return The number of database objects
	 */
	public static function countObjects($module_name, $table_name, $columns = false, $search = false) {
		$data_table_name = $module_name.'_'.$table_name;

		$where = [];
		if($columns !== false && $search !== false && strlen($search) !== 0) {
			$searchclause = [];
			foreach($columns as $field) {
				$searchclause[$field.'[~]'] = $search;
			}
			$where['OR'] = $searchclause;
		}

		$count = Database::getInstance()->count($data_table_name, $where);

	    return $count;
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

	/**
	 * Gets information about all views of a certain module
	 *
	 * @param module_name The name of the module
	 * @param only_in_sidebar Whether only views that are visible in the sidebar should be returned
	 * @return An array containing information about all views of the module
	 */
	public static function getModuleViews($module_name, $only_in_sidebar = false) {
		// Generate the name of the views table
		$views_table_name = $module_name.'_views';

		// Generate filter
		$filter = [];
		if($only_in_sidebar) {
			$filter["show_in_sidebar"] = true;
		}

		// Get information from views table
		$data = Database::getInstance()->select(
			$views_table_name,
			['view_name', 'view_title'],
			$filter
		);

		return $data;
	}

	/**
	 * Gets the config of a view of a certain module
	 *
	 * @param module_name The name of the module
	 * @param view_name The name of the view
	 * @return An array containing information about the specified view
	 */
	public static function getModuleView($module_name, $view_name) {
		// Generate the name of the meta table
		$views_table_name = $module_name.'_views';

		// Get information from views table
		$data = Database::getInstance()->select(
			$views_table_name,
			['view_config'],
			['view_name' => $view_name]
		);

		return $data[0]['view_config'];
	}

	/**
	 * Gets an array of all modules
	 *
	 * @param include_disabled
	 * @return An array containing information about all modules
	 */
	public static function getAllModules($only_enabled = false) {
		// Generate the name of the meta table
		$modules_table = 'core_modules';

		if($only_enabled) {
			$filter = ['enabled' => true];
		} else {
			$filter = [];
		}

		// Get information from views table
		$data = Database::getInstance()->select(
			$modules_table,
			'*',
			$filter
		);

		return $data;
	}


}

?>
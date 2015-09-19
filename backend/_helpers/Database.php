<?php

namespace Helpers;

class Database {

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
			'short_string' => 'VARCHAR(200)',
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
		$data = \Core\Database::getInstance()->select($data_table_name, '*', ['id' => $id]);

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
	public static function getObjects($module_name, $table_name, $columns = false, $search = false, $where = false, $offset = false, $limit = false, $sort = false, $order = false) {
		$data_table_name = $module_name.'_'.$table_name;
		$filter = [];

		// Parse parameters
		if($columns === false) $columns = '*';

		if($offset !== false && $limit !== false) {
			$filter['LIMIT'] = [$offset, $limit];
		}

		if($sort !== false && strlen($sort) !== 0 && $order !== false && strlen($order) !== 0) {
			$filter['ORDER'] = $sort . ' ' . strtoupper($order);
		}

		if($search !== false && strlen($search) !== 0) {
			$searchclause = [];
			foreach($columns as $field) {
				$searchclause[$field.'[~]'] = $search;
			}
			$filter['AND']['OR'] = $searchclause;
		}

		if($where !== false && strlen($where) !== 0) {
			$whereclause = [];
			$where_parts = explode(',', $where);

			if(!isset($filter['AND'])) {
				$filter['AND'] = [];
			}
			
			foreach($where_parts as $where_part) {
				$where_part = explode('=', $where_part);
				$filter['AND'][$where_part[0]] = $where_part[1];
			}
		}

		$data = \Core\Database::getInstance()->select($data_table_name, $columns, $filter);

	    return $data;
	}

	/**
	 * Counts database object specified by their module and table
	 *
	 * @param module_name The module the object belongs to
	 * @param table_name The table the object belongs to
	 * @return The number of database objects
	 */
	public static function countObjects($module_name, $table_name, $columns = false, $search = false, $where = false) {
		$data_table_name = $module_name.'_'.$table_name;

		$filter = [];

		if($search !== false && strlen($search) !== 0) {
			$searchclause = [];
			foreach($columns as $field) {
				$searchclause[$field.'[~]'] = $search;
			}
			$filter['AND']['OR'] = $searchclause;
		}

		if($where !== false && strlen($where) !== 0) {
			$whereclause = [];
			$where_parts = explode(',', $where);

			if(!isset($filter['AND'])) {
				$filter['AND'] = [];
			}
			
			foreach($where_parts as $where_part) {
				$where_part = explode('=', $where_part);
				$filter['AND'][$where_part[0]] = $where_part[1];
			}
		}

		$count = \Core\Database::getInstance()->count($data_table_name, $filter);

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
		$num_rows = \Core\Database::getInstance()->delete($data_table_name, ['id' => $id]);

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
		$new_id = \Core\Database::getInstance()->insert($data_table_name, $data);

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
		$num_rows = \Core\Database::getInstance()->update($data_table_name, $data, ['id' => $id]);

	    return ($num_rows > 0 ? true : false);
	}

	/**
	 * Gets information about all views of a certain module
	 *
	 * @param module_name The name of the module
	 * @param only_in_sidebar Whether only views that are visible in the sidebar should be returned
	 * @param filter_for_user Whether only views that are visible to the user should be returned
	 * @return An array containing information about all views of the module
	 */
	public static function getModuleViews($module_name, $only_in_sidebar = false, $filter_for_user = false) {
		if($filter_for_user && !\Core\User::getInstance()->canReadModule($module_name)) {
			return [];
		}

		// Generate filter
		$filter = [
			'AND' => [
				'module_name' => $module_name
			]
		];

		if($only_in_sidebar) {
			$filter['AND']['in_sidebar'] = $only_in_sidebar;
		}

		// Get information from views table
		$views = \Core\Database::getInstance()->select(
			'core_views',
			['name', 'title', 'does_edit'],
			$filter
		);

		if($filter_for_user) {
			if(!\Core\User::getInstance()->canWriteModule($module_name)) {
                $read_views = [];
                foreach($views as $view) {
                    if(!$view['does_edit']) {
                        $read_views[] = $view;
                    }
                }
                $views = $read_views;
            }
		}

		return $views;
	}

	/**
	 * Gets the config of a view of a certain module
	 *
	 * @param module_name The name of the module
	 * @param view_name The name of the view
	 * @return An array containing information about the specified view
	 */
	public static function getModuleView($module_name, $view_name) {
		// Get information about the view
		$view = \Core\Database::getInstance()->select(
			'core_views',
			['title', 'type', 'container', 'datasource', 'does_edit'],
			['AND' =>
				[
					'module_name' => $module_name,
					'name' => $view_name 
				]
			]
		);
		if(is_array($view) && count($view) >= 1) {
			$view = $view[0];
		} else {
			// TODO: Report error via exception
			die("ERROR");
		}

		$fields = \Core\Database::getInstance()->select(
			'core_views_fields',
			[
				'name',
				'data_key',
				'title',
				'type',
				'target',
				'icon',
				'visible',
				'store_module',
				'store_name'
			],
			[
				'AND' =>
					[
						'module_name' => $module_name,
						'view_name' => $view_name,
						'enabled' => true
					],
				'ORDER' => 'view_order ASC'
			]
		);

		// Parse information
		$view['fields'] = $fields;

		return $view;
	}

	/**
	 * Gets an array of all modules
	 *
	 * @param include_disabled
	 * @return An array containing information about all modules
	 */
	public static function getAllModules($only_enabled = false) {
		// Generate filter
		if($only_enabled) {
			$filter = ['enabled' => true];
		} else {
			$filter = [];
		}

		// Get information from views table
		$data = \Core\Database::getInstance()->select(
			'core_modules',
			'*',
			$filter
		);

		return $data;
	}

	/**
	 * Gets a store of a certain module
	 *
	 * @param module_name The name of the module
	 * @param store_name The name of the store
	 * @return An array containing a specific store
	 */
	public static function getModuleStore($module_name, $store_name) {
		// Get information about the store
		$store_config = \Core\Database::getInstance()->select(
			'core_stores',
			['name', 'module_name', 'model_name', 'data_key', 'value'],
			['AND' =>
				[
					'module_name' => $module_name,
					'name' => $store_name 
				]
			]
		);

		if(is_array($store_config) && count($store_config) >= 1) {
			$store_config = $store_config[0];
		} else {
			// TODO: Report error via exception
			die("ERROR");
		}

		$data_table = $store_config['module_name'] . '_' . $store_config['model_name'];
		$data = \Core\Database::getInstance()->select(
			$data_table,
			[
				$store_config['data_key'],
				$store_config['value']
			],
			[
				'ORDER' => $store_config['value'].' ASC'
			]
		);

		// Parse information
		$data_dict = [];
		foreach($data as $row) {
			$data_dict[$row[$store_config['data_key']]] = $row[$store_config['value']];
		}
		$store_config['data'] = $data_dict;

		return $store_config;
	}

	/**
	 * Gets an array of all stores
	 *
	 * @return An array containing information about all stores
	 */
	public static function getAllStores() {
		// Get information from stores table
		$data = \Core\Database::getInstance()->select(
			'core_stores',
			['module_name', 'name']
		);

		return $data;
	}


}

?>
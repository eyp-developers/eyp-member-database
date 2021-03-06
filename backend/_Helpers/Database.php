<?php

namespace Helpers;

/**
 * A helper class to perform the most common database actions
 */
class Database {

	/**
	 * @var {array} $_db_types_lookup An array mapping type names to MySQL types
	 */
	private static $_db_types_lookup = [
		'int' => 'INT',
		'string' => 'VARCHAR(1000)',
		'short_string' => 'VARCHAR(200)',
		'text' => 'TEXT',
		'date' => 'DATE',
		'datetime' => 'DATETIME',
		'decimal' => "DECIMAL(12,2)",
		'file' => 'VARCHAR(200)'
	];

	/**
	 * Gets the MySQL type for a type name
	 *
	 * @param {string} $type_name The name of the type
	 * @return {string} The corresponding MySQL type
	 */
	public static function getDBType($type_name) {
		return static::$_db_types_lookup[$type_name];
	}

	/**
	 * Gets the data of one database object specified by its module, table and id
	 *
	 * @param {string} $module_name The module the object belongs to
	 * @param {string} $table_name The table the object belongs to
	 * @param {int/string} $id The object's id
	 * @param {string} $id_property The id property. Defaults to 'id'.
	 * @return {array/boolean} The specified database object, or false if no object coult be found
	 */
	public static function getObject($module_name, $table_name, $id, $id_property = 'id') {
		$data_table_name = $module_name.'_'.$table_name;
		$data = \Core\Database::getInstance()->select($data_table_name, '*', [$id_property => $id]);

	    return ($data ? $data[0] : false);
	}

	/**
	 * Gets the (paginated) data of multiple database object specified by their module and table
	 *
	 * @param {string} $module_name The module the object belongs to
	 * @param {string} $table_name The table the object belongs to
	 * @param {string} $columns An array with the column names
	 * @param {string} $search A string to search for
	 * @param {where} $where Additional filter conditions
	 * @return {array/boolean} The specified database object, or false if no object could be found
	 */
	public static function getObjects($module_name, $table_name, $columns = false, $search = false, $where = false, $offset = false, $limit = false, $sort = false, $order = false, $filter = false) {
		$data_table_name = $module_name.'_'.$table_name;
		$sql_filter = [];

		// Parse parameters
		if($columns === false) $columns = '*';

		if($offset !== false && $limit !== false) {
			$sql_filter['LIMIT'] = [$offset, $limit];
		}

		if($sort !== false && strlen($sort) !== 0 && $order !== false && strlen($order) !== 0) {
			$sql_filter['ORDER'] = $sort . ' ' . strtoupper($order);
		}

		if($filter !== false && is_array($filter) && count($filter) >= 0) {

			$sql_filter['AND'] = array();

			foreach($filter as $filter_key => $filter_value) {
				$sql_filter['AND'][$filter_key.'[~]'] = $filter_value;
			}

		} else {

			if($search !== false && strlen($search) !== 0) {
				$searchclause = [];
				foreach($columns as $field) {
					$searchclause[$field.'[~]'] = $search;
				}
				$sql_filter['AND']['OR'] = $searchclause;
			}
		}

		if($where !== false && strlen($where) !== 0) {
			$whereclause = [];
			$where_parts = explode(',', $where);

			if(!isset($sql_filter['AND'])) {
				$sql_filter['AND'] = [];
			}
			
			foreach($where_parts as $where_part) {
				$where_part = explode('=', $where_part);
				$sql_filter['AND'][$where_part[0]] = $where_part[1];
			}
		}

		// Get data
		$data = \Core\Database::getInstance()->select($data_table_name, $columns, $sql_filter);

	    return $data;
	}

	/**
	 * Counts database object specified by their module and table
	 *
	 * @param {string} $module_name The module the object belongs to
	 * @param {string} $table_name The table the object belongs to
	 * @param {string} $columns An array with the column names
	 * @param {string} $search A string to search for
	 * @param {where} $where Additional filter conditions
	 * @return The specified database object, or 'false'
	 */
	public static function countObjects($module_name, $table_name, $columns = false, $search = false, $where = false, $filter = false) {
		$data_table_name = $module_name.'_'.$table_name;

		$sql_filter = [];

		if($filter !== false && is_array($filter) && count($filter) >= 0) {
			$sql_filter['AND'] = array();

			foreach($filter as $filter_key => $filter_value) {
				$sql_filter['AND'][$filter_key.'[~]'] = $filter_value;
			}
		} else {

			if($search !== false && strlen($search) !== 0) {
				$searchclause = [];
				foreach($columns as $field) {
					$searchclause[$field.'[~]'] = $search;
				}
				$sql_filter['AND']['OR'] = $searchclause;
			}

			if($where !== false && strlen($where) !== 0) {
				$whereclause = [];
				$where_parts = explode(',', $where);

				if(!isset($sql_filter['AND'])) {
					$sql_filter['AND'] = [];
				}
				
				foreach($where_parts as $where_part) {
					$where_part = explode('=', $where_part);
					$sql_filter['AND'][$where_part[0]] = $where_part[1];
				}
			}
		}

		$count = \Core\Database::getInstance()->count($data_table_name, $sql_filter);

	    return $count;
	}

	/**
	 * Deletes a database object specified by its module, table and id
	 *
	 * @param {string} $module_name The module the object belongs to
	 * @param {string} $table_name The table the object belongs to
	 * @param {string} $id The object's id
	 * @param {string} $id_property The id property. Defaults to 'id'.
	 * @return {boolean} Whether the object was deleted
	 */
	public static function deleteObject($module_name, $table_name, $id, $id_property = 'id') {
		$data_table_name = $module_name.'_'.$table_name;
		$num_rows = \Core\Database::getInstance()->delete($data_table_name, [$id_property => $id]);

	    return ($num_rows > 0 ? true : false);
	}

	/**
	 * Creates a database object specified by its module, table and data
	 *
	 * @param {string} $module_name The module the object belongs to
	 * @param {string} $table_name The table the object belongs to
	 * @param {array} $data The new object's data
	 * @param {array} &$invalid_field An array that will be populated with the names of all invalid fields
	 * @return {int/boolean} The id of the newly created object, or false
	 */
	public static function createObject($module_name, $table_name, $data, &$invalid_fields = []) {
		// Validate data
		$validate_result = \Helpers\Validator::validateDataForModuleAndModel($data, $module_name, $table_name, $invalid_fields);
		if(!$validate_result) {
			return false;
		}

		$data_table_name = $module_name.'_'.$table_name;
		$new_id = \Core\Database::getInstance()->insert($data_table_name, $data);

	    return (($new_id > 0) || !is_int($new_id) ? $new_id : false);
	}

	/**
	 * Update a database object specified by its module, table, id and new data
	 *
	 * @param {string} $module_name The module the object belongs to
	 * @param {string} $table_name The table the object belongs to
	 * @param {string} $id The object's id
	 * @param {array} $data The object's new data
	 * @param {string} $id_property The id property. Defaults to 'id'.
	 * @param {array} &$invalid_field An array that will be populated with the names of all invalid fields
	 * @return {boolean} Whether the object was updated
	 */
	public static function updateObject($module_name, $table_name, $id, $data, $id_property = 'id', &$invalid_fields = []) {
		// Validate data
		$validate_result = \Helpers\Validator::validateDataForModuleAndModel($data, $module_name, $table_name, $invalid_fields);
		if(!$validate_result) {
			return false;
		}

		$data_table_name = $module_name.'_'.$table_name;
		$num_rows = \Core\Database::getInstance()->update($data_table_name, $data, [$id_property => $id]);

	    return true;
	}

	/**
	 * Gets information about all views of a certain module
	 *
	 * @param {string} $module_name The name of the module
	 * @param {boolean} $only_in_sidebar Whether only views that are visible in the sidebar should be returned. Defaults to false.
	 * @param {boolean} $filter_for_user Whether only views that are visible to the user should be returned. Defaults to false.
	 * @return {array} Information about all views of the module
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
			['name', 'title', 'icon', 'does_edit'],
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
	 * @param {string} $module_name The name of the module
	 * @param {string} $view_name The name of the view
	 * @return {array/boolean} Information about the specified view, or false if the view could not be found
	 */
	public static function getModuleView($module_name, $view_name) {
		// Get information about the view
		$view = \Core\Database::getInstance()->select(
			'core_views',
			['title', 'type', 'container', 'datasource', 'load_data', 'does_edit', 'show_title', 'header_button_text', 'header_button_icon', 'header_button_target'],
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
			return false;
		}

		$fields = \Core\Database::getInstance()->select(
			'core_views_fields',
			[
				'name',
				'data_key',
				'title',
				'placeholder',
				'type',
				'target',
				'icon',
				'visible',
				'required',
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
	 * @param {boolean} $only_enabled Whether only enabled modules should be returned. Defaults to false.
	 * @return {array} Information about all modules
	 */
	public static function getAllModules($only_enabled = false) {
		// Generate filter
		$filter = ['ORDER' => 'view_order ASC'];
		if($only_enabled) {
			$filter['enabled'] = true;
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
	 * Gets all stores of a certain module
	 *
	 * @param {string} $module_name The name of the module
	 * @return {array/false} The store data, or false
	 */
	public static function getModuleStores($module_name) {
		// Get information about the store
		$store_configs = \Core\Database::getInstance()->select(
			'core_stores',
			['name', 'module_name', 'model_name', 'data_key', 'value'],
			['AND' =>
				[
					'module_name' => $module_name
				]
			]
		);

		if(!is_array($store_configs) || count($store_configs) == 0) {
			return false;
		}

		for($i = 0; $i < count($store_configs); $i++) {

			// The exportable store is not in the database - it has to be calculated
			if($store_configs[$i]['name'] === 'exportable') {

				if($store_configs[$i]['module_name'] === 'modules') {
					$exportable_classes = array();

					foreach(\Core\App::$_modules as $module_name => $module) {
						if($module instanceof \Core\Exportable) {
							$exportable_classes[$module->getLCName()] = $module->getName();
						}
					}

					$store_configs[$i]['data'] = $exportable_classes;
				} else {
					$module_obj = \Core\App::$_modules[$module_name];
					$fields = $module_obj->getExportFields();
					$store_configs[$i]['data'] = $fields;
				}

			} else {

				$data_table = $store_configs[$i]['module_name'] . '_' . $store_configs[$i]['model_name'];
				$data = \Core\Database::getInstance()->select(
					$data_table,
					[
						$store_configs[$i]['data_key'],
						$store_configs[$i]['value']
					],
					[
						'ORDER' => $store_configs[$i]['value'].' ASC'
					]
				);

				// Parse information
				$data_dict = [];
				foreach($data as $row) {
					$data_dict[$row[$store_configs[$i]['data_key']]] = $row[$store_configs[$i]['value']];
				}
				$store_configs[$i]['data'] = $data_dict;
			}
		}

		return $store_configs;
	}

	/**
	 * Gets a store of a certain module
	 *
	 * @param {string} $module_name The name of the module
	 * @param {string} $store_name The name of the store
	 * @return {array/false} The store data, or false
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
			return false;
		}

		// The exportable store is not in the database - it has to be calculated
		if($store_name === 'exportable') {

			if($module_name === 'modules') {
				$exportable_classes = array();

				foreach(\Core\App::$_modules as $module_name => $module) {
					if($module instanceof \Core\Exportable) {
						$exportable_classes[$module->getLCName()] = $module->getName();
					}
				}

				$store_config['data'] = $exportable_classes;
			} else {
				$module_obj = \Core\App::$_modules[$module_name];
				$fields = $module_obj->getExportFields();
				$store_config['data'] = $fields;
			}

		} else {

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
		}

		return $store_config;
	}

	/**
	 * Gets an array of all stores
	 *
	 * @return {array} Information about all stores
	 */
	public static function getAllStores() {
		// Get information from stores table
		$data = \Core\Database::getInstance()->select(
			'core_stores',
			['module_name', 'name']
		);

		return $data;
	}

	/**
	 * Gets a model of a certain module
	 *
	 * @param {string} $module_name The name of the module
	 * @param {string} $model_name The name of the model
	 * @return {array/false} The store data, or false
	 */
	public static function getModuleModel($module_name, $model_name) {
		// Get information about the store
		$model_fields = \Core\Database::getInstance()->select(
			'core_models_fields',
			'*',
			['AND' =>
				[
					'module_name' => $module_name,
					'model_name' => $model_name 
				]
			]
		);

		$model = [];
		if(is_array($model_fields) && count($model_fields) >= 1) {
			foreach($model_fields as $field) {
				$model[$field['name']] = [
					'type' => $field['type'],
					'required' => $field['required'],
					'generated' => $field['generated']
				];
			}
		} else {
			return false;
		}

		return $model;
	}

}

?>
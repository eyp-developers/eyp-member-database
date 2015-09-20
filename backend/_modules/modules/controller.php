<?php

namespace Modules;

/**
 * The Modules module
 */
class Modules extends \Core\Module {

    /**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Set supported actions
        $this->_actions = [
            'GET' => [
                '/' => 'index',
                '/installed' => 'installedModules',
                '/available' => 'availableModules',
                '/:module_name/views' => 'moduleViews',
                '/:module_name/views/:view_name' => 'moduleView',
                '/:module_name/stores/:store_name' => 'moduleStore'
            ],

            'POST' => [
                '/install/:folder_name' => 'install',
                '/setup' => 'setup'
            ],

            'DELETE' => [
                '/:module_name' => 'delete'
            ]
        ];
    }

    /**
     * Returns all installed modules
     *
     * @return void
     */
    public function index() {
        // Get all modules
        $modules = \Helpers\Database::getAllModules();

        // Send response
        \Helpers\Response::success($modules);
    }

    /**
     * Returns all installed modules
     *
     * @return void
     */
    public function installedModules() {
        // Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = \Core\App::getInstance()->request->get("where");

        // Exclude system modules
        if(strlen($where) > 0) {
            $where .= ',';
        }
        $where .= "system=0";

        // Get the data
        $data = \Helpers\Database::getObjects('core', 'modules', $fields, $search, $where, $offset, $limit, $sort, $order);
        $count = \Helpers\Database::countObjects('core', 'modules', $fields, $search, $where);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    /**
     * Returns all available modules
     *
     * @return void
     */
    public function availableModules() {
        // Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");

        $data = [];

        // Get all installed
        $installed_modules = \Core\Database::getInstance()->select('core_modules', 'name');

        // Get all module folders
        $module_dir_name = '_Modules';
        $dir = dir($module_dir_name);
        $child_name = readdir($dir->handle);

        while($child_name !== false) {

            // Make sure we have a non-hidden directory
            if(stripos($child_name, '.') !== 0 && is_dir($module_dir_name . '/' . $child_name)) {

                // Try to read the module info
                $module_info = \Helpers\Module::getModuleInfo($child_name);

                if($module_info !== false) {
                    if(array_search($module_info['name'], $installed_modules) === false) {
                        array_push($data, $module_info);
                    }
                }
            }
            
            // Get next child 
            $child_name = readdir($dir->handle);
        }

        // TODO: filter, sort, etc.

        // Send response
        \Helpers\Response::success([
            'total' => count($data),
            'rows' => $data
        ]);
    }

    /**
     * Installs a given module
     *
     * @param {string} $folder_name The name of the module's folder
     * @return void
     */
    public function install($folder_name) {
        $db = \Core\Database::getInstance();

        // Read module information from its json files
        $module_info = \Helpers\Module::getModuleInfo($folder_name);
        $model = \Helpers\Module::getModuleModel($folder_name);
        $views = \Helpers\Module::getModuleViews($folder_name);
        $stores = \Helpers\Module::getModuleStores($folder_name);
        $data = \Helpers\Module::getModuleData($folder_name);

        // Make sure we have at least the minimum required information about the module
        if($module_info === false || !isset($module_info['name']) || !isset($module_info['version'])) {
            \Helpers\Response::error(E_MISSING_MODULE_INFO);
            return;
        }

        // Check dependencies
        if(isset($module_info['dependencies'])) {
            $installed_modules = \Core\Database::getInstance()->select('core_modules', 'name');
            $missing_dependencies = [];

            foreach($module_info['dependencies'] as $dep_module_name) {
                if(array_search($dep_module_name, $installed_modules) === false) {
                    array_push($missing_dependencies, $dep_module_name);
                }
            }

            if(count($missing_dependencies) > 0) {
                \Helpers\Response::error(\Helpers\Response::$E_MISSING_DEPENDENCIES, $missing_dependencies);
                return;
            }
        }

        $module_name = $module_info['name'];
        $module_title = (isset($module_info['title']) ? $module_info['title'] : $module_name);

        // Add an entry to the modules table
        $db->insert('core_modules', [
            'name' => $module_name,
            'title' => $module_title,
            'icon' => (isset($module_info['icon']) ? $module_info['icon'] : null),
            'description' => (isset($module_info['description']) ? $module_info['description'] : ''),
            'version' => $module_info['version'],
            'enabled' => true,
            'min_permission' => (isset($module_info['min_permission']) ? $module_info['min_permission'] : 0),
            'system' => (isset($module_info['system']) ? $module_info['system'] : false),
        ]);

        // Keep track of all foreign keys
        $foreign_key_queue = [];

        // Iterate over all tables of the model
        if($model !== false && isset($model['tables'])) {
            foreach($model['tables'] as $table_name => $fields) {

                // Store model metadata
                $db->insert('core_models', [
                    'module_name' => $module_name,
                    'name' => $table_name
                ]);

                // SQL statement to create a table from the model
                $table_sql = 'CREATE TABLE '.$module_name.'_'.$table_name.' (';

                // Iterate over all fields of the table
                foreach($fields as $field_name => $field_config) {

                    // Set default values if values are not set for $field_config
                    if(!isset($field_config['primary_key'])) $field_config['primary_key'] = false;

                    // Add field name and type
                    $field_type = \Helpers\Database::getDBType($field_config['type']);
                    $table_sql .= $field_name.' '.$field_type;

                    // Check if the field is the table's primary key
                    if($field_config['primary_key']) {
                        $table_sql .= ' PRIMARY KEY';
                        if($field_config['type'] === 'int') {
                            $table_sql .= ' AUTO_INCREMENT';
                        }
                    }

                    // Finish this line
                    $table_sql .= ', ';

                    // Add an entry to the meta table
                    $db->insert('core_models_fields', [
                        'module_name' => $module_name,
                        'model_name' => $table_name,
                        'name' => $field_name,
                        'type' => $field_config['type'],
                        'creator_module_name' => $module_name
                    ]);

                    // Keep track of foreign keys
                    if(isset($field_config['foreign_key'])) {
                        $foreign_key = $field_config['foreign_key'];
                        $target_column = $foreign_key['module'] . '_' . $foreign_key['table'] . '(' . $foreign_key['field'] . ')';
                        $foreign_key_sql = 'ALTER TABLE ' . $module_name . '_' . $table_name . ' ADD FOREIGN KEY (' . $field_name . ') REFERENCES ' . $target_column;
                        $foreign_key_queue[] = $foreign_key_sql;
                    }
                }

                // Remove trailing comma
                $table_sql = substr($table_sql, 0, -2);

                // Finish the statement
                $table_sql .= ');';

                // Create the table
                \Core\Database::getInstance()->query($table_sql);
            }
        }

        // Iterate over all external tables of the model
        if($model !== false && isset($model['external'])) {
            foreach($model['external'] as $ext_module_name => $ext_module_tables) {
                foreach($ext_module_tables as $ext_table_name => $ext_fields) {
                    foreach($ext_fields as $ext_field_name => $ext_field_config) {

                        // Add an entry to the meta table
                        $db->insert('core_models_fields', [
                            'module_name' => $ext_module_name,
                            'model_name' => $ext_table_name,
                            'name' => $ext_field_name,
                            'type' => $ext_field_config['type'],
                            'creator_module_name' => $module_name
                        ]);

                        // Add column
                        $ext_table_name = $ext_module_name . '_' . $ext_table_name;
                        $add_column_sql = 'ALTER TABLE ' . $ext_table_name . ' ADD COLUMN ' . $ext_field_name . ' ' . \Helpers\Database::getDBType($ext_field_config['type']);
                        \Core\Database::getInstance()->query($add_column_sql);

                        // Keep track of foreign keys
                        if(isset($ext_field_config['foreign_key'])) {
                            $foreign_key = $ext_field_config['foreign_key'];
                            $target_column = $foreign_key['module'] . '_' . $foreign_key['table'] . '(' . $foreign_key['field'] . ')';
                            $foreign_key_sql = 'ALTER TABLE ' . $ext_table_name . ' ADD FOREIGN KEY (' . $ext_field_name . ') REFERENCES ' . $target_column;
                            $foreign_key_queue[] = $foreign_key_sql;
                        }
                    }
                }
            }
        }

        // Insert foreign keys
        foreach($foreign_key_queue as $foreign_key_sql) {
            $db->query($foreign_key_sql);
        }


        // Iterate over all views of the module
        if($views !== false && isset($views['views'])) {
            foreach($views['views'] as $view_name => $view_config) {
                // Sanitize view config
                if(!isset($view_config['title'])) $view_config['title'] = null;
                if(!isset($view_config['icon'])) $view_config['icon'] = null;
                if(!isset($view_config['datasource'])) $view_config['datasource'] = null;
                if(!isset($view_config['container'])) $view_config['container'] = null;
                if(!isset($view_config['in_sidebar'])) $view_config['in_sidebar'] = false;
                if(!isset($view_config['does_edit'])) $view_config['does_edit'] = false;

                // Insert view into view table
                $db->insert('core_views', [
                    'module_name' => $module_name,
                    'name' => $view_name,
                    'title' => $view_config['title'],
                    'icon' => $view_config['icon'],
                    'type' => $view_config['type'],
                    'datasource' => $view_config['datasource'],
                    'container' => $view_config['container'],
                    'in_sidebar' => $view_config['in_sidebar'],
                    'does_edit' => $view_config['does_edit']
                ]);

                // Iterate over all fields of the view
                if(isset($view_config['fields'])) {

                    $view_order = 1;
                    foreach($view_config['fields'] as $field_name => $field_config) {

                        $null_fields = ['data_key', 'title', 'type', 'target', 'icon', 'store_module', 'store_name'];
                        foreach($null_fields as $null_field) {
                            if(!isset($field_config[$null_field])) $field_config[$null_field] = null;
                        }
                        if(!isset($field_config['enabled'])) $field_config['enabled'] = 1;
                        if(!isset($field_config['visible'])) $field_config['visible'] = 1;

                        // Add an entry to the meta table
                        $db->insert('core_views_fields', [
                            'module_name' => $module_name,
                            'view_name' => $view_name,
                            'name' => $field_name,
                            'data_key' => $field_config['data_key'],
                            'title' => $field_config['title'],
                            'type' => $field_config['type'],
                            'target' => $field_config['target'],
                            'icon' => $field_config['icon'],
                            'enabled' => $field_config['enabled'],
                            'visible' => $field_config['visible'],
                            'view_order' => $view_order,
                            'store_module' => $field_config['store_module'],
                            'store_name' => $field_config['store_name'],
                            'creator_module_name' => $module_name
                        ]);

                        $view_order++;
                    }
                }
            }
        }

        // Iterate over all external views of the module
        if($views !== false && isset($views['external'])) {
            foreach($views["external"] as $ext_module_name => $ext_views) {
                foreach($ext_views as $ext_view_name => $ext_view_config) {

                    // Get current hightest view order
                    $view_order = $db->max(
                        'core_views_fields',
                        'view_order',
                        [
                            'AND' => [
                                'module_name' => $ext_module_name,
                                'view_name' => $ext_view_name,
                            ]
                        ]
                    );
                    $view_order++;

                    foreach($ext_view_config as $field_name => $field_config) {

                        $null_fields = ['data_key', 'title', 'type', 'target', 'icon', 'store_module', 'store_name'];
                        foreach($null_fields as $null_field) {
                            if(!isset($field_config[$null_field])) $field_config[$null_field] = null;
                        }
                        if(!isset($field_config['enabled'])) $field_config['enabled'] = 1;
                        if(!isset($field_config['visible'])) $field_config['visible'] = 1;

                        // Add an entry to the meta table
                        $db->insert('core_views_fields', [
                            'module_name' => $ext_module_name,
                            'view_name' => $ext_view_name,
                            'name' => $field_name,
                            'data_key' => $field_config['data_key'],
                            'title' => $field_config['title'],
                            'type' => $field_config['type'],
                            'target' => $field_config['target'],
                            'icon' => $field_config['icon'],
                            'enabled' => $field_config['enabled'],
                            'visible' => $field_config['visible'],
                            'view_order' => $view_order,
                            'store_module' => $field_config['store_module'],
                            'store_name' => $field_config['store_name'],
                            'creator_module_name' => $module_name
                        ]);

                        $view_order++;
                    }
                }
            }
        }


        // Iterate over all stores of the module
        $new_stores = [];
        if($stores !== false && isset($stores['stores'])) {
            foreach($stores['stores'] as $store_name => $store_config) {
                // Add an entry to the stores table
                $db->insert('core_stores', [
                    'name' => $store_name,
                    'module_name' => $store_config['module_name'],
                    'model_name' => $store_config['model_name'],
                    'data_key' => $store_config['data_key'],
                    'value' => $store_config['value'],
                ]);

                $new_stores[] = [
                    'module_name' => $store_config['module_name'],
                    'model_name' => $store_name
                ];
            }
        }

        // Iterate over all data of the module
        if($data !== false) {
            foreach($data as $module_name => $module_tables) {
                foreach($module_tables as $table_name => $table_data) {
                    foreach($table_data as $row) {
                        $db->insert($module_name . '_' . $table_name, $row);
                    }
                }
            }
        }

        // Set default permissions for this module
        $db->query("CALL proc_createPermissionsForModule('$folder_name');");

        // Add settings entry
        if(!isset($module_info['min_permission']) || $module_info['min_permission'] <= 1) {
            $db->insert('core_views_fields', [
                'module_name' => 'settings',
                'view_name' => 'edit_user',
                'name' => "permission_$folder_name",
                'data_key' => "permission_$folder_name",
                'title' => "Permission - $module_title",
                'type' => 'select',
                'view_order' => 99,
                'store_module' => 'settings',
                'store_name' => 'permission_names',
                'creator_module_name' => $folder_name
            ]);
        }

        // Send response
        \Helpers\Response::success(
            false,
            $new_stores,
            true
        );
    }

    /**
     * Deletes a given module
     *
     * @param {string} $folder_name The name of the module's folder
     * @return void
     */
    public function delete($folder_name) {
        $db = \Core\Database::getInstance();

        // Check dependencies
        $installed_modules = \Core\Database::getInstance()->select('core_modules', 'name');
        $dependent_modules = [];

        foreach($installed_modules as $dep_module_name) {
            $dep_module_info = \Helpers\Module::getModuleInfo($dep_module_name);
            if(isset($dep_module_info['dependencies']) &&
               array_search($folder_name, $dep_module_info['dependencies']) !== false) {
                array_push($dependent_modules, $dep_module_name);
            }
        }

        if(count($dependent_modules) > 0) {
            \Helpers\Response::error(E_EXISTING_DEPENDENCIES, $dependent_modules);
            return;
        }

        // Remove external fields
        $external_fields = $db->select('core_models_fields', '*',
            [
                'AND' => [
                    'module_name[!]' => $folder_name,
                    'creator_module_name' => $folder_name
                ]
            ]);

        foreach($external_fields as $field) {
            $target_table = $field['module_name'] . '_' . $field['model_name'];
            $schema_name = \Core\Config::$db_connection['database_name'];
            $column_name = $field['name'];

            // Remove foreign key
            $foreign_keys = $db->query(
                "SELECT constraint_name AS name ".
                "FROM information_schema.KEY_COLUMN_USAGE ".
                "WHERE table_schema='$schema_name' ".
                "AND table_name='$target_table' ".
                "AND constraint_name!='PRIMARY' ".
                "AND referenced_table_schema = '$schema_name' ".
                "AND column_name = '$column_name' ".
                "AND referenced_table_name IS NOT NULL ".
                "AND referenced_column_name IS NOT NULL"
            );

            foreach($foreign_keys as $foreign_key) {
                $foreign_key_name = $foreign_key['name'];
                $db->query(
                    "ALTER TABLE `$target_table` ".
                    "DROP FOREIGN KEY `$foreign_key_name`"
                );
            }

            // Remove indices
            $db->query(
                "ALTER TABLE `$target_table` ".
                "DROP INDEX `$column_name`"
            );

            // Remove colum 
            $db->query(
                "ALTER TABLE `$target_table` ".
                "DROP COLUMN `$column_name`"
            );
        }
    
        // Remove model meta information
        $db->delete('core_models_fields',
            [
                'OR' => [
                    'module_name' => $folder_name,
                    'creator_module_name' => $folder_name
                ]
            ]);
        $db->delete('core_models', ['module_name' => $folder_name]);

        // Remove view meta information
        $db->delete('core_views_fields',
            [
                'OR' => [
                    'module_name' => $folder_name,
                    'creator_module_name' => $folder_name
                ]
            ]);
        $db->delete('core_views', ['module_name' => $folder_name]);

        // Remove stores
        $db->delete('core_stores', ['module_name' => $folder_name]);

        // Remove all permissions
        $db->delete('core_users_permissions', ['module_name' => $folder_name]);

        // Remove entry from modules table
        $db->delete('core_modules', ['name' => $folder_name]);

        // Find all tables from the module
        $tables = $db->query('SELECT table_name FROM information_schema.tables WHERE table_name LIKE \''.$folder_name.'_%\';');

        // Remove all tables from the module
        foreach($tables as $table) {
            $db->query('DROP TABLE '.$table['table_name']);
        }

        // Send response
        \Helpers\Response::success(false, false, true);
    }

    /**
     * Returns all views of a given module
     *
     * @param {string} $module_name The name of the module
     * @return void
     */
    public function moduleViews($module_name) {
        // Only return views the user could actually use with their permissions
        if(\Core\User::getInstance()->canReadModule($module_name)) {
            \Helpers\Response::success(\Helpers\Database::getModuleViews($module_name));
        } else {
            \Helpers\Response::error(\Helpers\Response::$E_MISSING_PERMISSION);
        }
    }

    /**
     * Returns a view of a given module
     *
     * @param {string} $module_name The name of the module
     * @param {string} $view_name The name of the view
     * @return void
     */
    public function moduleView($module_name, $view_name) {
        // Only return views the user could actually use with their permissions
        if(\Core\User::getInstance()->canReadModule($module_name)) {
            
            $view = \Helpers\Database::getModuleView($module_name, $view_name);

            if($view['does_edit'] && !\Core\User::getInstance()->canWriteModule($module_name)) {
                \Helpers\Response::error(\Helpers\Response::$E_MISSING_PERMISSION);
            } else {
                \Helpers\Response::success($view);
            }
        } else {
            \Helpers\Response::error(\Helpers\Response::$E_MISSING_PERMISSION);
        }
    }

    /**
     * Returns a store of a given module
     *
     * @param {string} $module_name The name of the module
     * @param {string} $store The name of the store
     * @return void
     */
    public function moduleStore($module_name, $store_name) {
        $store = \Helpers\Database::getModuleStore($module_name, $store_name);

        if($store === false) {
            \Helpers\Response::error();
        } else {
            \Helpers\Response::success($store);
        }
    }

    /**
     * Sets up core modules
     *
     * @return void
     */
    public function setup() {
        $system_modules = ['settings', 'me'];

        foreach($system_modules as $module) {

            // Install core modules
            ob_start();
            $this->install($module);
            $result = json_decode(ob_get_contents(), true);
            ob_end_clean();

            if(!$result['success']) {
                echo $result;
                return;
            }
        }

        \Helpers\Response::success();
    }

}

?>
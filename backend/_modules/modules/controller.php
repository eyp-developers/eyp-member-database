<?php

namespace Modules;

class Modules extends \Core\Module {

    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Set supported actions
        $this->_actions = [
            'GET' => [
                '/modules' => 'index',
                '/modules/install/:folder_name' => 'install',
                '/modules/delete/:folder_name' => 'delete',
                '/modules/:module_name/views' => 'moduleViews',
                '/modules/:module_name/views/:view_name' => 'moduleView'
            ]
        ];
    }

    public function index() {
        // Get all modules
        $modules = \Helpers\Database::getAllModules();

        // Return result
        echo json_encode($modules);
    }

    public function install($folder_name) {
        $return = [];

        // Read module information from its json files
        $module_info = \Helpers\Module::getModuleInfo($folder_name);
        $model = \Helpers\Module::getModuleModel($folder_name);
        $views = \Helpers\Module::getModuleViews($folder_name);

        if($module_info === false || $model === false) {
            echo json_encode(['success' => false, 'data' => $module_info]);
            return;
        }

        if(!isset($module_info['short_name']) || !isset($module_info['version'])) {
            echo json_encode(['success' => false, 'data' => $module_info]);
            return;
        }

        $module_name = $module_info['short_name'];

        // Add an entry to the modules table
        \Core\Database::getInstance()->insert('core_modules', [
            'name' => $module_name,
            'title' => (isset($module_info['long_name']) ? $module_info['long_name'] : ''),
            'description' => (isset($module_info['description']) ? $module_info['description'] : ''),
            'version' => $module_info['version'],
            'enabled' => true
        ]);

        // Keep track of all foreign keys
        $foreign_key_queue = [];

        // Iterate over all tables of the model
        if(isset($model['tables'])) {
            foreach($model['tables'] as $table_name => $fields) {

                // Store model metadata
                \Core\Database::getInstance()->insert('core_models', [
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
                        $table_sql .= ' PRIMARY KEY AUTO_INCREMENT';
                    }

                    // Finish this line
                    $table_sql .= ', ';

                    // Add an entry to the meta table
                    \Core\Database::getInstance()->insert('core_models_fields', [
                        'module_name' => $module_name,
                        'model_name' => $table_name,
                        'name' => $field_name,
                        'type' => $field_config['type']
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
        if(isset($model['external'])) {
            foreach($model['external'] as $ext_module_name => $ext_module_tables) {
                foreach($ext_module_tables as $ext_table_name => $ext_fields) {
                    foreach($ext_fields as $ext_field_name => $ext_field_config) {

                        // Add an entry to the meta table
                        \Core\Database::getInstance()->insert('core_models_fields', [
                            'module_name' => $ext_module_name,
                            'model_name' => $ext_table_name,
                            'name' => $ext_field_name,
                            'type' => $ext_field_config['type']
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
            \Core\Database::getInstance()->query($foreign_key_sql);
        }


        // Iterate over all views of the module
        foreach($views["views"] as $view_name => $view_config) {
            // Sanitize view config
            if(!isset($view_config['title'])) $view_config['title'] = null;
            if(!isset($view_config['datasource'])) $view_config['datasource'] = null;
            if(!isset($view_config['in_sidebar'])) $view_config['in_sidebar'] = false;

            // Insert view into view table
            \Core\Database::getInstance()->insert('core_views', [
                'module_name' => $module_name,
                'name' => $view_name,
                'title' => $view_config['title'],
                'type' => $view_config['type'],
                'datasource' => $view_config['datasource'],
                'in_sidebar' => $view_config['in_sidebar']
            ]);

            // Iterate over all fields of the view
            if(isset($view_config['fields'])) {

                $view_order = 1;
                foreach($view_config['fields'] as $field_name => $field_config) {

                    $null_fields = ['data_key', 'title', 'type', 'target', 'icon'];
                    foreach($null_fields as $null_field) {
                        if(!isset($field_config[$null_field])) $field_config[$null_field] = null;
                    }
                    if(!isset($field_config['enabled'])) $field_config['enabled'] = 1;
                    if(!isset($field_config['visible'])) $field_config['visible'] = 1;

                    // Add an entry to the meta table
                    \Core\Database::getInstance()->insert('core_views_fields', [
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
                        'view_order' => $view_order
                    ]);

                    $view_order++;
                }
            }
        }

        // Iterate over all external views of the module
        if(isset($views['external'])) {
            foreach($views["external"] as $ext_module_name => $ext_views) {
                foreach($ext_views as $ext_view_name => $ext_view_config) {

                    // Get current hightest view order
                    $view_order = \Core\Database::getInstance()->max(
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

                        $null_fields = ['data_key', 'title', 'type', 'target', 'icon'];
                        foreach($null_fields as $null_field) {
                            if(!isset($field_config[$null_field])) $field_config[$null_field] = null;
                        }
                        if(!isset($field_config['enabled'])) $field_config['enabled'] = 1;
                        if(!isset($field_config['visible'])) $field_config['visible'] = 1;

                        // Add an entry to the meta table
                        \Core\Database::getInstance()->insert('core_views_fields', [
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
                            'view_order' => $view_order
                        ]);

                        $view_order++;
                    }
                }
            }
        }

        // Return result
        $return['success'] = true;
        echo json_encode($return);
    }

    public function delete($folder_name) {
        $db = \Core\Database::getInstance();

        // Remove model meta information
        $db->delete('core_models_fields', ['module_name' => $folder_name]);
        $db->delete('core_models', ['module_name' => $folder_name]);

        // Remove view meta information
        $db->delete('core_views_fields', ['module_name' => $folder_name]);
        $db->delete('core_views', ['module_name' => $folder_name]);

        // Remove entry from modules table
        $db->delete('core_modules', ['name' => $folder_name]);

        // Find all tables from the module
        $tables = $db->query('SELECT table_name FROM information_schema.tables WHERE table_name LIKE \''.$folder_name.'_%\';');

        // Remove all tables from the module
        foreach($tables as $table) {
            $db->query('DROP TABLE '.$table['table_name']);
        }

        // Return result
        $return['success'] = true;
        echo json_encode($return);
    }

    public function moduleViews($module_name) {
        echo json_encode(\Helpers\Database::getModuleViews($module_name));
    }

    public function moduleView($module_name, $view_name) {
        echo json_encode(\Helpers\Database::getModuleView($module_name, $view_name));
    }

}

?>
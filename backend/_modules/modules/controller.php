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
        $module_info = Module::getModuleInfo($folder_name);
        $model = Module::getModuleModel($folder_name);
        $views = Module::getModuleViews($folder_name);

        if($module_info === false || $model === false) {
            echo json_encode(['success' => false, 'data' => $module_info]);
            return;
        }

        if(!isset($module_info['short_name']) || !isset($module_info['version'])) {
            echo json_encode(['success' => false, 'data' => $module_info]);
            return;
        }

        $module_name = $module_info['short_name'];

        // Create the module's meta table
        $meta_table_name = $module_name.'_meta';
        $meta_sql = 'CREATE TABLE '.$meta_table_name.' ( table_name VARCHAR(200), field_name VARCHAR(200), field_type VARCHAR(200), system BOOL)';
        \Core\Database::getInstance()->query($meta_sql);

        $foreign_key_queue = [];

        // Iterate over all tables of the model
        if(isset($model['tables'])) {
            foreach($model['tables'] as $table_name => $fields) {

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
                    \Core\Database::getInstance()->insert($meta_table_name, [
                        'table_name' => $table_name,
                        'field_name' => $field_name,
                        'field_type' => $field_config['type'],
                        'system' => true
                    ]);

                    // Kepp track of foreign keys
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

        // Iterate over all tables of the model
        if(isset($model['external'])) {
            foreach($model['external'] as $ext_module_name => $ext_module_tables) {
                foreach($ext_module_tables as $ext_table_name => $ext_fields) {
                    foreach($ext_fields as $ext_field_name => $ext_field_config) {
                        $ext_table_name = $ext_module_name . '_' . $ext_table_name;
                        $add_column_sql = 'ALTER TABLE ' . $ext_table_name . ' ADD COLUMN ' . $ext_field_name . ' ' . DatabaseHelper::getDBType($ext_field_config['type']);
                        \Core\Database::getInstance()->query($add_column_sql);

                        // Kepp track of foreign keys
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

        // Add an entry to the modules table
        \Core\Database::getInstance()->insert('core_modules', [
            'short_name' => $module_name,
            'long_name' => (isset($module_info['long_name']) ? $module_info['long_name'] : ''),
            'description' => (isset($module_info['description']) ? $module_info['description'] : ''),
            'version' => $module_info['version'],
            'enabled' => true
        ]);

        // Create the module's view table
        $views_table_name = $module_name.'_views';
        $views_sql = 'CREATE TABLE '.$views_table_name.' ( view_name VARCHAR(200) PRIMARY KEY, view_title VARCHAR(200), show_in_sidebar BOOL, view_config TEXT )';
        \Core\Database::getInstance()->query($views_sql);

        // Iterate over all views of the module
        foreach($views as $view_name => $view_config) {
            // Sanitize view config
            if(!isset($view_config['show_in_sidebar'])) $view_config['show_in_sidebar'] = false;

            // Insert view into view table
            \Core\Database::getInstance()->insert($views_table_name, [
                'view_name' => $view_name,
                'view_title' => $view_config['title'],
                'show_in_sidebar' => $view_config['show_in_sidebar'],
                'view_config' => json_encode($view_config)
            ]);
        }

        // Return result
        $return['success'] = true;
        echo json_encode($return);
    }

    public function delete($folder_name) {
        $db = \Core\Database::getInstance();

        // Remove entry from modules table
        $db->delete('core_modules', ['short_name' => $folder_name]);

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
        echo \Helpers\Database::getModuleView($module_name, $view_name);
    }

}

?>
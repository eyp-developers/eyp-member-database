<?php

class Modules {

    public $actions = [
        'GET' => [
            '/modules' => 'index',
            '/modules/install/:folder_name' => 'install',
            '/modules/delete/:folder_name' => 'delete'
        ]
    ];

    public function index() {
        // Get all modules
        $modules = Database::getInstance()->select('core_modules', '*');

        // Return result
        echo json_encode($modules);
    }

    public function install($folder_name) {
        $return = [];

        // Read module information
        $module_info = ModuleHelper::getModuleInfo($folder_name);

        // Read model from JSON
        $model = ModuleHelper::getModuleModel($folder_name);

        if($module_info === false || $model === false) {
            echo json_encode(['success' => false, 'data' => $module_info]);
            return;
        }

        if(!isset($module_info->short_name) || !isset($module_info->version)) {
            echo json_encode(['success' => false, 'data' => $module_info]);
            return;
        }

        $module_name = $module_info->short_name;

        // Create the module's meta table
        $meta_table_name = $module_name.'_meta';
        $meta_sql = 'CREATE TABLE '.$meta_table_name.' ( table_name VARCHAR(200), field_name VARCHAR(200), field_type VARCHAR(200), overview BOOL, enabled BOOL, system BOOL)';
        Database::getInstance()->query($meta_sql);

        // Iterate over all tables of the model
        foreach($model as $table_name => $fields) {

            // SQL statement to create a table from the model
            $table_sql = 'CREATE TABLE '.$module_name.'_'.$table_name.' (';

            // Iterate over all fields of the table
            foreach($fields as $field_name => $field_config) {

                // Set default values if values are not set for $field_config
                if(!isset($field_config->primary_key)) $field_config->primary_key = false;
                if(!isset($field_config->overview)) $field_config->overview = false;

                // Add field name and type
                $field_type = DatabaseHelper::getDBType($field_config->type);
                $table_sql .= $field_name.' '.$field_type;

                // Check if the field is the table's primary key
                if($field_config->primary_key) {
                    $table_sql .= ' PRIMARY KEY AUTO_INCREMENT';
                }

                // Finish this line
                $table_sql .= ', ';

                // Add an entry to the meta table
                Database::getInstance()->insert($meta_table_name, [
                    'table_name' => $table_name,
                    'field_name' => $field_name,
                    'field_type' => $field_config->type,
                    'overview' => $field_config->overview,
                    'enabled' => true,
                    'system' => true
                ]);
            }

            // Remove trailing comma
            $table_sql = substr($table_sql, 0, -2);

            // Finish the statement
            $table_sql .= ');';

            // Create the table
            Database::getInstance()->query($table_sql);
        }

        // Add an entry to the modules table
        Database::getInstance()->insert('core_modules', [
            'short_name' => $module_name,
            'long_name' => (isset($module_info->long_name) ? $module_info->long_name : ''),
            'description' => (isset($module_info->description) ? $module_info->description : ''),
            'version' => $module_info->version,
            'enabled' => true
        ]);

        // Return result
        $return['success'] = true;
        echo json_encode($return);
    }

    public function delete($folder_name) {
        $db = Database::getInstance();

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

}

?>
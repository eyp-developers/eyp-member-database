<?php

class Modules {

    public $get_actions = [
        '/modules' => 'index',
        '/modules/install/:module_name' => 'install'
    ];

    public function index() {
        // Get all modules
        $modules = Database::getInstance()->select('core_modules', '*');

        // Return result
        echo json_encode($modules);
    }

    public function install($module_name) {
        $return = [];

        // Read model from JSON
        $json_data = file_get_contents('_modules/'.$module_name.'/model.json');
        $model = json_decode($json_data);

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
                $field_type = get_db_type($field_config->type);
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
            'long_name' => $module_name,
            'description' => $module_name,
            'enabled' => true
        ]);

        // Return result
        $return['success'] = true;
        echo json_encode($return);
    }

}

?>
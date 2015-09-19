<?php

namespace Modules;

class Settings extends \Core\Module {

    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Set supported actions
        $this->_actions = [
            'GET' => [
                '/' => 'index',
                '/app_settings' => 'app_settings',
                '/users' => 'list_users',
                '/user/:username' => 'show_user'
            ],
            'POST' => [
                '/app_settings' => 'save_settings',
                '/users' => 'create_user',
                '/user/:username' => 'update_user'
            ],
            'DELETE' => [
                '/user/:username' => 'delete_user'
            ]
        ];
    }

    public function app_settings() {
        // Get the app settings
        $db_app_settings = \Helpers\Database::getObjects('settings', 'settings');
        $app_settings = [];
        foreach($db_app_settings as $app_setting) {
            $app_settings[$app_setting['id']] = $app_setting['value'];
        }

        echo json_encode($app_settings);
    }

    public function save_settings() {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $new_settings = json_decode($data, true);

        // Replace empty values with null
        foreach($new_settings as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }
        }

        // Update settings
        foreach($new_settings as $setting_name => $setting_value) {
            \Helpers\Database::updateObject('settings', 'settings', $setting_name, [
                'value' => $setting_value
            ]);
        }

        echo json_encode(['success' => true, 'query' => \Core\Database::getInstance()->last_query()]);
    }

    public function index() {

        // Get the app settings
        $db_app_settings = \Helpers\Database::getObjects('settings', 'settings');
        $app_settings = [];
        foreach($db_app_settings as $app_setting) {
            $app_settings[$app_setting['id']] = $app_setting['value'];
        }

        // Get sidebar config
        $sidebar_config = [];
        $enabled_modules = \Helpers\Database::getAllModules(true);

        // Create top-level sidebar entries for all modules
        foreach($enabled_modules as $module) {
            $menu_item = [
                "title" => $module['title'],
                "items" => []
            ];

            // Create sub-entries for all views of the module
            $module_views = \Helpers\Database::getModuleViews($module['name'], true, true);
            if($module_views) {
                foreach($module_views as $view) {
                    $sub_item = [
                        'title' => $view['title'],
                        'target' => '/' . $module['name'] . '/' . $view['name']
                    ];
                    $menu_item['items'][] = $sub_item;
                }

                // Add the finished menu item to the config
                $sidebar_config[] = $menu_item;
            }
        }

        // Add user menu
        $sidebar_config[] = [
            'title' => \Core\User::getInstance()->getName(),
            'items' => [
                [
                    'title' => 'Edit account',
                    'target' => '/users/me'
                ],
                [
                    'title' => 'Log out',
                    'target' => '/logout'
                ]
            ]
        ];

        // Get store config
        $store_config = \Helpers\Database::getAllStores();

        // Build the full config
        $config = [
            'app_settings' => $app_settings,
            'sidebar' => $sidebar_config,
            'stores' => $store_config
        ];

        echo json_encode($config);
    }

    public function list_users() {
        // Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = \Core\App::getInstance()->request->get("where");

        // Get the data
        $data = \Helpers\Database::getObjects('core', 'users', $fields, $search, $where, $offset, $limit, $sort, $order);
        $count = \Helpers\Database::countObjects('core', 'users', $fields, $search, $where);

        for($i = 0; $i < count($data); $i++) {
            if(is_array($data[$i])) {
                unset($data[$i]['password']);
            }
        }

        echo json_encode(['total' => $count, 'rows' => $data]);
    }

    public function show_user($username) {
        $data = \Core\Database::getInstance()->select('core_users', ['username', 'name', 'default_permission'], ['username' => $username]);

        if(!is_array($data) || count($data) === 0) {
            echo json_encode(['success' => false]);
            return;
        }

        $data = $data[0];

        $permissions = \Core\Database::getInstance()->select('core_users_permissions', ['module_name', 'permission'], ['username' => $username]);

        if(!is_array($permissions) || count($permissions) === 0) {
            echo json_encode(['success' => false]);
            return;
        }

        foreach($permissions as $permission) {

            $data['permission_' . $permission['module_name']] = $permission['permission'];
        }

        echo json_encode($data);
        return;
    }

    public function update_user($username) {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $new_data = json_decode($data, true);

        $new_permissions = [];

        // Replace empty values with null and extract permissions
        foreach($new_data as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }

            if(strpos($key, 'permission_') === 0) {
                unset($new_data[$key]);
                
                $module = substr($key, strlen('permission_'));

                $new_permissions[] = [
                    'module_name' => $module,
                    'permission' => $value
                ];
            }
        }

        // Update permissions
        foreach($new_permissions as $new_permission) {
            \Core\Database::getInstance()->update(
                'core_users_permissions',
                ['permission' => $new_permission['permission']],
                ['AND' => [
                    'username' => $username,
                    'module_name' => $new_permission['module_name']
                    ]
                ]);
        }

        // Hash password
        if($new_data['password'] === NULL) {
            unset($new_data['password']);
        } else {
            $new_data['password'] = password_hash($new_data['password'], PASSWORD_DEFAULT);
        }

        // Update the data
        $num_rows = \Core\Database::getInstance()->update('core_users', $new_data, ['username' => $username]);
        $success = ($num_rows > 0);

        // Return the appropriate result
        echo json_encode([
            'success' => $success
        ]);
    }

    public function delete_user($username) {
        // Get the data
        $num_rows = \Core\Database::getInstance()->delete('core_users', ['username' => $username]);

        $status = ($num_rows > 0);

        echo json_encode(['success' => $status]);
    }

    public function create_user() {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $new_data = json_decode($data, true);

        // Replace empty values with null
        foreach($new_data as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }
        }

        // Insert the data
        $username = $new_data['username'];
        $name = $new_data['name'];
        $password = password_hash($new_data['password'], PASSWORD_DEFAULT);
        $default_permission = $new_data['default_permission'];
        $result = \Core\Database::getInstance()->query("CALL proc_createUser('$username', '$password', '$name', $default_permission)");

        // Return the appropriate result
        if($result === false) {
            echo json_encode(['success' => false]);
        } else {
            echo json_encode([
                'success' => true,
                'db_changes' => [
                    [
                        'module_name' => 'core',
                        'model_name' => 'users'
                    ]
                ]
            ]);
        }
    }
}

?>
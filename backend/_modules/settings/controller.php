<?php

namespace Modules;

/**
 * The Settings module
 */
class Settings extends \Core\Module {

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
                '/sidebar' => 'sidebar',
                '/app_settings' => 'appSettings',
                '/users' => 'listUsers',
                '/user/:username' => 'viewUser'
            ],
            'POST' => [
                '/app_settings' => 'saveSettings',
                '/users' => 'createUser',
                '/user/:username' => 'updateUser'
            ],
            'DELETE' => [
                '/user/:username' => 'deleteUser'
            ]
        ];
    }

    /**
     * Gets a summary of the application configuration
     *
     * @return void
     */
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
        $settings_menu = null;
        foreach($enabled_modules as $module) {
            $menu_item = [
                "title" => $module['title'],
                "icon" => $module['icon'],
                "items" => []
            ];

            // Create sub-entries for all views of the module
            $module_views = \Helpers\Database::getModuleViews($module['name'], true, true);
            if($module_views) {
                foreach($module_views as $view) {
                    $sub_item = [
                        'title' => $view['title'],
                        "icon" => $view['icon'],
                        'target' => '/' . $module['name'] . '/' . $view['name']
                    ];
                    $menu_item['items'][] = $sub_item;
                }

                // Add the finished menu item to the config
                if($module['name'] === 'settings') {
                    $settings_menu = $menu_item;
                } else {
                    $sidebar_config[] = $menu_item;
                }
            }
        }
        if($settings_menu) {
            $sidebar_config[] = $settings_menu;
        }

        // Add user menu
        $user_menu = [
            'title' => \Core\User::getInstance()->getName(),
            'items' => [
                [
                    'title' => 'Edit account',
                    'target' => '/me/edit'
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
            'user_menu' => $user_menu,
            'stores' => $store_config
        ];

        \Helpers\Response::success($config);
    }

    /**
     * Gets the sidebar configuration
     *
     * @return void
     */
    public function sidebar() {

        // Get sidebar config
        $sidebar_config = [];
        $enabled_modules = \Helpers\Database::getAllModules(true);

        // Create top-level sidebar entries for all modules
        foreach($enabled_modules as $module) {
            $menu_item = [
                "title" => $module['title'],
                "icon" => $module['icon'],
                "items" => []
            ];

            // Create sub-entries for all views of the module
            $module_views = \Helpers\Database::getModuleViews($module['name'], true, true);
            if($module_views) {
                foreach($module_views as $view) {
                    $sub_item = [
                        'title' => $view['title'],
                        "icon" => $view['icon'],
                        'target' => '/' . $module['name'] . '/' . $view['name']
                    ];
                    $menu_item['items'][] = $sub_item;
                }

                // Add the finished menu item to the config
                $sidebar_config[] = $menu_item;
            }
        }

        \Helpers\Response::success($sidebar_config);
    }

    /**
     * Gets the application settiongs
     *
     * @return void
     */
    public function appSettings() {
        // Get the app settings
        $db_app_settings = \Helpers\Database::getObjects('settings', 'settings');

        $app_settings = [];
        foreach($db_app_settings as $app_setting) {
            $app_settings[$app_setting['id']] = $app_setting['value'];
        }

        \Helpers\Response::success($app_settings);
    }

    /**
     * Saves the application settiongs
     *
     * @return void
     */
    public function saveSettings() {
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

        \Helpers\Response::success();
    }

    /**
     * Gets all users
     *
     * @return void
     */
    public function listUsers() {
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

        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    /**
     * Gets a certain user
     *
     * @param {string} $username The user's username
     * @return void
     */
    public function viewUser($username) {
        // Get data
        $data = \Core\Database::getInstance()->select('core_users', ['username', 'name', 'default_permission'], ['username' => $username]);

        if(!is_array($data) || count($data) === 0) {
            \Helpers\Response::error(\Helpers\Response::$E_RECORD_NOT_FOUND);
            return;
        }

        $data = $data[0];

        // Get permissions
        $permissions = \Core\Database::getInstance()->select('core_users_permissions', ['module_name', 'permission'], ['username' => $username]);

        if(!is_array($permissions) || count($permissions) === 0) {
            \Helpers\Response::error(\Helpers\Response::$E_RECORD_NOT_FOUND);
            return;
        }

        foreach($permissions as $permission) {
            $data['permission_' . $permission['module_name']] = $permission['permission'];
        }

        \Helpers\Response::success($data);
    }

    /**
     * Updates a certain user
     *
     * @param {string} $username The user's username
     * @return void
     */
    public function updateUser($username) {
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
        \Core\Database::getInstance()->update('core_users', $new_data, ['username' => $username]);

        // Return the appropriate result
        \Helpers\Response::success(false, false, true);
    }

    /**
     * Deletes a certain user
     *
     * @param {string} $username The user's username
     * @return void
     */
    public function deleteUser($username) {
        // Get the data
        $num_rows = \Core\Database::getInstance()->delete('core_users', ['username' => $username]);
        $success = ($num_rows > 0);

        \Helpers\Response::respond($success);
    }

    /**
     * Creates a certain user
     *
     * @return void
     */
    public function createUser() {
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
            \Helpers\Response::error();
        } else {
            \Helpers\Response::success(
                false,
                [
                    [
                        'module_name' => 'core',
                        'model_name' => 'users'
                    ]
                ]
            );
        }
    }
}

?>
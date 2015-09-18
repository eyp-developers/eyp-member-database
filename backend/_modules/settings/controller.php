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
                '/app_settings' => 'app_settings'
            ],
            'POST' => [
                '/app_settings' => 'save_settings'
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
            $module_views = \Helpers\Database::getModuleViews($module['name'], true);
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
            'title' => \Core\User::getInstance()->getUsername(),
            'items' => [
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
}

?>
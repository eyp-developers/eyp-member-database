<?php

namespace Modules;

class Config extends \Core\Module {

    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Set supported actions
        $this->_actions = [
            'GET' => [
                '/config' => 'index'
            ]
        ];
    }

    public function index() {
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


        // Build the full config
        $config = [
            'sidebar' => $sidebar_config
        ];

        echo json_encode($config);
    }
}

?>
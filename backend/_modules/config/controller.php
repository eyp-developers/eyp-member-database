<?php

namespace Modules;

class Config {

    public $actions = [
    	'GET' => [
        	'/config' => 'index'
        ],
    ];

    public function index() {
        // Get sidebar config
        $sidebar_config = [];
        $enabled_modules = \Helpers\Database::getAllModules(true);

        // Create top-level sidebar entries for all modules
        foreach($enabled_modules as $module) {
            $menu_item = [
                "title" => $module['long_name'],
                "items" => []
            ];

            // Create sub-entries for all views of the module
            $module_views = \Helpers\Database::getModuleViews($module['short_name'], true);
            if($module_views) {
                foreach($module_views as $view) {
                    $sub_item = [
                        'title' => $view['view_title'],
                        'target' => '/' . $module['short_name'] . '/' . $view['view_name']
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
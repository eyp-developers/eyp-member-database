<?php

namespace Core;

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

/**
 * A singleton wrapper class for the app
 */
class App {

	/**
	 * @var Singleton The reference to the Singleton instance of the class
	 */
	private static $instance;

	/**
     * Returns the Singleton instance of this class.
     *
     * @return Singleton The Singleton instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new \Slim\Slim(array(
			    'debug' => true,
			    'mode' => 'development'
			));
        }
        
        return static::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * Singleton via the 'new' operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * Singleton instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the Singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

    /**
     * Loads all enabled modules
     */
    public static function loadModules() {
		// Load Modules
		$enabled_modules = Database::getInstance()->select('core_modules', "*", ['enabled' => true]);

		foreach($enabled_modules as $module) {
			$short_name = $module["short_name"];

			// Load module

			$module_classname = '\\Modules\\'.ucfirst($short_name);
			$module = new $module_classname();

			// Set up routes for this module
			if(isset($module->actions['GET'])) {
				foreach($module->actions['GET'] as $action => $handler) {
					\Core\App::getInstance()->get($action, [$module, $handler]);
				}
			}
			if(isset($module->actions['POST'])) {
				foreach($module->actions['POST'] as $action => $handler) {
				    \Core\App::getInstance()->post($action, [$module, $handler]);
				}
			}
			if(isset($module->actions['DELETE'])) {
				foreach($module->actions['DELETE'] as $action => $handler) {
					\Core\App::getInstance()->delete($action, [$module, $handler]);
				}
			}
		}
    }

    /**
     * Runs the app
     */
    public static function run() {
    	\Core\App::getInstance()->run();
    }

}

?>
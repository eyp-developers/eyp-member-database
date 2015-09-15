<?php

namespace Core;

// Load Medoo database framework
require 'Medoo/medoo.min.php';

/**
 * A singleton wrapper class for the database connection
 */
class Database {

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
            static::$instance = new \medoo(\Core\Config::$db_connection);
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

}

?>
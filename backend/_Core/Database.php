<?php

namespace Core;

// Load Medoo database framework
require 'Medoo/medoo.min.php';

/**
 * A singleton class for the database connection
 */
class Database {

	/**
	 * @var {Database} $_instance The reference to the Singleton instance of the class
	 */
	private static $_instance;

	/**
     * Returns the Singleton instance of this class.
     *
     * @return Singleton The Singleton instance.
     */
    public static function getInstance()
    {
        if (null === static::$_instance) {
            static::$_instance = new \medoo(\Core\Config::$db_connection);
        }
        
        return static::$_instance;
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
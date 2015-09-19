<?php

namespace Core;

/**
 * A singleton wrapper class for the user
 */
class User {

	/**
	 * @var Singleton $instance The reference to the Singleton instance of the class
	 */
	private static $instance;

    private $_username;
    private $_name;
    private $_permissions;

	/**
     * Validates username and password and logs in the user
     *
     * @param username The username
     * @param password The password
     * @return An authToken, or false
     */
    public static function login($username, $password) {

        // Get the user information
        $userPassword = \Core\Database::getInstance()->select(
            'core_users',
            ['password'],
            ['username' => $username]
        );

        if(is_array($userPassword) && count($userPassword) === 1) {
            $userPassword = $userPassword[0]['password'];
        } else {
            return false;
        }

        // Validate the password
        if(password_verify($password, $userPassword)) {

            // Generate the potential authToken
            $authToken = bin2hex(openssl_random_pseudo_bytes(32));

            // Store the token
            \Core\Database::getInstance()->update(
                'core_users',
                ['token' => $authToken],
                ['username' => $username]
            );

            // Authenticate and proceed
            if(static::authenticate($authToken)) {
                return $authToken;
            } else {
                return false;
            }
        }

        return false;
    }
    /**
     * Authenticates an user by their auth token
     *
     * @param authToken The authentication token
     * @return Whether the authToken is valid
     */
    public static function authenticate($authToken) {

        // Check the authToken
        $userInfo = \Core\Database::getInstance()->select(
            'core_users',
            ['[>]core_users_permissions' => 'username'],
            ['username', 'name', 'module_name', 'permission'],
            ['token' => $authToken]
        );

        if(is_array($userInfo) && count($userInfo) >= 1) {
            $username = $userInfo[0]['username'];
            $name = $userInfo[0]['name'];

            $permissions = [];
            foreach($userInfo as $row) {
                $permissions[$row['module_name']] = $row['permission'];
            }

            // Create user object
            static::$instance = new \Core\User($username, $name, $permissions);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the logged in user
     *
     * @return Whether the authToken is valid
     */
    public static function getInstance() {
        return static::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * Singleton via the 'new' operator from outside of this class.
     */
    protected function __construct($username, $name, $permissions) {
        $this->_username = $username;
        $this->_name = $name;
        $this->_permissions = $permissions;
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * Singleton instance.
     *
     * @return void
     */
    private function __clone() {}

    /**
     * Private unserialize method to prevent unserializing of the Singleton
     * instance.
     *
     * @return void
     */
    private function __wakeup() {}

    /**
     * Returns the username
     * @return string The username
     */
    public function getUsername() {
        return $this->_username;
    }

    /**
     * Returns the name
     * @return string The name
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * Returns whether the user can read from the given module
     * @param module_name The module to check
     * @return boolean
     */
    public function canReadModule($module_name) {
        return $this->_permissions[$module_name] >= 1 ? true : false;
    }

    /**
     * Returns whether the user can write to the given module
     * @param module_name The module to check
     * @return boolean
     */
    public function canWriteModule($module_name) {
        return $this->_permissions[$module_name] >= 2 ? true : false;
    }

}

?>
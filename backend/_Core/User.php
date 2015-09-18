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
        error_log("Authenticating token $authToken");

        // Check the authToken
        $userInfo = \Core\Database::getInstance()->select(
            'core_users',
            ['username'],
            ['token' => $authToken]
        );

        if(is_array($userInfo) && count($userInfo) >= 1) {
            $userInfo = $userInfo[0];

            // Create user object
            static::$instance = new \Core\User($userInfo['username']);

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
    protected function __construct($username) {
        $this->_username = $username;
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

}

?>
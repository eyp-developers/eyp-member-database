<?php

namespace Core;

/**
 * A singleton class for the logged in user
 */
class User {

	/**
	 * @var {User} $_instance The reference to the Singleton instance of the class
	 */
	private static $_instance;

    /**
     * @var {string} $_username The logged in user's username
     */
    private $_username;
        public function getUsername() { return $this->_username; }

    /**
     * @var {string} $_name The logged in user's full name
     */
    private $_name;
        public function getName() { return $this->_name; }

    /**
     * @var {array} $_permissions The logged in user's permissions
     */
    private $_permissions;
        public function canReadModule($module_name) { return $this->_permissions[$module_name] >= 1 ? true : false; }
        public function canWriteModule($module_name) { return $this->_permissions[$module_name] >= 2 ? true : false; }

	/**
     * Validates username and password and logs in the user
     *
     * @param {string} $username The username
     * @param {string} $password The password
     * @return {string/boolean] The generated authentication token, or false if the authentication failed
     */
    public static function login($username, $password) {

        // Get the user information
        $user_password = \Core\Database::getInstance()->select(
            'core_users',
            ['password'],
            ['username' => $username]
        );

        if(is_array($user_password) && count($user_password) === 1) {
            $user_password = $user_password[0]['password'];
        } else {
            return false;
        }

        // Validate the password
        if(password_verify($password, $user_password)) {

            // Generate the new authentication token
            $authtoken = bin2hex(openssl_random_pseudo_bytes(32));

            // Store the token
            \Core\Database::getInstance()->update(
                'core_users',
                ['token' => $authtoken],
                ['username' => $username]
            );

            // Authenticate and proceed
            if(static::authenticate($authtoken)) {
                return $authtoken;
            } else {
                return false;
            }
        }

        return false;
    }
    /**
     * Authenticates an user by checking their auth token
     *
     * @param {string} $authtoken The authentication token
     * @param {string} $api_key The API key, if any
     * @return {boolean} Whether the authentication token is valid
     */
    public static function authenticate($authtoken, $api_key = null) {

        // Prefer authtoken to api_key 
        if($authtoken !== null && strlen($authtoken) !== 0) {
            $condition = ['token' => $authtoken];
        } else if($api_key !== null && strlen($api_key) !== 0) {
            $condition = ['api_key' => $api_key];
        } else {
            return false;
        }

        // Check the authentication
        $userInfo = \Core\Database::getInstance()->select(
            'core_users',
            ['[>]core_users_permissions' => 'username'],
            ['username', 'name', 'module_name', 'permission'],
            $condition
        );

        if(is_array($userInfo) && count($userInfo) >= 1) {

            // Get user data
            $username = $userInfo[0]['username'];
            $name = $userInfo[0]['name'];

            $permissions = [];
            foreach($userInfo as $row) {
                $permissions[$row['module_name']] = $row['permission'];
            }

            // Create user object
            static::$_instance = new \Core\User($username, $name, $permissions);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns the logged in user
     *
     * @return {User} The logged in user
     */
    public static function getInstance() {
        return static::$_instance;
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
     * Returns whether the user can read from the given module
     * @param module_name The module to check
     * @return boolean
     */

}

?>
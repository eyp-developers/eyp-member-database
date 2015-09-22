<?php

namespace Modules;

/**
 * The Auth module
 */
class Auth extends \Core\Module {

    /**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Set supported actions
        $this->_actions = [
            'POST' => [
                '/login' => 'login'
            ]
        ];
    }

    /**
     * Logs in the specified user
     *
     * @return void
     */
    public function login() {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $auth_data = json_decode($data, true);

        // Validate auth data
        if(!isset($auth_data['username']) || !isset($auth_data['password'])) {
            \Helpers\Response::error(\Helpers\Response::$E_INVALID_LOGIN_DATA);
            return;
        }

        $auth_token = \Core\User::login($auth_data['username'], $auth_data['password']);
        if($auth_token === false) {
            \Helpers\Response::error(\Helpers\Response::$E_INVALID_LOGIN_DATA);
            return;
        }

        \Helpers\Response::success([
            'auth_token' => $auth_token
        ]);
    }
}

?>
<?php

namespace Modules;

class Auth extends \Core\Module {

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

    public function login() {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $auth_data = json_decode($data, true);

        // Validate auth data
        if(!isset($auth_data['username']) || !isset($auth_data['password'])) {
            echo json_encode(['success' => false]);
            return;
        }

        $authToken = \Core\User::login($auth_data['username'], $auth_data['password']);
        if($authToken === false) {
            echo json_encode(['success' => false]);
            return;
        }

        echo json_encode(['success' => true, 'authToken' => $authToken]);
    }
}

?>
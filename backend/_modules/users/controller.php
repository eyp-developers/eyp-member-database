<?php

namespace Modules;

class Users extends \Core\Module {

    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Set supported actions
        $this->_actions = [
            'GET' => [
                '/' => 'viewMe'
            ],

            'POST' => [
                '/' => 'updateMe'
            ]
        ];
    }

    public function viewMe() {
        $username = \Core\User::getInstance()->getUsername();
        $name = \Core\User::getInstance()->getName();
        
        echo json_encode(['username' => $username, 'name' => $name]);
    }

    public function updateMe() {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $new_data = json_decode($data, true);

        // Replace empty values with null
        foreach($new_data as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }
        }

        if($new_data['password'] === NULL) {
            unset($new_data['password']);
        } else {
            $new_data['password'] = password_hash($new_data['password'], PASSWORD_DEFAULT);
        }

        // Update the data
        $num_rows = \Core\Database::getInstance()->update('core_users', $new_data, ['username' => \Core\User::getInstance()->getUsername()]);

        $success = ($num_rows > 0);

        // Return the appropriate result
        echo json_encode([
            'success' => $success
        ]);
    }
}

?>
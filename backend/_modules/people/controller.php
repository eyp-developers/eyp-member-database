<?php

class People {

    public $actions = [
    	'GET' => [
        	'/people' => 'index',
        	'/people/:id' => 'view',
        ],

        'POST' => [
        	'/people' => 'create',
        	'/people/:id' => 'update'
        ],

        'DELETE' => [
        	'/people/:id' => 'delete'
        ]
    ];

    public function index() {
        $fields = App::getInstance()->request->params("fields");
        $fields = explode(",", $fields);
    	echo json_encode(DatabaseHelper::getObjects('people', 'people', $fields));
    }

    public function view($id) {
	    echo json_encode(DatabaseHelper::getObject('people', 'people', $id));
    }

    public function delete($id) {
    	$status = DatabaseHelper::deleteObject('people', 'people', $id);
    	echo json_encode(['success' => $status]);
    }

    public function create() {
    	// Get the transmitted data
    	$data = App::getInstance()->request->getBody();
    	$new_person = json_decode($data, true);

    	// Insert the data
    	$new_id = DatabaseHelper::createObject('people', 'people', $new_person);

    	if($new_id === false) {
    		echo json_encode(['success' => false]);
    	} else {
    		echo json_encode(['success' => true, 'object_id' => $new_id]);
    	}
    }

    public function update($id) {
    	// Get the transmitted data
    	$data = App::getInstance()->request->getBody();
    	$new_person = json_decode($data, true);

    	// Update the data
    	$success = DatabaseHelper::updateObject('people', 'people', $id, $new_person);

   		echo json_encode(['success' => $success]);
    }
}

?>
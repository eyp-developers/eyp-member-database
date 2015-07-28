<?php

namespace Modules;

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
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");

        $people = \Helpers\Database::getObjects('people', 'people', $fields, $search, $offset, $limit, $sort, $order);
        $count = \Helpers\Database::countObjects('people', 'people', $fields, $search);

    	echo json_encode(['total' => $count, 'rows' => $people]);
    }

    public function view($id) {
	    echo json_encode(\Helpers\Database::getObject('people', 'people', $id));
    }

    public function delete($id) {
    	$status = \Helpers\Database::deleteObject('people', 'people', $id);
    	echo json_encode(['success' => $status]);
    }

    public function create() {
    	// Get the transmitted data
    	$data = App::getInstance()->request->getBody();
    	$new_person = json_decode($data, true);

    	// Insert the data
    	$new_id = \Helpers\Database::createObject('people', 'people', $new_person);

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
    	$success = \Helpers\Database::updateObject('people', 'people', $id, $new_person);

   		echo json_encode(['success' => $success]);
    }
}

?>
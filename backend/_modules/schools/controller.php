<?php

namespace Modules;

class Schools {

    public $actions = [
    	'GET' => [
        	'/schools' => 'index',
        	'/schools/:id' => 'view',
        ],

        'POST' => [
        	'/schools' => 'create',
        	'/schools/:id' => 'update'
        ],

        'DELETE' => [
        	'/schools/:id' => 'delete'
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

        $schools = \Helpers\Database::getObjects('schools', 'schools', $fields, $search, $offset, $limit, $sort, $order);
        $count = \Helpers\Database::countObjects('schools', 'schools', $fields, $search);

    	echo json_encode(['total' => $count, 'rows' => $schools]);
    }

    public function view($id) {
	    echo json_encode(\Helpers\Database::getObject('schools', 'schools', $id));
    }

    public function delete($id) {
    	$status = \Helpers\Database::deleteObject('schools', 'schools', $id);
    	echo json_encode(['success' => $status]);
    }

    public function create() {
    	// Get the transmitted data
    	$data = \Core\App::getInstance()->request->getBody();
    	$new_school = json_decode($data, true);

    	// Insert the data
    	$new_id = \Helpers\Database::createObject('schools', 'schools', $new_school);

    	if($new_id === false) {
    		echo json_encode(['success' => false]);
    	} else {
    		echo json_encode(['success' => true, 'object_id' => $new_id]);
    	}
    }

    public function update($id) {
    	// Get the transmitted data
    	$data = \Core\App::getInstance()->request->getBody();
    	$new_school = json_decode($data, true);

    	// Update the data
    	$success = \Helpers\Database::updateObject('schools', 'schools', $id, $new_school);

   		echo json_encode(['success' => $success]);
    }
}

?>
<?php

class People {

    public $get_actions = [
        '/people' => 'index',
        '/people/view/:id' => 'view'
    ];

    public function index() {
    	echo json_encode(DatabaseHelper::getOverviewData('people', 'people'));
    }

    public function view($id) {
	    echo json_encode(DatabaseHelper::getObject('people', 'people', $id));
    }
}

?>
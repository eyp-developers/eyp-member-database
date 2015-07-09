<?php

class People {

    public $actions = [
    	'GET' => [
        	'/people' => 'index',
        	'/people/view/:id' => 'view'
        ],

        'POST' => [
        	'/people/update/:id' => 'update'
        ]
    ];

    public function index() {
    	echo json_encode(DatabaseHelper::getOverviewData('people', 'people'));
    }

    public function view($id) {
	    echo json_encode(DatabaseHelper::getObject('people', 'people', $id));
    }

    public function update($id) {

    }
}

?>
<?php

namespace Modules;

class Schools extends \Core\Module {

    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Add additional route
        $this->_actions['GET']['/schools/students/:id'] = 'students';
    }

    public function students($school_id) {
    	// Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'school = '.$school_id;

        // Get the data
        $data = \Helpers\Database::getObjects('people', 'people', $fields, $search, $where, $offset, $limit, $sort, $order);
        $count = \Helpers\Database::countObjects('people', 'people', $fields, $search, $where);

        echo json_encode(['total' => $count, 'rows' => $data]);
    }


}

?>
<?php

namespace Modules;

/**
 * The Modules module
 */
class Schools extends \Core\Module {

    /**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Add additional route
        $this->_actions['GET']['/students/:id'] = 'students';
    }

    /**
     * Gets all students of a certain schoool
     *
     * @param {int} $school_id The Id of the school
     * @return void
     */
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

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }


}

?>
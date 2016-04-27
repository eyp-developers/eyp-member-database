<?php

namespace Modules;

/**
 * The Schools module
 */
class Schools extends \Core\Module {

    /**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Add additional route
        $this->_actions['GET']['/people/:id'] = 'people';
    }

    /**
     * Returns a paginated index of the module's data
     * 
     * @return void
     */
    public function index() {
        // Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = \Core\App::getInstance()->request->get("where");
        $filter = json_decode(\Core\App::getInstance()->request->get("filter"), true);

        // Get the data
        $data = \Helpers\Database::getObjects($this->_lc_classname, $this->_lc_classname, $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects($this->_lc_classname, $this->_lc_classname, $fields, $search, $where, $filter);

        // Create color coding
        $colors = [
            1 => 'blue',
            2 => 'yellow',
            3 => 'green',
            4 => 'purple',
            5 => 'orange',
            6 => 'red'
        ];

        $data = array_map(function($row) use($colors) {
            $row['bg_color'] = $colors[$row["kind"]];
            return $row;
        }, $data);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows'  => $data
        ]);
    }

    /**
     * Gets all people of a certain schoool
     *
     * @param {int} $school_id The Id of the school
     * @return void
     */
    public function people($school_id) {
    	// Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'school = '.$school_id;
        $filter = json_decode(\Core\App::getInstance()->request->get("filter"), true);

        // Get the data
        $data = \Helpers\Database::getObjects('people', 'people', $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects('people', 'people', $fields, $search, $where, $filter);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }


}

?>
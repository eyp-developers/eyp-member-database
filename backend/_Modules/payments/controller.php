<?php

namespace Modules;

/**
 * The Payments module
 */
class Payments extends \Core\Module {

    /**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Add additional route for member payments
        $this->_actions['GET']['/member_payments/:id'] = 'member_payments';
    }

    /**
     * Gets all payments of a certain person
     *
     * @param {int} $person_id The Id of the person
     * @return void
     */
    public function member_payments($person_id) {
    	// Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'person = '.$person_id;

        // Get the data
        $data = \Helpers\Database::getObjects('payments', 'payments', $fields, $search, $where, $offset, $limit, $sort, $order);
        
        $count = \Helpers\Database::countObjects('payments', 'payments', $fields, $search, $where);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }


}

?>
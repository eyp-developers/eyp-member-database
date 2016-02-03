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

    /**
     * Gets the current payment status of a person
     *
     * @param {int} $person_id The Id of the person
     * @return void
     */
    public static function getMembershipStatusForPersonId($person_id) {
        // Get the payments
        $payments = \Helpers\Database::getObjects('payments', 'payments', false, false, "person = $person_id");

        // Calculate the membership status
        // Default to 1 (= Invalid)
        $current_status = 1;
        $current_date = date("Y-m-d");
        $expiring_date = date_format(date_create("+2 weeks"), "Y-m-d");

        foreach($payments as $payment) {
            if($payment["kind"] == 1 && $payment["valid_from"] <= $current_date && $payment["valid_until"] >= $current_date) {
                $new_status = 3;
                if($payment["valid_until"] <= $expiring_date) {
                    $new_status = 2;
                }

                if($new_status > $current_status) {
                    $current_status = $new_status;
                }
            }
        }

        return $current_status;
    }


}

?>
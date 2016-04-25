<?php

namespace Modules;

/**
 * The Events module
 */
class Events extends \Core\Module {

    /**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Add additional routes for participants
        $this->_actions['GET']['/participants/:id'] = 'participants';
        $this->_actions['POST']['/participants/create'] = 'createParticipant';
        $this->_actions['DELETE']['/participants/:id'] = 'deleteParticipant';

        // Add additional routes for participations
        $this->_actions['GET']['/participations/:id'] = 'participations';
    }

    /**
     * Gets all participants of a certain events
     *
     * @param {int} $event_id The Id of the event
     * @return void
     */
    public function participants($event_id) {
    	// Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'event = '.$event_id;
        $filter = json_decode(\Core\App::getInstance()->request->get("filter"), true);

        // Get the data
        $data = \Helpers\Database::getObjects('events', 'participations', $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects('events', 'participations', $fields, $search, $where, $filter);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    /**
     * Gets all participations of a certain person
     *
     * @param {int} $person_id The Id of the person
     * @return void
     */
    public function participations($person_id) {
        // Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'person = '.$person_id;
        $filter = json_decode(\Core\App::getInstance()->request->get("filter"), true);

        // Get the data
        $data = \Helpers\Database::getObjects('events', 'participations', $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects('events', 'participations', $fields, $search, $where, $filter);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    /**
     * Creates a new participation
     * 
     * @return void
     */
    public function createParticipant() {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $new_data = json_decode($data, true);

        // Replace empty values with null
        foreach($new_data as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }
        }

        // Insert the data
        $invalid_fields = [];
        $new_id = \Helpers\Database::createObject('events', 'participations', $new_data, $invalid_fields);
        
        // Return the appropriate result
        if($new_id === false) {
            \Helpers\Response::error(\Helpers\Response::$E_SAVE_FAILED, [
                'invalid_fields' => $invalid_fields
            ]);
        } else {
            \Helpers\Response::success(
                [
                    'record_id' => $new_id
                ],
                [
                    [
                        'module_name' => 'events',
                        'model_name' => 'participations'
                    ]
                ]
            );
        }
    }

    /**
     * Deletes a participant
     * 
     * @param {int} $id The ID of the record
     * @return void
     */
    public function deleteParticipant($id) {
        // Delete the record
        $success = \Helpers\Database::deleteObject('events', 'participations', $id);

        // Send response
        \Helpers\Response::respond($success);
    }
}

?>
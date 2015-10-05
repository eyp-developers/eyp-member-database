<?php

namespace Modules;

/**
 * The Sponsors module
 */
class Sponsors extends \Core\Module {

    /**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Add additional routes for sponsorships
        $this->_actions['GET']['/sponsorships/:id'] = 'sponsorship';
        $this->_actions['POST']['/sponsorships/create'] = 'createSponsorship';
        $this->_actions['POST']['/sponsorships/:id'] = 'editSponsorship';
        $this->_actions['DELETE']['/sponsorships/:id'] = 'deleteSponsorship';

        // Add additional routes for sponsor sponsorships
        $this->_actions['GET']['/sponsor_sponsorships/:id'] = 'sponsor_sponsorships';

        // Add additional routes for session sponsorships
        $this->_actions['GET']['/session_sponsorships/:id'] = 'session_sponsorships';
    }

    /**
     * Returns a full sponsorship record
     * 
     * @param {int} $id The ID of the sponsorship
     * @return void
     */
    public function sponsorship($id) {
        // Get the record
        $record = \Helpers\Database::getObject('sponsors', 'sponsorships', $id);

        // Send response
        if($record === false) {
            \Helpers\Response::error(\Helpers\Response::$E_RECORD_NOT_FOUND);
        } else {
            \Helpers\Response::success($record);
        }
    }

    /**
     * Gets all sponsorships of a certain sponsor
     *
     * @param {int} $company_id The Id of the sponsor
     * @return void
     */
    public function sponsor_sponsorships($sponsor_id) {
    	// Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'sponsor = '.$sponsor_id;

        // Get the data
        $data = \Helpers\Database::getObjects('sponsors', 'sponsorships', $fields, $search, $where, $offset, $limit, $sort, $order);
        $count = \Helpers\Database::countObjects('sponsors', 'sponsorships', $fields, $search, $where);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    /**
     * Gets all sponsors of a certain session
     *
     * @param {int} $session_id The Id of the session
     * @return void
     */
    public function session_sponsorships($session_id) {
        // Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'session = '.$session_id;

        // Get the data
        $data = \Helpers\Database::getObjects('sponsors', 'sponsorships', $fields, $search, $where, $offset, $limit, $sort, $order);
        $count = \Helpers\Database::countObjects('sponsors', 'sponsorships', $fields, $search, $where);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    /**
     * Creates a new sponsorship
     * 
     * @return void
     */
    public function createSponsorship() {
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
        $new_id = \Helpers\Database::createObject('sponsors', 'sponsorships', $new_data, $invalid_fields);
        
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
                        'module_name' => 'sponsors',
                        'model_name' => 'sponsorships'
                    ]
                ]
            );
        }
    }

     /**
     * Updates a sponsorship
     * 
     * @param {int} $id The ID of the sponsorship
     * @return void
     */
    public function editSponsorship($id) {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $new_data = json_decode($data, true);

        // Replace empty values with null
        foreach($new_data as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }
        }

        // Update the data
        $invalid_fields = [];
        $success = \Helpers\Database::updateObject('sponsors', 'sponsorships', $id, $new_data, 'id', $invalid_fields);

        // Return the appropriate result
        if($success === false) {
            \Helpers\Response::error(\Helpers\Response::$E_SAVE_FAILED, [
                'invalid_fields' => $invalid_fields
            ]);
        } else {
            \Helpers\Response::success(
                false,
                [
                    [
                        'module_name' => 'sponsors',
                        'model_name' => 'sponsorships'
                    ]
                ]
            );
        }
    }

    /**
     * Deletes a sponsorship
     * 
     * @param {int} $id The ID of the sponsorship
     * @return void
     */
    public function deleteSponsorship($id) {
        // Delete the record
        $success = \Helpers\Database::deleteObject('sponsors', 'sponsorships', $id);

        // Send response
        \Helpers\Response::respond($success);
    }
}

?>
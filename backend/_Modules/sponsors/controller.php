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

        // Add additional routes for rejections
        $this->_actions['GET']['/rejections/:id'] = 'rejection';
        $this->_actions['POST']['/rejections/create'] = 'createRejection';
        $this->_actions['POST']['/rejections/:id'] = 'editRejection';
        $this->_actions['DELETE']['/rejections/:id'] = 'deleteRejection';

        // Add additional routes for sponsor rejections
        $this->_actions['GET']['/sponsor_rejections/:id'] = 'sponsor_rejections';

        // Add additional routes for session rejections
        $this->_actions['GET']['/session_rejections/:id'] = 'session_rejections';
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
        $filter = json_decode(\Core\App::getInstance()->request->get("filter"), true);

        // Get the data
        $data = \Helpers\Database::getObjects('sponsors', 'sponsorships', $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects('sponsors', 'sponsorships', $fields, $search, $where, $filter);

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
        $filter = json_decode(\Core\App::getInstance()->request->get("filter"), true);

        // Get the data
        $data = \Helpers\Database::getObjects('sponsors', 'sponsorships', $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects('sponsors', 'sponsorships', $fields, $search, $where, $filter);

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

        // Update the sponsor's has_sponsored field
        $sponsor_id = $new_data['sponsor'];
        $this->updateHasSponsored($sponsor_id);
        
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
        // Get the record
        $sponsorship = \Core\Database::getInstance()->select(
            'sponsors_sponsorships',
            '*',
            ['id' => $id]
        );
        if(is_array($sponsorship) && count($sponsorship) > 0) {
            $sponsorship = $sponsorship[0];
        }

        // Delete the record
        $success = \Helpers\Database::deleteObject('sponsors', 'sponsorships', $id);

        // Update the sponsor's has_sponsored field
        $sponsor_id = $sponsorship['sponsor'];
        $this->updateHasSponsored($sponsor_id);

        // Send response
        \Helpers\Response::respond($success);
    }

    /**
     * Returns a full rejection record
     * 
     * @param {int} $id The ID of the rejection
     * @return void
     */
    public function rejection($id) {
        // Get the record
        $record = \Helpers\Database::getObject('sponsors', 'rejections', $id);

        // Send response
        if($record === false) {
            \Helpers\Response::error(\Helpers\Response::$E_RECORD_NOT_FOUND);
        } else {
            \Helpers\Response::success($record);
        }
    }

    /**
     * Gets all rejections of a certain sponsor
     *
     * @param {int} $company_id The Id of the sponsor
     * @return void
     */
    public function sponsor_rejections($sponsor_id) {
        // Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'sponsor = '.$sponsor_id;
        $filter = json_decode(\Core\App::getInstance()->request->get("filter"), true);

        // Get the data
        $data = \Helpers\Database::getObjects('sponsors', 'rejections', $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects('sponsors', 'rejections', $fields, $search, $where, $filter);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    /**
     * Gets all rejections of a certain session
     *
     * @param {int} $session_id The Id of the session
     * @return void
     */
    public function session_rejections($session_id) {
        // Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'session = '.$session_id;
        $filter = json_decode(\Core\App::getInstance()->request->get("filter"), true);

        // Get the data
        $data = \Helpers\Database::getObjects('sponsors', 'rejections', $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects('sponsors', 'rejections', $fields, $search, $where, $filter);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    /**
     * Creates a new rejection
     * 
     * @return void
     */
    public function createRejection() {
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
        $new_id = \Helpers\Database::createObject('sponsors', 'rejections', $new_data, $invalid_fields);
        
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
                        'model_name' => 'rejections'
                    ]
                ]
            );
        }
    }

     /**
     * Updates a rejection
     * 
     * @param {int} $id The ID of the rejection
     * @return void
     */
    public function editRejection($id) {
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
        $success = \Helpers\Database::updateObject('sponsors', 'rejections', $id, $new_data, 'id', $invalid_fields);

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
                        'model_name' => 'rejections'
                    ]
                ]
            );
        }
    }

    /**
     * Deletes a rejection
     * 
     * @param {int} $id The ID of the rejection
     * @return void
     */
    public function deleteRejection($id) {
        // Delete the record
        $success = \Helpers\Database::deleteObject('sponsors', 'rejections', $id);

        // Send response
        \Helpers\Response::respond($success);
    }


    private function updateHasSponsored($sponsor_id) {
        if(!$sponsor_id) {
            return;
        }

        // Get sponsor
        $sponsor = \Core\Database::getInstance()->select(
            'sponsors_sponsors',
            '*',
            ['id' => $sponsor_id]
        );

        if(is_array($sponsor) && count($sponsor) > 0) {
            $sponsor = $sponsor[0];
        } else {
            return;
        }

        // Get sponsorships
        $sponsorships = \Core\Database::getInstance()->select(
            'sponsors_sponsorships',
            '*',
            ['sponsor' => $sponsor_id]
        );

        if(is_array($sponsorships) && count($sponsorships) > 0) {
            $sponsor['has_sponsored'] = 1;
        } else {
            $sponsor['has_sponsored'] = 0;
        }

        \Helpers\Database::updateObject('sponsors', 'sponsors', $sponsor_id, $sponsor);
    }


}

?>
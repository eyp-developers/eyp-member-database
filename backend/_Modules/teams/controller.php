<?php

namespace Modules;

/**
 * The Teams module
 */
class Teams extends \Core\Module {

    /**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Add additional routes for memberships
        $this->_actions['POST']['/memberships/create'] = 'createMembership';
        $this->_actions['DELETE']['/memberships/:id'] = 'deleteMembership';

        // Add additional routes for team members
        $this->_actions['GET']['/team_members/:id'] = 'team_members';
        

        // Add additional routes for member teams
        $this->_actions['GET']['/member_teams/:id'] = 'member_teams';
    }

    /**
     * Gets all members of a certain team
     *
     * @param {int} $team_id The Id of the team
     * @return void
     */
    public function team_members($team_id) {
    	// Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'team = '.$team_id;

        // Get the data
        $data = \Helpers\Database::getObjects('teams', 'memberships', $fields, $search, $where, $offset, $limit, $sort, $order);
        $count = \Helpers\Database::countObjects('teams', 'memberships', $fields, $search, $where);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    /**
     * Gets all team of a certain person
     *
     * @param {int} $person_id The Id of the person
     * @return void
     */
    public function member_teams($person_id) {
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
        $data = \Helpers\Database::getObjects('teams', 'memberships', $fields, $search, $where, $offset, $limit, $sort, $order);
        $count = \Helpers\Database::countObjects('teams', 'memberships', $fields, $search, $where);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    /**
     * Creates a new memberhip
     * 
     * @return void
     */
    public function createMembership() {
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
        $new_id = \Helpers\Database::createObject('teams', 'memberships', $new_data, $invalid_fields);
        
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
                        'module_name' => 'teams',
                        'model_name' => 'memberships'
                    ]
                ]
            );
        }
    }

    /**
     * Deletes a membership
     * 
     * @param {int} $id The ID of the record
     * @return void
     */
    public function deleteMembership($id) {
        // Delete the record
        $success = \Helpers\Database::deleteObject('teams', 'memberships', $id);

        // Send response
        \Helpers\Response::respond($success);
    }
}

?>
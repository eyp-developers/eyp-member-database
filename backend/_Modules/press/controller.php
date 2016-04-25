<?php

namespace Modules;

/**
 * The Press module
 */
class Press extends \Core\Module {

	/**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructor
        parent::__construct();

        // Add additional routes for appearances
        $this->_actions['GET']['/appearances/index'] = 'appearances';
        $this->_actions['GET']['/appearances/:id'] = 'appearance';
        $this->_actions['POST']['/appearances/create'] = 'createAppearance';
        $this->_actions['POST']['/appearances/:id'] = 'editAppearance';
        $this->_actions['DELETE']['/appearances/:id'] = 'deleteAppearance';

        // Add additional routes for medium appearances
        $this->_actions['GET']['/medium_appearances/:id'] = 'medium_appearances';

        // Add additional routes for session appearances
        $this->_actions['GET']['/event_appearances/:id'] = 'event_appearances';
    }

    public function appearances() {
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
        $data = \Helpers\Database::getObjects('press', 'appearances', $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects('press', 'appearances', $fields, $search, $where, $filter);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    public function appearance($id) {
        // Get the record
        $record = \Helpers\Database::getObject('press', 'appearances', $id);

        // Send response
        if($record === false) {
            \Helpers\Response::error(\Helpers\Response::$E_RECORD_NOT_FOUND);
        } else {
            \Helpers\Response::success($record);
        }
    }

    public function medium_appearances($medium_id) {
    	// Get pagination parameters
        $fields = \Core\App::getInstance()->request->get("fields");
        $fields = explode(",", $fields);

        $limit = \Core\App::getInstance()->request->get("limit");
        $offset = \Core\App::getInstance()->request->get("offset");
        $sort = \Core\App::getInstance()->request->get("sort");
        $order = \Core\App::getInstance()->request->get("order");
        $search = \Core\App::getInstance()->request->get("search");
        $where = 'medium = '.$medium_id;
        $filter = json_decode(\Core\App::getInstance()->request->get("filter"), true);

        // Get the data
        $data = \Helpers\Database::getObjects('press', 'appearances', $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects('press', 'appearances', $fields, $search, $where, $filter);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    public function event_appearances($event_id) {
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
        $data = \Helpers\Database::getObjects('press', 'appearances', $fields, $search, $where, $offset, $limit, $sort, $order, $filter);
        $count = \Helpers\Database::countObjects('press', 'appearances', $fields, $search, $where, $filter);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows' => $data
        ]);
    }

    public function createAppearance() {
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
        $new_id = \Helpers\Database::createObject('press', 'appearances', $new_data, $invalid_fields);
        
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
                        'module_name' => 'press',
                        'model_name' => 'appearances'
                    ]
                ]
            );
        }
    }

    public function editAppearance($id) {
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
        $success = \Helpers\Database::updateObject('press', 'appearances', $id, $new_data, 'id', $invalid_fields);

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
                        'module_name' => 'press',
                        'model_name' => 'appearances'
                    ]
                ]
            );
        }
    }

    public function deleteAppearance($id) {
        // Get the record
        $record = \Core\Database::getInstance()->select(
            'press_appearances',
            '*',
            ['id' => $id]
        );
        if(is_array($record) && count($record) > 0) {
            $record = $record[0];
        }

        // Delete the record
        $success = \Helpers\Database::deleteObject('press', 'appearances', $id);

        // Send response
        \Helpers\Response::respond($success);
    }

}

?>
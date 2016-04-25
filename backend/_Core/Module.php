<?php

namespace Core;

class Module {

    /**
     * @var {string} $_lc_classname The name of the class as a lowercase string
     */
    protected $_lc_classname;
        public function getLCName() { return $this->_lc_classname; }

    /**
     * @var {string} $_classname The name of the class as a string
     */
    protected $_classname;
        public function getName() { return $this->_classname; }

    /**
     * @var {array} $_actions All actions supported by this module
     */
    protected $_actions;
        public function getActions() { return $this->_actions; }

    /**
     * Constructs a new instance of Module
     */
    public function __construct() {
        // Generate the lowercase classname
        $this->_lc_classname = strtolower(join('', array_slice(explode('\\', get_class($this)), -1)));
        $this->_classname = join('', array_slice(explode('\\', get_class($this)), -1));

        // Set the default actions
        $this->_actions = [
            'GET' => [
                '/'=> 'index',
                '/:id' => 'view',
            ],

            'POST' => [
                '/' => 'create',
                '/:id' => 'update'
            ],

            'DELETE' => [
                '/:id' => 'delete'
            ]
        ];
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

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows'  => $data
        ]);
    }

    /**
     * Returns a full data record of the module
     * 
     * @param {int} $id The ID of the record
     * @return void
     */
    public function view($id) {
        // Get the record
        $record = \Helpers\Database::getObject($this->_lc_classname, $this->_lc_classname, $id);

        // Send response
        if($record === false) {
            \Helpers\Response::error(\Helpers\Response::$E_RECORD_NOT_FOUND);
        } else {
            \Helpers\Response::success($record);
        }
    }

    /**
     * Deletes a record of the module
     * 
     * @param {int} $id The ID of the record
     * @return void
     */
    public function delete($id) {
        // Delete the record
        $success = \Helpers\Database::deleteObject($this->_lc_classname, $this->_lc_classname, $id);

        // Send response
        \Helpers\Response::respond($success);
    }

    /**
     * Creates a new record of the module
     * 
     * @return void
     */
    public function create() {
        // Get the transmitted data
        $data = App::getInstance()->request->getBody();
        $new_data = json_decode($data, true);

        error_log(print_r($new_data, true));

        // Replace empty values with null
        foreach($new_data as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }
        }

        // Insert the data
        $invalid_fields = [];
        $new_id = \Helpers\Database::createObject($this->_lc_classname, $this->_lc_classname, $new_data, $invalid_fields);

        // Return the appropriate result
        if($new_id === false || $new_id == 0) {
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
                        'module_name' => $this->_lc_classname,
                        'model_name' => $this->_lc_classname
                    ]
                ]
            );
        }
    }

    /**
     * Updates a record of the module
     * 
     * @param {int} $id The ID of the record
     * @return void
     */
    public function update($id) {
        // Get the transmitted data
        $data = App::getInstance()->request->getBody();
        $new_data = json_decode($data, true);

        // Replace empty values with null
        foreach($new_data as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }
        }

        // Update the data
        $invalid_fields = [];
        $success = \Helpers\Database::updateObject($this->_lc_classname, $this->_lc_classname, $id, $new_data, 'id', $invalid_fields);

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
                        'module_name' => $this->_lc_classname,
                        'model_name' => $this->_lc_classname
                    ]
                ]
            );
        }
    }
}

?>
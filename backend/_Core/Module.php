<?php

namespace Core;

class Module {

    private $_lc_classname;

    /**
     * @var array $_actions All actions supported by this module
     */
    protected $_actions;
        public function getActions() { return $this->_actions; }

    /**
     * Constructs a new instance of Module
     */
    public function __construct() {
        // Generate the lowercase classname
        $this->_lc_classname = strtolower(join('', array_slice(explode('\\', get_class($this)), -1)));

        // Set the default actions
        $this->_actions = [
            'GET' => [
                '/'.$this->_lc_classname => 'index',
                '/'.$this->_lc_classname.'/:id' => 'view',
            ],

            'POST' => [
                '/'.$this->_lc_classname => 'create',
                '/'.$this->_lc_classname.'/:id' => 'update'
            ],

            'DELETE' => [
                '/'.$this->_lc_classname.'/:id' => 'delete'
            ]
        ];
    }

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

        // Get the data
        $data = \Helpers\Database::getObjects($this->_lc_classname, $this->_lc_classname, $fields, $search, $where, $offset, $limit, $sort, $order);
        $count = \Helpers\Database::countObjects($this->_lc_classname, $this->_lc_classname, $fields, $search, $where);

        echo json_encode(['total' => $count, 'rows' => $data]);
    }

    public function view($id) {
        // Get the data
        echo json_encode(\Helpers\Database::getObject($this->_lc_classname, $this->_lc_classname, $id));
    }

    public function delete($id) {
        // Get the data
        $status = \Helpers\Database::deleteObject($this->_lc_classname, $this->_lc_classname, $id);
        echo json_encode(['success' => $status]);
    }

    public function create() {
        // Get the transmitted data
        $data = App::getInstance()->request->getBody();
        $new_data = json_decode($data, true);

        // Replace empty values with null
        foreach($new_data as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }
        }

        // Insert the data
        $new_id = \Helpers\Database::createObject($this->_lc_classname, $this->_lc_classname, $new_data);

        // Return the appropriate result
        if($new_id === false) {
            echo json_encode(['success' => false]);
        } else {
            echo json_encode([
                'success' => true,
                'object_id' => $new_id,
                'db_changes' => [
                    [
                        'module_name' => $this->_lc_classname,
                        'model_name' => $this->_lc_classname
                    ]
                ]
            ]);
        }
    }

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
        $success = \Helpers\Database::updateObject($this->_lc_classname, $this->_lc_classname, $id, $new_data);

        // Return the appropriate result
        echo json_encode([
            'success' => $success,
            'db_changes' => [
                [
                    'module_name' => $this->_lc_classname,
                    'model_name' => $this->_lc_classname
                ]
            ]
        ]);
    }
}

?>
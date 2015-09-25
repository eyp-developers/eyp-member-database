<?php

namespace Modules;

/**
 * The People module
 */
class People extends \Core\Module {

	/**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructor
        parent::__construct();
    }

    /**
     * Creates a new person
     * 
     * @return void
     */
    public function create() {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $new_data = json_decode($data, true);

        // Replace empty values with null
        foreach($new_data as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }
        }

        // Set full name
        $new_data['full_name'] = $new_data['first_name'] . ' ' . $new_data['last_name'];

        // Insert the data
        $new_id = \Helpers\Database::createObject($this->_lc_classname, $this->_lc_classname, $new_data);

        // Return the appropriate result
        if($new_id === false) {
            \Helpers\Response::error(\Helpers\Response::$E_SAVE_FAILED);
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
     * Updates a person
     * 
     * @param {int} $id The ID of the record
     * @return void
     */
    public function update($id) {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $new_data = json_decode($data, true);

        // Replace empty values with null
        foreach($new_data as $key => $value) {
            if($value === '') {
                $new_data[$key] = NULL;
            }
        }

        // Set full name
        $new_data['full_name'] = $new_data['first_name'] . ' ' . $new_data['last_name'];

        // Update the data
        $success = \Helpers\Database::updateObject($this->_lc_classname, $this->_lc_classname, $id, $new_data);

        // Return the appropriate result
        if($success === false) {
            \Helpers\Response::error(\Helpers\Response::$E_RECORD_NOT_FOUND);
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
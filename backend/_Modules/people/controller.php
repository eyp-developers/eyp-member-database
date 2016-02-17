<?php

namespace Modules;

/**
 * The People module
 */
class People extends \Core\Module implements \Core\Exportable {

	/**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructor
        parent::__construct();
    }

    /**
     * Returns a full data record of a Person
     * 
     * @param {int} $id The ID of the person
     * @return void
     */
    public function view($id) {
        // Get the record
        $record = \Helpers\Database::getObject($this->_lc_classname, $this->_lc_classname, $id);

        // Add the payment status if the payment status module exists
        if(class_exists("\\Modules\\Payments")) {
            $membership_status = Payments::getMembershipStatusForPersonId($record["id"]);
            $record["membership_status"] = $membership_status;
        }

        // Send response
        if($record === false) {
            \Helpers\Response::error(\Helpers\Response::$E_RECORD_NOT_FOUND);
        } else {
            \Helpers\Response::success($record);
        }
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
        $invalid_fields = [];
        $new_id = \Helpers\Database::createObject($this->_lc_classname, $this->_lc_classname, $new_data, $invalid_fields);

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

    /**
     * Returns a description of the fields that can be exported
     *
     * @return Dictionary A dictionary of key=>value pairs describing the fields that can be exported
     */
    public function getExportFields() {
        // Specify the basic fields that can always be exported
        $fields = [
            "first_name" => "First name",
            "last_name" => "Last name",
            "email" => "Email",
            "newsletter" => "Newsletter"
        ];

        // Check if the payments module is installed
        // If so, we can also export a membership status
        if(class_exists("\\Modules\\Payments")) {
            $fields["valid_membership"] = "Valid membership";
        }

        return $fields;
    }

    /**
     * Returns the exported data
     *
     * @return Array An array of dictionaries containing the exported data
     */
    public function getExportData() {
        $people = \Helpers\Database::getObjects($this->_lc_classname, $this->_lc_classname, ["id", "first_name", "last_name", "email", "newsletter"]);

        // Check if the payments module is installed
        // If so, we can also export a membership status
        if(class_exists("\\Modules\\Payments")) {
            for($i = 0; $i < count($people); $i++) {
                $people[$i]["valid_membership"] = (Payments::getMembershipStatusForPersonId($people[$i]["id"]) >= 2);
            }
        }

        return $people;
    }
}

?>
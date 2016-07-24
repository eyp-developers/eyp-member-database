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

        // Add additional route
        $this->_actions['POST']['/import/csv'] = 'csvImport';
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

        // Create color coding
        $colors = [
            1 => 'none',
            2 => 'yellow',
            3 => 'green'
        ];

        $data = array_map(function($row) use($colors) {
            $status = Payments::getMembershipStatusForPersonId($row["id"]);
            $row['bg_color'] = $colors[$status];
            return $row;
        }, $data);

        // Send response
        \Helpers\Response::success([
            'total' => $count,
            'rows'  => $data
        ]);
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
     * Imports a list of people
     * 
     * @return void
     */
    public function csvImport() {
        // Get the transmitted data
        $data = \Core\App::getInstance()->request->getBody();
        $post_data = json_decode($data, true);

        // Load the csv file
        $filename = $post_data['file'];
        if(strpos($filename, "uploads/") !== 0 || strpos($filename, "..") === true) {
            \Helpers\Response::error(\Helpers\Response::$E_SAVE_FAILED);
            return;
        }

        $filename = '../' . $filename;

        $file = fopen($filename, "r");
        $csv_content = fread($file, filesize($filename));
        $csv_lines = explode("\n", $csv_content);

        // Get fields
        $header_fields = str_getcsv($csv_lines[0], ';');

        // Insert records
        for($line = 1; $line < count($csv_lines); $line++) {
            $new_data = array_combine($header_fields, str_getcsv($csv_lines[$line], ";"));

            // Replace empty values with null
            foreach($new_data as $key => $value) {
                if($value === '') {
                    $new_data[$key] = NULL;
                }
            }

            // Set full name
            $new_data['full_name'] = $new_data['first_name'] . ' ' . $new_data['last_name'];

            // Remember and remove the role
            $role = $new_data['role'];
            unset($new_data['role']);

            $new_id = false;

            // Check if this person already exists
            if($new_data['email'] !== NULL) {
                // Check email
                $record = \Helpers\Database::getObject($this->_lc_classname, $this->_lc_classname, $new_data['email'], 'email');
                if($record !== false && isset($record['id'])) {
                    $new_id = $record['id'];
                } else {
                    // Check name
                    $record = \Helpers\Database::getObject($this->_lc_classname, $this->_lc_classname, $new_data['first_name'] . ' ' . $new_data['last_name'], 'full_name');
                    if($record !== false && isset($record['id'])) {
                        $new_id = $record['id'];
                    }
                }
            }

            if($new_id === false) {
                // Insert the data
                $invalid_fields = [];
                $new_id = \Helpers\Database::createObject($this->_lc_classname, $this->_lc_classname, $new_data, $invalid_fields);
            }

            // Create participation
            if(intval($post_data['event']) !== 0 &&
               intval($role) !== 0 &&
               intval($new_id) !== 0) {
                $invalid_fields = [];
                $participation_id = \Helpers\Database::createObject(
                    'events',
                    'participations',
                    [
                        'person' => $new_id,
                        'role' => intval($role),
                        'event' => intval($post_data['event'])
                    ],
                    $invalid_fields
                );
            }
        }

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
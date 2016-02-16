<?php

namespace Modules;

// Load the MailChimp API
include('api/MailChimp.php');
include('api/Batch.php');
use \DrewM\MailChimp\MailChimp as MailChimpAPI;
use \DrewM\MailChimp\Batch;

/**
 * The Payments module
 */
class Mailchimp extends \Core\Module {

    /**
     * Constructs a new instance
     */
    public function __construct() {
        // Call Module constructur
        parent::__construct();

        // Add additional route for the sync call
        $this->_actions['POST']['/sync/:id'] = 'sync_list';
    }

    /**
     * Creates a new list
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

        // Get List Name from MailChimp
        $mc = new MailChimpAPI($new_data['api_key']);
        $mc_lists = $mc->get('lists');

		$new_data['name'] = '-- LIST NOT FOUND --';

		if(isset($mc_lists['lists'])) foreach($mc_lists['lists'] as $list) {
			if($list['id'] == $new_data['id']) {
				$new_data['name'] = $list['name'];
			}
		}

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
     * Updates a list of the module
     * 
     * @param {int} $id The ID of the list
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

        // Get List Name from MailChimp
        $mc = new MailChimpAPI($new_data['api_key']);
        $mc_lists = $mc->get('lists');

		$new_data['name'] = '-- LIST NOT FOUND --';

		if(isset($mc_lists['lists'])) foreach($mc_lists['lists'] as $list) {
			if($list['id'] == $new_data['id']) {
				$new_data['name'] = $list['name'];
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

    /**
     * Syncronises a list with Mailchimp
     *
     * @param {string} $list_id The id of the list
     * @return void
     */
    public function sync_list($list_id) {
        // Get the list details
        $list = \Helpers\Database::getObject('mailchimp', 'mailchimp', $list_id);
        
        error_log(print_r($list, true));

        // Return error if the list was not found
        if($list === false || count($list) === 0) \Helpers\Response::error();

        // Connect to Mailchimp API
        $mc = new MailChimpAPI($list['api_key']);
        $mc_batch = $mc->new_batch();
        
        // Add data
        $datasource_obj = \Core\App::$_modules[$list['datasource']];
        $data = $datasource_obj->getExportData();

        error_log(print_r($data, true));

        for($i = 0; $i < count($data); $i++) {
            $status = 'unsubscribed';
            if($data[$i]['newsletter'] == 2) {
                $status = 'subscribed';
            }

            $hash = md5(strtolower($data[$i][$list['email_field']]));
             
            $mc_batch->put('op'.$i, 'lists/'.$list['id'].'/members/'.$hash, [
                'email_address' => $data[$i][$list['email_field']],
                'status'        => $status,
                'merge_fields'  => [
                    'FNAME' => $data[$i][$list['first_name_field']],
                    'LNAME' => $data[$i][$list['last_name_field']]
                ]
            ]);
        }

        $result = $mc_batch->execute();

        // Send response
        \Helpers\Response::success(['result' => $result]);
    }

}

?>
<?php

namespace Helpers;

/**
 * A singleton wrapper class for the app
 */
class Response {

	/**
	 * General errors
	 */
	public static $E_GENERAL = 100;
	public static $E_INVALID_URI = 101;
	public static $E_MISSING_PERMISSION = 102;
	public static $E_NOT_LOGGED_IN = 103;
	public static $E_INVALID_LOGIN_DATA = 104;
	public static $E_NEED_SETUP = 105;

	/**
	 * Module-related errors
	 */
	public static $E_SAVE_FAILED = 200;
	public static $E_MISSING_MODULE_INFO = 201;
	public static $E_MISSING_DEPENDENCIES = 202;
	public static $E_EXISTING_DEPENDENCIES = 203;
	public static $E_RECORD_NOT_FOUND = 204;

	/**
	 * Sends a response
	 *
	 * @param {boolean} $success Whether the request was successful
	 * @param {any} $data The data to be sent (will be serialized to JSON)
	 * @return void
	 */
	public static function respond($success, $data= false) {
		$response = [
			'success'	=> $success,
			'data'		=> $data
		];

		echo json_encode($response);
	}

	/**
	 * Sends a success response
	 *
	 * @param {any} $data The data to be sent (will be serialized to JSON)
	 * @param {object} $db_changes An object describing Changes to the database
	 * @return void
	 */
	public static function success($data = false, $db_changes = false) {
		$response = [
			'success'	=> true,
			'data'		=> $data,
			'db_changes'=> $db_changes
		];

		echo json_encode($response);
	}

	/**
	 * Sends an error response
	 *
	 * @param {int} $error The error constant
	 * @param {any} $data The data to be sent (will be serialized to JSON)
	 * @return void
	 */
	public static function error($error = false, $data = false) {
		if($error === false) {
			$error = \Helpers\Response::$E_GENERAL;
		}

		$response = [
			'success'	=> false,
			'error'		=> $error,
			'data'		=> $data
		];

		echo json_encode($response);
	}
}

?>
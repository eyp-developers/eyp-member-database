<?php

namespace Helpers;

/**
 * The base class for all modules 
 */
class Module {

	/**
	 * Reads a module's JSON file
	 *
	 * @param {string} $folder_name The name of the module's folder
	 * @param {string} $file_name The name of the JSON file
	 * @return {array} The parsed json file
	 */
	public static function getModuleJSONFile($folder_name, $file_name) {
		$file_path = '_modules/'.$folder_name.'/'.$file_name.'.json';

		// Check if the file exists
		if(!file_exists($file_path)) {
			return false;
		}

		// Read the info file
		$json = file_get_contents($file_path);
        if($json === false) return false;

        // Parse JSON
        $data = json_decode($json, true);
        if($data === NULL) return false;

        return $data;
	}

	/**
	 * Reads a module's info from its info.json file
	 *
	 * @param {string} $folder_name The name of the module's folder
	 * @return {array} The parsed json file
	 */
	public static function getModuleInfo($folder_name) {
        return self::getModuleJSONFile($folder_name, 'info');
	}

	/**
	 * Reads a module's model from its model.json file
	 *
	 * @param {string} $folder_name The name of the module's folder
	 * @return {array} The parsed json file
	 */
	public static function getModuleModel($folder_name) {
		return self::getModuleJSONFile($folder_name, 'model');
	}

	/**
	 * Reads a module's view config from its view.json file
	 *
	 * @param {string} $folder_name The name of the module's folder
	 * @return {array} The parsed json file
	 */
	public static function getModuleViews($folder_name) {
		return self::getModuleJSONFile($folder_name, 'views');
	}

	/**
	 * Reads a module's store config from its stores.json file
	 *
	 * @param {string} $folder_name The name of the module's folder
	 * @return {array} The parsed json file
	 */
	public static function getModuleStores($folder_name) {
		return self::getModuleJSONFile($folder_name, 'stores');
	}

	/**
	 * Reads a module's data from its data.json file
	 *
	 * @param {string} $folder_name The name of the module's folder
	 * @return {array} The parsed json file
	 */
	public static function getModuleData($folder_name) {
		return self::getModuleJSONFile($folder_name, 'data');
	}

}

?>
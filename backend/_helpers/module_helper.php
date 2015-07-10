<?php

class ModuleHelper {

	/**
	 * Reads a module's JSON file
	 *
	 * @param folder_name The name of the module's folder
	 * @param file_name The name of the JSON file
	 * @return An array containing the JSON data
	 */
	public static function getModuleJSONFile($folder_name, $file_name) {
		// Read the info file
		$json = file_get_contents('_modules/'.$folder_name.'/'.$file_name.'.json');
        if($json === false) return false;

        // Parse JSON
        $data = json_decode($json, true);
        if($data === NULL) return false;

        return $data;
	}

	/**
	 * Reads a module's info from its info.json file
	 *
	 * @param folder_name The name of the module's folder
	 * @return An array containing the module's info
	 */
	public static function getModuleInfo($folder_name) {
        return ModuleHelper::getModuleJSONFile($folder_name, 'info');
	}

	/**
	 * Reads a module's model from its model.json file
	 *
	 * @param folder_name The name of the module's folder
	 * @return An array containing the module's model
	 */
	public static function getModuleModel($folder_name) {
		return ModuleHelper::getModuleJSONFile($folder_name, 'model');
	}

	/**
	 * Reads a module's view config from its view.json file
	 *
	 * @param folder_name The name of the module's folder
	 * @return An array containing the module's view config
	 */
	public static function getModuleViews($folder_name) {
		return ModuleHelper::getModuleJSONFile($folder_name, 'views');
	}

}

?>
<?php

class ModuleHelper {

	/**
	 * Reads a module's info from its info.json file
	 *
	 * @param folder_name The name of the module's folder
	 * @return An array containing the module's info
	 */
	public static function getModuleInfo($folder_name) {
		// Read the info file
		$module_info_json = file_get_contents('_modules/'.$folder_name.'/info.json');
        if($module_info_json === false) return false;

        // Parse JSON
        $module_info = json_decode($module_info_json);
        if($module_info === NULL) return false;

        return $module_info;
	}

	/**
	 * Reads a module's model from its model.json file
	 *
	 * @param folder_name The name of the module's folder
	 * @return An array containing the module's model
	 */
	public static function getModuleModel($folder_name) {
		// Read the info file
		$module_model_json = file_get_contents('_modules/'.$folder_name.'/model.json');
        if($module_model_json === false) return false;

        // Parse JSON
        $module_model = json_decode($module_model_json);
        if($module_model === NULL) return false;

        return $module_model;
	}

}

?>
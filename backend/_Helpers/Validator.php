<?php

namespace Helpers;

/**
 * The base class for all modules 
 */
class Validator {

	/**
	 * Validates a value for a given field
	 *
	 * @param {array} $value The value to be validated
	 * @param {array} $field_config The field to validate against
	 * @return {boolean} Whether the value is valid
	 */
	public static function validateValueForField($value, $field_config) {
		// Check if the field is generated
		if($field_config['generated'] == 1) {
			return true;
		}

		// Check if the field is required
		if($field_config['required'] == 1) {
			if($value=== null || $value == '') {
				return false;
			} 
		} else {
			if($value === null || $value == '') {
				return true;
			}
		}

		// Validate data type
		switch($field_config['type']) {
			case 'string':
				return strlen($value) < 1000;

			case 'short_string':
				return strlen($value) < 200;

			case 'int':
				return is_numeric($value) && is_int($value + 0);

			case 'date':
				return preg_match('/\d{4}-[01]\d-[0-3]\d$/', $value);

			case 'decimal':
				return is_int($value) || is_float($value) || is_numeric(str_replace(',', '.', $value));
		}

		return true;
	}

	/**
	 * Validates values for a given module and model
	 *
	 * @param {array} $data The data to be validated
	 * @param {array} $module_name The module to validate against
	 * @param {array} $model_name The model to validate against
	 * @param {array} &$invalid_field An array that will be populated with the names of all invalid fields
	 * @return {array} The parsed json file
	 */
	public static function validateDataForModuleAndModel($data, $module_name, $model_name, &$invalid_fields = []) {
		// Get Model config
		$model = \Helpers\Database::getModuleModel($module_name, $model_name);

		if(!$model) {
			return false;
		}

		// Iterate over all fields and validate them
		foreach($model as $field_name => $field_config) {
			$value = null;
			if(isset($data[$field_name])) {
				$value = $data[$field_name];
			}
			if(!static::validateValueForField($value, $field_config)) {
				$invalid_fields[] = $field_name;
			}
		}

		if(count($invalid_fields) > 0) {
			return false;
		}

        return true;
	}

}

?>
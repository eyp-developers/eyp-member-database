<?php

namespace Core;

/**
 * An interface to specify the functionality of exportable modules
 */
interface Exportable {

	/**
     * Returns a description of the fields that can be exported
     *
     * @return Dictionary A dictionary of key=>value pairs describing the fields that can be exported
     */
	public function getExportFields();

	/**
     * Returns the exported data
     *
     * @return Array An array of dictionaries containing the exported data
     */
	public function getExportData();
	
}
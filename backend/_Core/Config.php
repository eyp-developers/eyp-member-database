<?php

namespace Core;

/**
 * A class to store the app's server-side configuration
 */
class Config {

	public static $db_connection = [
		'database_type' => 'mysql',
		'database_name' => 'eyp_md',
		'server' => 'localhost',
		'username' => 'eyp_md',
		'password' => 'eyp_md',
		'charset' => 'utf8'
	];
}
?>
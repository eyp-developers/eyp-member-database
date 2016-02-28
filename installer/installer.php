<?php 

require '../backend/_Core/Config.php';
require '../backend/Medoo/medoo.min.php';

// Connect to db
try {
	$db = new \medoo(\Core\Config::$db_connection);
} catch (Exception $e) {
	echo json_encode(['success' => false, 'message' => 'Could not connect to database']);
	die;
}

// Execute setup sql
$install_sql = file_get_contents('database.sql');

$result = $db->query($install_sql);

/* Insert intallation data */
$db->query("SHOW TABLES;");
$db->query("INSERT INTO core_modules VALUES('modules', 'Modules', NULL, 'A module to manage all other modules', 1, 1, 1, 1, 0);");
$db->query("INSERT INTO core_modules VALUES('auth', 'Authentication', NULL, 'A module to handle authentication', 1, 1, 2, 1, 0);");
$db->query("INSERT INTO core_modules VALUES('files', 'Files', NULL, 'A module to handle file uploads', 1, 1, 2, 1, 0);");

/* Create admin user */
$db->query("CALL proc_createUser('admin', '\$2y\$10\$GvGoYPzIJhhvj4rRy1AgG./zUL.WtYySOFvIStFw8BRfaeOFzDWem', 'Administrator', 2);");

/* Create core stores */
$db->query("INSERT INTO core_stores VALUES('exportable', 'modules', 'exportable', 'id', 'name');");

if($result === false) {
	echo json_encode(['success' => false, 'message' => 'Could not set up database']);
} else {
	echo json_encode(['success' => true, 'result' => $result, 'sql' => $install_sql]);
}

?>
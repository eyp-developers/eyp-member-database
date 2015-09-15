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

if($result === false) {
	echo json_encode(['success' => false, 'message' => 'Could not set up database']);
} else {
	echo json_encode(['success' => true, 'result' => $result, 'sql' => $install_sql]);
}

?>
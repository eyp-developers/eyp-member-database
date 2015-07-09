<?php

$app = new \Slim\Slim(array(
    'debug' => true,
    'mode' => 'development'
));

// Load Modules
$enabled_modules = Database::getInstance()->select('core_modules', "*", ['enabled' => true]);

foreach($enabled_modules as $module) {
	$short_name = $module["short_name"];

	// Load module
	require("_modules/$short_name/controller.php");

	// Initialize class
	$module_classname = ucfirst($short_name);
	$module = new $module_classname();

	// Set up routes for this module
	if(isset($module->actions['GET'])) {
		foreach($module->actions['GET'] as $action => $handler) {
			$app->get($action, [$module, $handler]);
		}
	}
	if(isset($module->actions['POST'])) {
		foreach($module->actions['POST'] as $action => $handler) {
			$app->post($action, [$module, $handler]);
		}
	}
}

// Set response type to JSON
header('Content-Type', 'application/json');

// Start the App
$app->run();


?>
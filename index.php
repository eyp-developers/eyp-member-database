<?php

// Load database
require 'database.php';

// Load app
require 'app.php';

// Load helpers
require '_helpers/helpers.php';

// Initialize all modules
App::loadModules();

// Start the app
App::run();

?>
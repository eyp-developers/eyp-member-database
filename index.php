<?php

// Load Slim framework
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

// Load helpers
require '_helpers/helpers.php';

// Load database
require 'database.php';

// Load app
require 'app.php';

?>
<?php

namespace Core;

class Auth extends \Slim\Middleware {

	private $public_resources = [
		'/auth/login'
	];

	public function call() {
		$resourceUri = $this->app->request->getResourceUri();

		// Check if we are accessing a public ressource
		if(in_array($resourceUri, $this->public_resources)) {
			$this->next->call();
			return;
		}

		// Get the token from the query
		$authToken = $this->app->request->headers->get('AuthToken');

		// Check if the token is valid
		if(\Core\User::authenticate($authToken)) {

			// Check if the User has access to this module
			$is_write = $authToken = !$this->app->request->isGet();

			$module_name = explode('/', $resourceUri)[1];

			$has_permission = false;
			if($is_write) {
				$has_permission = \Core\User::getInstance()->canWriteModule($module_name);
			} else {
				$has_permission = \Core\User::getInstance()->canReadModule($module_name);
			}

			error_log("Result was " . ($has_permission ? "true" : "false"));

			if($has_permission) {
				$this->next->call();
				return;
			} else {
				echo json_encode(['success' => false, 'missing_permission' => true]);
				return;
			}
			
		} else {
			echo json_encode(['success' => false, 'need_login' => true]);
			return;
		}
	}
}
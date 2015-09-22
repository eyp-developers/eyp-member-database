<?php

namespace Core;

/**
 * A middleware to handle authentication
 */
class Auth extends \Slim\Middleware {

	/**
	 * @var {array} $_public_resources A list of all public resources (URIs that are available without authentication)
	 */
	private $_public_resources = [
		'/auth/login'
	];

	/**
	 * Performs the authentication
	 *
	 * @return void
	 */
	public function call() {
		// Get the URI of the resource
		$resource_uri = $this->app->request->getResourceUri();

		// Check if we are accessing a public ressource
		if(in_array($resource_uri, $this->_public_resources)) {
			$this->next->call();
			return;
		}

		// Get the token from the query
		$auth_token = $this->app->request->headers->get('auth_token');

		// Check if the token is valid
		if(\Core\User::authenticate($auth_token)) {

			// Check if the User wants to write
			$is_write = !($this->app->request->isGet() || $this->app->request->isHead());

			// Parse the resource URI
			if(strpos($resource_uri, '/') !== 0) {
				\Helpers\Response::error(\Helpers\Response::$E_INVALID_URI);
				return;
			}

			$resource_uri = substr($resource_uri, 1);
			$uri_parts = explode('/', $resource_uri);

			if(count($uri_parts) === 0) {
				\Helpers\Response::error(\Helpers\Response::$E_INVALID_URI);
				return;
			}

			$module_name = $uri_parts[0];

			// Check if the user has the appropriate permissions
			$has_permission = false;
			if($is_write) {
				$has_permission = \Core\User::getInstance()->canWriteModule($module_name);
			} else {
				$has_permission = \Core\User::getInstance()->canReadModule($module_name);
			}

			if($has_permission) {
				$this->next->call();
				return;
			} else {
				\Helpers\Response::error(\Helpers\Response::$E_MISSING_PERMISSION);
				return;
			}
			
		} else {
			\Helpers\Response::error(\Helpers\Response::$E_NOT_LOGGED_IN);
			return;
		}
	}
}
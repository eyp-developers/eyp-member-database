<?php

namespace Core;

class Auth extends \Slim\Middleware {

	public function call() {
		// Get the token from the query
		$authToken = $this->app->request->headers->get('AuthToken');

		if(\Core\User::authenticate($authToken) ||
		  	$this->app->request->getResourceUri() === '/auth/login') {
			$this->next->call();
		} else {
			echo json_encode(['success' => false, 'need_login' => true]);
			return;
		}
	}
}
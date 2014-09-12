<?php

require_once('Slim/Middleware.php');

class OAuthVerifyMiddleware extends \Slim\Middleware
{
	public function call() {
		
		
		// Check if the user is logged in to your oauth system.
		if( false ) {
			returnError("You must be logged into to use the API", 401, "user_not_logged_in");
			return;
		}
		// this line is required for the application to proceed
		$this->next->call();
	}
}
<?php

use \Slim\Slim as Slim;

// User Endpoints

$app->get('/user/add', 'addUser');
$app->get('/user/get', 'getUser');

function addUser() {
	$db = getDB();
	$app = Slim::getInstance();
	$userName = $app->request()->params('user_name');
	$nameFirst = $app->request()->params('name_first');
	$nameLast = $app->request()->params('name_last');
	
	// used for degugging if desired.
	$startTime = time();

	$results = Users::addUser($db, $userName, $nameFirst, $nameLast);
	if(!$results) {
		return;
	}

	// User ID is private so don't send it back to the client
	unset($results->user_id);

	sendResponse($results, $startTime);
}

function getUser() {
	$db = getDB();
	$app = Slim::getInstance();
	
	// used for degugging if desired.
	$startTime = time();

	$results = Users::getUser($db);
	if(!$results) {
		return;
	}

	// User ID is private so don't send it back to the client
	unset($results->user_id);

	sendResponse($results, $startTime);
}

?>
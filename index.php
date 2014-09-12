<?php
/**
 * Sample REST API in PHP using Slim Framework and Monolog
 * 
 * @author      Earl Swigert Josh Lockhart <info@slimframework.com>
 * @copyright   2014 Earl Swigert
 * @version     1.0
 *
 * Both the Slim Framework and Monolog are licenced under the MIT
 * license and are copyrights of their respective owners.
 * 
 * The MIT License (MIT)
 * 
 * Copyright (c) 2014 Earl Swigert
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
session_start();

date_default_timezone_set('UTC');

// App Settings and Slim
require_once('Config.php');
require_once('Slim/Slim.php');

// Logging
require_once('Psr/Log/InvalidArgumentException.php');
require_once('Psr/Log/LoggerInterface.php');
require_once('Monolog/Logger.php');
require_once('Monolog/Handler/HandlerInterface.php');
require_once('Monolog/Handler/AbstractHandler.php');
require_once('Monolog/Handler/AbstractProcessingHandler.php');
require_once('Monolog/Handler/StreamHandler.php');
require_once('Monolog/Handler/LogglyHandler.php');
require_once('Monolog/Handler/BrowserConsoleHandler.php');
require_once('Monolog/Processor/WebProcessor.php');
require_once('Log/MonologWriter.php');

// App files
require_once('commands/Users.php');
//require_once('middleware/OAuthVerifyMiddleware.php');

use \Slim\Slim as Slim;

Slim::registerAutoloader();

// You can add an environment variable to your local php.ini call APPLICATION_ENV and set it to 'dev'.  This allows you to have the settings of the system change from Dev to Stage or Production based on if it's on your local machine or on a server.  This is helpful when you don't want to remember to turn debugging on and off in your app, and you have database settings that change depending on if it's on the server or not.
//$appMode = getenv("APPLICATION_ENV");
$appMode = "dev";

if($appMode !== 'dev') {
	$appMode = 'prod';
}

// This logger will write a file for each day of logs.
$logHandlers = array(new \Monolog\Handler\StreamHandler('./logs/'.date('Y-m-d').'.log'));
        
if($appMode !== 'dev') {
	//$logHandlers[]= new \Monolog\Handler\LogglyHandler('loggly api key');
}

$logger = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
    'handlers' => $logHandlers,
    'processors' => array(new \Monolog\Processor\WebProcessor($_SERVER))
));

$app = new Slim(array(
    'mode' => $appMode,
    'log.writer' => $logger,
    'templates.path' => './views'
));

// Only invoked if mode is "prod"
$app->configureMode('prod', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => false
    ));
});

// Only invoked if mode is "dev"
$app->configureMode('dev', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'debug' => true
    ));
});

$config = Config::getInstance();
$config->setMode($appMode === 'dev' ? Config::DEVELOPMENT : Config::STAGING);

$app->contentType('application/json');
//$app->add(new OAuthVerifyMiddleware());

include 'routes/user.php';

/// ------------------------- ///
/// --- Utility Functions --- ///
/// ------------------------- ///


/**
 * Returns a formatted response to the requesting client.
 * @method sendResponse
 * @param  mixed       $results   The results could be either an array or stdClass.  Should be the data being returned to the client.
 * @param  integer       $startTime Time the app started processing the response.  Used for debugging time on the server during development.
 */
function sendResponse($results, $startTime = null) {
	$app = Slim::getInstance();

	$details = new stdClass();

	if(!is_null($startTime)) {
		$elapsedTime = time() - $startTime;
		$details->elapsedTime = $elapsedTime;
	}

	$details->responseCode = 200;

	$response = array("details"=>$details, "response"=>$results);

	$resp = $app->response;
	$resp->setStatus(200);
	$resp->headers->set('Content-Type', 'application/json');
	try {
		$resp->setBody(json_encode($response));
	} catch (Exception $e) {
		returnError($e->getMessage());
	}
}


/**
 * Returns an error from the application if something went wrong.
 * @method returnError
 * @param  String      $e           	Description of the error
 * @param  integer     $errorCode   	HTTP Error Code
 * @param  integer     $specialCode 	Special error code for internal use in the API.  Used to communicate special data to your front end app if needed.
 */
function returnError($e, $errorCode = 403, $specialCode = null) {
	$app = Slim::getInstance();
	$app->response()->status($errorCode);
	$app->response()->header('X-Status-Reason', $e);
	if($specialCode) {
		$error = new stdClass();
		$error->code = $specialCode;
		$error->message = $e;
	}
	else {
		$error = $e;
	}

	$app->log->error("CODE: " . $errorCode . " : " .$e);
	
	$app->response()->setBody(json_encode(array("error"=>$error)));
	return;
}

$app->run();

/**
 * Retrieve database connection.  This uses the settings in Config.php to connect to the database.
 * @method getDB
 * @return PDO The PDO instance of the database connection.
 */
function getDB() {
	$config = Config::getInstance();
	$dbSettings = $config->getDbSettings();
	$db = getDBFromOptions($dbSettings['host'], $dbSettings['user'], $dbSettings['pass']);
	return $db;
}

/**
 * Returns the PDO instance of the database connection from settings.
 * @method getDBFromOptions
 * @param  string           $host 	mysql host
 * @param  string           $user 	user name
 * @param  string           $pass 	password
 * @return PDO                 		Returns a PDO instance of the database connection.
 */	
function getDBFromOptions($host, $user, $pass) {
	$db = new PDO($host, $user, $pass, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));

	return $db;
}
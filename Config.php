<?php

final class Config {

	const DEVELOPMENT = "dev";
	const STAGING = "stage";
	const PRODUCTION = "prod";

	private $mode = "";

	private static $instance;

	private function __construct() {
		$mode = self::DEVELOPMENT;
		$this->setMode($mode);
	}

	public static function getInstance() {
		if(!isset(self::$instance)) {
			self::$instance = new Config();
		}
		
		return self::$instance;
	}

	public function setMode($mode) {
		$this->mode = $mode;
	}


	public function getDbSettings() {
		switch($this->mode) {
			case self::DEVELOPMENT:
				// Example Usage
				// $host = 'mysql:host=localhost;dbname=ExampleDatabase;charset=utf8';
				// $user = 'root';
				// $pass = '';
				$host = 'mysql:host=localhost;dbname=;charset=utf8';
				$user = '';
				$pass = '';
				break;
			case self::STAGING: 
				$host = 'mysql:host=localhost;dbname=;charset=utf8';
				$user = '';
				$pass = '';
				break;
			case self::PRODUCTION: 
				$host = 'mysql:host=localhost;dbname=;charset=utf8';
				$user = '';
				$pass = '';
				break;
		}

		return array('host'=>$host, 'user'=>$user, 'pass'=>$pass);
	}



}
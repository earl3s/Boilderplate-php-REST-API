<?php 

require_once('QueryUtils.php');

class Users {

	public static function addUser($db, $userName, $nameFirst, $nameLast) {

		/// Your code to add users would go here.
		$resp = new stdClass();

		return $resp;
	}

	public static function getUser($db) {

		$userName = $_SESSION['user_name'];

		// Check for existing user
		$getUserQuery = "SELECT user_id, user_name, name, avatar FROM user WHERE user_name = :userName";
		$statement = $db->prepare($getUserQuery);
		$statement->execute(array(":userName"=>$userName));
		$results = $statement->fetch(PDO::FETCH_OBJ);

		if(!$results) {
			returnError("User is not in the system.", 401, 'user_not_added');
			return;
		}

		$results->user_id = intval($results->user_id);
		
		return $results;
	}

}
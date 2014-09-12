<?php

class QueryUtils {

	// Used for parsing out paramaters to send to PDO
	public static function createParams($filters) {
		$params = array();

		foreach($filters as $key => $value) {
			if($value !== false && $value !== null) {
				$params[":".$key] = $value;
			}
		}

		return $params;
	}

	// Gets the universal filters for endpoints and sets defaults
	public static function getFilters() {
		$filters = new stdClass();

		$filters->offset = array_key_exists('offset', $_GET) ? intval($_GET['offset']) : 0;
		$filters->count = array_key_exists('count', $_GET) ? intval($_GET['count']) : 100;

		return $filters;
	}

	/// Prep functions

	public static function prepResults($results) {
		if(is_array($results)) {
			foreach($results as $result) {
				if(is_array($result)) {
					$result = prepResults($result);
				}
				else {
					$result = self::cleanResult($result);
				}
			}
		}
		else if(is_object($results)) {
			$results = self::cleanResult($results);
		}

		return $results;
	}

	private static function cleanResult($result) {
		if(property_exists($result, 'active')) {
			$result->active = !!intval($result->active);
		}

		/// Function allows you to make sure all types are corrected before being sent to the client


		return $result;
	}
}
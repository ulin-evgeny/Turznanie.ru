<?php

defined('SYSPATH') OR die('No direct script access.');

class URL extends Kohana_URL {

	public static function query(array $params = NULL, $use_get = TRUE) {
		if ($use_get) {
			if ($params === NULL) {
				// Use only the current parameters
				$params = $_GET;
			} else {
				// Merge the current and new parameters
				$params = Arr::merge($_GET, $params);
			}
		}

		if (empty($params)) {
			// No query parameters
			return '';
		}

		$not_empty_params = array_filter($params);

		// Note: http_build_query returns an empty string for a params array with only NULL values
		$query = http_build_query($not_empty_params, '', '&');

		// Don't prepend '?' to an empty string
		return ($query === '') ? strtok($_SERVER["REQUEST_URI"], '?') : ('?' . $query);
	}

}

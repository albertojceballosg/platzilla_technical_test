<?php

	/**
	 * Smarty serialize modifier plugin
	 *
	 * Note: This modifier plugin has been created only for Platzilla software, it will be use to pass a
	 * associative array in single string format
	 * Type: modifier
	 * Name: custom_serialize
	 * Purpose: convert associative array in single string
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	function smarty_modifier_custom_serialize ($array) {
		if (!is_array ($array) || empty ($array)) {
			return '';
		}
		$results = '';
		foreach ($array as $key => $value) {
			if (!$value) {
				continue;
			} else if (empty ($results)) {
				$results = $key;
			} else {
				$results .= '@' . $key;
			}
		}
		return $results;
	}

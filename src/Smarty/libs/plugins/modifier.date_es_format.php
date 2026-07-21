<?php

	/**
	 * Smarty date time modifier plugin
	 *
	 * Note: This modifier plugin has been created only for Platzilla
	 * Type: modifier
	 * Name: module_pluralize
	 * Purpose: convert to numerical date time (2023-08-15 14:26:08) humanized date time
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	function smarty_modifier_date_es_format ($strDate) {
		if (
			empty ($strDate) ||
			!is_string ($strDate) ||
			$strDate == '0000-00-00 00:00:00' ||
			$strDate == ' '
		) {
			return $strDate;
		}
		$pos    = strpos ($strDate,':');
		if ($pos === false) {
			$format = '%A %d de %B - %Y';
		} else {
			$format = '%A %d de %B - %Y, %H:%M:%S';
		}
		setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
		return ucwords (utf8_encode (strftime ($format, strtotime ($strDate))));
	}

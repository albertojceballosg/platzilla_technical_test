<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once 'include/utils/utils.php';

class DateTimeField {

	static protected $databaseTimeZone = null;
	protected $datetime;
	private static $cache = array();

	/**
	 * DateTimeField constructor.
	 *
	 * @param $value
	 */
	public function __construct($value) {
		if(empty($value)) {
			$value = date("Y-m-d H:i:s");
		}
		$this->date = null;
		$this->time = null;
		$this->datetime = $value;
	}

	/** Function to set date values compatible to database (YY_MM_DD)
	 * @param $user -- value :: Type Users
	 * @returns $insert_date -- insert_date :: Type string
	 */
	function getDBInsertDateValue($user = null) {
		global $log;
		$log->debug("Entering getDBInsertDateValue(" . $this->datetime . ") method ...");
		$value = explode(' ', $this->datetime);
		if (count($value) == 2) {
			$value[0] = self::convertToUserFormat($value[0]);
		}

		$insert_time = '';
		if ($value[1] != '') {
			$date = self::convertToDBTimeZone($this->datetime, $user);
			$insert_date = $date->format('Y-m-d');
		} else {
			$insert_date = self::convertToDBFormat($value[0]);
		}
		$log->debug("Exiting getDBInsertDateValue method ...");
		return $insert_date;
	}

	/**
	 *
	 * @param Users $user
	 * @return String
	 */
	public function getDBInsertDateTimeValue($user = null) {
		return $this->getDBInsertDateValue($user) . ' ' .
				$this->getDBInsertTimeValue($user);
	}

	public function getDisplayDateTimeValue ($user = null) {
		return $this->getDisplayDate($user) . ' ' . $this->getDisplayTime($user);
	}

	/**
	 *
	 * @global Users $current_user
	 * @param type $date
	 * @param Users $user
	 * @return type
	 */
	public static function convertToDBFormat($date, $user = null) {
		global $current_user;
		if(empty($user)) {
			$user = $current_user;
		}

		$format = $user->date_format;
		if(empty($format)) {
			$format = 'dd-mm-yyyy';
		}

		return self::__convertToDBFormat($date, $format);
	}

	/**
	 *
	 * @param type $date
	 * @param string $format
	 * @return string
	 */
	public static function __convertToDBFormat($date, $format) {
		// Validar que la fecha no esté vacía
		if (empty($date) || !is_string($date)) {
			$date = strval($date);
		}
		$date = trim($date);
		if (empty($date)) {
			return '';
		}

		if ($format == '' || empty($format)) {
			$format = 'dd-mm-yyyy';
		}
		
		// Si la fecha ya está en formato YYYY-MM-DD, devolverla directamente
		if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
			return $date;
		}
		
		// Determinar el separador usado en la FECHA recibida (no en el formato)
		$dateSeparator = '-';
		if (strpos($date, '/') !== false) {
			$dateSeparator = '/';
		} elseif (strpos($date, '.') !== false) {
			$dateSeparator = '.';
		}
		
		// Normalizar el formato para comparación (ignorar separador)
		$formatNormalized = str_replace(array('/', '.'), '-', strtolower($format));
		
		$dbDate = '';
		$parts = explode($dateSeparator, $date);
		
		if (count($parts) !== 3) {
			return '';
		}
		
		if ($formatNormalized == 'dd-mm-yyyy') {
			list($d, $m, $y) = $parts;
		} elseif ($formatNormalized == 'mm-dd-yyyy') {
			list($m, $d, $y) = $parts;
		} elseif ($formatNormalized == 'yyyy-mm-dd') {
			list($y, $m, $d) = $parts;
		} else {
			// Formato no reconocido, intentar detectar automáticamente
			// Si el primer elemento tiene 4 dígitos, es YYYY-MM-DD
			if (strlen($parts[0]) == 4) {
				list($y, $m, $d) = $parts;
			} else {
				// Asumir DD-MM-YYYY por defecto
				list($d, $m, $y) = $parts;
			}
		}

		if (!$y && !$m && !$d) {
			$dbDate = '';
		} else {
			// Asegurar formato correcto con padding de ceros
			$y = str_pad($y, 4, '0', STR_PAD_LEFT);
			$m = str_pad($m, 2, '0', STR_PAD_LEFT);
			$d = str_pad($d, 2, '0', STR_PAD_LEFT);
			$dbDate = $y . '-' . $m . '-' . $d;
		}
		return $dbDate;
	}

	/**
	 *
	 * @param Mixed $date
	 * @return Array
	 */
	public static function convertToInternalFormat($date) {
		if(!is_array($date)) {
			$date = explode(' ', $date);
		}
		return $date;
	}

	/**
	 *
	 * @param string $date
	 * @param Users $user
	 * @return string
	 */
	public static function convertToUserFormat($date, $user = null) {
		global $current_user;
		if(empty($user)) {
			$user = $current_user;
		}
		$format = $user->date_format;
		if(empty($format)) {
			$format = 'dd-mm-yyyy';
		}
		return self::__convertToUserFormat($date, $format);
	}

	/**
	 *
	 * @param string $date
	 * @param string $format
	 * @return string
	 */
	public static function __convertToUserFormat($date, $format) {
		$date = self::convertToInternalFormat($date);
		
		// Validar que $date[0] no esté vacío y tenga el formato correcto
		if (empty($date[0]) || trim($date[0]) == '') {
			return '';
		}
		
		$parts = explode('-', $date[0]);
		if (count($parts) !== 3) {
			return $date[0]; // Retornar tal cual si no tiene el formato esperado
		}
		
		list($y, $m, $d) = $parts;
		
		// Validar que las partes no estén vacías
		if (empty($y) || empty($m) || empty($d)) {
			return $date[0]; // Retornar tal cual si alguna parte está vacía
		}

		// Determinar el separador según el formato
		$separator = (strpos($format, '/') !== false) ? '/' : '-';

		if ($format == 'dd-mm-yyyy' || $format == 'dd/mm/yyyy') {
			$date[0] = $d . $separator . $m . $separator . $y;
		} elseif ($format == 'mm-dd-yyyy' || $format == 'mm/dd/yyyy') {
			$date[0] = $m . $separator . $d . $separator . $y;
		} elseif ($format == 'yyyy-mm-dd' || $format == 'yyyy/mm/dd') {
			$date[0] = $y . $separator . $m . $separator . $d;
		}
		if (isset($date[1]) && $date[1] != '') {
			$userDate = $date[0] . ' ' . $date[1];
		} else {
			$userDate = $date[0];
		}
		return $userDate;
	}

	/**
	 * @param string $value
	 * @param Users $user
	 *
	 * @return DateTime
	 */
	public static function convertToUserTimeZone($value, $user = null ) {
		global $current_user;
		if(empty($user)) {
			$user = $current_user;
		}
		$timeZone = $user->time_zone?$user->time_zone:'UTC';
		return DateTimeField::convertTimeZone($value, self::getDBTimeZone(), $timeZone);
	}

	/**
	 *
	 * @global Users $current_user
	 * @param type $value
	 * @param Users $user
	 */
	public static function convertToDBTimeZone( $value, $user = null ) {
		global $current_user;
		if(empty($user)) {
			$user = $current_user;
		}
		$timeZone = $user->time_zone;
		$value = self::sanitizeDate($value, $user);
		return DateTimeField::convertTimeZone($value, $timeZone, self::getDBTimeZone() );
	}

	/**
	 *
	 * @param type $time
	 * @param type $sourceTimeZoneName
	 * @param type $targetTimeZoneName
	 * @return DateTime
	 */
	public static function convertTimeZone($time, $sourceTimeZoneName, $targetTimeZoneName) {
		// TODO Caching is causing problem in getting the right date time format in Calendar module.
		// Need to figure out the root cause for the problem. Till then, disabling caching.
		//if(empty(self::$cache[$time][$targetTimeZoneName])) {
			// create datetime object for given time in source timezone
			$sourceTimeZone = new DateTimeZone($sourceTimeZoneName);
			if($time == '24:00') $time = '00:00';
			$myDateTime = new DateTime($time, $sourceTimeZone);

			// convert this to target timezone using the DateTimeZone object
			$targetTimeZone = new DateTimeZone($targetTimeZoneName);
			$myDateTime->setTimeZone($targetTimeZone);
			self::$cache[$time][$targetTimeZoneName] = $myDateTime;
		//}
		$myDateTime = self::$cache[$time][$targetTimeZoneName];
		return $myDateTime;
	}

	/** Function to set timee values compatible to database (GMT)
	 * @param $user -- value :: Type Users
	 * @returns $insert_date -- insert_date :: Type string
	 */
	function getDBInsertTimeValue($user = null) {
		global $log;
		$log->debug("Entering getDBInsertTimeValue(" . $this->datetime . ") method ...");
		$date = self::convertToDBTimeZone($this->datetime, $user);
		$log->debug("Exiting getDBInsertTimeValue method ...");
		return $date->format("H:i:s");
	}

	/**
	 * This function returns the date in user specified format.
	 *
	 * @param Users $user
	 *
	 * @return string
	 */
	function getDisplayDate( $user = null ) {
		global $log;
		$log->debug("Entering getDisplayDate(" . $this->datetime . ") method ...");

		$date_value = explode(' ',$this->datetime);
		if ($date_value[1] != '') {
			$date = self::convertToUserTimeZone($this->datetime, $user);
			$date_value = $date->format('Y-m-d');
		}

		$display_date = self::convertToUserFormat($date_value);
		$log->debug("Exiting getDisplayDate method ...");
		return $display_date;
	}

	function getDisplayTime( $user = null , $format = 'H:i:s') {
		global $log;
		$log->debug("Entering getDisplayTime(" . $this->datetime . ") method ...");
		$date = self::convertToUserTimeZone($this->datetime, $user);
		$time = $date->format($format);
		$log->debug("Exiting getDisplayTime method ...");
		return $time;
	}

	static function getDBTimeZone() {
		if(empty(self::$databaseTimeZone)) {
			$defaultTimeZone = date_default_timezone_get();
			if(empty($defaultTimeZone)) {
				$defaultTimeZone = 'UTC';
			}
			self::$databaseTimeZone = $defaultTimeZone;
		}
		return self::$databaseTimeZone;
	}

	static function getPHPDateFormat( $user = null) {
		global $current_user;
		if(empty($user)) {
			$user = $current_user;
		}
		return str_replace(array('yyyy', 'mm','dd'), array('Y', 'm', 'd'), $user->date_format);
	}

	private static function sanitizeDate($value, $user) {
		global $current_user;
		if(empty($user)) {
			$user = $current_user;
		}

		if($user->date_format == 'mm-dd-yyyy') {
			list($date, $time) = explode(' ', $value);
			if(!empty($date)) {
				list($m, $d, $y) = explode('-', $date);
				if(strlen($m) < 3) {
					$time = ' '.$time;
					$value = "$y-$m-$d".rtrim($time);
				}
			}
		}
		return $value;
	}

	/**
	 * This function returns the date in user specified format.
	 * limitation is that mm-dd-yyyy and dd-mm-yyyy will be considered same by this API.
	 * As in the date value is on mm-dd-yyyy and user date format is dd-mm-yyyy then the mm-dd-yyyy
	 * value will be return as the API will be considered as considered as in same format.
	 * this due to the fact that this API tries to consider the where given date is in user date
	 * format. we need a better gauge for this case.
	 *
	 * @param string $value The date which should a changed to user date format.
	 * @param Users|stdClass $currentUser
	 *
	 * @return Date
	 */
	public static function getValidDisplayDate ($value, $currentUser) {
		if (empty ($value)) {
			return '';
		}

		$format     = !empty ($currentUser->date_format) ? $currentUser->date_format : 'dd-mm-yyyy';
		$dummy      = explode ('-', $format);
		$formatYear = $dummy [0];
		$formatDay  = $dummy [2];

		$dummy    = explode (' ', $value);
		$datePart = $dummy [0];
		list ($year, $month, $day) = explode ('-', $datePart);

		if (((strlen ($formatYear) == 4) && (strlen ($year) == 4)) || ((strlen ($formatDay) == 4) && (strlen ($day) == 4))) {
			return "{$year}-{$month}-{$day}";
		} else {
			$date = new DateTimeField ($datePart);
			return $date->getDisplayDate ();
		}
	}

	/**
	 * @param Users|stdClass $user
	 *
	 * @return string
	 */
	public static function getUserDateFormat ($user) {
		
		if ($user->date_format == 'dd-mm-yyyy') {
			$format = "%d-%m-%Y";
		} elseif ($user->date_format == 'mm-dd-yyyy') {
			$format = "%m-%d-%Y";
		} elseif ($user->date_format == 'dd/mm/yyyy') {
			$format = "%d/%m/%Y";
		} elseif ($user->date_format == 'mm/dd/yyyy') {
			$format = "%m/%d/%Y";
		} else {
			$format = "%Y-%m-%d";
		}
		return $format;
	}

}

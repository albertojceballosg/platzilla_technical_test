<?php

	/**
	 * Class NotificationPeriodUtils
	 *
	 * Contiene metodos que dan soporte a funcionalidades basadas en periodos de tiempo.
	 */
	abstract class NotificationPeriodUtils {

		/**
		 * Obtiene arreglo de periodos de tiempos disponibles
		 *
		 * @return array
		 */
		public static function getAvailablePeriods () {
			return array (
				'custom'      => getTranslatedString ('Custom', 'notifications'),
				'prevfy'      => getTranslatedString ('Previous FY', 'notifications'),
				'thisfy'      => getTranslatedString ('Current FY', 'notifications'),
				'nextfy'      => getTranslatedString ('Next FY', 'notifications'),
				'prevfq'      => getTranslatedString ('Previous FQ', 'notifications'),
				'thisfq'      => getTranslatedString ('Current FQ', 'notifications'),
				'nextfq'      => getTranslatedString ('Next FQ', 'notifications'),
				'yesterday'   => getTranslatedString ('Yesterday', 'notifications'),
				'today'       => getTranslatedString ('Today', 'notifications'),
				'tomorrow'    => getTranslatedString ('Tomorrow', 'notifications'),
				'lastweek'    => getTranslatedString ('Last Week', 'notifications'),
				'thisweek'    => getTranslatedString ('Current Week', 'notifications'),
				'nextweek'    => getTranslatedString ('Next Week', 'notifications'),
				'lastmonth'   => getTranslatedString ('Last Month', 'notifications'),
				'thismonth'   => getTranslatedString ('Current Month', 'notifications'),
				'nextmonth'   => getTranslatedString ('Next Month', 'notifications'),
				'last7days'   => getTranslatedString ('Last 7 Days', 'notifications'),
				'last30days'  => getTranslatedString ('Last 30 Days', 'notifications'),
				'last60days'  => getTranslatedString ('Last 60 Days', 'notifications'),
				'last90days'  => getTranslatedString ('Last 90 Days', 'notifications'),
				'last120days' => getTranslatedString ('Last 120 Days', 'notifications'),
				'next7days'   => getTranslatedString ('Next 7 Days', 'notifications'),
				'next30days'  => getTranslatedString ('Next 30 Days', 'notifications'),
				'next60days'  => getTranslatedString ('Next 60 Days', 'notifications'),
				'next90days'  => getTranslatedString ('Next 90 Days', 'notifications'),
				'next120days' => getTranslatedString ('Next 120 Days', 'notifications'),
			);
		}

		/**
		 * Obtiene arreglo de periodos de tiempos tipo r�pido disponibles
		 *
		 * @return array
		 */
		public static function getAvailableShortPeriods () {
			return array (
				'custom'     => getTranslatedString ('Custom'),
				'today'      => getTranslatedString ('Today'),
				'lastweek'   => getTranslatedString ('Last Week'),
				'lastmonth'  => getTranslatedString ('Last Month'),
				'last30days' => getTranslatedString ('Last 30 Days'),
				'last60days' => getTranslatedString ('Last 60 Days'),
				'last90days' => getTranslatedString ('Last 90 Days'),
			);
		}

		// @codingStandardsIgnoreStart
		/**
		 * Function to get standardfilter startdate and enddate for the given type
		 *  @ param $type : Type String
		 *  returns the $datevalue Array in the given format
		 *  $datevalue = Array(0=>$startdate,1=>$enddate)
		 *
		 * @param $type
		 *
		 * @return array
		 * @SuppressWarnings(PHPMD.NPathComplexity)
		 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
		 */
		public static function getStandarFiltersStartAndEndDate ($type) {
			$today     = date ('Y-m-d', mktime (0, 0, 0, date ('m'), date ('d'), date ('Y')));
			$tomorrow  = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') + 1), date ('Y')));
			$yesterday = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') - 1), date ('Y')));

			$currentMonthIni = date ('Y-m-d', mktime (0, 0, 0, date ('m'), '01', date ('Y')));
			$currentMonthEnd = date ('Y-m-t');
			$lastMonthIni    = date ('Y-m-d', mktime (0, 0, 0, (date ('m') - 1), '01', date ('Y')));
			$lastMonthEnd    = date ('Y-m-t', strtotime ('-1 Month'));
			$nextMonthIni    = date ('Y-m-d', mktime (0, 0, 0, (date ('m') + 1), '01', date ('Y')));
			$nextMonthEnd    = date ('Y-m-t', strtotime ('+1 Month'));

			$lastWeekIni = date ('Y-m-d', strtotime ('-2 week Sunday'));
			$lastWeekEnd = date ('Y-m-d', strtotime ('-1 week Saturday'));

			$thisWeekIni = date ('Y-m-d', strtotime ('-1 week Sunday'));
			$thisWeekEnd = date ('Y-m-d', strtotime ('this Saturday'));

			$nextWeekIni = date ('Y-m-d', strtotime ('this Sunday'));
			$nextWeekEnd = date ('Y-m-d', strtotime ('+1 week Saturday'));

			$nextSevenDays            = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') + 6), date ('Y')));
			$nextThirtyDays           = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') + 29), date ('Y')));
			$nextSixtyDays            = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') + 59), date ('Y')));
			$nextNinetyDays           = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') + 89), date ('Y')));
			$nextHundredAndTwentyDays = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') + 119), date ('Y')));

			$lastSevenDays            = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') - 6), date ('Y')));
			$lastThirtyDays           = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') - 29), date ('Y')));
			$lastSixtyDays            = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') - 59), date ('Y')));
			$lastNinetyDays           = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') - 89), date ('Y')));
			$lastHundredAndTwentyDays = date ('Y-m-d', mktime (0, 0, 0, date ('m'), (date ('d') - 119), date ('Y')));

			$currentYear    = date ('Y-m-d', mktime (0, 0, 0, '01', '01', date ('Y')));
			$currentYearEnd = date ('Y-m-t', mktime (0, 0, 0, '12', date ('d'), date ('Y')));
			$lastYear       = date ('Y-m-d', mktime (0, 0, 0, '01', '01', (date ('Y') - 1)));
			$lastYearEnd    = date ('Y-m-t', mktime (0, 0, 0, '12', date ('d'), (date ('Y') - 1)));
			$nextYear       = date ('Y-m-d', mktime (0, 0, 0, '01', '01', (date ('Y') + 1)));
			$nextYearEnd    = date ('Y-m-t', mktime (0, 0, 0, '12', date ('d'), (date ('Y') + 1)));

			if (date ('m') <= 3) {
				$currentTermIni  = date ('Y-m-d', mktime (0, 0, 0, '01', '01', date ('Y')));
				$CurrentTermEnd  = date ('Y-m-d', mktime (0, 0, 0, '03', '31', date ('Y')));
				$nextTermIni     = date ('Y-m-d', mktime (0, 0, 0, '04', '01', date ('Y')));
				$nextTermEnd     = date ('Y-m-d', mktime (0, 0, 0, '06', '30', date ('Y')));
				$previousTermIni = date ('Y-m-d', mktime (0, 0, 0, '10', '01', (date ('Y') - 1)));
				$previousTermEnd = date ('Y-m-d', mktime (0, 0, 0, '12', '31', (date ('Y') - 1)));
			} else if (date ('m') > 3 && date ('m') <= 6) {
				$previousTermIni = date ('Y-m-d', mktime (0, 0, 0, '01', '01', date ('Y')));
				$previousTermEnd = date ('Y-m-d', mktime (0, 0, 0, '03', '31', date ('Y')));
				$currentTermIni  = date ('Y-m-d', mktime (0, 0, 0, '04', '01', date ('Y')));
				$CurrentTermEnd  = date ('Y-m-d', mktime (0, 0, 0, '06', '30', date ('Y')));
				$nextTermIni     = date ('Y-m-d', mktime (0, 0, 0, '07', '01', date ('Y')));
				$nextTermEnd     = date ('Y-m-d', mktime (0, 0, 0, '09', '30', date ('Y')));
			} else if (date ('m') > 6 && date ('m') <= 9) {
				$nextTermIni     = date ('Y-m-d', mktime (0, 0, 0, '10', '01', date ('Y')));
				$nextTermEnd     = date ('Y-m-d', mktime (0, 0, 0, '12', '31', date ('Y')));
				$previousTermIni = date ('Y-m-d', mktime (0, 0, 0, '04', '01', date ('Y')));
				$previousTermEnd = date ('Y-m-d', mktime (0, 0, 0, '06', '30', date ('Y')));
				$currentTermIni  = date ('Y-m-d', mktime (0, 0, 0, '07', '01', date ('Y')));
				$CurrentTermEnd  = date ('Y-m-d', mktime (0, 0, 0, '09', '30', date ('Y')));
			} else if (date ('m') > 9 && date ('m') <= 12) {
				$nextTermIni     = date ('Y-m-d', mktime (0, 0, 0, '01', '01', (date ('Y') + 1)));
				$nextTermEnd     = date ('Y-m-d', mktime (0, 0, 0, '03', '31', (date ('Y') + 1)));
				$previousTermIni = date ('Y-m-d', mktime (0, 0, 0, '07', '01', date ('Y')));
				$previousTermEnd = date ('Y-m-d', mktime (0, 0, 0, '09', '30', date ('Y')));
				$currentTermIni  = date ('Y-m-d', mktime (0, 0, 0, '10', '01', date ('Y')));
				$CurrentTermEnd  = date ('Y-m-d', mktime (0, 0, 0, '12', '31', date ('Y')));
			}

			if ($type == 'today') {
				$dateValue ['startdate'] = $today;
				$dateValue ['enddate']   = $today;
			} else if ($type == 'yesterday') {
				$dateValue ['startdate'] = $yesterday;
				$dateValue ['enddate']   = $yesterday;
			} else if ($type == 'tomorrow') {
				$dateValue ['startdate'] = $tomorrow;
				$dateValue ['enddate']   = $tomorrow;
			} else if ($type == 'thisweek') {
				$dateValue ['startdate'] = $thisWeekIni;
				$dateValue ['enddate']   = $thisWeekEnd;
			} else if ($type == 'lastweek') {
				$dateValue ['startdate'] = $lastWeekIni;
				$dateValue ['enddate']   = $lastWeekEnd;
			} else if ($type == 'nextweek') {
				$dateValue ['startdate'] = $nextWeekIni;
				$dateValue ['enddate']   = $nextWeekEnd;
			} else if ($type == 'thismonth') {
				$dateValue ['startdate'] = $currentMonthIni;
				$dateValue ['enddate']   = $currentMonthEnd;
			} else if ($type == 'lastmonth') {
				$dateValue ['startdate'] = $lastMonthIni;
				$dateValue ['enddate']   = $lastMonthEnd;
			} else if ($type == 'nextmonth') {
				$dateValue ['startdate'] = $nextMonthIni;
				$dateValue ['enddate']   = $nextMonthEnd;
			} else if ($type == 'next7days') {
				$dateValue ['startdate'] = $today;
				$dateValue ['enddate']   = $nextSevenDays;
			} else if ($type == 'next30days') {
				$dateValue ['startdate'] = $today;
				$dateValue ['enddate']   = $nextThirtyDays;
			} else if ($type == 'next60days') {
				$dateValue ['startdate'] = $today;
				$dateValue ['enddate']   = $nextSixtyDays;
			} else if ($type == 'next90days') {
				$dateValue ['startdate'] = $today;
				$dateValue ['enddate']   = $nextNinetyDays;
			} else if ($type == 'next120days') {
				$dateValue ['startdate'] = $today;
				$dateValue ['enddate']   = $nextHundredAndTwentyDays;
			} else if ($type == 'last7days') {
				$dateValue ['startdate'] = $lastSevenDays;
				$dateValue ['enddate']   = $today;
			} else if ($type == 'last30days') {
				$dateValue ['startdate'] = $lastThirtyDays;
				$dateValue ['enddate']   = $today;
			} else if ($type == 'last60days') {
				$dateValue ['startdate'] = $lastSixtyDays;
				$dateValue ['enddate']   = $today;
			} else if ($type == 'last90days') {
				$dateValue ['startdate'] = $lastNinetyDays;
				$dateValue ['enddate']   = $today;
			} else if ($type == 'last120days') {
				$dateValue ['startdate'] = $lastHundredAndTwentyDays;
				$dateValue['enddate']    = $today;
			} else if ($type == 'thisfy') {
				$dateValue ['startdate'] = $currentYear;
				$dateValue ['enddate']   = $currentYearEnd;
			} else if ($type == 'prevfy') {
				$dateValue ['startdate'] = $lastYear;
				$dateValue ['enddate']   = $lastYearEnd;
			} else if ($type == 'nextfy') {
				$dateValue ['startdate'] = $nextYear;
				$dateValue ['enddate']   = $nextYearEnd;
			} else if ($type == 'nextfq') {
				/** @noinspection PhpUndefinedVariableInspection */
				$dateValue ['startdate'] = $nextTermIni;
				/** @noinspection PhpUndefinedVariableInspection */
				$dateValue ['enddate'] = $nextTermEnd;
			} else if ($type == 'prevfq') {
				/** @noinspection PhpUndefinedVariableInspection */
				$dateValue ['startdate'] = $previousTermIni;
				/** @noinspection PhpUndefinedVariableInspection */
				$dateValue ['enddate'] = $previousTermEnd;
			} else if ($type == 'thisfq') {
				/** @noinspection PhpUndefinedVariableInspection */
				$dateValue ['startdate'] = $currentTermIni;
				/** @noinspection PhpUndefinedVariableInspection */
				$dateValue ['enddate'] = $CurrentTermEnd;
			} else {
				$dateValue ['startdate'] = '';
				$dateValue ['enddate']   = '';
			}
			return $dateValue;
		}
		// @codingStandardsIgnoreEnd

	}

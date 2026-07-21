<?php
	require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');

	abstract class IndicatorsPanelHelper {

		private static function caseLessEqualLimit ($objective, $inObjectiveVariance, $closeToObjectiveVariance) {
			$result                = array ();
			$inObjectiveLimit      = ($objective * (1 - ($inObjectiveVariance / 100)));
			$closeToObjectiveLimit = ($objective * (1 - ($closeToObjectiveVariance / 100)));
			if (
				(($objective < 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance > 0)) ||
				(($objective > 0) && ($inObjectiveVariance > 0) && ($closeToObjectiveVariance < 0))
			) {
				// Caso 1: objective -, inObjectiveVariance -, closeToObjectiveVariance + || objective +, inObjectiveVariance +, closeToObjectiveVariance -
				$result['inObjectiveMin']      = $inObjectiveLimit;
				$result['inObjectiveMax']      = $objective;
				$result['closeToObjectiveMin'] = $objective;
				$result['closeToObjectiveMax'] = $closeToObjectiveLimit;
			} else if (
				(($objective < 0) && ($inObjectiveVariance > 0) && ($closeToObjectiveVariance < 0)) ||
				(($objective > 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance > 0))
			) {
				// Caso 2: objective -, inObjectiveVariance +, closeToObjectiveVariance - || objective +, inObjectiveVariance -, closeToObjectiveVariance +
				$result['inObjectiveMin']      = $objective;
				$result['inObjectiveMax']      = $inObjectiveLimit;
				$result['closeToObjectiveMin'] = $closeToObjectiveLimit;
				$result['closeToObjectiveMax'] = $objective;
			} else if (($objective > 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance < 0)) {
				// Caso 3: objective +, inObjectiveVariance -, closeToObjectiveVariance -
				$result['inObjectiveMin']      = $objective;
				$result['inObjectiveMax']      = $inObjectiveLimit;
				$result['closeToObjectiveMin'] = $inObjectiveLimit;
				$result['closeToObjectiveMax'] = $closeToObjectiveLimit;
			} else {
				$result['inObjectiveMin']      = $inObjectiveLimit;
				$result['inObjectiveMax']      = $objective;
				$result['closeToObjectiveMin'] = $closeToObjectiveLimit;
				$result['closeToObjectiveMax'] = $inObjectiveLimit;
			}

			return $result;
		}

		private static function caseElseLimit ($objective, $inObjectiveVariance, $closeToObjectiveVariance) {
			$result                = array ();
			$inObjectiveLimit      = ($objective * (1 + ($inObjectiveVariance / 100)));
			$closeToObjectiveLimit = ($objective * (1 + ($closeToObjectiveVariance / 100)));
			if (
				(($objective < 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance < 0)) ||
				(($objective > 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance < 0))
			) {
				// Caso 1: objective -, inObjectiveVariance -, closeToObjectiveVariance - || objective +, inObjectiveVariance -, closeToObjectiveVariance -
				$result['inObjectiveMin']      = $inObjectiveLimit;
				$result['inObjectiveMax']      = $objective;
				$result['closeToObjectiveMin'] = $closeToObjectiveLimit;
				$result['closeToObjectiveMax'] = $inObjectiveLimit;
			} else if (
				(($objective < 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance > 0)) ||
				(($objective > 0) && ($inObjectiveVariance > 0) && ($closeToObjectiveVariance < 0))
			) {
				// Caso 2: objective -, inObjectiveVariance -, closeToObjectiveVariance + || objective +, inObjectiveVariance +, closeToObjectiveVariance -
				$result['inObjectiveMin']      = $objective;
				$result['inObjectiveMax']      = $inObjectiveLimit;
				$result['closeToObjectiveMin'] = $closeToObjectiveLimit;
				$result['closeToObjectiveMax'] = $objective;
			} else if (
				(($objective < 0) && ($inObjectiveVariance > 0) && ($closeToObjectiveVariance < 0)) ||
				(($objective > 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance > 0))
			) {
				// Caso 3: objective -, inObjectiveVariance +, closeToObjectiveVariance -|| objective +, inObjectiveVariance -, closeToObjectiveVariance +
				$result['inObjectiveMin']      = $inObjectiveLimit;
				$result['inObjectiveMax']      = $objective;
				$result['closeToObjectiveMin'] = $objective;
				$result['closeToObjectiveMax'] = $closeToObjectiveLimit;
			} else {
				$result['inObjectiveMin']      = $objective;
				$result['inObjectiveMax']      = $inObjectiveLimit;
				$result['closeToObjectiveMin'] = $inObjectiveLimit;
				$result['closeToObjectiveMax'] = $closeToObjectiveLimit;
			}

			return $result;
		}

		public static function deleteRelatedObjectives ($adb, $bsDataIds, $data) {
			if (!count ($bsDataIds))  {
				return;
			}
			$monthsSearch = ($data ['objetive_scale'] == 'MONTH') ? $data['target_month'] : array_keys ($data['week_target']);
			$year         = date ('Y');
			$totalIds     = count ($bsDataIds);
			foreach ($monthsSearch as $monthSearch) {
				for ($kk = 0; $kk < $totalIds; $kk++) {
					$boxScoreDataId = $bsDataIds[ $kk ];
					if (empty ($boxScoreDataId)) {
						continue;
					}
					$adb->pquery (
						'DELETE FROM vtiger_box_score_objective WHERE box_score_dataid=? AND month_apli=? AND (YEAR(`date_from`)=? OR YEAR(`date_end`)=?)',
						array ($boxScoreDataId, $monthSearch, $year, $year)
					);
				}
			}
		}
		
		/**
		 * @param $string
		 *
		 * @return string
		 */
		public static function getBoxScoreName ($string) {
			$string = str_replace (
				array ('á', 'á', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
				array ('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
				$string
			);
			$string = str_replace (
				array ('é', 'é', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
				array ('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
				$string
			);
			$string = str_replace (
				array ('í', 'í', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
				array ('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
				$string
			);
			$string = str_replace (
				array ('ó', 'ó', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
				array ('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
				$string
			);
			$string = str_replace (
				array ('ú', 'ú', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
				array ('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
				$string
			);
			$string = str_replace (
				array ('ñ', 'Ñ', 'ç', 'Ç'),
				array ('n', 'N', 'c', 'C'),
				$string
			);

			$string   = str_replace (
				array ('·', '$', '%', '&', '/', '(', ')', '?', '¡', '¿', '[', '^', ']', '+', '}', '{', '¨', '´', '>', '< ', ';', ',', ':', '.', ' )', ' '),
				'',
				$string
			);
			$string   = substr (strtolower ($string), 0, 12);
			$randomId = rand (100, 999);
			return $string . $randomId;
		}

		public static function getDataWhereClausesAndArguments ($boxScoreId, $crmId, $type) {
			$whereClauses = array ();
			$arguments    = array ();
			$whereClauses[] = 'vbsd.bsd_status=?';
			$arguments[]    = 'ENABLED';
			if (!empty($crmId)) {
				$whereClauses[] = 'vbsd.box_score_dataid=?';
				$arguments[]    = $crmId;
			}
			if ($type != '') {
				$whereClauses[] = 'vbsd.type=?';
				$arguments[]    = $type;
			}
			if ($boxScoreId != '') {
				$whereClauses[] = 'vbsd.boxscoreid=?';
				$arguments[]    = $boxScoreId;
			}
			return array (
				'whereclauses' => count ($whereClauses) > 0 ? 'WHERE ' . join (' AND ', $whereClauses) : '',
				'arguments'    => $arguments,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getFieldsByModule ($adb, $moduleName) {
			$uiType  = FieldInterface::UI_TYPE_CALCULATED_LINK;
			$results = $adb->pquery (
				'SELECT 
					f.fieldname,
					f.fieldlabel,
					f.paradicional
				  FROM 
				  	vtiger_field f
				  INNER JOIN vtiger_tab tab ON tab.tabid = f.tabid
				  WHERE 
				  	f.uitype = ? AND 
				  	tab.name=?',
				array ($uiType, $moduleName)
			);
			if (($adb->num_rows ($results) > 0)) {
				$records = array ();
				while ($row = $adb->fetchByAssoc ($results, -1, false)) {
					$records [] = $row;
				}
				return $records;
			}
			return null;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getModules ($adb) {
			$uiType   = FieldInterface::UI_TYPE_CALCULATED_LINK;
			$results = $adb->pquery (
				'SELECT DISTINCT tab.tablabel, tab.name FROM vtiger_tab tab INNER JOIN vtiger_field f ON f.tabid = tab.tabid WHERE f.uitype = ? AND f.presence IN (?,?) ORDER BY tab.name ASC',
				array ($uiType,0,2)
			);
			if (($adb->num_rows ($results) > 0)) {
				$records = array ();
				while ($row = $adb->fetchByAssoc ($results, -1, false)) {
					$records [] = $row;
				}
				return $records;
			}
			return null;
		}

		public static function getObjectives (PearDatabase $adb, $boxScoreDataId, $year) {
			$whereClause = '';
			$arguments   = array ();
			if (!empty($boxScoreDataId)) {
				$whereClause = 'AND o.box_score_dataid=? AND o.box_score_objectiveid IS NOT NULL';
				$arguments[] = $boxScoreDataId;
			}
			$result = $adb->pquery ("SELECT * FROM vtiger_box_score_objective o WHERE YEAR(CURDATE())=? {$whereClause}", array_merge (array ($year), $arguments));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			$objectives = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$objectives[] = $row;
			}
			return $objectives;
		}

		public static function getMonths () {
			return array (
				'01' => 'January',
				'02' => 'February',
				'03' => 'March',
				'04' => 'April',
				'05' => 'May',
				'06' => 'June',
				'07' => 'July',
				'08' => 'August',
				'09' => 'September',
				'10' => 'October',
				'11' => 'November',
				'12' => 'December',
			);
		}

		public static function getScale (PearDatabase $adb, $recordId) {
			$result = $adb->pquery ('SELECT scale FROM vtiger_boxscore WHERE boxscoreid=?', array ($recordId));

			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result);
				return $row ['scale'];
			} else {
				return null;
			}
		}

		public static function getApp (PearDatabase $adb, $recordId) {
			$result = $adb->pquery ('SELECT app_code FROM vtiger_boxscore WHERE boxscoreid=?', array ($recordId));

			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result);
				return $row ['app_code'];
			} else {
				return null;
			}
		}
		
		/**
		 * @param $from
		 * @param $to
		 * @param string $scale
		 * @param array $months
		 * @param integer $monthSearch
		 *
		 * @return array
		 */
		public static function getDates ($from, $to, $scale, $months, $monthSearch) {
			$year  = date ('Y');
			$month = !empty ($monthSearch) ? $months [ $monthSearch ] : date ('F');

			$dates = array ();
			
			if ((!empty ($from)) && (!empty ($to))) {
				$dates = self::getDatesFromTo ($from, $scale);
			} else if ($scale == 'Week') {
				$dates = self::getDatesScaleWeek ($month, $year);
			} else {
				// Adjusting function to visualize three months prior to the month consulted and the following two month
				for ($i = 2; $i >= 0; $i--) {
					$previousMonth = (intval ($monthSearch) - $i);
					if ($previousMonth == 1) {
						$monday        = date ('Y-m-d', strtotime ("last monday of {$months['01']} {$year}"));
					} else if ($previousMonth >= 2 && $previousMonth <= 9) {
						$previousMonth =  '0' . (intval ($monthSearch) - $i);
						$monday        = date ('Y-m-d', strtotime ("last monday of {$months[ $previousMonth ]} {$year}"));
					} else if ($previousMonth >= 10) {
						$monday        = date ('Y-m-d', strtotime ("last monday of {$months[ $previousMonth ]} {$year}"));
					} else {
						break;
					}
					$w        = strtotime ("{$monday} + 0 month");
					$w        = self::checkLastWeek ($w);
					$dates [] = array (
						'date'  => date ('Y-m-d', $w),
						'week'  => intval (date ('W', $w)),
						'month' => intval (date ('m', $w)),
						'year'  => date ('Y', $w),
					);
				}

				$nextMonth = $monthSearch;
				for ($i = 1; $i <= 2; $i++) {
					$nextMonth = (intval ($nextMonth) != 20) ? (intval ($monthSearch) + $i) : 20;
					if ($nextMonth == 12) {
						$monday        = date ('Y-m-d', strtotime ("last monday of {$months['12']} {$year}"));
						$nextMonth = '20';
					} else if ($nextMonth <= 9) {
						$nextMonth =  '0' . (intval ($monthSearch) + $i);
						$monday     = date ('Y-m-d', strtotime ("last monday of {$months[ $nextMonth ]} {$year}"));
					} else if ($nextMonth >= 10  && $nextMonth < 12) {
						$monday        = date ('Y-m-d', strtotime ("last monday of {$months[ $nextMonth ]} {$year}"));
					} else {
						break;
					}
					$w        = strtotime ("{$monday} + 0 month");
					$w        = self::checkLastWeek ($w);
					$dates [] = array (
						'date'  => date ('Y-m-d', $w),
						'week'  => intval (date ('W', $w)),
						'month' => intval (date ('m', $w)),
						'year'  => date ('Y', $w),
					);
				}
			}
			return $dates;
		}
		
		/**
		 * @param integer $monthSearch
		 *
		 * @return array|null
		 */
		public static function getMonthDatesByWeek ($adb, $monthSearch) {
			$year        = date ('Y');
			$firstDay    = WorkingDayUtils::getFirstDayWeek ($adb);
			$firstDayNum = WorkingDayUtils::getDayOfWeek ($firstDay);
			$totalDays   = cal_days_in_month(CAL_GREGORIAN, $monthSearch, $year);
			$firstWeek 	 = date ('W', strtotime ("$year-$monthSearch-01"));
			for ($day = 1; $day <= $totalDays; $day++) {
				if (checkdate ($monthSearch, $day, $year)) {
					$date    = "$year-$monthSearch-$day";
					$week    = date ('w', strtotime ($date));
					$numWeek = date ('W', strtotime ($date));
					if($week == $firstDayNum){ // monday
						$weeks [$numWeek]['start'] = date ('d-m-Y', strtotime ($date));
						$weeks [$numWeek]['end']   = date ('d-m-Y', strtotime ($weeks [$numWeek]['start'] . '+6 day'));
					}
				}
			}
			if (!isset ($weeks [$firstWeek]['start']) && ($weeks[$firstWeek]['end'])) {
				$weeks[$firstWeek]['start'] = date('d-m-Y', strtotime($weeks[$firstWeek]['end']. ' - 6 days'));
			}
			if ((isset($weeks[$numWeek]['start'])) && (!isset ($weeks [$numWeek]['end']))) {
				$weeks[$numWeek]['end'] = date('d-m-Y', strtotime($weeks[$numWeek]['start']. ' + 6 days'));
			}
			
			return (isset ($weeks)) ? $weeks : null;
		}
		
		public static function getDatesFromTo ($from, $scale) {
			$dates  = array ();
			$monday = date ('Y-m-d', strtotime ('last monday', strtotime ("{$from} +1 day")));
			if ($scale == 'Week') {
				for ($i = 0; $i < 10; $i++) {
					$w        = strtotime ("{$monday} +{$i} week");
					$w        = self::checkLastWeek ($w);
					$dates [] = array (
						'date'  => date ('Y-m-d', $w),
						'week'  => intval (date ('W', $w)),
						'month' => intval (date ('m', $w)),
						'year'  => date ('Y', $w),
					);
				}
			} else {
				for ($i = 0; $i < 5; $i++) {
					$w        = strtotime ("{$monday} +{$i} month");
					$w        = self::checkLastWeek ($w);
					$dates [] = array (
						'date'  => date ('Y-m-d', $w),
						'week'  => intval (date ('W', $w)),
						'month' => intval (date ('m', $w)),
						'year'  => date ('Y', $w),
					);
				}
			}

			return $dates;
		}

		public static function getDatesScaleWeek ($month, $year) {
			$dates       = array ();
			$firstMonday = date ('Y-m-d', strtotime ("first monday of {$month} {$year}"));
			$monday      = date ('Y-m-d', strtotime ("last monday of {$month} {$year}"));
			$startDate   = new DateTime ($firstMonday);
			$endDate     = new DateTime ($monday);
			$interval    = $startDate->diff ($endDate);
			$w           = intval (($interval->days) / 7);
			$selectedWeeks = array ();
			for ($i = $w; $i >= 0; $i--) {
				$w        = strtotime ("{$monday} -{$i} week");
				$w        = self::checkLastWeek ($w);
				if (!in_array (intval (date ('W', $w)), $selectedWeeks)) {
					$dates [] = array(
						'date'  => date ('Y-m-d', $w),
						'week'  => intval (date ('W', $w)),
						'month' => intval (date ('m', $w)),
						'year'  => date ('Y', $w),
					);
					$selectedWeeks [] = intval (date ('W', $w));
				}
			}

			return $dates;
		}

		public static function checkLastWeek ($w) {
			if ((date ('m', $w) == '12') && (date ('d', $w) > 28)) {
				$w = strtotime (date ('Y-m-28', $w) . ' -0 week');
			}
			return $w;
		}

		public static function getFulfillmentsByBoxScoreDataId (PearDatabase $adb, $boxScoreDataId) {
			$whereClause = '';
			$arguments   = array ();
			if (!empty ($boxScoreDataId)) {
				$whereClause  = 'WHERE vbsd.box_score_dataid=? AND o.box_score_objectiveid IS NOT NULL';
				$arguments [] = $boxScoreDataId;
			}
			$result = $adb->pquery (
				"SELECT
						vbsd.*
					FROM
						vtiger_box_score_data_cump vbsd
						LEFT OUTER JOIN vtiger_box_score_objective o ON o.box_score_dataid=vbsd.box_score_dataid AND o.box_score_objectiveid=vbsd.box_score_objectiveid
					{$whereClause}
					GROUP BY
						vbsd.label
					ORDER BY
						vbsd.label ASC",
				$arguments
			);

			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$fulfillment = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$fulfillment [] = $row;
			}
			return $fulfillment;
		}

		public static function getBlockIdRel (PearDatabase $adb, $typeRel) {
			$row    = array ();
			$result = $adb->pquery (
				'SELECT 
						b.*, 
						bs.boxscoreid, 
						bs.scale
					  FROM 
					  	vtiger_boxscore_blocks b
					  INNER JOIN vtiger_boxscore bs ON bs.boxscoreid = b.boxscoreid
					  WHERE 
					  	blockrel = ?',
				array ($typeRel)
			);

			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result);
			}

			return $row;
		}

		/**
		 * @return array
		 */
		public static function getCategories ($excludedCategories = null) {
			$categories     = array_map('html_entity_decode', array_column (getHeaderArray (), 'name'));
			$categoriesKeys = str_replace (
				array ('á', 'á', 'é', 'é','í', 'í','ó', 'ó', 'ú', 'ú'),
				array ('a', 'a','e','e', 'i', 'i', 'o', 'o','u','u'),
				$categories
			);
			$resultArray = array_combine ($categoriesKeys, $categories);
			
			if (!empty ($excludedCategories) && is_array ($excludedCategories)) {
				foreach ($excludedCategories as $category) {
					if (in_array ($category, $categoriesKeys)) {
						unset ($resultArray [$category]);
					}
				}
			}
			return $resultArray;
		}

		public static function getDataIdRel (PearDatabase $adb, $dataId) {
			$row    = array ();
			$result = $adb->pquery (
				'SELECT 
						d.*, 
						b.*, 
						bs.boxscoreid
					FROM 
						vtiger_box_score_data d
					INNER JOIN vtiger_boxscore_blocks b ON b.type = d.type
					INNER JOIN vtiger_boxscore bs ON bs.boxscoreid = d.boxscoreid
					WHERE 
						d.datarel = ? ',
				array ($dataId)
			);

			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result);
			}
			return $row;
		}

		public static function getCalcIdRel (PearDatabase $adb, $operationId) {
			$row    = array ();
			$result = $adb->pquery (
				'SELECT * FROM vtiger_boxscore_operation o 	WHERE o.operationrel = ?',
				array ($operationId)
			);

			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result);
			}

			return $row;
		}

		public static function calculateFulfillment (PearDatabase $adb, $crmId, $boxScoreId, $type, $monthSearch) {
			$year = date ('Y');
			$day  = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
			$from = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
			$to   = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));

			$whereClauses = array ();
			$arguments    = array ();
			if (!empty ($crmId)) {
				$whereClauses [] = 'vbsd.box_score_dataid=?';
				$arguments []    = $crmId;
			}
			if (!empty ($type)) {
				$whereClauses [] = 'vbsd.type=?';
				$arguments []    = $type;
			}
			if ($boxScoreId != '') {
				$whereClauses [] = 'vbsd.boxscoreid=?';
				$arguments []    = $boxScoreId;
			}
			$whereClauses = count ($whereClauses) > 0 ? 'WHERE ' . join (' AND ', $whereClauses) : '';

			$result = $adb->pquery (
				"SELECT
						vbsd.box_score_dataid,
						vbsd.box_score,
						vbsd.type,
						vbsd.boxscoreid,
						vbsd.accountid,
						vbsd.description,
						bk.colorbase,
						bk.colordegrade,
						o.objective,
						o.operator,
						o.box_score_objectiveid,
						o.objective,
						o.month_apli,
						o.date_from,
						o.date_end,
						o.fulfillment
					FROM
						vtiger_box_score_data vbsd
						LEFT OUTER JOIN vtiger_boxscore_blocks bk on bk.type=vbsd.type
						LEFT OUTER JOIN vtiger_box_score_objective o ON o.box_score_dataid=vbsd.box_score_dataid AND o.month_apli=? AND o.date_from=? AND o.date_end=?
					{$whereClauses}
					ORDER BY
						vbsd.type ASC
					LIMIT 1",
				array_merge (array ($monthSearch, $from, $to), $arguments)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$row = $adb->fetchByAssoc ($result);
			return $row ['fulfillment'];
		}

		public static function updateFulfillmentValue (PearDatabase $adb, $boxScoreDataId, $value, $monthSearch) {
			if ($monthSearch == '') {
				$monthSearch = date ('m');
			}
			$year = date ('Y');
			$day  = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
			$from = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
			$to   = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));

			$result = $adb->pquery (
				'SELECT
						vbsd.*,
						o.objective,
						o.box_score_objectiveid,
						o.operator
					FROM
						vtiger_box_score_data_cump vbsd
						LEFT OUTER JOIN vtiger_box_score_objective o ON o.box_score_dataid=vbsd.box_score_dataid AND o.box_score_objectiveid=vbsd.box_score_objectiveid AND o.month_apli=? AND o.date_from=? AND o.date_end=?
					WHERE
						vbsd.box_score_dataid=? AND
						o.box_score_objectiveid IS NOT NULL',
				array ($monthSearch, $from, $to, $boxScoreDataId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}

			$inObjectiveRow      = $adb->fetchByAssoc ($result);
			$closeToObjectiveRow = $adb->fetchByAssoc ($result);

			$operator                 = $inObjectiveRow ['operator'];
			$inObjectiveVariance      = doubleval ($inObjectiveRow ['value_variance']);
			$closeToObjectiveVariance = doubleval ($closeToObjectiveRow ['value_variance']);
			$objective                = doubleval (trim (str_replace (',', '.', $inObjectiveRow ['objective']), '%'));
			$limits                   = self::getFulfillmentLimits ($operator, $objective, $inObjectiveVariance, $closeToObjectiveVariance);

			if ($limits) {
				$fulfillment = self::limitFulfillment ($limits, $value, $objective, $operator);
			} else {
				$fulfillment = '';
			}

			$adb->pquery (
				'UPDATE vtiger_box_score_objective SET fulfillment=? WHERE box_score_dataid=? AND box_score_objectiveid=?',
				array ($fulfillment, $boxScoreDataId, $inObjectiveRow ['box_score_objectiveid'])
			);
		}

		public static function getFulfillmentLimits ($operator, $objective, $inObjectiveVariance, $closeToObjectiveVariance) {
			if (empty ($operator)) {
				return null;
			}

			if ($operator == 'less-equal') {
				$result              = self::caseElseLimit ($objective, $inObjectiveVariance, $closeToObjectiveVariance);
				$inObjectiveMin      = $result['inObjectiveMin'];
				$inObjectiveMax      = $result['inObjectiveMax'];
				$closeToObjectiveMin = $result['closeToObjectiveMin'];
				$closeToObjectiveMax = $result['closeToObjectiveMax'];
			} else {
				$result              = self::caseLessEqualLimit ($objective, $inObjectiveVariance, $closeToObjectiveVariance);
				$inObjectiveMin      = $result['inObjectiveMin'];
				$inObjectiveMax      = $result['inObjectiveMax'];
				$closeToObjectiveMin = $result['closeToObjectiveMin'];
				$closeToObjectiveMax = $result['closeToObjectiveMax'];
			}

			return array (
				'inobjectivemax'      => $inObjectiveMax,
				'inobjectivemin'      => $inObjectiveMin,
				'closetoobjectivemax' => $closeToObjectiveMax,
				'closetoobjectivemin' => $closeToObjectiveMin,
			);
		}

		public static function limitFulfillment ($limits, $value, $objective, $operator) {
			if (($value >= $limits ['inobjectivemin']) && ($value <= $limits ['inobjectivemax'])) {
				$fulfillment = 'According to the objective';
			} else if (($value >= $limits ['closetoobjectivemin']) && ($value <= $limits ['closetoobjectivemax'])) {
				$fulfillment = 'Near the goal';
			} else if ((($objective != '') && ($value != '')) && ($operator == 'less-equal' && $value <= $objective)) {
				$fulfillment = 'According to the objective';
			} else if ((($objective != '') && ($value != '')) && ($operator == 'greater-equal' && $value >= $objective)) {
				$fulfillment = 'According to the objective';
			} else if (($objective != '') && ($value != '')) {
				$fulfillment = 'Far from the objective';
			} else {
				$fulfillment = '';
			}

			return $fulfillment;
		}

		public static function formatDecimal ($value) {
			$val = explode ('.', $value);
			if ((isset ($val [1])) && ($val [1] != '')) {
				$n = strlen ($val [1]);
				if ($n > 1) {
					$decimal = substr ($val [1], 0, -($n - 1));
					$val     = "{$val [0]}.{$decimal}";
				} else {
					$val = $value;
				}
			} else {
				$val = $value;
			}
			return $val;
		}

		public static function updateInObjectiveFulfillment (PearDatabase $adb, $data, $boxScoreDataId, $objectiveId) {
			if ((isset ($data ['boxscorecump_dao_0'])) && (!empty ($data ['boxscorecump_dao_0']))) {
				$fulfillment = self::getFulfillmentByBoxScoreDataId ($adb, $boxScoreDataId, $objectiveId, $data ['fulfillment_0']);
			} else {
				$fulfillment = null;
			}
			if (!$fulfillment) {
				$adb->pquery (
					"INSERT INTO vtiger_box_score_data_cump (fulfillment, value_variance, box_score_dataid, label, type_variance, box_score_objectiveid) VALUES ('According to the objective', ?, ?, ?, ?, ?)",
					array (trim (str_replace (',', '.', $data ['margin_according_target']), '%'), $boxScoreDataId, $data ['fulfillment_0'], $data ['type_variance_according'], $objectiveId)
				);
			} else {
				$adb->pquery (
					"UPDATE vtiger_box_score_data_cump SET fulfillment='According to the objective', value_variance=?, label=?, type_variance=? WHERE box_score_objectiveid=? AND box_score_dataid=? AND id=?",
					array (trim (str_replace (',', '.', $data ['margin_according_target']), '%'), $data ['fulfillment_0'], $data ['type_variance_according'], $objectiveId, $boxScoreDataId, $fulfillment ['id'])
				);
			}
		}

		public static function getFulfillmentByBoxScoreDataId (PearDatabase $adb, $boxScoreDataId, $objectiveId, $label) {
			$result = $adb->pquery (
				'SELECT * FROM vtiger_box_score_data_cump WHERE box_score_dataid=? AND label=? AND box_score_objectiveid=? LIMIT 1',
				array ($boxScoreDataId, $label, $objectiveId)
			);

			return ($result) && ($adb->num_rows ($result) > 0) ? $adb->fetchByAssoc ($result, -1, false) : null;
		}

		public static function updateCloseToObjectiveFulfillment (PearDatabase $adb, $data, $boxScoreDataId, $objectiveId) {
			if ((isset ($data ['data_cump_two'])) && (!empty ($data ['data_cump_two']))) {
				$fulfillment = self::getFulfillmentByBoxScoreDataId ($adb, $boxScoreDataId, $objectiveId, $data ['fulfillment_two']);
			} else {
				$fulfillment = null;
			}
			if (!$fulfillment) {
				$adb->pquery (
					"INSERT INTO vtiger_box_score_data_cump (fulfillment, value_variance, box_score_dataid, label, type_variance, box_score_objectiveid) VALUES ('Near the goal', ?, ?, ?, ?, ?)",
					array (trim (str_replace (',', '.', $data ['margin_close_target']), '%'), $boxScoreDataId, $data ['fulfillment_two'], $data ['type_variance_close'], $objectiveId)
				);
			} else {
				$adb->pquery (
					"UPDATE vtiger_box_score_data_cump SET fulfillment='Near the goal', value_variance=?, label=?, type_variance=? WHERE box_score_objectiveid=? AND box_score_dataid=? AND id=?",
					array (trim (str_replace (',', '.', $data ['margin_close_target']), '%'), $data ['fulfillment_one'], $data ['type_variance_close'], $objectiveId, $boxScoreDataId, $fulfillment ['id'])
				);
			}
		}

		public static function getAplicationsInstance (PearDatabase $adb, $instanceName, $local_user, $current_user) {
			$current_user_profiles = null;
			require ('user_privileges/user_privileges.php');
			$profileIds = (!empty ($current_user_profiles)) ? implode (',', $current_user_profiles) : '';

			if (!empty($profileIds) && $profileIds != '') {
				$profileIds = " where profileid in ({$profileIds}) ";
			} else {
				$profileIds = '';
			}

			$applications     = array ();
			$applicationCodes = array ();
			$appCodesProfile  = array ();
			if (!empty ($instanceName)) {
				$masterAdb            = AdbManager::getInstance ()->getMasterAdb ();
				$instanceDatabaseName = "pg_crm_{$instanceName}";
				$resultProfile        = $masterAdb->pquery ("select REPLACE(REPLACE(applicationcodes, '\"]', '\''), '[\"', '\'') applicationcodes from vtiger_profile {$profileIds}", array ());
				if (($resultProfile) && ($masterAdb->num_rows ($resultProfile) > 0) && $current_user->is_admin == 'off') {
					while ($row = $adb->fetchByAssoc ($resultProfile, -1, false)) {
						$appCodesProfile = $row ['applicationcodes'];
					}
					if (!empty($appCodesProfile)) {
						$appCodes          = implode (',', $appCodesProfile);
						$appCodesProfileIn = " AND ica.app_code IN ({$appCodes})";
					} else {
						$appCodesProfileIn = '';
					}
				} else {
					$appCodesProfileIn = '';
				}

				$result = $masterAdb->pquery (
					"SELECT
						ica.config_applicationsid,
						ica.app_code,
						ica.app_name
					FROM
						vtiger_instanceapplications ia
						INNER JOIN vtiger_instances i ON i.code=ia.instancecode
						INNER JOIN vtiger_config_applications mca ON mca.app_code=ia.applicationcode
						INNER JOIN {$instanceDatabaseName}.vtiger_config_applications ica ON ica.app_code=mca.app_code AND ica.app_status='Activa'
					WHERE
						ia.status IN (?, ?) AND
						i.code=?
						{$appCodesProfileIn}",
					array (ApplicationSubscriptionInterface::STATUS_ACTIVE, ApplicationSubscriptionInterface::STATUS_SUBSCRIBED, $instanceName)
				);
			} else {
				$resultProfile = $adb->pquery ("select REPLACE(REPLACE(applicationcodes, '\"]', '\''), '[\"', '\'') applicationcodes from vtiger_profile {$profileIds}", array ());
				if (($resultProfile) && ($adb->num_rows ($resultProfile) > 0) && $current_user->is_admin == 'off') {
					while ($row = $adb->fetchByAssoc ($resultProfile, -1, false)) {
						$appCodesProfile[] = $row ['applicationcodes'];
					}
					$appCodes          = implode (',', $appCodesProfile);
					$appCodesProfileIn = " AND app_code IN ({$appCodes})";
				} else {
					$appCodesProfileIn = '';
				}

				$result = $adb->query ("SELECT config_applicationsid, app_code, app_name FROM vtiger_config_applications WHERE app_status='Activa' {$appCodesProfileIn}");
			}
			// Get the application catalog
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$applicationCode                   = $row ['app_code'];
					$applications [ $applicationCode ] = $row;
					$applicationCodes []               = $applicationCode;
				}
			} else {
				$applications     = null;
				$applicationCodes = null;
			}

			return $applications;
		}

		public static function getIndicatorDefault (PearDatabase $adb, $appCode, $scale) {
			$boxscore = array ();
			$result   = $adb->pquery (
				'SELECT b.boxscoreid,
						b.title,
						b.date,
						b.description,
						b.scale
					FROM
						vtiger_boxscore b
					WHERE
						app_code = ?  AND
						isdefault = 1 AND
						scale = ?
					LIMIT 1',
				array ($appCode, $scale)
			);

			if (($result) && ($adb->num_rows ($result) > 0)) {
				$boxscore['boxscoreid']  = $adb->query_result ($result, 0, 'boxscoreid');
				$boxscore['title']       = $adb->query_result ($result, 0, 'title');
				$boxscore['description'] = $adb->query_result ($result, 0, 'description');
				$boxscore['scale']       = $adb->query_result ($result, 0, 'scale');
			} else {
				$adb->pquery (
					'INSERT INTO vtiger_boxscore (title, date, description, app_code, scale) VALUES (?, NOW(), ?, ?, ?)',
					array ("Default {$appCode}", 'Default Indicator for app', $appCode, $scale)
				);
				$pointid                 = $adb->getLastInsertID ();
				$boxscore['boxscoreid']  = $pointid;
				$boxscore['title']       = "Default {$appCode}";
				$boxscore['description'] = 'Default Indicator for app';
				$boxscore['scale']       = $scale;
			}

			return $boxscore;
		}

		public static function updateDateRel ($blocks, $boxScoreRel, $row) {
			$data = array ();
			$n    = count ($blocks);
			for ($i = 0; $i < $n; $i++) {
				foreach ($boxScoreRel->boxs as $boxScoreData) {
					foreach ($boxScoreRel->dates as $dateBox) {
						$dateArray[ $boxScoreData['box_score_dataid'] ][ $dateBox['week'] ] = $dateBox['date'];
						$weeklyid[ $boxScoreData['box_score_dataid'] ][ $dateBox['week'] ]  = $boxScoreData['weekly'][ $dateBox['week'] ]['weeklyid'];
						$value[ $boxScoreData['box_score_dataid'] ][ $dateBox['week'] ]     = $boxScoreData['weekly'][ $dateBox['week'] ]['value'];
					}
				}
			}
			$data['value']      = $value;
			$data['weeklyid']   = $weeklyid;
			$data['date']       = $dateArray;
			$data['boxscoreid'] = $row['boxscoreid'];
			return $data;
		}

		public static function updateValueIndicatorMonth (PearDatabase $adb, $valueBoxScore) {
			foreach ($valueBoxScore as $key => $item) {
				$valUp = '';
				for ($i = 4; $i >= 0; $i--) {
					if (isset($item[ $i ]) && $item[ $i ][2] != '') {
						$valUp = $item[ $i ][2];
						break;
					}
				}
				$rowRel  = self::getDataIdRel ($adb, $key);
				$monthUp = explode ('-', $valueBoxScore[ $key ][0][1]);
				$adb->pquery (
					'UPDATE vtiger_box_score_data_weekly SET value=? WHERE box_score_dataid=? AND boxscoreid=? AND month(date) = ?',
					array ($valUp, $rowRel['box_score_dataid'], $rowRel['boxscoreid'], $monthUp[1])
				);
			}
		}

		public static function updateValueIndicatorWeekly (PearDatabase $adb, $valueBoxScore, $valueBoxScoreRel) {
			for ($kk = 0; $kk < 5; $kk++) {
				if (isset($valueBoxScoreRel[ $kk ])) {
					foreach ($valueBoxScoreRel[ $kk ] as $keyRel => $itemRel) {
						$rowRel   = self::getDataIdRel ($adb, $keyRel);
						$valUp    = $valueBoxScore[ $rowRel['box_score_dataid'] ][ $kk ][2];
						$weeklyid = join (',', $itemRel);
						$adb->pquery (
							"UPDATE vtiger_box_score_data_weekly SET value=? WHERE weeklyid IN ({$weeklyid})",
							array ($valUp)
						);
					}
				}
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $boxScoreName
		 * @param string $railesStatus
		 *
		 * @return void
		 * @throws Exception
		 */
		public static function updateRailes ($adb, $boxScoreName, $railesStatus) {
			if (empty($boxScoreName) || empty($railesStatus)) {
				throw new Exception ('Datos insuficientes!');
			}
			
			$adb->pquery (
				'UPDATE vtiger_box_score_data SET on_railes=? WHERE name=?',
				array ($railesStatus, $boxScoreName)
			);
		}

		public static function getFulfillmentWarning (PearDatabase $adb, $boxScoreDataId, $value, $monthSearch, $monthSelect, $alert = false, $systemalertsId = '', $date = '', $operatorCustom = '') {
			if ($monthSearch == '') {
				$monthSearch = date ('m');
			}
			$year = date ('Y');
			$day  = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
			$from = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
			$to   = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));

			$result = $adb->pquery (
				'SELECT
						vbsd.*,
						o.objective,
						o.box_score_objectiveid,
						o.operator
					FROM
						vtiger_box_score_data_cump vbsd
						LEFT OUTER JOIN vtiger_box_score_objective o ON o.box_score_dataid=vbsd.box_score_dataid AND o.box_score_objectiveid=vbsd.box_score_objectiveid AND o.month_apli=? AND o.date_from=? AND o.date_end=?
					WHERE
						vbsd.box_score_dataid=? AND
						o.box_score_objectiveid IS NOT NULL',
				array ($monthSearch, $from, $to, $boxScoreDataId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}

			$inObjectiveRow      = $adb->fetchByAssoc ($result);
			$closeToObjectiveRow = $adb->fetchByAssoc ($result);

			if ($alert && $operatorCustom != '') {
				$operator = $operatorCustom;
			} else {
				$operator = $inObjectiveRow ['operator'];
			}
			$inObjectiveVariance      = doubleval ($inObjectiveRow ['value_variance']);
			$closeToObjectiveVariance = doubleval ($closeToObjectiveRow ['value_variance']);
			$objective                = doubleval (trim (str_replace (',', '.', $inObjectiveRow ['objective']), '%'));
			$limits                   = self::getFulfillmentLimits ($operator, $objective, $inObjectiveVariance, $closeToObjectiveVariance);

			if ($limits) {
				$fulfillment = self::limitFulfillment ($limits, $value, $objective, $operator);
			} else {
				$fulfillment = '';
			}

			if ($fulfillment == 'Far from the objective' && $monthSelect != $monthSearch) {
				$fulfillmentWarning = '1';
				if ($alert && !empty($date)) {
					$adb->pquery ('DELETE FROM vtiger_systemalerts_occurrences WHERE systemalerts_id = ? AND date_alert = ? ', array ($systemalertsId, $date));
					$adb->pquery (
						'INSERT INTO vtiger_systemalerts_occurrences (systemalerts_id, condition_alert, value_alert, date_alert, objective) VALUES(?, ?, ?, ?, ?)',
						array ($systemalertsId, $operator, $value, $date, $objective)
					);
				}
			} else {
				$fulfillmentWarning = '0';
			}
			return $fulfillmentWarning;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string$moduleName
		 *
		 * @return boolean
		 */
		public static function hasboxScoreData ($adb, $moduleName) {
			if (empty ($moduleName)) {
				return false;
			}
			$result = $adb->pquery ('SELECT sourcemodule FROM vtiger_box_score_data WHERE sourcemodule=?', array ($moduleName));
			 return (($result) && ($adb->num_rows ($result) > 0));
		}

	}

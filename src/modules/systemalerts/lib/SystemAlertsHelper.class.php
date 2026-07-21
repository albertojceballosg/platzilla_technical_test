<?php
	require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/CustomView/CustomView.php');

	abstract class SystemAlertsHelper {

		const EXCLUDE_MODULES = array (
			'Calendar',
			'emailsreceived',
			'emailssent',
			'etapas_proyecto',
		);
		
		const DATE_FIELD_OPTIONS = array(
			'PREV-THREE-MONTH' => 'Tres meses antes',
			'PREV-TWO- MONTH'  => 'Dos meses antes',
			'PREV-ONE-MONTH'   => 'Un mes antes',
			'PREV-QUARTER'     => 'Quince días antes',
			'PREV-WEEK'        => 'Una semana antes',
			'CREATED-DATE'     => 'Fecha definida en el registro',
			'NEXT-WEEK'        => 'Una semana después',
			'NEXT-QUARTER'     => 'Quince días despues',
			'NEXT-ONE-MONTH'   => 'Un mes después',
			'NEXT-TWO-MONTH'   => 'Dos meses después',
			'NEXT-THREE-MONTH' => 'Tres meses después',
		);
		
		private static function getLastAlartId ($adb) {
			$result   = $adb->pquery ('SELECT MAX(systemalerts_id) as last_id FROM `vtiger_systemalerts` WHERE 1');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['last_id'];
		}
		
		public static function changeStatusAlert($adb, $idAlert, $status) {
			$adb->pquery ('UPDATE vtiger_systemalerts SET status=? WHERE systemalerts_id=?', array ($status, $idAlert));
		}
		
		public static function getOperator ($type = null) {
			$fLabels                   = array ();
			$fLabels ['e']             = 'EQUALS';
			$fLabels ['n']             = 'NOT_EQUALS_TO';
			$fLabels ['s']             = 'STARTS_WITH';
			$fLabels ['ew']            = 'ENDS_WITH';
			$fLabels ['c']             = 'CONTAINS';
			$fLabels ['k']             = 'DOES_NOT_CONTAINS';
			$fLabels ['l']             = 'LESS_THAN';
			$fLabels ['g']             = 'GREATER_THAN';
			$fLabels ['m']             = 'LESS_OR_EQUALS';
			$fLabels ['h']             = 'GREATER_OR_EQUALS';
			$fLabels ['bw']            = 'BETWEEN';
			$fLabels ['b']             = 'BEFORE';
			$fLabels ['a']             = 'AFTER';
			$fLabels ['less-equal']    = 'less-equal';
			$fLabels ['greater-equal'] = 'greater-equal';
			if (empty ($type)) {
				return $fLabels;
			} else {
				$typesOfData = self::getOperatorType ();
				foreach ($typesOfData [$type] as $typeOfData) {
					$typeResults[$typeOfData] = $fLabels [$typeOfData];
				}
				return $typeResults;
			}
		}
		
		public static function getOperatorType () {
			$typeOfData = array ();
			$typeOfData ['V']  = array ('e', 'n', 's', 'ew', 'c', 'k');
			$typeOfData ['N']  = array ('e', 'n', 'l', 'g', 'm', 'h');
			$typeOfData ['T']  = array ('e', 'n', 'l', 'g', 'm', 'h', 'b', 'a');
			$typeOfData ['I']  = array ('e', 'n', 'l', 'g', 'm', 'h');
			$typeOfData ['C']  = array ('e', 'n');
			$typeOfData ['D']  = array ('e', 'n', 'l', 'g', 'm', 'h', 'b', 'a');
			$typeOfData ['DT'] = array ('e', 'n', 'l', 'g', 'm', 'h', 'b', 'a');
			$typeOfData ['NN'] = array ('e', 'n', 'l', 'g', 'm', 'h');
			$typeOfData ['E']  = array ('e', 'n', 's', 'ew', 'c', 'k');
			return $typeOfData;
		}
		
		public static function getEntityField (PearDatabase $adb, $modulename) {
			$result = $adb->pquery ('SELECT * FROM vtiger_entityname WHERE modulename=?', array ($modulename));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $fieldName
		 *
		 * @return string|null
		 * @throws Exception
		 */
		public static function getModuleByFieldName (PearDatabase $adb, $fieldName) {
			$result = $adb->pquery('SELECT name FROM vtiger_tab t INNER JOIN vtiger_field f on f.tabid = t.tabid WHERE f.fieldname=?', array($fieldName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['name'];
		}
		
		public static function getFieldElementIndicators (PearDatabase $adb, $appSelect, $period) {
			$elements = array ();
			$elem     = array ();
			$result   = $adb->pquery (
				'SELECT d.box_score_dataid, d.box_score, d.boxscoreid, d.datarel, b.scale, bb.boxscoreid bxdatarel, bb.scale scaledatarel
						FROM vtiger_boxscore b
						INNER JOIN vtiger_box_score_data d ON d.boxscoreid = b.boxscoreid
						INNER JOIN vtiger_box_score_data dd ON dd.box_score_dataid = d.datarel
						INNER JOIN vtiger_boxscore bb ON bb.boxscoreid =  dd.boxscoreid
						WHERE b.app_code = ? AND
								bb.scale = ?',
				array ($appSelect, $period)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$elem['box_score_dataid'] = $row['box_score_dataid'];
				$elem['box_score']        = $row['box_score'];
				$elem['boxscoreid']       = $row['boxscoreid'];
				$elem['datarel']          = $row['datarel'];
				$elem['scale']            = $row['scale'];
				$elem['bxdatarel']        = $row['bxdatarel'];
				$elem['scaledatarel']     = $row['scaledatarel'];
				$elements[]               = $elem;
			}
			return $elements;
		}

		public static function getFieldElementModule (PearDatabase $adb, $appSelect, $instanceName, $isAdmin = null) {
			$profileTabsPermission = null;
			require ('user_privileges/user_privileges.php');
			$tabsapp = array ();
			$tapp    = array ();

			if (!empty ($instanceName)) {
				$masterAdb            = AdbManager::getInstance ()->getMasterAdb ();
				$instanceDatabaseName = "pg_crm_{$instanceName}";
				$result               = $masterAdb->pquery (
					"SELECT
						ica.config_applicationsid,
						ica.app_code,
						ica.app_name,
						tab.tabid,
						tab.name,
						tab.tablabel
					FROM
						vtiger_instanceapplications ia
						INNER JOIN vtiger_instances i ON i.code=ia.instancecode
						INNER JOIN vtiger_config_applications mca ON mca.app_code=ia.applicationcode
						INNER JOIN {$instanceDatabaseName}.vtiger_config_applications ica ON ica.app_code=mca.app_code AND ica.app_status='Activa'
						INNER JOIN vtiger_configapps_tab ctab ON ctab.config_applicationsid = mca.config_applicationsid
						INNER JOIN vtiger_tab tab ON tab.tabid = ctab.tabid
					WHERE
						ia.status IN (?, ?) AND
						i.code=? AND
						ica.app_code=?",
					array (ApplicationSubscriptionInterface::STATUS_ACTIVE, ApplicationSubscriptionInterface::STATUS_SUBSCRIBED, $instanceName, $appSelect)
				);
			} else {
				$result = $adb->pquery (
					"SELECT
						capp.config_applicationsid,
						capp.app_code,
						capp.app_name,
						tab.tabid,
						tab.name,
						tab.tablabel
					FROM
						vtiger_config_applications capp
						INNER JOIN vtiger_configapps_tab ctab ON ctab.config_applicationsid = capp.config_applicationsid
						INNER JOIN vtiger_tab tab ON tab.tabid = ctab.tabid
					WHERE
						app_status='Activa' AND
						capp.app_code=?",
					array ($appSelect)
				);
			}
			// Get the tab application catalog
			if (($result) && ($adb->num_rows ($result) > 0)) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (in_array ($row ['name'], self::EXCLUDE_MODULES)) {
						continue;
					}
					
					if (!empty($isAdmin)) {
						if ($isAdmin == 'on') {
							$tapp['tabid']    = $row ['tabid'];
							$tapp['name']     = $row ['name'];
							$tapp['tablabel'] = $row ['tablabel'];
							$tabsapp []       = $tapp;
						} else if ($profileTabsPermission[ $row ['tabid'] ] == 0) {
							$tapp['tabid']    = $row ['tabid'];
							$tapp['name']     = $row ['name'];
							$tapp['tablabel'] = $row ['tablabel'];
							$tabsapp []       = $tapp;
						}
					}
				}
			} else {
				$tabsapp = null;
				$tapp    = null;
			}

			return $tabsapp;
		}
		
		public static function getFieldId (PearDatabase $adb, $fieldName) {
			$result = $adb->pquery ('SELECT fieldid FROM vtiger_field WHERE fieldname=?', array ($fieldName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$row = $adb->fetchByAssoc ($result, -1, false);
			return $row ['fieldid'];
		}
		
		public static function getFieldsModule ($tabName, $current_language) {
			$oCustomView      = new CustomView();
			$columnslist      = $oCustomView->getModuleColumnsList ($tabName);
			$check_dup        = array ();
			$advfilter_option = array ();
			$modStrings       = return_specified_module_language ($current_language, $tabName);
			foreach ($oCustomView->module_list[ $tabName ] as $key => $value) {
				$advfilter = array ();
				if (isset($columnslist[ $tabName ][ $key ])) {
					foreach ($columnslist[ $tabName ][ $key ] as $field => $fieldlabel) {
						if (!in_array ($fieldlabel, $check_dup)) {
							if (isset($modStrings[ $fieldlabel ])) {
								$advfilter_option['value']     = $field;
								$advfilter_option['text']      = $modStrings[ $fieldlabel ];
								$advfilter_option['selected']  = '';
								$advfilter_option['value_all'] = $value;
							} else {
								$advfilter_option['value']     = $field;
								$advfilter_option['text']      = $fieldlabel;
								$advfilter_option['selected']  = '';
								$advfilter_option['value_all'] = $value;
							}
							$advfilter[]  = $advfilter_option;
							$check_dup [] = $fieldlabel;
						}
					}
				}
			}

			return $advfilter;
		}

		public static function getScale (PearDatabase $adb, $recordId) {
			$result = $adb->pquery ('SELECT scale FROM vtiger_systemalerts WHERE systemalerts_id=?', array ($recordId));

			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result);
				return $row ['scale'];
			} else {
				return null;
			}
		}

		public static function getApp (PearDatabase $adb, $recordId) {
			$result = $adb->pquery ('SELECT app_code FROM vtiger_systemalerts WHERE systemalerts_id=?', array ($recordId));

			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row = $adb->fetchByAssoc ($result);
				return $row ['app_code'];
			} else {
				return null;
			}
		}

		public static function getDetailIndicatorAlert (PearDatabase $adb, $recordId, $from, $to) {
			$detailAlert = array ();
			$result      = $adb->pquery (
				'SELECT *
					  FROM vtiger_systemalerts_occurrences
					  WHERE systemalerts_id=? AND date_alert >= ? AND date_alert <= ?
					  GROUP BY systemalerts_id, condition_alert, value_alert, date_alert, objective
					  ORDER BY date_alert  DESC',
				array ($recordId, $from, $to)
			);

			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}
			while ($row = $adb->fetchByAssoc ($result)) {
				if ($row['condition_alert'] == 'less-equal') {
					$row['condition_alert'] = '<=';
				} else if ($row['condition_alert'] == 'greater-equal') {
					$row['condition_alert'] = '>=';
				}
				$detailAlert[] = $row;
			}
			return $detailAlert;
		}

		public static function creatingAlertIndicator (PearDatabase $adb, $data, $automatic = '1', $alert = '', $type = '') {
			if ($alert == '') {
				$alert = 'Indicator away from the objective';
			}
			if ($type == '') {
				$type = 'Indicators';
			}
			$adb->pquery (
				'INSERT INTO vtiger_systemalerts(name, code_app, alert, source_alert, indicator_id, automatic, scale, boxscore_id, condition_alert, value_alert, status, users_ids, description, locked) VALUES(SUBSTRING(MD5(RAND()),1, 8),?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array ($data['codeApp'], $alert, $type, $data['codeElement'], $automatic, $data['scale'], $data['boxScoreId'], $data['codeElementOperator'], $data['codeElementValue'], $data['status'], $data['users_ids'], $data['description'], $data['locked'])
			);
		}

		public static function creatingAlertIndicatorFromIndicator (PearDatabase $adb, $app, $scale, $boxScoreDataId, $boxScore, $automatic = '1', $alert = '', $type = '') {
			if ($alert == '') {
				$alert = 'Indicator away from the objective';
			}
			if ($type == '') {
				$type = 'Indicators';
			}

			$adb->pquery (
				'INSERT INTO vtiger_systemalerts(name, code_app, alert, source_alert, indicator_id, automatic, scale, boxscore_id) VALUES(SUBSTRING(MD5(RAND()),1, 8), ?, ?, ?, ?, ?, ?, ?)',
				array ($app, $alert, $type, $boxScoreDataId, $automatic, $scale, $boxScore)
			);
		}

		public static function creatingAlertModule (PearDatabase $adb, $data) {
			$adb->pquery (
				'INSERT INTO vtiger_systemalerts(name, code_app, alert, source_alert, scale, condition_alert, value_alert, tab_id, field_id, tab_label, tab_name, field_name, status, users_ids, description, locked) VALUES(SUBSTRING(MD5(RAND()),1, 8), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array ($data['codeApp'], $data['titleAlert'], $data['codetype'], $data['scale'], $data['codeElementOperator'], $data['codeElementValue'], $data['codeElement'], $data['field'], $data['codeElementLabel'], $data['codeElementName'], $data['fieldElementName'], $data['status'], $data['users_ids'], $data['description'], $data['locked'])
			);
			return self::getLastAlartId ($adb);
		}

		public static function deleteAlerts (PearDatabase $adb, $systemAlertIds, $moduleName) {
			$adb->pquery ('DELETE FROM vtiger_systemalerts WHERE systemalerts_id=?', array ($systemAlertIds));
			if (!empty($moduleName)) {
				$adb->pquery ('DELETE FROM vtiger_systemalerts_filters WHERE modulename=? AND systemalerts_id=?', array($moduleName, $systemAlertIds));
				$adb->pquery ('DELETE FROM vtiger_systemalerts_filtergroups WHERE modulename=? AND systemalerts_id=?', array($moduleName, $systemAlertIds));
			}
		}

		public function generatingAlert (PearDatabase $adb, $boxScoreDataId, $value, $operator, $to) {
			$adb->pquery (
				'DELETE  sao
								FROM vtiger_systemalerts_occurrences sao
								INNER JOIN vtiger_systemalerts sa ON sao.systemalerts_id = sa.systemalerts_id
								WHERE sa.indicator_id = ? AND sao.date_alert = ? ',
				array ($boxScoreDataId, $to)
			);

			$result = $adb->pquery (
				"SELECT * FROM vtiger_systemalerts sa WHERE sa.indicator_id = ? AND sa.automatic = '1' AND sa.status = '1' ",
				array ($boxScoreDataId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$alertRow = $adb->fetchByAssoc ($result);

			$adb->pquery (
				'INSERT INTO vtiger_systemalerts_occurrences (systemalerts_id, condition_alert, value_alert, date_alert) VALUES(?, ?, ?, ?)',
				array ($alertRow['systemalerts_id'], $operator, $value, $to)
			);
		}

		public static function updateFulfillmentValueAlert (PearDatabase $adb, $boxScoreDataId, $value, $date, $monthSearch, $systemalertsId, $operatorCustom = '') {
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

			if ($operatorCustom != '') {
				$operator = $operatorCustom;
			} else {
				$operator = $inObjectiveRow ['operator'];
			}

			$inObjectiveVariance      = doubleval ($inObjectiveRow ['value_variance']);
			$closeToObjectiveVariance = doubleval ($closeToObjectiveRow ['value_variance']);
			$objective                = doubleval (trim (str_replace (',', '.', $inObjectiveRow ['objective']), '%'));
			$limits                   = IndicatorsPanelHelper::getFulfillmentLimits ($operator, $objective, $inObjectiveVariance, $closeToObjectiveVariance);
			if ($limits) {
				$fulfillment = IndicatorsPanelHelper::limitFulfillment ($limits, $value, $objective, $operator);
			} else {
				$fulfillment = '';
			}
			if ($fulfillment == 'Far from the objective' && !empty($date)) {
				$adb->pquery (
					'INSERT INTO vtiger_systemalerts_occurrences (systemalerts_id, condition_alert, value_alert, date_alert, objective) VALUES(?, ?, ?, ?, ?)',
					array ($systemalertsId, $operator, $value, $date, $objective)
				);
			}
		}

		/** @codingStandardsIgnoreStart * */
		public static function getColunmCondition ($comparator, $value, $typeOfData) {
			$results      = '';
			$charToString = "'";
			switch ($comparator) {
				case 'e':
					if (($typeOfData == 'N') || ($typeOfData == 'NN')) {
						if (!is_numeric ($value)) {
							$value = intval ($value);
						}
						$results = ' = ' . $value;
					} else {
						$results = ' = ' . $charToString . $value . $charToString;
					}
					break;
				case 'n':
					if (($typeOfData == 'N') || ($typeOfData == 'NN')) {
						$results = ' != ' . $value;
					} else {
						$results = ' != ' . $charToString . $value . $charToString;
					}
					break;
				case 's':
					$results = ' LIKE ' . $charToString . $value . '%' . $charToString;
					break;
				case 'ew':
					$results = ' LIKE ' . $charToString . '%' . $value . $charToString;
					break;
				case 'c':
					$results = ' LIKE ' . $charToString . '%' . $value . '%' . $charToString;
					break;
				case 'k':
					$results = ' NOT LIKE ' . $charToString . '%' . $value . '%' . $charToString;
					break;
				case 'l':
					if (($typeOfData == 'N') || ($typeOfData == 'NN')) {
						if (!is_numeric ($value)) {
							$value = intval ($value);
						}
						$value   = intval ($value);
						$results = ' < ' . $value;
					} else {
						$results = ' < ' . $charToString . $value . $charToString;
					}
					break;
				case 'g':
					if (($typeOfData == 'N') || ($typeOfData == 'NN')) {
						if (!is_numeric ($value)) {
							$value = intval ($value);
						}
						$value   = intval ($value);
						$results = ' > ' . $value;
					} else {
						$results = ' > ' . $charToString . $value . $charToString;
					}
					break;
				case 'm':
					if (($typeOfData == 'N') || ($typeOfData == 'NN')) {
						$results = ' <= ' . $value;
					} else {
						$results = ' <= ' . $charToString . $value . $charToString;
					}
					break;
				case 'h':
					if (($typeOfData == 'N') || ($typeOfData == 'NN')) {
						if (!is_numeric ($value)) {
							$value = intval ($value);
						}
						$value   = intval ($value);
						$results = ' >= ' . $value;
					} else {
						$results = ' >= ' . $charToString . $value . $charToString;
					}
					break;
				case 'b':
					if (($typeOfData == 'D') || ($typeOfData == 'DT')) {
						$results = ' < ' . $charToString . $value . $charToString;
					}
					break;
				case 'a':
					if (($typeOfData == 'D') || ($typeOfData == 'DT')) {
						$results = ' > ' . $charToString . $value . $charToString;
					}
					break;
				default:
					if (($typeOfData == 'N') || ($typeOfData == 'NN')) {
						if (!is_numeric ($value)) {
							$value = intval ($value);
						}
						$results = ' = ' . $value;
					} else {
						$results = ' = ' . $charToString . $value . $charToString;
					}
					break;
			}
			return $results;
		}

		/** @codingStandardsIgnoreEnd * */

		public static function getRelatedField (PearDatabase $adb, $fieldname, $module) {
			$result = $adb->pquery ('SELECT fieldid FROM vtiger_field A INNER JOIN vtiger_tab B ON A.tabid = B.tabid  AND B.name = ? WHERE columnname = ?', array ($module, $fieldname));
			if ($result) {
				$row       = $adb->fetchByAssoc ($result);
				$sql       = 'SELECT tablename, fieldname FROM vtiger_entityname
					INNER JOIN vtiger_fieldmodulerel ON (vtiger_entityname.modulename = vtiger_fieldmodulerel.relmodule)
					WHERE vtiger_fieldmodulerel.fieldid	= ? ORDER BY sequence';
				$resultTwo = $adb->pquery ($sql, array ($row['fieldid']));
				if ($resultTwo) {
					$rowTwo = $adb->fetchByAssoc ($resultTwo);
					return $rowTwo;
				}
			} else {
				return false;
			}
		}

		public static function getDatesWeeks ($months, $monthSearch, $year) {
			$n = count ($monthSearch);
			for ($k = 0; $k < $n; $k++) {
				$month       = $months [ $monthSearch[ $k ] ];
				$firstMonday = date ('Y-m-d', strtotime ("first monday of {$month} {$year}"));
				$monday      = date ('Y-m-d', strtotime ("last monday of {$month} {$year}"));
				$startDate   = new DateTime ($firstMonday);
				$endDate     = new DateTime ($monday);
				$interval    = $startDate->diff ($endDate);
				$w           = intval (($interval->days) / 7);
				for ($i = $w; $i >= 0; $i--) {
					$w        = strtotime ("{$monday} -{$i} week");
					$w        = IndicatorsPanelHelper::checkLastWeek ($w);
					$dateFrom = date ('Y-m-d', $w);
					$dates [] = array (
						'date_from' => $dateFrom,
						'week'      => date ('W', $w),
						'month'     => intval (date ('m', $w)),
						'year'      => date ('Y', $w),
					);
				}
			}
			return $dates;
		}

		public static function getDatesMonths ($monthSearch, $year) {
			$n = count ($monthSearch);
			for ($k = 0; $k < $n; $k++) {
				$d         = "{$year}-{$monthSearch[$k]}-01";
				$startDate = new DateTime ($d);
				$startDate->modify ('first day of this month');
				$dateFrom = $startDate->format ('Y-m-d');
				$startDate->modify ('last day of this month');
				$dateTo   = $startDate->format ('Y-m-d');
				$dates [] = array (
					'date_from' => $dateFrom,
					'date_to'   => $dateTo,
					'month'     => $monthSearch[ $k ],
					'year'      => $year,
				);
			}
			return $dates;
		}

		public static function getMonthSearch ($from, $to) {
			$begin    = new DateTime( $from );
			$end      = new DateTime( $to );
			$end      = $end->modify( '+1 month' );
			$interval = DateInterval::createFromDateString('1 month');
			$period   = new DatePeriod($begin, $interval, $end);
			foreach($period as $dt) {
				$monthSearch[] = $dt->format("m");
			}
			return $monthSearch;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param integer $ccurrenceId
		 * @param string $from
		 * @param string $to
		 */
		public static function setLookedAlert ($adb, $userId, $ccurrenceId, $from, $to) {
			$adb->pquery (
				'INSERT INTO vtiger_systemalerts_users (userid, systemalerts_ocurrence_id, from_period, to_period, status_occurrence) VALUES(?, ?, ?, ?, ?)',
				array ($userId, $ccurrenceId, $from, $to, 'DISCARDED')
			);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param string $from
		 * @param string $to
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getFlaggedAlerts ($adb, $userId, $from, $to) {
			$result = $adb->pquery(
				'SELECT
				  		systemalerts_ocurrence_id,
				  		status_occurrence
					  FROM
				  		vtiger_systemalerts_users
					  WHERE
				  	  	userid=? AND
				  	  	from_period <= ? AND
				  	  	to_period >= ?',
				array($userId, $from, $to)
			);
			if ($adb->num_rows ($result) > 0) {
				$alerts = array();
				while ($row = $adb->fetchByAssoc ($result)) {
					$alerts[] = $row;
				}
			}
			return isset($alerts) ? $alerts : null;
		}
		
	}

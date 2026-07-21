<?php

	require_once ('modules/systemalerts/lib/SystemAlertsHelper.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('include/platzilla/Managers/FieldManager.php');

	class SystemAlerts {
		
		/** @var PearDatabase */
		private $adb;
		private $app;
		
		public $scale  = null;
		public $alerts = array ();
		
		/**
		 * @param string $comparator
		 * @param string $value
		 * @param Field $field
		 * @return mixed
		 */
		private function getSqlOperator ($comparator, $value, $field) {
			if (in_array ($field->getUiType (), array (7, 9, 53, 71))) {
				$sqlOperator = array(
					'EQUALS'            => '= @',
					'NOT_EQUALS'        => '!= @',
					'LESS'              => '< @',
					'LESS_OR_EQUALS'    => '<= @',
					'GREATER'           => '> @',
					'GREATER_OR_EQUALS' => '>= @',
				);
				$sqlStr = str_replace ('@', $value, $sqlOperator[$comparator]);
				$sqlStr = "{$field->getTableName ()}.{$field->getColumnName ()} {$sqlStr}";
			} else if (in_array ($field->getUiType (), array (5, 6, 14, 70))) {
				$sqlOperator = array(
					'EQUALS'  => "= '@'",
					'LESS'    => "< '@'",
					'GREATER' => "> '@'",
				);
				$modifyDates = array(
					'PREV-THREE-MONTH' => '+ 3 month#90',
					'PREV-TWO- MONTH'  => '+ 2 month#60',
					'PREV-ONE-MONTH'   => '+ 1 month#30',
					'PREV-QUARTER'     => '+ 15 day#15',
					'PREV-WEEK'        => '+7 day#7',
					'CREATED-DATE'     => 'today',
					'NEXT-WEEK'        => '-7 day#7',
					'NEXT-QUARTER'     => '-15 day#15',
					'NEXT-ONE-MONTH'   => '- 1 month#30',
					'NEXT-TWO-MONTH'   => '- 2 month#60',
					'NEXT-THREE-MONTH' => '- 3 month#90',
				);
				$objectDate = new DateTime();
				if ($modifyDates[$value] != 'today') {
					$sqlOperator = array(
						'EQUALS'  => '= @',
						'LESS'    => '< @',
						'GREATER' => '> @',
					);
					list ($modify, $days) = explode ('#', $modifyDates[$value]);
					$objectDate->modify ($modify);
					$dateFrom = $objectDate->format ('Y-m-d');
					$sqlStr = "DATEDIFF({$field->getTableName ()}.{$field->getColumnName ()},'{$dateFrom}') ";
					$dummy  = explode ('-', $value);
					$value  = ($dummy[0] == 'PREV') ? (-1 * intval ($days)) : intval ($days);
					$sqlStr .= str_replace ('@', $value, $sqlOperator[$comparator]);
				} else {
					$value  = $objectDate->format ('Y-m-d');
					$sqlStr = str_replace ('@', $value, $sqlOperator[$comparator]);
					$sqlStr = "{$field->getTableName ()}.{$field->getColumnName ()} {$sqlStr}";
				}
			} else {
				$sqlOperator = array (
					'EQUALS'           => "= '@'",
					'NOT_EQUALS'       => "!= '@'",
					'STARTS_WITH'      => "LIKE '@%'",
					'ENDS_WITH'        => "LIKE '%@'",
					'CONTAINS'         => "LIKE '%@%'",
					'DOES_NOT_CONTAIN' => "NOT LIKE '%@%'",
				);
				$sqlStr = str_replace ('@', $value, $sqlOperator[$comparator]);
				$sqlStr = "{$field->getTableName ()}.{$field->getColumnName ()} {$sqlStr}";
			}
			
			return $sqlStr;
		}
		
		private function getActivityIdsByModules($adb, $moduleName) {
			$whereModule = '';
			$resultModule = $adb->pquery (
				'SELECT
					a.activityid
				 FROM
					vtiger_seactivityrel a
				 INNER JOIN vtiger_crmentity cr ON
					cr.crmid = a.crmid AND cr.deleted = 0
				WHERE
				  	cr.setype=?
				GROUP by cr.crmid',
				array ($moduleName)
			);
			if ($adb->num_rows ($resultModule)) {
				while ($rowModule = $adb->fetchByAssoc ($resultModule, -1, false)) {
						$activityIds[] = $rowModule ['activityid'];
				}
				$whereModule = "vtiger_activity.activityid IN{$adb->sql_expr_datalist($activityIds)} AND ";
			}
			return $whereModule;
		}
		
		public static function getInstance (PearDatabase $adb, $scaleSearch, $app, $from, $to, $status = '') {
			$alerts = new SystemAlerts ();
			$alerts->init ($adb, $scaleSearch, $app, $from, $to, $status);
			return $alerts;
		}

		public function init (PearDatabase $adb, $scaleSearch, $app, $from, $to, $status = '') {
			$this->adb    = $adb;
			$this->scale  = $scaleSearch;
			$this->app    = $app;
			$this->alerts = self::getAlerts ($adb, $scaleSearch, $app, $from, $to, $status);
			$this->sqlOperator;
		}

		public static function getAlerts (PearDatabase $adb, $scaleSearch, $app, $from, $to, $status) {
			$alerts = array ();
			$whereStatus = '';
			if (empty ($status)) {
				self::getAlertsAutomaticIndicators ($adb, $scaleSearch, $app, $from, $to);
				self::getAlertsIndicators ($adb, $scaleSearch, $app, $from, $to);
				self::getAlertsModules ($adb, $scaleSearch, $app, $from, $to);
				self::getAlertsCalendars ($adb, $scaleSearch, $app, $from, $to);
				$whereStatus = ' sa.status = 1 AND ';
			}

			$result = $adb->pquery (
				"SELECT sa.*, count(sao.systemalerts_id) num_alerts, bsd.box_score
				FROM vtiger_systemalerts sa
				LEFT OUTER JOIN vtiger_box_score_data bsd ON bsd.box_score_dataid = sa.indicator_id
				LEFT OUTER JOIN (SELECT *
				FROM vtiger_systemalerts_occurrences
				WHERE date_alert >= ? AND date_alert <= ?
				GROUP BY systemalerts_id, condition_alert, value_alert, date_alert, objective) sao ON sao.systemalerts_id = sa.systemalerts_id
				WHERE {$whereStatus} sa.source_alert ='Indicators' AND sa.code_app = ?
				GROUP BY sa.systemalerts_id, sa.indicator_id, sao.systemalerts_id, sa.alert
				UNION
				SELECT sa.*, SUM(sao.count_alert) num_alerts, ''
				FROM vtiger_systemalerts sa
				LEFT OUTER JOIN (SELECT *
				FROM vtiger_systemalerts_occurrences
			  	WHERE date_alert >= ? AND date_alert <= ?
				GROUP BY systemalerts_id, condition_alert, value_alert, date_alert, objective) sao ON sao.systemalerts_id = sa.systemalerts_id
				WHERE {$whereStatus} sa.source_alert <> 'Indicators' AND sa.code_app = ?
				GROUP BY sa.systemalerts_id",
				array ($from, $to,$app, $from, $to,$app)
			);

			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$alertCount = 0;
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				if (!empty($row['field_id'])) {
					$fieldAll           = $row['field_id'];
					$field              = explode (':', $fieldAll);
					$fieldLabel         = explode ('_', $field[3]);
					$nl                 = count ($fieldLabel);
					$row['field_label'] = $fieldLabel[ ($nl - 1) ];
				} else {
					$row['field_label'] = '';
				}
				$alerts['alerts'][ $row['systemalerts_id'] ] = $row;
				$alertCount                                  = ($alertCount + $row['num_alerts']);
			}

			$alerts['countAlert'] = $alertCount;
			return $alerts;
		}

		public static function getAlertsAutomaticIndicators (PearDatabase $adb, $scaleSearch, $app, $from, $to) {
			$monthSearch = SystemAlertsHelper::getMonthSearch ($from, $to);
			$monthFrom   = explode ('-', $from);
			$monthTo     = explode ('-', $to);

			$year = date ('Y');
			$from = date ('Y-m-d', mktime (0, 0, 0, $monthFrom[1], 1, date ('Y')));
			$day  = date ('d', mktime (0, 0, 0, ($monthTo[1] + 1), 0, date ('Y')));
			$to   = date ('Y-m-d', mktime (0, 0, 0, $monthTo[1], $day, $year));

			$result = $adb->pquery (
				"SELECT sa.*, count(sao.systemalerts_id) num_alerts
					FROM
						vtiger_systemalerts sa
					LEFT OUTER JOIN vtiger_systemalerts_occurrences sao ON sao.systemalerts_id=sa.systemalerts_id AND sao.date_alert >= ? AND sao.date_alert <= ?
					WHERE sa.scale = ? AND sa.code_app = ? AND source_alert = 'Indicators' AND sa.automatic = '1' AND sa.status = '1'
					GROUP BY sa.indicator_id, sao.systemalerts_id",
				array ($from, $to, $scaleSearch, $app)
			);

			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$n = count ($monthSearch);
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$adb->pquery ('DELETE FROM vtiger_systemalerts_occurrences WHERE systemalerts_id = ? AND (date_alert >= ? AND date_alert <= ?) OR date_alert IS NULL', array ($row['systemalerts_id'], $from, $to));
				for ($k = 0; $k < $n; $k++) {
					$bs              = IndicatorsPanel::getInstance ($adb, $monthSearch[ $k ], $row['boxscore_id'], '', '');
					$values          = $bs->getWeeklyValueByBoxScoreId ($row['indicator_id'], $monthSearch[ $k ], true);
					$normalizedValue = $values ? $values['normalizedvalue'] : '';
					SystemAlertsHelper::updateFulfillmentValueAlert ($adb, $row['indicator_id'], $normalizedValue, $values['datevalue'], $monthSearch[ $k ], $row['systemalerts_id']);
					$bs->getWarningAlerts ($row['indicator_id'], $monthSearch[ $k ], $row['systemalerts_id']);
				}
			}
		}

		public static function getAlertsIndicators (PearDatabase $adb, $scaleSearch, $app, $from, $to) {
			$monthSearch = SystemAlertsHelper::getMonthSearch ($from, $to);
			$monthFrom   = explode ('-', $from);
			$monthTo     = explode ('-', $to);

			$year = date ('Y');
			$from = date ('Y-m-d', mktime (0, 0, 0, $monthFrom[1], 1, date ('Y')));
			$day  = date ('d', mktime (0, 0, 0, ($monthTo[1] + 1), 0, date ('Y')));
			$to   = date ('Y-m-d', mktime (0, 0, 0, $monthTo[1], $day, $year));

			$result = $adb->pquery (
				"SELECT sa.*, count(sao.systemalerts_id) num_alerts
					FROM
						vtiger_systemalerts sa
					LEFT OUTER JOIN vtiger_systemalerts_occurrences sao ON sao.systemalerts_id=sa.systemalerts_id AND sao.date_alert >= ? AND sao.date_alert <= ?
					WHERE sa.scale = ? AND sa.code_app = ? AND source_alert = 'Indicators' AND sa.automatic = '0' AND sa.status = '1'
					GROUP BY sa.indicator_id, sao.systemalerts_id",
				array ($from, $to, $scaleSearch, $app)
			);

			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$n = count ($monthSearch);
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$adb->pquery ('DELETE FROM vtiger_systemalerts_occurrences WHERE systemalerts_id = ? AND date_alert >= ? AND date_alert <= ?', array ($row['systemalerts_id'], $from, $to));
				for ($k = 0; $k < $n; $k++) {
					$bs              = IndicatorsPanel::getInstance ($adb, $monthSearch[ $k ], $row['boxscore_id'], '', '');
					$values          = $bs->getWeeklyValueByBoxScoreId ($row['indicator_id'], $monthSearch[ $k ], true);
					$normalizedValue = IndicatorsPanelHelper::formatDecimal ($row['value_alert']);
					$operatorCustom  = $row['condition_alert'];
					SystemAlertsHelper::updateFulfillmentValueAlert ($adb, $row['indicator_id'], $normalizedValue, $values['datevalue'], $monthSearch[ $k ], $row['systemalerts_id'], $operatorCustom);
					$bs->getWarningAlerts ($row['indicator_id'], $monthSearch[ $k ], $row['systemalerts_id'], $operatorCustom, $normalizedValue);
				}
			}
		}

		public static function getDetailAlertById (PearDatabase $adb, $systemAlertsId, $typeAlert, $scale = '') {
			$detailalert = array ();
			$whereScale  = '';
			if ($typeAlert == 'Indicators') {
				if(!empty ($scale)) {
					$whereScale = " AND sa.scale='{$scale}' AND  b.scale='{$scale}'";
				}
				$result = $adb->pquery (
					"SELECT sa.systemalerts_id,
								sa.alert,
								sa.description,
								sa.code_app,
								sa.source_alert,
								sa.indicator_id,
								sa.boxscore_id,
								sa.scale,
								sa.condition_alert,
								sa.value_alert,
								sa.automatic
						FROM vtiger_boxscore b
						INNER JOIN vtiger_box_score_data d ON d.boxscoreid = b.boxscoreid
						INNER JOIN vtiger_box_score_data dd ON dd.box_score_dataid = d.datarel
						INNER JOIN vtiger_boxscore bb ON bb.boxscoreid =  dd.boxscoreid
						INNER JOIN vtiger_systemalerts sa ON sa.indicator_id = d.box_score_dataid
						WHERE sa.systemalerts_id = ? {$whereScale}",
					array ($systemAlertsId)
				);
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					return array ();
				}
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$detailalert['alert']           = $row['alert'];
					$detailalert['description']     = $row['description'];
					$detailalert['code_app']        = $row['code_app'];
					$detailalert['source_alert']    = $row['source_alert'];
					$detailalert['indicator_id']    = $row['indicator_id'];
					$detailalert['boxscore_id']     = $row['boxscore_id'];
					$detailalert['scale']           = $row['scale'];
					$detailalert['condition_alert'] = $row['condition_alert'];
					$detailalert['value_alert']     = $row['value_alert'];
					$detailalert['automatic']       = $row['automatic'];
					$detailalert['systemalerts_id'] = $row['systemalerts_id'];
				}
			} else {
				$result = $adb->pquery (
					'SELECT sa.systemalerts_id,
								sa.alert,
								sa.description,
								sa.code_app,
								sa.source_alert,
								sa.tab_id,
								sa.tab_label,
								sa.tab_name,
								sa.field_id,
								sa.field_name,
								sa.scale,
								sa.condition_alert,
								sa.value_alert,
								sa.automatic,
								sa.status,
								sa.users_ids
						FROM vtiger_systemalerts sa
						WHERE sa.systemalerts_id = ?',
					array ($systemAlertsId)
				);
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					return array ();
				}
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$detailalert['alert']           = $row['alert'];
					$detailalert['description']     = $row['description'];
					$detailalert['code_app']        = $row['code_app'];
					$detailalert['source_alert']    = $row['source_alert'];
					$detailalert['tab_id']          = $row['tab_id'];
					$detailalert['tab_label']       = $row['tab_label'];
					$detailalert['tab_name']        = $row['tab_name'];
					$detailalert['field_id']        = $row['field_id'];
					$detailalert['field_name']      = $row['field_name'];
					$detailalert['scale']           = $row['scale'];
					$detailalert['condition_alert'] = $row['condition_alert'];
					$detailalert['value_alert']     = $row['value_alert'];
					$detailalert['automatic']       = $row['automatic'];
					$detailalert['systemalerts_id'] = $row['systemalerts_id'];
					$detailalert['status']          = $row['status'];
					$detailalert['users_ids']       = $row['users_ids'];
				}
			}
			return $detailalert;
		}

		public static function getAlertsModules (PearDatabase $adb, $scaleSearch, $app, $from, $to) {
			$monthSearch = SystemAlertsHelper::getMonthSearch ($from, $to);
			$monthFrom   = explode ('-', $from);
			$monthTo     = explode ('-', $to);
			$year        = date ('Y');
			$from        = date ('Y-m-d', mktime (0, 0, 0, $monthFrom[1], 1, date ('Y')));
			$day         = date ('d', mktime (0, 0, 0, ($monthTo[1] + 1), 0, date ('Y')));
			$to          = date ('Y-m-d', mktime (0, 0, 0, $monthTo[1], $day, $year));
			$result = $adb->pquery (
				"SELECT sa.*
					FROM
						vtiger_systemalerts sa
					LEFT OUTER JOIN vtiger_systemalerts_occurrences sao ON sao.systemalerts_id=sa.systemalerts_id AND sao.date_alert >= ? AND sao.date_alert <= ?
					WHERE sa.scale = ? AND sa.code_app = ? AND source_alert = 'Task_object_no_cump' AND sa.automatic = '0' AND sa.status = '1'",
				array ($from, $to, $scaleSearch, $app)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$months = IndicatorsPanelHelper::getMonths ();
			if ($scaleSearch == 'Week') {
				$dates = SystemAlertsHelper::getDatesWeeks ($months, $monthSearch, $year);
			} else {
				$dates = SystemAlertsHelper::getDatesMonths ($monthSearch, $year);
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$adb->pquery ('DELETE FROM vtiger_systemalerts_occurrences WHERE systemalerts_id = ? AND date_alert >= ? AND date_alert <= ?', array ($row['systemalerts_id'], $from, $to));
				$sqlWhereJoin = self::getSqlWhereCondition ($adb, $row);
				$np           = count ($dates);
				if ($scaleSearch == 'Week') {
					for ($i = 0; $i < ($np - 1); $i++) {
						$from = $dates[ $i ]['date_from'];
						if (($i + 1) < $np) {
							$to = $dates[ ($i + 1) ]['date_from'];
						}
						self::getSqlAlertModule ($adb, $row, $sqlWhereJoin, $from, $to, $scaleSearch, $row['systemalerts_id']);
					}
				} else {
					for ($i = 0; $i < $np; $i++) {
						$from = $dates[ $i ]['date_from'];
						$to   = $dates[ $i ]['date_to'];
						self::getSqlAlertModule ($adb, $row, $sqlWhereJoin, $from, $to, $scaleSearch, $row['systemalerts_id']);
					}
				}
			}
		}

		private function getSqlWhereCondition (PearDatabase $adb, $row) {
			$module  = ($row['source_alert'] == 'Task_prog') ? 'calendar' : $row['tab_name'];
			$alertId = $row ['systemalerts_id'];
			$result  = $adb->pquery (
				'SELECT
					  	sf.*,
						sfg.operator AS goperator
					  FROM
						vtiger_systemalerts_filtergroups sfg
					  INNER JOIN vtiger_systemalerts_filters sf ON sf.groupid = sfg.groupid AND sf.systemalerts_id = sfg.systemalerts_id
					  WHERE
						sfg.systemalerts_id=? AND
						sfg.modulename=?
					  ORDER BY sfg.groupid ASC, sf.sequence ASC',
				array($alertId, $module)
			);
			if ($adb->num_rows ($result)) {
				$thisGroup      = 0;
				$conditions      = array();
				$group = array();
				$fieldObject = FieldManager::getInstance ($adb);
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!count ($group)) {
						$group []  = $row['groupid'];
						$thisGroup = $row['groupid'];
						$conditions[ $thisGroup ]['operator'] = $row['goperator'];
					} else if (!in_array ($row['groupid'], $group)) {
						$group []  = $row['groupid'];
						$thisGroup = $row['groupid'];
						$conditions[ $thisGroup ]['operator'] = $row['goperator'];
					}
					$fieldData                               = $fieldObject->fetchFieldByName ($row['modulename'], $row['fieldname'], true);
					$comparatorAndValue                      = self::getSqlOperator ($row['comparator'], $row['value'],$fieldData);
					$conditions[ $thisGroup ]['condition'][] = "({$comparatorAndValue}) {$row['operator']}";
				}
				$where = ' (';
				foreach ($conditions as $condition) {
					$where .= '(' . join (' ', $condition['condition']) . ") {$condition['operator']} ";
				}
				$where .= ') ';
			}
			
			return array (
				'sql'  => $where,
				'join' => '',
			);
		}

		private function getSqlAlertModule (PearDatabase $adb, $row, $sqlWhereJoin, $from, $to, $scale, $systemalertsId) {
			if ($scale == 'Week') {
				$sqlSelect = 'SELECT WEEK(vtiger_crmentity.createdtime) param_ap, count(vtiger_crmentity.crmid) cont FROM ';
			} else {
				$sqlSelect = 'SELECT MONTH(vtiger_crmentity.createdtime) param_ap, count(vtiger_crmentity.crmid) cont FROM ';
			}
			$condSql    = '';
			$module     = $row['tab_name'];
			$entityData = SystemAlertsHelper::getEntityField ($adb, $module);
			$sqlSelect  .= "{$entityData['tablename']} ";
			if ($entityData['tablename'] != 'vtiger_crmentity') {
				$sqlSelect .= ' INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = ';
				$sqlSelect .= "{$entityData ['tablename']}.{$entityData ['entityidfield']}";
			} else {
				$condSql = " AND vtiger_crmentity.setype = '{$module}' ";
			}

			if (!empty($sqlWhereJoin['join'])) {
				$sqlSelect .= $sqlWhereJoin['join'];
			}
			$sqlWhere = " WHERE 1 {$condSql} AND (vtiger_crmentity.deleted = 0 AND vtiger_crmentity.createdtime >= '{$from}' AND vtiger_crmentity.createdtime < '{$to}') ";

			if (!empty($sqlWhereJoin['sql'])) {
				$sqlWhere .= " AND {$sqlWhereJoin['sql']}";
			}
			if ($scale == 'Week') {
				$sqlGroup = ' GROUP BY WEEK(vtiger_crmentity.createdtime)';
			} else {
				$sqlGroup = ' GROUP BY MONTH(vtiger_crmentity.createdtime)';
			}
			$sql = $sqlSelect . $sqlWhere . $sqlGroup;
			
			$result = $adb->pquery ($sql, array ());
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			
			$today = date("Y-m-d");
			$weekRows = $adb->run_query_allrecords("SELECt param_ap FROM vtiger_systemalerts_occurrences WHERE systemalerts_id={$systemalertsId} AND (from_period <='{$today}' AND to_period >='{$today}')");
			if (!empty ($weekRows)) {
				$weeks = array_column ($weekRows, 'param_ap');
			}
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				if ( isset ($weeks) && in_array ($row ['param_ap'], $weeks)) {
					continue;
				}
				$adb->pquery (
					'INSERT INTO vtiger_systemalerts_occurrences (systemalerts_id, date_alert, count_alert, from_period, to_period, param_ap) VALUES(?, ?, ?, ?, ?, ?)',
					array ($systemalertsId, $today, $row['cont'], $from, $to, $row['param_ap'])
				);
			}
		}

		public static function getAlertsCalendars (PearDatabase $adb, $scaleSearch, $app, $from, $to) {
			$monthSearch = SystemAlertsHelper::getMonthSearch ($from, $to);
			$monthFrom   = explode ('-', $from);
			$monthTo     = explode ('-', $to);

			$year = date ('Y');
			$from = date ('Y-m-d', mktime (0, 0, 0, $monthFrom[1], 1, date ('Y')));
			$day  = date ('d', mktime (0, 0, 0, ($monthTo[1] + 1), 0, date ('Y')));
			$to   = date ('Y-m-d', mktime (0, 0, 0, $monthTo[1], $day, $year));
			
			$result = $adb->pquery (
				"SELECT sa.*
					FROM
						vtiger_systemalerts sa
					LEFT OUTER JOIN vtiger_systemalerts_occurrences sao ON sao.systemalerts_id=sa.systemalerts_id AND sao.date_alert >= ? AND sao.date_alert <= ?
					WHERE sa.scale = ? AND sa.code_app = ? AND source_alert IN ('Task_prog','Task_no_ejec') AND sa.automatic = '0' AND sa.status = '1'",
				array ($from, $to, $scaleSearch, $app)
			);
			
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return;
			}
			$months = IndicatorsPanelHelper::getMonths ();
			if ($scaleSearch == 'Week') {
				$dates = SystemAlertsHelper::getDatesWeeks ($months, $monthSearch, $year);
			} else {
				$dates = SystemAlertsHelper::getDatesMonths ($monthSearch, $year);
			}

			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$adb->pquery ('DELETE FROM vtiger_systemalerts_occurrences WHERE systemalerts_id = ? AND date_alert >= ? AND date_alert <= ?', array ($row['systemalerts_id'], $from, $to));
				$sqlWhereJoin = self::getSqlWhereCondition ($adb, $row);
				$np = count ($dates);
				if ($scaleSearch == 'Week') {
					for ($i = 0; $i < ($np - 1); $i++) {
						$from = $dates[ $i ]['date_from'];
						if (($i + 1) < $np) {
							$to = $dates[ ($i + 1) ]['date_from'];
						}
						self::getSqlAlertCalendar ($adb, $row, $from, $to, $scaleSearch, $row['systemalerts_id'], $sqlWhereJoin);
					}
				} else {
					for ($i = 0; $i < $np; $i++) {
						$from = $dates[ $i ]['date_from'];
						$to   = $dates[ $i ]['date_to'];
						self::getSqlAlertCalendar ($adb, $row, $from, $to, $scaleSearch, $row['systemalerts_id'], $sqlWhereJoin);
					}
				}
			}
		}

		public static function getSqlAlertCalendar (PearDatabase $adb, $row, $from, $to, $scaleSearch, $systemAlertsId, $sqlWhereJoin) {
			$whereCondition = '';
			$whereModule    = '';
			if (!empty ($row['tab_name'])) {
				$whereModule = self::getActivityIdsByModules($adb, $row['tab_name']);
			}
			if (!empty ($sqlWhereJoin) && is_array ($sqlWhereJoin) && !empty ($sqlWhereJoin ['sql'])) {
				$whereCondition = $sqlWhereJoin ['sql'] . 'AND';
			}
			if ($scaleSearch == 'Week') {
				$sql      = 'SELECT WEEK(vtiger_activity.date_start) param_ap,
								COUNT(vtiger_activity.activityid) num_alerts';
				$sqlGroup = ' GROUP BY WEEK(vtiger_activity.date_start)';
			} else {
				$sql      = 'SELECT MONTH(vtiger_activity.date_start) param_ap,
								COUNT(vtiger_activity.activityid) num_alerts';
				$sqlGroup = ' GROUP BY MONTH(vtiger_activity.date_start)';
			}
			$sql .= " FROM vtiger_activity
					LEFT JOIN vtiger_activitycf ON vtiger_activitycf.activityid = vtiger_activity.activityid
					LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
					LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
					WHERE vtiger_activity.activityid > 0 AND vtiger_crmentity.deleted = 0 AND activitytype != 'Emails' AND
					{$whereModule}
					{$whereCondition}
					(vtiger_activity.date_start >= ? AND vtiger_activity.date_start <= ?)";
			$sql = $sql . $sqlGroup;
			$result   = $adb->pquery ($sql, array ($from, $to));
			$today    = date("Y-m-d");
			$weekRows = $adb->run_query_allrecords("SELECt param_ap FROM vtiger_systemalerts_occurrences WHERE systemalerts_id={$systemAlertsId} AND (from_period <='{$today}' AND to_period >='{$today}')");
			if (!empty ($weekRows)) {
				$weeks = array_column ($weekRows, 'param_ap');
			}
			if ($adb->num_rows ($result) > 0) {
				while ($rowResult = $adb->fetchByAssoc ($result, -1, false)) {
					if ( isset ($weeks) && in_array ($row ['param_ap'], $weeks)) {
						continue;
					}
					$adb->pquery (
						'INSERT INTO vtiger_systemalerts_occurrences (systemalerts_id, date_alert, count_alert, from_period, to_period, param_ap) VALUES(?, ?, ?, ?, ?, ?)',
						array ($systemAlertsId, $today, $rowResult['num_alerts'], $from, $to, $rowResult['param_ap'])
					);
				}
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return integer
		 * @throws Exception
		 */
		public static function getTotalAlerts ($adb, $userId) {
			$first = new DateTime();
			$first->modify ('-1 day');
			$from = $first->format ('Y-m-d');
			$last = new DateTime();
			$last->modify ('last day of this month');
			$to    = $last->format ('Y-m-d');
			$total = 0;
			$result = $adb->pquery(
				'SELECT
						IFNULL(SUM(count_alert), 0) AS total
					  FROM
					  	vtiger_systemalerts_occurrences so
					  WHERE
					  NOT EXISTS (SELECT islooked FROM vtiger_systemalerts_users WHERE systemalerts_ocurrence_id= so.systemalerts_ocurrence_id AND userid=? AND (so.date_alert BETWEEN from_period AND to_period))
					  AND date_alert >= ? AND date_alert <= ?',
				array($userId, $from, $to)
			);
			if ($adb->num_rows ($result) > 0) {
				$row   = $adb->fetchByAssoc ($result, -1, false);
				$total = $row['total'];
			}
			return $total;
		}
		
	}

<?php

	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('modules/systemalerts/lib/SystemAlertsHelper.class.php');

	class IndicatorsPanel {
		/** @var PearDatabase */
		private $adb;
		private $app;
		private $months;
		/** @var string */
		private $calculatedName;

		public $boxs           = array ();
		public $boxsdefault    = array ();
		public $dates          = array ();
		public $calculate      = array ();
		public $warning        = array ();
		public $scale          = null;
		

		private function createBoxScoreData (array $data) {
			$row               = null;
			$boxScoreDataId    = null;
			$boxScoreData      = array ();
			$boxScoreDataIdNew = '';
			$boxScoreName      = IndicatorsPanelHelper::getBoxScoreName ($data ['box_score']);
			$this->adb->pquery (
				'INSERT INTO vtiger_box_score_data(name, box_score, objective, fulfillment, type, description, defaultplatzilla, querykpi, querykpiweekly, module, boxscoreid, sourcemodule, calculatedname, calculated_system) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array ($boxScoreName, $data['box_score'], $data['objetive_scale'], $data['fulfillment'], $data['type'], $data['description'], $data['defaultplatzilla'], $data['querykpi'], $data['querykpiweekly'], $data['module'], $data['boxscoreid'], $data['fldmodule'], $data['calculateField'], $data['calculationEngine'])
			);
			$boxScoreDataId = $this->adb->getLastInsertID ();

			// Creating Alert
			SystemAlertsHelper::creatingAlertIndicatorFromIndicator ($this->adb, $this->app, $this->scale, $boxScoreDataId, $data['boxscoreid']);

			$row = IndicatorsPanelHelper::getBlockIdRel ($this->adb, $data['type']);
			if (count ($row) > 0) {
				$this->adb->pquery (
 					'INSERT INTO vtiger_box_score_data(name, box_score, objective, fulfillment, type, description, defaultplatzilla, querykpi, querykpiweekly, module, boxscoreid, datarel, sourcemodule, calculatedname, calculated_system) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($boxScoreName, $data['box_score'], $data['objetive_scale'], $data['fulfillment'], $row['type'], $data['description'], $data['defaultplatzilla'], $data['querykpi'], $data['querykpiweekly'], $data['module'], $row['boxscoreid'], $boxScoreDataId, $data['fldmodule'], $data['calculateField'], $data['calculationEngine'])
				);
				$boxScoreDataIdNew = $this->adb->getLastInsertID ();
				array_push ($boxScoreData, $boxScoreDataIdNew);

				// Creating Alert boxscore_dataID rel
				SystemAlertsHelper::creatingAlertIndicatorFromIndicator ($this->adb, $this->app, $row['scale'], $boxScoreDataIdNew, $row['boxscoreid']);
			}
			array_push ($boxScoreData, $boxScoreDataId);

			$this->adb->pquery (
				'UPDATE vtiger_box_score_data SET datarel=? WHERE box_score_dataid=?',
				array ($boxScoreDataIdNew, intval ($boxScoreDataId))
			);

			return $boxScoreData;
		}

		private function getObjectiveByBoxScoreDataId ($boxScoreDataId, $month, $from, $to) {
			if (!$boxScoreDataId) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT
							*
						FROM
							vtiger_box_score_objective
						WHERE
							box_score_dataid=? AND
							month_apli=? AND
							date_from=? AND
							date_end=?
						LIMIT 1',
				array ($boxScoreDataId, $month, $from, $to)
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}
			return $this->adb->fetchByAssoc ($result, -1, false);
		}

		private function getWeeklyCalculations ($boxScoreDataIdList, $operationList, $boxScoreId, $monthSearch) {
			$subSelectClauses         = self::getWeeklyCalculationsSubSelectClauses ($boxScoreDataIdList, $operationList, $boxScoreId, $monthSearch);
			$scoreDetailClauses       = (!empty($boxScoreDataIdList) && $boxScoreDataIdList != ',') ? "bd.box_score_dataid IN({$boxScoreDataIdList})" : 1;
			$whereClausesAndArguments = self::getWeeklyCalculationsWhereClausesAndArguments ($boxScoreId, $monthSearch);
			$whereClauses             = $whereClausesAndArguments['whereclauses'];
			$arguments                = $whereClausesAndArguments['arguments'];
			$result                   = $this->adb->pquery (
				"SELECT
						REPLACE(({$subSelectClauses}), ',', '.') AS cal,
						WEEK(date,1) AS week
					FROM
						vtiger_box_score_data bd
						INNER JOIN vtiger_box_score_data_weekly bds ON bd.box_score_dataid=bds.box_score_dataid
					WHERE
						{$scoreDetailClauses}
						{$whereClauses}
					GROUP BY
						WEEK(date, 1)
					ORDER BY
						date ASC",
				$arguments
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return array ();
			}
			$weeklyCalculations = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$weeklyCalculations[ $row['week'] ] = $row;
			}
			return $weeklyCalculations;
		}

		private function getWeeklyCalculationsSubSelectClauses ($boxScoreDataIdList, $operationList, $boxScoreId, $monthSearch) {
			$boxScoreDataIds      = explode (',', $boxScoreDataIdList);
			$operations           = explode (',', $operationList);
			$subSelectClauses     = '';
			$totalBoxScoreDataIds = count ($boxScoreDataIds);
			$totalOperations      = count ($operations);
			for ($i = 0; $i < $totalBoxScoreDataIds; $i++) {
				$weeklyData = self::getWeeklyData ($boxScoreDataIds[ $i ], $boxScoreId, $monthSearch);
				if (count ($weeklyData) > 0) {
					$subSelectClauses .= "(SELECT(REPLACE(REPLACE(bds1.value, ',', '.'), '%', '')) FROM vtiger_box_score_data_weekly bds1 WHERE bds1.boxscoreid={$boxScoreId} AND bds1.date=bds.date AND bds1.box_score_dataid={$boxScoreDataIds[$i]}  LIMIT 1)";
				} else {
					$subSelectClauses .= '( 0 )';
				}
				if ($i < $totalOperations) {
					$subSelectClauses .= " {$operations[$i]}";
				}
			}
			return $subSelectClauses;
		}

		private function getWeeklyCalculationsWhereClausesAndArguments ($boxScoreId, $monthSearch) {
			$whereClauses   = array ();
			$arguments      = array ();
			$whereClauses[] = 'bds.boxscoreid=?';
			$arguments[]    = $boxScoreId;

			if ($monthSearch != '') {
				$month = $this->months[ $monthSearch ];
			} else {
				$monthSearch = date ('m');
				$month       = date ('F');
			}
			$year = date ('Y');
			$day  = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
			if ($this->scale == 'Week') {
				$from           = date ('Y-m-d', strtotime ("first monday of {$month} {$year}"));
				$to             = date ('Y-m-d', strtotime ("last monday of {$month} {$year}"));
				$whereClauses[] = 'date>=?';
				$whereClauses[] = 'date<=?';
				$arguments[]    = $from;
				$arguments[]    = $to;
			} else {
				$from           = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
				$to             = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));
				$whereClauses[] = "date>=('{$from}' - INTERVAL 2 MONTH)";
				$whereClauses[] = "date<=('{$to}' + INTERVAL 2 MONTH)";
			}
			return array (
				'whereclauses' => count ($whereClauses) > 0 ? 'AND ' . join (' AND ', $whereClauses) : '',
				'arguments'    => $arguments,
			);
		}

		public function getWeeklyData ($boxScoreDataId, $boxScoreId, $monthSearch, $alert = false, $systemalertsId = '') {
			if ($monthSearch != '') {
				$month = $this->months[ $monthSearch ];
			} else {
				$monthSearch = date ('m');
				$month       = date ('F');
			}
			$year                     = date ('Y');
			$day                      = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
			$from                     = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
			$to                       = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));
			$whereClausesAndArguments = self::getWeeklyDataWhereClausesAndArguments ($boxScoreId, $from, $to, $month, $year);
			$whereClause              = $whereClausesAndArguments['whereclauses'];
			$arguments                = $whereClausesAndArguments['arguments'];
			$result                   = $this->adb->pquery (
				"SELECT *, WEEK(date,1) AS weeks FROM vtiger_box_score_data_weekly s WHERE s.box_score_dataid=? $whereClause ORDER BY s.date ASC",
				array_merge (array ($boxScoreDataId), $arguments)
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return array ();
			}
			$values     = $this->getWeeklyValueByBoxScoreId ($boxScoreDataId, $monthSearch);
			$weeklyData = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$weeklyData[ $row['weeks'] ] = $row;
				$normalizedValue             = $values ? $values['normalizedvalue'] : '';
				if (!$alert) {
					IndicatorsPanelHelper::updateFulfillmentValue ($this->adb, $row['box_score_dataid'], $normalizedValue, null);
				} else {
					IndicatorsPanelHelper::updateFulfillmentValueAlert ($this->adb, $row['box_score_dataid'], $normalizedValue, $row['date'], null, $systemalertsId);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $weeklyData;
		}

		private function getWeeklyDataWhereClausesAndArguments ($boxScoreId, $from, $to, $month, $year) {
			$whereClauses[] = 's.boxscoreid=?';
			$arguments[]    = $boxScoreId;

			if ($this->scale == 'Week') {
				$whereClauses[] = 's.date>=?';
				$whereClauses[] = 's.date<=?';
				$arguments[]    = date ('Y-m-d', strtotime ("first monday of {$month} {$year}"));
				$arguments[]    = date ('Y-m-d', strtotime ("last monday of {$month} {$year}"));
			} else {
				$whereClauses[] = "s.date>=('{$from}' - INTERVAL 2 MONTH)";
				$whereClauses[] = "s.date<=('{$to}' + INTERVAL 2 MONTH)";
			}
			return array (
				'whereclauses' => count ($whereClauses) > 0 ? 'AND ' . join (' AND ', $whereClauses) : '',
				'arguments'    => $arguments,
			);
		}

		public function getWeeklyValueByBoxScoreId ($boxScoreDataId, $monthSearch, $includeWeeks = false) {
			if ($monthSearch != '') {
				$month = $this->months[ $monthSearch ];
			} else {
				$monthSearch = date ('m');
				$month       = date ('F');
			}
			$year         = date ('Y');
			$day          = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
			$whereClauses = array ();
			$arguments    = array ();
			if ($this->scale == 'Week') {
				$from           = date ('Y-m-d', strtotime ("first monday of {$month} {$year}"));
				$to             = date ('Y-m-d', strtotime ("last monday of {$month} {$year}"));
				$whereClauses[] = 's.date>=?';
				$whereClauses[] = 's.date<=?';
				$whereClauses[] = "(CASE WHEN MONTH(s.date)<10 THEN CONCAT('0', MONTH(s.date)) ELSE MONTH(s.date) END)={$monthSearch}";
				if ($includeWeeks) {
					$whereClauses[] = "WEEK(date,1) IN({$this->getWeeksWhereClause($from, $to)})";
				}
				$arguments[] = $from;
				$arguments[] = $to;
			} else {
				$from           = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
				$to             = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));
				$whereClauses[] = "s.date>=('{$from}' - INTERVAL 2 MONTH)";
				$whereClauses[] = "s.date<=('{$to}' + INTERVAL 2 MONTH)";
				$whereClauses[] = "(CASE WHEN MONTH(s.date)<10 THEN CONCAT('0', MONTH(s.date)) ELSE MONTH(s.date) END)={$monthSearch}";
				if ($includeWeeks) {
					$whereClauses[] = "WEEK(date,1) IN({$this->getWeeksWhereClause($from, $to)})";
				}
			}
			$whereClauses = count ($whereClauses) > 0 ? 'AND ' . join (' AND ', $whereClauses) : '';

			$result = $this->adb->pquery (
				"SELECT
							(REPLACE(REPLACE(value, ',', '.'), '%', '')) AS normalizedvalue,
							value AS realvalue,
							WEEK(date,1) AS weekly,
							date AS datevalue
						FROM
							vtiger_box_score_data_weekly s
						WHERE
							s.box_score_dataid=? AND
							(REPLACE(REPLACE(value, ',', '.'), '%', ''))<>''
							{$whereClauses}
						ORDER BY
							date DESC
						LIMIT 1",
				array_merge (array ($boxScoreDataId), $arguments)
			);

			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$resultArray = array (
					'normalizedvalue' => IndicatorsPanelHelper::formatDecimal ($row['normalizedvalue']),
					'realvalue'       => $row['realvalue'],
					'datevalue'       => $row['datevalue'],
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($resultArray)) ? $resultArray : null;
		}

		private function getWeeksWhereClause ($from, $to) {
			$startDate = new DateTime($from);
			$endDate   = new DateTime($to);
			$interval  = $startDate->diff ($endDate);
			$week      = intval (($interval->days) / 7);
			$weeks     = array ();
			for ($i = $week; $i >= 0; $i--) {
				$week    = IndicatorsPanelHelper::checkLastWeek (strtotime ("{$to} -{$i} week"));
				$weeks[] = date ('W', $week);
			}
			return join (', ', $weeks);
		}

		private function updateBoxScoreData (array $data) {
			$result = $this->adb->pquery (
				'SELECT box_score_dataid FROM vtiger_box_score_data WHERE  name=?',
				array ($data['box_score_name'])
			);
			
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$this->adb->pquery (
						'UPDATE vtiger_box_score_data SET box_score=?, objective=?, fulfillment=?, description=?, defaultplatzilla=?, querykpi=?, querykpiweekly=?, calculatedname=?, calculated_system=? WHERE box_score_dataid = ?',
						array ($data['box_score'], $data['objetive_scale'], $data['fulfillment'], $data['description'], $data['defaultplatzilla'], $data['querykpi'], $data['querykpiweekly'], $data['calculateField'], $data['calculationEngine'], $row['box_score_dataid'])
					);
					$boxScoreDataId [] = $row['box_score_dataid'];
				}
				
			}
			return (isset ($boxScoreDataId)) ? $boxScoreDataId : array();
		}

		public function add (array $data, $monthSearch, $mode, $isInstance) {
			if ($mode == 'edit') {
				$boxScoreData = self::updateBoxScoreData ($data);
			} else {
				$boxScoreData = self::createBoxScoreData ($data);
			}
			if ($isInstance) {
				$actualDieOnError = $this->adb->dieOnError;
				$this->adb->setDieOnError (false);
				$this->adb->pquery ('UPDATE vtiger_boxscore_blocks SET locked = ? WHERE type = ?', array ($isInstance, $data['type']));
				$this->adb->pquery ('UPDATE vtiger_boxscore_blocks SET locked = ? WHERE blockrel = ?', array ($isInstance, $data['type']));
				$this->adb->setDieOnError ($actualDieOnError);
			}
			if ($data ['target_month'][0] == 'all') {
				$monthActual = intval (date ('m'));
				$totalMonths = ($monthActual !== 12) ? (12 - $monthActual) : 1;
				$index       = 0;
				for ($k = 0; $k <= $totalMonths; $k++) {
					$targetMonth[] =  $monthActual + $index;
					$objectives [] = $data ['objetive'][0];
					$operator []   = $data ['operator'][0];
					$index++;
				}
			} else if ($data['objetive_scale'] === 'WEEK') {
				$targetMonth = array_keys ($data['week_target']);
			} else {
				$targetMonth = $data ['target_month'];
				$objectives  = $data ['objetive'];
				$operator    = $data ['operator'];
			}
			IndicatorsPanelHelper::deleteRelatedObjectives ($this->adb, $boxScoreData, $data);
			$year = date ('Y');
			for ($kk = 0; $kk < 2; $kk++) {
				$boxScoreDataId = $boxScoreData[ $kk ];
				if (empty($boxScoreDataId)) {
					continue;
				}
				foreach ($targetMonth as $key => $month) {
					if ($data['objetive_scale'] === 'MONTH') {
						$lastDays   = cal_days_in_month(CAL_GREGORIAN, $month, $year);
						$this->adb->pquery (
							'INSERT INTO vtiger_box_score_objective(box_score_dataid, objective, operator, month_apli, week_apli, date_from, date_end) VALUES(?, ?, ?, ?, ?, ?, ?)',
							array(
								$boxScoreDataId,
								$objectives [$key],
								$operator [$key],
								$month,
								0,
								date ('Y-m-d', strtotime ('01-' . $month . '-' . $year)),
								date ('Y-m-d', strtotime ($lastDays . '-' . $month . '-' . $year)),
							)
						);
						$objectiveId = $this->adb->getLastInsertID ();
						IndicatorsPanelHelper::updateInObjectiveFulfillment ($this->adb, $data, $boxScoreDataId, $objectiveId);
						IndicatorsPanelHelper::updateCloseToObjectiveFulfillment ($this->adb, $data, $boxScoreDataId, $objectiveId);
						$values = self::getWeeklyValueByBoxScoreId ($boxScoreDataId, $monthSearch);
						IndicatorsPanelHelper::updateFulfillmentValue ($this->adb, $boxScoreDataId, $values ? $values['normalizedvalue'] : '', $monthSearch);			// OBTENER SEMANAS DE LA META
					} else {
						$totalWeeks = count ($data ['week'][$month]);
						for ($k = 0; $k < $totalWeeks; $k++) {
							$this->adb->pquery (
								'INSERT INTO vtiger_box_score_objective(box_score_dataid, objective, operator, month_apli, week_apli, date_from, date_end) VALUES(?, ?, ?, ?, ?, ?, ?)',
								array(
									$boxScoreDataId,
									$data['objetive'][$month][$k],
									$data['operator'][$month][$k],
									$month,
									$data ['week'][$month][$k],
									date ('Y-m-d', strtotime ($data ['week_target'][$month]['from'][$k])),
									date ('Y-m-d', strtotime ($data ['week_target'][$month]['to'][$k])),
								)
							);
							$objectiveId = $this->adb->getLastInsertID ();
							IndicatorsPanelHelper::updateInObjectiveFulfillment ($this->adb, $data, $boxScoreDataId, $objectiveId);
							IndicatorsPanelHelper::updateCloseToObjectiveFulfillment ($this->adb, $data, $boxScoreDataId, $objectiveId);
							$values = self::getWeeklyValueByBoxScoreId ($boxScoreDataId, $monthSearch);
							IndicatorsPanelHelper::updateFulfillmentValue ($this->adb, $boxScoreDataId, $values ? $values['normalizedvalue'] : '', $monthSearch);
						}
					}
				}
			}
			if (isset($data['record']) && !empty($data['record']) && !empty($boxScoreDataId)) {
				$processedMonths = join (', ', $data['targetmonth']);
				$this->adb->pquery (
					"DELETE FROM vtiger_box_score_data_cump WHERE box_score_dataid in (?,?) AND box_score_objectiveid NOT IN(
								SELECT box_score_objectiveid FROM vtiger_box_score_objective WHERE box_score_dataid in (?,?) AND month_apli IN({$processedMonths})
							)",
					array ($data['record'], $boxScoreDataId, $data['record'], $boxScoreDataId)
				);

				$this->adb->pquery (
					"DELETE FROM vtiger_box_score_objective WHERE box_score_dataid in (?,?) AND month_apli NOT IN({$processedMonths})",
					array ($data['record'], $boxScoreDataId)
				);
			}
			return $boxScoreData[0];
		}

		public function deleteCalculation ($operationId) {
			$row = IndicatorsPanelHelper::getCalcIdRel ($this->adb, $operationId);
			$this->adb->pquery ('DELETE FROM vtiger_boxscore_operation WHERE operation_id IN (?,?)', array ($operationId, $row['operation_id']));
		}
		
		/**
		 * @return string
		 */
		public function getCaculatedName () {
			return $this->calculatedName;
		}
		
		public function init (PearDatabase $adb, $monthSearch, $recordId, $from, $to) {
			$this->adb    = $adb;
			$this->months = IndicatorsPanelHelper::getMonths ();
			$this->scale  = IndicatorsPanelHelper::getScale ($this->adb, $recordId);
			$this->dates  = IndicatorsPanelHelper::getDates ($from, $to, $this->scale, $this->months, $monthSearch);
			$this->app    = IndicatorsPanelHelper::getApp ($this->adb, $recordId);
		}

		public function getBasicDataByBoxScoreDataIds ($boxScoreId, $boxScoreDataIds, $type) {
			$whereClausesAndArguments = IndicatorsPanelHelper::getDataWhereClausesAndArguments ($boxScoreId, $boxScoreDataIds, $type);
			$whereClause              = $whereClausesAndArguments['whereclauses'];
			$arguments                = $whereClausesAndArguments['arguments'];
			$result                   = $this->adb->pquery ("SELECT vbsd.* FROM vtiger_box_score_data vbsd {$whereClause}", $arguments);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return array ();
			}
			$data = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$data[ $row['box_score_dataid'] ] = $row['box_score'];
			}
			return $data;
		}

		public function getBlocks ($boxScoreID, $type) {
			$whereClause = ' WHERE 1 ';
			$arguments   = array ();

			if ($boxScoreID != '') {
				$whereClause .= ' AND vbsd.boxscoreid=?';
				$arguments[] = $boxScoreID;
			}
			if ($type != '') {
				$whereClause .= ' AND vbsd.type=?';
				$arguments[] = $type;
			}

			$result = $this->adb->pquery (
				"SELECT
						vbsd.*
					FROM
						vtiger_boxscore_blocks vbsd
						INNER JOIN vtiger_boxscore vbs ON vbsd.boxscoreid = vbs.boxscoreid
					{$whereClause}
					ORDER BY
						vbsd.type ASC",
				$arguments
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return array ();
			}

			$blocks = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$blocks[] = $row;
			}
			return $blocks;
		}

		public function getCalculationEdition ($calculationId) {
			$result = $this->adb->pquery ('SELECT vbsd.* FROM vtiger_boxscore_operation vbsd WHERE vbsd.operation_id=?', array ($calculationId));
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return array ();
			}
			$row                     = $this->adb->fetchByAssoc ($result, -1, false);
			$row['boxscore_data_id'] = explode (',', $row['elements']);
			$row['operators_list']   = explode (',', $row['operators']);
			return $row;
		}

		public function getCalculations ($boxScoreId, $monthSearch) {
			$whereClause = '';
			$arguments   = array ();
			if ($boxScoreId != '') {
				$whereClause = 'vbsd.boxscoreid=?';
				$arguments[] = $boxScoreId;
			}
			$whereClause = count ($whereClause) > 0 ? "WHERE {$whereClause}" : '';
			$result      = $this->adb->pquery (
				"SELECT
						vbsd.*,
						bk.colorbase,
						bk.colordegrade
					FROM
						vtiger_boxscore_operation vbsd
						INNER JOIN vtiger_boxscore_blocks bk ON bk.type=vbsd.type
					{$whereClause}
					ORDER BY
						operation_id, type ASC",
				$arguments
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return array ();
			}
			$calculations = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$calculations[] = array (
					'operation_id' => $row['operation_id'],
					'boxscoreid'   => $row['boxscoreid'],
					'calculation'  => $row['calculation'],
					'weeklytotal'  => self::getWeeklyCalculations ($row['elements'], $row['operators'], $boxScoreId, $monthSearch),
					'type'         => $row['type'],
					'user'         => $row['user'],
					'colorbase'    => $row['colorbase'],
					'colordegrade' => $row['colordegrade'],
				);
			}
			return $calculations;
		}

		public function loadBasicDataByBoxScoreId ($boxScoreId, $type, $crmId = 0) {
			$whereClausesAndArguments = IndicatorsPanelHelper::getDataWhereClausesAndArguments ($boxScoreId, $crmId, $type);
			$whereClause              = $whereClausesAndArguments['whereclauses'];
			$arguments                = $whereClausesAndArguments['arguments'];
			$result                   = $this->adb->pquery ("SELECT vbsd.* FROM vtiger_box_score_data vbsd {$whereClause}", $arguments);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return array ();
			}
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$this->boxs[] = $row;
			}
			return $this->boxs;
		}

		public function loadData ($boxScoreId, $monthSearch, $type, $crmId = 0, $favorites = array(), $sourceModule = null) {
			$year                     = date ('Y');
			$totalDays                = cal_days_in_month(CAL_GREGORIAN, $monthSearch, $year);
			$day                      = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
			$from                     = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
			$to                       = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $totalDays, $year));
			$joinClause               = '';
			$whereModule              = '';
			$whereClausesAndArguments = IndicatorsPanelHelper::getDataWhereClausesAndArguments ($boxScoreId, $crmId, $type);
			
			$whereClause              = $whereClausesAndArguments['whereclauses'];
			$arguments                = $whereClausesAndArguments['arguments'];
			if (count ($favorites)) {
				$joinClause = 'INNER JOIN vtiger_user2boxscore ub ON ub.boxscorename = vbsd.name';
				$indicatorsName = $this->adb->sql_expr_datalist ($favorites);
				$whereClause .= " AND ub.boxscorename IN{$indicatorsName}";
			}
			if (!empty ($sourceModule)) {
				$whereModule = (!empty($whereClause)) ? " AND vbsd.sourcemodule = '{$sourceModule}' " : " WHERE vbsd.sourcemodule = '{$sourceModule}' ";
			}

			$result = $this->adb->pquery (
				"SELECT
						vbsd.box_score_dataid,
						vbsd.name,
						vbsd.box_score,
						vbsd.type,
						vbsd.boxscoreid,
						vbsd.accountid,
						vbsd.description,
       					vbsd.bsd_status,
       					vbsd.is_editable,
       					vbsd.objective AS objective_scale,
						bk.colorbase,
						bk.colordegrade,
						vbsd.defaultplatzilla,
						vbsd.querykpi,
						vbsd.querykpiweekly,
						vbsd.module,
						vbsd.sourcemodule,
						vbsd.calculatedname,
       					vbsd.calculated_system,
       					vbsd.on_railes,
						o.objective,
						o.operator,
						o.box_score_objectiveid,
						o.objective,
						o.month_apli,
       					o.week_apli,
						 MIN(o.date_from) as date_from,
    					MAX(o.date_end) as date_end
					FROM
						vtiger_box_score_data vbsd
						{$joinClause}
						LEFT OUTER JOIN vtiger_boxscore_blocks bk ON bk.type=vbsd.type
						LEFT OUTER JOIN vtiger_box_score_objective o ON o.box_score_dataid=vbsd.box_score_dataid AND o.month_apli=? AND YEAR(o.date_from)=?
					{$whereClause}
					{$whereModule}
					GROUP BY vbsd.name
					ORDER BY vbsd.type ASC",
				array_merge (array (intval ($monthSearch), $year), $arguments)
			);
			
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return;
			}
			
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				//$objetives
				$values          = self::getWeeklyValueByBoxScoreId ($row['box_score_dataid'], $monthSearch, true);
				$normalizedValue = $values ? $values['normalizedvalue'] : '';
				IndicatorsPanelHelper::updateFulfillmentValue ($this->adb, $row['box_score_dataid'], $normalizedValue, $monthSearch);
				$warning = array ();
				if ($this->scale != 'Week') {
					foreach ($this->dates as $item) {
						$monthWarning = $item['month'];
						if ($monthWarning < 10) {
							$monthWarning = '0' . $monthWarning;
						}
						$valuesWarning            = self::getWeeklyValueByBoxScoreId ($row['box_score_dataid'], $monthWarning, true);
						$normalizedValueWarning   = $valuesWarning ? $valuesWarning['normalizedvalue'] : '';
						$warning[ $monthWarning ] = IndicatorsPanelHelper::getFulfillmentWarning ($this->adb, $row['box_score_dataid'], $normalizedValueWarning, $monthWarning, $monthSearch);
					}
				}
				$subTitle = (!empty($row['date_from']) && !empty($row['date_end'])) ? "<br><small> {$row['date_from']} - {$row['date_end']}</small>" : '<br><small style="color: red">No hay objetivos definidos</small>';
				
				$row['box_score']                          = $row['box_score'] . $subTitle;
				$row['target_month']                       = $row['month_apli'];
				$row['all_objetive']                       = IndicatorsPanelHelper::getObjectives ($this->adb, $row['box_score_dataid'], $year);
				$row['weekly']                             = self::getWeeklyData ($row['box_score_dataid'], $boxScoreId, $monthSearch);
				$row['cump_array']                         = IndicatorsPanelHelper::getFulfillmentsByBoxScoreDataId ($this->adb, $row['box_score_dataid']);
				$row['fulfillment']                        = IndicatorsPanelHelper::calculateFulfillment ($this->adb, $row['box_score_dataid'], $boxScoreId, $type, $monthSearch);
				$row['scale']                              = $this->scale;
				$this->boxs[]                              = $row;
				$this->warning[ $row['box_score_dataid'] ] = $warning;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		public function saveBlock ($colorbase, $colordegrade, $boxScoreId, $isInstance, $type = '') {
			if ($type != '') {
				$this->adb->pquery ('UPDATE vtiger_boxscore_blocks SET colorbase = ?, colordegrade = ? WHERE type = ?', array ($colorbase, $colordegrade, $type));
				$this->adb->pquery ('UPDATE vtiger_boxscore_blocks SET colorbase = ?, colordegrade = ? WHERE blockrel = ?', array ($colorbase, $colordegrade, $type));
				return $type;
			} else {
				if ($this->scale == 'Month') {
					$scaleNew = 'Week';
				} else {
					$scaleNew = 'Month';
				}
				$blockNumber = rand (10, 99999999);
				$bsDefault = IndicatorsPanelHelper::getIndicatorDefault ($this->adb, $this->app, $scaleNew);
				$this->adb->pquery ('INSERT INTO vtiger_boxscore_blocks(colorbase, colordegrade, boxscoreid, blocknumber, locked) VALUES(?, ?, ?, ?, ?)', array ($colorbase, $colordegrade, $bsDefault['boxscoreid'], $blockNumber, $isInstance));
				$blockIdOne = $this->adb->getLastInsertID ();

				$this->adb->pquery ('INSERT INTO vtiger_boxscore_blocks(colorbase, colordegrade, boxscoreid, blockrel, blocknumber, locked) VALUES(?, ?, ?, ?, ?, ?)', array ($colorbase, $colordegrade, $boxScoreId, $blockIdOne, $blockNumber, $isInstance));
				$blockIdTwo = $this->adb->getLastInsertID ();

				$this->adb->pquery ('UPDATE vtiger_boxscore_blocks SET blockrel = ? WHERE type = ?', array ($blockIdTwo, $blockIdOne));

				return $blockIdTwo;
			}
		}

		public function saveCalculation ($boxScoreId, $calculation, $elements, $operators, $type) {
			$elementsNew   = array ();
			$elementsArray = explode (',', $elements);
			$n             = count ($elementsArray);
			for ($i = 0; $i < $n; $i++) {
				$row           = IndicatorsPanelHelper::getDataIdRel ($this->adb, $elementsArray[ $i ]);
				$elementsNew[] = $row['box_score_dataid'];
			}
			$elementsString = join (',', $elementsNew);
			$this->adb->pquery (
				'INSERT INTO vtiger_boxscore_operation(boxscoreid, calculation, elements, operators, type) VALUES(?, ?, ?, ?, ?)',
				array ($boxScoreId, $calculation, $elements, $operators, $type)
			);
			$calc = $this->adb->getLastInsertID ();

			$row = IndicatorsPanelHelper::getBlockIdRel ($this->adb, $type);
			$this->adb->pquery (
				'INSERT INTO vtiger_boxscore_operation(boxscoreid, calculation, elements, operators, type, operationrel) VALUES(?, ?, ?, ?, ?, ?)',
				array ($row['boxscoreid'], $calculation, $elementsString, $operators, $row['type'], $calc)
			);
			$newCalc = $this->adb->getLastInsertID ();

			$this->adb->pquery (
				'UPDATE vtiger_boxscore_operation SET operationrel = ? WHERE operation_id = ?',
				array ($newCalc, $calc)
			);

			return $calc;
		}
		
		/**
		 * @param $calculatedName
		 * @return void
		 */
		public function setCalculatedName ($calculatedName) {
			$this->calculatedName = $calculatedName;
		}
		
		public function update (array $data, $monthSearch, $rel = false) {
		$valueUpddate = array ();
			foreach ($data['value'] as $boxScoreDataId => $weeks) {
				$dataVal = array ();
				foreach ($weeks as $week => $value) {
					if ($data['weeklyid'][ $boxScoreDataId ][ $week ]) {
						$this->adb->pquery (
							'UPDATE vtiger_box_score_data_weekly SET box_score_dataid=?, boxscoreid=?, date=?, value=? WHERE weeklyid=?',
							array ($boxScoreDataId, $data['boxscoreid'], $data['date'][ $boxScoreDataId ][ $week ], $value, $data['weeklyid'][ $boxScoreDataId ][ $week ])
						);
						$weeklyid = $data['weeklyid'][ $boxScoreDataId ][ $week ];
					} else {
						$this->adb->pquery (
							'INSERT INTO vtiger_box_score_data_weekly(box_score_dataid, boxscoreid, date, value) VALUES(?, ?, ?, ?)',
							array ($boxScoreDataId, $data['boxscoreid'], $data['date'][ $boxScoreDataId ][ $week ], $value)
						);
						$weeklyid = $this->adb->getLastInsertID ();
					}

					if ($this->scale == 'Month') {
						$monthUp = explode ('-', $data['date'][ $boxScoreDataId ][ $week ]);
						$this->adb->pquery (
							'UPDATE vtiger_box_score_data_weekly SET value=? WHERE box_score_dataid=? AND boxscoreid=? AND month(date) = ?',
							array ($value, $boxScoreDataId, $data['boxscoreid'], $monthUp[1])
						);
					}
					if ($rel) {
						if ($this->scale == 'Month') {
							$dataVal[] = array ($weeklyid, $data['date'][ $boxScoreDataId ][ $week ], $value);
						} else {
							$dataVal[] = $weeklyid;
						}
					} else {
						$dataVal[] = array ($weeklyid, $data['date'][ $boxScoreDataId ][ $week ], $value);
					}

					$values          = self::getWeeklyValueByBoxScoreId ($boxScoreDataId, $monthSearch, true);
					$normalizedValue = $values ? $values['normalizedvalue'] : '';
					IndicatorsPanelHelper::updateFulfillmentValue ($this->adb, $boxScoreDataId, $normalizedValue, $monthSearch);
				}
				$valueUpddate[ $boxScoreDataId ] = $dataVal;
			}
			return $valueUpddate;
		}

		public function deleteBlock ($type) {
			$row    = null;
			$result = $this->adb->pquery ('SELECT blockrel FROM vtiger_boxscore_blocks WHERE type=?', array ($type));
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$row = $this->adb->fetchByAssoc ($result);
			}

			$this->adb->pquery (
				'DELETE FROM vtiger_box_score_data WHERE type IN (?,?)',
				array ($type, $row['blockrel'])
			);

			$this->adb->pquery (
				'DELETE FROM vtiger_boxscore_operation WHERE type IN (?,?)',
				array ($type, $row['blockrel'])
			);

			$this->adb->pquery (
				'DELETE FROM vtiger_boxscore_blocks WHERE type IN (?,?)',
				array ($type, $row['blockrel'])
			);

			return $type;
		}

		public static function getInstance (PearDatabase $adb, $monthSearch, $recordId, $from, $to) {
			$ip = new IndicatorsPanel ();
			$ip->init ($adb, $monthSearch, $recordId, $from, $to);
			return $ip;
		}

		public function getWarningAlerts ($boxScoreDataId, $monthSearch, $systemalertsId, $operationCustom = '', $valueCustom = '') {
			$warning = array ();
			if ($this->scale != 'Week') {
				foreach ($this->dates as $item) {
					$monthWarning = $item['month'];
					if ($monthWarning < 10) {
						$monthWarning = '0' . $monthWarning;
					}
					$valuesWarning = self::getWeeklyValueByBoxScoreId ($boxScoreDataId, $monthWarning, true);
					if ($operationCustom != '' && $valueCustom != '') {
						$normalizedValueWarning = $valueCustom;
					} else {
						$normalizedValueWarning = $valuesWarning ? $valuesWarning['normalizedvalue'] : '';
					}
					$warning[ $monthWarning ] = IndicatorsPanelHelper::getFulfillmentWarning ($this->adb, $boxScoreDataId, $normalizedValueWarning, $monthWarning, $monthSearch, true, $systemalertsId, $valuesWarning['datevalue'], $operationCustom);
				}
			}
		}

	}

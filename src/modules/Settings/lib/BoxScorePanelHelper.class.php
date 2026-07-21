<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/platzilla/Managers/ScoringBoxManager.php');
	require_once ('include/platzilla/Managers/CalculationElementManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	abstract class BoxScorePanelHelper	{
		
		private static function checkAlert ($targetAdb, $alertName) {
			if (empty ($alertName)) {
				return true;
			}
			$result = $targetAdb->pquery ('SELECT systemalerts_id FROM vtiger_systemalerts WHERE name=?', array ($alertName));
			if ($targetAdb->num_rows ($result) > 0) {
				return true;
			}
			return false;
		}
		
		private static function checkCalculatedElement ($adb, $elementName) {
			if (empty ($elementName)) {
				return true;
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_calculated_fields WHERE element_name=?', array ($elementName));
			
			if ($adb->num_rows ($result) > 0) {
				return true;
			}
			return false;
		}
		
		/**
		 * @return array|null
		 */
		private static function getMonthDatesByWeek () {
			$year        = date ('Y');
			$monthSearch = date ('m');
			$totalDays = cal_days_in_month(CAL_GREGORIAN, $monthSearch, $year);
			$firstWeek 					       = date ('W', strtotime ("$year-$monthSearch-01"));
			for ($day = 1; $day <= $totalDays; $day++) {
				if (checkdate ($monthSearch, $day, $year)) {
					$date    = "$year-$monthSearch-$day";
					$week    = date ('w', strtotime ($date));
					$numWeek = date ('W', strtotime ($date));
					if($week == 1){ // monday
						$weeks [$numWeek]['start'] = date('Y-m-d', strtotime ($date));
					} else if($week == 0){ // sunday
						$weeks [$numWeek]['end'] = date('Y-m-d', strtotime ($date));
					}
				}
			}
			if (!isset ($weeks [$firstWeek]['start']) && ($weeks[$firstWeek]['end'])) {
				$weeks[$firstWeek]['start'] = date('Y-m-d', strtotime($weeks[$firstWeek]['end']. ' - 6 days'));
			}
			if ((isset($weeks[$numWeek]['start'])) && (!isset ($weeks [$numWeek]['end']))) {
				$weeks[$numWeek]['end'] = date('Y-m-d', strtotime($weeks[$numWeek]['start']. ' + 6 days'));
			}
			return (isset ($weeks)) ? $weeks : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param ScoringBox[] $motherBoxScore
		 * @param array $blockRel
		 * @param array $scales
		 * @return void
		 */
		private static function updateBlockRels ($adb, $motherBoxScore, $blockRel, $scales) {
			foreach ($scales as $scale) {
				$adb->pquery (
					'UPDATE vtiger_boxscore_blocks SET blockrel = ? WHERE type = ?',
					array ($blockRel[$scale], $motherBoxScore[$scale]->getBlockScoreBoxes()[0]->getId())
				);
			}
		}
		
		/**
		 * @param ScoringDataBox $motherBoxScoreData
		 *
		 * @return void
		 */
		private static function updateWeeklyObjetives (&$motherBoxScoreData) {
			$weekDates      = self::getMonthDatesByWeek ();
			$totalObjetives = count ($motherBoxScoreData->getScoreObjectivesBoxes ());
			$index		    = 0;
			foreach ($weekDates as $nunWeek => $week) {
				if ($index >= $totalObjetives) {
					break;
				}
				$motherBoxScoreData->getScoreObjectivesBoxes ()[$index]->setWeekApli ($nunWeek);
				$motherBoxScoreData->getScoreObjectivesBoxes ()[$index]->setDateFrom ($week['start']);
				$motherBoxScoreData->getScoreObjectivesBoxes ()[$index]->setDateEnd ($week['end']);
				$index++;
				
			}
		}
		
		/**
		 * @param ScoringDataBox $motherBoxScoreData
		 *
		 * @return void
		 */
		private static function updateMonthlyObjetives (&$motherBoxScoreData) {
			$year        = date ('Y');
			$monthSearch = intval (date ('m'));
			$totalObjetives = count ($motherBoxScoreData->getScoreObjectivesBoxes ());
			
			for ($k = 0; $k < $totalObjetives; $k++) {
				$monthSearch += $k;
				if ($monthSearch > 12) {
					$monthSearch = 1;
					$year++;
				}
				$date = "{$year}-{$monthSearch}-1";
				$motherBoxScoreData->getScoreObjectivesBoxes ()[$k]->setWeekApli (date ('W', strtotime ($date)));
				$motherBoxScoreData->getScoreObjectivesBoxes ()[$k]->setDateFrom ($date);
				$totalDays = cal_days_in_month (CAL_GREGORIAN, $monthSearch, $year);
				$date      = "{$year}-{$monthSearch}-{$totalDays}";
				$motherBoxScoreData->getScoreObjectivesBoxes ()[$k]->setDateEnd ($date);
			}
			
		}
		
		/**
		 * @param $adb
		 * @param $codId
		 * @param $newStatus
		 
		 * @return void
		 * @throws Exception
		 */
		public static function changeStatus ($adb, $bsName, $newStatus) {
			if (empty($bsName)) {
				throw new Exception ('Imposible actualizar, Error en datos!');
			}
			
			$adb->pquery (
				'UPDATE vtiger_box_score_data SET bsd_status=? WHERE name=?',
				array ($newStatus, $bsName)
			);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $bsName
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public static function checkBoxScoreData ($adb, $bsName) {
			if (empty ($bsName)) {
				throw new Exception ('Imposible obtener datos, Error en datos! checkBoxScoreData');
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_box_score_data WHERE name=?', array ($bsName));
			if ($adb->num_rows ($result) > 0) {
				throw new Exception ('Uf, el indicador ya esta registrado en la instancia');
			}
			return true;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param PearDatabase $targetAdb
		 * @param CalculationSystem $calculation
		 *
		 * @return void
		 * @throws CalculationSystemException
		 */
		public static function checkCalculateSystem ($adb, $targetAdb, $calculation) {
			if (empty ($calculation) || !$calculation instanceof CalculationSystem) {
				return;
			}
			$result = $targetAdb->pquery ('SELECT * FROM vtiger_calculated_system WHERE calculated_name=?', array ($calculation->getCalculationName ()));
			if ($targetAdb->num_rows ($result) == 0) {
				$resultEq = $targetAdb->query ('SELECT max(calculated_equationid) + 10  AS eq_id FROM vtiger_calculated_equation WHERE 1');
				$row      = $targetAdb->fetchByAssoc ($resultEq, -1, false);
				
				$calculation->setEquationId ($row['eq_id']);
				$totalEquation = count ($calculation->getEquation ());
				$cem           = CalculationElementManager::getInstance ($adb);
				for ($i = 0; $i < $totalEquation; $i++) {
					$calculation->getEquation()[$i]->setId ($row['eq_id']);
					if ($calculation->getEquation()[$i]->getFirstElementType () =='e') {
						$hasCalculationElement = self::checkCalculatedElement ($targetAdb, $calculation->getEquation()[$i]->getFirstElement ());
						if (!$hasCalculationElement) {
							$calculatedElement = $cem->fetchCalculationElement ($calculation->getEquation ()[$i]->getFirstElement ());
							if (!empty ($calculatedElement)) {
								CalculationElementManager::getInstance ($targetAdb)->saveCalculationElement ($calculatedElement, 'insert');
							}
							unset ($calculatedElement);
						}
					}
					if ($calculation->getEquation()[$i]->getSecondElementType () =='e') {
						$hasCalculationElement = self::checkCalculatedElement ($targetAdb, $calculation->getEquation()[$i]->getSecondElement ());
						if (!$hasCalculationElement) {
							$calculatedElement = $cem->fetchCalculationElement ($calculation->getEquation()[$i]->getSecondElement ());
							if (!empty ($calculatedElement)) {
								CalculationElementManager::getInstance ($targetAdb)->saveCalculationElement ($calculatedElement, 'insert');
							}
							unset ($calculatedElement);
						}
					}
				}
				CalculationSystemManager::getInstance ($targetAdb)->saveCalculationSystem ($calculation, 'insert');
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $fieldName
		 * @param string $moduleName
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public static function checkCalculatedField ($adb, $fieldName, $moduleName) {
			if (empty ($moduleName) || empty ($fieldName)) {
				return;
			}
			$result = $adb->pquery (
				'SELECT
       				f.paradicional
				FROM vtiger_field f
				INNER JOIN vtiger_tab t ON t.tabid = f.tabid
				WHERE f.fieldname=? AND paradicional !=? AND t.name=?',
				array ($fieldName, '', $moduleName)
			);
			if ($adb->num_rows ($result) == 0) {
				throw new Exception ('Uf, El campo con calculo no esta registrado en la instancia');
			}
			return true;
		}
		
		/**
		 * @param PearDatabase $targetAdb
		 * @param  ScoringDataBox[] $motherBoxScoreData
		 * @param  ScoringBox[] $motherBoxScore
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public static function createBoxScore ($targetAdb, &$motherBoxScoreData, &$motherBoxScore, $scales) {
			$blockRel = array();
			foreach ($scales as $scale) {
				if (! $motherBoxScore[$scale] instanceof ScoringBox) {
					continue;
				}
				$daughterBoxScore[$scale] = self::getDefaultBoxScore (
					$targetAdb,
					array ('app_code' => $motherBoxScore[$scale]->getAppCode (), 'scale' => $motherBoxScore[$scale]->getScale ())
				);
				self::saveDefaultBoxScore ($targetAdb, $daughterBoxScore[$scale], $motherBoxScore[$scale]);
				if (empty ($motherBoxScore[$scale]->getBlockScoreBoxes ()[0]->getBlockRel())){
					if ($scale == 'Month') {
						$blockRel['Week'] = $motherBoxScore[$scale]->getBlockScoreBoxes ()[0]->getId();
					} else {
						$blockRel['Month'] = $motherBoxScore[$scale]->getBlockScoreBoxes ()[0]->getId();
					}
				}
			}
			if (count ($blockRel)) {
				$motherBoxScore ['Week']->getBlockScoreBoxes ()[0]->setBlockRel($blockRel['Week']);
				$motherBoxScore ['Month']->getBlockScoreBoxes ()[0]->setBlockRel($blockRel['Month']);
				self::updateBlockRels ($targetAdb, $motherBoxScore, $blockRel, $scales);
			}
			self::saveBoxScoreData ($targetAdb, $motherBoxScoreData, $motherBoxScore, $scales);
		}
		
		/**
		 * @param PearDatabase $targetAdb
		 * @param array $relatedAlerts
		 * @param ScoringDataBox[] $motherBoxScoreData
		 * @param ScoringBox[] $motherBoxScore
		 * @param array $scales
		 *
		 * @return void
		 */
		public static function createRelatedAlerts ($targetAdb, $relatedAlerts, $motherBoxScoreData, $motherBoxScore, $scales) {
			if (empty ($relatedAlerts)) {
				return;
			}
			foreach ($scales as $scale) {
				if (! $motherBoxScore[$scale] instanceof ScoringBox) {
					continue;
				}
				$totalAlerts = count ($relatedAlerts);
				for ($k = 0; $k < $totalAlerts; $k++) {
					$hasAlert = self::checkAlert ($targetAdb, $relatedAlerts[$k]['name']);
					if ($hasAlert) {
						continue;
					}
					if (strtolower ($relatedAlerts[$k]['code_app']) == strtolower ($motherBoxScore[$scale]->getAppCode ())) {
						$relatedAlerts[$k]['indicator_id'] = $motherBoxScoreData[$scale]->getId ();
						$relatedAlerts[$k]['boxscore_id']  = $motherBoxScore[$scale]->getId ();
						$targetAdb->pquery (
						'INSERT INTO vtiger_systemalerts(name, code_app, alert, source_alert, indicator_id, boxscore_id, automatic, status, scale, description) VALUES(?,?, ?, ?, ?, ?, ?, ?, ?, ?)',
							array (
								$relatedAlerts[$k]['name'],
								$relatedAlerts[$k]['code_app'],
								$relatedAlerts[$k]['alert'],
								$relatedAlerts[$k]['source_alert'],
								$relatedAlerts[$k]['indicator_id'],
								$relatedAlerts[$k]['boxscore_id'],
								$relatedAlerts[$k]['automatic'],
								$relatedAlerts[$k]['status'],
								$relatedAlerts[$k]['scale'],
								$relatedAlerts[$k]['description'],
							)
						);
					}
				}
			}
			
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $recordId
		 * @param string $bsName
		 *
		 * @return void
		 * @throws Exception
		 */
		public static function deleteIndicator ($adb, $recordId, $bsName) {
			if (!is_numeric ($recordId) || empty ($bsName)) {
				throw new Exception ('Imposible eliminar Indicador, Error en datos!');
			}
			$result = $adb->pquery ('DELETE FROM vtiger_box_score_data WHERE name=?', array ($bsName));
			if ($result) {
				$adb->pquery ('DELETE FROM vtiger_user2boxscore WHERE boxscorename=?', array ($bsName));
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param PearDatabase $targetAdb
		 * @param integer $boxScoreDataId
		 * @param integer $boxScoreId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getRelatedAlerts ($adb, $targetAdb, $boxScoreDataId, $boxScoreId) {
			if (empty ($boxScoreDataId) || empty ($boxScoreId)) {
				return null;
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_systemalerts WHERE indicator_id=? AND boxscore_id=?', array ($boxScoreDataId, $boxScoreId));
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$hasAlert = self::checkAlert ($targetAdb, $row ['name']);
					if ($hasAlert) {
						continue;
					}
					$relatedAlerts [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($relatedAlerts)) ? $relatedAlerts : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $scoringBoxId
		 *
		 * @return BlockScoreBox[]|null
		 */
		public static function getBoxScoreBlock ($adb, $scoringBoxId) {
			if (empty ($scoringBoxId)) {
				return null;
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_boxscore_blocks WHERE boxscoreid=?', array ($scoringBoxId));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$blockScore [] = BlockScoreBox::getInstance ()
					->setBlockNumber ($row ['blocknumber'])
					->setBlockRel ($row ['blockrel'])
					->setColorBase ($row ['colorbase'])
					->setColorDegrade ($row ['colordegrade'])
					->setId ($row ['type'])
					->setLocked (($row ['locked'] == 1) ? true : false)
					->setScoringBoxId ($row ['boxscoreid'])
					->setScoringDataBoxes (null)
					->setUser ($row ['user']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($blockScore)) ? $blockScore : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $bsName
		 *
		 * @return ScoringDataBox
		 * @throws Exception
		 */
		public static function getBoxScoreData ($adb, $bsName, $scale) {
			if (empty ($bsName) || empty ($scale)) {
				throw new Exception ('Imposible obtener datos, Error en datos!');
			}
			$result = $adb->pquery (
				'SELECT
       					bsd.*
					FROM vtiger_box_score_data bsd
					INNER JOIN vtiger_boxscore bs ON bsd.boxscoreid = bs.boxscoreid
					WHERE bsd.name=? AND bs.scale=?',
				array ($bsName, $scale)
			);
			if ($adb->num_rows ($result) > 0) {
				$fm  = FieldManager::getInstance ($adb);
				$csm = CalculationSystemManager::getInstance ($adb);
				$sbm = ScoringBoxManager::getInstance ($adb);
				$row = $adb->fetchByAssoc ($result, -1, false);
				$scoringData = ScoringDataBox::getInstance ()
					->setAccountId ($row ['accountid'])
					->setBoxScore ($row ['box_score'])
					->setBlockScoreBoxId ($row ['type'])
					->setCalculatedName ($row ['calculatedname'])
					->setCalculatedSystem ($csm->fetchCalculationSystem ($row ['calculated_system']))
					->setCalculatedSystemName ($row ['calculated_system'])
					->setCreatedDate ($row ['dt_created'])
					->setDataRel ($row ['datarel'])
					->setDefaultPlatzilla(($row ['defaultplatzilla'] == 1) ? true : false)
					->setDescription ($row ['description'])
					->setFulfillment ($row ['fulfillment'])
					->setId ($row ['box_score_dataid'])
					->setIsEditable ($row ['is_editable'])
					->setModuleName ($row ['module'])
					->setName ($row ['name'])
					->setObjective ($row ['objective'])
					->setQueryKpi ($row ['querykpi'])
					->setQueryKpiWeekly ($row ['querykpiweekly'])
					->setScoringBoxId ($row ['boxscoreid'])
					->setScoreObjectivesBoxes ($sbm->fetchScoreObjectivesBox ($row ['box_score_dataid']))
					->setSourceModule ($row ['sourcemodule'])
					->setStatus ($row ['bsd_status']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($scoringData)) ? $scoringData : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $whereArray
		 *
		 * @return ScoringBox|null
		 * @throws Exception
		 */
		public static function getDefaultBoxScore ($adb, $whereArray) {
			if (!is_array ($whereArray) || empty ($whereArray)) {
				return null;
			}
			$where  = '';
			$params = array ();
			foreach ($whereArray as $key => $value) {
				if (empty ($where)) {
					$where = "{$key} = ?";
				} else {
					$where .= "  AND {$key} = ?";
				}
				$params [] = $value;
			}
			
			$result = $adb->pquery ("SELECT * FROM vtiger_boxscore WHERE {$where} LIMIT 1", array_values ($params));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$boxScore = ScoringBox::getInstance ()
					->setAppCode ($row ['app_code'])
					->setBlockScoreBoxes (self::getBoxScoreBlock ($adb, $row ['boxscoreid']))
					->setDefault ($row ['isdefault'])
					->setDescription ($row ['description'])
					->setId ($row ['boxscoreid'])
					->setScale ($row ['scale'])
					->setTitle ($row ['title']);
				
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($boxScore)) ? $boxScore : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $codId
		 * @param string $newStatus
		 *
		 * @return void
		 * @throws Exception
		 */
		public static function setEditable ($adb, $codId, $newStatus) {
			if (!is_numeric ($codId)) {
				throw new Exception ('Imposible actualizar, Error en datos!');
			}
			
			$adb->pquery (
				'UPDATE vtiger_box_score_data SET is_editable=? WHERE box_score_dataid=?',
				array ($newStatus, $codId)
			);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param ScoringBox $motherBoxScore
		 *
		 * @return void
		 */
		public static function saveBoxScoreBlock ($adb, &$motherBoxScore) {
			$motherBoxScore->getBlockScoreBoxes ()[0]->setBlockNumber (rand (10, 99999999));
			$adb->pquery ('INSERT INTO vtiger_boxscore_blocks(colorbase, colordegrade, boxscoreid, blocknumber, locked) VALUES (?, ?, ?, ?, ?)',
				array (
					$motherBoxScore->getBlockScoreBoxes ()[0]->getColorBase (),
					$motherBoxScore->getBlockScoreBoxes ()[0]->getColorDegrade (),
					$motherBoxScore->getId (),
					$motherBoxScore->getBlockScoreBoxes ()[0]->getBlockNumber (),
					1
				)
			);
			$motherBoxScore->getBlockScoreBoxes ()[0]->setId ($adb->getLastInsertID ());
			$motherBoxScore->getBlockScoreBoxes ()[0]->setBlockRel (null);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param ScoringDataBox[] $motherBoxScoreData
		 * @param ScoringBox[] $motherBoxScore
		 * @param $scales
		 * @return void
		 */
		public static function saveBoxScoreData ($adb, &$motherBoxScoreData, $motherBoxScore, $scales) {
			$sbm = ScoringBoxManager::getInstance ($adb);
			foreach ($scales as $scale) {
				if ($motherBoxScoreData[$scale]->getObjective () == 'WEEK') {
					self::updateWeeklyObjetives ($motherBoxScoreData[$scale]);
				} else if ($motherBoxScoreData[$scale]->getObjective () == 'MONTH') {
					self::updateMonthlyObjetives ($motherBoxScoreData[$scale]);
				}
				$blocScoreBoxId = $motherBoxScore [$scale]->getId ();
				$scoringBoxId   = $motherBoxScore [$scale]->getBlockScoreBoxes()[0]->getId();
				
				$boxScoreDataId = $sbm->saveScoringDataBox ( $scoringBoxId, $blocScoreBoxId, array ($motherBoxScoreData[$scale]));
				$motherBoxScoreData[$scale]->setId ($boxScoreDataId[0]);
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param ScoringBox $daughterBoxScore
		 * @param ScoringBox $motherBoxScore
		 * @param string $scale
		 *
		 * @return void
		 */
		public static function saveDefaultBoxScore ($adb, $daughterBoxScore, &$motherBoxScore) {
			if (empty ($daughterBoxScore) || !$daughterBoxScore instanceof ScoringBox) {
				$adb->pquery (
					'INSERT INTO vtiger_boxscore (title, date, description, app_code, scale) VALUES (?, NOW(), ?, ?, ?)',
					array ($motherBoxScore->getTitle(), $motherBoxScore->getDescription(), $motherBoxScore->getAppCode(), $motherBoxScore->getScale())
				);
				$motherBoxScore->setId ($adb->getLastInsertID ());
				self::saveBoxScoreBlock ($adb, $motherBoxScore);
			} else if (empty ($daughterBoxScore->getBlockScoreBoxes()[0])) {
				$motherBoxScore->setId ($daughterBoxScore->getId ());
				self::saveBoxScoreBlock ($adb, $motherBoxScore);
			} else {
				$motherBoxScore->setId ($daughterBoxScore->getId ());
				$motherBoxScore->getBlockScoreBoxes()[0]->setId ($daughterBoxScore->getBlockScoreBoxes()[0]->getId ());
				$motherBoxScore->getBlockScoreBoxes()[0]->setBlockRel ($daughterBoxScore->getBlockScoreBoxes()[0]->getBlockRel ());
			}
		}
		
	}

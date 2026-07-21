<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/CalculationSystemManager.php');
	require_once ('include/platzilla/Objects/BlockScoreBox.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Objects/ScoreObjectivesBox.php');
	require_once ('include/platzilla/Objects/ScoringBox.php');
	require_once ('include/platzilla/Objects/ScoringDataBox.php');
	require_once ('include/platzilla/Objects/ScoringDataCompBox.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	
	class ScoringBoxManager {

		/** @var ScoringBoxManager|null */
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		/** @var array */
		private $blockScoringIds;

		/** @var array */
		private $scoringDataIds;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
			$this->blockScoringIds = array ();
			$this->scoringDataIds  = array ();
		}

		/**
		 * @param string $title
		 * @param string $scale
		 *
		 * @return boolean
		 */
		private function boxScoreRow ($title, $scale) {
			if (empty ($title) || empty($scale)) {
				return null;
			}

			$boxScoreId = null;
			$scoringBoxRow = $this->adb->run_query_allrecords("SELECT boxscoreid FROM vtiger_boxscore WHERE title='{$title}' AND scale='{$scale}'");
			if (!empty($scoringBoxRow)) {
				$boxScoreId = $scoringBoxRow [0]['boxscoreid'];
			}
			return $boxScoreId;
		}

		/**
		 * @param integer $scoringBoxId
		 * @param boolean $ignoreLock
		 * @param array $lockedBlocks
		 *
		 * @throws Exception
		 */
		private function deleteScoringBoxes ($scoringBoxId, $ignoreLock, $lockedBlocks) {
			if (empty($scoringBoxId)) {
				return;
			}

			$isLocked       = ($ignoreLock) ? '' : 'vtiger_boxscore_blocks.locked = 0 AND';
			$dataIdsLocked  = (!count ($this->scoringDataIds)) ? '( 0 )' : $this->adb->sql_expr_datalist ($this->scoringDataIds);
			$blockNumLocked = (!count ($lockedBlocks)) ? '( 0 )' : $this->adb->sql_expr_datalist ($lockedBlocks);
			$this->adb->query (
				"DELETE
						vtiger_boxscore_blocks,
						vtiger_box_score_data,
						vtiger_box_score_objective,
						vtiger_box_score_data_cump
					  FROM 
						vtiger_boxscore
					  LEFT JOIN vtiger_boxscore_blocks ON vtiger_boxscore_blocks.boxscoreid = vtiger_boxscore.boxscoreid 
					  LEFT JOIN vtiger_box_score_data ON vtiger_box_score_data.boxscoreid = vtiger_boxscore.boxscoreid
					  LEFT JOIN vtiger_box_score_objective ON vtiger_box_score_objective.box_score_dataid = vtiger_box_score_data.box_score_dataid
					  LEFT JOIN vtiger_box_score_data_cump ON vtiger_box_score_data_cump.box_score_dataid = vtiger_box_score_data.box_score_dataid AND vtiger_box_score_data_cump.box_score_objectiveid = vtiger_box_score_objective.box_score_objectiveid
					  WHERE
					   {$isLocked} 
					   vtiger_boxscore_blocks.blocknumber NOT IN {$blockNumLocked} AND 
					   vtiger_box_score_data.box_score_dataid NOT IN {$dataIdsLocked} AND 
					   vtiger_boxscore.boxscoreid = {$scoringBoxId}"
			);
		}

		/**
		 * @param integer $scoringBoxId
		 *
		 * @return BlockScoreBox[]|null
		 * @throws Exception
		 */
		private function fetchBlockScoreBoxes ($scoringBoxId) {
			if (empty($scoringBoxId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_boxscore_blocks WHERE boxscoreid=?', array ($scoringBoxId));
			if ($this->adb->num_rows ($result) > 0) {
				$blockScore = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$blockScore [] = BlockScoreBox::getInstance ()
						->setBlockNumber ($row ['blocknumber'])
						->setBlockRel ($row ['blockrel'])
						->setColorBase ($row ['colorbase'])
						->setColorDegrade ($row ['colordegrade'])
						->setId ($row ['type'])
						->setLocked(($row ['locked'] == 1) ? true : false)
						->setScoringBoxId ($row ['boxscoreid'])
						->setScoringDataBoxes ($this->fetchScoringDataBoxes ($row ['type'], $row ['boxscoreid']))
						->setUser ($row ['user']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($blockScore)) ? $blockScore : null;
		}

		/**
		 * @param integer $blockScoreBoxId
		 * @param integer $scoringBoxId
		 *
		 * @return ScoringDataBox[]|null
		 * @throws Exception
		 */
		private function fetchScoringDataBoxes ($blockScoreBoxId, $scoringBoxId) {
			if ((empty ($blockScoreBoxId)) || (empty ($scoringBoxId))) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_box_score_data WHERE type=? AND boxscoreid=?', array ($blockScoreBoxId, $scoringBoxId));
			if ($this->adb->num_rows ($result) > 0) {
				$scoringData = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$scoringData [] = ScoringDataBox::getInstance ()
						->setAccountId ($row ['accountid'])
						->setBoxScore ($row ['box_score'])
						->setBlockScoreBoxId ($row ['type'])
						->setCalculatedName ($row ['calculatedname'])
						->setCalculatedSystemName ($row ['calculated_system'])
						->setDataRel ($row ['datarel'])
						->setDefaultPlatzilla(($row ['defaultplatzilla'] == 1) ? true : false)
						->setDescription ($row ['description'])
						->setFulfillment ($row ['fulfillment'])
						->setId ($row ['box_score_dataid'])
						->setModuleName ($row ['module'])
						->setName ($row ['name'])
						->setObjective ($row ['objective'])
						->setQueryKpi ($row ['querykpi'])
						->setQueryKpiWeekly ($row ['querykpiweekly'])
						->setScoringBoxId ($row ['boxscoreid'])
						->setScoreObjectivesBoxes ($this->fetchScoreObjectivesBox ($row ['box_score_dataid']))
						->setSourceModule ($row ['sourcemodule']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($scoringData)) ? $scoringData : null;
		}

		/**
		 * @param integer $scoringDataBoxId
		 * @param integer $scoreObjectivesBoxId
		 *
		 * @return ScoringDataCompBox[]|null
		 * @throws Exception
		 */
		private function fetchScoringDataCompBox ($scoringDataBoxId, $scoreObjectivesBoxId) {
			if (empty ($scoringDataBoxId) || empty($scoreObjectivesBoxId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_box_score_data_cump WHERE box_score_dataid=? AND box_score_objectiveid=?', array ($scoringDataBoxId, $scoreObjectivesBoxId));
			if ($this->adb->num_rows ($result) > 0) {
				$scoreDataComp = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$scoreDataComp [] = ScoringDataCompBox::getInstance ()
						->setFulfillment ($row ['fulfillment'])
						->setId ($row ['id'])
						->setLabel ($row ['label'])
						->setLowerValue ($row ['lower_value'])
						->setScoringDataBoxId ($row ['box_score_dataid'])
						->setScoreObjectivesBoxId ($row ['box_score_objectiveid'])
						->setTopValue ($row ['top_value'])
						->setTypeDataHigher ($row ['type_data_sup'])
						->setTypeDataLower ($row ['type_data_inf'])
						->setVarianceType ($row ['type_variance'])
						->setVarianceValue ($row ['value_variance']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($scoreDataComp)) ? $scoreDataComp : null;
		}

		/**
		 * @param integer $scoringDataBoxId
		 *
		 * @return ScoreObjectivesBox[]|null
		 * @throws Exception
		 */
		public function fetchScoreObjectivesBox ($scoringDataBoxId) {
			if (empty ($scoringDataBoxId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_box_score_objective WHERE box_score_dataid=?', array ($scoringDataBoxId));
			if ($this->adb->num_rows ($result) > 0) {
				$scoreObjective = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$scoreObjective [] = ScoreObjectivesBox::getInstance ()
						->setDateEnd ($row ['date_end'])
						->setDateFrom ($row ['date_from'])
						->setFulfillment ($row ['fulfillment'])
						->setId ($row ['box_score_objectiveid'])
						->setMonthApli ($row ['month_apli'])
						->setWeekApli ($row ['week_apli'])
						->setObjective ($row ['objective'])
						->setOperator ($row ['operator'])
						->setScoringDataBoxId ($row ['box_score_dataid'])
						->setScoringDataCompBox ($this->fetchScoringDataCompBox ($scoringDataBoxId, $row ['box_score_objectiveid']))
						->setWeekApli ($row ['week_apli']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($scoreObjective)) ? $scoreObjective : null;
		}

		/**
		 * @return array
		 */
		private function getLockedBlocks () {
			$lockedIds = $this->adb->run_query_allrecords('SELECT blocknumber FROM vtiger_boxscore_blocks WHERE locked = 1');
			if (!empty ($lockedIds)) {
				return array_column($lockedIds, 'blocknumber');
			} else {
				return array ();
			}
		}

		private function getScoringDataIds () {
			$boxScoreDataIds = $this->adb->run_query_allrecords(
				'SELECT DISTINCT 
							bsd.box_score_dataid 
						FROM 
							vtiger_box_score_data bsd
						INNER JOIN vtiger_boxscore_blocks bb ON bb.type = bsd.type
						WHERE 
							bb.locked = 1'
			);
			if (!empty ($boxScoreDataIds)) {
				$this->scoringDataIds = array_column($boxScoreDataIds, 'box_score_dataid');
			}
		}

		/**
		 * @param integer $scoringBoxId
		 * @param BlockScoreBox[] $blockScoreBoxes
		 * @param array $lockedBlocks
		 */
		private function saveBlockScoreBoxes ($scoringBoxId, $blockScoreBoxes, $lockedBlocks) {
			if (empty ($scoringBoxId) || empty ($blockScoreBoxes)) {
				return;
			}
			foreach ($blockScoreBoxes as $blockScore) {
				if ((empty ($blockScore)) || !($blockScore instanceof BlockScoreBox)) {
					continue;
				} else if (in_array ($blockScore->getBlockNumber (), $lockedBlocks)) {
					continue;
				}

				$isLocked = ($blockScore->isLocked ()) ? 1 : 0;
				$this->adb->pquery (
					'INSERT INTO vtiger_boxscore_blocks (colorbase, colordegrade, boxscoreid, blockrel, blocknumber, locked) VALUES (?, ?, ?, ?, ?, ?)',
					array ($blockScore->getColorBase (), $blockScore->getColorDegrade(), $scoringBoxId, $blockScore->getBlockRel (), $blockScore->getBlockNumber () ,$isLocked)
				);
				$this->blockScoringIds [ $blockScore->getId () ] = $this->adb->getLastInsertID ();
				$this->saveScoringDataBox ($this->adb->getLastInsertID (), $scoringBoxId, $blockScore->getScoringDataBoxes ());
			}
		}

		/**
		 * @param integer $blocScoreBoxId
		 * @param integer $scoringBoxId
		 * @param ScoringDataBox[] $scoringDataBoxes
		 */
		public function saveScoringDataBox ($blocScoreBoxId, $scoringBoxId, $scoringDataBoxes) {
			if (empty($blocScoreBoxId) || empty($scoringBoxId) || empty ($scoringDataBoxes)) {
				return;
			}

			foreach ($scoringDataBoxes as $scoringDataBox) {
				if ((empty($scoringDataBox)) || !($scoringDataBox instanceof ScoringDataBox)) {
					continue;
				}

				$this->adb->pquery (
					'INSERT INTO vtiger_box_score_data (name, box_score, objective, fulfillment, type, boxscoreid, accountid, description, defaultplatzilla, querykpi, querykpiweekly, module, datarel, sourcemodule, calculatedname, calculated_system, bsd_status, is_editable) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?)',
					array (
						$scoringDataBox->getName (),
						$scoringDataBox->getBoxScore (),
						$scoringDataBox->getObjective (),
						$scoringDataBox->getFulfillment (),
						$blocScoreBoxId,
						$scoringBoxId,
						$scoringDataBox->getAccountId (),
						$scoringDataBox->getDescription (),
						($scoringDataBox->isDefaultPlatzilla()) ? 1 : 0,
						$scoringDataBox->getQueryKpi (),
						$scoringDataBox->getQueryKpiWeekly(),
						$scoringDataBox->getModuleName (),
						$scoringDataBox->getDataRel (),
						$scoringDataBox->getSourceModule (),
						$scoringDataBox->getCalculatedName (),
						$scoringDataBox->getCalculatedSystemName (),
						$scoringDataBox->getStatus (),
						'NO'
					)
				);
				$dataBoxIds[] = $this->adb->getLastInsertID ();
				$this->saveScoreObjectivesBox ($this->adb->getLastInsertID (), $scoringDataBox->getScoreObjectivesBoxes ());
				return $dataBoxIds;
			}
		}

		/**
		 * @param integer $scoringDataBoxId
		 * @param integer $scoreObjectivesBoxId
		 * @param ScoringDataCompBox[] $scoringDataCompBoxes
		 */
		private function saveScoringDataCompBox ($scoringDataBoxId, $scoreObjectivesBoxId, $scoringDataCompBoxes) {
			if (empty($scoringDataBoxId) || empty($scoringDataCompBoxes) || empty($scoreObjectivesBoxId)) {
				return;
			}
			foreach ($scoringDataCompBoxes as $scoringDataComp) {
				if ((empty($scoringDataComp)) || !($scoringDataComp instanceof ScoringDataCompBox)) {
					continue;
				}

				$this->adb->pquery (
					'INSERT INTO vtiger_box_score_data_cump (fulfillment, box_score_dataid, value_variance, type_variance, lower_value, top_value, type_data_inf, type_data_sup, label, box_score_objectiveid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array (
						$scoringDataComp->getFulfillment (),
						$scoringDataBoxId,
						$scoringDataComp->getVarianceValue (),
						$scoringDataComp->getVarianceType (),
						$scoringDataComp->getLowerValue (),
						$scoringDataComp->getTopValue (),
						$scoringDataComp->getTypeDataLower (),
						$scoringDataComp->getTypeDataHigher (),
						$scoringDataComp->getLabel (),
						$scoreObjectivesBoxId,
					)
				);
			}
		}

		/**
		 * @param integer $scoringDataBoxId
		 * @param ScoreObjectivesBox[] $scoreObjectivesBoxes
		 */
		private function saveScoreObjectivesBox ($scoringDataBoxId, $scoreObjectivesBoxes) {
			if (empty($scoringDataBoxId) || empty($scoreObjectivesBoxes)) {
				return;
			}
			foreach ($scoreObjectivesBoxes as $scoreObjective) {
				if ((empty($scoreObjective)) || !($scoreObjective instanceof ScoreObjectivesBox)) {
					continue;
				}

				$this->adb->pquery (
					'INSERT INTO vtiger_box_score_objective (box_score_dataid, objective, month_apli, week_apli, date_from, date_end, fulfillment, operator) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
					array (
						$scoringDataBoxId,
						$scoreObjective->getObjective (),
						$scoreObjective->getMonthApli (),
						$scoreObjective->getWeekApli (),
						$scoreObjective->getDateFrom (),
						$scoreObjective->getDateEnd (),
						$scoreObjective->getFulfillment (),
						$scoreObjective->getOperator (),
					)
				);
				$this->saveScoringDataCompBox ($scoringDataBoxId, $this->adb->getLastInsertID (), $scoreObjective->getScoringDataCompBox());
			}
		}

		private function upDateBlockRel () {
			if (empty ($this->blockScoringIds)) {
				return;
			}
			foreach ($this->blockScoringIds as $key => $blockScoringId) {
				$this->adb->pquery (
					'UPDATE vtiger_boxscore_blocks  SET blockrel=? WHERE blockrel=?',
					array ($blockScoringId, $key)
				);
			}
		}

		/**
		 * @param string $moduleName
		 *
		 * @return ScoringBox[]|null
		 * @throws Exception
		 */
		public function fetchScoringBoxes ($moduleName) {
			if ($moduleName != 'indicatorspanel') {
				return null;
			}
			$parameter = array(
				1,
				ScoringBox::SCALE_MONTH,
				ScoringBox::SCALE_WEEK,
			);

			$result = $this->adb->pquery ('SELECT * FROM vtiger_boxscore WHERE isdefault=? AND scale IN(?, ?)  ORDER BY title DESC, boxscoreid DESC', $parameter);
			if ($this->adb->num_rows ($result) > 0) {
				$scoringBoxes = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$boxScoreBlock = $this->fetchBlockScoreBoxes ($row ['boxscoreid']);
					if(!empty($boxScoreBlock)) {
						$scoringBoxes[] = ScoringBox::getInstance()
							->setAppCode($row ['app_code'])
							->setBlockScoreBoxes ($boxScoreBlock)
							->setCreatedDate($row ['date'])
							->setDefault(($row ['isdefault'] == 1) ? true : false)
							->setDescription($row ['description'])
							->setId($row ['boxscoreid'])
							->setScale($row ['scale'])
							->setTitle($row ['title']);
					}
					$boxScoreBlock = null;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($scoringBoxes)) ? $scoringBoxes : null;
		}
		
		/**
		 * @return ScoringDataBox[]|null
		 * @throws Exception
		 */
		public function fetchScoringAllDataBoxes () {
			$result = $this->adb->query ('SELECT * FROM vtiger_box_score_data WHERE 1 GROUP BY name');
			if ($this->adb->num_rows ($result) > 0) {
				$scoringData = array ();
				$fm = FieldManager::getInstance ($this->adb);
				$csm = CalculationSystemManager::getInstance ($this->adb);
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$scoringData [] = ScoringDataBox::getInstance ()
						->setAccountId ($row ['accountid'])
						->setBoxScore ($row ['box_score'])
						->setBlockScoreBoxId ($row ['type'])
						->setCalculatedField ($fm->fetchFieldByName  ($row ['sourcemodule'], $row ['calculatedname'], true))
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
						->setScoreObjectivesBoxes (null)
						->setSourceModule ($row ['sourcemodule'])
						->setStatus ($row ['bsd_status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($scoringData)) ? $scoringData : null;
		}
		
		/**
		 * @param Module $module
		 * @param boolean $ignoreLock
		 *
		 * @return null
		 * @throws Exception
		 */
		public function saveScoringBoxes ($module, $ignoreLock = false) {
			if ((empty ($module)) || (!($module instanceof Module))) {
				return null;
			}
			$scoringBoxes = $module->getScoringBox ();
			if (empty ($scoringBoxes)) {
				return null;
			}
			$lockedBlocks = array ();
			if (!$ignoreLock) {
				$lockedBlocks = $this->getLockedBlocks ();
				$this->getScoringDataIds ();
			}

			foreach ($scoringBoxes as $scoringBox) {
				if ((empty ($scoringBox)) || !($scoringBox instanceof ScoringBox)) {
					continue;
				}

				$scoringBoxRow = $this->boxScoreRow ($scoringBox->getTitle (), $scoringBox->getScale ());
				$default       = ($scoringBox->isDefault()) ? 1 : 0;

				if (!$scoringBoxRow) {
					$this->adb->pquery(
						'INSERT INTO vtiger_boxscore (title, date, description, scale, app_code, isdefault) VALUES (?, ?, ?, ?, ?, ?)',
						array($scoringBox->getTitle(), $scoringBox->getCreatedDate(), $scoringBox->getDescription(), $scoringBox->getScale(), $scoringBox->getAppCode(), $default)
					);
					$scoringBox->setId ($this->adb->getLastInsertID ());
				} else {
					$this->deleteScoringBoxes ($scoringBoxRow, $ignoreLock, $lockedBlocks);
					$scoringBox->setId ($scoringBoxRow);
				}
				$this->saveBlockScoreBoxes ($scoringBox->getId(), $scoringBox->getBlockScoreBoxes (), $lockedBlocks);
			}
			$this->upDateBlockRel ();
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ScoringBoxManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = array ();
			}
			if (!isset (self::$INSTANCE [ $adb->dbName ])) {
				self::$INSTANCE[ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCE [ $adb->dbName ];
		}

	}

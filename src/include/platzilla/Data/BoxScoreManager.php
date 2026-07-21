<?php
	require_once ('include/platzilla/Data/EntityHistoryManager.php');
	require_once ('include/platzilla/Data/BoxScore.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');

	/**
	 * Class BoxScoreManager
	 *
	 * Esta clase hace referencia a los métodos que sirven de utilería en la gestión de bases de datos del Panel de indicadores.
	 */
	class BoxScoreManager {
		
		/** @var BoxScoreManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * Establece el valor por defecto del indicador para la semana actual
		 *
		 * @param integer $scoreId
		 * @param integer $scoreDataId
		 *
		 * @throws Exception
		 */
		private function setDefaultBoxScoreWeekly ($scoreId, $scoreDataId) {
			$monday = date ('Y-m-d',strtotime ('monday this week'));
			$this->adb->pquery (
				'INSERT INTO vtiger_box_score_data_weekly (box_score_dataid, boxscoreid,date) VALUES (?, ?, ?)',
				array ($scoreDataId, $scoreId, $monday)
			);

			$this->fetchBoxScoreWeeklyByScoreId ($scoreId, $scoreDataId);
		}

		/**
		 * Elimina el indicador favorito
		 *
		 * @param integer $userId
		 * @param string $boxScoreName
		 *
		 * @return boolean
		 */
		public function delFavorite ($userId, $boxScoreName) {
			if (empty ($boxScoreName) || empty ($userId) || !is_numeric ($userId)) {
				return false;
			}
			$this->adb->pquery ('DELETE FROM vtiger_user2boxscore WHERE userid=? AND boxscorename=?', array ($userId, $boxScoreName));
			return true;
		}

		/**
		 * Obtiene la lista de los indicadores favoritos
		 *
		 * @param integer $userId
		 *
		 * @return array
		 */
		public function fetchAllFavorites ($userId) {
			if (empty ($userId) || !is_numeric ($userId)) {
				return array ();
			}
			return $this->adb->run_query_allrecords ("SELECT * FROM vtiger_user2boxscore WHERE userid = {$userId}");
		}

		/**
		 * Obtiene lista de indicadores según el nombre del módulo fuente del campo con cálculo asociado
		 *
		 * @param string $moduleName
		 *
		 * @return BoxScore[]|null
		 * @throws Exception
		 */
		public function fetchBoxScoreBySourceModule ($moduleName) {
			if (empty($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
						bs.*
					FROM 
						vtiger_boxscore bs 
					LEFT JOIN vtiger_box_score_data bsd ON bsd.boxscoreid = bs.boxscoreid
					WHERE 
						bsd.sourcemodule=?
					GROUP by bsd.boxscoreid',
				array ($moduleName)
			);

			if ($this->adb->num_rows ($result) > 0) {
				$boxScores = array();
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					$boxScores [] = BoxScore::getInstance()
						->setAppCode ($row ['app_code'])
						->setCreatedDate ($row ['date'])
						->setDefault (($row ['isdefault']) == 1 ? true : false)
						->setDescription ($row ['description'])
						->setId ($row ['boxscoreid'])
						->setScale ($row ['scale'])
						->setDataScores ($this->fetchBoxScoreDataByScoreId ($row ['boxscoreid']))
						->setTitle ($row ['title']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($boxScores)) ? $boxScores : null;
		}

		/**
		 * Obtiene los datos del indicador según su ID
		 *
		 * @param integer $scoreId
		 *
		 * @return BoxScoreData[]|null
		 * @throws Exception
		 */
		public function fetchBoxScoreDataByScoreId ($scoreId) {
			if (empty($scoreId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
						*
					FROM 
						vtiger_box_score_data 
					WHERE 
						boxscoreid=?',
				array ($scoreId)
			);

			if ($this->adb->num_rows ($result) > 0) {
				$dataScrores = array();
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					$dataScrores [] = BoxScoreData::getInstance()
						->setAccountId ($row ['accountid'])
						->setBoxScore ($row ['box_score'])
						->setBoxScoreId ($row ['boxscoreid'])
						->setCalculatedName ($row ['calculatedname'])
						->setDataRel ($row ['datarel'])
						->setDataScoresWeekly ($this->fetchBoxScoreWeeklyByScoreId($scoreId, $row['box_score_dataid']))
						->setDescription ($row ['description'])
						->setDefaultPlatzilla (($row ['defaultplatzilla'] == 1) ? true : false)
						->setFulfillment ($row ['fulfillment'])
						->setId ($row ['box_score_dataid'])
						->setModuleName ($row ['module'])
						->setObjective ($row ['objective'])
						->setQueryKpi ($row ['querykpi'])
						->setQueryKpiWeekly ($row ['querykpiweekly'])
						->setSourceModule ($row ['sourcemodule'])
						->setType ($row ['type']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($dataScrores)) ? $dataScrores : null;
		}

		/**
		 * Obtiene los valores semanales del indicador según su ID
		 *
		 * @param integer $scoreId
		 * @param integer $scoreDataId
		 *
		 * @return BoxScoreDataWeekly[]|null
		 * @throws Exception
		 */
		public function fetchBoxScoreWeeklyByScoreId ($scoreId, $scoreDataId) {
			if (empty($scoreId) || empty($scoreDataId)) {
				return null;
			}

			$month  = date ('m', strtotime ('monday this week'));
			$year   = date ('Y', strtotime ('monday this week'));
			$result = $this->adb->pquery (
				'SELECT
						*
					FROM 
						vtiger_box_score_data_weekly 
					WHERE 
						YEAR (date)=? AND
						(CASE WHEN MONTH(date)<10 THEN CONCAT("0", MONTH(date)) ELSE MONTH(date) END)=? AND
						boxscoreid=? AND 
						box_score_dataid=?
					ORDER BY date DESC',
				array ($year, $month, $scoreId, $scoreDataId)
			);

			if ($this->adb->num_rows ($result) > 0) {
				$dataScoresWeekly = array();
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					$dataScoresWeekly [] = BoxScoreDataWeekly::getInstance()
						->setAccountId ($row ['accountid'])
						->setBoxScoreDataId ($row ['box_score_dataid'])
						->setBoxScoreId ($row ['boxscoreid'])
						->setCreatedDate ($row ['date'])
						->setId ($row ['weeklyid'])
						->setValue ($row ['value']);
				}
			} else {
				DatabaseUtils::closeResult ($result);
				$result = null;
				$this->setDefaultBoxScoreWeekly($scoreId, $scoreDataId);
			}

			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($dataScoresWeekly)) ? $dataScoresWeekly : null;
		}

		/**
		 * Guarda los valores semanales para el indicador respectivo
		 *
		 * @param BoxScoreData $dataScore
		 * @param string $value
		 * @param string $monday
		 */
		public function saveBoxScoreDataWeekly ($dataScore, $value, $objetive) {
			$result = $this->adb->pquery (
				'SELECT
						*
					FROM 
						vtiger_box_score_data_weekly 
					WHERE 
						box_score_dataid=? AND 
						date=?
					ORDER BY date DESC',
				array ($dataScore->getId (), $objetive['date_end'])
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				$this->adb->pquery (
					'UPDATE vtiger_box_score_data_weekly  SET value=? WHERE weeklyid=?',
					array ($value, $row['weeklyid'])
				);
			} else {
				$this->adb->pquery (
					'INSERT INTO vtiger_box_score_data_weekly (box_score_dataid, boxscoreid, accountid, date, value) VALUES (?, ?, NULL, ?, ?)',
					array ($dataScore->getId (), $dataScore->getBoxScoreId (), $objetive['date_end'], $value)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$boxScoreDataRow = $this->adb->run_query_allrecords("SELECT type FROM vtiger_box_score_data WHERE box_score_dataid = {$dataScore->getBoxScoreId ()}");
			if (!empty ($boxScoreDataRow)) {
				$this->setBlockLocked ($boxScoreDataRow [0]['type']);
			}
		}
		
		/**
		 * @param integer $boxscoreId
		 * @param integer $boxscoreDataId
		 * @param integer|float $value
		 * @param string $monday
		 *
		 * @return void
		 * @throws Exception
		 */
		public function saveDataWeekly ($boxscoreId, $boxscoreDataId, $value, $monday) {
			$result = $this->adb->pquery (
				'SELECT
						*
					FROM
						vtiger_box_score_data_weekly
					WHERE
						boxscoreid=? AND
						box_score_dataid=? AND
						date=?
					LIMIT 1',
				array ($boxscoreId, $boxscoreDataId, $monday)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				$this->adb->pquery (
					'UPDATE vtiger_box_score_data_weekly  SET value=? WHERE weeklyid=?',
					array ($value, $row['weeklyid'])
				);
			} else {
				$this->adb->pquery (
					'INSERT INTO vtiger_box_score_data_weekly (box_score_dataid, boxscoreid, date, value) VALUES (?, ?, ?, ?)',
					array ($boxscoreDataId, $boxscoreId, $monday, $value)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$boxScore        = IndicatorsPanel::getInstance ($this->adb, '', $boxscoreId, '', '');
			$values          = $boxScore->getWeeklyValueByBoxScoreId ($boxscoreDataId, '', true);
			$normalizedValue = $values ? $values['normalizedvalue'] : '';
			IndicatorsPanelHelper::updateFulfillmentValue ($this->adb, $boxscoreDataId, $normalizedValue, '');
		}
		
		/**
		 * Guarda el indicador favorito
		 *
		 * @param integer $userId
		 * @param string $boxScoreName
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public function saveFavorite ($userId, $boxScoreName) {
			if (empty ($boxScoreName) || empty ($userId) || !is_numeric ($userId)) {
				return false;
			}

			try {
				$this->adb->run_insert_data ('vtiger_user2boxscore', array ('boxscorename' => $boxScoreName, 'userid' => $userId));
				return true;
			} catch (Exception $e) {
				return false;
			}
		}

		/**
		 * Establece como bloqueado el indicador
		 *
		 * @param integer $blockId
		 */
		public function setBlockLocked ($blockId) {
			if (empty($blockId) || (!preg_match ('/_appef/', $this->adb->dbName))) {
				return;
			}

			$this->adb->pquery (
				'UPDATE vtiger_boxscore_blocks  SET locked=? WHERE type=?',
				array (1, $blockId)
			);
		}

		/**
		 * Se obtiene un objeto BoxScoreManager con los atributos de la clase
		 *
		 * @param PearDatabase $adb
		 *
		 * @return BoxScoreManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}

<?php
	require_once ('include/platzilla/Data/BoxScoreManager.php');
	require_once ('include/platzilla/Data/EntityHistoryManager.php');

	abstract class BoxScoreUtils {

		/**
		 * @param array $calculateReference
		 *
		 * @return array|null
		 */
		private static function getFieldNames ($calculateReference) {
			if (empty ($calculateReference)) {
				return null;
			}
			$fieldNames = array ();
			$totalReference = count ($calculateReference);
			for ($k = 0; $k < $totalReference; $k++) {
				if(empty ($calculateReference [$k]) || !is_array ($calculateReference [$k])) {
					continue;
				}
				foreach ($calculateReference [$k] as $reference){
					if (!in_array($reference ['fieldname'], $fieldNames)) {
						$fieldNames [] = $reference ['fieldname'];
					}
				}
			}
			return $fieldNames;
		}
		
		/**
		 * @param array $calculateReference
		 * @param string $fieldNames
		 *
		 * @return integer|mixed
		 */
		private static function getCalculatedValue ($calculateReference, $fieldNames) {
			if (empty ($calculateReference)) {
				return 0;
			}
			$claculatedValue = 0;
			$totalReference = count ($calculateReference);
			for ($k = 0; $k < $totalReference; $k++) {
				if(empty ($calculateReference [$k]) || !is_array ($calculateReference [$k])) {
					continue;
				}
				foreach ($calculateReference [$k] as $reference){
					if ($reference['fieldname'] = $fieldNames) {
						$claculatedValue = $reference['calculated'];
						break;
					}
				}
			}
			return $claculatedValue;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param BoxScoreData $dataScore
		 *
		 * @return null
		 */
		private static function fetchBoxScoreObjetives ($adb, $dataScore) {
			if (! $dataScore instanceof BoxScoreData) {
				return null;
			}
			$result = $adb->pquery ('SELECT *  FROM vtiger_box_score_objective bso WHERE box_score_dataid=? AND (NOW() BETWEEN date_from AND date_end)', array ($dataScore->getId ()));
			if ($adb->num_rows ($result) > 0) {
				$objetives = array();
				while ($row = $adb->fetchByAssoc($result, -1, false)) {
					$objetives [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($objetives)) ? $objetives : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param BoxScoreData $dataScoreWeeks
		 * @param string $value
		 */
		private static function saveBoxScoreDataWeekly ($adb, $dataScore, $value) {
			$dataObjetives = self::fetchBoxScoreObjetives ($adb, $dataScore);
			if (empty ($dataObjetives)) {
				return null;
			}
			
			$bsm = BoxScoreManager::getInstance ($adb);
			foreach ($dataObjetives as $objetive) {
				$bsm->saveBoxScoreDataWeekly ($dataScore, $value, $objetive);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param array $calculateReference
		 *
		 * @return null
		 * @throws Exception
		 */
		public static function saveBoxScore ($adb, $moduleName, $calculateReference) {
			$fieldNames = self::getFieldNames ($calculateReference);
			if (empty($moduleName) || empty($fieldNames)) {
				return null;
			}
			
			$boxScores = BoxScoreManager::getInstance($adb)->fetchBoxScoreBySourceModule ($moduleName);
			foreach ($boxScores as $boxScore) {
				foreach ($boxScore->getDataScores () as $dataScore) {
					if (in_array ($dataScore->getCalculatedName (), $fieldNames)) {
						$scoreValue = self::getCalculatedValue ($calculateReference, $dataScore->getCalculatedName ());
						self::saveBoxScoreDataWeekly ($adb, $dataScore, $scoreValue);
					}
				}
			}
		}

	}

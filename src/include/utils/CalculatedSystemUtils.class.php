<?php
	require_once ('include/platzilla/Managers/CalculationElementManager.php');
	require_once ('include/platzilla/Managers/CalculationSystemManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('include/utils/BoxScoreUtils.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	

	/**
	 * Class CalculatedSystemUtils
	 *
	 * Clase abstracta que contiene las utilerias que brindan soporte a los calculos del sistema
	 */
	abstract class CalculatedSystemUtils {

		/**
		 * Actualiza los calculos del campo por la referencia
		 *
		 * @param PearDatabase $adb
		 * @param string $platform
		 * @param $user
		 * @param string $module
		 * @param array $calculateReference
		 * @param boolean $gridStatus
		 *
		 * @throws Exception
		 */
		private static function updateCalculatedFieldByReference ($adb, $platform, $user, $module, $calculateReference, $gridStatus) {
			if (empty ($calculateReference)) {
				return;
			}
			$totalReference = count ($calculateReference);
			for ($k = 0; $k < $totalReference; $k++) {
				if(empty ($calculateReference [$k]) || !is_array ($calculateReference [$k])) {
					continue;
				}
				foreach ($calculateReference [$k] as $reference){
					if ($module == $reference ['module']) {
						continue;
					}
					self::updateCalculatedField ($adb, $platform,$user, $reference ['module'], $reference ['record'], $gridStatus);
				}
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return IndicatorsPanel[]|null
		 * @throws Exception
		 */
		public static function fetchCalculatedBoxScoreData ($adb) {
			$result = $adb->query(
				'SELECT
					bsd.box_score_dataid,
		       		bsd.boxscoreid,
       		       	bsd.calculated_system,
		       		bsd.type
				FROM
					vtiger_box_score_data bsd
				WHERE
					calculated_system != ""
				ORDER BY
					box_score_dataid ASC'
			);
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$monthSearch = date ('m');
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$boxScores [] = array (
						'box_score_dataid' => $row ['box_score_dataid'],
						'boxscoreid'       => $row ['boxscoreid'],
						'calculated_system' => $row ['calculated_system'],
						'type'              => $row ['type']
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($boxScores)) ? $boxScores : null;
		}
		
		/**
		 * Actualiza los calculos en los campos donde este definido calculos
		 *
		 * @param PearDatabase $adb
		 * @param string $platform
		 * @param $user
		 * @param string $module
		 * @param integer $record
		 *
		 * @throws Exception
		 */
		public static function updateCalculatedField ($adb, $platform, $user, $module, $record, $gridStatus) {
			$calculateReference = array ();
			$vquery ='SELECT f.paradicional FROM vtiger_field f
				INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name= "'. $module.'" WHERE
				f.paradicional IS NOT NULL AND f.paradicional != ""';
			$result   = $adb->pquery ($vquery);

			if (($result) && ($adb->num_rows ($result) > 0)) {
				$cfu = new CalculatedFieldsUtils ($adb, $platform);
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$calculateReference [] = $cfu->getCalculateSystemById ($row ['paradicional'], $record, 'update', $user->id, $gridStatus);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			BoxScoreUtils::saveBoxScore ($adb, $module, $calculateReference);
			self::updateCalculatedFieldByReference ($adb, $platform, $user, $module, $calculateReference, $gridStatus);
		}

	}

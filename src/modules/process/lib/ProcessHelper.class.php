<?php
	require_once ('modules/process/Objects/ProcessStepInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/ProcessCasesUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	abstract class ProcessHelper {
		
		const STEPS_CTRL_BANDS = array ('center' => 0.75, 'risk' => 1.1);
		
		/**
		 * @param array $exeAccordingQuality
		 * @param array $controlBandsProcess
		 *
		 * @return void
		 */
		private static function setStepsCtrlBands (&$exeAccordingQuality, $controlBandsProcess) {
			$processedSteps = array();
			foreach ($controlBandsProcess as $controlBand) {
				if (in_array ($controlBand ['step_id'], $processedSteps)) {
					continue;
				}
				$processedSteps[] = $controlBand ['step_id'];
				foreach ($exeAccordingQuality as &$accordingQuality) {
					if (
						($accordingQuality['hours_worked'] <= $controlBand ['up_center_band']) &&
						($accordingQuality['hours_worked'] >= $controlBand ['low_center_band'])
					) {
						$accordingQuality['quality_time'] = 'IN_TIME';
					} else if (
						($accordingQuality['hours_worked'] <= $controlBand ['up_risk_band']) &&
						($accordingQuality['hours_worked'] >= $controlBand ['low_risk_band'])
					) {
						$accordingQuality['quality_time'] = 'AT_RISK';
					} else {
						$accordingQuality['quality_time'] = 'OUT_TIME';
					}
				}
			
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $processId
		 * @param array $periodDates
		 * @param array $periodTimes
		 * @param array $relatedUsers
		 *
		 * @throws Exception
		 * @return array|null
		 */
		public static function fetchExeAccordingQuality ($adb, $processId, $periodDates, $relatedUsers) {
			if (empty ($processId) || empty ($periodDates)) {
				return null;
			}
			$controlBandsProcess = self::fetchStepsCtrlBandsByProcess ($adb, $processId);
			if (empty ($controlBandsProcess)) {
				return null;
			}
			
			$whereUser    = '';
			if (!empty ($relatedUsers)) {
				$usersList = $adb->sql_expr_datalist ($relatedUsers);
				$whereUser = "(crm.smownerid IN{$usersList} OR crm.smcreatorid IN{$usersList}) AND ";
			}
			$startDate = $periodDates ['startdate'];
			$dueDate   = $periodDates ['enddate'];
			$result    = $adb->pquery (
				"SELECT
       				process_casesid,
       				pc.case_number,
       				process_step,
       				ps.step_name,
       				ps.related_module,
       				start_date,
       				due_date,
       				step_exec_time,
       				IFNULL(quality_valuation, 'Bueno') AS quality_valuation
				FROM vtiger_process_cases pc
				INNER JOIN vtiger_crmentity crm ON crm.crmid = pc.process_casesid AND crm.deleted = 0
				INNER JOIN vtiger_process_at_steps ps ON ps.step_id = pc.process_step AND ps.processtfid=?
				WHERE
				      {$whereUser}
				      pc.case_number IN (
				    SELECT case_number
				    FROM vtiger_process_cases pcs
				    GROUP BY case_number
				    HAVING COUNT(*) = (SELECT COUNT(*) FROM vtiger_process_at_steps WHERE processtfid=?) OR finish_process = 1
				)  AND pc.process=? AND (
				        (pc.start_date BETWEEN ? AND ?) OR
				        (pc.due_date BETWEEN ? AND ?)
				    
				    )
				ORDER BY ps.sequence, case_number ASC",
				array ($processId, $processId, $processId, $startDate, $dueDate, $startDate, $dueDate)
			);
			
			if ($adb->num_rows ($result) > 0) {
				$qualityExec = ProcessStepInterface::QUALITY_EXECUTION;
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row['hours_worked']  = ProcessCasesUtils::fetchHoursWorkedByPeriod (
						$adb,
						$row ['start_date'],
						$row ['due_date'],
						floatval ($row ['step_exec_time'])
					);
					$qValuation                = array_search($row ['quality_valuation'], $qualityExec);
					$row ['quality_valuation'] = ($qValuation !== false) ? $qValuation : 'GOOD';
					$exeAccordingQuality []    = $row;
				}
				self::setStepsCtrlBands ($exeAccordingQuality, $controlBandsProcess);
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($exeAccordingQuality)) ? $exeAccordingQuality  : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @throws Exception
		 * @return array|null
		 */
		public static function fetchProcess ($adb) {
			$results   = $adb->query (
				'SELECT
       					p.*
       				FROM vtiger_process p
					INNER JOIN vtiger_crmentity crm ON crm.crmid = p.processid AND crm.deleted = 0
					WHERE 1
					ORDER BY processid DESC'
			);
			if ($adb->num_rows ($results) > 0) {
				while ($row = $adb->fetchByAssoc ($results)) {
					$process[] = $row;
				}
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($process)) ? $process : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @throws Exception
		 * @return array|null
		 */
		public static function fetchProcessTypes ($adb) {
			$results   = $adb->query ('SELECT * FROM vtiger_process_type WHERE 1');
			if ($adb->num_rows ($results) > 0) {
				while ($row = $adb->fetchByAssoc ($results)) {
					$processTypes[] = $row;
				}
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($processTypes)) ? $processTypes : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $processId
		 *
		 * @throws Exception
		 * @return array|null
		 */
		public static function fetchStepsCtrlBandsByProcess ($adb, $processId) {
			if (empty ($processId)) {
				return null;
			}
			$centerBand = self::STEPS_CTRL_BANDS['center'];
			$riskBand   = self::STEPS_CTRL_BANDS['risk'];
			$result = $adb->pquery (
			'SELECT
				    pas.step_name,
				    pas.step_id,
				    ps.estimated_tim,
				    ps.error_rat,
				    ROUND((ps.estimated_tim + (ps.estimated_tim * (ps.error_rat * ? ))), 2) AS up_center_band,
				    ROUND((ps.estimated_tim - (ps.estimated_tim * (ps.error_rat * ? ))), 2) AS low_center_band,
				    ROUND((ps.estimated_tim + (ps.estimated_tim * (ps.error_rat * ? ))), 2) AS up_risk_band,
				    ROUND((ps.estimated_tim - (ps.estimated_tim * (ps.error_rat * ? ))), 2) AS low_risk_band,
				    (SELECT COUNT(*) FROM vtiger_process_at_steps WHERE processtfid=? ) AS total_steps
				FROM
				    vtiger_process_at_steps pas
				INNER JOIN vtiger_process_steps ps ON ps.process_stepsid = pas.step_id
				WHERE
				    pas.processtfid=?
				ORDER BY pas.sequence ASC',
				array ($centerBand, $centerBand, $riskBand, $riskBand, $processId, $processId)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$stepsCtrlBands [] = $row;
				}
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($stepsCtrlBands)) ? $stepsCtrlBands  : null;
		}
		
	}

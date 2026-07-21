<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ActivityReportManager.php');
	require_once ('include/platzilla/Data/TaskActivity.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/Pagination.php');
	require_once ('include/utils/CustomDateTime.class.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');
	require_once ('modules/Settings/lib/HowToHelper.class.php');
	require_once ('modules/daily_report/lib/DailyReportUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/operating_modes/Objects/DirectionModeInterface.php');
	require_once ('modules/report_rails/lib/SummaryReportHelper.class.php');
	
	class DirectionModeHelper implements DirectionModeInterface {
		
		const DAY_OF_WEEK_EN = array ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'saturday', 'Sunday');
		const DAY_OF_WEEK_ES = array ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');
		
		/**
				 * @return PearDatabase
				 */
		private static function adbManager () {
			return AdbManager::getInstance ()->getMasterAdb ();
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $reportId
		 *
		 * @return RailesAgreements[]|null
		 * @throws SummaryReportException
		 */
		private static function fetchAgreements ($adb, $reportId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_rails_agreements WHERE masterreportid = ? AND agreements_status = ?',
				array ($reportId, 'ACTIVE')
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$agreements [] =  RailesAgreements::getInstance()
						->setAgreement ($row['agreement'])
						->setAgreementId ($row['agreementid'])
						->setAgreementStatus ($row['agreements_status'])
						->setDescription ($row['description'])
						->setExecution ($row['execution'])
						->setRelatedAgreement (self::getRelatedAgreement ($adb, $row['tab_name'], $row['execution']))
						->setReportId ($row['masterreportid'])
						->setSequence ($row['sequence'])
						->setTabName ($row['tab_name'])
						->setUsersInvolved (self::fetchUserFromAgreement ($adb, $row['agreementid']));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($agreements)) ? $agreements : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $where
		 *
		 * @return MasterWeeklyReport|null
		 * @throws PlatformException
		 */
		private static function fetchMasterReport ($adb, $where, $isInstance) {
			if (empty ($where)) {
				$where = 1;
			}
			$masterAdb = ($isInstance) ? self::adbManager () : $adb;
			$result    = $adb->query ("SELECT * FROM vtiger_master_summary_report WHERE {$where}");
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$masterReport = MasterWeeklyReport::getInstance ()
					->setAgent (UsersHelper::getAgent ($masterAdb, $row['agentid']))
					->setAgentId ($row['agentid'])
					->setCodeInstance ($row['instance_code'])
					->setDescription ($row['description'])
					->setDateStart ($row['date_start'])
					->setDescription ($row['master_report_status'])
					->setDueDate ($row['due_date'])
					->setId ($row['masterreportid'])
					->setMailInstance ($row['instance_mail'])
					->setStatus ($row['master_status']);
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($masterReport)) ? $masterReport : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $reportId
		 *
		 * @return RailesPerformance[]|null
		 * @throws SummaryReportException
		 */
		private static function fetchPerformance ($adb, $reportId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_rails_performance WHERE masterreportid = ? AND performance_status = ?',
				array ($reportId, 'ACTIVE')
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$performances [] = RailesPerformance::getInstance ()
						->setDescription ($row['description'])
						->setIconPath ($row['iconpath'])
						->setIndexColor ($row['index_color'])
						->setPerformanceId ($row['performanceid'])
						->setPerformanceStatus ($row['performance_status'])
						->setPerformanceName ($row['name'])
						->setReportId ($row['masterreportid']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($performances)) ? $performances : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $agreementId
		 *
		 * @return array|null
		 */
		private static function fetchUserFromAgreement ($adb, $agreementId) {
			if (empty ($agreementId)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT
					ra.*,
					CONCAT(u.first_name, " ", u.last_name) AS username
				FROM
					vtiger_rails_agreements2users ra
				INNER JOIN vtiger_users u ON u.id = ra.userid
				WHERE
					ra.agreementid=?',
				array($agreementId)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$usersInvolved [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($usersInvolved)) ? $usersInvolved : null;
		}
		
		private static function getEntityAgreement ($adb, $tabName) {
			if (empty($tabName)) {
				return null;
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_entityname WHERE modulename =?', array ($tabName));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($row)) ? $row : null;
		}
		
		private static function getRelatedAgreement ($adb, $tabName, $crmId) {
			if (empty ($tabName) || empty ($crmId)) {
				return null;
			}
	
			$entityData = self::getEntityAgreement ($adb, $tabName);
			if (empty($entityData)) {
				return null;
			}
			$result = $adb->query (
				"SELECT
						{$entityData['fieldname']} AS entity_field
					FROM
						{$entityData['tablename']} e
					INNER JOIN vtiger_crmentity crm ON crm.crmid = e.{$entityData['entityidfield']}
					WHERE
						crm.deleted = 0 AND crm.crmid = {$crmId}"
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$relatedAgreement = $row ['entity_field'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($relatedAgreement)) ? $relatedAgreement : null;
		}
		
		private static function getFirstDayWeek ($adb) {
			$result = $adb->query ('SELECT start_day_week FROM vtiger_organizationdetails WHERE 1 LIMIT 1');
			if ($adb->num_rows ($result) > 0) {
				$firstDayWeek =  strtolower ($adb->query_result ($result, 0, 'start_day_week'));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($firstDayWeek)) ? $firstDayWeek : 'monday';
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param User $user
		 * @param string $periodTime
		 * @param boolean $isInstance
		 *
		 * @return false|string
		 * @throws SmartyException
		 */
		public static function getWeeklyContext ($adb, $user, $periodTime, $isInstance, $instanceCode) {
			global $current_language, $currentModule;
			
			$mod_strings                    = return_module_language($current_language, 'report_rails');
			list ($fromDate, $toDate)       = explode ('@', $periodTime);
			list ($targetInstance, $correo) = explode (';', $instanceCode);
			
			$dummy              = explode ('_', $adb->dbName);
			$week               = date ('W', strtotime ($fromDate));
			$weeklyReportId     = base64_encode ("{$targetInstance}-{$week}-{$fromDate}");
			$fromNextDay 		= date ('Y-m-d', strtotime ("{$fromDate} +7 day"));
			$week               = date ('W', strtotime ($fromNextDay));
			$nextWeeklyReportId = base64_encode ("{$dummy[2]}-{$week}-{$fromNextDay}");
			
			/** @var  fetchWeeklyReport */
			$weeklyReport = SummaryReportHelper::fetchWeeklyReport ($adb, $weeklyReportId, $isInstance);
			if (isset ($weeklyReport['weeklyReports']) && count($weeklyReport['weeklyReports']) > 0) {
				$dataGraphics = array (
					array ('Objetos', '% Horas de ejecución', 'Horas planificadas', 'Tareas avanzadas'),
				);
				$moduleObjects = array_column ($weeklyReport['weeklyReports'], 'related_module', 'related_id');
				foreach ($moduleObjects as $relatedId => $relatedModule) {
					$totalWeeklyReport = count ($weeklyReport['weeklyReports']);
					$indexWork         = null;
					$indexOther         = null;
					$totalAvance	   = 0;
					$isCalculated	   = false;
					for ($i = 0; $i < $totalWeeklyReport; $i++) {
						if (
							$relatedId != $weeklyReport['weeklyReports'][$i]['related_id'] ||
							$relatedModule != $weeklyReport['weeklyReports'][$i]['related_module']
						) {
							continue;
						}
						if (in_array ($relatedModule, array_keys (SummaryReportHelper::$SUMMARY_TAB))) {
							if (empty ($indexWork)) {
								$indexWork = $i;
								$weeklyReport['weeklyReports'][$i]['execution_hours'] = floatval ($weeklyReport['weeklyReports'][$i]['execution_hours']);
								$weeklyReport['weeklyReports'][$i]['planned_hours']   = floatval ($weeklyReport['weeklyReports'][$i]['planned_hours']);
							} else {
								$weeklyReport['weeklyReports'][$indexWork]['execution_hours'] += floatval ($weeklyReport['weeklyReports'][$i]['execution_hours']);
								$weeklyReport['weeklyReports'][$indexWork]['planned_hours']   += floatval ($weeklyReport['weeklyReports'][$i]['planned_hours']);
								unset ($weeklyReport['weeklyReports'][$i]);
							}
						} else {
							if ($totalAvance == 0) {
								$isCalculated = true;
								$indexOther    = $i;
								$totalAvance = ((floatval ($weeklyReport['weeklyReports'][$i]['advance_task']) / 100) * floatval ($weeklyReport['weeklyReports'][$indexOther]['execution_hours']));
								$weeklyReport['weeklyReports'][$indexOther]['execution_hours'] += floatval ($weeklyReport['weeklyReports'][$i]['execution_hours']);
								$weeklyReport['weeklyReports'][$indexOther]['planned_hours'] += floatval ($weeklyReport['weeklyReports'][$i]['planned_hours']);
							} else {
								$weeklyReport['weeklyReports'][$indexOther]['execution_hours'] += floatval ($weeklyReport['weeklyReports'][$i]['execution_hours']);
								$weeklyReport['weeklyReports'][$indexOther]['planned_hours']   += floatval ($weeklyReport['weeklyReports'][$i]['planned_hours']);
								unset ($weeklyReport['weeklyReports'][$i]);
							}
						}
					}
					if ($isCalculated) {
						$weeklyReport['weeklyReports'][$indexOther]['advance_task'] = ($totalAvance / floatval ($weeklyReport['weeklyReports'][$indexOther]['execution_hours'])) * 100;
						$weeklyReport['weeklyReports'][$indexOther]['advance_task'] =  number_format ($weeklyReport['weeklyReports'][$indexOther]['advance_task'], 2);
					}
					$weeklyReport['weeklyReports'] = array_values ($weeklyReport['weeklyReports']);
				}
				foreach ($weeklyReport['weeklyReports'] as $report) {
					if (isset($report['subject']) && !empty($report['subject'])) {
						$objects [] = $report['subject'];
					} else {
						$objects [] = $report['task_subject'];
					}
					if ($report['planned_hours'] != 0) {
						$timePorc = (floatval ($report['execution_hours']) / floatval ($report['planned_hours'])) * 100;
					} else {
						$timePorc = floatval ($report['execution_hours']);
					}
					$objects [] = $timePorc;
					$objects []  = 100;
					$objects [] = floatval ($report ['advance_task']);
					$dataGraphics [] = $objects;
					unset($objects);
				}
			}
			
			/** @var  fetchUpcomingReport */
			$upcomingActivity = SummaryReportHelper::fetchUpcomingReport ($adb, $nextWeeklyReportId, $isInstance);
			$upcomingTab      = SummaryReportHelper::fetchUpcomingTab ($adb, $nextWeeklyReportId, $isInstance);
			$moduleObjects    = array_column ($upcomingActivity['weeklyReports'], 'related_module', 'related_id');
			foreach ($moduleObjects as $relatedId => $relatedModule) {
				$totalWeeklyReport = count ($upcomingActivity['weeklyReports']);
				$indexWork         = null;
				$indexOther         = null;
				$totalAvance	   = 0;
				$isCalculated	   = false;
				for ($i = 0; $i < $totalWeeklyReport; $i++) {
					if (
						$relatedId != $upcomingActivity['weeklyReports'][$i]['related_id'] ||
						$relatedModule != $upcomingActivity['weeklyReports'][$i]['related_module']
					) {
						continue;
					}
					if (in_array ($relatedModule, array_keys (SummaryReportHelper::$SUMMARY_TAB))) {
						if (empty ($indexWork)) {
							$indexWork = $i;
							$upcomingActivity['weeklyReports'][$i]['execution_hours'] = floatval ($upcomingActivity['weeklyReports'][$i]['execution_hours']);
							$upcomingActivity['weeklyReports'][$i]['planned_hours']   = floatval ($upcomingActivity['weeklyReports'][$i]['planned_hours']);
						} else {
							$upcomingActivity['weeklyReports'][$indexWork]['execution_hours'] += floatval ($upcomingActivity['weeklyReports'][$i]['execution_hours']);
							$upcomingActivity['weeklyReports'][$indexWork]['planned_hours']   += floatval ($upcomingActivity['weeklyReports'][$i]['planned_hours']);
							unset ($upcomingActivity['weeklyReports'][$i]);
						}
					} else {
						if ($totalAvance == 0) {
							$isCalculated = true;
							$indexOther   = $i;
							$totalAvance  = ((floatval ($upcomingActivity['weeklyReports'][$i]['advance_task']) / 100) * floatval ($upcomingActivity['weeklyReports'][$indexOther]['execution_hours']));
							$upcomingActivity['weeklyReports'][$indexOther]['execution_hours'] += floatval ($upcomingActivity['weeklyReports'][$i]['execution_hours']);
							$upcomingActivity['weeklyReports'][$indexOther]['planned_hours'] += floatval ($upcomingActivity['weeklyReports'][$i]['planned_hours']);
						} else {
							$upcomingActivity['weeklyReports'][$indexOther]['execution_hours'] += floatval ($upcomingActivity['weeklyReports'][$i]['execution_hours']);
							$upcomingActivity['weeklyReports'][$indexOther]['planned_hours']   += floatval ($upcomingActivity['weeklyReports'][$i]['planned_hours']);
							unset ($upcomingActivity['weeklyReports'][$i]);
						}
					}
				}
				if ($isCalculated) {
					$upcomingActivity['weeklyReports'][$indexOther]['advance_task'] = ($totalAvance / floatval ($upcomingActivity['weeklyReports'][$indexOther]['execution_hours'])) * 100;
					$upcomingActivity['weeklyReports'][$indexOther]['advance_task'] =  number_format ($upcomingActivity['weeklyReports'][$indexOther]['advance_task'], 2);
				}
				$upcomingActivity['weeklyReports'] = array_values ($upcomingActivity['weeklyReports']);
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ACTION_PLAN', (!empty ($weeklyReport)) ? $weeklyReport['actionPlan'] : null);
			$smarty->assign ('DATA_TABLE', (isset($dataGraphics)) ? json_encode ($dataGraphics) : null);
			$smarty->assign ('ADB', $adb);
			$smarty->assign ('AFFAIRS', $upcomingTab ['affairs']);
			$smarty->assign ('BUSINESS_INITIATIVES', (!empty ($weeklyReport)) ? $weeklyReport['businessInitiatives'] : null);
			$smarty->assign ('CORRECTIVE_ACTIONS',$upcomingTab ['corrective_actions']);
			$smarty->assign ('INSTANCE_CODE', (!$isInstance && $dummy[2] != 'madre') ? $dummy[2] : null);
			$smarty->assign ('IS_INSTANCE', $isInstance);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('PERIOD_TIME', $periodTime);
			$smarty->assign ('PROJECT', (!empty ($weeklyReport)) ? $weeklyReport['project'] : null);
			$smarty->assign ('SELECTED_TAB', 'PLANNING_COMPLIANCE');
			$smarty->assign ('UPCOMING_ACTIVITIES',(!empty ($upcomingActivity)) ? $upcomingActivity['weeklyReports'] : null);
			$smarty->assign ('REPORT_ID', $weeklyReportId);
			$smarty->assign ('TARGET_INSTANCE', $targetInstance);
			$smarty->assign ('UPCOMING_TAB', $upcomingTab);
			$smarty->assign ('WEEKLY_REPORTS', (!empty ($weeklyReport)) ? $weeklyReport['weeklyReports'] : null);
			return $smarty->fetch ('Home/WeeklyReport/weeklyPerformanceReport.tpl');
		}
		
		public static function getWeeklyObjetives () {
			return 'getWeeklyObjetives';
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param User $current_user
		 * @param string $periodTime
		 * @param boolean $isInstance
		 *
		 * @return false|string|void
		 */
		public static function getWeeklyStatusReport ($adb, $current_user, $periodTime, $isInstance, $instance) {
			$periodDays = explode ('@', $periodTime);
			$agent      = (empty ($_REQUEST ['report_agent'])) ? PlatzillaUtils::purify ($_REQUEST, 'report_agent', null) : $_REQUEST ['report_agent'];
			try {
				if (count ($periodDays) != 2) {
					$message = 'El periodo de tiempo no es valido';
				}
				if ($isInstance) {
					$where = "date_start = '{$periodDays[0]}' AND due_date = '{$periodDays[1]}'";
				} else {
					if (empty ($instance)) {
						$message = (empty ($message)) ? 'La instancia no es valida' : $message;
					} elseif (empty ($agent)) {
						$message = (empty ($message)) ? '¡Agente no identificado!' : $message;
					}
					list ($instanceCode, $email) = explode (';', $instance);
					$where = "instance_code = '{$instanceCode}' AND agentid = '{$agent}' AND date_start = '{$periodDays[0]}' AND due_date = '{$periodDays[1]}'";
				}
				$masterReport = self::fetchMasterReport ($adb, $where, $isInstance);
				if (empty ($masterReport)) {
					$periodInfo = "del {$periodDays[0]} al {$periodDays[1]}";
					$message =(empty ($message)) ?  'No se encontró el reporte semanal: '. $periodInfo : $message;
				} else {
					if (!$isInstance) {
						$performances = SummaryReportHelper::fetchPerformance ($masterReport->getId ());
						$agreements   = SummaryReportHelper::fetchAgreements ($masterReport->getId ());
					} else {
						$performances = self::fetchPerformance ($adb, $masterReport->getId ());
						$agreements   = self::fetchAgreements ($adb, $masterReport->getId ());
					}
				}
				
				$smarty = new vtigerCRM_Smarty ();
				$smarty->assign ('ADB', $adb);
				$smarty->assign ('AGREEMENTS', isset ($agreements) ? $agreements : null);
				$smarty->assign ('IS_INSTANCE', $isInstance);
				$smarty->assign ('MASTER_REPORT', $masterReport);
				$smarty->assign ('MESSAGE', isset($message) ? $message : null);
				$smarty->assign ('PERFORMANCES', isset ($performances) ? $performances [0] : null);
				$smarty->assign('DATA_MULTIPLIER', $masterReport);
				return $smarty->fetch ('Home/WeeklyReport/WeeklyStatusReport.tpl');
				
			} catch (Exception $e) {
				$_SESSION ['flashmessage'] = array (
					'iserror' => false,
					'message' => $e->getMessage (),
				);
				
				header ("Location: index.php?module=Home&action=index");
			}
		
		
		}
		
	}

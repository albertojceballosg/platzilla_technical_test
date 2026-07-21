<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/platzilla/Managers/SummaryReportManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/platzilla/Objects/RailesAgreements.php');
	require_once ('include/platzilla/Objects/RailesPerformance.php');
	require_once ('include/platzilla/Objects/SummaryReport.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');
	
	abstract class SummaryReportHelper {
		
		private static $PLATFORM_ONLY_MODULE_NAMES = array (
			'almacenes',
			'answers',
			'daily_report',
			'diagnostic_report',
			'diagnostic_report_builder',
			'etapas_proyecto',
			'grid_view',
			'how_use',
			'materials',
			'model_action_plan',
			'operating_modes',
			'predefined_initiatives',
			'preloaded_tasks',
			'process',
			'process_steps',
			'questionnaire',
			'reportes',
			'systemalerts',
			'todotasks',
			'views_diagrams',
			'pasos_procesos',
			'management_situations',
			'work_views',
			'management_mechanisms',
		);
		
		public static $SUMMARY_TAB   = array (
			'orden_de_trabajo' => array (
				'column' => 'overall_progress_perc',
				'table'  => 'vtiger_orden_de_trabajo',
				'field'  => 'orden_de_trabajoid',
			),
			'proyectos' => array (
				'column' => 'porcentaje_de_avance_genera',
				'table'  => 'vtiger_proyectos',
				'field'  => 'proyectosid',
			),
		);
		
		/**
		 * @return PearDatabase
		 */
		private static function adbManager () {
			return AdbManager::getInstance ()->getMasterAdb ();
		}
		
		private static function fetchBSObjectives ($adb, $bsdId, $fromDate, $toDate) {
			$result = $adb->pquery (
				"SELECT
					objective,
				   date_from,
				   date_end
				FROM
					vtiger_box_score_objective
				WHERE
					box_score_dataid = ? AND date_from >= DATE(?) AND date_end <= DATE(?)",
				array ($bsdId, $fromDate, $toDate)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$bsObjectives [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($bsObjectives)) ? $bsObjectives : null;
		}
		
		private static function fetchBSDataWeekly ($adb, $bsdId, $fromDate, $toDate) {
			$result = $adb->pquery (
				"SELECT
					value,
					date
				FROM
					vtiger_box_score_data_weekly
				WHERE
					box_score_dataid = ? AND date >= DATE(?) AND date <= DATE(?)",
				array ($bsdId, $fromDate, $toDate)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$bsDataWeekly [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($bsDataWeekly)) ? $bsDataWeekly : null;
		}
		/**
		 * @param integer $reportId
		 * @param integer $agreementId
		 *
		 * @return array|null
		 * @throws PlatformException
		 */
		private static function fetchUserFromAgreement ($reportId, $agreementId) {
			$summaryReportManager = self::getMasterReport ($reportId);
			if (empty($summaryReportManager)) {
				return null;
			}
			$masterAdb = self::adbManager ();
			$targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($summaryReportManager->getCodeInstance ());
			$result = $masterAdb->query (
				"SELECT
				    ra.*,
				     CONCAT(u.first_name, ' ', u.last_name) AS username
				FROM
				    {$masterAdb->dbName}.vtiger_rails_agreements2users ra
				INNER JOIN {$targetInstance->dbName}.vtiger_users u ON u.id = ra.userid
				WHERE
				 ra.agreementid = {$agreementId}"
			);
			if ($masterAdb->num_rows ($result) > 0) {
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
					$usersInvolved [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($usersInvolved)) ? $usersInvolved : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $tabName
		 *
		 * @return mixed|null
		 */
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
		
		/**
		 * @param integer $agreementId
		 *
		 * @return integer|mixed
		 * @throws Exception
		 */
		private static function getNextAgreement ($reportId, $adb) {
			//$masterAdb = self::adbManager ();
			$result = $adb->pquery ('SELECT MAX(sequence) + 1 AS seq FROM `vtiger_rails_agreements` WHERE masterreportid = ?', array ($reportId));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$nextAgreement = $row ['seq'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($nextAgreement)) ? $nextAgreement : 1;
		}
		
		/**
		 * @param integer $reportId
		 * @param string $tabName
		 * @param integer $crmId
		 *
		 * @return mixed|null
		 * @throws PlatformException
		 */
		private static function getRelatedAgreement ($reportId, $tabName, $crmId) {
			$summaryReportManager = self::getMasterReport ($reportId);
			if (empty($summaryReportManager)) {
				return null;
			}
			$targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($summaryReportManager->getCodeInstance ());
			$entityData = self::getEntityAgreement ($targetInstance, $tabName);
			if (empty($entityData)) {
				return null;
			}
			$result = $targetInstance->query (
				"SELECT
						{$entityData['fieldname']} AS entity_field
					FROM
						{$entityData['tablename']} e
					INNER JOIN vtiger_crmentity crm ON crm.crmid = e.{$entityData['entityidfield']}
					WHERE
						crm.deleted = 0 AND crm.crmid = {$crmId}"
			);
			if ($targetInstance->num_rows ($result) > 0) {
				$row = $targetInstance->fetchByAssoc ($result, -1, false);
				$relatedAgreement = $row ['entity_field'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($relatedAgreement)) ? $relatedAgreement : null;
		}
		
		private static function getTaskSubject ($adb, &$row) {
			$result = $adb->pquery ('SELECT * FROM vtiger_entityname WHERE modulename = ?', array ($row['related_module']));
			if ($adb->num_rows ($result) > 0) {
				$entityData = $adb->fetchByAssoc ($result, -1, false);
				DatabaseUtils::closeResult ($result);
				$result = null;
				$result = $adb->pquery ('SELECT '. $entityData['fieldname'] . ' AS subject FROM ' . $entityData['tablename'] . ' WHERE ' . $entityData['entityidfield'] . ' = ?', array ($row['related_id']));
				if ($adb->num_rows ($result) > 0) {
					$entity = $adb->fetchByAssoc ($result, -1, false);
					$row['subject'] = $entity['subject'];
					DatabaseUtils::closeResult ($result);
					$result = null;
				}
			}
			
		}
		
		/**
		 * @param array $row
		 * @param string $reportType
		 *
		 * @return string
		 */
		private static function getWeeklyReportId ($row, $reportType) {
			if ($reportType == 'ACTUAL') {
				$week = date ('W', strtotime ($row['date_start']));
				return base64_encode ("{$row['instance_code']}-{$week}-{$row['date_start']}");
			} else {
				$nextDay = date ('Y-m-d', strtotime ($row['date_start'] . '+7 day'));
				$week    = date ('W', strtotime ($nextDay));
				return base64_encode ("{$row['instance_code']}-{$week}-{$nextDay}");
			}
		}
		
		/**
		 * @param RailesAgreements$agreement
		 * @param PearDatabase $adb
		 *
		 * @return void
		 */
		private static function saveAgreementToUsers ($agreement, $adb) {
			if (is_array ($agreement->getUsersInvolved ())) {
				if (empty ($adb)) {
					$adb = self::adbManager ();
				}
				
				foreach ($agreement->getUsersInvolved () as $user) {
					if (is_array ($user)) {
						$userId = $user['userid'];
					} else {
						$userId = $user;
					}
					$adb->pquery ('INSERT INTO `vtiger_rails_agreements2users` (agreementid, userid) VALUES (?, ?)', array ($agreement->getAgreementId (), $userId));
				}
			}
		}
		
		/**
		 * @param array $businessInitiatives
		 * @param array $actionPlan
		 * @param array $project
		 * @param array $row
		 *
		 * @return void
		 */
		private static function summaryWeeklyReport (&$businessInitiatives, &$actionPlan, &$project, &$row) {
			if (
				isset ($row ['business_initiative']) &&
				!empty($row ['business_initiative']) &&
				$row ['business_initiative'] != 'NOT_APPLIED'
			) {
				$element = json_decode ($row ['business_initiative'],true);
				if (!in_array ($element['crmId'], array_keys ($businessInitiatives))) {
					$element ['planned_hours']               = floatval ($row['planned_hours']);
					$element ['execution_hours']             = floatval ($row['execution_hours']);
					$element ['action_plan'] = ($row ['action_plan'] != 'NOT_APPLIED') ? json_decode ($row ['action_plan'],true) : null;
					$businessInitiatives [$element['crmId']] = $element;
					
				} else {
					$businessInitiatives [$element['crmId']] ['planned_hours']   += floatval ($row['planned_hours']);
					$businessInitiatives [$element['crmId']] ['execution_hours'] += floatval ($row['execution_hours']);
				}
				$row ['business_initiative'] = $element;
			}
			if (
				isset ($row ['action_plan']) &&
				!empty($row ['action_plan']) &&
				$row ['action_plan'] != 'NOT_APPLIED'
			) {
				$element = json_decode ($row ['action_plan'], true);
				if (!in_array ($element['crmId'], array_keys ($actionPlan))) {
					$element ['planned_hours']      = floatval ($row['planned_hours']);
					$element ['execution_hours']    = floatval ($row['execution_hours']);
					$actionPlan [$element['crmId']] = $element;
				} else {
					$actionPlan [$element['crmId']] ['planned_hours']   += floatval ($row['planned_hours']);
					$actionPlan [$element['crmId']] ['execution_hours'] += floatval ($row['execution_hours']);
				}
				$row ['action_plan'] = $element;
			}
			if (($row ['related_module'] == 'orden_de_trabajo') && !empty($row ['project'])) {
				$element   = json_decode ($row ['project'], true);
				if (!in_array ($element['crmId'], array_keys ($project))) {
					$element ['planned_hours']       = floatval ($row['planned_hours']);
					$element ['execution_hours']     = floatval ($row['execution_hours']);
					$element ['business_initiative'] = ($row ['business_initiative'] != 'NOT_APPLIED') ? $row ['business_initiative'] : null;
					$element ['action_plan']         = ($row ['action_plan'] != 'NOT_APPLIED') ? $row ['action_plan'] : null;
					$project [$element['crmId']]     = $element;
				} else {
					$project [$element['crmId']] ['planned_hours']   += floatval ($row['planned_hours']);
					$project [$element['crmId']] ['execution_hours'] += floatval ($row['execution_hours']);
					if (empty ($project [$element['crmId']]['business_initiative']) && !empty ($row ['business_initiative'])) {
						$project [$element['crmId']]['business_initiative'] = $row ['business_initiative'];
					}
					if (empty ($project [$element['crmId']]['action_plan']) && !empty ($row ['action_plan'])) {
						$project [$element['crmId']]['action_plan'] = $row ['action_plan'];
					}
				}
				$row ['project'] = json_decode ($row ['project'], true);
			}
		}
		
		/**
		 * @param string $reportId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function availableWeeklyReport ($reportId, $reportType) {
			if (empty ($reportId)) {
				return null;
			}
			$dummy     = explode ('-', base64_decode ($reportId), 3);
			$masterAdb = self::adbManager ();
			$result    = $masterAdb->pquery ('SELECT * FROM vtiger_weekly_report2instance WHERE report_type=? AND code_instance= ?',
				array ($reportType, $dummy[0])
			);
			if ($masterAdb->num_rows ($result) > 0) {
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
					$dummy              = explode ('-', base64_decode ($row['weekly_report_code']), 3);
					$dateFrom           = $dummy[2];
					$toDate             = date ('Y-m-d', strtotime ($dateFrom . '+6 day'));
					$row ['dates']      = "{$dateFrom} - {$toDate}";
					$availableReport [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($availableReport)) ? $availableReport : null;
		}
		
		public static function createMasterReport ($agentId, $week, $instance, $reportTitle, $reportId) {
			$masterAdb = self::adbManager ();
			$thisAgent = UsersHelper::getAgent ($masterAdb, $agentId);
			$masterReport = SummaryReport::getInstance ()
				->setAgent ($thisAgent)
				->setDateStart ($week[0])
				->setDueDate ($week[1])
				->setInstanceCode ($instance[0])
				->setMasterReportId ($reportId)
				->setPerformanceId (null)
				->setPerformanceText (null)
				->setRailesAgreements (null)
				->setRailesPerformance (null)
				->setReportId (null)
				->setMasterStatus (null)
				->setReportText (null)
				->setReportTitle ($reportTitle)
				->setUserId ($thisAgent->getUserId ());
		}
		
		/**
		 * @param integer $reportId
		 * @return void
		 * @throws Exception
		 */
		public static function deleteMasterReport ($reportId) {
			if (empty ($reportId)) {
				return;
			}
			$performances = self::fetchPerformance ($reportId);
			foreach ($performances as $performance) {
				self::deleteReportByPart ('performance', $performance->getPerformanceId ());
			}
			$agreements = self::fetchAgreements ($reportId);
			foreach ($agreements as $agreement) {
				self::deleteReportByPart ('agreements', $agreement->getAgreementId ());
			}
			$masterAdb = self::adbManager ();
			$masterAdb->pquery ('DELETE FROM `vtiger_master_summary_report` WHERE masterreportid = ?', array ($reportId));
		}
		
		/**
		 * @param string $part
		 * @param integer $id
		 *
		 * @return void
		 */
		public static function deleteReportByPart ($part, $id) {
			$masterAdb = self::adbManager ();
			if ($part == 'performance') {
				$masterAdb->pquery ('DELETE FROM vtiger_rails_performance WHERE performanceid = ?', array ($id));
			} else if ($part == 'agreements') {
				$masterAdb->pquery ('DELETE FROM vtiger_rails_agreements WHERE agreementid = ?', array ($id));
				$masterAdb->pquery ('DELETE FROM vtiger_rails_agreements2users WHERE agreementid = ?', array($id));
			}
		}
		
		/**
		 * @param integer $reportId
		 * @param PearDatabase|null $adb
		 *
		 * @return RailesAgreements[]|null
		 * @throws Exception
		 */
		public static function fetchAgreements ($reportId, $status = null) {
			$where = (!empty($status)) ? " AND agreements_status = '{$status}'" : '';
			$masterAdb = self::adbManager();
			$result = $masterAdb->pquery ('SELECT * FROM vtiger_rails_agreements WHERE masterreportid = ? ' . $where, array ($reportId));
			if ($masterAdb->num_rows ($result) > 0) {
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
					$agreements [] =  RailesAgreements::getInstance()
						->setAgreement ($row['agreement'])
						->setAgreementName ($row['agreement_name'])
						->setAgreementId ($row['agreementid'])
						->setAgreementStatus ($row['agreements_status'])
						->setDescription ($row['description'])
						->setExecution ($row['execution'])
						->setRelatedAgreement (self::getRelatedAgreement ($row['masterreportid'], $row['tab_name'], $row['execution']))
						->setReportId ($row['masterreportid'])
						->setSequence ($row['sequence'])
						->setTabName ($row['tab_name'])
						->setUsersInvolved (self::fetchUserFromAgreement ($row['masterreportid'], $row['agreementid']));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($agreements)) ? $agreements : null;
		}
		
		/**
		 * @param string $codeInstance
		 *
		 * @return Module[]|null
		 */
		public static function fetchAvailableModules ($codeInstance) {
			$excludedModuleNames = self::$PLATFORM_ONLY_MODULE_NAMES;
			$headersOnly         = true;
			$forAnInstance       = true;
			$targetInstance      = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
			$modules = ModuleManager::getInstance ($targetInstance)->fetchModulesByType(Module::TYPE_USER, $headersOnly, $forAnInstance);
			if (!empty($modules)) {
				$availableModules = array ();
				foreach ($modules as $module) {
					if (
						$module->getPresence () !== 0 ||
						in_array ($module->getName (), $excludedModuleNames)
					) {
									continue;
					}
					$availableModules [] = $module;
				}
			}
			return (isset($availableModules)) ? $availableModules : null;
		}
		
		/**
		 * @param string $codeInstance
		 * @param string $platform
		 *
		 * @return User[]|null
		 */
		public static function fetchAvailableUsers ($codeInstance, $platform) {
			$targetInstance      = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
			return UserManager::getInstance ($targetInstance, $platform)->fetchUsers ();
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $indicatorName
		 * @param string $fromDate
		 * @param string $dueDate
		 *
		 * @return array|mixed|null
		 */
		public static function fetchBoxScoreData ($adb, $indicatorName, $fromDate, $dueDate) {
			$result = $adb->pquery (
						'SELECT
						    objective AS scale,
						    description,
       						box_score,
						    box_score_dataid
						FROM
						    vtiger_box_score_data
						WHERE
						    name=?
						GROUP BY name',
						array ($indicatorName)
					);
					if ($adb->num_rows ($result) > 0) {
					$boxScores = array();
						while ($row = $adb->fetchByAssoc($result, -1, false)) {
							$row['objectives']  = self::fetchBSObjectives ($adb, $row['box_score_dataid'], $fromDate, $dueDate);
							$row['data_weekly'] = self::fetchBSDataWeekly ($adb, $row['box_score_dataid'], $fromDate, $dueDate);
							$boxScores = $row;
							
						}
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
					return (isset ($boxScores)) ? $boxScores : null;
				}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $appCode
		 *
		 * @return array|null
		 */
		public static function fetchBoxScoreRails ($adb, $appCode) {
			if (empty ($appCode)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT DISTINCT
					bsd.name,
                	IFNULL(bsd.objective,"MONTH") AS objective
				FROM
			    	vtiger_box_score_data bsd
				INNER JOIN vtiger_boxscore bs ON bs.boxscoreid = bsd.boxscoreid
				WHERE
			   	 bsd.on_railes=? AND
				 bs.app_code=?',
				array ('SHOW', $appCode)
			);
			if ($adb->num_rows ($result) > 0) {
				$boxScores = array();
				while ($row = $adb->fetchByAssoc($result, -1, false)) {
					$row['objective']           = (!empty($row['objective'])) ? $row['objective'] : 'MONTH';
					$boxScores ['objectives'][] = $row;
					$boxScores ['indicators'][] = $row['name'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($boxScores)) ? $boxScores : null;
		}
		
		/**
		 * @return SummaryReport[]|null
		 * @throws Exception
		 */
		public static function fetchMasterReport () {
			$masterAdb = self::adbManager ();
			$result = $masterAdb->query ('SELECT * FROM vtiger_master_summary_report WHERE 1  ORDER BY date_start DESC');
			if ($masterAdb->num_rows ($result) > 0) {
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
					$masterReport[] = MasterWeeklyReport::getInstance ()
						->setAgent (UsersHelper::getAgent ($masterAdb, $row['agentid']))
						->setAgentId ($row['agentid'])
						->setCodeInstance ($row['instance_code'])
						->setDateStart ($row['date_start'])
						->setDescription ($row['description'])
						->setDueDate ($row['due_date'])
						->setId ($row['masterreportid'])
						->setMailInstance ($row['instance_mail'])
						->setStatus ($row['master_status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($masterReport)) ? $masterReport : null;
		}
		
		/**
		 *
		 * @return RailesPerformance[]|null
		 * @throws Exception
		 */
		public static function fetchPerformance ($reportId, $status = null) {
			$where = (!empty($status)) ? " AND performance_status = '{$status}' " : '';
			$masterAdb = self::adbManager ();
			$result = $masterAdb->pquery ('SELECT * FROM vtiger_rails_performance WHERE masterreportid = ?' . $where,
				array ($reportId)
			);
			if ($masterAdb->num_rows ($result) > 0) {
				while ($row = $masterAdb->fetchByAssoc ($result, -1, false)) {
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
		 * @param string $reportId
		 * @param boolean $isInstance
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchUpcomingReport ($adb, $reportId, $isInstance) {
			if ($isInstance) {
				$whereStatus = ' AND status = "PUBLISHED" ';
				$targetAdb   = $adb;
			} else {
				$whereStatus = '';
				$dummy       = explode ('-', base64_decode ($reportId),3);
				$targetAdb   = AdbManager::getInstance ()->getTargetInstanceAdb ($dummy[0]);
			}
			$result = $targetAdb->pquery ('SELECT * FROM vtiger_upcoming_activities WHERE weekly_report_code = ?' . $whereStatus . ' ORDER BY related_module ASC',
				array ($reportId)
			);
			if ($targetAdb->num_rows ($result) > 0) {
				$actionPlan = array ();
				$businessInitiatives = array ();
				$project = array ();
				while ($row = $targetAdb->fetchByAssoc ($result, -1, false)) {
					if (empty ($row['related_module'])) {
						continue;
					}
					self::summaryWeeklyReport ($businessInitiatives, $actionPlan, $project, $row);
					$weeklyReports []  = $row;
				}
				uasort (
						$actionPlan, function ($a, $b) {
							return strcmp ($a['title'], $b['title']);
						}
					);
				uasort (
						$businessInitiatives, function ($a, $b) {
							return strcmp ($a['title'], $b['title']);
						}
					);
				uasort (
						$project, function ($a, $b) {
							return strcmp ($a['title'], $b['title']);
						}
					);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($weeklyReports)) {
				return array (
					'weeklyReports'       => $weeklyReports,
					'businessInitiatives' => (isset ($businessInitiatives)) ? $businessInitiatives : null,
					'actionPlan'          => (isset ($actionPlan)) ? $actionPlan : null,
					'project'             => (isset ($project)) ? $project : null
				);
			}
			return  null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $reportId
		 * @param boolean $isInstance
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchUpcomingTab ($adb, $reportId, $isInstance) {
			if ($isInstance) {
				$whereStatus = ' AND uat.status = "PUBLISHED" ';
				$targetAdb   = $adb;
			} else {
				$whereStatus = '';
				$dummy       = explode ('-', base64_decode ($reportId),3);
				$targetAdb   = AdbManager::getInstance ()->getTargetInstanceAdb ($dummy[0]);
			}
			$result = $targetAdb->pquery (
					"SELECT
					    uat.*,
					    CONCAT(u.first_name, ' ', u.last_name) AS username
					FROM
					    vtiger_upcoming_activities_tabs uat
					INNER JOIN vtiger_users u ON u.id = uat.user_id
					WHERE
					    weekly_report_code = ? {$whereStatus}
					ORDER BY
					    tab_name ASC",
				array ($reportId)
			);
			if ($targetAdb->num_rows ($result) > 0) {
				$correctiveActions = array ();
				$affairs           = array ();
				while ($row = $targetAdb->fetchByAssoc ($result, -1, false)) {
					$row['link'] = array (
						'title' => $row['title'],
						'crmId' => $row['crm_id'],
					);
					if ($row['tab_name'] == 'corrective_actions') {
						$correctiveActions [] = $row;
					} else if ($row['tab_name'] == 'affairs') {
						$affairs [] = $row;
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return array (
				'corrective_actions' => (isset ($correctiveActions) && count ($correctiveActions)) ? $correctiveActions : null,
				'affairs'            => (isset ($affairs) && count ($affairs)) ? $affairs : null
			);
		}
		
		/**
		 * @param $instanceCode
		 * @param $fromDate
		 * @param $toDate
		 *
		 * @return void|null
		 */
		public static function fetchWeeklyPerformance ($instanceCode, $fromDate, $toDate) {
			if (empty($instanceCode) || empty($fromDate) || empty($toDate)) {
				return null;
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $reportId
		 * @param boolean $isInstance
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchWeeklyReport ($adb, $reportId, $isInstance) {
			if ($isInstance) {
				$whereStatus = ' AND status = "PUBLISHED" ';
				$targetAdb   = $adb;
			} else {
				$whereStatus = '';
				$dummy       = explode ('-', base64_decode ($reportId),3);
				$targetAdb   = AdbManager::getInstance ()->getTargetInstanceAdb ($dummy[0]);
			}
			$result = $targetAdb->pquery ('SELECT * FROM vtiger_weekly_report_master WHERE weekly_report_code = ? ' . $whereStatus . 'ORDER BY related_module ASC',
				array ($reportId)
			);
			if ($targetAdb->num_rows ($result) > 0) {
				$actionPlan = array();
				$businessInitiatives = array();
				$project = array();
				while ($row = $targetAdb->fetchByAssoc ($result, -1, false)) {
					if (empty ($row['related_module'])) {
						continue;
					}
					self::summaryWeeklyReport ($businessInitiatives, $actionPlan, $project, $row);
					if (!empty($row['related_module']) && !empty($row['related_id'])) {
						self::getTaskSubject ($adb, $row);
					}
					$weeklyReports [] = $row;
				}
				
				uasort (
						$actionPlan, function ($a, $b) {
						return strcmp ($a['title'], $b['title']);
					}
				);
				uasort (
						$businessInitiatives, function ($a, $b) {
						return strcmp ($a['title'], $b['title']);
					}
				);
				uasort (
						$project, function ($a, $b) {
						return strcmp ($a['title'], $b['title']);
					}
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($weeklyReports)) {
				return array (
					'weeklyReports'       => $weeklyReports,
					'businessInitiatives' => (isset ($businessInitiatives)) ? $businessInitiatives : null,
					'actionPlan'          => (isset ($actionPlan)) ? $actionPlan : null,
					'project'             => (isset ($project)) ? $project : null
				);
			}
			return  null;
		}
		
		/**
		 * @param integer $reportId
		 *
		 * @return RailesAgreements|null
		 * @throws Exception
		 */
		public static function getAgreement ($agreementId) {
			$masterAdb = self::adbManager ();
			$result = $masterAdb->pquery ('SELECT * FROM vtiger_rails_agreements WHERE agreementid = ?', array ($agreementId));
			if ($masterAdb->num_rows ($result) > 0) {
				$row = $masterAdb->fetchByAssoc ($result, -1, false);
				$agreement =  RailesAgreements::getInstance()
					->setAgreement ($row['agreement'])
					->setAgreementName ($row['agreement_name'])
					->setAgreementId ($row['agreementid'])
					->setAgreementStatus ($row['agreements_status'])
					->setDescription ($row['description'])
					->setExecution ($row['execution'])
					->setRelatedAgreement (self::getRelatedAgreement ($row['masterreportid'], $row['tab_name'], $row['execution']))
					->setReportId ($row['masterreportid'])
					->setSequence ($row['sequence'])
					->setTabName ($row['tab_name'])
					->setUsersInvolved (self::fetchUserFromAgreement ($row['masterreportid'], $row['agreementid']));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($agreement)) ? $agreement : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $indicatorName
		 * @param string $fornDate
		 *
		 * @return string
		 * @throws Exception
		 */
		public static function getIndicatorDateScale ($adb, $indicatorName, $fornDate) {
			$result = $adb->pquery (
				'SELECT
			        IFNULL(objective,"MONTH") AS objective,
       				 DATE_SUB(?, INTERVAL 3 WEEK) AS weeks,
       				 DATE_SUB(?, INTERVAL 3 MONTH) AS months
				FROM
					vtiger_box_score_data
				WHERE
					name=?
				LIMIT 1',
				array ($fornDate, $fornDate, $indicatorName)
			);
			if ($adb->num_rows ($result) > 0) {
				$row     = $adb->fetchByAssoc ($result, -1, false);
				$theDate = (!empty ($row['objective']) && $row['objective'] == 'WEEK') ? $row ['weeks'] : $row ['months'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($theDate)) ? $theDate : $fornDate;
		}
		
		/**
		 * @param integer $reportId
		 *
		 * @return MasterWeeklyReport|null
		 * @throws PlatformException
		 */
		public static function getMasterReport ($reportId) {
			if (empty($reportId)) {
				return null;
			}
			$masterAdb = self::adbManager ();
			$result = $masterAdb->pquery ('SELECT * FROM vtiger_master_summary_report WHERE masterreportid = ?', array ($reportId));
			if ($masterAdb->num_rows ($result) > 0) {
				$row = $masterAdb->fetchByAssoc ($result, -1, false);
				$masterReport = MasterWeeklyReport::getInstance ()
					->setAgent (UsersHelper::getAgent ($masterAdb, $row['agentid']))
					->setAgentId ($row['agentid'])
					->setCodeInstance ($row['instance_code'])
					->setDateStart ($row['date_start'])
					->setDescription ($row['description'])
					->setDueDate ($row['due_date'])
					->setId ($row['masterreportid'])
					->setMailInstance ($row['instance_mail'])
					->setStatus ($row['master_status'])
					->setReportOfStatus ($row['master_report_status'])
					->setUpcomingReportId (self::getWeeklyReportId ($row, 'UPCOMING'))
					->setWeeklyReportId (self::getWeeklyReportId ($row, 'ACTUAL'));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($masterReport)) ? $masterReport : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $workId
		 *
		 * @return integer
		 */
		public static function getOverallProgress ($adb, $workId, $moduleName) {
			if (empty ($workId) || empty ($moduleName) || !in_array ($moduleName, array_keys (self::$SUMMARY_TAB))) {
				return 0;
			}
			$tableName = self::$SUMMARY_TAB [$moduleName]['table'];
			$column    = self::$SUMMARY_TAB [$moduleName]['column'];
			$field     = self::$SUMMARY_TAB [$moduleName]['field'];
			$result = $adb->pquery ("SELECT {$column} AS progress FROM {$tableName} WHERE {$field} = ?", array ($workId));
			if ($adb->num_rows ($result) > 0) {
				$row             = $adb->fetchByAssoc ($result, -1, false);
				$overallProgress = $row ['progress'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($overallProgress)) ? $overallProgress : 0;
		}
		
		/**
		 * @param integer $summaryReportId
		 *
		 * @return RailesPerformance|null
		 * @throws Exception
		 */
		public static function getPerformance ($performanceId) {
			$masterAdb = self::adbManager ();
			return SummaryReportManager::getInstance ($masterAdb)->getPerformanceById ($performanceId);
		}
		
		/**
		 * @param string $reportId
		 *
		 * @return void
		 * @throws Exception
		 */
		public static function publishReport ($reportId, $reportTypes) {
			if (empty ($reportId)) {
				throw new Exception ('ERROR_INVALID_REPORT_ID');
			}
			foreach ($reportTypes as $reportType) {
				if (empty($reportType)) {
					continue;
				}
				$masterAdb = self::adbManager ();
				$result = $masterAdb->pquery ('SELECT * FROM  vtiger_weekly_report2instance WHERE weekly_report_code = ? AND report_type=?', array ($reportId, $reportType));
				if ($masterAdb->num_rows ($result) > 0) {
					$row = $masterAdb->fetchByAssoc ($result, -1, false);
					if ($row ['code_instance'] != 'madre') {
						$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($row['code_instance']);
					} else {
						$targetAdb = $masterAdb;
					}
					if ($reportType == 'ACTUAL') {
						$targetAdb->pquery ('UPDATE vtiger_weekly_report_master SET status = ? WHERE weekly_report_code= ?', array('PUBLISHED', $reportId));
					} else if ($reportType == 'UPCOMING') {
						$targetAdb->pquery ('UPDATE vtiger_upcoming_activities SET status = ? WHERE weekly_report_code= ?', array('PUBLISHED', $reportId));
					} else if ($reportType == 'UPCOMING_TAB') {
						$targetAdb->pquery ('UPDATE vtiger_upcoming_activities_tabs SET status = ? WHERE weekly_report_code= ?', array('PUBLISHED', $reportId));
					}
					$masterAdb->pquery ('UPDATE vtiger_weekly_report2instance SET status = ? WHERE weekly_report_code= ? AND report_type=? ', array('PUBLISHED', $reportId, $reportType));
				} else {
					throw new Exception ('ERROR_REPORT_NOT_FOUND');
				}
			}
		}
		
		/**
		 * @param RailesAgreements $agreement
		 * @params PearDatabse|null $adb
		 *
		 * @return void
		 * @throws SummaryReportException
		 */
		public static function saveAgreement ($agreement, $adb = null) {
			if (! $agreement instanceof RailesAgreements) {
				throw new Exception ('ERROR_INVALID_AGREEMENTS_OBJECT');
			}
			$agreement->validate ();
			if (empty ($adb)) {
				$adb = self::adbManager ();
			}
			
			if (empty($agreement->getAgreementId ())) {
				$sequnce = self::getNextAgreement ($agreement->getReportId (), $adb);
				$adb->pquery (
					'INSERT INTO vtiger_rails_agreements (agreement_name, masterreportid, agreement, description, execution, tab_name, sequence, agreements_status) VALUES (?,?,?,?,?,?,?,?)',
					array($agreement->getAgreementName (), $agreement->getReportId (), $agreement->getAgreement (), $agreement->getDescription (), $agreement->getExecution (), $agreement->getTabName (), $sequnce, $agreement->getAgreementStatus ())
				);
				$agreement->setAgreementId ($adb->getLastInsertID ());
			} else {
				$adb->pquery (
					'UPDATE vtiger_rails_agreements SET masterreportid=?, agreement=?, description=?, execution=?, tab_name=?, sequence=?, agreements_status=? WHERE agreementid = ?',
					array($agreement->getReportId (), $agreement->getAgreement (), $agreement->getDescription (), $agreement->getExecution (), $agreement->getTabName (), $agreement->getSequence (), $agreement->getAgreementStatus (), $agreement->getAgreementId ())
				);
				$adb->pquery ('DELETE FROM vtiger_rails_agreements2users WHERE agreementid = ?', array($agreement->getAgreementId ()));
			}
			self::saveAgreementToUsers ($agreement, $adb);
		}
		
		/**
		 * @param RailesPerformance $performance
		 *
		 * @return void
		 * @throws SummaryReportException
		 */
		public static function savePerformance ($performance, $adb = null) {
			if (! $performance instanceof RailesPerformance) {
				throw new Exception ('ERROR_INVALID_PERFORMANCE_OBJECT');
			}
			$performance->validate ();
			if (empty ($adb)) {
				$adb = self::adbManager ();
			}
			if (empty($performance->getPerformanceId ())) {
				$adb->pquery (
					'INSERT INTO vtiger_rails_performance (masterreportid, index_color, iconpath, name, description, performance_status) VALUES (?,?,?,?,?,?)',
					array($performance->getReportId (), $performance->getIndexColor (), $performance->getIconPath (), $performance->getPerformanceName (), $performance->getDescription (), $performance->getPerformanceStatus ())
				);
				$performance->setPerformanceId ($adb->getLastInsertID ());
			} else {
				$adb->pquery (
					'UPDATE vtiger_rails_performance SET masterreportid=?, index_color= ?, iconpath = ?, name = ?, description = ?, performance_status =? WHERE performanceid = ?',
					array($performance->getReportId (), $performance->getIndexColor (), $performance->getIconPath (), $performance->getPerformanceName (), $performance->getDescription (), $performance->getPerformanceStatus (), $performance->getPerformanceId ())
				);
			}
			
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $dateStar
		 * @param string $dueDate
		 *
		 * @return void
		 * @throws Exception
		 */
		private static function validateMasterReport ($adb, $dateStar, $dueDate) {
			$result = $adb->pquery ('SELECT * FROM vtiger_master_summary_report WHERE date_start=? AND due_date=?',
				array ($dateStar, $dueDate)
			);
			$hasReport = ($adb->num_rows ($result) > 0);
			DatabaseUtils::closeResult ($result);
			$result = null;
			if ($hasReport) {
				throw new Exception ('¡El Reporte ya ha sido compartido!');
			}
		}
		
		/**
		 * @param integer $reprtId
		 *
		 * @return string
		 * @throws PlatformException
		 */
		public static function checkStatusReport ($reprtId) {
			$masterReport = self::getMasterReport ($reprtId);
			$message      = '';
			if (empty ($masterReport)) {
				throw new Exception ('Reporte no encontrado!');
			}
			$targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($masterReport->getCodeInstance ());
			self::validateMasterReport ($targetInstance, $masterReport->getDateStart (), $masterReport->getDueDate ());
			
			$availableAgreements = self::fetchAgreements ($reprtId, 'ACTIVE');
			if (empty($availableAgreements)) {
				$message .= '- No se han encontrado registro de acuerdos para compartir ';
			}
			
			$availablePerformances = self::fetchPerformance ($reprtId, 'ACTIVE');
			if (empty($availablePerformances)) {
				$message .= '- No se han encontrado indice de desempeños para compartir ';
			}
			return $message;
		}
		
		/**
		 * @param integer $reprtId
		 *
		 * @return void
		 * @throws PlatformException
		 * @throws Exception
		 * @throws SummaryReportException
		 */
		public static function shareReport ($reprtId) {
			$masterReport = self::getMasterReport ($reprtId);
			if (empty ($masterReport)) {
				throw new Exception ('Reporte no encontrado!');
			}
			$targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($masterReport->getCodeInstance ());
			self::validateMasterReport ($targetInstance, $masterReport->getDateStart (), $masterReport->getDueDate ());
			
			$targetInstance->pquery (
				'INSERT INTO vtiger_master_summary_report (description, agentid, date_start, due_date, instance_code, instance_mail, master_status, master_report_status) VALUES (?,?,?,?,?,?,?, ?)',
				array($masterReport->getDescription (), $masterReport->getAgentId (),$masterReport->getDateStart (), $masterReport->getDueDate (), $masterReport->getCodeInstance (), $masterReport->getMailInstance (), 'ACTIVE', $masterReport->getReportOfStatus ())
			);
			$masterReport->setId ($targetInstance->getLastInsertID ());
			$availableAgreements    = self::fetchAgreements ($reprtId);
			if (!empty($availableAgreements)) {
				foreach ($availableAgreements as $agreement) {
					$agreement->setReportId ($masterReport->getId ());
					$agreement->setAgreementId (null);
					self::saveAgreement ($agreement, $targetInstance);
				}
			}
			$availablePerformances = self::fetchPerformance ($reprtId);
			if (!empty($availablePerformances)) {
				foreach ($availablePerformances as $performance) {
					$performance->setReportId ($masterReport->getId ());
					$performance->setPerformanceId (null);
					self::savePerformance ($performance, $targetInstance);
				}
			}
		}
		
		/**
		 * @param MasterWeeklyReport $masterReport
		 *
		 * @return boolean
		 */
		public static function hasBeenPublished ($masterReport) {
			if (! $masterReport instanceof MasterWeeklyReport) {
				return false;
			}
			try {
				$targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($masterReport->getCodeInstance ());
				self::validateMasterReport ($targetInstance, $masterReport->getDateStart (), $masterReport->getDueDate ());
				return false;
			} catch (Exception $e) {
				return true;
			}
		}
		
		/**
		 * @param integer $reportId
		 * @param string $statusText
		 * @return void
		 */
		public static function updateMasterReportStatus ($reportId, $statusText) {
			$masterAdb = self::adbManager ();
			$masterAdb->pquery (
				'UPDATE vtiger_master_summary_report SET master_report_status=? WHERE masterreportid = ?',
				array ($statusText, $reportId)
			);
		}
		
		/**
		 * @param RailesAgreements $agreement
		 * @return void
		 * @throws PlatformException
		 */
		public static function updateAgreement ($agreement) {
			if (!$agreement instanceof RailesAgreements || empty($agreement->getReportId ())) {
				return;
			}
			$masterReport   = self::getMasterReport ($agreement->getReportId ());
			$targetInstance = AdbManager::getInstance ()->getTargetInstanceAdb ($masterReport->getCodeInstance ());
			$targetInstance->pquery (
				'UPDATE vtiger_rails_agreements SET agreement=?, description=?, execution=?, tab_name=?, sequence=?, agreements_status=?
					WHERE agreement_name = ?',
				array ($agreement->getAgreement (), $agreement->getDescription (), $agreement->getExecution (), $agreement->getTabName (), $agreement->getSequence (), $agreement->getSequence (), $agreement->getAgreementName ())
			);
			
			$result = $targetInstance->pquery ('SELECT agreementid FROM vtiger_rails_agreements WHERE agreement_name=?', array($agreement->getAgreementName ()));
			if ($targetInstance->num_rows ($result) > 0) {
				$row = $targetInstance->fetchByAssoc ($result, -1, false);
				$agreement->setAgreementId ($row ['agreementid']);
				$targetInstance->pquery ('DELETE FROM vtiger_rails_agreements2users WHERE agreementid = ?', array($agreement->getAgreementId ()));
				self::saveAgreementToUsers ($agreement, $targetInstance);
			} else {
				$agreement->setAgreementId (null);
				self::saveAgreement ($agreement, $targetInstance);
			}
		}
		
		/**
		 * @param string $part
		 * @param string $status
		 * @param integer $id
		 *
		 * @return void
		 */
		public static function updateStatusReportByPart ($part, $status, $id) {
			$masterAdb = self::adbManager ();
			if ($part == 'master') {
				$masterAdb->pquery ('UPDATE vtiger_master_summary_report SET master_status = ? WHERE masterreportid = ?', array ($status, $id));
			} else if ($part == 'performance') {
				$masterAdb->pquery ('UPDATE vtiger_rails_performance SET performance_status = ? WHERE performanceid = ?', array ($status, $id));
			} else if ($part == 'agreement') {
				$masterAdb->pquery ('UPDATE vtiger_rails_agreements SET agreements_status = ? WHERE agreementid = ?', array ($status, $id));
			}
		}
		
	}
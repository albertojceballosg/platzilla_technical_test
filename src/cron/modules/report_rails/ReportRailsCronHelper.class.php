<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('modules/daily_report/lib/DailyReportMaster.class.php');
	require_once ('include/platzilla/Data/ActivityReportManager.php');
	require_once ('include/platzilla/Data/BoxScoreManager.php');
	class ReportRailsCronHelper {
		
		protected $actionPlanField          = 'plan_initiatives';
		protected $actionPlanModule         = 'action_plan';
		protected $actionTablePrefix        = 'vtiger_action_plan';
		protected $businessInitiativeField  = 'resource_initiative';
		protected $businessInitiativeModule = 'business_initiatives';
		protected $businessTablePrefix      = 'vtiger_resource_initiative';
		protected $objectModules            = array ('proyectos', 'orden_de_trabajo', 'campaign_marketing');
		protected $objectSql                = '';
		protected $summaryTab               = array (
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
		
		public function __construct($adb) {
			$this->objectSql = $this->getObjectSql ($adb);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $fromDate
		 * @param string $toDate
		 * @param array $groupsIds
		 *
		 * @return ActivityReport[]|null
		 */
		private function fetchActivityReport ($adb, $groupsIds, $fromDate, $toDate) {
			if (is_array ($groupsIds) || count ($groupsIds) > 0) {
				$strId  = join (',', $groupsIds);
				$ids    = explode (',', $strId);
				$gIds   = $adb->sql_expr_datalist($ids);
				$whereReport  = " AND ar.activityreportid NOT IN {$gIds}";
			}
			$objSql = $this->objectSql;
			$result = $adb->pquery (
				"SELECT
       					ar.*, a.activityid, a.subject, a.date_start, a.due_date, a.estimated_time, a.progress,a.related_id,
       					(SELECT setype FROM vtiger_crmentity WHERE crmid=a.related_id) AS related_module
       					{$objSql}
					FROM vtiger_activity_report  ar
				    INNER JOIN vtiger_activity a ON a.activityid = ar.activityid
					INNER JOIN vtiger_crmentity ce ON ar.activityid = ce.crmid AND ce.deleted = 0
					WHERE ar.deleted = 0 AND (DATE(ar.reportdate) BETWEEN DATE(?) AND DATE(?))".$whereReport,
				array ($fromDate, $toDate)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['project'] = 'NOT_APPLIED';
					if (
						isset ($row ['business_initiatives']) &&
						!empty($row ['business_initiatives']) &&
						$row ['business_initiatives'] != 'NOT_APPLIED'
					) {
						$row ['business_initiatives'] = $this->getBusinessInitiativesById ($adb, $row ['business_initiatives']);
					}
					if (
						isset ($row ['action_plan']) &&
						!empty($row ['action_plan']) &&
						$row ['action_plan'] != 'NOT_APPLIED'
					) {
						$row ['action_plan'] = $this->getActionPlantById ($adb, $row ['action_plan']);
					}
					if (!empty ($row ['related_module']) && $row ['related_module'] == 'orden_de_trabajo') {
						$row ['project'] = $this->getProjectFromJob ($adb, $row ['related_id']);
					}
					$activityReport [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityReport)) ? $activityReport : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $allActivity
		 * @param string $fromDate
		 * @param string $toDate
		 *
		 * @return TaskActivity[]|null
		 * @throws Exception
		 */
		private function fetchActivityTask ($adb, $allActivity, $fromDate, $toDate) {
			$whereActivity = '';
			$objSql        = $this->objectSql;
			if (is_array ($allActivity) && count ($allActivity) > 0) {
				$gIds          = $adb->sql_expr_datalist($allActivity);
				$whereActivity = " AND a.activityid NOT IN {$gIds}";
			}
			
			$result = $adb->pquery (
				"SELECT
				   a.activityid, a.subject, a.date_start, a.due_date, a.estimated_time, a.progress, a.related_id,
				   (SELECT setype FROM vtiger_crmentity WHERE crmid=a.related_id) AS related_module
				  {$objSql}
				FROM
				   vtiger_activity a
				   INNER JOIN vtiger_crmentity crm ON  crm.crmid = a.activityid  AND crm.deleted=0
				   WHERE ((DATE(a.date_start) >= DATE(?) AND DATE(a.due_date) <= DATE(?)  AND a.eventstatus != ?) OR (DATE(a.date_start) < DATE(?) AND a.eventstatus != ?))" . $whereActivity,
				array ($fromDate, $toDate, 'Not Held', $fromDate, 'Not Held')
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['project'] = 'NOT_APPLIED';
					if (
						isset ($row ['business_initiatives']) &&
						!empty($row ['business_initiatives']) &&
						$row ['business_initiatives'] != 'NOT_APPLIED'
					) {
						$row ['business_initiatives'] = $this->getBusinessInitiativesById ($adb, $row ['business_initiatives']);
					}
					if (
						isset ($row ['action_plan']) &&
						!empty($row ['action_plan']) &&
						$row ['action_plan'] != 'NOT_APPLIED'
					) {
						$row ['action_plan'] = $this->getActionPlantById ($adb, $row ['action_plan']);
					}
					if (!empty ($row ['related_module']) && $row ['related_module'] == 'orden_de_trabajo') {
						$row ['project'] = $this->getProjectFromJob ($adb, $row ['related_id']);
					}
					$activityTask[] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityTask)) ? $activityTask : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $actionPlanId
		 *
		 * @return array|null
		 */
		private function getActionPlantById ($adb, $actionPlanId) {
			$result = $adb->pquery (
				"SELECT
       					ap.action_plan_name,
       					ap.overall_progress
					FROM vtiger_action_plan ap
					INNER JOIN vtiger_crmentity crm ON crm.crmid = ap.action_planid AND crm.deleted = 0
					WHERE action_planid = ?",
				array ($actionPlanId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$actionPlan = array (
					'crmId'    => $actionPlanId,
					'title'    => $row ['action_plan_name'],
					'progress' => $row ['overall_progress']
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($actionPlan)) ? $actionPlan : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $businessInitiativesId
		 *
		 * @return array|null
		 */
		private function getBusinessInitiativesById ($adb, $businessInitiativesId) {
			$result = $adb->pquery (
				"SELECT
       					bi.initiative_title,
       					bi.progress_initiative
					FROM vtiger_business_initiatives bi
					INNER JOIN vtiger_crmentity crm ON crm.crmid = bi.business_initiativesid AND crm.deleted = 0
					WHERE business_initiativesid = ?",
				array ($businessInitiativesId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$businessInitiatives = array (
					'crmId'    => $businessInitiativesId,
					'title'    => $row ['initiative_title'],
					'progress' => $row ['progress_initiative']
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($businessInitiatives)) ? $businessInitiatives : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $jobId
		 *
		 * @return array|string
		 */
		private function getProjectFromJob ($adb, $jobId) {
			if (empty ($jobId)) {
				return'NOT_APPLIED';
			}
			$result = $adb->pquery (
				"SELECT
       				p.proyectosid,
       				p.nombre,
       				p.porcentaje_de_avance_genera
				FROM vtiger_proyectos p
				INNER JOIN vtiger_project_works pw ON p.proyectosid = pw.crmid
				INNER JOIN vtiger_crmentity crm ON crm.crmid = p.proyectosid AND crm.deleted = 0
				WHERE pw.crmid_job = ?
				LIMIT 1",
				array ($jobId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$project = array (
					'crmId'    => $row ['proyectosid'],
					'title'    => $row ['nombre'],
					'progress' => $row ['porcentaje_de_avance_genera']
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($project)) ? $project : 'NOT_APPLIED';
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return string
		 * @throws Exception
		 */
		private function getObjectSql ($adb) {
			$actionPlanTable         = $this->getTableName ($adb, $this->actionPlanModule, $this->actionPlanField, $this->actionTablePrefix);
			$businessInitiativeTable = $this->getTableName ($adb, $this->businessInitiativeModule, $this->businessInitiativeField, $this->businessTablePrefix);
			$modules                 = $this->objectModules;
			
			if (empty($actionPlanTable) || empty($businessInitiativeTable)) {
				return '';
			}
			return ", CASE
				WHEN ((SELECT setype FROM vtiger_crmentity WHERE crmid=a.related_id) = '{$modules[0]}') OR
				((SELECT setype FROM vtiger_crmentity WHERE crmid=a.related_id) = '{$modules[1]}') OR
				((SELECT setype FROM vtiger_crmentity WHERE crmid=a.related_id) = '{$modules[2]}')
				THEN
				(SELECT recurse_initiativeid FROM {$businessInitiativeTable} WHERE crmid_resource = a.related_id LIMIT 1)
				ELSE 'NOT_APPLIED'
				END AS business_initiatives,
				CASE
				WHEN ((SELECT setype FROM vtiger_crmentity WHERE crmid=a.related_id) = '{$modules[0]}') OR
				((SELECT setype FROM vtiger_crmentity WHERE crmid=a.related_id) = '{$modules[1]}') OR
				((SELECT setype FROM vtiger_crmentity WHERE crmid=a.related_id) = '{$modules[2]}')
				THEN
				(SELECT action_planid FROM vtiger_action_plan ap INNER JOIN {$actionPlanTable} apft ON apft.action_plantfid = ap.action_planid INNER JOIN {$businessInitiativeTable} ri ON ri.recurse_initiativeid = apft.plan_initiativeid WHERE ri.crmid_resource = a.related_id LIMIT 1)
				ELSE 'NOT_APPLIED'
				END AS action_plan";
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param integer $relatedId
		 * @param string $moduleName
		 *
		 * @return integer|mixed
		 */
		private function getOverallProgress ($adb, $relatedId, $moduleName) {
			if (empty ($relatedId) || empty ($moduleName) || !in_array ($moduleName, array_keys ($this->summaryTab))) {
				return 0;
			}
			$tableName = $this->summaryTab [$moduleName]['table'];
			$column    = $this->summaryTab [$moduleName]['column'];
			$field     = $this->summaryTab [$moduleName]['field'];
			$result = $adb->pquery ("SELECT {$column} AS progress FROM {$tableName} WHERE {$field} = ?", array ($relatedId));
			if ($adb->num_rows ($result) > 0) {
				$row             = $adb->fetchByAssoc ($result, -1, false);
				$overallProgress = $row ['progress'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($overallProgress)) ? $overallProgress : 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param $fieldName
		 *
		 * @return string
		 * @throws Exception
		 */
		private function getTableName ($adb, $moduleName, $fieldName, $tablePrefix) {
			$field = FieldManager::getInstance ($adb)->fetchFieldByName ($moduleName, $fieldName, true);
			if (!$field instanceof Field) {
				return null;
			}
			return "{$tablePrefix}_ft{$field->getId ()}";
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $fromDate
		 * @param string $toDate
		 *
		 * @return string|null
		 * @throws Exception
		 */
		public function fetchDailyReport ($adb, $fromDate, $toDate) {
			$objSql = $this->objectSql;
			$result = $adb->pquery (
				"SELECT
				    drm.*, dr.total_hours_reported, a.related_id, a.related_to, a.estimated_time, a.subject,
				    (SELECT setype FROM vtiger_crmentity WHERE crmid=a.related_id) AS related_module
				    {$objSql}
				FROM
				    vtiger_daily_report_master drm
				INNER JOIN vtiger_daily_report dr ON dr.daily_reportid = drm.crmid
				INNER JOIN vtiger_activity a ON a.activityid = drm.activityid
				INNER JOIN vtiger_crmentity ce ON  drm.crmid = ce.crmid
				WHERE
				    DATE(drm.dt_created) BETWEEN DATE(?) AND DATE(?)",
				array ($fromDate, $toDate)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['project'] = 'NOT_APPLIED';
					if (
						isset ($row ['business_initiatives']) &&
						!empty($row ['business_initiatives']) &&
						$row ['business_initiatives'] != 'NOT_APPLIED'
					) {
						$row ['business_initiatives'] = $this->getBusinessInitiativesById ($adb, $row ['business_initiatives']);
					}
					if (
						isset ($row ['action_plan']) &&
						!empty($row ['action_plan']) &&
						$row ['action_plan'] != 'NOT_APPLIED'
					) {
						$row ['action_plan'] = $this->getActionPlantById ($adb, $row ['action_plan']);
					}
					if (!empty ($row ['related_module']) && $row ['related_module'] == 'orden_de_trabajo') {
						$row ['project'] = $this->getProjectFromJob ($adb, $row ['related_id']);
					}
					$dailyReports[]        = $row;
					$allActivityReports [] = $row ['activityreportid'];
					$allActivity []        = $row ['activityid'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($dailyReports)) {
				$dummy            = explode ('_', $adb->dbName);
				$week             = date ('W', strtotime ($fromDate));
				$weeklyReportCode = base64_encode ("{$dummy[2]}-{$week}-{$fromDate}");
				$this->saveWeeklyReportMaster ($adb, $weeklyReportCode, $dailyReports, 'DAILY_REPORT');
				$reportersTask = $this->fetchActivityReport ($adb, $allActivityReports, $fromDate, $toDate);
				$this->saveWeeklyReportMaster ($adb, $weeklyReportCode, $reportersTask, 'REPORT_TASK');
				$activityTask  = $this->fetchActivityTask ($adb, $allActivity, $fromDate, $toDate);
				$this->saveWeeklyReportMaster ($adb, $weeklyReportCode, $activityTask, 'ACTIVITY_TASK');
			}
			return isset ($weeklyReportCode) ? $weeklyReportCode : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $upcomingReportCode
		 * @param string $fromDate
		 * @param string $toDate
		 *
		 * @return string|null
		 */
		public function fetchUpcomingActivities ($adb, $upcomingReportCode,$fromDate, $toDate) {
			$objSql = $this->objectSql;
			$result = $adb->pquery (
				"SELECT
					a.activityid, a.subject, a.date_start, a.due_date, a.estimated_time, a.progress, a.related_id,
					(SELECT setype FROM vtiger_crmentity WHERE crmid=a.related_id) AS related_module
					{$objSql}
					FROM
						vtiger_activity a
					INNER JOIN vtiger_crmentity crm ON  crm.crmid = a.activityid  AND crm.deleted=0
					WHERE ((DATE(a.date_start) >= DATE(?) AND DATE(a.due_date) <= DATE(?)  AND a.eventstatus != ?) OR (DATE(a.date_start) < DATE(?) AND a.eventstatus != ? AND a.progress < 100))",
				array ($fromDate, $toDate, 'Not Held', $fromDate, 'Not Held')
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['project'] = 'NOT_APPLIED';
					if (
						isset ($row ['business_initiatives']) &&
						!empty($row ['business_initiatives']) &&
						$row ['business_initiatives'] != 'NOT_APPLIED'
					) {
						$row ['business_initiatives'] = $this->getBusinessInitiativesById ($adb, $row ['business_initiatives']);
					}
					if (
						isset ($row ['action_plan']) &&
						!empty($row ['action_plan']) &&
						$row ['action_plan'] != 'NOT_APPLIED'
					) {
						$row ['action_plan'] = $this->getActionPlantById ($adb, $row ['action_plan']);
					}
					if (!empty ($row ['related_module']) && $row ['related_module'] == 'orden_de_trabajo') {
						$row ['project'] = $this->getProjectFromJob ($adb, $row ['related_id']);
					}
					$activityTask[] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($activityTask)) {
				$this->saveUpcomingReport ($adb, $upcomingReportCode, $activityTask);
			}
			return (isset ($activityTask)) ? $upcomingReportCode : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $upcomingReportCode
		 * @param string $fromDate
		 * @param string $toDate
		 *
		 * @return string|null
		 */
		public function fetchUpcomingTabActivities ($adb, $upcomingReportCode, $fromDate, $toDate) {
			$result = $adb->pquery (
				'SELECT
					ac.title_ac AS title,
					ac.start_date_ac AS date_start,
					ac.state_ac AS state,
					crm.crmid,
					crm.smownerid AS user_id,
					crm.setype AS tab_name
				FROM
				   vtiger_corrective_actions ac
				INNER JOIN vtiger_crmentity crm ON crm.crmid = ac.corrective_actionsid AND crm.deleted = 0
				WHERE (DATE(ac.start_date_ac) BETWEEN DATE(?) AND DATE(?)) AND ac.state_ac != ?
				UNION
				SELECT
					af.affair_title AS title,
					af.affair_date AS date_start,
					af.affair_status AS state,
					crm.crmid,
					crm.smownerid AS user_id,
					crm.setype AS tab_name
					FROM
						vtiger_affairs af
					INNER JOIN vtiger_crmentity crm ON crm.crmid = af.affairsid AND crm.deleted = 0
					WHERE DATE(af.affair_date) BETWEEN DATE(?) AND DATE(?) AND (af.affair_status= ? OR af.affair_status= ?)',
				array ($fromDate, $toDate,'Terminada', $fromDate, $toDate,  'En trámite', 'Nuevo')
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$upcomingTabActivities[] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($upcomingTabActivities)) {
				$this->saveUpcomingTabReport ($adb, $upcomingReportCode, $upcomingTabActivities);
			}
			return (isset ($upcomingTabActivities)) ? $upcomingReportCode : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $weeklyReportCode
		 *
		 * @return boolean
		 */
		public function hasWeeklyReport ($adb, $weeklyReportCode, $reportType) {
			$result = $adb->pquery ('SELECT weekly_reportid FROM vtiger_weekly_report2instance WHERE weekly_report_code=? AND report_type=?', array ($weeklyReportCode, $reportType));
			return ($adb->num_rows ($result) > 0);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $weeklyReportCode
		 * @param array $data
		 *
		 * @return void
		 */
		public function saveUpcomingTabReport ($adb, $weeklyReportCode, $data) {
			if (!is_array ($data) || empty ($data)) {
				return;
			}
			
			foreach ($data as $row) {
				$adb->pquery (
					'INSERT INTO vtiger_upcoming_activities_tabs (
                        weekly_report_code,
                        tab_name,
                        crmid,
                        title,
                        record_date,
                        record_state,
                        user_id
    				)
    				VALUES (?, ?, ?, ?, ?, ?, ?)',
					array (
						$weeklyReportCode,
						$row ['tab_name'],
						$row ['crmid'],
						$row ['title'],
						$row ['date_start'],
						$row ['state'],
						$row ['user_id']
					)
				);
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 * @param string $weeklyReportCode
		 *
		 * @return void
		 */
		public function saveWeeklyReport ($adb, $instanceCode, $weeklyReportCode, $reportType) {
			$adb->pquery ('INSERT INTO vtiger_weekly_report2instance (weekly_report_code, code_instance, report_type) VALUES (?, ?, ?)', array ($weeklyReportCode, $instanceCode, $reportType));
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $weeklyReportCode
		 * @param array $data
		 *
		 * @return void
		 */
		public function saveUpcomingReport ($adb, $weeklyReportCode, $data) {
			if (!is_array ($data) || empty ($data)) {
				return;
			}
			
			foreach ($data as $row) {
				if (in_array ($row ['related_module'], array_keys ($this->summaryTab))) {
					$row ['progress'] = $this->getOverallProgress ($adb, $row ['related_id'], $row ['related_module']);
				}
				$adb->pquery (
					"INSERT INTO vtiger_upcoming_activities (
				            weekly_report_code,
				            task,
				            task_subject,
				            related_module,
				            related_id,
				            project,
				            business_initiative,
				            action_plan,
				            planned_hours,
				            advance_task
				        )
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
					array (
						$weeklyReportCode,
						$row ['activityid'],
						$row ['subject'],
						$row ['related_module'],
						$row ['related_id'],
						(is_array ($row ['project']) ? json_encode ($row ['project']): $row ['project']),
						(is_array ($row ['business_initiatives']) ? json_encode ($row ['business_initiatives']): $row ['business_initiatives']),
						(is_array ($row ['action_plan']) ? json_encode ($row ['action_plan']): $row ['action_plan']),
						$row ['estimated_time'],
						$row ['progress']
					)
				);
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $weeklyReportCode
		 * @param array $data
		 * @param string $reportType
		 */
		public function saveWeeklyReportMaster ($adb, $weeklyReportCode, $data, $reportType) {
			if (!is_array ($data) || empty ($data)) {
				return;
			}
			
			foreach ($data as $row) {
				if (in_array ($row ['related_module'], array_keys ($this->summaryTab))) {
					$row ['progress'] = $this->getOverallProgress ($adb, $row ['related_id'], $row ['related_module']);
				}
				$adb->pquery (
					"INSERT INTO vtiger_weekly_report_master (
                        weekly_report_code,
                        report_type,
                        task,
                        task_subject,
                        related_module,
                        related_id,
                        project,
                        business_initiative,
                        action_plan,
                        planned_hours,
                        execution_hours,
                        advance_Task
                    )
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
					array (
						$weeklyReportCode,
						$reportType,
						$row ['activityid'],
						$row ['subject'],
						$row ['related_module'],
						$row ['related_id'],
						(is_array ($row ['project']) ? json_encode ($row ['project']): $row ['project']),
						(is_array ($row ['business_initiatives']) ? json_encode ($row ['business_initiatives']): $row ['business_initiatives']),
						(is_array ($row ['action_plan']) ? json_encode ($row ['action_plan']): $row ['action_plan']),
						$row ['estimated_time'],
						($reportType == 'DAILY_REPORT') ? $row ['total_hours_reported'] : $row ['duration_time'],
						$row ['progress']
					)
				);
			}
			
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param string $theDate
		 * @param array $bscs
		 * @return void
		 * @throws Exception
		 */
		public function setBoxScoreCalculated ($adb, $theDate, $bscs) {
			$processedCalculation = array();
			$objCalculatedFields  = new CalculatedFieldsUtils ($adb, '');
			$bsm                  = BoxScoreManager::getInstance ($adb);
			foreach ($bscs as $bsc) {
				if (in_array ($bsc['box_score_dataid'], $processedCalculation)) {
					continue;
				}
				$result = $objCalculatedFields->getCalculateSystemById ($bsc['calculated_system'], 0, 'boxScore', 0, false);
				echo 'Ejecutando calculo en '. $bsc['calculated_system'] . ' ' . $theDate . ' ' . $adb->dbName . PHP_EOL;
				$bsm->saveDataWeekly ($bsc ['boxscoreid'], $bsc ['box_score_dataid'], $result, $theDate);
				$processedCalculation [] = $bsc['box_score_dataid'];
			}
		}
		
	}
<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ActivityReportManager.php');
	require_once ('include/platzilla/Data/EntityHistoryManager.php');
	require_once ('include/platzilla/Data/TaskActivity.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/utils/TableFieldUtils.php');
	require_once ('modules/Calendar/Activity.php');
	require_once ('modules/Settings/lib/PanelViewHelper.class.php');
	require_once ('modules/daily_report/Objects/DailyReportInterface.php');
	require_once ('modules/daily_report/handlers/progressOfWork.class.php');
	require_once ('modules/daily_report/lib/DailyReportMaster.class.php');
	require_once ('modules/incidencias/incidencias.php');
	
	class DailyReportActivities  implements DailyReportInterface {
		
		const OTHER_INFO_TYPE = array ('Problema', 'Sugerencias');
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		/** @var PearDatabase */
		protected $adb;
		
		protected $unAvailableModules = array (
			'answers',
			'daily_report',
			'etapas_proyecto',
			'questionnaire',
			'reportes',
			'platzi_issabel'
		);
		
		protected $errorMessage = '';
		
		public function __construct($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		/**
		 * @param string $plannedActivityId
		 *
		 * @return array|null
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function checkPlannedActivity ($plannedActivityId) {
			if (empty($plannedActivityId)) {
				return null;
			}
			list ($crmId, $dummy) = explode ('@%', $plannedActivityId);
			$result = $this->adb->pquery ('SELECT * FROM vtiger_daily_report_master WHERE dailyreportid LIKE ?', array ($plannedActivityId));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['crmid'] != $crmId) {
						continue;
					}
					$oldData [$row ['dailyreportid']] = $row;
				}
			}
			
			$this->adb->pquery ('DELETE FROM vtiger_daily_report_master WHERE dailyreportid LIKE ?', array($plannedActivityId));
			DatabaseUtils::closeResult ($result);
			$result = null;
			
			return (isset ($oldData)) ? $oldData : null;
		}
		
		/**
		 * @param string $appFieldParameters
		 *
		 * @return DailyReportMaster[]|null
		 * @throws Exception
		 */
		private function createDailyReport ($appFieldParameters) {
			$parameter            = base64_decode ($appFieldParameters);
			$dummy                = explode ('@', $parameter);
			$period ['startdate'] = $dummy [0];
			$period ['enddate']   = $dummy [0];
			$userId               = isset($dummy[1]) ? $dummy[1] : null;
			
			$activities = DataViewUtils::fetchTaskToDailyReport ($this->adb, $period, $userId, true);
			
			if (!empty($activities)) {
				foreach ($activities as $index => $activity) {
					$activityReports = $activity->getActivityReports();
					
					$reports         = $this->getTimeSpentFromReports ($activity);
					
					$dailyReports [] = DailyReportMaster::getInstance ()
						->setId ($activity->getActivityId ())
						->setCrmId (null)
						->setProgress ($reports ['progress'])
						->setDurationTime ($reports ['time'])
						->setActivity ($activity)
						->setReport ($activity->getActivityReports ())
						->setReportIds ($reports ['ids']);
				}
			}
			return (isset($dailyReports)) ? $dailyReports : null;
		}
		
		/**
		 * @param integer $userId
		 * @param integer $crmId
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function createTasksAndIncidents ($otherInformation, $userId, $crmId) {
			if (!empty ($otherInformation && count ($otherInformation['other_info_type']) || !empty ($crmid))) {
				$totalInformation = count ($otherInformation['other_info_type']);
				for ($i = 0; $i < $totalInformation; $i++) {
					if (in_array ($otherInformation ['other_info_type'][$i],self::OTHER_INFO_TYPE)) {
						$issueData = array (
							'title'       => trim ($otherInformation ['other_info_title'][$i]),
							'description' => trim ($otherInformation ['other_info_description'][$i]),
							'matter'      => trim ($otherInformation ['other_info_type'][$i]),
							'assigned'    => $userId,
							'crmId'       => $crmId,
						);
						$this->saveTaskAndIncidents ($issueData);
					}
				}
			}
		}
		
		/**
		 * @param $crmId
		 *
		 * @return null|DailyReportMaster[]
		 * @throws Exception
		 */
		private function fetchDailyReportById ($crmId, $user) {
			if (empty($crmId)) {
				return null;
			}
			$numberingHelper = NumberHelper::getInstance ($this->adb, $user);
			$paId            = "{$crmId}@%";
			$result = $this->adb->pquery ('SELECT * FROM vtiger_daily_report_master WHERE dailyreportid LIKE ?', array ($paId));
			$drm    = ActivityReportManager::getInstance ($this->adb);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$dailyReports [] = DailyReportMaster::getInstance ()
						->setId ($row ['dailyreportid'])
						->setCrmId ($row ['crmid'])
						->setProgress ($numberingHelper->setNumberFormat ($row ['progress']))
						->setDurationTime ($numberingHelper->setNumberFormat ($row ['duration_time']))
						->setActualCost ($numberingHelper->setNumberFormat ($row ['actual_cost']))
						->setActivity ($drm->fetchActivityTaskById (intval ($row ['activityid'])))
						->setReport ($drm->fetchActivityReportByGroup ($row ['activityreportid']))
						->setReportIds ($row ['activityreportid']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($dailyReports)) ? $dailyReports : null;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return null|array
		 * @throws Exception
		 */
		private function fetchRelatedTask ($crmId) {
			if (empty($crmId)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT
				    act.activityid,
				    act.subject
				FROM
				    vtiger_activity act
				INNER JOIN vtiger_crmentity crm ON crm.crmid = activityid AND crm.deleted = 0
				INNER JOIN vtiger_seactivityrel sea ON sea.activityid = act.activityid
				WHERE
				    sea.crmid=?',
				array ($crmId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$relatedTask [$row ['activityid']] = $row ['subject'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($relatedTask)) ? $relatedTask : null;
		}
		
		/**
		 * @param $type
		 *
		 * @return string
		 */
		private function getAttachmentType ($type) {
			if (empty ($type)) {
				return 'fa-file-o';
			}
			switch ($type) {
				case 'application/pdf':
					return 'fa-file-pdf-o';
				case 'application/msword':
					return 'fa-file-word-o';
				case 'application/vnd.ms-excel':
					return 'fa-file-excel-o';
				case 'application/vnd.ms-powerpoint':
					return 'fa-file-powerpoint-o';
				case 'application/zip':
					return 'fa-file-archive-o';
				case 'text/plain':
					return 'fa-file-text-o';
				case 'image/jpeg':
				case 'image/png':
				case 'image/gif':
					return 'fa-file-image-o';
				default:
					return 'fa-file-o';
			}
		}
		
		/**
		 * @return array|null
		 */
		private function getAvailableModules () {
			$result = $this->adb->run_query_allrecords ('SELECT tabid,name,tablabel FROM `vtiger_tab` WHERE `presence` IN(0,2) AND `tabsequence` != -1 AND `isplatzilla` = 0 AND `isvisibleinadmin` = 1  ORDER BY  `tablabel` ASC');
			if (count ($result)) {
				foreach ($result as $moduleData) {
					if (in_array ($moduleData['name'], $this->unAvailableModules)) {
						continue;
					}
					$moduleViewStatus = PanelViewHelper::getStatusModule ($this->adb, $moduleData ['name'], null);
					if ($moduleViewStatus == 'HIDDEN') {
						continue;
					}
					$availableModules[] = array(
						'label' => $moduleData ['tablabel'],
						'value' => "{$moduleData ['name']}@{$moduleData ['tabid']}",
					);
				}
			}
			return (isset($availableModules)) ? $availableModules : null;
		}
		
		/**
		 * @param TaskActivity $reports
		 *
		 * @return array|null
		 */
		private function getTimeSpentFromReports (&$reports) {
			if (empty($reports)) {
				return array ('progress' => 0,  'time' => 0, 'ids' => '');
			}
			$totalTame   = 0;
			$progress    = 0;
			$reportsId   = '';
			foreach ($reports->getActivityReports () as $report) {
				$theReport = trim ($report->getReport ());
				// No modificar el texto del reporte, solo mantenerlo como está
				$report->setReport ($theReport);
				
				$reportsId .= ",{$report->getId ()}";
				$totalTame += $report->getTimeDuration ();
				$progress   = ($report->getProgress () > $progress) ? $report->getProgress () : $progress;
			}
			$totalTame = number_format ($totalTame, 2, '.', '');
			return array ('progress' => $progress,  'time' => $totalTame, 'ids' => $reportsId);
		}
		
		/**
		 * @param array $achievements
		 * @param integer $crmId
		 * @return void
		 */
		private function saveAchievements ($achievements, $crmId, $userId) {
			if (!empty ($crmId)) {
				$this->adb->pquery ('DELETE FROM vtiger_daily_report_achievements WHERE daily_reporttfid= ? AND user_id=?', array ($crmId, $userId));
			}
			if (empty($achievements || !count ($achievements['achievement_name']) || empty ($crmId))) {
				return;
			}
			$totalAchievements = count ($achievements['achievement_name']);
			for ($i = 0; $i < $totalAchievements; $i++) {
				$this->adb->pquery (
					'INSERT INTO vtiger_daily_report_achievements (daily_reporttfid, user_id, achievement_name,  	achievement_description) VALUES (?, ?, ?, ?)',
					array ($crmId, $userId, $achievements['achievement_name'][$i], $achievements['achievement_description'][$i])
				);
			}
		}
		
		/**
		 * @param array $dataTask
		 *
		 * @return integer
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function saveActivity ($dataTask) {
			$today = date('Y-m-d');
			$activity                                     = new Activity ();
			$activity->column_fields ['activitytype']     = 'Activity';
			$activity->column_fields ['assigned_user_id'] = $dataTask ['ownerUserId'];
			$activity->column_fields ['date_start']       = $today;
			$activity->column_fields ['description']      = $dataTask ['description'];
			$activity->column_fields ['due_date']         = ($dataTask ['progress']>= 100) ? $today : null;
			$activity->column_fields ['estimated_time']   = $dataTask ['estimatedTime'];
			$activity->column_fields ['eventstatus']      = ($dataTask ['progress']>= 100) ? 'Held' : $dataTask ['eventStatus'];
			$activity->column_fields ['notime']           = 0;
			$activity->column_fields ['progress']         = $dataTask ['progress'];
			$activity->column_fields ['recurringtype']    = '--None--';
			$activity->column_fields ['sendnotification'] = 0;
			$activity->column_fields ['subject']          = $dataTask ['subject'];
			$activity->column_fields ['taskpriority']     = $dataTask ['priority'];
			$activity->column_fields ['categoryid']       = 10;
			$activity->column_fields ['time_end']         = null;
			$activity->column_fields ['time_start']       = null;
			$activity->column_fields ['visibility']       = 'Public';
			$activity->column_fields ['related_id']       = $dataTask ['related_id'];
			$activity->column_fields ['related_to']       = $dataTask ['related_module'];
			$activity->column_fields ['importance']       = $dataTask ['importance'];
			$activity->column_fields ['planned_task']     = $dataTask ['plannedTask'];
			$activity->column_fields ['show_in_matrix']   = 'YES';
			
			$activity->save ('Calendar');
			$this->adb->pquery ('INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array($dataTask ['related_id'], $activity->id));
			$activityId = $activity->id;
			unset ($activity);
			return $activityId;
			
		}
		
		/**
		 * @param array $otherInformation
		 * @param integer $crmId
		 * @return void
		 */
		private function saveOtherInformation ($otherInformation, $crmId, $userId) {
			if (!empty ($crmId)) {
				$this->adb->pquery ('DELETE FROM vtiger_daily_report_other_info WHERE daily_reporttfid = ?', array ($crmId));
			}
			if (empty ($otherInformation || !count ($otherInformation['other_info_type']) || empty ($crmid))) {
				return;
			}
			$totalInformation = count ($otherInformation['other_info_type']);
			for ($i = 0; $i < $totalInformation; $i++) {
				$this->adb->pquery (
					'INSERT INTO vtiger_daily_report_other_info (daily_reporttfid, other_info_type, user_id, other_info_title, other_info_description) VALUES (?, ?, ?, ?, ?)',
					array ($crmId, $otherInformation['other_info_type'][$i], $userId, $otherInformation['other_info_title'][$i], $otherInformation['other_info_description'][$i])
				);
			}
		}
		
		/**
		 * @param array $plannedActivities
		 * @param integer$userId
		 * @param array $oldData
		 *
		 * @throws Exception
	 */
	private function savePlannedActivities ($plannedActivities, $crmId, $userId, $oldData, $reportType = 'TASK', $reportDate = null) {
			$totalTask       = count ($plannedActivities['reported_task_id']);
			$numberingHelper = NumberHelper::getInstance ($this->adb);
			for ($k = 0; $k < $totalTask; $k++) {
				if (!isset ($plannedActivities['report_id'][$k]) || empty ($plannedActivities['reported_task_id'][$k])) {
					$numTask = ($k + 1);
					if (empty($this->errorMessage)) {
						$this->errorMessage = "Imposible guardar la tarea planeada N° {$numTask} tarea no encontrada";
					} else {
						$this->errorMessage .=  "<br/>Imposible guardar la tarea planeada N° {$numTask} tarea no encontrada";
					}
					continue;
				}
				// Si todos los campos de reporte están vacíos/cero, omitir esta tarea
				if ($this->isEmptyPlannedTask($plannedActivities, $k, $numberingHelper)) {
					// En modo edit con reporte previo: hacer soft-delete del reporte existente
					if (!empty($plannedActivities['report_id'][$k])) {
						try {
							ActivityReportManager::getInstance($this->adb)->softDeleteReport($plannedActivities['report_id'][$k]);
						} catch (Exception $e) {
							// ignorar si ya no existe
						}
					}
					continue;
				}
				if (isset($plannedActivities['report_id'][$k]) && empty($plannedActivities['report_id'][$k])) {
					$actualCost = isset($plannedActivities['actual_cost'][$k]) ? $numberingHelper->setSaveNumberFormat($plannedActivities['actual_cost'][$k]) : 0.00;
					// Si reportType es null, determinar por módulo; si es fijo (TASK), usar ese valor
					if ($reportType === null) {
						$reportOn = (isset($plannedActivities['reported_task_module'][$k]) && $plannedActivities['reported_task_module'][$k] == 'orden_de_trabajo') ? 'JOB' : 'TASK';
					} else {
						$reportOn = $reportType;
					}
					$reportData = array (
						$plannedActivities['reported_task_id'][$k],
						$userId,
						$plannedActivities['task_advanced_report'][$k],
						$reportOn,
						substr (strip_tags ($plannedActivities['task_advanced_report'][$k]), 0,120). '...',
						$numberingHelper->setSaveNumberFormat ($plannedActivities['time_reported'][$k]),
						$numberingHelper->setSaveNumberFormat ($plannedActivities['task_progress_perc'][$k]),
						$actualCost,
						$reportDate
					);
					$reportId = $this->saveReport ($reportData);
					$paId     = "{$crmId}@{$plannedActivities['reported_task_id'][$k]}@{$reportId}";
				} else {
					$paId     = "{$crmId}@{$plannedActivities['reported_task_id'][$k]}@{$plannedActivities['report_id'][$k]}";
					$reportId = $plannedActivities['report_id'][$k];
					// Actualizar el reporte existente con los nuevos valores
					$actualCostUpdate = isset($plannedActivities['actual_cost'][$k]) ? $numberingHelper->setSaveNumberFormat($plannedActivities['actual_cost'][$k]) : 0.00;
					$durationTimeUpdate = $numberingHelper->setSaveNumberFormat($plannedActivities['time_reported'][$k]);
					$progressUpdate = $numberingHelper->setSaveNumberFormat($plannedActivities['task_progress_perc'][$k]);
					$this->adb->pquery(
						'UPDATE vtiger_activity_report SET duration_time=?, progress=?, actual_cost=?, report=?, activity_report_date=? WHERE activityreportid=?',
						array($durationTimeUpdate, $progressUpdate, $actualCostUpdate, $plannedActivities['task_advanced_report'][$k], $reportDate, $reportId)
					);
				}
				$durationTime = $numberingHelper->setSaveNumberFormat ($plannedActivities['time_reported'][$k]);
				$progress	  = $numberingHelper->setSaveNumberFormat ($plannedActivities['task_progress_perc'][$k]);
				if (
					(!empty($oldData)) &&
					(($numberingHelper->setSaveNumberFormat($oldData[$paId]['progress']) != $progress) ||
					($numberingHelper->setSaveNumberFormat($oldData[ $paId]['duration_time']) != $durationTime))
				) {
					$dtCreated = date ('Y-m-d H:i:s');
				} else {
					$dtCreated = (!empty($oldData)) ? $oldData[ $paId]['dt_created'] : date ('Y-m-d H:i:s');
				}
				$actualCost = isset($plannedActivities['actual_cost'][$k]) ? $numberingHelper->setSaveNumberFormat($plannedActivities['actual_cost'][$k]) : 0.00;
				$this->adb->pquery (
					'INSERT INTO vtiger_daily_report_master (dailyreportid, crmid, activityid, activityreportid, userid, progress, duration_time, actual_cost, dt_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($paId, $crmId, $plannedActivities['reported_task_id'][$k], $reportId, $userId, $progress, $durationTime, $actualCost, $dtCreated)
				);
				$updateData = array (
					'activityid' => $plannedActivities ['reported_task_id'][$k],
					'progress'   => $progress,
					'reports'    => $plannedActivities['task_advanced_report'][$k],
					'related_to' => $plannedActivities['reported_task_module'][$k],
				
				);
				$this->updateActivityAndReport ($updateData);
			}
		}
		
		/**
		 * Determina si una tarea planeada no tiene ningún dato registrado.
		 * Retorna true si % avance, reporte de avance, unidades empleadas y costo incurrido
		 * están todos en sus valores vacíos/cero.
		 *
		 * @param array $plannedActivities
		 * @param int $k índice de la tarea
		 * @param NumberHelper $numberingHelper
		 * @return bool
		 */
		private function isEmptyPlannedTask($plannedActivities, $k, $numberingHelper) {
			$duration    = $numberingHelper->setSaveNumberFormat(isset($plannedActivities['time_reported'][$k])       ? $plannedActivities['time_reported'][$k]       : 0);
			$cost        = $numberingHelper->setSaveNumberFormat(isset($plannedActivities['actual_cost'][$k])         ? $plannedActivities['actual_cost'][$k]         : 0);
			$reportText  = isset($plannedActivities['task_advanced_report'][$k]) ? trim(strip_tags($plannedActivities['task_advanced_report'][$k])) : '';
			return ($duration == 0 && $cost == 0 && $reportText === '');
		}

		/**
		 * @param array $reportData
		 *
		 * @return integer
		 * @throws Exception
		 */
		private function saveReport ($reportData) {
			$this->adb->pquery (
				'INSERT INTO vtiger_activity_report (activityid, userid, report, report_on, title, duration_time, progress, actual_cost, activity_report_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
				$reportData
			);
			$result = $this->adb->query ('SELECT MAX(activityreportid) AS last_id FROM vtiger_activity_report WHERE 1');
			$row    = $this->adb->fetchByAssoc ($result, -1, false);
			$id     = $row ['last_id'];
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $id;
		}
		
		/**
		 * @param array $dataInfo
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function saveTaskAndIncidents ($dataInfo) {
			$tomorrow   = date ("Y-m-d", strtotime("+1 day"));
			$reportDate = $_REQUEST['daily_report_date'];
			$affairs    = CRMEntity::getInstance ('affairs');
			
			$affairs->column_fields = getColumnFields ('affairs');
			$affairs->column_fields ['affair_title']       = $dataInfo ['title'];
			$affairs->column_fields ['affair_description'] = $dataInfo ['description'];
			$affairs->column_fields ['affair_results']     = $dataInfo ['matter'];
			$affairs->column_fields ['affair_priority']    = 'Normal';
			$affairs->column_fields ['affair_status']      = 'Nuevo';
			$affairs->column_fields ['assigned_user_id']   = $dataInfo ['assigned'];
			$affairs->column_fields ['daily_report']        = $dataInfo ['crmId'];
			$affairs->column_fields ['affair_date']         = $reportDate;
			$affairs->save ('affairs');
			$idAffairs = $affairs->id;
			unset ($affairs);
			
			$activity =  CRMEntity::getInstance ('Calendar'); //new Activity ();
			$activity->column_fields ['activitytype']     = 'Activity';
			$activity->column_fields ['assigned_user_id'] = $dataInfo ['assigned'];
			$activity->column_fields ['date_start']       = $tomorrow;
			$activity->column_fields ['due_date']         = $tomorrow;
			$activity->column_fields ['estimated_time']   = 1;
			$activity->column_fields ['description']      =  $dataInfo ['description'];
			$activity->column_fields ['eventstatus']      = 'Planned';
			$activity->column_fields ['recurringtype']    = '--None--';
			$activity->column_fields ['sendnotification'] = 0;
			$activity->column_fields ['subject']          = $dataInfo ['title'];
			$activity->column_fields ['taskpriority']     = 'Bajo';
			$activity->column_fields ['categoryid']       = 10;
			$activity->column_fields ['time_end']         = null;
			$activity->column_fields ['visibility']       = 'Public';
			$activity->column_fields ['related_id']       = $idAffairs;
			$activity->column_fields ['importance']       = 'HIGH';
			$activity->column_fields ['planned_task']     = 'PLANNED_AND_RECORDED';
			$activity->column_fields ['show_in_matrix']   = 'YES';
			$activity->save ('Calendar');
			foreach (array ($idAffairs, $dataInfo['crmId']) as $crmId) {
				$this->adb->pquery ('INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array($crmId, $activity->id));
			}
			unset($activity);
			
		}
		
		/**
		 * @param $unregisteredActivities
		 * @param $crmId
		 * @param $userId
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function saveUnregisteredActivities ($unregisteredActivities, $crmId, $userId, $recordCode, $reportType = 'TASK', $reportDate = null) {
			$totalTask       = count ($unregisteredActivities['reported_task_condition']);
			$numberingHelper = NumberHelper::getInstance ($this->adb);
			for ($k = 0; $k < $totalTask; $k++) {
				if (empty ($unregisteredActivities['relatedcrmids'][$k])) {
					$numTask = ($k + 1);
					if (empty($this->errorMessage)) {
						$this->errorMessage = "Imposible guardar la tarea no planeada N° {$numTask} no esta relacionada";
					} else {
						$this->errorMessage .=  "<br/>Imposible guardar la tarea no planeada N° {$numTask} no esta relacionada";
					}
					continue;
				}
				$progress    = $numberingHelper->setSaveNumberFormat ($unregisteredActivities['pc_task_advanced'][$k]);
				$plannedTask = $unregisteredActivities['reported_task_condition'][$k];
				if ($unregisteredActivities['reported_task_condition'][$k] == 'PLANNED_UNREGISTERED') {
					$priority   =  'Bajo';
					$importance = 'HIGH';
				} else if ($unregisteredActivities['reported_task_condition'][$k] == 'UNEXPECTED')  {
					$priority   =  'Alto';
					$importance = 'HIGH';
				}
				$taskData = array (
					'ownerUserId'   => $userId,
					'estimatedTime' => $numberingHelper->setSaveNumberFormat ($unregisteredActivities['estimated_time_task'][$k]),
					'eventStatus'   => ($progress == 0) ? 'Planned' : (($progress >= 100) ? 'Held' : 'Not Held'),
					'progress'      => ($progress > 100) ? 100 : $progress,
					'subject'       => $unregisteredActivities['reported_task'][$k],
					'description'   => "Tarea creada desde el Informe díario código: {$recordCode}",
					'priority'      => $priority,
					'importance'    => $importance,
					'plannedTask'   => (empty($plannedTask)) ? 'PLANNED_UNREGISTERED' :$plannedTask,
					'related_id'    => $unregisteredActivities['relatedcrmids'][$k],
					'related_to'    => $unregisteredActivities['reported_task_module'][$k],
				);
				$activityId = $this->saveActivity ($taskData);
				$actualCost = isset($unregisteredActivities['actual_cost'][$k]) ? $numberingHelper->setSaveNumberFormat($unregisteredActivities['actual_cost'][$k]) : 0.00;
				// Si reportType es null, determinar por módulo; si es fijo (TASK), usar ese valor
				if ($reportType === null) {
					$reportOn = (isset($unregisteredActivities['reported_task_module'][$k]) && $unregisteredActivities['reported_task_module'][$k] == 'orden_de_trabajo') ? 'JOB' : 'TASK';
				} else {
					$reportOn = $reportType;
				}
				$reportData = array (
					$activityId,
					$userId,
					$unregisteredActivities['task_advanced_report'][$k],
					$reportOn,
					substr ($unregisteredActivities['task_advanced_report'][$k], 0,120). '...',
					$numberingHelper->setSaveNumberFormat ($unregisteredActivities['time_reported'][$k]),
					($progress > 100) ? 100 : $progress,
					$actualCost,
					$reportDate
				);
				$reportId = $this->saveReport ($reportData);
				$paId     = "{$crmId}@{$activityId}@{$reportId}";
				$durationTime = $numberingHelper->setSaveNumberFormat ($unregisteredActivities['time_reported'][$k]);
				$progress	  = $numberingHelper->setSaveNumberFormat ($unregisteredActivities['pc_task_advanced'][$k]);
				$this->adb->pquery (
					'INSERT INTO vtiger_daily_report_master (dailyreportid, crmid, activityid, activityreportid, userid, progress, duration_time, actual_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
					array ($paId, $crmId, $activityId, $reportId, $userId, $progress, $durationTime, $actualCost)
				);
			}
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function searchPreviousTasksAndIncidents ($crmId) {
			$result = $this->adb->pquery ('SELECT activityid FROM vtiger_seactivityrel WHERE crmid=?', array ($crmId));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$sql = $this->adb->pquery (
						'SELECT
								sea.crmid,
								DATE_FORMAT(crm.createdtime,"%Y-%m-%d %h:%i:%s") as dt_create,
								DATE_FORMAT(crm.modifiedtime,"%Y-%m-%d %h:%i:%s") as dt_modified
							  FROM
							  	vtiger_seactivityrel sea
							  INNER JOIN vtiger_crmentity crm ON crm.crmid = sea.crmid AND crm.deleted=0
							  WHERE
							  	crm.setype=? AND
							  	activityid=?
							  	ORDER By sea.crmid ASC',
						array ('incidencias', $row['activityid'])
					);
					if ($this->adb->num_rows ($sql) > 0) {
						while ($sqlRow = $this->adb->fetchByAssoc ($sql, -1, false)) {
							$records[] = array (
								'taskId'       => $row ['activityid'],
								'incidentId'   => $sqlRow ['crmid'],
								'createdDate'  => $sqlRow ['dt_create'],
								'midifiedDate' => $sqlRow ['dt_modified'],
							);
						}
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			DatabaseUtils::closeResult ($sql);
			$sql = null;
			return (isset($records)) ? $records : null;
		}
		
		/**
		 * @param array $updateData
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function updateActivityAndReport ($updateData) {
			$entity  = new Activity ();
			$entity->retrieve_entity_info ($updateData ['activityid'], 'Calendar');
			$entity->id      = $updateData ['activityid'];
			$entity->mode    = 'edit';
			$numberingHelper = NumberHelper::getInstance ($this->adb);
			$theProgress     = $numberingHelper->setSaveNumberFormat ($updateData ['progress']);
			if ($theProgress >= 100) {
				$entity->column_fields ['due_date']    = (empty ($entity->column_fields ['due_date'])) ? date ('Y-m-d') : $entity->column_fields ['due_date'];
				$entity->column_fields ['eventstatus'] = 'Held';
			}
			if (
				($entity->column_fields ['progress'] > 0) &&
				($entity->column_fields ['progress'] < 100) &&
				($entity->column_fields ['eventstatus'] != 'Held')
			) {
				$entity->column_fields ['eventstatus'] = 'Not Held';
			}
			
			if (empty ($entity->column_fields ['date_start'])) {
				$entity->column_fields ['date_start'] = date ('Y-m-d');
			}
			$entity->column_fields ['progress']     = $theProgress;
			$entity->column_fields ['related_to']   = $updateData ['related_to'];
			$entity->column_fields ['planned_task'] = 'PLANNED_AND_RECORDED';
			if (empty($entity->column_fields ['importance'])) {
				$entity->column_fields ['importance'] = 'LOW';
			}
			$entity->save ('Calendar');
			unset ($entity);
			
			if (!empty ($updateData['reports'])) {
				$dummy        = explode ('-->',$updateData['reports']);
				$totalReports = count ($dummy);
				for ($k=0; $k< $totalReports; $k++) {
					if (empty ($dummy[$k])) {
						continue;
					}
					$dataRecord = explode ('<!--', $dummy[$k]);
					$theReport  = explode ('-', $dataRecord [0], 2);
					$thisReport = "<p>{$theReport [1]}";
					$thisId     = intval (trim ($dataRecord [1]));
					if (empty($thisId) || empty($thisReport)) {
						continue;
					}
					$this->adb->pquery (
						'UPDATE vtiger_activity_report SET report=?  WHERE activityreportid=?',
						array ($thisReport, $thisId)
					);
				}
			}
		}
		
		/**
		 * @param $dataInfo
		 * @param $prevRows
		 * @throws Exception
		 */
		private function updateIncidents ($dataInfo, $prevRows) {
			if (empty ($prevRows)) {
				return;
			}
			
			$updateTitle       = null;
			$foundTitle        = false;
			$updateDescription = null;
			$foundDescription  = false;
			$incidentsId       = $prevRows['incidentId'];
			$entityHistories = EntityHistoryManager::getInstance ($this->adb)->fetchEntityHistory ($incidentsId);
			if (!empty($entityHistory)) {
				foreach ($entityHistories as $entityHistory) {
					if (($entityHistory->getFieldName () == 'titulo') && ! $foundTitle) {
						$foundTitle = true;
						$updateTitle = $entityHistory->getOldValue ();
					} else if (($entityHistory->getFieldName () == 'descripcion') && ! $foundDescription) {
						$foundDescription = true;
						$updateDescription = $entityHistory->getOldValue ();
					} else if ($foundDescription && $foundTitle) {
						break;
					}
				}
			}
			if (empty ($updateTitle) || empty($updateDescription) && $incidentsId) {
				$incidents = CRMEntity::getInstance ('incidencias');
				$incidents->mode = 'edit';
				$incidents->id   =  $incidentsId;
				$incidents->column_fields = getColumnFields ('incidencias');
				$incidents->retrieve_entity_info ($incidentsId, 'incidencias');
				$incidents->column_fields ['titulo']      = $dataInfo ['title'];
				$incidents->column_fields ['descripcion'] = $dataInfo ['description'];
				$incidents->save ('incidencias');
				unset ($incidents);
			}
			
		}
		
		/**
		 * @param $dataInfo
		 * @param $prevRows
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function updateTask ($dataInfo, $prevRows) {
			if (empty($prevRows)) {
				return;
			}
			$updateTitle        = null;
			$foundTitle         = false;
			$updateDescription  = null;
			$foundDescription   = false;
			$taskId             = $prevRows ['taskId'];
			$entityHistories = EntityHistoryManager::getInstance ($this->adb)->fetchEntityHistory ($taskId);
			if (!empty($entityHistory)) {
				foreach ($entityHistories as $entityHistory) {
					if (($entityHistory->getFieldName () == 'subject') && ! $foundTitle) {
						$foundTitle  = true;
						$updateTitle = $entityHistory->getOldValue ();
					} else if (($entityHistory->getFieldName () == 'description ') && ! $foundDescription) {
						$foundDescription  = true;
						$updateDescription = $entityHistory->getOldValue ();
					} else if ($foundDescription && $foundTitle) {
						break;
					}
				}
			}
			if ((empty ($updateTitle) || empty($updateDescription)) && $taskId) {
				$activity =  CRMEntity::getInstance ('Calendar');
				$activity->mode = 'edit';
				$activity->id   = $taskId;
				$activity->retrieve_entity_info ($taskId, 'Calendar');
				$activity->column_fields ['description'] = $dataInfo ['description'];
				$activity->column_fields ['subject']     = $dataInfo ['title'];
				$activity->save ('Calendar');
				unset($activity);
			}
		}
		
		/**
		 * @param integer $userId
		 * @param integer $crmId
		 * @param array $prevRows
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		private function updateTaskAndIncidents ($userId, $crmId, $othersInfo, $prevRows) {
			if (empty ($othersInfo ['other_info_type'])) {
				return;
			}
			
			$entityHistories = EntityHistoryManager::getInstance ($this->adb)->fetchEntityHistory ($crmId);
			if (!empty ($entityHistories)) {
				foreach ($entityHistories as $entityHistory) {
					if (($entityHistory->getFieldName () == 'otra_informacion') && ! empty ($entityHistory->getNewValue ())) {
							$lastValues = json_decode ($entityHistory->getNewValue (), true);
							break;
					}
				}
			}
			$rows = count ($othersInfo['other_info_type']);
			for ($k = 0; $k < $rows; $k++ ) {
				if (!in_array ($othersInfo['other_info_type'][$k],self::OTHER_INFO_TYPE)) {
						continue;
				}
					
				if (isset ($lastValues) && !isset ($lastValues ['other_info_title'][$k])) {
					$issueData = array (
						'title'       => trim ($othersInfo ['other_info_title'][$k]),
						'description' => trim ($othersInfo ['other_info_description'][$k]),
						'assigned'    => $userId,
						'crmId'       => $crmId,
					);
					$this->saveTaskAndIncidents ($issueData);
				} else if (
					isset ($lastValues) &&
					($othersInfo ['other_info_title'][$k] != $lastValues ['other_info_title'][$k]) ||
					($othersInfo ['other_info_description'][$k] != $lastValues ['other_info_description'][$k])
				){
					$issueData = array (
						'title'       => trim ($othersInfo ['other_info_title'][$k]),
						'description' => trim ($othersInfo ['other_info_description'][$k]),
						'assigned'    => $userId,
						'crmId'       => $crmId,
					);
						
					$this->updateIncidents ($issueData, $prevRows[$k]);
					$this->updateTask ($issueData, $prevRows[$k]);
				}
					
			}
			
		}
		
		/**
		 * @param integer $userId
		 * @param integer $crmId
		 * @param string $recordCode
		 *
		 * @throws Exception
		 */
		public function buildupDailyReport ($userId, $crmId, $recordCode, $mode) {
			if (empty ($userId) || empty($crmId)) {
				$_SESSION ['flashmessage']['iserror'] = true;
				$_SESSION ['flashmessage']['message'] = 'Uoops! algo salio mal, intenta de nuevo';
			} else if (
				empty ($_REQUEST['planned_tasks']) &&
				empty ($_REQUEST['performed_tasks']) &&
				empty ($_REQUEST['planned_actions']) &&
				empty ($_REQUEST['performed_actions'])
			) {
				$_SESSION ['flashmessage']['iserror'] = true;
				$_SESSION ['flashmessage']['message'] = 'No hay actividades reportadas';
			}
			
			$reportDate = isset ($_REQUEST['daily_report_date']) ? getValidDBInsertDateValue ($_REQUEST['daily_report_date']) : date ('Y-m-d');
			$paId   = "{$crmId}@%";
			$oldData = $this->checkPlannedActivity ($paId);
			$this->savePlannedActivities ($_REQUEST['planned_tasks'], $crmId, $userId, $oldData, 'TASK', $reportDate);
			$this->savePlannedActivities ($_REQUEST['planned_actions'], $crmId, $userId, $oldData, 'TASK', $reportDate);
			$this->saveUnregisteredActivities ($_REQUEST['performed_tasks'], $crmId, $userId, $recordCode, 'TASK', $reportDate);
			$this->saveUnregisteredActivities ($_REQUEST['performed_actions'], $crmId, $userId, $recordCode, 'TASK', $reportDate);
			$this->saveAchievements ($_REQUEST['achievements_day'], $crmId, $userId);
			$this->saveOtherInformation ($_REQUEST['other_information'], $crmId, $userId);
			if (!empty($this->errorMessage)) {
				$_SESSION ['flashmessage']['iserror'] = true;
				$_SESSION ['flashmessage']['message'] =  $this->errorMessage;
			}
			if ($mode == 'create' && !empty ($crmId) && !empty ($userId)) {
				$this->createTasksAndIncidents ($_REQUEST['other_information'], $userId, $crmId);
			} else if ($mode == 'edit' && !empty ($crmId) && !empty ($userId)) {
				$previousRecords = $this->searchPreviousTasksAndIncidents ($crmId);
				if (!empty ($previousRecords)) {
					$this->updateTaskAndIncidents ($userId, $crmId, $_REQUEST['other_information'], $previousRecords);
				} else {
					$this->createTasksAndIncidents ($_REQUEST['other_information'], $userId, $crmId);
				}
			}
		}
		
		/**
		 * @param integer $crmId
		 * @param Users $user
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchAchievements($crmId, $user) {
			if (empty ($crmId)) {
				return null;
			}
			$results = $this->adb->pquery (
				'SELECT * FROM vtiger_daily_report_achievements WHERE daily_reporttfid=? AND user_id=?',
				array ($crmId, $user->id)
			);
			if ($this->adb->num_rows ($results) > 0) {
				$achievements = array ();
				while ($row = $this->adb->fetchByAssoc ($results)) {
					$achievements[] = $row;
				}
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($achievements)) ? $achievements : null;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchAttachments ($crmId) {
			if (empty ($crmId)) {
						return null;
					}
			$result = $this->adb->pquery (
				'SELECT
						att.name,
		       			att.type,
		       			att.path,
		       			CONCAT(att.path, att.attachmentsid, "_", att.name) AS uri
					FROM
						vtiger_attachments att
					INNER JOIN vtiger_crmentity crm ON crm.crmid = att.attachmentsid AND crm.deleted = 0
					INNER JOIN vtiger_seattachmentsrel rel ON rel.attachmentsid = att.attachmentsid
					WHERE
						rel.crmid= ?',
				array ($crmId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if (!file_exists ("{$rootFolderPath}/{$row ['uri']}")) {
						continue;
					}
					$row['path']    = "{$rootFolderPath}/{$row ['uri']}";
					$row['type']    = $this->getAttachmentType ($row['type']);
					$attachments [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($attachments)) ? $attachments : null;
		}
		
		/**
		 * @param integer $crmId
		 * @param Users $user
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchOtherInformation ($crmId, $user) {
			if (empty ($crmId)) {
				return null;
			}
			$results = $this->adb->pquery (
				'SELECT * FROM vtiger_daily_report_other_info WHERE daily_reporttfid=? AND user_id=?',
				array ($crmId, $user->id)
			);
			if ($this->adb->num_rows ($results) > 0) {
				$otherInformation = array ();
				while ($row = $this->adb->fetchByAssoc ($results)) {
					$otherInformation[] = $row;
				}
			}
			DatabaseUtils::closeResult ($results);
			$results = null;
			return (isset ($otherInformation)) ? $otherInformation : null;
		}
		
		/**
		 * @param integer $crmId
		 * @param null|string $view
		 * @param User $currentUser
		 * @param null|string $appFieldParameters
		 *
		 * @return string
		 * @throws Exception
		 * @throws SmartyException
		 */
		public function run ($crmId, $view = null, $currentUser, $appFieldParameters = null) {
			require('modules/daily_report/language/es_es.lang.php');
			$plannedTask     = false;
			$unplannedTask   = false;
			$dailyReportData = $this->fetchDailyReportById ($crmId, $currentUser);
			if (empty ($view) && !empty ($appFieldParameters) && (empty ($crmId) || empty ($dailyReportData))) {
				$dailyReportData = $this->createDailyReport ($appFieldParameters);
			} else if(!empty ($view) && !empty ($crmId)){
				$globalReport = ProgressOfWork::getInstance ($this->adb)->fetchGlobalReportData ($crmId, $currentUser);
			}
			$unfinishedJobs = ProgressOfWork::getInstance ($this->adb)->fetchUnfinishedWorks ($currentUser);
			if (!empty ($dailyReportData)) {
				foreach ($dailyReportData as $taskActivity) {
					if (empty ($taskActivity->getActivity ())) {
						continue;
					}
					$taskCondition = $taskActivity->getActivity ()->getActivityCondition ();
					if (($taskCondition == 'PLANNED_AND_RECORDED') && !$plannedTask) {
						$plannedTask = true;
					} else if (($taskCondition == 'PLANNED_UNREGISTERED' || $taskCondition == 'UNEXPECTED') && !$unplannedTask) {
						$unplannedTask = true;
					}
					if ($taskActivity->getActivity ()->getRelatedModule () == 'orden_de_trabajo') {
						$taskActivity->getActivity ()->relatedTasks = $this->fetchRelatedTask ($taskActivity->getActivity ()->getRelatedId ());
					}
					$taskActivity->getActivity ()->attachments = $this->fetchAttachments ($taskActivity->getActivity ()->getActivityId ());
				}
			}
			
			$mode = isset ($_REQUEST['mode']) ? $_REQUEST['mode'] : null;
			$mode = (empty ($mode) && $_REQUEST['action'] == 'EditView') ? 'edit' : $mode;
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ACHIEVEMENTS', $this->fetchAchievements ($crmId, $currentUser));
			$smarty->assign ('AVAILABLE_MODULES', $this->getAvailableModules());
			$smarty->assign ('DAILY_REPORTS', $dailyReportData);
			// LOG: Detalle de datos enviados al template
			if (!empty($dailyReportData)) {
				foreach ($dailyReportData as $idx => $drm) {
					$act = $drm->getActivity();
					if ($act) {
					} else {
					}
					$reps = $drm->getReport();
				}
			}
			$smarty->assign ('DAILY_REPORT_SECTIONS', self::DAILY_REPORT_SECTIONS);
			$smarty->assign ('HAS_PLANNED_TASK', $plannedTask);
			$smarty->assign ('HAS_UNPLANNED_TASK', $unplannedTask);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('MODE', $mode);
			$smarty->assign ('VIEW', $view);
			/* GLOBAL REPORT DATA */
			$smarty->assign ('GLOBAL_REPORT', isset ($globalReport) ? $globalReport : null);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('NUMBERING_FORMAT', $currentUser->numbering_format);
			$smarty->assign ('NUMBERING_HELPER', NumberHelper::getInstance ($this->adb, $currentUser));
			$smarty->assign ('OTHER_INFORMATION', $this->fetchOtherInformation ($crmId, $currentUser));
			$smarty->assign ('UNFINISHED_JOBS', $unfinishedJobs);
			return $smarty->fetch ("modules/DailyReport/DailyReportActivities.tpl");
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return DailyReportActivities
		 */
		public static function getInstance (PearDatabase $adb) {
			return new self ($adb);
		}
	}

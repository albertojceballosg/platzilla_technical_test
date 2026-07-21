<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('modules/Calendar/Activity.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/grid_view/lib/GridViewHelper.class.php');
	
	class ProgressOfWork {
		
		const JOB_MODULE_NAME = 'orden_de_trabajo';
		
		/** @var PearDatabase */
		protected $masterAdb;
		
		/** @var PearDatabase */
		protected $adb;
		
		/**
				 * @param Users $user
				 *
				 * @return array|null
				 * @throws Exception
				 */
		private function fetchReportedWorks ($user, $crmId) {
			if (!$user instanceof Users) {
				return null;
			}
					
			if ($user->is_admin == 'on') {
				$whereUser = '';
			} else {
				$focus = new GetUserGroups();
				$focus->getAllUserGroups ($user->id);
				$groupIds   = (count ($focus->user_groups)) ? $this->adb->sql_expr_datalist ($focus->user_groups) : "('{$user->id}')";
				$whereUser = "  AND (crm.smcreatorid = {$user->id} OR crm.smownerid IN {$groupIds})";
			}
					
			$results = $this->adb->pquery (
				"SELECT
						ot.orden_de_trabajoid,
						ot.cod_orden_de_tra,
		       			ot.titulo,
		       			ot.numero_unidades_planificadas AS estimated_time,
		       			ot.overall_progress_perc AS progress_perc
					FROM vtiger_orden_de_trabajo ot
					INNER JOIN vtiger_crmentity crm ON ot.orden_de_trabajoid = crm.crmid
					WHERE
						crm.deleted=? AND
						ot.orden_de_trabajoid=?
						{$whereUser}",
				array (0, $crmId)
			);
					
			if ($this->adb->num_rows ($results) > 0) {
				$row = $this->adb->fetchByAssoc ($results);
				if (
					(isPermitted (self::JOB_MODULE_NAME, 'EditView', $row['orden_de_trabajoid']) == 'yes') ||
					(isPermitted (self::JOB_MODULE_NAME, 'DetailView', $row['orden_de_trabajoid']) == 'yes')
				) {
					$row['estimated_time'] = (empty ($row['estimated_time'])) ? '0.00' : $row ['estimated_time'];
					$row['progress_perc']  = (empty ($row['progress_perc'])) ? '0.00' : $row ['progress_perc'];
					$reportedWork [] = $row;
				}
				$reportedWork = $row;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($reportedWork)) ? $reportedWork : null;
		}
		
		private function saveActivityReports ($reportJob, $activityIds, $jobTitles, $userId, $reportDate = null) {
			$totalJobs       = count ($reportJob);
			$arm             = ActivityReportManager::getInstance ($this->adb);
			$activityReports = array ();
			$numberingHelper = NumberHelper::getInstance ($this->adb);
			for ($j = 0; $j < $totalJobs; $j++) {
				if (empty ($reportJob['reported_work'][$j])) {
					continue;
				}
				$title	        = $jobTitles[$j];
				$actualCost     = isset($reportJob['actual_cost'][$j]) ? $numberingHelper->setSaveNumberFormat($reportJob['actual_cost'][$j]) : 0.00;
				$activityReport = ActivityReport::getInstance ()
					->setId (null)
					->setActivityId ($activityIds[$j])
					->setProgress ($numberingHelper->setSaveNumberFormat ($reportJob['total_progress'][$j]))
					->setReport ($reportJob['progress_report'][$j])
					->setReportOn ('JOB')
					->setTimeDuration ($numberingHelper->setSaveNumberFormat ($reportJob['time_used'][$j]) ?: 0)
					->setTitle ($title)
					->setUserId ($userId)
					->setActualCost ($actualCost)
					->setActivityReportDate ($reportDate);
					
				$activityReports [$j] = $arm->saveActivityReport ($activityReport);
			}
			return $activityReports;
		}
		
		/**
		 * @param array $reportJob
		 * @param array $jobTitles
		 * @param integer $userId
		 *
		 * @return array|null
		 * @throws WebServiceException
		 */
		private function saveActivityTasks ($reportJob, $jobTitles, $userId) {
			$totalJobs    = count ($reportJob);
			$activityIds = array ();
			$arm         = ActivityReportManager::getInstance ($this->adb);
			$today	     = date ('Y-m-d');
			$time	     = date ('H:i:s');
			$numberingHelper = NumberHelper::getInstance ($this->adb);
			for ($j = 0; $j < $totalJobs; $j++) {
				if (empty ($reportJob['reported_work'][$j])) {
					continue;
				}
				$activityId = $arm->getTaskFromJobId ($reportJob['reported_work'][$j]);
				$isNewTask  = false;
				$entity     =  CRMEntity::getInstance ('Activity');
				if (empty ($activityId)) {
					$isNewTask    = true;
					$entity->id   = null;
					$entity->mode = 'create';
					$entity->column_fields = getColumnFields ('Calendar');
					$entity->column_fields ['activitytype']     = 'Job';
					$entity->column_fields ['subject']          = "Rep. trabajo {$jobTitles[$j]}";
					$entity->column_fields ['date_start']       = $today;
					$entity->column_fields ['time_start']       = $time;
					$entity->column_fields ['eventstatus']      = 'Not Held';
					$entity->column_fields ['importance']       = 'LOW';
					$entity->column_fields ['show_in_matrix']   = 'YES';
					$entity->column_fields ['assigned_user_id'] = $userId;
					$entity->column_fields ['estimated_time']   = 0;
					$entity->column_fields ['related_id']       = $reportJob['reported_work'][$j];
					$entity->column_fields ['related_to']       = self::JOB_MODULE_NAME;
				} else {
					$entity->id   = $activityId;
					$entity->mode = 'edit';
					$entity->retrieve_entity_info ($activityId, 'Calendar');
					if (empty ($entity->column_fields['importance'])) {
						$entity->column_fields['importance'] = 'LOW';
					}
				}
				$theProgress = $numberingHelper->setSaveNumberFormat ($reportJob['total_progress'][$j]);
				$entity->column_fields ['progress'] = $theProgress;
				if ($theProgress >= 100) {
					$entity->column_fields ['due_date']    = $today;
					$entity->column_fields ['time_end']    = $time;
					$entity->column_fields ['eventstatus'] = 'Held';
				}
				$entity->save ('Calendar');
				$activityId = $entity->id;
				if ($isNewTask) {
					$this->adb->pquery (
						'INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)',
						array($reportJob['reported_work'][$j], $activityId)
					);
				}
				$activityIds[$j] = $activityId;
				unset ($entity);
			}
			return $activityIds;
		}
		
		/**
		 * @param array $reportJob
		 * @return array
		 *
		 * @throws WebServiceException
		 */
		private function updateWorkRecords ($reportJob) {
			$reportTitles = array ();
			$totalJobs    = count ($reportJob);
			$arm          = ActivityReportManager::getInstance ($this->adb);
			for ($j = 0; $j < $totalJobs; $j++) {
				if (empty ($reportJob['reported_work'][$j])) {
					continue;
				}
				$arm->updateTaskByJob (
					$reportJob['reported_work'][$j],
					floatval ($reportJob['total_progress'][$j]),
					'En curso'
				);
				$calculatedProgress  = $arm->calculateProgress ($reportJob['reported_work'][$j]);
				$entityJob           = CRMEntity::getInstance (self::JOB_MODULE_NAME);
				$entityJob->id       = $reportJob['reported_work'][$j];
				$entityJob->mode     = 'edit';
				$entityJob->retrieve_entity_info ($reportJob['reported_work'][$j], self::JOB_MODULE_NAME);
				
				// DEBUG: Log valores antes de actualizar
				$currentProgress = isset($entityJob->column_fields['overall_progress_perc']) ? floatval($entityJob->column_fields['overall_progress_perc']) : 0;
				
				if (floatval ($calculatedProgress) >= 100 && $entityJob->column_fields ['estado_de_la_orden'] != 'Terminado') {
					$entityJob->column_fields ['estado_de_la_orden'] = 'Terminado';
				} else if (
					(floatval ($calculatedProgress) < 100) &&
					(
						($entityJob->column_fields ['estado_de_la_orden'] == 'Definido') ||
						($entityJob->column_fields ['estado_de_la_orden'] == 'Programado')
					)
				) {
					$entityJob->column_fields ['estado_de_la_orden'] = 'En curso';
				}
				$jobTitle = substr ($entityJob->column_fields ['titulo'], 0, 80);
				$reportTitles[$j] = $jobTitle;
				$entityJob->column_fields ['overall_progress_perc'] = floatval ($calculatedProgress);
	
				$entityJob->save ('orden_de_trabajo');
				$entityJob->retrieve_entity_info ($reportJob['reported_work'][$j], self::JOB_MODULE_NAME);
				$savedProgress = isset($entityJob->column_fields['overall_progress_perc']) ? floatval($entityJob->column_fields['overall_progress_perc']) : 0;
				unset ($entityJob);
			}
			return $reportTitles;
		}
		
		/**
		 * @param array $reportJob
		 * @param integer $crmId
		 * @param string $mode
		 *
		 * @return void
		 */
		private function saveGlobalReportData ($reportJob, $crmId, $mode) {
			if ($mode == 'edit') {
				return;
			}
			$this->adb->pquery ('DELETE FROM vtiger_seadaily_reportrel WHERE daily_reportid=?', array ($crmId));
			$totalJobs  = count ($reportJob);
			for ($j = 0; $j < $totalJobs; $j++) {
				if (empty ($reportJob['reported_work'][$j])) {
					continue;
				}
				$this->adb->pquery (
					'INSERT IGNORE INTO vtiger_seadaily_reportrel (daily_reportid, orden_de_trabajoid) VALUES (?, ?)',
					array($crmId, $reportJob['reported_work'][$j])
				);
			}
		}
		
		public function __construct($adb) {
			$this->masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$this->adb       = $adb;
		}
		
		/**
		 * @param integer$userId
		 * @param integer $crmId
		 *
		 * @return void
		 * @throws WebServiceException
		 */
		public function buildProgressOfWork ($userId, $crmId, $mode) {
			if (empty ($userId) || empty($crmId)) {
				throw new Exception ('Uoops! algo salio mal, intenta de nuevo');
			} else if (empty ($_REQUEST['report_job'])) {
				throw new Exception ('No hay trabajos reportados');
			}
			$reportDate = isset ($_REQUEST['daily_report_date']) ? getValidDBInsertDateValue ($_REQUEST['daily_report_date']) : date ('Y-m-d');
			$jobTitles       = $this->updateWorkRecords ($_REQUEST['report_job']);
			$activityIds     = $this->saveActivityTasks ($_REQUEST['report_job'], $jobTitles, $userId);
			$activityReports = $this->saveActivityReports ($_REQUEST['report_job'], $activityIds, $jobTitles, $userId, $reportDate);
			$this->saveGlobalReportData ($_REQUEST['report_job'], $crmId, $mode);
		}
		
		/**
		 * @param integer $crmId
		 * @param Users $user
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function fetchGlobalReportData ($crmId, $user) {
			if (empty ($crmId)) {
				return null;
			}
			$numberingHelper = NumberHelper::getInstance ($this->adb, $user);
			$results = $this->adb->pquery (
				'SELECT
				    ot.orden_de_trabajoid,
				    ot.overall_progress_perc,
       				CONCAT(ot.cod_orden_de_tra,": ",ot.titulo) AS title,
					ot.numero_unidades_planificadas AS estimated_time,
				    ar.report,
				    ar.duration_time,
				    ar.progress
				FROM
					vtiger_orden_de_trabajo ot
				INNER JOIN vtiger_crmentity crm ON crm.crmid = ot.orden_de_trabajoid AND crm.deleted= 0
				INNER JOIN vtiger_seadaily_reportrel sead ON sead.orden_de_trabajoid = ot.orden_de_trabajoid
				INNER JOIN vtiger_daily_report dr ON dr.daily_reportid = sead.daily_reportid
				INNER JOIN vtiger_seactivityrel seaa ON seaa.crmid = ot.orden_de_trabajoid
				INNER JOIN vtiger_activity act ON act.activityid = seaa.activityid
				INNER JOIN vtiger_activity_report ar ON ar.activityid = act.activityid AND ar.report_on=? AND ar.deleted = 0
				WHERE
					DATE(ar.reportdate) = DATE(dr.daily_report_date) AND
				    sead.daily_reportid=?',
				array ('JOB', $crmId)
			);
			if ($this->adb->num_rows ($results) > 0) {
				$globalReportData = array ();
				$dra = DailyReportActivities::getInstance ($this->adb);
				while ($row = $this->adb->fetchByAssoc ($results)) {
					
					$row ['attachments']           = $dra->fetchAttachments ($row['orden_de_trabajoid']);
					$row ['sum_estimated_time']    = $row ['estimated_time'];
					$row ['sum_duration_time']     = $row ['duration_time'];
					$row ['estimated_time']        = $numberingHelper->setNumberFormat ($row['estimated_time']);
					$row ['overall_progress_perc'] = $numberingHelper->setNumberFormat ($row['overall_progress_perc']);
					$row ['duration_time']         = $numberingHelper->setNumberFormat ($row['duration_time']);
					$row ['progress']              = $numberingHelper->setNumberFormat ($row['progress']);
					$globalReportData [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($globalReportData)) ? $globalReportData : null;
		}
		
		/**
				 * @param Users $user
				 *
				 * @return array|null
				 * @throws Exception
				 */
		public function fetchUnfinishedWorks ($user) {
			if (!$user instanceof Users) {
				return null;
			}
			$numberingHelper = NumberHelper::getInstance ($this->adb, $user);
			if ($user->is_admin == 'on') {
				$whereUser = '';
			} else {
				$focus = new GetUserGroups();
				$focus->getAllUserGroups ($user->id);
				$groupIds   = (count ($focus->user_groups)) ? $this->adb->sql_expr_datalist ($focus->user_groups) : "('{$user->id}')";
				$whereUser = "  AND (crm.smcreatorid = {$user->id} OR crm.smownerid IN {$groupIds})";
			}
			
			$results = $this->adb->pquery (
				"SELECT
						ot.orden_de_trabajoid,
						ot.cod_orden_de_tra,
		       			ot.titulo,
		       			ot.numero_unidades_planificadas AS estimated_time,
		       			ot.overall_progress_perc AS progress_perc
					FROM vtiger_orden_de_trabajo ot
					INNER JOIN vtiger_crmentity crm ON ot.orden_de_trabajoid = crm.crmid
					WHERE
						crm.deleted=? AND
						ot.estado_de_la_orden!=?
						{$whereUser}",
				array (0, 'Terminado')
			);
			
			if ($this->adb->num_rows ($results) > 0) {
				$unfinishedJobs = array ();
				while ($row = $this->adb->fetchByAssoc ($results)) {
					if (
						(isPermitted (self::JOB_MODULE_NAME, 'EditView', $row['orden_de_trabajoid']) == 'yes') ||
						(isPermitted (self::JOB_MODULE_NAME, 'DetailView', $row['orden_de_trabajoid']) == 'yes')
					) {
						$row['estimated_time'] = $numberingHelper->setNumberFormat ($row['estimated_time'], 'estimated_time');
						$row['progress_perc']  = $numberingHelper->setNumberFormat ($row['progress_perc'], 'progress_perc');
						$unfinishedJobs [] = $row;
					}
					
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($unfinishedJobs)) ? $unfinishedJobs : null;
		}
		
		/**
		 * @param $crmId
		 * @param string|null $view
		 * @param $currentUser
		 * @param $appFieldParameters
		 *
		 * @return string
		 */
		public function run ($crmId, $view = null, $currentUser, $appFieldParameters = null) {
			return null;
			require('modules/daily_report/language/es_es.lang.php');
			
			if (empty ($view)) {
				$html = 'modules/DailyReport/progressOfWorkEditView.tpl';
			} else {
				$globalReport = $this->fetchGlobalReportData ($crmId, $currentUser);
				if (empty ($globalReport)) {
					return null;
				}
				$html = 'modules/DailyReport/progressOfWorkDetailView.tpl';
			}
			$unfinishedJobs = $this->fetchUnfinishedWorks ($currentUser);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('GLOBAL_REPORT', isset ($globalReport) ? $globalReport : null);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('UNFINISHED_JOBS', $unfinishedJobs);
			$smarty->assign ('VIEW', $view);
			return $smarty->fetch ($html);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @return progressOfWork
		 */
		public static function getInstance (PearDatabase $adb) {
					return new self ($adb);
		}
	
	}

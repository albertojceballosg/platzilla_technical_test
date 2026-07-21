<?php
	require_once ('include/platzilla/Data/ActivityFeedbackManager.php');
	require_once ('include/platzilla/Data/ActivityReport.php');
	require_once ('include/platzilla/Data/TaskActivity.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/database/PearDatabase_Fix.php');

	class ActivityReportManager	{

		/** @var ActivityReportManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return float|int
		 * @throws Exception
		 */
		public function calculateProgress ($crmId) {
			if (empty ($crmId)) {
				return 0;
			}
			
			// Primero verificar si hay tareas con progress_weighting_factor definido
			$resultWeighted = $this->adb->pquery (
				'SELECT 
						a.activityid,
						a.progress,
						a.progress_weighting_factor,
						a.estimated_time
					FROM
						vtiger_activity a
					INNER JOIN vtiger_crmentity crm ON crm.crmid = a.activityid AND crm.deleted=0
					INNER JOIN vtiger_seactivityrel sa ON sa.activityid = a.activityid
					WHERE
						sa.crmid=? AND
						a.activitytype !=?',
				array ($crmId, 'Job')
			);
			
			$tasksWithWeighting = array();
			$tasksWithoutWeighting = array();
			$totalEstimatedTimeWithoutWeighting = 0;
			
			if ($this->adb->num_rows ($resultWeighted) > 0) {
				while ($row = $this->adb->fetchByAssoc ($resultWeighted, -1, false)) {
					if (!empty($row['progress_weighting_factor']) && floatval($row['progress_weighting_factor']) > 0) {
						$tasksWithWeighting[] = array(
							'progress' => floatval($row['progress']),
							'weighting_factor' => floatval($row['progress_weighting_factor'])
						);
					} else {
						$tasksWithoutWeighting[] = array(
							'progress' => floatval($row['progress']),
							'estimated_time' => floatval($row['estimated_time'])
						);
						$totalEstimatedTimeWithoutWeighting += floatval($row['estimated_time']);
					}
				}
			}
			DatabaseUtils::closeResult ($resultWeighted);
			$resultWeighted = null;
			
			$progress = 0;
			
			// Calcular progreso de tareas CON factor de ponderación
			$weightedProgress = 0;
			$totalWeightingUsed = 0;
			foreach ($tasksWithWeighting as $task) {
				// El factor de ponderación indica qué % del progreso total representa esta tarea
				// progress de la tarea (0-100) * factor de ponderación (0-100) / 100
				$weightedProgress += ($task['progress'] * $task['weighting_factor']) / 100;
				$totalWeightingUsed += $task['weighting_factor'];
			}
			
			// Calcular progreso de tareas SIN factor de ponderación (método tradicional)
			// Estas tareas comparten el porcentaje restante (100 - totalWeightingUsed)
			$remainingPercentage = 100 - $totalWeightingUsed;
			$unweightedProgress = 0;
			
			if ($remainingPercentage > 0 && $totalEstimatedTimeWithoutWeighting > 0) {
				$sumProgressTime = 0;
				foreach ($tasksWithoutWeighting as $task) {
					$sumProgressTime += $task['progress'] * $task['estimated_time'];
				}
				// Progreso proporcional de las tareas sin ponderación
				$avgProgressUnweighted = $sumProgressTime / $totalEstimatedTimeWithoutWeighting;
				// Escalar al porcentaje restante
				$unweightedProgress = ($avgProgressUnweighted * $remainingPercentage) / 100;
			} elseif ($remainingPercentage > 0 && count($tasksWithoutWeighting) > 0) {
				// Si no hay estimated_time pero hay tareas, usar promedio simple
				$sumProgress = 0;
				foreach ($tasksWithoutWeighting as $task) {
					$sumProgress += $task['progress'];
				}
				$avgProgressUnweighted = $sumProgress / count($tasksWithoutWeighting);
				$unweightedProgress = ($avgProgressUnweighted * $remainingPercentage) / 100;
			}
			
			// Progreso total = suma de ambos componentes
			$progress = $weightedProgress + $unweightedProgress;
			$progress = ($progress > 100) ? 100 : $progress;
			
			return $progress;
		}
		
		/**
		 * @param integer $entityId
		 *
		 * @return ActivityReport[]|null
		 * @throws Exception
		 */
		public function fetchActivityReport ($entityId) {
			if (empty($entityId)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT DISTINCT 
						ar.*,
						a.eventstatus,
						a.estimated_time_unit,
						a.subject as activity_subject,
						u.id AS userid,
						CONCAT(u.first_name, " ", u.last_name) AS username
					  FROM
						vtiger_activity_report ar 
					  INNER JOIN vtiger_seactivityrel sa ON sa.activityid = ar.activityid
					  INNER JOIN vtiger_activity a ON a.activityid = ar.activityid
					  INNER JOIN vtiger_crmentity crm ON  crm.crmid = sa.crmid  AND crm.deleted=0
					  INNER JOIN vtiger_users u ON u.id = ar.userid
					  WHERE
					  	crm.crmid=? AND
					  	ar.deleted = 0
					  ORDER BY ar.reportdate DESC',
				array ($entityId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$dummy  = explode('_', $this->adb->dbName);
				$dbName = $dummy[ 2 ];
				$rootFolderPath  = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$activityReports = array ();
				$afm             = ActivityFeedbackManager::getInstance ($this->adb);
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$avatarUri = "{$dbName}/user_images/Avatar_{$row ['userid']}.png";
					if (!file_exists ("{$rootFolderPath}/{$avatarUri}")) {
						$avatarUri = 'themes/centaurus/img/photo.png';
					}
					$activityReports [] = ActivityReport::getInstance()
						->setId ($row ['activityreportid'])
						->setIsHeld (($row['eventstatus'] == 'Held') ? true : false)
						->setActivityId ($row ['activityid'])
						->setFeedbacks ($afm->fetchFeedbackByReport ($row ['activityreportid']))
						->setProgress (floatval ($row ['progress']))
						->setReport ($row ['report'])
						->setReportDate ($row ['reportdate'])
						->setActivityReportDate (isset($row['activity_report_date']) ? $row['activity_report_date'] : null)
						->setTimeDuration (floatval ($row ['duration_time']))
						->setActualCost (isset($row['actual_cost']) ? floatval($row['actual_cost']) : null)
						->setEstimatedTimeUnit (isset($row['estimated_time_unit']) ? $row['estimated_time_unit'] : 'Hora')
						->setTitle (!empty($row['title']) ? $row['title'] : $row['activity_subject'])
						->setUserAvatar ($avatarUri)
						->setUserId ($row ['userid'])
						->setUserName ($row ['username']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityReports)) ? $activityReports : null;
		}
		
		/**
		 * @param integer $taskId
		 * @param array|null $period
		 *
		 * @return null|ActivityReport[]
		 * @throws Exception
		 */
		public function fetchActivityReportByActivityId ($taskId, $period = null, $includeJobReports = false) {
			if (empty($taskId)) {
				return null;
			}
			$wherePeriod = '';
			if (!empty ($period['startdate']) && !empty ($period['enddate'])) {
				$wherePeriod = "DATE(activity_report_date) BETWEEN DATE('{$period ['startdate']}') AND DATE('{$period ['enddate']}') AND";
			}
			$whereReportOn = $includeJobReports ? '' : "AND (ar.report_on IS NULL OR ar.report_on != 'JOB')";
			$sql = "SELECT ar.*, a.subject as activity_subject 
					FROM vtiger_activity_report ar 
					LEFT JOIN vtiger_activity a ON ar.activityid = a.activityid 
					WHERE {$wherePeriod} ar.activityid={$taskId} AND ar.deleted = 0 {$whereReportOn}
					ORDER BY ISNULL(ar.activity_report_date) ASC, ar.activity_report_date ASC, ar.reportdate ASC";
			
			$result = $this->adb->query ($sql);
			$numRows = $this->adb->num_rows ($result);
		
			if ($numRows > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$title = !empty($row['title']) ? $row['title'] : $row['activity_subject'];
					
					$activityReport[] = ActivityReport::getInstance ()
						->setId ($row ['activityreportid'])
						->setActivityId ($row ['activityid'])
						->setProgress (floatval ($row ['progress']))
						->setReport ($row ['report'])
						->setReportDate ($row ['reportdate'])
						->setActivityReportDate (isset($row['activity_report_date']) ? $row['activity_report_date'] : null)
						->setTimeDuration (floatval ($row ['duration_time']))
						->setActualCost (isset($row['actual_cost']) ? floatval($row['actual_cost']) : 0)
						->setTitle ($title)
						->setUserId (isset($row['userid']) ? $row['userid'] : null)
						->setUserAvatar (null)
						->setUserName (null);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityReport)) ? $activityReport : null;
		
		}
		
		/**
		 * @param string $groupsIds
		 *
		 * @return null|ActivityReport[]
		 * @throws Exception
		 */
		public function fetchActivityReportByGroup ($groupsIds) {
			if (empty($groupsIds)) {
				return null;
			}
			$ids    = explode (',', $groupsIds);
			$gIds   = $this->adb->sql_expr_datalist($ids);
			$result = $this->adb->query ("SELECT ar.*, a.subject as activity_subject 
										FROM vtiger_activity_report ar 
										LEFT JOIN vtiger_activity a ON ar.activityid = a.activityid 
										WHERE ar.deleted = 0 AND ar.activityreportid IN {$gIds}"	);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$activityReport[] = ActivityReport::getInstance ()
						->setId ($row ['activityreportid'])
						->setActivityId ($row ['activityid'])
						->setProgress (floatval ($row ['progress']))
						->setReport (trim ($row ['report']))
						->setReportDate ($row ['reportdate'])
						->setTimeDuration (floatval ($row ['duration_time']))
						->setTitle (!empty($row['title']) ? $row['title'] : $row['activity_subject'])
						->setUserAvatar (null)
						->setUserId (null)
						->setUserName (null);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityReport)) ? $activityReport : null;
			
		}
		
		/**
		 * @param integer $reportId
		 * @param integer $activityId [Opcional] ID de actividad para validar que el reporte pertenezca a la actividad correcta
		 * @param integer $userId [Opcional] ID de usuario para validar permisos
		 *
		 * @return ActivityReport|null
		 * @throws Exception
		 */
		public function fetchActivityReportById ($reportId, $activityId = null, $userId = null) {
			if (empty($reportId)) {
				return null;
			}
			
			// Construir consulta base
			$sql = 'SELECT DISTINCT 
						ar.*,
						a.subject as activity_subject,
						CONCAT(u.first_name, " ", u.last_name) AS username
					  FROM
						vtiger_activity_report ar 
					  INNER JOIN vtiger_seactivityrel sa ON sa.activityid = ar.activityid
					  INNER JOIN vtiger_crmentity crm ON  crm.crmid = sa.crmid  AND crm.deleted=0
					  INNER JOIN vtiger_activity a ON a.activityid = ar.activityid
					  INNER JOIN vtiger_users u ON u.id = ar.userid
					  WHERE ar.activityreportid=? AND ar.deleted = 0';
			
			$params = array($reportId);
			
			// Agregar validación de activityId si se proporciona
			if (!empty($activityId)) {
				$sql .= ' AND ar.activityid = ?';
				$params[] = $activityId;
			}
			
			// Agregar validación de userId si se proporciona (para permisos)
			if (!empty($userId)) {
				$sql .= ' AND ar.userid = ?';
				$params[] = $userId;
			}
			
						
			$result = $this->adb->pquery ($sql, $params);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$rootFolderPath  = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$avatarUri = "{$this->adb->dbName}/user_images/Avatar_{$row ['userid']}.png";
				if (!file_exists ("{$rootFolderPath}/{$avatarUri}")) {
					$avatarUri = 'themes/centaurus/img/photo.png';
				}
				$activityReport = ActivityReport::getInstance()
					->setId ($row ['activityreportid'])
					->setActivityId ($row ['activityid'])
					->setProgress (floatval ($row ['progress']))
					->setReport ($row ['report'])
					->setReportDate ($row ['reportdate'])
					->setActivityReportDate (isset($row['activity_report_date']) ? $row['activity_report_date'] : null)
					->setReportOn ($row ['report_on'])
					->setTimeDuration (floatval ($row ['duration_time']))
					->setActualCost (isset($row['actual_cost']) ? floatval($row['actual_cost']) : null)
					->setTitle (!empty($row['title']) ? $row['title'] : $row['activity_subject'])
					->setUserAvatar ($avatarUri)
					->setUserId ($row ['userid'])
					->setUserName ($row ['username']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityReport)) ? $activityReport : null;
		}
		
		/**
		 * @param integer $taskId
		 *
		 * @return null|TaskActivity
		 * @throws Exception
		 */
		public function fetchActivityTaskById ($taskId) {
			if (empty($taskId)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT
						ats.*,
						(SELECT setype FROM vtiger_crmentity WHERE crmid=ats.related_id) AS related_module,
						(IF ((SELECT setype FROM vtiger_crmentity WHERE crmid=ats.related_id)=?, (SELECT titulo FROM vtiger_orden_de_trabajo WHERE orden_de_trabajoid=ats.related_id), NULL)) AS relate_title
					   FROM
						vtiger_activity ats
					  INNER JOIN vtiger_crmentity crm ON  crm.crmid = ats.activityid  AND crm.deleted=0
					  WHERE ats.activityid=?',
				array ('orden_de_trabajo', $taskId)
			);
			
			if ($this->adb->num_rows ($result) > 0) {
				$numberingHelper = NumberHelper::getInstance ($this->adb);
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$activitytask = TaskActivity::getInstance ()
					->setActivityId ($row ['activityid'])
					->setProgress ($numberingHelper->setNumberFormat ($row ['progress']))
					->setRelatedId (intval ($row ['related_id']))
					->setRelatedModule ($row ['related_module'])
					->setRelatedTitle ($row ['relate_title'])
					->setTimeDuration ($numberingHelper->setNumberFormat ($row ['estimated_time']))
					->setStatus ($row ['eventstatus'])
					->setDueDate ($row ['due_date'])
					->setDurationHours ($row ['duration_hours'])
					->setActivityCondition ($row ['planned_task'])
					->setActivityType ($row ['activitytype'])
					->setImportance ($row ['importance'])
					->setPriority ($row ['priority'])
					->setStartDate ($row ['date_start'])
					->setSubject ($row ['subject']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activitytask)) ? $activitytask : null;
		}
		
		/**
		 * @param $feedBackId
		 *
		 * @return ActivityReport[]|null
		 * @throws Exception
		 */
		public function fetchReportByIFeedback ($feedBackId) {
			if (empty($feedBackId)) {
				return null;
			}
			
			$result = $this->adb->pquery ('SELECT DISTINCT activityreportid FROM vtiger_activity_report2feedback WHERE activityreportid=?', array ($feedBackId));
			if ($this->adb->num_rows ($result) > 0) {
				$activityReports = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$activityReports [] = $this->fetchActivityReportById ($row ['activityreportid']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityReports)) ? $activityReports : null;
		}
		
		/**
		 * @param integer $entityId
		 * @param integer $userId
		 *
		 * @return ActivityReport[]|null
		 * @throws Exception
		 */
		public function fetchActivityReportByUser ($entityId, $userId) {
			if (empty($entityId) || empty($userId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT DISTINCT
						ar.*,
						a.progress,
						CONCAT(u.first_name, " ", u.last_name) AS username
					  FROM
						vtiger_activity_report ar 
					  INNER JOIN vtiger_seactivityrel sa ON sa.activityid = ar.activityid
					  INNER JOIN vtiger_activity a ON a.activityid = ar.activityid
					  INNER JOIN vtiger_crmentity crm ON  crm.crmid = sa.crmid AND crm.deleted=0
					  INNER JOIN vtiger_users u ON u.id = ar.userid
					  WHERE 
					  	crm.crmid=? AND 
					  	ar.userid=? AND
					  	ar.deleted = 0',
				array ($entityId, $userId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$rootFolderPath  = PlatzillaUtils::getPlatzillaRootFolderPath ();
				$activityReports = array ();
				$afm = ActivityFeedbackManager::getInstance ($this->adb);
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$avatarUri = "{$this->adb->dbName}/user_images/Avatar_{$row ['userid']}.png";
					if (!file_exists ("{$rootFolderPath}/{$avatarUri}")) {
						$avatarUri = 'themes/centaurus/img/photo.png';
					}
					$activityReports [] = ActivityReport::getInstance()
						->setId ($row ['activityreportid'])
						->setActivityId ($row ['activityid'])
						->setFeedbacks ($afm->fetchFeedbackByReport ($row ['activityreportid']))
						->setProgress (floatval ($row ['progress']))
						->setReport ($row ['report'])
						->setReportOn ($row ['report_on'])
						->setReportDate ($row ['reportdate'])
						->setTitle ($row ['title'])
						->setUserAvatar ($avatarUri)
						->setUserId ($row ['userid'])
						->setUserName ($row ['username']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityReports)) ? $activityReports : null;
		}
		
		/**
		 * @param array $row
		 * @param integer $line
		 *
		 * @return string
		 */
		 /*
		public function getReportLine ($row, $line) {
			$myReport   = trim($row ['report']);
			$dummy      = explode ('>', $myReport, 2);
			$dummy      = (count ($dummy) > 1) ? explode ('<', $dummy[1], 2) : $dummy;
			$lineReport = (!preg_match ('/^(R\d+-)+$/', $dummy[0])) ? "R{$line}-{$dummy[0]}" : '';
			$myReport   = "<p>{$lineReport}</p><!--{$row ['activityreportid']}-->";
			return $myReport;
		}
	*/	
		/**
		 * @param integer $jobId
		 *
		 * @return integer|null
		 * @throws Exception		 
		 */
		public function getTaskFromJobId ($jobId) {
			if (empty($jobId)) {
				return null;
			}
			$result = $this->adb->pquery (
				'SELECT
						sa.activityid
					FROM
						vtiger_seactivityrel sa
					INNER JOIN vtiger_activity a ON a.activityid = sa.activityid
					INNER JOIN vtiger_crmentity crm ON crm.crmid = a.activityid AND crm.deleted=0
					WHERE
						sa.crmid=? AND
						a.activitytype=?
					LIMIT 1',
				array ($jobId, 'Job')
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$activityId = $row['activityid'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($activityId)) ? $activityId : null;
		}
		
		/**
		 * @param ActivityReport $activityReport
		 *
		 * @return ActivityReport|null
		 * @throws Exception
		 */
		public function saveActivityReport ($activityReport) {
			if (!$activityReport instanceof ActivityReport) {
				throw new Exception ('Se ha presentado un error, por favor intente mas tarde');
			}

			$isUpdate = !empty ($activityReport->getId ());

			if (empty ($activityReport->getId ())) {
				$this->adb->pquery (
					'INSERT INTO vtiger_activity_report (activityid, userid, report, report_on, title, activity_report_date, duration_time, progress, actual_cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array (
						$activityReport->getActivityId (),
						$activityReport->getUserId (),
						$activityReport->getReport (),
						$activityReport->getReportOn (),
						$activityReport->getTitle (),
						$activityReport->getActivityReportDate (),
						$activityReport->getTimeDuration (),
						$activityReport->getProgress (),
						$activityReport->getActualCost ()
					)
				);
				$activityReport->setId ($this->adb->getLastInsertID ());
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_activity_report SET activityid=?, userid=?, report=?, report_on=?, title=?, activity_report_date=?, duration_time=?, progress=?, actual_cost=?, modificated_date=NOW() WHERE activityreportid=?',
					array (
						$activityReport->getActivityId (),
						$activityReport->getUserId (),
						$activityReport->getReport (),
						$activityReport->getReportOn (),
						$activityReport->getTitle (),
						$activityReport->getActivityReportDate (),
						$activityReport->getTimeDuration (),
						$activityReport->getProgress (),
						$activityReport->getActualCost (),
						$activityReport->getId ()
					)
				);
			}

			// Sincronizar contadores y progress_ratio en vtiger_activity
			$this->syncActivityCounters($activityReport->getActivityId());

			// FIX: Llamar procedure para recalcular campos calculados (evita triggers)
			$this->adb->pquery(
				'CALL sp_update_activity_progress_clean(?, ?)',
				array($activityReport->getActivityId(), true)
			);
			pearDatabase_FlushResults($this->adb);

			return $activityReport;
		}

		/**
		 * Sincroniza los contadores reports, feedbacks y progress_ratio en vtiger_activity
		 * después de guardar un reporte de avance
		 *
		 * @param integer $activityId ID de la actividad (tarea)
		 * @return void
		 */
		private function syncActivityCounters($activityId) {
			if (empty($activityId)) {
				return;
			}

			// Contar reportes para esta actividad (excluyendo eliminados)
			$reportsResult = $this->adb->pquery(
				'SELECT COUNT(*) as report_count FROM vtiger_activity_report WHERE activityid = ? AND (deleted = 0 OR deleted IS NULL)',
				array($activityId)
			);
			$reportCount = 0;
			if ($this->adb->num_rows($reportsResult) > 0) {
				$reportCount = intval($this->adb->query_result($reportsResult, 0, 'report_count'));
			}
			DatabaseUtils::closeResult($reportsResult);

			// Contar feedbacks para esta actividad (solo de reportes no eliminados)
			$feedbacksResult = $this->adb->pquery(
				'SELECT COUNT(DISTINCT af.activityfeedbackid) as feedback_count
				 FROM vtiger_activity_feedback af
				 INNER JOIN vtiger_activity_report2feedback ar2f ON ar2f.activityfeedbackid = af.activityfeedbackid
				 INNER JOIN vtiger_activity_report ar ON ar.activityreportid = ar2f.activityreportid
				 WHERE ar.activityid = ? AND (ar.deleted = 0 OR ar.deleted IS NULL)',
				array($activityId)
			);
			$feedbackCount = 0;
			if ($this->adb->num_rows($feedbacksResult) > 0) {
				$feedbackCount = intval($this->adb->query_result($feedbacksResult, 0, 'feedback_count'));
			}
			DatabaseUtils::closeResult($feedbacksResult);

			// Obtener estimated_time de la tarea para calcular progress_ratio
			$taskResult = $this->adb->pquery(
				'SELECT estimated_time FROM vtiger_activity WHERE activityid = ?',
				array($activityId)
			);
			$estimatedTime = 0;
			if ($this->adb->num_rows($taskResult) > 0) {
				$estimatedTime = floatval($this->adb->query_result($taskResult, 0, 'estimated_time'));
			}
			DatabaseUtils::closeResult($taskResult);

			// Calcular total de unidades ejecutadas (sum of duration_time from reports no eliminados)
			$durationResult = $this->adb->pquery(
				'SELECT SUM(duration_time) as total_duration FROM vtiger_activity_report WHERE activityid = ? AND (deleted = 0 OR deleted IS NULL)',
				array($activityId)
			);
			$totalDuration = 0;
			if ($this->adb->num_rows($durationResult) > 0) {
				$totalDuration = floatval($this->adb->query_result($durationResult, 0, 'total_duration'));
			}
			DatabaseUtils::closeResult($durationResult);

			// Calcular progress_ratio: porcentaje acumulado de unidades ejecutadas frente al total programado
			$progressRatio = 0;
			if ($estimatedTime > 0) {
				$progressRatio = ($totalDuration / $estimatedTime) * 100;
				$progressRatio = min($progressRatio, 100); // Limitar a 100%
			}

			// Actualizar vtiger_activity con los nuevos valores
			$this->adb->pquery(
				'UPDATE vtiger_activity SET reports = ?, feedbacks = ?, progress_ratio = ? WHERE activityid = ?',
				array($reportCount, $feedbackCount, $progressRatio, $activityId)
			);
		}
		
		/**
		 * @param integer $jobId
		 * @param float $progress
		 * @param string $status
		 *
		 * @return void
		 */
		public function updateTaskByJob ($jobId, $progress, $status) {
			if (empty ($jobId) || empty ($status)) {
				return;
			}
			$today                    = date ('Y-m-d H:i:s');
			list ($dueDate, $timeEnd) = explode (' ', $today);
			$result = $this->adb->pquery (
				'SELECT
			       		sa.activityid
					FROM
						vtiger_seactivityrel sa
					INNER JOIN vtiger_activity a ON a.activityid = sa.activityid
					INNER JOIN vtiger_crmentity crm ON crm.crmid = a.activityid AND crm.deleted=0
					WHERE
						sa.crmid=? AND
						a.activitytype!=?',
				array ($jobId, 'Job')
			);
			
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if (($status == 'Terminado') || ($progress >= 100)) {
						$this->adb->pquery (
							'UPDATE vtiger_activity
								SET progress=?,
								    eventstatus=?,
								    due_date=?,
								    time_end=?
								WHERE activityid=?',
							array ($progress, 'Held', $dueDate, $timeEnd, $row ['activityid']));
					} else {
						$this->adb->pquery (
							'UPDATE vtiger_activity
								SET progress= IF(eventstatus!=?, ?, progress)
								WHERE activityid=?',
							array ('Held', $progress, $row ['activityid'])
						);
						$this->adb->pquery (
							'UPDATE vtiger_activity
								SET eventstatus= IF((eventstatus=? AND progress > 0 AND progress < 100), ?, eventstatus)
								WHERE activityid=?',
							array ('Planned', 'Not Held', $row ['activityid'])
						);
					}
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ActivityReportManager|mixed
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
		
		/**
		 * Calcula el costo total (estimado y real) de una entidad (proyecto, orden de trabajo, etc.)
		 * 
		 * @param integer $crmId ID de la entidad
		 * @return array Array con estimated_cost, actual_cost y variance
		 */
		public function calculateEntityCost($crmId) {
			if (empty($crmId)) {
				return array(
					'estimated_cost' => 0,
					'actual_cost' => 0,
					'variance' => 0
				);
			}
			
			$result = $this->adb->pquery(
				'SELECT 
					SUM(a.estimated_cost) as total_estimated,
					(SELECT SUM(ar.actual_cost) 
					 FROM vtiger_activity_report ar
					 INNER JOIN vtiger_activity a2 ON a2.activityid = ar.activityid
					 INNER JOIN vtiger_seactivityrel sa2 ON sa2.activityid = a2.activityid
					 WHERE sa2.crmid = ? AND ar.deleted = 0) as total_actual
				FROM 
					vtiger_activity a
					INNER JOIN vtiger_seactivityrel sa ON sa.activityid = a.activityid
					INNER JOIN vtiger_crmentity crm ON crm.crmid = a.activityid AND crm.deleted = 0
				WHERE 
					sa.crmid = ?',
				array($crmId, $crmId)
			);
			
			if ($this->adb->num_rows($result) > 0) {
				$row = $this->adb->fetchByAssoc($result, -1, false);
				$estimatedCost = !empty($row['total_estimated']) ? floatval($row['total_estimated']) : 0;
				$actualCost = !empty($row['total_actual']) ? floatval($row['total_actual']) : 0;
				$variance = $estimatedCost - $actualCost;
				
				return array(
					'estimated_cost' => $estimatedCost,
					'actual_cost' => $actualCost,
					'variance' => $variance
				);
			}
			
			return array(
				'estimated_cost' => 0,
				'actual_cost' => 0,
				'variance' => 0
			);
		}
		
		/**
		 * Calcula el progreso esperado de una entidad basado en el progreso estimado de las tareas
		 * Similar a calculateProgress pero usa estimated_progress en lugar de progress
		 * 
		 * @param integer $crmId ID de la entidad (orden de trabajo, proyecto, etc.)
		 *
		 * @return float|int Porcentaje de progreso esperado (0-100)
		 * @throws Exception
		 */
		public function calculateExpectedProgress ($crmId) {
			if (empty ($crmId)) {
				return 0;
			}
			
			// Obtener tareas con sus valores de estimated_progress y progress_weighting_factor
			$resultWeighted = $this->adb->pquery (
				'SELECT 
						a.activityid,
						a.estimated_progress,
						a.progress_weighting_factor,
						a.estimated_time
					FROM
						vtiger_activity a
					INNER JOIN vtiger_crmentity crm ON crm.crmid = a.activityid AND crm.deleted=0
					INNER JOIN vtiger_seactivityrel sa ON sa.activityid = a.activityid
					WHERE
						sa.crmid=? AND
						a.activitytype !=?',
				array ($crmId, 'Job')
			);
			
			$tasksWithWeighting = array();
			$tasksWithoutWeighting = array();
			$totalEstimatedTimeWithoutWeighting = 0;
			
			if ($this->adb->num_rows ($resultWeighted) > 0) {
				while ($row = $this->adb->fetchByAssoc ($resultWeighted, -1, false)) {
					if (!empty($row['progress_weighting_factor']) && floatval($row['progress_weighting_factor']) > 0) {
						$tasksWithWeighting[] = array(
							'estimated_progress' => floatval($row['estimated_progress']),
							'weighting_factor' => floatval($row['progress_weighting_factor'])
						);
					} else {
						$tasksWithoutWeighting[] = array(
							'estimated_progress' => floatval($row['estimated_progress']),
							'estimated_time' => floatval($row['estimated_time'])
						);
						$totalEstimatedTimeWithoutWeighting += floatval($row['estimated_time']);
					}
				}
			}
			DatabaseUtils::closeResult ($resultWeighted);
			$resultWeighted = null;
			
			$expectedProgress = 0;
			
			// Calcular progreso esperado de tareas CON factor de ponderación
			$weightedProgress = 0;
			$totalWeightingUsed = 0;
			foreach ($tasksWithWeighting as $task) {
				// El factor de ponderación indica qué % del progreso esperado total representa esta tarea
				// estimated_progress de la tarea (0-100) * factor de ponderación (0-100) / 100
				$weightedProgress += ($task['estimated_progress'] * $task['weighting_factor']) / 100;
				$totalWeightingUsed += $task['weighting_factor'];
			}
			
			// Calcular progreso esperado de tareas SIN factor de ponderación (método tradicional)
			// Estas tareas comparten el porcentaje restante (100 - totalWeightingUsed)
			$remainingPercentage = 100 - $totalWeightingUsed;
			$unweightedProgress = 0;
			
			if ($remainingPercentage > 0 && $totalEstimatedTimeWithoutWeighting > 0) {
				$sumProgressTime = 0;
				foreach ($tasksWithoutWeighting as $task) {
					$sumProgressTime += $task['estimated_progress'] * $task['estimated_time'];
				}
				// Progreso esperado proporcional de las tareas sin ponderación
				$avgProgressUnweighted = $sumProgressTime / $totalEstimatedTimeWithoutWeighting;
				// Escalar al porcentaje restante
				$unweightedProgress = ($avgProgressUnweighted * $remainingPercentage) / 100;
			} elseif ($remainingPercentage > 0 && count($tasksWithoutWeighting) > 0) {
				// Si no hay estimated_time pero hay tareas, usar promedio simple
				$sumProgress = 0;
				foreach ($tasksWithoutWeighting as $task) {
					$sumProgress += $task['estimated_progress'];
				}
				$avgProgressUnweighted = $sumProgress / count($tasksWithoutWeighting);
				$unweightedProgress = ($avgProgressUnweighted * $remainingPercentage) / 100;
			}
			
			// Progreso esperado total = suma de ambos componentes
			$expectedProgress = $weightedProgress + $unweightedProgress;
			$expectedProgress = ($expectedProgress > 100) ? 100 : $expectedProgress;
			
			return $expectedProgress;
		}

		/**
		 * Realiza soft delete de un reporte de actividad
		 * Marca deleted=1 y actualiza los contadores de la actividad asociada
		 *
		 * @param integer $reportId ID del reporte a eliminar
		 * @return boolean True si se eliminó correctamente
		 * @throws Exception
		 */
		public function softDeleteReport($reportId) {
			if (empty($reportId)) {
				throw new Exception('Report ID is required');
			}

			// Obtener activityid antes de marcar como eliminado
			$result = $this->adb->pquery(
				'SELECT activityid FROM vtiger_activity_report WHERE activityreportid = ?',
				array($reportId)
			);

			if ($this->adb->num_rows($result) == 0) {
				throw new Exception('Report not found');
			}

			$activityId = $this->adb->query_result($result, 0, 'activityid');
			DatabaseUtils::closeResult($result);

			// Marcar como eliminado (soft delete)
			$this->adb->pquery(
				'UPDATE vtiger_activity_report SET deleted = 1, modificated_date = NOW() WHERE activityreportid = ?',
				array($reportId)
			);

			// Recalcular progress como MAX de reportes no eliminados,
			// incluyendo los reportes globales del Job padre del mismo trabajo
			$progressResult = $this->adb->pquery(
				'SELECT COALESCE(MAX(ar.progress), 0) AS new_progress
				 FROM vtiger_activity_report ar
				 INNER JOIN vtiger_activity act ON act.activityid = ar.activityid
				 WHERE ar.deleted = 0
				   AND (
				       ar.activityid = ?
				       OR (
				           act.activitytype = ?
				           AND act.related_id = (SELECT related_id FROM vtiger_activity WHERE activityid = ?)
				       )
				   )',
				array($activityId, 'Job', $activityId)
			);
			$newProgress = ($this->adb->num_rows($progressResult) > 0)
				? floatval($this->adb->query_result($progressResult, 0, 'new_progress'))
				: 0;
			DatabaseUtils::closeResult($progressResult);

			$this->adb->pquery(
				'UPDATE vtiger_activity SET progress = ? WHERE activityid = ?',
				array($newProgress, $activityId)
			);

			// FIX: Sincronizar contadores después del soft delete
			$this->syncActivityCounters($activityId);

			// FIX: Llamar procedure para recalcular campos calculados (evita triggers)
			$this->adb->pquery(
				'CALL sp_update_activity_progress_clean(?, ?)',
				array($activityId, true)
			);
			pearDatabase_FlushResults($this->adb);

			return true;
		}
	}

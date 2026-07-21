<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ActivityReportManager.php');
	require_once ('include/platzilla/Data/TaskActivity.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/Pagination.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/fields/DateTimeField.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');
	require_once ('modules/Settings/lib/HowToHelper.class.php');
	require_once ('modules/daily_report/lib/DailyReportUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/operating_modes/Objects/ManagementModeInterface.php');
	require_once ('include/utils/UserInfoUtil.php');
	
	abstract class ManagementModeHelper implements ManagementModeInterface {
		
		const PRIORITY_TRANSLATE    = array ('ALTO' => 'HIGH', 'MEDIO' => 'HIGH', 'BAJO' => 'LOW');
		const QUADRANTS             = array ('HIGH-HIGH', 'HIGH-LOW', 'LOW-HIGH', 'LOW-LOW');
		const RECORDS_PER_PAGE      = 15;
		const START_RECORD          = 0;
		const BACKGROUND_TASK_COLOR = array (
			'Planned'           => '#ffffe5',
			'Planned_out_time'  => '#fbddab',
			'Not Held'          => '#c5dcf1',
			'Not Held_Retarded' => '#fc8d6e',
			'Held'              => '#99cc99',
			'Postponed'         => '#fbddab',
			'No iniciado'       => '#FFFFFF',
			'Asignado'          => '#f9bc7e',
			'Tramitado'         => '#a7cff2',
			'En espera de recibir compra' => '#b3f772',
			'Creado'            => '#FFFFFF',
			'Definido'          => '#f9bc7e',
			'En curso'          => '#a7cff2',
			'Terminado'         => '#4bde72',
			'Nuevo'             => '#ffffe5',
			'Programado'        => '#f9bc7e',
			'En trámite'        => '#a7cff2',
			'Resuelta'          => '#4bde72',
			'Resuelto'          => '#4bde72',
			'Descartado'        => '#d9d9d9',
			'Esperando datos'   => '#d4a9e6',
			'Iniciación'        => '#ffffe5',
			'Contactos'         => '#f9bc7e',
			'Negociación'       => '#a7cff2',
			'Revisión'          => '#b3f772',
			'Cierre - Ganada'   => '#4bde72',
			'Cierre - Perdida'  => '#A7B4AA',
		);
		
		protected static $recordPerPage = 15;
		
		/**
		 * Formatea una fecha del formato BD (Y-m-d) al formato del usuario
		 * 
		 * @param string $dateValue Fecha en formato BD (Y-m-d o Y-m-d H:i:s)
		 * @return string Fecha formateada según preferencias del usuario
		 */
		protected static function formatDateForUser($dateValue) {
			if ($dateValue === null || $dateValue === '' || $dateValue === '0000-00-00' || $dateValue === '0000-00-00 00:00:00') {
				return '';
			}
			try {
				$dateField = new DateTimeField($dateValue);
				return $dateField->getDisplayDate();
			} catch (Exception $e) {
				return $dateValue; // Retornar valor original si hay error
			}
		}
		
		/**
		 * Preserva el valor original de una fecha y asigna su versión formateada.
		 *
		 * @param array  $row
		 * @param string $fieldName
		 * @param string $suffix
		 *
		 * @return void
		 */
		protected static function normalizeDateField(array &$row, $fieldName, $suffix = '_db') {
			$rawKey = "{$fieldName}{$suffix}";
			$rawValue = isset($row[$fieldName]) ? $row[$fieldName] : null;
			$row[$rawKey] = $rawValue;
			$row[$fieldName] = self::formatDateForUser($rawValue);
		}
		
		/**
		 * @param $calendarArray
		 * @param $dataArray
		 *
		 * @return array|null
		 */
		private static function getCalendarData ($calendarArray, $dataArray, $calendarType) {
			if (empty ($dataArray)) {
				return null;
			}
			$dataKeys = array_keys ($dataArray[0]);
			$dataKeys = array_diff ($dataKeys, array ('event_status', 'eventstatus', 'estado_del_pedid', 'estado_incidencia', 'fase_de_venta', 'estado_de_la_orden'));
			foreach ($dataArray as $row) {
				foreach ($calendarArray as $key => $value) {
					if ($key === 'url') {
						$myTab  = (!empty($calendarType)) ? '&tab=task-list' : '';
						$result [$key] = "index.php?module={$row[$value[0]]}&action=DetailView&record={$row[$value[1]]}{$myTab}";
						continue;
					}
					if ($value === 'RND') {
						$result [$key] = rand (1000,10000);
						continue;
					}
					if ($key === 'backgroundColor') {
						$result [$key] = self::setBackgroundColorToTask ($row, $value);
						continue;
					}
					if (in_array($key, array('start', 'end'))) {
						$rawKey   = "{$value}_db";
						$rawValue = isset($row[$rawKey]) ? $row[$rawKey] : (isset($row[$value]) ? $row[$value] : null);
						$result[$key] = $rawValue;
						continue;
					}
					if (in_array ($value, array_values ($dataKeys))) {
						$result [$key] = $row [$value];
					} else {
						$result [$key] = (!empty ($value)) ? $value : 'tarea/acción no definida';
					}
				}
				$results [] = $result;
			}
			return (isset($results)) ? $results : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $jobId
		 * @param string|array $userId
		 *
		 * @return integer|mixed
		 */
		private static function getEstimatedTimeByJob ($adb, $jobId, $userId) {
			if (empty ($jobId)) {
				return 0;
			}
			if (is_array ($userId)) {
				$whereUsers = " AND ar.userid IN{$adb->sql_expr_datalist ($userId)}";
			} else {
				$whereUsers = " AND ar.userid IN({$userId})";
			}
			$result = $adb->pquery (
				"SELECT
       				IFNULL(SUM(task.estimated_time), 0) AS estimated_time,
       				COUNT(*) AS total_tasks,
       				duration_time
				FROM
					vtiger_activity task
				INNER JOIN vtiger_crmentity crm ON crm.crmid = task.activityid
				CROSS JOIN (
					    SELECT IFNULL(SUM(ar.duration_time), 0) AS duration_time
					    FROM vtiger_activity_report ar
					    INNER JOIN vtiger_activity act ON ar.activityid = act.activityid
					    INNER JOIN vtiger_crmentity cr ON cr.crmid = act.activityid AND cr.deleted = 0
					    WHERE act.related_id = ? AND ar.deleted = 0 {$whereUsers}
				    ) AS duration_time
				WHERE
					crm.deleted = ? AND
					task.related_id = ?",
				array ($jobId, 0, $jobId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$estimatedTime['estimated_time'] = $row ['estimated_time'];
				$estimatedTime['total_tasks']    = $row ['total_tasks'];
				$estimatedTime['duration_time']  = $row ['duration_time'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($estimatedTime)) ? $estimatedTime : 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $wordId
		 * @param integer $userId
		 *
		 * @return array|null
		 */
		private static function getProjectFromWork ($adb, $wordId, $userId) {
			if (empty($wordId) || empty($userId)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT
       				pr.proyectosid,
       				pr.nombre
				FROM
				    vtiger_project_works pw
				INNER JOIN vtiger_proyectos pr  ON pr.proyectosid = pw.crmid
				INNER JOIN vtiger_crmentity crm ON crm.crmid = pr.proyectosid
				WHERE
					crm.deleted= ? AND
				    pw.crmid_job= ? AND
				    pw.responsible_job= ?',
				array (0, $wordId, $userId)
			);
			if ($adb->num_rows ($result) > 0) {
				$projectFromWork = $adb->fetchByAssoc ($result, -1, false);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($projectFromWork)) ? $projectFromWork : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $activityId
		 * @param integer|array $userId
		 *
		 * @return integer|mixed
		 */
		private static function getReportTimeByActivity ($adb, $activityId, $userId) {
			if (empty ($activityId) || empty ($userId)) {
				return 0;
			}
			if (is_array($userId)) {
				$whereUsers = " AND userid IN{$adb->sql_expr_datalist ($userId)}";
			} else {
				$whereUsers = " AND userid IN({$userId})";
			}
			$result = $adb->pquery (
				"SELECT
			       	IFNULL(SUM(duration_time), 0) AS duration_time
				FROM
					vtiger_activity_report
				WHERE
					activityid = ?
					AND (deleted = 0 OR deleted IS NULL)
					{$whereUsers}",
				array ($activityId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$reportTime = $row ['duration_time'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($reportTime)) ? $reportTime : 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $taskId
		 *
		 * @return mixed|string
		 */
		private static function getTaskRelateModule ($adb, $taskId) {
			$result = $adb->pquery (
				'SELECT
			       		tab.tablabel,
						tab.name,
       					se.crmid
					FROM
						vtiger_tab tab
					INNER JOIN vtiger_crmentity crm ON crm.setype = tab.name
					INNER JOIN vtiger_seactivityrel se ON se.crmid = crm.crmid
					WHERE
						se.activityid = ?
					LIMIT 1',
				array ($taskId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$relateModule = $row;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($relateModule)) ? $relateModule : null;
		}
		
		/**
		 * @param array $activityWorks
		 *
		 * @return array
		 */
		private static function getTimeByQuadrant ($activityWorks) {
			$timeByQuadrant = array ();
			$totalTime    = 0;
			$totalTask    = 0;
			$reportTime   = 0;
			foreach (self::QUADRANTS as $quadrant) {
				$quadrantTime = 0;
				foreach ($activityWorks[$quadrant] as $activityWork) {
					$quadrantTime += floatval ($activityWork['_time']);
					$reportTime   += floatval ($activityWork['duration_time']);
					$totalTask    += ($quadrantTime > 0) ? 1 : 0;
				}
				$timeByQuadrant[] = $quadrantTime;
				$totalTime       += $quadrantTime;
			}
			$timeByQuadrant [] = $totalTime;
			$timeByQuadrant [] = ($totalTask + $activityWorks ['total_tasks']);
			$timeByQuadrant [] = $reportTime;
			return $timeByQuadrant;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $row
		 *
		 * @return int|mixed|string
		 */
		private static function getTitleRelatedModule ($adb, $row) {
			if (empty ($row)) {
				return '';
			}
			$result = $adb->pquery (
				'SELECT * FROM vtiger_entityname WHERE modulename=?',
				array ($row ['module_name'])
			);
			if ($adb->num_rows ($result) > 0) {
				$entityname = $adb->fetchByAssoc ($result, -1, false);
				$sql = $adb->pquery (
					"SELECT {$entityname['fieldname']} FROM {$entityname['tablename']} WHERE {$entityname['entityidfield']}=?",
					array ($row['crmid'])
				);
				if ($adb->num_rows ($sql) > 0) {
					$title = $adb->fetchByAssoc ($sql, -1, false)[$entityname['fieldname']];
				}
				DatabaseUtils::closeResult ($sql);
				$sql = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($title)) ? $title : '';
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $crmEntityId
		 * @params string $from
		 *
		 * @return integer
		 * @throws Exception
		 */
		private static function getTotalFeeddBacks ($adb, $crmEntityId, $from) {
			if (empty($crmEntityId)) {
				return 0;
			}
			if ($from == 'work') {
				$result = $adb->pquery('SELECT COUNT(activityid) AS total FROM vtiger_activity_feedback WHERE activityid=?', array($crmEntityId));
			} else if ($from == 'project') {
				$result = $adb->pquery (
					'SELECT
			       			COUNT(af.activityid) AS total
						FROM
							vtiger_activity_feedback af
						INNER JOIN vtiger_activity a ON af.activityid = a.activityid
						WHERE
							a.related_id=?',
					array($crmEntityId)
				);
			} else {
				return 0;
			}
			
			$totalResult = 0;
			if ($adb->num_rows ($result) > 0) {
				$row         = $adb->fetchByAssoc ($result, -1, false);
				$totalResult = $row ['total'];
			}
			DatabaseUtils::closeResult ($result);
			return isset ($totalResult) ? $totalResult : 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer|array $userId
		 * @param array $periodDates
		 * @return float|integer
		 */
		private static function getTotalHoursDailyReport ($adb, $userId, $periodDates) {
			if (empty ($userId) || empty ($periodDates)) {
				return 0;
			}
			$dummy = (is_array($userId)) ? $userId : explode (',', $userId);
			if (in_array (1, $dummy)) {
				$whereUsers = '';
			} else {
				$users = $adb->sql_expr_datalist ($dummy);
				$whereUsers = " AND crm.smownerid IN({$users})";
			}
			$result = $adb->pquery (
				"SELECT
					IFNULL(SUM(dr.total_hours_reported), 0) AS duration_time
				FROM
					vtiger_daily_report dr
				INNER JOIN vtiger_crmentity crm ON crm.crmid = dr.daily_reportid
				WHERE
					crm.deleted = 0
				AND dr.daily_report_date >= DATE(?)
				AND dr.daily_report_date <= DATE(?)
				{$whereUsers}",
				array ($periodDates['startdate'], $periodDates['enddate'])
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$reportTime = floatval ($row ['total_hours_reported']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($reportTime)) ? $reportTime : 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer|array $userId
		 * @param array $periodDates
		 *
		 * @return integer|array
		 */
		private static function getTotalHoursReported ($adb, $userId, $periodDates) {
			if (empty ($userId) || empty ($periodDates)) {
				return 0;
			}
			if (is_array($userId)) {
				$whereUsers = " AND ar.userid IN{$adb->sql_expr_datalist ($userId)}";
			} else {
				$whereUsers = " AND ar.userid IN({$userId})";
			}
			$result = $adb->pquery (
				"SELECT
					IFNULL(SUM(ar.duration_time), 0) AS duration_time,
					IFNULL(SUM(a.estimated_time), 0) AS estimated_time
				FROM
					vtiger_activity_report ar
				INNER JOIN vtiger_activity a ON a.activityid = ar.activityid
				INNER JOIN vtiger_crmentity crm ON crm.crmid = a.activityid
				WHERE
					crm.deleted = 0
					AND ar.deleted = 0
					AND a.date_start >= DATE(?)
					AND a.due_date <= DATE(?)
					{$whereUsers}",
				array ($periodDates['startdate'], $periodDates['enddate'])
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$reportTime[] = $row ['duration_time'];
				$reportTime[] = $row ['estimated_time'];
				
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($reportTime)) ? $reportTime : 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $crmEntityId
		 * @param string $from
		 *
		 * @return integer
		 * @throws Exception
		 */
		private static function getTotalReports ($adb, $crmEntityId, $from) {
			if (empty ($crmEntityId)) {
				return 0;
			}
			if ($from == 'work') {
				$result = $adb->pquery ('SELECT COUNT(activityid) AS total FROM vtiger_activity_report WHERE activityid=? AND (deleted = 0 OR deleted IS NULL)', array($crmEntityId));
			} else if ($from == 'project') {
				$result = $adb->pquery (
					'SELECT
       						COUNT(ar.activityid) AS total
						FROM
						     vtiger_activity_report ar
						INNER JOIN vtiger_activity a ON ar.activityid = a.activityid
						WHERE
						    a.related_id=? AND ar.deleted = 0',
					array($crmEntityId)
				);
			} else {
				return 0;
			}
			$totalResult = 0;
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$totalResult = $row ['total'];
			}
			DatabaseUtils::closeResult ($result);
			return isset ($totalResult) ? $totalResult : 0;
		}
		
		/**
		 * @param array $activityWorks
		 *
		 * @return array
		 */
		private static function getTotalsByQuadrant ($activityWorks) {
			$totalsByQuadrant = array ();
			$totalsQuadrant   = 0;
			foreach (self::QUADRANTS as $quadrant) {
				if (!isset ($activityWorks [$quadrant]) || empty ($activityWorks [$quadrant])) {
					$totalThis = 0;
				} else {
					$totalThis = count ($activityWorks[$quadrant]);
				}
				$totalsByQuadrant [] = $totalThis;
				$totalsQuadrant      += $totalThis;
			}
			$totalsByQuadrant [] = $totalsQuadrant;
			return $totalsByQuadrant;
		}
		
		/**
		 * @param array|string $users
		 *
		 * @return array
		 */
		private static function hasAdmin ($users) {
			$dummy = (!is_array ($users)) ? explode (',', $users) : $users;
			if (in_array (1, $dummy)) {
				return array (
					'join_user'  => "user.id IN({$users})",
					'where_user' => " ",
					);
			} else {
				return array (
					'join_user'  => "user.id IN({$users})",
					'where_user' => "crm.smownerid IN({$users}) AND ",
					);
			}
		}

		/**
		 * Verifica si el usuario tiene acceso a un registro considerando:
		 * 1. Si es administrador
		 * 2. Si es el propietario (smownerid)
		 * 3. Si es el creador (smcreatorid)
		 * 4. Si pertenece al grupo propietario (smownerid es un grupo)
		 * 5. Privilegios de Acceso Personalizados (Sharing Rules)
		 *
		 * @param integer|array $userIds ID(s) del usuario actual
		 * @param string $moduleName Nombre del módulo
		 * @param array $crmEntityData Datos del registro (crmid, smownerid, smcreatorid)
		 *
		 * @return boolean true si tiene acceso, false en caso contrario
		 */
		private static function hasAccess ($userIds, $moduleName, $crmEntityData) {
			// Normalizar userIds a array
			$users = (!is_array ($userIds)) ? explode (',', $userIds) : $userIds;

			// 1. Si es administrador (user ID 1), permitir acceso
			if (in_array (1, $users)) {
				return true;
			}

			// 2. Si es el propietario directo (smownerid), permitir acceso
			if (isset ($crmEntityData ['smownerid']) && in_array ($crmEntityData ['smownerid'], $users)) {
				return true;
			}

			// 3. Si es el creador (smcreatorid), permitir acceso
			if (isset ($crmEntityData ['smcreatorid']) && in_array ($crmEntityData ['smcreatorid'], $users)) {
				return true;
			}

			// 4. Si el registro está asignado a un GRUPO y el usuario pertenece a ese grupo
			if (isset ($crmEntityData ['smownerid'])) {
				global $current_user;
				if (isset ($current_user) && $current_user instanceof Users) {
					// Cargar privilegios de sharing que contienen los grupos del usuario
					$local_user = clone $current_user;
					// Usar ruta absoluta para evitar problemas con el directorio de trabajo
					$platRoot = isset ($_SESSION ['plat']) ? $_SESSION ['plat'] : '';
					if (!empty ($platRoot) && file_exists ($platRoot . '/user_privileges/sharing_privileges.php')) {
						require ($platRoot . '/user_privileges/sharing_privileges.php');
					} else {
						require ('user_privileges/sharing_privileges.php');
					}
					
					// Verificar si el propietario es un grupo al que pertenece el usuario
					if (isset ($current_user_groups) && is_array ($current_user_groups)) {
						if (in_array ($crmEntityData ['smownerid'], $current_user_groups)) {
							return true;
						}
					}
				}
			}

			// 5. Verificar Privilegios de Acceso Personalizados (Sharing Rules)
			if (isset ($crmEntityData ['crmid'])) {
				$moduleTabId = getTabid ($moduleName);
				// Pre-cargar sharing_privileges con ruta absoluta para asegurar que isReadPermittedBySharing funcione
				$platRoot = isset ($_SESSION ['plat']) ? $_SESSION ['plat'] : '';
				$sharingFilePath = $platRoot . '/user_privileges/sharing_privileges.php';
				
				if (!empty ($platRoot) && file_exists ($sharingFilePath)) {
					require_once ($sharingFilePath);
				}
				
				if (isReadPermittedBySharing ($moduleName, $moduleTabId, 2, $crmEntityData ['crmid']) == 'yes') {
					return true;
				}
			}

			return false;
		}
		
		/**
		 * @param array $activityWorks
		 * @param array $activityTasks
		 *
		 * @return void
		 */
		private static function mergeActivities (&$activityWorks, $activityTasks) {
			if (empty ($activityTasks)) {
				return;
			}
			foreach (self::QUADRANTS as $quadrant) {
				foreach ($activityTasks[ $quadrant ] as $activityTask) {
					if (empty ($activityTask)) {
						continue;
					}
					$activityWorks [ $quadrant ][] = $activityTask;
				}
			}
		}
		
		private static function setBackgroundColorToTask ($row, $field) {
			if (count ($row) == 0) {
				return self::BACKGROUND_TASK_COLOR ['Planned'];
			}
			
			$fechaActual = date ('Y-m-d');
			$dateStartRaw = isset($row['date_start_db']) ? $row['date_start_db'] : (isset($row['date_start']) ? $row['date_start'] : null);
			$dueDateRaw   = isset($row['due_date_db']) ? $row['due_date_db'] : (isset($row['due_date']) ? $row['due_date'] : null);
			
			try {
				$dateStart = !empty($dateStartRaw) ? new DateTime ($dateStartRaw) : null;
			} catch (Exception $e) {
				$dateStart = null;
			}
			try {
				$dueDate = !empty($dueDateRaw) ? new DateTime ($dueDateRaw) : null;
			} catch (Exception $e) {
				$dueDate = null;
			}
			$today = new DateTime ($fechaActual);
			
			if ($row[$field] =='Planned') {
				if ($dateStart && ($dateStart <= $today)) {
					return self::BACKGROUND_TASK_COLOR [ $row[$field] ];
				} else {
					return self::BACKGROUND_TASK_COLOR [ $row[$field] . '_out_time' ];
				}
			} else if (($row[$field] =='Not Held')) {
				if ($dueDate && ($dueDate <= $today)) {
					return self::BACKGROUND_TASK_COLOR [ $row[$field] ];
				} else {
					return self::BACKGROUND_TASK_COLOR [ $row[$field] . '_Retarded' ];
				}
			} else {
				$codeColor = self::BACKGROUND_TASK_COLOR [ $row[$field] ];
				return (!empty ($codeColor)) ? $codeColor : '#FFFFFF';
			}
		}
		/**
		 * @param integer $totalRows
		 * @param integer $id
		 *
		 * @return Pagination
		 */
		public static function configPaginator ($totalRows, $id) {
			$paginator       = Pagination::getInstance();
			$paginatorConfig = array (
				'totalRows'       => $totalRows,
				'perPage'         => self::RECORDS_PER_PAGE,
				'numLinks'        => 5,
				'attributes'      => array('class' => 'linkPag', 'onclick' => "DataViewUtils.goToPage (event, this, '{$id}');"),
				'firstTagOpen'    => "<li class='Pages'>",
				'firstTagClose'   => '</li>',
				'lastTagOpen'     => "<li class='Pages'>",
				'lastTagClose'    => '</li>',
				'currentTagOpen'  => "<li class='Pages'><a href='#'><strong>",
				'currentTagClose' => '</strong></a></li>',
				'numTagOpen'      => "<li class='Pages'>",
				'numTagClose'     => '</li>',
				'prevTagOpen'     => "<li class='Pages'>",
				'prevTagClose'    => '</li>',
				'nextTagOpen'     => "<li class='Pages'>",
				'nextTagClose'    => '</li>',
			);
			$paginator->initialize ($paginatorConfig);
			return $paginator;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param array $range
		 * @param integer $starRecord
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchActionTasksInProgress ($adb, $userId, $range, $starRecord) {
			$modStrings = return_module_language ('es_es','Calendar');
			$query = array (
				'select'     => 'crm.crmid, crm.smcreatorid, crm.smownerid, crm.setype AS module_name,
								CONCAT(user.first_name, " ",user.last_name) AS username,user.imagename,
								act.activityid, act.subject, act.date_start, act.due_date, act.activitytype,
								act.estimated_time, act.progress, act.eventstatus, act.combined_condition, tab.tablabel',
				'from'       => 'vtiger_seactivityrel sea',
				'inner_join' => "INNER JOIN vtiger_activity act ON act.activityid = sea.activityid
								INNER JOIN vtiger_crmentity crm ON crm.crmid = sea.crmid
								INNER JOIN vtiger_tab tab ON tab.name = crm.setype
								LEFT JOIN vtiger_users user ON user.id = crm.smownerid",
				'where'	    => "crm.deleted = 0 AND crm.setype != 'orden_de_trabajo' AND
								act.progress < 100 AND
								validityRecordByDate(act.date_start, act.due_date, crm.createdtime, '{$range ['startdate']}', '{$range ['enddate']}') = 1",
			);
			$recordsPerPage = self::$recordPerPage;
			$result = $adb->query (
				"SELECT  DISTINCT
					{$query ['select']},
					total.__total_records__
				FROM
					{$query ['from']}
					{$query ['inner_join']}
				CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM {$query ['from']}  {$query ['inner_join']} WHERE {$query ['where']}) AS total
				WHERE
					{$query ['where']}
				LIMIT {$starRecord}, {$recordsPerPage}"
			);
			$actionTasksInProgress = array();
			$totalRecords = 0;
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					// Verificar permisos de acceso (incluyendo Sharing Rules)
					$crmData = array(
						'crmid' => $row['crmid'],
						'smownerid' => $row['smownerid'],
						'smcreatorid' => $row['smcreatorid']
					);
					if (!self::hasAccess($userId, $row['module_name'], $crmData)) {
						continue;
					}
					
					if ($totalRecords === 0) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					unset ($row ['__total_records__']);
					
					$row ['event_status'] = $row ['eventstatus'];
					$row ['eventstatus']  = $modStrings [$row ['eventstatus']];
					$row ['reports']      = self::getTotalReports ($adb, $row['activityid'],'work');
					$row ['feedbacks']    = self::getTotalFeeddBacks ($adb, $row['activityid'], 'work');
					$row ['module_title'] = $row['tablabel'] . '/ '.self::getTitleRelatedModule ($adb, $row);
					$row ['help_data']    = HowToHelper::hasHowTo ($adb, 'Calendar', $row ['activityid'], 'DetailView_Task');
					self::normalizeDateField($row, 'date_start');
					self::normalizeDateField($row, 'due_date');
					$actionTasksInProgress [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (!empty ($actionTasksInProgress)) ? array($actionTasksInProgress, $totalRecords) : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $periodTime
		 * @param Users $current_user
		 *
		 * @return string|null
		 */
		public static function fetchActionsInProgress ($adb, $current_user, $periodTime) {
			if (empty ($periodTime) || empty ($current_user) || !$current_user instanceof Users) {
				return null;
			}
			$periodDates    = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodTime);
			$workInProgress = self::fetchWorkInProgress ($adb, $current_user->id, $periodDates,self::START_RECORD);
			$workPaginator  = null;
			$workTabId  	=  rand (1000, 10000);
			if (!empty ($workInProgress)) {
				$paginator     = self::configPaginator ($workInProgress[1], $workTabId);
				$workPaginator = $paginator->createLinks ();
			}
			
			$projectsInProgress = self::fetchProjectsInProgress ($adb, $current_user->id, $periodDates,self::START_RECORD);
			$projectTabId  	    =  rand (1001, 10001);
			$projectPaginator   = null;
			if (!empty ($projectsInProgress)) {
				$paginator        = self::configPaginator ($projectsInProgress[1],$projectTabId);
				$projectPaginator = $paginator->createLinks ();
			}
			$actionsTabId  	    =  rand (1500, 100010);
			$actionsPaginator   = null;
			$actionsInProgress = self::fetchActionTasksInProgress ($adb, $current_user->id, $periodDates,self::START_RECORD);
			if (!empty ($actionsInProgress)) {
				$paginator         = self::configPaginator ($actionsInProgress[1],$actionsTabId);
				$actionsPaginator  = $paginator->createLinks ();
			}
			$smarty = new vtigerCRM_Smarty ();
			global $app_strings;
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('ACTION_PAGER', $actionsPaginator);
			$smarty->assign ('ACTION_TAB_ID', $actionsTabId);
			$smarty->assign ('ACTION_IN_PROGRESS', (!empty($actionsInProgress)) ? $actionsInProgress[0] : null);
			$smarty->assign ('ACTION_TABLE_HEADER', self::ACTIONS_TABLE_HEADER);
			$smarty->assign ('ACTION_TABLE_ROW', self::ACTIONS_TABLE_ROW);
			$smarty->assign ('ACTION_TOTAL_ROWS', (!empty($actionsInProgress)) ? $actionsInProgress[1] : 0);
			$smarty->assign ('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar ($adb, $current_user));
			$smarty->assign ('MODULE', 'Home');
			$smarty->assign ('MODULE_TABS', self::ACTIONS_PROGRESS_MODULES);
			$smarty->assign ('MODULE_NAME', array_keys (self::ACTIONS_PROGRESS_MODULES));
			$smarty->assign ('PERIOD_DATES', NotificationPeriodUtils::getAvailablePeriods ());
			$smarty->assign ('PERIOD_SELECTED', $periodTime);
			$smarty->assign ('PROJECT_IN_PROGRESS', (!empty($projectsInProgress)) ? $projectsInProgress[0] : null);
			$smarty->assign ('PROJECT_PAGER', $projectPaginator);
			$smarty->assign ('PROJECT_TABLE_HEADER', self::PROJECT_TABLE_HEADER);
			$smarty->assign ('PROJECT_TABLE_ROW', self::PROJECT_TABLE_ROW);
			$smarty->assign ('PROJECT_TOTAL_ROWS', (!empty($projectsInProgress)) ? $projectsInProgress[1] : 0);
			$smarty->assign ('PROJECT_TAB_ID', $projectTabId);
			$smarty->assign ('RECORDS_PER_PAGE', self::RECORDS_PER_PAGE);
			$smarty->assign ('SELECTED_MODULE', 'orden_de_trabajo');
			$smarty->assign ('START_RECORD', (self::START_RECORD + 1));
			$smarty->assign ('URL_AVATARS', "{$_SESSION ['plat']}/user_images");
			$smarty->assign ('USER_IDS', array ($current_user->id));
			$smarty->assign ('USER_NAME', ($current_user->first_name . ' ' . $current_user->last_name));
			$smarty->assign ('WORK_TABLE_HEADER', self::WORK_TABLE_HEADER);
			$smarty->assign ('WORK_TABLE_ROW', self::WORK_TABLE_ROW);
			$smarty->assign ('WORK_IN_PROGRESS', (!empty($workInProgress)) ? $workInProgress[0] : null);
			$smarty->assign ('WORK_PAGER', $workPaginator);
			$smarty->assign ('WORK_TAB_ID', $workTabId);
			$smarty->assign ('WORK_TOTAL_ROWS', (!empty($workInProgress)) ? $workInProgress[1] : 0);
			
			// Datos para la pestaña "Por Proveedor"
			$supplierTabId = rand (2000, 20000);
			$availableSuppliers = self::fetchSuppliersWithTasks ($adb, $periodDates);
			$supplierWorkData = null;
			$supplierPaginator = null;
			$selectedSupplier = null;
			
			if (!empty ($availableSuppliers)) {
				$selectedSupplier = $availableSuppliers[0]['id'];
				$supplierWorkData = self::fetchWorkBySupplier ($adb, $selectedSupplier, $periodDates, self::START_RECORD);
				if (!empty ($supplierWorkData)) {
					$paginator = self::configPaginator ($supplierWorkData[1], $supplierTabId);
					$supplierPaginator = $paginator->createLinks ();
				}
			}
			
			$smarty->assign ('AVAILABLE_SUPPLIERS', $availableSuppliers);
			$smarty->assign ('SELECTED_SUPPLIER', $selectedSupplier);
			$smarty->assign ('SUPPLIER_WORK_DATA', (!empty($supplierWorkData)) ? $supplierWorkData[0] : null);
			$smarty->assign ('SUPPLIER_WORK_PAGER', $supplierPaginator);
			$smarty->assign ('SUPPLIER_WORK_TABLE_HEADER', self::SUPPLIER_WORK_TABLE_HEADER);
			$smarty->assign ('SUPPLIER_WORK_TABLE_ROW', self::SUPPLIER_WORK_TABLE_ROW);
			$smarty->assign ('SUPPLIER_WORK_TAB_ID', $supplierTabId);
			$smarty->assign ('SUPPLIER_WORK_TOTAL_ROWS', (!empty($supplierWorkData)) ? $supplierWorkData[1] : 0);
			
			return $smarty->fetch ('Home/ActionTabs/ActionsInProgress.tpl');
		}
		
		/**
		 * Obtiene la lista de proveedores que tienen tareas asignadas en trabajos
		 * @param PearDatabase $adb
		 * @param array $range Rango de fechas
		 * @return array|null Lista de proveedores con id, nombre y cantidad de tareas
		 */
		public static function fetchSuppliersWithTasks ($adb, $range) {
			$result = $adb->pquery (
				"SELECT 
					p.proveedoresid,
					COALESCE(p.alias, p.nombre_de_la_sociedad) AS supplier_name,
					COUNT(DISTINCT srel.activityid) AS task_count
				FROM vtiger_proveedores p
				INNER JOIN vtiger_crmentity crm_prov ON crm_prov.crmid = p.proveedoresid AND crm_prov.deleted = 0
				INNER JOIN vtiger_supplieractivityrel srel ON srel.proveedoresid = p.proveedoresid
				INNER JOIN vtiger_activity task ON task.activityid = srel.activityid
				INNER JOIN vtiger_crmentity crm_task ON crm_task.crmid = task.activityid AND crm_task.deleted = 0
				WHERE 
					validityRecordByDate(task.date_start, task.due_date, crm_task.createdtime, ?, ?) = 1
				GROUP BY p.proveedoresid, supplier_name
				ORDER BY supplier_name ASC",
				array ($range['startdate'], $range['enddate'])
			);
			
			$suppliers = array();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$suppliers[] = array(
						'id'         => $row['proveedoresid'],
						'name'       => $row['supplier_name'],
						'task_count' => $row['task_count']
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty($suppliers) ? $suppliers : null;
		}
		
		/**
		 * Obtiene trabajos y tareas asignados a un proveedor específico
		 * @param PearDatabase $adb
		 * @param integer|array $supplierId ID del proveedor o array de IDs
		 * @param array $range Rango de fechas
		 * @param integer $starRecord Registro inicial para paginación
		 * @return array|null
		 */
		public static function fetchWorkBySupplier ($adb, $supplierId, $range, $starRecord) {
			$modStrings = return_module_language ('es_es', 'Calendar');
			
			// Construir condición de proveedor
			if (is_array($supplierId)) {
				$supplierIds = implode(',', array_map('intval', $supplierId));
				$supplierCondition = "srel.proveedoresid IN ({$supplierIds})";
			} else {
				$supplierCondition = "srel.proveedoresid = " . intval($supplierId);
			}
			
			$query = array (
				'select' => 'crm.crmid, crm.smcreatorid, crm.smownerid, crm.setype AS tab_name,
							ot.orden_de_trabajoid AS entity_id, ot.orden_de_trabajoid, ot.titulo,
							ot.estado_de_la_orden, task.activityid, task.subject, task.activitytype,
							task.date_start, task.due_date, task.eventstatus,
							p.proveedoresid, COALESCE(p.alias, p.nombre_de_la_sociedad) AS supplier_name,
							proy.proyectosid AS proyectoid, proy.nombre AS project_name',
				'from'   => 'vtiger_crmentity crm',
				'inner_join' => "INNER JOIN vtiger_orden_de_trabajo ot ON crm.crmid = ot.orden_de_trabajoid
								INNER JOIN vtiger_activity task ON task.related_id = ot.orden_de_trabajoid
								INNER JOIN vtiger_crmentity crm_task ON crm_task.crmid = task.activityid AND crm_task.deleted = 0
								INNER JOIN vtiger_supplieractivityrel srel ON srel.activityid = task.activityid
								INNER JOIN vtiger_proveedores p ON p.proveedoresid = srel.proveedoresid
								LEFT JOIN vtiger_project_works pw ON pw.crmid_job = ot.orden_de_trabajoid
								LEFT JOIN vtiger_proyectos proy ON proy.proyectosid = pw.crmid
								LEFT JOIN vtiger_crmentity crm_proy ON crm_proy.crmid = proy.proyectosid AND crm_proy.deleted = 0",
				'where'  => "crm.deleted = 0 AND
							{$supplierCondition} AND
							validityRecordByDate(task.date_start, task.due_date, crm.createdtime, 
								'{$range['startdate']}', '{$range['enddate']}') = 1",
			);
			
			$recordsPerPage = self::$recordPerPage;
			$result = $adb->query (
				"SELECT {$query['select']}, total.__total_records__
				 FROM {$query['from']} {$query['inner_join']}
				 CROSS JOIN (SELECT COUNT(*) AS __total_records__ 
							 FROM {$query['from']} {$query['inner_join']} 
							 WHERE {$query['where']}) AS total
				 WHERE {$query['where']}
				 ORDER BY task.due_date ASC, supplier_name, entity_id
				 LIMIT {$starRecord}, {$recordsPerPage}"
			);
			
			$workBySupplier = array();
			$totalRecords = 0;
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					// Verificar permisos de acceso (incluyendo Sharing Rules)
					$crmData = array(
						'crmid' => $row['crmid'],
						'smownerid' => $row['smownerid'],
						'smcreatorid' => $row['smcreatorid']
					);
					if (!self::hasAccess($supplierId, 'orden_de_trabajo', $crmData)) {
						continue;
					}
					
					if ($totalRecords === 0) {
						$totalRecords = intval ($row['__total_records__']);
					}
					unset ($row['__total_records__']);
					
					$row['event_status'] = $row['eventstatus'];
					$row['eventstatus'] = $modStrings[$row['eventstatus']];
					$row['help_data']   = HowToHelper::hasHowTo ($adb, 'Calendar', $row['activityid'], 'DetailView_Task');
					self::normalizeDateField($row, 'date_start');
					self::normalizeDateField($row, 'due_date');
					// Si no hay proyecto asociado, mostrar vacío
					if (empty($row['proyectoid'])) {
						$row['project_name'] = '';
						$row['proyectoid'] = '';
					}
					$workBySupplier[]   = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($workBySupplier)) ? array ($workBySupplier, $totalRecords) : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $range
		 *
		 * @return array|null
		 */
		public static function fetchProjectsInProgress ($adb, $userId, $range, $starRecord) {
			global $current_user;
			
			// Agregar timeout para evitar cargas infinitas
			$originalTimeout = ini_get('default_socket_timeout');
			ini_set('default_socket_timeout', 30); // 30 segundos máximo
			
			try {
				$numberingHelper = NumberHelper::getInstance ($adb, $current_user);
				
				// Consulta optimizada - separar COUNT de datos principales
				$countQuery = array (
					'select'     => 'COUNT(*) as total',
					'from'       => 'vtiger_proyectos pr',
					'inner_join' => "INNER JOIN vtiger_crmentity crm ON crm.crmid = pr.proyectosid",
					'where'	    => "crm.deleted = 0 AND
									pr.etapa !='Terminado' AND
									validityRecordByDate(pr.fecha_de_inicio, '', crm.createdtime, '{$range ['startdate']}', '{$range ['enddate']}') = 1",
				);
				
				// Primero obtener el total
				$countResult = $adb->query("SELECT {$countQuery['select']} FROM {$countQuery['from']} {$countQuery['inner_join']} WHERE {$countQuery['where']}");
				$totalRecords = 0;
				if ($countResult && $adb->num_rows($countResult) > 0) {
					$totalRecords = intval($adb->query_result($countResult, 0, 'total'));
				}
				DatabaseUtils::closeResult($countResult);
				
				// Si no hay registros, retornar temprano
				if ($totalRecords == 0) {
					return null;
				}
				
				// Consulta principal optimizada sin CROSS JOIN
				$query = array (
					'select'     => 'crm.crmid, crm.smcreatorid, crm.smownerid,crm.setype,
									CONCAT(user.first_name, " ", user.last_name) AS username, user.imagename,
									pr.porcentaje_de_avance_genera,
									pr.proyectosid, pr.nombre, pr.fecha_de_inicio, pr.etapa,
									IF(ISNULL(pr.fecha_de_inicio), pr.est_start_date, pr.fecha_de_inicio) AS date_start, pr.est_end_date AS fecha_de_terminacion',
					'from'       => 'vtiger_proyectos pr',
					'inner_join' => "INNER JOIN vtiger_crmentity crm ON crm.crmid = pr.proyectosid
									LEFT JOIN vtiger_users user ON user.id = crm.smownerid",
					'where'	    => "crm.deleted = 0 AND
									pr.etapa !='Terminado' AND
									validityRecordByDate(pr.fecha_de_inicio, '', crm.createdtime, '{$range ['startdate']}', '{$range ['enddate']}') = 1",
				);
				
				$recordsPerPage = self::$recordPerPage;
				$result = $adb->query (
					"SELECT
							{$query ['select']}
						FROM
							{$query ['from']}
							{$query ['inner_join']}
						WHERE
							{$query ['where']}
						ORDER BY pr.fecha_de_inicio DESC
						LIMIT {$starRecord}, {$recordsPerPage}"
				);
				
				$projectsInProgress = array();
				if ($adb->num_rows ($result) > 0) {
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						// Verificar permisos de acceso (incluyendo Sharing Rules)
						$crmData = array(
							'crmid' => $row['crmid'],
							'smownerid' => $row['smownerid'],
							'smcreatorid' => $row['smcreatorid']
						);
						if (!self::hasAccess($userId, 'proyectos', $crmData)) {
							continue;
						}
						
						$row ['help_data']                   = HowToHelper::hasHowTo ($adb, 'proyectos', $row ['crmid'], 'DetailView_Task');
						$row ['porcentaje_de_avance_genera'] = $numberingHelper->setNumberFormat ($row ['porcentaje_de_avance_genera'], 'porcentaje_de_avance_genera');
						self::normalizeDateField($row, 'date_start');
						self::normalizeDateField($row, 'fecha_de_inicio');
						self::normalizeDateField($row, 'fecha_de_terminacion');
						$projectsInProgress [] = $row;
					}
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				
				return (!empty ($projectsInProgress)) ? array($projectsInProgress, $totalRecords) : null;
				
			} catch (Exception $e) {
				// Log del error y retorno seguro
				error_log("Error en fetchProjectsInProgress: " . $e->getMessage());
				return null;
			} finally {
				// Restaurar timeout original
				ini_set('default_socket_timeout', $originalTimeout);
			}
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 * @param array|string $periodTime
		 * @param boolean $ajax
		 * @param integer|string|null $userId
		 *
		 * @return false|string|null
		 * @throws SmartyException
		 */
		public static function fetchActivityReport ($adb, $current_user, $periodTime, $ajax = false, $userId = null) {
			if (empty ($periodTime) || empty ($current_user) || !$current_user instanceof Users) {
				return null;
			}
			global $mod_strings;
			if (empty ($userId)) {
				$userId = $current_user->id;
				$users  =  array ($userId);
			} else {
				$users = $userId;
			}
			if (is_scalar ($periodTime)) {
				$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodTime);
			} else {
				$periodDates = $periodTime;
			}
			$activityWorks  = self::fetchActivityWork ($adb, $userId, $periodDates);
			$activityTasks  = self::fetchActivityTask ($adb, $userId, $periodDates);
			self::mergeActivities ($activityWorks, $activityTasks);
			$totalsQuadrants  = self::getTotalsByQuadrant ($activityWorks);
			$timeByQuadrant   = self::getTimeByQuadrant ($activityWorks);
			/* Horas laborables totales */
			$regularWorkingHours = WorkingDayUtils::getRegularWorkingHours ($adb, $current_user->id);
			$workingDays         = WorkingDayUtils::getWorkingDays ($periodDates['startdate'], $periodDates ['enddate']);
			$workingDays         = ($workingDays == 0) ? 1 : $workingDays;
			$totalHoursWorked    = ($regularWorkingHours * $workingDays);
			$tienTasks           = self::getTotalHoursReported ($adb, $userId, $periodDates);
			$totalHoursDaily     = self::getTotalHoursDailyReport ($adb, $userId, $periodDates);
			$totalHoursReported  = (is_array ($tienTasks)) ? ($tienTasks[0] + $totalHoursDaily) : $totalHoursDaily;
			$estimatedTime       = (is_array ($tienTasks)) ? $tienTasks[1] : 0;
			/* El tiempo usado y horas reportadas como trabajadas*/
			//$reportTime = $timeByQuadrant [6];
			$extraHours = DailyReportUtils::getOvertimeWorked ($adb, $periodDates, $users);
			$isOverTime = false;
			$barMax     = 100;
			$barWidth   = 1;
			if (!empty ($totalHoursReported) && $estimatedTime> 0) {
				$barWidth   = floor ((($totalHoursReported * 100)/$estimatedTime));
				if (($barWidth > 100)) {
					$barMax     = $barWidth;
					$barWidth   = 100;
					$isOverTime = true;
					$overTime   = ($barMax - 100);
				}
			}
			$reportedDays      = DailyReportUtils::fetchDailyReportDateByUser ($adb, $current_user->id);
			$today             = date ('Y-m-d');
			$yesterday         = date ('Y-m-d',strtotime('-1 days'));
			$reportToDay       = "{$today}@{$userId}";
			$reportToYesterday = "{$yesterday}@{$userId}";
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ACHIEVEMENTS', DailyReportUtils::fetchAchievements ($adb, $periodDates, $users));
			$smarty->assign ('ACTIVITY_WORKS', $activityWorks);
			$smarty->assign ('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar ($adb, $current_user));
			$smarty->assign ('ESTIMATED_TIME', $estimatedTime);
			$smarty->assign ('EXTENDS_CLASS', ($ajax) ? 'QuadrantsReportLayout' : 'ActivityReportLayout');
			$smarty->assign ('ACTIVITY_TAB_ID', rand (1000, 99999));
			$smarty->assign ('EXTRA_HOURS', $extraHours);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('MODULE', 'Home');
			$smarty->assign ('OVER_TIME', $overTime);
			$smarty->assign ('PERIOD_DATES', NotificationPeriodUtils::getAvailablePeriods ());
			$smarty->assign ('PERIOD_SELECTED', $periodTime);
			$smarty->assign ('PROBLEMS', DailyReportUtils::fetchAdditionalInformation ($adb, $periodDates, $users, array ('Noticias', 'Sugerencias') ));
			$smarty->assign ('PROGRESS_BAR_MAX', $barMax);
			$smarty->assign ('PROGRESS_BAR_OVER', $isOverTime);
			$smarty->assign ('PROGRESS_BAR_WIDTH', $barWidth);
			$smarty->assign ('QUADRANTS', self::QUADRANTS);
			$smarty->assign ('REPORTED_DAYS', (is_array ($reportedDays)) ? join (';', $reportedDays) : null);
			$smarty->assign ('REPORTED_HOURS', $totalHoursReported);
			$smarty->assign ('REPORT_TODAY', base64_encode ($reportToDay));
			$smarty->assign ('REPORT_YESTERDAY',  base64_encode ($reportToYesterday));
			$smarty->assign ('SUGGESTIONS_NEWS', DailyReportUtils::fetchAdditionalInformation ($adb, $periodDates, $users));
			$smarty->assign ('TODAY', $today);
			$smarty->assign ('TOTALS_ESTIMATED', $timeByQuadrant);
			$smarty->assign ('TOTALS_QUADRANTS', $totalsQuadrants);
			$smarty->assign ('USER_IDS', $users);
			$smarty->assign ('USER_NAME', ($current_user->first_name . ' ' . $current_user->last_name));
			$smarty->assign ('WORKED_HOURS', $totalHoursWorked);
			$smarty->assign ('YESTERDAY', $yesterday);
			return $smarty->fetch ('Home/ActionTabs/ActivityReport.tpl');
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param integer|array $userId
		 * @param array $range
		 *
		 * @return array|null
		 */
		public static function fetchActivityTask ($adb, $userId, $range) {
			if (is_array($userId)) {
				$whereUsers = "(crm.smcreatorid IN{$adb->sql_expr_datalist ($userId)}) AND";
			} else {
				$whereUsers = "(crm.smcreatorid IN({$userId})) AND";
			}
			$result = $adb->pquery (
				"SELECT
					crm.crmid,
					crm.smcreatorid,
					crm.smownerid,
       				CONCAT(user.first_name, ' ', user.last_name) AS username,
       				crm.setype AS tab_name,
					task.activityid AS entity_id,
			       	task.date_start,
       				task.due_date,
       				task.eventstatus,
					task.combined_condition,
       				task.importance,
                    IF(ISNULL(task.estimated_time), 0, task.estimated_time) AS _time,
       				IF((ISNULL(task.priority) OR task.priority=''), 'BAJO', UPPER(task.priority)) AS priority,
       				IF((ISNULL(task.subject) OR task.subject=''), crm.description,task.subject) AS title
				FROM
					vtiger_activity task
				INNER JOIN vtiger_crmentity crm ON crm.crmid = task.activityid
				INNER JOIN vtiger_users user ON user.id = crm.smcreatorid
				WHERE
					crm.deleted = ? AND
					{$whereUsers}
				    NOT EXISTS (SELECT setype FROM vtiger_crmentity  WHERE  setype = ? AND crmid = task.related_id) AND
					taskToMatrix(task.date_start, task.due_date, task.eventstatus, crm.modifiedtime, ?, ?) = ?",
				array (0, 'orden_de_trabajo', $range ['startdate'], $range ['enddate'], 1)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$relatedModuleData     = self::getTaskRelateModule ($adb, $row ['entity_id']);
					if (!empty ($relatedModuleData)) {
						$row ['tab_label']   = $relatedModuleData ['tablabel'];
						$row ['module_name'] = $relatedModuleData ['name'];
						$row ['tab_id']      = $relatedModuleData ['crmid'];
					} else {
						$row ['tab_label']   = null;
						$row ['module_name'] = null;
						$row ['tab_id']      = null;
					}
					$row ['duration_time'] = self::getReportTimeByActivity ($adb, $row ['entity_id'], $userId);
					$quadrant = self::PRIORITY_TRANSLATE[ $row['priority'] ] . '-' . $row['importance'];
					$activityTask [ $quadrant ][]    = $row;
				}
				
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($activityTask)) ? $activityTask : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer|array $userId
		 * @param array $range
		 *
		 * @return array|null
		 */
		public static function fetchActivityWork ($adb, $userId, $range) {
			$dummy = (is_array ($userId)) ? $userId : explode (',', $userId);
			if (in_array (1, $dummy)) {
				$whereUsers = "";
			}else {
				$whereUsers = "crm.smownerid IN{$adb->sql_expr_datalist ($dummy)} AND";
			}
			$result = $adb->pquery (
				"SELECT
				    crm.crmid,
				    crm.smcreatorid,
				    crm.smownerid,
       				crm.setype AS tab_name,
				    ot.orden_de_trabajoid AS entity_id,
				    ot.titulo AS title,
       				ot.importance_work,
       				ot.work_priority,
       				ot.priority_index,ot.fecha_de_inicio,ot.fecha_real_de_ci, crm.modifiedtime
				FROM
				    vtiger_orden_de_trabajo ot
				INNER JOIN vtiger_crmentity crm ON crm.crmid = ot.orden_de_trabajoid
				WHERE
				    {$whereUsers}
				    crm.deleted = ? AND
				    ot.estado_de_la_orden != ? AND
				      validityRecordByDate (ot.fecha_de_inicio, ot.fecha_real_de_ci, crm.modifiedtime, ?, ?) = ?
				ORDER by ot.orden_de_trabajoid ASC",
				array (0,'Terminado', $range ['startdate'], $range ['enddate'], 1)
			);
			
			if ($adb->num_rows ($result) > 0) {
				$totalTask = 0;
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$priorityIndex         = floatval ($row ['priority_index']);
					$estimatedTime         = self::getEstimatedTimeByJob ($adb, $row ['entity_id'], $userId);
					$row ['_time']         = $estimatedTime ['estimated_time'];
					$row ['duration_time'] = $estimatedTime ['duration_time'];
					$totalTask            += $estimatedTime ['total_tasks'];
					if ($priorityIndex >= 60) {
						$activityWork [ self::QUADRANTS[0] ][]    = $row;
					} elseif ($priorityIndex >= 30 && $priorityIndex <= 50) {
						$activityWork [ self::QUADRANTS[1] ][]    = $row;
					} elseif ($priorityIndex > 10 && $priorityIndex <= 20) {
						$activityWork [ self::QUADRANTS[2] ][]    = $row;
					} else {
						$activityWork [ self::QUADRANTS[3] ][]    = $row;
					}
				}
				$activityWork ['total_tasks'] = $totalTask;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($activityWork)) ? $activityWork : null;
		}
		
		/**
		 * @param string $tab
		 * @param array $data
		 *
		 * @return array|null
		 */
		public static function fetchCalendarViewData ($tab, $data, $calendarType) {
			if ($tab == 'WORK_IN_PROGRESS') {
				$keyArray = array(
					'id'              => 'activityid',
					'crmid'           => 'crmid',
					'backgroundColor' => 'event_status',
					'borderColor'     => '#000000',
					'end'             => 'due_date',
					'start'           => 'date_start',
					'progress'        => 'progress',
					'textColor'       => '#000000',
					'title'           => 'subject',
					'taskStatus'      => 'eventstatus',
					'url'             => array ('tab_name', 'crmid')
				);
			} else if ($tab == 'PROJECT_IN_PROGRESS') {
				$keyArray = array(
					'id'              => 'activityid',
					'crmid'           => 'crmid',
					'backgroundColor' => 'event_status',
					'borderColor'     => '#000000',
					'end'             => 'fecha_de_terminacion',
					'start'           => 'date_start',
					'progress'        => 'progress',
					'textColor'       => '#000000',
					'title'           => 'nombre',
					'taskStatus'      => 'eventstatus',
					'url'             => array ('setype', 'crmid')
				);
			} else if ($tab == 'ORDERS_TO_PROCESSED') {
				$keyArray = array(
					'id'              => 'RND',
					'crmid'           => 'crmid',
					'backgroundColor' => 'estado_del_pedid',
					'borderColor'     => '#000000',
					'end'             => 'fecha_terminacion',
					'start'           => 'fecha_pedido',
					'progress'        => 'progress',
					'textColor'       => '#000000',
					'title'           => 'titulo',
					'taskStatus'      => 'estado_del_pedid',
					'url'             => array ('setype', 'crmid')
				);
			} else if ($tab == 'ISSUES_TO_PROCESSED') {
				$keyArray = array(
					'id'              => 'RND',
					'crmid'           => 'crmid',
					'backgroundColor' => 'estado_incidencia',
					'borderColor'     => '#000000',
					'end'             => 'fecha_de_cierre',
					'start'           => 'fecha_de_origen',
					'progress'        => 'progress',
					'textColor'       => '#000000',
					'title'           => 'titulo',
					'taskStatus'      => 'estado_incidencia',
					'url'             => array ('setype', 'crmid')
				);
			} else if ($tab == 'OPPORTUNITIES_TO_PROCESSED') {
				$keyArray = array(
					'id'              => 'RND',
					'crmid'           => 'crmid',
					'backgroundColor' => 'fase_de_venta',
					'borderColor'     => '#000000',
					'end'             => 'fecha_cierre_oportunidad',
					'start'           => 'fecha_oportunidad',
					'progress'        => 'progress',
					'textColor'       => '#000000',
					'title'           => 'titulo',
					'taskStatus'      => 'fase_de_venta',
					'url'             => array ('setype', 'crmid')
				);
			} else if ($tab == 'WORK_TO_PROCESSED') {
				$keyArray = array(
					'id'              => 'RND',
					'crmid'           => 'crmid',
					'backgroundColor' => 'estado_de_la_orden',
					'borderColor'     => '#000000',
					'end'             => 'due_date',
					'start'           => 'date_start',
					'progress'        => 'progress',
					'textColor'       => '#000000',
					'title'           => 'titulo',
					'taskStatus'      => 'estado_de_la_orden',
					'url'             => array ('setype', 'crmid')
				);
			} else if ($tab == 'ACTONS_IN_PROGRESS') {
				$keyArray = array(
					'id'              => 'activityid',
					'crmid'           => 'crmid',
					'backgroundColor' => 'event_status',
					'borderColor'     => '#000000',
					'end'             => 'due_date',
					'start'           => 'date_start',
					'progress'        => 'progress',
					'textColor'       => '#000000',
					'title'           => 'subject',
					'taskStatus'      => 'eventstatus',
					'url'             => array ('module_name', 'crmid')
				);
			} else if ($tab == 'CALENDAR_VIEW') {
				$keyArray = array(
					'id'              => 'activityid',
					'crmid'           => 'related_id',
					'backgroundColor' => 'eventstatus',
					'borderColor'     => '#000000',
					'end'             => 'due_date',
					'start'           => 'date_start',
					'progress'        => 'progress',
					'textColor'       => '#000000',
					'title'           => 'subject',
					'taskStatus'      => 'eventstatus',
					'url'             => array ('related_to', 'related_id')
				);
			} else {
				return null;
			}
			return self::getCalendarData ($keyArray, $data, $calendarType);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer|string $userId
		 * @param array $range
		 * @param integer $starRecord
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchIssuesToProcessed ($adb, $userId, $range, $starRecord) {
			$query = array (
				'select'     => 'crm.crmid, crm.smcreatorid, crm.smownerid,crm.createdtime,DATE(crm.createdtime) AS date_start,
								CONCAT(user.first_name, " ", user.last_name) AS username, user.imagename,
								inc.cod_incidencias, inc.titulo, inc.prioridad, inc.fecha_de_origen, inc.type_of_matter,
								IF(ISNULL(inc.fecha_de_origen), DATE(crm.createdtime),inc.fecha_de_origen) AS date_start,
								inc.estado_incidencia, inc.incidenciasid, inc.fecha_de_cierre',
				'from'       => 'vtiger_incidencias inc',
				'inner_join' => "INNER JOIN vtiger_crmentity crm ON crm.crmid = inc.incidenciasid
								 LEFT JOIN vtiger_users user ON user.id = crm.smownerid",
				'where'	    => "crm.deleted = 0 AND
								validityRecordByDate(inc.fecha_de_origen, inc.fecha_de_cierre, crm.createdtime, '{$range ['startdate']}', '{$range ['enddate']}') = 1",
			);
			$recordsPerPage = self::$recordPerPage;
			$result = $adb->query (
				"SELECT
					{$query ['select']},
					total.__total_records__
				FROM
					{$query ['from']}
					{$query ['inner_join']}
				CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM {$query ['from']}  {$query ['inner_join']} WHERE {$query ['where']}) AS total
				WHERE
					{$query ['where']}
				LIMIT {$starRecord}, {$recordsPerPage}"
			);
			$issuesToProcessed = array();
			$totalRecords = 0;
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					// Verificar permisos de acceso (incluyendo Sharing Rules)
					$crmData = array(
						'crmid' => $row['crmid'],
						'smownerid' => $row['smownerid'],
						'smcreatorid' => $row['smcreatorid']
					);
					if (!self::hasAccess($userId, 'incidencias', $crmData)) {
						continue;
					}
					
					if ($totalRecords === 0) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					unset ($row ['__total_records__']);
					
					$row ['help_data']    = HowToHelper::hasHowTo ($adb, 'incidencias', $row ['crmid'], 'DetailView_Task');
					self::normalizeDateField($row, 'date_start');
					self::normalizeDateField($row, 'fecha_de_origen');
					self::normalizeDateField($row, 'fecha_de_cierre');
					$issuesToProcessed [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (!empty ($issuesToProcessed)) ? array($issuesToProcessed, $totalRecords) : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer|string $userId
		 * @param array $range
		 * @param integer $starRecord
		 * @param Users $user
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchOpportunitiesToProcessed ($adb, $userId, $range, $starRecord, $user) {
			$numberingHelper = NumberHelper::getInstance ($adb, $user);
			$query = array (
				'select'     => 'crm.crmid, crm.smcreatorid, crm.smownerid,crm.setype,crm.createdtime,DATE(crm.createdtime) AS date_start,
								CONCAT(user.first_name, " ", user.last_name) AS username, user.imagename,
								 opo.cod_oportunidade, opo.titulo,opo.valor_oportunidad,opo.oportunidadesid,opo.fase_de_venta,
								 opo.fecha_oportunidad,opo.fecha_cierre_oportunidad',
				'from'       => 'vtiger_oportunidades opo',
				'inner_join' => "INNER JOIN vtiger_crmentity crm ON crm.crmid = opo.oportunidadesid
								LEFT JOIN vtiger_users user ON user.id = crm.smownerid",
				'where'	    => "crm.deleted = 0 AND
								validityRecordByDate(opo.fecha_oportunidad, opo.fecha_cierre_oportunidad, crm.createdtime, '{$range ['startdate']}', '{$range ['enddate']}') = 1",
			);
			$recordsPerPage = self::$recordPerPage;
			$result = $adb->query (
				"SELECT
					{$query ['select']},
					total.__total_records__
				FROM
					{$query ['from']}
					{$query ['inner_join']}
				CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM {$query ['from']}  {$query ['inner_join']} WHERE {$query ['where']}) AS total
				WHERE
					{$query ['where']}
				LIMIT {$starRecord}, {$recordsPerPage}"
			);
			
			$pportunitiesToProcessed = array();
			$totalRecords = 0;
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					// Verificar permisos de acceso (incluyendo Sharing Rules)
					$crmData = array(
						'crmid' => $row['crmid'],
						'smownerid' => $row['smownerid'],
						'smcreatorid' => $row['smcreatorid']
					);
					if (!self::hasAccess($userId, 'oportunidades', $crmData)) {
						continue;
					}
					
					if ($totalRecords === 0) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					unset ($row ['__total_records__']);
					
					$row ['valor_oportunidad'] = $numberingHelper->setNumberFormat ($row ['valor_oportunidad'], 'valor_oportunidad');
					self::normalizeDateField($row, 'date_start');
					self::normalizeDateField($row, 'fecha_oportunidad');
					self::normalizeDateField($row, 'fecha_cierre_oportunidad');
					$pportunitiesToProcessed [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (!empty ($pportunitiesToProcessed)) ? array($pportunitiesToProcessed, $totalRecords) : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer|string $userId
		 * @param array $range
		 * @param integer $starRecord
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchOrdersToProcessed ($adb, $userId, $range, $starRecord) {
			$query = array (
				'select'     => 'crm.crmid, crm.smcreatorid, crm.smownerid,crm.setype,crm.createdtime,DATE(crm.createdtime) AS date_start,
								CONCAT(user.first_name, " ", user.last_name) AS username, user.imagename,
								pe.cod_pedidos,pe.titulo, pe.que_o_quien_origina_el_pedid,
								pe.pedidosid, pe.prioridad_del_pe, pe.estado_del_pedid,pe.fecha_pedido,pe.fecha_terminacion',
				'from'       => 'vtiger_pedidos pe',
				'inner_join' => "INNER JOIN vtiger_crmentity crm ON crm.crmid = pe.pedidosid
								LEFT JOIN vtiger_users user ON user.id = crm.smownerid",
				'where'	    => "crm.deleted = 0 AND
								validityRecordByDate(pe.fecha_pedido, pe.fecha_terminacion, crm.createdtime, '{$range ['startdate']}', '{$range ['enddate']}') = 1",
			);
			$recordsPerPage = self::$recordPerPage;
			$result = $adb->query (
				"SELECT
					{$query ['select']},
					total.__total_records__
				FROM
					{$query ['from']}
					{$query ['inner_join']}
				CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM {$query ['from']}  {$query ['inner_join']} WHERE {$query ['where']}) AS total
				WHERE
					{$query ['where']}
				LIMIT {$starRecord}, {$recordsPerPage}"
			);
			
			$ordersToProcessed = array();
			$totalRecords = 0;
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					// Verificar permisos de acceso (incluyendo Sharing Rules)
					$crmData = array(
						'crmid' => $row['crmid'],
						'smownerid' => $row['smownerid'],
						'smcreatorid' => $row['smcreatorid']
					);
					if (!self::hasAccess($userId, 'pedidos', $crmData)) {
						continue;
					}
					
					if ($totalRecords === 0) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					unset ($row ['__total_records__']);
					
					$row ['help_data']    = HowToHelper::hasHowTo ($adb, 'pedidos', $row ['crmid'], 'DetailView_Task');
					self::normalizeDateField($row, 'date_start');
					self::normalizeDateField($row, 'fecha_pedido');
					self::normalizeDateField($row, 'fecha_terminacion');
					$ordersToProcessed [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (!empty ($ordersToProcessed)) ? array($ordersToProcessed, $totalRecords) : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param Users $current_user
		 * @param array $periodTime
		 *
		 * @return false|string|null
		 * @throws SmartyException
		 */
		public static function fetchToBeProcessed ($adb, $current_user, $periodTime) {
			if (empty ($periodTime) || empty ($current_user) || !$current_user instanceof Users) {
				return null;
			}
			$periodDates    = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodTime);
			/** @var  $ordersToProcessed */
			$ordersToProcessed = self::fetchOrdersToProcessed ($adb, $current_user->id, $periodDates,self::START_RECORD);
			$ordersPaginator   = null;
			$ordersTabId  	   =  rand (1000, 10000);
			if (!empty ($ordersToProcessed)) {
				$paginator       = self::configPaginator ($ordersToProcessed[1], $ordersTabId);
				$ordersPaginator = $paginator->createLinks ();
			}
			/** @var  $issuesToProcessed */
			$issuesToProcessed = self::fetchIssuesToProcessed ($adb, $current_user->id, $periodDates,self::START_RECORD);
			$issuesPaginator   = null;
			$issuesTabId  	   =  rand (1000, 10000);
			if (!empty ($issuesToProcessed)) {
				$paginator       = self::configPaginator ($issuesToProcessed[1], $issuesTabId);
				$issuesPaginator = $paginator->createLinks ();
			}
			/** @var  $opportunitiesToProcessed */
			$oportunitiesToProcessed = self::fetchOpportunitiesToProcessed ($adb, $current_user->id, $periodDates,self::START_RECORD, $current_user);
			$pportunitiesPaginator   = null;
			$pportunitiesTabId  	   =  rand (1000, 10000);
			if (!empty ($oportunitiesToProcessed)) {
				$paginator       = self::configPaginator ($oportunitiesToProcessed[1], $pportunitiesTabId);
				$pportunitiesPaginator = $paginator->createLinks ();
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_USERS', DataViewUtils::getAvailableUserAndAvatar ($adb, $current_user));
			$smarty->assign ('ISSUES_TABLE_HEADER', self::ISSUES_TABLE_HEADER);
			$smarty->assign ('ISSUES_TABLE_ROW', self::ISSUES_TABLE_ROW);
			$smarty->assign ('ISSUES_PAGER', $issuesPaginator);
			$smarty->assign ('ISSUES_TAB_ID', $issuesTabId);
			$smarty->assign ('ISSUES_TOTAL_ROWS', (isset ($issuesToProcessed[1])) ? $issuesToProcessed[1] : 0);
			$smarty->assign ('ISSUES_TO_PROCESSED', (isset ($issuesToProcessed[0])) ? $issuesToProcessed[0] : null);
			$smarty->assign ('MODULE', 'Home');
			$smarty->assign ('MODULE_NAME', array_keys (self::TO_PROCESSED_MODULES));
			$smarty->assign ('MODULE_TABS', self::TO_PROCESSED_MODULES);
			$smarty->assign ('OPPORTUNITIES_PAGER', $pportunitiesPaginator);
			$smarty->assign ('OPPORTUNITIES_TABLE_HEADER', self::OPPORTUNITIES_TABLE_HEADER);
			$smarty->assign ('OPPORTUNITIES_TABLE_ROW', self::OPPORTUNITIES_TABLE_ROW);
			$smarty->assign ('OPPORTUNITIES_TAB_ID', $pportunitiesTabId);
			$smarty->assign ('OPPORTUNITIES_TOTAL_ROWS', (isset ($oportunitiesToProcessed[1])) ? $oportunitiesToProcessed[1] : 0);
			$smarty->assign ('OPPORTUNITIES_TO_PROCESSED', (isset ($oportunitiesToProcessed[0])) ? $oportunitiesToProcessed[0] : null);
			$smarty->assign ('ORDERS_TABLE_HEADER', self::ORDERS_TABLE_HEADER);
			$smarty->assign ('ORDERS_TABLE_ROW', self::ORDERS_TABLE_ROW);
			$smarty->assign ('ORDERS_PAGER', $ordersPaginator);
			$smarty->assign ('ORDERS_TAB_ID', $ordersTabId);
			$smarty->assign ('ORDERS_TOTAL_ROWS', (!empty ($ordersToProcessed)) ? $ordersToProcessed[1] : 0);
			$smarty->assign ('ORDERS_TO_PROCESSED', (!empty ($ordersToProcessed)) ? $ordersToProcessed[0] : null);
			$smarty->assign ('PERIOD_DATES', NotificationPeriodUtils::getAvailablePeriods ());
			$smarty->assign ('PERIOD_SELECTED', $periodTime);
			$smarty->assign ('RECORDS_PER_PAGE', self::RECORDS_PER_PAGE);
			$smarty->assign ('SELECTED_MODULE', 'pedidos');
			$smarty->assign ('START_RECORD', (self::START_RECORD + 1));
			$smarty->assign ('URL_AVATARS', "{$_SESSION ['plat']}/user_images");
			$smarty->assign ('USER_IDS', array ($current_user->id));
			$smarty->assign ('USER_NAME', ($current_user->first_name . ' ' . $current_user->last_name));
			return $smarty->fetch ('Home/ActionTabs/ToBeProcessed.tpl');
		}
		
		public static function fetchToBeValidated () {}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param array $range
		 * @param integer $starRecord
		 *
		 * @return array|null
		 */
		public static function fetchWorkToProcessed ($adb, $userId, $range, $starRecord) {
			$query = array (
				'select'     => 'crm.crmid, crm.smcreatorid, crm.smownerid,crm.setype,crm.createdtime,
									CONCAT(user.first_name, " ", user.last_name) AS username, user.imagename,
									ot.cod_orden_de_tra,ot.titulo, ot.fecha_de_inicio AS date_start,
									ot.orden_de_trabajoid, ot.estado_de_la_orden, ot.fecha_real_de_ci AS due_date',
				'from'       => 'vtiger_orden_de_trabajo ot',
				'inner_join' => "INNER JOIN vtiger_crmentity crm ON crm.crmid = ot.orden_de_trabajoid
									LEFT JOIN vtiger_users user ON user.id = crm.smownerid",
				'where'	    => "crm.deleted = 0 AND
									validityRecordByDate(ot.fecha_de_inicio, ot.fecha_real_de_ci, crm.createdtime, '{$range ['startdate']}', '{$range ['enddate']}') = 1",
			);
					$recordsPerPage = self::$recordPerPage;
					
					$result = $adb->query (
						"SELECT
							{$query ['select']},
							total.__total_records__
						FROM
							{$query ['from']}
							{$query ['inner_join']}
						CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM {$query ['from']}  {$query ['inner_join']} WHERE {$query ['where']}) AS total
						WHERE
							{$query ['where']}
						LIMIT {$starRecord}, {$recordsPerPage}"
					);
					$workToProcessed = array();
					$totalRecords = 0;
					if ($adb->num_rows ($result) > 0) {
						while ($row = $adb->fetchByAssoc ($result, -1, false)) {
							// Verificar permisos de acceso (incluyendo Sharing Rules)
							$crmData = array(
								'crmid' => $row['crmid'],
								'smownerid' => $row['smownerid'],
								'smcreatorid' => $row['smcreatorid']
							);
							if (!self::hasAccess($userId, 'orden_de_trabajo', $crmData)) {
								continue;
							}
							
							if ($totalRecords === 0) {
								$totalRecords = intval ($row ['__total_records__']);
							}
							unset ($row ['__total_records__']);
							
							$workToProcessed [] = $row;
						}
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
					return (isset ($workToProcessed)) ? array($workToProcessed, $totalRecords) : null;
		}
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param array $range
		 * @param integer $startRecord
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchWorkInProgress ($adb, $userId, $range, $starRecord) {
			$modStrings = return_module_language ('es_es','Calendar');
			$query = array (
				'select' => 'crm.crmid, crm.smcreatorid, crm.smownerid, crm.description,crm.setype AS tab_name,
								CONCAT(user.first_name, " ", user.last_name) AS username, user.imagename,
								ot.orden_de_trabajoid AS entity_id, ot.orden_de_trabajoid, ot.titulo, ot.asociar_a, ot.cliente,
								ot.descripcion, ot.fecha_real_de_ci, ot.estado_de_la_orden, task.activityid, task.subject, task.activitytype,
								task.date_start, task.due_date, task.eventstatus, task.combined_condition',
				'from' => 'vtiger_crmentity crm',
				'inner_join' => "INNER JOIN vtiger_orden_de_trabajo ot ON crm.crmid = ot.orden_de_trabajoid
								INNER JOIN vtiger_activity task ON  task.related_id = ot.orden_de_trabajoid
								LEFT JOIN vtiger_users user ON user.id = crm.smownerid",
				'where' => "crm.deleted = 0 AND
							validityRecordByDate(task.date_start, task.due_date, crm.createdtime, '{$range ['startdate']}', '{$range ['enddate']}') = 1",
			);
			$recordsPerPage = self::$recordPerPage;
			$result = $adb->query (
			"SELECT
					{$query ['select']},
		       			total.__total_records__
				FROM
					{$query ['from']}
					{$query ['inner_join']}
				CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM {$query ['from']}  {$query ['inner_join']} WHERE {$query ['where']}) AS total
				WHERE
					{$query ['where']}
				ORDER BY
					entity_id, task.date_start ASC
				LIMIT {$starRecord}, {$recordsPerPage}"
		);
			$workInProgress = array();
			$totalRecords = 0;
			if ($adb->num_rows ($result) > 0) {
				$arm = ActivityReportManager::getInstance ($adb);
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					// Verificar permisos de acceso (incluyendo Sharing Rules)
					$crmData = array(
						'crmid' => $row['crmid'],
						'smownerid' => $row['smownerid'],
						'smcreatorid' => $row['smcreatorid']
					);
					if (!self::hasAccess($userId, 'orden_de_trabajo', $crmData)) {
						continue;
					}
					
					if ($totalRecords === 0) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					unset ($row ['__total_records__']);
					
					$row ['event_status'] = $row ['eventstatus'];
					$row ['eventstatus']  = $modStrings [$row ['eventstatus']];
					$row ['reports']      = self::getTotalReports ($adb, $row['activityid'],'work');
					$row ['feedbacks']    = self::getTotalFeeddBacks ($adb, $row['activityid'], 'work');
					$row ['project_data'] = self::getProjectFromWork ($adb, $row ['orden_de_trabajoid'], $userId);
					$row ['help_data']    = HowToHelper::hasHowTo ($adb, 'Calendar', $row ['activityid'], 'DetailView_Task');
					self::normalizeDateField($row, 'date_start');
					self::normalizeDateField($row, 'due_date');
					self::normalizeDateField($row, 'fecha_real_de_ci');
					$workInProgress []    = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (!empty ($workInProgress)) ? array ($workInProgress, $totalRecords) : null;
		}
		
		public static function getTotalPlannedTime ($adb, $userId, $range) {
			$result = $adb->pquery (
				'SELECT
					SUM(estimated_time) AS total_time
				FROM
					vtiger_activity task
				INNER JOIN vtiger_crmentity crm ON crm.crmid = task.activityid
				WHERE
					crm.deleted = ? AND
					(crm.smownerid IN(?)) AND
					taskToMatrix(task.date_start, task.due_date, task.eventstatus, crm.modifiedtime, ?, ?) = ?',
				array (0, $userId, $range ['startdate'], $range ['enddate'], 1)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$totalPlannedTime = $row['total_time'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($totalPlannedTime)) ? $totalPlannedTime : null;
		}
	}

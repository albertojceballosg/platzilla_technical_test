<?php
	require_once ('modules/operating_modes/lib/ManagementModeHelper.class.php');
	
	class SupplierPartOfWorkUtils extends ManagementModeHelper {
		
		const SUPPLIER_PART_WORK_TABLE_HEADER = array (
			'Tarea/actividad'         => array ('width' => '25', 'text_align' => 'left', 'class' => 'text-left', 'colspan' => '' ),
			'Trabajo'                 => array ('width' => '20', 'text_align' => 'left', 'class' => 'text-left', 'colspan' => '' ),
			'Proyecto'                => array ('width' => '20', 'text_align' => 'left', 'class' => 'text-left', 'colspan' => '' ),
			'Estado'                  => array ('width' => '10', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Fecha de<br>vencimiento' => array ('width' => '12', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '' ),
			'Operaciones'             => array ('width' => '13', 'text_align' => 'center', 'class' => 'text-center', 'colspan' => '2' ),
		);
		
		const SUPPLIER_PART_WORK_TABLE_ROW = array (
			'Tarea/actividad'      => array ('description', array ('','subject')),
			'Trabajo'              => array ('linkToDetailView', array ('','titulo', 'orden_de_trabajoid', 'orden_de_trabajo','string')),
			'Proyecto'             => array ('linkToDetailView', array ('','project_name', 'proyectoid', 'proyectos','string')),
			'Estado'               => array ('description', array ('','eventstatus')),
			'Fecha de vencimiento' => array ('description', array ('','due_date')),
			'Acciones1'            => array ('doReportAndFeedback', array ('','orden_de_trabajoid', 'activityid', 'orden_de_trabajo')),
			'Acciones2'            => array ('HelpOnRecord', array ('','help_data', 'orden_de_trabajoid', 'orden_de_trabajo')),
		);
		
		/** @var PearDatabase */
		private $adb;
		
		/**
		 * @param PearDatabase $adb
		 */
		public function __construct ($adb) {
			self::$recordPerPage = 1500;
			$this->adb = $adb;
		}
		
		/**
		 * Obtiene información del proveedor
		 * @param integer $supplierId
		 * @return array|null
		 */
		public function getSupplierInfo ($supplierId) {
			if (empty($supplierId)) {
				return null;
			}
			$result = $this->adb->pquery (
				"SELECT 
					p.proveedoresid,
					COALESCE(p.alias, p.nombre_de_la_sociedad) AS supplier_name,
					p.nombre_de_la_sociedad,
					p.alias,
					p.telefono,
					p.email
				FROM vtiger_proveedores p
				INNER JOIN vtiger_crmentity crm ON crm.crmid = p.proveedoresid
				WHERE crm.deleted = 0 AND p.proveedoresid = ?",
				array ($supplierId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$supplierInfo = $this->adb->fetchByAssoc ($result, -1, false);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($supplierInfo)) ? $supplierInfo : null;
		}
		
		/**
		 * Obtiene las tareas asignadas a un proveedor específico para el parte de trabajo
		 * @param integer $supplierId ID del proveedor
		 * @param array $range Rango de fechas
		 * @param integer $starRecord Registro inicial para paginación
		 * @return array|null
		 */
		public function fetchPartOfWorkBySupplier ($supplierId, $range, $starRecord) {
			if (empty($supplierId)) {
				return null;
			}
			
			$modStrings = return_module_language ('es_es', 'Calendar');
			
			$result = $this->adb->pquery (
				"SELECT
					crm.crmid,
					crm.smcreatorid,
					crm.smownerid,
					crm.setype AS tab_name,
					ot.orden_de_trabajoid AS entity_id,
					ot.orden_de_trabajoid,
					ot.titulo,
					ot.estado_de_la_orden,
					task.activityid,
					task.subject,
					task.activitytype,
					task.date_start,
					task.due_date,
					task.eventstatus,
					p.proveedoresid,
					COALESCE(p.alias, p.nombre_de_la_sociedad) AS supplier_name,
					proy.proyectosid AS proyectoid,
					proy.nombre AS project_name
				FROM vtiger_crmentity crm
				INNER JOIN vtiger_orden_de_trabajo ot ON crm.crmid = ot.orden_de_trabajoid
				INNER JOIN vtiger_activity task ON task.related_id = ot.orden_de_trabajoid
				INNER JOIN vtiger_crmentity crm_task ON crm_task.crmid = task.activityid AND crm_task.deleted = 0
				INNER JOIN vtiger_supplieractivityrel srel ON srel.activityid = task.activityid
				INNER JOIN vtiger_proveedores p ON p.proveedoresid = srel.proveedoresid
				LEFT JOIN vtiger_project_works pw ON pw.crmid_job = ot.orden_de_trabajoid
				LEFT JOIN vtiger_proyectos proy ON proy.proyectosid = pw.crmid
				LEFT JOIN vtiger_crmentity crm_proy ON crm_proy.crmid = proy.proyectosid AND crm_proy.deleted = 0
				WHERE
					crm.deleted = 0 AND
					srel.proveedoresid = ? AND
					validityRecordByDate(task.date_start, task.due_date, crm.createdtime, ?, ?) = 1
				ORDER BY task.due_date ASC, ot.orden_de_trabajoid",
				array ($supplierId, $range['startdate'], $range['enddate'])
			);
			
			$resultArray = array();
			$taskArray = array();
			
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					// Traducir estado
					$row['eventstatus'] = isset($modStrings[$row['eventstatus']]) ? $modStrings[$row['eventstatus']] : $row['eventstatus'];
					// Formatear fechas
					$row['date_start'] = self::formatDateForUser($row['date_start']);
					$row['due_date'] = self::formatDateForUser($row['due_date']);
					// Si no hay proyecto asociado, mostrar vacío
					if (empty($row['proyectoid'])) {
						$row['project_name'] = '';
						$row['proyectoid'] = '';
					}
					
					// Agregar cada tarea al resultado (sin agrupar)
					$resultArray[] = $row;
					
					// Agregar tarea al array de tareas por trabajo (para compatibilidad)
					$workId = $row['orden_de_trabajoid'];
					$taskArray[$workId][] = $row;
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			
			if (!empty($resultArray)) {
				$resultArray['task'] = $taskArray;
			}
			
			return !empty($resultArray) ? $resultArray : null;
		}
		
		/**
		 * Obtiene las tareas agrupadas por proyecto/trabajo para el PDF
		 * @param integer $supplierId ID del proveedor
		 * @param array $range Rango de fechas
		 * @return array|null
		 */
		public function fetchPartOfWorkBySupplierGrouped ($supplierId, $range) {
			if (empty($supplierId)) {
				return null;
			}
			
			$modStrings = return_module_language ('es_es', 'Calendar');
			
			$result = $this->adb->pquery (
				"SELECT
					crm.crmid,
					ot.orden_de_trabajoid,
					ot.titulo,
					task.activityid,
					task.subject,
					task.date_start,
					task.due_date,
					task.eventstatus,
					proy.proyectosid AS proyectoid,
					proy.nombre AS project_name
				FROM vtiger_crmentity crm
				INNER JOIN vtiger_orden_de_trabajo ot ON crm.crmid = ot.orden_de_trabajoid
				INNER JOIN vtiger_activity task ON task.related_id = ot.orden_de_trabajoid
				INNER JOIN vtiger_crmentity crm_task ON crm_task.crmid = task.activityid AND crm_task.deleted = 0
				INNER JOIN vtiger_supplieractivityrel srel ON srel.activityid = task.activityid
				LEFT JOIN vtiger_project_works pw ON pw.crmid_job = ot.orden_de_trabajoid
				LEFT JOIN vtiger_proyectos proy ON proy.proyectosid = pw.crmid
				LEFT JOIN vtiger_crmentity crm_proy ON crm_proy.crmid = proy.proyectosid AND crm_proy.deleted = 0
				WHERE
					crm.deleted = 0 AND
					srel.proveedoresid = ? AND
					validityRecordByDate(task.date_start, task.due_date, crm.createdtime, ?, ?) = 1
				ORDER BY proy.nombre ASC, ot.titulo ASC, task.date_start ASC, task.due_date ASC",
				array ($supplierId, $range['startdate'], $range['enddate'])
			);
			
			$groupedData = array();
			
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					// Traducir estado
					$row['eventstatus'] = isset($modStrings[$row['eventstatus']]) ? $modStrings[$row['eventstatus']] : $row['eventstatus'];
					// Formatear fechas
					$row['date_start_formatted'] = self::formatDateForUser($row['date_start']);
					$row['due_date_formatted'] = self::formatDateForUser($row['due_date']);
					
					// Crear clave de agrupación: proyecto_trabajo
					$projectId = !empty($row['proyectoid']) ? $row['proyectoid'] : 0;
					$workId = $row['orden_de_trabajoid'];
					$groupKey = $projectId . '_' . $workId;
					
					if (!isset($groupedData[$groupKey])) {
						$groupedData[$groupKey] = array(
							'project_id' => $projectId,
							'project_name' => !empty($row['project_name']) ? $row['project_name'] : '',
							'work_id' => $workId,
							'work_name' => $row['titulo'],
							'tasks' => array()
						);
					}
					
					$groupedData[$groupKey]['tasks'][] = array(
						'activityid' => $row['activityid'],
						'subject' => $row['subject'],
						'eventstatus' => $row['eventstatus'],
						'date_start' => $row['date_start_formatted'],
						'due_date' => $row['due_date_formatted']
					);
				}
			}
			
			DatabaseUtils::closeResult ($result);
			$result = null;
			
			return !empty($groupedData) ? array_values($groupedData) : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @return SupplierPartOfWorkUtils
		 */
		public static function getInstance ($adb) {
			return new self ($adb);
		}
	}

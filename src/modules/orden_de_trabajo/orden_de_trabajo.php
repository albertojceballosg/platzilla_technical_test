<?php
	require_once ('data/CRMEntity.php');
	require_once ('data/Tracker.php');
	require_once ('modules/orden_de_trabajo/handlers/taskToWork.class.php');

	class orden_de_trabajo extends CRMEntity {
		public $db;
		public $log;
		public $table_name = 'vtiger_orden_de_trabajo';
		public $table_index = 'orden_de_trabajoid';
		public $column_fields = array ();
		/** @var boolean Indicator if this is a custom module or standard module */
		public $IsCustomModule = true;
		/** @var array Mandatory table for supporting custom fields. */
		public $customFieldTable = array ('vtiger_orden_de_trabajocf', 'orden_de_trabajoid');
		/** @var array Mandatory for Saving, Include tables related to this module. */
		public $tab_name = array ('vtiger_crmentity', 'vtiger_orden_de_trabajo', 'vtiger_orden_de_trabajocf');
		/** @var array Mandatory for Saving, Include tablename and tablekey columnname here. */
		public $tab_name_index = array (
			'vtiger_crmentity'         => 'crmid',
			'vtiger_orden_de_trabajo'   => 'orden_de_trabajoid',
			'vtiger_orden_de_trabajocf' => 'orden_de_trabajoid',
		);
		/**
		 * @codingStandardsIgnoreStart
		 * Note: some line are in comment because they will be set later
		 * @var array Mandatory for Listing (Related listview)
		 * Format: Field Label => array(tablename, columnname)
		 * tablename should not have prefix 'vtiger_'
		 * 'Payslip Name'=> array('payslip', 'payslipname'),
		 * 'Assigned To' => array('crmentity','smownerid')
		 */
		public $list_fields = array (
			//'Código' => array ('orden_de_trabajo', 'cod_orden_de_tra'),
			'Título'         => array ('orden_de_trabajo', 'titulo'),
			'Descripción'    => array ('orden_de_trabajo', 'descripcion'),
			'Estado'         => array ('orden_de_trabajo', 'estado_de_la_orden'),
			'Tipo Actividad' => array ('orden_de_trabajo', 'tipo_dactividad'),
			'Coordinado por' => array ('orden_de_trabajo', 'coordinado_po'),
		);
		// @codingStandardsIgnoreEnd

		/**
		 * @var array Format: Field Label => fieldname
		 * 'Payslip Name'=> 'payslipname',
		 * 'Assigned To' => 'assigned_user_id'
		 */
		public $list_fields_name = array (
			'Código'         => 'cod_orden_de_tra',
			'Título'         => 'titulo',
			'Descripción'    => 'descripcion',
			'Estado'         => 'estado_de_la_orden',
			'Tipo Actividad' => 'tipo_dactividad',
			'Coordinado por' => 'coordinado_po',
			'Pedido'         => 'pedido',
			'Comentarios resultado' => 'comentarios_resultado'
		);
		/**
		 * @var string Make the field link to detail view from list view (Fieldname)
		 * $list_link_field = 'payslipname';
		 */
		public $list_link_field = 'titulo';
        
        /**
         * @var string Make the field link to detail view from list view alwys set
         * $defaultListLink = '';
         */
        public $defaultListLink = 'cod_orden_de_tra';
        
		/** @var array For Popup listview and UI type support.
		 * Format: Field Label => array (tablename, columnname).
		 * tablename should not have prefix 'vtiger_'
		 * 'Payslip Name'=> array('payslip', 'payslipname')
		 */
		public $search_fields = array (
			'Código' => array ('orden_de_trabajo', 'cod_orden_de_tra')
		);
		/**
		 * @var array Format: Field Label => fieldname
		 * 'Payslip Name'=> 'payslipname'
		 */
		public $search_fields_name = array (
			'Código' => 'cod_orden_de_tra'
		);
		/**
		 * @var array For Popup window record selection
		 * $popup_fields = array('payslipname');
		 */
		public $popup_fields = array ('cod_orden_de_tra');
		/** @var array Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields */
		public $sortby_fields = array ();
		/** @var string For Alphabetical search */
		public $def_basicsearch_col = 'cod_orden_de_tra';
		/** @var string Column value to use on detail view record text display */
		public $def_detailview_recname = 'cod_orden_de_tra';
		/**
		 * @var array Required Information for enabling Import feature
		 * $required_fields = array('payslipname'=>1);
		 */
		public $required_fields = array ('cod_orden_de_tra' => 1);
		/** @var array Callback function list during Importing */
		public $special_functions = array ('set_import_assigned_user');
		/** @var string */
		public $default_order_by = 'cod_orden_de_tra';
		/** @var string */
		public $default_sort_order = 'ASC';
		/**
		 * @var array Used when enabling/disabling the mandatory fields for the module.
		 * Refers to vtiger_field.fieldname values.
		 * $mandatory_fields = array('createdtime', 'modifiedtime', 'payslipname');
		 */
		public $mandatory_fields = array (array ('cod_orden_de_tra'));

		/** @var bool Flag para evitar recursiones al sincronizar trabajos con proyectos */
		private static $isProjectSyncInProgress = false;

		public function __construct () {
			global $log, $currentModule;
			$this->column_fields = getColumnFields ($currentModule);
			$this->db            = PearDatabase::getInstance ();
			$this->log           = $log;
		}

		private function getLeftJoinClauses ($moduleName) {
			$sql                = "SELECT DISTINCT
											fieldname,
											columnname,
											relmodule
										FROM
											vtiger_field
											INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid=vtiger_field.fieldid
										WHERE
											uitype='10' AND
											vtiger_fieldmodulerel.module=?";
			$linkedFieldsResult = $this->db->pquery ($sql, array ($moduleName));

			if (!$linkedFieldsResult) {
				return array ();
			}
			$leftJoinClauses   = array ();
			$linkedFieldsCount = $this->db->num_rows ($linkedFieldsResult);
			for ($i = 0; $i < $linkedFieldsCount; $i++) {
				$relatedModule = $this->db->query_result ($linkedFieldsResult, $i, 'relmodule');
				$columnName    = $this->db->query_result ($linkedFieldsResult, $i, 'columnname');
				$linkedModule  = CRMEntity::getInstance ($relatedModule);
				vtlib_setup_modulevars ($relatedModule, $linkedModule);
				$leftJoinClauses [] = "{$linkedModule->table_name} ON {$linkedModule->table_name}.{$linkedModule->table_index}={$this->table_name}.$columnName";
			}
			return (count ($leftJoinClauses) > 0) ? ' LEFT JOIN ' . join (' LEFT JOIN ', $leftJoinClauses) : '';
		}

		public function getSortOrder () {
			global $currentModule;
			$sortOrder = $this->default_sort_order;
			if ((isset ($_REQUEST ['sorder'])) && ($_REQUEST ['sorder'])) {
				$sortOrder = $this->db->sql_escape_string ($_REQUEST ['sorder']);
			} else if ((isset ($_SESSION ["{$currentModule}_Sort_Order"])) && ($_SESSION ["{$currentModule}_Sort_Order"])) {
				$sortOrder = $_SESSION ["{$currentModule}_Sort_Order"];
			}
			return $sortOrder;
		}

		public function getOrderBy () {
			global $currentModule;
			$orderBy = PerformancePrefs::getBoolean ('LISTVIEW_DEFAULT_SORTING', true) ? $this->default_order_by : '';
			if ((isset ($_REQUEST ['order_by'])) && ($_REQUEST ['order_by'])) {
				$orderBy = $this->db->sql_escape_string ($_REQUEST['order_by']);
			} else if ((isset ($_SESSION ["{$currentModule}_Order_By"])) && ($_SESSION ["{$currentModule}_Order_By"])) {
				$orderBy = $_SESSION ["{$currentModule}_Order_By"];
			}
			return $orderBy;
		}
		
		/**
		 * @param string $module
		 */
		public function save_module ($module) {
			global $adb, $current_user;
			try {
				taskToWork::getInstance ($adb)->saveTaskInWork ($this->id, $current_user, $this->column_fields ['titulo'], $this->mode);
			} catch (Exception $e) {
				$_SESSION ['flashmessage'] = array (
					'iserror' => true,
					'message' => $e->getMessage (),
				);
			}
			
			// TEMPORALMENTE DESHABILITADO - Causaba timeout de 120 segundos
			// TODO: Investigar por qué causa bucle infinito o timeout
			/*
			// Recalcular progreso y costos del trabajo basándose en las tareas relacionadas
			// Solo ejecutar en modo edición cuando el registro ya existe
			if (!empty($this->id) && $this->mode == 'edit') {
				try {
					require_once ('include/platzilla/Data/ActivityReportManager.php');
					$arm = ActivityReportManager::getInstance ($adb);
					
					// Recalcular overall_progress_perc
					$calculatedProgress = $arm->calculateProgress ($this->id);
					
					// Recalcular costos (estimado, ejecutado y varianza)
					$costs = $arm->calculateEntityCost ($this->id);
					
					// Actualizar campos en la base de datos
					$adb->pquery (
						'UPDATE vtiger_orden_de_trabajo 
						SET overall_progress_perc = ?,
							work_estimated_cost = ?,
							work_actual_cost = ?
						WHERE orden_de_trabajoid = ?',
						array (
							$calculatedProgress,
							$costs['estimated_cost'],
							$costs['actual_cost'],
							$this->id
						)
					);
					
					// Actualizar también en column_fields para que esté disponible en el mismo request
					$this->column_fields['overall_progress_perc'] = $calculatedProgress;
					$this->column_fields['work_estimated_cost'] = $costs['estimated_cost'];
					$this->column_fields['work_actual_cost'] = $costs['actual_cost'];
				} catch (Exception $e) {
					// Error silencioso - no interrumpir el guardado del trabajo
					error_log("[orden_de_trabajo] Error recalculando progreso/costos: " . $e->getMessage());
				}
			}
			*/
			
			$shouldHandleProjectRelation = !self::$isProjectSyncInProgress;
			if ($shouldHandleProjectRelation) {
				self::$isProjectSyncInProgress = true;
			}
			
			try {
				if ($shouldHandleProjectRelation) {
					require_once ('modules/proyectos/handlers/taskToProject.class.php');
					$projectHandler = taskToProject::getInstance ($adb);
					
					// Verificar si hay un proyecto relacionado en el campo 'proyecto'
					$projectId = !empty($this->column_fields['proyecto']) ? $this->column_fields['proyecto'] : null;
					
					if (!empty($projectId)) {
						// Verificar si ya existe la relación en vtiger_project_works
						$checkResult = $adb->pquery(
							'SELECT projectworksid FROM vtiger_project_works WHERE crmid = ? AND crmid_job = ?',
							array($projectId, $this->id)
						);
						
						if ($adb->num_rows($checkResult) == 0) {
							// No existe la relación, crearla
							require_once ('modules/proyectos/Objects/ProjectWorks.php');
							require_once ('include/utils/NumberHelper.class.php');
							
							$stageId = $this->resolveProjectStageId($adb, $projectId);
							if (!empty($stageId)) {
								$numberHelper = NumberHelper::getInstance($adb, $current_user);
								$percentageCompletion = !empty($this->column_fields['overall_progress_perc']) ? $this->column_fields['overall_progress_perc'] : 0;
								$percentageCompletion = $numberHelper->setSaveNumberFormat($percentageCompletion);
								
								$projectWork = ProjectWorks::getInstance()
									->setCrmId($projectId)
									->setCrmIdJob($this->id)
									->setJobName($this->column_fields['titulo'])
									->setStartDate(!empty($this->column_fields['fecha_de_inicio']) ? $this->column_fields['fecha_de_inicio'] : date('Y-m-d'))
									->setEstimatedDueDate(!empty($this->column_fields['fecha_prevista']) ? $this->column_fields['fecha_prevista'] : date('Y-m-d'))
									->setResponsibleJob(!empty($this->column_fields['assigned_user_id']) ? $this->column_fields['assigned_user_id'] : $current_user->id)
									->setJobContributionFactor(0) // Se puede ajustar manualmente después
									->setPercentageCompletion($percentageCompletion)
									->setProjectProgress(0)
									->setStageId($stageId)
									->setSummaryStr('N');
								
								$projectHandler->saveProjectWork($projectWork);
							}
						}
					}
					
					// Recalcular fechas estimadas y porcentaje de avance de los proyectos relacionados
					$projectIds = $projectHandler->getRelatedProjectIds ($this->id);
					foreach ($projectIds as $projectId) {
						$projectHandler->recalculateProjectEstimatedDatesFromDb ($projectId);
						// Recalcular el porcentaje de avance del proyecto
						$projectHandler->recalculateProjectProgress ($projectId);
						// Recalcular el costo estimado del proyecto (costo_total_estimad)
						$projectHandler->recalculateProjectEstimatedCost ($projectId);
					}
				}
			} catch (Exception $e) {
				// Error silencioso - no interrumpir el guardado del trabajo
			} finally {
				if ($shouldHandleProjectRelation) {
					self::$isProjectSyncInProgress = false;
				}
			}
			return;
		}

		/**
		 * Obtiene una etapa válida para asociar un trabajo con un proyecto
		 *
		 * @param PearDatabase $adb
		 * @param int $projectId
		 * @return int|null
		 */
		private function resolveProjectStageId ($adb, $projectId) {
			if (empty($projectId)) {
				return null;
			}
			
			// 1. Intentar reutilizar una etapa existente del proyecto
			$existingStageResult = $adb->pquery(
				'SELECT stageid FROM vtiger_project_works WHERE crmid = ? AND stageid IS NOT NULL ORDER BY projectworksid ASC LIMIT 1',
				array($projectId)
			);
			if ($existingStageResult && $adb->num_rows($existingStageResult) > 0) {
				$stageId = $adb->query_result($existingStageResult, 0, 'stageid');
				if (!empty($stageId)) {
					return intval($stageId);
				}
			}
			
			// 2. Usar la primera etapa disponible como fallback
			$defaultStageResult = $adb->pquery(
				'SELECT etapas_proyectoid FROM vtiger_etapas_proyecto ORDER BY etapas_proyectoid ASC LIMIT 1',
				array()
			);
			if ($defaultStageResult && $adb->num_rows($defaultStageResult) > 0) {
				$stageId = $adb->query_result($defaultStageResult, 0, 'etapas_proyectoid');
				if (!empty($stageId)) {
					return intval($stageId);
				}
			}
			
			return null;
		}

		/**
		 * Return query to use based on given modulename, fieldname
		 * Useful to handle specific case handling for Popup
		 *
		 * @param $module
		 * @param $fieldname
		 * @param $srcrecord
		 * @param string $query
		 */
		public function getQueryByModuleField ($module, $fieldname, $srcrecord, $query = '') {
			// $srcrecord could be empty
		}

		/**
		 * Get list view query (send more WHERE clause condition if required)
		 *
		 * @param $module
		 * @param string $additionalWhereClause
		 *
		 * @return string
		 * @throws Exception
		 */
		public function getListQuery ($module, $additionalWhereClause = '') {
			global $current_user;

			// Keep track of tables joined to avoid duplicates
			$joinedTables        = array (
				$this->table_name,
				'vtiger_crmentity',
				'vtiger_users',
				'vtiger_groups',
			);

			// Preparar cláusulas INNER JOIN con las tablas de campos personalizados (si existen)
			if (!empty ($this->customFieldTable)) {
				$customTableSelectClause = ", {$this->customFieldTable [0]}.*";
				$customTableJoinClause   = "INNER JOIN {$this->customFieldTable [0]} ON {$this->customFieldTable [0]}.{$this->customFieldTable [1]}={$this->table_name}.{$this->table_index}";
				$joinedTables []         = $this->customFieldTable [0];
			} else {
				$customTableSelectClause = '';
				$customTableJoinClause   = '';
			}

			// Obtener los módulos enlazados
			$sql                 = "SELECT DISTINCT
											fieldname,
											columnname,
											relmodule
										FROM
											vtiger_field
											INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid=vtiger_field.fieldid
										WHERE
											uitype='10' AND
											vtiger_fieldmodulerel.module=?";
			$linkedModulesResult = $this->db->pquery ($sql, array ($module));

			// Preparar cláusulas LEFT JOIN con los módulos enlazados
			$leftJoinClauses = array ();
			if ($linkedModulesResult) {
				$linkedFieldsCount = $this->db->num_rows ($linkedModulesResult);
				for ($i = 0; $i < $linkedFieldsCount; $i++) {
					$relatedModule = $this->db->query_result ($linkedModulesResult, $i, 'relmodule');
					$columnName    = $this->db->query_result ($linkedModulesResult, $i, 'columnname');
					$linkedEntity  = CRMEntity::getInstance ($relatedModule);
					vtlib_setup_modulevars ($relatedModule, $linkedEntity);
					if (!in_array ($linkedEntity->table_name, $joinedTables)) {
						$leftJoinClauses [] = "{$linkedEntity->table_name} ON {$linkedEntity->table_name}.{$linkedEntity->table_index}={$this->table_name}.$columnName";
						$joinedTables []    = $linkedEntity->table_name;
					}
				}
				$leftJoinClauses = (count ($leftJoinClauses) > 0) ? ' LEFT JOIN ' . join (' LEFT JOIN ', $leftJoinClauses) : '';
			}

			// Construir el código SQL de la consulta y retornarlo
			$sql = "SELECT
							vtiger_crmentity.*,
							{$this->table_name}.*
							$customTableSelectClause
						FROM
							{$this->table_name}
							INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid={$this->table_name}.{$this->table_index}
							$customTableJoinClause
							LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
							LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
							$leftJoinClauses
							{$this->getNonAdminAccessControlQuery ($module, $current_user)}
						WHERE
							vtiger_crmentity.deleted=0
							$additionalWhereClause";
			return trim (preg_replace ('/\s+/S', ' ', $sql));
		}

		/**
		 * Apply security restriction (sharing privilege) query part for List view.
		 *
		 * @param $module
		 *
		 * @return string
		 */
		public function getListViewSecurityParameter ($module) {
			$current_user_groups          = null;
			$current_user_parent_role_seq = null;
			$defaultOrgSharingPermission  = null;
			$is_admin                     = null;
			$profileGlobalPermission      = null;
			require ('user_privileges/user_privileges.php');
			require ('user_privileges/sharing_privileges.php');

			global $current_user;

			$sql   = '';
			$tabId = getTabid ($module);
			if (($is_admin == false) && ($profileGlobalPermission [1] == 1) && ($profileGlobalPermission [2] == 1) && ($defaultOrgSharingPermission [ $tabId ] == 3)) {
				$ownerIDSubQuery = "SELECT
											vtiger_user2role.userid
										FROM
											vtiger_user2role
											INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
											INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
										WHERE
											vtiger_role.parentrole LIKE '$current_user_parent_role_seq::%'";

				$currentUserGroups            = implode (',', $current_user_groups);
				$currentUserGroupsWhereClause = count ($current_user_groups) > 0 ? "vtiger_groups.groups IN ($currentUserGroups) OR" : '';

				// Construir las cláusulas y retornarlas
				$sql = " AND
					(
						vtiger_crmentity.smownerid IN ({$current_user->id}) OR
						vtiger_crmentity.smownerid IN ($ownerIDSubQuery) OR
						vtiger_crmentity.smownerid IN (SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per WHERE userid={$current_user->id} AND tabid=$tabId) OR
						$currentUserGroupsWhereClause
						vtiger_groups.groupid IN (SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid FROM vtiger_tmp_read_group_sharing_per WHERE userid={$current_user->id} AND tabid=$tabId)
					)";
				$sql = trim (preg_replace ('/\s+/S', ' ', $sql));
			}
			return $sql;
		}

		/**
		 * Create query to export the records.
		 *
		 * @param $whereClauses
		 *
		 * @return string
		 * @throws Exception
		 */
		public function create_export_query ($whereClauses) {
			global $current_user;

			$moduleName = isset ($_REQUEST ['module']) ? $_REQUEST ['module'] : '';

			// Obtener los campos enlazados
			$leftJoinClauses = $this->getLeftJoinClauses ($moduleName);

			// Obtener los campos permitidos al usuario
			require_once ('include/utils/ExportUtils.php');
			$fieldsListQuery = getPermittedFieldsQuery ($moduleName, 'detail_view');
			$fieldsList      = getFieldsListFromQuery ($fieldsListQuery);
			$whereClauses    = $whereClauses ? "$whereClauses AND" : '';
			$sql             = "SELECT
										$fieldsList,
										vtiger_users.user_name AS user_name
									FROM
										vtiger_crmentity
										INNER JOIN {$this->table_name} ON vtiger_crmentity.crmid={$this->table_name}.{$this->table_index}
										LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
										LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id AND vtiger_users.status='Active'
										$leftJoinClauses
										{$this->getNonAdminAccessControlQuery ($moduleName, $current_user)}
									WHERE
										$whereClauses
										vtiger_crmentity.deleted=0";
			return trim (preg_replace ('/\s+/S', ' ', $sql));
		}

		/**
		 * Initialize this instance for importing.
		 *
		 * @param $module
		 */
		public function initImport ($module) {
			$this->db = PearDatabase::getInstance ();
			$this->initImportableFields ($module);
		}

		/**
		 * Create list query to be shown at the last step of the import.
		 * Called From: modules/Import/UserLastImport.php
		 *
		 * @param $module
		 *
		 * @return string
		 */
		public function create_import_query ($module) {
			global $current_user;
			$sql = "SELECT
						crme.crmid,
						CASE WHEN (u.user_name NOT LIKE '') THEN u.user_name ELSE g.groupname END AS user_name,
						{$this->table_name}.*
					FROM
						{$this->table_name}
						INNER JOIN vtiger_crmentity crme ON crme.crmid={$this->table_name}.{$this->table_index}
						LEFT JOIN vtiger_users_last_import uli ON uli.bean_id=crme.crmid
						LEFT JOIN vtiger_users u ON u.id=crme.smownerid
						LEFT JOIN vtiger_groups g ON g.groupid=crme.smownerid
					WHERE
						uli.assigned_user_id='{$current_user->id}' AND
						uli.bean_type='$module' AND
						uli.deleted=0";
			return trim (preg_replace ('/\s+/S', ' ', $sql));
		}

		/**
		 * Delete the last imported records.
		 *
		 * @param $module
		 * @param $user_id
		 *
		 * @return integer
		 * @throws Exception
		 */
		public function undo_import ($module, $user_id) {
			global $adb;
			$count  = 0;
			$query  = "SELECT bean_id FROM vtiger_users_last_import WHERE assigned_user_id=? AND bean_type='$module' AND deleted=0";
			$result = $adb->pquery ($query, array ($user_id));
			if (!$result) {
				throw new Exception ('Error getting last import for undo');
			}
			while ($row = $adb->fetchByAssoc ($result)) {
				$queryUpdate  = 'UPDATE vtiger_crmentity SET deleted=1 WHERE crmid=?';
				$resultUpdate = $adb->pquery ($queryUpdate, array ($row ['bean_id']));
				if (!$resultUpdate) {
					throw new Exception ('Error undoing last import');
				}
				$count++;
			}
			return $count;
		}

		/**
		 * Transform the value while exporting
		 * NOTE: This function has been added to CRMEntity (base class).
		 * You can override the behavior by re-defining it here.
		 *
		 * @param $key
		 * @param $value
		 *
		 * @return string
		 */
		// function transform_export_value ($key, $value) {}

		/**
		 * Function which will set the assigned user id for import record.
		 */
		public function set_import_assigned_user () {
			global $current_user, $adb;

			$assignedUserId = $this->column_fields ['assigned_user_id'];
			if ($assignedUserId == $current_user->id) {
				return;
			}

			$result = $adb->pquery ('SELECT id FROM vtiger_users WHERE id=? UNION SELECT groupid AS id FROM vtiger_groups WHERE groupid=?', array ($assignedUserId, $assignedUserId));
			if ($this->db->num_rows ($result) != 1) {
				$this->column_fields ['assigned_user_id'] = $current_user->id;
			} else {
				$row                                      = $adb->fetchByAssoc ($result, -1, false);
				$this->column_fields ['assigned_user_id'] = (isset ($row ['id'])) && ($row ['id'] != -1) ? $row ['id'] : $current_user->id;
			}
		}

		/**
		 * Function which will give the basic query to find duplicates
		 *
		 * @param $module
		 * @param $tableCols
		 * @param $fieldValues
		 * @param $uiTypeArr
		 * @param string $selectColumns
		 *
		 * @return string
		 */
		public function getDuplicatesQuery ($module, $tableCols, $fieldValues, $uiTypeArr, $selectColumns = '') {
			$fromClauses  = join (
				' ',
				array (
					$this->table_name,
					"INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid={$this->table_name}.{$this->table_index}",
					'LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid',
					'LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid',
				)
			);
			$whereClauses = join (
				' ',
				array (
					'vtiger_crmentity.deleted=0',
					$this->getListViewSecurityParameter ($module),
				)
			);
			if ((isset ($selectColumns)) && (trim ($selectColumns) != '')) {
				$subQuery = "SELECT $selectColumns FROM {$this->table_name} AS t INNER JOIN vtiger_crmentity AS crm ON crm.crmid=t.{$this->table_index} WHERE crm.deleted=0 GROUP BY $selectColumns HAVING COUNT(*)>1";
			} else {
				$subQuery = "SELECT $tableCols FROM $fromClauses WHERE $whereClauses GROUP BY $tableCols HAVING COUNT(*)>1";
			}
			$subQueryOnClauses = get_on_clause ($fieldValues, $uiTypeArr, $module);
			$sql               = "SELECT
										{$this->table_name}.{$this->table_index} AS recordid,
										vtiger_users_last_import.deleted,
										$tableCols
									FROM
										$fromClauses
										LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id={$this->table_name}.{$this->table_index}
										INNER JOIN ($subQuery) AS temp ON $subQueryOnClauses
									WHERE
										$whereClauses
									ORDER BY
										$tableCols,
										{$this->table_name}.{$this->table_index} ASC";
			return trim (preg_replace ('/\s+/S', ' ', $sql));
		}

		/**
		 * Invoked when special actions are performed on the module.
		 *
		 * @param String Module name
		 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
		 */
		public function vtlib_handler ($moduleName, $eventType) {
			if ($eventType == 'module.postinstall') {
				// Handle post installation actions
			} else if ($eventType == 'module.disabled') {
				// Handle actions when this module is disabled.
			} else if ($eventType == 'module.enabled') {
				// Handle actions when this module is enabled.
			} else if ($eventType == 'module.preuninstall') {
				// Handle actions when this module is about to be deleted.
			} else if ($eventType == 'module.preupdate') {
				// Handle actions before this module is updated.
			} else if ($eventType == 'module.postupdate') {
				// Handle actions after this module is updated.
			}
		}

		/**
		 * Handle saving related module information.
		 * NOTE: This function has been added to CRMEntity (base class).
		 * You can override the behavior by re-defining it here.
		 */
		// public function save_related_module ($module, $crmId, $withModule, $withCrmId) { }

		/**
		 * Handle deleting related module information.
		 * NOTE: This function has been added to CRMEntity (base class).
		 * You can override the behavior by re-defining it here.
		 */
		// public function delete_related_module ($module, $crmId, $withModule, $withCrmId) { }

		/**
		 * Handle getting related list information.
		 * NOTE: This function has been added to CRMEntity (base class).
		 * You can override the behavior by re-defining it here.
		 */
		// public function get_related_list ($id, $curTabId, $relTabId, $actions=false) { }

		/**
		 * Handle getting dependents list information.
		 * NOTE: This function has been added to CRMEntity (base class).
		 * You can override the behavior by re-defining it here.
		 */
		// public function get_dependents_list ($id, $curTabId, $relTabId, $actions=false) { }

		/**
		 * Sobrescribe el método trash para eliminar las tareas relacionadas
		 * antes de eliminar el trabajo.
		 *
		 * @param string $module
		 * @param integer $id
		 */
		public function trash ($module, $id) {
			global $adb;
			
			// Eliminar las tareas relacionadas con el trabajo
			$this->deleteRelatedTasks ($id);
			
			// Llamar al método padre para completar la eliminación del trabajo
			parent::trash ($module, $id);
		}
		
		/**
		 * Elimina (marca como deleted=1) todas las tareas relacionadas con un trabajo.
		 * También elimina las relaciones en vtiger_seactivityrel.
		 *
		 * @param integer $workId ID del trabajo
		 */
		private function deleteRelatedTasks ($workId) {
			global $adb;
			
			if (empty ($workId)) {
				return;
			}
			
			// Obtener todas las tareas relacionadas con el trabajo
			$result = $adb->pquery (
				'SELECT rel.activityid
				 FROM vtiger_seactivityrel rel
				 INNER JOIN vtiger_crmentity crm ON crm.crmid = rel.activityid AND crm.deleted = 0
				 WHERE rel.crmid = ?',
				array ($workId)
			);
			
			if ($adb->num_rows ($result) > 0) {
				$taskIds = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$taskIds[] = $row['activityid'];
				}
				
				// Marcar cada tarea como eliminada
				foreach ($taskIds as $taskId) {
					// Verificar si la tarea tiene otras relaciones (excluyendo este trabajo)
					$otherRelations = $adb->pquery (
						'SELECT COUNT(*) as cnt FROM vtiger_seactivityrel WHERE activityid = ? AND crmid != ?',
						array ($taskId, $workId)
					);
					$hasOtherRelations = ($adb->query_result ($otherRelations, 0, 'cnt') > 0);
					
					// Solo eliminar la tarea si no tiene otras relaciones
					if (!$hasOtherRelations) {
						$adb->pquery (
							'UPDATE vtiger_crmentity SET deleted = 1, modifiedtime = NOW() WHERE crmid = ?',
							array ($taskId)
						);
					}
				}
				
				// Eliminar las relaciones de este trabajo con las tareas
				$adb->pquery ('DELETE FROM vtiger_seactivityrel WHERE crmid = ?', array ($workId));
			}
		}

	}

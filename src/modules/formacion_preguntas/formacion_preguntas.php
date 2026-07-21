<?php
	require_once ('data/CRMEntity.php');
	require_once ('data/Tracker.php');

	class formacion_preguntas extends CRMEntity {
		public $db;
		public $log;
		public $table_name = 'vtiger_formacion_preguntas';
		public $table_index = 'formacion_preguntasid';
		public $column_fields = array ();
		/** @var boolean Indicator if this is a custom module or standard module */
		public $IsCustomModule = true;
		/** @var array Mandatory table for supporting custom fields. */
		public $customFieldTable = array ('vtiger_formacion_preguntascf', 'formacion_preguntasid');
		/** @var array Mandatory for Saving, Include tables related to this module. */
		public $tab_name = array ('vtiger_crmentity', 'vtiger_formacion_preguntas', 'vtiger_formacion_preguntascf');
		/** @var array Mandatory for Saving, Include tablename and tablekey columnname here. */
		public $tab_name_index = array (
			'vtiger_crmentity'         => 'crmid',
			'vtiger_formacion_preguntas'   => 'formacion_preguntasid',
			'vtiger_formacion_preguntascf' => 'formacion_preguntasid',
		);
		/**
		 * @var array Mandatory for Listing (Related listview)
		 * Format: Field Label => array(tablename, columnname)
		 * tablename should not have prefix 'vtiger_'
		 * 'Payslip Name'=> array('payslip', 'payslipname'),
		 * 'Assigned To' => array('crmentity','smownerid')
		 */
		public $list_fields = array (
			'Código' => array ('formacion_preguntas', 'cod_formacion_pr'),
			'Título' => array ('formacion_preguntas', 'titulo'),
			'Tipo de pregunta' => array ('formacion_preguntas', 'tipo_de_pregunta'),
			'Pregunta' => array ('formacion_preguntas', 'pregunta'),
			'Ponderación' => array ('formacion_preguntas', 'ponderacion')
		);
		/**
		 * @var array Format: Field Label => fieldname
		 * 'Payslip Name'=> 'payslipname',
		 * 'Assigned To' => 'assigned_user_id'
		 */
		public $list_fields_name = array (
			'Código' => 'cod_formacion_pr',
			'Título' => 'titulo',
			'Tipo de pregunta' => 'tipo_de_pregunta',
			'Pregunta' => 'pregunta',
			'Ponderación' => 'ponderacion'
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
        public $defaultListLink = 'cod_formacion_pr';
		
		/** @var array For Popup listview and UI type support.
		 * Format: Field Label => array (tablename, columnname).
		 * tablename should not have prefix 'vtiger_'
		 * 'Payslip Name'=> array('payslip', 'payslipname')
		 */
		public $search_fields = array (
			'Título' => array ('formacion_preguntas', 'titulo')
		);
		/**
		 * @var array Format: Field Label => fieldname
		 * 'Payslip Name'=> 'payslipname'
		 */
		public $search_fields_name = array (
			'Título' => 'titulo'
		);
		/**
		 * @var array For Popup window record selection
		 * $popup_fields = array('payslipname');
		 */
		public $popup_fields = array ('titulo');
		/** @var array Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields */
		public $sortby_fields = array ();
		/** @var string For Alphabetical search */
		public $def_basicsearch_col = 'titulo';
		/** @var string Column value to use on detail view record text display */
		public $def_detailview_recname = 'titulo';
		/**
		 * @var array Required Information for enabling Import feature
		 * $required_fields = array('payslipname'=>1);
		 */
		public $required_fields = array ('titulo' => 1);
		/** @var array Callback function list during Importing */
		public $special_functions = array ('set_import_assigned_user');
		/** @var string */
		public $default_order_by = 'titulo';
		/** @var string */
		public $default_sort_order = 'ASC';
		/**
		 * @var array Used when enabling/disabling the mandatory fields for the module.
		 * Refers to vtiger_field.fieldname values.
		 * $mandatory_fields = array('createdtime', 'modifiedtime', 'payslipname');
		 */
		public $mandatory_fields = array ('titulo');

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

		public function save_module ($module) {
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
        public function getFormBlock() {
            $display_multchoice='block';
            $display_verdfalso='block';
            $lista='none';
            if($this->column_fields['tipo_de_pregunta']=='Verdadero/Falso') {
                $display_multchoice='none';
                $display_verdfalso='block';
            } else {
                if($this->column_fields['tipo_de_pregunta']=='Lista') {
                    $lista='block';
                    $display_multchoice='none';
                    $display_verdfalso='none';
                }
            }

            $buffer_salida='
				<div class="row">
					<div class="col-lg-12">
						<div class="main-box">
							<header class="main-box-header clearfix">
								<h2>Respuestas</h2>
							</header>
							<div class="main-box-body clearfix" >

								<script>
									var formaresp="Multiple Choice";
									var formaresp2="";
									jQuery("#tipo_de_pregunta").change(function() {
										formaresp2="";
										formaresp=jQuery("#tipo_de_pregunta").val();
										//alert(formaresp);
										jQuery("#multiple_choice").hide();
										jQuery("#respuesta_multiple").hide();
										jQuery("#verdadero_falso").hide();
										jQuery("#lista").hide();
										if(formaresp==="Multiple Choice" || formaresp==="Respuesta Multiple"){
											jQuery("#multiple_choice").show();
										}else if(formaresp==="Verdadero/Falso"){
											//alert("reconoce el cambio");
											jQuery("#verdadero_falso").show();
										}else if(formaresp==="Lista"){
											jQuery("#lista").show();
											formaresp2=formaresp;
										}
									});
									var posicion_choice=2;
									function agregarPreguntaChoice(){
										formaresp=jQuery("#tipo_de_pregunta").val();
										html=\'<table class="table table-bordered" id="pregunta_\'+posicion_choice+\'"><tr>\';
										html+=\'<td width="10%">Choice \'+posicion_choice+\'</td>\';
										html+=\'<td width="25%">\';
										html+=\'<input type="checkbox" name="es_correcta\'+formaresp2+\'[\'+posicion_choice+\']"> Aceptar como correcta?<br>\';
										html+=\'<input type="text" class="form-control" name="valor_correcta\'+formaresp2+\'[\'+posicion_choice+\']" value="0" class="detailedViewTextBox" style="width:70px"> % de la pregunta</td>\';
										html+=\'<td width="50%" >\';
										html+=\'<textarea class="form-control" id="respuesta_\'+posicion_choice+\'" name="respuesta\'+formaresp2+\'[\'+posicion_choice+\']"></textarea>\';
										html+=\'</td>\';
										if(formaresp==="Lista"){
											html+=\'<td width="10%">\';
											html+=\'<textarea class="form-control" name="seleccion[\'+posicion_choice+\']"></textarea>\';
											html+=\'</td>\';
										}
										html+=\'<td width="5%">'.$this->getDeleteBtn('deleteQuestion(\'+posicion_choice+\');return false;').'</td>\';
										html+=\'</tr></table>\';
										if(formaresp==="Lista"){
											jQuery("#add_choice"+formaresp).append(html);
										}else{
											jQuery("#add_choice").append(html);
										}
										posicion_choice++;
									}

									function deleteQuestion(pos){
										jQuery("#pregunta_"+pos).remove();
									}

								</script>

								<div align="center" id="multiple_choice" style="display:'.$display_multchoice.'">
									'.(($this->column_fields['tipo_de_pregunta']=='Multiple Choice' || $this->column_fields['tipo_de_pregunta']=='Respuesta Multiple' || $this->column_fields['tipo_de_pregunta']=='') ? $this->multiple_choice() : '').'
								</div>
								<div align="center" id="verdadero_falso"  style="display:'.$display_verdfalso.'">
									'.(($this->column_fields['tipo_de_pregunta']=='Verdadero/Falso' || $this->column_fields['tipo_de_pregunta']=='') ? $this->verdadero_falso() : '').'
								</div>
								<div align="center" id="lista"  style="display:'.$lista.'">
									'.(($this->column_fields['tipo_de_pregunta']=='Lista' || $this->column_fields['tipo_de_pregunta']=='') ? $this->multiple_choice('Lista') : '').'
								</div>
							</div>
						</div>
					</div>
				</div>
			';
            return $buffer_salida;
        }
        public function getDeleteBtn($onclick = '') {
            return '<a href="javascript:void(0)" onclick="'.$onclick.'" class="table-link danger"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-trash-o fa-stack-1x fa-inverse"></i></span></a>';
        }

        public function getRespuestas() {
            $buffer_salida='

				<div class="row">
					<div class="col-lg-12">
						<div class="main-box">
							<header class="main-box-header clearfix">
								<h2>Respuestas</h2>
							</header>
							<div class="main-box-body clearfix" >
								<table class="table table-bordered">';
            $i=1;
            $respuestas=$this->getRespuestasData($this->id);
            if(!empty($respuestas)) {
                foreach($respuestas as $r) {
                    $buffer_salida.='<tr>
									<td width="10%" >Choice '.$i.'</td>
									<td width="25%" >
										Aceptar como correcta? ('.($r['correcta'] ? 'Si' : 'No').')<br>';
                    if($this->column_fields['tipo_de_pregunta']!='Verdadero/Falso') {
                        $buffer_salida.=$r['porciento_valor'].' % de la pregunta';
                    }
                    $buffer_salida.='	</td>
									<td width="50%" >
										'.$r['respuesta'].'
									</td>';
                    $buffer_salida.='<td width="10%" >
										'.nl2br($r['seleccion']).'
									</td>';
                    $buffer_salida.='</tr>';
                    $i++;
                }
            }
            $buffer_salida.='	</table>

							</div>
						</div>
					</div>
				</div>
			';
            return $buffer_salida;
        }
        // @codingStandardsIgnoreStart
        public function multiple_choice($tipo = '') {
            // @codingStandardsIgnoreEnd
            $buffer_salida = '';
            $cantidad =0;
            $r=$this->getRespuestasData($this->id);
            if(!empty($r) && $this->cantidad_preguntas==0) {
                $cantidad=count($r);
            } else {
                if($this->cantidad_preguntas!=0) {
                    $cantidad=$this->cantidad_preguntas;
                }
            }

            if($cantidad>1) {
                $buffer_salida='<script>posicion_choice='.($cantidad+1).';</script>';
            }
            $colspan='4';
            if($tipo=='Lista') {
                $colspan='5';
            }
            $buffer_salida.='

					<table class="table">
						<tr>
							<td colspan="'.$colspan.'" >
								<table class="table table-bordered" id="pregunta_1">
									<tr>
										<td width="10%" >Choice 1</td>
										<td width="25%" >
											<input type="checkbox" name="es_correcta'.$tipo.'[1]" '.($r[0]['correcta'] ? 'checked' : '').'> Aceptar como correcta?<br>
											<input type="text" class="form-control" name="valor_correcta'.$tipo.'[1]" value="'.($r[0]['porciento_valor'] ? $r[0]['porciento_valor'] : '0').'" class="detailedViewTextBox" style="width:70px"> % de correcta
										</td>
										<td width="50%" >
											<textarea class="form-control" id="respuesta_1" name="respuesta'.$tipo.'[1]">'.$r[0]['respuesta'].'</textarea>
										</td>';
            $colspan='4';
            if($tipo=='Lista') {
                $buffer_salida.='<td width="10%" >
								<textarea name="seleccion[1]">'.$r[0]['seleccion'].'</textarea>
							</td>';
                $colspan='5';
            }
            $buffer_salida.='<td width="5%" >';
            $buffer_salida.=$this->getDeleteBtn('deleteQuestion(1);return false;');
            $buffer_salida.='</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td colspan="'.$colspan.'" align="center" id="add_choice'.$tipo.'">';
            if(!empty($r)) {
                unset($r[0]);
                $i=2;
                if($cantidad!=0) {
                    $i=$cantidad;
                }
                foreach($r as $re) {
                    $buffer_salida.='
						<table class="table table-bordered" id="pregunta_'.$i.'">
							<tr>
								<td width="10%" >Choice '.$i.'</td>
								<td width="25%" >
									<input type="checkbox" name="es_correcta'.$tipo.'['.$i.']" '.($re['correcta'] ? 'checked' : '').'> Aceptar como correcta?<br>
									<input type="text" class="form-control" name="valor_correcta'.$tipo.'['.$i.']" value="'.($re['porciento_valor'] ? $re['porciento_valor'] : '0').'" class="detailedViewTextBox" style="width:70px"> % de correcta
								</td>
								<td width="50%" >
									<textarea class="form-control"  id="respuesta_1" name="respuesta'.$tipo.'['.$i.']">'.$re['respuesta'].'</textarea>
								</td>';

                    $colspan='4';
                    if($tipo=='Lista') {
                        $buffer_salida.='<td width="10%" >
										<textarea class="form-control" name="seleccion['.$i.']">'.$re['seleccion'].'</textarea>
									</td>';
                        $colspan='5';
                    }
                    $buffer_salida.='<td width="5%" >';
                    $buffer_salida.=$this->getDeleteBtn('deleteQuestion('.$i.');return false;');
                    $buffer_salida.='</td>
							</tr>
						</table>
					';
                    $i++;
                }
            }
            $buffer_salida.='</td>
						</tr>
						<tr>
							<td colspan="4" align="center">
								<input type="button" class="btn btn-success" onclick="agregarPreguntaChoice()" value="Agregar Choice">
							</td>
						</tr>
					</table>

			';

            return $buffer_salida;
        }

        // @codingStandardsIgnoreStart
        public function verdadero_falso() {
            // @codingStandardsIgnoreEnd
            $r=$this->getRespuestasData($this->id);
            $buffer_salida='

					<table class="table table-bordered" >
						<tr>
							<td width="10%" >Choice 1</td>
							<td width="25%" >
								<input type="checkbox" name="vf_es_correcta[1]" value="1" '.($r[0]['correcta'] ? 'checked' : '').'> Aceptar como correcta?
							</td>
							<td width="60%" >
								<textarea id="vf_respuesta_1" name="vf_respuesta[1]" class="form-control">'.$r[0]['respuesta'].'</textarea>
							</td>
							<td width="5%" >&nbsp;</td>
						</tr>
						<tr>
							<td width="10%" >Choice 2</td>
							<td width="25%">
								<input type="checkbox" name="vf_es_correcta[2]" value="1" '.($r[1]['correcta'] ? 'checked' : '').'> Aceptar como correcta?
							</td>
							<td width="60%">
								<textarea  id="vf_respuesta_2" class="form-control" name="vf_respuesta[2]">'.$r[1]['respuesta'].'</textarea>
							</td>
							<td width="5%" >&nbsp;</td>
						</tr>

					</table>

			';

            return $buffer_salida;
        }

        public function getRespuestasData($crmid) {
            global $adb;
            if(!$crmid) {
                return array();
            }
            $ret = array();
            $sql="SELECT * FROM `vtiger_formacion_preguntas_respuestas` WHERE `formacion_preguntasid` = '".$crmid."'";
            $q=$adb->pquery($sql);
            while($r=$adb->fetchByAssoc($q)){
                $ret[]=$r;
            }
            return $ret;
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

	}

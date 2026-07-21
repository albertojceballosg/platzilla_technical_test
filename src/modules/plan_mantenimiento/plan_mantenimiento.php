<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('data/CRMEntity.php');
require_once('data/Tracker.php');

class plan_mantenimiento extends CRMEntity {
	var $db, $log; // Used in class functions of CRMEntity

	var $table_name = 'vtiger_plan_mantenimiento';
	var $table_index= 'plan_mantenimientoid';
	var $column_fields = array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = array('vtiger_plan_mantenimientocf', 'plan_mantenimientoid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = array('vtiger_crmentity', 'vtiger_plan_mantenimiento', 'vtiger_plan_mantenimientocf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_plan_mantenimiento'   => 'plan_mantenimientoid',
	    'vtiger_plan_mantenimientocf' => 'plan_mantenimientoid');

	/**
	 * @codingStandardsIgnoreStart
	 * Note: some line are in comment because they will be set later
	 * @var array Mandatory for Listing (Related listview)
	 * Format: Field Label => array(tablename, columnname)
	 * tablename should not have prefix 'vtiger_'
	 * 'Payslip Name'=> array('payslip', 'payslipname'),
	 * 'Assigned To' => array('crmentity','smownerid')
	 */
	var $list_fields = array (
		'Código'      => array ('plan_mantenimiento','cod_plan_de_mant'),
		'Título'      => array ('plan_mantenimiento','titulo'),
		'Descripción' => array ('plan_mantenimiento','description'),
		'Estado'      => array ('plan_mantenimiento','estado_plan'),
		'Frecuencia'  => array ('plan_mantenimiento','sys_frequency'),
		'Inicio'      => array ('plan_mantenimiento','fechainicial'),
		//'Nombre'       =>array('plan_mantenimiento','name'),
		//'Cliente'      =>array('plan_mantenimiento','accountid'),
		//'Fecha de fin' =>array('plan_mantenimiento','fechafinal'),
		//'Finalizado'   =>array('plan_mantenimiento','finalizado'),
		//'Tipo de Plan' =>array('plan_mantenimiento','tipo_auditoria'),
	);
	// @codingStandardsIgnoreEnd

	/**
	 * @var array Format: Field Label => fieldname
	 * 'Payslip Name'=> 'payslipname',
	 * 'Assigned To' => 'assigned_user_id'
	 */
	var $list_fields_name = array(
		/* Format: Field Label => fieldname */
		'Código'      => 'cod_plan_de_mant',
		'Título'      => 'titulo',
		'Descripción' =>'description',
		'Estado'      => 'estado_plan',
		'Frecuencia'  => 'sys_frequency',
		'Inicio'      => 'fechainicial',
		'Nombre'      =>'name',
		//'Cliente'=>'accountid',
		//'Fecha de Inicio'=>'fechainicial',
		//'Fecha de fin'=>'fechafinal',
		//'Finalizado'=>'finalizado',
		//'Tipo de Plan'=>'tipo_auditoria',
		//'Payslip Name'=> 'payslipname',
		//'Assigned To' => 'assigned_user_id'
	);

	// Make the field link to detail view from list view (Fieldname)
	//var $list_link_field = 'payslipname';
	var $list_link_field = 'cod_plan_de_mant';

	// For Popup listview and UI type support
	var $search_fields = array(
		/* Format: Field Label => array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		//'Payslip Name'=> array('payslip', 'payslipname')
		'Nombre'=>array('plan_mantenimiento','name'),

	);
	var $search_fields_name = array(
		/* Format: Field Label => fieldname */
		//'Payslip Name'=> 'payslipname'
		'Nombre'=>'name',

	);

	// For Popup window record selection
	//var $popup_fields = array('payslipname');
	var $popup_fields = array('name');

	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = array();

	// For Alphabetical search
	var $def_basicsearch_col = 'name';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'name';

	// Required Information for enabling Import feature
	//var $required_fields = array('payslipname'=>1);
	var $required_fields = array('name'=>1);


	// Callback function list during Importing
	var $special_functions = array('set_import_assigned_user');

	var $default_order_by = 'name';
	var $default_sort_order='ASC';
	var $force_column_order=false;
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	//var $mandatory_fields = array('createdtime', 'modifiedtime', 'payslipname');
	var $mandatory_fields = array('name');

	function __construct() {
		global $log, $currentModule;
		$this->column_fields = getColumnFields($currentModule);
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
	}

	function getSortOrder() {
		global $currentModule;

		$sortorder = $this->default_sort_order;
		if($_REQUEST['sorder']) $sortorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		else if($_SESSION[$currentModule.'_Sort_Order'])
			$sortorder = $_SESSION[$currentModule.'_Sort_Order'];

		return $sortorder;
	}

	function getOrderBy() {
		global $currentModule;

		$use_default_order_by = '';
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}

		$orderby = $use_default_order_by;
		if($_REQUEST['order_by']){
			$orderby = $this->db->sql_escape_string($_REQUEST['order_by']);
		}else if($_SESSION[$currentModule.'_Order_By'])
			$orderby = $_SESSION[$currentModule.'_Order_By'];
			/*
		if($orderby=='accountid'){
			$this->special_order='(SELECT accountname FROM vtiger_account WHERE accountid=vtiger_proyectos.accountid ORDER BY accountname '.$this->getSortOrder().')';
			$this->force_column_order=true;
		}
		*/
		return $orderby;
	}

	function save_module($module) {
	}

	/**
	 * Return query to use based on given modulename, fieldname
	 * Useful to handle specific case handling for Popup
	 */
	function getQueryByModuleField($module, $fieldname, $srcrecord, $query='') {
		// $srcrecord could be empty
	}

	/**
	 * Get list view query (send more WHERE clause condition if required)
	 */
	function getListQuery($module, $usewhere='') {
		$query = "SELECT vtiger_crmentity.*, $this->table_name.*";

		// Keep track of tables joined to avoid duplicates
		$joinedTables = array();

		// Select Custom Field Table Columns if present
		if(!empty($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$query .= " FROM $this->table_name";

		$query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		$joinedTables[] = $this->table_name;
		$joinedTables[] = 'vtiger_crmentity';

		// Consider custom table join as well.
		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
			$joinedTables[] = $this->customFieldTable[0];
		}
		$query .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$joinedTables[] = 'vtiger_users';
		$joinedTables[] = 'vtiger_groups';

		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
				" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
				" WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($module));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other =  CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			if(!in_array($other->table_name, $joinedTables)) {
				$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
				$joinedTables[] = $other->table_name;
			}
		}

		global $current_user;
		$query .= $this->getNonAdminAccessControlQuery($module,$current_user);
		$query .= "	WHERE vtiger_crmentity.deleted = 0 ".$usewhere;
		return $query;
	}

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 */
	function getListViewSecurityParameter($module) {
		global $current_user;
		$local_user = clone $current_user;
		require('user_privileges/user_privileges.php');
		require('user_privileges/sharing_privileges.php');

		$sec_query = '';
		$tabid = getTabid($module);

		if($is_admin==false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1
			&& $defaultOrgSharingPermission[$tabid] == 3) {

				$sec_query .= " AND (vtiger_crmentity.smownerid in($current_user->id) OR vtiger_crmentity.smownerid IN
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
						WHERE vtiger_role.parentrole LIKE '".$current_user_parent_role_seq."::%'
					)
					OR vtiger_crmentity.smownerid IN
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
						WHERE userid=".$current_user->id." AND tabid=".$tabid."
					)
					OR
						(";

					// Build the query based on the group association of current user.
					if(sizeof($current_user_groups) > 0) {
						$sec_query .= " vtiger_groups.groupid IN (". implode(",", $current_user_groups) .") OR ";
					}
					$sec_query .= " vtiger_groups.groupid IN
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=".$current_user->id." and tabid=".$tabid."
						)";
				$sec_query .= ")
				)";
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.
	 */
	function create_export_query($where)
	{
		global $current_user;
		$thismodule = $_REQUEST['module'];

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery($thismodule, "detail_view");

		$fields_list = getFieldsListFromQuery($sql);

		$query = "SELECT $fields_list, vtiger_users.user_name AS user_name
					FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
		}

		$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'";

		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field" .
				" INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid" .
				" WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($thismodule));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$fieldname = $this->db->query_result($linkedModulesQuery, $i, 'fieldname');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');

			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);

			$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
		}

		$query .= $this->getNonAdminAccessControlQuery($thismodule,$current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if($where != '') $query .= " WHERE ($where) AND $where_auto";
		else $query .= " WHERE $where_auto";

		return $query;
	}

	/**
	 * Initialize this instance for importing.
	 */
	function initImport($module) {
		$this->db = PearDatabase::getInstance();
		$this->initImportableFields($module);
	}

	/**
	 * Create list query to be shown at the last step of the import.
	 * Called From: modules/Import/UserLastImport.php
	 */
	function create_import_query($module) {
		global $current_user;
		$query = "SELECT vtiger_crmentity.crmid, case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name, $this->table_name.* FROM $this->table_name
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index
			LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=vtiger_crmentity.crmid
			LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_users_last_import.assigned_user_id='$current_user->id'
			AND vtiger_users_last_import.bean_type='$module'
			AND vtiger_users_last_import.deleted=0";
		return $query;
	}

	/**
	 * Delete the last imported records.
	 */
	function undo_import($module, $user_id) {
		global $adb;
		$count = 0;
		$query1 = "select bean_id from vtiger_users_last_import where assigned_user_id=? AND bean_type='$module' AND deleted=0";
		$result1 = $adb->pquery($query1, array($user_id)) or die("Error getting last import for undo: ".mysql_error());
		while ( $row1 = $adb->fetchByAssoc($result1))
		{
			$query2 = "update vtiger_crmentity set deleted=1 where crmid=?";
			$result2 = $adb->pquery($query2, array($row1['bean_id'])) or die("Error undoing last import: ".mysql_error());
			$count++;
		}
		return $count;
	}

	/**
	 * Transform the value while exporting
	 */
	function transform_export_value($key, $value) {
		return parent::transform_export_value($key, $value);
	}

	/**
	 * Function which will set the assigned user id for import record.
	 */
	function set_import_assigned_user()
	{
		global $current_user, $adb;
		$record_user = $this->column_fields["assigned_user_id"];

		if($record_user != $current_user->id){
			$sqlresult = $adb->pquery("select id from vtiger_users where id = ? union select groupid as id from vtiger_groups where groupid = ?", array($record_user, $record_user));
			if($this->db->num_rows($sqlresult)!= 1) {
				$this->column_fields["assigned_user_id"] = $current_user->id;
			} else {
				$row = $adb->fetchByAssoc($sqlresult, -1, false);
				if (isset($row['id']) && $row['id'] != -1) {
					$this->column_fields["assigned_user_id"] = $row['id'];
				} else {
					$this->column_fields["assigned_user_id"] = $current_user->id;
				}
			}
		}
	}

	/**
	 * Function which will give the basic query to find duplicates
	 */
	function getDuplicatesQuery($module,$table_cols,$field_values,$ui_type_arr,$select_cols='') {
		$select_clause = "SELECT ". $this->table_name .".".$this->table_index ." AS recordid, vtiger_users_last_import.deleted,".$table_cols;

		// Select Custom Field Table Columns if present
		if(isset($this->customFieldTable)) $query .= ", " . $this->customFieldTable[0] . ".* ";

		$from_clause = " FROM $this->table_name";

		$from_clause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

		// Consider custom table join as well.
		if(isset($this->customFieldTable)) {
			$from_clause .= " INNER JOIN ".$this->customFieldTable[0]." ON ".$this->customFieldTable[0].'.'.$this->customFieldTable[1] .
				      " = $this->table_name.$this->table_index";
		}
		$from_clause .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$where_clause = "	WHERE vtiger_crmentity.deleted = 0";
		$where_clause .= $this->getListViewSecurityParameter($module);

		if (isset($select_cols) && trim($select_cols) != '') {
			$sub_query = "SELECT $select_cols FROM  $this->table_name AS t " .
				" INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t.".$this->table_index;
			// Consider custom table join as well.
			if(isset($this->customFieldTable)) {
				$sub_query .= " LEFT JOIN ".$this->customFieldTable[0]." tcf ON tcf.".$this->customFieldTable[1]." = t.$this->table_index";
			}
			$sub_query .= " WHERE crm.deleted=0 GROUP BY $select_cols HAVING COUNT(*)>1";
		} else {
			$sub_query = "SELECT $table_cols $from_clause $where_clause GROUP BY $table_cols HAVING COUNT(*)>1";
		}


		$query = $select_clause . $from_clause .
					" LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=" . $this->table_name .".".$this->table_index .
					" INNER JOIN (" . $sub_query . ") AS temp ON ".get_on_clause($field_values,$ui_type_arr,$module) .
					$where_clause .
					" ORDER BY $table_cols,". $this->table_name .".".$this->table_index ." ASC";

		return $query;
	}

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	function vtlib_handler($modulename, $event_type) {
		if($event_type == 'module.postinstall') {
			// TODO Handle post installation actions
		} else if($event_type == 'module.disabled') {
			// TODO Handle actions when this module is disabled.
		} else if($event_type == 'module.enabled') {
			// TODO Handle actions when this module is enabled.
		} else if($event_type == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($event_type == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($event_type == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
	}

	/**
	 * Handle saving related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	// function save_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle deleting related module information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function delete_related_module($module, $crmid, $with_module, $with_crmid) { }

	/**
	 * Handle getting related list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	/**
	 * Handle getting dependents list information.
	 * NOTE: This function has been added to CRMEntity (base class).
	 * You can override the behavior by re-defining it here.
	 */
	//function get_dependents_list($id, $cur_tab_id, $rel_tab_id, $actions=false) { }

	function displayGANTT() {
		$urlplatdb = '';
		if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb']))
			$urlplatdb = "&platdb=".vtlib_purify($_REQUEST['platdb']);
		//return 'index.php?module=GANTT&action=index&parenttab=Support&Ajax=true&f=false&proyectosid='.$this->id.$urlplatdb;
		//return '<iframe src="index.php?module=GANTT&action=index&parenttab=Support&Ajax=true&f=false&proyectosid='.$this->id.$urlplatdb.'" width="100%" height="400px" frameborder="0" marginwidth="0" marginheight ="0" border="0"></iframe>';
	}

	function customScripts() {
		$bufferSalida = '

		function onClickTemplate() {
			jQuery(\'#td_fechainicial\').toggle();
			jQuery(\'#tdinfo_fechainicial\').toggle();
			jQuery(\'#td_fechafinal\').toggle();
			jQuery(\'#tdinfo_fechafinal\').toggle();
		}

		jQuery( document ).ready(function() {
			jQuery(\'#template\' ).click(function() {
				onClickTemplate();
			});
			if(jQuery("#template").is(\':checked\'))
				onClickTemplate();
		});
		';

		return $bufferSalida;
	}

	function customButtons($recordid) {
		return '<a class="table-link info" href="javascript:void(0)" title="Cambiar Fechas" onclick="cambiofechas(\''.$recordid.'\',\'\')"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-calendar fa-stack-1x fa-inverse"></i></span></a>';
	}

	function getProgressBarValue(){


		global $adb,$currentModule;

		$sql = "SELECT count(*) from vtiger_intervencion h
			join vtiger_crmentity crm on (crm.crmid=h.intervencionid and deleted=0)
			WHERE ".$currentModule."id = ?";

		list($total) = $adb->fetch_row($adb->pquery($sql, array($this->id)));

		$sql.= " and intstate = 'Finalizado'";

		list($cerrados) = $adb->fetch_row($adb->pquery($sql, array($this->id)));

		if ($total)
			$progress = $cerrados/$total;
		else
			$progress = 0;

		return $progress;

	}






	function obtenerEstructuraGantt($module) {
		global $adb,$current_user;

		$proyectoid = $this->id;
		if (empty($proyectoid) && isset($_REQUEST["'".$module."id'"]))
			$proyectoid = $_REQUEST["'".$module."id'"];

			// Fechas
			$fecha = date('Y-m-d');

			if (!isset($_REQUEST['inidate']) || empty($_REQUEST['inidate'])) {
				$fechaini = strtotime ( '-30 day' , strtotime ( $fecha ) ) ;
				$fechaini = date ( 'Y-m-d' , $fechaini );
				$_REQUEST['inidate'] = $fechaini;
			}
			if (!isset($_REQUEST['enddate']) || empty($_REQUEST['enddate'])) {
				$fechafin = strtotime ( '+30 day' , strtotime ( $fecha ) ) ;
				$fechafin = date ( 'Y-m-d' , $fechafin );
				$_REQUEST['enddate'] = $fechafin;
			}


		if (!empty($accountid) || !empty($proyectoid)) {
			$data = array();

			if (!empty($proyectoid)) {
				$sql="SELECT P.*, ifnull(fechainicial,CRM.createdtime) AS fechainicial_aux
								FROM vtiger_$module P
								INNER JOIN vtiger_crmentity CRM ON (CRM.crmid = P.".$module."id)and(CRM.deleted=0)
								WHERE  P.".$module."id = $proyectoid";
			}
			$result = $adb->query($sql);
			$i = 0;
			$bufferSalida = '';
			while ($reg = $adb->fetchByAssoc($result)) {

				// TITULO
				$titulo = "<a href='javascript:void();' onclick=\"alert('";
				$titulo .= 'Fecha Inicial: '.$reg['fechainicial'].'\n';
				$titulo .= 'Fecha Final: '.$reg['fechafinal'].'\n\n';
				$titulo .= 'Descripcion: '.$reg['description'];
				$titulo .= "');\">".ucfirst($reg['name'])."</a>";

				$titulo  = "<a href='index.php?action=DetailView&module='.$module.'&record=".$reg["'".$module."id'"]."'>";
				if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
					$titulo = str_ireplace('index.php?','index.php?platdb='.$_REQUEST['platdb'].'&',$titulo);
				}
				$titulo .= ucfirst($reg['name'])."</a>";
				// FECHAS
				$fechainicial  = $reg['fechainicial'];
				$fechafinal  = $reg['fechafinal'];
				if ($year > 0) {
						if ($reg['fechainicial'] < $year.'-01-01') $fechainicial = $year.'-01-01';
						if ($reg['fechafinal'] > $year.'-12-31') $fechafinal = $year.'-12-31';
				}

				// ESTADO = CLASS (existen 3 class, sin nada, 'important' y 'urgent')
				$class = '';  // AUTO: no se pide validacion al cliente
				if ($reg['estado_plan'] == 'SI: Confirmado por el cliente') $class = 'important';
				// if ($reg['estado'] == 'SI: Confirmado por el cliente') $class = 'urgent';

				$sqlDateUpdate="SELECT  min(fecha_inicio_previa) min_date ,max(fecha_fin_previa) max_date
				FROM vtiger_planes_replanificados where planid=".$reg[$module."id"]." and modulo='".$module."'";

				$resultDateUpdate = mysql_query($sqlDateUpdate);

				$min_date='';
				$max_date='';
				if ($resultDateUpdate) {
					if($regDateUpdate = mysql_fetch_array($resultDateUpdate)) {
						$min_date=$regDateUpdate['min_date'];
						$max_date=$regDateUpdate['max_date'];


					}
				}

				if (!empty($bufferSalida))
					$bufferSalida.= ',';
				$i++;
				$titulo = '<a href=\'index.php?action=DetailView&module='.$module.'&record='.$reg[$module."id"].'\' target=\'_parent\'>'.($reg['name']).'</a>';
				/*if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
					$titulo = str_ireplace('index.php?','index.php?platdb='.$_REQUEST['platdb'].'&',$titulo);
				}*/
				$bufferSalida.= '
				{id:'.$i.', text:"'.$titulo.'", start_date:"'.$reg['fechainicial_aux'].'", end_date:"'.$reg['fechafinal'].'",order:10,
	                    progress:'.calculoProgresoPlan($reg[$module."id"], $module).', open: true}
	            ';



					$sql="SELECT a . *
						FROM vtiger_intervencion a
						INNER JOIN vtiger_crmentity c ON c.crmid = a.intervencionid AND deleted =0
						WHERE a.".$module."id=".$reg[$module."id"]." ORDER BY a.inidate";

					$q= $adb->query($sql);


					$j = $i;

					while($r=$adb->fetchByAssoc($q)){
						$started = 0;
						if (isset($r['ticketid']) && $r['ticketid'] != '') {
							$sql_diarynotes="SELECT count( d.diarynoteid ) cantidad
									FROM vtiger_troubletickets t
									LEFT JOIN vtiger_diarynotes_desarrolladores d ON t.ticketid = d.ticketid
									WHERE  horas_dedicadas >0 and d.ticketid =".$r['ticketid'];
							$q_sql_diarynotes=$adb->query($sql_diarynotes);
							$started=0;
							if($request_q_sql_diarynotes=$adb->fetchByAssoc($q_sql_diarynotes))
							{
								if($request_q_sql_diarynotes['cantidad']>0 and $r['intstate']!='Finalizado') {
									$started=1;
								}
							}
						}

						if (!empty($bufferSalida))
							$bufferSalida.= ',';
						$i++;
						$titulo = '<a href=\'index.php?action=DetailView&module=intervencion&record='.$r['intervencionid'].'\' target=\'_parent\'>'.($r['name']).'</a>';
						/*if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
							$titulo = str_ireplace('index.php?','index.php?platdb='.$_REQUEST['platdb'].'&',$titulo);
						}*/
						$bufferSalida.= '
							{id:'.$i.', text:"'.$titulo.'", start_date:"'.$r['inidate'].'", end_date:"'.$r['enddate'].'",order:10,
									progress:'.calculoProgresoHito($r['intervencionid']).', open: true,parent:'.$j.'}
							';
						$k = $i;

							$sql = "SELECT crmer.crmid as intervencionid,date_start as start_date,
									date_expected as end_estimated_date,todotasksid,title,executed
									FROM `vtiger_crmentityrel` crmer join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
									join vtiger_todotasks tt on (tt.todotasksid = crmer.relcrmid)
									WHERE crmer.crmid = ".$r['intervencionid']." and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0  ";

							$q2= $adb->query($sql);

							while($r2=$adb->fetchByAssoc($q2)){

								$started = 1;
								$data[]= array(
								  'label' => "<span style='margin-left:20px'>".ucfirst($r2['title'])."</span>",
								  'start'	 => $r2['start_date'], //$r2['inidate'],
								  'end'  	 => $r2['end_estimated_date'], // $r2['enddate'],
								  'estado_hito' => 1,//comprobarFinTicket($r2['ticketid']),
								  'class' => 'important',
								  'started' => $started,

								);


								if ($r2['executed'])
									$progress = 1;
								else
									$progress = 0;


								//$progress = porcentajeAvanceTodoTasksGANTT($r2['ticketid']);

								if (!empty($bufferSalida))
									$bufferSalida.= ',';
								$i++;
								$titulo = '<a href=\'index.php?action=DetailView&module=todotasks&record='.$r2['todotasksid'].'\' target=\'_parent\'>'.($r2['title']).'</a>';
								/*if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
									$titulo = str_ireplace('index.php?','index.php?platdb='.$_REQUEST['platdb'].'&',$titulo);
								}*/
								$bufferSalida.= '
									{id:'.$i.', text:"'.$titulo.'", start_date:"'.$r2['start_date'].'", end_date:"'.$r2['end_estimated_date'].'",order:10,
											progress:'.$progress.', open: true,parent:'.$k.'}
									';


							}
					}
			}
		}
		$data = '
		<div id="gantt_here" style=\'width:100%; height:100%;\'></div>
		<script type="text/javascript">
			gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
	        var tasks =  {
	            data:[
	                '.$bufferSalida.'
	            ],
	        };

			gantt.templates.tooltip_text = function(start,end,task){
				return "<b>"+task.text+"</b><br/><b>Inicio:</b> " +
				gantt.templates.tooltip_date_format(start)+
				"<br/><b>Fin:</b> "+gantt.templates.tooltip_date_format(end);
			};
			gantt.templates.progress_text = function(start, end, task){
				return "<div style=\'text-align:left;\'>"+Math.round(task.progress*100)+ "%</span>";
			};


			gantt.config.columns = [
				{ name:"text", tree:true, width:200},
				{ name:"start_date", align: "center", width:80},
				{ name:"end_date", align: "center", width:80}
			];
			gantt.config.tooltip_offset_x = 0;
			gantt.config.readonly = true;
			gantt.init("gantt_here");
			gantt.config.date_scale = "%M %Y";
			gantt.config.scale_unit = "month";
			gantt.config.grid_width = 360;

			gantt.parse(tasks);

		</script>';

		return $data;
	}


	function estanHitosCerrados($module){

		global $adb,$app_strings;

		$sql = "SELECT h.intervencionid,name,intstate,hcf.* ,
			(
				SELECT count(*) FROM `vtiger_crmentityrel` crmer join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
				WHERE crmer.crmid = h.intervencionid and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0
			) as totaltickets,
			(
				SELECT count(*) FROM `vtiger_crmentityrel` crmer join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
				join vtiger_todotasks tt on (tt.todotasksid = crmer.relcrmid)
				WHERE crmer.crmid = h.intervencionid and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0 and executed = 1
			) as ticketscompletados
			FROM vtiger_intervencion h join vtiger_intervencioncf hcf on (hcf.intervencionid = h.intervencionid)
			join vtiger_crmentity crm on (crm.crmid = h.intervencionid)
			where crm.deleted = 0  and h.".$module."id =? ";

		$result = $adb->pquery($sql,array($this->id));

		while ($reg = $adb->fetchByAssoc($result)) {

			if ($reg['totaltickets'] > 0 ){
				if ($reg['totaltickets'] == $reg['ticketscompletados']){
					return 1;
				}else{
					return 0;
				}
			}else{
				return 0;
			}

		}

	}







	function actualizarEstadoHitos($module){

		global $adb,$app_strings;
		$hitos = array();


		$sql = "SELECT h.intervencionid,name,intstate,hcf.* ,
			(
				SELECT count(*) FROM `vtiger_crmentityrel` crmer join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
				WHERE crmer.crmid = h.intervencionid and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0
			) as totaltickets,
			(
				SELECT count(*) FROM `vtiger_crmentityrel` crmer join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
				join vtiger_todotasks tt on (tt.todotasksid = crmer.relcrmid)
				WHERE crmer.crmid = h.intervencionid and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0 and executed = 1
			) as ticketscompletados
			FROM vtiger_intervencion h join vtiger_intervencioncf hcf on (hcf.intervencionid = h.intervencionid)
			join vtiger_crmentity crm on (crm.crmid = h.intervencionid)
			where crm.deleted = 0  ";

			if(isset($this->id) && $this->id > 0){
				$sql .= "and h.".$module."id =".$this->id;
			}

		$result = $adb->pquery($sql,array());

			while ($reg = $adb->fetchByAssoc($result)) {
				$hito = array();
				$hito['id'] = $reg['intervencionid'];
				$hito['name'] = $reg['name'];
				$hito['state'] = $reg['intstate'];
				$hito['progreso'] = $reg['totaltickets'] ? $reg['ticketscompletados'] / $reg['totaltickets'] * 100 : 0;

				if ($reg['totaltickets'] > 0 ){
					if ($reg['totaltickets'] == $reg['ticketscompletados']){
						$sqlUpdateHitoState = "UPDATE vtiger_intervencion set intstate = 'Finalizado' WHERE intervencionid = ".$reg['intervencionid'];
						$adb->pquery($sqlUpdateHitoState);
					}else{
						$sqlUpdateHitoState = "UPDATE vtiger_intervencion set intstate = 'En desarrollo' WHERE intervencionid = ".$reg['intervencionid'];
						$adb->pquery($sqlUpdateHitoState);
					}
				}

			}

		$this->actualizarEstadoPlan($module);

	}




		function getHitosTareas(){

			global $adb,$app_strings,$currentModule;
			$hitos = array();

			$sql = "SELECT h.intervencionid,name,intstate,hcf.* ,
				(
					SELECT count(*) FROM `vtiger_crmentityrel` crmer join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
					WHERE crmer.crmid = h.intervencionid and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0
				) as totaltickets,
				(
					SELECT count(*) FROM `vtiger_crmentityrel` crmer join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
					join vtiger_todotasks tt on (tt.todotasksid = crmer.relcrmid)
					WHERE crmer.crmid = h.intervencionid and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0 and executed = 1
				) as ticketscompletados
				FROM vtiger_intervencion h join vtiger_intervencioncf hcf on (hcf.intervencionid = h.intervencionid)
				join vtiger_crmentity crm on (crm.crmid = h.intervencionid)
				where crm.deleted = 0  and h.".$currentModule."id =? ";



			$result = $adb->pquery($sql,array($this->id));

			while ($reg = $adb->fetchByAssoc($result)) {
				$hito = array();
				$hito['id'] = $reg['intervencionid'];
				$hito['name'] = $reg['name'];
				$hito['state'] = $reg['intstate'];
				$hito['progreso'] = $reg['totaltickets'] ? $reg['ticketscompletados'] / $reg['totaltickets'] * 100 : 0;

				$sqlHelpDeksk = "SELECT * FROM `vtiger_crmentityrel` crmer
					join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
					join vtiger_todotasks tt on (tt.todotasksid = crmer.relcrmid)
					WHERE crmer.crmid = ? and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0 ";
					$result2 = $adb->pquery($sqlHelpDeksk,array($reg['intervencionid']));

					//echo "$sql | ".$reg['intervencionid']."<br><br>";

				$tickets = array();
				while ($row = $adb->fetchByAssoc($result2)) {
					$ticket = array();
					$ticket['ticketid'] = $row['todotasksid'];
					$ticket['ticket_no'] = $row['todotasksid'];
					$ticket['status'] = ($row['executed'] == 1) ? 'Ejecutada' : 'En Proceso'; //$app_strings[$row['status']];
					$ticket['title'] = $row['title'];
					array_push($tickets,$ticket);
					unset($ticket);

				}

				$hito['tickets'] = $tickets;
				unset($tickets);

				array_push($hitos,$hito);
				unset($hito);
			}

			return $hitos;
		}

	function actualizarEstadoPlan($module){

		global $adb, $currentModule;

		$sql = "SELECT p.".$module."id,
				(
					select count(*) from vtiger_intervencion h join vtiger_crmentity crmeh on (crmeh.crmid = h.intervencionid )
					where crmeh.deleted = 0 and h.".$module."id =p.".$module."id
				) as hitos,
				(
					select count(*) from vtiger_intervencion h join vtiger_crmentity crmeh on (crmeh.crmid = h.intervencionid )
					where crmeh.deleted = 0 and h.".$module."id =p.".$module."id and intstate='Finalizado'
				) as hitosterminados
				FROM vtiger_".$module." p
				JOIN vtiger_crmentity crm ON ( crm.crmid = p.".$module."id )
				AND crm.deleted =0 ";

		if(isset($this->id) && $this->id > 0){
			$sql .= "and p.".$module."id = ".$this->id;
		}

		$result = $adb->query($sql);

		while ($reg = $adb->fetchByAssoc($result)) {

			if ($reg['hitos'] > 0 ){
				if ($reg['hitos'] == $reg['hitosterminados']){
					$sqlUpdate = "UPDATE vtiger_".$module." set estado_plan = 'Completado', finalizado = 1 WHERE ".$module."id = ".$reg["'".$module."id'"];
					$adb->query($sqlUpdate);
				}else{
					$sqlUpdate = "UPDATE vtiger_".$module." set estado_plan = 'En Progreso', finalizado = 0 WHERE ".$module."id = ".$reg["'".$module."id'"];
					$adb->query($sqlUpdate);
				}

			}

		}

	}



	function DeleteHitosTareas($plan){

		global $adb,$app_strings,$currentModule;
		$hitos = array();

		$sql = "SELECT h.intervencionid,name,intstate,hcf.* ,
				(
					SELECT count(*) FROM `vtiger_crmentityrel` crmer join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
					WHERE crmer.crmid = h.intervencionid and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0
				) as totaltickets,
				(
					SELECT count(*) FROM `vtiger_crmentityrel` crmer join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
					join vtiger_todotasks tt on (tt.todotasksid = crmer.relcrmid)
					WHERE crmer.crmid = h.intervencionid and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0 and executed = 1
				) as ticketscompletados
				FROM vtiger_intervencion h join vtiger_intervencioncf hcf on (hcf.intervencionid = h.intervencionid)
				join vtiger_crmentity crm on (crm.crmid = h.intervencionid)
				where crm.deleted = 0  and h.".$currentModule."id =? ";



		$result = $adb->pquery($sql,array($plan));

		$focus2 = CRMEntity::getInstance('intervencion');
		while ($reg = $adb->fetchByAssoc($result)) {

			$sqlHelpDeksk = "SELECT * FROM `vtiger_crmentityrel` crmer
					join vtiger_crmentity crme on (crme.crmid = crmer.relcrmid)
					join vtiger_todotasks tt on (tt.todotasksid = crmer.relcrmid)
					WHERE crmer.crmid = ? and module ='intervencion' and relmodule = 'todotasks' and crme.deleted = 0 ";
			$result2 = $adb->pquery($sqlHelpDeksk,array($reg['intervencionid']));

			$focus3 = CRMEntity::getInstance('todotasks');
			while ($row = $adb->fetchByAssoc($result2)) {
				DeleteEntity('todotasks', '', $focus3, $row['todotasksid'], '');
			}

			DeleteEntity('intervencion', '', $focus2, $reg['intervencionid'], '');

		}

	}




}

?>
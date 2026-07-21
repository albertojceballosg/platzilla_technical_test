<?php

	require_once('data/CRMEntity.php');
	require_once('data/Tracker.php');

	class formacion_cursos extends CRMEntity {

	/** @var PearDatabase */
	public $db;

	/** @var LoggerLog */
	public $log; // Used in class functions of CRMEntity

	/** @var string */
	public $table_name = 'vtiger_formacion_cursos';

	/** @var string */
	public $table_index= 'formacion_cursosid';

	/** @var array */
	public $column_fields = array();

	/** @var $IsCustomModule Boolean */
	public $IsCustomModule = true;

	/** @var $customFieldTable array */
	public $customFieldTable = array('vtiger_formacion_cursoscf', 'formacion_cursosid');

	/** @var $tab_name array */
	public $tab_name = array('vtiger_crmentity', 'vtiger_formacion_cursos', 'vtiger_formacion_cursoscf');

	/** @var $tab_name_index array */
	public $tab_name_index = array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_formacion_cursos'   => 'formacion_cursosid',
	    'vtiger_formacion_cursoscf' => 'formacion_cursosid'
	);

	/**
	 * Mandatory for Listing (Related listview)
	 * Format: Field Label => Array(tablename, columnname)
	 * tablename should not have prefix 'vtiger_'
	 * 'Payslip Name'=> Array('payslip', 'payslipname'),
	 * 'Assigned To' => Array('crmentity','smownerid')
	 *
	 * @var $list_fields array
	 */
	public $list_fields = array (
		'Título'=>array('formacion_cursos','titulo'),
		'Descripción'=>array('formacion_cursos','descripcion'),
		'Materiales'=>array('formacion_cursos','materiales'),
	);

	/**
	 * Format: Field Label => fieldname
	 * 'Payslip Name'=> 'payslipname',
	 * 'Assigned To' => 'assigned_user_id'
	 *
	 * @var array
	 */
	public $list_fields_name = array(
		'Título'=>'titulo',
		'Descripción'=>'descripcion',
		'Materiales'=>'materiales',
	);

	/**
	 * Make the field link to detail view from list view (Fieldname)
	 *
	 * @var $list_link_field = 'payslipname';
	 */
	public $list_link_field = 'titulo';

	/**
	 * For Popup listview and UI type support
	 * Format: Field Label => Array(tablename, columnname)
	 * tablename should not have prefix 'vtiger_'
	 * 'Payslip Name'=> Array('payslip', 'payslipname')
	 *
	 * @var $search_fields array
	 */
	public $search_fields = array(
		'Título'=>array('formacion_cursos','titulo'),
	);

	/**
	 * 	Format: Field Label => fieldname
	 *
	 * @var $search_fields_name array
	 */
	public $search_fields_name = array('Título' => 'titulo');

	/**
	 * For Popup window record selection
	 * var $popup_fields = Array('payslipname');
	 *
	 * @var $popup_fields array
	 */
	public $popup_fields = array('titulo');

	/**
	 * Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	 *
	 * @var $sortby_fields array
	 */
	public $sortby_fields = array();

	/**
	 * For Alphabetical search
	 *
	 * @var $def_basicsearch_col string
	 */
	public $def_basicsearch_col = 'titulo';

	/**
	 * Column value to use on detail view record text display
	 *
	 * @var $def_detailview_recname string
	 */
	public $def_detailview_recname = 'titulo';

	/**
	 * Required Information for enabling Import feature
	 * var $required_fields = Array('payslipname'=>1);
	 *
	 * @var $required_fields array
	 */
	public $required_fields = array('titulo' => 1);

	/**
	 * Callback function list during Importing
	 *
	 * @var $special_functions array
	 */
	public $special_functions = array('set_import_assigned_user');

	/** @var $default_order_by string */
	public $default_order_by = 'titulo';

	/** @var $default_sort_order string */
	public $default_sort_order='ASC';

	/**
	 * Used when enabling/disabling the mandatory fields for the module.
	 * Refers to vtiger_field.fieldname values.
	 * var $mandatory_fields = Array('createdtime', 'modifiedtime', 'payslipname');
	 *
	 * @var $mandatory_fields array
	 */
	public $mandatory_fields = array('titulo');

	/** @Constructor */
	public function __construct() {
		global $log, $currentModule;
		$this->column_fields = getColumnFields($currentModule);
		$this->db = PearDatabase::getInstance();
		$this->log = $log;
	}

	public function getSortOrder() {
		global $currentModule;

		$sortorder = $this->default_sort_order;
		if ($_REQUEST['sorder']) {
			$sortorder = $this->db->sql_escape_string($_REQUEST['sorder']);
		} else if ($_SESSION[$currentModule.'_Sort_Order']) {
			$sortorder = $_SESSION[$currentModule . '_Sort_Order'];
		}
		return $sortorder;
	}

	public function getOrderBy() {
		global $currentModule;
		
		$use_default_order_by = '';
		if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
			$use_default_order_by = $this->default_order_by;
		}
		
		$orderby = $use_default_order_by;
		if ($_REQUEST['order_by']) {
			$orderby = $this->db->sql_escape_string($_REQUEST['order_by']);
		} else if ($_SESSION[$currentModule.'_Order_By']) {
			$orderby = $_SESSION[$currentModule . '_Order_By'];
		}
		return $orderby;
	}

	// @codingStandardsIgnoreStart
	public function save_module($module) {
		echo $module;
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Return query to use based on given modulename, fieldname
	 * Useful to handle specific case handling for Popup

	 * @param $module
	 * @param $fieldname
	 * @param $srcRecord
	 * @param string $query
	 */
	public function getQueryByModuleField($module, $fieldname, $srcRecord, $query = '') {
		// $srcRecord could be empty
		echo $module;
		echo $fieldname;
		echo $srcRecord;
		echo $query;
	}

	/**
	 * Get list view query (send more WHERE clause condition if required)

	 * @param $module
	 * @param string $useWhere

	 * @return string
	 */
	public function getListQuery($module, $useWhere = '') {
		$query = 'SELECT vtiger_crmentity.*, $this->table_name.*';
		// Keep track of tables joined to avoid duplicates
		$joinedTables = array();
		// Select Custom Field Table Columns if present
		if (!empty($this->customFieldTable)) {
			$query .= ', ' . $this->customFieldTable[0] . '.* ';
		}
		$query .= " FROM $this->table_name";
		$query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";
		$joinedTables[] = $this->table_name;
		$joinedTables[] = 'vtiger_crmentity';
		// Consider custom table join as well.
		if(!empty($this->customFieldTable)) {
			$query .= " INNER JOIN $this->customFieldTable[0] ON $this->customFieldTable[0].$this->customFieldTable[1] = $this->table_name.$this->table_index";
			$joinedTables[] = $this->customFieldTable[0];
		}
		$query .= ' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid';
		$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$joinedTables[] = 'vtiger_users';
		$joinedTables[] = 'vtiger_groups';
		
		$linkedModulesQuery = $this->db->pquery("SELECT DISTINCT fieldname, columnname, relmodule FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE uitype='10' AND vtiger_fieldmodulerel.module=?",	array($module));

		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for ($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');
			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);
			if (!in_array($other->table_name, $joinedTables)) {
				$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
				$joinedTables[] = $other->table_name;
			}
		}

		global $current_user;
		$query .= $this->getNonAdminAccessControlQuery($module,$current_user);
		$query .= '	WHERE vtiger_crmentity.deleted = 0 '.$useWhere;
		return $query;
	}

	/**
	 *
	 * @param $module
	 * @param $userid
	 * @param $accountid
	 *
	 * @return string
	 */
	public function clientViewRestriction($module, $userid, $accountid = null) {
		echo $module;
		echo $userid;
		echo $accountid;
		return '';
	}

	/**
	 * Apply security restriction (sharing privilege) query part for List view.
	 *
	 * @param $module
	 *
	 * @return string
	 */
	public function getListViewSecurityParameter($module) {
		$current_user_groups          = null;
		$current_user_parent_role_seq = null;
		$defaultOrgSharingPermission  = null;
		$is_admin                     = null;
		$profileGlobalPermission      = null;
		require('user_privileges/user_privileges.php');
		require('user_privileges/sharing_privileges.php');

		global $current_user;

		$sec_query = '';
		$tabid = getTabid($module);
		if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabid] == 3) {
				$sec_query .= " AND (vtiger_crmentity.smownerid in($current_user->id) OR vtiger_crmentity.smownerid IN 
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role 
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid 
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid 
						WHERE vtiger_role.parentrole LIKE '$current_user_parent_role_seq::%'
					) 
					OR vtiger_crmentity.smownerid IN 
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per 
						WHERE userid=$current_user->id AND tabid=$tabid
					) 
					OR 
						(";
					// Build the query based on the group association of current user.
					if (count($current_user_groups) > 0) {
						$sec_query .= ' vtiger_groups.groupid IN ('. implode(',', $current_user_groups) .') OR ';
					}
					$sec_query .= " vtiger_groups.groupid IN 
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid 
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=$current_user->id and tabid=$tabid
						)";
				$sec_query .= ')
				)';
		}
		return $sec_query;
	}

	/**
	 * Create query to export the records.

	 * @param $where

	 * @return string
	 */
	// @codingStandardsIgnoreStart
	public function create_export_query($where) {
		// @codingStandardsIgnoreEnd
		global $current_user;
		$thismodule = $_REQUEST['module'];
		include('include/utils/ExportUtils.php');
		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery($thismodule, 'detail_view');
		$fieldsList = getFieldsListFromQuery($sql);
		$query = "SELECT $fieldsList, vtiger_users.user_name AS user_name FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";
		if (!empty($this->customFieldTable)) {
			$query .= ' INNER JOIN '.$this->customFieldTable[0].' ON '.$this->customFieldTable[0].'.'.$this->customFieldTable[1] . ' = '.$this->table_name.'.'.$this->table_index;
		}
		$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'";
		$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($thismodule));
		$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

		for($i=0; $i<$linkedFieldsCount; $i++) {
			$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
			$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');
			$other = CRMEntity::getInstance($related_module);
			vtlib_setup_modulevars($related_module, $other);
			$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
		}

		$query .= $this->getNonAdminAccessControlQuery($thismodule,$current_user);
		$whereAuto = ' vtiger_crmentity.deleted=0';
		if ($where != '') {
			$query .= " WHERE ($where) AND $whereAuto";
		} else {
			$query .= " WHERE $whereAuto";
		}

		return $query;
	}

	/**
	 * Initialize this instance for importing.

	 * @param $module
	 */
	public function initImport($module) {
		$this->db = PearDatabase::getInstance();
		$this->initImportableFields($module);
	}

	/**
	 * Create list query to be shown at the last step of the import.
	 * Called From: modules/Import/UserLastImport.php

	 * @param $module

	 * @return string
	 */
	// @codingStandardsIgnoreStart
	public function create_import_query($module) {
		global $current_user;
		$query = "SELECT vtiger_crmentity.crmid, case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name, $this->table_name.* FROM $this->table_name
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index
			LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=vtiger_crmentity.crmid
			LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_users_last_import.assigned_user_id='$current_user->id'
			AND vtiger_users_last_import.bean_type='$module'
			AND vtiger_users_last_import.deleted=0";
		// @codingStandardsIgnoreEnd
		return $query;
	}

	/**
	 * Delete the last imported records.

	 * @param $module
	 * @param $user_id

	 * @return integer
	 */
	// @codingStandardsIgnoreStart
	public function undo_import($module, $user_id) {
		// @codingStandardsIgnoreEnd
		global $adb;
		$count = 0;
		$queryOne = "SELECT bean_id FROM vtiger_users_last_import WHERE assigned_user_id=? AND bean_type='$module' AND deleted=0";
		$resultOne = $adb->pquery($queryOne, array($user_id)) || die('Error getting last import for undo: '.mysqli_error($adb));
		while ($rowOne = $adb->fetchByAssoc($resultOne)) {
			$queryTwo = 'UPDATE vtiger_crmentity SET deleted=1 WHERE crmid=?';
			$resultTwo = $adb->pquery($queryTwo, array($rowOne['bean_id'])) || die('Error undoing last import: '.mysqli_error($adb));
			echo $resultTwo;
			$count++;
		}
		return $count;
	}

	/**
	 * Transform the value while exporting

	 * @param $key
	 * @param $value

	 * @return mixed
	 */
	// @codingStandardsIgnoreStart
	public function transform_export_value($key, $value) {
		// @codingStandardsIgnoreEnd
		return parent::transform_export_value($key, $value);
	}

	/**
	 * Function which will set the assigned user id for import record.
	 */
	// @codingStandardsIgnoreStart
	public function set_import_assigned_user() {
		// @codingStandardsIgnoreEnd
		global $current_user, $adb;
		$record_user = $this->column_fields['assigned_user_id'];
		if ($record_user != $current_user->id) {
			$sqlresult = $adb->pquery('SELECT id FROM vtiger_users WHERE id = ? UNION SELECT groupid AS id FROM vtiger_groups WHERE groupid = ?', array($record_user, $record_user));
			if ($this->db->num_rows($sqlresult)!= 1) {
				$this->column_fields['assigned_user_id'] = $current_user->id;
			} else {
				$row = $adb->fetchByAssoc($sqlresult, -1, false);
				if (isset($row['id']) && $row['id'] != -1) {
					$this->column_fields['assigned_user_id'] = $row['id'];
				} else {
					$this->column_fields['assigned_user_id'] = $current_user->id;
				}
			}
		}
	}

	/**
	 * Function which will give the basic query to find duplicates

	 * @param $module
	 * @param $tableCols
	 * @param $field_values
	 * @param $ui_type_arr
	 * @param string $selectCols

	 * @return string
	 */
	public function getDuplicatesQuery($module, $tableCols, $field_values, $ui_type_arr, $selectCols = '') {
		global $query;
		$selectClause = "SELECT $this->table_name.$this->table_index AS recordid, vtiger_users_last_import.deleted, $tableCols";
		if (isset($this->customFieldTable)) {
			$query .= ', ' . $this->customFieldTable[0] . '.* ';
		}
		$fromClause = " FROM $this->table_name";
		$fromClause .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";
		// Consider custom table join as well.
		if(isset($this->customFieldTable)) {
			$fromClause .= " INNER JOIN $this->customFieldTable[0] ON $this->customFieldTable[0].$this->customFieldTable[1] = $this->table_name.$this->table_index";
		}
		$fromClause .= ' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid 
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
		
		$whereClause = ' WHERE vtiger_crmentity.deleted = 0';
		$whereClause .= $this->getListViewSecurityParameter($module);
					
		if (isset($selectCols) && trim($selectCols) != '') {
			$subQuery = "SELECT $selectCols FROM  $this->table_name AS t INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t.$this->table_index";
			// Consider custom table join as well.
			if(isset($this->customFieldTable)) {
				$subQuery .= " LEFT JOIN $this->customFieldTable[0] tcf ON tcf.$this->customFieldTable[1] = t.$this->table_index";
			}
			$subQuery .= " WHERE crm.deleted=0 GROUP BY $selectCols HAVING COUNT(*)>1";
		} else {
			$subQuery = "SELECT $tableCols $fromClause $whereClause GROUP BY $tableCols HAVING COUNT(*)>1";
		}
		
		
		$query = $selectClause . $fromClause .
					' LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=' . $this->table_name .'.'.$this->table_index .
					' INNER JOIN (' . $subQuery . ') AS temp ON '.get_on_clause($field_values,$ui_type_arr,$module) .
					$whereClause .
					' ORDER BY $tableCols,'. $this->table_name .'.'.$this->table_index .' ASC';

		return $query;
	}

	/** @noinspection PhpUndefinedClassInspection */

	/**
	 * Invoked when special actions are performed on the module.

	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	// @codingStandardsIgnoreStart
	public function vtlib_handler($modulename, $event_type) {
		// @codingStandardsIgnoreEnd
		echo $modulename;
		if ($event_type == 'module.postinstall') {
			// Handle post installation actions
			echo 'Post-Install';
		} else if ($event_type == 'module.disabled') {
			//  Handle actions when this module is disabled.
			echo 'Disabled';
		} else if ($event_type == 'module.enabled') {
			//  Handle actions when this module is enabled.
			echo 'Enabled';
		} else if ($event_type == 'module.preuninstall') {
			//  Handle actions when this module is about to be deleted.
			echo 'Pre-Uninstall';
		} else if ($event_type == 'module.preupdate') {
			//  Handle actions before this module is updated.
			echo 'Pre-Update';
		} else if ($event_type == 'module.postupdate') {
			// Handle actions after this module is updated.
			echo 'Post-Update';
		}
	}

	/**
	 * Invoked when special actions are performed on the module.

	 * @param $id
	 * @param $cur_tab_id
	 * @param $rel_tab_id
	 * @param $actions
	 * @param $onlyquery
	 * @param $relationId

	 * @return string
	 */
	// @codingStandardsIgnoreStart
	public function get_related_list($id, $cur_tab_id, $rel_tab_id, $actions = false, $onlyquery = false, $relationId = false) {
		// @codingStandardsIgnoreEnd
		global $currentModule, $adb;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);

		// Some standard module class doesn't have required variables
		// that are used in the query, they are defined in this generic API
		vtlib_setup_modulevars($currentModule, $this);
		vtlib_setup_modulevars($related_module, $other);

		$singular_modname = 'SINGLE_' . $related_module;
		$button = '';
		$button .= $this->getActionsAux($id, $cur_tab_id, $rel_tab_id, $actions, $relationId, $singular_modname);
		//Se determinan las acciones via VTiger_Links de Tipo RELATED_LIST_LINKS
		$customlink_params = array('MODULE' => $currentModule, 'RECORD' => $this->id, 'ACTION' => vtlib_purify($_REQUEST['action']));
		$relatedListLinks = Vtiger_Link::getAllByType(getTabid($currentModule), array('RELATED_LIST_LINKS'), $customlink_params, $relationId);
		$button .= $this->getButtonAux($relatedListLinks, $singular_modname);
		// To make the edit or del link actions to return back to same view.

		$returnset = $this->returnSetAux($id);
		$query = "SELECT vtiger_crmentity.*, $other->table_name.*,".$other->table_name.'cf.*';
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query .= ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name";
		$more_relation = $this->moreRelationAux($other);
		$query .= " FROM $other->table_name";
		$query .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $other->table_name.$other->table_index";
		$query .= ' INNER JOIN '.$other->table_name.'cf ON '.$other->table_name.'.'.$other->table_index.' = '.$other->table_name.'cf.'.$other->table_index;
		$query .= ' INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid OR vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)';
		$query .= $more_relation;
		if ($other->table_name!='vtiger_users') {
			$query .= ' LEFT  JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid';
		}
		$query .= ' LEFT  JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$query .= " WHERE vtiger_crmentity.deleted = 0 AND (vtiger_crmentityrel.crmid = $id OR vtiger_crmentityrel.relcrmid = $id)";
		
		if ($rel_tab_id=='29') {
			$query="SELECT  vtiger_users.*, 
					CONCAT(vtiger_users.first_name,' ',vtiger_users.last_name) AS nombre FROM vtiger_users 
					INNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.relcrmid = vtiger_users.id  
					WHERE (vtiger_crmentityrel.crmid = $id) ";
		}
		//Se une los datos de los campos 10 para
		$sql = 'SELECT vtiger_field.fieldid, tablename, columnname FROM vtiger_fieldmodulerel 
				INNER JOIN vtiger_field ON (vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid) 
				WHERE (module = ? AND relmodule = ?) ';
		$result = $adb->pquery($sql,array($related_module, $currentModule));

		$otherTables = '';
		$otherMoreRelation = '';
		while ($row = $adb->fetchByAssoc($result)) {
			if (!empty($other->related_tables)) {
				foreach ($other->related_tables as $tname => $relmap) {
					$otherTables .= ", $tname.*";

					// Setup the default JOIN conditions if not specified
					if (empty($relmap[1])) {
						$relmap[1] = $other->table_name;
					}
					if (empty($relmap[2])) {
						$relmap[2] = $relmap[0];
					}
					$otherMoreRelation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
				}
			}

			$query.= ' UNION SELECT vtiger_crmentity.*, '.$row['tablename'].'.*, '.$other->table_name.'.* ';
			$query.= ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name".$otherTables;
			$query.= ' FROM vtiger_crmentity INNER JOIN '.$row['tablename'];
			$query.= ' ON (vtiger_crmentity.crmid = '.$row['tablename'].'.'.$other->tab_name_index[$row['tablename']].' AND deleted = 0)';
			$query.= ' INNER JOIN '.$other->table_name.' ON '.$other->table_name.'.'.$other->table_index.' = '.$row['tablename'].'.'.$other->tab_name_index[$row['tablename']].'';
			$query.= ' LEFT  JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid';
			$query.= ' LEFT  JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid'.$otherMoreRelation;
			$query.= ' WHERE '.$row['tablename'].'.'.$row['columnname'].' = '.$id;
		}
		
		if ($onlyquery) {
			return $query;
		}
	
		$return_value = GetRelatedList($currentModule, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null) {
			$return_value = array();
		}
		$return_value['CUSTOM_BUTTON'] = $button;

		return $return_value;
	}

	public function getButtonAux($relatedListLinks, $singular_modname) {
		$button = '';
		foreach ($relatedListLinks as $valor) {
			$cuenta = count($valor);
			for ($i = 0; $i < $cuenta; $i++) {
				if (!empty($valor[$i]->linkicon)) {
					$button .= '<a href="'.$valor[$i]->linkurl.'" target="_blank">
								<img id="aidPrintPasajeros" src="themes/images/'.$valor[$i]->linkicon.'" style="border: 0 solid #000000;vertical-align: middle;margin-left: 10px;" alt="'.$valor[$i]->linklabel.'" title="'.$valor[$i]->linklabel.'">
								</a>';
				} else {
					$button .= '<input title=\'' . getTranslatedString($valor[$i]->linklabel) . ' ' . getTranslatedString($singular_modname) . '\' class=\'crmbutton small create\'
								 onclick=\''.$valor[$i]->linkurl.'\' type=\'button\' name=\'button\'
								 value=\'' . getTranslatedString($valor[$i]->linklabel) . '\'>&nbsp;';
				}
			}
		}
		return $button;
	}

	public function getActionsAux($id, $cur_tab_id, $rel_tab_id, $actions, $relationId, $singular_modname) {
		global $currentModule, $adb;
		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$parenttab = getParentTab();
		$button = '';
		if ($actions) {
			if (is_string($actions)) {
				$actions = explode(',', strtoupper($actions));
			}
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= '<input title=\'' . getTranslatedString('LBL_SELECT') . ' ' . getTranslatedString($related_module) . '\' class=\'btn btn-primary btn-sm\' ';
				$button .= " type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\"";
				$button .= ' value=\'' . getTranslatedString('LBL_SELECT') . ' ' . getTranslatedString($related_module, $related_module) . '\'>&nbsp;';
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				if (!$relationId) {
					list($relationId) = $adb->fetch_row($adb->pquery('SELECT relation_id FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?', array($cur_tab_id, $rel_tab_id)));
				}
				$modaledit = getRelatedListProperty($relationId, 'modaledit');
				$fn = "this.form.action.value='EditView';this.form.module.value='$related_module'";
				$btnType = 'submit';
				if ($modaledit) {
					$fn = "loadModalEditUI('', '$related_module', $relationId, '$id', '$currentModule', '".$_REQUEST['header']."', '".$_REQUEST['actions']."')";
					$btnType = 'button';
				}
				$button .= '<input type=\'hidden\' name=\'createmode\' id=\'createmode\' value=\'link\'>';
				$button	.= '<input title=\'' . getTranslatedString('LBL_ADD_NEW') . ' ' . getTranslatedString($singular_modname) . '\' class=\'btn btn-primary btn-sm\'';
				$button	.= " onclick=\"$fn\" type='$btnType' name='button'";
				$button	.= ' value=\''.getTranslatedString('LBL_ADD_NEW').' '.getTranslatedString($singular_modname, $related_module).'\'>&nbsp;';
			}
		}
		return $button;
	}

	public function moreRelationAux($other) {
		global $query;
		$more_relation = '';
		if (!empty($other->related_tables)) {
			foreach ($other->related_tables as $tname => $relmap) {
				$query .= ", $tname.*";
				// Setup the default JOIN conditions if not specified
				if (empty($relmap[1])) {
					$relmap[1] = $other->table_name;
				}
				if (empty($relmap[2])) {
					$relmap[2] = $relmap[0];
				}
				$more_relation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
			}
		}
		return $more_relation;
	}

	public function returnSetAux($id) {
		global $currentModule, $singlepane_view;

		if ($singlepane_view == 'true') {
			$returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
		} else {
			$returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";
		}
		return $returnset;
	}

	public function getLeccionesCurso($id,$current_user){
		global $adb;
		$sql="SELECT vfl.*,CONCAT(va.`path`,va.`attachmentsid`,'_',va.`name`) AS material,va.`name` AS archivo FROM vtiger_formacion_lecciones vfl
				INNER JOIN vtiger_crmentity crm ON crm.`crmid`=vfl.`formacion_leccionesid` AND crm.`deleted`=0
				INNER JOIN vtiger_crmentityrel crmrel ON crmrel.`relcrmid`=crm.`crmid` AND crmrel.`crmid`=$focus->id
				LEFT JOIN vtiger_attachments va ON va.`attachmentsid`=vfl.`materiales`
				ORDER by vfl.orden ASC";
		$order   = array("\r\n", "\n", "\r");
		$replace = '<br />';
		$q=$adb->pquery($sql, array());
		$lecciones = array();
		while ($r=$adb->fetchByAssoc($q)) {
			$r=str_replace($order,$replace,$r);
			$r['ext']=getExtension($r['file']);
			$r['ext_arch']=strtolower(getExtension($r['archivo']));
			$eval=getEvaluacion($r['formacion_leccionesid']);
			if ($eval) {
				$r['eval'] = $eval;
				$preg=getPreguntas($eval[0]['formacion_pruebasid']);
				$r['preg'] =$preg;
				$test=checkExamenporUsuario($current_user->id,$eval[0]['formacion_pruebasid']);
				$r['test']=$test;
			}

			$lecciones[]=$r;
		}
		return $lecciones;
	}

	}

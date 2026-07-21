<?php
	require_once('data/CRMEntity.php');
	require_once('data/Tracker.php');

	class itemformacion extends CRMEntity {

		/** @var PearDatabase  */
		public $db;

		/** @var LoggerLog  */
		public $log; // Used in class functions of CRMEntity

		// @codingStandardsIgnoreStart
		/** @var string  */
		public $table_name = 'vtiger_itemformacion';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var string  */
		public $table_index= 'itemformacionid';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  */
		public $column_fields = array();
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var bool  */
		public $IsCustomModule = true;
		// @codingStandardsIgnoreEnd

		/** @var array  */
		public $customFieldTable = array('vtiger_itemformacioncf', 'itemformacionid');

		// @codingStandardsIgnoreStart
		/** @var array  */
		public $tab_name = array('vtiger_crmentity', 'vtiger_itemformacion', 'vtiger_itemformacioncf');
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  */
		public $tab_name_index = array(
			'vtiger_crmentity' => 'crmid',
			'vtiger_itemformacion'   => 'itemformacionid',
			'vtiger_itemformacioncf' => 'itemformacionid',
		);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  */
		public $list_fields = array (
			'Título'=>array('itemformacion','titulo'),
			'Descripción'=>array('itemformacion','descripcion'),
			'Tipo'=>array('itemformacion','tipo'),
		);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  */
		public $list_fields_name = array(
			'Título'=>'titulo',
			'Descripción'=>'descripcion',
			'Tipo'=>'tipo',
		);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var string  */
		public $list_link_field = 'titulo';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  */
		public $search_fields = array(
			'Título'=>array('itemformacion','titulo'),
		);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  */
		public $search_fields_name = array(
			'Título'=>array('titulo'),
		);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  */
		public $popup_fields = array('titulo');
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  */
		public $sortby_fields = array();
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var string  */
		public $def_basicsearch_col = 'titulo';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var string  */
		public $def_detailview_recname = 'titulo';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  */
		public $required_fields = array('titulo' => 1);
		// @codingStandardsIgnoreEnd


		// @codingStandardsIgnoreStart
		/** @var array  */
		public $special_functions = array('set_import_assigned_user');
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var string  */
		public $default_order_by = 'titulo';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var string  */
		public $default_sort_order='ASC';
		// @codingStandardsIgnoreEnd

		/** @var array  */
		// @codingStandardsIgnoreStart
		public $mandatory_fields = array('titulo');
		// @codingStandardsIgnoreEnd

		/** @Constructor */
		public function __construct() {
			global $log, $currentModule;
			$this->column_fields = getColumnFields($currentModule);
			$this->db = PearDatabase::getInstance();
			$this->log = $log;
		}

		public function getSortOrder() {
			global $currentModule;
			$sortOrder = $this->default_sort_order;
			if($_REQUEST['sorder']) {
				$sortOrder = $this->db->sql_escape_string($_REQUEST['sorder']);
			} else if ($_SESSION[$currentModule.'_Sort_Order']) {
				$sortOrder = $_SESSION[$currentModule . '_Sort_Order'];
			}
			return $sortOrder;
		}

		public function getOrderBy() {
			global $currentModule;
			$use_default_order_by = '';
			if(PerformancePrefs::getBoolean('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_order_by = $this->default_order_by;
			}
			$orderBy = $use_default_order_by;
			if ($_REQUEST['order_by']) {
				$orderBy = $this->db->sql_escape_string($_REQUEST['order_by']);
			} else if($_SESSION[$currentModule.'_Order_By']) {
				$orderBy = $_SESSION[$currentModule . '_Order_By'];
			}
			return $orderBy;
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
			$query = "SELECT vtiger_crmentity.*, $this->table_name.*";
			// Keep track of tables joined to avoid duplicates
			$joinedTables = array();
			// Select Custom Field Table Columns if present
			if(!empty($this->customFieldTable)) {
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

			$linkedModulesQuery = $this->db->pquery("SELECT DISTINCT fieldname, columnname, relmodule FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($module));

			$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

			for($i=0; $i<$linkedFieldsCount; $i++) {
				$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
				$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');
				$other = CRMEntity::getInstance($related_module);
				vtlib_setup_modulevars($related_module, $other);
				if(!in_array($other->table_name, $joinedTables)) {
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
		 * Apply security restriction (sharing privilege) query part for List view.

		 * @param $module

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
			$tabId = getTabid($module);
			if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabId] == 3) {
				// @codingStandardsIgnoreStart
				$sec_query .= " AND (vtiger_crmentity.smownerid in($current_user->id) OR vtiger_crmentity.smownerid IN
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
						WHERE vtiger_role.parentrole LIKE ' $current_user_parent_role_seq ::%'
					)
					OR vtiger_crmentity.smownerid IN
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
						WHERE userid= $current_user->id  AND tabid= $tabId
					)
					OR
						(";
				// @codingStandardsIgnoreEnd
				// Build the query based on the group association of current user.
				if (count($current_user_groups) > 0) {
					$sec_query .= ' vtiger_groups.groupid IN ('. implode(',', $current_user_groups) .') OR ';
				}
				$sec_query .= ' vtiger_groups.groupid IN
						(
							SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
							FROM vtiger_tmp_read_group_sharing_per
							WHERE userid=' . $current_user->id . ' and tabid=' . $tabId . '
						)';
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
			$thisModule = $_REQUEST['module'];
			include('include/utils/ExportUtils.php');
			//To get the Permitted fields query and the permitted fields list
			$sql = getPermittedFieldsQuery($thisModule, 'detail_view');
			$fieldsList = getFieldsListFromQuery($sql);
			$query = "SELECT $fieldsList, vtiger_users.user_name AS user_name FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";
			if(!empty($this->customFieldTable)) {
				$query .= ' INNER JOIN '.$this->customFieldTable[0].' ON '.$this->customFieldTable[0].'.'.$this->customFieldTable[1] . ' = $this->table_name.$this->table_index';
			}
			$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
			$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'";
			$linkedModulesQuery = $this->db->pquery("SELECT distinct fieldname, columnname, relmodule FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array($thisModule));
			$linkedFieldsCount = $this->db->num_rows($linkedModulesQuery);

			for($i=0; $i<$linkedFieldsCount; $i++) {
				$related_module = $this->db->query_result($linkedModulesQuery, $i, 'relmodule');
				$columnname = $this->db->query_result($linkedModulesQuery, $i, 'columnname');
				$other = CRMEntity::getInstance($related_module);
				vtlib_setup_modulevars($related_module, $other);
				$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
			}

			$query .= $this->getNonAdminAccessControlQuery($thisModule,$current_user);
			$whereAuto = ' vtiger_crmentity.deleted=0';
			if($where != '') {
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
			$query = "SELECT vtiger_crmentity.crmid, case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as user_name, $this->table_name * FROM $this->table_name
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
			while ( $rowOne = $adb->fetchByAssoc($resultOne))
			{
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

			if($record_user != $current_user->id) {
				$sqlResult = $adb->pquery('SELECT id FROM vtiger_users WHERE id = ? UNION SELECT groupid as id FROM vtiger_groups WHERE groupid = ?', array($record_user, $record_user));
				if($this->db->num_rows($sqlResult)!= 1) {
				$this->column_fields['assigned_user_id'] = $current_user->id;
				} else {
					$row = $adb->fetchByAssoc($sqlResult, -1, false);
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
			$select_clause = "SELECT $this->table_name.$this->table_index AS recordid, vtiger_users_last_import.deleted, $tableCols";

			$fromClause = " FROM $this->table_name";

			$fromClause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

			// Consider custom table join as well.
			if(isset($this->customFieldTable)) {
				$fromClause .= " INNER JOIN $this->customFieldTable[0] ON $this->customFieldTable[0].$this->customFieldTable[1] = $this->table_name.$this->table_index";
			}
			$fromClause .= ' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
			$whereClause = '	WHERE vtiger_crmentity.deleted = 0';
			$whereClause .= $this->getListViewSecurityParameter($module);

			if (isset($selectCols) && trim($selectCols) != '') {
				$sub_query = "SELECT $selectCols FROM  $this->table_name AS t INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t.".$this->table_index;
				// Consider custom table join as well.
				if(isset($this->customFieldTable)) {
					$sub_query .= ' LEFT JOIN $this->customFieldTable[0] tcf ON tcf.$this->customFieldTable[1] = t.$this->table_index';
				}
				$sub_query .= " WHERE crm.deleted=0 GROUP BY $selectCols HAVING COUNT(*)>1";
			} else {
				$sub_query = "SELECT $tableCols $fromClause $whereClause GROUP BY $tableCols HAVING COUNT(*)>1";
			}


			$query = $select_clause . $fromClause .
					' LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=' . $this->table_name .'.'.$this->table_index .
					' INNER JOIN (' . $sub_query . ') AS temp ON '.get_on_clause($field_values,$ui_type_arr,$module) .
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
			if($event_type == 'module.postinstall') {
				// Handle post installation actions
				echo 'Post-Install';
			} else if($event_type == 'module.disabled') {
				// Handle actions when this module is disabled.
				echo 'Disabled';
			} else if($event_type == 'module.enabled') {
				// Handle actions when this module is enabled.
				echo 'Enabled';
			} else if($event_type == 'module.preuninstall') {
				// Handle actions when this module is about to be deleted.
				echo 'Pre-Uninstall';
			} else if($event_type == 'module.preupdate') {
				// Handle actions before this module is updated.
				echo 'Pre-Update';
			} else if($event_type == 'module.postupdate') {
				// Handle actions after this module is updated.
				echo 'Post-Update';
			}
		}

	}

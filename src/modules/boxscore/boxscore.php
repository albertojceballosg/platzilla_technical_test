<?php
	require_once ('data/CRMEntity.php');
	require_once ('data/Tracker.php');
	require_once ('modules/boxscore/box_score.php');
	require_once ('modules/cuatroq/cuatroq.php');

class boxscore extends CRMEntity {
	public $db;
	public $log;

	public $table_name = 'vtiger_boxscore';
	public $table_index = 'boxscoreid';
	public $column_fields = array ();
	public $IsCustomModule = true;
	public $customFieldTable = array ('vtiger_boxscorecf', 'boxscoreid');
	public $tab_name = array ('vtiger_crmentity', 'vtiger_boxscore', 'vtiger_boxscorecf');

	public $tab_name_index = array (
		'vtiger_crmentity' => 'crmid',
		'vtiger_boxscore' => 'boxscoreid',
		'vtiger_boxscorecf' => 'boxscoreid',
	);

	public $list_fields = array (
		'Titulo' => array ('boxscore', 'titulo'),
		'Fecha' => array ('boxscore', 'fecha'),
		'Descripción' => array ('boxscore', 'descripcion'),
	);

	public $list_fields_name = array (
		'Titulo' => 'titulo',
		'Fecha' => 'fecha',
		'Descripción' => 'descripcion',
	);

	public $list_link_field = 'titulo';
	public $search_fields = array ('Titulo' => array ('boxscore', 'titulo'));
	public $search_fields_name = array ('Titulo' => 'titulo');
	public $popup_fields = array ('titulo');
	public $sortby_fields = array ();
	public $def_basicsearch_col = 'titulo';
	public $def_detailview_recname = 'titulo';
	public $required_fields = array ('titulo' => 1);
	public $special_functions = array ('set_import_assigned_user');
	public $default_order_by = 'titulo';
	public $default_sort_order = 'ASC';
	public $mandatory_fields = array ('titulo');

	public function __construct () {
		global $log, $currentModule;
		$this->column_fields = getColumnFields ($currentModule);
		$this->db = PearDatabase::getInstance ();
		$this->log = $log;
	}

	private function getLeftJoinClauses ($moduleName) {
		$sql = "SELECT DISTINCT
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
		$leftJoinClauses = array ();
		$linkedFieldsCount = $this->db->num_rows ($linkedFieldsResult);
		for ($i = 0; $i < $linkedFieldsCount; $i++) {
			$relatedModule = $this->db->query_result ($linkedFieldsResult, $i, 'relmodule');
			$columnName = $this->db->query_result ($linkedFieldsResult, $i, 'columnname');
			$linkedModule = CRMEntity::getInstance ($relatedModule);
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

	// @codingStandardsIgnoreStart
	/**
	 * Save module
	 */
	public function save_module () {
	}
	// @codingStandardsIgnoreEnd

	/**
	 * Get Query By Module Field
	 */
	public function getQueryByModuleField () {
		// $srcrecord could be empty
	}

	public function getListQuery ($module, $additionalWhereClause = '') {
		global $current_user;

		// Keep track of tables joined to avoid duplicates
		$joinedTables = array (
			$this->table_name,
			'vtiger_crmentity',
			'vtiger_users',
			'vtiger_groups',
		);

		// Preparar cláusulas INNER JOIN con las tablas de campos personalizados (si existen)
		if (!empty ($this->customFieldTable)) {
			$customTableSelectClause = ", {$this->customFieldTable [0]}.*";
			$customTableJoinClause = "INNER JOIN {$this->customFieldTable [0]} ON {$this->customFieldTable [0]}.{$this->customFieldTable [1]}={$this->table_name}.{$this->table_index}";
			$joinedTables[] = $this->customFieldTable [0];
		} else {
			$customTableSelectClause = '';
			$customTableJoinClause = '';
		}

		// Obtener los módulos enlazados
		$sql = "SELECT DISTINCT
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
				$columnName = $this->db->query_result ($linkedModulesResult, $i, 'columnname');
				$linkedEntity = CRMEntity::getInstance ($relatedModule);
				vtlib_setup_modulevars ($relatedModule, $linkedEntity);
				if (!in_array ($linkedEntity->table_name, $joinedTables)) {
					$leftJoinClauses[] = "{$linkedEntity->table_name} ON {$linkedEntity->table_name}.{$linkedEntity->table_index}={$this->table_name}.$columnName";
					$joinedTables[] = $linkedEntity->table_name;
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

	public function getListViewSecurityParameter ($module) {
		$current_user_groups = null;
		$current_user_parent_role_seq = null;
		$defaultOrgSharingPermission = null;
		$is_admin = null;
		$profileGlobalPermission = null;
		require ('user_privileges/user_privileges.php');
		require ('user_privileges/sharing_privileges.php');

		global $current_user;

		$sql = '';
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

			$currentUserGroups = implode (',', $current_user_groups);
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

	// @codingStandardsIgnoreStart
	public function create_export_query ($whereClauses) {
		global $current_user;

		$moduleName = isset ($_REQUEST ['module']) ? $_REQUEST ['module'] : '';

		// Obtener los campos enlazados
		$leftJoinClauses = $this->getLeftJoinClauses ($moduleName);

		// Obtener los campos permitidos al usuario
		require_once ('include/utils/ExportUtils.php');
		$fieldsListQuery = getPermittedFieldsQuery ($moduleName, 'detail_view');
		$fieldsList = getFieldsListFromQuery ($fieldsListQuery);
		$whereClauses  = $whereClauses ? "$whereClauses AND" : '';
		$sql = "SELECT
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
	// @codingStandardsIgnoreEnd

	/**
	 * @param $module
	 */
	public function initImport ($module) {
		$this->db = PearDatabase::getInstance ();
		$this->initImportableFields ($module);
	}

	// @codingStandardsIgnoreStart
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
	// @codingStandardsIgnoreEnd

	// @codingStandardsIgnoreStart
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
	// @codingStandardsIgnoreEnd

	// @codingStandardsIgnoreStart
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
			$row = $adb->fetchByAssoc ($result, -1, false);
			$this->column_fields ['assigned_user_id'] = (isset ($row ['id'])) && ($row ['id'] != -1) ? $row ['id'] : $current_user->id;
		}
	}
	// @codingStandardsIgnoreEnd

	/**
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
		$sql = "SELECT
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

	public function registerCuatroq ($boxScoreDataId, $boxScoreId, $weeklyId, $date, $automatic) {
		global $adb, $current_user, $plat;
		// Si la plataforma es bundy, no se registra 4Q
		if ($plat == 'bundy') {
			return;
		}

		$result = $adb->pquery (
			'SELECT * FROM vtiger_cuatroq WHERE semanalid=? AND boxscore=? AND box_score_dataid=?',
			array ($weeklyId, $boxScoreId, $boxScoreDataId)
		);

		/** @var cuatroq|stdClass $entity */
		$entity = new cuatroq();
		if (($result) && ($adb->num_rows ($result) > 0)) {
			$row = $adb->fetchByAssoc ($result);
			$entity->mode = 'edit';
			$entity->id = $row ['cuatroqid'];
		}
		$entity->column_fields ['boxscore']  = $boxScoreId;
		$entity->column_fields ['box_score_dataid'] = $boxScoreDataId;
		$entity->column_fields ['semanalid'] = $weeklyId;
		$entity->column_fields ['fecha'] = $date;
		$entity->column_fields ['automatico'] = $automatic;
		$entity->column_fields ['assigned_user_id'] = $current_user->id;
		$entity->save ('cuatroq');
	}

	public function getCuatroq ($boxScoreId) {
		global $adb;
		$result = $adb->pquery ('SELECT * FROM vtiger_cuatroq WHERE boxscore=?', array ($boxScoreId));
		if ((!$result) || (!$adb->num_rows ($result) == 0)) {
			return array ();
		}

		$cuatroq = array ();
		while ($row = $adb->fetchByAssoc ($result)) {
			$cuatroq [ $row ['box_score_dataid'] ][ $row ['fecha'] ] = $row ['automatico'];
		}
		return $cuatroq;
	}

	public function getWeeklyId ($boxScoreDataId, $boxScoreId, $date) {
		global $adb;
		$result = $adb->pquery (
			'SELECT semanalid FROM vtiger_box_score_data_semanal WHERE box_score_dataid=? AND boxscoreid=? AND fecha=?',
			array ($boxScoreDataId, $boxScoreId, $date)
		);
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return null;
		}
		$row = $adb->fetchByAssoc ($result);
		return $row ['semanalid'];
	}

	public function getAssignedUserId ($recordId) {
		global $adb;
		$result = $adb->pquery ('SELECT smownerid FROM vtiger_crmentity WHERE crmid=?', array ($recordId));
		if ((!$result) || ($adb->num_rows ($result) == 0)) {
			return null;
		}
		$row = $adb->fetchByAssoc ($result);
		return $row ['smownerid'];
	}

	public function updateBoxScorePermissions ($oldUserId, $newUserId, $recordId) {
		global $adb;
		$adb->pquery ('UPDATE vtiger_boxscore_privileges SET userid=? WHERE userid=? AND boxscoreid=?', array ($newUserId, $oldUserId, $recordId));
	}

	public function getDatesFromTo($from, $escala) {
		$dates = array ();
		$monday = date ('Y-m-d', strtotime ('last monday', strtotime ("{$from} +1 day")));
		if ($escala == 'Week') {
			for ($i = 0; $i < 10; $i++) {
				$w = strtotime ("{$monday} +{$i} week");
				$w = BoxScoreHelper::checkLastWeek($w);
				$dates [] = array (
					'date' => date ('Y-m-d', $w),
					'week' => intval (date ('W', $w)),
				);
			}
		} else {
			for ($i = 0; $i < 5; $i++) {
				$w = strtotime ("{$monday} +{$i} month");
				$w = BoxScoreHelper::checkLastWeek($w);
				$dates [] = array (
					'date' => date ('Y-m-d', $w),
					'week' => intval (date ('W', $w)),
				);
			}
		}

		return $dates;
	}

	public function getDatesEscalaWeek($month, $year) {
		$dates = array ();
		$firstMonday = date ('Y-m-d', strtotime ("first monday of {$month} {$year}"));
		$monday = date ('Y-m-d', strtotime ("last monday of {$month} {$year}"));
		$startDate = new DateTime ($firstMonday);
		$endDate = new DateTime ($monday);
		$interval = $startDate->diff ($endDate);
		$w = intval (($interval->days) / 7);

		for ($i = $w; $i >= 0; $i--) {
			$w = strtotime ("{$monday} -{$i} week");
			$w = BoxScoreHelper::checkLastWeek($w);
			$dates [] = array (
				'date'  => date ('Y-m-d', $w),
				'week'  => intval (date ('W', $w)),
				'month' => intval (date ('m', $w)),
				'year'  => date ('Y', $w),
			);
		}

		return $dates;
	}

	public function caseMenorIgualLimit($objective, $inObjectiveVariance, $closeToObjectiveVariance) {
		$result = array();
		$inObjectiveLimit = ($objective * (1 - ($inObjectiveVariance / 100)));
		$closeToObjectiveLimit = ($objective * (1 - ($closeToObjectiveVariance / 100)));
		if (
			(($objective < 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance > 0)) ||
			(($objective > 0) && ($inObjectiveVariance > 0) && ($closeToObjectiveVariance < 0))
		) {
			// Caso 1: objective -, inObjectiveVariance -, closeToObjectiveVariance + || objective +, inObjectiveVariance +, closeToObjectiveVariance -
			$result['inObjectiveMin'] = $inObjectiveLimit;
			$result['inObjectiveMax'] = $objective;
			$result['closeToObjectiveMin'] = $objective;
			$result['closeToObjectiveMax'] = $closeToObjectiveLimit;
		} else if (
			(($objective < 0) && ($inObjectiveVariance > 0) && ($closeToObjectiveVariance < 0)) ||
			(($objective > 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance > 0))
		) {
			// Caso 2: objective -, inObjectiveVariance +, closeToObjectiveVariance - || objective +, inObjectiveVariance -, closeToObjectiveVariance +
			$result['inObjectiveMin'] = $objective;
			$result['inObjectiveMax'] = $inObjectiveLimit;
			$result['closeToObjectiveMin'] = $closeToObjectiveLimit;
			$result['closeToObjectiveMax'] = $objective;
		} else if (($objective > 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance < 0)) {
			// Caso 3: objective +, inObjectiveVariance -, closeToObjectiveVariance -
			$result['inObjectiveMin'] = $objective;
			$result['inObjectiveMax'] = $inObjectiveLimit;
			$result['closeToObjectiveMin'] = $inObjectiveLimit;
			$result['closeToObjectiveMax'] = $closeToObjectiveLimit;
		} else {
			$result['inObjectiveMin'] = $inObjectiveLimit;
			$result['inObjectiveMax'] = $objective;
			$result['closeToObjectiveMin'] = $closeToObjectiveLimit;
			$result['closeToObjectiveMax'] = $inObjectiveLimit;
		}

		return $result;
	}

	public function caseElseLimit($objective, $inObjectiveVariance, $closeToObjectiveVariance) {
		$result = array();
		$inObjectiveLimit = ($objective * (1 + ($inObjectiveVariance / 100)));
		$closeToObjectiveLimit = ($objective * (1 + ($closeToObjectiveVariance / 100)));
		if (
			(($objective < 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance < 0)) ||
			(($objective > 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance < 0))
		) {
			// Caso 1: objective -, inObjectiveVariance -, closeToObjectiveVariance - || objective +, inObjectiveVariance -, closeToObjectiveVariance -
			$result['inObjectiveMin'] = $inObjectiveLimit;
			$result['inObjectiveMax'] = $objective;
			$result['closeToObjectiveMin'] = $closeToObjectiveLimit;
			$result['closeToObjectiveMax'] = $inObjectiveLimit;
		} else if (
			(($objective < 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance > 0)) ||
			(($objective > 0) && ($inObjectiveVariance > 0) && ($closeToObjectiveVariance < 0))
		) {
			// Caso 2: objective -, inObjectiveVariance -, closeToObjectiveVariance + || objective +, inObjectiveVariance +, closeToObjectiveVariance -
			$result['inObjectiveMin'] = $objective;
			$result['inObjectiveMax'] = $inObjectiveLimit;
			$result['closeToObjectiveMin'] = $closeToObjectiveLimit;
			$result['closeToObjectiveMax'] = $objective;
		} else if (
			(($objective < 0) && ($inObjectiveVariance > 0) && ($closeToObjectiveVariance < 0)) ||
			(($objective > 0) && ($inObjectiveVariance < 0) && ($closeToObjectiveVariance > 0))
		) {
			// Caso 3: objective -, inObjectiveVariance +, closeToObjectiveVariance -|| objective +, inObjectiveVariance -, closeToObjectiveVariance +
			$result['inObjectiveMin'] = $inObjectiveLimit;
			$result['inObjectiveMax'] = $objective;
			$result['closeToObjectiveMin'] = $objective;
			$result['closeToObjectiveMax'] = $closeToObjectiveLimit;
		} else {
			$result['inObjectiveMin'] = $objective;
			$result['inObjectiveMax'] = $inObjectiveLimit;
			$result['closeToObjectiveMin'] = $inObjectiveLimit;
			$result['closeToObjectiveMax'] = $closeToObjectiveLimit;
		}

		return $result;
	}

}

<?php
	require_once ('data/CRMEntity.php');
	require_once ('data/Tracker.php');

	class product extends CRMEntity {

		/** @var PearDatabase */
		public $db; // Used in class functions of CRMEntity

		/** @var LoggerLog */
		public $log;

		// @codingStandardsIgnoreStart
		/** @var string */
		public $table_name = 'vtiger_product';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var string */
		public $table_index = 'productid';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array */
		public $column_fields = array ();
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var bool */
		public $IsCustomModule = true;
		// @codingStandardsIgnoreEnd

		/** @var array  Mandatory table for supporting custom fields. */
		public $customFieldTable = array ('vtiger_productcf', 'productid');

		// @codingStandardsIgnoreStart
		/** @var array  Mandatory for Saving, Include tables related to this module. */
		public $tab_name = array ('vtiger_crmentity', 'vtiger_product', 'vtiger_productcf');
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  Mandatory for Saving, Include tablename and tablekey columnname here. */
		public $tab_name_index = array (
			'vtiger_crmentity' => 'crmid',
			'vtiger_product'   => 'productid',
			'vtiger_productcf' => 'productid',
		);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array  Mandatory for Listing (Related listview) */
		public $list_fields = array (
			'Nro. producto'          => array ('product', 'product_no'),
			'Nombre'                 => array ('product', 'productname'),
			'Código'                 => array ('product', 'productcode'),
			'Categoría'              => array ('product', 'productcategory'),
			'Manufactura'            => array ('product', 'manufacturer'),
			'Cantidad por Unidad'    => array ('product', 'qty_per_unit'),
			'Precio Unitario'        => array ('product', 'unit_price'),
			'Medida'                 => array ('product', 'weight'),
			'Longitud'               => array ('product', 'pack_size'),
			'Fecha Venta Inicial'    => array ('product', 'sales_start_date'),
			'Fecha Venta Final'      => array ('product', 'sales_end_date'),
			'Fecha Inicial'          => array ('product', 'start_date'),
			'Fecha Vencimiento'      => array ('product', 'expiry_date'),
			'Factor de Costo'        => array ('product', 'cost_factor'),
			'Porcentaje Comisión'    => array ('product', 'commissionrate'),
			'Método comisión'        => array ('product', 'commissionmethod'),
			'Descontinuado'          => array ('product', 'discontinued'),
			'Unidad de Uso'          => array ('product', 'usageunit'),
			'reorderlevel'           => array ('product', 'reorderlevel'),
			'Cantidad en Existencia' => array ('product', 'qtyinstock'),
			'Cantidad Demandada'     => array ('product', 'qtyindemand'),
			'Proveedor'              => array ('product', 'proveedor_id'),
			'Imágen'                 => array ('product', 'imagename'),
			'Moneda'                 => array ('product', 'currency_id'),
		);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array */
		public $list_fields_name = array (
			'Nro. producto'          => 'product_no',
			'Nombre'                 => 'productname',
			'Código'                 => 'productcode',
			'Categoría'              => 'productcategory',
			'Manufactura'            => 'manufacturer',
			'Cantidad por Unidad'    => 'qty_per_unit',
			'Precio Unitario'        => 'unit_price',
			'Medida'                 => 'weight',
			'Longitud'               => 'pack_size',
			'Fecha Venta Inicial'    => 'sales_start_date',
			'Fecha Venta Final'      => 'sales_end_date',
			'Fecha Inicial'          => 'start_date',
			'Fecha Vencimiento'      => 'expiry_date',
			'Factor de Costo'        => 'cost_factor',
			'Porcentaje Comisión'    => 'commissionrate',
			'Método comisión'        => 'commissionmethod',
			'Descontinuado'          => 'discontinued',
			'Unidad de Uso'          => 'usageunit',
			'reorderlevel'           => 'reorderlevel',
			'Cantidad en Existencia' => 'qtyinstock',
			'Cantidad Demandada'     => 'qtyindemand',
			'Proveedor'              => 'proveedor_id',
			'Imágen'                 => 'imagename',
			'Moneda'                 => 'currency_id',
		);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var string  Make the field link to detail view from list view (Fieldname) */
		public $list_link_field = 'product_no';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array */
		public $search_fields = array (
			'Nro. producto' => array ('product', 'product_no'),
			'Nombre'        => array ('product', 'productname'),
			'Código'        => array ('product', 'productcode'),
			'Precio'        => array ('product', 'unit_price'),
		);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array */
		public $search_fields_name = array (
			'Nro. producto' => 'product_no',
		);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		// For Popup window record selection
		/** @var array */
		//public $popup_fields = array('product_no','productname','productcode','unit_price');
		public $popup_fields = array ('product_no', 'productname', 'productcode', 'unit_price');
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var array */
		public $sortby_fields = array ();
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		// For Alphabetical search
		/** @var string */
		public $def_basicsearch_col = 'product_no';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		// Column value to use on detail view record text display
		/** @var string */
		public $def_detailview_recname = 'product_no';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		// Required Information for enabling Import feature
		/** @var array */
		public $required_fields = array ('product_no' => 1);
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		// Callback function list during Importing
		/** @var array */
		public $special_functions = array ('set_import_assigned_user');
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var string */
		public $default_order_by = 'product_no';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		/** @var string */
		public $default_sort_order = 'ASC';
		// @codingStandardsIgnoreEnd

		// @codingStandardsIgnoreStart
		// Used when enabling/disabling the mandatory fields for the module.
		// Refers to vtiger_field.fieldname values.
		/** @var array */
		public $mandatory_fields = array ('product_no');
		public $id;
		public $mode;
		// @codingStandardsIgnoreEnd

		/** @Constructor */
		public function __construct () {
			global $log, $currentModule;
			$this->column_fields = getColumnFields ($currentModule);
			$this->db            = PearDatabase::getInstance ();
			$this->log           = $log;
		}

		public function getSortOrder () {
			global $currentModule;

			$sortorder = $this->default_sort_order;
			if ($_REQUEST['sorder']) {
				$sortorder = $this->db->sql_escape_string ($_REQUEST['sorder']);
			} else if ($_SESSION[ $currentModule . '_Sort_Order' ]) {
				$sortorder = $_SESSION[ $currentModule . '_Sort_Order' ];
			}

			return $sortorder;
		}

		public function getOrderBy () {
			global $currentModule;

			$use_default_order_by = '';
			if (PerformancePrefs::getBoolean ('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_order_by = $this->default_order_by;
			}

			$orderby = $use_default_order_by;
			if ($_REQUEST['order_by']) {
				$orderby = $this->db->sql_escape_string ($_REQUEST['order_by']);
			} else if ($_SESSION[ $currentModule . '_Order_By' ]) {
				$orderby = $_SESSION[ $currentModule . '_Order_By' ];
			}
			return $orderby;
		}

		// @codingStandardsIgnoreStart
		public function save_module ($module) {
			// @codingStandardsIgnoreEnd
			echo $module;
			//Inserting into product_taxrel table
			if ($_REQUEST['ajxaction'] != 'DETAILVIEW' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates') {
				$this->insertTaxInformation ('vtiger_producttaxrel', 'product');
				$this->insertPriceInformation ('vtiger_productcurrencyrel', 'product');
			}

			$this->updateUnitPrice ();
		}

		/**
		 * Function to save the product tax information in vtiger_producttaxrel table
		 *
		 * @param string $tablename
		 * @param string $module
		 *    $return void
		 */
		public function insertTaxInformation ($tablename, $module) {
			global $adb, $log;
			$log->debug ("Entering into insertTaxInformation($tablename, $module) method ...");
			$tax_details = getAllTaxes ();

			//Save the Product - tax relationship if corresponding tax check box is enabled
			//Delete the existing tax if any
			$associated_tax_count = count ($tax_details);
			if ($this->mode == 'edit') {
				for ($i = 0; $i < $associated_tax_count; $i++) {
					$taxid = getTaxId ($tax_details[ $i ]['taxname']);
					$sql   = 'DELETE FROM vtiger_producttaxrel WHERE productid=? AND taxid=?';
					$adb->pquery ($sql, array ($this->id, $taxid));
				}
			}
			for ($i = 0; $i < $associated_tax_count; $i++) {
				$taxName       = $tax_details[ $i ]['taxname'];
				$tax_checkname = $tax_details[ $i ]['taxname'] . '_check';
				if ($_REQUEST[ $tax_checkname ] == 'on' || $_REQUEST[ $tax_checkname ] == 1) {
					$taxid   = getTaxId ($taxName);
					$tax_per = $_REQUEST[ $taxName ];
					if ($tax_per == '') {
						$log->debug ('Tax selected but value not given so default value will be saved.');
						$tax_per = getTaxPercentage ($taxName);
					}
					$log->debug ("Going to save the Product - $taxName tax relationship");

					$query = 'INSERT INTO vtiger_producttaxrel (productid,taxid,taxpercentage) VALUES(?,?,?)';
					$adb->pquery ($query, array ($this->id, $taxid, $tax_per));
				}
			}

			$log->debug ("Exiting from insertTaxInformation($tablename, $module) method ...");
		}

		/**
		 * Function to save the service price information in vtiger_servicecurrencyrel table
		 *
		 * @param string $tablename Vtiger_tablename to save the service currency relationship (servicecurrencyrel)
		 * @param string $module Current module name
		 *    $return void
		 */
		public function insertPriceInformation ($tablename, $module) {
			global $adb, $log;
			$log->debug ("Entering into insertPriceInformation($tablename, $module) method ...");
			//removed the update of currency_id based on the logged in user's preference : fix 6490

			$currency_details = getAllCurrencies ('all');

			//Delete the existing currency relationship if any
			if ($this->mode == 'edit' && $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates') {
				$n = count ($currency_details);
				for ($i = 0; $i < $n; $i++) {
					$curid = $currency_details[ $i ]['curid'];
					$sql   = 'DELETE FROM vtiger_productcurrencyrel WHERE productid=? AND currencyid=?';
					$adb->pquery ($sql, array ($this->id, $curid));
				}
			}

			$service_base_conv_rate = getBaseConversionRateForProduct ($this->id, $this->mode, $module);

			//Save the Product - Currency relationship if corresponding currency check box is enabled
			$n = count ($currency_details);
			for ($i = 0; $i < $n; $i++) {
				$curid               = $currency_details[ $i ]['curid'];
				$curname             = $currency_details[ $i ]['currencylabel'];
				$cur_checkname       = 'cur_' . $curid . '_check';
				$cur_valuename       = 'curname' . $curid;
				$requestPrice        = CurrencyField::convertToDBFormat ($_REQUEST['unit_price'], null, true);
				$actualPrice         = CurrencyField::convertToDBFormat ($_REQUEST[ $cur_valuename ], null, true);
				if ($_REQUEST[ $cur_checkname ] == 'on' || $_REQUEST[ $cur_checkname ] == 1) {
					$conversion_rate        = $currency_details[ $i ]['conversionrate'];
					$actual_conversion_rate = ($service_base_conv_rate * $conversion_rate);
					$converted_price        = ($actual_conversion_rate * $requestPrice);

					$log->debug ("Going to save the Product - $curname currency relationship");

					$query = 'INSERT INTO vtiger_productcurrencyrel (productid, currencyid, converted_price, actual_price) VALUES(?,?,?,?)';
					$adb->pquery ($query, array ($this->id, $curid, $converted_price, $actualPrice));

					// Update the Product information with Base Currency choosen by the User.
					if ($_REQUEST['base_currency'] == $cur_valuename) {
						$adb->pquery ('UPDATE vtiger_product SET currency_id=?, unit_price=? WHERE productid=?', array ($curid, $actualPrice, $this->id));
					}
				}
			}

			$log->debug ("Exiting from insertPriceInformation($tablename, $module) method ...");
		}

		public function updateUnitPrice () {
			$prod_res           = $this->db->pquery ('SELECT unit_price, currency_id FROM vtiger_product WHERE productid=?', array ($this->id));
			$prod_unit_price    = $this->db->query_result ($prod_res, 0, 'unit_price');
			$prod_base_currency = $this->db->query_result ($prod_res, 0, 'currency_id');

			$query  = 'UPDATE vtiger_productcurrencyrel SET actual_price=? WHERE productid=? AND currencyid=?';
			$params = array ($prod_unit_price, $this->id, $prod_base_currency);
			$this->db->pquery ($query, $params);
		}

		/**
		 * Return query to use based on given modulename, fieldname
		 * Useful to handle specific case handling for Popup
		 *
		 * Param $module
		 * Param $fieldname
		 * Param $srcrecord
		 * Param string $query
		 */
		public function getQueryByModuleField () {
			// do nothing
		}

		/**
		 * Get list view query (send more WHERE clause condition if required)
		 *
		 * @param $module
		 * @param string $usewhere
		 *
		 * @return string
		 */
		public function getListQuery ($module, $usewhere = '') {
			$query = "SELECT vtiger_crmentity.*, $this->table_name.*";

			// Keep track of tables joined to avoid duplicates
			$joinedTables = array ();

			// Select Custom Field Table Columns if present
			if (!empty($this->customFieldTable)) {
				$query .= ', ' . $this->customFieldTable[0] . '.* ';
			}
			$query .= " FROM $this->table_name";

			$query .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

			$joinedTables[] = $this->table_name;
			$joinedTables[] = 'vtiger_crmentity';

			// Consider custom table join as well.
			if (!empty($this->customFieldTable)) {
				$query .= ' INNER JOIN ' . $this->customFieldTable[0] . ' ON ' . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] . ' = ' . $this->table_name . '.' . $this->table_index;
				$joinedTables[] = $this->customFieldTable[0];
			}
			$query .= ' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid';
			$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';

			$joinedTables[] = 'vtiger_users';
			$joinedTables[] = 'vtiger_groups';

			$linkedModulesQuery = $this->db->pquery ("SELECT DISTINCT fieldname, columnname, relmodule FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array ($module));
			$linkedFieldsCount  = $this->db->num_rows ($linkedModulesQuery);

			for ($i = 0; $i < $linkedFieldsCount; $i++) {
				$related_module = $this->db->query_result ($linkedModulesQuery, $i, 'relmodule');
				$columnname     = $this->db->query_result ($linkedModulesQuery, $i, 'columnname');

				$other = CRMEntity::getInstance ($related_module);
				vtlib_setup_modulevars ($related_module, $other);

				if (!in_array ($other->table_name, $joinedTables)) {
					$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
					$joinedTables[] = $other->table_name;
				}
			}

			global $current_user;
			$query .= $this->getNonAdminAccessControlQuery ($module, $current_user);
			$query .= ' WHERE vtiger_crmentity.deleted = 0 ' . $usewhere;

			return $query;
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

			$sec_query = '';
			$tabid     = getTabid ($module);

			if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[ $tabid ] == 3) {
				// @codingStandardsIgnoreStart
				$sec_query .= " AND (vtiger_crmentity.smownerid in($current_user->id) OR vtiger_crmentity.smownerid IN
					(
						SELECT vtiger_user2role.userid FROM vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid
						WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'
					)
					OR vtiger_crmentity.smownerid IN
					(
						SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per
						WHERE userid=" . $current_user->id . ' AND tabid=' . $tabid . '
                    )
                    OR
                        (';
				// @codingStandardsIgnoreEnd
				// Build the query based on the group association of current user.
				if (count ($current_user_groups) > 0) {
					$sec_query .= ' vtiger_groups.groupid IN (' . implode (',', $current_user_groups) . ') OR ';
				}
				$sec_query .= ' vtiger_groups.groupid IN
                        (
                            SELECT vtiger_tmp_read_group_sharing_per.sharedgroupid
                            FROM vtiger_tmp_read_group_sharing_per
                            WHERE userid=' . $current_user->id . ' and tabid=' . $tabid . '
                        )';
				$sec_query .= ')
                )';
			}
			return $sec_query;
		}

		/**
		 * Create query to export the records.
		 *
		 * @param $where
		 *
		 * @return string
		 */
		// @codingStandardsIgnoreStart
		public function create_export_query ($where) {
			// @codingStandardsIgnoreEnd
			global $current_user;
			$thismodule = $_REQUEST['module'];

			include ('include/utils/ExportUtils.php');

			//To get the Permitted fields query and the permitted fields list
			$sql = getPermittedFieldsQuery ($thismodule, 'detail_view');

			$fieldsList = getFieldsListFromQuery ($sql);

			$query = "SELECT $fieldsList, vtiger_users.user_name AS user_name FROM vtiger_crmentity INNER JOIN $this->table_name ON vtiger_crmentity.crmid=$this->table_name.$this->table_index";

			if (!empty($this->customFieldTable)) {
				$query .= ' INNER JOIN ' . $this->customFieldTable[0] . ' ON ' . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] . " = $this->table_name.$this->table_index";
			}

			$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
			$query .= " LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id and vtiger_users.status='Active'";

			$linkedModulesQuery = $this->db->pquery ("SELECT DISTINCT fieldname, columnname, relmodule FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE uitype='10' AND vtiger_fieldmodulerel.module=?", array ($thismodule));
			$linkedFieldsCount  = $this->db->num_rows ($linkedModulesQuery);

			for ($i = 0; $i < $linkedFieldsCount; $i++) {
				$related_module = $this->db->query_result ($linkedModulesQuery, $i, 'relmodule');
				$columnname     = $this->db->query_result ($linkedModulesQuery, $i, 'columnname');
				$other          = CRMEntity::getInstance ($related_module);
				vtlib_setup_modulevars ($related_module, $other);
				$query .= " LEFT JOIN $other->table_name ON $other->table_name.$other->table_index = $this->table_name.$columnname";
			}

			$query .= $this->getNonAdminAccessControlQuery ($thismodule, $current_user);
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
		// @codingStandardsIgnoreStart
		public function create_import_query ($module) {
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
		 *
		 * @param $module
		 * @param $user_id
		 *
		 * @return integer
		 */
		// @codingStandardsIgnoreStart
		public function undo_import ($module, $user_id) {
			// @codingStandardsIgnoreEnd
			global $adb;
			$count     = 0;
			$queryOne  = "SELECT bean_id FROM vtiger_users_last_import WHERE assigned_user_id=? AND bean_type='$module' AND deleted=0";
			$resultOne = $adb->pquery ($queryOne, array ($user_id)) || die('Error getting last import for undo: ' . mysqli_error ($adb));
			while ($rowOne = $adb->fetchByAssoc ($resultOne)) {
				$queryTwo  = 'UPDATE vtiger_crmentity SET deleted=1 WHERE crmid=?';
				$resultTwo = $adb->pquery ($queryTwo, array ($rowOne['bean_id'])) || die('Error undoing last import: ' . mysqli_error ($adb));
				echo $resultTwo;
				$count++;
			}
			return $count;
		}

		/**
		 * Transform the value while exporting
		 *
		 * @param $key
		 * @param $value
		 *
		 * @return mixed
		 */
		// @codingStandardsIgnoreStart
		public function transform_export_value ($key, $value) {
			// @codingStandardsIgnoreEnd
			return parent::transform_export_value ($key, $value);
		}

		/**
		 * Function which will set the assigned user id for import record.
		 */
		// @codingStandardsIgnoreStart
		public function set_import_assigned_user () {
			// @codingStandardsIgnoreEnd
			global $current_user, $adb;
			$record_user = $this->column_fields['assigned_user_id'];

			if ($record_user != $current_user->id) {
				$sqlresult = $adb->pquery ('SELECT id FROM vtiger_users WHERE id = ? UNION SELECT groupid AS id FROM vtiger_groups WHERE groupid = ?', array ($record_user, $record_user));
				if ($this->db->num_rows ($sqlresult) != 1) {
					$this->column_fields['assigned_user_id'] = $current_user->id;
				} else {
					$row = $adb->fetchByAssoc ($sqlresult, -1, false);
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
		 *
		 * @param $module
		 * @param $tableCols
		 * @param $field_values
		 * @param $ui_type_arr
		 * @param string $selectCols
		 *
		 * @return string
		 */
		public function getDuplicatesQuery ($module, $tableCols, $field_values, $ui_type_arr, $selectCols = '') {
			$select_clause = 'SELECT ' . $this->table_name . '.' . $this->table_index . ' AS recordid, vtiger_users_last_import.deleted,' . $tableCols;
			$query         = '';
			// Select Custom Field Table Columns if present
			if (isset($this->customFieldTable)) {
				$query .= ', ' . $this->customFieldTable[0] . '.* ';
			}

			$fromClause = " FROM $this->table_name";

			$fromClause .= "	INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $this->table_name.$this->table_index";

			// Consider custom table join as well.
			if (isset($this->customFieldTable)) {
				$fromClause .= ' INNER JOIN ' . $this->customFieldTable[0] . ' ON ' . $this->customFieldTable[0] . '.' . $this->customFieldTable[1] . " = $this->table_name.$this->table_index";
			}
			$fromClause .= ' LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';

			$whereClause = '   WHERE vtiger_crmentity.deleted = 0';
			$whereClause .= $this->getListViewSecurityParameter ($module);

			if (isset($selectCols) && trim ($selectCols) != '') {
				$sub_query = "SELECT $selectCols FROM  $this->table_name AS t " . ' INNER JOIN vtiger_crmentity AS crm ON crm.crmid = t.' . $this->table_index;
				// Consider custom table join as well.
				if (isset($this->customFieldTable)) {
					$sub_query .= ' LEFT JOIN ' . $this->customFieldTable[0] . ' tcf ON tcf.' . $this->customFieldTable[1] . " = t.$this->table_index";
				}
				$sub_query .= " WHERE crm.deleted=0 GROUP BY $selectCols HAVING COUNT(*)>1";
			} else {
				$sub_query = "SELECT $tableCols $fromClause $whereClause GROUP BY $tableCols HAVING COUNT(*)>1";
			}

			$query .= $select_clause . $fromClause .
			          ' LEFT JOIN vtiger_users_last_import ON vtiger_users_last_import.bean_id=' . $this->table_name . '.' . $this->table_index .
			          ' INNER JOIN (' . $sub_query . ') AS temp ON ' . get_on_clause ($field_values, $ui_type_arr, $module) .
			          $whereClause .
			          " ORDER BY $tableCols," . $this->table_name . '.' . $this->table_index . ' ASC';
			return $query;
		}

	}

<?php
	require_once ('config.php');
	require_once ('include/logging.php');
	require_once ('data/Tracker.php');
	require_once ('data/CrmEntityUtils.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('include/utils/CalculatedSystemUtils.class.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/NumberHelper.class.php');
	require_once ('include/utils/ProcessCasesUtils.class.php');
	require_once ('include/utils/TableFieldUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/Zend/Json.php');
	require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
	require_once ('modules/platzi_issabel/lib/PlatziIssabel.class.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');
	require_once ('modules/Settings/lib/TableFieldHelper.class.php');

	class CRMEntity {
		/** @var integer */
		public $ownedby;

		/** @var array To keep track of action of field filtering and avoiding doing more than once */
		protected $__inactive_fields_filtered = false;

		/** @var array */
		public $column_fields = array ();

		/** @var PearDatabase */
		public $db;

		/** @var string */
		public $case_number;
		
		/** @var string */
		public $mode;

		/** @var array */
		public $required_fields;

		/** @var string */
		public $table_index;

		/** @var string */
		public $table_name;

		/** @var array */
		public $tab_name_index;

		/** @var array */
		public $tab_name;

	/** @var array Cache de CHARACTER_MAXIMUM_LENGTH por tabla y columna */
	private static $_columnLengths = array();

		/**
		 * Detect if we are in bulk save mode, where some features can be turned-off to improve performance.
		 *
		 * @return boolean
		 */
		/**
	 * Devuelve la longitud maxima de una columna, cacheando por tabla.
	 *
	 * @param string $tableName
	 * @param string $columnName
	 * @param PearDatabase $adb
	 * @return int
	 */
	private static function getColumnMaxLength ($tableName, $columnName, $adb) {
		if (!isset(self::$_columnLengths[$tableName])) {
			self::$_columnLengths[$tableName] = array();
			$result = $adb->pquery(
				'SELECT COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH
				 FROM INFORMATION_SCHEMA.COLUMNS
				 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
				array($tableName)
			);
			while ($row = $adb->fetchByAssoc($result, -1, false)) {
				self::$_columnLengths[$tableName][strtolower($row['column_name'])] = intval($row['character_maximum_length']);
			}
		}
		$columnName = strtolower($columnName);
		return isset(self::$_columnLengths[$tableName][$columnName]) ? self::$_columnLengths[$tableName][$columnName] : 0;
	}
	public static function isBulkSaveMode () {
			global $VTIGER_BULK_SAVE_MODE;
			if (isset($VTIGER_BULK_SAVE_MODE) && $VTIGER_BULK_SAVE_MODE) {
				return true;
			}
			return false;
		}

		/**
		 * @param string $module
		 *
		 * @return CRMEntity
		 */
		public static function getInstance ($module) {
			$modName = $module;
			if ($module == 'Calendar' || $module == 'Events') {
				$module  = 'Calendar';
				$modName = 'Activity';
			}
			// File access security check
			if (!class_exists ($modName)) {
				if (file_exists ($_SESSION['plat'] . "/modules/$module/$modName.php")) {
					checkFileAccessForInclusion ($_SESSION['plat'] . "/modules/$module/$modName.php");
					require_once ($_SESSION['plat'] . "/modules/$module/$modName.php");
				} else {
					checkFileAccessForInclusion ("modules/$module/$modName.php");
					require_once ("modules/$module/$modName.php");
				}
			}
			$focus = new $modName();
			return $focus;
		}

		/**
		 * Function which returns the value based on result type (array / ADODB ResultSet)
		 *
		 * @param $result
		 * @param $index
		 * @param $columnname
		 *
		 * @return mixed|string
		 * @throws Exception
		 */
		private function resolve_query_result_value ($result, $index, $columnname) {
			global $adb;
			if (is_array ($result)) {
				return $result[ $index ][ $columnname ];
			} else {
				return $adb->query_result ($result, $index, $columnname);
			}
		}

		/**
		 * @param $d1
		 * @param $d2
		 *
		 * @return int
		 */
		private function __timediff ($d1, $d2) {
			list($t1_1, $t1_2) = explode (' ', $d1);
			list($t1_y, $t1_m, $t1_d) = explode ('-', $t1_1);
			list($t1_h, $t1_i, $t1_s) = explode (':', $t1_2);

			$t1 = mktime ($t1_h, $t1_i, $t1_s, $t1_m, $t1_d, $t1_y);

			list($t2_1, $t2_2) = explode (' ', $d2);
			list($t2_y, $t2_m, $t2_d) = explode ('-', $t2_1);
			list($t2_h, $t2_i, $t2_s) = explode (':', $t2_2);

			$t2 = mktime ($t2_h, $t2_i, $t2_s, $t2_m, $t2_d, $t2_y);

			if ($t1 == $t2) {
				return 0;
			}
			return $t2 - $t1;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $record
		 *
		 * @return mixed|null
		 */
		private function getEntityCaseNumber ($adb, $record) {
			$result = $adb->pquery ('SELECT case_number FROM vtiger_crmentity WHERE crmid=?',
				array ($record)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result);
				$caseNumber =  $row ['case_number'];
			}
			
			return (isset ($caseNumber)) ? $caseNumber : null;
		}
		
		/**
		 *
		 * @param string $tableName
		 * @param integer $tabId
		 * @param Users $user
		 * @param string $parentRole
		 * @param array $userGroups
		 *
		 * @return boolean
		 */
		protected function setupTemporaryTable ($tableName, $tabId, $user, $parentRole, $userGroups) {
			$module = null;
			if (!empty ($tabId)) {
				$module = getTabModuleName ($tabId);
			}
			$db     = PearDatabase::getInstance ();
			$result = $db->query ("CREATE TEMPORARY TABLE IF NOT EXISTS {$tableName} (id INT(11) PRIMARY KEY) IGNORE {$this->getNonAdminAccessQuery ($module, $user, $parentRole, $userGroups)}");
			if (is_object ($result)) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * @param string $moduleName
		 */
		public function save_module ($moduleName) {
			if (!empty ($moduleName)) {
				return;
			}
			return;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $module
		 * @param string $fileid
		 *
		 * @throws Exception
		 */
		public function saveentity ($adb, $module, $fileid = '') {
			
			$columnFields        = $this->column_fields;
			$anyValue            = false;
			$mandatoryEmpty      = null;
			$mandatoryValueEmpty = null;
			foreach ($columnFields as $indexCF => $value) {
				if (empty ($value)) {
					$anyValue            = true;
					$mandatoryEmpty      = $indexCF;
					$mandatoryValueEmpty = $value;
					break;
				}
			}
			if (!$anyValue) {
				die("<center>" . getTranslatedString ('LBL_MANDATORY_FIELD_MISSING') . ": " . $module . " => " . $mandatoryEmpty . " = " . $mandatoryValueEmpty . "</center>");
			}

			$this->db->println ("TRANS saveentity starts $module");
			$this->db->startTransaction ();

			foreach ($this->tab_name as $table_name) {
				if ($table_name == "vtiger_crmentity") {
					$this->insertIntoCrmEntity ($module, $fileid);
				} else {
					$this->insertIntoEntityTable ($table_name, $module);
				}
			}

			// EGC movido antes del save_module custom de la clase
			if (!isset($_REQUEST['ajxaction'])) { //Para acciones Ajax no se realiza este salvado
				$gridStatus = $this->saveFieldGrid ($adb, $module, $this->id);
				$this->saveRelatedListsAutomatic ($module, $this->id);
				// Excluir proyectos porque necesita calcular después de actualizar costo_general_del_proyect
				if ($module !== 'proyectos') {
					$this->saveCalculationsFields($module, $this->id, $gridStatus);
				}
				$this->saveTableFields ($adb, $module, $this->id);
				ProcessCasesUtils::saveProcessCase ($adb, $module, $this);
			}
			//Calling the Module specific save code
			$this->save_module ($module);

			$this->db->completeTransaction ();
			$this->db->println ("TRANS saveentity ends");
			
			// Recalcular campos calculados para proyectos después de completar la transacción
			// Esto asegura que los campos calculados usen los valores finales confirmados en BD
			if ($module === 'proyectos' && !isset($_REQUEST['ajxaction'])) {
				require_once('include/utils/CalculatedSystemUtils.class.php');
					global $current_user;
					list($prefix, $crm, $suffix) = explode('_', $adb->dbName);
					CalculatedSystemUtils::updateCalculatedField($adb, $suffix, $current_user, 'proyectos', $this->id, false);
			}

			// vtlib customization: Hook provide to enable generic module relation.
			if ($_REQUEST['createmode'] == 'link') {
				$for_module  = vtlib_purify ($_REQUEST['return_module']);
				$for_crmid   = vtlib_purify ($_REQUEST['return_id']);
				$with_module = $module;
				$with_crmid  = $this->id;

				$on_focus = CRMEntity::getInstance ($for_module);

				if ($for_module && $for_crmid && $with_module && $with_crmid) {
					relateEntities ($on_focus, $for_module, $for_crmid, $with_module, $with_crmid);
				}
			}

			$moduleseq = obtenerValorVariable ('module_sequence', $module);
			if (!empty($moduleseq) && $_REQUEST['mode'] != 'edit') {
				$this->createSequence ($this->id, $moduleseq);
			}
			// END
		}

		/**
		 * @param $id
		 * @param $module
		 * @param $filedata
		 * @param $filename
		 * @param $filesize
		 * @param $filetype
		 * @param $user_id
		 */
		public function insertIntoAttachment1 ($id, $module, $filedata, $filename, $filesize, $filetype, $user_id) {
			$date_var = date ("Y-m-d H:i:s");
			global $current_user;
			global $adb;
			global $log;

			$ownerid    = $user_id;
			$current_id = $adb->getUniqueID ("vtiger_crmentity");

			if ($module == 'Emails') {
				$log->info ("module is " . $module);
				$idname    = 'emailid';
				$tablename = 'emails';
			} else {
				$idname    = 'notesid';
				$tablename = 'notes';
			}

			$sql    = "update $tablename set filename=? where $idname=?";
			$params = array ($filename, $id);
			$adb->pquery ($sql, $params);

			$sql1    = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES(?, ?, ?, ?, ?, ?, ?)";
			$params1 = array ($current_id, $current_user->id, $ownerid, $module . " Attachment", '', $adb->formatDate ($date_var, true), $adb->formatDate ($date_var, true));
			$adb->pquery ($sql1, $params1);

			$sql2    = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type) VALUES(?, ?, ?, ?)";
			$params2 = array ($current_id, $filename, '', $filetype);
			$result  = $adb->pquery ($sql2, $params2);

			//TODO -- instead of put contents in db now we should store the file in harddisk
			$sql3    = 'INSERT INTO vtiger_seattachmentsrel VALUES(?, ?)';
			$params3 = array ($id, $current_id);
			$adb->pquery ($sql3, $params3);
		}

		/**
		 *      This function is used to upload the attachment in the server and save that attachment information in db.
		 *
		 * @param int $id - entity id to which the file to be uploaded
		 * @param string $module - the current module name
		 * @param array $file_details - array which contains the file information(name, type, size, tmp_name and error)
		 *
		 * @return boolean
		 */
		public function uploadAndSaveFile ($id, $module, $file_details) {
			global $log;
			$log->debug ("Entering into uploadAndSaveFile($id,$module,$file_details) method.");

			global $adb, $current_user;
			global $upload_badext;

			$date_var = date ("Y-m-d H:i:s");

			//to get the owner id
			$ownerid = $this->column_fields ['assigned_user_id'];
			if (!isset($ownerid) || $ownerid == '') {
				$ownerid = $current_user->id;
			}

			if (isset($file_details['original_name']) && $file_details['original_name'] != null) {
				$file_name = $file_details['original_name'];
			} else {
				$file_name = $file_details['name'];
			}
			$binFile = sanitizeUploadFileName ($file_name, $upload_badext);

			$current_id = $adb->getUniqueID ("vtiger_crmentity");

			$filename     = ltrim (basename (" " . $binFile)); //allowed filename like UTF-8 characters
			$filetype     = $file_details['type'];
			$filetmp_name = $file_details['tmp_name'];

			//get the file path inwhich folder we want to upload the file
			$upload_file_path = decideFilePath ();

			//upload the file in server
			$upload_status = move_uploaded_file ($filetmp_name, $upload_file_path . $current_id . "_" . $binFile);
			if (!$upload_status) {
				if (file_exists ($filetmp_name)) {
					if (copy ($filetmp_name, $upload_file_path . $current_id . "_" . $binFile)) {
						$upload_status = 'true';
					}
				}
			}
			$save_file = 'true';
			//only images are allowed for these modules
			if ($save_file == 'true' && $upload_status == 'true') {
				//This is only to update the attached filename in the vtiger_notes vtiger_table for the Notes module
				$sql1    = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) VALUES(?, ?, ?, ?, ?, ?, ?)";
				$params1 = array ($current_id, $current_user->id, $ownerid, $module . " Attachment", $this->column_fields ['description'], $adb->formatDate ($date_var, true), $adb->formatDate ($date_var, true));
				$adb->pquery ($sql1, $params1);

				$sql2    = "INSERT INTO vtiger_attachments(attachmentsid, name, description, type, path) VALUES(?, ?, ?, ?, ?)";
				$params2 = array ($current_id, $filename, $this->column_fields ['description'], $filetype, $upload_file_path);
				$adb->pquery ($sql2, $params2);

				if ($_REQUEST['mode'] == 'edit') {
					if ($id != '' && $_REQUEST['fileid'] != '') {
						$delquery  = 'DELETE FROM vtiger_seattachmentsrel WHERE crmid = ? AND attachmentsid = ?';
						$delparams = array ($id, $_REQUEST['fileid']);
						$adb->pquery ($delquery, $delparams);
					}
				}
				if ($module == 'Documents') {
					$query   = "DELETE FROM vtiger_seattachmentsrel WHERE crmid = ?";
					$qparams = array ($id);
					$adb->pquery ($query, $qparams);
				}
				$sql3 = 'INSERT INTO vtiger_seattachmentsrel VALUES(?,?)';
				$adb->pquery ($sql3, array ($id, $current_id));

				return $current_id;
			} else {
				$log->debug ("Skip the save attachment process.");
				return false;
			}
		}

		/** Function to insert values in the vtiger_crmentity for the specified module
		 *
		 * @param string $module -- module:: Type varchar
		 * @param string $fileid
		 *
		 * @throws Exception
		 */
		public function insertIntoCrmEntity ($module, $fileid = '') {
			global $adb;
			global $current_user;
			global $log;

			if ($fileid != '') {
				$this->id   = $fileid;
				$this->mode = 'edit';
			}

			$date_var = date ("Y-m-d H:i:s");

			$ownerid = $this->column_fields ['assigned_user_id'];

			$sql           = "SELECT ownedby FROM vtiger_tab WHERE name=?";
			$res           = $adb->pquery ($sql, array ($module));
			$this->ownedby = $adb->query_result ($res, 0, 'ownedby');

			if ($this->ownedby == 1) {
				$log->info ("module is =" . $module);
				$ownerid = $current_user->id;
			}
			// Asha - Change ownerid FROM '' to null since its an integer field.
			// It is empty for modules like Invoice/Quotes/SO/PO which do not have Assigned to field
			if ($ownerid === '' || !isset($ownerid)) {
				$ownerid = 0;
			}

			if ($module == 'Events') {
				$module = 'Calendar';
			}
			$insertion_mode = null;
			if ($this->mode == 'edit') {
				$description_val = from_html ($this->column_fields ['description'], ($insertion_mode == 'edit') ? true : false);
				$local_user      = clone $current_user;
				require ('user_privileges/user_privileges.php');

				$tabid = getTabid ($module);
				/**
				 * @var boolean $is_admin
				 * @var array $profileGlobalPermission
				 */
				if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
					$sql    = "UPDATE vtiger_crmentity SET smownerid=?,modifiedby=?,description=?, modifiedtime=? WHERE crmid=?";
					$params = array ($ownerid, $current_user->id, $description_val, $adb->formatDate ($date_var, true), $this->id);
				} else {
					$profileList = getCurrentUserProfileList ();
					$perm_qry    = "SELECT columnname FROM vtiger_field INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid = vtiger_field.fieldid INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid = vtiger_field.fieldid WHERE vtiger_field.tabid = ? AND vtiger_profile2field.visible = 0 AND vtiger_profile2field.readonly = 0 AND vtiger_profile2field.profileid IN (" . generateQuestionMarks ($profileList) . ") AND vtiger_def_org_field.visible = 0 AND vtiger_field.tablename='vtiger_crmentity' AND vtiger_field.displaytype IN (1,3) AND vtiger_field.presence IN (0,2);";
					$perm_result = $adb->pquery ($perm_qry, array ($tabid, $profileList));
					$perm_rows   = $adb->num_rows ($perm_result);
					for ($i = 0; $i < $perm_rows; $i++) {
						$columname[] = $adb->query_result ($perm_result, $i, "columnname");
					}
					if (is_array ($columname) && in_array ("description", $columname)) {
						$sql    = "UPDATE vtiger_crmentity SET smownerid=?,modifiedby=?,description=?, modifiedtime=? WHERE crmid=?";
						$params = array ($ownerid, $current_user->id, $description_val, $adb->formatDate ($date_var, true), $this->id);
					} else {
						$sql    = "UPDATE vtiger_crmentity SET smownerid=?,modifiedby=?, modifiedtime=? WHERE crmid=?";
						$params = array ($ownerid, $current_user->id, $adb->formatDate ($date_var, true), $this->id);
					}
				}
				$adb->pquery ($sql, $params);
				$sql1    = "DELETE FROM vtiger_ownernotify WHERE crmid=?";
				$params1 = array ($this->id);
				$adb->pquery ($sql1, $params1);
				if ($ownerid != $current_user->id) {
					$sql1    = "INSERT INTO vtiger_ownernotify (crmid, smownerid, flag) VALUES(?,?,?)";
					$params1 = array ($this->id, $ownerid, null);
					$adb->pquery ($sql1, $params1);
				}
			} else {
				//if this is the create mode and the group allocation is chosen, then do the following
				$current_id            = $adb->getUniqueID ("vtiger_crmentity");
				$_REQUEST['currentid'] = $current_id;
				if ($current_user->id == '') {
					$current_user->id = 0;
				}

				// Customization
				$created_date_var  = $adb->formatDate ($date_var, true);
				$modified_date_var = $adb->formatDate ($date_var, true);

				// Preserve the timestamp
				if (self::isBulkSaveMode ()) {
					if (!empty($this->column_fields ['createdtime'])) {
						$created_date_var = $adb->formatDate ($this->column_fields ['createdtime'], true);
					}
					//NOTE : modifiedtime ignored to support vtws_sync API track changes.
				}
				// END

				$description_val = from_html ($this->column_fields ['description'], ($insertion_mode == 'edit') ? true : false);
				$sql             = "INSERT INTO vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,modifiedby,createdtime,modifiedtime) VALUES(?,?,?,?,?,?,?,?)";
				$params          = array ($current_id, $current_user->id, $ownerid, $module, $description_val, $current_user->id, $created_date_var, $modified_date_var);
				$adb->pquery ($sql, $params, $adb->dieOnError);
				$this->id                             = $current_id;
				$this->column_fields ['createdtime']  = $created_date_var;
				$this->column_fields ['modifiedtime'] = $modified_date_var;
			}
		}

		/** Function to insert values in the specifed table for the specified module
		 *
		 * @param string $table_name -- table name:: Type varchar
		 * @param string $module -- module:: Type varchar
		 *
		 * @throws Exception
		 */
		public function insertIntoEntityTable ($table_name, $module) {
			global $adb, $app_strings, $current_user, $upload_badext;
			$insertionMode   = $this->mode;
			$numberingHelper = NumberHelper::getInstance ($adb, $current_user);
			//Checkin whether an entry is already is present in the vtiger_table to update
			if ($insertionMode == 'edit') {
				$tablekey = $this->tab_name_index[ $table_name ];
				// Make selection ON the primary key of the module table to check.
				$result   = $adb->pquery ("SELECT {$tablekey} FROM {$table_name} WHERE {$tablekey}=?", array ($this->id));
				$num_rows = $adb->num_rows ($result);
				if ($num_rows <= 0) {
					$insertionMode = '';
				}
			}

			$tabid = getTabid ($module);
			if ($insertionMode == 'edit') {
				$update        = array ();
				$update_params = array ();

				$local_user = clone $current_user;
				require ('user_privileges/user_privileges.php');
				/**
				 * @var boolean $is_admin
				 * @var array $profileGlobalPermission
				 */
				if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
					$sql    = "SELECT
									*
								FROM
									vtiger_field
								WHERE
									tabid IN (" . generateQuestionMarks ($tabid) . ") AND
									tablename=? AND
									displaytype IN (1, 3) AND
									presence IN (0, 2)
								GROUP BY
									columnname";
					$params = array ($tabid, $table_name);
				} else {
					$profileList = getCurrentUserProfileList ();
					if (count ($profileList) > 0) {
						$sql    = "SELECT
									*
			  					FROM
			  						vtiger_field
			  						INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid
			  						INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid
			  					WHERE
			  						vtiger_field.tabid=? AND
			  						vtiger_profile2field.visible=0 AND
			  						vtiger_profile2field.readonly=0 AND
			  						vtiger_profile2field.profileid IN (" . generateQuestionMarks ($profileList) . ") AND
			  						vtiger_def_org_field.visible=0 AND
			  						vtiger_field.tablename=? AND
			  						vtiger_field.displaytype IN (1, 3) AND
			  						vtiger_field.presence IN (0, 2)
			  					GROUP BY
			  						columnname";
						$params = array ($tabid, $profileList, $table_name);
					} else {
						$sql    = "SELECT
									*
			  					FROM
			  						vtiger_field
			  						INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid
			  						INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid
			  					WHERE
			  						vtiger_field.tabid=? AND
			  						vtiger_profile2field.visible=0 AND
			  						vtiger_profile2field.readonly=0 AND
			  						vtiger_def_org_field.visible=0 AND
			  						vtiger_field.tablename=? AND
			  						vtiger_field.displaytype IN (1, 3) AND
			  						vtiger_field.presence IN (0, 2)
			  					GROUP BY
			  						columnname";
						$params = array ($tabid, $table_name);
					}
				}
			} else {
				$table_index_column = $this->tab_name_index[ $table_name ];
				if ($table_index_column == 'id' && $table_name == 'vtiger_users') {
					$currentuser_id = $adb->getUniqueID ("vtiger_users");
					$this->id       = $currentuser_id;
				}
				$column = array ($table_index_column);
				$value  = array ($this->id);
				$sql    = "SELECT * FROM vtiger_field WHERE tabid=? AND tablename=? AND displaytype IN (1,3,4) AND vtiger_field.presence IN (0,2) AND uitype NOT IN (?)";
				$params = array ($tabid, $table_name, 2202);
			}

			// Attempt to re-use the quer-result to avoid reading for every save operation
			// TODO Need careful analysis on impact ... MEMORY requirement might be more
			static $_privatecache = array ();

			$cachekey = "{$insertionMode}-" . implode (',', $params);

			if (!isset($_privatecache[ $cachekey ])) {
				$result   = $adb->pquery ($sql, $params);
				$noofrows = $adb->num_rows ($result);
				if (CRMEntity::isBulkSaveMode ()) {
					$cacheresult = array ();
					for ($i = 0; $i < $noofrows; ++$i) {
						$cacheresult[] = $adb->fetch_array ($result);
					}
					$_privatecache[ $cachekey ] = $cacheresult;
				}
			} else { // Useful when doing bulk save
				$result   = $_privatecache[ $cachekey ];
				$noofrows = count ($result);
			}
			for ($i = 0; $i < $noofrows; $i++) {
				$fieldId          = $this->resolve_query_result_value ($result, $i, "fieldid");
				$fieldname        = $this->resolve_query_result_value ($result, $i, "fieldname");
				$columname        = $this->resolve_query_result_value ($result, $i, "columnname");
				$uitype           = $this->resolve_query_result_value ($result, $i, "uitype");
				$typeofdata       = $this->resolve_query_result_value ($result, $i, "typeofdata");
				$typeofdata_array = explode ("~", $typeofdata);
				$datatype         = $typeofdata_array[0];
				$ajaxSave         = false;
				if (
					($_REQUEST ['file'] == 'DetailViewAjax') &&
					($_REQUEST ['ajxaction'] == 'DETAILVIEW') &&
					(isset ($_REQUEST ["fldName"])) &&
					($_REQUEST ["fldName"] != $fieldname) ||
					($_REQUEST ['action'] == 'MassEditSave') &&
					(!isset ($_REQUEST [ $fieldname . "_mass_edit_check" ]))
				) {
					$ajaxSave = true;
				}

				if (($uitype == 4) && ($insertionMode != 'edit')) {
					$fldvalue = '';
					// Bulk Save Mode: Avoid generation of module sequence number, take care later.
					if (!CRMEntity::isBulkSaveMode ()) {
						$fldvalue = $this->setModuleSeqNumber ("increment", $module);
					}
					$this->column_fields [ $fieldname ] = $fldvalue;
				} else if ($uitype == 4096) {
					AttachmentsUtils::saveAttachments ($adb, $this->id, $module, $fieldId, $current_user->id, $_REQUEST [ $fieldname ], $upload_badext);
					continue;
				}
				if (isset ($this->column_fields [ $fieldname ])) {
					if ($uitype == 56) {
						if ($this->column_fields [ $fieldname ] == 'on' || $this->column_fields [ $fieldname ] == 1) {
							$fldvalue = '1';
						} else {
							$fldvalue = '0';
						}
					} else if ($uitype == 15) {
						$submittedValue = $this->column_fields[$fieldname];
						
						// Verificar si el valor es "Not Accessible" (comportamiento antiguo)
						if ($submittedValue == $app_strings['LBL_NOT_ACCESSIBLE']) {
							//If the value in the request is Not Accessible for a picklist, the existing value will be replaced instead of Not Accessible value.
							$res = $adb->pquery ("SELECT {$columname} FROM {$table_name} WHERE {$this->tab_name_index[$table_name]}=?", array ($this->id));
							if ($adb->num_rows ($res) > 0) {
								$row = $adb->fetchByAssoc ($result, -1, false);
								$fldvalue = $row[$columname];
							} else {
								$fldvalue = null;
							}
						} else {
							// Verificar si el valor contiene HTML (span rojo) y extraer el valor original
							if (strpos($submittedValue, '<span') !== false && strpos($submittedValue, '</span>') !== false) {
								// Extraer el valor original del HTML usando strip_tags
								$fldvalue = strip_tags($submittedValue);
								// Limpiar entidades HTML si existen
								$fldvalue = html_entity_decode($fldvalue, ENT_QUOTES, $default_charset);
							} else {
								// Valor normal sin HTML
								$fldvalue = $submittedValue;
							}
						}
					} else if ($uitype == 16) {
						if (is_array ($this->column_fields [ $fieldname ])) {
							$field_list = implode (' |##| ', $this->column_fields [ $fieldname ]);
						} else {
							$field_list = $this->column_fields [ $fieldname ];
						}
						$fldvalue = $field_list;
					} else if ($uitype == 33 || $uitype == 407) {
						if (is_array ($this->column_fields [ $fieldname ])) {
							$field_list = implode (' |##| ', $this->column_fields [ $fieldname ]);
						} else {
							$field_list = $this->column_fields [ $fieldname ];
						}
						$fldvalue = $field_list;
					} else if ($uitype == 5 || $uitype == 6 || $uitype == 23) {
						//Added to avoid function call getDBInsertDateValue in ajax save
						if (isset($current_user->date_format)) {
							$fldvalue = getValidDBInsertDateValue ($this->column_fields [ $fieldname ]);
						} else {
							$fldvalue = $this->column_fields [ $fieldname ];
						}
					} else if ($uitype == 7 || $uitype == 9) {
						$fldvalue = $this->column_fields [ $fieldname ];

					// Fix: Solo aplicar formato si el valor viene del formulario con formato regional
					// No aplicar si ya está en formato estándar de BD (punto decimal)
					if (is_string($fldvalue)) {
						// Si contiene coma, es formato europeo del formulario
						// Si no contiene punto ni coma, es número entero del formulario
						// Si contiene punto sin coma, es formato estándar de BD
						if (strpos($fldvalue, ',') !== false || (strpos($fldvalue, '.') === false && $fldvalue !== '')) {
							// Formato regional del formulario
							$fldvalue = $numberingHelper->setSaveNumberFormat ($fldvalue);
						} else {
							// Formato estándar de BD
							$fldvalue = floatval($fldvalue);
						}
					}
					// Si ya es float/int, no hacer nada
					
					} else if ($uitype == 26) {
						if (empty($this->column_fields [ $fieldname ])) {
							$fldvalue = 1; //the documents will stored in default folder
						} else {
							$fldvalue = $this->column_fields [ $fieldname ];
						}
					} else if ($uitype == 28) {
						if ($this->column_fields [ $fieldname ] == null) {
							$fileQuery = $adb->pquery ("SELECT filename FROM vtiger_notes WHERE notesid = ?", array ($this->id));
							$fldvalue  = null;
							if (isset($fileQuery)) {
								$rowCount = $adb->num_rows ($fileQuery);
								if ($rowCount > 0) {
									$fldvalue = $adb->query_result ($fileQuery, 0, 'filename');
								}
							}
						} else {
							$fldvalue = $this->column_fields [ $fieldname ];
						}
					} else if ($uitype == 8) {
						$this->column_fields [ $fieldname ] = rtrim ($this->column_fields [ $fieldname ], ',');
						$ids                                = explode (',', $this->column_fields [ $fieldname ]);
						$json                               = new Zend_Json();
						$fldvalue                           = $json->encode ($ids);
					} else if ($uitype == 12) {
						// Bulk Sae Mode: Consider the FROM email address as specified, if not lookup
						$fldvalue = $this->column_fields [ $fieldname ];
						if (empty($fldvalue)) {
							$query = "SELECT email1 FROM vtiger_users WHERE id = ?";
							$res   = $adb->pquery ($query, array ($current_user->id));
							$rows  = $adb->num_rows ($res);
							if ($rows > 0) {
								$fldvalue = $adb->query_result ($res, 0, 'email1');
							}
						}
						// END
					} else if ($uitype == 72 && !$ajaxSave) {
						// Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
						$fldvalue = CurrencyField::convertToDBFormat ($this->column_fields [ $fieldname ], null, true);
					} else if ($uitype == 71 && !$ajaxSave) {
						$fldvalue = $numberingHelper->setSaveNumberFormat ($this->column_fields[ $fieldname ]);
					} else if ($uitype == 257 || $uitype == 258) {
						if (isset($_FILES[ $fieldname ]) && $_FILES[ $fieldname ]['name'] != '') {
							$this->deleteFile ($_REQUEST[ $fieldname . '_id' ]);
							$fldvalue = $this->UploadAndSaveFile ($this->id, $module, $_FILES[ $fieldname ]);
						} else {
							$fldvalue = $_REQUEST[ $fieldname . '_id' ];
						}
					} else if ($uitype == 108) {
						$pp       = $_REQUEST['periodo_prueba'];
						$fldvalue = "{\"min\":\"" . $pp['min'] . "\",\"max\":\"" . $pp['max'] . "\",\"ini\":\"" . $pp['ini'] . "\",\"ord\":\"" . $pp['ord'] . "\"}";
					} else {
						$fldvalue = $this->column_fields [ $fieldname ];
					}
					if ($uitype != 33 && $uitype != 8) {
						$fldvalue = from_html ($fldvalue, ($insertion_mode == 'edit') ? true : false);
					}
				} else {
					$fldvalue = '';
				}
				if ($fldvalue === '' || $fldvalue === null) {
					$fldvalue = $this->get_column_value ($columname, $fldvalue, $fieldname, $uitype, $datatype);
				}

					// Fix: convertir formato decimal europeo (coma) a formato BD (punto) para evitar [1366]
				if (is_string($fldvalue) && in_array($datatype, array('N', 'NN')) && strpos($fldvalue, ',') !== false) {
					$fldvalue = str_replace('.', '', $fldvalue);
					$fldvalue = str_replace(',', '.', $fldvalue);
				}

				// Truncar strings si exceden la longitud maxima de la columna (evita Data too long)
				if (is_string($fldvalue) && $table_name != 'vtiger_crmentity') {
					$maxLength = self::getColumnMaxLength($table_name, $columname, $adb);
					if ($maxLength > 0 && strlen($fldvalue) > $maxLength) {
						$fldvalue = substr($fldvalue, 0, $maxLength);
					}
				}

				if ($insertionMode == 'edit') {
					// Excluir campos calculados por triggers y campos NOT NULL sin valor en Calendar/Save
					$excludedCalcColumns = array('executed_cost', 'combined_condition', 'progress_ratio', 'progress_condition', 'estimated_progress',
						'feedbacks', 'reports', 'sendnotification', 'notime');
					$shouldExcludeCalc = ($table_name == 'vtiger_activity') && in_array($columname, $excludedCalcColumns);
					
					if ($table_name != 'vtiger_ticketcomments' && $uitype != 4 && $uitype != 2202 && (!empty ($columname)) && !$shouldExcludeCalc) {
						array_push ($update, $columname . "=?");
						array_push ($update_params, $fldvalue);
					}
				} else {
					// Excluir campos del INSERT en vtiger_activity para usar el valor por defecto de la tabla
					// Caso particular para creación de tareas desde el módulo orden_de_trabajo
					// Campos excluidos: feedbacks, reports, sendnotification, notime, categoryid
					// NOTA: related_id removido de exclusiones para permitir su guardado desde Save.php
					// Campos base excluidos + campos calculados por triggers (para evitar errores de formato)
					$excludedColumns = array('feedbacks', 'reports', 'sendnotification', 'notime', 'categoryid',
						'executed_cost', 'combined_condition', 'progress_ratio', 'progress_condition', 'estimated_progress');
					$shouldExclude = ($table_name == 'vtiger_activity') && in_array($columname, $excludedColumns);
					
					if ($uitype != 2202 && !$shouldExclude) {
						array_push ($column, $columname);
						array_push ($value, $fldvalue);
					}
				}
			}

			if ($insertionMode == 'edit') {
				//Check done by Don. If update is empty the the query fails
				if (count ($update) > 0) {
					$sql1 = "update $table_name set " . implode (",", $update) . " where " . $this->tab_name_index[ $table_name ] . "=?";
					array_push ($update_params, $this->id);
					$adb->pquery ($sql1, $update_params, true);
				}
			} else {
				$sql1 = "insert into $table_name (" . implode (",", $column) . ") values(" . generateQuestionMarks ($value) . ")";
				$adb->pquery ($sql1, $value, $adb->dieOnError);
			}
		}

		/** Function to delete a record in the specifed table
		 *
		 * @param $table_name -- table name:: Type varchar
		 * The function will delete a record .The id is obtained from the class variable $this->id and the columnname got from $this->tab_name_index[$table_name]
		 */
		public function deleteRelation ($table_name) {
			global $adb;
			$check_query  = "select * FROM $table_name where " . $this->tab_name_index[ $table_name ] . "=?";
			$check_result = $adb->pquery ($check_query, array ($this->id));
			$num_rows     = $adb->num_rows ($check_result);

			if ($num_rows == 1) {
				$del_query = "DELETE FROM $table_name where " . $this->tab_name_index[ $table_name ] . "=?";
				$adb->pquery ($del_query, array ($this->id));
			}
		}

		/** Function to attachment filename of the given entity
		 *
		 * @param integer $notesid -- crmid:: Type Integer
		 * The function will get the attachmentsid for the given entityid from vtiger_seattachmentsrel table and get the attachmentsname from vtiger_attachments table
		 * returns the 'filename'
		 *
		 * @return mixed|string
		 */
		public function getOldFileName ($notesid) {
			global $log;
			$log->info ("in getOldFileName  " . $notesid);
			global $adb;
			$query1   = "SELECT * FROM vtiger_seattachmentsrel WHERE crmid=?";
			$result   = $adb->pquery ($query1, array ($notesid));
			$noofrows = $adb->num_rows ($result);
			if ($noofrows != 0) {
				$attachmentid = $adb->query_result ($result, 0, 'attachmentsid');
			} else {
				$attachmentid = '';
			}
			if ($attachmentid != '') {
				$query2   = "SELECT * FROM vtiger_attachments WHERE attachmentsid=?";
				$filename = $adb->query_result ($adb->pquery ($query2, array ($attachmentid)), 0, 'name');
			} else {
				$filename = null;
			}
			return $filename;
		}

		/**
		 * Function to retrive the information of the given recordid ,module
		 * This function retrives the information from the database and sets the value in the class columnfields array
		 *
		 * @param integer $record -- Id:: Type Integer
		 * @param string $module -- module:: Type varchar
		 * @param PearDatabase|null $targetAdb
		 */
		public function retrieve_entity_info ($record, $module, $targetAdb = null, $showDetail = false) {
			global $app_strings;
			if ($targetAdb == null) {
				global $adb;
			} else {
				$GLOBALS ['adb'] = $targetAdb;
				$adb             = $targetAdb;
			}
			$numberingHelper = NumberHelper::getInstance ($adb);
			$result          = array ();
			//Fix EV. 20140924
			if (empty($record) && !empty($module)) {
				return;
			}
			foreach ($this->tab_name_index as $table_name => $index) {
				$result[ $table_name ] = $adb->pquery ("SELECT * FROM {$table_name} WHERE {$index} =?", array ($record));
				if ($table_name == 'vtiger_crmentity') {
					$row             = $adb->fetchByAssoc ($result['vtiger_crmentity'], -1, false);
					$isRecordDeleted = $row ['deleted'];
					if ($isRecordDeleted !== 0 && $isRecordDeleted !== '0') {
						die("<br><br><center>" . $app_strings['LBL_RECORD_DELETE'] . " <a href='javascript:window.history.back()'>" . $app_strings['LBL_GO_BACK'] . ".</a></center>");
					}
				}
			}

			/* Prasad: Fix for ticket #4595 */
			$cachedRowData = array();
			if (isset ($this->table_name) ) {
				$mod_index_col = $this->tab_name_index[ $this->table_name ];
				$row           = $adb->fetchByAssoc ($result[ $this->table_name ], -1, false);
				$cachedRowData[ $this->table_name ] = $row;
				if ($row[ $mod_index_col ] == '') {
					die("<br><br><center>" . $app_strings['LBL_RECORD_NOT_FOUND'] .
						". <a href='javascript:window.history.back()'>" . $app_strings['LBL_GO_BACK'] . ".</a></center>");
				}
			}

			// Lookup in cache for information
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module);

			if ($cachedModuleFields === false) {
				$tabid = getTabid ($module);
				
				// Let us pick up all the fields first so that we can cache information
				$sql1 = "SELECT fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata, presence FROM vtiger_field WHERE tabid=?";

				// NOTE: Need to skip in-active fields which we will be done later.
				$result1  = $adb->pquery ($sql1, array ($tabid));
				$noofrows = $adb->num_rows ($result1);

				if ($noofrows) {
					while ($resultrow = $adb->fetchByAssoc ($result1, -1, false)) {
						// Update information to cache for re-use
						VTCacheUtils::updateFieldInfo (
							$tabid, $resultrow['fieldname'], $resultrow['fieldid'], $resultrow['fieldlabel'], $resultrow['columnname'], $resultrow['tablename'], $resultrow['uitype'], $resultrow['typeofdata'], $resultrow['presence']
						);
					}
				}

				// Get only active field information
				$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module);
			} else {
				$noofrows = 0;
			}

			if ($cachedModuleFields) {
				foreach ($cachedModuleFields as $fieldname => $fieldinfo) {
					$fieldcolname = $fieldinfo['columnname'];
					$tablename    = $fieldinfo['tablename'];
					$fieldname    = $fieldinfo['fieldname'];
					$uiType       = $fieldinfo['uitype'];
					// To avoid ADODB execption pick the entries that are in $tablename
					// (ex. when we don't have attachment for troubletickets, $result[vtiger_attachments]
					// will not be set so here we should not retrieve)
					if (isset($result[ $tablename ])) {
					// Fix: usar fila cacheada si fue consumida por fetchByAssoc (evita problema de cursor ADOdb)
					if (isset($cachedRowData[ $tablename ])) {
						$row = $cachedRowData[ $tablename ];
					} else {
						$row = $adb->raw_query_result_rowdata ($result[ $tablename ], 0);
					}
					$fld_value = isset($row[ $fieldcolname ]) ? $row[ $fieldcolname ] : '';
					} else {
						$adb->println ("There is no entry for this entity $record ($module) in the table $tablename");
						$fld_value = "";
						if ($fieldinfo['uitype'] == 2208) {
							$sqlTF = $adb->run_query_allrecords ("SELECT * FROM {$fieldinfo['tablename']} WHERE {$module}tfid = {$record}");
							foreach ($sqlTF as $keyRows => $tfRows) {
								foreach ($tfRows as $key => $value) {
									if (is_numeric ($key)) {
										continue;
									}
									if ($showDetail && is_numeric ($value)) {
										if (strpos ($value, '.') !== false) {
											$fieldValues[$key][] = $numberingHelper->setNumberFormat ($value);
										} else {
											$fieldValues[$key][] = trim ($value);
										}
									} else if ($showDetail && $key == 'summaryrow') {
										$arraySummary = json_decode ($value, true);
										foreach ($arraySummary as $index => &$value) {
											$value = $numberingHelper->setNumberFormat ($value);
										}
										$fieldValues[$key][] = json_encode ($arraySummary);
									} else {
										$fieldValues[$key][] = trim($value);
									}
								}
							}
							$fld_value = $fieldValues;
							unset ($fieldValues);
						}
					}
					if ($showDetail && in_array (intval($uiType), array (7, 9, 71))) {
						$this->column_fields [ $fieldname ] = $numberingHelper->setNumberFormat ($fld_value, $fieldname);
					} else {
						$this->column_fields [$fieldname] = $fld_value;
					}
				}
			}
			if ($module == 'Users') {
				for ($i = 0; $i < $noofrows; $i++) {
					$fieldcolname     = $adb->query_result ($result1, $i, "columnname");
					$tablename        = $adb->query_result ($result1, $i, "tablename");
					$fieldname        = $adb->query_result ($result1, $i, "fieldname");
					$fld_value        = $adb->query_result ($result[ $tablename ], 0, $fieldcolname);
					$this->$fieldname = $fld_value;
				}
			}
			/** obtener el numero de caso aquí */
			$this->case_number                     = $this->getEntityCaseNumber ($adb, $record);
			
			$this->column_fields ["record_id"]     = $record;
			$this->column_fields ["record_module"] = $module;
		}

		/**
		 * Function to saves the values in all the tables mentioned in the class variable $tab_name for the specified module
		 *
		 * @param string $moduleName
		 * @param string $fileid
		 *
         * @throws Exception
		 * @throws WebServiceException
		 */
		public function save ($moduleName, $fileid = '') {
			if ((!empty ($_SESSION ['platInstance'])) && (!isset ($this->id))) {
				$masterAdb          = AdbManager::getInstance ()->getMasterAdb ();
				$moduleSubscription = PlatformSubscriptionManager::getInstance ($masterAdb)->fetchModuleSubscription ($_SESSION ['platInstancia'], $moduleName);
				if (empty ($moduleSubscription)) {
					throw new Exception ('El módulo no se encuentra instalado. Te invitamos a instalar una aplicación que lo contenga');
				} else if ($moduleSubscription->getStatus () == ModuleSubscription::STATUS_INACTIVE) {
					throw new Exception ('El módulo se encuentra vencido. Te invitamos a renovar el servicio');
				} else if (($moduleSubscription->getMaxRecords () != -1) && ($moduleSubscription->getMaxRecords () <= $moduleSubscription->getTotalRecords ())) {
					throw new Exception ('Advertencia: has llegado al límite de registros que puedes crear con tu plan actual');
				}
			}
            global $adb, $current_user;
			
			// REGLA 1: case_number no se duplica en nuevos registros
			if (isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true' && $this->mode != 'edit') {
				$this->column_fields['case_number'] = '';
			require_once ('include/utils/DuplicateSanitizer.php');
			DuplicateSanitizer::sanitizeColumnFields($this, $moduleName, $adb);
			}
			
			if (!empty ($this->id) && isset ($this->id)) {
				$entity       = CRMEntity::getInstance ($moduleName);
				$entity->id   = $this->id;
				$entity->mode = 'edit';
				$entity->retrieve_entity_info ($this->id, $moduleName);
				$oldData = $entity->column_fields;
			} else {
				$oldData = array ();
			}

			$oldDieOnError = $adb->dieOnError;
			$adb->setDieOnError (false);
			BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('SAVE', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $this);
			if ($this->mode == 'edit') {
				BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('MODIFY', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $this);
			} else {
				BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('CREATE', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $this);
			}
			$adb->setDieOnError ($oldDieOnError);
			$adb->database->_errorMsg = null;

			//GS Save entity being called with the modulename as parameter
			$this->saveentity ($adb, $moduleName, $fileid);

			// Sincronizar con entidades compartidas
			$oldDieOnError = $adb->dieOnError;
			$adb->setDieOnError (false);
			DataSharingUtils::synchronize ($adb, $_SESSION ['platInstancia'], $moduleName, $this->id);
			$adb->setDieOnError ($oldDieOnError);

			// REGLA 2: Duplicar relaciones uitype=10 pero no relaciones de pestaña
			// Si estamos duplicando (isDuplicate=true) y no es edición, duplicar relaciones de campos uitype=10

			if (isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true' && $this->mode != 'edit' && isset($_REQUEST['record'])) {
				$originalRecordId = $_REQUEST['record'];
				
				$this->duplicateUitype10Relations($moduleName, $originalRecordId, $this->id);
				
				// REGLA 3: Ajustar fechas basadas en la fecha más antigua
				$this->adjustDatesInDuplication($moduleName, $originalRecordId, $this->id);
			}

			$hasErrors = !empty ($adb->database->_errorMsg);
			if (!$hasErrors) {
				$oldDieOnError = $adb->dieOnError;
				$adb->setDieOnError (false);
				BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('SAVE', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $this);
				if ($this->mode == 'edit') {
					BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('MODIFY', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $this);
				} else {
					BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('CREATE', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $this);
				}
				$adb->setDieOnError ($oldDieOnError);
			}

			if ($this->mode != 'edit') {    //Solo en ingreso se hace esta operaci�n
				global $current_user;
				$types = vtws_listtypes (null, $current_user);
				if (in_array ('Events', $types['types'])) {
					$this->saveRelatedEvent ($moduleName, $this->id);
				}
			}
			if (isset ($this->id)) {
				if ($this->mode != 'edit') {
					CrmEntityUtils::entry ($adb, $this->id, $moduleName, $this->column_fields, $current_user->id);
				} else {
					CrmEntityUtils::audit ($adb, $this->id, $moduleName, $oldData, $this->column_fields, $current_user->id);
				}
			}
		}

		/**
		 * @param string $module
		 * @param integer $record
		 * @param boolean $gridStaus
		 *
		 * @throws Exception
		 */
		public function saveCalculationsFields ($module, $record, $gridStaus) {
			global $adb, $current_user;
			$platform = $_SESSION ['plat'];
			CalculatedSystemUtils::updateCalculatedField ($adb, $platform, $current_user, $module, $record, $gridStaus);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $module
		 * @param integer $id
		 *
		 * @throws Exception
		 * @return boolean
		 */
		public function saveFieldGrid ($adb, $module, $id) {
			global $current_user, $upload_badext;
			$arguments = array (
				'module'       => $module,
				'recordId'     => $id,
				'requestData'  => $_REQUEST,
				'userId'       => $current_user->id,
				'upLoadBadext' => $upload_badext,
			);
			return GridFieldUtils::saveFieldGrid ($adb, $arguments);
		}
		
		/**
		 * @param PearDatabase$adb
		 * @param string $module
		 * @param integer $id
		 *
		 * @throws Exception
		 */
		public function saveTableFields ($adb, $module, $id) {
			$arguments = array (
				'module'      => $module,
				'recordId'    => $id,
				'requestData' => $_REQUEST,
			);
			TableFieldUtils::getInstance ($adb)->saveTableFields ($arguments);
		}
		
		/**
		 * @param string $module
		 * @param integer $id
		 */
		public function saveRelatedListsAutomatic ($module, $id) {
			$relatedList = getRelatedListaAutomatic ($module);

			for ($i = 0; $i < count ($relatedList); $i++) {
				if (getRecordRelatedList ($id, $relatedList[ $i ]['relmodule']) == 0) {
					saveRecordRelatedListAutomatic ($this, $module, $id, $relatedList[ $i ]['relmodule'], $this->column_fields [ $relatedList[ $i ]['field'] ], $relatedList[ $i ]['relfield']);
				}
			}
		}

		/**
		 * This function reads the configuration of the system to determine whether to insert a record of such event / task when making a new record of the specified module.
		 *
		 * @param string $module -- module:: Type varchar
		 * @param integer $id -- record id:: Type int
		 */
		public function saveRelatedEvent ($module, $id) {
			global $current_user;

			$recordEvent    = html_entity_decode (obtenerValorVariable ('record_event_cfg', $module));
			$lstRecordEvent = unserialize ($recordEvent);

			if ($recordEvent != '') {
				$startDirection = 1;
				if ($lstRecordEvent['startDirection'] == 'BEFORE') {
					$startDirection = -1;
				}

				$endDirection = 1;
				if ($lstRecordEvent['endDirection'] == 'BEFORE') {
					$endDirection = -1;
				}

				$dateStartField = $this->column_fields [ $lstRecordEvent['startDatefield'] ];
				list($dateStartFieldY, $dateStartFieldM, $dateStartFieldD) = explode ('-', $dateStartField);
				$mkTimeStart = mktime (0, 0, 0, $dateStartFieldM, $dateStartFieldD, $dateStartFieldY) + (60 * 60 * 24 * $lstRecordEvent['startDays'] * $startDirection);
				$dateStart   = date ('Y-m-d', $mkTimeStart);

				$dateEndField = $this->column_fields [ $lstRecordEvent['endDatefield'] ];
				list($dateEndFieldY, $dateEndFieldM, $dateEndFieldD) = explode ('-', $dateEndField);
				$mkTimeEnd = mktime (0, 0, 0, $dateEndFieldM, $dateEndFieldD, $dateEndFieldY) + (60 * 60 * 24 * $lstRecordEvent['endDays'] * $endDirection);
				$dateEnd   = date ('Y-m-d', $mkTimeEnd);

				if (strcasecmp ($lstRecordEvent['p_startTime'], 'pm') == 0 && $lstRecordEvent['h_startTime'] < 12) {
					$lstRecordEvent['h_startTime'] = (int) $lstRecordEvent['h_startTime'] + 12;
				}
				$timeStart = $lstRecordEvent['h_startTime'] . ':' . $lstRecordEvent['m_startTime'];

				if (strcasecmp ($lstRecordEvent['p_endTime'], 'pm') == 0 && $lstRecordEvent['h_endTime'] < 12) {
					$lstRecordEvent['h_endTime'] = (int) $lstRecordEvent['h_endTime'] + 12;
				}
				$timeEnd = $lstRecordEvent['h_endTime'] . ':' . $lstRecordEvent['m_endTime'];

				$dateStartValue = new DateTimeField($dateStart . ' ' . $timeStart);
				$dateEndValue   = new DateTimeField($dateEnd . ' ' . $timeEnd);

				$myfocus = CRMEntity::getInstance ('Calendar');

				$myfocus->column_fields ['subject']          = $this->processValue ($lstRecordEvent['eventName'], $module);
				$myfocus->column_fields ['assigned_user_id'] = $current_user->id;
				$myfocus->column_fields ['date_start']       = $dateStartValue->getDBInsertDateValue ();
				$myfocus->column_fields ['time_start']       = $dateStartValue->getDBInsertTimeValue ();

				$myfocus->column_fields ['due_date'] = $dateEndValue->getDBInsertDateValue ();
				$myfocus->column_fields ['time_end'] = $dateEndValue->getDBInsertTimeValue ();

				$myfocus->column_fields ['description']  = $this->processValue ($lstRecordEvent['description'], $module);
				$myfocus->column_fields ['parent_id']    = $id;
				$myfocus->column_fields ['eventstatus']  = $lstRecordEvent['event_status'];
				$myfocus->column_fields ['activitytype'] = $lstRecordEvent['event_type'];
				$myfocus->column_fields ['visibility']   = 'Public';

				$tab_type = 'Events';

				$myfocus->save ($tab_type);
			}
		}

		/**
		 * @param $value
		 * @param $module
		 *
		 * @return mixed
		 */
		public function processValue ($value, $module) {
			$prefix = $value;
			foreach ($this->column_fields as $campo => $valor) {
				if (getUItype ($module, $campo) == 5 || getUItype ($module, $campo) == 6 || getUItype ($module, $campo) == 23) {
					$prefix = str_replace ('$YEAR_' . $campo . '$', substr ($valor, 0, 4), $prefix);
					$prefix = str_replace ('$MONTH_' . $campo . '$', substr ($valor, 5, 2), $prefix);
					$prefix = str_replace ('$DAY_' . $campo . '$', substr ($valor, 8, 2), $prefix);
					$prefix = str_replace ('$' . $campo . '$', $valor, $prefix);
				} else {
					$prefix = str_replace ('$' . $campo . '$', $valor, $prefix);
				}
			}
			return $prefix;
		}

		/**
		 * @param $query
		 * @param $row_offset
		 * @param int $limit
		 * @param int $max_per_page
		 *
		 * @return array
		 */
		public function process_list_query ($query, $row_offset, $limit = -1, $max_per_page = -1) {
			global $list_max_entries_per_page;
			$this->log->debug ("process_list_query: " . $query);
			if (!empty($limit) && $limit != -1) {
				$result = &$this->db->limitQuery ($query, $row_offset + 0, $limit, true, "Error retrieving $this->object_name list: ");
			} else {
				$result = &$this->db->query ($query, true, "Error retrieving $this->object_name list: ");
			}

			$list = array ();
			if ($max_per_page == -1) {
				$max_per_page = $list_max_entries_per_page;
			}
			$rows_found = $this->db->getRowCount ($result);

			$this->log->debug ("Found $rows_found " . $this->object_name . "s");

			$previous_offset = $row_offset - $max_per_page;
			$next_offset     = $row_offset + $max_per_page;

			if ($rows_found != 0) {
				// We have some data.
				for ($index = $row_offset, $row = $this->db->fetchByAssoc ($result, $index); $row && ($index < $row_offset + $max_per_page || $max_per_page == -99); $index++, $row = $this->db->fetchByAssoc ($result, $index)) {
					foreach ($this->list_fields as $entry) {
						foreach ($entry as $key => $field) { // this will be cycled only once
							if (isset($row[ $field ])) {
								$this->column_fields [ $this->list_fields_names[ $key ] ] = $row[ $field ];

								$this->log->debug ("$this->object_name({$row['id']}): " . $field . " = " . $this->$field);
							} else {
								$this->column_fields [ $this->list_fields_names[ $key ] ] = "";
							}
						}
					}
					$list[] = clone($this); //added by Richie to support PHP5
				}
			}

			$response                    = array ();
			$response['list']            = $list;
			$response['row_count']       = $rows_found;
			$response['next_offset']     = $next_offset;
			$response['previous_offset'] = $previous_offset;

			return $response;
		}

		/**
		 * @param $query
		 *
		 * @return array|null
		 */
		public function process_full_list_query ($query) {
			$this->log->debug ("CRMEntity:process_full_list_query");
			$result = &$this->db->query ($query, false);
			if ($this->db->getRowCount ($result) > 0) {
				//	$this->db->println("process_full mid=".$this->table_index." mname=".$this->module_name);
				// We have some data.
				while ($row = $this->db->fetchByAssoc ($result)) {
					$rowid = $row[ $this->table_index ];

					if (isset($rowid)) {
						$this->retrieve_entity_info ($rowid, $this->module_name);
					} else {
						$this->db->println ("rowid not set unable to retrieve");
					}

					//clone function added to resolvoe PHP5 compatibility issue in Dashboards
					//If we do not use clone, while using PHP5, the memory address remains fixed but the
					//data gets overridden hence all the rows that come in bear the same value. This in turn
					//provides a wrong display of the Dashboard graphs. The data is erroneously shown for a specific month alone
					//Added by Richie
					$list[] = clone($this); //added by Richie to support PHP5
				}
			}

			if (isset($list)) {
				return $list;
			} else {
				return null;
			}
		}

		/**
		 * This function should be overridden in each module.  It marks an item as deleted.
		 * If it is not overridden, then marking this type of item is not allowed
		 *
		 * @param integer $id
		 *
		 * @throws Exception
		 */
		public function mark_deleted ($id) {
			global $current_user, $adb;

			$this->retrieve_entity_info ($id, get_class ($this));

			$oldDieOnError = $adb->dieOnError;
			$adb->setDieOnError (false);
			BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('DELETE', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $this);
			$adb->setDieOnError ($oldDieOnError);

			$date_var = date ("Y-m-d H:i:s");
			$query    = "UPDATE vtiger_crmentity SET deleted=1,modifiedtime=?,modifiedby=? WHERE crmid=?";
			$this->db->pquery ($query, array ($this->db->formatDate ($date_var, true), $current_user->id, $id), true, "Error marking record deleted: ");

			$oldDieOnError = $adb->dieOnError;
			$adb->setDieOnError (false);
			BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('DELETE', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $this);
			$adb->setDieOnError ($oldDieOnError);
		}

		/**
		 * @param $fields_array
		 * @param bool $encode
		 *
		 * @return $this|null
		 */
		public function retrieve_by_string_fields ($fields_array, $encode = true) {
			$query  = "SELECT * FROM $this->table_name";
			$result = &$this->db->requireSingleResult ($query, true, "Retrieving record:");
			if (empty($result)) {
				return null;
			}

			$row = $this->db->fetchByAssoc ($result, -1, $encode);

			foreach ($this->column_fields as $field) {
				if (isset($row[ $field ])) {
					$this->$field = $row[ $field ];
				}
			}
			return $this;
		}

		/**
		 * this method is called during an import before inserting a bean
		 * define an associative array called $special_fields
		 * the keys are user defined, and don't directly map to the bean's vtiger_fields
		 * the value is the method name within that bean that will do extra
		 * processing for that vtiger_field. example: 'full_name'=>'get_names_from_full_name'
		 */
		public function process_special_fields () {
			foreach ($this->special_functions as $func_name) {
				if (method_exists ($this, $func_name)) {
					$this->$func_name();
				}
			}
		}

		/**
		 * Function to check if the custom vtiger_field vtiger_table exists
		 * return true or false
		 *
		 * @param string $tablename
		 *
		 * @return boolean
		 */
		public function checkIfCustomTableExists ($tablename) {
			global $adb;
			$query   = "SELECT * FROM " . $adb->sql_escape_string ($tablename);
			$result  = $this->db->pquery ($query, array ());
			$testrow = $this->db->num_fields ($result);
			if ($testrow > 1) {
				$exists = true;
			} else {
				$exists = false;
			}
			return $exists;
		}

		/**
		 * function to construct the query to fetch the custom vtiger_fields
		 * return the query to fetch the custom vtiger_fields
		 *
		 * @param string $tablename
		 * @param string $module
		 *
		 * @return string
		 * @throws Exception
		 */
		public function constructCustomQueryAddendum ($tablename, $module) {
			global $adb;
			$tabid   = getTabid ($module);
			$sql1    = "SELECT columnname,fieldlabel FROM vtiger_field WHERE generatedtype=2 AND tabid=? AND vtiger_field.presence IN (0,2)";
			$result  = $adb->pquery ($sql1, array ($tabid));
			$numRows = $adb->num_rows ($result);
			$sql3    = "select ";
			for ($i = 0; $i < $numRows; $i++) {
				$columnName = $adb->query_result ($result, $i, "columnname");
				$fieldlabel = $adb->query_result ($result, $i, "fieldlabel");
				//construct query as below
				if ($i == 0) {
					$sql3 .= $tablename . "." . $columnName . " '" . $fieldlabel . "'";
				} else {
					$sql3 .= ", " . $tablename . "." . $columnName . " '" . $fieldlabel . "'";
				}
			}
			if ($numRows > 0) {
				$sql3 = $sql3 . ',';
			}
			return $sql3;
		}

		/**
		 * This function returns a full (ie non-paged) list of the current object type.
		 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
		 * All Rights Reserved..
		 * Contributor(s): ______________________________________..
		 *
		 * @param string $order_by
		 * @param string $where
		 *
		 * @return array|null
		 */
		public function get_full_list ($order_by = "", $where = "") {
			$query = $this->create_list_query ($order_by, $where);
			return $this->process_full_list_query ($query);
		}

		/**
		 * Track the viewing of a detail record.  This leverages get_summary_text() which is object specific
		 * params $user_id - The user that is viewing the record.
		 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc..
		 * All Rights Reserved..
		 * Contributor(s): ______________________________________..
		 *
		 * @param integer $user_id
		 * @param $current_module
		 * @param string $id
		 */
		public function track_view ($user_id, $current_module, $id = '') {
			$this->log->debug ("About to call vtiger_tracker (user_id, module_name, item_id)($user_id, $current_module, $this->id)");

			$tracker = new Tracker();
			$tracker->track_view ($user_id, $current_module, $id, '');
		}

		/**
		 * Function to get the column value of a field when the field value is empty ''
		 *
		 * @param string $columnname -- Column name for the field
		 * @param string $fldvalue -- Input value for the field taken from the User
		 * @param string $fieldname -- Name of the Field
		 * @param integer $uitype -- UI type of the field
		 * @param string $datatype
		 *
		 * @return string Column value of the field.
		 */
		public function get_column_value ($columnname, $fldvalue, $fieldname, $uitype, $datatype = '') {
			global $log, $adb;
			$log->debug ("Entering function get_column_value ($columnname, $fldvalue, $fieldname, $uitype, $datatype='')");

			// Added for the fields of uitype '57' which has datatype mismatch in crmentity table and particular entity table
			if ($uitype == 57 && $fldvalue == '') {
				return 0;
			}
			if (is_uitype ($uitype, "_date_") && $fldvalue == '') {
				return null;
			}
			if ($datatype == 'I' || $datatype == 'N' || $datatype == 'NN') {
				// Para campos numéricos vacíos, verificar si tienen valor por defecto
				// Si el campo tiene valor por defecto, usarlo; si no, retornar NULL
				if ($fldvalue == '') {
					// Consultar el valor por defecto del campo
					$defaultQuery = "SELECT defaultvalue FROM vtiger_field WHERE fieldname = ? LIMIT 1";
					$defaultResult = $adb->pquery ($defaultQuery, array ($fieldname));
					
					if ($adb->num_rows ($defaultResult) > 0) {
						$defaultValue = $adb->query_result ($defaultResult, 0, 'defaultvalue');
						if (!empty ($defaultValue)) {
							// Usar el valor por defecto configurado
							DatabaseUtils::closeResult ($defaultResult);
							$defaultResult = null;
							$log->debug ("Using default value for field $fieldname: $defaultValue");
							return $defaultValue;
						}
					}
					DatabaseUtils::closeResult ($defaultResult);
					$defaultResult = null;
					
					// Si no hay valor por defecto, retornar NULL
					$log->debug ("No default value for field $fieldname, returning NULL");
					return null;
				}
				return $fldvalue;
			}
			// Para cualquier campo vacío (no solo numéricos), verificar si tiene valor por defecto
			// Esto soluciona el problema con campos como feedbacks que tienen defaultvalue pero no son numéricos
			if ($fldvalue == '' || $fldvalue === null) {
				$defaultQuery = "SELECT defaultvalue FROM vtiger_field WHERE fieldname = ? AND tabid = (SELECT tabid FROM vtiger_tab WHERE name = ?) LIMIT 1";
				$defaultResult = $adb->pquery ($defaultQuery, array ($fieldname, $this->module_name));
				
				if ($adb->num_rows ($defaultResult) > 0) {
					$defaultValue = $adb->query_result ($defaultResult, 0, 'defaultvalue');
					if ($defaultValue !== null && $defaultValue !== '') {
						// Usar el valor por defecto configurado
						DatabaseUtils::closeResult ($defaultResult);
						$defaultResult = null;
						$log->debug ("Using default value for field $fieldname (non-numeric): $defaultValue");
						return $defaultValue;
					}
				}
				DatabaseUtils::closeResult ($defaultResult);
				$defaultResult = null;
			}
			$log->debug ("Exiting function get_column_value");
			return $fldvalue;
		}

		/**
		 * Function to make change to column fields, depending on the current user's accessibility for the fields
		 */
		public function apply_field_security () {
			global $current_user, $currentModule;

			require_once ('include/utils/UserInfoUtil.php');
			foreach ($this->column_fields as $fieldname => $fieldvalue) {
				$reset_value = false;
				if (getFieldVisibilityPermission ($currentModule, $current_user->id, $fieldname) != '0') {
					$reset_value = true;
				}

				if ($fieldname == "record_id" || $fieldname == "record_module") {
					$reset_value = false;
				}

				if ($reset_value == true) {
					$this->column_fields [ $fieldname ] = "";
				}
			}
		}

		/**
		 * Function invoked during export of module record value.
		 *
		 * @param string $key
		 * @param string $value
		 *
		 * @return string
		 */
		public function transform_export_value ($key, $value) {
			// NOTE: The sub-class can override this function as required.
			return $value;
		}

		/**
		 * Function to initialize the importable fields array, based on the User's accessibility to the fields
		 *
		 * @param string $module
		 */
		public function initImportableFields ($module) {
			global $current_user, $adb;
			require_once ('include/utils/UserInfoUtil.php');

			$skip_uitypes = array ('4'); // uitype 4 is for Mod numbers
			// Look at cache if the fields information is available.
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module);

			if ($cachedModuleFields === false) {
				getColumnFields ($module); // This API will initialize the cache as well
				// We will succeed now due to above function call
				$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module);
			}

			$colf = array ();

			if ($cachedModuleFields) {
				foreach ($cachedModuleFields as $fieldinfo) {
					// Skip non-supported fields
					if (in_array ($fieldinfo['uitype'], $skip_uitypes)) {
						continue;
					} else {
						$colf[ $fieldinfo['fieldname'] ] = $fieldinfo['uitype'];
					}
				}
			}

			foreach ($colf as $key => $value) {
				if (getFieldVisibilityPermission ($module, $current_user->id, $key, 'readwrite') == '0') {
					$this->importable_fields[ $key ] = $value;
				}
			}
		}

		/**
		 * Function to initialize the required fields array for that particular module
		 *
		 * @param string $module
		 *
		 * @throws Exception
		 */
		public function initRequiredFields ($module) {
			global $adb;

			$tabid   = getTabId ($module);
			$sql     = "SELECT * FROM vtiger_field WHERE tabid= ? AND typeofdata LIKE '%M%' AND uitype NOT IN ('53','70') AND vtiger_field.presence IN (0,2)";
			$result  = $adb->pquery ($sql, array ($tabid));
			$numRows = $adb->num_rows ($result);
			for ($i = 0; $i < $numRows; $i++) {
				$fieldName                           = $adb->query_result ($result, $i, "fieldname");
				$this->required_fields[ $fieldName ] = 1;
			}
		}

		/**
		 * Function to delete an entity with given Id
		 *
		 * @param string $module
		 * @param integer $id
		 */
		public function trash ($module, $id) {
			global $current_user, $adb;

			require_once ("include/events/include.inc");
			$em = new VTEventsManager($adb);

			// Initialize Event trigger cache
			$em->initTriggerCache ();

			$entityData = VTEntityData::fromEntityId ($adb, $id);

			$em->triggerEvent ("vtiger.entity.beforedelete", $entityData);

			$this->mark_deleted ($id);
			$this->unlinkDependencies ($module, $id);

			$sql_recentviewed = 'DELETE FROM vtiger_tracker WHERE user_id = ? AND item_id = ?';
			$this->db->pquery ($sql_recentviewed, array ($current_user->id, $id));

			$em->triggerEvent ("vtiger.entity.afterdelete", $entityData);
		}

		/**
		 * Function to unlink all the dependent entities of the given Entity by Id
		 *
		 * @param string $module
		 * @param integer $id
		 */
		public function unlinkDependencies ($module, $id) {
			$fieldRes    = $this->db->pquery ('SELECT tabid, tablename, columnname FROM vtiger_field WHERE fieldid IN (SELECT fieldid FROM vtiger_fieldmodulerel WHERE relmodule=?)', array ($module));
			$numOfFields = $this->db->num_rows ($fieldRes);
			for ($i = 0; $i < $numOfFields; $i++) {
				$tabId      = $this->db->query_result ($fieldRes, $i, 'tabid');
				$tableName  = $this->db->query_result ($fieldRes, $i, 'tablename');
				$columnName = $this->db->query_result ($fieldRes, $i, 'columnname');

				$relatedModule = vtlib_getModuleNameById ($tabId);
				$focusObj      = CRMEntity::getInstance ($relatedModule);

				//Backup Field Relations for the deleted entity
				$relQuery        = "SELECT $focusObj->table_index FROM $tableName WHERE $columnName=?";
				$relResult       = $this->db->pquery ($relQuery, array ($id));
				$numOfRelRecords = $this->db->num_rows ($relResult);
				if ($numOfRelRecords > 0) {
					$recordIdsList = array ();
					for ($k = 0; $k < $numOfRelRecords; $k++) {
						$recordIdsList[] = $this->db->query_result ($relResult, $k, $focusObj->table_index);
					}
					$params = array ($id, RB_RECORD_UPDATED, $tableName, $columnName, $focusObj->table_index, implode (",", $recordIdsList));
					$this->db->pquery ('INSERT INTO vtiger_relatedlists_rb VALUES (?,?,?,?,?,?,NULL)', $params);
				}
			}
		}

		/**
		 * Function to unlink an entity with given Id from another entity
		 *
		 * @param integer $id
		 * @param string $return_module
		 * @param integer $return_id
		 */
		public function unlinkRelationship ($id, $return_module, $return_id) {
			global $currentModule;

			$query  = 'DELETE FROM vtiger_crmentityrel WHERE (crmid=? AND relcrmid=?) OR (relcrmid=? AND crmid=?)';
			$params = array ($id, $return_id, $id, $return_id);
			$this->db->pquery ($query, $params);

			$fieldRes    = $this->db->pquery ('SELECT tabid, tablename, columnname FROM vtiger_field WHERE fieldid IN (SELECT fieldid FROM vtiger_fieldmodulerel WHERE module=? AND relmodule=?)', array ($currentModule, $return_module));
			$numOfFields = $this->db->num_rows ($fieldRes);
			for ($i = 0; $i < $numOfFields; $i++) {
				$tabId      = $this->db->query_result ($fieldRes, $i, 'tabid');
				$tableName  = $this->db->query_result ($fieldRes, $i, 'tablename');
				$columnName = $this->db->query_result ($fieldRes, $i, 'columnname');

				$relatedModule = vtlib_getModuleNameById ($tabId);
				$focusObj      = CRMEntity::getInstance ($relatedModule);

				$updateQuery  = "UPDATE $tableName SET $columnName=? WHERE $columnName=? AND $focusObj->table_index=?";
				$updateParams = array (null, $return_id, $id);
				$this->db->pquery ($updateQuery, $updateParams);
			}
		}

		/**
		 * Function to restore a deleted record of specified module with given crmid
		 *
		 * @param string $module -- module name:: Type varchar
		 * @param integer $id
		 */
		public function restore ($module, $id) {
			global $current_user;

			$this->db->println ("TRANS restore starts $module");
			$this->db->startTransaction ();

			$date_var = date ("Y-m-d H:i:s");
			$query    = 'UPDATE vtiger_crmentity SET deleted=0,modifiedtime=?,modifiedby=? WHERE crmid = ?';
			$this->db->pquery ($query, array ($this->db->formatDate ($date_var, true), $current_user->id, $id), true, "Error restoring records :");
			//Restore related entities/records
			$this->restoreRelatedRecords ($module, $id);

			//Event triggering code
			require_once ("include/events/include.inc");
			global $adb;
			$em = new VTEventsManager($adb);

			// Initialize Event trigger cache
			$em->initTriggerCache ();

			$this->id   = $id;
			$entityData = VTEntityData::fromCRMEntity ($this);
			//Event triggering code
			$em->triggerEvent ("vtiger.entity.afterrestore", $entityData);
			//Event triggering code ends

			$this->db->completeTransaction ();
			$this->db->println ("TRANS restore ends");
		}

		/**
		 * Function to restore all the related records of a given record by id
		 *
		 * @param string $module
		 * @param integer $record
		 */
		public function restoreRelatedRecords ($module, $record) {
			$result  = $this->db->pquery ('SELECT * FROM vtiger_relatedlists_rb WHERE entityid = ?', array ($record));
			$numRows = $this->db->num_rows ($result);
			for ($i = 0; $i < $numRows; $i++) {
				$action          = $this->db->query_result ($result, $i, "action");
				$rel_table       = $this->db->query_result ($result, $i, "rel_table");
				$rel_column      = $this->db->query_result ($result, $i, "rel_column");
				$ref_column      = $this->db->query_result ($result, $i, "ref_column");
				$related_crm_ids = $this->db->query_result ($result, $i, "related_crm_ids");

				if (strtoupper ($action) == RB_RECORD_UPDATED) {
					$related_ids = explode (",", $related_crm_ids);
					if ($rel_table == 'vtiger_crmentity' && $rel_column == 'deleted') {
						$sql = "UPDATE $rel_table set $rel_column = 0 WHERE $ref_column IN (" . generateQuestionMarks ($related_ids) . ")";
						$this->db->pquery ($sql, array ($related_ids));
					} else {
						$sql = "UPDATE $rel_table set $rel_column = ? WHERE $rel_column = 0 AND $ref_column IN (" . generateQuestionMarks ($related_ids) . ")";
						$this->db->pquery ($sql, array ($record, $related_ids));
					}
				} else if (strtoupper ($action) == RB_RECORD_DELETED) {
					if ($rel_table == 'vtiger_seproductrel') {
						$sql = "INSERT INTO $rel_table($rel_column, $ref_column, 'setype') VALUES (?,?,?)";
						$this->db->pquery ($sql, array ($record, $related_crm_ids, $module));
					} else {
						$sql = "INSERT INTO $rel_table($rel_column, $ref_column) VALUES (?,?)";
						$this->db->pquery ($sql, array ($record, $related_crm_ids));
					}
				}
			}

			//Clean up the the backup data also after restoring
			$this->db->pquery ('DELETE FROM vtiger_relatedlists_rb WHERE entityid = ?', array ($record));
		}

		/**
		 * Function to initialize the sortby fields array
		 *
		 * @param string $module
		 *
		 * @throws Exception
		 */
		public function initSortByField ($module) {
			global $adb, $log;
			$log->debug ("Entering function initSortByField ($module)");
			// Define the columnname's and uitype's which needs to be excluded
			$exclude_columns = array ('parent_id', 'quoteid', 'vendorid', 'access_count');
			$exclude_uitypes = array ();

			$tabid = getTabId ($module);
			if ($module == 'Calendar') {
				$tabid = array ('9', '16');
			}
			$sql    = "SELECT columnname FROM vtiger_field WHERE (fieldname NOT LIKE '%\_id' OR fieldname IN ('assigned_user_id')) AND tabid IN (" . generateQuestionMarks ($tabid) . ") AND vtiger_field.presence IN (0,2)";
			$params = array ($tabid);
			if (count ($exclude_columns) > 0) {
				$sql .= " AND columnname NOT IN (" . generateQuestionMarks ($exclude_columns) . ")";
				array_push ($params, $exclude_columns);
			}
			if (count ($exclude_uitypes) > 0) {
				$sql .= " AND uitype NOT IN (" . generateQuestionMarks ($exclude_uitypes) . ")";
				array_push ($params, $exclude_uitypes);
			}
			$result   = $adb->pquery ($sql, $params);
			$num_rows = $adb->num_rows ($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$columnname = $adb->query_result ($result, $i, 'columnname');
				if (in_array ($columnname, $this->sortby_fields)) {
					continue;
				} else {
					$this->sortby_fields[] = $columnname;
				}
			}
			if ($tabid == 21 or $tabid == 22) {
				$this->sortby_fields[] = 'crmid';
			}
			$log->debug ("Exiting initSortByField");
		}

		/**
		 * Function to set the Sequence string and sequence number starting value
		 *
		 * @param string $mode
		 * @param string $module
		 * @param string $req_str
		 * @param string $req_no
		 *
		 * @return boolean|string
		 * @throws Exception
		 */
		public function setModuleSeqNumber ($mode, $module, $req_str = '', $req_no = '') {
			global $adb;
			//when we configure the invoice number in Settings this will be used
			if ($mode == "configure" && $req_no != '') {
				$check = $adb->pquery ("SELECT cur_id FROM vtiger_modentity_num WHERE semodule=? AND prefix = ?", array ($module, $req_str));
				if ($adb->num_rows ($check) == 0) {
					$numid  = $adb->getUniqueId ("vtiger_modentity_num");
					$active = $adb->pquery ("SELECT num_id FROM vtiger_modentity_num WHERE semodule=? AND active=1", array ($module));
					$adb->pquery ("UPDATE vtiger_modentity_num SET active=0 WHERE num_id=?", array ($adb->query_result ($active, 0, 'num_id')));

					$adb->pquery ("INSERT INTO vtiger_modentity_num VALUES(?,?,?,?,?,?)", array ($numid, $module, $req_str, $req_no, $req_no, 1));
					return true;
				} else if ($adb->num_rows ($check) != 0) {
					$num_check = $adb->query_result ($check, 0, 'cur_id');
					if ($req_no < $num_check) {
						return false;
					} else {
						$adb->pquery ("UPDATE vtiger_modentity_num SET active=0 WHERE active=1 AND semodule=?", array ($module));
						$adb->pquery ("UPDATE vtiger_modentity_num SET cur_id=?, active = 1 WHERE prefix=? AND semodule=?", array ($req_no, $req_str, $module));
						return true;
					}
				}
			} else if ($mode == "increment" || $mode == "decrement") {
				$bSequence = false;

				$this->crmid_sequence = $this->column_fields [ obtenerValorVariable ('field_sequence', $module) ];
				if (isset($this->crmid_sequence) && !empty($this->crmid_sequence)) {
					//It checks if there own numbering for the record associated
					$check = $adb->pquery ("SELECT cur_id,prefix FROM vtiger_modentity_num WHERE semodule=? AND active = 1 AND crmid = ?", array ($module, $this->crmid_sequence));

					if ($adb->num_rows ($check) == 1) {
						$prefix    = $adb->query_result ($check, 0, 'prefix');
						$curid     = $adb->query_result ($check, 0, 'cur_id');
						$bSequence = true;
					}
				}
				if (!$bSequence) {
					//when we save new invoice we will increment the invoice id and write
					$check  = $adb->pquery ("SELECT cur_id,prefix FROM vtiger_modentity_num WHERE semodule=? AND active = 1", array ($module));
					$prefix = $adb->query_result ($check, 0, 'prefix');
					$curid  = $adb->query_result ($check, 0, 'cur_id');
				}
				//Se actualizan los datos de los campos según la siguiente secuencia

				foreach ($this->column_fields as $campo => $valor) {
					if (getUItype ($module, $campo) == 5 || getUItype ($module, $campo) == 6 || getUItype ($module, $campo) == 23) {
						$valor  = DateTimeField::convertToDBFormat ($valor);
						$prefix = str_replace ('$YEAR_' . $campo . '$', substr ($valor, 0, 4), $prefix);
						$prefix = str_replace ('$MONTH_' . $campo . '$', substr ($valor, 5, 2), $prefix);
						$prefix = str_replace ('$DAY_' . $campo . '$', substr ($valor, 8, 2), $prefix);
						$prefix = str_replace ('$' . $campo . '$', $valor, $prefix);
					} else {
						// EGC David pidió substring de un campo, el formato sería $campo|x$
						// donde x es la cantidad de caracteres a extraer,
						// ej: $timetravel|1$ extrae M o T del campo que indica el turno del viaje en tuninha
						if (preg_match ('/\$' . $campo . '\|(.?)\$/', $prefix, $matches)) {
							$prefix = str_replace ($matches[0], substr ($valor, 0, $matches[1]), $prefix);
						} else {
							$prefix = str_replace ('$' . $campo . '$', $valor, $prefix);
						}
					}
				}

				$prefix = str_replace ('$YEAR$', date ("Y"), $prefix);
				$prefix = str_replace ('$MONTH$', date ("m"), $prefix);
				$prefix = str_replace ('$DAY$', date ("d"), $prefix);

				if (strstr ($prefix, '$CURID$')) {
					$prefix      = str_replace ('$CURID$', $curid, $prefix);
					$prev_inv_no = $prefix;
				} else {
					$prev_inv_no = $prefix . $curid;
				}

				$strip = strlen ($curid) - strlen ($curid + 1);
				if ($strip < 0) {
					$strip = 0;
				}
				$temp = str_repeat ("0", $strip);
				if ($mode == "increment") {
					$req_no .= $temp . ($curid + 1);
				} else { // EGC casos donde no se quiere incrementar el contador, ej: devolución de billetes en viajes.
					$req_no .= $temp . ($curid - 1);
				}

				$adb->pquery ("UPDATE vtiger_modentity_num SET cur_id=? WHERE cur_id=? AND active=1 AND semodule=?", array ($req_no, $curid, $module));
				return decode_html ($prev_inv_no);
			} else {
				return false;
			}
		}

		/**
		 * Function to check if module sequence numbering is configured for the given module or not
		 *
		 * @param string $module
		 *
		 * @return boolean
		 */
		public function isModuleSequenceConfigured ($module) {
			$adb    = PearDatabase::getInstance ();
			$result = $adb->pquery ('SELECT 1 FROM vtiger_modentity_num WHERE semodule = ? AND active = 1', array ($module));
			if ($result && $adb->num_rows ($result) > 0) {
				return true;
			}
			return false;
		}

		/**
		 * Function to get the next module sequence number for a given module
		 *
		 * @param string $module
		 *
		 * @return array
		 * @throws Exception
		 */
		public function getModuleSeqInfo ($module) {
			global $adb;
			$check  = $adb->pquery ("SELECT cur_id,prefix FROM vtiger_modentity_num WHERE semodule=? AND active = 1", array ($module));
			$prefix = $adb->query_result ($check, 0, 'prefix');
			$curid  = $adb->query_result ($check, 0, 'cur_id');
			return array ($prefix, $curid);
		}

		/**
		 * Function to check if the mod number already exists
		 *
		 * @param $table
		 * @param $column
		 * @param $no
		 *
		 * @return boolean
		 */
		public function checkModuleSeqNumber ($table, $column, $no) {
			global $adb;
			$result   = $adb->pquery ("select " . $adb->sql_escape_string ($column) . " from " . $adb->sql_escape_string ($table) . " where " . $adb->sql_escape_string ($column) . " = ?", array ($no));
			$num_rows = $adb->num_rows ($result);

			if ($num_rows > 0) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * @param $module
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public function updateMissingSeqNumber ($module) {
			global $log, $adb;
			$log->debug ("Entered updateMissingSeqNumber function");

			vtlib_setup_modulevars ($module, $this);

			if (!$this->isModuleSequenceConfigured ($module)) {
				return null;
			}

			$tabid     = getTabid ($module);
			$fieldinfo = $adb->pquery ("SELECT * FROM vtiger_field WHERE tabid = ? AND uitype = 4", array ($tabid));

			$returninfo = array ();

			if ($fieldinfo && $adb->num_rows ($fieldinfo)) {
				// TODO: We assume the following for module sequencing field
				// 1. There will be only field per module
				// 2. This field is linked to module base table column
				$fld_table  = $adb->query_result ($fieldinfo, 0, 'tablename');
				$fld_column = $adb->query_result ($fieldinfo, 0, 'columnname');

				if ($fld_table == $this->table_name) {
					$records = $adb->query ("SELECT $this->table_index AS recordid FROM $this->table_name " .
											"WHERE $fld_column = '' OR $fld_column is NULL");

					if ($records && $adb->num_rows ($records)) {
						$returninfo['totalrecords']   = $adb->num_rows ($records);
						$returninfo['updatedrecords'] = 0;

						$modseqinfo = $this->getModuleSeqInfo ($module);
						$prefix     = $modseqinfo[0];
						$cur_id     = $modseqinfo[1];
						$segNumber  = intval($cur_id + 0);
						$old_cur_id = $cur_id;
						while ($recordinfo = $adb->fetch_array ($records)) {
							$strip = strlen ($cur_id) - strlen ($segNumber . '');
							
							if ($strip < 0) {
								$strip = 0;
							}
							$temp  = str_repeat ("0", $strip);
							$value = "{$prefix}{$temp}" . $segNumber;
							$adb->pquery ("UPDATE $fld_table SET $fld_column = ? WHERE $this->table_index = ?", array ($value, $recordinfo['recordid']));
							$returninfo['updatedrecords'] = $returninfo['updatedrecords'] + 1;
							$segNumber++;
						}
						$cur_id = $segNumber;
						if ($old_cur_id != $cur_id) {
							$value =  "{$temp}{$cur_id}";
							$adb->pquery ("UPDATE vtiger_modentity_num SET cur_id=? WHERE semodule=? AND active=1", array ($value, $module));
						}
					}
				} else {
					$log->fatal ("Updating Missing Sequence Number FAILED! REASON: Field table and module table mismatching.");
				}
			}
			return $returninfo;
		}

		/**
		 * Generic function to get attachments in the related list of a given module
		 *
		 * @param $id
		 * @param $cur_tab_id
		 * @param $rel_tab_id
		 * @param bool $actions
		 *
		 * @return array
		 */
		public function get_attachments ($id, $cur_tab_id, $rel_tab_id, $actions = false) {
			global $currentModule, $singlepane_view;
			$this_module = $currentModule;
			$parenttab   = getParentTab ();

			$related_module = vtlib_getModuleNameById ($rel_tab_id);
			$other          = CRMEntity::getInstance ($related_module);

			// Some standard module class doesn't have required variables
			// that are used in the query, they are defined in this generic API
			vtlib_setup_modulevars ($related_module, $other);

			$singular_modname = vtlib_toSingular ($related_module);
			$button           = '';
			if ($actions) {
				if (is_string ($actions)) {
					$actions = explode (',', strtoupper ($actions));
				}
				if (in_array ('SELECT', $actions) && isPermitted ($related_module, 4, '') == 'yes') {
					$button .= "<input title='" . getTranslatedString ('LBL_SELECT') . " " . getTranslatedString ($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString ('LBL_SELECT') . " " . getTranslatedString ($related_module) . "'>&nbsp;";
				}
				if (in_array ('ADD', $actions) && isPermitted ($related_module, 1, '') == 'yes') {
					$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
							   "<input title='" . getTranslatedString ('LBL_ADD_NEW') . " " . getTranslatedString ($singular_modname) . "' class='crmbutton small create'" .
							   " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
							   " value='" . getTranslatedString ('LBL_ADD_NEW') . " " . getTranslatedString ($singular_modname) . "'>&nbsp;";
				}
			}

			// To make the edit or del link actions to return back to same view.
			if ($singlepane_view == 'true') {
				$returnset = "&return_module=$this_module&return_action=DetailView&return_id=$id";
			} else {
				$returnset = "&return_module=$this_module&return_action=CallRelatedList&return_id=$id";
			}

			$userNameSql = getSqlForNameInDisplayFormat (array (
				'first_name' => 'vtiger_users.first_name',
				'last_name'  => 'vtiger_users.last_name',
			), 'Users');
			$query       = "select case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name," .
						   "'Documents' ActivityType,vtiger_attachments.type  FileType,crm2.modifiedtime lastmodified,vtiger_crmentity.modifiedtime,
				vtiger_seattachmentsrel.attachmentsid attachmentsid, vtiger_notes.notesid crmid,
				vtiger_notes.notecontent description,vtiger_notes.*
				FROM vtiger_notes
				INNER JOIN vtiger_senotesrel ON vtiger_senotesrel.notesid= vtiger_notes.notesid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid= vtiger_notes.notesid and vtiger_crmentity.deleted=0
				INNER JOIN vtiger_crmentity crm2 ON crm2.crmid=vtiger_senotesrel.crmid
				LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_seattachmentsrel  ON vtiger_seattachmentsrel.crmid =vtiger_notes.notesid
				LEFT JOIN vtiger_attachments ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid= vtiger_users.id
				where crm2.crmid=" . $id;

			$return_value = GetRelatedList ($this_module, $related_module, $other, $query, $button, $returnset);

			if ($return_value == null) {
				$return_value = array ();
			}
			$return_value['CUSTOM_BUTTON'] = $button;
			return $return_value;
		}

		/**
		 * For Record View Notification
		 *
		 * @param string|boolean $crmid
		 *
		 * @return boolean
		 */
		public function isViewed ($crmid = false) {
			if (!$crmid) {
				$crmid = $this->id;
			}
			if ($crmid) {
				global $adb;
				$result  = $adb->pquery ("SELECT viewedtime,modifiedtime,smcreatorid,smownerid,modifiedby FROM vtiger_crmentity WHERE crmid=?", array ($crmid));
				$resinfo = $adb->fetch_array ($result);

				$lastviewed  = $resinfo['viewedtime'];
				$modifiedon  = $resinfo['modifiedtime'];
				$smownerid   = $resinfo['smownerid'];
				$smcreatorid = $resinfo['smcreatorid'];
				$modifiedby  = $resinfo['modifiedby'];

				if ($modifiedby == '0' && ($smownerid == $smcreatorid)) {
					/** When module record is created * */
					return true;
				} else if ($smownerid == $modifiedby) {
					/** Owner and Modifier as same. * */
					return true;
				} else if ($lastviewed && $modifiedon) {
					/** Lastviewed and Modified time is available. */
					if ($this->__timediff ($modifiedon, $lastviewed) > 0) {
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * @param $userid
		 */
		public function markAsViewed ($userid) {
			global $adb;
			$adb->pquery ("UPDATE vtiger_crmentity SET viewedtime=? WHERE crmid=? AND smownerid=?", array (date ('Y-m-d H:i:s', time ()), $this->id, $userid));
		}

		/**
		 * Save the related module record information. Triggered from CRMEntity->saveentity method or updateRelations.php
		 *
		 * @param String This module name
		 * @param Integer This module record number
		 * @param String Related module name
		 * @param mixed Integer or array of related module record number
		 */
		public function save_related_module ($module, $crmid, $with_module, $with_crmid) {
			global $adb;
			if (!is_array ($with_crmid)) {
				$with_crmid = array ($with_crmid);
			}
			foreach ($with_crmid as $relcrmid) {
				if ($with_module == 'Documents') {
					$checkpresence = $adb->pquery ("SELECT crmid FROM vtiger_senotesrel WHERE crmid = ? AND notesid = ?", array ($crmid, $relcrmid));
					// Relation already exists? No need to add again
					if ($checkpresence && $adb->num_rows ($checkpresence)) {
						continue;
					}

					$adb->pquery ("INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?,?)", array ($crmid, $relcrmid));
				} else {
					$checkpresence = $adb->pquery ("SELECT crmid FROM vtiger_crmentityrel WHERE
					crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ?", array ($crmid, $module, $relcrmid, $with_module));

					// Relation already exists? No need to add again
					if ($checkpresence && $adb->num_rows ($checkpresence)) {
						continue;
					}

					$adb->pquery ("INSERT INTO vtiger_crmentityrel(crmid, module, relcrmid, relmodule) VALUES(?,?,?,?)", array ($crmid, $module, $relcrmid, $with_module));
				}
			}
		}

		/**
		 * @param $module
		 * @param $crmid
		 * @param $with_module
		 * @param $with_crmid
		 *
		 * @throws Exception
		 */
		public function duplicate_patron ($module, $crmid, $with_module, $with_crmid) {
			global $adb;
			if (!is_array ($with_crmid)) {
				$with_crmid = array ($with_crmid);
			}
			foreach ($with_crmid as $relcrmid) {
				$checkpresence = $adb->pquery ("SELECT *FROM vtiger_crmentityrel WHERE crmid = ? AND module = ? AND relcrmid = ? AND relmodule = ? ", array ($crmid, $module, $relcrmid, $with_module));

				// Relation already exists? No need to add again
				if ($checkpresence && $adb->num_rows ($checkpresence)) {
					continue;
				}

				$sql_patron    = "SELECT * FROM vtiger_" . $with_module . " WHERE " . $with_module . "id=? AND es_patron=?";
				$select_patron = $adb->pquery ($sql_patron, array ($relcrmid, 1));

				$name_module = "vtiger_" . $with_module . "";
				$query_field = $adb->pquery ("SELECT * FROM vtiger_field WHERE tablename=?", array ($name_module));

				//----------Duplico Campos y la informacion del registro Patron
				$focus_duplicado = CRMEntity::getInstance ($with_module);
				while ($row = $adb->fetch_row ($query_field)) {
					if ($row['fieldname'] == 'es_patron') {
						$focus_duplicado->column_fields [ $row['fieldname'] ] = '0';
					} else {
						$focus_duplicado->column_fields [ $row['fieldname'] ] = $adb->query_result ($select_patron, 0, $row['fieldname']);
					}
				}

				$focus_duplicado->save ($with_module);

				//----------Inserto la informacion del registro Patron
				$adb->pquery ("INSERT INTO vtiger_crmentityrel(crmid, module, relcrmid, relmodule) VALUES(?,?,?,?)", array ($crmid, $module, $focus_duplicado->id, $with_module));
			}
		}

		/**
		 * Duplicar relaciones de campos uitype=10 al duplicar un registro
		 * @param string $moduleName Módulo principal
		 * @param integer $originalRecordId ID del registro original
		 * @param integer $newRecordId ID del nuevo registro duplicado
		 */
		public function duplicateUitype10Relations($moduleName, $originalRecordId, $newRecordId) {
			global $adb;
			
			// Obtener todos los campos uitype=10 del módulo
			$tabId = getTabid($moduleName);
			$query = "SELECT f.fieldid, f.fieldname, f.columnname, rel.relmodule 
					  FROM vtiger_field f 
					  LEFT JOIN vtiger_fieldmodulerel rel ON f.fieldid = rel.fieldid 
					  WHERE f.tabid = ? AND f.uitype = 10 AND f.presence != 1";
			$result = $adb->pquery($query, array($tabId));
			
			while ($row = $adb->fetchByAssoc($result)) {
				$fieldName = $row['fieldname'];
				$relatedModule = $row['relmodule'];
				
				// Obtener el valor del campo en el registro original
				$originalEntity = CRMEntity::getInstance($moduleName);
				$originalEntity->retrieve_entity_info($originalRecordId, $moduleName);
				
				if (!empty($originalEntity->column_fields[$fieldName])) {
					$relatedRecordId = $originalEntity->column_fields[$fieldName];
					
					// Duplicar la relación usando save_related_module
					$this->save_related_module($moduleName, $newRecordId, $relatedModule, $relatedRecordId);
				}
			}
		}

		/**
		 * Calcular fecha base y ajustar todas las fechas en duplicación
		 * @param string $moduleName Nombre del módulo
		 * @param integer $originalRecordId ID del registro original
		 * @param integer $newRecordId ID del nuevo registro
		 */
		public function adjustDatesInDuplication($moduleName, $originalRecordId, $newRecordId) {
			global $adb;
						
			// Obtener todas las fechas del registro original
			$allDates = $this->getAllDatesFromRecord($moduleName, $originalRecordId);
			
			// Encontrar la fecha más antigua
			$oldestDate = min($allDates);
			$today = new DateTime();
			
			// Ajustar todas las fechas del nuevo registro
			$this->adjustAllFieldsDates($moduleName, $newRecordId, $oldestDate, $today);
			
			// Ajustar fechas especiales según el módulo
			$this->adjustSpecialDates($moduleName, $originalRecordId, $newRecordId, $oldestDate, $today);
		}

		/**
		 * Obtener todas las fechas de un registro
		 */
		private function getAllDatesFromRecord($moduleName, $recordId) {
			$dates = array();
			$dates = array_merge($dates, $this->getStandardDates($moduleName, $recordId));
			$dates = array_merge($dates, $this->getGridDates($moduleName, $recordId));
			$dates = array_merge($dates, $this->getTableFieldDates($moduleName, $recordId));
			$dates = array_merge($dates, $this->getSpecialModuleDates($moduleName, $recordId));
			return $dates;
		}

		/**
		 * Obtener fechas de campos estándar (uitype=5)
		 */
		private function getStandardDates($moduleName, $recordId) {
			global $adb;
			$dates = array();
			
			$tabId = getTabid($moduleName);
			$query = "SELECT f.fieldname, f.columnname, f.tablename 
					  FROM vtiger_field f 
					  WHERE f.tabid = ? AND f.uitype = 5 AND f.presence != 1 
					  AND f.fieldname NOT IN ('createdtime', 'modifiedtime', 'modifiedby', 'createdby')";
			$result = $adb->pquery($query, array($tabId));
			
			$entity = CRMEntity::getInstance($moduleName);
			$entity->retrieve_entity_info($recordId, $moduleName);
			
			while ($row = $adb->fetchByAssoc($result)) {
				$fieldName = $row['fieldname'];
				if (!empty($entity->column_fields[$fieldName])) {
					try {
						$date = new DateTime($entity->column_fields[$fieldName]);
						$dates[] = $date;
					} catch (Exception $e) {}
				}
			}
			
			return $dates;
		}

		/**
		 * Obtener fechas de campos GRID
		 */
		private function getGridDates($moduleName, $recordId) {
			global $adb;
			$dates = array();
			
			// Implementación para campos GRID
			// TODO: Implementar según la estructura de GRID
			
			return $dates;
		}

		/**
		 * Obtener fechas de campos de tabla
		 */
		private function getTableFieldDates($moduleName, $recordId) {
			global $adb;
			$dates = array();
			
			// Implementación para campos de tabla
			// TODO: Implementar según la estructura de TableField
			
			return $dates;
		}
		
		/**
		 * Obtener fechas de tareas del trabajo
		 */
		private function getWorkTaskDates($recordId) {
			global $adb;
			$dates = array();
			
			$query = "SELECT act.date_start, act.due_date 
					  FROM vtiger_activity act 
					  INNER JOIN vtiger_seactivityrel sar ON sar.activityid = act.activityid 
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid 
					  WHERE sar.crmid = ? AND crm.deleted = 0 AND act.activitytype <> 'Job'";
			$result = $adb->pquery($query, array($recordId));
			
			while ($row = $adb->fetchByAssoc($result)) {
				if (!empty($row['date_start']) && $row['date_start'] !== '0000-00-00') {
					try {
						$dates[] = new DateTime($row['date_start']);
					} catch (Exception $e) {}
				}
				if (!empty($row['due_date']) && $row['due_date'] !== '0000-00-00') {
					try {
						$dates[] = new DateTime($row['due_date']);
					} catch (Exception $e) {}
				}
			}
			
			return $dates;
		}

		/**
		 * Obtener fechas especiales del módulo
		 */
		private function getSpecialModuleDates($moduleName, $recordId) {
			$dates = array();
			
			switch ($moduleName) {
				case 'orden_de_trabajo':
					$dates = array_merge($dates, $this->getWorkTaskDates($recordId));
					break;
				case 'proyectos':
					$dates = array_merge($dates, $this->getProjectWorkDates($recordId));
					break;
				// Agregar otros módulos según sea necesario
			}
			
			return $dates;
		}

		/**
		 * Ajustar todas las fechas de campos estándar
		 */
		private function adjustAllFieldsDates($moduleName, $recordId, $oldestDate, $today) {
			global $adb;
			
			$tabId = getTabid($moduleName);
			// Excluir campos de sistema como createdtime, modifiedtime
			$query = "SELECT f.fieldname, f.columnname, f.fieldlabel, f.tablename 
					  FROM vtiger_field f 
					  WHERE f.tabid = ? AND f.uitype = 5 AND f.presence != 1 
					  AND f.fieldname NOT IN ('createdtime', 'modifiedtime', 'modifiedby', 'createdby')";
			$result = $adb->pquery($query, array($tabId));
			
			$entity = CRMEntity::getInstance($moduleName);
			$entity->retrieve_entity_info($recordId, $moduleName);
			
			while ($row = $adb->fetchByAssoc($result)) {
				$fieldName = $row['fieldname'];
				$fieldLabel = $row['fieldlabel'];
				$tablename = $row['tablename'];
				$columnname = $row['columnname'];
				
				if (!empty($entity->column_fields[$fieldName])) {
					try {
						$originalDate = new DateTime($entity->column_fields[$fieldName]);
						$daysDiff = $originalDate->diff($oldestDate)->days;
						$newDate = clone $today;
						$newDate->add(new DateInterval('P' . $daysDiff . 'D'));
						
						// Actualizar en BD
						$updateQuery = "UPDATE {$tablename} SET {$columnname} = ? WHERE " . 
									   $this->table_index . " = ?";
						$adb->pquery($updateQuery, array($newDate->format('Y-m-d'), $recordId));
						
						// Actualizar en el objeto
						$entity->column_fields[$fieldName] = $newDate->format('Y-m-d');
					} catch (Exception $e) {
						// Ignorar fechas inválidas
					}
				}
			}
		}

		/**
		 * Ajustar fechas especiales según el módulo
		 */
		private function adjustSpecialDates($moduleName, $originalRecordId, $newRecordId, $oldestDate, $today) {
			switch ($moduleName) {
				case 'orden_de_trabajo':
					$this->adjustWorkTaskDates($originalRecordId, $newRecordId, $oldestDate, $today);
					break;
				case 'proyectos':
					$this->adjustProjectWorkDates($originalRecordId, $newRecordId, $oldestDate, $today);
					break;
				// Agregar otros módulos según sea necesario
			}
		}

		/**
		 * Ajustar fechas de tareas del trabajo
		 */
		private function adjustWorkTaskDates($originalRecordId, $newRecordId, $oldestDate, $today) {
			global $adb;
			
			// Si la duplicación viene con las tareas ya ajustadas en el formulario,
			// no volver a duplicarlas desde el registro original.
			if (isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true' &&
				!empty($_REQUEST['projec_task']['types']) && is_array($_REQUEST['projec_task']['types']) && count($_REQUEST['projec_task']['types']) > 0) {
				return;
			}
			
			// Obtener tareas del registro original
			$query = "SELECT act.activityid, act.subject, act.date_start, act.due_date 
					  FROM vtiger_activity act 
					  INNER JOIN vtiger_seactivityrel sar ON sar.activityid = act.activityid 
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid 
					  WHERE sar.crmid = ? AND crm.deleted = 0 AND act.activitytype <> 'Job'";
			$result = $adb->pquery($query, array($originalRecordId));
			
			while ($row = $adb->fetchByAssoc($result)) {
				$originalTaskId = $row['activityid'];
				$taskSubject = $row['subject'];
				
				// Crear nueva tarea duplicada
				$activity = CRMEntity::getInstance('Calendar');
				$activity->retrieve_entity_info($originalTaskId, 'Calendar');
				
				// Ajustar fechas aplicando el mismo desplazamiento relativo a la fecha
				// más antigua del registro original (oldestDate) sobre la fecha base
				// de la nueva copia (today).
				$newStartDate = null;
				$newDueDate = null;
				
				if (!empty($row['date_start']) && $row['date_start'] !== '0000-00-00') {
					$originalStartDate = new DateTime($row['date_start']);
					$daysDiff = $originalStartDate->diff($oldestDate)->days;
					$newStartDate = clone $today;
					$newStartDate->add(new DateInterval('P' . $daysDiff . 'D'));
				}
				
				if (!empty($row['due_date']) && $row['due_date'] !== '0000-00-00') {
					$originalDueDate = new DateTime($row['due_date']);
					$daysDiff = $originalDueDate->diff($oldestDate)->days;
					$newDueDate = clone $today;
					$newDueDate->add(new DateInterval('P' . $daysDiff . 'D'));
				}
				
				// La fecha estimada de finalización no puede ser anterior a la de inicio.
				// En ese caso se asigna inicio + 5 días.
				if ($newStartDate && $newDueDate && $newDueDate->format('U') < $newStartDate->format('U')) {
					$newDueDate = clone $newStartDate;
					$newDueDate->add(new DateInterval('P5D'));
				}
				
				// Establecer nuevas fechas
				$activity->column_fields['date_start'] = $newStartDate ? $newStartDate->format('Y-m-d') : '';
				$activity->column_fields['due_date'] = $newDueDate ? $newDueDate->format('Y-m-d') : '';
				$activity->column_fields['status'] = 'Planned';
				$activity->column_fields['progress'] = '1.00';
				
				// Guardar nueva tarea
				$activity->mode = 'create';
				$activity->save('Calendar');
				
				// Relacionar con el nuevo trabajo
				$this->save_related_module('orden_de_trabajo', $newRecordId, 'Calendar', $activity->id);
			}
			
		}

		/**
		 * Obtener fechas de trabajos de proyecto
		 */
		private function getProjectWorkDates($recordId) {
			global $adb;
			$dates = array();
			
			// Implementación para proyectos
			// TODO: Implementar según la estructura de proyectos
			
			return $dates;
		}

		/**
		 * Ajustar fechas de trabajos de proyecto
		 */
		private function adjustProjectWorkDates($originalRecordId, $newRecordId, $oldestDate, $today) {
			global $adb;
			
			// Implementación para proyectos
			// TODO: Implementar según la estructura de proyectos
		}

		/**
		 * Delete the related module record information. Triggered from updateRelations.php
		 *
		 * @param String This module name
		 * @param Integer This module record number
		 * @param String Related module name
		 * @param mixed Integer or array of related module record number
		 */
		public function delete_related_module ($module, $crmid, $with_module, $with_crmid) {
			global $adb;
			if (!is_array ($with_crmid)) {
				$with_crmid = array ($with_crmid);
			}
			foreach ($with_crmid as $relcrmid) {

				if ($with_module == 'Documents') {
					$adb->pquery ("DELETE FROM vtiger_senotesrel WHERE crmid=? AND notesid=?", array ($crmid, $relcrmid));
				} else {
					$adb->pquery ("DELETE FROM vtiger_crmentityrel WHERE (crmid=? AND module=? AND relcrmid=? AND relmodule=?) OR (relcrmid=? AND relmodule=? AND crmid=? AND module=?)",
						array ($crmid, $module, $relcrmid, $with_module, $crmid, $module, $relcrmid, $with_module));
				}
			}
		}

		/**
		 * Default (generic) function to handle the related list for the module.
		 * NOTE: Vtiger_Module::setRelatedList sets reference to this function in vtiger_relatedlists table
		 * if function name is not explicitly specified.
		 *
		 * @param integer $id
		 * @param integer $cur_tab_id
		 * @param integer $rel_tab_id
		 * @param boolean|array $actions
		 * @param boolean $onlyquery
		 * @param boolean|integer $relationId
		 * @param boolean|integer $isCard
		 *
		 * @return array|string
		 * @throws Exception
		 */
		public function get_related_list ($id, $cur_tab_id, $rel_tab_id, $actions = false, $onlyquery = false, $relationId = false, $isCard = false) {
			global $currentModule, $singlepane_view, $adb;

			$related_module = vtlib_getModuleNameById ($rel_tab_id);
			if ($related_module == 'Calendar') {
				return $this->get_activities ($id, $cur_tab_id, $rel_tab_id, $actions, $isCard);
			}
			$other              = self::getInstance ($related_module);
			$relationshipFields = ModuleRelationshipManager::getInstance ($adb)->fetchRelationFieldById ($relationId);
			if (!empty ($relationshipFields) && (!empty ($relationshipFields->getFieldList ()))) {
				$other->list_fields      = $relationshipFields->getFieldList ();
				$other->list_fields_name = array_combine (array_keys ($relationshipFields->getFieldList ()),array_column ($relationshipFields->getFieldList (), 1));
			}
			// Some standard module class doesn't have required variables
			// that are used in the query, they are defined in this generic API
			vtlib_setup_modulevars ($currentModule, $this);
			vtlib_setup_modulevars ($related_module, $other);

			$singular_modname = 'SINGLE_' . $related_module;

			$button = '';
			if ($actions) {
				if (is_string ($actions)) {
					$actions = explode (',', strtoupper ($actions));
				}
				if ((in_array ('SELECT', $actions)) && (isPermitted ($related_module, 4, '') == 'yes')) {
					if (!$isCard) {
						$buttonLabel = 'Seleccionar ' . getTranslatedString ($singular_modname, $related_module);
						$button .= "<button type=\"button\" class=\"btn btn-primary\" data-current-entity-id=\"{$id}\" data-current-module=\"{$currentModule}\" data-referenced-module=\"{$related_module}\" data-multiple-selection=\"true\" data-title=\"{$buttonLabel}\" onclick=\"RelatedModuleModalUtils.openModal (this);\">{$buttonLabel}</button>";
					} else {
						$buttonLabel = 'Añadir ' . getTranslatedString ($singular_modname, $related_module);
						$button .= "<a href=\"index.php?module={$related_module}&action=EditView&return_module={$currentModule}&return_action=DetailView&return_id={$id}&createmode=link&tab=related_list&relationid={$relationId}\" class=\"link btn btn-primary btn-circle btn-xs\" target=\"_blank\" title=\"{$buttonLabel}\"><i class=\"fa fa-plus  fa-lg\"></i></a>&nbsp;";
					}
				}
				if ((in_array ('ADD', $actions)) && (isPermitted ($related_module, 1, '') == 'yes')) {
					if (!$isCard) {
						$buttonLabel = 'Añadir ' . getTranslatedString ($singular_modname, $related_module);
						$button .= "<a href=\"index.php?module={$related_module}&action=EditView&return_module={$currentModule}&return_action=CallRelatedList&return_id={$id}&createmode=link\" class=\"btn btn-primary\" title=\"{$buttonLabel}\">{$buttonLabel}</a>";
					} else {
						$buttonLabel = 'Seleccionar ' . getTranslatedString ($singular_modname, $related_module);
						$button .= "<a href=\"#\" class=\"link btn btn-info btn-circle btn-xs\"  data-current-entity-id=\"{$id}\" data-current-module=\"{$currentModule}\" data-referenced-module=\"{$related_module}\" data-multiple-selection=\"true\" data-title=\"{$buttonLabel}\" onclick=\"RelatedModuleModalUtils.openModal (this);\"  title=\"{$buttonLabel}\"><i class=\"fa fa-search\" aria-hidden=\"true\"></i></a>";
					}
				}
			}
			$customactions = getRelatedListProperty ($relationId, 'customactions');

			if (!empty($customactions)) {
				//Se reemplaza posibles variables
				$customactions = str_replace ('{$RECORD}', $id, $customactions);
				$button .= $customactions;
			}

			//Se determinan las acciones via VTiger_Links de Tipo RELATED_LIST_LINKS
			$customlink_params = array ('MODULE' => $currentModule, 'RECORD' => $this->id, 'ACTION' => vtlib_purify ($_REQUEST['action']));
			$relatedListLinks  = Vtiger_Link::getAllByType (getTabid ($currentModule), array ('RELATED_LIST_LINKS'), $customlink_params, $relationId);
			foreach ($relatedListLinks as $valor) {
				for ($i = 0; $i < count ($valor); $i++) {
					if (!empty ($valor [ $i ]->linkicon)) {
						$button .= "<a href=\"{$valor [ $i ]->linkurl}\" target=\"_blank\"><img id=\"aidPrintPasajeros\" src=\"themes/images/{$valor [ $i ]->linkicon}\" style=\"border: 0 solid #000000; vertical-align: middle; margin-left: 10px;\" alt=\"{$valor [ $i ]->linklabel}\" title=\"{$valor [ $i ]->linklabel}\"></a>";
					} else {
						$buttonTitle = getTranslatedString ($valor[ $i ]->linklabel) . ' ' . getTranslatedString ($singular_modname);
						$buttonValue = getTranslatedString ($valor[ $i ]->linklabel);
						$button .= "<input title=\"{$buttonTitle}\" class=\"crmbutton small create\" onclick=\"{$valor [ $i ]->linkurl}\" type=\"button\" name=\"button\" value=\"{$buttonValue}\">&nbsp;";
					}
				}
			}
			// To make the edit or del link actions to return back to same view.
			if ($singlepane_view == 'true') {
				$returnset = "&return_module={$currentModule}&return_action=DetailView&return_id={$id}";
			} else {
				$returnset = "&return_module={$currentModule}&return_action=CallRelatedList&return_id={$id}";
			}

			$query = "SELECT vtiger_crmentity.*, $other->table_name.*";

			$userNameSql = getSqlForNameInDisplayFormat (array (
				'first_name' => 'vtiger_users.first_name',
				'last_name'  => 'vtiger_users.last_name',
			), 'Users');
			$query .= ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name";

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
			$queryUnion = '';
			$query    .= " FROM $other->table_name";
			$query    .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $other->table_name.$other->table_index";
			
			$tableFieldRelated  = TableFieldHelper::getRelatedFields ($adb, $currentModule, $related_module);
			if (!empty ($tableFieldRelated)) {
				$tableFieldName  = TableFieldHelper::getTableName ($adb, $cur_tab_id, $tableFieldRelated[0]);
				$tableFieldIndex = $currentModule . 'tfid';
				$queryUnion      = ' UNION ' . $query;
				$queryUnion     .= " INNER JOIN {$tableFieldName} ON {$tableFieldName}.{$tableFieldRelated[1]} = {$other->table_name}.{$other->table_index}";
				$queryUnion     .= " LEFT  JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
				$queryUnion     .= " LEFT  JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
				$queryUnion     .= " WHERE vtiger_crmentity.deleted = 0 AND {$tableFieldName}.{$tableFieldIndex} = {$id}";
			}
			if ($related_module == 'Documents' && $other->table_name == 'vtiger_notes') {
				$query .= " INNER JOIN vtiger_senotesrel ON vtiger_senotesrel.notesid = vtiger_notes.notesid";
			} else {
				$query .= " INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid OR vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)";
			}
			$query .= $more_relation;
			$query .= " LEFT  JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";
			$query .= " LEFT  JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
			if ($related_module == 'Documents' && $other->table_name == 'vtiger_notes') {
				$query .= " WHERE vtiger_crmentity.deleted = 0 AND vtiger_senotesrel.crmid = $id ";
			} else {
				$query .= " WHERE vtiger_crmentity.deleted = 0 AND (vtiger_crmentityrel.crmid = $id OR vtiger_crmentityrel.relcrmid = $id)";
			}

			//Se une los datos de los campos 10 para
			$result = $adb->pquery (
				'SELECT
					vtiger_field.fieldid,
					vtiger_field.tablename,
					vtiger_field.columnname
				FROM
					vtiger_fieldmodulerel
					INNER JOIN vtiger_field ON vtiger_fieldmodulerel.fieldid=vtiger_field.fieldid
				WHERE
					vtiger_fieldmodulerel.module=? AND
					vtiger_fieldmodulerel.relmodule=?',
				array ($related_module, $currentModule)
			);

			$othertables        = '';
			$othermore_relation = '';
			while ($row = $adb->fetchByAssoc ($result)) {
				if (!empty ($other->related_tables)) {
					foreach ($other->related_tables as $tname => $relmap) {
						$othertables .= ", $tname.*";

						// Setup the default JOIN conditions if not specified
						if (empty($relmap[1])) {
							$relmap[1] = $other->table_name;
						}
						if (empty($relmap[2])) {
							$relmap[2] = $relmap[0];
						}
						$othermore_relation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
					}
				}

				$query .= " UNION SELECT vtiger_crmentity.*, " . $row['tablename'] . ".* "
						  . ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name"
						  . $othertables
						  . " FROM vtiger_crmentity INNER JOIN " . $row['tablename']
						  . " ON (vtiger_crmentity.crmid = " . $row['tablename'] . "." . $other->tab_name_index[ $row['tablename'] ] . " AND deleted = 0)"
						  . " LEFT  JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid"
						  . " LEFT  JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid"
						  . $othermore_relation
						  . " WHERE " . $row['tablename'] . "." . $row['columnname'] . " = " . $id;
			}
			$query .= $queryUnion;
			
			if ($onlyquery) {
				return $query;
			}

			$return_value = GetRelatedList ($currentModule, $related_module, $other, $query, $button, $returnset, '', '', '', true, $isCard);

			if ($return_value == null) {
				$return_value = array ();
			}
			$return_value['CUSTOM_BUTTON'] = $button;
			$return_value['delete_btn'] = true;
			return $return_value;
		}

		/**
		 * Default (generic) function to handle the dependents list for the module.
		 * NOTE: UI type '10' is used to stored the references to other modules for a given record.
		 * These dependent records can be retrieved through this function.
		 * For eg: A trouble ticket can be related to an Account or a Contact.
		 * From a given Contact/Account if we need to fetch all such dependent trouble tickets, get_dependents_list function can be used.
		 *
		 * @param integer $id
		 * @param integer $cur_tab_id
		 * @param integer $rel_tab_id
		 * @param boolean|array $actions
		 *
		 * @return array|null
		 */
		public function get_dependents_list ($id, $cur_tab_id, $rel_tab_id, $actions = false) {
			global $currentModule, $singlepane_view, $current_user;

			$related_module = vtlib_getModuleNameById ($rel_tab_id);
			$other          = CRMEntity::getInstance ($related_module);

			// Some standard module class doesn't have required variables
			// that are used in the query, they are defined in this generic API
			vtlib_setup_modulevars ($currentModule, $this);
			vtlib_setup_modulevars ($related_module, $other);

			$singular_modname = 'SINGLE_' . $related_module;

			$button = '';

			// To make the edit or del link actions to return back to same view.
			if ($singlepane_view == 'true') {
				$returnset = "&return_module=$currentModule&return_action=DetailView&return_id=$id";
			} else {
				$returnset = "&return_module=$currentModule&return_action=CallRelatedList&return_id=$id";
			}

			$return_value      = null;
			$dependentFieldSql = $this->db->pquery ("SELECT tabid, fieldname, columnname FROM vtiger_field WHERE uitype='10' AND" .
													" fieldid IN (SELECT fieldid FROM vtiger_fieldmodulerel WHERE relmodule=? AND module=?)", array ($currentModule, $related_module));
			$numOfFields       = $this->db->num_rows ($dependentFieldSql);

			if ($numOfFields > 0) {
				$dependentColumn = $this->db->query_result ($dependentFieldSql, 0, 'columnname');
				$dependentField  = $this->db->query_result ($dependentFieldSql, 0, 'fieldname');

				$button .= '<input type="hidden" name="' . $dependentColumn . '" id="' . $dependentColumn . '" value="' . $id . '">';
				$button .= '<input type="hidden" name="' . $dependentColumn . '_type" id="' . $dependentColumn . '_type" value="' . $currentModule . '">';
				if ($actions) {
					if (is_string ($actions)) {
						$actions = explode (',', strtoupper ($actions));
					}
					if (in_array ('ADD', $actions) && isPermitted ($related_module, 1, '') == 'yes'
						&& getFieldVisibilityPermission ($related_module, $current_user->id, $dependentField, 'readwrite') == '0'
					) {
						$button .= "<input title='" . getTranslatedString ('LBL_ADD_NEW') . " " . getTranslatedString ($singular_modname, $related_module) . "' class='crmbutton small create'" .
								   " onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
								   " value='" . getTranslatedString ('LBL_ADD_NEW') . " " . getTranslatedString ($singular_modname, $related_module) . "'>&nbsp;";
					}
				}

				$query = "SELECT vtiger_crmentity.*, $other->table_name.*";

				$userNameSql = getSqlForNameInDisplayFormat (array (
					'first_name' => 'vtiger_users.first_name',
					'last_name'  => 'vtiger_users.last_name',
				), 'Users');
				$query .= ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name";

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

				$query .= " FROM $other->table_name";
				$query .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $other->table_name.$other->table_index";
				$query .= " INNER  JOIN $this->table_name   ON $this->table_name.$this->table_index = $other->table_name.$dependentColumn";
				$query .= $more_relation;
				$query .= " LEFT  JOIN vtiger_users        ON vtiger_users.id = vtiger_crmentity.smownerid";
				$query .= " LEFT  JOIN vtiger_groups       ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

				$query .= " WHERE vtiger_crmentity.deleted = 0 AND $this->table_name.$this->table_index = $id";

				$return_value = GetRelatedList ($currentModule, $related_module, $other, $query, $button, $returnset);
			}
			if ($return_value == null) {
				$return_value = array ();
			}
			$return_value['CUSTOM_BUTTON'] = $button;

			return $return_value;
		}

		/**
		 * @param $entityId
		 * @param $currentModuleId
		 * @param $relatedModuleId
		 * @param boolean $actions
		 * @param boolean $isCard
		 *
		 * @return array
		 */
		public function get_activities ($entityId, $currentModuleId, $relatedModuleId, $actions = false, $isCard = false) {
			global $singlepane_view;

			$currentModuleName = vtlib_getModuleNameById ($currentModuleId);
			$relatedModuleName = vtlib_getModuleNameById ($relatedModuleId);
			require_once ("modules/{$relatedModuleName}/Activity.php");
			$other = new Activity ();
			vtlib_setup_modulevars ($relatedModuleName, $other);
			if ($singlepane_view == 'true') {
				$returnset = "&return_module={$currentModuleName}&return_action=DetailView&return_id={$entityId}";
			} else {
				$returnset = "&return_module={$currentModuleName}&return_action=CallRelatedList&return_id={$entityId}";
			}

			$button = '<input type="hidden" name="activity_mode" />';
			if ($actions) {
				if (is_string ($actions)) {
					$actions = explode (',', strtoupper ($actions));
				}
				if ((in_array ('SELECT', $actions)) && (isPermitted ($relatedModuleName, 4, '') == 'yes')) {
					if (!$isCard) {
						$buttonLabel = 'Seleccionar ' . getTranslatedString('LBL_TODO', $relatedModuleName);
						$button .= "<button type=\"button\" class=\"btn btn-primary\" data-current-entity-id=\"{$entityId}\" data-current-module=\"{$currentModuleName}\" data-referenced-module=\"{$relatedModuleName}\" data-multiple-selection=\"true\" data-title=\"{$buttonLabel}\" onclick=\"RelatedModuleModalUtils.openModal (this);\">{$buttonLabel}</button>";
					} else {
						$buttonLabel = 'Añadir ' . getTranslatedString ('LBL_TODO', $relatedModuleName);
						$button .= "<a href=\"index.php?module=Calendar&action=EditView&activity_mode=Task&return_module={$currentModuleName}&return_action=DetailView&return_id={$entityId}&idlist={$entityId}&tab=related_list\" class=\"link btn btn-primary btn-circle btn-xs\" title=\"{$buttonLabel}\"><i class=\"fa fa-plus fa-lg\"></i></a>&nbsp;";
					}
				}
				if ((in_array ('ADD', $actions)) && (isPermitted ($relatedModuleName, 1, '') == 'yes')) {
					if (!$isCard) {
						$buttonLabel = 'Añadir ' . getTranslatedString('LBL_TODO', $relatedModuleName);
						$button .= "<a href=\"index.php?module=Calendar&action=EditView&activity_mode=Task&return_module={$currentModuleName}&return_action=CallRelatedList&return_id={$entityId}&idlist={$entityId}\" class=\"btn btn-primary\" title=\"{$buttonLabel}\">{$buttonLabel}</a>";
					} else {
						$buttonLabel = 'Seleccionar ' . getTranslatedString('LBL_TODO', $relatedModuleName);
						$button .= "<a href=\"#\" class=\"link btn btn-info btn-circle btn-xs\"\"  data-current-entity-id=\"{$entityId}\" data-current-module=\"{$currentModuleName}\" data-referenced-module=\"{$relatedModuleName}\" data-multiple-selection=\"true\" data-title=\"{$buttonLabel}\" onclick=\"RelatedModuleModalUtils.openModal (this);\"><i class=\"fa fa-search\" aria-hidden=\"true\"></i></a>";
					}
				}
			}

			$userNameSql = getSqlForNameInDisplayFormat (array ('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
			$query       = "SELECT
								CASE WHEN (vtiger_users.user_name not like '') THEN {$userNameSql} ELSE vtiger_groups.groupname END AS user_name,
								vtiger_activity.activityid,
								vtiger_activity.subject,
								vtiger_activity.activitytype,
								vtiger_activity.date_start,
								vtiger_activity.due_date,
								vtiger_activity.time_start,
								vtiger_activity.time_end,
								vtiger_activity.activityid AS crmid,
								vtiger_crmentity_activity.smownerid,
								vtiger_crmentity_activity.modifiedtime,
								vtiger_activity.eventstatus,
								vtiger_activity.status,
								vtiger_seactivityrel.crmid as parent_id
							FROM
								vtiger_activity
								INNER JOIN vtiger_crmentity AS vtiger_crmentity_activity ON vtiger_crmentity_activity.crmid=vtiger_activity.activityid AND vtiger_crmentity_activity.deleted=0
								INNER JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid=vtiger_activity.activityid
								INNER JOIN vtiger_crmentity AS vtiger_crmentity_related ON vtiger_crmentity_related.crmid=vtiger_seactivityrel.crmid AND vtiger_crmentity_related.deleted=0 AND vtiger_crmentity_related.setype='{$currentModuleName}' AND vtiger_crmentity_related.crmid={$entityId}
								LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity_activity.smownerid
								LEFT OUTER JOIN vtiger_recurringevents ON vtiger_recurringevents.activityid=vtiger_activity.activityid
								LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity_activity.smownerid";
			$returnValue = GetRelatedList ($currentModuleName, $relatedModuleName, $other, $query, $button, $returnset, '', '', '', true, $isCard);
			if ($returnValue == null) {
				$returnValue = array ();
			}
			$returnValue ['CUSTOM_BUTTON'] = $button;
			return $returnValue;
		}

		/**
		 * Move the related records of the specified list of id's to the given record.
		 *
		 * @param string This module name
		 * @param array List of Entity Id's from which related records need to be transfered
		 * @param integer Id of the the Record to which the related records are to be moved
		 */
		public function transferRelatedRecords ($module, $transferEntityIds, $entityId) {
			global $adb, $log;
			$log->debug ("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");
			foreach ($transferEntityIds as $transferId) {
				// Pick the records related to the entity to be transfered, but do not pick the once which are already related to the current entity.
				$relatedRecords = $adb->pquery ("SELECT relcrmid, relmodule FROM vtiger_crmentityrel WHERE crmid=? AND module=?" .
												" AND relcrmid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid=? AND module=?)", array ($transferId, $module, $entityId, $module));
				$numOfRecords   = $adb->num_rows ($relatedRecords);
				for ($i = 0; $i < $numOfRecords; $i++) {
					$relcrmid  = $adb->query_result ($relatedRecords, $i, 'relcrmid');
					$relmodule = $adb->query_result ($relatedRecords, $i, 'relmodule');
					$adb->pquery ("UPDATE vtiger_crmentityrel SET crmid=? WHERE relcrmid=? AND relmodule=? AND crmid=? AND module=?", array ($entityId, $relcrmid, $relmodule, $transferId, $module));
				}

				// Pick the records to which the entity to be transfered is related, but do not pick the once to which current entity is already related.
				$parentRecords = $adb->pquery ("SELECT crmid, module FROM vtiger_crmentityrel WHERE relcrmid=? AND relmodule=?" .
											   " AND crmid NOT IN (SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid=? AND relmodule=?)", array ($transferId, $module, $entityId, $module));
				$numOfRecords  = $adb->num_rows ($parentRecords);
				for ($i = 0; $i < $numOfRecords; $i++) {
					$parcrmid  = $adb->query_result ($parentRecords, $i, 'crmid');
					$parmodule = $adb->query_result ($parentRecords, $i, 'module');
					$adb->pquery ("UPDATE vtiger_crmentityrel SET relcrmid=? WHERE crmid=? AND module=? AND relcrmid=? AND relmodule=?", array ($entityId, $parcrmid, $parmodule, $transferId, $module));
				}
			}
			$log->debug ("Exiting transferRelatedRecords...");
		}

		/**
		 * Function to get the primary query part of a report for which generateReportsQuery Doesnt exist in module
		 *
		 * @param string $module Primary module name
		 *
		 * @return string the query string formed on fetching the related data for report for primary module
		 * @throws Exception
		 *
		 * Modificado 2025-07-04: Solo LEFT JOIN para módulos relacionados seleccionados en el reporte.
		 * @param string $module
		 * @param array|null $allowedRelatedModules Si se pasa, restringe los JOIN a estos módulos relacionados.
		 */
		public function generateReportsQuery ($module, $allowedRelatedModules = null) {
			global $adb, $current_user;
			$primary = CRMEntity::getInstance ($module);

			vtlib_setup_modulevars ($module, $primary);
			$moduletable   = $primary->table_name;
			$moduleindex   = $primary->table_index;
			$modulecftable = $primary->customFieldTable[0];
			$modulecfindex = $primary->customFieldTable[1];

			if (isset($modulecftable)) {
				$cfquery = "INNER JOIN $modulecftable as $modulecftable ON $modulecftable.$modulecfindex=$moduletable.$moduleindex";
			} else {
				$cfquery = '';
			}

			// FIX: Simplificar el JOIN con vtiger_crmentity - Los filtros de permisos se aplican
		// mediante la tabla temporal (vt_tmp_uXXX_tYYY) que se agrega con getNonAdminAccessControlQuery
		// No es necesario duplicar las condiciones de permisos aquí
		$query = "FROM $moduletable $cfquery " .
		"INNER JOIN vtiger_crmentity AS vtiger_crmentity ON vtiger_crmentity.crmid = $moduletable.$moduleindex AND vtiger_crmentity.deleted = 0 " .
		"LEFT JOIN vtiger_groups ON (vtiger_groups.groupid = vtiger_crmentity.smownerid OR vtiger_groups.groupid = vtiger_crmentity.smcreatorid OR vtiger_groups.groupid = vtiger_crmentity.modifiedby) " .
		"LEFT JOIN vtiger_users as vtiger_users ON vtiger_users.id = ".$current_user->id." ";
			
			return $query;
		}

		/**
		 * Function to get the secondary query part of a report for which generateReportsSecQuery Doesnt exist in module
		 *
		 * @param string $module primary module name
		 * @param string $secmodule secondary module name
		 *
		 * @return string the query string formed on fetching the related data for report for secondary module
		 * @throws Exception
		 **/
		public function generateReportsSecQuery ($module, $secmodule, $allowedRelatedModules = null) {
			global $adb;
			$secondary = CRMEntity::getInstance ($secmodule);

			vtlib_setup_modulevars ($secmodule, $secondary);

			$tablename     = $secondary->table_name;
			$tableindex    = $secondary->table_index;
			$modulecftable = $secondary->customFieldTable[0];
			$modulecfindex = $secondary->customFieldTable[1];

			/* 2025-07-08- Marcado porque las tablasmodule>cf no se usan en Platzilla  
			if (isset($modulecftable)) {
				$cfquery = " LEFT JOIN $modulecftable as $modulecftable ON $modulecftable.$modulecfindex=$tablename.$tableindex";
			} else {
			*/
				$cfquery = '';
			//} 
			$query = $this->getRelationQuery ($module, $secmodule, "$tablename", "$tableindex");
			return $query;
		}

		/**
		 * Function to get the security query part of a report
		 *
		 * @param string $module primary module name
		 *
		 * @return string the query string formed on fetching the related data for report for security of the module
		 */
		public function getListViewSecurityParameter ($module) {
			$tabid = getTabid ($module);
			global $current_user;
			if ($current_user) {

				$local_user = clone $current_user;
				require ('user_privileges/user_privileges.php');
				require ('user_privileges/sharing_privileges.php');
			}
			/**
			 * @var array $current_user_groups
			 * @var string $current_user_parent_role_seq
			 */
			$sec_query = '';
			if (esVistaCliente ($current_user->id)) {
				$sec_query .= " and (vtiger_crmentity.smownerid = $current_user->id)";
			} else {
				$sec_query .= " and (vtiger_crmentity.smownerid in($current_user->id) or vtiger_crmentity.smownerid in(select vtiger_user2role.userid FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%') or vtiger_crmentity.smownerid in(select shareduserid FROM vtiger_tmp_read_user_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ") or (";

				if (sizeof ($current_user_groups) > 0) {
					$sec_query .= " vtiger_groups.groupid in (" . implode (",", $current_user_groups) . ") or ";
				}
				$sec_query .= " vtiger_groups.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid FROM vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . "))) ";
			}

			return $sec_query;
		}

		/**
		 * Function to get the security query part of a report
		 *
		 * @param string $module primary module name
		 *
		 * @return string the query string formed on fetching the related data for report for security of the module
		 */
		public function getSecListViewSecurityParameter ($module) {
			$tabid = getTabid ($module);
			global $current_user;
			if ($current_user) {
				$local_user = clone $current_user;
				require ('user_privileges/user_privileges.php');
				require ('user_privileges/sharing_privileges.php');
			}
			$sec_query = '';
			/**
			 * @var array $current_user_groups
			 * @var string $current_user_parent_role_seq
			 */
			if (esVistaCliente ($current_user->id)) {
				$sec_query .= " and (vtiger_crmentity.smownerid = $current_user->id)";
			} else {
				$sec_query .= " and (vtiger_crmentity$module.smownerid in($current_user->id) or vtiger_crmentity$module.smownerid in(select vtiger_user2role.userid FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%') or vtiger_crmentity$module.smownerid in(select shareduserid FROM vtiger_tmp_read_user_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . ") or (";

				if (sizeof ($current_user_groups) > 0) {
					$sec_query .= " vtiger_groups$module.groupid in (" . implode (",", $current_user_groups) . ") or ";
				}
				$sec_query .= " vtiger_groups$module.groupid in(select vtiger_tmp_read_group_sharing_per.sharedgroupid FROM vtiger_tmp_read_group_sharing_per where userid=" . $current_user->id . " and tabid=" . $tabid . "))) ";
			}

			return $sec_query;
		}

		/**
		 * Function to get the relation query part of a report
		 *
		 * @param string $module primary module name
		 * @param string $secmodule secondary module name
		 * @param string $table_name
		 * @param string $column_name
		 *
		 * @return string the query string formed on relating the primary module and secondary module
		 */
		public function getRelationQuery ($module, $secmodule, $table_name, $column_name) {
			// Caso especial para Calendar: usar directamente vtiger_seactivityrel
			if ($secmodule == 'Calendar') {
				$instance = self::getInstance($module);
				$moduleTableIndex = $instance->table_index;
				$moduleTableName = $instance->table_name;
				
				$query = " LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.crmid = $moduleTableName.$moduleTableIndex";
				$query .= " LEFT JOIN $table_name ON $table_name.$column_name = vtiger_seactivityrel.activityid";
				$query .= " AND $table_name.$column_name IN (SELECT crmid FROM vtiger_crmentity WHERE deleted=0)";
				$query .= " AND $table_name.activitytype != 'Emails'";
				
				return $query;
			}
			
			// Lógica original para otros módulos
			$tab    = getRelationTables ($module, $secmodule);
			$tables = array ();
			$fields = array ();

			foreach ($tab as $key => $value) {
				$tables[] = $key;
				$fields[] = $value;
			}
			$pritablename = $tables[0];
			$sectablename = $tables[1];
			$prifieldname = $fields[0][0];
			$secfieldname = $fields[0][1];
			$tmpname      = $pritablename . 'tmp' . $secmodule;
			if (!empty($tables[1]) && !empty($fields[1])) {
				$condvalue = $tables[1] . "." . $fields[1];
				$condition = "$pritablename.$prifieldname=$condvalue";
			} else {
				$condvalue = $table_name . "." . $column_name;
				$condition = "$pritablename.$secfieldname=$condvalue";
			}
			$secQuery = $table_name. " ON " . $condition . " AND ".$table_name.".".$column_name. " IN (select crmid FROM vtiger_crmentity WHERE deleted=0)";

			$query    = '';
			if ($pritablename == 'vtiger_crmentityrel') {
				$condition = "($table_name.$column_name={$tmpname}.{$secfieldname} OR $table_name.$column_name={$tmpname}.{$prifieldname})";
				$query     = " LEFT JOIN vtiger_crmentityrel as $tmpname ON ($condvalue={$tmpname}.{$secfieldname} OR $condvalue={$tmpname}.{$prifieldname}) ";
			} else if (strripos ($pritablename, 'rel') === (strlen ($pritablename) - 3)) {
				$instance      = self::getInstance ($module);
				$sectableindex = $instance->tab_name_index[ $sectablename ];
				$condition     = "$table_name.$column_name=$tmpname.$secfieldname";
				$query         = " LEFT JOIN $pritablename as $tmpname ON ($sectablename.$sectableindex=$tmpname.$prifieldname)";
				if ($secmodule == 'Leads') {
					$condition .= " AND $table_name.converted = 0";
				}
			}

			/*$query .= " LEFT JOIN ($secQuery) as $table_name on {$condition}";*/
			$query .= " LEFT JOIN ". $secQuery;

			return $query;
		}

		/**
		 * This function handles the import for uitype 10 fieldtype
		 *
		 * @param string $module - the current module name
		 * @param string fieldname - the related to field name
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public function add_related_to ($module, $fieldname) {
			global $adb, $current_user;

			$related_to = $this->column_fields [ $fieldname ];

			if (empty($related_to)) {
				return false;
			}

			//check if the field has module information; if not get the first module
			if (!strpos ($related_to, "::::")) {
				$module = getFirstModule ($module, $fieldname);
				$value  = $related_to;
			} else {
				//check the module of the field
				$arr    = explode ("::::", $related_to);
				$module = $arr[0];
				$value  = $arr[1];
			}

			$focus1 = CRMEntity::getInstance ($module);

			$entityNameArr = getEntityField ($module);
			$entityName    = $entityNameArr['fieldname'];
			$query         = "SELECT vtiger_crmentity.deleted, $focus1->table_name.* FROM $focus1->table_name INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=$focus1->table_name.$focus1->table_index where $entityName=? and vtiger_crmentity.deleted=0";
			$result        = $adb->pquery ($query, array ($value));

			if (!isset($this->checkFlagArr[ $module ])) {
				$this->checkFlagArr[ $module ] = (isPermitted ($module, 'EditView', '') == 'yes');
			}

			if ($adb->num_rows ($result) > 0) {
				//record found
				$focus1->id = $adb->query_result ($result, 0, $focus1->table_index);
			} else if ($this->checkFlagArr[ $module ]) {
				//record not found; create it
				$focus1->column_fields [ $focus1->list_link_field ] = $value;
				$focus1->column_fields ['assigned_user_id']         = $current_user->id;
				$focus1->column_fields ['modified_user_id']         = $current_user->id;
				$focus1->save ($module);

				$last_import                   = new UsersLastImport();
				$last_import->assigned_user_id = $current_user->id;
				$last_import->bean_type        = $module;
				$last_import->bean_id          = $focus1->id;
				$last_import->save ();
			} else {
				//record not found and cannot create
				$this->column_fields [ $fieldname ] = "";
				return false;
			}
			if (!empty($focus1->id)) {
				$this->column_fields [ $fieldname ] = $focus1->id;
				return true;
			} else {
				$this->column_fields [ $fieldname ] = "";
				return false;
			}
		}

		/**
		 * Filter in-active fields based on type
		 *
		 * @param String $module
		 */
		public function filterInactiveFields ($module) {
			if ($this->__inactive_fields_filtered) {
				return;
			}

			// Look for fields that has presence value NOT IN (0,2)
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module, array ('1'));
			if ($cachedModuleFields === false) {
				// Initialize the fields calling suitable API
				getColumnFields ($module);
				$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module, array ('1'));
			}

			$hiddenFields = array ();

			if ($cachedModuleFields) {
				foreach ($cachedModuleFields as $fieldinfo) {
					$fieldLabel = $fieldinfo['fieldlabel'];
					// NOTE: We should not translate the label to enable field diff based on it down
					$fieldName                   = $fieldinfo['fieldname'];
					$tableName                   = str_replace ("vtiger_", "", $fieldinfo['tablename']);
					$hiddenFields[ $fieldLabel ] = array ($tableName => $fieldName);
				}
			}

			if (isset($this->list_fields)) {
				$this->list_fields = array_diff_assoc ($this->list_fields, $hiddenFields);
			}

			if (isset($this->search_fields)) {
				$this->search_fields = array_diff_assoc ($this->search_fields, $hiddenFields);
			}

			// To avoid re-initializing everytime.
			$this->__inactive_fields_filtered = true;
		}

		/**
		 * @param $uitypes
		 * @param $value
		 *
		 * @return string
		 */
		public function buildSearchQueryForFieldTypes ($uitypes, $value) {
			if (!is_array ($uitypes)) {
				$uitypes = array ($uitypes);
			}
			$module = get_class ($this);

			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module);
			if ($cachedModuleFields === false) {
				getColumnFields ($module); // This API will initialize the cache as well
				// We will succeed now due to above function call
				$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module ($module);
			}

			$lookuptables  = array ();
			$lookupcolumns = array ();
			foreach ($cachedModuleFields as $fieldinfo) {
				if (in_array ($fieldinfo['uitype'], $uitypes)) {
					$lookuptables[]  = $fieldinfo['tablename'];
					$lookupcolumns[] = $fieldinfo['columnname'];
				}
			}

			$entityfields      = getEntityField ($module);
			$querycolumnnames  = implode (',', $lookupcolumns);
			$entitycolumnnames = $entityfields['fieldname'];
			$query             = "select crmid as id, $querycolumnnames, $entitycolumnnames as name ";
			$query .= " FROM $this->table_name ";
			$query .= " INNER JOIN vtiger_crmentity ON $this->table_name.$this->table_index = vtiger_crmentity.crmid AND deleted = 0 ";

			//remove the base table
			$LookupTable = array_unique ($lookuptables);
			$indexes     = array_keys ($LookupTable, $this->table_name);
			if (!empty($indexes)) {
				foreach ($indexes as $index) {
					unset($LookupTable[ $index ]);
				}
			}
			foreach ($LookupTable as $tablename) {
				$query .= " INNER JOIN $tablename
						on $this->table_name.$this->table_index = $tablename." . $this->tab_name_index[ $tablename ];
			}
			if (!empty($lookupcolumns)) {
				$query .= " WHERE ";
				$i           = 0;
				$columnCount = count ($lookupcolumns);
				foreach ($lookupcolumns as $columnname) {
					if (!empty($columnname)) {
						if ($i == 0 || $i == ($columnCount)) {
							$query .= sprintf ("%s = '%s'", $columnname, $value);
						} else {
							$query .= sprintf (" OR %s = '%s'", $columnname, $value);
						}
						$i++;
					}
				}
			}
			return $query;
		}

		/**
		 *
		 * @param string $tableName
		 *
		 * @return string
		 */
		public function getJoinClause ($tableName) {
			if (strripos ($tableName, 'rel') === (strlen ($tableName) - 3)) {
				return 'LEFT JOIN';
			} else {
				return 'INNER JOIN';
			}
		}

		/**
		 * @param string $module
		 * @param Users $user
		 * @param string $parentRole
		 * @param array $userGroups
		 *
		 * @return string
		 */
		public function getNonAdminAccessQuery ($module, $user, $parentRole, $userGroups) {
			$query = $this->getNonAdminUserAccessQuery ($user, $parentRole, $userGroups);
			if (!empty($module)) {
				$moduleAccessQuery = $this->getNonAdminModuleAccessQuery ($module, $user);
				if (!empty($moduleAccessQuery)) {
					$query .= " UNION $moduleAccessQuery";
				}
			}
			return $query;
		}

		/**
		 * @param Users $user
		 * @param string $parentRole
		 * @param array $userGroups
		 *
		 * @return string
		 */
		public function getNonAdminUserAccessQuery ($user, $parentRole, $userGroups) {
			$query = "(SELECT $user->id as id) UNION (SELECT vtiger_user2role.userid AS userid FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole like '$parentRole::%')";
			if (count ($userGroups) > 0) {
				$query .= " UNION (SELECT groupid FROM vtiger_groups where groupid in (" . implode (",", $userGroups) . "))";
			}
			return $query;
		}

		/**
		 * @param string $module
		 * @param Users $user
		 *
		 * @return string
		 */
		public function getNonAdminModuleAccessQuery ($module, $user) {
			$local_user = clone $user;
			require ('user_privileges/sharing_privileges.php');
			$tabId                   = getTabid ($module);
			$sharingRuleInfoVariable = $module . '_share_read_permission';
			$sharingRuleInfo         = $$sharingRuleInfoVariable;
			$sharedTabId             = null;
			$query                   = '';
			if (!empty($sharingRuleInfo) && (count ($sharingRuleInfo['ROLE']) > 0 ||
											 count ($sharingRuleInfo['GROUP']) > 0)
			) {
				$query = " (SELECT shareduserid FROM vtiger_tmp_read_user_sharing_per " .
						 "WHERE userid=$user->id AND tabid=$tabId) UNION (SELECT " .
						 "vtiger_tmp_read_group_sharing_per.sharedgroupid FROM " .
						 "vtiger_tmp_read_group_sharing_per WHERE userid=$user->id AND tabid=$tabId)";
			}
			return $query;
		}

		/**
		 * @param String $module - module name for which query needs to be generated.
		 * @param Users $user - user for which query needs to be generated.
		 *
		 * @param string $scope
		 *
		 * @return String Access control Query for the user.
		 */
		public function getNonAdminAccessControlQuery ($module, $user, $scope = '') {
			if ($user) {
				$local_user = clone $user;
				require ('user_privileges/user_privileges.php');
				require ('user_privileges/sharing_privileges.php');
			}

			$query = ' ';
			$tabId = getTabid ($module);
			/**
			 * @var boolean $is_admin
			 * @var array $profileGlobalPermission
			 * @var array $defaultOrgSharingPermission
			 * @var string $current_user_parent_role_seq
			 * @var array $current_user_groups
			 */
			if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[ $tabId ] == 3) {
				$tableName               = 'vt_tmp_u' . $user->id;
				$sharingRuleInfoVariable = $module . '_share_read_permission';
				$sharingRuleInfo         = $$sharingRuleInfoVariable;
				$sharedTabId             = null;
				if ((!empty ($sharingRuleInfo)) && ((count ($sharingRuleInfo ['ROLE']) > 0) || (count ($sharingRuleInfo ['GROUP']) > 0))) {
					$tableName   = $tableName . '_t' . $tabId;
					$sharedTabId = $tabId;
					$additionalIds = array ();
					if (count ($sharingRuleInfo ['ROLE']) > 0) {
						foreach ($sharingRuleInfo ['ROLE'] as $userIds) {
							$additionalIds = array_merge ($additionalIds, $userIds);
						}
					}
					if (count ($sharingRuleInfo ['GROUP']) > 0) {
						foreach ($sharingRuleInfo ['GROUP'] as $groupIds) {
							$additionalIds = array_merge ($additionalIds, $groupIds);
						}
					}
				} else if ($module == 'Calendar' || !empty($scope)) {
					$tableName .= '_t' . $tabId;
				}
				$this->setupTemporaryTable ($tableName, $sharedTabId, $user, $current_user_parent_role_seq, $current_user_groups);
				if (!empty ($additionalIds)) {
					$db = PearDatabase::getInstance ();
					foreach ($additionalIds as $additionalId) {
						$db->pquery ("INSERT IGNORE INTO {$tableName} (id) VALUES (?)", array ($additionalId));
					}
				}
				$query = " INNER JOIN {$tableName} {$tableName}{$scope} ON ({$tableName}{$scope}.id=vtiger_crmentity{$scope}.smownerid) ";
			}
			return $query;
		}

		/**
		 * @param string $query
		 *
		 * @return string
		 */
		public function listQueryNonAdminChange ($query) {
			//make the module base table as left hand side table for the joins,
			//as mysql query optimizer puts crmentity on the left side and considerably slow down
			$query = preg_replace ('/\s+/', ' ', $query);
			if (strripos ($query, ' WHERE ') !== false) {
				$query = str_ireplace (' where ', " WHERE $this->table_name.$this->table_index > 0  AND ", $query);
			}
			return $query;
		}

		/**
		 * Function to get the relation tables for related modules
		 *
		 * @param string $secmodule secondary module name
		 *
		 * @return array the array with table names and fieldnames storing relations between module and this module
		 */
		public function setRelationTables ($secmodule) {
			$rel_tables = array (
				"Documents" => array (
					"vtiger_senotesrel" => array ("crmid", "notesid"),
					$this->table_name   => $this->table_index,
				),
			);
			return $rel_tables[ $secmodule ];
		}

		/**
		 * Function to clear the fields which needs to be saved only once during the Save of the record
		 * For eg: Comments of HelpDesk should be saved only once during one save of a Trouble Ticket
		 */
		public function clearSingletonSaveFields () {
			return;
		}

		/**
		 * Function to track when a new record is linked to a given record
		 *
		 * @param $module
		 * @param $crmid
		 * @param $with_module
		 * @param $with_crmid
		 */
		public function trackLinkedInfo ($module, $crmid, $with_module, $with_crmid) {
			global $current_user;
			$adb         = PearDatabase::getInstance ();
			$currentTime = date ('Y-m-d H:i:s');

			$adb->pquery ('UPDATE vtiger_crmentity SET modifiedtime = ?, modifiedby = ? WHERE crmid = ?', array ($currentTime, $current_user->id, $crmid));
		}

		/**
		 * Function to get sort order
		 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
		 */
		public function getSortOrder () {
			global $log, $currentModule;
			$log->debug ("Entering getSortOrder() method ...");
			if (isset($_REQUEST['sorder'])) {
				$sorder = $this->db->sql_escape_string ($_REQUEST['sorder']);
			} else {
				$sorder = (($_SESSION[ $currentModule . '_Sort_Order' ] != '') ? ($_SESSION[ $currentModule . '_Sort_Order' ]) : ($this->default_sort_order));
			}
			$log->debug ("Exiting getSortOrder() method ...");
			return $sorder;
		}

		/**
		 * Function to get order by
		 * return string  $order_by    - fieldname(eg: 'accountname')
		 */
		public function getOrderBy () {
			global $log, $currentModule;
			$log->debug ("Entering getOrderBy() method ...");

			$use_default_order_by = '';
			if (PerformancePrefs::getBoolean ('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_order_by = $this->default_order_by;
			}

			if (isset($_REQUEST['order_by'])) {
				$order_by = $this->db->sql_escape_string ($_REQUEST['order_by']);
			} else {
				$order_by = (($_SESSION[ $currentModule . '_Order_By' ] != '') ? ($_SESSION[ $currentModule . '_Order_By' ]) : ($use_default_order_by));
			}
			$log->debug ("Exiting getOrderBy method ...");
			return $order_by;
		}

		/**
		 * Function to Listview buttons
		 *
		 * @param array $app_strings
		 * @param array $mod_strings
		 *
		 * @return array
		 */
		public function getListButtons ($app_strings, $mod_strings = null) {
			global $currentModule;
			$list_buttons = array ();

			if (isPermitted ($currentModule, 'Delete', '') == 'yes') {
				$list_buttons['del'] = $app_strings['LBL_MASS_DELETE'];
			}
			if (isPermitted ($currentModule, 'EditView', '') == 'yes') {
				$list_buttons['mass_edit'] = $app_strings['LBL_MASS_EDIT'];
			}
			return $list_buttons;
		}

		/**
		 * Function to track when a record is unlinked to a given record
		 *
		 * @param $module
		 * @param $crmid
		 * @param $with_module
		 * @param $with_crmid
		 */
		public function trackUnLinkedInfo ($module, $crmid, $with_module, $with_crmid) {
			global $current_user;
			$adb         = PearDatabase::getInstance ();
			$currentTime = date ('Y-m-d H:i:s');

			$adb->pquery ('UPDATE vtiger_crmentity SET modifiedtime = ?, modifiedby = ? WHERE crmid = ?', array ($currentTime, $current_user->id, $crmid));
		}

		/**
		 * @param $id
		 *
		 * @throws Exception
		 */
		public function deleteFile ($id) {
			$sql1 = 'SELECT attachmentsid FROM vtiger_attachments WHERE attachmentsid = ?';
			$res1 = $this->db->pquery ($sql1, array ($id));
			if ($this->db->num_rows ($res1) > 0) {
				$attachmentId = $this->db->query_result ($res1, 0, 'attachmentsid');

				$sql4 = 'DELETE FROM vtiger_attachments WHERE attachmentsid=?';
				$this->db->pquery ($sql4, array ($attachmentId));
			}
		}

		/**
		 * @param $tabid
		 *
		 * @return string
		 */
		public function vendorViewRestriction ($tabid) {
			global $adb, $current_user;
			$sql = '';

			$contactid   = $current_user->getContactId ();
			$vendorfield = '';

			if (isset($this->column_fields ['vendorid'])) {
				$vendorfield = 'vendorid';
			} else if (isset($this->column_fields ['vendor_id'])) {
				$vendorfield = 'vendor_id';
			}

			if (!empty($vendorfield)) {
				list($vendorfield) = $adb->fetch_row ($adb->pquery ("SELECT columnname FROM vtiger_field WHERE tabid=? AND fieldname=?", array ($tabid, $vendorfield)));
			}

			if (empty($vendorfield)) {
				return $sql;
			}

			$query  = "SELECT relcrmid FROM vtiger_crmentityrel INNER JOIN vtiger_crmentity crm1 ON (relcrmid=crm1.crmid AND deleted=0 AND setype='Vendors') WHERE vtiger_crmentityrel.crmid=? UNION SELECT vtiger_crmentityrel.crmid FROM vtiger_crmentityrel INNER JOIN vtiger_crmentity crm1 ON (vtiger_crmentityrel.crmid=crm1.crmid AND deleted=0 AND setype='Vendors') WHERE vtiger_crmentityrel.relcrmid=?";
			$result = $adb->pquery ($query, array ($contactid, $contactid));

			list($vendorid) = $adb->fetch_row ($result);

			if ($vendorid) {
				$sql = " and $vendorfield=$vendorid ";
			}

			return $sql;
		}

		/**
		 * @param $id
		 * @param $module
		 *
		 * @throws Exception
		 */
		public function createSequence ($id, $module) {
			global $adb;

			$sql    = "SELECT prefix FROM vtiger_modentity_num WHERE semodule = ? AND crmid IS NULL OR LENGTH(crmid) = 0";
			$result = $adb->pquery ($sql, array ($module));

			$prefix = $adb->query_result ($result, 0, 'prefix');

			$sequenceid = $adb->getUniqueID ("vtiger_modentity_num");

			$sql = "INSERT INTO vtiger_modentity_num (num_id,semodule,prefix,start_id,cur_id,active,crmid) VALUES (?,?,?,1,1,1,?)";
			$adb->pquery ($sql, array ($sequenceid, $module, $prefix, $id));
		}

		/**
		 * @return array
		 */
		public function gridTables () {
			$tables = array ();
			global $adb;
			$module = get_class ($this);
			$result = $adb->pquery ("SELECT fieldname FROM vtiger_field INNER JOIN vtiger_tab USING(tabid) WHERE uitype=2202 AND vtiger_tab.name=?", array ($module));

			while ($result && list($tablename) = $adb->fetch_row ($result)) {
				$tables[] = "vtiger_$module" . "_$tablename";
			}

			return $tables;
		}

		/**
		 * Get list view query (send more WHERE clause condition if required)
		 *
		 * @param $moduleName
		 * @param string $additionalWhereClause
		 *
		 * @return string
		 * @throws Exception
		 */
		public function getListQuery ($moduleName, $additionalWhereClause = '') {
			return '';
		}

		public function getRelatedCalls ($id, $curTabId, $relTabId, $fieldName, $onlyquery = false, $relationId = false, $isCard = false) {
			$clientCell = preg_replace('/[^0-9]/', '', $this->column_fields[$fieldName]);
			$relatedListData['header'] = array (
				'<div class="title-overflow"><a href="#" class="listFormHeaderLinks">Fecha</a</div>',
				'<div class="title-overflow"><a href="#" class="listFormHeaderLinks">Hora</a</div>',
				'<div class="title-overflow"><a href="#" class="listFormHeaderLinks">Origen</a</div>',
				'<div class="title-overflow"><a href="#" class="listFormHeaderLinks">Destino</a</div>',
				'<div class="title-overflow"><a href="#" class="listFormHeaderLinks">Duración</a</div>',
				'<div class="title-overflow"><a href="#" class="listFormHeaderLinks">Mensaje</a</div>',
			);
			if (!is_numeric ($clientCell)) {
				return $relatedListData;
			}
			$where = array(
				'OR' => array(
					'src = ' => "'{$clientCell}'",
					'dst = ' => "'{$clientCell}'",
				)
			);
			$objectIssabel     = PlatziIssabel::getInstance ($_SESSION ['plat']);
			$issabelMonitoring = $objectIssabel->fetchIssabelMonitoring ($where, 0);
			//$issabelMonitoring = null;
			if (empty ($issabelMonitoring)) {
				return $relatedListData;
			}
			foreach ($issabelMonitoring as $monitoring) {
				$recording = '';
				if (!empty ($monitoring->getMessage ())) {
					$recording = "<a data-width='650' data-toggle='lightbox' data-parent='' data-gallery='remoteload' data-title=''";
					$recording .= "href='index.php?module=platzi_issabel&action=AjaxPlatziIssabelUtils&function=AUDIO_MONITORING&uniqueid={$monitoring->getUniqueId()}&Ajax=true'";
					$recording .= "title='Reproducir audio de la grabación'><i class='fa fa-bullhorn' aria-hidden='true'></i></a>";
				}
						
				$relatedListData['entries'][ $monitoring->getUniqueId() ]['records'] = array (
					'Fecha'    => "<a href='index.php?module=platzi_issabel&parenttab=&action=DetailView&record=&uniqueid={$monitoring->getUniqueId()}' target='_blanck'>{$monitoring->getDate ()}</a>",
					'Hora'     => $monitoring->getTime(),
					'Origen'   => $monitoring->getOrigin(),
					'Destino'  => $monitoring->getDestination (),
					'Duración' => $monitoring->getDuration (),
					'Mensaje'  => $recording,
				);
			}
			$relatedListData['delete_btn'] = false;
			return $relatedListData;
		}
		
		
	}

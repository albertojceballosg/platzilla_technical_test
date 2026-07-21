<?php
	/*+********************************************************************************
	 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
	 * ("License"); You may not use this file except in compliance with the License
	 * The Original Code is:  vtiger CRM Open Source
	 * The Initial Developer of the Original Code is vtiger.
	 * Portions created by vtiger are Copyright (C) vtiger.
	 * All Rights Reserved.
	 ********************************************************************************/

	include_once ('config.php');
	require_once ('include/logging.php');
	require_once ('include/database/PearDatabase.php');
	require_once ('data/CRMEntity.php');
	require_once ('include/upload_file.php');

// Note is used to store customer information.
	class Documents extends CRMEntity {

		var $log;
		var $db;
		var $table_name            = "vtiger_notes";
		var $table_index           = 'notesid';
		var $default_note_name_dom = array ('Meeting vtiger_notes', 'Reminder');

		var $tab_name       = Array ('vtiger_crmentity', 'vtiger_notes');
		var $tab_name_index = Array ('vtiger_crmentity' => 'crmid', 'vtiger_notes' => 'notesid', 'vtiger_senotesrel' => 'notesid');

		var $column_fields = Array ();

		var $sortby_fields = Array ('title', 'modifiedtime', 'filename', 'createdtime', 'lastname', 'filedownloadcount', 'smownerid');

		// This is used to retrieve related vtiger_fields from form posts.
		var $additional_column_fields = Array ('', '', '', '');

		// This is the list of vtiger_fields that are in the lists.
		var $list_fields      = Array (
			'Title'         => Array ('notes' => 'title'),
			'File Name'     => Array ('notes' => 'filename'),
			'Modified Time' => Array ('crmentity' => 'modifiedtime'),
			'Assigned To'   => Array ('crmentity' => 'smownerid'),
			'Folder Name'   => Array ('attachmentsfolder' => 'foldername'),
		);
		var $list_fields_name = Array (
			'Title'         => 'notes_title',
			'File Name'     => 'filename',
			'Modified Time' => 'modifiedtime',
			'Assigned To'   => 'assigned_user_id',
			'Folder Name'   => 'folderid',
		);

		var $search_fields = Array (
			'Title'       => Array ('notes' => 'notes_title'),
			'File Name'   => Array ('notes' => 'filename'),
			'Assigned To' => Array ('crmentity' => 'smownerid'),
			'Folder Name' => Array ('attachmentsfolder' => 'foldername'),
		);

		var $search_fields_name = Array (
			'Title'       => 'notes_title',
			'File Name'   => 'filename',
			'Assigned To' => 'assigned_user_id',
			'Folder Name' => 'folderid',
		);
		var $list_link_field    = 'notes_title';
		var $old_filename       = '';
		//var $groupTable = Array('vtiger_notegrouprelation','notesid');

		var $mandatory_fields = Array ('notes_title', 'createdtime', 'modifiedtime', 'filename', 'filesize', 'filetype', 'filedownloadcount', 'assigned_user_id');

		//Added these variables which are used as default order by and sortorder in ListView
		var $default_order_by   = 'title';
		var $default_sort_order = 'ASC';

		function Documents () {
			$this->log = LoggerManager::getLogger ('notes');
			$this->log->debug ("Entering Documents() method ...");
			$this->db            = PearDatabase::getInstance ();
			$this->column_fields = getColumnFields ('Documents');
			$this->log->debug ("Exiting Documents method ...");
		}

		function save_module ($module) {
			global $log, $adb, $upload_badext;
			$insertion_mode = $this->mode;
			if (isset($this->parentid) && $this->parentid != '') {
				$relid = $this->parentid;
			}
			//inserting into vtiger_senotesrel
			if (isset($relid) && $relid != '') {
				$this->insertintonotesrel ($relid, $this->id);
			}
			$filetype_fieldname = $this->getFileTypeFieldName ();
			$filename_fieldname = $this->getFile_FieldName ();
			if ($this->column_fields[ $filetype_fieldname ] == 'I') {
				if (isset($_FILES[ $filename_fieldname ]['name']) && $_FILES[ $filename_fieldname ]['name'] != '') {
					$errCode = $_FILES[ $filename_fieldname ]['error'];
					if ($errCode == 0) {
						foreach ($_FILES as $fileindex => $files) {
							if ($files['name'] != '' && $files['size'] > 0) {
								$filename         = $_FILES[ $filename_fieldname ]['name'];
								$filename         = from_html (preg_replace ('/\s+/', '_', $filename));
								$filetype         = $_FILES[ $filename_fieldname ]['type'];
								$filesize         = $_FILES[ $filename_fieldname ]['size'];
								$filelocationtype = 'I';
								$binFile          = sanitizeUploadFileName ($filename, $upload_badext);
								$filename         = ltrim (basename (" " . $binFile)); //allowed filename like UTF-8 characters
							}
						}
					}
				} elseif ($this->mode == 'edit') {
					$fileres = $adb->pquery ("SELECT filetype, filesize,filename,filedownloadcount,filelocationtype FROM vtiger_notes WHERE notesid=?", array ($this->id));
					if ($adb->num_rows ($fileres) > 0) {
						$filename          = $adb->query_result ($fileres, 0, 'filename');
						$filetype          = $adb->query_result ($fileres, 0, 'filetype');
						$filesize          = $adb->query_result ($fileres, 0, 'filesize');
						$filedownloadcount = $adb->query_result ($fileres, 0, 'filedownloadcount');
						$filelocationtype  = $adb->query_result ($fileres, 0, 'filelocationtype');
					}
				} elseif ($this->column_fields[ $filename_fieldname ]) {
					$filename          = $this->column_fields[ $filename_fieldname ];
					$filesize          = $this->column_fields['filesize'];
					$filetype          = $this->column_fields['filetype'];
					$filelocationtype  = $this->column_fields[ $filetype_fieldname ];
					$filedownloadcount = 0;
				} else {
					$filelocationtype  = 'I';
					$filetype          = '';
					$filesize          = 0;
					$filedownloadcount = null;
				}
			} else if ($this->column_fields[ $filetype_fieldname ] == 'E') {
				$filelocationtype = 'E';
				$filename         = $this->column_fields[ $filename_fieldname ];
				// If filename does not has the protocol prefix, default it to http://
				// Protocol prefix could be like (https://, smb://, file://, \\, smb:\\,...)
				if (!empty($filename) && !preg_match ('/^\w{1,5}:\/\/|^\w{0,3}:?\\\\\\\\/', trim ($filename), $match)) {
					$filename = "http://$filename";
				}
				$filetype          = '';
				$filesize          = 0;
				$filedownloadcount = null;
			}
			$query = "UPDATE vtiger_notes SET filename = ? ,filesize = ?, filetype = ? , filelocationtype = ? , filedownloadcount = ? WHERE notesid = ?";
			$re    = $adb->pquery ($query, array ($filename, $filesize, $filetype, $filelocationtype, $filedownloadcount, $this->id));
			//Inserting into attachments table
			if ($filelocationtype == 'I') {
				$this->insertIntoAttachment ($this->id, 'Documents');
			} else {
				$query   = "DELETE FROM vtiger_seattachmentsrel WHERE crmid = ?";
				$qparams = array ($this->id);
				$adb->pquery ($query, $qparams);
			}
		}

		/**
		 *      This function is used to add the vtiger_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
		 *
		 * @param int $id - entity id to which the vtiger_files to be uploaded
		 * @param string $module - the current module name
		 */
		function insertIntoAttachment ($id, $module) {
			global $log, $adb;
			$log->debug ("Entering into insertIntoAttachment($id,$module) method.");

			$file_saved = false;

			foreach ($_FILES as $fileindex => $files) {
				if ($files['name'] != '' && $files['size'] > 0) {
					$files['original_name'] = vtlib_purify ($_REQUEST[ $fileindex . '_hidden' ]);
					$file_saved             = $this->uploadAndSaveFile ($id, $module, $files);
				}
			}

			$log->debug ("Exiting from insertIntoAttachment($id,$module) method.");
		}

		/**    Function used to get the sort order for Documents listview
		 * @return string  $sorder - first check the $_REQUEST['sorder'] if request value is empty then check in the $_SESSION['NOTES_SORT_ORDER'] if this session value is empty then default sort order will be returned.
		 */
		function getSortOrder () {
			global $log;
			$log->debug ("Entering getSortOrder() method ...");
			if (isset($_REQUEST['sorder'])) {
				$sorder = $this->db->sql_escape_string ($_REQUEST['sorder']);
			} else {
				$sorder = (($_SESSION['NOTES_SORT_ORDER'] != '') ? ($_SESSION['NOTES_SORT_ORDER']) : ($this->default_sort_order));
			}
			$log->debug ("Exiting getSortOrder() method ...");
			return $sorder;
		}

		/**     Function used to get the order by value for Documents listview
		 * @return string  $order_by  - first check the $_REQUEST['order_by'] if request value is empty then check in the $_SESSION['NOTES_ORDER_BY'] if this session value is empty then default order by will be returned.
		 */
		function getOrderBy () {
			global $log;
			$log->debug ("Entering getOrderBy() method ...");

			$use_default_order_by = '';
			if (PerformancePrefs::getBoolean ('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_order_by = $this->default_order_by;
			}

			if (isset($_REQUEST['order_by'])) {
				$order_by = $this->db->sql_escape_string ($_REQUEST['order_by']);
			} else {
				$order_by = (($_SESSION['NOTES_ORDER_BY'] != '') ? ($_SESSION['NOTES_ORDER_BY']) : ($use_default_order_by));
			}
			$log->debug ("Exiting getOrderBy method ...");
			return $order_by;
		}

		/**
		 * Function used to get the sort order for Documents listview
		 * @return String $sorder - sort order for a given folder.
		 */
		function getSortOrderForFolder ($folderId) {
			if (isset($_REQUEST['sorder']) && $_REQUEST['folderid'] == $folderId) {
				$sorder = $this->db->sql_escape_string ($_REQUEST['sorder']);
			} elseif (is_array ($_SESSION['NOTES_FOLDER_SORT_ORDER']) &&
					  !empty($_SESSION['NOTES_FOLDER_SORT_ORDER'][ $folderId ])
			) {
				$sorder = $_SESSION['NOTES_FOLDER_SORT_ORDER'][ $folderId ];
			} else {
				$sorder = $this->default_sort_order;
			}
			return $sorder;
		}

		/**
		 * Function used to get the order by value for Documents listview
		 * @return String order by column for a given folder.
		 */
		function getOrderByForFolder ($folderId) {
			$use_default_order_by = '';
			if (PerformancePrefs::getBoolean ('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_order_by = $this->default_order_by;
			}
			if (isset($_REQUEST['order_by']) && $_REQUEST['folderid'] == $folderId) {
				$order_by = $this->db->sql_escape_string ($_REQUEST['order_by']);
			} elseif (is_array ($_SESSION['NOTES_FOLDER_ORDER_BY']) &&
					  !empty($_SESSION['NOTES_FOLDER_ORDER_BY'][ $folderId ])
			) {
				$order_by = $_SESSION['NOTES_FOLDER_ORDER_BY'][ $folderId ];
			} else {
				$order_by = ($use_default_order_by);
			}
			return $order_by;
		}

		/** Function to export the notes in CSV Format
		 *
		 * @param reference variable - where condition is passed when the query is executed
		 * Returns Export Documents Query.
		 */
		function create_export_query ($where) {
			global $log, $current_user;
			$log->debug ("Entering create_export_query(" . $where . ") method ...");

			include ("include/utils/ExportUtils.php");
			//To get the Permitted fields query and the permitted fields list
			$sql         = getPermittedFieldsQuery ("Documents", "detail_view");
			$fields_list = getFieldsListFromQuery ($sql);

			$userNameSql = getSqlForNameInDisplayFormat (array (
				'first_name'                               =>
					'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name',
			), 'Users');
			$query       = "SELECT $fields_list, case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name" .
						   " FROM vtiger_notes
				inner join vtiger_crmentity
					on vtiger_crmentity.crmid=vtiger_notes.notesid
				LEFT JOIN vtiger_attachmentsfolder on vtiger_notes.folderid=vtiger_attachmentsfolder.folderid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id " .
						   " LEFT JOIN vtiger_groups ON vtiger_crmentity.smownerid=vtiger_groups.groupid ";
			$query .= getNonAdminAccessControlQuery ('Documents', $current_user);
			$where_auto = " vtiger_crmentity.deleted=0";
			if ($where != "") {
				$query .= "  WHERE ($where) AND " . $where_auto;
			} else {
				$query .= "  WHERE " . $where_auto;
			}

			$log->debug ("Exiting create_export_query method ...");
			return $query;
		}

		function del_create_def_folder ($query) {
			global $adb;
			$dbQuery   = $query . " and vtiger_attachmentsfolder.folderid = 0";
			$dbresult  = $adb->pquery ($dbQuery, array ());
			$noofnotes = $adb->num_rows ($dbresult);
			if ($noofnotes > 0) {
				$folderQuery    = "SELECT folderid FROM vtiger_attachmentsfolder";
				$folderresult   = $adb->pquery ($folderQuery, array ());
				$noofdeffolders = $adb->num_rows ($folderresult);

				if ($noofdeffolders == 0) {
					$insertQuery  = "INSERT INTO vtiger_attachmentsfolder VALUES (0,'Default','Contains all attachments for which a folder is not set',1,0)";
					$insertresult = $adb->pquery ($insertQuery, array ());
				}
			}
		}

		function insertintonotesrel ($relid, $id) {
			global $adb;
			$dbQuery  = "INSERT INTO vtiger_senotesrel VALUES ( ?, ? )";
			$dbresult = $adb->pquery ($dbQuery, array ($relid, $id));
		}

		/*function save_related_module($module, $crmid, $with_module, $with_crmid){
		}*/

		/*
		 * Function to get the primary query part of a report
		 * @param - $module Primary module name
		 * returns the query string formed on fetching the related data for report for primary module
		 */
		function generateReportsQuery ($module) {
			$moduletable = $this->table_name;
			$moduleindex = $this->tab_name_index[ $moduletable ];
			$query       = "from $moduletable
			        inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex
			        inner join vtiger_attachmentsfolder on vtiger_attachmentsfolder.folderid=$moduletable.folderid
					left join vtiger_groups as vtiger_groups" . $module . " on vtiger_groups" . $module . ".groupid = vtiger_crmentity.smownerid
		            left join vtiger_users as vtiger_users" . $module . " on vtiger_users" . $module . ".id = vtiger_crmentity.smownerid
					left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
		            left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_lastModifiedBy" . $module . " on vtiger_lastModifiedBy" . $module . ".id = vtiger_crmentity.modifiedby ";
			return $query;
		}

		/*
		 * Function to get the secondary query part of a report
		 * @param - $module primary module name
		 * @param - $secmodule secondary module name
		 * returns the query string formed on fetching the related data for report for secondary module
		 */
		function generateReportsSecQuery ($module, $secmodule) {
			$query = $this->getRelationQuery ($module, $secmodule, "vtiger_notes", "notesid");
			$query .= " left join vtiger_crmentity as vtiger_crmentityDocuments on vtiger_crmentityDocuments.crmid=vtiger_notes.notesid and vtiger_crmentityDocuments.deleted=0
		        left join vtiger_attachmentsfolder on vtiger_attachmentsfolder.folderid=vtiger_notes.folderid
				left join vtiger_groups as vtiger_groupsDocuments on vtiger_groupsDocuments.groupid = vtiger_crmentityDocuments.smownerid
				left join vtiger_users as vtiger_usersDocuments on vtiger_usersDocuments.id = vtiger_crmentityDocuments.smownerid
                left join vtiger_users as vtiger_lastModifiedByDocuments on vtiger_lastModifiedByDocuments.id = vtiger_crmentityDocuments.modifiedby ";

			return $query;
		}

		/*
		 * Function to get the relation tables for related modules
		 * @param - $secmodule secondary module name
		 * returns the array with table names and fieldnames storing relations between module and this module
		 */
		function setRelationTables ($secmodule) {
			$rel_tables = array ();
			return $rel_tables[ $secmodule ];
		}

		// Function to unlink all the dependent entities of the given Entity by Id
		function unlinkDependencies ($module, $id) {
			global $log;
			/*//Backup Documents Related Records
			$se_q = 'SELECT crmid FROM vtiger_senotesrel WHERE notesid = ?';
			$se_res = $this->db->pquery($se_q, array($id));
			if ($this->db->num_rows($se_res) > 0) {
				for($k=0;$k < $this->db->num_rows($se_res);$k++)
				{
					$se_id = $this->db->query_result($se_res,$k,"crmid");
					$params = array($id, RB_RECORD_DELETED, 'vtiger_senotesrel', 'notesid', 'crmid', $se_id);
					$this->db->pquery('INSERT INTO vtiger_relatedlists_rb VALUES (?,?,?,?,?,?)', $params);
				}
			}
			$sql = 'DELETE FROM vtiger_senotesrel WHERE notesid = ?';
			$this->db->pquery($sql, array($id));*/

			parent::unlinkDependencies ($module, $id);
		}

		// Function to unlink an entity with given Id from another entity
		function unlinkRelationship ($id, $return_module, $return_id) {
			global $log;
			if (empty($return_module) || empty($return_id)) {
				return;
			}

			$sql = 'DELETE FROM vtiger_senotesrel WHERE notesid = ? AND crmid = ?';
			$this->db->pquery ($sql, array ($id, $return_id));

			$sql    = 'DELETE FROM vtiger_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array ($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery ($sql, $params);
		}

// Function to get fieldname for uitype 27 assuming that documents have only one file type field

		function getFileTypeFieldName () {
			global $adb, $log;
			$query           = 'SELECT fieldname FROM vtiger_field WHERE tabid = ? AND uitype = ?';
			$tabid           = getTabid ('Documents');
			$filetype_uitype = 27;
			$res             = $adb->pquery ($query, array ($tabid, $filetype_uitype));
			$fieldname       = null;
			if (isset($res)) {
				$rowCount = $adb->num_rows ($res);
				if ($rowCount > 0) {
					$fieldname = $adb->query_result ($res, 0, 'fieldname');
				}
			}
			return $fieldname;
		}

//	Function to get fieldname for uitype 28 assuming that doc has only one file upload type

		function getFile_FieldName () {
			global $adb, $log;
			$query           = 'SELECT fieldname FROM vtiger_field WHERE tabid = ? AND uitype = ?';
			$tabid           = getTabid ('Documents');
			$filename_uitype = 28;
			$res             = $adb->pquery ($query, array ($tabid, $filename_uitype));
			$fieldname       = null;
			if (isset($res)) {
				$rowCount = $adb->num_rows ($res);
				if ($rowCount > 0) {
					$fieldname = $adb->query_result ($res, 0, 'fieldname');
				}
			}
			return $fieldname;
		}

		/**
		 * Check the existence of folder by folderid
		 */
		function isFolderPresent ($folderid) {
			global $adb;
			$result = $adb->pquery ("SELECT folderid FROM vtiger_attachmentsfolder WHERE folderid = ?", array ($folderid));
			if (!empty($result) && $adb->num_rows ($result) > 0) {
				return true;
			}
			return false;
		}

		/**
		 * Customizing the restore procedure.
		 */
		function restore ($modulename, $id) {
			parent::restore ($modulename, $id);

			global $adb;
			$fresult = $adb->pquery ("SELECT folderid FROM vtiger_notes WHERE notesid = ?", array ($id));
			if (!empty($fresult) && $adb->num_rows ($fresult)) {
				$folderid = $adb->query_result ($fresult, 0, 'folderid');
				if (!$this->isFolderPresent ($folderid)) {
					// Re-link to default folder
					$adb->pquery ("UPDATE vtiger_notes SET folderid = 1 WHERE notesid = ?", array ($id));
				}
			}
		}

		function getQueryByModuleField ($module, $fieldname, $srcrecord, $query) {
			if ($module == "MailManager") {
				$tempQuery = split ('WHERE', $query);
				if (!empty($tempQuery[1])) {
					$where = " vtiger_notes.filelocationtype = 'I' AND vtiger_notes.filename != '' AND vtiger_notes.filestatus != 0 AND ";
					$query = $tempQuery[0] . ' WHERE ' . $where . $tempQuery[1];
				} else {
					$query = $tempQuery[0] . ' WHERE ' . $tempQuery;
				}
				return $query;
			}
		}

		function getFolders ($focus, $controller, $request_folderid) {

			global $adb, $currentModule, $list_max_entries_per_page;

			$dbQuery = "SELECT * FROM vtiger_attachmentsfolder";
			if ($request_folderid != '') {
				//$dbQuery.=" where folderid=".$request_folderid;
				//$dbQuery.=" where ( parentfolder = ".$request_folderid." or folderid =".$request_folderid." )";
				$dbQuery .= " where ( parentfolder = " . $request_folderid . " )";
			} else {
				$dbQuery .= " where parentfolder = 0";
			}
			$result       = $adb->pquery ($dbQuery, array ());
			$foldercount  = $adb->num_rows ($result);
			$folders      = Array ();
			$emptyfolders = Array ();
			if ($foldercount > 0) {
				for ($i = 0; $i < $foldercount; $i++) {
					$query         = '';
					$displayFolder = '';
					$query         = $focus->query;
					$list_query    = '';
					$list_query    = $focus->query;
					$folder_id     = $adb->query_result ($result, $i, "folderid");
					$query .= " and vtiger_notes.folderid = $folder_id";
					$sorder = $focus->getSortOrderForFolder ($folder_id);
					if (!is_array ($_SESSION['NOTES_FOLDER_SORT_ORDER'])) {
						$_SESSION['NOTES_FOLDER_SORT_ORDER'] = array ();
					}
					$_SESSION['NOTES_FOLDER_SORT_ORDER'][ $folder_id ] = $sorder;
					$order_by                                          = $focus->getOrderByForFolder ($folder_id);
					if (!is_array ($_SESSION['NOTES_FOLDER_ORDER_BY'])) {
						$_SESSION['NOTES_FOLDER_ORDER_BY'] = array ();
					}
					$_SESSION['NOTES_FOLDER_ORDER_BY'][ $folder_id ] = $order_by;
					if ($folder_id != $request_folderid) {
						$start[ $folder_id ] = 1;
					}

					if (isset($order_by) && $order_by != '') {
						$tablename = getTableNameForField ('Documents', $order_by);
						$tablename = (($tablename != '') ? ($tablename . ".") : '');

						if ($adb->dbType == "pgsql") {
							$query .= ' GROUP BY ' . $tablename . $order_by;
							$list_query .= ' GROUP BY ' . $tablename . $order_by;
							$focus->additional_query .= ' GROUP BY ' . $tablename . $order_by;
						}

						$query .= ' ORDER BY ' . $tablename . $order_by . ' ' . $sorder;
						$list_query .= ' ORDER BY ' . $tablename . $order_by . ' ' . $sorder;
						$focus->additional_query .= ' ORDER BY ' . $tablename . $order_by . ' ' . $sorder;
					}
					//Retreiving the no of rows
					$count_result = $adb->query (mkCountQuery ($query));
					$num_records  = $adb->query_result ($count_result, 0, "count");
					// if($num_records > 0){
					$displayFolder = true;
					// }
					//navigation start
					$max_entries_per_page = $list_max_entries_per_page;
					//Postgres 8 fixes
					if ($adb->dbType == "pgsql") {
						$list_query = fixPostgresQuery ($query, $log, 0);
					}

					if ($folder_id == $request_folderid) {
						$start[ $folder_id ] = 1;
						if (!empty($_REQUEST['start'])) {
							$start[ $folder_id ] = ListViewSession::getRequestStartPage ();
							if ($start[ $folder_id ] == 'last') {
								if ($num_records > 0) {
									$start[ $folder_id ] = ceil ($num_records / $max_entries_per_page);
								}
							}
							if (!is_numeric ($start[ $folder_id ])) {
								$start[ $folder_id ] = 1;
							}
						}
					}

					$navigation_array = VT_getSimpleNavigationValues ($start[ $folder_id ], $max_entries_per_page, $num_records);
					if ($folder_id == $request_folderid) {
						if (!is_array ($_SESSION['lvs'][ $currentModule ]['start'])) {
							$_SESSION['lvs'][ $currentModule ]['start'] = array ();
						}
						$_SESSION['lvs'][ $currentModule ]['start'][ $folder_id ] = $start[ $folder_id ];
					}
					$limit_start_rec = ($start[ $folder_id ] - 1) * $max_entries_per_page;

					if ($adb->dbType == "pgsql") {
						$list_result = $adb->pquery ($query . " OFFSET $limit_start_rec LIMIT $max_entries_per_page", array ());
					} else {
						$list_result = $adb->pquery ($query . " LIMIT $limit_start_rec, $max_entries_per_page", array ());
					}
					//navigation end

					$folder_details               = Array ();
					$folderid                     = $adb->query_result ($result, $i, "folderid");
					$folder_details['folderid']   = $folderid;
					$folder_details['foldername'] = $adb->query_result ($result, $i, "foldername");
					if ($folderid == $request_folderid) {
						$selectedfoldername = $folder_details['foldername'];
					}
					$foldername                             = $folder_details['foldername'];
					$folder_details['description']          = $adb->query_result ($result, $i, "description");
					$folder_url_string                      = $url_string . "&folderid=$folderid";
					$folder_details['header']               = $controller->getListViewHeader ($focus, $currentModule,
						$folder_url_string, $sorder, $order_by);
					$folder_files                           = $controller->getListViewEntries ($focus, $currentModule, $list_result, $navigation_array);
					$folder_details['entries']              = $folder_files;
					$folder_details['navigation']           = getTableHeaderSimpleNavigation ($navigation_array, $url_string, "Documents", $folder_id, $viewid);
					$folder_details['recordListRange']      = getRecordRangeMessage ($list_result, $limit_start_rec,
						$num_records);
					$folder_details['TotalrecordListRange'] = $num_records;
					if ($displayFolder == true) {
						$folders[ $foldername ] = $folder_details;
					} else {
						$emptyfolders[ $foldername ] = $folder_details;
					}
					if ($folderid == 1) {
						$default_folder_details = $folder_details;
					}
				}
				if (count ($folders) == 0) {
					$folders[ $default_folder_details['foldername'] ] = $default_folder_details;
				}
			}

			return $folders;
		}

		/**
		 * Get list view query (send more WHERE clause condition if required)
		 *
		 * @param string $moduleName
		 * @param string $additionalWhereClause
		 *
		 * @return string
		 */
		public function getListQuery ($moduleName, $additionalWhereClause = '') {
			global $current_user;

			$sql = "SELECT
						CASE WHEN vtiger_users.user_name NOT LIKE '' THEN TRIM(CONCAT(vtiger_users.first_name, ' ', vtiger_users.last_name)) ELSE vtiger_groups.groupname END AS user_name,
						vtiger_crmentity.crmid,
						vtiger_crmentity.modifiedtime,
						vtiger_crmentity.smownerid,
						vtiger_attachmentsfolder.*,
						vtiger_notes.*
					FROM
						vtiger_notes
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_notes.notesid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
						LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
						LEFT JOIN vtiger_attachmentsfolder ON vtiger_notes.folderid=vtiger_attachmentsfolder.folderid
						{$this->getNonAdminAccessControlQuery ($moduleName, $current_user)}
					WHERE
						vtiger_crmentity.deleted=0
						{$additionalWhereClause}";
			return trim (preg_replace ('/\s+/S', ' ', $sql));
		}

	}

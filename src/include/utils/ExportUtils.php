<?php
	/**
	 * function used to get the permitted blocks
	 *
	 * @param string $module - module name
	 * @param string $disp_view - view name, this may be create_view, edit_view or detail_view
	 *
	 * @return string $blockid_list - list of block ids within the paranthesis with comma seperated
	 */
	function getPermittedBlocks ($module, $disp_view) {
		global $adb;

		$tabid        = getTabid ($module);
		$query        = "SELECT blockid, blocklabel, show_title FROM vtiger_blocks WHERE tabid=? AND {$disp_view}=0 AND visible=0 ORDER BY sequence";
		$result       = $adb->pquery ($query, array ($tabid));
		$noofrows     = $adb->num_rows ($result);
		$blockid_list = '(';
		for ($i = 0; $i < $noofrows; $i++) {
			$blockid = $adb->query_result ($result, $i, "blockid");
			if ($i != 0) {
				$blockid_list .= ', ';
			}
			$blockid_list .= $blockid;
		}
		$blockid_list .= ')';
		return $blockid_list;
	}

	/**
	 * function used to get the query which will list the permitted fields
	 *
	 * @param string $module - module name
	 * @param string $disp_view - view name, this may be create_view, edit_view or detail_view
	 *
	 * @return string $sql - query to get the list of fields which are permitted to the current user
	 */
	function getPermittedFieldsQuery ($module, $disp_view) {
		global $current_user;

		$current_user_groups          = null;
		$current_user_parent_role_seq = null;
		$defaultOrgSharingPermission  = null;
		$is_admin                     = null;
		$profileGlobalPermission      = null;
		$local_user                   = clone $current_user;
		require ('user_privileges/user_privileges.php');

		//To get the permitted blocks
		$blockid_list = getPermittedBlocks ($module, $disp_view);

		$tabid = getTabid ($module);
		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0 || $module == "Users") {
			$sql = "SELECT
						vtiger_field.columnname,
						vtiger_field.fieldlabel,
						vtiger_field.tablename
					FROM
						vtiger_field
					WHERE
						vtiger_field.tabid={$tabid} AND
						vtiger_field.block IN {$blockid_list} AND
						vtiger_field.displaytype IN (1, 2, 4) AND
						vtiger_field.presence IN (0, 2)
					ORDER BY
						vtiger_field.block,
						vtiger_field.sequence";
		} else {
			$profileList = getCurrentUserProfileList ();
			$sql         = "SELECT
								vtiger_field.columnname,
								vtiger_field.fieldlabel,
								vtiger_field.tablename
							FROM
								vtiger_field
								INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid
								INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid
							WHERE
								vtiger_field.tabid={$tabid} AND
								vtiger_field.block IN {$blockid_list} AND
								vtiger_field.displaytype IN (1, 2, 4) AND
								vtiger_profile2field.visible=0 AND
								vtiger_def_org_field.visible=0 AND
								vtiger_profile2field.profileid IN (" . implode (",", $profileList) . ") AND
								vtiger_field.presence IN (0, 2)
							GROUP BY
								vtiger_field.fieldid
							ORDER BY
								vtiger_field.block,
								vtiger_field.sequence";
		}
		return $sql;
	}

	/**
	 * function used to get the list of fields from the input query as a comma seperated string
	 *
	 * @param string $query - field table query which contains the list of fields
	 *
	 * @return string $fields - list of fields as a comma seperated string
	 */
	function getFieldsListFromQuery ($query) {
		global $adb;

		$result   = $adb->query ($query);
		$num_rows = $adb->num_rows ($result);
		$fields   = '';
		for ($i = 0; $i < $num_rows; $i++) {
			$columnName = $adb->query_result ($result, $i, "columnname");
			$fieldlabel = $adb->query_result ($result, $i, "fieldlabel");
			$tablename  = $adb->query_result ($result, $i, "tablename");
			if ($columnName == 'smownerid') { //for all assigned to user name
				$fields .= "CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN vtiger_users.user_name ELSE vtiger_groups.groupname END AS '{$fieldlabel}',";
			} elseif ($tablename == 'vtiger_attachments' && $columnName == 'name') { //Emails filename
				$fields .= "{$tablename}.name AS '{$fieldlabel}',";
			} elseif ($tablename == 'vtiger_notes' && ($columnName == 'filename' || $columnName == 'filetype' || $columnName == 'filesize' || $columnName == 'filelocationtype' || $columnName == 'filestatus' || $columnName == 'filedownloadcount' || $columnName == 'folderid')) {
				continue;
			} else {
				$fields .= "{$tablename}.{$columnName} AS '{$fieldlabel}',";
			}
		}
		$fields = trim ($fields, ",");
		return $fields;
	}

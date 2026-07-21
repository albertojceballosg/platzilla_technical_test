<?php
	/*********************************************************************************
	 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
	 * ("License"); You may not use this file except in compliance with the License
	 * The Original Code is:  vtiger CRM Open Source
	 * The Initial Developer of the Original Code is vtiger.
	 * Portions created by vtiger are Copyright (C) vtiger.
	 * All Rights Reserved.
	 *
	 ********************************************************************************/
	//Code Added by Minnie -Starts
	/**
	 * To get the lists of sharedids
	 * @param $id -- The user id :: Type integer
	 * @returns $sharedids -- The shared vtiger_users id :: Type Array
	 */

	$activitytype = array ('Call' => 'Llamada', 'Activity' => 'Actividad', 'Meeting' => 'Reunión', 'Assignment' => 'Asignación');
	global $activitytype;

	function getSharedUserId ($id) {
		global $adb;
		$sharedid = Array ();
		$query    = "SELECT vtiger_users.*,vtiger_sharedcalendar.* FROM vtiger_sharedcalendar LEFT JOIN vtiger_users ON vtiger_sharedcalendar.sharedid=vtiger_users.id WHERE userid=?";
		$result   = $adb->pquery ($query, array ($id));
		$rows     = $adb->num_rows ($result);
		for ($j = 0; $j < $rows; $j++) {

			$id              = $adb->query_result ($result, $j, 'sharedid');
			$sharedname      = getFullNameFromQResult ($result, $j, 'Users');
			$sharedid[ $id ] = $sharedname;
		}
		return $sharedid;
	}

	/**
	 * To get the lists of vtiger_users id who shared their calendar with specified user
	 * @param $sharedid -- The shared user id :: Type integer
	 * @returns $shared_ids -- a comma seperated vtiger_users id  :: Type string
	 */
	function getSharedCalendarId ($sharedid) {
		global $adb;
		$query  = "SELECT * FROM vtiger_sharedcalendar WHERE sharedid=?";
		$result = $adb->pquery ($query, array ($sharedid));
		if ($adb->num_rows ($result) != 0) {
			for ($j = 0; $j < $adb->num_rows ($result); $j++)
				$userid[] = $adb->query_result ($result, $j, 'userid');
			$shared_ids = implode (",", $userid);
		}
		return $shared_ids;
	}

	/**
	 * To get userid and username of all vtiger_users except the current user
	 * @param $id -- The user id :: Type integer
	 * @returns $user_details -- Array in the following format:
	 * $user_details=Array($userid1=>$username, $userid2=>$username,............,$useridn=>$username);
	 */
	function getOtherUserName ($id) {
		global $adb;
		$user_details = Array ();
		$query        = "SELECT * FROM vtiger_users WHERE deleted=0 AND status='Active' AND id!=?";
		$result       = $adb->pquery ($query, array ($id));
		$num_rows     = $adb->num_rows ($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$userid                  = $adb->query_result ($result, $i, 'id');
			$username                = getFullNameFromQResult ($result, $i, 'Users');
			$user_details[ $userid ] = $username;
		}
		return $user_details;
	}

	/**
	 * To get userid and username of vtiger_users in hierarchy level
	 * @param $id -- The user id :: Type integer
	 * @returns $user_details -- Array in the following format:
	 * $user_details=Array($userid1=>$username, $userid2=>$username,............,$useridn=>$username);
	 */

	function getSharingUserName ($id) {
		global $adb, $current_user;
		$user_details     = Array ();
		$assigned_user_id = $current_user->id;
		$local_user       = clone $current_user;
		require ('user_privileges/user_privileges.php');
		require ('user_privileges/sharing_privileges.php');

		if ($is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ getTabid ('Calendar') ] == 3 or $defaultOrgSharingPermission[ getTabid ('Calendar') ] == 0)) {
			$role_seq = implode ($parent_roles, "::");
			$query    = "SELECT id AS id,user_name AS user_name FROM vtiger_users WHERE id=? AND status='Active' UNION SELECT vtiger_user2role.userid AS id,vtiger_users.user_name AS user_name FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole LIKE ? AND status='Active' UNION SELECT shareduserid AS id,vtiger_users.user_name AS user_name FROM vtiger_tmp_write_user_sharing_per INNER JOIN vtiger_users ON vtiger_users.id=vtiger_tmp_write_user_sharing_per.shareduserid WHERE status='Active' AND vtiger_tmp_write_user_sharing_per.userid=? AND vtiger_tmp_write_user_sharing_per.tabid=9";
			$params   = array ($current_user->id, $role_seq . "::%", $current_user->id);
			if (!empty($assigned_user_id)) {
				$query .= " OR id=?";
				array_push ($params, $assigned_user_id);
			}
			$query .= " order by user_name ASC";
			$result = $adb->pquery ($query, $params, true, "Error filling in user array: ");
			while ($row = $adb->fetchByAssoc ($result)) {
				$temp_result[ $row['id'] ] = $row['user_name'];
			}
			$user_details = &$temp_result;
			unset($user_details[ $id ]);
		} else {
			$user_details = get_user_array (FALSE, "Active", $id);
			unset($user_details[ $id ]);
		}
		return $user_details;
	}

	/**
	 * To get hour,minute and format
	 * @param $starttime -- The date&time :: Type string
	 * @param $endtime -- The date&time :: Type string
	 * @param $format -- The format :: Type string
	 * @returns $timearr :: Type Array
	 */
	function getaddEventPopupTime ($starttime, $endtime, $format) {
		$timearr = Array ();
		list($sthr, $stmin) = explode (":", $starttime);
		list($edhr, $edmin) = explode (":", $endtime);
		if ($format == 'am/pm') {
			$hr                  = $sthr + 0;
			$timearr['startfmt'] = ($hr >= 12) ? "pm" : "am";
			if ($hr == 0)
				$hr = 12;
			$timearr['starthour'] = twoDigit (($hr > 12) ? ($hr - 12) : $hr);
			$timearr['startmin']  = $stmin;

			$edhr              = $edhr + 0;
			$timearr['endfmt'] = ($edhr >= 12) ? "pm" : "am";
			if ($edhr == 0)
				$edhr = 12;
			$timearr['endhour'] = twoDigit (($edhr > 12) ? ($edhr - 12) : $edhr);
			$timearr['endmin']  = $edmin;
			return $timearr;
		}
		if ($format == '24') {
			$timearr['starthour'] = twoDigit ($sthr);
			$timearr['startmin']  = $stmin;
			$timearr['startfmt']  = '';
			$timearr['endhour']   = twoDigit ($edhr);
			$timearr['endmin']    = $edmin;
			$timearr['endfmt']    = '';
			return $timearr;
		}
	}

	/**
	 *To construct time select combo box
	 * @param $format -- the format :: Type string
	 * @param $bimode -- The mode :: Type string
	 *constructs html select combo box for time selection
	 *and returns it in string format.
	 */
	function getTimeCombo ($format, $bimode, $hour = '', $min = '', $fmt = '', $todocheck = false) {
		global $mod_strings;
		$combo = '';
		$min   = $min - ($min % 5);
		if ($bimode == 'start' && !$todocheck)
			$jsfn = 'onChange="changeEndtime_StartTime(document.EditView.activitytype.value);"';
		else
			$jsfn = null;
		if ($format == 'am/pm') {
			$combo .= '<select class=small name="' . $bimode . 'hr" id="' . $bimode . 'hr" ' . $jsfn . '>';
			for ($i = 0; $i < 12; $i++) {
				if ($i == 0) {
					$hrtext  = 12;
					$hrvalue = 12;
				} else
					$hrvalue = $hrtext = twoDigit ($i);
				$hrsel = ($hour == $hrvalue) ? 'selected' : '';
				$combo .= '<option value="' . $hrvalue . '" ' . $hrsel . '>' . $hrtext . '</option>';
			}
			$combo .= '</select>&nbsp;';
			$combo .= '<select name="' . $bimode . 'min" id="' . $bimode . 'min" class=small ' . $jsfn . '>';
			for ($i = 0; $i < 12; $i++) {
				$value  = $i * 5;
				$value  = twoDigit ($value);
				$minsel = ($min == $value) ? 'selected' : '';
				$combo .= '<option value="' . $value . '" ' . $minsel . '>' . $value . '</option>';
			}
			$combo .= '</select>&nbsp;';
			$combo .= '<select name="' . $bimode . 'fmt" id="' . $bimode . 'fmt" class=small ' . $jsfn . '>';
			$amselected = ($fmt == 'am') ? 'selected' : '';
			$pmselected = ($fmt == 'pm') ? 'selected' : '';
			$combo .= '<option value="am" ' . $amselected . '>AM</option>';
			$combo .= '<option value="pm" ' . $pmselected . '>PM</option>';
			$combo .= '</select>';
		} else {
			$combo .= '<select name="' . $bimode . 'hr" id="' . $bimode . 'hr" class=small ' . $jsfn . '>';
			for ($i = 0; $i <= 23; $i++) {
				$hrvalue = twoDigit ($i);
				$hrsel   = ($hour == $hrvalue) ? 'selected' : '';
				$combo .= '<option value="' . $hrvalue . '" ' . $hrsel . '>' . $hrvalue . '</option>';
			}
			$combo .= '</select>' . $mod_strings[ LBL_HR ] . '&nbsp;';
			$combo .= '<select name="' . $bimode . 'min" id="' . $bimode . 'min" class=small ' . $jsfn . '>';
			for ($i = 0; $i < 12; $i++) {
				$value  = $i * 5;
				$value  = twoDigit ($value);
				$minsel = ($min == $value) ? 'selected' : '';
				$combo .= '<option value="' . $value . '" ' . $minsel . '>' . $value . '</option>';
			}
			$combo .= '</select>&nbsp;' . $mod_strings[ LBL_MIN ] . '<input type="hidden" name="' . $bimode . 'fmt" id="' . $bimode . 'fmt">';
		}
		return $combo;
	}

	/**
	 *Function to construct HTML select combo box
	 * @param $fieldname -- the field name :: Type string
	 * @param $tablename -- The table name :: Type string
	 *constructs html select combo box for combo field
	 *and returns it in string format.
	 */
	function getActFieldCombo ($fieldname, $tablename) {
		global $adb, $mod_strings, $current_user;
		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');
		$combo = '';
		$js_fn = '';
		if ($fieldname == 'eventstatus')
			$js_fn = 'onChange = "getSelectedStatus();"';
		$combo .= '<select name="' . $fieldname . '" id="' . $fieldname . '" class=small ' . $js_fn . '>';
		if ($is_admin)
			$q = "SELECT * FROM " . $tablename;
		else {
			$roleid  = $current_user->roleid;
			$subrole = getRoleSubordinates ($roleid);
			if (count ($subrole) > 0) {
				$roleids = $subrole;
				array_push ($roleids, $roleid);
			} else {
				$roleids = $roleid;
			}

			if (count ($roleids) > 1) {
				$q = "select distinct $fieldname from  $tablename inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = $tablename.picklist_valueid where roleid in (\"" . implode ($roleids, "\",\"") . "\") and picklistid in (select picklistid from $tablename) order by sortid asc";
			} else {
				$q = "select distinct $fieldname from $tablename inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = $tablename.picklist_valueid where roleid ='" . $roleid . "' and picklistid in (select picklistid from $tablename) order by sortid asc";
			}
		}
		$Res      = $adb->query ($q);
		$noofrows = $adb->num_rows ($Res);

		for ($i = 0; $i < $noofrows; $i++) {
			$value = $adb->query_result ($Res, $i, $fieldname);
			$combo .= '<option value="' . $value . '">' . getTranslatedString ($value) . '</option>';
		}

		$combo .= '</select>';
		return $combo;
	}

	/*Fuction to get value for Assigned To field
	 *returns values of Assigned To field in array format
	*/
	function getAssignedTo ($tabid) {
		global $current_user, $noof_group_rows, $adb;
		$assigned_user_id = $current_user->id;
		$local_user       = clone $current_user;
		require ('user_privileges/user_privileges.php');
		require ('user_privileges/sharing_privileges.php');
		if ($is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ $tabid ] == 3 or $defaultOrgSharingPermission[ $tabid ] == 0)) {
			$result = get_current_user_access_groups ('Calendar');
		} else {
			$result = get_group_options ();
		}
		if ($result)
			$nameArray = $adb->fetch_array ($result);

		if ($is_admin == false && $profileGlobalPermission[2] == 1 && ($defaultOrgSharingPermission[ $tabid ] == 3 or $defaultOrgSharingPermission[ $tabid ] == 0)) {
			$users_combo = get_select_options_array (get_user_array (FALSE, "Active", $assigned_user_id, 'private'), $assigned_user_id);
		} else {
			$users_combo = get_select_options_array (get_user_array (FALSE, "Active", $assigned_user_id), $assigned_user_id);
		}
		if ($noof_group_rows != 0) {
			do {
				$groupname      = $nameArray["groupname"];
				$group_option[] = array ($groupname => $selected);
			} while ($nameArray = $adb->fetch_array ($result));
		}
		$fieldvalue[] = $users_combo;
		$fieldvalue[] = $group_option;
		return $fieldvalue;
	}

	//Code Added by Minnie -Ends
	/**
	 * Function to get the vtiger_activity details for mail body
	 * @param   string $description - activity description
	 * @param   string $from - to differenciate from notification to invitation.
	 * return   string   $list              - HTML in string format
	 */
	function getActivityDetails ($description, $user_id, $from = '') {
		global $log, $current_user, $current_language;
		global $adb;
		require_once 'include/utils/utils.php';
		$mod_strings = return_module_language ($current_language, 'Calendar');
		$log->debug ("Entering getActivityDetails(" . $description . ") method ...");
		$updated = $mod_strings['LBL_UPDATED'];
		$created = $mod_strings['LBL_CREATED'];
		$reply   = (($description['mode'] == 'edit') ? "$updated" : "$created");
		if ($description['activity_mode'] == "Events") {
			$end_date_lable = $mod_strings['End date and time'];
		} else {
			$end_date_lable = $mod_strings['Due Date'];
		}

		$name = getUserFullName ($user_id);

		if ($from == "invite")
			$msg = getTranslatedString ($mod_strings['LBL_ACTIVITY_INVITATION']);
		else
			$msg = getTranslatedString ($mod_strings['LBL_ACTIVITY_NOTIFICATION']);

		$current_username = getUserFullName ($current_user->id);
		$status           = getTranslatedString ($description['status'], 'Calendar');
		$list             = $name . ',';
		$list .= '<br><br>' . $msg . ' ' . $reply . '.<br> ' . $mod_strings['LBL_DETAILS_STRING'] . ':<br>';
		$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["LBL_SUBJECT"] . ' : ' . $description['subject'];
		$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["Start date and time"] . ' : ' . $description['st_date_time'] . ' ' . DateTimeField::getDBTimeZone ();
		$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $end_date_lable . ' : ' . $description['end_date_time'] . ' ' . DateTimeField::getDBTimeZone ();
		$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["LBL_STATUS"] . ': ' . $status;
		$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["Priority"] . ': ' . getTranslatedString ($description['taskpriority']);
		$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["Related To"] . ': ' . getTranslatedString ($description['relatedto']);
		if (!empty($description['contact_name'])) {
			$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["LBL_CONTACT_LIST"] . ' ' . $description['contact_name'];
		} else
			$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["Location"] . ' : ' . $description['location'];

		$list .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $mod_strings["LBL_APP_DESCRIPTION"] . ': ' . $description['description'];
		$list .= '<br><br>' . $mod_strings["LBL_REGARDS_STRING"] . ' ,';
		$list .= '<br>' . $current_username . '.';

		$log->debug ("Exiting getActivityDetails method ...");
		return $list;
	}

	function twoDigit ($no) {
		if ($no < 10 && strlen (trim ($no)) < 2)
			return "0" . $no;
		else return "" . $no;
	}

	function timeString ($datetime, $fmt) {

		$timeStr = formatUserTimeString ($datetime, $fmt);
		$date    = new DateTimeField($timeStr);
		list($h, $m) = explode (':', $date->getDisplayTime ());
		$timeStr = formatUserTimeString (array ('hour' => $h, 'minute' => $m), $fmt);
		return $timeStr;
	}

	/**
	 *
	 * @param type $datetime
	 * @param type $fmt
	 * @return Date
	 */
	function formatUserTimeString ($datetime, $fmt) {

		if (is_object ($datetime)) {
			$hr  = $datetime->hour;
			$min = $datetime->minute;
		} else {
			$hr  = $datetime['hour'];
			$min = $datetime['minute'];
		}
		$timeStr = "";
		if ($fmt != 'am/pm') {
			$timeStr .= twoDigit ($hr) . ":" . twoDigit ($min);
		} else {
			$am_pm = array ('AM', 'PM');
			$hour  = twoDigit ($hr % 12);
			if ($hour == 0) {
				$hour = 12;
			}
			$timeStr = $hour . ':' . twoDigit ($min) . $am_pm[ ($hr / 12) % 2 ];
		}
		return $timeStr;
	}

	function sendInvitation ($inviteesid, $mode, $subject, $desc) {
		global $current_user, $mod_strings;
		require_once ("modules/Emails/mail.php");
		$invites        = $mod_strings['INVITATION'];
		$invitees_array = explode (';', $inviteesid);
		$subject        = $invites . ' : ' . $subject;
		$record         = $focus->id;
		foreach ($invitees_array as $inviteeid) {
			if ($inviteeid != '') {
				$description = getActivityDetails ($desc, $inviteeid, "invite");
				$to_email    = getUserEmailId ('id', $inviteeid);
				send_mail ('Calendar', $to_email, $current_user->user_name, '', $subject, $description);
			}
		}
	}

	// User Select Customization
	/**
	 * Function returns the id of the User selected by current user in the picklist of the ListView or Calendar view of Current User
	 * return String -  Id of the user that the current user has selected
	 */
	function calendarview_getSelectedUserId () {
		global $current_user, $default_charset;
		$only_for_user = htmlspecialchars (strip_tags ($_REQUEST['onlyforuser']), ENT_QUOTES, $default_charset);
		if ($only_for_user == '')
			$only_for_user = $current_user->id;
		return $only_for_user;
	}

	function calendarview_getSelectedUserFilterQuerySuffix () {
		global $current_user, $adb;
		$only_for_user = calendarview_getSelectedUserId ();
		$qcondition    = '';
		$operator      = ' AND ';

		if (obtenerValorVariable ('CALENDAR_MODE_TURNOS', 'Calendar') == 'true') {
			$desarrollador     = "SELECT vendorid FROM vtiger_vendor WHERE user_id=?";
			$desarrollador_res = $adb->pquery ($desarrollador, array ($only_for_user));
			$desarrollador_id  = $adb->query_result ($desarrollador_res, 0, "vendorid");
			$only_for_user_des = $desarrollador_id;

			if (!empty($only_for_user_des)) {
				if ($only_for_user_des != 'ALL') {
					// For logged in user include the group records also.

					if ($only_for_user_des == $current_user->id) {

						//$user_group_ids = fetchUserGroupids($current_user->id);

						// User does not belong to any group? Let us reset to non-existent group
						//	if(!empty($user_group_ids)) $user_group_ids .= ',';
						//	else $user_group_ids = '';
						//		$user_group_ids .= $current_user->id;
						if (empty($only_for_user_des)) {
							$qcondition = " AND vtiger_activity.desarrollador_id IN (" . $only_for_user_des . ")";
						} else {
							$qcondition = " AND vtiger_activity.desarrollador_id IN (" . $only_for_user_des . ")";
						}
					} else {
						if (empty($only_for_user_des)) {
							$qcondition = " AND vtiger_activity.desarrollador_id IN (" . $only_for_user_des . ")";
						} else {
							$qcondition = " AND vtiger_activity.desarrollador_id IN (" . $only_for_user_des . ")";
						}
					}
				}
			}
			$operator = ' OR ';
		}
		if (obtenerValorVariable ('MOSTRAR_ACTIVIDADES_ADICIONALES', 'Calendar') == 'true' ||
			obtenerValorVariable ('CALENDAR_MODE_TURNOS', 'Calendar') == 'false' ||
			obtenerValorVariable ('CALENDAR_MODE_TURNOS', 'Calendar') == ''
		) {
			if (!empty($only_for_user)) {
				if ($only_for_user != 'ALL') {
					// For logged in user include the group records also.
					if ($only_for_user == $current_user->id) {
						$user_group_ids = fetchUserGroupids ($current_user->id);
						// User does not belong to any group? Let us reset to non-existent group
						if (!empty($user_group_ids))
							$user_group_ids .= ',';
						else $user_group_ids = '';
						$user_group_ids .= $current_user->id;
						$qcondition .= $operator . " vtiger_crmentity.smownerid IN (" . $user_group_ids . ")";
					} else {
						$qcondition .= $operator . " vtiger_crmentity.smownerid = " . $adb->sql_escape_string ($only_for_user);
					}
				}
			}
		}
		return $qcondition;
	}

	/**
	 * Function returns the data of the user selected by current user in the picklist of the ListView or Calendar view of Current User
	 * @param $useridInUse - The Id of the user that the Current User has selected in dropdown picklist in Calendar modules listview or Calendar View
	 * return string - The array of the events for the user that the current user has selected
	 */
	function calendarview_getUserSelectOptions ($useridInUse) {
		global $adb, $app_strings, $current_user, $mod_strings;

		$userSelectdata = "<span style='padding-left: 10px; padding-right: 10px;'><b>" . $app_strings['LBL_LIST_OF'] . " : </b>";
		$userSelectdata .= "<select class='small' onchange='fnRedirect();' name='onlyforuser'>";

		// Providing All option for administrators only
		if (is_admin ($current_user)) {
			$userSelectdata .= "<option value='ALL'>" . $app_strings['COMBO_ALL'] . "</option>";
		}
		/* Esto pareciera no estar funcionando en Time */
		if (0) {
			//if (obtenerValorVariable('CALENDAR_MODE_TURNOS','Calendar') == 'true') {
			$users = $adb->query ("SELECT vendorname AS user_name,v.vendorid AS id
		FROM vtiger_vendor v
			LEFT JOIN vtiger_crmentity ON (crmid=v.vendorid)
			LEFT JOIN vtiger_vendorcf cf ON (v.vendorid=cf.vendorid)
		WHERE deleted=0 AND cf_642='Programador/Asistente' AND cf_679='1' ORDER BY vendorname");

			$desarrollador     = $adb->query ("SELECT vendorid FROM vtiger_vendor where user_id=$current_user->id");
			$desarrollador_id  = $adb->query_result ($desarrollador, 0, 'vendorid');
			$desarrollador     = "SELECT vendorid FROM vtiger_vendor WHERE user_id=?";
			$desarrollador_res = $adb->pquery ($desarrollador, array ($useridInUse));
			$useridInU         = $adb->query_result ($desarrollador_res, 0, "vendorid");
			$current_id        = $desarrollador_id;

			$userSelectdata .= "<option value='$desarrollador_id'" . (($desarrollador_id == $useridInU) ? "selected='true'" : "") . ">" . $mod_strings['LBL_MINE'] . "</option>";
		} else {
			$users = $adb->query ("SELECT id,concat(first_name,' ',last_name) AS user_name FROM vtiger_users WHERE status = 'Active' AND deleted = 0 AND contactid IS NULL ORDER BY 2");

			$userSelectdata .= "<option value='$current_user->id'" . (($current_user->id == $useridInUse) ? "selected='true'" : "") . ">" . $mod_strings['LBL_MINE'] . "</option>";
			$current_id = $current_user->id;
		}

		$userscount = $adb->num_rows ($users);

		for ($index = 0; $index < $userscount; ++$index) {
			$userid = $adb->query_result ($users, $index, 'id');
			if ($userid == $current_id) {
				continue; // We have already taken care of listing at first.
			}
			$username   = $adb->query_result ($users, $index, 'user_name');
			$userselect = '';
			if ($userid == $useridInUse)
				$userselect = "selected='true'";
			$userSelectdata .= "<option value='$userid' $userselect>$username</option>";
		}
		$userSelectdata .= "</select></span>";
		return $userSelectdata;
	}

	// END

	/*[ TT11406 ] Filtros en Calendario por usuario asignado y tipo de tareas - Jesus A - Se agrega parametro para filtrar consulta */
	function getFullCalendar ($order = '', $param = '') {
		require_once ('modules/Calendar/Calendar.php');
		global $adb, $current_user, $activitytype;

		/** @var CRMEntity $entity */
		$entity = CRMEntity::getInstance ('Calendar');
		if (!is_admin ($current_user)) {
			$fromClause = $entity->getNonAdminAccessControlQuery ('Calendar', $current_user);
		} else {
			$fromClause = '';
		}

		/*[ TT11406 ] Filtros en Calendario por usuario asignado y tipo de tareas - Jesus A - se filtra consulta por tipo de avtividad y id de usuario o ambas */
		$sqlCond = '';
		if (!empty ($param['activity_type'])) {
			$sqlCond .= " AND vtiger_activity.activitytype='{$param['activity_type']}'";
		}
		if (!empty ($param['user_id'])) {
			$sqlCond .= " AND vtiger_users.id='{$param['user_id']}'";
		}

		$sql = "SELECT
					vtiger_activity.*,
					vtiger_crmentity.modifiedtime AS date_modified,
					vtiger_crmentity.description AS description,
					NULL AS cfn,
					NULL AS cln,
					YEAR(vtiger_activity.date_start) AS year_start_date,
					MONTH(vtiger_activity.date_start) AS month_start_date,
					DAY(vtiger_activity.date_start) AS day_start_date,
					YEAR(vtiger_activity.due_date) AS year_due_date,
					MONTH(vtiger_activity.due_date) AS month_due_date,
					DAY(vtiger_activity.due_date) AS day_due_date,
					vtiger_salesmanactivityrel.smid,
					creatoruser.first_name AS creatorfirstname,
					creatoruser.last_name AS creatorlastname,
					vtiger_users.first_name,
					vtiger_users.last_name,
					vtiger_groups.groupname
				FROM
					vtiger_activity
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_activity.activityid
					INNER JOIN vtiger_salesmanactivityrel ON vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid
					INNER JOIN vtiger_users creatoruser ON creatoruser.id=vtiger_salesmanactivityrel.smid
					LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
					LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
					{$fromClause}
				WHERE
					vtiger_crmentity.deleted=0 {$sqlCond}";
		if ($order != '') {
			$sql .= "ORDER BY {$order}";
		}

		$result = $adb->query ($sql);
		if ($adb->num_rows ($result) > 0) {
			$activities = array ();
			$seenActivityIds = array(); // Array para rastrear IDs ya procesados
			while ($row = $adb->fetchByAssoc ($result)) {
				// Deduplicar: saltar si ya procesamos esta actividad
				if (isset($seenActivityIds[$row['activityid']])) {
					continue;
				}
				$seenActivityIds[$row['activityid']] = true;
				
				$row['subject']      = htmlspecialchars (html_entity_decode ($row['subject'], ENT_QUOTES, 'UTF-8'), ENT_NOQUOTES, 'UTF-8');
				$row['link']         = '#myModal-' . $row['activityid'];
				$row['hour_start']   = substr ($row['time_start'], 0, 2);
				$row['minute_start'] = substr ($row['time_start'], 3, 2);
				$row['hour_end']     = substr ($row['time_end'], 0, 2);
				$row['minute_end']   = substr ($row['time_end'], 3, 2);

				switch ($row['activitytype']) {
					case 'Activity':
						$row['class_name'] = 'label-success';
						$row['isAllDay']   = 'false';
						break;
					case 'Meeting':
						$row['class_name']   = 'label-info';
						$row['hour_start']   = substr ($row['time_start'], 0, 2);
						$row['minute_start'] = substr ($row['time_start'], 3, 2);
						$row['hour_end']     = substr ($row['time_end'], 0, 2);
						$row['minute_end']   = substr ($row['time_end'], 3, 2);
						$row['isAllDay']     = 'false';
						break;
					case 'Call':
						$row['class_name']   = 'label-warning';
						$row['hour_start']   = '00';
						$row['minute_start'] = '00';
						$row['hour_end']     = '00';
						$row['minute_end']   = '00';
						$row['isAllDay']     = 'true';
						break;
					default:
						$row['class_name']   = 'label-danger';
						$row['hour_start']   = substr ($row['time_start'], 0, 2);
						$row['minute_start'] = substr ($row['time_start'], 3, 2);
						$row['hour_end']     = substr ($row['time_end'], 0, 2);
						$row['minute_end']   = substr ($row['time_end'], 3, 2);
						$row['isAllDay']     = 'false';
						break;
				}

				$type                    = $row ['activitytype'];
				$row ['activitytype']    = isset ($activitytype [ $type ]) ? $activitytype [ $type ] : $type;
				$row ['relatedentities'] = Calendar::getRelatedEntities ($adb, $row ['activityid']);
				if (!empty($row ['relatedentities'])) {
					$row ['related_id'] = $row ['relatedentities'][0]['crmid'];
					$row ['related_to'] = $row ['relatedentities'][0]['modulename'];
				}
				
				$activities []           = $row;
			}
		} else {
			$activities = null;
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}
		return $activities;
	}

	/* Modificado por Johana Romero
	Pedido [TT11193] Adecuación Página Resumen de la aplicación - Platzilla
	Obtiene las actividades Segun el filtro seleccionado */
	function getFullCalendarFilter ($order = '', $filter, $allUsers = '') {

		global $adb, $current_user, $activitytype;

		switch ($filter) {
			case 'today':
				$userStartDate = date ('Y-m-d');
				$startDateTime = $userStartDate . ' 00:00:00';
				$endDateTime   = $userStartDate . ' 23:59:00';

				break;

			case 'yesterday':
				$yesterday     = date ("Y-m-d", mktime (0, 0, 0, date ("m"), date ("d") - 1, date ("Y")));
				$userStartDate = $yesterday;
				$startDateTime = $userStartDate . ' 00:00:00';
				$endDateTime   = $userStartDate . ' 23:59:00';

				break;

			case 'lastWeek':
				$lastweek      = date ("Y-m-d", mktime (0, 0, 0, date ("m"), date ("d") - 7, date ("Y")));
				$userStartDate = $lastweek;
				$startDateTime = $userStartDate . ' 00:00:00';
				$endDateTime   = date ('Y-m-d') . ' 23:59:00';

				break;

			default:
				$lastmonth     = date ("Y-m-d", mktime (0, 0, 0, date ("m") - 1, date ("d"), date ("Y")));
				$userStartDate = $lastmonth;
				$startDateTime = $userStartDate . ' 00:00:00';
				$endDateTime   = date ('Y-m-d') . ' 23:59:00';
				break;
		}

		$sql_filter = " and ((cast(actiondate as DATETIME) >= '$startDateTime' and cast(actiondate as DATETIME) <= '$endDateTime')) ";

		if (!is_admin ($current_user)) {
			$sql_user = " and (
				vtiger_crmentity.smownerid = " . $current_user->id . "
				or exists (SELECT 1 from vtiger_invitees where inviteeid = " . $current_user->id . " and activityid = vtiger_activity.activityid)
			)";
		} else {
			$sql_user = '';
		}
		$sql = "SELECT a.module, a.action, a.recordid, max(a.actiondate) AS action_date, t.tablabel, en.tablename, en.fieldname, en.entityidfield, crm.createdtime, crm.modifiedtime, usr.id, usr.last_name FROM vtiger_audit_trial a INNER JOIN vtiger_tab t ON t.name = a.module LEFT OUTER JOIN vtiger_entityname en ON en.modulename = a.module INNER JOIN vtiger_crmentity crm ON crm.crmid = a.recordid INNER JOIN vtiger_users AS usr ON (a.userid = usr.id) WHERE t.name NOT IN ('Tooltip','Home') AND a.action IN ('DetailView','Save', 'Delete') AND a.recordid <> '' ";

		/*[ TT11390 ] News bar - Jesus A. - Se omite el userid de la consulta si el valor de allUsers es vacío('') y no es 'All' */
		if ($allUsers == '' && $allUsers != 'All')
			$sql .= "AND a.userid = " . $current_user->id . " ";

		$sql .= $sql_filter . "GROUP BY action,recordid ORDER BY actiondate DESC";

		$query    = $adb->query ($sql);
		$history  = array ();
		$noofrows = $adb->num_rows ($query);
		if ($noofrows > 0) {
			while ($row = $adb->fetchByAssoc ($query)) {

				/*[ TT11390 ] News bar - Jesus A. - a partir del id que del registro se solicita la imagen del usuario si no tiene se deja el valor vacío*/
				$row['imagename'] = getUserImageName ($row['id']) ? getUserImageName ($row['id']) : '';

				$campo = str_replace (",", ",' ',", $row['fieldname']);

				if ($campo != '' && isset($row['tablename']) && isset($row['entityidfield']) && isset($row['recordid'])) {

					$entity = "SELECT CONCAT(" . $campo . ") c FROM " . $row['tablename'] . " where " . $row['entityidfield'] . " = '" . $row['recordid'] . "' LIMIT 1";
					$query2 = $adb->query ($entity);

					if ($adb->num_rows ($query2) > 0) {
						$row['label_entity'] = $adb->query_result ($query2, 0, "c");
						if ($row['action'] == 'DetailView') {
							$row['tipo'] = 'Nuevo';
						} elseif ($row['action'] == 'Delete') {
							$row['tipo'] = 'Eliminado';
						} else {
							$row['tipo'] = 'Modificado';
						}
					} else {
						$row['label_entity'] = '';
					}
				} else {
					$row['label_entity'] = '';
				}

				$history[] = $row;
			}
		}
		//var_dump($history);
		return $history;
	}

	function getInfoSelectAsignedUserId () {
		global $adb, $current_user;
		$data = array ();

		$sql = "SELECT id, first_name, last_name
		FROM `vtiger_users`
		WHERE STATUS = 'Active'
		AND deleted =0";

		$result = $adb->query ($sql);
		$rows   = $adb->num_rows ($result);
		while ($row = $adb->fetchByAssoc ($result)) {
			$temp['value'] = $row['id'];
			$temp['label'] = $row['first_name'] . ' ' . $row['last_name'];
			array_push ($data, $temp);
		}

		return $data;
	}

	function getInfoSelectGroupsId () {

		global $adb, $current_user;
		$data = array ();

		$sql = "SELECT groupid, groupname
		FROM vtiger_groups";

		$result = $adb->query ($sql);
		$rows   = $adb->num_rows ($result);
		while ($row = $adb->fetchByAssoc ($result)) {
			$temp['value'] = $row['groupid'];
			$temp['label'] = $row['groupname'];
			array_push ($data, $temp);
		}

		return $data;
	}

	/*[ TT11406 ] Filtros en Calendario por usuario asignado y tipo de tareas - Jesus A - Funciones de consulta de tipo de actividades del  modulo tarea*/
	function getActivityTypeCalendar ($id) {
		global $adb;
		$query  = "SELECT activitytype FROM vtiger_activity WHERE activityid=?";
		$result = $adb->pquery ($query, array ($id));
		return $adb->query_result ($result, 0, 'activitytype');
	}

	function getActivityTypes () {
		global $adb, $mod_strings;

		$types = array ();
		$result = $adb->query ('SELECT * FROM vtiger_activitytype');
		if ($adb->num_rows ($result) > 0) {
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$type = $row ['activitytype'];
				$types [ $type ] = isset ($mod_strings [ $type ]) ? $mod_strings [ $type ] : $type;
			}
		}
		if ($result instanceof ADORecordSet) {
			$result->Close ();
			$result = null;
		}

		return $types;
	}

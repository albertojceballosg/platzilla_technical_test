<?php
	ini_set ('max_execution_time', 600);
	require_once ('config.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');
	require_once ('modules/Users/Users.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/GetUserGroups.php');
	require_once ('include/utils/GetGroupUsers.php');

	/** Creates a file with all the user, user-role,user-profile, user-groups informations
	 *
	 * @param $userid -- user id:: Type integer
	 *
	 * @returns user_privileges_userid file under the user_privileges directory
	 */

	function createUserPrivilegesfile ($userid) {
		global $root_directory;
		$handle = null;
		if (isset ($_SESSION ['plat'])) {
			if (!is_dir ("{$root_directory}/{$_SESSION ['plat']}/user_privileges")) {
				$oldumask = umask (0);
				mkdir ("{$root_directory}/{$_SESSION ['plat']}/user_privileges", 0777, true);
				umask ($oldumask);
			}

			$handle = @fopen ("{$root_directory}/{$_SESSION ['plat']}/user_privileges/user_privileges_{$userid}.php", "w+");
		} else {
			$handle = @fopen ("{$root_directory}/user_privileges/user_privileges_{$userid}.php", "w+");
		}
		if ($handle) {
			$newbuf = '';
			$newbuf .= "<?php\n\n";
			$newbuf .= "\n";
			$newbuf .= "//This is the access privilege file\n";
			$user_focus = new Users();
			$user_focus->retrieve_entity_info ($userid, "Users");
			$userInfo                        = Array ();
			$user_focus->column_fields["id"] = '';
			$user_focus->id                  = $userid;
			// Get working day info
			$validWorkingDay = WorkingDayUtils::getValidWorkingDay ($user_focus->db, $userid);
			if (!empty ($validWorkingDay) && $validWorkingDay instanceof WorkingDayMaster) {
				$user_focus->start_hour    = $validWorkingDay->getMorningStartTime ();
				$user_focus->end_hour      = $validWorkingDay->getAfternoonDueTime ();
				$user_focus->working_hours = WorkingDayUtils::getWorkingHoursToday ($validWorkingDay);
			}
			foreach ($user_focus->column_fields as $field => $value_iter) {
				$userInfo[ $field ] = $user_focus->$field;
			}

			if ($user_focus->is_admin == 'on') {
				$newbuf .= "\$is_admin=true;\n";
				$newbuf .= "\n";
				$newbuf .= "\$user_info=" . constructSingleStringKeyValueArray ($userInfo) . ";\n";
				$newbuf .= "\n";
				$newbuf .= "?>";
				fputs ($handle, $newbuf);
				fclose ($handle);
				return;
			} else {
				$newbuf .= "\$is_admin=false;\n";
				$newbuf .= "\n";
				$globalPermissionArr    = getCombinedUserGlobalPermissions ($userid);
				$tabsPermissionArr      = getCombinedUserTabsPermissions ($userid);
				$customViewsPermissions = getCombinedUserCustomViewsPermissions ($userid);
				$actionPermissionArr    = getCombinedUserActionPermissions ($userid);
				$user_role              = fetchUserRole ($userid);
				$user_role_info         = getRoleInformation ($user_role);
				$user_role_parent       = $user_role_info[ $user_role ][1];
				$userGroupFocus         = new GetUserGroups();
				$userGroupFocus->getAllUserGroups ($userid);
				$subRoles        = getRoleSubordinates ($user_role);
				$subRoleAndUsers = getSubordinateRoleAndUsers ($user_role);
				$parentRoles     = getParentRole ($user_role);

				$newbuf .= "\$current_user_roles='" . $user_role . "';\n";
				$newbuf .= "\n";
				$newbuf .= "\$current_user_parent_role_seq='" . $user_role_parent . "';\n";
				$newbuf .= "\n";
				$newbuf .= "\$current_user_profiles=" . constructSingleArray (getUserProfile ($userid)) . ";\n";
				$newbuf .= "\n";
				$newbuf .= "\$profileGlobalPermission=" . constructArray ($globalPermissionArr) . ";\n";
				$newbuf .= "\n";
				$newbuf .= "\$profileTabsPermission=" . constructArray ($tabsPermissionArr) . ";\n";
				$newbuf .= "\n";
				$newbuf .= "\$profileCustomViewsPermission=" . constructArray ($customViewsPermissions) . ";\n";
				$newbuf .= "\n";
				$newbuf .= "\$profileActionPermission=" . constructTwoDimensionalArray ($actionPermissionArr) . ";\n";
				$newbuf .= "\n";
				$newbuf .= "\$current_user_groups=" . constructSingleArray ($userGroupFocus->user_groups) . ";\n";
				$newbuf .= "\n";
				$newbuf .= "\$subordinate_roles=" . constructSingleCharArray ($subRoles) . ";\n";
				$newbuf .= "\n";
				$newbuf .= "\$parent_roles=" . constructSingleCharArray ($parentRoles) . ";\n";
				$newbuf .= "\n";
				$newbuf .= "\$subordinate_roles_users=" . constructTwoDimensionalCharIntSingleArray ($subRoleAndUsers) . ";\n";
				$newbuf .= "\n";
				$newbuf .= "\$user_info=" . constructSingleStringKeyValueArray ($userInfo) . ";\n";

				$newbuf .= "?>";
				fputs ($handle, $newbuf);
				fclose ($handle);
			}
		}
	}

	/** Creates a file with all the organization default sharing permissions and custom sharing permissins specific for the specified user. In this file the information of the other users whose data is shared with the specified user is stored.
	 *
	 * @param $userid -- user id:: Type integer
	 *
	 * @returns sharing_privileges_userid file under the user_privileges directory
	 */
	function createUserSharingPrivilegesfile ($userid) {
		global $adb, $root_directory, $current_user;
		$local_user     = clone $current_user;
		$local_user->id = $userid;
		require ('user_privileges/user_privileges.php');
		$handle = null;
		if (isset($_SESSION['plat'])) {
			if (!is_dir ($_SESSION['plat'] . "/user_privileges")) {
				$oldumask = umask (0);
				mkdir ($_SESSION['plat'] . "/user_privileges", 0777, true);
				umask ($oldumask);
			}
			$handle = @fopen ($root_directory . $_SESSION['plat'] . '/user_privileges/sharing_privileges_' . $userid . '.php', "w+");
		} else {
			$handle = @fopen ($root_directory . 'user_privileges/sharing_privileges_' . $userid . '.php', "w+");
		}

		if ($handle) {
			$newbuf = '';
			$newbuf .= "<?php\n\n";
			$newbuf .= "\n";
			$newbuf .= "//This is the sharing access privilege file\n";
			$user_focus = new Users();
			$user_focus->retrieve_entity_info ($userid, "Users");
			if ($user_focus->is_admin == 'on') {
				$newbuf .= "\n";
				$newbuf .= "?>";
				fputs ($handle, $newbuf);
				fclose ($handle);
				return;
			} else {
				//Constructig the Default Org Share Array
				$def_org_share = getAllDefaultSharingAction ();
				$newbuf .= "\$defaultOrgSharingPermission=" . constructArray (array_filter ($def_org_share)) . ";\n";
				$newbuf .= "\n";

				//Constructing the Related Module Sharing Array
				$relModSharArr = Array ();
				$query         = "SELECT * FROM vtiger_datashare_relatedmodules";
				$result        = $adb->pquery ($query, array ());
				$num_rows      = $adb->num_rows ($result);
				for ($i = 0; $i < $num_rows; $i++) {
					$parTabId = $adb->query_result ($result, $i, 'tabid');
					$relTabId = $adb->query_result ($result, $i, 'relatedto_tabid');
					if (is_array ($relModSharArr[ $relTabId ])) {
						$temArr   = $relModSharArr[ $relTabId ];
						$temArr[] = $parTabId;
					} else {
						$temArr   = Array ();
						$temArr[] = $parTabId;
					}
					$relModSharArr[ $relTabId ] = $temArr;
				}

				$newbuf .= "\$related_module_share=" . constructTwoDimensionalValueArray ($relModSharArr) . ";\n\n";

				// Writing Sharing Rules For Custom Modules.
				// TODO: We are ignoring rules that has already been calculated above, it is good to add GENERIC logic here.
				$custom_modules = getSharingModuleList (
					Array (
						'Leads', 'Accounts', 'Contacts', 'Potentials', 'HelpDesk',
						'Emails', 'Campaigns', 'Quotes', 'PurchaseOrder', 'SalesOrder', 'Invoice',
					));

				// Escribir $otherModules para que populateSharingtmptables() pueda usarlo
				$newbuf .= "\$otherModules=" . constructArray($custom_modules) . ";\n\n";

				for ($idx = 0; $idx < count ($custom_modules); ++$idx) {
					$module_name          = $custom_modules[ $idx ];
				$module_num = $idx + 1;
					$mod_share_perm_array = getUserModuleSharingObjects ($module_name, $userid,
						$def_org_share, $current_user_roles, $parent_roles, $current_user_groups);

					$mod_share_read_perm  = $mod_share_perm_array['read'];
					$mod_share_write_perm = $mod_share_perm_array['write'];
					$newbuf .= '$' . $module_name . "_share_read_permission=array('ROLE'=>" .
							   constructTwoDimensionalCharIntSingleValueArray ($mod_share_read_perm['ROLE']) . ",'GROUP'=>" .
							   constructTwoDimensionalArray ($mod_share_read_perm['GROUP']) . ");\n\n";
					$newbuf .= '$' . $module_name . "_share_write_permission=array('ROLE'=>" .
							   constructTwoDimensionalCharIntSingleValueArray ($mod_share_write_perm['ROLE']) . ",'GROUP'=>" .
							   constructTwoDimensionalArray ($mod_share_write_perm['GROUP']) . ");\n\n";
				}
				// END

				$newbuf .= "?>";
				fputs ($handle, $newbuf);
				fclose ($handle);

				//Populating Temp Tables
				populateSharingtmptables ($userid);
			}
		}
	}

	/** Gives an array which contains the information for what all roles, groups and user data is to be shared with the spcified user for the specified module
	 *
	 * @param $module -- module name:: Type varchar
	 * @param $userid -- user id:: Type integer
	 * @param $def_org_share -- default organization sharing permission array:: Type array
	 * @param $current_user_roles -- roleid:: Type varchar
	 * @param $parent_roles -- parent roles:: Type varchar
	 * @param $current_user_groups -- user id:: Type integer
	 * @returns $mod_share_permission -- array which contains the id of roles,group and users data shared with specifed user for the specified module
	 */
	function getUserModuleSharingObjects ($module, $userid, $def_org_share, $current_user_roles, $parent_roles, $current_user_groups) {
		global $adb;

		$mod_tabid = getTabid ($module);

		$mod_share_permission;
		$mod_share_read_permission           = Array ();
		$mod_share_write_permission          = Array ();
		$mod_share_read_permission['ROLE']   = Array ();
		$mod_share_write_permission['ROLE']  = Array ();
		$mod_share_read_permission['GROUP']  = Array ();
		$mod_share_write_permission['GROUP'] = Array ();

		$share_id_members      = Array ();
		$share_id_groupmembers = Array ();
		//If Sharing of leads is Private
		if ($def_org_share[ $mod_tabid ] == 3 || $def_org_share[ $mod_tabid ] == 0) {
			$role_read_per  = Array ();
			$role_write_per = Array ();
			$rs_read_per    = Array ();
			$rs_write_per   = Array ();
			$grp_read_per   = Array ();
			$grp_write_per  = Array ();
			//Retreiving from vtiger_role to vtiger_role
			$query    = "SELECT vtiger_datashare_role2role.* FROM vtiger_datashare_role2role INNER JOIN vtiger_datashare_module_rel ON vtiger_datashare_module_rel.shareid=vtiger_datashare_role2role.shareid WHERE vtiger_datashare_module_rel.tabid=? AND vtiger_datashare_role2role.to_roleid=?";
			$result   = $adb->pquery ($query, array ($mod_tabid, $current_user_roles));
			$num_rows = $adb->num_rows ($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_roleid = $adb->query_result ($result, $i, 'share_roleid');

				$shareid                       = $adb->query_result ($result, $i, 'shareid');
				$share_id_role_members         = Array ();
				$share_id_roles                = Array ();
				$share_id_roles[]              = $share_roleid;
				$share_id_role_members['ROLE'] = $share_id_roles;
				$share_id_members[ $shareid ]  = $share_id_role_members;

				$share_permission = $adb->query_result ($result, $i, 'permission');
				if ($share_permission == 1) {
					if ($def_org_share[ $mod_tabid ] == 3) {
						if (!array_key_exists ($share_roleid, $role_read_per)) {

							$share_role_users               = getRoleUserIds ($share_roleid);
							$role_read_per[ $share_roleid ] = $share_role_users;
						}
					}
					if (!array_key_exists ($share_roleid, $role_write_per)) {

						$share_role_users                = getRoleUserIds ($share_roleid);
						$role_write_per[ $share_roleid ] = $share_role_users;
					}
				} elseif ($share_permission == 0 && $def_org_share[ $mod_tabid ] == 3) {
					if (!array_key_exists ($share_roleid, $role_read_per)) {

						$share_role_users               = getRoleUserIds ($share_roleid);
						$role_read_per[ $share_roleid ] = $share_role_users;
					}
				}
			}

			//Retreiving from role to rs
			$parRoleList = array ();
			foreach ($parent_roles as $par_role_id) {
				array_push ($parRoleList, $par_role_id);
			}
			array_push ($parRoleList, $current_user_roles);
			$query    = "SELECT vtiger_datashare_role2rs.* FROM vtiger_datashare_role2rs INNER JOIN vtiger_datashare_module_rel ON vtiger_datashare_module_rel.shareid=vtiger_datashare_role2rs.shareid WHERE vtiger_datashare_module_rel.tabid=? AND vtiger_datashare_role2rs.to_roleandsubid IN (" . generateQuestionMarks ($parRoleList) . ")";
			$result   = $adb->pquery ($query, array ($mod_tabid, $parRoleList));
			$num_rows = $adb->num_rows ($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_roleid = $adb->query_result ($result, $i, 'share_roleid');

				$shareid                       = $adb->query_result ($result, $i, 'shareid');
				$share_id_role_members         = Array ();
				$share_id_roles                = Array ();
				$share_id_roles[]              = $share_roleid;
				$share_id_role_members['ROLE'] = $share_id_roles;
				$share_id_members[ $shareid ]  = $share_id_role_members;

				$share_permission = $adb->query_result ($result, $i, 'permission');
				if ($share_permission == 1) {
					if ($def_org_share[ $mod_tabid ] == 3) {
						if (!array_key_exists ($share_roleid, $role_read_per)) {

							$share_role_users               = getRoleUserIds ($share_roleid);
							$role_read_per[ $share_roleid ] = $share_role_users;
						}
					}
					if (!array_key_exists ($share_roleid, $role_write_per)) {

						$share_role_users                = getRoleUserIds ($share_roleid);
						$role_write_per[ $share_roleid ] = $share_role_users;
					}
				} elseif ($share_permission == 0 && $def_org_share[ $mod_tabid ] == 3) {
					if (!array_key_exists ($share_roleid, $role_read_per)) {

						$share_role_users               = getRoleUserIds ($share_roleid);
						$role_read_per[ $share_roleid ] = $share_role_users;
					}
				}
			}

			//Get roles from Role2Grp
			$grpIterator = false;
			$groupList   = $current_user_groups;
			if (empty($groupList)) {
				$groupList = array (0);
			}

			if (!empty($groupList)) {
				$query   = "SELECT vtiger_datashare_role2group.* FROM vtiger_datashare_role2group INNER JOIN vtiger_datashare_module_rel ON vtiger_datashare_module_rel.shareid=vtiger_datashare_role2group.shareid WHERE vtiger_datashare_module_rel.tabid=?";
				$qparams = array ($mod_tabid);

				if (count ($groupList) > 0) {
					$query .= " and vtiger_datashare_role2group.to_groupid in (" . generateQuestionMarks ($groupList) . ")";
					array_push ($qparams, $groupList);
				}
				$result   = $adb->pquery ($query, $qparams);
				$num_rows = $adb->num_rows ($result);
				for ($i = 0; $i < $num_rows; $i++) {
					$share_roleid                  = $adb->query_result ($result, $i, 'share_roleid');
					$shareid                       = $adb->query_result ($result, $i, 'shareid');
					$share_id_role_members         = Array ();
					$share_id_roles                = Array ();
					$share_id_roles[]              = $share_roleid;
					$share_id_role_members['ROLE'] = $share_id_roles;
					$share_id_members[ $shareid ]  = $share_id_role_members;

					$share_permission = $adb->query_result ($result, $i, 'permission');
					if ($share_permission == 1) {
						if ($def_org_share[ $mod_tabid ] == 3) {
							if (!array_key_exists ($share_roleid, $role_read_per)) {

								$share_role_users               = getRoleUserIds ($share_roleid);
								$role_read_per[ $share_roleid ] = $share_role_users;
							}
						}
						if (!array_key_exists ($share_roleid, $role_write_per)) {

							$share_role_users                = getRoleUserIds ($share_roleid);
							$role_write_per[ $share_roleid ] = $share_role_users;
						}
					} elseif ($share_permission == 0 && $def_org_share[ $mod_tabid ] == 3) {
						if (!array_key_exists ($share_roleid, $role_read_per)) {

							$share_role_users               = getRoleUserIds ($share_roleid);
							$role_read_per[ $share_roleid ] = $share_role_users;
						}
					}
				}
			}

			//Retreiving from rs to vtiger_role
			$query    = "SELECT vtiger_datashare_rs2role.* FROM vtiger_datashare_rs2role INNER JOIN vtiger_datashare_module_rel ON vtiger_datashare_module_rel.shareid=vtiger_datashare_rs2role.shareid WHERE vtiger_datashare_module_rel.tabid=? AND vtiger_datashare_rs2role.to_roleid=?";
			$result   = $adb->pquery ($query, array ($mod_tabid, $current_user_roles));
			$num_rows = $adb->num_rows ($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_rsid       = $adb->query_result ($result, $i, 'share_roleandsubid');
				$share_roleids    = getRoleAndSubordinatesRoleIds ($share_rsid);
				$share_permission = $adb->query_result ($result, $i, 'permission');

				$shareid               = $adb->query_result ($result, $i, 'shareid');
				$share_id_role_members = Array ();
				$share_id_roles        = Array ();
				foreach ($share_roleids as $share_roleid) {
					$share_id_roles[] = $share_roleid;

					if ($share_permission == 1) {
						if ($def_org_share[ $mod_tabid ] == 3) {
							if (!array_key_exists ($share_roleid, $role_read_per)) {

								$share_role_users               = getRoleUserIds ($share_roleid);
								$role_read_per[ $share_roleid ] = $share_role_users;
							}
						}
						if (!array_key_exists ($share_roleid, $role_write_per)) {

							$share_role_users                = getRoleUserIds ($share_roleid);
							$role_write_per[ $share_roleid ] = $share_role_users;
						}
					} elseif ($share_permission == 0 && $def_org_share[ $mod_tabid ] == 3) {
						if (!array_key_exists ($share_roleid, $role_read_per)) {

							$share_role_users               = getRoleUserIds ($share_roleid);
							$role_read_per[ $share_roleid ] = $share_role_users;
						}
					}
				}
				$share_id_role_members['ROLE'] = $share_id_roles;
				$share_id_members[ $shareid ]  = $share_id_role_members;
			}

			//Retreiving from rs to rs
			$parRoleList = array ();
			foreach ($parent_roles as $par_role_id) {
				array_push ($parRoleList, $par_role_id);
			}
			array_push ($parRoleList, $current_user_roles);
			$query    = "SELECT vtiger_datashare_rs2rs.* FROM vtiger_datashare_rs2rs INNER JOIN vtiger_datashare_module_rel ON vtiger_datashare_module_rel.shareid=vtiger_datashare_rs2rs.shareid WHERE vtiger_datashare_module_rel.tabid=? AND vtiger_datashare_rs2rs.to_roleandsubid IN (" . generateQuestionMarks ($parRoleList) . ")";
			$result   = $adb->pquery ($query, array ($mod_tabid, $parRoleList));
			$num_rows = $adb->num_rows ($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_rsid       = $adb->query_result ($result, $i, 'share_roleandsubid');
				$share_roleids    = getRoleAndSubordinatesRoleIds ($share_rsid);
				$share_permission = $adb->query_result ($result, $i, 'permission');

				$shareid               = $adb->query_result ($result, $i, 'shareid');
				$share_id_role_members = Array ();
				$share_id_roles        = Array ();
				foreach ($share_roleids as $share_roleid) {

					$share_id_roles[] = $share_roleid;

					if ($share_permission == 1) {
						if ($def_org_share[ $mod_tabid ] == 3) {
							if (!array_key_exists ($share_roleid, $role_read_per)) {

								$share_role_users               = getRoleUserIds ($share_roleid);
								$role_read_per[ $share_roleid ] = $share_role_users;
							}
						}
						if (!array_key_exists ($share_roleid, $role_write_per)) {

							$share_role_users                = getRoleUserIds ($share_roleid);
							$role_write_per[ $share_roleid ] = $share_role_users;
						}
					} elseif ($share_permission == 0 && $def_org_share[ $mod_tabid ] == 3) {
						if (!array_key_exists ($share_roleid, $role_read_per)) {

							$share_role_users               = getRoleUserIds ($share_roleid);
							$role_read_per[ $share_roleid ] = $share_role_users;
						}
					}
				}
				$share_id_role_members['ROLE'] = $share_id_roles;
				$share_id_members[ $shareid ]  = $share_id_role_members;
			}

			//Get roles from Rs2Grp

			$query   = "SELECT vtiger_datashare_rs2grp.* FROM vtiger_datashare_rs2grp INNER JOIN vtiger_datashare_module_rel ON vtiger_datashare_module_rel.shareid=vtiger_datashare_rs2grp.shareid WHERE vtiger_datashare_module_rel.tabid=?";
			$qparams = array ($mod_tabid);
			if (count ($groupList) > 0) {
				$query .= " and vtiger_datashare_rs2grp.to_groupid in (" . generateQuestionMarks ($groupList) . ")";
				array_push ($qparams, $groupList);
			}
			$result   = $adb->pquery ($query, $qparams);
			$num_rows = $adb->num_rows ($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_rsid       = $adb->query_result ($result, $i, 'share_roleandsubid');
				$share_roleids    = getRoleAndSubordinatesRoleIds ($share_rsid);
				$share_permission = $adb->query_result ($result, $i, 'permission');

				$shareid               = $adb->query_result ($result, $i, 'shareid');
				$share_id_role_members = Array ();
				$share_id_roles        = Array ();

				foreach ($share_roleids as $share_roleid) {

					$share_id_roles[] = $share_roleid;

					if ($share_permission == 1) {
						if ($def_org_share[ $mod_tabid ] == 3) {
							if (!array_key_exists ($share_roleid, $role_read_per)) {

								$share_role_users               = getRoleUserIds ($share_roleid);
								$role_read_per[ $share_roleid ] = $share_role_users;
							}
						}
						if (!array_key_exists ($share_roleid, $role_write_per)) {

							$share_role_users                = getRoleUserIds ($share_roleid);
							$role_write_per[ $share_roleid ] = $share_role_users;
						}
					} elseif ($share_permission == 0 && $def_org_share[ $mod_tabid ] == 3) {
						if (!array_key_exists ($share_roleid, $role_read_per)) {

							$share_role_users               = getRoleUserIds ($share_roleid);
							$role_read_per[ $share_roleid ] = $share_role_users;
						}
					}
				}
				$share_id_role_members['ROLE'] = $share_id_roles;
				$share_id_members[ $shareid ]  = $share_id_role_members;
			}
			$mod_share_read_permission['ROLE']  = $role_read_per;
			$mod_share_write_permission['ROLE'] = $role_write_per;

			//Retreiving from the grp2role sharing
			$query    = "SELECT vtiger_datashare_grp2role.* FROM vtiger_datashare_grp2role INNER JOIN vtiger_datashare_module_rel ON vtiger_datashare_module_rel.shareid=vtiger_datashare_grp2role.shareid WHERE vtiger_datashare_module_rel.tabid=? AND vtiger_datashare_grp2role.to_roleid=?";
			$result   = $adb->pquery ($query, array ($mod_tabid, $current_user_roles));
			$num_rows = $adb->num_rows ($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_grpid      = $adb->query_result ($result, $i, 'share_groupid');
				$share_permission = $adb->query_result ($result, $i, 'permission');

				$shareid              = $adb->query_result ($result, $i, 'shareid');
				$share_id_grp_members = Array ();
				$share_id_grps        = Array ();
				$share_id_grps[]      = $share_grpid;

				if ($share_permission == 1) {
					if ($def_org_share[ $mod_tabid ] == 3) {
						if (!array_key_exists ($share_grpid, $grp_read_per)) {
							$focusGrpUsers = new GetGroupUsers();
							$focusGrpUsers->getAllUsersInGroup ($share_grpid);
							$share_grp_users              = $focusGrpUsers->group_users;
							$share_grp_subgroups          = $focusGrpUsers->group_subgroups;
							$grp_read_per[ $share_grpid ] = $share_grp_users;
							foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
								if (!array_key_exists ($subgrpid, $grp_read_per)) {
									$grp_read_per[ $subgrpid ] = $subgrpusers;
								}
								if (!in_array ($subgrpid, $share_id_grps)) {
									$share_id_grps[] = $subgrpid;
								}
							}
						}
					}
					if (!array_key_exists ($share_grpid, $grp_write_per)) {
						$focusGrpUsers = new GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup ($share_grpid);
						$share_grp_users               = $focusGrpUsers->group_users;
						$grp_write_per[ $share_grpid ] = $share_grp_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!array_key_exists ($subgrpid, $grp_write_per)) {
								$grp_write_per[ $subgrpid ] = $subgrpusers;
							}
							if (!in_array ($subgrpid, $share_id_grps)) {
								$share_id_grps[] = $subgrpid;
							}
						}
					}
				} elseif ($share_permission == 0 && $def_org_share[ $mod_tabid ] == 3) {
					if (!array_key_exists ($share_grpid, $grp_read_per)) {
						$focusGrpUsers = new GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup ($share_grpid);
						$share_grp_users              = $focusGrpUsers->group_users;
						$grp_read_per[ $share_grpid ] = $share_grp_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!array_key_exists ($subgrpid, $grp_read_per)) {
								$grp_read_per[ $subgrpid ] = $subgrpusers;
							}
							if (!in_array ($subgrpid, $share_id_grps)) {
								$share_id_grps[] = $subgrpid;
							}
						}
					}
				}
				$share_id_grp_members['GROUP'] = $share_id_grps;
				$share_id_members[ $shareid ]  = $share_id_grp_members;
			}

			//Retreiving from the grp2rs sharing

			$query    = "SELECT vtiger_datashare_grp2rs.* FROM vtiger_datashare_grp2rs INNER JOIN vtiger_datashare_module_rel ON vtiger_datashare_module_rel.shareid=vtiger_datashare_grp2rs.shareid WHERE vtiger_datashare_module_rel.tabid=? AND vtiger_datashare_grp2rs.to_roleandsubid IN (" . generateQuestionMarks ($parRoleList) . ")";
			$result   = $adb->pquery ($query, array ($mod_tabid, $parRoleList));
			$num_rows = $adb->num_rows ($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_grpid      = $adb->query_result ($result, $i, 'share_groupid');
				$share_permission = $adb->query_result ($result, $i, 'permission');

				$shareid              = $adb->query_result ($result, $i, 'shareid');
				$share_id_grp_members = Array ();
				$share_id_grps        = Array ();
				$share_id_grps[]      = $share_grpid;

				if ($share_permission == 1) {
					if ($def_org_share[ $mod_tabid ] == 3) {
						if (!array_key_exists ($share_grpid, $grp_read_per)) {
							$focusGrpUsers = new GetGroupUsers();
							$focusGrpUsers->getAllUsersInGroup ($share_grpid);
							$share_grp_users              = $focusGrpUsers->group_users;
							$grp_read_per[ $share_grpid ] = $share_grp_users;

							foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
								if (!array_key_exists ($subgrpid, $grp_read_per)) {
									$grp_read_per[ $subgrpid ] = $subgrpusers;
								}
								if (!in_array ($subgrpid, $share_id_grps)) {
									$share_id_grps[] = $subgrpid;
								}
							}
						}
					}
					if (!array_key_exists ($share_grpid, $grp_write_per)) {
						$focusGrpUsers = new GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup ($share_grpid);
						$share_grp_users               = $focusGrpUsers->group_users;
						$grp_write_per[ $share_grpid ] = $share_grp_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!array_key_exists ($subgrpid, $grp_write_per)) {
								$grp_write_per[ $subgrpid ] = $subgrpusers;
							}
							if (!in_array ($subgrpid, $share_id_grps)) {
								$share_id_grps[] = $subgrpid;
							}
						}
					}
				} elseif ($share_permission == 0 && $def_org_share[ $mod_tabid ] == 3) {
					if (!array_key_exists ($share_grpid, $grp_read_per)) {
						$focusGrpUsers = new GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup ($share_grpid);
						$share_grp_users              = $focusGrpUsers->group_users;
						$grp_read_per[ $share_grpid ] = $share_grp_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!array_key_exists ($subgrpid, $grp_read_per)) {
								$grp_read_per[ $subgrpid ] = $subgrpusers;
							}
							if (!in_array ($subgrpid, $share_id_grps)) {
								$share_id_grps[] = $subgrpid;
							}
						}
					}
				}
				$share_id_grp_members['GROUP'] = $share_id_grps;
				$share_id_members[ $shareid ]  = $share_id_grp_members;
			}

			//Retreiving from the grp2grp sharing

			$query   = "SELECT vtiger_datashare_grp2grp.* FROM vtiger_datashare_grp2grp INNER JOIN vtiger_datashare_module_rel ON vtiger_datashare_module_rel.shareid=vtiger_datashare_grp2grp.shareid WHERE vtiger_datashare_module_rel.tabid=?";
			$qparams = array ($mod_tabid);
			if (count ($groupList) > 0) {
				$query .= " and vtiger_datashare_grp2grp.to_groupid in (" . generateQuestionMarks ($groupList) . ")";
				array_push ($qparams, $groupList);
			}
			$result   = $adb->pquery ($query, $qparams);
			$num_rows = $adb->num_rows ($result);
			for ($i = 0; $i < $num_rows; $i++) {
				$share_grpid      = $adb->query_result ($result, $i, 'share_groupid');
				$share_permission = $adb->query_result ($result, $i, 'permission');

				$shareid              = $adb->query_result ($result, $i, 'shareid');
				$share_id_grp_members = Array ();
				$share_id_grps        = Array ();
				$share_id_grps[]      = $share_grpid;

				if ($share_permission == 1) {
					if ($def_org_share[ $mod_tabid ] == 3) {
						if (!array_key_exists ($share_grpid, $grp_read_per)) {
							$focusGrpUsers = new GetGroupUsers();
							$focusGrpUsers->getAllUsersInGroup ($share_grpid);
							$share_grp_users              = $focusGrpUsers->group_users;
							$grp_read_per[ $share_grpid ] = $share_grp_users;
							foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
								if (!array_key_exists ($subgrpid, $grp_read_per)) {
									$grp_read_per[ $subgrpid ] = $subgrpusers;
								}
								if (!in_array ($subgrpid, $share_id_grps)) {
									$share_id_grps[] = $subgrpid;
								}
							}
						}
					}
					if (!array_key_exists ($share_grpid, $grp_write_per)) {
						$focusGrpUsers = new GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup ($share_grpid);
						$share_grp_users               = $focusGrpUsers->group_users;
						$grp_write_per[ $share_grpid ] = $share_grp_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!array_key_exists ($subgrpid, $grp_write_per)) {
								$grp_write_per[ $subgrpid ] = $subgrpusers;
							}
							if (!in_array ($subgrpid, $share_id_grps)) {
								$share_id_grps[] = $subgrpid;
							}
						}
					}
				} elseif ($share_permission == 0 && $def_org_share[ $mod_tabid ] == 3) {
					if (!array_key_exists ($share_grpid, $grp_read_per)) {
						$focusGrpUsers = new GetGroupUsers();
						$focusGrpUsers->getAllUsersInGroup ($share_grpid);
						$share_grp_users              = $focusGrpUsers->group_users;
						$grp_read_per[ $share_grpid ] = $share_grp_users;
						foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
							if (!array_key_exists ($subgrpid, $grp_read_per)) {
								$grp_read_per[ $subgrpid ] = $subgrpusers;
							}
							if (!in_array ($subgrpid, $share_id_grps)) {
								$share_id_grps[] = $subgrpid;
							}
						}
					}
				}
				$share_id_grp_members['GROUP'] = $share_id_grps;
				$share_id_members[ $shareid ]  = $share_id_grp_members;
			}
			$mod_share_read_permission['GROUP']  = $grp_read_per;
			$mod_share_write_permission['GROUP'] = $grp_write_per;
		}
		$mod_share_permission['read']         = $mod_share_read_permission;
		$mod_share_permission['write']        = $mod_share_write_permission;
		$mod_share_permission['sharingrules'] = $share_id_members;
		return $mod_share_permission;
	}

	/** Gives an array which contains the information for what all roles, groups and user's related module data that is to be shared  for the specified parent module and shared module
	 *
	 * @param $par_mod -- parent module name:: Type varchar
	 * @param $share_mod -- shared module name:: Type varchar
	 * @param $userid -- user id:: Type integer
	 * @param $def_org_share -- default organization sharing permission array:: Type array
	 * @param $mod_sharingrule_members -- Sharing Rule Members array:: Type array
	 * @param $$mod_share_read_per -- Sharing Module Read Permission array:: Type array
	 * @param $$mod_share_write_per -- Sharing Module Write Permission array:: Type array
	 * @returns $related_mod_sharing_permission; -- array which contains the id of roles,group and users related module data to be shared
	 */
	function getRelatedModuleSharingArray ($par_mod, $share_mod, $mod_sharingrule_members, $mod_share_read_per, $mod_share_write_per, $def_org_share) {

		global $adb;
		$related_mod_sharing_permission = Array ();
		$mod_share_read_permission      = Array ();
		$mod_share_write_permission     = Array ();

		$mod_share_read_permission['ROLE']   = Array ();
		$mod_share_write_permission['ROLE']  = Array ();
		$mod_share_read_permission['GROUP']  = Array ();
		$mod_share_write_permission['GROUP'] = Array ();

		$par_mod_id   = getTabid ($par_mod);
		$share_mod_id = getTabid ($share_mod);

		if ($def_org_share[ $share_mod_id ] == 3 || $def_org_share[ $share_mod_id ] == 0) {

			$role_read_per  = Array ();
			$role_write_per = Array ();
			$grp_read_per   = Array ();
			$grp_write_per  = Array ();

			foreach ($mod_sharingrule_members as $sharingid => $sharingInfoArr) {
				$query            = "SELECT vtiger_datashare_relatedmodule_permission.* FROM vtiger_datashare_relatedmodule_permission INNER JOIN vtiger_datashare_relatedmodules ON vtiger_datashare_relatedmodules.datashare_relatedmodule_id=vtiger_datashare_relatedmodule_permission.datashare_relatedmodule_id WHERE vtiger_datashare_relatedmodule_permission.shareid=? AND vtiger_datashare_relatedmodules.tabid=? AND vtiger_datashare_relatedmodules.relatedto_tabid=?";
				$result           = $adb->pquery ($query, array ($sharingid, $par_mod_id, $share_mod_id));
				$share_permission = $adb->query_result ($result, 0, 'permission');

				foreach ($sharingInfoArr as $shareType => $shareEntArr) {
					foreach ($shareEntArr as $key => $shareEntId) {
						if ($shareType == 'ROLE') {
							if ($share_permission == 1) {
								if ($def_org_share[ $share_mod_id ] == 3) {
									if (!array_key_exists ($shareEntId, $role_read_per)) {
										if (array_key_exists ($shareEntId, $mod_share_read_per['ROLE'])) {
											$share_role_users = $mod_share_read_per['ROLE'][ $shareEntId ];
										} elseif (array_key_exists ($shareEntId, $mod_share_write_per['ROLE'])) {
											$share_role_users = $mod_share_write_per['ROLE'][ $shareEntId ];
										} else {

											$share_role_users = getRoleUserIds ($shareEntId);
										}

										$role_read_per[ $shareEntId ] = $share_role_users;
									}
								}
								if (!array_key_exists ($shareEntId, $role_write_per)) {
									if (array_key_exists ($shareEntId, $mod_share_read_per['ROLE'])) {
										$share_role_users = $mod_share_read_per['ROLE'][ $shareEntId ];
									} elseif (array_key_exists ($shareEntId, $mod_share_write_per['ROLE'])) {
										$share_role_users = $mod_share_write_per['ROLE'][ $shareEntId ];
									} else {

										$share_role_users = getRoleUserIds ($shareEntId);
									}

									$role_write_per[ $shareEntId ] = $share_role_users;
								}
							} elseif ($share_permission == 0 && $def_org_share[ $share_mod_id ] == 3) {
								if (!array_key_exists ($shareEntId, $role_read_per)) {
									if (array_key_exists ($shareEntId, $mod_share_read_per['ROLE'])) {
										$share_role_users = $mod_share_read_per['ROLE'][ $shareEntId ];
									} elseif (array_key_exists ($shareEntId, $mod_share_write_per['ROLE'])) {
										$share_role_users = $mod_share_write_per['ROLE'][ $shareEntId ];
									} else {

										$share_role_users = getRoleUserIds ($shareEntId);
									}

									$role_read_per[ $shareEntId ] = $share_role_users;
								}
							}
						} elseif ($shareType == 'GROUP') {
							if ($share_permission == 1) {
								if ($def_org_share[ $share_mod_id ] == 3) {

									if (!array_key_exists ($shareEntId, $grp_read_per)) {
										if (array_key_exists ($shareEntId, $mod_share_read_per['GROUP'])) {
											$share_grp_users = $mod_share_read_per['GROUP'][ $shareEntId ];
										} elseif (array_key_exists ($shareEntId, $mod_share_write_per['GROUP'])) {
											$share_grp_users = $mod_share_write_per['GROUP'][ $shareEntId ];
										} else {
											$focusGrpUsers = new GetGroupUsers();
											$focusGrpUsers->getAllUsersInGroup ($shareEntId);
											$share_grp_users = $focusGrpUsers->group_users;

											foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
												if (!array_key_exists ($subgrpid, $grp_read_per)) {
													$grp_read_per[ $subgrpid ] = $subgrpusers;
												}
											}
										}

										$grp_read_per[ $shareEntId ] = $share_grp_users;
									}
								}
								if (!array_key_exists ($shareEntId, $grp_write_per)) {
									if (!array_key_exists ($shareEntId, $grp_write_per)) {
										if (array_key_exists ($shareEntId, $mod_share_read_per['GROUP'])) {
											$share_grp_users = $mod_share_read_per['GROUP'][ $shareEntId ];
										} elseif (array_key_exists ($shareEntId, $mod_share_write_per['GROUP'])) {
											$share_grp_users = $mod_share_write_per['GROUP'][ $shareEntId ];
										} else {
											$focusGrpUsers = new GetGroupUsers();
											$focusGrpUsers->getAllUsersInGroup ($shareEntId);
											$share_grp_users = $focusGrpUsers->group_users;
											foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
												if (!array_key_exists ($subgrpid, $grp_write_per)) {
													$grp_write_per[ $subgrpid ] = $subgrpusers;
												}
											}
										}

										$grp_write_per[ $shareEntId ] = $share_grp_users;
									}
								}
							} elseif ($share_permission == 0 && $def_org_share[ $share_mod_id ] == 3) {
								if (!array_key_exists ($shareEntId, $grp_read_per)) {
									if (array_key_exists ($shareEntId, $mod_share_read_per['GROUP'])) {
										$share_grp_users = $mod_share_read_per['GROUP'][ $shareEntId ];
									} elseif (array_key_exists ($shareEntId, $mod_share_write_per['GROUP'])) {
										$share_grp_users = $mod_share_write_per['GROUP'][ $shareEntId ];
									} else {
										$focusGrpUsers = new GetGroupUsers();
										$focusGrpUsers->getAllUsersInGroup ($shareEntId);
										$share_grp_users = $focusGrpUsers->group_users;
										foreach ($focusGrpUsers->group_subgroups as $subgrpid => $subgrpusers) {
											if (!array_key_exists ($subgrpid, $grp_read_per)) {
												$grp_read_per[ $subgrpid ] = $subgrpusers;
											}
										}
									}

									$grp_read_per[ $shareEntId ] = $share_grp_users;
								}
							}
						}
					}
				}
			}
			$mod_share_read_permission['ROLE']   = $role_read_per;
			$mod_share_write_permission['ROLE']  = $role_write_per;
			$mod_share_read_permission['GROUP']  = $grp_read_per;
			$mod_share_write_permission['GROUP'] = $grp_write_per;
		}

		$related_mod_sharing_permission['read']  = $mod_share_read_permission;
		$related_mod_sharing_permission['write'] = $mod_share_write_permission;
		return $related_mod_sharing_permission;
	}

	/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file
	 *
	 * @param $var -- input array:: Type array
	 * @returns $code -- contains the whole array in a single string:: Type array
	 */
	function constructArray ($var) {
		if (is_array ($var)) {
			$code = 'array(';
			foreach ($var as $key => $value) {
				$code .= "'" . $key . "'=>" . $value . ',';
			}
			$code .= ')';
			return $code;
		}
	}

	/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file
	 *
	 * @param $var -- input array:: Type array
	 * @returns $code -- contains the whole array in a single string:: Type array
	 */
	function constructSingleStringValueArray ($var) {

		$size = sizeof ($var);
		$i    = 1;
		if (is_array ($var)) {
			$code = 'array(';
			foreach ($var as $key => $value) {
				if ($i < $size) {
					$code .= $key . "=>'" . $value . "',";
				} else {
					$code .= $key . "=>'" . $value . "'";
				}
				$i++;
			}
			$code .= ')';
			return $code;
		}
	}

	/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file
	 *
	 * @param $var -- input array:: Type array
	 * @returns $code -- contains the whole array in a single string:: Type array
	 */
	function constructSingleStringKeyAndValueArray ($var) {

		$size = sizeof ($var);
		$i    = 1;
		if (is_array ($var)) {
			$code = 'array(';
			foreach ($var as $key => $value) {
				if ($i < $size) {
					$code .= "'" . $key . "'=>" . $value . ",";
				} else {
					$code .= "'" . $key . "'=>" . $value;
				}
				$i++;
			}
			$code .= ')';
			return $code;
		}
	}

	/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file
	 *
	 * @param $var -- input array:: Type array
	 * @returns $code -- contains the whole array in a single string:: Type array
	 */
	function constructSingleStringKeyValueArray ($var) {
		global $adb;
		$size = sizeof ($var);
		$i    = 1;
		if (is_array ($var)) {
			$code = 'array(';
			foreach ($var as $key => $value) {
				//fix for signatue quote(') issue
				$value = $adb->sql_escape_string ($value);
				if ($i < $size) {
					$code .= "'" . $key . "'=>'" . $value . "',";
				} else {
					$code .= "'" . $key . "'=>'" . $value . "'";
				}
				$i++;
			}
			$code .= ')';
			return $code;
		}
	}

	/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file
	 *
	 * @param $var -- input array:: Type array
	 * @returns $code -- contains the whole array in a single string:: Type array
	 */
	function constructSingleArray ($var) {
		if (is_array ($var)) {
			$code = 'array(';
			foreach ($var as $value) {
				$code .= $value . ',';
			}
			$code .= ')';
			return $code;
		}
	}

	/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file
	 *
	 * @param $var -- input array:: Type array
	 * @returns $code -- contains the whole array in a single string:: Type array
	 */
	function constructSingleCharArray ($var) {
		if (is_array ($var)) {
			$code = "array(";
			foreach ($var as $value) {
				$code .= "'" . $value . "',";
			}
			$code .= ")";
			return $code;
		}
	}

	/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file
	 *
	 * @param $var -- input array:: Type array
	 * @returns $code -- contains the whole array in a single string:: Type array
	 */
	function constructTwoDimensionalArray ($var) {
		if (is_array ($var)) {
			$code = 'array(';
			foreach ($var as $key => $secarr) {
				$code .= $key . '=>array(';
				foreach ($secarr as $seckey => $secvalue) {
					$code .= $seckey . '=>' . $secvalue . ',';
				}
				$code .= '),';
			}
			$code .= ')';
			return $code;
		}
	}

	/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file
	 *
	 * @param $var -- input array:: Type array
	 * @returns $code -- contains the whole array in a single string:: Type array
	 */
	function constructTwoDimensionalValueArray ($var) {
		if (is_array ($var)) {
			$code = 'array(';
			foreach ($var as $key => $secarr) {
				$code .= $key . '=>array(';
				foreach ($secarr as $seckey => $secvalue) {
					$code .= $secvalue . ',';
				}
				$code .= '),';
			}
			$code .= ')';
			return $code;
		}
	}

	/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file
	 *
	 * @param $var -- input array:: Type array
	 * @returns $code -- contains the whole array in a single string:: Type array
	 */
	function constructTwoDimensionalCharIntSingleArray ($var) {
		if (is_array ($var)) {
			$code = "array(";
			foreach ($var as $key => $secarr) {
				$code .= "'" . $key . "'=>array(";
				foreach ($secarr as $seckey => $secvalue) {
					$code .= $seckey . ",";
				}
				$code .= "),";
			}
			$code .= ")";
			return $code;
		}
	}

	/** Converts the input array  to a single string to facilitate the writing of the input array in a flat file
	 *
	 * @param $var -- input array:: Type array
	 * @returns $code -- contains the whole array in a single string:: Type array
	 */
	function constructTwoDimensionalCharIntSingleValueArray ($var) {
		if (is_array ($var)) {
			$code = "array(";
			foreach ($var as $key => $secarr) {
				$code .= "'" . $key . "'=>array(";
				foreach ($secarr as $seckey => $secvalue) {
					$code .= $secvalue . ",";
				}
				$code .= "),";
			}
			$code .= ")";
			return $code;
		}
	}

	/** Function to populate the read/wirte Sharing permissions data of user/groups for the specified user into the database
	 *
	 * @param $userid -- user id:: Type integer
	 */

	function populateSharingtmptables ($userid) {
		global $adb, $current_user;
		$local_user     = clone $current_user;
		$local_user->id = $userid;
		require ('user_privileges/sharing_privileges.php');
		//Deleting from the existing vtiger_tables
		$table_arr = Array ('vtiger_tmp_read_user_sharing_per', 'vtiger_tmp_write_user_sharing_per', 'vtiger_tmp_read_group_sharing_per', 'vtiger_tmp_write_group_sharing_per', 'vtiger_tmp_read_user_rel_sharing_per', 'vtiger_tmp_write_user_rel_sharing_per', 'vtiger_tmp_read_group_rel_sharing_per', 'vtiger_tmp_write_group_rel_sharing_per');
		foreach ($table_arr as $tabname) {
			$query = "DELETE FROM " . $tabname . " WHERE userid=?";
			$adb->pquery ($query, array ($userid));
		}

		// Look up for modules for which sharing access is enabled.
		$sharingArray = $otherModules;

		foreach ($sharingArray as $module) {
			$module_sharing_read_permvar  = $module . '_share_read_permission';
			$module_sharing_write_permvar = $module . '_share_write_permission';

			populateSharingPrivileges ('USER', $userid, $module, 'read', $$module_sharing_read_permvar);
			populateSharingPrivileges ('USER', $userid, $module, 'write', $$module_sharing_write_permvar);
			populateSharingPrivileges ('GROUP', $userid, $module, 'read', $$module_sharing_read_permvar);
			populateSharingPrivileges ('GROUP', $userid, $module, 'write', $$module_sharing_write_permvar);
		}
		//Populating Values into the temp related sharing tables
		foreach ($related_module_share as $rel_tab_id => $tabid_arr) {
			$rel_tab_name = getTabname ($rel_tab_id);
			foreach ($tabid_arr as $taid) {
				$tab_name = getTabname ($taid);

				$relmodule_sharing_read_permvar  = $tab_name . '_' . $rel_tab_name . '_share_read_permission';
				$relmodule_sharing_write_permvar = $tab_name . '_' . $rel_tab_name . '_share_write_permission';

				populateRelatedSharingPrivileges ('USER', $userid, $tab_name, $rel_tab_name, 'read', $$relmodule_sharing_read_permvar);
				populateRelatedSharingPrivileges ('USER', $userid, $tab_name, $rel_tab_name, 'write', $$relmodule_sharing_write_permvar);
				populateRelatedSharingPrivileges ('GROUP', $userid, $tab_name, $rel_tab_name, 'read', $$relmodule_sharing_read_permvar);
			}
		}
	}

	/** Function to populate the read/wirte Sharing permissions data for the specified user into the database
	 *
	 * @param $userid -- user id:: Type integer
	 * @param $enttype -- can have the value of User or Group:: Type varchar
	 * @param $module -- module name:: Type varchar
	 * @param $pertype -- can have the value of read or write:: Type varchar
	 * @param $var_name_arr - Variable to use instead of including the sharing access again
	 */
	function populateSharingPrivileges ($enttype, $userid, $module, $pertype, $var_name_arr = false) {
		global $adb;
		$tabid = getTabid ($module);
		$insert_count = 0;

		if (!$var_name_arr) {
			require ('user_privileges/sharing_privileges_' . $userid . '.php');
		}

		if ($enttype == 'USER') {
			if ($pertype == 'read') {
				$table_name = 'vtiger_tmp_read_user_sharing_per';
				$var_name   = $module . '_share_read_permission';
			} elseif ($pertype == 'write') {
				$table_name = 'vtiger_tmp_write_user_sharing_per';
				$var_name   = $module . '_share_write_permission';
			}
			// Lookup for the variable if not set through function argument
			if (!$var_name_arr) {
				$var_name_arr = $$var_name;
			}
			$user_arr = Array ();
			if (sizeof ($var_name_arr['ROLE']) > 0) {
				foreach ($var_name_arr['ROLE'] as $roleid => $roleusers) {

					foreach ($roleusers as $user_id) {
						if (!in_array ($user_id, $user_arr)) {
							$query = "INSERT INTO " . $table_name . " VALUES(?,?,?)";
							$adb->pquery ($query, array ($userid, $tabid, $user_id));
							$user_arr[] = $user_id;
							$insert_count++;
						}
					}
				}
			}
			if (sizeof ($var_name_arr['GROUP']) > 0) {
				foreach ($var_name_arr['GROUP'] as $grpid => $grpusers) {
					foreach ($grpusers as $user_id) {
						if (!in_array ($user_id, $user_arr)) {
							$query = "INSERT INTO " . $table_name . " VALUES(?,?,?)";
							$adb->pquery ($query, array ($userid, $tabid, $user_id));
							$user_arr[] = $user_id;
							$insert_count++;
						}
					}
				}
			}
		} elseif ($enttype == 'GROUP') {
			if ($pertype == 'read') {
				$table_name = 'vtiger_tmp_read_group_sharing_per';
				$var_name   = $module . '_share_read_permission';
			} elseif ($pertype == 'write') {
				$table_name = 'vtiger_tmp_write_group_sharing_per';
				$var_name   = $module . '_share_write_permission';
			}
			// Lookup for the variable if not set through function argument
			if (!$var_name_arr) {
				$var_name_arr = $$var_name;
			}
			$grp_arr = Array ();
			if (sizeof ($var_name_arr['GROUP']) > 0) {

				foreach ($var_name_arr['GROUP'] as $grpid => $grpusers) {
					if (!in_array ($grpid, $grp_arr)) {
						$query = "INSERT INTO " . $table_name . " VALUES(?,?,?)";
						$adb->pquery ($query, array ($userid, $tabid, $grpid));
						$grp_arr[] = $grpid;
						$insert_count++;
					}
				}
			}
		}
	}

	/** Function to populate the read/wirte Sharing permissions related module data for the specified user into the database
	 *
	 * @param $userid -- user id:: Type integer
	 * @param $enttype -- can have the value of User or Group:: Type varchar
	 * @param $module -- module name:: Type varchar
	 * @param $relmodule -- related module name:: Type varchar
	 * @param $pertype -- can have the value of read or write:: Type varchar
	 * @param $var_name_arr - Variable to use instead of including the sharing access again
	 */

	function populateRelatedSharingPrivileges ($enttype, $userid, $module, $relmodule, $pertype, $var_name_arr = false) {
		global $adb, $current_user;
		$tabid    = getTabid ($module);
		$reltabid = getTabid ($relmodule);

		if (!$var_name_arr) {
			$local_user     = clone $current_user;
			$local_user->id = $userid;
			require ('user_privileges/sharing_privileges.php');
		}

		if ($enttype == 'USER') {
			if ($pertype == 'read') {
				$table_name = 'vtiger_tmp_read_user_rel_sharing_per';
				$var_name   = $module . '_' . $relmodule . '_share_read_permission';
			} elseif ($pertype == 'write') {
				$table_name = 'vtiger_tmp_write_user_rel_sharing_per';
				$var_name   = $module . '_' . $relmodule . '_share_write_permission';
			}
			// Lookup for the variable if not set through function argument
			if (!$var_name_arr) {
				$var_name_arr = $$var_name;
			}
			$user_arr = Array ();
			if (sizeof ($var_name_arr['ROLE']) > 0) {
				foreach ($var_name_arr['ROLE'] as $roleid => $roleusers) {

					foreach ($roleusers as $user_id) {
						if (!in_array ($user_id, $user_arr)) {
							$query = "INSERT INTO " . $table_name . " VALUES(?,?,?,?)";
							$adb->pquery ($query, array ($userid, $tabid, $reltabid, $user_id));
							$user_arr[] = $user_id;
						}
					}
				}
			}
			if (sizeof ($var_name_arr['GROUP']) > 0) {
				foreach ($var_name_arr['GROUP'] as $grpid => $grpusers) {
					foreach ($grpusers as $user_id) {
						if (!in_array ($user_id, $user_arr)) {
							$query = "INSERT INTO " . $table_name . " VALUES(?,?,?,?)";
							$adb->pquery ($query, array ($userid, $tabid, $reltabid, $user_id));
							$user_arr[] = $user_id;
						}
					}
				}
			}
		} elseif ($enttype == 'GROUP') {
			if ($pertype == 'read') {
				$table_name = 'vtiger_tmp_read_group_rel_sharing_per';
				$var_name   = $module . '_' . $relmodule . '_share_read_permission';
			} elseif ($pertype == 'write') {
				$table_name = 'vtiger_tmp_write_group_rel_sharing_per';
				$var_name   = $module . '_' . $relmodule . '_share_write_permission';
			}
			// Lookup for the variable if not set through function argument
			if (!$var_name_arr) {
				$var_name_arr = $$var_name;
			}
			$grp_arr = Array ();
			if (sizeof ($var_name_arr['GROUP']) > 0) {

				foreach ($var_name_arr['GROUP'] as $grpid => $grpusers) {
					if (!in_array ($grpid, $grp_arr)) {
						$query = "INSERT INTO " . $table_name . " VALUES(?,?,?,?)";
						$adb->pquery ($query, array ($userid, $tabid, $reltabid, $grpid));
						$grp_arr[] = $grpid;
					}
				}
			}
		}
	}

?>
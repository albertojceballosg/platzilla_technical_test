<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	abstract class UserUtils {

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		private static function getCurrentUserWriteSharingGroupIds (PearDatabase $adb, $moduleName) {
			global $current_user;
			$result = $adb->pquery (
				'SELECT
					vtiger_tmp_write_group_sharing_per.sharedgroupid
				FROM
					vtiger_tmp_write_group_sharing_per
					INNER JOIN vtiger_tab ON vtiger_tab.tabid=vtiger_tmp_write_group_sharing_per.tabid AND vtiger_tab.name=?
				WHERE
					vtiger_tmp_write_group_sharing_per.userid=?',
				array ($moduleName, $current_user->id)
			);
			if ($adb->num_rows ($result) > 0) {
				$groupIds = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$groupIds [] = intval ($row ['sharedgroupid']);
				}
			} else {
				$groupIds = null;
			}
			return $groupIds;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 */
		public static function getCurrentUserAccessGroups (PearDatabase $adb, $moduleName) {
			$userGroupIds    = self::getCurrentUserGroupIds ();
			$sharingGroupIds = self::getCurrentUserWriteSharingGroupIds ($adb, $moduleName);
			if ((empty ($userGroupIds)) && (empty ($sharingGroupIds))) {
				return null;
			} else if ((!empty ($userGroupIds)) && (!empty ($sharingGroupIds))) {
				$arguments = array_unique (array_merge ($userGroupIds, $sharingGroupIds));
			} else if (!empty ($userGroupIds)) {
				$arguments = $userGroupIds;
			} else {
				$arguments = $sharingGroupIds;
			}
			$questionMarks = str_repeat ('?, ', (count ($arguments) - 1)) . '?';
			$result        = $adb->pquery (
				"SELECT
					groupname,
					groupid
				FROM
					vtiger_groups
				WHERE
					groupid IN ({$questionMarks})",
				$arguments
			);
			if ($adb->num_rows ($result) > 0) {
				$groups = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					$groups [ $row ['groupid'] ] = $row ['groupname'];
				}
			} else {
				$groups = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $groups;
		}

		/**
		 * @return array|null
		 */
		public static function getCurrentUserGroupIds () {
			global $current_user_groups;
			require ('user_privileges/current_user_privileges.php');

			if (count ($current_user_groups) > 0) {
				$groups = array ();
				foreach ($current_user_groups as $groupId) {
					$groups [] = $groupId;
				}
			} else {
				$groups = null;
			}
			return $groups;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 */
		public static function getGroups (PearDatabase $adb) {
			$result = $adb->query ('SELECT groupname, groupid FROM vtiger_groups');
			if ($adb->num_rows ($result) > 0) {
				$groups = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					$groups [ $row ['groupid'] ] = $row ['groupname'];
				}
			} else {
				$groups = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $groups;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param Users $currentUser
		 * @param integer $assignedUserId
		 * @param boolean $onlyPrivate
		 *
		 * @return array|null
		 */
		public static function getModuleUsers (PearDatabase $adb, $moduleName, $currentUser, $assignedUserId = null, $onlyPrivate = false) {
			global $current_user_parent_role_seq;
			require ('user_privileges/current_user_privileges.php');

			if ($onlyPrivate) {
				if (!empty ($assignedUserId)) {
					$additionalWhereClause = 'OR vtiger_users.id=?';
					$additionalArguments   = array ($assignedUserId);
				} else {
					$additionalWhereClause = '';
					$additionalArguments   = array ();
				}
				$result = $adb->pquery (
					"SELECT
						vtiger_users.id,
						vtiger_users.user_name,
						vtiger_users.first_name,
						vtiger_users.last_name
					FROM
						vtiger_users
					WHERE
						vtiger_users.id=? AND
						vtiger_users.status=?
					UNION
					SELECT DISTINCT
						vtiger_user2role.userid AS id,
						vtiger_users.user_name,
						vtiger_users.first_name,
						vtiger_users.last_name
					FROM
						vtiger_user2role
						INNER JOIN vtiger_role2profile ON vtiger_role2profile.roleid=vtiger_user2role.roleid
						INNER JOIN vtiger_profile2tab ON vtiger_profile2tab.profileid=vtiger_role2profile.profileid AND vtiger_profile2tab.permissions=0
						INNER JOIN vtiger_tab ON vtiger_tab.tabid=vtiger_profile2tab.tabid AND vtiger_tab.name=?
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid AND vtiger_users.status=?
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid AND vtiger_role.parentrole LIKE ?
					UNION
					SELECT
						vtiger_tmp_write_user_sharing_per.shareduserid AS id,
						vtiger_users.user_name,
						vtiger_users.first_name,
						vtiger_users.last_name
					FROM
						vtiger_tmp_write_user_sharing_per
						INNER JOIN vtiger_tab ON vtiger_tab.tabid=vtiger_tmp_write_user_sharing_per.tabid AND vtiger_tab.name=?
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_tmp_write_user_sharing_per.shareduserid AND LENGTH(vtiger_users.user_name)>0 AND vtiger_users.status=?
					WHERE
						vtiger_tmp_write_user_sharing_per.userid=?
						{$additionalWhereClause}
					ORDER BY
						user_name ASC",
					array_merge (array ($currentUser->id, 'Active', $moduleName, 'Active', "{$current_user_parent_role_seq}::%", $moduleName, 'Active', $currentUser->id), $additionalArguments)
				);
			} else {
				$result = $adb->pquery (
					'SELECT DISTINCT
						vtiger_user2role.userid AS id,
						vtiger_users.user_name,
						vtiger_users.first_name,
						vtiger_users.last_name
					FROM
						vtiger_user2role
						INNER JOIN vtiger_role2profile ON vtiger_role2profile.roleid=vtiger_user2role.roleid
						INNER JOIN vtiger_profile2tab ON vtiger_profile2tab.profileid=vtiger_role2profile.profileid AND vtiger_profile2tab.permissions=0
						INNER JOIN vtiger_tab ON vtiger_tab.tabid=vtiger_profile2tab.tabid AND vtiger_tab.name=?
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid AND vtiger_users.status=?
					ORDER BY
						vtiger_users.user_name ASC',
					array ($moduleName, 'Active')
				);
			}
			if ($adb->num_rows ($result) > 0) {
				$users = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					$users [ $row ['id'] ] = trim ("{$row ['first_name']} {$row ['last_name']}");
				}
			} else {
				$users = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $users;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 * @param Users $currentUser
		 * @param string $status
		 * @param integer $assignedUserId
		 * @param boolean $onlyPrivate
		 *
		 * @return array|null
		 */
		public static function getUsers (PearDatabase $adb, $moduleName, $currentUser, $status = null, $assignedUserId = null, $onlyPrivate = false) {
			global $current_user_parent_role_seq;
			require ('user_privileges/current_user_privileges.php');

			if (empty ($status)) {
				$result = $adb->query ('SELECT id, user_name FROM vtiger_users');
			} else if ($onlyPrivate) {
				if (!empty ($assignedUserId)) {
					$additionalWhereClause = 'OR vtiger_users.id=?';
					$additionalArguments   = array ($assignedUserId);
				} else {
					$additionalWhereClause = '';
					$additionalArguments   = array ();
				}
				$result = $adb->pquery (
					"SELECT
						vtiger_users.id,
						vtiger_users.user_name,
						vtiger_users.first_name,
						vtiger_users.last_name
					FROM
						vtiger_users
					WHERE
						vtiger_users.id=? AND
						vtiger_users.status=?
					UNION
					SELECT
						vtiger_user2role.userid AS id,
						vtiger_users.user_name,
						vtiger_users.first_name,
						vtiger_users.last_name
					FROM
						vtiger_user2role
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid AND vtiger_users.status=?
						INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid AND vtiger_role.parentrole LIKE ?
					UNION
					SELECT
						vtiger_tmp_write_user_sharing_per.shareduserid AS id,
						vtiger_users.user_name,
						vtiger_users.first_name,
						vtiger_users.last_name
					FROM
						vtiger_tmp_write_user_sharing_per
						INNER JOIN vtiger_tab ON vtiger_tab.tabid=vtiger_tmp_write_user_sharing_per.tabid AND vtiger_tab.name=?
						INNER JOIN vtiger_users ON vtiger_users.id=vtiger_tmp_write_user_sharing_per.shareduserid AND LENGTH(vtiger_users.user_name)>0 AND vtiger_users.status=?
					WHERE
						vtiger_tmp_write_user_sharing_per.userid=?
						{$additionalWhereClause}
					ORDER BY
						user_name ASC",
					array_merge (array ($currentUser->id, 'Active', 'Active', "{$current_user_parent_role_seq}::%", $moduleName, 'Active', $currentUser->id), $additionalArguments)
				);
			} else {
				$result = $adb->pquery (
					'SELECT id, user_name, first_name, last_name FROM vtiger_users WHERE LENGTH(vtiger_users.user_name)>0 AND status=? ORDER BY user_name ASC',
					array ($status)
				);
			}
			if ($adb->num_rows ($result) > 0) {
				$users = array ();
				while ($row = $adb->fetchByAssoc ($result)) {
					$users [ $row ['id'] ] = trim ("{$row ['first_name']} {$row ['last_name']}");
				}
			} else {
				$users = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $users;
		}

	}

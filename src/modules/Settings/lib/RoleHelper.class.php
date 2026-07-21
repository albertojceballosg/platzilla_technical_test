<?php
	require_once ('include/platzilla/Managers/ProfileManager.php');
	require_once ('include/platzilla/Managers/RoleManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('modules/Users/Users.php');

	abstract class RoleHelper {

		public static function buildRoleHierarchy (PearDatabase $adb) {
			$result = $adb->query ('SELECT * FROM vtiger_role ORDER BY parentrole ASC');
			if ((!$result) && ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			$roleHierarchy = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$parentRoles = array_reverse (explode ('::', $row ['parentrole']));
				$tmp         = array ();
				foreach ($parentRoles as $parentRole) {
					$tmp = array ($parentRole => $tmp);
				}
				$roleHierarchy = array_merge_recursive ($roleHierarchy, $tmp);
			}
			return $roleHierarchy;
		}

		public static function fetchAvailableProfiles (PearDatabase $adb) {
			return ProfileManager::getInstance ($adb)->fetchProfiles (true);
		}

		public static function fetchRole (PearDatabase $adb, $roleId, $headersOnly = false) {
			return RoleManager::getInstance ($adb)->fetchRole ($roleId, $headersOnly);
		}

		public static function fetchRoleUsers (PearDatabase $adb, $platform, $roleId) {
			return UserManager::getInstance ($adb, $platform)->fetchUsersByRole ($roleId);
		}

		public static function fetchSelectedProfiles (PearDatabase $adb, $profileIds) {
			if (empty ($profileIds)) {
				return null;
			}

			$pm = ProfileManager::getInstance ($adb);
			$profiles = array ();
			foreach ($profileIds as $profileId) {
				$profiles [] = $pm->fetchProfileById ($profileId, true);
			}
			return $profiles;
		}

		public static function getRoleDetails ($roleId) {
			// Constructing the Profile list
			$roleProfiles = getRoleRelatedProfiles ($roleId);
			$profiles     = array ();
			foreach ($roleProfiles as $profileId => $profileName) {
				$profiles [] = $profileId;
				$profiles [] = $profileName;
			}
			$profiles = array_chunk ($profiles, 2);

			// Constructing the Users List
			$roleUsers = getRoleUsers ($roleId);
			$users     = array ();
			foreach ($roleUsers as $userId => $userName) {
				/** @var Users|stdClass $user */
				$user     = new Users ();
				$user->id = $userId;
				$user->retrieve_entity_info ($userId, 'Users');

				$users [] = $userId;
				$users [] = $userName;
				$users [] = getUserImageName ($userId);
				$users [] = $user->column_fields ['status'];
			}
			$users = array_chunk ($users, 4);

			return array (
				'profileinfo' => $profiles,
				'userinfo'    => $users,
			);
		}

		public static function getAllRoleDetails () {
			return getAllRoleDetails ();
		}

		public static function saveRole (Peardatabase $adb, $arguments) {
			$rm = RoleManager::getInstance ($adb);
			if (!empty ($arguments ['roleid'])) {
				$role = $rm->fetchRole ($arguments ['roleid']);
			}
			if (empty ($role)) {
				$role = Role::getInstance ();
			}

			if (!empty ($arguments ['profileids'])) {
				$profiles = array ();
				$pm = ProfileManager::getInstance ($adb);
				foreach ($arguments ['profileids'] as $profileId) {
					$profiles [] = $pm->fetchProfileById ($profileId, true);
				}
			} else {
				$profiles = null;
			}
			$role->setName ($arguments ['rolename'])
				->setParent ($rm->fetchRole ($arguments ['parentroleid']))
				->setProfiles ($profiles);
			$rm->saveRole ($role);

		}

	}

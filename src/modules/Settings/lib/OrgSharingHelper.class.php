<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/utils/utils.php');

	abstract class OrgSharingHelper {

		private static function getModuleDataById (PearDatabase $adb, $moduleId) {
			$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE tabid=?', array ($moduleId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			return $adb->fetchByAssoc ($result, -1, false);
		}

		private static function getModuleDataByName (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			return $adb->fetchByAssoc ($result, -1, false);
		}

		private static function getEntityName ($entityType, $entityId) {
			if ($entityType == 'groups') {
				$entityName = "groups::{$entityId}";
			} else if ($entityType == 'roles') {
				$entityName = "roles::{$entityId}";
			} else if ($entityType == 'rs') {
				$entityName = "rs::{$entityId}";
			} else {
				$entityName = null;
			}
			return $entityName;
		}

		public static function getAccessPrivileges (PearDatabase $adb, $dictionary, $availableModuleNames) {
			$defaultSharingActions = getDefaultSharingAction ();
			$accessPrivileges      = array ();
			foreach ($defaultSharingActions as $moduleId => $shareActionId) {
				$moduleData = self::getModuleDataById ($adb, $moduleId);
				if ((empty ($moduleData)) || (!is_array ($moduleData)) || ((!empty ($availableModuleNames)) && (!in_array ($moduleData ['name'], $availableModuleNames)))) {
					continue;
				}

				/** @var string $defaultShareAction */
				$defaultShareAction = getDefOrgShareActionName ($shareActionId);

				$accessPrivileges [] = "{$moduleData ['name']}";
				$accessPrivileges [] = $defaultShareAction;
				if ($defaultShareAction != 'Private') {
					$accessPrivileges [] = "{$dictionary ["LBL_DESCRIPTION_{$defaultShareAction}"]}";
				} else {
					$accessPrivileges [] = "{$dictionary ['LBL_USR_CANNOT_ACCESS']}";
				}
			}
			return array_chunk ($accessPrivileges, 3);
		}

		public static function getModulesDataByName (PearDatabase $adb) {
			$result = $adb->pquery ('SELECT * FROM vtiger_tab WHERE presence=? AND customized=? AND isentitytype=?', array (0, 1, 1));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$modules = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$modules [ $row ['name'] ] = $row;
			}
			return $modules;
		}

		public static function getSharingRules (PearDatabase $adb, $moduleName) {
			$dataShareTableArray = getDataShareTableandColumnArray ();
			if (empty ($dataShareTableArray)) {
				return null;
			}

			$moduleData        = self::getModuleDataByName ($adb, $moduleName);
			$accessPermissions = array ();
			foreach ($dataShareTableArray as $tableName => $columnName) {
				$columnNameData = explode ('::', $columnName);
				$result         = $adb->pquery (
					"SELECT t.* FROM {$tableName} t INNER JOIN vtiger_datashare_module_rel dsmr on dsmr.shareid=t.shareid WHERE dsmr.tabid=?",
					array ($moduleData ['tabid'])
				);
				if ((!$result) || ($adb->num_rows ($result) == 0)) {
					continue;
				}

				$fromEntityType = getEntityTypeFromCol ($columnNameData [0]);
				$toEntityType   = getEntityTypeFromCol ($columnNameData [1]);

				while ($row = $adb->fetchByAssoc ($result)) {
					$accessPermissions [ $moduleData ['name'] ][] = array (
						'shareid'    => $row ['shareid'],
						'from'       => self::getEntityName ($fromEntityType, $row [ $columnNameData [0] ]),
						'to'         => self::getEntityName ($toEntityType, $row [ $columnNameData [1] ]),
						'permission' => $row ['permission'],
					);
				}
			}
			return count ($accessPermissions) > 0 ? $accessPermissions : null;
		}

	}

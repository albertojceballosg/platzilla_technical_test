<?php

	abstract class ConfigRelationshipsHelper {

		public static function getCombinableModuleName (PearDatabase $adb, $platformId, $moduleName) {
			$result = $adb->pquery (
				'SELECT DISTINCT module_base FROM vtiger_relationsship_plat_modules WHERE relationsship_platid=? AND module=?',
				array ($platformId, $moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $adb->fetchByAssoc ($result);
			return $row ['module_base'];
		}

		public static function getCombinableModulePermissions (PearDatabase $adb, $platformId, $moduleName) {
			$result = $adb->pquery (
				'SELECT pm.view, pm.replication, pm.update FROM vtiger_relationsship_plat_modules pm WHERE pm.relationsship_platid=? AND pm.module=?',
				array ($platformId, $moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array (
					'replication' => 0,
					'update'      => 0,
					'view'        => 0,
				);
			}

			$row = $adb->fetchByAssoc ($result);
			return array (
				'replication' => $row ['replication'] ? $row ['replication'] : 0,
				'update'      => $row ['update'] ? $row ['update'] : 0,
				'view'        => $row ['view'] ? $row ['view'] : 0,
			);
		}

		public static function getRelatedPlatformData (PearDatabase $adb, $platformId) {
			$result = $adb->pquery (
				'SELECT
					pp.plat_base,
					pp.plat_hija,
					i.name AS name_plat_base
				FROM
					vtiger_relationsship_plat_plat pp
					LEFT JOIN vtiger_instances i ON i.code=pp.plat_base
				WHERE
					pp.relationsship_plat_platid=?',
				array ($platformId)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			return $adb->fetchByAssoc ($result);
		}

		public static function getRelationshipData (PearDatabase $adb, $instanceName) {
			$result = $adb->pquery (
				'SELECT
					p.relationsship_platid,
					p.plat,
					p.name,
					p.user,
					p.pass,
					p.childplat
				FROM
					vtiger_relationsship_plat p
					INNER JOIN vtiger_instances i ON i.code=p.plat
				WHERE
					i.code=?',
				array ($instanceName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return array ();
			}

			return $adb->fetchByAssoc ($result);
		}

		public static function renderExportableModules (PearDatabase $adb) {
			$result = $adb->query (
				'SELECT DISTINCT
					t.name
				FROM
					vtiger_tab t
					INNER JOIN vtiger_crmentity crme ON crme.setype=t.name AND crme.deleted=0
				WHERE
					t.isentitytype=1 AND
					t.presence IN (0,2)
				ORDER BY
					t.name'
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}

			$modulesData = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$modulesData [] = array (
					'label' => getTranslatedString ($row ['name']),
					'name'  => $row ['name'],
				);
			}

			require_once ('Smarty_setup.php');
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MODULES_DATA', $modulesData);
			return $smarty->fetch ('Settings/ExportableModules.tpl');
		}

		public static function renderCombinableModulePermissionsHtmlList (array $permissions) {
			require_once ('include/utils/CommonUtils.php');
			$allowedPermissions = array ();
			foreach ($permissions as $action => $permission) {
				if ($permission != 1) {
					continue;
				}

				if ($action == 'replication') {
					$label = getTranslatedString ('LBL_REPLICATION');
				} else if ($action == 'update') {
					$label = getTranslatedString ('LBL_UPDATE');
				} else if ($action == 'view') {
					$label = getTranslatedString ('LBL_VIEW');
				} else {
					$label = '';
				}

				$allowedPermissions [] = $label;
			}
			require_once ('include/utils/HtmlGenerator.class.php');
			return HtmlGenerator::renderUnorderedList ($allowedPermissions);
		}

	}

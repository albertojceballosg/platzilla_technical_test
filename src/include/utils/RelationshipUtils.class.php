<?php

	abstract class RelationshipUtils {
		const DB_MAIN  = 0;        //DB de la plataforma
		const DB_DT_SS = 1;        //DB del datamart de SixSigma
		const DB_WP_EF = 2;        //DB del WP de Emprender Facil
		const DB_WP_GF = 3;        //DB del WP de Gestionar Facil

		private static function getOrganizationName (PearDatabase $adb) {
			$result = $adb->query ('SELECT organizationname FROM vtiger_organizationdetails LIMIT 1');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}
			$row = $adb->fetchByAssoc ($result);
			return $row ['organizationname'];
		}

		public static function encrypt ($data) {
			if (!$data) {
				return null;
			}
			require_once ('include/utils/encryption.php');
			$encrypter = new Encryption ();
			return $encrypter->encrypt ($data);
		}

		public static function canManageChildModule (PearDatabase $adb, $platform, $moduleName, $permissionFieldName) {
			$result = $adb->pquery (
				"SELECT
 					*
 				FROM
 					vtiger_relationsship_plat_modules pm
 					INNER JOIN vtiger_relationsship_plat p ON p.relationsship_platid=pm.relationsship_platid
				WHERE
					p.plat=? AND
					pm.module=? AND
					pm.{$permissionFieldName}=1",
				array ($platform, $moduleName)
			);
			return ($result) && ($adb->num_rows ($result) > 0);
		}

		public static function renderPlatformsHtmlSelect (PearDatabase $adb, $platform, $moduleName, $isAjaxRequest, $sessionCombo) {
			if ($isAjaxRequest) {
				return str_replace ("\"{$platform}\"", "\"{$platform}\" selected=\"selected\"", str_replace ('selected', '', $sessionCombo));
			}

			$result = $adb->pquery (
				"SELECT
					p.plat,
					p.name
				FROM
					vtiger_relationsship_plat_modules pm
					INNER JOIN vtiger_relationsship_plat p ON p.relationsship_platid=pm.relationsship_platid
				WHERE
					(pm.module_base=? OR (pm.module=? AND pm.module_base='-')) AND
					childplat<>1",
				array ($moduleName, $moduleName)
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return '';
			}

			require_once ('include/utils/CommonUtils.php');

			$options = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$options [] = array (
					'text'  => $row ['name'],
					'value' => $row ['plat'],
				);
			}

			require_once ('Smarty_setup.php');
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('LBL_PLATAFORMAS', getTranslatedString ('LBL_PLATAFORMAS'));
			$smarty->assign ('OPTIONS', $options);
			$smarty->assign ('ORGANIZATION_NAME', self::getOrganizationName ($adb));
			$smarty->assign ('SELECTED_VALUE', $platform);
			return $smarty->fetch ('Settings/PlatformsSelect.tpl');
		}

	}

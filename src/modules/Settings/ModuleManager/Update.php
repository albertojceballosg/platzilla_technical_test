<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('vtlib/Vtiger/Package.php');
	require_once ('vtlib/Vtiger/Language.php');

	global $app_strings, $mod_strings, $theme, $uploadFolder; // Defined in modules/Settings/ModuleManager.php

	$moduleUpdateStep = SettingsUtils::purify ($_REQUEST, 'module_update');
	$targetModuleName = SettingsUtils::purify ($_REQUEST, 'target_modulename');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('THEME', $theme);
	if ($moduleUpdateStep == 'Step2') {
		if (!is_dir ($uploadFolder)) {
			mkdir ($uploadFolder);
		}
		$now            = time ();
		$uploadFileName = "usermodule_{$now}.zip";
		$uploadFilePath = "{$uploadFolder}/{$uploadFileName}";
		checkFileAccess ($uploadFilePath);
		if (move_uploaded_file ($_FILES ['module_zipfile']['tmp_name'], $uploadFileName)) {
			$package    = new Vtiger_Package();
			$moduleName = $package->getModuleNameFromZip ($uploadfilename);

			if ($moduleName == null) {
				$smarty->assign ('MODULEUPDATE_FAILED', 'true');
				$smarty->assign ('MODULEUPDATE_FILE_INVALID', 'true');
			} else if ((!$package->isLanguageType ()) && ($moduleName != $targetModuleName)) {
				$smarty->assign ('MODULEUPDATE_FAILED', 'true');
				$smarty->assign ('MODULEUPDATE_NAME_MISMATCH', 'true');
			} else if (($package->isLanguageType ()) && (trim ($package->xpath_value ('prefix')) != $targetModuleName)) {
				$smarty->assign ('MODULEUPDATE_FAILED', 'true');
				$smarty->assign ('MODULEUPDATE_NAME_MISMATCH', 'true');
			} else {
				$vTigerVersion = $package->getDependentVtigerVersion ();
				$license       = $package->getLicense ();
				$version       = $package->getVersion ();
				if (!$package->isLanguageType ()) {
					$moduleInstance         = Vtiger_Module::getInstance ($moduleName);
					$moduleExists           = $moduleInstance ? true : false;
					$moduleFolderName       = "modules/{$moduleName}";
					$moduleFolderNameExists = is_dir ($moduleFolderName) ? true : false;

					$smarty->assign ('MODULEUPDATE_CUR_VERSION', ($moduleInstance ? $moduleInstance->version : ''));
					$smarty->assign ('MODULEUPDATE_DIR', $moduleFolderName);
					$smarty->assign ('MODULEUPDATE_DIR_NOT_EXISTS', !($moduleFolderNameExists));
					$smarty->assign ('MODULEUPDATE_NOT_EXISTS', !($moduleExists));

					// If version is matching, dis-allow migration
					if (version_compare ($version, $moduleInstance->version, '=')) {
						$smarty->assign ('MODULEUPDATE_FAILED', 'true');
						$smarty->assign ('MODULEUPDATE_SAME_VERSION', 'true');
					}
				}

				$smarty->assign ('MODULEUPDATE_DEP_VTVERSION', $vTigerVersion);
				$smarty->assign ('MODULEUPDATE_FILE', $uploadfile);
				$smarty->assign ('MODULEUPDATE_LICENSE', $license);
				$smarty->assign ('MODULEUPDATE_NAME', $moduleName);
				$smarty->assign ('MODULEUPDATE_TYPE', $package->type ());
				$smarty->assign ('MODULEUPDATE_VERSION', $version);
			}
		} else {
			$smarty->assign ('MODULEUPDATE_FAILED', 'true');
		}
	} else if ($moduleUpdateStep == 'Step3') {
		$uploadFileName = SettingsUtils::purify ($_REQUEST, 'module_import_file');
		$updateType     = SettingsUtils::purify ($_REQUEST, 'module_update_type');
		$uploadFilePath = "{$uploadFolder}/{$uploadFile}";
		checkFileAccess ($uploadFilePath);

		$overwritedir = false; // Disallowing overwrites through Module Manager UI
		$package      = strtolower ($updateType) == 'language' ? new Vtiger_Language () : new Vtiger_Package ();
		$smarty->assign ('MODULEUPDATE_PACKAGE', $package);
		$smarty->assign ('MODULEUPDATE_PACKAGE_FILE', $uploadFilePath);
		$smarty->assign ('MODULEUPDATE_TARGETINSTANCE', Vtiger_Module::getInstance ($targetModuleName));
	}
	$smarty->display ("Settings/ModuleManager/ModuleUpdate{$moduleUpdateStep}.tpl");

<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('vtlib/Vtiger/Package.php');
	require_once ('vtlib/Vtiger/Language.php');

	global $app_strings, $mod_strings, $theme, $uploadFolder; // Defined in modules/Settings/ModuleManager.php

	$moduleImportStep = SettingsUtils::purify ($_REQUEST, 'module_import');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('THEME', $theme);

	if ($moduleImportStep == 'Step2') {
		if (!is_dir ($uploadFolder)) {
			mkdir ($uploadFolder);
		}
		$now            = time ();
		$uploadFileName = "usermodule_{$now}.zip";
		$uploadFilePath = "{$uploadFolder}/{$uploadFileName}";
		checkFileAccess ($uploadFilePath);

		if (!move_uploaded_file ($_FILES ['module_zipfile']['tmp_name'], $uploadFilePath)) {
			$smarty->assign ('MODULEIMPORT_FAILED', 'true');
		} else {
			$package    = new Vtiger_Package ();
			$moduleName = $package->getModuleNameFromZip ($uploadFilePath);
			if ($moduleName == null) {
				$smarty->assign ('MODULEIMPORT_FAILED', 'true');
				$smarty->assign ('MODULEIMPORT_FILE_INVALID', 'true');
			} else {
				if ((!$package->isLanguageType ()) && (!$package->isModuleBundle ())) {
					$moduleInstance = Vtiger_Module::getInstance ($moduleName);
					$smarty->assign ('MODULEIMPORT_EXISTS', $moduleInstance ? true : false);
					$smarty->assign ('MODULEIMPORT_DIR', "modules/{$moduleName}");
					$smarty->assign ('MODULEIMPORT_DIR_EXISTS', false);
				}
				$smarty->assign ('MODULEIMPORT_FILE', $uploadFileName);
				$smarty->assign ('MODULEIMPORT_TYPE', $package->type ());
				$smarty->assign ('MODULEIMPORT_NAME', $moduleName);
				$smarty->assign ('MODULEIMPORT_DEP_VTVERSION', $package->getDependentVtigerVersion ());
				$smarty->assign ('MODULEIMPORT_LICENSE', $package->getLicense ());
			}
		}
	} else if ($moduleImportStep == 'Step3') {
		$uploadFileName = SettingsUtils::purify ($_REQUEST, 'module_import_file');
		$uploadFilePath = "{$uploadFolder}/{$uploadFileName}";
		checkFileAccess ($uploadFilePath);

		$overwritedir = false; // Disallowing overwrites through Module Manager UI
		$importType   = SettingsUtils::purify ($_REQUEST, 'module_import_type');
		$package      = strtolower ($importtype) == 'language' ? new Vtiger_Language () : new Vtiger_Package ();

		ob_start ();
		$package->import ($uploadFilePath, $overwritedir);
		unlink ($uploadFilePath);
		$myStr = ob_get_contents ();
		ob_end_clean ();

		$smarty->assign ('RESULTADO_IMPORT', $myStr);
		$smarty->assign ('MODULEIMPORT_PACKAGE', $package);
		$smarty->assign ('MODULEIMPORT_DIR_OVERWRITE', $overwritedir);
		$smarty->assign ('MODULEIMPORT_PACKAGE_FILE', $uploadFilePath);
	}
	$smarty->display ("Settings/ModuleManager/ModuleImport{$moduleImportStep}.tpl");

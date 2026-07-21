<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $mod_strings, $theme;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}


	if (isset ($_SESSION ['application-data'])) {
		$oldApplicationId   = SettingsUtils::purify ($_SESSION ['application-data'], 'appidaduplicar');
		$newApplicationCode = SettingsUtils::purify ($_SESSION ['application-data'], 'appnueva');
		$newApplicationName = SettingsUtils::purify ($_SESSION ['application-data'], 'titulonuevo');
		$newModuleNames     = SettingsUtils::purify ($_SESSION ['application-data'], 'codigonuevomodulo');
		$newModuleLabels    = SettingsUtils::purify ($_SESSION ['application-data'], 'nombrenuevomodulo');
		unset ($_SESSION ['application-data']);
	} else {
		$oldApplicationId   = SettingsUtils::purify ($_REQUEST, 'appidaduplicar');
		$newApplicationCode = SettingsUtils::purify ($_REQUEST, 'appnueva');
		$newApplicationName = SettingsUtils::purify ($_REQUEST, 'titulonuevo');
		$newModuleNames = SettingsUtils::purify ($_REQUEST, 'codigonuevomodulo');
		$newModuleLabels = SettingsUtils::purify ($_REQUEST, 'nombrenuevomodulo');
	}

	if (isset ($_SESSION ['mensaje'])) {
		$message = SettingsUtils::purify ($_SESSION, 'mensaje', '');
		unset ($_SESSION ['mensaje']);
	} else {
		$message = null;
	}

	$applicationData     = PlatformUtils::getApplicationData ($adb, $oldApplicationId);
	$applicationModules  = PlatformUtils::getDuplicatableApplicationModulesData ($adb, $oldApplicationId);
	$existingApplication = PlatformUtils::getApplicationDataByCodeOrName ($adb, $newApplicationCode, $newApplicationName);

	$smarty = new vtigerCRM_Smarty ();
	if ($existingApplication !== null) {
		// Si existe la app, devolvemos al usuario a la pantalla anterior con un mensaje de error
		if ($existingApplication ['app_code'] == $newApplicationCode) {
			$message = "Ya existe una aplicación llamada {$newApplicationCode}";
		} else {
			$message = "Ya existe una aplicación con título {$newApplicationName}";
		}
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $message);
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', "index.php?module=Settings&action=AppDuplicator&appidaduplicar={$oldApplicationId}&appnueva={$newApplicationCode}&titulonuevo={$newApplicationName}");
		$smarty->display ('Message.tpl');
	} else {
		// Si no Existe la App
		$smarty->assign ('APPIDADUPLICAR', $oldApplicationId);
		$smarty->assign ('APPNUEVA', $newApplicationCode);
		$smarty->assign ('INFOAPPDUPLICAR', $applicationData);
		$smarty->assign ('MENSAJE', $message);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULOSASOCIADOS', $applicationModules);
		$smarty->assign ('NEW_MODULE_LABELS', $newModuleLabels);
		$smarty->assign ('NEW_MODULE_NAMES', $newModuleNames);
		$smarty->assign ('NUEVOTITULO', $newApplicationName);
		$smarty->assign ('THEME', $theme);
		$smarty->display ('Settings/ModuleManager/AppDuplicator2.tpl');
	}

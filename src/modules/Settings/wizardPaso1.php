<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');

	global $adb, $mod_strings, $theme;

	$protectedVariables = array ('module', 'action', 'Ajax', 'plat');
	foreach ($_POST as $key => $value) {
		if (!in_array ($key, $protectedVariables)) {
			$_SESSION [ $key ] = SettingsUtils::purify ($_POST, $key);
		}
	}

	$inAdministration = SettingsUtils::purify ($_SESSION, 'isAdmin');

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APPLICATIONS', WizardUtils::getAllActiveApplications ($adb));
	$smarty->assign ('ID_DLG_CREACION_MODULOS', 'dlgCreaModulos');
	$smarty->assign ('IN_ADMINISTRATION', $inAdministration ? true : false);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', SettingsUtils::purify ($_REQUEST, 'module'));
	$smarty->assign ('NOMBRE_CODIGO', SettingsUtils::purify ($_SESSION, 'nombreCodigo'));
	$smarty->assign ('NOMBRE_PUBLICO', SettingsUtils::purify ($_SESSION, 'nombrePublico'));
	$smarty->assign ('PARENT_MODULES', WizardUtils::getAllParentModules ($adb));
	$smarty->assign ('SELECTED_APPLICATION', SettingsUtils::purify ($_SESSION, 'appMadre'));
	$smarty->assign ('SELECTED_MODULE_TYPE', SettingsUtils::purify ($_SESSION, 'tipoModulo'));
	$smarty->assign ('SELECTED_PARENT_MODULE', SettingsUtils::purify ($_SESSION, 'moduloPadre'));
	$smarty->assign ('THEME', $theme);
	echo $smarty->fetch ('Settings/ModuleManager/wizardPaso1.tpl');

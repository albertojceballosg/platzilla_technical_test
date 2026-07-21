<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$moduleName       = SettingsUtils::purify ($_REQUEST, 'formodule');
	$returnModuleName = SettingsUtils::purify ($_REQUEST, 'return_module');

	$moduleId = PlatformUtils::getModuleId ($adb, $moduleName);

	$smarty = new vtigerCRM_Smarty ();
	if (PlatformUtils::isPlatzillaModule ($adb, $moduleId)) {
		$smarty->assign ('MENSAJE', 'Este módulo es genérico del sistema!<br />Sólo puede borrar módulos personalizados!');
		$smarty->assign ('LINKVOLVER', "index.php?module={$returnModuleName}&action=ModuleManager");
		$smarty->display ('Settings/ModuleManager/NoPuedeEliminar.tpl');
	}
	$moduleLabel = PlatformUtils::getModuleLabel ($adb, $moduleId);
	$message     = array (
		'tipo'        => 'error',
		'descripcion' => "¿Está seguro que desea eliminar el módulo <strong>{$moduleLabel}</strong> ?",
	);
	$smarty->assign ('MENSAJE', $message);
	$smarty->assign ('MODULOAELIMINAR', $moduleName);
	$smarty->assign ('TABIDAELIMINAR', $moduleId);
	$smarty->display ('Settings/ModuleManager/DeleteModule.tpl');

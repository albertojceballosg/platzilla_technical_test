<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('vtlib/Vtiger/Module.php');
	require_once ('vtlib/Vtiger/Package.php');

	$oldModuleName = SettingsUtils::purify ($_REQUEST, 'module_export');
	$newModuleName = SettingsUtils::purify ($_REQUEST, 'name_export');

	$package = new Vtiger_Package ();
	$package->export (Vtiger_Module::getInstance ($oldModuleName), '', "$newModuleName.zip", true, $newModuleName);
	exit ();

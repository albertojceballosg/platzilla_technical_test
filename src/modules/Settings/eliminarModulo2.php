<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;

	$moduleName = SettingsUtils::purify ($_REQUEST, 'formodule');
	$mm = ModuleManager::getInstance ($adb);
	$module = $mm->fetchModule ($moduleName);
	if (!empty ($module)) {
		$mm->deleteModule ($module, true);
	}
	$_SESSION ['flashmessage'] = array (
		'iserror' => false,
		'message' => 'El módulo ha sido eliminado',
	);
	header ('Location: index.php?module=Settings&action=ModuleManager&parenttab=Settings');

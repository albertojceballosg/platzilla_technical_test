<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$customized    = SettingsUtils::purify ($_REQUEST, 'valToModify');
	$fileName      = SettingsUtils::purify ($_REQUEST, 'file');
	$moduleName    = SettingsUtils::purify ($_REQUEST, 'moduleToModify');
	$orgAjax       = SettingsUtils::purify ($_REQUEST, 'orgajax');
	$settingAction = SettingsUtils::purify ($_REQUEST, 'setting_action');
	$module        = SettingsUtils::purify ($_REQUEST, 'module');

	$convertToCustom = (isset ($settingAction)) && ($settingAction == 'convertirCustomized') ? true : false;

	$filePath = $fileName ? "modules/Settings/{$fileName}.php" : null;
	if ($filePath) {
		checkFileAccessForInclusion ($filePath);
		require_once ($filePath);
	}

	if ($orgAjax) {
		require_once ('modules/Settings/CreateSharingRule.php');
	}

	if ($convertToCustom) {
		echo $adb->pquery ('UPDATE vtiger_tab SET customized=? WHERE name=?', array ($customized, $moduleName)) ? '1' : '0';
		return;
	}

	if ($module) {
		$fields = array();
		$sql = 'SELECT a.tabid, a.name, b.fieldid, b.columnname, b.fieldlabel FROM vtiger_tab AS a INNER JOIN vtiger_field AS b ON a.tabid = b.tabid';
		$result = $adb->query($sql);
		while($row = $adb->fetchByAssoc($result)) {
			$fields[] = $row;
		}
		return $fields;
	}

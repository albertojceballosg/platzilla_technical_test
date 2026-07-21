<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/AddBlockToDatabaseHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $currentModule;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$blockLabel = trim (SettingsUtils::purify ($_REQUEST, 'blocklabel', ''));
	$moduleName = SettingsUtils::purify ($_REQUEST, 'fld_module', '');
	$mode       = SettingsUtils::purify ($_REQUEST, 'mode');
	$parentTab  = getParentTab ();
	$tabId      = getTabid ($moduleName);

	if (!AddBlockToDatabaseHelper::isBlockLabelRegistered ($adb, $tabId, $blockLabel)) {
		$afterBlockId = SettingsUtils::purify ($_REQUEST, 'after_blockid');
		AddBlockToDatabaseHelper::registerBlock ($adb, $tabId, $blockLabel, trim ($afterBlockId));
		$isDuplicated = 'yes';
	} else {
		$isDuplicated = 'no';
	}
	header ("Location: index.php?module={$currentModule}&action=LayoutBlockList&fld_module={$moduleName}&duplicate={$isDuplicated}&parenttab={$parentTab}&mode={$mode}");

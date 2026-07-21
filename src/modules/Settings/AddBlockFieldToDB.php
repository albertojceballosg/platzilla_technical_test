<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $currentModule, $dup_error;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$fieldIds   = SettingsUtils::purify ($_REQUEST, 'field_assignid');
	$blockId    = SettingsUtils::purify ($_REQUEST, 'blockid');
	$moduleName = SettingsUtils::purify ($_REQUEST, 'fld_module');
	$parentTab  = getParentTab ();

	if (($blockId) && ($fieldIds) && (is_array ($fieldIds))) {
		$result   = $adb->pquery ('SELECT MAX(sequence) AS maxsequence FROM vtiger_field WHERE block=?', array ($blockId));
		$sequence = ($adb->query_result ($result, 0, 'maxsequence') + 1);
		foreach ($fieldIds as $fieldId) {
			if ($fieldId) {
				$adb->pquery ('UPDATE vtiger_field SET block=?, sequence=? WHERE fieldid=?', array ($blockId, $sequence, $fieldId));
				$sequence++;
			}
		}
	}

	header ("Location: index.php?module={$currentModule}&action=LayoutBlockList&fld_module={$moduleName}&parenttab={$parentTab}&duplicate={$dup_error}");

<?php
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$relatedModuleName = SettingsUtils::purify ($_REQUEST, 'relmodule');
	$fieldOrColumn     = SettingsUtils::purify ($_REQUEST, 'fieldorcolumn');

	$relatedModuleId = getTabid ($relatedModuleName);

	$fields = array ();
	$result = $adb->pquery ('SELECT fieldname, fieldlabel, uitype, tablename, columnname FROM vtiger_field WHERE tabid=? AND presence IN (0, 2)', array ($relatedModuleId));
	if (($result) && ($adb->num_rows ($result) > 0)) {
		while ($row = $adb->fetchByAssoc ($result)) {
			if ($fieldOrColumn == 'field') {
				$fields [] = array (
					$row ['fieldname'],
					getTranslatedString ($row ['fieldlabel']),
				);
			} else {
				$fields [] = array (
					"{$row ['tablename']}.{$row ['columnname']}",
					getTranslatedString ($row ['fieldlabel']),
				);
			}
		}
	}

	echo json_encode (array ('fields' => $fields));

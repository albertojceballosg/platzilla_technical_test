<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('user_privileges/default_module_view.php');

	global $adb, $currentModule;

	$entityId         = PlatzillaUtils::purify ($_POST, 'record');
	$operation        = PlatzillaUtils::purify ($_POST, 'operation');
	$relatedRecordIds = PlatzillaUtils::purify ($_POST, 'relatedrecords');

	var_dump ($_POST);
	exit ();

	if (!empty ($relatedRecordIds)) {
		if ($operation == 'delete') {
			$questionMarks = str_repeat ('?, ', (count ($relatedRecordIds) - 1)) . '?';
			$adb->pquery ("DELETE FROM vtiger_seactivityrel WHERE crmid=? AND activityid IN ({$questionMarks})", array_merge (array ($entityId), $relatedRecordIds));
		} else {
			foreach ($relatedRecordIds as $relatedRecordId) {
				$adb->pquery ('INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)', array ($entityId, $relatedRecordId));
			}
		}
	}
	exit ();

<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$result = $adb->pquery (
		'SELECT
			*
		FROM
			vtiger_instances i
		WHERE
			i.code=?',
		array (SettingsUtils::purify ($_REQUEST, 'domaincode'))
	);
	if ((!$result) || ($adb->num_rows ($result) == 0)) {
		echo 'FAIULURE';
	} else {
		echo 'SUCCESS';
	}
	exit ();

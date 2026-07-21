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
			vtiger_instanceusers iu
			INNER JOIN vtiger_instances i ON i.code=iu.instancecode
		WHERE
			iu.username=?',
		array (SettingsUtils::purify ($_REQUEST, 'email'))
	);
	if ((!$result) || ($adb->num_rows ($result) == 0)) {
		echo 'FAILURE';
	} else {
		echo 'SUCCESS';
	}
	exit ();

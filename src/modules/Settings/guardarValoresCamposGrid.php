<?php
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb, $current_user;

	$fieldId   = SettingsUtils::purify ($_REQUEST, 'fieldid');
	$returnMsn = upDateGridDefaultValues ($adb, $current_user, $fieldId);
	if (!empty($returnMsn)) {
		echo '<div class=\"alert alert-success alert-dismissable\">
		  		<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
				<strong>¡Muy bien !</strong> <br />{$returnMsn}
			</div>';
	} else {
		echo '<div class=\"alert alert-danger alert-dismissable\">
				<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
				<strong>¡Error !</strong> Imposible actualizar valores
			</div>';
	};
	exit ();

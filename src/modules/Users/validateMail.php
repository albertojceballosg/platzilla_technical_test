<?php
	require_once ('include/utils/utils.php');
	require_once ('config.inc.php');
	require_once ('modules/Users/Users.php');

	global $current_user, $adb;
	$fromName = 'Platzilla';
	$fromMail = "noreply@gen.timelocal.es";

	$sql                                      = "SELECT first_name, last_name, email1 FROM vtiger_users WHERE id = 1";
	$res                                      = $adb->query ($sql);
	$current_user->column_fields['firstname'] = $adb->query_result ($res, 0, 'first_name');
	$current_user->column_fields['lastname']  = $adb->query_result ($res, 0, 'last_name');
	$current_user->column_fields['email1']    = $adb->query_result ($res, 0, 'email1');

	$toName = $current_user->column_fields['firstname'] . ' ' . $current_user->column_fields['lastname'];
	$email  = $current_user->column_fields['email1'];

	$arrayVars['CUSTOM_CUSTOM1'] = rand (100000, 999999);

	updateValidateConfirmation ($arrayVars['CUSTOM_CUSTOM1']);
	echo 0;

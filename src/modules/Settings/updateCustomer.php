<?php
	require_once ('include/security.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$customerUser     = SettingsUtils::purify ($_REQUEST, 'user_customer');
	$customerPassword = SettingsUtils::purify ($_REQUEST, 'pass_customer');

	$userName = $customerUser ? encrypt ($customerUser, 'estaeslaclave01EncryptadaDeTimeManagement') : null;
	$password = $customerPassword ? encrypt ($customerPassword, 'estaeslaclave01EncryptadaDeTimeManagement') : null;

	$adb->pquery ('UPDATE vtiger_organizationdetails SET user_customer=?, pass_customer=?', array ($userName, $password));

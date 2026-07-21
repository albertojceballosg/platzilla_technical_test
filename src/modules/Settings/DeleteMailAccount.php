<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if ((!isset ($adb)) || (!$adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$idsString = SettingsUtils::purify ($_REQUEST, 'idstring');
	$idsList = SettingsUtils::purify ($_REQUEST, 'idlist');

	if ($idsString) {
		$accountIds = explode (';', $idsString);
	} else if ($idsList) {
		$accountIds = explode (';', $idsList);
	} else {
		$accountIds = null;
	}

	if (($accountIds) && (is_array ($accountIds)) && (count ($accountIds) > 0)) {
		foreach ($accountIds as $accountId) {
			$adb->pquery ('UPDATE vtiger_mail_accounts SET status=0 WHERE account_id=?', array ($accountId));
		}
	}

	header ('Location: index.php?module=Settings&action=ListMailAccount');

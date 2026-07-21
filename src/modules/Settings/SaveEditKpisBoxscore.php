<?php
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $adb;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}

	$action      = SettingsUtils::purify ($_REQUEST, 'actionCrud');
	$description = SettingsUtils::purify ($_REQUEST, 'description');
	$isActive    = SettingsUtils::purify ($_REQUEST, 'active');
	$moduleName  = SettingsUtils::purify ($_REQUEST, 'modulo');
	$query       = SettingsUtils::purify ($_REQUEST, 'query');
	$recordId    = SettingsUtils::purify ($_REQUEST, 'record');
	$moduleName  = SettingsUtils::purify ($_REQUEST, 'modulo');
	$title       = SettingsUtils::purify ($_REQUEST, 'title');
	$weeklyQuery = SettingsUtils::purify ($_REQUEST, 'querysemanal');

	if ($action == 'Save') {
		$adb->pquery (
			'INSERT INTO vtiger_kpisboxscore (name, description, module, querykpi, querykpisemanal, active) VALUES (?, ?, ?, ?, ?, ?)',
			array ($title, $description, $moduleName, $query, $weeklyQuery, $isActive)
		);
		header ("Location: index.php?module=Settings&action=DetailKpisBoxscore&parenttab=Settings&record={$adb->getLastInsertID ()}");
	} else if ($action == 'Edit') {
		$adb->pquery (
			'UPDATE vtiger_kpisboxscore SET module=?, name=?, description=?, querykpi=?, querykpisemanal=?, active=? WHERE kpisboxscoreid=?',
			array ($moduleName, $title, $description, $query, $weeklyQuery, $isActive, $recordId)
		);
		header ("Location: index.php?module=Settings&action=DetailKpisBoxscore&parenttab=Settings&record={$recordId}");
	} else {
		header ('Location: index.php?module=Settings&action=KpisBoxscore&parenttab=Settings');
	}

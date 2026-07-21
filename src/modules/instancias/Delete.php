<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');

	global $currentModule;

	$record       = SettingsUtils::purify ($_REQUEST, 'record', '');
	$module       = SettingsUtils::purify ($_REQUEST, 'module', '');
	$returnModule = SettingsUtils::purify ($_REQUEST, 'return_module', '');
	$returnAction = SettingsUtils::purify ($_REQUEST, 'return_action', '');
	$returnId     = SettingsUtils::purify ($_REQUEST, 'return_id', '');

	$parentTab     = getParentTab ();
	$searchUrlPart = getBasic_Advance_SearchURL ();
	$entity        = CRMEntity::getInstance ($currentModule);
	DeleteEntity ($currentModule, $returnModule, $entity, $record, $returnId);

	header ("Location: index.php?module={$returnModule}&action={$returnAction}&record={$returnId}&parenttab={$parentTab}&relmodule={$module}{$searchUrlPart}");

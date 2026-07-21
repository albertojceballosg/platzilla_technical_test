<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule;

	$record        = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : '';
	$module        = isset ($_REQUEST ['module']) ? vtlib_purify ($_REQUEST ['module']) : '';
	$returnModule  = isset ($_REQUEST ['return_module']) ? vtlib_purify ($_REQUEST ['return_module']) : '';
	$returnAction  = isset ($_REQUEST ['return_action']) ? vtlib_purify ($_REQUEST ['return_action']) : '';
	$returnId      = isset ($_REQUEST ['return_id']) ? vtlib_purify ($_REQUEST ['return_id']) : '';
	$parentTab     = getParentTab ();
	$searchUrlPart = getBasic_Advance_SearchURL ();

	$entity = CRMEntity::getInstance ($currentModule);
	DeleteEntity ($currentModule, $returnModule, $entity, $record, $returnId);

	header ("Location: index.php?module=$returnModule&action=$returnAction&record=$returnId&parenttab=$parentTab&relmodule={$module}{$searchUrlPart}");

<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('include/utils/VtlibUtils.php');

	global $currentModule;

	if(isset ($_REQUEST ['record'])) {
		$record = vtlib_purify($_REQUEST ['record']);
	} else{
		$record = '';
	}

	if(isset ($_REQUEST ['module'])) {
		$module = vtlib_purify ($_REQUEST ['module']);
	} else{
		$module = '';
	}

	if(isset($_REQUEST ['return_module'])) {
		$returnModule = vtlib_purify ($_REQUEST ['return_module']);
	} else{
		$returnModule = '';
	}

	if(isset($_REQUEST ['return_action'])) {
		$returnAction = vtlib_purify ($_REQUEST ['return_action']);
	} else{
		$returnAction = '';
	}

	if(isset($_REQUEST['return_id'])) {
		$returnId = vtlib_purify ($_REQUEST ['return_id']);
	} else{
		$returnId = '';
	}

	$parentTab = getParentTab ();
	$searchUrlPart = getBasic_Advance_SearchURL ();

	$entity = CRMEntity::getInstance ($currentModule);
	DeleteEntity ($currentModule, $returnModule, $entity, $record, $returnId);

	header ("Location: index.php?module={$returnModule}&action={$returnAction}&record={$returnId}&parenttab={$parentTab}&relmodule={$module}{$searchUrlPart}");

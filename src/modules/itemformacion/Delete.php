<?php
	global $currentModule;
	$focus = CRMEntity::getInstance($currentModule);

	$record = vtlib_purify($_REQUEST['record']);
	$module = vtlib_purify($_REQUEST['module']);
	$returnModule = vtlib_purify($_REQUEST['return_module']);
	$returnAction = vtlib_purify($_REQUEST['return_action']);
	$returnId = vtlib_purify($_REQUEST['return_id']);
	$parentTab = getParentTab();

	//Added to fix 4600
	$url = getBasic_Advance_SearchURL();

	DeleteEntity($currentModule, $returnModule, $focus, $record, $returnId);

	header("Location: index.php?module=$returnModule&action=$returnAction&record=$returnId&parenttab=$parentTab&relmodule=$module".$url);

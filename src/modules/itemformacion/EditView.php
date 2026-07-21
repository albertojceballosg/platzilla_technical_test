<?php
	require_once('modules/Vtiger/EditView.php');
	global $smarty;
	$reqModule = vtlib_purify($_REQUEST['module']);
	$focus = CRMEntity::getInstance($reqModule);
	if($focus->mode == 'edit') {
		$smarty->display('salesEditView.tpl');
	} else {
		$smarty->display('CreateView.tpl');
	}

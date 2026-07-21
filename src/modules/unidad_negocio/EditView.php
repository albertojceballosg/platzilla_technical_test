<?php
	require_once ('modules/Vtiger/EditView.php');

	/** @var $focus CRMEntity|stdClass */
	global $focus, $smarty;

	if ($focus->mode == 'edit') {
		$smarty->display ('salesEditView.tpl');
	} else {
		$smarty->display ('CreateView.tpl');
	}

<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/jQueryUtils.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/instancias/instancias.php');

	$recordId = isset ($_REQUEST ['record']) ? vtlib_purify ($_REQUEST ['record']) : null;

	$tabId = getTabid ('instancias');

	/** @var instancias|stdClass $entity */
	$entity = new instancias ();
	if ($recordId != '') {
		$entity->id = $recordId;
		$entity->retrieve_entity_info ($recordId, 'instancias');
	}
	if ($entity->column_fields ['parentid'] == 0) {
		$entity->column_fields ['parentid'] = '';
	}

	if ($recordId != '') {
		$smartyDlg = new vtigerCRM_Smarty ();
		$smartyDlg->assign ('DIALOG_ID', 'dlgCursos');
		$smartyDlg->assign ('URL', "index.php?module=instancias&action=instanciasAjax&file=obtenerCursos&record={$entity->id}");
		echo $smartyDlg->fetch ('modules/instancias/getCursos.tpl');
	}
	echo escribeDlgModal ($idDlgAcciones, '');

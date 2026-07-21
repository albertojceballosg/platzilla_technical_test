<?php
	global $currentModule;
	$focus = CRMEntity::getInstance($currentModule);

	$record = vtlib_purify($_REQUEST['record']);
	$module = vtlib_purify($_REQUEST['module']);
	$returnModule = vtlib_purify($_REQUEST['return_module']);
	$returnAction = vtlib_purify($_REQUEST['return_action']);
	$returnId = vtlib_purify($_REQUEST['return_id']);
	$parenttab = getParentTab();

	$url = getBasic_Advance_SearchURL();



	$tieneAppAsociada = tieneAppAsociada($record);
	if ($tieneAppAsociada == 1) {
	$error = 'No puede ser Eliminado porque tiene aplicaciones asociadas.';
	$url .= "&MENSAJE=$error&TIPO_MENSAJE=fail";
	} else{
	DeleteEntity($currentModule, $returnModule, $focus, $record, $returnId);
	}


	header("Location: index.php?module=$returnModule&action=$returnAction&record=$returnId&parenttab=$parenttab&relmodule=$module".$url);

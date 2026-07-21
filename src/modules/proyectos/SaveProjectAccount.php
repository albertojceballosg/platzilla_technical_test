<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
global $current_user, $currentModule;

	
checkFileAccessForInclusion("modules/$currentModule/$currentModule.php");
require_once("modules/$currentModule/$currentModule.php");

$focus = CRMEntity::getInstance('proyectos');
setObjectValuesFromRequest($focus);

$focusOld = CRMEntity::getInstance('proyectos');
$focusOld->id = $_REQUEST['proyecto'];
$focusOld->retrieve_entity_info($_REQUEST['proyecto'], $currentModule);

$focus->mode = '';
$focus->column_fields['name'] = html_entity_decode($focusOld->column_fields['name'],ENT_COMPAT | ENT_HTML401,'UTF-8');
$focus->column_fields['description'] = html_entity_decode($focusOld->column_fields['description'],ENT_COMPAT | ENT_HTML401,'UTF-8');
$focus->column_fields['assigned_user_id'] = $current_user->id;
$focus->column_fields['estado_proyecto'] = html_entity_decode($focusOld->column_fields['estado_proyecto'],ENT_COMPAT | ENT_HTML401,'UTF-8');

$focus->save($currentModule);

$focus->save_related_module('Accounts', $_REQUEST['accountid'], $currentModule, $focus->id);

//Luego acá se deben cargar los hitos y los pedidos.
for($i = 0;$i < count($_REQUEST['hitoid']);$i++) {
	$focusHitoOld = CRMEntity::getInstance('hito');
	$focusHitoOld->id = $_REQUEST['hitoid'][$i];
	$focusHitoOld->retrieve_entity_info($_REQUEST['hitoid'][$i], 'hito');
	
	$focusHito = CRMEntity::getInstance('hito');
	
	$focusHito->column_fields['name'] = html_entity_decode($focusHitoOld->column_fields['name'],ENT_COMPAT | ENT_HTML401,'UTF-8');
	$focusHito->column_fields['description'] = html_entity_decode($focusHitoOld->column_fields['description'],ENT_COMPAT | ENT_HTML401,'UTF-8');
	$focusHito->column_fields['assigned_user_id'] = $current_user->id;
	$focusHito->column_fields['inidate'] = $_REQUEST['inidate'][$i];
	$focusHito->column_fields['enddate'] = $_REQUEST['enddate'][$i];
	$focusHito->column_fields['hitostate'] = 'Pendiente';
	$focusHito->column_fields['proyectosid'] = $focus->id;
	
	$focusHito->save('hito');
}

$return_id = $focus->id;
$return_module = $currentModule;
$return_action = "DetailView";

header("Location: index.php?action=$return_action&module=$return_module&record=$return_id&parenttab=$parenttab&start=".vtlib_purify($_REQUEST['pagenumber']).$search);

?>
<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('Smarty_setup.php');
require_once 'include/utils/comunesTareas.php';
require_once 'include/utils/comunesKanban.php';
$smarty = new vtigerCRM_Smarty;

global $currentModule;
global $mod_strings;
global $app_strings;

$filtro = array();
$datosSeleccionados = array();
if (isset($_REQUEST['proyectosid'])) {
	$filtro['proyectosid'] = $_REQUEST['proyectosid'];
	$datosSeleccionados['proyectosid'] = $_REQUEST['proyectosid'];
}
if (isset($_REQUEST['account'])) {
	$filtro['account'] = $_REQUEST['account'];
	$datosSeleccionados['account'] = $_REQUEST['account'];
}
if (isset($_REQUEST['fecha_desde'])) {
	$filtro['fecha_desde'] = $_REQUEST['fecha_desde'];
	$datosSeleccionados['fecha_desde'] = $_REQUEST['fecha_desde'];
}
if (isset($_REQUEST['fecha_hasta'])) {
	$filtro['fecha_hasta'] = $_REQUEST['fecha_hasta'];
	$datosSeleccionados['fecha_hasta'] = $_REQUEST['fecha_hasta'];
}

//echo "<pre>datosSeleccionados ".print_r($datosSeleccionados,true)."</pre>";


$smarty->assign('MODULE',$currentModule);
$smarty->assign('PARENTTAB',$_REQUEST['parenttab']);
$smarty->assign('MOD', $mod_strings);
$smarty->assign('APP', $app_strings);
$smarty->assign('DATASELECCIONADA', $datosSeleccionados);


$smarty->assign('ACCOUNTS',escribeListaCuentas(null));
//$smarty->assign("TYPES",escribeListaTipos()); // No se encuentra en uso 
//$smarty->assign("VENDORS",escribeListaDesarrolladores());  // No se encuentra en uso 
$smarty->assign('PROJECTS',escribeListaProyectos($datosSeleccionados['account'],$datosSeleccionados['proyectosid']));

/*
    $module = 'HelpDesk';
    $tipos = obtieneTiposModulo($module);
*/
$module = 'todotasks';
$tipos = obtieneTiposModuloKanbanTT($module);

//echo "<pre>TIPOS ".print_r($tipos,true)."</pre>";
$smarty->assign('TIPOS_GENERICOS', $tipos);
$registrosGenericos = obtieneRegistrosSegunTareasTT($tipos,$filtro);

// echo "<pre>REGISTROS_GENERICOS ".print_r($registrosGenericos,true)."</pre>";

$smarty->assign('REGISTROS_GENERICOS', $registrosGenericos);

/**
 * Presentacion final de los valores de indicadores
 */
$smarty->display("modules/$currentModule/index.tpl");
?>

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
include_once 'include/utils/comunesTareas.php';
include_once "include/utils/comunesKanban.php";


$smarty = new vtigerCRM_Smarty;

global $currentModule;
global $mod_strings;
global $app_strings;

$filtro = array();
$datosSeleccionados = array();
if (isset($_REQUEST['proyectosid'])){
	$filtro['proyectosid'] = $_REQUEST['proyectosid'];
	$datosSeleccionados['proyectosid'] = $_REQUEST['proyectosid'];
}
if (isset($_REQUEST['account'])){
	$filtro['account'] = $_REQUEST['account'];
	$datosSeleccionados['account'] = $_REQUEST['account'];
}
if (isset($_REQUEST['fecha_desde'])){
	$filtro['fecha_desde'] = $_REQUEST['fecha_desde'];
	$datosSeleccionados['fecha_desde'] = $_REQUEST['fecha_desde'];
}
if (isset($_REQUEST['fecha_hasta'])){
	$filtro['fecha_hasta'] = $_REQUEST['fecha_hasta'];
	$datosSeleccionados['fecha_hasta'] = $_REQUEST['fecha_hasta'];
}



$smarty->assign("MODULE",$currentModule);
$smarty->assign("PARENTTAB",$_REQUEST['parenttab']);
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("DATASELECCIONADA", $datosSeleccionados);


$smarty->assign("ACCOUNTS",escribeListaCuentas(null));
$smarty->assign("TYPES",escribeListaTipos());
$smarty->assign("VENDORS",escribeListaDesarrolladores());
$smarty->assign("PROJECTS",escribeListaProyectos());


$module = 'todotasks';
$tipos = obtieneTiposModuloKanbanTT($module);
$smarty->assign("TIPOS_GENERICOS", $tipos);
$registrosGenericos = obtieneRegistrosSegunTareasTT($tipos,$filtro);
$smarty->assign("REGISTROS_GENERICOS", $registrosGenericos);



$TodosregistrosGenericos = obtieneTodosRegistrosTT($tipos,$filtro);
$smarty->assign("TODOS_REGISTROS_GENERICOS", $TodosregistrosGenericos);
//Quitar creados para medir horas trabajadas
$tipos2=$tipos;
unset($tipos2[0]);



$todosregistros = obtieneTodosRegistrosTT($tipos2,$filtro);
//$todosregistros = obtieneTodosRegistros($tipos2);
$smarty->assign("REGISTROS_HORAS", $todosregistros);
// echo "<pre>".print_r($tipos,true)."</pre>";
// echo "<pre>".print_r($todosregistros,true)."</pre>";
// exit;
/**
 * Presentacion final de los valores de indicadores
 */
$smarty->display("modules/$currentModule/analiticaTT.tpl");
?>

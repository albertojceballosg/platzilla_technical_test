<?php

require_once('Smarty_setup.php');
require_once('include/utils/utils.php');
require_once('modules/Vtiger/layout_utils.php');
require_once('modules/admin_widgets/admin_widgets.php');

global $adb,$mod_strings,$app_strings,$log,$theme,$currentModule;
$theme_path='themes/'.$theme.'/';
$image_path=$theme_path.'images/';

$smarty=new vtigerCRM_Smarty;

$Widget = new Widgets();

$tabid = $Widget->getTabId(vtlib_purify($_REQUEST['wmodule']));

$tableName = $Widget->getTableName(vtlib_purify($_REQUEST['fieldoperation']),$tabid);
$tableNameid = $Widget->getIdField($tableName);

$fieldOperation = vtlib_purify($_REQUEST['fieldoperation']);
$fieldGrouping = 'tq.'.vtlib_purify($_REQUEST['fieldgrouping']);
$uitypeFieldOperation = $Widget->getUiType($tableName,$fieldOperation);
$operation = vtlib_purify($_REQUEST['opcolumn']);

$textoWidget = vtlib_purify($_REQUEST['textwidget']);
$iconWidget = vtlib_purify($_REQUEST['icontype']);
$colorWidget = vtlib_purify($_REQUEST['colortype']);
$estatus = vtlib_purify($_REQUEST['estatus']);

// campos de filtro de fecha
$filterFieldDate = vtlib_purify($_REQUEST['filterFieldDate']);
$filterFieldDateDefault = vtlib_purify($_REQUEST['filterFieldDateDefault']);
$fechaDesde = vtlib_purify($_REQUEST['fecha_desde']);
$fechaHasta = vtlib_purify($_REQUEST['fecha_hasta']);

$filtroFecha='';
if ($filterFieldDate != '') {
	$filtroFecha = ' AND (DATE_FORMAT(tq.'.$filterFieldDate.', \'%Y-%m-%d\') BETWEEN \''.$fechaDesde.'\' AND \''.$fechaHasta.'\') ';
}

$colorValue = explode('-',$colorWidget);

$filterNum = vtlib_purify($_REQUEST['filterNumber']);

switch (vtlib_purify($_REQUEST['orderFilter'])) {
	case '1':
		$orderFilter = '>';
		break;
	
	case '2':
		$orderFilter = '<';
		break;
		
	default:
		$orderFilter = '=';
		break;
}

$opcFilter = vtlib_purify($_REQUEST['filterField']);

$BaseOperation = '';
if ($operation == 1) {
	$BaseOperation = ' count(*) as variablegraficar ';
}

if ($operation == 2) {
	$BaseOperation = ' ROUND( SUM('.$fieldGrouping.'),2 ) as variablegraficar ';
}

if ($operation == 3) {
	$BaseOperation = ' ROUND( AVG('.$fieldGrouping.'),2 ) as variablegraficar ';
}

$subqueryFields = $BaseOperation.' , tq.'.$fieldOperation;
$subqueryGroup = 'GROUP BY tq.'.$fieldOperation;
$fieldLabel = $Widget->getFieldLabel($fieldOperation);

if ($uitypeFieldOperation == '7') {
	$filtro = ' AND tq.'.$fieldOperation.' '.$orderFilter.' '.$filterNum;
} else {
	$filtro = ' AND tq.'.$fieldOperation.' = "'.$opcFilter.'"';
}

$tableAux = '';
$tableNameIdAux = '';
$join = '';

if ($_REQUEST['fieldgrouping'] != '') {
	$tableAux = $Widget->getTableName(vtlib_purify($_REQUEST['fieldgrouping']),$tabid);
	$tableNameIdAux = $Widget->getIdField($tableAux);
	if ($tableName != $tableAux) {
		$join = 'INNER JOIN ' . $tableAux . ' tq2 ON tq.' . $fieldOperation . ' = tq2.' . $tableNameIdAux;
	}
}

$sql = 'SELECT '.$subqueryFields.'
		FROM '.$tableName.' tq JOIN vtiger_crmentity crm ON (crm.crmid = tq.'.$tableNameid.') '.$join.' 
		WHERE deleted = 0 '.$filtroFecha.$filtro.' '.$subqueryGroup;

$result = $adb->pquery($sql, array());

$valor = '';
while($row = $adb->fetchByAssoc($result)) {
	$valor = $row['variablegraficar'];
}

$smarty->assign('MODULE', $currentModule);
$smarty->assign('ICONWIDGET', $iconWidget);
$smarty->assign('COLORWIDGET', $colorWidget);
$smarty->assign('COLORVALUE', $colorValue[0]);
$smarty->assign('VALOR', $valor);
$smarty->assign('TXTWIDGET', $textoWidget);

$smarty->display('modules/admin_widgets/preview.tpl');

?>

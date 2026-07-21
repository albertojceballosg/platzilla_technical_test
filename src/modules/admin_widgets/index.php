<?php
require_once('Smarty_setup.php');
require_once('modules/admin_widgets/admin_widgets.php');

global $mod_strings,$app_strings,$log,$theme,$currentModule,$current_user, $adb;
$theme_path='themes/'.$theme.'/';
$image_path=$theme_path.'images/';

$Widget = new Widgets();

if (isset($_REQUEST['registrarNuevoWidget']) && $_REQUEST['registrarNuevoWidget'] == 1) {
	$fieldOperation = vtlib_purify($_REQUEST['fieldoperation']);
	$opColumn 		= vtlib_purify($_REQUEST['opcolumn']);
	$fieldGrouping 	= (!empty($_REQUEST['fieldgrouping'])) ? 'tq.'.vtlib_purify($_REQUEST['fieldgrouping']) : null;
	$filterField    = vtlib_purify($_REQUEST['filterField']);
	$orderF         = vtlib_purify($_REQUEST['orderFilter']);
	$filterNumber   = vtlib_purify($_REQUEST['filterNumber']);

	// campos de filtro de fecha
	$filterFieldDate = vtlib_purify($_REQUEST['filterFieldDate']);
	$filterFieldDateDefault = vtlib_purify($_REQUEST['filterFieldDateDefault']);
	$fechaDesde = vtlib_purify($_REQUEST['fecha_desde']);
	$fechaHasta = vtlib_purify($_REQUEST['fecha_hasta']);

	$filtroFecha='';
	if ($filterFieldDate != '') {
		$filtroFecha = ' AND (DATE_FORMAT(tq.'.$filterFieldDate.', \'%Y-%m-%d\') BETWEEN \''.$fechaDesde.'\' AND \''.$fechaHasta.'\') ';
	}

	$param['fld_module'] = vtlib_purify($_REQUEST['wmodule']);
	$param['fieldOperation'] = $fieldOperation;
	$param['operation'] = intval($opColumn);
	$param['fieldGrouping'] = $fieldGrouping;
	$param['textoW'] = html_entity_decode(vtlib_purify($_REQUEST['textwidget']),ENT_QUOTES, 'UTF-8');
	$param['iconW'] = vtlib_purify($_REQUEST['icontype']);
	$param['colorW'] = vtlib_purify($_REQUEST['colortype']);
	$param['filternum'] = intval($filterNumber);
	$param['orderfilter'] = intval($orderF);
	$param['opcfilter'] = $filterField;

	$tabid = $Widget->getTabId(vtlib_purify($_REQUEST['wmodule']));
	$tableName = $Widget->getTableName($fieldOperation,$tabid);
	$tableNameId = $Widget->getIdField($tableName);

	$uitypeFieldOperation = $Widget->getUiType($tableName,$fieldOperation);

	switch ($orderF) {
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

	if ($opColumn == 1) {
		$BaseOperation = ' count(*) as variablegraficar ';
	}

	if ($opColumn == 2) {
		$BaseOperation = ' ROUND( SUM('.$fieldGrouping.'),2 ) as variablegraficar ';
	}

	if ($opColumn == 3) {
		$BaseOperation = ' ROUND( AVG('.$fieldGrouping.'),2 ) as variablegraficar ';
	}

	$subqueryFields = $BaseOperation.' , tq.'.$fieldOperation;
	$subqueryGroup = 'group by tq.'.$fieldOperation;
	$fieldLabel = $Widget->getFieldLabel($fieldOperation);

	if ($uitypeFieldOperation == '7') {
		$filtro = ' and tq.'.$fieldOperation.' '.$orderFilter.' '.$filterNumber;
	} else {
		$filtro = ' and tq.'.$fieldOperation.' = "'.$filterField.'"';
	}

	$tableAux = '';
	$tableNameIdAux = '';
	$join = '';

	if ($_REQUEST['fieldgrouping'] != '') {
		$tableAux = $Widget->getTableName(vtlib_purify($_REQUEST['fieldgrouping']),$tabid);
		$tableNameIdAux = $Widget->getIdField($tableAux);
		if ($tableName != $tableAux) {
			$join = 'inner join ' . $tableAux . ' tq2 on tq.' . $fieldOperation . ' = tq2.' . $tableNameIdAux;
		}
	}

	$sqlPrimario = 'SELECT '.$subqueryFields.'
		FROM '.$tableName.' tq join vtiger_crmentity crm on (crm.crmid = tq.'.$tableNameId.') '.$join.' 
		WHERE deleted = 0 '.$filtroFecha.$filtro.' '.$subqueryGroup;

	$param['sqlW'] = $sqlPrimario;
	$param['statusW'] = intval(vtlib_purify($_REQUEST['estatus']));
	// campos de filtro de fecha
	$param['campofecha'] = $filterFieldDate;
	$param['tiempofecha'] = $filterFieldDateDefault;
	$param['fechadesde'] = $fechaDesde;
	$param['fechahasta'] = $fechaHasta;

	$query = 'SELECT * FROM vtiger_widgets WHERE texto = ?';
	$result = $adb->pquery($query, array(vtlib_purify($_REQUEST['textwidget'])));
	$numRows = $adb->num_rows($result);

	if ($numRows == 0) {
		$Widget->guardarWidget($param);
	}
}

$output = array();

$query = 'SELECT * FROM vtiger_widgets';
$result = $adb->pquery($query, array());
$numRows = $adb->num_rows($result);

while ($row = $adb->fetchByAssoc($result)) {
	$row['modulelabel'] = getTabIdLabelByName($row['fld_module']);
	$output[] = $row;
}

$smarty=new vtigerCRM_Smarty;

$smarty->assign('WIDGETSLIST',$output);
$smarty->assign('MODULE',$currentModule);
$smarty->assign('MOD',$mod_strings);
$smarty->assign('APP',$app_strings);
$smarty->assign('THEME', $theme);
$smarty->assign('IS_ADMIN', is_admin($current_user));

$smarty->display('modules/admin_widgets/index.tpl');

?>

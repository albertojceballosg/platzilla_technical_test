<?php
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('modules/admin_widgets/admin_widgets.php');

$Widgets = new Widgets();

global $adb;

if (isset($_REQUEST['record']) && $_REQUEST['record'] != '') {
	$widgetId = $_REQUEST['record'];

	$fieldOperation = vtlib_purify($_REQUEST['fieldoperation']);
	$opColumn 		= intval(vtlib_purify($_REQUEST['opcolumn']));
	$fieldGrouping 	= (!empty($_REQUEST['fieldgrouping'])) ? 'tq.'.vtlib_purify($_REQUEST['fieldgrouping']) : null;
	$fieldLabel     = $Widgets->getFieldLabel($fieldOperation);
	$filterField    = vtlib_purify($_REQUEST['filterField']);
	$orderF         = intval(vtlib_purify($_REQUEST['orderFilter']));
	$filterNumber   = intval(vtlib_purify($_REQUEST['filterNumber']));
	$texto          = html_entity_decode(vtlib_purify($_REQUEST['textwidget']),ENT_QUOTES, 'UTF-8');
	$icono          = vtlib_purify($_REQUEST['icontype']);
	$color          = vtlib_purify($_REQUEST['colortype']);
	$estatus        = intval(vtlib_purify($_REQUEST['estatus']));

	$tabId = $Widgets->getTabId($_REQUEST['wmodule']);

	// campos de filtro de fecha
	$filterFieldDate = vtlib_purify($_REQUEST['filterFieldDate']);
	$filterFieldDateDefault = vtlib_purify($_REQUEST['filterFieldDateDefault']);
	$fechaDesde = vtlib_purify($_REQUEST['fecha_desde']);
	$fechaHasta = vtlib_purify($_REQUEST['fecha_hasta']);

	$filtroFecha='';
	if ($filterFieldDate != '') {
		$filtroFecha = ' AND (DATE_FORMAT(tq.'.$filterFieldDate.', \'%Y-%m-%d\') BETWEEN \''.$fechaDesde.'\' AND \''.$fechaHasta.'\') ';
	}

	$tableName = $Widgets->getTableName($fieldOperation,$tabId);
	$tableNameId = $Widgets->getIdField($tableName);
	$uitypeFieldOperation = $Widgets->getUiType($tableName,$fieldOperation);

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
	$fieldLabel = $Widgets->getFieldLabel($fieldOperation);

	if ($uitypeFieldOperation == '7') {
		$filtro = ' and tq.'.$fieldOperation.' '.$orderFilter.' '.$filterNumber;
	} else {
		$filtro = ' and tq.'.$fieldOperation.' = "'.$filterField.'"';
	}

	$tableAux = '';
	$tableNameIdAux = '';
	$join = '';

	if ($_REQUEST['fieldgrouping'] != '' && $opColumn != 1) {
		$tableAux = $Widgets->getTableName(vtlib_purify($_REQUEST['fieldgrouping']),$tabId);
		$tableNameIdAux = $Widgets->getIdField($tableAux);
		if ($tableName != $tableAux) {
			$join = 'INNER JOIN ' . $tableAux . ' tq2 ON tq.' . $fieldOperation . ' = tq2.' . $tableNameIdAux;
		}
	}

	$sqlprimario = 'SELECT '.$subqueryFields.'
		FROM '.$tableName.' tq join vtiger_crmentity crm on (crm.crmid = tq.'.$tableNameId.') '.$join.' 
		WHERE deleted = 0 '.$filtroFecha.$filtro.' '.$subqueryGroup;

	$sql = 'UPDATE vtiger_widgets SET fieldoperation = ?, operation =?, fieldgrouping = ?, texto = ?, icono = ?, color = ?, 
			filterNumber = ?, orderFilter = ?, filterField = ?, sqlprimario = ?, estatus = ?, 
			campofecha = ?, tiempofecha = ?, fechadesde = ?, fechahasta = ? WHERE widgetid = ?';
	$params = array(
		$fieldOperation,
		$opColumn,
		$fieldGrouping,
		$texto,
		$icono,
		$color,
		$filterNumber,
		$orderF,
		$filterField,
		$sqlprimario,
		$estatus,
		$filterFieldDate,
		$filterFieldDateDefault,
		$fechaDesde,
		$fechaHasta,
		$widgetId,
	);

	$adb->pquery($sql, $params);
}

header('Location: index.php?module=admin_widgets&action=index');

?>

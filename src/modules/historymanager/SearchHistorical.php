<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/historymanager/lib/RecordHistoryHelper.class.php');

	global $adb, $app_strings, $current_language, $currentModule;

	$formodule    = PlatzillaUtils::purify ($_REQUEST, 'formodule');
	$record       = PlatzillaUtils::purify ($_REQUEST, 'record');
	$dayFrom      = PlatzillaUtils::purify ($_REQUEST, 'historyDateFrom');
	$dayUntil     = PlatzillaUtils::purify ($_REQUEST, 'historyDateTo');
	$searchFrom   = PlatzillaUtils::purify ($_REQUEST, 'historyPeriod');
	$dataToTab    = PlatzillaUtils::purify ($_REQUEST, 'activetab');

	$filterData = array (
		'filterField'     => PlatzillaUtils::purify ($_REQUEST, 'filterField'),
		'filterOperator'  => PlatzillaUtils::purify ($_REQUEST, 'filterOperator'),
		'filterValue'     => PlatzillaUtils::purify ($_REQUEST, 'filterValue'),
		'filterJoin'      => PlatzillaUtils::purify ($_REQUEST, 'filterJoin'),
		'filterGroupJoin' => PlatzillaUtils::purify ($_REQUEST, 'conditionGroups'),
		'indexGrupo'      => PlatzillaUtils::purify ($_REQUEST, 'indexGrupo'),
		'fieldId'         => PlatzillaUtils::purify ($_REQUEST, 'fieldId'),
		'moduleFilter'    => $formodule,
	);

	$idFieldSelected = '';
	$searchFilter    = RecordHistoryHelper::getSqlFilter($adb, $filterData);

	if (!empty ($filterData['filterField'])) {
		$advancedFilter = str_replace ($filterData ['filterField'],'newvalue',$searchFilter);
		$advancedFilter = "({$advancedFilter} ) OR (".str_replace ($filterData ['filterField'],'oldvalue',$searchFilter).')';
	} else {
		$advancedFilter = 1;
	}

	if (!empty ($filterData['fieldId'])) {
		$idFieldSelected = join (',',array_unique ($filterData ['fieldId']));
	}

	$arguments = array(
		'module'   => $formodule,
		'dayFrom'  => $dayFrom,
		'dayTo'    => $dayUntil,
		'record'   => $record,
		'sql'      => $advancedFilter,
		'fieldIds' => $idFieldSelected,
	);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('MOD', return_module_language ($current_language, $currentModule));
	if ($dataToTab == 'history-data') {
		$smarty->assign ('HISTORICALRECORDS', RecordHistoryHelper::getHistoryDataFromModule ($adb, $arguments));
		$dataTable = $smarty->fetch('modules/historymanager/historyTable.tpl');
	} else if ($dataToTab == 'history-events') {
		$smarty->assign ('RELHISTORY', RecordHistoryHelper::getHistoricalRelatedEvents($adb, $arguments));
		$dataTable = $smarty->fetch('modules/historymanager/historyEvents.tpl');
	} else {
		$dataTable = null;
	}
	echo $dataTable;
	exit ();

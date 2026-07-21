<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/historymanager/lib/RecordHistoryHelper.class.php');

	global $adb, $theme;

	$forModule    = PlatzillaUtils::purify ($_REQUEST, 'formodule');
	$fieldName    = PlatzillaUtils::purify ($_REQUEST, 'historyField');
	$record       = PlatzillaUtils::purify ($_REQUEST, 'record');
	$dayFrom      = PlatzillaUtils::purify ($_REQUEST, 'historyDateFrom');
	$dayUntil     = PlatzillaUtils::purify ($_REQUEST, 'historyDateTo');
	$searchFrom   = PlatzillaUtils::purify ($_REQUEST, 'historyPeriod');
	$typeGraphic  = PlatzillaUtils::purify ($_REQUEST, 'typeGraphic');

	try {
		if (empty ($fieldName)) {
			throw new Exception ('No has suministrado el nombre del campo que contiene la información a graficar');
		} else if (empty ($typeGraphic)) {
			throw new Exception ('No has suministrado el tipo de gráfico');
		}

		$filterData = array (
			'filterField'     => PlatzillaUtils::purify ($_REQUEST, 'filterField'),
			'filterOperator'  => PlatzillaUtils::purify ($_REQUEST, 'filterOperator'),
			'filterValue'     => PlatzillaUtils::purify ($_REQUEST, 'filterValue'),
			'filterJoin'      => PlatzillaUtils::purify ($_REQUEST, 'filterJoin'),
			'filterGroupJoin' => PlatzillaUtils::purify ($_REQUEST, 'conditionGroups'),
			'indexGrupo'      => PlatzillaUtils::purify ($_REQUEST, 'indexGrupo'),
			'moduleFilter'    => $forModule,
		);

		$advancedFilter  = null;
		$searchFilter    = RecordHistoryHelper::getSqlFilter ($adb, $filterData);
		if ((in_array($fieldName, $filterData ['filterField'])) && (!empty ($filterData['filterField']))) {
			$advancedFilter = str_replace($filterData ['filterField'], 'newvalue', $searchFilter);
			$advancedFilter = "({$advancedFilter} ) OR (" . str_replace($filterData ['filterField'], 'oldvalue', $searchFilter) . ')';
		}

		$arguments = array(
			'module'    => $forModule,
			'dayFrom'   => $dayFrom,
			'dayTo'     => $dayUntil,
			'record'    => $record,
			'sql'       => $advancedFilter,
			'fieldName' => array ($fieldName),
		);

		$historicalRecords = RecordHistoryHelper::getHistoryGraphicsData ($adb, $arguments);

		$graph = array (
			'applicationcode' => 'Historico_Registro',
			'dataGrafico'     => $historicalRecords,
			'graficoid'       => 0,
			'fieldoperation'  => $fieldName,
			'tipografico'     => $typeGraphic,
			'colors'          => array ('#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad'),
		);

		echo json_encode ($graph);
	} catch (Exception $e) {
		echo json_encode (array ('error' => $e->getMessage ()));
	}
	exit ();

<?php
	require_once ('include/platzilla/Data/GraphicManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/graficosgenerales/lib/GraphUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200311
	global $site_URL;
	require_once ('include/bugsnag-php-2.9.2/src/Bugsnag/Autoload.php');
	$bugsnag = new Bugsnag_Client('834d564193a48c47f138dc66d2cf5e83');
	$bugsnag->setAppVersion('1.0.0');
	if ($site_URL == 'https://apphome.platzillatest.com/') {
		$bugsnag->setReleaseStage('https://apphome.platzillatest.com/');
	} else if ($site_URL == 'https://app.platzilla.com/') {
		$bugsnag->setReleaseStage('https://app.platzilla.com/');
	} else {
		$bugsnag->setReleaseStage($site_URL);
	}
	$bugsnag->setErrorReportingLevel(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
	// Agregado por EB para integrar BUGSNAG - 20200311

	global $adb;

	$dateGrouping       = PlatzillaUtils::purify ($_REQUEST, 'dategrouping');
	$fieldGrouping      = PlatzillaUtils::purify ($_REQUEST, 'fieldgrouping');
	$fieldOperation     = PlatzillaUtils::purify ($_REQUEST, 'fieldoperation');
	$graphId            = PlatzillaUtils::purify ($_REQUEST, 'record');
	$graphicType        = PlatzillaUtils::purify ($_REQUEST, 'graphictype');
	$opColumn           = PlatzillaUtils::purify ($_REQUEST, 'opcolumn');
	$wModule            = PlatzillaUtils::purify ($_REQUEST, 'wmodules');
	$title              = trim(PlatzillaUtils::purify ($_REQUEST, 'graphcTitule'));
	$fieldToCalculation = PlatzillaUtils::purify ($_REQUEST, 'fieldsOperations');
	$activeTab          = PlatzillaUtils::purify ($_REQUEST, 'activeTab');
	$optionsGraphic     = PlatzillaUtils::purify ($_REQUEST, 'options');
	$createTable        = PlatzillaUtils::purify ($_REQUEST, 'createTable');
	$returnModule       = PlatzillaUtils::purify ($_REQUEST, 'return_module', 'graficosgenerales');
	$isLocked           = PlatzillaUtils::purify ($_REQUEST, 'isLocked', null);
	$optionsGraphic     = GraphUtils::getOptionChart ($optionsGraphic, $graphicType);

	if (is_array ($fieldToCalculation)) {
		$calculation = join (';', $fieldToCalculation);
	}
	$filterGroupJoin = PlatzillaUtils::purify ($_REQUEST, 'conditionGroups');
	$groupJoin       = (is_array ($filterGroupJoin)) ? array_pop ($filterGroupJoin) : null;

	$filterData = array (
		'filterField'     => PlatzillaUtils::purify ($_REQUEST, 'filterField'),
		'filterOperator'  => PlatzillaUtils::purify ($_REQUEST, 'filterOperator'),
		'filterValue'     => PlatzillaUtils::purify ($_REQUEST, 'filterValue'),
		'filterJoin'      => PlatzillaUtils::purify ($_REQUEST, 'filterJoin'),
		'filterGroupJoin' => $filterGroupJoin,
		'indexGrupo'      => PlatzillaUtils::purify ($_REQUEST, 'indexGrupo'),
		'moduleFilter'    => $wModule,
	);

	$sqlFilter = GraphUtils::getSqlFilterGraph ($adb, $filterData);
	if (!empty($sqlFilter)) {
		$sqlFilter   = json_encode ($sqlFilter, (JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT));
		$arrayFilter = json_encode ($filterData);
	} else {
		$filterData = '';
	}

	$isInstance = !empty ($_SESSION ['platInstancia']);



	if (!empty ($_REQUEST ['applicationcodes'])) {
		$applicationCodes = PlatzillaUtils::purify ($_REQUEST, 'applicationcodes');
	} else {
		$applicationCodes = null;
	}

	try {
		$chart = Graphic::getInstance ()
			->setApplicationCodes ($applicationCodes)
			->setDateGrouping (!empty ($dateGrouping) ? $dateGrouping : null)
			->setFieldName ($fieldOperation)
			->setFieldGrid (null)
			->setGroupBy (!empty ($fieldGrouping) ? $fieldGrouping: null)
			->setId (($isInstance && empty ($isLocked)) ? null : $graphId)
			->setLocked ($isInstance)
			->setModuleName ($wModule)
			->setOperation ($opColumn)
			->setTitle ($title)
			->setType ($graphicType)
			->setVariables ($arrayFilter)
			->setSqlQuery ($sqlFilter)
			->setFieldCompare(((isset ($calculation)) ? $calculation : null))
			->setCompareOperation (null)
			->setGraphicOptions ($optionsGraphic);

		$gm = GraphicManager::getInstance ($adb);
		$gm->saveChart ($chart);
		if (!empty ($createTable) && ($createTable != $graphicType)) {
			$tableOptions = array (
				'width'         => '100%',
				'forceIFrame'   => true,
				'page'          => 'enable',
				'pageSize'      => 10,
				'cssClassNames' => array (
					'headerRow'   => 'platzilla-headerRow',
					'tableRow'    => 'platzilla-tableRow',
					'oddTableRow' => 'platzilla-oddtableRow',
					'tableCell'   => 'platzilla-tableCell',
				),
			);
			$chart->setId (null);
			$chart->setType ($createTable);
			$chart->setGraphicOptions ($tableOptions);
			$gm->saveChart ($chart);
		}
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => null,
		);
	}
	header ("Location: index.php?module={$returnModule}&action=index&parenttab=Settings&activeTab={$activeTab}&tab=graphic");

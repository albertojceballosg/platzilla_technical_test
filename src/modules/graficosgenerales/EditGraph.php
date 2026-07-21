<?php
	require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');
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

	global $adb, $currentModule, $mod_strings, $smarty;

	$isInstance = !empty ($_SESSION ['platInstancia']);
	try {
		$graphId      = PlatzillaUtils::purify ($_REQUEST, 'record');
		$activeTab    = PlatzillaUtils::purify ($_REQUEST, 'activeTab');
		$returnModule = PlatzillaUtils::purify ($_REQUEST, 'return_module', 'graficosgenerales');
		if (empty ($graphId)) {
			throw new Exception ('No has suministrado el ID del gráfico');
		}

		$graph = GraphUtils::getGraphById ($adb, $graphId);

		if (empty ($graph)) {
			throw new Exception ('El gráfico solicitado no está registrado');
		}

		$gm              = GraphicManager::getInstance ($adb);
		$graphic         = $gm->fetchChart ($graphId);
		$modules         = json_decode ($graphic->getModuleName ());
		$availableFields = array ();

		foreach ($modules as $moduleName) {
			$availableFields [] = GraphUtils::getGraphicalColumnsData ($adb, $moduleName);
		}

		if (!empty($graphic->getGroupBy ())) {
			$dummy = explode ('.', $graphic->getGroupBy());
			if (count ($dummy) == 2) {
				$fieldGrouping = array ();
				foreach ($availableFields as $modules) {
					foreach ($modules as $fieldData) {
						if ($fieldData['tablename'] != $dummy [0]) {
							continue;
						} else if ((in_array ($fieldData ['typeofdata'], array ('V', 'D'))) && (!in_array($fieldData ['uitype'], array ('4096', '53', '258', '21', '2203')))) {
							$fieldGrouping[ $fieldData ['label'] ] = "{$fieldData ['tablename']}.{$fieldData ['fieldname']}";
						}
					}
				}
			} else {
				$modules = array_values (array_unique ($modules));
				$resultModules  = array ();
				if (count ($modules) > 2) {
					$k            = 1;
					$totalModules = count ($modules);
					foreach ($modules as $module) {
						for ($i = $k; $i < $totalModules; $i++) {
							$resultModules [] = GraphUtils::getModulesRel ($adb, array ($module, $modules [$i]));
						}
						$k++;
					}
				} else {
					$resultModules [] = GraphUtils::getModulesRel ($adb, $modules);
				}
			}
		}

		$moduleName = $graph ['fld_module'];
		if (!empty ($graph ['gridoperation'])) {
			$gridData = explode ('@', $graph ['gridoperation']);
			$row      = array (
				'fieldname'  => $graph ['fieldoperation'],
				'fieldlabel' => GraphUtils::getFieldLabel ($adb, $graph ['fieldoperation']),
			);
			$columns = array ();
			GraphUtils::getGridFields ($adb, $moduleName, $row, $columns, 'numeric');
			$graph ['fieldoperation'] .= '@' . $gridData [ 1 ];
			array_splice ($gridData,1,1);
			$graph ['gridoperation'] = join ('@', $gridData);
		} else {
			$columns = null;
		}

		$smarty->assign ('ACTIVE_APPLICATIONS', GraphUtils::getCategories ());
		$smarty->assign ('activeTab', $activeTab);
		$smarty->assign ('AVAILABLE_FIELDS', $availableFields);
		$smarty->assign ('AVAILABLE_DATE_GROUPINGS', GraphUtils::getDefinedDateGroupings ());
		$smarty->assign ('AVAILABLE_GROUPINGS', GraphUtils::getNumericColumns ($adb, $moduleName));
		$smarty->assign ('AVAILABLE_MODULES', GraphUtils::getModules ($adb));
		$smarty->assign ('AVAILABLE_OPERATIONS', GraphUtils::getDefinedOperations ());
		$smarty->assign ('AVAILABLE_TYPES', GraphUtils::getDefinedGraphTypes ());
		$smarty->assign ('CALCULATION_ROW', (!empty ($graphic->getFieldCompare ())) ? explode(';', $graphic->getFieldCompare ()) : null);
		$smarty->assign ('FIELDS_GROUPING', (isset ($fieldGrouping)) ? $fieldGrouping : null);
		$smarty->assign ('FIELDS_MODULE_GROUPING', (isset ($resultModules)) ? $resultModules[0] : null);
		$smarty->assign ('FILTER_TYPE', GraphUtils::getTypeOfData ());
		$smarty->assign ('GRAPH_DATA', $graph);
		$smarty->assign ('GRAPH', $graphic);
		$smarty->assign ('GRAPH_FILTER', GraphUtils::getFiltersGroup($adb, $graphic->getVariables()));
		$smarty->assign ('GRID_FIELDS', $columns);
		$smarty->assign ('OPERATION_COLUMNS', GraphUtils::getOperatorsCalculations ());
		$smarty->assign ('PROPERTIES', $gm->fetchChartOption ($graphic->getType (), $isInstance));
		$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULE', $currentModule);
		$smarty->assign ('RTN_MODULE', $returnModule);
		$smarty->assign ('RECORD', $graphId);
		$smarty->display ('modules/graficosgenerales/GraphDetails.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', "index.php?module={$currentModule}&action=index");
		$smarty->display ('Message.tpl');
	}

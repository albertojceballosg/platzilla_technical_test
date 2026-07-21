<?php
	// Aumentar timeout para carga de galería de gráficos (puede tener muchos gráficos)
	set_time_limit(300); // 5 minutos
	ini_set('max_execution_time', 300);
	
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ApplicationsManager.php');
	require_once ('include/platzilla/Data/GraphicManager.php');
	require_once ('include/platzilla/Utils/JSGraphicUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/how_use/lib/HowToUseHelper.php');

	global $adb, $theme, $currentModule, $current_user, $smarty, $site_URL;

	setBugSnag ($site_URL);

	$createBoxScoreAdvancedGraph = PlatzillaUtils::purify ($_REQUEST, 'registrarNuevoGraficoBSAvanzado2');
	$createBoxScoreSimpleGraph   = PlatzillaUtils::purify ($_REQUEST, 'registrarNuevoGraficoBS');
	$createRegularGraph          = PlatzillaUtils::purify ($_REQUEST, 'registrarNuevoGrafico');
	$isModal                     = PlatzillaUtils::purify ($_REQUEST, 'is_modal', null);
	$flMoule                     = PlatzillaUtils::purify ($_REQUEST, 'fl_module', null);
	$hiddenTab                   = PlatzillaUtils::purify ($_REQUEST, 'hidden_tab', null);
	$graphicCategory             = PlatzillaUtils::purify ($_REQUEST, 'graphicCategory', 'STANDARD');
	$returnModule                = PlatzillaUtils::purify ($_REQUEST, 'return_module', 'graficosgenerales');
	$idModeSelected              = PlatzillaUtils::purify ($_POST, 'howusename', 0);

	$searchFrom    = PlatzillaUtils::purify ($_REQUEST, 'graphicsPeriod');
	$graphicFrom   = PlatzillaUtils::purify ($_REQUEST, 'graphicsDateFrom');
	$graphicTo     = PlatzillaUtils::purify ($_REQUEST, 'graphicsDateTo');
	$activeTab     = PlatzillaUtils::purify ($_REQUEST, 'activeTab');
	$ajax          = PlatzillaUtils::purify ($_REQUEST, 'Ajax');
	$onlyFavorites = PlatzillaUtils::purify ($_REQUEST, 'Favorites');
	$fromHome      = PlatzillaUtils::purify ($_REQUEST, 'is_home', null);

	$objectDate = new DateTime();
	$dateTo     = $objectDate->format ('Y-m-d');
	$objectDate = new DateTime();
	$objectDate->modify ('-3 month');
	$dateFrom       = $objectDate->format ('Y-m-d');
	$graphicThMonth = $dateFrom;

	$objectDate = new DateTime();
	$objectDate->modify ('-1 day');
	$graphicToday = $objectDate->format ('Y-m-d');

	$objectDate = new DateTime();
	$objectDate->modify ('-7 day');
	$graphicWeek = $objectDate->format ('Y-m-d');

	$objectDate = new DateTime();
	$objectDate->modify ('-1 month');
	$dateMonth = $objectDate->format ('Y-m-d');

	$objectDate = new DateTime();
	$objectDate->modify ('-6 month');
	$graphicSixMonth = $objectDate->format ('Y-m-d');

	$objectDate = new DateTime();
	$objectDate->modify ('-12 month');
	$graphicYear = $objectDate->format ('Y-m-d');

	if (!empty($graphicTo)) {
		$dateTo = $graphicTo;
	}

	if ($searchFrom !== 'CUSTOM_DATE') {
		if (! empty($graphicFrom)) {
			$dateFrom = $graphicFrom;
		} else if (! empty($searchFrom)) {
			$dateFrom = $searchFrom;
		}
		
		if (empty($searchFrom)) {
			$searchFrom = $dateFrom;
		}
	} else {
		$dateFrom = $graphicFrom;
	}
	
	$isInstance = !empty ($_SESSION ['platInstancia']);

	if (!empty ($createBoxScoreSimpleGraph)) {
		GraphUtils::saveBoxScoreGraph (
			$adb,
			array (
				'comparar'           => PlatzillaUtils::purify ($_REQUEST, 'comparar'),
				'fld_module'         => 'BoxScore',
				'fieldOperation'     => '1',
				'operation'          => 1,
				'reporteavanzado'    => 1,
				'roles_grafico'      => PlatzillaUtils::purify ($_REQUEST, 'roles_grafico'),
				'sqlprimarioreporte' => PlatzillaUtils::purify ($_REQUEST, 'sqlprimarioreporte'),
				'tipoGrafico'        => 'barra',
				'title'              => PlatzillaUtils::purify ($_REQUEST, 'nombre'),
				'varreporte'         => PlatzillaUtils::purify ($_REQUEST, 'varreporte'),
			)
		);
	} else if (!empty ($createBoxScoreAdvancedGraph)) {
		GraphUtils::saveBoxScoreGraph (
			$adb,
			array (
				'comparar'           => 0,
				'fld_module'         => 'BoxScore',
				'fieldOperation'     => '1',
				'operation'          => 1,
				'reporteavanzado'    => 2,
				'roles_grafico'      => PlatzillaUtils::purify ($_REQUEST, 'roles_grafico'),
				'sqlprimarioreporte' => PlatzillaUtils::purify ($_REQUEST, 'sqlprimarioreporte'),
				'tipoGrafico'        => 'barra',
				'title'              => PlatzillaUtils::purify ($_REQUEST, 'nombre'),
				'varreporte'         => PlatzillaUtils::purify ($_REQUEST, 'varreporte'),
			)
		);
	}

	$categories = GraphUtils::getCategories ();

	foreach ($categories as $key => $category) {
		$categoryCatalg [ $key ] = array (
			'app_code' => $key,
			'app_name' => $category,
		);
	}

	$graphs = array (
		'applications'     => array (),
		'boxscoresimple'   => array (),
		'boxscoreadvanced' => array (),
		'others'           => array (),
	);
	$userCharts     = GraphicManager::getInstance ($adb)->fetchAllFavoriteGraphics ($current_user->id);
	$favoriteCharts = (count ($userCharts)) ? array_column ($userCharts, 'graficoid') : array ();
	if ($onlyFavorites && $fromHome) {
		$favorites = $favoriteCharts;
	} else if ($onlyFavorites && !$fromHome) {
		$flModule = PlatzillaUtils::purify ($_REQUEST, 'fl_module');
		$favorites = GraphicManager::getInstance ($adb)->fetchAllFavoriteByModule ($current_user->id, $flModule);
	} else if($graphicCategory == 'STANDARD') {
		$favorites = array ();
		$howToUse  = HowToUseHelper::getDefaultMode ($adb, $flMoule, $idModeSelected, 'GRAPHIC_VIEW');
		if(!empty($howToUse['howUseId'])) {
			$favorites = GraphUtils::customSort ($howToUse ['relatedView'], $howToUse ['viewId']);
		}
	} else {
		$favorites = array ();
	}

	$dateFilter = array (
		'dateFrom' => $dateFrom,
		'dateTo'   => $dateTo,
		'category' => ($graphicCategory == 'STANDARD') ? 0 : 1,
	);

	GraphicManager::getInstance($adb)->getBasicGraphics ($graphs, $isInstance, $categories, $dateFilter, $favorites, $flMoule);

	// INICIO DE GRAFICOS BOXSCORE
	if (PlatformUtils::isModuleEnabled ($adb, 'boxscore')) {
		// Consultando graficos BoxScore tipo 1
		$result = $adb->query ("SELECT g.* FROM vtiger_graficos g WHERE g.reporteavanzado=1 AND g.fld_module='BoxScore'");
		if (($result) && ($adb->num_rows ($result) > 0)) {
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$row ['dataGrafico'] = GraphUtils::getBoxScoreSimpleGraphData ($adb, $row);
				$row ['fechas']      = GraphUtils::getDatesFromVariables ($row);
				$countKPI            = count ($row ['dataGrafico']);
				if ($countKPI > 1) {
					foreach ($row ['dataGrafico'][0]['semanal'] as $key => $value) {
						$semanas [ $key ] = array ();
					}
				} else {
					$semanas = array ();
				}

				foreach ($semanas as $keyW => $valueW) {
					for ($i = 0; $i <= ($countKPI - 1); $i++) {
						$semanas [ $keyW ][ $i ]['titulo'] = $row ['dataGrafico'][ $i ]['box_score'];
						$semanas [ $keyW ][ $i ]['fecha']  = $row ['dataGrafico'][ $i ]['semanal'][ $keyW ]['fecha'];
						$semanas [ $keyW ][ $i ]['valor']  = $row ['dataGrafico'][ $i ]['semanal'][ $keyW ]['valor'];
					}
				}
				$row ['semanas']             = $semanas;
				$graphs ['boxscoresimple'][] = $row;
			}
		}

		// Consultando graficos BoxScore tipo 2
		$result = $adb->query ("SELECT g.* FROM vtiger_graficos g WHERE g.reporteavanzado=2 AND g.fld_module='BoxScore'");
		if (($result) && ($adb->num_rows ($result) > 0)) {
			require_once ('modules/graficosgenerales/graficosgenerales.php');
			$Grafico = new Graficos ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$Grafico->getDataGraficoBoxScoreTipoTwo ($row);
				$row ['dataGrafico']           = $Grafico->boxGraficar;
				$row ['semanas']               = $Grafico->boxGraficar;
				$row ['jsGraficoInd']          = $Grafico->jsGraficoInd;
				$graphs ['boxscoreadvanced'][] = $row;
			}
		}
	}
	$smarty      = new vtigerCRM_Smarty ();
	$graphsUtils = JSGraphicUtils::getInstance ($adb);
	$smarty->register_function('loadGraphic', array(&$graphsUtils, 'fetchGoogleChartJs'));
	$smarty->assign ('activeTab', $activeTab);
	$smarty->assign ('GRAPHIC_CATEGORY', $graphicCategory);
	$smarty->assign ('graphicToday', $graphicToday);
	$smarty->assign ('graphicWeek', $graphicWeek);
	$smarty->assign ('graphicMonth', $dateMonth);
	$smarty->assign ('graphicThMonth', $graphicThMonth);
	$smarty->assign ('graphicSixMonth', $graphicSixMonth);
	$smarty->assign ('graphicYear', $graphicYear);
	$smarty->assign ('graphicDateTo', $dateTo);
	$smarty->assign ('graphicDateFrom', $dateFrom);
	$smarty->assign ('searchFrom', $searchFrom);
	$smarty->assign ('MOD', return_module_language ($current_language, $currentModule));
	$smarty->assign ('APPLICATIONS', $categoryCatalg);
	$smarty->assign ('COLORS', array ('#2ecc71', '#e74c3c', '#f39c12', '#3fcfbb', '#626f70', '#8f44ad'));
	$smarty->assign ('GRAPHS', $graphs);
	$smarty->assign ('HIDEEN_TAB', $hiddenTab);
	$smarty->assign ('FAVORITES', $favoriteCharts);
	$smarty->assign ('IS_ADMIN', is_admin ($current_user));
	$smarty->assign ('IS_INSTANCE', $isInstance);
	if (!empty ($isModal)) {
		$smarty->assign ('IS_MODAL',$isModal);
	}
	$smarty->assign ('RTN_MODULE', $returnModule);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('OPERATIONS', GraphUtils::getDefinedOperations ());
	$smarty->assign ('THEME', $theme);
	if (!empty($ajax)) {
		if (!in_array ($returnModule, array ('Home', 'graficosgenerales'))) {
			$smarty->assign ('MODULE', $returnModule);
			echo $smarty->fetch ('GraphicModulesListView.tpl');
		} else {
			echo $smarty->fetch ('Home/TabsContents/GraphicListView.tpl');
		}
	} else {
		$smarty->display ('modules/graficosgenerales/index.tpl');
	}

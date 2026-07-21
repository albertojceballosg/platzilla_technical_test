<?php
	require_once ('include/platzilla/Data/BoxScoreManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	
	global $adb, $theme, $currentModule, $mod_strings, $site_URL, $smarty, $current_user;
	setBugSnag ($site_URL);
	
	$platform    = $_SESSION ['plat'];
	$local_user  = clone $current_user;
	$view        = PlatzillaUtils::purify ($_REQUEST, 'viewScale', 'Month');
	$app         = PlatzillaUtils::purify ($_REQUEST, 'codeApp');
	$monthSearch = PlatzillaUtils::purify ($_REQUEST, 'monthsearch', date ('m'));
	$function    = PlatzillaUtils::purify ($_REQUEST, 'function',null);
	
	$excludedCategories = array ('Marco','Infraestructura','Actividades','Revision','Control','Mejoras');
	$categories         = IndicatorsPanelHelper::getCategories ($excludedCategories);
	$categories ['KR']  = 'KR';
	
	foreach ($categories as $key => $category) {
		$categoryCatalg [ $key ] = array (
			'app_code' => $key,
			'app_name' => $category,
		);
	}

	//Getting boxscore data
	$allBoxScore     = array ();
	$allBlocks       = array ();
	$allCalculations = array ();

	$type = '';
	$from = PlatzillaUtils::purify ($_REQUEST, 'date_from');
	$to   = PlatzillaUtils::purify ($_REQUEST, 'date_to');

	$myBoxScore      = BoxScoreManager::getInstance ($adb)->fetchAllFavorites ($current_user->id);
	$userFavorites   = array_column ($myBoxScore, 'boxscorename');
	$record          = null;
	$totalCategories = count ($categoryCatalg);
	if ($totalCategories > 0 && (!empty($categoryCatalg))) {
		$categoryCode = array_column ($categoryCatalg, 'app_code');
		$codeFirst    = $categoryCode[0];
		for ($i = 0; $i < $totalCategories; $i++) {
			$code = $categoryCode[ $i ];
			if ($code != 'all') {
				$bsDefault = IndicatorsPanelHelper::getIndicatorDefault ($adb, $code, $view);
				$record    = $bsDefault['boxscoreid'];
				$boxScore  = IndicatorsPanel::getInstance ($adb, $monthSearch, $record, $from, $to);
				$boxScore->loadData ($record, $monthSearch, $type, 0, (!empty ($function) ? $userFavorites : array ()));
				$blocks               = $boxScore->getBlocks ($record, $type);
				$calculations         = null;
				$allBoxScore[ $code ] = array ($boxScore, $blocks, $calculations, $record);
			}
		}
	}
	if (($view == 'Week') && !empty ($monthSearch)) {
		$weeks = IndicatorsPanelHelper::getMonthDatesByWeek ($adb, intval ($monthSearch));
	}
	
	$categoryCatalg = (array ('all' => array ('config_applicationsid' => 0, 'app_code' => 'all', 'app_name' => $mod_strings['ALL_APLICATIONS'])) + $categoryCatalg);
	if ($smarty == null) {
		require_once ('Smarty_setup.php');
		$smarty = new vtigerCRM_Smarty;
	}
	$smarty->assign ('ALL_BOX_SCORE', $allBoxScore);
	$smarty->assign ('APPCODE', 'all');
	$smarty->assign ('APPLICATIONS', $categoryCatalg);
	$smarty->assign ('CODE_FIRST', $codeFirst);
	$smarty->assign ('FAVORITES', $userFavorites);
	$smarty->assign ('IS_ADMIN', $current_user->is_admin);
	$smarty->assign ('IS_HOME', (!empty ($function)));
	$smarty->assign ('IS_MOTHER', empty ($_SESSION ['platInstancia']));
	$smarty->assign ('MODSTRING', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('MONTH_SEARCH', $monthSearch);
	$smarty->assign ('TAB_ACTIVE', $app);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('URL_ACTION', 'allAppDetailView');
	$smarty->assign ('VIEW_SEARCH', $view);
	$smarty->assign ('WEEKS', $weeks);
	$smarty->assign ('YEAR_DATE', date ('Y'));

	if (PlatzillaUtils::purify ($_REQUEST, 'ajax')) {
		$smarty->display('modules/indicatorspanel/AllAppDetailView.tpl');
	} else if (PlatzillaUtils::purify ($_REQUEST, 'Ajax')) {
		$smarty->display('Home/TabsContents/BoxScoreHomeDetailView.tpl');
	} else {
		$smarty->display ('modules/indicatorspanel/index.tpl');
	}

<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/BoxScoreManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	
	global $adb, $theme, $currentModule, $mod_strings, $site_URL, $smarty, $current_user;
	setBugSnag ($site_URL);
	
	$view = PlatzillaUtils::purify ($_REQUEST, 'viewScale', 'Month');

	if (isset ($_REQUEST['codeApp'])) {
		$codeApp    = PlatzillaUtils::purify ($_REQUEST, 'codeApp');
		$bsDefault  = IndicatorsPanelHelper::getIndicatorDefault ($adb, $codeApp, $view);
		$record     = $bsDefault ['boxscoreid'];
	} else {
		$codeApp = null;
		$record  = null;
	}
	
	$monthSearch = PlatzillaUtils::purify ($_REQUEST, 'monthsearch', date ('m'));
	$recordId    = PlatzillaUtils::purify ($_REQUEST, 'record');

	if (empty($recordId)) {
		$recordId = $record;
	}
	
	$from = PlatzillaUtils::purify ($_REQUEST, 'date_from');
	$to   = PlatzillaUtils::purify ($_REQUEST, 'date_to');
	
	$type = PlatzillaUtils::purify ($_REQUEST, 'type');

	$boxScore = IndicatorsPanel::getInstance ($adb, $monthSearch, $recordId, $from, $to);
	//var_dump ($boxScore);
	$boxScore->loadData ($record, $monthSearch, $type);
	$blocks       = $boxScore->getBlocks ($record, $type);
	$calculations = $boxScore->getCalculations ($recordId, $monthSearch);
	$myBoxScore   = BoxScoreManager::getInstance ($adb)->fetchAllFavorites ($current_user->id);

	$year = date ('Y');
	$day  = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
	$from = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
	$to   = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));
	
	if (($view == 'Week') && !empty ($monthSearch)) {
		$weeks = IndicatorsPanelHelper::getMonthDatesByWeek ($adb, intval ($monthSearch));
		
	}

	$smarty = new vtigerCRM_Smarty;
	$smarty->assign ('APPCODE', $codeApp);
	$smarty->assign ('BLOCKS', $blocks);
	$smarty->assign ('BOX_SCORE', $boxScore);
	$smarty->assign ('CALCULATIONS', $calculations);
	$smarty->assign ('FAVORITES', array_column ($myBoxScore, 'boxscorename'));
	$smarty->assign ('IS_ADMIN', $current_user->is_admin);
	$smarty->assign ('IS_MOTHER', empty ($_SESSION ['platInstancia']));
	$smarty->assign ('MODSTRING', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('MONTH_SEARCH', $monthSearch);
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('URL_ACTION', 'DetailView');
	$smarty->assign ('VIEW_SEARCH', $view);
	$smarty->assign ('WEEKS', $weeks);
	$smarty->assign ('YEAR_DATE', date('Y'));

	$smarty->display ('modules/indicatorspanel/DetailView.tpl');

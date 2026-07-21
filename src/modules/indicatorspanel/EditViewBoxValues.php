<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	require_once ('Smarty_setup.php');
	
	global $adb, $theme, $currentModule, $current_user, $mod_strings, $app_strings, $site_URL;
	setBugSnag ($site_URL);

	$monthSearch = PlatzillaUtils::purify ($_REQUEST, 'monthsearch', date ('m'));
	$recordId    = PlatzillaUtils::purify ($_REQUEST, 'boxscoreid');
	$from        = PlatzillaUtils::purify ($_REQUEST, 'date_from');
	$to          = PlatzillaUtils::purify ($_REQUEST, 'date_to');
	$isHome      = PlatzillaUtils::purify ($_REQUEST, 'is_home', null);

	$type = PlatzillaUtils::purify ($_REQUEST, 'type');
	$view = PlatzillaUtils::purify ($_REQUEST, 'viewScale', 'Month');

	$boxScore = IndicatorsPanel::getInstance ($adb, $monthSearch, $recordId, $from, $to);
	$boxScore->loadData ($recordId, $monthSearch, $type);
	$blocks = $boxScore->getBlocks ($recordId, $type);

	$app = PlatzillaUtils::purify ($_REQUEST, 'app');

	$smarty = new vtigerCRM_Smarty;
	$smarty->assign ('MODSTRING', $mod_strings);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('MODULE', $currentModule);

	//assigning variables to editview boxscore
	$smarty->assign ('BOX_SCORE', $boxScore);
	$smarty->assign ('BLOCKS', $blocks);
	$smarty->assign ('IS_ADMIN', $current_user->is_admin);
	$smarty->assign ('IS_HOME', $isHome);
	$smarty->assign ('RECORD', $recordId);
	$smarty->assign ('APPCODE', $app);
	$smarty->assign ('MONTH_SEARCH', $monthSearch);
	$smarty->assign ('VIEW_SEARCH', $view);
	$smarty->assign ('TYPE', $type);
	$smarty->assign ('YEAR_DATE', date('Y'));

	echo $smarty->fetch ('modules/indicatorspanel/EditViewBoxValues.tpl');

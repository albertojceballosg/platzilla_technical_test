<?php
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	// Agregado por EB para integrar BUGSNAG - 20200527
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
	// Agregado por EB para integrar BUGSNAG - 20200527

	global $adb, $theme, $currentModule, $mod_strings, $smarty, $current_user;

	$local_user   = clone $current_user;
	$applications = IndicatorsPanelHelper::getAplicationsInstance ($adb, $_SESSION ['platInstancia'], $local_user, $current_user);

	$view = PlatzillaUtils::purify ($_REQUEST, 'viewScale', 'Month');

	//Getting boxscore data
	$record = null;
	if (count ($applications) > 0 && (!empty($applications))) {
		$aplicationCode = array_keys ($applications);
		$code           = $aplicationCode[0];
		$bsDefault      = IndicatorsPanelHelper::getIndicatorDefault ($adb, $code, $view);
		$record         = $bsDefault['boxscoreid'];
	} else {
		$code = null;
	}

	$app = PlatzillaUtils::purify ($_REQUEST, 'app');

	$monthSearch = PlatzillaUtils::purify ($_REQUEST, 'monthsearch', date ('m'));
	if ($record != null) {
		$recordId = $record;
	} else {
		$recordId = PlatzillaUtils::purify ($_REQUEST, 'record');
	}
	$from = PlatzillaUtils::purify ($_REQUEST, 'date_from');
	$to   = PlatzillaUtils::purify ($_REQUEST, 'date_to');

	$type = PlatzillaUtils::purify ($_REQUEST, 'type');

	$boxScore = IndicatorsPanel::getInstance ($adb, $monthSearch, $recordId, $from, $to);
	$boxScore->loadData ($record, $monthSearch, $type);
	$blocks       = $boxScore->getBlocks ($record, $type);
	$calculations = $boxScore->getCalculations ($recordId, $monthSearch);

	$year         = date ('Y');
	$day          = date ('d', mktime (0, 0, 0, ($monthSearch + 1), 0, date ('Y')));
	$from         = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, 1, date ('Y')));
	$to           = date ('Y-m-d', mktime (0, 0, 0, $monthSearch, $day, $year));
	$applications = (array ('all' => array ('config_applicationsid' => 0, 'app_code' => 'all', 'app_name' => $mod_strings['ALL_APLICATIONS'])) + $applications);

	$smarty->assign ('APPLICATIONS', $applications);
	$smarty->assign ('MODSTRING', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('TAB_ACTIVE', $app);

	//assigning variables to editview boxscore
	$smarty->assign ('BOX_SCORE', $boxScore);
	$smarty->assign ('BLOCKS', $blocks);
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('APPCODE', $code);
	$smarty->assign ('MONTH_SEARCH', $monthSearch);
	$smarty->assign ('VIEW_SEARCH', $view);
	$smarty->assign ('CALCULATIONS', $calculations);

	$smarty->display ('modules/indicatorspanel/index.tpl');

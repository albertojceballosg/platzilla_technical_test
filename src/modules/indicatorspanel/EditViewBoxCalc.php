<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	require_once ('Smarty_setup.php');
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

	global $adb, $app_strings, $currentModule, $mod_strings;

	$mode = PlatzillaUtils::purify ($_REQUEST, 'mode');
	$view = PlatzillaUtils::purify ($_REQUEST, 'viewScale', 'Month');

	$accountId   = PlatzillaUtils::purify ($_REQUEST, 'account_id');
	$app         = PlatzillaUtils::purify ($_REQUEST, 'app');
	$monthSearch = PlatzillaUtils::purify ($_REQUEST, 'monthsearch', date ('m'));
	$recordId    = PlatzillaUtils::purify ($_REQUEST, 'record');
	$from        = PlatzillaUtils::purify ($_REQUEST, 'date_from');
	$to          = PlatzillaUtils::purify ($_REQUEST, 'date_to');

	$type = PlatzillaUtils::purify ($_REQUEST, 'type');

	if ($mode == 'edit') {
		$boxScore    = IndicatorsPanel::getInstance ($adb, $monthSearch, $accountId, $from, $to);
		$calculation = $boxScore->getCalculationEdition ($recordId);
		$boxScore->loadBasicDataByBoxScoreId ($accountId, $type);
	} else {
		$boxScore    = null;
		$calculation = null;
	}

	$bsx = IndicatorsPanel::getInstance ($adb, $monthSearch, $accountId, $from, $to);
	$bsx->loadBasicDataByBoxScoreId ($accountId, $type);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ACCOUNT_ID', $accountId);
	$smarty->assign ('BOX_SCORE', $bsx);
	$smarty->assign ('CALCULATION', $calculation);
	$smarty->assign ('EDITABLE_BOX_SCORE', $boxScore);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODE', $mode);
	$smarty->assign ('RECORD', $recordId);
	$smarty->assign ('TYPE', $type);
	$smarty->assign ('CODE_APP', $app);
	$smarty->assign ('MONTH_SEARCH', $monthSearch);
	$smarty->assign ('VIEW_SEARCH', $view);
	$smarty->assign ('MODULE', $currentModule);

	echo $smarty->fetch ('modules/indicatorspanel/EditViewBoxCalc.tpl');

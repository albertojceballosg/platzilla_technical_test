<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/systemalerts/lib/SystemAlertsHelper.class.php');
	require_once ('Smarty_setup.php');

	global $adb, $currentModule, $mod_strings;

	$scaleSearch   = PlatzillaUtils::purify ($_REQUEST, 'viewScale');
	$app           = PlatzillaUtils::purify ($_REQUEST, 'app');
	$systemAlertId = PlatzillaUtils::purify ($_REQUEST, 'record');
	$from          = PlatzillaUtils::purify ($_REQUEST, 'from');
	$to            = PlatzillaUtils::purify ($_REQUEST, 'to');
	$sourceAlert   = PlatzillaUtils::purify ($_REQUEST, 'sourceAlert');

	$detailAlert = SystemAlertsHelper::getDetailIndicatorAlert ($adb, $systemAlertId, $from, $to);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('MODSTRING', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('VIEW_SEARCH', $scaleSearch);
	$smarty->assign ('DATE_FROM', $from);
	$smarty->assign ('DATE_TO', $to);
	$smarty->assign ('APP', $app);
	$smarty->assign ('DETAIL_ALERT', $detailAlert);
	$smarty->assign ('RECORD', $systemAlertId);
	$smarty->assign ('SOURCE_ALERT', $sourceAlert);

	echo $smarty->fetch ('modules/systemalerts/ViewIndicatorsAlerts.tpl');

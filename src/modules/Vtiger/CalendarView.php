<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CalendarViewUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	$ruleId = PlatzillaUtils::purify ($_REQUEST, 'rule', null);
	$viewId = PlatzillaUtils::purify ($_REQUEST, 'record');

	global $adb, $currentModule, $current_user, $mod_strings;

	$smarty = new vtigerCRM_Smarty ();
	try {
		if (empty ($viewId)) {
			throw new Exception ('No se ha suministrado el ID de la vista calendario');
		}

		$view          = CalendarViewUtils::getCalendarViewById ($adb, $viewId);
		$data          = CalendarViewUtils::getCalendarData ($adb, $view, $current_user, $ruleId);
		
		$smarty->assign ('CALENDAR_VIEWS', CalendarViewUtils::getCalendarViewByModules ($adb));
		$smarty->assign ('CALENDAR_TYPE', '');
		$smarty->assign ('DATA', $data);
		$smarty->assign ('MOD', $mod_strings);
		$smarty->assign ('MODULE', $currentModule);
		$smarty->assign ('RULE_ID', !empty($ruleId) ? $ruleId : null);
		$smarty->assign ('VIEW', $view);
		$smarty->assign ('VIEW_ID', $viewId);
		$smarty->display ('CalendarView.tpl');
	} catch (Exception $e) {
		$smarty->assign ('LABEL', 'Volver');
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('TYPE', 'ERROR');
		$smarty->assign ('URL', "index.php?module={$currentModule}&action=index");
		$smarty->display ('Message.tpl');
	}

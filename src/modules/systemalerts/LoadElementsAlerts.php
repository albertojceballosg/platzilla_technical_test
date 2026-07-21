<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/systemalerts/lib/SystemAlertsHelper.class.php');

	global $adb, $mod_strings, $current_language, $current_user;

	$function = PlatzillaUtils::purify ($_REQUEST, 'function');
	$period   = PlatzillaUtils::purify ($_REQUEST, 'viewPeriod');

	if ($function == 'paramFieldElements') {
		$appSelect = PlatzillaUtils::purify ($_REQUEST, 'appSelect');
		$type      = PlatzillaUtils::purify ($_REQUEST, 'type');

		$local_user = clone $current_user;
		if ($type == 'Indicators') {
			$element = SystemAlertsHelper::getFieldElementIndicators ($adb, $appSelect, $period);
		} else {
			$element = SystemAlertsHelper::getFieldElementModule ($adb, $appSelect, $_SESSION ['platInstancia'], $current_user->is_admin);
		}
		echo json_encode ($element);
	}

	if ($function == 'codeElementField') {
		$tab       = PlatzillaUtils::purify ($_REQUEST, 'tabid');
		$tabName   = PlatzillaUtils::purify ($_REQUEST, 'tabname');
		$fieldsTab = SystemAlertsHelper::getFieldsModule ($tabName, $current_language);
		echo json_encode ($fieldsTab);
	}

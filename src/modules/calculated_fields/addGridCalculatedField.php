<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	require_once ('modules/calculated_fields/lib/CalculatedFieldsHelper.class.php');
	require_once ('vtlib/Vtiger/Utils.php');
	// Agregado por EB para integrar BUGSNAG - 20200326
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
	// Agregado por EB para integrar BUGSNAG - 20200326

	global $adb, $current_language;

	$fieldId = SettingsUtils::purify ($_REQUEST, 'record');
	$subfieldId = SettingsUtils::purify ($_REQUEST, 'subRecord');
	$smarty = new vtigerCRM_Smarty ();
	$equationData = CalculatedFieldsHelper::getEquationData($adb,$subfieldId);

	if($equationData) {
		$groupName = array();
		$totalGroup = count($equationData['operatorGroup']);
		for ($i = 0; $i < $totalGroup; $i++) {
			$groupName[] = substr('abcdefghijklmnopqrstuvwxyz', $i, 1);
		}

		$smarty->assign('TYPE_ELEMENT', $equationData['typeElement']);
		$smarty->assign('SUBFIELD_NAME', $equationData['subFieldName']);
		$smarty->assign('REFERENCE', $equationData['calculatedRefrence']);
		$smarty->assign('ELEMENT_VALUE', $equationData['elemValue']);
		$smarty->assign('OPERATOR', $equationData['operator']);
		$smarty->assign('OPERATOR_GROUP', $equationData['operatorGroup']);
		$smarty->assign('GROUP_NAME', $groupName);
	};

	$smarty->assign ('MOD', return_module_language ($current_language, 'calculated_fields'));
	$smarty->assign('SUBFIELD', CalculatedFieldsHelper::getGridColumns($adb,$fieldId));
	$smarty->assign('FIELD_ID', $fieldId);
	$smarty->assign('SUBFIELD_ID', $subfieldId);
	$smarty->assign('MODULE_NAME', CalculatedFieldsHelper::getModuleName($adb,$fieldId));
	$smarty->display ('modules/calculated_fields/CreateCalculatedGridFields.tpl');

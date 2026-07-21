<?php
	require_once ('modules/calculated_fields/lib/CalculatedFieldsHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
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

	global $adb;

	$fieldId           = SettingsUtils::purify ($_REQUEST, 'fieldId');
	$subfieldId           = SettingsUtils::purify ($_REQUEST, 'subfieldId');

	$fieldsData = array (
		'typeElement'        => SettingsUtils::purify ($_REQUEST, 'typeElement'),
		'subFieldName'       => SettingsUtils::purify ($_REQUEST, 'subFieldId'),
		'calculatedRefrence' => SettingsUtils::purify ($_REQUEST, 'calculatedRefrence'),
		'elemValue'          => SettingsUtils::purify ($_REQUEST, 'elemValue'),
		'operator'           => SettingsUtils::purify ($_REQUEST, 'operator'),
		'operatorGroup'      => SettingsUtils::purify ($_REQUEST, 'operatorGroup'),
	);

	$equation = CalculatedFieldsHelper::saveCalculatedField($adb,$fieldsData, $subfieldId);
	$equation = str_replace('_'.$fieldId.'[x]','',$equation);
	echo $equation;
	exit();

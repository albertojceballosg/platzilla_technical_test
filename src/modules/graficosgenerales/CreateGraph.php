<?php
	require_once ('modules/graficosgenerales/lib/GraphUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200311
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
	// Agregado por EB para integrar BUGSNAG - 20200311

	global $adb, $currentModule, $mod_strings, $smarty, $adv_filter_options;

	$smarty->assign ('ACTIVE_APPLICATIONS', GraphUtils::getCategories ());
	$smarty->assign ('AVAILABLE_DATE_GROUPINGS', GraphUtils::getDefinedDateGroupings ());
	$smarty->assign ('AVAILABLE_OPERATIONS', GraphUtils::getDefinedOperations ());
	$smarty->assign ('AVAILABLE_MODULES', GraphUtils::getModules ($adb));
	$smarty->assign ('AVAILABLE_TYPES', GraphUtils::getDefinedGraphTypes ());
	$smarty->assign ('IS_INSTANCE', !empty ($_SESSION ['platInstancia']));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('OPERATION_COLUMNS', GraphUtils::getOperatorsCalculations ());
	$smarty->display ('modules/graficosgenerales/GraphDetails.tpl');

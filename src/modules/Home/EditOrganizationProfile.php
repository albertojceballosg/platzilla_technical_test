<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	// Agregado por EB para integrar BUGSNAG - 20200330
	global $site_URL, $adb;
	
	setBugSnag ($site_URL);
	$dayOfWeek = array (
		'MONDAY'    => 'Lunes',
		'TUESDAY'   => 'Martes',
		'WEDNESDAY' => 'Miércoles',
		'THURSDAY'  => 'Jueves',
		'FRIDAY'    => 'Viernes',
		'SATURDAY'  => 'Sábado',
		'SUNDAY'    => 'Domingo',
	);
	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('AVAILABLE_COUNTRIES', HomeUtils::getAvailableCountries ());
	$smarty->assign ('AVAILABLE_CURRENCIES', HomeUtils::getAvailableCurrencies ());
	$smarty->assign ('AVAILABLE_DEFAULT_MODULES', HomeUtils::getAvailableDefaultModules ($adb));
	$smarty->assign ('DAY_OF_WEEK', $dayOfWeek);
	$smarty->assign ('ORGANIZATION', HomeUtils::getOrganizationDetails ($adb, $_SESSION ['plat']));
	$smarty->assign ('ORGANIZATION_CURRENCY', HomeUtils::getOrganizationCurrency ($adb));
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Home/EditOrganizationProfile.tpl');

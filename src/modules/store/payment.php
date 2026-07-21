<?php
//	require_once ('include/Braintree/Braintree.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	date_default_timezone_set ('UTC');

	global $app_strings, $mod_strings, $smarty, $theme;
	if ((!isset ($smarty)) || (!$smarty)) {
		require_once ('Smarty_setup.php');
		$smarty = new vtigerCRM_Smarty ();
	}

// Se comento para verificar funcionamiento de proceso de facturación*/
//	function integrationMerchantConfig () {
//		if (0) {
//			Braintree_Configuration::environment ('sandbox');
//			Braintree_Configuration::merchantId ('qwkhy92jkp5dryrm');
//			Braintree_Configuration::publicKey ('26zv6rjhx6nf7dks');
//			Braintree_Configuration::privateKey ('0e5a34a66d700f97e4f471417bb64a71');
//		} else {
//			Braintree_Configuration::environment ('production');
//			Braintree_Configuration::merchantId ('cmg4g9f6zr5tdcbm');
//			Braintree_Configuration::publicKey ('fztbrmr28d7fhjw2');
//			Braintree_Configuration::privateKey ('d881bb33f43e96f052c30be7aa0ec45f');
//		}
//	}
//
//	integrationMerchantConfig ();
//
//	$planes = array (
//		array ('Plan Básico Anual', 12 * 12, 365),
//		array ('Plan Básico Mensual', 15, 30),
//		array ('Plan Plus Anual', 29 * 12, 365),
//		array ('Plan Plus Mensual', 35, 30),
//		array ('Plan Profesional Anual', 49 * 12, 365),
//		array ('Plan Profesional Mensual', 55, 30),
//		array ('Plan Premium Anual', 99 * 12, 365),
//		array ('Plan Premium Mensual', 129, 30),
//	);
//
// $clientToken = Braintree_ClientToken::generate ();

	if (isset ($_REQUEST ['usersCounter'])) {
		$totalUsers = vtlib_purify($_REQUEST ['usersCounter']);
		$_SESSION ['usersCounter'] = $totalUsers;
	} else {
		$totalUsers = 0;
		$_SESSION ['usersCounter'] = $totalUsers;
	}

	if (isset ($_REQUEST ['module'])) {
		$module = vtlib_purify($_REQUEST ['module']);
	} else {
		$module = null;
	}

	// Calculando monto del pago (por las aplicaciones)
	$paymentTotal = 0;
	$plan         = '';
	foreach ($_SESSION ['appsToContract'] as $application) {
		$paymentTotal += $application ['app_price'];
		$plan .= $application ['app_name'] . ',';
	}

	// Calculando monto del pago (por los usuarios)
	$instanceDetails = StoreUtils::getInstanceDetails ($_SESSION ['platInstancia']);
	$usuariosContratados = intval ($instanceDetails ['numusuarios']);
	if ($totalUsers > $usuariosContratados) {
		$totalUsers = ($totalUsers - $usuariosContratados);
	}
	$paymentTotal += ($totalUsers * StoreUtils::getPriceForUser ());
	$plan = substr ($plan, 0, (strlen ($plan) - 1));

	$_SESSION ['usersCounter'] = $totalUsers;
	$_SESSION ['amounttopay']  = $paymentTotal;
	StoreUtils::updateInstanceOwnershipDetails ($_SESSION ['platInstancia'], $_REQUEST);

	$smarty->assign ('AMOUNT', $paymentTotal);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MODULE', $module);
	$smarty->assign ('PLAN_PAGO', $plan);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('TOTALUSERS', $totalUsers);

// Se comento para verificar funcionamiento de proceso de facturación*/
// $smarty->assign ('TOKEN_BRAINTREE', $clientToken);
	$smarty->display ('modules/store/payment.tpl');

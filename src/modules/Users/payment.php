<?php
	require_once "include/Braintree/Braintree.php";

	/**  function integrationMerchantConfig()
	{
		if (0) {
		Braintree_Configuration::environment('sandbox');
		Braintree_Configuration::merchantId('qwkhy92jkp5dryrm');
		Braintree_Configuration::publicKey('26zv6rjhx6nf7dks');
		Braintree_Configuration::privateKey('0e5a34a66d700f97e4f471417bb64a71');
		} else {
		Braintree_Configuration::environment('production');
		Braintree_Configuration::merchantId('cmg4g9f6zr5tdcbm');
		Braintree_Configuration::publicKey('fztbrmr28d7fhjw2');
		Braintree_Configuration::privateKey('d881bb33f43e96f052c30be7aa0ec45f');
		}
	} */

	function integrationMerchantConfig() {
		if (0) {
			Braintree_Configuration::environment('sandbox');
			Braintree_Configuration::merchantId('wkzd8gcnr7cbtw3x');
			Braintree_Configuration::publicKey('hnn8d3fct28hqbdz');
			Braintree_Configuration::privateKey('af38947ddfd7d50d37fdd9d5c6987e9c');
			/**} else {
			 * Braintree_Configuration::environment('production');
			 * Braintree_Configuration::merchantId('cmg4g9f6zr5tdcbm');
			 * Braintree_Configuration::publicKey('fztbrmr28d7fhjw2');
			 * Braintree_Configuration::privateKey('d881bb33f43e96f052c30be7aa0ec45f');
			 * }*/
		}
	}

	integrationMerchantConfig();

	date_default_timezone_set('UTC');
	
	global $mod_strings,$app_strings,$theme,$bDlgModales;
	
	$planes = array(
		array('Plan Básico Anual',12*12,365),
		array('Plan Básico Mensual',15,30),
		array('Plan Plus Anual',29*12,365),
		array('Plan Plus Mensual',35,30),
		array('Plan Profesional Anual',49*12,365),
		array('Plan Profesional Mensual',55,30),
		array('Plan Premium Anual',99*12,365),
		array('Plan Premium Mensual',129,30),
		);

	$clientToken = Braintree_ClientToken::generate();
	$smartyDlg = new vtigerCRM_Smarty;
	$smartyDlg->assign('MODULE',$_REQUEST['module']);
	$smartyDlg->assign('MOD',$mod_strings);
	$smartyDlg->assign('APP',$app_strings);
	$smartyDlg->assign('THEME', $theme);
	$smartyDlg->assign('TOKEN_BRAINTREE', $clientToken);
	list($plan,$amount,$days) = $planes[($_REQUEST['amount']-1)];
	$smartyDlg->assign('AMOUNT', $amount);
	$smartyDlg->assign('PLAN_PAGO', $plan);
	$smartyDlg->assign('PLANES', $_REQUEST['amount']);
	$_SESSION['amounttopay'] = $amount;
	
	$smartyDlg->display('payment.tpl');

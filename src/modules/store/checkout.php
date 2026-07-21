<?php
//	require_once ('include/Braintree/Braintree.php');
	require_once ('include/utils/VtlibUtils.php');
	require_once ('modules/store/procesoFactura.php');

	function getModulesFromApp ($applicationId) {
		$modules = array ();
		$adb     = AdbManager::getInstance ()->getMasterAdb ();
		$result  = $adb->pquery (
			'SELECT t.tabid,name FROM vtiger_configapps_tab capps JOIN vtiger_tab t ON (capps.tabid=t.tabid) WHERE config_applicationsid=?',
			array ($applicationId)
		);
		while ($row = $adb->fetchByAssoc ($result)) {
			$modules [] = $row;
		}
		return $modules;
	}

	function updateModulesFromAppOnPurchase ($applicationId, $instanceName) {
		$modules = getModulesFromApp ($applicationId);
		$adb     = AdbManager::getInstance ()->getMasterAdb ();
		foreach ($modules as $module) {
			$adb->pquery (
				'UPDATE vtiger_instancemodules SET status=?, dateiniservice=? WHERE instancecode=? AND modulename=?',
				array ('activo', date ('Y-m-d'), $instanceName, $module ['name'])
			);
		}
	}

	function actualizaApps () {
		$adb         = AdbManager::getInstance ()->getMasterAdb ();
		$paymentType = 'anual';

		// Actualizando cantidad máxima de usuarios
		$adb->pquery ('UPDATE vtiger_instancias SET numusuarios=? WHERE code=?', array ($_SESSION ['usersCounter'], $_SESSION ['platInstancia']));

		// Actualizando instancias
		foreach ($_SESSION ['appsToContract'] as $application) {
			// Consultando si existe la app
			$result = $adb->pquery ('SELECT * FROM vtiger_instanceapplications WHERE instancecode=? AND applicationid=?', array ($_SESSION ['platInstancia'], $application ['applicationid']));
			if ($adb->num_rows ($result) > 0) {
				// Actualizando la app
				$sql = "UPDATE vtiger_instanceapplications SET status='activa', price=?, dateiniservice=?, dateendservice=DATE_ADD(curdate(), INTERVAL 1 YEAR), renovationtype=? WHERE instancecode=? AND applicationid=?";
				$adb->pquery ($sql, array ($application ['app_price'], date ('Y-m-d'), $paymentType, $_SESSION ['platInstancia'], $application ['applicationid']));
				updateModulesFromAppOnPurchase ($application ['applicationid'], $_SESSION ['platInstancia']);
			} else {
				// Obteniendo id del servicio
				$sql           = 'SELECT serviceid FROM vtiger_service WHERE servicename=?';
				$resultService = $adb->pquery ($sql, array (html_entity_decode ($application ['app_name'], ENT_QUOTES, 'UTF8')));
				$serviceId     = $adb->query_result ($resultService, 0, 'serviceid');

				// registrando nueva app
				$sql = 'INSERT INTO vtiger_instanceapplications (instancecode, applicationid, status, dateiniservice, serviceid, price, renovationtype, dateendservice) VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(curdate(), INTERVAL 1 YEAR))';
				$adb->pquery ($sql, array ($_SESSION ['platInstancia'], $application ['applicationid'], 'activa', date ('Y-m-d'), $serviceId, $application ['app_price'], $paymentType));

				// Llenando la tabla que relaciona modulos a instancias
				$newModules = getModulesFromApp ($application ['appId']);
				foreach ($newModules as $newModule) {
					$sql = "INSERT INTO vtiger_instancemodules (instancecode, modulename, status, datedemo, dateiniservice) VALUES (?, ?, ?, ?, ?)";
					$adb->pquery ($sql, array ($_SESSION ['platInstancia'], $newModule ['name'], 'activo', date ('Y-m-d'), date ('Y-m-d')));
				}
			}
		}
	}

	date_default_timezone_set ('UTC');

	global $app_strings, $current_language, $mod_strings, $smarty, $theme;
	if ((!isset ($smarty)) || (!$smarty)) {
		require_once ('Smarty_setup.php');
		$smarty = new vtigerCRM_Smarty ();
	}

	$nonce = isset ($_POST ['payment_method_nonce']) ? $_POST ['payment_method_nonce'] : null;

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
//	integrationMerchantConfig ();
//
//	$result = Braintree_Transaction::sale (array (
//		'amount'             => $_SESSION['amounttopay'],
//		'paymentMethodNonce' => $nonce,
//		'options'            => array ('submitForSettlement' => true),
//	));
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

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('MODULE', $_REQUEST ['module']);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('THEME', $theme);
//	list($plan,$amount,$days) = $planes[$_REQUEST['amount']-1];

//	if ($result->success) {
		$smarty->display ('modules/store/approved.tpl');
		generaFacturaApps ();
		actualizaApps ();
		//Se actualiza la fecha de expiración según la suscripción tanto en platzilla, como en marketing
//		updateProximoVencimiento ($_SESSION['plat'], $days, $plan);
//	} else {
//		$smarty->assign ('AMOUNT', $_REQUEST['amount']);
//		$smarty->display ('modules/store/unapproved.tpl');
//
//		generaFacturaApps (); // Se colocó sólo de prueba
//		actualizaApps (); // Se colocó sólo de prueba
//	}

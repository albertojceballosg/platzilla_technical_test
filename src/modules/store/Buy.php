<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $adb, $current_user, $theme;

	$isInstance = !empty ($_SESSION ['platInstancia']) ? true : false;

	$smarty = new vtigerCRM_Smarty ();
	if ((!is_admin ($current_user)) || (!$isInstance)) {
		// En caso de que el Store no se ejecute desde una instancia, sino desde la Plataforma Madre - AGREGADO AV 20170612
		$smarty->display ('modules/store/storeNoActivo.tpl');
	} else {
		$applicationCodeToDelete = PlatzillaUtils::purify ($_REQUEST, 'deleteapplication');
		$applications            = StoreUtils::getInstanceApplicationsPendingForPayment ($_SESSION ['platInstancia']);
		$instance                = StoreUtils::getInstanceDetails ($_SESSION ['platInstancia']);
		$priceForUser = StoreUtils::getPriceForUser ();
		if (!empty ($applicationToDelete)) {
			foreach ($applications as $index => $application) {
				if ($application ['app_code'] == $applicationCodeToDelete) {
					unset ($applications [ $index ]);
				}
			}
		}
		$_SESSION ['cart'] = array (
			'applications' => $applications,
			'users'        => 0,
		);

		$smarty->assign ('APPSIMAGE_PATH', 'storage/appsimages');
		$smarty->assign ('CART', $_SESSION ['cart']);
		$smarty->assign ('COUNTRIES', PlatzillaUtils::getCountries ($adb));
		$smarty->assign ('INSTANCE', $instance);
		$smarty->assign ('PRICE_FOR_USER', $priceForUser);
		$smarty->assign ('THEME', $theme);
		$smarty->display ('modules/store/Buy.tpl');
	}
/*
	require_once ('include/utils/VtlibUtils.php');

	global $app_strings, $current_language, $mod_strings, $smarty, $theme;
	if ((!isset ($smarty)) || (!$smarty)) {
		require_once ('Smarty_setup.php');
		$smarty = new vtigerCRM_Smarty ();
	}

	// Total Users Added - Added AV 20170620
	if (isset ($_SESSION ['usersCounter'])) {
		$totalUsers = vtlib_purify ($_SESSION ['usersCounter']);
	} else {
		$totalUsers = 0;
	}
	// Total Users Added - Added AV 20170620

	$themePath             = "themes/$theme";
	$deleteApplicationCode = isset ($_REQUEST ['deleteApp']) ? vtlib_purify ($_REQUEST ['deleteApp']) : null;
	$priceForUser          = getPriceForUsers ();
	$usuariosContratados   = getUsuariosContratados ();
	// Countries For Select - Added AV 20170620
	$paisesForSelect = getPaisesForSelect ();
	// Countries For Select - Added AV 20170620
	if (!$usuarioscontratados) {
		$usuarioscontratados = 1;
	}

	// Si se elimina una aplicacion en la pantalla de confirmacion
	if ($deleteApplicationCode) {
		$appsToContract = $_SESSION ['appsToContract'];
		foreach ($appsToContract as $key => $value) {
			if ($value ['app_code'] == $_REQUEST ['deleteApp']) {
				unset ($appsToContract [ $key ]);
				break;
			}
		}

		$appsToContract             = array_values (array_values ($appsToContract)); // 'reindex' array
		$_SESSION['appsToContract'] = $appsToContract;
	}

	$appsToContract = $_SESSION ['appsToContract'];
	foreach ($appsToContract as $key => $value) {
		$_SESSION ['appsToContract'][ $key ]['app_price'] = $value ['app_price'];
	}

	$appsToContract = $_SESSION['appsToContract'];
	if (!empty ($_SESSION ['appsToContract'])) {
		foreach ($appsToContract as $key => $value) {
			foreach ($_SESSION ['appsContratadas'] as $keyContratadas => $valueContratadas) {
				if ($value ['appId'] == $_SESSION ['appsContratadas'][ $keyContratadas ]['appId']) {
					unset ($appsToContract [ $key ]);
				}
			}
		}
		$appsToContract              = array_values (array_values ($appsToContract)); // 'reindex' array
		$_SESSION ['appsToContract'] = $appsToContract;
	}

	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('APPSIMAGE_PATH', 'storage/appsimages/');
	$smarty->assign ('APPSTOCONTRACT', $_SESSION ['appsToContract']);
	$smarty->assign ('CMOD', $mod_strings);
	$smarty->assign ('IMAGE_PATH', "$themePath/images/");
	$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
	$smarty->assign ('ORGANIZATIONDETAILS', getInstanceClientAndAccountDetails ());
	$smarty->assign ('PRICEFORUSERS', $priceForUser);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('THEME_PATH', "{$themePath}/");
	$smarty->assign ('TOTALPRICEFORUSERS', ($priceForUser * $totalUsers));
	$smarty->assign ('USUARIOSCONTRATADOS', $usuariosContratados);
	// Currently Users Added  - Added AV 20170620
	//$smarty->assign ('USUARIOSCONTRATADOSALMOMENTO', ($usuariosContratados - 20)); habilitar cuando se suban los cambios
	$smarty->assign ('USUARIOSCONTRATADOSALMOMENTO', (2));
	//$smarty->assign ('TOTALPRICEFORUSERSALMOMENTO', ($priceForUser * ($usuariosContratados - 20))); habilitar cuando se suban los cambios
	$smarty->assign ('TOTALPRICEFORUSERSALMOMENTO', ($priceForUser * 2));
	// Currently Users Added  - Added AV 20170620
	// Countries For Select - Added AV 20170620
	$smarty->assign ('PAISESFORSELECT', $paisesForSelect);
	// Countries For Select - Added AV 20170620
	// Total Users Added - Added AV 20170620
	$smarty->assign ('TOTALUSERS', $totalUsers);
	// Total Users Added - Added AV 20170620
	$smarty->display ('modules/store/confirmacion.tpl');
*/
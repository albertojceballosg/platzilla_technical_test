<?php
	global $adb, $currentModule, $platPrincipal;

	// Se respaldan las variables y se le asignan los valores necesarios
	$authenticatedUserMenuBackup                     = $_SESSION ['authenticated_user_menu']['tabdata'];
	$_SESSION ['authenticated_user_menu']['tabdata'] = '';

	$moduleBackup        = $_REQUEST ['module'];
	$_REQUEST ['module'] = 'myinvoice';

	$currentModuleBackup = $currentModule;
	$currentModule       = 'myinvoice';

	$platBackup        = $_SESSION ['plat'];
	$_SESSION ['plat'] = $platPrincipal;

	// Se conecta a la plataforma principal
	$adb = conectaPlataformaHija ($platPrincipal);

	// Se cargan las funciones
	require_once ('modules/myinvoice/myinvoice.php');
	require_once ('modules/myinvoice/CreatePDF.php');

	// Se restauran los valores a las variables modificadas
	$_SESSION ['plat']                               = $platBackup;
	$currentModule                                   = $currentModuleBackup;
	$_REQUEST ['module']                             = $moduleBackup;
	$_SESSION ['authenticated_user_menu']['tabdata'] = $authenticatedUserMenuBackup;

	// Se conecta nuevamente a la instancia
	$adb = conectaPlataformaHija ($_SESSION ['plat']);

<?php
	global $adb, $platPrincipal;

	// Se respaldan las variables y se le asignan los valores necesarios
	$authenticatedUserMenuBackup                     = $_SESSION ['authenticated_user_menu']['tabdata'];
	$_SESSION ['authenticated_user_menu']['tabdata'] = '';

	$moduleBackup       = $_REQUEST ['module'];
	$_REQUEST['module'] = 'myinvoice';

	// Se conecta a la plataforma principal
	$adb = conectaPlataformaHija ($platPrincipal);

	// Se carga la función
	require_once ('modules/myinvoice/reportValidate.php');

	// Se restauran los valores a las variables modificadas
	$_REQUEST ['module']                             = $moduleBackup;
	$_SESSION ['authenticated_user_menu']['tabdata'] = $authenticatedUserMenuBackup;

	// Se conecta nuevamente a la instancia
	$adb = conectaPlataformaHija ($_SESSION ['plat']);
	exit ();

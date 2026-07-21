<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/utils.php');
	include_once 'modules/notificaciones/notificaciones.php';
	include_once 'modules/Home/invoiceCustomer.php';

	$obj = new CNotificaciones;
	global $currentModule, $current_language, $adb, $current_user;
	$currentModule           = 'notificaciones';
	$mod_strings             = return_module_language ($current_language, $currentModule);
	$_REQUEST['contactid'][] = 818;
	$_REQUEST['contactid'][] = 828;
	$obj->GuardarNotificacion ('NOTIFICACION_PLANA',
		ACCOUNTID_EMPRESAFACIL,
		$_REQUEST['subject'],
		$_REQUEST['textoMensaje'],
		$_REQUEST['contactid']);

	global $adb;

	$adb = conectaPlataformaHija ($_SESSION['plat']);
	require ('modules/Home/CustomerView.php');

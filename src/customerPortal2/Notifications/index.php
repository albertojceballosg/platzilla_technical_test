<?php
	include("PortalConfig.php");
	include('../include/utils/interfazAuxiliar.php');
	include("../time/modules/notificaciones/notificaciones.php");
	
	global $default_language;
	setPortalCurrentLanguage();
	$default_language = getPortalCurrentLanguage();
	require_once("../time/modules/notificaciones/language/".$default_language.".lang.php");
	$customerid = $_SESSION['customer_id'];
	$accountid = $_SESSION['customer_account_id'];
	global $default_language;
	global $client;

	$obj = new CNotificaciones;
	$obj->Funcion = 'PanelNotificacionesClientes';
	
	if (isset($_REQUEST['Funcion']))
		$obj->Funcion = $_REQUEST['Funcion'];
		
	$obj->asignarDatosContacto($accountid,$customerid);

	$obj->main();

?>
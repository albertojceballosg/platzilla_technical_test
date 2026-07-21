<?php
	include_once 'modules/notificaciones/notificaciones.php';
	require_once('Smarty_setup.php');
	$smarty = new vtigerCRM_Smarty;
	$function = @$_REQUEST['function'];
	$obj = new CNotificaciones;
	
	if ($function == 'COMPOSE_UI') {
		$obj->loadTemplate4Entity($_REQUEST['relcrmid']);
		
		$smarty->assign('CONTENT', $obj->EscribeFormaEnviarNotificacion(true));
		$smarty->display('modules/notificaciones/ComposeUI.tpl');
	} else if ($function == 'SAVE_NOTIFICATION') {
		$response = array('ret' => true, 'msg' => getTranslatedString('Notificación enviada exitosamente!'));
		if ($_REQUEST['Funcion'] == 'RegistrarNotificacion') {
			$obj->GuardarNotificacion('NOTIFICACION_PLANA',
											$_REQUEST['accountid'],
											$_REQUEST['subject'],
											$_REQUEST['TextoMensaje'],
											$_REQUEST['contactid']);
		} else {
			$obj->GuardarNotificacionCliente();
		}
		echo json_encode($response);
		exit(0);
	} else {
		$obj->main();
	}
?>
<?php
	require_once ('Smarty_setup.php');

	if (isset ($_GET ['status'])) {
		switch ($_GET ['status']) {
			case 'E0001';
				$isError = true;
				$message = 'No has suministrado tu dirección de correo electrónico';
				break;
			case 'E0002':
				$isError = true;
				$message = 'La dirección de correo que suministraste no corresponde a un administrador. Te invitamos a que contactes al administrador de tu sistema';
				break;
			case 'E0003':
				$isError = true;
				$message = 'No has suministrado tu nueva contraseña';
				break;
			case 'E0004':
				$isError = true;
				$message = 'Las contraseñas no coinciden';
				break;
			case 'E0005':
				$isError = true;
				$message = 'Se ha presentado un error al actualizar la contraseña. Intenta más tarde';
				break;
			case 'S0001':
				$isError = false;
				$message = 'Te hemos enviado a tu dirección correo electrónico las instrucciones para reestablecer tu contraseña';
				break;
			case 'S0002':
				$isError = false;
				$message = 'Tu contraseña ha sido reestablecida';
				break;
			default:
				$isError = true;
				$message = 'Se ha presentado un error inesperado. Intenta más tarde';
				break;
		}
	} else {
		$isError = false;
		$message = null;
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('IS_ERROR', $isError);
	$smarty->assign ('MESSAGE', $message);
	$smarty->display ('ForgotPasswordMessage.tpl');

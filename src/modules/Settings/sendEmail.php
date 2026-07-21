<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/emailmanager/emailmanager.php');

	global $current_user;

	$category = PlatzillaUtils::purify ($_POST, 'categoria');
	$message  = PlatzillaUtils::purify ($_POST, 'mensaje');
	$platform = $_SESSION ['plat'];

	try {
		if (empty ($category)) {
			throw new Exception ('No has suministrado la categoría');
		}
		if (empty ($message)) {
			throw new Exception ('No has suministrado el mensaje');
		}

		$adb       = AdbManager::getInstance ()->getMasterAdb ();
		$variables = array (
			'CATEGORÍA'              => $category,
			'CORREO_ELECTRÓNICO'     => $current_user->column_fields ['email1'],
			'MENSAJE'                => trim (nl2br ($message)),
			'NOMBRE_DE_LA_INSTANCIA' => !empty ($platform) ? $platform : 'Plataforma madre',
			'NOMBRE_DEL_USUARIO'     => trim ("{$current_user->column_fields ['first_name']} {$current_user->column_fields ['last_name']}"),
		);

		$status = emailmanager::getInstance ($adb, $platform)->addSender (
			'Platzilla',
			'no_reply@platzilla.com'
		)->send (
			'soporte@platzilla.com',
			'es',
			'[SYS] - Solicitud de ayuda',
			$variables
		);
		if ($status != emailmanager::STATUS_SENT) {
			throw new Exception ("Se ha presentado un error al enviar el correo: código {$status}");
		}
		$statusCode    = 200;
		$statusMessage = 'OK';
		$message       = 'La solicitud ha sido enviada';
	} catch (Exception $e) {
		$statusCode    = 400;
		$statusMessage = 'Bad request';
		$message       = $e->getMessage ();
	}
	header ("HTTP/1.1 {$statusCode} {$statusMessage}");
	header ('Content-Type: application/json');
	echo json_encode ($message);
	exit ();

<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb;

	$emailAddress = PlatzillaUtils::purify ($_GET, 'emailaddress');

	try {
		if (empty ($emailAddress)) {
			throw new Exception ('No se ha suministrado la dirección de correo electrónico', 400);
		}

		$provider = WebmailUtils::getMailProvider ($adb, $emailAddress);
		if (empty ($provider)) {
			throw new Exception ('No hemos podido determinar la configuración de tu servidor de correo', 400);
		}

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($provider->jsonSerialize ());
	} catch (Exception $e) {
		$statusCode    = !empty ($e->getCode ()) ? $e->getCode () : 500;
		$statusMessage = !empty ($e->getCode ()) ? 'Bad request' : 'Internal server error';
		header ("HTTP/1.1 {$statusCode} {$statusMessage}");
		header ('Content-Type: application/json');
		if (!isset ($data)) {
			$data = array ();
		}
		$data ['errormessage'] = $e->getMessage ();
		echo json_encode ($data);
	}
	exit ();
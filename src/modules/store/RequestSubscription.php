<?php
	global $dbconfig, $platPrincipal;

	require ('config.inc.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/emailmanager/emailmanager.php');

	try {
		$emailAddress = PlatzillaUtils::purify ($_POST, 'email');
		$firstName    = PlatzillaUtils::purify ($_POST, 'firstname');
		$lastName     = PlatzillaUtils::purify ($_POST, 'lastname');

		if (filter_var ($emailAddress, FILTER_VALIDATE_EMAIL) === false) {
			throw new Exception ('La dirección de correo que suministraste no parece ser válida', 400);
		}

		$dummy       = explode ('@', $emailAddress);
		$emailDomain = $dummy [1];

		$result = getmxrr ($emailDomain, $mxhosts);
		if ((empty ($result)) || (empty ($mxhosts))) {
			throw new Exception ('La dirección de correo que suministraste no parece ser válida', 400);
		}

		$adb    = AdbManager::getInstance ()->getMasterAdb ();
		$result = $adb->pquery ('SELECT * FROM vtiger_instancerequests WHERE email=?', array ($emailAddress));
		if ($adb->num_rows ($result) > 0) {
			$e = new Exception ('Ya tienes una solicitud pendiente', 400);
		}
		DatabaseUtils::closeResult ($result);
		$result = null;
		if (isset ($e)) {
			throw $e;
		}

		$pm = PlatformManager::getInstance ($adb, $dbconfig ['db_serverForNewUsers']);
		if ($pm->userHasInstance ($emailAddress)) {
			throw new Exception ('Ya estás registrado en Platzilla', 400);
		}

		$adb->pquery ('INSERT INTO vtiger_instancerequests (email, firstname, lastname) VALUES (?, ?, ?)', array ($emailAddress, $firstName, $lastName));
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('Te enviaremos un correo electrónico con la información de acceso a tu cuenta');
	} catch (Exception $e) {
		$statusCode    = !empty ($e->getCode ()) ? $e->getCode () : 500;
		$statusMessage = $statusCode == 400 ? 'Bad request' : 'Internal server error';
		header ("HTTP/1.1 {$statusCode} {$statusMessage}");
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();

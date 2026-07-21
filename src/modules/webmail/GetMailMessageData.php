<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/webmail/lib/WebmailUtils.class.php');

	global $adb, $current_user;

	$record = PlatzillaUtils::purify ($_GET, 'record');

	try {
		if (empty ($record)) {
			throw new Exception ('No se ha suministrado el identificador del mensaje', 400);
		}

		$messageData = WebmailUtils::fetchEmailData ($adb, $record, true, true);
		if (empty ($messageData)) {
			throw new Exception ('El mensaje solicitado no se encuentra registrado', 400);
		}

		$statusCode          = 200;
		$statusMessage       = 'OK';
		$data                = $messageData;
	} catch (Exception $e) {
		$statusCode    = 400;
		$statusMessage = 'Bad request';
		$data          = $e->getMessage ();
	}
	header ("HTTP/1.1 {$statusCode} {$statusMessage}");
	header ('Content-Type: application/json');
	echo json_encode ($data);
	exit ();

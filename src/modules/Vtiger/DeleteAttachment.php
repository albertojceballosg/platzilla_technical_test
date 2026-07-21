<?php
	require_once ('include/utils/AttachmentsUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb;

	try {
		$attachmentId = PlatzillaUtils::purify ($_POST, 'attachmentid');
		$entityId     = PlatzillaUtils::purify ($_POST, 'entityid');

		AttachmentsUtils::deleteEntityAttachment ($adb, $entityId, $attachmentId);

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();

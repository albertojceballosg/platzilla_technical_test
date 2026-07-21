<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');

	global $adb, $current_user, $mod_strings;

	try {
		$comments       = PlatzillaUtils::purify ($_POST, 'comments');
		$contactId      = PlatzillaUtils::purify ($_POST, 'contactid');
		$customerId     = PlatzillaUtils::purify ($_POST, 'customerid');
		$emailAddresses = PlatzillaUtils::purify ($_POST, 'emailaddresses');
		$moduleName     = PlatzillaUtils::purify ($_POST, 'modulename');
		$recipientType  = PlatzillaUtils::purify ($_POST, 'recipienttype');
		$recordIds      = PlatzillaUtils::purify ($_POST, 'recordids');
		$ruleId         = PlatzillaUtils::purify ($_POST, 'ruleid');

		if ($recipientType == DataSharingRequest::RECIPIENT_TYPE_LITERAL) {
			$recipients = $emailAddresses;
		} else if ($recipientType == DataSharingRequest::RECIPIENT_TYPE_CUSTOMER) {
			$recipients = array ($customerId);
		} else if ($recipientType == DataSharingRequest::RECIPIENT_TYPE_CONTACT) {
			$recipients = array ($contactId);
		} else {
			$recipients = null;
		}

		$arguments = array (
			'comments'           => $comments,
			'modulename'         => $moduleName,
			'recipients'         => $recipients,
			'recipienttype'      => $recipientType,
			'recordids'          => $recordIds,
			'ruleid'             => $ruleId,
			'sourceinstancecode' => $_SESSION ['platInstancia'],
			'userid'             => $current_user->id,
		);
		DataSharingUtils::sendRequest ($adb, $arguments);
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('Se ha enviado la invitación a compartir contenido');
	} catch (Exception $e) {
		header ('HTTP/1.1 400 Bad request');
		header ('Content-Type: application/json');
		echo json_encode (ucfirst (strtolower ($mod_strings [$e->getMessage ()])));
	}
	exit ();

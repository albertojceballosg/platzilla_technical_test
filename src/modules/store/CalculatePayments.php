<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	$requestAction   = PlatzillaUtils::purify ($_GET, 'requestaction');
	$applicationCode = PlatzillaUtils::purify ($_GET, 'applicationcode');
	$totalUsers      = PlatzillaUtils::purify ($_GET, 'totalusers');

	try {
		if ($requestAction == 'SUBSCRIBE') {
			$payments = StoreUtils::calculateInstancePaymentsWithNewSubscription ($_SESSION ['platInstancia'], $applicationCode, $totalUsers);
		} else {
			$payments = StoreUtils::calculateInstancePaymentsWithoutSubscription ($_SESSION ['platInstancia'], $applicationCode, $totalUsers);
		}
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($payments);
	} catch (Exception $e) {
		$statusCode    = !empty ($statusCode) ? $statusCode : 500;
		$statusMessage = !empty ($statusMessage) ? $statusMessage : 'Internal server error';
		header ("HTTP/1.1 400 'Bad request'");
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();

<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	$statusCode    = null;
	$statusMessage = null;
	try {
		$applicationId = PlatzillaUtils::purify ($_REQUEST, 'applicationid');
		if (empty ($applicationId)) {
			$statusCode    = 400;
			$statusMessage = 'Bad request';
			throw new Exception ('No has suministrado la aplicación a agregar');
		}

		$found = false;
		$cart             = isset ($_SESSION ['cart']) ? $_SESSION ['cart'] : array ('applications' => null, 'users' => 0);
		$cartApplications = $cart ['applications'];
		if (!empty ($cartApplications)) {
			foreach ($cartApplications as $cartApplication) {
				if ($cartApplication ['applicationid'] == $applicationId) {
					$found = true;
					break;
				}
			}
		}
		if (!$found) {
			$_SESSION ['cart']['applications'][] = StoreUtils::getCatalogApplicationById ($applicationId);
		}
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($_SESSION ['cart']);
	} catch (Exception $e) {
		$statusCode    = !empty ($statusCode) ? $statusCode : 500;
		$statusMessage = !empty ($statusMessage) ? $statusMessage : 'Internal server error';
		header ("HTTP/1.1 {$statusCode} {$statusMessage}");
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();

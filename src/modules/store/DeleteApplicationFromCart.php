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
			throw new Exception ('No has suministrado la aplicación a eliminar');
		}

		if ((isset ($_SESSION ['cart'])) && (!empty ($_SESSION ['cart']['applications']))) {
			$cartApplications = $_SESSION ['cart']['applications'];
			foreach ($cartApplications as $index => $cartApplication) {
				if ($cartApplications [$index]['config_applicationsid'] == $applicationId) {
					unset ($cartApplications [ $index ]);
					$_SESSION ['cart']['applications'] = array_values ($cartApplications);
					break;
				}
			}
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

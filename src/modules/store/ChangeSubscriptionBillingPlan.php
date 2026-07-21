<?php
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $current_user;

	$billingPlanId = PlatzillaUtils::purify ($_POST, 'billingplanid');
	$numUsers      = PlatzillaUtils::purify ($_POST, 'numusers');

	try {
		if ((empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($billingPlanId)) {
			throw new Exception ('No has suministrado el plan a suscribirte', 400);
		}

		$masterAdb    = AdbManager::getInstance ()->getMasterAdb ();
		$thePlan      = PlatformBillingPlanManager::getInstance ($masterAdb)->fetchPlan ($billingPlanId);
		$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
		$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
		$psm->changeSubscriptionBillingPlan ($subscription, $billingPlanId, (($thePlan->getTotalUsers() > 1) ? $numUsers : 0));

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('Se ha actualizado tu suscripción');
	} catch (Exception $e) {
		$statusCode = !empty ($e->getCode ()) ? $e->getCode () : 400;
		switch ($statusCode) {
			case 401:
				$statusMessage = 'Access denied';
				break;
			default:
				$statusMessage = 'Bad request';
				break;
		}
		header ("HTTP/1.1 {$statusCode} {$statusMessage}");
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();

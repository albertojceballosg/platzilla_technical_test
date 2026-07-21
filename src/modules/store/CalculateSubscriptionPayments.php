<?php
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $current_user;

	$billingPlanId = PlatzillaUtils::purify ($_GET, 'billingplanid');
	$numUsers      = PlatzillaUtils::purify ($_GET, 'numusers');

	try {
		if ((empty ($_SESSION ['platInstancia'])) || (!is_admin ($current_user))) {
			throw new Exception ('Acceso denegado', 401);
		} else if (empty ($billingPlanId)) {
			throw new Exception ('No has suministrado el plan a suscribirte', 400);
		}

		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$thePlan   = PlatformBillingPlanManager::getInstance ($masterAdb)->fetchPlan ($billingPlanId);
		if ($thePlan->getTotalUsers() > 1) {
			$totalUsers = $numUsers;
		} else if ($thePlan->getTotalUsers() == 1) {
			$numUsers   = 0;
			$totalUsers = 1;
		} else {
			$numUsers   = 0;
			$totalUsers = 'ilimitados';
		}
		$psm                = PlatformSubscriptionManager::getInstance ($masterAdb);
		$subscription       = $psm->fetchSubscription ($_SESSION ['platInstancia']);
		$newBillingPlan     = $psm->checkIfBillingPlanIsApplicable ($subscription, $billingPlanId, $numUsers);

		$transactionPayment = $psm->calculateChangePlanLastMonthDaysTransactionPayment ($subscription, $newBillingPlan);
		if (empty ($transactionPayment)) {
			$transactionPayment = $psm->calculateChangePlanRegularTransactionPayment ($subscription, $newBillingPlan);
		}
		if (!empty ($transactionPayment)) {
			$proratedPayment = array (
				'amountwithouttax' => $transactionPayment->getSubTotal (),
				'taxpercentage'    => $transactionPayment->getTaxPercentage (),
				'taxamount'        => $transactionPayment->getTaxAmount (),
				'amountwithtax'    => $transactionPayment->getTotalAmount (),
				'from'             => $transactionPayment->getServiceStartDate ()->format ('Y-m-d'),
				'to'               => $transactionPayment->getServiceEndDate ()->format ('Y-m-d'),
			);
		} else {
			$proratedPayment = null;
		}

		if (!$newBillingPlan->getProduct ()->getPricebook ()->isDefault ()) {
			$nextMonthPlanProduct  = ProductManager::getInstance ($masterAdb)->fetchProductWithApplicableTax ($newBillingPlan->getProduct ()->getId (), $_SESSION ['platInstancia'], $numUsers);
			$nextMonthSubscription = array (
				'amountwithouttax' => $nextMonthPlanProduct->getPriceBeforeTax (),
				'taxpercentage'    => $nextMonthPlanProduct->getTax ()->getPercentage (),
				'taxamount'        => $nextMonthPlanProduct->getTaxAmount (),
				'amountwithtax'    => $nextMonthPlanProduct->getPriceAfterTax (),
			);
		} else {
			$nextMonthSubscription = null;
		}

		$subscriptionPayment = $psm->calculateChangePlanSubscriptionPayment ($subscription, $newBillingPlan);
		$paymentInformation  = array (
			'proratedpayment'       => $proratedPayment,
			'subscription'          => array (
				'amountwithouttax' => $subscriptionPayment->getSubTotal (),
				'taxpercentage'    => $subscriptionPayment->getTaxPercentage (),
				'taxamount'        => $subscriptionPayment->getTaxAmount (),
				'amountwithtax'    => $subscriptionPayment->getTotalAmount (),
				'from'             => $subscriptionPayment->getServiceStartDate ()->format ('Y-m-d'),
				'to'               => $subscriptionPayment->getServiceEndDate ()->format ('Y-m-d'),
				'totalUsers'       => $totalUsers,
			),
			'nextmonthsubscription' => $nextMonthSubscription,
		);

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($paymentInformation);
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

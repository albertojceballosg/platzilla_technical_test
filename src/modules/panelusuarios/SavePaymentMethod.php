<?php
	require_once ('include/platzilla/Managers/PaymentGatewayManager.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $platPrincipal;

	$addressId       = PlatzillaUtils::purify ($_POST, 'addressid');
	$city            = PlatzillaUtils::purify ($_POST, 'city');
	$company         = PlatzillaUtils::purify ($_POST, 'company');
	$countryCode     = PlatzillaUtils::purify ($_POST, 'countrycode');
	$extendedAddress = PlatzillaUtils::purify ($_POST, 'extendedaddress');
	$firstName       = PlatzillaUtils::purify ($_POST, 'firstname');
	$isDefault       = PlatzillaUtils::purify ($_POST, 'isdefault', false);
	$lastName        = PlatzillaUtils::purify ($_POST, 'lastname');
	$nonce           = PlatzillaUtils::purify ($_POST, 'nonce');
	$state           = PlatzillaUtils::purify ($_POST, 'state');
	$streetAddress   = PlatzillaUtils::purify ($_POST, 'streetaddress');
	$zipCode         = PlatzillaUtils::purify ($_POST, 'zipcode');

	try {
		$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
		$instance  = PlatformManager::getInstance ($masterAdb)->fetchInstanceAsCrmEntity ($_SESSION ['platInstancia']);

		BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runEventTriggeredTasks (
			'UPDATE PAYMENT METHODS',
			BackgroundTaskInterface::EVENT_INSTANT_BEFORE,
			$instance
		);

		$pgm      = PaymentGatewayManager::getInstance ();
		$customer = $pgm->fetchInstanceCustomer ($_SESSION ['platInstancia']);
		if (empty ($customer)) {
			$customer = $pgm->registerInstanceCustomer ($_SESSION ['platInstancia']);
		}

		$isDefault     = (empty ($customer->paymentMethods)) || ($isDefault) ? true : false;
		$paymentMethod = $pgm->registerInstancePaymentMethod (
			$_SESSION ['platInstancia'],
			array (
				'addressid'       => $addressId,
				'city'            => $city,
				'company'         => $company,
				'countrycode'     => $countryCode,
				'extendedaddress' => $extendedAddress,
				'firstname'       => $firstName,
				'isdefault'       => $isDefault,
				'lastname'        => $lastName,
				'nonce'           => $nonce,
				'state'           => $state,
				'streetaddress'   => $streetAddress,
				'zipcode'         => $zipCode,
			)
		);

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se han actualizado los métodos de pago',
		);

		BackgroundTasksRunner::getInstance ($masterAdb, $platPrincipal)->runEventTriggeredTasks (
			'UPDATE PAYMENT METHODS',
			BackgroundTaskInterface::EVENT_INSTANT_AFTER,
			$instance
		);

		header ('Location: index.php?module=Home&action=ViewSubscriptionDetails&tab=payment-methods');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ('Location: index.php?module=panelusuarios&action=AddPaymentMethod');
	}
	exit ();

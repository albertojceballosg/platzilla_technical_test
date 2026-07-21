<?php
	require_once ('include/platzilla/Managers/PaymentGatewayManager.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Managers/TaxManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Courses/lib/CoursesHelper.php');

	global $adb, $current_user, $site_URL;
	setBugSnag ($site_URL);

	$userName        = PlatzillaUtils::purify($_POST, 'user_name');
	$addressId       = PlatzillaUtils::purify ($_POST, 'addressid');
	$city            = PlatzillaUtils::purify ($_POST, 'city');
	$company         = PlatzillaUtils::purify ($_POST, 'company');
	$countryCode     = PlatzillaUtils::purify ($_POST, 'countrycode');
	$extendedAddress = PlatzillaUtils::purify ($_POST, 'extendedaddress');
	$firstName       = PlatzillaUtils::purify ($_POST, 'firstname');
	$isDefault       = PlatzillaUtils::purify ($_POST, 'isdefault', true);
	$lastName        = PlatzillaUtils::purify ($_POST, 'lastname');
	$nonce           = PlatzillaUtils::purify ($_POST, 'nonce');
	$state           = PlatzillaUtils::purify ($_POST, 'state');
	$streetAddress   = PlatzillaUtils::purify ($_POST, 'streetaddress');
	$zipCode         = PlatzillaUtils::purify ($_POST, 'zipcode');
	$hasCredidCart   = PlatzillaUtils::purify ($_POST, 'hasCredidcart');
	$courseId        = PlatzillaUtils::purify ($_POST, 'record');

	try {
    // Validación del usuario para el cual se hace el pago
	$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
    $userRecord = $masterAdb->pquery(
        "SELECT username FROM vtiger_instanceusers WHERE username = ?",
        array($userName)
    );
    if ($masterAdb->num_rows($userRecord) == 0) {
        $_SESSION['flashmessage'] = array(
            'iserror' => true,
            'message' => 'El usuario indicado no existe.',
        );
        header('Location: index.php?module=Courses&action=AddPaymentCourse&record=' . $courseId);
        exit();
    }
    $userIdForPayment = $masterAdb->query_result($userRecord, 0, 'id');
		$actualDieOnError = $adb->dieOnError;
		$adb->setDieOnError (false);
		$tax = TaxManager::getInstance ($adb)->fetchDefaultTax ();
		$adb->setDieOnError ($actualDieOnError);

		$masterAdb     = AdbManager::getInstance ()->getMasterAdb ();
		$course        = CoursesHelper::fetchCourseById ($masterAdb, $courseId);
		$taxPercentage = (empty($tax)) ? 25 : $tax->getPercentage ();
		$product       = $course->getTargetAudience() . ': '.$course->getName ();
		$instance      = PlatformManager::getInstance ($masterAdb)->fetchInstanceAsCrmEntity ($_SESSION ['platInstancia']);
		$pgm           = PaymentGatewayManager::getInstance ();
		$customer      = $pgm->fetchInstanceCustomer ($_SESSION ['platInstancia']);
		if (empty ($customer)) {
			$customer = $pgm->registerInstanceCustomer ($_SESSION ['platInstancia']);
		}

		if (!$hasCredidCart) {
			$defaultPaymentMethod = $pgm->registerInstancePaymentMethod (
				$_SESSION ['platInstancia'],
				array(
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
		} else {
			$defaultPaymentMethod = $pgm->hasDefaultPaymentMethod ($customer);
		}
		if (!empty($defaultPaymentMethod)) {
			$payments[] = Payment::getInstance ()
				->setDueDate (null)
				->setId (null)
				->setInstanceCode ($_SESSION ['platInstancia'])
				->setLastErrorMessage ('')
				->setProductName($product)
				->setServiceEndDate (null)
				->setServiceStartDate (date ('Y-m-d', strtotime('now')))
				->setStatus (Payment::STATUS_PENDING)
				->setSubTotal ((100 * $course->getPrice ()) / (100 + $taxPercentage))
				->setTaxPercentage ($taxPercentage)
				->setType (Payment::TYPE_TRANSACTION);
			$myPayment = $pgm->chargeInstanceCustomerPayments ($_SESSION ['platInstancia'], $payments);
			if (!empty ($myPayment)) {
				if (!empty ($myPayment [0]->getId ())) {
					CoursesHelper::savePaidCourse ($masterAdb, $courseId, $_SESSION ['platInstancia'], $myPayment [0]->getId (), $current_user->id, $userName, $adb);
					$_SESSION ['flashmessage'] = array (
						'iserror' => false,
						'message' => 'Se ha realizado el pago con éxito!',
					);
				}
			} else {
				$_SESSION ['flashmessage'] = array (
					'iserror' => true,
					'message' => 'Error en el método de pago',
				);
			}
		} else {
			$_SESSION ['flashmessage'] = array (
				'iserror' => true,
				'message' => 'Error en el método de pago',
			);
		}
		
		header ('Location: index.php?module=Home&action=index&tab=TRAINING');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ('Location: index.php?module=Courses&action=AddPaymentCourse&record=' . $courseId);
	}
	exit ();

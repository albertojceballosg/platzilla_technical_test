<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PaymentGatewayManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	$pm        = PaymentGatewayManager::getInstance ();
	$countries = PlatzillaUtils::getCountries ();
	$token     = $pm->generateClientToken ();
	$customer  = $pm->fetchInstanceCustomer ($_SESSION ['platInstancia']);
	if (!empty ($customer)) {
		$customerAddresses = $customer->addresses;
		if (!empty ($customerAddresses)) {
			foreach ($customerAddresses as $customerAddress) {
				$addresses [ $customerAddress->id ] = array (
					'city'            => $customerAddress->locality,
					'company'         => $customerAddress->company,
					// @codingStandardsIgnoreStart
					'countrycode'     => $customerAddress->countryCodeAlpha2,
					// @codingStandardsIgnoreEnd
					'countryname'     => $customerAddress->countryName,
					'extendedaddress' => $customerAddress->extendedAddress,
					'firstname'       => $customerAddress->firstName,
					'lastname'        => $customerAddress->lastName,
					'state'           => $customerAddress->region,
					'streetaddress'   => $customerAddress->streetAddress,
					'zipcode'         => $customerAddress->postalCode,
				);
			}
		} else {
			$addresses = null;
		}
	} else {
		$addresses = null;
	}

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('ADDRESSES', $addresses);
	$smarty->assign ('COUNTRIES', $countries);
	$smarty->assign ('TOKEN', $token);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('modules/panelusuarios/AddPaymentMethod.tpl');

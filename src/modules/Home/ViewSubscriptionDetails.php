<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/ApplicationManager.php');
	require_once ('include/platzilla/Managers/InvoiceManager.php');
	require_once ('include/platzilla/Managers/PlatformBillingPlanManager.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('modules/how_use/lib/HowToUseHelper.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $adb, $current_user, $mod_strings, $theme, $platPrincipal, $site_URL;

	setBugSnag ($site_URL);

	$isInstance = !empty ($_SESSION ['platInstancia']) ? true : false;

	try {
		$phum          = ProfilesHowToUseManager::getInstance($adb);
		$profilesToUse = $phum->fetchProfilesHowToUse (true);

		$smarty = new vtigerCRM_Smarty ();
		if ((!is_admin ($current_user)) || (!$isInstance)) {
			// En caso de que el Store no se ejecute desde una instancia, sino desde la Plataforma Madre - AGREGADO AV 20170612
			$smarty->display ('AccessDenied.tpl');
		} else {
			$selectedTab            = PlatzillaUtils::purify ($_GET, 'tab');
			$platform               = !empty ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : $platPrincipal;
			$masterAdb              = AdbManager::getInstance ()->getMasterAdb ();
			$subscription           = PlatformSubscriptionManager::getInstance ($masterAdb)->fetchSubscription ($_SESSION ['platInstancia'], true);
			$categories             = StoreUtils::fetchApplicationCategories ($adb);
			$applicationsByCategory = StoreUtils::fetchApplicationsByCategory ();
			$organization           = HomeUtils::getOrganizationDetails ($adb, $platform);
			$organizationCurrency   = HomeUtils::getOrganizationCurrency ($adb);
			$invoices               = InvoiceManager::getInstance ()->fetchInvoices ($_SESSION ['platInstancia']);
			$availableBillingPlans  = PlatformBillingPlanManager::getInstance ($masterAdb)->fetchPlans ($_SESSION ['platInstancia']);
			$availableApplications  = array ();
			foreach ($applicationsByCategory as $categoryId => $applications) {
				$categoryName = isset ($categories [ $categoryId ]) ? $categories [ $categoryId ]['name'] : '';
				foreach ($applications as $applicationCode => $application) {
					$availableApplications [ $categoryName ][] = $application;
				}
			}
			$subscribedBillingPlan = $subscription->getBillingPlan ();
			if ((empty ($subscribedBillingPlan->getProduct ())) || ($subscribedBillingPlan->getProduct ()->getBasePrice () == 0)) {
				$fifteenDaysInterval = new DateInterval ('P14D');
				$registrationDate    = date_create ($subscription->getRegistrationDate ()->format ('Y-m-d'));
				$trialPeriodEndDate  = $registrationDate->setTime (0, 0, 0)->add ($fifteenDaysInterval);
			} else {
				$trialPeriodEndDate = null;
			}
			$applicationSubscriptions   = $subscription->getApplicationSubscriptions ();
			$subscribedApplicationCodes = array ();
			$subscribedApplicationNames = array ();
			$installedApplicationCodes  = array ();
			foreach ($applicationSubscriptions as $applicationSubscription) {
				if ($applicationSubscription->getStatus () == ApplicationSubscription::STATUS_SUBSCRIBED) {
					$subscribedApplicationCodes [] = $applicationSubscription->getApplicationCode ();
					$subscribedApplicationNames [] = $applicationSubscription->getApplicationName ();
				} else if ($applicationSubscription->getStatus () == ApplicationSubscription::STATUS_ACTIVE) {
					$installedApplicationCodes [] = $applicationSubscription->getApplicationCode ();
				}
			}
			$canAddApplications = ($subscribedBillingPlan->getTotalApplications () == -1) || ($subscribedBillingPlan->getTotalApplications () > count ($subscribedApplicationCodes));
			$smarty->assign ('APPSIMAGE_PATH', 'storage/appsimages');
			$smarty->assign ('AVAILABLE_BILLING_PLANS', $availableBillingPlans);
			$smarty->assign ('AVAILABLE_APPLICATIONS', $availableApplications);
			$smarty->assign ('CAN_ADD_APPLICATIONS', $canAddApplications);
			$smarty->assign ('CATEGORIES', $categories);
			$smarty->assign ('COMPANY_PHASES', $phum->fetchCompanyPhases());
			$smarty->assign ('COMPANY_SECTOR', $phum->fetchCompanySector());
			$smarty->assign ('COMPANY_TYPES', $phum->fetchCompanyTypes());
			$smarty->assign ('INSTANCE_PROFILE', ProfilesHowToUseManager::getInstance ($adb)->fetchProfilesHowToUseByCode ());
			$smarty->assign ('INVOICES', $invoices);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('ORGANIZATION', $organization);
			$smarty->assign ('ORGANIZATION_CURRENCY', $organizationCurrency);
			$smarty->assign ('PROFILE_USE', $profilesToUse);
			$smarty->assign ('SUBSCRIBED_APPLICATION_CODES', $subscribedApplicationCodes);
			$smarty->assign ('SUBSCRIBED_APPLICATION_NAMES', $subscribedApplicationNames);
			$smarty->assign ('SUBSCRIBED_BILLING_PLAN', $subscribedBillingPlan);
			$smarty->assign ('SUBSCRIPTION', $subscription);
			$smarty->assign ('SELECTED_TAB', $selectedTab);
			$smarty->assign ('SUBSCRIBED_BILLING_PLAN', $subscribedBillingPlan);
			$smarty->assign ('SUBSCRIPTION', $subscription);
			$smarty->assign ('THEME', $theme);
			$smarty->assign ('TRIAL_PERIOD_END_DATE', $trialPeriodEndDate);
			if ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE) {
				if ($subscribedBillingPlan->getProduct ()->getBasePrice () == 0) {
					$smarty->assign ('MESSAGE', 'Tus días de prueba han finalizado. Te invitamos a suscribirte al plan que más te convenga');
				} else {
					$smarty->assign ('MESSAGE', 'Tu suscripción se encuentra inactiva');
				}
				$smarty->assign ('IS_ERROR', true);
			}
		}
	} catch (Exception $e) {
		$smarty->assign ('MESSAGE', $e->getMessage ());
		$smarty->assign ('IS_ERROR', true);
	}
	$smarty->display ('Home/SubscriptionDetails.tpl');

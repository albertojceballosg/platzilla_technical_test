<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/InvoiceManager.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Home/lib/HomeUtils.class.php');
	require_once ('modules/Home/lib/WorkingDayUtils.class.php');
	require_once ('modules/Courses/lib/CourseManager.php');
	require_once ('modules/materials/lib/FolderUtils.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $adb, $app_strings, $current_user, $mod_strings, $platPrincipal, $site_URL;
	/** BugSnag temporalmente suspendido  */
	//setBugSnag ($site_URL);

	$selectedTab = PlatzillaUtils::purify ($_REQUEST, 'tab');
	$platform    = !empty ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : $platPrincipal;

	$isAdmin               = is_admin ($current_user);
	$user                  = UserManager::getInstance ($adb, $platform)->fetchUserById ($current_user->id);
	$availableBillingPlans = null;
	$invoices              = null;
	$isPattern             = false;
	$organization          = null;
	$organizationCurrency  = null;
	$subscription          = null;
	if ($isAdmin) {
		$organization         = HomeUtils::getOrganizationDetails ($adb, $platform);
		$organizationCurrency = HomeUtils::getOrganizationCurrency ($adb);
		if (!empty ($_SESSION ['platInstancia'])) {
			$masterAdb             = AdbManager::getInstance ()->getMasterAdb ();
			$instance              = PlatformManager::getInstance ($masterAdb)->fetchInstance ($_SESSION ['platInstancia'], true);
			$isPattern             = $instance->isPattern ();
			$subscription          = PlatformSubscriptionManager::getInstance ($masterAdb)->fetchSubscription ($_SESSION ['platInstancia'], true);
			$availableBillingPlans = PlatformBillingPlanManager::getInstance ($masterAdb)->fetchPlans ($_SESSION ['platInstancia']);
			$invoices              = InvoiceManager::getInstance ()->fetchInvoices ($_SESSION ['platInstancia']);
		}
		$categories             = StoreUtils::fetchApplicationCategories ($adb);
		$applicationsByCategory = StoreUtils::fetchApplicationsByCategory ();
		$availableApplications  = array ();
		foreach ($applicationsByCategory as $categoryId => $applications) {
			$categoryName = isset ($categories [ $categoryId ]) ? $categories [ $categoryId ]['name'] : '';
			foreach ($applications as $applicationCode => $application) {
				$availableApplications [ $categoryName ][] = $application;
			}
		}
		$result = $adb->query ('SELECT * FROM vtiger_crmentity WHERE demo=1');
		if ($adb->num_rows ($result) > 0) {
			$hasDemoData = true;
		} else {
			$hasDemoData = false;
		}
	}
	
	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('AVAILABLE_APPLICATIONS', $availableApplications);
	$smarty->assign ('AVAILABLE_BILLING_PLANS', $availableBillingPlans);
	$smarty->assign ('AVAILABLE_WORKING_DAYS', WorkingDayManager::getInstance ($adb)->fetchWorkingDay ());
	$smarty->assign ('CATEGORIES', $categories);
	$smarty->assign ('CODE', $platform);
	$smarty->assign ('COURSE_SEEN', CourseManager::getInstance($adb)->fetchCoursesStatistics ($current_user->id));
	$smarty->assign ('CUSTOMER', $customer);
	$smarty->assign ('DAYS_WEEK', WorkingDayUtils::getDaysOfWeek ($adb));
	$smarty->assign ('DONLOADED_FILES', FolderUtils::getInstance ($platPrincipal)->fetchDownloadedFile($adb, $current_user->id));
	$smarty->assign ('HAS_DEMO_DATA', $hasDemoData);
	$smarty->assign ('INVOICES', $invoices);
	$smarty->assign ('IS_ADMIN', $isAdmin);
	$smarty->assign ('IS_PATTERN', $isPattern);
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('ORGANIZATION', $organization);
	$smarty->assign ('ORGANIZATION_CURRENCY', $organizationCurrency);
	$smarty->assign ('SELECTED_TAB', $selectedTab);
	$smarty->assign ('SUBSCRIPTION', $subscription);
	$smarty->assign ('USER', $user);
	$smarty->assign ('USER_WORKING_DAY', WorkingDayUtils::getValidWorkingDay ($adb, $current_user->id));
	$smarty->assign ('WORKING_DAYS_HISTORY', WorkingDayUtils::fetchWorkingDaysFromUser ($adb, $current_user->id));
	$smarty->assign ('WORKING_DAY_STATUS', WorkingDayInterface::WORKING_DAY_STATUS);
	if (isset ($_SESSION ['flashmessage'])) {
		$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
		$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
		unset ($_SESSION ['flashmessage']);
	}
	$smarty->display ('Home/CustomerView.tpl');

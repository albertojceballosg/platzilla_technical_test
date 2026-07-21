<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	global $current_user, $theme;

	$isInstance = !empty ($_SESSION ['platInstancia']) ? true : false;

	$smarty = new vtigerCRM_Smarty ();
	if ((!is_admin ($current_user)) || (!$isInstance)) {
		// En caso de que el Store no se ejecute desde una instancia, sino desde la Plataforma Madre - AGREGADO AV 20170612
		$smarty->display ('modules/store/storeNoActivo.tpl');
	} else {
		$applicationsByCategory = StoreUtils::getInstanceApplicationsByCategory ($_SESSION ['platInstancia']);
		$categories             = array_keys ($applicationsByCategory);

		$availableApplications            = array ();
		$subscribedApplications           = array ();
		$totalSubscribedApplicationsPrice = 0;
		$totalSubscribedApplications      = 0;
		foreach ($applicationsByCategory as $categoryName => $applications) {
			foreach ($applications as $application) {
				if ((empty ($application ['status'])) || ($application ['status'] == 'cancelada')) {
					$availableApplications [ $categoryName ][] = $application;
				} else {
					$subscribedApplications [ $categoryName ][] = $application;
					$totalSubscribedApplicationsPrice += $application ['finalprice'];
					$totalSubscribedApplications++;
				}
			}
		}

		$instanceData = StoreUtils::getInstanceDetails ($_SESSION ['platInstancia']);
		$pricePerUser = StoreUtils::getPriceForUser ();
		$totalUsers = $instanceData ['numusuarios'];
		$totalPricePerUsers = max (($totalUsers - 1), 0) * $pricePerUser;

		$smarty->assign ('APPSIMAGE_PATH', 'storage/appsimages');
		$smarty->assign ('AVAILABLE_APPLICATIONS', $availableApplications);
		$smarty->assign ('CATEGORIES', $categories);
		$smarty->assign ('INSTANCE_DATA', $instanceData);
		$smarty->assign ('PRICE_PER_USER', $pricePerUser);
		$smarty->assign ('SUBSCRIBED_APPLICATIONS', $subscribedApplications);
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('TOTAL_PRICE_SUBSCRIBED_APPLICATIONS', $totalSubscribedApplicationsPrice);
		$smarty->assign ('TOTAL_PRICE_PER_USERS', $totalPricePerUsers);
		$smarty->assign ('TOTAL_USERS', $totalUsers);
		$smarty->display ('modules/store/store.tpl');
	}

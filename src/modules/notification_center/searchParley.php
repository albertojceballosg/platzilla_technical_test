<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule, $current_user;

	$objectDate = new DateTime();
	$searchFrom = PlatzillaUtils::purify ($_REQUEST, 'searchFrom');

	$to = PlatzillaUtils::purify ($_REQUEST, 'dateTo', $objectDate->format ('Y-m-d'));
	if (!empty ($to)) {
		$dateTo = new DateTime($to);
		$dateTo->modify ('+1 day');
		$to = $dateTo->format ('Y-m-d');
	}
	$searchData = array (
		'minTime'     => PlatzillaUtils::purify ($_REQUEST, 'viewPeriod'),
		'module'      => PlatzillaUtils::purify ($_REQUEST, 'viewModule'),
		'searchText'  => PlatzillaUtils::purify ($_REQUEST, 'searchText'),
		'searchField' => PlatzillaUtils::purify ($_REQUEST, 'searchField'),
		'recordId'    => PlatzillaUtils::purify ($_REQUEST, 'recordId'),
		'dateFrom'    => PlatzillaUtils::purify ($_REQUEST, 'dateFrom', $objectDate->format ('Y-m-d')),
		'dateTo'      => $to,
	);

	$parley_array = NotificationHelper::searchParleyByWhere ($adb, $current_user, $searchData);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('CURRENT_USER_NAME', $current_user->column_fields['first_name'] . ' ' . $current_user->column_fields['last_name']);
	$smarty->assign ('CHATS', $parley_array);
	if ($searchFrom == 'modalView') {
		echo $smarty->fetch ('modules/notification_center/listParley.tpl');
	} else {
		echo $smarty->fetch ('modules/notification_center/chatParley.tpl');
	}
	exit();

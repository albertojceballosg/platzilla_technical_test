<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule,  $current_user;

	$objectDate = new DateTime();
	$dateTo = $objectDate->format ('Y-m-d');

	$searchFrom  = PlatzillaUtils::purify ($_REQUEST, 'emailsPeriod');
	$emailFrom   = PlatzillaUtils::purify ($_REQUEST, 'emailsDatefrom');
	$emailTo     = PlatzillaUtils::purify ($_REQUEST, 'emailsDateTo');

	if(! empty($emailTo)) {
		$dateTo = $emailTo;
	}

	if(! empty($emailFrom)) {
		$searchFrom = $emailFrom;
	}

	$seachEmails = array('emailFrom' => $searchFrom, 'dateTo' => $dateTo);

	$emailsNotArchived = NotificationHelper::fetchNonRelatedEmails ($adb, $current_user, $seachEmails);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('EMAILS',$emailsNotArchived);
	$smarty->display ('modules/notification_center/listEmails.tpl');
	exit();

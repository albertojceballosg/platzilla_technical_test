<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/notification_center/lib/NotificationHelper.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $current_user;

	$objectDate = new DateTime();
	$dateTo = $objectDate->format ('Y-m-d');

	$emailModule = PlatzillaUtils::purify ($_REQUEST, 'viewArchveModule');
	$searchFrom  = PlatzillaUtils::purify ($_REQUEST, 'archivedEmailsPeriod');
	$emailFrom   = PlatzillaUtils::purify ($_REQUEST, 'archivedEmailsDatefrom');
	$emailTo     = PlatzillaUtils::purify ($_REQUEST, 'archivedEmailsDateTo');

	if(! empty($emailTo)) {
		$dateTo = $emailTo;
	}

	if(! empty($emailFrom)) {
		$searchFrom = $emailFrom;
	}

	$seachArchivedEmails = array('emailFrom' => $searchFrom, 'dateTo' => $dateTo, 'emailModule' => $emailModule);

	$emailsArchived = NotificationHelper::getEmailsRelatedEntities($adb, $current_user, $seachArchivedEmails);

	$smarty = new vtigerCRM_Smarty ();
	$smarty->assign ('EMAILS_ARCHIVED',$emailsArchived);
	echo $smarty->display ('modules/notification_center/listArchivedEmails.tpl');
	exit();

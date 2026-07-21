<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/part_work/lib/PartOfWorkUtils.class.php');
    
    global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
    
    setBugSnag($site_URL);
	
    $isInstance = ! empty ($_SESSION ['platInstancia']);
	try {
		$dueDate      = PlatzillaUtils::purify ($_POST, 'duedate', null);
		$function     = PlatzillaUtils::purify($_REQUEST, 'function');
		$moduleName   = PlatzillaUtils::purify($_REQUEST, 'flmodule');
		$page         = PlatzillaUtils::purify ($_POST, 'page');
		$periodTask   = PlatzillaUtils::purify ($_POST, 'periodtask');
		$startDate    = PlatzillaUtils::purify ($_POST, 'datestart', null);
		$totalRecords = PlatzillaUtils::purify ($_POST, 'total_records');
		$users        = PlatzillaUtils::purify ($_POST, 'invitees_id');
		$workId	      = PlatzillaUtils::purify ($_POST, 'hometabid');
		
		if ($periodTask == 'custom') {
			$periodDates ['startdate'] = $startDate;
			$periodDates ['enddate']   = $dueDate;
		} else {
			$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodTask);
		}
		$recordPerPage = ManagementModeHelper::RECORDS_PER_PAGE;
		if ((empty ($page)) || ($page <= 0)) {
			$startRecord = 0;
		} else {
			$startRecord = (($page - 1) * $recordPerPage);
		}
		
		$partOfWorkObject = PartOfWorkUtils::getInstance ($adb);
		$partOfWorks      = $partOfWorkObject->fetchPartOfWork ($users, $periodDates, $startRecord);
		$listRows         = (!empty($partOfWorks)) ? $partOfWorks['task']  : null;
		unset ($partOfWorks['task']);
		
		$smarty = new vtigerCRM_Smarty ();
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$reportData = "{$periodDates ['startdate']}@{$periodDates ['enddate']}@{$users}@platzilla";
		
		$smarty->assign ('FIELDS_HEADER', PartOfWorkUtils::PART_WORK_TABLE_HEADER);
		$smarty->assign ('FIELDS_ROWS', PartOfWorkUtils::PART_WORK_TABLE_ROW);
		$smarty->assign ('TABLE_ROWS', (!empty($partOfWorks)) ? $partOfWorks : null);
		$smarty->assign ('LIST_ROWS', $listRows);
		$smarty->assign ('PERIOD_DATES', $periodDates);
		$smarty->assign ('REPORT_DATA', base64_encode ($reportData));
		$smarty->assign ('URL_AVATARS', "{$_SESSION ['plat']}/user_images");
		
		$smarty->display ('Home/ActionTabs/PartOfWork.tpl');
	} catch (Exception $e) {
		$code   = $e->getCode ();
		$smarty = new vtigerCRM_Smarty ();
		if ($code === 400) {
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta');
			$smarty->display ('instanciaUnverified.tpl');
		} else if ($code === 403) {
			$smarty->assign ('LABEL', 'Tu suscripción');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php?module=Home&action=index');
			$smarty->display ('Message.tpl');
		} else {
			$smarty->assign ('LABEL', 'Se ha presentado un error fatal');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php?module=Home&action=index');
			$smarty->display ('Message.tpl');
		}
	}
	

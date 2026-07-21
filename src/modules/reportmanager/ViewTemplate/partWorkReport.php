<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/part_work/lib/PartOfWorkUtils.class.php');
    
    global $adb;
	try {
		$reportData = base64_decode (PlatzillaUtils::purify ($_GET, 'report_data'));
		$data = explode ('@', $reportData);
		
		$periodDates ['startdate'] = $data [0];
		$periodDates ['enddate']   = $data [1];
		$users                     = $data [2];
		$startRecord               = 0;
		
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
		$tamplateName = date('YmdHis').'parte_trabajo.pdf';
		$donwLoadField = $tamplateName;
		$smarty->assign ('FIELDS_HEADER', PartOfWorkUtils::PART_WORK_PDF_HEADER);
		$smarty->assign ('FIELDS_ROWS', PartOfWorkUtils::PART_WORK_PDF_ROW);
		$smarty->assign ('TABLE_ROWS', (!empty($partOfWorks)) ? $partOfWorks : null);
		$smarty->assign ('LIST_ROWS', $listRows);
		$smarty->assign ('PERIOD_DATES', $periodDates);
		$smarty->assign ('URL_AVATARS', "{$_SESSION ['plat']}/user_images");
		$html = $smarty->fetch ('Home/ActionTabs/PartOfWorkPDF.tpl');
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
	

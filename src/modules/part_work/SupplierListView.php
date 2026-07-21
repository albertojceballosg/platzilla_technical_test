<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/part_work/lib/SupplierPartOfWorkUtils.class.php');
    
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
		$supplierId   = PlatzillaUtils::purify ($_POST, 'supplierid');
		$workId	      = PlatzillaUtils::purify ($_POST, 'hometabid');
		
		if ($periodTask == 'custom') {
			// Convertir fechas - soporta formatos dd/mm/yyyy y Y-m-d
			$periodDates ['startdate'] = date('Y-m-d');
			$periodDates ['enddate'] = date('Y-m-d');
			
			if (!empty($startDate)) {
				$dateObj = DateTime::createFromFormat('d/m/Y', $startDate);
				if ($dateObj === false) {
					$dateObj = DateTime::createFromFormat('Y-m-d', $startDate);
				}
				if ($dateObj !== false) {
					$periodDates ['startdate'] = $dateObj->format('Y-m-d');
				}
			}
			
			if (!empty($dueDate)) {
				$dateObj = DateTime::createFromFormat('d/m/Y', $dueDate);
				if ($dateObj === false) {
					$dateObj = DateTime::createFromFormat('Y-m-d', $dueDate);
				}
				if ($dateObj !== false) {
					$periodDates ['enddate'] = $dateObj->format('Y-m-d');
				}
			}
		} else {
			$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodTask);
		}
		$recordPerPage = 1500;
		if ((empty ($page)) || ($page <= 0)) {
			$startRecord = 0;
		} else {
			$startRecord = (($page - 1) * $recordPerPage);
		}
		
		$partOfWorkObject = SupplierPartOfWorkUtils::getInstance ($adb);
		$partOfWorks      = $partOfWorkObject->fetchPartOfWorkBySupplier ($supplierId, $periodDates, $startRecord);
		$supplierInfo     = $partOfWorkObject->getSupplierInfo ($supplierId);
		$listRows         = (!empty($partOfWorks)) ? $partOfWorks['task']  : null;
		unset ($partOfWorks['task']);
		
		$smarty = new vtigerCRM_Smarty ();
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$reportData = "{$periodDates ['startdate']}@{$periodDates ['enddate']}@{$supplierId}@supplier";
		
		$smarty->assign ('FIELDS_HEADER', SupplierPartOfWorkUtils::SUPPLIER_PART_WORK_TABLE_HEADER);
		$smarty->assign ('FIELDS_ROWS', SupplierPartOfWorkUtils::SUPPLIER_PART_WORK_TABLE_ROW);
		$smarty->assign ('TABLE_ROWS', (!empty($partOfWorks)) ? $partOfWorks : null);
		$smarty->assign ('LIST_ROWS', $listRows);
		$smarty->assign ('PERIOD_DATES', $periodDates);
		$smarty->assign ('SUPPLIER_INFO', $supplierInfo);
		$smarty->assign ('REPORT_DATA', base64_encode ($reportData));
		$smarty->assign ('URL_AVATARS', "{$_SESSION ['plat']}/user_images");
		
		$smarty->display ('Home/ActionTabs/SupplierPartOfWork.tpl');
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
	

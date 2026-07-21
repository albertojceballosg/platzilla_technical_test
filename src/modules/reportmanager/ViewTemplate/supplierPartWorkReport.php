<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	require_once ('modules/part_work/lib/SupplierPartOfWorkUtils.class.php');
    
    global $adb;
	try {
		$reportData = base64_decode (PlatzillaUtils::purify ($_GET, 'report_data'));
		$data = explode ('@', $reportData);
		
		$periodDates ['startdate'] = $data [0];
		$periodDates ['enddate']   = $data [1];
		$supplierId                = $data [2];
		
		$partOfWorkObject = SupplierPartOfWorkUtils::getInstance ($adb);
		$groupedData      = $partOfWorkObject->fetchPartOfWorkBySupplierGrouped ($supplierId, $periodDates);
		$supplierInfo     = $partOfWorkObject->getSupplierInfo ($supplierId);
		
		$smarty = new vtigerCRM_Smarty ();
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		$supplierName = !empty($supplierInfo['supplier_name']) ? preg_replace('/[^a-zA-Z0-9]/', '_', $supplierInfo['supplier_name']) : 'proveedor';
		$tamplateName = date('YmdHis') . '_parte_trabajo_' . $supplierName . '.pdf';
		$donwLoadField = $tamplateName;
		$smarty->assign ('GROUPED_DATA', $groupedData);
		$smarty->assign ('PERIOD_DATES', $periodDates);
		$smarty->assign ('SUPPLIER_INFO', $supplierInfo);
		$smarty->assign ('URL_AVATARS', "{$_SESSION ['plat']}/user_images");
		$smarty->assign ('EMISSION_DATE', date('d/m/Y H:i'));
		$smarty->assign ('CURRENT_USER', $_SESSION['authenticated_user_info']['user_name']);
		$html = $smarty->fetch ('Home/ActionTabs/SupplierPartOfWorkPDF.tpl');
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
	

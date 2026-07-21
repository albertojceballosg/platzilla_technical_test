<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/platzi_issabel/lib/PlatziIssabel.class.php');
	require_once ('modules/platzi_issabel/platzi_issabel.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	
	global $adb, $app_strings, $currentModule, $mod_strings;
	$isInstance = !empty ($_SESSION ['platInstancia']);
	
	try {
		if ($isInstance) {
			$platziIssabel = new platzi_issabel();
			$platziIssabel->checkModuleSubscription ($currentModule);
		}
		$periodSelected = 'thismonth';
		$periodDates    = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodSelected);
		$where          = array (
			'AND' => array (
				'DATE(calldate) >= ' => "'{$periodDates ['startdate']}'",
				'DATE(calldate) <= ' => "'{$periodDates ['enddate']}'",
			)
		);
		
		$objectIssabel     = PlatziIssabel::getInstance ($_SESSION ['plat']);
		$issabelMonitoring = $objectIssabel->fetchIssabelMonitoring ($where, null);
		$totalRecords      = 0;
		if (!empty ($issabelMonitoring)) {
			$totalRecords     = $issabelMonitoring[0]->getTotalRecords ();
			$idPlatziIsabel   =  rand (1001, 10001);
			$paginator        = $objectIssabel->configPaginator ($totalRecords,$idPlatziIsabel);
			$issabelPaginator = $paginator->createLinks ();
		}
		$recordPerPage = ($objectIssabel->getRecordPerPage () > $totalRecords) ? $totalRecords : $objectIssabel->getRecordPerPage ();
		$smarty        = new vtigerCRM_Smarty ();
		if (isset ($_SESSION ['flashmessage'])) {
			$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
			$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
			unset ($_SESSION ['flashmessage']);
		}
		
		$smarty->assign ('ISSABEL_ID', $idPlatziIsabel);
		$smarty->assign ('ISSABEL_MONITORING', $issabelMonitoring);
		$smarty->assign ('ISSABEL_PAGER', $issabelPaginator);
		$smarty->assign ('ISSABEL_TOTAL_ROWS', $totalRecords);
		$smarty->assign ('MOD',$mod_strings);
		$smarty->assign ('PERIOD_DATES', NotificationPeriodUtils::getAvailablePeriods ());
		$smarty->assign ('PERIOD_SELECTED', $periodSelected);
		$smarty->assign ('RECORDS_PER_PAGE', $recordPerPage);
		$smarty->assign ('START_RECORD', ($objectIssabel->getStartRecord() + 1));
		$smarty->display ('modules/platzi_issabel/ListView.tpl');
	} catch (Exception $e) {
		$code   = $e->getCode ();
		$smarty = new vtigerCRM_Smarty ();
		$smarty->assign ('ISSABEL_MONITORING', null);
		if ($code === 400) {
			$smarty->assign('IS_ERROR', true);
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta');
			$smarty->display ('instanciaUnverified.tpl');
		} else if ($code === 403) {
			$smarty->assign('IS_ERROR', true);
			$smarty->assign ('LABEL', 'Tu suscripción');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
		} else {
			$smarty->assign('IS_ERROR', true);
			$smarty->assign ('LABEL', 'Se ha presentado un error fatal');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
		}
		$smarty->display ('modules/platzi_issabel/ListView.tpl');
	}

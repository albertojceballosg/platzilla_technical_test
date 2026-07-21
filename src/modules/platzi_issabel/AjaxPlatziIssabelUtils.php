<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('modules/platzi_issabel/lib/PlatziIssabel.class.php');
	require_once ('modules/platzi_issabel/platzi_issabel.php');
	require_once ('modules/notifications/lib/NotificationPeriodUtils.class.php');
	
	global $adb, $app_strings, $currentModule, $mod_strings;
	$isInstance = !empty ($_SESSION ['platInstancia']);
	$function   = PlatzillaUtils::purify($_REQUEST, 'function');
	if ($function == 'SEARCH_MONITORING') {
		try {
			$dueDate        = PlatzillaUtils::purify ($_POST, 'duedate', null);
			$function       = PlatzillaUtils::purify ($_REQUEST, 'function');
			$idPlatziIsabel = PlatzillaUtils::purify ($_POST, 'tabid');
			$page           = PlatzillaUtils::purify ($_POST, 'page', null);
			$periodTask     = PlatzillaUtils::purify ($_POST, 'period_dates');
			$recordingType  = PlatzillaUtils::purify ($_POST, 'recording_type');
			$searchInput    = PlatzillaUtils::purify ($_POST, 'search_input');
			$searchOption   = PlatzillaUtils::purify ($_POST, 'search_option');
			$startDate      = PlatzillaUtils::purify ($_POST, 'datestart', null);
			if ($periodTask == 'custom') {
				$periodDates ['startdate'] = $startDate;
				$periodDates ['enddate']   = $dueDate;
			} else {
				$periodDates = NotificationPeriodUtils::getStandarFiltersStartAndEndDate ($periodTask);
			}
			
			$where = array(
				'AND' => array (
					'DATE(calldate) >= ' => "'{$periodDates ['startdate']}'",
					'DATE(calldate) <= ' => "'{$periodDates ['enddate']}'",
				)
			);
			if (!empty($searchInput) && $searchOption !== '"recordingfile') {
				$where ['AND'] [$searchOption . ' = '] = "'{$searchInput}'";
			} else if (!empty ($recordingType)) {
				if ($recordingType == 'i') {
					$where ['AND'] ["SUBSTRING(SUBSTRING_INDEX(recordingfile, '/', -1),1,1) NOT IN"] = "('o','p','q')";
				} else {
					$where ['AND'] ["SUBSTRING(SUBSTRING_INDEX(recordingfile, '/', -1),1,1) = "] = "'{$recordingType}'";
				}
			}
			
			$objectIssabel = PlatziIssabel::getInstance ($_SESSION ['plat']);
			$recordPerPage = $objectIssabel->getRecordPerPage ();
			if ((empty ($page)) || ($page <= 0)) {
				$startRecord = 0;
			} else {
				$startRecord = (($page - 1) * $recordPerPage);
			}
			
			$issabelMonitoring = $objectIssabel->fetchIssabelMonitoring ($where, $startRecord);
			$recordsLink = '';
			if (!empty ($issabelMonitoring)) {
				$totalRecords = $issabelMonitoring[0]->getTotalRecords ();
				$paginator = $objectIssabel->configPaginator ($totalRecords, $idPlatziIsabel);
				$recordsLink = $paginator->createLinks ();
			}
			
			$toRecord = ($recordPerPage + $startRecord);
			if ($toRecord > $totalRecords) {
				$toRecord = $totalRecords;
			}
			$startRecord = ($startRecord == 0) ? 1 : $startRecord;
			$smarty      = new vtigerCRM_Smarty ();
			
			$smarty->assign ('ISSABEL_MONITORING', $issabelMonitoring);
			$smarty->assign ('MOD',$mod_strings);
			$outputArray = array(
				'rows'      => $smarty->fetch ('modules/platzi_issabel/RowsTableBlock.tpl'),
				'paginator' => $recordsLink,
				'records'   => "<span>Mostrando registros&nbsp;{$startRecord} - {$toRecord}&nbsp;de&nbsp;{$totalRecords}</span>",
			);
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK', 'html' => $outputArray));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	}else if ($function == 'AUDIO_MONITORING') {
		try {
			$uniqueId = PlatzillaUtils::purify ($_REQUEST, 'uniqueid');
			if (empty ($uniqueId)) {
				throw new Exception ('Grabación no identificada!');
			}
			$objectIssabel = PlatziIssabel::getInstance ($_SESSION ['plat']);
			// Check record is valid and points to an actual file
			$filebyUid = $objectIssabel->getAudioByUniqueId ($uniqueId);
			if (empty ($filebyUid)) {
				throw new Exception ('Grabación no encontrada');
			}
			if ($filebyUid['deleted']) {
				throw new Exception ('Grabación eliminada');
			}
			if (empty ($filebyUid['fullpath']) || empty ($filebyUid['mimetype'])) {
				throw new Exception ('Grabación no encontrada');
			}
			$smarty = new vtigerCRM_Smarty ();
			if (isset ($_SESSION ['flashmessage'])) {
				$smarty->assign ('IS_ERROR', $_SESSION ['flashmessage']['iserror']);
				$smarty->assign ('MESSAGE', $_SESSION ['flashmessage']['message']);
				unset ($_SESSION ['flashmessage']);
			}
			
			$smarty->assign ('RECORDING_AUDIO', $filebyUid);
			$smarty->display ('modules/platzi_issabel/AudioMonitoringModal.tpl');
		} catch (Exception $e) {
			$code   = $e->getCode ();
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('RECORDING_AUDIO', null);
			$smarty->assign('IS_ERROR', true);
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->display ('modules/platzi_issabel/AudioMonitoringModal.tpl');
		}
	}
	exit();
?>

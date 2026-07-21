<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/BoxScorePanelHelper.class.php');
	require_once ('include/platzilla/Managers/CalculationSystemManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/indicatorspanel/indicatorspanel.php');
	
	global $adb, $app_strings, $current_user, $mod_strings, $theme, $site_URL;
	setBugSnag ($site_URL);
	
	$smarty     = new vtigerCRM_Smarty ();
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	
	if ($function == 'CALL_CLONE_BOXSCORE') {
		$bsName = PlatzillaUtils::purify ($_POST, 'name');
		$id     = PlatzillaUtils::purify ($_POST, 'id_page');
		$title  = PlatzillaUtils::purify ($_POST, 'title');
		try {
			if (empty ($bsName)) {
				throw new Exception ('Indice no encontrado');
			}
			$instances =  (!$isInstance) ? PlatformUtils::getValidInstances () : null;
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('BOX_SCORE_NAME', $bsName);
			$smarty->assign ('INSTANCES', $instances);
			$smarty->assign ('ID_PAGE', $id);
			$smarty->assign ('MOD_STRINGS', $mod_strings);
			$smarty->assign ('TITLE', !empty ($title) ? $title : 'Indicador seleccionado');
			$htmlOutput = $smarty->fetch ('Settings/BoxScore/objets/BoxScoreShareModa.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
		
	} else if ($function == 'CHANGE_EDITABLE') {
		try {
			$codeInstance = PlatzillaUtils::purify ($_POST, 'code');
			$record = PlatzillaUtils::purify ($_POST, 'record', null);
			$status = PlatzillaUtils::purify ($_POST, 'status');
			if (empty ($record)) {
				throw new Exception ('Indice no encontrado');
			} else if (empty ($status) || !in_array ($status, array('YES', 'NO'))) {
						throw new Exception ('Imposible cambiar el estado de edición');
			}
			if ($codeInstance !== 'MOTHER' && !$isInstance) {
				$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
			} else {
				$targetAdb = $adb;
			}
			
			$status = ($status == 'YES') ? 'NO' : 'YES';
			
			BoxScorePanelHelper::setEditable ($targetAdb, $record, $status);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	} else if ($function == 'CHANGE_STATUS') {
		try {
			$bsName       = PlatzillaUtils::purify ($_POST, 'name');
			$codeInstance = PlatzillaUtils::purify ($_POST, 'code');
			$record = PlatzillaUtils::purify ($_POST, 'record', null);
			$status = PlatzillaUtils::purify ($_POST, 'status');
			if (empty ($record) || empty ($bsName)) {
				throw new Exception ('Indice no encontrado');
			} else if (empty ($status) || !in_array ($status, array('ENABLED', 'DISABLED'))) {
				throw new Exception ('Imposible cambiar el status');
			}
			if ($codeInstance !== 'MOTHER' && !$isInstance) {
				$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
			} else {
				$targetAdb = $adb;
			}
			
			$status = ($status == 'ENABLED') ? 'DISABLED' : 'ENABLED';
			
			BoxScorePanelHelper::changeStatus ($targetAdb, $bsName, $status);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => $e->getMessage ()));
		}
	} else if ($function == 'CLONE_BOXSCORE') {
		$bsName       = PlatzillaUtils::purify ($_POST, 'box_score_name');
		$codeInstance = PlatzillaUtils::purify ($_POST, 'code_instance');
		try {
			if (empty ($bsName) || empty ($codeInstance)) {
				throw new Exception ('Imposible clonar el indicador, información incompleta!');
			}
			$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
			BoxScorePanelHelper::checkBoxScoreData ($targetAdb, $bsName);
			$scales = array ('Month', 'Week');
			foreach ($scales as $scale) {
				$motherBoxScoreData [$scale] = BoxScorePanelHelper::getBoxScoreData ($adb, $bsName, $scale);
				$motherBoxScore  [$scale]    = BoxScorePanelHelper::getDefaultBoxScore (
					$adb,
					array ('boxscoreid' => $motherBoxScoreData[$scale]->getScoringBoxId ())
				);
				BoxScorePanelHelper::checkCalculatedField (
					$targetAdb,
					$motherBoxScoreData[$scale]->getCalculatedName (),
					$motherBoxScoreData[$scale]->getSourceModule ()
				);
				BoxScorePanelHelper::checkCalculateSystem ($adb, $targetAdb, $motherBoxScoreData [$scale]->getCalculatedSystem ());
				$relatedAlerts = BoxScorePanelHelper::getRelatedAlerts ($adb, $targetAdb, $motherBoxScoreData [$scale]->getId (), $motherBoxScore [$scale]->getId ());
			}
			BoxScorePanelHelper::createBoxScore ($targetAdb, $motherBoxScoreData, $motherBoxScore, $scales);
			BoxScorePanelHelper::createRelatedAlerts ($targetAdb, $relatedAlerts, $motherBoxScoreData, $motherBoxScore, $scales);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'DELETE_BOXSCORE') {
		try {
			$codeInstance = PlatzillaUtils::purify ($_POST, 'code');
			$record       = PlatzillaUtils::purify ($_POST, 'record', null);
			$bsName       = PlatzillaUtils::purify ($_POST, 'name');
			if (empty ($record) || empty ($bsName)) {
				throw new Exception ('Indice no encontrado');
			}
			if ($codeInstance !== 'MOTHER' && !$isInstance) {
				$targetAdb = AdbManager::getInstance ()->getTargetInstanceAdb ($codeInstance);
			} else {
				$targetAdb = $adb;
			}
			BoxScorePanelHelper::deleteIndicator ($targetAdb, $record, $bsName);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
	exit();

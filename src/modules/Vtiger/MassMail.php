<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/MassMailUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/SystemVariables.class.php');
	require_once ('modules/emailmanager/lib/EmailManagerUtils.class.php');

	global $adb, $currentModule;
	$function = PlatzillaUtils::purify ($_REQUEST, 'function');
	$modalId  = PlatzillaUtils::purify ($_REQUEST,   'ID', rand (1000,150000));
	if ($function == 'FETCH_FIELDS') {
		try {
			$flModule = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
			if (empty ($flModule)) {
				throw new Exception ('Modulo origen no encontrado');
			}
					
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('FIELDS', MassMailUtils::fetchFieldsByModule ($adb, $flModule));
			$htmlOutput = $smarty->fetch ('Objetcs/MassMail/SourceModuleFileds.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('HTTP/1.1 400 Bad request');
			header ('Content-Type: application/json');
			echo json_encode ($e->getMessage ());
		}
	} else if ($function == 'OPEN_MODAL') {
		try {
			$recordIds = PlatzillaUtils::purify ($_REQUEST, 'record_ids');
			if (empty ($recordIds)) {
				throw new Exception ('No has seleccionado ningún registro');
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_LANGUAGES', MassMailUtils::getAvailableLanguages ());
			$smarty->assign ('AVAILABLE_MODULES', MassMailUtils::fetchAvailableModules ($adb));
			$smarty->assign ('FIELDS', MassMailUtils::fetchFieldsByModule ($adb, $currentModule));
			$smarty->assign ('MODULE', $currentModule);
			$smarty->assign ('RECORD_IDS', explode (';', $recordIds));
			$smarty->assign ('SYSTEM_VARIABLES', SystemVariables::getAvailableVariables ());
			$smarty->assign ('TEMPLATES', MassMailUtils::fetchEmailManagerTemplates ($adb));
			$htmlOutput = $smarty->fetch ('MassMailModal.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('HTTP/1.1 400 Bad request');
			header ('Content-Type: application/json');
			echo json_encode ($e->getMessage ());
		}
	}
	exit ();


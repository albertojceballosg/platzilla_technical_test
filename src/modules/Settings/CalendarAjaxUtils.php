<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/utils.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('modules/Settings/lib/ActivityAjaxHelper.class.php');
	require_once ('modules/Settings/lib/SettingsUtils.class.php');
	
	global $adb, $mod_strings, $current_language;
	if (!isset ($adb)) {
		require_once ('include/database/PearDatabase.php');
	}
	
	$function = SettingsUtils::purify ($_REQUEST, 'function');
	if ($function == 'FETCH-PICKLIST') {
		try {
			$fieldName = PlatzillaUtils::purify ($_REQUEST, 'fieldname');
			$flModule  = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
			if (empty ($fieldName)) {
				throw new Exception ('Campo lista no encontrado');
			} else if (empty($flModule)) {
				throw new Exception ('Modulo del campo lista no encontrado');
			}
			$moduleTranslator = return_module_language ($current_language, $flModule);
			
			$pickList = PicklistManager::getInstance ($adb)->fetchPicklistByName ($fieldName, true);
			if (empty($pickList)) {
				throw new Exception ('Lista no encontrada');
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MOD', $moduleTranslator);
			$smarty->assign ('PICKLIST_VALUES', $pickList);
			$smarty->assign ('VALUE', null);
			
			$htmlOutput = $smarty->fetch ('utils/HTMLPickListOptions.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'FIELD_TYPE_OWNER') {
		try {
			$userOwner = getUserslist();
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $userOwner));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
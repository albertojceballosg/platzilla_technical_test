<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ApplicationsManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/platzilla/Managers/PicklistManager.php');
	require_once ('include/platzilla/Managers/PipelineManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/LayoutBlockListHelper.class.php');
	
	global $adb, $app_strings, $current_user, $current_module, $mod_strings, $site_URL, $theme;
	
	setBugSnag ($site_URL);
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	$masterAdb   = AdbManager::getInstance ()->getMasterAdb ();
	try {
		if ($isInstance) {
			if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
				throw new Exception ('Debes verificar tu cuenta', 400);
			}
			$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
			$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
			if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
				throw new Exception ('Tu suscripción se encuentra inactiva', 403);
			}
		}
	} catch (Exception $e) {
		header ('Access-Control-Allow-Origin: *');
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => $e->getMessage()));
	}
	if ($function == 'RELATED-LIST-FIELDS') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			
			$index                  = PlatzillaUtils::purify ($_REQUEST, 'index');
			$availableRelatedFields[ $moduleName ] = FieldManager::getInstance ($adb)->fetchFieldHeaders ($moduleName);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('AVAILABLE_RELATED_FIELDS', $availableRelatedFields);
			$smarty->assign ('FROM_AJAX', true);
			$smarty->assign ('INDEX', $index);
			$smarty->assign ('RELATED_LIST', null);
			$smarty->assign ('relatedListRelatedModuleName', $moduleName);
			$smarty->assign ('relatedListRelatedModuleLabel', getTabIdLabelByName($moduleName));
			
			$htmlOutput = $smarty->fetch ('Settings/LayoutEditor/RelatedListFields.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'RELATED-IMPORT-FIELDS') {
		try {
			$mainModule = PlatzillaUtils::purify ($_REQUEST, 'mainmodule');
			if(empty ($moduleName) || empty($mainModule)) {
				throw new Exception ('Módulo no encontrado');
			}
			
			$index                                 = PlatzillaUtils::purify ($_REQUEST, 'index');
			$availableRelatedFields[ $moduleName ] = FieldManager::getInstance ($adb)->fetchFieldHeaders ($moduleName);
			$availableRelatedFields[ $mainModule ] = FieldManager::getInstance ($adb)->fetchFieldHeaders ($mainModule);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('AVAILABLE_RELATED_FIELDS', $availableRelatedFields);
			$smarty->assign ('DATE_FIELD_IMPORT', LayoutBlockListHelper::DATE_FIELD_IMPORT);
			$smarty->assign ('FROM_AJAX', true);
			$smarty->assign ('INDEX', $index);
			$smarty->assign ('moduleName', $mainModule);
			$smarty->assign ('moduleLabel', getTabIdLabelByName($mainModule));
			$smarty->assign ('N0_IMPORT_FIELD', LayoutBlockListHelper::N0_IMPORT_FIELD);
			$smarty->assign ('RELATED_LIST', null);
			$smarty->assign ('relatedListRelatedModuleName', $moduleName);
			$smarty->assign ('relatedListRelatedModuleLabel', getTabIdLabelByName($moduleName));
			
			$htmlOutput = $smarty->fetch ('Settings/LayoutEditor/RelatedImportFields.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'FETCH-PICKLIST') {
		try {
			$fieldName = PlatzillaUtils::purify ($_REQUEST, 'fieldname');
			if(empty ($fieldName)) {
				throw new Exception ('Campo lista no encontrado');
			}
			
			$pickList = PicklistManager::getInstance ($adb)->fetchPicklistByName ($fieldName, true);
			if (empty($pickList)) {
				throw new Exception ('Lista no encontrada');
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('PICKLIST_VALUES', $pickList);
			$smarty->assign ('VALUE', null);
			
			$htmlOutput = $smarty->fetch ('utils/HTMLPickListOptions.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'FETCH-PIPELINE') {
		try {
			$fieldName = PlatzillaUtils::purify ($_REQUEST, 'fieldname');
			if(empty ($fieldName)) {
				throw new Exception ('Campo pipeline no encontrado');
			}
			
			$pipeLine = PipelineManager::getInstance ($adb)->fetchPipeline ($moduleName, $fieldName);
			
			if (empty($pipeLine)) {
				throw new Exception ('Lista no encontrada');
			}
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('PIPELINE_VALUES', $pipeLine->getValues ());
			$smarty->assign ('VALUE', null);
			
			$htmlOutput = $smarty->fetch ('utils/HTMLPipelimeOptions.tpl');
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
	
	exit();

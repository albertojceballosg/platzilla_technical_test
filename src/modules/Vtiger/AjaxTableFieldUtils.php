<?php
	require_once ('Smarty_setup.php');
	require_once ('include/platzilla/Data/ApplicationsManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Managers/GlobalPicklistManager.php');
	require_once ('include/platzilla/Managers/TableFieldManager.php');
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
	$masterAdb  = AdbManager::getInstance ()->getMasterAdb ();
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
	if ($function == 'ADD-ROW-TABLE') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$fieldName    = PlatzillaUtils::purify ($_REQUEST, 'fieldname');
			$idTableField = PlatzillaUtils::purify ($_REQUEST, 'idtable');
			
			$tablaFields = TableFieldManager::getInstance ($adb)->fetchTableFieldConfig ($fieldName, $moduleName);
			
			foreach ($tablaFields as $field) {
				if ($field->getUiType () == FieldInterface::UI_TYPE_GLOBAL_PICKLIST) {
					$myPicklist[$field->getFieldName ()] = GlobalPicklistManager::getInstance ($adb)->fetchPicklistByName ($field->getFieldName ())->getValues ();
				}
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('TABLE_FIELDS', $tablaFields);
			$smarty->assign ('FIELD_NAME', $fieldName);
			$smarty->assign ('GLOBAL_PICKLIST', (isset($myPicklist)) ? $myPicklist : null);
			$smarty->assign ('ID_TABLE_FIELD', $idTableField);
			$smarty->assign ('MODULE', $moduleName);
			$smarty->assign ('NUMBERING_FORMAT', $current_user->numbering_format);
			$smarty->assign ('USER_DATE_FORMAT', $current_user->date_format ? $current_user->date_format : 'yyyy-mm-dd');
			$htmlOutput = $smarty->fetch ('Settings/GridManager/TableFieldActions/TableFieldEditViewRow.tpl');
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
	} else if ($function == 'RELATED-MODULE-ACTION') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$record       = PlatzillaUtils::purify ($_REQUEST, 'crmid');
			$idTableField = PlatzillaUtils::purify ($_REQUEST, 'idtable');
			$entity       = CRMEntity::getInstance ($moduleName);
			$entity->retrieve_entity_info ($record, $moduleName);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode (array('error' => 'OK', 'html' => $entity->column_fields));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'PICKLIST-ACTION') {
		try {
			
			$pickListName = PlatzillaUtils::purify ($_REQUEST, 'picklist');
			$idTableField = PlatzillaUtils::purify ($_REQUEST, 'idtable');
			$idRowTable   = PlatzillaUtils::purify ($_REQUEST, 'idrow');
			$fieldName    = PlatzillaUtils::purify ($_REQUEST, 'fieldname');
			$column       = PlatzillaUtils::purify ($_REQUEST, 'column');
			
			$pickList = PicklistManager::getInstance ($adb)->fetchPicklistByName ($pickListName); //, $isInstance
			
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_PICKLIST', $pickList->getValues ());
			$smarty->assign ('COLUMN_NAME', $column);
			$smarty->assign ('FIELD_NAME', $fieldName);
			$smarty->assign ('ID_TABLE_FIELD', $idTableField);
			$smarty->assign ('ID_ROW_TABLE', $idRowTable);
			$smarty->assign ('MOD', $mod_strings);
			$htmlOutput = $smarty->fetch ('Settings/GridManager/TableFieldActions/Picklist_Action.tpl');
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
	} else if ($function == 'FIELD-TO-ACTIVATION') {
		try {
			$tableColumns = PlatzillaUtils::purify ($_REQUEST, 'columns');
			$idLinkage    = PlatzillaUtils::purify ($_REQUEST, 'idlinkage');
			$checkboxName  = PlatzillaUtils::purify ($_REQUEST, 'checkbox');
			$column       = array();
			foreach ($tableColumns as $fieldData) {
				list($label, $name, $type) = explode ('@', $fieldData);
				$column [] = array(
					'name'  => $name,
					'label' => $label,
					'type'  =>$type,
				);
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_COLUMNS', $column);
			$smarty->assign ('CHECKBOX_NAME', $checkboxName);
			$smarty->assign ('ID_LINKAGE', $idLinkage);
			$htmlOutput = $smarty->fetch ('Settings/GridManager/TableFieldActions/CheckBoxActions.tpl');
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
	} else if ($function == 'LINKAGE-LIST') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$tableLists  = PlatzillaUtils::purify ($_REQUEST, 'list');
			$textColumns = PlatzillaUtils::purify ($_REQUEST, 'columms');
			$myList     = array();
			foreach ($tableLists as $listField) {
				list($name, $label, $values) = explode ('@', $listField);
				$values = str_replace ("\n", '@', $values);
				$myList [] = array (
					'name'   => $name,
					'label'  => $label,
					'values' => explode ('@', $values),
					'string' => $values,
				);
			}
			$myColumns = array ();
			foreach ($textColumns as $textField) {
				list($label, $name, $type) = explode ('@', $textField);
				$myColumns [] = array (
					'name'  => $name,
					'label' => $label,
					'type'  =>$type,
				);
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_COLUMNS', $myColumns);
			$smarty->assign ('AVAILABLE_FIELD', null);
			$smarty->assign ('AVAILABLE_LIST', $myList);
			$smarty->assign ('MODULE_WITH_LIST', getModulesWithGridFields ($moduleName, '15'));
			$htmlOutput = $smarty->fetch ('Settings/GridManager/TableFieldActions/ListLinkages.tpl');
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
	} else if ($function == 'OPERATION-ROW') {
		try {
			
			$columns   = PlatzillaUtils::purify ($_REQUEST, 'columns');
			$myColumns = array ();
			foreach ($columns as $column) {
				list($label, $name, $type) = explode ('@', $column);
				$myColumns [] = array (
					'name'  => $name,
					'label' => $label,
					'type'  =>$type,
				);
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_COLUMNS', $myColumns);
			$smarty->assign ('AVAILABLE_OPERATIONS', array ( 'ADD', 'SUBTRACT', 'MULTIPLY', 'DIVIDE', 'RULE_THREE'));
			$smarty->assign ('MOD', $mod_strings);
			$htmlOutput = $smarty->fetch ('Settings/GridManager/TableFieldActions/OperationRow.tpl');
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
	} else if ($function == 'APPEARANCE') {
		try {
			
			$columns     = PlatzillaUtils::purify ($_REQUEST, 'columms');
			$myColumns   = array ();
			$totalColumn = count ($columns);
			$widthInit   = floor ((100/$totalColumn));
			foreach ($columns as $column) {
				list($label, $name) = explode ('@', $column);
				$myColumns [] = array (
					'name'  => $name,
					'label' => $label,
					'width' => $widthInit,
				);
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_COLUMNS', $myColumns);
			$smarty->assign ('MOD', $mod_strings);
			$htmlOutput = $smarty->fetch ('Settings/GridManager/TableFieldActions/TableRowAppearance.tpl');
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

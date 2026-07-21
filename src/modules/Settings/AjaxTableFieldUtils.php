<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/Settings/lib/TableFieldHelper.class.php');
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
	
	$function      = PlatzillaUtils::purify ($_REQUEST, 'function');
	$moduleName    = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
	$relModuleName = PlatzillaUtils::purify ($_REQUEST, 'reModule', null);
	
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
	if ($function == 'FIELD-TO-IMPORT') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$tableColumns  = PlatzillaUtils::purify ($_REQUEST, 'columns');
			$fieldName     = PlatzillaUtils::purify ($_REQUEST, 'fieldName');
			$idLinkage     = PlatzillaUtils::purify ($_REQUEST, 'idlinkage');
			$tableFileName = PlatzillaUtils::purify ($_REQUEST, 'tableFileName');
			$column        = array();
			foreach ($tableColumns as $fieldData) {
				list($label, $name, $type) = explode ('@', $fieldData);
				$column [] = array(
					'name'  => $name,
					'label' => $label,
					'type'  =>$type,
				);
			}
			
			if (!empty($tableFileName)) {
				$tableFields = TableFieldManager::getInstance ($adb)->fetchTableFieldConfig ($tableFileName, $relModuleName);
				if (!empty($tableFields)) {
					$actionsField = array();
					foreach ($tableFields as $tableField ) {
						if (($tableField->getUiType () == FieldInterface::UI_TYPE_MODULE_REFERENCE) && $tableField->getFieldName () == $fieldName) {
							$actionsField = $tableField->getActionArray ();
						}
					}
					$moduleField = $actionsField['import']['modulefield'];
					$tableField = $actionsField['import']['tablefield'];
				}
			}
			
			$muduleFields = FieldManager::getInstance ($adb)->fetchFieldHeaders ($moduleName);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_FIELD', FieldManager::getInstance ($adb)->fetchFieldHeaders ($moduleName));
			$smarty->assign ('AVAILABLE_COLUMNS', $column);
			$smarty->assign ('ACTION_MODULE_FIELDS', isset($moduleField) ? $moduleField : null);
			$smarty->assign ('ACTION_TABLE_FIELDS', isset($tableField) ? $tableField : null);
			$smarty->assign ('AVAILABLE_FIELD_TYPES', array ('1', '5', '7', '9', '11', '13', '15', '17', '21', '71','5010'));
			$smarty->assign ('FIELD_NAME', $fieldName);
			$smarty->assign ('ID_LINKAGE', $idLinkage);
			$htmlOutput = $smarty->fetch ('Settings/GridManager/TableFieldActions/FieldsLinkages.tpl');
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
	} else if ($function == 'SUMMARY-ROW') {
		try {
			$columns       = PlatzillaUtils::purify ($_REQUEST, 'columns');
			$tableFileName = PlatzillaUtils::purify ($_REQUEST, 'tableFileName');
			$myColumns     = array ();
			foreach ($columns as $column) {
				list($label, $name, $type) = explode ('@', $column);
				$myColumns [] = array (
					'name'  => $name,
					'label' => $label,
					'type'  =>$type,
				);
			}
			
			if (!empty($tableFileName)) {
				$tableFields = TableFieldManager::getInstance ($adb)->fetchTableFieldConfig ($tableFileName, $relModuleName);
				if (!empty($tableFields)) {
					$actionsField = array();
					foreach ($tableFields as $tableField ) {
						if ($tableField->getUiType () == FieldInterface::UI_TYPE_SUMMARY_ROW) {
							$actionsField = $tableField->getActionArray ();
						}
					}
				}
				if (!count($actionsField)) {
					header ('Access-Control-Allow-Origin: *');
					header ('HTTP/1.1 200 OK');
					header ('Content-Type: application/json; charset=utf-8');
					echo json_encode(array('error' => 'OK', 'html' => ''));
					exit();
				}
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ACTIONS_FILES', (isset($actionsField)) ? $actionsField : null);
			$smarty->assign ('AVAILABLE_COLUMNS', $myColumns);
			$smarty->assign ('AVAILABLE_OPERATIONS', array ( 'SUM_COLUMN', 'COUNT_COLUMN', 'AVERAGE_COLUMN'));
			$smarty->assign ('MOD', $mod_strings);
			$htmlOutput = $smarty->fetch ('Settings/GridManager/TableFieldActions/SummaryRow.tpl');
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
			$tableColumns  = PlatzillaUtils::purify ($_REQUEST, 'columns');
			$idLinkage     = PlatzillaUtils::purify ($_REQUEST, 'idlinkage');
			$checkboxName  = PlatzillaUtils::purify ($_REQUEST, 'checkbox');
			$tableFileName = PlatzillaUtils::purify ($_REQUEST, 'tableFileName');
			$column         = array();
			foreach ($tableColumns as $fieldData) {
				list($label, $name, $type) = explode ('@', $fieldData);
				$column [] = array(
					'name'  => $name,
					'label' => $label,
					'type'  =>$type,
				);
			}
			
			if (!empty($tableFileName)) {
				$tableFields = TableFieldManager::getInstance ($adb)->fetchTableFieldConfig ($tableFileName, $relModuleName);
				if (!empty ($tableFields)) {
					$actionsField = array();
					foreach ($tableFields as $tableField) {
						if (($tableField->getUiType () == FieldInterface::UI_TYPE_CHECKBOX) && $tableField->getFieldName () == $checkboxName) {
							$actionsField = $tableField->getActionArray ();
						}
					}
				}
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_COLUMNS', $column);
			$smarty->assign ('ACTIONS_FILES', (isset($actionsField)) ? $actionsField : null);
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
			$tableLists    = PlatzillaUtils::purify ($_REQUEST, 'list');
			$textColumns   = PlatzillaUtils::purify ($_REQUEST, 'columms');
			$tableFileName = PlatzillaUtils::purify ($_REQUEST, 'tableFileName');
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
			
			if (!empty($textColumns)) {
				$myColumns = array ();
				foreach ($textColumns as $textField) {
					list($label, $name, $type) = explode ('@', $textField);
					$myColumns [] = array(
						'name' => $name,
						'label' => $label,
						'type' => $type,
					);
				}
			}
			
			if (!empty ($tableFileName)) {
				$tableFields = TableFieldManager::getInstance ($adb)->fetchTableFieldConfig ($tableFileName, $moduleName);
				if (!empty ($tableFields)) {
					$actionsField = array();
					foreach ($tableFields as $tableField ) {
						if ($tableField->getUiType () == FieldInterface::UI_TYPE_PICKLIST) {
							$actionsField[$tableField->getFieldName ()] = $tableField->getActionArray ()['list'];
						}
					}
				}
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_COLUMNS', (isset ($myColumns)) ? $myColumns : null);
			$smarty->assign ('AVAILABLE_FIELD', null);
			$smarty->assign ('AVAILABLE_LIST', $myList);
			$smarty->assign ('ACTIONS_FILES', (isset($actionsField)) ? $actionsField : null);
			$smarty->assign ('MODULE_WITH_LIST', (isset ($myColumns)) ? getModulesWithGridFields ($moduleName, '15') : null);
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
			$columns       = PlatzillaUtils::purify ($_REQUEST, 'columns');
			$tableFileName = PlatzillaUtils::purify ($_REQUEST, 'tableFileName');
			$myColumns = array ();
			foreach ($columns as $column) {
				list($label, $name, $type) = explode ('@', $column);
				$myColumns [] = array (
					'name'  => $name,
					'label' => $label,
					'type'  =>$type,
				);
			}
			if (!empty($tableFileName)) {
				$tableFields = TableFieldManager::getInstance ($adb)->fetchTableFieldConfig ($tableFileName, $relModuleName);
				if (!empty ($tableFields)) {
					$actionsField = array();
					foreach ($tableFields as $tableField ) {
						if ($tableField->getUiType () == FieldInterface::UI_TYPE_OPERATION_ROW) {
							$actionsField = $tableField->getActionArray ();
						}
					}
				}
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('ACTIONS_FILES', (isset($actionsField)) ? $actionsField : null);
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
			$columns       = PlatzillaUtils::purify ($_REQUEST, 'columms');
			$tableFileName = PlatzillaUtils::purify ($_REQUEST, 'tableFileName');
			$myColumns     = array ();
			$totalColumn   = count ($columns);
			$widthInit     = floor ((100/$totalColumn));
			foreach ($columns as $column) {
				list($label, $name) = explode ('@', $column);
				$myColumns [] = array (
					'name'  => $name,
					'label' => $label,
					'width' => $widthInit,
				);
			}
			if (!empty($tableFileName)) {
				$tableFields = TableFieldManager::getInstance ($adb)->fetchTableFieldConfig ($tableFileName, $relModuleName);
			}
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('AVAILABLE_COLUMNS', $myColumns);
			$smarty->assign ('TABLE_FIELDS', (isset($tableFields)) ? $tableFields : null);
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
	} else if ($function == 'VALIDATE-NAME') {
		try {
			if(empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$tableLabel     = PlatzillaUtils::purify ($_REQUEST, 'tableFileName');
			$fieldTableName = TableFieldHelper::sanitizeString ($tableLabel);
			$fieldTableName = substr ($fieldTableName, 0, 49);
			
			$field  = FieldManager::getInstance ($adb)->fetchFieldByName ($moduleName, $fieldTableName, true);
			
			if (!empty ($field)) {
				throw new Exception ('Uoops! Ya se ha registrado un campo tabla con el mismo nombre');
			}
			
			$htmlOutput =  'NOT_EXIST';
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

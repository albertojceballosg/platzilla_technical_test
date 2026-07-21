<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('modules/systemalerts/systemalerts.php');
	require_once ('modules/Settings/lib/LayoutBlockListHelper.class.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	global $adb, $currentModule, $mod_strings, $theme, $current_user;
	try {
		$local_user   = clone $current_user;
		$applications = IndicatorsPanelHelper::getAplicationsInstance ($adb, $_SESSION ['platInstancia'], $local_user, $current_user);
		$app          = PlatzillaUtils::purify ($_REQUEST, 'app');
		$mode         = PlatzillaUtils::purify ($_REQUEST, 'mode');
		$scaleSearch  = PlatzillaUtils::purify ($_REQUEST, 'viewPeriod', 'Month');
		$codeType     = PlatzillaUtils::purify ($_REQUEST, 'codeType');
		
		$optionsMenu = getHeaderArray ();
		$appReady = array ('all' => $alertModString['ALL_APLICATIONS']);
		foreach ($optionsMenu as $optionMenu) {
			$code = str_replace (array ('&oacute;'), array('o'), $optionMenu['name']);
			$code = strtolower ($code);
			if (in_array ($code, array_keys ($appReady)) || $code == 'revision') {
				continue;
			}
			$appReady[ $code ] = $optionMenu ['name'];
		}
		
		$smarty       = new vtigerCRM_Smarty ();
		if ($mode != 'create') {
			$systemAlertId = PlatzillaUtils::purify ($_REQUEST, 'systemAlertId');
			$detailAlert   = SystemAlerts::getDetailAlertById ($adb, $systemAlertId, $codeType);
			if (!empty($detailAlert) && $detailAlert['source_alert'] == 'Task_object_no_cump') {
				$fieldsTab = array ();
				$fields    = FieldManager::getInstance ($adb)->fetchFieldHeaders ($detailAlert['tab_name']);
				$notAvailable = array_merge (array(2202, 10), LayoutBlockListHelper::N0_IMPORT_FIELD);
				if (!empty($fields)) {
					foreach ($fields as $field) {
						if (in_array ($field->getUiType (), $notAvailable)) {
							continue;
						}
						$label       = ($field->getLabel () == 'Assigned To') ? 'Asignado a' : $field->getLabel ();
						$fieldsTab[] = array (
							'fieldName'  => $field->getName (),
							'fieldLabel' => $label,
							'uiType'     => $field->getUiType (),
							'fieldType'  => $field->getDataType (),
						);
					}
				}
				$alertFieldData = FieldManager::getInstance ($adb)->fetchFieldById ($detailAlert ['field_id']);
				if ($alertFieldData->getUiType () == FieldInterface::UI_TYPE_PICKLIST) {
					$pickList = PicklistManager::getInstance ($adb)->fetchPicklistByName ($alertFieldData->getName (), true);
					$smarty->assign ('PICKLIST_VALUES', $pickList);
					$smarty->assign ('VALUE', $detailAlert['value_alert']);
					$pickListAlertValue = $smarty->fetch ('utils/HTMLPickListOptions.tpl');
				} else if ($alertFieldData->getUiType () == FieldInterface::UI_TYPE_PIPELINE) {
					$pipeLine = PipelineManager::getInstance ($adb)->fetchPipeline ($moduleName, $fieldName);
					$smarty->assign ('PIPELINE_VALUES', $pipeLine->getValues ());
					$smarty->assign ('VALUE', $detailAlert['value_alert']);
					$pickListAlertValue = $smarty->fetch ('utils/HTMLPipelimeOptions.tpl');
				} else if ($alertFieldData->getUiType () == FieldInterface::UI_TYPE_OWNER) {
					$pickListAlertValue   = str_replace ('value=' . $detailAlert['value_alert']. '>', 'value=' . $detailAlert['value_alert'] . ' selected="selected">', getUserslist(false));
				}
				$optionsMenu = getHeaderArray ();
				foreach ($optionsMenu as $optionMenu) {
					$code = str_replace (array ('&oacute;'), array('o'), $optionMenu['name']);
					$code = strtolower ($code);
					if ($code !== $detailAlert['code_app']) {
						continue;
					}
					foreach ($optionMenu['elementos'] as $theElement) {
						$detailAlert ['element'][] = array (
							'tabid'    => $theElement ['id'],
							'name'     => $theElement ['name'],
							'tablabel' => $theElement ['label'],
						);
					}
				}
				$typeOfData               = explode ('~',$alertFieldData->getTypeOfData ());
				$detailAlert ['operator'] = SystemAlertsHelper::getOperator ($typeOfData[ 0 ]);
			} else if (!empty($detailAlert) && $detailAlert['source_alert'] == 'Indicators') {
				$detailAlert ['element'] = SystemAlertsHelper::getFieldElementIndicators ($adb, $detailAlert['code_app'], $detailAlert['scale']);
			}
		}
		
		$smarty->assign ('THEME', $theme);
		$smarty->assign ('MODSTRING', $mod_strings);
		$smarty->assign ('MODULE', $currentModule);
		$smarty->assign ('VIEW_SEARCH', $scaleSearch);
		$smarty->assign ('APPLICATIONS', $appReady);
		$smarty->assign ('TAB_ACTIVE', $app);
		$smarty->assign ('MODE', $mode);
		$smarty->assign ('DETAIL_ALERT', (isset($detailAlert)) ? $detailAlert : array());
		$smarty->assign ('AVAIABLE_FIELD', (isset($fieldsTab)) ? $fieldsTab : null);
		$smarty->assign ('PICK_LIST_VALUES', (isset($pickListAlertValue)) ? $pickListAlertValue : null);
		$htmlOutput = $smarty->fetch ('modules/systemalerts/CreateAlertIndicator.tpl');
		
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

<?php
	require_once ('Smarty_setup.php');
	require_once ('modules/indicatorspanel/lib/IndicatorsPanelHelper.class.php');
	require_once ('modules/systemalerts/systemalerts.php');
	require_once ('modules/systemalerts/lib/SystemAlertsHelper.class.php');
	require_once ('modules/systemalerts/lib/SystemAlertFilterUtils.class.php');
	require_once ('modules/Settings/lib/LayoutBlockListHelper.class.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	
	global $adb, $currentModule, $mod_strings, $smarty, $theme, $current_user, $current_language;
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	
	if ($function == 'LOAD_ALERTS') {
		$first = new DateTime();
		$first->modify ('first day of this month');
		$last = new DateTime();
		$last->modify ('last day of this month');
		$first->modify ('first day of this month');
		$firstDay = $first->format ('Y-m-d');
		
		$scaleSearch = PlatzillaUtils::purify ($_REQUEST, 'viewPeriod', 'Month');
		$from        = PlatzillaUtils::purify ($_REQUEST, 'date_from', $first->format ('Y-m-d'));
		$to          = PlatzillaUtils::purify ($_REQUEST, 'date_to', $last->format ('Y-m-d'));
		$idView      = PlatzillaUtils::purify ($_REQUEST, 'idView');
		$first       = new DateTime();
		$countAlerts = 0;
		try {
			$local_user   = clone $current_user;
			$applications = IndicatorsPanelHelper::getAplicationsInstance ($adb, $_SESSION ['platInstancia'], $local_user, $current_user);
			$app          = PlatzillaUtils::purify ($_REQUEST, 'app', 'all');
			$applications = (array ('all' => array ('config_applicationsid' => 0, 'app_code' => 'all', 'app_name' => $mod_strings['ALL_APLICATIONS'])) + $applications);
		
			if ($app == 'all') {
				$optionsMenu = getHeaderArray ();
				$appReady = array ('all' => $mod_strings['ALL_APLICATIONS']);
				foreach ($optionsMenu as $optionMenu) {
					$code = str_replace (array ('&oacute;'), array('o'), $optionMenu['name']);
					$code = strtolower ($code);
					if (in_array ($code, array_keys ($appReady)) || $code == 'revision') {
						continue;
					}
					$appReady[ $code ] = $optionMenu ['name'];
					$alert = SystemAlerts::getInstance ($adb, $scaleSearch, $code, $from, $to, 'no');
					if ($alert->alerts != null) {
						$alerts[ $code ]              = $alert->alerts;
						$alerts[ $code ] ['app_name'] = $optionMenu['name'];
						$countAlerts                  = ($countAlerts + $alerts[ $code ]['countAlert']);
					}
				}
				$fetchField = 'modules/systemalerts/DetailViewAllAlerts.tpl';
			} else {
				$alert       = SystemAlerts::getInstance ($adb, $scaleSearch, $app, $from, $to, 'no');
				$alerts      = $alert->alerts;
				$countAlerts = $alerts['countAlert'];
				$fetchField = 'modules/systemalerts/DetailViewAlerts.tpl';
			}
			
			$smarty = new vtigerCRM_Smarty;
			$smarty->assign ('THEME', $theme);
			$smarty->assign ('MODSTRING', $mod_strings);
			$smarty->assign ('MODULE', $currentModule);
			$smarty->assign ('VIEW_SEARCH', $scaleSearch);
			$smarty->assign ('DATE_FROM', $from);
			$smarty->assign ('DATE_TO', $to);
			$smarty->assign ('FIRST_DAY', $firstDay);
			$smarty->assign ('TAB_ACTIVE', $app);
			$smarty->assign ('LABEL_ALL_APLICATIONS', $mod_strings['ALL_APLICATIONS']);
			$smarty->assign ('ALL_ALERTS', $alerts);
			$smarty->assign ('LABEL_OPERATOR', SystemAlertsHelper::getOperator ());
			$smarty->assign ('COUNT_ALL_ALERTS', $countAlerts);
			$smarty->assign ('idAlertListView', $idView);
			$htmlOutput = $smarty->fetch ($fetchField);
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
	} else if ($function == 'VIEW_ALERTS') {
		$scaleSearch   = PlatzillaUtils::purify ($_REQUEST, 'viewScale');
		$app           = PlatzillaUtils::purify ($_REQUEST, 'app');
		$systemAlertId = PlatzillaUtils::purify ($_REQUEST, 'record');
		$from          = PlatzillaUtils::purify ($_REQUEST, 'date_from');
		$to            = PlatzillaUtils::purify ($_REQUEST, 'date_to');
		$sourceAlert   = PlatzillaUtils::purify ($_REQUEST, 'sourceAlert');
		
		try {
			$detailAlert = SystemAlertsHelper::getDetailIndicatorAlert ($adb, $systemAlertId, $from, $to);
			
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('FLAGGED_ALERTS', SystemAlertsHelper::getFlaggedAlerts ($adb, $current_user->id, $from, $to));
			$smarty->assign ('MODSTRING', $mod_strings);
			$smarty->assign ('MODULE', $currentModule);
			$smarty->assign ('VIEW_SEARCH', $scaleSearch);
			$smarty->assign ('DATE_FROM', $from);
			$smarty->assign ('DATE_TO', $to);
			$smarty->assign ('APP', $app);
			$smarty->assign ('DETAIL_ALERT', $detailAlert);
			$smarty->assign ('RECORD', $systemAlertId);
			$smarty->assign ('SOURCE_ALERT', $sourceAlert);
			$htmlOutput = $smarty->fetch ('modules/systemalerts/ViewIndicatorsAlerts.tpl');
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
	} else if ($function == 'DELETE_ALERTS') {
		$delete   = PlatzillaUtils::purify ($_REQUEST, 'delete');
		$recordId = PlatzillaUtils::purify ($_REQUEST, 'record');
		$codeType = PlatzillaUtils::purify ($_REQUEST, 'codeType');
		try {
			if (empty ($codeType) || empty ($recordId) || empty ($delete)) {
				throw new Exception ('Uoops! Faltó información');
			}
			$detailAlert   = SystemAlerts::getDetailAlertById ($adb, $recordId, $codeType);
			if (empty($detailAlert)) {
				throw new Exception ('Imposible elimnar! alerta no encontrada!');
			}
			$moduleName = '';
			if ($detailAlert['source_alert'] != 'Indicators') {
				$moduleName = $detailAlert['tab_name'];
			}
			SystemAlertsHelper::deleteAlerts ($adb, $recordId, $moduleName);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'PARAM_FIELD_ELEMENTS') {
		$appSelect = PlatzillaUtils::purify ($_REQUEST, 'appSelect');
		$type      = PlatzillaUtils::purify ($_REQUEST, 'type');
		$period    = PlatzillaUtils::purify ($_REQUEST, 'viewPeriod');
		try {
			$local_user = clone $current_user;
			$optionsMenu = getHeaderArray ();
			
			if ($type == 'Indicators') {
				$element = SystemAlertsHelper::getFieldElementIndicators ($adb, $appSelect, $period);
				$excludedCategories = array ('Marco','Infraestructura','Actividades','Revision','Control','Mejoras');
				$categories         = IndicatorsPanelHelper::getCategories ($excludedCategories);
				$categories ['KR']  = 'KR';
			} else {
				$element = array ();
				foreach ($optionsMenu as $optionMenu) {
					$code = str_replace (array ('&oacute;'), array('o'), $optionMenu['name']);
					$code = strtolower ($code);
					if ($code !== $appSelect) {
						continue;
					}
					foreach ($optionMenu['elementos'] as $theElement) {
						$element[] = array (
							'tabid'    => $theElement ['id'],
							'name'     => $theElement ['name'],
							'tablabel' => $theElement ['label'],
						);
					}
					
				}
			}
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $element));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'CODE_ELEMENT_FIELD') {
		$tab       = PlatzillaUtils::purify ($_REQUEST, 'tabid');
		$tabName   = PlatzillaUtils::purify ($_REQUEST, 'tabname');
		
		try {
			if(empty ($tabName) || empty($tab)) {
				throw new Exception ('Módulo no encontrado');
			}
			$fieldsTab = array ();
			$fields    = FieldManager::getInstance ($adb)->fetchFieldHeaders ($tabName);
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
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $fieldsTab));
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
			$translatorField  = array ('eventstatus', 'activitytype');
			$moduleTranslator = null;
			if (in_array ($fieldName, $translatorField)) {
				$moduleName       = SystemAlertsHelper::getModuleByFieldName ($adb, $fieldName);
				$moduleTranslator = return_module_language ($current_language, $moduleName);
			}
			
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
	} else if ($function == 'VIEW-TASK') {
		$tab       = PlatzillaUtils::purify ($_REQUEST, 'tabid');
		$tabName   = PlatzillaUtils::purify ($_REQUEST, 'tabname');
		try {
			if(empty ($tabName)) {
				throw new Exception ('Módulo no encontrado');
			}
			$taskModString = return_module_language($current_language,'Calendar');
			$fieldsTab = array ();
			$fields    = FieldManager::getInstance ($adb)->fetchFieldHeaders ('calendar');
			$notAvailable = array_merge (array(2202, 10, 16), LayoutBlockListHelper::N0_IMPORT_FIELD);
			$notFieldName = array('time_start', 'time_end', 'taskstatus', 'sendnotification', 'duration_hours', 'duration_minutes', 'location', 'recurringtype', 'notime','modifiedby', 'categoryid');
			if (!empty($fields)) {
				foreach ($fields as $field) {
					if (in_array ($field->getUiType (), $notAvailable) || in_array ($field->getName (), $notFieldName)) {
						continue;
					}
					$label       = ($field->getLabel () == 'Assigned To') ? 'Asignado a' : $taskModString[ $field->getName () ];
					$fieldsTab[] = array (
						'fieldName'  => $field->getName (),
						'fieldLabel' => $label,
						'uiType'     => $field->getUiType (),
						'fieldType'  => $field->getDataType (),
					);
				}
			}
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => $fieldsTab));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'LOCATION_ALERT') {
		$mode    = PlatzillaUtils::purify ($_REQUEST, 'mode');
		$idAlert  = PlatzillaUtils::purify ($_REQUEST, 'systemAlertId');
		$codeType = PlatzillaUtils::purify ($_REQUEST, 'codeType');
		try {
			$excludedCategories = array ('Marco','Infraestructura','Actividades','Revision','Control','Mejoras');
			$categories         = IndicatorsPanelHelper::getCategories ($excludedCategories);
			$categories ['KR']  = 'KR';
			
			$userList = getUserslist(true);
			if ($mode != 'create') {
				$detailAlert = SystemAlerts::getDetailAlertById ($adb, $idAlert, $codeType);
				if (!empty ($detailAlert)) {
					$userList = str_replace ('value=' . $detailAlert['users_ids'] . '>', 'value=' . $detailAlert['users_ids'] . ' selected="selected">', getUserslist (false));
				}
			}
			
			$smarty       = new vtigerCRM_Smarty ();
			$smarty->assign ('APPLICATIONS', $categories);
			$smarty->assign ('DETAIL_ALERT', (isset($detailAlert)) ? $detailAlert : null);
			$smarty->assign ('IS_INSTANCIA', $isInstance);
			$smarty->assign ('IS_ADMIN', $current_user->is_admin);
			$smarty->assign ('USER_OWER', $userList);
			$smarty->assign ('MODSTRING', $mod_strings);
			$htmlOutput = $smarty->fetch ('modules/systemalerts/Wizard/LocateAlert.tpl');
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
	} else if ($function == 'SOURCE_ALERT') {
		$mode     = PlatzillaUtils::purify ($_REQUEST, 'mode');
		$idAlert  = PlatzillaUtils::purify ($_REQUEST, 'systemAlertId');
		$codeType = PlatzillaUtils::purify ($_REQUEST, 'codeType');
		$scale    = PlatzillaUtils::purify ($_REQUEST, 'scale', '');
		try {
			if ($mode != 'create') {
				$detailAlert   = SystemAlerts::getDetailAlertById ($adb, $idAlert, $codeType, $scale);
				if (!empty($detailAlert) && in_array ($detailAlert['source_alert'], array('Task_object_no_cump', 'Task_prog'))) {
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
				} else if (!empty($detailAlert) && $detailAlert['source_alert'] == 'Indicators') {
					$detailAlert ['element'] = SystemAlertsHelper::getFieldElementIndicators ($adb, $detailAlert['code_app'], $detailAlert['scale']);
				}
			}
			$smarty       = new vtigerCRM_Smarty ();
			$smarty->assign ('THEME', $theme);
			$smarty->assign ('MODSTRING', $mod_strings);
			$smarty->assign ('DETAIL_ALERT', (isset($detailAlert)) ? $detailAlert : array());
			$htmlOutput = $smarty->fetch ('modules/systemalerts/Wizard/AlertSource.tpl');
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
	} else if ($function == 'FILTER_ALERT') {
		$mode       = PlatzillaUtils::purify ($_REQUEST, 'mode');
		$idAlert    = PlatzillaUtils::purify ($_REQUEST, 'systemAlertId');
		$codeType   = PlatzillaUtils::purify ($_REQUEST, 'codeType');
		$moduleName = PlatzillaUtils::purify ($_REQUEST, 'flmodule');
		try {
			$notFieldName = array();
			if ($codeType == 'Task_prog') {
				$moduleName   = 'Calendar';
				$notFieldName = array ('time_start', 'time_end', 'taskstatus', 'sendnotification', 'duration_hours', 'duration_minutes', 'location', 'recurringtype', 'notime','modifiedby', 'categoryid');
			}
			$smarty = new vtigerCRM_Smarty ();
			if ($mode != 'create') {
				$systemAlertId = PlatzillaUtils::purify ($_REQUEST, 'systemAlertId');
				$detailAlert   = SystemAlerts::getDetailAlertById ($adb, $systemAlertId, $codeType);
				if (!empty($detailAlert) && in_array ($detailAlert['source_alert'], array('Task_object_no_cump', 'Task_prog'))) {
					$moduleFilter = ($detailAlert['source_alert'] == 'Task_prog') ? 'calendar' : $detailAlert['tab_name'];
					$alertFilters  = SystemAlertFilterUtils::getInstance ($adb)->fetchConditionGroups ($idAlert, $moduleFilter);
					$totalArletFilter = count($alertFilters);
					if (!empty($alertFilters)) {
						$objField = FieldManager::getInstance ($adb);
						$alertValues = array();
						for ($k = 0; $k < $totalArletFilter; $k++) {
							foreach ($alertFilters[$k]->getFilters() as $taskFilter) {
								$fieldFilter = $objField->fetchFieldByName ($taskFilter->getModuleName(), $taskFilter->getFieldName());
								if (in_array ($fieldFilter->getUiType (), array (Field::UI_TYPE_MULTI_SELECT, Field::UI_TYPE_PICKLIST))) {
									$smarty->assign ('MOD', return_module_language ($current_language, $taskFilter->getModuleName()));
									$smarty->assign ('PICKLIST_VALUES', $fieldFilter->getPicklist());
									$smarty->assign ('VALUE', $taskFilter->getValue ());
									$alertValues[$taskFilter->getFieldName()] = $smarty->fetch ('utils/HTMLPickListOptions.tpl');
								} else if ($fieldFilter->getUiType () == FieldInterface::UI_TYPE_PIPELINE) {
									$smarty->assign ('MOD', return_module_language ($current_language, $taskFilter->getModuleName()));
									$smarty->assign ('PICKLIST_VALUES', $fieldFilter->getPipeline());
									$smarty->assign ('VALUE', $taskFilter->getValue ());
									$alertValues[$taskFilter->getFieldName()] = $smarty->fetch ('utils/HTMLPickListOptions.tpl');
								} else if ($fieldFilter->getUiType () == FieldInterface::UI_TYPE_CHECKBOX) {
									$smarty->assign ('AVAILABLE_OPTION', array('1' => 'Si', '0' => 'No'));
									$smarty->assign ('SELECTED_VALUE', $taskFilter->getValue ());
									$alertValues[$taskFilter->getFieldName()] = $smarty->fetch ('modules/systemalerts/Wizard/HTMLSelectAlertOptions.tpl');
								} else if ($fieldFilter->getUiType () == FieldInterface::UI_TYPE_DATE || $fieldFilter->getUiType () == FieldInterface::UI_TYPE_DATETIME) {
									$smarty->assign ('AVAILABLE_OPTION', SystemAlertsHelper::DATE_FIELD_OPTIONS);
									$smarty->assign ('SELECTED_VALUE', $taskFilter->getValue ());
									$alertValues[$taskFilter->getFieldName()] = $smarty->fetch ('modules/systemalerts/Wizard/HTMLSelectAlertOptions.tpl');
								} else if ($fieldFilter->getUiType () == FieldInterface::UI_TYPE_OWNER) {
									$alertValues[$taskFilter->getFieldName()] = str_replace ('value=' . $detailAlert['users_ids'] . '>', 'value=' . $detailAlert['users_ids'] . ' selected="selected">', getUserslist (false));
								} else {
									$alertValues[$taskFilter->getFieldName()] = null;
								}
							}
						}
					}
				} else if (!empty($detailAlert) && $detailAlert['source_alert'] == 'Indicators') {
					$detailAlert ['element'] = SystemAlertsHelper::getFieldElementIndicators ($adb, $detailAlert['code_app'], $detailAlert['scale']);
				}
			}
			$notAvailableUiType = array_merge (array(2202, 10), LayoutBlockListHelper::N0_IMPORT_FIELD);
			$moduleObject = ModuleManager::getInstance ($adb)->fetchModule ($moduleName, false);
			if (empty ($moduleObject)) {
				throw new Exception ('El módulo solicitado no está registrado');
			}
			
			$sortFieldByLabelFunction = function (Field $fieldA, Field $fieldB) {
				return strcmp ($fieldA->getLabel (), $fieldB->getLabel ());
			};
			
			$smarty->assign ('THEME', $theme);
			$smarty->assign ('MODSTRING', $mod_strings);
			$smarty->assign ('AVAILABLE_FILTER_ALERT', (isset($alertFilters) ? $alertFilters : null));
			$smarty->assign ('AVAILABLE_DATE_OPTION', SystemAlertsHelper::DATE_FIELD_OPTIONS);
			$smarty->assign ('MODULE', $moduleObject);
			$smarty->assign ('NO_AVAIABLE_UITYPE', $notAvailableUiType);
			$smarty->assign ('NO_AVAIABLE_FIELDNAME', $notFieldName);
			$smarty->assign ('OPTION_VALUES', isset($alertValues) ? $alertValues : null);
			$smarty->assign ('SORT_BY_LABEL_FUNCTION', $sortFieldByLabelFunction);
			$htmlOutput = $smarty->fetch ('modules/systemalerts/Wizard/AlertFilter.tpl');
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
	} else if ($function == 'CHANGE_STATUS') {
		$status   = intval (PlatzillaUtils::purify ($_REQUEST, 'status'));
		$recordId = PlatzillaUtils::purify ($_REQUEST, 'record');
		try {
			if (!is_numeric ($status) || empty ($recordId)) {
				throw new Exception ('Uoops! Faltó información');
			}
			
			$status = ($status == 1) ? 0 : 1;
			SystemAlertsHelper::changeStatusAlert ($adb, intval ($recordId), $moduleName);
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	} else if ($function == 'LOOK-ALERT') {
		$recordId = PlatzillaUtils::purify ($_REQUEST, 'record');
		$from     = PlatzillaUtils::purify ($_REQUEST, 'date_from');
		$to       = PlatzillaUtils::purify ($_REQUEST, 'date_to');
		$idOcurrence = PlatzillaUtils::purify ($_REQUEST, 'idOcurrence');
		try {
			if (empty ($idOcurrence) || empty($from) || empty($to)) {
				throw new Exception ('Uoops! Faltó información');
			}
			
			SystemAlertsHelper::setLookedAlert ($adb, $current_user->id, $idOcurrence, $from, $to);
			
			
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => 'OK', 'html' => 'La alerta fue marcada como descartada!'));
		} catch (Exception $e) {
			header ('Access-Control-Allow-Origin: *');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json; charset=utf-8');
			echo json_encode(array('error' => $e->getMessage()));
		}
	}
	
	exit();

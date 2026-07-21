<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/Settings/lib/GetFieldPropertiesHelper.class.php');
	require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
	require_once ('modules/Settings/lib/LayoutBlockListHelper.class.php');
	require_once ('modules/Settings/lib/WizardUtils.class.php');
	require_once ('modules/Settings/lib/SaveFieldPropertiesHelper.class.php');
	
	global $adb, $app_strings, $current_user, $current_module, $current_language, $mod_strings, $site_URL, $theme;
	
	setBugSnag ($site_URL);
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	
	if ($function == 'SHOW_FIELD_HELP') {
		try {
			$helpFieldId = PlatzillaUtils::purify ($_GET, 'record');
			$moduleName  = PlatzillaUtils::purify ($_GET, 'module');
			
			if (empty($helpFieldId) || !is_numeric ($helpFieldId)) {
				throw new Exception('Inposible mostrar ayuda');
			}
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			
			$helpField = HelpSettingsHelper::fetchHelpFieldById ($masterAdb, $helpFieldId, $moduleName, $adb);
			if (empty ($helpField)) {
				throw new Exception('Información de ayuda no encontrada');
			} else if (empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			}
			if ($helpField->isEditable () == 'YES') {
				$moduleObject = ModuleManager::getInstance ($adb)->fetchModule ($helpField->getModuleName (), false);
				if (empty ($moduleObject)) {
					throw new Exception('El módulo solicitado para la ayuda no está registrado');
				}
				$fieldObject = FieldManager::getInstance ($adb)->fetchFieldById ($helpField->getFieldId ());
				if ($fieldObject->getUiType () == FieldInterface::UI_TYPE_PICKLIST) {
					$pickListData      = PicklistManager::getInstance ($adb)->fetchPicklistByName ($fieldObject->getName (), $isInstance);
					$pickListRelations = GetFieldPropertiesHelper::getPicklistRelationship ($adb, $moduleName, $fieldObject->getName ());
				}
			}
			
			$smarty = new vtigerCRM_Smarty();
			
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('BLOCKS_ID', LayoutBlockListHelper::getCustomBlock($adb, $moduleName)->getId ());
			$smarty->assign ('FIELD_OBJECT', (isset($fieldObject)) ? $fieldObject : null);
			$smarty->assign ('HELP_FIELD', $helpField);
			$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
			$smarty->assign ('MODULE', (isset($moduleObject)) ? $moduleObject : null);
			$smarty->assign ('PICK_LIST_DATA', (isset($pickListData)) ? $pickListData : null);
			$smarty->assign ('PICK_LIST_RELATED', (isset($pickListRelations)) ? $pickListRelations : null);
			$smarty->assign ('Z_MODAL', 'zmodal-maximo');
		} catch (Exception $e) {
			$smarty->assign ('IS_ERROR', true);
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
		}
		$smarty->display ('Settings/HelpSystem/HelpSystemFields.tpl');
	} else if ($function == 'ADD_FIELD_HELP') {
		try {
			$mod_strings = return_module_language ($current_language, 'Settings');
			$moduleName = PlatzillaUtils::purify ($_GET, 'module');
			$idHelp     = PlatzillaUtils::purify ($_GET, 'idhelp');
			if (empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			} else if (empty($idHelp)) {
				throw new Exception ('Error al cargar ventana modal');
			}
			
			$entityModules      = LayoutBlockListHelper::getEntityModules ($adb, $moduleName);
			$entityModulesName  = (count ($entityModules)) ? array_column ($entityModules,'name') : array ();
			$calculatedFields   = new CalculatedFieldsUtils ($adb, $_SESSION ['plat']);
			$calculatedSystems  = $calculatedFields->getAllCalculateSystem ($current_user);
			usort (
				$calculatedSystems,
				function (CalculationSystem $calculatedSystemA, CalculationSystem $calculatedSystemB) {
					return strcmp (
						$calculatedSystemA->getName (),
						$calculatedSystemB->getName ()
					);
				}
			);
			
			$smarty = new vtigerCRM_Smarty();
			$smarty->assign ('AVAILABLE_GLOBAL_PICKLISTS', GlobalPicklistManager::getInstance ($adb)->fetchPicklists ());
			$smarty->assign ('AVAILABLE_ENTITY_MODULES', $entityModules);
			$smarty->assign ('BLOCKS_ID', LayoutBlockListHelper::getCustomBlock($adb, $moduleName)->getId ());
			$smarty->assign ('APP', $app_strings);
			$smarty->assign ('CALCULATED_SYSTEMS', $calculatedSystems);
			$smarty->assign ('FIELD_OBJECT', Field::getInstance ());
			$smarty->assign ('FIELD_TYPE_OPTIONS', WizardUtils::getFieldTypesAsOptions ());
			$smarty->assign ('FIELDS_VISIBILITY', $fieldsVisibility);
			$smarty->assign ('HELP_id', $idHelp);
			$smarty->assign ('IS_INSTANCE', $isInstance);
			$smarty->assign ('MOD', return_module_language ($current_language, 'Settings'));
			$smarty->assign ('MODULE', $moduleName);
		} catch (Exception $e) {
			$smarty->assign ('IS_ERROR', true);
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
		}
		$smarty->display ('Settings/HelpSystem/HelpSystemAddField.tpl');
	} else if ($function == 'SAVE_FIELD_LABEL') {
		try {
			$moduleName = PlatzillaUtils::purify ($_POST, 'modulename');
			$fieldId    = PlatzillaUtils::purify ($_POST, 'fieldid');
			$label      = PlatzillaUtils::purify ($_POST, 'label');
			
			if (empty ($moduleName)) {
				throw new Exception ('No has suministrado el nombre del módulo');
			} else if (empty ($fieldId)) {
				throw new Exception ('No has suministrado el ID del campo');
			} else if (empty ($label)) {
				throw new Exception ('No has suministrado el nombre del campo');
			}
			
			
			$fm    = FieldManager::getInstance ($adb);
			$field = $fm->fetchFieldById ($fieldId);
			if (empty ($field)) {
				throw new Exception ('El campo suministrado no se encuentra registrado');
			} else if ($field->getModuleName () != $moduleName) {
				throw new Exception ('El campo no se encuentra asociado al módulo suministrado');
			}
			
			$field->setLabel ($label)->setLocked (!empty ($_SESSION ['platInstancia']));
			$fm->updateFieldHeader ($field);
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode (array ('error' => 'OK'));
		} catch (Exception $e) {
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode (array ('error' => $e->getMessage ()));
		}
		exit ();
	} else if ($function == 'HIDDEN_FIELDS') {
		try {
			$moduleName = PlatzillaUtils::purify ($_POST, 'module');
			$fieldId    = PlatzillaUtils::purify ($_POST, 'fieldid');
			
			if (empty ($moduleName)) {
				throw new Exception ('No has suministrado el nombre del módulo');
			} else if (empty ($fieldId)) {
				throw new Exception ('No has suministrado el ID del campo');
			}
			
			SaveFieldPropertiesHelper::setVisibilityAllProfiles ($adb, $fieldId, $moduleName);
			
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode (array ('error' => 'OK'));
		} catch (Exception $e) {
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode (array ('error' => $e->getMessage ()));
		}
		exit ();
	} else if ($function == 'MANDATORY_FIELDS') {
		try {
			$moduleName     = PlatzillaUtils::purify ($_POST, 'module');
			$fieldName      = PlatzillaUtils::purify ($_POST, 'fieldName');
			$mandatoryfield = PlatzillaUtils::purify ($_POST, 'mandatory');
			
			if (empty ($moduleName)) {
				throw new Exception ('No has suministrado el nombre del módulo');
			} else if (empty ($fieldName)) {
				throw new Exception ('No has suministrado el ID del campo');
			}
			$fm            = FieldManager::getInstance ($adb);
			$selectedField = $fm->fetchFieldByName ($moduleName, $fieldName);
			if (empty ($selectedField)) {
				throw new Exception ("El campo {$fieldName} no se encuentra registrado en el módulo {$moduleName}");
			}
			$isInstance = !empty ($_SESSION ['platInstancia']) ? true : false;
			$uiType     = $selectedField->getUiType ();
			if (!in_array ($uiType, array (FieldInterface::UI_TYPE_CODE, FieldInterface::UI_TYPE_CREATED_TIME, FieldInterface::UI_TYPE_OWNER))) {
				$dummy = $mandatoryfield ? false : true;
				$selectedField->setMandatory ($dummy);
				$selectedField->setLocked ($isInstance);
				$fm->saveField ($selectedField);
			} else {
				throw new Exception ("Imposible quitar obligatoriedad al campo {$fieldName}");
			}
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode (array ('error' => 'OK'));
		} catch (Exception $e) {
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode (array ('error' => $e->getMessage ()));
		}
		exit ();
	} else if ($function == 'UPDATE_PICK_LIST') {
		try {
			$moduleName       = PlatzillaUtils::purify ($_POST, 'module');
			$fieldName        = PlatzillaUtils::purify ($_POST, 'fieldName');
			$pickListValues    = PlatzillaUtils::purify ($_POST, 'picklist');
			$pickListSequence = PlatzillaUtils::purify ($_POST, 'sequence');
			
			if (!count($pickListValues) || !count ($pickListSequence)) {
				throw new Exception ('Upoos! valores de lista no encontrados ');
			} else if (empty ($moduleName)) {
				throw new Exception ('No has suministrado el nombre del módulo');
			} else if (empty ($fieldName)) {
				throw new Exception ('No has suministrado el campo lista');
			}
			$fm            = FieldManager::getInstance ($adb);
			$selectedField = $fm->fetchFieldByName ($moduleName, $fieldName);
			$pickListCurrent = PicklistManager::getInstance ($adb)->fetchPicklistByName ($fieldName, $isInstance);
			
			if (empty($selectedField)) {
				throw new Exception ('Campo lista no encontrado');
			} else if (empty ($pickListCurrent)) {
				throw new Exception ('Lista no encontrada');
			}
			
			$currentValues     = array ();
			$oldPicklistValues = $pickListCurrent->getValues ();
			foreach ($oldPicklistValues as $oldPicklistValue) {
				$currentValues [] = $oldPicklistValue->getValue();
			}
			
			$totalValue        = count ($pickListSequence);
			$newPicklistValues = array ();
			$changedValues     = array ();
			foreach ($pickListValues as $pickListValue) {
				list ($newValue, $newId)  = explode ('@', $pickListValue);
				$changedValues []         = $newValue;
				$selectedOldPicklistValue = null;
				if (!empty ($oldPicklistValues)) {
					foreach ($oldPicklistValues as $oldPicklistValue) {
						if ($oldPicklistValue->getId () == $newId) {
							$selectedOldPicklistValue = $oldPicklistValue;
							break;
						}
					}
				}
				if (!empty ($selectedOldPicklistValue)) {
					if (
						$isInstance &&
						(($oldPicklistValue->isLocked ()) || ($oldPicklistValue->getValue () != $newValue) || (!SaveFieldPropertiesHelper::areRolesEqual ($selectedOldPicklistValue->getRoles (),null)))
					) {
						$isLocked = true;
					} else {
						$isLocked = false;
					}
					$newPicklistValues [] = $oldPicklistValue
						->setLocked ($isLocked)
						->setValue ($newValue);
				} else {
					$newPicklistValues [] = PicklistValue::getInstance (true)
						->setLocked ($isInstance)
						->setValue ($newValue);
				}
			}
			$diffChangedValues = array_values (array_diff ($changedValues, $currentValues));
			$diffCurrentValues = array_values (array_diff ($currentValues, $changedValues));
			if (count ($diffChangedValues) && count ($diffCurrentValues)) {
				SaveFieldPropertiesHelper::updatePickListValues ($adb, $diffChangedValues, $diffCurrentValues, $fieldName, $selectedField->getTableName());
			}
			$picklist = $selectedField->getPicklist ();
			$picklist->setValues ($newPicklistValues);
			$selectedField->setPicklist ($picklist);
			$fm->saveField ($selectedField);
			
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode (array ('error' => 'OK'));
		} catch (Exception $e) {
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode (array ('error' => $e->getMessage ()));
		}
		exit ();
	}

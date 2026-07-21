<?php
    require_once ('data/CRMEntity.php');
    require_once ('include/platzilla/Data/ActivityReportManager.php');
    require_once ('include/platzilla/Managers/ModuleManager.php');
    require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/AttachmentsUtils.class.php');
    require_once ('include/utils/CommonUtils.php');
    require_once ('include/utils/DataViewUtils.php');
	require_once ('include/utils/EditViewUtils.class.php');
	require_once ('include/utils/NumberHelper.class.php');
    require_once ('include/utils/PlatformUtils.class.php');
    require_once ('include/utils/PlatzillaUtils.class.php');
    require_once ('modules/business_initiatives/handlers/ResourceToInitiative.class.php');
    require_once ('modules/calculated_fields/CalculatedFields.class.php');
    require_once ('modules/Settings/lib/GetFieldPropertiesHelper.class.php');
    require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
    require_once ('modules/Settings/lib/HowToHelper.class.php');
    require_once ('modules/Settings/lib/LayoutBlockListHelper.class.php');
    require_once ('modules/Settings/lib/SaveFieldPropertiesHelper.class.php');
    require_once ('modules/Settings/lib/WizardUtils.class.php');
	
	global $adb, $app_strings, $current_user, $current_module, $current_language, $mod_strings, $site_URL, $theme;
	
	// it does not use
	//setBugSnag ($site_URL);
	
	$function   = PlatzillaUtils::purify ($_REQUEST, 'function');
	$isInstance = !empty ($_SESSION ['platInstancia']);
	
	if ($function == 'ADD_FIELD_HELP') {
		try {
			$mod_strings = return_module_language ($current_language, 'Settings');
			$moduleName = PlatzillaUtils::purify ($_GET, 'module');
			$idHelp = PlatzillaUtils::purify ($_GET, 'idhelp');
			if (empty ($moduleName)) {
				throw new Exception ('Módulo no encontrado');
			} else if (empty($idHelp)) {
				throw new Exception ('Error al cargar ventana modal');
			}
			
			$entityModules = LayoutBlockListHelper::getEntityModules ($adb, $moduleName);
			$entityModulesName = (count ($entityModules)) ? array_column ($entityModules, 'name') : array();
			$calculatedFields = new CalculatedFieldsUtils ($adb, $_SESSION ['plat']);
			$calculatedSystems = $calculatedFields->getAllCalculateSystem ($current_user);
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
			$smarty->assign ('BLOCKS_ID', LayoutBlockListHelper::getCustomBlock ($adb, $moduleName)->getId ());
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
	} else if ($function == 'ATTACHMENT_DOC') {
		$moduleName = PlatzillaUtils::purify ($_GET, 'formodule');
		$record     = PlatzillaUtils::purify ($_GET, 'record');
		$reportId   = PlatzillaUtils::purify ($_GET, 'reportId', 0);
		try {
			if (empty ($moduleName)) {
				throw new Exception ('No has seleccionado un módulo');
			}
		
			if (empty ($record)) {
					throw new Exception ('No has seleccionado un registro');
			}
		
			$smarty = new vtigerCRM_Smarty ();
			// Si hay reportId, cargar adjuntos del reporte específico
			if (!empty($reportId)) {
				$smarty->assign ('ENTITY_ATTACHMENTS', AttachmentsUtils::fetchActivityReportAttachments ($adb, $reportId));
			} else {
				$smarty->assign ('ENTITY_ATTACHMENTS', AttachmentsUtils::fetchEntityAttachments ($adb, $record));
			}
			$smarty->assign ('MODULE', $moduleName);
			$smarty->assign ('RECORD', $record);
			$smarty->assign ('REPORT_ID', $reportId);
			$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
			$smarty->display ('modules/DailyReport/Objects/daily_report_attachment.tpl');
		} catch (Exception $e) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('IS_ERROR', true);
			$smarty->display ('modules/DailyReport/Objects/daily_report_attachment.tpl');
		}
	} else if ($function == 'DAILY_TASK_MATRIX') {
		try {
			$idRow    = PlatzillaUtils::purify ($_GET, 'rowid');
			$isAction  = PlatzillaUtils::purify ($_GET, 'is_action', null);
			$period    = PlatzillaUtils::purify ($_GET, 'period');
			$tasksView = DataViewUtils::fetchView ($adb, 'Calendar', 'ALL');
			
			if (empty ($tasksView)) {
				throw new Exception ('La vista solicitada no se encuentra registrada');
			}
			$periodDates ['startdate'] = $period;
			$periodDates ['enddate']   = $period;
			$users                     = array ($current_user->id);
				
			$tasksViewPermissions = DataViewUtils::fetchViewPermissions ($adb, $tasksView, $current_user);
			$tasksData            = DataViewUtils::fetchTaskToMatrix ($adb, $periodDates, $users);
			$activitiesRecords    = array ();
			$modules              = array ();
			$priorityTranslate    = array ('Alto' => 'High', 'Bajo' => 'Low');
			$totalRecords      = count ($tasksData);
			for ($k = 0; $k < $totalRecords; $k++) {
				if ($isAction == 'YES' && $tasksData[ $k ]->getRelatedModule () == 'orden_de_trabajo') {
					continue;
				} else if ($isAction == 'NO' && $tasksData[ $k ]->getRelatedModule () != 'orden_de_trabajo') {
					continue;
				}
				$tasksViewData ['records'][ $k ]['invitee']        = DataViewUtils::fetchInviteesByActivity ($adb, $tasksData[ $k ]->getActivityId (), $current_user->id);
				$tasksViewData ['records'][ $k ]['str_date_start'] = $tasksData[ $k ]->getStartDate ();
				$tasksViewData ['records'][ $k ]['str_due_date']   = $tasksData[ $k ]->getDueDate ();
					
				$thisPriority = $tasksData[ $k ]->getPriority ();
				$tasksViewData ['records'][ $k ]['taskpriority']   = ((!empty ($thisPriority)) && (in_array($thisPriority, array ('Alto', 'Bajo')))) ? $thisPriority : 'Bajo';
				$tasksViewData ['records'][ $k ]['crmid']          = $tasksData[ $k ]->getActivityId ();
				$tasksViewData ['records'][ $k ]['importance']     = $tasksData[ $k ]->getImportance ();
				$tasksViewData ['records'][ $k ]['progress']       = $tasksData[ $k ]->getProgress ();
				$tasksViewData ['records'][ $k ]['related_id']     = $tasksData[ $k ]->getRelatedId ();
				$tasksViewData ['records'][ $k ]['modulename']     = $tasksData[ $k ]->getModuleName ();
				$tasksViewData ['records'][ $k ]['tab_name']       = $tasksData[ $k ]->getRelatedModule ();
				$tasksViewData ['records'][ $k ]['estimated_time'] = $tasksData[ $k ]->getTimeDuration ();
				$tasksViewData ['records'][ $k ]['subject']        = $tasksData[ $k ]->getSubject ();
				$tasksViewData ['records'][ $k ]['planned_task']   = $tasksData[ $k ]->getActivityCondition ();
				$tasksViewData ['records'][ $k ]['description']    = $tasksData[ $k ]->getDescription ();
					
				$quadrant    = $priorityTranslate[$tasksViewData ['records'][ $k ]['taskpriority']] . '-' . $tasksData[ $k ]->getImportance ();
				$parameters  = "{$tasksViewData ['records'][ $k ]['activitytype']};{$priorityTranslate[$tasksViewData ['records'][ $k ]['taskpriority']]};{$tasksViewData ['records'][ $k ]['importance']};{$tasksData[ $k ]->getActivityId ()}";
				$tasksViewData ['records'][ $k ]['parameters'] = $parameters;
				$activitiesRecords[ $quadrant ][] = $tasksViewData ['records'][ $k ];
				if (!in_array ($tasksViewData ['records'][ $k ]['modulename'], $modules)) {
						$modules[] = $tasksViewData ['records'][ $k ]['modulename'];
				}
			}
			$quadrants = array ('High-HIGH', 'Low-HIGH', 'High-LOW', 'Low-LOW');
			asort ($modules);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('QUADRANTS', $quadrants);
			$smarty->assign ('TAB_HOME_ID', rand (1000, 10000));
			$smarty->assign ('TASKS_VIEW_DATA', $activitiesRecords);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('MODULES',$modules);
			$smarty->assign ('PERIOD',$period);
			$smarty->assign ('ROW_ID', $idRow);
			$smarty->display ('modules/DailyReport/DailyReportTaskView.tpl');
		} catch (Exception $e) {
			$code   = $e->getCode ();
		}
	} else if ($function == 'FETCH_JOBS') {
		try {
			$idRow    = PlatzillaUtils::purify ($_GET, 'rowid');
			$period    = PlatzillaUtils::purify ($_GET, 'period');
			$page     = 15;
			// Convertir la fecha del formato de usuario al formato de base de datos
			$periodDB = getValidDBInsertDateValue($period);
			$jobs = EditViewUtils::fetchDailyReportJobs ($adb, $current_user->id, $periodDB);
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('JOBS_VIEW_DATA', $jobs);
			$smarty->assign ('MOD', $mod_strings);
			$smarty->assign ('ROW_ID', $idRow);
			$smarty->assign ('TAB_HOME_ID', rand (1000, 10000));
			$smarty->display ('modules/DailyReport/DailyReportWorkView.tpl');
		} catch (Exception $e) {
			$code   = $e->getCode ();
		}
	} else if ($function == 'DAILY_REPORT_DATA') {
	try {
		$record  = PlatzillaUtils::purify ($_REQUEST, 'record');
		$period  = PlatzillaUtils::purify ($_REQUEST, 'period', null);
		// Convertir la fecha del formato de usuario al formato de base de datos
		$periodDB = getValidDBInsertDateValue($period);
		$periodDates ['startdate'] = $periodDB;
		$periodDates ['enddate']   = $periodDB;
		$reports = ActivityReportManager::getInstance ($adb)->fetchActivityReportByActivityId ($record, $periodDates);
		$numberingHelper = NumberHelper::getInstance ($adb, $current_user);
		
		if (empty($reports)) {
			$taskResult = $adb->pquery(
				'SELECT progress FROM vtiger_activity WHERE activityid = ?',
				array($record)
			);
			if ($adb->num_rows($taskResult) > 0) {
				$taskRow = $adb->fetchByAssoc($taskResult);
				$progress = !empty($taskRow['progress']) ? $taskRow['progress'] : 0;
			} else {
				$progress = 0;
			}
			$progress = $numberingHelper->setNumberFormat($progress);
			$htmlOutput = array('progress' => $progress, 'html' => '', 'time' => 0, 'ids' => '');
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
			exit();
		}
		
		$totalTame       = 0;
		$reportsText     = '';
		$progress        = 0;
		$i               = 1;
		$reportsId       = '';
		foreach ($reports as $report) {
			$theReport    = trim ($report->getReport ());
			$dummy        = explode ('>', $theReport, 2);
			$reportsText .= "<p>R{$i}-{$dummy[1]}<!--{$report->getId ()}-->";
			if (empty ($reportsText)) {
				$reportsId   = $report->getId ();
			} else {
				$reportsId   .= ",{$report->getId ()}";
			}
			$totalTame += $report->getTimeDuration ();
			$progress   = ($report->getProgress () > $progress) ? $report->getProgress () : $progress;
			$i++;
		}
		$progress  = $numberingHelper->setNumberFormat ($progress);
		$totalTame = $numberingHelper->setNumberFormat ($totalTame);
		$htmlOutput = array ('progress' => $progress, 'html' => $reportsText,  'time' => $totalTame, 'ids' => $reportsId);
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
	} catch (Exception $e) {
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode (array ('error' => $e->getMessage ()));
	}
	exit ();
	} else if ($function == 'GET_PROJECT_JOB') {
		try {
			$moduleName = PlatzillaUtils::purify ($_POST, 'module');
			$record     = PlatzillaUtils::purify ($_POST, 'record');
			if (empty ($moduleName)) {
				throw new Exception ('No has suministrado el nombre del módulo');
			} else if (empty ($record)) {
				throw new Exception ('Registro no encontrado');
			}
			
			$entity       = CRMEntity::getInstance ($moduleName);
			$entity->id   = $record;
			$entity->mode = 'edit';
			$entity->retrieve_entity_info ($record, $moduleName);
			if (!empty ($entity->column_fields ['assigned_user_id'])) {
				$entity->column_fields['user_full_name'] = getUserFullName ($entity->column_fields['assigned_user_id']);
			} else {
				$entity->column_fields['user_full_name'] = null;
			}
			if(empty ($entity->column_fields['fecha_prevista']) || $entity->column_fields['fecha_prevista'] == 'null' || $entity->column_fields['fecha_prevista'] == '0000-00-00') {
				$entity->column_fields['fecha_prevista'] = '';
			}
			if(empty ($entity->column_fields['fecha_estim_fin']) || $entity->column_fields['fecha_estim_fin'] == 'null' || $entity->column_fields['fecha_estim_fin'] == '0000-00-00') {
				$entity->column_fields['fecha_estim_fin'] = '';
			}
			
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode(array('error' => 'OK', 'html' => $entity->column_fields));
		} catch (Exception $e) {
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode (array ('error' => $e->getMessage ()));
		}
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
    } else if ($function == 'GET-HOW-TO') {
        $smarty = new vtigerCRM_Smarty ();
        try {
            $record = PlatzillaUtils::purify($_REQUEST, 'record');
            if (empty ($record)) {
                throw new Exception ('Imposible encontar el HowTo');
            }
            $howTo = HowToHelper::fetchHowToById($adb, $record);
            $smarty->assign('HOW_TO', $howTo);
            $smarty->display('DetailViewHowTo.tpl');
        } catch (Exception $e) {
            $smarty->assign('IS_ERROR', true);
            $smarty->assign('LABEL', 'Volver');
            $smarty->assign('MESSAGE', $e->getMessage());
            $smarty->assign('TYPE', 'ERROR');
            $smarty->assign('HOW_TO', null);
            $smarty->display('DetailViewHowTo.tpl');
        }
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
	} else if ($function == 'PROGRESS_FACTOR') {
		try {
			$record     = PlatzillaUtils::purify ($_REQUEST, 'record');
			$moduleName = PlatzillaUtils::purify ($_REQUEST, 'module');
			if (empty ($record)) {
				throw new Exception ('Registro no encontado!');
			}
			
			$entity = CRMEntity::getInstance ($moduleName);
			$entity->retrieve_entity_info ($record, $moduleName);
			$retrieveField = ResourceInterface::MODULES_FACTOR_FIELD[ $moduleName ];
			$factor        = $entity->column_fields [ $retrieveField ];
			if (empty ($factor)) {
				throw new Exception('Factor no determinado!');
			}
			
			$htmlOutput = $factor;
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode(array('error' => 'OK', 'html' => $htmlOutput));
		} catch (Exception $e) {
			header ('HTTP/1.1 200 OK');
			header ('Content-Type: application/json');
			echo json_encode (array ('error' => $e->getMessage ()));
		}
		exit ();
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
	} else if ($function == 'SHOW_FIELD_HELP') {
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
			$fieldObject = FieldManager::getInstance ($adb)->fetchFieldById ($helpField->getFieldId ());
			if ($helpField->isEditable () == 'YES') {
				$moduleObject = ModuleManager::getInstance ($adb)->fetchModule ($helpField->getModuleName (), false);
				if (empty ($moduleObject)) {
					throw new Exception('El módulo solicitado para la ayuda no está registrado');
				}
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
	exit();

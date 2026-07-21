<?php
    require_once ('include/platzilla/Managers/FieldDependencyManager.php');
    require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
    require_once ('include/platzilla/Managers/PicklistRelationshipManager.php');
    require_once ('include/platzilla/Managers/PicklistPipelineRelationshipManager.php');
    require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
    require_once ('include/platzilla/Managers/TableFieldManager.php');
    require_once ('include/platzilla/Objects/NotificationInterface.php');
    require_once ('include/utils/AttachmentsUtils.class.php');
    require_once ('include/utils/CommonUtils.php');
    require_once ('include/utils/EditViewUtils.class.php');
	require_once ('include/utils/ProcessCasesUtils.class.php');
    require_once ('include/utils/PlatformUtils.class.php');
    require_once ('include/utils/PlatzillaUtils.class.php');
    require_once ('include/utils/TableFieldUtils.php');
    require_once ('modules/backgroundtasks/lib/BackgroundTasksRunner.class.php');
    require_once ('modules/notifications/lib/NotificationUtils.class.php');
    require_once ('modules/PickList/DependentPickListUtils.php');
    require_once ('modules/Settings/lib/HelpSettingsHelper.class.php');
    require_once ('modules/Settings/lib/HowToHelper.class.php');
    require_once ('modules/store/lib/StoreUtils.class.php');
    require_once ('Smarty_setup.php');

    // Asegura que el objeto global $smarty esté correctamente instanciado
    global $smarty;
    if (!isset($smarty) || !$smarty instanceof vtigerCRM_Smarty) {
        $smarty = new vtigerCRM_Smarty();
    }

	global $adb, $app_strings, $current_user, $mod_strings, $currentModule, $theme;

	if (!empty ($_SESSION ['platInstancia'])) {
		if (!StoreUtils::isInstanceVerified ($_SESSION ['platInstancia'])) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('MENSAJE', 'Debes verificar tu cuenta!');
			$smarty->display ('instanciaUnverified.tpl');
			exit ();
		}

		$masterAdb          = AdbManager::getInstance ()->getMasterAdb ();
		$subscription       = null;
		$moduleSubscription = null;
		try {
			$psm          = PlatformSubscriptionManager::getInstance ($masterAdb);
			$subscription = $psm->fetchSubscription ($_SESSION ['platInstancia']);
			if ((empty ($subscription)) || ($subscription->getStatus () == PlatformSubscription::STATUS_INACTIVE)) {
				throw new Exception ('Tu suscripción se encuentra inactiva');
			}

			$moduleSubscription = $psm->fetchModuleSubscription ($_SESSION ['platInstancia'], $currentModule);
			if (empty ($moduleSubscription)) {
				throw new Exception ('El módulo no se encuentra instalado. Te invitamos a instalar una aplicación que lo contenga');
			} else if ($moduleSubscription->getStatus () == ModuleSubscription::STATUS_INACTIVE) {
				throw new Exception ('El módulo se encuentra vencido. Te invitamos a renovar el servicio');
			}
		} catch (Exception $e) {
			$smarty = new vtigerCRM_Smarty ();
			$smarty->assign ('LABEL', 'Tu suscripción');
			$smarty->assign ('MESSAGE', $e->getMessage ());
			$smarty->assign ('TYPE', 'ERROR');
			$smarty->assign ('URL', 'index.php?module=Home&action=ViewSubscriptionDetails&tab=subscription');
			$smarty->display ('Message.tpl');
			exit ();
		}

		$applications     = PlatformUtils::getApplicationsByUserRole ($adb, $current_user->column_fields ['roleid'], $currentModule);
		$canCreateRecords = ($moduleSubscription->getMaxRecords () == -1) || ($moduleSubscription->getMaxRecords () > $moduleSubscription->getTotalRecords ());
	} else {
		$applications     = PlatformUtils::getApplicationsByModuleName ($adb, $currentModule);
		$canCreateRecords = true;
	}

	$createMode     = PlatzillaUtils::purify ($_REQUEST, 'createmode');
	$isduplicate    = PlatzillaUtils::purify ($_REQUEST, 'isDuplicate');
	$mode           = isset ($_REQUEST ['mode']) ? vtlib_purify ($_REQUEST ['mode']) : null;
	$profileIds     = PlatzillaUtils::purify ($_REQUEST, 'profileids');
	$record         = PlatzillaUtils::purify ($_REQUEST, 'record');
	$returnAction   = PlatzillaUtils::purify ($_REQUEST, 'return_action');
	$returnId       = PlatzillaUtils::purify ($_REQUEST, 'return_id');
	$returnModule   = PlatzillaUtils::purify ($_REQUEST, 'return_module');
	$returnViewName = PlatzillaUtils::purify ($_REQUEST, 'return_viewname');
	$returnTab      = (!empty ($_REQUEST ['tab'])) ? vtlib_purify ($_REQUEST ['tab']) : null;
	$relationId     = (!empty ($_REQUEST ['relationid'])) ? vtlib_purify ($_REQUEST ['relationid']) : null;
	$caseNumber     = (isset ($_REQUEST ['case_number'])) ? vtlib_purify ($_REQUEST ['case_number']) : '';
	
	$profileIds                = !empty ($profileIds) ? explode (',', $profileIds) : null;
	$modulecfId                = '';
	$entityIdentifierFieldName = null;
	/** @var CRMEntity|stdClass $focus */
	$focus = CRMEntity::getInstance ($currentModule);
	if ($record) {
		$focus->id   = $record;
		$focus->mode = 'edit';
		$focus->retrieve_entity_info ($record, $currentModule);
		
		foreach ($focus->column_fields as $fieldName => $fieldValue) {
			if (preg_match('/fecha|date|task_work/i', $fieldName)) {
				$displayValue = is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue;
			}
		}
		
		$caseNumber  = (empty($caseNumber)) ? $focus->case_number : $caseNumber;
		$caseId      = null;
		$modulecfId    = $record;
		$oldDieOnError = $adb->dieOnError;
		$adb->setDieOnError (false);
		BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('EDIT', BackgroundTaskInterface::EVENT_INSTANT_BEFORE, $focus);
		$adb->setDieOnError ($oldDieOnError);
		$moduleHeaders             = ModuleManager::getInstance ($adb)->fetchModule ($currentModule, true);
		$entityIdentifierFieldName = $moduleHeaders->getFieldIdentifier();
		$tableFieldData            = TableFieldUtils::getInstance ($adb)->fetchDataTableField ($currentModule, $record);
		
		// AJUSTAR FECHAS EN DUPLICACIÓN (debe estar aquí, después de cargar tableFieldData)
		if ($isduplicate == 'true') {			
			// Recolectar todas las fechas de campos estándar
			$allDates = array();
			$systemFields = array('createdtime', 'modifiedtime', 'created_user_id', 'modifiedby');
			
			foreach ($focus->column_fields as $fieldName => $fieldValue) {
				if (!empty($fieldValue) && !in_array($fieldName, $systemFields) && 
				    preg_match('/^\d{4}-\d{2}-\d{2}/', $fieldValue)) {
					$allDates[$fieldName] = $fieldValue;
				}
			}
			
			// Recolectar fechas de tableFieldData (campos de tabla personalizados)
			$tableFieldDates = array();
			if (!empty($tableFieldData)) {
				foreach ($tableFieldData as $tableFieldName => $tableRows) {
					if (is_array($tableRows)) {
						foreach ($tableRows as $rowIndex => $row) {
							if (is_array($row)) {
								foreach ($row as $colName => $colValue) {
									// Buscar columnas de fecha
									if (!empty($colValue) && preg_match('/^\d{4}-\d{2}-\d{2}/', $colValue)) {
										$tableFieldDates["{$tableFieldName}[{$rowIndex}][{$colName}]"] = $colValue;
									}
								}
							}
						}
					}
				}
			}
			
			// CALCULAR FECHA BASE GLOBAL (incluyendo fechas de tareas que se procesarán después)
			// Primero necesitamos obtener las fechas de las tareas para incluirlas en el cálculo de fecha base
			$taskDates = array();
			if ($currentModule == 'orden_de_trabajo' && !empty($record)) {
				// Obtener fechas de tareas directamente de la BD
				$taskResult = $adb->pquery(
					'SELECT act.date_start, act.due_date, act.subject
					 FROM vtiger_activity act
					 INNER JOIN vtiger_seactivityrel sar ON sar.activityid = act.activityid
					 INNER JOIN vtiger_crmentity crm ON crm.crmid = act.activityid AND crm.deleted = 0
					 WHERE sar.crmid = ? AND act.activitytype <> ?
					 ORDER BY act.date_start ASC',
					array($record, 'Job')
				);
				while ($taskRow = $adb->fetchByAssoc($taskResult)) {
					if (!empty($taskRow['date_start']) && $taskRow['date_start'] !== '0000-00-00') {
						$taskDates['tarea_' . $taskRow['subject'] . '_inicio'] = $taskRow['date_start'];
					}
					if (!empty($taskRow['due_date']) && $taskRow['due_date'] !== '0000-00-00') {
						$taskDates['tarea_' . $taskRow['subject'] . '_fin'] = $taskRow['due_date'];
					}
				}
			}
			
			// CALCULAR Y AJUSTAR FECHAS
			$allDatesToAdjust = array_merge($allDates, $tableFieldDates, $taskDates);
			if (!empty($allDatesToAdjust)) {
				
				// Encontrar la fecha más antigua (fecha base GLOBAL)
				$minDate = null;
				$minDateField = null;
				foreach ($allDatesToAdjust as $fieldName => $dateValue) {
					$dateOnly = substr($dateValue, 0, 10);
					if ($minDate === null || $dateOnly < $minDate) {
						$minDate = $dateOnly;
						$minDateField = $fieldName;
					}
				}
				
				// Guardar fecha base global en REQUEST para que taskToWork la use
				$_REQUEST['_global_base_date'] = $minDate;
								
				// Calcular diferencias y nuevas fechas
				$baseDateTime = new DateTime($minDate);
				$todayDateTime = new DateTime(date('Y-m-d'));
				$today = date('Y-m-d');
				
				foreach ($allDatesToAdjust as $fieldName => $dateValue) {
					$dateOnly = substr($dateValue, 0, 10);
					$originalDateTime = new DateTime($dateOnly);
					
					$diffFromBase = $baseDateTime->diff($originalDateTime)->days;
					$isAfterBase = ($originalDateTime >= $baseDateTime);
					
					$newDateTime = clone $todayDateTime;
					if ($isAfterBase) {
						$newDateTime->modify("+{$diffFromBase} days");
					} else {
						$newDateTime->modify("-{$diffFromBase} days");
					}
					$newDate = $newDateTime->format('Y-m-d');
					
					// Actualizar el campo
					if (preg_match('/^(.+)\[(\d+)\]\[(.+)\]$/', $fieldName, $matches)) {
						// Es una fecha de campo de tabla
						$tableName = $matches[1];
						$rowIndex = $matches[2];
						$colName = $matches[3];
						if (isset($tableFieldData[$tableName][$rowIndex][$colName])) {
							$tableFieldData[$tableName][$rowIndex][$colName] = $newDate;
						}
					} else {
						// Es un campo estándar
						$focus->column_fields[$fieldName] = $newDate;
					}
				}
				
			}
		}
		
	} else if (!empty($relationId) && !empty($returnModule) && !empty($returnId)) {
		$relatedFields = ModuleRelationshipManager::getInstance ($adb)->fetchRelationFieldById ($relationId);
		if (!empty ($relatedFields)) {
			$importFields  = $relatedFields->getFieldImport ();
			$relatedModule = CRMEntity::getInstance ($returnModule);
			$relatedModule->retrieve_entity_info ($returnId, $returnModule);
			foreach ($importFields as $fieldName => $data) {
				if ($data[0] == 'FIELD') {
					$focus->column_fields[$fieldName] = $relatedModule->column_fields[$data[1]];
				} else if ($data[0] == 'LIST') {
					$focus->column_fields[$fieldName] = $data[1];
				} else if ($data[0] == 'GRID') {
					$originData = array (
						'module'            => $returnModule,
						'record'            => $returnId,
						'destination_field' => $fieldName,
						'origin_field'      => $data [1],
					);
					$originGrid = escribeCamposGrid ($currentModule, $record, false, $originData);
				} else if ($data[0] == 'CHECK') {
					$focus->column_fields[$fieldName] = $data[1];
				} else if ($data[0] == 'DATE') {
					if ($data[1] == 'CREATED-DATE') {
						$focus->column_fields[$fieldName] = date_create ()->format ('Y-m-d');
					} else if ($data[1] == 'NEXT-WEEK') {
						$focus->column_fields[$fieldName] = date_create ()->modify ('7 day')->format ('Y-m-d');
					} else if ($data[1] == 'NEXT-MONTH') {
						$focus->column_fields[$fieldName] = date_create ()->modify ('1 month')->format ('Y-m-d');
					} else {
						$focus->column_fields[$fieldName] = $relatedModule->column_fields[$data[1]];
					}
				}
			}
		}
	}
	if (method_exists ($focus,'setPreloadData')) {
		$focus->setPreloadData ($current_user, $currentModule, $focus);
	}
	if ($mode == 'create' && !empty($caseNumber)) {
		$caseId = ProcessCasesUtils::getLastCaseId ($adb, $caseNumber);
	}
	if ($isduplicate == 'true') {
		$focus->id   = '';
		$focus->mode = '';
	}
	if ((empty ($record)) && ($focus->mode != 'edit')) {
		setObjectValuesFromRequest ($focus);
	}

	$displayView         = $focus->mode == 'edit' ? 'edit_view' : 'create_view';
	$currentModuleId     = getTabid ($currentModule);
	$validationData      = getDBValidationData ($focus->tab_name, $currentModuleId);
	$validationArray     = EditViewUtils::splitValidationData ($validationData);
	$moduleSequenceField = getModuleSequenceField ($currentModule);
	$swDetailViewGrid    = false;

	if (($focus->mode == 'edit') || ($isduplicate)) {
		$recordName = array_values (getEntityName ($currentModule, $record));
		$recordName = $recordName [0];
	} else {
		$recordName = null;
	}

	if (($focus->mode != 'edit') && ($moduleSequenceField != null)) {
		$autostr        = getTranslatedString ('MSG_AUTO_GEN_ON_SAVE');
		$mod_seq_string = $adb->pquery ('SELECT prefix, cur_id FROM vtiger_modentity_num WHERE semodule=? AND active=1', array ($currentModule));
		$mod_seq_prefix = $adb->query_result ($mod_seq_string, 0, 'prefix');
		$mod_seq_no     = $adb->query_result ($mod_seq_string, 0, 'cur_id');
	} else {
		$autostr        = null;
		$mod_seq_string = null;
		$mod_seq_prefix = null;
		$mod_seq_no     = null;
	}

	// Obtener relaciones Picklist->Pipeline del módulo (tabla vtiger_picklist2pipeline)
	// Se usan para filtrar dinámicamente los valores del pipeline según el valor del picklist madre
	$picklistPipelineRelationshipsRaw = PicklistPipelineRelationshipManager::getInstance ($adb)->fetchPicklistPipelineRelationshipByModule ($currentModule);
	$picklistPipelineRelationships    = array ();
	if (!empty ($picklistPipelineRelationshipsRaw)) {
		foreach ($picklistPipelineRelationshipsRaw as $rel) {
			$motherField   = $rel ['motherpicklistname'];
			$pipelineField = $rel ['pipelinefieldname'];
			$motherValue   = $rel ['motherlistvalue'];
			$visibleValues = !empty ($rel ['pipelinevaluesvisible']) ? json_decode ($rel ['pipelinevaluesvisible'], true) : array ();
			if (!isset ($picklistPipelineRelationships [ $motherField ])) {
				$picklistPipelineRelationships [ $motherField ] = array ();
			}
			if (!isset ($picklistPipelineRelationships [ $motherField ][ $pipelineField ])) {
				$picklistPipelineRelationships [ $motherField ][ $pipelineField ] = array ();
			}
			$picklistPipelineRelationships [ $motherField ][ $pipelineField ][ $motherValue ] = is_array ($visibleValues) ? $visibleValues : array ();
		}
	}

	$fieldDependencies = FieldDependencyManager::getInstance ($adb)->fetchDependencies ($currentModule);
	if (!empty ($fieldDependencies)) {
		$dependencies = array ();
		foreach ($fieldDependencies as $fieldDependency) {
			$sourceFieldName  = $fieldDependency->getSourceFieldName ();
			$sourceFieldValue = $fieldDependency->getSourceFieldValue ();
			$targetFieldName  = $fieldDependency->getTargetFieldName ();
			if ($sourceFieldName == $targetFieldName) {
				continue;
			}
			$dependencies [ $sourceFieldName ][ $sourceFieldValue ][ $targetFieldName ] = $fieldDependency->getTargetFieldVisibility ();
		}
	} else {
		$dependencies = null;
	}

	$fieldHeaders = FieldManager::getInstance ($adb)->fetchFieldHeaders ($currentModule);
	if (!empty ($fieldHeaders)) {
		$fields = array ();
		foreach ($fieldHeaders as $fieldHeader) {
			$fieldName        = $fieldHeader->getName ();
			$fieldValidations = FieldValidationManager::getInstance ($adb)->fetchValidationsByFieldName ($currentModule, $fieldName);
			if (!empty ($fieldValidations)) {
				$validations = array ();
				foreach ($fieldValidations as $fieldValidation) {
					$initialValue = $fieldValidation->getInitialValue ();
					$maximumValue = $fieldValidation->getMaximumValue ();
					$validations [] = array (
						'initialvalue' => $initialValue == 'today' ? date ('Y-m-d') : $initialValue,
						'maximumvalue' => $maximumValue == 'today' ? date ('Y-m-d') : $maximumValue,
						'modulename'   => $fieldValidation->getModuleName (),
						'tablename'    => $fieldValidation->getTableName (),
						'type'         => $fieldValidation->getType (),
					);
				}
			} else {
				$validations = null;
			}
			$relationship = null;
			if ($fieldHeader->getUiType() == FieldInterface::UI_TYPE_PICKLIST) {
				$picklistRelationship = PicklistRelationshipManager::getInstance($adb)->fetchPicklistRelationshipByModule ($currentModule, $fieldName);
				if (!empty($picklistRelationship)) {
					foreach ($picklistRelationship as $relation) {
						$resultArray ['mother'] = $relation->getMotherPicklistName ();
						$resultArray ['daughter'] = $relation->getDaughterPicklistName ();
						if (!empty ($relation->getPicklistRelationshipMaster())) {
							unset($relationshipValues);
							$isFirstValues      = true;
							foreach ($relation->getPicklistRelationshipMaster() as $relationMaster) {
								if ($isFirstValues) {
									$relationshipValues = $relationMaster->getRelationshipValues ();
									$isFirstValues      = false;
								} else {
									$relationshipValues = array_merge ($relationshipValues, $relationMaster->getRelationshipValues ());
								}
							}
						}

						$resultArray ['values'] = (isset ($relationshipValues)) ? json_encode ($relationshipValues) : null;
						$relationship [] = $resultArray;
						unset($resultArray);
					}
				}
			}
			$fields [ $fieldHeader->getName () ] = array (
				'ismandatory'  => $fieldHeader->isMandatory (),
				'label'        => getTranslatedString ($fieldHeader->getLabel (), $currentModule),
				'uitype'       => intval ($fieldHeader->getUiType ()),
				'validations'  => $validations,
				'relationship' => $relationship,
			);
		}
	} else {
		$fields = null;
	}

	$notificationDataModal = array (
		'module'   => $currentModule,
		'user'     => $current_user,
		'view'     => Notification::EDIT_VIEW,
		'style'    => Notification::STYLE_MODAL,
		'recordId' => $record,
		'mode'     => ($mode) ? $mode : 'edit',
		'platform' => $_SESSION ['plat'],
	);
	
    $howToId = HowToHelper::hasHowTo ($adb, $currentModule, $record, 'EditView');
	$smarty->assign ('ACTIVE_APPLICATIONS', $applications);
	$smarty->assign ('APP', $app_strings);
	$smarty->assign ('APPLICATION_VIEWS_ENABLED', PlatformUtils::areApplicationViewsEnabled ($adb));
	$smarty->assign ('BLOCKS', getBlocks ($currentModule, $displayView, $focus->mode, $focus->column_fields, '', $profileIds));
	$smarty->assign ('CASE_ID', isset($caseId) ? $caseId : null);
	$smarty->assign ('CAMPOS_TIPO_GRID', (isset ($originGrid)) ? $originGrid : escribeCamposGrid ($currentModule, $modulecfId, $swDetailViewGrid));
	$smarty->assign ('CAN_CREATE_RECORDS', $canCreateRecords);
	$smarty->assign ('CATEGORY', getParentTab ());
	$smarty->assign ('CHECK', Button_Check ($currentModule));
	$smarty->assign ('CREATEMODE', $createMode);
	$smarty->assign ('DUPLICATE', $isduplicate);
	$smarty->assign ('ENTITY_IDENTIFIER_VALUE', (!empty($entityIdentifierFieldName)) ? $focus->column_fields [ $entityIdentifierFieldName ] : null);
	$smarty->assign ('FIELD_ATTACHMENTS', AttachmentsUtils::fetchFieldAttachments ($adb, $record, $currentModule));
	$smarty->assign ('FIELD_DEPENDENCIES', $dependencies);
	$smarty->assign ('FIELDS', $fields);
	$smarty->assign ('HELP_ITEMS', HelpSettingsHelper::fetchFieldHelpItems ($applications, $currentModule));
    $smarty->assign ('HOW_TO_ID', (!empty ($howToId)) ? $howToId : null);
	$smarty->assign ('ID', $focus->id);
	// Evaluate modals for both EDIT and CREATE modes
	// CREATE_RECORD modals will show when entering the create form
	// EDIT_RECORD modals will show when entering the edit form
	// SAVE_RECORD modals are evaluated after save in DetailView
	// CANCEL_RECORD modals are evaluated in CancelCreate.php
	$modalId = NotificationUtils::fetchApplicableOnScreenNotificationsModal ($adb, $notificationDataModal);
	$smarty->assign ('ID_NOTIFICATION_MODAL', $modalId);
	$smarty->assign ('IMAGE_PATH', "themes/$theme/images/");
	$smarty->assign ('IS_ADMIN', is_admin ($current_user));
	$smarty->assign ('UPLOAD_MAXSIZE', (PlatzillaUtils::getMaxFileSizeInMb () * 1024 * 1024));
	$smarty->assign ('MOD', $mod_strings);
	$smarty->assign ('MOD_SEQ_ID', $autostr);
	$smarty->assign ('MODE', ($isduplicate === 'true') ? 'create' : $focus->mode);
	$smarty->assign ('MODULE', $currentModule);
	$smarty->assign ('NUMBERING_FORMAT', $current_user->numbering_format);
	$smarty->assign ('OP_MODE', $displayView);
	$smarty->assign ('USER_DATE_FORMAT', $current_user->date_format ? $current_user->date_format : 'yyyy-mm-dd');
	$smarty->assign ('PICKIST_DEPENDENCY_DATASOURCE', Zend_Json::encode (Vtiger_DependencyPicklist::getPicklistDependencyDatasource ($currentModule)));
	$smarty->assign ('PICKLIST_PIPELINE_RELATIONSHIPS', Zend_Json::encode ($picklistPipelineRelationships));
	$smarty->assign ('PROFILE_IDS', $profileIds);
	$smarty->assign ('PROCESS', ProcessCasesUtils::fetchAvailableProcess ($adb, $current_user, $currentModule, $caseNumber));
	$smarty->assign ('RECORD', $record);
	$smarty->assign ('SEARCH', getBasic_Advance_SearchURL ());
	$smarty->assign ('SINGLE_MOD', "SINGLE_{$currentModule}");
	$smarty->assign ('TABLE_FIELDS', TableFieldManager::getInstance ($adb)->fetchTableFieldByModule ($currentModule));
	$smarty->assign ('TABLE_FIELD_DATA',(isset ($tableFieldData)) ? $tableFieldData : null);
	$smarty->assign ('THEME', $theme);
	$smarty->assign ('VALIDATION_DATA_FIELDNAME', $validationArray ['fieldname']);
	$smarty->assign ('VALIDATION_DATA_FIELDDATATYPE', $validationArray ['datatype']);
	$smarty->assign ('VALIDATION_DATA_FIELDLABEL', $validationArray ['fieldlabel']);
	if (($focus->mode == 'edit') || ($isduplicate)) {
		$smarty->assign ('NAME', $recordName);
	}
	if (!empty ($returnAction)) {
		$smarty->assign ('RETURN_ACTION', $returnAction);
	}
	if (!empty ($returnId)) {
		$smarty->assign ('RETURN_ID', $returnId);
	}
	if (!empty ($returnModule)) {
		$smarty->assign ('RETURN_MODULE', $returnModule);
	}
	if (!empty($returnTab)) {
		$smarty->assign ('RETURN_TAB', $returnTab);
	}
	if (!empty ($returnViewName)) {
		$smarty->assign ('RETURN_VIEWNAME', $returnViewName);
	}
	$smarty->display ('EditView.tpl');

	if ($record) {
		$oldDieOnError = $adb->dieOnError;
		$adb->setDieOnError (false);
		BackgroundTasksRunner::getInstance ($adb, $_SESSION ['plat'])->runEventTriggeredTasks ('EDIT', BackgroundTaskInterface::EVENT_INSTANT_AFTER, $focus);
		$adb->setDieOnError ($oldDieOnError);
	}

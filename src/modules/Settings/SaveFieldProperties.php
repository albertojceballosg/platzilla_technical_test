<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/platzilla/Utils/MiscellaneousUtils.php');
	require_once ('include/platzilla/Utils/VtigerUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/SaveFieldPropertiesHelper.class.php');
	require_once ('modules/Settings/lib/DateDefaultValueUtils.php');

	global $adb;

	$calculationId                = PlatzillaUtils::purify ($_POST, 'calculationid');
	$defaultValue                 = PlatzillaUtils::purify ($_POST, 'defaultvalue');
	$fieldName                    = PlatzillaUtils::purify ($_POST, 'fieldname');
	$hiddenFields                 = PlatzillaUtils::purify ($_POST, 'hiddenfields');
	$isMandatory                  = PlatzillaUtils::purify ($_POST, 'ismandatory', false);
	$length                       = PlatzillaUtils::purify ($_POST, 'length');
	$moduleName                   = PlatzillaUtils::purify ($_POST, 'modulename');
	$newPicklistValuesData        = PlatzillaUtils::purify ($_POST, 'picklistvalues');
	$newPipelineValuesData        = PlatzillaUtils::purify ($_POST, 'pipelinevalues');
	$precision                    = PlatzillaUtils::purify ($_POST, 'precision');
	$presence                     = PlatzillaUtils::purify ($_POST, 'presence');
	$moduleReference              = PlatzillaUtils::purify ($_POST, 'modulereference');
	$validationDateInitialValue   = PlatzillaUtils::purify ($_POST, 'validationdateinitialvalue');
	$validationDateMaximumValue   = PlatzillaUtils::purify ($_POST, 'validationdatemaximumvalue');
	$validationNumberInitialValue = PlatzillaUtils::purify ($_POST, 'validationnumberinitialvalue');
	$validationNumberMaximumValue = PlatzillaUtils::purify ($_POST, 'validationnumbermaximumvalue');
	$validationUnique             = PlatzillaUtils::purify ($_POST, 'validationunique', false);
	$visibleFields                = PlatzillaUtils::purify ($_POST, 'visiblefields');
	$hiddenProfiles               = PlatzillaUtils::purify ($_POST, 'hiddenprofiles');
	$visibleProfiles              = PlatzillaUtils::purify ($_POST, 'visibleprofiles');
	$daughterPicklist             = PlatzillaUtils::purify ($_POST, 'daughterpicklist');
	$motherPicklistId             = PlatzillaUtils::purify ($_POST, 'motherpicklistid');
	$daughterOption               = PlatzillaUtils::purify ($_POST, 'selecteddaughteroptions');
	$relationshipsName            = PlatzillaUtils::purify ($_POST, 'relationshipname');
	// NUEVO: Variables para relaciones picklist-pipeline
	$pipelineDaughterPicklist     = PlatzillaUtils::purify ($_POST, 'pipelinedaughterpicklist');
	$motherPipelinePicklistValue  = PlatzillaUtils::purify ($_POST, 'motherpipelinepicklistid');
	$selectedPipelineDaughterOptions = PlatzillaUtils::purify ($_POST, 'selectedpipelinedaughteroptions');
	$pipelineRelationshipName     = PlatzillaUtils::purify ($_POST, 'pipelinerelationshipname');

	try {
		// Validaciones básicas
		if (empty ($fieldName)) {
			throw new Exception ('No se ha suministrado el nombre del campo');
		}
		if (empty ($moduleName)) {
			throw new Exception ('No se ha suministrado el nombre del módulo');
		}
		// Obtener el campo
		$fm            = FieldManager::getInstance ($adb);
		$selectedField = $fm->fetchFieldByName ($moduleName, $fieldName);
		if (empty ($selectedField)) {
			throw new Exception ("El campo {$fieldName} no se encuentra registrado en el módulo {$moduleName}");
		}
		$isInstance       = !empty ($_SESSION ['platInstancia']) ? true : false;
		$uiType           = $selectedField->getUiType ();
		$vTigerModuleData = VtigerUtils::parseModuleFile ($adb, $moduleName);

		// Propiedades básicas
		if (!in_array ($uiType, array (FieldInterface::UI_TYPE_CODE, FieldInterface::UI_TYPE_CREATED_TIME, FieldInterface::UI_TYPE_OWNER))) {
			// Si es un campo de fecha, validar y guardar la expresión
			if ($uiType == FieldInterface::UI_TYPE_DATE && $defaultValue !== '') {
				$validation = validateDateDefaultExpression($defaultValue);
				if (!$validation['valid']) {
					throw new Exception('Expresión de fecha inválida: ' . $validation['message']);
				}
				// Limpiar y normalizar la expresión antes de guardar
				$cleanExpression = strtoupper(trim($defaultValue));
				// Remover caracteres no deseados al final (guiones, espacios extras)
				$cleanExpression = preg_replace('/[^A-Z0-9+\-]+$/', '', $cleanExpression);
				// Remover espacios dentro de la expresión
				$cleanExpression = preg_replace('/\s+/', '', $cleanExpression);
				$dummy = $cleanExpression;
			} else {
				$dummy = $defaultValue !== '' ? $defaultValue : null;
			}
			if ($selectedField->getDefaultValue () != $dummy) {
				$selectedField->setDefaultValue ($dummy);
				$selectedField->setLocked ($isInstance);
			}

			$dummy = $isMandatory ? true : false;
			if ($selectedField->isMandatory () != $dummy) {
				$selectedField->setMandatory ($dummy);
				$selectedField->setLocked ($isInstance);
			}

			if ($selectedField->getPresence () != $presence) {
				$selectedField->setPresence ($presence);
				$selectedField->setLocked ($isInstance);
			}

			if ($selectedField->getCalculationName () != $calculationId) {
				$selectedField->setCalculationName ($calculationId);
				$selectedField->setLocked ($isInstance);
			}
		}
		if (in_array ($uiType, array (FieldInterface::UI_TYPE_CURRENCY, FieldInterface::UI_TYPE_NUMBER, FieldInterface::UI_TYPE_PERCENTAGE, FieldInterface::UI_TYPE_TEXT))) {
			$length    = (is_numeric ($length)) && (intval ($length) == $length) ? intval ($length) : null;
			$precision = (is_numeric ($precision)) && (intval ($precision) == $precision) ? intval ($precision) : null;
			if (($selectedField->getLength () != $length) || ($selectedField->getPrecision () != $precision)) {
				$selectedField->updateLength ($length, $precision);
				$selectedField->setLocked ($isInstance);
			}
		}

		// Validaciones
		$validations = array ();
		if ($validationUnique) {
			$validations [] = FieldValidation::getInstance ()
				->setFieldName ($fieldName)
				->setModuleName ($moduleName)
				->setTableName ($vTigerModuleData ['maintable']['name'])
				->setType (FieldValidationInterface::VALIDATION_TYPE_UNIQUE);
		}
		if ((in_array ($uiType, array (FieldInterface::UI_TYPE_DATE, FieldInterface::UI_TYPE_DATETIME))) && (($validationDateInitialValue !== null) || ($validationDateMaximumValue !== null))) {
			$validations [] = FieldValidation::getInstance ()
				->setFieldName ($fieldName)
				->setInitialValue ($validationDateInitialValue)
				->setMaximumValue ($validationDateMaximumValue)
				->setModuleName ($moduleName)
				->setTableName ($vTigerModuleData ['maintable']['name'])
				->setType (FieldValidationInterface::VALIDATION_TYPE_DATE);
		} else if (($uiType == FieldInterface::UI_TYPE_NUMBER) && (($validationNumberInitialValue !== null) || ($validationNumberMaximumValue !== null))) {
			$validations [] = FieldValidation::getInstance ()
				->setFieldName ($fieldName)
				->setInitialValue ($validationNumberInitialValue)
				->setMaximumValue ($validationNumberMaximumValue)
				->setModuleName ($moduleName)
				->setTableName ($vTigerModuleData ['maintable']['name'])
				->setType (FieldValidationInterface::VALIDATION_TYPE_NUMBER);
		}
		if (!empty ($validations)) {
			$selectedField->setValidations ($validations);
		}

		// Referencias a módulos
		if ($uiType == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
			$referencedModuleName   = $moduleReference ['name'];
			$referenceRelationships = $moduleReference ['relationships'];
			if (!empty ($referenceRelationships)) {
				$relationships = array ();
				foreach ($referenceRelationships as $relationshipReferencedFieldName => $relationshipFieldName) {
					$relationships [] = FieldModuleReferenceRelationship::getInstance ()
						->setFieldName ($relationshipFieldName)
						->setReferencedFieldName ($relationshipReferencedFieldName);
				}
			} else {
				$relationships = null;
			}

			if (!empty ($moduleReference ['filters'])) {
				$referenceFilters = array ();
				foreach ($moduleReference ['filters'] as $index => $referenceFilter) {
					$referenceFilters [] = FieldModuleReferenceFilter::getInstance ()
						->setComparator ($referenceFilter ['comparator'])
						->setFieldName ($referenceFilter ['field'])
						->setOperator (isset ($referenceFilter ['operator']) ? $referenceFilter ['operator'] : null)
						->setSequence (intval ($index))
						->setValue (isset ($referenceFilter ['value']) ? $referenceFilter ['value'] : null)
						->setValueModuleName ((isset ($referenceFilter ['valuemodulename'])) && ($referenceFilter ['valuetype'] == FieldModuleReferenceFilter::TYPE_SOURCE_FIELD) ? $referenceFilter ['valuemodulename'] : null)
						->setValueType ($referenceFilter ['valuetype']);
				}
			} else {
				$referenceFilters = null;
			}

			$oldReference = FieldModuleReferenceManager::getInstance ($adb)->fetchReference ($moduleName, $fieldName, $referencedModuleName);
			$newReference = FieldModuleReference::getInstance ()
				->setFieldName ($fieldName)
				->setFilters ($referenceFilters)
				->setModuleName ($moduleName)
				->setReferencedModuleName ($referencedModuleName)
				->setRelationships ($relationships)
				->setSequence (1);
			if (empty ($oldReference) || (!$newReference->isEqualTo ($oldReference))) {
				$newReference->setLocked (true);
			}

			$selectedField->setModuleReferences (array ($newReference));
		}

		// Picklist Values
		if ($uiType == FieldInterface::UI_TYPE_PICKLIST || $uiType == FieldInterface::UI_TYPE_MULTI_SELECT) {
			$picklist          = $selectedField->getPicklist ();
			$oldPicklistValues = $picklist->getValues ();
			if (!empty ($newPicklistValuesData)) {
				$newPicklistValues = array ();
				$changedValues     = array ();
				$currentValues     = array ();
				foreach ($oldPicklistValues as $oldPicklistValue) {
					$currentValues[] = $oldPicklistValue->getValue();
				}
				foreach ($newPicklistValuesData as $newPicklistId => $newPicklistValueData) {
					$changedValues[ $newPicklistValueData ['seq'] ] = $newPicklistValueData ['value'];

					if (!empty ($newPicklistValueData ['roles'])) {
						$newPicklistValueRoles = array ();
						foreach ($newPicklistValueData ['roles'] as $roleId) {
							$newPicklistValueRoles [] = Role::getInstance ()->setId ($roleId);
						}
					} else {
						$newPicklistValueRoles = null;
					}

					$selectedOldPicklistValue = null;
					if (!empty ($oldPicklistValues)) {
						foreach ($oldPicklistValues as $oldPicklistValue) {
							if ($oldPicklistValue->getId () == $newPicklistId) {
								$selectedOldPicklistValue = $oldPicklistValue;
								break;
							}
						}
					}

					if (!empty ($selectedOldPicklistValue)) {
						if (
							($isInstance) && (
								($oldPicklistValue->isLocked ()) ||
								($oldPicklistValue->getValue () != $newPicklistValueData ['value']) ||
								(!SaveFieldPropertiesHelper::areRolesEqual ($selectedOldPicklistValue->getRoles (), $newPicklistValueRoles))
							)
						) {
							$isLocked = true;
						} else {
							$isLocked = false;
						}
						$newPicklistValues [] = $oldPicklistValue->setRoles ($newPicklistValueRoles)
							->setLocked ($isLocked)
							->setValue (trim ($newPicklistValueData ['value']));
					} else {
						$newPicklistValues [] = PicklistValue::getInstance (false)->setRoles ($newPicklistValueRoles)
							->setLocked ($isInstance)
							->setValue (trim ($newPicklistValueData ['value']));
					}
				}
				ksort ($changedValues);
				$diffChangedValues = array_values (array_diff ($changedValues, $currentValues));
				$diffCurrentValues = array_values (array_diff ($currentValues, $changedValues));
				if (count ($diffChangedValues) && count ($diffCurrentValues)) {
					SaveFieldPropertiesHelper::updatePickListValues ($adb, $diffChangedValues, $diffCurrentValues, $fieldName, $selectedField->getTableName());
				}
			} else {
				$newPicklistValues = null;
			}
			$picklist->setValues ($newPicklistValues);
			$selectedField->setPicklist ($picklist);
		}

		// Pipeline Values
		if ($uiType == FieldInterface::UI_TYPE_PIPELINE) {
			$pipeline          = $selectedField->getPipeline ();
			$oldPipelineValues = $pipeline->getValues ();
			$newPipelineValues = array_map ('trim', $newPipelineValuesData);
			
			// Detectar cambios en los valores del pipeline (comparando por índice/posición)
			$changedValues = array ();
			$currentValues = array ();
			
			if (!empty ($oldPipelineValues) && !empty ($newPipelineValues)) {
				// Comparar arrays por posición para detectar cambios
				foreach ($newPipelineValues as $index => $newValue) {
					if (isset ($oldPipelineValues[$index])) {
						$oldValue = $oldPipelineValues[$index];
						if ($oldValue !== $newValue) {
							// El valor en esta posición cambió
							$changedValues[] = $newValue;
							$currentValues[] = $oldValue;
						}
					}
				}
			}
			
			$pipeline->setValues ($newPipelineValues);
			$selectedField->setPipeline ($pipeline);
			
			// Si hay cambios, actualizar los registros del módulo y generar histórico
			if (count ($changedValues) > 0 && count ($currentValues) > 0) {
				global $current_user;
				SaveFieldPropertiesHelper::updatePipelineValues ($adb, $changedValues, $currentValues, $fieldName, $selectedField->getTableName(), $moduleName, $current_user->id);
			}
		}
		$fm->saveField ($selectedField);

		// Dependencias
		if (in_array ($uiType, array (FieldInterface::UI_TYPE_PICKLIST, FieldInterface::UI_TYPE_PIPELINE, FieldInterface::UI_TYPE_GLOBAL_PICKLIST))) {
			$fdm = FieldDependencyManager::getInstance ($adb);
			$fdm->deleteDependenciesBySourceFieldName ($moduleName, $fieldName);
			if (!empty ($hiddenFields)) {
				foreach ($hiddenFields as $picklistValue => $hiddenFieldNames) {
					if ($picklistValue == '__NO_SELECTION__') {
						$picklistValue = null;
					} else if ($picklistValue == '__EMPTY__') {
						$picklistValue = '';
					}
					foreach ($hiddenFieldNames as $hiddenFieldName) {
						$dependency = FieldDependency::getInstance ()
							->setModuleName ($moduleName)
							->setSourceFieldName ($fieldName)
							->setSourceFieldValue ($picklistValue)
							->setTargetFieldName ($hiddenFieldName)
							->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_HIDDEN);
						$fdm->saveDependency ($dependency);
					}
				}
			}
			if (!empty ($visibleFields)) {
				foreach ($visibleFields as $picklistValue => $visibleFieldNames) {
					if ($picklistValue == '__NO_SELECTION__') {
						$picklistValue = null;
					} else if ($picklistValue == '__EMPTY__') {
						$picklistValue = '';
					}
					foreach ($visibleFieldNames as $visibleFieldName) {
						$dependency = FieldDependency::getInstance ()
							->setModuleName ($moduleName)
							->setSourceFieldName ($fieldName)
							->setSourceFieldValue ($picklistValue)
							->setTargetFieldName ($visibleFieldName)
							->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_VISIBLE);
						$fdm->saveDependency ($dependency);
					}
				}
			}
		}

		//visible in profiles
		if (!empty ($hiddenProfiles) || !empty ($visibleProfiles)) {
			SaveFieldPropertiesHelper::setVisibilityByProfiles ($adb, $visibleProfiles, $hiddenProfiles);
		}

		if (!empty ($motherPicklistId) && !empty ($daughterOption) && !empty ($daughterPicklist) && ($uiType == FieldInterface::UI_TYPE_PICKLIST)) {
			$motherPicklistIds = explode (',', $motherPicklistId);
			$daughterOptions   = explode (';', $daughterOption);
			$totalPickListId   = count ($motherPicklistIds);
			for ($k = 0; $k < $totalPickListId; $k++) {
				$pickListRelationships[ $motherPicklistIds[ $k ] ] = explode (',', $daughterOptions[ $k ]);
				if (($k + 1) != $totalPickListId) {
					array_pop ($pickListRelationships[ $motherPicklistIds[ $k ] ]);
				}
			}

			$parameters = array (
				'moduleName'        => $moduleName,
				'motherPicklist'    => $fieldName,
				'daughterPicklist'  => $daughterPicklist,
				'relationshipsName' => $relationshipsName,
				'locked'            => !empty ($_SESSION ['platInstancia']),
			);
			SaveFieldPropertiesHelper::savePicklistRelationship ($adb, $pickListRelationships, $parameters);
		}

		// NUEVO: Procesar relaciones Picklist → Pipeline
		if (!empty ($motherPipelinePicklistValue) && !empty ($selectedPipelineDaughterOptions) && !empty ($pipelineDaughterPicklist) && ($uiType == FieldInterface::UI_TYPE_PICKLIST)) {
			$motherPicklistValues = explode (',', $motherPipelinePicklistValue);
			$selectedPipelineOptions = explode (';', $selectedPipelineDaughterOptions);
			$totalPickListValues = count ($motherPicklistValues);
			for ($k = 0; $k < $totalPickListValues; $k++) {
				$pickListPipelineRelationships[ $motherPicklistValues[ $k ] ] = explode (',', $selectedPipelineOptions[ $k ]);
				if (($k + 1) != $totalPickListValues) {
					array_pop ($pickListPipelineRelationships[ $motherPicklistValues[ $k ] ]);
				}
			}

			$parameters = array (
				'moduleName'        => $moduleName,
				'motherPicklist'    => $fieldName,
				'daughterPipeline'  => $pipelineDaughterPicklist,
				'relationshipsName' => $pipelineRelationshipName,
				'locked'            => !empty ($_SESSION ['platInstancia']),
			);
			SaveFieldPropertiesHelper::savePicklistPipelineRelationship ($adb, $pickListPipelineRelationships, $parameters);
		}

		header ('HTTP/1.1 200 OK');
	} catch (Exception $e) {
		header ('HTTP/1.1 500 Internal error');
		header ('Content-Type: text/plain');
		echo $e->getMessage ();
	}
	exit ();

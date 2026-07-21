<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/calculated_fields/CalculatedFields.class.php');
	require_once ('modules/Settings/lib/GetFieldPropertiesHelper.class.php');

	global $adb, $current_user;

	$moduleName        = PlatzillaUtils::purify ($_GET, 'modulename');
	$selectedFieldName = PlatzillaUtils::purify ($_GET, 'fieldname');

	try {
		// Validaciones básicas
		if (empty ($selectedFieldName)) {
			throw new Exception ('No se ha suministrado el nombre del campo');
		}
		if (empty ($moduleName)) {
			throw new Exception ('No se ha suministrado el nombre del módulo');
		}

		// Obtener el campo solicitado
		$selectedField = GetFieldPropertiesHelper::getField ($adb, $moduleName, $selectedFieldName);
		if (empty ($selectedField)) {
			throw new Exception ("El campo {$selectedFieldName} no se encuentra registrado en el módulo {$moduleName}");
		}

		// Obtener las propiedades básicas
		$properties = GetFieldPropertiesHelper::getFieldBasicProperties ($adb, $selectedField);

		if ($properties ['uitype'] == FieldInterface::UI_TYPE_CALCULATED_LINK) {
			$platform           = $_SESSION ['plat'];
			$CalculatedFields   = new CalculatedFieldsUtils ($adb, $platform);
			$calculations       = array ();
			$calculationObjects = $CalculatedFields->getAllCalculateSystem ($current_user);

			foreach ($calculationObjects as $object) {
				$calculations [] = array (
					'calculated_systemid' => $object->getId (),
					'name'                => $object->getName (),
					'description'         => $object->getDescription (),
					'calculationName'     => $object->getCalculationName (),
				);
			}
			$properties ['calculatedSystem'] = (!empty($calculations)) ? json_encode ($calculations) : null;
		} else {
			$properties ['calculatedSystem'] = null;
		}

		// Obtener las propiedades del campo grid
		if ($properties ['uitype'] == FieldInterface::UI_TYPE_GRID) {
			$gridProperties = GetFieldPropertiesHelper::getGridFields ($selectedField);
			if (!empty ($gridProperties)) {
				$properties ['grid'] = $gridProperties;
			}
		}

		// Agregar las validaciones a las propiedades
		$validations = GetFieldPropertiesHelper::getFieldValidations ($selectedField);
		if (!empty ($validations)) {
			$properties ['validations'] = $validations;
		}

		$uiType = $selectedField->getUiType ();
		if (in_array ($uiType, array (FieldInterface::UI_TYPE_GLOBAL_PICKLIST, FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_PICKLIST))) {
			// Agregar los picklist values y dependencias
			$properties ['picklistvalues']     = GetFieldPropertiesHelper::getFieldPicklistValues ($selectedField);
			$properties ['availableroles']     = GetFieldPropertiesHelper::getAvailableRoles ($adb);
			$properties ['modulefields']       = GetFieldPropertiesHelper::getModuleFields ($adb, $selectedField);
			$properties ['relationship']       = GetFieldPropertiesHelper::getPicklistRelationship ($adb, $moduleName, $selectedFieldName);
			$properties ['daughtersAvailable'] = GetFieldPropertiesHelper::getPickListByModule  ($adb, $moduleName, $selectedFieldName);
			// NUEVO: Agregar campos pipeline y relaciones picklist-pipeline
			// Obtener todos los pipelines (sin filtrar) para verificar si el módulo tiene pipelines
			$allPipelines = GetFieldPropertiesHelper::getPipelineFields ($adb, $moduleName, false);
			// Obtener pipelines disponibles (excluyendo los que ya tienen relación)
			$properties ['pipelinefields'] = GetFieldPropertiesHelper::getPipelineFields ($adb, $moduleName, true, $selectedFieldName);
			$properties ['picklistpipelinerelationship'] = GetFieldPropertiesHelper::getPicklistPipelineRelationship ($adb, $moduleName, $selectedFieldName);
			
			// Información adicional para el frontend sobre el estado de los pipelines
			$properties ['pipelineinfo'] = array (
				'haspipelines' => !empty ($allPipelines),
				'hasavailablepipelines' => !empty ($properties ['pipelinefields']),
				'allpipelinesrelated' => !empty ($allPipelines) && empty ($properties ['pipelinefields'])
			);
			
			// Agregar traducciones para mensajes de pipeline
			global $current_language;
			$properties ['pipelinetranslations'] = array (
				'no_available' => getTranslatedString ('LBL_PIPELINE_NO_AVAILABLE', 'Settings'),
				'no_available_title' => getTranslatedString ('LBL_PIPELINE_NO_AVAILABLE_TITLE', 'Settings')
			);
		} else if ($uiType == FieldInterface::UI_TYPE_PIPELINE) {
			// Agregar los valores y dependencias
			$properties ['pipelinevalues'] = GetFieldPropertiesHelper::getFieldPipelineValues ($selectedField);
			$properties ['modulefields']   = GetFieldPropertiesHelper::getModuleFields ($adb, $selectedField);
		} else if ($uiType == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
			// Agregar la referencias a módulo
			$moduleReferenceProperties             = GetFieldPropertiesHelper::getFieldReferencedModuleProperties ($selectedField);
			$properties ['referencedmodulefields'] = GetFieldPropertiesHelper::getAvailableFieldsData ($adb, $moduleReferenceProperties ['name']);
			$properties ['modulereference']        = $moduleReferenceProperties;
			$properties ['modulefields']           = GetFieldPropertiesHelper::getModuleFields ($adb, $selectedField);
		}

		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ($properties);
	} catch (Exception $e) {
		header ('HTTP/1.1 500 Internal error');
		header ('Content-Type: text/plain');
		echo $e->getMessage ();
	}
	exit ();

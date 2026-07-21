<?php
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/Settings/lib/FieldInformationUtils.class.php');
	require_once ('modules/Settings/lib/DateDefaultValueUtils.php');

	global $adb, $current_user, $mod_strings;

	$blockId              = PlatzillaUtils::purify ($_POST, 'blockid');
	$calculationName      = PlatzillaUtils::purify ($_POST, 'calculationname');
	$defaultDate          = PlatzillaUtils::purify ($_POST, 'defaultdate');
	$fieldName            = PlatzillaUtils::purify ($_POST, 'name');
	$globalPicklistName   = PlatzillaUtils::purify ($_POST, 'globalpicklistname');
	$handlerClass         = PlatzillaUtils::purify ($_POST, 'handlerclass');
	$handlerMethod        = PlatzillaUtils::purify ($_POST, 'handlermethod');
	$label                = PlatzillaUtils::purify ($_POST, 'label');
	$length               = PlatzillaUtils::purify ($_POST, 'length');
	$moduleName           = PlatzillaUtils::purify ($_POST, 'modulename');
	$precision            = PlatzillaUtils::purify ($_POST, 'precision');
	$rawPicklistValues    = PlatzillaUtils::purify ($_POST, 'picklistvalues');
	$referencedModuleName = PlatzillaUtils::purify ($_POST, 'referencedmodulename');
	$uiType               = PlatzillaUtils::purify ($_POST, 'uitype');
	$unique               = PlatzillaUtils::purify ($_POST, 'unique', false);

	$isInstance = !empty ($_SESSION ['platInstancia']);
	$platform   = !empty ($_SESSION ['platInstancia']) ? $_SESSION ['platInstancia'] : $platPrincipal;

	try {
		if (!is_admin ($current_user)) {
			throw new Exception ('Acceso denegado', 401);
		}

		$isFieldNameDuplicate = FieldInformationUtils::validateFieldName ($adb, $moduleName, $fieldName);
		if ($isFieldNameDuplicate) {
			throw new Exception ($mod_strings [FieldException::ERROR_DUPLICATE_FIELD_NAME]);
		}

		$fm = FieldManager::getInstance ($adb);
		if (in_array ($uiType, array (FieldInterface::UI_TYPE_GLOBAL_PICKLIST))) {
			$dummy = GlobalPicklistManager::getInstance ($adb)->fetchPicklistByName ($globalPicklistName);
			if (empty ($dummy)) {
				throw new Exception ("No se encuentra registrado el campo {$globalPicklistName}");
			}
			$fieldName = $dummy->getName ();
		}
		if ($uiType == FieldInterface::UI_TYPE_APP) {
			if (empty ($handlerClass) || empty($handlerMethod)) {
				throw new Exception('Nombre de clase o método principal de la aplicación no encontrados');
			}
			$appField = array ('class' => $handlerClass, 'method' => $handlerMethod);
			$tableName = 'vtiger_' . strtolower ($handlerClass);
			$platform  = (!empty($platform)) ? "pg_crm_{$platform}" : $platform;
			if (!FieldInformationUtils::checkAppTable ($adb, $tableName)) {
				$tableName = FieldInformationUtils::getTableName ($moduleName, $fieldName);
			}
		} else {
			$tableName = FieldInformationUtils::getTableName ($moduleName, $fieldName);
		}
		
		// Establecer valor por defecto para campos de fecha
	$defaultValue = '';
	if ($uiType == FieldInterface::UI_TYPE_DATE) {
		// Validar y guardar la expresión de fecha
		if (!empty($defaultDate)) {
			$validation = validateDateDefaultExpression($defaultDate);
			if (!$validation['valid']) {
				throw new Exception('Expresión de fecha inválida: ' . $validation['message']);
			}
			// Limpiar y normalizar la expresión antes de guardar
			$cleanExpression = strtoupper(trim($defaultDate));
			// Remover caracteres no deseados al final (guiones, espacios extras)
			$cleanExpression = preg_replace('/[^A-Z0-9+\-]+$/', '', $cleanExpression);
			// Remover espacios dentro de la expresión
			$cleanExpression = preg_replace('/\s+/', '', $cleanExpression);
			$defaultValue = $cleanExpression;
		} else {
			// Si está vacío, guardar expresión TODAY
			$defaultValue = 'TODAY';
		}
	}
		
		$field = Field::getInstance ()
			->setBlockId ($blockId)
			->setAppField (isset($appField) ? $appField : null)
			->setCalculationName ($calculationName)
			->setColumnName ($fieldName)
			->setDefaultValue ($defaultValue)
			->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
			->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
			->setLabel ($label)
			->setLocked ($isInstance)
			->setMandatory (false)
			->setMassEditable (FieldInterface::MASS_EDITABLE_USER_DEFINED)
			->setModuleName ($moduleName)
			->setName ($fieldName)
			->setPresence (FieldInterface::PRESENCE_USER_DEFINED)
			->setQuickCreate (FieldInterface::QUICK_CREATE_ENABLED)
			->setReadOnly (FieldInterface::READ_WRITE)
			->setTableName ($tableName)
			->setUiType ($uiType, $length, $precision);

		if (in_array ($uiType, array (FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_PICKLIST))) {
			$dummies        = explode ("\n", $rawPicklistValues);
			$picklistValues = array ();
			foreach ($dummies as $dummy) {
				$picklistValues [] = PicklistValue::getInstance (true)->setLocked ($isInstance)->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ($dummy);
			}
			$picklist = Picklist::getInstance ()
				->setName ($fieldName)
				->setValues ($picklistValues);
			$field->setPicklist ($picklist);
		} else if (in_array ($uiType, array (FieldInterface::UI_TYPE_PIPELINE))) {
			$dummies  = explode ("\n", $rawPicklistValues);
			$pipeline = Pipeline::getInstance ()
				->setFieldName ($fieldName)
				->setModuleName ($moduleName)
				->setValues ($dummies);
			$field->setPipeline ($pipeline);
		} else if ($uiType == FieldInterface::UI_TYPE_MODULE_REFERENCE) {
			$reference = FieldModuleReference::getInstance ()
				->setFieldName ($fieldName)
				->setModuleName ($moduleName)
				->setReferencedModuleName ($referencedModuleName);
			$field->setModuleReferences (array ($reference));
		}
		if (!empty ($unique)) {
			$validation = FieldValidation::getInstance ()
				->setFieldName ($fieldName)
				->setLocked ($isInstance)
				->setModuleName ($moduleName)
				->setType (FieldValidation::VALIDATION_TYPE_UNIQUE);
			$field->setValidations (array ($validation));
		}
		$fm->saveField ($field);
		header ('HTTP/1.1 200 OK');
		header ('Content-Type: application/json');
		echo json_encode ('OK');
	} catch (Exception $e) {
		if ($e->getCode () == 401) {
			header ('HTTP/1.1 401 Access denied');
		} else {
			header ('HTTP/1.1 400 Bad request');
		}
		header ('Content-Type: application/json');
		echo json_encode ($e->getMessage ());
	}
	exit ();

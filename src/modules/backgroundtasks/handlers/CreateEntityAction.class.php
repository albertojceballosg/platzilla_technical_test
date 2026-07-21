<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');
	require_once ('include/platzilla/Configuration/BackgroundTaskParameterConfigurationInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/ProcessCasesUtils.class.php');
	require_once ('modules/process_steps/handlers/StepsType.class.php');

	class CreateEntityAction extends BackgroundTaskActionHandler {
		
		/**
		 * @param CRMEntity $entity
		 * @param string $moduleName
		 *
		 * @return void
		 * @throws WebServiceException
		 */
		private function createEntityCase ($entity, $moduleName) {
			$stepTypeObj    = StepsType::getInstance ($this->adb);
			$isModuleOfStep = $stepTypeObj->isModuleOfStep ($_GET ['step_id'], $moduleName);
			if (
				$isModuleOfStep &&
				!empty ($_GET ['case_number']) &&
				!empty ($_GET ['process_id']) &&
				!empty ($_GET ['step_id'])
			) {
				$this->logger->emit ('INFO', "Creando: Caso N° {$_GET ['case_number']} - {$moduleName}");
				$this->adb->pquery (
					'UPDATE vtiger_crmentity SET case_number=? WHERE crmid=?',
					array ($_GET ['case_number'], $entity->id)
				);
				$stepObj = $stepTypeObj->getStepsTypeById ($_GET ['step_id']);
				$dataCase = array (
					'case_number'      => $_GET ['case_number'],
					'process_id'       => $_GET ['process_id'],
					'step_id'          => $_GET ['step_id'],
					'step_type'        => $stepObj->StepType,
					'moduleName'       => $moduleName,
					'assigned_user_id' => $_GET ['user_id'],
				);
				$caseId = ProcessCasesUtils::createNewCase ($dataCase);
				$this->adb->pquery (
					'UPDATE vtiger_crmentity SET case_number=? WHERE crmid=?',
					array ($entity->id, $caseId)
				);
				
			} else {
				$this->logger->emit ('INFO', "Imposible crear caso para - {$moduleName}, no es un paso de proceso");
			}
		}
		
		/**
		 * @param BackgroundTaskAction $action
		 *
		 * @return integer
		 * @throws Exception
		 */
		public function run ($action) {
			if (empty ($action)) {
				throw new Exception ('No se ha suministrado la acción');
			}

			$parameters = $action->getParameters ();
			if (empty ($parameters)) {
				throw new Exception ('No se han suministrado los parámetros de la acción');
			}

			// Obtener el ID del registro que disparó la tarea
			$triggerRecordId = null;
			$triggerModuleName = null;
			foreach ($parameters as $parameter) {
				if ($parameter->getName () == 'record_id') {
					$triggerRecordId = $parameter->getValue ();
				}
			}

			$this->logger->emit ('INFO', 'Obteniendo parámetros');
			$requestedParameterNames = array ('modulename', 'fieldnames');
			$dummy                   = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			$moduleName              = $dummy ['modulename'];
			$fieldValuesData         = $dummy ['fieldnames'];
			$dummy                   = $this->getParameterValues ($parameters, array ('relateto', 'gridfieldnames'), false);
			$relateTo                = $dummy ['relateto'];
			$gridFieldValues         = $dummy ['gridfieldnames'];

			// Obtener el parámetro fieldnames para acceder a sus tipos y fórmulas
			$fieldnamesParameter = null;
			foreach ($parameters as $parameter) {
				if ($parameter->getName () == 'fieldnames') {
					$fieldnamesParameter = $parameter;
					break;
				}
			}

			$this->logger->emit ('INFO', "Localizando el archivo {$moduleName}.php");
			$moduleFilePath = "modules/{$moduleName}/{$moduleName}.php";
			if (!file_exists (__DIR__ . "/../../../{$moduleFilePath}")) {
				throw new Exception ("No se encuentra el archivo {$moduleFilePath}");
			}

			$this->logger->emit ('INFO', "Creando objeto del tipo {$moduleName}");
			require_once ($moduleFilePath);
			/** @var CRMEntity|stdClass $entity */
			if ($moduleName == 'Calendar') {
				$entity = new Activity ();
			} else {
				$entity = new $moduleName ();
			}

			$this->logger->emit ('INFO', 'Insertando data');
			foreach ($fieldValuesData as $fieldName => $fieldValue) {
				// Obtener el tipo y la fórmula del campo actual
				$types = $fieldnamesParameter->getType ();
				$valueFormulas = $fieldnamesParameter->getValueFormula ();
				
				// Manejar el caso cuando fieldnames es showExpanded
				if (is_array ($types) && isset ($types [$fieldName])) {
					$type = $types [$fieldName];
				} else {
					$type = null;
				}
				
				if (is_array ($valueFormulas) && isset ($valueFormulas [$fieldName])) {
					$valueFormula = $valueFormulas [$fieldName];
				} else {
					$valueFormula = null;
				}
				
				// Si el tipo es FORMULA, evaluar la fórmula
				if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_FORMULA && !empty ($valueFormula)) {
					$evaluatedValue = $this->evaluateDateFormula ($valueFormula, $triggerRecordId);
					$this->logger->emit ('INFO', "Fórmula evaluada: {$valueFormula} -> {$evaluatedValue}");
					$entity->column_fields [ $fieldName ] = $evaluatedValue;
				} 
				// Para campos de texto, verificar si contienen fórmulas de concatenación
				else if (!empty ($fieldValue) && is_string ($fieldValue) && 
				         (strpos ($fieldValue, '|') !== false || strpos ($fieldValue, '{') !== false)) {
					$evaluatedValue = $this->evaluateTextFormula ($fieldValue, $triggerRecordId);
					$this->logger->emit ('INFO', "Texto concatenado: {$fieldValue} -> {$evaluatedValue}");
					$entity->column_fields [ $fieldName ] = $evaluatedValue;
				} 
				else {
					$entity->column_fields [ $fieldName ] = $fieldValue;
				}
			}

			if (($moduleName == 'Calendar') && !empty ($entity->column_fields)) {
				$entity->column_fields ['related_id'] = $relateTo;
			}
			$this->logger->emit ('INFO', 'Guardando');
			$entity->save ($moduleName);
			if (($moduleName == 'Calendar') && isset($relateTo)) {
				$this->adb->pquery (
					'INSERT IGNORE INTO vtiger_seactivityrel (crmid, activityid) VALUES (?, ?)',
					array($relateTo, $entity->id)
				);
			}
			$this->createEntityCase ($entity, $moduleName);
			$this->saveGridValues ($entity, $gridFieldValues);
			/** @noinspection PhpUndefinedFieldInspection */
			if (!empty ($this->adb->database->_errorMsg)) {
				/** @noinspection PhpUndefinedFieldInspection */
				throw new Exception ("Se ha presentado un error: {$this->adb->database->_errorMsg}");
			}
			$this->relateEntities ($entity, $relateTo);

			return $entity->id;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return CreateEntityAction|null
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

		/**
		 * Evalúa una fórmula de fecha
		 *
		 * @param string $formula - Fórmula como "{TODAY} + 7 días" o "|date_start| - 30 días"
		 * @param integer|null $triggerRecordId - ID del registro que disparó la tarea (para obtener valores de campos)
		 *
		 * @return string - Fecha evaluada en formato Y-m-d
		 */
		private function evaluateDateFormula ($formula, $triggerRecordId = null) {
			// Reemplazar variables del sistema (usar las variables estándar definidas en SystemVariables)
			$formula = str_replace ('{TODAY}', date ('Y-m-d'), $formula);
			$formula = str_replace ('{NOW}', date ('Y-m-d H:i:s'), $formula);

			// Buscar campos del módulo en el formato |campo|
			preg_match_all ('/\|([^|]+)\|/', $formula, $matches);
			if (!empty ($matches [1])) {
				foreach ($matches [1] as $fieldName) {
					$fieldValue = $this->getFieldValue ($triggerRecordId, $fieldName);
					if ($fieldValue !== null) {
						$formula = str_replace ("|{$fieldName}|", $fieldValue, $formula);
					}
				}
			}

			// Evaluar operaciones de suma/resta de días
			// Formato: "YYYY-MM-DD + X días" o "YYYY-MM-DD - X días"
			preg_match ('/(\d{4}-\d{2}-\d{2})\s*([+-])\s*(\d+)\s*(días|dia|days?|day)?/i', $formula, $matches);
			if (!empty ($matches)) {
				$baseDate = $matches [1];
				$operator = $matches [2];
				$days = intval ($matches [3]);

				$timestamp = strtotime ($baseDate);
				if ($operator == '+') {
					$timestamp += ($days * 86400); // 86400 segundos = 1 día
				} else if ($operator == '-') {
					$timestamp -= ($days * 86400);
				}

				return date ('Y-m-d', $timestamp);
			}

			// Si no hay operaciones, devolver la fórmula tal cual (asumiendo que es una fecha)
			return trim ($formula);
		}

		/**
		 * Evalúa una fórmula de texto con concatenación de campos y variables del sistema
		 *
		 * @param string $formula - Fórmula como "Llamar a |firstname| {lastname| el día {TODAY}"
		 * @param integer|null $triggerRecordId - ID del registro que disparó la tarea
		 * @param array|null $targetRecordData - Datos del registro que se está modificando (opcional)
		 *
		 * @return string - Texto evaluado con valores reemplazados
		 */
		private function evaluateTextFormula ($formula, $triggerRecordId = null, $targetRecordData = null) {
			// Reemplazar variables del sistema
			$systemVariables = SystemVariables::getAvailableVariableValues ($this->adb, $targetRecordData ?: ['record_id' => $triggerRecordId]);
			foreach ($systemVariables as $variableName => $variableValue) {
				if ($variableValue !== null) {
					$formula = str_replace ('{' . $variableName . '}', $variableValue, $formula);
				}
			}

			// Buscar campos del módulo en el formato |campo|
			preg_match_all ('/\|([^|]+)\|/', $formula, $matches);
			if (!empty ($matches [1])) {
				foreach ($matches [1] as $fieldName) {
					// Primero intentar obtener del registro que se está modificando (target)
					$fieldValue = null;
					if ($targetRecordData && isset ($targetRecordData [$fieldName])) {
						$fieldValue = $targetRecordData [$fieldName];
					}
					
					// Si no se encuentra, intentar del registro que disparó la tarea (trigger)
					if ($fieldValue === null && $triggerRecordId) {
						$fieldValue = $this->getFieldValue ($triggerRecordId, $fieldName);
					}
					
					if ($fieldValue !== null) {
						$formula = str_replace ("|{$fieldName}|", $fieldValue, $formula);
					}
				}
			}

			return trim ($formula);
		}

		/**
		 * Obtiene el valor de un campo del registro que disparó la tarea
		 *
		 * @param integer|null $recordId
		 * @param string $fieldName
		 *
		 * @return string|null
		 */
		private function getFieldValue ($recordId, $fieldName) {
			if (empty ($recordId)) {
				return null;
			}

			// Obtener el nombre del módulo del registro
			$result = $this->adb->pquery ('SELECT setype FROM vtiger_crmentity WHERE crmid=? AND deleted=0', array ($recordId));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$moduleName = $row ['setype'];
			} else {
				return null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			// Obtener el nombre de la columna del campo
			$result = $this->adb->pquery (
				'SELECT columnname, tablename FROM vtiger_field WHERE fieldname=? AND tabid IN (SELECT tabid FROM vtiger_tab WHERE name=?)',
				array ($fieldName, $moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$columnName = $row ['columnname'];
				$tableName = $row ['tablename'];
			} else {
				return null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			// Obtener el valor del campo
			$result = $this->adb->pquery ("SELECT {$columnName} FROM {$tableName} WHERE {$moduleName}id=?", array ($recordId));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$fieldValue = $row [$columnName];
			} else {
				return null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $fieldValue;
		}

	}

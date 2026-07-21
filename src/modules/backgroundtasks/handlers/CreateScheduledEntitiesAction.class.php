<?php
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');

	class CreateScheduledEntitiesAction extends BackgroundTaskActionHandler {

		/**
		 * @param BackgroundTaskParameter[] $parameters
		 *
		 * @return array
		 */
		private function getCalculatedDateFieldName ($parameters) {
			$this->logger->emit ('INFO', 'Obteniendo nombres de los campos de fecha calculada');
			$calculatedDateFieldNames = array ();
			foreach ($parameters as $parameter) {
				if ($parameter->getName () != 'fieldnames') {
					continue;
				}

				$types = $parameter->getType ();
				foreach ($types as $fieldName => $type) {
					if ($type == BackgroundTaskParameterConfigurationInterface::PARAMETER_TYPE_CALCULATED_DATE) {
						$calculatedDateFieldNames [] = $fieldName;
					}
				}
			}
			return $calculatedDateFieldNames;
		}

		/**
		 * @param array $parameterValues
		 *
		 * @return string
		 * @throws Exception
		 */
		private function getFrequency ($parameterValues) {
			$this->logger->emit ('INFO', 'Obteniendo frecuencia');
			$frequency = $parameterValues ['frequencyfieldname'];
			if (empty ($frequency)) {
				throw new Exception ('No se ha suministrado la frecuencia');
			} else if (!in_array ($frequency, array ('Diaria', 'Semanal', 'Quincenal', 'Mensual'))) {
				throw new Exception ("La frecuencia {$frequency} no es válida. Valores aceptables: Diaria, Semanal, Quincenal, Mensual");
			}
			return $frequency;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return string
		 * @throws Exception
		 */
		private function getModuleFilePath ($moduleName) {
			$this->logger->emit ('INFO', "Localizando el archivo {$moduleName}.php");
			$moduleFilePath = "modules/{$moduleName}/{$moduleName}.php";
			if (!file_exists (__DIR__ . "/../../../{$moduleFilePath}")) {
				throw new Exception ("No se encuentra el archivo {$moduleFilePath}");
			}
			return $moduleFilePath;
		}

		/**
		 * @param array $parameterValues
		 *
		 * @return string
		 * @throws Exception
		 */
		private function getSelectedEndDate ($parameterValues) {
			$this->logger->emit ('INFO', 'Obteniendo fecha de fin');
			$endDate = $parameterValues ['enddatefieldname'];
			if (empty ($endDate)) {
				throw new Exception ('No se ha suministrado la fecha de fin');
			}
			return $endDate;
		}

		/**
		 * @param string $frequency
		 * @param array $parameterValues
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function getSelectedMonthDays ($frequency, $parameterValues) {
			if ($frequency != 'Mensual') {
				return null;
			}

			$this->logger->emit ('INFO', 'Obteniendo días del mes');
			$selectedMonthDays = !is_array ($parameterValues ['monthdaysfieldname']) ? explode (' |##| ', $parameterValues ['monthdaysfieldname']) : $parameterValues ['monthdaysfieldname'];
			if (empty ($selectedMonthDays)) {
				throw new Exception ('No se han suministrado los días del mes');
			}
			return $selectedMonthDays;
		}

		/**
		 * @param array $parameterValues
		 *
		 * @return string
		 * @throws Exception
		 */
		private function getSelectedStartDate ($parameterValues) {
			$this->logger->emit ('INFO', 'Obteniendo fecha de inicio');
			$startDate = $parameterValues ['startdatefieldname'];
			if (empty ($startDate)) {
				throw new Exception ('No se ha suministrado la fecha de inicio');
			}
			return $startDate;
		}

		/**
		 * @param string $frequency
		 * @param array $parameterValues
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private function getSelectedWeekDayNumbers ($frequency, $parameterValues) {
			if ($frequency != 'Semanal') {
				return null;
			}

			$this->logger->emit ('INFO', 'Obteniendo días de la semana');
			$selectedWeekDays = !is_array ($parameterValues ['weekdaysfieldname']) ? explode (' |##| ', $parameterValues ['weekdaysfieldname']) : $parameterValues ['weekdaysfieldname'];
			if (empty ($selectedWeekDays)) {
				throw new Exception ('No se han suministrado los días de la semana');
			} else {
				foreach ($selectedWeekDays as $selectedWeekDay) {
					if (!in_array ($selectedWeekDay, array ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'))) {
						throw new Exception ("El día de la semana {$selectedWeekDay} no es válido. Valores aceptables: Lunes, Martes, Miércoles, Jueves, Viernes, Sábado, Domingo");
					}
				}
			}
			$weekDayNumbers         = array (
				'Lunes'     => 1,
				'Martes'    => 2,
				'Miércoles' => 3,
				'Jueves'    => 4,
				'Viernes'   => 5,
				'Sábado'    => 6,
				'Domingo'   => 7,
			);
			$selectedWeekDayNumbers = array ();
			foreach ($selectedWeekDays as $selectedWeekDay) {
				if (isset ($weekDayNumbers [ $selectedWeekDay ])) {
					$selectedWeekDayNumbers [] = $weekDayNumbers [ $selectedWeekDay ];
				}
			}
			return $selectedWeekDayNumbers;
		}

		/**
		 * @param BackgroundTaskAction $action
		 *
		 * @return array
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

			$this->logger->emit ('INFO', 'Obteniendo parámetros');
			$requestedParameterNames = array ('enddatefieldname', 'fieldnames', 'frequencyfieldname', 'modulename', 'monthdaysfieldname', 'startdatefieldname', 'weekdaysfieldname');
			$parameterValues         = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			$moduleName              = $parameterValues ['modulename'];

			$frequency                = $this->getFrequency ($parameterValues);
			$selectedWeekDayNumbers   = $this->getSelectedWeekDayNumbers ($frequency, $parameterValues);
			$selectedMonthDays        = $this->getSelectedMonthDays ($frequency, $parameterValues);
			$startDate                = $this->getSelectedStartDate ($parameterValues);
			$endDate                  = $this->getSelectedEndDate ($parameterValues);
			$calculatedDateFieldNames = $this->getCalculatedDateFieldName ($parameters);
			$moduleFilePath           = $this->getModuleFilePath ($moduleName);

			$dummy    = $this->getParameterValues ($parameters, array ('relateto'), false);
			$relateTo = $dummy ['relateto'];

			$output = array ();
			require_once ($moduleFilePath);
			$interval        = new DateInterval ('P1D');
			$dummyDateObject = date_create ($startDate);
			$endDateObject   = date_create ($endDate);
			while ($dummyDateObject <= $endDateObject) {
				$dummyDateWeekDayNumber = $dummyDateObject->format ('N');
				$dummyDateDayNumber = $dummyDateObject->format ('d');
				if (
					($frequency == 'Diaria') ||
					(($frequency == 'Semanal') && (in_array ($dummyDateWeekDayNumber, $selectedWeekDayNumbers))) ||
					(($frequency == 'Quincenal') && (in_array ($dummyDateDayNumber, array ('01', '15')))) ||
					(($frequency == 'Mensual') && (in_array ($dummyDateDayNumber, $selectedMonthDays)))
				) {
					$this->logger->emit ('INFO', "Creando objeto del tipo {$moduleName}");
					/** @var CRMEntity|stdClass $entity */
					$entity = new $moduleName ();

					$this->logger->emit ('INFO', 'Insertando data');
					$fieldValues = $parameterValues ['fieldnames'];
					foreach ($fieldValues as $fieldName => $fieldValue) {
						if (in_array ($fieldName, $calculatedDateFieldNames)) {
							$entity->column_fields [ $fieldName ] = $dummyDateObject->format ('Y-m-d');
						} else {
							$entity->column_fields [ $fieldName ] = $fieldValue;
						}
					}

					$this->logger->emit ('INFO', 'Guardando');
					$entity->save ($moduleName);
					/** @noinspection PhpUndefinedFieldInspection */
					if (!empty ($this->adb->database->_errorMsg)) {
						/** @noinspection PhpUndefinedFieldInspection */
						throw new Exception ("Se ha presentado un error: {$this->adb->database->_errorMsg}");
					}
					$this->relateEntities ($entity, $relateTo);
					$output [] = $entity->id;
				}
				$dummyDateObject->add ($interval);
			}
			return $output;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return CreateScheduledEntitiesAction|null
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

	}

<?php
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');

	class ModifyEntityAction extends BackgroundTaskActionHandler {

		/**
		 * @param string $moduleName
		 * @param integer $entityId
		 *
		 * @return CRMEntity|stdClass
		 * @throws Exception
		 */
		private function getCrmEntity ($moduleName, $entityId) {
			$this->logger->emit ('INFO', "Localizando el archivo {$moduleName}.php");
			$moduleFilePath = "modules/{$moduleName}/{$moduleName}.php";
			if (!file_exists (__DIR__ . "/../../../{$moduleFilePath}")) {
				throw new Exception ("No se encuentra el archivo {$moduleFilePath}");
			}

			$this->logger->emit ('INFO', "Creando objeto del tipo {$moduleName}");
			$entity = PlatformUtils::getCrmEntity ($this->adb, $moduleName, $entityId);
			return $entity;
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

			$this->logger->emit ('INFO', 'Obteniendo parámetros');
			$requestedParameterNames = array ('entityid', 'modulename');
			$parameterValues         = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			$moduleName      = $parameterValues ['modulename'];
			$entityId        = $parameterValues ['entityid'];
			$dummy = $this->getParameterValues ($parameters, array ('relateto'), false);
			$relateTo = $dummy ['relateto'];

			$fieldValues = array ();
			$fieldTypes = array ();
			foreach ($parameters as $parameter) {
				$parameterName = $parameter->getName ();
				if ($parameterName == 'fieldnames') {
					$fieldValues = $parameter->getValue ();
					$fieldTypes = $parameter->getType ();
				}
			}

			$this->logger->emit ('INFO', 'Obteniendo el registro a modificar');
			$result = $this->adb->pquery ('SELECT * FROM vtiger_crmentity crme WHERE crme.deleted=0 AND crme.setype=? AND crme.crmid=?', array ($moduleName, $entityId));
			if ($this->adb->num_rows ($result) == 0) {
				throw new Exception ("El registro con el ID {$entityId} no se encuentra registrado, ha sido borrado o no es del tipo {$moduleName}");
			}
			$entity = $this->getCrmEntity ($moduleName, $entityId);

			$this->logger->emit ('INFO', 'Actualizando registro');
			$entity->mode = 'edit';
			foreach ($fieldValues as $fieldName => $fieldValue) {
				if (!empty ($fieldTypes [$fieldName])) {
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

			return $entity->id;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return ModifyEntityAction
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

	}

<?php
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');

	class SynchronizeEntitiesAction extends BackgroundTaskActionHandler {

		/**
		 * @param string $moduleName
		 *
		 * @return array
		 */
		private function getNonSynchronizableFields ($moduleName) {
			$nonSynchronizableFields = array ('record_id');
			$fields                  = Vtiger_Module::getInstance ($moduleName)->getFields ();
			/** @var Vtiger_Field $field */
			foreach ($fields as $field) {
				if (!in_array ($field->uitype, array (3, 4, 10, 51, 53, 70))) {
					continue;
				}
				$nonSynchronizableFields [] = $field->name;
			}
			return $nonSynchronizableFields;
		}

		/**
		 * @param string $sourceInstanceName
		 * @param string $sourceModuleName
		 * @param integer $sourceEntityId
		 * @param string $targetInstanceName
		 * @param string $targetModuleName
		 * @param integer $targetEntityId
		 *
		 * @throws Exception
		 */
		private function validateConfiguration ($sourceInstanceName, $sourceModuleName, $sourceEntityId, $targetInstanceName, $targetModuleName, $targetEntityId) {
			if (!$sourceModuleName) {
				throw new Exception ('No se ha suministrado el nombre del módulo de origen');
			}
			if (!$sourceEntityId) {
				throw new Exception ('No se ha suministrado el ID de la entidad de origen');
			}
			if (!$targetInstanceName) {
				throw new Exception ('No se ha suministrado el nombre de la instancia de destino');
			}
			if ($sourceInstanceName == $targetInstanceName) {
				throw new Exception ('No se permite sincronizar entidades de la misma instancia');
			}
			if (!$targetModuleName) {
				throw new Exception ('No se ha suministrado el nombre del módulo de destino');
			}
			if ($sourceModuleName != $targetModuleName) {
				throw new Exception ('No se permite sincronizar entidades de módulos distintos');
			}
			if (!$targetEntityId) {
				throw new Exception ('No se ha suministrado el ID de la entidad de destino');
			}
		}

		/**
		 * @param BackgroundTaskAction $action
		 *
		 * @return null
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
			$requestedParameterNames = array ('sourceinstancename', 'sourcemodulename', 'sourceentityid', 'targetinstancename', 'targetmodulename', 'targetentityid');
			$parameterValues         = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			$sourceInstanceName      = $parameterValues ['sourceinstancename'];
			$sourceModuleName        = $parameterValues ['sourcemodulename'];
			$sourceEntityId          = $parameterValues ['sourceentityid'];
			$targetInstanceName      = $parameterValues ['targetinstancename'];
			$targetModuleName        = $parameterValues ['targetmodulename'];
			$targetEntityId          = $parameterValues ['targetentityid'];

			$this->logger->emit ('INFO', 'Validando la configuración');
			$this->validateConfiguration ($sourceInstanceName, $sourceModuleName, $sourceEntityId, $targetInstanceName, $targetModuleName, $targetEntityId);

			$this->logger->emit ('INFO', 'Obteniendo lista de campos no sincronizables');
			$nonSynchronizableFields = $this->getNonSynchronizableFields ($sourceModuleName);

			$this->logger->emit ('INFO', "Conectando a la instancia {$sourceInstanceName}");
			$sourceAdb = AdbManager::getInstance ()->getSourceInstanceAdb ($sourceInstanceName);

			$this->logger->emit ('INFO', "Obteniendo {$sourceModuleName} con el ID {$sourceEntityId}");
			$sourceEntity = PlatformUtils::getCrmEntity ($sourceAdb, $sourceModuleName, $sourceEntityId);
			$sourceFields = $sourceEntity->column_fields;

			$targetAdb    = AdbManager::getInstance ()->getTargetInstanceAdb ($targetInstanceName);
			$targetEntity = PlatformUtils::getCrmEntity ($targetAdb, $targetModuleName, $targetEntityId);
			$targetFields = $targetEntity->column_fields;

			$hasChanges = false;
			foreach ($sourceFields as $sourceFieldName => $sourceFieldValue) {
				if ((!in_array ($sourceFieldName, $nonSynchronizableFields)) && ($sourceFields [ $sourceFieldName ] !== $targetFields [ $sourceFieldName ])) {
					$hasChanges                                       = true;
					$targetEntity->column_fields [ $sourceFieldName ] = html_entity_decode ($sourceFieldValue, ENT_QUOTES, 'UTF-8');
				}
			}
			if ($hasChanges) {
				$targetEntity->mode                           = 'edit';
				$targetEntity->column_fields ['modifiedtime'] = date_create ()->format ('Y-m-d h:i:s');
				$targetEntity->saveentity ($sourceModuleName);
			}

			return null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return SynchronizeEntitiesAction
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

	}

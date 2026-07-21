<?php
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandlerInterface.php');
	require_once ('include/platzilla/Managers/BackgroundTaskManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Class BackgroundTaskActionHandler
	 *
	 * La clase "Manejador Acción Tarea Oculta" hace referencia a las funciones que controlan los tipos de acción de una
	 * "Tarea Oculta" en la "Plataforma" y/o "Instancia". La clase está asociada al gestionador "Gestionador Tarea Oculta".
	 */
	abstract class BackgroundTaskActionHandler implements BackgroundTaskActionHandlerInterface {
		/** @var PearDatabase */
		protected $adb;

		/** @var Logger */
		protected $logger;

		/**
		 * BackgroundTaskActionHandler constructor.
		 *
		 * @param PearDatabase $adb
		 * @param Logger|null $logger
		 */
		protected function __construct (PearDatabase $adb, Logger $logger = null) {
			$this->adb    = $adb;
			$this->logger = $logger;
		}

		/**
		 * Para obtener los valores de los parametros/variables se definiran para la tarea oculta
		 *
		 * @param BackgroundTaskParameter[] $actionParameters
		 * @param string[] $requestedParameterNames
		 * @param boolean $isMandatory
		 *
		 * @return mixed
		 * @throws Exception
		 */
		protected function getParameterValues ($actionParameters, $requestedParameterNames, $isMandatory) {
			$values = array ();
			foreach ($actionParameters as $parameter) {
				$parameterName = $parameter->getName ();
				if (!in_array ($parameterName, $requestedParameterNames)) {
					continue;
				}

				$value = $parameter->getValue ();
				if (($isMandatory) && ($value === null)) {
					throw new Exception ("El parámetro {$parameterName} no puede ser vacío");
				}

				$values [ $parameterName ] = $value;
			}
			return $values;
		}

		/**
		 * Para relacionar las entidades/objetos CRMEntity necesitara la tarea oculta
		 *
		 * @param CRMEntity|stdClass $entity
		 * @param integer[]|integer $targetEntityIds
		 */
		protected function relateEntities (CRMEntity $entity, $targetEntityIds) {
			if ((empty ($entity)) || (empty ($targetEntityIds))) {
				return;
			}

			if (is_array ($targetEntityIds)) {
				$dummy = $targetEntityIds;
			} else {
				$dummy = array ($targetEntityIds);
			}

			foreach ($dummy as $targetEntityId) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_crmentity WHERE crmid=?', array ($targetEntityId));
				if ($this->adb->num_rows ($result) > 0) {
					$row = $this->adb->fetchByAssoc ($result, -1, false);
					$entity->save_related_module (get_class ($entity), $entity->id, $row ['setype'], $targetEntityId);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}

		/**
		 * Para almacenar los objetos grid son empleados en el manejador de las acciones de las tareas ocultas
		 *
		 * @param CRMEntity|stdClass $entity
		 * @param array $gridFieldValues
		 */
		protected function saveGridValues ($entity, $gridFieldValues) {
			if ((!($entity instanceof CRMEntity)) || (!is_array ($gridFieldValues)) || (empty ($gridFieldValues))) {
				return;
			}

			$moduleName = get_class ($entity);
			$entityId   = $entity->id;
			$values     = array ();
			foreach ($gridFieldValues as $fullGridFieldName => $fullGridFieldValues) {
				if (empty ($fullGridFieldValues)) {
					continue;
				}
				$dummy         = explode ('.', $fullGridFieldName);
				$gridName      = $dummy [0];
				$gridFieldName = $dummy [1];
				foreach ($fullGridFieldValues as $index => $fullGridFieldValue) {
					$values [ $gridName ] [ $index ][ $gridFieldName ] = $fullGridFieldValue;
				}
			}
			foreach ($values as $gridName => $gridValues) {
				GridFieldUtils::setGridValues ($this->adb, $moduleName, $gridName, $entityId, $gridValues);
			}
		}

		/**
		 * Para obtener las opciones por defecto de los parametros para el manejador de las funciones empleara la tarea oculta
		 *
		 * @param PearDatabase $adb
		 * @param $parameterConfiguration
		 * @param $selectedParameterValues
		 *
		 * @return array|null
		 */
		public static function getDefaultOptions (PearDatabase $adb, $parameterConfiguration, $selectedParameterValues) {
			if ((empty ($adb)) || (empty ($parameterConfiguration)) || (empty ($selectedParameterValues))) {
				return null;
			}
			return null;
		}

	}

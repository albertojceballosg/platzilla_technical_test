<?php
	require_once ('include/platzilla/Exceptions/BackgroundTaskActionException.php');
	require_once ('include/platzilla/Objects/BackgroundTaskInterface.php');
	require_once ('include/platzilla/Objects/BackgroundTaskParameter.php');

	/**
	 * Class BackgroundTaskAction
	 *
	 * Esta clase define el objeto "Acción Tarea Oculta" el cual hace referencia a los tipos de acciones que pueden
	 * configurarse para una "Tarea Oculta" en la "Plataforma" y/o "Instancia", de manera transparente al usuario.
	 * La clase esta asociada al objeto "Parametro Tarea Oculta".
	 */
	class BackgroundTaskAction {
		/** @var string */
		private $handlerClass;

		/** @var string */
		private $handlerMethod;

		/** @var string */
		private $name;

		/** @var BackgroundTaskParameter[] */
		private $parameters;

		/** @var integer */
		private $sequence;

		/** @var string */
		private $scope;

		/** @var integer */
		private $taskId;

		/** @var string */
		private $type;

		/**
		 * Realiza copia de los parametros de las acciones asociadas a la tarea oculta
		 *
		 * @param BackgroundTaskParameter[] $sourceParameters
		 */
		private function copyParameters ($sourceParameters) {
			$parameters = array ();
			foreach ($sourceParameters as $sourceParameter) {
				$found = false;
				foreach ($this->parameters as $targetParameter) {
					if ($sourceParameter->getName () != $targetParameter->getName ()) {
						continue;
					} else if (!$targetParameter->isEqualTo ($sourceParameter)) {
						$targetParameter->copyValuesFrom ($sourceParameter);
					}
					$parameters [] = $targetParameter;
					$found         = true;
					break;
				}
				if (!$found) {
					$parameters [] = $sourceParameter->duplicate (null, $sourceParameter->getActionName ());
				}
			}
			$this->parameters = $parameters;
		}

		/**
		 * Realiza copia de los parametros de las acciones asociadas a la tarea oculta desde otra tarea oculta
		 *
		 * @param BackgroundTaskAction $action
		 */
		private function copyParametersFrom ($action) {
			$sourceParameters = $action->getParameters ();
			if ((empty ($sourceParameters)) && (empty ($this->parameters))) {
				return;
			}

			if (empty ($sourceParameters)) {
				$this->parameters = null;
			} else if (empty ($this->parameters)) {
				$parameters = array ();
				foreach ($sourceParameters as $sourceParameter) {
					$parameters [] = $sourceParameter->duplicate (null, $sourceParameter->getActionName ());
				}
				$this->parameters = $parameters;
			} else {
				$this->copyParameters ($sourceParameters);
			}
		}

		/**
		 * Duplica los parametros de las acciones asociadas a la tarea oculta
		 *
		 * @return BackgroundTaskParameter[]|null
		 */
		private function duplicateParameters () {
			if (empty ($this->parameters)) {
				return null;
			}
			$parameters = array ();
			foreach ($this->parameters as $parameter) {
				$parameters [] = $parameter->duplicate (null, $parameter->getActionName ());
			}
			return $parameters;
		}

		/**
		 * Para realizar validacion que los parametros asociados a las acciones de las tareas ocultas, esten correctamente
		 *
		 * @throws BackgroundTaskActionException
		 */
		private function validateParameters () {
			if (empty ($this->parameters)) {
				return;
			}

			foreach ($this->parameters as $parameter) {
				if (!($parameter instanceof BackgroundTaskParameter)) {
					throw new BackgroundTaskActionException (BackgroundTaskActionException::ERROR_BACKGROUND_TASK_ACTION_INVALID_PARAMETER);
				} else {
					$parameter->validate ();
				}
			}
		}

		/**
		 * Para obtener la clase que maneja/controla las acciones de la tarea oculta
		 *
		 * @return string
		 */
		public function getHandlerClass () {
			return $this->handlerClass;
		}

		/**
		 * Para obtener los metodos que manejan/controlan las acciones de la tarea oculta
		 *
		 * @return string
		 */
		public function getHandlerMethod () {
			return $this->handlerMethod;
		}

		/**
		 * Para obtener el nombre de la accion para la tarea oculta
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener los parametros asociados a la accion de la tarea oculta
		 *
		 * @return BackgroundTaskParameter[]
		 */
		public function getParameters () {
			return $this->parameters;
		}

		/**
		 * Para obtener la secuencia asociada a la accion de la tarea oculta
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener el alcance de la accion realizara la tarea oculta
		 *
		 * @return string
		 */
		public function getScope () {
			return $this->scope;
		}

		/**
		 * Para obtener el ID de la tarea oculta
		 *
		 * @return integer
		 */
		public function getTaskId () {
			return $this->taskId;
		}

		/**
		 * Para obtener el tipo de accion de la tarea oculta
		 *
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * Establece la clase que maneja/controla las acciones de la tarea oculta
		 *
		 * @param string $handlerClass
		 *
		 * @return BackgroundTaskAction
		 */
		public function setHandlerClass ($handlerClass) {
			$this->handlerClass = $handlerClass;
			return $this;
		}

		/**
		 * Establece los metodos que manejan/controlan las acciones de la tarea oculta
		 *
		 * @param string $handlerMethod
		 *
		 * @return BackgroundTaskAction
		 */
		public function setHandlerMethod ($handlerMethod) {
			$this->handlerMethod = $handlerMethod;
			return $this;
		}

		/**
		 * Establece el nombre de la accion de la tarea oculta
		 *
		 * @param string $name
		 *
		 * @return BackgroundTaskAction
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece los parametros de la accion para la tarea oculta
		 *
		 * @param BackgroundTaskParameter[] $parameters
		 *
		 * @return BackgroundTaskAction
		 */
		public function setParameters ($parameters) {
			if (($parameters === null) || ((is_array ($parameters))) && (!empty ($parameters))) {
				$this->parameters = $parameters;
			}
			return $this;
		}

		/**
		 * Establece la secuencia asociada a la accion de la tarea oculta
		 *
		 * @param integer $sequence
		 *
		 * @return BackgroundTaskAction
		 */
		public function setSequence ($sequence) {
			if ((is_int ($sequence)) && ($sequence > 0)) {
				$this->sequence = $sequence;
			}
			return $this;
		}

		/**
		 * Establece el alcance de la accion realizara la tarea oculta
		 *
		 * @param string $scope
		 *
		 * @return BackgroundTaskAction
		 */
		public function setScope ($scope) {
			if (in_array ($scope, array (BackgroundTaskInterface::SCOPE_SYSTEM, BackgroundTaskInterface::SCOPE_USER))) {
				$this->scope = $scope;
			}
			return $this;
		}

		/**
		 * Establece el ID de la tarea oculta
		 *
		 * @param integer $taskId
		 *
		 * @return BackgroundTaskAction
		 */
		public function setTaskId ($taskId) {
			$this->taskId = $taskId;
			return $this;
		}

		/**
		 * Establece el tipo de accion de la tarea oculta
		 *
		 * @param string $type
		 *
		 * @return BackgroundTaskAction
		 */
		public function setType ($type) {
			$this->type = $type;
			return $this;
		}

		/**
		 * Realiza copia de los valores/atributos definidos para la accion de la tarea oculta
		 *
		 * @param BackgroundTaskAction $action
		 */
		public function copyValuesFrom ($action) {
			if ((empty ($action)) || (!($action instanceof BackgroundTaskAction))) {
				return;
			}

			$this->handlerClass  = $action->getHandlerClass ();
			$this->handlerMethod = $action->getHandlerMethod ();
			$this->name          = $action->getName ();
			$this->sequence      = $action->getSequence ();
			$this->scope         = $action->getScope ();
			$this->type          = $action->getType ();

			$this->copyParametersFrom ($action);
		}

		/**
		 * Duplica las acciones asociadas a la tarea oculta
		 *
		 * @param integer $newTaskId
		 * @param string $newActionName
		 *
		 * @return BackgroundTaskAction
		 */
		public function duplicate ($newTaskId, $newActionName) {
			$this->validate ();

			$object = new self ();
			return $object->setHandlerClass ($this->handlerClass)
				->setHandlerMethod ($this->handlerMethod)
				->setName ($newActionName)
				->setParameters ($this->duplicateParameters ())
				->setSequence ($this->sequence)
				->setScope ($this->scope)
				->setTaskId ($newTaskId)
				->setType ($this->type);
		}

		/**
		 * Compara si los parametros asociados a la accion de la tarea oculta son iguales a otros
		 *
		 * @param BackgroundTaskAction $action
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($action, $deepCheck = true) {
			if (
				(empty ($action)) ||
				(!($action instanceof BackgroundTaskAction)) ||
				($this->handlerClass != $action->getHandlerClass ()) ||
				($this->handlerMethod != $action->getHandlerMethod ()) ||
				($this->name != $action->getName ()) ||
				($this->sequence != $action->getSequence ()) ||
				($this->scope != $action->getScope ()) ||
				($this->type != $action->getType ()) ||
				(($deepCheck) && (!MiscellaneousUtils::areObjectArraysEqual ($this->parameters, $action->getParameters ())))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida si las acciones definidas y establecidas para la tarea oculta estan corretos
		 *
		 * @throws BackgroundTaskActionException
		 */
		public function validate () {
			if (empty ($this->name)) {
				throw new BackgroundTaskActionException (BackgroundTaskActionException::ERROR_BACKGROUND_TASK_ACTION_EMPTY_NAME);
			} else if (empty ($this->type)) {
				throw new BackgroundTaskActionException (BackgroundTaskActionException::ERROR_BACKGROUND_TASK_ACTION_EMPTY_TYPE);
			}
			$this->validateParameters ();
		}

		/**
		 * Instanciación de la clase BackgroundTaskAction. Se obtiene un objeto BackgroundTaskAction con los valores de la clase
		 *
		 * @return BackgroundTaskAction
		 */
		public static function getInstance () {
			return new self ();
		}

	}

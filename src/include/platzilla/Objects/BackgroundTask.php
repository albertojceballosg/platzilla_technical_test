<?php
	require_once ('include/platzilla/Exceptions/BackgroundTaskException.php');
	require_once ('include/platzilla/Objects/BackgroundTaskAction.php');
	require_once ('include/platzilla/Objects/BackgroundTaskFilterGroup.php');
	require_once ('include/platzilla/Objects/BackgroundTaskInterface.php');

	/**
	 * Class BackgroundTask
	 *
	 * Esta clase define el objeto "Tarea oculta" el cual hace referencia a las Tareas Ocultas encargadas de ejecutar en forma automatizada tareas en la "Plataforma" y/o "Instancia",
	 * de manera trasparente al usuario.
	 * La clase está asociada al objeto "Acción Tarea Oculta".
	 */
	class BackgroundTask implements BackgroundTaskInterface {
		/** @var integer */
		private $id;

		/** @var BackgroundTaskAction[] */
		private $actions;

		/** @var string */
		private $category;

		/** @var boolean */
		private $deleted;

		/** @var string */
		private $description;

		/** @var string */
		private $event;

		/** @var string */
		private $eventInstant;

		/** @var BackgroundTaskFilterGroup[] */
		private $filterGroups;

		/** @var integer */
		private $frequency;

		/** @var DateTime */
		private $lastExecutedOn;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		/** @var boolean */
		private $protected;

		/** @var string */
		private $scope;

		/** @var string */
		private $status;

		/** @var string */
		private $trigger;

		/** @var string */
		private $urlVideo;

		/**
		 * BackgroundTask constructor.
		 */
		public function __construct () {
			$this->deleted   = false;
			$this->locked    = false;
			$this->protected = false;
		}

		/**
		 * Realiza copia de las acciones realizara la tarea oculta
		 *
		 * @param BackgroundTaskAction[] $sourceActions
		 */
		private function copyActions ($sourceActions) {
			$actions = array ();
			foreach ($sourceActions as $sourceAction) {
				$found = false;
				foreach ($this->actions as $targetAction) {
					if ($sourceAction->getName () != $targetAction->getName ()) {
						continue;
					} else if (!$targetAction->isEqualTo ($sourceAction)) {
						$targetAction->copyValuesFrom ($sourceAction);
					}
					$actions [] = $targetAction;
					$found      = true;
					break;
				}
				if (!$found) {
					$actions [] = $sourceAction->duplicate (null, $sourceAction->getName ());
				}
			}
			$this->actions = $actions;
		}

		/**
		 * Realiza copia de las acciones realizara la tarea tarea oculta, desde otra tarea oculta
		 *
		 * @param BackgroundTask $task
		 */
		private function copyActionsFrom ($task) {
			$sourceActions = $task->getActions ();
			if ((empty ($sourceActions)) && (empty ($this->actions))) {
				return;
			}

			if (empty ($sourceActions)) {
				$this->actions = null;
			} else if (empty ($this->actions)) {
				$actions = array ();
				foreach ($sourceActions as $sourceAction) {
					$actions [] = $sourceAction->duplicate (null, $sourceAction->getName ());
				}
				$this->actions = $actions;
			} else {
				$this->copyActions ($sourceActions);
			}
		}

		/**
		 * Copia los grupos de filtro de la tarea oculta
		 *
		 * @param BackgroundTaskFilterGroup[] $sourceGroups
		 */
		private function copyFilterGroups ($sourceGroups) {
			$groups = array ();
			foreach ($sourceGroups as $sourceGroup) {
				$found = false;
				foreach ($this->filterGroups as $targetGroup) {
					if ($sourceGroup->getId () != $targetGroup->getId ()) {
						continue;
					} else if (!$targetGroup->isEqualTo ($sourceGroup)) {
						$targetGroup->copyValuesFrom ($sourceGroup);
					}
					$groups [] = $targetGroup;
					$found     = true;
					break;
				}
				if (!$found) {
					$groups [] = $sourceGroup->duplicate (null, $sourceGroup->getId ());
				}
			}
			$this->filterGroups = $groups;
		}

		/**
		 * Copia los grupos de filtro de la tarea desde otra tarea
		 *
		 * @param BackgroundTask $task
		 */
		private function copyFilterGroupsFrom ($task) {
			$sourceGroups = $task->getFilterGroups ();
			if ((empty ($sourceGroups)) && (empty ($this->filterGroups))) {
				return;
			}

			if (empty ($sourceGroups)) {
				$this->actions = null;
			} else if (empty ($this->filterGroups)) {
				$groups = array ();
				foreach ($sourceGroups as $sourceGroup) {
					$groups [] = $sourceGroup->duplicate (null, $sourceGroup->getId ());
				}
				$this->filterGroups = $groups;
			} else {
				$this->copyFilterGroups ($sourceGroups);
			}
		}

		/**
		 * Duplica las acciones que realiza una tarea oculta
		 *
		 * @return BackgroundTaskAction[]|null
		 */
		private function duplicateActions () {
			if (empty ($this->actions)) {
				return null;
			}
			$actions = array ();
			foreach ($this->actions as $action) {
				$actions [] = $action->duplicate (null, $action->getName ());
			}
			return $actions;
		}

		/**
		 * Duplica el grupo de filtros asociados a la tarea oculta
		 *
		 * @return BackgroundTaskFilterGroup[]|null
		 */
		private function duplicateFilterGroups () {
			if (empty ($this->filterGroups)) {
				return null;
			}
			$groups = array ();
			foreach ($this->filterGroups as $group) {
				$groups [] = $group->duplicate (null, $group->getId ());
			}
			return $groups;
		}

		/**
		 * Valida los atributos (no este vacia las acciones y sean validas) para las acciones de la tarea oculta
		 *
		 * @throws BackgroundTaskException
		 */
		private function validateActions () {
			if (empty ($this->actions)) {
				throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_EMPTY_ACTIONS);
			}

			foreach ($this->actions as $action) {
				if (!($action instanceof BackgroundTaskAction)) {
					throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_INVALID_ACTION);
				} else {
					$action->validate ();
				}
			}
		}

		/**
		 * Valida que el grupo de filtros asociados a la tarea oculta esten correctos
		 *
		 * @throws BackgroundTaskException
		 * @throws FilterException
		 * @throws FilterGroupException
		 */
		private function validateFilterGroups () {
			if (empty ($this->filterGroups)) {
				return;
			}

			foreach ($this->filterGroups as $group) {
				if (!($group instanceof BackgroundTaskFilterGroup)) {
					throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_INVALID_FILTER_GROUP);
				} else {
					$group->validate ();
				}
			}
		}

		/**
		 * Para obtener el id asociado a la tarea oculta
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Obtiene el grupo de acciones de la tarea oculta
		 *
		 * @return BackgroundTaskAction[]
		 */
		public function getActions () {
			return $this->actions;
		}

		/**
		 * Para obtener la categoria tendra asociada la tarea oculta
		 *
		 * @return string
		 */
		public function getCategory () {
			return $this->category;
		}

		/**
		 * Para obtener la descripcion de la tarea oculta
		 *
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * Obtiene las condiciones/eventos que accionan la tarea oculta
		 *
		 * @return string
		 */
		public function getEvent () {
			return $this->event;
		}

		/**
		 * Obtiene el evento instantaneo que acciona la tarea oculta
		 *
		 * @return string
		 */
		public function getEventInstant () {
			return $this->eventInstant;
		}

		/**
		 * Obtiene el grupo de filtros asociados a la tarea oculta
		 *
		 * @return BackgroundTaskFilterGroup[]
		 */
		public function getFilterGroups () {
			return $this->filterGroups;
		}

		/**
		 * Para obtener la frecuencia en que se accionara la tarea oculta
		 *
		 * @return integer
		 */
		public function getFrequency () {
			return $this->frequency;
		}

		/**
		 * Para obtener la ultima ejecucion de la tarea oculta
		 *
		 * @return DateTime
		 */
		public function getLastExecutedOn () {
			return $this->lastExecutedOn;
		}

		/**
		 * Para obtener el nombre del modulo donde ocurrira la tarea oculta
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener el nombre asignado a la tarea oculta
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener el alcance de la tarea oculta
		 *
		 * @return string
		 */
		public function getScope () {
			return $this->scope;
		}

		/**
		 * Para obtener el estatus (Habilitado o Deshabilitado) de la tarea oculta
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Obtiene el procedimiento (Trigger) que se ejecuta a nivel de la BD segun las condiciones de la tarea oculta
		 *
		 * @return string
		 */
		public function getTrigger () {
			return $this->trigger;
		}

		/**
		 * @return string
		 */
		public function getUrlVideo () {
			return $this->urlVideo;
		}

		/**
		 * Para validar si la tarea oculta esta protegida
		 *
		 * @return boolean
		 */
		public function isProtected () {
			return $this->protected;
		}

		/**
		 * Para realizar el borrado de la tarea oculta
		 *
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * Obtiene el valor de la bandera que controla si la tarea oculta puede bloquearse o no
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Establece el id asociado a la tarea oculta
		 *
		 * @param integer $id
		 *
		 * @return BackgroundTask
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece el grupo de acciones de la tarea oculta
		 *
		 * @param BackgroundTaskAction[] $actions
		 *
		 * @return BackgroundTask
		 */
		public function setActions ($actions) {
			if (($actions == null) || ((is_array ($actions)) && (!empty ($actions)))) {
				$this->actions = $actions;
			}
			return $this;
		}

		/**
		 * Establece la categoria tendra asociada la tarea oculta
		 *
		 * @param string $category
		 *
		 * @return BackgroundTask
		 */
		public function setCategory ($category) {
			$this->category = $category;
			return $this;
		}

		/**
		 * Establece la accion de borrado de la tarea oculta
		 *
		 * @param boolean $deleted
		 *
		 * @return BackgroundTask
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * Establece la descripcion de la tarea oculta
		 *
		 * @param string $description
		 *
		 * @return BackgroundTask
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}

		/**
		 * Establece el evento instantaneo que acciona la tarea oculta
		 *
		 * @param string $event
		 *
		 * @return BackgroundTask
		 */
		public function setEvent ($event) {
			$this->event = $event;
			return $this;
		}

		/**
		 * Establece el evento instantaneo que acciona la tarea oculta
		 *
		 * @param string $eventInstant
		 *
		 * @return BackgroundTask
		 */
		public function setEventInstant ($eventInstant) {
			if (in_array ($eventInstant, array (self::EVENT_INSTANT_AFTER, self::EVENT_INSTANT_BEFORE))) {
				$this->eventInstant = $eventInstant;
			}
			return $this;
		}

		/**
		 * Establece el grupo de filtros asociados a la tarea oculta
		 *
		 * @param BackgroundTaskFilterGroup[] $filterGroups
		 *
		 * @return BackgroundTask
		 */
		public function setFilterGroups ($filterGroups) {
			if (($filterGroups == null) || ((is_array ($filterGroups)) && (!empty ($filterGroups)))) {
				$this->filterGroups = $filterGroups;
			}
			return $this;
		}

		/**
		 * Establece la frecuencia en que se accionara la tarea oculta
		 *
		 * @param integer $frequency
		 *
		 * @return BackgroundTask
		 */
		public function setFrequency ($frequency) {
			if ((is_numeric ($frequency)) && ($frequency >= 0) && (intval ($frequency) == $frequency)) {
				$this->frequency = intval ($frequency);
			}
			return $this;
		}

		/**
		 * Establece la ultima ejecucion de la tarea oculta
		 *
		 * @param DateTime|string $lastExecutedOn
		 *
		 * @return BackgroundTask
		 */
		public function setLastExecutedOn ($lastExecutedOn) {
			if (($lastExecutedOn == null) || ((is_object ($lastExecutedOn)) && ($lastExecutedOn instanceof DateTime))) {
				$this->lastExecutedOn = $lastExecutedOn;
			} else if (is_string ($lastExecutedOn)) {
				$date = DateTime::createFromFormat ('Y-m-d H:i:s', $lastExecutedOn);
				if ($date !== false) {
					$this->lastExecutedOn = $date;
				}
			}
			return $this;
		}

		/**
		 * Establece el valor de la bandera que controla si la tarea oculta puede bloquearse o no
		 *
		 * @param boolean $locked
		 *
		 * @return BackgroundTask
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * Establece el nombre del modulo donde ocurrira la tarea oculta
		 *
		 * @param string $moduleName
		 *
		 * @return BackgroundTask
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece el nombre para la tarea oculta
		 *
		 * @param string $name
		 *
		 * @return BackgroundTask
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece la validacion si la tarea oculta esta protegida
		 *
		 * @param boolean $protected
		 *
		 * @return BackgroundTask
		 */
		public function setProtected ($protected) {
			if (is_bool ($protected)) {
				$this->protected = $protected;
			} else {
				$this->protected = false;
			}
			return $this;
		}

		/**
		 * Establece el alcance de la tarea oculta
		 *
		 * @param string $scope
		 *
		 * @return BackgroundTask
		 */
		public function setScope ($scope) {
			if (in_array ($scope, array (self::SCOPE_SYSTEM, self::SCOPE_USER))) {
				$this->scope = $scope;
			}
			return $this;
		}

		/**
		 * Establece el estatus (Habilitado o Deshabilitado) de la tarea oculta
		 *
		 * @param string $status
		 *
		 * @return BackgroundTask
		 */
		public function setStatus ($status) {
			if (in_array ($status, array (self::STATUS_DISABLED, self::STATUS_ENABLED))) {
				$this->status = $status;
			}
			return $this;
		}

		/**
		 * Establece el procedimiento (Trigger) que se ejecuta a nivel de la BD segun las condiciones de la tarea oculta
		 *
		 * @param string $trigger
		 *
		 * @return BackgroundTask
		 */
		public function setTrigger ($trigger) {
			if (in_array ($trigger, array (self::TRIGGER_EVENT, self::TRIGGER_MANUAL, self::TRIGGER_DAILY_SCHEDULE, self::TRIGGER_TIMED_SCHEDULE))) {
				$this->trigger = $trigger;
				if ($trigger == self::TRIGGER_EVENT) {
					$this->frequency = null;
				} else if ($trigger === self::TRIGGER_MANUAL) {
					$this->event        = null;
					$this->eventInstant = null;
					$this->frequency    = null;
				} else if (in_array ($trigger, array (self::TRIGGER_DAILY_SCHEDULE, self::TRIGGER_TIMED_SCHEDULE))) {
					$this->event        = null;
					$this->eventInstant = null;
				}
			}
			return $this;
		}

		/**
		 * @param string $urlVideo
		 */
		public function setUrlVideo ($urlVideo) {
			if (filter_var ($urlVideo, FILTER_VALIDATE_URL)) {
				$this->urlVideo = $urlVideo;
			} else {
				$this->urlVideo = null;
			}
			return $this;
		}

		/**
		 * Realiza copia de los atributos establecidos para la tarea oculta
		 *
		 * @param BackgroundTask $task
		 */
		public function copyValuesFrom ($task) {
			if ((empty ($task)) || (!($task instanceof BackgroundTask))) {
				return;
			}

			$this->category     = $task->getCategory ();
			$this->description  = $task->getDescription ();
			$this->event        = $task->getEvent ();
			$this->eventInstant = $task->getEventInstant ();
			$this->frequency    = $task->getFrequency ();
			$this->moduleName   = $task->getModuleName ();
			$this->name         = $task->getName ();
			$this->protected    = $task->isProtected ();
			$this->scope        = $task->getScope ();
			$this->status       = $task->getStatus ();
			$this->trigger      = $task->getTrigger ();
			$this->urlVideo     = $task->getUrlVideo ();

			$this->copyActionsFrom ($task);
			$this->copyFilterGroupsFrom ($task);
		}

		/**
		 * Para duplicar la tarea oculta
		 *
		 * @param integer $newTaskId
		 *
		 * @return BackgroundTask
		 */
		public function duplicate ($newTaskId) {
			$this->validate ();

			$object = new self ();
			return $object->setId ($newTaskId)
				->setActions ($this->duplicateActions ())
				->setCategory ($this->getCategory ())
				->setDescription ($this->getDescription ())
				->setEvent ($this->event)
				->setEventInstant ($this->eventInstant)
				->setFilterGroups ($this->duplicateFilterGroups ())
				->setFrequency ($this->frequency)
				->setLastExecutedOn ($this->lastExecutedOn)
				->setModuleName ($this->moduleName)
				->setName ($this->name)
				->setProtected ($this->protected)
				->setScope ($this->scope)
				->setStatus ($this->status)
				->setTrigger ($this->trigger)
				->setUrlVideo ($this->urlVideo);
		}

		/**
		 * Para comparar si una tarea oculta es igual a otra
		 *
		 * @param BackgroundTask $task
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($task, $deepCheck = true) {
			if (
				(empty ($task)) ||
				(!($task instanceof BackgroundTask)) ||
				($this->category != $task->getCategory ()) ||
				($this->description != $task->getDescription ()) ||
				($this->event != $task->getEvent ()) ||
				($this->eventInstant != $task->getEventInstant ()) ||
				($this->frequency != $task->getFrequency ()) ||
				($this->moduleName != $task->getModuleName ()) ||
				($this->name != $task->getName ()) ||
				($this->protected != $task->isProtected ()) ||
				($this->scope != $task->getScope ()) ||
				($this->status != $task->getStatus ()) ||
				($this->trigger != $task->getTrigger ()) ||
				($this->urlVideo != $task->getUrlVideo ()) ||
				(($deepCheck) && ((!MiscellaneousUtils::areObjectArraysEqual ($this->actions, $task->getActions ())) || (!MiscellaneousUtils::areObjectArraysEqual ($this->filterGroups, $task->getFilterGroups ()))))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida que los atributos/valores (nombre, alcance, estatus, trigger, eventos) establecidos para la tarea oculta esten correctamente definidos
		 *
		 * @throws BackgroundTaskException
		 */
		public function validate () {
			if ($this->deleted) {
				return;
			} else if (empty ($this->name)) {
				throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_EMPTY_NAME);
			} else if (empty ($this->scope)) {
				throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_EMPTY_SCOPE);
			} else if (empty ($this->status)) {
				throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_EMPTY_STATUS);
			} else if (empty ($this->trigger)) {
				throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_EMPTY_TRIGGER);
			} else if ($this->trigger == self::TRIGGER_EVENT) {
				if (empty ($this->event)) {
					throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_EMPTY_EVENT);
				} else if (empty ($this->eventInstant)) {
					throw new BackgroundTaskException (BackgroundTaskException::ERROR_BACKGROUND_TASK_EMPTY_EVENT_INSTANT);
				}
			}
			$this->validateActions ();
			$this->validateFilterGroups ();
		}

		/**
		 * Instanciación de la clase BackgroundTask. Se obtiene un objeto BackgroundTask con los valores de la clase
		 *
		 * @return BackgroundTask
		 */
		public static function getInstance () {
			return new self ();
		}

	}

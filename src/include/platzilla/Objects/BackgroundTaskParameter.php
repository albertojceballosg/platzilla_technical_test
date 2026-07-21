<?php
	require_once ('include/platzilla/Configuration/BackgroundTaskParameterConfigurationInterface.php');
	require_once ('include/platzilla/Exceptions/BackgroundTaskParameterException.php');

	/**
	 * Class BackgroundTaskParameter
	 *
	 * La clase "Parametro Tarea Oculta" hace referencia a los parametros/variables que pueden configurarse en la plantilla/template
	 * de una tarea oculta en la "Plataforma" y/o "Instancia".
	 * La clase está asociada al objeto "Acción Tarea Oculta".
	 */
	class BackgroundTaskParameter implements BackgroundTaskParameterConfigurationInterface {
		/** @var string */
		private $actionName;

		/** @var string */
		private $actionType;

		/** @var string[] */
		private $availableTypes;

		/** @var mixed */
		private $defaultOptions;

		/** @var string */
		private $defaultOptionsType;

		/** @var string */
		private $defaultOptionsFormula;

		/** @var boolean */
		private $isMandatory;

		/** @var boolean */
		private $isMultiValued;

		/** @var string */
		private $name;

		/** @var boolean */
		private $refreshOnChanges;

		/** @var integer */
		private $sequence;

		/** @var boolean */
		private $showExpanded;

		/** @var integer */
		private $taskId;

		/** @var string */
		private $translationModule;

		/** @var array|string */
		private $type;

		/** @var string */
		private $value;

		/** @var array|string */
		private $valueFormula;

		/**
		 * BackgroundTaskParameter constructor.
		 */
		public function __construct () {
			$this->isMandatory   = false;
			$this->isMultiValued = false;
			$this->showExpanded  = false;
		}

		/**
		 * Para obtener el nombre de la accion de la tarea oculta
		 *
		 * @return string
		 */
		public function getActionName () {
			return $this->actionName;
		}

		/**
		 * Para obtener el tipo de accion de la tarea oculta
		 *
		 * @return string
		 */
		public function getActionType () {
			return $this->actionType;
		}

		/**
		 * Para obtener los tipos de parametros/variables disponibles para la tarea oculta
		 *
		 * @return string[]
		 */
		public function getAvailableTypes () {
			return $this->availableTypes;
		}

		/**
		 * Para obtener las opciones por defecto de los parametros se definiran para la tarea oculta
		 *
		 * @return mixed
		 */
		public function getDefaultOptions () {
			return $this->defaultOptions;
		}

		/**
		 * Para obtener los tipos de opciones por defectos para los parametros se definiran para la tarea oculta
		 *
		 * @return string
		 */
		public function getDefaultOptionsType () {
			return $this->defaultOptionsType;
		}

		/**
		 * Para obtener la formula de las opciones por defecto para los parametros se definiran para la tarea oculta
		 *
		 * @return string
		 */
		public function getDefaultOptionsFormula () {
			return $this->defaultOptionsFormula;
		}

		/**
		 * Valida si el parametro para la tarea oculta es obligatorio
		 *
		 * @return boolean
		 */
		public function isMandatory () {
			return $this->isMandatory;
		}

		/**
		 * Valida si el parametro para la tarea oculta toma multiple valores
		 *
		 * @return boolean
		 */
		public function isMultiValued () {
			return $this->isMultiValued;
		}

		/**
		 * Obtiene el nombre del parametro de la tarea oculta
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para actualizar los cambios realizados en los parametros de la tarea oculta
		 *
		 * @return boolean
		 */
		public function refreshOnChanges () {
			return $this->refreshOnChanges;
		}

		/**
		 * Para obtener la secuencia de los parametros asociados a la tarea oculta
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener el id de la tarea oculta
		 *
		 * @return integer
		 */
		public function getTaskId () {
			return $this->taskId;
		}

		/**
		 * Para obtener el modulo de traduccion de los parametros asociados a la tarea oculta
		 *
		 * @return string
		 */
		public function getTranslationModule () {
			return $this->translationModule;
		}

		/**
		 * Para obtener el tipo de accion de la tarea oculta
		 *
		 * @return array|string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * Para obtener el valor de los parametros asociados a la tarea oculta
		 *
		 * @return string
		 */
		public function getValue () {
			return $this->value;
		}

		/**
		 * Obtiene el valor de la formula empleada para los parametros de la tarea oculta
		 *
		 * @return array|string
		 */
		public function getValueFormula () {
			return $this->valueFormula;
		}

		/**
		 * Para mostrar la expansion de los parametros de la tarea oculta
		 *
		 * @return boolean
		 */
		public function showExpanded () {
			return $this->showExpanded;
		}

		/**
		 * Establece el nombre de la accion de la tarea oculta
		 *
		 * @param string $actionName
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setActionName ($actionName) {
			$this->actionName = $actionName;
			return $this;
		}

		/**
		 * Establece el tipo de accion de la tarea oculta
		 *
		 * @param string $actionType
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setActionType ($actionType) {
			$this->actionType = $actionType;
			return $this;
		}

		/**
		 * Establece los tipos de parametros/variables disponibles para la tarea oculta
		 *
		 * @param string[] $availableTypes
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setAvailableTypes ($availableTypes) {
			if (($availableTypes == null) || ((is_array ($availableTypes)) && (!empty ($availableTypes)))) {
				$this->availableTypes = $availableTypes;
			}
			return $this;
		}

		/**
		 * Establece la formula de las opciones por defecto de los parametros se definiran para la tarea oculta
		 *
		 * @param mixed $defaultOptions
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setDefaultOptions ($defaultOptions) {
			$this->defaultOptions = $defaultOptions;
			return $this;
		}

		/**
		 * Establece los tipos de opciones por defectos para los parametros se definiran para la tarea oculta
		 *
		 * @param string $defaultOptionsType
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setDefaultOptionsType ($defaultOptionsType) {
			if (($defaultOptionsType == null) || (in_array ($defaultOptionsType, array (self::OPTION_TYPE_HANDLER, self::OPTION_TYPE_JSON, self::OPTION_TYPE_LITERAL, self::OPTION_TYPE_SQL)))) {
				$this->defaultOptionsType = $defaultOptionsType;
			}
			return $this;
		}

		/**
		 * Establece la formula de las opciones por defecto para los parametros se definiran para la tarea oculta
		 *
		 * @param string $defaultOptionsFormula
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setDefaultOptionsFormula ($defaultOptionsFormula) {
			$this->defaultOptionsFormula = $defaultOptionsFormula;
			return $this;
		}

		/**
		 * Establece la validacion si el parametro para la tarea oculta es obligatorio
		 *
		 * @param boolean $isMandatory
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setIsMandatory ($isMandatory) {
			if (is_bool ($isMandatory)) {
				$this->isMandatory = $isMandatory;
			}
			return $this;
		}

		/**
		 * Establece la validacion si el parametro para la tarea oculta toma multiple valores
		 *
		 * @param boolean $isMultiValued
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setIsMultiValued ($isMultiValued) {
			if (is_bool ($isMultiValued)) {
				$this->isMultiValued = $isMultiValued;
			}
			return $this;
		}

		/**
		 * Establece el nombre del parametro de la tarea oculta
		 *
		 * @param string $name
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece la actualizacion para los cambios realizados en los parametros de la tarea oculta
		 *
		 * @param boolean $refreshOnChanges
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setRefreshOnChanges ($refreshOnChanges) {
			if (is_bool ($refreshOnChanges)) {
				$this->refreshOnChanges = $refreshOnChanges;
			}
			return $this;
		}

		/**
		 * Establece la secuencia de los parametros asociados a la tarea oculta
		 *
		 * @param integer $sequence
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setSequence ($sequence) {
			if ((is_int ($sequence)) && ($sequence > 0)) {
				$this->sequence = $sequence;
			}
			return $this;
		}

		/**
		 * Establece la accion para mostrar la expansion de los parametros de la tarea oculta
		 *
		 * @param boolean $showExpanded
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setShowExpanded ($showExpanded) {
			if (is_bool ($showExpanded)) {
				$this->showExpanded = $showExpanded;
			}
			return $this;
		}

		/**
		 * Establece el id de la tarea oculta
		 *
		 * @param integer $taskId
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setTaskId ($taskId) {
			$this->taskId = $taskId;
			return $this;
		}

		/**
		 * Establece el modulo de traduccion para los parametros asociados a la tarea oculta
		 *
		 * @param string $translationModule
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setTranslationModule ($translationModule) {
			$this->translationModule = $translationModule;
			return $this;
		}

		/**
		 * Establece el tipo de accion de la tarea oculta
		 *
		 * @param string $type
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setType ($type) {
			$this->type = $type;
			return $this;
		}

		/**
		 * Establece el valor de los parametros asociados a la tarea oculta
		 *
		 * @param string $value
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setValue ($value) {
			$this->value = $value;
			return $this;
		}

		/**
		 * Establece el valor de la formula empleada para los parametros de la tarea oculta
		 *
		 * @param string $formula
		 *
		 * @return BackgroundTaskParameter
		 */
		public function setValueFormula ($formula) {
			$this->valueFormula = $formula;
			return $this;
		}

		/**
		 * Realiza copia de los valores/atributos definidos para los parametros de la tarea oculta
		 *
		 * @param BackgroundTaskParameter $parameter
		 */
		public function copyValuesFrom ($parameter) {
			if ((empty ($parameter)) || (!($parameter instanceof BackgroundTaskParameter))) {
				return;
			}

			$this->actionName            = $parameter->getActionName ();
			$this->actionType            = $parameter->getActionType ();
			$this->availableTypes        = $parameter->getAvailableTypes ();
			$this->defaultOptionsType    = $parameter->getDefaultOptionsType ();
			$this->defaultOptionsFormula = $parameter->getDefaultOptionsFormula ();
			$this->isMandatory           = $parameter->isMandatory ();
			$this->isMultiValued         = $parameter->isMultiValued ();
			$this->name                  = $parameter->getName ();
			$this->refreshOnChanges      = $parameter->refreshOnChanges ();
			$this->sequence              = $parameter->getSequence ();
			$this->showExpanded          = $parameter->showExpanded ();
			$this->translationModule     = $parameter->getTranslationModule ();
			$this->type                  = $parameter->getType ();
			$this->value                 = $parameter->getValue ();
			$this->valueFormula          = $parameter->getValueFormula ();
		}

		/**
		 * Duplica los parametros asociadas a la tarea oculta
		 *
		 * @param integer $newTaskId
		 * @param string $newActionName
		 *
		 * @return BackgroundTaskParameter
		 */
		public function duplicate ($newTaskId, $newActionName) {
			$this->validate ();

			$object = new self ();
			return $object->setActionName ($newActionName)
				->setActionType ($this->actionType)
				->setAvailableTypes ($this->availableTypes)
				->setDefaultOptions ($this->defaultOptions)
				->setDefaultOptionsType ($this->defaultOptionsType)
				->setDefaultOptionsFormula ($this->defaultOptionsFormula)
				->setIsMandatory ($this->isMandatory)
				->setIsMultiValued ($this->isMultiValued)
				->setName ($this->name)
				->setRefreshOnChanges ($this->refreshOnChanges)
				->setSequence ($this->sequence)
				->setShowExpanded ($this->showExpanded)
				->setTaskId ($newTaskId)
				->setTranslationModule ($this->translationModule)
				->setType ($this->type)
				->setValue ($this->value)
				->setValueFormula ($this->valueFormula);
		}

		/**
		 * Compara si los parametros asociados a la tarea oculta son iguales a otros
		 *
		 * @param BackgroundTaskParameter $parameter
		 *
		 * @return boolean
		 */
		public function isEqualTo ($parameter) {
			if (
				(empty ($parameter)) ||
				(!($parameter instanceof BackgroundTaskParameter)) ||
				($this->actionName != $parameter->getActionName ()) ||
				($this->actionType != $parameter->getActionType ()) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->availableTypes, $parameter->getAvailableTypes ())) ||
				($this->defaultOptionsType != $parameter->getDefaultOptionsType ()) ||
				($this->defaultOptionsFormula != $parameter->getDefaultOptionsFormula ()) ||
				($this->isMandatory != $parameter->isMandatory ()) ||
				($this->isMultiValued != $parameter->isMultiValued ()) ||
				($this->name != $parameter->getName ()) ||
				($this->refreshOnChanges != $parameter->refreshOnChanges ()) ||
				($this->sequence != $parameter->getSequence ()) ||
				($this->showExpanded != $parameter->showExpanded ()) ||
				($this->translationModule != $parameter->getTranslationModule ()) ||
				($this->type != $parameter->getType ()) ||
				($this->valueFormula != $parameter->getValueFormula ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida si los parametros definidos y establecidas para la tarea oculta estan corretos
		 *
		 * @throws BackgroundTaskParameterException
		 */
		public function validate () {
			if (empty ($this->actionName)) {
				throw new BackgroundTaskParameterException (BackgroundTaskParameterException::ERROR_BACKGROUND_TASK_PARAMETER_EMPTY_ACTION_NAME);
			} else if (empty ($this->actionType)) {
				throw new BackgroundTaskParameterException (BackgroundTaskParameterException::ERROR_BACKGROUND_TASK_PARAMETER_EMPTY_ACTION_TYPE);
			} else if (empty ($this->name)) {
				throw new BackgroundTaskParameterException (BackgroundTaskParameterException::ERROR_BACKGROUND_TASK_PARAMETER_EMPTY_NAME);
			} else if (!empty ($this->type)) {
				$types = is_array ($this->type) ? $this->type : array ($this->type);
				foreach ($types as $type) {
					if (($type !== null) && (!in_array ($type, array (self::PARAMETER_TYPE_CALCULATED_DATE, self::PARAMETER_TYPE_CUSTOM_SQL, self::PARAMETER_TYPE_EMAIL_SOURCE_FIELD, self::PARAMETER_TYPE_FORMULA, self::PARAMETER_TYPE_INSTANCE_EMAILS, self::PARAMETER_TYPE_JSON, self::PARAMETER_TYPE_LITERAL, self::PARAMETER_TYPE_PREVIOUS_OUTPUT, self::PARAMETER_TYPE_RECORD_ID_SOURCE_FIELD, self::PARAMETER_TYPE_RELATED_SOURCE_FIELD, self::PARAMETER_TYPE_SOURCE_FIELD, self::PARAMETER_TYPE_SOURCE_GRID_FIELD, self::PARAMETER_TYPE_VARIABLE, self::PARAMETER_TYPE_NOTIFICATIONS)))) {
						throw new BackgroundTaskParameterException (BackgroundTaskParameterException::ERROR_BACKGROUND_TASK_PARAMETER_INVALID_TYPE);
					}
				}
			}
		}

		/**
		 * Instanciación de la clase BackgroundTaskParameter. Se obtiene un objeto BackgroundTaskParameter con los valores de la clase
		 *
		 * @return BackgroundTaskParameter
		 */
		public static function getInstance () {
			return new self ();
		}

	}

<?php
	require_once ('include/platzilla/Configuration/BackgroundTaskParameterConfigurationInterface.php');
	require_once ('include/platzilla/Exceptions/BackgroundTaskParameterConfigurationException.php');
	require_once ('include/platzilla/Utils/MiscellaneousUtils.php');

	class BackgroundTaskParameterConfiguration implements BackgroundTaskParameterConfigurationInterface {
		/** @var string */
		private $actionType;

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

		/** @var string[] */
		private $options;

		/** @var integer */
		private $order;

		/** @var boolean */
		private $refreshOnChanges;

		/** @var boolean */
		private $showExpanded;

		/** @var string */
		private $translationModule;

		/**
		 * BackgroundTaskParameterConfiguration constructor.
		 */
		public function __construct () {
			$this->isMandatory   = false;
			$this->isMultiValued = false;
			$this->showExpanded  = false;
		}

		/**
		 * @return string
		 */
		public function getActionType () {
			return $this->actionType;
		}

		/**
		 * @return string
		 */
		public function getDefaultOptionsType () {
			return $this->defaultOptionsType;
		}

		/**
		 * @return string
		 */
		public function getDefaultOptionsFormula () {
			return $this->defaultOptionsFormula;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return string[]
		 */
		public function getOptions () {
			return $this->options;
		}

		/**
		 * @return integer
		 */
		public function getOrder () {
			return $this->order;
		}

		/**
		 * @return string
		 */
		public function getTranslationModule () {
			return $this->translationModule;
		}

		/**
		 * @return boolean
		 */
		public function isMandatory () {
			return $this->isMandatory;
		}

		/**
		 * @return boolean
		 */
		public function isMultiValued () {
			return $this->isMultiValued;
		}

		/**
		 * @return boolean
		 */
		public function refreshOnChanges () {
			return $this->refreshOnChanges;
		}

		/**
		 * @return boolean
		 */
		public function showExpanded () {
			return $this->showExpanded;
		}

		/**
		 * @param string $actionType
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setActionType ($actionType) {
			$this->actionType = $actionType;
			return $this;
		}

		/**
		 * @param string $defaultOptionsType
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setDefaultOptionsType ($defaultOptionsType) {
			if (($defaultOptionsType == null) || (in_array ($defaultOptionsType, array (self::OPTION_TYPE_HANDLER, self::OPTION_TYPE_JSON, self::OPTION_TYPE_LITERAL, self::OPTION_TYPE_SQL)))) {
				$this->defaultOptionsType = $defaultOptionsType;
			}
			return $this;
		}

		/**
		 * @param string $defaultOptionsFormula
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setDefaultOptionsFormula ($defaultOptionsFormula) {
			$this->defaultOptionsFormula = $defaultOptionsFormula;
			return $this;
		}

		/**
		 * @param boolean $isMandatory
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setIsMandatory ($isMandatory) {
			if (is_bool ($isMandatory)) {
				$this->isMandatory = $isMandatory;
			}
			return $this;
		}

		/**
		 * @param boolean $isMultiValued
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setIsMultiValued ($isMultiValued) {
			if (is_bool ($isMultiValued)) {
				$this->isMultiValued = $isMultiValued;
			}
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * @param string[] $options
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setOptions ($options) {
			if (($options === null) || ((is_array ($options))) && (!empty ($options))) {
				$this->options = $options;
			}
			return $this;
		}

		/**
		 * @param integer $order
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setOrder ($order) {
			$this->order = $order;
			return $this;
		}

		/**
		 * @param boolean $refreshOnChanges
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setRefreshOnChanges ($refreshOnChanges) {
			if (is_bool ($refreshOnChanges)) {
				$this->refreshOnChanges = $refreshOnChanges;
			}
			return $this;
		}

		/**
		 * @param boolean $showExpanded
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setShowExpanded ($showExpanded) {
			if (is_bool ($showExpanded)) {
				$this->showExpanded = $showExpanded;
			}
			return $this;
		}

		/**
		 * @param string $translationModule
		 *
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function setTranslationModule ($translationModule) {
			$this->translationModule = $translationModule;
			return $this;
		}

		/**
		 * @param BackgroundTaskParameterConfiguration $parameter
		 */
		public function copyValuesFrom ($parameter) {
			if ((empty ($parameter)) || (!($parameter instanceof BackgroundTaskParameterConfiguration))) {
				return;
			}

			$this->actionType            = $parameter->getActionType ();
			$this->defaultOptionsType    = $parameter->getDefaultOptionsType ();
			$this->defaultOptionsFormula = $parameter->getDefaultOptionsFormula ();
			$this->isMandatory           = $parameter->isMandatory ();
			$this->isMultiValued         = $parameter->isMultiValued ();
			$this->name                  = $parameter->getName ();
			$this->options               = $parameter->getOptions ();
			$this->order                 = $parameter->getOrder ();
			$this->refreshOnChanges      = $parameter->refreshOnChanges ();
			$this->showExpanded          = $parameter->showExpanded ();
			$this->translationModule     = $parameter->getTranslationModule ();
		}

		/**
		 * @return BackgroundTaskParameterConfiguration
		 */
		public function duplicate () {
			$this->validate ();

			$object = new self ();
			return $object->setActionType ($this->actionType)
				->setDefaultOptionsType ($this->defaultOptionsType)
				->setDefaultOptionsFormula ($this->defaultOptionsFormula)
				->setIsMandatory ($this->isMandatory)
				->setIsMultiValued ($this->isMultiValued)
				->setName ($this->name)
				->setOptions ($this->options)
				->setOrder ($this->order)
				->setRefreshOnChanges ($this->refreshOnChanges)
				->setShowExpanded ($this->showExpanded)
				->setTranslationModule ($this->translationModule);
		}

		/**
		 * @param BackgroundTaskParameterConfiguration $parameter
		 *
		 * @return boolean
		 */
		public function isEqualTo ($parameter) {
			if (
				(empty ($parameter)) ||
				(!($parameter instanceof BackgroundTaskParameterConfiguration)) ||
				($this->actionType != $parameter->getActionType ()) ||
				($this->defaultOptionsType != $parameter->getDefaultOptionsType ()) ||
				($this->defaultOptionsFormula != $parameter->getDefaultOptionsFormula ()) ||
				($this->isMandatory != $parameter->isMandatory ()) ||
				($this->isMultiValued != $parameter->isMultiValued ()) ||
				($this->name != $parameter->getName ()) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->options, $parameter->getOptions ())) ||
				($this->order != $parameter->getOrder ()) ||
				($this->refreshOnChanges != $parameter->refreshOnChanges ()) ||
				($this->showExpanded != $parameter->showExpanded ()) ||
				($this->translationModule != $parameter->getTranslationModule ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws BackgroundTaskParameterConfigurationException
		 */
		public function validate () {
			if (empty ($this->actionType)) {
				throw new BackgroundTaskParameterConfigurationException (BackgroundTaskParameterConfigurationException::ERROR_BACKGROUND_TASK_PARAMETER_CONFIGURATION_EMPTY_ACTION_TYPE);
			} else if (empty ($this->name)) {
				throw new BackgroundTaskParameterConfigurationException (BackgroundTaskParameterConfigurationException::ERROR_BACKGROUND_TASK_PARAMETER_CONFIGURATION_EMPTY_PARAMETER_NAME);
			} else if ($this->order === null) {
				throw new BackgroundTaskParameterConfigurationException (BackgroundTaskParameterConfigurationException::ERROR_BACKGROUND_TASK_PARAMETER_CONFIGURATION_EMPTY_PARAMETER_ORDER);
			}
		}

		/**
		 * @return BackgroundTaskParameterConfiguration
		 */
		public static function getInstance () {
			return new self ();
		}

	}

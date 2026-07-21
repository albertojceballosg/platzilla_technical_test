<?php
	require_once ('include/platzilla/Configuration/BackgroundTaskParameterConfiguration.php');
	require_once ('include/platzilla/Exceptions/BackgroundTaskActionConfigurationException.php');
	require_once ('include/platzilla/Objects/BackgroundTaskInterface.php');

	class BackgroundTaskActionConfiguration {
		/** @var string */
		private $handlerClass;

		/** @var string */
		private $handlerMethod;

		/** @var BackgroundTaskParameterConfiguration[] */
		private $parameters;

		/** @var string */
		private $scope;

		/** @var string */
		private $type;

		/**
		 * @param BackgroundTaskParameterConfiguration[] $sourceParameters
		 */
		private function copyParameters ($sourceParameters) {
			$parameters = array ();
			foreach ($sourceParameters as $sourceParameter) {
				$found = false;
				foreach ($this->parameters as $targetParameter) {
					if (
						($sourceParameter->getActionType () != $targetParameter->getActionType ()) ||
						($sourceParameter->getName () != $targetParameter->getName ())
					) {
						continue;
					} else if (!$targetParameter->isEqualTo ($sourceParameter)) {
						$targetParameter->copyValuesFrom ($sourceParameter);
					}
					$parameters [] = $targetParameter;
					$found         = true;
					break;
				}
				if (!$found) {
					$parameters [] = $sourceParameter->duplicate ();
				}
			}
			$this->parameters = $parameters;
		}

		/**
		 * @param BackgroundTaskActionConfiguration $action
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
					$parameters [] = $sourceParameter->duplicate ();
				}
				$this->parameters = $parameters;
			} else {
				$this->copyParameters ($sourceParameters);
			}
		}

		/**
		 * @return BackgroundTaskParameterConfiguration[]|null
		 */
		private function duplicateParameters () {
			if (empty ($this->parameters)) {
				return null;
			}
			$parameters = array ();
			foreach ($this->parameters as $parameter) {
				$parameters [] = $parameter->duplicate ();
			}
			return $parameters;
		}

		/**
		 * @throws BackgroundTaskActionConfigurationException
		 */
		private function validateParameters () {
			if (empty ($this->parameters)) {
				return;
			}

			foreach ($this->parameters as $parameter) {
				if (!($parameter instanceof BackgroundTaskParameterConfiguration)) {
					throw new BackgroundTaskActionConfigurationException (BackgroundTaskActionConfigurationException::ERROR_BACKGROUND_TASK_ACTION_CONFIGURATION_INVALID_PARAMETER);
				} else {
					$parameter->validate ();
				}
			}
		}

		/**
		 * @return string
		 */
		public function getHandlerClass () {
			return $this->handlerClass;
		}

		/**
		 * @return string
		 */
		public function getHandlerMethod () {
			return $this->handlerMethod;
		}

		/**
		 * @return BackgroundTaskParameterConfiguration[]
		 */
		public function getParameters () {
			return $this->parameters;
		}

		/**
		 * @return string
		 */
		public function getScope () {
			return $this->scope;
		}

		/**
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * @param string $handlerClass
		 *
		 * @return BackgroundTaskActionConfiguration
		 */
		public function setHandlerClass ($handlerClass) {
			$this->handlerClass = $handlerClass;
			return $this;
		}

		/**
		 * @param string $handlerMethod
		 *
		 * @return BackgroundTaskActionConfiguration
		 */
		public function setHandlerMethod ($handlerMethod) {
			$this->handlerMethod = $handlerMethod;
			return $this;
		}

		/**
		 * @param BackgroundTaskParameterConfiguration[] $parameters
		 *
		 * @return BackgroundTaskActionConfiguration
		 */
		public function setParameters ($parameters) {
			if (($parameters === null) || ((is_array ($parameters))) && (!empty ($parameters))) {
				$this->parameters = $parameters;
			}
			return $this;
		}

		/**
		 * @param string $scope
		 *
		 * @return BackgroundTaskActionConfiguration
		 */
		public function setScope ($scope) {
			if (in_array ($scope, array (BackgroundTaskInterface::SCOPE_SYSTEM, BackgroundTaskInterface::SCOPE_USER))) {
				$this->scope = $scope;
			}
			return $this;
		}

		/**
		 * @param string $type
		 *
		 * @return BackgroundTaskActionConfiguration
		 */
		public function setType ($type) {
			$this->type = $type;
			return $this;
		}

		/**
		 * @param BackgroundTaskActionConfiguration $action
		 */
		public function copyValuesFrom ($action) {
			if ((empty ($action)) || (!($action instanceof BackgroundTaskActionConfiguration))) {
				return;
			}

			$this->handlerClass  = $action->getHandlerClass ();
			$this->handlerMethod = $action->getHandlerMethod ();
			$this->scope         = $action->getScope ();
			$this->type          = $action->getType ();

			$this->copyParametersFrom ($action);
		}

		/**
		 * @return BackgroundTaskActionConfiguration
		 */
		public function duplicate () {
			$this->validate ();

			$object = new self ();
			return $object->setHandlerClass ($this->handlerClass)
				->setHandlerMethod ($this->handlerMethod)
				->setParameters ($this->duplicateParameters ())
				->setScope ($this->scope)
				->setType ($this->type);
		}

		/**
		 * @param BackgroundTaskActionConfiguration $action
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($action, $deepCheck = true) {
			if (
				(empty ($action)) ||
				(!($action instanceof BackgroundTaskActionConfiguration)) ||
				($this->handlerClass != $action->getHandlerClass ()) ||
				($this->handlerMethod != $action->getHandlerMethod ()) ||
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
		 * @throws BackgroundTaskActionConfigurationException
		 */
		public function validate () {
			if (empty ($this->handlerClass)) {
				throw new BackgroundTaskActionConfigurationException (BackgroundTaskActionConfigurationException::ERROR_BACKGROUND_TASK_ACTION_CONFIGURATION_EMPTY_HANDLER_CLASS);
			} else if (empty ($this->handlerMethod)) {
				throw new BackgroundTaskActionConfigurationException (BackgroundTaskActionConfigurationException::ERROR_BACKGROUND_TASK_ACTION_CONFIGURATION_EMPTY_HANDLER_METHOD);
			} else if (empty ($this->type)) {
				throw new BackgroundTaskActionConfigurationException (BackgroundTaskActionConfigurationException::ERROR_BACKGROUND_TASK_ACTION_CONFIGURATION_EMPTY_TYPE);
			}
			$this->validateParameters ();
		}

		/**
		 * @return BackgroundTaskActionConfiguration
		 */
		public static function getInstance () {
			return new self ();
		}

	}

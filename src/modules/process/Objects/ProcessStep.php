<?php
	require_once ('modules/process_steps/Objects/StepsTypeInterface.php');
	class ProcessStep implements StepsTypeInterface {
		
		/** @var string */
		private $actionOnStep;
		
		/** @var string */
		private $actionOnTask;
		
		/** @var integer */
		private $processId;
		
		/** @var string */
		private $relatedTab;
		
		/** @var integer */
		private $sequence;
		
		/** @var string */
		private $stepCode;
		
		/** @var integer */
		private $stepId;
		
		/** @var string */
		private $stepName;
		
		/** @var string */
		private $stepResponsibleRole;
		
		/** @var string */
		private $stepState;
		
		/** @var string */
		private $stepType;
		
		/**
		 * @return string
		 */
		public function getActionOnStep () {
			return $this->actionOnStep;
		}
		
		/**
		 * @return string
		 */
		public function getActionOnTask () {
			return $this->actionOnTask;
		}
		
		/**
		 * @return integer
		 */
		public function getProcessId () {
			return $this->processId;
		}
		
		/**
		 * @return string
		 */
		public function getRelatedTab () {
			return $this->relatedTab;
		}
		
		/**
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}
		
		/**
		 * @return string
		 */
		public function getStepCode () {
			return $this->stepCode;
		}
		
		/**
		 * @return integer
		 */
		public function getStepId () {
			return $this->stepId;
		}
		
		/**
		 * @return string
		 */
		public function getStepName () {
			return $this->stepName;
		}
		
		/**
		 * @return string
		 */
		public function getStepResponsibleRole () {
			return $this->stepResponsibleRole;
		}
		
		/**
		 * @return string
		 */
		public function getStepState () {
			return $this->stepState;
		}
		
		/**
		 * @return string
		 */
		public function getStepType () {
			return $this->stepType;
		}
		
		/**
		 * @param string $actionOnStep
		 *
		 * @return ProcessStep
		 */
		public function setActionOnStep ($actionOnStep) {
			$this->actionOnStep = $actionOnStep;
			return $this;
		}
		
		/**
		 * @param string $actionOnTask
		 *
		 * @return ProcessStep
		 */
		public function setActionOnTask ($actionOnTask) {
			$this->actionOnTask = $actionOnTask;
			return $this;
		}
		
		/**
		 * @param integer $processId
		 *
		 * @return ProcessStep
		 */
		public function setProcessId ($processId) {
			$this->processId = $processId;
			return $this;
		}
		
		/**
		 * @param string $relatedTab
		 *
		 * @return ProcessStep
		 */
		public function setRelatedTab ($relatedTab) {
			$this->relatedTab = $relatedTab;
			return $this;
		}
		
		/**
		 * @param integer $sequence
		 *
		 * @return ProcessStep
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}
		
		
		/**
		 * @param string $stepCode
		 *
		 * @return ProcessStep
		 */
		public function setRelatedTabFromModule ($moduleName) {
			$this->relatedTab = $moduleName;
			return $this;
		}
		
		/**
		 * @param string $stepCode
		 *
		 * @return ProcessStep
		 */
		public function setStepCode ($stepCode) {
			$this->stepCode = $stepCode;
			return $this;
		}
		
		/**
		 * @param integer $stepId
		 *
		 * @return ProcessStep
		 */
		public function setStepId ($stepId) {
			$this->stepId = $stepId;
			return $this;
		}
		
		/**
		 * @param string $stepName
		 *
		 * @return ProcessStep
		 */
		public function setStepName ($stepName) {
			$this->stepName = $stepName;
			return $this;
		}
		
		/**
		 * @param string $stepResponsibleRole
		 *
		 * @return ProcessStep
		 */
		public function setResponsibleRole ($role) {
			$this->stepResponsibleRole = $role;
			return $this;
		}
		
		/**
		 * @param string $stepState
		 *
		 * @return ProcessStep
		 */
		public function setStepState ($stepState) {
			$this->stepState = $stepState;
			return $this;
		}
		
		/**
		 * @param string $stepType
		 *
		 * @return ProcessStep
		 */
		public function setStepType ($stepType) {
			$this->stepType = $stepType;
			return $this;
		}
		
		/**
		 * @return ProcessStep
		 */
		public static function getInstance () {
			return new self ();
		}
	
		
	}

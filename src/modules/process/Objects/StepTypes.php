<?php
	require_once ('modules/process_steps/Objects/StepsTypeInterface.php');
	class StepTypes implements StepsTypeInterface {
		
		/** @var string */
		private $stepComments;
		
		/** @var string */
		private $stepModule;
		
		/** @var integer */
		private $stepTask;
		
		/** @var string */
		private $stepType;
		
		/** @var integer */
		private $stepTypeid;
		
		/**
		 * @return string
		 */
		public function getStepComments () {
			return $this->stepComments;
		}
		
		/**
		 * @return string
		 */
		public function getStepModule () {
			return $this->stepModule;
		}
		
		/**
		 * @return integer
		 */
		public function getStepTask () {
			return $this->stepTask;
		}
		
		/**
		 * @return string
		 */
		public function getStepType () {
			return $this->stepType;
		}
		
		/**
		 * @return integer
		 */
		public function getStepTypeid () {
			return $this->stepTypeid;
		}
		
		/**
		 * @param $stepComments
		 *
		 * @return StepTypes
		 */
		public function setStepComments ($stepComments) {
			$this->stepComments = $stepComments;
			return $this;
		}
		
		/**
		 * @param $stepModule
		 *
		 * @return StepTypes
		 */
		public function setStepModule ($stepModule) {
			$this->stepModule = $stepModule;
			return $this;
		}
		
		/**
		 * @param $stepTask
		 *
		 * @return StepTypes
		 */
		public function setStepTask ($stepTask) {
			$this->stepTask = $stepTask;
			return $this;
		}
		
		/**
		 * @param $stepType
		 *
		 * @return StepTypes
		 */
		public function setStepType ($stepType) {
			$this->stepType = $stepType;
			return $this;
		}
		
		/**
		 * @param $stepTypeid
		 *
		 * @return StepTypes
		 */
		public function setStepTypeid ($stepTypeid) {
			$this->stepTypeid = $stepTypeid;
			return $this;
		}
		
		/**
		 * @return StepTypes
		 */
		public static function getInstance () {
					return new self ();
		}
		
	}
<?php
	require_once ('modules/preloaded_tasks/Objects/PrecreatedTaskInterface.php');
	class PrecreatedTask implements PrecreatedTaskInterface {
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $areaName;
		
		/** @var string */
		private $codeArea;
		
		/** @var string */
		private $moduleName;
		
		/** @var string */
		private $status;
		
		/** @var string */
		private $tabName;
		
		/** @var string */
		private $taskName;
		
		/** @var string */
		private $taskDescription;
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return string
		 */
		public function getAreaName () {
			return $this->areaName;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @return string
		 */
		public function getCodeArea () {
			return $this->codeArea;
		}
		
		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}
		
		/**
		 * @return string
		 */
		public function getTabName () {
			return $this->tabName;
		}
		
		/**
		 * @return string
		 */
		public function getTaskDescription () {
			return $this->taskDescription;
		}
		
		/**
		 * @return string
		 */
		public function getTaskName () {
			return $this->taskName;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return PrecreatedTask
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $areaName
		 *
		 * @return PrecreatedTask
		 */
		public function setAreaName ($areaName) {
			$this->areaName = $areaName;
			return $this;
		}
		
		/**
		 * @param string $codeArea
		 *
		 * @return PrecreatedTask
		 */
		public function setCodeArea ($codeArea) {
			$this->codeArea = $codeArea;
			return $this;
		}
		
		/**
		 * @param integer $moduleName
		 *
		 * @return PrecreatedTask
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return PrecreatedTask
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param string $tabName
		 *
		 * @return PrecreatedTask
		 */
		public function setTabName ($tabName) {
			$this->tabName = $tabName;
			return $this;
		}
		
		/**
		 * @param string $taskDescription
		 *
		 * @return PrecreatedTask
		 */
		public function setTaskDescription ($taskDescription) {
			$this->taskDescription = $taskDescription;
			return $this;
		}
		
		/**
		 * @param string $taskName
		 *
		 * @return PrecreatedTask
		 */
		public function setTaskName ($taskName) {
			$this->taskName = $taskName;
			return $this;
		}
		
		/**
		 * @return PrecreatedTask
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

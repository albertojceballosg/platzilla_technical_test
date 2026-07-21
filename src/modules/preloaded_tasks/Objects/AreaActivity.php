<?php
	require_once ('modules/preloaded_tasks/Objects/PrecreatedTaskInterface.php');
	class AreaActivity implements PrecreatedTaskInterface {
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $areaName;
		
		/** @var string */
		private $codeArea;
		
		/** @var string */
		private $status;
		
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
		public function getCodeArea () {
			return $this->codeArea;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return AreaActivity
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $areaName
		 *
		 * @return AreaActivity
		 */
		public function setAreaName ($areaName) {
			$this->areaName = $areaName;
			return $this;
		}
		
		/**
		 * @param string $codeArea
		 *
		 * @return AreaActivity
		 */
		public function setCodeArea ($codeArea) {
			$this->codeArea = $codeArea;
			return $this;
		}
		
		/**
		 * @param $status
		 *
		 * @return AreaActivity
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @return AreaActivity
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

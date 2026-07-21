<?php
	require_once ('include/platzilla/Exceptions/SystemalertsException.php');
	require_once ('include/platzilla/Objects/SystemalertsInterface.php');
	
	class AlertsOfSystem implements SystemalertsInterface {
		
		/** @var integer */
		private $alertId;
		
		/** @var string */
		private $alertName;
		
		/** @var string */
		private $alertTitle;
		
		/** @var string */
		private $appCode;
		
		/** @var integer */
		private $boxScoreId;
		
		/** @var string */
		private $description;
		
		/** @var AlertFilterGroup[] */
		private $filtroGrupo;
		
		/** @var integer */
		private $indicatorId;
		
		/** @var integer */
		private $locked;
		
		/** @var string */
		private $scale;
		
		/** @var string */
		private $sourceAlert;
		
		/** @var integer */
		private $status;
		
		/** @var integer */
		private $tabId;
		
		/** @var string */
		private $tabName;
		
		/** @var string */
		private $tabLabel;
		
		/** @var integer */
		private $userId;
		
		/**
		 * @return integer
		 */
		public function getAlertId () {
			return $this->alertId;
		}
		
		/**
		 * @return string
		 */
		public function getAlertName () {
			return $this->alertName;
		}
		
		/**
		 * @return string
		 */
		public function getAlertTitle () {
			return $this->alertTitle;
		}
		
		/**
		 * @return string
		 */
		public function getAppCode () {
			return $this->appCode;
		}
		
		/**
		 * @return int
		 */
		public function getBoxScoreId () {
			return $this->boxScoreId;
		}
		
		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}
		
		/**
		 * @return AlertFilterGroup[]
		 */
		public function getFiltroGrupo () {
			return $this->filtroGrupo;
		}
		
		/**
		 * @return integer
		 */
		public function getIndicatorId () {
			return $this->indicatorId;
		}
		
		/**
		 * @return integer
		 */
		public function getLocked () {
			return $this->locked;
		}
		
		/**
		 * @return string
		 */
		public function getScale () {
			return $this->scale;
		}
		
		/**
		 * @return string
		 */
		public function getSourceAlert () {
			return $this->sourceAlert;
		}
		
		/**
		 * @return integer
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @return int
		 */
		public function getTabId () {
			return $this->tabId;
		}
		
		/**
		 * @return string
		 */
		public function getTabLabel () {
			return $this->tabLabel;
		}
		
		/**
		 * @return string
		 */
		public function getTabName () {
			return $this->tabName;
		}
		
		/**
		 * @return integer
		 */
		public function getUserId () {
			return $this->userId;
		}
		
		/**
		 * @param integer $alertId
		 *
		 * @return AlertsOfSystem
		 */
		public function setAlertId ($alertId) {
			$this->alertId = $alertId;
			return $this;
		}
		
		/**
		 * @param $alertName
		 *
		 * @return AlertsOfSystem
		 */
		public function setAlertName ($alertName) {
			$this->alertName = $alertName;
			return $this;
		}
		
		/**
		 * @param string $alertTitle
		 *
		 * @return AlertsOfSystem
		 */
		public function setAlertTitle ($alertTitle) {
			$this->alertTitle = $alertTitle;
			return $this;
		}
		
		/**
		 * @param string $appCode
		 *
		 * @return AlertsOfSystem
		 */
		public function setAppCode ($appCode) {
			$this->appCode = $appCode;
			return $this;
		}
		
		/**
		 * @param $boxScoreId
		 *
		 * @return AlertsOfSystem
		 */
		public function setBoxScoreId ($boxScoreId) {
			$this->boxScoreId = $boxScoreId;
			return $this;
		}
		
		/**
		 * @param $description
		 *
		 * @return AlertsOfSystem
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param AlertFilterGroup[] $filtroGrupo
		 *
		 * @return AlertsOfSystem
		 */
		public function setFiltroGrupo ($filtroGrupo) {
			$this->filtroGrupo = $filtroGrupo;
			return $this;
		}
		
		/**
		 * @param $indicatorId
		 *
		 * @return AlertsOfSystem
		 */
		public function setIndicatorId ($indicatorId) {
			$this->indicatorId = $indicatorId;
			return $this;
		}
		
		/**
		 * @param integer $locked
		 *
		 * @return AlertsOfSystem
		 */
		public function setLocked ($locked) {
			$this->locked = $locked;
			return $this;
		}
		
		/**
		 * @param $scale
		 *
		 * @return AlertsOfSystem
		 */
		public function setScale ($scale) {
			$this->scale = $scale;
			return $this;
		}
		
		/**
		 * @param $sourceAlert
		 *
		 * @return AlertsOfSystem
		 */
		public function setSourceAlert ($sourceAlert) {
			$this->sourceAlert = $sourceAlert;
			return $this;
		}
		
		/**
		 * @param $status
		 *
		 * @return AlertsOfSystem
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param $tabId
		 *
		 * @return AlertsOfSystem
		 */
		public function setTabId ($tabId) {
			$this->tabId = $tabId;
			return $this;
		}
		
		/**
		 * @param $tabLabel
		 *
		 * @return AlertsOfSystem
		 */
		public function setTabLabel ($tabLabel) {
			$this->tabLabel = $tabLabel;
			return $this;
		}
		
		/**
		 * @param $tabName
		 *
		 * @return AlertsOfSystem
		 */
		public function setTabName ($tabName) {
			$this->tabName = $tabName;
			return $this;
		}
		
		/**
		 * @param $userId
		 *
		 * @return AlertsOfSystem
		 */
		public function setUserId ($userId) {
			$this->userId = $userId;
			return $this;
		}
		
		/**
		 * @return AlertsOfSystem
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

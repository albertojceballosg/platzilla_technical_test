<?php
	require_once ('include/platzilla/Data/ActivityReport.php');
	require_once ('include/platzilla/Data/TaskActivity.php');
	
	class DailyReportMaster {
		
		/** @var float */
		private $actualCost;
		
		/** @var TaskActivity */
		private $activity;
		
		/** @var integer */
		private $crmId;
		
		/** @var string */
		private $dailyReportDate;
		
		/** @var float */
		private $durationTime;
		
		/** @var integer */
		private $id;
		
		/** @var float */
		private $progress;
		
		/** @var ActivityReport[] */
		private $report;
		
		/** @var string */
		private $reportIds;
		
		/** @var float */
		private $summaryRow;
		
		/** @var float */
		private $totalHoursReported;
		
		/** @var integer */
		private $userId;
		
		/** @var string */
		private $userName;
		
		/**
		 * @return float
		 */
		public function getActualCost () {
			return $this->actualCost;
		}
		
		/**
		 * @return TaskActivity
		 */
		public function getActivity () {
			return $this->activity;
		}
		
		/**
		 * @return integer
		 */
		public function getCrmId () {
			return $this->crmId;
		}
		
		/**
		 * @return string
		 */
		public function getDailyReportDate () {
			return $this->dailyReportDate;
		}
		
		/**
		 * @return float
		 */
		public function getDurationTime () {
			return $this->durationTime;
		}
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return float
		 */
		public function getProgress () {
			return $this->progress;
		}
		
		/**
		 * @return ActivityReport[]
		 */
		public function getReport () {
			return $this->report;
		}
		
		/**
		 * @return string
		 */
		public function getReportIds () {
			return $this->reportIds;
		}
		
		/**
		 * @return float
		 */
		public function getSummaryRow () {
			return (!empty($this->summaryRow)) ? $this->summaryRow : 0;
		}
		
		/**
		 * @return float
		 */
		public function getTotalHoursReported () {
			return $this->totalHoursReported;
		}
		
		/**
		 * @return integer
		 */
		public function getUserId () {
			return $this->userId;
		}
		
		/**
		 * @return string
		 */
		public function getUserName () {
			return $this->userName;
		}
		
		/**
		 * @param float $actualCost
		 *
		 * @return DailyReportMaster
		 */
		public function setActualCost ($actualCost) {
			$this->actualCost = $actualCost;
			return $this;
		}
		
		/**
		 * @param TaskActivity $activity
		 * @return DailyReportMaster
		 */
		public function setActivity ($activity) {
			$this->activity = $activity;
			return $this;
		}
		
		/**
		 * @param integer $crmId
		 *
		 * @return DailyReportMaster
		 */
		public function setCrmId ($crmId) {
			$this->crmId = $crmId;
			return $this;
		}
		
		/**
		 * @param string $dailyReportDate
		 *
		 * @return DailyReportMaster
		 */
		public function setDailyReportDate ($dailyReportDate) {
			$this->dailyReportDate = $dailyReportDate;
			return $this;
		}
		
		/**
		 * @param float $durationTime
		 *
		 * @return DailyReportMaster
		 */
		public function setDurationTime ($durationTime) {
			$this->durationTime = $durationTime;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return DailyReportMaster
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param float $progress
		 *
		 * @return DailyReportMaster
		 */
		public function setProgress ($progress) {
			$this->progress = $progress;
			return $this;
		}
		
		/**
		 * @param ActivityReport[] $report
		 *
		 * @return DailyReportMaster
		 */
		public function setReport ($report) {
			$this->report = $report;
			return $this;
		}
		
		/**
		 * @param string $reportIds
		 *
		 * @return DailyReportMaster
		 */
		public function setReportIds ($reportIds) {
			$this->reportIds = $reportIds;
			return $this;
		}
		
		/**
		 * @param float $summaryRow
		 *
		 * @return DailyReportMaster
		 */
		public function setSummaryRow ($summaryRow) {
			$this->summaryRow = $summaryRow;
			return $this;
		}
		
		/**
		 * @param float $totalHoursReported
		 *
		 * @return DailyReportMaster
		 */
		public function setTotalHoursReported ($totalHoursReported) {
			$this->totalHoursReported = $totalHoursReported;
			return $this;
		}
		
		/**
		 * @param integer $userId
		 *
		 * @return DailyReportMaster
		 */
		public function setUserId ($userId) {
			$this->userId = $userId;
			return $this;
		}
		
		/**
		 * @param string $userName
		 *
		 * @return DailyReportMaster
		 */
		public function setUserName ($userName) {
			$this->userName = $userName;
			return $this;
		}
		
		/**
		 * @return DailyReportMaster
		 */
		public static function getInstance () {
			return new self ();
		}
	}

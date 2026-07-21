<?php
	require_once ('include/platzilla/Exceptions/SummaryReportException.php');
	require_once ('include/platzilla/Objects/SummaryReportInterface.php');
	require_once ('include/platzilla/Objects/RailesPerformance.php');
	
	class SummaryReport implements SummaryReportInterface 	{
		
		/** @var integer */
		private	$id;
		
		/** @var integer */
		private	$masterReportId;
		
		/** @var RailesPerformance */
		private	$performance;
		
		/** @var integer */
		private	$performanceId;
		
		/** @var string */
		private	$status;
		
		/** @var string */
		private	$summaryText;
		
		/** @var string */
		private	$summaryTitle;
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return integer
		 */
		public function getMasterReportId () {
			return $this->masterReportId;
		}
		
		/**
		 * @return RailesPerformance
		 */
		public function getPerformance () {
			return $this->performance;
		}
		
		/**
		 * @return integer
		 */
		public function getPerformanceId () {
			return $this->performanceId;
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
		public function getSummaryText () {
			return $this->summaryText;
		}
		
		/**
		 * @return string
		 */
		public function getSummaryTitle () {
			return $this->summaryTitle;
		}
		
		/**
		 * @param $id
		 *
		 * @return SummaryReport
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param $masterReportId
		 *
		 * @return SummaryReport
		 */
		public function setMasterReportId ($masterReportId) {
			$this->masterReportId = $masterReportId;
			return $this;
		}
		
		/**
		 * @param RailesPerformance $performance
		 *
		 * @return SummaryReport
		 */
		public function setPerformance ($performance) {
			$this->performance = $performance;
			return $this;
		}
		
		/**
		 * @param $performance
		 *
		 * @return SummaryReport
		 */
		public function setPerformanceId ($performance) {
			$this->performanceId = $performance;
			return $this;
		}
		
		/**
		 * @param $status
		 *
		 * @return SummaryReport
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param $summaryText
		 *
		 * @return SummaryReport
		 */
		public function setSummaryText ($summaryText) {
			$this->summaryText = $summaryText;
			return $this;
		}
		
		/**
		 * @param $summaryTitle
		 *
		 * @return SummaryReport
		 */
		public function setSummaryTitle ($summaryTitle) {
			$this->summaryTitle = $summaryTitle;
			return $this;
		}
		
		/**
		 * @return SummaryReport
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

<?php
	require_once ('include/platzilla/Exceptions/SummaryReportException.php');
	require_once ('include/platzilla/Objects/SummaryReportInterface.php');
	class RailesPerformance implements SummaryReportInterface {
		
		/** @var string */
		private	$description;
		
		/** @var string */
		private	$iconPath;
		
		/** @var string */
		private	$indexColor;
		
		/** @var integer */
		private	$performanceId;
		
		/** @var string */
		private	$performanceStatus;
		
		/** @var string */
		private $performanceName;
		
		private	$reportId;
		
		/**
		 * @return string
		 */
		public function getDescription() {
			return $this->description;
		}
		
		/**
		 * @return string
		 */
		public function getIconPath() {
			return $this->iconPath;
		}
		
		/**
		 * @return string
		 */
		public function getIndexColor() {
			return $this->indexColor;
		}
		
		/**
		 * @return integer
		 */
		public function getPerformanceId() {
			return $this->performanceId;
		}
		
		/**
		 * @return string
		 */
		public function getPerformanceStatus() {
			return $this->performanceStatus;
		}
		
		/**
		 * @return string
		 */
		public function getPerformanceName () {
			return $this->performanceName;
		}
		
		/**
		 * @return integer
		 */
		public function getReportId () {
			return $this->reportId;
		}
		
		/**
		 * @param $description
		 *
		 * @return RailesPerformance
		 */
		public function setDescription($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param $iconPath
		 *
		 * @return RailesPerformance
		 */
		public function setIconPath($iconPath) {
			$this->iconPath = $iconPath;
			return $this;
		}
		
		/**
		 * @param $indexColor
		 *
		 * @return RailesPerformance
		 */
		public function setIndexColor($indexColor) {
			$this->indexColor = $indexColor;
			return $this;
		}
		
		/**
		 * @param $performanceId
		 *
		 * @return RailesPerformance
		 */
		public function setPerformanceId($performanceId) {
			$this->performanceId = $performanceId;
			return $this;
		}
		
		/**
		 * @param $performanceStatus
		 *
		 * @return RailesPerformance
		 */
		public function setPerformanceStatus ($performanceStatus) {
			if (!in_array($performanceStatus, array_keys (self::PERFORMANCES_STATUS))) {
				throw new SummaryReportException(SummaryReportException::ERROR_INVALID_PERFORMANCES_STATUS);
			}
			$this->performanceStatus = $performanceStatus;
			return $this;
		}
		
		/**
		 * @param $performanceName
		 *
		 * @return RailesPerformance
		 */
		public function setPerformanceName ($performanceName) {
			$this->performanceName = $performanceName;
			return $this;
		}
		
		/**
		 * @param $reportId
		 *
		 * @return RailesPerformance
		 */
		public function setReportId ($reportId) {
			$this->reportId = $reportId;
			return $this;
		}
		
		
		/**
		 * @return void
		 * @throws SummaryReportException
		 */
		public function validate () {
			if (empty ($this->iconPath) || empty ($this->indexColor)) {
				throw new SummaryReportException(SummaryReportException::ERROR_PERFORMANCE_INDEX_EMPTY);
			} else if (empty ($this->performanceStatus)) {
				throw new SummaryReportException(SummaryReportException::ERROR_INVALID_PERFORMANCES_STATUS);
			} else if (empty($this->performanceName)) {
				throw new SummaryReportException(SummaryReportException::ERROR_PERFORMANCE_NAME_EMPTY);
			} else if (empty($this->reportId)) {
				throw new SummaryReportException(SummaryReportException::ERROR_INVALID_REPORT_ID);
			}
		}
		
		/**
		 * @return RailesPerformance
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

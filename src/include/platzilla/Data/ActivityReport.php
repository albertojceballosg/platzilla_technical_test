<?php
	require_once ('include/platzilla/Data/ActivityReportInterface.php');

	class ActivityReport implements ActivityReportInterface {

		/** @var integer */
		private $activityId;

		/** @var integer */
		private $ceilProgress;
		
		/** @var ActivityFeedback[] */
		private $feedbacks;
		
		/** @var integer */
		private $id;
		
		/** @var boolean */
		private $isHeld;
		
		/** @var float */
		private $progress;
		
		/** @var string */
		private $report;

		/** @var string */
		private $reportDate;

		/** @var string|null */
		private $activityReportDate;
		
		/** @var string */
		private $reportOn;
		
		/** @var float */
		private $timeDuration;
		
		/** @var float|null */
		private $actualCost;
		
		/** @var string|null */
		private $estimatedTimeUnit;
		
		/** @var string */
		private $title;
		
		/** @var string */
		private $userAvatar;

		/** @var integer */
		private $userId;

		/** @var string */
		private $userName;
		

		/**
		 * @return integer
		 */
		public function getActivityId () {
			return $this->activityId;
		}
		
		/**
		 * @return integer
		 */
		public function getCeilProgress () {
			return $this->ceilProgress;
		}
		
		/**
		 * @return ActivityFeedback[]
		 */
		public function getFeedbacks () {
			return $this->feedbacks;
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
		 * @return string
		 */
		public function getReport () {
			return $this->report;
		}

		/**
		 * @return string
		 */
		public function getReportDate () {
			return $this->reportDate;
		}

		/**
		 * @return string|null
		 */
		public function getActivityReportDate () {
			return $this->activityReportDate;
		}
		
		/**
		 * @return string
		 */
		public function getReportOn (){
			return $this->reportOn;
		}
		
		/**
		 * @return float
		 */
		public function getTimeDuration () {
			return $this->timeDuration;
		}
		
		/**
		 * @return float|null
		 */
		public function getActualCost () {
			return $this->actualCost;
		}
		
		/**
		 * @return string|null
		 */
		public function getEstimatedTimeUnit () {
			return $this->estimatedTimeUnit;
		}
		
		/**
		 * @return string
		 */
		public function getTitle () {
			return $this->title;
		}
		
		/**
		 * @return string
		 */
		public function getUserAvatar () {
			return $this->userAvatar;
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
		 * @return boolean
		 */
		public function isHeld () {
			return $this->isHeld;
		}
		
		/**
		 * @param integer $activityId
		 *
		 * @return ActivityReport
		 */
		public function setActivityId ($activityId) {
			$this->activityId = $activityId;
			return $this;
		}
		
		/**
		 * @param ActivityFeedback[] $feedbacks
		 *
		 * @return ActivityReport
		 */
		public function setFeedbacks ($feedbacks) {
			if (!empty($feedbacks)) {
				foreach ($feedbacks as $feedback) {
					if (!$feedback instanceof ActivityFeedback) {
						continue;
					}
					$this->feedbacks [] = $feedback;
				}
				
				
			}
			return $this;
		}
		
		/**
		 * @param boolean $isHeld
		 *
		 * @return ActivityReport
		 */
		public function setIsHeld ($isHeld) {
			$this->isHeld = $isHeld;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return ActivityReport
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $progress
		 *
		 * @return ActivityReport
		 */
		public function setReportOn ( $reportOn){
			$this->reportOn = $reportOn;
			return $this;
		}
		
		/**
		 * @param float $progress
		 *
		 * @return ActivityReport
		 */
		public function setProgress ($progress) {
			if (is_float ($progress)) {
				$this->progress     = $progress;
				$this->ceilProgress = ceil ($progress);
			} else {
				$this->progress     = 1.00;
				$this->ceilProgress = 0;
			}
			
			return $this;
		}

		/**
		 * @param string $report
		 *
		 * @return ActivityReport
		 */
		public function setReport ($report) {
			$this->report = $report;
			return $this;
		}

		/**
		 * @param string|null $activityReportDate Fecha en formato Y-m-d
		 *
		 * @return ActivityReport
		 */
		public function setActivityReportDate ($activityReportDate) {
			$this->activityReportDate = !empty($activityReportDate) ? $activityReportDate : null;
			return $this;
		}

		/**
		 * @param string $reportDate
		 * @param null|string $format
		 *
		 * @return ActivityReport
		 */
		public function setReportDate ($reportDate, $format = null) {
			if (!empty($reportDate)) {
				$format = (empty($format)) ? '%A %d de %B - %Y, %H:%M:%S' : $format;
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->reportDate = ucwords (mb_convert_encoding(strftime ($format, strtotime ($reportDate)), 'UTF-8', 'ISO-8859-1'));
			} else {
				$this->reportDate = null;
			}
			return $this;
		}
		
		/**
		 * @param float $timeDuration
		 *
		 * @return ActivityReport
		 */
		public function setTimeDuration ($timeDuration) {
			$this->timeDuration = $timeDuration;
			return $this;
		}
		
		/**
		 * @param float|null $actualCost
		 *
		 * @return ActivityReport
		 */
		public function setActualCost ($actualCost) {
			$this->actualCost = ($actualCost !== null) ? floatval($actualCost) : null;
			return $this;
		}
		
		/**
		 * @param string|null $estimatedTimeUnit
		 *
		 * @return ActivityReport
		 */
		public function setEstimatedTimeUnit ($estimatedTimeUnit) {
			$this->estimatedTimeUnit = $estimatedTimeUnit;
			return $this;
		}
		
		/**
		 * @param string $title
		 *
		 * @return ActivityReport
		 */
		public function setTitle ($title) {
			$this->title = $title;
			return $this;
		}
		
		/**
		 * @param string $userAvatar
		 *
		 * @return ActivityReport
		 */
		public function setUserAvatar ($userAvatar) {
			$this->userAvatar = $userAvatar;
			return $this;
		}

		/**
		 * @param $userId
		 *
		 * @return ActivityReport
		 */
		public function setUserId ($userId) {
			$this->userId = $userId;
			return $this;
		}

		/**
		 * @param string $userName
		 *
		 * @return ActivityReport
		 */
		public function setUserName ($userName) {
			$this->userName = $userName;
			return $this;
		}

		/**
		 * @return ActivityReport
		 */
		public static function getInstance () {
			return new self ();
		}

	}

<?php
	require_once ('include/platzilla/Data/ActivityReportInterface.php');

	class ActivityFeedback implements ActivityReportInterface {

		/** @var integer */
		private $activityId;

		/** @var integer */
		private $id;

		/** @var string */
		private $feedback;

		/** @var string */
		private $feedbackDate;
		
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
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getFeedback () {
			return $this->feedback;
		}

		/**
		 * @return string
		 */
		public function getFeedbackDate () {
			return $this->feedbackDate;
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
		 * @param integer $activityId
		 *
		 * @return ActivityFeedback
		 */
		public function setActivityId ($activityId) {
			$this->activityId = $activityId;
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return ActivityFeedback
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $feedback
		 *
		 * @return ActivityFeedback
		 */
		public function setFeedback ($feedback) {
			$this->feedback = $feedback;
			return $this;
		}

		/**
		 * @param string $feedbackDate
		 * @param null|string $format
		 *
		 * @return ActivityFeedback
		 */
		public function setFeedbackDate ($feedbackDate, $format = null) {
			if (!empty($feedbackDate)) {
				$format = (empty($format)) ? '%A %d de %B - %Y, %H:%M:%S' : $format;
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->feedbackDate = ucwords (utf8_encode (strftime ($format, strtotime ($feedbackDate))));
			} else {
				$this->feedbackDate = null;
			}
			return $this;
		}
		
		/**
		 * @param string $title
		 *
		 * @return ActivityFeedback
		 */
		public function setTitle ($title) {
			$this->title = $title;
			return $this;
		}
		
		
		/**
		 * @param string $userAvatar
		 *
		 * @return ActivityFeedback
		 */
		public function setUserAvatar ($userAvatar) {
			$this->userAvatar = $userAvatar;
			return $this;
		}

		/**
		 * @param $userId
		 *
		 * @return ActivityFeedback
		 */
		public function setUserId ($userId) {
			$this->userId = $userId;
			return $this;
		}

		/**
		 * @param string $userName
		 *
		 * @return ActivityFeedback
		 */
		public function setUserName ($userName) {
			$this->userName = $userName;
			return $this;
		}

		/**
		 * @return ActivityFeedback
		 */
		public static function getInstance () {
			return new self ();
		}

	}

<?php
	require_once ('include/platzilla/Data/ActivityInterface.php');
	require_once ('include/platzilla/Data/ActivityReport.php');
	
	class TaskActivity implements ActivityInterface {
		
		/** @var integer */
		private $activityId;
		
		/** @var ActivityReport[] */
		private $activityReports;
		
		/** @var string */
		private $activityCondition;
		
		/** @var string */
		private $activityType;
		
		/** @var string */
		private $dateEnd;
		
		/** @var string */
		private $dateInit;
		
		/** @var string */
		private $description;
		
		/** @var string */
		private $dueDate;
		
		/** @var string */
		private $durationHours;
		
		/** @var integer */
		private $groupId;
		
		/** @var string */
		private $importance;
		
		/** @var string */
		private $moduleName;
		
		/** @var string */
		private $priority;
		
		/** @var float */
		private $progress;
		
		/** @var integer */
		private $relatedId;
		
		/** @var string */
		private $relatedModule;
		
		/** @var string */
		private $relatedTitle;
		
		/** @var string */
		private $startDate;
		
		/** @var string */
		private $status;
		
		/** @var string */
		private $subject;
		
		/** @var float */
		private $timeDuration;
		
		/** @var string */
		private $userAvatar;
		
		/** @var string */
		private $userName;
		
		/** @var array */
		public $attachments;
		
		/** @var array */
		public $relatedTasks;
		
		/**
		 * @return integer
		 */
		public function getActivityId () {
			return $this->activityId;
		}
		
		/**
		 * @return string
		 */
		public function getActivityCondition () {
			return $this->activityCondition;
		}
		
		/**
		 * @return string
		 */
		public function getDateEnd () {
			return $this->dateEnd;
		}
		
		/**
		 * @return string
		 */
		public function getDateInit () 	{
			return $this->dateInit;
		}
		
		/**
		 * @return string
		 */
		public function getActivityType () {
			return $this->activityType;
		}
		
		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}
		
		/**
		 * @return string
		 */
		public function getDueDate () {
			return $this->dueDate;
		}
		
		/**
		 * @return string
		 */
		public function getDurationHours () {
			return $this->durationHours;
		}
		
		/**
		 * @return integer
		 */
		public function getGroupId () {
			return $this->groupId;
		}
		/**
		 * @return string
		 */
		public function getImportance () {
			return $this->importance;
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
		public function getPriority () {
			return $this->priority;
		}
		
		/**
		 * @return float
		 */
		public function getProgress () {
			return $this->progress;
		}
		
		/**
		 * @return integer
		 */
		public function getRelatedId () {
			return $this->relatedId;
		}
		
		/**
		 * @return string
		 */
		public function getRelatedModule () {
			return $this->relatedModule;
		}
		
		/**
		 * @return string
		 */
		public function getRelatedTitle () {
			return $this->relatedTitle;
		}
		
		/**
		 * @return null|ActivityReport[]
		 */
		public function getActivityReports () {
			return $this->activityReports;
		}
		
		/**
		 * @return string
		 */
		public function getStartDate () {
			return $this->startDate;
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
		public function getSubject () {
			return $this->subject;
		}
		
		/**
		 * @return float
		 */
		public function getTimeDuration () {
			return $this->timeDuration;
		}
		
		/**
		 * @return string
		 */
		public function getUserAvatar () {
			return $this->userAvatar;
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
		 * @return TaskActivity
		 */
		public function setActivityId ($activityId) {
			$this->activityId = $activityId;
			return $this;
		}
		
		/**
		 * @param string $activityCondition
		 *
		 * @return TaskActivity
		 */
		public function setActivityCondition ($activityCondition) {
			$this->activityCondition = $activityCondition;
			return $this;
		}
		
		/**
		 * @param string $activityType
		 *
		 * @return TaskActivity
		 */
		public function setActivityType ($activityType) {
			$this->activityType = $activityType;
			return $this;
		}
		
		/**
		 * @param string $description
		 *
		 * @return TaskActivity
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param string $dueDate
		 * @param string $format
		 *
		 * @return TaskActivity
		 */
		public function setDueDate ($dueDate, $format = null) {
			if (!empty($dueDate)) {
				$format = (empty($format)) ? '%A %d de %B - %Y, %H:%M:%S' : $format;
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->dueDate = ucwords (mb_convert_encoding(strftime ($format, strtotime ($dueDate)), 'UTF-8', 'ISO-8859-1'));
				$this->dateEnd = $dueDate;
			} else {
				$this->dueDate = null;
			}
			return $this;
		}
		
		/**
		 * @param string $durationHours
		 *
		 * @return TaskActivity
		 */
		public function setDurationHours ($durationHours) {
			$this->durationHours = $durationHours;
			return $this;
		}
		
		/**
		 * @param $groupId
		 *
		 * @return TaskActivity
		 */
		public function setGroupId ($groupId) {
			$this->groupId = $groupId;
			return $this;
		}
		
		/**
		 * @param string $importance
		 *
		 * @return TaskActivity
		 */
		public function setImportance ($importance) {
			$this->importance = $importance;
			return $this;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return TaskActivity
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}
		
		/**
		 * @param string $priority
		 *
		 * @return TaskActivity
		 */
		public function setPriority ($priority) {
			$this->priority = $priority;
			return $this;
		}
		
		/**
		 * @param float $progress
		 *
		 * @return TaskActivity
		 */
		public function setProgress ($progress) {
			$this->progress = $progress;
			return $this;
		}
		
		/**
		 * @param integer $relatedId
		 *
		 * @return TaskActivity
		 */
		public function setRelatedId ($relatedId) {
			$this->relatedId = $relatedId;
			return $this;
		}
		
		/**
		 * @param string $relatedModule
		 *
		 * @return TaskActivity
		 */
		public function setRelatedModule ($relatedModule) {
			$this->relatedModule = $relatedModule;
			return $this;
		}
		
		/**
		 * @param string $relatedTitle
		 *
		 * @return TaskActivity
		 */
		public function setRelatedTitle ($relatedTitle) {
			$this->relatedTitle = $relatedTitle;
			return $this;
		}
		
		/**
		 * @param null|ActivityReport[] $activityReports
		 *
		 * @return TaskActivity;
		 */
		public function setActivityReports ($activityReports) {
			$this->activityReports = $activityReports;
			return $this;
		}
		
		/**
		 * @param string $startDate
		 * @param string $format
		 *
		 * @return TaskActivity
		 */
		public function setStartDate ($startDate,  $format = null) {
			if (!empty($startDate)) {
				$format = (empty($format)) ? '%A %d de %B - %Y, %H:%M:%S' : $format;
				setlocale (LC_ALL, 'es_ES', 'Spanish_Traditional_Sort',  'Spanish_Spain', 'Spanish');
				$this->startDate = ucwords (mb_convert_encoding(strftime ($format, strtotime ($startDate)), 'UTF-8', 'ISO-8859-1'));
				$this->dateInit  = $startDate;
			} else {
				$this->startDate = null;
			}
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return TaskActivity
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param string $subject
		 *
		 * @return TaskActivity
		 */
		public function setSubject ($subject) {
			$this->subject = $subject;
			return $this;
		}
		
		/**
		 * @param float $timeDuration
		 *
		 * @return TaskActivity
		 */
		public function setTimeDuration ($timeDuration) {
			$this->timeDuration = $timeDuration;
			return $this;
		}
		
		/**
		 * @param string $userAvatar
		 *
		 * @return TaskActivity
		 */
		public function setUserAvatar ($userAvatar) {
			$this->userAvatar = $userAvatar;
			return $this;
		}
		
		/**
		 * @param $userName
		 *
		 * @return TaskActivity
		 */
		public function setUserName ($userName) {
			$this->userName = $userName;
			return $this;
		}
		
		/**
		 * @return TaskActivity
		 */
		public static function getInstance () {
			return new self ();
		}
	}

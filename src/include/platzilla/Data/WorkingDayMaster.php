<?php
	require_once ('include/platzilla/Data/WorkingDayException.php');
	require_once ('include/platzilla/Data/WorkingDayInterface.php');
	require_once ('include/platzilla/Data/WorkingDaysOfWeek.php');
	
	class WorkingDayMaster  implements WorkingDayInterface {
		
		/** @var string */
		private $afternoonDueTime;
		
		/** @var string */
		private $afternoonStartTime;
		
		/** @var string */
		private $dataTimeCreated;
		
		/** @var string */
		private $description;
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $morningDueTime;
		
		/** @var string */
		private $morningStartTime;
		
		/** @var integer */
		private $regularWorkingHours;
		
		/** @var string */
		private $workingDayName;
		
		/** @var WorkingDaysOfWeek[] */
		private $workingDaysOfWeek;
		
		/** @var string */
		private $workingDayStatus;
		
		/**
		 * @return string
		 */
		public function getAfternoonDueTime () {
			return $this->afternoonDueTime;
		}
		
		/**
		 * @return string
		 */
		public function getAfternoonStartTime () {
			return $this->afternoonStartTime;
		}
		
		/**
		 * @return string
		 */
		public function getDataTimeCreated () {
			return $this->dataTimeCreated;
		}
		
		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
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
		public function getMorningDueTime () {
			return $this->morningDueTime;
		}
		
		/**
		 * @return string
		 */
		public function getMorningStartTime () {
			return $this->morningStartTime;
		}
		
		/**
		 * @return integer
		 */
		public function getRegularWorkingHours () {
			return $this->regularWorkingHours;
		}
		
		/**
		 * @return string
		 */
		public function getWorkingDayName () {
			return $this->workingDayName;
		}
		
		/**
		 * @return WorkingDaysOfWeek[]
		 */
		public function getWorkingDaysOfWeek ()  {
			return $this->workingDaysOfWeek;
		}
		
		/**
		 * @return string
		 */
		public function getWorkingDayStatus () {
			return $this->workingDayStatus;
		}
		
		/**
		 * @param string $afternoonDueTime
		 *
		 * @return WorkingDayMaster
		 */
		public function setAfternoonDueTime ($afternoonDueTime) {
			$this->afternoonDueTime = $afternoonDueTime;
			return $this;
		}
		
		/**
		 * @param string $afternoonStartTime
		 *
		 * @return WorkingDayMaster
		 */
		public function setAfternoonStartTime ($afternoonStartTime) {
			$this->afternoonStartTime = $afternoonStartTime;
			return $this;
		}
		
		/**
		 * @param string $dataTimeCreated
		 *
		 * @return WorkingDayMaster
		 */
		public function setDataTimeCreated ($dataTimeCreated) {
			$this->dataTimeCreated = $dataTimeCreated;
			return $this;
		}
		
		/**
		 * @param string $description
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return WorkingDayMaster
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $morningDueTime
		 *
		 * @return WorkingDayMaster
		 */
		public function setMorningDueTime ($morningDueTime) {
			$this->morningDueTime = $morningDueTime;
			return $this;
		}
		
		/**
		 * @param string $morningStartTime
		 *
		 * @return WorkingDayMaster
		 */
		public function setMorningStartTime ($morningStartTime) {
			$this->morningStartTime = $morningStartTime;
			return $this;
		}
		
		/**
		 * @param integer $regularWorkingHours
		 *
		 * @return WorkingDayMaster
		 */
		public function setRegularWorkingHours ($regularWorkingHours) {
			$this->regularWorkingHours = $regularWorkingHours;
			return $this;
		}
		
		/**
		 * @param $workingDayName
		 *
		 * @return WorkingDayMaster
		 */
		public function setWorkingDayName ($workingDayName) {
			$this->workingDayName = $workingDayName;
			return $this;
		}
		
		/**
		 * @param WorkingDaysOfWeek[] $workingDaysOfWeek
		 *
		 * @return WorkingDayMaster
		 */
		public function setWorkingDaysOfWeek ($workingDaysOfWeek) {
			$this->workingDaysOfWeek = $workingDaysOfWeek;
			return $this;
		}
		
		/**
		 * @param string $workingDayStatus
		 *
		 * @return WorkingDayMaster
		 */
		public function setWorkingDayStatus ($workingDayStatus) {
			$this->workingDayStatus = $workingDayStatus;
			return $this;
		}
		
		/**
		 * @return WorkingDayMaster
		 */
		public static function getInstance () {
			return new self ();
		}
	}

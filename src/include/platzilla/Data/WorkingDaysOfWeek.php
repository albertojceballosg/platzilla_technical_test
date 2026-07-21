<?php
	require_once ('include/platzilla/Data/WorkingDayException.php');
	require_once ('include/platzilla/Data/WorkingDayInterface.php');
	
	class WorkingDaysOfWeek  implements WorkingDayInterface {
		/** @var string */
		private $afternoonDueTime;
		
		/** @var string */
		private $afternoonStartTime;
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $morningDueTime;
		
		/** @var string */
		private $morningStartTime;
		
		/** @var integer */
		private $workingDayId;
		
		/** @var string */
		private $workingDayName;
		
		/** @var integer */
		private $workingHours;
		
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
		public function getWorkingDayId () {
			return $this->workingDayId;
		}
		
		/**
		 * @return string
		 */
		public function getWorkingDayName () {
			return $this->workingDayName;
		}
		
		/**
		 * @return string
		 */
		public function getWorkingDayStatus () {
			return $this->workingDayStatus;
		}
		
		/**
		 * @return integer
		 */
		public function getWorkingHours () {
			return $this->workingHours;
		}
		
		/**
		 * @param string $afternoonDueTime
		 * @return WorkingDaysOfWeek
		 */
		public function setAfternoonDueTime ($afternoonDueTime) {
			$this->afternoonDueTime = $afternoonDueTime;
			return $this;
		}
		
		/**
		 * @param string $afternoonStartTime
		 * @return WorkingDaysOfWeek
		 */
		public function setAfternoonStartTime ($afternoonStartTime) {
			$this->afternoonStartTime = $afternoonStartTime;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return WorkingDaysOfWeek
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $morningDueTime
		 *
		 * @return WorkingDaysOfWeek
		 */
		public function setMorningDueTime ($morningDueTime) {
			$this->morningDueTime = $morningDueTime;
			return $this;
		}
		
		/**
		 * @param string $morningStartTime
		 *
		 * @return WorkingDaysOfWeek
		 */
		public function setMorningStartTime ($morningStartTime) {
			$this->morningStartTime = $morningStartTime;
			return $this;
		}
		
		/**
		 * @param integer $workingDayId
		 *
		 * @return WorkingDaysOfWeek
		 */
		public function setWorkingDayId ($workingDayId) {
			$this->workingDayId = $workingDayId;
			return $this;
		}
		
		/**
		 * @param string $workingDayName
		 *
		 * @return WorkingDaysOfWeek
		 */
		public function setWorkingDayName ($workingDayName) {
			$this->workingDayName = $workingDayName;
			return $this;
		}
		
		/**
		 * @param string $workingDayStatus
		 *
		 * @return WorkingDaysOfWeek
		 */
		public function setWorkingDayStatus ($workingDayStatus) {
			$this->workingDayStatus = $workingDayStatus;
			return $this;
		}
		
		/**
		 * @param integer $workingHours
		 *
		 * @return WorkingDaysOfWeek
		 */
		public function setWorkingHours ($workingHours) {
			$this->workingHours = $workingHours;
			return $this;
		}
		
		/**
		 * @return WorkingDaysOfWeek
		 */
		public static function getInstance () {
			return new self ();
		}
	}

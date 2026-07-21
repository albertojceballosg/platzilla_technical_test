<?php
	require_once ('include/platzilla/Exceptions/ReportScheduleException.php');
	require_once ('include/platzilla/Objects/ReportScheduleInterface.php');
	require_once ('include/platzilla/Utils/MiscellaneousUtils.php');

	class ReportSchedule implements ReportScheduleInterface {
		/** @var integer */
		private $day;

		/** @var string */
		private $format;

		/** @var string */
		private $frequency;

		/** @var integer[] */
		private $groups;

		/** @var integer */
		private $month;

		/** @var integer */
		private $reportId;

		/** @var string[] */
		private $roles;

		/** @var string[] */
		private $rolesAndSubordinates;

		/** @var string */
		private $time;

		/** @var integer[] */
		private $users;

		/** @var integer */
		private $weekDay;

		/**
		 * ReportSchedule constructor.
		 *
		 * @param integer $frequency
		 * @param string $time
		 * @param integer $day
		 * @param integer $month
		 */
		public function __construct ($frequency, $time, $day = null, $month = null) {
			if (!in_array ($frequency, array (self::FREQUENCY_BIWEEKLY, self::FREQUENCY_DAILY, self::FREQUENCY_MONTHLY, self::FREQUENCY_WEEKLY, self::FREQUENCY_YEARLY))) {
				return;
			}

			switch ($frequency) {
				case self::FREQUENCY_BIWEEKLY:
				case self::FREQUENCY_WEEKLY:
					$this->day     = null;
					$this->month   = null;
					$this->weekDay = in_array ($day, array (self::WEEKDAY_SUNDAY, self::WEEKDAY_MONDAY, self::WEEKDAY_TUESDAY, self::WEEKDAY_WEDNESDAY, self::WEEKDAY_THURSDAY, self::WEEKDAY_FRIDAY, self::WEEKDAY_SATURDAY)) ? $day : null;
					break;
				case self::FREQUENCY_DAILY:
					$this->day     = null;
					$this->month   = null;
					$this->weekDay = null;
					break;
				case self::FREQUENCY_MONTHLY:
					$this->day     = ($day !== null) && (is_int ($day)) && (0 < $day) && ($day < 32) ? $day : null;
					$this->month   = null;
					$this->weekDay = null;
					break;
				case self::FREQUENCY_YEARLY:
					$this->day     = ($day !== null) && (is_int ($day)) && (0 < $day) && ($day < 32) ? $day : null;
					$this->month   = in_array ($month, array (self::MONTH_JANUARY, self::MONTH_FEBRUARY, self::MONTH_MARCH, self::MONTH_APRIL, self::MONTH_MAY, self::MONTH_JUNE, self::MONTH_JULY, self::MONTH_AUGUST, self::MONTH_SEPTEMBER, self::MONTH_OCTOBER, self::MONTH_NOVEMBER, self::MONTH_DECEMBER)) ? $month : null;
					$this->weekDay = null;
					break;
				default:
					// do nothing
					break;
			}
			$this->frequency = $frequency;
			$this->time      = $time;
		}

		/**
		 * @return integer
		 */
		public function getDay () {
			return $this->day;
		}

		/**
		 * @return string
		 */
		public function getFormat () {
			return $this->format;
		}

		/**
		 * @return string
		 */
		public function getFrequency () {
			return $this->frequency;
		}

		/**
		 * @return integer[]
		 */
		public function getGroups () {
			return $this->groups;
		}

		/**
		 * @return integer
		 */
		public function getMonth () {
			return $this->month;
		}

		/**
		 * @return integer
		 */
		public function getReportId () {
			return $this->reportId;
		}

		/**
		 * @return string[]
		 */
		public function getRoles () {
			return $this->roles;
		}

		/**
		 * @return string[]
		 */
		public function getRolesAndSubordinates () {
			return $this->rolesAndSubordinates;
		}

		/**
		 * @return string
		 */
		public function getTime () {
			return $this->time;
		}

		/**
		 * @return integer[]
		 */
		public function getUsers () {
			return $this->users;
		}

		/**
		 * @return integer
		 */
		public function getWeekDay () {
			return $this->weekDay;
		}

		/**
		 * @param string $format
		 *
		 * @return ReportSchedule
		 */
		public function setFormat ($format) {
			if (in_array ($format, array (self::FORMAT_BOTH, self::FORMAT_EXCEL, self::FORMAT_PDF))) {
				$this->format = $format;
			}
			return $this;
		}

		/**
		 * @param integer[] $groups
		 *
		 * @return ReportSchedule
		 */
		public function setGroups ($groups) {
			if ($groups === null) {
				$this->groups = null;
			} else if ((is_array ($groups)) && (!empty ($groups))) {
				$this->groups = array_values ($groups);
			}
			return $this;
		}

		/**
		 * @param integer $reportId
		 *
		 * @return ReportSchedule
		 */
		public function setReportId ($reportId) {
			$this->reportId = $reportId;
			return $this;
		}

		/**
		 * @param string[] $roles
		 *
		 * @return ReportSchedule
		 */
		public function setRoles ($roles) {
			if (($roles === null) || ((is_array ($roles)) && (!empty ($roles)))) {
				$this->roles = $roles;
			}
			return $this;
		}

		/**
		 * @param string[] $rolesAndSubordinates
		 *
		 * @return ReportSchedule
		 */
		public function setRolesAndSubordinates ($rolesAndSubordinates) {
			if (($rolesAndSubordinates === null) || ((is_array ($rolesAndSubordinates)) && (!empty ($rolesAndSubordinates)))) {
				$this->rolesAndSubordinates = $rolesAndSubordinates;
			}
			return $this;
		}

		/**
		 * @param integer[] $users
		 *
		 * @return ReportSchedule
		 */
		public function setUsers ($users) {
			if (($users === null) || ((is_array ($users)) && (!empty ($users)))) {
				$this->users = $users;
			}
			return $this;
		}

		/**
		 * @param ReportSchedule $schedule
		 */
		public function copyValuesFrom ($schedule) {
			if ((empty ($schedule)) || (!($schedule instanceof ReportSchedule))) {
				return;
			}

			$this->day                  = $schedule->getDay ();
			$this->format               = $schedule->getFormat ();
			$this->frequency            = $schedule->getFrequency ();
			$this->groups               = $schedule->getGroups ();
			$this->month                = $schedule->getMonth ();
			$this->roles                = $schedule->getRoles ();
			$this->rolesAndSubordinates = $schedule->getRolesAndSubordinates ();
			$this->time                 = $schedule->getTime ();
			$this->users                = $schedule->getUsers ();
			$this->weekDay              = $schedule->getWeekDay ();
		}

		/**
		 * @param integer $newReportId
		 *
		 * @return ReportSchedule
		 * @throws ReportScheduleException
		 */
		public function duplicate ($newReportId) {
			$this->validate ();
			$day = in_array ($this->frequency, array (self::FREQUENCY_BIWEEKLY, self::FREQUENCY_WEEKLY)) ? $this->weekDay : $this->day;
			return self::getInstance ($this->frequency, $this->time, $day, $this->month)
				->setFormat ($this->getFormat ())
				->setGroups ($this->getGroups ())
				->setReportId ($newReportId)
				->setRoles ($this->getRoles ())
				->setRolesAndSubordinates ($this->getRolesAndSubordinates ())
				->setUsers ($this->getUsers ());
		}

		/**
		 * @param ReportSchedule $schedule
		 *
		 * @return boolean
		 */
		public function isEqualTo ($schedule) {
			if (
				(empty ($schedule)) ||
				(!($schedule instanceof ReportSchedule)) ||
				($this->day != $schedule->getDay ()) ||
				($this->format != $schedule->getFormat ()) ||
				($this->frequency != $schedule->getFrequency ()) ||
				($this->month != $schedule->getMonth ()) ||
				($this->weekDay != $schedule->getWeekDay ()) ||
				($this->time != $schedule->getTime ()) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->groups, $schedule->getGroups ())) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->roles, $schedule->getRoles ())) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->rolesAndSubordinates, $schedule->getRolesAndSubordinates ())) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->users, $schedule->getUsers ()))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws ReportScheduleException
		 */
		public function validate () {
			if (empty ($this->format)) {
				throw new ReportScheduleException (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_FORMAT);
			} else if (empty ($this->frequency)) {
				throw new ReportScheduleException (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_FREQUENCY);
			} else if (empty ($this->time)) {
				throw new ReportScheduleException (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_TIME);
			} else if ((in_array ($this->frequency, array (self::FREQUENCY_BIWEEKLY, self::FREQUENCY_WEEKLY))) && (empty ($this->weekDay))) {
				throw new ReportScheduleException (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_WEEKDAY);
			} else if ((in_array ($this->frequency, array (self::FREQUENCY_MONTHLY, self::FREQUENCY_YEARLY))) && (empty ($this->day))) {
				throw new ReportScheduleException (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_DAY);
			} else if (($this->frequency == self::FREQUENCY_YEARLY) && (empty ($this->month))) {
				throw new ReportScheduleException (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_MONTH);
			} else if ((empty ($this->groups)) && (empty ($this->roles)) && (empty ($this->rolesAndSubordinates)) && (empty ($this->users))) {
				throw new ReportScheduleException (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_RECIPIENTS);
			}
		}

		/**
		 * @param integer $frequency
		 * @param string $time
		 * @param integer $day
		 * @param integer $month
		 *
		 * @return ReportSchedule
		 */
		public static function getInstance ($frequency, $time, $day = null, $month = null) {
			return new self ($frequency, $time, $day, $month);
		}

	}

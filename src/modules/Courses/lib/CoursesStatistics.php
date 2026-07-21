<?php
	require_once ('modules/Courses/lib/Course.php');

	class CoursesStatistics implements CoursesInterface {

		/** @var Course */
		private $courseSeen;

		/** @var string */
		private $lastTime;

		/** @var LessonsStatistics [] */
		private $lessons;

		/** @var string */
		private $seenDate;

		/** @var integer */
		private $userId;

		/**
		 * @param integer $original
		 *
		 * @return null|string
		 */
		private function timeSince ($original) {
			$chunks = array (
				array (60 * 60 * 24 * 365, 'año'),
				array (60 * 60 * 24 * 30, 'mes'),
				array (60 * 60 * 24 * 7, 'sem'),
				array (60 * 60 * 24, 'día'),
				array (60 * 60, 'h'),
				array (60, 'min'),
			);
			$today  = time ();
			$since  = ($today - intval ($original));
			if ($since < 0) {
				return null;
			}
			$j = count ($chunks);
			for ($i = 0; $i < $j; $i++) {
				$seconds = $chunks[ $i ][0];
				$name    = $chunks[ $i ][1];
				$count   = floor ($since / $seconds);
				if ($count != 0) {
					$print = ($count == 1) ? '1 ' . $name : (($name == 'mes') ? "$count {$name}es" : "$count {$name}s");
					if (($i + 1) < $j) {
						$secondsTwo = $chunks[ ($i + 1) ][0];
						$nameTwo    = $chunks[ ($i + 1) ][1];
						$countTwo   = floor (($since - ($seconds * $count)) / $secondsTwo);
						if ($countTwo != 0) {
							$print .= ($countTwo == 1) ? ', 1 ' . $nameTwo : ", $countTwo {$nameTwo}s";
						}
					}
					break;
				}
			}
			return isset ($print) ? $print : null;
		}

		/**
		 * @return Course
		 */
		public function getCourseSeen () {
			return $this->courseSeen;
		}

		/**
		 * @return string
		 */
		public function getLastTime () {
			return $this->lastTime;
		}

		/**
		 * @return string
		 */
		public function getSeenDate () {
			return $this->seenDate;
		}

		/**
		 * @return LessonsStatistics[]
		 */
		public function getLessons () {
			return $this->lessons;
		}

		/**
		 * @return integer
		 */
		public function getUserId () {
			return $this->userId;
		}

		/**
		 * @param $courseSeen
		 *
		 * @return CoursesStatistics
		 */
		public function setCourseSeen($courseSeen) {
			if ($courseSeen instanceof Course) {
				$this->courseSeen = $courseSeen;
			} else {
				$this->courseSeen = null;
			}
			return $this;
		}

		/**
		 * @param string $lastTime
		 *
		 * @return CoursesStatistics
		 */
		public function setLastTime ($lastTime) {
			if ($lastTime) {
				$this->lastTime = $this->timeSince ($lastTime);
			}
			return $this;
		}

		/**
		 * @param LessonsStatistics[] $lessons
		 *
		 * @return CoursesStatistics
		 */
		public function setLessons ($lessons) {
			if ((is_array ($lessons)) && (!empty ($lessons))) {
				$this->lessons = $lessons;
			} else {
				$this->lessons = null;
			}
			return $this;
		}

		/**
		 * @param $seenDate
		 *
		 * @return CoursesStatistics
		 */
		public function setSeenDate($seenDate) {
			$this->seenDate = $seenDate;
			return $this;
		}

		/**
		 * @param integer $userId
		 *
		 * @return CoursesStatistics
		 */
		public function setUserId ($userId) {
			$this->userId = $userId;
			return $this;
		}

		/**
		 * @return CoursesStatistics
		 */
		public static function getInstance () {
			return new self ();
		}

	}

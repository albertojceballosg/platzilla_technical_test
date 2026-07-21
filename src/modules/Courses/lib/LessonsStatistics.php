<?php
	require_once ('modules/Courses/lib/Course.php');

	class LessonsStatistics implements CoursesInterface {

		/** @var integer */
		private $courseId;

		/** @var string */
		private $lastTime;

		/** @var CourseLesson */
		private $lessonSeen;

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
		 * @return integer
		 */
		public function getCourseId () {
			return $this->courseId;
		}

		/**
		 * @return string
		 */
		public function getLastTime () {
			return $this->lastTime;
		}

		/**
		 * @return CourseLesson
		 */
		public function getLessonSeen () {
			return $this->lessonSeen;
		}

		/**
		 * @return string
		 */
		public function getSeenDate () {
			return $this->seenDate;
		}

		/**
		 * @return integer
		 */
		public function getUserId () {
			return $this->userId;
		}

		/**
		 * @param integer $courseId
		 *
		 * @return LessonsStatistics
		 */
		public function setCourseId ($courseId) {
			$this->courseId = $courseId;
			return $this;
		}

		/**
		 * @param CourseLesson $courseSeen
		 *
		 * @return LessonsStatistics
		 */
		public function setLessonSeen($courseSeen) {
			if ($courseSeen instanceof CourseLesson) {
				$this->lessonSeen = $courseSeen;
			} else {
				$this->lessonSeen = null;
			}
			return $this;
		}

		/**
		 * @param string $seenDate
		 *
		 * @return LessonsStatistics
		 */
		public function setSeenDate ($seenDate) {
			$this->seenDate = $seenDate;
			return $this;
		}

		/**
		 * @param integer $lastTime
		 *
		 * @return LessonsStatistics
		 */
		public function setLastTime ($lastTime) {
			if ($lastTime) {
				$this->lastTime = $this->timeSince ($lastTime);
			} else {
				$this->lastTime = null;
			}
			return $this;
		}

		/**
		 * @param integer $userId
		 *
		 * @return LessonsStatistics
		 */
		public function setUserId ($userId) {
			$this->userId = $userId;
			return $this;
		}

		/**
		 * @return LessonsStatistics
		 */
		public static function getInstance () {
			return new self ();
		}

	}

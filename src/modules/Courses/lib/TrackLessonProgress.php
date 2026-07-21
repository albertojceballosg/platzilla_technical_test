<?php
	
	require_once ('modules/Courses/lib/CourseException.php');
	require_once ('modules/Courses/lib/CoursesInterface.php');
	class TrackLessonProgress implements CoursesInterface {
		
		/** @var integer */
		private $courseId;
		
		/** @var string */
		private $endDate;
		
		/** @var integer */
		private $lessonId;
		
		/** @var integer */
		private $lessonToUserId;
		
		/** @var string */
		private $starDate;
		
		/** @var string */
		private $status;
		
		/** @var integer */
		private $userId;
		
		/**
		 * @return integer
		 */
		public function getCourseId () {
			return $this->courseId;
		}
		
		/**
		 * @return string
		 */
		public function getEndDate () {
			return $this->endDate;
		}
		
		/**
		 * @return integer
		 */
		public function getLessonId () {
			return $this->lessonId;
		}
		
		/**
		 * @return integer
		 */
		public function getLessonToUserId () {
			return $this->lessonToUserId;
		}
		
		/**
		 * @return string
		 */
		public function getStartDate () {
			return $this->starDate;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
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
		 * @return TrackLessonProgress
		 */
		public function setCourseId ($courseId) {
			$this->courseId = $courseId;
			return $this;
		}
		
		/**
		 * @param string $endDate
		 *
		 * @return TrackLessonProgress
		 */
		public function setEndDate ($endDate) {
			$this->endDate = $endDate;
			return $this;
		}
		
		/**
		 * @param integer $lessonId
		 *
		 * @return TrackLessonProgress
		 */
		public function setLessonId ($lessonId) {
			$this->lessonId = $lessonId;
			return $this;
		}
		
		/**
		 * @param integer $lessonToUserId
		 *
		 * @return TrackLessonProgress
		 */
		public function setLessonToUserId ($lessonToUserId) {
			$this->lessonToUserId = $lessonToUserId;
			return $this;
		}
		
		/**
		 * @param string $startDate
		 *
		 * @return TrackLessonProgress
		 */
		public function setStartDate ($startDate) {
			$this->starDate = $startDate;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return TrackLessonProgress
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param integer $userId
		 *
		 * @return TrackLessonProgress
		 */
		public function setUserId ($userId) {
			$this->userId = $userId;
			return $this;
		}
		
		/**
		 * @throws CourseException
		 */
		public function validate () {
			if (empty($this->courseId)) {
				throw new CourseException(CourseException::ERROR_COURSE_EMPTY);
			}
			if (empty($this->userId)) {
				throw new CourseException(CourseException::ERROR_COURSE_INVALID_USER);
			}
			if (empty($this->lessonId)) {
				throw new CourseException(CourseException::ERROR_COURSE_INVALID_LESSON);
			}
			if (empty($this->status)) {
				throw new CourseException(CourseException::ERROR_COURSE_EMPTY_STATUS);
			}
		}
		
		/**
		 * @return TrackLessonProgress
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

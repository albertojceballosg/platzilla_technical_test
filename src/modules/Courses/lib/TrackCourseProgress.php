<?php
	
	require_once ('modules/Courses/lib/CourseException.php');
	require_once ('modules/Courses/lib/CoursesInterface.php');
	class TrackCourseProgress implements CoursesInterface {
		
		/** @var integer */
		private $courseId;
		
		/** @var integer */
		private $courseToUserId;
		
		/** @var string */
		private $endDate;
		
		/** @var string */
		private $startDate;
		
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
		 * @return integer
		 */
		public function getCourseToUserId () {
			return $this->courseToUserId;
		}
		
		/**
		 * @return string
		 */
		public function getEndDate () {
			return $this->endDate;
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
		 * @return integer
		 */
		public function getUserId () {
			return $this->userId;
		}
		
		/**
		 * @param integer $courseId
		 *
		 * @return TrackCourseProgress
		 */
		public function setCourseId ($courseId) {
			$this->courseId = $courseId;
			return $this;
		}
		
		/**
		 * @param integer $courseToUserId
		 *
		 * @return TrackCourseProgress
		 */
		public function setCourseToUserId ($courseToUserId) {
			$this->courseToUserId = $courseToUserId;
			return $this;
		}
		
		/**
		 * @param string $endDate
		 *
		 * @return TrackCourseProgress
		 */
		public function setEndDate ($endDate) {
			$this->endDate = $endDate;
			return $this;
		}
		
		/**
		 * @param string $startDate
		 *
		 * @return TrackCourseProgress
		 */
		public function setStartDate ($startDate) {
			$this->startDate = $startDate;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return TrackCourseProgress
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param integer $userId
		 *
		 * @return TrackCourseProgress
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
			if (empty($this->status)) {
				throw new CourseException(CourseException::ERROR_COURSE_EMPTY_STATUS);
			}
		}
		
		/**
		 * @return TrackCourseProgress
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

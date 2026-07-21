<?php
	require_once ('modules/Courses/lib/CoursesInterface.php');
	require_once ('modules/Courses/lib/CourseException.php');
	require_once ('modules/Courses/lib/CourseLessonException.php');
	require_once ('modules/Courses/lib/LessonTestResults.php');
	class LessonEvaluated implements CoursesInterface {
		/** @var integer */
		private $courseId;
		
		/** @var integer */
		private $id;
		
		/** @var integer */
		private $lessonId;
		
		/** @var LessonTestResults[] */
		private $lessonTestResults;
		
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
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return integer
		 */
		public function getLessonId () {
			return $this->lessonId;
		}
		
		/**
		 * @return LessonTestResults[]
		 */
		public function getLessonTestResults () {
			return $this->lessonTestResults;
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
		 * @return LessonEvaluated
		 */
		public function setCourseId ($courseId) {
			$this->courseId = $courseId;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return LessonEvaluated
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param integer $lessonId
		 *
		 * @return LessonEvaluated
		 */
		public function setLessonId ($lessonId) {
			$this->lessonId = $lessonId;
			return $this;
		}
		
		/**
		 * @param LessonTestResults[] $lessonTestResults
		 *
		 * @return LessonEvaluated
		 */
		public function setLessonTestResults ($lessonTestResults) {
			$this->lessonTestResults = $lessonTestResults;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return LessonEvaluated
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param integer $userId
		 *
		 * @return LessonEvaluated
		 */
		public function setUserId ($userId) {
			$this->userId = $userId;
			return $this;
		}
		
		/**
		 * @return void
		 *
		 * @throws CourseLessonException
		 * @throws CourseException
		 */
		public function validate () {
			if (empty ($this->courseId)) {
				throw new CourseException (CourseException::ERROR_COURSE_EMPTY_COURSE_ID);
			}
			
			if (empty ($this->lessonId)) {
				throw new CourseException (CourseLessonException::ERROR_COURSE_LESSON_EMPTY_COURSE_ID);
			}
			
			if (empty ($this->lessonTestResults)) {
				throw new CourseException (CourseLessonException::ERROR_COURSE_EMPTY_TEST_RESULTS);
			}
			
			if (empty ($this->userId)) {
				throw new CourseException (CourseException::ERROR_LESSON_INVALID_USER);
			}
		}
		
		/**
		 * @return LessonEvaluated
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

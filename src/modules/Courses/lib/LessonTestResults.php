<?php
	require_once ('modules/Courses/lib/CoursesInterface.php');
	require_once ('modules/Courses/lib/CourseTestException.php');
	class LessonTestResults implements CoursesInterface {
		
		/** @var integer */
		private $answerId;
		
		/** @var integer */
		private $evaluateId;
		
		/** @var integer */
		private $id;
		
		/** @var integer */
		private $questionId;
		
		/** @var string */
		private $status;
		
		/**
		 * @return integer
		 */
		public function getAnswerId () {
			return $this->answerId;
		}
		
		/**
		 * @return integer
		 */
		public function getEvaluateId () {
			return $this->evaluateId;
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
		public function getQuestionId () {
			return $this->questionId;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		
		/**
		 * @param integer $answerId
		 *
		 * @return LessonTestResults
		 */
		public function setAnswerId ($answerId) {
			$this->answerId = $answerId;
			return $this;
		}
		
		/**
		 * @param integer $evaluateId
		 *
		 * @return LessonTestResults
		 */
		public function setEvaluateId ($evaluateId) {
			$this->evaluateId = $evaluateId;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return LessonTestResults
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param integer $questionId
		 *
		 * @return LessonTestResults
		 */
		public function setQuestionId ($questionId) {
			$this->questionId = $questionId;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return LessonTestResults
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		
		/**
		 * @return LessonTestResults
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

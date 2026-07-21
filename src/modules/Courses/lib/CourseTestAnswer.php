<?php
	require_once ('modules/Courses/lib/CourseTestAnswerException.php');

	class CourseTestAnswer {
		/** @var integer */
		private $id;

		/** @var boolean */
		private $correct;
		
		/** @var string */
		private $feedback;

		/** @var integer */
		private $questionId;

		/** @var string */
		private $statement;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getStatement () {
			return $this->statement;
		}

		/**
		 * @return integer
		 */
		public function getQuestionId () {
			return $this->questionId;
		}

		/**
		 * @return boolean
		 */
		public function isCorrect () {
			return $this->correct;
		}

		/**
		 * @return string
		 */
		public function getFeedback () {
			return $this->feedback;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return CourseTestAnswer
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = intval ($id);
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param boolean $isCorrect
		 *
		 * @return CourseTestAnswer
		 */
		public function setCorrect ($isCorrect) {
			if (filter_var ($isCorrect, FILTER_VALIDATE_BOOLEAN) !== false) {
				$this->correct = boolval ($isCorrect);
			} else {
				$this->correct = false;
			}
			return $this;
		}

		/**
		 * @param string $feedback
		 *
		 * @return CourseTestAnswer
		 */
		public function setFeedback ($feedback) {
			if (is_scalar ($feedback)) {
				$this->feedback = $feedback;
			} else {
				$this->feedback = null;
			}
			return $this;
		}
		
		/**
		 * @param integer $questionId
		 *
		 * @return CourseTestAnswer
		 */
		public function setQuestionId ($questionId) {
			if ((is_numeric ($questionId)) && ($questionId > 0) && (intval ($questionId) == $questionId)) {
				$this->questionId = intval ($questionId);
			} else {
				$this->questionId = null;
			}
			return $this;
		}

		/**
		 * @param string $statement
		 *
		 * @return CourseTestAnswer
		 */
		public function setStatement ($statement) {
			if (is_scalar ($statement)) {
				$this->statement = $statement;
			} else {
				$this->statement = null;
			}
			return $this;
		}

		/**
		 * @throws CourseTestAnswerException
		 */
		public function validate () {
			if (empty ($this->statement)) {
				throw new CourseTestAnswerException (CourseTestAnswerException::ERROR_COURSE_TEST_ANSWER_EMPTY_STATEMENT);
			}
		}

		/**
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->id,
					$this->correct,
					$this->questionId,
					$this->statement,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->correct,
				$this->questionId,
				$this->statement,
			) = unserialize ($serialized);
		}

		/**
		 * @return CourseTestAnswer
		 */
		public static function getInstance () {
			return new self ();
		}

	}

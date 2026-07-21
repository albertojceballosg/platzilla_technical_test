<?php
	require_once ('modules/Courses/lib/CourseTestQuestionException.php');
	require_once ('modules/Courses/lib/CourseTestAnswer.php');

	class CourseTestQuestion implements Serializable {
		const TYPE_MULTIPLE_CHOICE = 'MULTIPLE CHOICE';
		const TYPE_SINGLE_CHOICE   = 'SINGLE CHOICE';

		/** @var integer */
		private $id;

		/** @var CourseTestAnswer[] */
		private $answers;

		/** @var string */
		private $statement;

		/** @var integer */
		private $testId;

		/** @var string */
		private $type;

		/**
		 * @throws CourseTestQuestionException
		 */
		private function validateAnswers () {
			if (empty ($this->answers)) {
				throw new CourseTestQuestionException (CourseTestQuestionException::ERROR_COURSE_TEST_QUESTION_EMPTY_ANSWERS);
			}
			$hasCorrectAnswers = false;
			foreach ($this->answers as $answer) {
				if (!($answer instanceof CourseTestAnswer)) {
					throw new CourseTestQuestionException (CourseTestQuestionException::ERROR_COURSE_TEST_QUESTION_INVALID_ANSWER);
				} else {
					$answer->validate ();
				}
				$hasCorrectAnswers = ($hasCorrectAnswers || $answer->isCorrect ());
			}
			if (!$hasCorrectAnswers) {
				throw new CourseTestQuestionException (CourseTestQuestionException::ERROR_COURSE_TEST_QUESTION_EMPTY_CORRECT_ANSWERS);
			}
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return CourseTestAnswer[]
		 */
		public function getAnswers () {
			return $this->answers;
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
		public function getTestId () {
			return $this->testId;
		}

		/**
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * @param integer $id
		 *
		 * @return CourseTestQuestion
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
		 * @param CourseTestAnswer[] $answers
		 *
		 * @return CourseTestQuestion
		 */
		public function setAnswers ($answers) {
			if ((is_array ($answers)) && (!empty ($answers))) {
				$this->answers = $answers;
			} else {
				$this->answers = null;
			}
			return $this;
		}

		/**
		 * @param string $statement
		 *
		 * @return CourseTestQuestion
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
		 * @param integer $testId
		 *
		 * @return CourseTestQuestion
		 */
		public function setTestId ($testId) {
			if ((is_numeric ($testId)) && ($testId > 0) && (intval ($testId) == $testId)) {
				$this->testId = intval ($testId);
			} else {
				$this->testId = null;
			}
			return $this;
		}

		/**
		 * @param string $type
		 *
		 * @return CourseTestQuestion
		 */
		public function setType ($type) {
			if (in_array ($type, self::getAvailableTypes ())) {
				$this->type = $type;
			} else {
				$this->type = null;
			}
			return $this;
		}

		/**
		 * @throws CourseTestQuestionException
		 */
		public function validate () {
			if (empty ($this->statement)) {
				throw new CourseTestQuestionException (CourseTestQuestionException::ERROR_COURSE_TEST_QUESTION_EMPTY_STATEMENT);
			} else if (empty ($this->type)) {
				throw new CourseTestQuestionException (CourseTestQuestionException::ERROR_COURSE_TEST_QUESTION_EMPTY_TYPE);
			}
			$this->validateAnswers ();
		}

		/**
		 * @return string
		 */
		public function serialize () {
			if (!empty ($this->answers)) {
				$serializedAnswers = array ();
				foreach ($this->answers as $answer) {
					$serializedAnswers [] = $answer->serialize ();
				}
			} else {
				$serializedAnswers = null;
			}

			return serialize (
				array (
					$this->id,
					$this->statement,
					$this->testId,
					$this->type,
					$serializedAnswers,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->statement,
				$this->testId,
				$this->type,
				$serializedAnswers,
			) = unserialize ($serialized);

			if (!empty ($serializedAnswers)) {
				foreach ($serializedAnswers as $serializedAnswer) {
					$answer = CourseTestAnswer::getInstance ();
					$answer->unserialize ($serializedAnswer);
					$this->answers [] = $answer;
				}
			} else {
				$this->answers = null;
			}
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableTypes () {
			return array (self::TYPE_MULTIPLE_CHOICE, self::TYPE_SINGLE_CHOICE);
		}

		/**
		 * @return CourseTestQuestion
		 */
		public static function getInstance () {
			return new self ();
		}

	}

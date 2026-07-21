<?php
	require_once ('modules/Courses/lib/CourseTestException.php');
	require_once ('modules/Courses/lib/CourseTestQuestion.php');

	class CourseTest implements Serializable {
		/** @var string */
		private $description;

		/** @var string */
		private $feedback;

		/** @var string */
		private $feedbackNotApproved;
		
		/** @var integer */
		private $lessonId;

		/** @var float */
		private $minimumScore;

		/** @var CourseTestQuestion[] */
		private $questions;

		/** @var integer */
		private $totalQuestionsPerTest;

		/**
		 * @throws CourseTestException
		 * @throws CourseTestQuestionException
		 */
		private function validateQuestions () {
			foreach ($this->questions as $question) {
				if (!($question instanceof CourseTestQuestion)) {
					throw new CourseTestException (CourseTestException::ERROR_COURSE_TEST_INVALID_QUESTION);
				} else {
					$question->validate ();
				}
			}
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return string
		 */
		public function getFeedback () {
			return $this->feedback;
		}

		/**
		 * @return string
		 */
		public function getFeedbackNotApproved () {
			return $this->feedbackNotApproved;
		}
		
		/**
		 * @return integer
		 */
		public function getLessonId () {
			return $this->lessonId;
		}

		/**
		 * @return float
		 */
		public function getMinimumScore () {
			return $this->minimumScore;
		}

		/**
		 * @return CourseTestQuestion[]
		 */
		public function getQuestions () {
			return $this->questions;
		}

		/**
		 * @return integer
		 */
		public function getTotalQuestionsPerTest () {
			return $this->totalQuestionsPerTest;
		}

		/**
		 * @param string $description
		 *
		 * @return CourseTest
		 */
		public function setDescription ($description) {
			if (is_scalar ($description)) {
				$this->description = $description;
			} else {
				$this->description = null;
			}
			return $this;
		}

		/**
		 * @param string $feedback
		 *
		 * @return CourseTest
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
		 * @param string $feedbackNotApproved
		 *
		 * @return CourseTest
		 */
		public function setFeedbackNotApproved ($feedbackNotApproved) {
			if (is_scalar ($feedbackNotApproved)) {
				$this->feedbackNotApproved = $feedbackNotApproved;
			} else {
				$this->feedbackNotApproved = null;
			}
			return $this;
		}
		
		/**
		 * @param integer $lessonId
		 *
		 * @return CourseTest
		 */
		public function setLessonId ($lessonId) {
			if ((is_numeric ($lessonId)) && ($lessonId > 0)) {
				$this->lessonId = intval ($lessonId);
			} else {
				$this->lessonId = null;
			}
			return $this;
		}

		/**
		 * @param integer $minimumScore
		 *
		 * @return CourseTest
		 */
		public function setMinimumScore ($minimumScore) {
			if ((is_numeric ($minimumScore)) && ($minimumScore >= 0) && ($minimumScore <= 100) && (floatval ($minimumScore) == $minimumScore)) {
				$this->minimumScore = $minimumScore;
			} else {
				$this->minimumScore = null;
			}
			return $this;
		}

		/**
		 * @param CourseTestQuestion[] $questions
		 *
		 * @return CourseTest
		 */
		public function setQuestions ($questions) {
			if ((is_array ($questions)) && (!empty ($questions))) {
				$this->questions = $questions;
			} else {
				$this->questions = null;
			}
			return $this;
		}

		/**
		 * @param integer $totalQuestionsPerTest
		 *
		 * @return CourseTest
		 */
		public function setTotalQuestionsPerTest ($totalQuestionsPerTest) {
			if ((is_numeric ($totalQuestionsPerTest)) && ($totalQuestionsPerTest > 0) && (intval ($totalQuestionsPerTest) == $totalQuestionsPerTest)) {
				$this->totalQuestionsPerTest = intval ($totalQuestionsPerTest);
			} else {
				$this->totalQuestionsPerTest = null;
			}
			return $this;
		}

		/**
		 * @throws CourseTestException
		 */
		public function validate () {
			if (empty ($this->description)) {
				throw new CourseTestException (CourseTestException::ERROR_COURSE_TEST_EMPTY_DESCRIPTION);
			} else if ($this->minimumScore === null) {
				throw new CourseTestException (CourseTestException::ERROR_COURSE_TEST_EMPTY_MINIMUM_SCORE);
			} else if (empty ($this->questions)) {
				throw new CourseTestException (CourseTestException::ERROR_COURSE_TEST_EMPTY_QUESTIONS);
			} else if (empty ($this->totalQuestionsPerTest)) {
				throw new CourseTestException (CourseTestException::ERROR_COURSE_TEST_EMPTY_TOTAL_QUESTIONS_PER_TEST);
			} else if ($this->totalQuestionsPerTest > count ($this->questions)) {
				throw new CourseTestException (CourseTestException::ERROR_COURSE_TEST_INVALID_TOTAL_QUESTIONS_PER_TEST);
			}
			$this->validateQuestions ();
		}

		/**
		 * @return string
		 */
		public function serialize () {
			if (!empty ($this->questions)) {
				$serializedQuestions = array ();
				foreach ($this->questions as $question) {
					$serializedQuestions [] = $question->serialize ();
				}
			} else {
				$serializedQuestions = null;
			}

			return serialize (
				array (
					$this->description,
					$this->feedback,
					$this->feedbackNotApproved,
					$this->lessonId,
					$this->minimumScore,
					$this->totalQuestionsPerTest,
					$serializedQuestions,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->description,
				$this->feedback,
				$this->feedbackNotApproved,
				$this->lessonId,
				$this->minimumScore,
				$this->totalQuestionsPerTest,
				$serializedQuestions,
			) = unserialize ($serialized);

			if (!empty ($serializedQuestions)) {
				foreach ($serializedQuestions as $serializedQuestion) {
					$question = CourseTestQuestion::getInstance ();
					$question->unserialize ($serializedQuestion);
					$this->questions [] = $question;
				}
			} else {
				$this->questions = null;
			}
		}

		/**
		 * @return CourseTest
		 */
		public static function getInstance () {
			return new self ();
		}

	}

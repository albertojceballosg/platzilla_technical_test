<?php
	require_once ('modules/questionnaire/Objects/QuestionInterface.class.php');
	require_once ('modules/questionnaire/Objects/QuestionException.class.php');
	require_once ('modules/questionnaire/Objects/RangeGroup.class.php');
	
	class QuestionToGroup implements QuestionInterface {
		
		/** @var string */
		private $groupName;
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $question;
		
		/** @var integer */
		private $questionId;
		
		/** @var RangeGroup[] */
		private $ranges;
		
		/** @var string */
		private $themeName;
		
		/**
		 * @return string
		 */
		public function getGroupName () {
			return $this->groupName;
		}
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return string
		 */
		public function getQuestion () {
			return $this->question;
		}
		
		/**
		 * @return integer
		 */
		public function getQuestionId () {
			return $this->questionId;
		}
		
		/**
		 * @return RangeGroup[]
		 */
		public function getRanges () {
			return $this->ranges;
		}
		
		/**
		 * @return string
		 */
		public function getThemeName () {
			return $this->themeName;
		}
		
		/**
		 * @param string $groupName
		 *
		 * @return QuestionToGroup
		 */
		public function setGroupName ($groupName) {
			$this->groupName = $groupName;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return QuestionToGroup
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $question
		 *
		 * @return QuestionToGroup
		 */
		public function setQuestion ($question) {
			$this->question = $question;
			return $this;
		}
		
		/**
		 * @param integer $questionId
		 *
		 * @return QuestionToGroup
		 */
		public function setQuestionId ($questionId) {
			$this->questionId = $questionId;
			return $this;
		}
		
		/**
		 * @param ResponseOption[] $responseOptions
		 *
		 * @return QuestionToGroup
		 */
		public function setResponseOptions ($responseOptions) {
			$this->responseOptions = $responseOptions;
			return  $this;
		}
		
		/**
		 * @param RangeGroup[] $ranges
		 *
		 * @return QuestionToGroup
		 */
		public function setRanges ($ranges) {
			$this->ranges = $ranges;
			return $this;
		}
		
		/**
		 * @param string $themeName
		 *
		 * @return QuestionToGroup
		 */
		public function setThemeName ($themeName) {
			$this->themeName = $themeName;
			return $this;
		}
		
		/**
		 * @return QuestionToGroup
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

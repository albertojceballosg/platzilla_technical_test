<?php
	require_once ('modules/questionnaire/Objects/QuestionInterface.class.php');
	class SurveyNav implements QuestionInterface {
		
		/** @var string */
		private $responseName;
		
		/** @var integer */
		private $questionId;
		
		/** @var integer */
		private $questionnairesId;
		
		/** @var integer */
		private $questonToAnswereId;
		
		/** @var integer */
		private $surveyNavId;
		
		/**
		 * @return string
		 */
		public function getResponseName () {
			return $this->responseName;
		}
		
		/**
		 * @return integer
		 */
		public function getQuestionId () {
			return $this->questionId;
		}
		
		/**
		 * @return integer
		 */
		public function getQuestionnairesId () {
			return $this->questionnairesId;
		}
		
		/**
		 * @return integer
		 */
		public function getQuestonToAnswereId () {
			return $this->questonToAnswereId;
		}
		
		/**
		 * @return integer
		 */
		public function getSurveyNavId () {
			return $this->surveyNavId;
		}
		
		/**
		 * @param string $responseName
		 *
		 * @return SurveyNav
		 */
		public function setResponseName ($responseName) {
			$this->responseName = $responseName;
			return $this;
		}
		
		/**
		 * @param integer $questionId
		 *
		 * @return SurveyNav
		 */
		public function setQuestionId ($questionId) {
			$this->questionId = $questionId;
			return $this;
		}
		
		/**
		 * @param integer $questionnairesId
		 *
		 * @return SurveyNav
		 */
		public function setQuestionnairesId ($questionnairesId) {
			$this->questionnairesId = $questionnairesId;
			return $this;
		}
		
		/**
		 * @param integer $questonToAnswereId
		 *
		 * @return SurveyNav
		 */
		public function setQuestonToAnswereId ($questonToAnswereId) {
			$this->questonToAnswereId = $questonToAnswereId;
			return $this;
		}
		
		/**
		 * @param integer $surveyNavId
		 *
		 * @return SurveyNav
		 */
		public function setSurveyNavId ($surveyNavId) {
			$this->surveyNavId = $surveyNavId;
			return $this;
		}
		
		/**
		 * @return SurveyNav
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

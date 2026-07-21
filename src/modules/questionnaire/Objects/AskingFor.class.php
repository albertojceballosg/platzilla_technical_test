<?php
	require_once ('modules/questionnaire/Objects/QuestionInterface.class.php');
	require_once ('modules/questionnaire/Objects/QuestionException.class.php');
	
	class AskingFor implements QuestionInterface {
		
		/** @var string */
		private  $calculationType;
		
		/** @var float */
		private $calculationResult;
	
		/** @var string */
		private $description;
		
		/** @var string */
		private $feedBack;
		
		/** @var string */
		private $help;
		
		/** @var string */
		private $htmlResponse;
		
		/** @var integer */
		private $id;
		
		/** @var integer */
		public $idQuestionRow;
		
		/** @var float */
		private $puctuation;
		
		/** @var string */
		private $question;
		
		/** @var string */
		private $questionForm;
		
		/** @var integer */
		private $questionGroupId;
		
		/** @var integer */
		private $questionnaireId;
		
		/** @var integer */
		private $questionStageId;
		
		/** @var string */
		private $questionType;
		
		/** @var ResponseOption[] */
		private $responseOption;
		
		/** @var integer */
		private $sequence;
		
		/** @var string */
		private $status;
		
		/** @var integer */
		private $surveyTotal;
		
		/** @var string */
		private $urlVideo;
		
		/** @var float */
		private $weighing;
		
		/**
		 * @return string
		 */
		public function getCalculationType () {
			return $this->calculationType;
		}
		
		/**
		 * @return float
		 */
		public function getCalculationResult () {
			$result = number_format ($this->calculationResult, 2, ',', '.');
			return $result;
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
		public function getFeedBack () {
			return $this->feedBack;
		}
		
		/**
		 * @return string
		 */
		public function getHelp () {
			return $this->help;
		}
		
		/**
		 * @return string
		 */
		public function getHtmlResponse () {
			return $this->htmlResponse;
		}
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return float
		 */
		public function getPuctuation () {
			return $this->puctuation;
		}
		
		/**
		 * @return string
		 */
		public function getQuestion () {
			return $this->question;
		}
		
		/**
		 * @return string
		 */
		public function getQuestionForm () {
			return $this->questionForm;
		}
		
		/**
		 * @return integer
		 */
		public function getQuestionGroupId () {
			return $this->questionGroupId;
		}
		
		/**
		 * @return integer
		 */
		public function getQuestionnaireId () {
			return $this->questionnaireId;
		}
		
		/**
		 * @return integer
		 */
		public function getquestionStageId () {
			return $this->questionStageId;
		}
		
		/**
		 * @return string
		 */
		public function getQuestionType () {
			return $this->questionType;
		}
		
		/**
		 * @return ResponseOption[]
		 */
		public function getResponseOption () {
			return $this->responseOption;
		}
		
		/**
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
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
		public function getSurveyTotal () {
			return $this->surveyTotal;
		}
		
		/**
		 * @return string
		 */
		public function getUrlVideo () {
			return $this->urlVideo;
		}
		
		/**
		 * @return float
		 */
		public function getWeighing () {
			return $this->weighing;
		}
		
		/**
		 * @param string $calculationType
		 *
		 * @return AskingFor
		 */
		public function setCalculationType ($calculationType) {
			$this->calculationType = $calculationType;
			return $this;
		}
		
		/**
		 * @param $calculationResult
		 *
		 * @return AskingFor
		 */
		public function setCalculationResult ($calculationResult) {
			$this->calculationResult = $calculationResult;
			return $this;
		}
		
		/**
		 * @param string $description
		 *
		 * @return AskingFor
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param string $feedBack
		 *
		 * @return AskingFor
		 */
		public function setFeedBack ($feedBack) {
			$this->feedBack = $feedBack;
			return $this;
		}
		
		/**
		 * @param string $help
		 *
		 * @return AskingFor
		 */
		public function setHelp ($help) {
			$this->help = $help;
			return $this;
		}
		
		/**
		 * @param string $htmlResponse
		 *
		 * @return AskingFor
		 */
		public function setHtmlResponse ($htmlResponse) {
			$this->htmlResponse = $htmlResponse;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return AskingFor
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param float $puctuation
		 *
		 * @return AskingFor
		 */
		public function setPuctuation ($puctuation) {
			$this->puctuation = $puctuation;
			return $this;
		}
		
		/**
		 * @param string $question
		 *
		 * @return AskingFor
		 */
		public function setQuestion ($question) {
			$this->question = $question;
			return $this;
		}
		
		/**
		 * @param integer $questionForm
		 *
		 * @return AskingFor
		 */
		public function setQuestionForm ($questionForm) {
			$this->questionForm = $questionForm;
			return $this;
		}
		
		/**
		 * @param integer $questionGroupId
		 *
		 * @return AskingFor
		 */
		public function setQuestionGroupId ($questionGroupId) {
			$this->questionGroupId = $questionGroupId;
			return $this;
		}
		
		/**
		 * @param integer $questionnaireId
		 *
		 * @return AskingFor
		 */
		public function setQuestionnaireId ($questionnaireId) {
			$this->questionnaireId = $questionnaireId;
			return $this;
		}
		
		/**
		 * @param integer $questionStageId
		 *
		 * @return AskingFor
		 */
		public function setQuestionStageId ($questionStageId) {
			$this->questionStageId = $questionStageId;
			return $this;
		}
		
		/**
		 * @param string $questionType
		 *
		 * @return AskingFor
		 */
		public function setQuestionType ($questionType) {
			$this->questionType = $questionType;
			return $this;
		}
		
		/**
		 * @param ResponseOption[] $responseOption
		 *
		 * @return AskingFor
		 */
		public function setResponseOption ($responseOption) {
			$this->responseOption = $responseOption;
			return $this;
		}
		
		/**
		 * @param integer $sequence
		 *
		 * @return AskingFor
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return AskingFor
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param integer $surveyTotal
		 *
		 * @return AskingFor
		 */
		public function setSurveyTotal ($surveyTotal) {
			$this->surveyTotal = $surveyTotal;
			return $this;
		}
		
		/**
		 * @param string $urlVideo
		 *
		 * @return AskingFor
		 */
		public function setUrlVideo ($urlVideo) {
			$this->urlVideo = $urlVideo;
			return $this;
		}
		
		/**
		 * @param float $weighing
		 *
		 * @return AskingFor
		 */
		public function setWeighing ($weighing) {
			$this->weighing = $weighing;
			return $this;
		}
		
		/**
		 * @throws QuestionException
		 */
		public function validate () {
			if (empty ($this->calculationType)) {
				throw new QuestionException(QuestionException::ERROR_CALCULATION_TYPE_EMPTY);
			} else if (empty ($this->question)) {
				throw new QuestionException(QuestionException::ERROR_QUESTION_EMPTY);
			} else if (empty ($this->questionForm)) {
				throw new QuestionException(QuestionException::ERROR_QUESTION_FORM_EMPTY);
			} else if (empty ($this->questionType)) {
				throw new QuestionException(QuestionException::ERROR_QUESTION_TYPE_EMPTY);
			} else if (empty ($this->puctuation)) {
				throw new QuestionException(QuestionException::ERROR_QUESTION_PUCTUATION_EMPTY);
			} else if (empty ($this->weighing)) {
				throw new QuestionException(QuestionException::ERROR_QUESTION_WEIGHING_EMPTY);
			}
		}
		
		/**
		 * @return AskingFor
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

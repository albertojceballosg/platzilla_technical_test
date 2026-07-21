<?php
	require_once ('modules/questionnaire/Objects/SurveyNav.class.php');
	require_once ('modules/questionnaire/Objects/QuestionInterface.class.php');
	require_once ('modules/questionnaire/Objects/RangeGroup.class.php');
	class ResponseOption implements QuestionInterface {
		
		/** @var integer */
		private $additionalData;
		
		/** @var string */
		private $feedBack;
		
		/** @var integer */
		private $id;
		
		/** @var null|string */
		private $image;
		
		/** @var string */
		private $imageType;
		
		/** @var string */
		private $mainLabel;
		
		/** @var string */
		private $name;
		
		/** @var integer */
		private $questionId;
		
		/** @var string */
		private $secondLabel;
		
		/** @var string */
		private $selected;
		
		/** @var integer */
		private $sequence;
		
		/** @var string */
		private $status;
		
		/** @var array */
		public $summaryRow;
		
		/** @var SurveyNav */
		public $surveyNav;
		
		/** @var float */
		private $suveyPorcent;
		
		/** @var integer */
		private $surveyTotal;
		
		/** @var integer */
		private $surveySecondTotal;
		
		/** @var string */
		private $value;
		
		/**
		 * @param integer|null $length
		 * @param boolean $isPin
		 *
		 * @return string
		 */
		public function randomName ($length = null, $isPin = false) {
			$length      = (empty($length)) ? 12 : $length;
			$alphabet    = (!$isPin) ? 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789' : '0123456789';
			$pass        = array ();
			$alphaLength = (strlen ($alphabet) - 1);
			for ($i = 0; $i < $length; $i++) {
				$n      = rand(0, $alphaLength);
				$pass[] = $alphabet [$n];
			}
			return implode ($pass);
		}
		
		/**
		 * @return integer
		 */
		public function getAdditionalData () {
			return $this->additionalData;
		}
		
		/**
		 * @return string
		 */
		public function getFeedBack () {
			return $this->feedBack;
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
		public function getImage () {
			return $this->image;
		}
		
		/**
		 * @return string
		 */
		public function getImageType () {
			return $this->imageType;
		}
		
		/**
		 * @return string
		 */
		public function getMainLabel () {
			return $this->mainLabel;
		}
		
		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
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
		public function getSecondLabel () {
			return $this->secondLabel;
		}
		
		/**
		 * @return string
		 */
		public function getSelected () {
			return $this->selected;
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
		 * @return array
		 */
		public function getSummaryRow () {
			return $this->summaryRow;
		}
		
		/**
		 * @return SurveyNav
		 */
		public function getSurveyNav () {
			return $this->surveyNav;
		}
		
		/**
		 * @return float
		 */
		public function getSuveyPorcent () {
			$porcent = number_format ($this->suveyPorcent, 2, ',', '.');
			return $porcent;
		}
		
		/**
		 * @return integer
		 */
		public function getSurveyTotal () {
			return $this->surveyTotal;
		}
		
		/**
		 * @return integer
		 */
		public function getSurveySecondTotal () {
			return $this->surveySecondTotal;
		}
		
		/**
		 * @return string
		 */
		public function getValue () {
			return $this->value;
		}
		
		/**
		 * @param integer $additionalData
		 *
		 * @return ResponseOption
		 */
		public function setAdditionalData ($additionalData) {
			$this->additionalData = $additionalData;
			return $this;
		}
		
		/**
		 * @param string $feedBack
		 *
		 * @return ResponseOption
		 */
		public function setFeedBack ($feedBack) {
			$this->feedBack = $feedBack;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return ResponseOption
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $image
		 *
		 * @return ResponseOption
		 */
		public function setImage ($image) {
			$this->image = $image;
			return $this;
		}
		
		/**
		 * @param string $imageType
		 *
		 * @return ResponseOption
		 */
		public function setImageType ($imageType) {
			$this->imageType = $imageType;
			return $this;
		}
		
		/**
		 * @param string $mainLabel
		 *
		 * @return ResponseOption
		 */
		public function setMainLabel ($mainLabel) {
			$this->mainLabel = $mainLabel;
			return $this;
		}
		
		/**
		 * @param string $name
		 *
		 * @return ResponseOption
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}
		
		/**
		 * @param integer $questionId
		 *
		 * @return ResponseOption
		 */
		public function setQuestionId ($questionId) {
			$this->questionId = $questionId;
			return $this;
		}
		
		/**
		 * @param string $secondLabel
		 *
		 * @return ResponseOption
		 */
		public function setSecondLabel ($secondLabel) {
			$this->secondLabel = $secondLabel;
			return $this;
		}
		
		/**
		 * @param integer $selected
		 *
		 * @return ResponseOption
		 */
		public function setSelected ($selected) {
			$this->selected = $selected;
			return $this;
		}
		
		/**
		 * @param integer $sequence
		 *
		 * @return ResponseOption
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return ResponseOption
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @param array $summaryRow
		 *
		 * @return ResponseOption
		 */
		public function setSummaryRow ($summaryRow) {
			$this->summaryRow = $summaryRow;
			return $this;
		}
		
		/**
		 * @param SurveyNav $surveyNav
		 *
		 * @return ResponseOption
		 */
		public function setSurveyNav ($surveyNav) {
			$this->surveyNav = $surveyNav;
			return $this;
		}
		
		/**
		 * @param float $suveyPorcent
		 *
		 * @return ResponseOption
		 */
		public function setSuveyPorcent ($suveyPorcent) {
			$this->suveyPorcent = $suveyPorcent;
			return $this;
		}
		
		/**
		 * @param integer $surveyTotal
		 *
		 * @return ResponseOption
		 */
		public function setSurveyTotal ($surveyTotal) {
			$this->surveyTotal = $surveyTotal;
			return $this;
		}
		
		/**
		 * @param integer $surveySecondTotal
		 *
		 * @return ResponseOption
		 */
		public function setSurveySecondTotal ($surveySecondTotal) {
			$this->surveySecondTotal = $surveySecondTotal;
			return $this;
		}
		
		/**
		 * @param string $value
		 *
		 * @return ResponseOption
		 */
		public function setValue ($value) {
			$this->value = $value;
			return $this;
		}
		
		/**
		 * @return ResponseOption
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

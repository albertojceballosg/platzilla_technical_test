<?php
	require_once ('modules/questionnaire/Objects/QuestionInterface.class.php');
	require_once ('modules/questionnaire/Objects/QuestionException.class.php');
	
	class RangeGroup implements QuestionInterface {
		
		/** @var string */
		private $feedBack;
		
		/** @var integer */
		private $id;
		
		/** @var integer */
		private $maximum;
		
		/** @var integer */
		private $minimum;
		
		/** @var integer */
		private $questionId;
		
		/** @var string */
		private $themeName;
		
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
		 * @return integer
		 */
		public function getMaximum () {
			return $this->maximum;
		}
		
		/**
		 * @return integer
		 */
		public function getMinimum () {
			return $this->minimum;
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
		public function getThemeName () {
			return $this->themeName;
		}
		
		/**
		 * @param string $feedBack
		 *
		 * @return RangeGroup
		 */
		public function setFeedBack ($feedBack) {
			$this->feedBack = $feedBack;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return RangeGroup
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param integer $maximum
		 *
		 * @return RangeGroup
		 */
		public function setMaximum ($maximum) {
			$this->maximum = $maximum;
			return $this;
		}
		
		/**
		 * @param integer $minimum
		 *
		 * @return RangeGroup
		 */
		public function setMinimum ($minimum) {
			$this->minimum = $minimum;
			return $this;
		}
		
		/**
		 * @param integer $questionId
		 *
		 * @return RangeGroup
		 */
		public function setQuestionId ($questionId) {
			$this->questionId = $questionId;
			return $this;
		}
		
		/**
		 * @param string $themeName
		 *
		 * @return RangeGroup
		 */
		public function setThemeName ($themeName) {
			$this->themeName = $themeName;
			return $this;
		}
		
		/**
		 * @return RangeGroup
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

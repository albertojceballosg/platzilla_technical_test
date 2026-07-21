<?php
	require_once ('modules/questionnaire/Objects/QuestionInterface.class.php');
	class QuestionType implements QuestionInterface {
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $label;
		
		/** @var string */
		private $name;
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}
		
		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return QuestionType
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param $label
		 *
		 * @return QuestionType
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}
		
		/**
		 * @param string $name
		 *
		 * @return QuestionType
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}
		
		/**
		 * @return QuestionType
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

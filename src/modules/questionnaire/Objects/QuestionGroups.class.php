<?php
	require_once ('modules/questionnaire/Objects/QuestionInterface.class.php');
	require_once ('modules/questionnaire/Objects/QuestionException.class.php');
	
	class QuestionGroups implements QuestionInterface {
		
		/** @var string */
		private $description;
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $name;
		
		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
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
		public function getName () {
			return $this->name;
		}
		
		/**
		 * @param $description
		 *
		 * @return QuestionGroups
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return QuestionGroups
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param string $name
		 *
		 * @return QuestionGroups
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}
		
		/**
		 * @throws QuestionException
		 */
		public function validate () {
			if (empty ($this->getName ())) {
				throw new QuestionException(QuestionException::ERROR_QUESTION_GROUP_EMPTY);
			}
		}
		
		/**
		 * @return QuestionGroups
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

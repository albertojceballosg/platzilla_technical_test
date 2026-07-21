<?php
	require_once ('modules/diagnostic_report_builder/Objects/DiagnosticReportBuilderException.php');
	require_once ('modules/diagnostic_report_builder/Objects/DiagnosticReportBuilderInterface.php');
	
	class DiagnosticReportToAnswer implements DiagnosticReportBuilderInterface {
		
		/** @var string */
		private $answerName;
		
		/** @var string */
		private $attributes;
		
		/** @var integer */
		private $diagnosticReportId;
		
		/** @var string */
		private $elementType;
		
		/** @var string */
		private $handler;
		
		/** @var string */
		private $htmlBlock;
		
		/** @var integer */
		private $id;
		
		/** @var integer */
		private $idQuestionBlock;
		
		/** @var string */
		private $joinType;
		
		/** @var integer */
		private $questionId;
		
		/** @var integer */
		private $questionJoin;
		
		/** @var string */
		private $reportBlock;
		
		/** @var string */
		private $result;
		
		/**
		 * @return string
		 */
		public function getAnswerName () {
			return $this->answerName;
		}
		
		/**
		 * @return string
		 */
		public function getAttributes () {
			return $this->attributes;
		}
		
		/**
		 * @return integer
		 */
		public function getDiagnosticReportId () {
			return $this->diagnosticReportId;
		}
		
		/**
		 * @return string
		 */
		public function getElementType () {
			return $this->elementType;
		}
		
		/**
		 * @return string
		 */
		public function getHandler () {
			return $this->handler;
		}
		
		/**
		 * @return string
		 */
		public function getHtmlBlock () {
			return $this->htmlBlock;
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
		public function getIdQuestionBlock () {
			return $this->idQuestionBlock;
		}
		
		/**
		 * @return string
		 */
		public function getJoinType () {
			return $this->joinType;
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
		public function getQuestionJoin () {
			return $this->questionJoin;
		}
		
		/**
		 * @return string
		 */
		public function getReportBlock () {
			return $this->reportBlock;
		}
		
		/**
		 * @return string
		 */
		public function getResult () {
			return $this->result;
		}
		
		/**
		 * @param string $answerName
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setAnswerName ($answerName) {
			$this->answerName = $answerName;
			return $this;
		}
		
		/**
		 * @param string $attributes
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setAttributes ($attributes) {
			$this->attributes = $attributes;
			return $this;
		}
		
		/**
		 * @param integer $diagnosticReportId
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setDiagnosticReportId ($diagnosticReportId) {
			$this->diagnosticReportId = $diagnosticReportId;
			return $this;
		}
		
		/**
		 * @param string $elementType
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setElementType ($elementType) {
			$this->elementType = $elementType;
			return $this;
		}
		
		/**
		 * @param string $handler
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setHandler ($handler) {
			$this->handler = $handler;
			return $this;
		}
		
		/**
		 * @param string $htmlBlock
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setHtmlBlock ($htmlBlock) {
			$this->htmlBlock = $htmlBlock;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param integer $idQuestionBlock
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setIdQuestionBlock ($idQuestionBlock) {
			$this->idQuestionBlock = $idQuestionBlock;
			return $this;
		}
		
		/**
		 * @param string $joinType
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setJoinType ($joinType) {
			$this->joinType = $joinType;
			return $this;
		}
		
		/**
		 * @param integer $questionId
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setQuestionId ($questionId) {
			$this->questionId = $questionId;
			return $this;
		}
		
		/**
		 * @param integer $questionJoin
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setQuestionJoin ($questionJoin) {
			$this->questionJoin = $questionJoin;
			return $this;
		}
		
		/**
		 * @param string $reportBlock
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setReportBlock ($reportBlock) {
			$this->reportBlock = $reportBlock;
			return $this;
		}
		
		/**
		 * @param string $result
		 *
		 * @return DiagnosticReportToAnswer
		 */
		public function setResult ($result) {
			$this->result = $result;
			return $this;
		}
		
		/**
		 * @return DiagnosticReportToAnswer
		 */
		public static function getInstance () {
			return new self();
		}
		
	}

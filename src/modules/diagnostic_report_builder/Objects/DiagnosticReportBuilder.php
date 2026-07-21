<?php
	require_once ('modules/diagnostic_report_builder/Objects/DiagnosticReportBuilderException.php');
	require_once ('modules/diagnostic_report_builder/Objects/DiagnosticReportBuilderInterface.php');
	require_once ('modules/diagnostic_report_builder/Objects/DiagnosticReportToAnswer.php');
	
	class DiagnosticReportBuilder implements DiagnosticReportBuilderInterface {
		
		/** @var integer */
		private $id;
		
		/** @var string */
		private $name;
		
		/** @var integer */
		private $questionnaireId;
		
		/** @var string */
		private $questionnaireName;
		
		/** @var diagnosticReportToAnswer[] */
		private $reportsToAnswer;
		
		/** @var string */
		private $status;
		
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
		 * @return integer
		 */
		public function getQuestionnaireId () {
			return $this->questionnaireId;
		}
		
		/**
		 * @return string
		 */
		public function getQuestionnaireName () {
			return $this->questionnaireName;
		}
		
		/**
		 * @return diagnosticReportToAnswer[]
		 */
		public function getReportsToAnswer () {
			return $this->reportsToAnswer;
		}
		
		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}
		
		/**
		 * @param string $name
		 *
		 * @return DiagnosticReportBuilder
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return DiagnosticReportBuilder
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param integer $questionnaireId
		 *
		 * @return DiagnosticReportBuilder
		 */
		public function setQuestionnaireId ($questionnaireId) {
			$this->questionnaireId = $questionnaireId;
			return $this;
		}
		
		/**
		 * @param string $questionnaireName
		 *
		 * @return DiagnosticReportBuilder
		 */
		public function setQuestionnaireName ($questionnaireName) {
			$this->questionnaireName = $questionnaireName;
			return $this;
		}
		
		/**
		 * @param DiagnosticReportToAnswer[] $reportsToAnswer
		 *
		 * @return DiagnosticReportBuilder
		 */
		public function setReportsToAnswer ($reportsToAnswer) {
			if (empty ($reportsToAnswer)) {
				$this->reportsToAnswer = null;
			} else {
				$this->reportsToAnswer = $reportsToAnswer;
			}
			return $this;
		}
		
		/**
		 * @param string $status
		 *
		 * @return DiagnosticReportBuilder
		 */
		public function setStatus ($status) {
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @throws Exception
		 */
		public function validate () {
			if (empty($this->questionnaireId)) {
				throw new Exception(DiagnosticReportBuilderException::DIAGNOSTIC_REPORT_HAS_NOT_QUESTIONNAIRE);
			} else if (empty($this->name)) {
				throw new Exception(DiagnosticReportBuilderException::DIAGNOSTIC_REPORT_EMPTY_NAME);
			}
		}
		
		/**
		 * @return DiagnosticReportBuilder
		 */
		public static function getInstance () {
			return new self();
		}
		
	}

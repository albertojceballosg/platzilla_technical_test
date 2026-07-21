<?php
	require_once ('include/platzilla/Exceptions/SummaryReportException.php');
	require_once ('include/platzilla/Objects/SummaryReportInterface.php');
	class RailesAgreements implements SummaryReportInterface {
		
		/** @var string */
		private	$agreement;
		
		/** @var integer */
		private	$agreementId;
		
		/** @var string */
		private	$agreementName;
		
		/** @var string */
		private $agreementStatus;
		
		/** @var string */
		private	$description;
		
		/** @var integer */
		private	$execution;
		
		/** @var string */
		private	$relatedAgreement;
		
		/** @var integer */
		private	$reportId;
		
		/** @var integer */
		private	$sequence;
		
		/** @var string */
		private	$tabName;
		
		/** @var array */
		private	$usersInvolved;
		
		/**
		 * @return string
		 */
		public function getAgreement () {
			return $this->agreement;
		}
		
		/**
		 * @return integer
		 */
		public function getAgreementId () {
			return $this->agreementId;
		}
		
		/**
		 * @return string
		 */
		public function getAgreementName () {
			return $this->agreementName;
			
		}
		
		/**
		 * @return string
		 */
		public function getAgreementStatus () {
			return $this->agreementStatus;
		}
		
		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}
		
		/**
		 * @return integer
		 */
		public function getExecution () {
			return $this->execution;
		}
		
		/**
		 * @return string
		 */
		public function getRelatedAgreement () {
			return $this->relatedAgreement;
		}
		
		/**
		 * @return integer
		 */
		public function getReportId () {
			return $this->reportId;
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
		public function getTabName () {
			return $this->tabName;
		}
		
		/**
		 * @return array
		 */
		public function getUsersInvolved () {
			return $this->usersInvolved;
		}
		
		/**
		 * @param integer $agreement
		 *
		 * @return RailesAgreements
		 */
		public function setAgreement ($agreement) {
			$this->agreement = $agreement;
			return $this;
		}
		
		/**
		 * @param integer $agreementId
		 *
		 * @return RailesAgreements
		 */
		public function setAgreementId ($agreementId) {
			$this->agreementId = $agreementId;
			return $this;
		}
		
		/**
		 * @param string $name
		 *
		 * @return RailesAgreements
		 */
		public function setAgreementName ($name) {
			$this->agreementName = $name;
			return $this;
		}
		
		/**
		 * @param $agreementStatus
		 *
		 * @return RailesAgreements
		 * @throws SummaryReportException
		 */
		public function setAgreementStatus ($agreementStatus) {
			if (!in_array($agreementStatus, array_keys (self::AGREEMENTS_STATUS))) {
				throw new SummaryReportException(SummaryReportException::ERROR_INVALID_AGREEMENTS_STATUS);
			}
			$this->agreementStatus = $agreementStatus;
			return $this;
		}
		
		/**
		 * @param string $description
		 *
		 * @return RailesAgreements
		 */
		public function setDescription ($description) {
			$this->description = $description;
			return $this;
		}
		
		/**
		 * @param integer $execution
		 *
		 * @return RailesAgreements
		 */
		public function setExecution ($execution) {
			$this->execution = $execution;
			return $this;
		}
		
		/**
		 * @param string $relatedAgreement
		 *
		 * @return RailesAgreements
		 */
		public function setRelatedAgreement ($relatedAgreement) {
			$this->relatedAgreement = $relatedAgreement;
			return $this;
		}
		
		/**
		 * @param integer $reportId
		 *
		 * @return RailesAgreements
		 */
		public function setReportId ($reportId) {
			$this->reportId = $reportId;
			return $this;
		}
		
		/**
		 * @param integer $sequence
		 *
		 * @return RailesAgreements
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}
		
		/**
		 * @param string $tabName
		 *
		 * @return RailesAgreements
		 */
		public function setTabName ($tabName) {
			$this->tabName = $tabName;
			return $this;
		}
		
		
		/**
		 * @param array $usersInvolved
		 *
		 * @return RailesAgreements
		 */
		public function setUsersInvolved ($usersInvolved) {
			if (!empty ($usersInvolved)) {
				$this->usersInvolved = $usersInvolved;
			} else {
				$this->usersInvolved = array();
			}
			return $this;
		}
		
		/**
		 * @return void
		 * @throws SummaryReportException
		 */
		public function validate () {
			if (empty ($this->agreement)) {
				throw new SummaryReportException(SummaryReportException::ERROR_AGREEMENT_EMPTY);
			}
			if (empty ($this->agreementStatus)) {
				throw new SummaryReportException(SummaryReportException::ERROR_INVALID_AGREEMENTS_STATUS);
			}
			if (empty ($this->reportId)) {
				throw new SummaryReportException(SummaryReportException::ERROR_INVALID_REPORT_ID);
			}
			if (empty ($this->tabName)) {
				throw new SummaryReportException(SummaryReportException::ERROR_TAB_NAME_EMPTY);
			}
			if (empty ($this->usersInvolved)) {
				throw new SummaryReportException(SummaryReportException::ERROR_USERS_INVOLVED_EMPTY);
			}
		}
		
		/**
		 * @return RailesAgreements
		 */
		public static function getInstance () {
			return new self ();
		}
	}

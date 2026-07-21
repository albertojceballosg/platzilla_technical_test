<?php
	require_once ('include/platzilla/Exceptions/DataSharingRequestException.php');
	require_once ('include/platzilla/Objects/DataSharingRequestInterface.php');

	class DataSharingRequest implements DataSharingRequestInterface {
		/** @var integer */
		private $id;

		/** @var string */
		private $applicationCode;

		/** @var string */
		private $comments;

		/** @var User */
		private $createdBy;

		/** @var DateTime */
		private $creationDate;

		/** @var string */
		private $moduleName;

		/** @var DateTime */
		private $processingDate;

		/** @var string */
		private $recipientAddress;

		/** @var integer[] */
		private $recordIds;

		/** @var string */
		private $ruleId;

		/** @var string */
		private $sourceInstanceCode;

		/** @var string */
		private $status;

		/** @var string */
		private $targetInstanceCode;

		/**
		 * @throws DataSharingRequestException
		 */
		private function validateRecordIds () {
			if (empty ($this->recordIds)) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_RECORD_IDS);
			}
			foreach ($this->recordIds as $recordId) {
				if ((empty ($recordId)) || (!is_numeric ($recordId)) || ($recordId <= 0)) {
					throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_INVALID_RECORD_ID);
				}
			}
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
		public function getApplicationCode () {
			return $this->applicationCode;
		}

		/**
		 * @return string
		 */
		public function getComments () {
			return $this->comments;
		}

		/**
		 * @return User
		 */
		public function getCreatedBy () {
			return $this->createdBy;
		}

		/**
		 * @return DateTime
		 */
		public function getCreationDate () {
			return $this->creationDate;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return DateTime
		 */
		public function getProcessingDate () {
			return $this->processingDate;
		}

		/**
		 * @return string
		 */
		public function getRecipientAddress () {
			return $this->recipientAddress;
		}

		/**
		 * @return integer[]
		 */
		public function getRecordIds () {
			return $this->recordIds;
		}

		/**
		 * @return string
		 */
		public function getRuleId () {
			return $this->ruleId;
		}

		/**
		 * @return string
		 */
		public function getSourceInstanceCode () {
			return $this->sourceInstanceCode;
		}

		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @return string
		 */
		public function getTargetInstanceCode () {
			return $this->targetInstanceCode;
		}

		/**
		 * @param integer $id
		 *
		 * @return DataSharingRequest
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = intval ($id);
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param string $applicationCode
		 *
		 * @return DataSharingRequest
		 */
		public function setApplicationCode ($applicationCode) {
			if (is_scalar ($applicationCode)) {
				$this->applicationCode = $applicationCode;
			} else {
				$this->applicationCode = null;
			}
			return $this;
		}

		/**
		 * @param string $comments
		 *
		 * @return DataSharingRequest
		 */
		public function setComments ($comments) {
			if (is_scalar ($comments)) {
				$this->comments = $comments;
			} else {
				$this->comments = null;
			}
			return $this;
		}

		/**
		 * @param User $createdBy
		 *
		 * @return DataSharingRequest
		 */
		public function setCreatedBy ($createdBy) {
			if ($createdBy instanceof User) {
				$this->createdBy = $createdBy;
			} else {
				$this->createdBy = null;
			}
			return $this;
		}

		/**
		 * @param DateTime $creationDate
		 *
		 * @return DataSharingRequest
		 */
		public function setCreationDate ($creationDate) {
			if ($creationDate instanceof DateTime) {
				$this->creationDate = $creationDate;
			} else {
				$this->creationDate = null;
			}
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return DataSharingRequest
		 */
		public function setModuleName ($moduleName) {
			if (is_scalar ($moduleName)) {
				$this->moduleName = $moduleName;
			} else {
				$this->moduleName = null;
			}
			return $this;
		}

		/**
		 * @param DateTime $processingDate
		 *
		 * @return DataSharingRequest
		 */
		public function setProcessingDate ($processingDate) {
			if ($processingDate instanceof DateTime) {
				$this->processingDate = $processingDate;
			} else {
				$this->processingDate = null;
			}
			return $this;
		}

		/**
		 * @param string $recipientAddress
		 *
		 * @return DataSharingRequest
		 */
		public function setRecipientAddress ($recipientAddress) {
			if ((is_scalar ($recipientAddress)) && (filter_var ($recipientAddress, FILTER_VALIDATE_EMAIL))) {
				$this->recipientAddress = $recipientAddress;
			} else {
				$this->recipientAddress = null;
			}
			return $this;
		}

		/**
		 * @param integer[] $recordIds
		 *
		 * @return DataSharingRequest
		 */
		public function setRecordIds ($recordIds) {
			if ((is_array ($recordIds)) && (!empty ($recordIds))) {
				$this->recordIds = $recordIds;
			} else {
				$this->recordIds = null;
			}
			return $this;
		}

		/**
		 * @param string $ruleId
		 *
		 * @return DataSharingRequest
		 */
		public function setRuleId ($ruleId) {
			if (is_scalar ($ruleId)) {
				$this->ruleId = $ruleId;
			} else {
				$this->ruleId = null;
			}
			return $this;
		}

		/**
		 * @param string $sourceInstanceCode
		 *
		 * @return DataSharingRequest
		 */
		public function setSourceInstanceCode ($sourceInstanceCode) {
			if (is_scalar ($sourceInstanceCode)) {
				$this->sourceInstanceCode = $sourceInstanceCode;
			} else {
				$this->sourceInstanceCode = null;
			}
			return $this;
		}

		/**
		 * @param string $status
		 *
		 * @return DataSharingRequest
		 */
		public function setStatus ($status) {
			if (in_array ($status, self::getAvailableStatuses ())) {
				$this->status = $status;
			} else {
				$this->status = null;
			}
			return $this;
		}

		/**
		 * @param string $targetInstanceCode
		 *
		 * @return DataSharingRequest
		 */
		public function setTargetInstanceCode ($targetInstanceCode) {
			if (is_scalar ($targetInstanceCode)) {
				$this->targetInstanceCode = $targetInstanceCode;
			} else {
				$this->targetInstanceCode = null;
			}
			return $this;
		}

		/**
		 * @throws DataSharingRequestException
		 */
		public function validate () {
			if (empty ($this->ruleId)) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_RULE_ID);
			} else if ((!is_numeric ($this->ruleId)) && (!in_array ($this->ruleId, self::getDefaultRuleIds ()))) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_INVALID_RULE_ID);
			} else if (empty ($this->createdBy)) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_CREATED_BY);
			} else if (empty ($this->creationDate)) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_CREATION_DATE);
			} else if (empty ($this->moduleName)) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_MODULE_NAME);
			} else if (empty ($this->recipientAddress)) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_RECIPIENT_ADDRESS);
			} else if ($this->sourceInstanceCode === null) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_SOURCE_INSTANCE_CODE);
			} else if (empty ($this->status)) {
				throw new DataSharingRequestException (DataSharingRequestException::ERROR_DATA_SHARING_REQUEST_EMPTY_STATUS);
			}
			$this->validateRecordIds ();
		}

		/**
		 * @return string[]
		 */
		public static function getDefaultRuleIds () {
			return array (self::RULE_FULL, self::RULE_MINIMAL);
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableStatuses () {
			return array (self::STATUS_ACCEPTED, self::STATUS_REJECTED, self::STATUS_SENT);
		}

		/**
		 * @return DataSharingRequest
		 */
		public static function getInstance () {
			return new self ();
		}

	}

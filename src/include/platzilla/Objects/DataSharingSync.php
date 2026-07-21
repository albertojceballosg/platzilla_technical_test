<?php
	require_once ('include/platzilla/Exceptions/DataSharingSyncException.php');

	class DataSharingSync {
		/** @var integer */
		private $id;

		/** @var string */
		private $ruleId;

		/** @var string */
		private $sourceEmailAddress;

		/** @var string */
		private $sourceInstanceCode;

		/** @var string */
		private $sourceModuleName;

		/** @var integer */
		private $sourceRecordId;

		/** @var string */
		private $targetEmailAddress;

		/** @var string */
		private $targetInstanceCode;

		/** @var string */
		private $targetModuleName;

		/** @var integer */
		private $targetRecordId;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
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
		public function getSourceEmailAddress () {
			return $this->sourceEmailAddress;
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
		public function getSourceModuleName () {
			return $this->sourceModuleName;
		}

		/**
		 * @return integer
		 */
		public function getSourceRecordId () {
			return $this->sourceRecordId;
		}

		/**
		 * @return string
		 */
		public function getTargetEmailAddress () {
			return $this->targetEmailAddress;
		}

		/**
		 * @return string
		 */
		public function getTargetInstanceCode () {
			return $this->targetInstanceCode;
		}

		/**
		 * @return string
		 */
		public function getTargetModuleName () {
			return $this->targetModuleName;
		}

		/**
		 * @return integer
		 */
		public function getTargetRecordId () {
			return $this->targetRecordId;
		}

		/**
		 * @param integer $id
		 *
		 * @return DataSharingSync
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id >= 0) && (intval ($id) == $id)) {
				$this->id = intval ($id);
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param string $ruleId
		 *
		 * @return DataSharingSync
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
		 * @param string $sourceEmailAddress
		 *
		 * @return DataSharingSync
		 */
		public function setSourceEmailAddress ($sourceEmailAddress) {
			if (filter_var ($sourceEmailAddress, FILTER_VALIDATE_EMAIL)) {
				$this->sourceEmailAddress = $sourceEmailAddress;
			} else {
				$this->sourceEmailAddress = null;
			}
			return $this;
		}

		/**
		 * @param string $sourceInstanceCode
		 *
		 * @return DataSharingSync
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
		 * @param string $sourceModuleName
		 *
		 * @return DataSharingSync
		 */
		public function setSourceModuleName ($sourceModuleName) {
			if (is_scalar ($sourceModuleName)) {
				$this->sourceModuleName = $sourceModuleName;
			} else {
				$this->sourceModuleName = null;
			}
			return $this;
		}

		/**
		 * @param integer $sourceRecordId
		 *
		 * @return DataSharingSync
		 */
		public function setSourceRecordId ($sourceRecordId) {
			if ((is_numeric ($sourceRecordId)) && ($sourceRecordId >= 0) && (intval ($sourceRecordId) == $sourceRecordId)) {
				$this->sourceRecordId = intval ($sourceRecordId);
			} else {
				$this->sourceRecordId = null;
			}
			return $this;
		}

		/**
		 * @param string $targetEmailAddress
		 *
		 * @return DataSharingSync
		 */
		public function setTargetEmailAddress ($targetEmailAddress) {
			if (filter_var ($targetEmailAddress, FILTER_VALIDATE_EMAIL)) {
				$this->targetEmailAddress = $targetEmailAddress;
			} else {
				$this->targetEmailAddress = null;
			}
			return $this;
		}

		/**
		 * @param string $targetInstanceCode
		 *
		 * @return DataSharingSync
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
		 * @param string $targetModuleName
		 *
		 * @return DataSharingSync
		 */
		public function setTargetModuleName ($targetModuleName) {
			if (is_scalar ($targetModuleName)) {
				$this->targetModuleName = $targetModuleName;
			} else {
				$this->targetModuleName = null;
			}
			return $this;
		}

		/**
		 * @param integer $targetRecordId
		 *
		 * @return DataSharingSync
		 */
		public function setTargetRecordId ($targetRecordId) {
			if ((is_numeric ($targetRecordId)) && ($targetRecordId >= 0) && (intval ($targetRecordId) == $targetRecordId)) {
				$this->targetRecordId = intval ($targetRecordId);
			} else {
				$this->targetRecordId = null;
			}
			return $this;
		}

		/**
		 * @throws DataSharingSyncException
		 */
		public function validate () {
			if (empty ($this->ruleId)) {
				throw new DataSharingSyncException (DataSharingSyncException::ERROR_DATA_SHARING_SYNC_EMPTY_RULE_ID);
			} else if (empty ($this->sourceEmailAddress)) {
				throw new DataSharingSyncException (DataSharingSyncException::ERROR_DATA_SHARING_SYNC_EMPTY_SOURCE_EMAIL_ADDRESS);
			} else if ($this->sourceInstanceCode === null) {
				throw new DataSharingSyncException (DataSharingSyncException::ERROR_DATA_SHARING_SYNC_EMPTY_SOURCE_INSTANCE_CODE);
			} else if (empty ($this->sourceModuleName)) {
				throw new DataSharingSyncException (DataSharingSyncException::ERROR_DATA_SHARING_SYNC_EMPTY_SOURCE_MODULE_NAME);
			} else if (empty ($this->sourceRecordId)) {
				throw new DataSharingSyncException (DataSharingSyncException::ERROR_DATA_SHARING_SYNC_EMPTY_SOURCE_RECORD_ID);
			} else if (empty ($this->targetEmailAddress)) {
				throw new DataSharingSyncException (DataSharingSyncException::ERROR_DATA_SHARING_SYNC_EMPTY_TARGET_EMAIL_ADDRESS);
			} else if ($this->targetInstanceCode === null) {
				throw new DataSharingSyncException (DataSharingSyncException::ERROR_DATA_SHARING_SYNC_EMPTY_TARGET_INSTANCE_CODE);
			} else if (empty ($this->targetModuleName)) {
				throw new DataSharingSyncException (DataSharingSyncException::ERROR_DATA_SHARING_SYNC_EMPTY_TARGET_MODULE_NAME);
			} else if (empty ($this->targetRecordId)) {
				throw new DataSharingSyncException (DataSharingSyncException::ERROR_DATA_SHARING_SYNC_EMPTY_TARGET_RECORD_ID);
			}
		}

		/**
		 * @return DataSharingSync
		 */
		public static function getInstance () {
			return new self ();
		}

	}

<?php
	require_once ('include/platzilla/Exceptions/DataSharingRuleException.php');
	require_once ('include/platzilla/Objects/DataSharingRuleDetail.php');
	require_once ('include/platzilla/Objects/DataSharingRuleInterface.php');

	class DataSharingRule implements DataSharingRuleInterface, Serializable {
		/** @var integer */
		private $id;

		/** @var boolean */
		private $deleted;

		/** @var DataSharingRuleDetail[] */
		private $details;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		/** @var string */
		private $status;

		/**
		 * DataSharingRule constructor.
		 */
		public function __construct () {
			$this->deleted         = false;
			$this->locked          = false;
		}

		/**
		 * @throws DataSharingRuleDetailException
		 * @throws DataSharingRuleException
		 */
		private function validateDetails () {
			if (empty ($this->details)) {
				throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_EMPTY_DETAILS);
			}
			foreach ($this->details as $detail) {
				if (empty ($detail)) {
					throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_EMPTY_DETAIL);
				} else if (!($detail instanceof DataSharingRuleDetail)) {
					throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_INVALID_DETAIL);
				} else {
					$detail->validate ();
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
		 * @return DataSharingRuleDetail[]
		 */
		public function getDetails () {
			return $this->details;
		}

		/**
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * @param integer $id
		 *
		 * @return DataSharingRule
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
		 * @param boolean $deleted
		 *
		 * @return DataSharingRule
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * @param DataSharingRuleDetail[] $details
		 *
		 * @return DataSharingRule
		 */
		public function setDetails ($details) {
			if ((is_array ($details)) && (!empty ($details))) {
				$this->details = $details;
			} else {
				$this->details = null;
			}
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return DataSharingRule
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return DataSharingRule
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
		 * @param string $name
		 *
		 * @return DataSharingRule
		 */
		public function setName ($name) {
			if (is_scalar ($name)) {
				$this->name = $name;
			} else {
				$this->name = null;
			}
			return $this;
		}

		/**
		 * @param string $status
		 *
		 * @return DataSharingRule
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
		 * @return string
		 */
		public function serialize () {
			$details = $this->details;
			if (!empty ($details)) {
				$serializedDetails = array ();
				foreach ($details as $detail) {
					$serializedDetails [] = $detail->serialize ();
				}
			} else {
				$serializedDetails = null;
			}

			return serialize (
				array (
					$this->id,
					$this->deleted,
					$this->locked,
					$this->moduleName,
					$this->name,
					$this->status,
					$serializedDetails,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->deleted,
				$this->locked,
				$this->moduleName,
				$this->name,
				$this->status,
				$serializedDetails,
				) = unserialize ($serialized);

			if (!empty ($serializedDetails)) {
				$this->details = array ();
				foreach ($serializedDetails as $serializedDetail) {
					$detail = DataSharingRuleDetail::getInstance ();
					$detail->unserialize ($serializedDetail);
					$this->details [] = $detail;
				}
			}
		}

		/**
		 * @throws DataSharingRuleException
		 */
		public function validate () {
			if (empty ($this->moduleName)) {
				throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_EMPTY_MODULE_NAME);
			} else if (empty ($this->name)) {
				throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_EMPTY_NAME);
			} else if (empty ($this->status)) {
				throw new DataSharingRuleException (DataSharingRuleException::ERROR_DATA_SHARING_RULE_EMPTY_STATUS);
			}
			$this->validateDetails ();
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableStatuses () {
			return array (self::STATUS_ACTIVE, self::STATUS_INACTIVE);
		}

		/**
		 * @return DataSharingRule
		 */
		public static function getInstance () {
			return new self ();
		}

	}

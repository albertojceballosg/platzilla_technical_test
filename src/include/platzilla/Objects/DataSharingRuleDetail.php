<?php
	require_once ('include/platzilla/Exceptions/DataSharingRuleDetailException.php');
	require_once ('include/platzilla/Objects/DataSharingRuleDetailInterface.php');

	class DataSharingRuleDetail implements DataSharingRuleDetailInterface, Serializable {
		/** @var integer */
		private $id;

		/** @var string */
		private $actionType;

		/** @var string */
		private $parameterFormula;

		/** @var string */
		private $parameterType;

		/** @var integer */
		private $ruleId;

		/** @var string */
		private $sourceModuleName;

		/** @var string */
		private $targetFieldName;

		/** @var string */
		private $targetModuleName;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getActionType () {
			return $this->actionType;
		}

		/**
		 * @return string
		 */
		public function getParameterFormula () {
			return $this->parameterFormula;
		}

		/**
		 * @return string
		 */
		public function getParameterType () {
			return $this->parameterType;
		}

		/**
		 * @return integer
		 */
		public function getRuleId () {
			return $this->ruleId;
		}

		/**
		 * @return string
		 */
		public function getSourceModuleName () {
			return $this->sourceModuleName;
		}

		/**
		 * @return string
		 */
		public function getTargetFieldName () {
			return $this->targetFieldName;
		}

		/**
		 * @return string
		 */
		public function getTargetModuleName () {
			return $this->targetModuleName;
		}

		/**
		 * @param integer $id
		 *
		 * @return DataSharingRuleDetail
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
		 * @param string $actionType
		 *
		 * @return DataSharingRuleDetail
		 */
		public function setActionType ($actionType) {
			if (in_array ($actionType, self::getAvailableActionTypes ())) {
				$this->actionType = $actionType;
			} else {
				$this->actionType = null;
			}
			return $this;
		}

		/**
		 * @param string $parameterFormula
		 *
		 * @return DataSharingRuleDetail
		 */
		public function setParameterFormula ($parameterFormula) {
			if (is_scalar ($parameterFormula)) {
				$this->parameterFormula = $parameterFormula;
			} else {
				$this->parameterFormula = null;
			}
			return $this;
		}

		/**
		 * @param string $parameterType
		 *
		 * @return DataSharingRuleDetail
		 */
		public function setParameterType ($parameterType) {
			if (in_array ($parameterType, self::getAvailableParameterTypes ())) {
				$this->parameterType = $parameterType;
			} else {
				$this->parameterType = null;
			}
			return $this;
		}

		/**
		 * @param integer $ruleId
		 *
		 * @return DataSharingRuleDetail
		 */
		public function setRuleId ($ruleId) {
			if ((is_numeric ($ruleId)) && ($ruleId > 0) && (intval ($ruleId) == $ruleId)) {
				$this->ruleId = $ruleId;
			} else {
				$this->ruleId = null;
			}
			return $this;
		}

		/**
		 * @param string $sourceModuleName
		 *
		 * @return DataSharingRuleDetail
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
		 * @param string $targetFieldName
		 *
		 * @return DataSharingRuleDetail
		 */
		public function setTargetFieldName ($targetFieldName) {
			if (is_scalar ($targetFieldName)) {
				$this->targetFieldName = $targetFieldName;
			} else {
				$this->targetFieldName = null;
			}
			return $this;
		}

		/**
		 * @param string $targetModuleName
		 *
		 * @return DataSharingRuleDetail
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
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->id,
					$this->actionType,
					$this->parameterFormula,
					$this->parameterType,
					$this->ruleId,
					$this->sourceModuleName,
					$this->targetFieldName,
					$this->targetModuleName,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->actionType,
				$this->parameterFormula,
				$this->parameterType,
				$this->ruleId,
				$this->sourceModuleName,
				$this->targetFieldName,
				$this->targetModuleName,
				) = unserialize ($serialized);
		}

		/**
		 * @throws DataSharingRuleDetailException
		 */
		public function validate () {
			if (empty ($this->actionType)) {
				throw new DataSharingRuleDetailException (DataSharingRuleDetailException::ERROR_DATA_SHARING_RULE_DETAIL_EMPTY_ACTION_TYPE);
			} else if (empty ($this->sourceModuleName)) {
				throw new DataSharingRuleDetailException (DataSharingRuleDetailException::ERROR_DATA_SHARING_RULE_DETAIL_EMPTY_SOURCE_MODULE_NAME);
			} else if (empty ($this->targetModuleName)) {
				throw new DataSharingRuleDetailException (DataSharingRuleDetailException::ERROR_DATA_SHARING_RULE_DETAIL_EMPTY_TARGET_MODULE_NAME);
			} else if (empty ($this->targetFieldName)) {
				throw new DataSharingRuleDetailException (DataSharingRuleDetailException::ERROR_DATA_SHARING_RULE_DETAIL_EMPTY_TARGET_FIELD_NAME);
			}
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableActionTypes () {
			return array (self::ACTION_RECEIVE_ONLY, self::ACTION_SEND_AND_RECEIVE, self::ACTION_SEND_ONLY);
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableParameterTypes () {
			return array (self::PARAMETER_TYPE_LITERAL, self::PARAMETER_TYPE_SHARING_RULE, self::PARAMETER_TYPE_SOURCE_FIELD, self::PARAMETER_TYPE_SOURCE_GRID_FIELD, self::PARAMETER_TYPE_VARIABLE);
		}

		/**
		 * @return DataSharingRuleDetail
		 */
		public static function getInstance () {
			return new self ();
		}

	}

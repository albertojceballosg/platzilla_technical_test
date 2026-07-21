<?php
	require_once ('include/platzilla/Exceptions/CalendarViewException.php');
	require_once ('include/platzilla/Objects/CalendarViewRuleInterface.php');

	/**
	 * Class CalendarViewRule
	 */
	class CalendarViewRule implements CalendarViewRuleInterface {
		/** @var integer */
		private $id;

		/** @var string */
		private $backgroundColor;

		/** @var string */
		private $fieldName;

		/** @var string */
		private $joinRule;
		
		/** @var string */
		private $moduleName;

		/** @var string */
		private $operator;

		/** @var string */
		private $value;

		/** @var string */
		private $viewId;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getBackgroundColor () {
			return $this->backgroundColor;
		}

		/**
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}
		
		/**
		 * @return string
		 */
		public function getJoinRule () {
			return $this->joinRule;
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
		public function getOperator () {
			return $this->operator;
		}

		/**
		 * @return string
		 */
		public function getValue () {
			return $this->value;
		}

		/**
		 * @return integer
		 */
		public function getViewId () {
			return $this->viewId;
		}

		/**
		 * @param integer $id
		 *
		 * @return CalendarViewRule
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $backgroundColor
		 *
		 * @return CalendarViewRule
		 */
		public function setBackgroundColor ($backgroundColor) {
			$this->backgroundColor = $backgroundColor;
			return $this;
		}

		/**
		 * @param string $fieldName
		 *
		 * @return CalendarViewRule
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}
		
		/**
		 * @param string $joinRule
		 *
		 * @return CalendarViewRule
		 */
		public function setJoinRule ($joinRule) {
			$this->joinRule = $joinRule;
			return $this;
		}
		
		/**
		 * @param string $moduleName
		 *
		 * @return CalendarViewRule
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param string $operator
		 *
		 * @return CalendarViewRule
		 */
		public function setOperator ($operator) {
			if (in_array ($operator, array (self::OPERATOR_DISTINCT, self::OPERATOR_EQUALS, self::OPERATOR_GREATER, self::OPERATOR_GREATER_OR_EQUALS, self::OPERATOR_LESS, self::OPERATOR_LESS_OR_EQUALS))) {
				$this->operator = $operator;
			}
			return $this;
		}

		/**
		 * @param string $value
		 *
		 * @return CalendarViewRule
		 */
		public function setValue ($value) {
			$this->value = $value;
			return $this;
		}

		/**
		 * @param integer $viewId
		 *
		 * @return CalendarViewRule
		 */
		public function setViewId ($viewId) {
			$this->viewId = $viewId;
			return $this;
		}

		/**
		 * @param CalendarViewRule $rule
		 */
		public function copyValuesFrom ($rule) {
			if ((empty ($rule)) || (!($rule instanceof CalendarViewRule))) {
				return;
			}

			$this->backgroundColor = $rule->getBackgroundColor ();
			$this->fieldName       = $rule->getFieldName ();
			$this->joinRule        = $rule->getJoinRule ();
			$this->moduleName      = $rule->getModuleName ();
			$this->operator        = $rule->getOperator ();
			$this->value           = $rule->getValue ();
		}

		/**
		 * @param integer $newViewId
		 * @param integer $newRuleId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return CalendarViewRule
		 */
		public function duplicate ($newViewId, $newRuleId = null, $oldCodeFieldName = null, $newCodeFieldName = null) {
			if ((!empty ($newCodeFieldName)) && ($this->fieldName == $oldCodeFieldName)) {
				$fieldName = $newCodeFieldName;
			} else {
				$fieldName = $this->fieldName;
			}

			$object = new self ();
			return $object->setId ($newRuleId)
				->setBackgroundColor ($this->backgroundColor)
				->setFieldName ($fieldName)
				->setJoinRule ($this->joinRule)
				->setModuleName ($this->moduleName)
				->setOperator ($this->operator)
				->setValue ($this->value)
				->setViewId ($newViewId);
		}

		/**
		 * @param CalendarViewRule $rule
		 *
		 * @return boolean
		 */
		public function isEqualTo ($rule) {
			if (
				(empty ($rule)) ||
				(!($rule instanceof CalendarViewRule)) ||
				($this->backgroundColor != $rule->getBackgroundColor ()) ||
				($this->fieldName != $rule->getFieldName ()) ||
				($this->joinRule != $rule->getJoinRule ()) ||
				($this->moduleName != $rule->getModuleName ()) ||
				($this->operator != $rule->getOperator ()) ||
				($this->value != $rule->getValue ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws CalendarViewRuleException
		 */
		public function validate () {
			if (empty ($this->backgroundColor)) {
				throw new CalendarViewRuleException (CalendarViewRuleException::ERROR_CALENDAR_VIEW_RULE_EMPTY_BACKGROUND_COLOR);
			} else if (!preg_match ('/^#[0-9A-Fa-f]{6}$/', $this->backgroundColor)) {
				throw new CalendarViewRuleException (CalendarViewRuleException::ERROR_CALENDAR_VIEW_RULE_INVALID_BACKGROUND_COLOR);
			} else if (empty ($this->fieldName)) {
				throw new CalendarViewRuleException (CalendarViewRuleException::ERROR_CALENDAR_VIEW_RULE_EMPTY_FIELD_NAME);
			} else if (empty ($this->moduleName)) {
				throw new CalendarViewRuleException (CalendarViewRuleException::ERROR_CALENDAR_VIEW_RULE_EMPTY_MODULE_NAME);
			} else if (empty ($this->operator)) {
				throw new CalendarViewRuleException (CalendarViewRuleException::ERROR_CALENDAR_VIEW_RULE_EMPTY_OPERATOR);
			}
		}

		/**
		 * @return CalendarViewRule
		 */
		public static function getInstance () {
			return new self ();
		}

	}

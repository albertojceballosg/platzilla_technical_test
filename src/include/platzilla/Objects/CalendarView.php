<?php
	require_once ('include/platzilla/Exceptions/CalendarViewException.php');
	require_once ('include/platzilla/Objects/CalendarViewRule.php');

	/**
	 * Class CalendarView
	 */
	class CalendarView {
		/** @var integer */
		private $id;

		/** @var string[] */
		private $applicationCodes;

		/** @var string */
		private $backgroundColor;

		/** @var boolean */
		private $deleted;

		/** @var string */
		private $fromFieldName;

		/** @var string */
		private $fromModuleName;

		/** @var string */
		private $label;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var CalendarViewRule[][] */
		private $rules;

		/** @var string */
		private $subTitle;
		
		/** @var string */
		private $titleFieldName;

		/** @var string */
		private $titleModuleName;

		/** @var string */
		private $toFieldName;

		/** @var string */
		private $toModuleName;

		public function __construct () {
			$this->backgroundColor = '#FFFFFF';
			$this->deleted = false;
			$this->locked = false;
		}

		/**
		 * @param CalendarViewRule[] $rules
		 *
		 * @return boolean
		 */
		private function areRulesEqual ($rules) {
			if ((empty ($this->rules)) && (empty ($rules))) {
				return true;
			} else if (
				(empty ($this->rules) !== empty ($rules)) ||
				(!is_array ($rules)) ||
				(count ($this->rules) != count ($rules))
			) {
				return false;
			} else {
				foreach ($this->rules as $thisRule) {
					$equals = false;
					foreach ($rules as $rule) {
						if ($rule->isEqualTo ($thisRule)) {
							$equals = true;
							break;
						}
					}
					if (!$equals) {
						return false;
					}
				}
				return true;
			}
		}

		/**
		 * @param object|CalendarViewRule[] $elements
		 * @param string $oldModuleName
		 * @param string $newModuleName
		 */
		private function changeModuleName ($elements, $oldModuleName, $newModuleName) {
			if ((empty ($elements)) || ($oldModuleName == $newModuleName)) {
				return;
			}

			if (!is_array ($elements)) {
				$elements = array ($elements);
			}

			$n = count ($elements);
			for ($i = 0; $i < $n; $i++) {
				if (
					(is_object ($elements [ $i ])) &&
					(is_callable (array ($elements [ $i ], 'getModuleName'))) &&
					(is_callable (array ($elements [ $i ], 'setModuleName'))) &&
					($oldModuleName == $elements [ $i ]->getModuleName ())
				) {
					$elements [ $i ]->setModuleName ($newModuleName);
				}
			}
		}

		/**
		 * @param integer $viewId
		 * @param CalendarViewRule[] $sourceRules
		 */
		private function copyRules ($viewId, $sourceRules) {
			$rules = array ();
			foreach ($sourceRules as $sourceRule) {
				$found = false;
				foreach ($this->rules as $targetRule) {
					if ($targetRule->isEqualTo ($sourceRule)) {
						$rules [] = $targetRule;
						$found = true;
						break;
					}
				}
				if (!$found) {
					$rules [] = $sourceRule->duplicate ($viewId);
				}
			}
			$this->rules = $rules;
		}

		/**
		 * @param CalendarView $view
		 */
		private function copyRulesFrom ($view) {
			$sourceRules = $view->getRules ();
			if ((empty ($sourceRules)) && (empty ($this->rules))) {
				return;
			}

			if (empty ($sourceRules)) {
				$this->rules = null;
			} else if (empty ($this->rules)) {
				$rules = array ();
				foreach ($sourceRules as $sourceRule) {
					$rules [] = $sourceRule->duplicate ($view->getId ());
				}
				$this->rules = $rules;
			} else {
				$this->copyRules ($view->getId (), $sourceRules);
			}
		}

		/**
		 * @throws CalendarViewException
		 * @throws CalendarViewRuleException
		 */
		private function validateRules () {
			if (empty ($this->rules)) {
				return;
			}
			foreach ($this->rules as $thisRule) {
				foreach ($thisRule as $rule) {
					if (empty ($rule)) {
						throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_EMPTY_RULE);
					} else if (! ($rule instanceof CalendarViewRule)) {
						throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_INVALID_RULE);
					} else {
						$rule->validate ();
					}
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
		 * @return string[]
		 */
		public function getApplicationCodes () {
			return $this->applicationCodes;
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
		public function getFromFieldName () {
			return $this->fromFieldName;
		}

		/**
		 * @return string
		 */
		public function getFromModuleName () {
			return $this->fromModuleName;
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
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * @return CalendarViewRule[]
		 */
		public function getRules () {
			return $this->rules;
		}
		
		/**
		 * @return string
		 */
		public function getSubTitle () {
			return $this->subTitle;
		}
		/**
		 * @return string
		 */
		public function getTitleFieldName () {
			return $this->titleFieldName;
		}

		/**
		 * @return string
		 */
		public function getTitleModuleName () {
			return $this->titleModuleName;
		}

		/**
		 * @return string
		 */
		public function getToFieldName () {
			return $this->toFieldName;
		}

		/**
		 * @return string
		 */
		public function getToModuleName () {
			return $this->toModuleName;
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
		 * @return CalendarView
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string[] $applicationCodes
		 *
		 * @return CalendarView
		 */
		public function setApplicationCodes ($applicationCodes) {
			if (is_array ($applicationCodes)) {
				$this->applicationCodes = $applicationCodes;
			}
			return $this;
		}

		/**
		 * @param string $backgroundColor
		 *
		 * @return CalendarView
		 */
		public function setBackgroundColor ($backgroundColor) {
			$this->backgroundColor = $backgroundColor;
			return $this;
		}

		/**
		 * @param boolean $deleted
		 *
		 * @return CalendarView
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * @param string $fromFieldName
		 *
		 * @return CalendarView
		 */
		public function setFromFieldName ($fromFieldName) {
			$this->fromFieldName = $fromFieldName;
			return $this;
		}

		/**
		 * @param string $fromModuleName
		 *
		 * @return CalendarView
		 */
		public function setFromModuleName ($fromModuleName) {
			$this->fromModuleName = $fromModuleName;
			return $this;
		}

		/**
		 * @param string $label
		 *
		 * @return CalendarView
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return CalendarView
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
		 * @return CalendarView
		 */
		public function setModuleName ($moduleName) {
			$this->changeModuleName ($this->rules, $this->moduleName, $moduleName);
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * @param CalendarViewRule[] $rules
		 *
		 * @return CalendarView
		 */
		public function setRules ($rules) {
			$this->rules = $rules;
			return $this;
		}
		
		/**
		 * @param string $subTitle
		 */
		public function setSubTitle ($subTitle) {
			$this->subTitle = $subTitle;
			return $this;
		}
		/**
		 * @param string $titleFieldName
		 *
		 * @return CalendarView
		 */
		public function setTitleFieldName ($titleFieldName) {
			$this->titleFieldName = $titleFieldName;
			return $this;
		}

		/**
		 * @param string $titleModuleName
		 *
		 * @return CalendarView
		 */
		public function setTitleModuleName ($titleModuleName) {
			$this->titleModuleName = $titleModuleName;
			return $this;
		}

		/**
		 * @param string $toFieldName
		 *
		 * @return CalendarView
		 */
		public function setToFieldName ($toFieldName) {
			$this->toFieldName = $toFieldName;
			return $this;
		}

		/**
		 * @param string $toModuleName
		 *
		 * @return CalendarView
		 */
		public function setToModuleName ($toModuleName) {
			$this->toModuleName = $toModuleName;
			return $this;
		}

		/**
		 * @param CalendarView $view
		 */
		public function copyValuesFrom ($view) {
			if ((empty ($view)) || (!($view instanceof CalendarView))) {
				return;
			}

			$this->applicationCodes = $view->getApplicationCodes ();
			$this->backgroundColor = $view->getBackgroundColor ();
			$this->fromFieldName   = $view->getFromFieldName ();
			$this->fromModuleName  = $view->getFromModuleName ();
			$this->label           = $view->getLabel ();
			$this->moduleName      = $view->getModuleName ();
			$this->subTitle        = $view->getSubTitle ();
			$this->titleFieldName  = $view->getTitleFieldName ();
			$this->titleModuleName = $view->getTitleModuleName ();
			$this->toFieldName     = $view->getToFieldName ();
			$this->toModuleName    = $view->getToModuleName ();
			$this->copyRulesFrom ($view);
		}

		/**
		 * @param integer $newViewId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return CalendarView
		 */
		public function duplicate ($newViewId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			if (!empty ($this->rules)) {
				$duplicatedRules = array ();
				foreach ($this->rules as $rule) {
					$duplicatedRules [] = $rule->duplicate ($newViewId, !empty ($newViewId) ? $rule->getId () : null, $oldCodeFieldName, $newCodeFieldName);
				}
			} else {
				$duplicatedRules = null;
			}

			if ((!empty ($newCodeFieldName)) && ($this->titleFieldName == $oldCodeFieldName)) {
				$titleFieldName = $newCodeFieldName;
			} else {
				$titleFieldName = $this->titleFieldName;
			}

			$object = new self ();
			return $object->setId ($newViewId)
				->setApplicationCodes ($this->applicationCodes)
				->setBackgroundColor ($this->backgroundColor)
				->setFromFieldName ($this->fromFieldName)
				->setFromModuleName ($this->fromModuleName)
				->setLabel ($this->label)
				->setModuleName ($this->moduleName)
				->setRules ($duplicatedRules)
				->setSubTitle ($this->subTitle)
				->setTitleFieldName ($titleFieldName)
				->setTitleModuleName ($this->titleModuleName)
				->setToFieldName ($this->toFieldName)
				->setToModuleName ($this->toModuleName);
		}

		/**
		 * @param CalendarView $view
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($view, $deepCheck = true) {
			if (
				(empty ($view)) ||
				(!($view instanceof CalendarView)) ||
				($this->applicationCodes != $view->getApplicationCodes ()) ||
				($this->backgroundColor != $view->getBackgroundColor ()) ||
				($this->fromFieldName != $view->getFromFieldName ()) ||
				($this->fromModuleName != $view->getFromModuleName ()) ||
				($this->label != $view->getLabel ()) ||
				($this->moduleName != $view->getModuleName ()) ||
				($this->subTitle != $view->getSubTitle ()) ||
				($this->titleFieldName != $view->getTitleFieldName ()) ||
				($this->titleModuleName != $view->getTitleModuleName ()) ||
				($this->toFieldName != $view->getToFieldName ()) ||
				($this->toModuleName != $view->getToModuleName ()) ||
				(($deepCheck) && (!$this->areRulesEqual ($view->getRules ())))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws CalendarViewException
		 * @throws CalendarViewRuleException
		 */
		public function validate () {
			if ($this->deleted) {
				return;
			} else if (empty ($this->applicationCodes)) {
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_EMPTY_APPLICATION_CODES);
			} else if (!preg_match ('/^#[0-9A-Fa-f]{6}$/', $this->backgroundColor)) {
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_INVALID_BACKGROUND_COLOR);
			} else if (empty ($this->fromFieldName)) {
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_EMPTY_FROM_FIELD_NAME);
			} else if (empty ($this->fromModuleName)) {
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_EMPTY_FROM_MODULE_NAME);
			} else if (empty ($this->label)) {
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_EMPTY_LABEL);
			} else if (empty ($this->titleFieldName)) {
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_EMPTY_TITLE_FIELD_NAME);
			} else if (empty ($this->titleModuleName)) {
				throw new CalendarViewException (CalendarViewException::ERROR_CALENDAR_VIEW_EMPTY_TITLE_MODULE_NAME);
			} else {
				$this->validateRules ();
			}
		}

		/**
		 * @return CalendarView
		 */
		public static function getInstance () {
			return new self ();
		}

	}

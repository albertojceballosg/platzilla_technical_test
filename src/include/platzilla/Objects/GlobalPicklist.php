<?php
	require_once ('include/platzilla/Objects/GlobalPicklistValue.php');
	require_once ('include/platzilla/Objects/Picklist.php');

	/**
	 * Class GlobalPicklist
	 *
	 * @codingStandardsIgnoreStart
	 * @property GlobalPicklistValue[] $values
	 * @method boolean areValuesEqual ($values)
	 * @method GlobalPicklist setId ($id)
	 * @method GlobalPicklist setName ($name)
	 * @method GlobalPicklist setValues ($values)
	 * @method GlobalPicklist copyValuesFrom ($picklist)
	 * @method GlobalPicklist duplicate ($newPicklistId)
	 * @codingStandardsIgnoreEnd
	 */
	class GlobalPicklist extends Picklist {
		/** @var string */
		private $label;

		/** @var boolean */
		private $multiple;

		/**
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * @return boolean
		 */
		public function isMultiple () {
			return $this->multiple;
		}

		/**
		 * @param string $label
		 *
		 * @return GlobalPicklist
		 */
		public function setLabel ($label) {
			if (is_scalar ($label)) {
				$this->label = $label;
			} else {
				$this->label = null;
			}
			return $this;
		}

		/**
		 * @param boolean $multiple
		 *
		 * @return GlobalPicklist
		 */
		public function setMultiple ($multiple) {
			if ((is_bool ($multiple)) && (boolval ($multiple) === $multiple)) {
				$this->multiple = $multiple;
			} else {
				$this->multiple = null;
			}
			return $this;
		}

		/**
		 * @param GlobalPicklist $picklist
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($picklist, $deepCheck = true) {
			if (
				(empty ($picklist)) ||
				(!($picklist instanceof GlobalPicklist)) ||
				($this->name != $picklist->getName ()) ||
				(($deepCheck) && (!$this->areValuesEqual ($picklist->getValues ())))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws PicklistException
		 */
		public function validate () {
			if (empty ($this->name)) {
				throw new PicklistException (PicklistException::ERROR_PICKLIST_EMPTY_NAME);
			} else if (empty ($this->label)) {
				throw new PicklistException (PicklistException::ERROR_PICKLIST_EMPTY_LABEL);
			} else {
				foreach ($this->values as $value) {
					if (!($value instanceof GlobalPicklistValue)) {
						throw new PicklistException (PicklistException::ERROR_PICKLIST_INVALID_VALUE);
					}
				}
			}
		}

		/**
		 * @return GlobalPicklist
		 */
		public static function getInstance () {
			return new self ();
		}

	}

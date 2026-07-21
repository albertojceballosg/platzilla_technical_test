<?php
	require_once ('include/platzilla/Objects/PicklistValue.php');

	/**
	 * Class GlobalPicklistValue
	 *
	 * @codingStandardsIgnoreStart
	 * @method GlobalPicklistValue setId ($id)
	 * @method GlobalPicklistValue setValue ($value)
	 * @codingStandardsIgnoreEnd
	 */
	class GlobalPicklistValue extends PicklistValue {

		public function __construct () {
			parent::__construct (true);
			parent::setLocked (true);
		}

		/**
		 * @param boolean $deleted
		 *
		 * @return GlobalPicklistValue
		 */
		public function setDeleted ($deleted) {
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return GlobalPicklistValue
		 */
		public function setLocked ($locked) {
			return $this;
		}

		/**
		 * @param integer $presence
		 *
		 * @return GlobalPicklistValue
		 */
		public function setPresence ($presence) {
			return $this;
		}

		/**
		 * @param Role[] $roles
		 *
		 * @return GlobalPicklistValue
		 */
		public function setRoles ($roles) {
			return $this;
		}

		/**
		 * @param GlobalPicklistValue $picklistValue
		 */
		public function copyValuesFrom ($picklistValue) {
			if ((empty ($picklistValue)) || (!($picklistValue instanceof GlobalPicklistValue))) {
				return;
			}

			$this->setValue ($picklistValue->getValue ());
		}

		/**
		 * @param integer $newValueId
		 *
		 * @return GlobalPicklistValue
		 */
		public function duplicate ($newValueId) {
			$object = new self ();
			return $object->setId ($newValueId)
				->setValue ($this->getValue ());
		}

		/**
		 * @param PicklistValue $picklistValue
		 *
		 * @return boolean
		 */
		public function isEqualTo ($picklistValue) {
			if (
				(empty ($picklistValue)) ||
				(!($picklistValue instanceof GlobalPicklistValue)) ||
				($this->getValue () != $picklistValue->getValue ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @return GlobalPicklistValue
		 */
		public static function getInstance () {
			return new self ();
		}

	}

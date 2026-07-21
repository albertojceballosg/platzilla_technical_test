<?php
	require_once ('include/platzilla/Exceptions/EditableFieldsException.php');
	require_once ('include/platzilla/Objects/EditableFieldsInterface.php');

	class EditableFieldsField implements EditableFieldsInterface {
		/** @var integer */
		private $id;

		/** @var string */
		private $buttonName;

		/** @var Field */
		private $field;

		/** @var string */
		private $fieldLabel;

		/** @var string */
		private $fieldName;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @param EditableFieldsField $eff
		 */
		public function copyValuesFrom ($eff) {
			if ((empty ($eff)) || (!($eff instanceof EditableFieldsField))) {
				return;
			}
			$this->id         = $eff->getId ();
			$this->buttonName = $eff->getButtonName ();
			$this->fieldLabel = $eff->getFieldLabel ();
			$this->fieldName  = $eff->getFieldName ();
		}

		/**
		 * @param null|integer $newEditableFieldId
		 *
		 * @return EditableFieldsField
		 */
		public function duplicate ($newEditableFieldId = null) {
			$object = new self ();
			return $object->setId ($newEditableFieldId)
				->setButtonName ($this->buttonName)
				->setFieldLabel($this->fieldLabel)
				->setFieldName ($this->fieldName);
		}

		/**
		 * @return string
		 */
		public function getButtonName () {
			return $this->buttonName;
		}

		/**
		 * @return Field
		 */
		public function getField() {
			return $this->field;
		}

		/**
		 * @return string
		 */
		public function getFieldLabel () {
			return $this->fieldLabel;
		}

		/**
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * @param integer $id
		 *
		 * @return EditableFieldsField
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param string $buttonName
		 *
		 * @return EditableFieldsField
		 */
		public function setButtonName ($buttonName) {
			if (is_scalar($buttonName)) {
				$this->buttonName = $buttonName;
			} else {
				$this->buttonName = null;
			}
			return $this;
		}

		/**
		 * @param $field
		 *
		 * @return EditableFieldsField
		 */
		public function setField ($field) {
			if ($field instanceof Field) {
				$this->field = $field;
			} else {
				$this->field = null;
			}
			return $this;
		}

		/**
		 * @param string $fieldLabel
		 *
		 * @return EditableFieldsField
		 */
		public function setFieldLabel ($fieldLabel) {
			if (is_scalar($fieldLabel)) {
				$this->fieldLabel = $fieldLabel;
			} else {
				$this->fieldLabel = null;
			}
			return $this;
		}

		/**
		 * @param string $fieldName
		 *
		 * @return EditableFieldsField
		 */
		public function setFieldName ($fieldName) {
			if (is_scalar($fieldName)) {
				$this->fieldName = $fieldName;
			} else {
				$this->fieldName = null;
			}
			return $this;
		}

		/**
		 * @param EditableFieldsField $eff
		 *
		 * @return boolean
		 */
		public function isEqualTo ($eff) {
			if (
				(empty ($eff)) ||
				($this->buttonName != $eff->getButtonName ()) ||
				($this->fieldLabel != $eff->getFieldLabel ()) ||
				($this->fieldName != $eff->getFieldName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @throws EditableFieldsException
		 */
		public function validate () {
			if (empty ($this->fieldName)) {
				throw new EditableFieldsException (EditableFieldsException::ERROR_EDITABLE_FIELD_EMPTY_FIELD_NAME);
			} else if (empty ($this->buttonName)) {
				throw new EditableFieldsException (EditableFieldsException::ERROR_EDITABLE_FIELD_EMPTY_BUTTON_NAME);
			}
		}

		/**
		 * @return EditableFieldsField
		 */
		public static function getInstance () {
			return new self ();
		}

	}

<?php
	require_once ('include/platzilla/Exceptions/EditableFieldsException.php');
	require_once ('include/platzilla/Objects/EditableFieldsInterface.php');

	class EditableFieldsButton implements EditableFieldsInterface {
		/** @var integer */
		private $id;

		/** @var EditableFieldsField[] */
		private $editableFields;

		/** @var string */
		private $description;

		/** @var string */
		private $instances;

		/** @var string */
		private $label;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		/** @var boolean */
		private $status;
		
		/**
		 * @param EditableFieldsField[] $editableFields
		 *
		 * @return array|null
		 */
		private function copyEditableFields ($editableFields) {
			if (empty($editableFields)) {
				return null;
			}
			$fields = array();
			foreach ($editableFields as $editableField) {
				if (empty($editableField) || !$editableField instanceof EditableFieldsField) {
					continue;
				}
				$fields [] = $editableField->duplicate ();
			}
			return (count ($fields)) ? $fields : null;
		}

		/**
		 * @param EditableFieldsField[] $effs
		 *
		 * @return array
		 */
		private function duplicateFromEditableFields ($effs) {
			$editableFields = array ();
			foreach ($effs as $eff) {
				$editableFields [] = $eff->duplicate ();
			}
			return $editableFields;
		}

		/**
		 * @param EditableFieldsField[] $theseFieldsField
		 * @param EditableFieldsField[] $thoseFieldsField
		 *
		 * @return boolean
		 */
		private function isEditableFieldsEqualTo ($theseFieldsField, $thoseFieldsField) {
			$totalFieldsField = count ($theseFieldsField);
			$equals          = true;
			if ($totalFieldsField != count ($thoseFieldsField)) {
				return false;
			}

			for ($k = 0; $k < $totalFieldsField; $k++) {
				if (!$theseFieldsField [ $k ]->isEqualTo ($thoseFieldsField [ $k ])) {
					$equals = false;
				}
			}
			return $equals;
		}

		public function __construct () {
			$this->locked = false;
			$this->status = true;
		}

		/**
		 * @param EditableFieldsButton $efb
		 */
		public function copyValuesFrom ($efb) {
			if ((empty ($efb)) || (!($efb instanceof EditableFieldsButton))) {
				return;
			}
			$this->id             = $efb->getId ();
			$this->description    = $efb->getDescription ();
			$this->editableFields = $this->copyEditableFields ($efb->getEditableFields ());
			$this->instances      = $efb->getInstance ();
			$this->label          = $efb->getLabel ();
			$this->locked         = $efb->isLocked ();
			$this->moduleName     = $efb->getModuleName ();
			$this->name           = $efb->getName();
			$this->status         = $efb->isStatus ();
		}

		/**
		 * @param null|integer $editableFieldButtonId
		 *
		 * @return EditableFieldsButton
		 */
		public function duplicate ($editableFieldButtonId = null) {
			$object = new self ();
			return $object->setId ($editableFieldButtonId)
				->setEditableFields ($this->duplicateFromEditableFields ($this->editableFields))
				->setDescription ($this->description)
				->setInstances ($this->instances)
				->setLabel ($this->label)
				->setLocked ($this->locked)
				->setModuleName ($this->moduleName)
				->setName ($this->name)
				->setStatus ($this->status);
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return EditableFieldsField[]
		 */
		public function getEditableFields () {
			return $this->editableFields;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return string
		 */
		public function getInstances () {
			return $this->instances;
		}

		/**
		 * @return string
		 */
		public function getLabel() {
			return $this->label;
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
		 * @param EditableFieldsButton $efb
		 *
		 * @return boolean
		 */
		public function isEqualTo ($efb) {
			if (
				(empty ($efb)) ||
				($this->isEditableFieldsEqualTo($this->editableFields, $efb->getEditableFields())) ||
				($this->description != $efb->getDescription ()) ||
				($this->instances != $efb->getInstances ()) ||
				($this->label != $efb->getLabel ()) ||
				($this->moduleName != $efb->getModuleName ()) ||
				($this->name != $efb->getName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @return boolean
		 */
		public function isStatus() {
			return $this->status;
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
		 * @return EditableFieldsButton
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * @param null|EditableFieldsField[] $editableFields
		 *
		 * @return EditableFieldsButton
		 */
		public function setEditableFields($editableFields) {
			if (empty($editableFields)) {
				$this->editableFields = null;
				return $this;
			}
			foreach ($editableFields as $editableField) {
				if (($editableField == null) || ($editableField instanceof EditableFieldsField) && (!empty ($editableField))) {
					$this->editableFields [] = $editableField;
				}
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return EditableFieldsButton
		 */
		public function setDescription ($description) {
			if (is_scalar ($description)) {
				$this->description = $description;
			} else {
				$this->description = null;
			}
			return $this;
		}

		/**
		 * @param string $instances
		 *
		 * @return EditableFieldsButton
		 */
		public function setInstances ($instances) {
			if (is_scalar ($instances)) {
				$this->instances = $instances;
			} else {
				$this->instances = null;
			}
			return $this;
		}

		/**
		 * @param string $label
		 *
		 * @return EditableFieldsButton
		 */
		public function setLabel ($label) {
			if (is_scalar($label)) {
				$this->label = $label;
			} else {
				$this->label = null;
			}
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return EditableFieldsButton
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
		 * @return EditableFieldsButton
		 */
		public function setModuleName ($moduleName) {
			if(is_scalar ($moduleName)) {
				$this->moduleName = $moduleName;
			} else {
				$this->moduleName = null;
			}
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return EditableFieldsButton
		 */
		public function setName ($name) {
			if (is_scalar($name)) {
				$this->name = $name;
			} else {
				$this->name = null;
			}
			return $this;
		}

		/**
		 * @param boolean $status
		 *
		 * @return EditableFieldsButton
		 */
		public function setStatus ($status) {
			if (is_bool ($status)) {
				$this->status = $status;
			}
			return $this;
		}

		/**
		 * @throws EditableFieldsException
		 */
		public function validate () {
			if (empty ($this->moduleName)) {
				throw new EditableFieldsException (EditableFieldsException::ERROR_EDITABLE_FIELD_EMPTY_MODULE_NAME);
			} else if (empty ($this->name)) {
				throw new EditableFieldsException (EditableFieldsException::ERROR_EDITABLE_FIELD_EMPTY_BUTTON_NAME);
			} else if (empty ($this->editableFields)) {
				throw new EditableFieldsException (EditableFieldsException::ERROR_EDITABLE_FIELD_EMPTY_EDITABLE_FIELD);
			}
		}

		/**
		 * @return EditableFieldsButton
		 */
		public static function getInstance () {
			return new self ();
		}

	}

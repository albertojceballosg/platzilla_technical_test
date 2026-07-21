<?php
	require_once ('include/platzilla/Exceptions/KanbanViewException.php');
	require_once ('include/platzilla/Objects/KanbanViewInterface.php');

	class KanbanCardConfig implements KanbanViewInterface, Serializable {

		/**@var integer */
		private $idCardField;

		/** @var string */
		private $fieldName;

		/** @var string */
		private $fieldLabel;

		/** @var integer */
		private $idField;

		/** @var integer */
		private $idKanban;

		/**
		 * @param $kanban
		 */
		public function copyValuesFrom ($kanban) {
			if ((empty ($kanban)) || (!($kanban instanceof KanbanCardConfig))) {
				return;
			}
			$this->idField   = $kanban->getIdField ();
			$this->fieldName = $kanban->getFieldName ();
			$this->idKanban  = $kanban->getIdKanban ();
		}

		/**
		 * @param integer $newKanbanViewId
		 *
		 * @return KanbanCardConfig
		 */
		public function duplicate ($newKanbanViewId = null) {
			$object = new self ();
			return $object->setIdCardField (!empty ($newKanbanViewId) ? $this->idCardField : null)
				->setIdField ($this->idField)
				->setFieldName ($this->fieldName)
				->setIdKanban (!empty ($newKanbanViewId) ? $newKanbanViewId : null);
		}

		/**
		 * @return integer
		 */
		public function getIdCardField () {
			return $this->idCardField;
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
		public function getFieldLabel () {
			return $this->fieldLabel;
		}

		/**
		 * @return integer
		 */
		public function getIdField () {
			return $this->idField;
		}

		/**
		 * @return integer
		 */
		public function getIdKanban () {
			return $this->idKanban;
		}

		/**
		 * @param KanbanCardConfig $kanban
		 *
		 * @return boolean
		 */
		public function isEqualTo ($kanban) {
			if (
				(empty ($kanban)) ||
				(!($kanban instanceof KanbanCardConfig)) ||
				($this->fieldName != $kanban->getFieldName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param integer $id
		 *
		 * @return KanbanCardConfig
		 */
		public function setIdCardField ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->idCardField = $id;
			} else {
				$this->idCardField = null;
			}
			return $this;
		}

		/**
		 * @param $fieldName
		 *
		 * @return KanbanCardConfig
		 */
		public function setFieldName ($fieldName) {
			if (is_scalar ($fieldName)) {
				$this->fieldName = $fieldName;
			} else {
				$this->fieldName = null;
			}
			return $this;
		}

		/**
		 * @param string $fieldLabel
		 *
		 * @return KanbanCardConfig
		 */
		public function setFieldLabel ($fieldLabel) {
			if (is_scalar ($fieldLabel)) {
				$this->fieldLabel = $fieldLabel;
			} else {
				$this->fieldLabel = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return KanbanCardConfig
		 */
		public function setIdField ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->idField = $id;
			} else {
				$this->idField = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return KanbanCardConfig
		 */
		public function setIdKanban ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->idKanban = $id;
			} else {
				$this->idKanban = null;
			}
			return $this;
		}

		/**
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->idCardField,
					$this->idField,
					$this->fieldName,
					$this->idKanban,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->idCardField,
				$this->idField,
				$this->fieldName,
				$this->idKanban,
				) = unserialize ($serialized);
		}

		/**
		 * @throws KanbanViewException
		 */
		public function validate () {
			if (empty ($this->idField)) {
				throw new KanbanViewException (KanbanViewException::ERROR_KANABAN_VIEW_EMPTY_FIELD_ID);
			}
		}

		/**
		 * @return KanbanCardConfig
		 */
		public static function getInstance () {
			return new self ();
		}

	}

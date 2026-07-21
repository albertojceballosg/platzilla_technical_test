<?php
	require_once ('include/platzilla/Exceptions/KanbanViewException.php');
	require_once ('include/platzilla/Objects/KanbanViewInterface.php');

	class KanbanFieldConfig implements KanbanViewInterface, Serializable {
		/**@var integer */
		private  $idKanbanFieldConfig;

		/** @var @var string */
		private $backgroundColor;

		/** @var string */
		private $fieldName;

		/** @var string */
		private $fieldNameOperation;

		/** @var integer */
		private $idKanban;

		/** @var integer */
		private $idPickField;

		/** @var string */
		private $operation;

		/**
		 * @param $kanban
		 */
		public function copyValuesFrom ($kanban) {
			if ((empty ($kanban)) || (!($kanban instanceof KanbanFieldConfig))) {
				return;
			}
			$this->backgroundColor    = $kanban->getBackgroundColor ();
			$this->fieldName          = $kanban->getFieldName ();
			$this->fieldNameOperation = $kanban->getFieldNameOperation ();
			$this->idKanban           = $kanban->getIdKanban ();
			$this->idPickField        = $kanban->getIdPickField ();
			$this->operation          = $kanban->getOperation ();
		}

		/**
		 * @param integer $newKanbanViewId
		 *
		 * @return KanbanFieldConfig
		 */
		public function duplicate ($newKanbanViewId = null) {
			$object = new self ();
			return $object->setIdKanbanFieldConfig (!empty ($newKanbanViewId) ? $this->idKanbanFieldConfig : null)
				->setBackgroundColor ($this->backgroundColor)
				->setFieldName ($this->fieldName)
				->setFieldNameOperation ($this->fieldNameOperation)
				->setIdKanban (!empty ($newKanbanViewId) ? $newKanbanViewId : null)
				->setIdPickField ($this->idPickField)
				->setOperation ($this->operation);
		}

		/**
		 * @return integer
		 */
		public function getIdKanbanFieldConfig () {
			return $this->idKanbanFieldConfig;
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
		public function getFieldNameOperation() {
			return $this->fieldNameOperation;
		}

		/**
		 * @return integer
		 */
		public function getIdKanban () {
			return $this->idKanban;
		}

		/**
		 * @return integer
		 */
		public function getIdPickField () {
			return $this->idPickField;
		}

		/**
		 * @return string
		 */
		public function getOperation() {
			return $this->operation;
		}

		/**
		 * @param KanbanFieldConfig $kanban
		 *
		 * @return boolean
		 */
		public function isEqualTo ($kanban) {
			if (
				(empty ($kanban)) ||
				(!($kanban instanceof KanbanFieldConfig)) ||
				($this->backgroundColor != $kanban->getBackgroundColor ()) ||
				($this->fieldName != $kanban->getFieldName ()) ||
				($this->fieldNameOperation != $kanban->getFieldNameOperation ()) ||
				($this->operation != $kanban->getOperation ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * @param integer $id
		 *
		 * @return KanbanFieldConfig
		 */
		public function setIdKanbanFieldConfig ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->idKanbanFieldConfig = $id;
			} else {
				$this->idKanbanFieldConfig = null;
			}
			return $this;
		}

		/**
		 * @param $colorCode
		 *
		 * @return KanbanFieldConfig
		 */
		public function setBackgroundColor ($colorCode) {
			if (is_scalar ($colorCode)) {
				$this->backgroundColor = $colorCode;
			} else {
				$this->backgroundColor = null;
			}
			return $this;
		}

		/**
		 * @param $fieldName
		 *
		 * @return KanbanFieldConfig
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
		 * @param $fieldName
		 *
		 * @return KanbanFieldConfig
		 */
		public function setFieldNameOperation ($fieldName) {
			if (is_scalar ($fieldName)) {
				$this->fieldNameOperation = $fieldName;
			} else {
				$this->fieldNameOperation = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return KanbanFieldConfig
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
		 * @param integer $id
		 *
		 * @return KanbanFieldConfig
		 */
		public function setIdPickField ($id) {
			// Se permite 0 porque los pipelines usan el indice del array JSON (0-based)
			// como pickfieldid. Para picklists clasicos los ids empiezan en 1, asi que aceptar
			// 0 no introduce ambiguedad: no existen picklists con id=0 en vtiger_{fieldname}.
			if ((is_numeric ($id)) && ($id >= 0) && (intval ($id) == $id)) {
				$this->idPickField = intval ($id);
			} else {
				$this->idPickField = null;
			}
			return $this;
		}

		/**
		 * @param string $operation
		 *
		 * @return KanbanFieldConfig
		 */
		public function setOperation ($operation) {
			if (is_scalar ($operation)) {
				$this->operation = $operation;
			} else {
				$this->operation = null;
			}
			return $this;
		}

		/**
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->idKanbanFieldConfig,
					$this->backgroundColor,
					$this->fieldName,
					$this->fieldNameOperation,
					$this->idPickField,
					$this->idKanban,
					$this->operation,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->idKanbanFieldConfig,
				$this->backgroundColor,
				$this->fieldName,
				$this->fieldNameOperation,
				$this->idPickField,
				$this->idKanban,
				$this->operation,
				) = unserialize ($serialized);
		}

		/**
		 * @throws KanbanViewException
		 */
		public function validate () {
			// Se permite el valor numerico 0 porque los pipelines usan el indice del array JSON
			// como pickfieldid (0-based), por lo que empty() daria falso positivo.
			if ($this->idPickField === null || $this->idPickField === '' || !is_numeric ($this->idPickField) || intval ($this->idPickField) < 0) {
				throw new KanbanViewException (KanbanViewException::ERROR_KANABAN_VIEW_EMPTY_PICK_ID);
			} else if (empty ($this->backgroundColor)) {
				throw new KanbanViewException (KanbanViewException::ERROR_KANABAN_VIEW_EMPTY_BACKGROUND_COLOR);
			}
		}

		/**
		 * @return KanbanFieldConfig
		 */
		public static function getInstance () {
			return new self ();
		}

	}

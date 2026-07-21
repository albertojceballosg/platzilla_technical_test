<?php
	require_once ('include/platzilla/Data/EntityHistoryException.php');

	/**
	 * Class EntityHistory
	 *
	 * Esta clase hace referencia a los campos de la tabla vtiger_crmrntityutils del registro histórico de cambios
	 */
	class EntityHistory implements Serializable {
		/** @var integer */
		private $id;

		/** @var string */
		private $createdDate;

		/** @var  integer */
		private $fieldId;

		/** @var  string */
		private $fieldLabel;

		/** @var  string */
		private $fieldName;

		/** @var integer */
		private $moduleId;

		/** @var  string */
		private $moduleLabel;

		/** @var string */
		private $moduleName;

		/** @var integer */
		private $modifiedBy;

		/** @var integer */
		private $modifiedOn;

		/** @var string */
		private $newValue;

		/** @var string */
		private $oldValue;

		/** @var integer */
		private $registryId;

		/** @var integer */
		private $uiType;

		/** @var string */
		private $userName;

		/**
		 * Valida que el formato y datos de la fecha esten correctos
		 *
		 * @param string $date
		 * @param string $format
		 *
		 * @return boolean
		 */
		private function validateDate ($date, $format = 'Y-m-d') {
			$objectDate = DateTime::createFromFormat ($format, $date);
			return $objectDate && $objectDate->format ($format) == $date;
		}

		/**
		 * Obtiene el ID del registro histórico actual
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Devuelve la fecha de creación del registro actual
		 *
		 * @return string
		 */
		public function getCreatedDate () {
			return $this->createdDate;
		}

		/**
		 * Obtiene el ID del campo incluido en el histórico actual
		 *
		 * @return integer
		 */
		public function getFieldId () {
			return $this->fieldId;
		}

		/**
		 * Obtiene la etiqueta del campo involucrado en el registro histórico actual
		 *
		 * @return string
		 */
		public function getFieldLabel () {
			return $this->fieldLabel;
		}

		/**
		 * Obtiene el nombre del campo involucrado en el registro histórico actual
		 *
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}

		/**
		 * Obtiene el tabid del módulo al cual pertenecen los campos en el registro histórico actual
		 *
		 * @return integer
		 */
		public function getModuleId () {
			return $this->moduleId;
		}

		/**
		 * Obtiene la etiqueta del módulo al cual pertenecen los campos en el registro histórico actual
		 *
		 * @return string
		 */
		public function getModuleLabel () {
			return $this->moduleLabel;
		}

		/**
		 * Obtiene el nombre del módulo al cual pertenecen los campos en el registro histórico actual
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Devuelve el ID del usuario quien realizo la modificación del contenido del campo en el histórico de cambios
		 *
		 * @return integer
		 */
		public function getModifiedBy () {
			return $this->modifiedBy;
		}

		/**
		 * Devuelve el 0/1 que indica si se ha mmodificado o insertado respectivamente
		 *
		 * @return integer
		 */
		public function getModifiedOn () {
			return $this->modifiedOn;
		}

		/**
		 * Devuelve el valor actual (nuevo) en el campo involucrado en el registro histórico actual
		 *
		 * @return string
		 */
		public function getNewValue () {
			return $this->newValue;
		}

		/**
		 * Devuelve el valor anterior (viejo) en el campo involucrado en el registro histórico actual
		 *
		 * @return string
		 */
		public function getOldValue () {
			return $this->oldValue;
		}

		/**
		 * Obtiene el ID del registro cuyo módulo contiene todos los campos involucrados en el histórico de cambios actual
		 *
		 * @return integer
		 */
		public function getRegistryId () {
			return $this->registryId;
		}

		/**
		 * Devuelve el código uitype del campo involucrado en el registro histórico actual
		 *
		 * @return integer
		 */
		public function getUiType () {
			return $this->uiType;
		}

		/**
		 * Devuelve el nombre del usuario quien realizo la actualización en el campo
		 *
		 * @return string
		 */
		public function getUserName () {
			return $this->userName;
		}

		/**
		 * Establece el ID del registro histórico actual
		 *
		 * @param integer $id
		 *
		 * @return EntityHistory
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = $id;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha de creación del registro actual
		 *
		 * @param string $dateTime
		 *
		 * @return EntityHistory
		 */
		public function setCreatedDate ($dateTime) {
			$date = explode (' ', $dateTime);
			if ($this->validateDate ($date [0])) {
				$this->createdDate = $dateTime;
			} else {
				$this->createdDate = null;
			}
			return $this;
		}

		/**
		 * Establece el ID del campo incluido en el histórico actual
		 *
		 * @param integer $fieldId
		 *
		 * @return EntityHistory
		 */
		public function setFieldId ($fieldId) {
			if ((is_numeric ($fieldId)) && ($fieldId > 0) && (intval ($fieldId) == $fieldId)) {
				$this->fieldId = $fieldId;
			} else {
				$this->fieldId = 0;
			}
			return $this;
		}

		/**
		 * Establece la etiqueta del campo incluido en el histórico actual
		 *
		 * @param string $fieldLabel
		 *
		 * @return EntityHistory
		 */
		public function setFieldLabel ($fieldLabel) {
			if (is_scalar ($fieldLabel)) {
				$this->fieldLabel = $fieldLabel;
			} else {
				$this->fieldName = null;
			}
			return $this;
		}

		/**
		 * Establece el nombre del campo incluido en el histórico actual
		 *
		 * @param string $fieldName
		 *
		 * @return EntityHistory
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
		 * Establece el tabid del módulo al cual pertenecen los campos en el registro histórico actual
		 *
		 * @param integer $moduleId
		 *
		 * @return EntityHistory
		 */
		public function setModuleId ($moduleId) {
			if ((is_numeric ($moduleId)) && ($moduleId > 0) && (intval ($moduleId) == $moduleId)) {
				$this->moduleId = $moduleId;
			} else {
				$this->moduleId = 0;
			}
			return $this;
		}

		/**
		 * Establece la etiqueta del módulo al cual pertenecen los campos en el registro histórico actual
		 *
		 * @param string $moduleLabel
		 *
		 * @return EntityHistory
		 */
		public function setModuleLabel ($moduleLabel) {
			if (is_scalar ($moduleLabel)) {
				$this->moduleLabel = $moduleLabel;
			} else {
				$this->moduleLabel = null;
			}
			return $this;
		}

		/**
		 * Establece el nombre del módulo al cual pertenecen los campos en el registro histórico actual
		 *
		 * @param string $moduleName
		 *
		 * @return EntityHistory
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
		 * Establece el ID del usuario quien realizo la modificación del contenido del campo en el histórico de cambios
		 *
		 * @param integer $modifiedBy
		 *
		 * @return EntityHistory
		 */
		public function setModifiedBy ($modifiedBy) {
			if ((is_numeric ($modifiedBy)) && ($modifiedBy > 0) && (intval ($modifiedBy) == $modifiedBy)) {
				$this->modifiedBy = $modifiedBy;
			} else {
				$this->modifiedBy = 0;
			}
			return $this;
		}

		/**
		 * @param integer $modifiedOn
		 *
		 * @return EntityHistory
		 */
		public function setModifiedOn ($modifiedOn) {
			if ((is_numeric ($modifiedOn)) && ($modifiedOn > 0) && (intval ($modifiedOn) == $modifiedOn)) {
				$this->modifiedOn = $modifiedOn;
			} else {
				$this->modifiedOn = 0;
			}
			return $this;
		}

		/**
		 * Establece el valor actual (nuevo) en el campo involucrado en el registro histórico actual
		 *
		 * @param string $newValue
		 *
		 * @return EntityHistory
		 */
		public function setNewValue ($newValue) {
			if (is_scalar ($newValue)) {
				$this->newValue = $newValue;
			} else {
				$this->newValue = null;
			}
			return $this;
		}

		/**
		 * Establce el valor anterior  (viejo) en el campo involucrado en el registro histórico actual
		 *
		 * @param string $oldValue
		 *
		 * @return EntityHistory
		 */
		public function setOldValue ($oldValue) {
			if (is_scalar ($oldValue) || empty($oldValue)) {
				$this->oldValue = $oldValue;
			} else {
				$this->oldValue = null;
			}
			return $this;
		}

		/**
		 * Establece el ID del registro cuyo módulo contiene todos los campos involucrados en el histórico de cambios actual
		 *
		 * @param integer $registryId
		 *
		 * @return EntityHistory
		 */
		public function setRegistryId ($registryId) {
			if ((is_numeric ($registryId)) && ($registryId > 0) && (intval ($registryId) == $registryId)) {
				$this->registryId = $registryId;
			} else {
				$this->registryId = 0;
			}
			return $this;
		}

		/**
		 * Serializa los valores establecidos para el registro histórico actual
		 *
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->id,
					$this->createdDate,
					$this->fieldId,
					$this->fieldName,
					$this->moduleId,
					$this->moduleName,
					$this->modifiedBy,
					$this->modifiedOn,
					$this->newValue,
					$this->oldValue,
					$this->registryId,
				)
			);
		}

		/**
		 * Establece el código uitype del campo involucrado en el registro histórico actual
		 *
		 * @param integer $uiType
		 *
		 * @return EntityHistory
		 */
		public function setUiType ($uiType) {
			if ((is_numeric ($uiType)) && ($uiType > 0) && (intval ($uiType) == $uiType)) {
				$this->uiType = $uiType;
			} else {
				$this->uiType = null;
			}
			return $this;
		}

		/**
		 * Recupera valores desde una cadena serializada
		 *
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->createdDate,
				$this->fieldId,
				$this->fieldName,
				$this->moduleId,
				$this->moduleName,
				$this->modifiedBy,
				$this->modifiedOn,
				$this->newValue,
				$this->oldValue,
				$this->registryId,
				) = unserialize ($serialized);
		}

		/**
		 * Establece el nombre del usuario
		 *
		 * @param string $userName
		 *
		 * @return EntityHistory
		 */
		public function setUserName ($userName) {
			if (is_scalar ($userName)) {
				$this->userName = $userName;
			} else {
				$this->userName = null;
			}
			return $this;
		}

		/**
		 * Valida los datos del registro histórico de cambios
		 *
		 * @throws EntityHistoryException
		 */
		public function validate () {
			if (empty ($this->createdDate)) {
				throw new EntityHistoryException (EntityHistoryException::ERROR_ENTITY_HISTORY_EMPTY_DATETIME);
			} else if (empty ($this->fieldId)) {
				throw new EntityHistoryException (EntityHistoryException::ERROR_ENTITY_HISTORY_EMPTY_FIELD_ID);
			} else if (empty ($this->moduleId)) {
				throw new EntityHistoryException (EntityHistoryException::ERROR_ENTITY_HISTORY_EMPTY_MODULE_ID);
			} else if (empty ($this->registryId)) {
				throw new EntityHistoryException (EntityHistoryException::ERROR_ENTITY_HISTORY_EMPTY_RECORD_ID);
			}
		}

		/**
		 * Se obtiene un objeto EntityHistory con los atributos de la clase
		 *
		 * @return EntityHistory
		 */
		public static function getInstance () {
			return new self ();
		}

	}

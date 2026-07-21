<?php
	require_once ('include/platzilla/Exceptions/FieldException.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Exceptions/GridFieldException.php');
	require_once ('include/platzilla/Objects/GridFieldInterface.php');
	
	class TableField implements FieldInterface {
		
		/** @var string */
		private $actionField;
		
		/** @var array */
		private $actionFieldArray;
		
		/** @var string */
		private $attributes;
		
		/** @var array */
		private $attributesArray;
		
		/** @var string */
		private $dataField;
		
		/** @var string|integer|null */
		private $defaultValue;
		
		/** @var string */
		private $entityName;
		
		/** @var string */
		private $fieldLabel;
		
		/** @var integer */
		private $fieldLength;
		
		/** @var string */
		private $fieldName;
		
		/** @var integer */
		private $fieldPrecision;
		
		/** @var array */
		private $filterField;
		
		/** @var string */
		private $relModule;
		
		/** @var integer */
		private $sequence;
		
		/** @var integer */
		private $tableFieldId;
		
		/** @var string */
		private $tableFieldName;
		
		/** @var string */
		private $tableName;
		
		/** @var integer */
		private $locked;
		
		/** @var integer */
		private $uiType;
		
		/**
		 * @return array
		 */
		public function getActionArray () {
			return $this->actionFieldArray;
		}
		
		/**
		 * @return string
		 */
		public function getActionField () {
			return $this->actionField;
		}
		
		/**
		 * @return array
		 */
		public function getAttributesArray () {
			return $this->attributesArray;
		}
		
		/**
		 * @return string
		 */
		public function getAttributes () {
			return $this->attributes;
		}
		
		/**
		 * @return string
		 */
		public function getDataField () {
			return $this->dataField;
		}
		
		/**
		 * @return string
		 */
		public function getDefaultValue () {
			return $this->defaultValue;
		}
		
		/**
		 * @return string
		 */
		public function getEntityName () {
			return $this->entityName;
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
		public function getFieldLength () {
			return $this->fieldLength;
		}
		
		/**
		 * @return string
		 */
		public function getFieldName () {
			return $this->fieldName;
		}
		
		/**
		 * @return integer
		 */
		public function getFieldPrecision () {
			return $this->fieldPrecision;
		}
		
		/**
		 * @return array
		 */
		public function getFilterField () {
			return $this->filterField;
		}
		
		/**
		 * @return string
		 */
		public function getRelModule () {
			return $this->relModule;
		}
		
		/**
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}
		
		/**
		 * @return integer
		 */
		public function getTableFieldId () {
			return $this->tableFieldId;
		}
		
		/**
		 * @return string
		 */
		public function getTableFieldName () {
			return $this->tableFieldName;
		}
		
		/**
		 * @return string
		 */
		public function getTableName () {
			return $this->tableName;
		}
		
		/**
		 * @return integer
		 */
		public function getLocked () {
			return $this->locked;
		}
		
		/**
		 * @return integer
		 */
		public function getUiType () {
			return $this->uiType;
		}
		
		/**
		 * @param string $actionField
		 *
		 * @return TableField
		 */
		public function setActionFieldArray ($actionField) {
			$this->actionFieldArray  = unserialize (base64_decode ($actionField));
			return $this;
		}
		
		/**
		 * @param array|string $actionField
		 *
		 * @return TableField
		 */
		public function setActionField ($actionField) {
			if (!empty($actionField) && is_array ($actionField)) {
				$this->actionField = base64_encode (serialize ($actionField));
			} else {
				$this->actionField = $actionField;
			}
			return $this;
		}
		
		/**
		 * @param string $attributesArray
		 *
		 * @return TableField
		 */
		public function setAttributesArray ($attributesArray) {
			$this->attributesArray = json_decode ($attributesArray, true);
			return $this;
		}
		
		/**
		 * @param array|string $attributes
		 *
		 * @return TableField
		 */
		public function setAttributes ($attributes) {
			if (!empty($attributes) && is_array ($attributes)) {
				$this->attributes = json_encode ($attributes);
			} else {
				$this->attributes = $attributes;
			}
			return $this;
		}
		
		/**
		 * @param string $dataField
		 *
		 * @return TableField
		 */
		public function setDataField ($dataField) {
			$this->dataField = $dataField;
			return $this;
		}
		
		/**
		 * @param null|integer|string $defaultValue
		 *
		 * @return TableField
		 */
		public function setDefaultValue ($defaultValue) {
			$this->defaultValue = $defaultValue;
			return $this;
		}
		
		/**
		 * @param string $entityName
		 *
		 * @return TableField
		 */
		public function setEntityName ($entityName) {
			$this->entityName = $entityName;
			return $this;
		}
		
		/**
		 * @param string $fieldLabel
		 *
		 * @return TableField
		 */
		public function setFieldLabel ($fieldLabel) {
			$this->fieldLabel = $fieldLabel;
			return $this;
		}
		
		/**
		 * @param integer $fieldLength
		 *
		 * @return TableField
		 */
		public function setFieldLength ($fieldLength) {
			$this->fieldLength = $fieldLength;
			return $this;
		}
		
		/**
		 * @param string $fieldName
		 *
		 * @return TableField
		 */
		public function setFieldName ($fieldName) {
			$this->fieldName = $fieldName;
			return $this;
		}
		
		/**
		 * @param integer $fieldPrecision
		 *
		 * @return TableField
		 */
		public function setFieldPrecision ($fieldPrecision) {
			$this->fieldPrecision = $fieldPrecision;
			return $this;
		}
		
		/**
		 * @param array $filterField
		 *
		 * @return TableField
		 */
		public function setFilterField ($filterField) {
			if (!empty($filterField) && is_array ($filterField)) {
				$this->filterField = base64_encode (serialize ($filterField));
			} else {
				$this->filterField = $filterField;
			}
			return $this;
		}
		
		/**
		 * @param string $relModule
		 *
		 * @return TableField
		 */
		public function setRelModule ($relModule) {
			$this->relModule = $relModule;
			return $this;
		}
		
		/**
		 * @param integer $sequence
		 *
		 * @return TableField
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}
		
		/**
		 * @param string $tableFieldId
		 *
		 * @return TableField
		 */
		public function setTableFieldId ($tableFieldId) {
			$this->tableFieldId = $tableFieldId;
			return $this;
		}
		
		/**
		 * @param string $tableFieldName
		 *
		 * @return TableField
		 */
		public function setTableFieldName ($tableFieldName) {
			$this->tableFieldName = $tableFieldName;
			return $this;
		}
		
		/**
		 * @param string $tableName
		 *
		 * @return TableField
		 */
		public function setTableName ($tableName) {
			$this->tableName = $tableName;
			return $this;
		}
		
		/**
		 * @param integer $locked
		 *
		 * @return TableField
		 */
		public function setLocked ($locked) {
			$this->locked = $locked;
			return $this;
		}
		
		/**
		 * @param integer $uiType
		 *
		 * @return TableField
		 */
		public function setUiType ($uiType) {
			$this->uiType = $uiType;
			return $this;
		}
		
		/**
		 * @throws FieldException
		 */
		public function validate () {
			if (strlen ($this->fieldLength) > 50) {
				throw new FieldException (FieldException::ERROR_FIELD_COLUMN_NAME_TOO_LONG);
			} else if (strlen ($this->tableName) > 50) {
				throw new FieldException (FieldException::ERROR_FIELD_TABLE_NAME_TOO_LONG);
			} else if (empty($this->tableName)) {
				throw new FieldException(FieldException::ERROR_FIELD_EMPTY_TABLE_NAME);
			} else if (empty($this->fieldName)) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_NAME);
			}
		}
		
		/**
		 * @return TableField
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}

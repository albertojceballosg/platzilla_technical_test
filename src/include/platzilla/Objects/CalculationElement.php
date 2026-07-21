<?php
	require_once ('include/platzilla/Exceptions/CalculationElementException.php');
	require_once ('include/platzilla/Objects/CalculationElementInterface.php');

	class CalculationElement implements CalculationElementInterface, Serializable {
		/** @var integer */
		private $id;

		/** @var string */
		private $columnName;

		/** @var string */
		private $elementName;

		/** @var string */
		private $description;

		/** @var string */
		private $fieldlabel;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;
		
		/** @var string */
		private $period;
		
		/** @var string */
		private $periodField;
		
		/** @var string */
		private $operationName;

		/** @var @var string */
		private $relatedModules;

		/** @var float */
		private $result;

		/** @var string */
		private $sqlFilter;

		/** @var string */
		private $sqlData;

		/** @var string */
		private $status;

		/** @var string */
		private $tabLabel;

		/** @var datetime */
		private $updatedDate;

		/**
		 * @param datetime $date
		 * @param string $format
		 *
		 * @return boolean
		 */
		private function validateDate($date, $format = 'Y-m-d H:m:s') {
			$objectDate = DateTime::createFromFormat($format, $date);
			return $objectDate && $objectDate->format($format) == $date;
		}

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @param $calculation
		 */
		public function copyValuesFrom ($calculation) {
			if ((empty ($calculation)) || (!($calculation instanceof CalculationElement))) {
				return;
			}
			$this->id            = $calculation->getId ();
			$this->columnName    = $calculation->getColumnName ();
			$this->elementName    = $calculation->getElementName ();
			$this->description   = $calculation->getDescription ();
			$this->locked        = $calculation->isLocked ();
			$this->moduleName    = $calculation->getModuleName ();
			$this->name          = $calculation->getName ();
			$this->operationName = $calculation->getOperationName ();
			$this->relatedModules = $calculation->getRelatedModules();
			$this->result        = $calculation->getResult ();
			$this->sqlFilter     = $calculation->getSqlFilter ();
			$this->sqlData       = $calculation->getSqlData ();
			$this->status        = $calculation->getStatus ();
			$this->updatedDate   = $calculation->getUpdatedDate();
		}

		/**
		 * @return CalculationElement
		 */
		public function duplicate ($newCalculationElementId = null) {
			$object = new self ();
			return $object->setId ((! $newCalculationElementId) ? $this->id : $newCalculationElementId)
				->setColumnName ($this->columnName)
				->setElementName ($this->elementName)
				->setDescription ($this->description)
				->setLocked ($this->locked)
				->setModuleName ($this->moduleName)
				->setName ($this->name)
				->setOperationName ($this->operationName)
				->setRelatedModules ($this->relatedModules)
				->setResult ($this->result)
				->setSqlFilter ($this->sqlFilter)
				->setSqlData ($this->sqlData)
				->setStatus ($this->status)
				->setUpdatedDate (date ('Y-m-d H:m:s'));
		}

		/**
		 * @return string
		 */
		public function getColumnName () {
			return $this->columnName;
		}

		/**
		 * @return string
		 */
		public function getElementName () {
			return $this->elementName;
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
		public function getFieldLabel () {
			return $this->fieldlabel;
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
		 * @return string
		 */
		public function getPeriod () {
			return $this->period;
		}
		
		/**
		 * @return string
		 */
		public function getPeriodField () {
			return $this->periodField;
		}
		
		/**
		 * @return string
		 */
		public function getOperationName () {
			return $this->operationName;
		}

		/**
		 * @return string
		 */
		public function getRelatedModules () {
			return $this->relatedModules;
		}

		/**
		 * @return float
		 */
		public function getResult () {
			return $this->result;
		}

		/**
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @return string
		 */
		public function getSqlFilter () {
			return $this->sqlFilter;
		}

		/**
		 * @return string
		 */
		public function getSqlData () {
			return $this->sqlData;
		}

		/**
		 * @return string
		 */
		public function getTabLabel () {
			return $this->tabLabel;
		}

		/**
		 * @return datetime
		 */
		public function getUpdatedDate () {
			return $this->updatedDate;
		}

		/**
		 * @param CalculationElement $calculation
		 *
		 * @return boolean
		 */
		public function isEqualTo ($calculation) {
			if (
				(empty ($calculation)) ||
				(!($calculation instanceof CalculationElement)) ||
				($this->columnName != $calculation->getColumnName ()) ||
				($this->elementName != $calculation->getElementName ()) ||
				($this->description != $calculation->getDescription ()) ||
				($this->moduleName != $calculation->getModuleName ()) ||
				($this->name != $calculation->getName ()) ||
				($this->operationName != $calculation->getOperationName ()) ||
				($this->sqlFilter != $calculation->getSqlFilter ()) ||
				($this->sqlData != $calculation->getSqlData ())
			) {
				return false;
			} else {
				return true;
			}
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
		 * @return CalculationElement
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
		 * @param string $columnName
		 *
		 * @return CalculationElement
		 */
		public function setColumnName ($columnName) {
			if (is_scalar ($columnName)) {
				$this->columnName = $columnName;
			} else {
				$this->columnName = null;
			}
			return $this;
		}

		/**
		 * @param string $elementName
		 *
		 * @return CalculationElement
		 */
		public function setElementName ($elementName) {
			if (is_scalar ($elementName)) {
				$this->elementName = $elementName;
			} else {
				$this->elementName = null;
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return CalculationElement
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
		 * @param string $fieldlabel
		 *
		 * @return CalculationElement
		 */
		public function setFieldLabel ($fieldlabel) {
			if (is_scalar ($fieldlabel)) {
				$this->fieldlabel = $fieldlabel;
			} else {
				$this->fieldlabel = null;
			}
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return CalculationElement
		 */
		public function setLocked ($locked) {
			if ($locked) {
				$this->locked = 1;
			} else {
				$this->locked = 0;
			}
			return $this;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return CalculationElement
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
		 * @param string $name
		 *
		 * @return CalculationElement
		 */
		public function setName ($name) {
			if (is_scalar ($name)) {
				$this->name = $name;
			} else {
				$this->name = null;
			}
			return $this;
		}

		/**
		 * @param float $result
		 *
		 * @return CalculationElement
		 */
		public function setPeriod ($period){
			$this->period = $period;
			return $this;
		}
		
		/**
		 * @param float $result
		 *
		 * @return CalculationElement
		 */
		public function setPeriodField ($periodField){
			$this->periodField = $periodField;
			return $this;
		}
		
		/**
		 * @param string $operationName
		 *
		 * @return CalculationElement
		 */
		public function setOperationName ($operationName) {
			if (in_array ($operationName, array (self::AVERAGE, self::COUNT, self::MAXIMUN, self::MINIMUN, self::SUM))) {
				$this->operationName = $operationName;
			} else {
				$this->operationName = null;
			}
			return $this;
		}

		/**
		 * @param string $relModules
		 *
		 * @return CalculationElement
		 */
		public function setRelatedModules ($relModules) {
			if (is_scalar ($relModules)) {
				$this->relatedModules = $relModules;
			} else {
				$this->relatedModules = null;
			}
			return $this;
		}

		/**
		 * @param float $result
		 *
		 * @return CalculationElement
		 */
		public function setResult ($result) {
			if ((is_numeric ($result))) {
				$this->result = $result;
			} else {
				$this->result = 0.0;
			}
			return $this;
		}

		/**
		 * @param string $status
		 *
		 * @return CalculationElement
		 */
		public function setStatus ($status) {
			if (in_array ($status, array (self::STATUS_ACTIVE, self::STATUS_INACTIVE))) {
				$this->status = $status;
			} else {
				$this->status = null;
			}
			return $this;
		}

		/**
		 * @param string $sql
		 *
		 * @return CalculationElement
		 */
		public function setSqlFilter ($sql) {
			if (is_scalar ($sql)) {
				$this->sqlFilter = $sql;
			} else {
				$this->sqlFilter = null;
			}
			return $this;
		}

		/**
		 * @param string $sql
		 *
		 * @return CalculationElement
		 */
		public function setSqlData ($sql) {
			if (is_scalar ($sql)) {
				$this->sqlData = $sql;
			} else {
				$this->sqlData = null;
			}
			return $this;
		}

		/**
		 * @param $tabLabel
		 *
		 * @return CalculationElement
		 */
		public function setTabLabel ($tabLabel) {
			if (is_scalar ($tabLabel)) {
				$this->tabLabel = $tabLabel;
			} else {
				$this->tabLabel = null;
			}
			return $this;
		}

		/**
		 * @param datetime $date
		 *
		 * @return CalculationElement
		 */
		public function setUpdatedDate ($date) {
			if ($this->validateDate ($date)) {
				$this->updatedDate = $date;
			} else {
				$this->updatedDate = null;
			}
			return $this;
		}

		/**
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->id,
					$this->columnName,
					$this->elementName,
					$this->description,
					$this->moduleName,
					$this->name,
					$this->operationName,
					$this->status,
					$this->sqlFilter,
					$this->sqlData,
					$this->status,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->columnName,
				$this->elementName,
				$this->description,
				$this->moduleName,
				$this->name,
				$this->operationName,
				$this->status,
				$this->sqlFilter,
				$this->sqlData,
				$this->status,
				) = unserialize ($serialized);
		}

		/**
		 * @throws CalculationElementException
		 */
		public function validate () {
			if (empty ($this->columnName)) {
				throw new CalculationElementException (CalculationElementException::ERROR_CALCULATION_ELEMENT_EMPTY_COLUMNA_NAME);
			} else if (empty ($this->moduleName)) {
				throw new CalculationElementException (CalculationElementException::ERROR_CALCULATION_ELEMENT_EMPTY_MODULE_NAME);
			} else if (empty ($this->name)) {
				throw new CalculationElementException (CalculationElementException::ERROR_CALCULATION_ELEMENT_EMPTY_NAME);
			} else if (empty ($this->operationName)) {
				throw new CalculationElementException (CalculationElementException::ERROR_CALCULATION_ELEMENT_EMPTY_OPERATION_NAME);
			} else if (empty ($this->updatedDate)) {
				throw new CalculationElementException (CalculationElementException::ERROR_CALCULATION_ELEMENT_EMPTY_UPDATED_DATE);
			}
		}

		/**
		 * @return CalculationElement
		 */
		public static function getInstance () {
			return new self ();
		}

	}

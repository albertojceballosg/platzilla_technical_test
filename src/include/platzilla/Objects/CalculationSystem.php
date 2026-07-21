<?php
	require_once ('include/platzilla/Exceptions/CalculationSystemException.php');
	require_once ('include/platzilla/Objects/CalculationSystemInterface.php');

	class CalculationSystem implements CalculationSystemInterface, Serializable {
		/** @var integer */
		private $id;

		/** @var string */
		private $calculatedData;

		/** @var string */
		private $calculationName;

		/** @var string */
		private $description;

		/** @var CalculationEquation[] */
		private $equations;

		/** @var integer */
		private $equationId;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		/** @var @var string */
		private $relatedModules;

		/** @var float */
		private $result;

		/** @var string */
		private $sql;

		/** @var string */
		private $status;

		/** @var string */
		private $updatedDate;

		/**
		 * @param CalculationEquation[] $equations
		 *
		 * @return array
		 */
		private function duplicateFromEquation ($equations) {
			$calculationsEquations = array();
			foreach ($equations as $equation) {
				$calculationsEquations [] = $equation->duplicate ($equation);
			}
			return $calculationsEquations;
		}

		/**
		 * @param CalculationEquation[] $equations
		 * @param CalculationEquation[] $thoseEquations
		 *
		 * @return boolean
		 */
		private function isEquationEqualTo($equations, $thoseEquations) {
			$totalEquations = count ($equations);
			$equals         = false;
			if ($totalEquations != count ($thoseEquations)) {
				return false;
			}

			for ($k = 0; $k < $totalEquations; $k++) {
				if ($equations [$k]->isEqualTo ($thoseEquations [$k])) {
					$equals = true;
					$k = ($totalEquations + 1);
				}
			}
			return $equals;
		}

		/**
		 * @param string $date
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

		public function clearEquation () {
			$this->equations = (array) null;
		}

		/**
		 * @param $calculation
		 */
		public function copyValuesFrom ($calculation) {
			if ((empty ($calculation)) || (!($calculation instanceof CalculationSystem))) {
				return;
			}
			$this->calculatedData  = $calculation->getCalculatedData ();
			$this->calculationName = $calculation->getCalculationName ();
			$this->description     = $calculation->getDescription ();
			$this->equations       = $calculation->getEquation ();
			$this->equationId      = $calculation->getEquationId ();
			$this->locked          = $calculation->isLocked ();
			$this->moduleName      = $calculation->getModuleName ();
			$this->name            = $calculation->getName ();
			$this->relatedModules  = $calculation->getRelatedModules ();
			$this->result          = $calculation->getResult ();
			$this->sql             = $calculation->getSql ();
			$this->status          = $calculation->getStatus ();
			$this->updatedDate     = date ('Y-m-d H:m:s');
		}

		/**
		 * @param integer $newCalculationSystemId
		 *
		 * @return CalculationSystem
		 */
		public function duplicate ($newCalculationSystemId = null) {
			$object = new self ();
			return $object->setId ($newCalculationSystemId)
				->setCalculatedData ($this->calculatedData)
				->setCalculationName ($this->calculationName)
				->setDescription ($this->description)
				->setEquation ($this->duplicateFromEquation ($this->equations))
				->setEquationId ($this->equationId)
				->setLocked ($this->locked)
				->setModuleName ($this->moduleName)
				->setName ($this->name)
				->setRelatedModules ($this->relatedModules)
				->setResult ($this->result)
				->setSql ($this->sql)
				->setStatus ($this->status)
				->setUpdatedDate (date ('Y-m-d H:m:s'));
		}

		/**
		 * @return string
		 */
		public function getCalculatedData () {
			return $this->calculatedData;
		}

		/**
		 * @return string
		 */
		public function getCalculationName () {
			return $this->calculationName;
		}

		/**
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * @return CalculationEquation[]
		 */
		public function getEquation () {
			return $this->equations;
		}

		/**
		 * @return integer
		 */
		public function getEquationId () {
			return $this->equationId;
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
		public function getSql () {
			return $this->sql;
		}

		/**
		 * @return datetime
		 */
		public function getUpdatedDate () {
			return $this->updatedDate;
		}

		/**
		 * @param CalculationSystem $calculation
		 *
		 * @return boolean
		 */
		public function isEqualTo ($calculation) {
			if (
				(empty ($calculation)) ||
				(!($calculation instanceof CalculationSystem)) ||
				($this->calculatedData != $calculation->getCalculatedData ()) ||
				($this->calculationName != $calculation->getCalculationName ()) ||
				($this->description != $calculation->getDescription ()) ||
				($this->moduleName != $calculation->getModuleName ()) ||
				($this->name != $calculation->getName ()) ||
				($this->sql != $calculation->getSql ()) ||
				!$this->isEquationEqualTo ($this->equations, $calculation->getEquation ())
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
		 * @return CalculationSystem
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
		 * @param string $calculatedData
		 *
		 * @return CalculationSystem
		 */
		public function setCalculatedData ($calculatedData) {
			if (is_scalar ($calculatedData)) {
				$this->calculatedData = $calculatedData;
			} else {
				$this->calculatedData = null;
			}
			return $this;
		}

		/**
		 * @param string $calculationName
		 *
		 * @return CalculationSystem
		 */
		public function setCalculationName ($calculationName) {
			if (is_scalar ($calculationName)) {
				$this->calculationName = $calculationName;
			} else {
				$this->calculationName = null;
			}
			return $this;
		}

		/**
		 * @param string $description
		 *
		 * @return CalculationSystem
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
		 * @param CalculationEquation[] $equations
		 *
		 * @return CalculationSystem
		 */
		public function setEquation ($equations) {
			foreach ($equations as $equation) {
				if (($equation == null) || ($equation instanceof CalculationEquation) && (!empty ($equation))) {
					$this->equations [] = $equation;
				}
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return CalculationSystem
		 */
		public function setEquationId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->equationId = $id;
			} else {
				$this->equationId = null;
			}
			return $this;
		}

		/**
		 * @param boolean $locked
		 *
		 * @return CalculationSystem
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
		 * @return CalculationSystem
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
		 * @return CalculationSystem
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
		 * @param string $relModules
		 *
		 * @return CalculationSystem
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
		 * @return CalculationSystem
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
		 * @return CalculationSystem
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
		 * @return CalculationSystem
		 */
		public function setSql ($sql) {
			if (is_scalar ($sql)) {
				$this->sql = $sql;
			} else {
				$this->sql = null;
			}
			return $this;
		}

		/**
		 * @param string $date
		 *
		 * @return CalculationSystem
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
					$this->calculatedData,
					$this->calculationName,
					$this->description,
					$this->equations,
					$this->equationId,
					$this->moduleName,
					$this->status,
					$this->sql,
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
				$this->calculatedData,
				$this->calculationName,
				$this->description,
				$this->equations,
				$this->equationId,
				$this->moduleName,
				$this->status,
				$this->sql,
				$this->status,
				) = unserialize ($serialized);
		}

		/**
		 * @throws CalculationSystemException
		 */
		public function validate () {
			if (empty ($this->moduleName)) {
				throw new CalculationSystemException (CalculationSystemException::ERROR_CALCULATION_SYSTEM_EMPTY_MODULE_NAME);
			} else if (empty ($this->name)) {
				throw new CalculationSystemException (CalculationSystemException::ERROR_CALCULATION_SYSTEM_EMPTY_NAME);
			} else if (empty ($this->updatedDate)) {
				throw new CalculationSystemException (CalculationSystemException::ERROR_CALCULATION_SYSTEM_EMPTY_UPDATED_DATE);
			}
		}

		/**
		 * @return CalculationSystem
		 */
		public static function getInstance () {
			return new self ();
		}

	}

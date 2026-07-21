<?php
	require_once ('include/platzilla/Exceptions/CalculationSystemException.php');
	require_once ('include/platzilla/Objects/CalculationSystemInterface.php');

	class CalculationEquation implements CalculationSystemInterface, Serializable {
		/** @var integer */
		private $id;

		/** @var string */
		private $firstElement;

		/** @var string */
		private $firstElementType;

		/** @var integer */
		private $indexGroup;

		/** @var string */
		private $operationGroup;

		/** @var string */
		private $operation;

		/** @var string */
		private $secondElement;

		/** @var string */
		private $secondElementType;

		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @param CalculationEquation $equation
		 */
		public function copyValuesFrom ($equation) {
			if ((empty ($equation)) || (!($equation instanceof CalculationEquation))) {
				return;
			}
			$this->firstElement = $equation->getFirstElement();
			$this->firstElementType = $equation->getFirstElementType();
			$this->indexGroup = $equation->getIndexGroup();
			$this->operationGroup = $equation->getOperationGroup();
			$this->operation = $equation->getOperation();
			$this->secondElement = $equation->getSecondElement();
			$this->secondElementType = $equation->getSecondElementType();
		}

		/**
		 * @return CalculationEquation
		 */
		public function duplicate ($newCalculationEquationId = null) {
			$object = new self ();
			return $object->setId ($newCalculationEquationId)
				->setFirstElement ($this->firstElement)
				->setFirstElementType ($this->firstElementType)
				->setIndexGroup ($this->indexGroup)
				->setOperationGroup ($this->operationGroup)
				->setOperation ($this->operation)
				->setSecondElement ($this->secondElement)
				->setSecondElementType ($this->secondElementType);
		}

		/**
		 * @return array
		 */
		public function elementsTypes () {
			return array (
				self::ELEMENT,
				self::FIELD,
				self::VALUE,
				self::REFERENCE,
			);
		}

		/**
		 * @return string
		 */
		public function getFirstElement () {
			return $this->firstElement;
		}

		/**
		 * @return string
		 */
		public function getFirstElementType () {
			return $this->firstElementType;
		}

		/**
		 * @return integer
		 */
		public function getIndexGroup () {
			return $this->indexGroup;
		}

		/**
		 * @return string
		 */
		public function getOperationGroup () {
			return $this->operationGroup;
		}

		/**
		 * @return string
		 */
		public function getOperation () {
			return $this->operation;
		}

		/**
		 * @return string
		 */
		public function getSecondElement () {
			return $this->secondElement;
		}

		/**
		 * @return string
		 */
		public function getSecondElementType () {
			return $this->secondElementType;
		}

		/**
		 * @param CalculationEquation $equation
		 *
		 * @return boolean
		 */
		public function isEqualTo ($equation) {
			if(empty ($equation)) {
				return false;
			} else {
				if ($equation->getFirstElementType () != CalculationSystemInterface::ELEMENT) {
					if (($this->firstElement != $equation->getFirstElement()) ||
						($this->firstElementType != $equation->getFirstElementType())
					) {
						return false;
					}
				}
				if ($equation->getSecondElementType () != CalculationSystemInterface::ELEMENT) {
					if (($this->secondElement != $equation->getSecondElement()) ||
						($this->secondElementType != $equation->getSecondElementType())
					) {
						return false;
					}
				}
				if (($this->operationGroup != $equation->getOperationGroup ()) ||
					($this->operation != $equation->getOperation())
				) {
					return false;
				}
			}
			return true;
		}

		/**
		 * @param integer $id
		 *
		 * @return CalculationEquation
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
		 * @param string $firstElement
		 *
		 * @return CalculationEquation
		 */
		public function setFirstElement ($firstElement) {
			if (is_scalar ($firstElement)) {
				$this->firstElement = $firstElement;
			} else {
				$this->firstElement = null;
			}
			return $this;
		}

		/**
		 * @param string $firstElementType
		 *
		 * @return CalculationEquation
		 */
		public function setFirstElementType ($firstElementType) {
			if (in_array ($firstElementType, $this->elementsTypes())) {
				$this->firstElementType = $firstElementType;
			} else {
				$this->firstElementType = null;
			}
			return $this;
		}

		/**
		 * @param $index
		 *
		 * @return CalculationEquation
		 */
		public function setIndexGroup ($index) {
			if ((is_numeric ($index)) && ($index > 0) && (intval ($index) == $index)) {
				$this->indexGroup = $index;
			} else {
				$this->indexGroup = 0;
			}
			return $this;
		}

		/**
		 * @param string $operationGroup
		 *
		 * @return CalculationEquation
		 */
		public function setOperationGroup ($operationGroup) {
			if (is_scalar ($operationGroup)) {
				$this->operationGroup = $operationGroup;
			} else {
				$this->operationGroup = null;
			}
			return $this;
		}

		/**
		 * @param string $operation
		 *
		 * @return CalculationEquation
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
		 * @param string $secondElement
		 *
		 * @return CalculationEquation
		 */
		public function setSecondElement ($secondElement) {
			if (is_scalar ($secondElement)) {
				$this->secondElement = $secondElement;
			} else {
				$this->secondElement = null;
			}
			return $this;
		}

		/**
		 * @param string $secondElementType
		 *
		 * @return CalculationEquation
		 */
		public function setSecondElementType ($secondElementType) {
			if (in_array ($secondElementType, $this->elementsTypes())) {
				$this->secondElementType = $secondElementType;
			} else {
				$this->secondElementType = null;
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
					$this->firstElement,
					$this->firstElementType,
					$this->indexGroup,
					$this->operationGroup,
					$this->operation,
					$this->secondElement,
					$this->secondElementType,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->firstElement,
				$this->firstElementType,
				$this->indexGroup,
				$this->operationGroup,
				$this->operation,
				$this->secondElement,
				$this->secondElementType,
				) = unserialize ($serialized);
		}

		/**
		 * @return CalculationEquation
		 */
		public static function getInstance () {
			return new self ();
		}

	}

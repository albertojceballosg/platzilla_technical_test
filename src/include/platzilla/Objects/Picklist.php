<?php
	require_once ('include/platzilla/Exceptions/PicklistException.php');
	require_once ('include/platzilla/Objects/PicklistValue.php');

	/**
	 * Class Picklist
	 *
	 * Esta clase "Picklist" hace referencia a los campos del tipo "Picklist" o lista desplegable que contiene un Módulo.
	 * La clase está asociada al objeto "Valores Picklist".
	 */
	class Picklist {
		/** @var integer */
		protected $id;

		/** @var string */
		protected $name;

		/** @var PicklistValue[] */
		protected $values;

		/**
		 * Compara si los valores del picklist (lista desplegable) son iguales
		 *
		 * @param PicklistValue[] $values
		 *
		 * @return boolean
		 */
		protected function areValuesEqual ($values) {
			if ((empty ($this->values)) && (empty ($values))) {
				return true;
			} else if (
				(empty ($this->values) !== empty ($values)) ||
				(!is_array ($values)) ||
				(count ($this->values) != count ($values))
			) {
				return false;
			} else {
				foreach ($this->values as $thisValue) {
					$equals = false;
					foreach ($values as $value) {
						if ($value->isEqualTo ($thisValue)) {
							$equals = true;
							break;
						}
					}
					if (!$equals) {
						return false;
					}
				}
				return true;
			}
		}

		/**
		 * Copia los valores/atributos del picklist
		 *
		 * @param PicklistValue[] $sourceValues
		 */
		private function copyPicklistValues ($sourceValues) {
			$values = array ();
			foreach ($sourceValues as $sourceValue) {
				$found = false;
				foreach ($this->values as $targetValue) {
					if ($sourceValue->getValue () != $targetValue->getValue ()) {
						continue;
					} else if ((!$targetValue->isDeleted ()) && (!$targetValue->isEqualTo ($sourceValue))) {
						$targetValue->copyValuesFrom ($sourceValue);
					}
					$values [] = $targetValue;
					$found     = true;
					break;
				}
				if (!$found) {
					$targetValue = $sourceValue->duplicate (null);
					$values []   = $targetValue;
				}
			}
			$this->values = $values;
		}

		/**
		 * Copia los valores/atributos del picklist
		 *
		 * @param Picklist $picklist
		 */
		private function copyPicklistValuesFrom ($picklist) {
			$sourceValues = $picklist->getValues ();
			if ((empty ($sourceValues)) && (empty ($this->values))) {
				return;
			}

			if (empty ($sourceValues)) {
				$this->values = null;
			} else if (empty ($this->values)) {
				$values = array ();
				foreach ($sourceValues as $sourceValue) {
					$values [] = $sourceValue->duplicate (null);
				}
				$this->values = $values;
			} else {
				$this->copyPicklistValues ($sourceValues);
			}
		}

		/**
		 * Obtiene el id del picklist
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener el nombre del picklist
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener el valor del picklist
		 *
		 * @return PicklistValue[]
		 */
		public function getValues () {
			return $this->values;
		}

		/**
		 * Establece el id del picklist
		 *
		 * @param integer $id
		 *
		 * @return Picklist
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece el nombre del picklist
		 *
		 * @param string $name
		 *
		 * @return Picklist
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece los valores tendra el picklist
		 *
		 * @param PicklistValue[] $values
		 *
		 * @return Picklist
		 */
		public function setValues ($values) {
			if ((is_array ($values)) && (!empty ($values))) {
				$this->values = $values;
			} else {
				$this->values = null;
			}
			return $this;
		}

		/**
		 * Copia los valores del picklist desde otro picklist
		 *
		 * @param Picklist $picklist
		 */
		public function copyValuesFrom ($picklist) {
			if ((empty ($picklist)) || (!($picklist instanceof Picklist))) {
				return;
			}

			$this->name = $picklist->getName ();
			$this->copyPicklistValuesFrom ($picklist);
		}

		/**
		 * Duplica los valores del picklist
		 *
		 * @param integer $newPicklistId
		 *
		 * @return Picklist
		 * @throws PicklistException
		 */
		public function duplicate ($newPicklistId) {
			$this->validate ();

			$values = array ();
			foreach ($this->values as $value) {
				$values [] = $value->duplicate (!empty ($newPicklistId) ? $value->getId () : null);
			}

			$object = new self ();
			return $object->setId ($newPicklistId)
				->setName ($this->name)
				->setValues ($values);
		}

		/**
		 * Compara si los atributos/valores de un picklist son iguales a otro
		 *
		 * @param Picklist $picklist
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($picklist, $deepCheck = true) {
			if (
				(empty ($picklist)) ||
				(!($picklist instanceof Picklist)) ||
				($this->name != $picklist->getName ()) ||
				(($deepCheck) && (!$this->areValuesEqual ($picklist->getValues ())))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida que los atributos/valores (Nombre, valores) del picklist esten correctamente
		 *
		 * @throws PicklistException
		 */
		public function validate () {
			if (empty ($this->name)) {
				throw new PicklistException (PicklistException::ERROR_PICKLIST_EMPTY_NAME);
			} else if ((empty ($this->values)) || (!is_array ($this->values))) {
				throw new PicklistException (PicklistException::ERROR_PICKLIST_EMPTY_VALUES);
			} else {
				foreach ($this->values as $value) {
					if (!($value instanceof PicklistValue)) {
						throw new PicklistException (PicklistException::ERROR_PICKLIST_INVALID_VALUE);
					}
				}
			}
		}

		/**
		 * Instanciación de la clase Picklist. Se obtiene un objeto Picklists con los valores de la clase
		 *
		 * @return Picklist
		 */
		public static function getInstance () {
			return new self ();
		}

	}

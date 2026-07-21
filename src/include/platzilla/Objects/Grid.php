<?php
	require_once ('include/platzilla/Exceptions/GridException.php');
	require_once ('include/platzilla/Objects/GridField.php');

	/**
	 * Class Grid
	 *
	 *  La clase "Grid" hace referencia a los campos del tipo "Grid" o tablas inteligentes que contiene un Módulo.
	 *  La clase está asociada al objeto "Campo Grid"
	 */
	class Grid {
		/** @var GridField[] */
		private $fields;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		/**
		 * Compara si los grid son iguales
		 *
		 * @param GridField[] $fields
		 *
		 * @return boolean
		 */
		private function areFieldsEqual ($fields) {
			if ((empty ($this->fields)) && (empty ($fields))) {
				return true;
			} else if (
				(empty ($this->fields) !== empty ($fields)) ||
				(!is_array ($fields)) ||
				(count ($this->fields) != count ($fields))
			) {
				return false;
			} else {
				foreach ($this->fields as $thisField) {
					$equals = false;
					foreach ($fields as $field) {
						if ($field->isEqualTo ($thisField)) {
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
		 * Realiza copia de la estructura del grid
		 *
		 * @param GridField[] $sourceFields
		 */
		private function copyFields ($sourceFields) {
			$fields = array ();
			foreach ($sourceFields as $sourceField) {
				$found = false;
				foreach ($this->fields as $targetField) {
					if ($sourceField->getName () != $targetField->getName ()) {
						continue;
					} else if (!$targetField->isEqualTo ($sourceField)) {
						$targetField->copyValuesFrom ($sourceField);
					}
					$fields [] = $targetField;
					$found     = true;
					break;
				}
				if (!$found) {
					$fields [] = $sourceField->duplicate ();
				}
			}
			$this->fields = $fields;
		}

		/**
		 * Realiza copia copia de la estrucutura del grid desde otro
		 *
		 * @param Grid $grid
		 */
		private function copyFieldsFrom ($grid) {
			$sourceFields = $grid->getFields ();
			if ((empty ($sourceFields)) && (empty ($this->fields))) {
				return;
			}

			if (empty ($sourceFields)) {
				$this->fields = null;
			} else if (empty ($this->fields)) {
				$fields = array ();
				foreach ($sourceFields as $sourceField) {
					$fields [] = $sourceField->duplicate ();
				}
				$this->fields = $fields;
			} else {
				$this->copyFields ($sourceFields);
			}
		}

		/**
		 * Obtiene el nombre del campo grid
		 *
		 * @return GridField[]
		 */
		public function getFields () {
			return $this->fields;
		}

		/**
		 * Obtiene el nombre del modulo asociado al campo grid
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Obtiene el nombre del campo grid
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Establece el campo grid
		 *
		 * @param GridField[] $fields
		 *
		 * @return Grid
		 */
		public function setFields ($fields) {
			$this->fields = $fields;
			return $this;
		}

		/**
		 * Establece el nombre del modulo asociado al campo grid
		 *
		 * @param string $moduleName
		 *
		 * @return Grid
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece el nombre del campo grid
		 *
		 * @param string $name
		 *
		 * @return Grid
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Copia el campo grid desde otro
		 *
		 * @param Grid $grid
		 */
		public function copyValuesFrom ($grid) {
			if ((empty ($grid)) || (!($grid instanceof Grid))) {
				return;
			}

			$this->moduleName = $grid->getModuleName ();
			$this->name       = $grid->getName ();
			$this->copyFieldsFrom ($grid);
		}

		/**
		 * Duplica el grid
		 *
		 * @return Grid
		 * @throws GridException
		 */
		public function duplicate () {
			$this->validate ();

			$fields = array ();
			foreach ($this->fields as $field) {
				$fields [] = $field->duplicate ();
			}

			$object = new self ();
			return $object->setFields ($fields)
				->setModuleName ($this->moduleName)
				->setName ($this->name);
		}

		/**
		 * Compara si el grid es igual a otro
		 *
		 * @param Grid $grid
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($grid, $deepCheck = true) {
			if (
				(empty ($grid)) ||
				(!($grid instanceof Grid)) ||
				($this->moduleName != $grid->getModuleName ()) ||
				($this->name != $grid->getName ()) ||
				(($deepCheck) && (!$this->areFieldsEqual ($grid->getFields ())))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida si los valores/atributos (nombre del grid y el nombre del modulo asociado) del campo grid estan definidos
		 *
		 * @throws GridException
		 */
		public function validate () {
			if (empty ($this->name)) {
				throw new GridException (GridException::ERROR_GRID_EMPTY_NAME);
			} else if (empty ($this->moduleName)) {
				throw new GridException (GridException::ERROR_GRID_EMPTY_MODULE_NAME);
			}
		}

		/**
		 * Instanciación de la clase Grid. Se obtiene un objeto Grid con los atributos de la clase
		 *
		 * @return Grid
		 */
		public static function getInstance () {
			return new self ();
		}

	}

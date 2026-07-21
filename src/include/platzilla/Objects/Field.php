<?php
	require_once ('include/platzilla/Exceptions/FieldException.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Objects/FieldDependency.php');
	require_once ('include/platzilla/Objects/FieldModuleReference.php');
	require_once ('include/platzilla/Objects/FieldValidation.php');
	require_once ('include/platzilla/Objects/Grid.php');
	require_once ('include/platzilla/Objects/Picklist.php');
	require_once ('include/platzilla/Objects/Pipeline.php');

	/**
	 * Class Field
	 *
	 * En esta clase se define el objeto "Campo" el cual hace referencia a los campos que componen un Bloque.
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
	 * @codingStandardsIgnoreEnd
	 */
	class Field implements FieldInterface {

		/** @var integer */
		private $id;
		
		/** @var array|null */
		private $appField;

		/** @var integer */
		private $blockId;

		/** @var string */
		private $calculationName;

		/** @var string */
		private $columnName;

		/** @var string */
		private $dataType;

		/** @var string */
		private $defaultValue;

		/** @var boolean */
		private $deleted;

		/** @var FieldDependency[] */
		private $dependencies;

		/** @var integer */
		private $displayType;

		/** @var integer */
		private $generatedType;

		/** @var Grid */
		private $grid;

		/** @var string */
		private $label;

		/** @var integer */
		private $length;

		/** @var boolean */
		private $locked;

		/** @var boolean */
		private $mandatory;

		/** @var integer */
		private $massEditable;

		/** @var string */
		private $moduleName;

		/** @var FieldModuleReference[] */
		private $moduleReferences;

		/** @var string */
		private $name;

		/** @var Picklist */
		private $picklist;

		/** @var Pipeline */
		private $pipeline;

		/** @var integer */
		private $precision;

		/** @var integer */
		private $presence;

		/** @var integer */
		private $quickCreate;

		/** @var integer */
		private $quickCreateSequence;

		/** @var integer */
		private $readOnly;

		/** @var integer */
		private $sequence;

		/** @var string */
		private $tableName;

		/** @var integer */
		private $uiType;

		/** @var FieldValidation[] */
		private $validations;

		/**
		 * Crea una instancia de la clase. Si se suministra un string como los que genera vTiger para la columna typeofdata, inicializa el tipo de datos, la obligatoriedad
		 * del campo, la longitud y la precisión.
		 *
		 * @param string $typeOfData
		 */
		public function __construct ($typeOfData = '') {
			$typeOfData      = explode ('~', $typeOfData);
			$this->dataType  = $this->getDataTypeFromTypeOfData ($typeOfData);
			$this->deleted   = false;
			$this->length    = $this->getLengthFromTypeOfData ($typeOfData);
			$this->locked    = false;
			$this->mandatory = $this->getMandatoryFromTypeOfData ($typeOfData);
			$this->precision = $this->getPrecisionFromTypeOfData ($typeOfData);
		}

		/**
		 * Compara si son iguales las dependencias de los campos
		 *
		 * @param FieldDependency[] $dependencies
		 *
		 * @return boolean
		 */
		private function areDependenciesEqual ($dependencies) {
			if ((empty ($this->dependencies)) && (empty ($dependencies))) {
				return true;
			} else if (
				(empty ($this->dependencies) !== empty ($dependencies)) ||
				(!is_array ($dependencies)) ||
				(count ($this->dependencies) != count ($dependencies))
			) {
				return false;
			} else {
				foreach ($this->dependencies as $thisDependency) {
					$equals = false;
					foreach ($dependencies as $dependency) {
						if ($thisDependency->isEqualTo ($dependency)) {
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
		 * Compara si son iguales los grids
		 *
		 * @param Grid $grid
		 *
		 * @return boolean
		 */
		private function areGridsEqual ($grid) {
			if ((empty ($this->grid)) && (empty ($grid))) {
				return true;
			} else if (empty ($this->grid) !== empty ($grid)) {
				return false;
			} else {
				return $this->grid->isEqualTo ($grid);
			}
		}

		/**
		 * Compara si son iguales las referencias de los modulos
		 *
		 * @param FieldModuleReference[] $references
		 *
		 * @return boolean
		 */
		private function areModuleReferencesEqual ($references) {
			if ((empty ($this->moduleReferences)) && (empty ($references))) {
				return true;
			} else if (
				(empty ($this->moduleReferences) !== empty ($references)) ||
				(!is_array ($references)) ||
				(count ($this->moduleReferences) != count ($references))
			) {
				return false;
			} else {
				foreach ($this->moduleReferences as $thisReference) {
					$equals = false;
					foreach ($references as $reference) {
						if ($thisReference->isEqualTo ($reference)) {
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
		 * Compara si son iguales los campos del tipo lista desplegables
		 *
		 * @param Picklist $picklist
		 *
		 * @return boolean
		 */
		private function arePicklistsEqual ($picklist) {
			if ((empty ($this->picklist)) && (empty ($picklist))) {
				return true;
			} else if (empty ($this->picklist) !== empty ($picklist)) {
				return false;
			} else {
				return $this->picklist->isEqualTo ($picklist);
			}
		}

		/**
		 * Compara sí son iguales las fuentes de informacion para los campos
		 *
		 * @param Pipeline $pipeline
		 *
		 * @return boolean
		 */
		private function arePipelinesEqual ($pipeline) {
			if ((empty ($this->pipeline)) && (empty ($pipeline))) {
				return true;
			} else if (empty ($this->pipeline) !== empty ($pipeline)) {
				return false;
			} else {
				return $this->pipeline->isEqualTo ($pipeline);
			}
		}

		/**
		 * Compara sí son iguales las validaciones para los campos
		 *
		 * @param FieldValidation[] $validations
		 *
		 * @return boolean
		 */
		private function areValidationsEqual ($validations) {
			if ((empty ($this->validations)) && (empty ($validations))) {
				return true;
			} else if (
				(empty ($this->validations) !== empty ($validations)) ||
				(!is_array ($validations)) ||
				(count ($this->validations) != count ($validations))
			) {
				return false;
			} else {
				foreach ($this->validations as $thisValidation) {
					$equals = false;
					foreach ($validations as $validation) {
						if ($thisValidation->isEqualTo ($validation)) {
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
		 * Permite cambiar el nombre del modulo donde se tienen los campos
		 *
		 * @param FieldDependency[]|FieldModuleReference[]|FieldValidation[]|Grid|Pipeline $elements
		 * @param string $oldModuleName
		 * @param string $newModuleName
		 */
		private function changeModuleName ($elements, $oldModuleName, $newModuleName) {
			if ((empty ($elements)) || ($oldModuleName == $newModuleName)) {
				return;
			}

			if (!is_array ($elements)) {
				$elements = array ($elements);
			}

			$n = count ($elements);
			for ($i = 0; $i < $n; $i++) {
				if (
					(is_object ($elements [ $i ])) &&
					(is_callable (array ($elements [ $i ], 'getModuleName'))) &&
					(is_callable (array ($elements [ $i ], 'setModuleName'))) &&
					($oldModuleName == $elements [ $i ]->getModuleName ())
				) {
					$elements [ $i ]->setModuleName ($newModuleName);
				}
			}
		}

		/**
		 * Realiza copia de las dependencias se han establecido en los campos
		 *
		 * @param FieldDependency[] $sourceDependencies
		 */
		private function copyDependencies ($sourceDependencies) {
			$dependencies = array ();
			foreach ($sourceDependencies as $sourceDependency) {
				$found = false;
				foreach ($this->dependencies as $targetDependency) {
					if ($targetDependency->isEqualTo ($sourceDependency)) {
						$dependencies [] = $targetDependency;
						$found           = true;
						break;
					}
				}
				if (!$found) {
					$dependencies [] = $sourceDependency->duplicate ();
				}
			}
			$this->dependencies = $dependencies;
		}

		/**
		 * Realiza copia de las dependencias se han establecido en los campos desde otras fuentes
		 *
		 * @param Field $field
		 */
		private function copyDependenciesFrom ($field) {
			$sourceDependencies = $field->getDependencies ();
			if ((empty ($sourceDependencies)) && (empty ($this->dependencies))) {
				return;
			}

			if (empty ($sourceDependencies)) {
				$this->dependencies = null;
			} else if (empty ($this->dependencies)) {
				$dependencies = array ();
				foreach ($sourceDependencies as $sourceDependency) {
					$dependencies [] = $sourceDependency->duplicate ();
				}
				$this->dependencies = $dependencies;
			} else {
				$this->copyDependencies ($sourceDependencies);
			}
		}

		/**
		 * Realiza copia de los grids desde otros
		 *
		 * @param Field $field
		 */
		private function copyGridFrom ($field) {
			$sourceGrid = $field->getGrid ();
			if ((empty ($sourceGrid)) && (empty ($this->grid))) {
				return;
			}

			if (empty ($sourceGrid)) {
				$this->grid = null;
			} else if (empty ($this->grid)) {
				$this->grid = $sourceGrid->duplicate ();
			} else {
				$this->grid->copyValuesFrom ($sourceGrid);
			}
		}

		/**
		 * Realiza copia de las referencias del modulo
		 *
		 * @param FieldModuleReference[] $sourceReferences
		 */
		private function copyModuleReferences ($sourceReferences) {
			$references = array ();
			foreach ($sourceReferences as $sourceReference) {
				$found = false;
				foreach ($this->moduleReferences as $targetReference) {
					if ($targetReference->isEqualTo ($sourceReference)) {
						$references [] = $targetReference;
						$found         = true;
						break;
					}
				}
				if (!$found) {
					$references [] = $sourceReference->duplicate ();
				}
			}
			$this->moduleReferences = $references;
		}

		/**
		 * Realiza copia de las referencias del modulo desde otras fuentes
		 *
		 * @param Field $field
		 */
		private function copyModuleReferencesFrom ($field) {
			$sourceReferences = $field->getModuleReferences ();
			if ((empty ($sourceReferences)) && (empty ($this->moduleReferences))) {
				return;
			}

			if (empty ($sourceReferences)) {
				$this->moduleReferences = null;
			} else if (empty ($this->moduleReferences)) {
				$references = array ();
				foreach ($sourceReferences as $sourceReference) {
					$references [] = $sourceReference->duplicate ();
				}
				$this->moduleReferences = $references;
			} else {
				$this->copyModuleReferences ($sourceReferences);
			}
		}

		/**
		 * Realiza copia de campos tipo lista desplegable desde otras listas desplegables
		 *
		 * @param Field $field
		 */
		private function copyPicklistFrom ($field) {
			$sourcePicklist = $field->getPicklist ();
			if ((empty ($sourcePicklist)) && (empty ($this->picklist))) {
				return;
			}

			if (empty ($sourcePicklist)) {
				$this->picklist = null;
			} else if (empty ($this->picklist)) {
				$this->picklist = $sourcePicklist->duplicate (null);
			} else {
				$this->picklist->copyValuesFrom ($sourcePicklist);
			}
		}

		/**
		 * Realiza copia de las fuentes de informacion para los campos
		 *
		 * @param Field $field
		 */
		private function copyPipelineFrom ($field) {
			$sourcePipeline = $field->getPipeline ();
			if ((empty ($sourcePipeline)) && (empty ($this->pipeline))) {
				return;
			}

			if (empty ($sourcePipeline)) {
				$this->pipeline = null;
			} else if (empty ($this->pipeline)) {
				$this->pipeline = $sourcePipeline->duplicate ();
			} else {
				$this->pipeline->copyValuesFrom ($sourcePipeline);
			}
		}

		/**
		 * Realiza copia de las validaciones de los campos
		 *
		 * @param FieldValidation[] $sourceValidations
		 */
		private function copyValidations ($sourceValidations) {
			$validations = array ();
			foreach ($sourceValidations as $sourceValidation) {
				$found = false;
				foreach ($this->validations as $targetValidation) {
					if ($targetValidation->isEqualTo ($sourceValidation)) {
						$validations [] = $targetValidation;
						$found          = true;
						break;
					}
				}
				if (!$found) {
					$validations [] = $sourceValidation->duplicate ();
				}
			}
			$this->validations = $validations;
		}

		/**
		 * Realiza copia de las validaciones de los campos desde otros campos
		 *
		 * @param Field $field
		 */
		private function copyValidationsFrom ($field) {
			$sourceValidations = $field->getValidations ();
			if ((empty ($sourceValidations)) && (empty ($this->validations))) {
				return;
			}

			if (empty ($sourceValidations)) {
				$this->validations = null;
			} else if (empty ($this->validations)) {
				$validations = array ();
				foreach ($sourceValidations as $sourceValidation) {
					$validations [] = $sourceValidation->duplicate ();
				}
				$this->validations = $validations;
			} else {
				$this->copyValidations ($sourceValidations);
			}
		}

		/**
		 * Realiza el duplicado de las dependencias de los campos
		 *
		 * @return FieldDependency[]|null
		 */
		private function duplicateDependencies () {
			if (empty ($this->dependencies)) {
				return null;
			}

			$dependencies = array ();
			foreach ($this->dependencies as $dependency) {
				$dependencies [] = $dependency->duplicate ();
			}
			return $dependencies;
		}

		/**
		 * Para duplicar las referencias del modulo
		 *
		 * @return FieldModuleReference[]|null
		 */
		private function duplicateModuleReferences () {
			if (empty ($this->moduleReferences)) {
				return null;
			}

			$references = array ();
			foreach ($this->moduleReferences as $reference) {
				$references [] = $reference->duplicate ();
			}

			return $references;
		}

		/**
		 * Para realizar duplicacion de las validaciones del campo
		 *
		 * @return FieldValidation[]|null
		 */
		private function duplicateValidations () {
			if (empty ($this->validations)) {
				return null;
			}

			$validations = array ();
			foreach ($this->validations as $validation) {
				$validations [] = $validation->duplicate ();
			}

			return $validations;
		}

		/**
		 * Para obtener el tipo de dato asociado para los tipos de campos
		 *
		 * @param string $typeOfData
		 *
		 * @return string|null
		 */
		private function getDataTypeFromTypeOfData ($typeOfData) {
			$dataType = strtoupper ($typeOfData [0]);
			if (in_array ($dataType, array (self::DATA_TYPE_CHECKBOX, self::DATA_TYPE_DATE, self::DATA_TYPE_DATETIME, self::DATA_TYPE_EMAIL, self::DATA_TYPE_GRID, self::DATA_TYPE_INTEGER, self::DATA_TYPE_NEGATIVE_NUMBER, self::DATA_TYPE_NUMBER, self::DATA_TYPE_PASSWORD, self::DATA_TYPE_TIME, self::DATA_TYPE_VARCHAR))) {
				return $dataType;
			} else {
				return null;
			}
		}

		/**
		 * Para obtener el tipo de datos asociado a nivel de Base de Datos para los tipos de campos
		 *
		 * @param integer $uiType Tipo de dato asociado a nivel de BD para el campo
		 *
		 * @return string
		 */
		private function getDataTypeFromUiType ($uiType) {
			if (empty ($uiType)) {
				$dataType = null;
			} else if ($uiType == self::UI_TYPE_CHECKBOX) {
				$dataType = self::DATA_TYPE_CHECKBOX;
			} else if (in_array ($uiType, array (self::UI_TYPE_ATTACHMENTS, self::UI_TYPE_CODE, self::UI_TYPE_IMAGE_DISPLAY, self::UI_TYPE_MODIFIED_BY, self::UI_TYPE_MODULE_RECORDS, self::UI_TYPE_MODULE_REFERENCE, self::UI_TYPE_MULTI_SELECT, self::UI_TYPE_OWNER, self::UI_TYPE_PHONE, self::UI_TYPE_PICKLIST, self::UI_TYPE_PIPELINE, self::UI_TYPE_SKYPE, self::UI_TYPE_TEXT, self::UI_TYPE_TEXTAREA, self::UI_TYPE_URL, self::UI_TYPE_VIDEO))) {
				$dataType = self::DATA_TYPE_VARCHAR;
			} else if (in_array ($uiType, array (self::UI_TYPE_CREATED_TIME, self::UI_TYPE_DATETIME))) {
				$dataType = self::DATA_TYPE_DATETIME;
			} else if (in_array ($uiType, array (self::UI_TYPE_CURRENCY, self::UI_TYPE_PERCENTAGE, self::UI_TYPE_CALCULATED_LINK))) {
				$dataType = self::DATA_TYPE_NUMBER;
			} else if (in_array ($uiType, array (self::UI_TYPE_DATE))) {
				$dataType = self::DATA_TYPE_DATE;
			} else if ($uiType == self::UI_TYPE_EMAIL) {
				$dataType = self::DATA_TYPE_EMAIL;
			} else if ($uiType == self::UI_TYPE_GRID || $uiType == self::UI_TYPE_TABLE_FIELD) {
				$dataType = self::DATA_TYPE_GRID;
			} else if (in_array ($uiType, array (self::UI_TYPE_NUMBER))) {
				$dataType = self::DATA_TYPE_NEGATIVE_NUMBER;
			} else if (in_array ($uiType, array (self::UI_TYPE_TIME))) {
				$dataType = self::DATA_TYPE_TIME;
			} else {
				$dataType = self::DATA_TYPE_VARCHAR;
			}
			return $dataType;
		}

		/**
		 * Para obtener la longitud del tipo de datos asociado al campo
		 *
		 * @param string $typeOfData
		 *
		 * @return integer|null Numero entero que indica el tamaño de la longitud para el tipo de dato del campo
		 */
		private function getLengthFromTypeOfData ($typeOfData) {
			$dataType = strtoupper ($typeOfData [0]);
			if ($dataType == self::DATA_TYPE_CHECKBOX) {
				$length = 3;
			} else if ($dataType == self::DATA_TYPE_EMAIL) {
				$length = 50;
			} else if ($dataType == self::DATA_TYPE_GRID) {
				$length = 100;
			} else if ($dataType == self::DATA_TYPE_INTEGER) {
				$length = 19;
			} else if (in_array ($dataType, array (self::DATA_TYPE_NEGATIVE_NUMBER, self::DATA_TYPE_NUMBER))) {
				$dummy  = isset ($typeOfData [2]) ? explode (',', $typeOfData [2]) : array (10, 2);
				$length = (!empty ($dummy [0])) && (is_numeric ($dummy [0])) ? intval ($dummy [0]) : 10;
			} else if ($dataType == self::DATA_TYPE_VARCHAR) {
				$length = (isset ($typeOfData [3])) && (is_numeric ($typeOfData [3])) ? intval ($typeOfData [3]) : 255;
			} else {
				$length = null;
			}
			return $length;
		}

		/**
		 * Para obtener la longitud del tipo de datos asociado a nivel de Base de Datos para los tipos de campos
		 *
		 * @param integer $uiType
		 * @param integer $length
		 *
		 * @return integer Numero entero que indica el tamaño de la longitud en BD para el tipo de dato del campo
		 */
		private function getLengthFromUiType ($uiType, $length) {
			if (empty ($uiType)) {
				$length = null;
			} else if ($uiType == self::UI_TYPE_CHECKBOX) {
				$length = 3;
			} else if ($uiType == self::UI_TYPE_PHONE) {
				$length = 30;
			} else if ($uiType == self::UI_TYPE_EMAIL) {
				$length = 50;
			} else if (in_array ($uiType, array (self::UI_TYPE_CODE, self::UI_TYPE_TEXT, self::UI_TYPE_MODULE_REFERENCE))) {
				$length = (!empty ($length)) && (is_numeric ($length)) ? intval ($length) : 255;
			} else if (in_array ($uiType, array (self::UI_TYPE_CURRENCY, self::UI_TYPE_NUMBER, self::UI_TYPE_PERCENTAGE, self::UI_TYPE_CALCULATED_LINK))) {
				$length = (!empty ($length)) && (is_numeric ($length)) ? intval ($length) : 10;
			} else if (in_array ($uiType, array (self::UI_TYPE_CREATED_TIME, self::UI_TYPE_DATE, self::UI_TYPE_DATETIME, self::UI_TYPE_GRID, self::UI_TYPE_IMAGE_DISPLAY, self::UI_TYPE_MODIFIED_BY, self::UI_TYPE_MODULE_RECORDS, self::UI_TYPE_MULTI_SELECT, self::UI_TYPE_OWNER, self::UI_TYPE_TEXTAREA, self::UI_TYPE_TIME, self::UI_TYPE_TABLE_FIELD))) {
				$length = null;
			} else {
				$length = 255;
			}
			return $length;
		}

		/**
		 * Obtiene si el tipo de dato del campo es obligatorio
		 *
		 * @param string $typeOfData
		 *
		 * @return boolean <code>true</code> si el tipo de dato es obligatorio. <code>false</code> caso contrario
		 */
		private function getMandatoryFromTypeOfData ($typeOfData) {
			if ((isset ($typeOfData [1])) && (strtoupper ($typeOfData [1] == 'M'))) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Obtiene si el tipo de dato a nivel de base de dato del campo es obligatorio
		 *
		 * @param integer $uiType
		 * @param boolean $mandatory
		 *
		 * @return boolean <code>true</code> si el tipo de dato a nivel de BD es obligatorio. <code>false</code> caso contrario
		 */
		private function getMandatoryFromUiType ($uiType, $mandatory) {
			if ($uiType == self::UI_TYPE_OWNER) {
				return true;
			} else {
				return $mandatory;
			}
		}

		/**
		 * Obtiene la precision del tipo de datos del campo
		 *
		 * @param string $typeOfData Tipo de dato del campo
		 *
		 * @return integer|null
		 */
		private function getPrecisionFromTypeOfData ($typeOfData) {
			$dataType = strtoupper ($typeOfData [0]);
			if (!in_array ($dataType, array (self::DATA_TYPE_NEGATIVE_NUMBER, self::DATA_TYPE_NUMBER))) {
				return null;
			}

			$dummy = isset ($typeOfData [2]) ? explode (',', $typeOfData [2]) : array (10, 2);
			return (isset ($dummy [1])) && (is_numeric ($dummy [1])) ? intval ($dummy [1]) : 2;
		}

		/**
		 * Obtiene la precision del tipo de datos del campo a nivel de la BD
		 *
		 * @param integer $uiType
		 * @param integer $precision
		 *
		 * @return integer|null
		 */
		private function getPrecisionFromUiType ($uiType, $precision) {
			if (in_array ($uiType, array (self::UI_TYPE_CURRENCY, self::UI_TYPE_NUMBER, self::UI_TYPE_PERCENTAGE))) {
				return ($precision !== null) && (is_numeric ($precision)) ? intval ($precision) : 2;
			} else if ($uiType == self::UI_TYPE_CALCULATED_LINK) {
				return 2;
			} else {
				return null;
			}
		}

		/**
		 * Valida si es un grid
		 *
		 * @throws FieldException
		 */
		private function validateGrid () {
			if ($this->uiType != self::UI_TYPE_GRID) {
				return;
			}
		}

		/**
		 * Valida las propiedades obligatorias del campo
		 *
		 * @throws FieldException
		 */
		private function validateMandatoryProperties () {
			if (empty ($this->blockId)) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_BLOCK_ID);
			} else if (empty ($this->columnName)) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_COLUMN_NAME);
			} else if (empty ($this->label)) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_LABEL);
			} else if (empty ($this->moduleName)) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_MODULE_NAME);
			} else if (empty ($this->name)) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_NAME);
			} else if (empty ($this->uiType)) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_UI_TYPE);
			} else if (empty ($this->dataType)) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_DATA_TYPE);
			}
		}

		/**
		 * Valida las referencias asociadas del modulo
		 *
		 * @throws FieldException
		 */
		private function validateModuleReferences () {
			if ($this->uiType != self::UI_TYPE_MODULE_REFERENCE) {
				return;
			}
			if ((empty ($this->moduleReferences)) || (!is_array ($this->moduleReferences))) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_MODULE_REFERENCE);
			}

			foreach ($this->moduleReferences as $reference) {
				if (!($reference instanceof FieldModuleReference)) {
					throw new FieldException (FieldException::ERROR_FIELD_INVALID_MODULE_REFERENCE);
				} else {
					$reference->validate ();
				}
			}
		}

		/**
		 * Valida el tipo de dato a nivel de BD para el campo lista desplegable y lista multiseleccion
		 *
		 * @throws FieldException
		 */
		private function validatePicklist () {
			if (!in_array ($this->uiType, array (self::UI_TYPE_MULTI_SELECT, self::UI_TYPE_PICKLIST))) {
				return;
			}
			if ((empty ($this->picklist)) || (!($this->picklist instanceof Picklist))) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_PICKLIST);
			}
			$this->picklist->validate ();
		}

		/**
		 * Valida la fuente de informacion del campo
		 *
		 * @throws FieldException
		 */
		private function validatePipeline () {
			if (!in_array ($this->uiType, array (self::UI_TYPE_PIPELINE))) {
				return;
			}
			if ((empty ($this->pipeline)) || (!($this->pipeline instanceof Pipeline))) {
				throw new FieldException (FieldException::ERROR_FIELD_EMPTY_PIPELINE);
			}
			$this->pipeline->validate ();
		}

		/**
		 * Valida la longitud de las propiedades del campo
		 *
		 * @throws FieldException
		 */
		private function validatePropertiesLength () {
			if (strlen ($this->columnName) > 30) {
				throw new FieldException (FieldException::ERROR_FIELD_COLUMN_NAME_TOO_LONG);
			} else if (strlen ($this->name) > 50) {
				throw new FieldException (FieldException::ERROR_FIELD_NAME_TOO_LONG);
			} else if (strlen ($this->tableName) > 50) {
				throw new FieldException (FieldException::ERROR_FIELD_TABLE_NAME_TOO_LONG);
			}
		}

		/**
		 * Para obtener el id del campo
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return array
		 */
		public function getAppField () {
			return $this->appField;
		}
		
		/**
		 * Para obtener el ID del bloque donde estara el campo
		 *
		 * @return integer
		 */
		public function getBlockId () {
			return $this->blockId;
		}

		/**
		 * Para obtener el nombre del calculo
		 *
		 * @return string
		 */
		public function getCalculationName () {
			return $this->calculationName;
		}

		/**
		 * Para obtener el nombre de la columna
		 *
		 * @return string
		 */
		public function getColumnName () {
			return $this->columnName;
		}

		/**
		 * Para obtener el tipo de dato del campo
		 *
		 * @return integer
		 */
		public function getDataType () {
			return $this->dataType;
		}

		/**
		 * Para obtener el valor por defecto tendra el campo
		 *
		 * @return string
		 */
		public function getDefaultValue () {
			return $this->defaultValue;
		}

		/**
		 * Para obtener las dependencias del campo
		 *
		 * @return FieldDependency[]
		 */
		public function getDependencies () {
			return $this->dependencies;
		}

		/**
		 * Para obtener el valor del campo por pantalla
		 *
		 * @return integer
		 */
		public function getDisplayType () {
			return $this->displayType;
		}

		/**
		 * Para obtener el tipo de campo generado
		 *
		 * @return integer
		 */
		public function getGeneratedType () {
			return $this->generatedType;
		}

		/**
		 * Para obtener el campo grid
		 *
		 * @return grid
		 */
		public function getGrid () {
			return $this->grid;
		}

		/**
		 * Para obtener la etiqueta del campo
		 *
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
		}

		/**
		 * Para obtener la longitud del campo
		 *
		 * @return integer
		 */
		public function getLength () {
			return $this->length;
		}

		/**
		 * Para obtener los campos editables
		 *
		 * @return integer
		 */
		public function getMassEditable () {
			return $this->massEditable;
		}

		/**
		 * Para obtener el nombre del modulo
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener las referencias del modulo
		 *
		 * @return FieldModuleReference[]
		 */
		public function getModuleReferences () {
			return $this->moduleReferences;
		}

		/**
		 * Para obtener el nombre del campo
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener los campos del tipo lista desplegable
		 *
		 * @return Picklist
		 */
		public function getPicklist () {
			return $this->picklist;
		}

		/**
		 * Para obtener las fuentes de informacion del campo
		 *
		 * @return Pipeline
		 */
		public function getPipeline () {
			return $this->pipeline;
		}

		/**
		 * Para obtener la precision del campo
		 *
		 * @return integer
		 */
		public function getPrecision () {
			return $this->precision;
		}

		/**
		 * Para obtener la visibilidad del campo
		 *
		 * @return integer
		 */
		public function getPresence () {
			return $this->presence;
		}

		/**
		 * Para obtener la creacion rapida del campo
		 *
		 * @return integer
		 */
		public function getQuickCreate () {
			return $this->quickCreate;
		}

		/**
		 * Para obtener la secuencia de la creacion rapida del campo
		 *
		 * @return integer
		 */
		public function getQuickCreateSequence () {
			return $this->quickCreateSequence;
		}

		/**
		 * Para obtener el atributo ReadOnly del campo
		 *
		 * @return integer
		 */
		public function getReadOnly () {
			return $this->readOnly;
		}

		/**
		 * Para obtener la secuencia del campo
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener un SQL con los atributos del tipo de datos del campo: uiType, longitud y precision
		 *
		 * @return string
		 */
		public function getSqlDataType () {
			return self::calculateSqlDataType ($this->uiType, $this->length, $this->precision);
		}

		/**
		 * Para obtener el nombre en la tabla del campo
		 *
		 * @return string
		 */
		public function getTableName () {
			return $this->tableName;
		}

		/**
		 * Para obtener los atributos del tipo de dato del campo uiType, longitud, precision y obligatoriedad
		 *
		 * @return string
		 */
		public function getTypeOfData () {
			return self::calculateTypeOfData ($this->uiType, $this->length, $this->precision, $this->mandatory);
		}

		/**
		 * Para obtener el tipo de dato a nivel de la BD del campo
		 *
		 * @return integer
		 */
		public function getUiType () {
			return $this->uiType;
		}

		/**
		 * Para obtener las validaciones que tiene el campo
		 *
		 * @return FieldValidation[]
		 */
		public function getValidations () {
			return $this->validations;
		}

		/**
		 * Para realizar el borrado del campo
		 *
		 * @return boolean <code>true</code> si el campo fue borrado. <code>false</code> caso contrario
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * Obtiene el valor de la bandera que controla si el campo puede bloquearse o no
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Indica si el campo es obligatorio
		 *
		 * @return boolean
		 */
		public function isMandatory () {
			return $this->mandatory;
		}

		/**
		 * Establece el id del campo
		 *
		 * @param integer $id
		 *
		 * @return Field
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param $appField
		 *
		 * @return Field
		 */
		public function setAppField ($appField) {
			$this->appField = $appField;
			return $this;
		}

		/**
		 * Establece el id del bloque que contendra el campo
		 *
		 * @param $blockId
		 *
		 * @return Field
		 */
		public function setBlockId ($blockId) {
			$this->blockId = $blockId;
			return $this;
		}

		/**
		 * Establece el nombre del calculo
		 *
		 * @param string $calculationName
		 *
		 * @return Field
		 */
		public function setCalculationName ($calculationName) {
			$this->calculationName = $calculationName;
			return $this;
		}

		/**
		 * Establece el nombre de la columna
		 *
		 * @param string $columnName
		 *
		 * @return Field
		 */
		public function setColumnName ($columnName) {
			$this->columnName = $columnName;
			return $this;
		}

		/**
		 * Establece el valor por defecto tendra el campo
		 *
		 * @param string $defaultValue
		 *
		 * @return Field
		 */
		public function setDefaultValue ($defaultValue) {
			$this->defaultValue = $defaultValue;
			return $this;
		}

		/**
		 * Establece las dependencias para el campo
		 *
		 * @param FieldDependency[] $dependencies
		 *
		 * @return Field
		 */
		public function setDependencies ($dependencies) {
			$this->dependencies = $dependencies;
			return $this;
		}

		/**
		 * Establece la accion de borrado para el campo
		 *
		 * @param boolean $deleted
		 *
		 * @return Field
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * Establece el valor del campo por pantalla
		 *
		 * @param integer $displayType
		 *
		 * @return Field
		 */
		public function setDisplayType ($displayType) {
			$this->displayType = $displayType;
			return $this;
		}

		/**
		 * Establece el tipo de campo generado
		 *
		 * @param integer $generatedType
		 *
		 * @return Field
		 */
		public function setGeneratedType ($generatedType) {
			$this->generatedType = $generatedType;
			return $this;
		}

		/**
		 * Establece el campo grid
		 *
		 * @param Grid $grid
		 *
		 * @return Field
		 */
		public function setGrid ($grid) {
			$this->grid = $grid;
			return $this;
		}

		/**
		 * Establece la etiqueta para el campo
		 *
		 * @param string $label
		 *
		 * @return Field
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * Establece el valor de la bandera que controla si el campo puede bloquearse o no
		 *
		 * @param boolean $locked
		 *
		 * @return Field
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * Establece el atributo de obligatoriedad que se le pueda definir al campo
		 *
		 * @param boolean $isMandatory
		 *
		 * @return Field
		 */
		public function setMandatory ($isMandatory) {
			$this->mandatory = !!$isMandatory;
			return $this;
		}

		/**
		 * Establece la edicion masiva de campos
		 *
		 * @param integer $massEditable
		 *
		 * @return Field
		 */
		public function setMassEditable ($massEditable) {
			$this->massEditable = $massEditable;
			return $this;
		}

		/**
		 * Establece el nombre del modulo con sus atributos: dependencias, grid, referencias, fuente de informacion y validaciones
		 *
		 * @param string $moduleName
		 *
		 * @return Field
		 */
		public function setModuleName ($moduleName) {
			$this->changeModuleName ($this->dependencies, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->grid, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->moduleReferences, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->pipeline, $this->moduleName, $moduleName);
			$this->changeModuleName ($this->validations, $this->moduleName, $moduleName);
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece las referencias del modulo
		 *
		 * @param FieldModuleReference[] $moduleReferences
		 *
		 * @return Field
		 */
		public function setModuleReferences ($moduleReferences) {
			if (empty ($moduleReferences)) {
				$this->moduleReferences = null;
			} else if (!is_array ($moduleReferences)) {
				$this->moduleReferences = array ($moduleReferences);
			} else {
				$this->moduleReferences = $moduleReferences;
			}
			return $this;
		}

		/**
		 * Establece el nombre llevara el campo
		 *
		 * @param string $name
		 *
		 * @return Field
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece el campo del tipo lista desplegable
		 *
		 * @param Picklist $picklist
		 *
		 * @return Field
		 */
		public function setPicklist ($picklist) {
			$this->picklist = $picklist;
			return $this;
		}

		/**
		 * Establece las fuentes de informacion del campo
		 *
		 * @param Pipeline $pipeline
		 *
		 * @return Field
		 */
		public function setPipeline ($pipeline) {
			$this->pipeline = $pipeline;
			return $this;
		}

		/**
		 * Establece la visibilidad llevara el campo
		 *
		 * @param integer $presence
		 *
		 * @return Field
		 */
		public function setPresence ($presence) {
			$this->presence = $presence;
			return $this;
		}

		/**
		 * Establece la secuencia de la creacion rapida del campo
		 *
		 * @param integer $quickCreate
		 *
		 * @return Field
		 */
		public function setQuickCreate ($quickCreate) {
			$this->quickCreate = $quickCreate;
			return $this;
		}

		/**
		 * Establece la secuencia para la creacion rapida del campo
		 *
		 * @param integer $quickCreateSequence
		 *
		 * @return Field
		 */
		public function setQuickCreateSequence ($quickCreateSequence) {
			$this->quickCreateSequence = $quickCreateSequence;
			return $this;
		}

		/**
		 * Establece el atributo ReadOnly del campo
		 *
		 * @param integer $readOnly
		 *
		 * @return Field
		 */
		public function setReadOnly ($readOnly) {
			$this->readOnly = $readOnly;
			return $this;
		}

		/**
		 * Establece la secuencia del campo
		 *
		 * @param integer $sequence
		 *
		 * @return Field
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Establece el nombre en la tabla para el campo
		 *
		 * @param string $tableName
		 *
		 * @return Field
		 */
		public function setTableName ($tableName) {
			$this->tableName = $tableName;
			return $this;
		}

		/**
		 * Establece el tipo de dato a nivel de la BD del campo
		 *
		 * @param integer $uiType
		 * @param integer|null $length
		 * @param integer|null $precision
		 *
		 * @return Field
		 */
		public function setUiType ($uiType, $length = null, $precision = null) {
			$this->uiType    = $uiType;
			$this->dataType  = $this->getDataTypeFromUiType ($uiType);
			$this->length    = $this->getLengthFromUiType ($uiType, isset ($length) ? $length : $this->length);
			$this->mandatory = $this->getMandatoryFromUiType ($uiType, $this->mandatory);
			$this->precision = $this->getPrecisionFromUiType ($uiType, isset ($precision) ? $precision : $this->precision);
			return $this;
		}

		/**
		 * Establece las validaciones que tiene el campo
		 *
		 * @param FieldValidation[] $validations
		 *
		 * @return Field
		 */
		public function setValidations ($validations) {
			$this->validations = $validations;
			return $this;
		}

		/**
		 * Copia los valores/atributos que se hayan definido para el campo
		 *
		 * @param Field $field
		 */
		public function copyValuesFrom ($field) {
			if ((empty ($field)) || (!($field instanceof Field))) {
				return;
			}
			$this->appField            = $field->getAppField ();
			$this->calculationName     = $field->getCalculationName ();
			$this->columnName          = $field->getColumnName ();
			$this->dataType            = $field->getDataType ();
			$this->defaultValue        = $field->getDefaultValue ();
			$this->displayType         = $field->getDisplayType ();
			$this->generatedType       = $field->getGeneratedType ();
			$this->label               = $field->getLabel ();
			$this->length              = $field->getLength ();
			$this->mandatory           = $field->isMandatory ();
			$this->massEditable        = $field->getMassEditable ();
			$this->moduleName          = $field->getModuleName ();
			$this->name                = $field->getName ();
			$this->precision           = $field->getPrecision ();
			$this->presence            = $field->getPresence ();
			$this->quickCreate         = $field->getQuickCreate ();
			$this->quickCreateSequence = $field->getQuickCreateSequence ();
			$this->readOnly            = $field->getReadOnly ();
			$this->sequence            = $field->getSequence ();
			$this->tableName           = $field->getTableName ();
			$this->uiType              = $field->getUiType ();
			$this->copyDependenciesFrom ($field);
			$this->copyGridFrom ($field);
			$this->copyModuleReferencesFrom ($field);
			$this->copyPicklistFrom ($field);
			$this->copyPipelineFrom ($field);
			$this->copyValidationsFrom ($field);
		}

		/**
		 * Realiza la accion de duplicar el campo
		 *
		 * @param integer $newFieldId
		 * @param integer $newBlockId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return Field
		 */
		public function duplicate ($newFieldId, $newBlockId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			$this->validate ();

			if (($this->uiType == self::UI_TYPE_CODE) && ($this->name == $oldCodeFieldName) && (!empty ($newCodeFieldName))) {
				$columnName = $newCodeFieldName;
				$fieldName  = $newCodeFieldName;
			} else {
				$columnName = $this->columnName;
				$fieldName  = $this->name;
			}

			$object = new self ();
			return $object->setId ($newFieldId)
				->setAppField ($this->appField)
				->setBlockId ($newBlockId)
				->setCalculationName ($this->calculationName)
				->setColumnName ($columnName)
				->setDefaultValue ($this->defaultValue)
				->setDependencies ($this->duplicateDependencies ())
				->setDisplayType ($this->displayType)
				->setGeneratedType ($this->generatedType)
				->setGrid (!empty ($this->grid) ? $this->grid->duplicate () : null)
				->setLabel ($this->label)
				->setMandatory ($this->mandatory)
				->setMassEditable ($this->massEditable)
				->setModuleName ($this->moduleName)
				->setModuleReferences ($this->duplicateModuleReferences ())
				->setName ($fieldName)
				->setPicklist (!empty ($this->picklist) ? $this->picklist->duplicate (!empty ($newFieldId) ? $this->picklist->getId () : null) : null)
				->setPipeline (!empty ($this->pipeline) ? $this->pipeline->duplicate () : null)
				->setPresence ($this->presence)
				->setQuickCreate ($this->quickCreate)
				->setQuickCreateSequence ($this->quickCreateSequence)
				->setReadOnly ($this->readOnly)
				->setSequence ($this->sequence)
				->setTableName ($this->tableName)
				->setUiType ($this->uiType, $this->length, $this->precision)
				->setValidations ($this->duplicateValidations ());
		}

		/**
		 * Compara si el campo es igual a otro
		 *
		 * @param Field $field
		 * @param boolean $deepCheck
		 *
		 * @return boolean <code>true</code> si los campos son iguales. <code>false</code> caso contrario
		 */
		public function isEqualTo ($field, $deepCheck = true) {
			if (
				(empty ($field)) ||
				(!($field instanceof Field)) ||
				($this->appField != $field->getAppField ()) ||
				($this->calculationName != $field->getCalculationName ()) ||
				($this->columnName != $field->getColumnName ()) ||
				($this->defaultValue != $field->getDefaultValue ()) ||
				($this->displayType != $field->getDisplayType ()) ||
				($this->generatedType != $field->getGeneratedType ()) ||
				($this->label != $field->getLabel ()) ||
				($this->length != $field->getLength ()) ||
				($this->mandatory != $field->isMandatory ()) ||
				($this->moduleName != $field->getModuleName ()) ||
				($this->name != $field->getName ()) ||
				($this->precision != $field->getPrecision ()) ||
				($this->presence != $field->getPresence ()) ||
				($this->readOnly != $field->getReadOnly ()) ||
				($this->sequence != $field->getSequence ()) ||
				($this->tableName != $field->getTableName ()) ||
				($this->uiType != $field->getUiType ()) ||
				(($deepCheck) && ((!$this->areDependenciesEqual ($field->getDependencies ())) || (!$this->areGridsEqual ($field->getGrid ())) || (!$this->areModuleReferencesEqual ($field->getModuleReferences ())) || (!$this->arePicklistsEqual ($field->getPicklist ())) || (!$this->arePipelinesEqual ($field->getPipeline ())) || (!$this->areValidationsEqual ($field->getValidations ()))))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Para actualizar la longitud del campo
		 *
		 * @param integer $newLength
		 * @param integer $newPrecision
		 */
		public function updateLength ($newLength, $newPrecision = null) {
			if (
				(!in_array ($this->uiType, array (FieldInterface::UI_TYPE_CURRENCY, FieldInterface::UI_TYPE_NUMBER, FieldInterface::UI_TYPE_PERCENTAGE, FieldInterface::UI_TYPE_TEXT))) ||
				(!is_int ($newLength)) || ($newLength <= 0) || ($newPrecision < 0) ||
				(($this->length == $newLength) && ($this->getPrecision () == $newPrecision))
			) {
				return;
			}

			$this->length    = $newLength;
			$this->precision = $newPrecision;
		}

		/**
		 * Valida si los valores/atributos del campo estan definidos
		 *
		 * @throws FieldException
		 */
		public function validate () {
			if ($this->deleted) {
				return;
			}

			$this->validateMandatoryProperties ();
			$this->validatePropertiesLength ();
			$this->validateGrid ();
			$this->validatePicklist ();
			$this->validatePipeline ();
			$this->validateModuleReferences ();
		}

		/**
		 * Calcula un SQL con los atributos del tipo de datos del campo: uiType, longitud y precision
		 *
		 * @param integer $uiType
		 * @param integer $length
		 * @param integer $precision
		 *
		 * @return null|string
		 */
		public static function calculateSqlDataType ($uiType, $length, $precision = null) {
			$length = (is_numeric ($length)) && ($length > 0) ? $length : 255;
			if (($uiType === null) || (in_array ($uiType, array (self::UI_TYPE_ATTACHMENTS, self::UI_TYPE_GRID)))) {
				return null;
			} else if (in_array ($uiType, array (self::UI_TYPE_CHECKBOX, self::UI_TYPE_CODE, self::UI_TYPE_EMAIL, self::UI_TYPE_MODULE_REFERENCE, self::UI_TYPE_PHONE, self::UI_TYPE_PICKLIST, self::UI_TYPE_PIPELINE, self::UI_TYPE_SKYPE, self::UI_TYPE_TEXT, self::UI_TYPE_URL, self::UI_TYPE_APP))) {
				return "VARCHAR({$length})";
			} else if (in_array ($uiType, array (self::UI_TYPE_CREATED_TIME, self::UI_TYPE_DATETIME))) {
				return 'DATETIME';
			} else if ($uiType == self::UI_TYPE_DATE) {
				return 'DATE';
			} else if ($uiType == self::UI_TYPE_TIME) {
				return 'TIME';
			} else if (in_array ($uiType, array (self::UI_TYPE_MODIFIED_BY, self::UI_TYPE_MULTI_SELECT, self::UI_TYPE_TEXTAREA, self::UI_TYPE_VIDEO))) {
				return 'TEXT';
			} else if (in_array ($uiType, array (self::UI_TYPE_CURRENCY, self::UI_TYPE_NUMBER, self::UI_TYPE_PERCENTAGE))) {
				return "NUMERIC({$length},{$precision})";
			} else if (in_array ($uiType, array (self::UI_TYPE_OWNER, self::UI_TYPE_MODIFIED_BY))) {
				return 'INT(19)';
			} else if (in_array ($uiType, array (self::UI_TYPE_CALCULATED_LINK))) {
				return 'DECIMAL(20,2)';
			} else {
				return 'VARCHAR(255)';
			}
		}

		/**
		 * Calcula los atributos del tipo de dato del campo: uiType, longitud, precision y obligatoriedad
		 *
		 * @param integer $uiType
		 * @param integer $length
		 * @param integer $precision
		 * @param boolean $isMandatory
		 *
		 * @return null|string
		 */
		public static function calculateTypeOfData ($uiType, $length, $precision, $isMandatory) {
			$mandatory = $isMandatory ? 'M' : 'O';
			if (in_array ($uiType, array (self::UI_TYPE_CODE, self::UI_TYPE_TEXT))) {
				$dataType   = self::DATA_TYPE_VARCHAR;
				$length     = (is_numeric ($length)) && ($length >= 0) && (intval ($length) == $length) ? intval ($length) : 255;
				$typeOfData = "{$dataType}~{$mandatory}~LE~{$length}";
			} else if (in_array ($uiType, array (self::UI_TYPE_CREATED_TIME, self::UI_TYPE_DATETIME))) {
				$dataType   = self::DATA_TYPE_DATETIME;
				$typeOfData = "{$dataType}~{$mandatory}";
			} else if ($uiType == self::UI_TYPE_DATE) {
				$dataType   = self::DATA_TYPE_DATE;
				$typeOfData = "{$dataType}~{$mandatory}";
			} else if ($uiType == self::UI_TYPE_TIME) {
				$dataType   = self::DATA_TYPE_TIME;
				$typeOfData = "{$dataType}~{$mandatory}";
			} else if ($uiType == self::UI_TYPE_GRID || $uiType == self::UI_TYPE_TABLE_FIELD) {
				$dataType   = self::DATA_TYPE_GRID;
				$typeOfData = "{$dataType}~{$mandatory}";
			} else if (in_array ($uiType, array (self::UI_TYPE_CURRENCY, self::UI_TYPE_NUMBER, self::UI_TYPE_CALCULATED_LINK))) {
				$dataType   = self::DATA_TYPE_NEGATIVE_NUMBER;
				$length     = (is_numeric ($length)) && ($length >= 0) && (intval ($length) == $length) ? intval ($length) : 18;
				$precision  = (is_numeric ($precision)) && ($precision >= 0) && (intval ($precision) == $precision) ? $precision : 0;
				$typeOfData = "{$dataType}~{$mandatory}~{$length},{$precision}";
			} else if (in_array ($uiType, array (self::UI_TYPE_PERCENTAGE))) {
				$dataType   = self::DATA_TYPE_NUMBER;
				$length     = (is_numeric ($length)) && ($length >= 0) && (intval ($length) == $length) ? intval ($length) : 18;
				$precision  = (is_numeric ($precision)) && ($precision >= 0) && (intval ($precision) == $precision) ? $precision : 0;
				$typeOfData = "{$dataType}~{$mandatory}~{$length},{$precision}";
			} else {
				$dataType   = self::DATA_TYPE_VARCHAR;
				$typeOfData = "{$dataType}~{$mandatory}";
			}
			return $typeOfData;
		}

		/**
		 * Instanciación de la clase Field. Se obtiene un objeto Field con los atributos de la clase
		 *
		 * @param string $typeOfData
		 *
		 * @return Field
		 */
		public static function getInstance ($typeOfData = '') {
			return new self ($typeOfData);
		}

	}

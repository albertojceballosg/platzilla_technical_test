<?php
	require_once ('include/platzilla/Exceptions/BlockException.php');
	require_once ('include/platzilla/Objects/BlockInterface.php');
	require_once ('include/platzilla/Objects/Field.php');

	/**
	 * Class Block
	 *
	 * En esta clase se define el objeto "Bloque" el cual hace referencia a los bloques que componen a un modulo que un usuario puede dar de alta en una Aplicacion.
	 **/
	class Block implements BlockInterface {
		/** @var integer */
		private $id;

		/** @var boolean */
		private $deleted;

		/** @var Field[] */
		private $fields;

		/** @var integer */
		private $isCustom;

		/** @var string */
		private $label;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var integer */
		private $sequence;

		/** @var integer */
		private $showTitle;

		/** @var integer */
		private $visibility;

		/** @var integer */
		private $visibilityInCreateView;

		/** @var integer */
		private $visibilityInDetailView;

		/** @var integer */
		private $visibilityInEditView;

		/*
		 * Block constructor
		 */
		public function __construct () {
			$this->deleted                = false;
			$this->isCustom               = self::IS_CUSTOM_NO;
			$this->locked                 = false;
			$this->showTitle              = self::SHOW_TITLE_YES;
			$this->visibility             = self::VISIBILITY_VISIBLE;
			$this->visibilityInCreateView = self::VISIBILITY_VISIBLE;
			$this->visibilityInDetailView = self::VISIBILITY_VISIBLE;
			$this->visibilityInEditView   = self::VISIBILITY_VISIBLE;
		}

		/**
		 * Realiza comparacion si los campos del bloque son iguales a otro
		 *
		 * @param Field[] $fields
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
					foreach ($fields as $thatField) {
						if (($thisField->getName () != $thatField->getName ()) && ($thisField->isEqualTo ($thatField))) {
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
		 * Para realizar la accion del cambio de nombre del modulo
		 *
		 * @param object|Field[] $elements
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
		 * Realiza copia de los campos del bloque que componen al modulo
		 *
		 * @param integer $blockId
		 * @param Field[] $sourceFields
		 */
		private function copyFields ($blockId, $sourceFields) {
			$fields = array ();
			foreach ($sourceFields as $sourceField) {
				$found = false;
				foreach ($this->fields as $targetField) {
					if ($sourceField->getName () != $targetField->getName ()) {
						continue;
					} else if ((!$targetField->isDeleted ()) && (!$targetField->isEqualTo ($sourceField))) {
						$targetField->copyValuesFrom ($sourceField);
					}
					$fields [] = $targetField;
					$found     = true;
					break;
				}
				if (!$found) {
					$fields [] = $sourceField->duplicate (null, $blockId);
				}
			}
			$this->fields = $fields;
		}

		/**
		 * Realiza copia de los campos del bloque desde otro bloque
		 *
		 * @param Block $block
		 */
		private function copyFieldsFrom ($block) {
			$sourceFields = $block->getFields ();
			if ((empty ($sourceFields)) && (empty ($this->fields))) {
				return;
			}

			if (empty ($sourceFields)) {
				$this->fields = null;
			} else if (empty ($this->fields)) {
				$fields = array ();
				foreach ($sourceFields as $sourceField) {
					$fields [] = $sourceField->duplicate (null, $this->id);
				}
				$this->fields = $fields;
			} else {
				$this->copyFields ($this->id, $sourceFields);
			}
		}

		/**
		 * Para obtener el id del bloque
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener la visibilidad tendra el bloque en el modulo
		 *
		 * @return integer
		 */
		public function getDisplayStatus () {
			return $this->visibility == self::VISIBILITY_VISIBLE ? self::VISIBILITY_HIDDEN : self::VISIBILITY_VISIBLE;
		}

		/**
		 * Para obtener los campos asociados al bloque que compone al modulo
		 *
		 * @return Field[]
		 */
		public function getFields () {
			return $this->fields;
		}

		/**
		 * Para obtener si el bloque es personalizable
		 *
		 * @return integer
		 */
		public function getIsCustom () {
			return $this->isCustom;
		}

		/**
		 * Para obtener las etiquetas de los bloques que compone el modulo
		 *
		 * @return string
		 */
		public function getLabel () {
			return $this->label;
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
		 * Para obtener la secuencia del bloque que compone al modulo
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Para obtener el valor que controla si se muestra o no el titulo del bloque
		 *
		 * @return integer
		 */
		public function getShowTitle () {
			return $this->showTitle;
		}

		/**
		 * Para obtener el valor que controla si el bloque es visible en el modulo
		 *
		 * @return integer
		 */
		public function getVisibility () {
			return $this->visibility;
		}

		/**
		 * Para obtener el valor que controla si el bloque es visible en la vista de creacion de registro del modulo
		 *
		 * @return integer
		 */
		public function getVisibilityInCreateView () {
			return $this->visibilityInCreateView;
		}

		/**
		 * Para obtener el valor que controla si el bloque es visible en la vista detallada del modulo
		 *
		 * @return integer
		 */
		public function getVisibilityInDetailView () {
			return $this->visibilityInDetailView;
		}

		/**
		 * Para obtener el valor que controla si el bloque es visible en la vista de edicion del modulo
		 *
		 * @return integer
		 */
		public function getVisibilityInEditView () {
			return $this->visibilityInEditView;
		}

		/**
		 * Obtiene el valor de la bandera que controla si el bloque puede ser borrado del modulo
		 *
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * Obtiene el valor de la bandera que controla si el bloque puede bloquearse o no
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Establece el id que tendra el bloque para el modulo
		 *
		 * @param integer $id
		 *
		 * @return Block
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece la accion de borrado para el bloque
		 *
		 * @param boolean $deleted
		 *
		 * @return Block
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * Establece los campos que pueden definirse dentrol del bloque
		 *
		 * @param Field[] $fields
		 *
		 * @return Block
		 */
		public function setFields ($fields) {
			$this->fields = $fields;
			return $this;
		}

		/**
		 * Establece la accion para poder personalizar el bloque
		 *
		 * @param integer $isCustom
		 *
		 * @return Block
		 */
		public function setIsCustom ($isCustom) {
			if (in_array ($isCustom, array (self::IS_CUSTOM_NO, self::IS_CUSTOM_YES))) {
				$this->isCustom = $isCustom;
			}
			return $this;
		}

		/**
		 * Establece la etiqueta para el bloque
		 *
		 * @param string $label
		 *
		 * @return Block
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * Establece la accion para que el bloque pueda ser bloqueado
		 *
		 * @param boolean $locked
		 *
		 * @return Block
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * Establece el nombre del modulo
		 *
		 * @param string $moduleName
		 *
		 * @return Block
		 */
		public function setModuleName ($moduleName) {
			$this->changeModuleName ($this->fields, $this->moduleName, $moduleName);
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece la secuencia para el bloque
		 *
		 * @param integer $sequence
		 *
		 * @return Block
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Establece el valor que controla la accion que muestra el titulo del bloque
		 *
		 * @param integer $showTitle
		 *
		 * @return Block
		 */
		public function setShowTitle ($showTitle) {
			if (in_array ($showTitle, array (self::SHOW_TITLE_NO, self::SHOW_TITLE_YES))) {
				$this->showTitle = $showTitle;
			}
			return $this;
		}

		/**
		 * Establece la visibilidad del bloque y los campos que lo conforman
		 *
		 * @param integer $visibility
		 * @param integer $inCreateView
		 * @param integer $inDetailView
		 * @param integer $inEditView
		 *
		 * @return Block
		 */
		public function setVisibility ($visibility, $inCreateView = self::VISIBILITY_VISIBLE, $inDetailView = self::VISIBILITY_VISIBLE, $inEditView = self::VISIBILITY_VISIBLE) {
			if (in_array ($visibility, array (self::VISIBILITY_HIDDEN, self::VISIBILITY_VISIBLE))) {
				$this->visibility = $visibility;
			}
			if (in_array ($inCreateView, array (self::VISIBILITY_HIDDEN, self::VISIBILITY_VISIBLE))) {
				$this->visibilityInCreateView = $inCreateView;
			}
			if (in_array ($inDetailView, array (self::VISIBILITY_HIDDEN, self::VISIBILITY_VISIBLE))) {
				$this->visibilityInDetailView = $inDetailView;
			}
			if (in_array ($inEditView, array (self::VISIBILITY_HIDDEN, self::VISIBILITY_VISIBLE))) {
				$this->visibilityInEditView = $inEditView;
			}
			return $this;
		}

		/**
		 * Realiza el copiado de los valores/atributos que se definieron y establecieron para el bloque
		 *
		 * @param Block $block
		 */
		public function copyValuesFrom ($block) {
			if ((empty ($block)) || (!($block instanceof Block))) {
				return;
			}

			$this->isCustom               = $block->getIsCustom ();
			$this->label                  = $block->getLabel ();
			$this->moduleName             = $block->getModuleName ();
			$this->sequence               = $block->getSequence ();
			$this->showTitle              = $block->getShowTitle ();
			$this->visibility             = $block->getVisibility ();
			$this->visibilityInCreateView = $block->getVisibilityInCreateView ();
			$this->visibilityInDetailView = $block->getVisibilityInDetailView ();
			$this->visibilityInEditView   = $block->getVisibilityInEditView ();
			$this->copyFieldsFrom ($block);
		}

		/**
		 * Realiza la accion de duplicar el bloque con los valores/atributos que tiene definido
		 *
		 * @param integer $newBlockId
		 * @param string $oldCodeFieldName
		 * @param string $newCodeFieldName
		 *
		 * @return Block
		 * @throws BlockException
		 */
		public function duplicate ($newBlockId, $oldCodeFieldName = null, $newCodeFieldName = null) {
			$this->validate ();

			$fields = array ();
			if (!empty ($this->fields)) {
				foreach ($this->fields as $field) {
					$fields [] = $field->duplicate (!empty ($newBlockId) ? $field->getId () : null, $newBlockId, $oldCodeFieldName, $newCodeFieldName);
				}
			}

			$object = new self ();
			return $object->setId ($newBlockId)
				->setFields ($fields)
				->setIsCustom ($this->isCustom)
				->setLabel ($this->label)
				->setModuleName ($this->moduleName)
				->setSequence ($this->sequence)
				->setShowTitle ($this->showTitle)
				->setVisibility ($this->visibility, $this->visibilityInCreateView, $this->visibilityInDetailView, $this->visibilityInEditView);
		}

		/**
		 * Compara que los atributos/valores de un bloque sea igual a otro
		 *
		 * @param Block $block
		 * @param boolean $deepCheck
		 *
		 * @return boolean
		 */
		public function isEqualTo ($block, $deepCheck = true) {
			if (
				(empty ($block)) ||
				(!($block instanceof Block)) ||
				($this->isCustom != $block->getIsCustom ()) ||
				($this->label != $block->getLabel ()) ||
				($this->moduleName != $block->getModuleName ()) ||
				($this->sequence != $block->getSequence ()) ||
				($this->showTitle != $block->getShowTitle ()) ||
				($this->visibility != $block->getVisibility ()) ||
				($this->visibilityInCreateView != $block->getVisibilityInCreateView ()) ||
				($this->visibilityInDetailView != $block->getVisibilityInDetailView ()) ||
				($this->visibilityInEditView != $block->getVisibilityInEditView ()) ||
				(($deepCheck) && (!$this->areFieldsEqual ($block->getFields ())))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Establece el nombre para la tabla que almacenara los valores del bloque
		 *
		 * @param string $oldTableName
		 * @param string $newTableName
		 */
		public function setTableName ($oldTableName, $newTableName) {
			if (empty ($this->fields)) {
				return;
			}

			$n = count ($this->fields);
			for ($i = 0; $i < $n; $i++) {
				if ($this->fields [ $i ]->getTableName () == $oldTableName) {
					$this->fields [ $i ]->setTableName ($newTableName);
				}
			}
		}

		/**
		 * Valida si el bloque muestra el titulo, no tiene la etiqueta vacia y los campos son validos
		 *
		 * @throws BlockException
		 * @throws FieldException
		 */
		public function validate () {
			if ($this->deleted) {
				return;
			} else if (($this->showTitle == self::SHOW_TITLE_YES) && (empty ($this->label))) {
				throw new BlockException (BlockException::ERROR_BLOCK_EMPTY_LABEL);
			} else if ((!empty ($this->fields)) && (!is_array ($this->fields))) {
				throw new BlockException (BlockException::ERROR_BLOCK_INVALID_FIELDS);
			} else if ((!empty ($this->id)) && (!empty ($this->fields))) {
				foreach ($this->fields as $field) {
					if (!($field instanceof Field)) {
						throw new BlockException (BlockException::ERROR_BLOCK_INVALID_FIELD);
					} else {
						$field->validate ();
					}
				}
			}
		}

		/**
		 * Instanciacion de la clase Block. Se obtiene un objeto Block con los atributos de la clase
		 *
		 * @return Block
		 */
		public static function getInstance () {
			return new self ();
		}

	}

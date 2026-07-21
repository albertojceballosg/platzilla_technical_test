<?php
	require_once ('include/platzilla/Exceptions/ModuleRelationshipException.php');
	require_once ('include/platzilla/Objects/ModuleRelationshipFields.php');
	require_once ('include/platzilla/Objects/ModuleRelationshipInterface.php');

	/**
	 * Class ModuleRelationship
	 *
	 * En esta clase se define el objeto "Relaciones Modulo" el cual hace referencia a las relaciones que se pueden establecer con otros módulos en una Aplicación.
	 **/
	class ModuleRelationship implements ModuleRelationshipInterface {
		/** @var string[] */
		private $actions;

		/** @var boolean */
		private $deleted;

		/** @var string */
		private $function;

		/** @var string */
		private $label;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var integer */
		private $presence;
		
		/** @var ModuleRelationshipFields */
		private $relatedFields;
		
		/** @var integer */
		private $relationId;
		
		/** @var string */
		private $relatedModuleLabel;
		
		/** @var string */
		private $relatedModuleName;

		/** @var integer */
		private $sequence;

		/**
		 * ModuleRelationship constructor.
		 *
		 */
		public function __construct () {
			$this->deleted = false;
			$this->locked  = false;
			$this->presence = self::PRESENCE_VISIBLE;
		}

		/**
		 * Valida sí las relaciones (agregar y seleccionar) del modulo estan definidas y establecidas
		 *
		 * @throws ModuleRelationshipException
		 */
		private function validateActions () {
			if (empty ($this->actions)) {
				return;
			}

			if (!is_array ($this->actions)) {
				throw new ModuleRelationshipException (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_INVALID_ACTIONS);
			}

			foreach ($this->actions as $action) {
				if (!in_array ($action, array ('', self::ACTION_ADD, self::ACTION_SELECT))) {
					throw new ModuleRelationshipException (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_INVALID_ACTION);
				}
			}
		}

		/**
		 * Para obtener las relaciones del modulo
		 *
		 * @return string[]
		 */
		public function getActions () {
			return $this->actions;
		}

		/**
		 * Para obtener la funcion/accion de la relacion del modulo
		 *
		 * @return string
		 */
		public function getFunction () {
			return $this->function;
		}

		/**
		 * Para obtener la etiqueta de la relacion del modulo
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
		 * Para obtener la visibilidad del modulo
		 *
		 * @return integer
		 */
		public function getPresence () {
			return $this->presence;
		}
		
		/**
		 * @return ModuleRelationshipFields
		 */
		public function getRelatedFields () {
			return $this->relatedFields;
		}
		
		/**
		 * @return integer
		 */
		public function getRelationId () {
			return $this->relationId;
		}
		
		/**
		 * @return string
		 */
		public function getRelatedModuleLabel () {
			return $this->relatedModuleLabel;
		}
		
		/**
		 * Para obtener el nombre de la relaciones del modulo
		 *
		 * @return string
		 */
		public function getRelatedModuleName () {
			return $this->relatedModuleName;
		}

		/**
		 * Para obtener la secuencia de relacion del modulo
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Realiza el borrado de la relacion del modulo
		 *
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * Para realizar el bloqueo de la relacion del modulo
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Establece las relaciones para el modulo
		 *
		 * @param string[] $actions
		 *
		 * @return ModuleRelationship
		 */
		public function setActions ($actions) {
			if ((!empty ($actions)) && (is_array ($actions))) {
				$dummy = array ();
				foreach ($actions as $action) {
					if (is_string ($action)) {
						$dummy [] = strtoupper ($action);
					} else {
						$dummy [] = $action;
					}
				}
			} else {
				$dummy = $actions;
			}
			$this->actions = $dummy;
			return $this;
		}

		/**
		 * Establece el borrado para las relaciones del modulo
		 *
		 * @param boolean $deleted
		 *
		 * @return ModuleRelationship
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * Establece la funcion/accion de la relacion del modulo
		 *
		 * @param string $function
		 *
		 * @return ModuleRelationship
		 */
		public function setFunction ($function) {
			if (!empty ($function)) {
				$this->function = $function;
			}
			return $this;
		}

		/**
		 * Establece la etiqueta de la relacion del modulo
		 *
		 * @param string $label
		 *
		 * @return ModuleRelationship
		 */
		public function setLabel ($label) {
			$this->label = $label;
			return $this;
		}

		/**
		 * Establece el bloqueo de la relacion del modulo
		 *
		 * @param boolean $locked
		 *
		 * @return ModuleRelationship
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * Establece el nombre de la relaciones del modulo
		 *
		 * @param string $moduleName
		 *
		 * @return ModuleRelationship
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece la visibilidad del modulo
		 *
		 * @param integer $presence
		 *
		 * @return ModuleRelationship
		 */
		public function setPresence ($presence) {
			if (in_array ($presence, array (self::PRESENCE_HIDDEN, self::PRESENCE_VISIBLE))) {
				$this->presence = $presence;
			}
			return $this;
		}
		
		/**
		 * @param ModuleRelationshipFields $relatedFields
		 *
		 * @return ModuleRelationship
		 */
		public function setRelatedFields ($relatedFields) {
			if ($relatedFields instanceof ModuleRelationshipFields) {
				$this->relatedFields = $relatedFields;
			} else {
				$this->relatedFields = null;
			}
			return $this;
		}
		
		/**
		 * @param integer $relationId
		 *
		 * @return ModuleRelationship
		 */
		public function setRelationId ($relationId) {
			$this->relationId = $relationId;
			return $this;
		}
		
		/**
		 * @param string $relatedModuleLabel
		 *
		 * @return ModuleRelationship
		 */
		public function setRelatedModuleLabel ($relatedModuleLabel) {
			$this->relatedModuleLabel = $relatedModuleLabel;
			return $this;
		}
		
		/**
		 * Establece el nombre de la relaciones del modulo
		 *
		 * @param string $relatedModuleName
		 *
		 * @return ModuleRelationship
		 */
		public function setRelatedModuleName ($relatedModuleName) {
			$this->relatedModuleName = $relatedModuleName;
			return $this;
		}

		/**
		 * Establece la secuencia de relacion del modulo
		 *
		 * @param integer $sequence
		 *
		 * @return ModuleRelationship
		 */
		public function setSequence ($sequence) {
			$this->sequence = $sequence;
			return $this;
		}

		/**
		 * Realiza copia de las relaciones del modulo desde otro modulo
		 *
		 * @param ModuleRelationship $relationship
		 */
		public function copyValuesFrom ($relationship) {
			if ((empty ($relationship)) || (!($relationship instanceof ModuleRelationship))) {
				return;
			}

			$this->actions           = $relationship->getActions ();
			$this->function          = $relationship->getFunction ();
			$this->label             = $relationship->getLabel ();
			$this->moduleName        = $relationship->getModuleName ();
			$this->presence          = $relationship->getPresence ();
			$this->relatedModuleName = $relationship->getRelatedModuleName ();
			$this->sequence          = $relationship->getSequence ();
		}

		/**
		 * Realiza duplicado de las relaciones del modulo
		 *
		 * @return ModuleRelationship
		 * @throws ModuleRelationshipException
		 */
		public function duplicate () {
			$this->validate ();
			$object = new self ();
			return $object->setActions ($this->actions)
				->setFunction ($this->function)
				->setLabel ($this->label)
				->setModuleName ($this->moduleName)
				->setPresence ($this->presence)
				->setRelatedModuleName ($this->relatedModuleName)
				->setSequence ($this->sequence);
		}

		/**
		 * Para realizar comparacion si es igual la relaciones de un modulo con otro
		 *
		 * @param ModuleRelationship $relationship
		 *
		 * @return boolean
		 */
		public function isEqualTo ($relationship) {
			if (
				(empty ($relationship)) ||
				(!($relationship instanceof ModuleRelationship)) ||
				($this->actions != $relationship->getActions ()) ||
				($this->function != $relationship->getFunction ()) ||
				($this->label != $relationship->getLabel ()) ||
				($this->moduleName != $relationship->getModuleName ()) ||
				($this->presence != $relationship->getPresence ()) ||
				($this->relatedModuleName != $relationship->getRelatedModuleName ()) ||
				($this->sequence != $relationship->getSequence ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Valida si la relaciones del modulo tiene asignado relacion/funcion, etiqueta, nombre y las relaciones no estan vacias
		 *
		 * @throws ModuleRelationshipException
		 */
		public function validate () {
			if ($this->deleted) {
				return;
			} else if (empty ($this->function)) {
				throw new ModuleRelationshipException (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_EMPTY_FUNCTION);
			} else if (empty ($this->label)) {
				throw new ModuleRelationshipException (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_EMPTY_LABEL);
			} else if (!isset ($this->moduleName)) {
				throw new ModuleRelationshipException (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_EMPTY_MODULE_NAME);
			} else if (!isset ($this->relatedModuleName)) {
				throw new ModuleRelationshipException (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_EMPTY_RELATED_MODULE_NAME);
			}
			$this->validateActions ();
		}

		/**
		 * Instanciación de la clase ModuleRelationship. Se obtiene un objeto ModuleRelationship con los atributos de la clase
		 *
		 * @return ModuleRelationship
		 */
		public static function getInstance () {
			return new self ();
		}

	}

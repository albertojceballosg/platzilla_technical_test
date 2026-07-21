<?php
	require_once ('include/platzilla/Objects/PicklistValueInterface.php');
	require_once ('include/platzilla/Objects/Role.php');
	require_once ('include/platzilla/Utils/MiscellaneousUtils.php');

	/**
	 * Class PicklistValue
	 *
	 * Esta clase define el objeto "Valores Picklist" hace referencia a los valores que pueden tomar los campos del tipo "Picklist" o lista desplegable que contiene un Módulo.
	 * La clase está asociada al objeto "Rol"
	 */
	class PicklistValue implements PicklistValueInterface {
		/** @var integer */
		private $id;

		/** @var boolean */
		private $availableForAllRoles;

		/** @var boolean */
		private $deleted;

		/** @var boolean */
		private $locked;

		/** @var integer */
		private $presence;

		/** @var Role[] */
		private $roles;

		/** @var string */
		private $value;

		/**
		 * PicklistValue constructor.
		 *
		 * @param bool $availableForAllRoles
		 */
		public function __construct ($availableForAllRoles = true) {
			$this->deleted = false;
			$this->locked = false;
			$this->presence             = self::PRESENCE_VISIBLE;
			$this->availableForAllRoles = is_bool ($availableForAllRoles) ? $availableForAllRoles : true;
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
		 * Obtiene la visibilidad del picklist a nivel de la BD
		 *
		 * @return integer
		 */
		public function getPresence () {
			if (!in_array ($this->presence, array (self::PRESENCE_HIDDEN, self::PRESENCE_VISIBLE))) {
				return self::PRESENCE_VISIBLE;
			} else {
				return $this->presence;
			}
		}

		/**
		 * Obtiene los roles tendra asociados los valores del picklist
		 *
		 * @return Role[]
		 */
		public function getRoles () {
			return $this->roles;
		}

		/**
		 * Obtiene los valores del picklist
		 *
		 * @return string
		 */
		public function getValue () {
			return $this->value;
		}

		/**
		 * Para validar si los valores del picklist estan disponibles para todos los roles
		 *
		 * @return boolean
		 */
		public function isAvailableForAllRoles () {
			return $this->availableForAllRoles;
		}

		/**
		 * Para realizar el borrado del picklist
		 *
		 * @return boolean
		 */
		public function isDeleted () {
			return $this->deleted;
		}

		/**
		 * Obtiene el valor de la bandera que controla si el picklist puede bloquearse o no
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Estable el id para el picklist
		 *
		 * @param integer $id
		 *
		 * @return PicklistValue
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece la accion de borrado para el picklist
		 *
		 * @param boolean $deleted
		 *
		 * @return PicklistValue
		 */
		public function setDeleted ($deleted) {
			if (is_bool ($deleted)) {
				$this->deleted = $deleted;
			}
			return $this;
		}

		/**
		 * Para establecer el valor de la bandera que controla si el picklist puede bloquearse o no
		 *
		 * @param boolean $locked
		 *
		 * @return PicklistValue
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			}
			return $this;
		}

		/**
		 * Establece la visibilidad del picklist a nivel de la BD
		 *
		 * @param integer $presence
		 *
		 * @return PicklistValue
		 */
		public function setPresence ($presence) {
			if (in_array ($presence, array (self::PRESENCE_HIDDEN, self::PRESENCE_VISIBLE))) {
				$this->presence = $presence;
			}
			return $this;
		}

		/**
		 * Establece los roles tendra asociados los valores del picklist
		 *
		 * @param Role[] $roles
		 *
		 * @return PicklistValue
		 */
		public function setRoles ($roles) {
			if (($roles === null) || ((is_array ($roles)) && (!empty ($roles)))) {
				$this->roles = $roles;
			}
			return $this;
		}

		/**
		 * Establece los valores tendra el picklist
		 *
		 * @param string $value
		 *
		 * @return PicklistValue
		 */
		public function setValue ($value) {
			$this->value = $value;
			return $this;
		}

		/**
		 * Realiza copia de los valores del picklist desde otro picklist
		 *
		 * @param PicklistValue $picklistValue
		 */
		public function copyValuesFrom ($picklistValue) {
			if ((empty ($picklistValue)) || (!($picklistValue instanceof PicklistValue))) {
				return;
			}

			$this->presence = $picklistValue->getPresence ();
			$this->roles    = $picklistValue->getRoles ();
			$this->value    = $picklistValue->getValue ();
		}

		/**
		 * Duplica los atributos (id, visibilidad, roles asociados y valor) del picklist
		 *
		 * @param integer $newValueId
		 *
		 * @return PicklistValue
		 */
		public function duplicate ($newValueId) {
			$object = new self ();
			return $object->setId ($newValueId)
				->setPresence ($this->presence)
				->setRoles ($this->roles)
				->setValue ($this->value);
		}

		/**
		 * Compara si los atributos (visibilidad, valores) del picklist es igual a otro
		 *
		 * @param PicklistValue $picklistValue
		 *
		 * @return boolean
		 */
		public function isEqualTo ($picklistValue) {
			if (
				(empty ($picklistValue)) ||
				(!($picklistValue instanceof PicklistValue)) ||
				($this->presence != $picklistValue->getPresence ()) ||
				($this->value != $picklistValue->getValue ()) ||
				(!MiscellaneousUtils::areArrayValuesEqual ($this->roles, $picklistValue->getRoles ()))
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Instanciación de la clase PicklistValue. Se obtiene un objeto PicklistsValue con los valores de la clase
		 *
		 * @param boolean $availableForAllRoles
		 *
		 * @return PicklistValue
		 */
		public static function getInstance ($availableForAllRoles = true) {
			return new self ($availableForAllRoles);
		}

	}

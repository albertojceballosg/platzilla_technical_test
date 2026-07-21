<?php
	require_once ('include/platzilla/Exceptions/RoleException.php');
	require_once ('include/platzilla/Objects/Profile.php');

	/**
	 * Class Role
	 *
	 * La clase "Rol" hace referencia a la asignación de roles que determinan cuáles son las acciones que puede realizar o no un usuario, en la "Instancia".
	 * La clase está asociada al objeto "Perfil".
	 */
	class Role {
		/** @var string */
		private $id;

		/** @var string */
		private $defaultModule;

		/** @var string */
		private $name;

		/** @var Role */
		private $parent;

		/** @var Profile[] */
		private $profiles;

		/**
		 * Para validar los perfiles
		 *
		 * @throws ProfileException
		 */
		private function validateProfiles () {
			if (empty ($this->profiles)) {
				return;
			}

			foreach ($this->profiles as $profile) {
				$profile->validate ();
			}
		}

		/**
		 * Obtiene el ID del rol
		 *
		 * @return string
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener el modulo por defecto para el rol
		 *
		 * @return string
		 */
		public function getDefaultModule () {
			return $this->defaultModule;
		}

		/**
		 * Para obtener el nombre del rol
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener la jerarquia del rol
		 *
		 * @return Role
		 */
		public function getParent () {
			return $this->parent;
		}

		/**
		 * Para obtener el perfil o perfiles asociados al rol
		 *
		 * @return Profile[]
		 */
		public function getProfiles () {
			return $this->profiles;
		}

		/**
		 * Establece el id asociado al rol
		 *
		 * @param string $id
		 *
		 * @return Role
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}

		/**
		 * Establece el modulo por defecto para el rol
		 *
		 * @param string $defaultModule
		 *
		 * @return Role
		 */
		public function setDefaultModule ($defaultModule) {
			$this->defaultModule = $defaultModule;
			return $this;
		}

		/**
		 * Establece el nombre para el rol
		 *
		 * @param string $name
		 *
		 * @return Role
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece la jerarquia para el rol
		 *
		 * @param Role $parent
		 *
		 * @return Role
		 */
		public function setParent ($parent) {
			if (empty ($parent)) {
				$this->parent = null;
			} else if ($parent instanceof Role) {
				$this->parent = $parent;
			}
			return $this;
		}

		/**
		 * Establece los perfiles para el rol
		 *
		 * @param Profile[] $profiles
		 *
		 * @return Role
		 */
		public function setProfiles ($profiles) {
			if (is_array ($profiles)) {
				$this->profiles = $profiles;
			}
			return $this;
		}

		/**
		 * Valida que los atributos (nombre y jerarquia) esten correctamente
		 *
		 * @throws RoleException
		 */
		public function validate () {
			if (empty ($this->name)) {
				throw new RoleException (RoleException::ERROR_ROLE_EMPTY_NAME);
			} else if (isset ($this->parent)) {
				$this->parent->validate ();
			}

			$this->validateProfiles ();
		}

		/**
		 * Instanciación de la clase Role. Se obtiene un objeto Role con los valores de la clase
		 *
		 * @return Role
		 */
		public static function getInstance () {
			return new self ();
		}

	}

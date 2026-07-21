<?php
	require_once ('include/platzilla/Exceptions/UserException.php');
	require_once ('include/platzilla/Objects/Role.php');
	require_once ('include/platzilla/Objects/UserInterface.php');

	/**
	 * Class User
	 *
	 * Esta clase define el objeto "Usuario" el cual hace referencia a los usuarios que pueden acceder a la "Plataforma" y/o la "Instancia".
	 * La clase está asociada al objeto "Rol".
	 */
	class User implements UserInterface {
		/** @var integer */
		private $id;

		/** @var boolean */
		private $administrator;

		/** @var string */
		private $defaultModuleName;

		/** @var string */
		private $defaultOperating;
		
		/** @var string */
		private $defaultHomeTab;

		/** @var string */
		private $email;

		/** @var string */
		private $firstName;

		/** @var string */
		private $imageUri;

		/** @var string */
		private $lastName;

		/** @var string */
		private $plainPassword;

		/** @var Role[] */
		private $roles;

		/** @var string */
		private $status;

		/** @var string */
		private $userName;

		/**
		 * User constructor.
		 */
		public function __construct () {
			$this->administrator = false;
			$this->status          = self::STATUS_ACTIVE;
		}

		/**
		 * Valida los roles se asociaran al usuario
		 *
		 * @throws RoleException
		 * @throws UserException
		 */
		private function validateRoles () {
			if (empty ($this->roles)) {
				return;
			}

			foreach ($this->roles as $role) {
				if (!($role instanceof Role)) {
					throw new UserException (UserException::ERROR_USER_INVALID_ROLE);
				} else {
					$role->validate ();
				}
			}
		}

		/**
		 * Para obtener el id del usuario
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener el nombre por defecto del modulo
		 *
		 * @return string
		 */
		public function getDefaultModuleName () {
			return $this->defaultModuleName;
		}

		/**
		 * @return string
		 */
		public function getDefaultOperating () {
			return $this->defaultOperating;
		}
		
		/**
		 * @return string
		 */
		public function getDefaultHomeTab () {
			return $this->defaultHomeTab;
		}
		
		/**
		 * Para obtener el email asociado al usuario
		 *
		 * @return string
		 */
		public function getEmail () {
			return $this->email;
		}

		/**
		 * Para obtener el primer nombre del usuario
		 *
		 * @return string
		 */
		public function getFirstName () {
			return $this->firstName;
		}

		/**
		 * Para obtener el URI de la imagen se la asociara al usuario
		 *
		 * @return string
		 */
		public function getImageUri () {
			return $this->imageUri;
		}

		/**
		 * Para obtener el segundo nombre se le colocara al usuario
		 *
		 * @return string
		 */
		public function getLastName () {
			return $this->lastName;
		}

		/**
		 * Para obtener la contrasena del usuario
		 *
		 * @return string
		 */
		public function getPlainPassword () {
			return $this->plainPassword;
		}

		/**
		 * Para obtener el rol asociado al usuario
		 *
		 * @return Role[]
		 */
		public function getRoles () {
			return $this->roles;
		}

		/**
		 * Para obtener el estatus (activo o desactivo) del usuario
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Para obtener el nombre de usuario se le asignara
		 *
		 * @return string
		 */
		public function getUserName () {
			return $this->userName;
		}

		/**
		 * Para validar si el usuario tendra privilegios de administracion
		 *
		 * @return boolean
		 */
		public function isAdministrator () {
			return $this->administrator;
		}

		/**
		 * Establece el id del usuario
		 *
		 * @param integer $id
		 *
		 * @return User
		 */
		public function setId ($id) {
			if ((is_scalar ($id)) && (is_numeric ($id)) && ($id > 0)) {
				$this->id = $id;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * Establece el nombre por defecto del modulo
		 *
		 * @param string $defaultModuleName
		 *
		 * @return User
		 */
		public function setDefaultModuleName ($defaultModuleName) {
			if (is_scalar ($defaultModuleName)) {
				$this->defaultModuleName = $defaultModuleName;
			} else {
				$this->defaultModuleName = null;
			}
			return $this;
		}

		/**
		 * @param $defaultOperating
		 *
		 * @return User
		 */
		public function setDefaultOperating ($defaultOperating) {
			if (in_array ($defaultOperating, self::OPERATING_MODO)) {
				$this->defaultOperating = $defaultOperating;
			} else {
				$this->defaultOperating = 'MANAGEMENT_MODE';
			}
			return $this;
		}
		
		/**
		 * @param $defaultHomeTab
		 *
		 * @return User
		 */
		public function setDefaultHomeTab ($defaultHomeTab) {
			if (!empty($this->defaultOperating)) {
				if (in_array ($defaultHomeTab, self::HOME_TABS[ $this->defaultOperating ])) {
					$this->defaultHomeTab = $defaultHomeTab;
				} else {
					$this->defaultHomeTab = null;
				}
			} else {
				$this->defaultHomeTab = null;
			}
			return $this;
		}
		
		/**
		 * Establece el email asociado al usuario
		 *
		 * @param string $email
		 *
		 * @return User
		 */
		public function setEmail ($email) {
			if ((is_scalar ($email)) && (filter_var ($email, FILTER_VALIDATE_EMAIL) !== false)) {
				$this->email = $email;
			} else {
				$this->email = null;
			}
			return $this;
		}

		/**
		 * Establece el primer nombre del usuario
		 *
		 * @param string $firstName
		 *
		 * @return User
		 */
		public function setFirstName ($firstName) {
			if (is_scalar ($firstName)) {
				$this->firstName = $firstName;
			} else {
				$this->firstName = null;
			}
			return $this;
		}

		/**
		 * Establece la validacion para saber si el usuario tendra privilegios administrador
		 *
		 * @param boolean $administrator
		 *
		 * @return User
		 */
		public function setAdministrator ($administrator) {
			if (is_bool ($administrator)) {
				$this->administrator = $administrator;
			} else {
				$this->administrator = false;
			}
			return $this;
		}

		/**
		 * Establece el URI de la imagen se la asociara al usuario
		 *
		 * @param string $imageUri
		 *
		 * @return User
		 */
		public function setImageUri ($imageUri) {
			if (is_scalar ($imageUri)) {
				$this->imageUri = $imageUri;
			} else {
				$this->imageUri = null;
			}
			return $this;
		}

		/**
		 * Establece el segundo nombre se le colocara al usuario
		 *
		 * @param string $lastName
		 *
		 * @return User
		 */
		public function setLastName ($lastName) {
			if (is_scalar ($lastName)) {
				$this->lastName = $lastName;
			} else {
				$this->lastName = null;
			}
			return $this;
		}

		/**
		 * Para establecer la contrasena del usuario
		 *
		 * @param string $plainPassword
		 *
		 * @return User
		 */
		public function setPlainPassword ($plainPassword) {
			if (is_scalar ($plainPassword)) {
				$this->plainPassword = $plainPassword;
			} else {
				$this->plainPassword = null;
			}
			return $this;
		}

		/**
		 * Establece el rol asociado al usuario
		 *
		 * @param Role[] $roles
		 *
		 * @return User
		 */
		public function setRoles ($roles) {
			if (is_array ($roles)) {
				$this->roles = $roles;
			} else {
				$this->roles = null;
			}
			return $this;
		}

		/**
		 * Establece el estatus (activo o desactivo) del usuario
		 *
		 * @param string $status Estatus (activo o desactivo) del usuario
		 *
		 * @return User
		 */
		public function setStatus ($status) {
			if (in_array ($status, array (self::STATUS_ACTIVE, self::STATUS_INACTIVE))) {
				$this->status = $status;
			}
			return $this;
		}

		/**
		 * Establece el nombre de usuario se le asignara
		 *
		 * @param string $userName Nombre de usuario
		 *
		 * @return User
		 */
		public function setUserName ($userName) {
			if (is_scalar ($userName)) {
				$this->userName = $userName;
			} else {
				$this->userName = null;
			}
			return $this;
		}

		/**
		 * Valida que los atributos del usuario (nombre, segundo nombre y nombre de usuario) esten correctamente definidos
		 *
		 * @throws UserException
		 */
		public function validate () {
			if (empty ($this->email)) {
				throw new UserException (UserException::ERROR_USER_EMPTY_EMAIL);
			} else if (empty ($this->lastName)) {
				throw new UserException (UserException::ERROR_USER_EMPTY_LAST_NAME);
			} else if (empty ($this->userName)) {
				throw new UserException (UserException::ERROR_USER_EMPTY_USER_NAME);
			}
			$this->validateRoles ();
		}

		/**
		 * Instanciación de la clase User. Se obtiene un objeto User con los valores de la clase
		 *
		 * @return User
		 */
		public static function getInstance () {
			return new self ();
		}

	}

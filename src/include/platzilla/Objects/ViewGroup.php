<?php
	require_once ('include/platzilla/Exceptions/ViewException.php');
	require_once ('include/platzilla/Objects/ViewInterface.php');

	/**
	 * Class ViewGroup
	 *
	 * Esta clase "Vista Grupo" hace referencia a las vista que controla el aspecto visual de la lista de registros
	 * consultados a través de grupos de usuarios en las opciones personalizables de las vistas.
	 */
	class ViewGroup implements ViewInterface {

		/** @var integer */
		private $id;

		/** @var boolean */
		private $locked;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $name;

		/**
		 * Copia los valores de la vista según los parametros indicados
		 *
		 * @param ViewGroup $viewGroup
		 */
		public function copyValuesFrom ($viewGroup) {
			if ((empty ($viewGroup)) || (!($viewGroup instanceof ViewGroup))) {
				return;
			}
			$this->id         = $viewGroup->getId ();
			$this->locked     = $viewGroup->isLocked ();
			$this->moduleName = $viewGroup->getModuleName ();
			$this->name       = $viewGroup->getName ();
		}

		/**
		 * Duplica la vista de acuerdo con los parametros indicados
		 *
		 * @param null|integer $cvGroupId
		 *
		 * @return ViewGroup
		 */
		public function duplicate ($cvGroupId = null) {
			$object = new self ();
			return $object->setId ((empty($cvGroupId)) ? $this->id : $cvGroupId)
				->setLocked ($this->locked)
				->setModuleName ($this->moduleName)
				->setName ($this->name);
		}

		/**
		 * Para obtener el ID del filtro
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener el nombre del modulo donde actua la vista
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Obtiene el nombre de la vista
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para comparar si dos vistas son iguales
		 *
		 * @param ViewGroup $viewGroup
		 *
		 * @return boolean
		 */
		public function isEqualTo ($viewGroup) {
			if (
				(empty ($viewGroup)) ||
				($this->moduleName != $viewGroup->getModuleName ()) ||
				($this->name != $viewGroup->getName ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Indica si la vista esta bloqueada
		 *
		 * @return boolean
		 */
		public function isLocked () {
			return $this->locked;
		}

		/**
		 * Establece el id de la vista
		 *
		 * @param integer $id
		 *
		 * @return ViewGroup
		 */
		public function setId ($id) {
			if (!empty($id)) {
				$this->id = ($id);
			} else {
				$this->id = 0;
			}
			return $this;
		}

		/**
		 * Establece si la vista esta bloqueada
		 *
		 * @param boolean $locked
		 *
		 * @return ViewGroup
		 */
		public function setLocked ($locked) {
			if (is_bool ($locked)) {
				$this->locked = $locked;
			} else {
				$this->locked = false;
			}
			return $this;
		}

		/**
		 * Establece el nombre del modulo donde actuara la vista
		 *
		 * @param string $moduleName
		 *
		 * @return ViewGroup
		 */
		public function setModuleName ($moduleName) {
			$this->moduleName = $moduleName;
			return $this;
		}

		/**
		 * Establece el nombre de la vista
		 *
		 * @param string $name
		 *
		 * @return ViewGroup
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Valida que los atributos/valores (nombre y propietario) de la vista esten correctos
		 *
		 * @throws ViewException
		 */
		public function validate () {
			if (empty ($this->name)) {
				throw new ViewException (ViewException::ERROR_VIEW_EMPTY_GROUP_NAME);
			}
		}

		/**
		 * Instanciación de la clase ViewGroup. Se obtiene un objeto View con los valores de la clase
		 *
		 * @return ViewGroup
		 */
		public static function getInstance () {
			return new self ();
		}

	}

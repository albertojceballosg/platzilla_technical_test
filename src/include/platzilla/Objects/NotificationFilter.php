<?php
	require_once ('include/platzilla/Exceptions/NotificationException.php');
	require_once ('include/platzilla/Objects/NotificationInterface.php');

	/**
	 * Class NotificationFilter
	 *
	 * Esta clase hace referencia a la tabla vtiger_notifications_filters del m�dulo
	 */
	class NotificationFilter implements NotificationInterface, Serializable {

		/** @var integer */
		private $id;

		/** @var string[] */
		private $advancedFilter;

		/** @var string */
		private $columnPeriod;

		/** @var string */
		private $filterPeriod;

		/** @var string */
		private $moduleFilter;

		/** @var integer */
		private $recordId;
		
		/** @var string */
		private $sqlFilter;

		/** @var string[] */
		private $standardFilter;

		/** @var string[] */
		private $usersFilter;

		/**
		 * Valida los datos de usuarios
		 *
		 * @throws NotificationException
		 */
		private function validateUsers () {
			if (empty ($this->usersFilter)) {
				throw new NotificationException (NotificationException::ERROR_NOTIFICATION_EMPTY_USERS_NAMES);
			}
			foreach ($this->usersFilter as $user) {
				if (!is_numeric ($user)) {
					throw new NotificationException (NotificationException::ERROR_NOTIFICATION_INVALID_USERS_NAME);
				}
			}
		}

		/**
		 * Obtiene el ID de una notifiaci�n
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Copia un objeto NotificationFilter a partir de otro objeto NotificationFilter
		 *
		 * @param $notify
		 */
		public function copyValuesFrom ($notify) {
			if ((empty ($notify)) || (!($notify instanceof NotificationFilter))) {
				return;
			}
			$this->advancedFilter = $notify->getAdvancedFilter ();
			$this->columnPeriod   = $notify->getColumnPeriod ();
			$this->filterPeriod   = $notify->getFilterPeriod ();
			$this->moduleFilter   = $notify->getModuleFilter ();
			$this->sqlFilter      = $notify->getSqlFilter ();
			$this->standardFilter = $notify->getStandardFilter ();
			$this->usersFilter    = $notify->getUsersFilter ();
		}

		/**
		 * Duplica un objeto NotificationFilter
		 *
		 * @param integer $newNotificationId
		 *
		 * @return NotificationFilter
		 */
		public function duplicate ($newNotificationId = null) {
			$object = new self ();
			return $object->setId ($newNotificationId)
				->setAdvancedFilter ($this->advancedFilter)
				->setColumnPeriod ($this->columnPeriod)
				->setFilterPeriod ($this->filterPeriod)
				->setModuleFilter ($this->moduleFilter)
				->setSqlFilter ($this->sqlFilter)
				->setStandardFilter ($this->standardFilter)
				->setUsersFilter ($this->usersFilter);
		}

		/**
		 * Obtiene la estructura de datos de los filtros de una notifiaci�n
		 *
		 * @return string[]
		 */
		public function getAdvancedFilter () {
			return $this->advancedFilter;
		}

		/**
		 * Obtiene los campos que aplica el filtros temporales de uan notificaci�n
		 *
		 * @return string
		 */
		public function getColumnPeriod () {
			return $this->columnPeriod;
		}

		/**
		 * Obtiene el filtro temporal de una notifiaci�n
		 *
		 * @return string
		 */
		public function getFilterPeriod () {
			return $this->filterPeriod;
		}

		/**
		 * Obtiene los m�dulos donde se mostrar� la notifiaci�n
		 *
		 * @return string
		 */
		public function getModuleFilter () {
			return $this->moduleFilter;
		}
		
		/**
		 * @return integer
		 */
		public function getRecordId () {
			return $this->recordId;
		}
		
		/**
		 * Obtiene la estructura sql del filtro avanzado
		 *
		 * @return string
		 */
		public function getSqlFilter () {
			return $this->sqlFilter;
		}

		/**
		 * Obtiene la estructura del filtro estandar
		 *
		 * @return string[]
		 */
		public function getStandardFilter () {
			return $this->standardFilter;
		}

		/**
		 * Obtiene estructura de filtro por usaurios
		 *
		 * @return string[]
		 */
		public function getUsersFilter () {
			return $this->usersFilter;
		}

		/**
		 * Compara dos objetos NotificationFilter
		 *
		 * @param NotificationFilter $notify
		 *
		 * @return boolean
		 */
		public function isEqualTo ($notify) {
			if (
				(empty ($notify)) ||
				(!($notify instanceof NotificationFilter)) ||
				($this->advancedFilter != $notify->getAdvancedFilter ()) ||
				($this->columnPeriod != $notify->getColumnPeriod ()) ||
				($this->filterPeriod != $notify->getFilterPeriod ()) ||
				($this->moduleFilter != $notify->getModuleFilter ()) ||
				($this->sqlFilter != $notify->getSqlFilter ()) ||
				($this->standardFilter != $notify->getStandardFilter ()) ||
				($this->usersFilter != $notify->getUsersFilter ())
			) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Devuelve una cadena serializada de los valores de las propiedades
		 *
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->id,
					$this->advancedFilter,
					$this->columnPeriod,
					$this->filterPeriod,
					$this->moduleFilter,
					$this->standardFilter,
					$this->usersFilter,
				)
			);
		}

		/**
		 * Establece el Id del registro de filtro de una notifiaci�n
		 *
		 * @param integer $id
		 *
		 * @return NotificationFilter
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
		 * Establece la estructura de datos de un filtro avanzado de una otifiaci�n
		 *
		 * @param string $filter
		 *
		 * @return NotificationFilter
		 */
		public function setAdvancedFilter ($filter) {
			if (is_scalar ($filter)) {
				$this->advancedFilter = $filter;
			} else {
				$this->advancedFilter = null;
			}
			return $this;
		}

		/**
		 * Establece el campo donde se aplicar� el filtro temporal de uan notifiaci�n
		 *
		 * @param string $field
		 *
		 * @return NotificationFilter
		 */
		public function setColumnPeriod ($field) {
			if (is_scalar ($field)) {
				$this->columnPeriod = $field;
			} else {
				$this->columnPeriod = null;
			}
			return $this;
		}

		/**
		 * Establece la estructura de dato del filtro temporal
		 *
		 * @param string $period
		 *
		 * @return NotificationFilter
		 */
		public function setFilterPeriod ($period) {
			if (is_scalar ($period)) {
				$this->filterPeriod = $period;
			} else {
				$this->filterPeriod = null;
			}
			return $this;
		}

		/**
		 * Establece la estructura de datos para filtro de m�dulos
		 *
		 * @param string $moduleName
		 *
		 * @return NotificationFilter
		 */
		public function setModuleFilter ($moduleName) {
			if (is_scalar ($moduleName)) {
				$this->moduleFilter = $moduleName;
			} else {
				$this->moduleFilter = null;
			}
			return $this;
		}
		
		/**
		 * @param integer $recordId
		 *
		 * @return NotificationFilter
		 */
		public function setRecordId ($recordId) {
			$this->recordId = $recordId;
			return $this;
		}
		
		/**
		 * Establece la estructura de datos para el SQl de filtro avanzado
		 *
		 * @param string $sql
		 *
		 * @return NotificationFilter
		 */
		public function setSqlFilter ($sql) {
			if (is_scalar ($sql)) {
				$this->sqlFilter = $sql;
			} else {
				$this->sqlFilter = null;
			}
			return $this;
		}

		/**
		 * Establece la estructura de datos para el SQl de filtro estandar
		 *
		 * @param string $filter
		 *
		 * @return NotificationFilter
		 */
		public function setStandardFilter ($filter) {
			if (is_scalar ($filter)) {
				$this->standardFilter = $filter;
			} else {
				$this->standardFilter = null;
			}
			return $this;
		}

		/**
		 * Establece la estructura de datos para filtro de usuarios
		 *
		 * @param string $users
		 *
		 * @return NotificationFilter
		 */
		public function setUsersFilter ($users) {
			if ((is_array ($users)) && (!empty ($users))) {
				$this->usersFilter = $users;
			} else {
				$this->usersFilter = null;
			}
			return $this;
		}

		/**
		 * Devuelve los valores de las propiedades a partir de una cadena serializada
		 *
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->advancedFilter,
				$this->columnPeriod,
				$this->filterPeriod,
				$this->moduleFilter,
				$this->standardFilter,
				$this->usersFilter,
				) = unserialize ($serialized);
		}

		/**
		 * Valida datos de usuarios
		 *
		 * @throws NotificationException
		 */
		public function validate () {
			$this->validateUsers ();
		}

		/**
		 * Instanciaci�n de la clase NotificationFilter. Se obtiene un objeto NotificationFilter con los atributos de la clase
		 *
		 * @return NotificationFilter
		 */
		public static function getInstance () {
			return new self ();
		}

	}

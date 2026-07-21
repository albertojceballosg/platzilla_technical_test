<?php
	require_once ('include/platzilla/Objects/ModuleSubscriptionInterface.php');

	/**
	 * Class ModuleSubscription
	 *
	 * En esta clase se define el objeto "Suscripción Modulo" el cual hace referencia a los módulos que un usuario puede dar de alta en una Aplicación.
	 **/
	class ModuleSubscription implements ModuleSubscriptionInterface {
		/** @var integer */
		private $maxRecords;

		/** @var string */
		private $moduleLabel;

		/** @var string */
		private $moduleName;

		/** @var string */
		private $status;

		/** @var integer */
		private $totalRecords;

		/**
		 * Para obtener la cantidad maxima de registros que puede crearse en el modulo en la capa gratuita
		 *
		 * @return integer
		 */
		public function getMaxRecords () {
			return $this->maxRecords;
		}

		/**
		 * Para obtener la etiqueta del modulo
		 *
		 * @return string
		 */
		public function getModuleLabel () {
			return $this->moduleLabel;
		}

		/**
		 * Para obtener el nombre del modulo que se puede dar de alta en una aplicacion
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Para obtener el estatus (activo o desactivo) del modulo que se puede dar de alta en una aplicacion
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Para obtener el total de registros creados en el modulo en la capa gratuita
		 *
		 * @return integer
		 */
		public function getTotalRecords () {
			return $this->totalRecords;
		}

		/**
		 * Establece la cantidad maxima de registros que puede crearse en el modulo en la capa gratuita
		 *
		 * @param integer $maxRecords
		 *
		 * @return ModuleSubscription
		 */
		public function setMaxRecords ($maxRecords) {
			if ((is_numeric ($maxRecords)) && ($maxRecords >= -1) && (intval ($maxRecords) == $maxRecords)) {
				$this->maxRecords = intval ($maxRecords);
			} else {
				$this->maxRecords = null;
			}
			return $this;
		}

		/**
		 * Establece la etiqueta del modulo suscrita
		 *
		 * @param string $moduleLabel
		 *
		 * @return ModuleSubscription
		 */
		public function setModuleLabel ($moduleLabel) {
			if (is_scalar ($moduleLabel)) {
				$this->moduleLabel = $moduleLabel;
			} else {
				$this->moduleLabel = null;
			}
			return $this;
		}

		/**
		 * Establece el nombre del modulo que se puede dar de alta en una aplicacion
		 *
		 * @param string $moduleName
		 *
		 * @return ModuleSubscription
		 */
		public function setModuleName ($moduleName) {
			if (is_scalar ($moduleName)) {
				$this->moduleName = $moduleName;
			} else {
				$this->moduleName = null;
			}
			return $this;
		}

		/**
		 * Establece el estatus (activo o desactivo) del modulo que se puede dar de alta en una aplicacion
		 *
		 * @param string $status
		 *
		 * @return ModuleSubscription
		 */
		public function setStatus ($status) {
			if (in_array ($status, self::getAvailableStatuses (), true)) {
				$this->status = $status;
			} else {
				$this->status = null;
			}
			return $this;
		}

		/**
		 * Establece el total de registros creados en el modulo en la capa gratuita
		 *
		 * @param integer $totalRecords
		 *
		 * @return ModuleSubscription
		 */
		public function setTotalRecords ($totalRecords) {
			if ((is_numeric ($totalRecords)) && ($totalRecords >= 0) && (intval ($totalRecords) == $totalRecords)) {
				$this->totalRecords = intval ($totalRecords);
			} else {
				$this->totalRecords = null;
			}
			return $this;
		}

		/**
		 * Para obtener los estatus (activo, inactivo o suscrito) que puede tomar un modulo en uno de los planes
		 *
		 * @return string[]
		 */
		public static function getAvailableStatuses () {
			return array (self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_SUBSCRIBED);
		}

		/**
		 * Instanciación de la clase ModuleSubscription. Se obtiene un objeto ModuleSubscription con los atributos de la clase
		 *
		 * @return ModuleSubscription
		 */
		public static function getInstance () {
			return new self ();
		}

	}

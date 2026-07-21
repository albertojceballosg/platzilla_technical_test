<?php
	require_once ('include/platzilla/Exceptions/PlatformFreeBillingPlanLimitException.php');

	/**
	 * Class PlatformFreeBillingPlanLimit
	 *
	 * En esta clase se define el objeto "Plan Fremium Plataforma" el cual hace referencia a los limites disponibles para que los usuarios puedan dar de alta las "Instancias" gratuitamente.
	 **/
	class PlatformFreeBillingPlanLimit {
		/** @var integer */
		private $maxRecords;

		/** @var string */
		private $moduleLabel;

		/** @var string */
		private $moduleName;

		/**
		 * Para obtener el limite máximo de registros que puede el usuario insertar en los módulos empleando platzilla de manera gratuita
		 *
		 * @return integer
		 */
		public function getMaxRecords () {
			return $this->maxRecords;
		}

		/**
		 * Para obtener la etiqueta asignada al módulo dentro del plan free
		 *
		 * @return string
		 */
		public function getModuleLabel () {
			return $this->moduleLabel;
		}

		/**
		 * Para obtener el nombre del módulo asignada al módulo dentro del plan free
		 *
		 * @return string
		 */
		public function getModuleName () {
			return $this->moduleName;
		}

		/**
		 * Establece la etiqueta asignada al módulo dentro del plan free
		 *
		 * @param string $moduleLabel
		 *
		 * @return PlatformFreeBillingPlanLimit
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
		 * Establece el limite máximo de registros que puede el usuario insertar en los módulos empleando platzilla de manera gratuita
		 *
		 * @param integer $maxRecords
		 *
		 * @return PlatformFreeBillingPlanLimit
		 */
		public function setMaxRecords ($maxRecords) {
			if ((is_numeric ($maxRecords)) && ($maxRecords >= -1) && (intval ($maxRecords) == $maxRecords)) {
				$this->maxRecords = $maxRecords;
			} else {
				$this->maxRecords = null;
			}
			return $this;
		}

		/**
		 * Establece el nombre del módulo asignada al módulo dentro del plan free
		 *
		 * @param string $moduleName
		 *
		 * @return PlatformFreeBillingPlanLimit
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
		 * Valida que no puede insertar más registros en los módulos, pues ha llegado al limite establecido en la capa gratuita
		 *
		 * @throws PlatformFreeBillingPlanLimitException
		 */
		public function validate () {
			if ($this->maxRecords === null) {
				throw new PlatformFreeBillingPlanLimitException (PlatformFreeBillingPlanLimitException::ERROR_FREE_BILLING_PLAN_LIMIT_EMPTY_MAX_RECORDS);
			} else if (empty ($this->moduleName)) {
				throw new PlatformFreeBillingPlanLimitException (PlatformFreeBillingPlanLimitException::ERROR_FREE_BILLING_PLAN_LIMIT_EMPTY_MODULE_NAME);
			}
		}

		/**
		 * Instanciación de la clase PlatformFreeBillingPlanLimit. Se obtiene un objeto PlatformFreeBillingPlanLimit con los atributos de la clase.
		 *
		 * @return PlatformFreeBillingPlanLimit
		 */
		public static function getInstance () {
			return new self ();
		}

	}

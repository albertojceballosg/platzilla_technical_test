<?php
	require_once ('include/platzilla/Exceptions/PlatformBillingPlanException.php');
	require_once ('include/platzilla/Objects/PlatformBillingPlanInterface.php');
	require_once ('include/platzilla/Objects/Product.php');

	/**
	 * Class PlatformBillingPlan
	 *
	 * En esta clase se define el objeto "Plan de Pago Plataforma" el cual hace referencia los planes de pago disponibles para que los usuarios puedan dar de alta las "Instancias".
	 **/
	class PlatformBillingPlan implements PlatformBillingPlanInterface, Serializable {
		/** @var integer */
		private $id;

		/** @var string */
		private $description;

		/** @var string */
		private $name;

		/** @var Product */
		private $product;

		/** @var string */
		private $status;

		/** @var integer */
		private $totalApplications;

		/** @var integer */
		private $totalDiskSpace;

		/** @var integer */
		private $totalUsers;

		/**
		 * Para obtener el ID del plan de pago que posee actualmente la instancia
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Para obtener la descripción del plan de pago que posee actualmente la instancia
		 *
		 * @return string
		 */
		public function getDescription () {
			return $this->description;
		}

		/**
		 * Para obtener el nombre del plan de pago que posee actualmente la instancia
		 *
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Para obtener el nombre del o los productos contratados dentro del plan de pagos
		 *
		 * @return Product
		 */
		public function getProduct () {
			return $this->product;
		}

		/**
		 * Para obtener el estatus de la instancia, siendo paga ó vencida
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Para obtener el total de aplicaciones contratadas en el plan suscrito en la instancia
		 *
		 * @return integer
		 */
		public function getTotalApplications () {
			return $this->totalApplications;
		}

		/**
		 * Para obtener el tamaño actual de espacio disponible en el plan contratado en la instancia
		 *
		 * @return integer
		 */
		public function getTotalDiskSpace () {
			return $this->totalDiskSpace;
		}

		/**
		 * Para obtener el total de usuarios disponibles en el plan contratado en la instancia
		 *
		 * @return integer
		 */
		public function getTotalUsers () {
			return $this->totalUsers;
		}

		/**
		 * Establece el id del plan de pago asociado a la instancia
		 *
		 * @param integer $id
		 *
		 * @return PlatformBillingPlan
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0) && (intval ($id) == $id)) {
				$this->id = intval ($id);
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * Establece la descripción del plan de pago que posee actualmente la instancia
		 *
		 * @param string $description
		 *
		 * @return PlatformBillingPlan
		 */
		public function setDescription ($description) {
			if (is_scalar ($description)) {
				$this->description = $description;
			} else {
				$this->description = null;
			}
			return $this;
		}

		/**
		 * Establece el nombre del plan de pago que posee actualmente la instancia
		 *
		 * @param string $name
		 *
		 * @return PlatformBillingPlan
		 */
		public function setName ($name) {
			if (is_scalar ($name)) {
				$this->name = $name;
			} else {
				$this->name = null;
			}
			return $this;
		}

		/**
		 * Establece el estatus actual en que se encuentra la instancia
		 *
		 * @param string $status
		 *
		 * @return PlatformBillingPlan
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
		 * Establece el nombre del o los productos contratados dentro del plan de pagos
		 *
		 * @param Product $product
		 *
		 * @return PlatformBillingPlan
		 */
		public function setProduct ($product) {
			if ($product instanceof Product) {
				$this->product = $product;
			} else {
				$this->product = null;
			}
			return $this;
		}

		/**
		 * Establece el total de aplicaciones contratadas en el plan suscrito en la instancia
		 *
		 * @param integer $totalApplications
		 *
		 * @return PlatformBillingPlan
		 */
		public function setTotalApplications ($totalApplications) {
			if ((is_numeric ($totalApplications)) && ($totalApplications >= -1) && (intval ($totalApplications) == $totalApplications)) {
				$this->totalApplications = intval ($totalApplications);
			} else {
				$this->totalApplications = null;
			}
			return $this;
		}

		/**
		 * Establece el tamaño actual de espacio disponible en el plan contratado en la instancia
		 *
		 * @param float $totalDiskSpace
		 *
		 * @return PlatformBillingPlan
		 */
		public function setTotalDiskSpace ($totalDiskSpace) {
			if ((is_numeric ($totalDiskSpace)) && (($totalDiskSpace == -1) || ($totalDiskSpace >= 0))) {
				$this->totalDiskSpace = floatval ($totalDiskSpace);
			} else {
				$this->totalDiskSpace = null;
			}
			return $this;
		}

		/**
		 * Establece el total de usuarios disponibles en el plan contratado en la instancia
		 *
		 * @param integer $totalUsers
		 *
		 * @return PlatformBillingPlan
		 */
		public function setTotalUsers ($totalUsers) {
			if ((is_numeric ($totalUsers)) && ($totalUsers >= -1) && (intval ($totalUsers) == $totalUsers)) {
				$this->totalUsers = intval ($totalUsers);
			} else {
				$this->totalUsers = null;
			}
			return $this;
		}

		/**
		 * Para validar que los parametros suficientes (administrador, usuarios, aplicaciones, módulos) del Plan de pago de la instancia esten correctos.
		 *
		 * @throws PlatformBillingPlanException
		 * NOTA: PHP Code Sniffer detecta una violación de complejidad ciclomática (11) por la cantidad de comparaciones que hay. Todas son necesarias.
		 * Adicionalmente, la ganancia de dividir el método en varios métodos privados no parece suficiente en comparación con la ganancia en legibilidad
		 * @codingStandardsIgnoreStart
		 */
		public function validate () {
			if (empty ($this->description)) {
				throw new PlatformBillingPlanException (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_DESCRIPTION);
			} else if (empty ($this->name)) {
				throw new PlatformBillingPlanException (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_NAME);
			} else if (empty ($this->product)) {
				throw new PlatformBillingPlanException (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_PRODUCT);
			} else if (empty ($this->status)) {
				throw new PlatformBillingPlanException (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_STATUS);
			} else if ($this->totalApplications === null) {
				throw new PlatformBillingPlanException (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_TOTAL_APPLICATIONS);
			} else if ($this->totalDiskSpace === null) {
				throw new PlatformBillingPlanException (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_TOTAL_DISK_SPACE);
			} else if ($this->totalUsers === null) {
				throw new PlatformBillingPlanException (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_TOTAL_USERS);
			} else {
				$this->product->validate ();
			}

		}
		// @codingStandardsIgnoreEnd

		/**
		 * Para generar el serial de los productos contratados en el plan de pago se asociara a la instancia
		 *
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->id,
					$this->description,
					$this->name,
					$this->status,
					$this->totalApplications,
					$this->totalDiskSpace,
					$this->totalUsers,
					!empty ($this->product) ? $this->product->serialize () : null,
				)
			);
		}

		/**
		 * Para setear el serial asignado a los productos contratados en el plan de pago se asociara a la instancia
		 *
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->id,
				$this->description,
				$this->name,
				$this->status,
				$this->totalApplications,
				$this->totalDiskSpace,
				$this->totalUsers,
				$serializedProduct,
				) = unserialize ($serialized);

			if (!empty ($serializedProduct)) {
				$this->product = Product::getInstance ();
				$this->product->unserialize ($serializedProduct);
			} else {
				$this->product = null;
			}
		}

		/**
		 * Instanciación de la clase PlatformBillingPlan. Se obtiene un objeto PlatformBillingPlan con los atributos de la clase
		 *
		 * @return PlatformBillingPlan
		 */
		public static function getInstance () {
			return new self ();
		}

		/**
		 * Obtiene los estatus disponibles (Activo o Inactivo) de la instancia
		 *
		 * @return string[]
		 */
		public static function getAvailableStatuses () {
			return array (self::STATUS_ACTIVE, self::STATUS_INACTIVE);
		}

	}

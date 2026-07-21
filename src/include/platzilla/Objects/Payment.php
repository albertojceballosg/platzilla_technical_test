<?php
	require_once ('include/platzilla/Exceptions/PaymentException.php');
	require_once ('include/platzilla/Objects/PaymentInterface.php');
	require_once ('include/platzilla/Objects/Product.php');
	require_once ('include/platzilla/Objects/Tax.php');

	class Payment implements PaymentInterface {
		/** @var DateTime */
		private $dueDate;

		/** @var string */
		private $id;

		/** @var string */
		private $instanceCode;

		/** @var integer */
		private $invoiceId;

		/** @var string */
		private $lastErrorMessage;

		/** @var integer */
		private $productId;

		/** @var string */
		private $productName;

		/** @var DateTime */
		private $serviceEndDate;

		/** @var DateTime */
		private $serviceStartDate;

		/** @var string */
		private $status;

		/** @var float */
		private $subTotal;

		/** @var float */
		private $taxPercentage;

		/** @var string */
		private $type;

		/**
		 * @param Product $product
		 */
		public function __construct ($product = null) {
			if ($product instanceof Product) {
				$this->productId     = $product->getId ();
				$this->productName   = $product->getName ();
				$this->subTotal      = $product->getPriceBeforeTax ();
				$this->taxPercentage = !empty ($product->getTax ()) ? $product->getTax ()->getPercentage () : 0.0;
			}
		}

		/**
		 * Obtiene la fecha de vencimiento
		 *
		 * @return DateTime
		 */
		public function getDueDate () {
			return $this->dueDate;
		}

		/**
		 * Obtiene el ID del pago en la pasarela de pagos
		 *
		 * @return string
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Obtiene el código de la instancia
		 *
		 * @return string
		 */
		public function getInstanceCode () {
			return $this->instanceCode;
		}

		/**
		 * @return integer
		 */
		public function getInvoiceId () {
			return $this->invoiceId;
		}

		/**
		 * Obtiene el último mensaje de error de la pasarela de pagos
		 *
		 * @return string
		 */
		public function getLastErrorMessage () {
			return $this->lastErrorMessage;
		}

		/**
		 * @return integer
		 */
		public function getProductId () {
			return $this->productId;
		}

		/**
		 * @return string
		 */
		public function getProductName () {
			return $this->productName;
		}

		/**
		 * @return DateTime
		 */
		public function getServiceEndDate () {
			return $this->serviceEndDate;
		}

		/**
		 * @return DateTime
		 */
		public function getServiceStartDate () {
			return $this->serviceStartDate;
		}

		/**
		 * Obtiene el status del pago
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * @return float
		 */
		public function getSubTotal () {
			return $this->subTotal;
		}

		/**
		 * @return float
		 */
		public function getTaxPercentage () {
			return $this->taxPercentage;
		}

		/**
		 * Obtiene el tipo de pago
		 *
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * Establece la fecha de vencimiento
		 *
		 * @param string|DateTime $dueDate
		 *
		 * @return Payment
		 */
		public function setDueDate ($dueDate) {
			if ((!empty ($dueDate)) && (is_scalar ($dueDate))) {
				$dummy = date_create ($dueDate);
			} else if ($dueDate instanceof DateTime) {
				$dummy = $dueDate;
			} else {
				$dummy = null;
			}

			if (!empty ($dummy)) {
				$this->dueDate = $dummy;
			} else {
				$this->dueDate = null;
			}
			return $this;
		}

		/**
		 * Establece el ID del pago en la pasarela de pagos
		 *
		 * @param string $id
		 *
		 * @return Payment
		 */
		public function setId ($id) {
			if (is_scalar ($id)) {
				$this->id = $id;
			} else {
				$this->id = null;
			}
			return $this;
		}

		/**
		 * @param integer $invoiceId
		 *
		 * @return Payment
		 */
		public function setInvoiceId ($invoiceId) {
			if ((is_numeric ($invoiceId)) && ($invoiceId > 0) && (intval ($invoiceId) == $invoiceId)) {
				$this->invoiceId = $invoiceId;
			} else {
				$this->invoiceId = null;
			}
			return $this;
		}

		/**
		 * Establece el código de la instancia
		 *
		 * @param string $instanceCode
		 *
		 * @return Payment
		 */
		public function setInstanceCode ($instanceCode) {
			if (is_scalar ($instanceCode)) {
				$this->instanceCode = $instanceCode;
			} else {
				$this->instanceCode = null;
			}
			return $this;
		}

		/**
		 * Establece el último mensaje de error
		 *
		 * @param string $lastErrorMessage
		 *
		 * @return Payment
		 */
		public function setLastErrorMessage ($lastErrorMessage) {
			if (is_scalar ($lastErrorMessage)) {
				$this->lastErrorMessage = $lastErrorMessage;
			} else {
				$this->lastErrorMessage = null;
			}
			return $this;
		}

		/**
		 * @param integer $productId
		 *
		 * @return Payment
		 */
		public function setProductId ($productId) {
			if ((is_numeric ($productId)) && ($productId > 0) && (intval ($productId) == $productId)) {
				$this->productId = $productId;
			} else {
				$this->productId = null;
			}
			return $this;
		}

		/**
		 * @param string $productName
		 *
		 * @return Payment
		 */
		public function setProductName ($productName) {
			if (is_scalar ($productName)) {
				$this->productName = $productName;
			} else {
				$this->productName = null;
			}
			return $this;
		}

		/**
		 * @param DateTime $serviceEndDate
		 *
		 * @return Payment
		 */
		public function setServiceEndDate ($serviceEndDate) {
			if ((!empty ($serviceEndDate)) && (is_scalar ($serviceEndDate))) {
				$dummy = date_create ($serviceEndDate);
			} else if ($serviceEndDate instanceof DateTime) {
				$dummy = $serviceEndDate;
			} else {
				$dummy = null;
			}

			if (!empty ($dummy)) {
				$this->serviceEndDate = $dummy;
			} else {
				$this->serviceEndDate = null;
			}
			return $this;
		}

		/**
		 * @param DateTime $serviceStartDate
		 *
		 * @return Payment
		 */
		public function setServiceStartDate ($serviceStartDate) {
			if ((!empty ($serviceStartDate)) && (is_scalar ($serviceStartDate))) {
				$dummy = date_create ($serviceStartDate);
			} else if ($serviceStartDate instanceof DateTime) {
				$dummy = $serviceStartDate;
			} else {
				$dummy = null;
			}

			if (!empty ($dummy)) {
				$this->serviceStartDate = $dummy;
			} else {
				$this->serviceStartDate = null;
			}
			return $this;
		}

		/**
		 * Establece el status
		 *
		 * @param string $status
		 *
		 * @return Payment
		 */
		public function setStatus ($status) {
			$availableStatuses = self::getAvailableStatuses ();
			if (in_array ($status, $availableStatuses, true)) {
				$this->status = $status;
			} else {
				$this->status = null;
			}
			return $this;
		}

		/**
		 * @param float $subTotal
		 *
		 * @return Payment
		 */
		public function setSubTotal ($subTotal) {
			if ((is_numeric ($subTotal)) && ($subTotal >= 0)) {
				$this->subTotal = floatval ($subTotal);
			} else {
				$this->subTotal = null;
			}
			return $this;
		}

		/**
		 * @param float $taxPercentage
		 *
		 * @return Payment
		 */
		public function setTaxPercentage ($taxPercentage) {
			if ((is_numeric ($taxPercentage)) && ($taxPercentage >= 0)) {
				$this->taxPercentage = floatval ($taxPercentage);
			} else {
				$this->taxPercentage = null;
			}
			return $this;
		}

		/**
		 * Establece el tipo
		 *
		 * @param string $type
		 *
		 * @return Payment
		 */
		public function setType ($type) {
			$availableTypes = self::getAvailableTypes ();
			if (in_array ($type, $availableTypes, true)) {
				$this->type = $type;
			} else {
				$this->type = null;
			}
			return $this;
		}

		// Utils

		/**
		 * @return boolean
		 */
		public function isPaid () {
			return in_array ($this->status, array (self::STATUS_PAID, self::STATUS_SUBMITTED));
		}

		/**
		 * @return boolean
		 */
		public function isPending () {
			return in_array ($this->status, array (self::STATUS_PENDING, self::STATUS_PAST_DUE));
		}

		/**
		 * @return float
		 */
		public function getTaxAmount () {
			$subTotal      = !empty ($this->subTotal) ? $this->subTotal : 0.0;
			$taxPercentage = !empty ($this->taxPercentage) ? $this->taxPercentage : 0.0;
			return ($subTotal * $taxPercentage / 100);
		}

		/**
		 * @return float
		 */
		public function getTotalAmount () {
			$subTotal      = !empty ($this->subTotal) ? $this->subTotal : 0.0;
			$taxPercentage = !empty ($this->taxPercentage) ? $this->taxPercentage : 0.0;
			return ($subTotal * (1 + ($taxPercentage / 100)));
		}

		/**
		 * Valida que el objeto tenga todos los valores requeridos
		 *
		 * @throws PaymentException
		 * NOTA: PHP Code Sniffer detecta una violación de complejidad ciclomática (11) por la cantidad de validaciones que hay. Todas son necesarias.
		 * Adicionalmente, la ganancia de dividir el método en varios métodos privados no parece suficiente en comparación con la ganancia en legibilidad
		 * @codingStandardsIgnoreStart
		 */
		public function validate () {
			if (empty ($this->id)) {
				throw new PaymentException (PaymentException::ERROR_PAYMENT_EMPTY_ID);
			} else if (empty ($this->dueDate)) {
				throw new PaymentException (PaymentException::ERROR_PAYMENT_EMPTY_DUE_DATE);
			} else if (empty ($this->instanceCode)) {
				throw new PaymentException (PaymentException::ERROR_PAYMENT_EMPTY_INSTANCE_CODE);
			} else if (empty ($this->productId)) {
				throw new PaymentException (PaymentException::ERROR_PAYMENT_EMPTY_PRODUCT_ID);
			} else if (empty ($this->productName)) {
				throw new PaymentException (PaymentException::ERROR_PAYMENT_EMPTY_PRODUCT_NAME);
			} else if (empty ($this->serviceEndDate)) {
				throw new PaymentException (PaymentException::ERROR_PAYMENT_EMPTY_SERVICE_END_DATE);
			} else if (empty ($this->serviceStartDate)) {
				throw new PaymentException (PaymentException::ERROR_PAYMENT_EMPTY_SERVICE_START_DATE);
			} else if (empty ($this->status)) {
				throw new PaymentException (PaymentException::ERROR_PAYMENT_EMPTY_STATUS);
			} else if ($this->subTotal === null) {
				throw new PaymentException (PaymentException::ERROR_PAYMENT_EMPTY_SUB_TOTAL);
			} else if (empty ($this->type)) {
				throw new PaymentException (PaymentException::ERROR_PAYMENT_EMPTY_TYPE);
			}
		}
		// @codingStandardsIgnoreEnd

		// Static utils

		/**
		 * Obtiene todos los status permitidos
		 *
		 * @return string[]
		 */
		public static function getAvailableStatuses () {
			return array (self::STATUS_CANCELLED, self::STATUS_PAST_DUE, self::STATUS_PAID, self::STATUS_PENDING, self::STATUS_REJECTED, self::STATUS_SUBMITTED);
		}

		/**
		 * Obtiene todos los tipos permitidos
		 *
		 * @return string[]
		 */
		public static function getAvailableTypes () {
			return array (self::TYPE_SUBSCRIPTION, self::TYPE_TRANSACTION);
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableCompletedStatuses () {
			return array (self::STATUS_PAID, self::STATUS_SUBMITTED);
		}

		/**
		 * @return string[]
		 */
		public static function getAvailablePendingStatuses () {
			return array (self::STATUS_PAST_DUE, self::STATUS_PENDING, self::STATUS_REJECTED);
		}

		/**
		 * @param Product $product
		 *
		 * @return Payment
		 */
		public static function getInstance ($product = null) {
			return new self ($product);
		}

	}

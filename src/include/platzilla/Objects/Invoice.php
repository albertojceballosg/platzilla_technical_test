<?php
	require_once ('include/platzilla/Exceptions/InvoiceException.php');
	require_once ('include/platzilla/Objects/InvoiceItem.php');

	/**
	 * Representa una factura de servicios
	 */
	class Invoice {
		/** @var integer */
		private $accountId;

		/** @var DateTime */
		private $creationDate;

		/** @var DateTime */
		private $dueDate;

		/** @var integer */
		private $id;

		/** @var string */
		private $instanceCode;

		/** @var InvoiceItem[] */
		private $items;

		/** @var string */
		private $number;

		/** @var string */
		private $status;

		/** @var string */
		private $subject;

		// Getters

		/**
		 * Obtiene el ID de la cuenta
		 *
		 * @return integer
		 */
		public function getAccountId () {
			return $this->accountId;
		}

		/**
		 * Obtiene la fecha de creación
		 *
		 * @return DateTime
		 */
		public function getCreationDate () {
			return $this->creationDate;
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
		 * Obtiene el ID de la factura
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * Obtiene el código de la instancia del cliente al cual va dirigida la factura
		 *
		 * @return string
		 */
		public function getInstanceCode () {
			return $this->instanceCode;
		}

		/**
		 * Obtiene la lista de items
		 *
		 * @return InvoiceItem[]
		 */
		public function getItems () {
			return $this->items;
		}

		/**
		 * @return string
		 */
		public function getNumber () {
			return $this->number;
		}

		/**
		 * Obtiene el status
		 *
		 * @return string
		 */
		public function getStatus () {
			return $this->status;
		}

		/**
		 * Obtiene el asunto
		 *
		 * @return string
		 */
		public function getSubject () {
			return $this->subject;
		}

		/**
		 * Establece el ID de la cuenta
		 *
		 * @param integer $accountId
		 *
		 * @return Invoice
		 */
		public function setAccountId ($accountId) {
			if ((is_numeric ($accountId)) && ($accountId > 0) && (intval ($accountId) == $accountId)) {
				$this->accountId = intval ($accountId);
			} else {
				$this->accountId = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha de creación
		 *
		 * @param DateTime $creationDate
		 *
		 * @return Invoice
		 */
		public function setCreationDate ($creationDate) {
			if ((!empty ($creationDate)) && (is_scalar ($creationDate))) {
				$dummy = date_create ($creationDate);
			} else if ($creationDate instanceof DateTime) {
				$dummy = $creationDate;
			} else {
				$dummy = null;
			}

			if (!empty ($dummy)) {
				$this->creationDate = $dummy;
			} else {
				$this->creationDate = null;
			}
			return $this;
		}

		/**
		 * Establece la fecha de vencimiento
		 *
		 * @param DateTime $dueDate
		 *
		 * @return Invoice
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
		 * Establece el ID de la factura
		 *
		 * @param integer $id
		 *
		 * @return Invoice
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
		 * Establece el código de la instancia del cliente al cual va dirigida la factura
		 *
		 * @param string $instanceCode
		 *
		 * @return Invoice
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
		 * Establece los items de la factura
		 *
		 * @param InvoiceItem[] $items
		 *
		 * @return Invoice
		 */
		public function setItems ($items) {
			if ((is_array ($items)) && (!empty ($items))) {
				$this->items = $items;
			} else {
				$this->items = null;
			}
			return $this;
		}

		/**
		 * @param string $number
		 *
		 * @return Invoice
		 */
		public function setNumber ($number) {
			if (is_scalar ($number)) {
				$this->number = $number;
			} else {
				$this->number = null;
			}
			return $this;
		}

		/**
		 * Establece el status de la factura
		 *
		 * @param string $status
		 *
		 * @return Invoice
		 */
		public function setStatus ($status) {
			if (is_scalar ($status)) {
				$this->status = $status;
			} else {
				$this->status = null;
			}
			return $this;
		}

		/**
		 * Establece el asunto de la factura
		 *
		 * @param string $subject
		 *
		 * @return Invoice
		 */
		public function setSubject ($subject) {
			if (is_scalar ($subject)) {
				$this->subject = $subject;
			} else {
				$this->subject = null;
			}
			return $this;
		}

		// Utils

		/**
		 * Calcula el monto total de la factura
		 *
		 * @return float
		 */
		public function getTotal () {
			if (empty ($this->items)) {
				return 0.0;
			}

			$total = 0.0;
			foreach ($this->items as $item) {
				$total += ($item->getQuantity () * ($item->getPrice () * (1 + ($item->getTaxPercentage () / 100))));
			}
			return $total;
		}

		/**
		 * Valida que la factura tenga los datos requeridos
		 *
		 * @throws InvoiceException Si alguno de los datos no ha sido establecido
		 */
		public function validate () {
			if ($this->accountId === null) {
				throw new InvoiceException (InvoiceException::ERROR_INVOICE_EMPTY_ACCOUNT_ID);
			} else if (empty ($this->creationDate)) {
				throw new InvoiceException (InvoiceException::ERROR_INVOICE_EMPTY_CREATION_DATE);
			} else if (empty ($this->dueDate)) {
				throw new InvoiceException (InvoiceException::ERROR_INVOICE_EMPTY_DUE_DATE);
			} else if (empty ($this->instanceCode)) {
				throw new InvoiceException (InvoiceException::ERROR_INVOICE_EMPTY_INSTANCE_CODE);
			} else if (empty ($this->items)) {
				throw new InvoiceException (InvoiceException::ERROR_INVOICE_EMPTY_ITEMS);
			} else if (empty ($this->status)) {
				throw new InvoiceException (InvoiceException::ERROR_INVOICE_EMPTY_STATUS);
			} else if (empty ($this->subject)) {
				throw new InvoiceException (InvoiceException::ERROR_INVOICE_EMPTY_SUBJECT);
			}

			foreach ($this->items as $item) {
				if (!($item instanceof InvoiceItem)) {
					throw new InvoiceException (InvoiceException::ERROR_INVOICE_INVALID_ITEM);
				}
				$item->validate ();
			}
		}

		// Static utils

		/**
		 * Crea un nuevo objeto Invoice. Útil para encadenar métodos
		 *
		 * @return Invoice
		 */
		public static function getInstance () {
			return new self ();
		}

	}

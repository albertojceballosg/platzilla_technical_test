<?php
	require_once ('include/platzilla/Exceptions/InvoiceItemException.php');

	/**
	 * Representa un item de una factura
	 */
	class InvoiceItem {
		/** @var integer */
		private $id;

		/** @var string */
		private $name;

		/** @var double */
		private $price;

		/** @var float */
		private $quantity;

		/** @var integer */
		private $sequence;

		/** @var float */
		private $taxPercentage;

		// Getters

		/**
		 * Obtiene el ID del item (producto o servicio)
		 *
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}

		/**
		 * @return string
		 */
		public function getName () {
			return $this->name;
		}

		/**
		 * Obtiene el precio sin impuesto del item
		 *
		 * @return float
		 */
		public function getPrice () {
			return !empty ($this->price) ? $this->price : 0.0;
		}

		/**
		 * Obtiene la cantidad de items
		 *
		 * @return float
		 */
		public function getQuantity () {
			return !empty ($this->quantity) ? $this->quantity : 0.0;
		}

		/**
		 * Obtiene la posición en la lista de items
		 *
		 * @return integer
		 */
		public function getSequence () {
			return $this->sequence;
		}

		/**
		 * Obtiene el porcentaje de impuesto a aplicar
		 *
		 * @return float
		 */
		public function getTaxPercentage () {
			return !empty ($this->taxPercentage) ? $this->taxPercentage : 0.0;
		}

		// Setters

		/**
		 * Establece el ID del item (producto o servicio)
		 *
		 * @param integer $id
		 *
		 * @return InvoiceItem
		 */
		public function setId ($id) {
			if ((is_numeric ($id)) && ($id > 0)) {
				$this->id = intval ($id);
			}
			return $this;
		}

		/**
		 * @param string $name
		 *
		 * @return InvoiceItem
		 */
		public function setName ($name) {
			$this->name = $name;
			return $this;
		}

		/**
		 * Establece el precio sin impuesto del item
		 *
		 * @param float $price
		 *
		 * @return InvoiceItem
		 */
		public function setPrice ($price) {
			if ((is_numeric ($price)) && ($price >= 0)) {
				$this->price = doubleval ($price);
			}
			return $this;
		}

		/**
		 * Establece la cantidad de items
		 *
		 * @param float $quantity
		 *
		 * @return InvoiceItem
		 */
		public function setQuantity ($quantity) {
			if ((is_numeric ($quantity)) && ($quantity >= 0)) {
				$this->quantity = floatval ($quantity);
			}
			return $this;
		}

		/**
		 * Establece la posición en la lista de items
		 *
		 * @param integer $sequence
		 *
		 * @return InvoiceItem
		 */
		public function setSequence ($sequence) {
			if ((is_numeric ($sequence)) && ($sequence > 0)) {
				$this->sequence = intval ($sequence);
			}
			return $this;
		}

		/**
		 * Establece el porcentaje de impuesto a aplicar
		 *
		 * @param float $taxPercentage
		 *
		 * @return InvoiceItem
		 */
		public function setTaxPercentage ($taxPercentage) {
			if ((is_numeric ($taxPercentage)) && ($taxPercentage >= 0)) {
				$this->taxPercentage = floatval ($taxPercentage);
			}
			return $this;
		}

		// Utils

		/**
		 * Calcula el precio final del item
		 *
		 * @return float
		 */
		public function getTotalPrice () {
			$taxPercentage = !empty ($this->taxPercentage) ? $this->taxPercentage : 0;
			return ($this->price * (1 + $taxPercentage / 100));
		}

		/**
		 * Valida que el item tenga los datos requeridos
		 *
		 * @throws InvoiceItemException Si alguno de los datos no ha sido establecido
		 */
		public function validate () {
			if (empty ($this->id)) {
				throw new InvoiceItemException (InvoiceItemException::ERROR_INVOICE_ITEM_EMPTY_ID);
			} else if ($this->price === null) {
				throw new InvoiceItemException (InvoiceItemException::ERROR_INVOICE_ITEM_EMPTY_PRICE);
			} else if ($this->sequence === null) {
				throw new InvoiceItemException (InvoiceItemException::ERROR_INVOICE_ITEM_EMPTY_SEQUENCE);
			}
		}

		// Static utils

		/**
		 * Crea un nuevo objeto InvoiceItem. Útil para encadenar métodos
		 *
		 * @return InvoiceItem
		 */
		public static function getInstance () {
			return new self ();
		}

	}

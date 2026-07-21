<?php
	require_once ('include/platzilla/Exceptions/ProductException.php');
	require_once ('include/platzilla/Objects/Pricebook.php');
	require_once ('include/platzilla/Objects/ProductInterface.php');
	require_once ('include/platzilla/Objects/Tax.php');

	class Product implements ProductInterface, Serializable {
		/** @var float */
		private $basePrice;

		/** @var integer */
		private $id;

		/** @var string */
		private $name;

		/** @var Pricebook */
		private $pricebook;

		/** @var integer */
		private $subscribedUsers;

		/** @var Tax */
		private $tax;

		/** @var string */
		private $type;

		/**
		 * @return float
		 */
		public function getBasePrice () {
			return $this->basePrice;
		}

		/**
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
		 * @return Pricebook
		 */
		public function getPricebook () {
			return $this->pricebook;
		}

		/**
		 * Para obtener los usuarios suscritos
		 *
		 * @return integer
		 */
		public function getSubscribedUsers () {
			return $this->subscribedUsers;
		}

		/**
		 * @return Tax
		 */
		public function getTax () {
			return $this->tax;
		}

		/**
		 * @return string
		 */
		public function getType () {
			return $this->type;
		}

		/**
		 * @param float $basePrice
		 *
		 * @return Product
		 */
		public function setBasePrice ($basePrice) {
			if ((is_numeric ($basePrice)) && ($basePrice >= 0)) {
				$this->basePrice = floatval ($basePrice);
			} else {
				$this->basePrice = null;
			}
			return $this;
		}

		/**
		 * @param integer $id
		 *
		 * @return Product
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
		 * @param string $name
		 *
		 * @return Product
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
		 * @param Pricebook $pricebook
		 *
		 * @return Product
		 */
		public function setPricebook ($pricebook) {
			if ($pricebook instanceof Pricebook) {
				$this->pricebook = $pricebook;
			} else {
				$this->pricebook = null;
			}
			return $this;
		}

		/**
		 * Para establecer los usuarios suscritos
		 *
		 * @param integer $subscribedUsers
		 *
		 * @return Product
		 */
		public function setSubscribedUsers ($subscribedUsers) {
			$this->subscribedUsers = $subscribedUsers;
			return $this;
		}

		/**
		 * @param Tax $tax
		 *
		 * @return Product
		 */
		public function setTax ($tax) {
			if ($tax instanceof Tax) {
				$this->tax = $tax;
			} else {
				$this->tax = null;
			}
			return $this;
		}

		/**
		 * @param string $type
		 *
		 * @return Product
		 */
		public function setType ($type) {
			if (in_array ($type, self::getAvailableTypes ())) {
				$this->type = $type;
			} else {
				$this->type = null;
			}
			return $this;
		}

		/**
		 * @return float
		 */
		public function getPriceBeforeTax () {
			$basePrice           = !empty ($this->basePrice) ? $this->basePrice : 0.0;
			$pricebookMultiplier = !empty ($this->pricebook) ? $this->pricebook->getMultiplier (): 1.0;
			$numUsers            = !($this->subscribedUsers) ? 1.0 : $this->subscribedUsers;
			return ($basePrice * $pricebookMultiplier * $numUsers);
		}

		/**
		 * @return float
		 */
		public function getPriceAfterTax () {
			$priceBeforeTax = $this->getPriceBeforeTax ();
			$taxPercentage  = !empty ($this->tax) ? $this->tax->getPercentage () : 0.0;
			return ($priceBeforeTax * (1 + ($taxPercentage / 100)));
		}

		/**
		 * @return float
		 */
		public function getTaxAmount () {
			$priceBeforeTax = $this->getPriceBeforeTax ();
			$taxPercentage  = !empty ($this->tax) ? $this->tax->getPercentage () : 0.0;
			return ($priceBeforeTax * $taxPercentage / 100);
		}

		/**
		 * @throws ProductException
		 */
		public function validate () {
			if ($this->basePrice === null) {
				throw new ProductException (ProductException::ERROR_PRODUCT_EMPTY_PRICE);
			} else if ($this->name === null) {
				throw new ProductException (ProductException::ERROR_PRODUCT_EMPTY_NAME);
			} else if ($this->type === null) {
				throw new ProductException (ProductException::ERROR_PRODUCT_EMPTY_TYPE);
			}
		}

		/**
		 * @return string
		 */
		public function serialize () {
			return serialize (
				array (
					$this->basePrice,
					$this->id,
					$this->name,
					$this->type,
					!empty ($this->pricebook) ? $this->pricebook->serialize () : null,
					!empty ($this->tax) ? $this->tax->serialize () : null,
				)
			);
		}

		/**
		 * @param string $serialized
		 */
		public function unserialize ($serialized) {
			list (
				$this->basePrice,
				$this->id,
				$this->name,
				$this->type,
				$serializedPricebook,
				$serializedTax,
				) = unserialize ($serialized);

			if (!empty ($serializedPricebook)) {
				$this->pricebook = Pricebook::getInstance ();
				$this->pricebook->unserialize ($serializedPricebook);
			} else {
				$this->pricebook = null;
			}
			if (!empty ($serializedTax)) {
				$this->tax = Tax::getInstance ();
				$this->tax->unserialize ($serializedTax);
			} else {
				$this->tax = null;
			}
		}

		/**
		 * @return string[]
		 */
		public static function getAvailableTypes () {
			return array (self::TYPE_PRODUCT, self::TYPE_SERVICE);
		}

		/**
		 * @return Product
		 */
		public static function getInstance () {
			return new self ();
		}

	}

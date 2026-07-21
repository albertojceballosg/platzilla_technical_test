<?php
	require_once ('include/platzilla/Managers/PricebookManager.php');
	require_once ('include/platzilla/Managers/TaxManager.php');
	require_once ('include/platzilla/Objects/Product.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Class ProductManager
	 *
	 * En esta clase se implementan los metodos para la gestion de los productos contratados en los planes de suscripcion
	 */
	class ProductManager {
		const RECORDS_PER_PAGE = 25;

		/** @var ProductManager */
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * ProductManager constructor.
		 *
		 * @param \PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * Para validar la existencia del producto
		 *
		 * @param Product $product
		 *
		 * @throws ProductException
		 */
		private function validate ($product) {
			if ((empty ($product)) || (!($product instanceof Product))) {
				throw new ProductException (ProductException::ERROR_PRODUCT_EMPTY);
			}

			$product->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_products WHERE productid=?', array ($product->getName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row       = $this->adb->fetchByAssoc ($result, -1, false);
				$productId = $product->getId ();
				if ((empty ($productId)) || ($row ['productid'] != $productId)) {
					$e = new ProductException (ProductException::ERROR_PRODUCT_DUPLICATE_NAME);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * Para eliminar el producto
		 *
		 * @param Product $product
		 */
		public function deleteProduct ($product) {
			if ((empty ($product)) || (empty ($product->getId ()))) {
				return;
			}

			$this->adb->pquery ('DELETE FROM vtiger_products WHERE productid=?', array ($product->getId ()));
		}

		/**
		 * Busca el producto actual
		 *
		 * @param string $instanceCode
		 *
		 * @return null|Product
		 */
		public function fetchCurrentProduct ($instanceCode, $numUsers = 0) {
			if (empty ($instanceCode)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					p.*
				FROM
					vtiger_products p
					INNER JOIN vtiger_instancebillingplans ibp ON ibp.productid=p.productid
					INNER JOIN vtiger_instances i ON i.billingplanid=ibp.planid AND i.code=?',
				array ($instanceCode)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$product = Product::getInstance ()
					->setBasePrice ($row ['baseprice'])
					->setId ($row ['productid'])
					->setName ($row ['productname'])
					->setPricebook (PricebookManager::getInstance ($this->adb)->fetchDefaultPricebook ())
					->setSubscribedUsers($numUsers)
					->setTax (TaxManager::getInstance ($this->adb)->getApplicableTax ($instanceCode))
					->setType ($row ['producttype']);
			} else {
				$product = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $product;
		}

		/**
		 * Busca el producto
		 *
		 * @param integer $id
		 * @param string $instanceCode
		 *
		 * @return null|Product
		 */
		public function fetchProduct ($id, $instanceCode = null, $numUsers = 0) {
			if (empty ($id)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_products WHERE productid=?', array ($id));
			if ($this->adb->num_rows ($result) > 0) {
				if (!empty ($instanceCode)) {
					$pricebook = PricebookManager::getInstance ($this->adb)->getApplicablePricebook ($instanceCode);
					$tax       = TaxManager::getInstance ($this->adb)->getApplicableTax ($instanceCode);
				} else {
					$pricebook = PricebookManager::getInstance ($this->adb)->fetchDefaultPricebook ();
					$tax       = TaxManager::getInstance ($this->adb)->fetchDefaultTax ();
				}
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$product = Product::getInstance ()
					->setBasePrice ($row ['baseprice'])
					->setId ($row ['productid'])
					->setName ($row ['productname'])
					->setSubscribedUsers($numUsers)
					->setPricebook ($pricebook)
					->setTax ($tax)
					->setType ($row ['producttype']);
			} else {
				$product = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $product;
		}

		/**
		 * Busca producto con impuestos aplicables
		 *
		 * @param integer $id
		 * @param string $instanceCode
		 * @param  integer $numUsers
		 *
		 * @return null|Product
		 */
		public function fetchProductWithApplicableTax ($id, $instanceCode, $numUsers = 0) {
			if (empty ($id)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_products WHERE productid=?', array ($id));
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$product = Product::getInstance ()
					->setBasePrice ($row ['baseprice'])
					->setId ($row ['productid'])
					->setName ($row ['productname'])
					->setPricebook (PricebookManager::getInstance ($this->adb)->fetchDefaultPricebook ())
					->setSubscribedUsers($numUsers)
					->setTax (TaxManager::getInstance ($this->adb)->getApplicableTax ($instanceCode))
					->setType ($row ['producttype']);
			} else {
				$product = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $product;
		}

		/**
		 * Busca los productos
		 *
		 * @param integer $page
		 *
		 * @return array
		 */
		public function fetchProducts ($page = 1) {
			if ((empty ($page)) || ($page <= 0)) {
				$startRecord = 0;
			} else {
				$startRecord = (($page - 1) * self::RECORDS_PER_PAGE);
			}

			$limit = self::RECORDS_PER_PAGE;

			$result = $this->adb->query (
				"SELECT
					p.*,
					total.__total_records__
				FROM
					vtiger_products p
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_products) AS total
				ORDER BY
					p.productname
				LIMIT {$startRecord}, {$limit}"
			);
			if ($this->adb->num_rows ($result) > 0) {
				$startRecord++;
				$totalRecords = null;
				$records      = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($totalRecords === null) {
						$totalRecords = intval ($row ['__total_records__']);
					}
					$records [] = Product::getInstance ()
						->setBasePrice ($row ['baseprice'])
						->setId ($row ['productid'])
						->setName ($row ['productname'])
						->setTax (TaxManager::getInstance ($this->adb)->fetchDefaultTax ())
						->setType ($row ['producttype']);
				}
				$endRecord  = count ($records);
				$totalPages = ceil ($totalRecords / self::RECORDS_PER_PAGE);
			} else {
				$totalRecords = 0;
				$records      = null;
				$endRecord    = 0;
				$totalPages   = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return array (
				'startRecord'  => $startRecord,
				'endRecord'    => $endRecord,
				'totalRecords' => $totalRecords,
				'page'         => empty ($page) ? 1 : intval ($page),
				'totalPages'   => $totalPages,
				'records'      => $records,
			);
		}

		/**
		 * Para el guardado del producto
		 *
		 * @param Product $product
		 *
		 * @return Product
		 * @throws ProductException
		 */
		public function saveProduct ($product) {
			if ((empty ($product)) || (!($product instanceof Product))) {
				return null;
			}
			$this->validate ($product);

			$productId = $product->getId ();
			if (empty ($productId)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_products (productname, producttype, baseprice) VALUES (?, ?, ?)',
					array ($product->getName (), $product->getType (), $product->getBasePrice ())
				);
				$productId = intval ($this->adb->getLastInsertID ());
				$product->setId ($productId);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_products SET productname=?, producttype=?, baseprice=? WHERE productid=?',
					array ($product->getName (), $product->getType (), $product->getBasePrice (), $productId)
				);
			}
			return $product;
		}

		/**
		 * Instanciación de la clase ProductManager. Se obtiene un objeto ProductManager con los atributos de la clase
		 *
		 * @param PearDatabase $adb
		 *
		 * @return ProductManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ($adb);
			}
			return self::$INSTANCE;
		}

	}

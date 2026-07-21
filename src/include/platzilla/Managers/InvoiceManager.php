<?php
	require_once ('include/platzilla/Objects/Invoice.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/GridFieldUtils.class.php');
	require_once ('include/utils/utils.php');

	class InvoiceManager {
		/** @var InvoiceManager */
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * Asigna una conexión a la base de datos principal a la variable privada $adb
		 */
		protected function __construct () {
			$this->adb = AdbManager::getInstance ()->getMasterAdb ();
		}

		/**
		 * Crea una factura
		 *
		 * @param Invoice $invoice
		 *
		 * @return Invoice
		 */
		private function createInvoice (Invoice $invoice) {
			// Obtener el próximo ID para construir el código de la factura
			$result = $this->adb->pquery ('SELECT cur_id, prefix FROM vtiger_modentity_num WHERE semodule=? AND active=1', array ('facturas'));
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$row              = $this->adb->fetchByAssoc ($result, -1, false);
				$invoiceCurrentId = intval ($row ['cur_id']);
				$invoiceCode      = "{$row ['prefix']}{$row ['cur_id']}";
			} else {
				$invoiceCurrentId = 1;
				$invoiceCode      = 'FAC-001';
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			// Obtener el ID del usuario al cual se asignará la factura
			$result = $this->adb->query ('SELECT id FROM vtiger_users ORDER BY id LIMIT 1');
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$userId = intval ($row ['id']);
			} else {
				$userId = 1;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$this->adb->startTransaction ();
			$invoiceId = $this->adb->getUniqueID ('vtiger_crmentity');
			$this->adb->pquery (
				'INSERT INTO vtiger_crmentity (crmid, smcreatorid, setype, createdtime, deleted, smownerid) VALUES (?, ?, ?, ?, ?, ?)',
				array ($invoiceId, $userId, 'facturas', date ('Y-m-d H:i:s'), 0, $userId)
			);
			$this->adb->pquery (
				'INSERT INTO vtiger_facturas (facturasid, cod_facturas, numero_factura, cliente, fecha_de_vencimiento, estado_factura, observaciones, fecha_de_emision) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
				array ($invoiceId, $invoiceCode, $invoiceCurrentId, $invoice->getAccountId (), $invoice->getDueDate ()->format ('Y-m-d'), $invoice->getStatus (), $invoice->getSubject (), $invoice->getCreationDate ()->format ('Y-m-d'))
			);
			$this->adb->pquery (
				'INSERT INTO vtiger_facturascf (facturasid) VALUES (?)',
				array ($invoiceId)
			);
			$this->adb->pquery ('UPDATE vtiger_modentity_num SET cur_id=? WHERE semodule=? AND active=1', array ($invoiceCurrentId + 1, 'facturas'));
			$this->adb->pquery ('UPDATE vtiger_crmentity_seq SET id=?', array ($invoiceId + 1));
			$invoice->setId ($invoiceId);
			$this->updateInvoiceItems ($invoice);
			$this->adb->completeTransaction ();
			return $invoice;
		}

		/**
		 * Obtiene un arreglo de objetos InvoiceItem asociados a una factura cuyo ID es suministrado como parámetro, o <code>null</code> si no se encuentra ninguno registrado
		 *
		 * @param integer $invoiceId
		 *
		 * @return array|null
		 */
		public function fetchInvoiceItems ($invoiceId) {
			if (empty ($invoiceId)) {
				return null;
			}

			$values = GridFieldUtils::getGridValues ($this->adb, 'facturas', 'articulos_a_facturar', $invoiceId);
			if (empty ($values)) {
				return null;
			}

			$items = array ();
			foreach ($values as $index => $value) {
				$items [] = InvoiceItem::getInstance ()
					->setName ($value ['articulo'])
					->setPrice ($value ['precio'])
					->setQuantity ($value ['cantidad'])
					->setSequence ($index + 1)
					->setTaxPercentage ($value ['impuesto']);
			}
			return $items;
		}

		/**
		 * Elimina los items almacenados en base de datos y los crea nuevamente
		 *
		 * @param Invoice $invoice
		 */
		private function updateInvoiceItems (Invoice $invoice) {
			$items     = $invoice->getItems ();
			$invoiceId = $invoice->getId ();

			if (!empty ($items)) {
				$values = array ();
				foreach ($items as $item) {
					$values [] = array (
						'articulo'   => $item->getName (),
						'cantidad'   => $item->getQuantity (),
						'descuento_' => 0,
						'impuesto'   => $item->getTaxPercentage (),
						'precio'     => $item->getPrice (),
						'subtotal'   => $item->getPrice (),
						'total'      => $item->getTotalPrice (),
					);
				}
			} else {
				$values = null;
			}

			GridFieldUtils::setGridValues ($this->adb, 'facturas', 'articulos_a_facturar', $invoiceId, $values);
		}

		/**
		 * Actualiza una factura existente
		 *
		 * @param Invoice $invoice
		 *
		 * @return Invoice
		 */
		private function updateInvoice (Invoice $invoice) {
			$invoiceId = $invoice->getId ();
			$this->adb->startTransaction ();
			$this->adb->pquery (
				'UPDATE vtiger_facturas SET cliente=?, fecha_de_vencimiento=?, estado_factura=?, observaciones=?, fecha_de_emision=? WHERE facturasid=?',
				array ($invoice->getAccountId (), $invoice->getDueDate ()->format ('Y-m-d'), $invoice->getStatus (), $invoice->getSubject (), $invoice->getCreationDate ()->format ('Y-m-d') , $invoiceId)
			);
			$this->adb->pquery ('UPDATE vtiger_crmentity SET modifiedtime=? WHERE crmid=?', array (date ('Y-m-d H:i:s'), $invoiceId));
			$this->updateInvoiceItems ($invoice);
			$this->adb->completeTransaction ();
			return $invoice;
		}

		/**
		 * Elimina un objeto Invoice de la base de datos
		 *
		 * @param Invoice $invoice
		 */
		public function deleteInvoice (Invoice $invoice) {
			if (empty ($invoice->getId ())) {
				return;
			}

			$invoiceId = $invoice->getId ();
			$this->adb->startTransaction ();
			// Eliminar los items de la tabla
			$this->adb->pquery ('DELETE FROM vtiger_subfields_values WHERE modulecfid=?', array ($invoiceId));
			$this->adb->pquery ('DELETE FROM vtiger_facturascf WHERE facturasid=?', array ($invoiceId));
			$this->adb->pquery ('DELETE FROM vtiger_facturas WHERE facturasid=?', array ($invoiceId));
			$this->adb->pquery ('DELETE FROM vtiger_crmentity WHERE crmid=?', array ($invoiceId));
			$this->adb->completeTransaction ();
		}

		/**
		 * Obtiene un objeto Invoice cuyo ID es suministrado como parámetro, con la información almacenada en la base de datos, o <code>null</code> si no se encuentra registrada
		 *
		 * @param integer $invoiceId
		 * @param string $instanceCode
		 *
		 * @return Invoice|null
		 */
		public function fetchInvoice ($invoiceId, $instanceCode) {
			if (empty ($invoiceId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					f.*,
					crme.createdtime
				FROM
					vtiger_facturas f
					INNER JOIN vtiger_crmentity crme ON crme.crmid=f.facturasid AND crme.deleted=0
				WHERE
					f.facturasid=?',
				array ($invoiceId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$invoice = Invoice::getInstance ()
					->setAccountId ($row ['cliente'])
					->setCreationDate ($row ['createdtime'])
					->setDueDate ($row ['fecha_de_vencimiento'])
					->setId (intval ($row ['facturasid']))
					->setInstanceCode ($instanceCode)
					->setItems ($this->fetchInvoiceItems ($invoiceId))
					->setNumber ($row ['numero_factura'])
					->setStatus ($row ['estado_factura'])
					->setSubject ($row ['observaciones']);
			} else {
				$invoice = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $invoice;
		}

		/**
		 * @param string $instanceCode
		 *
		 * @return Invoice[]|null
		 */
		public function fetchInvoices ($instanceCode) {
			if (empty ($instanceCode)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					f.*,
					crme.createdtime
				FROM
					vtiger_facturas f
					INNER JOIN vtiger_crmentity crme ON crme.crmid=f.facturasid AND crme.deleted=0
					INNER JOIN vtiger_instances i ON i.accountid=f.cliente AND i.code=?
				ORDER BY
					crme.createdtime',
				array ($instanceCode)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$invoices = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$invoices [] = Invoice::getInstance ()
						->setAccountId ($row ['cliente'])
						->setCreationDate ($row ['createdtime'])
						->setDueDate ($row ['fecha_de_vencimiento'])
						->setId (intval ($row ['facturasid']))
						->setInstanceCode ($instanceCode)
						->setItems ($this->fetchInvoiceItems (intval ($row ['facturasid'])))
						->setNumber ($row ['numero_factura'])
						->setStatus ($row ['estado_factura'])
						->setSubject ($row ['observaciones']);
				}
			} else {
				$invoices = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $invoices;
		}

		/**
		 * Crea o actualiza un objeto Invoice en la base de datos
		 *
		 * @param Invoice $invoice
		 *
		 * @return Invoice
		 */
		public function saveInvoice (Invoice $invoice) {
			$this->validateInvoice ($invoice);

			$invoiceId = $invoice->getId ();
			if (empty ($invoiceId)) {
				$this->createInvoice ($invoice);
			} else {
				$this->updateInvoice ($invoice);
			}
			return $invoice;
		}

		/**
		 * Valida que la factura tenga todos los datos requeridos y que el ID de la cuenta se corresponde a registro existente en la base de datos
		 *
		 * @param Invoice $invoice
		 *
		 * @throws InvoiceException
		 */
		public function validateInvoice (Invoice $invoice) {
			$invoice->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_clientes WHERE clientesid=?', array ($invoice->getAccountId ()));
			if ($this->adb->num_rows ($result) == 0) {
				$e = new InvoiceException (InvoiceException::ERROR_INVOICE_INVALID_ACCOUNT_ID);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * Crea un nuevo objeto InvoiceManager. Útil para encadenar métodos
		 *
		 * @return InvoiceManager
		 */
		public static function getInstance () {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ();
			}
			return self::$INSTANCE;
		}

	}

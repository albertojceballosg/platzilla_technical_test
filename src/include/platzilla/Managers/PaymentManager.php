<?php
	require_once ('include/platzilla/Managers/ProductManager.php');
	require_once ('include/platzilla/Objects/Payment.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class PaymentManager {
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * Constructor
		 *
		 * @param PearDatabase $adb
		 */
		protected function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param Payment $payment
		 */
		public function deletePayment ($payment) {
			if (empty ($payment)) {
				return;
			}

			$this->adb->pquery (
				'DELETE FROM vtiger_instancepayments WHERE instancecode=? AND type=? AND duedate=?',
				array ($payment->getInstanceCode (), $payment->getType (), $payment->getDueDate ()->format ('Y-m-d'))
			);
		}

		/**
		 * @param string $paymentId
		 *
		 * @return Payment|null
		 */
		public function fetchPayment ($paymentId) {
			if (empty ($paymentId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancepayments WHERE paymentid=?', array ($paymentId));
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$payment = Payment::getInstance ()
					->setDueDate ($row ['duedate'])
					->setId ($row ['paymentid'])
					->setInstanceCode ($row ['instancecode'])
					->setInvoiceId ($row ['invoiceid'])
					->setLastErrorMessage ($row ['lasterrormessage'])
					->setProductId ($row ['productid'])
					->setProductName ($row ['productname'])
					->setServiceEndDate ($row ['serviceenddate'])
					->setServiceStartDate ($row ['servicestartdate'])
					->setStatus ($row ['status'])
					->setSubTotal ($row ['subtotalamount'])
					->setTaxPercentage ($row ['taxpercentage'])
					->setType ($row ['type']);
			} else {
				$payment = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $payment;
		}

		/**
		 * @param string $instanceCode
		 * @param string $status
		 *
		 * @return Payment[]|null
		 */
		public function fetchPayments ($instanceCode, $status = null) {
			if (empty ($instanceCode)) {
				return null;
			}

			if (!empty ($status)) {
				$whereClause = 'AND status=?';
				$arguments   = array ($status);
			} else {
				$whereClause = '';
				$arguments   = array ();
			}

			$result = $this->adb->pquery ("SELECT * FROM vtiger_instancepayments WHERE instancecode=? {$whereClause}", array_merge (array ($instanceCode), $arguments));
			if ($this->adb->num_rows ($result) > 0) {
				$payments = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$payments [] = Payment::getInstance ()
						->setDueDate ($row ['duedate'])
						->setId ($row ['paymentid'])
						->setInstanceCode ($row ['instancecode'])
						->setInvoiceId ($row ['invoiceid'])
						->setLastErrorMessage ($row ['lasterrormessage'])
						->setProductId ($row ['productid'])
						->setProductName ($row ['productname'])
						->setServiceEndDate ($row ['serviceenddate'])
						->setServiceStartDate ($row ['servicestartdate'])
						->setStatus ($row ['status'])
						->setSubTotal ($row ['subtotalamount'])
						->setTaxPercentage ($row ['taxpercentage'])
						->setType ($row ['type']);
				}
			} else {
				$payments = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $payments;
		}

		/**
		 * Obtiene los pagos registrados para una instancia cuya fecha de vencimiento sea menor o igual a la suministrada como parámetro
		 *
		 * @param string $instanceCode
		 * @param string|DateTime $dueDate
		 *
		 * @return Payment[]|null
		 * @throws Exception
		 */
		public function fetchPendingPayments ($instanceCode, $dueDate) {
			if ((is_object ($dueDate)) && ($dueDate instanceof DateTime)) {
				$paymentDueDate = $dueDate->format ('Y-m-d');
			} else {
				$paymentDueDate = $dueDate;
			}
			$result = $this->adb->pquery (
				'SELECT * FROM vtiger_instancepayments ip WHERE ip.instancecode=? AND ip.duedate<=? AND ip.status IN (?, ?, ?) ORDER BY duedate',
				array ($instanceCode, $paymentDueDate, Payment::STATUS_PENDING, Payment::STATUS_PAST_DUE, Payment::STATUS_REJECTED)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$payments = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$payments [] = Payment::getInstance ()
						->setDueDate ($row ['duedate'])
						->setId ($row ['paymentid'])
						->setInstanceCode ($row ['instancecode'])
						->setInvoiceId ($row ['invoiceid'])
						->setLastErrorMessage ($row ['lasterrormessage'])
						->setProductId ($row ['productid'])
						->setProductName ($row ['productname'])
						->setServiceEndDate ($row ['serviceenddate'])
						->setServiceStartDate ($row ['servicestartdate'])
						->setStatus ($row ['status'])
						->setSubTotal ($row ['subtotalamount'])
						->setTaxPercentage ($row ['taxpercentage'])
						->setType ($row ['type']);
				}
			} else {
				$payments = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $payments;
		}

		/**
		 * @param Payment $payment
		 *
		 * @return Payment
		 */
		public function savePayment ($payment) {
			if (empty ($payment)) {
				return null;
			}

			$payment->validate ();

			$paymentId = $payment->getId ();
			$dueDate   = $payment->getDueDate ()->format ('Y-m-d');
			$result    = $this->adb->pquery ('SELECT * FROM vtiger_instancepayments WHERE paymentid=?', array ($paymentId));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_instancepayments (paymentid, instancecode, type, duedate, productid, productname, subtotalamount, taxpercentage, taxamount, totalamount, status, servicestartdate, serviceenddate, invoiceid, lasterrormessage, updatedon) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($paymentId, $payment->getInstanceCode (), $payment->getType (), $dueDate, $payment->getProductId (), $payment->getProductName (), $payment->getSubTotal (), $payment->getTaxPercentage (), $payment->getTaxAmount (), $payment->getTotalAmount (), $payment->getStatus (), $payment->getServiceStartDate ()->format ('Y-m-d'), $payment->getServiceEndDate ()->format ('Y-m-d'), $payment->getInvoiceId (), $payment->getLastErrorMessage (), date ('Y-m-d H:i:s'))
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_instancepayments SET instancecode=?, type=?, duedate=?, productid=?, productname=?, subtotalamount=?, taxpercentage=?, taxamount=?, totalamount=?, status=?, servicestartdate=?, serviceenddate=?, invoiceid=?, lasterrormessage=?, updatedon=? WHERE paymentid=?',
					array ($payment->getInstanceCode (), $payment->getType (), $dueDate, $payment->getProductId (), $payment->getProductName (), $payment->getSubTotal (), $payment->getTaxPercentage (), $payment->getTaxAmount (), $payment->getTotalAmount (), $payment->getStatus (), $payment->getServiceStartDate ()->format ('Y-m-d'), $payment->getServiceEndDate ()->format ('Y-m-d'), $payment->getInvoiceId (), $payment->getLastErrorMessage (), date ('Y-m-d H:i:s'), $paymentId)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $payment;
		}

		/**
		 * Crea un nuevo objeto PaymentManager. Útil para encadenar métodos
		 *
		 * @param PearDatabase $adb
		 *
		 * @return PaymentManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ($adb);
			}
			return self::$INSTANCE;
		}

	}

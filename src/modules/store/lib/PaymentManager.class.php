<?php
	require_once ('include/platzilla/Managers/InvoiceManager.php');
	require_once ('include/platzilla/Objects/PlatformInstanceInterface.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/store/lib/Payment.class.php');
	require_once ('modules/store/lib/PaymentGatewayManager.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	/**
	 * Gestiona los pagos pendientes de los clientes
	 */
	class PaymentManager {
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * Constructor
		 */
		protected function __construct () {
			$this->adb = AdbManager::getInstance ()->getMasterAdb ();
		}

		/**
		 * Crea las facturas que faltan, tomando como base los pagos registrados
		 *
		 * @param InvoiceManager $im
		 * @param array $instanceData
		 * @param integer $daysBeforeDueDate
		 *
		 * @throws Exception
		 */
		private function createMissingInvoices (InvoiceManager $im, $instanceData, $daysBeforeDueDate = 0) {
			if ((!is_numeric ($daysBeforeDueDate)) || ($daysBeforeDueDate < 0)) {
				throw new Exception ('La cantidad de días suministrada no es un número válido');
			}

			$daysBeforeDueDate = intval ($daysBeforeDueDate);
			$dueDateInterval   = new DateInterval ("P{$daysBeforeDueDate}D");
			$payments          = $this->getInstancePayments ($instanceData ['code'], date_create ('today')->add ($dueDateInterval));
			if (empty ($payments)) {
				return;
			}

			foreach ($payments as $payment) {
				$invoice = $im->getInstanceInvoiceByDueDate ($instanceData ['code'], $payment->getDueDate (), $payment->getDescription ());
				if (empty ($invoice)) {
					// La factura no existe, crearla
					$services = $payment->getServices ();
					$items    = array ();
					$sequence = 1;
					foreach ($services as $serviceId => $service) {
						$items [] = InvoiceItem::getInstance ()
							->setId ($serviceId)
							->setName ($service ['servicename'])
							->setPrice ($service ['listprice'])
							->setQuantity ($service ['quantity'])
							->setSequence ($sequence)
							->setTaxPercentage ($service ['taxpercentage']);
						$sequence++;
					}
					$paymentStatus = $payment->getStatus ();
					if ($paymentStatus == Payment::STATUS_SUBMITTED) {
						$invoiceStatus = 'Approved';
					} else if ($paymentStatus == Payment::STATUS_PAID) {
						$invoiceStatus = 'Paid';
					} else {
						$invoiceStatus = 'Created';
					}
					$invoice = Invoice::getInstance ()
						->setAccountId ($instanceData ['accountid'])
						->setCreationDate (date_create ('today'))
						->setDueDate ($payment->getDueDate ())
						->setInstanceCode ($instanceData ['code'])
						->setItems ($items)
						->setStatus ($invoiceStatus)
						->setSubject ($payment->getDescription ());
					$im->saveInvoice ($invoice);
				}
			}
		}

		/**
		 * @param array $application
		 *
		 * @return DateTime
		 */
		private function getApplicationEndDate ($application) {
			$today           = date_create ();
			$lastDateOfMonth = date_create ($today->format ('Y-m-t'));
			$day             = intval ($today->format ('d'));
			$lastDayOfMonth  = intval ($lastDateOfMonth->format ('d'));
			if (!empty ($application ['disablingdate'])) {
				// La aplicación fue inactivada en una fecha. Se considerará ese momento para el cálculo de precios.
				$applicationEndDate = date_create ($application ['disablingdate'])->setTime (23, 59, 59);
			} else if (!empty ($application ['serviceenddate'])) {
				$applicationEndDate = date_create ($application ['serviceenddate'])->setTime (23, 59, 59);
			} else if (($application ['status'] == ApplicationInterface::STATUS_SUBSCRIPTION_PENDING) && (in_array ($day, array (29, 30))) && ($day < $lastDayOfMonth)) {
				$applicationEndDate = $today->setTime (23, 59, 59);
			} else {
				$applicationEndDate = date_create ('today')->add (new DateInterval ('P1M'))->sub (new DateInterval ('P1D'))->setTime (23, 59, 59);
			}
			return $applicationEndDate;
		}

		/**
		 * @param array $application
		 *
		 * @return DateTime
		 */
		private function getApplicationStartDate ($application) {
			if (!empty ($application ['servicestartdate'])) {
				$applicationStartDate = date_create ($application ['servicestartdate'])->setTime (0, 0, 0);
			} else {
				$applicationStartDate = date_create ()->setTime (0, 0, 0);
			}
			return $applicationStartDate;
		}

		/**
		 * Obtiene información de los servicios facturables a partir de las aplicaciones facturables
		 *
		 * @param array $applications
		 *
		 * @return array|null
		 */
		private function getBillableServices ($applications) {
			if (empty ($applications)) {
				return null;
			}

			$services = array ();
			foreach ($applications as $application) {
				$result = $this->adb->pquery (
					'SELECT
						s.articulosid AS serviceid,
						s.nombre_del_articulo AS servicename,
						s.descripcion
					FROM
						vtiger_articulos s
						INNER JOIN vtiger_crmentity crme ON crme.crmid=s.articulosid AND crme.deleted=0
					WHERE
						s.articulosid=?',
					array ($application ['app_service'])
				);
				if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
					continue;
				}

				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$services [ $row ['serviceid'] ] = array (
						'servicename'   => $row ['servicename'],
						'description'   => $row ['descripcion'],
						'quantity'      => 1,
						'listprice'     => $application ['listprice'],
						'taxpercentage' => $application ['taxpercentage'],
						'tax'           => $application ['tax'],
						'finalprice'    => $application ['finalprice'],
					);
				}
			}
			return $services;
		}

		/**
		 * @param string $instanceCode
		 *
		 * @return DateTime
		 */
		private function getCurrentBillingPeriodStartDate ($instanceCode) {
			$instanceData = $this->getInstanceData ($instanceCode);

			// Obtener la fecha de inicio del servicio
			if (!empty ($instanceData ['servicestartdate'])) {
				$dummy = date_create ($instanceData ['servicestartdate']);
			} else {
				$dummy   = date_create ('today');
				$day     = intval ($dummy->format ('d'));
				$lastDay = intval ($dummy->format ('t'));
				if (in_array ($day, array (29, 30))) {
					$dummy->setDate (intval ($dummy->format ('Y')), intval ($dummy->format ('m')), $lastDay);
				}
				if ($lastDay < 31) {
					$dummy = $dummy->add (new DateInterval ('P1D'));
				}
			}

			$oneMonth = new DateInterval ('P1M');
			$today    = date_create ('today');
			if ($dummy <= $today) {
				while ($dummy <= $today) {
					$dummy->add ($oneMonth);
				}
				$startDate = date_create ($dummy->format ('Y-m-d'))->sub ($oneMonth);
			} else {
				$startDate = date_create ($dummy->format ('Y-m-d'));
			}

			return $startDate->setTime (0, 0, 0);
		}

		/**
		 * Obtener la información necesaria de la instancia
		 *
		 * @param string $instanceCode
		 *
		 * @return array
		 * @throws Exception
		 */
		private function getInstanceData ($instanceCode) {
			if (empty ($instanceCode)) {
				throw new Exception ('No se ha suministrado el código de la instancia');
			}

			$result = $this->adb->pquery (
				'SELECT
					i.*,
					a.nombre_comercial,
					c.contactosid,
					c.nombre,
					c.apellidos
				FROM
					vtiger_instances i
					INNER JOIN vtiger_clientes a ON a.clientesid=i.accountid
					INNER JOIN vtiger_contactos c ON c.clientes=a.clientesid AND c.email=i.administrator
				WHERE
					i.code=?
				ORDER BY
					c.contactosid
				LIMIT 1',
				array ($instanceCode)
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				throw new Exception ('El código de la instancia suministrado no está registrado');
			}

			return $this->adb->fetchByAssoc ($result, -1, false);
		}

		/**
		 * @param string $instanceCode
		 * @param DateTime|string $dueDate
		 * @param string $description
		 * @param float $price
		 * @param string $type
		 * @param array $services
		 * @param DateTime|string $updatedOn
		 */
		private function updateInstancePayment ($instanceCode, $dueDate, $description, $price, $type, $services, $updatedOn) {
			$payment = $this->getInstancePayment ($instanceCode, $dueDate, $description, $type);
			if ($type == Payment::TYPE_TRANSACTION) {
				foreach ($services as $serviceId => $service) {
					$listPrice                             = ((100 * $price) / (100 + doubleval ($service ['taxpercentage'])));
					$services [ $serviceId ]['finalprice'] = $price;
					$services [ $serviceId ]['listprice']  = $listPrice;
					$services [ $serviceId ]['tax']        = ($price - $listPrice);
				}
			}
			if (empty ($payment)) {
				// No existe el pago. Crearlo
				$payment = Payment::getInstance ()
					->setAmount ($price)
					->setDescription ($description)
					->setDueDate ($dueDate)
					->setInstanceCode ($instanceCode)
					->setServices ($services)
					->setStatus (Payment::STATUS_PENDING)
					->setType ($type);
			} else if (($payment->isPending ()) && ($price != round ($payment->getAmount (), 2))) {
				// El pago existe, está pendiente y el monto no concuerda. Actualizarlo
				$payment->setAmount ($price)
					->setServices ($services)
					->setStatus (Payment::STATUS_PENDING);
			}
			$this->savePayment ($payment, $updatedOn);
		}

		/**
		 * Actualiza las facturas pendientes tomando como información los pagos ya registrados:
		 * 1. Elimina las facturas pendientes que no tengan un pago registrado
		 * 2. Cambia el status de las facturas cuyo pago asociado haya cambiado de status
		 * a. Pagado => Pagada
		 * b. Enviado a Braintree => Aprobada
		 * 3. Si el monto del pago cambió, actualiza los items de la factura
		 *
		 * @param InvoiceManager $im
		 * @param array $instanceData
		 *
		 * @throws Exception
		 */
		private function updatePendingInvoices (InvoiceManager $im, $instanceData) {
			$invoices = $im->getPendingInstanceInvoices ($instanceData ['code']);
			if (empty ($invoices)) {
				return;
			}

			foreach ($invoices as $invoice) {
				$payment = $this->getInstancePayment ($instanceData ['code'], $invoice->getDueDate (), $invoice->getSubject ());
				if (empty ($payment)) {
					// La factura existe, está pendiente, pero no hay pago asociado. Eliminar
					$im->deleteInvoice ($invoice);
				} else {
					$deletePayment = false;
					$hasChanges    = false;
					// La factura existe y el pago también, actualizar
					if ($payment->getStatus () == Payment::STATUS_PAID) {
						$invoice->setStatus ('Paid');
						$hasChanges    = true;
						$deletePayment = true;
					} else if ($payment->getStatus () == Payment::STATUS_SUBMITTED) {
						$invoice->setStatus ('Approved');
						$hasChanges = true;
					}

					$paymentAmount = round ($payment->getAmount (), 2);
					$invoiceTotal  = round ($invoice->getTotal (), 2);
					if ($paymentAmount != $invoiceTotal) {
						// Los montos difieren. Los items deben ser diferentes. Recrear los items de las facturas
						$services = $payment->getServices ();
						$items    = array ();
						$sequence = 1;
						foreach ($services as $serviceId => $service) {
							$items [] = InvoiceItem::getInstance ()
								->setId ($serviceId)
								->setName ($service ['servicename'])
								->setPrice ($service ['listprice'])
								->setQuantity ($service ['quantity'])
								->setSequence ($sequence)
								->setTaxPercentage ($service ['taxpercentage']);
							$sequence++;
						}
						$invoice->setItems ($items);
						$hasChanges = true;
					}

					if ($hasChanges) {
						$im->saveInvoice ($invoice);
					}

					if ($deletePayment) {
						$this->deletePayment ($payment);
					}
				}
			}
		}

		// Payment related methods

		/**
		 * Elimina un pago registrado
		 *
		 * @param Payment $payment
		 */
		public function deletePayment (Payment $payment) {
			$this->adb->pquery (
				'DELETE FROM vtiger_instancepayments WHERE instancecode=? AND type=? AND description=?',
				array ($payment->getInstanceCode (), $payment->getType (), $payment->getDescription ())
			);
		}

		/**
		 * Guarda un pago, creando en base de datos si no existe, actualizando en caso contrario
		 *
		 * @param Payment $payment
		 * @param string|DateTime|null $updatedOn
		 *
		 * @throws Exception
		 */
		public function savePayment (Payment $payment, $updatedOn = null) {
			$payment->validate ();
			if (!isset ($updatedOn)) {
				$updatedOn = date_create ('now');
			} else if ((!is_object ($updatedOn)) || (!($updatedOn instanceof DateTime))) {
				$updatedOn = date_create ($updatedOn);
			}
			$result = $this->adb->pquery (
				'SELECT * FROM vtiger_instancepayments WHERE instancecode=? AND duedate=? AND description=?',
				array ($payment->getInstanceCode (), $payment->getDueDate ()->format ('Y-m-d'), $payment->getDescription ())
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_instancepayments (instancecode, duedate, description, type, amount, services, status, paymentgatewayid, lasterrormessage, updatedon) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($payment->getInstanceCode (), $payment->getDueDate ()->format ('Y-m-d'), $payment->getDescription (), $payment->getType (), $payment->getAmount (), json_encode ($payment->getServices ()), $payment->getStatus (), $payment->getGatewayId (), $payment->getLastErrorMessage (), $updatedOn->format ('Y-m-d H:i:s'))
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_instancepayments SET type=?, amount=?, services=?, status=?, paymentgatewayid=?, lasterrormessage=?, updatedon=? WHERE instancecode=? AND duedate=? AND description=?',
					array ($payment->getType (), $payment->getAmount (), json_encode ($payment->getServices ()), $payment->getStatus (), $payment->getGatewayId (), $payment->getLastErrorMessage (), $updatedOn->format ('Y-m-d H:i:s'), $payment->getInstanceCode (), $payment->getDueDate ()->format ('Y-m-d'), $payment->getDescription ())
				);
			}
		}

		// Instance related methods

		/**
		 * Obtiene un pago según los parámetros suministrados. Retorna <code>null</code> si no está registrado
		 *
		 * @param string $instanceCode
		 * @param string|DateTime $dueDate
		 * @param string $description
		 * @param string $type
		 *
		 * @return null|Payment
		 * @throws Exception
		 */
		public function getInstancePayment ($instanceCode, $dueDate, $description, $type = null) {
			if ((is_object ($dueDate)) && ($dueDate instanceof DateTime)) {
				$paymentDueDate = $dueDate->format ('Y-m-d');
			} else {
				$paymentDueDate = $dueDate;
			}

			if (!empty ($type)) {
				$typeWhereClause = 'AND type=?';
				$typeArguments   = array ($type);
			} else {
				$typeWhereClause = '';
				$typeArguments   = array ();
			}

			$result = $this->adb->pquery (
				"SELECT * FROM vtiger_instancepayments WHERE instancecode=? AND duedate=? AND description=? {$typeWhereClause}",
				array_merge (array ($instanceCode, $paymentDueDate, $description), $typeArguments)
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$row = $this->adb->fetchByAssoc ($result, -1, false);
			return Payment::getInstance ()
				->setAmount (doubleval ($row ['amount']))
				->setDescription ($description)
				->setDueDate ($paymentDueDate)
				->setGatewayId ($row ['paymentgatewayid'])
				->setInstanceCode ($instanceCode)
				->setLastErrorMessage ($row ['lasterrormessage'])
				->setServices (json_decode ($row ['services'], true))
				->setStatus ($row ['status'])
				->setType ($row ['type']);
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
		public function getInstancePayments ($instanceCode, $dueDate) {
			if ((is_object ($dueDate)) && ($dueDate instanceof DateTime)) {
				$paymentDueDate = $dueDate->format ('Y-m-d');
			} else {
				$paymentDueDate = $dueDate;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_instancepayments WHERE instancecode=? AND duedate<=?', array ($instanceCode, $paymentDueDate));
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$payments = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$payments [] = Payment::getInstance ()
					->setAmount (doubleval ($row ['amount']))
					->setDescription ($row ['description'])
					->setDueDate ($row ['duedate'])
					->setGatewayId ($row ['paymentgatewayid'])
					->setInstanceCode ($instanceCode)
					->setLastErrorMessage ($row ['lasterrormessage'])
					->setServices (json_decode ($row ['services'], true))
					->setStatus ($row ['status'])
					->setType ($row ['type']);
			}
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
		public function getPendingInstancePayments ($instanceCode, $dueDate) {
			if ((is_object ($dueDate)) && ($dueDate instanceof DateTime)) {
				$paymentDueDate = $dueDate->format ('Y-m-d');
			} else {
				$paymentDueDate = $dueDate;
			}
			$result = $this->adb->pquery (
				'SELECT * FROM vtiger_instancepayments WHERE instancecode=? AND duedate<=? AND status IN (?, ?, ?) ORDER BY duedate',
				array ($instanceCode, $paymentDueDate, Payment::STATUS_PENDING, Payment::STATUS_PAST_DUE, Payment::STATUS_REJECTED)
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$payments = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$payments [] = Payment::getInstance ()
					->setAmount (doubleval ($row ['amount']))
					->setDescription ($row ['description'])
					->setDueDate ($row ['duedate'])
					->setGatewayId ($row ['paymentgatewayid'])
					->setInstanceCode ($instanceCode)
					->setLastErrorMessage ($row ['lasterrormessage'])
					->setServices (json_decode ($row ['services'], true))
					->setStatus ($row ['status'])
					->setType ($row ['type']);
			}
			return $payments;
		}

		/**
		 * Obtiene los pagos registrados que no están en status finales para una instancia cuya fecha de vencimiento sea menor o igual a la suministrada como parámetro
		 *
		 * @param string $instanceCode
		 * @param string|DateTime $dueDate
		 *
		 * @return Payment[]|null
		 * @throws Exception
		 */
		public function getNonFinalInstancePayments ($instanceCode, $dueDate) {
			if ((is_object ($dueDate)) && ($dueDate instanceof DateTime)) {
				$paymentDueDate = $dueDate->format ('Y-m-d');
			} else {
				$paymentDueDate = $dueDate;
			}
			$result = $this->adb->pquery (
				'SELECT
					*
				FROM
					vtiger_instancepayments
				WHERE
					instancecode=? AND
					duedate<=? AND
					status IN (?, ?, ?, ?)
				ORDER BY
					duedate',
				array ($instanceCode, $paymentDueDate, Payment::STATUS_PENDING, Payment::STATUS_PAST_DUE, Payment::STATUS_REJECTED, Payment::STATUS_SUBMITTED)
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$payments = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$payments [] = Payment::getInstance ()
					->setAmount (doubleval ($row ['amount']))
					->setDescription ($row ['description'])
					->setDueDate ($row ['duedate'])
					->setGatewayId ($row ['paymentgatewayid'])
					->setInstanceCode ($instanceCode)
					->setLastErrorMessage ($row ['lasterrormessage'])
					->setServices (json_decode ($row ['services'], true))
					->setStatus ($row ['status'])
					->setType ($row ['type']);
			}
			return $payments;
		}

		/**
		 * Sincroniza la información de los pagos registrados con la información almacenada en la pasarela de pagos
		 *
		 * @param string $instanceCode
		 * @param DateTime|string $dueDate
		 */
		public function synchronizeGatewayData ($instanceCode, $dueDate) {
			if ((is_object ($dueDate)) && ($dueDate instanceof DateTime)) {
				$paymentDueDate = $dueDate->format ('Y-m-d');
			} else {
				$paymentDueDate = $dueDate;
			}

			$payments = $this->getNonFinalInstancePayments ($instanceCode, $paymentDueDate);
			if (empty ($payments)) {
				return;
			}

			$results = PaymentGatewayManager::getInstance ()->synchronizeInstancePayments ($instanceCode, $payments);
			if (empty ($results)) {
				return;
			}

			foreach ($results as $result) {
				$this->savePayment ($result);
			}
		}

		/**
		 * Actualiza los pagos de una instancia a partir de las aplicaciones facturables al momento de la ejecución
		 *
		 * @param string $instanceCode
		 *
		 * @throws Exception
		 */
		public function updateInstancePayments ($instanceCode) {
			$now   = date_create ('now');
			$today = date_create ('today');
			// Obtener la lista de aplicaciones facturables
			$subscribedApplications = StoreUtils::getSubscribedApplications ($instanceCode);
			if (!empty ($subscribedApplications)) {
				$subscriptionPrice      = 0.0;
				$subscriptionServices   = array ();
				$currentPeriodStartDate = $this->getCurrentBillingPeriodStartDate ($instanceCode);
				$currentPeriodEndDate   = date_create ($currentPeriodStartDate->format ('Y-m-d'))->add (new DateInterval ('P1M'))->sub (new DateInterval ('P1D'))->setTime (23, 59, 59);
				foreach ($subscribedApplications as $application) {
					if ($application ['status'] == ApplicationInterface::STATUS_CANCELLED) {
						// Aplicación cancelada no se factura. Saltar
						continue;
					}

					// Preparar datos que harán falta
					$applicationStartDate = $this->getApplicationStartDate ($application);
					$applicationEndDate   = $this->getApplicationEndDate ($application);
					$applicationServices  = $this->getBillableServices (array ($application));

					if (($application ['status'] == ApplicationInterface::STATUS_ACTIVE) && ($applicationStartDate <= $today) && ($today <= $applicationEndDate)) {
						// Aplicación vigente
						$subscriptionPrice += $application ['finalprice'];
						foreach ($applicationServices as $applicationServiceId => $applicationService) {
							$subscriptionServices [ $applicationServiceId ] = $applicationService;
						}
					} else if (($currentPeriodStartDate < $applicationEndDate) && ($applicationEndDate < $currentPeriodEndDate)) {
						// Aplicación vencida. Vence dentro del período actual de facturación. Requiere pago prorateado hasta el fin del período
						$applicationPrice   = round (StoreUtils::calculateProratedPrice ($application ['listprice'], $applicationEndDate, $currentPeriodEndDate, doubleval ($application ['taxpercentage'])), 2);
						$paymentDescription = "Suscripción Platzilla {$applicationEndDate->format ('d/m/Y')} - {$currentPeriodEndDate->format ('d/m/Y')}";
						$this->updateInstancePayment ($instanceCode, $applicationEndDate, $paymentDescription, $applicationPrice, Payment::TYPE_TRANSACTION, $applicationServices, $now);
					} else if (($applicationEndDate < $currentPeriodStartDate) && ($applicationEndDate < $currentPeriodEndDate)) {
						// Aplicación vencida. Vence antes del período actual de facturación. Requiere pago prorateado
						if ($today <= $currentPeriodStartDate) {
							$transactionEndDate = date_create ($currentPeriodStartDate->format ('Y-m-d'))->sub (new DateInterval ('P1D'))->setTime (23, 59, 59);
							$applicationPrice   = round (StoreUtils::calculateProratedPrice ($application ['listprice'], $applicationEndDate, $transactionEndDate, doubleval ($application ['taxpercentage'])), 2);
							$paymentDescription = "Suscripción Platzilla {$applicationEndDate->format ('d/m/Y')} - {$transactionEndDate->format ('d/m/Y')}";
							// Como vence antes del período actual de facturación, se incluye el precio para el cálculo de la suscripción actual
							$subscriptionPrice += $application ['finalprice'];
							foreach ($applicationServices as $applicationServiceId => $applicationService) {
								$subscriptionServices [ $applicationServiceId ] = $applicationService;
							}
						} else {
							$applicationPrice   = round (StoreUtils::calculateProratedPrice ($application ['listprice'], $applicationEndDate, $currentPeriodEndDate, doubleval ($application ['taxpercentage'])), 2);
							$paymentDescription = "Suscripción Platzilla {$applicationEndDate->format ('d/m/Y')} - {$currentPeriodEndDate->format ('d/m/Y')}";
						}
						$this->updateInstancePayment ($instanceCode, $applicationEndDate, $paymentDescription, $applicationPrice, Payment::TYPE_TRANSACTION, $applicationServices, $now);
					} else if (($currentPeriodStartDate <= $applicationEndDate) && ($applicationEndDate <= $currentPeriodEndDate)) {
						// Inicia y termina en el plazo de la suscripción actual. Agregar el precio para el cálculo de la suscripción actual
						$subscriptionPrice += $application ['finalprice'];
						foreach ($applicationServices as $applicationServiceId => $applicationService) {
							$subscriptionServices [ $applicationServiceId ] = $applicationService;
						}
					}
				}

				// Calcular suscripción por usuarios contratados
				$instanceDetails = StoreUtils::getInstanceDetails ($instanceCode);
				if ($instanceDetails ['totalusers'] > 1) {
					$pricePerUser = StoreUtils::getPriceForUser ();
					$subscriptionPrice += (($instanceDetails ['totalusers'] - 1) * $pricePerUser);
				}

				// Crear pago tipo suscripción
				if ($subscriptionPrice > 0) {
					$paymentDescription = "Suscripción Platzilla {$currentPeriodStartDate->format ('d/m/Y')} - {$currentPeriodEndDate->format ('d/m/Y')}";
					$this->updateInstancePayment ($instanceCode, $currentPeriodStartDate, $paymentDescription, $subscriptionPrice, Payment::TYPE_SUBSCRIPTION, $subscriptionServices, $now);
				}
			}

			// Eliminar los pagos que ya no aplican para el cliente
			$this->adb->pquery (
				'DELETE FROM vtiger_instancepayments WHERE instancecode=? AND status NOT IN (?, ?) AND updatedon<>?',
				array ($instanceCode, Payment::STATUS_PAID, Payment::STATUS_SUBMITTED, $now->format ('Y-m-d H:i:s'))
			);
		}

		/**
		 * Actualiza las facturas de la instancia cuyo vencimiento es $daysBeforeDueDate días después de la fecha de ejecución
		 *
		 * @param string $instanceCode
		 * @param integer $daysBeforeDueDate
		 *
		 * @return PaymentManager
		 * @throws Exception
		 */
		public function updateInstanceInvoices ($instanceCode, $daysBeforeDueDate = 0) {
			$im           = InvoiceManager::getInstance ();
			$instanceData = $this->getInstanceData ($instanceCode);
			$this->updatePendingInvoices ($im, $instanceData);
			$this->createMissingInvoices ($im, $instanceData, $daysBeforeDueDate);
			return $this;
		}

		/**
		 * @param string $instanceCode
		 */
		public function updateInstanceSubscription ($instanceCode) {
			// Obtener la lista de aplicaciones suscritas
			$applications = StoreUtils::getSubscribedApplications ($instanceCode);
			if (empty ($applications)) {
				return;
			}

			$instanceData      = $this->getInstanceData ($instanceCode);
			$serviceDueDate    = PlatzillaUtils::getDueDate ($instanceData ['servicestartdate']);
			$subscriptionPrice = 0.0;
			foreach ($applications as $application) {
				$subscriptionPrice += $application ['finalprice'];
			}

			$day             = $serviceDueDate->format ('d');
			$lastDateOfMonth = date_create ($serviceDueDate->format ('Y-m-t'));
			$lastDayOfMonth  = intval ($lastDateOfMonth->format ('d'));
			if (in_array ($day, array (29, 30))) {
				$serviceDueDate = date_create ($lastDateOfMonth->format ('Y-m-d'));
			}
			if ($lastDayOfMonth < 31) {
				$serviceDueDate = $serviceDueDate->add (new DateInterval ('P1D'));
			}
			$subscription = PaymentGatewayManager::getInstance ()->updateInstanceSubscription ($instanceCode, $subscriptionPrice, intval ($serviceDueDate->format ('d')));
			StoreUtils::updateInstanceBillingPlan ($instanceCode, PlatformInstanceInterface::BILLING_PLAN_MONTHLY_SUBSCRIPTION, isset ($subscription->firstBillingDate) ? $subscription->firstBillingDate : null);
		}

		/**
		 * Crea un nuevo objeto PaymentManager. Útil para encadenar métodos
		 *
		 * @return PaymentManager
		 */
		public static function getInstance () {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ();
			}
			return self::$INSTANCE;
		}

	}

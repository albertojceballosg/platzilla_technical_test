<?php
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');
	require_once ('modules/store/lib/PaymentManager.class.php');
	require_once ('modules/store/lib/StoreUtils.class.php');

	class UpdateCustomerServicesAction extends BackgroundTaskActionHandler {

		/**
		 * Deshabilita el acceso a los servicios asociados a los pagos suministrados como parámetro
		 *
		 * @param string $instanceData
		 * @param Payment[] $payments
		 *
		 * @throws Exception
		 */
		private function disableServices ($instanceData, $payments) {
			if (empty ($payments)) {
				return;
			}

			foreach ($payments as $payment) {
				$serviceIds = array_keys ($payment->getServices ());
				foreach ($serviceIds as $serviceId) {
					$this->logger->emit ('INFO', "Desactivando servicio {$serviceId}");
					StoreUtils::disableApplicationByServiceId ($instanceData, $serviceId);
				}
			}
		}

		/**
		 * Habilita el acceso a los servicios asociados a los pagos suministrados como parámetro
		 *
		 * @param string $instanceData
		 * @param Payment[] $payments
		 * @param DateTime $serviceDueDate Fecha de pago de la instancia
		 *
		 * @throws Exception
		 */
		private function enableServices ($instanceData, $payments, $serviceDueDate) {
			if (empty ($payments)) {
				return;
			}

			$today = date_create ('today');
			foreach ($payments as $payment) {
				$startDate = date_create ('today');
				if (($payment->getType () == Payment::TYPE_SUBSCRIPTION) && ($serviceDueDate <= $today)) {
					$endDate = date_create ($serviceDueDate->format ('Y-m-d'))->add (new DateInterval ('P1M'))->sub (new DateInterval ('P1D'));
				} else {
					$endDate = $payment->getDueDate ();
				}
				$serviceIds = array_keys ($payment->getServices ());
				foreach ($serviceIds as $serviceId) {
					$this->logger->emit ('INFO', "Activando servicio {$serviceId}");
					StoreUtils::enableApplicationByServiceId ($instanceData, $serviceId, $startDate, $endDate);
				}
			}
		}

		/**
		 * @param BackgroundTaskAction $action
		 *
		 * @return null
		 * @throws Exception
		 */
		public function run ($action) {
			if (empty ($action)) {
				throw new Exception ('No se ha suministrado la configuración de la acción');
			}

			$parameters = $action->getParameters ();
			if (empty ($parameters)) {
				throw new Exception ('No se han suministrado los parámetros de la acción');
			}

			$this->logger->emit ('INFO', 'Obteniendo parámetros');
			$requestedParameterNames = array ('gracedays', 'instancecode', 'servicesstartdate');
			$parameterValues         = $this->getParameterValues ($parameters, $requestedParameterNames, false);
			$instanceCode            = $parameterValues ['instancecode'];
			$serviceStartDate        = !empty ($parameterValues ['servicesstartdate']) ? $parameterValues ['servicesstartdate'] : date_create ()->format ('Y-m-d');
			$graceDays               = intval ($parameterValues ['gracedays']);
			$graceInterval           = new DateInterval ("P{$graceDays}D");

			$this->logger->emit ('INFO', "Obteniendo la información de pagos para la instancia {$instanceCode}");
			$payments = PaymentManager::getInstance ()->getInstancePayments ($instanceCode, date_create ('today'));
			if (empty ($payments)) {
				$this->logger->emit ('INFO', "La instancia {$instanceCode} no tiene pagos pendientes");
				return;
			}

			$this->logger->emit ('INFO', 'Determinado servicios a activar/desactivar');
			$servicesToEnable  = array ();
			$servicesToDisable = array ();
			foreach ($payments as $payment) {
				$disableDate   = date_create ($payment->getDueDate ()->format ('Y-m-d'))->add ($graceInterval);
				$today         = date_create ('today');
				$paymentStatus = $payment->getStatus ();
				if (in_array ($paymentStatus, array (Payment::STATUS_SUBMITTED, Payment::STATUS_PAID))) {
					// El pago está en proceso o ya pagado. Activar los servicios
					$servicesToEnable [] = $payment;
				} else if ((in_array ($paymentStatus, array (Payment::STATUS_PAST_DUE, Payment::STATUS_PENDING, Payment::STATUS_REJECTED))) && ($disableDate < $today)) {
					// El pago fue rechazado. Desactivar si se superan los días de gracia
					$servicesToDisable [] = $payment;
				}
			}
			if (!empty ($servicesToDisable)) {
				$this->disableServices (array ('code' => $instanceCode), $servicesToDisable);
			} else {
				$this->logger->emit ('INFO', 'No hay servicios a desactivar');
			}

			if (!empty ($servicesToEnable)) {
				$serviceDueDate = PlatzillaUtils::getDueDate ($serviceStartDate);
				$this->enableServices (array ('code' => $instanceCode), $servicesToEnable, $serviceDueDate);
			} else {
				$this->logger->emit ('INFO', 'No hay servicios a activar');
			}
			$this->logger->emit ('INFO', 'Se han activado/desactivado los servicios');
			return null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return UpdateCustomerServicesAction
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

	}

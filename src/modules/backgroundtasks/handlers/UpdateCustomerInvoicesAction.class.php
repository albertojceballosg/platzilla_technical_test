<?php
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');
	require_once ('modules/store/lib/PaymentManager.class.php');

	class UpdateCustomerInvoicesAction extends BackgroundTaskActionHandler {

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
			$requestedParameterNames = array ('instancecode', 'daysbeforeduedate');
			$parameterValues         = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			$instanceCode            = $parameterValues ['instancecode'];
			$daysBeforeDueDate       = isset ($parameterValues ['daysbeforeduedate']) ? intval ($parameterValues ['daysbeforeduedate']) : 0;

			$this->logger->emit ('INFO', "Actualizando la facturación para la instancia {$instanceCode}");
			$dueDateInterval = new DateInterval ("P{$daysBeforeDueDate}D");
			$dueDate         = date_create ('today')->add ($dueDateInterval);
			$this->logger->emit ('INFO', "Se actualizarán las facturas cuyo vencimiento sea menor o igual a {$dueDate->format ('d/m/Y')}");
			PaymentManager::getInstance ()->updateInstanceInvoices ($instanceCode, $daysBeforeDueDate);
			$this->logger->emit ('INFO', 'Se ha actualizado la facturación');
			return null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return UpdateCustomerInvoicesAction
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

	}

<?php
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');
	require_once ('modules/store/lib/PaymentManager.class.php');

	class UpdateCustomerChargesAction extends BackgroundTaskActionHandler {

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
			$requestedParameterNames = array ('instancecode');
			$parameterValues         = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			$instanceCode            = $parameterValues ['instancecode'];

			$this->logger->emit ('INFO', "Actualizando los cobros pendientes para la instancia {$instanceCode}");
			PaymentManager::getInstance ()->updateInstancePayments ($instanceCode);
			$this->logger->emit ('INFO', 'Se han actualizado los cobros pendientes');
			return null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return UpdateCustomerChargesAction
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

	}

<?php
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');
	require_once ('modules/store/lib/PaymentManager.class.php');

	class UpdateCustomerSubscriptionAction extends BackgroundTaskActionHandler {

		/**
		 * @param BackgroundTaskAction $action
		 *
		 * @return null
		 * @throws Exception
		 */
		public function run ($action) {
			if (empty ($action)) {
				throw new Exception ('No se ha suministrado la acción');
			}

			$parameters = $action->getParameters ();
			if (empty ($parameters)) {
				throw new Exception ('No se han suministrado los parámetros de la acción');
			}

			$this->logger->emit ('INFO', 'Obteniendo parámetros');
			$requestedParameterNames = array ('instancecode');
			$parameterValues         = $this->getParameterValues ($parameters, $requestedParameterNames, true);
			$instanceCode            = $parameterValues ['instancecode'];

			$this->logger->emit ('INFO', "Actualizando la suscripción para la instancia {$instanceCode}");
			try {
				PaymentManager::getInstance ()->updateInstanceSubscription ($instanceCode);
				$this->logger->emit ('INFO', 'Se ha actualizado la suscripción');
			} catch (Exception $e) {
				$this->logger->emit ('ERROR', $e->getMessage ());
			}

			return null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Logger $logger
		 * @param string $platform
		 *
		 * @return UpdateCustomerSubscriptionAction
		 */
		public static function getInstance (PearDatabase $adb, Logger $logger = null, $platform = null) {
			return new self ($adb, $logger, $platform);
		}

	}

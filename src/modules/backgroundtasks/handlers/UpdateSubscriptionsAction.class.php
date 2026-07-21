<?php
	require_once ('include/platzilla/Managers/PlatformSubscriptionManager.php');
	require_once ('include/platzilla/Objects/BackgroundTaskActionHandler.php');

	class UpdateSubscriptionsAction extends BackgroundTaskActionHandler {

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

			try {
				PlatformSubscriptionManager::getInstance ($this->adb)->updateSubscriptionsWithPaymentData ();
				$this->logger->emit ('INFO', 'Las suscripciones se actualizaron con éxito');
			} catch (Exception $e) {
				$this->logger->emit ('ERROR', $e->getMessage ());
				$this->logger->emit ('ERROR', $e->getTraceAsString ());
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

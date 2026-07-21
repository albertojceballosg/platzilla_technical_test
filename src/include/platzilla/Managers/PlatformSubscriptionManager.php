<?php
	require_once ('include/platzilla/Managers/ApplicationSubscriptionManager.php');
	require_once ('include/platzilla/Managers/InvoiceManager.php');
	require_once ('include/platzilla/Managers/PaymentGatewayManager.php');
	require_once ('include/platzilla/Managers/PaymentManager.php');
	require_once ('include/platzilla/Managers/PlatformBillingPlanManager.php');
	require_once ('include/platzilla/Objects/ModuleSubscription.php');
	require_once ('include/platzilla/Objects/PlatformSubscription.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	/**
	 * Class PlatformSubscriptionManager
	 *
	 * En esta clase se encuentran implementados los metodos para la gestion de la suscripción de las instancias/plataformas
	 */
	class PlatformSubscriptionManager {
		/** @var PlatformSubscriptionManager */
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		/**
		 * PlatformSubscriptionManager constructor.
		 *
		 * @param \PearDatabase $adb
		 */
		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * Para cargar los pagos de las suscripciones
		 *
		 * @param PlatformSubscription $subscription
		 * @param float $listPrice
		 * @param float $taxPercentage
		 * @param integer $billingDayOfMonth
		 * @param PlatformBillingPlan $newBillingPlan
		 *
		 * @return Payment[]
		 * @throws PaymentGatewayException
		 */
		private function chargeSubscriptionPayments ($subscription, $listPrice, $taxPercentage, $billingDayOfMonth, $newBillingPlan) {
			$newBillingPlanProduct = $newBillingPlan->getProduct ();
			$instanceCode          = $subscription->getInstanceCode ();
			$productId             = $newBillingPlanProduct->getId ();
			$productName           = $newBillingPlanProduct->getName ();
			$pgm                   = PaymentGatewayManager::getInstance ();
			$transactionPayment    = $this->calculateChangePlanLastMonthDaysTransactionPayment ($subscription, $newBillingPlan);
			$payments              = array_merge (
				array ($pgm->updateInstanceSubscription ($instanceCode, $productId, $productName, $listPrice, $taxPercentage, $billingDayOfMonth)),
				!empty ($transactionPayment) ? $pgm->chargeInstanceCustomerPayments ($subscription->getInstanceCode (), array ($transactionPayment)) : array ()
			);
			return array_filter ($payments);
		}

		/**
		 * Para crear la factura cobrada de la suscripcion
		 *
		 * @param PlatformSubscription $subscription
		 * @param Payment $payment
		 *
		 * @return Invoice
		 */
		private function createInvoice ($subscription, $payment) {
			if (!($payment instanceof Payment)) {
				return null;
			}

			$subjectType = $payment->getType () == Payment::TYPE_SUBSCRIPTION ? 'Suscripción a Platzilla' : 'Actualización de suscripción a Platzilla';
			switch ($payment->getStatus ()) {
				case Payment::STATUS_PAID:
				case Payment::STATUS_SUBMITTED:
					$invoiceStatus = 'Cobrada';
					break;
				default:
					$invoiceStatus = 'Pendiente';
					break;
			}
			$item    = InvoiceItem::getInstance ()
				->setId ($payment->getProductId ())
				->setName ($payment->getProductName ())
				->setPrice ($payment->getSubTotal ())
				->setQuantity (1)
				->setSequence (1)
				->setTaxPercentage ($payment->getTaxPercentage ());
			$invoice = Invoice::getInstance ()
				->setAccountId ($subscription->getAccountId ())
				->setCreationDate (date_create ())
				->setDueDate ($payment->getDueDate ())
				->setInstanceCode ($payment->getInstanceCode ())
				->setItems (array ($item))
				->setStatus ($invoiceStatus)
				->setSubject ("{$subjectType} para el período {$payment->getServiceStartDate ()->format ('d/m/Y')} - {$payment->getServiceEndDate ()->format ('d/m/Y')}");
			return InvoiceManager::getInstance ()->saveInvoice ($invoice);
		}

		/**
		 * Obtiene el estado de suscripción del modulo
		 *
		 * @param string $applicationSubscriptionStatus
		 *
		 * @return null|string
		 */
		private function getModuleSubscriptionStatus ($applicationSubscriptionStatus) {
			switch ($applicationSubscriptionStatus) {
				case ApplicationSubscription::STATUS_ACTIVE:
					$moduleSubscriptionStatus = ModuleSubscription::STATUS_ACTIVE;
					break;
				case ApplicationSubscription::STATUS_INACTIVE:
					$moduleSubscriptionStatus = ModuleSubscription::STATUS_INACTIVE;
					break;
				case ApplicationSubscription::STATUS_SUBSCRIBED:
					$moduleSubscriptionStatus = ModuleSubscription::STATUS_SUBSCRIBED;
					break;
				default:
					$moduleSubscriptionStatus = null;
					break;
			}
			return $moduleSubscriptionStatus;
		}

		/**
		 * Obtiene el espacio usado en disco para la instancia
		 *
		 * @param string $instanceCode
		 *
		 * @return float
		 */
		private function getInstanceUsedDiskSpace ($instanceCode) {
			$rootFolderPath    = PlatzillaUtils::getPlatzillaRootFolderPath ();
			$storageFolderPath = "{$rootFolderPath}/{$instanceCode}/storage";
			if (!is_dir ($storageFolderPath)) {
				return 0.0;
			}

			$totalBytes        = 0;
			$directoryIterator = new RecursiveDirectoryIterator ($storageFolderPath, FilesystemIterator::SKIP_DOTS);
			$iterator          = new RecursiveIteratorIterator ($directoryIterator);
			foreach ($iterator as $innerFolder) {
				$totalBytes += $innerFolder->getSize ();
			}
			return ($totalBytes / 1024 / 1024);
		}

		/**
		 * Obtiene el codigo de la aplicacion
		 *
		 * @param ApplicationSubscription $applicationSubscription
		 *
		 * @return string
		 * NOTA: PHP Mess Detector reporta este método como no usado. Falso. Se usa para obtener los códigos de las aplicaciones en el método fetchModuleSubscriptions. Se deshabilita el warning
		 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
		 */
		private static function getApplicationCode ($applicationSubscription) {
			return $applicationSubscription->getApplicationCode ();
		}

		/**
		 * Ordenar suscripcion de las aplicaciones por su nombre
		 *
		 * @param ApplicationSubscription $applicationSubscriptionA
		 * @param ApplicationSubscription $applicationSubscriptionB
		 *
		 * @return integer
		 * NOTA: PHP Mess Detector reporta este método como no usado. Falso. Se usa para ordenar las suscripciones a aplicaciones en los métodos fetchApplicationSubscriptions y fetchSubscription. Se deshabilita el warning
		 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
		 */
		private static function sortApplicationSubscriptionsByApplicationName ($applicationSubscriptionA, $applicationSubscriptionB) {
			return strcmp ($applicationSubscriptionA->getApplicationName (), $applicationSubscriptionB->getApplicationName ());
		}

		/**
		 * Ordenar suscripcion de los modulos por su etiqueta
		 *
		 * @param ModuleSubscription $moduleSubscriptionA
		 * @param ModuleSubscription $moduleSubscriptionB
		 *
		 * @return integer
		 * NOTA: PHP Mess Detector reporta este método como no usado. Falso. Se usa para ordenar las suscripciones a módulos en el método fetchModuleSubscriptions. Se deshabilita el warning
		 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
		 */
		private static function sortModuleSubscriptionsByModuleLabel ($moduleSubscriptionA, $moduleSubscriptionB) {
			return strcmp ($moduleSubscriptionA->getModuleLabel (), $moduleSubscriptionB->getModuleLabel ());
		}

		/**
		 * Calcula el plan de pagos para el cambio de una suscripcion
		 *
		 * @param PlatformSubscription $subscription
		 * @param PlatformBillingPlan $newBillingPlan
		 *
		 * @return Payment
		 */
		public function calculateChangePlanSubscriptionPayment ($subscription, $newBillingPlan) {
			$now                   = date_create ();
			$subscriptionStartDate = $this->getSubscriptionStartDate ($subscription->getServiceStartDate (), $now);
			$subscriptionEndDate   = $this->getSubscriptionEndDate ($subscription->getServiceEndDate (), $subscriptionStartDate);
			$newBillingPlanProduct = $newBillingPlan->getProduct ();
			return Payment::getInstance ($newBillingPlanProduct)
				->setDueDate ($subscriptionEndDate)
				->setInstanceCode ($subscription->getInstanceCode ())
				->setServiceEndDate ($subscriptionEndDate)
				->setServiceStartDate ($subscriptionStartDate)
				->setType (Payment::TYPE_SUBSCRIPTION);
		}

		/**
		 * Calcula el plan de pago para el cambio de la suscripcion para los ultimos dias del mes
		 *
		 * @param PlatformSubscription $subscription
		 * @param PlatformBillingPlan $newBillingPlan
		 *
		 * @return Payment|null
		 */
		public function calculateChangePlanLastMonthDaysTransactionPayment ($subscription, $newBillingPlan) {
			$oldBillingPlan        = $subscription->getBillingPlan ();
			$now                   = date_create ();
			$subscriptionStartDate = $this->getSubscriptionStartDate ($subscription->getServiceStartDate (), $now);
			$subscriptionEndDate   = $this->getSubscriptionEndDate ($subscription->getServiceEndDate (), $subscriptionStartDate);
			if (($now >= $subscriptionStartDate) || ($now >= $subscriptionEndDate)) {
				return null;
			}

			// Hoy es días antes del período de la suscripción. Ocurre cuando la fecha de inicio cae 29, 30 o 31.
			// En esos casos, el inicio de la suscripción se rueda hacia el 1 del siguiente mes
			// Hay que crear un pago por la diferencia de días
			$oneDayInterval            = new DateInterval ('P1D');
			$paymentStartDate          = date_create ($now->format ('Y-m-d'));
			$paymentEndDate            = date_create ($subscriptionStartDate->format ('Y-m-d'))->sub ($oneDayInterval);
			$remainingDays             = $subscriptionStartDate->diff ($now)->format ('%a');
			$oldBillingPlanProduct     = $oldBillingPlan->getProduct ();
			$oldBillingPlanPrice       = $oldBillingPlanProduct->getPriceBeforeTax ();
			$newBillingPlanProduct     = $newBillingPlan->getProduct ();
			$newBillingPlanPrice       = $newBillingPlanProduct->getPriceBeforeTax ();
			$proratedPaymentWithoutTax = (($newBillingPlanPrice - $oldBillingPlanPrice) * ($remainingDays / 30));
			return Payment::getInstance ()
				->setDueDate ($now)
				->setInstanceCode ($subscription->getInstanceCode ())
				->setProductId ($newBillingPlanProduct->getId ())
				->setProductName ($newBillingPlanProduct->getName ())
				->setServiceEndDate ($paymentEndDate)
				->setServiceStartDate ($paymentStartDate)
				->setSubTotal ($proratedPaymentWithoutTax)
				->setTaxPercentage ($newBillingPlanProduct->getTax ()->getPercentage ())
				->setType (Payment::TYPE_TRANSACTION);
		}

		/**
		 * Calcula la transacion de pago al cambiar el plan regular
		 *
		 * @param PlatformSubscription $subscription
		 * @param PlatformBillingPlan $newBillingPlan
		 *
		 * @return Payment|null
		 */
		public function calculateChangePlanRegularTransactionPayment ($subscription, $newBillingPlan) {
			$oldBillingPlan        = $subscription->getBillingPlan ();
			$now                   = date_create ();
			$subscriptionStartDate = $this->getSubscriptionStartDate ($subscription->getServiceStartDate (), $now);
			$subscriptionEndDate   = $this->getSubscriptionEndDate ($subscription->getServiceEndDate (), $subscriptionStartDate);
			$oldBillingPlanProduct = $oldBillingPlan->getProduct ();
			$oldBillingPlanPrice   = $oldBillingPlanProduct->getPriceBeforeTax ();
			$newBillingPlanProduct = $newBillingPlan->getProduct ();
			$newBillingPlanPrice   = $newBillingPlanProduct->getPriceBeforeTax ();
			if (
				($oldBillingPlanPrice == 0) ||
				($oldBillingPlanPrice >= $newBillingPlanPrice) ||
				($subscriptionStartDate >= $now) ||
				($now >= $subscriptionEndDate)
			) {
				return null;
			}

			// Hoy está dentro del período de la suscripción
			$paymentStartDate          = date_create ($now->format ('Y-m-d'));
			$paymentEndDate            = date_create ($subscriptionEndDate->format ('Y-m-d'));
			$remainingDays             = $subscriptionEndDate->diff ($now)->format ('%a');
			$proratedPaymentWithoutTax = (($newBillingPlanPrice - $oldBillingPlanPrice) * ($remainingDays / 30));
			return Payment::getInstance ()
				->setDueDate ($now)
				->setInstanceCode ($subscription->getInstanceCode ())
				->setProductId ($newBillingPlanProduct->getId ())
				->setProductName ($newBillingPlanProduct->getName ())
				->setServiceEndDate ($paymentEndDate)
				->setServiceStartDate ($paymentStartDate)
				->setSubTotal ($proratedPaymentWithoutTax)
				->setTaxPercentage ($newBillingPlanProduct->getTax ()->getPercentage ())
				->setType (Payment::TYPE_TRANSACTION);
		}

		/**
		 * Cambiar el plan de facturacion de la suscripcion
		 *
		 * @param PlatformSubscription $subscription
		 * @param integer $newBillingPlanId
		 * @param integer $numUser
		 *
		 * @throws Exception
		 */
		public function changeSubscriptionBillingPlan ($subscription, $newBillingPlanId, $numUser = 0) {
			$newBillingPlan        = $this->checkIfBillingPlanIsApplicable ($subscription, $newBillingPlanId, $numUser);
			$newBillingPlanProduct = $newBillingPlan->getProduct ();
			$subscriptionPrice     = $newBillingPlanProduct->getPriceAfterTax ();
			$now                   = date_create ()->setTime (0, 0, 0);

			if ($subscriptionPrice > 0) {
				$subscriptionStartDate = $this->getSubscriptionStartDate ($subscription->getServiceStartDate (), $now);
				$subscriptionEndDate   = $this->getSubscriptionEndDate ($subscription->getServiceEndDate (), $subscriptionStartDate);
				$billingDayOfMonth     = intval ($subscriptionStartDate->format ('d'));
			} else {
				$subscriptionStartDate = null;
				$subscriptionEndDate   = null;
				$billingDayOfMonth     = null;
			}
			$payments = $this->chargeSubscriptionPayments ($subscription, $newBillingPlanProduct->getPriceBeforeTax (), $newBillingPlanProduct->getTax ()->getPercentage (), $billingDayOfMonth, $newBillingPlan);
			if (empty ($payments)) {
				return;
			}
			$this->adb->pquery (
				'UPDATE vtiger_instances SET servicestartdate=?, serviceenddate=?, billingplanid=?, pricebookid=?, subscribedusers=? WHERE code=?',
				array (isset ($subscriptionStartDate) ? $subscriptionStartDate->format ('Y-m-d') : null, isset ($subscriptionEndDate) ? $subscriptionEndDate->format ('Y-m-d') : null, $newBillingPlanId, $newBillingPlanProduct->getPricebook ()->getId (), $numUser, $subscription->getInstanceCode ())
			);
			$this->adb->pquery (
				'UPDATE vtiger_instanceapplications SET status=?, servicestartdate=?, serviceenddate=? WHERE instancecode=?',
				array (ApplicationSubscriptionInterface::STATUS_SUBSCRIBED, isset ($subscriptionStartDate) ? $subscriptionStartDate->format ('Y-m-d') : null, isset ($subscriptionEndDate) ? $subscriptionEndDate->format ('Y-m-d') : null, $subscription->getInstanceCode ())
			);
			try {
				$pm = PaymentManager::getInstance ($this->adb);
				foreach ($payments as $payment) {
					$invoice = $this->createInvoice ($subscription, $payment);
					$payment->setInvoiceId ($invoice->getId ());
					$pm->savePayment ($payment);
				}
			} catch (Exception $ignored) {
				// Si ya se cobró, no se puede detener el proceso por una falla al guardar los pagos o en la creación de la factura
			}
		}

		/**
		 * Verifica si el plan de facturacion es aplicable
		 *
		 * @codingStandardsIgnoreStart
		 * @SuppressWarnings(PHPMD)
		 * NOTA: CodeSniffer detecta una violación de complejidad ciclomática, dada la cantidad de casos. Es imposible reducirlos (todos son necesarios)
		 * @param PlatformSubscription $subscription
		 * @param integer $newBillingPlanId
		 * @param integer $numUser
		 *
		 * @return PlatformBillingPlan
		 * @throws PlatformSubscriptionException
		 */
		public function checkIfBillingPlanIsApplicable ($subscription, $newBillingPlanId, $numUser = 0) {
			$subscriptionPlan = $subscription->getBillingPlan();
			if (empty ($newBillingPlanId)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_BILLING_PLAN_ID);
			} else if (!($subscription instanceof PlatformSubscription)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY);
			} else if (($subscriptionPlan->getTotalUsers() != -1) && ($subscription->getSubscribedUsers () > 0) && ($subscription->getBillingPlan ()->getId () == $newBillingPlanId)) {
				if ($numUser < $subscription->getSubscribedUsers ()) {
					throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_TOTAL_USERS_EXCEEDED);
				} else if ($numUser == $subscription->getSubscribedUsers()) {
					throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_SAME_BILLING_PLAN);
				}
			} else if (($subscriptionPlan->getTotalUsers() == -1) && $numUser != 0) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_MINOR_PLAN);
			} else if (($subscription->getBillingPlan ()->getId () == $newBillingPlanId) && (($numUser == 0) || ($numUser == $subscription->getSubscribedUsers()))) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_SAME_BILLING_PLAN);
			}

			$newBillingPlan                  = PlatformBillingPlanManager::getInstance ($this->adb)->fetchPlan ($newBillingPlanId, $subscription->getInstanceCode (), $numUser);
			$newBillingPlanTotalApplications = $newBillingPlan->getTotalApplications ();
			$newBillingPlanTotalDiskSpace    = $newBillingPlan->getTotalDiskSpace ();
			$newBillingPlanTotalUsers        = $newBillingPlan->getTotalUsers ();
			if ((empty ($newBillingPlan)) || ($newBillingPlan->getStatus () == PlatformBillingPlan::STATUS_INACTIVE)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_INVALID_BILLING_PLAN);
			} else if (($subscriptionPlan->getTotalUsers() != 1) && ($subscription->getSubscribedUsers () > 0) && ($subscription->getBillingPlan ()->getId () != $newBillingPlanId)) {
				if (($newBillingPlan->getTotalUsers() != -1) && ($newBillingPlan->getTotalUsers() < $subscription->getBillingPlan()->getTotalUsers())) {
					throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_MINOR_PLAN);
				} else if (($newBillingPlan->getTotalUsers() != -1) && (($numUser <= $subscription->getTotalActiveUsers()) || ($numUser <= $subscription->getSubscribedUsers()))) {
					throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_TOTAL_USERS_EXCEEDED);
				}
			} else if (($newBillingPlanTotalApplications != -1) && ($newBillingPlanTotalApplications < $subscription->getTotalSubscribedApplications ())) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_TOTAL_APPLICATIONS_EXCEEDED);
			} else if (($newBillingPlanTotalUsers != -1) && ($newBillingPlanTotalUsers < $subscription->getTotalActiveUsers ())) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_TOTAL_USERS_EXCEEDED);
			} else if (($newBillingPlanTotalDiskSpace != -1) && ($newBillingPlanTotalDiskSpace < $subscription->getTotalDiskSpace ())) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_TOTAL_DISK_SPACE_EXCEEDED);
			}
			return $newBillingPlan;
		}
		// @codingStandardsIgnoreEnd

		/**
		 * Elimina las suscripciones
		 *
		 * @param string $instanceCode
		 */
		public function deleteSubscriptions ($instanceCode) {
			if (empty ($instanceCode)) {
				return;
			}

			$this->adb->pquery ('DELETE FROM vtiger_instanceapplications WHERE instancecode=?', array ($instanceCode));
		}

		/**
		 * Busca las aplicaciones suscritas
		 *
		 * @param string $instanceCode
		 *
		 * @return ApplicationSubscription[]|null
		 */
		public function fetchApplicationSubscriptions ($instanceCode) {
			if (empty ($instanceCode)) {
				return null;
			}

			/** @var ADORecordSet $result */
			$result = $this->adb->pquery ('SELECT * FROM vtiger_instances WHERE code=?', array ($instanceCode));
			if ($this->adb->num_rows ($result) > 0) {
				// Obtener las suscripciones a las aplicaciones ordenadas por nombre de la aplicación
				$row                      = $this->adb->fetchByAssoc ($result, -1, false);
				$applicationSubscriptions = ApplicationSubscriptionManager::getInstance ($this->adb)->fetchSubscriptions ($row ['code']);
				if (empty ($applicationSubscriptions)) {
					return null;
				}
				usort ($applicationSubscriptions, array ('PlatformSubscriptionManager', 'sortApplicationSubscriptionsByApplicationName'));
			} else {
				$applicationSubscriptions = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $applicationSubscriptions;
		}

		/**
		 * Busca el modulo suscrito
		 *
		 * @param string $instanceCode
		 * @param string $moduleName
		 *
		 * @return ModuleSubscription|null
		 */
		public function fetchModuleSubscription ($instanceCode, $moduleName) {
			if ((empty ($instanceCode)) || (empty ($moduleName))) {
				return null;
			}

			$iDb = "pg_crm_{$instanceCode}";
			/** @var ADORecordSet $result */
			$result = $this->adb->pquery (
				"SELECT DISTINCT
					{$iDb}.vtiger_tab.tablabel,
					{$iDb}.vtiger_tab.name,
					{$iDb}.vtiger_tab.customized AS moduletype,
					vtiger_instanceapplications.status,
					(SELECT COUNT(*) FROM {$iDb}.vtiger_crmentity WHERE setype={$iDb}.vtiger_tab.name AND deleted=0 AND demo=0) AS totalrecords,
					CASE vtiger_instanceapplications.status WHEN ? THEN 0 WHEN ? THEN -1 ELSE vtiger_instancefreeplanlimits.maxrecords END AS maxrecords,
					CASE vtiger_instanceapplications.status WHEN ? THEN 1 WHEN ? THEN 0 ELSE 2 END AS sequence
				FROM
					{$iDb}.vtiger_tab
					INNER JOIN {$iDb}.vtiger_configapps_tab ON {$iDb}.vtiger_configapps_tab.tabid ={$iDb}.vtiger_tab.tabid
					INNER JOIN {$iDb}.vtiger_config_applications ON {$iDb}.vtiger_config_applications.config_applicationsid={$iDb}.vtiger_configapps_tab.config_applicationsid
					INNER JOIN vtiger_instanceapplications ON vtiger_instanceapplications.applicationcode={$iDb}.vtiger_config_applications.app_code
					LEFT JOIN vtiger_instancefreeplanlimits ON vtiger_instancefreeplanlimits.modulename={$iDb}.vtiger_tab.name
				WHERE
					{$iDb}.vtiger_tab.tabid={$iDb}.vtiger_configapps_tab.tabid AND
					{$iDb}.vtiger_tab.customized IN (0, 1) AND
					{$iDb}.vtiger_tab.isentitytype=1 AND
					{$iDb}.vtiger_tab.name=? AND
					vtiger_instanceapplications.instancecode=?
				ORDER BY
					sequence
				LIMIT 1",
				array (ApplicationSubscription::STATUS_INACTIVE, ApplicationSubscription::STATUS_SUBSCRIBED, ApplicationSubscription::STATUS_INACTIVE, ApplicationSubscription::STATUS_SUBSCRIBED, $moduleName, $instanceCode)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				if ($row ['moduletype'] == ModuleInterface::TYPE_TOOL) {
					$maxRecords = -1;
					$status     = ModuleSubscription::STATUS_SUBSCRIBED;
				} else {
					$maxRecords = $row ['maxrecords'];
					$status     = $this->getModuleSubscriptionStatus ($row ['status']);
				}
				$moduleSubscription = ModuleSubscription::getInstance ()
					->setMaxRecords ($maxRecords)
					->setModuleLabel (getTranslatedString ($row ['tablabel'], $moduleName))
					->setModuleName ($moduleName)
					->setStatus ($status)
					->setTotalRecords ($row ['totalrecords']);
			} else {
				$moduleSubscription = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $moduleSubscription;
		}

		/**
		 * Busca los modulos suscritos
		 *
		 * @param string $instanceCode
		 * @param ApplicationSubscription[] $applicationSubscriptions
		 *
		 * @return ModuleSubscription[]|null
		 */
		public function fetchModuleSubscriptions ($instanceCode, $applicationSubscriptions) {
			if ((empty ($instanceCode)) || (empty ($applicationSubscriptions))) {
				return null;
			}

			$applicationCodes = array_unique (array_map (array ('PlatformSubscriptionManager', 'getApplicationCode'), $applicationSubscriptions));
			$questionMarks    = str_repeat ('?, ', (count ($applicationCodes) - 1)) . '?';
			$iDb              = "pg_crm_{$instanceCode}";
			$result           = $this->adb->pquery (
				"SELECT DISTINCT
					{$iDb}.vtiger_tab.tablabel,
					{$iDb}.vtiger_tab.name,
					{$iDb}.vtiger_tab.customized AS moduletype,
					vtiger_instanceapplications . status,
					(SELECT COUNT(*) FROM {$iDb}.vtiger_crmentity WHERE setype={$iDb}.vtiger_tab.name AND deleted=0 AND demo=0) AS totalrecords,
					CASE vtiger_instanceapplications.status WHEN ? THEN 0 WHEN ? THEN - 1 ELSE vtiger_instancefreeplanlimits.maxrecords END AS maxrecords,
					CASE WHEN vtiger_instanceapplications.status=? THEN 1 WHEN vtiger_instanceapplications.status=? THEN 2 ELSE 0 END AS sequence
				FROM
					vtiger_instanceapplications
					INNER JOIN {$iDb}.vtiger_config_applications ON {$iDb}.vtiger_config_applications.app_code=vtiger_instanceapplications.applicationcode AND {$iDb}.vtiger_config_applications.app_code IN ({$questionMarks})
					INNER JOIN {$iDb}.vtiger_configapps_tab ON {$iDb}.vtiger_configapps_tab.config_applicationsid={$iDb}.vtiger_config_applications.config_applicationsid
					INNER JOIN {$iDb}.vtiger_tab ON {$iDb}.vtiger_tab.tabid={$iDb}.vtiger_configapps_tab.tabid AND {$iDb}.vtiger_tab.isentitytype=1
					LEFT JOIN vtiger_instancefreeplanlimits ON vtiger_instancefreeplanlimits.modulename={$iDb}.vtiger_tab.name
				WHERE
					vtiger_instanceapplications.instancecode=?
				ORDER BY
					sequence",
				array_merge (array (ApplicationSubscription::STATUS_INACTIVE, ApplicationSubscription::STATUS_SUBSCRIBED, ApplicationSubscription::STATUS_INACTIVE, ApplicationSubscription::STATUS_SUBSCRIBED), $applicationCodes, array ($instanceCode))
			);
			if ($this->adb->num_rows ($result) > 0) {
				/** @var ModuleSubscription[] $moduleSubscriptions */
				$moduleSubscriptions = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['moduletype'] == ModuleInterface::TYPE_TOOL) {
						$maxRecords = -1;
						$status     = ModuleSubscription::STATUS_SUBSCRIBED;
					} else {
						$maxRecords = $row ['maxrecords'];
						$status     = $this->getModuleSubscriptionStatus ($row ['status']);
					}
					$moduleName                          = $row ['name'];
					$moduleSubscriptions [ $moduleName ] = ModuleSubscription::getInstance ()
						->setMaxRecords ($maxRecords)
						->setModuleLabel (getTranslatedString ($row ['tablabel'], $moduleName))
						->setModuleName ($moduleName)
						->setStatus ($status)
						->setTotalRecords ($row ['totalrecords']);
				}
				usort ($moduleSubscriptions, array ('PlatformSubscriptionManager', 'sortModuleSubscriptionsByModuleLabel'));
			} else {
				$moduleSubscriptions = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return count ($moduleSubscriptions) > 0 ? $moduleSubscriptions : null;
		}

		/**
		 * Busca la suscripcion
		 *
		 * @param string $instanceCode
		 * @param boolean $includeGatewayData
		 *
		 * @return null|PlatformSubscription
		 * @throws Exception
		 */
		public function fetchSubscription ($instanceCode, $includeGatewayData = false) {
			if (empty ($instanceCode)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					i.*,
					(SELECT COUNT(*) FROM vtiger_instanceusers iu WHERE iu.instancecode=i.code) AS totalactiveusers
				FROM
					vtiger_instances i
				WHERE
					code=?',
				array ($instanceCode)
			);
			if ($this->adb->num_rows ($result) > 0) {
				// Obtener las suscripciones a las aplicaciones ordenadas por nombre de la aplicación
				$row                      = $this->adb->fetchByAssoc ($result, -1, false);
				$applicationSubscriptions = ApplicationSubscriptionManager::getInstance ($this->adb)->fetchSubscriptions ($row ['code']);
				if (!empty ($applicationSubscriptions)) {
					usort ($applicationSubscriptions, array ('PlatformSubscriptionManager', 'sortApplicationSubscriptionsByApplicationName'));
					// Obtener las suscripciones a los módulos
					$moduleSubscriptions = $this->fetchModuleSubscriptions ($instanceCode, $applicationSubscriptions);
				} else {
					$moduleSubscriptions = null;
				}

				// Determinar el status de la suscripción
				$today = date_create ()->setTime (0, 0, 0);
				// Agregaremos el día de cobro como día usable porque Braintree toma su tiempo en cobrar, y no queremos castigar al usuario por demoras de Braintree
				$oneDayInterval      = new DateInterval ('P1D');
				$fourteenDaysInterval = new DateInterval ('P14D');
				if ((!empty ($row ['serviceenddate'])) && ($today > date_create ($row ['serviceenddate'])->setTime (0, 0, 0)->add ($oneDayInterval))) {
					// El usuario está suscrito pero su servicio ha caducado
					$status = PlatformSubscription::STATUS_INACTIVE;
				} else if ((empty ($row ['serviceenddate'])) && ($today > date_create ($row ['registrationdate'])->setTime (0, 0, 0)->add ($fourteenDaysInterval))) {
					// El usuario no está suscrito y vencieron los 14 días de prueba
					$status = PlatformSubscription::STATUS_INACTIVE;
				} else {
					$status = PlatformSubscription::STATUS_ACTIVE;
				}

				// Incluir la información proveniente de la pasarela de pagos
				if ($includeGatewayData) {
					try {
						$customer                   = PaymentGatewayManager::getInstance ()->fetchInstanceCustomer ($row ['code']);
						$paymentGatewayErrorMessage = null;
					} catch (\Braintree\Exception $e) {
						$customer                   = null;
						$paymentGatewayErrorMessage = $e->getMessage ();
					}
				} else {
					$customer                   = null;
					$paymentGatewayErrorMessage = null;
				}

				// Para obtener la suscripción actual, hay que obtener el plan con la tarifa estándar y el impuesto aplicable
				// Generar la suscripción
				$subscription = PlatformSubscription::getInstance ()
					->setAccountId ($row ['accountid'])
					->setApplicationSubscriptions ($applicationSubscriptions)
					->setBillingPlan (PlatformBillingPlanManager::getInstance ($this->adb)->fetchCurrentPlan ($row ['code'], $row ['subscribedusers']))
					->setCustomer ($customer)
					->setInstanceCode ($row ['code'])
					->setLastGatewayErrorMessage ($paymentGatewayErrorMessage)
					->setModuleSubscriptions ($moduleSubscriptions)
					->setPendingPayments (PaymentManager::getInstance ($this->adb)->fetchPendingPayments ($row ['code'], date_create ('today')))
					->setPricebookId ($row ['pricebookid'])
					->setRegistrationDate ($row ['registrationdate'])
					->setServiceEndDate ($row ['serviceenddate'])
					->setServiceStartDate ($row ['servicestartdate'])
					->setStatus ($status)
					->setSubscribedUsers ($row ['subscribedusers'])
					->setTotalActiveUsers ($row ['totalactiveusers'])
					->setTotalDiskSpace ($this->getInstanceUsedDiskSpace ($row ['code']));
			} else {
				$subscription = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $subscription;
		}

		/**
		 * Para obtener la fecha fin de la suscripcion
		 *
		 * @param Datetime $serviceEndDate
		 * @param Datetime $ubscriptionStartDate
		 *
		 * @return DateTime
		 */
		public function getSubscriptionEndDate ($serviceEndDate, $ubscriptionStartDate) {
			if (!empty ($serviceEndDate)) {
				$subscriptionEndDate = date_create ($serviceEndDate->format ('Y-m-d'));
			} else {
				$oneDayInterval      = new DateInterval ('P1D');
				$oneMonthInterval    = new DateInterval ('P1M');
				$subscriptionEndDate = date_create ($ubscriptionStartDate->format ('Y-m-d'))->add ($oneMonthInterval)->sub ($oneDayInterval);
			}
			return $subscriptionEndDate->setTime (23, 59, 59);
		}

		/**
		 * Para obtener la fecha de inicio de la suscripcion
		 *
		 * @param DateTime $serviceStartDate
		 * @param DateTime $now
		 *
		 * @return DateTime
		 */
		public function getSubscriptionStartDate ($serviceStartDate, $now) {
			if (!empty ($serviceStartDate)) {
				$dummy = date_create ($serviceStartDate->format ('Y-m-d'));
			} else {
				$dummy = date_create ($now->format ('Y-m-d'));
			}
			$day = intval ($dummy->format ('d'));
			if (in_array ($day, array (29, 30, 31))) {
				$oneDayInterval        = new DateInterval ('P1D');
				$subscriptionStartDate = date_create ("{$dummy->format ('Y')}-{$dummy->format ('m')}-{$dummy->format ('t')}")->add ($oneDayInterval);
			} else {
				$subscriptionStartDate = date_create ($dummy->format ('Y-m-d'));
			}
			return $subscriptionStartDate->setTime (0, 0, 0);
		}

		/**
		 * Para salvar/guardar la suscripcion
		 *
		 * @param PlatformSubscription $subscription
		 *
		 * @return PlatformSubscription
		 * @throws PlatformSubscriptionException
		 */
		public function saveSubscription ($subscription) {
			if (!($subscription instanceof PlatformSubscription)) {
				return $subscription;
			}

			$subscription->validate ();

			$instanceCode             = $subscription->getInstanceCode ();
			$applicationSubscriptions = $subscription->getApplicationSubscriptions ();
			if (empty ($applicationSubscriptions)) {
				$this->adb->pquery ('DELETE FROM vtiger_instanceapplications WHERE instancecode=?', array ($instanceCode));
				return $subscription;
			}

			$processedApplicationCodes = array ();
			foreach ($applicationSubscriptions as $applicationSubscription) {
				$applicationCode  = $applicationSubscription->getApplicationCode ();
				$registrationDate = !empty ($applicationSubscription->getRegistrationDate ()) ? $applicationSubscription->getRegistrationDate ()->format ('y-m-d') : null;
				$serviceStartDate = !empty ($applicationSubscription->getServiceStartDate ()) ? $applicationSubscription->getServiceStartDate ()->format ('y-m-d') : null;
				$serviceEndDate   = !empty ($applicationSubscription->getServiceEndDate ()) ? $applicationSubscription->getServiceEndDate ()->format ('y-m-d') : null;
				$result           = $this->adb->pquery ('SELECT * FROM vtiger_instanceapplications WHERE instancecode=? AND applicationcode=?', array ($instanceCode, $applicationCode));
				if ($this->adb->num_rows ($result) == 0) {
					$this->adb->pquery (
						'INSERT INTO vtiger_instanceapplications (instancecode, applicationcode, status, registrationdate, servicestartdate, serviceenddate) VALUES (?, ?, ?, ?, ?, ?)',
						array ($instanceCode, $applicationCode, $applicationSubscription->getStatus (), $registrationDate, $serviceStartDate, $serviceEndDate)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_instanceapplications SET status=?, registrationdate=?, servicestartdate=?, serviceenddate=? WHERE instancecode=? AND applicationcode=?',
						array ($applicationSubscription->getStatus (), $registrationDate, $serviceStartDate, $serviceEndDate, $instanceCode, $applicationCode)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result                       = null;
				$processedApplicationCodes [] = $applicationCode;
			}
			$questionMarks = str_repeat ('?, ', (count ($processedApplicationCodes) - 1)) . '?';
			$this->adb->pquery (
				"DELETE FROM vtiger_instanceapplications WHERE instancecode =? AND applicationcode NOT IN ({$questionMarks})",
				array_merge (array ($instanceCode, $processedApplicationCodes))
			);
			return $subscription;
		}

		/**
		 * Actualiza la suscripcion en la instancia con la fecha de pago
		 *
		 * @param string $instanceCode
		 *
		 * @throws PaymentGatewayException
		 */
		public function updateSubscriptionWithPaymentData ($instanceCode) {
			$subscription = $this->fetchSubscription ($instanceCode);
			if (empty ($subscription)) {
				return;
			}

			try {
				$billingPlan = $subscription->getBillingPlan ();
				$product     = $billingPlan->getProduct ();
				$payment     = PaymentGatewayManager::getInstance ()->fetchLastSubscriptionPayment ($instanceCode, $product->getTax ()->getPercentage ());
			} catch (PaymentGatewayException $pge) {
				$payment = null;
				$product = null;
			}
			if (empty ($payment)) {
				return;
			}

			$oneMonthInterval = new DateInterval ('P1M');
			$serviceEndDate   = date_create ($subscription->getServiceEndDate ()->format ('Y-m-d'))->add ($oneMonthInterval);
			if (($payment->getServiceEndDate () < $serviceEndDate) || (!$payment->isPaid ())) {
				return;
			}

			$this->adb->pquery (
				'UPDATE vtiger_instances SET servicestartdate=?, serviceenddate=? WHERE code=?',
				array ($payment->getServiceStartDate ()->format ('Y-m-d'), $payment->getServiceEndDate ()->format ('Y-m-d'), $instanceCode)
			);
			$this->adb->pquery (
				'UPDATE vtiger_instanceapplications SET servicestartdate=?, serviceenddate=? WHERE instancecode=?',
				array ($payment->getServiceStartDate ()->format ('Y-m-d'), $payment->getServiceEndDate ()->format ('Y-m-d'), $instanceCode)
			);
			$payment->setProductId ($product->getId ())
				->setProductName ($product->getName ());
			$invoice = $this->createInvoice ($subscription, $payment);
			$payment->setInvoiceId ($invoice->getId ());
			PaymentManager::getInstance ($this->adb)->savePayment ($payment);
		}

		/**
		 * Actualiza la suscripcion de las instancias con sus fechas de pago
		 *
		 * @throws PaymentGatewayException
		 * @throws PlatformException
		 * @throws Exception
		 */
		public function updateSubscriptionsWithPaymentData () {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_instances WHERE serviceenddate IS NOT NULL AND serviceenddate<=? ORDER BY instanceid', array (date_create ()->format ('Y-m-d')));
			if ($this->adb->num_rows ($result) > 0) {
				$errorMessages = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					try {
						$this->updateSubscriptionWithPaymentData ($row ['code']);
					} catch (Exception $e) {
						$errorMessages [] = $e->getMessage ();
					}
				}
			} else {
				$errorMessages = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (!empty ($errorMessages)) {
				throw new PlatformSubscriptionException (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_UNABLE_TO_UPDATE_SUBSCRIPTIONS . ': ' . join (PHP_EOL, $errorMessages));
			}
		}

		/**
		 * Instanciación de la clase PlatformSubscriptionManager. Se obtiene un objeto PlatformSubscriptionManager con los atributos de la clase
		 *
		 * @param PearDatabase $adb
		 *
		 * @return PlatformSubscriptionManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ($adb);
			}
			return self::$INSTANCE;
		}

	}

<?php
	require_once ('include/platzilla/Objects/ApplicationSubscription.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ApplicationSubscriptionManager {
		/** @var ApplicationSubscriptionManager */
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param ApplicationSubscription $subscription
		 */
		public function deleteSubscription ($subscription) {
			if ((empty ($subscription)) || (!($subscription instanceof ApplicationSubscription))) {
				return;
			}

			$this->adb->pquery ('DELETE FROM vtiger_instanceapplications WHERE instancecode=? AND applicationcode=?', array ($subscription->getInstanceCode (), $subscription->getApplicationCode ()));
		}

		/**
		 * @param string $instanceCode
		 */
		public function deleteSubscriptions ($instanceCode) {
			if (empty ($instanceCode)) {
				return;
			}

			$this->adb->pquery ('DELETE FROM vtiger_instanceapplications WHERE instancecode=?', array ($instanceCode));
		}

		/**
		 * @param string $instanceCode
		 * @param string $applicationCode
		 *
		 * @return ApplicationSubscription|null
		 */
		public function fetchSubscription ($instanceCode, $applicationCode) {
			if ((empty ($instanceCode)) || (empty ($applicationCode))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					ia.*,
					ca.app_descripcion,
					ca.app_name
				FROM
					vtiger_instanceapplications ia
					INNER JOIN vtiger_config_applications ca ON ca.app_code=ia.applicationcode
				WHERE
					ia.instancecode=? AND
					ia.applicationcode=?',
				array ($instanceCode, $applicationCode)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row          = $this->adb->fetchByAssoc ($result, -1, false);
				$subscription = ApplicationSubscription::getInstance ()
					->setApplicationCode ($row ['applicationcode'])
					->setApplicationDescription ($row ['app_descripcion'])
					->setApplicationName ($row ['app_name'])
					->setInstanceCode ($row ['instancecode'])
					->setRegistrationDate ($row ['registrationdate'])
					->setServiceEndDate ($row ['serviceenddate'])
					->setServiceStartDate ($row ['servicestartdate'])
					->setStatus ($row ['status']);
			} else {
				$subscription = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $subscription;
		}

		/**
		 * @param string $instanceCode
		 * @param string[]|null $statuses
		 *
		 * @return ApplicationSubscription[]\null
		 */
		public function fetchSubscriptions ($instanceCode, $statuses = null) {
			if (empty ($instanceCode)) {
				return null;
			}

			if ((!is_array ($statuses)) || (empty ($statuses))) {
				$statuses = ApplicationSubscription::getAvailableStatuses ();
			}
			$questionMarks = str_repeat ('?, ', (count ($statuses) - 1)) . '?';
			$result        = $this->adb->pquery (
				"SELECT
					ia.*,
					ca.app_descripcion,
					ca.app_name
				FROM
					vtiger_instanceapplications ia
					INNER JOIN vtiger_config_applications ca ON ca.app_code=ia.applicationcode
				WHERE
					ia.instancecode=? AND
					ia.status IN ({$questionMarks})",
				array_merge (array ($instanceCode), $statuses)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$subscriptions = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$subscriptions [] = ApplicationSubscription::getInstance ()
						->setApplicationCode ($row ['applicationcode'])
						->setApplicationDescription ($row ['app_descripcion'])
						->setApplicationName ($row ['app_name'])
						->setInstanceCode ($row ['instancecode'])
						->setRegistrationDate ($row ['registrationdate'])
						->setServiceEndDate ($row ['serviceenddate'])
						->setServiceStartDate ($row ['servicestartdate'])
						->setStatus ($row ['status']);
				}
			} else {
				$subscriptions = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $subscriptions;
		}

		/**
		 * @param ApplicationSubscription $subscription
		 *
		 * @return ApplicationSubscription
		 */
		public function saveSubscription ($subscription) {
			if (empty ($subscription)) {
				return null;
			}

			$subscription->validate ();

			$instanceCode     = $subscription->getInstanceCode ();
			$applicationCode  = $subscription->getApplicationCode ();
			$registrationDate = !empty ($subscription->getRegistrationDate ()) ? $subscription->getRegistrationDate ()->format ('Y-m-d') : null;
			$serviceEndDate   = !empty ($subscription->getServiceEndDate ()) ? $subscription->getServiceEndDate ()->format ('Y-m-d') : null;
			$serviceStartDate = !empty ($subscription->getServiceStartDate ()) ? $subscription->getServiceStartDate ()->format ('Y-m-d') : null;
			$result           = $this->adb->pquery ('SELECT * FROM vtiger_instanceapplications WHERE instancecode=? AND applicationcode=?', array ($instanceCode, $applicationCode));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_instanceapplications (instancecode, applicationcode, status, registrationdate, servicestartdate, serviceenddate) VALUES (?, ?, ?, ?, ?, ?)',
					array ($instanceCode, $applicationCode, $subscription->getStatus (), $registrationDate, $serviceStartDate, $serviceEndDate)
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_instanceapplications SET status=?, registrationdate=?, servicestartdate=?, serviceenddate=? WHERE instancecode=? AND applicationcode=?',
					array ($subscription->getStatus (), $registrationDate, $serviceStartDate, $serviceEndDate, $instanceCode, $applicationCode)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $subscription;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ApplicationSubscriptionManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ($adb);
			}
			return self::$INSTANCE;
		}

	}

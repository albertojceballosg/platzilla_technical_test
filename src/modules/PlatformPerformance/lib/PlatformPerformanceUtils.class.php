<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	abstract class PlatformPerformanceUtils {

		/**
		 * @param PearDatabase $adb
		 *
		 * @return string[]
		 * @throws Exception
		 */
		private static function fetchExpiredInstanceCodes (PearDatabase $adb) {
			$result = $adb->pquery (
				'SELECT
					i.*
				FROM
					vtiger_instances i
				WHERE
					SUBSTRING(i.code, 1, 5) = ? AND
					CAST(SUBSTRING(i.code, 6) AS UNSIGNED) >= ? AND
					i.servicestartdate IS NULL AND
					DATE(i.registrationdate) < CURDATE() - INTERVAL 15 DAY',
				array ('appef', 1)
			);
			if ($adb->num_rows ($result) > 0) {
				$instanceCodes = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$dummy          = $adb->pquery ('SHOW DATABASES LIKE ?', array ("pg_crm_{$row ['code']}"));
					$databaseExists = ($adb->num_rows ($dummy) > 0);
					DatabaseUtils::closeResult ($dummy);
					if ($databaseExists) {
						$instanceCodes [] = $row ['code'];
					}
				}
			} else {
				$instanceCodes = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $instanceCodes;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return string[]|null
		 * @throws Exception
		 */
		private static function fetchActiveInstanceCodes (PearDatabase $adb, $startDate, $endDate) {
			$result = $adb->pquery (
				'SELECT
					i.*
				FROM
					vtiger_instances i
				WHERE
					SUBSTRING(i.code, 1, 5) = ? AND
					CAST(SUBSTRING(i.code, 6) AS UNSIGNED) >= ? AND
					i.servicestartdate IS NULL AND
					i.registrationdate >= ? AND
					i.registrationdate <= ?',
				array ('appef', 1, $startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
			);
			if ($adb->num_rows ($result) > 0) {
				$instanceCodes = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$dummy          = $adb->pquery ('SHOW DATABASES LIKE ?', array ("pg_crm_{$row ['code']}"));
					$databaseExists = ($adb->num_rows ($dummy) > 0);
					DatabaseUtils::closeResult ($dummy);
					if ($databaseExists) {
						$date                    = $row ['registrationdate'];
						$instanceCodes [ $date ] = $row ['code'];
					}
				}
			} else {
				$instanceCodes = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $instanceCodes;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return string[]
		 * @throws Exception
		 */
		private static function fetchInstanceCodes (PearDatabase $adb) {
			$result = $adb->pquery (
				'SELECT
					i.*
				FROM
					vtiger_instances i
				WHERE
					SUBSTRING(i.code, 1, 5) = ? AND
					CAST(SUBSTRING(i.code, 6) AS UNSIGNED) >= ?',
				array ('appef', 1)
			);
			if ($adb->num_rows ($result) > 0) {
				$instanceCodes = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$dummy          = $adb->pquery ('SHOW DATABASES LIKE ?', array ("pg_crm_{$row ['code']}"));
					$databaseExists = ($adb->num_rows ($dummy) > 0);
					DatabaseUtils::closeResult ($dummy);
					if ($databaseExists) {
						$instanceCodes [] = $row ['code'];
					}
				}
			} else {
				$instanceCodes = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $instanceCodes;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return string[]
		 * @throws Exception
		 */
		private static function fetchRegisteredInstanceCodes (PearDatabase $adb, $startDate, $endDate) {
			$result = $adb->pquery (
				'SELECT
					i.*
				FROM
					vtiger_instances i
				WHERE
					i.registrationdate >= ? AND
					i.registrationdate <= ? AND
					SUBSTRING(i.code, 1, 5) = ? AND
					CAST(SUBSTRING(i.code, 6) AS UNSIGNED) >= ?',
				array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'), 'appef', 1,)
			);
			if ($adb->num_rows ($result) > 0) {
				$registeredInstanceCodes = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$dummy          = $adb->pquery ('SHOW DATABASES LIKE ?', array ("pg_crm_{$row ['code']}"));
					$databaseExists = ($adb->num_rows ($dummy) > 0);
					DatabaseUtils::closeResult ($dummy);
					if ($databaseExists) {
						$registeredInstanceCodes [] = $row ['code'];
					}
				}
			} else {
				$registeredInstanceCodes = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $registeredInstanceCodes;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string[] $instanceCodes
		 * @param integer $minimumSessions
		 *
		 * @return string[]
		 * @throws Exception
		 */
		private static function fetchInstanceCodesByMinimumTotalSessions (PearDatabase $adb, $instanceCodes, $minimumSessions) {
			$instanceCodesWithMinimumSessions = array ();
			foreach ($instanceCodes as $instanceCode) {
				$iDb    = "pg_crm_{$instanceCode}";
				$result = $adb->pquery (
					"SELECT
						COUNT(DISTINCT {$iDb}.vtiger_audit_trial.sessionid) AS total
					FROM
						{$iDb}.vtiger_audit_trial
					GROUP BY
						{$iDb}.vtiger_audit_trial.userid
					HAVING
						COUNT(DISTINCT {$iDb}.vtiger_audit_trial.sessionid) >= ?
					ORDER BY
						COUNT(DISTINCT {$iDb}.vtiger_audit_trial.sessionid) DESC
					LIMIT 1",
					array ($minimumSessions)
				);
				if ($adb->num_rows ($result) > 0) {
					$instanceCodesWithMinimumSessions [] = $instanceCode;
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return !empty ($instanceCodesWithMinimumSessions) ? $instanceCodesWithMinimumSessions : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string[] $instanceCodes
		 * @param integer $minimumRecords
		 *
		 * @return string[]
		 * @throws Exception
		 */
		private static function fetchInstanceCodesByMinimumTotalRecords (PearDatabase $adb, $instanceCodes, $minimumRecords) {
			$instanceCodesWithMinimumRecords = array ();
			foreach ($instanceCodes as $instanceCode) {
				$iDb    = "pg_crm_{$instanceCode}";
				$result = $adb->pquery (
					"SELECT
						COUNT(DISTINCT {$iDb}.vtiger_crmentity.crmid) AS totalsessions
					FROM
						{$iDb}.vtiger_crmentity
					WHERE
						{$iDb}.vtiger_crmentity.setype NOT LIKE '%Attachment' AND
						{$iDb}.vtiger_crmentity.setype NOT IN (SELECT name FROM {$iDb}.vtiger_settings_field WHERE tab IS NULL) AND
						{$iDb}.vtiger_crmentity.deleted = 0 AND
						{$iDb}.vtiger_crmentity.demo = 0
					GROUP BY
						{$iDb}.vtiger_crmentity.setype
					HAVING
						COUNT(DISTINCT {$iDb}.vtiger_crmentity.crmid) >= ?
					ORDER BY
						COUNT(DISTINCT {$iDb}.vtiger_crmentity.crmid) DESC
					LIMIT 1",
					array ($minimumRecords)
				);
				if ($adb->num_rows ($result) > 0) {
					$instanceCodesWithMinimumRecords [] = $instanceCode;
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return !empty ($instanceCodesWithMinimumRecords) ? $instanceCodesWithMinimumRecords : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $instanceCode
		 *
		 * @return string[]|null
		 */
		private static function fetchSessionDates (PearDatabase $adb, $instanceCode) {
			$iDb    = "pg_crm_{$instanceCode}";
			$result = $adb->query ("SELECT DISTINCT CAST({$iDb}.vtiger_audit_trial.actiondate AS DATE) AS actiondate FROM {$iDb}.vtiger_audit_trial");
			if ($adb->num_rows ($result) > 0) {
				$dates = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$dates [] = $row ['actiondate'];
				}
			} else {
				$dates = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $dates;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string[] $instanceCodes
		 *
		 * @return string[]
		 * @throws Exception
		 */
		private static function fetchSubscribedInstanceCodes (PearDatabase $adb, $instanceCodes) {
			$questionMarks = str_repeat ('?, ', (count ($instanceCodes) - 1)) . '?';
			$result        = $adb->pquery (
				"SELECT * FROM vtiger_instances i WHERE i.code IN ({$questionMarks}) AND i.servicestartdate IS NOT NULL",
				$instanceCodes
			);
			if ($adb->num_rows ($result) > 0) {
				$subscribedInstanceCodes = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$subscribedInstanceCodes [] = $row ['code'];
				}
			} else {
				$subscribedInstanceCodes = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $subscribedInstanceCodes;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 * @param integer $minimumSessions
		 * @param integer $minimumRecords
		 *
		 * @return integer[]
		 * @throws Exception
		 */
		public static function fetchOnboardingEvolutionData (PearDatabase $adb, $startDate, $endDate, $minimumSessions, $minimumRecords) {
			$evolutionData = array ();

			$registeredInstanceCodes = self::fetchRegisteredInstanceCodes ($adb, $startDate, $endDate);
			if (empty ($registeredInstanceCodes)) {
				return null;
			}
			$evolutionData ['Altas'] = floatval (count ($registeredInstanceCodes));

			$minimumSessionsInstanceCodes = self::fetchInstanceCodesByMinimumTotalSessions ($adb, $registeredInstanceCodes, $minimumSessions);
			if (empty ($minimumSessionsInstanceCodes)) {
				return $evolutionData;
			}
			$evolutionData ['Sesiones'] = floatval (count ($minimumSessionsInstanceCodes));

			$minimumRecordsInstanceCodes = self::fetchInstanceCodesByMinimumTotalRecords ($adb, $minimumSessionsInstanceCodes, $minimumRecords);
			if (empty ($minimumRecordsInstanceCodes)) {
				return $evolutionData;
			}
			$evolutionData ['Registros'] = floatval (count ($minimumRecordsInstanceCodes));

			$subscribedInstanceCodes = self::fetchSubscribedInstanceCodes ($adb, $minimumRecordsInstanceCodes);
			if (empty ($subscribedInstanceCodes)) {
				return $evolutionData;
			}
			$evolutionData ['Suscripciones'] = floatval (count ($subscribedInstanceCodes));

			return $evolutionData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return integer[]
		 * @throws Exception
		 */
		public static function fetchRegistrationsVsSubscriptionsData (PearDatabase $adb, $startDate, $endDate) {
			$result = $adb->pquery (
				'SELECT
					COUNT(i.instanceid) AS total
				FROM
					vtiger_instances i
				WHERE
					i.registrationdate >= ? AND
					i.registrationdate <= ?',
				array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
			);
			if ($adb->num_rows ($result) > 0) {
				$row           = $adb->fetchByAssoc ($result, -1, false);
				$registrations = intval ($row ['total']);
			} else {
				$registrations = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $adb->pquery (
				'SELECT
					COUNT(i.instanceid) AS total
				FROM
					vtiger_instances i
				WHERE
					i.servicestartdate >= ? AND
					i.servicestartdate <= ?',
				array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
			);
			if ($adb->num_rows ($result) > 0) {
				$row           = $adb->fetchByAssoc ($result, -1, false);
				$subscriptions = intval ($row ['total']);
			} else {
				$subscriptions = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return array (
				'Registros'     => $registrations,
				'Suscripciones' => $subscriptions,
			);
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return integer[]
		 * @throws Exception
		 */
		public static function fetchRegistrationsBySourceData (PearDatabase $adb, $startDate, $endDate) {
			$sources = array ();
			$result  = $adb->query ('SELECT DISTINCT i.source FROM vtiger_instances i ORDER BY i.source');
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$sources [] = $row ['source'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $adb->pquery (
				'SELECT
					i.registrationdate,
					i.source,
					COUNT(i.instanceid) AS total
				FROM
					vtiger_instances i
				WHERE
					i.registrationdate >= ? AND
					i.registrationdate <= ?
				GROUP BY
					i.registrationdate,
					i.source',
				array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
			);
			if ($adb->num_rows ($result) > 0) {
				$registrations = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$date                               = $row ['registrationdate'];
					$source                             = $row ['source'];
					$registrations [ $date ][ $source ] = intval ($row ['total']);
				}
			} else {
				$registrations = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$oneDayInterval = new DateInterval ('P1D');
			$dummy          = date_create ($startDate->format ('Y-m-d'));
			while ($dummy < $endDate) {
				$date = $dummy->format ('Y-m-d');
				foreach ($sources as $source) {
					if (!isset ($registrations [ $date ][ $source ])) {
						$registrations [ $date ][ $source ] = 0;
					}
				}
				$dummy->add ($oneDayInterval);
			}
			ksort ($registrations);
			return $registrations;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return integer[]
		 * @throws Exception
		 */
		public static function fetchTotalDailyRegistrations (PearDatabase $adb, $startDate, $endDate) {
			$registrations = array ();
			$result        = $adb->pquery (
				'SELECT
					i.registrationdate,
					COUNT(i.instanceid) AS total
				FROM
					vtiger_instances i
				WHERE
					i.registrationdate >= ? AND
					i.registrationdate <= ?
				GROUP BY
					i.registrationdate
				ORDER BY
					i.registrationdate',
				array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$registrationDate                    = $row ['registrationdate'];
					$registrations [ $registrationDate ] = intval ($row ['total']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$oneDayInterval = new DateInterval ('P1D');
			$dummy          = date_create ($startDate->format ('Y-m-d'));
			while ($dummy < $endDate) {
				$registrationDate = $dummy->format ('Y-m-d');
				if (!isset ($registrations [ $registrationDate ])) {
					$registrations [ $registrationDate ] = 0;
				}
				$dummy->add ($oneDayInterval);
			}
			ksort ($registrations);
			return $registrations;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return integer[]
		 * @throws Exception
		 */
		public static function fetchTotalDailySubscriptions (PearDatabase $adb, $startDate, $endDate) {
			$subscriptions = array ();
			$result        = $adb->pquery (
				'SELECT
					i.servicestartdate,
					COUNT(i.instanceid) AS total
				FROM
					vtiger_instances i
				WHERE
					i.servicestartdate >= ? AND
					i.servicestartdate <= ?
				GROUP BY
					i.servicestartdate
				ORDER BY
					i.servicestartdate',
				array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$subscriptionDate                    = $row ['servicestartdate'];
					$subscriptions [ $subscriptionDate ] = intval ($row ['total']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$oneDayInterval = new DateInterval ('P1D');
			$dummy          = date_create ($startDate->format ('Y-m-d'));
			while ($dummy < $endDate) {
				$subscriptionDate = $dummy->format ('Y-m-d');
				if (!isset ($subscriptions [ $subscriptionDate ])) {
					$subscriptions [ $subscriptionDate ] = 0;
				}
				$dummy->add ($oneDayInterval);
			}
			ksort ($subscriptions);
			return $subscriptions;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchTotalRecordsPerApplication (PearDatabase $adb, $startDate, $endDate) {
			$instanceCodes = self::fetchInstanceCodes ($adb);
			if (empty ($instanceCodes)) {
				return null;
			}

			$records = array ();
			foreach ($instanceCodes as $instanceCode) {
				$iDb    = "pg_crm_{$instanceCode}";
				$result = $adb->pquery (
					"SELECT
						{$iDb}.vtiger_config_applications.app_name,
						COUNT({$iDb}.vtiger_crmentity.crmid) AS total
					FROM
						{$iDb}.vtiger_crmentity
						INNER JOIN {$iDb}.vtiger_tab ON {$iDb}.vtiger_tab.name={$iDb}.vtiger_crmentity.setype
						INNER JOIN {$iDb}.vtiger_configapps_tab ON {$iDb}.vtiger_configapps_tab.tabid={$iDb}.vtiger_tab.tabid
						INNER JOIN {$iDb}.vtiger_config_applications ON {$iDb}.vtiger_config_applications.config_applicationsid={$iDb}.vtiger_configapps_tab.config_applicationsid
					WHERE
						{$iDb}.vtiger_crmentity.setype NOT LIKE '%Attachment' AND
						{$iDb}.vtiger_crmentity.setype NOT IN (SELECT name FROM {$iDb}.vtiger_settings_field WHERE tab IS NULL) AND
						{$iDb}.vtiger_crmentity.deleted=0 AND
						{$iDb}.vtiger_crmentity.demo=0 AND
						{$iDb}.vtiger_crmentity.createdtime >= ? AND
						{$iDb}.vtiger_crmentity.createdtime <= ?
					GROUP BY
						{$iDb}.vtiger_config_applications.app_name
					ORDER BY
						{$iDb}.vtiger_config_applications.app_name",
					array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
				);
				if ($adb->num_rows ($result) > 0) {
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						$applicationName = $row ['app_name'];
						$records [ $applicationName ] += intval ($row ['total']);
					}
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return count ($records) > 0 ? $records : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchTotalRecordsPerCustomer (PearDatabase $adb, $startDate, $endDate) {
			$instanceCodes = self::fetchInstanceCodes ($adb);
			if (empty ($instanceCodes)) {
				return null;
			}

			$records = array ();
			foreach ($instanceCodes as $instanceCode) {
				$iDb    = "pg_crm_{$instanceCode}";
				$result = $adb->pquery (
					"SELECT
						COUNT({$iDb}.vtiger_crmentity.crmid) AS total
					FROM
						{$iDb}.vtiger_crmentity
					WHERE
						{$iDb}.vtiger_crmentity.setype NOT LIKE '%Attachment' AND
						{$iDb}.vtiger_crmentity.setype NOT IN (SELECT name FROM {$iDb}.vtiger_settings_field WHERE tab IS NULL) AND
						{$iDb}.vtiger_crmentity.deleted=0 AND
						{$iDb}.vtiger_crmentity.demo=0 AND
						{$iDb}.vtiger_crmentity.createdtime >= ? AND
						{$iDb}.vtiger_crmentity.createdtime <= ?
					GROUP BY
						{$iDb}.vtiger_crmentity.setype
					ORDER BY
						{$iDb}.vtiger_crmentity.setype",
					array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
				);
				if ($adb->num_rows ($result) > 0) {
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						$records [ $instanceCode ] += intval ($row ['total']);
					}
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				arsort ($records);
				$records = array_slice ($records, 0, 10);
			}
			return count ($records) > 0 ? $records : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchTotalRecordsPerModule (PearDatabase $adb, $startDate, $endDate) {
			$instanceCodes = self::fetchInstanceCodes ($adb);
			if (empty ($instanceCodes)) {
				return null;
			}

			$records = array ();
			foreach ($instanceCodes as $instanceCode) {
				$iDb    = "pg_crm_{$instanceCode}";
				$result = $adb->pquery (
					"SELECT
						{$iDb}.vtiger_crmentity.setype,
						COUNT({$iDb}.vtiger_crmentity.crmid) AS total
					FROM
						{$iDb}.vtiger_crmentity
					WHERE
						{$iDb}.vtiger_crmentity.setype NOT LIKE '%Attachment' AND
						{$iDb}.vtiger_crmentity.setype NOT IN (SELECT name FROM {$iDb}.vtiger_settings_field WHERE tab IS NULL) AND
						{$iDb}.vtiger_crmentity.deleted=0 AND
						{$iDb}.vtiger_crmentity.demo=0 AND
						{$iDb}.vtiger_crmentity.createdtime >= ? AND
						{$iDb}.vtiger_crmentity.createdtime <= ?
					GROUP BY
						{$iDb}.vtiger_crmentity.setype
					ORDER BY
						{$iDb}.vtiger_crmentity.setype",
					array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
				);
				if ($adb->num_rows ($result) > 0) {
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						$moduleLabel = getTranslatedString ($row ['setype'], $row ['setype']);
						$records [ $moduleLabel ] += intval ($row ['total']);
					}
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return count ($records) > 0 ? $records : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchTotalRegistrationsPerSource (PearDatabase $adb, $startDate, $endDate) {
			$subscriptions = array ();
			$result        = $adb->pquery (
				'SELECT
					i.source,
					COUNT(i.instanceid) AS total
				FROM
					vtiger_instances i
				WHERE
					i.registrationdate >= ? AND
					i.registrationdate <= ?
				GROUP BY
					i.source',
				array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$subscriptionSource                    = $row ['source'];
					$subscriptions [ $subscriptionSource ] = intval ($row ['total']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $subscriptions;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchTotalTimePerCustomer (PearDatabase $adb, $startDate, $endDate) {
			$instanceCodes = self::fetchInstanceCodes ($adb);
			if (empty ($instanceCodes)) {
				return null;
			}

			$totalTime = array ();
			foreach ($instanceCodes as $instanceCode) {
				$iDb    = "pg_crm_{$instanceCode}";
				$result = $adb->pquery (
					"SELECT
						{$iDb}.vtiger_audit_trial.sessionid,
						MIN({$iDb}.vtiger_audit_trial.actiondate) AS starttime,
						MAX({$iDb}.vtiger_audit_trial.actiondate) AS endtime
					FROM
						{$iDb}.vtiger_audit_trial
					WHERE
						{$iDb}.vtiger_audit_trial.actiondate >= ? AND
						{$iDb}.vtiger_audit_trial.actiondate <= ?
					GROUP BY
						{$iDb}.vtiger_audit_trial.sessionid",
					array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
				);
				if ($adb->num_rows ($result) > 0) {
					$sessionTime = 0;
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						$sessionTime += (strtotime ($row ['endtime']) - strtotime ($row ['starttime']));
					}
					$totalTime [ $instanceCode ] = ($sessionTime / 60);
				} else {
					$totalTime [ $instanceCode ] = 0;
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
				arsort ($totalTime);
				$totalTime = array_slice ($totalTime, 0, 10);
			}
			return $totalTime;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchTotalVisitsPerApplication (PearDatabase $adb, $startDate, $endDate) {
			$instanceCodes = self::fetchInstanceCodes ($adb);
			if (empty ($instanceCodes)) {
				return null;
			}

			$records = array ();
			foreach ($instanceCodes as $instanceCode) {
				$iDb    = "pg_crm_{$instanceCode}";
				$result = $adb->pquery (
					"SELECT
						{$iDb}.vtiger_config_applications.app_name,
						COUNT({$iDb}.vtiger_audit_trial.auditid) AS total
					FROM
						{$iDb}.vtiger_audit_trial
						INNER JOIN {$iDb}.vtiger_tab ON {$iDb}.vtiger_tab.name={$iDb}.vtiger_audit_trial.module
						INNER JOIN {$iDb}.vtiger_configapps_tab ON {$iDb}.vtiger_configapps_tab.tabid={$iDb}.vtiger_tab.tabid
						INNER JOIN {$iDb}.vtiger_config_applications ON {$iDb}.vtiger_config_applications.config_applicationsid={$iDb}.vtiger_configapps_tab.config_applicationsid
					WHERE
						{$iDb}.vtiger_audit_trial.module NOT LIKE '%Attachment' AND
						{$iDb}.vtiger_audit_trial.module NOT IN (SELECT name FROM {$iDb}.vtiger_settings_field WHERE tab IS NULL) AND
						{$iDb}.vtiger_audit_trial.actiondate >= ? AND
						{$iDb}.vtiger_audit_trial.actiondate <= ?
					GROUP BY
						{$iDb}.vtiger_config_applications.app_name
					ORDER BY
						{$iDb}.vtiger_config_applications.app_name",
					array ($startDate->format ('Y-m-d'), $endDate->format ('Y-m-d'))
				);
				if ($adb->num_rows ($result) > 0) {
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						$applicationName = $row ['app_name'];
						$records [ $applicationName ] += intval ($row ['total']);
					}
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return count ($records) > 0 ? $records : null;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return string[]
		 * @throws Exception
		 */
		public static function fetchExpiredInstancesTotalRecords (PearDatabase $adb) {
			$instanceCodes = self::fetchExpiredInstanceCodes ($adb);
			if (empty ($instanceCodes)) {
				return null;
			}

			$totalRecords = array (
				'Instancias caducadas' => count ($instanceCodes),
				'Sin registros'        => 0,
				'1 registro'           => 0,
				'2 registros'          => 0,
				'3 registros'          => 0,
				'4 registros'          => 0,
				'5 registros'          => 0,
				'6 registros'          => 0,
				'7 registros'          => 0,
				'8 registros'          => 0,
				'9 registros'          => 0,
				'10+ registros'        => 0,
			);
			foreach ($instanceCodes as $instanceCode) {
				$iDb    = "pg_crm_{$instanceCode}";
				$result = $adb->query (
					"SELECT
						COUNT({$iDb}.vtiger_crmentity.crmid) AS total
					FROM
						{$iDb}.vtiger_crmentity
					WHERE
						{$iDb}.vtiger_crmentity.setype NOT LIKE '%Attachment' AND
						{$iDb}.vtiger_crmentity.setype NOT IN (SELECT name FROM {$iDb}.vtiger_settings_field WHERE tab IS NULL) AND
						{$iDb}.vtiger_crmentity.deleted=0 AND
						{$iDb}.vtiger_crmentity.demo=0"
				);
				if ($adb->num_rows ($result) > 0) {
					$row     = $adb->fetchByAssoc ($result, -1, false);
					$records = intval ($row ['total']);
					switch ($records) {
						case 0:
							$totalRecords ['Sin registros'] += 1;
							break;
						case 1:
							$totalRecords ['1 registro'] += 1;
							break;
						case 2:
						case 3:
						case 4:
						case 5:
						case 6:
						case 7:
						case 8:
						case 9:
							$totalRecords ["{$records} registros"] += 1;
							break;
						default:
							$totalRecords ['10+ registros'] += 1;
							break;
					}
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return $totalRecords;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return string[]
		 * @throws Exception
		 */
		public static function fetchExpiredInstancesTotalSessions (PearDatabase $adb) {
			$instanceCodes = self::fetchExpiredInstanceCodes ($adb);
			if (empty ($instanceCodes)) {
				return null;
			}

			$totalSessions = array (
				'Instancias caducadas' => count ($instanceCodes),
				'Sin sesiones'         => 0,
				'1 sesión'             => 0,
				'2 sesiones'           => 0,
				'3 sesiones'           => 0,
				'4 sesiones'           => 0,
				'5 sesiones'           => 0,
				'6 sesiones'           => 0,
				'7 sesiones'           => 0,
				'8 sesiones'           => 0,
				'9 sesiones'           => 0,
				'10+ sesiones'         => 0,
			);
			foreach ($instanceCodes as $instanceCode) {
				$iDb    = "pg_crm_{$instanceCode}";
				$result = $adb->query (
					"SELECT
						COUNT(DISTINCT {$iDb}.vtiger_audit_trial.sessionid) AS total
					FROM
						{$iDb}.vtiger_audit_trial"
				);
				if ($adb->num_rows ($result) > 0) {
					$row      = $adb->fetchByAssoc ($result, -1, false);
					$sessions = intval ($row ['total']);
					switch ($sessions) {
						case 0:
							$totalSessions ['Sin sesiones'] += 1;
							break;
						case 1:
							$totalSessions ['1 sesión'] += 1;
							break;
						case 2:
						case 3:
						case 4:
						case 5:
						case 6:
						case 7:
						case 8:
						case 9:
							$totalSessions ["{$sessions} sesiones"] += 1;
							break;
						default:
							$totalSessions ['10+ sesiones'] += 1;
							break;
					}
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return $totalSessions;
		}

		/**
		 * @param PearDatabase $adb
		 * @param DateTime $startDate
		 * @param DateTime $endDate
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchOfferData (PearDatabase $adb, $startDate, $endDate) {
			$offerData     = array ();
			$instanceCodes = self::fetchActiveInstanceCodes ($adb, $startDate, $endDate);
			var_dump ($instanceCodes);
			$oneDayInterval = new DateInterval ('P1D');
			$dummy          = date_create ($startDate->format ('Y-m-d'));
			while ($dummy < $endDate) {
				$date                                    = $dummy->format ('Y-m-d');
				$offerData [ $date ]['Altas o regresos'] = 0;
				$offerData [ $date ]['Probaron un poco'] = 0;
				$offerData [ $date ]['Buenas']           = 0;
				$offerData [ $date ]['Calientes']        = 0;
				foreach ($instanceCodes as $registrationDate => $instanceCode) {
					if ($registrationDate == $date) {
						$offerData [ $date ]['Altas o regresos'] += 1;
					} else {
						$sessionDates = self::fetchSessionDates ($adb, $instanceCode);
						if (in_array ($date, $sessionDates)) {
							$offerData [ $date ]['Altas o regresos'] += 1;
						} else if ((count ($sessionDates) == 1) && ($dummy->diff (date_create ($registrationDate))->format ('%a') == 3)) {
							$offerData [ $date ]['Sin uso'] += 1;
						} else if ((count ($sessionDates) > 1) && ($dummy->diff (date_create ($registrationDate))->format ('%a') <= 3)) {
							$offerData [ $date ]['Probaron un poco'] += 1;
						} else if ((count ($sessionDates) > 3) && ($dummy->diff (date_create ($registrationDate))->format ('%a') > 3) && ($dummy->diff (date_create ($registrationDate))->format ('%a') <= 10)) {
							$offerData [ $date ]['Buenas'] += 1;
						} else if ((count ($sessionDates) > 3) && (count ($sessionDates) < 10) && ($dummy->diff (date_create ($registrationDate))->format ('%a') > 10)) {
							$offerData [ $date ]['Calientes'] += 1;
						}
					}
				}
				$dummy->add ($oneDayInterval);
			}
			ksort ($offerData);
			return $offerData;
		}

	}
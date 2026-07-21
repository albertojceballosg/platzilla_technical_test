<?php
	require_once ('include/platzilla/Objects/PlatformFreeBillingPlanLimit.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');

	class PlatformFreeBillingPlanLimitManager {
		/** @var PlatformFreeBillingPlanLimitManager */
		private static $INSTANCE = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param PlatformFreeBillingPlanLimit $limit
		 *
		 * @throws PlatformFreeBillingPlanLimitException
		 */
		private function validate ($limit) {
			if ((empty ($limit)) || (!($limit instanceof PlatformFreeBillingPlanLimit))) {
				throw new PlatformFreeBillingPlanLimitException (PlatformFreeBillingPlanLimitException::ERROR_FREE_BILLING_PLAN_LIMIT_INVALID);
			} else {
				$limit->validate ();
			}

			$result = null;
			try {
				$moduleName = $limit->getModuleName ();
				$result     = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($moduleName));
				if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
					throw new PlatformFreeBillingPlanLimitException (PlatformFreeBillingPlanLimitException::ERROR_FREE_BILLING_PLAN_LIMIT_INVALID_MODULE_NAME);
				}
			} catch (PlatformFreeBillingPlanLimitException $ie) {
				$e = $ie;
			} finally {
				DatabaseUtils::closeResult ($result);
				$result = null;
				if (isset ($e)) {
					throw $e;
				}
			}
		}

		/**
		 * @return integer
		 */
		public function fetchDefaultMaxRecordsLimit () {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_variables_instancias WHERE varname=?', array ('freeplanmaxrecords'));
			if ($this->adb->num_rows ($result) == 0) {
				$this->saveDefaultMaxRecordsLimit (5);
				$maxRecords = 5;
			} else {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$maxRecords = intval ($row ['varvalue']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $maxRecords;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return PlatformFreeBillingPlanLimit|null
		 */
		public function fetchLimit ($moduleName) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					ml.*,
					t.tablabel
				FROM
					vtiger_instancefreeplanlimits ml
					INNER JOIN vtiger_tab t ON t.name=ml.modulename
				WHERE
					ml.modulename=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$limit = PlatformFreeBillingPlanLimit::getInstance ()
					->setMaxRecords (intval ($row ['maxrecords']))
					->setModuleLabel (getTranslatedString ($row ['tablabel'], $row ['modulename']))
					->setModuleLabel ($row ['modulename']);
			} else {
				$limit = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $limit;
		}

		/**
		 * @return PlatformFreeBillingPlanLimit[]|null
		 */
		public function fetchLimits () {
			$result = $this->adb->query (
				'SELECT
					ml.*,
					t.tablabel
				FROM
					vtiger_instancefreeplanlimits ml
					INNER JOIN vtiger_tab t ON t.name=ml.modulename'
			);
			if ($this->adb->num_rows ($result) > 0) {
				$limits = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$limits [] = PlatformFreeBillingPlanLimit::getInstance ()
						->setMaxRecords (intval ($row ['maxrecords']))
						->setModuleLabel (getTranslatedString ($row ['tablabel'], $row ['modulename']))
						->setModuleName ($row ['modulename']);
				}
				usort (
					$limits,
					function (PlatformFreeBillingPlanLimit $limitA, PlatformFreeBillingPlanLimit $limitB) {
						return strcmp ($limitA->getModuleLabel (), $limitB->getModuleLabel ());
					}
				);
			} else {
				$limits = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $limits;
		}

		/**
		 * @param integer $maxRecords
		 *
		 * @throws PlatformFreeBillingPlanLimitException
		 */
		public function saveDefaultMaxRecordsLimit ($maxRecords) {
			if (($maxRecords === null) || (!is_numeric ($maxRecords))) {
				throw new PlatformFreeBillingPlanLimitException (PlatformFreeBillingPlanLimitException::ERROR_FREE_BILLING_PLAN_LIMIT_EMPTY_MAX_RECORDS);
			} else if ($maxRecords < -1) {
				throw new PlatformFreeBillingPlanLimitException (PlatformFreeBillingPlanLimitException::ERROR_FREE_BILLING_PLAN_LIMIT_INVALID);
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_variables_instancias WHERE varname=?', array ('freeplanmaxrecords'));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery ('INSERT INTO vtiger_variables_instancias (varname, varvalue) VALUES (?, ?)', array ('freeplanmaxrecords', $maxRecords));
			} else {
				$this->adb->pquery ('UPDATE vtiger_variables_instancias SET varvalue=? WHERE varname=?', array ($maxRecords, 'freeplanmaxrecords'));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param PlatformFreeBillingPlanLimit $limit
		 *
		 * @return PlatformFreeBillingPlanLimit
		 * @throws PlatformFreeBillingPlanLimitException
		 */
		public function saveLimit ($limit) {
			$this->validate ($limit);

			$moduleName = $limit->getModuleName ();
			$result     = $this->adb->pquery ('SELECT * FROM vtiger_instancefreeplanlimits WHERE modulename=?', array ($moduleName));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery ('INSERT IGNORE INTO vtiger_instancefreeplanlimits (modulename, maxrecords) VALUES (?, ?)', array ($moduleName, $limit->getMaxRecords ()));
			} else {
				$this->adb->pquery ('UPDATE vtiger_instancefreeplanlimits SET maxrecords=? WHERE modulename=?', array ($limit->getMaxRecords (), $moduleName));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
			return $limit;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return PlatformFreeBillingPlanLimitManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCE === null) {
				self::$INSTANCE = new self ($adb);
			}
			return self::$INSTANCE;
		}

	}

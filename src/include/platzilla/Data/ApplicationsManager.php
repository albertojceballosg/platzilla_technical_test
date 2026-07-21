<?php
	require_once ('include/platzilla/Data/ApplicationObject.php');
	require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ApplicationsManager {

		/** @var ApplicationsManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		private function fetchApplicationModules ($applicationId) {
			$result = $this->adb->pquery (
				'SELECT t.*  FROM vtiger_configapps_tab cat INNER JOIN vtiger_tab t ON t.tabid=cat.tabid  WHERE config_applicationsid=?',
				array ($applicationId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$modules = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$modules [] = $row ['name'];
				}
			} else {
				$modules = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $modules;
		}

		public function getAvailableApplications ($current_user) {
			// Validate that $current_user is a valid object before cloning
			if (!is_object($current_user)) {
				throw new Exception('ApplicationsManager::getAvailableApplications() requires a valid user object');
			}
			$local_user = clone $current_user;
			require ('user_privileges/user_privileges.php');
			$profileIds = null;
			if (!empty($current_user_profiles)) {
				$profileIds = implode(',', $current_user_profiles);
			}
			if (!empty($profileIds) && $profileIds != '') {
				$profileIds = " where profileid in ({$profileIds}) ";
			} else {
				$profileIds = '';
			}

			$applications     = array ();
			$appCodesProfile  = array ();

			$resultProfile = $this->adb->pquery ("select REPLACE(REPLACE(applicationcodes, '\"]', '\''), '[\"', '\'') applicationcodes from vtiger_profile {$profileIds}", array ());

			if (($resultProfile) && ($this->adb->num_rows ($resultProfile) > 0) && $current_user->is_admin == 'off') {
				while ($row = $this->adb->fetchByAssoc ($resultProfile, -1, false)) {
					$appCodesProfile[] = $row ['applicationcodes'];
				}
				$appCodes = implode (',', $appCodesProfile);
				if (!empty($appCodes) && $appCodes != '') {
					$appCodesProfileIn = " AND app_code IN ({$appCodes})";
				} else {
					$appCodesProfileIn = '';
				}
			} else {
				$appCodesProfileIn = '';
			}

			$result = $this->adb->query ("SELECT config_applicationsid, app_code, app_name FROM vtiger_config_applications WHERE app_status='Activa' {$appCodesProfileIn}");

			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$applications [] = ApplicationObject::getInstance ()
						->setCategoryId (intval ($row ['app_category']))
						->setCode ($row ['app_code'])
						->setDescription ($row ['app_descripcion'])
						->setId (intval ($row ['config_applicationsid']))
						->setModules ($this->fetchApplicationModules($row ['config_applicationsid']))
						->setName ($row ['app_name'])
						->setPrice (doubleval ($row ['app_price']))
						->setProfile ($row ['app_profile'])
						->setStatus ($row ['app_status'])
						->setUrl ($row ['app_url']);
				}
			} else {
				$applications     = null;
				$applicationCodes = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $applications;
		}

		/**
		 * @param string $platForm
		 *
		 * @return array
		 * @throws Exception
		 */
		public function getApplicationCatalog ($platForm) {
			if (!empty ($platForm)) {
				$masterAdb            = AdbManager::getInstance ()->getMasterAdb ();
				$instanceDatabaseName = "pg_crm_{$platForm}";
				$result               = $masterAdb->pquery (
					"SELECT
							ica.config_applicationsid,
							ica.app_code,
							ica.app_name
						  FROM
							vtiger_instanceapplications ia
							INNER JOIN vtiger_instances i ON i.code=ia.instancecode
							INNER JOIN vtiger_config_applications mca ON mca.app_code=ia.applicationcode
							INNER JOIN {$instanceDatabaseName}.vtiger_config_applications ica ON ica.app_code=mca.app_code AND ica.app_status='Activa'
						  WHERE
							ia.status IN (?, ?) AND
							i.code=?",
					array (ApplicationSubscriptionInterface::STATUS_ACTIVE, ApplicationSubscriptionInterface::STATUS_SUBSCRIBED, $platForm)
				);
			} else {
				$result = $this->adb->query ("SELECT config_applicationsid, app_code, app_name FROM vtiger_config_applications WHERE app_status='Activa'");
			}
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$applications     = array ();
				$applicationCodes = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$applicationCode                   = $row ['app_code'];
					$applications [ $applicationCode ] = $row;
					$applicationCodes []               = $applicationCode;
				}
			} else {
				$applications     = null;
				$applicationCodes = null;
			}

			DatabaseUtils::closeResult ($result);
			$result = null;
			return array ('applications' => $applications, 'applicationCodes' => $applicationCodes);
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ApplicationsManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}

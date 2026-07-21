<?php
	require_once ('include/platzilla/Managers/FieldProfileManager.php');
	require_once ('include/platzilla/Managers/ModuleProfileManager.php');
	require_once ('include/platzilla/Managers/ViewProfileManager.php');
	require_once ('include/platzilla/Objects/Profile.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ProfileManager {
		/** @var ProfileManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param integer $profileId
		 * @param integer $action
		 *
		 * @return integer
		 */
		private function fetchPermission ($profileId, $action) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_profile2globalpermissions WHERE profileid=? AND globalactionid=?', array ($profileId, $action));
			if ($this->adb->num_rows ($result) > 0) {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$permission = intval ($row ['globalactionpermission']);
			} else {
				$permission = ProfileInterface::PERMISSION_ALLOW;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $permission;
		}

		/**
		 * @param Profile $profile
		 */
		private function saveFieldProfiles ($profile) {
			$fieldProfiles = $profile->getFieldProfiles ();
			if (empty ($fieldProfiles)) {
				return;
			}

			$fpm = FieldProfileManager::getInstance ($this->adb);
			foreach ($fieldProfiles as $fieldProfile) {
				$fpm->saveProfile ($fieldProfile);
			}
		}

		/**
		 * @param Profile $profile
		 */
		private function saveModuleProfiles ($profile) {
			$moduleProfiles = $profile->getModuleProfiles ();
			if (empty ($moduleProfiles)) {
				return;
			}

			$mpm = ModuleProfileManager::getInstance ($this->adb);
			foreach ($moduleProfiles as $moduleProfile) {
				$mpm->saveProfile ($moduleProfile);
			}
		}

		/**
		 * @param Profile $profile
		 */
		private function saveViewProfiles ($profile) {
			$viewProfiles = $profile->getViewProfiles ();
			if (empty ($viewProfiles)) {
				return;
			}

			$vpm = ViewProfileManager::getInstance ($this->adb);
			foreach ($viewProfiles as $viewProfile) {
				$vpm->saveProfile ($viewProfile);
			}
		}

		/**
		 * @param Profile $profile
		 *
		 * @throws ProfileException
		 */
		private function validate ($profile) {
			if ((empty ($profile)) || (!($profile instanceof Profile))) {
				throw new ProfileException (ProfileException::ERROR_PROFILE_EMPTY);
			}

			$profile->validate ();
		}

		/**
		 * @param string $profileName
		 * @param string $profileDescription
		 * @param string $applicationCode
		 *
		 * @return Profile
		 */
		public function createDefaultProfile ($profileName, $profileDescription, $applicationCode = null) {
			$profile = Profile::getInstance ()
				->setDescription ($profileDescription)
				->setMainApplicationCode ($applicationCode)
				->setName ($profileName);

			$this->adb->startTransaction ();
			$this->saveProfile ($profile);
			ModuleProfileManager::getInstance ($this->adb)->createDefaultProfilesByProfileName ($profileName);
			FieldProfileManager::getInstance ($this->adb)->createDefaultProfilesByProfileName ($profileName);
			ViewProfileManager::getInstance ($this->adb)->createDefaultProfilesByProfileName ($profileName);
			$this->adb->completeTransaction ();
			return $profile;
		}

		/**
		 * @param Profile $profile
		 */
		public function deleteProfile ($profile) {
			if ((empty ($profile)) || (!($profile instanceof Profile))) {
				return;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_profile WHERE profilename=?', array ($profile->getName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row       = $this->adb->fetchByAssoc ($result, -1, false);
				$profileId = intval ($row ['profileid']);
				$this->adb->startTransaction ();
				ViewProfileManager::getInstance ($this->adb)->deleteProfilesByProfileName ($profile->getName ());
				FieldProfileManager::getInstance ($this->adb)->deleteProfilesByProfileName ($profile->getName ());
				ModuleProfileManager::getInstance ($this->adb)->deleteProfilesByProfileName ($profile->getName ());
				$this->adb->pquery ('DELETE FROM vtiger_profile2globalpermissions WHERE profileid=?', array ($profileId));
				$this->adb->pquery ('DELETE FROM vtiger_profile WHERE profileid=?', array ($profileId));
				$this->adb->completeTransaction ();
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param Profile $profile
		 * @param string $moduleName
		 */
		public function deleteProfilesByModuleName ($profile, $moduleName) {
			if ((empty ($moduleName)) || (empty ($profile)) || (!($profile instanceof Profile))) {
				return;
			}

			$this->adb->startTransaction ();
			ViewProfileManager::getInstance ($this->adb)->deleteProfilesByProfileAndModuleName ($profile->getName (), $moduleName);
			FieldProfileManager::getInstance ($this->adb)->deleteProfilesByProfileAndModuleName ($profile->getName (), $moduleName);
			ModuleProfileManager::getInstance ($this->adb)->deleteProfilesByProfileAndModuleName ($profile->getName (), $moduleName);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $profileName
		 * @param boolean $headersOnly
		 * @param string[] $excludedModuleNames
		 *
		 * @return Profile|null
		 */
		public function fetchProfile ($profileName, $headersOnly = false, $excludedModuleNames = null) {
			if (empty ($profileName)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_profile WHERE profilename=?', array ($profileName));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				return null;
			}

			$row       = $this->adb->fetchByAssoc ($result, -1, false);
			$profileId = intval ($row ['profileid']);
			$profile   = Profile::getInstance ()
				->setId ($profileId)
				->setDescription ($row ['description'])
				->setEditPermission ($this->fetchPermission ($profileId, ProfileInterface::ACTION_EDIT_ALL))
				->setName ($row ['profilename'])
				->setSecondaryApplicationCodes (!empty ($row ['applicationcodes']) ? json_decode ($row ['applicationcodes']) : null)
				->setViewPermission ($this->fetchPermission ($profileId, ProfileInterface::ACTION_VIEW_ALL));
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery (
				'SELECT
					ca.*
				FROM
					vtiger_config_applications ca
					INNER JOIN vtiger_profile p ON p.profileid=ca.app_profile AND p.profilename=?',
				array ($profileName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$profile->setMainApplicationCode ($row ['app_code']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (!$headersOnly) {
				$profile->setFieldProfiles (FieldProfileManager::getInstance ($this->adb)->fetchProfilesByProfileName ($profileName, $excludedModuleNames))
					->setModuleProfiles (ModuleProfileManager::getInstance ($this->adb)->fetchProfilesByProfileName ($profileName, $excludedModuleNames))
					->setViewProfiles (ViewProfileManager::getInstance ($this->adb)->fetchProfilesByProfileName ($profileName, $excludedModuleNames));
			}
			return $profile;
		}

		/**
		 * @param integer $profileId
		 * @param boolean $headersOnly
		 * @param string[] $excludedModuleNames
		 *
		 * @return Profile|null
		 */
		public function fetchProfileById ($profileId, $headersOnly = false, $excludedModuleNames = null) {
			if (empty ($profileId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_profile WHERE profileid=?', array ($profileId));
			if ($this->adb->num_rows ($result) == 0) {
				DatabaseUtils::closeResult ($result);
				$result = null;
				return null;
			}

			$row         = $this->adb->fetchByAssoc ($result, -1, false);
			$profileId   = intval ($row ['profileid']);
			$profileName = $row ['profilename'];
			$profile     = Profile::getInstance ()
				->setId ($profileId)
				->setDescription ($row ['description'])
				->setEditPermission ($this->fetchPermission ($profileId, ProfileInterface::ACTION_EDIT_ALL))
				->setName ($row ['profilename'])
				->setSecondaryApplicationCodes (!empty ($row ['applicationcodes']) ? json_decode ($row ['applicationcodes']) : null)
				->setViewPermission ($this->fetchPermission ($profileId, ProfileInterface::ACTION_VIEW_ALL));
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery (
				'SELECT
					ca.*
				FROM
					vtiger_config_applications ca
				WHERE
					app_profile=?',
				array ($profileId)
			);
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				$profile->setMainApplicationCode ($row ['app_code']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (!$headersOnly) {
				$profile->setFieldProfiles (FieldProfileManager::getInstance ($this->adb)->fetchProfilesByProfileName ($profileName, $excludedModuleNames))
					->setModuleProfiles (ModuleProfileManager::getInstance ($this->adb)->fetchProfilesByProfileName ($profileName, $excludedModuleNames))
					->setViewProfiles (ViewProfileManager::getInstance ($this->adb)->fetchProfilesByProfileName ($profileName, $excludedModuleNames));
			}
			return $profile;
		}

		/**
		 * @param boolean $headersOnly
		 * @param string[] $excludedModuleNames
		 *
		 * @return Profile[]|null
		 */
		public function fetchProfiles ($headersOnly = false, $excludedModuleNames = null) {
			$result = $this->adb->query ('SELECT * FROM vtiger_profile ORDER BY profileid');
			if ($this->adb->num_rows ($result) > 0) {
				$profiles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$profiles [] = $this->fetchProfile ($row ['profilename'], $headersOnly, $excludedModuleNames);
				}
			} else {
				$profiles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profiles;
		}

		/**
		 * @param string $applicationCode
		 * @param boolean $headersOnly
		 * @param string[] $excludedModuleNames
		 *
		 * @return Profile[]|null
		 */
		public function fetchSecondaryProfiles ($applicationCode, $headersOnly = false, $excludedModuleNames = null) {
			$result = $this->adb->query ('SELECT * FROM vtiger_profile ORDER BY profileid');
			if ($this->adb->num_rows ($result) > 0) {
				$profiles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$applicationCodes = !empty ($row ['applicationcodes']) ? json_decode ($row ['applicationcodes']) : null;
					if ((!empty ($applicationCodes)) && (in_array ($applicationCode, $applicationCodes))) {
						$profiles [] = $this->fetchProfile ($row ['profilename'], $headersOnly, $excludedModuleNames);
					}
				}
			} else {
				$profiles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return !empty ($profiles) ? $profiles : null;
		}

		/**
		 * @param Profile $profile
		 *
		 * @return Profile|null
		 */
		public function saveProfile ($profile) {
			$this->validate ($profile);

			$applicationCodes = $profile->getSecondaryApplicationCodes ();
			$this->adb->startTransaction ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_profile WHERE profilename=?', array ($profile->getName ()));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_profile (profilename, description, applicationcodes) VALUES (?, ?, ?)',
					array ($profile->getName (), $profile->getDescription (), !empty ($applicationCodes) ? json_encode ($applicationCodes) : null)
				);
				$profileId = intval ($this->adb->getLastInsertID ());
				$this->adb->pquery (
					'INSERT INTO vtiger_profile2globalpermissions (profileid, globalactionid, globalactionpermission) VALUES (?, ?, ?)',
					array ($profileId, ProfileInterface::ACTION_EDIT_ALL, $profile->getEditPermission ())
				);
				$this->adb->pquery (
					'INSERT INTO vtiger_profile2globalpermissions (profileid, globalactionid, globalactionpermission) VALUES (?, ?, ?)',
					array ($profileId, ProfileInterface::ACTION_VIEW_ALL, $profile->getViewPermission ())
				);
				$profile->setId ($profileId);
			} else {
				$row       = $this->adb->fetchByAssoc ($result, -1, false);
				$profileId = intval ($row ['profileid']);
				$this->adb->pquery (
					'UPDATE vtiger_profile SET description=?, applicationcodes=? WHERE profilename=?',
					array ($profile->getDescription (), !empty ($applicationCodes) ? json_encode ($applicationCodes) : null, $profile->getName ())
				);
				$this->adb->pquery (
					'UPDATE vtiger_profile2globalpermissions SET globalactionpermission=? WHERE profileid=? AND globalactionid=?',
					array ($profile->getEditPermission (), $profileId, ProfileInterface::ACTION_EDIT_ALL)
				);
				$this->adb->pquery (
					'UPDATE vtiger_profile2globalpermissions SET globalactionpermission=? WHERE profileid=? AND globalactionid=?',
					array ($profile->getViewPermission (), $profileId, ProfileInterface::ACTION_VIEW_ALL)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$this->saveModuleProfiles ($profile);
			$this->saveFieldProfiles ($profile);
			$this->saveViewProfiles ($profile);
			$this->adb->completeTransaction ();
			return $profile;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ProfileManager
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

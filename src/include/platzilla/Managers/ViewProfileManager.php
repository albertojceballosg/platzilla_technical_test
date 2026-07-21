<?php
	require_once ('include/platzilla/Objects/ViewProfile.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ViewProfileManager {
		/** @var ViewProfileManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param ViewProfile $profile
		 *
		 * @throws ViewProfileException
		 */
		private function validate ($profile) {
			if ((empty ($profile)) || (!($profile instanceof ViewProfile))) {
				throw new ViewProfileException (ViewProfileException::ERROR_VIEW_PROFILE_EMPTY_PROFILE);
			}

			$profile->validate ();
		}

		/**
		 * @param string $moduleName
		 * @param string $viewName
		 */
		public function createDefaultProfiles ($moduleName, $viewName) {
			if ((empty ($moduleName)) || (empty ($viewName))) {
				return;
			}
			$this->adb->pquery (
				'INSERT INTO vtiger_profile2customview (profileid, cvid, tabid, permissions, setdefault)
				SELECT
					p.profileid,
					v.cvid,
					t.tabid,
					?,
					?
				FROM
					vtiger_customview v
					INNER JOIN vtiger_tab t ON t.name=v.entitytype AND t.name=?
					CROSS JOIN vtiger_profile p
				WHERE
					v.viewname=?',
				array (ViewProfileInterface::PERMISSION_ALLOW, ViewProfileInterface::DEFAULT_NO, $moduleName, $viewName)
			);
		}

		/**
		 * @param string $profileName
		 */
		public function createDefaultProfilesByProfileName ($profileName) {
			if (empty ($profileName)) {
				return;
			}
			$this->adb->pquery (
				'INSERT INTO vtiger_profile2customview (profileid, cvid, tabid, permissions, setdefault)
				SELECT
					p.profileid,
					v.cvid,
					t.tabid,
					?,
					?
				FROM
					vtiger_profile p
					CROSS JOIN vtiger_customview v
					INNER JOIN vtiger_tab t ON t.name=v.entitytype
				WHERE
					p.profilename=?',
				array (ViewProfileInterface::PERMISSION_ALLOW, ViewProfileInterface::DEFAULT_NO, $profileName)
			);
		}

		/**
		 * @param ViewProfile $profile
		 */
		public function deleteProfile ($profile) {
			if ((empty ($profile)) || (!($profile instanceof ViewProfile))) {
				return;
			}

			$this->adb->pquery (
				'DELETE
					p2v
				FROM
					vtiger_profile2customview p2v
					INNER JOIN vtiger_profile p ON p.profileid=p2v.profileid AND p.profilename=?
					INNER JOIN vtiger_customview v ON v.cvid=p2v.cvid AND v.viewname=? AND v.entitytype=?',
				array ($profile->getProfileName (), $profile->getViewName (), $profile->getModuleName ())
			);
		}

		/**
		 * @param string $moduleName
		 * @param string $viewName
		 */
		public function deleteProfiles ($moduleName, $viewName) {
			if ((empty ($moduleName)) || (empty ($viewName))) {
				return;
			}

			$this->adb->pquery (
				'DELETE
					p2cv
				FROM
					vtiger_profile2customview p2cv
					INNER JOIN vtiger_customview cv ON cv.cvid=p2cv.cvid AND cv.entitytype=? AND cv.viewname=?',
				array ($moduleName, $viewName)
			);
		}

		/**
		 * @param string $profileName
		 * @param string $moduleName
		 */
		public function deleteProfilesByProfileAndModuleName ($profileName, $moduleName) {
			if ((empty ($profileName)) || (empty ($moduleName))) {
				return;
			}

			$this->adb->pquery (
				'DELETE
					p2v
				FROM
					vtiger_profile2customview p2v
					INNER JOIN vtiger_profile p ON p.profileid=p2v.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2v.tabid AND t.name=?',
				array ($profileName, $moduleName)
			);
		}

		/**
		 * @param string $profileName
		 */
		public function deleteProfilesByProfileName ($profileName) {
			if (empty ($profileName)) {
				return;
			}

			$this->adb->pquery (
				'DELETE
					p2v
				FROM
					vtiger_profile2customview p2v
					INNER JOIN vtiger_profile p ON p.profileid=p2v.profileid AND p.profilename=?',
				array ($profileName)
			);
		}

		/**
		 * @param string $profileName
		 * @param string $moduleName
		 * @param string $viewName
		 *
		 * @return ViewProfile|null
		 */
		public function fetchProfileByProfileName ($profileName, $moduleName, $viewName) {
			if ((empty ($profileName)) || (empty ($moduleName)) || (empty ($viewName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					p2v.*
				FROM
					vtiger_profile2customview p2v
					INNER JOIN vtiger_profile p ON p.profileid=p2v.profileid AND p.profilename=?
					INNER JOIN vtiger_customview v ON v.cvid=p2v.cvid AND v.viewname=? AND v.entitytype=?',
				array ($profileName, $viewName, $moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$profile = ViewProfile::getInstance ()
					->setAccessPermission (intval ($row ['permissions']))
					->setDefault (intval ($row ['setdefault']))
					->setModuleName ($moduleName)
					->setProfileName ($profileName)
					->setViewName ($viewName);
			} else {
				$profile = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profile;
		}

		/**
		 * @param string $moduleName
		 * @param string $viewName
		 *
		 * @return ViewProfile[]|null
		 */
		public function fetchProfiles ($moduleName, $viewName) {
			if ((empty ($moduleName)) || (empty ($viewName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					p2v.*,
					p.profilename
				FROM
					vtiger_profile2customview p2v
					INNER JOIN vtiger_profile p ON p.profileid=p2v.profileid
					INNER JOIN vtiger_customview v ON v.cvid=p2v.cvid AND v.viewname=? AND v.entitytype=?',
				array ($viewName, $moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$profiles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$profiles [] = ViewProfile::getInstance ()
						->setAccessPermission (intval ($row ['permissions']))
						->setDefault (intval ($row ['setdefault']))
						->setModuleName ($moduleName)
						->setProfileName ($row ['profilename'])
						->setViewName ($viewName);
				}
			} else {
				$profiles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profiles;
		}

		/**
		 * @param string $profileName
		 * @param string[]|null $excludedModuleNames
		 *
		 * @return ViewProfile[]|null
		 */
		public function fetchProfilesByProfileName ($profileName, $excludedModuleNames = null) {
			if (empty ($profileName)) {
				return null;
			}

			if (!empty ($excludedModuleNames)) {
				$questionMarks = str_repeat ('?, ', (count ($excludedModuleNames) - 1)) . '?';
				$joinClause    = "AND v.entitytype NOT IN ({$questionMarks})";
				$arguments     = $excludedModuleNames;
			} else {
				$joinClause = '';
				$arguments  = array ();
			}

			$result = $this->adb->pquery (
				"SELECT
					p2v.*,
					p.profilename,
					v.viewname,
					v.entitytype
				FROM
					vtiger_profile2customview p2v
					INNER JOIN vtiger_profile p ON p.profileid=p2v.profileid AND p.profilename=?
					INNER JOIN vtiger_customview v ON v.cvid=p2v.cvid {$joinClause}",
				array_merge (array ($profileName), $arguments)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$profiles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$profiles [] = ViewProfile::getInstance ()
						->setAccessPermission (intval ($row ['permissions']))
						->setDefault (intval ($row ['setdefault']))
						->setModuleName ($row ['entitytype'])
						->setProfileName ($row ['profilename'])
						->setViewName ($row ['viewname']);
				}
			} else {
				$profiles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profiles;
		}

		/**
		 * @param ViewProfile $profile
		 *
		 * @return ViewProfile
		 * @throws ViewProfileException
		 */
		public function saveProfile ($profile) {
			$this->validate ($profile);
			$viewName = $profile->getViewName ();

			$result    = $this->adb->pquery ('SELECT * FROM vtiger_profile WHERE profilename=?', array ($profile->getProfileName ()));
			$row       = $this->adb->fetchByAssoc ($result, -1, false);
			$profileId = intval ($row ['profileid']);
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result   = $this->adb->pquery (
				'SELECT v.*, t.tabid FROM vtiger_customview v INNER JOIN vtiger_tab t ON t.name=v.entitytype WHERE v.viewname=? AND v.entitytype=?',
				array ($viewName, $profile->getModuleName ())
			);
			$row      = $this->adb->fetchByAssoc ($result, -1, false);
			$moduleId = intval ($row ['tabid']);
			$viewId   = intval ($row ['cvid']);
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE profileid=? AND cvid=?', array ($profileId, $viewId));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT IGNORE INTO vtiger_profile2customview (profileid, cvid, tabid, permissions, setdefault) VALUES (?, ?, ?, ?, ?)',
					array ($profileId, $viewId, $moduleId, $profile->getAccessPermission (), $profile->getDefault ())
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_profile2customview SET tabid=?, permissions=?, setdefault=? WHERE profileid=? AND cvid=?',
					array ($moduleId, $profile->getAccessPermission (), $profile->getDefault (), $profileId, $viewId)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profile;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ViewProfileManager
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

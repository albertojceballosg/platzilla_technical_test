<?php
	require_once ('include/platzilla/Objects/ModuleProfile.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ModuleProfileManager {
		/** @var ModuleProfileManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param string $profileName
		 * @param string $moduleName
		 *
		 * @return array
		 */
		private function fetchStandardPermissions ($profileName, $moduleName) {
			$permissions   = array (
				'Delete'     => ModuleProfileInterface::PERMISSION_ALLOW,
				'DetailView' => ModuleProfileInterface::PERMISSION_ALLOW,
				'EditView'   => ModuleProfileInterface::PERMISSION_ALLOW,
				'index'      => ModuleProfileInterface::PERMISSION_ALLOW,
				'Save'       => ModuleProfileInterface::PERMISSION_ALLOW,
			);
			$questionMarks = str_repeat ('?, ', (count ($permissions) - 1)) . '?';
			$result        = $this->adb->pquery (
				"SELECT
					am.actionname,
					p2sp.permissions
				FROM
					vtiger_profile2standardpermissions p2sp
					INNER JOIN vtiger_profile p ON p.profileid=p2sp.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2sp.tabid AND t.name=?
					INNER JOIN vtiger_actionmapping am ON am.actionid=p2sp.operation AND am.actionname IN ({$questionMarks})",
				array_merge (array ($profileName, $moduleName), array_keys ($permissions))
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$permissions [ $row ['actionname'] ] = intval ($row ['permissions']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $permissions;
		}

		/**
		 * @param string $profileName
		 * @param string $moduleName
		 *
		 * @return array
		 */
		private function fetchUtilityPermissions ($profileName, $moduleName) {
			$permissions   = array (
				'DuplicatesHandling' => ModuleProfileInterface::PERMISSION_ALLOW,
				'Export'             => ModuleProfileInterface::PERMISSION_ALLOW,
				'Import'             => ModuleProfileInterface::PERMISSION_ALLOW,
				'Merge'              => ModuleProfileInterface::PERMISSION_DENY,
			);
			$questionMarks = str_repeat ('?, ', (count ($permissions) - 1)) . '?';
			$result        = $this->adb->pquery (
				"SELECT
					am.actionname,
					p2u.permission
				FROM
					vtiger_profile2utility p2u
					INNER JOIN vtiger_profile p ON p.profileid=p2u.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2u.tabid AND t.name=?
					INNER JOIN vtiger_actionmapping am ON am.actionid=p2u.activityid AND am.actionname IN ({$questionMarks})",
				array_merge (array ($profileName, $moduleName), array_keys ($permissions))
			);
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$permissions [ $row ['actionname'] ] = intval ($row ['permission']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $permissions;
		}

		/**
		 * @param integer $profileId
		 * @param integer $moduleId
		 * @param ModuleProfile $profile
		 */
		private function saveStandardPermissions ($profileId, $moduleId, $profile) {
			$operations = array (
				'Delete'     => $profile->getDeletePermission (),
				'DetailView' => $profile->getReadPermission (),
				'EditView'   => $profile->getEditPermission (),
				'index'      => $profile->getListPermission (),
				'Save'       => $profile->getSavePermission (),
			);
			foreach ($operations as $operationName => $permission) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_actionmapping WHERE actionname=?', array ($operationName));
				if ($this->adb->num_rows ($result) == 0) {
					DatabaseUtils::closeResult ($result);
					$result = null;
					continue;
				}

				$row         = $this->adb->fetchByAssoc ($result, -1, false);
				$operationId = intval ($row ['actionid']);
				DatabaseUtils::closeResult ($result);
				$result = null;
				$result = $this->adb->pquery (
					'SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?',
					array ($profileId, $moduleId, $operationId)
				);
				if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
					$this->adb->pquery (
						'INSERT INTO vtiger_profile2standardpermissions (profileid, tabid, operation, permissions) VALUES (?, ?, ?, ?)',
						array ($profileId, $moduleId, $operationId, $permission)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_profile2standardpermissions SET permissions=? WHERE profileid=? AND tabid=? AND operation=?',
						array ($permission, $profileId, $moduleId, $operationId)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}

		/**
		 * @param integer $profileId
		 * @param integer $moduleId
		 * @param ModuleProfile $profile
		 */
		private function saveUtilityPermissions ($profileId, $moduleId, $profile) {
			$operations = array (
				'DuplicatesHandling' => $profile->getHandleDuplicatesPermission (),
				'Export'             => $profile->getExportPermission (),
				'Import'             => $profile->getImportPermission (),
				'Merge'              => $profile->getMergePermission (),
			);
			foreach ($operations as $operationName => $permission) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_actionmapping WHERE actionname=?', array ($operationName));
				if ($this->adb->num_rows ($result) == 0) {
					DatabaseUtils::closeResult ($result);
					$result = null;
					continue;
				}

				$row         = $this->adb->fetchByAssoc ($result, -1, false);
				$operationId = intval ($row ['actionid']);
				DatabaseUtils::closeResult ($result);
				$result = null;
				$result = $this->adb->pquery (
					'SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?',
					array ($profileId, $moduleId, $operationId)
				);
				if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
					$this->adb->pquery (
						'INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES (?, ?, ?, ?)',
						array ($profileId, $moduleId, $operationId, $permission)
					);
				} else {
					$this->adb->pquery (
						'UPDATE vtiger_profile2utility SET permission=? WHERE profileid=? AND tabid=? AND activityid=?',
						array ($permission, $profileId, $moduleId, $operationId)
					);
				}
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
		}

		/**
		 * @param ModuleProfile $profile
		 *
		 * @throws ModuleProfileException
		 */
		private function validate ($profile) {
			if ((empty ($profile)) || (!($profile instanceof ModuleProfile))) {
				throw new ModuleProfileException (ModuleProfileException::ERROR_MODULE_PROFILE_EMPTY);
			}

			$profile->validate ();
		}

		/**
		 * @param string $moduleName
		 */
		public function createDefaultProfiles ($moduleName) {
			if (empty ($moduleName)) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery (
				'INSERT INTO vtiger_profile2tab (profileid, tabid, permissions)
				SELECT
					p.profileid,
					t.tabid,
					?
				FROM
					vtiger_tab t
					CROSS JOIN vtiger_profile p
				WHERE
					t.name=?',
				array (ModuleProfileInterface::PERMISSION_ALLOW, $moduleName)
			);
			$this->adb->pquery (
				"INSERT INTO vtiger_profile2standardpermissions (profileid, tabid, operation, permissions)
				SELECT
					p.profileid,
					t.tabid,
					am.actionid,
					?
				FROM
					vtiger_tab t
					CROSS JOIN vtiger_actionmapping am ON am.actionname IN ('Save', 'EditView', 'Delete', 'index', 'DetailView')
					CROSS JOIN vtiger_profile p
				WHERE
					t.name=?",
				array (ModuleProfileInterface::PERMISSION_ALLOW, $moduleName)
			);
			$this->adb->pquery (
				"INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission)
				SELECT
					p.profileid,
					t.tabid,
					am.actionid,
					IF(am.actionname IN ('Import', 'Export', 'DuplicatesHandling'), ?, ?)
				FROM
					vtiger_tab t
					CROSS JOIN vtiger_actionmapping am ON am.actionname IN ('Import', 'Export', 'Merge', 'DuplicatesHandling')
					CROSS JOIN vtiger_profile p
				WHERE
					t.name=?",
				array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY, $moduleName)
			);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $profileName
		 */
		public function createDefaultProfilesByProfileName ($profileName) {
			if (empty ($profileName)) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery (
				'INSERT INTO vtiger_profile2tab (profileid, tabid, permissions)
				SELECT
					p.profileid,
					t.tabid,
					?
				FROM
					vtiger_profile p
					CROSS JOIN vtiger_tab t
				WHERE
					p.profilename=?',
				array (ModuleProfileInterface::PERMISSION_ALLOW, $profileName)
			);
			$this->adb->pquery (
				"INSERT INTO vtiger_profile2standardpermissions (profileid, tabid, operation, permissions)
				SELECT
					p.profileid,
					t.tabid,
					am.actionid,
					?
				FROM
					vtiger_profile p
					CROSS JOIN vtiger_actionmapping am ON am.actionname IN ('Delete', 'DetailView', 'EditView', 'index', 'Save')
					CROSS JOIN vtiger_tab t
				WHERE
					p.profilename=?",
				array (ModuleProfileInterface::PERMISSION_ALLOW, $profileName)
			);
			$this->adb->pquery (
				"INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission)
				SELECT
					p.profileid,
					t.tabid,
					am.actionid,
					IF(am.actionname IN ('DuplicatesHandling', 'Export', 'Import'), ?, ?)
				FROM
					vtiger_profile p
					CROSS JOIN vtiger_actionmapping am ON am.actionname IN ('DuplicatesHandling', 'Export', 'Import', 'Merge')
					CROSS JOIN vtiger_tab t
				WHERE
					p.profilename=?",
				array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY, $profileName)
			);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param ModuleProfile $profile
		 */
		public function deleteProfile ($profile) {
			if ((empty ($profile)) || (!($profile instanceof ModuleProfile))) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery (
				'DELETE
					p2u
				FROM
					vtiger_profile2utility p2u
					INNER JOIN vtiger_profile p ON p.profileid=p2u.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2u.tabid AND t.name=?',
				array ($profile->getProfileName (), $profile->getModuleName ())
			);
			$this->adb->pquery (
				'DELETE
					p2sp
				FROM
					vtiger_profile2standardpermissions p2sp
					INNER JOIN vtiger_profile p ON p.profileid=p2sp.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2sp.tabid AND t.name=?',
				array ($profile->getProfileName (), $profile->getModuleName ())
			);
			$this->adb->pquery (
				'DELETE
					p2t
				FROM
					vtiger_profile2tab p2t
					INNER JOIN vtiger_profile p ON p.profileid=p2t.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid AND t.name=?',
				array ($profile->getProfileName (), $profile->getModuleName ())
			);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $moduleName
		 */
		public function deleteProfiles ($moduleName) {
			if (empty ($moduleName)) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery (
				'DELETE
					p2u
				FROM
					vtiger_profile2utility p2u
					INNER JOIN vtiger_tab t ON t.tabid=p2u.tabid AND t.name=?',
				array ($moduleName)
			);
			$this->adb->pquery (
				'DELETE
					p2sp
				FROM
					vtiger_profile2standardpermissions p2sp
					INNER JOIN vtiger_tab t ON t.tabid=p2sp.tabid AND t.name=?',
				array ($moduleName)
			);
			$this->adb->pquery (
				'DELETE
					p2t
				FROM
					vtiger_profile2tab p2t
					INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid AND t.name=?',
				array ($moduleName)
			);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $profileName
		 * @param string $moduleName
		 */
		public function deleteProfilesByProfileAndModuleName ($profileName, $moduleName) {
			if ((empty ($profileName)) || (empty ($moduleName))) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery (
				'DELETE
					p2u
				FROM
					vtiger_profile2utility p2u
					INNER JOIN vtiger_profile p ON p.profileid=p2u.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2u.tabid AND t.name=?',
				array ($profileName, $moduleName)
			);
			$this->adb->pquery (
				'DELETE
					p2sp
				FROM
					vtiger_profile2standardpermissions p2sp
					INNER JOIN vtiger_profile p ON p.profileid=p2sp.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2sp.tabid AND t.name=?',
				array ($profileName, $moduleName)
			);
			$this->adb->pquery (
				'DELETE
					p2t
				FROM
					vtiger_profile2tab p2t
					INNER JOIN vtiger_profile p ON p.profileid=p2t.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid AND t.name=?',
				array ($profileName, $moduleName)
			);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $profileName
		 */
		public function deleteProfilesByProfileName ($profileName) {
			if (empty ($profileName)) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery (
				'DELETE
					p2u
				FROM
					vtiger_profile2utility p2u
					INNER JOIN vtiger_profile p ON p.profileid=p2u.profileid AND p.profilename=?',
				array ($profileName)
			);
			$this->adb->pquery (
				'DELETE
					p2sp
				FROM
					vtiger_profile2standardpermissions p2sp
					INNER JOIN vtiger_profile p ON p.profileid=p2sp.profileid AND p.profilename=?',
				array ($profileName)
			);
			$this->adb->pquery (
				'DELETE
					p2t
				FROM
					vtiger_profile2tab p2t
					INNER JOIN vtiger_profile p ON p.profileid=p2t.profileid AND p.profilename=?',
				array ($profileName)
			);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $profileName
		 * @param string $moduleName
		 *
		 * @return ModuleProfile|null
		 */
		public function fetchProfileByProfileName ($profileName, $moduleName) {
			if ((empty ($profileName)) || (empty ($moduleName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					p2t.*
				FROM
					vtiger_profile2tab p2t
					INNER JOIN vtiger_profile p ON p.profileid=p2t.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid AND t.name=?',
				array ($profileName, $moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row                 = $this->adb->fetchByAssoc ($result, -1, false);
				$standardPermissions = $this->fetchStandardPermissions ($profileName, $moduleName);
				$utilityPermissions  = $this->fetchUtilityPermissions ($profileName, $moduleName);
				$moduleProfile       = ModuleProfile::getInstance ()
					->setModuleName ($moduleName)
					->setProfileName ($profileName)
					->setAccessPermission (intval ($row ['permissions']))
					->setDeletePermission ($standardPermissions ['Delete'])
					->setEditPermission ($standardPermissions ['EditView'])
					->setExportPermission ($utilityPermissions ['Export'])
					->setHandleDuplicatesPermission ($utilityPermissions ['DuplicatesHandling'])
					->setImportPermission ($utilityPermissions ['Import'])
					->setListPermission ($standardPermissions ['index'])
					->setMergePermission ($utilityPermissions ['Merge'])
					->setReadPermission ($standardPermissions ['DetailView'])
					->setSavePermission ($standardPermissions ['Save']);
			} else {
				$moduleProfile = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $moduleProfile;
		}

		/**
		 * @param string $moduleName
		 *
		 * @return ModuleProfile[]|null
		 */
		public function fetchProfiles ($moduleName) {
			if (empty ($moduleName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					p2t.*,
					p.profilename
				FROM
					vtiger_profile2tab p2t
					INNER JOIN vtiger_profile p ON p.profileid=p2t.profileid
					INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid AND t.name=?',
				array ($moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$profiles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$standardPermissions = $this->fetchStandardPermissions ($row ['profilename'], $moduleName);
					$utilityPermissions  = $this->fetchUtilityPermissions ($row ['profilename'], $moduleName);
					$profiles []         = ModuleProfile::getInstance ()
						->setModuleName ($moduleName)
						->setProfileName ($row ['profilename'])
						->setAccessPermission (intval ($row ['permissions']))
						->setDeletePermission ($standardPermissions ['Delete'])
						->setEditPermission ($standardPermissions ['EditView'])
						->setExportPermission ($utilityPermissions ['Export'])
						->setHandleDuplicatesPermission ($utilityPermissions ['DuplicatesHandling'])
						->setImportPermission ($utilityPermissions ['Import'])
						->setListPermission ($standardPermissions ['index'])
						->setMergePermission ($utilityPermissions ['Merge'])
						->setReadPermission ($standardPermissions ['DetailView'])
						->setSavePermission ($standardPermissions ['Save']);
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
		 * @return ModuleProfile[]|null
		 */
		public function fetchProfilesByProfileName ($profileName, $excludedModuleNames = null) {
			if (empty ($profileName)) {
				return null;
			}

			if (!empty ($excludedModuleNames)) {
				$questionMarks = str_repeat ('?, ', (count ($excludedModuleNames) - 1)) . '?';
				$joinClause    = "AND t.name NOT IN ({$questionMarks})";
				$arguments     = $excludedModuleNames;
			} else {
				$joinClause = '';
				$arguments  = array ();
			}

			$result = $this->adb->pquery (
				"SELECT
					p2t.*,
					t.name AS modulename
				FROM
					vtiger_profile2tab p2t
					INNER JOIN vtiger_profile p ON p.profileid=p2t.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid {$joinClause}",
				array_merge (array ($profileName), $arguments)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$profiles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$standardPermissions = $this->fetchStandardPermissions ($profileName, $row ['modulename']);
					$utilityPermissions  = $this->fetchUtilityPermissions ($profileName, $row ['modulename']);
					$profiles []         = ModuleProfile::getInstance ()
						->setModuleName ($row ['modulename'])
						->setProfileName ($profileName)
						->setAccessPermission (intval ($row ['permissions']))
						->setDeletePermission ($standardPermissions ['Delete'])
						->setEditPermission ($standardPermissions ['EditView'])
						->setExportPermission ($utilityPermissions ['Export'])
						->setHandleDuplicatesPermission ($utilityPermissions ['DuplicatesHandling'])
						->setImportPermission ($utilityPermissions ['Import'])
						->setListPermission ($standardPermissions ['index'])
						->setMergePermission ($utilityPermissions ['Merge'])
						->setReadPermission ($standardPermissions ['DetailView'])
						->setSavePermission ($standardPermissions ['Save']);
				}
			} else {
				$profiles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profiles;
		}

		/**
		 * @param ModuleProfile $profile
		 *
		 * @return ModuleProfile
		 */
		public function saveProfile ($profile) {
			$this->validate ($profile);

			$result    = $this->adb->pquery ('SELECT * FROM vtiger_profile WHERE profilename=?', array ($profile->getProfileName ()));
			$row       = $this->adb->fetchByAssoc ($result, -1, false);
			$profileId = intval ($row ['profileid']);
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result   = $this->adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($profile->getModuleName ()));
			$row      = $this->adb->fetchByAssoc ($result, -1, false);
			$moduleId = intval ($row ['tabid']);
			DatabaseUtils::closeResult ($result);
			$result = null;

			$this->adb->startTransaction ();
			$result = $this->adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE profileid=? AND tabid=?', array ($profileId, $moduleId));
			if ($this->adb->num_rows ($result) == 0) {
				$this->adb->pquery (
					'INSERT INTO vtiger_profile2tab (profileid, tabid, permissions) VALUES (?, ?, ?)',
					array ($profileId, $moduleId, $profile->getAccessPermission ())
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_profile2tab SET permissions=? WHERE profileid=? AND tabid=?',
					array ($profile->getAccessPermission (), $profileId, $moduleId)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$this->saveStandardPermissions ($profileId, $moduleId, $profile);
			$this->saveUtilityPermissions ($profileId, $moduleId, $profile);
			$this->adb->completeTransaction ();
			return $profile;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ModuleProfileManager
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

<?php
	require_once ('include/platzilla/Objects/FieldProfile.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class FieldProfileManager {
		/** @var FieldProfileManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param FieldProfile $profile
		 *
		 * @throws FieldProfileException
		 */
		private function validate ($profile) {
			if ((empty ($profile)) || (!($profile instanceof FieldProfile))) {
				throw new FieldProfileException (FieldProfileException::ERROR_FIELD_PROFILE_EMPTY_PROFILE);
			}

			$profile->validate ();
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 */
		public function createDefaultProfiles ($moduleName, $fieldName) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery (
				'INSERT INTO vtiger_def_org_field (tabid, fieldid, visible, readonly)
				SELECT
					f.tabid,
					f.fieldid,
					?,
					?
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
				WHERE
					f.fieldname=?',
				array (FieldProfile::VISIBILITY_VISIBLE, FieldProfile::READ_WRITE, $moduleName, $fieldName)
			);
			$this->adb->pquery (
				'INSERT INTO vtiger_profile2field (profileid, tabid, fieldid, visible, readonly)
				SELECT
					p.profileid,
					f.tabid,
					f.fieldid,
					?,
					?
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
					CROSS JOIN vtiger_profile p
				WHERE
					f.fieldname=?',
				array (FieldProfile::VISIBILITY_VISIBLE, FieldProfile::READ_WRITE, $moduleName, $fieldName)
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

			$this->adb->pquery (
				'INSERT INTO vtiger_profile2field (profileid, tabid, fieldid, visible, readonly)
				SELECT
					p.profileid,
					f.tabid,
					f.fieldid,
					?,
					?
				FROM
					vtiger_profile p
					CROSS JOIN vtiger_field f
				WHERE
					p.profilename=?',
				array (FieldProfile::VISIBILITY_VISIBLE, FieldProfile::READ_WRITE, $profileName)
			);
		}

		/**
		 * @param FieldProfile $profile
		 */
		public function deleteProfile ($profile) {
			if ((empty ($profile)) || (!($profile instanceof FieldProfile))) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery (
				'DELETE
					dof
				FROM
					vtiger_def_org_field dof
					INNER JOIN vtiger_field f ON f.fieldid=dof.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.fieldid AND t.name=?',
				array ($profile->getFieldName (), $profile->getModuleName ())
			);
			$this->adb->pquery (
				'DELETE
					p2f
				FROM
					vtiger_profile2field p2f
					INNER JOIN vtiger_profile p ON p.profileid=p2f.profileid AND p.profilename=?
					INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.fieldid AND t.name=?',
				array ($profile->getProfileName (), $profile->getFieldName (), $profile->getModuleName ())
			);
			$this->adb->completeTransaction ();
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 */
		public function deleteProfiles ($moduleName, $fieldName) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return;
			}

			$this->adb->startTransaction ();
			$this->adb->pquery (
				'DELETE
					dof
				FROM
					vtiger_def_org_field dof
					INNER JOIN vtiger_field f ON f.fieldid=dof.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($fieldName, $moduleName)
			);
			$this->adb->pquery (
				'DELETE
					p2f
				FROM
					vtiger_profile2field p2f
					INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?',
				array ($fieldName, $moduleName)
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

			$this->adb->pquery (
				'DELETE
					p2f
				FROM
					vtiger_profile2field p2f
					INNER JOIN vtiger_profile p ON p.profileid=p2f.profileid AND p.profilename=?
					INNER JOIN vtiger_tab t ON t.tabid=p2f.tabid AND t.name=?',
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
					p2f
				FROM
					vtiger_profile2field p2f
					INNER JOIN vtiger_profile p ON p.profileid=p2f.profileid AND p.profilename=?',
				array ($profileName)
			);
		}

		/**
		 * @param string $profileName
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return FieldProfile|null
		 */
		public function fetchProfileByProfileName ($profileName, $moduleName, $fieldName) {
			if ((empty ($profileName)) || (empty ($moduleName)) || (empty ($fieldName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					p2f.*
				FROM
					vtiger_profile2field p2f
					INNER JOIN vtiger_profile p ON p.profileid=p2f.profileid AND p.profilename=?
					INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.fieldid AND t.name=?',
				array ($profileName, $fieldName, $moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row     = $this->adb->fetchByAssoc ($result, -1, false);
				$profile = FieldProfile::getInstance ()
					->setFieldName ($fieldName)
					->setModuleName ($moduleName)
					->setProfileName ($profileName)
					->setReadOnly ($row ['readonly'])
					->setVisibility ($row ['visible']);
			} else {
				$profile = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profile;
		}

		/**
		 * @param string $profileName
		 * @param string[]|null $excludedModuleNames
		 *
		 * @return FieldProfile[]|null
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
					p2f.*,
					t.name AS modulename,
					f.fieldname
				FROM
					vtiger_profile2field p2f
					INNER JOIN vtiger_profile p ON p.profileid=p2f.profileid AND p.profilename=?
					INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid {$joinClause}",
				array_merge (array ($profileName), $arguments)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$profiles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$profiles [] = FieldProfile::getInstance ()
						->setFieldName ($row ['fieldname'])
						->setModuleName ($row ['modulename'])
						->setProfileName ($profileName)
						->setReadOnly ($row ['readonly'])
						->setVisibility ($row ['visible']);
				}
			} else {
				$profiles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profiles;
		}

		/**
		 * @param string $moduleName
		 * @param string $fieldName
		 *
		 * @return FieldProfile[]|null
		 */
		public function fetchProfiles ($moduleName, $fieldName) {
			if ((empty ($moduleName)) || (empty ($fieldName))) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					p2f.*,
					p.profilename
				FROM
					vtiger_profile2field p2f
					INNER JOIN vtiger_profile p ON p.profileid=p2f.profileid
					INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=?
					INNER JOIN vtiger_tab t ON t.tabid=f.fieldid AND t.name=?',
				array ($fieldName, $moduleName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$profiles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$profiles [] = FieldProfile::getInstance ()
						->setFieldName ($fieldName)
						->setModuleName ($moduleName)
						->setProfileName ($row ['profilename'])
						->setReadOnly ($row ['readonly'])
						->setVisibility ($row ['visible']);
				}
			} else {
				$profiles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profiles;
		}

		/**
		 * @param FieldProfile $profile
		 *
		 * @return FieldProfile
		 */
		public function saveProfile ($profile) {
			$this->validate ($profile);

			$result = $this->adb->pquery ('SELECT * FROM vtiger_profile WHERE profilename=?', array ($profile->getProfileName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row       = $this->adb->fetchByAssoc ($result, -1, false);
				$profileId = intval ($row ['profileid']);
			} else {
				$profileId = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery (
				'SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?',
				array ($profile->getModuleName (), $profile->getFieldName ())
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row      = $this->adb->fetchByAssoc ($result, -1, false);
				$moduleId = intval ($row ['tabid']);
				$fieldId  = intval ($row ['fieldid']);
			} else {
				$moduleId = 0;
				$fieldId  = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$result = $this->adb->pquery ('SELECT * FROM vtiger_profile2field WHERE profileid=? AND tabid=? AND fieldid=?', array ($profileId, $moduleId, $fieldId));
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				$this->adb->pquery (
					'INSERT INTO vtiger_profile2field (profileid, tabid, fieldid, visible, readonly) VALUES (?, ?, ?, ?, ?)',
					array ($profileId, $moduleId, $fieldId, $profile->getVisibility (), $profile->getReadOnly ())
				);
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_profile2field SET tabid=?, visible=?, readonly=? WHERE profileid=? AND fieldid=?',
					array ($moduleId, $profile->getVisibility (), $profile->getReadOnly (), $profileId, $fieldId)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profile;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return FieldProfileManager
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

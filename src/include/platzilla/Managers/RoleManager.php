<?php
	require_once ('include/platzilla/Managers/ProfileManager.php');
	require_once ('include/platzilla/Objects/Role.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Class RoleManager
	 */
	class RoleManager {
		/** @var RoleManager[]|null */
		private static $INSTANCES = null;

		/** @var Role[]|null Caché de roles por ID */
		private static $ROLE_CACHE = array();

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param string $roleId
		 * @param boolean $headersOnly
		 *
		 * @return Profile[]|null
		 */
		private function fetchProfiles ($roleId, $headersOnly = false) {
			if (empty ($roleId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT p.* FROM vtiger_role2profile r2p INNER JOIN vtiger_profile p ON p.profileid=r2p.profileid WHERE r2p.roleid=?', array ($roleId));
			if ($this->adb->num_rows ($result) > 0) {
				$pm       = ProfileManager::getInstance ($this->adb);
				$profiles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$profiles [] = $pm->fetchProfile ($row ['profilename'], $headersOnly);
				}
			} else {
				$profiles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $profiles;
		}

		/**
		 * @param Role $role
		 *
		 * @return string|null
		 */
		private function fetchParentRoleString ($role) {
			if ((empty ($role)) || (!($role instanceof Role))) {
				return null;
			}

			$roleId = $role->getId ();
			if (empty ($roleId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_role WHERE roleid=?', array ($roleId));
			if ($this->adb->num_rows ($result) > 0) {
				$row              = $this->adb->fetchByAssoc ($result, -1, false);
				$parentRoleString = $row ['parentrole'];
			} else {
				$parentRoleString = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $parentRoleString;
		}

		/**
		 * @param Role $role
		 */
		private function savePicklistsPermissions ($role) {
			$this->adb->pquery (
				'INSERT IGNORE INTO vtiger_role2picklist (roleid, picklistvalueid, picklistid, sortid)
				SELECT ?, picklistvalueid, picklistid, sortid FROM vtiger_role2picklist WHERE roleid=?',
				array ($role->getId (), 'H1')
			);
		}

		/**
		 * @param Role $role
		 */
		private function saveProfiles ($role) {
			$roleId = $role->getId ();
			$this->adb->pquery ('DELETE FROM vtiger_role2profile WHERE roleid=?', array ($roleId));

			$profiles = $role->getProfiles ();
			if (empty ($profiles)) {
				return;
			}

			foreach ($profiles as $profile) {
				$profileId = $profile->getId ();
				if (!empty ($profileId)) {
					$this->adb->pquery ('INSERT INTO vtiger_role2profile (roleid, profileid) VALUES (?, ?)', array ($roleId, $profileId));
				}
			}
		}

		/**
		 * @param Role $role
		 *
		 * @throws RoleException
		 */
		private function validate ($role) {
			$role->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_role WHERE rolename=?', array ($role->getName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row    = $this->adb->fetchByAssoc ($result, -1, false);
				$roleId = $role->getId ();
				if ((empty ($roleId)) || ($row ['roleid'] != $roleId)) {
					$e = new RoleException (RoleException::ERROR_ROLE_DUPLICATE_NAME);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param Role $role
		 */
		public function deleteRole ($role) {
			if ((empty ($role)) || (!($role instanceof Role))) {
				return;
			}

			$roleId = $role->getId ();
			if (empty ($roleId)) {
				return;
			}

			$this->adb->pquery ('DELETE FROM vtiger_role2picklist WHERE roleid=?', array ($roleId));
			$this->adb->pquery ('DELETE FROM vtiger_role2profile WHERE roleid=?', array ($roleId));
			$this->adb->pquery ('DELETE FROM vtiger_role WHERE roleid=?', array ($roleId));
		}

		/**
		 * @param string $roleId
		 * @param boolean $headersOnly
		 *
		 * @return Role|null
		 */
		public function fetchRole ($roleId, $headersOnly = false) {
			if (empty ($roleId)) {
				return null;
			}

			// Verificar caché
			$cacheKey = $roleId . '_' . ($headersOnly ? '1' : '0');
			if (isset (self::$ROLE_CACHE [$cacheKey])) {
				return self::$ROLE_CACHE [$cacheKey];
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_role WHERE roleid=?', array ($roleId));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				if ((!empty ($row ['parentrole'])) && (strtoupper ($row ['parentrole']) != strtoupper ($roleId))) {
					$dummy        = explode ('::', $row ['parentrole']);
					$parentRoleId = $dummy [ (count ($dummy) - 2) ];
					$parentRole   = $this->fetchRole ($parentRoleId, $headersOnly);
				} else {
					$parentRole = null;
				}
				$role = Role::getInstance ()
					->setId ($row ['roleid'])
					->setDefaultModule ($row ['default_module'])
					->setName ($row ['rolename'])
					->setParent ($parentRole)
					->setProfiles ($this->fetchProfiles ($row ['roleid'], $headersOnly));
				// Guardar en caché
				self::$ROLE_CACHE [$cacheKey] = $role;
			} else {
				$role = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $role;
		}

		/**
		 * @param boolean $headersOnly
		 *
		 * @return Role[]|null
		 */
		public function fetchRoles ($headersOnly = false) {
			$result = $this->adb->query ('SELECT * FROM vtiger_role ORDER BY roleid');
			if ($this->adb->num_rows ($result) > 0) {
				$roles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$roleId = $row ['roleid'];
					if ((!empty ($row ['parentrole'])) && (strtoupper ($row ['parentrole']) != strtoupper ($roleId))) {
						$dummy        = explode ('::', $row ['parentrole']);
						$parentRoleId = $dummy [ (count ($dummy) - 2) ];
						$parentRole   = $this->fetchRole ($parentRoleId, $headersOnly);
					} else {
						$parentRole = null;
					}

					$roles [] = Role::getInstance ()
						->setId ($roleId)
						->setDefaultModule ($row ['default_module'])
						->setName ($row ['rolename'])
						->setParent ($parentRole)
						->setProfiles ($this->fetchProfiles ($row ['roleid'], $headersOnly));
				}
			} else {
				$roles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $roles;
		}

		/**
		 * @param string $profileName
		 * @param boolean $headersOnly
		 *
		 * @return Role[]|null
		 */
		public function fetchRolesByProfileName ($profileName, $headersOnly = false) {
			if (empty ($profileName)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					r.*
				FROM
					vtiger_role r
					INNER JOIN vtiger_role2profile r2p ON r2p.roleid=r.roleid
					INNER JOIN vtiger_profile p ON p.profileid=r2p.profileid AND p.profilename=?',
				array ($profileName)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$roles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$roles [] = $this->fetchRole ($row ['roleid'], $headersOnly);
				}
			} else {
				$roles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $roles;
		}

		/**
		 * @param Role $role
		 *
		 * @return Role
		 */
		public function saveRole ($role) {
			$this->validate ($role);

			$roleId = $role->getId ();
			if (!empty ($roleId)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_role WHERE roleid=?', array ($roleId));
			} else {
				$result = null;
			}
			$parentRoleString = $this->fetchParentRoleString ($role->getParent ());
			$depth            = !empty ($parentRoleString) ? count (explode ('::', $parentRoleString)) : 0;

			if ($this->adb->num_rows ($result) == 0) {
				$roleId     = !empty ($roleId) ? $roleId : "H{$this->adb->getUniqueID ('vtiger_role')}";
				$roleString = !empty ($parentRoleString) ? "{$parentRoleString}::{$roleId}" : $roleId;
				$this->adb->pquery (
					'INSERT INTO vtiger_role (roleid, rolename, parentrole, depth, iscustomer, ispartner, default_module) VALUES (?, ?, ?, ?, NULL, NULL, NULL)',
					array ($roleId, $role->getName (), $roleString, $depth)
				);
				$role->setId ($roleId);
			} else {
				$roleString = !empty ($parentRoleString) ? "{$parentRoleString}::{$roleId}" : $roleId;
				$this->adb->pquery ('UPDATE vtiger_role SET rolename=?, parentrole=?, depth=? WHERE roleid=?', array ($role->getName (), $roleString, $depth, $roleId));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$this->saveProfiles ($role);
			$this->savePicklistsPermissions ($role);
			return $role;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return RoleManager
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

<?php
	require_once ('include/platzilla/Managers/RoleManager.php');
	require_once ('include/platzilla/Objects/User.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/PlatzillaUtils.class.php');

	/**
	 * Class UserManager
	 */
	class UserManager {
		/** @var UserManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		/** @var string */
		private $platform;

		public function __construct (PearDatabase $adb, $platform) {
			$this->adb      = $adb;
			$this->platform = !empty ($platform) ? $platform : '';
		}

		/**
		 * @param string $userName
		 * @param string $plainPassword
		 *
		 * @return string
		 */
		private function encryptPassword ($userName, $plainPassword) {
			$salt = '$1$' . str_pad (substr ($userName, 0, 2), 9, '0');
			return crypt ($plainPassword, $salt);
		}

		/**
		 * @param $currencyCode
		 *
		 * @return integer
		 */
		private function fetchCurrencyId ($currencyCode) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_currency_info WHERE currency_code=?', array ($currencyCode));
			if ($this->adb->num_rows ($result) > 0) {
				$row        = $this->adb->fetchByAssoc ($result, -1, false);
				$currencyId = intval ($row ['id']);
			} else {
				$currencyId = 1;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $currencyId;
		}

		/**
		 * @param integer $userId
		 *
		 * @return Role[]|null
		 */
		private function fetchRoles ($userId) {
			if (empty ($userId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_user2role WHERE userid=?', array ($userId));
			if ($this->adb->num_rows ($result) > 0) {
				$rm    = RoleManager::getInstance ($this->adb);
				$roles = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$roles [] = $rm->fetchRole ($row ['roleid']);
				}
			} else {
				$roles = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $roles;
		}

		/**
		 * @param string $imageFileName
		 *
		 * @return null|string
		 */
		private function getUserImageUri ($imageFileName) {
			$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			$imageFilePath  = !empty ($imageFileName) ? "{$this->platform}/user_images/{$imageFileName}" : null;
			return (!empty ($imageFileName)) && (file_exists ("{$rootFolderPath}/{$imageFilePath}")) ? $imageFilePath : null;
		}

		/**
		 * @param User $user
		 */
		private function saveRoles ($user) {
			$roles = $user->getRoles ();
			$this->adb->pquery ('DELETE FROM vtiger_user2role WHERE userid=?', array ($user->getId ()));
			if (empty ($roles)) {
				return;
			}

			foreach ($roles as $role) {
				$this->adb->pquery ('INSERT INTO vtiger_user2role (userid, roleid) VALUES (?, ?)', array ($user->getId (), $role->getId ()));
			}
		}

		/**
		 * @param User $user
		 *
		 * @throws UserException
		 */
		private function validate ($user) {
			$user->validate ();

			$result = $this->adb->pquery ('SELECT * FROM vtiger_users WHERE deleted=0 AND user_name=?', array ($user->getUserName ()));
			if ($this->adb->num_rows ($result) > 0) {
				$row = $this->adb->fetchByAssoc ($result, -1, false);
				if ((empty ($user->getId ())) || ($row ['id'] != $user->getId ())) {
					$e = new UserException (UserException::ERROR_USER_DUPLICATE_USER_NAME);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			if (isset ($e)) {
				throw $e;
			}
		}

		/**
		 * @param User $user
		 */
		public function deleteUser ($user) {
			if ((empty ($user)) || (!($user instanceof User)) || (empty ($user->getId ()))) {
				return;
			}

			$this->adb->pquery ('DELETE FROM vtiger_users WHERE id=?', array ($user->getId ()));
		}

		/**
		 * @param integer $userId
		 * @param boolean $headersOnly
		 *
		 * @return User|null
		 */
		public function fetchUserById ($userId, $headersOnly = false) {
			if (empty ($userId)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_users u WHERE u.deleted=0 AND id=?', array ($userId));
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$user = User::getInstance ()
					->setAdministrator ($row ['is_admin'] == 'on')
					->setDefaultModuleName ($row ['default_module'])
					->setDefaultOperating ($row ['default_operating'])
					->setDefaultHomeTab ($row ['defaulthometab'])
					->setEmail ($row ['email1'])
					->setFirstName ($row ['first_name'])
					->setId (intval ($row ['id']))
					->setImageUri ($this->getUserImageUri ($row ['imagename']))
					->setLastName ($row ['last_name'])
					->setRoles (!$headersOnly ? $this->fetchRoles ($userId) : null)
					->setStatus ($row ['status'])
					->setUserName ($row ['user_name']);
			} else {
				$user = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $user;
		}

		/**
		 * @param string $username
		 *
		 * @return null|User
		 */
		public function fetchUserByUsername ($username) {
			if (empty ($username)) {
				return null;
			}

			$result = $this->adb->pquery ('SELECT * FROM vtiger_users u WHERE u.deleted=0 AND user_name=?', array ($username));
			if ($this->adb->num_rows ($result) > 0) {
				$row  = $this->adb->fetchByAssoc ($result, -1, false);
				$user = User::getInstance ()
					->setAdministrator ($row ['is_admin'] == 'on')
					->setDefaultModuleName ($row ['default_module'])
					->setDefaultOperating ($row ['default_operating'])
					->setDefaultHomeTab ($row ['defaulthometab'])
					->setEmail ($row ['email1'])
					->setFirstName ($row ['first_name'])
					->setId (intval ($row ['id']))
					->setImageUri ($this->getUserImageUri ($row ['imagename']))
					->setLastName ($row ['last_name'])
					->setRoles ($this->fetchRoles ($row ['id']))
					->setStatus ($row ['status'])
					->setUserName ($row ['user_name']);
			} else {
				$user = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $user;
		}

		/**
		 * @return User[]|null
		 */
		public function fetchUsers () {
			$result = $this->adb->query ('SELECT * FROM vtiger_users WHERE deleted=0');
			if ($this->adb->num_rows ($result) > 0) {
				$users = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$users [] = User::getInstance ()
						->setAdministrator ($row ['is_admin'] == 'on')
						->setDefaultModuleName ($row ['default_module'])
						->setDefaultOperating ($row ['default_operating'])
						->setDefaultHomeTab ($row ['defaulthometab'])
						->setEmail ($row ['email1'])
						->setFirstName ($row ['first_name'])
						->setId (intval ($row ['id']))
						->setImageUri ($this->getUserImageUri ($row ['imagename']))
						->setLastName ($row ['last_name'])
						->setStatus ($row ['status'])
						->setUserName ($row ['user_name']);
				}
			} else {
				$users = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $users;
		}

		/**
		 * @param string $roleId
		 *
		 * @return User[]|null
		 */
		public function fetchUsersByRole ($roleId) {
			if (empty ($roleId)) {
				return null;
			}

			$result = $this->adb->pquery (
				'SELECT
					u.*
				FROM
					vtiger_users u
					INNER JOIN vtiger_user2role u2r ON u2r.userid=u.id AND u2r.roleid=?
				WHERE
					u.deleted=0',
				array ($roleId)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$users = array ();
				while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
					$users [] = User::getInstance ()
						->setAdministrator ($row ['is_admin'] == 'on')
						->setDefaultModuleName ($row ['default_module'])
						->setDefaultOperating ($row ['default_operating'])
						->setDefaultHomeTab ($row ['defaulthometab'])
						->setEmail ($row ['email1'])
						->setFirstName ($row ['first_name'])
						->setId (intval ($row ['id']))
						->setImageUri ($this->getUserImageUri ($row ['imagename']))
						->setLastName ($row ['last_name'])
						->setStatus ($row ['status'])
						->setUserName ($row ['user_name']);
				}
			} else {
				$users = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $users;
		}

		/**
		 * @param User $user
		 *
		 * @return User
		 * @throws UserException
		 */
		public function saveUser ($user) {
			$this->validate ($user);

			$activityView                              = 'This Week';
			$cryptType                                  = 'PHP5.3MD5';
			$currencyCode                          = 'EUR';
			$currencyDecimalSeparator   = '.';
			$currencyGroupingPattern      = '123,456,789';
			$currencyGroupingSeparator  = ',';
			$currencySymbolPlacement   = '$1.0';
			$dateFormat                               = 'yyyy-mm-dd';
			$language                                   = 'es_es';
			$leadView                                   = 'Today';
            $imageName                             = null;
			$reminderInterval                      = '1 Minute';
			$startTime                                  = '08:00';
			$theme                                        = 'centaurus';
			$timeFormat                              = 'am/pm';
			$timeZone                                  = 'UTC';

			$currencyId = $this->fetchCurrencyId ($currencyCode);

			$userId                     = $user->getId ();
			$email                      = $user->getEmail ();
			$firstName              = $user->getFirstName ();
			$lastName              = $user->getlastName ();
			$plainPassword    = $user->getPlainPassword ();
			$status                     = $user->getStatus ();
			$userName             = $user->getUserName ();
			$isAdministrator    = $user->isAdministrator () ? 'on' : 'off';
			$defaultModule      = $user->getDefaultModuleName ();
			$defaultOperating  = $user->getDefaultOperating ();
			$defaultTab            = $user->getDefaultHomeTab ();

			if (!empty ($userId)) {
				$result = $this->adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($userId));
			} else {
				$result = null;
			}

			if ($this->adb->num_rows ($result) == 0) {
				$userId            = !empty ($userId) ? $userId : $this->adb->getUniqueID ('vtiger_users');
				$encryptedPassword = $this->encryptPassword ($userName, $plainPassword);
				$hash              = strtolower (md5 ($plainPassword));
				$this->adb->pquery (
					'INSERT INTO vtiger_users (id, user_name, user_password, user_hash, first_name, last_name, is_admin, currency_id, email1, status, date_format, hour_format, start_hour, activity_view, lead_view, imagename, confirm_password, reminder_interval, crypt_type, theme, language, time_zone, currency_grouping_pattern, currency_decimal_separator, currency_grouping_separator, currency_symbol_placement, default_module, default_operating, defaulthometab) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($userId, $userName, $encryptedPassword, $hash, $firstName, $lastName, $isAdministrator, $currencyId, $email, $status, $dateFormat, $timeFormat, $startTime, $activityView, $leadView, $imageName, $encryptedPassword, $reminderInterval, $cryptType, $theme, $language, $timeZone, $currencyGroupingPattern, $currencyDecimalSeparator, $currencyGroupingSeparator, $currencySymbolPlacement, $defaultModule, $defaultOperating, $defaultTab)
				);
				$user->setId ($userId);
			} else {
				if (!empty ($plainPassword)) {
					$encryptedPassword = $this->encryptPassword ($userName, $plainPassword);
					$hash              = strtolower (md5 ($plainPassword));
				} else {
					$row               = $this->adb->fetchByAssoc ($result, -1, false);
					$encryptedPassword = $row ['user_password'];
					$hash              = $row ['user_hash'];
				}
				$this->adb->pquery (
					'UPDATE vtiger_users SET user_name=?, user_password=?, user_hash=?, first_name=?, last_name=?, is_admin=?, currency_id=?, email1=?, status=?, date_format=?, hour_format=?, start_hour=?, activity_view=?, lead_view=?,  imagename=?, confirm_password=?, reminder_interval=?, crypt_type=?, theme=?, language=?, time_zone=?, currency_grouping_pattern=?, currency_decimal_separator=?, currency_grouping_separator=?, currency_symbol_placement=?, defaulthometab=? WHERE id=?',
					array ($userName, $encryptedPassword, $hash, $firstName, $lastName, $isAdministrator, $currencyId, $email, $status, $dateFormat, $timeFormat, $startTime, $activityView, $leadView, $imageName, $encryptedPassword, $reminderInterval, $cryptType, $theme, $language, $timeZone, $currencyGroupingPattern, $currencyDecimalSeparator, $currencyGroupingSeparator, $currencySymbolPlacement, $defaultTab, $userId)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			$this->saveRoles ($user);

			return $user;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $platform
		 *
		 * @return UserManager
		 */
		public static function getInstance (PearDatabase $adb, $platform) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb, $platform);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}

	}

<?php
	require_once ('include/platzilla/Data/Agents.php');
	require_once ('include/platzilla/Managers/PlatformManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('include/utils/utils.php');
	require_once ('include/platzilla/Managers/PlatformInstanceManager.php');

	abstract class UsersHelper {
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return PlatformInstance[]|null
		 * @throws PlatformException
		 */
		private static function fetchPlatformInstances($adb, $userId) {
			$result = $adb->pquery('SELECT * FROM vtiger_agents2instances WHERE agentsid = ?', array ($userId));
			if ($adb->num_rows ($result) > 0) {
				$platformInstances = array();
				$pim = PlatformInstanceManager::getInstance($adb);
				while ($row = $adb->fetchByAssoc ($result)) {
					$platformInstances [] = $pim->fetchInstance ($row ['code'], $row ['administrator'],true,false);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($platformInstances)) ? $platformInstances : null;
		}
		
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $agentId
		 * @param string $status
		 *
		 * @return void
		 * @throws Exception
		 */
		public static function changeStatusToAgent ($adb, $agentId, $status) {
			if (empty($agentId)) {
				throw new Exception('Agente desconocido');
			} else if (empty($status)) {
				throw new Exception('Imposible cambiar el estado!');
			}
			$adb->pquery (
				'UPDATE vtiger_agents SET status=? WHERE agentsid=?',
				array ($status, $agentId)
			);
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @return void
		 */
		public static function deleteAgent($adb, $userId) {
			$adb->pquery('DELETE FROM vtiger_agents2instances WHERE agentsid = ?', array ($userId));
			$adb->pquery('DELETE FROM vtiger_agents WHERE agentsid = ?', array ($userId));
		}
		
		/**
		 * @param $adb
		 *
		 * @return Agents[]|null
		 * @throws PlatformException
		 */
		public static function FetchAgents ($adb, $onlyActive = false) {
			$whereSql = '';
			if ($onlyActive) {
				$whereSql = " WHERE ag.status = 'ACTIVE'";
			}
			$result = $adb->query (
				"SELECT
       				ag.*,
       				CONCAT(user.first_name, ' ', user.last_name) AS username,
       				user.imagename
				FROM vtiger_agents ag
				LEFT JOIN vtiger_users user ON user.id = ag.agentsid
				{$whereSql}"
			);
			if ($adb->num_rows ($result) > 0) {
				$agents = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$avatar = (empty ($row['imagename'])) ? '/Image/avatar/png/man.png' : "{$_SESSION ['plat']}/user_images/{$row['imagename']}";
					$agents [] = Agents::getInstance ()
						->setId ($row['agentsid'])
						->setName ($row['agent_name'])
						->setDescription ($row['description'])
						->setUserAvatar ($avatar)
						->setUserName ($row['username'])
						->setStatus ($row['status'])
						->setPlatformInstance (self::fetchPlatformInstances ($adb, $row['agentsid']));
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($agents)) ? $agents : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $agentId
		 *
		 * @return Agents|null
		 * @throws PlatformException
		 */
		public static function getAgent ($adb, $agentId) {
			if (empty ($agentId)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT
	   				ag.*,
	   				CONCAT(user.first_name, " ", user.last_name) AS username,
	   				user.imagename
				FROM vtiger_agents ag
				LEFT JOIN vtiger_users user ON user.id = ag.agentsid
				WHERE ag.agentsid = ?',
				array ($agentId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$avatar = (empty ($row['imagename'])) ? '/Image/avatar/png/man.png' : "{$_SESSION ['plat']}/user_images/{$row['imagename']}";
				$agent = Agents::getInstance ()
					->setId ($row['agentsid'])
					->setName ($row['agent_name'])
					->setDescription ($row['description'])
					->setUserAvatar ($avatar)
					->setUserName ($row['username'])
					->setStatus ($row['status'])
					->setPlatformInstance (self::fetchPlatformInstances ($adb, $row['agentsid']));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($agent)) ? $agent : null;
		}
		
		/**
		 * @return string[]|null
		 */
		public static function getAvatarFileNames () {
			$availableAvatarFileNames = array_diff (scandir (__DIR__ . '/../avatars'), array ('..', '.'));
			return !empty ($availableAvatarFileNames) ? $availableAvatarFileNames : null;
		}

		public static function getTotalUsers (PearDatabase $adb) {
			$result = $adb->query ('SELECT COUNT(*) AS totalusers FROM vtiger_users');
			if (($result) && ($adb->num_rows ($result) > 0)) {
				$row        = $adb->fetchByAssoc ($result, -1, false);
				$totalUsers = intval ($row ['totalusers']);
			} else {
				$totalUsers = 0;
			}
			return $totalUsers;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return Users|null
		 * @throws Exception
		 */
		public static function getUser (PearDatabase $adb, $userId) {
			if (empty ($userId)) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE id=?', array ($userId));
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				throw new Exception ('El usuario solicitado no se encuentra registrado');
			}

			$row = $adb->fetchByAssoc ($result, -1, false);

			/** @var Users $focus */
			$user = new Users ();
			$user->retrieve_entity_info ($userId, 'Users');
			$user->column_fields ['default_module']    = $row ['default_module'];
			$user->column_fields ['default_operating'] = $row ['default_operating'];
			$user->column_fields ['default_home_tab']  = $row ['defaulthometab'];
			return $user;
		}

		/**
		 * @param string $platform
		 * @param string $userImageName
		 * @param string[] $availableAvatarFileNames
		 *
		 * @return string|null
		 */
		public static function getUserAvatarFileName ($platform, $userImageName, $availableAvatarFileNames) {
			if (empty ($availableAvatarFileNames)) {
				return null;
			}

			$rootFolderPath       = PlatzillaUtils::getPlatzillaRootFolderPath ();
			$userImagesFolderPath = "{$rootFolderPath}/{$platform}/user_images";
			$userImagePath        = "{$userImagesFolderPath}/{$userImageName}";
			if (!file_exists ($userImagePath)) {
				return null;
			}

			$type          = pathinfo ($userImagePath, PATHINFO_EXTENSION);
			$data          = file_get_contents ($userImagePath);
			$base64Data    = base64_encode ($data);
			$userImageData = "data:image/{$type};base64,{$base64Data}";

			$selectedAvatarFileName = null;
			foreach ($availableAvatarFileNames as $availableAvatarFileName) {
				$avatarFilePath   = realpath (__DIR__ . "/../avatars/{$availableAvatarFileName}");
				$avatarFileType   = pathinfo ($avatarFilePath, PATHINFO_EXTENSION);
				$avatarData       = file_get_contents ($avatarFilePath);
				$avatarBase64Data = base64_encode ($avatarData);
				$avatarImageData  = "data:image/{$avatarFileType};base64,{$avatarBase64Data}";
				if ($userImageData == $avatarImageData) {
					$selectedAvatarFileName = $availableAvatarFileName;
					break;
				}
			}
			return $selectedAvatarFileName;
		}

		public static function getUsers (PearDatabase $adb) {
			$result = $adb->query (
				"SELECT
					u.*,
					u2r.roleid,
					r.rolename,
					DATE_FORMAT(u.date_entered,'%d/%m/%Y') AS date_entered
				FROM
					vtiger_users u
					INNER JOIN vtiger_user2role u2r ON u2r.userid=u.id
					INNER JOIN vtiger_role r ON u2r.roleid=r.roleid
				ORDER BY
					u.id"
			);
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}

			$users = array ();
			while ($row = $adb->fetchByAssoc ($result)) {
				$row ['profileimage'] = getUserImageName ($row ['id']);
				$users []             = $row;
			}
			return $users;
		}

		/**
		 * @param string $instanceCode
		 *
		 * @return integer
		 */
		public static function getUsersLimit ($instanceCode = null) {
			if (empty ($instanceCode)) {
				return -1;
			}
			$adb = AdbManager::getInstance ()->getMasterAdb ();
			return PlatformManager::getInstance ($adb)->fetchInstanceUsersLimit ($instanceCode);
		}

		public static function getRoles (PearDatabase $adb) {
			$result = $adb->query ('SELECT * FROM vtiger_role WHERE depth>0 ORDER BY rolename');
			if ((!$result) || ($adb->num_rows ($result) == 0)) {
				return null;
			}
			$roles = array ();
			while ($row = $adb->fetchByAssoc ($result, -1, false)) {
				$roles [] = $row;
			}
			return $roles;
		}

		public static function registerInstanceUser ($instanceName, Users $user) {
			if ((empty ($instanceName)) || (empty ($user)) || (empty ($user->id))) {
				return;
			}

			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$masterAdb->pquery ('INSERT IGNORE INTO vtiger_instanceusers VALUES (?, ?)', array ($instanceName, $user->column_fields ['user_name']));

			$result = $masterAdb->pquery ('SELECT COUNT(*) AS totalusers FROM vtiger_instanceusers WHERE instancecode=?', array ($instanceName));
			if (($result) && ($masterAdb->num_rows ($result) > 0)) {
				$row        = $masterAdb->fetchByAssoc ($result, -1, false);
				$totalUsers = intval ($row ['totalusers']);
			} else {
				$totalUsers = 0;
			}
			$masterAdb->pquery ('UPDATE vtiger_instances SET activeusers=? WHERE code=?', array ($totalUsers, $instanceName));
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $record
		 * @param Agents $agent
		 * @param array $relatedInstanceCode
		 *
		 * @throws Exception
		 */
		public static function saveAgent ($adb, $record, $agent, $relatedInstanceCode) {
			if ((!$agent instanceof Agents) || (empty ($relatedInstanceCode))) {
				throw new Exception ('Imposible guardar el agente');
			}
			if (empty ($record)) {
				$adb->pquery (
					"INSERT INTO vtiger_agents (agentsid, agent_name, description, status) VALUES (?, ?, ?, ?)",
						array ($agent->getId(), $agent->getName(), $agent->getDescription(), $agent->getStatus())
				);
			} else {
				$adb->pquery (
					"UPDATE vtiger_agents SET agent_name=?, description=?, status=? WHERE agentsid=?",
						array ($agent->getName(), $agent->getDescription(), $agent->getStatus(), $agent->getId())
				);
				$adb->pquery (
					"DELETE FROM vtiger_agents2instances WHERE agentsid=?",
						array ($agent->getId())
				);
			}
			
			foreach ($relatedInstanceCode as $instanceCode) {
				if (empty ($instanceCode)) {
					continue;
				}
				$dummy = explode (';', $instanceCode);
				$adb->pquery (
					"INSERT INTO vtiger_agents2instances (agentsid, code, administrator) VALUES (?, ?, ?)",
						array ($agent->getId(), $dummy[0], $dummy [1])
				);
			}
			
		}

		public static function saveImage (PearDatabase $adb, $platform, $userId, $userImageType, $userImageData) {
			if ((empty ($userImageType)) || (empty ($userImageData))) {
				return;
			}

			$rootFolderPath = PlatzillaUtils::getPlatzillaRootFolderPath ();
			array_map ('unlink', glob ("{$rootFolderPath}/{$platform}/user_images/Avatar_{$userId}.*"));
			if ($userImageType == 'AVATAR') {
				$avatarFilePath = __DIR__ . "/../avatars/{$userImageData}";
				$imageExtension = pathinfo ($avatarFilePath, PATHINFO_EXTENSION);
				$imageContents  = file_get_contents ($avatarFilePath);
			} else {
				$imageType      = substr ($userImageData, (strpos ($userImageData, 'data:') + 5), (strpos ($userImageData, ';base64,') - 5));
				$imageExtension = substr ($imageType, (strpos ($imageType, '/') + 1));
				$imageContents  = base64_decode (str_replace (' ', '+', substr ($userImageData, (strpos ($userImageData, 'base64,') + 7))));
			}
			$tempFilePath = tempnam ('/tmp', 'user-image-');
			file_put_contents ($tempFilePath, $imageContents);
			$imageFileName    = "Avatar_{$userId}.{$imageExtension}";
			$imagesFolderPath = "{$rootFolderPath}/{$platform}/user_images";
			if (!file_exists ($imagesFolderPath)) {
				mkdir ($imagesFolderPath, 0777, true);
			}
			rename ($tempFilePath, "{$imagesFolderPath}/{$imageFileName}");
			$adb->pquery ('UPDATE vtiger_users SET imagename=? WHERE id=?', array ($imageFileName, $userId));
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $userData
		 *
		 * @return Users|null
		 */
		public static function saveUser (PearDatabase $adb, $userData) {
			if (empty ($userData)) {
				return null;
			}

			$firstName        = $userData ['firstname'];
			$lastName         = $userData ['lastname'];
			$password         = $userData ['password'];
			$passwordRepeated = $userData ['passwordrepeated'];
			$roleId           = $userData ['roleid'];
			$status           = $userData ['status'];
			$userId           = $userData ['id'];
			$userName         = $userData ['username'];

			/** @var Users $user */
			$user = new Users ();
			if (!empty ($userId)) {
				$user->retrieve_entity_info ($userId, 'Users');
				$user->mode = 'edit';
				if ((!empty ($password)) || (!empty ($passwordRepeated))) {
					$user->change_password (null, $password, false);
				}
			} else {
				$user->mode                                          = 'create';
				$user->column_fields ['user_name']                   = $userName;
				$user->column_fields ['user_password']               = $password;
				$user->column_fields ['confirm_password']            = $password;
				$user->column_fields ['email1']                      = $userName;
				$user->column_fields ['crypt_type']                  = 'PHP5.3MD5';
				$user->column_fields ['is_admin']                    = 'off';
				$user->column_fields ['theme']                       = 'centaurus';
				$user->column_fields ['language']                    = 'es_es';
				$user->column_fields ['hour_format']                 = 'am/pm';
				$user->column_fields ['start_hour']                  = '08:00';
				$user->column_fields ['activity_view']               = 'This Week';
				$user->column_fields ['lead_view']                   = 'Today';
				$user->column_fields ['internal_mailer']             = '1';
				$user->column_fields ['reminder_interval']           = '1 Minute';
				$user->column_fields ['currency_grouping_pattern']   = '123,456,789';
				$user->column_fields ['currency_decimal_separator']  = '.';
				$user->column_fields ['currency_grouping_separator'] = ',';
				$user->column_fields ['currency_symbol_placement']   = '$1.0';
			}
			$user->column_fields ['first_name'] = $firstName;
			$user->column_fields ['last_name']  = $lastName;
			$user->column_fields ['roleid']     = $roleId;
			$user->column_fields ['status']     = $status;
			$user->save ('Users');

			return $user;
		}

		public static function updateUserProfile (PearDatabase $adb, $userId, array $userData) {
			if (empty ($userData)) {
				return;
			}
			$adb->pquery (
				'UPDATE vtiger_users SET first_name=?, last_name=?, default_module=?, default_operating=?, defaulthometab=? WHERE id=?',
				array ($userData ['first_name'], $userData ['last_name'], $userData ['default_module'], $userData ['default_operating'], $userData ['default_home_tab'], $userId)
			);
		}

	}

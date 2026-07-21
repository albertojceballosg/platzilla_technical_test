<?php
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('include/utils/AdbManager.class.php');

	abstract class WebmailUtils {
		const RECORDS_PER_PAGE = 25;

		/**
		 * @param PearDatabase $adb
		 * @param ImapMailManager $imm
		 * @param array $account
		 * @param array $emailAddressesInAccountModules
		 *
		 * @throws Exception
		 */
		private static function fetchAccountReceivedEmails (PearDatabase $adb, ImapMailManager $imm, $account, $emailAddressesInAccountModules) {
			while ($message = $imm->fetchNextMessage ($account ['receivedfolder'])) {
				$from      = $message ['from'];
				$relatedTo = null;
				foreach ($emailAddressesInAccountModules as $emailAddress => $data) {
					if (trim ($from) != trim (strtolower ($emailAddress))) {
						continue;
					}
					foreach ($data as $moduleName => $entityIds) {
						foreach ($entityIds as $entityId) {
							$relatedTo [ $moduleName ][] = $entityId;
						}
					}
				}

				$result = $adb->pquery ('SELECT * FROM vtiger_emailsreceived WHERE uid=?', array ($message ['uid']));
				if ($adb->num_rows ($result) == 0) {
					$emailId = $adb->getUniqueID ('vtiger_crmentity');
					$adb->pquery (
						'INSERT INTO vtiger_crmentity (crmid, smcreatorid, modifiedby, setype, description, createdtime, viewedtime, status, version, presence, deleted, smviewer, smownerid, modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
						array ($emailId, $account ['userid'], $account ['userid'], 'emailsreceived', null, date ('Y-m-d H:i:s'), null, null, 0, 1, 0, null, $account ['userid'], date ('Y-m-d H:i:s'))
					);
					$adb->pquery (
						'INSERT INTO vtiger_emailsreceived (emailsreceivedid, subject, from_, to_, cc, bcc, maildate, body, folder, uid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
						array ($emailId, $message ['subject'], $from, $message ['to'], $message ['cc'], null, $message ['date'], $message ['body'], $account ['receivedfolder'], $message ['uid'])
					);
					$adb->pquery ('INSERT INTO vtiger_emailsreceivedcf (emailsreceivedid) VALUES (?)', array ($emailId));
				} else {
					$row     = $adb->fetchByAssoc ($result, -1, false);
					$emailId = $row ['emailsreceivedid'];
				}
				DatabaseUtils::closeResult ($result);
				$result = null;

				self::saveRelatedEntities ($adb, $emailId, 'emailsreceived', $relatedTo);
			}

			$adb->pquery ('UPDATE vtiger_webmail_users SET lastsyncedon=NOW(), lasterror=NULL WHERE userid=? AND provider=?', array ($account ['userid'], $account ['provider']));
		}

		/**
		 * @param PearDatabase $adb
		 * @param ImapMailManager $imm
		 * @param array $account
		 * @param array $emailAddressesInAccountModules
		 *
		 * @throws Exception
		 */
		private static function fetchAccountSentEmails (PearDatabase $adb, ImapMailManager $imm, $account, $emailAddressesInAccountModules) {
			while ($message = $imm->fetchNextMessage ($account ['sentfolder'])) {
				$recipients = array_unique (array_filter (array_merge (explode (',', strtolower ($message ['to'])), explode (',', strtolower ($message ['cc'])))));
				$to         = array_map ('trim', $recipients);
				$relatedTo  = null;
				foreach ($emailAddressesInAccountModules as $emailAddress => $data) {
					if (!in_array (trim (strtolower ($emailAddress)), $to)) {
						continue;
					}
					foreach ($data as $moduleName => $entityIds) {
						foreach ($entityIds as $entityId) {
							$relatedTo [ $moduleName ][] = $entityId;
						}
					}
				}

				$result = $adb->pquery ('SELECT * FROM vtiger_emailssent WHERE uid=?', array ($message ['uid']));
				if ($adb->num_rows ($result) == 0) {
					$emailId = $adb->getUniqueID ('vtiger_crmentity');
					$adb->pquery (
						'INSERT INTO vtiger_crmentity (crmid, smcreatorid, modifiedby, setype, description, createdtime, viewedtime, status, version, presence, deleted, smviewer, smownerid, modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
						array ($emailId, $account ['userid'], $account ['userid'], 'emailssent', null, date ('Y-m-d H:i:s'), null, null, 0, 1, 0, null, $account ['userid'], date ('Y-m-d H:i:s'))
					);
					$adb->pquery (
						'INSERT INTO vtiger_emailssent (emailssentid, subject, from_, to_, cc, bcc, maildate, body, folder, uid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
						array ($emailId, $message ['subject'], $message ['from'], $message ['to'], $message ['cc'], null, $message ['date'], $message ['body'], $account ['sentfolder'], $message ['uid'])
					);
					$adb->pquery ('INSERT INTO vtiger_emailssentcf (emailssentid) VALUES (?)', array ($emailId));
				} else {
					$row     = $adb->fetchByAssoc ($result, -1, false);
					$emailId = $row ['emailssentid'];
				}
				DatabaseUtils::closeResult ($result);
				$result = null;

				self::saveRelatedEntities ($adb, $emailId, 'emailssent', $relatedTo);
			}
			$adb->pquery ('UPDATE vtiger_webmail_users SET lastsyncedon=NOW(), lasterror=NULL WHERE userid=? AND provider=?', array ($account ['userid'], $account ['provider']));
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private static function fetchEmailFieldsInModule (PearDatabase $adb, $moduleName) {
			$result = $adb->pquery (
				'SELECT
					f.tablename,
					en.entityidfield,
					f.columnname
				FROM
					vtiger_field f
					INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?
					INNER JOIN vtiger_entityname en ON en.tabid=t.tabid
				WHERE
					f.uitype=13',
				array ($moduleName)
			);
			if ($adb->num_rows ($result) > 0) {
				$fields = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$fields [] = $row;
				}
			} else {
				$fields = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $fields;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private static function fetchRegisteredEmailAddressesInModule (PearDatabase $adb, $moduleName) {
			$emailFields = self::fetchEmailFieldsInModule ($adb, $moduleName);
			if (empty ($emailFields)) {
				return null;
			}

			$tableName     = null;
			$selectColumns = array ();
			$whereClauses  = array ();
			foreach ($emailFields as $emailField) {
				if (empty ($tableName)) {
					$tableName        = $emailField ['tablename'];
					$selectColumns [] = $emailField ['entityidfield'];
				}
				$selectColumns [] = $emailField ['columnname'];
				$whereClauses []  = "({$emailField ['columnname']} IS NOT NULL AND TRIM({$emailField ['columnname']})<>'')";
			}

			$selectColumns = join (', ', $selectColumns);
			$whereClauses  = join (' OR ', $whereClauses);
			$result        = $adb->query ("SELECT {$selectColumns} FROM {$tableName} WHERE {$whereClauses}");
			if ($adb->num_rows ($result) > 0) {
				$emailAddresses = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$values   = array_values ($row);
					$entityId = array_shift ($values);
					$emails   = array_filter (array_unique ($values));
					foreach ($emails as $email) {
						$emailAddresses [ $email ][] = $entityId;
					}
				}
			} else {
				$emailAddresses = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return !empty ($emailAddresses) ? $emailAddresses : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param string $providerName
		 * @param string $encryptionKey
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private static function fetchUserAccountData (PearDatabase $adb, $userId, $providerName, $encryptionKey) {
			if ((empty ($userId)) || (empty ($providerName))) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					*
				FROM
					vtiger_webmail_users ue
					INNER JOIN vtiger_webmail_providers ep ON ep.name=ue.provider
				WHERE
					ue.userid=? AND
					ue.provider=?',
				array ($userId, $providerName)
			);
			if ($adb->num_rows ($result) > 0) {
				$accountData                   = $adb->fetchByAssoc ($result, -1, false);
				$accountData ['plainpassword'] = ImapMailManager::decryptPassword ($accountData ['password'], $encryptionKey);
				$accountData ['modulenames']   = self::fetchUserAccountModuleNames ($adb, $userId, $accountData ['provider']);
			} else {
				$accountData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $accountData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		private static function fetchUserAccountModuleNames (PearDatabase $adb, $userId) {
			if (empty ($userId)) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_usersmodules WHERE userid=?', array ($userId));
			if ($adb->num_rows ($result) > 0) {
				$modules = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$modules [] = $row ['modulename'];
				}
			} else {
				$modules = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $modules;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $emailId
		 * @param string $emailType
		 * @param array $relatedEntities
		 */
		private static function saveRelatedEntities (PearDatabase $adb, $emailId, $emailType, $relatedEntities) {
			if (empty ($relatedEntities)) {
				return;
			}

			foreach ($relatedEntities as $moduleName => $entityIds) {
				foreach ($entityIds as $entityId) {
					$result = $adb->pquery (
						'SELECT * FROM vtiger_crmentityrel WHERE crmid=? AND module=? AND relcrmid=? AND relmodule=?',
						array ($emailId, $emailType, $entityId, $moduleName)
					);
					if ($adb->num_rows ($result) == 0) {
						$adb->pquery (
							'INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?, ?, ?, ?)',
							array ($emailId, $emailType, $entityId, $moduleName)
						);
					}
					DatabaseUtils::closeResult ($result);
					$result = null;
				}
			}
		}

		/**
		 * @param array $arguments
		 *
		 * @throws Exception
		 * @throws null
		 */
		private static function validateUserAccount ($arguments) {
			if (empty ($arguments)) {
				throw new Exception ('No se ha suministrado la información de la cuenta de correo');
			} else if (empty ($arguments ['emailaddress'])) {
				throw new Exception ('No se ha suministrado la dirección de correo del usuario');
			} else if (empty ($arguments ['password'])) {
				throw new Exception ('No se ha suministrado la contraseña');
			} else if (empty ($arguments ['protocol'])) {
				throw new Exception ('No se ha suministrado el protocolo del servidor de correo');
			} else if (empty ($arguments ['hostname'])) {
				throw new Exception ('No se ha suministrado el nombre del servidor');
			} else if (empty ($arguments ['port'])) {
				throw new Exception ('No se ha suministrado el puerto de conexión');
			} else if (empty ($arguments ['securitytype'])) {
				throw new Exception ('No se ha suministrado el tipo de seguridad');
			} else if (empty ($arguments ['authenticationmethod'])) {
				throw new Exception ('No se ha suministrado el mecanismo de autenticación');
			} else if (empty ($arguments ['incomingfoldername'])) {
				throw new Exception ('No se ha suministrado el nombre de la carpeta de correos entrantes');
			} else if (empty ($arguments ['outgoingfoldername'])) {
				throw new Exception ('No se ha suministrado el nombre de la carpeta de correos salientes');
			}
		}

		/**
		 * @param $emailAddress
		 *
		 * @return MailManagerConfiguration|null
		 */
		public static function detectMailServerSettings ($emailAddress) {
			if ((empty ($emailAddress)) || (filter_var ($emailAddress, FILTER_VALIDATE_EMAIL) === false)) {
				return null;
			}

			$domain = strtolower (substr ($emailAddress, (strpos ($emailAddress, '@') + 1)));

			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_providers WHERE domain=?', array ($domain));
			if ($adb->num_rows ($result) > 0) {
				$settings = $adb->fetchByAssoc ($result, -1, false);
			} else {
				$settings = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if ($settings !== null) {
				return MailManagerConfiguration::getInstance (
					$settings ['hostname'],
					$settings ['port'],
					$settings ['securitytype'],
					$settings ['authenticationmethod'],
					$settings ['protocol'],
					$settings ['usernametype']
				);
			} else {
				return MailManagerConfigurationDetector::detect ($emailAddress);
			}
		}

		/**
		 * @param string[] $mailServerSettings
		 */
		public static function saveProvider ($mailServerSettings) {
			if ((empty ($mailServerSettings)) || (!is_array ($mailServerSettings))) {
				return;
			}

			$adb    = AdbManager::getInstance ()->getMasterAdb ();
			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_providers WHERE domain=? AND protocol=?', array ($mailServerSettings ['domain'], 'imap'));
			if ($adb->num_rows ($result) == 0) {
				$adb->pquery (
					'INSERT INTO vtiger_webmail_providers (domain, protocol, hostname, port, securitytype, authenticationmethod, usernametype) VALUES (?, ?, ?, ?, ?, ?, ?)',
					array ($mailServerSettings ['domain'], 'imap', $mailServerSettings ['hostname'], $mailServerSettings ['port'], $mailServerSettings ['securitytype'], $mailServerSettings ['authenticationmethod'], $mailServerSettings ['usernametype'])
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string[] $moduleNames
		 *
		 * @return array|null
		 */
		public static function getRegisteredEmailAddressesForAccountModules (PearDatabase $adb, $moduleNames) {
			if (empty ($moduleNames)) {
				return null;
			}

			$emailAddresses = array ();
			foreach ($moduleNames as $moduleName) {
				$emailsInModule = self::fetchRegisteredEmailAddressesInModule ($adb, $moduleName);
				if (empty ($emailsInModule)) {
					continue;
				}
				foreach ($emailsInModule as $email => $entityIds) {
					foreach ($entityIds as $entityId) {
						$emailAddresses [ $email ][ $moduleName ][] = $entityId;
					}
				}
			}
			return !empty ($emailAddresses) ? $emailAddresses : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param string $encryptionKey
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getUserAccountData (PearDatabase $adb, $userId, $encryptionKey) {
			if (empty ($userId)) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_users WHERE userid=?', array ($userId));
			if ($adb->num_rows ($result) > 0) {
				$accountData = $adb->fetchByAssoc ($result, -1, false);
			} else {
				$accountData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $accountData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param string $encryptionKey
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getUserAccounts (PearDatabase $adb, $userId, $encryptionKey) {
			if (empty ($userId)) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_users WHERE userid=?', array ($userId));
			if ($adb->num_rows ($result) > 0) {
				$accounts = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['modulenames']   = self::fetchUserAccountModuleNames ($adb, $userId);
					$accounts []           = $row;
				}
			} else {
				$accounts = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $accounts;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function getUsersAccounts (PearDatabase $adb) {
			$result = $adb->pquery (
				'SELECT
					*
				FROM
					vtiger_webmail_users ue
					INNER JOIN vtiger_webmail_providers ep ON ep.name=ue.provider',
				array ()
			);
			if ($adb->num_rows ($result) > 0) {
				$accounts = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['modulenames'] = self::fetchUserAccountModuleNames ($adb, $row ['userid'], $row ['provider']);
					$accounts []         = $row;
				}
			} else {
				$accounts = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $accounts;
		}

		/**
		 * @param string $instanceName
		 *
		 * @throws null
		 */
		public static function fetchInstanceUsersEmails ($instanceName) {
			$webMailClient = null;
			require ('config.inc.php');

			$adb = AdbManager::getInstance ()->getTargetInstanceAdb ($instanceName);

			$accounts = self::getUsersAccounts ($adb);
			if (empty ($accounts)) {
				return;
			}

			foreach ($accounts as $account) {
				if (empty ($account ['modulenames'])) {
					continue;
				}

				$emailAddressesInAccountModules = self::getRegisteredEmailAddressesForAccountModules ($adb, $account ['modulenames']);
				if (empty ($emailAddressesInAccountModules)) {
					continue;
				}

				$imm = null;
				$e   = null;
				try {
					$imm      = new ImapMailManager ($account ['incominghostname'], $account ['incomingport'], $account ['incomingsecuritytype']);
					$password = ImapMailManager::decryptPassword ($account ['password'], $webMailClient ['encryptionkey']);
					$imm->login ($account ['username'], $password, $account ['incomingauthenticationmethod']);
					self::fetchAccountReceivedEmails ($adb, $imm, $account, $emailAddressesInAccountModules);
					self::fetchAccountSentEmails ($adb, $imm, $account, $emailAddressesInAccountModules);
				} catch (Exception $ie) {
					$e = $ie;
					$adb->pquery ('UPDATE vtiger_webmail_users SET lasterror=?, lastsyncedon=NOW() WHERE userid=? AND provider=?', array ($e->getMessage (), $account ['userid'], $account ['provider']));
				}
				if ($imm !== null) {
					$imm->logout ();
				}
				if ($e instanceof Exception) {
					throw $e;
				}
			}
		}

		public static function fetchUserEmails (PearDatabase $adb, $userId, $providerName, $encryptionKey) {
			$accountData = self::fetchUserAccountData ($adb, $userId, $providerName, $encryptionKey);
			if ((empty ($accountData)) || (empty ($accountData ['modulenames']))) {
				return null;
			}

			$emailAddressesInAccountModules = self::getRegisteredEmailAddressesForAccountModules ($adb, $accountData ['modulenames']);
			if (empty ($emailAddressesInAccountModules)) {
				return null;
			}

			$imm = null;
			$e   = null;
			try {
				$imm      = new ImapMailManager ($accountData ['incominghostname'], $accountData ['incomingport'], $accountData ['incomingsecuritytype']);
				$password = ImapMailManager::decryptPassword ($accountData ['password'], $encryptionKey);
				$imm->login ($accountData ['username'], $password, $accountData ['incomingauthenticationmethod']);
				self::fetchAccountReceivedEmails ($adb, $imm, $accountData, $emailAddressesInAccountModules);
				self::fetchAccountSentEmails ($adb, $imm, $accountData, $emailAddressesInAccountModules);
			} catch (Exception $ie) {
				$e = $ie;
				$adb->pquery ('UPDATE vtiger_webmail_users SET lasterror=?, lastsyncedon=NOW() WHERE userid=? AND provider=?', array ($e->getMessage (), $accountData ['userid'], $accountData ['provider']));
			}
			if ($imm !== null) {
				$imm->logout ();
			}
			if ($e instanceof Exception) {
				throw $e;
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $arguments
		 * @param array $emailRelatedModuleNames
		 * @param integer $userId
		 * @param string $encryptionKey
		 *
		 * @throws Exception
		 * @throws null
		 */
		public static function saveUserAccount (PearDatabase $adb, $arguments, $emailRelatedModuleNames, $userId, $encryptionKey) {
			self::validateUserAccount ($arguments);

			try {
				$configuration = MailManagerConfiguration::getInstance (
					$arguments ['hostname'],
					$arguments ['port'],
					$arguments ['securitytype'],
					$arguments ['authenticationmethod'],
					$arguments ['protocol'],
					$arguments ['usernametype']
				);
				$mm = MailManager::getInstance ()->setConfiguration ($configuration);
				$mm->login ($arguments ['emailaddress'], $arguments ['password']);
				$folders = $mm->getSubscribedFolders ();
				if (!in_array ($arguments ['incomingfoldername'], $folders)) {
					throw new Exception ("La carpeta {$arguments ['incomingfoldername']} no se encuentra suscrita");
				} else if (!in_array ($arguments ['outgoingfoldername'], $folders)) {
					throw new Exception ("La carpeta {$arguments ['outgoingfoldername']} no se encuentra suscrita");
				}
			} catch (Excepton $ie) {
				$e = $ie;
			} finally {
				if (isset ($imm)) {
					$imm->logout ();
				}
				if (isset ($e)) {
					throw $e;
				}
			}

//			$password = ImapMailManager::encryptPassword ($arguments ['password'], $encryptionKey);
			$password = $arguments ['password'];
			$result   = $adb->pquery ('SELECT * FROM vtiger_webmail_users WHERE userid=?', array ($userId));
			if ($adb->num_rows ($result) == 0) {
				$adb->pquery (
					'INSERT INTO vtiger_webmail_users (userid, emailaddress, password, protocol, hostname, port, securitytype, authenticationmethod, usernametype, receivedfolder, sentfolder) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($userId, $arguments ['emailaddress'], $password, $arguments ['protocol'], $arguments ['hostname'], $arguments ['port'], $arguments ['securitytype'], $arguments ['authenticationmethod'], $arguments ['usernametype'], $arguments ['incomingfoldername'], $arguments ['outgoingfoldername'])
				);
			} else {
				$account = $adb->fetchByAssoc ($result, -1, false);
				if ($account ['emailaddress'] != $arguments ['emailaddress']) {
					$lastSyncedOn = null;
					$lastError    = null;
				} else {
					$lastSyncedOn = $account ['lastsyncedon'];
					$lastError    = $account ['lasterror'];
				}
				$adb->pquery (
					'UPDATE vtiger_webmail_users SET emailaddress=?, password=?, protocol=?, hostname=?, port=?, securitytype=?, authenticationmethod=?, usernametype=?, receivedfolder=?, sentfolder=?, lastreceivedmessageuid=?, lastsentmessageuid=?, lastsyncedon=?, lasterror=? WHERE userid=?',
					array ($arguments ['emailaddress'], $password, $arguments ['protocol'], $arguments ['hostname'], $arguments ['port'], $arguments ['securitytype'], $arguments ['authenticationmethod'], $arguments ['usernametype'], $arguments ['incomingfoldername'], $arguments ['outgoingfoldername'], null, null, $lastSyncedOn, $lastError, $userId)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			$adb->pquery ('DELETE FROM vtiger_webmail_usersmodules WHERE userid=?', array ($userId));

			$mrm = ModuleRelationshipManager::getInstance ($adb);
			if (empty ($emailRelatedModuleNames)) {
				$mrm->deleteRelationships ('emailsreceived');
				$mrm->deleteRelationships ('emailssent');
			} else {
				foreach ($emailRelatedModuleNames as $moduleName) {
					$adb->pquery (
						'INSERT INTO vtiger_webmail_usersmodules (userid, modulename) VALUES (?, ?)',
						array ($userId, $moduleName)
					);

					$relationship = ModuleRelationship::getInstance ()
						->setActions (array (ModuleRelationship::ACTION_ADD, ModuleRelationship::ACTION_SELECT))
						->setFunction ('get_related_list')
						->setLabel ('Correos recibidos')
						->setLocked (true)
						->setModuleName ($moduleName)
						->setPresence (ModuleRelationship::PRESENCE_VISIBLE)
						->setRelatedModuleName ('emailsreceived');
					$mrm->saveRelationship ($relationship);
					$relationship = ModuleRelationship::getInstance ()
						->setActions (array (ModuleRelationship::ACTION_ADD, ModuleRelationship::ACTION_SELECT))
						->setFunction ('get_related_list')
						->setLabel ('Correos enviados')
						->setLocked (true)
						->setModuleName ($moduleName)
						->setPresence (ModuleRelationship::PRESENCE_VISIBLE)
						->setRelatedModuleName ('emailssent');
					$mrm->saveRelationship ($relationship);
				}
			}
		}

	}

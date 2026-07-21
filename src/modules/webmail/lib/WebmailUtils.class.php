<?php
	require_once ('include/MailManager/PlatzillaMailManager.php');
	require_once ('include/platzilla/Managers/FieldManager.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('modules/OAuth2Manager/lib/OAuth2Utils.class.php');

	use League\OAuth2\Client\Token\AccessToken;
	use Platzilla\MailManager\Account\GenericAccount;
	use Platzilla\MailManager\Account\GenericAccountException;
	use Platzilla\MailManager\Message\Message;
	use Platzilla\MailManager\Message\MessageException;
	use Platzilla\MailManager\Provider\GenericProvider;
	use Platzilla\MailManager\Provider\SmtpProvider;
	use Platzilla\MailManager\Service\MailManagerException;
	use Platzilla\MailManager\Service\MailManager;
	use Platzilla\MailManager\Service\ProviderDetector;
	use Platzilla\MailManager\Type\AuthenticationMethod;
	use Platzilla\MailManager\Utils\MailUtils;

	abstract class WebmailUtils {
		const RECORDS_PER_PAGE = 25;
		const MAXIMUM_EMAILS   = 100;

		const STATUS_ALL                = 'ALL';
		const STATUS_INCOMING           = 'INCOMING';
		const STATUS_INCOMING_RELATED   = 'INCOMING RELATED';
		const STATUS_INCOMING_UNRELATED = 'INCOMING UNRELATED';
		const STATUS_OUTGOING           = 'OUTGOING';
		const STATUS_OUTGOING_RELATED   = 'OUTGOING RELATED';
		const STATUS_OUTGOING_UNRELATED = 'OUTGOING UNRELATED';
		const STATUS_RELATED            = 'RELATED';
		const STATUS_UNRELATED          = 'UNRELATED';

		const STATUS_READ_EMAIL         = 'READ_EMAIL';
		const STATUS_UNREAD_EMAIL       = 'UNREAD_EMAIL';

		const TYPE_INCOMING = 0;
		const TYPE_OUTGOING = 1;

		const DEFAULT_MAIL = 'notificaciones@platzilla.com';

		/**
		 * @param string[] $accountData
		 *
		 * @return GenericAccount
		 */
		private static function buildMailAccount ($accountData) {
			$provider = new GenericProvider ($accountData);
			return GenericAccount::getInstance ($accountData ['emailaddress'], $provider)
				->setAccessToken (!empty ($accountData ['accesstoken']) ? new AccessToken (json_decode ($accountData ['accesstoken'], true)) : null)
				->setIncomingFolderName ($accountData ['incomingfoldername'])
				->setOutgoingFolderName ($accountData ['outgoingfoldername'])
				->setPassword ($accountData ['password']);
		}

		/**
		 * @param integer $original
		 *
		 * @return null|string
		 */
		private static function buildTimeSinceString ($original) {
			$chunks = array (
				array (60 * 60 * 24 * 365, 'año'),
				array (60 * 60 * 24 * 30, 'mes'),
				array (60 * 60 * 24 * 7, 'semana'),
				array (60 * 60 * 24, 'día'),
				array (60 * 60, 'hora'),
				array (60, 'minuto'),
			);
			$today  = time ();
			$since  = ($today - $original);
			if ($since < 0) {
				return null;
			}
			$j = count ($chunks);
			for ($i = 0; $i < $j; $i++) {
				$seconds = $chunks[ $i ][0];
				$name    = $chunks[ $i ][1];
				$count   = floor ($since / $seconds);
				if ($count != 0) {
					$print = ($count == 1) ? '1 ' . $name : (($name == 'mes') ? "$count {$name}es" : "$count {$name}s");
					if (($i + 1) < $j) {
						$secondsTwo = $chunks[ ($i + 1) ][0];
						$nameTwo    = $chunks[ ($i + 1) ][1];
						$countTwo   = floor (($since - ($seconds * $count)) / $secondsTwo);
						if ($countTwo != 0) {
							$print .= ($countTwo == 1) ? ', 1 ' . $nameTwo : ", $countTwo {$nameTwo}s";
						}
					}
					break;
				}
			}
			return isset ($print) ? $print : null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $emailAddress
		 *
		 * @return string[]|null
		 * @throws Exception
		 */
		private static function fetchContactsDataByEmail (PearDatabase $adb, $emailAddress) {
			if (empty ($emailAddress)) {
				return null;
			}

			$dummy        = explode (' ', $emailAddress);
			$emailAddress = str_replace ('<', '', str_replace ('>', '', array_pop ($dummy)));
			$result       = $adb->pquery (
				'SELECT DISTINCT 
						co.*,
						att.* 
					  FROM 
					  	vtiger_contactos co
					  LEFT JOIN vtiger_attachments att ON att.attachmentsid = co.imagen_contacto
					  WHERE 
					  email=?',
				array ($emailAddress)
			);
			if ($adb->num_rows ($result) > 0) {
				$customersData = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$customersData [] = array (
						'id'       => $row ['contactosid'],
						'fullname' => trim ("{$row ['nombre']} {$row ['apellidos']}"),
						'photo'    => (!empty ($row ['name'])) ? $row ['path'] . $row ['attachmentsid'] . '_'. $row ['name'] : 'themes/centaurus/img/avatar_2x.png',
					);
				}
			} else {
				$customersData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $customersData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $emailAddress
		 *
		 * @return string[]|null
		 * @throws Exception
		 */
		private static function fetchCustomersDataByEmail (PearDatabase $adb, $emailAddress) {
			if (empty ($emailAddress)) {
				return null;
			}

			$dummy        = explode (' ', $emailAddress);
			$emailAddress = str_replace ('<', '', str_replace ('>', '', array_pop ($dummy)));
			$result       = $adb->pquery ('SELECT * FROM vtiger_clientes WHERE e_mail=?', array ($emailAddress));
			if ($adb->num_rows ($result) > 0) {
				$customersData = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$customersData [] = array (
						'id'       => $row ['clientesid'],
						'fullname' => $row ['alias'],
					);
				}
			} else {
				$customersData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $customersData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $emailAddress
		 *
		 * @return string[]|null
		 * @throws Exception
		 */
		private static function fetchPotentialsDataByEmail (PearDatabase $adb, $emailAddress) {
			if (empty ($emailAddress)) {
				return null;
			}

			$dummy        = explode (' ', $emailAddress);
			$emailAddress = str_replace ('<', '', str_replace ('>', '', array_pop ($dummy)));
			$result       = $adb->pquery ('SELECT * FROM vtiger_potenciales_clientes WHERE e_mail=?', array ($emailAddress));
			if ($adb->num_rows ($result) > 0) {
				$customersData = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$customersData [] = array (
						'id'       => $row ['potenciales_clientesid'],
						'fullname' => $row ['alias'],
					);
				}
			} else {
				$customersData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $customersData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $emailAddress
		 *
		 * @return string[]|null
		 * @throws Exception
		 */
		private static function fetchProvidersDataByEmail (PearDatabase $adb, $emailAddress) {
			if (empty ($emailAddress)) {
				return null;
			}

			$dummy        = explode (' ', $emailAddress);
			$emailAddress = str_replace ('<', '', str_replace ('>', '', array_pop ($dummy)));
			$result       = $adb->pquery ('SELECT * FROM vtiger_proveedores WHERE email=?', array ($emailAddress));
			if ($adb->num_rows ($result) > 0) {
				$customersData = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$customersData [] = array (
						'id'       => $row ['proveedoresid'],
						'fullname' => $row ['alias'],
					);
				}
			} else {
				$customersData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $customersData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $emailAddress
		 * @param integer $userId
		 * @param string $type
		 *
		 * @return integer
		 */
		private static function fetchLastMailMessageUid (PearDatabase $adb, $emailAddress, $userId, $type) {
			if ((empty ($emailAddress)) || (empty ($userId))) {
				return 0;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_accountshistory WHERE userid=? AND emailaddress=?', array ($userId, $emailAddress));
			if ($adb->num_rows ($result) > 0) {
				$row        = $adb->fetchByAssoc ($result, -1, false);
				$messageUid = intval ($row [ $type == self::TYPE_INCOMING ? 'lastincomingmessageuid' : 'lastoutgoingmessageuid' ]);
			} else {
				$messageUid = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $messageUid;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param string $from
		 * @param string $to
		 * @param string $status
		 * @param boolean $includeBody
		 *
		 * @return string[]|null
		 */
		private static function fetchReceivedEmailsData (PearDatabase $adb, $userId, $from, $to, $status, $includeBody = true) {
			$whereClauses = array ();
			$arguments    = array ();
			if ((!empty ($from)) && (!empty ($to))) {
				$whereClauses [] = "DATE(er.maildate) BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')";
				$arguments       = array ($from, $to);
			}
			if (in_array ($status, array (self::STATUS_RELATED, self::STATUS_INCOMING_RELATED))) {
				$whereClauses [] = 'EXISTS (SELECT * FROM vtiger_crmentityrel crmer WHERE crmer.crmid=crme.crmid)';
			} else if (in_array ($status, array (self::STATUS_UNRELATED, self::STATUS_INCOMING_UNRELATED))) {
				$whereClauses [] = 'NOT EXISTS (SELECT * FROM vtiger_crmentityrel crmer WHERE crmer.crmid=crme.crmid)';
			} else if (in_array ($status, array (self::STATUS_UNREAD_EMAIL))) {
				$whereClauses [] = "er.status='{$status}'";
			}
			$whereClause = count ($whereClauses) > 0 ? 'AND ' . join (' AND ', $whereClauses) : '';

			$result = $adb->pquery (
				"SELECT
					crme.crmid,
					er.account,
					er.from_ AS sender,
					er.subject,
					er.body,
					er.maildate AS date,
					er.status AS status_email
				FROM
					vtiger_crmentity crme
					INNER JOIN vtiger_emailsreceived er ON er.emailsreceivedid=crme.crmid
				WHERE
					crme.deleted=0 AND
					crme.smownerid=?
					{$whereClause}
				ORDER BY
					date DESC",
				array_merge (array ($userId), $arguments)
			);
			$emailsData = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!$includeBody) {
						unset ($row ['body']);
					}
					$row ['type']            = self::TYPE_INCOMING;
					$row ['registeredas']    = array (
						'contacts'   => self::fetchContactsDataByEmail ($adb, $row ['sender']),
						'customers'  => self::fetchCustomersDataByEmail ($adb, $row ['sender']),
						'potentials' => self::fetchPotentialsDataByEmail ($adb, $row ['sender']),
						'providers'  => self::fetchProvidersDataByEmail ($adb, $row ['sender']),
					);
					$row ['relatedentities'] = self::fetchRelatedEntitiesData ($adb, $row ['crmid']);
					$row ['timesince']       = self::buildTimeSinceString (strtotime ($row ['date']));
					$emailsData []           = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $emailsData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $emailId
		 *
		 * @return array|null
		 */
		private static function fetchRelatedEntitiesData (PearDatabase $adb, $emailId) {
			if (empty ($emailId)) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_crmentityrel WHERE crmid=?', array ($emailId));
			if ($adb->num_rows ($result) > 0) {
				$mm   = ModuleManager::getInstance ($adb);
				$data = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$moduleName = $row ['relmodule'];
					$entity     = PlatformUtils::getCrmEntity ($adb, $moduleName, $row ['relcrmid']);
					$data []    = array (
						'id'         => $entity->id,
						'modulename' => get_class ($entity),
						'value'      => $entity->column_fields [ $mm->fetchModule ($moduleName, true)->getEntityIdentifier () ],
					);
				}
			} else {
				$data = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $data;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $domain
		 *
		 * @return GenericProvider|null
		 * @throws Exception
		 */
		private static function fetchSavedMailProvider (PearDatabase $adb, $domain) {
			if (empty ($domain)) {
				return null;
			}

			$incomingProvider = null;
			$outgoingProvider = null;
			$result           = $adb->pquery ('SELECT * FROM vtiger_webmail_providers WHERE domain=?', array ($domain));
			if ($adb->num_rows ($result) > 0) {
				$row      = $adb->fetchByAssoc ($result, -1, false);
				$provider = new GenericProvider ($row);
			} else {
				$provider = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $provider;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param string $from
		 * @param string $to
		 * @param string $status
		 * @param boolean $includeBody
		 *
		 * @return string[]|null
		 */
		private static function fetchSentEmailsData (PearDatabase $adb, $userId, $from, $to, $status, $includeBody = true) {
			$whereClauses = array ();
			$arguments    = array ();
			if ((!empty ($from)) && (!empty ($to))) {
				$whereClauses [] = "DATE(es.maildate) BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d')";
				$arguments       = array ($from, $to);
			}
			if (in_array ($status, array (self::STATUS_RELATED, self::STATUS_OUTGOING_RELATED))) {
				$whereClauses [] = 'EXISTS (SELECT * FROM vtiger_crmentityrel crmer WHERE crmer.crmid=crme.crmid)';
			} else if (in_array ($status, array (self::STATUS_UNRELATED, self::STATUS_OUTGOING_UNRELATED))) {
				$whereClauses [] = 'NOT EXISTS (SELECT * FROM vtiger_crmentityrel crmer WHERE crmer.crmid=crme.crmid)';
			}
			$whereClause = count ($whereClauses) > 0 ? 'AND ' . join (' AND ', $whereClauses) : '';

			$result = $adb->pquery (
				"SELECT
					crme.crmid,
					es.account,
					es.from_ AS sender,
					es.subject,
					es.body,
					es.maildate AS date
				FROM
					vtiger_crmentity crme
					INNER JOIN vtiger_emailssent es ON es.emailssentid=crme.crmid
				WHERE
					crme.deleted=0 AND
					crme.smownerid=?
					{$whereClause}
				ORDER BY
					date DESC",
				array_merge (array ($userId), $arguments)
			);
			$emailsData = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (!$includeBody) {
						unset ($row ['body']);
					}
					$row ['type']            = self::TYPE_OUTGOING;
					$row ['registeredas']    = array (
						'contacts'   => self::fetchContactsDataByEmail ($adb, $row ['sender']),
						'customers'  => self::fetchCustomersDataByEmail ($adb, $row ['sender']),
						'potentials' => self::fetchPotentialsDataByEmail ($adb, $row ['sender']),
						'providers'  => self::fetchProvidersDataByEmail ($adb, $row ['sender']),
					);
					$row ['relatedentities'] = self::fetchRelatedEntitiesData ($adb, $row ['crmid']);
					$row ['timesince']       = self::buildTimeSinceString (strtotime ($row ['date']));
					$emailsData []           = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $emailsData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return GenericAccount[]
		 * @throws Exception
		 */
		private static function getDefaultAccount ($adb, $userId) {
			$masterAdb = AdbManager::getInstance ()->getMasterAdb ();
			$accounts  = array ();
			$result    = $masterAdb->pquery ('SELECT * FROM vtiger_webmail_accounts WHERE emailaddress=?', array (self::DEFAULT_MAIL));
			if ($masterAdb->num_rows ($result) > 0) {
				$accountData = $masterAdb->fetchByAssoc ($result, -1, false);
				$accountData ['userid'] = $userId;
				$adb->run_insert_data('vtiger_webmail_accounts', $accountData);
				$accounts [] = self::buildMailAccount ($accountData);
			}
			return $accounts;
		}

		/**
		 * @param PearDatabase $adb
		 * @param $moduleName
		 * @param $messageUid
		 *
		 * @return CRMEntity|stdClass
		 * @throws Exception
		 */
		private static function getMailCrmEntity (PearDatabase $adb, $moduleName, $messageUid) {
			$entity = PlatformUtils::getCrmEntity ($adb, $moduleName);
			$result = $adb->pquery ("SELECT * FROM {$entity->table_name} WHERE uid=?", array ($messageUid));
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$entity->retrieve_entity_info ($row [ $entity->table_index ], $moduleName);
				$entity->id   = $row [ $entity->table_index ];
				$entity->mode = 'edit';
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $entity;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $emailAddress
		 * @param integer $userId
		 * @param integer $messageUid
		 * @param string $type
		 */
		private static function saveAccountLastMessageUid (PearDatabase $adb, $emailAddress, $userId, $messageUid, $type) {
			if ((empty ($emailAddress)) || (empty ($userId))) {
				return;
			}

			if ($type == self::TYPE_INCOMING) {
				$lastIncomingMessageUid = $messageUid;
				$lastOutgoingMessageUid = 0;
			} else {
				$lastIncomingMessageUid = 0;
				$lastOutgoingMessageUid = $messageUid;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_accountshistory WHERE userid=? AND emailaddress=?', array ($userId, $emailAddress));
			if ($adb->num_rows ($result) == 0) {
				$adb->pquery (
					'INSERT INTO vtiger_webmail_accountshistory (userid, emailaddress, lastchecked, lastincomingerrormessage, lastincomingmessageuid, lastoutgoingerrormessage, lastoutgoingmessageuid) VALUES (?, ?, ?, ?, ?, ?, ?)',
					array ($userId, $emailAddress, date ('Y-m-d H:i:s'), null, $lastIncomingMessageUid, null, $lastOutgoingMessageUid)
				);
			} else if ($type == self::TYPE_INCOMING) {
				$adb->pquery (
					'UPDATE vtiger_webmail_accountshistory SET lastchecked=?, lastincomingerrormessage=?, lastincomingmessageuid=? WHERE userid=? AND emailaddress=?',
					array (date ('Y-m-d H:i:s'), null, $lastIncomingMessageUid, $userId, $emailAddress)
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_webmail_accountshistory SET lastchecked=?, lastoutgoingerrormessage=?, lastoutgoingmessageuid=? WHERE userid=? AND emailaddress=?',
					array (date ('Y-m-d H:i:s'), null, $lastOutgoingMessageUid, $userId, $emailAddress)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Message $message
		 * @param string $moduleName
		 * @param Field $attachmentsField
		 * @param integer $entityId
		 * @param integer $userId
		 * @param array $badFileExtensions
		 */
		private static function saveMailMessageAttachments (PearDatabase $adb, $message, $moduleName, $attachmentsField, $entityId, $userId, $badFileExtensions) {
			$attachments = $message->getAttachments ();
			if (!empty ($attachments)) {
				$attachmentsData = array ();
				foreach ($attachments as $attachment) {
					$attachmentsData [] = array (
						'data'     => sprintf ('data:%s;base64,%s', $attachment->getMimeType (), base64_encode ($attachment->getData ())),
						'filename' => $attachment->getFileName (),
					);
				}
				if (isset ($attachmentsField)) {
					AttachmentsUtils::saveAttachments ($adb, $entityId, $moduleName, $attachmentsField->getId (), $userId, $attachmentsData, $badFileExtensions);
				} else {
					AttachmentsUtils::saveEntityAttachments ($adb, $entityId, $moduleName, $userId, $attachmentsData, $badFileExtensions);
				}
			} else {
				if (isset ($attachmentsField)) {
					AttachmentsUtils::deleteAttachments ($adb, $entityId, $attachmentsField->getId ());
				} else {
					AttachmentsUtils::deleteEntityAttachments ($adb, $entityId);
				}
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $accountName
		 * @param Message $message
		 * @param integer $type
		 * @param integer $userId
		 * @param array $badFileExtensions
		 */
		private static function saveValidMailMessage (PearDatabase $adb, $accountName, $message, $type, $userId, $badFileExtensions) {
			$moduleName       = $type == self::TYPE_INCOMING ? 'emailsreceived' : 'emailssent';
			$messageUid       = $message->getUid ();
			$fields           = FieldManager::getInstance ($adb)->fetchFieldsByUiType ($moduleName, FieldInterface::UI_TYPE_ATTACHMENTS, true);
			$attachmentsField = !empty ($fields) ? $fields [0] : null;

			$entity                                     = self::getMailCrmEntity ($adb, $moduleName, $messageUid);
			$entity->column_fields ['assigned_user_id'] = $userId;
			$entity->column_fields ['account']          = $accountName;
			$entity->column_fields ['bcc']              = $message->getBcc ();
			$entity->column_fields ['body']             = $message->getBody ();
			$entity->column_fields ['cc']               = $message->getCc ();
			$entity->column_fields ['maildate']         = $message->getDate ()->format ('Y-m-d');
			$entity->column_fields ['folder']           = $message->getFolderName ();
			$entity->column_fields ['from_']            = $message->getFrom ();
			$entity->column_fields ['subject']          = $message->getSubject ();
			$entity->column_fields ['to_']              = $message->getTo ();
			$entity->column_fields ['uid']              = $message->getUid ();
			$actualErrorStatus                          = $adb->dieOnError;
			$adb->setDieOnError (false);
			$entity->save ($moduleName);
			$entityId = $entity->id;

			self::saveMailMessageAttachments ($adb, $message, $moduleName, $attachmentsField, $entityId, $userId, $badFileExtensions);
			self::saveAccountLastMessageUid ($adb, $message->getAccountHolder (), $userId, $message->getUid (), $type);
			$adb->setDieOnError ($actualErrorStatus);
		}

		/**
		 * @param Message $message
		 * @param integer $type
		 *
		 * @throws Exception
		 * @throws MessageException
		 */
		private static function validateMailMessage ($message, $type) {
			if (!($message instanceof Message)) {
				throw new Exception ('El mensaje suministrado no es válido');
			} else if (!in_array ($type, array (self::TYPE_INCOMING, self::TYPE_OUTGOING))) {
				throw new Exception ('El tipo suministrado no es válido');
			} else {
				$message->validate ();
			}
		}

		/**
		 * @param array $accountData
		 *
		 * @return GenericAccount
		 */
		public static function deserializeMailAccount ($accountData) {
			return GenericAccount::jsonDeserialize ($accountData);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $emailAddress
		 * @param integer $userId
		 */
		public static function deleteMailAccount (PearDatabase $adb, $emailAddress, $userId) {
			if ((empty ($emailAddress)) || (empty ($userId))) {
				return;
			}

			$adb->pquery ('DELETE FROM vtiger_webmail_accounts WHERE userid=? AND emailaddress=?', array ($userId, $emailAddress));
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $emailAddress
		 * @param integer $userId
		 *
		 * @return null|GenericAccount
		 */
		public static function fetchMailAccount (PearDatabase $adb, $emailAddress, $userId) {
			if ((empty ($userId)) || (empty ($emailAddress))) {
				return null;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_accounts WHERE userid=? AND emailaddress=?', array ($userId, $emailAddress));
			if ($adb->num_rows ($result) > 0) {
				$accountData = $adb->fetchByAssoc ($result, -1, false);
				$account     = self::buildMailAccount ($accountData);
			} else {
				$account = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $account;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param boolean $isInstance
		 *
		 * @return GenericAccount[]
		 * @throws Exception
		 */
		public static function fetchMailAccounts (PearDatabase $adb, $userId, $isInstance = false) {
			if (empty ($userId)) {
				return null;
			}
			$accounts = array ();
			$result   = $adb->pquery ('SELECT * FROM vtiger_webmail_accounts WHERE userid=?', array ($userId));
			if ($adb->num_rows ($result) > 0) {
				while ($accountData = $adb->fetchByAssoc ($result, -1, false)) {
					$accounts [] = self::buildMailAccount ($accountData);
				}
			} else if($isInstance && $userId == 1) {
				$accounts = self::getDefaultAccount ($adb, $userId);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $accounts;
		}

		/**
		 * @param PearDatabase $adb
		 * @param GenericAccount $account
		 * @param integer $userId
		 * @param string $encryptionKey
		 * @param string $mask
		 *
		 * @return null|array
		 * @throws Exception
		 */
		public static function fetchMailMessages (PearDatabase $adb, $account, $userId, $encryptionKey, $mask = null) {
			$token    = $account->getAccessToken ();
			$provider = $account->getProvider ();
			if (($provider->getIncomingAuthenticationMethod () == AuthenticationMethod::OAUTH2) && ($token instanceof AccessToken) && ($token->hasExpired ())) {
				$token = OAuth2Utils::refreshToken ($adb, $provider->getIncomingHostName (), $token);
				if (!($token instanceof \League\OAuth2\Client\Token\AccessTokenInterface)) {
					throw new Exception ('Imposible obtener un token de acceso');
				}
				$account->setAccessToken ($token);
			}

			$lastIncomingMessageUid = self::fetchLastMailMessageUid ($adb, $account->getEmailAddress (), $userId, self::TYPE_INCOMING);
			$lastOutgoingMessageUid = self::fetchLastMailMessageUid ($adb, $account->getEmailAddress (), $userId, self::TYPE_OUTGOING);

			return MailManager::getInstance ()->fetchMessages ($account, $lastIncomingMessageUid, $lastOutgoingMessageUid, $encryptionKey, self::MAXIMUM_EMAILS, $mask);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param integer $page
		 * @param boolean $isInstance
		 * @param integer $recordsPerPage
		 *
		 * @return array|null
		 */
		public static function fetchPagedMailAccounts (PearDatabase $adb, $userId, $page, $isInstance = false, $recordsPerPage = self::RECORDS_PER_PAGE) {
			if (
				(empty ($userId)) ||
				(($page !== null) && ((!is_numeric ($page)) || ($page <= 0))) ||
				(($recordsPerPage !== null) && ((!is_numeric ($recordsPerPage)) || ($recordsPerPage <= 0)))
			) {
				return null;
			}
			$defaultEmail = self::DEFAULT_MAIL;
			$where        = ($isInstance) ? "AND emailaddress !='{$defaultEmail}'" : '';

			$startRecord = (($page - 1) * $recordsPerPage);
			$result      = $adb->pquery (
				"SELECT
					*
				FROM
					vtiger_webmail_accounts
					CROSS JOIN (SELECT COUNT(*) AS __total_records__ FROM vtiger_webmail_accounts WHERE userid=? {$where}) AS total
				WHERE
					userid=? 
					{$where}
				ORDER BY
					emailaddress
				LIMIT
					?, ?",
				array ($userId, $userId, $startRecord, $recordsPerPage)
			);
			if ($adb->num_rows ($result) > 0) {
				$totalRecords = 0;
				$accounts     = array ();
				while ($accountData = $adb->fetchByAssoc ($result, -1, false)) {
					$totalRecords = ($page !== null) ? intval ($accountData ['__total_records__']) : ($totalRecords + 1);
					$accounts []  = self::buildMailAccount ($accountData);
				}
				$endRecord  = count ($accounts);
				$totalPages = ceil ($totalRecords / $recordsPerPage);
			} else {
				$accounts     = null;
				$totalRecords = 0;
				$endRecord    = 0;
				$totalPages   = 0;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return array (
				'startRecord'  => $startRecord,
				'endRecord'    => $endRecord,
				'totalRecords' => $totalRecords,
				'page'         => intval ($page),
				'totalPages'   => $totalPages,
				'records'      => $accounts,
			);
		}

		/**
		 * @param GenericAccount $account
		 * @param string $encryptionKey
		 *
		 * @return null|string[]
		 * @throws MailManagerException
		 */
		public static function getMailAccountSubscribedFolders ($account, $encryptionKey) {
			$provider = $account->getProvider ();
			if ($provider->getIncomingAuthenticationMethod () == AuthenticationMethod::OAUTH2) {
				$accessToken = $account->getAccessToken ()->getToken ();
			} else {
				$accessToken = MailUtils::decrypt ($account->getPassword (), $encryptionKey);
			}
			return MailManager::getInstance ()->getSubscribedFolders ($account->getEmailAddress (), $accessToken, $provider);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param string $status
		 *
		 * @return integer
		 */
		public static function getMailCountByStatus ($adb, $userId, $status = self::STATUS_UNREAD_EMAIL) {
			$results = $adb->pquery(
				'SELECT DISTINCT 
						er.emailsreceivedid 
					  FROM 
					  	vtiger_emailsreceived  er
					  INNER JOIN vtiger_webmail_accounts wa ON wa.emailaddress = er.to_
					  WHERE 
					  	status=?
					  	AND wa.userid=?',
				array ($status, $userId)
			);
			return $adb->getRowCount ($results);
		}

		/**
		 * @param string $emailAddress
		 * @param string $accessToken
		 * @param string[] $providerData
		 *
		 * @return null|string[]
		 * @throws MailManagerException
		 */
		public static function getMailSubscribedFolders ($emailAddress, $accessToken, $providerData) {
			$provider = new GenericProvider ($providerData, true, false);
			return MailManager::getInstance ()->getSubscribedFolders ($emailAddress, $accessToken, $provider);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $emailAddress
		 *
		 * @return GenericProvider|null
		 */
		public static function getMailProvider (PearDatabase $adb, $emailAddress) {
			if ((empty ($emailAddress)) || (filter_var ($emailAddress, FILTER_VALIDATE_EMAIL) === false)) {
				return null;
			}

			$domain   = strtolower (substr ($emailAddress, (strpos ($emailAddress, '@') + 1)));
			$provider = self::fetchSavedMailProvider ($adb, $domain);
			if (empty ($provider)) {
				$provider = ProviderDetector::detect ($emailAddress);
			}

			return $provider;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string[][] $accountData
		 * @param string $encryptionKey
		 * @param integer $userId
		 * @param string $localHostName
		 * @param boolean $isInstance
		 *
		 * @throws GenericAccountException
		 */
		public static function saveMailAccount (PearDatabase $adb, $accountData, $encryptionKey, $userId, $localHostName, $isInstance = false) {
			$provider = new GenericProvider ($accountData ['provider']);
			if ($provider->getIncomingAuthenticationMethod () == AuthenticationMethod::OAUTH2) {
				$password        = null;
				$accessToken     = new AccessToken (json_decode (base64_decode ($accountData ['accesstokendata']), true));
				$accessTokenData = json_encode ($accessToken->jsonSerialize ());
			} else {
				$password        = MailUtils::encrypt ($accountData ['plainpassword'], $encryptionKey);
				$accessToken     = null;
				$accessTokenData = null;
			}

			$account = GenericAccount::getInstance ($accountData ['emailaddress'], $provider)
				->setAccessToken ($accessToken)
				->setIncomingFolderName ($accountData ['incomingfoldername'])
				->setOutgoingFolderName ($accountData ['outgoingfoldername'])
				->setPassword ($password);
			MailManager::getInstance ()->testAccount ($account, $encryptionKey, $localHostName);
			if ($isInstance) {
				$adb->pquery ('DELETE FROM vtiger_webmail_accounts WHERE userid=? AND emailaddress=?', array ($userId, self::DEFAULT_MAIL));
			}
			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_accounts WHERE userid=? AND emailaddress=?', array ($userId, $accountData ['emailaddress']));
			if ($adb->num_rows ($result) == 0) {
				$adb->pquery (
					'INSERT INTO vtiger_webmail_accounts (userid, emailaddress, password, accesstoken, incomingservice, incominghostname, incomingport, incomingsecuritytype, incomingauthenticationmethod, incomingusernametype, outgoingservice, outgoinghostname, outgoingport, outgoingsecuritytype, outgoingauthenticationmethod, outgoingusernametype, incomingfoldername, outgoingfoldername) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($userId, $accountData ['emailaddress'], $password, $accessTokenData, $provider->getIncomingService (), $provider->getIncomingHostName (), $provider->getIncomingPort (), $provider->getIncomingSecurityType (), $provider->getIncomingAuthenticationMethod (), $provider->getIncomingUserNameType (), $provider->getOutgoingService (), $provider->getOutgoingHostName (), $provider->getOutgoingPort (), $provider->getOutgoingSecurityType (), $provider->getOutgoingAuthenticationMethod (), $provider->getOutgoingUserNameType (), $account->getIncomingFolderName (), $account->getOutgoingFolderName ())
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_webmail_accounts SET password=?, accesstoken=?, incomingservice=?, incominghostname=?, incomingport=?, incomingsecuritytype=?, incomingauthenticationmethod=?, incomingusernametype=?, outgoingservice=?, outgoinghostname=?, outgoingport=?, outgoingsecuritytype=?, outgoingauthenticationmethod=?, outgoingusernametype=?, incomingfoldername=?, outgoingfoldername=? WHERE userid=? AND emailaddress=?',
					array ($password, $accessTokenData, $provider->getIncomingService (), $provider->getIncomingHostName (), $provider->getIncomingPort (), $provider->getIncomingSecurityType (), $provider->getIncomingAuthenticationMethod (), $provider->getIncomingUserNameType (), $provider->getOutgoingService (), $provider->getOutgoingHostName (), $provider->getOutgoingPort (), $provider->getOutgoingSecurityType (), $provider->getOutgoingAuthenticationMethod (), $provider->getOutgoingUserNameType (), $account->getIncomingFolderName (), $account->getOutgoingFolderName (), $userId, $accountData ['emailaddress'])
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $emailAddress
		 * @param integer $userId
		 * @param string $errorMessage
		 * @param string $type
		 */
		public static function saveMailAccountLastErrorMessage (PearDatabase $adb, $emailAddress, $userId, $errorMessage, $type) {
			if ((empty ($emailAddress)) || (empty ($userId))) {
				return;
			}

			if ($type == self::TYPE_INCOMING) {
				$lastIncomingErrorMessage = $errorMessage;
				$lastOutgoingErrorMessage = null;
			} else {
				$lastIncomingErrorMessage = null;
				$lastOutgoingErrorMessage = $errorMessage;
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_accountshistory WHERE userid=? AND emailaddress=?', array ($userId, $emailAddress));
			if ($adb->num_rows ($result) == 0) {
				$adb->pquery (
					'INSERT INTO vtiger_webmail_accountshistory (userid, emailaddress, lastchecked, lastincomingerrormessage, lastoutgoingerrormessage) VALUES (?, ?, ?, ?, ?)',
					array ($userId, $emailAddress, date ('Y-m-d H:i:s'), $lastIncomingErrorMessage, $lastOutgoingErrorMessage)
				);
			} else if ($type == self::TYPE_INCOMING) {
				$adb->pquery (
					'UPDATE vtiger_webmail_accountshistory SET lastchecked=?, lastincomingerrormessage=? WHERE userid=? AND emailaddress=?',
					array (date ('Y-m-d H:i:s'), $lastIncomingErrorMessage, $userId, $emailAddress)
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_webmail_accountshistory SET lastchecked=?, lastoutgoingerrormessage=? WHERE userid=? AND emailaddress=?',
					array (date ('Y-m-d H:i:s'), $lastOutgoingErrorMessage, $userId, $emailAddress)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $accountName
		 * @param Message $message
		 * @param integer $type
		 * @param integer $userId
		 * @param array $badFileExtensions
		 *
		 * @throws Exception
		 * @throws MessageException
		 */
		public static function saveMailMessage (PearDatabase $adb, $accountName, $message, $type, $userId, $badFileExtensions) {
			self::validateMailMessage ($message, $type);
			self::saveValidMailMessage ($adb, $accountName, $message, $type, $userId, $badFileExtensions);
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $accountName
		 * @param Message[] $messages
		 * @param integer $type
		 * @param integer $userId
		 * @param array $badFileExtensions
		 *
		 * @throws Exception
		 * @throws MessageException
		 */
		public static function saveMailMessages (PearDatabase $adb, $accountName, $messages, $type, $userId, $badFileExtensions) {
			if ((empty ($accountName)) || (empty ($messages))) {
				return;
			}
			foreach ($messages as $message) {
				self::validateMailMessage ($message, $type);
			}
			usort ($messages, function (Message $messageA, Message $messageB) {
				if ($messageA->getUid () < $messageB->getUid ()) {
					return -1;
				} else if ($messageA->getUid () > $messageB->getUid ()) {
					return 1;
				} else {
					return 0;
				}
			});
			foreach ($messages as $message) {
				self::saveValidMailMessage ($adb, $accountName, $message, $type, $userId, $badFileExtensions);
			}
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $domain
		 * @param string[] $providerData
		 *
		 * @throws Exception
		 */
		public static function saveMailProvider (PearDatabase $adb, $domain, $providerData) {
			if (empty ($domain)) {
				throw new Exception ('No se ha suministrado el dominio');
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_webmail_providers WHERE domain=?', array ($domain));
			if ($adb->num_rows ($result) == 0) {
				$adb->pquery (
					'INSERT INTO vtiger_webmail_providers (domain, incomingservice, incominghostname, incomingport, incomingsecuritytype, incomingauthenticationmethod, incomingusernametype, outgoingservice, outgoinghostname, outgoingport, outgoingsecuritytype, outgoingauthenticationmethod, outgoingusernametype) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
					array ($domain, $providerData ['incomingservice'], $providerData ['incominghostname'], $providerData ['incomingport'], $providerData ['incomingsecuritytype'], $providerData ['incomingauthenticationmethod'], $providerData ['incomingusernametype'], $providerData ['outgoingservice'], $providerData ['outgoinghostname'], $providerData ['outgoingport'], $providerData ['outgoingsecuritytype'], $providerData ['outgoingauthenticationmethod'], $providerData ['outgoingusernametype'])
				);
			} else {
				$adb->pquery (
					'UPDATE vtiger_webmail_providers SET incomingservice=?, incominghostname=?, incomingport=?, incomingsecuritytype=?, incomingauthenticationmethod=?, incomingusernametype=?, outgoingservice=?, outgoinghostname=?, outgoingport=?, outgoingsecuritytype=?, outgoingauthenticationmethod=?, outgoingusernametype=? WHERE domain=?',
					array ($providerData ['incomingservice'], $providerData ['incominghostname'], $providerData ['incomingport'], $providerData ['incomingsecuritytype'], $providerData ['incomingauthenticationmethod'], $providerData ['incomingusernametype'], $providerData ['outgoingservice'], $providerData ['outgoinghostname'], $providerData ['outgoingport'], $providerData ['outgoingsecuritytype'], $providerData ['outgoingauthenticationmethod'], $providerData ['outgoingusernametype'], $domain)
				);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		/**
		 * @param PearDatabase $adb
		 * @param GenericAccount $account
		 * @param Message $message
		 * @param string $encryptionKey
		 * @param string $localHostName
		 *
		 * @throws Exception
		 * @throws MessageException
		 * @throws \Platzilla\MailManager\MailException
		 */
		public static function sendMailMessage (PearDatabase $adb, $account, $message, $encryptionKey, $localHostName) {
			if (!($message instanceof Message)) {
				throw new Exception ('No se ha suministrado un mensaje válido');
			} else if (!($account instanceof GenericAccount)) {
				throw new Exception ('No se ha suministrado una cuenta de correo válida');
			} else {
				$message->validate ();
			}

			$token    = $account->getAccessToken ();
			$provider = $account->getProvider ();
			if (($provider->getIncomingAuthenticationMethod () == AuthenticationMethod::OAUTH2) && ($token instanceof AccessToken) && ($token->hasExpired ())) {
				$token = OAuth2Utils::refreshToken ($adb, $provider->getIncomingHostName (), $token);
				if (!($token instanceof \League\OAuth2\Client\Token\AccessTokenInterface)) {
					throw new Exception ('Imposible obtener un token de acceso');
				}
				$account->setAccessToken ($token);
			}

			MailManager::getInstance ()->sendMessage ($account, $message, $encryptionKey, $localHostName);
		}

		/**
		 * @param GenericAccount $account
		 * @param string $encryptionKey
		 * @param string $localHostName
		 *
		 * @throws Exception
		 * @throws MailManagerException
		 */
		public static function testMailAccount ($account, $encryptionKey, $localHostName) {
			if (!($account instanceof GenericAccount)) {
				throw new Exception ('No se ha suministrado una cuenta de correo válida');
			}

			MailManager::getInstance ()->testAccount ($account, $encryptionKey, $localHostName);
		}

		/**
		 * @param array $providerData
		 *
		 * @return boolean
		 */
		public static function testMailProvider ($providerData) {
			$provider = new GenericProvider ($providerData);
			return ProviderDetector::test ($provider);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 * @param boolean $includeBody
		 * @param boolean $includeAttachments
		 *
		 * @return string[]|null
		 */
		public static function fetchEmailData (PearDatabase $adb, $entityId, $includeBody = true, $includeAttachments = true) {
			$result = $adb->pquery ('SELECT * FROM vtiger_crmentity WHERE crmid=?', array ($entityId));
			if ($adb->num_rows ($result) > 0) {
				$row        = $adb->fetchByAssoc ($result, -1, false);
				$moduleName = $row ['setype'];
			} else {
				$moduleName = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if ((empty ($moduleName)) || (!in_array ($moduleName, array ('emailsreceived', 'emailssent')))) {
				return null;
			}

			$entity = PlatformUtils::getCrmEntity ($adb, $moduleName);
			$result = $adb->pquery ("SELECT * FROM {$entity->table_name} WHERE {$entity->table_index}=?", array ($entityId));
			if ($adb->num_rows ($result) > 0) {
				$row       = $adb->fetchByAssoc ($result, -1, false);
				$emailData = array (
					'id'      => $row [ $entity->table_index ],
					'account' => $row ['account'],
					'bcc'     => $row ['bcc'],
					'cc'      => $row ['cc'],
					'date'    => $row ['maildate'],
					'from'    => $row ['from_'],
					'subject' => $row ['subject'],
					'to'      => $row ['to_'],
				);
				if ($includeBody) {
					if (preg_match ('/<body[^>]*>(.*?)<\/body>/is', $row ['body'], $matches)) {
						$emailData ['body'] = $matches [1];
					} else {
						$emailData ['body'] = $row ['body'];
					}
				}
				if ($includeAttachments) {
					$emailData ['attachments'] = AttachmentsUtils::fetchEntityAttachments ($adb, $entityId);
				}
				//email will be open them it´s a read email
				if ($moduleName == 'emailsreceived') {
					$adb->pquery ('UPDATE vtiger_emailsreceived SET status=? WHERE emailsreceivedid=?', array (self::STATUS_READ_EMAIL, $entityId));
				}
			} else {
				$emailData = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $emailData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param array|null $filters
		 * @param boolean $includeBody
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchEmailsData (PearDatabase $adb, $userId, $filters = null, $includeBody = true) {
			$from   = !empty ($filters ['from']) ? $filters ['from'] : null;
			$status = !empty ($filters ['status']) ? $filters ['status'] : self::STATUS_ALL;
			$to     = !empty ($filters ['to']) ? $filters ['to'] : null;

			if (in_array ($status, array (self::STATUS_ALL, self::STATUS_INCOMING, self::STATUS_INCOMING_RELATED, self::STATUS_INCOMING_UNRELATED, self::STATUS_RELATED, self::STATUS_UNRELATED, self::STATUS_UNREAD_EMAIL))) {
				$receivedEmailsData = self::fetchReceivedEmailsData ($adb, $userId, $from, $to, $status, $includeBody);
			} else {
				$receivedEmailsData = array ();
			}

			if (in_array ($status, array (self::STATUS_ALL, self::STATUS_OUTGOING, self::STATUS_OUTGOING_RELATED, self::STATUS_OUTGOING_UNRELATED, self::STATUS_RELATED, self::STATUS_UNRELATED))) {
				$sentEmailsData = self::fetchSentEmailsData ($adb, $userId, $from, $to, $status, $includeBody);
			} else {
				$sentEmailsData = array ();
			}

			$emailsData = array_merge ($receivedEmailsData, $sentEmailsData);
			usort ($emailsData, function ($emailDataA, $emailDataB) {
				$dateA = date_create ($emailDataA ['date']);
				$dateB = date_create ($emailDataB ['date']);
				if ($dateA < $dateB) {
					return 1;
				} else if ($dateA > $dateB) {
					return -1;
				} else {
					return 0;
				}
			});
			return $emailsData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $entityId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchRelatedEmailsData (PearDatabase $adb, $entityId) {
			if (empty ($entityId)) {
				return null;
			}

			$result = $adb->pquery (
				'SELECT
					crme.crmid,
					es.account,
					es.from_ AS sender,
					es.subject,
					es.body,
					es.maildate AS date
				FROM
					vtiger_crmentity crme
					INNER JOIN vtiger_crmentityrel crmer ON crmer.crmid=crme.crmid AND crmer.relcrmid=?
					INNER JOIN vtiger_emailssent es ON es.emailssentid=crme.crmid
				WHERE
					crme.deleted=0
				UNION
				SELECT
					crme.crmid,
					er.account,
					er.from_ AS sender,
					er.subject,
					er.body,
					er.maildate AS date
				FROM
					vtiger_crmentity crme
					INNER JOIN vtiger_crmentityrel crmer ON crmer.crmid=crme.crmid AND crmer.relcrmid=?
					INNER JOIN vtiger_emailsreceived er ON er.emailsreceivedid=crme.crmid
				WHERE
					crme.deleted=0
				ORDER BY
					date',
				array ($entityId, $entityId)
			);
			$emailsData = array ();
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['type']            = self::TYPE_OUTGOING;
					$row ['registeredas']    = array (
						'contacts'   => self::fetchContactsDataByEmail ($adb, $row ['sender']),
						'customers'  => self::fetchCustomersDataByEmail ($adb, $row ['sender']),
						'potentials' => self::fetchPotentialsDataByEmail ($adb, $row ['sender']),
						'providers'  => self::fetchProvidersDataByEmail ($adb, $row ['sender']),
					);
					$row ['relatedentities'] = self::fetchRelatedEntitiesData ($adb, $row ['crmid']);
					$row ['timesince']       = self::buildTimeSinceString (strtotime ($row ['date']));
					$emailsData []           = $row;
				}
			}
			usort ($emailsData, function ($emailDataA, $emailDataB) {
				$dateA = date_create ($emailDataA ['date']);
				$dateB = date_create ($emailDataB ['date']);
				if ($dateA < $dateB) {
					return -1;
				} else if ($dateA > $dateB) {
					return 1;
				} else {
					return 0;
				}
			});
			DatabaseUtils::closeResult ($result);
			$result = null;

			return $emailsData;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $accountHolder
		 * @param string[] $emailData
		 * @param integer $userId
		 * @param string $encryptionKey
		 * @param string $localHostName
		 *
		 * @throws Exception
		 * @throws MessageException
		 */
		public static function sendEmailData (PearDatabase $adb, $accountHolder, $emailData, $userId, $encryptionKey, $localHostName) {
			$account = self::fetchMailAccount ($adb, $accountHolder, $userId);
			if (empty ($account)) {
				throw new Exception ('El remitente suministrado no tiene una cuenta de correo asociada');
			}

			$message = Message::getInstance ($accountHolder)
				->setBcc ($emailData ['bcc'])
				->setBody ($emailData ['body'])
				->setCc ($emailData ['cc'])
				->setDate (date_create ())
				->setSubject ($emailData ['subject'])
				->setTo ($emailData ['to']);
			$message->validate ();

			self::sendMailMessage ($adb, $account, $message, $encryptionKey, $localHostName);
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $emailId
		 * @param integer[] $relatedEntityIds
		 *
		 * @throws Exception
		 */
		public static function relateEmail (PearDatabase $adb, $emailId, $relatedEntityIds) {
			if (empty ($emailId)) {
				throw new Exception ('No se ha suministrado el identificador del mensaje', 400);
			} else if ((empty ($relatedEntityIds)) || (!is_array ($relatedEntityIds))) {
				throw new Exception ('No se ha suministrado los identidicadores de los registros a asociar', 400);
			}

			$result = $adb->pquery ('SELECT * FROM vtiger_crmentity WHERE crmid=? AND setype IN (?, ?)', array ($emailId, 'emailssent', 'emailsreceived'));
			if ($adb->num_rows ($result) > 0) {
				$row        = $adb->fetchByAssoc ($result, -1, false);
				$moduleName = $row ['setype'];
			} else {
				$moduleName = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (empty ($moduleName)) {
				throw new Exception ('El identificador suministrado no es un email registrado');
			}

			foreach ($relatedEntityIds as $relatedEntityId) {
				$result = $adb->pquery ('SELECT * FROM vtiger_crmentityrel WHERE crmid=? AND relcrmid=?', array ($emailId, $relatedEntityId));
				$exists = ($adb->num_rows ($result) > 0);
				DatabaseUtils::closeResult ($result);
				$result = null;

				if (!$exists) {
					$adb->pquery (
						'INSERT IGNORE INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule)
						SELECT ?, ?, crme.crmid, crme.setype FROM vtiger_crmentity crme WHERE crmid=?',
						array ($emailId, $moduleName, $relatedEntityId)
					);
				}
			}
		}

	}

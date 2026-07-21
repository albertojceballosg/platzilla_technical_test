<?php
	require_once ('include/platzilla/Managers/ParleyManager.php');
	require_once ('include/platzilla/Objects/FieldInterface.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/CommonUtils.php');
	require_once ('modules/instancesdatasharing/lib/DataSharingUtils.class.php');
	require_once ('modules/panelusuarios/lib/UsersHelper.class.php');

	abstract class NotificationHelper {

		private static function fetchContactsToChat (PearDatabase $adb, &$usersToChat) {
			$result = $adb->query (
				'SELECT
						c.*,
						a.*
					FROM
						vtiger_contactos c
						INNER JOIN vtiger_crmentity crme ON crme.crmid=c.contactosid AND crme.deleted=0
						LEFT JOIN vtiger_attachments a ON a.attachmentsid=c.imagen_contacto'
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$contactImage = '../themes/centaurus/img/avatar_2x.png';
					if (!empty ($row ['imagen_contacto'])) {
						$contactImage = $row ['path'] . $row ['attachmentsid'] . '_' . $row ['name'];
						if (!file_exists ($contactImage)) {
							$contactImage = '../themes/centaurus/img/avatar_2x.png';
						}
					}
					$usersToChat[] = array (
						'id'    => $row ['contactosid'],
						'name'  => ucwords ($row ['nombre'] . ' ' . $row ['apellidos']),
						'image' => $contactImage,
						'type'  => 'Contacto',
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		private static function fetchCustomersToChat (PearDatabase $adb, &$usersToChat) {
			$result = $adb->query (
				"SELECT
						c.*,
						a.*
					FROM
						vtiger_clientes c
						INNER JOIN vtiger_crmentity crme ON crme.crmid=c.clientesid AND crme.deleted=0
						LEFT JOIN vtiger_attachments a ON a.attachmentsid = c.logotipo_
					WHERE
						c.nombre_comercial IS NOT NULL AND
						c.nombre_comercial<>''"
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$contactImage = '../themes/centaurus/img/avatar_2x.png';
					if (!empty ($row ['imagen_contacto'])) {
						$contactImage = $row ['path'] . $row ['attachmentsid'] . '_' . $row ['name'];
						if (!file_exists ($contactImage)) {
							$contactImage = '../themes/centaurus/img/avatar_2x.png';
						}
					}
					$usersToChat[] = array (
						'id'    => $row ['clientesid'],
						'name'  => ucwords ($row ['nombre_comercial']),
						'image' => $contactImage,
						'type'  => 'Cliente',
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
		}

		private static function fetchUserIdToShare (PearDatabase $adb, $userEmail) {
			$result = $adb->pquery ('SELECT * FROM vtiger_contactos WHERE email=?', array ($userEmail));
			if ($adb->num_rows ($result) > 0) {
				$row    = $adb->fetchByAssoc ($result, -1, false);
				$userId = $row ['contactosid'];
			} else {
				$result = $adb->pquery ('SELECT * FROM vtiger_clientes WHERE e_mail=?', array ($userEmail));
				if ($adb->num_rows ($result) > 0) {
					$row    = $adb->fetchByAssoc ($result, -1, false);
					$userId = $row ['clientesid'];
				} else {
					$userId = null;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $userId;
		}

		private static function getUserIdsByRoleWhereClause (PearDatabase $adb, $currentUser) {
			$userIds = array ();
			$result  = $adb->pquery (
				'SELECT
					u2r.userid
				FROM
					vtiger_user2role u2r
					LEFT JOIN vtiger_role r ON r.roleid=u2r.roleid
				WHERE
					r.parentrole LIKE ?',
				array ("{$currentUser->roleid}%")
			);
			if ($adb->num_rows ($result) == 0) {
				$whereClause = "={$currentUser->id}";
			} else {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ((is_admin ($currentUser)) || ($row ['userid'] != 1)) {
						$userIds [] = $row ['userid'];
					}
				}
				if (!empty ($userIds)) {
					$dummy       = join ("','", $userIds);
					$whereClause = " IN ('{$dummy}')";
				} else {
					$whereClause = "={$currentUser->id}";
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $whereClause;
		}

		private static function hadParleyHistory (PearDatabase $adb, $moduleName, $recordId, $relatedUsers, $search) {
			$recordInWhere = ($search == 'recordid') ? 'sourcerecord' : 'recordid';
			$result = $adb->pquery (
				"SELECT * FROM vtiger_parley WHERE module=? AND {$recordInWhere}=? AND usersid IN ({$relatedUsers}) ORDER BY parleyid DESC LIMIT 1",
				array ($moduleName, $recordId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row         = $adb->fetchByAssoc ($result, -1, false);
				$localRecord = $row [ $search ];
			} else {
				$localRecord = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $localRecord;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $recordId
		 * @param string $module
		 *
		 * @return boolean
		 */
		public static function checkForAnswers ($adb, $recordId, $module) {
			$result     = $adb->pquery (
				'SELECT
					p.usersid
 				FROM
 					vtiger_parley p
 					INNER JOIN vtiger_parley_history ph ON ph.parleyid=p.parleyid
					INNER JOIN vtiger_parley2users pu ON pu.parleyid=ph.parleyid AND pu.usersfrom=p.usersid
				WHERE
					p.recordid=? AND
					p.module=?',
				array ($recordId, $module)
			);
			$hasAnswers = ($adb->num_rows ($result) > 0);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $hasAnswers;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $record
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchActiveUserByRecord (PearDatabase $adb, $record) {
			$users  = array ();
			$result = $adb->pquery (
				'SELECT DISTINCT
				  	pu.usersid,
				  	pu.usersfrom
				FROM
					vtiger_parley2users pu
					INNER JOIN vtiger_parley p ON p.parleyid = pu.parleyid
				WHERE
					p.recordid=?
				ORDER BY
					p.parleyid DESC',
				array ($record)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$users [] = $row ['usersid'];
					$users [] = $row ['usersfrom'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $users;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $record
		 * @param string $moduleName
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchRelatedUserByRecord (PearDatabase $adb, $record, $moduleName) {
			$userIds = array ();
			$result  = $adb->pquery (
				'SELECT
					f.columnname,
					e.*
				FROM
					vtiger_field f
					INNER JOIN vtiger_entityname e ON e.tabid = f.tabid
				WHERE
					e.modulename=? AND
					f.uitype=?',
				array ($moduleName, FieldInterface::UI_TYPE_MODULE_REFERENCE)
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$userIdsResult = $adb->query ("SELECT {$row ['columnname']} AS fieldvalue FROM {$row ['tablename']} WHERE {$row ['entityidcolumn']}={$record}");
					if ($adb->num_rows ($userIdsResult) > 0) {
						$userIdsRow = $adb->fetchByAssoc ($userIdsResult, -1, false);
						$userIds [] = $userIdsRow ['fieldvalue'];
					}
					DatabaseUtils::closeResult ($userIdsResult);
					$userIdsResult = null;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $userIds;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $platform
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchUserToChat (PearDatabase $adb, $platform) {
			$usersToChat = array ();
			if (DataSharingUtils::hasAvailableContacts ($adb)) {
				self::fetchContactsToChat ($adb, $usersToChat);
			}

			if (DataSharingUtils::hasAvailableCustomers ($adb)) {
				self::fetchCustomersToChat ($adb, $usersToChat);
			}
			$users = UserManager::getInstance ($adb, $platform)->fetchUsers ();
			foreach ($users as $user) {
				$usersToChat[] = array (
					'id'    => $user->getId (),
					'name'  => ucwords ($user->getFirstName () . ' ' . $user->getLastName ()),
					'image' => (empty ($user->getImageUri ())) ? '../themes/centaurus/img/avatar_2x.png' : $user->getImageUri (),
					'type'  => 'Usuario',
				);
			}
			return $usersToChat;
		}

		/**
		 * @return array
		 */
		public static function getInitialParameters () {
			$parameters = array ();
			if ((isset ($default_timezone)) && (function_exists ('date_default_timezone_set'))) {
				date_default_timezone_set ($default_timezone);
			} else {
				date_default_timezone_set ('UTC');
			}

			$today                      = date ('Y-m-d h:i:s');
			$parameters ['todayTime']   = strtotime ($today);
			$parameters ['lastWeek']    = (time () - (7 * 24 * 60 * 60));
			$parameters ['lastMonth']   = (time () - (30 * 24 * 60 * 60));
			$parameters ['lastThMonth'] = (time () - (90 * 24 * 60 * 60));

			$objectDate = new DateTime();
			$objectDate->modify ('-30 day');
			$parameters ['dateFrom'] = $objectDate->format ('Y-m-d');
			$objectDate              = new DateTime();
			$objectDate->modify ('-3 month');
			$parameters ['dateThMonthFrom'] = $objectDate->format ('Y-m-d');
			$objectDate                     = new DateTime();
			$objectDate->modify ('+1 day');
			$parameters ['dateTo'] = $objectDate->format ('Y-m-d');
			return $parameters;
		}

		/**
		 * @param PearDatabase $adb
		 * @param $usersId
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getParleyModules ($adb, $usersId) {
			$modules = array ();
			$result  = $adb->pquery (
				'SELECT
					p.module,
					t.tablabel
				FROM
					vtiger_parley p
					LEFT JOIN vtiger_tab t ON t.name=p.module
				WHERE
					usersid=? AND
					module IS NOT NULL AND
					module<>?
				GROUP BY
					module
				ORDER BY
					module ASC',
				array ($usersId, 'NULL')
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$modules [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $modules;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $recordId
		 * @param string $typeShare
		 *
		 * @return string|null
		 * @throws Exception
		 */
		public static function getContactToShare ($adb, $recordId, $typeShare) {
			if ($typeShare == 'Contacto') {
				$result = $adb->pquery ('SELECT * FROM vtiger_contactos WHERE contactosid=?', array ($recordId));
				if ($adb->num_rows ($result) > 0) {
					$row          = $adb->fetchByAssoc ($result, -1, false);
					$emailAddress = $row ['email'];
				} else {
					$emailAddress = null;
				}
			} else {
				$result = $adb->pquery ('SELECT * FROM vtiger_clientes WHERE clientesid=?', array ($recordId));
				if ($adb->num_rows ($result) > 0) {
					$row          = $adb->fetchByAssoc ($result, -1, false);
					$emailAddress = $row ['e_mail'];
				} else {
					$emailAddress = null;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $emailAddress;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $email
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public static function getContactInstances ($adb, $email) {
			$sql    = 'SELECT instancecode FROM vtiger_instanceusers WHERE username=?';
			$result = $adb->pquery ($sql, array ($email));
			if ($adb->num_rows ($result) > 0) {
				$row          = $adb->fetchByAssoc ($result, -1, false);
				$instanceCode = $row ['instancecode'];
			} else {
				$instanceCode = false;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $instanceCode;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $userName
		 *
		 * @return integer
		 * @throws Exception
		 */
		public static function getUsersToShare ($adb, $userName) {
			$user = 0;
			$result = $adb->pquery ('SELECT * FROM vtiger_users WHERE user_name=?', array ($userName));
			if ($adb->num_rows ($result) > 0) {
				$row      = $adb->fetchByAssoc ($result, -1, false);
				$user = $row ['id'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $user;
		}

		public static function logToFile ($filename, $msg, $arr) {
			$filename = '../' . $filename;
			$fd       = fopen ($filename, 'a');
			$str      = '[' . date ('Y/m/d h:i:s', mktime ()) . '] ' . $msg;
			fwrite ($fd, $str . "\n");
			if (is_array ($arr)) {
				foreach ($arr as $key => $value) {
					$str = $key . ': ' . $value;
					fwrite ($fd, $str . '\n');
				}
			}
			fclose ($fd);
		}

		/**
		 * @param $original
		 *
		 * @return boolean|string
		 */
		public static function timeSince ($original) {
			$chunks = array (
				array (60 * 60 * 24 * 365, 'año'),
				array (60 * 60 * 24 * 30, 'mes'),
				array (60 * 60 * 24 * 7, 'sem'),
				array (60 * 60 * 24, 'd'),
				array (60 * 60, 'h'),
				array (60, 'min'),
			);
			$today  = time ();
			$since  = ($today - $original);
			if ($since < 0) {
				return false;
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
		 * @param array $dataParley
		 *
		 * @return boolean
		 */
		public static function shareParleyHistory ($adb, array $dataParley) {
			$message      = $dataParley ['message'];
			$usersTo      = $dataParley ['userid'];
			$src          = $dataParley ['src'];
			$record       = $dataParley ['record'];
			$sourceRecord = $dataParley ['sourceRecord'];
			$module       = $dataParley ['module'];
			$title        = $dataParley ['parleyTitle'];
			$usersFrom    = $dataParley ['recordShare'];
			$whomShare    = $dataParley ['whomShare'];
			$time         = time ();

			$objParleyManager = ParleyManager::getInstance ($adb);
			$objParley        = $objParleyManager->saveParley (
				Parley::getInstance ()
					->setTitle ($title)
					->setName ($whomShare)
					->setModuleName ($module)
					->setRecordId ($record)
					->setSourceRecord ($sourceRecord)
					->setTime ($time)
					->setUsersId ($usersFrom)
			);

			$idParley = $objParley->getId ();
			if ($idParley == 0) {
				return false;
			}

			$objParleyManager->saveParleyHistory (
				ParleyHistories::getInstance ()
					->setParleyId ($idParley)
					->setMessageTime ($time)
					->setMessage ($message)
					->setAttached (null)
					->setUsersId ($usersFrom)
					->setUsersAvatar ($src)
					->setParleyStatus (1)
			);

			if (!empty ($usersFrom)) {
				ParleyManager::getInstance ($adb)->shareParley (
					ParleyToUsers::getInstance ()
						->setParleyId ($idParley)
						->setUsersId ($usersTo)
						->setUsersName ($whomShare)
						->setUsersFrom ($usersFrom)
						->setJoinTime ($time)
				);
			}
			return true;
		}

		/**
		 * @param PearDatabase $adb
		 * @param array $dataParley
		 *
		 * @return boolean
		 * @throws Exception
		 */
		public static function saveParleyFromRecord ($adb, array $dataParley) {
			$title          = '';
			$message        = $dataParley ['message'];
			$name           = $dataParley ['name'];
			$usersFrom      = $dataParley ['userid'];
			$usersEmail     = $dataParley ['userEmail'];
			$src            = $dataParley ['src'];
			$record         = $dataParley ['record'];
			$module         = $dataParley ['module'];
			$usersTo        = $dataParley ['recordShare'];
			$typeShare      = $dataParley ['typeShare'];
			$whomShare      = $dataParley ['whomShare'];
			$sourceInstance = $dataParley ['sourceinstancecode'];
			$relatedUsers   = (!empty($dataParley ['relatedUsers'])) ? "{$dataParley ['relatedUsers']},{$usersFrom}" : $usersFrom;
			$newChat        = $dataParley ['newChat'];
			$time           = time ();
			if (!empty ($dataParley ['parleyTitle'])) {
				$title = $dataParley ['parleyTitle'];
			}
			$sourceRecord   = ($newChat) ? null : self::hadParleyHistory ($adb, $module, $record, $relatedUsers, 'sourcerecord');
			$objParleyManager = ParleyManager::getInstance ($adb);
			$objParley        = $objParleyManager->saveParley (
				Parley::getInstance ()
					->setTitle ($title)
					->setName ($name)
					->setModuleName ($module)
					->setRecordId ($record)
					->setSourceRecord ($sourceRecord)
					->setTime ($time)
					->setUsersId ($usersFrom)
			);

			$idParley = $objParley->getId ();
			if ($idParley == 0) {
				return false;
			}

			$objParleyManager->saveParleyHistory (
				ParleyHistories::getInstance ()
					->setParleyId ($idParley)
					->setMessageTime ($time)
					->setMessage ($message)
					->setAttached (null)
					->setUsersId ($usersFrom)
					->setUsersAvatar ($src)
					->setParleyStatus (1)
			);

			if (!empty ($usersTo)) {
				$objParleyManager->shareParley (
					ParleyToUsers::getInstance ()
						->setParleyId ($idParley)
						->setUsersId ($usersTo)
						->setUsersName ($whomShare)
						->setUsersFrom ($usersFrom)
						->setJoinTime ($time)
				);
			}

			if ((!empty ($typeShare)) && ($typeShare != 'Users')) {
				$contactToShare = self::getContactToShare ($adb, $usersTo, $typeShare);
				$dataSharing    = array (
					'comments'           => $message,
					'modulename'         => $module,
					'recipients'         => array ($usersTo),
					'recipienttype'      => ($typeShare == 'Contacto') ? DataSharingRequest::RECIPIENT_TYPE_CONTACT : DataSharingRequest::RECIPIENT_TYPE_CUSTOMER,
					'recordids'          => array ($record),
					'ruleid'             => 'FULL',
					'sourceinstancecode' => $sourceInstance,
					'userid'             => $usersFrom,
				);

				if ((!empty ($contactToShare)) && ($contactToShare != 'null')) {
					$adbMaster = AdbManager::getInstance ()->getMasterAdb ();
					$code      = self::getContactInstances ($adbMaster, $contactToShare);
					if ($code) {
						$instanceAdb    = AdbManager::getInstance ()->getTargetInstanceAdb (trim ($code));
						$usersToShare   = self::getUsersToShare ($instanceAdb, $contactToShare);
						$usersFromShace = self::fetchUserIdToShare ($instanceAdb, $usersEmail);
						$targetRecord   = ($newChat) ? null : self::hadParleyHistory ($instanceAdb, $module, $record, $relatedUsers, 'recordid');
						if ($usersToShare && $usersFromShace) {
							$dataParley ['userid']       = $usersToShare;
							$dataParley ['src']          = $src;
							$dataParley ['name']         = $whomShare;
							$dataParley ['recordShare']  = $usersFromShace;
							$dataParley ['whomShare']    = $name;
							$dataParley ['sourceRecord'] = $record;
							$dataParley ['record']       = $targetRecord;
							self::shareParleyHistory ($instanceAdb, $dataParley);
						}
						if (empty ($targetRecord)) {
							DataSharingUtils::sendRequest ($adb, $dataSharing);
						}
					} else {
						//Send email to invited
						DataSharingUtils::sendRequest ($adb, $dataSharing);
					}
				}
			}
			return true;
		}

		/**
		 * @param PearDatabase $adb
		 * @param integer $recordId
		 * @param string $module
		 * @param User $current_user
		 * @param integer $minTime
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getParleyFromRecord ($adb, $recordId, $module, $current_user, $minTime = 0) {
			$parleys  = array ();
			$dataUser = '';
			$joinUser = '';

			$hasAnswers = self::checkForAnswers ($adb, $recordId, $module);
			if ($hasAnswers) {
				$dataUser = ", pu.usersname, DATE_FORMAT(pu.datecreated, '%d-m-%Y') AS datereply";
				$joinUser = 'LEFT JOIN vtiger_parley2users pu ON pu.usersfrom=ph.usersid AND pu.parleyid=ph.parleyid';
			}
			if ($minTime != 0) {
				$whereClause = ' AND  ph.messagetime >= ' . $minTime;
			} else {
				$whereClause = '';
			}

			$result = $adb->pquery (
				"SELECT
					p.*,
					DATE_FORMAT(p.datecreated, '%d-%m-%Y') AS dateparley,
					ph.message,
					ph.messagetime
 					{$dataUser}
 				FROM
 					vtiger_parley p
					LEFT JOIN vtiger_parley_history ph ON ph.parleyid = p.parleyid
					{$joinUser}
				WHERE
					p.recordid=? AND
					p.module=? AND
					p.usersid=?
					{$whereClause}
				ORDER BY
					p.parleyid DESC ",
				array ($recordId, $module, self::getUserIdsByRoleWhereClause ($adb, $current_user))
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['time_since'] = self::timeSince ($row ['messagetime']);
					if (isset ($row ['usersname'])) {
						$row ['parleyname'] = $row ['usersname'];
					}
					if (!file_exists ($row ['usersavatar'])) {
						$row ['usersavatar'] = '../themes/centaurus/img/avatar_2x.png';
					}

					$parleys [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $parleys;
		}

		/**
		 * @param PearDatabase $adb
		 * @param $current_user
		 * @param array $data
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function searchParleyByWhere ($adb, $current_user, $data) {
			$parleys     = array ();
			$where       = '';
			$minTime     = isset($data['minTime']) ? $data['minTime'] : null;
			$theModule   = isset($data['theModule']) ? $data['theModule'] : null;
			$searchText  = isset($data['searchText']) ? $data['searchText'] : null;
			$searchField = isset($data['searchField']) ? $data['searchField'] : null;
			$from        = isset($data['dateFrom']) ? $data['dateFrom'] : null;
			$to          = isset($data['dateTo']) ? $data['dateTo'] : null;
			$recordId    = isset($data['recordId']) ? $data['recordId'] : null;

			$whereUser = self::getUserIdsByRoleWhereClause ($adb, $current_user);

			if (!empty ($recordId) && $recordId != 'null') {
				$where .= " AND p.recordid = {$recordId}";
			} else {
				$where .= ' AND p.recordid IS NOT NULL';
			}
			if ((!empty ($from)) && (!empty ($to))) {
				$minTime = strtotime ($from);
				$maxTime = strtotime ($to);
			} else {
				$maxTime = 0;
			}
			$where .= ($maxTime == 0) ? "  AND  ph.messagetime >= {$minTime}" : "  AND  ph.messagetime BETWEEN {$minTime} AND {$maxTime}";
			if (!empty ($theModule)) {
				$where .= " AND p.module ='{$theModule}'";
			}

			if ((!empty ($searchText)) && (!empty ($searchField))) {
				$where .= " AND {$searchField} LIKE '%{$searchText}%' ";
			}

			$result = $adb->query (
				"SELECT
					p.*,
					DATE_FORMAT(p.datecreated, '%d-%m-%Y') AS dateparley,
					ph.message,
					ph.messagetime,
					ph.usersavatar,
					DATE_FORMAT(pu.datecreated, '%d-m-%Y') AS datereply,
					t.tablabel,
					pu.usersid AS userto,
					pu.usersfrom
 				FROM
 					vtiger_parley p
 					INNER JOIN vtiger_tab t ON t.name = p.module
					INNER JOIN vtiger_parley_history ph ON ph.usersid  = p.usersid AND ph.parleyid = p.parleyid
					INNER JOIN vtiger_parley2users pu   ON pu.usersfrom = p.usersid AND pu.parleyid = p.parleyid
				WHERE
					p.usersid {$whereUser}
					{$where}
				UNION
				SELECT
					p.*,
					DATE_FORMAT(p.datecreated, '%d-%m-%Y') AS dateparley,
					ph.message,
					ph.messagetime,
					ph.usersavatar,
					DATE_FORMAT(pu.datecreated, '%d-m-%Y') AS datereply,
					t.tablabel,
					pu.usersid AS userto,
					pu.usersfrom
 				FROM
 					vtiger_parley2users pu
 					INNER JOIN vtiger_parley p  ON p.parleyid = pu.parleyid
 					INNER JOIN vtiger_tab t ON t.name = p.module
					INNER JOIN vtiger_parley_history ph ON ph.usersid = pu.usersfrom AND ph.parleyid = pu.parleyid
				WHERE
					pu.usersid {$whereUser}
					{$where}"
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$row ['time_since'] = self::timeSince ($row ['messagetime']);
					if (isset($row ['usersname'])) {
						$row ['parleyname'] = $row ['usersname'];
					}
					if (!file_exists ($row ['usersavatar'])) {
						$row ['usersavatar'] = '../themes/centaurus/img/avatar_2x.png';
					}
					$parleys [] = $row;
				}
			}
			uasort (
				$parleys,
				function ($idA, $idB) {
					return ($idA ['parleyid'] < $idB ['parleyid']);
				}
			);
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $parleys;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $moduleName
		 *
		 * @return string
		 * @throws Exception
		 */
		public static function getFieldsByModule (PearDatabase $adb, $moduleName) {
			$fields = array ();
			$result = $adb->pquery (
				'SELECT
					f.tablename,
					f.columnname,
					f.fieldlabel
				FROM
					vtiger_field f
					LEFT JOIN vtiger_tab t ON t.tabid=f.tabid
				WHERE
					t.name=? AND
					f.uitype IN (?, ?, ?, ?, ?) AND
					f.typeofdata LIKE ?
				ORDER BY
					f.fieldlabel ASC',
				array ($moduleName, FieldInterface::UI_TYPE_EMAIL, FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_TEXTAREA, '2', '19', '%M%')
			);

			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$fields [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return json_encode ($fields);
		}

		/**
		 * @param PearDatabase $adb
		 * @param $fieldData
		 * @param $searchText
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getRecordsInModule (PearDatabase $adb, $fieldData, $searchText) {
			$records        = array ();
			$arrayDataField = explode ('@', $fieldData);
			$idField        = str_replace ('vtiger_', '', $arrayDataField[0]);
			$idField .= 'id';

			$result = $adb->query ("SELECT DISTINCT {$arrayDataField[1]}, {$idField} FROM {$arrayDataField[0]} WHERE {$arrayDataField[1]} LIKE '%{$searchText}%'");
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$records [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $records;
		}

		/**
		 * @param PearDatabase $adb
		 * @param string $idEmail
		 * @param string $record
		 * @param string $moduleName
		 *
		 * @return stdClass
		 */
		public static function setArchiveEmail (PearDatabase $adb, $idEmail, $record, $moduleName) {
			$result = $adb->pquery ('SELECT * FROM vtiger_crmentity WHERE crmid=? AND setype IN (?, ?)', array ($idEmail, 'emailssent', 'emailsreceived'));
			if ($adb->num_rows ($result) > 0) {
				$row       = $adb->fetchByAssoc ($result, -1, false);
				$emailType = $row ['setype'];
			} else {
				$emailType = null;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;

			if (!empty ($emailType)) {
				$adb->pquery (
					'INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?, ?, ?, ?)',
					array ($idEmail, $emailType, $record, $moduleName)
				);
				$message = json_encode (array ('result' => 'El correo ha sido archivado correctamente!'));
			} else {
				$message = json_encode (array ('result' => 'Imposible archivar el correo!'));
			}
			return $message;
		}

		/**
		 * @param PearDatabase $adb
		 * @param $current_user
		 * @param $data
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function getEmailsRelatedEntities (PearDatabase $adb, $current_user, $data) {
			$entities  = array ();
			$where     = '';
			$uId       = $current_user->id;
			$myUsers   = UsersHelper::getUsers ($adb);
			$dayFrom   = $data ['emailFrom'];
			$dayUntil  = $data ['dateTo'];
			$theModule = $data ['emailModule'];
			if (!empty ($theModule)) {
				$where .= " AND crm.relmodule = '{$theModule}'";
			}
			if ((!empty ($dayFrom)) && (!empty ($dayUntil))) {
				$where .= " AND ( (DATE(er.maildate) BETWEEN STR_TO_DATE('{$dayFrom}','%Y-%m-%d') AND STR_TO_DATE('{$dayUntil}','%Y-%m-%d')) OR (DATE(es.maildate) BETWEEN STR_TO_DATE('{$dayFrom}','%Y-%m-%d') AND STR_TO_DATE('{$dayUntil}','%Y-%m-%d')) )";
			}
			$result = $adb->query (
				"SELECT DISTINCT
					crm.*,
					er.uid AS recived_id,
					er.from_,
					er.subject AS recived_subject,
					er.body AS recived_body,
					er.maildate AS recived_dt,
					es.subject AS send_subject,
					es.body AS send_body,
					es.maildate AS send_dt,
					es.uid AS send_id,
					ent.createdtime,
					wu.fullname
				FROM
					vtiger_crmentityrel crm
					LEFT JOIN vtiger_emailsreceived er ON er.emailsreceivedid=crm.crmid
					LEFT JOIN vtiger_emailssent es ON es.emailssentid=crm.crmid
					LEFT JOIN vtiger_crmentity ent ON ent.crmid=er.emailsreceivedid OR ent.crmid=es.emailssentid
					LEFT JOIN vtiger_webmail_users wu ON wu.email=es.from_ OR er.to_ LIKE CONCAT('%', wu.email, '%')
				WHERE
					crm.module IN ('emailsreceived', 'emailssent')
					AND wu.userid={$uId}
					{$where}
				ORDER BY
					recived_dt DESC,
					send_dt DESC"
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['module'] == 'emailssent') {
						$row ['usersavatar'] = $myUsers[0]['profileimage'];
						$row ['time_since']  = self::timeSince (strtotime ($row ['send_dt']));
					} else {
						$row ['usersavatar'] = '../themes/centaurus/img/avatar_2x.png';
						$row ['time_since']  = self::timeSince (strtotime ($row ['recived_dt']));
					}
					$entities [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $entities;
		}

		/**
		 * @param PearDatabase $adb
		 * @param Users $currentUser
		 * @param array $filters
		 *
		 * @return array
		 * @throws Exception
		 */
		public static function fetchNonRelatedEmails (PearDatabase $adb, $currentUser, $filters) {
			$entities = array ();
			$myUsers  = UsersHelper::getUsers ($adb);
			$dayFrom  = $filters ['emailFrom'];
			$dayUntil = $filters ['dateTo'];

			$whereClauses = array (
				'crme.setype IN (?, ?)',
				'crme.smownerid=?',
				'NOT EXISTS (SELECT * FROM vtiger_crmentityrel crmer WHERE crmer.crmid=crme.crmid)',
			);
			$arguments = array ('emailsreceived', 'emailssent', $currentUser->id);
			if ((!empty ($dayFrom)) && (!empty ($dayUntil))) {
				$filterWhereClauses = array (
					"(DATE(er.maildate) BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d'))",
					"(DATE(es.maildate) BETWEEN STR_TO_DATE(?, '%Y-%m-%d') AND STR_TO_DATE(?, '%Y-%m-%d'))",
				);
				$filterWhereClause = join (' OR ', $filterWhereClauses);
				$whereClauses [] = "({$filterWhereClause})";
				$arguments = array_merge ($arguments, array ($dayFrom, $dayUntil, $dayFrom, $dayUntil));
			}
			$whereClause = join (' AND ', $whereClauses);
			$result = $adb->pquery (
				"SELECT DISTINCT
					crme.*,
					er.emailsreceivedid AS recived_id,
					er.from_,
					er.subject AS recived_subject,
					er.body AS recived_body,
					er.maildate AS recived_dt,
					es.subject AS send_subject,
					es.body AS send_body,
					es.maildate AS send_dt,
					es.emailssentid AS send_id
				FROM
					vtiger_crmentity crme
					LEFT JOIN vtiger_emailsreceived er ON er.emailsreceivedid=crme.crmid
					LEFT JOIN vtiger_emailssent es ON es.emailssentid=crme.crmid
				WHERE
					{$whereClause}
				GROUP BY
					er.uid,
					es.uid
				ORDER BY
					recived_dt DESC,
					send_dt DESC",
				$arguments
			);
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if ($row ['setype'] == 'emailssent') {
						$row ['usersavatar'] = $myUsers[0]['profileimage'];
						$row ['time_since']  = self::timeSince (strtotime ($row ['send_dt']));
					} else {
						$row ['usersavatar'] = '../themes/centaurus/img/avatar_2x.png';
						$row ['time_since']  = self::timeSince (strtotime ($row ['recived_dt']));
					}
					$entities [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $entities;
		}

		/**
		 * @param integer[] $processedEntityIds
		 * @param string $moduleName
		 * @param PearDatabase $sourceAdb
		 * @param PearDatabase $targetAdb
		 */
		public static function updateParley ($processedEntityIds, $moduleName, $sourceAdb, $targetAdb) {
			if (empty ($processedEntityIds)) {
				return;
			}

			foreach ($processedEntityIds as $sourceEntityId => $targetEntityId) {
				$targetAdb->pquery (
					'UPDATE vtiger_parley SET recordid=?  WHERE sourcerecord=? AND module=? AND recordid IS NULL ',
					array ($targetEntityId, $sourceEntityId, $moduleName)
				);
				$sourceAdb->pquery (
					'UPDATE vtiger_parley SET sourcerecord=?  WHERE recordid=? AND module=? AND sourcerecord IS NULL ',
					array ($targetEntityId, $sourceEntityId, $moduleName)
				);
			}
		}

	}

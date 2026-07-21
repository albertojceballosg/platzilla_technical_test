<?php
	require_once ('include/platzilla/Objects/Parley.php');
	require_once ('include/platzilla/Objects/ParleyToUsers.php');
	require_once ('include/platzilla/Objects/ParleyHistories.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	class ParleyManager {
		/** @var ParleyManager[]|null */
		private static $INSTANCES = null;

		/** @var PearDatabase */
		private $adb;

		public function __construct (PearDatabase $adb) {
			$this->adb = $adb;
		}

		/**
		 * @param ParleyHistories $chat
		 *
		 * @return integer
		 * @throws Exception
		 */
		public function getAllNewParley ($chat) {
			$total  = 0;
			$result = $this->adb->pquery (
				'SELECT
					COUNT(*) AS totalparley
 				FROM
 					vtiger_parley_history
 					INNER JOIN vtiger_parley2users ON vtiger_parley2users.parleyid=vtiger_parley_history.parleyid
 					INNER JOIN vtiger_parley ON vtiger_parley.parleyid=vtiger_parley_history.parleyid
				WHERE
					vtiger_parley2users.usersid=? AND
					vtiger_parley_history.status=? AND
					vtiger_parley.recordid IS NOT NULL',
				array ($chat->getUsersId (), 1)
			);
			if ($this->adb->num_rows ($result) > 0) {
				$row   = $this->adb->fetchByAssoc ($result, -1, false);
				$total = intval ($row ['totalparley']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return $total;
		}

		/**
		 * @param Parley $chat
		 *
		 * @return Parley
		 */
		public function saveParley ($chat) {
			if ((empty ($chat)) || (!($chat instanceof Parley))) {
				$chat->setId (0);
				return $chat;
			}

			$this->adb->pquery (
				'INSERT INTO vtiger_parley (parleytitle, parleyname, module, recordid, sourcerecord, parleytime,usersid) VALUES (?, ?, ?, ?, ?, ?, ?)',
				array ($chat->getTitle (), $chat->getName (), $chat->getModuleName (), $chat->getRecordId (), $chat->getSourceRecord (), $chat->getTime (), $chat->getUsersId ())
			);
			$chat->setId ($this->adb->getLastInsertID ());
			return $chat;
		}

		/**
		 * @param ParleyHistories $chatHistory
		 *
		 * @return ParleyHistories
		 */
		public function saveParleyHistory ($chatHistory) {
			if ((empty ($chatHistory)) || (!($chatHistory instanceof ParleyHistories))) {
				return $chatHistory;
			}

			$this->adb->pquery (
				'INSERT INTO vtiger_parley_history (parleyid, messagetime, message, attached,usersid, usersavatar, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
				array ($chatHistory->getParleyId (), $chatHistory->getMessageTime (), $chatHistory->getMessage (), $chatHistory->getAttached (), $chatHistory->getUsersId (), $chatHistory->getUsersAvatar (), $chatHistory->getParleyStatus ())
			);
			return $chatHistory;
		}

		/**
		 * @param ParleyHistories $chat
		 *
		 * @return integer
		 */
		public function setLookedParley ($chat) {
			if ((empty ($chat)) || (!($chat instanceof ParleyHistories))) {
				return 0;
			}
			$this->adb->pquery (
				'UPDATE
					vtiger_parley_history
					INNER JOIN vtiger_parley2users ON vtiger_parley2users.parleyid=vtiger_parley_history.parleyid
				SET
					status=?
				WHERE
					vtiger_parley2users.usersid=?',
				array ('0', $chat->getUsersId ())
			);
			return 1;
		}

		/**
		 * @param ParleyToUsers $chatToShare
		 *
		 * @return ParleyToUsers
		 */
		public function shareParley ($chatToShare) {
			if ((empty ($chatToShare)) || (!($chatToShare instanceof ParleyToUsers))) {
				return $chatToShare;
			}

			$this->adb->pquery (
				'INSERT INTO vtiger_parley2users (parleyid, usersid, usersname, usersfrom,jointime) VALUES (?, ?, ?, ?, ?)',
				array ($chatToShare->getParleyId (), $chatToShare->getUsersId (), $chatToShare->getUsersName (), $chatToShare->getUsersFrom (), $chatToShare->getJoinTime ())
			);
			return $chatToShare;
		}

		/**
		 * @param PearDatabase $adb
		 *
		 * @return ParleyManager
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

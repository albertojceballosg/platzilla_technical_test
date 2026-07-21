<?php

	class NotificationsManager {
		private static $INSTANCE = null;
		private        $adb;

		protected function __construct () {
			require_once ('include/database/PearDatabase.php');
			global $adb;
			if (($adb) && ($adb instanceof PearDatabase)) {
				$this->adb = $adb;
			} else {
				$this->adb = PearDatabase::getInstance ();
			}
		}

		private function getProtocol () {
			if (strpos ($_SERVER ['SERVER_PROTOCOL'], '/') !== false) {
				$serverProtocol = explode ('/', $_SERVER ['SERVER_PROTOCOL']);
				return strtolower ($serverProtocol [0]);
			} else {
				return strtolower ($_SERVER ['SERVER_PROTOCOL']);
			}
		}

		private function buildUrl ($invitationId) {
			$protocol       = $this->getProtocol ();
			$host           = rtrim ((isset ($_SERVER ['HTTP_HOST'])) ? $_SERVER ['HTTP_HOST'] : $_SERVER ['SERVER_NAME'], '/');
			$docRoot        = rtrim ($_SERVER ['DOCUMENT_ROOT'], '/');
			$platzillaRoot  = realpath (__DIR__ . '/../../../');
			$docRootURIPart = strpos ($docRoot, $platzillaRoot) !== 0 ? '/' . rtrim (str_replace ($docRoot, '', $platzillaRoot), '/') : '';
			$instance       = md5 ("[{$_SESSION ['plat']}]");
			$payload        = md5 ("[$invitationId]");
			return "{$protocol}://{$host}{$docRootURIPart}/index.php?module=store&action=invitation&instance=$instance&invitation=$payload";
		}

		private function getInvitation ($invitationId) {
			/** @var invitations $invitation */
			$invitation = CRMEntity::getInstance ('invitations');
			$invitation->retrieve_entity_info ($invitationId, 'invitations');
			return $invitation;
		}

		/**
		 * @param $invitation
		 *
		 * @return Users|stdClass
		 */
		private function getUser ($invitation) {
			/** @var Users $user */
			$user = CRMEntity::getInstance ('Users');
			$user->retrieve_entity_info ($invitation->column_fields ['assigned_user_id'], 'Users');
			return $user;
		}

		private function getUserLanguage (Users $user) {
			$languageId = 585;

			if ($user->column_fields ['language'] == 'en_us') {
				$languageId = 586;
			} else if (($user->column_fields ['language'] == 'pt_pt') || ($user->column_fields ['language'] == 'pt_br')) {
				$languageId = 587;
			}

			return $languageId;
		}

		private function getEventId ($eventCode) {
			$result = $this->adb->pquery ('SELECT eme.eventid FROM vtiger_emailmanager_events eme WHERE eme.code=?', array ($eventCode));
			if ($this->adb->num_rows ($result) == 0) {
				return null;
			} else {
				$row = $this->adb->fetch_array ($result);
				return $row ['eventid'];
			}
		}

		private function sendNotification ($to, $language, $eventId, $variables) {
			require_once ('modules/emailmanager/EmailManager.inc.php');
			return EmailManager::getInstance ()->addSender (
				'Platzilla',
				'no_reply@platzilla.com'
			)->send (
				$to,
				$language,
				$eventId,
				$variables
			);
		}

		public function sendInvitation ($invitationId) {
			$eventCode  = 'INVITATION_CREATED';
			$invitation = $this->getInvitation ($invitationId);
			$guest      = $invitation->column_fields ['guest'];
			$user       = $this->getUser ($invitation);
			$language   = $this->getUserLanguage ($user);
			$eventId    = $this->getEventId ($eventCode);
			$variables  = array (
				'GUEST'    => $guest,
				'CUSTOMER' => trim ("{$user->column_fields ['first_name']} {$user->column_fields ['last_name']}"),
				'URL'      => $this->buildUrl ($invitationId),
			);
			return $this->sendNotification ($guest, $language, $eventId, $variables);
		}

		public static function getInstance () {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new NotificationsManager ();
			}
			return self::$INSTANCE;
		}

	}

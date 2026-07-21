<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');

	class WebmailInstaller {
		const MODULE_NAME = 'webmail';

		private static $INSTANCE = null;

		private function buildSqlStatementsToCreateTables () {
			return array_merge (
				$this->buildSqlStatementsToDropTables (),
				array (
					'CREATE TABLE IF NOT EXISTS `vtiger_webmail_providers` (
						 `domain` varchar(255) NOT NULL,
						 `incomingservice` varchar(15) NOT NULL,
						 `incominghostname` varchar(255) NOT NULL,
						 `incomingport` int(11) NOT NULL,
						 `incomingsecuritytype` varchar(15) NOT NULL,
						 `incomingauthenticationmethod` varchar(25) NOT NULL,
						 `incomingusernametype` varchar(25) NOT NULL,
						 `outgoingservice` varchar(15) NOT NULL,
						 `outgoinghostname` varchar(255) NOT NULL,
						 `outgoingport` int(11) NOT NULL,
						 `outgoingsecuritytype` varchar(15) NOT NULL,
						 `outgoingauthenticationmethod` varchar(25) NOT NULL,
						 `outgoingusernametype` varchar(25) NOT NULL,
						 PRIMARY KEY (`domain`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8',
					'CREATE TABLE IF NOT EXISTS vtiger_webmail_users (
						userid INT(11) NOT NULL,
						provider VARCHAR(50) NOT NULL,
						fullname VARCHAR(255) NOT NULL,
						email VARCHAR(255) NOT NULL,
						username VARCHAR(255) NOT NULL,
						password VARCHAR(255) NOT NULL,
						receivedfolder VARCHAR(50) NOT NULL,
						sentfolder VARCHAR(50) NOT NULL,
						lastreceivedmessageuid INT(11) NULL DEFAULT NULL,
						lastsentmessageuid INT(11) NULL DEFAULT NULL,
						lastsyncedon DATETIME NULL DEFAULT NULL,
						lasterror VARCHAR(255) NULL DEFAULT NULL,
						PRIMARY KEY (userid, provider),
						INDEX FK_vtiger_webmail_users_vtiger_webmail_providers (provider),
						CONSTRAINT FK_vtiger_webmail_users_vtiger_webmail_providers FOREIGN KEY (provider) REFERENCES vtiger_webmail_providers (domain) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT FK_vtiger_webmail_users_vtiger_users_mail FOREIGN KEY (userid) REFERENCES vtiger_users (id) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE=InnoDB',
					'CREATE TABLE IF NOT EXISTS vtiger_webmail_usersmodules (
						userid INT(11) NOT NULL,
						provider VARCHAR(50) NOT NULL,
						modulename VARCHAR(25) NOT NULL,
						PRIMARY KEY (userid, provider, modulename),
						INDEX FK_vtiger_webmail_usersmodules_vtiger_tab (modulename),
						CONSTRAINT FK_vtiger_webmail_usersmodules_vtiger_tab FOREIGN KEY (modulename) REFERENCES vtiger_tab (name) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT FK_vtiger_webmail_usersmodules_vtiger_webmail_users FOREIGN KEY (userid, provider) REFERENCES vtiger_webmail_users (userid, provider) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE=InnoDB',
				)
			);
		}

		private function buildSqlStatementsToDropTables () {
			return array (
				'DROP TABLE IF EXISTS vtiger_webmail_usersmodules',
				'DROP TABLE IF EXISTS vtiger_webmail_users',
				'DROP TABLE IF EXISTS vtiger_webmail_providers',
			);
		}

		private function buildSqlStatementsToPopulateTables () {
			return array (
				"INSERT INTO `vtiger_webmail_providers` (`domain`, `incomingservice`, `incominghostname`, `incomingport`, `incomingsecuritytype`, `incomingauthenticationmethod`, `incomingusernametype`, `outgoingservice`, `outgoinghostname`, `outgoingport`, `outgoingsecuritytype`, `outgoingauthenticationmethod`, `outgoingusernametype`) 
				VALUES ('timemanagement.es', 'imap', 'mail.timemanagement.es', '143', 'starttls', 'password-cleartext', '%emailaddress%', 'smtp', 'mail.timemanagement.es', '587', 'starttls', 'password-cleartext', '%emailaddress%')",
				"INSERT INTO vtiger_oauth2_providers (providername, clientid, clientsecrets, classname) VALUES
				('Google', '979685675493-2o0slclb31kfceo2bchjj9l1ukrj1va7.apps.googleusercontent.com', 'mGmn8fX-DNGlbaojf7spqryq', 'League\\OAuth2\\Client\\Provider\\Google'),
				('Yahoo', 'dj0yJmk9VDRXVUxFY0dHWUVOJnM9Y29uc3VtZXJzZWNyZXQmc3Y9MCZ4PTJm', '6d0b1d0106efebff1a83ff599160cd227e121d61', 'Hayageek\\OAuth2\\Client\\Provider\\Yahoo')",
				"INSERT INTO `vtiger_oauth2_resources` (`resourcename`, `providername`, `authenticationscopeoptions`) VALUES
				('imap.gmail.com', 'Google', '{ \"scope\": [ \"https://mail.google.com/\" ] }'),
				('imap.mail.yahoo.com', 'Yahoo', '{ \"scope\": [ \"openid\" ] }')",
			);
		}

		public function install (PearDatabase $adb) {
			$this->uninstall ($adb);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance ()
					->setLabel ('Webmail')
					->setName (self::MODULE_NAME)
					->setPresence (ModuleInterface::PRESENCE_VISIBLE)
					->setShowInAdminConsole (true)
					->setType (ModuleInterface::TYPE_TOOL)
			);
		}

		public function runPostInstallTasks (PearDatabase $adb) {
			$sqlStatements = array_merge (
				$this->buildSqlStatementsToCreateTables (),
				$this->buildSqlStatementsToPopulateTables ()
			);
			if (empty ($sqlStatements)) {
				return;
			}
			$adb->query ('START TRANSACTION');
			foreach ($sqlStatements as $sqlStatement) {
				$adb->query ($sqlStatement);
			}
			$adb->query ('COMMIT');
		}

		public function runPreUninstallTasks (PearDatabase $adb) {
			$sqlStatements = $this->buildSqlStatementsToDropTables ();
			if (empty ($sqlStatements)) {
				return;
			}
			$adb->query ('START TRANSACTION');
			foreach ($sqlStatements as $sqlStatement) {
				$adb->query ($sqlStatement);
			}
			$adb->query ('COMMIT');
		}

		public function uninstall (PearDatabase $adb) {
			$mm     = ModuleManager::getInstance ($adb);
			$module = $mm->fetchModule (self::MODULE_NAME);
			if (empty ($module)) {
				return;
			}
			$mm->deleteModule ($module);
		}

		public static function getInstance () {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new self ();
			}
			return self::$INSTANCE;
		}

	}

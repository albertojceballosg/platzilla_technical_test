<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');

	class EmailManagerInstaller {
		const MODULE_NAME = 'emailmanager';

		private static $INSTANCE = null;

		private function buildSqlStatementsToCreateTables () {
			$sqlStatements = $this->buildSqlStatementsToDropTables ();
			return array_merge (
				$sqlStatements,
				array (
					'CREATE TABLE `vtiger_emailmanager_templates` (
						`templateid` INT(11) NOT NULL AUTO_INCREMENT,
						`templatename` VARCHAR(255) NOT NULL,
						`language` VARCHAR(5) NOT NULL,
						`subject` VARCHAR(255) NOT NULL,
						`body` LONGTEXT NOT NULL,
						`adddefaultheader` TINYINT(1) NOT NULL DEFAULT \'0\',
						`adddefaultfooter` TINYINT(1) NOT NULL DEFAULT \'0\',
						`attachments` TEXT NULL,
						`scope` VARCHAR(10) NULL DEFAULT \'USER\',
						PRIMARY KEY (`templateid`),
						UNIQUE INDEX `templatename_language` (`templatename`, `language`)
					) ENGINE=InnoDB',
					'CREATE TABLE `vtiger_emailmanager_emailhistory` (
						`emailid` INT(11) NOT NULL AUTO_INCREMENT,
						`templatename` VARCHAR(255) NULL DEFAULT NULL,
						`language` VARCHAR(5) NULL DEFAULT NULL,
						`createdon` DATETIME NOT NULL,
						`from` VARCHAR(255) NOT NULL,
						`to` VARCHAR(2000) NOT NULL,
						`subject` VARCHAR(255) NOT NULL,
						`body` LONGTEXT NOT NULL,
						`attachments` TEXT NULL,
						`status` VARCHAR(25) NOT NULL,
						`errormessage` TEXT NULL,
						PRIMARY KEY (`emailid`),
						INDEX `from` (`from`),
						INDEX `to` (`to`(255)),
						INDEX `errorcode` (`status`),
						INDEX `FK_vtiger_emailmanager_emailhistory_templates` (`templatename`, `language`),
						INDEX `createdon` (`createdon`),
						CONSTRAINT `FK_vtiger_emailmanager_emailhistory_templates` FOREIGN KEY (`templatename`, `language`) REFERENCES `vtiger_emailmanager_templates` (`templatename`, `language`) ON DELETE SET NULL
					)
						ENGINE=InnoDB',
				)
			);
		}

		private function buildSqlStatementsToDropTables () {
			return array (
				'DROP TABLE IF EXISTS vtiger_emailmanager_emailhistory',
				'DROP TABLE IF EXISTS vtiger_emailmanager_templates',
			);
		}

		private function buildSqlStatementsToRegisterAsSettingsModule () {
			$sqlStatements = array (
				'SET @BlockID := NULL',
				'SET @FieldID := NULL',
				"SELECT @BlockID:=blockid FROM vtiger_settings_blocks WHERE label='LBL_GENERAL_SETTINGS'",
				"INSERT INTO vtiger_settings_blocks (blockid, label, sequence)
					SELECT IFNULL(MAX(blockid), 0) + 1, 'LBL_GENERAL_SETTINGS', IFNULL(MAX(sequence), 0) + 1 FROM vtiger_settings_blocks HAVING @BlockID IS NULL",
				'SELECT @BlockId:=IFNULL(@BlockId, LAST_INSERT_ID())',
				'UPDATE vtiger_settings_blocks_seq SET id=(SELECT MAX(blockid) FROM vtiger_settings_blocks)',

				"SELECT @FieldID:=fieldid FROM vtiger_settings_field WHERE blockid=@BlockId AND name='LBL_EMAIL_MANAGER'",
				"INSERT INTO vtiger_settings_field (fieldid, blockid, name, iconpath, description, linkto, sequence)
					SELECT IFNULL(GREATEST(MAX(fs.id), MAX(f.fieldid)), 0) + 1, @BlockID, 'LBL_EMAIL_MANAGER', 'fa fa-envelope-o emerald-bg', 'Gestión de correos enviados por el sistema', 'index.php?module=emailmanager&action=index&parenttab=Settings', IFNULL(MAX(f.sequence), 0) + 1 FROM vtiger_settings_field_seq fs, vtiger_settings_field f WHERE f.blockid=@BlockID HAVING @FieldID IS NULL",
				"UPDATE vtiger_settings_field SET name='LBL_EMAIL_MANAGER', iconpath='fa fa-envelope-o emerald-bg', description='LBL_EMAIL_MANAGER_DESCRIPTION', linkto='index.php?module=emailmanager&action=index&parenttab=Settings' WHERE @FieldId IS NOT NULL AND fieldid=@FieldId AND blockid=@BlockId",
				'UPDATE vtiger_settings_field_seq SET id=(SELECT MAX(fieldid) FROM vtiger_settings_field)',
			);
			return $sqlStatements;
		}

		private function buildSqlStatementsToUnregisterAsSettingsModule () {
			$sqlStatements = array (
				"DELETE FROM vtiger_settings_field WHERE name='LBL_EMAIL_MANAGER'",
				'UPDATE vtiger_settings_field_seq SET id=(SELECT MAX(fieldid) FROM vtiger_settings_field)',
			);
			return $sqlStatements;
		}

		private function createDatabaseItems (PearDatabase $adb) {
			$sqlStatements = array_merge (
				$this->buildSqlStatementsToCreateTables (),
				$this->buildSqlStatementsToRegisterAsSettingsModule ()
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

		private function deleteDatabaseItems (PearDatabase $adb) {
			$sqlStatements = array_merge (
				$this->buildSqlStatementsToUnregisterAsSettingsModule (),
				$this->buildSqlStatementsToDropTables ()
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

		public function install (PearDatabase $adb) {
			$this->uninstall ($adb);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance ()
					->setLabel ('Gestor de correos')
					->setName (self::MODULE_NAME)
					->setPresence (ModuleInterface::PRESENCE_VISIBLE)
					->setShowInAdminConsole (false)
					->setType (ModuleInterface::TYPE_TOOL)
			);
		}

		public function runPostInstallTasks (PearDatabase $adb) {
			$this->createDatabaseItems ($adb);
		}

		public function runPreUninstallTasks (PearDatabase $adb) {
			$this->deleteDatabaseItems ($adb);
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

<?php
	require_once ('include/platzilla/Managers/ModuleManager.php');

	class GraphsInstaller {
		const MODULE_NAME = 'graficosgenerales';

		private static $INSTANCE = null;

		private function buildSqlStatementsToCreateTables () {
			return array_merge (
				$this->buildSqlStatementsToDropTables (),
				array (
					'CREATE TABLE `vtiger_graficos` (
						`graficoid` INT(11) NOT NULL AUTO_INCREMENT,
						`fld_module` VARCHAR(20) NOT NULL,
						`fieldoperation` VARCHAR(50) NOT NULL,
						`fieldcompare` VARCHAR(60) NULL,
						`operation` INT(2) NOT NULL,
						`tipografico` VARCHAR(20) NOT NULL,
						`title` VARCHAR(400) NOT NULL,
						`roles_grafico` VARCHAR(200) NULL DEFAULT NULL,
						`sqlprimarioreporte` TEXT NULL,
						`varreporte` TEXT NULL,
						`reporteavanzado` INT(11) NOT NULL DEFAULT \'0\',
						`comparar` INT(11) NULL DEFAULT \'0\',
						`ishome` INT(1) NOT NULL DEFAULT \'0\',
						`fieldgrouping` VARCHAR(50) NULL DEFAULT NULL,
						 `compareoperation` INT(2) NULL,
						`dategrouping` TINYINT(4) NULL DEFAULT NULL,
						`applicationcodes` TEXT NULL,
						`locked` TINYINT(1) NOT NULL DEFAULT \'0\',
						PRIMARY KEY (`graficoid`)
					) ENGINE=InnoDB',
				)
			);
		}

		private function buildSqlStatementsToDropTables () {
			return array (
				'DROP TABLE IF EXISTS vtiger_graficos',
			);
		}

		private function createDatabaseItems (PearDatabase $adb) {
			$sqlStatements = $this->buildSqlStatementsToCreateTables ();
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

		public function install (PearDatabase $adb) {
			$this->uninstall ($adb);

			ModuleManager::getInstance ($adb)->saveModule (
				Module::getInstance ()
					->setLabel ('Gráficos')
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

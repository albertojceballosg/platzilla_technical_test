<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/ReportsManager.php');

	/**
	 * Prueba funcional de la clase ReportsManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ReportsManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_blocks, vtiger_field, vtiger_subfields, vtiger_field_dependency, vtiger_fieldmodulerel, vtiger_picklist, vtiger_profile,
		 * vtiger_profile2field, vtiger_role, vtiger_role2picklist, vtiger_crmentity
		 * 4. Crear tabla de un módulo simulado vtiger_test_module
		 * 5. Simular existencia de dos módulos, un bloque, dos perfiles y dos roles
		 */
		public static function setUpBeforeClass () {
			global $dbconfig;
			parent::setUpBeforeClass ();
			require ('config.inc.php');
			$adb = new PearDatabase ($dbconfig ['db_type'], $dbconfig ['db_serverForNewDB'], '', $dbconfig ['db_username'], $dbconfig ['db_password']);
			$adb->query ('DROP DATABASE IF EXISTS `platzilla_test`');
			$adb->query ("CREATE DATABASE IF NOT EXISTS `platzilla_test` /*!40100 COLLATE 'utf8_general_ci' */");
			$adb->disconnect ();
			unset ($adb);
			self::$adb = new PearDatabase ($dbconfig ['db_type'], $dbconfig ['db_serverForNewDB'], 'platzilla_test', $dbconfig ['db_username'], $dbconfig ['db_password']);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_tab` (
					`tabid` INT(19) NOT NULL DEFAULT '0',
					`name` VARCHAR(25) NOT NULL,
					`presence` INT(19) NOT NULL DEFAULT '1',
					`tabsequence` INT(10) DEFAULT NULL,
					`tablabel` VARCHAR(64) NOT NULL,
					`modifiedby` INT(19) DEFAULT NULL,
					`modifiedtime` INT(19) DEFAULT NULL,
					`customized` INT(19) DEFAULT NULL,
					`ownedby` INT(19) DEFAULT NULL,
					`isentitytype` INT(11) NOT NULL DEFAULT '1',
					`version` VARCHAR(10) DEFAULT NULL,
					`parent` VARCHAR(30) DEFAULT NULL,
					`permite_filtros_listas` INT(1) NOT NULL DEFAULT '0',
					`combinable` INT(11) DEFAULT '0',
					`sends_notifications` INT(11) DEFAULT '0',
					`avaliable` INT(11) DEFAULT '1',
					`isplatzilla` INT(1) NOT NULL DEFAULT '1',
					`in_administration` INT(1) NOT NULL DEFAULT '1',
					`isvisibleinadmin` TINYINT(4) NOT NULL DEFAULT '1',
					PRIMARY KEY (`tabid`),
					UNIQUE KEY `tab_name_idx` (`name`),
					KEY `tab_modifiedby_idx` (`modifiedby`),
					KEY `tab_tabid_idx` (`tabid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_field` (
					`tabid` INT(19) NOT NULL,
					`fieldid` INT(19) NOT NULL AUTO_INCREMENT,
					`columnname` VARCHAR(30) NOT NULL,
					`tablename` VARCHAR(50) NOT NULL,
					`generatedtype` INT(19) NOT NULL DEFAULT '0',
					`uitype` VARCHAR(30) NOT NULL,
					`fieldname` VARCHAR(50) NOT NULL,
					`fieldlabel` VARCHAR(255) NOT NULL,
					`readonly` INT(1) NOT NULL,
					`presence` INT(19) NOT NULL DEFAULT '1',
					`defaultvalue` TEXT,
					`maximumlength` INT(19) DEFAULT NULL,
					`sequence` INT(19) DEFAULT NULL,
					`block` INT(19) DEFAULT NULL,
					`displaytype` INT(19) DEFAULT NULL,
					`typeofdata` VARCHAR(100) DEFAULT NULL,
					`quickcreate` INT(10) NOT NULL DEFAULT '1',
					`quickcreatesequence` INT(19) DEFAULT NULL,
					`info_type` VARCHAR(20) DEFAULT NULL,
					`masseditable` INT(10) NOT NULL DEFAULT '1',
					`helpinfo` TEXT,
					`paradicional` VARCHAR(255) DEFAULT NULL,
					PRIMARY KEY (`fieldid`),
					KEY `field_tabid_idx` (`tabid`),
					KEY `field_fieldname_idx` (`fieldname`),
					KEY `field_block_idx` (`block`),
					KEY `field_displaytype_idx` (`displaytype`),
					CONSTRAINT `fk_1_vtiger_field` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_field_validation` (
					`tabid` INT(19) NOT NULL,
					`fieldid` INT(19) NOT NULL,
					`tablename` VARCHAR(50) NOT NULL,
					`fieldname` VARCHAR(30) NOT NULL,
					`validationtype` VARCHAR(100) NOT NULL,
					`initialvalue` VARCHAR(32) NULL DEFAULT NULL,
					`maximumvalue` VARCHAR(32) NULL DEFAULT NULL
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_selectquery_seq` (
					`id` INT(11) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_selectquery` (
					`queryid` INT(19) NOT NULL,
					`startindex` INT(19) NULL DEFAULT '0',
					`numofobjects` INT(19) NULL DEFAULT '0',
					PRIMARY KEY (`queryid`),
					INDEX `selectquery_queryid_idx` (`queryid`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_selectcolumn` (
					`queryid` INT(19) NOT NULL,
					`columnindex` INT(11) NOT NULL DEFAULT '0',
					`columnname` VARCHAR(250) NULL DEFAULT '',
					PRIMARY KEY (`queryid`, `columnindex`),
					INDEX `selectcolumn_queryid_idx` (`queryid`),
					CONSTRAINT `fk_1_vtiger_selectcolumn` FOREIGN KEY (`queryid`) REFERENCES `vtiger_selectquery` (`queryid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_relcriteria_grouping` (
					`groupid` INT(11) NOT NULL,
					`queryid` INT(19) NOT NULL,
					`group_condition` VARCHAR(256) NULL DEFAULT NULL,
					`condition_expression` TEXT NULL,
					PRIMARY KEY (`groupid`, `queryid`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_relcriteria` (
					`queryid` INT(19) NOT NULL,
					`columnindex` INT(11) NOT NULL,
					`columnname` VARCHAR(250) NULL DEFAULT '',
					`comparator` VARCHAR(10) NULL DEFAULT '',
					`value` VARCHAR(200) NULL DEFAULT '',
					`groupid` INT(11) NULL DEFAULT '1',
					`column_condition` VARCHAR(256) NULL DEFAULT 'and',
					PRIMARY KEY (`queryid`, `columnindex`),
					INDEX `relcriteria_queryid_idx` (`queryid`),
					CONSTRAINT `fk_1_vtiger_relcriteria` FOREIGN KEY (`queryid`) REFERENCES `vtiger_selectquery` (`queryid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_reportfolder` (
					`folderid` INT(19) NOT NULL AUTO_INCREMENT,
					`foldername` VARCHAR(100) NOT NULL DEFAULT '',
					`description` VARCHAR(250) NULL DEFAULT '',
					`state` VARCHAR(50) NULL DEFAULT 'SAVED',
					PRIMARY KEY (`folderid`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_report` (
					`reportid` INT(19) NOT NULL,
					`folderid` INT(19) NOT NULL,
					`reportname` VARCHAR(100) NULL DEFAULT '',
					`description` VARCHAR(250) NULL DEFAULT '',
					`reporttype` VARCHAR(50) NULL DEFAULT '',
					`queryid` INT(19) NOT NULL DEFAULT '0',
					`state` VARCHAR(50) NULL DEFAULT 'SAVED',
					`customizable` INT(1) NULL DEFAULT '1',
					`category` INT(11) NULL DEFAULT '1',
					`owner` INT(11) NULL DEFAULT '1',
					`sharingtype` VARCHAR(200) NULL DEFAULT 'Private',
					`applicationcodes` TEXT NULL,
					PRIMARY KEY (`reportid`),
					INDEX `report_queryid_idx` (`queryid`),
					INDEX `report_folderid_idx` (`folderid`),
					CONSTRAINT `fk_2_vtiger_report` FOREIGN KEY (`queryid`) REFERENCES `vtiger_selectquery` (`queryid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_reportmodules` (
					`reportmodulesid` INT(19) NOT NULL,
					`primarymodule` VARCHAR(50) NOT NULL DEFAULT '',
					`secondarymodules` VARCHAR(250) NULL DEFAULT '',
					PRIMARY KEY (`reportmodulesid`),
					CONSTRAINT `fk_1_vtiger_reportmodules` FOREIGN KEY (`reportmodulesid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_reportsortcol` (
					`sortcolid` INT(19) NOT NULL,
					`reportid` INT(19) NOT NULL,
					`columnname` VARCHAR(250) NULL DEFAULT '',
					`sortorder` VARCHAR(250) NULL DEFAULT 'Asc',
					PRIMARY KEY (`sortcolid`, `reportid`),
					INDEX `fk_1_vtiger_reportsortcol` (`reportid`),
					CONSTRAINT `fk_1_vtiger_reportsortcol` FOREIGN KEY (`reportid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_reportgroupbycolumn` (
					`reportid` INT(19) NULL DEFAULT NULL,
					`sortid` INT(19) NULL DEFAULT NULL,
					`sortcolname` VARCHAR(250) NULL DEFAULT NULL,
					`dategroupbycriteria` VARCHAR(250) NULL DEFAULT NULL,
					`fieldpk` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					PRIMARY KEY (`fieldpk`),
					INDEX `fk_1_vtiger_reportgroupbycolumn` (`reportid`),
					CONSTRAINT `fk_1_vtiger_reportgroupbycolumn` FOREIGN KEY (`reportid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_reportsummary` (
					`reportsummaryid` INT(19) NOT NULL,
					`summarytype` INT(19) NOT NULL,
					`columnname` VARCHAR(250) NOT NULL DEFAULT '',
					PRIMARY KEY (`reportsummaryid`, `summarytype`, `columnname`),
					INDEX `reportsummary_reportsummaryid_idx` (`reportsummaryid`),
					CONSTRAINT `fk_1_vtiger_reportsummary` FOREIGN KEY (`reportsummaryid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_reportdatefilter` (
					`datefilterid` INT(19) NOT NULL,
					`datecolumnname` VARCHAR(250) NULL DEFAULT '',
					`datefilter` VARCHAR(250) NULL DEFAULT '',
					`startdate` DATE NULL DEFAULT NULL,
					`enddate` DATE NULL DEFAULT NULL,
					PRIMARY KEY (`datefilterid`),
					INDEX `reportdatefilter_datefilterid_idx` (`datefilterid`),
					CONSTRAINT `fk_1_vtiger_reportdatefilter` FOREIGN KEY (`datefilterid`) REFERENCES `vtiger_report` (`reportid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_reportsharing` (
					`reportid` INT(19) NOT NULL,
					`shareid` INT(19) NOT NULL,
					`setype` VARCHAR(200) NOT NULL,
					`fieldpk` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					PRIMARY KEY (`fieldpk`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_scheduled_reports` (
					`reportid` INT(11) NOT NULL,
					`recipients` TEXT NULL,
					`schedule` TEXT NULL,
					`format` VARCHAR(10) NULL DEFAULT NULL,
					`next_trigger_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					PRIMARY KEY (`reportid`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_deletedelements` (
					`elementtype` VARCHAR(255) NOT NULL,
					`modulename` VARCHAR(50) NOT NULL,
					`identifier` VARCHAR(255) NOT NULL,
					`deletedon` DATETIME NOT NULL,
					`serializedobject` LONGTEXT NULL,
					PRIMARY KEY (`elementtype`, `modulename`, `identifier`)
				) ENGINE=InnoDB"
			);

			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (2, 'related_module_one', 0, 2, 'Related module one', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (3, 'related_module_two', 0, 2, 'Related module two', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 1, 'code_field', 'vtiger_test_module', 1, '4', 'code_field', 'My code field', 1, 2, '', 100, 1, 4166, 1, 'V~M~LE~100', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 2, 'date_field', 'vtiger_test_module', 1, '5', 'date_field', 'My date field', 1, 2, '', 100, 1, 4166, 1, 'D~O', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 3, 'number_field', 'vtiger_test_module', 1, '7', 'number_field', 'My number field', 1, 2, '', 100, 1, 4166, 1, 'NN~O~16,2', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 4, 'text_field', 'vtiger_test_module', 1, '1', 'text_field', 'My text field', 1, 2, '', 100, 1, 4166, 1, 'NN~O~16,2', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 5, 'datetime_field', 'vtiger_test_module', 1, '6', 'datetime_field', 'My datetime field', 1, 2, '', 100, 1, 4166, 1, 'NN~O~16,2', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (2, 6, 'related_date_field', 'vtiger_related_module_one', 1, '5', 'related_date_field', 'My related date field', 1, 2, '', 100, 1, 4166, 1, 'NN~O~16,2', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (2, 7, 'related_text_field', 'vtiger_related_module_one', 1, '1', 'related_text_field', 'My related text field', 1, 2, '', 100, 1, 4166, 1, 'NN~O~16,2', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (3, 8, 'another_date_field', 'vtiger_related_module_two', 1, '5', 'another_date_field', 'Another related date field', 1, 2, '', 100, 1, 4166, 1, 'NN~O~16,2', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (3, 9, 'another_text_field', 'vtiger_related_module_two', 1, '1', 'another_text_field', 'Another related text field', 1, 2, '', 100, 1, 4166, 1, 'NN~O~16,2', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_selectquery_seq` (id) VALUES (0)");
		}

		/**
		 * Cerrar la prueba:
		 * 1. Eliminar la base de datos de prueba
		 * 2. Desconectar de la base de datos
		 */
		public static function tearDownAfterClass () {
			parent::tearDownAfterClass ();
			self::$adb->query ('DROP DATABASE IF EXISTS `platzilla_test`');
			self::$adb->disconnect ();
		}

		/**
		 * Intentar crear un reporte sin la información mínima necesaria
		 * Debe arrojar una ReportException
		 */
		public function testCreateIncompleteReport () {
			$report = Report::getInstance ();
			$this->expectException (ReportException::class);
			ReportsManager::getInstance (self::$adb)->saveReport ($report);
		}

		/**
		 * Crear una carpeta de reportes
		 */
		public function testCreateFolder () {
			$folderId          = 1;
			$folderDescription = 'My report folder description';
			$folderName        = 'My folder';

			$folder      = ReportFolder::getInstance ()
				->setName ($folderName)
				->setDescription ($folderDescription)
				->setStatus (ReportInterface::STATUS_SAVED);
			$savedFolder = ReportsManager::getInstance (self::$adb)->saveFolder ($folder);

			// Verificar que se almacenaron los datos de la carpeta en la base de datos
			$this->assertNotNull ($savedFolder, 'Report folder should not be null');
			$this->assertNotEmpty ($savedFolder->getId (), 'Report folder ID should not be empty');

			$result = self::$adb->query ('SELECT * FROM vtiger_reportfolder');
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Folder not found in database');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($folderId, $row ['folderid'], 'Folder IDs do not match');
			$this->assertEquals ($folderName, $row ['foldername'], 'Folder names do not match');
			$this->assertEquals ($folderDescription, $row ['description'], 'Folder descriptions do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Folder statuses do not match');
		}

		/**
		 * Intentar obtener una carpeta de reportes de la base de datos que no existe
		 */
		public function testFetchFolderByNonExistingId () {
			$folderId = 159;
			$folder   = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$this->assertNull ($folder, 'Report folder should be null');
		}

		/**
		 * Intentar obtener una carpeta de reportes de la base de datos que no existe
		 */
		public function testFetchFolderByNonExistingName () {
			$folderName = 'Unknown folder';
			$folder     = ReportsManager::getInstance (self::$adb)->fetchFolderByName ($folderName);
			$this->assertNull ($folder, 'Report folder should be null');
		}

		/**
		 * Obtener una carpeta de reportes de la base de datos por el ID
		 * @depends testCreateFolder
		 */
		public function testFetchFolderByExistingId () {
			$folderId          = 1;
			$folderDescription = 'My report folder description';
			$folderName        = 'My folder';

			$folder = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);

			// Verificar que se obtuvieron los datos de la carpeta en la base de datos
			$this->assertNotNull ($folder, 'Report folder should not be null');
			$this->assertNotEmpty ($folder->getId (), 'Report folder ID should not be empty');
			$this->assertEquals ($folderId, $folder->getId (), 'Folder IDs do not match');
			$this->assertEquals ($folderName, $folder->getName (), 'Folder names do not match');
			$this->assertEquals ($folderDescription, $folder->getDescription (), 'Folder descriptions do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $folder->getStatus (), 'Folder statuses do not match');
		}

		/**
		 * Obtener una carpeta de reportes de la base de datos por el nombre
		 * @depends testCreateFolder
		 */
		public function testFetchFolderByExistingName () {
			$folderId          = 1;
			$folderDescription = 'My report folder description';
			$folderName        = 'My folder';

			$folder = ReportsManager::getInstance (self::$adb)->fetchFolderByName ($folderName);

			// Verificar que se obtuvieron los datos de la carpeta en la base de datos
			$this->assertNotNull ($folder, 'Report folder should not be null');
			$this->assertNotEmpty ($folder->getId (), 'Report folder ID should not be empty');
			$this->assertEquals ($folderId, $folder->getId (), 'Folder IDs do not match');
			$this->assertEquals ($folderName, $folder->getName (), 'Folder names do not match');
			$this->assertEquals ($folderDescription, $folder->getDescription (), 'Folder descriptions do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $folder->getStatus (), 'Folder statuses do not match');
		}

		/**
		 * Intentar crear una carpeta de reportes con un nombre duplicado
		 * @depends testCreateFolder
		 */
		public function testCreateDuplicatedNameFolder () {
			$folderDescription = 'My report folder description # 2';
			$folderName        = 'My folder';

			$folder = ReportFolder::getInstance ()
				->setName ($folderName)
				->setDescription ($folderDescription)
				->setStatus (ReportInterface::STATUS_SAVED);
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_FOLDER_DUPLICATE_NAME);
			ReportsManager::getInstance (self::$adb)->saveFolder ($folder);
		}

		/**
		 * Actualizar una carpeta de reportes existente
		 * @depends testCreateFolder
		 */
		public function testUpdateFolder () {
			$folderId          = 1;
			$folderDescription = 'My report folder description # 2';
			$folderName        = 'My folder # 2';

			$folder      = ReportFolder::getInstance ()
				->setId ($folderId)
				->setName ($folderName)
				->setDescription ($folderDescription)
				->setStatus (ReportInterface::STATUS_CUSTOMIZED);
			$savedFolder = ReportsManager::getInstance (self::$adb)->saveFolder ($folder);

			// Verificar que se almacenaron los datos de la carpeta en la base de datos
			$this->assertNotNull ($savedFolder, 'Report folder should not be null');
			$this->assertNotEmpty ($savedFolder->getId (), 'Report folder ID should not be empty');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportfolder WHERE folderid=?', array ($folderId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Folder not found in database');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($folderId, $row ['folderid'], 'Folder IDs do not match');
			$this->assertEquals ($folderName, $row ['foldername'], 'Folder names do not match');
			$this->assertEquals ($folderDescription, $row ['description'], 'Folder descriptions do not match');
			$this->assertEquals (ReportInterface::STATUS_CUSTOMIZED, $row ['state'], 'Folder statuses do not match');
		}

		/**
		 * Intentar crear un reporte para un módulo no existente
		 * Debe arrojar una ReportException
		 * @depends testCreateFolder
		 */
		public function testCreateInvalidModuleReport () {
			$field  = Field::getInstance ()
				->setColumnName ('code_field')
				->setLabel ('My field label')
				->setModuleName ('unknown_module')
				->setName ('code_field')
				->setTableName ('vtiger_test_module')
				->setUiType (FieldInterface::UI_TYPE_TEXT);
			$report = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns (array (
					ReportColumn::getInstance ($field)->setSequence (0),
				))
				->setDescription ('My report description')
				->setFolder (ReportFolder::getInstance ()->setId (1)->setName ('My folder')->setDescription ('My report folder description')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ('unknown_module')
				->setName ('My report')
				->setOwner (1)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_INVALID_MODULE_NAME);
			ReportsManager::getInstance (self::$adb)->saveReport ($report);
		}

		/**
		 * Crear un reporte tabular con el mínimo de información
		 * @depends testCreateFolder
		 */
		public function testCreateMinimalTabularReport () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que no existe información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');
		}

		/**
		 * Crear un reporte tabular con módulos relacionados
		 * @depends testCreateFolder
		 */
		public function testCreateTabularReportWithRelatedModules () {
			$folderId           = 1;
			$fieldLabels        = array ('My text field', 'My date field', 'My related text field', 'My related date field', 'Another related text field', 'Another related date field');
			$fieldTypes         = array (FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_DATETIME, FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_DATETIME, FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_DATETIME);
			$fieldNames         = array ('text_field', 'date_field', 'related_text_field', 'related_date_field', 'another_text_field', 'another_date_field');
			$moduleNames        = array ('test_module', 'test_module', 'related_module_one', 'related_module_one', 'related_module_two', 'related_module_two');
			$tableNames         = array ('vtiger_test_module', 'vtiger_test_module', 'vtiger_related_module_one', 'vtiger_related_module_one', 'vtiger_related_module_two', 'vtiger_related_module_two');
			$reportDescription  = 'My report description';
			$reportName         = 'Tabular report with related modules';
			$relatedModuleNames = array ('related_module_one', 'related_module_two');

			/** @var Field[] $fields */
			$fields = array ();
			foreach ($fieldNames as $index => $fieldName) {
				$fields [] = Field::getInstance ()
					->setUiType ($fieldTypes [ $index ])
					->setColumnName ($fieldName)
					->setName ($fieldName)
					->setLabel ($fieldLabels [ $index ])
					->setModuleName ($moduleNames [ $index ])
					->setTableName ($tableNames [ $index ]);
			}
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
				ReportColumn::getInstance ($fields [4])->setSequence (4),
				ReportColumn::getInstance ($fields [5])->setSequence (5),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleNames [0])
				->setName ($reportName)
				->setOwner (1)
				->setRelatedModuleNames ($relatedModuleNames)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleNames [0], $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals ($relatedModuleNames, explode (':', $row ['secondarymodules']), 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que no existe información de programación de envíos en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');
		}

		/**
		 * Crear un reporte tabular con columnas de ordenamiento. Debe generar las columnas de ordenamiento por defecto
		 * @depends testCreateFolder
		 */
		public function testCreateTabularReportWithSortColumns () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report with sort columns';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setSortColumns (array ($columns [3], $columns [1]))
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que no existe información de programación de envíos en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');
		}

		/**
		 * Crear un reporte tabular con columnas de totalización
		 * @depends testCreateFolder
		 */
		public function testCreateTabularReportWithTotalColumns () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report with total columns';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			/** @var ReportColumn[] $columns */
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setTotalColumns (array (
					$columns [1]->duplicate (null)->setTotalsOperation (ReportColumnInterface::TOTALS_OPERATION_AVERAGE),
					$columns [3]->duplicate (null)->setTotalsOperation (ReportColumnInterface::TOTALS_OPERATION_MINIMUM),
				))
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar tiene asociadas las columnas de totalización
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Saved report total columns count do not match');
			$index        = 0;
			$totalColumns = $report->getTotalColumns ();
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$operation = $totalColumns [ $index ]->getTotalsOperation ();
				switch ($operation) {
					case ReportColumnInterface::TOTALS_OPERATION_AVERAGE:
						$dummy = 3;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_MAXIMUM:
						$dummy = 5;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_MINIMUM:
						$dummy = 4;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_SUM:
						$dummy = 2;
						break;
					default:
						$dummy = 1;
						break;
				}
				$columnName = "cb:{$totalColumns [$index]->getTableName ()}:{$totalColumns [$index]->getColumnName ()}:{$totalColumns [$index]->getLabel ()}_{$totalColumns [$index]->getTotalsOperation ()}:{$dummy}";
				$this->assertEquals ($reportId, $row ['reportsummaryid'], 'Total column report ID should not be empty');
				$this->assertEquals (($index + 1), $row ['summarytype'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que no existe información de programación de envíos en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');
		}

		/**
		 * Crear un reporte tabular con un filtro estándar
		 * @depends testCreateFolder
		 */
		public function testCreateTabularReportWithStandardFilter () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report with standard filter';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setStandardFilter (ReportStandardFilter::getInstance ($fields [3])->setEndDate ('2018-12-31')->setPeriod (ReportStandardFilterInterface::PERIOD_CURRENT_YEAR)->setStartDate ('2018-01-01'))
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar tiene asociado el filtro estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getName ()}:{$fields [3]->getModuleName ()}_{$label}";
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['datefilterid'], 'Saved report standard filter report IDs do not match');
			$this->assertEquals ($columnName, $row ['datecolumnname'], 'Saved report standard filter column names do not match');
			$this->assertEquals (ReportStandardFilterInterface::PERIOD_CURRENT_YEAR, $row ['datefilter'], 'Saved report standard filter periods do not match');
			$this->assertEquals ('2018-12-31', $row ['enddate'], 'Saved report standard filter end dates do not match');
			$this->assertEquals ('2018-01-01', $row ['startdate'], 'Saved report standard filter start dates do not match');

			// Verificar que no tiene filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que no existe información de programación de envíos en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');
		}

		/**
		 * Crear un reporte tabular con grupos de filtros avanzados
		 * @depends testCreateFolder
		 */
		public function testCreateTabularReportWithAdvancedFilters () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report with advanced filters';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setAdvancedFilterGroups (array (
					ReportAdvancedFilterGroup::getInstance ()
						->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)
						->setSequence (0)
						->setFilters (array (
							ReportAdvancedFilter::getInstance ($fields [0])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
								->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)
								->setSequence (0)
								->setValue ('COD-0001'),
							ReportAdvancedFilter::getInstance ($fields [1])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS)
								->setSequence (1)
								->setValue ('TEST'),
						)),
					ReportAdvancedFilterGroup::getInstance ()
						->setSequence (1)
						->setFilters (array (
							ReportAdvancedFilter::getInstance ($fields [2])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_GREATER)
								->setOperator (ReportAdvancedFilterInterface::OPERATOR_OR)
								->setSequence (2)
								->setValue (0),
							ReportAdvancedFilter::getInstance ($fields [3])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS)
								->setSequence (3)
								->setValue ('2017-12-31'),
						)),
				))
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que tiene asociados los grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que se almacenaron correctamente los datos del primer grupo
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('and', $row ['group_condition'], 'Group operators do not match');
			$this->assertEquals (' 0 and 1 ', $row ['condition_expression'], 'Group condition expresions do not match');

			// Verificar que se almacenaron correctamente los datos del segundo grupo
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('', $row ['group_condition'], 'Group operators do not match');
			$this->assertEquals (' 2 or 3 ', $row ['condition_expression'], 'Group condition expresions do not match');

			// Verificar que se crearon los filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'Saved report advanced filters count do not match');

			// Verificar que se almacenaron correctamente los datos del primer filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [0]->getLabel ());
			$columnName = "{$fields [0]->getTableName ()}:{$fields [0]->getColumnName ()}:{$fields [0]->getModuleName ()}_{$label}:{$fields [0]->getName ()}:{$fields [0]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (0, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_EQUALS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('COD-0001', $row ['value'], 'Values do not match');
			$this->assertEquals (0, $row ['groupid'], 'Values do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_AND, $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del segundo filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [1]->getLabel ());
			$columnName = "{$fields [1]->getTableName ()}:{$fields [1]->getColumnName ()}:{$fields [1]->getModuleName ()}_{$label}:{$fields [1]->getName ()}:{$fields [1]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (1, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('TEST', $row ['value'], 'Values do not match');
			$this->assertEquals (0, $row ['groupid'], 'Values do not match');
			$this->assertEquals ('', $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del tercer filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [2]->getLabel ());
			$columnName = "{$fields [2]->getTableName ()}:{$fields [2]->getColumnName ()}:{$fields [2]->getModuleName ()}_{$label}:{$fields [2]->getName ()}:{$fields [2]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (2, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_GREATER, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals (0, $row ['value'], 'Values do not match');
			$this->assertEquals (1, $row ['groupid'], 'Values do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_OR, $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del cuarto filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getModuleName ()}_{$label}:{$fields [3]->getName ()}:{$fields [3]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (3, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('2017-12-31', $row ['value'], 'Values do not match');
			$this->assertEquals (1, $row ['groupid'], 'Values do not match');
			$this->assertEquals ('', $row ['column_condition'], 'Values do not match');

			// Verificar que no existe información de compartir en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que no existe información de programación de envíos en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');
		}

		/**
		 * Crear un reporte tabular compartido con usuario y grupo
		 * @depends testCreateFolder
		 */
		public function testCreateSharedTabularReport () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Shared tabular report';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setShareWith (array (
					ReportSharingEntity::getInstance ()->setId (15)->setType (ReportSharingEntityInterface::TYPE_USER),
					ReportSharingEntity::getInstance ()->setId (10)->setType (ReportSharingEntityInterface::TYPE_GROUP),
				))
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_SHARED);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_SHARED, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que se almacenaron correctamente los datos de la primera entidad
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sharing entities report IDs do not match');
			$this->assertEquals (15, $row ['shareid'], 'Saved report sharing entity IDs do not match');
			$this->assertEquals (ReportSharingEntityInterface::TYPE_USER, $row ['setype'], 'Saved report sharing types do not match');

			// Verificar que se almacenaron correctamente los datos de la segunda entidad
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sharing entities report IDs do not match');
			$this->assertEquals (10, $row ['shareid'], 'Saved report sharing entity IDs do not match');
			$this->assertEquals (ReportSharingEntityInterface::TYPE_GROUP, $row ['setype'], 'Saved report sharing types do not match');

			// Verificar que no existe información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');
		}

		/**
		 * Crear un reporte tabular con programación de envío diario
		 * @depends testCreateFolder
		 */
		public function testCreateTabularReportWithDailyScheduling () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report with daily scheduling';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setSchedule (
					ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_DAILY, '06:00')
						->setGroups (array (23, 56))
						->setFormat (ReportScheduleInterface::FORMAT_EXCEL)
						->setRolesAndSubordinates (array ('H25'))
				)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que tiene asociada la información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');

			$row      = self::$adb->fetchByAssoc ($result, -1, false);
			$schedule = !empty ($row ['schedule']) ? json_decode ($row ['schedule'], true) : null;
			$this->assertNotEmpty ($schedule, 'Saved report schedule data should not be empty');
			switch ($schedule ['scheduletype']) {
				case ReportScheduleInterface::FREQUENCY_BIWEEKLY:
				case ReportScheduleInterface::FREQUENCY_WEEKLY:
					$day     = null;
					$month   = null;
					$weekDay = in_array ($schedule ['day'], array (ReportScheduleInterface::WEEKDAY_SUNDAY, ReportScheduleInterface::WEEKDAY_MONDAY, ReportScheduleInterface::WEEKDAY_TUESDAY, ReportScheduleInterface::WEEKDAY_WEDNESDAY, ReportScheduleInterface::WEEKDAY_THURSDAY, ReportScheduleInterface::WEEKDAY_FRIDAY, ReportScheduleInterface::WEEKDAY_SATURDAY)) ? $schedule ['day'] : null;
					break;
				case ReportScheduleInterface::FREQUENCY_DAILY:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_MONTHLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_YEARLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = in_array ($schedule ['month'], array (ReportScheduleInterface::MONTH_JANUARY, ReportScheduleInterface::MONTH_FEBRUARY, ReportScheduleInterface::MONTH_MARCH, ReportScheduleInterface::MONTH_APRIL, ReportScheduleInterface::MONTH_MAY, ReportScheduleInterface::MONTH_JUNE, ReportScheduleInterface::MONTH_JULY, ReportScheduleInterface::MONTH_AUGUST, ReportScheduleInterface::MONTH_SEPTEMBER, ReportScheduleInterface::MONTH_OCTOBER, ReportScheduleInterface::MONTH_NOVEMBER, ReportScheduleInterface::MONTH_DECEMBER)) ? $schedule ['month'] : null;
					$weekDay = null;
					break;
				default:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
			}

			$recipients = !empty ($row ['recipients']) ? json_decode ($row ['recipients'], true) : null;
			$this->assertNotEmpty ($recipients, 'Saved report schedule recipients should not be empty');

			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report scheduling report IDs do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_EXCEL, $row ['format'], 'Saved report scheduling formats do not match');
			$this->assertEquals (array (23, 56), $recipients ['groups'], 'Saved report schedule groups do not match');
			$this->assertEquals (array (), $recipients ['roles'], 'Saved report schedule roles do not match');
			$this->assertEquals (array ('H25'), $recipients ['rs'], 'Saved report schedule roles and subordinates do not match');
			$this->assertEquals (array (), $recipients ['users'], 'Saved report schedule users do not match');
			$this->assertEquals ('06:00', $schedule ['time'], 'Saved report scheduling days do not match');
			$this->assertEquals (null, $day, 'Saved report scheduling days do not match');
			$this->assertEquals (null, $month, 'Saved report scheduling months do not match');
			$this->assertEquals (null, $weekDay, 'Saved report scheduling weekdays do not match');
		}

		/**
		 * Crear un reporte tabular con programación de envío semanal
		 * @depends testCreateFolder
		 */
		public function testCreateTabularReportWithWeeklyScheduling () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report with weekly scheduling';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setSchedule (
					ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_WEEKLY, '06:00', ReportScheduleInterface::WEEKDAY_WEDNESDAY)
						->setGroups (array (23, 56))
						->setFormat (ReportScheduleInterface::FORMAT_EXCEL)
						->setRolesAndSubordinates (array ('H25'))
				)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que tiene asociada la información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');

			$row      = self::$adb->fetchByAssoc ($result, -1, false);
			$schedule = !empty ($row ['schedule']) ? json_decode ($row ['schedule'], true) : null;
			$this->assertNotEmpty ($schedule, 'Saved report schedule data should not be empty');
			switch ($schedule ['scheduletype']) {
				case ReportScheduleInterface::FREQUENCY_BIWEEKLY:
				case ReportScheduleInterface::FREQUENCY_WEEKLY:
					$day     = null;
					$month   = null;
					$weekDay = in_array ($schedule ['day'], array (ReportScheduleInterface::WEEKDAY_SUNDAY, ReportScheduleInterface::WEEKDAY_MONDAY, ReportScheduleInterface::WEEKDAY_TUESDAY, ReportScheduleInterface::WEEKDAY_WEDNESDAY, ReportScheduleInterface::WEEKDAY_THURSDAY, ReportScheduleInterface::WEEKDAY_FRIDAY, ReportScheduleInterface::WEEKDAY_SATURDAY)) ? $schedule ['day'] : null;
					break;
				case ReportScheduleInterface::FREQUENCY_DAILY:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_MONTHLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_YEARLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = in_array ($schedule ['month'], array (ReportScheduleInterface::MONTH_JANUARY, ReportScheduleInterface::MONTH_FEBRUARY, ReportScheduleInterface::MONTH_MARCH, ReportScheduleInterface::MONTH_APRIL, ReportScheduleInterface::MONTH_MAY, ReportScheduleInterface::MONTH_JUNE, ReportScheduleInterface::MONTH_JULY, ReportScheduleInterface::MONTH_AUGUST, ReportScheduleInterface::MONTH_SEPTEMBER, ReportScheduleInterface::MONTH_OCTOBER, ReportScheduleInterface::MONTH_NOVEMBER, ReportScheduleInterface::MONTH_DECEMBER)) ? $schedule ['month'] : null;
					$weekDay = null;
					break;
				default:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
			}

			$recipients = !empty ($row ['recipients']) ? json_decode ($row ['recipients'], true) : null;
			$this->assertNotEmpty ($recipients, 'Saved report schedule recipients should not be empty');

			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report scheduling report IDs do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_EXCEL, $row ['format'], 'Saved report scheduling formats do not match');
			$this->assertEquals (array (23, 56), $recipients ['groups'], 'Saved report schedule groups do not match');
			$this->assertEquals (array (), $recipients ['roles'], 'Saved report schedule roles do not match');
			$this->assertEquals (array ('H25'), $recipients ['rs'], 'Saved report schedule roles and subordinates do not match');
			$this->assertEquals (array (), $recipients ['users'], 'Saved report schedule users do not match');
			$this->assertEquals ('06:00', $schedule ['time'], 'Saved report scheduling days do not match');
			$this->assertEquals (null, $day, 'Saved report scheduling days do not match');
			$this->assertEquals (null, $month, 'Saved report scheduling months do not match');
			$this->assertEquals (ReportScheduleInterface::WEEKDAY_WEDNESDAY, $weekDay, 'Saved report scheduling weekdays do not match');
		}

		/**
		 * Crear un reporte tabular con programación de envío quincenal
		 * @depends testCreateFolder
		 */
		public function testCreateTabularReportWithBiweeklyScheduling () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report with biweekly scheduling';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setSchedule (
					ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_BIWEEKLY, '06:00', ReportScheduleInterface::WEEKDAY_MONDAY)
						->setGroups (array (23, 56))
						->setFormat (ReportScheduleInterface::FORMAT_EXCEL)
						->setRolesAndSubordinates (array ('H25'))
				)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que tiene asociada la información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');

			$row      = self::$adb->fetchByAssoc ($result, -1, false);
			$schedule = !empty ($row ['schedule']) ? json_decode ($row ['schedule'], true) : null;
			$this->assertNotEmpty ($schedule, 'Saved report schedule data should not be empty');
			switch ($schedule ['scheduletype']) {
				case ReportScheduleInterface::FREQUENCY_BIWEEKLY:
				case ReportScheduleInterface::FREQUENCY_WEEKLY:
					$day     = null;
					$month   = null;
					$weekDay = in_array ($schedule ['day'], array (ReportScheduleInterface::WEEKDAY_SUNDAY, ReportScheduleInterface::WEEKDAY_MONDAY, ReportScheduleInterface::WEEKDAY_TUESDAY, ReportScheduleInterface::WEEKDAY_WEDNESDAY, ReportScheduleInterface::WEEKDAY_THURSDAY, ReportScheduleInterface::WEEKDAY_FRIDAY, ReportScheduleInterface::WEEKDAY_SATURDAY)) ? $schedule ['day'] : null;
					break;
				case ReportScheduleInterface::FREQUENCY_DAILY:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_MONTHLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_YEARLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = in_array ($schedule ['month'], array (ReportScheduleInterface::MONTH_JANUARY, ReportScheduleInterface::MONTH_FEBRUARY, ReportScheduleInterface::MONTH_MARCH, ReportScheduleInterface::MONTH_APRIL, ReportScheduleInterface::MONTH_MAY, ReportScheduleInterface::MONTH_JUNE, ReportScheduleInterface::MONTH_JULY, ReportScheduleInterface::MONTH_AUGUST, ReportScheduleInterface::MONTH_SEPTEMBER, ReportScheduleInterface::MONTH_OCTOBER, ReportScheduleInterface::MONTH_NOVEMBER, ReportScheduleInterface::MONTH_DECEMBER)) ? $schedule ['month'] : null;
					$weekDay = null;
					break;
				default:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
			}

			$recipients = !empty ($row ['recipients']) ? json_decode ($row ['recipients'], true) : null;
			$this->assertNotEmpty ($recipients, 'Saved report schedule recipients should not be empty');

			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report scheduling report IDs do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_EXCEL, $row ['format'], 'Saved report scheduling formats do not match');
			$this->assertEquals (array (23, 56), $recipients ['groups'], 'Saved report schedule groups do not match');
			$this->assertEquals (array (), $recipients ['roles'], 'Saved report schedule roles do not match');
			$this->assertEquals (array ('H25'), $recipients ['rs'], 'Saved report schedule roles and subordinates do not match');
			$this->assertEquals (array (), $recipients ['users'], 'Saved report schedule users do not match');
			$this->assertEquals ('06:00', $schedule ['time'], 'Saved report scheduling days do not match');
			$this->assertEquals (null, $day, 'Saved report scheduling days do not match');
			$this->assertEquals (null, $month, 'Saved report scheduling months do not match');
			$this->assertEquals (ReportScheduleInterface::WEEKDAY_MONDAY, $weekDay, 'Saved report scheduling weekdays do not match');
		}

		/**
		 * Crear un reporte tabular con programación de envío mensual
		 * @depends testCreateFolder
		 */
		public function testCreateTabularReportWithMonthlyScheduling () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report with monthly scheduling';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setSchedule (
					ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_MONTHLY, '06:00', 25)
						->setGroups (array (23, 56))
						->setFormat (ReportScheduleInterface::FORMAT_EXCEL)
						->setRolesAndSubordinates (array ('H25'))
				)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que tiene asociada la información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');

			$row      = self::$adb->fetchByAssoc ($result, -1, false);
			$schedule = !empty ($row ['schedule']) ? json_decode ($row ['schedule'], true) : null;
			$this->assertNotEmpty ($schedule, 'Saved report schedule data should not be empty');
			switch ($schedule ['scheduletype']) {
				case ReportScheduleInterface::FREQUENCY_BIWEEKLY:
				case ReportScheduleInterface::FREQUENCY_WEEKLY:
					$day     = null;
					$month   = null;
					$weekDay = in_array ($schedule ['day'], array (ReportScheduleInterface::WEEKDAY_SUNDAY, ReportScheduleInterface::WEEKDAY_MONDAY, ReportScheduleInterface::WEEKDAY_TUESDAY, ReportScheduleInterface::WEEKDAY_WEDNESDAY, ReportScheduleInterface::WEEKDAY_THURSDAY, ReportScheduleInterface::WEEKDAY_FRIDAY, ReportScheduleInterface::WEEKDAY_SATURDAY)) ? $schedule ['day'] : null;
					break;
				case ReportScheduleInterface::FREQUENCY_DAILY:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_MONTHLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_YEARLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = in_array ($schedule ['month'], array (ReportScheduleInterface::MONTH_JANUARY, ReportScheduleInterface::MONTH_FEBRUARY, ReportScheduleInterface::MONTH_MARCH, ReportScheduleInterface::MONTH_APRIL, ReportScheduleInterface::MONTH_MAY, ReportScheduleInterface::MONTH_JUNE, ReportScheduleInterface::MONTH_JULY, ReportScheduleInterface::MONTH_AUGUST, ReportScheduleInterface::MONTH_SEPTEMBER, ReportScheduleInterface::MONTH_OCTOBER, ReportScheduleInterface::MONTH_NOVEMBER, ReportScheduleInterface::MONTH_DECEMBER)) ? $schedule ['month'] : null;
					$weekDay = null;
					break;
				default:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
			}

			$recipients = !empty ($row ['recipients']) ? json_decode ($row ['recipients'], true) : null;
			$this->assertNotEmpty ($recipients, 'Saved report schedule recipients should not be empty');

			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report scheduling report IDs do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_EXCEL, $row ['format'], 'Saved report scheduling formats do not match');
			$this->assertEquals (array (23, 56), $recipients ['groups'], 'Saved report schedule groups do not match');
			$this->assertEquals (array (), $recipients ['roles'], 'Saved report schedule roles do not match');
			$this->assertEquals (array ('H25'), $recipients ['rs'], 'Saved report schedule roles and subordinates do not match');
			$this->assertEquals (array (), $recipients ['users'], 'Saved report schedule users do not match');
			$this->assertEquals ('06:00', $schedule ['time'], 'Saved report scheduling days do not match');
			$this->assertEquals (25, $day, 'Saved report scheduling days do not match');
			$this->assertEquals (null, $month, 'Saved report scheduling months do not match');
			$this->assertEquals (null, $weekDay, 'Saved report scheduling weekdays do not match');
		}

		/**
		 * Crear un reporte tabular con programación de envío anual
		 * @depends testCreateFolder
		 */
		public function testCreateTabularReportWithYearlyScheduling () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report with yearly scheduling';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setSchedule (
					ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_YEARLY, '06:00', 5, ReportScheduleInterface::MONTH_JULY)
						->setGroups (array (23, 56))
						->setFormat (ReportScheduleInterface::FORMAT_EXCEL)
						->setRolesAndSubordinates (array ('H25'))
				)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que tiene asociada la información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');

			$row      = self::$adb->fetchByAssoc ($result, -1, false);
			$schedule = !empty ($row ['schedule']) ? json_decode ($row ['schedule'], true) : null;
			$this->assertNotEmpty ($schedule, 'Saved report schedule data should not be empty');
			switch ($schedule ['scheduletype']) {
				case ReportScheduleInterface::FREQUENCY_BIWEEKLY:
				case ReportScheduleInterface::FREQUENCY_WEEKLY:
					$day     = null;
					$month   = null;
					$weekDay = in_array ($schedule ['day'], array (ReportScheduleInterface::WEEKDAY_SUNDAY, ReportScheduleInterface::WEEKDAY_MONDAY, ReportScheduleInterface::WEEKDAY_TUESDAY, ReportScheduleInterface::WEEKDAY_WEDNESDAY, ReportScheduleInterface::WEEKDAY_THURSDAY, ReportScheduleInterface::WEEKDAY_FRIDAY, ReportScheduleInterface::WEEKDAY_SATURDAY)) ? $schedule ['day'] : null;
					break;
				case ReportScheduleInterface::FREQUENCY_DAILY:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_MONTHLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_YEARLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = in_array ($schedule ['month'], array (ReportScheduleInterface::MONTH_JANUARY, ReportScheduleInterface::MONTH_FEBRUARY, ReportScheduleInterface::MONTH_MARCH, ReportScheduleInterface::MONTH_APRIL, ReportScheduleInterface::MONTH_MAY, ReportScheduleInterface::MONTH_JUNE, ReportScheduleInterface::MONTH_JULY, ReportScheduleInterface::MONTH_AUGUST, ReportScheduleInterface::MONTH_SEPTEMBER, ReportScheduleInterface::MONTH_OCTOBER, ReportScheduleInterface::MONTH_NOVEMBER, ReportScheduleInterface::MONTH_DECEMBER)) ? $schedule ['month'] : null;
					$weekDay = null;
					break;
				default:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
			}

			$recipients = !empty ($row ['recipients']) ? json_decode ($row ['recipients'], true) : null;
			$this->assertNotEmpty ($recipients, 'Saved report schedule recipients should not be empty');

			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report scheduling report IDs do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_EXCEL, $row ['format'], 'Saved report scheduling formats do not match');
			$this->assertEquals (array (23, 56), $recipients ['groups'], 'Saved report schedule groups do not match');
			$this->assertEquals (array (), $recipients ['roles'], 'Saved report schedule roles do not match');
			$this->assertEquals (array ('H25'), $recipients ['rs'], 'Saved report schedule roles and subordinates do not match');
			$this->assertEquals (array (), $recipients ['users'], 'Saved report schedule users do not match');
			$this->assertEquals ('06:00', $schedule ['time'], 'Saved report scheduling days do not match');
			$this->assertEquals (5, $day, 'Saved report scheduling days do not match');
			$this->assertEquals (ReportScheduleInterface::MONTH_JULY, $month, 'Saved report scheduling months do not match');
			$this->assertEquals (null, $weekDay, 'Saved report scheduling weekdays do not match');
		}

		/**
		 * Crear un reporte sumarizado con el mínimo de información
		 * @depends testCreateFolder
		 */
		public function testCreateMinimalSummaryReport () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Summary report';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_SUMMARY, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que no existe información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');
		}

		/**
		 * Crear un reporte sumarizado con columnas de ordenamiento
		 * @depends testCreateFolder
		 */
		public function testCreateSummaryReportWithSortColumns () {
			$folderId          = 1;
			$moduleName        = 'test_module';
			$reportDescription = 'My report description';
			$reportName        = 'Tabular report with sort columns';
			$tableName         = 'vtiger_test_module';
			/** @var Field[] $fields */
			$fields      = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATE)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$folder      = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1)->setSortOrder (ReportColumnInterface::SORT_ORDER_DESCENDING),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3)->setSortOrder (ReportColumnInterface::SORT_ORDER_DESCENDING),
			);
			$report      = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setSortColumns (array ($columns [3], $columns [1]))
				->setStatus (ReportInterface::STATUS_SAVED)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_PUBLIC, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_SUMMARY, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals (null, $row ['secondarymodules'], 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');

			// Verificar que se creó la primera columna de ordenamiento
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getModuleName ()}_{$label}:{$fields [3]->getName ()}:{$fields [3]->getDataType ()}";
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
			$this->assertEquals (1, $row ['sortcolid'], 'Saved report sort column sequences do not match');
			$this->assertEquals ($columnName, $row ['columnname'], 'Saved report sort column names do not match');
			$this->assertEquals (ReportColumnInterface::SORT_ORDER_DESCENDING, $row ['sortorder'], 'Saved report sort column orderings do not match');

			// Verificar que se creó la segunda columna de ordenamiento
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [1]->getLabel ());
			$columnName = "{$fields [1]->getTableName ()}:{$fields [1]->getColumnName ()}:{$fields [1]->getModuleName ()}_{$label}:{$fields [1]->getName ()}:{$fields [1]->getDataType ()}";
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
			$this->assertEquals (2, $row ['sortcolid'], 'Saved report sort column sequences do not match');
			$this->assertEquals ($columnName, $row ['columnname'], 'Saved report sort column names do not match');
			$this->assertEquals (ReportColumnInterface::SORT_ORDER_DESCENDING, $row ['sortorder'], 'Saved report sort column orderings do not match');

			// Verificar que se creó la tercera columna de ordenamiento (Columna por defecto)
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
			$this->assertEquals (3, $row ['sortcolid'], 'Saved report sort column sequences do not match');
			$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
			$this->assertEquals (ReportColumnInterface::SORT_ORDER_ASCENDING, $row ['sortorder'], 'Saved report sort column orderings do not match');

			// Verificar que se creó la columna de agrupamiento
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getModuleName ()}_{$label}:{$fields [3]->getName ()}:{$fields [3]->getDataType ()}";
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report group by report IDs do not match');
			$this->assertEquals (1, $row ['sortid'], 'Saved report group by sequences do not match');
			$this->assertEquals ($columnName, $row ['sortcolname'], 'Saved report group by column names do not match');
			$this->assertEquals ('None', $row ['dategroupbycriteria'], 'Saved report group by criterias do not match');

			// Verificar que no tiene columnas de totalización asociadas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que no tiene filtro estándar asociado
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que no tiene filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que no existe información de compartir en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que no existe información de programación de envíos en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');
		}

		/**
		 * Crear un reporte tabular con todos los juguetes
		 * @depends testCreateFolder
		 */
		public function testCreateFullTabularReport () {
			$folderId           = 1;
			$fieldLabels        = array ('My text field', 'My date field', 'My related text field', 'My related date field', 'Another related text field', 'Another related date field');
			$fieldTypes         = array (FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_DATE, FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_DATE, FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_DATE);
			$fieldNames         = array ('text_field', 'date_field', 'related_text_field', 'related_date_field', 'another_text_field', 'another_date_field');
			$moduleNames        = array ('test_module', 'test_module', 'related_module_one', 'related_module_one', 'related_module_two', 'related_module_two');
			$tableNames         = array ('vtiger_test_module', 'vtiger_test_module', 'vtiger_related_module_one', 'vtiger_related_module_one', 'vtiger_related_module_two', 'vtiger_related_module_two');
			$reportDescription  = 'My report description';
			$reportName         = 'Full tabular report';
			$relatedModuleNames = array ('related_module_one', 'related_module_two');

			/** @var Field[] $fields */
			$fields = array ();
			foreach ($fieldNames as $index => $fieldName) {
				$fields [] = Field::getInstance ()
					->setUiType ($fieldTypes [ $index ])
					->setColumnName ($fieldName)
					->setName ($fieldName)
					->setLabel ($fieldLabels [ $index ])
					->setModuleName ($moduleNames [ $index ])
					->setTableName ($tableNames [ $index ]);
			}
			$folder = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			/** @var ReportColumn[] $columns */
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
				ReportColumn::getInstance ($fields [4])->setSequence (4),
				ReportColumn::getInstance ($fields [5])->setSequence (5),
			);
			$report      = Report::getInstance ()
				->setAdvancedFilterGroups (array (
					ReportAdvancedFilterGroup::getInstance ()
						->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)
						->setSequence (0)
						->setFilters (array (
							ReportAdvancedFilter::getInstance ($fields [0])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
								->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)
								->setSequence (0)
								->setValue ('COD-0001'),
							ReportAdvancedFilter::getInstance ($fields [1])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS)
								->setSequence (1)
								->setValue ('TEST'),
						)),
					ReportAdvancedFilterGroup::getInstance ()
						->setSequence (1)
						->setFilters (array (
							ReportAdvancedFilter::getInstance ($fields [2])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_GREATER)
								->setOperator (ReportAdvancedFilterInterface::OPERATOR_OR)
								->setSequence (2)
								->setValue (0),
							ReportAdvancedFilter::getInstance ($fields [3])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS)
								->setSequence (3)
								->setValue ('2017-12-31'),
						)),
				))
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleNames [0])
				->setName ($reportName)
				->setOwner (1)
				->setRelatedModuleNames ($relatedModuleNames)
				->setSchedule (
					ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_BIWEEKLY, '06:00', ReportScheduleInterface::WEEKDAY_MONDAY)
						->setGroups (array (23, 56))
						->setFormat (ReportScheduleInterface::FORMAT_EXCEL)
						->setRolesAndSubordinates (array ('H25'))
				)
				->setShareWith (array (
					ReportSharingEntity::getInstance ()->setId (15)->setType (ReportSharingEntityInterface::TYPE_USER),
					ReportSharingEntity::getInstance ()->setId (10)->setType (ReportSharingEntityInterface::TYPE_GROUP),
				))
				->setSortColumns (array (
					$columns [3]->duplicate (null)->setSortOrder (ReportColumnInterface::SORT_ORDER_DESCENDING),
					$columns [0]->duplicate (null)->setSortOrder (ReportColumnInterface::SORT_ORDER_DESCENDING),
				))
				->setStandardFilter (ReportStandardFilter::getInstance ($fields [3])->setEndDate ('2018-12-31')->setPeriod (ReportStandardFilterInterface::PERIOD_CURRENT_YEAR)->setStartDate ('2018-01-01'))
				->setStatus (ReportInterface::STATUS_SAVED)
				->setTotalColumns (array (
					$columns [1]->duplicate (null)->setTotalsOperation (ReportColumnInterface::TOTALS_OPERATION_AVERAGE),
					$columns [3]->duplicate (null)->setTotalsOperation (ReportColumnInterface::TOTALS_OPERATION_MINIMUM),
				))
				->setType (ReportInterface::TYPE_TABULAR)
				->setVisibility (ReportInterface::VISIBILITY_SHARED);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_SHARED, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleNames [0], $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals ($relatedModuleNames, explode (':', $row ['secondarymodules']), 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento por defecto
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');
			$index = 1;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
				$this->assertEquals ($index, $row ['sortcolid'], 'Saved report sort column sequences do not match');
				$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
				$this->assertEquals ('Ascending', $row ['sortorder'], 'Saved report sort column orderings do not match');
				$index++;
			}
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			// Verificar tiene asociadas las columnas de totalización
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Saved report total columns count do not match');
			$index        = 0;
			$totalColumns = $report->getTotalColumns ();
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$operation = $totalColumns [ $index ]->getTotalsOperation ();
				switch ($operation) {
					case ReportColumnInterface::TOTALS_OPERATION_AVERAGE:
						$dummy = 3;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_MAXIMUM:
						$dummy = 5;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_MINIMUM:
						$dummy = 4;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_SUM:
						$dummy = 2;
						break;
					default:
						$dummy = 1;
						break;
				}
				$columnName = "cb:{$totalColumns [$index]->getTableName ()}:{$totalColumns [$index]->getColumnName ()}:{$totalColumns [$index]->getLabel ()}_{$totalColumns [$index]->getTotalsOperation ()}:{$dummy}";
				$this->assertEquals ($reportId, $row ['reportsummaryid'], 'Total column report ID should not be empty');
				$this->assertEquals (($index + 1), $row ['summarytype'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar tiene asociado el filtro estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getName ()}:{$fields [3]->getModuleName ()}_{$label}";
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['datefilterid'], 'Saved report standard filter report IDs do not match');
			$this->assertEquals ($columnName, $row ['datecolumnname'], 'Saved report standard filter column names do not match');
			$this->assertEquals (ReportStandardFilterInterface::PERIOD_CURRENT_YEAR, $row ['datefilter'], 'Saved report standard filter periods do not match');
			$this->assertEquals ('2018-12-31', $row ['enddate'], 'Saved report standard filter end dates do not match');
			$this->assertEquals ('2018-01-01', $row ['startdate'], 'Saved report standard filter start dates do not match');

			// Verificar que tiene asociados los grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que se almacenaron correctamente los datos del primer grupo
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('and', $row ['group_condition'], 'Group operators do not match');
			$this->assertEquals (' 0 and 1 ', $row ['condition_expression'], 'Group condition expresions do not match');

			// Verificar que se almacenaron correctamente los datos del segundo grupo
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('', $row ['group_condition'], 'Group operators do not match');
			$this->assertEquals (' 2 or 3 ', $row ['condition_expression'], 'Group condition expresions do not match');

			// Verificar que se crearon los filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'Saved report advanced filters count do not match');

			// Verificar que se almacenaron correctamente los datos del primer filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [0]->getLabel ());
			$columnName = "{$fields [0]->getTableName ()}:{$fields [0]->getColumnName ()}:{$fields [0]->getModuleName ()}_{$label}:{$fields [0]->getName ()}:{$fields [0]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (0, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_EQUALS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('COD-0001', $row ['value'], 'Values do not match');
			$this->assertEquals (0, $row ['groupid'], 'Values do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_AND, $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del segundo filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [1]->getLabel ());
			$columnName = "{$fields [1]->getTableName ()}:{$fields [1]->getColumnName ()}:{$fields [1]->getModuleName ()}_{$label}:{$fields [1]->getName ()}:{$fields [1]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (1, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('TEST', $row ['value'], 'Values do not match');
			$this->assertEquals (0, $row ['groupid'], 'Values do not match');
			$this->assertEquals ('', $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del tercer filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [2]->getLabel ());
			$columnName = "{$fields [2]->getTableName ()}:{$fields [2]->getColumnName ()}:{$fields [2]->getModuleName ()}_{$label}:{$fields [2]->getName ()}:{$fields [2]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (2, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_GREATER, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals (0, $row ['value'], 'Values do not match');
			$this->assertEquals (1, $row ['groupid'], 'Values do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_OR, $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del cuarto filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getModuleName ()}_{$label}:{$fields [3]->getName ()}:{$fields [3]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (3, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('2017-12-31', $row ['value'], 'Values do not match');
			$this->assertEquals (1, $row ['groupid'], 'Values do not match');
			$this->assertEquals ('', $row ['column_condition'], 'Values do not match');

			// Verificar se almacenó correctamente la información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que se almacenaron correctamente los datos de la primera entidad
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sharing entities report IDs do not match');
			$this->assertEquals (15, $row ['shareid'], 'Saved report sharing entity IDs do not match');
			$this->assertEquals (ReportSharingEntityInterface::TYPE_USER, $row ['setype'], 'Saved report sharing types do not match');

			// Verificar que se almacenaron correctamente los datos de la segunda entidad
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sharing entities report IDs do not match');
			$this->assertEquals (10, $row ['shareid'], 'Saved report sharing entity IDs do not match');
			$this->assertEquals (ReportSharingEntityInterface::TYPE_GROUP, $row ['setype'], 'Saved report sharing types do not match');

			// Verificar que tiene asociada la información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');

			$row      = self::$adb->fetchByAssoc ($result, -1, false);
			$schedule = !empty ($row ['schedule']) ? json_decode ($row ['schedule'], true) : null;
			$this->assertNotEmpty ($schedule, 'Saved report schedule data should not be empty');
			switch ($schedule ['scheduletype']) {
				case ReportScheduleInterface::FREQUENCY_BIWEEKLY:
				case ReportScheduleInterface::FREQUENCY_WEEKLY:
					$day     = null;
					$month   = null;
					$weekDay = in_array ($schedule ['day'], array (ReportScheduleInterface::WEEKDAY_SUNDAY, ReportScheduleInterface::WEEKDAY_MONDAY, ReportScheduleInterface::WEEKDAY_TUESDAY, ReportScheduleInterface::WEEKDAY_WEDNESDAY, ReportScheduleInterface::WEEKDAY_THURSDAY, ReportScheduleInterface::WEEKDAY_FRIDAY, ReportScheduleInterface::WEEKDAY_SATURDAY)) ? $schedule ['day'] : null;
					break;
				case ReportScheduleInterface::FREQUENCY_DAILY:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_MONTHLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_YEARLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = in_array ($schedule ['month'], array (ReportScheduleInterface::MONTH_JANUARY, ReportScheduleInterface::MONTH_FEBRUARY, ReportScheduleInterface::MONTH_MARCH, ReportScheduleInterface::MONTH_APRIL, ReportScheduleInterface::MONTH_MAY, ReportScheduleInterface::MONTH_JUNE, ReportScheduleInterface::MONTH_JULY, ReportScheduleInterface::MONTH_AUGUST, ReportScheduleInterface::MONTH_SEPTEMBER, ReportScheduleInterface::MONTH_OCTOBER, ReportScheduleInterface::MONTH_NOVEMBER, ReportScheduleInterface::MONTH_DECEMBER)) ? $schedule ['month'] : null;
					$weekDay = null;
					break;
				default:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
			}

			$recipients = !empty ($row ['recipients']) ? json_decode ($row ['recipients'], true) : null;
			$this->assertNotEmpty ($recipients, 'Saved report schedule recipients should not be empty');

			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report scheduling report IDs do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_EXCEL, $row ['format'], 'Saved report scheduling formats do not match');
			$this->assertEquals (array (23, 56), $recipients ['groups'], 'Saved report schedule groups do not match');
			$this->assertEquals (array (), $recipients ['roles'], 'Saved report schedule roles do not match');
			$this->assertEquals (array ('H25'), $recipients ['rs'], 'Saved report schedule roles and subordinates do not match');
			$this->assertEquals (array (), $recipients ['users'], 'Saved report schedule users do not match');
			$this->assertEquals ('06:00', $schedule ['time'], 'Saved report scheduling days do not match');
			$this->assertEquals (null, $day, 'Saved report scheduling days do not match');
			$this->assertEquals (null, $month, 'Saved report scheduling months do not match');
			$this->assertEquals (ReportScheduleInterface::WEEKDAY_MONDAY, $weekDay, 'Saved report scheduling weekdays do not match');
		}

		/**
		 * Crear un reporte sumarizado con todos los juguetes
		 * @depends testCreateFolder
		 */
		public function testCreateFullSummaryReport () {
			$folderId           = 1;
			$fieldLabels        = array ('My text field', 'My date field', 'My related text field', 'My related date field', 'Another related text field', 'Another related date field');
			$fieldTypes         = array (FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_DATE, FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_DATE, FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_DATE);
			$fieldNames         = array ('text_field', 'date_field', 'related_text_field', 'related_date_field', 'another_text_field', 'another_date_field');
			$moduleNames        = array ('test_module', 'test_module', 'related_module_one', 'related_module_one', 'related_module_two', 'related_module_two');
			$tableNames         = array ('vtiger_test_module', 'vtiger_test_module', 'vtiger_related_module_one', 'vtiger_related_module_one', 'vtiger_related_module_two', 'vtiger_related_module_two');
			$reportDescription  = 'My report description';
			$reportName         = 'Full summary report';
			$relatedModuleNames = array ('related_module_one', 'related_module_two');

			/** @var Field[] $fields */
			$fields = array ();
			foreach ($fieldNames as $index => $fieldName) {
				$fields [] = Field::getInstance ()
					->setUiType ($fieldTypes [ $index ])
					->setColumnName ($fieldName)
					->setName ($fieldName)
					->setLabel ($fieldLabels [ $index ])
					->setModuleName ($moduleNames [ $index ])
					->setTableName ($tableNames [ $index ]);
			}
			$folder = ReportsManager::getInstance (self::$adb)->fetchFolderById ($folderId);
			/** @var ReportColumn[] $columns */
			$columns     = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0),
				ReportColumn::getInstance ($fields [1])->setSequence (1),
				ReportColumn::getInstance ($fields [2])->setSequence (2),
				ReportColumn::getInstance ($fields [3])->setSequence (3),
				ReportColumn::getInstance ($fields [4])->setSequence (4),
				ReportColumn::getInstance ($fields [5])->setSequence (5),
			);
			$report      = Report::getInstance ()
				->setAdvancedFilterGroups (array (
					ReportAdvancedFilterGroup::getInstance ()
						->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)
						->setSequence (0)
						->setFilters (array (
							ReportAdvancedFilter::getInstance ($fields [0])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
								->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)
								->setSequence (0)
								->setValue ('COD-0001'),
							ReportAdvancedFilter::getInstance ($fields [1])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS)
								->setSequence (1)
								->setValue ('TEST'),
						)),
					ReportAdvancedFilterGroup::getInstance ()
						->setSequence (1)
						->setFilters (array (
							ReportAdvancedFilter::getInstance ($fields [2])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_GREATER)
								->setOperator (ReportAdvancedFilterInterface::OPERATOR_OR)
								->setSequence (2)
								->setValue (0),
							ReportAdvancedFilter::getInstance ($fields [3])
								->setComparator (ReportAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS)
								->setSequence (3)
								->setValue ('2017-12-31'),
						)),
				))
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns ($columns)
				->setDescription ($reportDescription)
				->setFolder ($folder)
				->setModuleName ($moduleNames [0])
				->setName ($reportName)
				->setOwner (1)
				->setRelatedModuleNames ($relatedModuleNames)
				->setSchedule (
					ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_BIWEEKLY, '06:00', ReportScheduleInterface::WEEKDAY_MONDAY)
						->setGroups (array (23, 56))
						->setFormat (ReportScheduleInterface::FORMAT_EXCEL)
						->setRolesAndSubordinates (array ('H25'))
				)
				->setShareWith (array (
					ReportSharingEntity::getInstance ()->setId (15)->setType (ReportSharingEntityInterface::TYPE_USER),
					ReportSharingEntity::getInstance ()->setId (10)->setType (ReportSharingEntityInterface::TYPE_GROUP),
				))
				->setSortColumns (array (
					$columns [3]->duplicate (null)->setSortOrder (ReportColumnInterface::SORT_ORDER_DESCENDING),
					$columns [0]->duplicate (null)->setSortOrder (ReportColumnInterface::SORT_ORDER_DESCENDING),
				))
				->setStandardFilter (ReportStandardFilter::getInstance ($fields [3])->setEndDate ('2018-12-31')->setPeriod (ReportStandardFilterInterface::PERIOD_CURRENT_YEAR)->setStartDate ('2018-01-01'))
				->setStatus (ReportInterface::STATUS_SAVED)
				->setTotalColumns (array (
					$columns [1]->duplicate (null)->setTotalsOperation (ReportColumnInterface::TOTALS_OPERATION_AVERAGE),
					$columns [3]->duplicate (null)->setTotalsOperation (ReportColumnInterface::TOTALS_OPERATION_MINIMUM),
				))
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_SHARED);
			$savedReport = ReportsManager::getInstance (self::$adb)->saveReport ($report);

			$this->assertNotNull ($savedReport, 'Saved report should not be null');
			$this->assertNotEmpty ($savedReport->getId (), 'Report ID should not be empty');

			// Verificar que se creó el reporte en la base de datos
			$reportId = $savedReport->getId ();
			$result   = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se almacenaron correctamente los datos del reporte en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Report IDs do not match');
			$this->assertEquals ($reportId, $row ['queryid'], 'Report IDs do not match');
			$this->assertEquals ($reportName, $row ['reportname'], 'Report names do not match');
			$this->assertEquals ($reportDescription, $row ['description'], 'Report descriptions do not match');
			$this->assertEquals ($folderId, $row ['folderid'], 'Report folder IDs do not match');
			$this->assertEquals (1, $row ['owner'], 'Report owner IDs do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_SHARED, $row ['sharingtype'], 'Report visibilities do not match');
			$this->assertEquals (ReportInterface::TYPE_SUMMARY, $row ['reporttype'], 'Report types do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $row ['state'], 'Report statuses do not match');
			$this->assertEquals (array ('crm', 'facturacion'), json_decode ($row ['applicationcodes']), 'Application codes do not match');

			// Verificar que se almacenaron correctamente los datos de los módulos del reporte en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report modules count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleNames [0], $row ['primarymodule'], 'Report primary module names do not match');
			$this->assertEquals ($relatedModuleNames, explode (':', $row ['secondarymodules']), 'Report secondary module names do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (count ($fields), self::$adb->num_rows ($result), 'Saved report columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getName ()}:{$fields [$index]->getDataType ()}";
				$this->assertEquals ($reportId, $row ['queryid'], 'Column report ID should not be empty');
				$this->assertEquals ($index, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que tiene asociadas las columnas de ordenamiento
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');

			// Verificar que se creó la primera columna de ordenamiento
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getModuleName ()}_{$label}:{$fields [3]->getName ()}:{$fields [3]->getDataType ()}";
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
			$this->assertEquals (1, $row ['sortcolid'], 'Saved report sort column sequences do not match');
			$this->assertEquals ($columnName, $row ['columnname'], 'Saved report sort column names do not match');
			$this->assertEquals (ReportColumnInterface::SORT_ORDER_DESCENDING, $row ['sortorder'], 'Saved report sort column orderings do not match');

			// Verificar que se creó la segunda columna de ordenamiento
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [0]->getLabel ());
			$columnName = "{$fields [0]->getTableName ()}:{$fields [0]->getColumnName ()}:{$fields [0]->getModuleName ()}_{$label}:{$fields [0]->getName ()}:{$fields [0]->getDataType ()}";
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
			$this->assertEquals (2, $row ['sortcolid'], 'Saved report sort column sequences do not match');
			$this->assertEquals ($columnName, $row ['columnname'], 'Saved report sort column names do not match');
			$this->assertEquals (ReportColumnInterface::SORT_ORDER_DESCENDING, $row ['sortorder'], 'Saved report sort column orderings do not match');

			// Verificar que se creó la tercera columna de ordenamiento (Columna por defecto)
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sort column report IDs do not match');
			$this->assertEquals (3, $row ['sortcolid'], 'Saved report sort column sequences do not match');
			$this->assertEquals ('none', $row ['columnname'], 'Saved report sort column names do not match');
			$this->assertEquals (ReportColumnInterface::SORT_ORDER_ASCENDING, $row ['sortorder'], 'Saved report sort column orderings do not match');

			// Verificar que se creó la columna de agrupamiento
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report group by columns count do not match');

			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getModuleName ()}_{$label}:{$fields [3]->getName ()}:{$fields [3]->getDataType ()}";
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report group by report IDs do not match');
			$this->assertEquals (1, $row ['sortid'], 'Saved report group by sequences do not match');
			$this->assertEquals ($columnName, $row ['sortcolname'], 'Saved report group by column names do not match');
			$this->assertEquals ('None', $row ['dategroupbycriteria'], 'Saved report group by criterias do not match');

			// Verificar tiene asociadas las columnas de totalización
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Saved report total columns count do not match');
			$index        = 0;
			$totalColumns = $report->getTotalColumns ();
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$operation = $totalColumns [ $index ]->getTotalsOperation ();
				switch ($operation) {
					case ReportColumnInterface::TOTALS_OPERATION_AVERAGE:
						$dummy = 3;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_MAXIMUM:
						$dummy = 5;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_MINIMUM:
						$dummy = 4;
						break;
					case ReportColumnInterface::TOTALS_OPERATION_SUM:
						$dummy = 2;
						break;
					default:
						$dummy = 1;
						break;
				}
				$columnName = "cb:{$totalColumns [$index]->getTableName ()}:{$totalColumns [$index]->getColumnName ()}:{$totalColumns [$index]->getLabel ()}_{$totalColumns [$index]->getTotalsOperation ()}:{$dummy}";
				$this->assertEquals ($reportId, $row ['reportsummaryid'], 'Total column report ID should not be empty');
				$this->assertEquals (($index + 1), $row ['summarytype'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar tiene asociado el filtro estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getName ()}:{$fields [3]->getModuleName ()}_{$label}";
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['datefilterid'], 'Saved report standard filter report IDs do not match');
			$this->assertEquals ($columnName, $row ['datecolumnname'], 'Saved report standard filter column names do not match');
			$this->assertEquals (ReportStandardFilterInterface::PERIOD_CURRENT_YEAR, $row ['datefilter'], 'Saved report standard filter periods do not match');
			$this->assertEquals ('2018-12-31', $row ['enddate'], 'Saved report standard filter end dates do not match');
			$this->assertEquals ('2018-01-01', $row ['startdate'], 'Saved report standard filter start dates do not match');

			// Verificar que tiene asociados los grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que se almacenaron correctamente los datos del primer grupo
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('and', $row ['group_condition'], 'Group operators do not match');
			$this->assertEquals (' 0 and 1 ', $row ['condition_expression'], 'Group condition expresions do not match');

			// Verificar que se almacenaron correctamente los datos del segundo grupo
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('', $row ['group_condition'], 'Group operators do not match');
			$this->assertEquals (' 2 or 3 ', $row ['condition_expression'], 'Group condition expresions do not match');

			// Verificar que se crearon los filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'Saved report advanced filters count do not match');

			// Verificar que se almacenaron correctamente los datos del primer filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [0]->getLabel ());
			$columnName = "{$fields [0]->getTableName ()}:{$fields [0]->getColumnName ()}:{$fields [0]->getModuleName ()}_{$label}:{$fields [0]->getName ()}:{$fields [0]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (0, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_EQUALS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('COD-0001', $row ['value'], 'Values do not match');
			$this->assertEquals (0, $row ['groupid'], 'Values do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_AND, $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del segundo filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [1]->getLabel ());
			$columnName = "{$fields [1]->getTableName ()}:{$fields [1]->getColumnName ()}:{$fields [1]->getModuleName ()}_{$label}:{$fields [1]->getName ()}:{$fields [1]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (1, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('TEST', $row ['value'], 'Values do not match');
			$this->assertEquals (0, $row ['groupid'], 'Values do not match');
			$this->assertEquals ('', $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del tercer filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [2]->getLabel ());
			$columnName = "{$fields [2]->getTableName ()}:{$fields [2]->getColumnName ()}:{$fields [2]->getModuleName ()}_{$label}:{$fields [2]->getName ()}:{$fields [2]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (2, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_GREATER, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals (0, $row ['value'], 'Values do not match');
			$this->assertEquals (1, $row ['groupid'], 'Values do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_OR, $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del cuarto filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getModuleName ()}_{$label}:{$fields [3]->getName ()}:{$fields [3]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (3, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('2017-12-31', $row ['value'], 'Values do not match');
			$this->assertEquals (1, $row ['groupid'], 'Values do not match');
			$this->assertEquals ('', $row ['column_condition'], 'Values do not match');

			// Verificar se almacenó correctamente la información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que se almacenaron correctamente los datos de la primera entidad
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sharing entities report IDs do not match');
			$this->assertEquals (15, $row ['shareid'], 'Saved report sharing entity IDs do not match');
			$this->assertEquals (ReportSharingEntityInterface::TYPE_USER, $row ['setype'], 'Saved report sharing types do not match');

			// Verificar que se almacenaron correctamente los datos de la segunda entidad
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report sharing entities report IDs do not match');
			$this->assertEquals (10, $row ['shareid'], 'Saved report sharing entity IDs do not match');
			$this->assertEquals (ReportSharingEntityInterface::TYPE_GROUP, $row ['setype'], 'Saved report sharing types do not match');

			// Verificar que tiene asociada la información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');

			$row      = self::$adb->fetchByAssoc ($result, -1, false);
			$schedule = !empty ($row ['schedule']) ? json_decode ($row ['schedule'], true) : null;
			$this->assertNotEmpty ($schedule, 'Saved report schedule data should not be empty');
			switch ($schedule ['scheduletype']) {
				case ReportScheduleInterface::FREQUENCY_BIWEEKLY:
				case ReportScheduleInterface::FREQUENCY_WEEKLY:
					$day     = null;
					$month   = null;
					$weekDay = in_array ($schedule ['day'], array (ReportScheduleInterface::WEEKDAY_SUNDAY, ReportScheduleInterface::WEEKDAY_MONDAY, ReportScheduleInterface::WEEKDAY_TUESDAY, ReportScheduleInterface::WEEKDAY_WEDNESDAY, ReportScheduleInterface::WEEKDAY_THURSDAY, ReportScheduleInterface::WEEKDAY_FRIDAY, ReportScheduleInterface::WEEKDAY_SATURDAY)) ? $schedule ['day'] : null;
					break;
				case ReportScheduleInterface::FREQUENCY_DAILY:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_MONTHLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = null;
					$weekDay = null;
					break;
				case ReportScheduleInterface::FREQUENCY_YEARLY:
					$day     = ($schedule ['date'] !== null) && (is_int ($schedule ['date'])) && (0 < $schedule ['date']) && ($schedule ['date'] < 32) ? $schedule ['date'] : null;
					$month   = in_array ($schedule ['month'], array (ReportScheduleInterface::MONTH_JANUARY, ReportScheduleInterface::MONTH_FEBRUARY, ReportScheduleInterface::MONTH_MARCH, ReportScheduleInterface::MONTH_APRIL, ReportScheduleInterface::MONTH_MAY, ReportScheduleInterface::MONTH_JUNE, ReportScheduleInterface::MONTH_JULY, ReportScheduleInterface::MONTH_AUGUST, ReportScheduleInterface::MONTH_SEPTEMBER, ReportScheduleInterface::MONTH_OCTOBER, ReportScheduleInterface::MONTH_NOVEMBER, ReportScheduleInterface::MONTH_DECEMBER)) ? $schedule ['month'] : null;
					$weekDay = null;
					break;
				default:
					$day     = null;
					$month   = null;
					$weekDay = null;
					break;
			}

			$recipients = !empty ($row ['recipients']) ? json_decode ($row ['recipients'], true) : null;
			$this->assertNotEmpty ($recipients, 'Saved report schedule recipients should not be empty');

			$this->assertEquals ($reportId, $row ['reportid'], 'Saved report scheduling report IDs do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_EXCEL, $row ['format'], 'Saved report scheduling formats do not match');
			$this->assertEquals (array (23, 56), $recipients ['groups'], 'Saved report schedule groups do not match');
			$this->assertEquals (array (), $recipients ['roles'], 'Saved report schedule roles do not match');
			$this->assertEquals (array ('H25'), $recipients ['rs'], 'Saved report schedule roles and subordinates do not match');
			$this->assertEquals (array (), $recipients ['users'], 'Saved report schedule users do not match');
			$this->assertEquals ('06:00', $schedule ['time'], 'Saved report scheduling days do not match');
			$this->assertEquals (null, $day, 'Saved report scheduling days do not match');
			$this->assertEquals (null, $month, 'Saved report scheduling months do not match');
			$this->assertEquals (ReportScheduleInterface::WEEKDAY_MONDAY, $weekDay, 'Saved report scheduling weekdays do not match');
		}

		/**
		 * Intentar obtener un reporte no existente
		 * @depends testCreateMinimalTabularReport
		 */
		public function testFetchNonExistingReport () {
			$this->assertNull (ReportsManager::getInstance (self::$adb)->fetchReport ('test_module', 'unknown_report'));
			$this->assertNull (ReportsManager::getInstance (self::$adb)->fetchReport ('unknown_module', 'Tabular report'));
		}

		/**
		 * Obtener un reporte tabular existente
		 * @depends testCreateFullTabularReport
		 */
		public function testFetchFullTabularReport () {
			$moduleName = 'test_module';
			$reportName = 'Full tabular report';
			$report     = ReportsManager::getInstance (self::$adb)->fetchReport ($moduleName, $reportName);
			$this->assertNotNull ($report, 'Report should not be null');
			$this->assertNotEmpty ($report->getId (), 'Report ID should not be empty');

			$folderId           = 1;
			$fieldLabels        = array ('My text field', 'My date field', 'My related text field', 'My related date field', 'Another related text field', 'Another related date field');
			$fieldDataTypes     = array (FieldInterface::DATA_TYPE_VARCHAR, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_VARCHAR, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_VARCHAR, FieldInterface::DATA_TYPE_DATE);
			$fieldNames         = array ('text_field', 'date_field', 'related_text_field', 'related_date_field', 'another_text_field', 'another_date_field');
			$moduleNames        = array ('test_module', 'test_module', 'related_module_one', 'related_module_one', 'related_module_two', 'related_module_two');
			$tableNames         = array ('vtiger_test_module', 'vtiger_test_module', 'vtiger_related_module_one', 'vtiger_related_module_one', 'vtiger_related_module_two', 'vtiger_related_module_two');
			$reportDescription  = 'My report description';
			$reportName         = 'Full tabular report';
			$relatedModuleNames = array ('related_module_one', 'related_module_two');

			$reportId = $report->getId ();
			// Verificar los datos simples del reporte
			$this->assertEquals (array ('crm', 'facturacion'), $report->getApplicationCodes (), 'Application codes do not match');
			$this->assertEquals ($reportDescription, $report->getDescription (), 'Descriptions do not match');
			$this->assertEquals ($folderId, $report->getFolder ()->getId (), 'Folder IDs do not match');
			$this->assertEquals ($moduleName, $report->getModuleName (), 'Module names do not match');
			$this->assertEquals ($reportName, $report->getName (), 'Report names do not match');
			$this->assertEquals (1, $report->getOwner (), 'Owner IDs do not match');
			$this->assertEquals ($relatedModuleNames, $report->getRelatedModuleNames (), 'Related module names do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $report->getStatus (), 'Statuses do not match');
			$this->assertEquals (ReportInterface::TYPE_TABULAR, $report->getType (), 'Types do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_SHARED, $report->getVisibility (), 'Visibilities do not match');

			// Verificar las columnas
			$columns = $report->getColumns ();
			$this->assertNotNull ($columns, 'Columns should not be null');
			$this->assertEquals (count ($fieldNames), count ($columns), 'Columns count do not match');
			foreach ($columns as $index => $column) {
				$this->assertEquals ($fieldNames [ $index ], $column->getColumnName (), 'Column names do not match');
				$this->assertEquals ($fieldDataTypes [ $index ], $column->getDataType (), 'Column data types do not match');
				$this->assertEquals ($fieldNames [ $index ], $column->getFieldName (), 'Column field names do not match');
				$this->assertEquals ($fieldLabels [ $index ], $column->getLabel (), 'Column labels do not match');
				$this->assertEquals ($moduleNames [ $index ], $column->getModuleName (), 'Column module names do not match');
				$this->assertEquals ($reportId, $column->getReportId (), 'Column labels do not match');
				$this->assertEquals ($index, $column->getSequence (), 'Column sequences do not match');
				$this->assertEquals (null, $column->getSortOrder (), 'Column sort orders do not match');
				$this->assertEquals ($tableNames [ $index ], $column->getTableName (), 'Column sort orders do not match');
				$this->assertEquals (null, $column->getTotalsOperation (), 'Column total operations do not match');
			}

			// Verificar las columnas de ordenamiento
			$this->assertNull ($report->getSortColumns (), 'Sort columns should be null');

			// Verificar las columnas de totalización
			$columns = $report->getTotalColumns ();
			$this->assertNotNull ($columns, 'Total columns should not be null');
			$this->assertEquals (2, count ($columns), 'Total columns count do not match');

			// Verificar la primera columna de totalización
			$column = $columns [0];
			$this->assertEquals ($fieldNames [1], $column->getColumnName (), 'Total column names do not match');
			$this->assertEquals (null, $column->getDataType (), 'Total column data types do not match');
			$this->assertEquals ($fieldNames [1], $column->getFieldName (), 'Total column field names do not match');
			$this->assertEquals ($fieldLabels [1], $column->getLabel (), 'Total column labels do not match');
			$this->assertEquals ($reportId, $column->getReportId (), 'Total column labels do not match');
			$this->assertEquals (1, $column->getSequence (), 'Total column sequences do not match');
			$this->assertEquals (null, $column->getSortOrder (), 'Total column sort orders do not match');
			$this->assertEquals ($tableNames [1], $column->getTableName (), 'Total column sort orders do not match');
			$this->assertEquals (ReportColumnInterface::TOTALS_OPERATION_AVERAGE, $column->getTotalsOperation (), 'Total column total operations do not match');

			// Verificar la segunda columna de totalización
			$column = $columns [1];
			$this->assertEquals ($fieldNames [3], $column->getColumnName (), 'Total column names do not match');
			$this->assertEquals (null, $column->getDataType (), 'Total column data types do not match');
			$this->assertEquals ($fieldNames [3], $column->getFieldName (), 'Total column field names do not match');
			$this->assertEquals ($fieldLabels [3], $column->getLabel (), 'Total column labels do not match');
			$this->assertEquals ($reportId, $column->getReportId (), 'Total column labels do not match');
			$this->assertEquals (2, $column->getSequence (), 'Total column sequences do not match');
			$this->assertEquals (null, $column->getSortOrder (), 'Total column sort orders do not match');
			$this->assertEquals ($tableNames [3], $column->getTableName (), 'Total column sort orders do not match');
			$this->assertEquals (ReportColumnInterface::TOTALS_OPERATION_MINIMUM, $column->getTotalsOperation (), 'Total column total operations do not match');

			// Verificar el filtro estándar
			$filter = $report->getStandardFilter ();
			$this->assertNotNull ($filter, 'Standard filter should not be null');
			$this->assertEquals ($fieldNames [3], $filter->getColumnName (), 'Standard filter column names do not match');
			$this->assertEquals ('2018-12-31', $filter->getEndDate ()->format ('Y-m-d'), 'Standard filter end dates do not match');
			$this->assertEquals ($fieldNames [3], $filter->getFieldName (), 'Standard filter field names do not match');
			$this->assertEquals ($fieldLabels [3], $filter->getLabel (), 'Standard filter field labels do not match');
			$this->assertEquals ($moduleNames [3], $filter->getModuleName (), 'Standard filter module names do not match');
			$this->assertEquals (ReportStandardFilterInterface::PERIOD_CURRENT_YEAR, $filter->getPeriod (), 'Standard filter periods do not match');
			$this->assertEquals ($reportId, $filter->getReportId (), 'Standard filter report IDs do not match');
			$this->assertEquals ('2018-01-01', $filter->getStartDate ()->format ('Y-m-d'), 'Standard filter start dates do not match');
			$this->assertEquals ($tableNames [3], $filter->getTableName (), 'Standard filter table names do not match');

			// Verificar los filtros avanzados
			$groups = $report->getAdvancedFilterGroups ();
			$this->assertNotNull ($groups, 'Advanced filter groups should not be null');
			$this->assertEquals (2, count ($groups), 'Advanced filter groups count do not match');

			// Verificar el primer grupo
			$group = $groups [0];
			$this->assertEquals ($reportId, $group->getReportId (), 'Advanced filter group report IDs do not match');
			$this->assertEquals (0, $group->getSequence (), 'Advanced filter group sequences do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_AND, $group->getOperator (), 'Advanced filter group operators do not match');

			$filters = $group->getFilters ();
			$this->assertNotNull ($filters, 'Advanced filter group filters should not be null');
			$this->assertEquals (2, count ($filters), 'Advanced filter group filters count do not match');

			// Verificar el primer filtro
			$filter = $filters [0];
			$this->assertNotNull ($filter, 'Advanced filter should not be null');
			$this->assertEquals ($fieldNames [0], $filter->getColumnName (), 'Advanced filter column names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_EQUALS, $filter->getComparator (), 'Advanced filter comparators do not match');
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $filter->getDataType (), 'Advanced filter data types do not match');
			$this->assertEquals ($fieldNames [0], $filter->getFieldName (), 'Advanced filter field names do not match');
			$this->assertEquals (0, $filter->getGroupId (), 'Advanced filter group IDs do not match');
			$this->assertEquals ($fieldLabels [0], $filter->getLabel (), 'Advanced filter field labels do not match');
			$this->assertEquals ($moduleNames [0], $filter->getModuleName (), 'Advanced filter module names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_AND, $filter->getOperator (), 'Advanced filter operators do not match');
			$this->assertEquals ($reportId, $filter->getReportId (), 'Advanced filter report IDs do not match');
			$this->assertEquals ($tableNames [0], $filter->getTableName (), 'Advanced filter table names do not match');
			$this->assertEquals ('COD-0001', $filter->getValue (), 'Standard filter values do not match');

			// Verificar el segundo filtro
			$filter = $filters [1];
			$this->assertNotNull ($filter, 'Advanced filter should not be null');
			$this->assertEquals ($fieldNames [1], $filter->getColumnName (), 'Advanced filter column names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS, $filter->getComparator (), 'Advanced filter comparators do not match');
			$this->assertEquals (FieldInterface::DATA_TYPE_DATE, $filter->getDataType (), 'Advanced filter data types do not match');
			$this->assertEquals ($fieldNames [1], $filter->getFieldName (), 'Advanced filter field names do not match');
			$this->assertEquals (0, $filter->getGroupId (), 'Advanced filter group IDs do not match');
			$this->assertEquals ($fieldLabels [1], $filter->getLabel (), 'Advanced filter field labels do not match');
			$this->assertEquals ($moduleNames [1], $filter->getModuleName (), 'Advanced filter module names do not match');
			$this->assertEquals (null, $filter->getOperator (), 'Advanced filter operators do not match');
			$this->assertEquals ($reportId, $filter->getReportId (), 'Advanced filter report IDs do not match');
			$this->assertEquals ($tableNames [1], $filter->getTableName (), 'Advanced filter table names do not match');
			$this->assertEquals ('TEST', $filter->getValue (), 'Standard filter values do not match');

			// Verificar el segundo grupo
			$group = $groups [1];
			$this->assertEquals ($reportId, $group->getReportId (), 'Advanced filter group report IDs do not match');
			$this->assertEquals (1, $group->getSequence (), 'Advanced filter group sequences do not match');
			$this->assertEquals (null, $group->getOperator (), 'Advanced filter group operators do not match');

			$filters = $group->getFilters ();
			$this->assertNotNull ($filters, 'Advanced filter group filters should not be null');
			$this->assertEquals (2, count ($filters), 'Advanced filter group filters count do not match');

			// Verificar el tercer filtro
			$filter = $filters [0];
			$this->assertNotNull ($filter, 'Advanced filter should not be null');
			$this->assertEquals ($fieldNames [2], $filter->getColumnName (), 'Advanced filter column names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_GREATER, $filter->getComparator (), 'Advanced filter comparators do not match');
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $filter->getDataType (), 'Advanced filter data types do not match');
			$this->assertEquals ($fieldNames [2], $filter->getFieldName (), 'Advanced filter field names do not match');
			$this->assertEquals (1, $filter->getGroupId (), 'Advanced filter group IDs do not match');
			$this->assertEquals ($fieldLabels [2], $filter->getLabel (), 'Advanced filter field labels do not match');
			$this->assertEquals ($moduleNames [2], $filter->getModuleName (), 'Advanced filter module names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_OR, $filter->getOperator (), 'Advanced filter operators do not match');
			$this->assertEquals ($reportId, $filter->getReportId (), 'Advanced filter report IDs do not match');
			$this->assertEquals ($tableNames [2], $filter->getTableName (), 'Advanced filter table names do not match');
			$this->assertEquals (0, $filter->getValue (), 'Standard filter values do not match');

			// Verificar el cuarto filtro
			$filter = $filters [1];
			$this->assertNotNull ($filter, 'Advanced filter should not be null');
			$this->assertEquals ($fieldNames [3], $filter->getColumnName (), 'Advanced filter column names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS, $filter->getComparator (), 'Advanced filter comparators do not match');
			$this->assertEquals (FieldInterface::DATA_TYPE_DATE, $filter->getDataType (), 'Advanced filter data types do not match');
			$this->assertEquals ($fieldNames [3], $filter->getFieldName (), 'Advanced filter field names do not match');
			$this->assertEquals (1, $filter->getGroupId (), 'Advanced filter group IDs do not match');
			$this->assertEquals ($fieldLabels [3], $filter->getLabel (), 'Advanced filter field labels do not match');
			$this->assertEquals ($moduleNames [3], $filter->getModuleName (), 'Advanced filter module names do not match');
			$this->assertEquals (null, $filter->getOperator (), 'Advanced filter operators do not match');
			$this->assertEquals ($reportId, $filter->getReportId (), 'Advanced filter report IDs do not match');
			$this->assertEquals ($tableNames [3], $filter->getTableName (), 'Advanced filter table names do not match');
			$this->assertEquals ('2017-12-31', $filter->getValue (), 'Standard filter values do not match');

			// Verificar la información de visibilidad
			$sharingEntities = $report->getShareWith ();
			$this->assertNotNull ($sharingEntities, 'Sharing entities should not be null');
			$this->assertEquals (2, count ($sharingEntities), 'Sharing entities count do not match');

			// Verificar la primera entidad
			$sharingEntity = $sharingEntities [0];
			$this->assertNotNull ($sharingEntity, 'Sharing entity should not be null');
			$this->assertEquals (15, $sharingEntity->getId (), 'Sharing entity IDs do not match');
			$this->assertEquals (ReportSharingEntityInterface::TYPE_USER, $sharingEntity->getType (), 'Sharing entity types do not match');

			// Verificar la segunda entidad
			$sharingEntity = $sharingEntities [1];
			$this->assertNotNull ($sharingEntity, 'Sharing entity should not be null');
			$this->assertEquals (10, $sharingEntity->getId (), 'Sharing entity IDs do not match');
			$this->assertEquals (ReportSharingEntityInterface::TYPE_GROUP, $sharingEntity->getType (), 'Sharing entity types do not match');

			// Verificar programación de envío
			$schedule = $report->getSchedule ();
			$this->assertNotNull ($schedule, 'Schedule should not be null');
			$this->assertEquals (null, $schedule->getDay (), 'Schedule days do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_EXCEL, $schedule->getFormat (), 'Schedule formats do not match');
			$this->assertEquals (ReportScheduleInterface::FREQUENCY_BIWEEKLY, $schedule->getFrequency (), 'Schedule frequencies do not match');
			$this->assertEquals (array (23, 56), $schedule->getGroups (), 'Schedule groups do not match');
			$this->assertEquals (null, $schedule->getMonth (), 'Schedule months do not match');
			$this->assertEquals ($reportId, $schedule->getReportId (), 'Schedule report IDs do not match');
			$this->assertEquals (null, $schedule->getRoles (), 'Schedule roles do not match');
			$this->assertEquals (array ('H25'), $schedule->getRolesAndSubordinates (), 'Schedule roles and subordinates do not match');
			$this->assertEquals ('06:00', $schedule->getTime (), 'Schedule times do not match');
			$this->assertEquals (null, $schedule->getUsers (), 'Schedule users do not match');
			$this->assertEquals (ReportScheduleInterface::WEEKDAY_MONDAY, $schedule->getWeekDay (), 'Schedule weekdays do not match');
		}

		/**
		 * Obtener un reporte sumarizado existente
		 * @depends testCreateFullSummaryReport
		 */
		public function testFetchFullSummaryReport () {
			$moduleName = 'test_module';
			$reportName = 'Full summary report';
			$report     = ReportsManager::getInstance (self::$adb)->fetchReport ($moduleName, $reportName);
			$this->assertNotNull ($report, 'Report should not be null');
			$this->assertNotEmpty ($report->getId (), 'Report ID should not be empty');

			$folderId           = 1;
			$fieldLabels        = array ('My text field', 'My date field', 'My related text field', 'My related date field', 'Another related text field', 'Another related date field');
			$fieldDataTypes     = array (FieldInterface::DATA_TYPE_VARCHAR, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_VARCHAR, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_VARCHAR, FieldInterface::DATA_TYPE_DATE);
			$fieldNames         = array ('text_field', 'date_field', 'related_text_field', 'related_date_field', 'another_text_field', 'another_date_field');
			$moduleNames        = array ('test_module', 'test_module', 'related_module_one', 'related_module_one', 'related_module_two', 'related_module_two');
			$tableNames         = array ('vtiger_test_module', 'vtiger_test_module', 'vtiger_related_module_one', 'vtiger_related_module_one', 'vtiger_related_module_two', 'vtiger_related_module_two');
			$reportDescription  = 'My report description';
			$reportName         = 'Full summary report';
			$relatedModuleNames = array ('related_module_one', 'related_module_two');

			$reportId = $report->getId ();
			// Verificar los datos simples del reporte
			$this->assertEquals (array ('crm', 'facturacion'), $report->getApplicationCodes (), 'Application codes do not match');
			$this->assertEquals ($reportDescription, $report->getDescription (), 'Descriptions do not match');
			$this->assertEquals ($folderId, $report->getFolder ()->getId (), 'Folder IDs do not match');
			$this->assertEquals ($moduleName, $report->getModuleName (), 'Module names do not match');
			$this->assertEquals ($reportName, $report->getName (), 'Report names do not match');
			$this->assertEquals (1, $report->getOwner (), 'Owner IDs do not match');
			$this->assertEquals ($relatedModuleNames, $report->getRelatedModuleNames (), 'Related module names do not match');
			$this->assertEquals (ReportInterface::STATUS_SAVED, $report->getStatus (), 'Statuses do not match');
			$this->assertEquals (ReportInterface::TYPE_SUMMARY, $report->getType (), 'Types do not match');
			$this->assertEquals (ReportInterface::VISIBILITY_SHARED, $report->getVisibility (), 'Visibilities do not match');

			// Verificar las columnas
			$columns = $report->getColumns ();
			$this->assertNotNull ($columns, 'Columns should not be null');
			$this->assertEquals (count ($fieldNames), count ($columns), 'Columns count do not match');
			foreach ($columns as $index => $column) {
				$this->assertEquals ($fieldNames [ $index ], $column->getColumnName (), 'Column names do not match');
				$this->assertEquals ($fieldDataTypes [ $index ], $column->getDataType (), 'Column data types do not match');
				$this->assertEquals ($fieldNames [ $index ], $column->getFieldName (), 'Column field names do not match');
				$this->assertEquals ($fieldLabels [ $index ], $column->getLabel (), 'Column labels do not match');
				$this->assertEquals ($moduleNames [ $index ], $column->getModuleName (), 'Column module names do not match');
				$this->assertEquals ($reportId, $column->getReportId (), 'Column labels do not match');
				$this->assertEquals ($index, $column->getSequence (), 'Column sequences do not match');
				$this->assertEquals (null, $column->getSortOrder (), 'Column sort orders do not match');
				$this->assertEquals ($tableNames [ $index ], $column->getTableName (), 'Column sort orders do not match');
				$this->assertEquals (null, $column->getTotalsOperation (), 'Column total operations do not match');
			}

			// Verificar las columnas de ordenamiento
			$columns = $report->getSortColumns ();
			$this->assertNotNull ($columns, 'Sort columns should not be null');
			$this->assertEquals (2, count ($columns), 'Columns count do not match');

			// Verificar la primera columna de ordenamiento
			$column = $columns [0];
			$this->assertEquals ($fieldNames [3], $column->getColumnName (), 'Sort column names do not match');
			$this->assertEquals (FieldInterface::DATA_TYPE_DATE, $column->getDataType (), 'Sort column data types do not match');
			$this->assertEquals ($fieldNames [3], $column->getFieldName (), 'Sort column field names do not match');
			$this->assertEquals ($fieldLabels [3], $column->getLabel (), 'Sort column labels do not match');
			$this->assertEquals ($reportId, $column->getReportId (), 'Sort column labels do not match');
			$this->assertEquals (1, $column->getSequence (), 'Sort column sequences do not match');
			$this->assertEquals (ReportColumnInterface::SORT_ORDER_DESCENDING, $column->getSortOrder (), 'Sort column sort orders do not match');
			$this->assertEquals ($tableNames [3], $column->getTableName (), 'Sort column table names do not match');
			$this->assertEquals (null, $column->getTotalsOperation (), 'Sort column total operations do not match');

			// Verificar la segunda columna de ordenamiento
			$column = $columns [1];
			$this->assertEquals ($fieldNames [0], $column->getColumnName (), 'Sort column names do not match');
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $column->getDataType (), 'Sort column data types do not match');
			$this->assertEquals ($fieldNames [0], $column->getFieldName (), 'Sort column field names do not match');
			$this->assertEquals ($fieldLabels [0], $column->getLabel (), 'Sort column labels do not match');
			$this->assertEquals ($reportId, $column->getReportId (), 'Sort column labels do not match');
			$this->assertEquals (2, $column->getSequence (), 'Sort column sequences do not match');
			$this->assertEquals (ReportColumnInterface::SORT_ORDER_DESCENDING, $column->getSortOrder (), 'Sort column sort orders do not match');
			$this->assertEquals ($tableNames [0], $column->getTableName (), 'Sort column table names do not match');
			$this->assertEquals (null, $column->getTotalsOperation (), 'Sort column total operations do not match');

			// Verificar las columnas de totalización
			$columns = $report->getTotalColumns ();
			$this->assertNotNull ($columns, 'Total columns should not be null');
			$this->assertEquals (2, count ($columns), 'Total columns count do not match');

			// Verificar la primera columna de totalización
			$column = $columns [0];
			$this->assertEquals ($fieldNames [1], $column->getColumnName (), 'Total column names do not match');
			$this->assertEquals (null, $column->getDataType (), 'Total column data types do not match');
			$this->assertEquals ($fieldNames [1], $column->getFieldName (), 'Total column field names do not match');
			$this->assertEquals ($fieldLabels [1], $column->getLabel (), 'Total column labels do not match');
			$this->assertEquals ($reportId, $column->getReportId (), 'Total column labels do not match');
			$this->assertEquals (1, $column->getSequence (), 'Total column sequences do not match');
			$this->assertEquals (null, $column->getSortOrder (), 'Total column sort orders do not match');
			$this->assertEquals ($tableNames [1], $column->getTableName (), 'Total column sort orders do not match');
			$this->assertEquals (ReportColumnInterface::TOTALS_OPERATION_AVERAGE, $column->getTotalsOperation (), 'Total column total operations do not match');

			// Verificar la segunda columna de totalización
			$column = $columns [1];
			$this->assertEquals ($fieldNames [3], $column->getColumnName (), 'Total column names do not match');
			$this->assertEquals (null, $column->getDataType (), 'Total column data types do not match');
			$this->assertEquals ($fieldNames [3], $column->getFieldName (), 'Total column field names do not match');
			$this->assertEquals ($fieldLabels [3], $column->getLabel (), 'Total column labels do not match');
			$this->assertEquals ($reportId, $column->getReportId (), 'Total column labels do not match');
			$this->assertEquals (2, $column->getSequence (), 'Total column sequences do not match');
			$this->assertEquals (null, $column->getSortOrder (), 'Total column sort orders do not match');
			$this->assertEquals ($tableNames [3], $column->getTableName (), 'Total column sort orders do not match');
			$this->assertEquals (ReportColumnInterface::TOTALS_OPERATION_MINIMUM, $column->getTotalsOperation (), 'Total column total operations do not match');

			// Verificar el filtro estándar
			$filter = $report->getStandardFilter ();
			$this->assertNotNull ($filter, 'Standard filter should not be null');
			$this->assertEquals ($fieldNames [3], $filter->getColumnName (), 'Standard filter column names do not match');
			$this->assertEquals ('2018-12-31', $filter->getEndDate ()->format ('Y-m-d'), 'Standard filter end dates do not match');
			$this->assertEquals ($fieldNames [3], $filter->getFieldName (), 'Standard filter field names do not match');
			$this->assertEquals ($fieldLabels [3], $filter->getLabel (), 'Standard filter field labels do not match');
			$this->assertEquals ($moduleNames [3], $filter->getModuleName (), 'Standard filter module names do not match');
			$this->assertEquals (ReportStandardFilterInterface::PERIOD_CURRENT_YEAR, $filter->getPeriod (), 'Standard filter periods do not match');
			$this->assertEquals ($reportId, $filter->getReportId (), 'Standard filter report IDs do not match');
			$this->assertEquals ('2018-01-01', $filter->getStartDate ()->format ('Y-m-d'), 'Standard filter start dates do not match');
			$this->assertEquals ($tableNames [3], $filter->getTableName (), 'Standard filter table names do not match');

			// Verificar los filtros avanzados
			$groups = $report->getAdvancedFilterGroups ();
			$this->assertNotNull ($groups, 'Advanced filter groups should not be null');
			$this->assertEquals (2, count ($groups), 'Advanced filter groups count do not match');

			// Verificar el primer grupo
			$group = $groups [0];
			$this->assertEquals ($reportId, $group->getReportId (), 'Advanced filter group report IDs do not match');
			$this->assertEquals (0, $group->getSequence (), 'Advanced filter group sequences do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_AND, $group->getOperator (), 'Advanced filter group operators do not match');

			$filters = $group->getFilters ();
			$this->assertNotNull ($filters, 'Advanced filter group filters should not be null');
			$this->assertEquals (2, count ($filters), 'Advanced filter group filters count do not match');

			// Verificar el primer filtro
			$filter = $filters [0];
			$this->assertNotNull ($filter, 'Advanced filter should not be null');
			$this->assertEquals ($fieldNames [0], $filter->getColumnName (), 'Advanced filter column names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_EQUALS, $filter->getComparator (), 'Advanced filter comparators do not match');
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $filter->getDataType (), 'Advanced filter data types do not match');
			$this->assertEquals ($fieldNames [0], $filter->getFieldName (), 'Advanced filter field names do not match');
			$this->assertEquals (0, $filter->getGroupId (), 'Advanced filter group IDs do not match');
			$this->assertEquals ($fieldLabels [0], $filter->getLabel (), 'Advanced filter field labels do not match');
			$this->assertEquals ($moduleNames [0], $filter->getModuleName (), 'Advanced filter module names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_AND, $filter->getOperator (), 'Advanced filter operators do not match');
			$this->assertEquals ($reportId, $filter->getReportId (), 'Advanced filter report IDs do not match');
			$this->assertEquals ($tableNames [0], $filter->getTableName (), 'Advanced filter table names do not match');
			$this->assertEquals ('COD-0001', $filter->getValue (), 'Standard filter values do not match');

			// Verificar el segundo filtro
			$filter = $filters [1];
			$this->assertNotNull ($filter, 'Advanced filter should not be null');
			$this->assertEquals ($fieldNames [1], $filter->getColumnName (), 'Advanced filter column names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS, $filter->getComparator (), 'Advanced filter comparators do not match');
			$this->assertEquals (FieldInterface::DATA_TYPE_DATE, $filter->getDataType (), 'Advanced filter data types do not match');
			$this->assertEquals ($fieldNames [1], $filter->getFieldName (), 'Advanced filter field names do not match');
			$this->assertEquals (0, $filter->getGroupId (), 'Advanced filter group IDs do not match');
			$this->assertEquals ($fieldLabels [1], $filter->getLabel (), 'Advanced filter field labels do not match');
			$this->assertEquals ($moduleNames [1], $filter->getModuleName (), 'Advanced filter module names do not match');
			$this->assertEquals (null, $filter->getOperator (), 'Advanced filter operators do not match');
			$this->assertEquals ($reportId, $filter->getReportId (), 'Advanced filter report IDs do not match');
			$this->assertEquals ($tableNames [1], $filter->getTableName (), 'Advanced filter table names do not match');
			$this->assertEquals ('TEST', $filter->getValue (), 'Standard filter values do not match');

			// Verificar el segundo grupo
			$group = $groups [1];
			$this->assertEquals ($reportId, $group->getReportId (), 'Advanced filter group report IDs do not match');
			$this->assertEquals (1, $group->getSequence (), 'Advanced filter group sequences do not match');
			$this->assertEquals (null, $group->getOperator (), 'Advanced filter group operators do not match');

			$filters = $group->getFilters ();
			$this->assertNotNull ($filters, 'Advanced filter group filters should not be null');
			$this->assertEquals (2, count ($filters), 'Advanced filter group filters count do not match');

			// Verificar el tercer filtro
			$filter = $filters [0];
			$this->assertNotNull ($filter, 'Advanced filter should not be null');
			$this->assertEquals ($fieldNames [2], $filter->getColumnName (), 'Advanced filter column names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_GREATER, $filter->getComparator (), 'Advanced filter comparators do not match');
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $filter->getDataType (), 'Advanced filter data types do not match');
			$this->assertEquals ($fieldNames [2], $filter->getFieldName (), 'Advanced filter field names do not match');
			$this->assertEquals (1, $filter->getGroupId (), 'Advanced filter group IDs do not match');
			$this->assertEquals ($fieldLabels [2], $filter->getLabel (), 'Advanced filter field labels do not match');
			$this->assertEquals ($moduleNames [2], $filter->getModuleName (), 'Advanced filter module names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_OR, $filter->getOperator (), 'Advanced filter operators do not match');
			$this->assertEquals ($reportId, $filter->getReportId (), 'Advanced filter report IDs do not match');
			$this->assertEquals ($tableNames [2], $filter->getTableName (), 'Advanced filter table names do not match');
			$this->assertEquals (0, $filter->getValue (), 'Standard filter values do not match');

			// Verificar el cuarto filtro
			$filter = $filters [1];
			$this->assertNotNull ($filter, 'Advanced filter should not be null');
			$this->assertEquals ($fieldNames [3], $filter->getColumnName (), 'Advanced filter column names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS, $filter->getComparator (), 'Advanced filter comparators do not match');
			$this->assertEquals (FieldInterface::DATA_TYPE_DATE, $filter->getDataType (), 'Advanced filter data types do not match');
			$this->assertEquals ($fieldNames [3], $filter->getFieldName (), 'Advanced filter field names do not match');
			$this->assertEquals (1, $filter->getGroupId (), 'Advanced filter group IDs do not match');
			$this->assertEquals ($fieldLabels [3], $filter->getLabel (), 'Advanced filter field labels do not match');
			$this->assertEquals ($moduleNames [3], $filter->getModuleName (), 'Advanced filter module names do not match');
			$this->assertEquals (null, $filter->getOperator (), 'Advanced filter operators do not match');
			$this->assertEquals ($reportId, $filter->getReportId (), 'Advanced filter report IDs do not match');
			$this->assertEquals ($tableNames [3], $filter->getTableName (), 'Advanced filter table names do not match');
			$this->assertEquals ('2017-12-31', $filter->getValue (), 'Standard filter values do not match');

			// Verificar la información de visibilidad
			$sharingEntities = $report->getShareWith ();
			$this->assertNotNull ($sharingEntities, 'Sharing entities should not be null');
			$this->assertEquals (2, count ($sharingEntities), 'Sharing entities count do not match');

			// Verificar la primera entidad
			$sharingEntity = $sharingEntities [0];
			$this->assertNotNull ($sharingEntity, 'Sharing entity should not be null');
			$this->assertEquals (15, $sharingEntity->getId (), 'Sharing entity IDs do not match');
			$this->assertEquals (ReportSharingEntityInterface::TYPE_USER, $sharingEntity->getType (), 'Sharing entity types do not match');

			// Verificar la segunda entidad
			$sharingEntity = $sharingEntities [1];
			$this->assertNotNull ($sharingEntity, 'Sharing entity should not be null');
			$this->assertEquals (10, $sharingEntity->getId (), 'Sharing entity IDs do not match');
			$this->assertEquals (ReportSharingEntityInterface::TYPE_GROUP, $sharingEntity->getType (), 'Sharing entity types do not match');

			// Verificar programación de envío
			$schedule = $report->getSchedule ();
			$this->assertNotNull ($schedule, 'Schedule should not be null');
			$this->assertEquals (null, $schedule->getDay (), 'Schedule days do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_EXCEL, $schedule->getFormat (), 'Schedule formats do not match');
			$this->assertEquals (ReportScheduleInterface::FREQUENCY_BIWEEKLY, $schedule->getFrequency (), 'Schedule frequencies do not match');
			$this->assertEquals (array (23, 56), $schedule->getGroups (), 'Schedule groups do not match');
			$this->assertEquals (null, $schedule->getMonth (), 'Schedule months do not match');
			$this->assertEquals ($reportId, $schedule->getReportId (), 'Schedule report IDs do not match');
			$this->assertEquals (null, $schedule->getRoles (), 'Schedule roles do not match');
			$this->assertEquals (array ('H25'), $schedule->getRolesAndSubordinates (), 'Schedule roles and subordinates do not match');
			$this->assertEquals ('06:00', $schedule->getTime (), 'Schedule times do not match');
			$this->assertEquals (null, $schedule->getUsers (), 'Schedule users do not match');
			$this->assertEquals (ReportScheduleInterface::WEEKDAY_MONDAY, $schedule->getWeekDay (), 'Schedule weekdays do not match');
		}

		/**
		 * Eliminar un campo de un módulo y validar que se elimina de las columnas
		 * @depends testFetchFullSummaryReport
		 */
		public function testDeleteField () {
			$moduleName = 'related_module_one';
			$fieldName  = 'related_date_field';
			$tableName  = 'vtiger_related_module_one';
			$result     = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE columnname LIKE ?', array ("{$tableName}:{$fieldName}:{$moduleName}_%:{$fieldName}:D"));
			$this->assertGreaterThan (0, self::$adb->num_rows ($result), 'Advanced filter columns count should be greater than zero');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datecolumnname LIKE ?', array ("{$tableName}:{$fieldName}:{$fieldName}:{$moduleName}_%"));
			$this->assertGreaterThan (0, self::$adb->num_rows ($result), 'Standard filter columns count should be greater than zero');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE columnname LIKE ?', array ("{$tableName}:{$fieldName}:{$moduleName}_%:{$fieldName}:D"));
			$this->assertGreaterThan (0, self::$adb->num_rows ($result), 'Sorting columns count should be greater than zero');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE columnname LIKE ?', array ("{$tableName}:{$fieldName}:{$moduleName}_%:{$fieldName}:D"));
			$this->assertGreaterThan (0, self::$adb->num_rows ($result), 'Columns count should be greater than zero');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE columnname LIKE ?', array ("cb:{$tableName}:{$fieldName}:%"));
			$this->assertGreaterThan (0, self::$adb->num_rows ($result), 'Columns count should be greater than zero');

			$field = FieldManager::getInstance (self::$adb)->fetchFieldByName ($moduleName, $fieldName);
			ReportsManager::getInstance (self::$adb)->deleteFieldFromReports ($field);

			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datecolumnname LIKE ?', array ("{$tableName}:{$fieldName}:{$fieldName}:{$moduleName}_%"));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Standard filter columns count should be zero');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE columnname LIKE ?', array ("{$tableName}:{$fieldName}:{$moduleName}_%:{$fieldName}:D"));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Sorting columns count should be zero');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE columnname LIKE ?', array ("{$tableName}:{$fieldName}:{$moduleName}_%:{$fieldName}:D"));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Columns count should be zero');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE columnname LIKE ?', array ("cb:{$tableName}:{$fieldName}:%"));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Columns count should be zero');
		}

		/**
		 * Eliminar un reporte sumarizado existente
		 * @depends testFetchFullSummaryReport
		 */
		public function testDeleteFullSummaryReport () {
			$moduleName = 'test_module';
			$reportName = 'Full summary report';

			$rm     = ReportsManager::getInstance (self::$adb);
			$report = $rm->fetchReport ($moduleName, $reportName);
			$this->assertNotNull ($report, 'Report should not be null');

			$reportId = $report->getId ();
			$rm->deleteReport ($report);

			// Verificar que se eliminó el reporte
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectquery WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report not found in database');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_report WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report not found in database');

			// Verificar que se eliminaron los datos de los módulos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportmodules WHERE reportmodulesid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report modules count do not match');

			// Verificar que se eliminaron las columnas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_selectcolumn WHERE queryid=? ORDER BY columnindex', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report columns count do not match');

			// Verificar que se eliminaron las columnas de ordenamiento
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsortcol WHERE reportid=? ORDER BY sortcolid', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sort columns count do not match');

			// Verificar que se eliminaron las columnas de totalización
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsummary WHERE reportsummaryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report total columns count do not match');

			// Verificar que se eliminaron los filtros estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportdatefilter WHERE datefilterid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report standard filters count do not match');

			// Verificar que se eliminaron los grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria_grouping WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que se eliminaron los filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relcriteria WHERE queryid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report advanced filter groups count do not match');

			// Verificar que se eliminó la información de compartir
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportsharing WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report sharing rules count do not match');

			// Verificar que se eliminó la información de programación de envíos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_scheduled_reports WHERE reportid=?', array ($reportId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved report scheduling rules count do not match');
		}

		/**
		 * Eliminar carpeta de reportes
		 */
		public function testDeleteFolder () {
			$folderId = 1;

			$rm     = ReportsManager::getInstance (self::$adb);
			$folder = $rm->fetchFolderById ($folderId);
			$this->assertNotNull ($folder, 'Folder should not be null');

			$rm->deleteFolder ($folder);

			// Verificar que se eliminó la carpeta
			$result = self::$adb->pquery ('SELECT * FROM vtiger_reportfolder WHERE folderid=?', array ($folderId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Folder should be null');

			// Verificar que se eliminó toda información de reportes
			$result = self::$adb->query ('SELECT * FROM vtiger_selectquery');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Reports found');
			$result = self::$adb->query ('SELECT * FROM vtiger_report');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Reports found');

			// Verificar que se eliminaron los datos de los módulos
			$result = self::$adb->query ('SELECT * FROM vtiger_reportmodules');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Report modules count do not match');

			// Verificar que se eliminaron las columnas
			$result = self::$adb->query ('SELECT * FROM vtiger_selectcolumn');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Report columns count do not match');

			// Verificar que se eliminaron las columnas de ordenamiento
			$result = self::$adb->query ('SELECT * FROM vtiger_reportsortcol');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Report sort columns count do not match');

			// Verificar que se eliminaron las columnas de totalización
			$result = self::$adb->query ('SELECT * FROM vtiger_reportsummary');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Report total columns count do not match');

			// Verificar que se eliminaron los filtros estándar
			$result = self::$adb->query ('SELECT * FROM vtiger_reportdatefilter');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Report standard filters count do not match');

			// Verificar que se eliminaron los grupos de filtros avanzados
			$result = self::$adb->query ('SELECT * FROM vtiger_relcriteria_grouping');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Report advanced filter groups count do not match');

			// Verificar que se eliminaron los filtros avanzados
			$result = self::$adb->query ('SELECT * FROM vtiger_relcriteria');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Report advanced filter groups count do not match');

			// Verificar que se eliminó la información de compartir
			$result = self::$adb->query ('SELECT * FROM vtiger_reportsharing');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Report sharing rules count do not match');

			// Verificar que se eliminó la información de programación de envíos
			$result = self::$adb->query ('SELECT * FROM vtiger_scheduled_reports');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Report scheduling rules count do not match');
		}

	}
	// @codingStandardsIgnoreEnd

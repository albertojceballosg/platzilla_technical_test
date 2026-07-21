<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/BlockManager.php');

	/**
	 * Prueba funcional de la clase BlockManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class BlockManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_blocks, vtiger_blocks_seq, vtiger_field, vtiger_subfields, vtiger_field_dependency, vtiger_fieldmodulerel, vtiger_picklist,
		 * vtiger_picklist_seq, vtiger_picklistvalues_seq, vtiger_profile, vtiger_profile2field, vtiger_role, vtiger_role2picklist, vtiger_crmentity
		 * 4. Crear tabla de un módulo simulado vtiger_test_module
		 * 5. Simular existencia de dos módulos, dos perfiles y dos roles
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
				"CREATE TABLE `vtiger_entityname` (
					`tabid` INT(19) NOT NULL DEFAULT '0',
					`modulename` VARCHAR(50) NOT NULL,
					`tablename` VARCHAR(100) NOT NULL,
					`fieldname` VARCHAR(150) NOT NULL,
					`entityidfield` VARCHAR(150) NOT NULL,
					`entityidcolumn` VARCHAR(150) NOT NULL,
					PRIMARY KEY (`tabid`),
					INDEX `entityname_tabid_idx` (`tabid`),
					CONSTRAINT `fk_1_vtiger_entityname` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
				) COLLATE='utf8_general_ci' ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_blocks` (
					`blockid` INT(19) NOT NULL,
					`tabid` INT(19) NOT NULL,
					`blocklabel` VARCHAR(100) NOT NULL,
					`sequence` INT(10) DEFAULT NULL,
					`show_title` INT(2) DEFAULT NULL,
					`visible` INT(2) NOT NULL DEFAULT '0',
					`create_view` INT(2) NOT NULL DEFAULT '0',
					`edit_view` INT(2) NOT NULL DEFAULT '0',
					`detail_view` INT(2) NOT NULL DEFAULT '0',
					`display_status` INT(1) NOT NULL DEFAULT '1',
					`iscustom` INT(1) NOT NULL DEFAULT '0',
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`blockid`),
					KEY `block_tabid_idx` (`tabid`),
					CONSTRAINT `fk_1_vtiger_blocks` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_blocks_seq` (
					`id` int(11) NOT NULL,
					PRIMARY KEY (`id`)
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
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`fieldid`),
					KEY `field_tabid_idx` (`tabid`),
					KEY `field_fieldname_idx` (`fieldname`),
					KEY `field_block_idx` (`block`),
					KEY `field_displaytype_idx` (`displaytype`),
					CONSTRAINT `fk_1_vtiger_field` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_subfields` (
					`fieldid` int(11) NOT NULL,
					`name` varchar(255) NOT NULL,
					`label` varchar(255) NOT NULL,
					`sequence` int(11) NOT NULL,
					`uitype` int(11) NOT NULL,
					`length` int(11) NOT NULL,
					`precision` int(11) DEFAULT NULL,
					`defaultvalue` varchar(255) DEFAULT NULL,
					`values` text,
					`relmodule` varchar(32) DEFAULT NULL,
					PRIMARY KEY (`fieldid`,`name`),
					CONSTRAINT `vtiger_subfields_ibfk_1` FOREIGN KEY (`fieldid`) REFERENCES `vtiger_field` (`fieldid`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_subfields_special` (
					`subfieldsid` int(11) NOT NULL AUTO_INCREMENT,
					`fieldid` int(19) NOT NULL,
					`name` varchar(255) NOT NULL,
					`label` varchar(255) NOT NULL,
					`sequence` int(11) NOT NULL,
					`uitype` int(11) NOT NULL,
					`length` int(11) DEFAULT NULL,
					`precision` int(11) DEFAULT NULL,
					`defaultvalue` varchar(255) DEFAULT NULL,
					`values` text,
					`action_field` longblob,
					`filter_field` longblob,
					`relmodule` text,
					`data_field` text,
					PRIMARY KEY (`subfieldsid`,`fieldid`),
					KEY `vtiger_subfields_special_ibfk` (`fieldid`),
					CONSTRAINT `vtiger_subfields_special_ibfk` FOREIGN KEY (`fieldid`) REFERENCES `vtiger_field` (`fieldid`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_subfields_values` (
					`subfieldsvaluesid` int(11) NOT NULL AUTO_INCREMENT,
					`modulecfid` int(11) NOT NULL,
					`subfieldsid` int(11) NOT NULL,
					`field_values` longblob,
					PRIMARY KEY (`subfieldsvaluesid`,`subfieldsid`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_picklist` (
  					`picklistid` INT(11) NOT NULL AUTO_INCREMENT,
  					`name` VARCHAR(200) NOT NULL,
  					PRIMARY KEY (`picklistid`),
  					UNIQUE KEY `picklist_name_idx` (`name`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_picklist_seq` (
					`id` INT(11) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_picklistvalues_seq` (
					`id` INT(11) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_field_dependency` (
				  `fielddependency_id` INT(11) NOT NULL AUTO_INCREMENT,
				  `parentfield` INT(11) NOT NULL,
				  `nameparent` VARCHAR(100) NOT NULL,
				  `field` INT(11) NOT NULL,
				  `visible` INT(1) NOT NULL,
				  PRIMARY KEY (`fielddependency_id`),
				  KEY `parentfield` (`parentfield`,`field`),
				  KEY `field` (`field`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_field_validation` (
					`tabid` INT(19) NOT NULL,
					`fieldid` INT(19) NOT NULL,
					`tablename` VARCHAR(50) NOT NULL,
					`fieldname` VARCHAR(30) NOT NULL,
					`validationtype` VARCHAR(100) NOT NULL,
					`initialvalue` VARCHAR(32) NULL DEFAULT NULL,
					`maximumvalue` VARCHAR(32) NULL DEFAULT NULL,
					`locked` TINYINT(1) NOT NULL DEFAULT '0'
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_fieldmodulerel` (
					`fieldid` INT(11) NOT NULL,
					`module` VARCHAR(100) NOT NULL,
					`relmodule` VARCHAR(100) NOT NULL,
					`status` VARCHAR(10) NULL DEFAULT NULL,
					`sequence` INT(11) NULL DEFAULT NULL,
					`fieldpk` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					PRIMARY KEY (`fieldpk`),
					INDEX `module_relmodule_fieldpk` (`fieldpk`, `module`, `relmodule`),
					INDEX `FK_vtiger_fieldmodulerel_vtiger_field` (`fieldid`),
					INDEX `FK_vtiger_fieldmodulerel_vtiger_tab` (`module`),
					INDEX `FK_vtiger_fieldmodulerel_vtiger_tab_2` (`relmodule`),
					CONSTRAINT `FK_vtiger_fieldmodulerel_vtiger_tab_2` FOREIGN KEY (`relmodule`) REFERENCES `vtiger_tab` (`name`) ON UPDATE CASCADE ON DELETE CASCADE,
					CONSTRAINT `FK_vtiger_fieldmodulerel_vtiger_field` FOREIGN KEY (`fieldid`) REFERENCES `vtiger_field` (`fieldid`) ON UPDATE CASCADE ON DELETE CASCADE,
					CONSTRAINT `FK_vtiger_fieldmodulerel_vtiger_tab` FOREIGN KEY (`module`) REFERENCES `vtiger_tab` (`name`) ON UPDATE CASCADE ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_fieldmodulerel_relationships` (
					`referenceid` INT(10) UNSIGNED NOT NULL,
					`fieldname` VARCHAR(50) NOT NULL,
					`relfieldname` VARCHAR(50) NOT NULL,
					PRIMARY KEY (`referenceid`, `fieldname`, `relfieldname`),
					CONSTRAINT `FK_vtiger_fieldmodulerel_map_vtiger_fieldmodulerel` FOREIGN KEY (`referenceid`) REFERENCES `vtiger_fieldmodulerel` (`fieldpk`) ON UPDATE CASCADE ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_customview` (
					`cvid` int(19) NOT NULL,
					`viewname` varchar(100) NOT NULL,
					`setdefault` int(1) DEFAULT '0',
					`setmetrics` int(1) DEFAULT '0',
					`entitytype` varchar(25) NOT NULL,
					`status` int(1) DEFAULT '1',
					`userid` int(19) DEFAULT '1',
					`clientview` int(11) NOT NULL DEFAULT '0',
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`cvid`),
					KEY `customview_entitytype_idx` (`entitytype`),
					CONSTRAINT `fk_1_vtiger_customview` FOREIGN KEY (`entitytype`) REFERENCES `vtiger_tab` (`name`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvadvfilter` (
					`cvid` int(19) NOT NULL,
					`columnindex` int(11) NOT NULL,
					`columnname` varchar(250) DEFAULT '',
					`comparator` varchar(10) DEFAULT '',
					`value` varchar(200) DEFAULT '',
					`groupid` int(11) DEFAULT '1',
					`column_condition` varchar(255) DEFAULT 'and',
					PRIMARY KEY (`cvid`,`columnindex`),
					KEY `cvadvfilter_cvid_idx` (`cvid`),
					CONSTRAINT `fk_1_vtiger_cvadvfilter` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvcolumnlist` (
					`cvid` int(19) NOT NULL,
					`columnindex` int(11) NOT NULL,
					`columnname` varchar(250) DEFAULT '',
					PRIMARY KEY (`cvid`,`columnindex`),
					KEY `cvcolumnlist_columnindex_idx` (`columnindex`),
					KEY `cvcolumnlist_cvid_idx` (`cvid`),
					CONSTRAINT `fk_1_vtiger_cvcolumnlist` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvstdfilter` (
					`cvid` int(19) NOT NULL,
					`columnname` varchar(250) DEFAULT '',
					`stdfilter` varchar(250) DEFAULT '',
					`startdate` date DEFAULT NULL,
					`enddate` date DEFAULT NULL,
					PRIMARY KEY (`cvid`),
					KEY `cvstdfilter_cvid_idx` (`cvid`),
					CONSTRAINT `fk_1_vtiger_cvstdfilter` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_profile` (
					`profileid` INT(10) NOT NULL AUTO_INCREMENT,
					`profilename` VARCHAR(50) NOT NULL,
					`description` TEXT,
					PRIMARY KEY (`profileid`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_profile2field` (
					`profileid` INT(11) NOT NULL,
					`tabid` INT(10) DEFAULT NULL,
					`fieldid` INT(19) NOT NULL,
					`visible` INT(19) DEFAULT NULL,
					`readonly` INT(19) DEFAULT NULL,
					PRIMARY KEY (`profileid`,`fieldid`),
					KEY `profile2field_profileid_tabid_fieldname_idx` (`profileid`,`tabid`),
					KEY `profile2field_tabid_profileid_idx` (`tabid`,`profileid`),
					KEY `profile2field_visible_profileid_idx` (`visible`,`profileid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_def_org_field` (
					`tabid` int(10) DEFAULT NULL,
					`fieldid` int(19) NOT NULL,
					`visible` int(19) DEFAULT NULL,
					`readonly` int(19) DEFAULT NULL,
					PRIMARY KEY (`fieldid`),
					KEY `def_org_field_tabid_fieldid_idx` (`tabid`,`fieldid`),
					KEY `def_org_field_tabid_idx` (`tabid`),
					KEY `def_org_field_visible_fieldid_idx` (`visible`,`fieldid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_role` (
					`roleid` VARCHAR(255) NOT NULL,
					`rolename` VARCHAR(200) DEFAULT NULL,
					`parentrole` VARCHAR(255) DEFAULT NULL,
					`depth` INT(19) DEFAULT NULL,
					`iscustomer` INT(11) DEFAULT NULL,
					`ispartner` INT(11) DEFAULT NULL,
					`default_module` VARCHAR(255) DEFAULT NULL,
					PRIMARY KEY (`roleid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_role2picklist` (
					`roleid` VARCHAR(255) NOT NULL,
					`picklistvalueid` INT(11) NOT NULL,
					`picklistid` INT(11) NOT NULL,
					`sortid` INT(11) DEFAULT NULL,
					PRIMARY KEY (`roleid`,`picklistvalueid`,`picklistid`),
					KEY `role2picklist_roleid_picklistid_idx` (`roleid`,`picklistid`,`picklistvalueid`),
					KEY `fk_2_vtiger_role2picklist` (`picklistid`),
					CONSTRAINT `fk_1_vtiger_role2picklist` FOREIGN KEY (`roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE,
					CONSTRAINT `fk_2_vtiger_role2picklist` FOREIGN KEY (`picklistid`) REFERENCES `vtiger_picklist` (`picklistid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_test_module` (
				  `test_moduleid` INT(11) NOT NULL AUTO_INCREMENT,
				  PRIMARY KEY (`test_moduleid`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"
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
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
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
				"CREATE TABLE IF NOT EXISTS `vtiger_graficos` (
					`graficoid` int(11) NOT NULL AUTO_INCREMENT,
					`fld_module` varchar(20) NOT NULL,
					`fieldoperation` varchar(20) NOT NULL,
					`operation` int(2) NOT NULL,
					`tipografico` varchar(20) NOT NULL,
					`title` varchar(400) NOT NULL,
					`roles_grafico` varchar(200) DEFAULT NULL,
					`sqlprimarioreporte` text,
					`varreporte` text,
					`reporteavanzado` int(11) NOT NULL DEFAULT '0',
					`comparar` int(11) DEFAULT '0',
					`ishome` int(1) NOT NULL DEFAULT '0',
					`fieldgrouping` varchar(20) DEFAULT NULL,
					`dategrouping` tinyint(4) DEFAULT NULL,
					`applicationcodes` text,
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`graficoid`)
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
			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (2, 'test_related_module', 0, 2, 'Test related module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_blocks_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_picklist_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_picklistvalues_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (1, 'Administrator', 'Admin Profile')");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (2, 'CRM', 'El CRM blah blah blah')");
			self::$adb->query ("INSERT INTO `vtiger_role` (`roleid`, `rolename`, `parentrole`, `depth`, `iscustomer`, `ispartner`, `default_module`) VALUES ('H1', 'Organización', 'H1', 0, NULL, NULL, NULL)");
			self::$adb->query ("INSERT INTO `vtiger_role` (`roleid`, `rolename`, `parentrole`, `depth`, `iscustomer`, `ispartner`, `default_module`) VALUES ('H2', 'Director General', 'H1::H2', 1, 0, NULL, 'Home')");
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
		 * Intentar crear un bloque sin la información mínima necesaria
		 * Debe arrojar una BlockException
		 */
		public function testCreateIncompleteBlock () {
			$block = Block::getInstance ();
			$this->expectException (BlockException::class);
			BlockManager::getInstance (self::$adb)->saveBlock ($block);
		}

		/**
		 * Intentar crear un bloque asociado a un nombre de módulo no existente
		 * Debe arrojar una BlockException
		 */
		public function testCreateNonExistingModuleNameBlock () {
			$block = Block::getInstance ()
				->setModuleName ('non_existing_module')
				->setLabel ('My test block')
				->setFields (array (Field::getInstance ()));
			$this->expectException (BlockException::class);
			$this->expectExceptionMessage (BlockException::ERROR_BLOCK_INVALID_MODULE_NAME);
			BlockManager::getInstance (self::$adb)->saveBlock ($block);
		}

		/**
		 * Crear un bloque válido
		 */
		public function testCreateValidBlock () {
			$moduleName = 'test_module';

			$block = Block::getInstance ()
				->setModuleName ($moduleName)
				->setLabel ('My test block')
				->setFields (array (
					Field::getInstance ()
						->setColumnName ('checkbox_field')
						->setLabel ('My checkbox field')
						->setMandatory (true)
						->setModuleName ($moduleName)
						->setName ('checkbox_field')
						->setTableName ('vtiger_test_module')
						->setUiType (FieldInterface::UI_TYPE_CHECKBOX)
						->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
						->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
						->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
						->setPresence (FieldInterface::PRESENCE_VISIBLE)
						->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
						->setReadOnly (FieldInterface::READ_WRITE),
					Field::getInstance ()
						->setColumnName ('test_module_reference_field')
						->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
						->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
						->setLabel ('My module reference field')
						->setMandatory (true)
						->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
						->setModuleName ($moduleName)
						->setModuleReferences (array (
							FieldModuleReference::getInstance ()
								->setFieldName ('test_module_reference_field')
								->setModuleName ($moduleName)
								->setReferencedModuleName ('test_related_module'),
						))
						->setName ('test_module_reference_field')
						->setPresence (FieldInterface::PRESENCE_VISIBLE)
						->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
						->setReadOnly (FieldInterface::READ_WRITE)
						->setTableName ('vtiger_test_module')
						->setUiType (FieldInterface::UI_TYPE_MODULE_REFERENCE, 1024),
					Field::getInstance ()
						->setColumnName ('test_picklist_field')
						->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
						->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
						->setLabel ('My picklist field')
						->setMandatory (true)
						->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
						->setModuleName ($moduleName)
						->setName ('test_picklist_field')
						->setPicklist (
							Picklist::getInstance ()
								->setName ('test_picklist_field')
								->setValues (array (
									PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('First value'),
									PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('Second value'),
									PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('Third value'),
								))
						)
						->setPresence (FieldInterface::PRESENCE_VISIBLE)
						->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
						->setReadOnly (FieldInterface::READ_WRITE)
						->setTableName ('vtiger_test_module')
						->setUiType (FieldInterface::UI_TYPE_PICKLIST),
				));
			$savedBlock = BlockManager::getInstance (self::$adb)->saveBlock ($block);

			// Verificar que el objeto existe y tiene ID
			$this->assertNotNull ($savedBlock, 'Saved block should not be null');
			$this->assertNotEmpty ($savedBlock->getId (), 'Saved block ID should not be null');

			// Verificar que el bloque fue creado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=? WHERE b.blocklabel=?', array ($moduleName, 'My test block'));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que el bloque contiene todos los valores suministrados
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($savedBlock->getDisplayStatus (), $row ['display_status'], 'Block display_status properties do not match');
			$this->assertEquals ($savedBlock->getLabel (), $row ['blocklabel'], 'Block labels do not match');
			$this->assertEquals (1, $row ['sequence'], 'Block sequences do not match');
			$this->assertEquals (BlockInterface::SHOW_TITLE_YES, $row ['show_title'], 'Block show_title properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $row ['visible'], 'Block visible properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $row ['create_view'], 'Block create_view properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $row ['edit_view'], 'Block edit_view properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $row ['detail_view'], 'Block edit_view properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_HIDDEN, $row ['display_status'], 'Block edit_view properties do not match');
			$this->assertEquals (BlockInterface::IS_CUSTOM_NO, $row ['iscustom'], 'Block iscustom properties do not match');

			// Verificar que se crearon los campos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field WHERE block=?', array ($savedBlock->getId ()));
			$this->assertEquals (3, self::$adb->num_rows ($result));
		}

		/**
		 * Intentar obtener un bloque no existente
		 */
		public function testFetchNonExistingBlock () {
			$this->assertNull (BlockManager::getInstance (self::$adb)->fetchBlock (155));
		}

		/**
		 * Obtener un bloque existente
		 * @depends testCreateValidBlock
		 */
		public function testFetchExistingBlock () {
			$block = BlockManager::getInstance (self::$adb)->fetchBlock (10);

			// Verificar que el objeto existe y contiene todos los valores suministrados
			$this->assertNotNull ($block, 'Saved block should not be null');
			$this->assertEquals (10, $block->getId (), 'Saved block ID should not be null');
			$this->assertEquals ('My test block', $block->getLabel (), 'Block labels do not match');
			$this->assertEquals (1, $block->getSequence (), 'Block sequences do not match');
			$this->assertEquals (BlockInterface::SHOW_TITLE_YES, $block->getShowTitle (), 'Block show_title properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $block->getVisibility (), 'Block visible properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_HIDDEN, $block->getDisplayStatus (), 'Block display status properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $block->getVisibilityInCreateView (), 'Block create_view properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $block->getVisibilityInDetailView (), 'Block edit_view properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $block->getVisibilityInEditView (), 'Block edit_view properties do not match');
			$this->assertEquals (BlockInterface::IS_CUSTOM_NO, $block->getIsCustom (), 'Block iscustom properties do not match');
			$this->assertCount (3, $block->getFields (), 'Fields count do not match');
		}

		/**
		 * Obtener los bloques existentes para un módulo existente. Agregar dos previamente
		 * @depends testFetchExistingBlock
		 */
		public function testFetchExistingBlocks () {
			$moduleName = 'test_module';
			BlockManager::getInstance (self::$adb)->saveBlock (
				Block::getInstance ()
					->setModuleName ($moduleName)
					->setLabel ('My test block # 2')
					->setFields (array (
						Field::getInstance ()
							->setColumnName ('text_field')
							->setLabel ('My text field')
							->setMandatory (true)
							->setModuleName ($moduleName)
							->setName ('text_field')
							->setTableName ('vtiger_test_module')
							->setUiType (FieldInterface::UI_TYPE_TEXT)
							->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
							->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
							->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
							->setPresence (FieldInterface::PRESENCE_VISIBLE)
							->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
							->setReadOnly (FieldInterface::READ_WRITE),
					))
			);
			BlockManager::getInstance (self::$adb)->saveBlock (
				Block::getInstance ()
					->setModuleName ($moduleName)
					->setLabel ('My test block # 2')
					->setFields (array (
						Field::getInstance ()
							->setColumnName ('date_field')
							->setLabel ('My date field')
							->setMandatory (true)
							->setModuleName ($moduleName)
							->setName ('date_field')
							->setTableName ('vtiger_test_module')
							->setUiType (FieldInterface::UI_TYPE_DATE)
							->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
							->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
							->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
							->setPresence (FieldInterface::PRESENCE_VISIBLE)
							->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
							->setReadOnly (FieldInterface::READ_WRITE),
					))
			);

			// Obtener los bloques
			$objects = BlockManager::getInstance (self::$adb)->fetchBlocks ($moduleName);
			// Verificar que el objeto existe y tiene ID
			$this->assertNotNull ($objects, 'Blocks should not be null');
			$this->assertCount (3, $objects, 'Blocks count do not match');
		}

		/**
		 * Eliminar un bloque
		 * @depends testFetchExistingBlock
		 */
		public function testDeleteBlock () {
			// Verificar que el bloque existe en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_blocks b WHERE blockid=?', array (10));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			$block = Block::getInstance ()->setId (10);
			BlockManager::getInstance (self::$adb)->deleteBlock ($block);

			// Verificar que el bloque fue eliminado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_blocks b WHERE blockid=?', array (10));
			$this->assertEquals (0, self::$adb->num_rows ($result));

			// Verificar que se eliminaron los campos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field WHERE block=?', array (10));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

		/**
		 * Crear nuevos bloques para el módulo. Deben eliminarse los existentes excepto el segundo
		 * @depends testDeleteBlock
		 */
		public function testSaveModuleBlocks () {
			$moduleName = 'test_module';

			// Verificar que aun quedan registrados dos bloques para ese módulo
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=?', array ($moduleName));
			$this->assertEquals (2, self::$adb->num_rows ($result));

			// Actualizar los bloques del módulo
			$blocks = array (
				Block::getInstance ()
					->setModuleName ($moduleName)
					->setLabel ('My test block # 12')
					->setFields (array (
						Field::getInstance ()
							->setColumnName ('date_field')
							->setLabel ('My date field')
							->setMandatory (true)
							->setModuleName ($moduleName)
							->setName ('date_field')
							->setTableName ('vtiger_test_module')
							->setUiType (FieldInterface::UI_TYPE_DATE)
							->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
							->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
							->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
							->setPresence (FieldInterface::PRESENCE_VISIBLE)
							->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
							->setReadOnly (FieldInterface::READ_WRITE),
					)),
				Block::getInstance ()
					->setId (20)
					->setModuleName ($moduleName)
					->setLabel ('My test block # 2')
					->setFields (array (
						Field::getInstance ()
							->setBlockId (20)
							->setColumnName ('date_field')
							->setLabel ('My date field')
							->setMandatory (true)
							->setModuleName ($moduleName)
							->setName ('date_field')
							->setTableName ('vtiger_test_module')
							->setUiType (FieldInterface::UI_TYPE_DATE)
							->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
							->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
							->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
							->setPresence (FieldInterface::PRESENCE_VISIBLE)
							->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
							->setReadOnly (FieldInterface::READ_WRITE),
					)),
				Block::getInstance ()
					->setModuleName ($moduleName)
					->setLabel ('My test block # 32')
					->setFields (array (
						Field::getInstance ()
							->setColumnName ('date_field')
							->setLabel ('My date field')
							->setMandatory (true)
							->setModuleName ($moduleName)
							->setName ('date_field')
							->setTableName ('vtiger_test_module')
							->setUiType (FieldInterface::UI_TYPE_DATE)
							->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
							->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
							->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
							->setPresence (FieldInterface::PRESENCE_VISIBLE)
							->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
							->setReadOnly (FieldInterface::READ_WRITE),
					)),
			);
			BlockManager::getInstance (self::$adb)->saveBlocks ($moduleName, $blocks);

			// Verificar que se crearon los bloques
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=?', array ($moduleName));
			$this->assertEquals (3, self::$adb->num_rows ($result));

			// Verificar el primer bloque
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=? WHERE b.blocklabel=?', array ($moduleName, 'My test block # 12'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
			// Verificar que el segundo bloque existe
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=? WHERE b.blocklabel=?', array ($moduleName, 'My test block # 2'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
			// Verificar que el segundo bloque es el mismo que estaba inicialmente guardado
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (20, $row ['blockid']);
			// Verificar el tercer bloque
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=? WHERE b.blocklabel=?', array ($moduleName, 'My test block # 32'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
		}

		/**
		 * Eliminar todos los bloques del módulo
		 * @depends testSaveModuleBlocks
		 */
		public function testDeleteBlocks () {
			$moduleName = 'test_module';

			// Verificar que aun quedan registrados los bloques para ese módulo
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=?', array ($moduleName));
			$this->assertEquals (3, self::$adb->num_rows ($result));

			// Eliminar los bloques
			BlockManager::getInstance (self::$adb)->deleteBlocks ($moduleName);

			// Verificar que se eliminaron los bloques
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_blocks b INNER JOIN vtiger_tab t ON t.tabid=b.tabid AND t.name=?', array ($moduleName));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

	}
	// @codingStandardsIgnoreEnd

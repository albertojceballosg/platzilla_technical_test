<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/FieldManager.php');

	/**
	 * Prueba funcional de la clase FieldManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class FieldManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_entityname, vtiger_blocks, vtiger_field, vtiger_subfields, vtiger_field_dependency, vtiger_fieldmodulerel, vtiger_picklist,
		 * vtiger_profile, vtiger_profile2field, vtiger_role, vtiger_role2picklist, vtiger_crmentity
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
				"CREATE TABLE `vtiger_deletedelements` (
					`elementtype` VARCHAR(255) NOT NULL,
					`modulename` VARCHAR(50) NOT NULL,
					`identifier` VARCHAR(255) NOT NULL,
					`deletedon` DATETIME NOT NULL,
					`serializedobject` LONGTEXT NULL,
					PRIMARY KEY (`elementtype`, `modulename`, `identifier`)
				) ENGINE=InnoDB"
			);
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
					`fieldid` INT(11) NOT NULL,
					`name` VARCHAR(255) NOT NULL,
					`label` VARCHAR(255) NOT NULL,
					`sequence` INT(11) NOT NULL,
					`uitype` INT(11) NOT NULL,
					`length` INT(11) NOT NULL,
					`precision` INT(11) DEFAULT NULL,
					`defaultvalue` VARCHAR(255) DEFAULT NULL,
					`values` TEXT,
					`relmodule` VARCHAR(32) DEFAULT NULL,
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
					`id` int(11) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_picklistvalues_seq` (
					`id` int(11) NOT NULL,
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
				"CREATE TABLE IF NOT EXISTS `vtiger_crmentity` (
					`crmid` INT(19) NOT NULL,
					`smcreatorid` INT(19) NOT NULL DEFAULT '0',
					`modifiedby` INT(19) NOT NULL DEFAULT '0',
					`setype` VARCHAR(30) NOT NULL,
					`description` TEXT,
					`createdtime` DATETIME NOT NULL,
					`viewedtime` DATETIME DEFAULT NULL,
					`status` VARCHAR(50) DEFAULT NULL,
					`version` INT(19) NOT NULL DEFAULT '0',
					`presence` INT(1) DEFAULT '1',
					`deleted` INT(1) NOT NULL DEFAULT '0',
					`smviewer` INT(11) DEFAULT NULL,
					`smownerid` INT(19) DEFAULT NULL,
					`modifiedtime` DATETIME DEFAULT NULL,
					PRIMARY KEY (`crmid`),
					KEY `crmentity_smcreatorid_idx` (`smcreatorid`),
					KEY `crmentity_modifiedby_idx` (`modifiedby`),
					KEY `crmentity_deleted_idx` (`deleted`),
					KEY `crm_ownerid_del_setype_idx` (`deleted`,`setype`)
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
					PRIMARY KEY (`graficoid`)
				) ENGINE=InnoDB"
			);

			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (2, 'test_related_module', 0, 2, 'Test related module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_blocks` (`blockid`, `tabid`, `blocklabel`, `sequence`, `show_title`, `visible`, `create_view`, `edit_view`, `detail_view`, `display_status`, `iscustom`) VALUES (1, 1, 'LBL_TEST_BLOCK', 1, 0, 0, 0, 0, 0, 1, 0)");
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
		 * @param Field $field
		 * @param string $moduleName
		 * @param integer $blockId
		 * @param string $name
		 * @param string $label
		 * @param string $dataType
		 * @param string $tableName
		 * @param boolean $isMandatory
		 * @param integer $precision
		 * @param integer $length
		 * @param integer $sequence
		 * @param string $sqlDataType
		 * @param string $typeOfData
		 * @param integer $uiType
		 */
		private function checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $precision, $length, $sequence, $sqlDataType, $typeOfData, $uiType) {
			// Verificar que el objeto existe y tiene ID
			$this->assertNotNull ($field, 'Saved field should not be null');
			$this->assertNotEmpty ($field->getId (), 'Saved field ID should not be null');

			// Verificar que el objeto tiene las propiedades dependientes del uiType
			$this->assertEquals ($dataType, $field->getDataType (), 'Field data types do not match');
			$this->assertEquals ($isMandatory, $field->isMandatory (), 'Field mandatory properties do not match');
			$this->assertEquals ($precision, $field->getPrecision (), 'Field precisions do not match');
			$this->assertEquals ($length, $field->getLength (), 'Field lengths do not match');
			$this->assertEquals ($sqlDataType, $field->getSqlDataType (), 'Field SQL data types do not match');
			$this->assertNull ($field->getQuickCreateSequence (), 'Field quick create sequences should be null');

			// Verificar que el campo fue creado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?', array ($moduleName, $name));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que el campo contiene todos los valores suministrados
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($blockId, $row ['block'], 'Field block IDs do not match');
			$this->assertEquals ($uiType, $row ['uitype'], 'Field UI types do not match');
			$this->assertEquals ($label, $row ['fieldlabel'], 'Field labels do not match');
			$this->assertEquals ($name, $row ['columnname'], 'Field column names do not match');
			$this->assertEquals ($sequence, $row ['sequence'], 'Field sequences do not match');
			$this->assertEquals ($tableName, $row ['tablename'], 'Field table names do not match');
			$this->assertEquals ($typeOfData, $row ['typeofdata'], 'Field types of data do not match');

			$this->assertEquals (FieldInterface::GENERATED_TYPE_CUSTOM, $row ['generatedtype'], 'Field generated types do not match');
			$this->assertEquals (FieldInterface::READ_WRITE, $row ['readonly'], 'Field read only properties do not match');
			$this->assertEquals (FieldInterface::PRESENCE_VISIBLE, $row ['presence'], 'Field presences do not match');
			$this->assertEquals (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY, $row ['displaytype'], 'Field display types do not match');
			$this->assertEquals (FieldInterface::QUICK_CREATE_DISABLED, $row ['quickcreate'], 'Field quick create properties do not match');
			$this->assertEquals (FieldInterface::MASS_EDITABLE_DISABLED, $row ['masseditable'], 'Field mass editable properties do not match');

			// Verificar que se crearon los perfiles de campo por defecto
			$result = self::$adb->pquery ('SELECT p2f.* FROM vtiger_profile2field p2f INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=? INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?', array ($name, $moduleName));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Field profiles count do not match');
		}

		/**
		 * @param string $moduleName
		 * @param integer $blockId
		 * @param string $name
		 * @param string $label
		 * @param string $dataType
		 * @param string $tableName
		 * @param boolean $isMandatory
		 * @param integer $precision
		 * @param integer $length
		 * @param integer $sequence
		 * @param string $sqlDataType
		 * @param string $typeOfData
		 * @param integer $uiType
		 */
		private function createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $precision, $length, $sequence, $sqlDataType, $typeOfData, $uiType) {
			$field = Field::getInstance ()
				->setBlockId ($blockId)
				->setColumnName ($name)
				->setLabel ($label)
				->setMandatory ($isMandatory)
				->setModuleName ($moduleName)
				->setName ($name)
				->setTableName ($tableName)
				->setUiType ($uiType, $length, $precision)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE);
			FieldManager::getInstance (self::$adb)->saveField ($field);
			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $precision, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Verificar que se genera adecuadamente el campo
		 *
		 * @param integer $fieldId
		 * @param string $name
		 * @param string $label
		 * @param integer $uiType
		 * @param string $sqlDataType
		 * @param integer $sequence
		 * @param integer $length
		 * @param integer $precision
		 * @param string $defaultValue
		 * @param string $values
		 * @param string $moduleReferenceName
		 */
		private function checkGridField ($fieldId, $name, $label, $uiType, $sqlDataType, $sequence, $length, $precision, $defaultValue, $values, $moduleReferenceName) {
			// Verificar que el campo se genera en la tabla vtiger_subfields
			$result = self::$adb->pquery ('SELECT * FROM vtiger_subfields WHERE fieldid=? AND name=?', array ($fieldId, $name));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid field not found');

			// Verificar que las columnas de la tabla vtiger_subfields contienen los valores correctos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($label, $row ['label'], 'Grid field labels do not match');
			$this->assertEquals ($uiType, $row ['uitype'], 'Grid field uitypes do not match');
			$this->assertEquals ($sequence, $row ['sequence'], 'Grid field sequences do not match');
			$this->assertEquals ($length, $row ['length'], 'Grid field lengths do not match');
			$this->assertEquals ($precision, $row ['precision'], 'Grid field precisions do not match');
			$this->assertEquals ($defaultValue, $row ['defaultvalue'], 'Grid field default values do not match');
			$this->assertEquals ($values, $row ['values'], 'Grid field values do not match');
			$this->assertEquals ($moduleReferenceName, $row ['relmodule'], 'Grid field module references do not match');

			// Verificar que en la tabla de valores del grid se genera la columna correctamente
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field WHERE Field=?', array ($name));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid field column not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($sqlDataType, strtoupper ($row ['type']), 'Grid field SQL data types do not match');
		}

		/**
		 * Intentar crear un campo sin la información mínima necesaria
		 * Debe arrojar una FieldException
		 */
		public function testCreateIncompleteField () {
			$field = Field::getInstance ();
			$this->expectException (FieldException::class);
			FieldManager::getInstance (self::$adb)->saveField ($field);
		}

		/**
		 * Intentar crear un campo con un nombre de tabla no existente
		 * Debe arrojar una FieldException
		 */
		public function testCreateInvalidTableNameField () {
			$field = Field::getInstance ()
				->setBlockId (1)
				->setColumnName ('test_field')
				->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setLabel ('My test field')
				->setMandatory (true)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setModuleName ('test_module')
				->setName ('test_field')
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ('non_existing_table_name')
				->setUiType (FieldInterface::UI_TYPE_TEXT);
			$this->expectException (FieldException::class);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_INVALID_TABLE_NAME);
			FieldManager::getInstance (self::$adb)->saveField ($field);
		}

		/**
		 * Intentar crear un campo asociado a un nombre de módulo no existente
		 * Debe arrojar una FieldException
		 */
		public function testCreateNonExistingModuleNameField () {
			$field = Field::getInstance ()
				->setBlockId (1)
				->setColumnName ('test_field')
				->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setLabel ('My test field')
				->setMandatory (true)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setModuleName ('non_existing_module')
				->setName ('test_field')
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ('vtiger_crmentity')
				->setUiType (FieldInterface::UI_TYPE_TEXT);
			$this->expectException (FieldException::class);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_INVALID_MODULE_NAME);
			FieldManager::getInstance (self::$adb)->saveField ($field);
		}

		/**
		 * Intentar crear un campo asociado a un nombre de módulo no existente
		 * Debe arrojar una FieldException
		 */
		public function testCreateNonExistingBlockIdField () {
			$field = Field::getInstance ()
				->setBlockId (2)
				->setColumnName ('test_field')
				->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setLabel ('My test field')
				->setMandatory (true)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setModuleName ('test_module')
				->setName ('test_field')
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ('vtiger_crmentity')
				->setUiType (FieldInterface::UI_TYPE_TEXT);
			$this->expectException (FieldException::class);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_INVALID_BLOCK_ID);
			FieldManager::getInstance (self::$adb)->saveField ($field);
		}

		/**
		 * Intentar crear un campo de tipo Grid sin suministrar el grid
		 * Debe arrojar una FieldException
		 */
		public function testCreateEmptyGridField () {
			$field = Field::getInstance ()
				->setBlockId (1)
				->setColumnName ('test_grid_field')
				->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setLabel ('My grid field')
				->setMandatory (true)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setModuleName ('test_module')
				->setName ('test_grid_field')
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ('vtiger_crmentity')
				->setUiType (FieldInterface::UI_TYPE_GRID);
			$this->expectException (FieldException::class);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_EMPTY_GRID);
			FieldManager::getInstance (self::$adb)->saveField ($field);
		}

		/**
		 * Crear un campo checkbox válido
		 */
		public function testCreateValidCheckboxField () {
			$dataType    = FieldInterface::DATA_TYPE_CHECKBOX;
			$decimals    = 0;
			$isMandatory = false;
			$label       = 'My checkbox field';
			$length      = 3;
			$name        = 'checkbox_field';
			$sequence    = 1;
			$sqlDataType = 'VARCHAR(3)';
			$typeOfData  = 'C~O';
			$uiType      = FieldInterface::UI_TYPE_CHECKBOX;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo código válido
		 * @depends testCreateValidCheckboxField
		 */
		public function testCreateValidCodeField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = 0;
			$isMandatory = true;
			$label       = 'My code field';
			$length      = 100;
			$name        = 'code_field';
			$sequence    = 2;
			$sqlDataType = 'VARCHAR(100)';
			$typeOfData  = 'V~M~LE~100';
			$uiType      = FieldInterface::UI_TYPE_CODE;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo CreatedTime válido
		 * @depends testCreateValidCodeField
		 */
		public function testCreateValidCreatedTimeField () {
			$dataType    = FieldInterface::DATA_TYPE_DATETIME;
			$decimals    = 0;
			$isMandatory = true;
			$label       = 'My createdtime field';
			$length      = 0;
			$name        = 'createdtime_field';
			$sequence    = 3;
			$sqlDataType = 'DATETIME';
			$typeOfData  = 'DT~M';
			$uiType      = FieldInterface::UI_TYPE_CREATED_TIME;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_crmentity';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo CreatedTime válido
		 * @depends testCreateValidCreatedTimeField
		 */
		public function testCreateValidCurrencyField () {
			$dataType    = FieldInterface::DATA_TYPE_NUMBER;
			$decimals    = 2;
			$isMandatory = false;
			$label       = 'My currency field';
			$length      = 15;
			$name        = 'currency_field';
			$sequence    = 4;
			$sqlDataType = 'NUMERIC(15,2)';
			$typeOfData  = 'N~O~15,2';
			$uiType      = FieldInterface::UI_TYPE_CURRENCY;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de fecha válido
		 * @depends testCreateValidCurrencyField
		 */
		public function testCreateValidDateField () {
			$dataType    = FieldInterface::DATA_TYPE_DATE;
			$decimals    = 0;
			$isMandatory = true;
			$label       = 'My date field';
			$length      = 0;
			$name        = 'date_field';
			$sequence    = 5;
			$sqlDataType = 'DATE';
			$typeOfData  = 'D~M';
			$uiType      = FieldInterface::UI_TYPE_DATE;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de fecha/hora válido
		 * @depends testCreateValidDateField
		 */
		public function testCreateValidDateTimeField () {
			$dataType    = FieldInterface::DATA_TYPE_DATETIME;
			$decimals    = 0;
			$isMandatory = false;
			$label       = 'My datetime field';
			$length      = 0;
			$name        = 'datetime_field';
			$sequence    = 6;
			$sqlDataType = 'DATETIME';
			$typeOfData  = 'DT~O';
			$uiType      = FieldInterface::UI_TYPE_DATETIME;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de email válido
		 * @depends testCreateValidDateTimeField
		 */
		public function testCreateValidEmailField () {
			$dataType    = FieldInterface::DATA_TYPE_EMAIL;
			$decimals    = 0;
			$isMandatory = true;
			$label       = 'My email field';
			$length      = 50;
			$name        = 'email_field';
			$sequence    = 7;
			$sqlDataType = 'VARCHAR(50)';
			$typeOfData  = 'E~M';
			$uiType      = FieldInterface::UI_TYPE_EMAIL;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de tipo IMAGE DISPLAY válido
		 * @depends testCreateValidEmailField
		 */
		public function testCreateValidImageDisplayField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My image display field';
			$length      = null;
			$name        = 'image_display_field';
			$sequence    = 8;
			$sqlDataType = 'VARCHAR(255)';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_IMAGE_DISPLAY;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de tipo IMAGE REFERENCE válido
		 * @depends testCreateValidImageDisplayField
		 */
		public function testCreateValidImageReferenceField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = false;
			$label       = 'My image reference field';
			$length      = 255;
			$name        = 'image_reference_field';
			$sequence    = 9;
			$sqlDataType = 'VARCHAR(255)';
			$typeOfData  = 'V~O';
			$uiType      = FieldInterface::UI_TYPE_IMAGE_REFERENCE;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de tipo Modified by válido
		 * @depends testCreateValidImageReferenceField
		 */
		public function testCreateValidModifiedByField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = false;
			$label       = 'My modified by field';
			$length      = null;
			$name        = 'modified_by_field';
			$sequence    = 10;
			$sqlDataType = 'TEXT';
			$typeOfData  = 'V~O';
			$uiType      = FieldInterface::UI_TYPE_MODIFIED_BY;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de tipo Module records válido
		 * @depends testCreateValidModifiedByField
		 */
		public function testCreateValidModuleRecordsField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = false;
			$label       = 'My module records field';
			$length      = null;
			$name        = 'module_records_field';
			$sequence    = 11;
			$sqlDataType = 'VARCHAR(255)';
			$typeOfData  = 'V~O';
			$uiType      = FieldInterface::UI_TYPE_MODULE_RECORDS;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo numérico válido
		 * @depends testCreateValidModuleRecordsField
		 */
		public function testCreateValidNumberField () {
			$dataType    = FieldInterface::DATA_TYPE_NEGATIVE_NUMBER;
			$decimals    = 1;
			$isMandatory = false;
			$label       = 'My number field';
			$length      = 11;
			$name        = 'number_field';
			$sequence    = 12;
			$sqlDataType = 'NUMERIC(11,1)';
			$typeOfData  = 'NN~O~11,1';
			$uiType      = FieldInterface::UI_TYPE_NUMBER;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo owner válido
		 * @depends testCreateValidNumberField
		 */
		public function testCreateValidOwnerField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My owner field';
			$length      = null;
			$name        = 'owner_field';
			$sequence    = 13;
			$sqlDataType = 'INT(19)';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_OWNER;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de porcentaje válido
		 * @depends testCreateValidOwnerField
		 */
		public function testCreateValidPercentageField () {
			$dataType    = FieldInterface::DATA_TYPE_NUMBER;
			$decimals    = 3;
			$isMandatory = true;
			$label       = 'My percentage field';
			$length      = 8;
			$name        = 'percentage_field';
			$sequence    = 14;
			$sqlDataType = 'NUMERIC(8,3)';
			$typeOfData  = 'N~M~8,3';
			$uiType      = FieldInterface::UI_TYPE_PERCENTAGE;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de teléfono válido
		 * @depends testCreateValidPercentageField
		 */
		public function testCreateValidPhoneField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = false;
			$label       = 'My phone field';
			$length      = 30;
			$name        = 'phone_field';
			$sequence    = 15;
			$sqlDataType = 'VARCHAR(30)';
			$typeOfData  = 'V~O';
			$uiType      = FieldInterface::UI_TYPE_PHONE;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de skype válido
		 * @depends testCreateValidProgressBarField
		 */
		public function testCreateValidSkypeField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = false;
			$label       = 'My skype field';
			$length      = 255;
			$name        = 'skype_field';
			$sequence    = 17;
			$sqlDataType = 'VARCHAR(255)';
			$typeOfData  = 'V~O';
			$uiType      = FieldInterface::UI_TYPE_SKYPE;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de texto válido
		 * @depends testCreateValidSkypeField
		 */
		public function testCreateValidTextField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = 0;
			$isMandatory = true;
			$label       = 'My text field';
			$length      = 255;
			$name        = 'text_field';
			$sequence    = 18;
			$sqlDataType = 'VARCHAR(255)';
			$typeOfData  = 'V~M~LE~255';
			$uiType      = FieldInterface::UI_TYPE_TEXT;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de área de texto válido
		 * @depends testCreateValidTextField
		 */
		public function testCreateValidTextAreaField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = 0;
			$isMandatory = true;
			$label       = 'My textarea field';
			$length      = null;
			$name        = 'textarea_field';
			$sequence    = 19;
			$sqlDataType = 'TEXT';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_TEXTAREA;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de tiempo válido
		 * @depends testCreateValidTextAreaField
		 */
		public function testCreateValidTimeField () {
			$dataType    = FieldInterface::DATA_TYPE_TIME;
			$decimals    = 0;
			$isMandatory = false;
			$label       = 'My time field';
			$length      = 0;
			$name        = 'time_field';
			$sequence    = 20;
			$sqlDataType = 'TIME';
			$typeOfData  = 'T~O';
			$uiType      = FieldInterface::UI_TYPE_TIME;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo de URL válido
		 * @depends testCreateValidTimeField
		 */
		public function testCreateValidUrlField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = 0;
			$isMandatory = true;
			$label       = 'My url field';
			$length      = 255;
			$name        = 'url_field';
			$sequence    = 21;
			$sqlDataType = 'VARCHAR(255)';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_URL;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$this->createAndCheckField ($moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Crear un campo grid válido
		 * @depends testCreateValidUrlField
		 */
		public function oldtestCreateValidGridField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My grid field';
			$length      = null;
			$name        = 'test_grid_field';
			$sequence    = 22;
			$sqlDataType = null;
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_GRID;
			$blockId     = 1;
			$moduleName  = 'test_module';
			$tableName   = 'vtiger_test_module';

			$reference = FieldModuleReference::getInstance ()
				->setFieldName ('module_reference_field')
				->setModuleName ($moduleName)
				->setReferencedModuleName ('test_related_module');
			$field     = Field::getInstance ()
				->setBlockId ($blockId)
				->setColumnName ($name)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setGrid (Grid::getInstance ()
					->setModuleName ($moduleName)
					->setName ($name)
					->setFields (array (
						GridField::getInstance ()->setLabel ('Checkbox field')->setName ('checkbox_field')->setUiType (GridFieldInterface::UI_TYPE_CHECKBOX),
						GridField::getInstance ()->setLabel ('Datetime field')->setName ('datetime_field')->setUiType (GridFieldInterface::UI_TYPE_DATETIME),
						GridField::getInstance ()->setLabel ('Module reference field')->setName ('module_reference_field')->setModuleReference ($reference)->setUiType (GridFieldInterface::UI_TYPE_MODULE_REFERENCE),
						GridField::getInstance ()->setLabel ('Number field')->setName ('number_field')->setUiType (GridFieldInterface::UI_TYPE_NUMBER, 16, 3),
						GridField::getInstance ()->setLabel ('Percentage field')->setName ('percentage_field')->setUiType (GridFieldInterface::UI_TYPE_PERCENTAGE, 4, 1),
						GridField::getInstance ()->setLabel ('Picklist field')->setName ('picklist_field')->setUiType (GridFieldInterface::UI_TYPE_PICKLIST)->setValues (array ('First value', 'Second value', 'Third value')),
						GridField::getInstance ()->setLabel ('Text field')->setName ('text_field')->setUiType (GridFieldInterface::UI_TYPE_TEXT, 44),
						GridField::getInstance ()->setLabel ('Textarea field')->setName ('textarea_field')->setUiType (GridFieldInterface::UI_TYPE_TEXTAREA),
						GridField::getInstance ()->setLabel ('URL field')->setName ('url_field')->setUiType (GridFieldInterface::UI_TYPE_URL),
					))
				)
				->setLabel ($label)
				->setMandatory ($isMandatory)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setModuleName ($moduleName)
				->setName ($name)
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ($tableName)
				->setUiType ($uiType);
			FieldManager::getInstance (self::$adb)->saveField ($field);

			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar que se crea la tabla correctamente
			$result = self::$adb->pquery ('SHOW TABLES LIKE ?', array ('vtiger_test_module_test_grid_field'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table not found');

			// Verificar que se generan la cantidad de columnas esperadas (9 correspondientes a los campos + 1 del id del módulo + 1 del id del campo)
			$result = self::$adb->query ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field');
			$this->assertEquals (11, self::$adb->num_rows ($result), 'Grid table columns total do not match');

			// Verificar que se genera la columna del id del módulo
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field WHERE Field=?', array ('test_moduleid'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table does not have moduleid column');

			// Verificar que se genera la columna del id del campo
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field WHERE Field=?', array ('test_grid_fieldid'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table does not have gridid column');

			// Verificar que se generan los campos en la tabla
			$this->checkGridField ($field->getId (), 'checkbox_field', 'Checkbox field', GridFieldInterface::UI_TYPE_CHECKBOX, 'VARCHAR(3)', 1, 3, null, null, null, null);
			$this->checkGridField ($field->getId (), 'datetime_field', 'Datetime field', GridFieldInterface::UI_TYPE_DATETIME, 'DATETIME', 2, null, null, null, null, null);
			$this->checkGridField ($field->getId (), 'module_reference_field', 'Module reference field', GridFieldInterface::UI_TYPE_MODULE_REFERENCE, 'VARCHAR(255)', 3, 255, null, null, null, 'test_related_module');
			$this->checkGridField ($field->getId (), 'number_field', 'Number field', GridFieldInterface::UI_TYPE_NUMBER, 'DECIMAL(16,3)', 4, 16, 3, null, null, null);
			$this->checkGridField ($field->getId (), 'percentage_field', 'Percentage field', GridFieldInterface::UI_TYPE_PERCENTAGE, 'DECIMAL(4,1)', 5, 4, 1, null, null, null);
			$this->checkGridField ($field->getId (), 'picklist_field', 'Picklist field', GridFieldInterface::UI_TYPE_PICKLIST, 'VARCHAR(255)', 6, 255, null, null, json_encode (array ('First value', 'Second value', 'Third value')), null);
			$this->checkGridField ($field->getId (), 'text_field', 'Text field', GridFieldInterface::UI_TYPE_TEXT, 'VARCHAR(44)', 7, 44, null, null, null, null);
			$this->checkGridField ($field->getId (), 'textarea_field', 'Textarea field', GridFieldInterface::UI_TYPE_TEXTAREA, 'TEXT', 8, null, null, null, null, null);
			$this->checkGridField ($field->getId (), 'url_field', 'URL field', GridFieldInterface::UI_TYPE_URL, 'VARCHAR(255)', 9, 255, null, null, null, null);
		}

		/**
		 * Crear un campo de referencia a módulos válido
		 * @depends testCreateValidUrlField
		 */
		public function testCreateValidModuleReferenceField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My module reference field';
			$length      = 1024;
			$name        = 'test_module_reference_field';
			$sequence    = 22;
			$sqlDataType = 'VARCHAR(1024)';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_MODULE_REFERENCE;
			$blockId     = 1;
			$moduleName  = 'test_module';
			$tableName   = 'vtiger_test_module';

			$reference = FieldModuleReference::getInstance ()
				->setFieldName ('test_module_reference_field')
				->setModuleName ($moduleName)
				->setReferencedModuleName ('test_related_module');
			$field     = Field::getInstance ()
				->setBlockId ($blockId)
				->setColumnName ($name)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setLabel ($label)
				->setMandatory ($isMandatory)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setModuleName ($moduleName)
				->setModuleReferences (array ($reference))
				->setName ($name)
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ($tableName)
				->setUiType ($uiType, $length, $decimals);
			FieldManager::getInstance (self::$adb)->saveField ($field);

			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar que se crearon las referencias en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=? AND relmodule=? AND sequence=?', array ($field->getId (), $moduleName, 'test_related_module', 1));
			$this->assertEquals (1, self::$adb->num_rows ($result));
		}

		/**
		 * Crear un campo picklist válido
		 * @depends testCreateValidModuleReferenceField
		 */
		public function testCreateValidPicklistField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My picklist field';
			$length      = 255;
			$name        = 'test_picklist_field';
			$sequence    = 23;
			$sqlDataType = 'VARCHAR(255)';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_PICKLIST;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$picklist = Picklist::getInstance ()
				->setName ($name)
				->setValues (array (
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('First value'),
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('Second value'),
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('Third value'),
				));
			$field    = Field::getInstance ()
				->setBlockId ($blockId)
				->setColumnName ($name)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setLabel ($label)
				->setMandatory ($isMandatory)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setModuleName ($moduleName)
				->setName ($name)
				->setPicklist ($picklist)
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ($tableName)
				->setUiType ($uiType, $length, $decimals);
			FieldManager::getInstance (self::$adb)->saveField ($field);

			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar en base de datos si se creó correctamente el picklist y que se crearon las tablas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ($name));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_{$name}'");
			$this->assertEquals (1, self::$adb->num_rows ($result), "Table vtiger_{$name} should exists");
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_{$name}_seq'");
			$this->assertEquals (1, self::$adb->num_rows ($result), "Table vtiger_{$name}_seq should exists");

			// Verificar en base de datos si se crearon los valores
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ("SELECT * FROM vtiger_{$name} WHERE {$name} IN (?, ?, ?)", array ('First value', 'Second value', 'Third value'));
			$this->assertEquals (3, self::$adb->num_rows ($result), "{$name} values should be 3");

			// Verificar si se asignaron los roles
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ("SELECT * FROM vtiger_role2picklist r2pl INNER JOIN vtiger_{$name} pl ON pl.picklist_valueid=r2pl.picklistvalueid WHERE pl.{$name} IN (?, ?, ?)", array ('First value', 'Second value', 'Third value'));
			$this->assertEquals (6, self::$adb->num_rows ($result), 'roles should be 6');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistvalueid IN (?, ?, ?)', array (10, 20, 30));
			$this->assertEquals (6, self::$adb->num_rows ($result), 'roles should be 6');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistid IN (?)', array (10));
			$this->assertEquals (6, self::$adb->num_rows ($result), 'roles should be 6');
		}

		/**
		 * Intentar obtener un campo inexistente por el ID en la base de datos
		 */
		public function testFetchNonExistingFieldById () {
			$this->assertNull (FieldManager::getInstance (self::$adb)->fetchFieldById (2017), 'Field should be null');
		}

		/**
		 * Obtener un campo existente por el ID en la base de datos
		 * @depends testCreateValidCheckboxField
		 */
		public function testFetchExistingFieldById () {
			$dataType    = FieldInterface::DATA_TYPE_CHECKBOX;
			$decimals    = 0;
			$isMandatory = false;
			$label       = 'My checkbox field';
			$length      = 3;
			$name        = 'checkbox_field';
			$sequence    = 1;
			$sqlDataType = 'VARCHAR(3)';
			$typeOfData  = 'C~O';
			$uiType      = FieldInterface::UI_TYPE_CHECKBOX;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$field = FieldManager::getInstance (self::$adb)->fetchFieldById (10);
			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Intentar obtener un campo inexistente por nombre en la base de datos
		 */
		public function testFetchNonExistingFieldByName () {
			$this->assertNull (FieldManager::getInstance (self::$adb)->fetchFieldByName ('unknown_module', 'text_field'), 'Field should be null');
			$this->assertNull (FieldManager::getInstance (self::$adb)->fetchFieldByName ('test_module', 'unknown_field'), 'Field should be null');
		}

		/**
		 * Obtener un campo existente por nombre en la base de datos
		 * @depends testCreateValidTextField
		 */
		public function testFetchExistingFieldByName () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = 0;
			$isMandatory = true;
			$label       = 'My textarea field';
			$length      = null;
			$name        = 'textarea_field';
			$sequence    = 19;
			$sqlDataType = 'TEXT';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_TEXTAREA;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$field = FieldManager::getInstance (self::$adb)->fetchFieldByName ($moduleName, $name);
			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);
		}

		/**
		 * Obtener un campo grid existente
		 * @depends testCreateValidGridField
		 */
		public function oldtestFetchExistingGridField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My grid field';
			$length      = null;
			$name        = 'test_grid_field';
			$sequence    = 22;
			$sqlDataType = null;
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_GRID;
			$blockId     = 1;
			$moduleName  = 'test_module';
			$tableName   = 'vtiger_test_module';

			$field = FieldManager::getInstance (self::$adb)->fetchFieldByName ($moduleName, $name);
			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar el objeto Grid
			$grid = $field->getGrid ();
			$this->assertNotNull ($grid, 'Grid should not be null');
			$this->assertInstanceOf (Grid::class, $grid, 'Grid should be an instance of Grid');
			$this->assertEquals ($moduleName, $grid->getModuleName (), 'Grid module names do not match');
			$this->assertEquals ($name, $grid->getName (), 'Grid names do not match');

			// Verificar los campos del grid
			$gridFields = $grid->getFields ();
			$this->assertEquals (9, count ($gridFields), 'Grid fields count do not match');
			foreach ($gridFields as $gridField) {
				$values              = !empty ($gridField->getValues ()) ? json_encode ($gridField->getValues ()) : null;
				$moduleReferenceName = !empty ($gridField->getModuleReference ()) ? $gridField->getModuleReference ()->getReferencedModuleName () : null;
				$this->checkGridField ($field->getId (), $gridField->getName (), $gridField->getLabel (), $gridField->getUiType (), $gridField->getSqlDataType (), $gridField->getSequence (), $gridField->getLength (), $gridField->getPrecision (), $gridField->getDefaultValue (), $values, $moduleReferenceName);
			}
		}

		/**
		 * Obtener un campo de referencia a módulos existente
		 * @depends testCreateValidModuleReferenceField
		 */
		public function testFetchExistingModuleReferenceField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My module reference field';
			$length      = 255;
			$name        = 'test_module_reference_field';
			$sequence    = 22;
			$sqlDataType = 'VARCHAR(255)';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_MODULE_REFERENCE;
			$blockId     = 1;
			$moduleName  = 'test_module';
			$tableName   = 'vtiger_test_module';

			$field = FieldManager::getInstance (self::$adb)->fetchFieldByName ($moduleName, $name);
			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar las referencias
			$references = $field->getModuleReferences ();
			$this->assertNotEmpty ($references, 'References should not be empty');
			foreach ($references as $reference) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=? AND relmodule=? AND sequence=?', array ($field->getId (), $reference->getModuleName (), $reference->getReferencedModuleName (), $reference->getSequence ()));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'Reference not found');
			}
		}

		/**
		 * Obtener un campo picklist existente
		 * @depends testCreateValidPicklistField
		 */
		public function testFetchExistingPicklistField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My picklist field';
			$length      = 255;
			$name        = 'test_picklist_field';
			$sequence    = 23;
			$sqlDataType = 'VARCHAR(255)';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_PICKLIST;
			$blockId     = 1;
			$moduleName  = 'test_module';
			$tableName   = 'vtiger_test_module';

			$field = FieldManager::getInstance (self::$adb)->fetchFieldByName ($moduleName, $name);
			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar el objeto Picklist
			$picklist = $field->getPicklist ();
			$this->assertNotNull ($picklist, 'Picklist should not be null');
			$this->assertInstanceOf (Picklist::class, $picklist, 'Picklist should be an instance of Grid');
			$this->assertNotEmpty ($picklist->getId (), 'Picklist should have an ID');
			$this->assertEquals ($name, $picklist->getName (), 'Picklist names do not match');

			// Verificar los valores del picklist
			$picklistValues = $picklist->getValues ();
			$this->assertEquals (3, count ($picklistValues), 'Picklist values count do not match');
			foreach ($picklistValues as $picklistValue) {
				/** @noinspection SqlResolve */
				$result = self::$adb->pquery ("SELECT * FROM vtiger_{$name} WHERE {$name}id=?", array ($picklistValue->getId ()));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'PicklistValue not found');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($row [ $name ], $picklistValue->getValue (), 'PicklistValue values do not match');
				$this->assertEquals ($row ['presence'], $picklistValue->getPresence (), 'PicklistValue presences do not match');
			}
		}

		/**
		 * Obtener los campos asociados a un bloque específico
		 * @depends testCreateValidPicklistField
		 */
		public function testFetchFieldsByBlockId () {
			$blockId = 1;
			$fields = FieldManager::getInstance (self::$adb)->fetchFieldsByBlockId ($blockId);
			$this->assertNotNull ($fields, 'Fields by block should not be null');
			$this->assertEquals (23, count ($fields), 'Fields by block counts do not match');
		}

		/**
		 * Cambiar el tipo del campo grid a text, validar que se elimina el grid
		 * @depends testFetchExistingGridField
		 */
		public function oldtestChangeGridFieldType () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My grid field';
			$length      = 1024;
			$name        = 'test_grid_field';
			$sequence    = 22;
			$sqlDataType = 'VARCHAR(1024)';
			$typeOfData  = 'V~M~LE~1024';
			$uiType      = FieldInterface::UI_TYPE_TEXT;
			$blockId     = 1;
			$moduleName  = 'test_module';
			$tableName   = 'vtiger_test_module';

			// Obtener el campo grid existente
			$field = FieldManager::getInstance (self::$adb)->fetchFieldByName ($moduleName, $name);

			// Cambiar el tipo a texto de 1024 caracteres y guardar
			$field->setUiType (FieldInterface::UI_TYPE_TEXT, $length, $decimals);
			FieldManager::getInstance (self::$adb)->saveField ($field);

			// Validar que el campo queda almacenado correctamente como un campo de texto
			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar que el campo no tiene objeto grid asociado
			$this->assertNull ($field->getGrid (), 'Grid should be null');

			// Verificar en base de datos si se eliminaron correctamente las tablas del grid y los campos del grid
			$result = self::$adb->pquery ('SELECT * FROM vtiger_subfields WHERE fieldid=?', array ($field->getId ()));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Grid fields should not exist');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_{$moduleName}_{$name}'");
			$this->assertEquals (0, self::$adb->num_rows ($result), "Table vtiger_{$moduleName}_{$name} should not exists");
		}

		/**
		 * Cambiar el tipo del campo picklist a text, validar que se elimina el picklist
		 * @depends testFetchExistingPicklistField
		 */
		public function testChangePicklistFieldType () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My picklist field';
			$length      = 1111;
			$name        = 'test_picklist_field';
			$sequence    = 23;
			$sqlDataType = 'VARCHAR(1111)';
			$typeOfData  = 'V~M~LE~1111';
			$uiType      = FieldInterface::UI_TYPE_TEXT;
			$blockId     = 1;
			$moduleName  = 'test_module';
			$tableName   = 'vtiger_test_module';

			// Obtener el campo picklist existente
			$field = FieldManager::getInstance (self::$adb)->fetchFieldByName ($moduleName, $name);

			// Cambiar el tipo a texto de 1111 caracteres y guardar
			$field->setUiType (FieldInterface::UI_TYPE_TEXT, $length, $decimals);
			FieldManager::getInstance (self::$adb)->saveField ($field);

			// Validar que el campo queda almacenado correctamente como un campo de texto
			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar que el campo no tiene objeto picklist asociado
			$this->assertNull ($field->getPicklist (), 'Picklist should be null');

			// Verificar en base de datos si se eliminaron correctamente las tablas del picklist
			$result = self::$adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ($name));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Picklist should not exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_{$name}'");
			$this->assertEquals (0, self::$adb->num_rows ($result), "Table vtiger_{$name} should not exists");
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_{$name}_seq'");
			$this->assertEquals (0, self::$adb->num_rows ($result), "Table vtiger_{$name}_seq should not exists");
		}

		/**
		 * Cambiar el tipo del campo de referencia a módulos a text, validar que se eliminan las referencias
		 * @depends testFetchExistingModuleReferenceField
		 */
		public function testChangeModuleReferenceFieldType () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My module reference field';
			$length      = 777;
			$name        = 'test_module_reference_field';
			$sequence    = 22;
			$sqlDataType = 'VARCHAR(777)';
			$typeOfData  = 'V~M~LE~777';
			$uiType      = FieldInterface::UI_TYPE_TEXT;
			$blockId     = 1;
			$moduleName  = 'test_module';
			$tableName   = 'vtiger_test_module';

			// Obtener el campo de referencia a módulos existente
			$field = FieldManager::getInstance (self::$adb)->fetchFieldByName ($moduleName, $name);

			// Cambiar el tipo a texto de 777 caracteres y guardar
			$field->setUiType (FieldInterface::UI_TYPE_TEXT, $length, $decimals);
			FieldManager::getInstance (self::$adb)->saveField ($field);

			// Validar que el campo queda almacenado correctamente como un campo de texto
			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar que el campo no tiene referencias a módulos asociadas
			$this->assertNull ($field->getModuleReferences (), 'Module references should be null');

			// Verificar en base de datos si se eliminaron correctamente las referencias
			$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=?', array ($field->getId ()));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Module references count should be zero');
		}

		/**
		 * Eliminar un campo existente
		 * @depends testFetchExistingFieldByName
		 */
		public function testDeleteExistingField () {
			$name       = 'textarea_field';
			$moduleName = 'test_module';

			$fm    = FieldManager::getInstance (self::$adb);
			$field = $fm->fetchFieldByName ($moduleName, $name);

			// Determinar cuántas columnas hay en la tabla
			$result = self::$adb->query ("SHOW COLUMNS FROM {$field->getTableName ()}");
			$totalColumns = self::$adb->num_rows ($result);

			// Eliminar el campo
			$fm->deleteField ($field);

			// Verificar que el campo fue eliminado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?', array ($moduleName, $name));
			$this->assertEquals (0, self::$adb->num_rows ($result));

			// Verificar que se eliminaron los perfiles de campo por defecto
			$result = self::$adb->pquery ('SELECT p2f.* FROM vtiger_profile2field p2f INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=? INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?', array ($name, $moduleName));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Field profiles count do not match');

			if ($field->getTableName () != 'vtiger_crmentity') {
				// Verificar que se eliminó la columna de la tabla
				$result = self::$adb->pquery ("SHOW COLUMNS FROM {$field->getTableName ()} WHERE Field=?", array ($field->getColumnName ()));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Table should not contain column field');

				$result = self::$adb->query ("SHOW COLUMNS FROM {$field->getTableName ()}");
				$this->assertEquals (($totalColumns - 1), self::$adb->num_rows ($result), 'Table columns count do not match');
			} else {
				$result = self::$adb->query ("SHOW COLUMNS FROM {$field->getTableName ()}");
				$this->assertEquals ($totalColumns, self::$adb->num_rows ($result), 'Table columns count do not match');
			}
		}

		/**
		 * Eliminar un campo de tipo grid. Se creará uno previamente
		 */
		public function oldtestDeleteGridField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My grid field 2';
			$length      = null;
			$name        = 'test_grid_field2';
			$sequence    = 25;
			$sqlDataType = null;
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_GRID;
			$blockId     = 1;
			$moduleName  = 'test_module';
			$tableName   = 'vtiger_test_module';

			$reference = FieldModuleReference::getInstance ()
				->setFieldName ('module_reference_field')
				->setModuleName ($moduleName)
				->setReferencedModuleName ('test_related_module');
			$field     = Field::getInstance ()
				->setBlockId ($blockId)
				->setColumnName ($name)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setGrid (Grid::getInstance ()
					->setModuleName ($moduleName)
					->setName ($name)
					->setFields (array (
						GridField::getInstance ()->setLabel ('Checkbox field')->setName ('checkbox_field')->setUiType (GridFieldInterface::UI_TYPE_CHECKBOX),
						GridField::getInstance ()->setLabel ('Datetime field')->setName ('datetime_field')->setUiType (GridFieldInterface::UI_TYPE_DATETIME),
						GridField::getInstance ()->setLabel ('Module reference field')->setName ('module_reference_field')->setModuleReference ($reference)->setUiType (GridFieldInterface::UI_TYPE_MODULE_REFERENCE),
						GridField::getInstance ()->setLabel ('Number field')->setName ('number_field')->setUiType (GridFieldInterface::UI_TYPE_NUMBER, 16, 3),
						GridField::getInstance ()->setLabel ('Percentage field')->setName ('percentage_field')->setUiType (GridFieldInterface::UI_TYPE_PERCENTAGE, 4, 1),
						GridField::getInstance ()->setLabel ('Picklist field')->setName ('picklist_field')->setUiType (GridFieldInterface::UI_TYPE_PICKLIST)->setValues (array ('First value', 'Second value', 'Third value')),
						GridField::getInstance ()->setLabel ('Text field')->setName ('text_field')->setUiType (GridFieldInterface::UI_TYPE_TEXT, 44),
						GridField::getInstance ()->setLabel ('Textarea field')->setName ('textarea_field')->setUiType (GridFieldInterface::UI_TYPE_TEXTAREA),
						GridField::getInstance ()->setLabel ('URL field')->setName ('url_field')->setUiType (GridFieldInterface::UI_TYPE_URL),
					))
				)
				->setLabel ($label)
				->setMandatory ($isMandatory)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setModuleName ($moduleName)
				->setName ($name)
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ($tableName)
				->setUiType ($uiType);
			FieldManager::getInstance (self::$adb)->saveField ($field);

			// Determinar cuántas columnas hay en la tabla
			$result       = self::$adb->query ("SHOW COLUMNS FROM {$tableName}");
			$totalColumns = self::$adb->num_rows ($result);

			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar que se crea la tabla correctamente
			$result = self::$adb->pquery ('SHOW TABLES LIKE ?', array ("vtiger_{$moduleName}_{$name}"));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table not found');

			// Verificar que se generan la cantidad de columnas esperadas (9 correspondientes a los campos + 1 del id del módulo + 1 del id del campo)
			$result = self::$adb->query ("SHOW COLUMNS FROM vtiger_{$moduleName}_{$name}");
			$this->assertEquals (11, self::$adb->num_rows ($result), 'Grid table columns total do not match');

			// Verificar que se genera la columna del id del módulo
			$result = self::$adb->pquery ("SHOW COLUMNS FROM vtiger_{$moduleName}_{$name} WHERE Field=?", array ("{$moduleName}id"));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table does not have moduleid column');

			// Verificar que se genera la columna del id del campo
			$result = self::$adb->pquery ("SHOW COLUMNS FROM vtiger_{$moduleName}_{$name} WHERE Field=?", array ("{$name}id"));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table does not have gridid column');

			// Eliminar el campo
			FieldManager::getInstance (self::$adb)->deleteField ($field);

			// Verificar que el campo fue eliminado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT f.* FROM vtiger_field f INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=? WHERE f.fieldname=?', array ($moduleName, $name));
			$this->assertEquals (0, self::$adb->num_rows ($result));

			// Verificar en base de datos si se eliminaron correctamente las tablas del grid y los campos del grid
			$result = self::$adb->pquery ('SELECT * FROM vtiger_subfields WHERE fieldid=?', array ($field->getId ()));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Grid fields should not exist');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_{$moduleName}_{$name}'");
			$this->assertEquals (0, self::$adb->num_rows ($result), "Table vtiger_{$moduleName}_{$name} should not exists");

			// Verificar que se mantienen iguales las columnas de la tabla
			$result = self::$adb->query ("SHOW COLUMNS FROM {$tableName}");
			$this->assertEquals ($totalColumns, self::$adb->num_rows ($result), 'Table columns count do not match');

			// Verificar que se eliminaron los perfiles de campo por defecto
			$result = self::$adb->pquery ('SELECT p2f.* FROM vtiger_profile2field p2f INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=? INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?', array ($name, $moduleName));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Field profiles count do not match');
		}

		/**
		 * Eliminar un campo de referencia a módulos. Se creará uno previamente
		 */
		public function testDeleteModuleReferenceField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My module reference field';
			$length      = 1024;
			$name        = 'test_module_reference_field2';
			$sequence    = 24;
			$sqlDataType = 'VARCHAR(1024)';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_MODULE_REFERENCE;
			$blockId     = 1;
			$moduleName  = 'test_module';
			$tableName   = 'vtiger_test_module';

			$reference = FieldModuleReference::getInstance ()
				->setFieldName ('test_module_reference_field2')
				->setModuleName ($moduleName)
				->setReferencedModuleName ('test_related_module');
			$field     = Field::getInstance ()
				->setBlockId ($blockId)
				->setColumnName ($name)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setLabel ($label)
				->setMandatory ($isMandatory)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setModuleName ($moduleName)
				->setModuleReferences (array ($reference))
				->setName ($name)
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ($tableName)
				->setUiType ($uiType, $length, $decimals);
			FieldManager::getInstance (self::$adb)->saveField ($field);

			// Determinar cuántas columnas hay en la tabla
			$result       = self::$adb->query ("SHOW COLUMNS FROM {$tableName}");
			$totalColumns = self::$adb->num_rows ($result);

			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar que se crearon las referencias en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=? AND relmodule=? AND sequence=?', array ($field->getId (), $moduleName, 'test_related_module', 1));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Eliminar el campo
			FieldManager::getInstance (self::$adb)->deleteField ($field);

			// Verificar que el campo no tiene referencias a módulos asociadas
			$this->assertNull ($field->getModuleReferences (), 'Module references should be null');

			// Verificar en base de datos si se eliminaron correctamente las referencias
			$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=?', array ($field->getId ()));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Module references count should be zero');

			// Verificar que se eliminaron los perfiles de campo por defecto
			$result = self::$adb->pquery ('SELECT p2f.* FROM vtiger_profile2field p2f INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=? INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?', array ($name, $moduleName));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Field profiles count do not match');

			// Verificar que se eliminó la columna de la tabla
			$result = self::$adb->pquery ("SHOW COLUMNS FROM {$tableName} WHERE Field=?", array ($field->getColumnName ()));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Table should not contain column field');

			$result = self::$adb->query ("SHOW COLUMNS FROM {$tableName}");
			$this->assertEquals (($totalColumns - 1), self::$adb->num_rows ($result), 'Table columns count do not match');
		}

		/**
		 * Eliminar un campo picklist válido. Se creará uno previamente
		 */
		public function testDeletePicklistField () {
			$dataType    = FieldInterface::DATA_TYPE_VARCHAR;
			$decimals    = null;
			$isMandatory = true;
			$label       = 'My picklist field';
			$length      = 255;
			$name        = 'test_picklist_field2';
			$sequence    = 24;
			$sqlDataType = 'VARCHAR(255)';
			$typeOfData  = 'V~M';
			$uiType      = FieldInterface::UI_TYPE_PICKLIST;

			$blockId    = 1;
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';

			$picklist = Picklist::getInstance ()
				->setName ($name)
				->setValues (array (
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('First value'),
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('Second value'),
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('Third value'),
				));
			$field    = Field::getInstance ()
				->setBlockId ($blockId)
				->setColumnName ($name)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setLabel ($label)
				->setMandatory ($isMandatory)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setModuleName ($moduleName)
				->setName ($name)
				->setPicklist ($picklist)
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE)
				->setTableName ($tableName)
				->setUiType ($uiType, $length, $decimals);
			FieldManager::getInstance (self::$adb)->saveField ($field);

			// Determinar cuántas columnas hay en la tabla
			$result       = self::$adb->query ("SHOW COLUMNS FROM {$tableName}");
			$totalColumns = self::$adb->num_rows ($result);

			$this->checkField ($field, $moduleName, $blockId, $name, $label, $dataType, $tableName, $isMandatory, $decimals, $length, $sequence, $sqlDataType, $typeOfData, $uiType);

			// Verificar en base de datos si se creó correctamente el picklist y que se crearon las tablas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ($name));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_{$name}'");
			$this->assertEquals (1, self::$adb->num_rows ($result), "Table vtiger_{$name} should exists");
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_{$name}_seq'");
			$this->assertEquals (1, self::$adb->num_rows ($result), "Table vtiger_{$name}_seq should exists");

			// Verificar en base de datos si se crearon los valores
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ("SELECT * FROM vtiger_{$name} WHERE {$name} IN (?, ?, ?)", array ('First value', 'Second value', 'Third value'));
			$this->assertEquals (3, self::$adb->num_rows ($result), "{$name} values should be 3");

			// Verificar si se asignaron los roles
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ("SELECT * FROM vtiger_role2picklist r2pl INNER JOIN vtiger_{$name} pl ON pl.picklist_valueid=r2pl.picklistvalueid WHERE pl.{$name} IN (?, ?, ?)", array ('First value', 'Second value', 'Third value'));
			$this->assertEquals (6, self::$adb->num_rows ($result), 'roles should be 6');

			// Eliminar el campo
			FieldManager::getInstance (self::$adb)->deleteField ($field);

			// Verificar en base de datos si se eliminaron correctamente las tablas del picklist
			$result = self::$adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ($name));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Picklist should not exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_{$name}'");
			$this->assertEquals (0, self::$adb->num_rows ($result), "Table vtiger_{$name} should not exists");
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_{$name}_seq'");
			$this->assertEquals (0, self::$adb->num_rows ($result), "Table vtiger_{$name}_seq should not exists");
			$result = self::$adb->query ('SELECT * FROM vtiger_role2picklist');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'roles count should be zero');

			// Verificar que se eliminó la columna de la tabla
			$result = self::$adb->pquery ("SHOW COLUMNS FROM {$tableName} WHERE Field=?", array ($field->getColumnName ()));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Table should not contain column field');

			$result = self::$adb->query ("SHOW COLUMNS FROM {$tableName}");
			$this->assertEquals (($totalColumns - 1), self::$adb->num_rows ($result), 'Table columns count do not match');

			// Verificar que se eliminaron los perfiles de campo por defecto
			$result = self::$adb->pquery ('SELECT p2f.* FROM vtiger_profile2field p2f INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=? INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?', array ($name, $moduleName));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Field profiles count do not match');
		}

		/**
		 * Eliminar los campos asociados a un bloque
		 * @depends testDeletePicklistField
		 */
		public function testDeleteFieldsByBlockId () {
			$blockId = 1;
			$moduleName = 'test_module';
			$tableName = 'vtiger_test_module';

			FieldManager::getInstance (self::$adb)->deleteFieldsByBlockId ($blockId);

			// Verificar que los campos fueron eliminados correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT f.* FROM vtiger_field f WHERE f.block=?', array ($blockId));
			$this->assertEquals (0, self::$adb->num_rows ($result));

			// Verificar que se eliminaron los perfiles de campo por defecto
			$result = self::$adb->pquery ('SELECT p2f.* FROM vtiger_profile2field p2f INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.block=?', array ($blockId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Field profiles count do not match');

			// Verificar que se eliminaron las columnas de la tabla excepto la columna ID
			$result = self::$adb->query ("SHOW COLUMNS FROM {$tableName}");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table should contain column field');
			$result = self::$adb->pquery ("SHOW COLUMNS FROM {$tableName} WHERE Field=?", array ("{$moduleName}id"));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table should contain column field');
		}

	}
	// @codingStandardsIgnoreEnd

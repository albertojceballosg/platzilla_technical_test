<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/ApplicationManager.php');

	/**
	 * Prueba funcional de la clase ApplicationManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ApplicationManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas
		 * 4. Simular existencia de dos perfiles y dos roles
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
				"CREATE TABLE `vtiger_modentity_num` (
					`num_id` INT(19) NOT NULL,
					`semodule` VARCHAR(50) NOT NULL,
					`prefix` VARCHAR(50) NOT NULL DEFAULT '',
					`start_id` VARCHAR(50) NOT NULL,
					`cur_id` VARCHAR(50) NOT NULL,
					`active` VARCHAR(2) NOT NULL,
					PRIMARY KEY (`num_id`),
					UNIQUE INDEX `num_idx` (`num_id`),
					INDEX `semodule_active_idx` (`semodule`, `active`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_modentity_num_seq` (
					`id` INT(11) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_parenttab` (
					`parenttabid` INT(19) NOT NULL,
					`parenttab_label` VARCHAR(100) NOT NULL,
					`sequence` INT(10) NOT NULL,
					`visible` INT(2) NOT NULL DEFAULT '0',
					`padre` INT(11) NOT NULL DEFAULT '0',
					`color` VARCHAR(50) NULL DEFAULT NULL,
					`iconclass` VARCHAR(50) NULL DEFAULT NULL,
					`avaliable` INT(11) NULL DEFAULT '1',
					PRIMARY KEY (`parenttabid`),
					INDEX `parenttab_parenttabid_parenttabl_label_visible_idx` (`parenttabid`, `parenttab_label`, `visible`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_parenttabrel` (
					`parenttabid` INT(19) NOT NULL,
					`tabid` INT(19) NOT NULL,
					`sequence` INT(3) NOT NULL,
					`fieldpk` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					PRIMARY KEY (`fieldpk`),
					INDEX `parenttabrel_tabid_parenttabid_idx` (`tabid`, `parenttabid`),
					INDEX `fk_2_vtiger_parenttabrel` (`parenttabid`),
					CONSTRAINT `fk_1_vtiger_parenttabrel` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE,
					CONSTRAINT `fk_2_vtiger_parenttabrel` FOREIGN KEY (`parenttabid`) REFERENCES `vtiger_parenttab` (`parenttabid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
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
					`id` INT(11) NOT NULL,
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
				"CREATE TABLE `vtiger_subfields` (
					`fieldid` INT(11) NOT NULL,
					`name` VARCHAR(255) NOT NULL,
					`label` VARCHAR(255) NOT NULL,
					`sequence` INT(11) NOT NULL,
					`uitype` INT(11) NOT NULL,
					`length` INT(11) NULL DEFAULT NULL,
					`precision` INT(11) NULL DEFAULT NULL,
					`defaultvalue` VARCHAR(255) NULL DEFAULT NULL,
					`values` TEXT NULL,
					`relmodule` VARCHAR(32) NULL DEFAULT NULL,
					PRIMARY KEY (`fieldid`, `name`),
					CONSTRAINT `vtiger_subfields_ibfk_1` FOREIGN KEY (`fieldid`) REFERENCES `vtiger_field` (`fieldid`) ON UPDATE CASCADE ON DELETE CASCADE
				) COLLATE='utf8_general_ci' ENGINE=InnoDB"
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
					`cvid` INT(19) NOT NULL,
					`viewname` VARCHAR(100) NOT NULL,
					`setdefault` INT(1) DEFAULT '0',
					`setmetrics` INT(1) DEFAULT '0',
					`entitytype` VARCHAR(25) NOT NULL,
					`status` INT(1) DEFAULT '1',
					`userid` INT(19) DEFAULT '1',
					`clientview` INT(11) NOT NULL DEFAULT '0',
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`cvid`),
					KEY `customview_entitytype_idx` (`entitytype`),
					CONSTRAINT `fk_1_vtiger_customview` FOREIGN KEY (`entitytype`) REFERENCES `vtiger_tab` (`name`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_customview_seq` (
					`id` INT(11) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvadvfilter` (
					`cvid` INT(19) NOT NULL,
					`columnindex` INT(11) NOT NULL,
					`columnname` VARCHAR(250) DEFAULT '',
					`comparator` VARCHAR(10) DEFAULT '',
					`value` VARCHAR(200) DEFAULT '',
					`groupid` INT(11) DEFAULT '1',
					`column_condition` VARCHAR(255) DEFAULT 'and',
					PRIMARY KEY (`cvid`,`columnindex`),
					KEY `cvadvfilter_cvid_idx` (`cvid`),
					CONSTRAINT `fk_1_vtiger_cvadvfilter` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvadvfilter_grouping` (
					`groupid` INT(11) NOT NULL,
					`cvid` INT(19) NOT NULL,
					`group_condition` VARCHAR(255) DEFAULT NULL,
					`condition_expression` TEXT,
					PRIMARY KEY (`groupid`,`cvid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvcolumnlist` (
					`cvid` INT(19) NOT NULL,
					`columnindex` INT(11) NOT NULL,
					`columnname` VARCHAR(250) DEFAULT '',
					PRIMARY KEY (`cvid`,`columnindex`),
					KEY `cvcolumnlist_columnindex_idx` (`columnindex`),
					KEY `cvcolumnlist_cvid_idx` (`cvid`),
					CONSTRAINT `fk_1_vtiger_cvcolumnlist` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvstdfilter` (
					`cvid` INT(19) NOT NULL,
					`columnname` VARCHAR(250) DEFAULT '',
					`stdfilter` VARCHAR(250) DEFAULT '',
					`startdate` DATE DEFAULT NULL,
					`enddate` DATE DEFAULT NULL,
					PRIMARY KEY (`cvid`),
					KEY `cvstdfilter_cvid_idx` (`cvid`),
					CONSTRAINT `fk_1_vtiger_cvstdfilter` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_cvadvcolor` (
					`cvid` INT(19) NOT NULL,
					`columnindex` INT(11) NOT NULL,
					`columnname` VARCHAR(250) NULL DEFAULT '',
					`comparator` VARCHAR(10) NULL DEFAULT '',
					`value` VARCHAR(200) NULL DEFAULT '',
					`groupid` INT(11) NULL DEFAULT '1',
					`column_condition` VARCHAR(255) NULL DEFAULT 'and',
					PRIMARY KEY (`cvid`, `columnindex`),
					INDEX `cvadvfilter_cvid_idx` (`cvid`),
					CONSTRAINT `fk_1_vtiger_cvadvcolor` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_cvadvcolor_grouping` (
					`groupid` INT(11) NOT NULL,
					`cvid` INT(19) NOT NULL,
					`group_color` VARCHAR(20) NULL DEFAULT NULL,
					`condition_expression` TEXT NULL,
					PRIMARY KEY (`groupid`, `cvid`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_custombuttons` (
					`custombuttonid` INT(11) NOT NULL AUTO_INCREMENT,
					`module` VARCHAR(30) NOT NULL,
					`action` VARCHAR(20) NOT NULL DEFAULT 'DetailView',
					`style` VARCHAR(255) NOT NULL,
					`label` VARCHAR(255) NOT NULL,
					`onclick` VARCHAR(200) NULL DEFAULT NULL,
					`link` VARCHAR(200) NULL DEFAULT NULL,
					`type` VARCHAR(10) NOT NULL,
					`description` TEXT NULL,
					`active` INT(1) NOT NULL DEFAULT '1',
					`runinnewwindow` TINYINT(4) NOT NULL DEFAULT '1',
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`custombuttonid`)
				) COLLATE='utf8_general_ci' ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_graficos` (
					`graficoid` INT(11) NOT NULL AUTO_INCREMENT,
					`fld_module` VARCHAR(20) NOT NULL,
					`fieldoperation` VARCHAR(255) NOT NULL,
					`operation` INT(2) NOT NULL,
					`tipografico` VARCHAR(20) NOT NULL,
					`title` VARCHAR(400) NOT NULL,
					`roles_grafico` VARCHAR(200) NULL DEFAULT NULL,
					`sqlprimarioreporte` TEXT NULL,
					`varreporte` TEXT NULL,
					`reporteavanzado` INT(11) NOT NULL DEFAULT '0',
					`comparar` INT(11) NULL DEFAULT '0',
					`ishome` INT(1) NOT NULL DEFAULT '0',
					`fieldgrouping` VARCHAR(20) NULL DEFAULT NULL,
					`dategrouping` TINYINT(4) NULL DEFAULT NULL,
					`applicationcodes` TEXT NULL,
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`graficoid`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_module_report` (
					`tabid` INT(11) NOT NULL,
					`reportavailable` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`tabid`, `reportavailable`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_relatedlists` (
					`relation_id` INT(19) NOT NULL,
					`tabid` INT(10) NULL DEFAULT NULL,
					`related_tabid` INT(10) NULL DEFAULT NULL,
					`name` VARCHAR(100) NULL DEFAULT NULL,
					`sequence` INT(10) NULL DEFAULT NULL,
					`label` VARCHAR(100) NULL DEFAULT NULL,
					`presence` INT(10) NOT NULL DEFAULT '0',
					`actions` VARCHAR(50) NOT NULL DEFAULT '',
					`relfield` VARCHAR(255) NULL DEFAULT NULL,
					PRIMARY KEY (`relation_id`),
					INDEX `relatedlists_relation_id_idx` (`relation_id`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_profile` (
					`profileid` INT(10) NOT NULL AUTO_INCREMENT,
					`profilename` VARCHAR(50) NOT NULL,
					`description` TEXT,
					`applicationcodes` TEXT NULL,
					PRIMARY KEY (`profileid`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_profile2globalpermissions` (
					`profileid` INT(19) NOT NULL,
					`globalactionid` INT(19) NOT NULL,
					`globalactionpermission` INT(19) DEFAULT NULL,
					PRIMARY KEY (`profileid`,`globalactionid`),
					KEY `idx_profile2globalpermissions` (`profileid`,`globalactionid`),
					CONSTRAINT `fk_1_vtiger_profile2globalpermissions` FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
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
				"CREATE TABLE IF NOT EXISTS `vtiger_profile2customview` (
					`profileid` INT(10) NOT NULL,
					`cvid` INT(19) NOT NULL,
					`tabid` INT(19) NOT NULL,
					`permissions` TINYINT(4) NOT NULL DEFAULT '0',
					`setdefault` INT(11) NOT NULL DEFAULT '0',
					PRIMARY KEY (`profileid`,`cvid`),
					KEY `FK_vtiger_profile2customview_vtiger_customview` (`cvid`),
					KEY `IDX_profile2customview_permissions` (`permissions`),
					KEY `FK_vtiger_profile2customview_vtiger_tab` (`tabid`),
					KEY `IDX_profile2customview_profileid_tabid` (`profileid`,`tabid`),
					KEY `IDX_profile2customview_setdefault` (`setdefault`),
					CONSTRAINT `FK_vtiger_profile2customview_vtiger_customview` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE ON UPDATE CASCADE,
					CONSTRAINT `FK_vtiger_profile2customview_vtiger_profile` FOREIGN KEY (`profileid`) REFERENCES `vtiger_profile` (`profileid`) ON DELETE CASCADE ON UPDATE CASCADE,
					CONSTRAINT `FK_vtiger_profile2customview_vtiger_tab` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_profile2standardpermissions` (
					`profileid` INT(11) NOT NULL,
					`tabid` INT(10) NOT NULL,
					`operation` INT(10) NOT NULL,
					`permissions` INT(1) DEFAULT NULL,
					PRIMARY KEY (`profileid`,`tabid`,`operation`),
					KEY `profile2standardpermissions_profileid_tabid_operation_idx` (`profileid`,`tabid`,`operation`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_profile2tab` (
					`profileid` INT(11) DEFAULT NULL,
					`tabid` INT(10) DEFAULT NULL,
					`permissions` INT(10) NOT NULL DEFAULT '0',
					`fieldpk` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
					PRIMARY KEY (`fieldpk`),
					KEY `profile2tab_profileid_tabid_idx` (`profileid`,`tabid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_profile2utility` (
					`profileid` INT(11) NOT NULL,
					`tabid` INT(11) NOT NULL,
					`activityid` INT(11) NOT NULL,
					`permission` INT(1) DEFAULT NULL,
					PRIMARY KEY (`profileid`,`tabid`,`activityid`),
					KEY `profile2utility_profileid_tabid_activityid_idx` (`profileid`,`tabid`,`activityid`)
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
				"CREATE TABLE `vtiger_actionmapping` (
					`actionid` INT(19) NOT NULL,
					`actionname` VARCHAR(200) NOT NULL,
					`securitycheck` INT(19) NULL DEFAULT NULL,
					PRIMARY KEY (`actionid`, `actionname`)
				) ENGINE=InnoDB"
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
				"CREATE TABLE `vtiger_org_share_action_mapping` (
					`share_action_id` INT(19) NOT NULL,
					`share_action_name` VARCHAR(200) NULL DEFAULT NULL,
					PRIMARY KEY (`share_action_id`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_def_org_share` (
					`ruleid` INT(11) NOT NULL AUTO_INCREMENT,
					`tabid` INT(11) NOT NULL,
					`permission` INT(19) NULL DEFAULT NULL,
					`editstatus` INT(19) NULL DEFAULT NULL,
					PRIMARY KEY (`ruleid`),
					INDEX `fk_1_vtiger_def_org_share` (`permission`),
					CONSTRAINT `fk_1_vtiger_def_org_share` FOREIGN KEY (`permission`) REFERENCES `vtiger_org_share_action_mapping` (`share_action_id`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_def_org_share_seq` (
					`id` INT(11) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_ws_entity` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`name` VARCHAR(25) NOT NULL,
					`handler_path` VARCHAR(255) NOT NULL,
					`handler_class` VARCHAR(64) NOT NULL,
					`ismodule` INT(3) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_ws_entity_seq` (
					`id` INT(11) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_category_apps` (
					`catappid` INT(11) NOT NULL AUTO_INCREMENT,
					`code` VARCHAR(20) NOT NULL,
					`name` VARCHAR(256) NOT NULL,
					`status` VARCHAR(20) NOT NULL,
					`description` TEXT NULL,
					PRIMARY KEY (`catappid`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_config_applications` (
					`config_applicationsid` INT(11) NOT NULL AUTO_INCREMENT,
					`app_code` VARCHAR(255) NOT NULL,
					`app_name` VARCHAR(100) NOT NULL,
					`app_descripcion` TEXT NULL,
					`app_status` VARCHAR(255) NOT NULL,
					`app_date_act` DATETIME NULL DEFAULT NULL,
					`app_profile` INT(11) NULL DEFAULT NULL,
					`app_price` DECIMAL(10,2) NULL DEFAULT '0.00',
					`app_category` INT(11) NOT NULL,
					`app_url` VARCHAR(256) NOT NULL,
					`settings_blocks_id` INT(11) NULL DEFAULT NULL,
					PRIMARY KEY (`config_applicationsid`),
					UNIQUE INDEX `app_code` (`app_code`),
					INDEX `FK_vtiger_config_applications_vtiger_profile` (`app_profile`),
					INDEX `FK_vtiger_config_applications_vtiger_category_apps` (`app_category`),
					CONSTRAINT `FK_vtiger_config_applications_vtiger_category_apps` FOREIGN KEY (`app_category`) REFERENCES `vtiger_category_apps` (`catappid`) ON UPDATE CASCADE,
					CONSTRAINT `FK_vtiger_config_applications_vtiger_profile` FOREIGN KEY (`app_profile`) REFERENCES `vtiger_profile` (`profileid`) ON UPDATE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_configapps_tab` (
					`config_applicationsid` INT(19) NOT NULL,
					`tabid` INT(19) NOT NULL,
					`fieldpk` INT(11) NOT NULL AUTO_INCREMENT,
					PRIMARY KEY (`fieldpk`),
					INDEX `config_applicationsid_tabid_idx` (`tabid`, `config_applicationsid`),
					INDEX `fk_2_vtiger_configapps_tab` (`config_applicationsid`),
					CONSTRAINT `fk_1_vtiger_configapps_tab` FOREIGN KEY (`tabid`) REFERENCES `vtiger_tab` (`tabid`) ON DELETE CASCADE,
					CONSTRAINT `fk_2_vtiger_configapps_tab` FOREIGN KEY (`config_applicationsid`) REFERENCES `vtiger_config_applications` (`config_applicationsid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_calendarviews` (
					`calendarviewid` INT(19) NOT NULL AUTO_INCREMENT,
					`label` VARCHAR(50) NOT NULL,
					`modulename` VARCHAR(25) NOT NULL,
					`titlemodulename` VARCHAR(25) NOT NULL,
					`titlefieldname` VARCHAR(50) NOT NULL,
					`frommodulename` VARCHAR(25) NOT NULL,
					`fromfieldname` VARCHAR(25) NOT NULL,
					`tomodulename` VARCHAR(25) NULL DEFAULT NULL,
					`tofieldname` VARCHAR(25) NULL DEFAULT NULL,
					`backgroundcolor` VARCHAR(7) NOT NULL DEFAULT '#FFFFFF',
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`calendarviewid`),
					UNIQUE INDEX `label` (`label`),
					INDEX `FK_vtiger_calendarviews_modulename` (`modulename`),
					INDEX `titlemodulename_titlefieldname` (`titlemodulename`, `titlefieldname`),
					INDEX `frommodulename_fromfieldname` (`frommodulename`, `fromfieldname`),
					INDEX `tomodulename_tofieldname` (`tomodulename`, `tofieldname`),
					CONSTRAINT `FK_vtiger_calendarviews_modulename` FOREIGN KEY (`modulename`) REFERENCES `vtiger_tab` (`name`) ON UPDATE CASCADE ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_calendarviews_applications` (
					`calendarviewid` INT(19) NOT NULL,
					`applicationcode` VARCHAR(255) NOT NULL,
					PRIMARY KEY (`calendarviewid`, `applicationcode`),
					INDEX `FK_vtiger_calendarviews_applications_vtiger_config_applications` (`applicationcode`),
					CONSTRAINT `FK_vtiger_calendarviews_applications_vtiger_config_applications` FOREIGN KEY (`applicationcode`) REFERENCES `vtiger_config_applications` (`app_code`) ON UPDATE CASCADE ON DELETE CASCADE,
					CONSTRAINT `FK_vtiger_calendarviews_applications_vtiger_calendarviews` FOREIGN KEY (`calendarviewid`) REFERENCES `vtiger_calendarviews` (`calendarviewid`) ON UPDATE CASCADE ON DELETE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_calendarviews_rules` (
					`ruleid` INT(19) NOT NULL AUTO_INCREMENT,
					`calendarviewid` INT(19) NOT NULL,
					`modulename` VARCHAR(25) NOT NULL,
					`fieldname` VARCHAR(50) NOT NULL,
					`operator` VARCHAR(2) NOT NULL,
					`value` VARCHAR(255) NOT NULL,
					`backgroundcolor` VARCHAR(7) NOT NULL,
					PRIMARY KEY (`ruleid`),
					INDEX `FK_vtiger_calendarviews_rules_calendarviews` (`calendarviewid`),
					INDEX `modulename_fieldname` (`modulename`, `fieldname`),
					CONSTRAINT `FK_vtiger_calendarviews_rules_calendarviews` FOREIGN KEY (`calendarviewid`) REFERENCES `vtiger_calendarviews` (`calendarviewid`) ON UPDATE CASCADE ON DELETE CASCADE,
					CONSTRAINT `FK_vtiger_calendarviews_rules_modulename` FOREIGN KEY (`modulename`) REFERENCES `vtiger_tab` (`name`) ON UPDATE CASCADE ON DELETE CASCADE
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
				"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_cfg_actions` (
					`actiontype` varchar(25) NOT NULL,
					`scope` varchar(10) NOT NULL DEFAULT 'USER',
					`handlerclass` varchar(255) NOT NULL,
					`handlermethod` varchar(255) NOT NULL,
					PRIMARY KEY (`actiontype`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_cfg_categories` (
					`categoryname` varchar(255) NOT NULL,
					`description` text NOT NULL,
					PRIMARY KEY (`categoryname`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_cfg_events` (
					`eventname` varchar(25) NOT NULL,
					`description` varchar(255) NOT NULL,
					`scope` varchar(10) NOT NULL,
					PRIMARY KEY (`eventname`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_cfg_parameters` (
					`actiontype` varchar(25) NOT NULL,
					`parametername` varchar(50) NOT NULL,
					`parameterorder` int(11) unsigned NOT NULL,
					`ismultivalued` tinyint(4) NOT NULL DEFAULT '0',
					`ismandatory` tinyint(4) NOT NULL DEFAULT '0',
					`refreshonchanges` tinyint(4) NOT NULL DEFAULT '0',
					`showexpanded` tinyint(4) NOT NULL DEFAULT '0',
					`defaultoptionstype` varchar(25) DEFAULT NULL,
					`defaultoptionsformula` text,
					`translationmodule` varchar(50) DEFAULT NULL,
					PRIMARY KEY (`actiontype`,`parametername`),
					UNIQUE KEY `actiontype_parameterorder` (`actiontype`,`parameterorder`),
					CONSTRAINT `FK_vtiger_bgtasks_cfg_parameters_vtiger_bgtasks_cfg_actions` FOREIGN KEY (`actiontype`) REFERENCES `vtiger_bgtasks_cfg_actions` (`actiontype`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_cfg_parameteroptions` (
					`actiontype` varchar(25) NOT NULL,
					`parametername` varchar(50) NOT NULL,
					`parametertype` varchar(25) NOT NULL,
					PRIMARY KEY (`actiontype`,`parametername`,`parametertype`),
					CONSTRAINT `FK_bgtasks_cfg_parameteroptions_cfg_parameters` FOREIGN KEY (`actiontype`, `parametername`) REFERENCES `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_data` (
					`taskid` int(11) NOT NULL AUTO_INCREMENT,
					`taskname` varchar(50) NOT NULL,
					`description` text,
					`category` varchar(255) DEFAULT NULL,
					`scope` varchar(10) NOT NULL DEFAULT 'USER',
					`modulename` varchar(25) DEFAULT NULL,
					`trigger` varchar(15) NOT NULL,
					`event` varchar(25) DEFAULT NULL,
					`eventinstant` varchar(15) DEFAULT NULL,
					`taskstatus` varchar(15) NOT NULL,
					`frequency` bigint(20) DEFAULT NULL,
					`lastexecutedon` datetime DEFAULT NULL,
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`taskid`),
					UNIQUE KEY `taskname` (`taskname`),
					KEY `trigger` (`trigger`),
					KEY `taskstatus` (`taskstatus`),
					KEY `trigger_event_eventinstant_taskstatus` (`taskstatus`,`trigger`,`event`,`eventinstant`),
					KEY `trigger_frequency_lastexecutedon` (`taskstatus`,`trigger`,`lastexecutedon`),
					KEY `FK_vtiger_bgtasks_data_vtiger_bgtasks_cfg_events` (`event`),
					KEY `FK_vtiger_bgtasks_data_vtiger_tab` (`modulename`),
					KEY `FK_vtiger_bgtasks_data_vtiger_bgtasks_cfg_categories` (`category`),
					CONSTRAINT `FK_vtiger_bgtasks_data_vtiger_bgtasks_cfg_categories` FOREIGN KEY (`category`) REFERENCES `vtiger_bgtasks_cfg_categories` (`categoryname`) ON DELETE CASCADE ON UPDATE CASCADE,
					CONSTRAINT `FK_vtiger_bgtasks_data_vtiger_bgtasks_cfg_events` FOREIGN KEY (`event`) REFERENCES `vtiger_bgtasks_cfg_events` (`eventname`) ON DELETE CASCADE ON UPDATE CASCADE,
					CONSTRAINT `FK_vtiger_bgtasks_data_vtiger_tab` FOREIGN KEY (`modulename`) REFERENCES `vtiger_tab` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_data_actions` (
					`taskid` int(11) NOT NULL,
					`actionname` varchar(50) NOT NULL,
					`actiontype` varchar(25) NOT NULL,
					`actionorder` int(11) unsigned NOT NULL,
					PRIMARY KEY (`taskid`,`actionname`),
					KEY `FK_vtiger_bgtasks_data_actions_vtiger_bgtasks_cfg_actions` (`actiontype`),
					CONSTRAINT `FK_vtiger_bgtasks_data_actions_vtiger_bgtasks_cfg_actions` FOREIGN KEY (`actiontype`) REFERENCES `vtiger_bgtasks_cfg_actions` (`actiontype`) ON DELETE CASCADE ON UPDATE CASCADE,
					CONSTRAINT `FK_vtiger_bgtasks_data_actions_vtiger_bgtasks_data` FOREIGN KEY (`taskid`) REFERENCES `vtiger_bgtasks_data` (`taskid`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_data_filtergroups` (
					`taskid` int(11) NOT NULL,
					`groupid` int(11) NOT NULL,
					`operator` varchar(15) DEFAULT NULL,
					PRIMARY KEY (`taskid`,`groupid`),
					CONSTRAINT `FK_vtiger_bgtasks_filtergroups_vtiger_bgtasks_data` FOREIGN KEY (`taskid`) REFERENCES `vtiger_bgtasks_data` (`taskid`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_data_filters` (
					`taskid` int(11) NOT NULL,
					`groupid` int(11) NOT NULL,
					`sequence` int(11) NOT NULL,
					`modulename` varchar(25) NOT NULL,
					`fieldname` varchar(50) NOT NULL,
					`label` varchar(255) NOT NULL,
					`comparator` varchar(25) NOT NULL,
					`value` varchar(255) DEFAULT NULL,
					`operator` varchar(3) DEFAULT NULL,
					PRIMARY KEY (`taskid`,`groupid`,`sequence`),
					KEY `modulename_fieldname` (`modulename`,`fieldname`),
					CONSTRAINT `FK_vtiger_bgtasks_data_filters_vtiger_bgtasks_data_filtergroups` FOREIGN KEY (`taskid`, `groupid`) REFERENCES `vtiger_bgtasks_data_filtergroups` (`taskid`, `groupid`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_bgtasks_data_parameters` (
					`taskid` int(11) NOT NULL,
					`actionname` varchar(50) NOT NULL,
					`parametername` varchar(50) NOT NULL,
					`expandedkey` varchar(255) NOT NULL,
					`actiontype` varchar(25) NOT NULL,
					`parametertype` varchar(255) DEFAULT NULL,
					`parameterformula` text,
					PRIMARY KEY (`taskid`,`actionname`,`parametername`,`expandedkey`),
					KEY `FK_vtiger_bgtasks_data_parameters_vtiger_bgtasks_cfg_parameters` (`actiontype`,`parametername`),
					CONSTRAINT `FK_vtiger_bgtasks_data_parameters_vtiger_bgtasks_cfg_parameters` FOREIGN KEY (`actiontype`, `parametername`) REFERENCES `vtiger_bgtasks_cfg_parameters` (`actiontype`, `parametername`) ON DELETE CASCADE ON UPDATE CASCADE,
					CONSTRAINT `FK_vtiger_bgtasks_data_parameters_vtiger_bgtasks_data_actions` FOREIGN KEY (`taskid`, `actionname`) REFERENCES `vtiger_bgtasks_data_actions` (`taskid`, `actionname`) ON DELETE CASCADE ON UPDATE CASCADE
				) ENGINE=InnoDB"
			);

			self::$adb->query ("INSERT INTO `vtiger_parenttab` (`parenttabid`, `parenttab_label`, `sequence`, `visible`, `padre`, `color`, `iconclass`, `avaliable`) VALUES (1, 'Entradas', 0, 0, 0, NULL, 'fa-edit', 1)");
			self::$adb->query ("INSERT INTO `vtiger_parenttab` (`parenttabid`, `parenttab_label`, `sequence`, `visible`, `padre`, `color`, `iconclass`, `avaliable`) VALUES (2, 'Planificación', 1, 0, 0, NULL, 'fa-list-ul', 1)");
			self::$adb->query ("INSERT INTO `vtiger_parenttab` (`parenttabid`, `parenttab_label`, `sequence`, `visible`, `padre`, `color`, `iconclass`, `avaliable`) VALUES (3, 'Ejecución', 2, 0, 0, NULL, 'fa-play', 1)");
			self::$adb->query ("INSERT INTO `vtiger_parenttab` (`parenttabid`, `parenttab_label`, `sequence`, `visible`, `padre`, `color`, `iconclass`, `avaliable`) VALUES (4, 'Revisión', 3, 0, 0, NULL, 'fa-check', 1)");
			self::$adb->query ("INSERT INTO `vtiger_modentity_num_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_blocks_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_customview_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_picklist_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_picklistvalues_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_def_org_share_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_ws_entity_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (1, 'Administrator', 'Admin Profile')");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (2, 'CRM', 'El CRM blah blah blah')");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (0, 'Save', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (1, 'EditView', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (2, 'Delete', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (3, 'index', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (4, 'DetailView', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (5, 'Import', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (6, 'Export', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (8, 'Merge', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (10, 'DuplicatesHandling', 0)");
			self::$adb->query ("INSERT INTO `vtiger_role` (`roleid`, `rolename`, `parentrole`, `depth`, `iscustomer`, `ispartner`, `default_module`) VALUES ('H1', 'Organización', 'H1', 0, NULL, NULL, NULL)");
			self::$adb->query ("INSERT INTO `vtiger_role` (`roleid`, `rolename`, `parentrole`, `depth`, `iscustomer`, `ispartner`, `default_module`) VALUES ('H2', 'Director General', 'H1::H2', 1, 0, NULL, 'Home')");
			self::$adb->query ("INSERT INTO `vtiger_org_share_action_mapping` (`share_action_id`, `share_action_name`) VALUES (0, 'Public: Read Only')");
			self::$adb->query ("INSERT INTO `vtiger_org_share_action_mapping` (`share_action_id`, `share_action_name`) VALUES (1, 'Public: Read, Create/Edit')");
			self::$adb->query ("INSERT INTO `vtiger_org_share_action_mapping` (`share_action_id`, `share_action_name`) VALUES (2, 'Public: Read, Create/Edit, Delete')");
			self::$adb->query ("INSERT INTO `vtiger_org_share_action_mapping` (`share_action_id`, `share_action_name`) VALUES (3, 'Private')");
			self::$adb->query ("INSERT INTO `vtiger_org_share_action_mapping` (`share_action_id`, `share_action_name`) VALUES (4, 'Hide Details')");
			self::$adb->query ("INSERT INTO `vtiger_org_share_action_mapping` (`share_action_id`, `share_action_name`) VALUES (5, 'Hide Details and Add Events')");
			self::$adb->query ("INSERT INTO `vtiger_org_share_action_mapping` (`share_action_id`, `share_action_name`) VALUES (6, 'Show Details')");
			self::$adb->query ("INSERT INTO `vtiger_org_share_action_mapping` (`share_action_id`, `share_action_name`) VALUES (7, 'Show Details and Add Events')");
			self::$adb->query ("INSERT INTO `vtiger_category_apps` (`catappid`, `code`, `name`, `status`, `description`) VALUES (1, 'personalizada', 'Personalizadas', 'Activa', 'Aplicaciones Personalizadas')");
			self::$adb->query ("INSERT INTO `vtiger_category_apps` (`catappid`, `code`, `name`, `status`, `description`) VALUES (10, 'estrategia_marketing', 'Estrategia / Marketing / Comercial', 'Activa', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_selectquery_seq` (id) VALUES (0)");
		}

		/**
		 * Cerrar la prueba:
		 * 1. Eliminar la base de datos de prueba
		 * 2. Desconectar de la base de datos
		 */
		public static function tearDownAfterClass () {
			parent::tearDownAfterClass ();

			$mm = ModuleManager::getInstance (self::$adb);
			$module = $mm->fetchModule ('my_contacts');
			if (!empty ($module)) {
				$mm->deleteModule ($module, true);
			}

			$module = $mm->fetchModule ('my_customers');
			if (!empty ($module)) {
				$mm->deleteModule ($module, true);
			}

			$module = $mm->fetchModule ('my_potentials');
			if (!empty ($module)) {
				$mm->deleteModule ($module, true);
			}

			self::$adb->query ('DROP DATABASE IF EXISTS `platzilla_test`');
			self::$adb->disconnect ();
		}

		/**
		 * @return Module
		 */
		private function createContactsModule () {
			$fields = array (
				Field::getInstance ()->setColumnName ('code')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Code')->setMandatory (true)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('code')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_ONLY)->setUiType (FieldInterface::UI_TYPE_CODE),
				Field::getInstance ()->setColumnName ('full_name')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Full name')->setMandatory (true)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('full_name')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_TEXT),
				Field::getInstance ()->setColumnName ('email')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Email')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('email')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_EMAIL),
				Field::getInstance ()->setColumnName ('phone')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Phone')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('phone')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_PHONE),
				Field::getInstance ()->setColumnName ('customer')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Customer')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('customer')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_MODULE_REFERENCE)->setModuleReferences (array (FieldModuleReference::getInstance ()->setReferencedModuleName ('my_customers'))),
			);
			$module      = Module::getInstance (true, 'CON-', '00001')
				->setBlocks (array (
					Block::getInstance ()
						->setLabel ('General information')
						->setFields ($fields),
					Block::getInstance ()
						->setLabel ('Additional information')
						->setFields (array (
							Field::getInstance ()->setColumnName ('comments')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Comments')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('comments')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_TEXTAREA),
						)),
				))
				->setEntityIdentifier ('code')
				->setLabel ('Contacts')
				->setMenuLabel ('Entradas')
				->setName ('my_contacts')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setType (ModuleInterface::TYPE_USER)
				->setViews (array (
					View::getInstance ()
						->setColumns (array (
							ViewColumn::getInstance ($fields [0])->setSequence (0),
							ViewColumn::getInstance ($fields [1])->setSequence (1),
							ViewColumn::getInstance ($fields [2])->setSequence (2),
							ViewColumn::getInstance ($fields [3])->setSequence (3),
						))
						->setDefault (ViewInterface::DEFAULT_YES)
						->setName ('All')
						->setOwner (1)
						->setShowCountInMenu (ViewInterface::SHOW_COUNT_YES)
						->setStatus (ViewInterface::STATUS_PUBLIC),
				));
			ModuleManager::getInstance (self::$adb)->saveModule ($module, true);
			return $module;
		}

		/**
		 * @return Module
		 */
		private function createCustomersModule () {
			$fields = array (
				Field::getInstance ()->setColumnName ('code')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Code')->setMandatory (true)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('code')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_ONLY)->setUiType (FieldInterface::UI_TYPE_CODE),
				Field::getInstance ()->setColumnName ('company')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Company')->setMandatory (true)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('company')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_TEXT),
				Field::getInstance ()->setColumnName ('email')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Email')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('email')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_EMAIL),
				Field::getInstance ()->setColumnName ('phone')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Phone')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('phone')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_PHONE),
			);
			$module = Module::getInstance (true, 'CUS-', '00001')
				->setBlocks (array (
					Block::getInstance ()
						->setLabel ('General information')
						->setFields ($fields),
					Block::getInstance ()
						->setLabel ('Additional information')
						->setFields (array (
							Field::getInstance ()->setColumnName ('comments')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Comments')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('comments')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_TEXTAREA),
						)),
				))
				->setEntityIdentifier ('code')
				->setLabel ('Customers')
				->setMenuLabel ('Entradas')
				->setName ('my_customers')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setType (ModuleInterface::TYPE_USER)
				->setViews (array (
					View::getInstance ()
						->setColumns (array (
							ViewColumn::getInstance ($fields [0])->setSequence (0),
							ViewColumn::getInstance ($fields [1])->setSequence (1),
							ViewColumn::getInstance ($fields [2])->setSequence (2),
							ViewColumn::getInstance ($fields [3])->setSequence (3),
						))
						->setDefault (ViewInterface::DEFAULT_YES)
						->setName ('All')
						->setOwner (1)
						->setShowCountInMenu (ViewInterface::SHOW_COUNT_YES)
						->setStatus (ViewInterface::STATUS_PUBLIC),
				));
			ModuleManager::getInstance (self::$adb)->saveModule ($module, true);
			return $module;
		}

		/**
		 * @return Module
		 */
		private function createPotentialsModule () {
			$fields = array (
				Field::getInstance ()->setColumnName ('code')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Code')->setMandatory (true)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('code')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_ONLY)->setUiType (FieldInterface::UI_TYPE_CODE),
				Field::getInstance ()->setColumnName ('company')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Company')->setMandatory (true)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('company')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_TEXT),
				Field::getInstance ()->setColumnName ('email')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Email')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('email')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_EMAIL),
				Field::getInstance ()->setColumnName ('phone')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Phone')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('phone')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_PHONE),
				Field::getInstance ()->setColumnName ('contact')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Contact')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('contact')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_MODULE_REFERENCE)->setModuleReferences (array (FieldModuleReference::getInstance ()->setReferencedModuleName ('my_contacts'))),
			);
			$module = Module::getInstance (true, 'POT-', '00001')
				->setBlocks (array (
					Block::getInstance ()
						->setLabel ('General information')
						->setFields ($fields),
					Block::getInstance ()
						->setLabel ('Additional information')
						->setFields (array (
							Field::getInstance ()->setColumnName ('comments')->setDisplayType (FieldInterface::DISPLAY_TYPE_ALL)->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)->setLabel ('Comments')->setMandatory (false)->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)->setName ('comments')->setPresence (FieldInterface::PRESENCE_USER_DEFINED)->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)->setReadOnly (FieldInterface::READ_WRITE)->setUiType (FieldInterface::UI_TYPE_TEXTAREA),
						)),
				))
				->setEntityIdentifier ('code')
				->setLabel ('Potentials')
				->setMenuLabel ('Entradas')
				->setName ('my_potentials')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setType (ModuleInterface::TYPE_USER)
				->setViews (array (
					View::getInstance ()
						->setColumns (array (
							ViewColumn::getInstance ($fields [0])->setSequence (0),
							ViewColumn::getInstance ($fields [1])->setSequence (1),
							ViewColumn::getInstance ($fields [2])->setSequence (2),
							ViewColumn::getInstance ($fields [3])->setSequence (3),
						))
						->setDefault (ViewInterface::DEFAULT_YES)
						->setName ('All')
						->setOwner (1)
						->setShowCountInMenu (ViewInterface::SHOW_COUNT_YES)
						->setStatus (ViewInterface::STATUS_PUBLIC),
				));
			ModuleManager::getInstance (self::$adb)->saveModule ($module);
			return $module;
		}

		/**
		 * Intentar crear una aplicación sin la información mínima necesaria
		 * Debe arrojar una ApplicationException
		 */
		public function testCreateIncompleteApplication () {
			$object = Application::getInstance ();
			$this->expectException (ApplicationException::class);
			ApplicationManager::getInstance (self::$adb)->saveApplication ($object);
		}

		/**
		 * Crear una aplicación válida
		 */
		public function testCreateApplication () {
			$customers = $this->createCustomersModule ();
			$contacts = $this->createContactsModule ();
			$object = Application::getInstance ()
				->setCategoryId (10)
				->setCode ('my_crm')
				->setDescription ('My super duper cuper CRM')
				->setModules (array ($contacts, $customers))
				->setName ('My CRM')
				->setPrice (14.50)
				->setServiceId (123)
				->setStatus (ApplicationInterface::STATUS_ACTIVE)
				->setUrl ('http://idontcare.info');
			$application = ApplicationManager::getInstance (self::$adb)->saveApplication ($object);

			// Verificar que el objeto tiene las propiedades que faltaban
			$this->assertNotNull ($application, 'Application should not be null');
			$this->assertNotEmpty ($application->getId (), 'Application ID should not be empty');
			$this->assertNotEmpty ($application->getProfile (), 'Application profile should not be empty');

			// Verificar la información de la aplicación en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_code=?', array ('my_crm'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Applications count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (1, $row ['config_applicationsid'], 'IDs do not match');
			$this->assertEquals ('my_crm', $row ['app_code'], 'Codes do not match');
			$this->assertEquals ('My CRM', $row ['app_name'], 'Names do not match');
			$this->assertEquals ('My super duper cuper CRM', $row ['app_descripcion'], 'Descriptions do not match');
			$this->assertEquals (ApplicationInterface::STATUS_ACTIVE, $row ['app_status'], 'Statuses do not match');
			$this->assertEquals (3, $row ['app_profile'], 'Profile IDs do not match');
			$this->assertEquals (14.50, $row ['app_price'], 'Prices do not match');
			$this->assertEquals (10, $row ['app_category'], 'Category IDs do not match');
			$this->assertEquals ('http://idontcare.info', $row ['app_url'], 'URLs do not match');
			$this->assertEquals (null, $row ['settings_blocks_id'], 'Setting block IDs do not match');

			// Verificar la información de los módulos de la aplicación en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_configapps_tab WHERE config_applicationsid=?', array (1));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Application modules count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_configapps_tab WHERE config_applicationsid=? AND tabid=?', array (1, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Application modules count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_configapps_tab WHERE config_applicationsid=? AND tabid=?', array (1, 2));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Application modules count do not match');

			// Verificar la información de los perfiles de la aplicación en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile WHERE profileid=?', array (3));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Profiles count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('My CRM', $row ['profilename'], 'Profile names do not match');
			$this->assertEquals ('My super duper cuper CRM', $row ['description'], 'Profile descriptions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2globalpermissions WHERE profileid=?', array (3));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Global permissions count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE profileid=?', array (3));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Module profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=?', array (3));
			$this->assertEquals (10, self::$adb->num_rows ($result), 'Standard permission profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=?', array (3));
			$this->assertEquals (8, self::$adb->num_rows ($result), 'Utility profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2field WHERE profileid=?', array (3));
			$this->assertEquals (17, self::$adb->num_rows ($result), 'Field profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE profileid=?', array (3));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'View profiles count do not match');
		}

		/**
		 * Actualizar una aplicación existente
		 * @depends testCreateApplication
		 */
		public function testUpdateApplication () {
			$potentials = $this->createPotentialsModule ();
			$contacts = ModuleManager::getInstance (self::$adb)->fetchModule ('my_contacts');
			$object = Application::getInstance ()
				->setId (1)
				->setCategoryId (10)
				->setCode ('my_crm')
				->setDescription ('My super duper cuper CRM')
				->setModules (array ($contacts, $potentials))
				->setName ('My CRM')
				->setPrice (9.99)
				->setProfile (Profile::getInstance ()->setId (3)->setName ('My CRM')->setDescription ('My super duper cuper CRM')->setMainApplicationCode ('my_crm'))
				->setServiceId (123)
				->setStatus (ApplicationInterface::STATUS_ACTIVE)
				->setUrl ('http://idontcare.info');

			$application = ApplicationManager::getInstance (self::$adb)->saveApplication ($object);
			$this->assertNotNull ($application, 'Application should not be null');

			// Verificar la información de la aplicación en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_code=?', array ('my_crm'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Applications count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('my_crm', $row ['app_code'], 'Codes do not match');
			$this->assertEquals ('My CRM', $row ['app_name'], 'Names do not match');
			$this->assertEquals ('My super duper cuper CRM', $row ['app_descripcion'], 'Descriptions do not match');
			$this->assertEquals (ApplicationInterface::STATUS_ACTIVE, $row ['app_status'], 'Statuses do not match');
			$this->assertEquals (3, $row ['app_profile'], 'Profile IDs do not match');
			$this->assertEquals (9.99, $row ['app_price'], 'Prices do not match');
			$this->assertEquals (10, $row ['app_category'], 'Category IDs do not match');
			$this->assertEquals ('http://idontcare.info', $row ['app_url'], 'URLs do not match');
			$this->assertEquals (null, $row ['settings_blocks_id'], 'Setting block IDs do not match');

			// Verificar la información de los módulos de la aplicación en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_configapps_tab WHERE config_applicationsid=?', array (1));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Application modules count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_configapps_tab WHERE config_applicationsid=? AND tabid=?', array (1, 1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Application modules count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_configapps_tab WHERE config_applicationsid=? AND tabid=?', array (1, 2));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Application modules count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_configapps_tab WHERE config_applicationsid=? AND tabid=?', array (1, 3));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Application modules count do not match');

			// Verificar la información de los perfiles de la aplicación en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile WHERE profileid=?', array (3));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Profiles count do not match');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('My CRM', $row ['profilename'], 'Profile names do not match');
			$this->assertEquals ('My super duper cuper CRM', $row ['description'], 'Profile descriptions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2globalpermissions WHERE profileid=?', array (3));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Global permissions count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE profileid=?', array (3));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Module profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=?', array (3));
			$this->assertEquals (10, self::$adb->num_rows ($result), 'Standard permission profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=?', array (3));
			$this->assertEquals (8, self::$adb->num_rows ($result), 'Utility profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2field WHERE profileid=?', array (3));
			$this->assertEquals (18, self::$adb->num_rows ($result), 'Field profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE profileid=?', array (3));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'View profiles count do not match');
		}

		/**
		 * Elimina la aplicación existente
		 * @depends testUpdateApplication
		 */
		public function testDeleteApplication () {
			$applicationId = 1;
			$applicationCode = 'my_crm';
			$profileId = 3;
			$application = Application::getInstance ()->setCode ($applicationCode);
			ApplicationManager::getInstance (self::$adb)->deleteApplication ($application);

			// Verificar que se eliminó la aplicación en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_config_applications WHERE app_code=?', array ($applicationCode));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Applications count do not match');

			// Verificar que se eliminaron los módulos de la aplicación
			$result = self::$adb->pquery ('SELECT * FROM vtiger_configapps_tab WHERE config_applicationsid=?', array ($applicationId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Application modules count do not match');

			// Verificar la información de los perfiles de la aplicación en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile WHERE profileid=?', array ($profileId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2globalpermissions WHERE profileid=?', array ($profileId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Global permissions count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE profileid=?', array ($profileId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Module profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=?', array ($profileId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Standard permission profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=?', array ($profileId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Utility profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2field WHERE profileid=?', array ($profileId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Field profiles count do not match');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE profileid=?', array ($profileId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View profiles count do not match');
		}

	}
	// @codingStandardsIgnoreEnd

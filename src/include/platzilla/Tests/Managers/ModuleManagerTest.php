<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/ModuleManager.php');

	/**
	 * Prueba funcional de la clase ModuleManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ModuleManagerTest extends PHPUnit_Framework_TestCase {
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
		 * @param integer $moduleId
		 * @param integer $blockId
		 * @param string $blockLabel
		 * @param integer $blockSequence
		 */
		private function checkBlock ($moduleId, $blockId = null, $blockLabel = null, $blockSequence = null) {
			if (!isset ($blockId)) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_blocks WHERE tabid=?', array ($moduleId));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Block should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_blocks WHERE blockid=?', array ($blockId));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'Block should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($moduleId, $row ['tabid'], 'Block IDs do not match');
				$this->assertEquals ($blockId, $row ['blockid'], 'Block IDs do not match');
				$this->assertEquals ($blockLabel, $row ['blocklabel'], 'Block labels do not match');
				$this->assertEquals ($blockSequence, $row ['sequence'], 'Block sequences do not match');
				$this->assertEquals (BlockInterface::SHOW_TITLE_YES, $row ['show_title'], 'Block show_title properties do not match');
				$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $row ['visible'], 'Block visible properties do not match');
				$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $row ['create_view'], 'Block create_view properties do not match');
				$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $row ['edit_view'], 'Block edit_view properties do not match');
				$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $row ['detail_view'], 'Block edit_view properties do not match');
				$this->assertEquals (BlockInterface::IS_CUSTOM_NO, $row ['iscustom'], 'Block iscustom properties do not match');
			}
		}

		/**
		 * @param string $moduleName
		 * @param integer $buttonId
		 * @param string $buttonLabel
		 * @param string $buttonDescription
		 *
		 */
		private function checkButton ($moduleName, $buttonId = null, $buttonLabel = null, $buttonDescription = null) {
			if (!isset ($buttonId)) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_custombuttons WHERE module=?', array ($moduleName));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Button should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_custombuttons WHERE custombuttonid=?', array ($buttonId));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'Button should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($moduleName, $row ['module'], 'Module names do not match');
				$this->assertEquals ($buttonLabel, $row ['label'], 'Labels do not match');
				$this->assertEquals ($buttonDescription, $row ['description'], 'Descriptions do not match');
				$this->assertEquals (null, $row ['link'], 'Links do not match');
				$this->assertEquals ('doSomething();', $row ['onclick'], 'OnClick properties do not match');
				$this->assertEquals ('success', $row ['style'], 'Styles do not match');
				$this->assertEquals (ButtonInterface::TYPE_JAVASCRIPT, $row ['type'], 'Types do not match');
				$this->assertEquals (ButtonInterface::LOCATION_LIST_VIEW, $row ['action'], 'Locations do not match');
				$this->assertEquals (1, $row ['active'], 'IsActive properties do not match');
				$this->assertEquals (0, $row ['runinnewwindow'], 'RunInNewWndow properties do not match');
			}
		}

		/**
		 * @param string $moduleName
		 * @param integer $chartId
		 * @param string $chartTitle
		 * @param string $chartFieldName
		 * @param string $chartGroupBy
		 */
		private function checkChart ($moduleName, $chartId = null, $chartTitle = null, $chartFieldName = null, $chartGroupBy = null) {
			if (!isset ($chartId)) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=?', array ($moduleName));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Chart should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE graficoid=?', array ($chartId));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'Chart should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($moduleName, $row ['fld_module'], 'Module names do not match');
				$this->assertEquals ($chartTitle, $row ['title'], 'Titles do not match');
				$this->assertEquals ($chartGroupBy, $row ['fieldgrouping'], 'Group by properties do not match');
				$this->assertEquals ($chartFieldName, $row ['fieldoperation'], 'Field names do not match');
				$this->assertEquals (ChartInterface::OPERATION_AVERAGE, $row ['operation'], 'Operations do not match');
				$this->assertEquals (ChartInterface::TYPE_BARS, $row ['tipografico'], 'Types do not match');
				$this->assertEquals (ChartInterface::DATE_GROUPING_ANNUAL, $row ['dategrouping'], 'Date grouping properties do not match');
				$this->assertEquals (null, $row ['roles_grafico'], 'Roles do not match');
				$this->assertEquals ('SELECT 1', $row ['sqlprimarioreporte'], 'SQL queries do not match');
				$this->assertEquals (json_encode (array ('a' => 1, 'b' => 2)), $row ['varreporte'], 'Variables do not match');
				$this->assertEquals (json_encode (array ('application_one', 'application_two', 'application_three')), $row ['applicationcodes'], 'Application codes do not match');
				$this->assertEquals (0, $row ['reporteavanzado'], 'Advanced properties do not match');
				$this->assertEquals (0, $row ['comparar'], 'Compare properties do not match');
			}
		}

		/**
		 * @param integer $moduleId
		 * @param string $moduleName
		 * @param string $entityIdentifier
		 */
		private function checkEntityName ($moduleName, $moduleId = null, $entityIdentifier = null) {
			if (!isset ($moduleId)) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_entityname WHERE modulename=?', array ($moduleName));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Entity identifier should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_entityname WHERE tabid=?', array ($moduleId));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'Entity identifier should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($moduleId, $row ['tabid'], 'IDs do not match');
				$this->assertEquals ($moduleName, $row ['modulename'], 'Names do not match');
				$this->assertEquals ("vtiger_{$moduleName}", $row ['tablename'], 'Table names do not match');
				$this->assertEquals ($entityIdentifier, $row ['fieldname'], 'Field names do not match');
				$this->assertEquals ("{$moduleName}id", $row ['entityidfield'], 'Entity ID field names do not match');
				$this->assertEquals ("{$moduleName}id", $row ['entityidcolumn'], 'Entity ID column names do not match');
			}
		}

		/**
		 * @param string $moduleName
		 * @param integer $id
		 * @param string $prefix
		 * @param string $sequence
		 */
		private function checkEntitySequence ($moduleName, $id = null, $prefix = null, $sequence = null) {
			$result = self::$adb->pquery ('SELECT * FROM vtiger_modentity_num WHERE semodule=?', array ($moduleName));
			if (!isset ($id)) {
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Entity sequence should be null');
			} else {
				$this->assertEquals (1, self::$adb->num_rows ($result), 'Entity sequence should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($id, $row ['num_id'], 'IDs do not match');
				$this->assertEquals ($moduleName, $row ['semodule'], 'Names do not match');
				$this->assertEquals ($prefix, $row ['prefix'], 'Prefixes do not match');
				$this->assertEquals ($sequence, $row ['start_id'], 'Sequences do not match');
				$this->assertEquals ($sequence, $row ['cur_id'], 'Sequences do not match');
				$this->assertEquals (1, $row ['active'], 'Sequences do not match');
			}
		}

		/**
		 * @param integer $moduleId
		 * @param integer $blockId
		 * @param integer $fieldId
		 * @param string $fieldName
		 * @param string $fieldLabel
		 * @param integer $fieldUiType
		 * @param integer $fieldSequence
		 * @param string $fieldTableName
		 * @param string $columnName
		 * @param integer $generatedType
		 * @param integer $presence
		 * @param integer $displayType
		 * @param integer $quickCreate
		 * @param integer $massEditable
		 */
		private function checkField ($moduleId, $blockId = null, $fieldId = null, $fieldName = null, $fieldLabel = null, $fieldUiType = null, $fieldSequence = null, $fieldTableName = null, $columnName = null, $generatedType = null, $presence = null, $displayType = null, $quickCreate = null, $massEditable = null) {
			if (!isset ($blockId)) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_field WHERE tabid=?', array ($moduleId));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Field should be null');
			} else {
				$columnName    = isset ($columnName) ? $columnName : $fieldName;
				$generatedType = isset ($generatedType) ? $generatedType : FieldInterface::GENERATED_TYPE_CUSTOM;
				$presence      = isset ($presence) ? $presence : FieldInterface::PRESENCE_VISIBLE;
				$displayType   = isset ($displayType) ? $displayType : FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY;
				$quickCreate   = isset ($quickCreate) ? $quickCreate : FieldInterface::QUICK_CREATE_DISABLED;
				$massEditable  = isset ($massEditable) ? $massEditable : FieldInterface::MASS_EDITABLE_DISABLED;

				$result = self::$adb->pquery ('SELECT * FROM vtiger_field WHERE fieldid=?', array ($fieldId));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'Field should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($moduleId, $row ['tabid'], 'Field block IDs do not match');
				$this->assertEquals ($blockId, $row ['block'], 'Field block IDs do not match');
				$this->assertEquals ($fieldId, $row ['fieldid'], 'Field IDs do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Field column names do not match');
				$this->assertEquals ($fieldName, $row ['fieldname'], 'Field names do not match');
				$this->assertEquals ($fieldLabel, $row ['fieldlabel'], 'Field labels do not match');
				$this->assertEquals ($fieldUiType, $row ['uitype'], 'Field UI types do not match');
				$this->assertEquals ($fieldSequence, $row ['sequence'], 'Field sequences do not match');
				$this->assertEquals ($fieldTableName, $row ['tablename'], 'Field table names do not match');
				$this->assertEquals ($generatedType, $row ['generatedtype'], 'Field generated types do not match');
				$this->assertEquals ($presence, $row ['presence'], 'Field presences do not match');
				$this->assertEquals ($displayType, $row ['displaytype'], 'Field display types do not match');
				$this->assertEquals ($quickCreate, $row ['quickcreate'], 'Field quick create properties do not match');
				$this->assertEquals ($massEditable, $row ['masseditable'], 'Field mass editable properties do not match');
				$this->assertEquals (FieldInterface::READ_WRITE, $row ['readonly'], 'Field read only properties do not match');
			}
		}

		/**
		 * @param integer $moduleId
		 * @param integer $fieldId
		 * @param integer $expectedTotal
		 */
		private function checkFieldProfiles ($moduleId, $fieldId = null, $expectedTotal = null) {
			if (!isset ($fieldId)) {
				$result        = self::$adb->pquery ('SELECT * FROM vtiger_profile2field WHERE tabid=?', array ($moduleId));
				$expectedTotal = 0;
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2field WHERE tabid=? AND fieldid=?', array ($moduleId, $fieldId));
			}
			$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), 'Field profiles count do not match');
		}

		/**
		 * @param integer $id
		 * @param string $name
		 * @param string $label
		 * @param integer $type
		 * @param integer $sequence
		 * @param integer $expectedTablesCreated
		 */
		private function checkModule ($name, $id = null, $label = null, $type = null, $sequence = null, $expectedTablesCreated = null) {
			if (!isset ($id)) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_tab WHERE name=?', array ($name));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Module should be null');
			} else {
				// Verificar que se creó el módulo en la base de datos
				$result = self::$adb->pquery ('SELECT * FROM vtiger_tab WHERE tabid=?', array ($id));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'Module should not me null');

				// Verificar que se crearon los datos del módulo en la base de datos
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($id, $row ['tabid'], 'IDs do not match');
				$this->assertEquals ($name, $row ['name'], 'Names do not match');
				$this->assertEquals ($sequence, $row ['tabsequence'], 'Sequences do not match');
				$this->assertEquals ($label, $row ['tablabel'], 'Labels do not match');
				$this->assertEquals ($type, $row ['customized'], 'Types do not match');

				$this->assertEquals (ModuleInterface::PRESENCE_VISIBLE, $row ['presence'], 'Presences do not match');
				$this->assertEquals ($type == ModuleInterface::TYPE_TOOL ? 0 : 1, $row ['isentitytype'], 'Entity types do not match');
				$this->assertEquals (1, $row ['avaliable'], 'Available properties do not match');
				$this->assertEquals ($type == ModuleInterface::TYPE_TOOL ? 1 : 0, $row ['isplatzilla'], 'Is Platzilla properties do not match');
				$this->assertEquals (0, $row ['isvisibleinadmin'], 'Admin console visibilities do not match');

				// Verificar que se crearon las tablas y las columnas
				$result = self::$adb->pquery ('SHOW TABLES LIKE ?', array ("vtiger_{$name}"));
				$this->assertEquals ($expectedTablesCreated, self::$adb->num_rows ($result), 'Table not found');
				$result = self::$adb->pquery ('SHOW TABLES LIKE ?', array ("vtiger_{$name}cf"));
				$this->assertEquals ($expectedTablesCreated, self::$adb->num_rows ($result), 'Custom fields table not found');
				if (!empty ($expectedTablesCreated)) {
					$result = self::$adb->pquery ("SHOW COLUMNS FROM `vtiger_{$name}` WHERE Field=?", array ("{$name}id"));
					$this->assertEquals ($expectedTablesCreated, self::$adb->num_rows ($result), 'Id column in table not found');
					$result = self::$adb->pquery ("SHOW COLUMNS FROM `vtiger_{$name}` WHERE Field=?", array ("{$name}id"));
					$this->assertEquals ($expectedTablesCreated, self::$adb->num_rows ($result), 'Id column in table not found');
					$result = self::$adb->pquery ("SHOW COLUMNS FROM `vtiger_{$name}cf` WHERE Field=?", array ("{$name}id"));
					$this->assertEquals ($expectedTablesCreated, self::$adb->num_rows ($result), 'Id column in custom fields table not found');
				}
			}
		}

		/**
		 * @param string $moduleName
		 * @param integer $moduleId
		 * @param integer $expectedTotal
		 */
		private function checkModuleProfiles ($moduleName, $moduleId = null, $expectedTotal = null) {
			if (!isset ($moduleId)) {
				$result = self::$adb->pquery ('SELECT p2t.* FROM vtiger_profile2tab p2t INNER JOIN vtiger_tab t ON t.tabid=p2t.tabid AND t.name=?', array ($moduleName));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Profile should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE tabid=?', array ($moduleId));
				$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), 'Profiles count do not match');

				if (!empty ($expectedTotal)) {
					while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
						$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Profile permissions do not match');
					}
				}
			}
		}

		/**
		 * @param string $moduleName
		 * @param integer $moduleId
		 * @param integer $expectedTotal
		 */
		private function checkModuleStandardPermissions ($moduleName, $moduleId = null, $expectedTotal = null) {
			if (!isset ($moduleId)) {
				$result = self::$adb->pquery ('SELECT p2sp.* FROM vtiger_profile2standardpermissions p2sp INNER JOIN vtiger_tab t ON t.tabid=p2sp.tabid AND t.name=?', array ($moduleName));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Profiles should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE tabid=?', array ($moduleId));
				$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), 'Profiles count do not match');

				if (!empty ($expectedTotal)) {
					while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
						$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Module profile permissions do not match');
					}
				}
			}
		}

		/**
		 * @param string $moduleName
		 * @param integer $moduleId
		 * @param integer $expectedTotal
		 */
		private function checkModuleUtilityPermissions ($moduleName, $moduleId = null, $expectedTotal = null) {
			if (!isset ($moduleId)) {
				$result = self::$adb->pquery ('SELECT p2u.* FROM vtiger_profile2utility p2u INNER JOIN vtiger_tab t ON t.tabid=p2u.tabid AND t.name=?', array ($moduleName));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Profiles should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE tabid=?', array ($moduleId));
				$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), 'Profiles count do not match');

				if (!empty ($expectedTotal)) {
					while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
						$permission = $row ['activityid'] == 8 ? ModuleProfileInterface::PERMISSION_DENY : ModuleProfileInterface::PERMISSION_ALLOW;
						$this->assertEquals ($permission, $row ['permission'], 'Module profile permissions do not match');
					}
				}
			}
		}

		/**
		 * @param integer $moduleId
		 * @param integer $expectedTotal
		 */
		private function checkSharingConfiguration ($moduleId, $expectedTotal) {
			$result = self::$adb->pquery ('SELECT * FROM vtiger_def_org_share WHERE tabid=?', array ($moduleId));
			$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), 'Sharing configuration should not be null');
		}

		/**
		 * @param string $moduleName
		 * @param integer $viewId
		 * @param string $viewLabel
		 */
		private function checkView ($moduleName, $viewId = null, $viewLabel = null) {
			if (!isset ($viewId)) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_customview WHERE entitytype=?', array ($moduleName));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'View should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_customview WHERE cvid=?', array ($viewId));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'View should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($viewId, $row ['cvid'], 'View IDs do not match');
				$this->assertEquals ($viewLabel, $row ['viewname'], 'View names do not match');
				$this->assertEquals ($moduleName, $row ['entitytype'], 'View module names do not match');
				$this->assertEquals (ViewInterface::DEFAULT_YES, $row ['setdefault'], 'View default properties do not match');
				$this->assertEquals (ViewInterface::SHOW_COUNT_NO, $row ['setmetrics'], 'View showCountInMenu properties do not match');
				$this->assertEquals (ViewInterface::STATUS_PUBLIC, $row ['status'], 'View statuses do not match');
				$this->assertEquals (1, $row ['userid'], 'View owners do not match');
			}
		}

		/**
		 * @param integer $viewId
		 * @param integer $groupId
		 * @param string $groupCondition
		 * @param string $groupExpression
		 */
		private function checkViewAdvancedFilterGroup ($viewId, $groupId = null, $groupCondition = null, $groupExpression = null) {
			if (!isset ($groupId)) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid=?', array ($viewId));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'View advanced filter group should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid=? AND groupid=?', array ($viewId, $groupId));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'View advanced filter group should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($groupCondition, $row ['group_condition'], 'Group operators do not match');
				$this->assertEquals ($groupExpression, $row ['condition_expression'], 'Group condition expresions do not match');
			}
		}

		/**
		 * @param integer $viewId
		 * @param integer $groupId
		 * @param integer $columnIndex
		 * @param string $columnName
		 * @param string $comparator
		 * @param string $value
		 * @param string $condition
		 */
		private function checkViewAdvancedFilter ($viewId, $groupId = null, $columnIndex = null, $columnName = null, $comparator = null, $value = null, $condition = null) {
			if (!isset ($groupId)) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=?', array ($viewId));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'View advanced filter should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=? AND columnindex=?', array ($viewId, $columnIndex));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'View advanced filter should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($groupId, $row ['groupid'], 'Values do not match');
				$this->assertEquals ($columnIndex, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$this->assertEquals ($comparator, $row ['comparator'], 'Comparators do not match');
				$this->assertEquals ($value, $row ['value'], 'Values do not match');
				$this->assertEquals ($condition, $row ['column_condition'], 'Conditions do not match');
			}
		}

		/**
		 * @param integer $viewId
		 * @param integer $columnIndex
		 * @param string $columnName
		 */
		private function checkViewColumn ($viewId, $columnIndex = null, $columnName = null) {
			if (!isset ($columnIndex)) {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_cvcolumnlist WHERE cvid=?', array ($viewId));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'View column should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_cvcolumnlist WHERE cvid=? AND columnindex=?', array ($viewId, $columnIndex));
				$this->assertEquals (1, self::$adb->num_rows ($result), 'View column should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($viewId, $row ['cvid'], 'Column view ID should not be empty');
				$this->assertEquals ($columnIndex, $row ['columnindex'], 'Column indexes do not match');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			}
		}

		/**
		 * @param string $moduleName
		 * @param integer $viewId
		 * @param integer $expectedTotal
		 */
		private function checkViewProfiles ($moduleName, $viewId = null, $expectedTotal = null) {
			if (!isset ($viewId)) {
				$result = self::$adb->pquery ('SELECT p2cv.* FROM vtiger_profile2customview p2cv INNER JOIN vtiger_tab t ON t.tabid=p2cv.tabid AND t.name=?', array ($moduleName));
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Profiles should be null');
			} else {
				$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE cvid=?', array ($viewId));
				$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), 'Profiles count do not match');

				if (!empty ($expectedTotal)) {
					while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
						$this->assertEquals (ViewProfileInterface::DEFAULT_NO, $row ['setdefault'], 'Profile defaults do not match');
						$this->assertEquals (ViewProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Profile permissions do not match');
					}
				}
			}
		}

		/**
		 * @param integer $viewId
		 * @param string $columnName
		 * @param string $period
		 * @param string $startDate
		 * @param string $endDate
		 */
		private function checkViewStandardFilter ($viewId, $columnName = null, $period = null, $startDate = null, $endDate = null) {
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvstdfilter WHERE cvid=?', array ($viewId));
			if (!isset ($columnName)) {
				$this->assertEquals (0, self::$adb->num_rows ($result), 'Standard filter should be null');
			} else {
				$this->assertEquals (1, self::$adb->num_rows ($result), 'Standard filter should not be null');
				$row = self::$adb->fetchByAssoc ($result, -1, false);
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$this->assertEquals ($period, $row ['stdfilter'], 'Standard filter periods do not match');
				$this->assertEquals ($startDate, $row ['startdate'], 'Standard filter start dates do not match');
				$this->assertEquals ($endDate, $row ['enddate'], 'Standard filter end dates do not match');
			}
		}

		/**
		 * @param string $moduleName
		 * @param integer $expectedTotal
		 */
		private function checkWebServiceConfiguration ($moduleName, $expectedTotal) {
			$result = self::$adb->pquery ('SELECT * FROM vtiger_ws_entity WHERE name=?', array ($moduleName));
			$this->assertEquals ($expectedTotal, self::$adb->num_rows ($result), 'Web service configurations do not match');
		}

		/**
		 * Intentar crear un módulo sin la información mínima necesaria
		 * Debe arrojar una ModuleException
		 */
		public function testCreateIncompleteModule () {
			$object = Module::getInstance ();
			$this->expectException (ModuleException::class);
			ModuleManager::getInstance (self::$adb)->saveModule ($object);
		}

		/**
		 * Crear un módulo sin campos con la información mínima
		 */
		public function testCreateValidToolModule () {
			$menuLabel   = 'Entradas';
			$moduleId    = 1;
			$moduleName  = 'tool_module';
			$moduleLabel = 'My tool module';

			$module      = Module::getInstance ()
				->setLabel ($moduleLabel)
				->setMenuLabel ($menuLabel)
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setType (ModuleInterface::TYPE_TOOL);
			$savedModule = ModuleManager::getInstance (self::$adb)->saveModule ($module, true);

			// Verificar que el objeto existe y que se asignó el ID
			$this->assertNotNull ($savedModule, 'Module should not be null');
			$this->assertNotEmpty ($savedModule->getId (), 'Module ID should not be empty');

			// Verificar la información del módulo en la base de datos
			$this->checkModule ($moduleName, $moduleId, $moduleLabel, ModuleInterface::TYPE_TOOL, 1, 0);
			$this->checkModuleProfiles ($moduleName, $moduleId, 2);
			$this->checkModuleStandardPermissions ($moduleName, $moduleId, 10);
			$this->checkModuleUtilityPermissions ($moduleName, $moduleId, 8);
			$this->checkEntityName ($moduleName);
			$this->checkEntitySequence ($moduleName);
			$this->checkBlock ($moduleId);
			$this->checkField ($moduleId);
			$this->checkFieldProfiles ($moduleId);
			$this->checkButton ($moduleName);
			$this->checkChart ($moduleName);
			$this->checkView ($moduleName);
			$this->checkViewProfiles ($moduleName);
			$this->checkSharingConfiguration ($moduleId, 0);
			$this->checkWebServiceConfiguration ($moduleName, 0);
		}

		/**
		 * Intentar crear un módulo con el mismo nombre de un módulo existente
		 * @depends testCreateValidToolModule
		 */
		public function testCreateDuplicatedNameModule () {
			$menuLabel   = 'Entradas';
			$moduleName  = 'tool_module';
			$moduleLabel = 'My tool module';

			$module = Module::getInstance ()
				->setLabel ($moduleLabel)
				->setMenuLabel ($menuLabel)
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setType (ModuleInterface::TYPE_TOOL);
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_DUPLICATE_NAME);
			ModuleManager::getInstance (self::$adb)->saveModule ($module);
		}

		/**
		 * Crear un módulo con campos con la información mínima
		 */
		public function testCreateValidEntityTypeModule () {
			$blockId     = 10;
			$blockLabel  = 'My test block';
			$fieldId     = 10;
			$fieldLabel  = 'My text field';
			$fieldName   = 'text_field';
			$menuLabel   = 'Entradas';
			$moduleId    = 2;
			$moduleName  = 'user_module';
			$moduleLabel = 'My user module';
			$viewId      = 10;
			$viewLabel   = 'All';

			$field = Field::getInstance ()
				->setColumnName ($fieldName)
				->setLabel ($fieldLabel)
				->setMandatory (true)
				->setName ($fieldName)
				->setUiType (FieldInterface::UI_TYPE_TEXT)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE);

			$module      = Module::getInstance (true, 'USM-', '0000001')
				->setBlocks (array (
					Block::getInstance ()
						->setLabel ($blockLabel)
						->setFields (array ($field)),
				))
				->setEntityIdentifier ($fieldName)
				->setLabel ($moduleLabel)
				->setMenuLabel ($menuLabel)
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setType (ModuleInterface::TYPE_USER)
				->setViews (array (
					View::getInstance ()
						->setColumns (array (ViewColumn::getInstance ($field)->setSequence (0)))
						->setDefault (ViewInterface::DEFAULT_YES)
						->setName ('All')
						->setOwner (1)
						->setShowCountInMenu (ViewInterface::SHOW_COUNT_NO)
						->setStatus (ViewInterface::STATUS_PUBLIC),
				));
			$savedModule = ModuleManager::getInstance (self::$adb)->saveModule ($module, true);

			// Verificar que el objeto existe y que se asignó el ID
			$this->assertNotNull ($savedModule, 'Module should not be null');
			$this->assertNotEmpty ($savedModule->getId (), 'Module ID should not be empty');

			// Verificar la información del módulo en la base de datos
			$this->checkModule ($moduleName, $moduleId, $moduleLabel, ModuleInterface::TYPE_USER, 2, 1);
			$this->checkModuleProfiles ($moduleName, $moduleId, 2);
			$this->checkModuleStandardPermissions ($moduleName, $moduleId, 10);
			$this->checkModuleUtilityPermissions ($moduleName, $moduleId, 8);
			$this->checkEntityName ($moduleName, $moduleId, $fieldName);
			$this->checkEntitySequence ($moduleName, 10, 'USM-', '0000001');
			$this->checkBlock ($moduleId, $blockId, $blockLabel, 1);
			$this->checkField ($moduleId, $blockId, $fieldId, $fieldName, $fieldLabel, FieldInterface::UI_TYPE_TEXT, 1, "vtiger_{$moduleName}");
			$this->checkField ($moduleId, $blockId, 20, 'assigned_user_id', 'Assigned To', FieldInterface::UI_TYPE_OWNER, 2, 'vtiger_crmentity', 'smownerid', FieldInterface::GENERATED_TYPE_EXISTING, FieldInterface::PRESENCE_USER_DEFINED, FieldInterface::DISPLAY_TYPE_ALL, FieldInterface::QUICK_CREATE_UNKNOWN, FieldInterface::MASS_EDITABLE_ENABLED);
			$this->checkField ($moduleId, $blockId, 30, 'createdtime', 'Created Time', FieldInterface::UI_TYPE_CREATED_TIME, 3, 'vtiger_crmentity', 'createdtime', FieldInterface::GENERATED_TYPE_EXISTING, FieldInterface::PRESENCE_USER_DEFINED, FieldInterface::DISPLAY_TYPE_ALL, FieldInterface::QUICK_CREATE_UNKNOWN, FieldInterface::MASS_EDITABLE_DISABLED);
			$this->checkField ($moduleId, $blockId, 40, 'modifiedtime', 'Modified Time', FieldInterface::UI_TYPE_CREATED_TIME, 4, 'vtiger_crmentity', 'modifiedtime', FieldInterface::GENERATED_TYPE_EXISTING, FieldInterface::PRESENCE_USER_DEFINED, FieldInterface::DISPLAY_TYPE_ALL, FieldInterface::QUICK_CREATE_UNKNOWN, FieldInterface::MASS_EDITABLE_DISABLED);
			$this->checkFieldProfiles ($moduleId, $fieldId, 2);
			$this->checkButton ($moduleName);
			$this->checkChart ($moduleName);
			$this->checkView ($moduleName, $viewId, $viewLabel);
			$this->checkViewProfiles ($moduleName, $viewId, 2);
			$label = str_replace (' ', '_', $fieldLabel);
			$this->checkViewColumn ($viewId, 0, "vtiger_{$moduleName}:{$fieldName}:{$fieldName}:{$moduleName}_{$label}:V");
			$this->checkViewStandardFilter ($viewId);
			$this->checkViewAdvancedFilterGroup ($viewId);
			$this->checkViewAdvancedFilter ($viewId);
			$this->checkSharingConfiguration ($moduleId, 1);
			$this->checkWebServiceConfiguration ($moduleName, 1);
		}

		/**
		 * Convertir un módulo tipo herramienta a un módulo tipo usuario
		 * @depends testCreateValidToolModule
		 */
		public function testUpdateToolModule () {
			$blockId     = 20;
			$blockLabel  = 'My code block';
			$fieldId     = 50;
			$fieldLabel  = 'My code field';
			$fieldName   = 'code_field';
			$menuLabel   = 'Planificación';
			$moduleId    = 1;
			$moduleName  = 'tool_module';
			$moduleLabel = 'My tool module v2.0';
			$viewId      = 20;
			$viewLabel   = 'All v2.0';

			$field = Field::getInstance ()
				->setColumnName ($fieldName)
				->setLabel ($fieldLabel)
				->setMandatory (true)
				->setModuleName ($moduleName)
				->setName ($fieldName)
				->setUiType (FieldInterface::UI_TYPE_CODE)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE);

			$module = Module::getInstance (true, 'TOM-', '0001')
				->setBlocks (array (
					Block::getInstance ()
						->setModuleName ($moduleName)
						->setLabel ($blockLabel)
						->setFields (array ($field)),
				))
				->setButtons (array (
					Button::getInstance ()
						->setAction ('doSomething();')
						->setDescription ('My super duper cuper button')
						->setIsActive (true)
						->setLabel ('My button')
						->setLocation (ButtonInterface::LOCATION_LIST_VIEW)
						->setRunInNewWindow (false)
						->setStyle ('success')
						->setType (ButtonInterface::TYPE_JAVASCRIPT),
				))
				->setCharts (array (
					Chart::getInstance ()
						->setAdvanced (ChartInterface::ADVANCED_NO)
						->setApplicationCodes (array ('application_one', 'application_two', 'application_three'))
						->setCompare (false)
						->setDateGrouping (ChartInterface::DATE_GROUPING_ANNUAL)
						->setFieldName ($fieldName)
						->setGroupBy ($fieldName)
						->setOperation (ChartInterface::OPERATION_AVERAGE)
						->setSqlQuery ('SELECT 1')
						->setTitle ('My super duper cuper chart')
						->setType (ChartInterface::TYPE_BARS)
						->setVariables (json_encode (array ('a' => 1, 'b' => 2))),
				))
				->setEntityIdentifier ($fieldName)
				->setId ($moduleId)
				->setLabel ($moduleLabel)
				->setMenuLabel ($menuLabel)
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setType (ModuleInterface::TYPE_USER)
				->setViews (array (
					View::getInstance ()
						->setColumns (array (ViewColumn::getInstance ($field)->setSequence (0)))
						->setDefault (ViewInterface::DEFAULT_YES)
						->setModuleName ($moduleName)
						->setName ($viewLabel)
						->setOwner (1)
						->setShowCountInMenu (ViewInterface::SHOW_COUNT_NO)
						->setStatus (ViewInterface::STATUS_PUBLIC),
				));
			ModuleManager::getInstance (self::$adb)->saveModule ($module, true);

			// Verificar que el objeto existe y que se asignó el ID
			$this->assertNotNull ($module, 'Module should not be null');
			$this->assertNotEmpty ($module->getId (), 'Module ID should not be empty');

			// Verificar la información del módulo en la base de datos
			$this->checkModule ($moduleName, $moduleId, $moduleLabel, ModuleInterface::TYPE_USER, 1, 1);
			$this->checkModuleProfiles ($moduleName, $moduleId, 2);
			$this->checkModuleStandardPermissions ($moduleName, $moduleId, 10);
			$this->checkEntityName ($moduleName, $moduleId, $fieldName);
			$this->checkEntitySequence ($moduleName, 20, 'TOM-', '0001');
			$this->checkBlock ($moduleId, $blockId, $blockLabel, 1);
			$this->checkField ($moduleId, $blockId, $fieldId, $fieldName, $fieldLabel, FieldInterface::UI_TYPE_CODE, 1, "vtiger_{$moduleName}");
			$this->checkField ($moduleId, $blockId, 60, 'assigned_user_id', 'Assigned To', FieldInterface::UI_TYPE_OWNER, 2, 'vtiger_crmentity', 'smownerid', FieldInterface::GENERATED_TYPE_EXISTING, FieldInterface::PRESENCE_USER_DEFINED, FieldInterface::DISPLAY_TYPE_ALL, FieldInterface::QUICK_CREATE_UNKNOWN, FieldInterface::MASS_EDITABLE_ENABLED);
			$this->checkField ($moduleId, $blockId, 70, 'createdtime', 'Created Time', FieldInterface::UI_TYPE_CREATED_TIME, 3, 'vtiger_crmentity', 'createdtime', FieldInterface::GENERATED_TYPE_EXISTING, FieldInterface::PRESENCE_USER_DEFINED, FieldInterface::DISPLAY_TYPE_ALL, FieldInterface::QUICK_CREATE_UNKNOWN, FieldInterface::MASS_EDITABLE_DISABLED);
			$this->checkField ($moduleId, $blockId, 80, 'modifiedtime', 'Modified Time', FieldInterface::UI_TYPE_CREATED_TIME, 4, 'vtiger_crmentity', 'modifiedtime', FieldInterface::GENERATED_TYPE_EXISTING, FieldInterface::PRESENCE_USER_DEFINED, FieldInterface::DISPLAY_TYPE_ALL, FieldInterface::QUICK_CREATE_UNKNOWN, FieldInterface::MASS_EDITABLE_DISABLED);
			$this->checkFieldProfiles ($moduleId, $fieldId, 2);
			$this->checkButton ($moduleName, 1, 'My button', 'My super duper cuper button');
			$this->checkChart ($moduleName, 1, 'My super duper cuper chart', $fieldName, $fieldName);
			$this->checkView ($moduleName, $viewId, $viewLabel);
			$this->checkViewProfiles ($moduleName, $viewId, 2);
			$label = str_replace (' ', '_', $fieldLabel);
			$this->checkViewColumn ($viewId, 0, "vtiger_{$moduleName}:{$fieldName}:{$fieldName}:{$moduleName}_{$label}:V");
			$this->checkViewStandardFilter ($viewId);
			$this->checkViewAdvancedFilterGroup ($viewId);
			$this->checkViewAdvancedFilter ($viewId);
			$this->checkSharingConfiguration ($moduleId, 1);
			$this->checkWebServiceConfiguration ($moduleName, 1);
		}

		/**
		 * Obtener un módulo
		 * @depends testUpdateToolModule
		 */
		public function testFetchModule () {
			$moduleName = 'tool_module';
			$module     = ModuleManager::getInstance (self::$adb)->fetchModule ($moduleName);

			// Verificar que el objeto existe y que se asignó el ID
			$this->assertNotNull ($module, 'Module should not be null');
			$this->assertNotEmpty ($module->getId (), 'Module ID should not be empty');

			// Verificar que se obtuvo la información del módulo de la base de datos
			$this->checkModule ($moduleName, $module->getId (), $module->getLabel (), $module->getType (), $module->getSequence (), 1);
			$this->checkModuleProfiles ($moduleName, $module->getId (), 2);
			$this->checkModuleStandardPermissions ($moduleName, $module->getId (), 10);
			$this->checkEntityName ($moduleName, $module->getId (), $module->getEntityIdentifier ());
			$this->checkEntitySequence ($moduleName, 20, $module->getEntityPrefix (), $module->getEntityInitialSequence ());

			// Verificar que se obtuvo la información de los bloques
			$blocks = $module->getBlocks ();
			$this->assertNotNull ($blocks, 'Blocks should not be null');
			$this->assertCount (1, $blocks, 'Blocks count do not match');
			$this->checkBlock ($module->getId (), $blocks [0]->getId (), $blocks [0]->getLabel (), $blocks [0]->getSequence ());

			// Verificar que se obtuvo la información de los campos
			$fields = $module->getFields ();
			$this->assertNotNull ($fields, 'Fields should not be null');
			$this->assertCount (4, $fields, 'Fields count do not match');
			$this->checkField ($module->getId (), $blocks [0]->getId (), $fields [0]->getId (), $fields [0]->getName (), $fields [0]->getLabel (), $fields [0]->getUiType (), $fields [0]->getSequence (), $fields [0]->getTableName ());
			$this->checkFieldProfiles ($module->getId (), $fields [0]->getId (), 2);

			// Verificar que se obtuvo la información de los botones
			$buttons = $module->getButtons ();
			$this->assertNotNull ($buttons, 'Buttons should not be null');
			$this->assertCount (1, $buttons, 'Buttons count do not match');
			$this->checkButton ($moduleName, $buttons [0]->getId (), $buttons [0]->getLabel (), $buttons [0]->getDescription ());

			// Verificar que se obtuvo la información de los gráficos
			$charts = $module->getCharts ();
			$this->assertNotNull ($charts, 'Charts should not be null');
			$this->assertCount (1, $charts, 'Charts count do not match');
			$this->checkChart ($moduleName, $charts [0]->getId (), $charts [0]->getTitle (), $charts [0]->getFieldName (), $charts [0]->getGroupBy ());

			// Verificar que se obtuvo la información de las vistas
			$views = $module->getViews ();
			$this->assertNotNull ($views, 'Views should not be null');
			$this->assertCount (1, $views, 'Views count do not match');
			$this->checkView ($moduleName, $views [0]->getId (), $views [0]->getName ());
			$this->checkViewProfiles ($moduleName, $views [0]->getId (), 2);

			// Verificar que se obtuvo la información de las columnas
			$columns = $views [0]->getColumns ();
			$this->assertNotNull ($columns, 'Columns should not be null');
			$this->assertCount (1, $columns, 'Columns count do not match');
			$label = str_replace (' ', '_', $columns [0]->getLabel ());
			$this->checkViewColumn ($views [0]->getId (), $columns [0]->getSequence (), "{$columns [0]->getTableName ()}:{$columns [0]->getFieldName ()}:{$columns [0]->getFieldName ()}:{$columns [0]->getModuleName ()}_{$label}:{$columns [0]->getDataType ()}");

			$this->checkViewStandardFilter ($views [0]->getId ());
			$this->checkViewAdvancedFilterGroup ($views [0]->getId ());
			$this->checkViewAdvancedFilter ($views [0]->getId ());
			$this->checkSharingConfiguration ($module->getId (), 1);
			$this->checkWebServiceConfiguration ($moduleName, 1);
		}

		/**
		 * Obtener los encabezados del módulo
		 * @depends testUpdateToolModule
		 */
		public function testFetchModuleHeaders () {
			$moduleName = 'tool_module';
			$module     = ModuleManager::getInstance (self::$adb)->fetchModule ($moduleName, true);

			// Verificar que el objeto existe y que se asignó el ID
			$this->assertNotNull ($module, 'Module should not be null');
			$this->assertNotEmpty ($module->getId (), 'Module ID should not be empty');

			// Verificar que se obtuvo la información del módulo de la base de datos
			$this->checkModule ($moduleName, $module->getId (), $module->getLabel (), $module->getType (), $module->getSequence (), 1);
			$this->checkModuleProfiles ($moduleName, $module->getId (), 2);
			$this->checkModuleStandardPermissions ($moduleName, $module->getId (), 10);
			$this->checkEntityName ($moduleName, $module->getId (), $module->getEntityIdentifier ());
			$this->checkEntitySequence ($moduleName, 20, $module->getEntityPrefix (), $module->getEntityInitialSequence ());

			// Verificar que no se obtuvo la información de los bloques
			$blocks = $module->getBlocks ();
			$this->assertNull ($blocks, 'Blocks should be null');

			// Verificar que no se obtuvo la información de los campos
			$fields = $module->getFields ();
			$this->assertNull ($fields, 'Fields should be null');

			// Verificar que no se obtuvo la información de los botones
			$buttons = $module->getButtons ();
			$this->assertNull ($buttons, 'Buttons should be null');

			// Verificar que no se obtuvo la información de los gráficos
			$charts = $module->getCharts ();
			$this->assertNull ($charts, 'Charts should be null');

			// Verificar que no se obtuvo la información de las vistas
			$views = $module->getViews ();
			$this->assertNull ($views, 'Views should be null');

			$this->checkSharingConfiguration ($module->getId (), 1);
			$this->checkWebServiceConfiguration ($moduleName, 1);
		}

		/**
		 * Eliminar todos los módulos
		 * @depends testFetchModule
		 */
		public function testDeleteModules () {
			$mm = ModuleManager::getInstance (self::$adb);

			$moduleName = 'tool_module';
			$module     = $mm->fetchModule ($moduleName);
			$mm->deleteModule ($module, true);

			// Verificar que eliminó el módulo
			$this->checkModule ($moduleName);
			$this->checkEntityName ($moduleName);
			$this->checkEntitySequence ($moduleName);
			$this->checkBlock ($module->getId ());
			$this->checkField ($module->getId ());
			$this->checkFieldProfiles ($module->getId ());
			$this->checkButton ($moduleName);
			$this->checkChart ($moduleName);
			$this->checkView ($moduleName);
			$this->checkSharingConfiguration ($module->getId (), 0);
			$this->checkWebServiceConfiguration ($moduleName, 0);

			$moduleName = 'user_module';
			$module     = $mm->fetchModule ($moduleName);
			$mm->deleteModule ($module, true);

			// Verificar que eliminó el módulo
			$this->checkModule ($moduleName);
			$this->checkEntityName ($moduleName);
			$this->checkEntitySequence ($moduleName);
			$this->checkBlock ($module->getId ());
			$this->checkField ($module->getId ());
			$this->checkFieldProfiles ($module->getId ());
			$this->checkButton ($moduleName);
			$this->checkChart ($moduleName);
			$this->checkView ($moduleName);
			$this->checkSharingConfiguration ($module->getId (), 0);
			$this->checkWebServiceConfiguration ($moduleName, 0);
		}

	}
	// @codingStandardsIgnoreEnd

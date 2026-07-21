<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/ProfileManager.php');

	/**
	 * Prueba funcional de la clase ProfileManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ProfileManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas
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
				"CREATE TABLE IF NOT EXISTS `vtiger_customview` (
					`cvid` INT(19) NOT NULL,
					`viewname` VARCHAR(100) NOT NULL,
					`setdefault` INT(1) DEFAULT '0',
					`setmetrics` INT(1) DEFAULT '0',
					`entitytype` VARCHAR(25) NOT NULL,
					`status` INT(1) DEFAULT '1',
					`userid` INT(19) DEFAULT '1',
					`clientview` INT(11) NOT NULL DEFAULT '0',
					PRIMARY KEY (`cvid`),
					KEY `customview_entitytype_idx` (`entitytype`),
					CONSTRAINT `fk_1_vtiger_customview` FOREIGN KEY (`entitytype`) REFERENCES `vtiger_tab` (`name`) ON DELETE CASCADE
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
				"CREATE TABLE `vtiger_actionmapping` (
					`actionid` INT(19) NOT NULL,
					`actionname` VARCHAR(200) NOT NULL,
					`securitycheck` INT(19) NULL DEFAULT NULL,
					PRIMARY KEY (`actionid`, `actionname`)
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
					`tabid` INT(10) DEFAULT NULL,
					`fieldid` INT(19) NOT NULL,
					`visible` INT(19) DEFAULT NULL,
					`readonly` INT(19) DEFAULT NULL,
					PRIMARY KEY (`fieldid`),
					KEY `def_org_field_tabid_fieldid_idx` (`tabid`,`fieldid`),
					KEY `def_org_field_tabid_idx` (`tabid`),
					KEY `def_org_field_visible_fieldid_idx` (`visible`,`fieldid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_category_apps` (
					`catappid` INT(11) NOT NULL AUTO_INCREMENT,
					`code` VARCHAR(20) NOT NULL,
					`name` VARCHAR(256) NOT NULL,
					`status` VARCHAR(20) NOT NULL,
					`description` TEXT NULL,
					PRIMARY KEY (`catappid`)
				)
				COLLATE='utf8_general_ci'
				ENGINE=InnoDB"
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
					INDEX `FK_vtiger_config_applications_vtiger_profile` (`app_profile`),
					INDEX `FK_vtiger_config_applications_vtiger_category_apps` (`app_category`),
					CONSTRAINT `FK_vtiger_config_applications_vtiger_category_apps` FOREIGN KEY (`app_category`) REFERENCES `vtiger_category_apps` (`catappid`) ON UPDATE CASCADE,
					CONSTRAINT `FK_vtiger_config_applications_vtiger_profile` FOREIGN KEY (`app_profile`) REFERENCES `vtiger_profile` (`profileid`) ON UPDATE CASCADE
				)
				COLLATE='utf8_general_ci'
				ENGINE=InnoDB"
			);

			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (0, 'Save', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (1, 'EditView', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (2, 'Delete', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (3, 'index', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (4, 'DetailView', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (5, 'Import', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (6, 'Export', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (8, 'Merge', 0)");
			self::$adb->query ("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (10, 'DuplicatesHandling', 0)");
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

		// Profiles

		/**
		 * Intentar obtener un perfil inexistente
		 */
		public function testFetchNonExistingProfile () {
			$this->assertNull (ProfileManager::getInstance (self::$adb)->fetchProfile ('unknown_profile'));
		}

		/**
		 * Intentar crear un perfil incompleto
		 * Debe arrojar una ProfileException
		 */
		public function testCreateIncompleteProfile () {
			$profile = Profile::getInstance ();
			$this->expectException (ProfileException::class);
			ProfileManager::getInstance (self::$adb)->saveProfile ($profile);
		}

		/**
		 * Crear un perfil válido
		 */
		public function testCreateValidProfile () {
			$profile      = Profile::getInstance ()
				->setEditPermission (ProfileInterface::PERMISSION_DENY)
				->setName ('my_test_profile')
				->setDescription ('This is my test profile')
				->setViewPermission (ProfileInterface::PERMISSION_DENY);
			$savedProfile = ProfileManager::getInstance (self::$adb)->saveProfile ($profile);

			// Verificar que el método retorna el perfil correctamente configurado
			$this->assertNotNull ($savedProfile, 'Saved profile should not be null');
			$this->assertInstanceOf (Profile::class, $savedProfile, 'Saved profile should be an instance of Profile');
			$this->assertNotEmpty ($savedProfile->getId (), 'Saved profile ID should not be empty');

			// Verificar que se creó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile WHERE profilename=?', array ('my_test_profile'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');

			// Verificar que los valores en la base de datos se crearon correctamente
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (1, $row ['profileid'], 'Saved profile IDs do not match');
			$this->assertEquals ('my_test_profile', $row ['profilename'], 'Saved profile names do not match');
			$this->assertEquals ('This is my test profile', $row ['description'], 'Saved profile descriptions do not match');

			// Verificar que se crearon los perfiles globales
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2globalpermissions WHERE profileid=? AND globalactionid=?', array (1, ProfileInterface::ACTION_EDIT_ALL));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Edit global profile not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ProfileInterface::PERMISSION_DENY, $row ['globalactionpermission'], 'Edit permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2globalpermissions WHERE profileid=? AND globalactionid=?', array (1, ProfileInterface::ACTION_VIEW_ALL));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'View global profile not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ProfileInterface::PERMISSION_DENY, $row ['globalactionpermission'], 'View permissions do not match');
		}

		/**
		 * Actualizar un perfil existente
		 * @depends testCreateValidProfile
		 */
		public function testUpdateProfile () {
			$pm = ProfileManager::getInstance (self::$adb);
			$profile = $pm->fetchProfile ('my_test_profile')
				->setEditPermission (ProfileInterface::PERMISSION_ALLOW)
				->setDescription ('This is my test profile (version 2)')
				->setViewPermission (ProfileInterface::PERMISSION_ALLOW);
			$pm->saveProfile ($profile);

			// Verificar que los valores en la base de datos se actualizaron correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile WHERE profilename=?', array ('my_test_profile'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (1, $row ['profileid'], 'Saved profile IDs do not match');
			$this->assertEquals ('my_test_profile', $row ['profilename'], 'Saved profile names do not match');
			$this->assertEquals ('This is my test profile (version 2)', $row ['description'], 'Saved profile descriptions do not match');

			// Verificar que se actualizaron los perfiles globales
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2globalpermissions WHERE profileid=? AND globalactionid=?', array (1, ProfileInterface::ACTION_EDIT_ALL));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Edit global profile not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ProfileInterface::PERMISSION_ALLOW, $row ['globalactionpermission'], 'Edit permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2globalpermissions WHERE profileid=? AND globalactionid=?', array (1, ProfileInterface::ACTION_VIEW_ALL));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'View global profile not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ProfileInterface::PERMISSION_ALLOW, $row ['globalactionpermission'], 'View permissions do not match');
		}

		/**
		 * Obtener un perfil existente
		 * @depends testUpdateProfile
		 */
		public function testFetchExistingProfile () {
			$profile = ProfileManager::getInstance (self::$adb)->fetchProfile ('my_test_profile');
			$this->assertNotNull ($profile, 'Profile should not be null');
			$this->assertInstanceOf (Profile::class, $profile, 'Profile should be an instance of Profile');
			$this->assertEquals (1, $profile->getId (), 'Profile ids do not match');
			$this->assertEquals ('my_test_profile', $profile->getName (), 'Profile names do not match');
			$this->assertEquals ('This is my test profile (version 2)', $profile->getDescription (), 'Profile descriptions do not match');
			$this->assertEquals (ProfileInterface::PERMISSION_ALLOW, $profile->getEditPermission (), 'Edit permissions do not match');
			$this->assertEquals (ProfileInterface::PERMISSION_ALLOW, $profile->getViewPermission (), 'View permissions do not match');
		}

		/**
		 * Eliminar un perfil existente
		 * @depends testFetchExistingProfile
		 */
		public function testDeleteProfile () {
			$profile = Profile::getInstance ()
				->setName ('my_test_profile');
			ProfileManager::getInstance (self::$adb)->deleteProfile ($profile);

			// Verificar que se eliminó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile WHERE profilename=?', array ('my_test_profile'));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved profile not found');

			// Verificar que se eliminaron los perfiles globales
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2globalpermissions WHERE profileid=?', array (1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Global profiles count do not match');
		}

	}
	// @codingStandardsIgnoreEnd

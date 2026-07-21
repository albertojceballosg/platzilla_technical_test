<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/ModuleProfileManager.php');

	/**
	 * Prueba funcional de la clase ModuleProfileManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ModuleProfileManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas
		 * 4. Simular existencia de un módulo y tres perfiles
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
				"CREATE TABLE IF NOT EXISTS `vtiger_profile` (
					`profileid` INT(10) NOT NULL AUTO_INCREMENT,
					`profilename` VARCHAR(50) NOT NULL,
					`description` TEXT,
					PRIMARY KEY (`profileid`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"
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
				"CREATE TABLE `vtiger_actionmapping` (
					`actionid` INT(19) NOT NULL,
					`actionname` VARCHAR(200) NOT NULL,
					`securitycheck` INT(19) NULL DEFAULT NULL,
					PRIMARY KEY (`actionid`, `actionname`)
				) ENGINE=InnoDB"
			);

			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (1, 'Administrator', 'Admin Profile')");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (2, 'CRM', 'El CRM blah blah blah')");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (3, 'Another profile', 'Another super duper cuper profile')");
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

		/**
		 * Intentar crear un perfil de módulo incompleto
		 * Debe arrojar una ModuleProfileException
		 */
		public function testCreateIncompleteProfile () {
			$moduleProfile = ModuleProfile::getInstance ();
			$this->expectException (ModuleProfileException::class);
			ModuleProfileManager::getInstance (self::$adb)->saveProfile ($moduleProfile);
		}

		/**
		 * Crear un perfil de módulo válido
		 */
		public function testCreateValidProfile () {
			$moduleProfile = ModuleProfile::getInstance ()
				->setModuleName ('test_module')
				->setProfileName ('CRM')
				->setAccessPermission (ModuleProfileInterface::PERMISSION_DENY)
				->setDeletePermission (ModuleProfileInterface::PERMISSION_DENY)
				->setEditPermission (ModuleProfileInterface::PERMISSION_DENY)
				->setExportPermission (ModuleProfileInterface::PERMISSION_DENY)
				->setHandleDuplicatesPermission (ModuleProfileInterface::PERMISSION_DENY)
				->setImportPermission (ModuleProfileInterface::PERMISSION_DENY)
				->setListPermission (ModuleProfileInterface::PERMISSION_DENY)
				->setMergePermission (ModuleProfileInterface::PERMISSION_ALLOW)
				->setReadPermission (ModuleProfileInterface::PERMISSION_DENY)
				->setSavePermission (ModuleProfileInterface::PERMISSION_DENY);
			$savedProfile  = ModuleProfileManager::getInstance (self::$adb)->saveProfile ($moduleProfile);

			// Verificar que el método retorna el perfil correctamente configurado
			$this->assertNotNull ($savedProfile, 'Saved profile should not be null');
			$this->assertInstanceOf (ModuleProfile::class, $savedProfile, 'Saved profile should be an instance of ModuleProfile');

			// Verificar que se creó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');

			// Verificar que los valores en la base de datos se crearon correctamente
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permissions'], 'Access permissions do not match');

			// Verificar que se crearon los permisos estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (5, self::$adb->num_rows ($result), 'Standard permissions count do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 0));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Save permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permissions'], 'Save permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Edit permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permissions'], 'Edit permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 2));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Delete permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permissions'], 'Delete permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 3));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'List permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permissions'], 'List permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 4));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Read permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permissions'], 'Read permissions do not match');

			// Verificar que se crearon los permisos de utilidad
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'Utility permissions count do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 5));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Import permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permission'], 'Import permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 6));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Export permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permission'], 'Export permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 8));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Merge permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permission'], 'Merge permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 10));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Duplicate handling permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permission'], 'Duplicate handling permissions do not match');
		}

		/**
		 * Obtener un perfil existente
		 * @depends testCreateValidProfile
		 */
		public function testFetchProfiles () {
			$profiles = ModuleProfileManager::getInstance (self::$adb)->fetchProfiles ('test_module');
			$this->assertNotNull ($profiles, 'Profiles should not be null');
			$this->assertCount (1, $profiles, 'Profiles count do not match');

			// Verificar que el perfil tiene los valores adecuados
			$profile = $profiles [0];
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $profile->getAccessPermission (), 'Access permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $profile->getDeletePermission (), 'Delete permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $profile->getEditPermission (), 'Edit permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $profile->getExportPermission (), 'Export permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $profile->getHandleDuplicatesPermission (), 'Handle duplicates permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $profile->getImportPermission (), 'Import permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $profile->getListPermission (), 'List permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getMergePermission (), 'Merge permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $profile->getReadPermission (), 'Read permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $profile->getSavePermission (), 'Save permissions do not match');
		}

		/**
		 * Actualizar el perfil de módulo existente
		 * @depends testFetchProfiles
		 */
		public function testUpdateProfile () {
			$moduleProfile = ModuleProfile::getInstance ()
				->setModuleName ('test_module')
				->setProfileName ('CRM')
				->setAccessPermission (ModuleProfileInterface::PERMISSION_ALLOW)
				->setDeletePermission (ModuleProfileInterface::PERMISSION_ALLOW)
				->setEditPermission (ModuleProfileInterface::PERMISSION_ALLOW)
				->setExportPermission (ModuleProfileInterface::PERMISSION_ALLOW)
				->setHandleDuplicatesPermission (ModuleProfileInterface::PERMISSION_ALLOW)
				->setImportPermission (ModuleProfileInterface::PERMISSION_ALLOW)
				->setListPermission (ModuleProfileInterface::PERMISSION_ALLOW)
				->setMergePermission (ModuleProfileInterface::PERMISSION_DENY)
				->setReadPermission (ModuleProfileInterface::PERMISSION_ALLOW)
				->setSavePermission (ModuleProfileInterface::PERMISSION_ALLOW);
			$savedProfile  = ModuleProfileManager::getInstance (self::$adb)->saveProfile ($moduleProfile);

			// Verificar que el método retorna el perfil correctamente configurado
			$this->assertNotNull ($savedProfile, 'Saved profile should not be null');
			$this->assertInstanceOf (ModuleProfile::class, $savedProfile, 'Saved profile should be an instance of ModuleProfile');

			// Verificar que se creó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');

			// Verificar que los valores en la base de datos se crearon correctamente
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Saved profile permissions do not match');

			// Verificar que se crearon los permisos estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (5, self::$adb->num_rows ($result), 'Standard permissions count do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 0));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Save permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Save permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Edit permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Edit permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 2));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Delete permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Delete permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 3));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'List permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'List permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 4));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Read permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Read permissions do not match');

			// Verificar que se crearon los permisos de utilidad
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'Utility permissions count do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 5));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Import permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permission'], 'Import permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 6));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Export permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permission'], 'Export permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 8));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Merge permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permission'], 'Merge permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 10));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Duplicate handling permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permission'], 'Duplicate handling permissions do not match');
		}

		/**
		 * Eliminar el perfil de módulo existente
		 * @depends testUpdateProfile
		 */
		public function testDeleteProfile () {
			$moduleProfile = ModuleProfile::getInstance ()
				->setModuleName ('test_module')
				->setProfileName ('CRM');
			ModuleProfileManager::getInstance (self::$adb)->deleteProfile ($moduleProfile);

			// Verificar que se eliminó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved profile not found');

			// Verificar que se eliminaron los permisos estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Standard permissions count do not match');

			// Verificar que se eliminaron los permisos de utilidad
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Utility permissions count do not match');
		}

		/**
		 * Crear perfiles por defecto para un módulo
		 * @depends testDeleteProfile
		 */
		public function testCreateDefaultProfiles () {
			ModuleProfileManager::getInstance (self::$adb)->createDefaultProfiles ('test_module');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE tabid=?', array (1));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Default profiles count do not match');

			// Verificar que se crearon los permisos estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (5, self::$adb->num_rows ($result), 'Standard permissions count do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 0));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Save permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Save permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Edit permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Edit permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 2));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Delete permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Delete permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 3));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'List permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'List permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (2, 1, 4));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Read permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Read permissions do not match');

			// Verificar que se crearon los permisos de utilidad
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'Utility permissions count do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 5));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Import permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permission'], 'Import permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 6));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Export permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permission'], 'Export permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 8));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Merge permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permission'], 'Merge permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (2, 1, 10));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Duplicate handling permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permission'], 'Duplicate handling permissions do not match');
		}

		/**
		 * Eliminar perfiles de módulo
		 * @depends testCreateDefaultProfiles
		 */
		public function testDeleteProfiles () {
			ModuleProfileManager::getInstance (self::$adb)->deleteProfiles ('test_module');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE tabid=?', array (1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Default profiles count do not match');

			// Verificar que se eliminaron los permisos estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Standard permissions count do not match');

			// Verificar que se eliminaron los permisos de utilidad
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=?', array (2, 1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Utility permissions count do not match');
		}

		/**
		 * Crear perfiles por defecto
		 * @depends testDeleteProfiles
		 */
		public function testCreateDefaultProfilesByProfileName () {
			ModuleProfileManager::getInstance (self::$adb)->createDefaultProfilesByProfileName ('Another profile');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE profileid=? AND tabid=?', array (3, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');

			// Verificar que se crearon los permisos estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=?', array (3, 1));
			$this->assertEquals (5, self::$adb->num_rows ($result), 'Standard permissions count do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (3, 1, 0));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Save permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Save permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (3, 1, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Edit permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Edit permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (3, 1, 2));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Delete permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Delete permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (3, 1, 3));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'List permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'List permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=? AND operation=?', array (3, 1, 4));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Read permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Read permissions do not match');

			// Verificar que se crearon los permisos de utilidad
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=?', array (3, 1));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'Utility permissions count do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (3, 1, 5));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Import permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permission'], 'Import permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (3, 1, 6));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Export permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permission'], 'Export permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (3, 1, 8));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Merge permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $row ['permission'], 'Merge permissions do not match');

			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=?', array (3, 1, 10));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Duplicate handling permission not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $row ['permission'], 'Duplicate handling permissions do not match');
		}

		/**
		 * Obtener los perfiles de módulo para un perfil suministrado
		 * @depends testCreateDefaultProfilesByProfileName
		 */
		public function testFetchProfilesByProfileName () {
			$profiles = ModuleProfileManager::getInstance (self::$adb)->fetchProfilesByProfileName ('Another profile');
			$this->assertNotNull ($profiles, 'Profiles should not be null');
			$this->assertCount (1, $profiles, 'Profiles count do not match');

			// Verificar que el perfil tiene los valores adecuados
			$profile = $profiles [0];
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getAccessPermission (), 'Access permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getDeletePermission (), 'Delete permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getEditPermission (), 'Edit permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getExportPermission (), 'Export permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getHandleDuplicatesPermission (), 'Handle duplicates permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getImportPermission (), 'Import permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getListPermission (), 'List permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_DENY, $profile->getMergePermission (), 'Merge permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getReadPermission (), 'Read permissions do not match');
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getSavePermission (), 'Save permissions do not match');
		}

		/**
		 * Eliminar los perfiles de módulo para un perfil suministrado
		 * @depends testFetchProfilesByProfileName
		 */
		public function testDeleteProfilesByProfileName () {
			ModuleProfileManager::getInstance (self::$adb)->deleteProfilesByProfileName ('Another profile');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2tab WHERE tabid=?', array (1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Default profiles count do not match');

			// Verificar que se eliminaron los permisos estándar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2standardpermissions WHERE profileid=? AND tabid=?', array (3, 1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Standard permissions count do not match');

			// Verificar que se eliminaron los permisos de utilidad
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2utility WHERE profileid=? AND tabid=?', array (3, 1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Utility permissions count do not match');
		}

	}
	// @codingStandardsIgnoreEnd

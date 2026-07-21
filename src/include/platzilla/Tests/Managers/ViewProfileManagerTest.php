<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/ViewProfileManager.php');

	/**
	 * Prueba funcional de la clase ViewViewProfileManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ViewViewProfileManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas
		 * 4. Simular existencia de un módulo, un campo y dos perfiles
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
				"CREATE TABLE IF NOT EXISTS `vtiger_profile` (
					`profileid` INT(10) NOT NULL AUTO_INCREMENT,
					`profilename` VARCHAR(50) NOT NULL,
					`description` TEXT,
					PRIMARY KEY (`profileid`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"
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

			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_customview` (`cvid`, `viewname`, `setdefault`, `setmetrics`, `entitytype`, `status`, `userid`, `clientview`) VALUES (1, 'Test view', 1, 1, 'test_module', 1, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (1, 'Administrator', 'Admin Profile')");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (2, 'CRM', 'El CRM blah blah blah')");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (3, 'Another profile', 'Another super duper cuper profile')");
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
		 * Intentar crear un perfil de vista incompleto
		 * Debe arrojar una ViewProfileException
		 */
		public function testCreateIncompleteProfile () {
			$profile = ViewProfile::getInstance ();
			$this->expectException (ViewProfileException::class);
			ViewProfileManager::getInstance (self::$adb)->saveProfile ($profile);
		}

		/**
		 * Crear un perfil de vista válido
		 */
		public function testCreateValidProfile () {
			$profile      = ViewProfile::getInstance ()
				->setAccessPermission (ViewProfileInterface::PERMISSION_DENY)
				->setDefault (ViewProfileInterface::DEFAULT_YES)
				->setModuleName ('test_module')
				->setProfileName ('CRM')
				->setViewName ('Test view');
			$savedProfile = ViewProfileManager::getInstance (self::$adb)->saveProfile ($profile);

			// Verificar que el método retorna el perfil correctamente configurado
			$this->assertNotNull ($savedProfile, 'Saved profile should not be null');
			$this->assertInstanceOf (ViewProfile::class, $savedProfile, 'Saved profile should be an instance of ViewProfile');

			// Verificar que se creó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE profileid=? AND cvid=?', array (2, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');

			// Verificar que los valores en la base de datos se crearon correctamente
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ViewProfileInterface::DEFAULT_YES, $row ['setdefault'], 'Saved profile default properties do not match');
			$this->assertEquals (ViewProfileInterface::PERMISSION_DENY, $row ['permissions'], 'Saved profile permissions do not match');
		}

		/**
		 * Actualizar el perfil de vista existente
		 * @depends testCreateValidProfile
		 */
		public function testUpdateProfile () {
			$profile      = ViewProfile::getInstance ()
				->setAccessPermission (ViewProfileInterface::PERMISSION_ALLOW)
				->setDefault (ViewProfileInterface::DEFAULT_NO)
				->setModuleName ('test_module')
				->setProfileName ('CRM')
				->setViewName ('Test view');
			$savedProfile = ViewProfileManager::getInstance (self::$adb)->saveProfile ($profile);

			// Verificar que el método retorna el perfil correctamente configurado
			$this->assertNotNull ($savedProfile, 'Saved profile should not be null');
			$this->assertInstanceOf (ViewProfile::class, $savedProfile, 'Saved profile should be an instance of ViewProfile');

			// Verificar que se creó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE profileid=? AND cvid=?', array (2, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');

			// Verificar que los valores en la base de datos se crearon correctamente
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (ViewProfileInterface::DEFAULT_NO, $row ['setdefault'], 'Saved profile default properties do not match');
			$this->assertEquals (ViewProfileInterface::PERMISSION_ALLOW, $row ['permissions'], 'Saved profile permissions do not match');
		}

		/**
		 * Eliminar el perfil de vista existente
		 * @depends testUpdateProfile
		 */
		public function testDeleteProfile () {
			$profile = ViewProfile::getInstance ()
				->setModuleName ('test_module')
				->setProfileName ('CRM')
				->setViewName ('Test view');
			ViewProfileManager::getInstance (self::$adb)->deleteProfile ($profile);

			// Verificar que se eliminó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE profileid=? AND cvid=?', array (2, 1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved profile not found');
		}

		/**
		 * Crear perfiles por defecto para una vista
		 * @depends testDeleteProfile
		 */
		public function testCreateDefaultProfiles () {
			ViewProfileManager::getInstance (self::$adb)->createDefaultProfiles ('test_module', 'Test view');
			$result = self::$adb->pquery ('SELECT p2v.* FROM vtiger_profile2customview p2v WHERE p2v.cvid=?', array (1));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Default view profiles count do not match');
		}

		/**
		 * Eliminar perfiles de vista
		 * @depends testCreateDefaultProfiles
		 */
		public function testDeleteProfiles () {
			ViewProfileManager::getInstance (self::$adb)->deleteProfiles ('test_module', 'Test view');
			$result = self::$adb->pquery ('SELECT p2v.* FROM vtiger_profile2customview p2v WHERE p2v.cvid=?', array (1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Default view profiles count do not match');
		}

		/**
		 * Crear perfiles por defecto
		 * @depends testDeleteProfiles
		 */
		public function testCreateDefaultProfilesByProfileName () {
			// Crear perfiles de vista por defecto
			ViewProfileManager::getInstance (self::$adb)->createDefaultProfilesByProfileName ('Another profile');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE profileid=? AND tabid=?', array (3, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');
		}

		/**
		 * Obtener los perfiles de módulo para un perfil suministrado
		 * @depends testCreateDefaultProfilesByProfileName
		 */
		public function testFetchProfilesByProfileName () {
			$profiles = ViewProfileManager::getInstance (self::$adb)->fetchProfilesByProfileName ('Another profile');
			$this->assertNotNull ($profiles, 'Profiles should not be null');
			$this->assertCount (1, $profiles, 'Profiles count do not match');

			// Verificar que el perfil tiene los valores adecuados
			$profile = $profiles [0];
			$this->assertEquals (ModuleProfileInterface::PERMISSION_ALLOW, $profile->getAccessPermission (), 'Access permissions do not match');
		}

		/**
		 * Eliminar los perfiles de módulo para un perfil suministrado
		 * @depends testFetchProfilesByProfileName
		 */
		public function testDeleteProfilesByProfileName () {
			ViewProfileManager::getInstance (self::$adb)->deleteProfilesByProfileName ('Another profile');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2customview WHERE tabid=?', array (1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Default profiles count do not match');
		}

	}
	// @codingStandardsIgnoreEnd

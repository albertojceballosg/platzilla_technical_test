<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/FieldProfileManager.php');

	/**
	 * Prueba funcional de la clase FieldProfileManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class FieldProfileManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas
		 * 4. Simular existencia de un módulo, un campo y hasta tres perfiles
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

			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 1, 'test_field', 'vtiger_test_module', 1, '1', 'test_field', 'Test field', 1, -1, '', 100, 2, -1, 1, 'V~O', 1, NULL, 'BAS', 1, '', NULL)");
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
		 * Intentar crear un perfil de campo incompleto
		 * Debe arrojar una FieldProfileException
		 */
		public function testCreateIncompleteFieldProfile () {
			$fieldProfile = FieldProfile::getInstance ();
			$this->expectException (FieldProfileException::class);
			FieldProfileManager::getInstance (self::$adb)->saveProfile ($fieldProfile);
		}

		/**
		 * Crear un perfil de campo válido
		 */
		public function testCreateValidFieldProfile () {
			$fieldProfile      = FieldProfile::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('test_module')
				->setProfileName ('CRM')
				->setReadOnly (FieldProfileInterface::READ_ONLY)
				->setVisibility (FieldProfileInterface::VISIBILITY_HIDDEN);
			$savedFieldProfile = FieldProfileManager::getInstance (self::$adb)->saveProfile ($fieldProfile);

			// Verificar que el método retorna el perfil correctamente configurado
			$this->assertNotNull ($savedFieldProfile, 'Saved profile should not be null');
			$this->assertInstanceOf (FieldProfile::class, $savedFieldProfile, 'Saved profile should be an instance of Profile');

			// Verificar que se creó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2field WHERE profileid=? AND tabid=? AND fieldid=?', array (2, 1, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');

			// Verificar que los valores en la base de datos se crearon correctamente
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (1, $row ['readonly'], 'Saved profile readonly properties do not match');
			$this->assertEquals (1, $row ['visible'], 'Saved profile visibilities do not match');
		}

		/**
		 * Obtener un perfil de campo existente
		 * @depends testCreateValidFieldProfile
		 */
		public function testFetchExistingFieldProfile () {
			$fieldName     = 'test_field';
			$moduleName    = 'test_module';
			$fieldProfiles = FieldProfileManager::getInstance (self::$adb)->fetchProfiles ($moduleName, $fieldName);
			$this->assertNotNull ($fieldProfiles, 'Field profiles should not be null');
			$this->assertCount (1, $fieldProfiles, 'Field profiles count do not match');

			$fieldProfile = $fieldProfiles [0];
			// Verificar que el objeto contiene los valores existentes en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2field p2f INNER JOIN vtiger_tab t ON t.tabid=p2f.tabid AND t.name=? INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=?', array ($moduleName, $fieldName));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Field profiles count do not match');

			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($fieldProfile->getReadOnly (), $row ['readonly'], 'Readonly properties do not match');
			$this->assertEquals ($fieldProfile->getVisibility (), $row ['visible'], 'Visible properties do not match');
		}

		/**
		 * Actualizar un perfil de campo válido
		 * @depends testFetchExistingFieldProfile
		 */
		public function testUpdateFieldProfile () {
			$fieldProfile      = FieldProfile::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('test_module')
				->setProfileName ('CRM')
				->setReadOnly (FieldProfileInterface::READ_WRITE)
				->setVisibility (FieldProfileInterface::VISIBILITY_VISIBLE);
			$savedFieldProfile = FieldProfileManager::getInstance (self::$adb)->saveProfile ($fieldProfile);

			// Verificar que el método retorna el perfil correctamente configurado
			$this->assertNotNull ($savedFieldProfile, 'Saved profile should not be null');
			$this->assertInstanceOf (FieldProfile::class, $savedFieldProfile, 'Saved profile should be an instance of Profile');

			// Verificar que se creó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2field WHERE profileid=? AND tabid=? AND fieldid=?', array (2, 1, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');

			// Verificar que los valores en la base de datos se crearon correctamente
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (FieldProfileInterface::READ_WRITE, $row ['readonly'], 'Saved profile readonly properties do not match');
			$this->assertEquals (FieldProfileInterface::VISIBILITY_VISIBLE, $row ['visible'], 'Saved profile visibilities do not match');
		}

		/**
		 * Eliminar un perfil de campo válido
		 * @depends testUpdateFieldProfile
		 */
		public function testDeleteFieldProfile () {
			$fieldProfile = FieldProfile::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('test_module')
				->setProfileName ('CRM');
			FieldProfileManager::getInstance (self::$adb)->deleteProfile ($fieldProfile);

			// Verificar que se eliminó el perfil correctamente
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2field WHERE profileid=? AND tabid=? AND fieldid=?', array (2, 1, 1));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved profile not found');
		}

		/** Crear perfiles por defecto para un campo */
		public function testCreateDefaultFieldProfiles () {
			FieldProfileManager::getInstance (self::$adb)->createDefaultProfiles ('test_module', 'test_field');
			$result = self::$adb->pquery ('SELECT p2f.* FROM vtiger_profile2field p2f WHERE p2f.tabid=? AND p2f.fieldid=?', array (1, 1));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Default field profiles count do not match');
		}

		/**
		 * Eliminar perfiles de un campo
		 * @depends testCreateDefaultFieldProfiles
		 */
		public function testDeleteFieldProfiles () {
			FieldProfileManager::getInstance (self::$adb)->deleteProfiles ('test_module', 'test_field');
			$result = self::$adb->pquery ('SELECT p2f.* FROM vtiger_profile2field p2f INNER JOIN vtiger_field f ON f.fieldid=p2f.fieldid AND f.fieldname=? INNER JOIN vtiger_tab t ON t.tabid=f.tabid AND t.name=?', array ('test_field', 'test_module'));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Default field profiles count do not match');
		}

		/**
		 * Crear perfiles de campo por defecto para el perfil suministrado
		 * @depends testDeleteFieldProfiles
		 */
		public function testCreateDefaultProfilesByProfileName () {
			FieldProfileManager::getInstance (self::$adb)->createDefaultProfilesByProfileName ('Another profile');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_profile2field WHERE profileid=? AND tabid=?', array (3, 1));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved profile not found');
		}

		/**
		 * Obtener los perfiles de campo para un perfil suministrado
		 * @depends testCreateDefaultProfilesByProfileName
		 */
		public function testFetchProfilesByProfileName () {
			$profiles = FieldProfileManager::getInstance (self::$adb)->fetchProfilesByProfileName ('Another profile');
			$this->assertNotNull ($profiles, 'Profiles should not be null');
			$this->assertCount (1, $profiles, 'Profiles count do not match');
		}

	}
	// @codingStandardsIgnoreEnd

<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/PicklistManager.php');

	/**
	 * Prueba funcional de la clase PicklistManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class PicklistManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_field, vtiger_picklist, vtiger_role, vtiger_role2picklist
		 * 4. Simular la existencia de dos roles
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

			self::$adb->query ("INSERT INTO `vtiger_picklist_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_picklistvalues_seq` (id) VALUES (0)");
			self::$adb->query ("INSERT INTO `vtiger_role` (`roleid`, `rolename`, `parentrole`, `depth`, `iscustomer`, `ispartner`, `default_module`) VALUES ('H1', 'Organización', 'H1', 0, NULL, NULL, NULL)");
			self::$adb->query ("INSERT INTO `vtiger_role` (`roleid`, `rolename`, `parentrole`, `depth`, `iscustomer`, `ispartner`, `default_module`) VALUES ('H2', 'Director General', 'H1::H2', 1, 0, NULL, 'Home')");
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
		 * Intentar crear un Picklist no válido
		 * Debe arrojar una excepción PicklistException
		 */
		public function testCreateInvalidPicklist () {
			$picklist = Picklist::getInstance ()
				->setName ('My picklist')
				->setValues (array ());
			$this->expectException (PicklistException::class);
			PicklistManager::getInstance (self::$adb)->savePicklist ($picklist);
		}

		/**
		 * Crear un picklist válido
		 */
		public function testCreateValidPicklist () {
			$picklist = Picklist::getInstance ()
				->setName ('my_picklist')
				->setValues (array (
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('My first picklist value'),
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setValue ('My second picklist value'),
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_HIDDEN)->setValue ('My third picklist value'),
				));
			$this->assertNull ($picklist->getId (), 'Picklist Id should be null');
			PicklistManager::getInstance (self::$adb)->savePicklist ($picklist);

			// Verificar que el objeto Picklist es correcto, y que se asignó el Id
			$this->assertNotNull ($picklist, 'Picklist should not be null');
			$this->assertNotNull ($picklist->getId (), 'Picklist Id should not be null');

			// Verificar en base de datos si se creó correctamente y que se crearon las tablas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ('my_picklist'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'my_picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_picklist'");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table vtiger_my_picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_picklist_seq'");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table vtiger_my_picklist_seq should exists');

			// Verificar en base de datos si se crearon los valores
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ('SELECT * FROM vtiger_my_picklist WHERE my_picklist IN (?, ?, ?)', array ('My first picklist value', 'My second picklist value', 'My third picklist value'));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'my_picklist values should be 3');

			// Verificar si se asignaron los roles
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl INNER JOIN vtiger_my_picklist pl ON pl.picklist_valueid=r2pl.picklistvalueid WHERE pl.my_picklist IN (?, ?, ?)', array ('My first picklist value', 'My second picklist value', 'My third picklist value'));
			$this->assertEquals (6, self::$adb->num_rows ($result), 'roles should be 6');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistvalueid IN (?, ?, ?)', array (10, 20, 30));
			$this->assertEquals (6, self::$adb->num_rows ($result), 'roles should be 6');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistid IN (?)', array (10));
			$this->assertEquals (6, self::$adb->num_rows ($result), 'roles should be 6');
		}

		/**
		 * Obtener un picklist válido
		 * @depends testCreateValidPicklist
		 */
		public function testFetchExistingPicklist () {
			$picklist = PicklistManager::getInstance (self::$adb)->fetchPicklistByName ('my_picklist');
			$this->assertNotNull ($picklist, 'Picklist should not be null');
			$this->assertInstanceOf (Picklist::class, $picklist, 'Picklist should be of class Picklist');

			$picklistValues = $picklist->getValues ();
			$this->assertEquals (3, count ($picklistValues), 'Picklist has no values');

			$initialValues = array ('My first picklist value', 'My second picklist value', 'My third picklist value');
			foreach ($picklistValues as $picklistValue) {
				$this->assertNotNull ($picklistValue->getId (), 'Picklist value should not be null');
				$this->assertContains ($picklistValue->getValue (), $initialValues, "{$picklistValue->getValue ()} is an unexpected picklist value");
			}
		}

		/**
		 * Obtener un Picklist no existente de la base de datos
		 */
		public function testFetchNonExistingPicklist () {
			$unknownPicklist = PicklistManager::getInstance (self::$adb)->fetchPicklistByName ('Unknown picklist');
			$this->assertNull ($unknownPicklist, 'Unknown picklist should be null');
		}

		/**
		 * Actualizar un picklist existente
		 * @depends testCreateValidPicklist
		 */
		public function testUpdateExistingPicklist () {
			$picklist = Picklist::getInstance ()
				->setName ('my_picklist')
				->setValues (array (
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_HIDDEN)->setValue ('My fourth picklist value'),
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_HIDDEN)->setValue ('My fifth picklist value'),
				));
			$pm       = PicklistManager::getInstance (self::$adb);
			$pm->savePicklist ($picklist);

			$samePicklist = $pm->fetchPicklistByName ('my_picklist');
			$this->assertNotNull ($samePicklist, 'Picklist should not be null');
			$this->assertInstanceOf (Picklist::class, $samePicklist, 'Picklist should be of class Picklist');

			$picklistValues = $samePicklist->getValues ();
			$this->assertEquals (2, count ($picklistValues), 'Picklist has no values');

			$initialValues = array ('My fourth picklist value', 'My fifth picklist value');
			foreach ($picklistValues as $picklistValue) {
				$this->assertNotNull ($picklistValue->getId (), 'Picklist value should not be null');
				$this->assertContains ($picklistValue->getValue (), $initialValues, "{$picklistValue->getValue ()} is an unexpected picklist value");
			}

			// Verificar en base de datos si se crearon los valores
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ('SELECT * FROM vtiger_my_picklist WHERE my_picklist IN (?, ?, ?)', array ('My first picklist value', 'My second picklist value', 'My third picklist value'));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'my_picklist values should be 0');
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ('SELECT * FROM vtiger_my_picklist WHERE my_picklist IN (?, ?)', array ('My fourth picklist value', 'My fifth picklist value'));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'my_picklist values should be 2');

			// Verificar si se asignaron los roles
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl INNER JOIN vtiger_my_picklist pl ON pl.picklist_valueid=r2pl.picklistvalueid WHERE pl.my_picklist IN (?, ?, ?)', array ('My first picklist value', 'My second picklist value', 'My third picklist value'));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'roles should be 0');
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl INNER JOIN vtiger_my_picklist pl ON pl.picklist_valueid=r2pl.picklistvalueid WHERE pl.my_picklist IN (?, ?)', array ('My fourth picklist value', 'My fifth picklist value'));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'roles should be 4');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistvalueid IN (?, ?)', array (40, 50));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'roles should be 4');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistid IN (?)', array (10));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'roles should be 4');
		}

		/**
		 * Obtener un PicklistValue existente de la base de datos
		 * @depends testUpdateExistingPicklist
		 */
		public function testFetchExistingPicklistValue () {
			$picklistValue = PicklistManager::getInstance (self::$adb)->fetchPicklistValue ('my_picklist', 'My fifth picklist value');
			$this->assertNotNull ($picklistValue, 'PicklistValue should not be null');
			$this->assertNotNull ($picklistValue->getId (), 'PicklistValue ID should not be null');
			$this->assertEquals (PicklistValueInterface::PRESENCE_HIDDEN, $picklistValue->getPresence (), 'PicklistValue presences do not match');
			$this->assertEquals ('My fifth picklist value', $picklistValue->getValue (), 'PicklistValue values do not match');
		}

		/**
		 * Intentar eliminar un Picklist que está asociado a un campo existente.
		 * No debe eliminar el picklist
		 * @depends testFetchExistingPicklistValue
		 */
		public function testDeleteUsedPicklist () {
			// Verificar que el picklist existe y las tablas también
			$result = self::$adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ('my_picklist'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'my_picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_picklist'");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table vtiger_my_picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_picklist_seq'");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table vtiger_my_picklist_seq should exists');

			// Simular un picklist asociado a un campo: crear módulo y campo
			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test Module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 1, 'my_picklist', 'vtiger_test_module', 1, '15', 'my_picklist', 'My picklist', 1, 2, '', 100, 4, 4146, 1, 'V~M', 2, 4, 'BAS', 2, '', NULL)");

			// Intentar eliminar
			$picklist = Picklist::getInstance ()
				->setName ('my_picklist')
				->setValues (array (
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_HIDDEN)->setValue ('My fourth picklist value'),
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_HIDDEN)->setValue ('My fifth picklist value'),
				));
			PicklistManager::getInstance (self::$adb)->deletePicklist ($picklist);

			// Verificar que aún existen tanto el picklist como las tablas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ('my_picklist'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'my_picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_picklist'");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table vtiger_my_picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_picklist_seq'");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table vtiger_my_picklist_seq should exists');

			// Verificar si los roles permanecen igual
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl INNER JOIN vtiger_my_picklist pl ON pl.picklist_valueid=r2pl.picklistvalueid WHERE pl.my_picklist IN (?, ?, ?)', array ('My first picklist value', 'My second picklist value', 'My third picklist value'));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'roles should be 0');
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl INNER JOIN vtiger_my_picklist pl ON pl.picklist_valueid=r2pl.picklistvalueid WHERE pl.my_picklist IN (?, ?)', array ('My fourth picklist value', 'My fifth picklist value'));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'roles should be 4');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistvalueid IN (?, ?)', array (40, 50));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'roles should be 4');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistid IN (?)', array (10));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'roles should be 4');
		}

		/**
		 * Intentar eliminar un Picklist que no tiene asociación con ningún campo
		 * Debe eliminar el Picklist y las tablas del mismo
		 * @depends testDeleteUsedPicklist
		 */
		public function testDeleteUnusedPicklist () {
			// Verificar que el picklist existe y las tablas también
			$result = self::$adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ('my_picklist'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'my_picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_picklist'");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table vtiger_my_picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_picklist_seq'");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table vtiger_my_picklist_seq should exists');

			// Simular un picklist no asociado: Eliminar campos
			self::$adb->query ('DELETE FROM vtiger_field');

			// Intentar eliminar
			$picklist = Picklist::getInstance ()
				->setName ('my_picklist')
				->setValues (array (
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_HIDDEN)->setValue ('My fourth picklist value'),
					PicklistValue::getInstance ()->setPresence (PicklistValueInterface::PRESENCE_HIDDEN)->setValue ('My fifth picklist value'),
				));
			PicklistManager::getInstance (self::$adb)->deletePicklist ($picklist);

			// Verificar que se eliminaron tanto la entrada en vtiger_picklist como las tablas correspondientes
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_picklist'");
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Table vtiger_my_picklist should not exists anymore');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_picklist_seq'");
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Table vtiger_my_picklist should not exists anymore');

			// Verificar si se eliminaron los roles
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistvalueid IN (?, ?)', array (40, 50));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'roles should be 0');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistid IN (?)', array (10));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'roles should be 0');
		}

		/**
		 * Obtener PicklistValue no existentes de la base de datos
		 * 1. Intentar obtener un PicklistValue que no existe de un Picklist que sí existe
		 * 2. Intentar obtener un PicklistValue de un Picklist que no existe
		 * Ambos deben retornar null
		 */
		public function testFetchNonExistingPicklistValue () {
			$this->assertNull (PicklistManager::getInstance (self::$adb)->fetchPicklistValue ('my_picklist', 'My non existing picklist value'), 'PicklistValue should be null');
			$this->assertNull (PicklistManager::getInstance (self::$adb)->fetchPicklistValue ('non_existing_picklist', 'My non existing picklist value'), 'PicklistValue should be null');
		}

		public function testCreatePicklistWithSelectedRoles () {
			$role = Role::getInstance ()
				->setId ('H1')
				->setName ('Organización');
			$picklist = Picklist::getInstance ()
				->setName ('my_last_picklist')
				->setValues (array (
					PicklistValue::getInstance (false)->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setRoles (array ($role))->setValue ('My 1st picklist value'),
					PicklistValue::getInstance (false)->setPresence (PicklistValueInterface::PRESENCE_VISIBLE)->setRoles (array ($role))->setValue ('My 2nd picklist value'),
					PicklistValue::getInstance (false)->setPresence (PicklistValueInterface::PRESENCE_HIDDEN)->setRoles (array ($role))->setValue ('My 3rd picklist value'),
				));
			$this->assertNull ($picklist->getId (), 'Picklist Id should be null');
			PicklistManager::getInstance (self::$adb)->savePicklist ($picklist);

			// Verificar que el objeto Picklist es correcto, y que se asignó el Id
			$this->assertNotNull ($picklist, 'Picklist should not be null');
			$this->assertNotNull ($picklist->getId (), 'Picklist Id should not be null');

			// Verificar en base de datos si se creó correctamente y que se crearon las tablas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_picklist WHERE name=?', array ('my_last_picklist'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'my_last_picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_last_picklist'");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table vtiger_my_picklist should exists');
			$result = self::$adb->query ("SHOW TABLES LIKE 'vtiger_my_last_picklist_seq'");
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Table vtiger_my_picklist_seq should exists');

			// Verificar en base de datos si se crearon los valores
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ('SELECT * FROM vtiger_my_last_picklist WHERE my_last_picklist IN (?, ?, ?)', array ('My 1st picklist value', 'My 2nd picklist value', 'My 3rd picklist value'));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'my_picklist values should be 3');

			// Verificar si se asignaron los roles
			/** @noinspection SqlResolve */
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl INNER JOIN vtiger_my_last_picklist pl ON pl.picklist_valueid=r2pl.picklistvalueid WHERE pl.my_last_picklist IN (?, ?, ?)', array ('My 1st picklist value', 'My 2nd picklist value', 'My 3rd picklist value'));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'roles should be 3');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistvalueid IN (?, ?, ?)', array (60, 70, 80));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'roles should be 3');
			$result = self::$adb->pquery ('SELECT * FROM vtiger_role2picklist r2pl WHERE r2pl.picklistid IN (?)', array (20));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'roles should be 3');
		}

	}
	// @codingStandardsIgnoreEnd

<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/FieldModuleReferenceManager.php');

	/**
	 * Prueba funcional de la clase FieldModuleReferenceManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class FieldModuleReferenceManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_field, vtiger_fieldmodulerel
		 * 5. Simular existencia de dos módulos y tres campos, uno de ellos de tipo diferente a UI_TYPE_MODULE_REFERENCE
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

			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (2, 'test_related_module', 0, 2, 'Test related module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 1, 'test_field', 'vtiger_test_module', 1, '10', 'test_field', 'Test field', 1, 2, '', 100, 2, 4146, 1, 'V~M~LE~255', 2, 2, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (2, 2, 'test_related_field', 'vtiger_test_related_module', 1, '10', 'test_related_field', 'Test related field', 1, 2, '', 100, 2, 4146, 1, 'V~M~LE~255', 2, 2, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 3, 'test_non_reference_field', 'vtiger_test_module', 1, '1', 'test_non_reference_field', 'Test non module reference field', 1, 2, '', 100, 2, 4146, 1, 'V~M~LE~255', 2, 2, 'BAS', 2, '', NULL)");
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
		 * Intentar crear una referencia sin la información mínima necesaria
		 * Debe arrojar una FieldModuleReferenceException
		 */
		public function testCreateIncompleteReference () {
			$object = FieldModuleReference::getInstance ();
			$this->expectException (FieldModuleReferenceException::class);
			FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($object);
		}

		/**
		 * Intentar crear una referencia asociada a un nombre de campo no existente
		 * Debe arrojar una FieldModuleReferenceException
		 */
		public function testCreateNonExistingFieldNameReference () {
			$object = FieldModuleReference::getInstance ()
				->setFieldName ('unknown_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('test_related_module')
				->setSequence (1)
				->setStatus ('unknown');
			$this->expectException (FieldModuleReferenceException::class);
			$this->expectExceptionMessage (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_INVALID_FIELD_NAME);
			FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($object);
		}

		/**
		 * Intentar crear una referencia asociada a un nombre de módulo no existente
		 * Debe arrojar una FieldModuleReferenceException
		 */
		public function testCreateNonExistingModuleNameReference () {
			$object = FieldModuleReference::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('unknown_module')
				->setReferencedModuleName ('test_related_module')
				->setSequence (1)
				->setStatus ('unknown');
			$this->expectException (FieldModuleReferenceException::class);
			$this->expectExceptionMessage (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_INVALID_MODULE_NAME);
			FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($object);
		}

		/**
		 * Intentar crear una referencia asociada a un campo que no es de tipo referencia a módulo
		 * Debe arrojar una FieldModuleReferenceException
		 */
		public function testCreateNonReferenceFieldReference () {
			$object = FieldModuleReference::getInstance ()
				->setFieldName ('test_non_reference_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('test_related_module')
				->setSequence (1)
				->setStatus ('unknown');
			$this->expectException (FieldModuleReferenceException::class);
			$this->expectExceptionMessage (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_INVALID_FIELD_NAME);
			FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($object);
		}

		/**
		 * Intentar crear una referencia válida
		 */
		public function testCreateValidReference () {
			$object      = FieldModuleReference::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('test_related_module')
				->setStatus ('unknown');
			$savedObject = FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($object);
			$this->assertNotNull ($savedObject, 'Reference should not be null');
			$this->assertInstanceOf (FieldModuleReference::class, $savedObject, 'Reference is not an instance of FieldModuleReference');
			$this->assertEquals (1, $object->getSequence (), 'Sequence numbers do not match');

			// Verificar que se creó correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=? AND relmodule=? AND sequence=? AND status=?', array (1, 'test_module', 'test_related_module', 1, 'unknown'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
		}

		/**
		 * Intentar actualizar una referencia válida
		 * @depends testCreateValidReference
		 */
		public function testUpdateExistingReference () {
			$object      = FieldModuleReference::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('test_related_module')
				->setSequence (5)
				->setStatus ('known');
			$savedObject = FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($object);
			$this->assertNotNull ($savedObject, 'Reference should not be null');
			$this->assertInstanceOf (FieldModuleReference::class, $savedObject, 'Reference is not an instance of FieldModuleReference');

			// Verificar que no existe la referencia anterior
			$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=? AND relmodule=? AND sequence=? AND status=?', array (1, 'test_module', 'test_related_module', 1, 'unknown'));
			$this->assertEquals (0, self::$adb->num_rows ($result));

			// Verificar que se actualizó la referencia
			$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=? AND relmodule=? AND sequence=? AND status=?', array (1, 'test_module', 'test_related_module', 5, 'known'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
		}

		/**
		 * Intentar eliminar una referencia existente
		 * @depends testUpdateExistingReference
		 */
		public function testDeleteReference () {
			$object = FieldModuleReference::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('test_related_module');
			FieldModuleReferenceManager::getInstance (self::$adb)->deleteReference ($object);

			// Verificar que se actualizó la referencia
			$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=? AND relmodule=? AND sequence=? AND status=?', array (1, 'test_module', 'test_related_module', 5, 'known'));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

		/**
		 * Intentar eliminar múltiples referencias existentes. Se crearán dos referencias para la prueba
		 * @depends testDeleteReference
		 */
		public function testDeleteReferencesByFieldName () {
			$object      = FieldModuleReference::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('test_related_module')
				->setStatus ('unknown');
			$savedObject = FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($object);
			$this->assertNotNull ($savedObject, 'Reference should not be null');
			$this->assertInstanceOf (FieldModuleReference::class, $savedObject, 'Reference is not an instance of FieldModuleReference');

			$anotherObject      = FieldModuleReference::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('test_module')
				->setStatus ('known');
			$anotherSavedObject = FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($anotherObject);
			$this->assertNotNull ($anotherSavedObject, 'Reference should not be null');
			$this->assertInstanceOf (FieldModuleReference::class, $anotherSavedObject, 'Reference is not an instance of FieldModuleReference');

			// Verificar que se crearon correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=?', array (1, 'test_module'));
			$this->assertEquals (2, self::$adb->num_rows ($result));

			// Eliminar las referencias
			FieldModuleReferenceManager::getInstance (self::$adb)->deleteReferencesByFieldName ('test_module', 'test_field');

			// Verificar que se eliminaron correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=? AND module=?', array (1, 'test_module'));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

		/**
		 * Crear múltiples referencias y validar que se genera adecuadamente el número de secuencia
		 * @depends testDeleteReferencesByFieldName
		 */
		public function testSequenceNumbers () {
			$object      = FieldModuleReference::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('test_related_module')
				->setStatus ('unknown');
			$savedObject = FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($object);
			$this->assertNotNull ($savedObject, 'Reference should not be null');
			$this->assertInstanceOf (FieldModuleReference::class, $savedObject, 'Reference is not an instance of FieldModuleReference');
			$this->assertEquals (1, $object->getSequence (), 'Sequence numbers do not match');

			$anotherObject      = FieldModuleReference::getInstance ()
				->setFieldName ('test_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('test_module')
				->setStatus ('known');
			$anotherSavedObject = FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($anotherObject);
			$this->assertNotNull ($anotherSavedObject, 'Reference should not be null');
			$this->assertInstanceOf (FieldModuleReference::class, $anotherSavedObject, 'Reference is not an instance of FieldModuleReference');
			$this->assertEquals (2, $anotherSavedObject->getSequence (), 'Sequence numbers do not match');

			$aThirdObject      = FieldModuleReference::getInstance ()
				->setFieldName ('test_related_field')
				->setModuleName ('test_related_module')
				->setReferencedModuleName ('test_module')
				->setStatus ('who_knows');
			$aThirdSavedObject = FieldModuleReferenceManager::getInstance (self::$adb)->saveReference ($aThirdObject);
			$this->assertNotNull ($aThirdSavedObject, 'Reference should not be null');
			$this->assertInstanceOf (FieldModuleReference::class, $aThirdSavedObject, 'Reference is not an instance of FieldModuleReference');
			$this->assertEquals (1, $aThirdSavedObject->getSequence (), 'Sequence numbers do not match');
		}

		/**
		 * Obtener las referencias de la base de datos
		 * @depends testSequenceNumbers
		 */
		public function testFetchExistingReferences () {
			$references = FieldModuleReferenceManager::getInstance (self::$adb)->fetchReferences ('test_module', 'test_field');
			$this->assertNotNull ($references, 'References should not be null');
			$this->assertEquals (2, count ($references), 'References count do not match');
			foreach ($references as $index => $reference) {
				$this->assertNotNull ($reference, 'Reference should not be null');
				$this->assertInstanceOf (FieldModuleReference::class, $reference, 'Reference is not an instance of FieldModuleReference');
				$this->assertEquals (($index + 1), $reference->getSequence (), 'Sequence numbers do not match');
				$this->assertEquals ('test_field', $reference->getFieldName (), 'Field names do not match');
				$this->assertEquals ('test_module', $reference->getModuleName (), 'Module names do not match');
				$this->assertTrue (in_array ($reference->getReferencedModuleName (), array ('test_module', 'test_related_module')), 'Referenced module names do not match');
			}

			$references = FieldModuleReferenceManager::getInstance (self::$adb)->fetchReferences ('test_related_module', 'test_related_field');
			$this->assertNotNull ($references, 'References should not be null');
			$this->assertEquals (1, count ($references), 'References count do not match');
			$reference = $references [0];
			$this->assertNotNull ($reference, 'Reference should not be null');
			$this->assertInstanceOf (FieldModuleReference::class, $reference, 'Reference is not an instance of FieldModuleReference');
			$this->assertEquals (1, $reference->getSequence (), 'Sequence numbers do not match');
			$this->assertEquals ('test_related_field', $reference->getFieldName (), 'Field names do not match');
			$this->assertEquals ('test_related_module', $reference->getModuleName (), 'Module names do not match');
			$this->assertEquals ('test_module', $reference->getReferencedModuleName (), 'Referenced module names do not match');
		}

		/**
		 * Intentar obtener referencias no existentes de la base de datos
		 * @depends testSequenceNumbers
		 */
		public function testFetchNonExistingReferences () {
			$references = FieldModuleReferenceManager::getInstance (self::$adb)->fetchReferences ('test_module', 'test_related_field');
			$this->assertNull ($references, 'References should be null');
		}

	}
	// @codingStandardsIgnoreEnd

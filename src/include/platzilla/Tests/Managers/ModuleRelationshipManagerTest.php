<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/ModuleRelationshipManager.php');

	/**
	 * Prueba funcional de la clase ModuleRelationshipManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ModuleRelationshipManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_relatedlists, vtiger_relatedlists_seq
		 * 4. Simular existencia de dos módulos
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
					`locked` TINYINT(1) NULL DEFAULT '0',
					PRIMARY KEY (`relation_id`),
					INDEX `relatedlists_relation_id_idx` (`relation_id`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE `vtiger_relatedlists_seq` (
					`id` INT(11) NOT NULL,
					PRIMARY KEY (`id`)
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
			self::$adb->query ("INSERT INTO `vtiger_relatedlists_seq` (id) VALUES (0)");
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
		 * Intentar crear una relación sin la información mínima necesaria
		 * Debe arrojar una ModuleRelationshipException
		 */
		public function testCreateIncompleteRelationship () {
			$object = ModuleRelationship::getInstance ();
			$this->expectException (ModuleRelationshipException::class);
			ModuleRelationshipManager::getInstance (self::$adb)->saveRelationship ($object);
		}

		/**
		 * Intentar crear una relación de un módulo no existente
		 * Debe arrojar una ModuleRelationshipException
		 */
		public function testCreateNonExistingModuleRelationship () {
			$object = ModuleRelationship::getInstance ()
				->setActions (array (ModuleRelationshipInterface::ACTION_ADD, ModuleRelationshipInterface::ACTION_SELECT))
				->setFunction ('get_related_list')
				->setLabel ('My relationship')
				->setModuleName ('unknown_module')
				->setRelatedModuleName ('test_related_module')
				->setSequence (1);
			$this->expectException (ModuleRelationshipException::class);
			$this->expectExceptionMessage (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_INVALID_MODULE_NAME);
			ModuleRelationshipManager::getInstance (self::$adb)->saveRelationship ($object);
		}

		/**
		 * Intentar crear una relación de módulo existente a uno no existente
		 * Debe arrojar una ModuleRelationshipException
		 */
		public function testCreateNonExistingRelatedModuleRelationship () {
			$object = ModuleRelationship::getInstance ()
				->setActions (array (ModuleRelationshipInterface::ACTION_ADD, ModuleRelationshipInterface::ACTION_SELECT))
				->setFunction ('get_related_list')
				->setLabel ('My relationship')
				->setModuleName ('test_module')
				->setRelatedModuleName ('unknown_module')
				->setSequence (1);
			$this->expectException (ModuleRelationshipException::class);
			$this->expectExceptionMessage (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_INVALID_RELATED_MODULE_NAME);
			ModuleRelationshipManager::getInstance (self::$adb)->saveRelationship ($object);
		}

		/**
		 * Crear una relación válida
		 */
		public function testCreateValidRelationship () {
			$object      = ModuleRelationship::getInstance ()
				->setActions (array (ModuleRelationshipInterface::ACTION_ADD, ModuleRelationshipInterface::ACTION_SELECT))
				->setFunction ('get_related_list')
				->setLabel ('My relationship')
				->setModuleName ('test_module')
				->setRelatedModuleName ('test_related_module')
				->setSequence (1);
			$savedObject = ModuleRelationshipManager::getInstance (self::$adb)->saveRelationship ($object);

			// Verificar que el objeto existe
			$this->assertNotNull ($savedObject, 'Saved relationship should not be null');

			// Verificar que la relación fue creada correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?', array (1, 2));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que la relación contiene todos los valores suministrados
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (10, $row ['relation_id'], 'IDs do not match');
			$this->assertEquals (1, $row ['tabid'], 'Module IDs do not match');
			$this->assertEquals (2, $row ['related_tabid'], 'Related module IDs do not match');
			$this->assertEquals ('get_related_list', $row ['name'], 'Functions do not match');
			$this->assertEquals (1, $row ['sequence'], 'Sequences do not match');
			$this->assertEquals ('My relationship', $row ['label'], 'Labels do not match');
			$this->assertEquals (ModuleRelationshipInterface::PRESENCE_VISIBLE, $row ['presence'], 'Presences do not match');
			$this->assertEquals (join (',', array (ModuleRelationshipInterface::ACTION_ADD, ModuleRelationshipInterface::ACTION_SELECT)), $row ['actions'], 'Actions do not match');
		}

		/**
		 * Intentar obtener una relación no existente
		 */
		public function testFetchNonExistingRelationship () {
			$this->assertNull (ModuleRelationshipManager::getInstance (self::$adb)->fetchRelationship ('test_related_module', 'test_module', 'get_related_list'));
		}

		/**
		 * Obtener una relación válida
		 * @depends testCreateValidRelationship
		 */
		public function testFetchExistingRelationship () {
			$object = ModuleRelationshipManager::getInstance (self::$adb)->fetchRelationship ('test_module', 'test_related_module', 'get_related_list');

			// Verificar que el objeto existe
			$this->assertNotNull ($object, 'Relationship should not be null');

			// Verificar que el bloque contiene todos los valores suministrados
			$this->assertEquals ('test_module', $object->getModuleName (), 'Module names do not match');
			$this->assertEquals ('test_related_module', $object->getRelatedModuleName (), 'Related module names do not match');
			$this->assertEquals ('get_related_list', $object->getFunction (), 'Functions do not match');
			$this->assertEquals (1, $object->getSequence (), 'Sequences do not match');
			$this->assertEquals ('My relationship', $object->getLabel (), 'Labels do not match');
			$this->assertEquals (ModuleRelationshipInterface::PRESENCE_VISIBLE, $object->getPresence (), 'Presences do not match');
			$this->assertEquals (array (ModuleRelationshipInterface::ACTION_ADD, ModuleRelationshipInterface::ACTION_SELECT), $object->getActions (), 'Actions do not match');
		}

		/**
		 * Obtener las relaciones existentes para un módulo existente. Agregar una previamente
		 * @depends testFetchExistingRelationship
		 */
		public function testFetchExistingRelationships () {
			ModuleRelationshipManager::getInstance (self::$adb)->saveRelationship (
				ModuleRelationship::getInstance ()
					->setActions (array (ModuleRelationshipInterface::ACTION_SELECT))
					->setFunction ('get_second_list')
					->setLabel ('My second relationship')
					->setModuleName ('test_module')
					->setPresence (ModuleRelationshipInterface::PRESENCE_HIDDEN)
					->setRelatedModuleName ('test_module')
					->setSequence (2)
			);

			// Obtener las relaciones
			$objects = ModuleRelationshipManager::getInstance (self::$adb)->fetchRelationships ('test_module');

			// Verificar que se obtienen las relaciones
			$this->assertNotNull ($objects, 'Relationships should not be null');
			$this->assertCount (2, $objects, 'Relationships count do not match');
		}

		/**
		 * Modificar una relación existente
		 */
		public function testUpdateRelationship () {
			$object = ModuleRelationship::getInstance ()
				->setActions (array (ModuleRelationshipInterface::ACTION_ADD))
				->setFunction ('get_related_list')
				->setLabel ('My first relationship')
				->setModuleName ('test_module')
				->setPresence (ModuleRelationshipInterface::PRESENCE_HIDDEN)
				->setRelatedModuleName ('test_related_module')
				->setSequence (15);
			$savedObject = ModuleRelationshipManager::getInstance (self::$adb)->saveRelationship ($object);

			// Verificar que el objeto existe
			$this->assertNotNull ($savedObject, 'Saved relationship should not be null');

			// Verificar que se mantiene el mismo número de relaciones
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE tabid=?', array (1));
			$this->assertEquals (2, self::$adb->num_rows ($result));

			// Verificar que la relación fue actualizada correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?', array (1, 2));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que la relación contiene todos los valores suministrados
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (10, $row ['relation_id'], 'IDs do not match');
			$this->assertEquals (1, $row ['tabid'], 'Module IDs do not match');
			$this->assertEquals (2, $row ['related_tabid'], 'Related module IDs do not match');
			$this->assertEquals ('get_related_list', $row ['name'], 'Functions do not match');
		}

		/**
		 * Eliminar una relación
		 * @depends testFetchExistingRelationships
		 */
		public function testDeleteRelationship () {
			$object = ModuleRelationship::getInstance ()
				->setActions (array (ModuleRelationshipInterface::ACTION_ADD))
				->setFunction ('get_related_list')
				->setLabel ('My first relationship')
				->setModuleName ('test_module')
				->setPresence (ModuleRelationshipInterface::PRESENCE_HIDDEN)
				->setRelatedModuleName ('test_related_module')
				->setSequence (15);
			ModuleRelationshipManager::getInstance (self::$adb)->deleteRelationship ($object);

			// Verificar que la relación fue eliminada correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE tabid=?', array (1));
			$this->assertEquals (1, self::$adb->num_rows ($result));
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE relation_id=?', array (10));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

		/**
		 * Eliminar todas las relaciones asociadas del módulo
		 * @depends testDeleteRelationship
		 */
		public function testDeleteRelationships () {
			ModuleRelationshipManager::getInstance (self::$adb)->deleteRelationships ('test_module');

			// Verificar que se eliminaron las relaciones
			$result = self::$adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE tabid=?', array (1));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

	}
	// @codingStandardsIgnoreEnd

<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/FieldDependencyManager.php');

	/**
	 * Prueba funcional de la clase FieldDependencyManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class FieldDependencyManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_field, vtiger_picklist
		 * 4. Crear tabla de un picklist, vtiger_tipo_de_entidad
		 * 5. Simular existencia de un módulo, un campo picklist y dos campos no picklist
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
				"CREATE TABLE IF NOT EXISTS `vtiger_role` (
					`roleid` varchar(255) NOT NULL,
					`rolename` varchar(200) DEFAULT NULL,
					`parentrole` varchar(255) DEFAULT NULL,
					`depth` int(19) DEFAULT NULL,
					`iscustomer` int(11) DEFAULT NULL,
					`ispartner` int(11) DEFAULT NULL,
					`default_module` varchar(255) DEFAULT NULL,
					PRIMARY KEY (`roleid`)
				) ENGINE=InnoDB"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_role2picklist` (
					`roleid` varchar(255) NOT NULL,
					`picklistvalueid` int(11) NOT NULL,
					`picklistid` int(11) NOT NULL,
					`sortid` int(11) DEFAULT NULL,
					PRIMARY KEY (`roleid`,`picklistvalueid`,`picklistid`),
					KEY `role2picklist_roleid_picklistid_idx` (`roleid`,`picklistid`,`picklistvalueid`),
					KEY `fk_2_vtiger_role2picklist` (`picklistid`),
					CONSTRAINT `fk_1_vtiger_role2picklist` FOREIGN KEY (`roleid`) REFERENCES `vtiger_role` (`roleid`) ON DELETE CASCADE,
					CONSTRAINT `fk_2_vtiger_role2picklist` FOREIGN KEY (`picklistid`) REFERENCES `vtiger_picklist` (`picklistid`) ON DELETE CASCADE
				) ENGINE=InnoDB"
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
				"CREATE TABLE IF NOT EXISTS `vtiger_picklist_test` (
				  `picklist_testid` INT(11) NOT NULL AUTO_INCREMENT,
				  `picklist_test` VARCHAR(200) NOT NULL,
				  `presence` INT(1) NOT NULL DEFAULT '1',
				  `picklist_valueid` INT(11) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`picklist_testid`)
				) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8"
			);

			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 1, 'cod_test_module', 'vtiger_test_module', 1, '4', 'cod_test_module', 'Código', 1, 2, '', 100, 1, 4146, 1, 'V~O~LE~100', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 2, 'test_name', 'vtiger_test_module', 1, '1', 'test_name', 'Nombre', 1, 2, '', 100, 2, 4146, 1, 'V~M~LE~255', 2, 2, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 3, 'picklist_test', 'vtiger_test_module', 1, '15', 'picklist_test', 'Picklist', 1, 2, '', 100, 4, 4146, 1, 'V~M', 2, 4, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_picklist` (`picklistid`, `name`) VALUES (1, 'picklist_test')");
			/** @noinspection SqlResolve */
			self::$adb->query ("INSERT INTO `vtiger_picklist_test` (`picklist_testid`, `picklist_test`, `presence`, `picklist_valueid`) VALUES (10, '-', 1, 1)");
			/** @noinspection SqlResolve */
			self::$adb->query ("INSERT INTO `vtiger_picklist_test` (`picklist_testid`, `picklist_test`, `presence`, `picklist_valueid`) VALUES (20, 'Media Difusor', 1, 2)");
			/** @noinspection SqlResolve */
			self::$adb->query ("INSERT INTO `vtiger_picklist_test` (`picklist_testid`, `picklist_test`, `presence`, `picklist_valueid`) VALUES (30, 'Medio', 1, 3)");
			/** @noinspection SqlResolve */
			self::$adb->query ("INSERT INTO `vtiger_picklist_test` (`picklist_testid`, `picklist_test`, `presence`, `picklist_valueid`) VALUES (40, 'Productora', 1, 4)");
			/** @noinspection SqlResolve */
			self::$adb->query ("INSERT INTO `vtiger_picklist_test` (`picklist_testid`, `picklist_test`, `presence`, `picklist_valueid`) VALUES (50, 'Programa', 1, 5)");
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
		 * Intentar crear una dependencia incompleta
		 * Debe arrojar una excepción FieldDependencyException
		 */
		public function testCreateIncompleteDependency () {
			$dependency = FieldDependency::getInstance ()
				->setModuleName ('test_module')
				->setSourceFieldName ('cod_test_module');
			$this->expectException (FieldDependencyException::class);
			FieldDependencyManager::getInstance (self::$adb)->saveDependency ($dependency);
		}

		/**
		 * Intentar crear una dependencia sobre un módulo no existente
		 */
		public function testCreateNonExistingModuleFieldDependency () {
			$dependency = FieldDependency::getInstance ()
				->setModuleName ('non_existing_module')
				->setSourceFieldName ('picklist_test')
				->setSourceFieldValue (null)
				->setTargetFieldName ('cod_test_module')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_HIDDEN);
			$this->expectException (FieldDependencyException::class);
			$this->expectExceptionMessage (FieldDependencyException::ERROR_FIELD_DEPENDENCY_INVALID_MODULE_NAME);
			FieldDependencyManager::getInstance (self::$adb)->saveDependency ($dependency);
		}

		/**
		 * Intentar crear una dependencia sobre un campo de origen no existente
		 */
		public function testCreateNonExistingSourceFieldDependency () {
			$dependency = FieldDependency::getInstance ()
				->setModuleName ('test_module')
				->setSourceFieldName ('non_existing_field')
				->setSourceFieldValue (null)
				->setTargetFieldName ('cod_test_module')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_HIDDEN);
			$this->expectException (FieldDependencyException::class);
			$this->expectExceptionMessage (FieldDependencyException::ERROR_FIELD_DEPENDENCY_INVALID_SOURCE_FIELD_NAME);
			FieldDependencyManager::getInstance (self::$adb)->saveDependency ($dependency);
		}

		/**
		 * Intentar crear una dependencia sobre un campo destino no existente
		 */
		public function testCreateNonExistingTargetFieldDependency () {
			$dependency = FieldDependency::getInstance ()
				->setModuleName ('test_module')
				->setSourceFieldName ('picklist_test')
				->setSourceFieldValue (null)
				->setTargetFieldName ('non_existing_field')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_HIDDEN);
			$this->expectException (FieldDependencyException::class);
			$this->expectExceptionMessage (FieldDependencyException::ERROR_FIELD_DEPENDENCY_INVALID_TARGET_FIELD_NAME);
			FieldDependencyManager::getInstance (self::$adb)->saveDependency ($dependency);
		}

		/**
		 * Intentar crear una dependencia sobre un campo que no es picklist
		 */
		public function testCreateNonExistingPicklistFieldDependency () {
			$dependency = FieldDependency::getInstance ()
				->setModuleName ('test_module')
				->setSourceFieldName ('cod_test_module')
				->setSourceFieldValue (null)
				->setTargetFieldName ('cod_test_module')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_HIDDEN);
			$this->expectException (FieldDependencyException::class);
			$this->expectExceptionMessage (FieldDependencyException::ERROR_FIELD_DEPENDENCY_INVALID_SOURCE_FIELD_UITYPE);
			FieldDependencyManager::getInstance (self::$adb)->saveDependency ($dependency);
		}

		/**
		 * Intentar crear una dependencia válida
		 */
		public function testCreateValidDependency () {
			$dependency = FieldDependency::getInstance ()
				->setModuleName ('test_module')
				->setSourceFieldName ('picklist_test')
				->setSourceFieldValue (null)
				->setTargetFieldName ('cod_test_module')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_HIDDEN);
			$savedDependency = FieldDependencyManager::getInstance (self::$adb)->saveDependency ($dependency);
			$this->assertNotNull ($savedDependency, 'Dependency should not be null');

			// Verificar que se creó la dependencia en base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE field=?', array (1));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que la dependencia tiene los valores correctos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($row ['parentfield'], 0, 'Dependency values do not match');
			$this->assertEquals ($row ['nameparent'], 'picklist_test', 'Dependency source field names do not match');
			$this->assertEquals ($row ['field'], 1, 'Dependency target fields do not match');
			$this->assertEquals ($row ['visible'], FieldDependencyInterface::VISIBILITY_HIDDEN, 'Dependency visibilities fields do not match');
		}

		/**
		 * Actualizar una dependencia existente
		 *
		 * @depends testCreateValidDependency
		 */
		public function testUpdateExistingDependency () {
			$dependency      = FieldDependency::getInstance ()
				->setModuleName ('test_module')
				->setSourceFieldName ('picklist_test')
				->setSourceFieldValue (null)
				->setTargetFieldName ('cod_test_module')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_VISIBLE);
			$savedDependency = FieldDependencyManager::getInstance (self::$adb)->saveDependency ($dependency);
			$this->assertNotNull ($savedDependency, 'Dependency should not be null');

			// Verificar que se creó la dependencia en base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE field=?', array (1));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que la dependencia tiene los valores correctos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($row ['parentfield'], 0, 'Dependency values do not match');
			$this->assertEquals ($row ['nameparent'], 'picklist_test', 'Dependency source field names do not match');
			$this->assertEquals ($row ['field'], 1, 'Dependency target fields do not match');
			$this->assertEquals ($row ['visible'], FieldDependencyInterface::VISIBILITY_VISIBLE, 'Dependency visibilities fields do not match');
		}

		/**
		 * Obtener una dependencia existente
		 *
		 * @depends testUpdateExistingDependency
		 */
		public function testFetchExistingDependencies () {
			$dependencies = FieldDependencyManager::getInstance (self::$adb)->fetchDependenciesBySourceFieldName ('test_module', 'picklist_test');
			$this->assertCount (1, $dependencies, 'Dependencies count does not match');
			$this->assertInstanceOf (FieldDependency::class, $dependencies [0]);
		}

		/**
		 * Eliminar una dependencia existente según el campo destino
		 *
		 * @depends testUpdateExistingDependency
		 */
		public function testDeleteExistingDependenciesByTargetField () {
			$dependency      = FieldDependency::getInstance ()
				->setModuleName ('test_module')
				->setSourceFieldName ('picklist_test')
				->setSourceFieldValue (null)
				->setTargetFieldName ('test_name')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_VISIBLE);
			$savedDependency = FieldDependencyManager::getInstance (self::$adb)->saveDependency ($dependency);
			$this->assertNotNull ($savedDependency, 'Dependency should not be null');

			// Verificar que existen dos dependencias para el mismo campo de origen
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE nameparent=?', array ('picklist_test'));
			$this->assertEquals (2, self::$adb->num_rows ($result));

			// Verificar que se creó la dependencia en base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE field=?', array (2));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Eliminar la dependencia recién creada
			FieldDependencyManager::getInstance (self::$adb)->deleteDependenciesByTargetFieldName ('test_module', 'test_name');

			// Verificar que existe una sola dependencia para el mismo campo de origen
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE nameparent=?', array ('picklist_test'));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que se eliminó la dependencia en base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE field=?', array (2));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

		/**
		 * Eliminar una dependencia existente según el campo de origen
		 *
		 * @depends testDeleteExistingDependenciesByTargetField
		 */
		public function testDeleteExistingDependenciesBySourceField () {
			$dependency      = FieldDependency::getInstance ()
				->setModuleName ('test_module')
				->setSourceFieldName ('picklist_test')
				->setSourceFieldValue (null)
				->setTargetFieldName ('test_name')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_VISIBLE);
			$savedDependency = FieldDependencyManager::getInstance (self::$adb)->saveDependency ($dependency);
			$this->assertNotNull ($savedDependency, 'Dependency should not be null');

			// Verificar que existen dos dependencias para el mismo campo de origen
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE nameparent=?', array ('picklist_test'));
			$this->assertEquals (2, self::$adb->num_rows ($result));

			// Verificar que se creó la dependencia en base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE field=?', array (2));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Eliminar las dependencias asociadas al campo picklist_test
			FieldDependencyManager::getInstance (self::$adb)->deleteDependenciesBySourceFieldName ('test_module', 'picklist_test');

			// Verificar que no existe ninguna dependencia para el mismo campo de origen
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE nameparent=?', array ('picklist_test'));
			$this->assertEquals (0, self::$adb->num_rows ($result));

			// Verificar que se eliminó la dependencia en base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE field=?', array (2));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

		/**
		 * Eliminar una dependencia existente
		 */
		public function testDeleteExistingDependency () {
			$dependency      = FieldDependency::getInstance ()
				->setModuleName ('test_module')
				->setSourceFieldName ('picklist_test')
				->setSourceFieldValue ('Media Difusor')
				->setTargetFieldName ('test_name')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_VISIBLE);
			$savedDependency = FieldDependencyManager::getInstance (self::$adb)->saveDependency ($dependency);
			$this->assertNotNull ($savedDependency, 'Dependency should not be null');

			// Verificar que se creó la dependencia en base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE field=? AND nameparent=?', array (2, 'picklist_test'));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Eliminar las dependencias asociadas al campo picklist_test
			FieldDependencyManager::getInstance (self::$adb)->deleteDependency ($savedDependency);

			// Verificar que no existe ninguna dependencia para el mismo campo de origen
			$result = self::$adb->pquery ('SELECT * FROM vtiger_field_dependency WHERE field=? AND nameparent=?', array (2, 'picklist_test'));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

		/**
		 * Eliminar una dependencia no existente según el campo destino
		 */
		public function testDeleteNonExistingDependenciesByTargetField () {
			$result = self::$adb->query ('SELECT * FROM vtiger_field_dependency');
			$totalDependencies = self::$adb->num_rows ($result);

			// Intentar eliminar una dependencia que no existe
			FieldDependencyManager::getInstance (self::$adb)->deleteDependenciesBySourceFieldName ('test_module', 'non_existing_field');

			// Verificar que existe la misma cantidad de dependencias
			$result = self::$adb->query ('SELECT * FROM vtiger_field_dependency');
			$this->assertEquals ($totalDependencies, self::$adb->num_rows ($result));

			// Intentar eliminar otra dependencia que no existe
			FieldDependencyManager::getInstance (self::$adb)->deleteDependenciesBySourceFieldName ('non_existing_module', 'picklist_test');

			// Verificar que existe la misma cantidad de dependencias
			$result = self::$adb->query ('SELECT * FROM vtiger_field_dependency');
			$this->assertEquals ($totalDependencies, self::$adb->num_rows ($result));

			// Intentar eliminar una dependencia que no existe
			FieldDependencyManager::getInstance (self::$adb)->deleteDependenciesByTargetFieldName ('test_module', 'non_existing_field');

			// Verificar que existe la misma cantidad de dependencias
			$result = self::$adb->query ('SELECT * FROM vtiger_field_dependency');
			$this->assertEquals ($totalDependencies, self::$adb->num_rows ($result));

			// Intentar eliminar otra dependencia que no existe
			FieldDependencyManager::getInstance (self::$adb)->deleteDependenciesByTargetFieldName ('non_existing_module', 'picklist_test');

			// Verificar que existe la misma cantidad de dependencias
			$result = self::$adb->query ('SELECT * FROM vtiger_field_dependency');
			$this->assertEquals ($totalDependencies, self::$adb->num_rows ($result));
		}

		/**
		 * Intentar obtener dependencias de campos o módulos no existentes
		 */
		public function testFetchNonExistingDependencies () {
			$this->assertNull (FieldDependencyManager::getInstance (self::$adb)->fetchDependenciesBySourceFieldName ('test_module', 'non_existing_field'), 'Dependencies should be null');
			$this->assertNull (FieldDependencyManager::getInstance (self::$adb)->fetchDependenciesBySourceFieldName ('non_existing_module', 'non_existing_field'), 'Dependencies should be null');
		}
	}
	// @codingStandardsIgnoreEnd

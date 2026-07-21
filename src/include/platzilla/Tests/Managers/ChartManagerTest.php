<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/ChartManager.php');

	/**
	 * Prueba funcional de la clase ChartManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ChartManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_field, vtiger_graficos
		 * 4. Simular existencia de un módulo y dos campos
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

			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 1, 'test_date_field', 'vtiger_test_module', 1, '5', 'test_date_field', 'Test date field', 1, -1, '', 100, 2, -1, 1, 'D~O', 1, NULL, 'BAS', 1, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 2, 'test_text_field', 'vtiger_test_module', 1, '1', 'test_text_field', 'Test text field', 1, -1, '', 100, 2, -1, 1, 'V~O', 1, NULL, 'BAS', 1, '', NULL)");
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
		 * Intentar crear un gráfico sin la información mínima necesaria
		 * Debe arrojar una ChartException
		 */
		public function testCreateIncompleteChart () {
			$object = Chart::getInstance ();
			$this->expectException (ChartException::class);
			ChartManager::getInstance (self::$adb)->saveChart ($object);
		}

		/**
		 * Intentar crear un gráfico asociado a un nombre de módulo no existente
		 * Debe arrojar una ChartException
		 */
		public function testCreateNonExistingModuleNameChart () {
			$object = Chart::getInstance ()
				->setFieldName ('test_text_field')
				->setGroupBy ('test_date_field')
				->setModuleName ('unknown_module')
				->setOperation (ChartInterface::OPERATION_AVERAGE)
				->setTitle ('My test chart')
				->setType (ChartInterface::TYPE_BARS);
			$this->expectException (ChartException::class);
			$this->expectExceptionMessage (ChartException::ERROR_CHART_INVALID_MODULE_NAME);
			ChartManager::getInstance (self::$adb)->saveChart ($object);
		}

		/**
		 * Intentar crear un gráfico asociado a un nombre de campo no existente
		 * Debe arrojar una ChartException
		 */
		public function testCreateNonExistingFieldNameChart () {
			$object = Chart::getInstance ()
				->setFieldName ('unknown_field')
				->setGroupBy ('test_date_field')
				->setModuleName ('test_module')
				->setOperation (ChartInterface::OPERATION_AVERAGE)
				->setTitle ('My test chart')
				->setType (ChartInterface::TYPE_BARS);
			$this->expectException (ChartException::class);
			$this->expectExceptionMessage (ChartException::ERROR_CHART_INVALID_FIELD_NAME);
			ChartManager::getInstance (self::$adb)->saveChart ($object);
		}

		/**
		 * Intentar crear un gráfico con un groupby no existente
		 * Debe arrojar una ChartException
		 */
		public function testCreateNonExistingGroupByChart () {
			$object = Chart::getInstance ()
				->setFieldName ('test_text_field')
				->setGroupBy ('unknown_field')
				->setModuleName ('test_module')
				->setOperation (ChartInterface::OPERATION_AVERAGE)
				->setTitle ('My test chart')
				->setType (ChartInterface::TYPE_BARS);
			$this->expectException (ChartException::class);
			$this->expectExceptionMessage (ChartException::ERROR_CHART_INVALID_GROUP_BY);
			ChartManager::getInstance (self::$adb)->saveChart ($object);
		}

		/**
		 * Crear un gráfico válido
		 */
		public function testCreateValidChart () {
			$applicationCodes = array ('application_one', 'application_two', 'application_three');
			$fieldName      = 'test_text_field';
			$groupBy        = 'test_date_field';
			$moduleName     = 'test_module';
			/** @noinspection SqlResolve */
			$sqlQuery  = 'SELECT * FROM vtiger_test_module';
			$title     = 'My test chart';
			$variables = json_encode (array ('a' => 1, 'b' => 2));

			$object      = Chart::getInstance ()
				->setAdvanced (ChartInterface::ADVANCED_NO)
				->setApplicationCodes ($applicationCodes)
				->setCompare (false)
				->setDateGrouping (ChartInterface::DATE_GROUPING_ANNUAL)
				->setFieldName ($fieldName)
				->setGroupBy ($groupBy)
				->setModuleName ($moduleName)
				->setOperation (ChartInterface::OPERATION_AVERAGE)
				->setSqlQuery ($sqlQuery)
				->setTitle ($title)
				->setType (ChartInterface::TYPE_BARS)
				->setVariables ($variables);
			$savedObject = ChartManager::getInstance (self::$adb)->saveChart ($object);

			// Verificar que el objeto existe y tiene ID
			$this->assertNotNull ($savedObject, 'Saved chart should not be null');
			$this->assertNotEmpty ($savedObject->getId (), 'Saved chart ID should not be null');

			// Verificar que el gráfico fue creado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=? AND title=?', array ($moduleName, $title));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que el gráfico contiene todos los valores suministrados
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['fld_module'], 'Module names do not match');
			$this->assertEquals ($groupBy, $row ['fieldgrouping'], 'Group by properties do not match');
			$this->assertEquals ($fieldName, $row ['fieldoperation'], 'Field names do not match');
			$this->assertEquals (ChartInterface::OPERATION_AVERAGE, $row ['operation'], 'Operations do not match');
			$this->assertEquals (ChartInterface::TYPE_BARS, $row ['tipografico'], 'Types do not match');
			$this->assertEquals (ChartInterface::DATE_GROUPING_ANNUAL, $row ['dategrouping'], 'Date grouping properties do not match');
			$this->assertEquals ($title, $row ['title'], 'Titles do not match');
			$this->assertEquals (json_encode ($applicationCodes), $row ['applicationcodes'], 'Application Ids do not match');
			$this->assertEquals ($sqlQuery, $row ['sqlprimarioreporte'], 'SQL queries do not match');
			$this->assertEquals ($variables, $row ['varreporte'], 'Variables do not match');
			$this->assertEquals (0, $row ['reporteavanzado'], 'Advanced properties do not match');
			$this->assertEquals (0, $row ['comparar'], 'Compare properties do not match');
		}

		/**
		 * Intentar obtener un gráfico no existente
		 */
		public function testFetchNonExistingChart () {
			$this->assertNull (ChartManager::getInstance (self::$adb)->fetchChart (155));
		}

		/**
		 * Obtener un gráfico existente
		 * @depends testCreateValidChart
		 */
		public function testFetchExistingChart () {
			$object = ChartManager::getInstance (self::$adb)->fetchChart (1);

			// Verificar que el objeto existe y tiene ID
			$this->assertNotNull ($object, 'Chart should not be null');
			$this->assertNotEmpty ($object->getId (), 'Chart ID should not be null');

			// Verificar que el gráfico contiene todos los valores suministrados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE graficoid=?', array (1));
			$row    = self::$adb->fetchByAssoc ($result, -1, false);

			$this->assertEquals ($row ['fld_module'], $object->getModuleName (), 'Module names do not match');
			$this->assertEquals ($row ['fieldgrouping'], $object->getGroupBy (), 'Group by properties do not match');
			$this->assertEquals ($row ['fieldoperation'], $object->getFieldName (), 'Field names do not match');
			$this->assertEquals ($row ['operation'], $object->getOperation (), 'Operations do not match');
			$this->assertEquals ($row ['tipografico'], $object->getType (), 'Types do not match');
			$this->assertEquals ($row ['dategrouping'], $object->getDateGrouping (), 'Date grouping properties do not match');
			$this->assertEquals ($row ['title'], $object->getTitle (), 'Titles do not match');
			$this->assertEquals ($row ['applicationcodes'], json_encode ($object->getApplicationCodes ()), 'Application codes do not match');
			$this->assertEquals ($row ['sqlprimarioreporte'], $object->getSqlQuery (), 'SQL queries do not match');
			$this->assertEquals ($row ['varreporte'], $object->getVariables (), 'Variables do not match');
			$this->assertEquals ($row ['reporteavanzado'], $object->getAdvanced (), 'Advanced properties do not match');
			$this->assertEquals ($row ['comparar'] == 1 ? true : false, $object->getCompare (), 'Compare properties do not match');
		}

		/**
		 * Actualizar un gráfico existente
		 * @depends testFetchExistingChart
		 */
		public function testUpdateExistingChart () {
			$applicationCodes = array ('application_four', 'application_five');
			$fieldName      = 'test_date_field';
			$moduleName     = 'test_module';
			$title          = 'My test chart # 2';

			$object      = Chart::getInstance ()
				->setId (1)
				->setAdvanced (ChartInterface::ADVANCED_YES)
				->setApplicationCodes ($applicationCodes)
				->setCompare (true)
				->setDateGrouping (null)
				->setFieldName ($fieldName)
				->setGroupBy (null)
				->setModuleName ($moduleName)
				->setOperation (ChartInterface::OPERATION_COUNT)
				->setSqlQuery (null)
				->setTitle ($title)
				->setType (ChartInterface::TYPE_POINTS)
				->setVariables (null);
			$savedObject = ChartManager::getInstance (self::$adb)->saveChart ($object);

			// Verificar que el objeto existe y tiene ID
			$this->assertNotNull ($savedObject, 'Saved chart should not be null');
			$this->assertNotEmpty ($savedObject->getId (), 'Saved chart ID should not be null');

			// Verificar que el gráfico fue creado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=? AND title=?', array ($moduleName, $title));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que el gráfico contiene todos los valores suministrados
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['fld_module'], 'Module names do not match');
			$this->assertEquals (null, $row ['fieldgrouping'], 'Group by properties do not match');
			$this->assertEquals ($fieldName, $row ['fieldoperation'], 'Field names do not match');
			$this->assertEquals (ChartInterface::OPERATION_COUNT, $row ['operation'], 'Operations do not match');
			$this->assertEquals (ChartInterface::TYPE_POINTS, $row ['tipografico'], 'Types do not match');
			$this->assertEquals (null, $row ['dategrouping'], 'Date grouping properties do not match');
			$this->assertEquals ($title, $row ['title'], 'Titles do not match');
			$this->assertEquals (json_encode ($applicationCodes), $row ['applicationcodes'], 'Application Ids do not match');
			$this->assertEquals (null, $row ['sqlprimarioreporte'], 'SQL queries do not match');
			$this->assertEquals (null, $row ['varreporte'], 'Variables do not match');
			$this->assertEquals (1, $row ['reporteavanzado'], 'Advanced properties do not match');
			$this->assertEquals (1, $row ['comparar'], 'Compare properties do not match');
		}

		/**
		 * Obtener los gráficos existentes para un módulo existente. Agregar dos gráficos más previamente
		 * @depends testUpdateExistingChart
		 */
		public function testFetchExistingCharts () {
			$moduleName = 'test_module';
			ChartManager::getInstance (self::$adb)->saveChart (
				Chart::getInstance ()
					->setFieldName ('test_date_field')
					->setModuleName ($moduleName)
					->setOperation (ChartInterface::OPERATION_COUNT)
					->setTitle ('Another chart')
					->setType (ChartInterface::TYPE_BARS)
			);
			ChartManager::getInstance (self::$adb)->saveChart (
				Chart::getInstance ()
					->setFieldName ('test_text_field')
					->setModuleName ($moduleName)
					->setOperation (ChartInterface::OPERATION_COUNT)
					->setTitle ('A third chart')
					->setType (ChartInterface::TYPE_PIE)
			);

			// Obtener los gráficos
			$objects = ChartManager::getInstance (self::$adb)->fetchCharts ($moduleName);

			// Verificar que el objeto existe y tiene ID
			$this->assertNotNull ($objects, 'Charts should not be null');
			$this->assertCount (3, $objects, 'Charts count do not match');
		}

		/**
		 * Eliminar un gráfico existente
		 * @depends testFetchExistingCharts
		 */
		public function testDeleteChart () {
			$object = Chart::getInstance ()
				->setId (1);
			ChartManager::getInstance (self::$adb)->deleteChart ($object);

			// Verificar que el gráfico fue eliminado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE graficoid=?', array (1));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

		/**
		 * Crear nuevos gráficos para el módulo. Deben eliminarse los existentes excepto el segundo
		 * @depends testDeleteChart
		 */
		public function testSaveModuleCharts () {
			$moduleName = 'test_module';

			// Verificar que aun quedan registrados dos gráficos para ese módulo
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=?', array ($moduleName));
			$this->assertEquals (2, self::$adb->num_rows ($result));

			// Actualizar los gráficos del módulo
			$charts = array (
				Chart::getInstance ()
					->setAdvanced (ChartInterface::ADVANCED_NO)
					->setApplicationCodes (array ('application_one', 'application_two', 'application_three'))
					->setCompare (false)
					->setDateGrouping (ChartInterface::DATE_GROUPING_ANNUAL)
					->setFieldName ('test_text_field')
					->setGroupBy ('test_date_field')
					->setModuleName ($moduleName)
					->setOperation (ChartInterface::OPERATION_AVERAGE)
					->setSqlQuery ('SELECT * FROM information_schema.CHARACTER_SETS')
					->setTitle ('My test chart # 1')
					->setType (ChartInterface::TYPE_BARS)
					->setVariables (json_encode (array ('a' => 1, 'b' => 2))),
				Chart::getInstance ()
					->setFieldName ('test_date_field')
					->setId (2)
					->setModuleName ($moduleName)
					->setOperation (ChartInterface::OPERATION_COUNT)
					->setTitle ('Another chart again')
					->setType (ChartInterface::TYPE_BARS),
				Chart::getInstance ()
					->setFieldName ('test_text_field')
					->setModuleName ($moduleName)
					->setOperation (ChartInterface::OPERATION_COUNT)
					->setTitle ('My test chart # 3')
					->setType (ChartInterface::TYPE_PIE),
			);
			ChartManager::getInstance (self::$adb)->saveCharts ($moduleName, $charts);

			// Verificar que se eliminaron los gráficos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=?', array ($moduleName));
			$this->assertEquals (3, self::$adb->num_rows ($result));

			// Verificar el primer gráfico
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=? AND title=?', array ($moduleName, 'My test chart # 1'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
			// Verificar que el segundo gráfico existe
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=? AND title=?', array ($moduleName, 'Another chart again'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
			// Verificar que el segundo gráfico es el mismo que estaba inicialmente guardado
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (2, $row ['graficoid']);
			// Verificar el tercer gráfico
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=? AND title=?', array ($moduleName, 'My test chart # 3'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
		}

		/**
		 * Eliminar todos los gráficos del módulo
		 * @depends testSaveModuleCharts
		 */
		public function testDeleteCharts () {
			$moduleName = 'test_module';

			// Verificar que aun quedan registrados dos gráficos para ese módulo
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=?', array ($moduleName));
			$this->assertEquals (3, self::$adb->num_rows ($result));

			// Eliminar los gráficos
			ChartManager::getInstance (self::$adb)->deleteCharts ($moduleName);

			// Verificar que se eliminaron los gráficos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_graficos WHERE fld_module=?', array ($moduleName));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

	}
	// @codingStandardsIgnoreEnd

<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');

	/**
	 * Prueba funcional de la clase DatabaseUtils
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class DatabaseUtilsTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tabla de pruebas: vtiger_test_table
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
				"CREATE TABLE IF NOT EXISTS `vtiger_test_table` (
					`testid` INT(19) NOT NULL,
					PRIMARY KEY (`testid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
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

		public function testCheckIfColumnExists () {
			$this->assertTrue (DatabaseUtils::checkIfColumnExists (self::$adb, 'vtiger_test_table', 'testid'));
			$this->assertFalse (DatabaseUtils::checkIfColumnExists (self::$adb, 'vtiger_test_table', 'unknown_column'));
			$this->assertFalse (DatabaseUtils::checkIfColumnExists (self::$adb, 'vtiger_unknown_table', 'testid'));
		}

		public function testCheckIfTableExists () {
			$this->assertTrue (DatabaseUtils::checkIfTableExists (self::$adb, 'vtiger_test_table'));
			$this->assertFalse (DatabaseUtils::checkIfTableExists (self::$adb, 'vtiger_unknown_table'));
		}

		public function testAddExistingColumn () {
			DatabaseUtils::addColumnIfNotExists (self::$adb, 'vtiger_test_table', 'testid', 'VARCHAR(100)');

			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('testid'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have testid column');

			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('INT(19)', $row ['type'], 'SQL Types do not match', 0.0, 10, false, true);
			$this->assertEquals ('NO', $row ['null'], 'NULL values do not match', 0.0, 10, false, true);
			$this->assertEquals ('PRI', $row ['key'], 'Key not empty', 0.0, 10, false, true);
			$this->assertEmpty ($row ['default'], 'Default not empty');
		}

		public function testAddColumnToNonExistingTable () {
			DatabaseUtils::addColumnIfNotExists (self::$adb, 'vtiger_unknown_table', 'testid', 'VARCHAR(100)');

			$result = self::$adb->pquery ('SHOW TABLES LIKE ?', array ('vtiger_unknown_table'));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'vtiger_unknown_table should not exist');
		}

		public function testAddNonExistingColumn () {
			DatabaseUtils::addColumnIfNotExists (self::$adb, 'vtiger_test_table', 'testname', 'VARCHAR(100)');

			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('testname'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have testname column');

			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('VARCHAR(100)', $row ['type'], 'SQL Types do not match', 0.0, 10, false, true);
			$this->assertEquals ('YES', $row ['null'], 'NULL values do not match', 0.0, 10, false, true);
			$this->assertEmpty ($row ['key'], 'Key not empty');
			$this->assertEmpty ($row ['default'], 'Default not empty');

			DatabaseUtils::addColumnIfNotExists (self::$adb, 'vtiger_test_table', 'testvalue', 'INT(19)', false);

			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('testvalue'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have testvalue column');

			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('INT(19)', $row ['type'], 'SQL Types do not match', 0.0, 10, false, true);
			$this->assertEquals ('NO', $row ['null'], 'NULL values do not match', 0.0, 10, false, true);
			$this->assertEmpty ($row ['key'], 'Key not empty');
			$this->assertEmpty ($row ['default'], 'Default not empty');

			DatabaseUtils::addColumnIfNotExists (self::$adb, 'vtiger_test_table', 'testdatetime', 'DATETIME', false, "'0000-00-00 00:00:00'");

			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('testdatetime'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have testdatetime column');

			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('DATETIME', $row ['type'], 'SQL Types do not match', 0.0, 10, false, true);
			$this->assertEquals ('NO', $row ['null'], 'NULL values do not match', 0.0, 10, false, true);
			$this->assertEmpty ($row ['key'], 'Key not empty');
			$this->assertEquals ('0000-00-00 00:00:00', $row ['default'], 'Default not empty');
		}

		public function testAddInvalidColumn () {
			$this->expectException (DatabaseException::class);
			DatabaseUtils::addColumnIfNotExists (self::$adb, 'vtiger_test_table', 'testdate', 'DATE', false, "'CURDATE()'");
		}

		public function testDeleteNonExistingColumn () {
			$result       = self::$adb->query ('SHOW COLUMNS FROM vtiger_test_table');
			$totalColumns = self::$adb->num_rows ($result);
			$this->assertGreaterThan (0, $totalColumns, 'Table has ho columns');

			DatabaseUtils::deleteColumnIfExists (self::$adb, 'vtiger_test_table', 'unknown_column');
			$result = self::$adb->query ('SHOW COLUMNS FROM vtiger_test_table');
			$this->assertEquals ($totalColumns, self::$adb->num_rows ($result), 'Column totals do not match');

			// Verificar que no produce excepción
			DatabaseUtils::deleteColumnIfExists (self::$adb, 'vtiger_unknown_table', 'unknown_column');
		}

		public function testDeleteExistingColumn () {
			$result       = self::$adb->query ('SHOW COLUMNS FROM vtiger_test_table');
			$totalColumns = self::$adb->num_rows ($result);
			$this->assertGreaterThan (0, $totalColumns, 'vtiger_test_table has ho columns');

			DatabaseUtils::addColumnIfNotExists (self::$adb, 'vtiger_test_table', 'my_new_column', 'VARCHAR(255)');

			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('my_new_column'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have my_new_column column');
			$result = self::$adb->query ('SHOW COLUMNS FROM vtiger_test_table');
			$this->assertEquals (($totalColumns + 1), self::$adb->num_rows ($result), 'vtiger_test_table should have one extra column');

			DatabaseUtils::deleteColumnIfExists (self::$adb, 'vtiger_test_table', 'my_new_column');

			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('my_new_column'));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'vtiger_test_table should not have my_new_column column');
			$result = self::$adb->query ('SHOW COLUMNS FROM vtiger_test_table');
			$this->assertEquals ($totalColumns, self::$adb->num_rows ($result), 'vtiger_test_table total columns do not match');
		}

		public function testUpdateColumn () {
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('my_new_column'));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'vtiger_test_table should not have my_new_column column');

			DatabaseUtils::addColumnIfNotExists (self::$adb, 'vtiger_test_table', 'my_new_column', 'VARCHAR(255)');

			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('my_new_column'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have my_new_column column');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('VARCHAR(255)', strtoupper ($row ['type']), 'SQL data types do not match');
			$this->assertEquals ('YES', strtoupper ($row ['null']), 'Null values do not match');
			$this->assertNull ($row ['default'], 'Defaults do not match');

			DatabaseUtils::updateColumnIfExists (self::$adb, 'vtiger_test_table', 'my_new_column', 'INT(19)');
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('my_new_column'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have my_new_column column');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('INT(19)', strtoupper ($row ['type']), 'SQL data types do not match');
			$this->assertEquals ('YES', strtoupper ($row ['null']), 'Null values do not match');
			$this->assertNull ($row ['default'], 'Defaults do not match');

			DatabaseUtils::updateColumnIfExists (self::$adb, 'vtiger_test_table', 'my_new_column', 'INT(19)', false);
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('my_new_column'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have my_new_column column');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('INT(19)', strtoupper ($row ['type']), 'SQL data types do not match');
			$this->assertEquals ('NO', strtoupper ($row ['null']), 'Null values do not match');
			$this->assertNull ($row ['default'], 'Defaults do not match');

			DatabaseUtils::updateColumnIfExists (self::$adb, 'vtiger_test_table', 'my_new_column', 'INT(19)', false, '');
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('my_new_column'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have my_new_column column');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('INT(19)', strtoupper ($row ['type']), 'SQL data types do not match');
			$this->assertEquals ('NO', strtoupper ($row ['null']), 'Null values do not match');
			$this->assertEquals ('', $row ['default'], 'Defaults do not match');
		}

		public function testDeleteColumns () {
			$result = self::$adb->query ('SHOW COLUMNS FROM vtiger_test_table');
			$this->assertGreaterThan (0, self::$adb->num_rows ($result), 'vtiger_test_table has ho columns');

			DatabaseUtils::deleteColumnIfExists (self::$adb, 'vtiger_test_table', 'my_new_column');

			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('my_new_column'));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'vtiger_test_table should not have my_new_column column');

			DatabaseUtils::addColumnIfNotExists (self::$adb, 'vtiger_test_table', 'my_new_column', 'VARCHAR(255)');

			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('my_new_column'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have my_new_column column');

			DatabaseUtils::deleteColumns (self::$adb, 'vtiger_test_table', array ('my_new_column'));

			$result = self::$adb->query ('SHOW COLUMNS FROM vtiger_test_table');
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table has ho columns');
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_table WHERE Field=?', array ('my_new_column'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'vtiger_test_table should have my_new_column column');
		}

	}
	// @codingStandardsIgnoreEnd

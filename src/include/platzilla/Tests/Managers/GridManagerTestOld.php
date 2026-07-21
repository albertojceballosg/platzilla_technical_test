<?php
	require_once ('include/platzilla/Managers/GridManager.php');

	/**
	 * Prueba funcional de la clase GridManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class GridManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_field, vtiger_subfields
		 * 4. Simular existencia de dos módulos (nativo y para relacionar), un campo grid y un campo no grid (texto)
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
				"CREATE TABLE `vtiger_subfields` (
					`fieldid` INT(11) NOT NULL,
					`name` VARCHAR(255) NOT NULL,
					`label` VARCHAR(255) NOT NULL,
					`sequence` INT(11) NOT NULL,
					`uitype` INT(11) NOT NULL,
					`length` INT(11) NULL DEFAULT NULL,
					`precision` INT(11) NULL DEFAULT NULL,
					`defaultvalue` VARCHAR(255) NULL DEFAULT NULL,
					`values` TEXT NULL,
					`relmodule` VARCHAR(32) NULL DEFAULT NULL,
					PRIMARY KEY (`fieldid`, `name`),
					CONSTRAINT `vtiger_subfields_ibfk_1` FOREIGN KEY (`fieldid`) REFERENCES `vtiger_field` (`fieldid`) ON UPDATE CASCADE ON DELETE CASCADE
				) COLLATE='utf8_general_ci' ENGINE=InnoDB"
			);

			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (2, 'test_related_module', 0, 1, 'Test related module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 1, 'test_grid_field', 'vtiger_test_module', 1, '2202', 'test_grid_field', 'Test grid field', 1, -1, '', 100, 2, -1, 1, 'D~O', 1, NULL, 'BAS', 1, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 2, 'test_text_field', 'vtiger_test_module', 1, '1', 'test_text_field', 'Test text field', 1, -1, '', 100, 2, -1, 1, 'V~O', 1, NULL, 'BAS', 1, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (2, 3, 'test_related_field', 'test_related_module', 1, '1', 'test_related_field', 'Test text field', 1, -1, '', 100, 2, -1, 1, 'V~O', 1, NULL, 'BAS', 1, '', NULL)");
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
		 * Verificar que se genera adecuadamente el campo
		 *
		 * @param string $name
		 * @param string $label
		 * @param integer $uiType
		 * @param string $sqlDataType
		 * @param integer $sequence
		 * @param integer $length
		 * @param integer $precision
		 * @param string $defaultValue
		 * @param string $values
		 * @param string $moduleReferenceName
		 */
		private function checkDatabaseField ($name, $label, $uiType, $sqlDataType, $sequence, $length, $precision, $defaultValue, $values, $moduleReferenceName) {
			// Verificar que el campo se genera en la tabla vtiger_subfields
			$result = self::$adb->pquery ('SELECT * FROM vtiger_subfields WHERE fieldid=? AND name=?', array (1, $name));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid field not found');

			// Verificar que las columnas de la tabla vtiger_subfields contienen los valores correctos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($label, $row ['label'], 'Grid field labels do not match');
			$this->assertEquals ($uiType, $row ['uitype'], 'Grid field uitypes do not match');
			$this->assertEquals ($sequence, $row ['sequence'], 'Grid field sequences do not match');
			$this->assertEquals ($length, $row ['length'], 'Grid field lengths do not match');
			$this->assertEquals ($precision, $row ['precision'], 'Grid field precisions do not match');
			$this->assertEquals ($defaultValue, $row ['defaultvalue'], 'Grid field default values do not match');
			$this->assertEquals ($values, $row ['values'], 'Grid field values do not match');
			$this->assertEquals ($moduleReferenceName, $row ['relmodule'], 'Grid field module references do not match');

			// Verificar que en la tabla de valores del grid se genera la columna correctamente
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field WHERE Field=?', array ($name));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid field column not found');
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($sqlDataType, strtoupper ($row ['type']), 'Grid field SQL data types do not match');
		}

		/**
		 * Verifica que el campo contiene los valores esperados
		 *
		 * @param GridField $field
		 * @param string $name
		 * @param string $label
		 * @param integer $uiType
		 * @param string $sqlDataType
		 * @param integer $sequence
		 * @param integer $length
		 * @param integer $precision
		 * @param string $defaultValue
		 * @param string $values
		 * @param string $moduleReferenceName
		 */
		private function checkGridField ($field, $name, $label, $uiType, $sqlDataType, $sequence, $length, $precision, $defaultValue, $values, $moduleReferenceName) {
			$fieldValues = !empty ($field->getValues ()) ? json_encode ($field->getValues ()) : null;
			$this->assertEquals ($defaultValue, $field->getDefaultValue (), 'Grid field default values do not match');
			$this->assertEquals ($label, $field->getLabel (), 'Grid field labels do not match');
			$this->assertEquals ($length, $field->getLength (), 'Grid field lengths do not match');
			$this->assertEquals ($name, $field->getName (), 'Grid field names do not match');
			$this->assertEquals ($precision, $field->getPrecision (), 'Grid field precisions do not match');
			$this->assertEquals ($sequence, $field->getSequence (), 'Grid field sequences do not match');
			$this->assertEquals ($sqlDataType, $field->getSqlDataType (), 'Grid field SQL data types do not match');
			$this->assertEquals ($uiType, $field->getUiType (), 'Grid field ui types do not match');
			$this->assertEquals ($values, $fieldValues, 'Grid field values do not match');

			$moduleReference = $field->getModuleReference ();
			if (!isset ($moduleReferenceName)) {
				$this->assertNull ($moduleReference, 'Grid field module reference should be null');
			} else {
				$this->assertNotNull ($moduleReference, 'Grid field module reference should not be null');
				$this->assertEquals ($name, $moduleReference->getFieldName (), 'Grid field module reference field names do not match');
				$this->assertEquals ($moduleReferenceName, $moduleReference->getReferencedModuleName (), 'Grid field module reference referenced module names do not match');
			}
		}

		/**
		 * Intentar crear un grid sin la información mínima necesaria
		 * Debe arrojar una GridException
		 */
		public function testCreateIncompleteGrid () {
			$grid = Grid::getInstance ();
			$this->expectException (GridException::class);
			GridManager::getInstance (self::$adb)->saveGrid ($grid);
		}

		/**
		 * Intentar crear un grid asociada a un modulo no existente
		 * Debe arrojar una GridException
		 */
		public function testCreateNonExistingModuleGrid () {
			$fields = array (
				GridField::getInstance ()
					->setLabel ('Text field')
					->setName ('text_field')
					->setUiType (GridField::UI_TYPE_TEXT, 220),
			);

			$this->expectException (GridException::class);
			$this->expectExceptionMessage (GridException::ERROR_GRID_INVALID_MODULE_NAME);
			$grid = Grid::getInstance ()
				->setModuleName ('unknown_module_name')
				->setName ('test_grid_field')
				->setFields ($fields);
			GridManager::getInstance (self::$adb)->saveGrid ($grid);
		}

		/**
		 * Intentar crear un grid asociada a un campo no existente
		 * Debe arrojar una GridException
		 */
		public function testCreateNonExistingFieldGrid () {
			$fields = array (
				GridField::getInstance ()
					->setLabel ('Text field')
					->setName ('text_field')
					->setUiType (GridField::UI_TYPE_TEXT, 220),
			);

			$this->expectException (GridException::class);
			$this->expectExceptionMessage (GridException::ERROR_GRID_INVALID_FIELD_NAME);
			$grid = Grid::getInstance ()
				->setModuleName ('test_module')
				->setName ('unknown_grid_name')
				->setFields ($fields);
			GridManager::getInstance (self::$adb)->saveGrid ($grid);
		}

		/**
		 * Intentar crear un grid asociada a un campo no grid
		 * Debe arrojar una GridException
		 */
		public function testCreateNonGridFieldGrid () {
			$fields = array (
				GridField::getInstance ()
					->setLabel ('Text field')
					->setName ('text_field')
					->setUiType (GridField::UI_TYPE_TEXT, 220),
			);

			$this->expectException (GridException::class);
			$this->expectExceptionMessage (GridException::ERROR_GRID_INVALID_FIELD_NAME);
			$grid = Grid::getInstance ()
				->setModuleName ('test_module')
				->setName ('test_text_field')
				->setFields ($fields);
			GridManager::getInstance (self::$adb)->saveGrid ($grid);
		}

		/**
		 * Intentar crear un grid asociada a un campo grid, con un campo de referencia a módulo inválido
		 * Debe arrojar una GridException
		 */
		public function testCreateInvalidModuleReferenceFieldGrid () {
			$reference = FieldModuleReference::getInstance ()
				->setFieldName ('module_reference_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('unknown_related_module');
			$fields    = array (
				GridField::getInstance ()
					->setLabel ('Module reference field')
					->setName ('module_reference_field')
					->setModuleReference ($reference)
					->setUiType (GridField::UI_TYPE_MODULE_REFERENCE),
			);

			$this->expectException (GridException::class);
			$this->expectExceptionMessage (GridException::ERROR_GRID_INVALID_REFERENCED_MODULE_NAME);
			$grid = Grid::getInstance ()
				->setModuleName ('test_module')
				->setName ('test_grid_field')
				->setFields ($fields);
			GridManager::getInstance (self::$adb)->saveGrid ($grid);
		}

		/**
		 * Crear un grid válido, con cada uno de los diferentes tipos de campos
		 */
		public function testCreateValidGrid () {
			$reference = FieldModuleReference::getInstance ()
				->setFieldName ('module_reference_field')
				->setModuleName ('test_module')
				->setReferencedModuleName ('test_related_module');
			$fields    = array (
				GridField::getInstance ()
					->setLabel ('Checkbox field')
					->setName ('checkbox_field')
					->setUiType (GridField::UI_TYPE_CHECKBOX),
				GridField::getInstance ()
					->setLabel ('Datetime field')
					->setName ('datetime_field')
					->setUiType (GridField::UI_TYPE_DATETIME),
				GridField::getInstance ()
					->setLabel ('Module reference field')
					->setName ('module_reference_field')
					->setModuleReference ($reference)
					->setUiType (GridField::UI_TYPE_MODULE_REFERENCE),
				GridField::getInstance ()
					->setLabel ('Number field')
					->setName ('number_field')
					->setUiType (GridField::UI_TYPE_NUMBER, 16, 3),
				GridField::getInstance ()
					->setLabel ('Percentage field')
					->setName ('percentage_field')
					->setUiType (GridField::UI_TYPE_PERCENTAGE, 4, 1),
				GridField::getInstance ()
					->setLabel ('Picklist field')
					->setName ('picklist_field')
					->setUiType (GridField::UI_TYPE_PICKLIST)
					->setValues (array ('First value', 'Second value', 'Third value')),
				GridField::getInstance ()
					->setLabel ('Text field')
					->setName ('text_field')
					->setUiType (GridField::UI_TYPE_TEXT, 44),
				GridField::getInstance ()
					->setLabel ('Textarea field')
					->setName ('textarea_field')
					->setUiType (GridField::UI_TYPE_TEXTAREA),
				GridField::getInstance ()
					->setLabel ('URL field')
					->setName ('url_field')
					->setUiType (GridField::UI_TYPE_URL),
			);
			$grid      = Grid::getInstance ()
				->setModuleName ('test_module')
				->setName ('test_grid_field')
				->setFields ($fields);
			GridManager::getInstance (self::$adb)->saveGrid ($grid);

			// Verificar que se crea la tabla correctamente
			$result = self::$adb->pquery ('SHOW TABLES LIKE ?', array ('vtiger_test_module_test_grid_field'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table not found');

			// Verificar que se generan la cantidad de columnas esperadas (9 correspondientes a los campos + 1 del id del módulo + 1 del id del campo)
			$result = self::$adb->query ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field');
			$this->assertEquals (11, self::$adb->num_rows ($result), 'Grid table columns total do not match');

			// Verificar que se genera la columna del id del módulo
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field WHERE Field=?', array ('test_moduleid'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table does not have moduleid column');

			// Verificar que se genera la columna del id del campo
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field WHERE Field=?', array ('test_grid_fieldid'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table does not have gridid column');

			// Verificar que se generan los campos en la tabla
			$this->checkDatabaseField ('checkbox_field', 'Checkbox field', GridField::UI_TYPE_CHECKBOX, 'VARCHAR(3)', 1, 3, null, null, null, null);
			$this->checkDatabaseField ('datetime_field', 'Datetime field', GridField::UI_TYPE_DATETIME, 'DATETIME', 2, null, null, null, null, null);
			$this->checkDatabaseField ('module_reference_field', 'Module reference field', GridField::UI_TYPE_MODULE_REFERENCE, 'VARCHAR(255)', 3, 255, null, null, null, 'test_related_module');
			$this->checkDatabaseField ('number_field', 'Number field', GridField::UI_TYPE_NUMBER, 'DECIMAL(16,3)', 4, 16, 3, null, null, null);
			$this->checkDatabaseField ('percentage_field', 'Percentage field', GridField::UI_TYPE_PERCENTAGE, 'DECIMAL(4,1)', 5, 4, 1, null, null, null);
			$this->checkDatabaseField ('picklist_field', 'Picklist field', GridField::UI_TYPE_PICKLIST, 'VARCHAR(255)', 6, 255, null, null, json_encode (array ('First value', 'Second value', 'Third value')), null);
			$this->checkDatabaseField ('text_field', 'Text field', GridField::UI_TYPE_TEXT, 'VARCHAR(44)', 7, 44, null, null, null, null);
			$this->checkDatabaseField ('textarea_field', 'Textarea field', GridField::UI_TYPE_TEXTAREA, 'TEXT', 8, null, null, null, null, null);
			$this->checkDatabaseField ('url_field', 'URL field', GridField::UI_TYPE_URL, 'VARCHAR(255)', 9, 255, null, null, null, null);
		}

		/**
		 * Intentar obtener un grid de un módulo no existente, o de un módulo que no tiene campso grid
		 */
		public function testFetchInvalidModuleGrid () {
			$this->assertNull (GridManager::getInstance (self::$adb)->fetchGridByName ('unknown_module', 'test_grid_field'), 'grid should be null');
			$this->assertNull (GridManager::getInstance (self::$adb)->fetchGridByName ('test_related_module', 'test_related_field'), 'grid should be null');
		}

		/**
		 * Intentar obtener un grid de un campo no existente, o de un campo no grid
		 */
		public function testFetchInvalidFieldGrid () {
			$this->assertNull (GridManager::getInstance (self::$adb)->fetchGridByName ('test_module', 'unknown_field'), 'grid should be null');
			$this->assertNull (GridManager::getInstance (self::$adb)->fetchGridByName ('test_module', 'test_text_field'), 'grid should be null');
		}

		/**
		 * Obtener un grid existente
		 * @depends testCreateValidGrid
		 */
		public function testFetchExistingGrid () {
			$grid = GridManager::getInstance (self::$adb)->fetchGridByName ('test_module', 'test_grid_field');
			$this->assertNotNull ($grid, 'grid should not be null');
			$this->assertEquals ($grid->getModuleName (), 'test_module', 'Grid modules do not match');
			$this->assertEquals ($grid->getName (), 'test_grid_field', 'Grid names do not match');

			$fields = $grid->getFields ();
			$this->assertEquals (9, count ($fields), 'Grid fields count do not match');

			// Verificar que los campos tienen los valores correctos
			$this->checkGridField ($fields [0], 'checkbox_field', 'Checkbox field', GridField::UI_TYPE_CHECKBOX, 'VARCHAR(3)', 1, 3, null, null, null, null);
			$this->checkGridField ($fields [1], 'datetime_field', 'Datetime field', GridField::UI_TYPE_DATETIME, 'DATETIME', 2, null, null, null, null, null);
			$this->checkGridField ($fields [2], 'module_reference_field', 'Module reference field', GridField::UI_TYPE_MODULE_REFERENCE, 'VARCHAR(255)', 3, 255, null, null, null, 'test_related_module');
			$this->checkGridField ($fields [3], 'number_field', 'Number field', GridField::UI_TYPE_NUMBER, 'DECIMAL(16,3)', 4, 16, 3, null, null, null);
			$this->checkGridField ($fields [4], 'percentage_field', 'Percentage field', GridField::UI_TYPE_PERCENTAGE, 'DECIMAL(4,1)', 5, 4, 1, null, null, null);
			$this->checkGridField ($fields [5], 'picklist_field', 'Picklist field', GridField::UI_TYPE_PICKLIST, 'VARCHAR(255)', 6, 255, null, null, json_encode (array ('First value', 'Second value', 'Third value')), null);
			$this->checkGridField ($fields [6], 'text_field', 'Text field', GridField::UI_TYPE_TEXT, 'VARCHAR(44)', 7, 44, null, null, null, null);
			$this->checkGridField ($fields [7], 'textarea_field', 'Textarea field', GridField::UI_TYPE_TEXTAREA, 'TEXT', 8, null, null, null, null, null);
			$this->checkGridField ($fields [8], 'url_field', 'URL field', GridField::UI_TYPE_URL, 'VARCHAR(255)', 9, 255, null, null, null, null);
		}

		/**
		 * Actualizar un grid existente
		 * @depends testFetchExistingGrid
		 */
		public function testUpdateExistingGrid () {
			$grid = Grid::getInstance()
				->setModuleName ('test_module')
				->setName ('test_grid_field')
				->setFields (array (
					GridField::getInstance ()
						->setLabel ('Checkbox field')
						->setName ('checkbox_field2')
						->setUiType (GridField::UI_TYPE_CHECKBOX),
					GridField::getInstance ()
						->setLabel ('Datetime field')
						->setName ('datetime_field2')
						->setUiType (GridField::UI_TYPE_DATETIME),
					GridField::getInstance ()
						->setLabel ('Module reference field')
						->setName ('module_reference_field2')
						->setModuleReference (FieldModuleReference::getInstance ()
							->setFieldName ('module_reference_field')
							->setModuleName ('test_module')
							->setReferencedModuleName ('test_related_module'))
						->setUiType (GridField::UI_TYPE_MODULE_REFERENCE),
					GridField::getInstance ()
						->setLabel ('Number field')
						->setName ('number_field2')
						->setUiType (GridField::UI_TYPE_NUMBER, 8, 6),
					GridField::getInstance ()
						->setLabel ('Picklist field')
						->setName ('picklist_field2')
						->setUiType (GridField::UI_TYPE_PICKLIST)
						->setValues (array ('Fourth value', 'Fifth value')),
					GridField::getInstance ()
						->setLabel ('Text field')
						->setName ('text_field2')
						->setUiType (GridField::UI_TYPE_TEXT, 22),
					GridField::getInstance ()
						->setLabel ('Textarea field')
						->setName ('textarea_field2')
						->setUiType (GridField::UI_TYPE_TEXTAREA),
				));
			GridManager::getInstance (self::$adb)->saveGrid ($grid);

			// Verificar que se crea la tabla correctamente
			$result = self::$adb->pquery ('SHOW TABLES LIKE ?', array ('vtiger_test_module_test_grid_field'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table not found');

			// Verificar que se generan la cantidad de columnas esperadas (9 correspondientes a los campos + 1 del id del módulo + 1 del id del campo)
			$result = self::$adb->query ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field');
			$this->assertEquals (9, self::$adb->num_rows ($result), 'Grid table columns total do not match');

			// Verificar que se genera la columna del id del módulo
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field WHERE Field=?', array ('test_moduleid'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table does not have moduleid column');

			// Verificar que se genera la columna del id del campo
			$result = self::$adb->pquery ('SHOW COLUMNS FROM vtiger_test_module_test_grid_field WHERE Field=?', array ('test_grid_fieldid'));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Grid table does not have gridid column');

			// Verificar que se generan los campos en la tabla
			$this->checkDatabaseField ('checkbox_field2', 'Checkbox field', GridField::UI_TYPE_CHECKBOX, 'VARCHAR(3)', 1, 3, null, null, null, null);
			$this->checkDatabaseField ('datetime_field2', 'Datetime field', GridField::UI_TYPE_DATETIME, 'DATETIME', 2, null, null, null, null, null);
			$this->checkDatabaseField ('module_reference_field2', 'Module reference field', GridField::UI_TYPE_MODULE_REFERENCE, 'VARCHAR(255)', 3, 255, null, null, null, 'test_related_module');
			$this->checkDatabaseField ('number_field2', 'Number field', GridField::UI_TYPE_NUMBER, 'DECIMAL(8,6)', 4, 8, 6, null, null, null);
			$this->checkDatabaseField ('picklist_field2', 'Picklist field', GridField::UI_TYPE_PICKLIST, 'VARCHAR(255)', 5, 255, null, null, json_encode (array ('Fourth value', 'Fifth value')), null);
			$this->checkDatabaseField ('text_field2', 'Text field', GridField::UI_TYPE_TEXT, 'VARCHAR(22)', 6, 22, null, null, null, null);
			$this->checkDatabaseField ('textarea_field2', 'Textarea field', GridField::UI_TYPE_TEXTAREA, 'TEXT', 7, null, null, null, null, null);
		}

		/**
		 * Obtener un grid existente
		 * @depends testUpdateExistingGrid
		 */
		public function testFetchUpdatedGrid () {
			$grid = GridManager::getInstance (self::$adb)->fetchGridByName ('test_module', 'test_grid_field');
			$this->assertNotNull ($grid, 'grid should not be null');
			$this->assertEquals ($grid->getModuleName (), 'test_module', 'Grid modules do not match');
			$this->assertEquals ($grid->getName (), 'test_grid_field', 'Grid names do not match');

			$fields = $grid->getFields ();
			$this->assertEquals (7, count ($fields), 'Grid fields count do not match');

			// Verificar que los campos tienen los valores correctos
			$this->checkGridField ($fields [0], 'checkbox_field2', 'Checkbox field', GridField::UI_TYPE_CHECKBOX, 'VARCHAR(3)', 1, 3, null, null, null, null);
			$this->checkGridField ($fields [1], 'datetime_field2', 'Datetime field', GridField::UI_TYPE_DATETIME, 'DATETIME', 2, null, null, null, null, null);
			$this->checkGridField ($fields [2], 'module_reference_field2', 'Module reference field', GridField::UI_TYPE_MODULE_REFERENCE, 'VARCHAR(255)', 3, 255, null, null, null, 'test_related_module');
			$this->checkGridField ($fields [3], 'number_field2', 'Number field', GridField::UI_TYPE_NUMBER, 'DECIMAL(8,6)', 4, 8, 6, null, null, null);
			$this->checkGridField ($fields [4], 'picklist_field2', 'Picklist field', GridField::UI_TYPE_PICKLIST, 'VARCHAR(255)', 5, 255, null, null, json_encode (array ('Fourth value', 'Fifth value')), null);
			$this->checkGridField ($fields [5], 'text_field2', 'Text field', GridField::UI_TYPE_TEXT, 'VARCHAR(22)', 6, 22, null, null, null, null);
			$this->checkGridField ($fields [6], 'textarea_field2', 'Textarea field', GridField::UI_TYPE_TEXTAREA, 'TEXT', 7, null, null, null, null, null);
		}

		/**
		 * Obtener un grid existente
		 * @depends testFetchUpdatedGrid
		 */
		public function testDeleteGrid () {
			GridManager::getInstance (self::$adb)->deleteGrid (Grid::getInstance ()->setModuleName ('test_module')->setName ('test_grid_field'));

			// Verificar que se eliminaron los campos de la tabla vtiger_subfields
			$result = self::$adb->query ('SELECT * FROM vtiger_subfields');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Grid fields count should be zero');

			// Verificar que se crea la tabla correctamente
			$result = self::$adb->pquery ('SHOW TABLES LIKE ?', array ('vtiger_test_module_test_grid_field'));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Grid table not found');
		}

	}
	// @codingStandardsIgnoreEnd

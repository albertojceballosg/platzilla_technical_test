<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/ViewManager.php');

	/**
	 * Prueba funcional de la clase ViewManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ViewManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_blocks, vtiger_field, vtiger_subfields, vtiger_field_dependency, vtiger_fieldmodulerel, vtiger_picklist, vtiger_profile,
		 * vtiger_profile2field, vtiger_role, vtiger_role2picklist, vtiger_crmentity
		 * 4. Crear tabla de un módulo simulado vtiger_test_module
		 * 5. Simular existencia de dos módulos, un bloque, dos perfiles y dos roles
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
				"CREATE TABLE IF NOT EXISTS `vtiger_customview_seq` (
					`id` INT(11) NOT NULL,
					PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvadvfilter` (
					`cvid` INT(19) NOT NULL,
					`columnindex` INT(11) NOT NULL,
					`columnname` VARCHAR(250) DEFAULT '',
					`comparator` VARCHAR(10) DEFAULT '',
					`value` VARCHAR(200) DEFAULT '',
					`groupid` INT(11) DEFAULT '1',
					`column_condition` VARCHAR(255) DEFAULT 'and',
					PRIMARY KEY (`cvid`,`columnindex`),
					KEY `cvadvfilter_cvid_idx` (`cvid`),
					CONSTRAINT `fk_1_vtiger_cvadvfilter` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvadvfilter_grouping` (
					`groupid` INT(11) NOT NULL,
					`cvid` INT(19) NOT NULL,
					`group_condition` VARCHAR(255) DEFAULT NULL,
					`condition_expression` TEXT,
					PRIMARY KEY (`groupid`,`cvid`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvcolumnlist` (
					`cvid` INT(19) NOT NULL,
					`columnindex` INT(11) NOT NULL,
					`columnname` VARCHAR(250) DEFAULT '',
					PRIMARY KEY (`cvid`,`columnindex`),
					KEY `cvcolumnlist_columnindex_idx` (`columnindex`),
					KEY `cvcolumnlist_cvid_idx` (`cvid`),
					CONSTRAINT `fk_1_vtiger_cvcolumnlist` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8"
			);
			self::$adb->query (
				"CREATE TABLE IF NOT EXISTS `vtiger_cvstdfilter` (
					`cvid` INT(19) NOT NULL,
					`columnname` VARCHAR(250) DEFAULT '',
					`stdfilter` VARCHAR(250) DEFAULT '',
					`startdate` DATE DEFAULT NULL,
					`enddate` DATE DEFAULT NULL,
					PRIMARY KEY (`cvid`),
					KEY `cvstdfilter_cvid_idx` (`cvid`),
					CONSTRAINT `fk_1_vtiger_cvstdfilter` FOREIGN KEY (`cvid`) REFERENCES `vtiger_customview` (`cvid`) ON DELETE CASCADE
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
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 1, 'code_field', 'vtiger_test_module', 1, '4', 'code_field', 'My code field', 1, 2, '', 100, 1, 4166, 1, 'V~M~LE~100', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 2, 'date_field', 'vtiger_test_module', 1, '5', 'date_field', 'My date field', 1, 2, '', 100, 1, 4166, 1, 'D~O', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 3, 'number_field', 'vtiger_test_module', 1, '7', 'number_field', 'My number field', 1, 2, '', 100, 1, 4166, 1, 'NN~O~16,2', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 4, 'text_field', 'vtiger_test_module', 1, '1', 'text_field', 'My text field', 1, 2, '', 100, 1, 4166, 1, 'NN~O~16,2', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_field` (`tabid`, `fieldid`, `columnname`, `tablename`, `generatedtype`, `uitype`, `fieldname`, `fieldlabel`, `readonly`, `presence`, `defaultvalue`, `maximumlength`, `sequence`, `block`, `displaytype`, `typeofdata`, `quickcreate`, `quickcreatesequence`, `info_type`, `masseditable`, `helpinfo`, `paradicional`) VALUES (1, 5, 'datetime_field', 'vtiger_test_module', 1, '6', 'datetime_field', 'My datetime field', 1, 2, '', 100, 1, 4166, 1, 'NN~O~16,2', 2, 1, 'BAS', 2, '', NULL)");
			self::$adb->query ("INSERT INTO `vtiger_customview_seq` (id) VALUES (0)");

			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (1, 'Administrator', 'Admin Profile')");
			self::$adb->query ("INSERT INTO `vtiger_profile` (`profileid`, `profilename`, `description`) VALUES (2, 'CRM', 'El CRM blah blah blah')");
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
		 * Intentar crear una vista sin la información mínima necesaria
		 * Debe arrojar una ViewException
		 */
		public function testCreateIncompleteView () {
			$view = View::getInstance ();
			$this->expectException (ViewException::class);
			ViewManager::getInstance (self::$adb)->saveView ($view);
		}

		/**
		 * Intentar crear una vista para un módulo no existente
		 * Debe arrojar una ViewException
		 */
		public function testCreateInvalidModuleView () {
			$field = Field::getInstance ()
				->setColumnName ('code_field')
				->setLabel ('My field label')
				->setModuleName ('unknown_module')
				->setName ('code_field')
				->setTableName ('vtiger_test_module')
				->setUiType (FieldInterface::UI_TYPE_TEXT);
			$view  = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($field)->setSequence (0),
				))
				->setModuleName ('unknown_module')
				->setName ('My view')
				->setOwner (1);
			$this->expectException (ViewException::class);
			$this->expectExceptionMessage (ViewException::ERROR_VIEW_INVALID_MODULE_NAME);
			ViewManager::getInstance (self::$adb)->saveView ($view);
		}

		/**
		 * Intentar crear una vista con un filtro estándar de un módulo no existente
		 * Debe arrojar una ViewColumnException
		 */
		public function testCreateInvalidModuleViewStandardFilter () {
			$validField   = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$invalidField = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('unknown_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$view         = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($validField)->setSequence (0),
				))
				->setModuleName ('test_module')
				->setName ('My view')
				->setOwner (1)
				->setStandardFilter (
					ViewStandardFilter::getInstance ($invalidField)
						->setEndDate ('2017-12-31')
						->setPeriod (ViewStandardFilterInterface::PERIOD_CURRENT_MONTH)
						->setStartDate ('2017-12-01')
				);
			$this->expectException (ViewStandardFilterException::class);
			$this->expectExceptionMessage (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_INVALID_MODULE_NAME);
			ViewManager::getInstance (self::$adb)->saveView ($view);
		}

		/**
		 * Intentar crear una vista con un filtro estándar de una columna no existente
		 * Debe arrojar una ViewColumnException
		 */
		public function testCreateInvalidColumnNameViewStandardFilter () {
			$validField   = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$invalidField = Field::getInstance ()->setColumnName ('unknown_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$view         = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($validField)->setSequence (0),
				))
				->setModuleName ('test_module')
				->setName ('My view')
				->setOwner (1)
				->setStandardFilter (
					ViewStandardFilter::getInstance ($invalidField)
						->setEndDate ('2017-12-31')
						->setPeriod (ViewStandardFilterInterface::PERIOD_CURRENT_MONTH)
						->setStartDate ('2017-12-01')
				);
			$this->expectException (ViewStandardFilterException::class);
			$this->expectExceptionMessage (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_INVALID_COLUMN_NAME);
			ViewManager::getInstance (self::$adb)->saveView ($view);
		}

		/**
		 * Intentar crear una vista con un filtro estándar de un campo no existente
		 * Debe arrojar una ViewColumnException
		 */
		public function testCreateInvalidFieldViewStandardFilter () {
			$validField   = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$invalidField = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('unknown_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$view         = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($validField)->setSequence (0),
				))
				->setModuleName ('test_module')
				->setName ('My view')
				->setOwner (1)
				->setStandardFilter (
					ViewStandardFilter::getInstance ($invalidField)
						->setEndDate ('2017-12-31')
						->setPeriod (ViewStandardFilterInterface::PERIOD_CURRENT_MONTH)
						->setStartDate ('2017-12-01')
				);
			$this->expectException (ViewStandardFilterException::class);
			$this->expectExceptionMessage (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_INVALID_FIELD_NAME);
			ViewManager::getInstance (self::$adb)->saveView ($view);
		}

		/**
		 * Intentar crear una vista con un filtro estándar de una tabla no existente
		 * Debe arrojar una ViewColumnException
		 */
		public function testCreateInvalidTableNameViewStandardFilter () {
			$validField   = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$invalidField = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('unknown_table_name')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$view         = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($validField)->setSequence (0),
				))
				->setModuleName ('test_module')
				->setName ('My view')
				->setOwner (1)
				->setStandardFilter (
					ViewStandardFilter::getInstance ($invalidField)
						->setEndDate ('2017-12-31')
						->setPeriod (ViewStandardFilterInterface::PERIOD_CURRENT_MONTH)
						->setStartDate ('2017-12-01')
				);
			$this->expectException (ViewStandardFilterException::class);
			$this->expectExceptionMessage (ViewStandardFilterException::ERROR_VIEW_STANDARD_FILTER_INVALID_TABLE_NAME);
			ViewManager::getInstance (self::$adb)->saveView ($view);
		}

		/**
		 * Intentar crear una vista con un filtro avanzado de un módulo no existente
		 * Debe arrojar una ViewColumnException
		 */
		public function testCreateInvalidModuleViewAdvancedFilter () {
			$validField   = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$invalidField = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('unknown_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$view         = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($validField)->setSequence (0),
				))
				->setModuleName ('test_module')
				->setName ('My view')
				->setOwner (1)
				->setAdvancedFilterGroups (array (
					ViewAdvancedFilterGroup::getInstance ()
						->setSequence (0)
						->setFilters (array (
							ViewAdvancedFilter::getInstance ($invalidField)
								->setComparator (ViewAdvancedFilterInterface::COMPARATOR_EQUALS)
								->setSequence (0)
								->setValue (5),
						)),
				));
			$this->expectException (ViewAdvancedFilterException::class);
			$this->expectExceptionMessage (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_INVALID_MODULE_NAME);
			ViewManager::getInstance (self::$adb)->saveView ($view);
		}

		/**
		 * Intentar crear una vista con un filtro avanzado de una columna no existente
		 * Debe arrojar una ViewColumnException
		 */
		public function testCreateInvalidColumnNameViewAdvancedFilter () {
			$validField   = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$invalidField = Field::getInstance ()->setColumnName ('unknown_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$view         = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($validField)->setSequence (0),
				))
				->setModuleName ('test_module')
				->setName ('My view')
				->setOwner (1)
				->setAdvancedFilterGroups (array (
					ViewAdvancedFilterGroup::getInstance ()
						->setSequence (0)
						->setFilters (array (
							ViewAdvancedFilter::getInstance ($invalidField)
								->setComparator (ViewAdvancedFilterInterface::COMPARATOR_EQUALS)
								->setSequence (0)
								->setValue (5),
						)),
				));
			$this->expectException (ViewAdvancedFilterException::class);
			$this->expectExceptionMessage (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_INVALID_COLUMN_NAME);
			ViewManager::getInstance (self::$adb)->saveView ($view);
		}

		/**
		 * Intentar crear una vista con un filtro avanzado de un campo no existente
		 * Debe arrojar una ViewColumnException
		 */
		public function testCreateInvalidFieldViewAdvancedFilter () {
			$validField   = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$invalidField = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('unknown_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$view         = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($validField)->setSequence (0),
				))
				->setModuleName ('test_module')
				->setName ('My view')
				->setOwner (1)
				->setAdvancedFilterGroups (array (
					ViewAdvancedFilterGroup::getInstance ()
						->setSequence (0)
						->setFilters (array (
							ViewAdvancedFilter::getInstance ($invalidField)
								->setComparator (ViewAdvancedFilterInterface::COMPARATOR_EQUALS)
								->setSequence (0)
								->setValue (5),
						)),
				));
			$this->expectException (ViewAdvancedFilterException::class);
			$this->expectExceptionMessage (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_INVALID_FIELD_NAME);
			ViewManager::getInstance (self::$adb)->saveView ($view);
		}

		/**
		 * Intentar crear una vista con un filtro avanzado de una tabla no existente
		 * Debe arrojar una ViewColumnException
		 */
		public function testCreateInvalidTableNameViewAdvancedFilter () {
			$validField   = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('vtiger_test_module')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$invalidField = Field::getInstance ()->setColumnName ('code_field')->setLabel ('My field label')->setModuleName ('test_module')->setName ('code_field')->setTableName ('unknown_table_name')->setUiType (FieldInterface::UI_TYPE_TEXT);
			$view         = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($validField)->setSequence (0),
				))
				->setModuleName ('test_module')
				->setName ('My view')
				->setOwner (1)
				->setAdvancedFilterGroups (array (
					ViewAdvancedFilterGroup::getInstance ()
						->setSequence (0)
						->setFilters (array (
							ViewAdvancedFilter::getInstance ($invalidField)
								->setComparator (ViewAdvancedFilterInterface::COMPARATOR_EQUALS)
								->setSequence (0)
								->setValue (5),
						)),
				));
			$this->expectException (ViewAdvancedFilterException::class);
			$this->expectExceptionMessage (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_INVALID_TABLE_NAME);
			ViewManager::getInstance (self::$adb)->saveView ($view);
		}

		/**
		 * Crear una vista con el mínimo de información
		 */
		public function testCreateView () {
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';
			$viewName   = 'Test view # 1';
			/** @var Field[] $fields */
			$fields    = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$view      = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($fields [0])->setSequence (0),
					ViewColumn::getInstance ($fields [1])->setSequence (1),
					ViewColumn::getInstance ($fields [2])->setSequence (2),
					ViewColumn::getInstance ($fields [3])->setSequence (3),
				))
				->setDefault (ViewInterface::DEFAULT_YES)
				->setModuleName ($moduleName)
				->setName ($viewName)
				->setOwner (1)
				->setShowCountInMenu (ViewInterface::SHOW_COUNT_NO)
				->setStatus (ViewInterface::STATUS_PUBLIC);
			$savedView = ViewManager::getInstance (self::$adb)->saveView ($view);

			$this->assertNotNull ($savedView, 'Saved view should not be null');
			$this->assertNotEmpty ($savedView->getId (), 'Saved view ID should not be empty');

			// Verificar que se creó la vista en la base de datos
			$viewId = $savedView->getId ();
			$result = self::$adb->pquery ('SELECT * FROM vtiger_customview WHERE cvid=?', array ($viewId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved view not found in database');

			// Verificar que se almacenaron correctamente los datos de la vista en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($viewName, $row ['viewname'], 'View names do not match');
			$this->assertEquals (ViewInterface::DEFAULT_YES, $row ['setdefault'], 'View default properties do not match');
			$this->assertEquals (ViewInterface::SHOW_COUNT_NO, $row ['setmetrics'], 'View showCountInMenu properties do not match');
			$this->assertEquals ($moduleName, $row ['entitytype'], 'View module names do not match');
			$this->assertEquals (ViewInterface::STATUS_PUBLIC, $row ['status'], 'View statuses do not match');
			$this->assertEquals (1, $row ['userid'], 'View owners do not match');

			// Verificar que se almacenaron correctamente los datos de las columnas en la base de datos
			$columns = $savedView->getColumns ();
			$result  = self::$adb->pquery ('SELECT * FROM vtiger_cvcolumnlist WHERE cvid=? ORDER BY columnindex', array ($viewId));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'Saved view columns count do not match');
			$index = 0;
			while ($row = self::$adb->fetchByAssoc ($result, -1, false)) {
				$label      = str_replace (' ', '_', $fields [ $index ]->getLabel ());
				$columnName = "{$fields [$index]->getTableName ()}:{$fields [$index]->getColumnName ()}:{$fields [$index]->getName ()}:{$fields [$index]->getModuleName ()}_{$label}:{$fields [$index]->getDataType ()}";
				$this->assertNotEmpty ($columns [ $index ]->getViewId (), 'Column view ID should not be empty');
				$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
				$index++;
			}

			// Verificar que no tiene filtro estándar asociado
			$standardFilter = $view->getStandardFilter ();
			$this->assertNull ($standardFilter, 'Saved view standard filter should be null');

			// Verificar que no existe el filtro estándar en la base de datos
			$viewId = $savedView->getId ();
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvstdfilter WHERE cvid=?', array ($viewId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved view standard filters count do not match');

			// Verificar que no tiene filtros avanzados
			$groups = $view->getAdvancedFilterGroups ();
			$this->assertNull ($groups, 'Saved view advanced filters groups should be null');

			// Verificar que no existen grupos en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid=?', array ($viewId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved view advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=?', array ($viewId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved view advanced filter groups count do not match');
		}

		/**
		 * Crear una vista con un filtro estándar
		 */
		public function testCreateViewWithStandardFilter () {
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';
			$viewName   = 'Test view # 2';
			/** @var Field[] $fields */
			$fields    = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$view      = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($fields [0])->setSequence (0),
					ViewColumn::getInstance ($fields [1])->setSequence (1),
					ViewColumn::getInstance ($fields [2])->setSequence (2),
					ViewColumn::getInstance ($fields [3])->setSequence (3),
				))
				->setModuleName ($moduleName)
				->setName ($viewName)
				->setOwner (1)
				->setStandardFilter (
					ViewStandardFilter::getInstance ($fields [3])
						->setEndDate ('2017-12-31')
						->setPeriod (ViewStandardFilterInterface::PERIOD_CUSTOM)
						->setStartDate ('2017-12-01')
				);
			$savedView = ViewManager::getInstance (self::$adb)->saveView ($view);

			$this->assertNotNull ($savedView, 'Saved view should not be null');
			$this->assertNotEmpty ($savedView->getId (), 'Saved view ID should not be empty');

			// Verificar que se creó el filtro estándar
			$standardFilter = $view->getStandardFilter ();
			$this->assertNotNull ($standardFilter, 'Saved view standard filter should not be null');
			$this->assertInstanceOf (ViewStandardFilter::class, $standardFilter, 'Saved view standard filter is not an instance of ViewStandardFilter');
			$this->assertNotEmpty ($standardFilter->getViewId (), 'Standard filter view ID should not be empty');

			// Verificar que se creó el filtro estándar en la base de datos
			$viewId = $savedView->getId ();
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvstdfilter WHERE cvid=?', array ($viewId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved view standard filters count do not match');

			// Verificar que se almacenaron correctamente los datos del filtro estándar
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getName ()}:{$fields [3]->getModuleName ()}_{$label}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (ViewStandardFilterInterface::PERIOD_CUSTOM, $row ['stdfilter'], 'Standard filter periods do not match');
			$this->assertEquals ('2017-12-01', $row ['startdate'], 'Standard filter start dates do not match');
			$this->assertEquals ('2017-12-31', $row ['enddate'], 'Standard filter end dates do not match');

			// Verificar que no tiene filtros avanzados
			$groups = $view->getAdvancedFilterGroups ();
			$this->assertNull ($groups, 'Saved view advanced filters groups should be null');

			// Verificar que no existen grupos en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid=?', array ($viewId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved view advanced filter groups count do not match');

			// Verificar que no existen filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=?', array ($viewId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'Saved view advanced filter groups count do not match');
		}

		/**
		 * Crear una vista con un filtro avanzado
		 */
		public function testCreateViewWithAdvancedFilters () {
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';
			$viewName   = 'Test view # 3';
			/** @var Field[] $fields */
			$fields    = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$view      = View::getInstance ()
				->setColumns (array (
					ViewColumn::getInstance ($fields [0])->setSequence (0),
					ViewColumn::getInstance ($fields [1])->setSequence (1),
					ViewColumn::getInstance ($fields [2])->setSequence (2),
					ViewColumn::getInstance ($fields [3])->setSequence (3),
				))
				->setModuleName ($moduleName)
				->setName ($viewName)
				->setOwner (1)
				->setAdvancedFilterGroups (array (
					ViewAdvancedFilterGroup::getInstance ()
						->setOperator (ViewAdvancedFilterInterface::OPERATOR_AND)
						->setSequence (0)
						->setFilters (array (
							ViewAdvancedFilter::getInstance ($fields [0])
								->setComparator (ViewAdvancedFilterInterface::COMPARATOR_EQUALS)
								->setOperator (ViewAdvancedFilterInterface::OPERATOR_AND)
								->setSequence (0)
								->setValue ('COD-0001'),
							ViewAdvancedFilter::getInstance ($fields [1])
								->setComparator (ViewAdvancedFilterInterface::COMPARATOR_CONTAINS)
								->setSequence (1)
								->setValue ('TEST'),
						)),
					ViewAdvancedFilterGroup::getInstance ()
						->setSequence (1)
						->setFilters (array (
							ViewAdvancedFilter::getInstance ($fields [2])
								->setComparator (ViewAdvancedFilterInterface::COMPARATOR_GREATER)
								->setOperator (ViewAdvancedFilterInterface::OPERATOR_OR)
								->setSequence (2)
								->setValue (0),
							ViewAdvancedFilter::getInstance ($fields [3])
								->setComparator (ViewAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS)
								->setSequence (3)
								->setValue ('2017-12-31'),
						)),
				));
			$savedView = ViewManager::getInstance (self::$adb)->saveView ($view);

			$this->assertNotNull ($savedView, 'Saved view should not be null');
			$this->assertNotEmpty ($savedView->getId (), 'Saved view ID should not be empty');

			// Verificar que se crearon los grupos
			$groups = $view->getAdvancedFilterGroups ();
			$this->assertNotNull ($groups, 'Saved view advanced filters groups should not be null');
			$this->assertCount (2, $groups, 'Saved view advanced filters groups count do not match');
			$this->assertNotEmpty ($groups [0]->getViewId (), 'Advanced filter group view ID should not be empty');
			$this->assertNotEmpty ($groups [1]->getViewId (), 'Advanced filter group view ID should not be empty');

			// Verificar que se crearon los filtros del grupo
			$filters = $groups [0]->getFilters ();
			$this->assertNotNull ($filters, 'Saved view advanced filter group filters should not be null');
			$this->assertCount (2, $filters, 'Saved view advanced filter group filters count do not match');
			$this->assertNotEmpty ($filters [0]->getViewId (), 'Advanced filter view ID should not be empty');
			$this->assertNotEmpty ($filters [1]->getViewId (), 'Advanced filter view ID should not be empty');

			$filters = $groups [1]->getFilters ();
			$this->assertNotNull ($filters, 'Saved view advanced filter group filters should not be null');
			$this->assertCount (2, $filters, 'Saved view advanced filter group filters count do not match');
			$this->assertNotEmpty ($filters [0]->getViewId (), 'Advanced filter view ID should not be empty');
			$this->assertNotEmpty ($filters [1]->getViewId (), 'Advanced filter view ID should not be empty');

			// Verificar que se crearon los grupos en la base de datos
			$viewId = $savedView->getId ();
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid=?', array ($viewId));
			$this->assertEquals (2, self::$adb->num_rows ($result), 'Saved view advanced filter groups count do not match');

			// Verificar que se almacenaron correctamente los datos del primer grupo
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('and', $row ['group_condition'], 'Group operators do not match');
			$this->assertEquals (' 0 and 1 ', $row ['condition_expression'], 'Group condition expresions do not match');

			// Verificar que se almacenaron correctamente los datos del segundo grupo
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('', $row ['group_condition'], 'Group operators do not match');
			$this->assertEquals (' 2 or 3 ', $row ['condition_expression'], 'Group condition expresions do not match');

			// Verificar que se crearon los filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=? ORDER BY columnindex', array ($viewId));
			$this->assertEquals (4, self::$adb->num_rows ($result), 'Saved view advanced filters count do not match');

			// Verificar que se almacenaron correctamente los datos del primer filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [0]->getLabel ());
			$columnName = "{$fields [0]->getTableName ()}:{$fields [0]->getColumnName ()}:{$fields [0]->getName ()}:{$fields [0]->getModuleName ()}_{$label}:{$fields [0]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (0, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_EQUALS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('COD-0001', $row ['value'], 'Values do not match');
			$this->assertEquals (0, $row ['groupid'], 'Values do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::OPERATOR_AND, $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del segundo filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [1]->getLabel ());
			$columnName = "{$fields [1]->getTableName ()}:{$fields [1]->getColumnName ()}:{$fields [1]->getName ()}:{$fields [1]->getModuleName ()}_{$label}:{$fields [1]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (1, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_CONTAINS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('TEST', $row ['value'], 'Values do not match');
			$this->assertEquals (0, $row ['groupid'], 'Values do not match');
			$this->assertEquals ('', $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del tercer filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [2]->getLabel ());
			$columnName = "{$fields [2]->getTableName ()}:{$fields [2]->getColumnName ()}:{$fields [2]->getName ()}:{$fields [2]->getModuleName ()}_{$label}:{$fields [2]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (2, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_GREATER, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals (0, $row ['value'], 'Values do not match');
			$this->assertEquals (1, $row ['groupid'], 'Values do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::OPERATOR_OR, $row ['column_condition'], 'Values do not match');

			// Verificar que se almacenaron correctamente los datos del cuarto filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [3]->getLabel ());
			$columnName = "{$fields [3]->getTableName ()}:{$fields [3]->getColumnName ()}:{$fields [3]->getName ()}:{$fields [3]->getModuleName ()}_{$label}:{$fields [3]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (3, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('2017-12-31', $row ['value'], 'Values do not match');
			$this->assertEquals (1, $row ['groupid'], 'Values do not match');
			$this->assertEquals ('', $row ['column_condition'], 'Values do not match');
		}

		/**
		 * Intentar obtener una vista no existente
		 */
		public function testFetchNonExistingView () {
			$this->assertNull (ViewManager::getInstance (self::$adb)->fetchView ('test_module', 'unknown_view'));
			$this->assertNull (ViewManager::getInstance (self::$adb)->fetchView ('unknown_module', 'Test view # 1'));
		}

		/**
		 * Obtener la vista simple de la base de datos
		 * @depends testCreateView
		 */
		public function testFetchView () {
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';
			$viewName   = 'Test view # 1';
			$view       = ViewManager::getInstance (self::$adb)->fetchView ($moduleName, $viewName);

			// Verificar que se obtuvo la vista
			$this->assertNotNull ($view, 'View should not be null');
			$this->assertNotEmpty ($view->getId (), 'View ID should not be empty');

			// Verificar que se obtuvieron los valores esperados
			$this->assertEquals (ViewInterface::DEFAULT_YES, $view->getDefault (), 'Default properties do not match');
			$this->assertEquals ($moduleName, $view->getModuleName (), 'Module names do not match');
			$this->assertEquals ($viewName, $view->getName (), 'View names do not match');
			$this->assertEquals (1, $view->getOwner (), 'Owners do not match');
			$this->assertEquals (ViewInterface::SHOW_COUNT_NO, $view->getShowCountInMenu (), 'ShowCountInMenu properties do not match');
			$this->assertEquals (ViewInterface::STATUS_PUBLIC, $view->getStatus (), 'Statuses do not match');
			$this->assertNull ($view->getAdvancedFilterGroups (), 'Advanced filters should be null');
			$this->assertNull ($view->getStandardFilter (), 'Standard filter should be null');

			// Data original
			/** @var Field[] $fields */
			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);

			// Verificar las columnas
			$columns = $view->getColumns ();
			$this->assertNotNull ($columns, 'Columns should not be null');
			$this->assertCount (4, $columns, 'Columns count do not match');

			foreach ($columns as $index => $column) {
				$this->assertEquals ($view->getId (), $column->getViewId (), 'View IDs do not match');
				$this->assertEquals ($fields [ $index ]->getColumnName (), $column->getColumnName (), 'Column names do not match');
				$this->assertEquals ($fields [ $index ]->getDataType (), $column->getDataType (), 'Data types do not match');
				$this->assertEquals ($fields [ $index ]->getName (), $column->getFieldName (), 'Field names do not match');
				$this->assertEquals ($fields [ $index ]->getLabel (), $column->getLabel (), 'Labels do not match');
				$this->assertEquals ($fields [ $index ]->getModuleName (), $column->getModuleName (), 'Module names do not match');
				$this->assertEquals ($fields [ $index ]->getTableName (), $column->getTableName (), 'Table names do not match');
			}
		}

		/**
		 * Obtener la vista con filtro estándar de la base de datos
		 * @depends testCreateViewWithStandardFilter
		 */
		public function testFetchViewWithStandardFilter () {
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';
			$viewName   = 'Test view # 2';
			$view       = ViewManager::getInstance (self::$adb)->fetchView ($moduleName, $viewName);

			// Verificar que se obtuvo la vista
			$this->assertNotNull ($view, 'View should not be null');

			// Verificar que se obtuvieron los valores esperados
			$this->assertNotNull ($view->getStandardFilter (), 'Standard filter should not be null');
			$this->assertNull ($view->getAdvancedFilterGroups (), 'Advanced filters should be null');

			// Data original
			/** @var Field[] $fields */
			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);

			// Verificar el filtro estándar
			$standardFilter = $view->getStandardFilter ();
			$this->assertEquals ($view->getId (), $standardFilter->getViewId (), 'View IDs do not match');
			$this->assertEquals ($fields [3]->getColumnName (), $standardFilter->getColumnName (), 'Column names do not match');
			$this->assertEquals ($fields [3]->getLabel (), $standardFilter->getLabel (), 'Labels do not match');
			$this->assertEquals ($fields [3]->getName (), $standardFilter->getFieldName (), 'Field names do not match');
			$this->assertEquals ($fields [3]->getModuleName (), $standardFilter->getModuleName (), 'Module names do not match');
			$this->assertEquals ($fields [3]->getTableName (), $standardFilter->getTableName (), 'Table names do not match');
			$this->assertEquals ('2017-12-31', $standardFilter->getEndDate ()->format ('Y-m-d'), 'End dates do not match');
			$this->assertEquals (ViewStandardFilterInterface::PERIOD_CUSTOM, $standardFilter->getPeriod (), 'Periods do not match');
			$this->assertEquals ('2017-12-01', $standardFilter->getStartDate ()->format ('Y-m-d'), 'Start dates do not match');
		}

		/**
		 * Obtener la vista con filtros avanzados de la base de datos
		 * @depends testCreateViewWithAdvancedFilters
		 */
		public function testFetchViewWithAdvancedFilters () {
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';
			$viewName   = 'Test view # 3';
			$view       = ViewManager::getInstance (self::$adb)->fetchView ($moduleName, $viewName);

			// Verificar que se obtuvo la vista
			$this->assertNotNull ($view, 'View should not be null');

			// Verificar que se obtuvieron los valores esperados
			$this->assertNotNull ($view->getAdvancedFilterGroups (), 'Advanced filters should not be null');
			$this->assertNull ($view->getStandardFilter (), 'Standard filter should be null');

			// Data original
			/** @var Field[] $fields */
			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);

			// Verificar los grupos
			$groups = $view->getAdvancedFilterGroups ();
			$this->assertCount (2, $groups, 'Groups count do not match');

			// Verificar el primer grupo
			$this->assertNotNull ($groups [0], 'Group should not be null');
			$this->assertEquals ($view->getId (), $groups [0]->getViewId (), 'View IDs do not match');
			$this->assertEquals (0, $groups [0]->getSequence (), 'Group sequences do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::OPERATOR_AND, $groups [0]->getOperator (), 'Group operators do not match');

			// Verificar los filtros del primer grupo
			$filters = $groups [0]->getFilters ();
			$this->assertNotNull ($filters, 'Group filters should not be null');
			$this->assertCount (2, $filters, 'Group filters count do not match');

			// Verificar el primer filtro
			$this->assertEquals ($view->getId (), $filters [0]->getViewId (), 'View IDs do not match');
			$this->assertEquals ($fields [0]->getColumnName (), $filters [0]->getColumnName (), 'Column names do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_EQUALS, $filters [0]->getComparator (), 'Comparators do not match');
			$this->assertEquals ($fields [0]->getDataType (), $filters [0]->getDataType (), 'Data types do not match');
			$this->assertEquals ($fields [0]->getName (), $filters [0]->getFieldName (), 'Field names do not match');
			$this->assertEquals (0, $filters [0]->getGroupId (), 'Group IDs do not match');
			$this->assertEquals ($fields [0]->getLabel (), $filters [0]->getLabel (), 'Labels do not match');
			$this->assertEquals ($fields [0]->getModuleName (), $filters [0]->getModuleName (), 'Module names do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::OPERATOR_AND, $filters [0]->getOperator (), 'Operators do not match');
			$this->assertEquals (0, $filters [0]->getSequence (), 'Sequences do not match');
			$this->assertEquals ($fields [0]->getTableName (), $filters [0]->getTableName (), 'Table names do not match');
			$this->assertEquals ('COD-0001', $filters [0]->getValue (), 'Values do not match');

			// Verificar el segundo filtro
			$this->assertEquals ($view->getId (), $filters [1]->getViewId (), 'View IDs do not match');
			$this->assertEquals ($fields [1]->getColumnName (), $filters [1]->getColumnName (), 'Column names do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_CONTAINS, $filters [1]->getComparator (), 'Comparators do not match');
			$this->assertEquals ($fields [1]->getDataType (), $filters [1]->getDataType (), 'Data types do not match');
			$this->assertEquals ($fields [1]->getName (), $filters [1]->getFieldName (), 'Field names do not match');
			$this->assertEquals (0, $filters [1]->getGroupId (), 'Group IDs do not match');
			$this->assertEquals ($fields [1]->getLabel (), $filters [1]->getLabel (), 'Labels do not match');
			$this->assertEquals ($fields [1]->getModuleName (), $filters [1]->getModuleName (), 'Module names do not match');
			$this->assertEquals ('', $filters [1]->getOperator (), 'Operators do not match');
			$this->assertEquals (1, $filters [1]->getSequence (), 'Sequences do not match');
			$this->assertEquals ($fields [1]->getTableName (), $filters [1]->getTableName (), 'Table names do not match');
			$this->assertEquals ('TEST', $filters [1]->getValue (), 'Values do not match');

			// Verificar el segundo grupo
			$this->assertNotNull ($groups [1], 'Group should not be null');
			$this->assertEquals ($view->getId (), $groups [1]->getViewId (), 'View IDs do not match');
			$this->assertEquals (1, $groups [1]->getSequence (), 'Group sequences do not match');
			$this->assertEquals ('', $groups [1]->getOperator (), 'Group operators do not match');

			// Verificar los filtros del segundo grupo
			$filters = $groups [1]->getFilters ();
			$this->assertNotNull ($filters, 'Group filters should not be null');
			$this->assertCount (2, $filters, 'Group filters count do not match');

			// Verificar el primer filtro
			$this->assertEquals ($view->getId (), $filters [0]->getViewId (), 'View IDs do not match');
			$this->assertEquals ($fields [2]->getColumnName (), $filters [0]->getColumnName (), 'Column names do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_GREATER, $filters [0]->getComparator (), 'Comparators do not match');
			$this->assertEquals ($fields [2]->getDataType (), $filters [0]->getDataType (), 'Data types do not match');
			$this->assertEquals ($fields [2]->getName (), $filters [0]->getFieldName (), 'Field names do not match');
			$this->assertEquals (1, $filters [0]->getGroupId (), 'Group IDs do not match');
			$this->assertEquals ($fields [2]->getLabel (), $filters [0]->getLabel (), 'Labels do not match');
			$this->assertEquals ($fields [2]->getModuleName (), $filters [0]->getModuleName (), 'Module names do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::OPERATOR_OR, $filters [0]->getOperator (), 'Operators do not match');
			$this->assertEquals (2, $filters [0]->getSequence (), 'Sequences do not match');
			$this->assertEquals ($fields [2]->getTableName (), $filters [0]->getTableName (), 'Table names do not match');
			$this->assertEquals (0, $filters [0]->getValue (), 'Values do not match');

			// Verificar el segundo filtro
			$this->assertEquals ($view->getId (), $filters [1]->getViewId (), 'View IDs do not match');
			$this->assertEquals ($fields [3]->getColumnName (), $filters [1]->getColumnName (), 'Column names do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS, $filters [1]->getComparator (), 'Comparators do not match');
			$this->assertEquals ($fields [3]->getDataType (), $filters [1]->getDataType (), 'Data types do not match');
			$this->assertEquals ($fields [3]->getName (), $filters [1]->getFieldName (), 'Field names do not match');
			$this->assertEquals (1, $filters [1]->getGroupId (), 'Group IDs do not match');
			$this->assertEquals ($fields [3]->getLabel (), $filters [1]->getLabel (), 'Labels do not match');
			$this->assertEquals ($fields [3]->getModuleName (), $filters [1]->getModuleName (), 'Module names do not match');
			$this->assertEquals ('', $filters [1]->getOperator (), 'Operators do not match');
			$this->assertEquals (3, $filters [1]->getSequence (), 'Sequences do not match');
			$this->assertEquals ($fields [3]->getTableName (), $filters [1]->getTableName (), 'Table names do not match');
			$this->assertEquals ('2017-12-31', $filters [1]->getValue (), 'Values do not match');
		}

		/**
		 * Actualizar una vista simple, agregar filtros, cambiar columnas y valores básicos
		 * @depends testFetchView
		 */
		public function testUpdateView () {
			$moduleName = 'test_module';
			$tableName  = 'vtiger_test_module';
			$viewName   = 'Test view # 1';
			$view       = ViewManager::getInstance (self::$adb)->fetchView ($moduleName, $viewName);
			$oldViewId  = $view->getId ();

			// Verificar que se obtuvo la vista
			$this->assertNotNull ($view, 'View should not be null');

			// Data para cambios
			$newField = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('datetime_field')->setName ('datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName);

			// Data original
			/** @var Field[] $fields */
			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('code_field')->setName ('code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('text_field')->setName ('text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('number_field')->setName ('number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('date_field')->setName ('date_field')->setLabel ('My date field')->setModuleName ($moduleName)->setTableName ($tableName),
			);

			// Modificar la vista
			$view->setAdvancedFilterGroups (array (
				ViewAdvancedFilterGroup::getInstance ()
					->setViewId ($view->getId ())
					->setSequence (0)
					->setFilters (array (
						ViewAdvancedFilter::getInstance ($newField)
							->setComparator (ViewAdvancedFilterInterface::COMPARATOR_GREATER)
							->setGroupId (0)
							->setSequence (0)
							->setValue ('2017-01-01')
							->setViewId ($view->getId ()),
					)),
				))
				->setColumns (array (
					ViewColumn::getInstance ($fields [0])->setSequence (0)->setViewId ($view->getId ()),
					ViewColumn::getInstance ($fields [2])->setSequence (1)->setViewId ($view->getId ()),
					ViewColumn::getInstance ($newField)->setSequence (2)->setViewId ($view->getId ()),
				))
				->setDefault (ViewInterface::DEFAULT_NO)
				->setName ('My new view')
				->setOwner (2)
				->setShowCountInMenu (ViewInterface::SHOW_COUNT_YES)
				->setStandardFilter (
					ViewStandardFilter::getInstance ($newField)
						->setEndDate ('2018-12-31')
						->setPeriod (ViewStandardFilterInterface::PERIOD_NEXT_YEAR)
						->setStartDate ('2018-01-01')
						->setViewId ($view->getId ())
				)
				->setStatus (ViewInterface::STATUS_PENDING);
			$savedView = ViewManager::getInstance (self::$adb)->saveView ($view);

			$this->assertNotNull ($savedView, 'Saved view should not be null');
			$this->assertNotEmpty ($savedView->getId (), 'Saved view ID should not be empty');
			$this->assertEquals ($oldViewId, $savedView->getId (), 'Saved view IDs do not match');

			// Verificar que se actualizó la vista en la base de datos
			$viewId = $savedView->getId ();
			$result = self::$adb->pquery ('SELECT * FROM vtiger_customview WHERE cvid=?', array ($viewId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved view not found in database');

			// Verificar que se actualizaron correctamente los datos de la vista en la base de datos
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('My new view', $row ['viewname'], 'View names do not match');
			$this->assertEquals (ViewInterface::DEFAULT_NO, $row ['setdefault'], 'View default properties do not match');
			$this->assertEquals (ViewInterface::SHOW_COUNT_YES, $row ['setmetrics'], 'View showCountInMenu properties do not match');
			$this->assertEquals ($moduleName, $row ['entitytype'], 'View module names do not match');
			$this->assertEquals (ViewInterface::STATUS_PENDING, $row ['status'], 'View statuses do not match');
			$this->assertEquals (2, $row ['userid'], 'View owners do not match');

			// Verificar que se actualizaron correctamente los datos de las columnas en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvcolumnlist WHERE cvid=? ORDER BY columnindex', array ($viewId));
			$this->assertEquals (3, self::$adb->num_rows ($result), 'Saved view columns count do not match');

			// Primera columna
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [0]->getLabel ());
			$columnName = "{$fields [0]->getTableName ()}:{$fields [0]->getColumnName ()}:{$fields [0]->getName ()}:{$fields [0]->getModuleName ()}_{$label}:{$fields [0]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (0, $row ['columnindex'], 'Column indexes do not match');

			// Segunda columna
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $fields [2]->getLabel ());
			$columnName = "{$fields [2]->getTableName ()}:{$fields [2]->getColumnName ()}:{$fields [2]->getName ()}:{$fields [2]->getModuleName ()}_{$label}:{$fields [2]->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (1, $row ['columnindex'], 'Column indexes do not match');

			// Tercera columna
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $newField->getLabel ());
			$columnName = "{$newField->getTableName ()}:{$newField->getColumnName ()}:{$newField->getName ()}:{$newField->getModuleName ()}_{$label}:{$newField->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (2, $row ['columnindex'], 'Column indexes do not match');

			// Verificar que se creó el filtro estándar
			$standardFilter = $view->getStandardFilter ();
			$this->assertNotNull ($standardFilter, 'Saved view standard filter should not be null');
			$this->assertInstanceOf (ViewStandardFilter::class, $standardFilter, 'Saved view standard filter is not an instance of ViewStandardFilter');
			$this->assertNotEmpty ($standardFilter->getViewId (), 'Standard filter view ID should not be empty');

			// Verificar que se creó el filtro estándar en la base de datos
			$viewId = $savedView->getId ();
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvstdfilter WHERE cvid=?', array ($viewId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved view standard filters count do not match');

			// Verificar que se almacenaron correctamente los datos del filtro estándar
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $newField->getLabel ());
			$columnName = "{$newField->getTableName ()}:{$newField->getColumnName ()}:{$newField->getName ()}:{$newField->getModuleName ()}_{$label}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (ViewStandardFilterInterface::PERIOD_NEXT_YEAR, $row ['stdfilter'], 'Standard filter periods do not match');
			$this->assertEquals ('2018-01-01', $row ['startdate'], 'Standard filter start dates do not match');
			$this->assertEquals ('2018-12-31', $row ['enddate'], 'Standard filter end dates do not match');

			// Verificar que se crearon los grupos
			$groups = $view->getAdvancedFilterGroups ();
			$this->assertNotNull ($groups, 'Saved view advanced filters groups should not be null');
			$this->assertCount (1, $groups, 'Saved view advanced filters groups count do not match');
			$this->assertNotEmpty ($groups [0]->getViewId (), 'Advanced filter group view ID should not be empty');

			// Verificar que se crearon los filtros del grupo
			$filters = $groups [0]->getFilters ();
			$this->assertNotNull ($filters, 'Saved view advanced filter group filters should not be null');
			$this->assertCount (1, $filters, 'Saved view advanced filter group filters count do not match');
			$this->assertNotEmpty ($filters [0]->getViewId (), 'Advanced filter view ID should not be empty');

			// Verificar que se crearon los grupos en la base de datos
			$viewId = $savedView->getId ();
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid=?', array ($viewId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved view advanced filter groups count do not match');

			// Verificar que se almacenaron correctamente los datos del primer grupo
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ('', $row ['group_condition'], 'Group operators do not match');
			$this->assertEquals (' 0 ', $row ['condition_expression'], 'Group condition expresions do not match');

			// Verificar que se crearon los filtros avanzados en la base de datos
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=? ORDER BY columnindex', array ($viewId));
			$this->assertEquals (1, self::$adb->num_rows ($result), 'Saved view advanced filters count do not match');

			// Verificar que se almacenaron correctamente los datos del primer filtro
			$row        = self::$adb->fetchByAssoc ($result, -1, false);
			$label      = str_replace (' ', '_', $newField->getLabel ());
			$columnName = "{$newField->getTableName ()}:{$newField->getColumnName ()}:{$newField->getName ()}:{$newField->getModuleName ()}_{$label}:{$newField->getDataType ()}";
			$this->assertEquals ($columnName, $row ['columnname'], 'Column names do not match');
			$this->assertEquals (0, $row ['columnindex'], 'Comparators do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_GREATER, $row ['comparator'], 'Comparators do not match');
			$this->assertEquals ('2017-01-01', $row ['value'], 'Values do not match');
			$this->assertEquals (0, $row ['groupid'], 'Values do not match');
			$this->assertEquals ('', $row ['column_condition'], 'Values do not match');
		}

		/**
		 * Eliminar una vista
		 * @depends testUpdateView
		 */
		public function testDeleteView () {
			$moduleName = 'test_module';
			$viewName   = 'My new view';
			$view       = ViewManager::getInstance (self::$adb)->fetchView ($moduleName, $viewName);
			$viewId     = $view->getId ();

			// Verificar que se obtuvo la vista
			$this->assertNotNull ($view, 'View should not be null');

			ViewManager::getInstance (self::$adb)->deleteView ($view);

			// Verificar que se eliminó la vista
			$result = self::$adb->pquery ('SELECT * FROM vtiger_customview WHERE cvid=?', array ($viewId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View found in database');

			// Verificar que se eliminaron las columnas
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvcolumnlist WHERE cvid=?', array ($viewId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View columns found in database');

			// Verificar que se eliminó el filtro estandar
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvstdfilter WHERE cvid=?', array ($viewId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View standard filter found in database');

			// Verificar que se eliminaron los grupos de filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter_grouping WHERE cvid=?', array ($viewId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View advanced filter groups found in database');

			// Verificar que se eliminaron los filtros avanzados
			$result = self::$adb->pquery ('SELECT * FROM vtiger_cvadvfilter WHERE cvid=?', array ($viewId));
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View advanced filters found in database');
		}

		/**
		 * Eliminar las vistas de un módulo
		 * @depends testDeleteView
		 */
		public function testDeleteModuleViews () {
			$moduleName = 'test_module';

			ViewManager::getInstance (self::$adb)->deleteViews ($moduleName);

			// Verificar que se eliminaron las vista
			$result = self::$adb->query ('SELECT * FROM vtiger_customview');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View found in database');

			// Verificar que se eliminaron las columnas
			$result = self::$adb->query ('SELECT * FROM vtiger_cvcolumnlist');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View columns found in database');

			// Verificar que se eliminaron los filtros estandar
			$result = self::$adb->query ('SELECT * FROM vtiger_cvstdfilter');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View standard filter found in database');

			// Verificar que se eliminaron los grupos de filtros avanzados
			$result = self::$adb->query ('SELECT * FROM vtiger_cvadvfilter_grouping');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View advanced filter groups found in database');

			// Verificar que se eliminaron los filtros avanzados
			$result = self::$adb->query ('SELECT * FROM vtiger_cvadvfilter');
			$this->assertEquals (0, self::$adb->num_rows ($result), 'View advanced filters found in database');
		}

	}
	// @codingStandardsIgnoreEnd

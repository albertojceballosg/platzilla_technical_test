<?php
	require_once ('include/database/PearDatabase.php');
	require_once ('include/platzilla/Managers/ButtonManager.php');

	/**
	 * Prueba funcional de la clase ButtonManager
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ButtonManagerTest extends PHPUnit_Framework_TestCase {
		/** @var PearDatabase */
		private static $adb;

		/**
		 * Preparar la prueba:
		 * 1. Crear la base de datos de pruebas, platzilla_test
		 * 2. Establecer conexión global (self::$adb) a esa nueva base de datos
		 * 3. Crear tablas básicas: vtiger_tab, vtiger_custombuttons
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
				"CREATE TABLE `vtiger_custombuttons` (
					`custombuttonid` INT(11) NOT NULL AUTO_INCREMENT,
					`module` VARCHAR(30) NOT NULL,
					`action` VARCHAR(20) NOT NULL DEFAULT 'DetailView',
					`style` VARCHAR(255) NOT NULL,
					`label` VARCHAR(255) NOT NULL,
					`onclick` VARCHAR(200) NULL DEFAULT NULL,
					`link` VARCHAR(200) NULL DEFAULT NULL,
					`type` VARCHAR(10) NOT NULL,
					`description` TEXT NULL,
					`active` INT(1) NOT NULL DEFAULT '1',
					`runinnewwindow` TINYINT(4) NOT NULL DEFAULT '1',
					`locked` TINYINT(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`custombuttonid`)
				) COLLATE='utf8_general_ci' ENGINE=InnoDB"
			);

			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (1, 'test_module', 0, 1, 'Test module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
			self::$adb->query ("INSERT INTO `vtiger_tab` (`tabid`, `name`, `presence`, `tabsequence`, `tablabel`, `modifiedby`, `modifiedtime`, `customized`, `ownedby`, `isentitytype`, `version`, `parent`, `permite_filtros_listas`, `combinable`, `sends_notifications`, `avaliable`, `isplatzilla`, `in_administration`, `isvisibleinadmin`) VALUES (2, 'test_related_module', 0, 2, 'Test related module', NULL, NULL, 1, 0, 1, '0', 'Test Menu', 0, 0, 0, 1, 0, 1, 0)");
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
		 * Intentar crear un botón sin la información mínima necesaria
		 * Debe arrojar una ButtonException
		 */
		public function testCreateIncompleteButton () {
			$object = Button::getInstance ();
			$this->expectException (ButtonException::class);
			ButtonManager::getInstance (self::$adb)->saveButton ($object);
		}

		/**
		 * Intentar crear un botón asociado a un nombre de módulo no existente
		 * Debe arrojar una ButtonException
		 */
		public function testCreateNonExistingModuleNameButton () {
			$object = Button::getInstance ()
				->setAction ('doSomething();')
				->setLabel ('My test button')
				->setLocation (ButtonInterface::LOCATION_LIST_VIEW)
				->setModuleName ('unknown_module')
				->setStyle ('danger')
				->setType (ButtonInterface::TYPE_JAVASCRIPT);
			$this->expectException (ButtonException::class);
			ButtonManager::getInstance (self::$adb)->saveButton ($object);
		}

		/**
		 * Crear un botón válido
		 */
		public function testCreateValidButton () {
			$action      = 'doSomething();';
			$description = 'My super duper cuper button';
			$label       = 'My test button';
			$moduleName  = 'test_module';
			$style       = 'danger';

			$object      = Button::getInstance ()
				->setAction ($action)
				->setDescription ($description)
				->setIsActive (false)
				->setLabel ($label)
				->setLocation (ButtonInterface::LOCATION_LIST_VIEW)
				->setModuleName ($moduleName)
				->setRunInNewWindow (false)
				->setStyle ($style)
				->setType (ButtonInterface::TYPE_JAVASCRIPT);
			$savedObject = ButtonManager::getInstance (self::$adb)->saveButton ($object);

			// Verificar que el objeto existe y tiene ID
			$this->assertNotNull ($savedObject, 'Saved button should not be null');
			$this->assertNotEmpty ($savedObject->getId (), 'Saved button ID should not be null');

			// Verificar que el botón fue creado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.module=? AND b.label=?', array ($moduleName, $label));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que el botón contiene todos los valores suministrados
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['module'], 'Module names do not match');
			$this->assertEquals (ButtonInterface::LOCATION_LIST_VIEW, $row ['action'], 'Locations do not match');
			$this->assertEquals ($style, $row ['style'], 'Styles do not match');
			$this->assertEquals ($label, $row ['label'], 'Labels do not match');
			$this->assertEquals ($action, $row ['onclick'], 'OnClick properties do not match');
			$this->assertEquals (null, $row ['link'], 'Links do not match');
			$this->assertEquals (ButtonInterface::TYPE_JAVASCRIPT, $row ['type'], 'Types do not match');
			$this->assertEquals ($description, $row ['description'], 'Descriptions do not match');
			$this->assertEquals (0, $row ['active'], 'IsActive properties do not match');
			$this->assertEquals (0, $row ['runinnewwindow'], 'RunInNewWndow properties do not match');
		}

		/**
		 * Intentar obtener un botón no existente
		 */
		public function testFetchNonExistingButton () {
			$this->assertNull (ButtonManager::getInstance (self::$adb)->fetchButton (155));
		}

		/**
		 * Obtener un botón existente
		 * @depends testCreateValidButton
		 */
		public function testFetchExistingButton () {
			$object = ButtonManager::getInstance (self::$adb)->fetchButton (1);

			// Verificar que el objeto existe y tiene ID
			$this->assertNotNull ($object, 'Button should not be null');
			$this->assertNotEmpty ($object->getId (), 'Button ID should not be null');

			// Verificar que el botón contiene todos los valores suministrados
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.custombuttonid=?', array (1));
			$row    = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($row ['module'], $object->getModuleName (), 'Module names do not match');
			$this->assertEquals ($row ['action'], $object->getLocation (), 'Locations do not match');
			$this->assertEquals ($row ['style'], $object->getStyle (), 'Styles do not match');
			$this->assertEquals ($row ['label'], $object->getLabel (), 'Labels do not match');
			$this->assertEquals ($row ['onclick'], $object->getAction (), 'OnClick properties do not match');
			$this->assertEquals ($row ['type'], $object->getType (), 'Types do not match');
			$this->assertEquals ($row ['description'], $object->getDescription (), 'Descriptions do not match');
			$this->assertEquals ($row ['active'] == 1 ? true : false, $object->getIsActive (), 'IsActive properties do not match');
			$this->assertEquals ($row ['runinnewwindow'] == 1 ? true : false, $object->getRunInNewWindow (), 'RunInNewWndow properties do not match');
		}

		/**
		 * Actualizar un botón existente
		 * @depends testFetchExistingButton
		 */
		public function testUpdateExistingButton () {
			$action      = 'new action';
			$description = 'New description';
			$label       = 'New label';
			$moduleName  = 'test_module';
			$style       = 'new-style';

			$object = Button::getInstance ()
				->setAction ($action)
				->setDescription ($description)
				->setId (1)
				->setIsActive (true)
				->setLabel ($label)
				->setLocation (ButtonInterface::LOCATION_EDIT_VIEW)
				->setModuleName ($moduleName)
				->setRunInNewWindow (true)
				->setStyle ($style)
				->setType (ButtonInterface::TYPE_LINK);
			ButtonManager::getInstance (self::$adb)->saveButton ($object);

			// Verificar que el botón fue creado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.custombuttonid=?', array (1));
			$this->assertEquals (1, self::$adb->num_rows ($result));

			// Verificar que el botón contiene todos los valores suministrados
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals ($moduleName, $row ['module'], 'Module names do not match');
			$this->assertEquals (ButtonInterface::LOCATION_EDIT_VIEW, $row ['action'], 'Locations do not match');
			$this->assertEquals ($style, $row ['style'], 'Styles do not match');
			$this->assertEquals ($label, $row ['label'], 'Labels do not match');
			$this->assertEquals (null, $row ['onclick'], 'OnClick properties do not match');
			$this->assertEquals ($action, $row ['link'], 'Links do not match');
			$this->assertEquals (ButtonInterface::TYPE_LINK, $row ['type'], 'Types do not match');
			$this->assertEquals ($description, $row ['description'], 'Descriptions do not match');
			$this->assertEquals (1, $row ['active'], 'IsActive properties do not match');
			$this->assertEquals (1, $row ['runinnewwindow'], 'RunInNewWndow properties do not match');
		}

		/**
		 * Obtener los botones existentes para un módulo existente. Agregar dos botones más previamente
		 * @depends testUpdateExistingButton
		 */
		public function testFetchExistingButtons () {
			$moduleName  = 'test_module';
			ButtonManager::getInstance (self::$adb)->saveButton (
				Button::getInstance ()
					->setAction ('/index.php?module=backgroundtasks&action=RunTask&taskname=Convertir+potencial+cliente+en+cliente&record=[record]&return_module=[module]&return_action=[action]&return_record=[record]&Ajax=true')
					->setDescription ('Another super duper cuper button')
					->setIsActive (true)
					->setLabel ('Another test button')
					->setLocation (ButtonInterface::LOCATION_DETAIL_VIEW)
					->setModuleName ($moduleName)
					->setRunInNewWindow (false)
					->setStyle ('success')
					->setType (ButtonInterface::TYPE_LINK)
			);
			ButtonManager::getInstance (self::$adb)->saveButton (
				Button::getInstance ()
					->setAction ('/index.php')
					->setDescription ('Go home')
					->setIsActive (true)
					->setLabel ('Home')
					->setLocation (ButtonInterface::LOCATION_DETAIL_VIEW)
					->setModuleName ($moduleName)
					->setRunInNewWindow (false)
					->setStyle ('primary')
					->setType (ButtonInterface::TYPE_LINK)
			);

			// Obtener los botones
			$objects = ButtonManager::getInstance (self::$adb)->fetchButtons ($moduleName);

			// Verificar que el objeto existe y tiene ID
			$this->assertNotNull ($objects, 'Buttons should not be null');
			$this->assertCount (3, $objects, 'Buttons count do not match');
		}

		/**
		 * Eliminar un botón existente
		 * @depends testFetchExistingButtons
		 */
		public function testDeleteButton () {
			$object = Button::getInstance ()
				->setId (1);
			ButtonManager::getInstance (self::$adb)->deleteButton ($object);

			// Verificar que el botón fue eliminado correctamente en la base de datos
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.custombuttonid=?', array (1));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

		/**
		 * Crear nuevos botones para el módulo. Deben eliminarse los existentes excepto el segundo
		 * @depends testDeleteButton
		 */
		public function testSaveModuleButtons () {
			$moduleName = 'test_module';

			// Verificar que aun quedan registrados dos botones para ese módulo
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.module=?', array ($moduleName));
			$this->assertEquals (2, self::$adb->num_rows ($result));

			// Actualizar los botones del módulo
			$buttons = array (
				Button::getInstance ()
					->setAction ('doSomethingOne();')
					->setDescription ('My super duper cuper button # 1')
					->setIsActive (false)
					->setLabel ('My new button # 1')
					->setLocation (ButtonInterface::LOCATION_LIST_VIEW)
					->setModuleName ($moduleName)
					->setRunInNewWindow (false)
					->setStyle ('danger')
					->setType (ButtonInterface::TYPE_JAVASCRIPT),
				Button::getInstance ()
					->setAction ('/index.php')
					->setDescription ('Go home')
					->setId (3)
					->setIsActive (true)
					->setLabel ('Home')
					->setLocation (ButtonInterface::LOCATION_DETAIL_VIEW)
					->setModuleName ($moduleName)
					->setRunInNewWindow (false)
					->setStyle ('primary')
					->setType (ButtonInterface::TYPE_LINK),
				Button::getInstance ()
					->setAction ('doSomethingThree();')
					->setDescription ('My super duper cuper button # 2')
					->setIsActive (false)
					->setLabel ('My new button # 3')
					->setLocation (ButtonInterface::LOCATION_LIST_VIEW)
					->setModuleName ($moduleName)
					->setRunInNewWindow (false)
					->setStyle ('danger')
					->setType (ButtonInterface::TYPE_JAVASCRIPT),
			);
			ButtonManager::getInstance (self::$adb)->saveButtons ($moduleName, $buttons);

			// Verificar que siguen registrados dos botones para ese módulo
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.module=?', array ($moduleName));
			$this->assertEquals (3, self::$adb->num_rows ($result));

			// Verificar el primer botón
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.module=? AND b.label=?', array ($moduleName, 'My new button # 1'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
			// Verificar que el segundo botón existe
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.module=? AND b.label=?', array ($moduleName, 'Home'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
			// Verificar que el segundo botón es el mismo que estaba inicialmente guardado
			$row = self::$adb->fetchByAssoc ($result, -1, false);
			$this->assertEquals (3, $row ['custombuttonid']);
			// Verificar el tercer botón
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.module=? AND b.label=?', array ($moduleName, 'My new button # 3'));
			$this->assertEquals (1, self::$adb->num_rows ($result));
		}

		/**
		 * Eliminar todos los botones del módulo
		 * @depends testSaveModuleButtons
		 */
		public function testDeleteButtons () {
			$moduleName = 'test_module';

			// Verificar que aun quedan registrados tres botones para ese módulo
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.module=?', array ($moduleName));
			$this->assertEquals (3, self::$adb->num_rows ($result));

			// Eliminar los botones
			ButtonManager::getInstance (self::$adb)->deleteButtons ($moduleName);

			// Verificar que se eliminaron los botones
			$result = self::$adb->pquery ('SELECT b.* FROM vtiger_custombuttons b WHERE b.module=?', array ($moduleName));
			$this->assertEquals (0, self::$adb->num_rows ($result));
		}

	}
	// @codingStandardsIgnoreEnd

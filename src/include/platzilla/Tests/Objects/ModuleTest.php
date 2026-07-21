<?php
	require_once ('include/platzilla/Objects/Module.php');

	/**
	 * Prueba unitaria de la clase Module
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ModuleTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object    = Module::getInstance ();
			$testClass = get_class ($object);
			/** @noinspection SqlResolve */
			$testProperties = [
				'id'                 => 8,
				'entityIdentifier'   => 'identifier_field',
				'label'              => 'My module',
				'menuLabel'          => 'My menu label',
				'name'               => 'my_module_name',
				'presence'           => array (ModuleInterface::PRESENCE_ALWAYS_HIDDEN, ModuleInterface::PRESENCE_HIDDEN, ModuleInterface::PRESENCE_USER_DEFINED, ModuleInterface::PRESENCE_VISIBLE),
				'sequence'           => 2,
				'showInAdminConsole' => array (true, false),
				'type'               => array (ModuleInterface::TYPE_ADMIN, ModuleInterface::TYPE_TOOL, ModuleInterface::TYPE_USER),
			];

			foreach ($testProperties as $propertyName => $propertyValues) {
				$propertyName = ucfirst ($propertyName);
				$getter       = "get{$propertyName}";
				$setter       = "set{$propertyName}";

				if (is_array ($propertyValues)) {
					foreach ($propertyValues as $propertyValue) {
						$result = $object->{$setter} ($propertyValue);
						$this->assertNotNull ($result, "Method {$setter} does not return an instance of {$testClass}. Got null");
						$this->assertTrue (is_object ($result), "Method {$setter} does not return an object");
						$this->assertInstanceOf ($testClass, $result, "Method {$setter} does not return an instance of {$testClass}. Got " . get_class ($result));
						$value = $object->{$getter} ();
						if (is_object ($propertyValue)) {
							$this->assertTrue (is_object ($value), "Method {$getter} does not return an object");
							$this->assertInstanceOf (get_class ($propertyValue), $value, "Method {$getter} does not return an instance of " . get_class ($propertyValue) . '. Got ' . get_class ($value));
						} else {
							$this->assertFalse (is_object ($value), "Method {$getter} returns an object");
							$this->assertEquals ($propertyValue, $value, "{$testClass} {$getter} does not work. Expected {$value}. Got {$value}");
						}
					}
				} else {
					$result = $object->{$setter} ($propertyValues);
					$this->assertNotNull ($result, "Method {$setter} does not return an instance of {$testClass}. Got null");
					$this->assertTrue (is_object ($result), "Method {$setter} does not return an object");
					$this->assertInstanceOf ($testClass, $result, "Method {$setter} does not return an instance of {$testClass}. Got " . get_class ($result));
					$value = $object->{$getter} ();
					if (is_object ($propertyValues)) {
						$this->assertTrue (is_object ($value), "Method {$getter} does not return an object");
						$this->assertInstanceOf (get_class ($propertyValues), $value, "Method {$getter} does not return an instance of " . get_class ($propertyValues) . '. Got ' . get_class ($value));
					} else {
						$this->assertFalse (is_object ($value), "Method {$getter} returns an object");
						$this->assertEquals ($propertyValues, $value, "{$testClass} {$getter} does not work. Expected {$value}. Got {$value}");
					}
				}
			}
		}

		public function testEntityTypeGetters () {
			$object = Module::getInstance (true, 'TM-', '0001');
			$this->assertEquals (true, $object->getIsEntityType (), 'Entity type properties do not match');
			$this->assertEquals ('TM-', $object->getEntityPrefix (), 'Entity prefixes do not match');
			$this->assertEquals ('0001', $object->getEntityInitialSequence (), 'Entity sequences do not match');

			$object = Module::getInstance ();
			$this->assertEquals (false, $object->getIsEntityType (), 'Entity type properties do not match');
			$this->assertEquals (null, $object->getEntityPrefix (), 'Entity prefix should be null');
			$this->assertEquals (null, $object->getEntityInitialSequence (), 'Entity sequence shoud be null');
		}

		public function testBlocksGetterAndSetter () {
			$blocks = array (Block::getInstance (), Block::getInstance (), Block::getInstance ());
			$object = Module::getInstance (true, 'TM-', '0001')->setBlocks ($blocks);
			$this->assertEquals ($blocks, $object->getBlocks (), 'Blocks do not match');
		}

		public function testButtonsGetterAndSetter () {
			$buttons = array (Button::getInstance (), Button::getInstance (), Button::getInstance ());
			$object  = Module::getInstance (true, 'TM-', '0001')->setButtons ($buttons);
			$this->assertEquals ($buttons, $object->getButtons (), 'Buttons do not match');

			$object = Module::getInstance ()->setButtons ($buttons);
			$this->assertEquals ($buttons, $object->getButtons (), 'Buttons do not match');
		}

		public function testChartsGetterAndSetter () {
			$charts = array (Chart::getInstance (), Chart::getInstance (), Chart::getInstance ());
			$object = Module::getInstance (true, 'TM-', '0001')->setCharts ($charts);
			$this->assertEquals ($charts, $object->getCharts (), 'Charts do not match');

			$object = Module::getInstance ()->setCharts ($charts);
			$this->assertEquals ($charts, $object->getCharts (), 'Charts do not match');
		}

		public function testViewsGetterAndSetter () {
			$views  = array (View::getInstance (), View::getInstance (), View::getInstance ());
			$object = Module::getInstance (true, 'TM-', '0001')->setViews ($views);
			$this->assertEquals ($views, $object->getViews (), 'Views do not match');
		}

		public function testEmptyLabelValidation () {
			$object = Module::getInstance ();
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_EMPTY_LABEL);
			$object->validate ();
		}

		public function testEmptyNameValidation () {
			$object = Module::getInstance ()
				->setLabel ('My module');
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_EMPTY_NAME);
			$object->validate ();
		}

		public function testInvalidNameValidation () {
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my module');
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_NAME);
			$object->validate ();
		}

		public function testEmptyPresenceValidation () {
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my_module');
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_EMPTY_PRESENCE);
			$object->validate ();
		}

		public function testInvalidSequenceValidation () {
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence ('coco');
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_SEQUENCE);
			$object->validate ();
		}

		public function testEmptyTypeValidation () {
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0);
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_EMPTY_TYPE);
			$object->validate ();
		}

		public function testInvalidEntitySequencePrefixValidation () {
			$object = Module::getInstance (true, 'MYM-', 'NOT_A_NUMBER')
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER);
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_ENTITY_SEQUENCE);
			$object->validate ();
		}

		public function testEmptyBlocksValidation () {
			$object = Module::getInstance (true, 'MYM-', '0001')
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER);
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_EMPTY_BLOCKS);
			$object->validate ();
		}

		public function testInvalidBlocksValidation () {
			/** @noinspection PhpParamsInspection */
			$object = Module::getInstance (true, 'MYM-', '0001')
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setBlocks (Block::getInstance ());
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_BLOCKS);
			$object->validate ();
		}

		public function testNotABlockValidation () {
			$object = Module::getInstance (true, 'MYM-', '0001')
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setBlocks (array (new stdClass ()));
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_BLOCK);
			$object->validate ();
		}

		public function testInvalidBlockValidation () {
			$object = Module::getInstance (true, 'MYM-', '0001')
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setBlocks (array (Block::getInstance ()));
			$this->expectException (BlockException::class);
			$object->validate ();
		}

		public function testTooManyCodeFieldsValidation () {
			$moduleName = 'my_module';
			$block      = Block::getInstance ()
				->setLabel ('My block label')
				->setModuleName ($moduleName)
				->setFields (array (
					Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE),
					Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE),
				));
			$object     = Module::getInstance (true, 'MYM-', '0001')
				->setLabel ('My module')
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setBlocks (array ($block));
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_CODE_FIELD);
			$object->validate ();
		}

		public function testInvalidEntityIdentifierValidation () {
			$moduleName = 'my_module';
			$block      = Block::getInstance ()
				->setLabel ('My block label')
				->setModuleName ($moduleName)
				->setFields (array (
					Field::getInstance ()->setName ('my_field')->setUiType (FieldInterface::UI_TYPE_CODE),
				));
			$object     = Module::getInstance (true, 'MYM-', '0001')
				->setLabel ('My module')
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setBlocks (array ($block))
				->setEntityIdentifier ('unknown_field');
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_ENTITY_IDENTIFIER);
			$object->validate ();
		}

		public function testInvalidButtonsValidation () {
			/** @noinspection PhpParamsInspection */
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setButtons (Button::getInstance ());
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_BUTTONS);
			$object->validate ();
		}

		public function testNotAButtonValidation () {
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setButtons (array (new stdClass ()));
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_BUTTON);
			$object->validate ();
		}

		public function testInvalidButtonValidation () {
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setButtons (array (Button::getInstance ()));
			$this->expectException (ButtonException::class);
			$object->validate ();
		}

		public function testInvalidChartsValidation () {
			/** @noinspection PhpParamsInspection */
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setCharts (Chart::getInstance ());
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_CHARTS);
			$object->validate ();
		}

		public function testNotAChartValidation () {
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setCharts (array (new stdClass ()));
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_CHART);
			$object->validate ();
		}

		public function testInvalidChartValidation () {
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setCharts (array (Chart::getInstance ()));
			$this->expectException (ChartException::class);
			$object->validate ();
		}

		public function testInvalidViewsValidation () {
			$moduleName = 'my_module';
			$block      = Block::getInstance ()
				->setLabel ('My block label')
				->setModuleName ($moduleName)
				->setFields (array (
					Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE),
				));
			/** @noinspection PhpParamsInspection */
			$object = Module::getInstance (true, 'MYM-', '0001')
				->setLabel ('My module')
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setBlocks (array ($block))
				->setViews (new stdClass ());
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_VIEWS);
			$object->validate ();
		}

		public function testNotAViewValidation () {
			$moduleName = 'my_module';
			$block      = Block::getInstance ()
				->setLabel ('My block label')
				->setModuleName ($moduleName)
				->setFields (array (
					Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE),
				));
			$object     = Module::getInstance (true, 'MYM-', '0001')
				->setLabel ('My module')
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setBlocks (array ($block))
				->setViews (array (new stdClass ()));
			$this->expectException (ModuleException::class);
			$this->expectExceptionMessage (ModuleException::ERROR_MODULE_INVALID_VIEW);
			$object->validate ();
		}

		public function testInvalidViewValidation () {
			$moduleName = 'my_module';
			$block      = Block::getInstance ()
				->setLabel ('My block label')
				->setModuleName ($moduleName)
				->setFields (array (
					Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE),
				));
			$object     = Module::getInstance (true, 'MYM-', '0001')
				->setLabel ('My module')
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setBlocks (array ($block))
				->setViews (array (View::getInstance ()));
			$this->expectException (ViewException::class);
			$object->validate ();
		}

		public function testValidNonEntityTypeModule () {
			$object = Module::getInstance ()
				->setLabel ('My module')
				->setName ('my_module')
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER);
			$object->validate ();
		}

		public function testValidEntityTypeModule () {
			$moduleName = 'my_module';
			$object     = Module::getInstance (true, 'MYM-', '0001')
				->setLabel ('My module')
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_VISIBLE)
				->setSequence (0)
				->setType (ModuleInterface::TYPE_USER)
				->setBlocks (array (
					Block::getInstance ()
						->setLabel ('My block label')
						->setModuleName ($moduleName)
						->setFields (array (
							Field::getInstance ()
								->setColumnName ('my_code_field')
								->setLabel ('My code field')
								->setModuleName ($moduleName)
								->setName ('my_code_field')
								->setUiType (FieldInterface::UI_TYPE_CODE),
						)),
				))
				->setViews (array (
					View::getInstance ()
						->setColumns (array (
							ViewColumn::getInstance ()
								->setColumnName ('my_code_field')
								->setDataType (FieldInterface::DATA_TYPE_VARCHAR)
								->setFieldName ('my_code_field')
								->setLabel ('My code field')
								->setModuleName ($moduleName)
								->setSequence (0)
								->setTableName ('vtiger_my_module'),
						))
						->setModuleName ($moduleName)
						->setName ('my_view')
						->setOwner (1),
				));
			$object->validate ();
		}

		public function testDuplicate () {
			$blockId     = 10;
			$blockLabel  = 'My test block';
			$fieldId     = 1;
			$fieldLabel  = 'My text field';
			$fieldName   = 'text_field';
			$menuLabel   = 'Entradas';
			$moduleId    = 2;
			$moduleName  = 'user_module';
			$moduleLabel = 'My user module';
			$viewId      = 10;
			$viewLabel   = 'All';

			$field = Field::getInstance ()
				->setId ($fieldId)
				->setBlockId ($blockId)
				->setColumnName ($fieldName)
				->setLabel ($fieldLabel)
				->setMandatory (true)
				->setModuleName ($moduleName)
				->setName ($fieldName)
				->setUiType (FieldInterface::UI_TYPE_TEXT)
				->setDisplayType (FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_CUSTOM)
				->setMassEditable (FieldInterface::MASS_EDITABLE_DISABLED)
				->setPresence (FieldInterface::PRESENCE_VISIBLE)
				->setQuickCreate (FieldInterface::QUICK_CREATE_DISABLED)
				->setReadOnly (FieldInterface::READ_WRITE);

			$object = Module::getInstance (true, 'USM-', '0000001')
				->setId ($moduleId)
				->setBlocks (array (
					Block::getInstance ()
						->setId ($blockId)
						->setLabel ($blockLabel)
						->setFields (array ($field)),
				))
				->setEntityIdentifier ($fieldName)
				->setLabel ($moduleLabel)
				->setMenuLabel ($menuLabel)
				->setName ($moduleName)
				->setPresence (ModuleInterface::PRESENCE_USER_DEFINED)
				->setSequence (4)
				->setShowInAdminConsole (true)
				->setType (ModuleInterface::TYPE_USER)
				->setViews (array (
					View::getInstance ()
						->setId ($viewId)
						->setColumns (array (ViewColumn::getInstance ($field)->setSequence (0)->setViewId ($viewId)))
						->setDefault (ViewInterface::DEFAULT_YES)
						->setName ($viewLabel)
						->setOwner (10)
						->setShowCountInMenu (ViewInterface::SHOW_COUNT_NO)
						->setStatus (ViewInterface::STATUS_PUBLIC),
				));

			$duplicatedObject = $object->duplicate (false, false, false);
			$this->assertEquals ($moduleId, $duplicatedObject->getId (), 'IDs do not match');
			$this->assertEquals ($fieldName, $duplicatedObject->getEntityIdentifier (), 'Entity identifiers do not match');
			$this->assertEquals ('USM-', $duplicatedObject->getEntityPrefix (), 'Entity prefixes do not match');
			$this->assertEquals ('0000001', $duplicatedObject->getEntityInitialSequence (), 'Entity sequences do not match');
			$this->assertEquals (true, $duplicatedObject->getIsEntityType (), 'Entity types do not match');
			$this->assertEquals ($moduleLabel, $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals ($menuLabel, $duplicatedObject->getMenuLabel (), 'Menu labels do not match');
			$this->assertEquals ($moduleName, $duplicatedObject->getName (), 'Names do not match');
			$this->assertEquals (ModuleInterface::PRESENCE_USER_DEFINED, $duplicatedObject->getPresence (), 'Presences do not match');
			$this->assertEquals (4, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals (true, $duplicatedObject->getShowInAdminConsole (), 'Show in admin console properties do not match');
			$this->assertEquals (ModuleInterface::TYPE_USER, $duplicatedObject->getType (), 'Types do not match');

			$blocks = $duplicatedObject->getBlocks ();
			$this->assertCount (1, $blocks, 'Blocks count do not match');
			$this->assertEquals ($blockId, $blocks [0]->getId (), 'Block IDs do not match');

			$fields = $duplicatedObject->getFields ();
			$this->assertCount (1, $fields, 'Fields count do not match');
			$this->assertEquals ($fieldId, $fields [0]->getId (), 'Field IDs do not match');
			$this->assertEquals ($blockId, $fields [0]->getBlockId (), 'Field block IDs do not match');

			$views = $duplicatedObject->getViews ();
			$this->assertCount (1, $views, 'Fields count do not match');
			$this->assertEquals ($viewId, $views [0]->getId (), 'View IDs do not match');
			$this->assertEquals (10, $views [0]->getOwner (), 'View owners do not match');

			$duplicatedObject = $object->duplicate (true, true, true);
			$this->assertEquals (null, $duplicatedObject->getId (), 'IDs do not match');
			$this->assertEquals ($fieldName, $duplicatedObject->getEntityIdentifier (), 'Entity identifiers do not match');
			$this->assertEquals ('USM-', $duplicatedObject->getEntityPrefix (), 'Entity prefixes do not match');
			$this->assertEquals ('0000001', $duplicatedObject->getEntityInitialSequence (), 'Entity sequences do not match');
			$this->assertEquals (true, $duplicatedObject->getIsEntityType (), 'Entity types do not match');
			$this->assertEquals ($moduleLabel, $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals ($menuLabel, $duplicatedObject->getMenuLabel (), 'Menu labels do not match');
			$this->assertEquals ($moduleName, $duplicatedObject->getName (), 'Names do not match');
			$this->assertEquals (ModuleInterface::PRESENCE_ALWAYS_HIDDEN, $duplicatedObject->getPresence (), 'Presences do not match');
			$this->assertEquals (4, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals (true, $duplicatedObject->getShowInAdminConsole (), 'Show in admin console properties do not match');
			$this->assertEquals (ModuleInterface::TYPE_USER, $duplicatedObject->getType (), 'Types do not match');

			$blocks = $duplicatedObject->getBlocks ();
			$this->assertCount (1, $blocks, 'Blocks count do not match');
			$this->assertEquals (null, $blocks [0]->getId (), 'Block IDs do not match');

			$fields = $duplicatedObject->getFields ();
			$this->assertCount (1, $fields, 'Fields count do not match');
			$this->assertEquals (null, $fields [0]->getId (), 'Field IDs do not match');
			$this->assertEquals (null, $fields [0]->getBlockId (), 'Field block IDs do not match');

			$views = $duplicatedObject->getViews ();
			$this->assertCount (1, $views, 'Fields count do not match');
			$this->assertEquals (null, $views [0]->getId (), 'View IDs do not match');
			$this->assertEquals (1, $views [0]->getOwner (), 'View owners do not match');
		}

	}
	// @codingStandardsIgnoreEnd

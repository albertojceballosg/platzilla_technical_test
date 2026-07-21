<?php
	require_once ('include/platzilla/Objects/Block.php');

	/**
	 * Prueba unitaria de la clase Block
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class BlockTest extends PHPUnit_Framework_TestCase {

		public function testGettersAndSetters () {
			$object         = Block::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'         => 8,
				'isCustom'   => array (BlockInterface::IS_CUSTOM_NO, BlockInterface::IS_CUSTOM_YES),
				'label'      => 'My label',
				'moduleName' => 'my_module_name',
				'sequence'   => 41,
				'showTitle'  => array (BlockInterface::SHOW_TITLE_NO, BlockInterface::SHOW_TITLE_YES),
			];

			foreach ($testProperties as $propertyName => $propertyValues) {
				$propertyName = ucfirst ($propertyName);
				$getter       = is_bool ($propertyValues) ? "is{$propertyName}" : "get{$propertyName}";
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

		public function testVisibilityGetterAndSetter () {
			$testProperties = [
				'visibility'   => array (BlockInterface::VISIBILITY_HIDDEN, BlockInterface::VISIBILITY_VISIBLE),
				'inCreateView' => array (BlockInterface::VISIBILITY_HIDDEN, BlockInterface::VISIBILITY_VISIBLE),
				'inDetailView' => array (BlockInterface::VISIBILITY_HIDDEN, BlockInterface::VISIBILITY_VISIBLE),
				'inEditView'   => array (BlockInterface::VISIBILITY_HIDDEN, BlockInterface::VISIBILITY_VISIBLE),
			];

			$object = Block::getInstance ();
			foreach ($testProperties ['visibility'] as $visibility) {
				$result = $object->setVisibility ($visibility);
				$this->assertNotNull ($result, 'Method setVisibility does not return an instance of Block. Got null');
				$this->assertInstanceOf (Block::class, $result, 'Method setVisibility does not return an instance of Block. Got ' . get_class ($result));
				$this->assertEquals ($visibility, $object->getVisibility (), 'Visibilities do not match');
				$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $object->getVisibilityInCreateView (), 'Visibilities in create view do not match');
				$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $object->getVisibilityInDetailView (), 'Visibilities in detail view do not match');
				$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $object->getVisibilityInEditView (), 'Visibilities in edit view do not match');
			}

			foreach ($testProperties ['inCreateView'] as $visibility) {
				$object->setVisibility (BlockInterface::VISIBILITY_HIDDEN, $visibility);
				$this->assertEquals ($visibility, $object->getVisibilityInCreateView (), 'Visibilities in create view do not match');
				$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $object->getVisibilityInDetailView (), 'Visibilities in detail view do not match');
				$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $object->getVisibilityInEditView (), 'Visibilities in edit view do not match');
			}

			foreach ($testProperties ['inDetailView'] as $visibility) {
				$object->setVisibility (BlockInterface::VISIBILITY_HIDDEN, BlockInterface::VISIBILITY_HIDDEN, $visibility);
				$this->assertEquals ($visibility, $object->getVisibilityInDetailView (), 'Visibilities in detail view do not match');
				$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $object->getVisibilityInEditView (), 'Visibilities in edit view do not match');
			}

			foreach ($testProperties ['inEditView'] as $visibility) {
				$object->setVisibility (BlockInterface::VISIBILITY_HIDDEN, BlockInterface::VISIBILITY_HIDDEN, BlockInterface::VISIBILITY_HIDDEN, $visibility);
				$this->assertEquals ($visibility, $object->getVisibilityInEditView (), 'Visibilities in edit view do not match');
			}

		}

		public function testEmptyLabelValidation () {
			$object = Block::getInstance ();
			$this->expectException (BlockException::class);
			$this->expectExceptionMessage (BlockException::ERROR_BLOCK_EMPTY_LABEL);
			$object->validate ();
		}

		public function testInvalidFieldsValidation () {
			/** @noinspection PhpParamsInspection */
			$object = Block::getInstance ()
				->setLabel ('My block label')
				->setModuleName ('my_module_name')
				->setFields (new stdClass ());
			$this->expectException (BlockException::class);
			$this->expectExceptionMessage (BlockException::ERROR_BLOCK_INVALID_FIELDS);
			$object->validate ();
		}

		public function testInvalidFieldValidation () {
			/** @noinspection PhpParamsInspection */
			$object = Block::getInstance ()
				->setId (5)
				->setLabel ('My block label')
				->setModuleName ('my_module_name')
				->setFields (array (null, null, null));
			$this->expectException (BlockException::class);
			$this->expectExceptionMessage (BlockException::ERROR_BLOCK_INVALID_FIELD);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = Block::getInstance ()
				->setLabel ('My block label')
				->setModuleName ('my_module_name')
				->setFields (array (
					Field::getInstance ()
				));
			$object->validate ();
		}

		public function testDuplicate () {
			$object = Block::getInstance ()
				->setId (1596)
				->setIsCustom (BlockInterface::IS_CUSTOM_YES)
				->setLabel ('My block label')
				->setModuleName ('my_module_name')
				->setSequence (8)
				->setShowTitle (BlockInterface::SHOW_TITLE_NO)
				->setVisibility (BlockInterface::VISIBILITY_HIDDEN, BlockInterface::VISIBILITY_HIDDEN, BlockInterface::VISIBILITY_HIDDEN, BlockInterface::VISIBILITY_HIDDEN)
				->setFields (array (
					Field::getInstance ()
						->setId (2015)
						->setBlockId (1596)
						->setColumnName ('column_name_one')
						->setLabel ('Field label one')
						->setModuleName ('my_module_name')
						->setName ('field_one')
						->setUiType (FieldInterface::UI_TYPE_TEXT, 100),
					Field::getInstance ()
						->setId (2025)
						->setBlockId (1596)
						->setColumnName ('column_name_two')
						->setLabel ('Field label two')
						->setModuleName ('my_module_name')
						->setName ('field_two')
						->setUiType (FieldInterface::UI_TYPE_TEXT, 1024),
				));

			$duplicatedObject = $object->duplicate ($object->getId ());
			$this->assertEquals (1596, $duplicatedObject->getId (), 'IDs do not match');
			$this->assertEquals ('My block label', $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals (8, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals (BlockInterface::SHOW_TITLE_NO, $duplicatedObject->getShowTitle (), 'Show title properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_HIDDEN, $duplicatedObject->getVisibility (), 'Visibilities do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $duplicatedObject->getDisplayStatus (), 'Display statuses do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_HIDDEN, $duplicatedObject->getVisibilityInCreateView (), 'Create view visibilities do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_HIDDEN, $duplicatedObject->getVisibilityInDetailView (), 'Detail view visibilities do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_HIDDEN, $duplicatedObject->getVisibilityInEditView (), 'Edit view visibilities do not match');
			$this->assertEquals (BlockInterface::IS_CUSTOM_YES, $duplicatedObject->getIsCustom (), 'Is custom properties do not match');

			$fields = $duplicatedObject->getFields ();
			$this->assertCount (2, $fields, 'Fields count do not match');
			foreach ($fields as $field) {
				$this->assertEquals (1596, $field->getBlockId (), 'Field block IDs do not match');
				$this->assertNotNull ($field->getId (), 'Field IDs do not match');
			}

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals (null, $duplicatedObject->getId (), 'IDs do not match');
			$this->assertEquals ('My block label', $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals (8, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals (BlockInterface::SHOW_TITLE_NO, $duplicatedObject->getShowTitle (), 'Show title properties do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_HIDDEN, $duplicatedObject->getVisibility (), 'Visibilities do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_VISIBLE, $duplicatedObject->getDisplayStatus (), 'Display statuses do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_HIDDEN, $duplicatedObject->getVisibilityInCreateView (), 'Create view visibilities do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_HIDDEN, $duplicatedObject->getVisibilityInDetailView (), 'Detail view visibilities do not match');
			$this->assertEquals (BlockInterface::VISIBILITY_HIDDEN, $duplicatedObject->getVisibilityInEditView (), 'Edit view visibilities do not match');
			$this->assertEquals (BlockInterface::IS_CUSTOM_YES, $duplicatedObject->getIsCustom (), 'Is custom properties do not match');

			$fields = $duplicatedObject->getFields ();
			$this->assertCount (2, $fields, 'Fields count do not match');
			foreach ($fields as $field) {
				$this->assertEquals (null, $field->getBlockId (), 'Field block IDs do not match');
				$this->assertNull ($field->getId (), 'Field IDs do not match');
			}
		}

	}
	// @codingStandardsIgnoreEnd

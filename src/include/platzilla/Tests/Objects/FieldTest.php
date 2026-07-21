<?php
	require_once ('include/platzilla/Objects/Field.php');

	/**
	 * Prueba unitaria de la clase Field
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class FieldTest extends PHPUnit_Framework_TestCase {

		public function testGettersAndSetters () {
			$object         = Field::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'blockId'             => 100,
				'columnName'          => 'my_column_name',
				'defaultValue'        => 'My default value',
				'displayType'         => array (FieldInterface::DISPLAY_TYPE_NOWHERE, FieldInterface::DISPLAY_TYPE_ALL, FieldInterface::DISPLAY_TYPE_DETAIL_VIEW_ONLY, FieldInterface::DISPLAY_TYPE_ALL, FieldInterface::DISPLAY_TYPE_LIST_VIEW_ONLY, FieldInterface::DISPLAY_TYPE_PASSWORD),
				'generatedType'       => array (FieldInterface::GENERATED_TYPE_EXISTING, FieldInterface::GENERATED_TYPE_CUSTOM),
				'label'               => 'My label',
				'mandatory'           => true,
				'massEditable'        => array (FieldInterface::MASS_EDITABLE_DISABLED, FieldInterface::MASS_EDITABLE_ENABLED, FieldInterface::MASS_EDITABLE_USER_DEFINED),
				'moduleName'          => 'my_module_name',
				'name'                => 'my_field_name',
				'presence'            => array (FieldInterface::PRESENCE_ALWAYS_HIDDEN, FieldInterface::PRESENCE_VISIBLE, FieldInterface::PRESENCE_HIDDEN, FieldInterface::PRESENCE_USER_DEFINED),
				'quickCreate'         => array (FieldInterface::QUICK_CREATE_ENABLED, FieldInterface::QUICK_CREATE_DISABLED),
				'quickCreateSequence' => 30,
				'readOnly'            => array (FieldInterface::READ_ONLY, FieldInterface::READ_WRITE),
				'sequence'            => 35,
				'tableName'           => 'my_table_name',
				'uiType'              => array (FieldInterface::UI_TYPE_CHECKBOX, FieldInterface::UI_TYPE_CODE, FieldInterface::UI_TYPE_CREATED_TIME, FieldInterface::UI_TYPE_CURRENCY, FieldInterface::UI_TYPE_EMAIL, FieldInterface::UI_TYPE_DATE, FieldInterface::UI_TYPE_DATETIME, FieldInterface::UI_TYPE_GRID, FieldInterface::UI_TYPE_MODULE_REFERENCE, FieldInterface::UI_TYPE_MODIFIED_BY, FieldInterface::UI_TYPE_MULTI_SELECT, FieldInterface::UI_TYPE_NUMBER, FieldInterface::UI_TYPE_OWNER, FieldInterface::UI_TYPE_PERCENTAGE, FieldInterface::UI_TYPE_PHONE, FieldInterface::UI_TYPE_PICKLIST, FieldInterface::UI_TYPE_SKYPE, FieldInterface::UI_TYPE_TEXT, FieldInterface::UI_TYPE_TEXTAREA, FieldInterface::UI_TYPE_URL),
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

		public function testEmptyBlockIdValidation () {
			$field = Field::getInstance ();
			$this->expectException (FieldException::class);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_EMPTY_BLOCK_ID);
			$field->validate ();
		}

		public function testEmptyColumnNameValidation () {
			$field = Field::getInstance ()
				->setBlockId (100);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_EMPTY_COLUMN_NAME);
			$field->validate ();
		}

		public function testEmptyLabelValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel (null);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_EMPTY_LABEL);
			$field->validate ();
		}

		public function testEmptyModuleIdValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label');
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_EMPTY_MODULE_NAME);
			$field->validate ();
		}

		public function testEmptyNameValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name');
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_EMPTY_NAME);
			$field->validate ();
		}

		public function testEmptyUiTypeValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setTableName ('my_table_name');
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_EMPTY_UI_TYPE);
			$field->validate ();
		}

		public function testColumnNameTooLongValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('This is a very very very very long column name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_CHECKBOX);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_COLUMN_NAME_TOO_LONG);
			$field->validate ();
		}

		public function testNameTooLongValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_very_very_very_very_very_very_very_very_very_very_very_very_very_very_very_long_field_name')
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_CHECKBOX);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_NAME_TOO_LONG);
			$field->validate ();
		}

		public function testTableNameTooLongValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setTableName ('my_very_very_very_very_very_very_very_very_very_very_very_very_very_very_very_long_table_name')
				->setUiType (FieldInterface::UI_TYPE_CHECKBOX);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_TABLE_NAME_TOO_LONG);
			$field->validate ();
		}

		public function testEmptyGridValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_GRID);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_EMPTY_GRID);
			$field->validate ();
		}

		public function testEmptyPicklistValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_PICKLIST);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_EMPTY_PICKLIST);
			$field->validate ();
		}

		public function testEmptyRelatedModuleNameValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_MODULE_REFERENCE);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_EMPTY_MODULE_REFERENCE);
			$field->validate ();
		}

		public function testInvalidRelatedModuleNameValidation () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setModuleReferences (array (
					new stdClass ()
				))
				->setName ('my_field_name')
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_MODULE_REFERENCE);
			$this->expectExceptionMessage (FieldException::ERROR_FIELD_INVALID_MODULE_REFERENCE);
			$field->validate ();
		}

		public function testValidationSucceed () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setModuleReferences (array (
					FieldModuleReference::getInstance ()->setFieldName ('my_field_name')->setModuleName ('my_module_name')->setReferencedModuleName ('my_referenced_module_name'),
				))
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_MODULE_REFERENCE);
			$field->validate ();
		}

		public function testCheckboxField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CHECKBOX, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_CHECKBOX, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(3)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('C~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (3, $field->getLength (), 'Lengths do not match');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');

			$field = Field::getInstance ('C~M');
			$this->assertEquals (FieldInterface::DATA_TYPE_CHECKBOX, $field->getDataType (), 'DataTypes do not match');
			$this->assertTrue ($field->isMandatory (), 'Field isMandatory should be true');
			$this->assertEquals (3, $field->getLength (), 'Lengths do not match');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testCodeField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(100)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O~LE~100', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (100, $field->getLength (), 'Lengths do not match');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testCreatedTimeField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CREATED_TIME, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_DATETIME, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('DATETIME', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('DT~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testCurrencyField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CURRENCY, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_NUMBER, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('NUMERIC(100,20)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('N~O~100,20', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (100, $field->getLength (), 'Lengths do not match');
			$this->assertEquals (20, $field->getPrecision (), 'Precisions do not match');
		}

		public function testDateField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATE, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_DATE, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('DATE', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('D~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testDateTimeField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_DATETIME, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('DATETIME', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('DT~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testEmailField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_EMAIL, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_EMAIL, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(50)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('E~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (50, $field->getLength (), 'Lengths do not match');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testGridField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_GRID, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertNull ($field->getSqlDataType (), 'SQL data types should be null');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testImageDisplayField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_IMAGE_DISPLAY, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(255)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testImageReferenceField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_IMAGE_REFERENCE, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(255)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (255, $field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testModifiedByField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_MODIFIED_BY, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('TEXT', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testModuleRecordsField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_MODULE_RECORDS, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(255)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testModuleReferenceField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_MODULE_REFERENCE, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(100)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (100, $field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testMultiSelectField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_MULTI_SELECT, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('TEXT', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testNumberField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_NEGATIVE_NUMBER, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('NUMERIC(100,20)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('NN~O~100,20', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (100, $field->getLength (), 'Length should be null');
			$this->assertEquals (20, $field->getPrecision (), 'Precision should be null');
		}

		public function testOwnerField () {
			$field = Field::getInstance ()
				->setMandatory (false)
				->setUiType (FieldInterface::UI_TYPE_OWNER, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('INT(19)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~M', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertTrue ($field->isMandatory (), 'Owner field isMandatory should be true');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testPercentageField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_PERCENTAGE, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_NUMBER, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('NUMERIC(100,20)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('N~O~100,20', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (100, $field->getLength (), 'Lengths do not match');
			$this->assertEquals (20, $field->getPrecision (), 'Precisions do not match');
		}

		public function testPhoneField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_PHONE, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(30)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (30, $field->getLength (), 'Lengths do not match');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testPicklistField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_PICKLIST, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(255)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (255, $field->getLength (), 'Lengths do not match');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testSkypeField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_SKYPE, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(255)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (255, $field->getLength (), 'Lengths do not match');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testTextField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(100)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O~LE~100', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (100, $field->getLength (), 'Lengths do not match');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testTextareaField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXTAREA, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('TEXT', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testTimeField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TIME, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_TIME, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('TIME', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('T~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertNull ($field->getLength (), 'Length should be null');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testUrlField () {
			$field = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_URL, 100, 20);
			$this->assertEquals (FieldInterface::DATA_TYPE_VARCHAR, $field->getDataType (), 'DataTypes do not match');
			$this->assertEquals ('VARCHAR(255)', $field->getSqlDataType (), 'SQL data types do not match');
			$this->assertEquals ('V~O', $field->getTypeOfData (), 'Types of data types do not match');
			$this->assertFalse ($field->isMandatory (), 'Field isMandatory should be false');
			$this->assertEquals (255, $field->getLength (), 'Lengths do not match');
			$this->assertNull ($field->getPrecision (), 'Precision should be null');
		}

		public function testDuplicate () {
			$field = Field::getInstance ()
				->setId (423565)
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setDefaultValue ('My default value')
				->setDisplayType (FieldInterface::DISPLAY_TYPE_NOWHERE)
				->setGeneratedType (FieldInterface::GENERATED_TYPE_EXISTING)
				->setLabel ('My field label')
				->setMandatory (true)
				->setMassEditable (FieldInterface::MASS_EDITABLE_USER_DEFINED)
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setPresence (FieldInterface::PRESENCE_ALWAYS_HIDDEN)
				->setQuickCreate (FieldInterface::QUICK_CREATE_UNKNOWN)
				->setQuickCreateSequence (5)
				->setReadOnly (FieldInterface::READ_ONLY)
				->setSequence (5)
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_TEXT, 125);

			$duplicatedField = $field->duplicate ($field->getId (), $field->getBlockId ());
			$this->assertEquals (423565, $duplicatedField->getId (), 'IDs do not match');
			$this->assertEquals (100, $duplicatedField->getBlockId (), 'Block IDs do not match');
			$this->assertEquals ('my_column_name', $duplicatedField->getColumnName (), 'Column names do not match');
			$this->assertEquals ('My default value', $duplicatedField->getDefaultValue (), 'Default values do not match');
			$this->assertEquals (FieldInterface::DISPLAY_TYPE_NOWHERE, $duplicatedField->getDisplayType (), 'Display types do not match');
			$this->assertEquals (FieldInterface::GENERATED_TYPE_EXISTING, $duplicatedField->getGeneratedType (), 'Generated types do not match');
			$this->assertEquals ('My field label', $duplicatedField->getLabel (), 'Labels do not match');
			$this->assertEquals (125, $duplicatedField->getLength (), 'Lengths do not match');
			$this->assertEquals (true, $duplicatedField->isMandatory (), 'Mandatory properties do not match');
			$this->assertEquals (FieldInterface::MASS_EDITABLE_USER_DEFINED, $duplicatedField->getMassEditable (), 'Mass editable properties do not match');
			$this->assertEquals ('my_module_name', $duplicatedField->getModuleName (), 'Module names do not match');
			$this->assertEquals ('my_field_name', $duplicatedField->getName (), 'Names do not match');
			$this->assertEquals (null, $duplicatedField->getPrecision (), 'Precisions do not match');
			$this->assertEquals (FieldInterface::PRESENCE_ALWAYS_HIDDEN, $duplicatedField->getPresence (), 'Presences do not match');
			$this->assertEquals (FieldInterface::QUICK_CREATE_UNKNOWN, $duplicatedField->getQuickCreate (), 'Quick create properties do not match');
			$this->assertEquals (5, $duplicatedField->getQuickCreateSequence (), 'Quick create sequences do not match');
			$this->assertEquals (FieldInterface::READ_ONLY, $duplicatedField->getReadOnly (), 'Read only properties do not match');
			$this->assertEquals (5, $duplicatedField->getSequence (), 'Sequences do not match');
			$this->assertEquals ('my_table_name', $duplicatedField->getTableName (), 'Table names do not match');
			$this->assertEquals (FieldInterface::UI_TYPE_TEXT, $duplicatedField->getUiType (), 'UI types do not match');

			$duplicatedField = $field->duplicate (null, null);
			$this->assertEquals (null, $duplicatedField->getId (), 'IDs do not match');
			$this->assertEquals (null, $duplicatedField->getBlockId (), 'Block IDs do not match');
			$this->assertEquals ('my_column_name', $duplicatedField->getColumnName (), 'Column names do not match');
			$this->assertEquals ('My default value', $duplicatedField->getDefaultValue (), 'Default values do not match');
			$this->assertEquals (FieldInterface::DISPLAY_TYPE_NOWHERE, $duplicatedField->getDisplayType (), 'Display types do not match');
			$this->assertEquals (FieldInterface::GENERATED_TYPE_EXISTING, $duplicatedField->getGeneratedType (), 'Generated types do not match');
			$this->assertEquals ('My field label', $duplicatedField->getLabel (), 'Labels do not match');
			$this->assertEquals (125, $duplicatedField->getLength (), 'Lengths do not match');
			$this->assertEquals (true, $duplicatedField->isMandatory (), 'Mandatory properties do not match');
			$this->assertEquals (FieldInterface::MASS_EDITABLE_USER_DEFINED, $duplicatedField->getMassEditable (), 'Mass editable properties do not match');
			$this->assertEquals ('my_module_name', $duplicatedField->getModuleName (), 'Module names do not match');
			$this->assertEquals ('my_field_name', $duplicatedField->getName (), 'Names do not match');
			$this->assertEquals (null, $duplicatedField->getPrecision (), 'Precisions do not match');
			$this->assertEquals (FieldInterface::PRESENCE_ALWAYS_HIDDEN, $duplicatedField->getPresence (), 'Presences do not match');
			$this->assertEquals (FieldInterface::QUICK_CREATE_UNKNOWN, $duplicatedField->getQuickCreate (), 'Quick create properties do not match');
			$this->assertEquals (5, $duplicatedField->getQuickCreateSequence (), 'Quick create sequences do not match');
			$this->assertEquals (FieldInterface::READ_ONLY, $duplicatedField->getReadOnly (), 'Read only properties do not match');
			$this->assertEquals (5, $duplicatedField->getSequence (), 'Sequences do not match');
			$this->assertEquals ('my_table_name', $duplicatedField->getTableName (), 'Table names do not match');
			$this->assertEquals (FieldInterface::UI_TYPE_TEXT, $duplicatedField->getUiType (), 'UI types do not match');
		}

	}
	// @codingStandardsIgnoreEnd

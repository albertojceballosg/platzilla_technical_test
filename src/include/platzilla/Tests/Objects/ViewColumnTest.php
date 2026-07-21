<?php
	require_once ('include/platzilla/Objects/ViewColumn.php');

	/**
	 * Prueba unitaria de la clase ViewColumn
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ViewColumnTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = ViewColumn::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'columnName' => 'my_column_name',
				'dataType'   => array (FieldInterface::DATA_TYPE_CHECKBOX, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_DATETIME, FieldInterface::DATA_TYPE_EMAIL, FieldInterface::DATA_TYPE_INTEGER, FieldInterface::DATA_TYPE_NEGATIVE_NUMBER, FieldInterface::DATA_TYPE_NUMBER, FieldInterface::DATA_TYPE_PASSWORD, FieldInterface::DATA_TYPE_TIME, FieldInterface::DATA_TYPE_VARCHAR),
				'fieldName'  => 'my_field_name',
				'label'      => 'My label',
				'moduleName' => 'my_module_name',
				'sequence'   => 41,
				'tableName'  => 'my_table_name',
				'viewId'     => 5,
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

		public function testEmptyColumnNameValidation () {
			$object = ViewColumn::getInstance ();
			$this->expectException (ViewColumnException::class);
			$this->expectExceptionMessage (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_COLUMN_NAME);
			$object->validate ();
		}

		public function testEmptyDataTypeValidation () {
			$object = ViewColumn::getInstance ()
				->setColumnName ('my_column_name')
				->setDataType (-1);
			$this->expectException (ViewColumnException::class);
			$this->expectExceptionMessage (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_DATA_TYPE);
			$object->validate ();
		}

		public function testEmptyFieldNameValidation () {
			$object = ViewColumn::getInstance ()
				->setColumnName ('my_column_name')
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR);
			$this->expectException (ViewColumnException::class);
			$this->expectExceptionMessage (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_FIELD_NAME);
			$object->validate ();
		}

		public function testEmptyLabelValidation () {
			$object = ViewColumn::getInstance ()
				->setColumnName ('my_column_name')
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR)
				->setFieldName ('my_field_name');
			$this->expectException (ViewColumnException::class);
			$this->expectExceptionMessage (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_LABEL);
			$object->validate ();
		}

		public function testEmptySequenceValidation () {
			$object = ViewColumn::getInstance ()
				->setColumnName ('my_column_name')
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR)
				->setFieldName ('my_field_name')
				->setLabel ('My field name')
				->setModuleName ('my_module_name');
			$this->expectException (ViewColumnException::class);
			$this->expectExceptionMessage (ViewColumnException::ERROR_VIEW_COLUMN_EMPTY_SEQUENCE);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = ViewColumn::getInstance ()
				->setColumnName ('my_column_name')
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR)
				->setFieldName ('my_field_name')
				->setLabel ('My field name')
				->setModuleName ('my_module_name')
				->setSequence (0)
				->setTableName ('vtiger_my_module_name')
				->setViewId (8);
			$object->validate ();
		}

		public function testConstructorWithValidField () {
			$field  = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_TEXT);
			$object = ViewColumn::getInstance ($field);

			$this->assertEquals ($field->getColumnName (), $object->getColumnName (), 'Column names do not match');
			$this->assertEquals ($field->getDataType (), $object->getDataType (), 'Data types do not match');
			$this->assertEquals ($field->getName (), $object->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $object->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $object->getModuleName (), 'Module names do not match');
			$this->assertEquals ($field->getTableName (), $object->getTableName (), 'Table names do not match');
		}

		public function testDuplicate () {
			$field  = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_TEXT);
			$object = ViewColumn::getInstance ($field)
				->setSequence (3)
				->setViewId (2);

			$duplicatedObject = $object->duplicate ($object->getViewId ());
			$this->assertEquals ($field->getColumnName (), $duplicatedObject->getColumnName (), 'Column names do not match');
			$this->assertEquals ($field->getDataType (), $duplicatedObject->getDataType (), 'Data types do not match');
			$this->assertEquals ($field->getName (), $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ($field->getTableName (), $duplicatedObject->getTableName (), 'Table names do not match');
			$this->assertEquals ($field->getTableName (), $duplicatedObject->getTableName (), 'Table names do not match');
			$this->assertEquals (3, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals (2, $duplicatedObject->getViewId (), 'View IDs do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals ($field->getColumnName (), $duplicatedObject->getColumnName (), 'Column names do not match');
			$this->assertEquals ($field->getDataType (), $duplicatedObject->getDataType (), 'Data types do not match');
			$this->assertEquals ($field->getName (), $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ($field->getTableName (), $duplicatedObject->getTableName (), 'Table names do not match');
			$this->assertEquals ($field->getTableName (), $duplicatedObject->getTableName (), 'Table names do not match');
			$this->assertEquals (3, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals (null, $duplicatedObject->getViewId (), 'View IDs do not match');
		}

	}
	// @codingStandardsIgnoreEnd

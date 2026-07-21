<?php
	require_once ('include/platzilla/Objects/ReportAdvancedFilter.php');

	/**
	 * Prueba unitaria de la clase ReportAdvancedFilter
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ReportAdvancedFilterTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = ReportAdvancedFilter::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'columnName' => 'my_column_name',
				'comparator' => array (ReportAdvancedFilterInterface::COMPARATOR_AFTER, ReportAdvancedFilterInterface::COMPARATOR_BEFORE, ReportAdvancedFilterInterface::COMPARATOR_BETWEEN, ReportAdvancedFilterInterface::COMPARATOR_CONTAINS, ReportAdvancedFilterInterface::COMPARATOR_DOES_NOT_CONTAIN, ReportAdvancedFilterInterface::COMPARATOR_ENDS_WITH, ReportAdvancedFilterInterface::COMPARATOR_EQUALS, ReportAdvancedFilterInterface::COMPARATOR_GREATER, ReportAdvancedFilterInterface::COMPARATOR_GREATER_OR_EQUALS, ReportAdvancedFilterInterface::COMPARATOR_LESS, ReportAdvancedFilterInterface::COMPARATOR_LESS_OR_EQUALS, ReportAdvancedFilterInterface::COMPARATOR_NOT_EQUALS, ReportAdvancedFilterInterface::COMPARATOR_STARTS_WITH),
				'dataType'   => array (FieldInterface::DATA_TYPE_CHECKBOX, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_DATETIME, FieldInterface::DATA_TYPE_EMAIL, FieldInterface::DATA_TYPE_INTEGER, FieldInterface::DATA_TYPE_NEGATIVE_NUMBER, FieldInterface::DATA_TYPE_NUMBER, FieldInterface::DATA_TYPE_PASSWORD, FieldInterface::DATA_TYPE_TIME, FieldInterface::DATA_TYPE_VARCHAR),
				'fieldName'  => 'my_field_name',
				'groupId'    => 69,
				'label'      => 'My label',
				'moduleName' => 'my_module_name',
				'operator'   => array ('', ReportAdvancedFilterInterface::OPERATOR_AND, ReportAdvancedFilterInterface::OPERATOR_OR),
				'reportId'   => 3,
				'sequence'   => 5,
				'tableName'  => 'my_table_name',
				'value'      => array (null, '60', '2017-12-31'),
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
			$object = ReportAdvancedFilter::getInstance ();
			$this->expectException (ReportAdvancedFilterException::class);
			$this->expectExceptionMessage (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_COLUMN_NAME);
			$object->validate ();
		}

		public function testEmptyOrInvalidComparatorValidation () {
			$object = ReportAdvancedFilter::getInstance ()
				->setColumnName ('my_column_name');
			$this->expectException (ReportAdvancedFilterException::class);
			$this->expectExceptionMessage (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_COMPARATOR);
			$object->validate ();

			$object = ReportAdvancedFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setComparator ('unknown_comparator');
			$this->expectException (ReportAdvancedFilterException::class);
			$this->expectExceptionMessage (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_COMPARATOR);
			$object->validate ();
		}

		public function testEmptyOrInvalidDataTypeValidation () {
			$object = ReportAdvancedFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS);
			$this->expectException (ReportAdvancedFilterException::class);
			$this->expectExceptionMessage (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_DATA_TYPE);
			$object->validate ();

			$object = ReportAdvancedFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
				->setDataType ('unknown_data_type');
			$this->expectException (ReportAdvancedFilterException::class);
			$this->expectExceptionMessage (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_DATA_TYPE);
			$object->validate ();
		}

		public function testEmptyFieldNameValidation () {
			$object = ReportAdvancedFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR);
			$this->expectException (ReportAdvancedFilterException::class);
			$this->expectExceptionMessage (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_FIELD_NAME);
			$object->validate ();
		}

		public function testEmptyLabelValidation () {
			$object = ReportAdvancedFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR)
				->setFieldName ('my_field_name')
				->setGroupId (6);
			$this->expectException (ReportAdvancedFilterException::class);
			$this->expectExceptionMessage (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_LABEL);
			$object->validate ();
		}

		public function testEmptySequenceValidation () {
			$object = ReportAdvancedFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR)
				->setFieldName ('my_field_name')
				->setGroupId (6)
				->setLabel ('My field name')
				->setModuleName ('my_module_name');
			$this->expectException (ReportAdvancedFilterException::class);
			$this->expectExceptionMessage (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_EMPTY_SEQUENCE);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = ReportAdvancedFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR)
				->setFieldName ('my_field_name')
				->setGroupId (6)
				->setLabel ('My field name')
				->setModuleName ('my_module_name')
				->setReportId (8)
				->setSequence (0)
				->setTableName ('vtiger_my_module_name');
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
			$object = ReportAdvancedFilter::getInstance ($field);

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
			$object = ReportAdvancedFilter::getInstance ($field)
				->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
				->setGroupId (2)
				->setReportId (4)
				->setSequence (1)
				->setValue ('test_value');

			$duplicatedObject = $object->duplicate ($object->getReportId (), $object->getGroupId ());
			$this->assertEquals ($field->getColumnName (), $duplicatedObject->getColumnName (), 'Column names do not match');
			$this->assertEquals ($field->getDataType (), $duplicatedObject->getDataType (), 'Data types do not match');
			$this->assertEquals ($field->getName (), $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ($field->getTableName (), $duplicatedObject->getTableName (), 'Table names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_EQUALS, $duplicatedObject->getComparator (), 'Comparators do not match');
			$this->assertEquals (2, $duplicatedObject->getGroupId (), 'Group IDs do not match');
			$this->assertEquals (1, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals ('test_value', $duplicatedObject->getValue (), 'Values do not match');
			$this->assertEquals (4, $duplicatedObject->getReportId (), 'Report IDs do not match');

			$duplicatedObject = $object->duplicate (null, null);
			$this->assertEquals ($field->getColumnName (), $duplicatedObject->getColumnName (), 'Column names do not match');
			$this->assertEquals ($field->getDataType (), $duplicatedObject->getDataType (), 'Data types do not match');
			$this->assertEquals ($field->getName (), $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ($field->getTableName (), $duplicatedObject->getTableName (), 'Table names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_EQUALS, $duplicatedObject->getComparator (), 'Comparators do not match');
			$this->assertEquals (null, $duplicatedObject->getGroupId (), 'Group IDs do not match');
			$this->assertEquals (1, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals ('test_value', $duplicatedObject->getValue (), 'Values do not match');
			$this->assertEquals (null, $duplicatedObject->getReportId (), 'Report IDs do not match');
		}

	}
	// @codingStandardsIgnoreEnd

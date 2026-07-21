<?php
	require_once ('include/platzilla/Objects/ReportColumn.php');

	/**
	 * Prueba unitaria de la clase ReportColumn
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ReportColumnTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = ReportColumn::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'columnName'      => 'my_column_name',
				'dataType'        => array (FieldInterface::DATA_TYPE_CHECKBOX, FieldInterface::DATA_TYPE_DATE, FieldInterface::DATA_TYPE_DATETIME, FieldInterface::DATA_TYPE_EMAIL, FieldInterface::DATA_TYPE_INTEGER, FieldInterface::DATA_TYPE_NEGATIVE_NUMBER, FieldInterface::DATA_TYPE_NUMBER, FieldInterface::DATA_TYPE_PASSWORD, FieldInterface::DATA_TYPE_TIME, FieldInterface::DATA_TYPE_VARCHAR),
				'fieldName'       => 'my_field_name',
				'label'           => 'My label',
				'moduleName'      => 'my_module_name',
				'reportId'        => 5,
				'sequence'        => 41,
				'sortOrder'       => array (ReportColumnInterface::SORT_ORDER_ASCENDING, ReportColumnInterface::SORT_ORDER_DESCENDING),
				'tableName'       => 'my_table_name',
				'totalsOperation' => array (ReportColumnInterface::TOTALS_OPERATION_AVERAGE, ReportColumnInterface::TOTALS_OPERATION_MAXIMUM, ReportColumnInterface::TOTALS_OPERATION_MINIMUM, ReportColumnInterface::TOTALS_OPERATION_SUM),
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
			$object = ReportColumn::getInstance ();
			$this->expectException (ReportColumnException::class);
			$this->expectExceptionMessage (ReportColumnException::ERROR_REPORT_COLUMN_EMPTY_COLUMN_NAME);
			$object->validate ();
		}

		public function testEmptyFieldNameValidation () {
			$object = ReportColumn::getInstance ()
				->setColumnName ('my_column_name')
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR);
			$this->expectException (ReportColumnException::class);
			$this->expectExceptionMessage (ReportColumnException::ERROR_REPORT_COLUMN_EMPTY_FIELD_NAME);
			$object->validate ();
		}

		public function testEmptyLabelValidation () {
			$object = ReportColumn::getInstance ()
				->setColumnName ('my_column_name')
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR)
				->setFieldName ('my_field_name');
			$this->expectException (ReportColumnException::class);
			$this->expectExceptionMessage (ReportColumnException::ERROR_REPORT_COLUMN_EMPTY_LABEL);
			$object->validate ();
		}

		public function testEmptySequenceValidation () {
			$object = ReportColumn::getInstance ()
				->setColumnName ('my_column_name')
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR)
				->setFieldName ('my_field_name')
				->setLabel ('My field name')
				->setModuleName ('my_module_name');
			$this->expectException (ReportColumnException::class);
			$this->expectExceptionMessage (ReportColumnException::ERROR_REPORT_COLUMN_EMPTY_SEQUENCE);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = ReportColumn::getInstance ()
				->setColumnName ('my_column_name')
				->setDataType (FieldInterface::DATA_TYPE_VARCHAR)
				->setFieldName ('my_field_name')
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
			$object = ReportColumn::getInstance ($field);

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
			$object = ReportColumn::getInstance ($field)
				->setReportId (2)
				->setSequence (3)
				->setSortOrder (ReportColumnInterface::SORT_ORDER_DESCENDING)
				->setTotalsOperation (ReportColumnInterface::TOTALS_OPERATION_AVERAGE);

			$duplicatedObject = $object->duplicate ($object->getReportId ());
			$this->assertEquals ($field->getColumnName (), $duplicatedObject->getColumnName (), 'Column names do not match');
			$this->assertEquals ($field->getDataType (), $duplicatedObject->getDataType (), 'Data types do not match');
			$this->assertEquals ($field->getName (), $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ($field->getTableName (), $duplicatedObject->getTableName (), 'Table names do not match');
			$this->assertEquals (2, $duplicatedObject->getReportId (), 'Report IDs do not match');
			$this->assertEquals (3, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals (ReportColumnInterface::SORT_ORDER_DESCENDING, $duplicatedObject->getSortOrder (), 'Sort orders do not match');
			$this->assertEquals (ReportColumnInterface::TOTALS_OPERATION_AVERAGE, $duplicatedObject->getTotalsOperation (), 'Totals operations do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals ($field->getColumnName (), $duplicatedObject->getColumnName (), 'Column names do not match');
			$this->assertEquals ($field->getDataType (), $duplicatedObject->getDataType (), 'Data types do not match');
			$this->assertEquals ($field->getName (), $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ($field->getTableName (), $duplicatedObject->getTableName (), 'Table names do not match');
			$this->assertEquals (null, $duplicatedObject->getReportId (), 'Report IDs do not match');
			$this->assertEquals (3, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals (ReportColumnInterface::SORT_ORDER_DESCENDING, $duplicatedObject->getSortOrder (), 'Sort orders do not match');
			$this->assertEquals (ReportColumnInterface::TOTALS_OPERATION_AVERAGE, $duplicatedObject->getTotalsOperation (), 'Totals operations do not match');
		}

	}
	// @codingStandardsIgnoreEnd

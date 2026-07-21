<?php
	require_once ('include/platzilla/Objects/ReportStandardFilter.php');

	/**
	 * Prueba unitaria de la clase ReportStandardFilter
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ReportStandardFilterTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = ReportStandardFilter::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'columnName' => 'my_column_name',
				'endDate'    => DateTime::createFromFormat ('Y-m-d', '2017-11-16'),
				'fieldName'  => 'my_field_name',
				'moduleName' => 'my_module_name',
				'period'     => array (ReportStandardFilterInterface::PERIOD_CURRENT_MONTH, ReportStandardFilterInterface::PERIOD_CURRENT_QUARTER, ReportStandardFilterInterface::PERIOD_CURRENT_WEEK, ReportStandardFilterInterface::PERIOD_CURRENT_YEAR, ReportStandardFilterInterface::PERIOD_CUSTOM, ReportStandardFilterInterface::PERIOD_LAST_MONTH, ReportStandardFilterInterface::PERIOD_LAST_7_DAYS, ReportStandardFilterInterface::PERIOD_LAST_30_DAYS, ReportStandardFilterInterface::PERIOD_LAST_60_DAYS, ReportStandardFilterInterface::PERIOD_LAST_90_DAYS, ReportStandardFilterInterface::PERIOD_LAST_120_DAYS, ReportStandardFilterInterface::PERIOD_NEXT_30_DAYS, ReportStandardFilterInterface::PERIOD_NEXT_60_DAYS, ReportStandardFilterInterface::PERIOD_NEXT_90_DAYS, ReportStandardFilterInterface::PERIOD_NEXT_120_DAYS, ReportStandardFilterInterface::PERIOD_LAST_WEEK, ReportStandardFilterInterface::PERIOD_NEXT_MONTH, ReportStandardFilterInterface::PERIOD_NEXT_QUARTER, ReportStandardFilterInterface::PERIOD_NEXT_WEEK, ReportStandardFilterInterface::PERIOD_NEXT_YEAR, ReportStandardFilterInterface::PERIOD_PREVIOUS_QUARTER, ReportStandardFilterInterface::PERIOD_PREVIOUS_YEAR, ReportStandardFilterInterface::PERIOD_TODAY, ReportStandardFilterInterface::PERIOD_TOMORROW, ReportStandardFilterInterface::PERIOD_YESTERDAY),
				'reportId'   => 5,
				'startDate'  => DateTime::createFromFormat ('Y-m-d', '2017-11-16'),
				'tableName'  => 'my_table_name',
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
			$object = ReportStandardFilter::getInstance ();
			$this->expectException (ReportStandardFilterException::class);
			$this->expectExceptionMessage (ReportStandardFilterException::ERROR_REPORT_STANDARD_FILTER_EMPTY_COLUMN_NAME);
			$object->validate ();
		}

		public function testEmptyOrInvalidEndDateValidation () {
			$object = ReportStandardFilter::getInstance ()
				->setColumnName ('my_column_name');
			$this->expectException (ReportStandardFilterException::class);
			$this->expectExceptionMessage (ReportStandardFilterException::ERROR_REPORT_STANDARD_FILTER_EMPTY_END_DATE);
			$object->validate ();

			$object = ReportStandardFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setEndDate ('2018-02-31');
			$this->expectException (ReportStandardFilterException::class);
			$this->expectExceptionMessage (ReportStandardFilterException::ERROR_REPORT_STANDARD_FILTER_EMPTY_END_DATE);
			$object->validate ();
		}

		public function testEmptyFieldNameValidation () {
			$object = ReportStandardFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setEndDate (DateTime::createFromFormat ('Y-m-d', '2017-11-16'));
			$this->expectException (ReportStandardFilterException::class);
			$this->expectExceptionMessage (ReportStandardFilterException::ERROR_REPORT_STANDARD_FILTER_EMPTY_FIELD_NAME);
			$object->validate ();
		}

		public function testEmptyLabelValidation () {
			$object = ReportStandardFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setEndDate (DateTime::createFromFormat ('Y-m-d', '2017-11-16'))
				->setFieldName ('my_field_name');
			$this->expectException (ReportStandardFilterException::class);
			$this->expectExceptionMessage (ReportStandardFilterException::ERROR_REPORT_STANDARD_FILTER_EMPTY_LABEL);
			$object->validate ();
		}

		public function testEmptyOrInvalidPeriodValidation () {
			$object = ReportStandardFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setEndDate (DateTime::createFromFormat ('Y-m-d', '2017-11-16'))
				->setFieldName ('my_field_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name');
			$this->expectException (ReportStandardFilterException::class);
			$this->expectExceptionMessage (ReportStandardFilterException::ERROR_REPORT_STANDARD_FILTER_EMPTY_PERIOD);
			$object->validate ();

			$object = ReportStandardFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setEndDate (DateTime::createFromFormat ('Y-m-d', '2017-11-16'))
				->setFieldName ('my_field_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setPeriod ('invalid_period');
			$this->expectException (ReportStandardFilterException::class);
			$this->expectExceptionMessage (ReportStandardFilterException::ERROR_REPORT_STANDARD_FILTER_EMPTY_PERIOD);
			$object->validate ();
		}

		public function testEmptyOrInvalidStartDateValidation () {
			$object = ReportStandardFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setEndDate (DateTime::createFromFormat ('Y-m-d', '2017-11-16'))
				->setFieldName ('my_field_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setPeriod (ReportStandardFilterInterface::PERIOD_PREVIOUS_YEAR);
			$this->expectException (ReportStandardFilterException::class);
			$this->expectExceptionMessage (ReportStandardFilterException::ERROR_REPORT_STANDARD_FILTER_EMPTY_START_DATE);
			$object->validate ();

			$object = ReportStandardFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setEndDate (DateTime::createFromFormat ('Y-m-d', '2017-11-16'))
				->setFieldName ('my_field_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setPeriod (ReportStandardFilterInterface::PERIOD_PREVIOUS_YEAR)
				->setStartDate ('2018-02-31');
			$this->expectException (ReportStandardFilterException::class);
			$this->expectExceptionMessage (ReportStandardFilterException::ERROR_REPORT_STANDARD_FILTER_EMPTY_START_DATE);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = ReportStandardFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setEndDate (DateTime::createFromFormat ('Y-m-d', '2017-11-16'))
				->setFieldName ('my_field_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setPeriod (ReportStandardFilterInterface::PERIOD_PREVIOUS_YEAR)
				->setReportId (8)
				->setStartDate (DateTime::createFromFormat ('Y-m-d', '2017-11-16'))
				->setTableName ('vtiger_my_module_name');
			$object->validate ();
		}

		public function testDateGettersAndSettersWithStringParameters () {
			$object = ReportStandardFilter::getInstance ()
				->setColumnName ('my_column_name')
				->setEndDate ('2017-11-16')
				->setFieldName ('my_field_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setPeriod (ReportStandardFilterInterface::PERIOD_PREVIOUS_YEAR)
				->setReportId (8)
				->setStartDate ('2017-11-16')
				->setTableName ('vtiger_my_module_name');
			$this->assertEquals (DateTime::createFromFormat ('Y-m-d', '2017-11-16')->setTime (0, 0, 0), $object->getEndDate (), 'End dates do not match');
			$this->assertEquals (DateTime::createFromFormat ('Y-m-d', '2017-11-16')->setTime (0, 0, 0), $object->getStartDate (), 'End dates do not match');
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
			$object = ReportStandardFilter::getInstance ($field);

			$this->assertEquals ($field->getColumnName (), $object->getColumnName (), 'Column names do not match');
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
			$object = ReportStandardFilter::getInstance ($field)
				->setEndDate ('2017-12-31')
				->setPeriod (ReportStandardFilterInterface::PERIOD_CUSTOM)
				->setReportId (45)
				->setStartDate ('2017-12-01');

			$duplicatedObject = $object->duplicate ($object->getReportId ());
			$this->assertEquals ($field->getColumnName (), $duplicatedObject->getColumnName (), 'Column names do not match');
			$this->assertEquals (date_create ('2017-12-31'), $duplicatedObject->getEndDate (), 'End dates do not match');
			$this->assertEquals ($field->getName (), $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals (ReportStandardFilterInterface::PERIOD_CUSTOM, $duplicatedObject->getPeriod (), 'Periods do not match');
			$this->assertEquals (date_create ('2017-12-01'), $duplicatedObject->getStartDate (), 'Start dates do not match');
			$this->assertEquals ($field->getTableName (), $duplicatedObject->getTableName (), 'Table names do not match');
			$this->assertEquals (45, $duplicatedObject->getReportId (), 'Report IDs do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals ($field->getColumnName (), $duplicatedObject->getColumnName (), 'Column names do not match');
			$this->assertEquals (date_create ('2017-12-31'), $duplicatedObject->getEndDate (), 'End dates do not match');
			$this->assertEquals ($field->getName (), $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals (ReportStandardFilterInterface::PERIOD_CUSTOM, $duplicatedObject->getPeriod (), 'Periods do not match');
			$this->assertEquals (date_create ('2017-12-01'), $duplicatedObject->getStartDate (), 'Start dates do not match');
			$this->assertEquals ($field->getTableName (), $duplicatedObject->getTableName (), 'Table names do not match');
			$this->assertEquals (null, $duplicatedObject->getReportId (), 'Report IDs do not match');
		}
	}
	// @codingStandardsIgnoreEnd

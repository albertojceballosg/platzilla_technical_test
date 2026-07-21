<?php
	require_once ('include/platzilla/Objects/ReportAdvancedFilterGroup.php');

	/**
	 * Prueba unitaria de la clase ReportAdvancedFilterGroup
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ReportAdvancedFilterGroupTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = ReportAdvancedFilterGroup::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'operator' => array ('', ReportAdvancedFilterInterface::OPERATOR_AND, ReportAdvancedFilterInterface::OPERATOR_OR),
				'reportId' => 3,
				'sequence' => 0,
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

		public function testEmptySequenceValidation () {
			$object = ReportAdvancedFilterGroup::getInstance ();
			$this->expectException (ReportAdvancedFilterGroupException::class);
			$this->expectExceptionMessage (ReportAdvancedFilterGroupException::ERROR_REPORT_ADVANCED_FILTER_GROUP_EMPTY_SEQUENCE);
			$object->validate ();
		}

		public function testInvalidFiltersValidation () {
			$object = ReportAdvancedFilterGroup::getInstance ()
				->setReportId (3)
				->setSequence (1)
				->setFilters (array (new stdClass ()));
			$this->expectException (ReportAdvancedFilterException::class);
			$this->expectExceptionMessage (ReportAdvancedFilterException::ERROR_REPORT_ADVANCED_FILTER_INVALID_FILTER);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$field  = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_TEXT);
			$object = ReportAdvancedFilterGroup::getInstance ()
				->setReportId (3)
				->setSequence (1)
				->setFilters (array (
					ReportAdvancedFilter::getInstance ($field)
						->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
						->setGroupId (49)
						->setReportId (3)
						->setSequence (0),
				));
			$object->validate ();
		}

		public function testDuplicate () {
			$field = Field::getInstance ()
				->setBlockId (100)
				->setColumnName ('my_column_name')
				->setLabel ('My field label')
				->setModuleName ('my_module_name')
				->setName ('my_field_name')
				->setTableName ('my_table_name')
				->setUiType (FieldInterface::UI_TYPE_TEXT);
			$object = ReportAdvancedFilterGroup::getInstance ()
				->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)
				->setReportId (3)
				->setSequence (2)
				->setFilters (array (
					ReportAdvancedFilter::getInstance ($field)
						->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)
						->setGroupId (2)
						->setReportId (3)
						->setSequence (1)
						->setValue ('test_value')
				));

			$duplicatedObject = $object->duplicate ($object->getReportId (), $object->getSequence ());
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_AND, $duplicatedObject->getOperator (), 'Group operators do not match');
			$this->assertEquals (2, $duplicatedObject->getSequence (), 'Group sequences do not match');
			$this->assertEquals (3, $duplicatedObject->getReportId (), 'Group report IDs do not match');
			$this->assertCount (1, $duplicatedObject->getFilters (), 'Group filters count do not match');

			$filters = $duplicatedObject->getFilters ();
			$filter = $filters [0];
			$this->assertEquals ($field->getColumnName (), $filter->getColumnName (), 'Filter column names do not match');
			$this->assertEquals ($field->getDataType (), $filter->getDataType (), 'Filter data types do not match');
			$this->assertEquals ($field->getName (), $filter->getFieldName (), 'Filter field names do not match');
			$this->assertEquals ($field->getLabel (), $filter->getLabel (), 'Filter labels do not match');
			$this->assertEquals ($field->getModuleName (), $filter->getModuleName (), 'Filter module names do not match');
			$this->assertEquals ($field->getTableName (), $filter->getTableName (), 'Filter table names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_EQUALS, $filter->getComparator (), 'Comparators do not match');
			$this->assertEquals (2, $filter->getGroupId (), 'Group IDs do not match');
			$this->assertEquals (1, $filter->getSequence (), 'Sequences do not match');
			$this->assertEquals ('test_value', $filter->getValue (), 'Values do not match');
			$this->assertEquals (3, $filter->getReportId (), 'Report IDs do not match');

			$duplicatedObject = $object->duplicate (null, null);
			$this->assertEquals (ReportAdvancedFilterInterface::OPERATOR_AND, $duplicatedObject->getOperator (), 'Group operators do not match');
			$this->assertEquals (2, $duplicatedObject->getSequence (), 'Group sequences do not match');
			$this->assertEquals (null, $duplicatedObject->getReportId (), 'Group report IDs do not match');
			$this->assertCount (1, $duplicatedObject->getFilters (), 'Group filters count do not match');

			$filters = $duplicatedObject->getFilters ();
			$filter  = $filters [0];
			$this->assertEquals ($field->getColumnName (), $filter->getColumnName (), 'Filter column names do not match');
			$this->assertEquals ($field->getDataType (), $filter->getDataType (), 'Filter data types do not match');
			$this->assertEquals ($field->getName (), $filter->getFieldName (), 'Filter field names do not match');
			$this->assertEquals ($field->getLabel (), $filter->getLabel (), 'Filter labels do not match');
			$this->assertEquals ($field->getModuleName (), $filter->getModuleName (), 'Filter module names do not match');
			$this->assertEquals ($field->getTableName (), $filter->getTableName (), 'Filter table names do not match');
			$this->assertEquals (ReportAdvancedFilterInterface::COMPARATOR_EQUALS, $filter->getComparator (), 'Comparators do not match');
			$this->assertEquals (null, $filter->getGroupId (), 'Group IDs do not match');
			$this->assertEquals (1, $filter->getSequence (), 'Sequences do not match');
			$this->assertEquals ('test_value', $filter->getValue (), 'Values do not match');
			$this->assertEquals (null, $filter->getReportId (), 'Report IDs do not match');
		}

	}
	// @codingStandardsIgnoreEnd

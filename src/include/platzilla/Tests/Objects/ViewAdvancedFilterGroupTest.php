<?php
	require_once ('include/platzilla/Objects/ViewAdvancedFilterGroup.php');

	/**
	 * Prueba unitaria de la clase ViewAdvancedFilterGroup
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ViewAdvancedFilterGroupTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = ViewAdvancedFilterGroup::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'operator' => array ('', ViewAdvancedFilterInterface::OPERATOR_AND, ViewAdvancedFilterInterface::OPERATOR_OR),
				'sequence' => 0,
				'viewId'   => 3,
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
			$object = ViewAdvancedFilterGroup::getInstance ();
			$this->expectException (ViewAdvancedFilterGroupException::class);
			$this->expectExceptionMessage (ViewAdvancedFilterGroupException::ERROR_VIEW_ADVANCED_FILTER_GROUP_EMPTY_SEQUENCE);
			$object->validate ();
		}

		public function testInvalidFiltersValidation () {
			$object = ViewAdvancedFilterGroup::getInstance ()
				->setSequence (1)
				->setViewId (3)
				->setFilters (array (new stdClass ()));
			$this->expectException (ViewAdvancedFilterException::class);
			$this->expectExceptionMessage (ViewAdvancedFilterException::ERROR_VIEW_ADVANCED_FILTER_INVALID_FILTER);
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
			$object = ViewAdvancedFilterGroup::getInstance ()
				->setSequence (1)
				->setViewId (3)
				->setFilters (array (
					ViewAdvancedFilter::getInstance ($field)
						->setComparator (ViewAdvancedFilterInterface::COMPARATOR_EQUALS)
						->setGroupId (49)
						->setSequence (0)
						->setViewId (3),
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
			$object = ViewAdvancedFilterGroup::getInstance ()
				->setOperator (ViewAdvancedFilterInterface::OPERATOR_AND)
				->setSequence (2)
				->setViewId (3)
				->setFilters (array (
					ViewAdvancedFilter::getInstance ($field)
						->setComparator (ViewAdvancedFilterInterface::COMPARATOR_EQUALS)
						->setGroupId (2)
						->setSequence (1)
						->setValue ('test_value')
						->setViewId (3)
				));

			$duplicatedObject = $object->duplicate ($object->getViewId (), $object->getSequence ());
			$this->assertEquals (ViewAdvancedFilterInterface::OPERATOR_AND, $duplicatedObject->getOperator (), 'Group operators do not match');
			$this->assertEquals (2, $duplicatedObject->getSequence (), 'Group sequences do not match');
			$this->assertEquals (3, $duplicatedObject->getViewId (), 'Group view IDs do not match');
			$this->assertCount (1, $duplicatedObject->getFilters (), 'Group filters count do not match');

			$filters = $duplicatedObject->getFilters ();
			$filter = $filters [0];
			$this->assertEquals ($field->getColumnName (), $filter->getColumnName (), 'Filter column names do not match');
			$this->assertEquals ($field->getDataType (), $filter->getDataType (), 'Filter data types do not match');
			$this->assertEquals ($field->getName (), $filter->getFieldName (), 'Filter field names do not match');
			$this->assertEquals ($field->getLabel (), $filter->getLabel (), 'Filter labels do not match');
			$this->assertEquals ($field->getModuleName (), $filter->getModuleName (), 'Filter module names do not match');
			$this->assertEquals ($field->getTableName (), $filter->getTableName (), 'Filter table names do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_EQUALS, $filter->getComparator (), 'Comparators do not match');
			$this->assertEquals (2, $filter->getGroupId (), 'Group IDs do not match');
			$this->assertEquals (1, $filter->getSequence (), 'Sequences do not match');
			$this->assertEquals ('test_value', $filter->getValue (), 'Values do not match');
			$this->assertEquals (3, $filter->getViewId (), 'View IDs do not match');

			$duplicatedObject = $object->duplicate (null, null);
			$this->assertEquals (ViewAdvancedFilterInterface::OPERATOR_AND, $duplicatedObject->getOperator (), 'Group operators do not match');
			$this->assertEquals (2, $duplicatedObject->getSequence (), 'Group sequences do not match');
			$this->assertEquals (null, $duplicatedObject->getViewId (), 'Group view IDs do not match');
			$this->assertCount (1, $duplicatedObject->getFilters (), 'Group filters count do not match');

			$filters = $duplicatedObject->getFilters ();
			$filter  = $filters [0];
			$this->assertEquals ($field->getColumnName (), $filter->getColumnName (), 'Filter column names do not match');
			$this->assertEquals ($field->getDataType (), $filter->getDataType (), 'Filter data types do not match');
			$this->assertEquals ($field->getName (), $filter->getFieldName (), 'Filter field names do not match');
			$this->assertEquals ($field->getLabel (), $filter->getLabel (), 'Filter labels do not match');
			$this->assertEquals ($field->getModuleName (), $filter->getModuleName (), 'Filter module names do not match');
			$this->assertEquals ($field->getTableName (), $filter->getTableName (), 'Filter table names do not match');
			$this->assertEquals (ViewAdvancedFilterInterface::COMPARATOR_EQUALS, $filter->getComparator (), 'Comparators do not match');
			$this->assertEquals (null, $filter->getGroupId (), 'Group IDs do not match');
			$this->assertEquals (1, $filter->getSequence (), 'Sequences do not match');
			$this->assertEquals ('test_value', $filter->getValue (), 'Values do not match');
			$this->assertEquals (null, $filter->getViewId (), 'View IDs do not match');
		}

	}
	// @codingStandardsIgnoreEnd

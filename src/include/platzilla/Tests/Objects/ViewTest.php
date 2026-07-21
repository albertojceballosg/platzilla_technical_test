<?php
	require_once ('include/platzilla/Objects/View.php');

	/**
	 * Prueba unitaria de la clase View
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ViewTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = View::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'              => 2,
				'default'         => array (ViewInterface::DEFAULT_NO, ViewInterface::DEFAULT_YES),
				'moduleName'      => 'my_module_name',
				'name'            => 'my_view_name',
				'owner'           => 1,
				'showCountInMenu' => array (ViewInterface::SHOW_COUNT_NO, ViewInterface::SHOW_COUNT_YES),
				'status'          => array (ViewInterface::STATUS_APPROVED, ViewInterface::STATUS_PENDING, ViewInterface::STATUS_PRIVATE, ViewInterface::STATUS_PUBLIC),
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

		public function testColumnsGetterAndSetter () {
			$viewId     = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';

			$reference = FieldModuleReference::getInstance ()->setFieldName ('my_reference_field')->setModuleName ($moduleName)->setReferencedModuleName ('test_related_module');
			$fields    = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('my_code_field')->setName ('my_code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('my_number_field')->setName ('my_number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_MODULE_REFERENCE)->setColumnName ('my_reference_field')->setName ('my_reference_field')->setLabel ('My reference field')->setModuleName ($moduleName)->setTableName ($tableName)->setModuleReferences (array ($reference)),
			);
			$columns   = array (
				ViewColumn::getInstance ($fields [0])->setSequence (0)->setViewId ($viewId),
				ViewColumn::getInstance ($fields [1])->setSequence (1)->setViewId ($viewId),
				ViewColumn::getInstance ($fields [2])->setSequence (2)->setViewId ($viewId),
				ViewColumn::getInstance ($fields [3])->setSequence (3)->setViewId ($viewId),
				ViewColumn::getInstance ($fields [4])->setSequence (4)->setViewId ($viewId),
			);
			$view      = View::getInstance ()
				->setColumns ($columns)
				->setId ($viewId)
				->setModuleName ($moduleName)
				->setName ('my_view')
				->setOwner ($viewId);

			$this->assertEquals ($columns, $view->getColumns (), 'Columns do not match');
		}

		public function testStandardFilterGetterAndSetter () {
			$viewId         = 1;
			$moduleName     = 'my_module_name';
			$tableName      = 'vtiger_my_module_name';
			$field          = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName);
			$standardFilter = ViewStandardFilter::getInstance ($field)->setEndDate (date_create ('today'))->setPeriod (ViewStandardFilterInterface::PERIOD_CURRENT_MONTH)->setStartDate (date_create ('tomorrow'))->setViewId ($viewId);
			$view           = View::getInstance ()
				->setId ($viewId)
				->setModuleName ($moduleName)
				->setName ('my_view')
				->setOwner ($viewId)
				->setStandardFilter ($standardFilter);

			$this->assertEquals ($standardFilter, $view->getStandardFilter (), 'Standard filters do not match');
		}

		public function testAdvancedFilterGroupsGetterAndSetter () {
			$viewId     = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';
			$fields     = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('my_code_field')->setName ('my_code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('my_number_field')->setName ('my_number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$groups     = array (
				ViewAdvancedFilterGroup::getInstance ()
					->setSequence (0)
					->setOperator (ViewAdvancedFilterInterface::OPERATOR_AND)
					->setViewId ($viewId)
					->setFilters (array (
						ViewAdvancedFilter::getInstance ($fields [0])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setOperator (ViewAdvancedFilterInterface::OPERATOR_OR)->setSequence (0)->setValue ('MOL')->setViewId ($viewId),
						ViewAdvancedFilter::getInstance ($fields [1])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setSequence (1)->setValue ('COL')->setViewId ($viewId),
					)),
				ViewAdvancedFilterGroup::getInstance ()
					->setSequence (1)
					->setViewId ($viewId)
					->setFilters (array (
						ViewAdvancedFilter::getInstance ($fields [2])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_EQUALS)->setGroupId (1)->setOperator (ViewAdvancedFilterInterface::OPERATOR_AND)->setSequence (0)->setValue (1)->setViewId ($viewId),
						ViewAdvancedFilter::getInstance ($fields [3])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_NOT_EQUALS)->setGroupId (1)->setSequence (1)->setValue ('test_module')->setViewId ($viewId),
					)),
			);
			$view       = View::getInstance ()
				->setId ($viewId)
				->setModuleName ($moduleName)
				->setName ('my_view')
				->setOwner ($viewId)
				->setAdvancedFilterGroups ($groups);

			$this->assertEquals ($groups, $view->getAdvancedFilterGroups (), 'Advanced filter groups do not match');
		}

		public function testEmptyNameValidation () {
			$object = View::getInstance ()
				->setModuleName ('my_module_name');
			$this->expectException (ViewException::class);
			$this->expectExceptionMessage (ViewException::ERROR_VIEW_EMPTY_NAME);
			$object->validate ();
		}

		public function testEmptyOwnerValidation () {
			$object = View::getInstance ()
				->setModuleName ('my_module_name')
				->setName ('my_view_name');
			$this->expectException (ViewException::class);
			$this->expectExceptionMessage (ViewException::ERROR_VIEW_EMPTY_OWNER);
			$object->validate ();
		}

		public function testEmptyOrInvalidColumnsValidation () {
			$object = View::getInstance ()
				->setModuleName ('my_module_name')
				->setName ('my_view_name')
				->setOwner (1);
			$this->expectException (ViewException::class);
			$this->expectExceptionMessage (ViewException::ERROR_VIEW_INVALID_COLUMNS);
			$object->validate ();

			/** @noinspection PhpParamsInspection */
			$object = View::getInstance ()
				->setModuleName ('my_module_name')
				->setName ('my_view_name')
				->setOwner (1)
				->setColumns (ViewColumn::getInstance ());
			$this->expectException (ViewException::class);
			$this->expectExceptionMessage (ViewException::ERROR_VIEW_INVALID_COLUMNS);
			$object->validate ();
		}

		public function testInvalidColumnValidation () {
			$object = View::getInstance ()
				->setId (1)
				->setModuleName ('my_module_name')
				->setName ('my_view_name')
				->setOwner (1)
				->setColumns (array (
					null,
					ViewColumn::getInstance (),
					ViewColumn::getInstance (),
				));
			$this->expectException (ViewException::class);
			$object->validate ();

			$object = View::getInstance ()
				->setId (1)
				->setModuleName ('my_module_name')
				->setName ('my_view_name')
				->setOwner (1)
				->setColumns (array (
					ViewColumn::getInstance (),
					ViewColumn::getInstance (),
				));
			$this->expectException (ViewColumnException::class);
			$object->validate ();
		}

		public function testInvalidStandardFilterValidation () {
			$viewId     = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';
			$viewName   = 'my_view_name';
			$field      = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName);

			/** @noinspection PhpParamsInspection */
			$object = View::getInstance ()
				->setId ($viewId)
				->setModuleName ($moduleName)
				->setName ($viewName)
				->setOwner (1)
				->setColumns (array (
					ViewColumn::getInstance ($field)->setSequence (0)->setViewId ($viewId),
				))
				->setStandardFilter ('this is not a standard filter');
			$this->expectException (ViewException::class);
			$this->expectExceptionMessage (ViewException::ERROR_VIEW_INVALID_STANDARD_FILTER);
			$object->validate ();

			$object = View::getInstance ()
				->setId ($viewId)
				->setModuleName ($moduleName)
				->setName ($viewName)
				->setOwner (1)
				->setColumns (array (
					ViewColumn::getInstance ($field)->setSequence (0)->setViewId ($viewId),
				))
				->setStandardFilter (ViewStandardFilter::getInstance ($field));
			$this->expectException (ViewStandardFilterException::class);
			$object->validate ();
		}

		public function testInvalidAdvancedFilterGroupsValidation () {
			$viewId     = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';
			$viewName   = 'my_view_name';
			$field      = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName);

			/** @noinspection PhpParamsInspection */
			$object = View::getInstance ()
				->setId ($viewId)
				->setModuleName ($moduleName)
				->setName ($viewName)
				->setOwner (1)
				->setColumns (array (
					ViewColumn::getInstance ($field)->setSequence (0)->setViewId ($viewId),
				))
				->setAdvancedFilterGroups ('this is not an advanced filter group array');
			$this->expectException (ViewException::class);
			$this->expectExceptionMessage (ViewException::ERROR_VIEW_INVALID_ADVANCED_FILTER_GROUPS);
			$object->validate ();

			$object = View::getInstance ()
				->setId ($viewId)
				->setModuleName ($moduleName)
				->setName ($viewName)
				->setOwner (1)
				->setColumns (array (
					ViewColumn::getInstance ($field)->setSequence (0)->setViewId ($viewId),
				))
				->setAdvancedFilterGroups (array (
					null,
					null,
				));
			$this->expectException (ViewException::class);
			$this->expectExceptionMessage (ViewException::ERROR_VIEW_INVALID_ADVANCED_FILTER_GROUP);
			$object->validate ();

			$object = View::getInstance ()
				->setId ($viewId)
				->setModuleName ($moduleName)
				->setName ($viewName)
				->setOwner (1)
				->setColumns (array (
					ViewColumn::getInstance ($field)->setSequence (0)->setViewId ($viewId),
				))
				->setAdvancedFilterGroups (array (
					ViewAdvancedFilterGroup::getInstance (),
					ViewAdvancedFilterGroup::getInstance (),
				));
			$this->expectException (ViewAdvancedFilterGroupException::class);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$viewId     = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';
			$viewName   = 'my_view_name';
			$field      = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName);
			$fields     = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('my_code_field')->setName ('my_code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('my_number_field')->setName ('my_number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$object     = View::getInstance ()
				->setId ($viewId)
				->setModuleName ($moduleName)
				->setName ($viewName)
				->setOwner (1)
				->setColumns (array (
					ViewColumn::getInstance ($field)->setSequence (0)->setViewId ($viewId),
				))
				->setAdvancedFilterGroups (array (
					ViewAdvancedFilterGroup::getInstance ()
						->setSequence (0)
						->setOperator (ViewAdvancedFilterInterface::OPERATOR_AND)
						->setViewId ($viewId)
						->setFilters (array (
							ViewAdvancedFilter::getInstance ($fields [0])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setOperator (ViewAdvancedFilterInterface::OPERATOR_OR)->setSequence (0)->setValue ('MOL')->setViewId ($viewId),
							ViewAdvancedFilter::getInstance ($fields [1])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setSequence (1)->setValue ('COL')->setViewId ($viewId),
						)),
					ViewAdvancedFilterGroup::getInstance ()
						->setSequence (1)
						->setViewId ($viewId)
						->setFilters (array (
							ViewAdvancedFilter::getInstance ($fields [2])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_EQUALS)->setGroupId (1)->setOperator (ViewAdvancedFilterInterface::OPERATOR_AND)->setSequence (0)->setValue (1)->setViewId ($viewId),
							ViewAdvancedFilter::getInstance ($fields [3])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_NOT_EQUALS)->setGroupId (1)->setSequence (1)->setValue ('test_module')->setViewId ($viewId),
						)),
				));
			$object->validate ();

			$this->assertEquals (ViewInterface::DEFAULT_NO, $object->getDefault (), 'View default properties do not match');
			$this->assertEquals (ViewInterface::SHOW_COUNT_YES, $object->getShowCountInMenu (), 'View show count in menu properties do not match');
			$this->assertEquals (ViewInterface::STATUS_PRIVATE, $object->getStatus (), 'View statuses do not match');
		}

		public function testDuplicate () {
			$viewId     = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';
			$viewName   = 'my_view_name';
			$field      = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName);
			/** @var Field[] $fields */
			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('my_code_field')->setName ('my_code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('my_number_field')->setName ('my_number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$object = View::getInstance ()
				->setId ($viewId)
				->setModuleName ($moduleName)
				->setName ($viewName)
				->setOwner (1)
				->setColumns (array (
					ViewColumn::getInstance ($fields [0])->setSequence (0)->setViewId ($viewId),
					ViewColumn::getInstance ($fields [1])->setSequence (1)->setViewId ($viewId),
					ViewColumn::getInstance ($fields [2])->setSequence (2)->setViewId ($viewId),
				))
				->setAdvancedFilterGroups (array (
					ViewAdvancedFilterGroup::getInstance ()
						->setSequence (0)
						->setOperator (ViewAdvancedFilterInterface::OPERATOR_AND)
						->setViewId ($viewId)
						->setFilters (array (
							ViewAdvancedFilter::getInstance ($fields [0])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setOperator (ViewAdvancedFilterInterface::OPERATOR_AND)->setSequence (0)->setValue ('MOL')->setViewId ($viewId),
							ViewAdvancedFilter::getInstance ($fields [1])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setSequence (1)->setValue ('COL')->setViewId ($viewId),
						)),
					ViewAdvancedFilterGroup::getInstance ()
						->setSequence (1)
						->setViewId ($viewId)
						->setFilters (array (
							ViewAdvancedFilter::getInstance ($fields [2])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_NOT_EQUALS)->setGroupId (1)->setOperator (ViewAdvancedFilterInterface::OPERATOR_AND)->setSequence (0)->setValue ('MOL')->setViewId ($viewId),
							ViewAdvancedFilter::getInstance ($fields [3])->setComparator (ViewAdvancedFilterInterface::COMPARATOR_NOT_EQUALS)->setGroupId (1)->setSequence (1)->setValue ('COL')->setViewId ($viewId),
						)),
				))
				->setStandardFilter (ViewStandardFilter::getInstance ($field)->setEndDate ('2017-12-31')->setPeriod (ViewStandardFilterInterface::PERIOD_CUSTOM)->setStartDate ('2017-12-01')->setViewId ($viewId));

			$duplicatedObject = $object->duplicate ($object->getId (), $object->getOwner ());
			$this->assertEquals ($viewId, $duplicatedObject->getId (), 'View IDs do not match');
			$this->assertEquals ($moduleName, $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ($viewName, $duplicatedObject->getName (), 'View names do not match');
			$this->assertEquals (1, $duplicatedObject->getOwner (), 'Owner IDs do not match');

			$columns = $duplicatedObject->getColumns ();
			$this->assertCount (3, $columns, 'View columns count do not match');
			foreach ($columns as $index => $column) {
				$this->assertEquals ($fields [ $index ]->getColumnName (), $column->getColumnName (), 'Column names do not match');
				$this->assertEquals ($fields [ $index ]->getDataType (), $column->getDataType (), 'Data types do not match');
				$this->assertEquals ($fields [ $index ]->getName (), $column->getFieldName (), 'Field names do not match');
				$this->assertEquals ($fields [ $index ]->getLabel (), $column->getLabel (), 'Labels do not match');
				$this->assertEquals ($fields [ $index ]->getModuleName (), $column->getModuleName (), 'Module names do not match');
				$this->assertEquals ($fields [ $index ]->getTableName (), $column->getTableName (), 'Table names do not match');
				$this->assertEquals ($index, $column->getSequence (), 'Sequences do not match');
				$this->assertEquals ($viewId, $column->getViewId (), 'View IDs do not match');
			}

			$standardFilter = $duplicatedObject->getStandardFilter ();
			$this->assertNotNull ($standardFilter, 'Standard filter should not be null');
			$this->assertEquals ($field->getColumnName (), $standardFilter->getColumnName (), 'Column names do not match');
			$this->assertEquals (date_create ('2017-12-31'), $standardFilter->getEndDate (), 'End dates do not match');
			$this->assertEquals ($field->getName (), $standardFilter->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $standardFilter->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $standardFilter->getModuleName (), 'Module names do not match');
			$this->assertEquals (ViewStandardFilterInterface::PERIOD_CUSTOM, $standardFilter->getPeriod (), 'Periods do not match');
			$this->assertEquals (date_create ('2017-12-01'), $standardFilter->getStartDate (), 'Start dates do not match');
			$this->assertEquals ($field->getTableName (), $standardFilter->getTableName (), 'Table names do not match');
			$this->assertEquals ($viewId, $standardFilter->getViewId (), 'View IDs do not match');

			$index                = 0;
			$advancedFilterGroups = $duplicatedObject->getAdvancedFilterGroups ();
			$this->assertCount (3, $columns, 'Advanced filter groups count do not match');
			foreach ($advancedFilterGroups as $i => $group) {
				$operator = $i == 0 ? ViewAdvancedFilterInterface::OPERATOR_AND : null;
				$this->assertEquals ($operator, $group->getOperator (), 'Group operators do not match');
				$this->assertEquals ($i, $group->getSequence (), 'Group sequences do not match');
				$this->assertEquals ($viewId, $group->getViewId (), 'Group view IDs do not match');

				$filters = $group->getFilters ();
				$this->assertCount (2, $filters, 'Group filters count do not match');
				foreach ($filters as $j => $filter) {
					$filterField = $fields [ $index ];
					$comparator  = $i == 0 ? ViewAdvancedFilterInterface::COMPARATOR_CONTAINS : ViewAdvancedFilterInterface::COMPARATOR_NOT_EQUALS;
					$operator    = $j == 0 ? ViewAdvancedFilterInterface::OPERATOR_AND : null;
					$value       = $j == 0 ? 'MOL' : 'COL';
					$this->assertEquals ($filterField->getColumnName (), $filter->getColumnName (), 'Filter column names do not match');
					$this->assertEquals ($filterField->getDataType (), $filter->getDataType (), 'Filter data types do not match');
					$this->assertEquals ($filterField->getName (), $filter->getFieldName (), 'Filter field names do not match');
					$this->assertEquals ($filterField->getLabel (), $filter->getLabel (), 'Filter labels do not match');
					$this->assertEquals ($filterField->getModuleName (), $filter->getModuleName (), 'Filter module names do not match');
					$this->assertEquals ($filterField->getTableName (), $filter->getTableName (), 'Filter table names do not match');
					$this->assertEquals ($comparator, $filter->getComparator (), 'Comparators do not match');
					$this->assertEquals ($i, $filter->getGroupId (), 'Group IDs do not match');
					$this->assertEquals ($operator, $filter->getOperator (), 'Operators do not match');
					$this->assertEquals ($j, $filter->getSequence (), 'Sequences do not match');
					$this->assertEquals ($value, $filter->getValue (), 'Values do not match');
					$this->assertEquals ($viewId, $filter->getViewId (), 'View IDs do not match');
					$index++;
				}
			}

			$duplicatedObject = $object->duplicate (null, 1);
			$this->assertEquals (null, $duplicatedObject->getId (), 'View IDs do not match');
			$this->assertEquals ($moduleName, $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ($viewName, $duplicatedObject->getName (), 'View names do not match');
			$this->assertEquals (1, $duplicatedObject->getOwner (), 'Owner IDs do not match');

			$columns = $duplicatedObject->getColumns ();
			$this->assertCount (3, $columns, 'View columns count do not match');
			foreach ($columns as $index => $column) {
				$this->assertEquals ($fields [ $index ]->getColumnName (), $column->getColumnName (), 'Column names do not match');
				$this->assertEquals ($fields [ $index ]->getDataType (), $column->getDataType (), 'Data types do not match');
				$this->assertEquals ($fields [ $index ]->getName (), $column->getFieldName (), 'Field names do not match');
				$this->assertEquals ($fields [ $index ]->getLabel (), $column->getLabel (), 'Labels do not match');
				$this->assertEquals ($fields [ $index ]->getModuleName (), $column->getModuleName (), 'Module names do not match');
				$this->assertEquals ($fields [ $index ]->getTableName (), $column->getTableName (), 'Table names do not match');
				$this->assertEquals ($index, $column->getSequence (), 'Sequences do not match');
				$this->assertEquals (null, $column->getViewId (), 'View IDs do not match');
			}

			$standardFilter = $duplicatedObject->getStandardFilter ();
			$this->assertNotNull ($standardFilter, 'Standard filter should not be null');
			$this->assertEquals ($field->getColumnName (), $standardFilter->getColumnName (), 'Column names do not match');
			$this->assertEquals (date_create ('2017-12-31'), $standardFilter->getEndDate (), 'End dates do not match');
			$this->assertEquals ($field->getName (), $standardFilter->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $standardFilter->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $standardFilter->getModuleName (), 'Module names do not match');
			$this->assertEquals (ViewStandardFilterInterface::PERIOD_CUSTOM, $standardFilter->getPeriod (), 'Periods do not match');
			$this->assertEquals (date_create ('2017-12-01'), $standardFilter->getStartDate (), 'Start dates do not match');
			$this->assertEquals ($field->getTableName (), $standardFilter->getTableName (), 'Table names do not match');
			$this->assertEquals (null, $standardFilter->getViewId (), 'View IDs do not match');

			$index                = 0;
			$advancedFilterGroups = $duplicatedObject->getAdvancedFilterGroups ();
			$this->assertCount (3, $columns, 'Advanced filter groups count do not match');
			foreach ($advancedFilterGroups as $i => $group) {
				$operator = $i == 0 ? ViewAdvancedFilterInterface::OPERATOR_AND : null;
				$this->assertEquals ($operator, $group->getOperator (), 'Group operators do not match');
				$this->assertEquals ($i, $group->getSequence (), 'Group sequences do not match');
				$this->assertEquals (null, $group->getViewId (), 'Group view IDs do not match');

				$filters = $group->getFilters ();
				$this->assertCount (2, $filters, 'Group filters count do not match');
				foreach ($filters as $j => $filter) {
					$filterField = $fields [ $index ];
					$comparator  = $i == 0 ? ViewAdvancedFilterInterface::COMPARATOR_CONTAINS : ViewAdvancedFilterInterface::COMPARATOR_NOT_EQUALS;
					$operator    = $j == 0 ? ViewAdvancedFilterInterface::OPERATOR_AND : null;
					$value       = $j == 0 ? 'MOL' : 'COL';
					$this->assertEquals ($filterField->getColumnName (), $filter->getColumnName (), 'Filter column names do not match');
					$this->assertEquals ($filterField->getDataType (), $filter->getDataType (), 'Filter data types do not match');
					$this->assertEquals ($filterField->getName (), $filter->getFieldName (), 'Filter field names do not match');
					$this->assertEquals ($filterField->getLabel (), $filter->getLabel (), 'Filter labels do not match');
					$this->assertEquals ($filterField->getModuleName (), $filter->getModuleName (), 'Filter module names do not match');
					$this->assertEquals ($filterField->getTableName (), $filter->getTableName (), 'Filter table names do not match');
					$this->assertEquals ($comparator, $filter->getComparator (), 'Comparators do not match');
					$this->assertEquals ($i, $filter->getGroupId (), 'Group IDs do not match');
					$this->assertEquals ($operator, $filter->getOperator (), 'Operators do not match');
					$this->assertEquals ($j, $filter->getSequence (), 'Sequences do not match');
					$this->assertEquals ($value, $filter->getValue (), 'Values do not match');
					$this->assertEquals (null, $filter->getViewId (), 'View IDs do not match');
					$index++;
				}
			}
		}

	}
	// @codingStandardsIgnoreEnd

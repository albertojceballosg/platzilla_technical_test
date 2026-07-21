<?php
	require_once ('include/platzilla/Objects/Report.php');

	/**
	 * Prueba unitaria de la clase Report
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ReportTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = Report::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'                 => 2,
				'applicationCodes'   => array (array ('my_first_application', 'my_second_application')),
				'description'        => 'My report description',
				'folder'             => ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED),
				'moduleName'         => 'my_module_name',
				'name'               => 'My report name',
				'owner'              => 1,
				'relatedModuleNames' => array (array ('my_related_module_one', 'my_related_module_two')),
				'schedule'           => ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_DAILY, '09:00')->setFormat (ReportScheduleInterface::FORMAT_BOTH)->setReportId (2)->setUsers (array (1)),
				'status'             => array (ReportInterface::STATUS_CUSTOMIZED, ReportInterface::STATUS_SAVED),
				'type'               => array (ReportInterface::TYPE_SUMMARY, ReportInterface::TYPE_TABULAR),
				'visibility'         => array (ReportInterface::VISIBILITY_PRIVATE, ReportInterface::VISIBILITY_PUBLIC, ReportInterface::VISIBILITY_SHARED),
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

		public function testAdvancedFilterGroupsGetterAndSetter () {
			$reportId   = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';
			$fields     = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('my_code_field')->setName ('my_code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('my_number_field')->setName ('my_number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$groups     = array (
				ReportAdvancedFilterGroup::getInstance ()
					->setSequence (0)
					->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)
					->setReportId ($reportId)
					->setFilters (array (
						ReportAdvancedFilter::getInstance ($fields [0])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setOperator (ReportAdvancedFilterInterface::OPERATOR_OR)->setSequence (0)->setValue ('MOL')->setReportId ($reportId),
						ReportAdvancedFilter::getInstance ($fields [1])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setSequence (1)->setValue ('COL')->setReportId ($reportId),
					)),
				ReportAdvancedFilterGroup::getInstance ()
					->setSequence (1)
					->setReportId ($reportId)
					->setFilters (array (
						ReportAdvancedFilter::getInstance ($fields [2])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)->setGroupId (1)->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)->setSequence (0)->setValue (1)->setReportId ($reportId),
						ReportAdvancedFilter::getInstance ($fields [3])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_NOT_EQUALS)->setGroupId (1)->setSequence (1)->setValue ('test_module')->setReportId ($reportId),
					)),
			);
			$report     = Report::getInstance ()
				->setId ($reportId)
				->setModuleName ($moduleName)
				->setName ('my_Report')
				->setOwner (1)
				->setAdvancedFilterGroups ($groups);

			$this->assertEquals ($groups, $report->getAdvancedFilterGroups (), 'Advanced filter groups do not match');
		}

		public function testColumnsGetterAndSetter () {
			$reportId   = 1;
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
				ReportColumn::getInstance ($fields [0])->setSequence (0)->setReportId ($reportId),
				ReportColumn::getInstance ($fields [1])->setSequence (1)->setReportId ($reportId),
				ReportColumn::getInstance ($fields [2])->setSequence (2)->setReportId ($reportId),
				ReportColumn::getInstance ($fields [3])->setSequence (3)->setReportId ($reportId),
				ReportColumn::getInstance ($fields [4])->setSequence (4)->setReportId ($reportId),
			);
			$report    = Report::getInstance ()
				->setColumns ($columns)
				->setId ($reportId)
				->setModuleName ($moduleName)
				->setName ('My report')
				->setOwner ($reportId);

			$this->assertEquals ($columns, $report->getColumns (), 'Columns do not match');
		}

		public function testShareWithGetterAndSetter () {
			$reportId   = 1;
			$moduleName = 'my_module_name';

			$entities = array (
				ReportSharingEntity::getInstance ()->setId (1)->setType (ReportSharingEntityInterface::TYPE_USER),
				ReportSharingEntity::getInstance ()->setId (10)->setType (ReportSharingEntityInterface::TYPE_GROUP),
			);
			$report    = Report::getInstance ()
				->setId ($reportId)
				->setModuleName ($moduleName)
				->setName ('My report')
				->setOwner ($reportId)
				->setShareWith ($entities);

			$this->assertEquals ($entities, $report->getShareWith (), 'Sharing entities do not match');
		}

		public function testSortColumnsGetterAndSetter () {
			$reportId   = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';

			$fields    = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('my_code_field')->setName ('my_code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('my_number_field')->setName ('my_number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$columns   = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0)->setReportId ($reportId),
				ReportColumn::getInstance ($fields [1])->setSequence (1)->setReportId ($reportId),
				ReportColumn::getInstance ($fields [2])->setSequence (2)->setReportId ($reportId),
			);
			$report    = Report::getInstance ()
				->setId ($reportId)
				->setModuleName ($moduleName)
				->setName ('My report')
				->setOwner ($reportId)
				->setSortColumns ($columns);

			$this->assertEquals ($columns, $report->getSortColumns (), 'Columns do not match');
		}

		public function testStandardFilterGetterAndSetter () {
			$reportId       = 1;
			$moduleName     = 'my_module_name';
			$tableName      = 'vtiger_my_module_name';
			$field          = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName);
			$standardFilter = ReportStandardFilter::getInstance ($field)->setEndDate (date_create ('today'))->setPeriod (ReportStandardFilterInterface::PERIOD_CURRENT_MONTH)->setStartDate (date_create ('tomorrow'))->setReportId ($reportId);
			$report         = Report::getInstance ()
				->setId ($reportId)
				->setModuleName ($moduleName)
				->setName ('My report')
				->setOwner ($reportId)
				->setStandardFilter ($standardFilter);

			$this->assertEquals ($standardFilter, $report->getStandardFilter (), 'Standard filters do not match');
		}

		public function testTotalColumnsGetterAndSetter () {
			$reportId   = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';

			$fields  = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('my_code_field')->setName ('my_code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('my_number_field')->setName ('my_number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$columns = array (
				ReportColumn::getInstance ($fields [0])->setSequence (0)->setReportId ($reportId),
				ReportColumn::getInstance ($fields [1])->setSequence (1)->setReportId ($reportId),
				ReportColumn::getInstance ($fields [2])->setSequence (2)->setReportId ($reportId),
			);
			$report  = Report::getInstance ()
				->setId ($reportId)
				->setModuleName ($moduleName)
				->setName ('My report')
				->setOwner ($reportId)
				->setTotalColumns ($columns);

			$this->assertEquals ($columns, $report->getTotalColumns (), 'Columns do not match');
		}

		public function testEmptyApplicationCodesValidation () {
			$object = Report::getInstance ();
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_EMPTY_APPLICATION_CODES);
			$object->validate ();
		}

		public function testEmptyFolderValidation () {
			$object = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'));
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_EMPTY_FOLDER);
			$object->validate ();
		}

		public function testEmptyModuleNameValidation () {
			$object = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED));
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_EMPTY_MODULE_NAME);
			$object->validate ();
		}

		public function testEmptyNameValidation () {
			$object = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ('my_module_name');
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_EMPTY_NAME);
			$object->validate ();
		}

		public function testEmptyOwnerValidation () {
			$object = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ('my_module_name')
				->setName ('My report');
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_EMPTY_OWNER);
			$object->validate ();
		}

		public function testEmptyTypeValidation () {
			$object = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ('my_module_name')
				->setName ('My report')
				->setOwner (1);
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_EMPTY_TYPE);
			$object->validate ();
		}

		public function testEmptyVisibilityValidation () {
			$object = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ('my_module_name')
				->setName ('My report')
				->setOwner (1)
				->setType (ReportInterface::TYPE_SUMMARY);
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_EMPTY_VISIBILITY);
			$object->validate ();
		}

		public function testEmptyOrInvalidColumnsValidation () {
			$object = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ('my_module_name')
				->setName ('My report')
				->setOwner (1)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_INVALID_COLUMNS);
			$object->validate ();

			/** @noinspection PhpParamsInspection */
			$object = Report::getInstance ()
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setColumns (ReportColumn::getInstance ())
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ('my_module_name')
				->setName ('My report')
				->setOwner (1)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC);
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_INVALID_COLUMNS);
			$object->validate ();
		}

		public function testInvalidAdvancedFilterGroupsValidation () {
			$reportId   = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';
			$reportName = 'My report';
			$field      = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName);

			/** @noinspection PhpParamsInspection */
			$object = Report::getInstance ()
				->setId ($reportId)
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC)
				->setColumns (array (
					ReportColumn::getInstance ($field)->setSequence (0)->setReportId ($reportId),
				))
				->setAdvancedFilterGroups ('this is not an advanced filter group array');
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_INVALID_ADVANCED_FILTER_GROUPS);
			$object->validate ();

			$object = Report::getInstance ()
				->setId ($reportId)
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC)
				->setColumns (array (
					ReportColumn::getInstance ($field)->setSequence (0)->setReportId ($reportId),
				))
				->setAdvancedFilterGroups (array (
					null,
					null,
				));
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_INVALID_ADVANCED_FILTER_GROUP);
			$object->validate ();

			$object = Report::getInstance ()
				->setId ($reportId)
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC)
				->setColumns (array (
					ReportColumn::getInstance ($field)->setSequence (0)->setReportId ($reportId),
				))
				->setAdvancedFilterGroups (array (
					ReportAdvancedFilterGroup::getInstance (),
					ReportAdvancedFilterGroup::getInstance (),
				));
			$this->expectException (ReportAdvancedFilterGroupException::class);
			$object->validate ();
		}

		public function testInvalidColumnValidation () {
			$object = Report::getInstance ()
				->setId (1)
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ('my_module_name')
				->setName ('My report')
				->setOwner (1)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC)
				->setColumns (array (
					null,
					ReportColumn::getInstance (),
					ReportColumn::getInstance (),
				));
			$this->expectException (ReportException::class);
			$object->validate ();

			$object = Report::getInstance ()
				->setId (1)
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ('my_module_name')
				->setName ('My report')
				->setOwner (1)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC)
				->setColumns (array (
					ReportColumn::getInstance (),
					ReportColumn::getInstance (),
				));
			$this->expectException (ReportColumnException::class);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$reportId   = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';
			$reportName = 'My report';
			$field      = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName);
			$fields     = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('my_code_field')->setName ('my_code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('my_number_field')->setName ('my_number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$object     = Report::getInstance ()
				->setId ($reportId)
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC)
				->setColumns (array (
					ReportColumn::getInstance ($field)->setSequence (0)->setReportId ($reportId),
				))
				->setAdvancedFilterGroups (array (
					ReportAdvancedFilterGroup::getInstance ()
						->setSequence (0)
						->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)
						->setReportId ($reportId)
						->setFilters (array (
							ReportAdvancedFilter::getInstance ($fields [0])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setOperator (ReportAdvancedFilterInterface::OPERATOR_OR)->setSequence (0)->setValue ('MOL')->setReportId ($reportId),
							ReportAdvancedFilter::getInstance ($fields [1])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setSequence (1)->setValue ('COL')->setReportId ($reportId),
						)),
					ReportAdvancedFilterGroup::getInstance ()
						->setSequence (1)
						->setReportId ($reportId)
						->setFilters (array (
							ReportAdvancedFilter::getInstance ($fields [2])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_EQUALS)->setGroupId (1)->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)->setSequence (0)->setValue (1)->setReportId ($reportId),
							ReportAdvancedFilter::getInstance ($fields [3])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_NOT_EQUALS)->setGroupId (1)->setSequence (1)->setValue ('test_module')->setReportId ($reportId),
						)),
				));
			$object->validate ();
		}

		public function testDuplicate () {
			$reportId   = 1;
			$moduleName = 'my_module_name';
			$tableName  = 'vtiger_my_module_name';
			$reportName = 'My report';
			$field      = Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName);
			/** @var Field[] $fields */
			$fields = array (
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_CODE)->setColumnName ('my_code_field')->setName ('my_code_field')->setLabel ('My code field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_TEXT)->setColumnName ('my_text_field')->setName ('my_text_field')->setLabel ('My text field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_NUMBER)->setColumnName ('my_number_field')->setName ('my_number_field')->setLabel ('My number field')->setModuleName ($moduleName)->setTableName ($tableName),
				Field::getInstance ()->setUiType (FieldInterface::UI_TYPE_DATETIME)->setColumnName ('my_datetime_field')->setName ('my_datetime_field')->setLabel ('My datetime field')->setModuleName ($moduleName)->setTableName ($tableName),
			);
			$object = Report::getInstance ()
				->setId ($reportId)
				->setApplicationCodes (array ('crm', 'facturacion'))
				->setFolder (ReportFolder::getInstance ()->setDescription ('My report folder description')->setId (4)->setName ('My report folder')->setStatus (ReportInterface::STATUS_SAVED))
				->setModuleName ($moduleName)
				->setName ($reportName)
				->setOwner (1)
				->setType (ReportInterface::TYPE_SUMMARY)
				->setVisibility (ReportInterface::VISIBILITY_PUBLIC)
				->setColumns (array (
					ReportColumn::getInstance ($fields [0])->setSequence (0)->setReportId ($reportId),
					ReportColumn::getInstance ($fields [1])->setSequence (1)->setReportId ($reportId),
					ReportColumn::getInstance ($fields [2])->setSequence (2)->setReportId ($reportId),
				))
				->setAdvancedFilterGroups (array (
					ReportAdvancedFilterGroup::getInstance ()
						->setSequence (0)
						->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)
						->setReportId ($reportId)
						->setFilters (array (
							ReportAdvancedFilter::getInstance ($fields [0])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)->setSequence (0)->setValue ('MOL')->setReportId ($reportId),
							ReportAdvancedFilter::getInstance ($fields [1])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_CONTAINS)->setGroupId (0)->setSequence (1)->setValue ('COL')->setReportId ($reportId),
						)),
					ReportAdvancedFilterGroup::getInstance ()
						->setSequence (1)
						->setReportId ($reportId)
						->setFilters (array (
							ReportAdvancedFilter::getInstance ($fields [2])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_NOT_EQUALS)->setGroupId (1)->setOperator (ReportAdvancedFilterInterface::OPERATOR_AND)->setSequence (0)->setValue ('MOL')->setReportId ($reportId),
							ReportAdvancedFilter::getInstance ($fields [3])->setComparator (ReportAdvancedFilterInterface::COMPARATOR_NOT_EQUALS)->setGroupId (1)->setSequence (1)->setValue ('COL')->setReportId ($reportId),
						)),
				))
				->setStandardFilter (ReportStandardFilter::getInstance ($field)->setEndDate ('2017-12-31')->setPeriod (ReportStandardFilterInterface::PERIOD_CUSTOM)->setStartDate ('2017-12-01')->setReportId ($reportId));

			$duplicatedObject = $object->duplicate ($object->getId (), $object->getOwner ());
			$this->assertEquals ($reportId, $duplicatedObject->getId (), 'Report IDs do not match');
			$this->assertEquals ($moduleName, $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ($reportName, $duplicatedObject->getName (), 'Report names do not match');
			$this->assertEquals (1, $duplicatedObject->getOwner (), 'Owner IDs do not match');

			$columns = $duplicatedObject->getColumns ();
			$this->assertCount (3, $columns, 'Report columns count do not match');
			foreach ($columns as $index => $column) {
				$this->assertEquals ($fields [ $index ]->getColumnName (), $column->getColumnName (), 'Column names do not match');
				$this->assertEquals ($fields [ $index ]->getDataType (), $column->getDataType (), 'Data types do not match');
				$this->assertEquals ($fields [ $index ]->getName (), $column->getFieldName (), 'Field names do not match');
				$this->assertEquals ($fields [ $index ]->getLabel (), $column->getLabel (), 'Labels do not match');
				$this->assertEquals ($fields [ $index ]->getModuleName (), $column->getModuleName (), 'Module names do not match');
				$this->assertEquals ($fields [ $index ]->getTableName (), $column->getTableName (), 'Table names do not match');
				$this->assertEquals ($index, $column->getSequence (), 'Sequences do not match');
				$this->assertEquals ($reportId, $column->getReportId (), 'Report IDs do not match');
			}

			$standardFilter = $duplicatedObject->getStandardFilter ();
			$this->assertNotNull ($standardFilter, 'Standard filter should not be null');
			$this->assertEquals ($field->getColumnName (), $standardFilter->getColumnName (), 'Column names do not match');
			$this->assertEquals (date_create ('2017-12-31'), $standardFilter->getEndDate (), 'End dates do not match');
			$this->assertEquals ($field->getName (), $standardFilter->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $standardFilter->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $standardFilter->getModuleName (), 'Module names do not match');
			$this->assertEquals (ReportStandardFilterInterface::PERIOD_CUSTOM, $standardFilter->getPeriod (), 'Periods do not match');
			$this->assertEquals (date_create ('2017-12-01'), $standardFilter->getStartDate (), 'Start dates do not match');
			$this->assertEquals ($field->getTableName (), $standardFilter->getTableName (), 'Table names do not match');
			$this->assertEquals ($reportId, $standardFilter->getReportId (), 'Report IDs do not match');

			$index                = 0;
			$advancedFilterGroups = $duplicatedObject->getAdvancedFilterGroups ();
			$this->assertCount (3, $columns, 'Advanced filter groups count do not match');
			foreach ($advancedFilterGroups as $i => $group) {
				$operator = $i == 0 ? ReportAdvancedFilterInterface::OPERATOR_AND : null;
				$this->assertEquals ($operator, $group->getOperator (), 'Group operators do not match');
				$this->assertEquals ($i, $group->getSequence (), 'Group sequences do not match');
				$this->assertEquals ($reportId, $group->getReportId (), 'Group Report IDs do not match');

				$filters = $group->getFilters ();
				$this->assertCount (2, $filters, 'Group filters count do not match');
				foreach ($filters as $j => $filter) {
					$filterField = $fields [ $index ];
					$comparator  = $i == 0 ? ReportAdvancedFilterInterface::COMPARATOR_CONTAINS : ReportAdvancedFilterInterface::COMPARATOR_NOT_EQUALS;
					$operator    = $j == 0 ? ReportAdvancedFilterInterface::OPERATOR_AND : null;
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
					$this->assertEquals ($reportId, $filter->getReportId (), 'Report IDs do not match');
					$index++;
				}
			}

			$duplicatedObject = $object->duplicate (null, 1);
			$this->assertEquals (null, $duplicatedObject->getId (), 'Report IDs do not match');
			$this->assertEquals ($moduleName, $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ($reportName, $duplicatedObject->getName (), 'Report names do not match');
			$this->assertEquals (1, $duplicatedObject->getOwner (), 'Owner IDs do not match');

			$columns = $duplicatedObject->getColumns ();
			$this->assertCount (3, $columns, 'Report columns count do not match');
			foreach ($columns as $index => $column) {
				$this->assertEquals ($fields [ $index ]->getColumnName (), $column->getColumnName (), 'Column names do not match');
				$this->assertEquals ($fields [ $index ]->getDataType (), $column->getDataType (), 'Data types do not match');
				$this->assertEquals ($fields [ $index ]->getName (), $column->getFieldName (), 'Field names do not match');
				$this->assertEquals ($fields [ $index ]->getLabel (), $column->getLabel (), 'Labels do not match');
				$this->assertEquals ($fields [ $index ]->getModuleName (), $column->getModuleName (), 'Module names do not match');
				$this->assertEquals ($fields [ $index ]->getTableName (), $column->getTableName (), 'Table names do not match');
				$this->assertEquals ($index, $column->getSequence (), 'Sequences do not match');
				$this->assertEquals (null, $column->getReportId (), 'Report IDs do not match');
			}

			$standardFilter = $duplicatedObject->getStandardFilter ();
			$this->assertNotNull ($standardFilter, 'Standard filter should not be null');
			$this->assertEquals ($field->getColumnName (), $standardFilter->getColumnName (), 'Column names do not match');
			$this->assertEquals (date_create ('2017-12-31'), $standardFilter->getEndDate (), 'End dates do not match');
			$this->assertEquals ($field->getName (), $standardFilter->getFieldName (), 'Field names do not match');
			$this->assertEquals ($field->getLabel (), $standardFilter->getLabel (), 'Labels do not match');
			$this->assertEquals ($field->getModuleName (), $standardFilter->getModuleName (), 'Module names do not match');
			$this->assertEquals (ReportStandardFilterInterface::PERIOD_CUSTOM, $standardFilter->getPeriod (), 'Periods do not match');
			$this->assertEquals (date_create ('2017-12-01'), $standardFilter->getStartDate (), 'Start dates do not match');
			$this->assertEquals ($field->getTableName (), $standardFilter->getTableName (), 'Table names do not match');
			$this->assertEquals (null, $standardFilter->getReportId (), 'Report IDs do not match');

			$index                = 0;
			$advancedFilterGroups = $duplicatedObject->getAdvancedFilterGroups ();
			$this->assertCount (3, $columns, 'Advanced filter groups count do not match');
			foreach ($advancedFilterGroups as $i => $group) {
				$operator = $i == 0 ? ReportAdvancedFilterInterface::OPERATOR_AND : null;
				$this->assertEquals ($operator, $group->getOperator (), 'Group operators do not match');
				$this->assertEquals ($i, $group->getSequence (), 'Group sequences do not match');
				$this->assertEquals (null, $group->getReportId (), 'Group Report IDs do not match');

				$filters = $group->getFilters ();
				$this->assertCount (2, $filters, 'Group filters count do not match');
				foreach ($filters as $j => $filter) {
					$filterField = $fields [ $index ];
					$comparator  = $i == 0 ? ReportAdvancedFilterInterface::COMPARATOR_CONTAINS : ReportAdvancedFilterInterface::COMPARATOR_NOT_EQUALS;
					$operator    = $j == 0 ? ReportAdvancedFilterInterface::OPERATOR_AND : null;
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
					$this->assertEquals (null, $filter->getReportId (), 'Report IDs do not match');
					$index++;
				}
			}
		}

	}
	// @codingStandardsIgnoreEnd

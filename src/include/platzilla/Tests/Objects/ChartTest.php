<?php
	require_once ('include/platzilla/Objects/Chart.php');

	/**
	 * Prueba unitaria de la clase Chart
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ChartTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object    = Chart::getInstance ();
			$testClass = get_class ($object);
			/** @noinspection SqlResolve */
			$testProperties = [
				'id'             => 8,
				'advanced'       => array (true, false),
				'applicationCodes' => array (array ('application_one', 'application_two', 'application_three')),
				'compare'        => array (true, false),
				'dateGrouping'   => array (ChartInterface::DATE_GROUPING_ANNUAL, ChartInterface::DATE_GROUPING_BIANNUAL, ChartInterface::DATE_GROUPING_DAILY, ChartInterface::DATE_GROUPING_MONTHLY, ChartInterface::DATE_GROUPING_QUARTERLY, ChartInterface::DATE_GROUPING_WEEKLY),
				'fieldName'      => 'my_field',
				'groupBy'        => 'my_test_field',
				'moduleName'     => 'my_module_name',
				'operation'      => array (ChartInterface::OPERATION_AVERAGE, ChartInterface::OPERATION_COUNT, ChartInterface::OPERATION_SUM),
				'sqlQuery'       => 'SELECT * FROM vtiger_my_test_module',
				'title'          => 'My test chart',
				'type'           => array (ChartInterface::TYPE_BARS, ChartInterface::TYPE_DONUT, ChartInterface::TYPE_FUNNEL, ChartInterface::TYPE_PIE, ChartInterface::TYPE_POINTS),
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

		public function testDateGroupingGetterAndSetter () {
			$object = Chart::getInstance ()
				->setDateGrouping (999);
			$this->assertNull ($object->getDateGrouping (), 'Date grouping should be null');
		}

		public function testOperationGroupingGetterAndSetter () {
			$object = Chart::getInstance ()
				->setOperation (999);
			$this->assertNull ($object->getOperation (), 'Operation should be null');
		}

		public function testTypeGetterAndSetter () {
			$object = Chart::getInstance ()
				->setType ('unknown_type');
			$this->assertNull ($object->getType (), 'Type should be null');
		}

		public function testEmptyFieldNameValidation () {
			$object = Chart::getInstance ();
			$this->expectException (ChartException::class);
			$this->expectExceptionMessage (ChartException::ERROR_CHART_EMPTY_FIELD_NAME);
			$object->validate ();
		}

		public function testEmptyOperationValidation () {
			$object = Chart::getInstance ()
				->setFieldName ('my_field')
				->setModuleName ('my_test_module');
			$this->expectException (ChartException::class);
			$this->expectExceptionMessage (ChartException::ERROR_CHART_EMPTY_OPERATION);
			$object->validate ();
		}

		public function testEmptyTitleValidation () {
			$object = Chart::getInstance ()
				->setFieldName ('my_field')
				->setModuleName ('my_test_module')
				->setOperation (ChartInterface::OPERATION_COUNT);
			$this->expectException (ChartException::class);
			$this->expectExceptionMessage (ChartException::ERROR_CHART_EMPTY_TITLE);
			$object->validate ();
		}

		public function testEmptyTypeValidation () {
			$object = Chart::getInstance ()
				->setFieldName ('my_field')
				->setModuleName ('my_test_module')
				->setOperation (ChartInterface::OPERATION_COUNT)
				->setTitle ('My test chart');
			$this->expectException (ChartException::class);
			$this->expectExceptionMessage (ChartException::ERROR_CHART_EMPTY_TYPE);
			$object->validate ();
		}

		public function testEmptyGroupByValidation () {
			$object = Chart::getInstance ()
				->setFieldName ('my_field')
				->setModuleName ('my_test_module')
				->setOperation (ChartInterface::OPERATION_AVERAGE)
				->setTitle ('My test chart')
				->setType (ChartInterface::TYPE_BARS);
			$this->expectException (ChartException::class);
			$this->expectExceptionMessage (ChartException::ERROR_CHART_EMPTY_GROUP_BY);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = Chart::getInstance ()
				->setFieldName ('my_field')
				->setModuleName ('my_test_module')
				->setOperation (ChartInterface::OPERATION_COUNT)
				->setTitle ('My test chart')
				->setType (ChartInterface::TYPE_BARS);
			$object->validate ();
		}

		public function testDuplicate () {
			$object = Chart::getInstance ()
				->setId (39115)
				->setAdvanced (ChartInterface::ADVANCED_NO)
				->setApplicationCodes (array ('application_one', 'application_two', 'application_three'))
				->setCompare (true)
				->setDateGrouping (ChartInterface::DATE_GROUPING_MONTHLY)
				->setFieldName ('my_field')
				->setGroupBy ('my_group_by')
				->setModuleName ('my_test_module')
				->setOperation (ChartInterface::OPERATION_COUNT)
				->setSqlQuery ('SELECT something')
				->setTitle ('My test chart')
				->setType (ChartInterface::TYPE_BARS)
				->setVariables (json_encode (array ('a' => 1, 'b' => 2)));

			$duplicatedObject = $object->duplicate ($object->getId ());
			$this->assertEquals (39115, $duplicatedObject->getId (), 'IDs do not match');
			$this->assertEquals (ChartInterface::ADVANCED_NO, $duplicatedObject->getAdvanced (), 'Advanced properties do not match');
			$this->assertEquals (array ('application_one', 'application_two', 'application_three'), $duplicatedObject->getApplicationCodes (), 'Application codes do not match');
			$this->assertEquals (true, $duplicatedObject->getCompare (), 'Compare properties do not match');
			$this->assertEquals (ChartInterface::DATE_GROUPING_MONTHLY, $duplicatedObject->getDateGrouping (), 'Date grouping properties do not match');
			$this->assertEquals ('my_field', $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ('my_group_by', $duplicatedObject->getGroupBy (), 'Group by properties do not match');
			$this->assertEquals ('my_test_module', $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals (ChartInterface::OPERATION_COUNT, $duplicatedObject->getOperation (), 'Operations do not match');
			$this->assertEquals ('SELECT something', $duplicatedObject->getSqlQuery (), 'SQL queries do not match');
			$this->assertEquals ('My test chart', $duplicatedObject->getTitle (), 'Titles do not match');
			$this->assertEquals (ChartInterface::TYPE_BARS, $duplicatedObject->getType (), 'Types do not match');
			$this->assertEquals (json_encode (array ('a' => 1, 'b' => 2)), $duplicatedObject->getVariables (), 'Variables do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals (null, $duplicatedObject->getId (), 'IDs do not match');
			$this->assertEquals (ChartInterface::ADVANCED_NO, $duplicatedObject->getAdvanced (), 'Advanced properties do not match');
			$this->assertEquals (array ('application_one', 'application_two', 'application_three'), $duplicatedObject->getApplicationCodes (), 'Application codes do not match');
			$this->assertEquals (true, $duplicatedObject->getCompare (), 'Compare properties do not match');
			$this->assertEquals (ChartInterface::DATE_GROUPING_MONTHLY, $duplicatedObject->getDateGrouping (), 'Date grouping properties do not match');
			$this->assertEquals ('my_field', $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ('my_group_by', $duplicatedObject->getGroupBy (), 'Group by properties do not match');
			$this->assertEquals ('my_test_module', $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals (ChartInterface::OPERATION_COUNT, $duplicatedObject->getOperation (), 'Operations do not match');
			$this->assertEquals ('SELECT something', $duplicatedObject->getSqlQuery (), 'SQL queries do not match');
			$this->assertEquals ('My test chart', $duplicatedObject->getTitle (), 'Titles do not match');
			$this->assertEquals (ChartInterface::TYPE_BARS, $duplicatedObject->getType (), 'Types do not match');
			$this->assertEquals (json_encode (array ('a' => 1, 'b' => 2)), $duplicatedObject->getVariables (), 'Variables do not match');
		}

	}
	// @codingStandardsIgnoreEnd

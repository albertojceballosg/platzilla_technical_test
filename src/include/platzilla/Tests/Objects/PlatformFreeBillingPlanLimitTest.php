<?php
	require_once ('include/platzilla/Objects/PlatformFreeBillingPlanLimit.php');

	/**
	 * Prueba unitaria de la clase PlatformFreeBillingPlanLimit
	 *
	 * @codingStandardsIgnoreStart
	 * @group Payments
	 * @SuppressWarnings(PHPMD)
	 */
	class PlatformFreeBillingPlanLimitTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSettersWithInvalidValues () {
			$object         = PlatformFreeBillingPlanLimit::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'maxRecords'  => array (null, '', 'notanumber', true, -2, 1.5, array (1.25), new stdClass ()),
				'moduleLabel' => array (array ('My label'), new stdClass ()),
				'moduleName'  => array (array ('My name'), new stdClass ()),
			];

			foreach ($testProperties as $propertyName => $propertyValues) {
				$propertyName = ucfirst ($propertyName);
				$getter       = "get{$propertyName}";
				$setter       = "set{$propertyName}";

				foreach ($propertyValues as $propertyValue) {
					$result = $object->{$setter} ($propertyValue);
					$this->assertNotNull ($result, "Method {$setter} does not return an instance of {$testClass}. Got null");
					$this->assertTrue (is_object ($result), "Method {$setter} does not return an object");
					$this->assertInstanceOf ($testClass, $result, "Method {$setter} does not return an instance of {$testClass}. Got " . get_class ($result));
					$value = $object->{$getter} ();
					$this->assertNull ($value, "Method {$getter} should return null. Got {$value}");
				}
			}
		}

		public function testGettersAndSettersWithValidValues () {
			$object         = PlatformFreeBillingPlanLimit::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'maxRecords'  => array (-1, 0, 1),
				'moduleLabel' => 'My label',
				'moduleName'  => 'My name',
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

		public function testEmptyMaxRecordsValidation () {
			$object = PlatformFreeBillingPlanLimit::getInstance ();
			$this->expectException (PlatformFreeBillingPlanLimitException::class);
			$this->expectExceptionMessage (PlatformFreeBillingPlanLimitException::ERROR_FREE_BILLING_PLAN_LIMIT_EMPTY_MAX_RECORDS);
			$object->validate ();
		}

		public function testEmptyModuleNameValidation () {
			$object = PlatformFreeBillingPlanLimit::getInstance ()
				->setMaxRecords (-1);
			$this->expectException (PlatformFreeBillingPlanLimitException::class);
			$this->expectExceptionMessage (PlatformFreeBillingPlanLimitException::ERROR_FREE_BILLING_PLAN_LIMIT_EMPTY_MODULE_NAME);
			$object->validate ();
		}

		public function testValidationSucceeded () {
			$object = PlatformFreeBillingPlanLimit::getInstance ()
				->setMaxRecords (-1)
				->setModuleName ('my_module');
			$object->validate ();
		}

	}
	// @codingStandardsIgnoreEnd

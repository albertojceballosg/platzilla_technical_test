<?php
	require_once ('include/platzilla/Objects/ApplicationSubscription.php');

	/**
	 * Prueba unitaria de la clase ApplicationSubscription
	 *
	 * @codingStandardsIgnoreStart
	 * @group Payments
	 * @SuppressWarnings(PHPMD)
	 */
	class ApplicationSubscriptionTest extends PHPUnit_Framework_TestCase {
		private function checkDateTimeGettersAndSetters ($object, $getter, $setter) {
			$testClass     = get_class ($object);
			$invalidValues = array (null, '', 'invaliddate', 1, true, array (), new stdClass ());
			foreach ($invalidValues as $invalidValue) {
				$result = $object->{$setter} ($invalidValue);
				$this->assertNotNull ($result, "Method {$setter} does not return an instance of {$testClass}. Got null");
				$this->assertTrue (is_object ($result), "Method {$setter} does not return an object");
				$this->assertInstanceOf ($testClass, $result, "Method {$setter} does not return an instance of {$testClass}. Got " . get_class ($result));
				$value = $object->{$getter} ();
				$this->assertNull ($value, "Method {$getter} should return null. Got {$value}");
			}

			$dueDates = array ('2018-05-31', date_create ('2018-05-31'));
			foreach ($dueDates as $dueDate) {
				$result = $object->{$setter} ($dueDate);
				$this->assertNotNull ($result, "Method {$setter} does not return an instance of {$testClass}. Got null");
				$this->assertTrue (is_object ($result), "Method {$setter} does not return an object");
				$this->assertInstanceOf ($testClass, $result, "Method {$setter} does not return an instance of {$testClass}. Got " . get_class ($result));
				$value = $object->{$getter} ();
				$this->assertTrue (is_object ($value), "Method {$getter} does not return an object");
				$this->assertInstanceOf (DateTime::class, $value, "Method {$getter} does not return an instance of " . DateTime::class . '. Got ' . get_class ($value));
			}
		}

		public function testGettersAndSettersWithInvalidValues () {
			$object         = ApplicationSubscription::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'applicationCode'        => array (array ('My application code'), new stdClass ()),
				'applicationDescription' => array (array ('My application description'), new stdClass ()),
				'applicationName'        => array (array ('My application name'), new stdClass ()),
				'instanceCode'           => array (array ('My application name'), new stdClass ()),
				'status'                 => array ('notastatus', 12.65, true, array (ApplicationSubscription::STATUS_ACTIVE), new stdClass ()),
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
					$this->assertNull ($value, "Method {$setter} should return null. Got {$value}");
				}
			}
		}

		public function testGettersAndSettersWithValidValues () {
			$object         = ApplicationSubscription::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'applicationCode'        => 'crm',
				'applicationDescription' => 'CRM profesional',
				'applicationName'        => 'CRM',
				'instanceCode'           => 'My instance code',
				'status'                 => ApplicationSubscription::STATUS_ACTIVE,
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

		public function testRegistrationDateGetterAndSetter () {
			$object        = ApplicationSubscription::getInstance ();
			$this->checkDateTimeGettersAndSetters ($object, 'getRegistrationDate', 'setRegistrationDate');
		}

		public function testServiceEndDateGetterAndSetter () {
			$object = ApplicationSubscription::getInstance ();
			$this->checkDateTimeGettersAndSetters ($object, 'getServiceEndDate', 'setServiceEndDate');
		}

		public function testServiceStartDateGetterAndSetter () {
			$object = ApplicationSubscription::getInstance ();
			$this->checkDateTimeGettersAndSetters ($object, 'getServiceStartDate', 'setServiceStartDate');
		}

		public function testEmptyApplicationCodeValidation () {
			$object = ApplicationSubscription::getInstance ();
			$this->expectException (ApplicationSubscriptionException::class);
			$this->expectExceptionMessage (ApplicationSubscriptionException::ERROR_APPLICATION_SUBSCRIPTION_EMPTY_APPLICATION_CODE);
			$object->validate ();
		}

		public function testEmptyInstanceCodeValidation () {
			$object = ApplicationSubscription::getInstance ()
				->setApplicationCode ('crm');
			$this->expectException (ApplicationSubscriptionException::class);
			$this->expectExceptionMessage (ApplicationSubscriptionException::ERROR_APPLICATION_SUBSCRIPTION_EMPTY_INSTANCE_CODE);
			$object->validate ();
		}

		public function testEmptyRegistrationDateValidation () {
			$object = ApplicationSubscription::getInstance ()
				->setApplicationCode ('crm')
				->setInstanceCode ('appef1');
			$this->expectException (ApplicationSubscriptionException::class);
			$this->expectExceptionMessage (ApplicationSubscriptionException::ERROR_APPLICATION_SUBSCRIPTION_EMPTY_REGISTRATION_DATE);
			$object->validate ();
		}

		public function testEmptyStatusValidation () {
			$object = ApplicationSubscription::getInstance ()
				->setApplicationCode ('crm')
				->setInstanceCode ('appef11')
				->setRegistrationDate (date ('Y-m-d'));
			$this->expectException (ApplicationSubscriptionException::class);
			$this->expectExceptionMessage (ApplicationSubscriptionException::ERROR_APPLICATION_SUBSCRIPTION_EMPTY_STATUS);
			$object->validate ();
		}

		public function testValidationSucceeded () {
			$object = ApplicationSubscription::getInstance ()
				->setApplicationCode ('crm')
				->setInstanceCode ('appef11')
				->setRegistrationDate (date ('Y-m-d'))
				->setStatus (ApplicationSubscription::STATUS_ACTIVE);
			$object->validate ();
		}

	}
	// @codingStandardsIgnoreEnd

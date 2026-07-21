<?php
	require_once ('include/platzilla/Objects/PlatformSubscription.php');

	/**
	 * Prueba unitaria de la clase PlatformSubscription
	 *
	 * @codingStandardsIgnoreStart
	 * @group Payments
	 * @SuppressWarnings(PHPMD)
	 */
	class PlatformSubscriptionTest extends PHPUnit_Framework_TestCase {
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
			$object         = PlatformSubscription::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'accountId'                => array (null, '', 'notanumber', true, -2, 3.14159, array (1.25), new stdClass ()),
				'applicationSubscriptions' => array (array (), new stdClass ()),
				'billingPlan'              => array (null, '', 1, true, 'My billing plan', array ('My application description'), new stdClass ()),
				'customer'                 => array (null, '', 'My billing plan', true, 1, -3.14159, array ('My application description'), new stdClass ()),
				'instanceCode'             => array (array ('My instance code'), new stdClass ()),
				'pricebookId'              => array (null, '', 'notanumber', true, -2, 3.14159, array (1.25), new stdClass ()),
				'totalActiveUsers'         => array (null, '', 'notanumber', true, -1, 3.14159, array (1.25), new stdClass ()),
				'totalDiskSpace'           => array (null, '', 'notanumber', true, -1, array (1.25), new stdClass ()),
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
			$object         = PlatformSubscription::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'accountId'                => 55,
				'applicationSubscriptions' => array (array (ApplicationSubscription::getInstance ()->setApplicationCode ('crm')->setInstanceCode ('appef1')->setRegistrationDate (date ('Y-m-d'))->setStatus (ApplicationSubscription::STATUS_ACTIVE))),
				'billingPlan'              => PlatformBillingPlan::getInstance ()->setId (1)->setArticleId (15)->setDescription ('My billing plan')->setListPrice (49.99)->setName ('XXL')->setStatus (PlatformBillingPlan::STATUS_ACTIVE)->setTaxId (1)->setTaxPercentage (21.00)->setTotalApplications (-1)->setTotalDiskSpace (-1)->setTotalUsers (-1),
				'instanceCode'             => 'appef1',
				'pricebookId'              => 5,
				'totalActiveUsers'         => array (0, 1),
				'totalDiskSpace'         => array (0, 0.01, 3.14159),
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
			$object = PlatformSubscription::getInstance ();
			$this->checkDateTimeGettersAndSetters ($object, 'getRegistrationDate', 'setRegistrationDate');
		}

		public function testServiceEndDateGetterAndSetter () {
			$object = PlatformSubscription::getInstance ();
			$this->checkDateTimeGettersAndSetters ($object, 'getServiceEndDate', 'setServiceEndDate');
		}

		public function testServiceStartDateGetterAndSetter () {
			$object = PlatformSubscription::getInstance ();
			$this->checkDateTimeGettersAndSetters ($object, 'getServiceStartDate', 'setServiceStartDate');
		}

		public function testEmptyAccountIdValidation () {
			$object = PlatformSubscription::getInstance ();
			$this->expectException (PlatformSubscriptionException::class);
			$this->expectExceptionMessage (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_ACCOUNT_ID);
			$object->validate ();
		}

		public function testEmptyInstanceCodeValidation () {
			$object = PlatformSubscription::getInstance ()
				->setAccountId (5);
			$this->expectException (PlatformSubscriptionException::class);
			$this->expectExceptionMessage (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_INSTANCE_CODE);
			$object->validate ();
		}

		public function testEmptyRegistrationDateValidation () {
			$object = PlatformSubscription::getInstance ()
				->setAccountId (5)
				->setInstanceCode ('appef1')
				->setPricebookId (53);
			$this->expectException (PlatformSubscriptionException::class);
			$this->expectExceptionMessage (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_REGISTRATION_DATE);
			$object->validate ();
		}

		public function testEmptyTotalActiveUsersValidation () {
			$object = PlatformSubscription::getInstance ()
				->setAccountId (5)
				->setInstanceCode ('appef1')
				->setPricebookId (53)
				->setRegistrationDate (date ('Y-m-d'));
			$this->expectException (PlatformSubscriptionException::class);
			$this->expectExceptionMessage (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_TOTAL_ACTIVE_USERS);
			$object->validate ();
		}

		public function testEmptyTotalDiskSpaceValidation () {
			$object = PlatformSubscription::getInstance ()
				->setAccountId (5)
				->setInstanceCode ('appef1')
				->setPricebookId (53)
				->setRegistrationDate (date ('Y-m-d'))
				->setTotalActiveUsers (4);
			$this->expectException (PlatformSubscriptionException::class);
			$this->expectExceptionMessage (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_EMPTY_TOTAL_DISK_SPACE);
			$object->validate ();
		}

		public function testInvalidBillingPlanValidation () {
			$object = PlatformSubscription::getInstance ()
				->setAccountId (5)
				->setInstanceCode ('appef1')
				->setPricebookId (53)
				->setRegistrationDate (date ('Y-m-d'))
				->setTotalActiveUsers (0)
				->setTotalDiskSpace (0.01)
				->setBillingPlan (PlatformBillingPlan::getInstance ());
			$this->expectException (PlatformBillingPlanException::class);
			$object->validate ();
		}

		public function testInvalidApplicationSubscriptionsValidation () {
			$object = PlatformSubscription::getInstance ()
				->setAccountId (5)
				->setInstanceCode ('appef1')
				->setPricebookId (53)
				->setRegistrationDate (date ('Y-m-d'))
				->setTotalActiveUsers (0)
				->setTotalDiskSpace (0.01)
				->setBillingPlan (PlatformBillingPlan::getInstance ()->setId (1)->setArticleId (15)->setDescription ('My billing plan')->setListPrice (49.99)->setName ('XXL')->setStatus (PlatformBillingPlan::STATUS_ACTIVE)->setTaxId (1)->setTaxPercentage (21.00)->setTotalApplications (-1)->setTotalDiskSpace (-1)->setTotalUsers (-1))
				->setApplicationSubscriptions (array (new stdClass ()));
			$this->expectException (PlatformSubscriptionException::class);
			$this->expectExceptionMessage (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_INVALID_APPLICATION_SUBSCRIPTION);
			$object->validate ();
		}

		public function testValidationSucceeded () {
			$object = PlatformSubscription::getInstance ()
				->setAccountId (5)
				->setInstanceCode ('appef1')
				->setPricebookId (53)
				->setRegistrationDate (date ('Y-m-d'))
				->setTotalActiveUsers (0)
				->setTotalDiskSpace (0.01)
				->setBillingPlan (PlatformBillingPlan::getInstance ()->setId (1)->setArticleId (15)->setDescription ('My billing plan')->setListPrice (49.99)->setName ('XXL')->setStatus (PlatformBillingPlan::STATUS_ACTIVE)->setTaxId (1)->setTaxPercentage (21.00)->setTotalApplications (-1)->setTotalDiskSpace (-1)->setTotalUsers (-1))
				->setApplicationSubscriptions (array (array (ApplicationSubscription::getInstance ()->setApplicationCode ('crm')->setInstanceCode ('appef1')->setRegistrationDate (date ('Y-m-d'))->setStatus (ApplicationSubscription::STATUS_ACTIVE))));
			$this->expectException (PlatformSubscriptionException::class);
			$this->expectExceptionMessage (PlatformSubscriptionException::ERROR_PLATFORM_SUBSCRIPTION_INVALID_APPLICATION_SUBSCRIPTION);
			$object->validate ();
		}

	}
	// @codingStandardsIgnoreEnd

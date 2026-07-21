<?php
	require_once ('include/platzilla/Objects/PlatformBillingPlan.php');

	/**
	 * Prueba unitaria de la clase PlatformBillingPlan
	 *
	 * @codingStandardsIgnoreStart
	 * @group Payments
	 * @SuppressWarnings(PHPMD)
	 */
	class PlatformBillingPlanTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSettersWithInvalidValues () {
			$object         = PlatformBillingPlan::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'                => array (null, '', 'notanumber', true, -1, array (1.25), new stdClass ()),
				'articleId'         => array (null, '', 'notanumber', true, -1, 1.5, array (1.25), new stdClass ()),
				'description'       => array (array ('My description'), new stdClass ()),
				'listPrice'         => array (null, '', 'notanumber', true, -1, array (1.25), new stdClass ()),
				'name'              => array (array ('My name'), new stdClass ()),
				'status'            => array ('notastatus', 12.65, true, array (PlatformBillingPlan::STATUS_ACTIVE), new stdClass ()),
				'taxId'             => array (null, '', 'notanumber', true, -1.5, array (1.25), new stdClass ()),
				'taxPercentage'     => array (null, '', 'notanumber', true, -1.5, array (1.25), new stdClass ()),
				'totalApplications' => array (null, '', 'notanumber', true, 1.5, array (1.25), new stdClass ()),
				'totalDiskSpace'    => array (null, '', 'notanumber', true, -1.05, array (1.25), new stdClass ()),
				'totalUsers'        => array (null, '', 'notanumber', true, 1.5, array (1.25), new stdClass ()),
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
			$object         = PlatformBillingPlan::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'                => 4,
				'articleId'         => 15,
				'description'       => 'My description',
				'listPrice'         => 9.99,
				'name'              => 'XXL',
				'status'            => PlatformBillingPlan::STATUS_ACTIVE,
				'taxId'             => 1,
				'taxPercentage'     => 12.0,
				'totalApplications' => 2,
				'totalUsers'        => 5,
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

		public function testEmptyArticleIdValidation () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6);
			$this->expectException (PlatformBillingPlanException::class);
			$this->expectExceptionMessage (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_PRODUCT_ID);
			$object->validate ();
		}

		public function testEmptyDescriptionValidation () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6)
				->setProductId (16);
			$this->expectException (PlatformBillingPlanException::class);
			$this->expectExceptionMessage (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_DESCRIPTION);
			$object->validate ();
		}

		public function testEmptyListPriceValidation () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6)
				->setProductId (16)
				->setDescription ('My plan description');
			$this->expectException (PlatformBillingPlanException::class);
			$this->expectExceptionMessage (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_LIST_PRICE);
			$object->validate ();
		}

		public function testEmptyNameValidation () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6)
				->setProductId (16)
				->setDescription ('My plan description')
				->setListPrice (9.99);
			$this->expectException (PlatformBillingPlanException::class);
			$this->expectExceptionMessage (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_NAME);
			$object->validate ();
		}

		public function testEmptyStatusValidation () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6)
				->setProductId (16)
				->setDescription ('My plan description')
				->setListPrice (9.99)
				->setName ('My plan');
			$this->expectException (PlatformBillingPlanException::class);
			$this->expectExceptionMessage (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_STATUS);
			$object->validate ();
		}

		public function testEmptyTaxIdValidation () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6)
				->setProductId (16)
				->setDescription ('My plan description')
				->setListPrice (9.99)
				->setName ('My plan')
				->setStatus (PlatformBillingPlan::STATUS_ACTIVE);
			$this->expectException (PlatformBillingPlanException::class);
			$this->expectExceptionMessage (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_TAX_ID);
			$object->validate ();
		}

		public function testEmptyTaxPercentageValidation () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6)
				->setProductId (16)
				->setDescription ('My plan description')
				->setListPrice (9.99)
				->setName ('My plan')
				->setStatus (PlatformBillingPlan::STATUS_ACTIVE)
				->setTaxId (1);
			$this->expectException (PlatformBillingPlanException::class);
			$this->expectExceptionMessage (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_TAX_PERCENTAGE);
			$object->validate ();
		}

		public function testEmptyTotalApplicationsValidation () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6)
				->setProductId (16)
				->setDescription ('My plan description')
				->setListPrice (9.99)
				->setName ('My plan')
				->setStatus (PlatformBillingPlan::STATUS_ACTIVE)
				->setTaxId (1)
				->setTaxPercentage (21);
			$this->expectException (PlatformBillingPlanException::class);
			$this->expectExceptionMessage (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_TOTAL_APPLICATIONS);
			$object->validate ();
		}

		public function testEmptyTotalDiskSpaceValidation () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6)
				->setProductId (16)
				->setDescription ('My plan description')
				->setListPrice (9.99)
				->setName ('My plan')
				->setStatus (PlatformBillingPlan::STATUS_ACTIVE)
				->setTaxId (1)
				->setTaxPercentage (21)
				->setTotalApplications (-1);
			$this->expectException (PlatformBillingPlanException::class);
			$this->expectExceptionMessage (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_TOTAL_DISK_SPACE);
			$object->validate ();
		}

		public function testEmptyTotalUsersValidation () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6)
				->setProductId (16)
				->setDescription ('My plan description')
				->setListPrice (9.99)
				->setName ('My plan')
				->setStatus (PlatformBillingPlan::STATUS_ACTIVE)
				->setTaxId (1)
				->setTaxPercentage (21)
				->setTotalApplications (-1)
				->setTotalDiskSpace (-1);
			$this->expectException (PlatformBillingPlanException::class);
			$this->expectExceptionMessage (PlatformBillingPlanException::ERROR_BILLING_PLAN_EMPTY_TOTAL_USERS);
			$object->validate ();
		}

		public function testValidationSucceeded () {
			$object = PlatformBillingPlan::getInstance ()
				->setId (6)
				->setProductId (16)
				->setDescription ('My plan description')
				->setListPrice (9.99)
				->setName ('My plan')
				->setStatus (PlatformBillingPlan::STATUS_ACTIVE)
				->setTaxId (1)
				->setTaxPercentage (21)
				->setTotalApplications (-1)
				->setTotalDiskSpace (-1)
				->setTotalUsers (-1);
			$object->validate ();
		}

	}
	// @codingStandardsIgnoreEnd

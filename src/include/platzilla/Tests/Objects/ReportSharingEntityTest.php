<?php
	require_once ('include/platzilla/Objects/ReportSharingEntity.php');

	/**
	 * Prueba unitaria de la clase ReportSharingEntity
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ReportSharingEntityTest extends PHPUnit_Framework_TestCase {

		public function testGettersAndSetters () {
			$object         = ReportSharingEntity::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'   => 41,
				'type' => array (ReportSharingEntityInterface::TYPE_GROUP, ReportSharingEntityInterface::TYPE_USER),
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

		public function testEmptyIdValidation () {
			$object = ReportSharingEntity::getInstance ();
			$this->expectException (ReportSharingEntityException::class);
			$this->expectExceptionMessage (ReportSharingEntityException::ERROR_REPORT_SHARING_ENTITY_EMPTY_ID);
			$object->validate ();
		}

		public function testEmptyTypeValidation () {
			$object = ReportSharingEntity::getInstance ()
				->setId (10);
			$this->expectException (ReportSharingEntityException::class);
			$this->expectExceptionMessage (ReportSharingEntityException::ERROR_REPORT_SHARING_ENTITY_EMPTY_TYPE);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = ReportSharingEntity::getInstance ()
				->setId (10)
				->setType (ReportSharingEntityInterface::TYPE_GROUP);
			$object->validate ();
		}

		public function testDuplicate () {
			$object           = ReportSharingEntity::getInstance ()
				->setId (10)
				->setType (ReportSharingEntityInterface::TYPE_GROUP);
			$duplicatedObject = $object->duplicate (20);
			$this->assertEquals (ReportSharingEntityInterface::TYPE_GROUP, $duplicatedObject->getType (), 'Types do not match');
			$this->assertEquals (20, $duplicatedObject->getId (), 'IDs do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals (ReportSharingEntityInterface::TYPE_GROUP, $duplicatedObject->getType (), 'Types do not match');
			$this->assertEquals (null, $duplicatedObject->getId (), 'IDs do not match');
		}

		public function testCopyValuesFromDailySchedule () {
			$object = ReportSharingEntity::getInstance ()
				->setId (10)
				->setType (ReportSharingEntityInterface::TYPE_GROUP);

			$objectCopy = ReportSharingEntity::getInstance ();
			$objectCopy->copyValuesFrom ($object);
			$this->assertEquals (ReportSharingEntityInterface::TYPE_GROUP, $objectCopy->getType (), 'Types do not match');
			$this->assertEquals (10, $objectCopy->getId (), 'IDs do not match');
		}

		public function testIsEqualTo () {
			$object        = ReportSharingEntity::getInstance ()
				->setId (10)
				->setType (ReportSharingEntityInterface::TYPE_GROUP);
			$anotherObject = ReportSharingEntity::getInstance ()
				->setId (10)
				->setType (ReportSharingEntityInterface::TYPE_GROUP);
			$this->assertTrue ($object->isEqualTo ($object), 'Objects should be equal');
			$this->assertTrue ($anotherObject->isEqualTo ($anotherObject), 'Objects should be equal');
			$this->assertTrue ($object->isEqualTo ($anotherObject), 'Objects should be equal');

			// El método copyValuesFrom debería dar un objeto igual
			$aThirdObject = ReportSharingEntity::getInstance ();
			$aThirdObject->copyValuesFrom ($object);
			$this->assertTrue ($aThirdObject->isEqualTo ($aThirdObject), 'Objects should be equal');
			$this->assertTrue ($aThirdObject->isEqualTo ($object), 'Objects should be equal');
		}

	}
	// @codingStandardsIgnoreEnd

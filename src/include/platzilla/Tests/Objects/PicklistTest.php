<?php
	require_once ('include/platzilla/Exceptions/PicklistException.php');
	require_once ('include/platzilla/Objects/Picklist.php');

	/**
	 * Prueba unitaria de la clase Picklist
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class PicklistTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = Picklist::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'     => 100,
				'name'   => 'My picklist',
				'values' => array ('My first picklist value', 'My second picklist value', 'My third picklist value'),
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

		public function testEmptyNameValidation () {
			$object = Picklist::getInstance ();
			$this->expectException (PicklistException::class);
			$this->expectExceptionMessage (PicklistException::ERROR_PICKLIST_EMPTY_NAME);
			$object->validate ();
		}

		public function testEmptyValuesValidation () {
			$object = Picklist::getInstance ()
				->setName ('My picklist');
			$this->expectException (PicklistException::class);
			$this->expectExceptionMessage (PicklistException::ERROR_PICKLIST_EMPTY_VALUES);
			$object->validate ();

			$object->setValues (array ());
			$this->expectException (PicklistException::class);
			$this->expectExceptionMessage (PicklistException::ERROR_PICKLIST_EMPTY_VALUES);
			$object->validate ();
		}

		public function testInvalidValuesValidation () {
			$object = Picklist::getInstance ()
				->setName ('My picklist')
				->setValues (array ('My first picklist value', 'My second picklist value', 'My third picklist value'));
			$this->expectException (PicklistException::class);
			$this->expectExceptionMessage (PicklistException::ERROR_PICKLIST_INVALID_VALUE);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = Picklist::getInstance ()
				->setName ('My picklist')
				->setValues (array (
					PicklistValue::getInstance ()->setValue ('My first picklist value'),
					PicklistValue::getInstance ()->setValue ('My second picklist value'),
					PicklistValue::getInstance ()->setValue ('My third picklist value'),
				));
			$object->validate ();
		}

		public function testDuplicate () {
			$object = PicklistValue::getInstance ()
				->setId (150)
				->setPresence (PicklistValueInterface::PRESENCE_HIDDEN)
				->setValue ('My picklist value');

			$duplicatedObject = $object->duplicate ($object->getId ());
			$this->assertEquals (150, $duplicatedObject->getId (), 'IDs do not match');
			$this->assertEquals (PicklistValueInterface::PRESENCE_HIDDEN, $duplicatedObject->getPresence (), 'Presences do not match');
			$this->assertEquals ('My picklist value', $duplicatedObject->getValue (), 'Values do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals (null, $duplicatedObject->getId (), 'IDs do not match');
			$this->assertEquals (PicklistValueInterface::PRESENCE_HIDDEN, $duplicatedObject->getPresence (), 'Presences do not match');
			$this->assertEquals ('My picklist value', $duplicatedObject->getValue (), 'Values do not match');
		}

	}
	// @codingStandardsIgnoreEnd

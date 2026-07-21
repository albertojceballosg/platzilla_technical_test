<?php
	require_once ('include/platzilla/Exceptions/PicklistValueException.php');
	require_once ('include/platzilla/Objects/PicklistValue.php');

	/**
	 * Prueba unitaria de la clase PicklistValue
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class PicklistValueTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = PicklistValue::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'       => 100,
				'presence' => PicklistValue::PRESENCE_VISIBLE,
				'value'    => 'My picklist value',
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

		public function testInvalidPresence () {
			$object = PicklistValue::getInstance ()
				->setPresence (-1);
			$this->assertEquals (PicklistValue::PRESENCE_VISIBLE, $object->getPresence (), 'Values do not match');
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

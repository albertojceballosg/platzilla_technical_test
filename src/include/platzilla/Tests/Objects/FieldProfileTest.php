<?php
	require_once ('include/platzilla/Objects/FieldProfile.php');

	/**
	 * Prueba unitaria de la clase FieldProfile
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class FieldProfileTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = FieldProfile::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'fieldName'   => 'my_field',
				'moduleName'  => 'my_module',
				'profileName' => 'administrator',
				'readOnly'    => array (FieldProfileInterface::READ_ONLY, FieldProfileInterface::READ_WRITE),
				'visibility'  => array (FieldProfileInterface::VISIBILITY_HIDDEN, FieldProfileInterface::VISIBILITY_VISIBLE),
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

		public function testEmptyFieldNameValidation () {
			$object = FieldProfile::getInstance ();
			$this->expectException (FieldProfileException::class);
			$this->expectExceptionMessage (FieldProfileException::ERROR_FIELD_PROFILE_EMPTY_FIELD_NAME);
			$object->validate ();
		}

		public function testEmptyModuleNameValidation () {
			$object = FieldProfile::getInstance ()
				->setFieldName ('test_field_name');
			$this->expectException (FieldProfileException::class);
			$this->expectExceptionMessage (FieldProfileException::ERROR_FIELD_PROFILE_EMPTY_MODULE_NAME);
			$object->validate ();
		}

		public function testEmptyProfileNameValidation () {
			$object = FieldProfile::getInstance ()
				->setFieldName ('test_field_name')
				->setModuleName ('test_module_name');
			$this->expectException (FieldProfileException::class);
			$this->expectExceptionMessage (FieldProfileException::ERROR_FIELD_PROFILE_EMPTY_PROFILE_NAME);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = FieldProfile::getInstance ()
				->setFieldName ('test_field_name')
				->setModuleName ('test_module_name')
				->setProfileName ('adiministrator');
			$object->validate ();
		}
	}
	// @codingStandardsIgnoreEnd

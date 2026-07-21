<?php
	require_once ('include/platzilla/Objects/Profile.php');

	/**
	 * Prueba unitaria de la clase Profile
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ProfileTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = Profile::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'                        => 100,
				'description'               => 'Administrator profile',
				'editPermission'            => array (ProfileInterface::PERMISSION_ALLOW, ProfileInterface::PERMISSION_DENY),
				'fieldProfiles'             => array (array (FieldProfile::getInstance (), FieldProfile::getInstance (), FieldProfile::getInstance ())),
				'mainApplicationCode'       => 'my_test_app',
				'moduleProfiles'            => array (array (ModuleProfile::getInstance (), ModuleProfile::getInstance (), ModuleProfile::getInstance ())),
				'name'                      => 'Administrator',
				'secondaryApplicationCodes' => array (array ('another_app', 'a_third_app')),
				'viewPermission'            => array (ProfileInterface::PERMISSION_ALLOW, ProfileInterface::PERMISSION_DENY),
				'viewProfiles'              => array (array (ViewProfile::getInstance (), ViewProfile::getInstance (), ViewProfile::getInstance ())),
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
			$object = Profile::getInstance ();
			$this->expectException (ProfileException::class);
			$this->expectExceptionMessage (ProfileException::ERROR_PROFILE_EMPTY_PROFILE_NAME);
			$object->validate ();
		}

		public function testInvalidEditPermission () {
			$object = Profile::getInstance ()
				->setEditPermission (-999);
			$this->assertEquals (ProfileInterface::PERMISSION_ALLOW, $object->getEditPermission (), 'Edit permissions do not match');
		}

		public function testInvalidViewPermission () {
			$object = Profile::getInstance ()
				->setViewPermission (-999);
			$this->assertEquals (ProfileInterface::PERMISSION_ALLOW, $object->getViewPermission (), 'View permissions do not match');
		}

		public function testValidationSucceed () {
			$object = Profile::getInstance ()
				->setName ('My profile')
				->setDescription ('My profile for all applications');
			$object->validate ();
		}
	}
	// @codingStandardsIgnoreEnd

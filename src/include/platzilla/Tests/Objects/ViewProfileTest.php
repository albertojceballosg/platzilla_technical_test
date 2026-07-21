<?php
	require_once ('include/platzilla/Objects/ViewProfile.php');

	/**
	 * Prueba unitaria de la clase ViewProfile
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ViewProfileTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = ViewProfile::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'accessPermission' => array (ViewProfileInterface::PERMISSION_ALLOW, ViewProfileInterface::PERMISSION_DENY),
				'default'          => array (ViewProfileInterface::DEFAULT_NO, ViewProfileInterface::DEFAULT_YES),
				'moduleName'       => 'my_module',
				'profileName'      => 'administrator',
				'viewName'         => 'My view',
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

		public function testEmptyModuleNameValidation () {
			$object = ViewProfile::getInstance ();
			$this->expectException (ViewProfileException::class);
			$this->expectExceptionMessage (ViewProfileException::ERROR_VIEW_PROFILE_EMPTY_MODULE_NAME);
			$object->validate ();
		}

		public function testEmptyProfileNameValidation () {
			$object = ViewProfile::getInstance ()
				->setModuleName ('test_module_name');
			$this->expectException (ViewProfileException::class);
			$this->expectExceptionMessage (ViewProfileException::ERROR_VIEW_PROFILE_EMPTY_PROFILE_NAME);
			$object->validate ();
		}

		public function testEmptyViewNameValidation () {
			$object = ViewProfile::getInstance ()
				->setModuleName ('test_module_name')
				->setProfileName ('administrator');
			$this->expectException (ViewProfileException::class);
			$this->expectExceptionMessage (ViewProfileException::ERROR_VIEW_PROFILE_EMPTY_VIEW_NAME);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = ViewProfile::getInstance ()
				->setModuleName ('test_module_name')
				->setProfileName ('administrator')
				->setViewName ('All');
			$object->validate ();
		}
	}
	// @codingStandardsIgnoreEnd

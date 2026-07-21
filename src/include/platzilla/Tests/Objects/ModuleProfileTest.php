<?php
	require_once ('include/platzilla/Objects/ModuleProfile.php');

	/**
	 * Prueba unitaria de la clase ModuleProfile
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ModuleProfileTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = ModuleProfile::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'accessPermission'           => array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY),
				'deletePermission'           => array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY),
				'editPermission'             => array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY),
				'exportPermission'           => array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY),
				'handleDuplicatesPermission' => array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY),
				'importPermission'           => array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY),
				'listPermission'             => array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY),
				'mergePermission'            => array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY),
				'moduleName'                 => 'my_module',
				'profileName'                => 'administrator',
				'readPermission'             => array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY),
				'savePermission'             => array (ModuleProfileInterface::PERMISSION_ALLOW, ModuleProfileInterface::PERMISSION_DENY),
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

		public function testInvalidPermissionGettersAndSetters () {
			$object         = ModuleProfile::getInstance ();
			$testProperties = [
				'accessPermission'           => ModuleProfileInterface::PERMISSION_ALLOW,
				'deletePermission'           => ModuleProfileInterface::PERMISSION_ALLOW,
				'editPermission'             => ModuleProfileInterface::PERMISSION_ALLOW,
				'exportPermission'           => ModuleProfileInterface::PERMISSION_ALLOW,
				'handleDuplicatesPermission' => ModuleProfileInterface::PERMISSION_ALLOW,
				'importPermission'           => ModuleProfileInterface::PERMISSION_ALLOW,
				'listPermission'             => ModuleProfileInterface::PERMISSION_ALLOW,
				'mergePermission'            => ModuleProfileInterface::PERMISSION_DENY,
				'readPermission'             => ModuleProfileInterface::PERMISSION_ALLOW,
				'savePermission'             => ModuleProfileInterface::PERMISSION_ALLOW,
			];

			foreach ($testProperties as $propertyName => $expectedValue) {
				$invalidValue = -999;
				$propertyName = ucfirst ($propertyName);
				$getter       = "get{$propertyName}";
				$setter       = "set{$propertyName}";
				$object->{$setter} ($invalidValue);
				$this->assertEquals ($expectedValue, $object->{$getter} (), "{$getter} does not work. Expected {$expectedValue}. Got {$object->{$getter}}");
			}
		}

		public function testEmptyModuleNameValidation () {
			$object = ModuleProfile::getInstance ();
			$this->expectException (ModuleProfileException::class);
			$this->expectExceptionMessage (ModuleProfileException::ERROR_MODULE_PROFILE_EMPTY_MODULE_NAME);
			$object->validate ();
		}

		public function testEmptyProfileNameValidation () {
			$object = ModuleProfile::getInstance ()
				->setModuleName ('test_module_name');
			$this->expectException (ModuleProfileException::class);
			$this->expectExceptionMessage (ModuleProfileException::ERROR_MODULE_PROFILE_EMPTY_PROFILE_NAME);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = ModuleProfile::getInstance ()
				->setModuleName ('test_module_name')
				->setProfileName ('administrator');
			$object->validate ();
		}
	}
	// @codingStandardsIgnoreEnd

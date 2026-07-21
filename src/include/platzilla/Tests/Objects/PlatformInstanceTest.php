<?php
	require_once ('include/platzilla/Objects/PlatformInstance.php');

	/**
	 * Prueba unitaria de la clase PlatformInstance
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class PlatformInstanceTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = PlatformInstance::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'                => 100,
				'accountId'         => 19536,
				'administrator'     => User::getInstance ()->setEmail ('fperez@timemanagement.es')->setFirstName ('Felipe')->setLastName ('Pérez')->setUserName ('fperez@timemanagement.es'),
				'applications'      => array (array (Application::getInstance (), Application::getInstance (), Application::getInstance ())),
				'code'              => 'appef0001',
				'name'              => 'My super super cuper instance',
				'status'            => array (PlatformInstanceInterface::STATUS_UNVERIFIED, PlatformInstanceInterface::STATUS_VERIFIED),
				'totalAllowedUsers' => 1,
				'users'             => array (array ('dpolo@timemanagement.es', 'ebriceno@timemanagement.es', 'avergara@timemanagement.es')),
				'verificationCode'  => 'ABCDEF',
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

		public function testEmptyCodeValidation () {
			$object = PlatformInstance::getInstance ();
			$this->expectException (PlatformInstanceException::class);
			$this->expectExceptionMessage (PlatformInstanceException::ERROR_INSTANCE_EMPTY_CODE);
			$object->validate ();
		}

		public function testEmptyAdministratorValidation () {
			$object = PlatformInstance::getInstance ()
				->setCode ('appef00001')
				->setStatus (PlatformInstanceInterface::STATUS_UNVERIFIED)
				->setAccountId (15245);
			$this->expectException (PlatformInstanceException::class);
			$this->expectExceptionMessage (PlatformInstanceException::ERROR_INSTANCE_EMPTY_ADMINISTRATOR);
			$object->validate ();
		}

		public function testEmptyNameValidation () {
			$object = PlatformInstance::getInstance ()
				->setCode ('appef00001')
				->setStatus (PlatformInstanceInterface::STATUS_UNVERIFIED)
				->setAccountId (15245)
				->setAdministrator (User::getInstance ()->setEmail ('fperez@timemanagement.es')->setFirstName ('Felipe')->setLastName ('Pérez')->setUserName ('fperez@timemanagement.es'));
			$this->expectException (PlatformInstanceException::class);
			$this->expectExceptionMessage (PlatformInstanceException::ERROR_INSTANCE_EMPTY_NAME);
			$object->validate ();
		}

		public function testEmptyModulesValidation () {
			$object = PlatformInstance::getInstance ()
				->setCode ('appef00001')
				->setStatus (PlatformInstanceInterface::STATUS_UNVERIFIED)
				->setAccountId (15245)
				->setAdministrator (User::getInstance ()->setEmail ('fperez@timemanagement.es')->setFirstName ('Felipe')->setLastName ('Pérez')->setUserName ('fperez@timemanagement.es'))
				->setName ('My super duper cuper instance');
			$this->expectException (PlatformInstanceException::class);
			$this->expectExceptionMessage (PlatformInstanceException::ERROR_INSTANCE_EMPTY_MODULES);
			$object->validate ();
		}

		public function testNoUsersValidationSucceed () {
			$object = PlatformInstance::getInstance ()
				->setCode ('appef00001')
				->setStatus (PlatformInstanceInterface::STATUS_UNVERIFIED)
				->setAccountId (15245)
				->setAdministrator (User::getInstance ()->setEmail ('fperez@timemanagement.es')->setFirstName ('Felipe')->setLastName ('Pérez')->setUserName ('fperez@timemanagement.es'))
				->setModules (array (
					Module::getInstance ()->setLabel ('My module')->setName ('my_module')->setPresence (ModuleInterface::PRESENCE_USER_DEFINED)->setType (ModuleInterface::TYPE_TOOL)
				))
				->setName ('My super duper cuper instance');
			$object->validate ();
		}

		public function testEmptyUsersValidation () {
			$object = PlatformInstance::getInstance ()
				->setCode ('appef00001')
				->setStatus (PlatformInstanceInterface::STATUS_UNVERIFIED)
				->setAccountId (15245)
				->setAdministrator (User::getInstance ()->setEmail ('fperez@timemanagement.es')->setFirstName ('Felipe')->setLastName ('Pérez')->setUserName ('fperez@timemanagement.es'))
				->setModules (array (
					Module::getInstance ()->setLabel ('My module')->setName ('my_module')->setPresence (ModuleInterface::PRESENCE_USER_DEFINED)->setType (ModuleInterface::TYPE_TOOL)
				))
				->setName ('My super duper cuper instance')
				->setUsers (array (''));
			$this->expectException (PlatformInstanceException::class);
			$this->expectExceptionMessage (PlatformInstanceException::ERROR_INSTANCE_INVALID_USER);
			$object->validate ();
		}

		public function testInvalidUsersValidation () {
			$object = PlatformInstance::getInstance ()
				->setCode ('appef00001')
				->setStatus (PlatformInstanceInterface::STATUS_UNVERIFIED)
				->setAccountId (15245)
				->setAdministrator (User::getInstance ()->setEmail ('fperez@timemanagement.es')->setFirstName ('Felipe')->setLastName ('Pérez')->setUserName ('fperez@timemanagement.es'))
				->setModules (array (
					Module::getInstance ()->setLabel ('My module')->setName ('my_module')->setPresence (ModuleInterface::PRESENCE_USER_DEFINED)->setType (ModuleInterface::TYPE_TOOL)
				))
				->setName ('My super duper cuper instance')
				->setUsers (array ('thisisnotanuser', 'thisis@anemail.info'));
			$this->expectException (PlatformInstanceException::class);
			$this->expectExceptionMessage (PlatformInstanceException::ERROR_INSTANCE_INVALID_USER);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = PlatformInstance::getInstance ()
				->setCode ('appef00001')
				->setStatus (PlatformInstanceInterface::STATUS_UNVERIFIED)
				->setAccountId (15245)
				->setAdministrator (User::getInstance ()->setEmail ('fperez@timemanagement.es')->setFirstName ('Felipe')->setLastName ('Pérez')->setUserName ('fperez@timemanagement.es'))
				->setModules (array (
					Module::getInstance ()->setLabel ('My module')->setName ('my_module')->setPresence (ModuleInterface::PRESENCE_USER_DEFINED)->setType (ModuleInterface::TYPE_TOOL)
				))
				->setName ('My super duper cuper instance')
				->setUsers (array (
					User::getInstance ()->setEmail ('jsparrow@timemanagement.es')->setFirstName ('Jack')->setLastName ('Sparrow')->setUserName ('jsparrow@timemanagement.es'),
					User::getInstance ()->setEmail ('hbarbossa@timemanagement.es')->setFirstName ('Héctor')->setLastName ('Barbossa')->setUserName ('hbarbossa@timemanagement.es'),
				));
			$object->validate ();
		}

	}
	// @codingStandardsIgnoreEnd

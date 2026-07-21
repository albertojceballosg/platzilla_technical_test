<?php
	require_once ('include/platzilla/Objects/Application.php');

	/**
	 * Prueba unitaria de la clase Application
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ApplicationTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = Application::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'categoryId'  => 4,
				'code'        => 'crm',
				'description' => 'My super duper cuper application',
				'modules'     => array (array (Module::getInstance (), Module::getInstance (), Module::getInstance ())),
				'name'        => 'CRM',
				'price'       => 15.50,
				'profile'     => Profile::getInstance ()->setId (3)->setDescription ('My profile description')->setName ('My profile')->setMainApplicationCode ('crm'),
				'serviceId'   => 162,
				'status'      => array (ApplicationInterface::STATUS_ACTIVE, ApplicationInterface::STATUS_INACTIVE),
				'url'         => 'www.myapp.info',
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

		public function testEmptyCategoryIdValidation () {
			$object = Application::getInstance ();
			$this->expectException (ApplicationException::class);
			$this->expectExceptionMessage (ApplicationException::ERROR_APPLICATION_EMPTY_CATEGORY_ID);
			$object->validate ();
		}

		public function testEmptyCodeValidation () {
			$object = Application::getInstance ()
				->setCategoryId (4);
			$this->expectException (ApplicationException::class);
			$this->expectExceptionMessage (ApplicationException::ERROR_APPLICATION_EMPTY_CODE);
			$object->validate ();
		}

		public function testEmptyNameValidation () {
			$object = Application::getInstance ()
				->setCategoryId (4)
				->setCode ('my_app');
			$this->expectException (ApplicationException::class);
			$this->expectExceptionMessage (ApplicationException::ERROR_APPLICATION_EMPTY_NAME);
			$object->validate ();
		}

		public function testEmptyModulesValidation () {
			$object = Application::getInstance ()
				->setCategoryId (4)
				->setCode ('my_app')
				->setName ('My app');
			$this->expectException (ApplicationException::class);
			$this->expectExceptionMessage (ApplicationException::ERROR_APPLICATION_EMPTY_MODULES);
			$object->validate ();
		}

		public function testEmptyUrlValidation () {
			$object = Application::getInstance ()
				->setCategoryId (4)
				->setCode ('my_app')
				->setName ('My app')
				->setModules (array (Module::getInstance (), Module::getInstance ()));
			$this->expectException (ApplicationException::class);
			$this->expectExceptionMessage (ApplicationException::ERROR_APPLICATION_EMPTY_URL);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = Application::getInstance ()
				->setCategoryId (4)
				->setCode ('my_app')
				->setName ('My app')
				->setModules (array (Module::getInstance (), Module::getInstance ()))
				->setUrl ('http://www.myapp.info');
			$object->validate ();
		}

	}
	// @codingStandardsIgnoreEnd

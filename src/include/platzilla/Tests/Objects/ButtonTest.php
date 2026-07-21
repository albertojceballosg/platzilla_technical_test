<?php
	require_once ('include/platzilla/Objects/Button.php');

	/**
	 * Prueba unitaria de la clase Button
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ButtonTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = Button::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'             => 8,
				'action'         => '/index.php?module=backgroundtasks&action=RunTask&taskname=Convertir+potencial+cliente+en+cliente&record=[record]&return_module=[module]&return_action=[action]&return_record=[record]&Ajax=true',
				'description'    => 'My test button description',
				'isActive'       => array (true, false),
				'label'          => 'My test button',
				'location'       => array (ButtonInterface::LOCATION_DETAIL_VIEW, ButtonInterface::LOCATION_EDIT_VIEW, ButtonInterface::LOCATION_LIST_VIEW),
				'moduleName'     => 'my_module_name',
				'runInNewWindow' => array (true, false),
				'style'          => 'danger',
				'type'           => array (ButtonInterface::TYPE_JAVASCRIPT, ButtonInterface::TYPE_LINK),
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

		public function testLocationGetterAndSetter () {
			$object = Button::getInstance ()
				->setLocation ('unknown_location');
			$this->assertNull ($object->getLocation (), 'Location should be null');
		}

		public function testTypeGetterAndSetter () {
			$object = Button::getInstance ()
				->setType ('unknown_type');
			$this->assertNull ($object->getType (), 'Type should be null');
		}

		public function testEmptyActionValidation () {
			$object = Button::getInstance ();
			$this->expectException (ButtonException::class);
			$this->expectExceptionMessage (ButtonException::ERROR_BUTTON_EMPTY_ACTION);
			$object->validate ();
		}

		public function testEmptyLabelValidation () {
			$object = Button::getInstance ()
				->setAction ('doSomething();');
			$this->expectException (ButtonException::class);
			$this->expectExceptionMessage (ButtonException::ERROR_BUTTON_EMPTY_LABEL);
			$object->validate ();
		}

		public function testEmptyLocationValidation () {
			$object = Button::getInstance ()
				->setAction ('doSomething();')
				->setLabel ('My test button');
			$this->expectException (ButtonException::class);
			$this->expectExceptionMessage (ButtonException::ERROR_BUTTON_EMPTY_LOCATION);
			$object->validate ();
		}

		public function testEmptyStyleValidation () {
			$object = Button::getInstance ()
				->setAction ('doSomething();')
				->setLabel ('My test button')
				->setLocation (ButtonInterface::LOCATION_LIST_VIEW)
				->setModuleName ('test_module');
			$this->expectException (ButtonException::class);
			$this->expectExceptionMessage (ButtonException::ERROR_BUTTON_EMPTY_STYLE);
			$object->validate ();
		}

		public function testEmptyTypeValidation () {
			$object = Button::getInstance ()
				->setAction ('doSomething();')
				->setLabel ('My test button')
				->setLocation (ButtonInterface::LOCATION_LIST_VIEW)
				->setModuleName ('test_module')
				->setStyle ('danger');
			$this->expectException (ButtonException::class);
			$this->expectExceptionMessage (ButtonException::ERROR_BUTTON_EMPTY_TYPE);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = Button::getInstance ()
				->setAction ('doSomething();')
				->setLabel ('My test button')
				->setLocation (ButtonInterface::LOCATION_LIST_VIEW)
				->setModuleName ('test_module')
				->setStyle ('danger')
				->setType (ButtonInterface::TYPE_JAVASCRIPT);
			$object->validate ();
		}

		public function testDuplicate () {
			$object = Button::getInstance ()
				->setId (758)
				->setAction ('doSomething();')
				->setDescription ('My super duper cuper test button')
				->setIsActive (false)
				->setLabel ('My test button')
				->setLocation (ButtonInterface::LOCATION_LIST_VIEW)
				->setModuleName ('test_module')
				->setRunInNewWindow (true)
				->setStyle ('danger')
				->setType (ButtonInterface::TYPE_JAVASCRIPT);

			$duplicatedObject = $object->duplicate ($object->getId ());
			$this->assertEquals (758, $duplicatedObject->getId (), 'IDs do not match');
			$this->assertEquals ('doSomething();', $duplicatedObject->getAction (), 'OnClick properties do not match');
			$this->assertEquals ('My super duper cuper test button', $duplicatedObject->getDescription (), 'Descriptions do not match');
			$this->assertEquals (false, $duplicatedObject->getIsActive (), 'IsActive properties do not match');
			$this->assertEquals ('My test button', $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals (ButtonInterface::LOCATION_LIST_VIEW, $duplicatedObject->getLocation (), 'Locations do not match');
			$this->assertEquals ('test_module', $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals (true, $duplicatedObject->getRunInNewWindow (), 'RunInNewWndow properties do not match');
			$this->assertEquals ('danger', $duplicatedObject->getStyle (), 'Styles do not match');
			$this->assertEquals (ButtonInterface::TYPE_JAVASCRIPT, $duplicatedObject->getType (), 'Types do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals (null, $duplicatedObject->getId (), 'IDs do not match');
			$this->assertEquals ('doSomething();', $duplicatedObject->getAction (), 'OnClick properties do not match');
			$this->assertEquals ('My super duper cuper test button', $duplicatedObject->getDescription (), 'Descriptions do not match');
			$this->assertEquals (false, $duplicatedObject->getIsActive (), 'IsActive properties do not match');
			$this->assertEquals ('My test button', $duplicatedObject->getLabel (), 'Labels do not match');
			$this->assertEquals (ButtonInterface::LOCATION_LIST_VIEW, $duplicatedObject->getLocation (), 'Locations do not match');
			$this->assertEquals ('test_module', $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals (true, $duplicatedObject->getRunInNewWindow (), 'RunInNewWndow properties do not match');
			$this->assertEquals ('danger', $duplicatedObject->getStyle (), 'Styles do not match');
			$this->assertEquals (ButtonInterface::TYPE_JAVASCRIPT, $duplicatedObject->getType (), 'Types do not match');
		}

	}
	// @codingStandardsIgnoreEnd
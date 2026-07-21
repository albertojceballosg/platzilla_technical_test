<?php
	require_once ('include/platzilla/Objects/ModuleRelationship.php');

	/**
	 * Prueba unitaria de la clase ModuleRelationship
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ModuleRelationshipTest extends PHPUnit_Framework_TestCase {

		public function testGettersAndSetters () {
			$object         = ModuleRelationship::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'actions'           => array (array (ModuleRelationshipInterface::ACTION_ADD), array (ModuleRelationshipInterface::ACTION_SELECT)),
				'function'          => 'my_function_name',
				'label'             => 'My label',
				'moduleName'        => 'module_name',
				'presence'          => array (ModuleRelationshipInterface::PRESENCE_HIDDEN, ModuleRelationshipInterface::PRESENCE_VISIBLE),
				'relatedModuleName' => 'related_module_name',
				'sequence'          => 0,
			];

			foreach ($testProperties as $propertyName => $propertyValues) {
				$propertyName = ucfirst ($propertyName);
				$getter       = is_bool ($propertyValues) ? "is{$propertyName}" : "get{$propertyName}";
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

		public function testEmptyFunctionValidation () {
			$object = ModuleRelationship::getInstance ();
			$this->expectException (ModuleRelationshipException::class);
			$this->expectExceptionMessage (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_EMPTY_FUNCTION);
			$object->validate ();
		}

		public function testEmptyLabelValidation () {
			$object = ModuleRelationship::getInstance ()
				->setFunction ('my_function_name');
			$this->expectException (ModuleRelationshipException::class);
			$this->expectExceptionMessage (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_EMPTY_LABEL);
			$object->validate ();
		}

		public function testEmptyModuleNameValidation () {
			$object = ModuleRelationship::getInstance ()
				->setFunction ('my_function_name')
				->setLabel ('My relationship');
			$this->expectException (ModuleRelationshipException::class);
			$this->expectExceptionMessage (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_EMPTY_MODULE_NAME);
			$object->validate ();
		}

		public function testEmptyRelatedModuleNameValidation () {
			$object = ModuleRelationship::getInstance ()
				->setFunction ('my_function_name')
				->setLabel ('My relationship')
				->setModuleName ('my_module_name');
			$this->expectException (ModuleRelationshipException::class);
			$this->expectExceptionMessage (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_EMPTY_RELATED_MODULE_NAME);
			$object->validate ();
		}

		public function testInvalidActionsValidation () {
			/** @noinspection PhpParamsInspection */
			$object = ModuleRelationship::getInstance ()
				->setFunction ('my_function_name')
				->setLabel ('My relationship')
				->setModuleName ('my_module_name')
				->setRelatedModuleName ('my_related_module_name')
				->setSequence (0)
				->setActions ('not_an_action_array');
			$this->expectException (ModuleRelationshipException::class);
			$this->expectExceptionMessage (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_INVALID_ACTIONS);
			$object->validate ();
		}

		public function testInvalidActionValidation () {
			$object = ModuleRelationship::getInstance ()
				->setFunction ('my_function_name')
				->setLabel ('My relationship')
				->setModuleName ('my_module_name')
				->setRelatedModuleName ('my_related_module_name')
				->setActions (array ('not_an_action'));
			$this->expectException (ModuleRelationshipException::class);
			$this->expectExceptionMessage (ModuleRelationshipException::ERROR_MODULE_RELATIONSHIP_INVALID_ACTION);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = ModuleRelationship::getInstance ()
				->setFunction ('my_function_name')
				->setLabel ('My relationship')
				->setModuleName ('my_module_name')
				->setRelatedModuleName ('my_related_module_name')
				->setActions (array (ModuleRelationshipInterface::ACTION_ADD, ModuleRelationshipInterface::ACTION_SELECT));
			$object->validate ();
		}

	}
	// @codingStandardsIgnoreEnd

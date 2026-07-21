<?php
	require_once ('include/platzilla/Exceptions/FieldModuleReferenceException.php');
	require_once ('include/platzilla/Objects/FieldModuleReference.php');
	/**
	 * Prueba unitaria de la clase FieldModuleReference
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class FieldModuleReferenceTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = FieldModuleReference::getInstance ();
			$testClass      = FieldModuleReference::class;
			$testProperties = [
				'fieldName'            => 'my_field_name',
				'moduleName'           => 'my_module_name',
				'referencedModuleName' => 'my_referenced_module_name',
				'sequence'             => 35,
				'status'               => 'my_status',
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

		public function testEmptyReferencedModuleNameValidation () {
			$object = FieldModuleReference::getInstance ()
			->setFieldName ('my_field_name')
			->setModuleName ('my_module_name');
			$this->expectException (FieldModuleReferenceException::class);
			$this->expectExceptionMessage (FieldModuleReferenceException::ERROR_FIELD_MODULE_REFERENCE_EMPTY_REFERENCED_MODULE_NAME);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = FieldModuleReference::getInstance ()
			->setFieldName ('my_field_name')
			->setModuleName ('my_module_name')
			->setReferencedModuleName ('my_referenced_module_name');
			$object->validate ();
		}

		public function testDuplicate () {
			$object = FieldModuleReference::getInstance ()
						->setFieldName ('another_field_name')
						->setModuleName ('another_module_name')
						->setReferencedModuleName ('my_referenced_module_name')
						->setSequence (3)
						->setStatus ('unknown');

			$duplicatedObject = $object->duplicate ();
			$this->assertEquals ('another_field_name', $duplicatedObject->getFieldName (), 'Field names do not match');
			$this->assertEquals ('another_module_name', $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ('my_referenced_module_name', $duplicatedObject->getReferencedModuleName (), 'Referenced module names do not match');
			$this->assertEquals (3, $duplicatedObject->getSequence (), 'Sequences do not match');
			$this->assertEquals ('unknown', $duplicatedObject->getStatus (), 'Sequences do not match');
		}

	}
	// @codingStandardsIgnoreEnd

<?php
	require_once ('include/platzilla/Objects/FieldDependency.php');

	/**
	 * Prueba unitaria de la clase FieldDependency
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class FieldDependencyTest extends PHPUnit_Framework_TestCase {

		public function testGettersAndSetters () {
			$object         = FieldDependency::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'moduleName'            => 'my_module_name',
				'sourceFieldName'       => 'my_source_field_name',
				'sourceFieldValue'      => 'My source field value',
				'targetFieldName'       => 'my_target_field_name',
				'targetFieldVisibility' => FieldDependencyInterface::VISIBILITY_VISIBLE,
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
			$object = FieldDependency::getInstance ();
			$this->expectException (FieldDependencyException::class);
			$this->expectExceptionMessage (FieldDependencyException::ERROR_FIELD_DEPENDENCY_EMPTY_MODULE_NAME);
			$object->validate ();
		}

		public function testEmptySourceFieldNameValidation () {
			$object = FieldDependency::getInstance ()
				->setModuleName ('my_module_name');
			$this->expectException (FieldDependencyException::class);
			$this->expectExceptionMessage (FieldDependencyException::ERROR_FIELD_DEPENDENCY_EMPTY_SOURCE_FIELD_NAME);
			$object->validate ();
		}

		public function testEmptyTargetFieldNameValidation () {
			$object = FieldDependency::getInstance ()
				->setModuleName ('my_module_name')
				->setSourceFieldName ('my_source_field_name');
			$this->expectException (FieldDependencyException::class);
			$this->expectExceptionMessage (FieldDependencyException::ERROR_FIELD_DEPENDENCY_EMPTY_TARGET_FIELD_NAME);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = FieldDependency::getInstance ()
				->setModuleName ('my_module_name')
				->setSourceFieldName ('my_source_field_name')
				->setTargetFieldName ('my_target_field_name')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_VISIBLE);
			$object->validate ();
		}

		public function testDuplicate () {
			$object = FieldDependency::getInstance ()
				->setModuleName ('my_module_name')
				->setSourceFieldName ('my_source_field_name')
				->setSourceFieldValue ('My source field value')
				->setTargetFieldName ('my_target_field_name')
				->setTargetFieldVisibility (FieldDependencyInterface::VISIBILITY_HIDDEN);

			$duplicatedObject = $object->duplicate ();
			$this->assertEquals ('my_module_name', $duplicatedObject->getModuleName (), 'Module names do not match');
			$this->assertEquals ('my_source_field_name', $duplicatedObject->getSourceFieldName (), 'Source field names do not match');
			$this->assertEquals ('My source field value', $duplicatedObject->getSourceFieldValue (), 'Source field values do not match');
			$this->assertEquals ('my_target_field_name', $duplicatedObject->getTargetFieldName (), 'Target field names do not match');
			$this->assertEquals (FieldDependencyInterface::VISIBILITY_HIDDEN, $duplicatedObject->getTargetFieldVisibility (), 'Target field visibilities do not match');
		}

	}
	// @codingStandardsIgnoreEnd

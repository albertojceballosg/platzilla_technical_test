<?php
	require_once ('include/platzilla/Objects/ReportFolder.php');

	/**
	 * Prueba unitaria de la clase ReportFolder
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ReportFolderTest extends PHPUnit_Framework_TestCase {
		public function testGettersAndSetters () {
			$object         = ReportFolder::getInstance ();
			$testClass      = get_class ($object);
			$testProperties = [
				'id'          => 41,
				'description' => 'My report folder description',
				'name'        => 'My report folder',
				'status'      => array (ReportInterface::STATUS_CUSTOMIZED, ReportInterface::STATUS_SAVED),
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
			$object = ReportFolder::getInstance ();
			$this->expectException (ReportException::class);
			$this->expectExceptionMessage (ReportException::ERROR_REPORT_FOLDER_EMPTY_NAME);
			$object->validate ();
		}

		public function testValidationSucceed () {
			$object = ReportFolder::getInstance ()
				->setName ('My report folder');
			$object->validate ();
		}

		public function testDuplicate () {
			$object = ReportFolder::getInstance ()
				->setId (33)
				->setDescription ('My report folder description')
				->setName ('My report folder')
				->setStatus (ReportInterface::STATUS_CUSTOMIZED);

			$duplicatedObject = $object->duplicate ($object->getId ());
			$this->assertEquals ($object->getDescription (), $duplicatedObject->getDescription (), 'Descriptions do not match');
			$this->assertEquals ($object->getName (), $duplicatedObject->getName (), 'Names do not match');
			$this->assertEquals (33, $duplicatedObject->getId (), 'Folder IDs do not match');
			$this->assertEquals (ReportInterface::STATUS_CUSTOMIZED, $duplicatedObject->getStatus (), 'Statuses do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals ($object->getDescription (), $duplicatedObject->getDescription (), 'Descriptions do not match');
			$this->assertEquals ($object->getName (), $duplicatedObject->getName (), 'Names do not match');
			$this->assertEquals (null, $duplicatedObject->getId (), 'Folder IDs do not match');
			$this->assertEquals (ReportInterface::STATUS_CUSTOMIZED, $duplicatedObject->getStatus (), 'Statuses do not match');
		}

	}
	// @codingStandardsIgnoreEnd

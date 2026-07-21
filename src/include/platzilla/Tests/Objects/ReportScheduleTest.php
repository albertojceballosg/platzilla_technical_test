<?php
	require_once ('include/platzilla/Objects/ReportSchedule.php');

	/**
	 * Prueba unitaria de la clase ReportSchedule
	 *
	 * @codingStandardsIgnoreStart
	 * @SuppressWarnings(PHPMD)
	 */
	class ReportScheduleTest extends PHPUnit_Framework_TestCase {

		public function testEmptyFormatValidation () {
			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_DAILY, '09:00');
			$this->expectException (ReportScheduleException::class);
			$this->expectExceptionMessage (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_FORMAT);
			$object->validate ();
		}

		public function testEmptyFrequencyValidation () {
			$object = ReportSchedule::getInstance (null, '09:00')
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$this->expectException (ReportScheduleException::class);
			$this->expectExceptionMessage (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_FREQUENCY);
			$object->validate ();
		}

		public function testEmptyTimeValidation () {
			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_DAILY, null)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$this->expectException (ReportScheduleException::class);
			$this->expectExceptionMessage (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_TIME);
			$object->validate ();
		}

		public function testEmptyWeekdayValidation () {
			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_BIWEEKLY, '09:00')
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$this->expectException (ReportScheduleException::class);
			$this->expectExceptionMessage (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_WEEKDAY);
			$object->validate ();

			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_WEEKLY, '09:00')
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();

			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_BIWEEKLY, '09:00', 7)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();

			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_WEEKLY, '09:00', 7)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();
		}

		public function testEmptyDayValidation () {
			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_MONTHLY, '09:00')
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$this->expectException (ReportScheduleException::class);
			$this->expectExceptionMessage (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_DAY);
			$object->validate ();

			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_YEARLY, '09:00')
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();

			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_MONTHLY, '09:00', 55)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();
			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_YEARLY, '09:00', 55)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();
		}

		public function testEmptyMonthValidation () {
			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_YEARLY, '09:00', 5)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$this->expectException (ReportScheduleException::class);
			$this->expectExceptionMessage (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_MONTH);
			$object->validate ();

			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_YEARLY, '09:00', 5, 14)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();
		}

		public function testEmptyRecipientsValidation () {
			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_DAILY, '09:00')
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$this->expectException (ReportScheduleException::class);
			$this->expectExceptionMessage (ReportScheduleException::ERROR_REPORT_SCHEDULE_EMPTY_RECIPIENTS);
			$object->validate ();

			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_DAILY, '09:00')
				->setGroups (array ())
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();

			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_DAILY, '09:00')
				->setRoles (array ())
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();

			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_DAILY, '09:00')
				->setRolesAndSubordinates (array ())
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();

			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_DAILY, '09:00')
				->setUsers (array ())
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$object->validate ();
		}

		public function testValidDailySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_DAILY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1');
			$rolesAndSubordinates = array ('H2');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00')
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);
			$object->validate ();

			$this->assertEquals ($frequency, $object->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $object->getGroups (), 'Groups do not match');
			$this->assertEquals ($reportId, $object->getReportId (), 'Report IDs do not match');
			$this->assertEquals ($roles, $object->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $object->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $object->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $object->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $object->getFormat (), 'Formats do not match');
			$this->assertEquals (null, $object->getDay (), 'Days do not match');
			$this->assertEquals (null, $object->getMonth (), 'Months do not match');
			$this->assertEquals (null, $object->getWeekDay (), 'Weekdays do not match');
		}

		public function testValidWeeklySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_BIWEEKLY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1');
			$rolesAndSubordinates = array ('H2');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00', ReportScheduleInterface::WEEKDAY_WEDNESDAY)
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);
			$object->validate ();

			$this->assertEquals ($frequency, $object->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $object->getGroups (), 'Groups do not match');
			$this->assertEquals ($reportId, $object->getReportId (), 'Report IDs do not match');
			$this->assertEquals ($roles, $object->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $object->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $object->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $object->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $object->getFormat (), 'Formats do not match');
			$this->assertEquals (null, $object->getDay (), 'Days do not match');
			$this->assertEquals (null, $object->getMonth (), 'Months do not match');
			$this->assertEquals (ReportScheduleInterface::WEEKDAY_WEDNESDAY, $object->getWeekDay (), 'Weekdays do not match');
		}

		public function testValidMonthlySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_MONTHLY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1');
			$rolesAndSubordinates = array ('H2');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00', 5)
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);
			$object->validate ();

			$this->assertEquals ($frequency, $object->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $object->getGroups (), 'Groups do not match');
			$this->assertEquals ($reportId, $object->getReportId (), 'Report IDs do not match');
			$this->assertEquals ($roles, $object->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $object->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $object->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $object->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $object->getFormat (), 'Formats do not match');
			$this->assertEquals (5, $object->getDay (), 'Days do not match');
			$this->assertEquals (null, $object->getMonth (), 'Months do not match');
			$this->assertEquals (null, $object->getWeekDay (), 'Weekdays do not match');
		}

		public function testValidYearlySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_YEARLY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1');
			$rolesAndSubordinates = array ('H2');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00', 5, ReportScheduleInterface::MONTH_JULY)
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);
			$object->validate ();

			$this->assertEquals ($frequency, $object->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $object->getGroups (), 'Groups do not match');
			$this->assertEquals ($reportId, $object->getReportId (), 'Report IDs do not match');
			$this->assertEquals ($roles, $object->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $object->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $object->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $object->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $object->getFormat (), 'Formats do not match');
			$this->assertEquals (5, $object->getDay (), 'Days do not match');
			$this->assertEquals (ReportScheduleInterface::MONTH_JULY, $object->getMonth (), 'Months do not match');
			$this->assertEquals (null, $object->getWeekDay (), 'Weekdays do not match');
		}

		public function testDuplicateDailySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_DAILY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1');
			$rolesAndSubordinates = array ('H2');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00')
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);

			$duplicatedObject = $object->duplicate (45);
			$this->assertEquals ($frequency, $duplicatedObject->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $duplicatedObject->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $duplicatedObject->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $duplicatedObject->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $duplicatedObject->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $duplicatedObject->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $duplicatedObject->getFormat (), 'Formats do not match');
			$this->assertEquals (null, $duplicatedObject->getDay (), 'Days do not match');
			$this->assertEquals (null, $duplicatedObject->getMonth (), 'Months do not match');
			$this->assertEquals (null, $duplicatedObject->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (45, $duplicatedObject->getReportId (), 'Report IDs do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals ($frequency, $duplicatedObject->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $duplicatedObject->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $duplicatedObject->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $duplicatedObject->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $duplicatedObject->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $duplicatedObject->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $duplicatedObject->getFormat (), 'Formats do not match');
			$this->assertEquals (null, $duplicatedObject->getDay (), 'Days do not match');
			$this->assertEquals (null, $duplicatedObject->getMonth (), 'Months do not match');
			$this->assertEquals (null, $duplicatedObject->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (null, $duplicatedObject->getReportId (), 'Report IDs do not match');
		}

		public function testDuplicateWeeklySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_WEEKLY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1');
			$rolesAndSubordinates = array ('H2');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00', ReportScheduleInterface::WEEKDAY_WEDNESDAY)
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);

			$duplicatedObject = $object->duplicate (45);
			$this->assertEquals ($frequency, $duplicatedObject->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $duplicatedObject->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $duplicatedObject->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $duplicatedObject->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $duplicatedObject->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $duplicatedObject->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $duplicatedObject->getFormat (), 'Formats do not match');
			$this->assertEquals (null, $duplicatedObject->getDay (), 'Days do not match');
			$this->assertEquals (null, $duplicatedObject->getMonth (), 'Months do not match');
			$this->assertEquals (ReportScheduleInterface::WEEKDAY_WEDNESDAY, $duplicatedObject->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (45, $duplicatedObject->getReportId (), 'Report IDs do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals ($frequency, $duplicatedObject->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $duplicatedObject->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $duplicatedObject->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $duplicatedObject->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $duplicatedObject->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $duplicatedObject->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $duplicatedObject->getFormat (), 'Formats do not match');
			$this->assertEquals (null, $duplicatedObject->getDay (), 'Days do not match');
			$this->assertEquals (null, $duplicatedObject->getMonth (), 'Months do not match');
			$this->assertEquals (ReportScheduleInterface::WEEKDAY_WEDNESDAY, $duplicatedObject->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (null, $duplicatedObject->getReportId (), 'Report IDs do not match');
		}

		public function testDuplicateMonthlySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_MONTHLY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1');
			$rolesAndSubordinates = array ('H2');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00', 5)
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);

			$duplicatedObject = $object->duplicate (45);
			$this->assertEquals ($frequency, $duplicatedObject->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $duplicatedObject->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $duplicatedObject->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $duplicatedObject->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $duplicatedObject->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $duplicatedObject->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $duplicatedObject->getFormat (), 'Formats do not match');
			$this->assertEquals (5, $duplicatedObject->getDay (), 'Days do not match');
			$this->assertEquals (null, $duplicatedObject->getMonth (), 'Months do not match');
			$this->assertEquals (null, $duplicatedObject->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (45, $duplicatedObject->getReportId (), 'Report IDs do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals ($frequency, $duplicatedObject->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $duplicatedObject->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $duplicatedObject->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $duplicatedObject->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $duplicatedObject->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $duplicatedObject->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $duplicatedObject->getFormat (), 'Formats do not match');
			$this->assertEquals (5, $duplicatedObject->getDay (), 'Days do not match');
			$this->assertEquals (null, $duplicatedObject->getMonth (), 'Months do not match');
			$this->assertEquals (null, $duplicatedObject->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (null, $duplicatedObject->getReportId (), 'Report IDs do not match');
		}

		public function testDuplicateYearlySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_YEARLY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1', 'H2');
			$rolesAndSubordinates = array ('H3');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00', 5, ReportScheduleInterface::MONTH_JULY)
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);

			$duplicatedObject = $object->duplicate (45);
			$this->assertEquals ($frequency, $duplicatedObject->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $duplicatedObject->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $duplicatedObject->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $duplicatedObject->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $duplicatedObject->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $duplicatedObject->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $duplicatedObject->getFormat (), 'Formats do not match');
			$this->assertEquals (5, $duplicatedObject->getDay (), 'Days do not match');
			$this->assertEquals (ReportScheduleInterface::MONTH_JULY, $duplicatedObject->getMonth (), 'Months do not match');
			$this->assertEquals (null, $duplicatedObject->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (45, $duplicatedObject->getReportId (), 'Report IDs do not match');

			$duplicatedObject = $object->duplicate (null);
			$this->assertEquals ($frequency, $duplicatedObject->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $duplicatedObject->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $duplicatedObject->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $duplicatedObject->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $duplicatedObject->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $duplicatedObject->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $duplicatedObject->getFormat (), 'Formats do not match');
			$this->assertEquals (5, $duplicatedObject->getDay (), 'Days do not match');
			$this->assertEquals (ReportScheduleInterface::MONTH_JULY, $duplicatedObject->getMonth (), 'Months do not match');
			$this->assertEquals (null, $duplicatedObject->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (null, $duplicatedObject->getReportId (), 'Report IDs do not match');
		}

		public function testCopyValuesFromDailySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_DAILY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1');
			$rolesAndSubordinates = array ('H2');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00')
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);

			$objectCopy = ReportSchedule::getInstance (null, null)
				->setReportId (45);
			$objectCopy->copyValuesFrom ($object);
			$this->assertEquals ($frequency, $objectCopy->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $objectCopy->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $objectCopy->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $objectCopy->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $objectCopy->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $objectCopy->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $objectCopy->getFormat (), 'Formats do not match');
			$this->assertEquals (null, $objectCopy->getDay (), 'Days do not match');
			$this->assertEquals (null, $objectCopy->getMonth (), 'Months do not match');
			$this->assertEquals (null, $objectCopy->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (45, $objectCopy->getReportId (), 'Report IDs do not match');
		}

		public function testCopyValuesFromWeeklySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_WEEKLY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1');
			$rolesAndSubordinates = array ('H2');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00', ReportScheduleInterface::WEEKDAY_WEDNESDAY)
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);

			$objectCopy = ReportSchedule::getInstance (null, null)
				->setReportId (45);
			$objectCopy->copyValuesFrom ($object);
			$this->assertEquals ($frequency, $objectCopy->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $objectCopy->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $objectCopy->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $objectCopy->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $objectCopy->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $objectCopy->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $objectCopy->getFormat (), 'Formats do not match');
			$this->assertEquals (null, $objectCopy->getDay (), 'Days do not match');
			$this->assertEquals (null, $objectCopy->getMonth (), 'Months do not match');
			$this->assertEquals (ReportScheduleInterface::WEEKDAY_WEDNESDAY, $objectCopy->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (45, $objectCopy->getReportId (), 'Report IDs do not match');
		}

		public function testCopyValuesFromMonthlySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_MONTHLY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1');
			$rolesAndSubordinates = array ('H2');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00', 5)
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);

			$objectCopy = ReportSchedule::getInstance (null, null)
				->setReportId (45);
			$objectCopy->copyValuesFrom ($object);
			$this->assertEquals ($frequency, $objectCopy->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $objectCopy->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $objectCopy->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $objectCopy->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $objectCopy->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $objectCopy->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $objectCopy->getFormat (), 'Formats do not match');
			$this->assertEquals (5, $objectCopy->getDay (), 'Days do not match');
			$this->assertEquals (null, $objectCopy->getMonth (), 'Months do not match');
			$this->assertEquals (null, $objectCopy->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (45, $objectCopy->getReportId (), 'Report IDs do not match');
		}

		public function testCopyValuesFromYearlySchedule () {
			$frequency            = ReportScheduleInterface::FREQUENCY_YEARLY;
			$reportId             = 23;
			$groups               = array (10);
			$roles                = array ('H1', 'H2');
			$rolesAndSubordinates = array ('H3');
			$users                = array (1);

			$object = ReportSchedule::getInstance ($frequency, '09:00', 5, ReportScheduleInterface::MONTH_JULY)
				->setGroups ($groups)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH)
				->setReportId ($reportId)
				->setRoles ($roles)
				->setRolesAndSubordinates ($rolesAndSubordinates)
				->setUsers ($users);

			$objectCopy = ReportSchedule::getInstance (null, null)
				->setReportId (45);
			$objectCopy->copyValuesFrom ($object);
			$this->assertEquals ($frequency, $objectCopy->getFrequency (), 'Frequencies do not match');
			$this->assertEquals ($groups, $objectCopy->getGroups (), 'Groups do not match');
			$this->assertEquals ($roles, $objectCopy->getRoles (), 'Roles do not match');
			$this->assertEquals ($rolesAndSubordinates, $objectCopy->getRolesAndSubordinates (), 'Roles and subordinates do not match');
			$this->assertEquals ($users, $objectCopy->getUsers (), 'Users do not match');
			$this->assertEquals ('09:00', $objectCopy->getTime (), 'Times do not match');
			$this->assertEquals (ReportScheduleInterface::FORMAT_BOTH, $objectCopy->getFormat (), 'Formats do not match');
			$this->assertEquals (5, $objectCopy->getDay (), 'Days do not match');
			$this->assertEquals (ReportScheduleInterface::MONTH_JULY, $objectCopy->getMonth (), 'Months do not match');
			$this->assertEquals (null, $objectCopy->getWeekDay (), 'Weekdays do not match');
			$this->assertEquals (45, $objectCopy->getReportId (), 'Report IDs do not match');
		}

		public function testIsEqualTo () {
			$object = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_YEARLY, '09:00', 5, ReportScheduleInterface::MONTH_JULY)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$anotherObject = ReportSchedule::getInstance (ReportScheduleInterface::FREQUENCY_YEARLY, '09:00', 5, ReportScheduleInterface::MONTH_JULY)
				->setFormat (ReportScheduleInterface::FORMAT_BOTH);
			$this->assertTrue ($object->isEqualTo ($object), 'Objects should be equal');
			$this->assertTrue ($anotherObject->isEqualTo ($anotherObject), 'Objects should be equal');
			$this->assertTrue ($object->isEqualTo ($anotherObject), 'Objects should be equal');

			// Cambiar ReportId no debe hacer diferencia
			$object->setReportId (23);
			$this->assertTrue ($object->isEqualTo ($object), 'Objects should be equal');
			$this->assertTrue ($object->isEqualTo ($anotherObject), 'Objects should be equal');

			$anotherObject->setReportId (32);
			$this->assertTrue ($anotherObject->isEqualTo ($anotherObject), 'Objects should be equal');
			$this->assertTrue ($object->isEqualTo ($anotherObject), 'Objects should be equal');

			// Cambiar grupos
			$object->setGroups (array (10, 11));
			$this->assertTrue ($object->isEqualTo ($object), 'Objects should be equal');
			$this->assertFalse ($object->isEqualTo ($anotherObject), 'Objects should not be equal');

			$anotherObject->setGroups (array (11, 10));
			$this->assertTrue ($anotherObject->isEqualTo ($anotherObject), 'Objects should be equal');
			$this->assertTrue ($object->isEqualTo ($anotherObject), 'Objects should be equal');

			// Cambiar roles
			$object->setRoles (array ('H1', 'H2'));
			$this->assertTrue ($object->isEqualTo ($object), 'Objects should be equal');
			$this->assertFalse ($object->isEqualTo ($anotherObject), 'Objects should not be equal');

			$anotherObject->setRoles (array ('H2', 'H1'));
			$this->assertTrue ($anotherObject->isEqualTo ($anotherObject), 'Objects should be equal');
			$this->assertTrue ($object->isEqualTo ($anotherObject), 'Objects should be equal');

			// Cambiar roles y subordinados
			$object->setRolesAndSubordinates (array ('H3', 'H4'));
			$this->assertTrue ($object->isEqualTo ($object), 'Objects should be equal');
			$this->assertFalse ($object->isEqualTo ($anotherObject), 'Objects should not be equal');

			$anotherObject->setRolesAndSubordinates (array ('H4', 'H3'));
			$this->assertTrue ($anotherObject->isEqualTo ($anotherObject), 'Objects should be equal');
			$this->assertTrue ($object->isEqualTo ($anotherObject), 'Objects should be equal');

			// Cambiar usuarios
			$object->setUsers (array (1, 2));
			$this->assertTrue ($object->isEqualTo ($object), 'Objects should be equal');
			$this->assertFalse ($object->isEqualTo ($anotherObject), 'Objects should not be equal');

			$anotherObject->setUsers (array (2, 1));
			$this->assertTrue ($anotherObject->isEqualTo ($anotherObject), 'Objects should be equal');
			$this->assertTrue ($object->isEqualTo ($anotherObject), 'Objects should be equal');

			// El método copyValuesFrom debería dar un objeto igual
			$aThirdObject = ReportSchedule::getInstance (null, null);
			$aThirdObject->copyValuesFrom ($object);
			$this->assertTrue ($aThirdObject->isEqualTo ($aThirdObject), 'Objects should be equal');
			$this->assertTrue ($aThirdObject->isEqualTo ($object), 'Objects should be equal');
		}

	}
	// @codingStandardsIgnoreEnd

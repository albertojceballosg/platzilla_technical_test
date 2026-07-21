<?php
	require_once ('include/platzilla/Data/WorkingDayMaster.php');
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('config.php');
	
	class WorkingDayManager {
		
		/** @var WorkingDayManager|null */
		private static $INSTANCES = null;
		
		/** @var PearDatabase */
		private $adb;
		
		/** @var PearDatabase  */
		private $masterAdb;
		
		public function __construct (PearDatabase $adb) {
			$this->adb       = $adb;
			$this->masterAdb = ($this->adb->dbName == $dbconfig['db_name']) ? $adb : AdbManager::getInstance ()->getMasterAdb ();
		}
		
		/**
		 * @param integer $workingDayId
		 */
		private function checkDaysOfWeek ($workingDayId) {
			if (empty($workingDayId)) {
				return;
			}
			$this->adb->pquery ('DELETE FROM vtiger_working_days WHERE workingdayid=?', array ($workingDayId));
		}
		
		/**
		 * @param integer $workingDayId
		 *
		 * @return WorkingDaysOfWeek[]|null
		 * @throws Exception
		 */
		private function fetchWorkingDayOfWeek ($workingDayId) {
			if (empty($workingDayId)) {
				return null;
			}
			$result = $this->adb->pquery ('SELECT * FROM vtiger_working_days WHERE workingdayid=?', array($workingDayId));
			if ($this->adb->num_rows ($result) > 0) {
				$workDayOfWeek = array();
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					$workDayOfWeek [] = WorkingDaysOfWeek::getInstance ()
						->setAfternoonStartTime ($row ['afternoon_start_time'])
						->setAfternoonDueTime ($row ['afternoon_due_time'])
						->setId ($row ['working_dayid'])
						->setMorningDueTime ($row ['morning_due_time'])
						->setMorningStartTime ($row ['afternoon_start_time'])
						->setWorkingDayId ($row ['workingdayid'])
						->setWorkingDayName ($row ['weekday'])
						->setWorkingHours ($row ['working_hours']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($workDayOfWeek)) ? $workDayOfWeek : null;
		}
		
		/**
		 * @param WorkingDayMaster $workingDay
		 */
		private function saveWorkingDaysOfWeek ($workingDay) {
			if (empty ($workingDay->getWorkingDaysOfWeek ())) {
				return;
			}
			
			$this->checkDaysOfWeek ($workingDay->getId ());
			
			foreach ($workingDay->getWorkingDaysOfWeek () as $weekDay) {
				if (!$weekDay instanceof WorkingDaysOfWeek) {
					continue;
				}
				$this->adb->pquery (
					'INSERT INTO vtiger_working_days (workingdayid, weekday, working_hours, morning_start_time, morning_due_time, afternoon_start_time, afternoon_due_time) VALUES (?, ?, ?, ?, ?, ?, ?)',
					array ($workingDay->getId (), $weekDay->getWorkingDayName (), $weekDay->getWorkingHours (), $weekDay->getMorningStartTime (), $weekDay->getMorningDueTime (), $weekDay->getAfternoonStartTime (), $weekDay->getAfternoonDueTime ())
				);
			}
		}
		
		/**
		 * @param boolean $headersOnly
		 * @param boolean $enabledOnly
		 *
		 * @return WorkingDayMaster[]|null
		 * @throws Exception
		 */
		public function fetchWorkingDay ($headersOnly = true, $enabledOnly = false ) {
			$where = '';
			if ($enabledOnly) {
				$where = "AND working_day_status = 'ENABLED'";
			}
			
			$result = $this->adb->query ("SELECT * FROM vtiger_working_days_master WHERE 1 {$where} ORDER BY working_day_name");
			if ($this->adb->num_rows ($result) > 0) {
				$workingDaysMaster = array();
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					$workingDaysMaster [] = WorkingDayMaster::getInstance ()
						->setAfternoonDueTime ($row ['afternoon_due_time'])
						->setAfternoonStartTime ($row ['afternoon_start_time'])
						->setDataTimeCreated ($row ['dt_created'])
						->setDescription ($row ['description'])
						->setId ($row ['workingdayid'])
						->setMorningDueTime ($row ['morning_due_time'])
						->setMorningStartTime ($row ['morning_start_time'])
						->setRegularWorkingHours ($row ['regular_working_hours'])
						->setWorkingDayName ($row ['working_day_name'])
						->setWorkingDaysOfWeek ((!$headersOnly) ? $this->fetchWorkingDayOfWeek ($row ['workingdayid']) : null)
						->setWorkingDayStatus ($row ['working_day_status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($workingDaysMaster)) ? $workingDaysMaster : null;
		}
		
		/**
		 * @param integer $workingDayId
		 * @param boolean $headersOnly
		 *
		 * @return WorkingDayMaster|null
		 * @throws Exception
		 */
		public function getWorkingDayById ($workingDayId, $headersOnly = true) {
			$result = $this->adb->pquery ('SELECT * FROM vtiger_working_days_master WHERE workingdayid=?', array ($workingDayId));
			if ($this->adb->num_rows ($result) > 0) {
				while ($row = $this->adb->fetchByAssoc($result, -1, false)) {
					$workingDaysMaster = WorkingDayMaster::getInstance ()
						->setAfternoonDueTime ($row ['afternoon_due_time'])
						->setAfternoonStartTime ($row ['afternoon_start_time'])
						->setDataTimeCreated ($row ['dt_created'])
						->setDescription ($row ['description'])
						->setId ($row ['workingdayid'])
						->setMorningDueTime ($row ['morning_due_time'])
						->setMorningStartTime ($row ['morning_start_time'])
						->setRegularWorkingHours ($row ['regular_working_hours'])
						->setWorkingDayName ($row ['working_day_name'])
						->setWorkingDaysOfWeek ((!$headersOnly) ? $this->fetchWorkingDayOfWeek ($row ['workingdayid']) : null)
						->setWorkingDayStatus ($row ['working_day_status']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($workingDaysMaster)) ? $workingDaysMaster : null;
		}
		
		/**
		 * @param WorkingDayMaster $workingDay
		 *
		 * @throws Exception
		 */
		public function saveWorkingDayMaster ($workingDay) {
			if (!$workingDay instanceof WorkingDayMaster) {
				throw new Exception ('Se ha presentado un error, por favor intente mas tarde');
			}
			
			if (empty ($workingDay->getId ())) {
				$this->adb->pquery (
					'INSERT INTO vtiger_working_days_master (working_day_name, description, regular_working_hours, morning_start_time, morning_due_time, afternoon_start_time, afternoon_due_time, working_day_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
					array ($workingDay->getWorkingDayName (), $workingDay->getDescription (), $workingDay->getRegularWorkingHours (), $workingDay->getMorningStartTime (), $workingDay->getMorningDueTime (), $workingDay->getAfternoonStartTime (), $workingDay->getAfternoonDueTime (), $workingDay->getWorkingDayStatus ())
				);
				$workingDay->setId ($this->adb->getLastInsertID ('vtiger_working_days_master'));
			} else {
				$this->adb->pquery (
					'UPDATE vtiger_working_days_master SET working_day_name=?, description=?, regular_working_hours=?, morning_start_time=?, morning_due_time=?, afternoon_start_time=?, afternoon_due_time=?, working_day_status=? WHERE workingdayid=?',
					array ($workingDay->getWorkingDayName (), $workingDay->getDescription (), $workingDay->getRegularWorkingHours (), $workingDay->getMorningStartTime (), $workingDay->getMorningDueTime (), $workingDay->getAfternoonStartTime (), $workingDay->getAfternoonDueTime (), $workingDay->getWorkingDayStatus (), $workingDay->getId ())
				);
			}
			$this->saveWorkingDaysOfWeek ($workingDay);
		}
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return mixed|WorkingDayManager
		 */
		public static function getInstance (PearDatabase $adb) {
			if (self::$INSTANCES === null) {
				self::$INSTANCES = array ();
			}
			if (!isset (self::$INSTANCES [ $adb->dbName ])) {
				self::$INSTANCES [ $adb->dbName ] = new self ($adb);
			}
			return self::$INSTANCES [ $adb->dbName ];
		}
	}

<?php
	require_once ('include/platzilla/Data/WorkingDayManager.php');
	require_once ('include/platzilla/Managers/UserManager.php');
	
	abstract class WorkingDayUtils {
		
		
		const DAY_OF_WEEK = array ('Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado');
		
		/**
		 * @param PearDatabase $adb
		 * @param string$name
		 *
		 * @return boolean
		 */
		public static function checkWorkingDayName ($adb, $name) {
			$result = $adb->pquery (
				'SELECT workingdayid FROM vtiger_working_days_master WHERE working_day_name=?',
				array ($name)
			);
			if ($adb->num_rows ($result) > 0) {
				$found = true;
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($found)) ? $found : false;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return WorkingDayMaster[]|null
		 * @throws Exception
		 */
		public static function fetchWorkingDaysFromUser ($adb, $userId) {
			if (empty ($userId)) {
				return null;
			}
			$adb->query ("SET lc_time_names = 'es_ES'");
			$result = $adb->pquery (
				'SELECT
						workingdayid,
						working_day_status,
						DATE_FORMAT(dt_created,"%W %D %M %Y") AS date_create
					  FROM
					  	vtiger_users2working_days
					  WHERE userid=?
					  ORDER BY
					  	working_day_status,
					  	date_create
					  ASC',
				array ($userId)
			);
			if ($adb->num_rows ($result) > 0) {
				$wdm = WorkingDayManager::getInstance ($adb);
				while ($row = $adb->fetchByAssoc($result, -1, false)) {
					$workingType = $wdm->getWorkingDayById ($row ['workingdayid']);
					$workingType->dateCreated = $row ['date_create'];
					$workingType->status      = $row ['working_day_status'];
					$userWorkingDay []        = $workingType;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($userWorkingDay)) ? $userWorkingDay : null;
		}
		
		/**
		 * @param $adb
		 *
		 * @return array|null
		 */
		public static function getDaysOfWeek ($adb) {
			$result = $adb->query (
				'SELECT * FROM vtiger_globalpicklists_values WHERE `picklistname`= "sys_weekdays" ORDER BY picklistvalueid ASC'
			);
			if ($adb->num_rows ($result) > 0) {
				$dayOfWeek = array();
				while ($row = $adb->fetchByAssoc($result, -1, false)) {
					$dayOfWeek [] = $row;
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($dayOfWeek)) ? $dayOfWeek : null;
		}
		
		public static function getFirstDayWeek ($adb) {
			$result = $adb->query ('SELECT start_day_week FROM vtiger_organizationdetails WHERE 1 LIMIT 1');
			if ($adb->num_rows ($result) > 0) {
				$firstDayWeek =  strtolower ($adb->query_result ($result, 0, 'start_day_week'));
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($firstDayWeek)) ? $firstDayWeek : 'monday';
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return integer
		 * @throws Exception
		 */
		public static function getRegularWorkingHours ($adb, $userId) {
			if (empty ($userId)) {
				return 0;
			}
			$result = $adb->pquery (
				'SELECT
       				wdm.regular_working_hours
				FROM
					vtiger_working_days_master wdm
				INNER JOIN vtiger_users2working_days u2wd ON u2wd.workingdayid = wdm.workingdayid
				WHERE
					u2wd.userid= ? AND
					u2wd.working_day_status=?
				LIMIT 1',
				array ($userId, 'VALID')
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$regularWorkingHours = intval ($row ['regular_working_hours']);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($regularWorkingHours)) ? $regularWorkingHours : 0;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return null|WorkingDayMaster
		 * @throws Exception
		 */
		public static function getValidWorkingDay ($adb, $userId) {
			if (empty ($userId)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT workingdayid FROM vtiger_users2working_days WHERE userid=? AND working_day_status=?',
				array ($userId, 'VALID')
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc($result, -1, false);
				$userWorkingDay = WorkingDayManager::getInstance ($adb)->getWorkingDayById ($row['workingdayid'], false);
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset ($userWorkingDay)) ? $userWorkingDay : null;
		}
		
		/**
		 * @param string $startDate
		 * @param string $dueDate
		 *
		 * @return integer
		 */
		public static function getWorkingDays ($startDate, $dueDate) {
			$begin = strtotime($startDate);
			$end   = strtotime($dueDate);
			if ($begin > $end) {
				return 0;
			} else {
				$numDays  = 0;
				while ($begin <= $end) {
					$whatDay = date("N", $begin);
					if (!in_array ($whatDay, array (6, 7))) {
						$numDays++;
					}
					$begin += 86400;
				};
				return $numDays;
			}
		}
		
		/**
		 * @param WorkingDayMaster $workingDay
		 * @return null|integer
		 */
		public static function getWorkingHoursToday ($workingDay) {
			if (empty ($workingDay->getWorkingDaysOfWeek ())) {
				return null;
			}
			$today = intval (date ('w'));
			foreach ($workingDay->getWorkingDaysOfWeek () as $daysOfWeek) {
				if ($daysOfWeek->getWorkingDayName () == self::DAY_OF_WEEK [$today]) {
					$totalHours = $daysOfWeek->getWorkingHours ();
					break;
				}
			}
			return (isset ($totalHours)) ? intval ($totalHours) : intval ($workingDay->getRegularWorkingHours ());
		}
		
		/**
		 * @param string $day
		 * @return integer
		 */
		public static function getDayOfWeek ($day) {
		    $days      = array ('SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY');
		    $numberDay = array_search($day, $days);
		    return $numberDay !== false ? $numberDay : 1;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 * @param integer $workingDayId
		 *
		 * @throws Exception
		 */
		public static function setWorkingDayToUser ($adb, $userId, $workingDayId) {
			if (empty ($userId)) {
				throw new Exception ('Usuario no identificado!');
			}
			
			$result = $adb->pquery (
				'SELECT workingdayid FROM vtiger_users2working_days WHERE userid=? AND workingdayid=? AND working_day_status=?',
				array ($userId, $workingDayId, 'VALID')
			);
			if ($adb->num_rows ($result) > 0) {
				throw new Exception ('Ha seleccionado la jornada laboral actual vigente!');
			}
			
			$adb->pquery ( 'UPDATE vtiger_users2working_days SET working_day_status=? WHERE userid=?', array ('EXPIRED', $userId));
			
			$adb->pquery (
				'INSERT INTO vtiger_users2working_days (userid, workingdayid, working_day_status) VALUES (?, ?, ?)',
				array ($userId, $workingDayId, 'VALID')
			);
		}
		
	}

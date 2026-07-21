<?php
	require_once ('include/platzilla/Utils/DatabaseUtils.php');
	require_once ('include/utils/AdbManager.class.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	
	abstract class DailyReportUtils {
		
		/**
		 * @param PearDatabase $adb
		 *
		 * @return string|null
		 * @throws  Exception
		 */
		private static function getTableInfo ($adb, $fieldName) {
			if (empty($fieldName)) {
				return null;
			}
			$result = $adb->query ("SELECT tablename FROM vtiger_field WHERE fieldname = '{$fieldName}'");
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$tableName = $row['tablename'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($tableName)) ? $tableName : null;
		}
		
		/***
		 * @param PearDatabase $adb
		 * @param string $date
		 * @param integer $userId
		 *
		 * @return null
		 */
		public static function  checkDailyReportByDate ($adb, $date , $userId) {
			$result = $adb->pquery (
				'SELECT
					dr.daily_reportid,
					dr.daily_report_status
				  FROM
					vtiger_daily_report dr
				  INNER JOIN vtiger_crmentity crm ON crm.crmid = dr.daily_reportid AND crm.deleted = 0
				  WHERE
				  	dr.daily_report_date=? AND
				  	crm.smcreatorid=?
				  ORDER BY
    				crm.createdtime
				  LIMIT 1',
				array ($date, $userId)
			);
			if ($adb->num_rows ($result) > 0) {
				$row = $adb->fetchByAssoc ($result, -1, false);
				$reportData ['crmid']  = $row['daily_reportid'];
				$reportData ['status'] = $row['daily_report_status'];
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($reportData)) ? $reportData : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array|null $period
		 * @param array|null $users
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchAchievements ($adb, $period, $users) {
			$rangeInit  = date ('Y-m-d');
			$rangeEnd   = date ('Y-m-d');
			$whereUsers = '';
			if (count($users)) {
				$whereUsers = "crm.smcreatorid IN{$adb->sql_expr_datalist ($users)} AND";
			}
			if (count ($period)) {
				$rangeInit = $period ['startdate'];
				$rangeEnd  = $period ['enddate'];
			}
			
			$result = $adb->query (
				"SELECT
					  ft.*
				  	  FROM
					  	vtiger_daily_report_achievements ft
					  INNER JOIN vtiger_daily_report dr ON dr.daily_reportid = ft.daily_reporttfid
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = ft.daily_reporttfid AND crm.deleted = 0
					  WHERE
				    	{$whereUsers}
				   		(DATE(dr.daily_report_date) BETWEEN '{$rangeInit}' AND '{$rangeEnd}')"
			);
			
			if ($adb->num_rows ($result) > 0) {
				$infoResult = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$infoResult[] = array(
						'title'       => $row ['achievement_name'],
						'description' => $row ['achievement_description'],
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($infoResult)) ? $infoResult : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array|null $period
		 * @param array|null $users
		 * @param string|array|null $typeInformation
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchAdditionalInformation ($adb, $period, $users, $typeInformation = null) {
			if (empty ($typeInformation)) {
				$otherInformation = "'Problema'";
			} else if (is_array ($typeInformation)) {
				$otherInformation = implode ("','", $typeInformation);
				$otherInformation =  "'{$otherInformation}'";
			} else {
				$otherInformation = $typeInformation;
			}
			$rangeInit  = date ('Y-m-d');
			$rangeEnd   = date ('Y-m-d');
			$whereUsers = '';
			if (count($users)) {
				$whereUsers = "crm.smcreatorid IN{$adb->sql_expr_datalist ($users)} AND";
			}
			if (count ($period)) {
				$rangeInit = $period ['startdate'];
				$rangeEnd  = $period ['enddate'];
			}
			
			$result = $adb->query (
				"SELECT
					  ft.*
				  	  FROM
					  	vtiger_daily_report_other_info ft
					  INNER JOIN vtiger_daily_report dr ON dr.daily_reportid = ft.daily_reporttfid
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = ft.daily_reporttfid AND crm.deleted = 0
					  WHERE
				    	{$whereUsers}
				    	ft.other_info_type NOT IN({$otherInformation}) AND
				   		(DATE(dr.daily_report_date) BETWEEN '{$rangeInit}' AND '{$rangeEnd}')
					 GROUP BY ft.other_info_type"
			);
			
			if ($adb->num_rows ($result) > 0) {
				$infoResult = array ();
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					$infoResult[ $row['other_info_type'] ][] = array(
						'title'       => $row['other_info_title'],
						'description' => $row ['other_info_description'],
					);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($infoResult)) ? $infoResult : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param integer $userId
		 *
		 * @return array|null
		 * @throws Exception
		 */
		public static function fetchDailyReportDateByUser ($adb, $userId) {
			if (empty($userId)) {
				return null;
			}
			$result = $adb->pquery (
				'SELECT
					DATE_FORMAT(dr.daily_report_date,"%m-%d-%Y") as dt_create
				  FROM
					vtiger_daily_report dr
				  INNER JOIN vtiger_crmentity crm ON crm.crmid = dr.daily_reportid AND crm.deleted = 0
				  WHERE
				  	crm.smcreatorid=? AND
				  	MONTH(dr.daily_report_date) BETWEEN MONTH(CURDATE() - INTERVAL 3 MONTH) AND (MONTH(CURDATE()) + 1)
				  ORDER BY
    				dr.daily_report_date ASC ',
				array ($userId)
			);
			
			if ($adb->num_rows ($result) > 0) {
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (empty ($row['dt_create'])) {
						continue;
					}
					$reportDate []  = $row['dt_create'];
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($reportDate)) ? $reportDate : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array|null $period
		 * @param array $users
		 *
		 * @return float|null
		 * @throws Exception
		 */
		public static function getActivityReportTotalTime ($adb, $period, $users) {
			$rangeInit  = date ('Y-m-d');
			$rangeEnd   = date ('Y-m-d');
			$whereUsers = '';
			if (count ($users)) {
				$whereUsers = "crm.smcreatorid IN{$adb->sql_expr_datalist ($users)} AND";
			}
			if (count ($period)) {
				$rangeInit = $period ['startdate'];
				$rangeEnd  = $period ['enddate'];
			}
			$result = $adb->query (
				"SELECT
					  IFNULL(SUM(ar.duration_time),0) AS hrs
				  	  FROM
					  	vtiger_activity_report ar
					  INNER JOIN vtiger_seactivityrel sa ON sa.activityid = ar.activityid
					  INNER JOIN vtiger_activity task ON task.activityid = ar.activityid
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = sa.crmid AND crm.deleted = 0
					  WHERE
					  	ar.deleted = 0 AND
					  	/*task.show_in_matrix = 'YES' AND */
				    	{$whereUsers}
				   		taskToMatrix(task.date_start, task.due_date, task.eventstatus, crm.createdtime, '{$rangeInit}', '{$rangeEnd}') = 1
					 GROUP BY ar.activityreportid"
			);
			
			if ($adb->num_rows ($result) > 0) {
				$totalHours = 0;
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (empty($row['hrs'])) {
						continue;
					}
					$totalHours += floatval ($row['hrs']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($totalHours)) ? $totalHours : null;
		}
		
		/**
		 * @param PearDatabase $adb
		 * @param array $period
		 * @param array $users
		 *
		 * @return float|int|null
		 * @throws Exception
		 */
		public static function getTotalHoursWorked ($adb, $period, $users) {
			$rangeInit  = date ('Y-m-d');
			$rangeEnd   = date ('Y-m-d');
			$whereUsers = '';
			if (count($users)) {
				$whereUsers = "crm.smcreatorid IN{$adb->sql_expr_datalist ($users)} AND";
			}
			if (count ($period)) {
				$rangeInit = $period ['startdate'];
				$rangeEnd  = $period ['enddate'];
			}
			
			$result = $adb->query (
				"SELECT
					  IFNULL(SUM(dr.workday_size),0) AS hrs
				  	  FROM
					  	vtiger_daily_report dr
					  INNER JOIN vtiger_crmentity crm ON crm.crmid = dr.daily_reportid AND crm.deleted = 0
					  WHERE
				    	{$whereUsers}
				   		(DATE(dr.daily_report_date) BETWEEN '{$rangeInit}' AND '{$rangeEnd}')"
			);
			
			if ($adb->num_rows ($result) > 0) {
				$totalHours = 0;
				while ($row = $adb->fetchByAssoc ($result, -1, false)) {
					if (empty($row['hrs'])) {
						continue;
					}
					$totalHours +=  floatval ($row['hrs']);
				}
			}
			DatabaseUtils::closeResult ($result);
			$result = null;
			return (isset($totalHours)) ? $totalHours : null;
		}
		
		public static function getOvertimeWorked ($adb, $period, $users) {
			if (!count ($users)) {
				return 0;
			}
			$rangeInit  = date ('Y-m-d');
			$rangeEnd   = date ('Y-m-d');
			if (count ($period)) {
				$rangeInit = $period ['startdate'];
				$rangeEnd  = $period ['enddate'];
			}
			$totalOverTime = 0;
			foreach ($users as $userId) {
				$overTime = 0;
				$result = $adb->query (
					"SELECT
						SUM(dr.workday_size) AS hrs,
       					SUM(dr.total_hours_reported) AS hours_reported
					FROM
						vtiger_daily_report dr
					INNER JOIN vtiger_crmentity crm ON crm.crmid = dr.daily_reportid AND crm.deleted = 0
					WHERE
					    crm.smcreatorid = {$userId} AND
						(DATE(dr.daily_report_date) BETWEEN '{$rangeInit}' AND '{$rangeEnd}')"
				);
				if ($adb->num_rows ($result) > 0) {
					$row = $adb->fetchByAssoc ($result, -1, false);
					$overTime = floatval ($row['hrs']) - floatval ($row['hours_reported']);
					if ($overTime < 0) {
						$overTime = (-1 * $totalOverTime);
					} else {
						$overTime = 0;
					}
				}
				$totalOverTime += $overTime;
				DatabaseUtils::closeResult ($result);
				$result = null;
			}
			return $totalOverTime;
		}
	}

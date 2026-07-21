<?php
	/*+********************************************************************************
	 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
	 * ("License"); You may not use this file except in compliance with the License
	 * The Original Code is:  vtiger CRM Open Source
	 * The Initial Developer of the Original Code is vtiger.
	 * Portions created by vtiger are Copyright (C) vtiger.
	 * All Rights Reserved.
	 *******************************************************************************/

	/**
	 * this file will contain the utility functions for Home module
	 */

	/**
	 * function to get upcoming activities for today
	 *
	 * @param integer $maxval - the maximum number of records to display
	 * @param integer $calCnt - returns the count query if this is set
	 * return array    $values   - activities record in array format
	 */
	function homepage_getUpcomingActivities ($maxval, $calCnt) {
		require_once ('data/Tracker.php');
		require_once ('include/utils/utils.php');

		global $adb;
		global $current_user;

		$dbStartDateTime   = new DateTimeField(date ('Y-m-d H:i:s'));
		$userStartDate     = $dbStartDateTime->getDisplayDate ();
		$userStartDateTime = new DateTimeField($userStartDate . ' 00:00:00');
		$startDateTime     = $userStartDateTime->getDBInsertDateTimeValue ();

		$userEndDateTime = new DateTimeField($userStartDate . ' 23:59:00');
		$endDateTime     = $userEndDateTime->getDBInsertDateTimeValue ();

		$upcoming_condition = " AND (CAST((CONCAT(date_start,' ',time_start)) AS DATETIME) BETWEEN '$startDateTime' AND '$endDateTime'
									OR CAST((CONCAT(vtiger_recurringevents.recurringdate,' ',time_start)) AS DATETIME) BETWEEN '$startDateTime' AND '$endDateTime')";

		$list_query = " select vtiger_crmentity.crmid,vtiger_crmentity.smownerid," .
					  "vtiger_crmentity.setype, vtiger_recurringevents.recurringdate, vtiger_activity.* " .
					  "from vtiger_activity inner join vtiger_crmentity on vtiger_crmentity.crmid=" .
					  "vtiger_activity.activityid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = " .
					  "vtiger_crmentity.smownerid left outer join vtiger_recurringevents on " .
					  "vtiger_recurringevents.activityid=vtiger_activity.activityid";
		$list_query .= getNonAdminAccessControlQuery ('Calendar', $current_user);
		$list_query .= "WHERE vtiger_crmentity.deleted=0 and vtiger_activity.activitytype not in " .
					   "('Emails') AND ( vtiger_activity.status is NULL OR vtiger_activity.status not in " .
					   "('Completed','Deferred')) and  (  vtiger_activity.eventstatus is NULL OR " .
					   "vtiger_activity.eventstatus not in ('Held','Not Held') )" . $upcoming_condition;

		$list_query .= " GROUP BY vtiger_activity.activityid";
		$list_query .= " ORDER BY date_start,time_start ASC";
		$list_query .= " limit $maxval";

		$res         = $adb->query ($list_query);
		$noofrecords = $adb->num_rows ($res);
		if ($calCnt == 'calculateCnt') {
			return $noofrecords;
		}

		$open_activity_list = array ();
		if ($noofrecords > 0) {
			for ($i = 0; $i < $noofrecords; $i++) {
				$dateValue          = $adb->query_result ($res, $i, 'date_start') . ' ' .
									  $adb->query_result ($res, $i, 'time_start');
				$endDateValue       = $adb->query_result ($res, $i, 'due_date') . ' ' .
									  $adb->query_result ($res, $i, 'time_end');
				$recurringDateValue = $adb->query_result ($res, $i, 'due_date') . ' ' .
									  $adb->query_result ($res, $i, 'time_start');
				$date               = new DateTimeField($dateValue);
				$endDate            = new DateTimeField($endDateValue);
				$recurringDate      = new DateTimeField($recurringDateValue);

				$open_activity_list[] = array (
					'name'          => $adb->query_result ($res, $i, 'subject'),
					'id'            => $adb->query_result ($res, $i, 'activityid'),
					'type'          => $adb->query_result ($res, $i, 'activitytype'),
					'module'        => $adb->query_result ($res, $i, 'setype'),
					'date_start'    => $date->getDisplayDate (),
					'due_date'      => $endDate->getDisplayDate (),
					'recurringdate' => $recurringDate->getDisplayDate (),
					'priority'      => $adb->query_result ($res, $i, 'priority'),
				);
			}
		}
		$values               = getActivityEntries ($open_activity_list);
		$values['ModuleName'] = 'Calendar';
		$values['search_qry'] = "&action=ListView&from_homepage=upcoming_activities";

		return $values;
	}

	function homepage_getTrialDataActivities ($maxval, $calCnt) {
		require_once ("data/Tracker.php");
		require_once ('include/utils/utils.php');

		global $adb;
		global $current_user;

		/*

		$dbStartDateTime = new DateTimeField(date('Y-m-d H:i:s'));
		$userStartDate = $dbStartDateTime->getDisplayDate();
		$userStartDateTime = new DateTimeField($userStartDate.' 00:00:00');
		$startDateTime = $userStartDateTime->getDBInsertDateTimeValue();

		$userEndDateTime = new DateTimeField($userStartDate.' 23:59:00');
		$endDateTime = $userEndDateTime->getDBInsertDateTimeValue();

		$upcoming_condition = " AND (CAST((CONCAT(date_start,' ',time_start)) AS DATETIME) BETWEEN '$startDateTime' AND '$endDateTime'
										OR CAST((CONCAT(vtiger_recurringevents.recurringdate,' ',time_start)) AS DATETIME) BETWEEN '$startDateTime' AND '$endDateTime')";

	*/
		$condition = " AND crmid < 14 ";

		/*
			$list_query = " select vtiger_crmentity.crmid,vtiger_crmentity.smownerid,".
				"vtiger_crmentity.setype, vtiger_recurringevents.recurringdate, vtiger_activity.* ".
				"from vtiger_activity inner join vtiger_crmentity on vtiger_crmentity.crmid=".
				"vtiger_activity.activityid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = ".
				"vtiger_crmentity.smownerid left outer join vtiger_recurringevents on ".
				"vtiger_recurringevents.activityid=vtiger_activity.activityid";
			$list_query .= getNonAdminAccessControlQuery('Calendar',$current_user);
			$list_query .= "WHERE vtiger_crmentity.deleted=0 and vtiger_activity.activitytype not in ".
			"('Emails') AND ( vtiger_activity.status is NULL OR vtiger_activity.status not in ".
			"('Completed','Deferred')) and  (  vtiger_activity.eventstatus is NULL OR ".
			"vtiger_activity.eventstatus not in ('Held','Not Held') )".$upcoming_condition;

		*/

		$list_query = " select vtiger_crmentity.crmid,vtiger_crmentity.smownerid," .
					  "vtiger_crmentity.setype, vtiger_recurringevents.recurringdate, vtiger_activity.* " .
					  "from vtiger_activity inner join vtiger_crmentity on vtiger_crmentity.crmid=" .
					  "vtiger_activity.activityid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = " .
					  "vtiger_crmentity.smownerid left outer join vtiger_recurringevents on " .
					  "vtiger_recurringevents.activityid=vtiger_activity.activityid AND ( vtiger_activity.status is NULL OR vtiger_activity.status not in " .
					  "('Completed','Deferred')) and  (  vtiger_activity.eventstatus is NULL OR " .
					  "vtiger_activity.eventstatus not in ('Held','Not Held') )";
		//$list_query .= getNonAdminAccessControlQuery('Calendar',$current_user);
		$list_query .= " WHERE vtiger_crmentity.deleted=0 and vtiger_activity.activitytype not in " .
					   "('Emails') " . $condition;

		$list_query .= " GROUP BY vtiger_activity.activityid";
		$list_query .= " ORDER BY date_start,time_start ASC";
		//$list_query.= " limit $maxval";
		$list_query .= " limit 10";

		$res         = $adb->query ($list_query);
		$noofrecords = $adb->num_rows ($res);
		if ($calCnt == 'calculateCnt') {
			return $noofrecords;
		}

		$open_activity_list = array ();
		if ($noofrecords > 0) {
			for ($i = 0; $i < $noofrecords; $i++) {
				$dateValue          = $adb->query_result ($res, $i, 'date_start') . ' ' .
									  $adb->query_result ($res, $i, 'time_start');
				$endDateValue       = $adb->query_result ($res, $i, 'due_date') . ' ' .
									  $adb->query_result ($res, $i, 'time_end');
				$recurringDateValue = $adb->query_result ($res, $i, 'due_date') . ' ' .
									  $adb->query_result ($res, $i, 'time_start');
				$date               = new DateTimeField($dateValue);
				$endDate            = new DateTimeField($endDateValue);
				$recurringDate      = new DateTimeField($recurringDateValue);

				$open_activity_list[] = array (
					'name'          => $adb->query_result ($res, $i, 'subject'),
					'id'            => $adb->query_result ($res, $i, 'activityid'),
					'type'          => $adb->query_result ($res, $i, 'activitytype'),
					'module'        => $adb->query_result ($res, $i, 'setype'),
					'date_start'    => $date->getDisplayDate (),
					'due_date'      => $endDate->getDisplayDate (),
					'recurringdate' => $recurringDate->getDisplayDate (),
					'priority'      => $adb->query_result ($res, $i, 'priority'),
				);
			}
		}
		$values               = getActivityEntries ($open_activity_list);
		$values['ModuleName'] = 'Calendar';
		$values['search_qry'] = "&action=ListView&from_homepage=upcoming_activities";

		return $values;
	}

	/**
	 * this function returns the activity entries in array format
	 * it takes in an array containing activity details as a parameter
	 *
	 * @param array $open_activity_list - the array containing activity details
	 * return array $values - activities record in array format
	 */
	function getActivityEntries ($open_activity_list) {
		global $current_language, $app_strings;
		$current_module_strings = return_module_language ($current_language, 'Calendar');
		if (!empty($open_activity_list)) {
			$header   = array ();
			$header[] = $current_module_strings['LBL_LIST_SUBJECT'];
			$header[] = $current_module_strings['Type'];

			$entries = array ();
			foreach ($open_activity_list as $event) {
				$recur_date = preg_replace ('/--/', '', $event['recurringdate']);
				if ($recur_date != "") {
					$event['date_start'] = $event['recurringdate'];
				}
				$font_color_high   = "#000000"; //"color:#00DD00;";
				$font_color_medium = "#000000"; //"color:#DD00DD;";

				switch ($event['priority']) {
					case 'High':
						$font_color = $font_color_high;
						break;
					case 'Medium':
						$font_color = $font_color_medium;
						break;
					default:
						$font_color = '';
				}

				if ($event['type'] != 'Task' && $event['type'] != 'Emails' && $event['type'] != '') {
					$activity_type = 'Events';
				} else {
					$activity_type = 'Task';
				}

				$entries[ $event['id'] ] = array (
					'0' => '<a href="index.php?action=DetailView&module=' . $event["module"] . '&activity_mode=' . $activity_type . '&record=' . $event["id"] . '" style="' . $font_color . ';">' . $event["name"] . '</a>',
					'1' => $event["type"],
				);
			}
			$values = array ('noofactivities' => count ($open_activity_list), 'Header' => $header, 'Entries' => $entries);
		} else {
			$values = array (
				'noofactivities' => count ($open_activity_list), 'Entries' =>
					'<div class="componentName">' . $app_strings['LBL_NO_DATA'] . '</div>',
			);
		}
		return $values;
	}

	/** Modificado por Johana Romero
	 * Pedido [TT11193] Adecuación Página Resumen de la aplicación - Platzilla
	 * function to get pending activities for today
	 *
	 * @param integer $maxval - the maximum number of records to display
	 * @param integer $calCnt - returns the count query if this is set
	 * return array    $values   - activities record in array format
	 */
	function homepage_getPendingActivities ($maxval, $calCnt) {
		require_once ("data/Tracker.php");
		require_once ("include/utils/utils.php");
		require_once ('include/utils/CommonUtils.php');

		global $adb;
		global $current_user;

		$userStartDate = date ('Y-m-d');
		$startDateTime = $userStartDate . ' 00:00:00';
		$endDateTime   = $userStartDate . ' 23:59:00';

		$pending_condition = " OR ((CAST((CONCAT(due_date,' ',time_end)) AS DATETIME) >= '$startDateTime' AND CAST((CONCAT(due_date,' ',time_end)) AS DATETIME) <= '$endDateTime') OR (CAST((CONCAT(vtiger_recurringevents.recurringdate,' ',time_end)) AS DATETIME) >= '$startDateTime' AND CAST((CONCAT(vtiger_recurringevents.recurringdate,' ',time_end)) AS DATETIME) <= '$endDateTime'))";

		$list_query = "select vtiger_crmentity.crmid,vtiger_crmentity.smownerid,vtiger_crmentity." .
					  "setype, vtiger_recurringevents.recurringdate, vtiger_activity.* from vtiger_activity " .
					  "inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid LEFT " .
					  "JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid left outer join " .
					  "vtiger_recurringevents on vtiger_recurringevents.activityid=vtiger_activity.activityid";
		$list_query .= getNonAdminAccessControlQuery ('Calendar', $current_user);
		$list_query .= "WHERE vtiger_crmentity.deleted=0 and (vtiger_activity.activitytype not in " .
					   "('Emails')) AND (vtiger_activity.status is NULL OR vtiger_activity.status not in " .
					   "('Completed','Deferred')) and (vtiger_activity.eventstatus is NULL OR  vtiger_activity." .
					   "eventstatus = 'Not Held') " . $pending_condition;

		$list_query .= " GROUP BY vtiger_activity.activityid";
		$list_query .= " ORDER BY date_start,time_start ASC";
		//$list_query.= " limit $maxval";
		//$list_query.= " limit 10";

		$res         = $adb->query ($list_query);
		$noofrecords = $adb->num_rows ($res);
		if ($calCnt == 'calculateCnt') {
			return $noofrecords;
		}

		$open_activity_list = array ();
		$noofrows           = $adb->num_rows ($res);
		if (count ($res) > 0) {
			for ($i = 0; $i < $noofrows; $i++) {
				$dateValue          = $adb->query_result ($res, $i, 'date_start') . ' ' .
									  $adb->query_result ($res, $i, 'time_start');
				$endDateValue       = $adb->query_result ($res, $i, 'due_date') . ' ' .
									  $adb->query_result ($res, $i, 'time_end');
				$recurringDateValue = $adb->query_result ($res, $i, 'due_date') . ' ' .
									  $adb->query_result ($res, $i, 'time_start');
				$date               = new DateTimeField($dateValue);
				$endDate            = new DateTimeField($endDateValue);
				$recurringDate      = new DateTimeField($recurringDateValue);

				$open_activity_list[] = array (
					'name'          => $adb->query_result ($res, $i, 'subject'),
					'id'            => $adb->query_result ($res, $i, 'activityid'),
					'type'          => $adb->query_result ($res, $i, 'activitytype'),
					'module'        => $adb->query_result ($res, $i, 'setype'),
					'date_start'    => $date->getDisplayDate (),
					'due_date'      => $endDate->getDisplayDate (),
					'recurringdate' => $recurringDate->getDisplayDate (),
					'priority'      => $adb->query_result ($res, $i, 'priority'),
				);
			}
		}

		$values               = getActivityEntries ($open_activity_list);
		$values['ModuleName'] = 'Calendar';
		$values['search_qry'] = "&action=ListView&from_homepage=pending_activities";

		return $values;
	}

	/** Hecho por Johana Romero
	 * Pedido [TT11193] Adecuación Página Resumen de la aplicación - Platzilla
	 * function to get expired activities
	 *
	 * @param integer $maxval - the maximum number of records to display
	 * @param integer $calCnt - returns the count query if this is set
	 * return array    $values   - activities record in array format
	 */
	function homepage_getExpiredActivities ($maxval, $calCnt) {
		require_once ("data/Tracker.php");
		require_once ("include/utils/utils.php");
		require_once ('include/utils/CommonUtils.php');

		global $adb;
		global $current_user;

		$userStartDate = date ('Y-m-d');
		$endDateTime   = $userStartDate . ' 00:00:00';
		//$endDateTime = $userStartDate.' 23:59:00';

		$pending_condition = " AND (CAST((CONCAT(due_date,' ',time_end)) AS DATETIME) < '$endDateTime' OR CAST((CONCAT(vtiger_recurringevents.recurringdate,' ',time_end)) AS DATETIME) < '$endDateTime')";

		$list_query = "select vtiger_crmentity.crmid,vtiger_crmentity.smownerid,vtiger_crmentity." .
					  "setype, vtiger_recurringevents.recurringdate, vtiger_activity.* from vtiger_activity " .
					  "inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid LEFT " .
					  "JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid left outer join " .
					  "vtiger_recurringevents on vtiger_recurringevents.activityid=vtiger_activity.activityid";
		$list_query .= getNonAdminAccessControlQuery ('Calendar', $current_user);
		$list_query .= "WHERE vtiger_crmentity.deleted=0 and (vtiger_activity.activitytype not in " .
					   "('Emails')) AND (vtiger_activity.status is NULL OR vtiger_activity.status not in " .
					   "('Completed','Deferred')) and (vtiger_activity.eventstatus is NULL OR  vtiger_activity." .
					   "eventstatus not in ('Held','Not Held')) " . $pending_condition;

		$list_query .= " GROUP BY vtiger_activity.activityid";
		$list_query .= " ORDER BY date_start,time_start ASC";
		//$list_query.= " limit $maxval";
		//$list_query.= " limit 10";

		$res         = $adb->query ($list_query);
		$noofrecords = $adb->num_rows ($res);
		if ($calCnt == 'calculateCnt') {
			return $noofrecords;
		}

		$open_activity_list = array ();
		$noofrows           = $adb->num_rows ($res);
		if (count ($res) > 0) {
			for ($i = 0; $i < $noofrows; $i++) {
				$dateValue          = $adb->query_result ($res, $i, 'date_start') . ' ' .
									  $adb->query_result ($res, $i, 'time_start');
				$endDateValue       = $adb->query_result ($res, $i, 'due_date') . ' ' .
									  $adb->query_result ($res, $i, 'time_end');
				$recurringDateValue = $adb->query_result ($res, $i, 'due_date') . ' ' .
									  $adb->query_result ($res, $i, 'time_start');
				$date               = new DateTimeField($dateValue);
				$endDate            = new DateTimeField($endDateValue);
				$recurringDate      = new DateTimeField($recurringDateValue);

				$open_activity_list[] = array (
					'name'          => $adb->query_result ($res, $i, 'subject'),
					'id'            => $adb->query_result ($res, $i, 'activityid'),
					'type'          => $adb->query_result ($res, $i, 'activitytype'),
					'module'        => $adb->query_result ($res, $i, 'setype'),
					'date_start'    => $date->getDisplayDate (),
					'due_date'      => $endDate->getDisplayDate (),
					'recurringdate' => $recurringDate->getDisplayDate (),
					'priority'      => $adb->query_result ($res, $i, 'priority'),
				);
			}
		}

		$values               = getActivityEntries ($open_activity_list);
		$values['ModuleName'] = 'Calendar';
		$values['search_qry'] = "&action=ListView&from_homepage=pending_activities";

		return $values;
	}

	/**
	 * this function returns the number of columns in the home page for the current user.
	 * if nothing is found in the database it returns 4 by default
	 * return integer $data - the number of columns
	 */
	function getNumberOfColumns () {
		global $current_user, $adb;

		$sql    = "SELECT * FROM vtiger_home_layout WHERE userid=?";
		$result = $adb->pquery ($sql, array ($current_user->id));

		if ($adb->num_rows ($result) > 0) {
			$data = $adb->query_result ($result, 0, "layout");
		} else {
			$data = 4;    //default is 4 column layout for now
		}
		return $data;
	}

	/** Modificado por Johana Romero
	 * Pedido [TT11193] Adecuación Página Resumen de la aplicación - Platzilla
	 * Obtiene las aplicaciones que posee el usuario
	 */
	function getAplicaciones () {
		require_once ('include/platzilla/Objects/ApplicationInterface.php');
		require_once ('include/platzilla/Objects/ApplicationSubscriptionInterface.php');
		global $platPrincipal, $adb, $current_user;

		//Conectando instancia principal.
		$adbPrincipal = conectaPlataformaHija ($platPrincipal);

		$sqlemail    = "SELECT email1 FROM vtiger_users WHERE id=" . $current_user->id;
		$resultemail = $adb->query ($sqlemail);
		$email       = $resultemail->fields[0];

		$result       = $adbPrincipal->pquery (
			'SELECT
				*
			FROM
				vtiger_instanceapplications ia
				INNER JOIN vtiger_config_applications ca ON ca.app_code=ia.applicationcode
				INNER JOIN vtiger_instances i ON i.code=ia.instancecode
			WHERE
				ca.app_status=? AND
				ia.status IN (?, ?) AND
				i.administrator=?',
			array (ApplicationInterface::STATUS_ACTIVE, ApplicationSubscriptionInterface::STATUS_ACTIVE, ApplicationSubscriptionInterface::STATUS_SUBSCRIBED, $email)
		);
		$noofrecords  = $adbPrincipal->num_rows ($result);
		$aplicaciones = array ();

		if ($noofrecords > 0) {
			for ($i = 0; $i < $noofrecords; $i++) {
				$aplicaciones[] = array (
					'appid'           => $adbPrincipal->query_result ($result, $i, 'appid'),
					'app_code'        => $adbPrincipal->query_result ($result, $i, 'app_code'),
					'app_name'        => $adbPrincipal->query_result ($result, $i, 'app_name'),
					'app_descripcion' => $adbPrincipal->query_result ($result, $i, 'app_descripcion'),
					'instanciasid'    => $adbPrincipal->query_result ($result, $i, 'instanceid'),
					'instanciasname'  => $adbPrincipal->query_result ($result, $i, 'name'),
				);
			}
		}

		return $aplicaciones;
	}

	function getModulos ($filter, $page) {
		require_once ('include/platzilla/Objects/ApplicationInterface.php');
		global $platPrincipal, $adb, $current_user;

		//Conectando instancia principal.
		$adbPrincipal = conectaPlataformaHija ($platPrincipal);

		$sqlemail    = "SELECT email1 FROM vtiger_users WHERE id=" . $current_user->id;
		$resultemail = $adb->query ($sqlemail);
		$email       = $resultemail->fields[0];
		$max_rows    = 4;

		/* [TT11207] Ajustes Página Tours Platzilla - 11/07/16 - Johana Romero - Genera el LIMIT del sql */
		if (isset($page)) {
			if ($page == 1) {
				$start_from  = 0;
				$finish_from = $max_rows;
			} else {
				$start_from  = $page;
				$finish_from = ($page) + 4;
			}
			$sql_limit = " LIMIT " . $start_from . "," . $finish_from;
		} else {
			$sql_limit = '';
		}

		$result_modulos = $adbPrincipal->pquery (
			"SELECT
				t.tabid,
				t.name,
				t.tablabel,
				ca.config_applicationsid,
				en.tablename,
				en.entityidfield
			FROM
				vtiger_instanceapplications ia
				INNER JOIN vtiger_config_applications ca ON ca.app_code=ia.applicationcode
				INNER JOIN vtiger_instances i ON i.code=ia.instancecode
				INNER JOIN vtiger_configapps_tab cat ON cat.config_applicationsid=ca.config_applicationsid
				INNER JOIN vtiger_tab t ON cat.tabid = t.tabid
				INNER JOIN vtiger_entityname en ON t.tabid=en.tabid
			WHERE
				ca.app_status=? AND
				i.administrator=?
			{$sql_limit}",
			array (ApplicationInterface::STATUS_ACTIVE, $email)
		);
		$num_modulos    = $adbPrincipal->num_rows ($result_modulos);
		$modulos        = array ();

		switch ($filter) {
			case 'today':
				$userStartDate = date ('Y-m-d');
				$startDateTime = $userStartDate . ' 00:00:00';
				$endDateTime   = $userStartDate . ' 23:59:00';

				break;

			case 'yesterday':
				$yesterday     = date ("Y-m-d", mktime (0, 0, 0, date ("m"), date ("d") - 1, date ("Y")));
				$userStartDate = $yesterday;
				$startDateTime = $userStartDate . ' 00:00:00';
				$endDateTime   = $userStartDate . ' 23:59:00';

				break;

			case 'lastMonth':
				$lastmonth     = date ("Y-m-d", mktime (0, 0, 0, date ("m") - 1, date ("d"), date ("Y")));
				$userStartDate = $lastmonth;
				$startDateTime = $userStartDate . ' 00:00:00';
				$endDateTime   = date ('Y-m-d') . ' 23:59:00';

				break;

			case 'lastWeek':
				$lastweek      = date ("Y-m-d", mktime (0, 0, 0, date ("m"), date ("d") - 7, date ("Y")));
				$userStartDate = $lastweek;
				$startDateTime = $userStartDate . ' 00:00:00';
				$endDateTime   = date ('Y-m-d') . ' 23:59:00';

				break;
		}

		$sql_filter = "createdtime >= '$startDateTime' AND createdtime <= '$endDateTime'";

		if ($num_modulos > 0) {
			for ($i = 0; $i < $num_modulos; $i++) {
				$tablename = $adbPrincipal->query_result ($result_modulos, $i, 'tablename');
				$entityID  = $adbPrincipal->query_result ($result_modulos, $i, 'entityidfield');
				/* [TT11207] Ajustes Página Tours Platzilla - 08/07/16 - Johana Romero - Si no tiene ningun filtro, traer todos los modulos de las aplicaciones activas */
				if ($filter == '') {
					$sql = "SELECT count('" . $entityID . "') as cantidad FROM " . $tablename . " INNER JOIN vtiger_crmentity ON (" . $tablename . "." . $entityID . " = vtiger_crmentity.crmid)";
				} else {
					$sql = "SELECT count('" . $entityID . "') as cantidad FROM " . $tablename . " INNER JOIN vtiger_crmentity ON (" . $tablename . "." . $entityID . " = vtiger_crmentity.crmid) WHERE " . $sql_filter;
				}

				/* */

				if (existeTabla ($tablename)) {
					$result   = $adb->query ($sql);
					$cantidad = $adb->query_result ($result, 0, 'cantidad');
				} else {
					$cantidad = 0;
				}

				$modulos[] = array (
					'tabid'                 => $adbPrincipal->query_result ($result_modulos, $i, 'tabid'),
					'name'                  => $adbPrincipal->query_result ($result_modulos, $i, 'name'),
					'tablabel'              => $adbPrincipal->query_result ($result_modulos, $i, 'tablabel'),
					'config_applicationsid' => $adbPrincipal->query_result ($result_modulos, $i, 'config_applicationsid'),
					'tablename'             => $tablename,
					'entitiId'              => $entityID,
					'cantidad'              => $cantidad,
				);
			}
		}
		return $modulos;
	}

	/**
	 * @param PearDatabase $adb
	 * @param stdClass $currentUser
	 *
	 * @return string
	 */
	function getUserIdsByRoleWhereClause (PearDatabase $adb, $currentUser) {
		$userIds = array ();
		$result  = $adb->pquery (
			'SELECT
				u2r.userid
			FROM
				vtiger_user2role u2r
				LEFT JOIN vtiger_role r ON r.roleid=u2r.roleid
			WHERE
				r.parentrole LIKE ?',
			array ("{$currentUser->roleid}%")
		);
		if ($adb->num_rows ($result) == 0) {
			return " AND a.userid='{$currentUser->id}'";
		}

		while ($row = $adb->fetchByAssoc ($result, -1, false)) {
			if ((is_admin ($currentUser)) || ($row ['userid'] != 1)) {
				$userIds [] = $row ['userid'];
			}
		}
		if (!empty ($userIds)) {
			$dummy = join ("','", $userIds);
			return " AND a.userid IN ('{$dummy}')";
		} else {
			return " AND a.userid='{$currentUser->id}'";
		}
	}

	function getAllActivity (PearDatabase $adb, $currentUser, $startDateTime, $endDateTime) {
		$sqlUser     = getUserIdsByRoleWhereClause ($adb, $currentUser);
		$queryResult = $adb->query (
			"SELECT
				a.module,
				a.action,
				a.recordid,
				MAX(a.actiondate) AS action_date,
				t.tablabel,
	 			en.tablename,
	 			en.fieldname,
	 			en.entityidfield,
	 			usr.id,
	 			usr.last_name
	 		FROM
	 			vtiger_audit_trial a
	 			LEFT JOIN vtiger_tab t ON t.name=a.module
	 			LEFT JOIN vtiger_entityname en ON en.modulename=a.module
	 			LEFT JOIN vtiger_users AS usr ON a.userid=usr.id
	 		WHERE
	 			t.name NOT IN ('Tooltip', 'Home') AND
	 			a.action IN ('DetailView', 'Save', 'Delete', 'CalendarAjax', 'EditGraph', 'EditView') AND
	 			CAST(actiondate AS DATETIME)>='{$startDateTime}' AND
	 			CAST(actiondate as DATETIME)<='{$endDateTime}'
	 			{$sqlUser}
	 		GROUP BY
	 			action,
	 			recordid
	 		ORDER BY
	 			actiondate DESC"
		);
		$history     = array ();
		$noofrows    = $adb->num_rows ($queryResult);
		if ($noofrows > 0) {
			$row = $adb->fetchByAssoc ($queryResult);
			while ($row) {
				$userImagen = getUserImageName ($row['id']);
				if ($userImagen) {
					$row['imagename'] = $userImagen;
				} else {
					$row['imagename'] = '';
				}
				$campo = str_replace (',', ",' ',", $row['fieldname']);
				if (($campo != '') && (isset($row['tablename'])) && (isset($row['entityidfield'])) && (isset($row['recordid'])) && ($row['recordid'] != '')) {
					$entity      = "SELECT CONCAT({$campo}) c FROM {$row['tablename']} WHERE {$row['entityidfield']}={$row['recordid']} LIMIT 1";
					$queryEntity = $adb->query ($entity);
					if ($adb->num_rows ($queryEntity) > 0) {
						$row['label_entity'] = $adb->query_result ($queryEntity, 0, 'c');
						if ($row['action'] == 'DetailView') {
							$row['tipo'] = 'Nuevo';
						} else if ($row['action'] == 'Delete') {
							$row['tipo'] = 'Eliminado';
						} else {
							$row['tipo'] = 'Modificado';
						}
					} else {
						$row['label_entity'] = '';
						$row['tipo']         = 'Modificado';
					}
				} else {
					$row['label_entity'] = '';
					$row['tipo']         = 'Modificado';
				}
				$fileToChck = "./modules/{$row ['module']}/ListView.php";
				if (file_exists ($fileToChck)) {
					$row['url_module'] = "index.php?action=ListView&module={$row ['module']}&parenttab=";
				} else {
					$row['url_module'] = "index.php?module={$row ['module']}&action=index";
				}
				$history[] = $row;
				$row       = $adb->fetchByAssoc ($queryResult);
			}
		}
		return $history;
	}

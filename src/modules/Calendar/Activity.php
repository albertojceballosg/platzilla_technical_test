<?php
	/*********************************************************************************
	 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
	 * ("License"); You may not use this file except in compliance with the
	 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
	 * Software distributed under the License is distributed on an  "AS IS"  basis,
	 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
	 * the specific language governing rights and limitations under the License.
	 * The Original Code is:  SugarCRM Open Source
	 * The Initial Developer of the Original Code is SugarCRM, Inc.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________.
	 ********************************************************************************/
	/*********************************************************************************
	 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Activities/Activity.php,v 1.26 2005/03/26 10:42:13 rank Exp $
	 * Description:  TODO: To be written.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 ********************************************************************************/

	include_once ('config.php');
	require_once ('include/logging.php');
	require_once ('include/database/PearDatabase.php');
	require_once ('modules/Calendar/RenderRelatedListUI.php');
	require_once ('data/CRMEntity.php');
	require_once ('modules/Calendar/CalendarCommon.php');

	// Task is used to store customer information.
	class Activity extends CRMEntity {
		var $log;
		var $db;
		var $table_name     = "vtiger_activity";
		var $table_index    = 'activityid';
		var $reminder_table = 'vtiger_activity_reminder';
		var $tab_name       = Array ('vtiger_crmentity', 'vtiger_activity', 'vtiger_activitycf');

		var $tab_name_index = Array ('vtiger_crmentity' => 'crmid', 'vtiger_activity' => 'activityid', 'vtiger_seactivityrel' => 'activityid', 'vtiger_salesmanactivityrel' => 'activityid', 'vtiger_activity_reminder' => 'activity_id', 'vtiger_recurringevents' => 'activityid', 'vtiger_activitycf' => 'activityid');

		var $column_fields = Array ();
		var $sortby_fields = Array ('subject', 'due_date', 'date_start', 'smownerid', 'activitytype', 'lastname', 'status');    //Sorting is added for due date and start date

		// This is used to retrieve related vtiger_fields from form posts.
		var $additional_column_fields = Array ('assigned_user_name', 'assigned_user_id', 'contactname', 'contact_phone', 'contact_email', 'parent_name');

		/**
		 * Mandatory table for supporting custom fields.
		 */
		var $customFieldTable = Array ('vtiger_activitycf', 'activityid');

		// This is the list of vtiger_fields that are in the lists.
		var $list_fields = Array (
			'Subject'        => Array ('activity' => 'subject'),
			'Type'           => Array ('activity' => 'activitytype'),
			'Estado'         => Array ('activity' => 'eventstatus'),
			'Start Date'     => Array ('activity' => 'date_start'),
			'End Date'       => Array ('activity' => 'due_date'),
			'Assigned To'    => Array ('crmentity' => 'smownerid'),
		);

		var $range_fields = Array (
			'name',
			'date_modified',
			'start_date',
			'id',
			'status',
			'eventstatus',
			'date_due',
			'time_start',
			'description',
			'contact_name',
			'priority',
			'duehours',
			'dueminutes',
			'location',
		);

		var $list_fields_name = Array (
			'Close'             => 'status',
			'Type'              => 'activitytype',
			'Subject'           => 'subject',
			'Contact Name'      => 'lastname',
			'Related to'        => 'parent_id',
			'Start Date & Time' => 'date_start',
			'End Date & Time'   => 'due_date',
			'Recurring Type'    => 'recurringtype',
			'Assigned To'       => 'assigned_user_id',
			'Start Date'        => 'date_start',
			'Start Time'        => 'time_start',
			'End Date'          => 'due_date',
			'End Time'          => 'time_end',
			'Estado'            => 'eventstatus'
		);

		var $list_link_field = 'subject';

		//Added these variables which are used as default order by and sortorder in ListView
		var $default_order_by   = 'due_date';
		var $default_sort_order = 'ASC';

		//var $groupTable = Array('vtiger_activitygrouprelation','activityid');

		function Activity () {
			$this->log           = LoggerManager::getLogger ('Calendar');
			$this->db            = PearDatabase::getInstance ();
			$this->column_fields = getColumnFields ('Calendar');
		}

		function save_module ($module) {
			global $adb;
			//Handling module specific save
			//Insert into seactivity rel
			if (isset($this->column_fields['parent_id']) && $this->column_fields['parent_id'] != '') {
				$this->insertIntoEntityTable ("vtiger_seactivityrel", $module);
			} elseif ($this->column_fields['parent_id'] == '' && $insertion_mode == "edit") {
				$this->deleteRelation ("vtiger_seactivityrel");
			}
			$recur_type = '';
			if (($recur_type == "--None--" || $recur_type == '') && $this->mode == "edit") {
				$sql = 'DELETE  FROM vtiger_recurringevents WHERE activityid=?';
				$adb->pquery ($sql, array ($this->id));
			}
			//Handling for recurring type
			//Insert into vtiger_recurring event table
			if (isset($this->column_fields['recurringtype']) && $this->column_fields['recurringtype'] != '' && $this->column_fields['recurringtype'] != '--None--') {
				$recur_type = trim ($this->column_fields['recurringtype']);
				$recur_data = getrecurringObjValue ();
				if (is_object ($recur_data)) {
					$this->insertIntoRecurringTable ($recur_data);
				}
			}

			//Insert into vtiger_activity_remainder table

			$this->insertIntoReminderTable ('vtiger_activity_reminder', $module, "");

			//Handling for invitees
			$selected_users_string = $_REQUEST['inviteesid'];
			$invitees_array        = explode (';', $selected_users_string);
			$this->insertIntoInviteeTable ($module, $invitees_array);

			//Inserting into sales man activity rel
			$this->insertIntoSmActivityRel ($module);

			$this->insertIntoActivityReminderPopup ($module);

			// Recalcular fechas estimadas de los trabajos relacionados (orden_de_trabajo)
			$workIds = array ();

			// 1) Relación directa mediante related_to / related_id
			if (!empty ($this->column_fields['related_to']) && $this->column_fields['related_to'] === 'orden_de_trabajo' && !empty ($this->column_fields['related_id'])) {
				$workIds[] = (int)$this->column_fields['related_id'];
			}

			// 2) Relación mediante vtiger_seactivityrel
			if (!empty ($this->id)) {
				$result = $adb->pquery (
					'SELECT ce.crmid
					   FROM vtiger_seactivityrel sar
					   INNER JOIN vtiger_crmentity ce ON ce.crmid = sar.crmid AND ce.deleted = 0
					  WHERE sar.activityid = ? AND ce.setype = ?'
					,
					array ($this->id, 'orden_de_trabajo')
				);
				if ($adb->num_rows ($result) > 0) {
					while ($row = $adb->fetchByAssoc ($result, -1, false)) {
						$workIds[] = (int)$row['crmid'];
					}
				}
			}

			if (!empty ($workIds)) {
				require_once ('modules/orden_de_trabajo/handlers/taskToWork.class.php');
				require_once ('include/platzilla/Data/ActivityReportManager.php');
				
				$taskHandler = taskToWork::getInstance ($adb);
				$arm = ActivityReportManager::getInstance ($adb);
				$workIds = array_unique ($workIds);
				
				foreach ($workIds as $workId) {
					// Recalcular fechas estimadas
					$taskHandler->recalculateWorkEstimatedDatesFromDb ($workId);
					
					// Recalcular progreso del trabajo
					$calculatedProgress = $arm->calculateProgress ($workId);
					
					// Actualizar overall_progress_perc en la orden de trabajo
					$adb->pquery (
						'UPDATE vtiger_orden_de_trabajo SET overall_progress_perc = ? WHERE orden_de_trabajoid = ?',
						array ($calculatedProgress, $workId)
					);
				}
			}
		}

		/** Function to insert values in vtiger_activity_reminder_popup table for the specified module
		 *
		 * @param $cbmodule -- module:: Type varchar
		 */
		function insertIntoActivityReminderPopup ($cbmodule) {

			global $adb;

			$cbrecord = $this->id;
			unset($_SESSION['next_reminder_time']);
			if (isset($cbmodule) && isset($cbrecord)) {
				$cbdate = getValidDBInsertDateValue ($this->column_fields['date_start']);
				$cbtime = $this->column_fields['time_start'];

				$reminder_query  = "SELECT reminderid FROM vtiger_activity_reminder_popup WHERE semodule = ? AND recordid = ?";
				$reminder_params = array ($cbmodule, $cbrecord);
				$reminderidres   = $adb->pquery ($reminder_query, $reminder_params);

				$reminderid = null;
				if ($adb->num_rows ($reminderidres) > 0) {
					$reminderid = $adb->query_result ($reminderidres, 0, "reminderid");
				}

				if (isset($reminderid)) {
					$callback_query  = "UPDATE vtiger_activity_reminder_popup SET status = 0, date_start = ?, time_start = ? WHERE reminderid = ?";
					$callback_params = array ($cbdate, $cbtime, $reminderid);
				} else {
					$callback_query  = "INSERT INTO vtiger_activity_reminder_popup (recordid, semodule, date_start, time_start, status) VALUES (?,?,?,?,?)";
					$callback_params = array ($cbrecord, $cbmodule, $cbdate, $cbtime, 0);
				}

				$adb->pquery ($callback_query, $callback_params);
			}
		}

		/** Function to insert values in vtiger_activity_remainder table for the specified module,
		 *
		 * @param $table_name -- table name:: Type varchar
		 * @param $module -- module:: Type varchar
		 */
		function insertIntoReminderTable ($table_name, $module, $recurid) {
			global $log;
			$log->info ("in insertIntoReminderTable  " . $table_name . "    module is  " . $module);
			if ($_REQUEST['set_reminder'] == 'Yes') {
				unset($_SESSION['next_reminder_time']);
				$log->debug ("set reminder is set");
				$rem_days = $_REQUEST['remdays'];
				$log->debug ("rem_days is " . $rem_days);
				$rem_hrs = $_REQUEST['remhrs'];
				$log->debug ("rem_hrs is " . $rem_hrs);
				$rem_min = $_REQUEST['remmin'];
				$log->debug ("rem_minutes is " . $rem_min);
				$reminder_time = $rem_days * 24 * 60 + $rem_hrs * 60 + $rem_min;
				$log->debug ("reminder_time is " . $reminder_time);
				if ($recurid == "") {
					if ($_REQUEST['mode'] == 'edit') {
						$this->activity_reminder ($this->id, $reminder_time, 0, $recurid, 'edit');
					} else {
						$this->activity_reminder ($this->id, $reminder_time, 0, $recurid, '');
					}
				} else {
					$this->activity_reminder ($this->id, $reminder_time, 0, $recurid, '');
				}
			} elseif ($_REQUEST['set_reminder'] == 'No') {
				$this->activity_reminder ($this->id, '0', 0, $recurid, 'delete');
			}
		}


		// Code included by Jaguar - starts
		/** Function to insert values in vtiger_recurringevents table for the specified tablename,module
		 *
		 * @param $recurObj -- Recurring Object:: Type varchar
		 */
		function insertIntoRecurringTable (& $recurObj) {
			global $log, $adb;
			$log->info ("in insertIntoRecurringTable  ");
			$st_date = $recurObj->startdate->get_DB_formatted_date ();
			$log->debug ("st_date " . $st_date);
			$end_date = $recurObj->enddate->get_DB_formatted_date ();
			$log->debug ("end_date is set " . $end_date);
			$type = $recurObj->getRecurringType ();
			$log->debug ("type is " . $type);
			$flag = "true";

			if ($_REQUEST['mode'] == 'edit') {
				$activity_id = $this->id;

				$sql      = 'SELECT min(recurringdate) AS min_date,max(recurringdate) AS max_date, recurringtype, activityid FROM vtiger_recurringevents WHERE activityid=? GROUP BY activityid, recurringtype';
				$result   = $adb->pquery ($sql, array ($activity_id));
				$noofrows = $adb->num_rows ($result);
				for ($i = 0; $i < $noofrows; $i++) {
					$recur_type_b4_edit = $adb->query_result ($result, $i, "recurringtype");
					$date_start_b4edit  = $adb->query_result ($result, $i, "min_date");
					$end_date_b4edit    = $adb->query_result ($result, $i, "max_date");
				}
				if (($st_date == $date_start_b4edit) && ($end_date == $end_date_b4edit) && ($type == $recur_type_b4_edit)) {
					if ($_REQUEST['set_reminder'] == 'Yes') {
						$sql = 'DELETE FROM vtiger_activity_reminder WHERE activity_id=?';
						$adb->pquery ($sql, array ($activity_id));
						$sql = 'DELETE  FROM vtiger_recurringevents WHERE activityid=?';
						$adb->pquery ($sql, array ($activity_id));
						$flag = "true";
					} elseif ($_REQUEST['set_reminder'] == 'No') {
						$sql = 'DELETE  FROM vtiger_activity_reminder WHERE activity_id=?';
						$adb->pquery ($sql, array ($activity_id));
						$flag = "false";
					} else {
						$flag = "false";
					}
				} else {
					$sql = 'DELETE FROM vtiger_activity_reminder WHERE activity_id=?';
					$adb->pquery ($sql, array ($activity_id));
					$sql = 'DELETE  FROM vtiger_recurringevents WHERE activityid=?';
					$adb->pquery ($sql, array ($activity_id));
				}
			}

			$recur_freq    = $recurObj->getRecurringFrequency ();
			$recurringinfo = $recurObj->getDBRecurringInfoString ();

			if ($flag == "true") {
				$max_recurid_qry = 'SELECT max(recurringid) AS recurid FROM vtiger_recurringevents;';
				$result          = $adb->pquery ($max_recurid_qry, array ());
				$noofrows        = $adb->num_rows ($result);
				$recur_id        = 0;
				if ($noofrows > 0) {
					$recur_id = $adb->query_result ($result, 0, "recurid");
				}
				$current_id       = $recur_id + 1;
				$recurring_insert = "INSERT INTO vtiger_recurringevents VALUES (?,?,?,?,?,?)";
				$rec_params       = array ($current_id, $this->id, $st_date, $type, $recur_freq, $recurringinfo);
				$adb->pquery ($recurring_insert, $rec_params);
				unset($_SESSION['next_reminder_time']);
				if ($_REQUEST['set_reminder'] == 'Yes') {
					$this->insertIntoReminderTable ("vtiger_activity_reminder", $module, $current_id, '');
				}
			}
		}

		/** Function to insert values in vtiger_invitees table for the specified module,tablename ,invitees_array
		 *
		 * @param $table_name -- table name:: Type varchar
		 * @param $module -- module:: Type varchar
		 * @param $invitees_array Array
		 */
		function insertIntoInviteeTable ($module, $invitees_array) {
			global $log, $adb;
			$log->debug ("Entering insertIntoInviteeTable(" . $module . "," . $invitees_array . ") method ...");
			if ($this->mode == 'edit') {
				$sql = "DELETE FROM vtiger_invitees WHERE activityid=?";
				$adb->pquery ($sql, array ($this->id));
			}
			foreach ($invitees_array as $inviteeid) {
				if ($inviteeid != '') {
					$query = "INSERT INTO vtiger_invitees VALUES(?,?)";
					$adb->pquery ($query, array ($this->id, $inviteeid));
				}
			}
			$log->debug ("Exiting insertIntoInviteeTable method ...");
		}

		/** Function to insert values in vtiger_salesmanactivityrel table for the specified module
		 *
		 * @param $module -- module:: Type varchar
		 */

		function insertIntoSmActivityRel ($module) {
			global $adb;

			$user_sql = $adb->pquery ("SELECT count(*) AS count FROM vtiger_users WHERE id=?", array ($this->column_fields['assigned_user_id']));
			if ($adb->query_result ($user_sql, 0, 'count') != 0) {
				$sql_qry = "INSERT IGNORE INTO vtiger_salesmanactivityrel (smid,activityid) VALUES(?,?)";
				$adb->pquery ($sql_qry, array ($this->column_fields['assigned_user_id'], $this->id));
			}

			if (isset($_REQUEST['inviteesid']) && $_REQUEST['inviteesid'] != '') {
				$selected_users_string = $_REQUEST['inviteesid'];
				$invitees_array        = explode (';', $selected_users_string);
				foreach ($invitees_array as $inviteeid) {
					if ($inviteeid != '') {
						$resultcheck = $adb->pquery ("SELECT * FROM vtiger_salesmanactivityrel WHERE activityid=? AND smid=?", array ($this->id, $inviteeid));
						if ($adb->num_rows ($resultcheck) != 1) {
							$query = "INSERT IGNORE INTO vtiger_salesmanactivityrel VALUES(?,?)";
							$adb->pquery ($query, array ($inviteeid, $this->id));
						}
					}
				}
			}
		}

		/**
		 *
		 * @param String $tableName
		 *
		 * @return String
		 */
		public function getJoinClause ($tableName) {
			if ($tableName == "vtiger_activity_reminder") {
				return 'LEFT JOIN';
			}
			return parent::getJoinClause ($tableName);
		}


		// Mike Crowe Mod --------------------------------------------------------Default ordering for us
		/**
		 * Function to get sort order
		 * return string  $sorder    - sortorder string either 'ASC' or 'DESC'
		 */
		function getSortOrder () {
			global $log;
			$log->debug ("Entering getSortOrder() method ...");
			if (isset($_REQUEST['sorder'])) {
				$sorder = $this->db->sql_escape_string ($_REQUEST['sorder']);
			} else {
				$sorder = (($_SESSION['ACTIVITIES_SORT_ORDER'] != '') ? ($_SESSION['ACTIVITIES_SORT_ORDER']) : ($this->default_sort_order));
			}
			$log->debug ("Exiting getSortOrder method ...");
			return $sorder;
		}

		/**
		 * Function to get order by
		 * return string  $order_by    - fieldname(eg: 'subject')
		 */
		function getOrderBy () {
			global $log;
			$log->debug ("Entering getOrderBy() method ...");

			$use_default_order_by = '';
			if (PerformancePrefs::getBoolean ('LISTVIEW_DEFAULT_SORTING', true)) {
				$use_default_order_by = $this->default_order_by;
			}

			if (isset($_REQUEST['order_by'])) {
				$order_by = $this->db->sql_escape_string ($_REQUEST['order_by']);
			} else {
				$order_by = (($_SESSION['ACTIVITIES_ORDER_BY'] != '') ? ($_SESSION['ACTIVITIES_ORDER_BY']) : ($use_default_order_by));
			}
			$log->debug ("Exiting getOrderBy method ...");
			return $order_by;
		}
		// Mike Crowe Mod --------------------------------------------------------

//Function Call for Related List -- Start
		/**
		 * Function to get Activity related Users
		 *
		 * @param  integer $id - activityid
		 * returns related Users record in array format
		 */

		function get_users ($id) {
			global $log;
			$log->debug ("Entering get_contacts(" . $id . ") method ...");
			global $app_strings;

			$focus = new Users();

			$button = '<input title="Change" accessKey="" tabindex="2" type="button" class="crmbutton small edit"
					value="' . getTranslatedString ('LBL_SELECT_USER_BUTTON_LABEL') . '" name="button" LANGUAGE=javascript
					onclick=\'return window.open("index.php?module=Users&return_module=Calendar&return_action={$return_modname}&activity_mode=Events&action=Popup&popuptype=detailview&form=EditView&form_submit=true&select=enable&return_id=' . $id . '&recordid=' . $id . '","test","width=640,height=525,resizable=0,scrollbars=0")\';>';

			$returnset = '&return_module=Calendar&return_action=CallRelatedList&return_id=' . $id;

			$query = 'SELECT vtiger_users.id, vtiger_users.first_name,vtiger_users.last_name, vtiger_users.user_name, vtiger_users.email1, vtiger_users.email2, vtiger_users.status, vtiger_users.is_admin, vtiger_user2role.roleid, vtiger_users.secondaryemail, vtiger_users.phone_home, vtiger_users.phone_work, vtiger_users.phone_mobile, vtiger_users.phone_other, vtiger_users.phone_fax,vtiger_activity.date_start,vtiger_activity.due_date,vtiger_activity.time_start,vtiger_activity.duration_hours,vtiger_activity.duration_minutes FROM vtiger_users INNER JOIN vtiger_salesmanactivityrel ON vtiger_salesmanactivityrel.smid=vtiger_users.id  INNER JOIN vtiger_activity ON vtiger_activity.activityid=vtiger_salesmanactivityrel.activityid INNER JOIN vtiger_user2role ON vtiger_user2role.userid=vtiger_users.id WHERE vtiger_activity.activityid=' . $id;

			$return_data = GetRelatedList ('Calendar', 'Users', $focus, $query, $button, $returnset);

			if ($return_data == null) {
				$return_data = Array ();
			}
			$return_data['CUSTOM_BUTTON'] = $button;

			$log->debug ("Exiting get_users method ...");
			return $return_data;
		}

		/**
		 * Function to get activities for given criteria
		 *
		 * @param   string $criteria - query string
		 * returns  activity records in array format($list) or null value
		 */
		function get_full_list ($criteria) {
			global $log;
			$log->debug ("Entering get_full_list(" . $criteria . ") method ...");
			$query  = "SELECT vtiger_crmentity.crmid,vtiger_crmentity.smownerid,vtiger_crmentity.setype, vtiger_activity.*,
	    		NULL AS lastname, NULL AS firstname, NULL AS contactid
	    		FROM vtiger_activity
	    		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_activity.activityid
	    		LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
	    		WHERE vtiger_crmentity.deleted=0 " . $criteria;
			$result = $this->db->query ($query);

			if ($this->db->getRowCount ($result) > 0) {

				// We have some data.
				while ($row = $this->db->fetchByAssoc ($result)) {
					foreach ($this->list_fields_name as $field) {
						if (isset($row[ $field ])) {
							$this->$field = $row[ $field ];
						} else {
							$this->$field = '';
						}
					}
					$list[] = $this;
				}
			}
			if (isset($list)) {
				$log->debug ("Exiting get_full_list method ...");
				return $list;
			} else {
				$log->debug ("Exiting get_full_list method ...");
				return null;
			}
		}


//calendarsync
		/**
		 * Function to get meeting count
		 *
		 * @param  string $user_name - User Name
		 * return  integer  $row["count(*)"]  - count
		 */
		function getCount_Meeting ($user_name) {
			global $log;
			$log->debug ("Entering getCount_Meeting(" . $user_name . ") method ...");
			$query      = "SELECT count(*) FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_activity.activityid INNER JOIN vtiger_salesmanactivityrel ON vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid INNER JOIN vtiger_users ON vtiger_users.id=vtiger_salesmanactivityrel.smid WHERE user_name=? AND vtiger_crmentity.deleted=0 AND vtiger_activity.activitytype='Meeting'";
			$result     = $this->db->pquery ($query, array ($user_name), true, "Error retrieving contacts count");
			$rows_found = $this->db->getRowCount ($result);
			$row        = $this->db->fetchByAssoc ($result, 0);
			$log->debug ("Exiting getCount_Meeting method ...");
			return $row["count(*)"];
		}
//calendarsync
		/**
		 * Function to get task count
		 *
		 * @param  string $user_name - User Name
		 * return  integer  $row["count(*)"]  - count
		 */
		function getCount ($user_name) {
			global $log;
			$log->debug ("Entering getCount(" . $user_name . ") method ...");
			$query      = "SELECT count(*) FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_activity.activityid INNER JOIN vtiger_salesmanactivityrel ON vtiger_salesmanactivityrel.activityid=vtiger_activity.activityid INNER JOIN vtiger_users ON vtiger_users.id=vtiger_salesmanactivityrel.smid WHERE user_name=? AND vtiger_crmentity.deleted=0 AND vtiger_activity.activitytype='Task'";
			$result     = $this->db->pquery ($query, array ($user_name), true, "Error retrieving contacts count");
			$rows_found = $this->db->getRowCount ($result);
			$row        = $this->db->fetchByAssoc ($result, 0);

			$log->debug ("Exiting getCount method ...");
			return $row["count(*)"];
		}

		/**
		 * Function to get reminder for activity
		 *
		 * @param  integer $activity_id - activity id
		 * @param  string $reminder_time - reminder time
		 * @param  integer $reminder_sent - 0 or 1
		 * @param  integer $recurid - recuring eventid
		 * @param  string $remindermode - string like 'edit'
		 */
		function activity_reminder ($activity_id, $reminder_time, $reminder_sent = 0, $recurid, $remindermode = '') {
			global $log;
			$log->debug ("Entering vtiger_activity_reminder(" . $activity_id . "," . $reminder_time . "," . $reminder_sent . "," . $recurid . "," . $remindermode . ") method ...");
			//Check for vtiger_activityid already present in the reminder_table
			$query_exist  = "SELECT activity_id FROM " . $this->reminder_table . " WHERE activity_id = ?";
			$result_exist = $this->db->pquery ($query_exist, array ($activity_id));

			if ($remindermode == 'edit') {
				if ($this->db->num_rows ($result_exist) > 0) {
					$query = "UPDATE " . $this->reminder_table . " SET";
					$query .= " reminder_sent = ?, reminder_time = ? WHERE activity_id =?";
					$params = array ($reminder_sent, $reminder_time, $activity_id);
				} else {
					$query  = "INSERT INTO " . $this->reminder_table . " VALUES (?,?,?,?)";
					$params = array ($activity_id, $reminder_time, 0, $recurid);
				}
			} elseif (($remindermode == 'delete') && ($this->db->num_rows ($result_exist) > 0)) {
				$query  = "DELETE FROM " . $this->reminder_table . " WHERE activity_id = ?";
				$params = array ($activity_id);
			} else {
				$query  = "INSERT INTO " . $this->reminder_table . " VALUES (?,?,?,?)";
				$params = array ($activity_id, $reminder_time, 0, $recurid);
			}
			$this->db->pquery ($query, $params, true, "Error in processing vtiger_table $this->reminder_table");
			$log->debug ("Exiting vtiger_activity_reminder method ...");
		}

		// Function to unlink all the dependent entities of the given Entity by Id
		function unlinkDependencies ($module, $id) {
			global $log;

			$sql = 'DELETE FROM vtiger_activity_reminder WHERE activity_id=?';
			$this->db->pquery ($sql, array ($id));

			$sql = 'DELETE FROM vtiger_recurringevents WHERE activityid=?';
			$this->db->pquery ($sql, array ($id));

			parent::unlinkDependencies ($module, $id);
		}

		// Function to unlink an entity with given Id from another entity
		function unlinkRelationship ($id, $return_module, $return_id) {
			global $log;
			if (empty($return_module) || empty($return_id)) {
				return;
			}

			$sql = 'DELETE FROM vtiger_seactivityrel WHERE activityid=?';
			$this->db->pquery ($sql, array ($id));

			$sql    = 'DELETE FROM vtiger_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array ($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery ($sql, $params);
		}

		/**
		 * this function sets the status flag of activity to true or false depending on the status passed to it
		 *
		 * @param string $status - the status of the activity flag to set
		 *
		 * @return:: true if successful; false otherwise
		 */
		function setActivityReminder ($status) {
			global $adb;
			if ($status == "on") {
				$flag = 0;
			} elseif ($status == "off") {
				$flag = 1;
			} else {
				return false;
			}
			$sql = "UPDATE vtiger_activity_reminder_popup SET status=1 WHERE recordid=?";
			$adb->pquery ($sql, array ($this->id));
			return true;
		}

		/*
		 * Function to get the relation tables for related modules
		 * @param - $secmodule secondary module name
		 * returns the array with table names and fieldnames storing relations between module and this module
		 */
		function setRelationTables ($secmodule) {
			return '';
		}

		/*
		 * Function to get the secondary query part of a report

			/*
			 * Function to get the secondary query part of a report
			 * @param - $module primary module name
			 * @param - $secmodule secondary module name
			 * returns the query string formed on fetching the related data for report for secondary module
			 */
		function generateReportsSecQuery ($module, $secmodule) {
			global $adb;

			$modulesActive = array ();
			$modulesActive = getModuleActive ($adb);
			$query         = $this->getRelationQuery ($module, $secmodule, "vtiger_activity", "activityid");
			
			// Solo agregar JOINs adicionales que no estén ya incluidos en getRelationQuery
			// getRelationQuery ya incluye vtiger_seactivityrel y vtiger_activity
			$query .= " left join vtiger_crmentity as vtiger_crmentityCalendar on vtiger_crmentityCalendar.crmid=vtiger_activity.activityid and vtiger_crmentityCalendar.deleted=0
				left join vtiger_activity_reminder on vtiger_activity_reminder.activity_id = vtiger_activity.activityid
				left join vtiger_recurringevents on vtiger_recurringevents.activityid = vtiger_activity.activityid";
			$query .= " left join vtiger_groups as vtiger_groupsCalendar on vtiger_groupsCalendar.groupid = vtiger_crmentityCalendar.smownerid
					 left join vtiger_users as vtiger_usersCalendar on vtiger_usersCalendar.id = vtiger_crmentityCalendar.smownerid
        			left join vtiger_users as vtiger_lastModifiedByCalendar on vtiger_lastModifiedByCalendar.id = vtiger_crmentityCalendar.modifiedby ";
			return $query;
		}

		public function getNonAdminAccessControlQuery ($module, $user, $scope = '') {
			$local_user = clone $user;
			require ('user_privileges/user_privileges.php');
			require ('user_privileges/sharing_privileges.php');
			$query = ' ';
			$tabId = getTabid ($module);
			if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2]
																		  == 1 && $defaultOrgSharingPermission[ $tabId ] == 3
			) {
				$tableName               = 'vt_tmp_u' . $user->id . '_t' . $tabId;
				$sharingRuleInfoVariable = $module . '_share_read_permission';
				$sharingRuleInfo         = $$sharingRuleInfoVariable;
				$sharedTabId             = null;
				$this->setupTemporaryTable ($tableName, $sharedTabId, $user,
					$current_user_parent_role_seq, $current_user_groups);
				$query     = " INNER JOIN $tableName $tableName$scope ON ((($tableName$scope.id = " .
					 "vtiger_crmentity$scope.smcreatorid) or ($tableName$scope.id = vtiger_crmentity$scope.smownerid)) and $tableName$scope.shared=0) ";
				$sharedIds = getSharedCalendarId ($user->id);
				if (!empty($sharedIds)) {
					$query .= "or ($tableName$scope.id = vtiger_crmentity$scope.smownerid AND " .
							  "$tableName$scope.shared=1 and vtiger_activity.visibility = 'Public') ";
				}
			}
			return $query;
		}

		protected function setupTemporaryTable ($tableName, $tabId, $user, $parentRole, $userGroups) {
			$module = null;
			if (!empty($tabId)) {
				$module = getTabname ($tabId);
			}
			$query  = $this->getNonAdminAccessQuery ($module, $user, $parentRole, $userGroups);
			$query  = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key, shared " .
					  "int(1) default 0) ignore " . $query;
			$db     = PearDatabase::getInstance ();
			$result = $db->pquery ($query, array ());
			if (is_object ($result)) {
				$query  = "create temporary table IF NOT EXISTS $tableName(id int(11) primary key, shared " .
						  "int(1) default 0) replace select 1, userid as id from vtiger_sharedcalendar where " .
						  "sharedid = $user->id";
				$result = $db->pquery ($query, array ());
				if (is_object ($result)) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Get list view query (send more WHERE clause condition if required)
		 *
		 * @param string $moduleName
		 * @param string $additionalWhereClause
		 *
		 * @return string
		 */
		public function getListQuery ($moduleName, $additionalWhereClause = '') {
			global $current_user;
			$sql = "SELECT
						vtiger_activity.activityid AS act_id,
						vtiger_crmentity.crmid,
						vtiger_crmentity.smownerid,
						vtiger_crmentity.setype,
						vtiger_activity.*,
						NULL AS lastname,
						NULL AS firstname,
						NULL AS contactid,
						NULL AS accountid,
						NULL AS accountname
					FROM
						vtiger_activity
						LEFT JOIN vtiger_activitycf ON vtiger_activitycf.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
						LEFT OUTER JOIN vtiger_activity_reminder ON vtiger_activity_reminder.activity_id = vtiger_activity.activityid
						LEFT JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_users vtiger_users2 ON vtiger_crmentity.modifiedby = vtiger_users2.id
						LEFT JOIN vtiger_groups vtiger_groups2 ON vtiger_crmentity.modifiedby = vtiger_groups2.groupid
						{$this->getNonAdminAccessControlQuery ($moduleName, $current_user)}
					WHERE
						vtiger_crmentity.deleted=0 AND
						activitytype<>'Emails'
						{$additionalWhereClause}";
			trim (preg_replace ('/\s+/S', ' ', $sql));
		}

		/**
		 * Obtener costo total ejecutado de una actividad
		 * 
		 * @param PearDatabase $adb - Database instance
		 * @param integer $activityId - ID de la actividad
		 * @return float - Costo total ejecutado
		 */
		public static function getTotalActualCost($adb, $activityId) {
			if (empty($activityId)) {
				return 0.0;
			}
			
			$result = $adb->pquery(
				'SELECT IFNULL(SUM(actual_cost), 0) AS total_cost 
				 FROM vtiger_activity_report 
				 WHERE activityid = ? AND (deleted = 0 OR deleted IS NULL)',
				array($activityId)
			);
			
			if ($adb->num_rows($result) > 0) {
				$row = $adb->fetchByAssoc($result, -1, false);
				return floatval($row['total_cost']);
			}
			
			return 0.0;
		}
		
		/**
		 * Obtener análisis de variación de costos de una actividad
		 * 
		 * @param PearDatabase $adb - Database instance
		 * @param integer $activityId - ID de la actividad
		 * @return array - Array con análisis de costos
		 *   ['estimated' => float, 'actual' => float, 'variance' => float, 
		 *    'variance_pct' => float, 'status' => string]
		 */
		public static function getCostVariance($adb, $activityId) {
			if (empty($activityId)) {
				return array(
					'estimated' => 0,
					'actual' => 0,
					'variance' => 0,
					'variance_pct' => 0,
					'status' => 'NO_BUDGET'
				);
			}
			
			$result = $adb->pquery(
				'SELECT 
					a.estimated_cost,
					IFNULL(SUM(ar.actual_cost), 0) AS total_actual_cost
				 FROM vtiger_activity a
				 LEFT JOIN vtiger_activity_report ar ON ar.activityid = a.activityid AND (ar.deleted = 0 OR ar.deleted IS NULL)
				 WHERE a.activityid = ?
				 GROUP BY a.activityid',
				array($activityId)
			);
			
			if ($adb->num_rows($result) > 0) {
				$row = $adb->fetchByAssoc($result, -1, false);
				$estimated = floatval($row['estimated_cost']);
				$actual = floatval($row['total_actual_cost']);
				$variance = $actual - $estimated;
				$variancePct = ($estimated > 0) ? ($variance / $estimated * 100) : 0;
				
				// Determinar estado presupuestario
				$status = 'NO_BUDGET';
				if ($estimated > 0) {
					if ($variance > 0) {
						$status = 'OVER_BUDGET';
					} elseif ($variance < 0) {
						$status = 'UNDER_BUDGET';
					} else {
						$status = 'ON_BUDGET';
					}
				}
				
				return array(
					'estimated' => $estimated,
					'actual' => $actual,
					'variance' => $variance,
					'variance_pct' => $variancePct,
					'status' => $status
				);
			}
			
			return array(
				'estimated' => 0,
				'actual' => 0,
				'variance' => 0,
				'variance_pct' => 0,
				'status' => 'NO_BUDGET'
			);
		}
		
		/**
		 * Obtener datos completos de análisis de costos de una actividad
		 * Incluye tiempo y costo para comparación
		 * 
		 * @param PearDatabase $adb - Database instance
		 * @param integer $activityId - ID de la actividad
		 * @return array - Array con análisis completo
		 */
		public static function getCostAnalysis($adb, $activityId) {
			if (empty($activityId)) {
				return null;
			}
			
			$result = $adb->pquery(
				'SELECT * FROM v_activity_cost_analysis WHERE activityid = ?',
				array($activityId)
			);
			
			if ($adb->num_rows($result) > 0) {
				return $adb->fetchByAssoc($result, -1, false);
			}
			
			return null;
		}
		
		/**
		 * Calcular índice de desempeño de costos (CPI)
		 * CPI > 1: Eficiente (bajo presupuesto)
		 * CPI = 1: En presupuesto
		 * CPI < 1: Ineficiente (sobre presupuesto)
		 * 
		 * @param PearDatabase $adb - Database instance
		 * @param integer $activityId - ID de la actividad
		 * @return float|null - CPI o null si no hay datos suficientes
		 */
		public static function getCostPerformanceIndex($adb, $activityId) {
			if (empty($activityId)) {
				return null;
			}
			
			$result = $adb->pquery(
				'SELECT 
					a.estimated_cost,
					a.progress,
					IFNULL(SUM(ar.actual_cost), 0) AS total_actual_cost
				 FROM vtiger_activity a
				 LEFT JOIN vtiger_activity_report ar ON ar.activityid = a.activityid AND (ar.deleted = 0 OR ar.deleted IS NULL)
				 WHERE a.activityid = ?
				 GROUP BY a.activityid',
				array($activityId)
			);
			
			if ($adb->num_rows($result) > 0) {
				$row = $adb->fetchByAssoc($result, -1, false);
				$estimated = floatval($row['estimated_cost']);
				$progress = floatval($row['progress']);
				$actual = floatval($row['total_actual_cost']);
				
				// CPI = (Progreso * Costo Estimado) / Costo Real
				if ($actual > 0 && $estimated > 0 && $progress > 0) {
					$earnedValue = ($progress / 100) * $estimated;
					$cpi = $earnedValue / $actual;
					return round($cpi, 2);
				}
			}
			
			return null;
		}	
	}

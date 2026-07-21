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
	 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Activities/Save.php,v 1.11 2005/04/18 10:37:49 samk Exp $
	 * Description:  Saves an Account record and then redirects the browser to the
	 * defined return URL.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 ********************************************************************************/

	require_once ('modules/Calendar/Activity.php');
	require_once ('include/logging.php');
	require_once ("config.php");
	require_once ('include/database/PearDatabase.php');
	require_once ('modules/Calendar/CalendarCommon.php');
	global $adb, $theme;
	$local_log     = LoggerManager::getLogger ('index');
	$focus         = new Activity();
	$activity_mode = vtlib_purify ($_REQUEST['activity_mode']);
	$tab_type      = 'Calendar';
//added to fix 4600
	$search = vtlib_purify ($_REQUEST['search_url']);

	$focus->column_fields["activitytype"] = 'Task';
	if (isset($_REQUEST['record'])) {
		$focus->id = $_REQUEST['record'];
		$local_log->debug ("id is " . $id);
	}
	if (isset($_REQUEST['mode'])) {
		$focus->mode = $_REQUEST['mode'];
	}

	if ((isset($_REQUEST['change_status']) && $_REQUEST['change_status']) && ($_REQUEST['status'] != '' || $_REQUEST['eventstatus'] != '')) {
		$status        = '';
		$activity_type = '';
		$return_id     = $focus->id;
		if (isset($_REQUEST['status'])) {
			$status        = $_REQUEST['status'];
			$activity_type = "Task";
		} elseif (isset($_REQUEST['eventstatus'])) {
			$status        = $_REQUEST['eventstatus'];
			$activity_type = "Events";
		}
		if (isPermitted ("Calendar", "EditView", $_REQUEST['record']) == 'yes') {
			ChangeStatus ($status, $return_id, $activity_type);
		} else {
			echo "<link rel='stylesheet' type='text/css' href='themes/$theme/style.css'>";
			echo "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
			echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>

			<table border='0' cellpadding='5' cellspacing='0' width='98%'>
			<tbody><tr>
			<td rowspan='2' width='11%'><img src='<?php echo vtiger_imageurl('denied.gif', $theme). ?>' ></td>
			<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>$app_strings[LBL_PERMISSION]</span></td>
			</tr>
			<tr>
			<td class='small' align='right' nowrap='nowrap'>
			<a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br>								   						     </td>
			</tr>
			</tbody></table>
		</div>";
			echo "</td></tr></table>";
			die;
		}
		$invitee_qry = "SELECT * FROM vtiger_invitees WHERE activityid=?";
		$invitee_res = $adb->pquery ($invitee_qry, array ($return_id));
		$count       = $adb->num_rows ($invitee_res);
		if ($count != 0) {
			for ($j = 0; $j < $count; $j++) {
				$invitees_ids[] = $adb->query_result ($invitee_res, $j, "inviteeid");
			}
			$invitees_ids_string = implode (';', $invitees_ids);
			sendInvitation ($invitees_ids_string, $activity_type, $mail_data['subject'], $mail_data);
		}
	} else {
		$timeFields = array ('time_start', 'time_end');
		$tabId      = getTabid ($tab_type);
		foreach ($focus->column_fields as $fieldname => $val) {
			$fieldInfo  = getFieldRelatedInfo ($tabId, $fieldname);
			$uitype     = $fieldInfo['uitype'];
			$typeofdata = $fieldInfo['typeofdata'];
			if (isset($_REQUEST[ $fieldname ])) {
				if (is_array ($_REQUEST[ $fieldname ])) {
					$value = $_REQUEST[ $fieldname ];
				} else {
					$value = trim ($_REQUEST[ $fieldname ]);
				}

				if ((($typeofdata == 'T~M') || ($typeofdata == 'T~O')) && ($uitype == 2 || $uitype == 70)) {
					if (!in_array ($fieldname, $timeFields)) {
						$date  = DateTimeField::convertToDBTimeZone ($value);
						$value = $date->format ('H:i');
					}
					$focus->column_fields[ $fieldname ] = $value;
				} else {
					$focus->column_fields[ $fieldname ] = $value;
				}
				if (($fieldname == 'notime') && ($focus->column_fields[ $fieldname ])) {
					$focus->column_fields['time_start']       = '';
					$focus->column_fields['duration_hours']   = '';
					$focus->column_fields['duration_minutes'] = '';
				}
				if (($fieldname == 'recurringtype') && !isset($_REQUEST['recurringcheck'])) {
					$focus->column_fields['recurringtype'] = '--None--';
				}
			}
		}
		if (isset($_REQUEST['visibility']) && $_REQUEST['visibility'] != '') {
			$focus->column_fields['visibility'] = $_REQUEST['visibility'];
		} else {
			$focus->column_fields['visibility'] = 'Private';
		}

		if ($_REQUEST['assigntype'] == 'U') {
			$focus->column_fields['assigned_user_id'] = $_REQUEST['assigned_user_id'];
		} elseif ($_REQUEST['assigntype'] == 'T') {
			$focus->column_fields['assigned_user_id'] = $_REQUEST['assigned_group_id'];
		}

		// Fecha Inicio
		$dateField                          = 'fecha_inicio';
		$fecha_inicio                       = $_REQUEST['fecha_inicio'];
		$fecha_inicio                       = explode ('-', $fecha_inicio);
		$fecha_inicio                       = $fecha_inicio[2] . '-' . $fecha_inicio[1] . '-' . $fecha_inicio[0];
		$focus->column_fields['date_start'] = $fecha_inicio;

		// Fecha fin
		$fecha_inicio                       = $_REQUEST['fecha_inicio'];
		$fecha_inicio                       = explode ('-', $fecha_inicio);
		$fecha_inicio                       = $fecha_inicio[2] . '-' . $fecha_inicio[1] . '-' . $fecha_inicio[0];
		$focus->column_fields['date_start'] = $fecha_inicio;

		//$fieldname = 'time_start';
		$fieldname = 'hora_inicio';

		$focus->column_fields['time_start'] = $_REQUEST['hora_inicio'];

		$fecha_fin                        = $_REQUEST['fecha_fin'];
		$fecha_fin                        = explode ('-', $fecha_fin);
		$fecha_fin                        = $fecha_fin[2] . '-' . $fecha_fin[1] . '-' . $fecha_fin[0];
		$focus->column_fields['due_date'] = $fecha_fin;

		//$date = new DateTimeField($_REQUEST[$dateField]. ' ' . $_REQUEST[$fieldname]);

		//$date = new DateTimeField($fecha_inicio. ' ' . $_REQUEST[$fieldname]);
		//$date = new DateTimeField($fecha_inicio);
		//$focus->column_fields[$dateField] = $date->getDBInsertDateValue();
		//$focus->column_fields[$fieldname] = $date->getDBInsertTimeValue();
		if (empty($_REQUEST['time_end'])) {
			$_REQUEST['time_end'] = date ('H:i', strtotime ('+10 minutes',
				strtotime ($focus->column_fields['date_start'] . ' ' . $_REQUEST['time_start'])));
		}
		//$dateField = 'due_date';
		$fieldname = 'time_end';

		//$date = new DateTimeField($_REQUEST[$dateField]);
		//$focus->column_fields[$dateField] = $date->getDBInsertDateValue();
		//$focus->column_fields[$fieldname] = $date->getDBInsertTimeValue();

		$focus->column_fields['time_end'] = $_REQUEST['hora_fin'];

		$focus->save ($tab_type);
		/* For Followup START -- by Minnie */
		if (isset($_REQUEST['followup']) && $_REQUEST['followup'] == 'on' && $activity_mode == 'Events' && isset($_REQUEST['followup_time_start']) && $_REQUEST['followup_time_start'] != '') {
			$heldevent_id                    = $focus->id;
			$focus->column_fields['subject'] = '[Followup] ' . $focus->column_fields['subject'];
			$startDate                       = new DateTimeField($_REQUEST['followup_date'] . ' ' .
																 $_REQUEST['followup_time_start']);
			$endDate                         = new DateTimeField($_REQUEST['followup_due_date'] . ' ' .
																 $_REQUEST['followup_time_end']);
			//$focus->column_fields['date_start'] = $startDate->getDBInsertDateValue();
			//$focus->column_fields['due_date'] = $endDate->getDBInsertDateValue();
			$focus->column_fields['time_start']  = $startDate->getDBInsertTimeValue ();
			$focus->column_fields['time_end']    = $endDate->getDBInsertTimeValue ();
			$focus->column_fields['eventstatus'] = 'Planned';
			$focus->mode                         = 'create';
			$focus->save ($tab_type);
		}
		/* For Followup END -- by Minnie */
		$return_id = $focus->id;
	}

	if (isset($_REQUEST['return_module']) && $_REQUEST['return_module'] != "") {
		$return_module = vtlib_purify ($_REQUEST['return_module']);
	} else {
		$return_module = "Calendar";
	}
	if (isset($_REQUEST['return_action']) && $_REQUEST['return_action'] != "") {
		$return_action = vtlib_purify ($_REQUEST['return_action']);
	} else {
		$return_action = "DetailView";
	}
	if (isset($_REQUEST['return_id']) && $_REQUEST['return_id'] != "") {
		$return_id = vtlib_purify ($_REQUEST['return_id']);
	}

	$activemode = "";
	if ($activity_mode != '') {
		$activemode = "&activity_mode=" . $activity_mode;
	}

	function getFieldRelatedInfo ($tabId, $fieldName) {
		$fieldInfo = VTCacheUtils::lookupFieldInfo ($tabId, $fieldName);
		if ($fieldInfo === false) {
			getColumnFields (getTabModuleName ($tabid));
			$fieldInfo = VTCacheUtils::lookupFieldInfo ($tabId, $fieldName);
		}
		return $fieldInfo;
	}

//to delete activity and its parent table relation
	if (isset($_REQUEST['del_actparent_rel']) && $_REQUEST['del_actparent_rel'] != '' && $_REQUEST['mode'] == 'edit') {
		$parnt_id = $_REQUEST['del_actparent_rel'];
		$sql      = 'DELETE FROM vtiger_seactivityrel WHERE crmid=? AND activityid=?';
		$adb->pquery ($sql, array ($parnt_id, $record));
	}

//[ TT11157 ] Defecto en el icono “crear tarea”
//DM 29062016
//Solucionando incidencia en la creación de tareas para módulos relacionados
	if (isset($_REQUEST['return_module']) &&
		isset($_REQUEST['activity_mode']) && ($_REQUEST['activity_mode'] == 'Events' || $_REQUEST['activity_mode'] == 'Task') &&
		$_REQUEST['mode'] != 'edit'
	) {

		if ($_REQUEST['idlist'] == 'all') {
			$result        = getSelectAllQuery ($_REQUEST, $_REQUEST['return_module']);
			$numRows       = $adb->num_rows ($result);
			$listnewrecord = "";
			if ($numRows > 0) {
				while ($r = $adb->fetchByAssoc ($result)) {
					$list .= $r['leadid'] . ";";
				}
			}
		} else {
			$list = $_REQUEST['idlist'];
		}

		$listnewrecord = explode (";", $list);

		if (count ($listnewrecord) > 0) {

			for ($i = 0; $i < count ($listnewrecord); $i++) {
				if ($listnewrecord[ $i ] != '') {

					$leadId      = $listnewrecord[ $i ];
					$idActividad = $focus->id;
					$sql         = "INSERT INTO vtiger_seactivityrel (crmid,activityid) VALUES (?,?)";
					$adb->pquery ($sql, array ($leadId, $idActividad));
				}
			}
		}
	}

	if (isset($_REQUEST['view']) && $_REQUEST['view'] != '') {
		$view = vtlib_purify ($_REQUEST['view']);
	}
	if (isset($_REQUEST['hour']) && $_REQUEST['hour'] != '') {
		$hour = vtlib_purify ($_REQUEST['hour']);
	}
	if (isset($_REQUEST['day']) && $_REQUEST['day'] != '') {
		$day = vtlib_purify ($_REQUEST['day']);
	}
	if (isset($_REQUEST['month']) && $_REQUEST['month'] != '') {
		$month = vtlib_purify ($_REQUEST['month']);
	}
	if (isset($_REQUEST['year']) && $_REQUEST['year'] != '') {
		$year = vtlib_purify ($_REQUEST['year']);
	}
	if (isset($_REQUEST['viewOption']) && $_REQUEST['viewOption'] != '') {
		$viewOption = vtlib_purify ($_REQUEST['viewOption']);
	}
	if (isset($_REQUEST['subtab']) && $_REQUEST['subtab'] != '') {
		$subtab = vtlib_purify ($_REQUEST['subtab']);
	}

	if ($_REQUEST['recurringcheck']) {
		include_once dirname (__FILE__) . '/RepeatEvents.php';
		Calendar_RepeatEvents::repeatFromRequest ($focus);
	}

//code added for returning back to the current view after edit from list view
	if ($_REQUEST['return_viewname'] == '') {
		$return_viewname = '0';
	}
	if ($_REQUEST['return_viewname'] != '') {
		$return_viewname = vtlib_purify ($_REQUEST['return_viewname']);
	}

	$parenttab = getParentTab ();

	if (!empty($_REQUEST['start'])) {
		$page = '&start=' . vtlib_purify ($_REQUEST['start']);
	}
	if (!empty($_REQUEST['pagenumber'])) {
		$page = "&start=" . vtlib_purify ($_REQUEST['pagenumber']);
	}

	header ("Location: index.php?action=$return_action&module=$return_module$activemode&viewname=$return_viewname$page&parenttab=$parenttab$search");

?>
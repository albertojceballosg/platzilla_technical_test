<?php
	/*+********************************************************************************
	 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
	 * ("License"); You may not use this file except in compliance with the License
	 * The Original Code is:  vtiger CRM Open Source
	 * The Initial Developer of the Original Code is vtiger.
	 * Portions created by vtiger are Copyright (C) vtiger.
	 * All Rights Reserved.
	 ********************************************************************************/

	/**This function generates the security parameters for a given module based on the assigned profile
	 *Param $module - module name
	 *Returns an string value
	 */

	function getDashboardQuery ($query, $module) {
		global $current_user;
		$secQuery = getNonAdminAccessControlQuery ($module, $current_user);
		if (strlen ($secQuery) > 1) {
			$query = appendFromClauseToQuery ($query, $secQuery);
		}
		return $query;
	}

	/**This function generates the security parameters for a given user base picklist values
	 *Param $graph - name of the graph
	 *Returns an string value
	 */

	function picklist_check ($module, $graph_by) {
		global $current_user, $adb;
		$pick_query = '';
		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');
		$roleid  = $current_user->roleid;
		$subrole = getRoleSubordinates ($roleid);
		if (count ($subrole) > 0) {
			$roleids = $subrole;
			array_push ($roleids, $roleid);
		} else {
			$roleids = $roleid;
		}
		if ($graph_by == 'sostatus' || $graph_by == 'leadsource' || $graph_by == 'leadstatus' || $graph_by == 'industry' || $graph_by == 'productcategory' || $graph_by == 'postatus' || $graph_by == 'invoicestatus' || $graph_by == 'ticketstatus' || $graph_by == 'priority' || $graph_by == 'category' || $graph_by == 'quotestage') {
			$temp_fieldname = $graph_by;
			if ($graph_by == 'priority') {
				$temp_fieldname = 'ticketpriorities';
			}
			if ($graph_by == 'category') {
				$temp_fieldname = 'ticketcategories';
			}

			if (count ($roleids) > 1) {
				$pick_query = " in (select distinct $temp_fieldname from vtiger_" . $temp_fieldname . "  inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_" . $temp_fieldname . ".picklist_valueid where roleid in (\"" . implode ($roleids, "\",\"") . "\")) ";
			} else {
				$pick_query = " in (select distinct $temp_fieldname from vtiger_" . $temp_fieldname . "  inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_" . $temp_fieldname . ".picklist_valueid where roleid in ('$roleids')) ";
			}
		}
		return $pick_query;
	}

?>
<script id="dash_script">
	var gdash_display_type = '<?php echo vtlib_purify ($_REQUEST['display_view']);?>';
</script>

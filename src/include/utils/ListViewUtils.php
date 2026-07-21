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
	 * $Header: /cvsroot/vtigercrm/vtiger_crm/include/utils/ListViewUtils.php,v 1.32 2006/02/03 06:53:08 mangai Exp $
	 * Description:  Includes generic helper functions used throughout the application.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 ********************************************************************************/

	require_once ('include/database/PearDatabase.php');
	require_once ('include/ComboUtil.php'); //new
	require_once ('modules/Settings/lib/EditableFieldsHelper.class.php');
	require_once ('include/utils/CommonUtils.php'); //new
	require_once ('user_privileges/default_module_view.php'); //new
	require_once ('include/utils/PlatformUtils.class.php');
	require_once ('include/utils/UserInfoUtil.php');
	require_once ('include/Zend/Json.php');

	/* * This function is used to get the list view header values in a list view
 * Param $focus - module object
* Param $module - module name
* Param $sort_qry - sort by value
* Param $sorder - sorting order (asc/desc)
* Param $order_by - order by
* Param $relatedlist - flag to check whether the header is for listvie or related list
* Param $oCv - Custom view object
* Returns the listview header values in an array
*/

	function getListViewHeader ($focus, $module, $sort_qry = '', $sorder = '', $order_by = '', $relatedlist = '', $oCv = '', $relatedmodule = '', $skipActions = false) {
		global $adb, $app_strings, $current_user, $log, $theme;

		$log->debug ("Entering getListViewHeader(" . $module . "," . $sort_qry . "," . $sorder . "," . $order_by . "," . $relatedlist . "," . (is_object ($oCv) ? get_class ($oCv) : $oCv) . ") method ...");
		$theme_path = "themes/" . $theme . "/";

		$list_header = array ();

		//Get the vtiger_tabid of the module
		$tabid          = getTabid ($module);
		$tabname        = getParentTab ();
		$target         = null;
		$relatedListRow = getRelatedListFormattedHeader($adb, $relatedmodule, $module);
		if (!empty ($relatedListRow)) {
			$header          = $relatedListRow['label'];
			$formattedHeader = str_replace (' ', '', $header);
			$target          = 'tbl_' . $relatedmodule . '_' . $formattedHeader;
		}

		//added for vtiger_customview 27/5
		if ($oCv) {
			if (isset($oCv->list_fields)) {
				$focus->list_fields = $oCv->list_fields;
			}
		}
		// Remove fields which are made inactive
		$focus->filterInactiveFields ($module);

		//Added to reduce the no. of queries logging for non-admin user -- by Minnie-start
		$field_list = array ();
		$j          = 0;
		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');
		foreach ($focus->list_fields as $name => $tableinfo) {
			$fieldname = $focus->list_fields_name[ $name ];
			if ($oCv) {
				if (isset($oCv->list_fields_name)) {
					$fieldname = $oCv->list_fields_name[ $name ];
				}
			}
			array_push ($field_list, $fieldname);
			$j++;
		}
		$field = Array ();
		if ($is_admin == false) {
			if ($module == 'Emails') {
				$query  = "SELECT fieldname FROM vtiger_field WHERE tabid = ? AND vtiger_field.presence IN (0,2)";
				$params = array ($tabid);
			} else {
				$profileList = getCurrentUserProfileList ();
				$params      = array ();

				$query = "SELECT DISTINCT vtiger_field.fieldname
					FROM vtiger_field
					INNER JOIN vtiger_profile2field
					ON vtiger_profile2field.fieldid = vtiger_field.fieldid
					INNER JOIN vtiger_def_org_field
					ON vtiger_def_org_field.fieldid = vtiger_field.fieldid";
				if ($module == "Calendar") {
					$query .= " WHERE vtiger_field.tabid in (9,16) and vtiger_field.presence in (0,2)";
				} else {
					$query .= " WHERE vtiger_field.tabid = ? and vtiger_field.presence in (0,2)";
					array_push ($params, $tabid);
				}

				$query .= " AND vtiger_profile2field.visible = 0 AND vtiger_def_org_field.visible = 0";
				if (!empty($profileList)) {
					$query .= " AND vtiger_profile2field.profileid IN (" . generateQuestionMarks ($profileList) . ")";
					array_push ($params, $profileList);
				}
				$query .= " AND vtiger_field.fieldname IN (" . generateQuestionMarks ($field_list) . ") ";
				array_push ($params, $field_list);
			}
			$result = $adb->pquery ($query, $params);
			for ($k = 0; $k < $adb->num_rows ($result); $k++) {
				$field[] = $adb->query_result ($result, $k, "fieldname");
			}
		}
		//end
		//modified for vtiger_customview 27/5 - $app_strings change to $mod_strings
		foreach ($focus->list_fields as $name => $tableinfo) {
			//added for vtiger_customview 27/5
			if ($oCv) {
				if (isset($oCv->list_fields_name)) {
					$fieldname = $oCv->list_fields_name[ $name ];
				} else {
					$fieldname = $focus->list_fields_name[ $name ];
				}
			} else {
				$fieldname = $focus->list_fields_name[ $name ];
				if ($fieldname == 'lastname' && ($module == 'SalesOrder' || $module == 'PurchaseOrder' || $module == 'Invoice' || $module == 'Quotes' || $module == 'Calendar')) {
					$fieldname = 'contact_id';
				}
			}
			$headerTitle = '';
			if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0 || in_array ($fieldname, $field) || $fieldname == '' || ($name == 'Close' && $module == 'Calendar')) {
				if (isset($focus->sortby_fields) && $focus->sortby_fields != '') {
					//Added on 14-12-2005 to avoid if and else check for every list vtiger_field for arrow image and change order
					$change_sorder = array ('ASC' => 'DESC', 'DESC' => 'ASC');
					$arrow_gif     = array ('ASC' => 'up', 'DESC' => 'down');
					foreach ($focus->list_fields[ $name ] as $tab => $col) {
						if (in_array ($col, $focus->sortby_fields)) {
							if ($order_by == $col) {
								$temp_sorder = $change_sorder[ $sorder ];
								$arrow       = "<i class=\"fa fa-caret-{$arrow_gif [ $sorder ]}\" aria-hidden=\"true\" style=\"margin-left: 0.5em;\"></i>";
							} else {
								$temp_sorder = 'ASC';
								$arrow       = '<i class="fa fa-caret-up" aria-hidden="true" style="margin-left:.5em;" ></i>';
							}
							$lbl_name = getTranslatedString (decode_html ($name), $module);
							//added to display vtiger_currency symbol in listview header
							if ($lbl_name == 'Amount') {
								$lbl_name .= ' (' . $app_strings ['LBL_IN'] . ' ' . $user_info ['currency_symbol'] . ')';
							}
							if ($relatedlist != '' && $relatedlist != 'global') {
								$relationURL = '';
								if (!empty($_REQUEST['relation_id'])) {
									$relationURL = '&relation_id=' . vtlib_purify ($_REQUEST ['relation_id']);
								}
								$actionsURL = '';
								if (!empty($_REQUEST['actions'])) {
									$actionsURL = '&actions=' . vtlib_purify ($_REQUEST ['actions']);
								}
								if (empty($_REQUEST['header'])) {
									$moduleLabel = getTranslatedString ($module, $module);
								} else {
									$moduleLabel = $_REQUEST ['header'];
								}
								$moduleLabel = str_replace (' ', '', $moduleLabel);
								$target = (empty($target)) ? "tbl_{$relatedmodule}_{$moduleLabel}" : $target;

								$headerTitle = "<div class=\"title-overflow\"><a href=\"javascript:void(0);\" onclick=\"loadRelatedListBlock ('module={$relatedmodule}&action={$relatedmodule}Ajax&file=DetailViewAjax&ajxaction=LOADRELATEDLIST&header={$moduleLabel}&order_by={$col}&record={$relatedlist}&sorder={$temp_sorder}{$relationURL}{$actionsURL}', '{$target}', '{$relatedmodule}_{$moduleLabel}',this);\" rel='{$temp_sorder}' class=\"listFormHeaderLinks\">{$lbl_name}</a></div>{$arrow}";
							} elseif ($module == 'Users' && $name == 'User Name') {
								$headerTitle = "<div class=\"title-overflow\"><a href=\"javascript:;\" onclick=\"getListViewEntries_js ('{$module}', 'parenttab={$tabname}&order_by={$col}&start=1&sorder={$temp_sorder}{$sort_qry}');\" class=\"listFormHeaderLinks\">" . getTranslatedString ('LBL_LIST_USER_NAME_ROLE', $module) . "</a></div>{$arrow}";
							} elseif ($relatedlist == "global") {
								$headerTitle = $lbl_name;
							} else {
								$headerTitle = "<div class=\"title-overflow\"><a href=\"javascript:;\" onclick=\"getListViewEntries_js ('{$module}', 'parenttab={$tabname}&order_by={$col}&start=1&sorder={$temp_sorder}{$sort_qry}');\" class=\"listFormHeaderLinks\">{$lbl_name}</a></div>{$arrow}";
							}
						} else {
							if (stripos ($col, 'cf_') === 0) {
								$tablenameArray = array_keys ($tableinfo, $col);
								$tablename      = $tablenameArray[0];
								$cf_columns     = $adb->getColumnNames ($tablename);
								if (array_search ($col, $cf_columns) != null) {
									$pquery = "SELECT fieldlabel,typeofdata FROM vtiger_field WHERE tablename = ? AND fieldname = ? AND vtiger_field.presence IN (0,2)";
									$cf_res = $adb->pquery ($pquery, array ($tablename, $col));
									if (count ($cf_res) > 0) {
										$cf_fld_label    = $adb->query_result ($cf_res, 0, "fieldlabel");
										$typeofdata      = explode ("~", $adb->query_result ($cf_res, 0, "typeofdata"));
										$new_field_label = $tablename . ":" . $col . ":" . $col . ":" . $module . "_" . str_replace (" ", "_", $cf_fld_label) . ":" . $typeofdata[0];
										$headerTitle     = $cf_fld_label;

										// Update the existing field name in the database with new field name.
										$upd_query  = "UPDATE vtiger_cvcolumnlist SET columnname = ? WHERE columnname LIKE '" . $tablename . ":" . $col . ":" . $col . "%'";
										$upd_params = array ($new_field_label);
										$adb->pquery ($upd_query, $upd_params);
									}
								}
							} else {
								$title_head  = getTranslatedString ($name, $module);
								$headerTitle = "<div class=\"title-overflow\" title=\"{$title_head}\">{$title_head}</div><i class=\"fa none\" aria-hidden=\"true\" style=\"margin-left:.5em;\" ></i>";
							}
						}
					}
				}
				//added to display vtiger_currency symbol in related listview header
				if ($name == 'Amount' && $relatedlist != '') {
					$headerTitle .= ' (' . $app_strings['LBL_IN'] . ' ' . $user_info['currency_symbol'] . ')';
				}

				if ($module == "Calendar" && $name == $app_strings['Close']) {
					if (isPermitted ("Calendar", "EditView") == 'yes') {
						if ((getFieldVisibilityPermission ('Events', $current_user->id, 'eventstatus') == '0') || (getFieldVisibilityPermission ('Calendar', $current_user->id, 'taskstatus') == '0')) {
							array_push ($list_header, $name);
						}
					}
				} else {
					$list_header[] = $headerTitle;
				}
			}
		}

		//Added for Action - edit and delete link header in listview
		if (!$skipActions && (isPermitted ($module, "EditView", "") == 'yes' || isPermitted ($module, "Delete", "") == 'yes')) {
			$list_header[] = $app_strings["LBL_ACTION"];
		}

		$log->debug ("Exiting getListViewHeader method ...");
		return $list_header;
	}

	/* * This function is used to get the list view header in popup
 * Param $focus - module object
* Param $module - module name
* Param $sort_qry - sort by value
* Param $sorder - sorting order (asc/desc)
* Param $order_by - order by
* Returns the listview header values in an array
*/

	function getSearchListViewHeader ($focus, $module, $sort_qry = '', $sorder = '', $order_by = '') {
		global $log;
		$log->debug ("Entering getSearchListViewHeader(" . get_class ($focus) . "," . $module . "," . $sort_qry . "," . $sorder . "," . $order_by . ") method ...");
		global $adb;
		global $theme;
		global $app_strings;
		global $mod_strings, $current_user;
		$arrow       = '';
		$list_header = Array ();
		$tabid       = getTabid ($module);
		if (isset($_REQUEST['task_relmod_id'])) {
			$task_relmod_id = vtlib_purify ($_REQUEST['task_relmod_id']);
			$pass_url .= "&task_relmod_id=" . $task_relmod_id;
		}
		if (isset($_REQUEST['relmod_id'])) {
			$relmod_id = vtlib_purify ($_REQUEST['relmod_id']);
			$pass_url .= "&relmod_id=" . $relmod_id;
		}
		if (isset($_REQUEST['task_parent_module'])) {
			$task_parent_module = vtlib_purify ($_REQUEST['task_parent_module']);
			$pass_url .= "&task_parent_module=" . $task_parent_module;
		}
		if (isset($_REQUEST['parent_module'])) {
			$parent_module = vtlib_purify ($_REQUEST['parent_module']);
			$pass_url .= "&parent_module=" . $parent_module;
		}

		// vtlib Customization : For uitype 10 popup during paging
		if ($_REQUEST['form'] == 'vtlibPopupView') {
			$pass_url .= '&form=vtlibPopupView&forfield=' . vtlib_purify ($_REQUEST['forfield']) . '&srcmodule=' . vtlib_purify ($_REQUEST['srcmodule']) . '&forrecord=' . vtlib_purify ($_REQUEST['forrecord']);
		}
		// END
		//Added to reduce the no. of queries logging for non-admin user -- by Minnie-start
		$field_list = array ();
		$j          = 0;
		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');

		foreach ($focus->search_fields as $name => $tableinfo) {
			$fieldname = $focus->search_fields_name[ $name ];
			array_push ($field_list, $fieldname);
			$j++;
		}
		$field = Array ();
		if ($is_admin == false && $module != 'Users') {
			if ($module == 'Emails') {
				$query  = "SELECT fieldname FROM vtiger_field WHERE tabid = ? AND vtiger_field.presence IN (0,2)";
				$params = array ($tabid);
			} else {
				$profileList = getCurrentUserProfileList ();
				$query       = "SELECT DISTINCT vtiger_field.fieldname
					FROM vtiger_field
					INNER JOIN vtiger_profile2field
					ON vtiger_profile2field.fieldid = vtiger_field.fieldid
					INNER JOIN vtiger_def_org_field
					ON vtiger_def_org_field.fieldid = vtiger_field.fieldid
					WHERE vtiger_field.tabid = ?
					AND vtiger_profile2field.visible=0
					AND vtiger_def_org_field.visible=0
					AND vtiger_profile2field.profileid IN (" . generateQuestionMarks ($profileList) . ")
							AND vtiger_field.fieldname IN (" . generateQuestionMarks ($field_list) . ") AND vtiger_field.presence IN (0,2)";

				$params = array ($tabid, $profileList, $field_list);
			}

			$result = $adb->pquery ($query, $params);
			for ($k = 0; $k < $adb->num_rows ($result); $k++) {
				$field[] = $adb->query_result ($result, $k, "fieldname");
			}
		}
		//end
		$theme_path = "themes/" . $theme . "/";
		$image_path = $theme_path . "images/";

		$focus->filterInactiveFields ($module);

		foreach ($focus->search_fields as $name => $tableinfo) {
			$fieldname = $focus->search_fields_name[ $name ];
			$tabid     = getTabid ($module);

			global $current_user;
			$local_user = clone $current_user;
			require ('user_privileges/user_privileges.php');
			if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0 || in_array ($fieldname, $field) || $module == 'Users') {

				if (isset($focus->sortby_fields) && $focus->sortby_fields != '') {
					foreach ($focus->search_fields[ $name ] as $tab => $col) {
						if (in_array ($col, $focus->sortby_fields)) {
							if ($order_by == $col) {
								if ($sorder == 'ASC') {
									$sorder = "DESC";
									$arrow  = "<img src ='" . vtiger_imageurl ('arrow_down.gif', $theme) . "' border='0'>";
								} else {
									$sorder = 'ASC';
									$arrow  = "<img src ='" . vtiger_imageurl ('arrow_up.gif', $theme) . "' border='0'>";
								}
							}
							// vtlib customization: If translation is not available use the given name
							$tr_name = getTranslatedString ($name, $module);
							$name    = "<a href='javascript:;' onClick=\"getListViewSorted_js('" . $module . "','" . $sort_qry . $pass_url . "&order_by=" . $col . "&sorder=" . $sorder . "')\" class='listFormHeaderLinks'>" . $tr_name . "&nbsp;" . $arrow . "</a>";
							// END
							$arrow = '';
						} else {
							// vtlib customization: If translation is not available use the given name
							$tr_name = getTranslatedString ($name, $module);
							$name    = $tr_name;
							// END
						}
					}
				}
				$list_header[] = $name;
			}
		}
		$log->debug ("Exiting getSearchListViewHeader method ...");
		return $list_header;
	}

	/* * This function generates the navigation array in a listview
 * Param $display - start value of the navigation
* Param $noofrows - no of records
* Param $limit - no of entries per page
* Returns an array type
*/

	//code contributed by raju for improved pagination
	function getNavigationValues ($display, $noofrows, $limit) {
		global $log;
		$log->debug ("Entering getNavigationValues(" . $display . "," . $noofrows . "," . $limit . ") method ...");
		$navigation_array = Array ();
		global $limitpage_navigation;
		if (isset($_REQUEST['allflag']) && $_REQUEST['allflag'] == 'All') {
			$navigation_array['start']    = 1;
			$navigation_array['first']    = 1;
			$navigation_array['end']      = 1;
			$navigation_array['prev']     = 0;
			$navigation_array['next']     = 0;
			$navigation_array['end_val']  = $noofrows;
			$navigation_array['current']  = 1;
			$navigation_array['allflag']  = 'Normal';
			$navigation_array['verylast'] = 1;
			$log->debug ("Exiting getNavigationValues method ...");
			return $navigation_array;
		}
		if ($noofrows != 0) {
			if (((($display * $limit) - $limit) + 1) > $noofrows) {
				$display = floor ($noofrows / $limit);
			}
			$start = ((($display * $limit) - $limit) + 1);
		} else {
			$start = 0;
		}

		$end = $start + ($limit - 1);
		if ($end > $noofrows) {
			$end = $noofrows;
		}
		$paging = ceil ($noofrows / $limit);
		// Display the navigation
		if ($display > 1) {
			$previous = $display - 1;
		} else {
			$previous = 0;
		}
		if ($noofrows < $limit) {
			$first = '';
		} elseif ($noofrows != $limit) {
			$last  = $paging;
			$first = 1;
			if ($paging > $limitpage_navigation) {
				$first = $display - floor (($limitpage_navigation / 2));
				if ($first < 1) {
					$first = 1;
				}
				$last = ($limitpage_navigation - 1) + $first;
			}
			if ($last > $paging) {
				$first = $paging - ($limitpage_navigation - 1);
				$last  = $paging;
			}
		}
		if ($display < $paging) {
			$next = $display + 1;
		} else {
			$next = 0;
		}
		$navigation_array['start']    = $start;
		$navigation_array['first']    = $first;
		$navigation_array['end']      = $last;
		$navigation_array['prev']     = $previous;
		$navigation_array['next']     = $next;
		$navigation_array['end_val']  = $end;
		$navigation_array['current']  = $display;
		$navigation_array['allflag']  = 'All';
		$navigation_array['verylast'] = $paging;
		$log->debug ("Exiting getNavigationValues method ...");
		return $navigation_array;
	}

	//End of code contributed by raju for improved pagination

	/* * This function generates the List view entries in a list view
 * Param $focus - module object
* Param $list_result - resultset of a listview query
* Param $navigation_array - navigation values in an array
* Param $relatedlist - check for related list flag
* Param $returnset - list query parameters in url string
* Param $edit_action - Edit action value
* Param $del_action - delete action value
* Param $oCv - vtiger_customview object
* Returns an array type
*/

	//parameter added for vtiger_customview $oCv 27/5
	function getListViewEntries ($focus, $module, $list_result, $navigation_array, $relatedlist = '', $returnset = '', $edit_action = 'EditView', $del_action = 'Delete', $oCv = '', $page = '', $selectedfields = '', $contRelatedfields = '', $skipActions = false) {
    // Cache local para traducciones
    $translationCache = array();

		global $log;
		global $mod_strings, $modalEdit, $callbackEditModal, $currentModule, $clientView, $demoMode;
		$log->debug ("Entering getListViewEntries(" . get_class ($focus) . "," . $module . "," . $list_result . "," . $navigation_array . "," . $relatedlist . "," . $returnset . "," . $edit_action . "," . $del_action . "," . (is_object ($oCv) ? get_class ($oCv) : $oCv) . ") method ...");
		$tabname = getParentTab ();
		global $adb, $current_user;
		global $app_strings;
		$noofrows   = $adb->num_rows ($list_result);
		$list_block = Array ();
		global $theme;
		$evt_status = '';
		$theme_path = "themes/" . $theme . "/";
		$image_path = $theme_path . "images/";
		//getting the vtiger_fieldtable entries from database
		$tabid     = getTabid ($module);
		//added for vtiger_customview 27/5

		if ($oCv) {
			if (isset($oCv->list_fields)) {
				$focus->list_fields = $oCv->list_fields;
			}
		}
		if (is_array ($selectedfields) && $selectedfields != '') {
			$focus->list_fields = $selectedfields;
		}

		// Remove fields which are made inactive
		$focus->filterInactiveFields ($module);

		//Added to reduce the no. of queries logging for non-admin user -- by minnie-start
		$field_list = array ();
		$j          = 0;
		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');

		foreach ($focus->list_fields as $name => $tableinfo) {
			$fieldname = $focus->list_fields_name[ $name ];
			if ($oCv) {
				if (isset($oCv->list_fields_name)) {
					$fieldname = $oCv->list_fields_name[ $name ];
				}
			}
			array_push ($field_list, $fieldname);
			$j++;
		}
		$field = Array ();
		if ($is_admin == false) {
			if ($module == 'Emails') {
				$query  = "SELECT fieldname FROM vtiger_field WHERE tabid = ? AND vtiger_field.presence IN (0,2)";
				$params = array ($tabid);
			} else {
				$profileList = getCurrentUserProfileList ();
				$params      = array ();
				$query       = "SELECT DISTINCT vtiger_field.fieldname
					FROM vtiger_field
					INNER JOIN vtiger_profile2field
					ON vtiger_profile2field.fieldid = vtiger_field.fieldid
					INNER JOIN vtiger_def_org_field
					ON vtiger_def_org_field.fieldid = vtiger_field.fieldid";

				if ($module == "Calendar") {
					$query .= " WHERE vtiger_field.tabid in (9,16) and vtiger_field.presence in (0,2)";
				} else {
					$query .= " WHERE vtiger_field.tabid = ? and vtiger_field.presence in (0,2)";
					array_push ($params, $tabid);
				}

				$query .= " AND vtiger_profile2field.visible = 0
					AND vtiger_profile2field.visible = 0
					AND vtiger_def_org_field.visible = 0";
				if (!empty($profileList)) {
					$query .= " AND vtiger_profile2field.profileid IN (" . generateQuestionMarks ($profileList) . ")";
					array_push ($params, $profileList);
				}
				$query .= " AND vtiger_field.fieldname IN (" . generateQuestionMarks ($field_list) . ") ";
				array_push ($params, $field_list);
			}

			$result = $adb->pquery ($query, $params);
			for ($k = 0; $k < $adb->num_rows ($result); $k++) {
				$field[] = $adb->query_result ($result, $k, "fieldname");
			}
		}
		//constructing the uitype and columnname array
		$ui_col_array = Array ();

		$params = array ();
		$query  = "SELECT fieldid,uitype, columnname, fieldname FROM vtiger_field ";

		if ($module == "Calendar") {
			$query .= " WHERE vtiger_field.tabid in (SELECT tabid FROM vtiger_tab WHERE name='Calendar') and vtiger_field.presence in (0,2)";
		} else {
			$query .= " WHERE vtiger_field.tabid = ? and vtiger_field.presence in (0,2,-1)";//Campos tipo matriz
			array_push ($params, $tabid);
		}
		$query .= " AND fieldname IN (" . generateQuestionMarks ($field_list) . ") ";
		array_push ($params, $field_list);

		$result   = $adb->pquery ($query, $params);
		$num_rows = $adb->num_rows ($result);
		for ($i = 0; $i < $num_rows; $i++) {
			$tempArr                     = array ();
			$uitype                      = $adb->query_result ($result, $i, 'uitype');
			$columnname                  = $adb->query_result ($result, $i, 'columnname');
			$field_name                  = $adb->query_result ($result, $i, 'fieldname');
			$fieldid[ $field_name ]      = $adb->query_result ($result, $i, 'fieldid');
			$tempArr[ $uitype ]          = $columnname;
			$ui_col_array[ $field_name ] = $tempArr;
		}
		//end
		if ($navigation_array['start'] != 0) {
			for ($i = 1; $i <= $noofrows; $i++) {
				$list_header = Array ();
				//Getting the entityid
				if (isset($focus->entityid)) {
					$entity_id = $adb->query_result ($list_result, $i - 1, $focus->entityid);
				} else if ($module != 'Users') {
					$entity_id = $adb->query_result ($list_result, $i - 1, "crmid");
					$owner_id  = $adb->query_result ($list_result, $i - 1, "smownerid");
				} else {
					$entity_id = $adb->query_result ($list_result, $i - 1, "id");
				}
				// Fredy Klammsteiner, 4.8.2005: changes from 4.0.1 migrated to 4.2
				// begin: Armando Lüscher 05.07.2005 -> §priority
				// Code contri buted by fredy Desc: Set Priority color
				$priority = $adb->query_result ($list_result, $i - 1, "priority");

				$font_color_high   = "color:#00DD00;";
				$font_color_medium = "color:#DD00DD;";
				$P_FONT_COLOR      = "";
				switch ($priority) {
					case 'High':
						$P_FONT_COLOR = $font_color_high;
						break;
					case 'Medium':
						$P_FONT_COLOR = $font_color_medium;
						break;
					default:
						$P_FONT_COLOR = "";
				}
				//end: Armando Lüscher 05.07.2005 -> §priority
				foreach ($focus->list_fields as $name => $tableinfo) {
					$fieldname = $focus->list_fields_name[ $name ];

					// EGC para los uitype = 10, no hay que entrar a chequear el nombre del campo mas abajo
					$keys = $ui_col_array[ $fieldname ];
					if (is_array ($keys)) {
						list($uitype) = array_keys ($keys);
					}
					//added for vtiger_customview 27/5
					if ($oCv) {
						if (isset($oCv->list_fields_name)) {
							$fieldname = $oCv->list_fields_name[ $name ];
						} else {
							$fieldname = $focus->list_fields_name[ $name ];
						}
					} else {
						$fieldname = $focus->list_fields_name[ $name ];
					}
					if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0 || in_array ($fieldname, $field) || $fieldname == '' || ($name == 'Close' && $module == 'Calendar')) {
						if ($fieldname == '') {
							$table_name  = '';
							$column_name = '';
							foreach ($tableinfo as $tablename => $colname) {
								$table_name  = $tablename;
								$column_name = $colname;
							}
							$value = $adb->query_result ($list_result, $i - 1, $colname);
						} else {
							if (($module == 'Calendar') && ($fieldname == 'progress')) {
								$value = intval ($adb->query_result ($list_result, $i - 1, $fieldname));
							} else if ($module == "Documents" && ($fieldname == 'filelocationtype' || $fieldname == 'filename' || $fieldname == 'filesize' || $fieldname == 'filestatus' || $fieldname == 'filetype')) {
								$value = $adb->query_result ($list_result, $i - 1, $fieldname);
								if ($fieldname == 'filelocationtype') {
									if ($value == 'I') {
                                            $cacheKey = 'LBL_INTERNAL|' . $module;
                                            if (isset($translationCache[$cacheKey])) {
                                                $value = $translationCache[$cacheKey];
                                            } else {
                                                $value = getTranslatedString('LBL_INTERNAL', $module);
                                                $translationCache[$cacheKey] = $value;
                                            }
                                        } elseif ($value == 'E') {
                                            $cacheKey = 'LBL_EXTERNAL|' . $module;
                                            if (isset($translationCache[$cacheKey])) {
                                                $value = $translationCache[$cacheKey];
                                            } else {
                                                $value = getTranslatedString('LBL_EXTERNAL', $module);
                                                $translationCache[$cacheKey] = $value;
                                            }
                                        } else {
										$value = ' --';
									}
								}
								if ($fieldname == 'filename') {
									$downloadtype = $adb->query_result ($list_result, $i - 1, 'filelocationtype');
									if ($downloadtype == 'I') {
										$fld_value = $value;
										$ext_pos   = strrpos ($fld_value, ".");
										$ext       = substr ($fld_value, $ext_pos + 1);
										$ext       = strtolower ($ext);
										if ($value != '') {
											if ($ext == 'bin' || $ext == 'exe' || $ext == 'rpm') {
												$fileicon = "<img src='" . vtiger_imageurl ('fExeBin.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
											} elseif ($ext == 'jpg' || $ext == 'gif' || $ext == 'bmp') {
												$fileicon = "<img src='" . vtiger_imageurl ('fbImageFile.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
											} elseif ($ext == 'txt' || $ext == 'doc' || $ext == 'xls') {
												$fileicon = "<img src='" . vtiger_imageurl ('fbTextFile.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
											} elseif ($ext == 'zip' || $ext == 'gz' || $ext == 'rar') {
												$fileicon = "<img src='" . vtiger_imageurl ('fbZipFile.gif', $theme) . "' hspace='3' align='absmiddle'	border='0'>";
											} else {
												$fileicon = "<img src='" . vtiger_imageurl ('fbUnknownFile.gif', $theme) . "' hspace='3' align='absmiddle' border='0'>";
											}
										}
									} elseif ($downloadtype == 'E') {
										if (trim ($value) != '') {
											$fld_value = $value;
											$cacheKey = 'LBL_EXTERNAL_LNK|' . $module;
											if (isset($translationCache[$cacheKey])) {
												$extLnkLabel = $translationCache[$cacheKey];
											} else {
												$extLnkLabel = getTranslatedString('LBL_EXTERNAL_LNK', $module);
												$translationCache[$cacheKey] = $extLnkLabel;
											}
											$fileicon  = "<img src='" . vtiger_imageurl ('fbLink.gif', $theme) . "' alt='" . $extLnkLabel . "' title='" . $extLnkLabel . "' hspace='3' align='absmiddle' border='0'>";
										} else {
											$fld_value = '--';
											$fileicon  = '';
										}
									} else {
										$fld_value = ' --';
										$fileicon  = '';
									}

									$file_name     = $adb->query_result ($list_result, $i - 1, 'filename');
									$notes_id      = $adb->query_result ($list_result, $i - 1, 'crmid');
									$folder_id     = $adb->query_result ($list_result, $i - 1, 'folderid');
									$download_type = $adb->query_result ($list_result, $i - 1, 'filelocationtype');
									$file_status   = $adb->query_result ($list_result, $i - 1, 'filestatus');
									$fileidQuery   = "SELECT attachmentsid FROM vtiger_seattachmentsrel WHERE crmid=?";
									$fileidres     = $adb->pquery ($fileidQuery, array ($notes_id));
									$fileid        = $adb->query_result ($fileidres, 0, 'attachmentsid');
									if ($file_name != '' && $file_status == 1) {
										if ($download_type == 'I') {
											$cacheKey = 'LBL_DOWNLOAD_FILE|' . $module;
											if (isset($translationCache[$cacheKey])) {
												$downloadFileLabel = $translationCache[$cacheKey];
											} else {
												$downloadFileLabel = getTranslatedString('LBL_DOWNLOAD_FILE', $module);
												$translationCache[$cacheKey] = $downloadFileLabel;
											}
											$fld_value = "<a href='index.php?module=uploads&action=downloadfile&entityid=$notes_id&fileid=$fileid' title='" . $downloadFileLabel . "' onclick='javascript:dldCntIncrease($notes_id);'>" . textlength_check ($fld_value) . "</a>";
																					} elseif ($download_type == 'E') {
																						$cacheKey = 'LBL_DOWNLOAD_FILE|' . $module;
											if (isset($translationCache[$cacheKey])) {
												$downloadFileLabel = $translationCache[$cacheKey];
											} else {
												$downloadFileLabel = getTranslatedString('LBL_DOWNLOAD_FILE', $module);
												$translationCache[$cacheKey] = $downloadFileLabel;
											}
											$fld_value = "<a target='_blank' href='$file_name' onclick='javascript:dldCntIncrease($notes_id);' title='" . $downloadFileLabel . "'>" . textlength_check ($fld_value) . "</a>";
										} else {
											$fld_value = ' --';
										}
									}
									$value = $fileicon . $fld_value;
								}
								if ($fieldname == 'filesize') {
									$downloadtype = $adb->query_result ($list_result, $i - 1, 'filelocationtype');
									if ($downloadtype == 'I') {
										$filesize = $value;
										if ($filesize < 1024) {
											$value = $filesize . ' B';
										} elseif ($filesize > 1024 && $filesize < 1048576) {
											$value = round ($filesize / 1024, 2) . ' KB';
										} else if ($filesize > 1048576) {
											$value = round ($filesize / (1024 * 1024), 2) . ' MB';
										}
									} else {
										$value = ' --';
									}
								}
								if ($fieldname == 'filestatus') {
									$filestatus = $value;
									if ($filestatus == 1) {
                                            $cacheKey = 'yes|' . $module;
                                            if (isset($translationCache[$cacheKey])) {
                                                $value = $translationCache[$cacheKey];
                                            } else {
                                                $value = getTranslatedString('yes', $module);
                                                $translationCache[$cacheKey] = $value;
                                            }
                                        } elseif ($filestatus == 0) {
                                            $cacheKey = 'no|' . $module;
                                            if (isset($translationCache[$cacheKey])) {
                                                $value = $translationCache[$cacheKey];
                                            } else {
                                                $value = getTranslatedString('no', $module);
                                                $translationCache[$cacheKey] = $value;
                                            }
                                        } else {
										$value = ' --';
									}
								}
								if ($fieldname == 'filetype') {
									$downloadtype = $adb->query_result ($list_result, $i - 1, 'filelocationtype');
									$filetype     = $adb->query_result ($list_result, $i - 1, 'filetype');
									if ($downloadtype == 'E' || $downloadtype != 'I') {
										$value = ' --';
									} else {
										$value = $filetype;
									}
								}
								if ($fieldname == 'notecontent') {
									$value = decode_html ($value);
									$value = textlength_check ($value);
								}
							} elseif ($name == 'Product') {
								$product_id = textlength_check ($adb->query_result ($list_result, $i - 1, "productname"));
								$value      = $product_id;
							} else {
								$list_result_count = $i - 1;
								$value             = getValue ($ui_col_array, $list_result, $fieldname, $focus, $module, $entity_id, $list_result_count, "list", "", $returnset, $oCv->setdefaultviewid);
							}
						}

						if ($demoMode) {
							global $demoModeStyle;
							$value = '<span style="' . $demoModeStyle . '">' . $value . '</span>';
						}

						// vtlib customization: For listview javascript triggers
//						$value = "$value <span type='vtlib_metainfo' vtrecordid='{$entity_id}' vtfieldname='{$fieldname}' vtmodule='$module' style='display:none;'></span>";
						// END

						if ($module == "Calendar" && $name == $app_strings['Close']) {
							if (isPermitted ("Calendar", "EditView") == 'yes') {
								if ((getFieldVisibilityPermission ('Events', $current_user->id, 'eventstatus') == '0') || (getFieldVisibilityPermission ('Calendar', $current_user->id, 'taskstatus') == '0')) {
									array_push ($list_header, $value);
								}
							}
						} else {
							$list_header[ $fieldname ] = $value;
						}
					}
				}
				$varreturnset = '';
				if ($returnset == '') {
					$varreturnset = '&return_module=' . $module . '&return_action=index';
				} else {
					$varreturnset = $returnset;
				}

				if ($module == 'Calendar') {
					$actvity_type = $adb->query_result ($list_result, $list_result_count, 'activitytype');
					if ($actvity_type == 'Task') {
						$varreturnset .= '&activity_mode=Task';
					} else {
						$varreturnset .= '&activity_mode=Events';
					}
				}

				//Added for Actions ie., edit and delete links in listview
				$links_info = "";
				if (!(is_array ($selectedfields) && $selectedfields != '')) {
					if (isPermitted ($module, "EditView", "") == 'yes') {
						$edit_link = getListViewEditLink ($module, $entity_id, $relatedlist, $varreturnset, $list_result, $list_result_count);
						if ($module == 'Calendar') {
							$finaleditlink = "<a href=\"$edit_link\"><i class=\"fa fa-pencil listview-controller btn btn-link\"></i></a>";
						} elseif (isset($_REQUEST['start']) && $_REQUEST['start'] > 1 && $module != 'Emails') {
							$finaleditlink = "<li><a href=\"$edit_link&start=" . vtlib_purify ($_REQUEST['start']) . "\"><i class=\"fa fa-pencil\"></i>" . $app_strings["LNK_EDIT"] . "</a></li>";
						} else {
							/*$finaleditlink = '<li><a href="'.$edit_link.'" class="table-link">
							<span class="fa-stack">
							<i class="fa fa-square fa-stack-2x"></i>
							<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
							</span>
										</a></li>';/**/
							if ($module == 'Calendar') {
								$finaleditlink = "<a data-platzilla=\"SBE{$i}\" href=\"$edit_link\"><i class=\"fa fa-pencil listview-controller btn btn-link\"></i></a>";
							} else {
								$finaleditlink = "<a data-platzilla=\"SBE{$i}\" class=\"table-link\" href=\"$edit_link\"><span class=\"fa-stack\"><i class=\"fa fa-square fa-stack-2x\"></i><i class=\"fa fa-pencil fa-stack-1x fa-inverse\"></i></span></a>";
							}
						}
						if (isset($modalEdit) && (int) $modalEdit == 1) {
							$relationId = @$_REQUEST['relation_id'];
							if ($relationId) {
								$fn = "loadModalEditUI('$entity_id', '$module', $relationId, '" . $_REQUEST['record'] . "', '$currentModule', '" . $_REQUEST['header'] . "', '" . $_REQUEST['actions'] . "')";
							} else {
								$fn = "loadModalEditUI('$entity_id', '$module', null, null, null)";
							}

							/*$finaleditlink = '<li><a href="javascript:'.$fn.'" class="table-link">
											<span class="fa-stack">
												<i class="fa fa-square fa-stack-2x"></i>
												<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
											</span>
										</a></li>';/**/
							$finaleditlink = "<li><a href=\"javascript:void(0);\" onclick=\"$fn\"><i class=\"fa fa-pencil\"></i>" . $app_strings["LNK_EDIT"] . "</a></li>";
						}

						$links_info .= $finaleditlink;
					}

					if (isPermitted ($module, "Delete", "") == 'yes') {
						$del_link = getListViewDeleteLink ($module, $entity_id, $relatedlist, $varreturnset);

						if ($links_info != "" && $del_link != "") {
							//$links_info .= " | ";
							if ($del_link != "") {
								if ($module == 'Calendar') {
									$finalLink = '<a data-platzilla="SBD'.$i.'" class="table-link danger" href="javascript:confirmdelete(&quot;' . addslashes (urlencode ($del_link)) . '&quot;,' . $entity_id . ')"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa fa-trash-o fa-stack-1x fa-inverse"></i></span></a>';
								} else {
									$finalLink = '<a data-platzilla="SBD'.$i.'" class="table-link danger" href="javascript:confirmdelete(&quot;' . addslashes (urlencode ($del_link)) . '&quot;,' . $entity_id . ')"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa fa-trash-o fa-stack-1x fa-inverse"></i></span></a>';
								}
							}
						}
						if (isset($modalEdit) && (int) $modalEdit == 1) {
							$relationId = @$_REQUEST['relation_id'];
							if ($relationId) {
								$fn = "deleteAjax('$del_link', '$currentModule', '$module', $relationId, '" . $_REQUEST['header'] . "', '" . $_REQUEST['actions'] . "', '" . $_REQUEST['record'] . "')";
							} else {
								$fn = "deleteAjax('$del_link', null, null, null)";
							}
							/*$finalLink = "<li>
										<a href='javascript:".$fn."' class='table-link danger'>
											<span class='fa-stack'>
												<i class='fa fa-square fa-stack-2x'></i>
												<i class='fa fa-trash-o fa-stack-1x fa-inverse'></i>
											</span>
										</a>
									</li>";/**/
							$finalLink = "<li><a href=\"javascript:void(0);\" onclick=\"$fn\"><i class='fa fa-trash-o'></i>" . $app_strings["LNK_DELETE"] . "</a></li>";
						}

						$links_info .= $finalLink;
					}
				}
				if (method_exists ($focus, 'customButtons')) {
					$links_info .= $focus->customButtons ($entity_id);
				}

				//Boton de acciones para el listview
				if ($_REQUEST['action'] != 'UnifiedSearch') {
					$links_info .= getActionButton ($entity_id, $focus);
					$links_info .= getRelateListButton ($module, $entity_id);
				}

				// Record Change Notification
				/********************************* No se migra al nuevo tema | MA *******************************/
				/*
			if (method_exists($focus, 'isViewed') && PerformancePrefs::getBoolean('LISTVIEW_RECORD_CHANGE_INDICATOR', true) && !$clientView) {
		if (!$focus->isViewed($entity_id)) {
		$links_info .= " | <img src='" . vtiger_imageurl('important1.gif', $theme) . "' border=0>";
		}
		}
		*/
				// END
				if ($links_info != "" && !$skipActions) {
					$list_header[] = $links_info;
				}
				//Color background
				if (method_exists ($focus, 'getColorRow')) {
					$list_block[ $entity_id ]['records']     = $list_header;
					$list_block[ $entity_id ]['color']       = $focus->getColorRow ($entity_id);
					$list_block[ $entity_id ]['onmouseover'] = $focus->getOnMouseOverRow ($entity_id);
					$list_block[ $entity_id ]['onmouseout']  = $focus->getOnMouseOutRow ($entity_id);
				} else {
					$list_block[ $entity_id ]['records'] = $list_header;
				}

				$list_block[ $entity_id ]['fields_attributes'] = getFieldAttributes ($field, $entity_id, $focus);
			}
		}
		$log->debug ("Exiting getListViewEntries method ...");
		return $list_block;
	}

	function getActionButton ($entity_id, $focus) {
		global $mod_strings, $modalEdit, $callbackEditModal, $currentModule, $clientView, $demoMode, $adb, $current_user, $app_strings;

		$smarty = new vtigerCRM_Smarty;
		$smarty->assign ("MOD", $mod_strings);
		$smarty->assign ("APP", $app_strings);

		$smarty->assign ("THEME", $theme);
		$smarty->assign ("IMAGE_PATH", $image_path);

		if (isset($focus->name)) {
			$smarty->assign ("NAME", $focus->name);
		} else {
			$smarty->assign ("NAME", "");
		}

		$smarty->assign ('MOD_SEQ_ID', $mod_seq_id);

		$smarty->assign ("CUSTOMFIELD", $cust_fld);
		$smarty->assign ("ID", $entity_id);
		$smarty->assign ("SINGLE_MOD", $currentModule);
		$category = getParentTab ();
		$smarty->assign ("CATEGORY", $category);

		if (isPermitted ("Emails", "EditView", '') == 'yes') {
			$vtwsObject        = VtigerWebserviceObject::fromName ($adb, $currentModule);			
			$vtwsCRMObjectMeta = new VtigerCRMObjectMeta($vtwsObject, $current_user);
			
			// [PLATZILLA PERFORMANCE] Cachear resultado de getEmailFields por módulo para evitar consultas repetidas
			static $emailFieldsCache = array();
			if (!isset($emailFieldsCache[$currentModule])) {
				$emailFieldsCache[$currentModule] = $vtwsCRMObjectMeta->getEmailFields();
			}
			$emailFields = $emailFieldsCache[$currentModule];
			$smarty->assign ("SENDMAILBUTTON", "permitted");
			$emails = array ();
			foreach ($emailFields as $key => $value) {
				$emails[] = $value;
			}
			$smarty->assign ("EMAILS", $emails);
			$cond      = "LTrim('%s') !=''";
			$condition = array ();
			foreach ($emails as $key => $value) {
				$condition[] = sprintf ($cond, $value);
			}
			$condition_str = implode ("||", $condition);
			$js            = "if(" . $condition_str . "){fnvshobj(this,'sendmail_cont');sendmail('" . $currentModule . "'," . $entity_id . ");}else{OpenCompose('','create');}";

			$smarty->assign ('JS', $js);
		}
		include_once ('vtlib/Vtiger/Link.php');
		$customlink_params = Array ('MODULE' => $currentModule, 'RECORD' => $entity_id, 'ACTION' => vtlib_purify ($_REQUEST['action']));
		$smarty->assign ('CUSTOM_LINKS', Vtiger_Link::getAllByType (getTabid ($currentModule), Array ('DETAILVIEWBASIC', 'DETAILVIEW', 'DETAILVIEWWIDGET'), $customlink_params));

		$smarty->assign ("TODO_PERMISSION", CheckFieldPermission ('parent_id', 'Calendar'));
		$smarty->assign ("EVENT_PERMISSION", CheckFieldPermission ('parent_id', 'Events'));
		$actions = $smarty->fetch ('DetailViewActions.tpl');
		return $actions;
	}

	function getRelateListButton ($module, $entity_id) {
		global $mod_strings, $modalEdit, $callbackEditModal, $currentModule, $clientView, $demoMode, $adb, $current_user, $app_strings, $plat;
		//$relatedlist        = isPresentRelatedLists ($module);
		$relatedlist        = null;
		$customActions      = PlatformUtils::getCustomButtons ($adb, $module, 'ActionButton', array ('record' => $entity_id, 'action' => 'ListView', 'module' => $module, 'isActionButton' => 1));
		$editableFields     = EditableFieldsHelper::fetchEditableButtonsByModule($adb, $module, $entity_id, $app_strings);
		$totalEditableField = count ($editableFields);

		if (is_array ($relatedlist) || count ($customActions) || $totalEditableField) {
			$bufferSalida = '</i>
				<div id="btn-group-'.$entity_id.'"  class="btn-group btn-list-group">
				<button type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown" style="padding: 0px 5px;">
				<i class="fa">&nbsp;...&nbsp;</i><span class="caret"></span>
				</button>
				<ul id="ul-group-'.$entity_id.'" class="dropdown-menu" role="menu" style="left: -120px;">';
			if (is_array ($relatedlist)) {
				foreach ($relatedlist as $k => $r) {
					$bufferSalida .= '<li><a href="index.php?action=CallRelatedList&module=' . $module . '&record=' . $entity_id . '&parenttab=&selected_header=' . $r . '&relation_id=' . $k . '&platdb=">' . getTranslatedString($r) . '</a></li>';
				}
			}
			if (count ($customActions)) {
				foreach ($customActions as $customAction) {
					$newwindow = '';
					if ($customAction['runinnewwindow']) {
						$newwindow = "target='_blank'";
					}
					$bufferSalida .= "<li><a href='{$customAction['link']}'  title='{$customAction['description']}' {$newwindow} >{$customAction['label']}</a> </li>";
				}
			}

			if ($totalEditableField) {
				foreach ($editableFields as $editableField) {
					$bufferSalida .= $editableField;

				}
			}
			$bufferSalida .= '</ul>
				</div>';
		}
		return $bufferSalida;
	}

	function getFieldAttributes ($field, $entity_id, $focus) {
		global $adb;
		if (!existeTabla ('vtiger_field_attribute') || empty($field)) {
			return null;
		}
		$ret = array ();
		$sql = "SELECT vfa.*,vf.fieldname,vf2.fieldname AS first_fieldname,vf2.`tablename` AS first_table,
			vf3.fieldname AS second_fieldname,vf3.`tablename` AS second_table
			FROM vtiger_field vf
			INNER JOIN `vtiger_field_attribute` vfa ON vfa.fieldid=vf.fieldid
			LEFT JOIN vtiger_field vf2 ON vf2.fieldid=vfa.first_fieldid
			LEFT JOIN vtiger_field vf3 ON vf3.fieldid=vfa.second_fieldid
			WHERE vf.fieldname IN ('" . implode ("','", $field) . "')";
		$q   = $adb->query ($sql);
		if ($adb->num_rows ($q) <= 0) {
			return null;
		}
		$addcrmentity = "";
		while ($r = $adb->fetchByAssoc ($q)) {
			$ret[ $r['fieldname'] ] = $r;
			if ($r['first_fieldname']) {
				$columns[ $r['first_fieldname'] ] = $r['first_table'] . "." . $r['first_fieldname'];
			}
			if ($r['second_fieldname']) {
				$columns[ $r['second_fieldname'] ] = $r['second_table'] . "." . $r['second_fieldname'];
			}
			if ($r['first_table'] != 'vtiger_crmentity' && $r['second_table'] != 'vtiger_crmentity') {
				$tables[ $r['first_table'] ]  = $r['first_table'];
				$tables[ $r['second_table'] ] = $r['second_table'];
			} else {
				$addcrmentity      = " and vtiger_crmentity.crmid=" . $entity_id;
				$addtablecrmentity = ",vtiger_crmentity";
			}
		}
		//Busca todas las columnas, en caso de condiciones de columnas ocultas

		$columns  = implode (",", array_filter ($columns));
		$tables_n = implode (",", array_filter ($tables));
		$tables_e = implode ("." . $focus->table_index . "=" . $entity_id . " and ", array_filter ($tables)) . "." . $focus->table_index . "=" . $entity_id . " " . $addcrmentity;

		$sql = "select " . $columns . " from " . $tables_n . $addtablecrmentity . " where " . $tables_e;
		$q   = $adb->query ($sql);
		$acc = $adb->fetchByAssoc ($q);
		foreach ($ret as $fld => $d) {
			switch ($d['condition']) {
				case 'empty':
					if (!$acc[ $d['first_fieldname'] ]) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case 'not empty':
					if ($acc[ $d['first_fieldname'] ]) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case 'like':
					if (($d['value'] != '' && preg_match ("/" . $d['value'] . "/i", $acc[ $d['first_fieldname'] ])) || ($acc[ $d['second_fieldname'] ] != '' && preg_match ("/" . $acc[ $d['second_fieldname'] ] . "/i", $acc[ $d['first_fieldname'] ]))) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case 'not like':
					if (($d['value'] != '' && !preg_match ("/" . $d['value'] . "/i", $acc[ $d['first_fieldname'] ])) || ($acc[ $d['second_fieldname'] ] != '' && !preg_match ("/" . $acc[ $d['second_fieldname'] ] . "/i", $acc[ $d['first_fieldname'] ]))) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case '==':
				case 'equal':
					if (($d['value'] != '' && $acc[ $d['first_fieldname'] ] == $d['value']) || ($acc[ $d['second_fieldname'] ] != '' && $acc[ $d['first_fieldname'] ] == $acc[ $d['second_fieldname'] ])) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case '>':
					if (($d['value'] != '' && $acc[ $d['first_fieldname'] ] > $d['value']) || ($acc[ $d['second_fieldname'] ] != '' && $acc[ $d['first_fieldname'] ] > $acc[ $d['second_fieldname'] ])) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case '>=':
					if (($d['value'] != '' && $acc[ $d['first_fieldname'] ] >= $d['value']) || ($acc[ $d['second_fieldname'] ] != '' && $acc[ $d['first_fieldname'] ] >= $acc[ $d['second_fieldname'] ])) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case '<':
					if (($d['value'] != '' && $acc[ $d['first_fieldname'] ] < $d['value']) || ($acc[ $d['second_fieldname'] ] != '' && $acc[ $d['first_fieldname'] ] < $acc[ $d['second_fieldname'] ])) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case '<=':
					if (($d['value'] != '' && $acc[ $d['first_fieldname'] ] <= $d['value']) || ($acc[ $d['second_fieldname'] ] != '' && $acc[ $d['first_fieldname'] ] <= $acc[ $d['second_fieldname'] ])) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case '!=':
					if (($d['value'] != '' && $acc[ $d['first_fieldname'] ] != $d['value']) || ($acc[ $d['second_fieldname'] ] != '' && $acc[ $d['first_fieldname'] ] != $acc[ $d['second_fieldname'] ])) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case 'between':
					$d['value']                    = array_filter (explode (',', $d['value']));
					$acc[ $d['second_fieldname'] ] = array_filter (explode (',', $acc[ $d['second_fieldname'] ]));
					if ((!empty($d['value']) && ($acc[ $d['first_fieldname'] ] >= $d['value'][0] || $acc[ $d['first_fieldname'] ] <= $d['value'][1]))
						|| (!empty($acc[ $d['second_fieldname'] ]) && ($acc[ $d['first_fieldname'] ] >= $acc[ $d['second_fieldname'] ][0] || $acc[ $d['first_fieldname'] ] <= $acc[ $d['second_fieldname'] ][1]))
					) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case 'not between':
					$d['value']                    = array_filter (explode (',', $d['value']));
					$acc[ $d['second_fieldname'] ] = array_filter (explode (',', $acc[ $d['second_fieldname'] ]));
					if ((!empty($d['value']) && ($acc[ $d['first_fieldname'] ] < $d['value'][0] || $acc[ $d['first_fieldname'] ] > $d['value'][1]))
						|| (!empty($acc[ $d['second_fieldname'] ]) && ($acc[ $d['first_fieldname'] ] < $acc[ $d['second_fieldname'] ][0] || $acc[ $d['first_fieldname'] ] > $acc[ $d['second_fieldname'] ][1]))
					) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case 'before date':
					if (strtotime ($acc[ $d['first_fieldname'] ]) < strtotime ($d['value'])) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case 'after date':
					if (strtotime ($acc[ $d['first_fieldname'] ]) >= strtotime ($d['value'])) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
				case 'date':
					if (strtotime (date ('Y-m-d', strtotime ($acc[ $d['first_fieldname'] ]))) == strtotime ($d['value'])) {
						$ret[ $fld ]['bgcolor'] = true;
					}
					break;
			}
		}
		return $ret;
	}

	/* * This function generates the value for a given vtiger_field namee
 * Param $field_result - vtiger_field result in array
* Param $list_result - resultset of a listview query
* Param $fieldname - vtiger_field name
* Param $focus - module object
* Param $module - module name
* Param $entity_id - entity id
* Param $list_result_count - list result count
* Param $mode - mode type
* Param $popuptype - popup type
* Param $returnset - list query parameters in url string
* Param $viewid - custom view id
* Returns an string value
*/

	function getValue ($field_result, $list_result, $fieldname, $focus, $module, $entity_id, $list_result_count, $mode, $popuptype, $returnset = '', $viewid = '') {
		global $log, $listview_max_textlength, $app_strings, $current_language, $currentModule;
		$log->debug ("Entering getValue(" . $field_result . "," . $list_result . "," . $fieldname . "," . get_class ($focus) . "," . $module . "," . $entity_id . "," . $list_result_count . "," . $mode . "," . $popuptype . "," . $returnset . "," . $viewid . ") method ...");
		global $adb, $current_user, $default_charset;

		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');
		$tabname                = getParentTab ();
		$tabid                  = getTabid ($module);
		$current_module_strings = return_module_language ($current_language, $module);

		//Se chequea si contiene la palabra get_related_list que significa que es una lista relacionada
		if (strstr ($fieldname, 'get_related_list')) {
			list($funcion, $relmodule) = explode ('|', $fieldname);
			$value = getRelatedEntities ($entity_id, $module, $relmodule);
		} else {
			$uicolarr = $field_result[ $fieldname ];
			if (!is_array ($uicolarr)) {
				return;
			}
			foreach ($uicolarr as $key => $value) {
				$uitype  = $key;
				$colname = $value;
			}
			//added for getting event status in Custom view - Jaguar
			if ($module == 'Calendar' && $colname == "status") {
				$colname = "activitystatus";
			}
			//Ends
			$field_val = $adb->query_result ($list_result, $list_result_count, $colname);
			if ($uitype != 8) {
				$temp_val = html_entity_decode ($field_val, ENT_QUOTES, $default_charset);
			} else {
				$temp_val = $field_val;
			}

			// vtlib customization: New uitype to handle relation between modules
			if ($uitype == 10 || $uitype == 404) {
				$parent_id = $field_val;
				if (!empty($parent_id)) {
					$parent_module = getSalesEntityType ($parent_id);
					$valueTitle    = $parent_module;
					if ($app_strings[ $valueTitle ]) {
						$valueTitle = $app_strings[ $valueTitle ];
					}

					$displayValueArray = getEntityName ($parent_module, $parent_id);
					if (!empty($displayValueArray)) {
						foreach ($displayValueArray as $key => $value) {
							$value = $value;
						}
					}
					$value = "<a href='index.php?module=$parent_module&action=DetailView&record=$parent_id' title='$valueTitle'>" . textlength_check ($value) . "</a>";
				} else {
					$value = '';
				}
			} // END
			else if ($uitype == 53) {
				$value = $adb->query_result ($list_result, $list_result_count, 'user_name');
				// When Assigned To field is used in Popup window
				if ($value == '') {
					$user_id = $adb->query_result ($list_result, $list_result_count, 'smownerid');
					if ($user_id != null && $user_id != '') {
						$value = getOwnerName ($user_id);
						$value = textlength_check ($value);
					}
				}
			} elseif ($uitype == 52) {
				$value = getOwnerName ($adb->query_result ($list_result, $list_result_count, $colname));
				$value = textlength_check ($value);
			} elseif ($uitype == 50) {//Accounts - Member Of
				$relatedto = $adb->query_result ($list_result, $list_result_count, $colname);

				$entity_name = textlength_check (getAccountName ($relatedto));
				$value       = '<a href="index.php?module=' . $module . '&action=DetailView&record=' . $relatedto . '&parenttab=' . $tabname . '" style="' . $P_FONT_COLOR . '">' . $entity_name . '</a>';
			} elseif ($uitype == 51) {//Accounts - Member Of
				$parentid = $adb->query_result ($list_result, $list_result_count, "parentid");

				if (!isset($parentid)) {
					$parentid = $adb->query_result ($list_result, $list_result_count, "parent_id");
				}
				$entity_name = textlength_check (getAccountName ($parentid));
				$value = '<a href="index.php?module=' . $module . '&action=DetailView&record=' . $parentid . '&parenttab=' . $tabname . '" style="' . $P_FONT_COLOR . '">' . $entity_name . '</a>';
			} elseif ($uitype == 77) {
				$value = getOwnerName ($adb->query_result ($list_result, $list_result_count, 'inventorymanager'));
				$value = textlength_check ($value);
			} elseif ($uitype == 5 || $uitype == 6 || $uitype == 23 || $uitype == 70) {
				$temp_val  = trim ($temp_val);
				$timeField = 'time_start';
				if ($fieldname == 'due_date') {
					$timeField = 'time_end';
				}
				if ($temp_val != '' && $module == 'Calendar' && ($uitype == 23 || $uitype == 6) &&
					$timeField != '' && ($fieldname == 'date_start' || $fieldname == 'due_date')
				) {
					$time = $adb->query_result ($list_result, $list_result_count, $timeField);
					if (empty($time)) {
						$time = getSingleFieldValue ('vtiger_activity', $timeField, 'activityid', $entity_id);
					}
				}
				if ($temp_val == '0000-00-00' || empty($temp_val)) {
					$value = '';
				} else {
					if (empty($time) && strpos ($temp_val, ' ') == false) {
						$value = DateTimeField::convertToUserFormat ($temp_val);
					} else {
						if (!empty($time)) {
							if (strlen ($time) < 5) {
								$time = '00:00';
							}
							if (strlen ($temp_val) > 10) {
								$date = new DateTimeField($temp_val);
							} else {
								$date = new DateTimeField("{$temp_val} {$time}");
							}
							$value = $date->getDisplayDate ();
						} else {
							$date  = new DateTimeField($temp_val);
							$value = $date->getDisplayDateTimeValue ();
						}
					}
					if ($_REQUEST['ajxaction'] == 'LOADRELATEDLIST') {
						$value = '<a href="index.php?module=' . $module . '&action=DetailView&record=' . $entity_id . '">' . $value . '</a>';
					}
				}
			} elseif ($uitype == 15 || ($uitype == 55 && $fieldname == "salutationtype")) {
				$temp_val = decode_html ($adb->query_result ($list_result, $list_result_count, $colname));
				if (($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) && $temp_val != '') {
					$temp_acttype = $adb->query_result ($list_result, $list_result_count, 'activitytype');
					if (($temp_acttype != 'Task') && $fieldname == "taskstatus") {
						$temptable = "eventstatus";
					} else {
						$temptable = $fieldname;
					}
					$roleid  = $current_user->roleid;
					$roleids = Array ();
					$subrole = getRoleSubordinates ($roleid);
					if (count ($subrole) > 0) {
						$roleids = $subrole;
					}
					array_push ($roleids, $roleid);

					//here we are checking wheather the table contains the sortorder column .If  sortorder is present in the main picklist table, then the role2picklist will be applicable for this table...

					$sql             = "select * from vtiger_$temptable where $temptable=?";
					$res             = $adb->pquery ($sql, array (decode_html ($temp_val)));
					
					// Si no encuentra el valor exacto, intentar con valor limpio (sin caracteres de control)
					if ($adb->num_rows($res) == 0 && !empty($temp_val)) {
						$clean_value = trim($temp_val);
						// Eliminar caracteres de control (CR, LF, TAB, etc.)
						$clean_value = rtrim($clean_value, "\r\n\t\0\x0B");
						$clean_value = preg_replace('/[\x00-\x1F\x7F]/', '', $clean_value);
						
						// Intentar búsqueda con valor limpio
						$res = $adb->pquery($sql, array(decode_html($clean_value)));
					}
					
					$picklistvalueid = $adb->query_result ($res, 0, 'picklist_valueid');
					if ($picklistvalueid != null) {
						$pick_query = "select * from vtiger_role2picklist where picklistvalueid=$picklistvalueid and roleid in (" . generateQuestionMarks ($roleids) . ")";
						$res_val    = $adb->pquery ($pick_query, array ($roleids));
						$num_val    = $adb->num_rows ($res_val);
					}
					if ($num_val > 0 || ($temp_acttype == 'Task' && $fieldname == 'activitytype')) {
						$temp_val = $temp_val;
					} else {
						$temp_val = "<font color='red'>" . $app_strings['LBL_NOT_ACCESSIBLE'] . "</font>";
					}
				}
				$value = ($current_module_strings[ $temp_val ] != '') ? $current_module_strings[ $temp_val ] : (($app_strings[ $temp_val ] != '') ? ($app_strings[ $temp_val ]) : $temp_val);
				if ($value != "<font color='red'>" . $app_strings['LBL_NOT_ACCESSIBLE'] . "</font>") {
					$value = textlength_check ($value);
				}
			} elseif ($uitype == 16) {
				$value = getTranslatedString ($temp_val, $currentModule);
				$value = textlength_check ($value);
			} elseif ($uitype == 71 || $uitype == 72) {
				if ($temp_val != '') {
					// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
					if ($uitype == 72) {
						if ($fieldname == 'unit_price') {
							$currency_id     = getProductBaseCurrency ($entity_id, $module);
							$cursym_convrate = getCurrencySymbolandCRate ($currency_id);
							$currency_symbol = $cursym_convrate['symbol'];
						} else {
							$currency_info   = getInventoryCurrencyInfo ($module, $entity_id);
							$currency_symbol = $currency_info['currency_symbol'];
						}
						$currencyValue = CurrencyField::convertToUserFormat ($temp_val, null, true);
						$value         = CurrencyField::appendCurrencySymbol ($currencyValue, $currency_symbol);
					} else {
						//changes made to remove vtiger_currency symbol infront of each vtiger_potential amount
						if ($temp_val != 0) {
							$value = CurrencyField::convertToUserFormat ($temp_val);
						} else {
							$value = $temp_val;
						}
					}
				} else {
					$value = '';
				}
			} elseif ($uitype == 17) {
				$matchPattern = "^[\w]+:\/\/^";
				preg_match ($matchPattern, $field_val, $matches);
				if (!empty($matches[0])) {
					$value = '<a href="' . $field_val . '" target="_blank">' . textlength_check ($temp_val) . '</a>';
				} else {
					$value = '<a href="http://' . $field_val . '" target="_blank">' . textlength_check ($temp_val) . '</a>';
				}
			} elseif ($uitype == 13 || $uitype == 104 && ($_REQUEST['action'] != 'Popup' && $_REQUEST['file'] != 'Popup')) {
				if ($_SESSION['internal_mailer'] == 1) {
					//check added for email link in user detailview
					if ($module == 'Calendar') {
						if (getActivityType ($entity_id) == 'Task') {
							$tabid = 9;
						} else {
							$tabid = 16;
						}
					} else {
						$tabid = getTabid ($module);
					}
					$fieldid = getFieldid ($tabid, $fieldname);
					if (empty($popuptype)) {
						# Deshabilitado temporalmente | MA | 17-02-2016
						//$value = '<a href="javascript:InternalMailer(' . $entity_id . ',' . $fieldid . ',\'' . $fieldname . '\',\'' . $module . '\',\'record_id\');">' . textlength_check($temp_val) . '</a>';
						$value = textlength_check ($temp_val);
					} else {
						$value = $temp_val;
						$value = textlength_check ($value);
					}
				} else {
					$value = '<a href="mailto:' . $field_val . '">' . textlength_check ($temp_val) . '</a>';
				}
			} elseif ($uitype == 56) {
				if ($temp_val == 1) {
					$value = $app_strings['yes'];
				} elseif ($temp_val == 0) {
					$value = $app_strings['no'];
				} else {
					$value = '';
				}
			} //Added by Minnie to get Campaign Source
			elseif ($uitype == 58) {
				if ($temp_val != '') {
					$sql          = "SELECT * FROM vtiger_campaign WHERE campaignid=?";
					$result       = $adb->pquery ($sql, array ($temp_val));
					$campaignname = $adb->query_result ($result, 0, "campaignname");
					$value        = '<a href=index.php?module=Campaigns&action=DetailView&record=' . $temp_val . '>' . textlength_check ($campaignname) . '</a>';
				} else {
					$value = '';
				}
			}
			//End
			elseif ($uitype == 61) {
				$attachmentid = $adb->query_result ($adb->pquery ("SELECT * FROM vtiger_seattachmentsrel WHERE crmid = ?", array ($entity_id)), 0, 'attachmentsid');
				$value        = '<a href = "index.php?module=uploads&action=downloadfile&return_module=' . $module . '&fileid=' . $attachmentid . '&filename=' . $temp_val . '">' . textlength_check ($temp_val) . '</a>';
			} elseif ($uitype == 78) {
				if ($temp_val != '') {

					$quote_name = getQuoteName ($temp_val);
					$value      = '<a href=index.php?module=Quotes&action=DetailView&record=' . $temp_val . '&parenttab=' . urlencode ($tabname) . '>' . textlength_check ($quote_name) . '</a>';
				} else {
					$value = '';
				}
			} elseif ($uitype == 79) {
				if ($temp_val != '') {

					$purchaseorder_name = getPoName ($temp_val);
					$value              = '<a href=index.php?module=PurchaseOrder&action=DetailView&record=' . $temp_val . '&parenttab=' . urlencode ($tabname) . '>' . textlength_check ($purchaseorder_name) . '</a>';
				} else {
					$value = '';
				}
			} elseif ($uitype == 80) {
				if ($temp_val != '') {

					$salesorder_name = getSoName ($temp_val);
					$value           = "<a href=index.php?module=SalesOrder&action=DetailView&record=$temp_val&parenttab=" . urlencode ($tabname) . ">" . textlength_check ($salesorder_name) . '</a>';
				} else {
					$value = '';
				}
			} elseif ($uitype == 75 || $uitype == 81) {

				if ($temp_val != '') {

					$vendor_name = getVendorName ($temp_val);
					$value       = '<a href=index.php?module=Vendors&action=DetailView&record=' . $temp_val . '&parenttab=' . urlencode ($tabname) . '>' . textlength_check ($vendor_name) . '</a>';
				} else {
					$value = '';
				}
			} elseif ($uitype == 98) {
				$value = '<a href="index.php?action=RoleDetailView&module=Settings&parenttab=Settings&roleid=' . $temp_val . '">' . textlength_check (getRoleName ($temp_val)) . '</a>';
			} elseif ($uitype == 33) {
				$value = ($temp_val != "") ? str_ireplace (' |##| ', ', ', $temp_val) : "";
				if (!$is_admin && $value != '') {
					$value = ($field_val != "") ? str_ireplace (' |##| ', ', ', $field_val) : "";
					if ($value != '') {
						$value_arr = explode (',', trim ($value));
						$roleid    = $current_user->roleid;
						$subrole   = getRoleSubordinates ($roleid);
						if (count ($subrole) > 0) {
							$roleids = $subrole;
							array_push ($roleids, $roleid);
						} else {
							$roleids = $roleid;
						}

						if (count ($roleids) > 0) {
							$pick_query = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid in (" . generateQuestionMarks ($roleids) . ") and picklistid in (select picklistid from vtiger_$fieldname) order by $fieldname asc";
							$params     = array ($roleids);
						} else {
							$pick_query = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where picklistid in (select picklistid from vtiger_$fieldname) order by $fieldname asc";
							$params     = array ();
						}
						$pickListResult = $adb->pquery ($pick_query, $params);
						$picklistval    = Array ();
						for ($i = 0; $i < $adb->num_rows ($pickListResult); $i++) {
							$picklistarr[] = $adb->query_result ($pickListResult, $i, $fieldname);
						}
						$value_temp  = Array ();
						$string_temp = '';
						$str_c       = 0;
						foreach ($value_arr as $ind => $val) {
							$notaccess = '<font color="red">' . $app_strings['LBL_NOT_ACCESSIBLE'] . "</font>";
							if (!$listview_max_textlength || !(strlen (preg_replace ("/(<\/?)(\w+)([^>]*>)/i", "", $string_temp)) > $listview_max_textlength)) {
								$value_temp1 = (in_array (trim ($val), $picklistarr)) ? $val : $notaccess;
								if ($str_c != 0) {
									$string_temp .= ' , ';
								}
								$string_temp .= $value_temp1;
								$str_c++;
							} else {
								$string_temp .= '...';
							}
						}
						$value = $string_temp;
					}
				}
			} elseif ($uitype == 85) {
				$value = ($temp_val != "") ? "<a href='skype:{$temp_val}?call'>{$temp_val}</a>" : "";
			} elseif ($uitype == 116) {
				$value = ($temp_val != "") ? getCurrencyName ($temp_val) : "";
			} elseif ($uitype == 117) {
				// NOTE: Without symbol the value could be used for filtering/lookup hence avoiding the translation
				$value = ($temp_val != "") ? getCurrencyName ($temp_val, false) : "";
			} elseif ($uitype == 26) {
				$sql        = "SELECT foldername FROM vtiger_attachmentsfolder WHERE folderid = ?";
				$res        = $adb->pquery ($sql, array ($temp_val));
				$foldername = $adb->query_result ($res, 0, 'foldername');
				$value      = $foldername;
			} //added for asterisk integration
			elseif ($uitype == 11) {
				// Fix added for Trac Id: 6139
				if (vtlib_isModuleActive ('PBXManager')) {
					$value = "<a href='javascript:;' onclick='startCall(&quot;$temp_val&quot;, &quot;$entity_id&quot;)'>" . textlength_check ($temp_val) . "</a>";
				} else {
					$value = $temp_val;
				}
			}
			//asterisk changes end here
			//Added for email status tracking
			elseif ($uitype == 25) {
				$contactid = $_REQUEST['record'];
				$emailid   = $adb->query_result ($list_result, $list_result_count, "activityid");
				$result    = $adb->pquery ("SELECT access_count FROM vtiger_email_track WHERE crmid=? AND mailid=?", array ($contactid, $emailid));
				$value     = $adb->query_result ($result, 0, "access_count");
				if (!$value) {
					$value = 0;
				}
			} elseif ($uitype == 8) {
				if (!empty($temp_val)) {
					$temp_val = html_entity_decode ($temp_val, ENT_QUOTES, $default_charset);
					$json     = new Zend_Json();
					$value    = vt_suppressHTMLTags (implode (',', $json->decode ($temp_val)));
				}
			} //end email status tracking
			else {
				if ($fieldname == $focus->list_link_field) {
					if ($mode == "search") {
						if ($popuptype == "specific" || $popuptype == "toDospecific") {
							$slashes_temp_val = popup_from_html ($temp_val);
							$slashes_temp_val = htmlspecialchars ($slashes_temp_val, ENT_QUOTES, $default_charset);

							//Added to avoid the error when select SO from Invoice through AjaxEdit
							if ($module == 'SalesOrder') {
								$count = counterValue ();
								$value = '<a href="javascript:window.close();" onclick=\'set_return_specific("' . $entity_id . '", "' . nl2br (decode_html ($slashes_temp_val)) . '","' . $_REQUEST['form'] . '");\' id = ' . $count . '>' . textlength_check ($temp_val) . '</a>';
							} else if ($popuptype == 'toDospecific') {
								$count = counterValue ();
								$value = '<a href="javascript:window.close();" onclick=\'set_return_toDospecific("' . $entity_id . '", "' . nl2br (decode_html ($slashes_temp_val)) . '");\'id = ' . $count . '>' . textlength_check ($temp_val) . '</a>';
							} else {
								$count = counterValue ();
								$value = '<a href="javascript:window.close();" onclick=\'set_return_specific("' . $entity_id . '", "' . nl2br (decode_html ($slashes_temp_val)) . '");\'id = ' . $count . '>' . textlength_check ($temp_val) . '</a>';
							}
						} elseif ($popuptype == "detailview") {
							$slashes_temp_val = popup_from_html ($temp_val);
							$slashes_temp_val = htmlspecialchars ($slashes_temp_val, ENT_QUOTES, $default_charset);

							$focus->record_id = $_REQUEST['recordid'];
							$popupMode        = $_REQUEST['popupmode'];
							$callBack         = $_REQUEST['callback'];

							// Re-Open [ TT11387 ] Correcciones del Calendario - Jesus A- Se estandariza el uso de la función add_data_to_relatedlist() para que el modulo Calendar la utilice
							//if ($_REQUEST['return_module'] == "Calendar") {
							//	$count = counterValue();
							//	$value = '<a href="javascript:window.close();" id="calendarCont' . $entity_id . '" LANGUAGE=javascript onclick=\'add_data_to_relatedlist_incal("' . $entity_id . '","' . decode_html($slashes_temp_val) . '");\'id = ' . $count . '>' . textlength_check($temp_val) . '</a>';
							//} else {
							$count = counterValue ();
							if (empty($callBack)) {
								$value = '<a style="cursor:pointer;" onclick=\'add_data_to_relatedlist("' . $entity_id . '","' . $focus->record_id . '","' . $module . '","' . $popupMode . '");\'>' . textlength_check ($temp_val) . '</a>';
							} else {
								$value = '<a style="cursor:pointer;" onclick=\'add_data_to_relatedlist("' . $entity_id . '","' . $focus->record_id . '","' . $module . '","' . $popupMode . '",' . $callBack . ');\'>' . textlength_check ($temp_val) . '</a>';
							}
							//}
						} elseif ($popuptype == "formname_specific") {
							$slashes_temp_val = popup_from_html ($temp_val);
							$slashes_temp_val = htmlspecialchars ($slashes_temp_val, ENT_QUOTES, $default_charset);
							$count            = counterValue ();
							$value            = '<a href="javascript:window.close();" onclick=\'set_return_formname_specific("' . $_REQUEST['form'] . '", "' . $entity_id . '", "' . nl2br (decode_html ($slashes_temp_val)) . '");\'id = ' . $count . '>' . textlength_check ($temp_val) . '</a>';
						} elseif ($popuptype == "inventory_service") {
							$row_id = $_REQUEST['curr_row'];

							//To get all the tax types and values and pass it to product details
							$tax_str     = '';
							$tax_details = getAllTaxes ();
							for ($tax_count = 0; $tax_count < count ($tax_details); $tax_count++) {
								$tax_str .= $tax_details[ $tax_count ]['taxname'] . '=' . $tax_details[ $tax_count ]['percentage'] . ',';
							}
							$tax_str   = trim ($tax_str, ',');
							$rate      = $user_info['conv_rate'];
							$unitprice = '';

							$slashes_temp_val  = popup_from_html ($field_val);
							$slashes_temp_val  = htmlspecialchars ($slashes_temp_val, ENT_QUOTES, $default_charset);
							$description       = popup_from_html ($adb->query_result ($list_result, $list_result_count, 'description'));
							$slashes_temp_desc = decode_html (htmlspecialchars ($description, ENT_QUOTES, $default_charset));

							$slashes_desc = str_replace (array ("\r", "\n"), array ('\r', '\n'), $slashes_temp_desc);
							$tmp_arr      = array ("entityid" => $entity_id, "prodname" => "" . stripslashes (decode_html (nl2br ($slashes_temp_val))) . "", "unitprice" => "$unitprice", "taxstring" => "$tax_str", "rowid" => "$row_id", "desc" => "$slashes_desc");
							require_once ('include/Zend/Json.php');
							$prod_arr = Zend_Json::encode ($tmp_arr);

							$value = '<a href="javascript:window.close();" id=\'popup_product_' . $entity_id . '\' onclick=\'set_return_inventory("' . $entity_id . '", "' . decode_html (nl2br ($slashes_temp_val)) . '", "' . $unitprice . '", "' . $tax_str . '","' . $row_id . '","' . $slashes_desc . '");\'  vt_prod_arr=\'' . $prod_arr . '\' >' . textlength_check ($temp_val) . '</a>';
						} //added by rdhital/Raju for better emails
						elseif ($popuptype == "set_return_emails") {
							$name         = getFullNameFromQResult ($list_result, $list_result_count, $module);
							$emailaddress = $adb->query_result ($list_result, $list_result_count, "email1");

							$slashes_name = popup_from_html ($name);
							$slashes_name = htmlspecialchars ($slashes_name, ENT_QUOTES, $default_charset);
							$email_check  = 1;
							$count        = counterValue ();
							$value        = '<a href="javascript:window.close();" onclick=\'return set_return_emails(' . $entity_id . ',-1,"' . decode_html ($slashes_name) . '","' . $emailaddress . '","' . $emailaddress2 . '","' . $email_check . '"); \'id = ' . $count . '>' . textlength_check ($name) . '</a>';
						} elseif ($popuptype == "specific_vendor_address") {
							require_once ('modules/Vendors/Vendors.php');
							$acct_focus = new Vendors();
							$acct_focus->retrieve_entity_info ($entity_id, "Vendors");

							$slashes_temp_val = popup_from_html ($temp_val);
							$slashes_temp_val = htmlspecialchars ($slashes_temp_val, ENT_QUOTES, $default_charset);
							$xyz              = array ('street', 'city', 'postalcode', 'pobox', 'country', 'state');
							for ($i = 0; $i < 6; $i++) {
								if (getFieldVisibilityPermission ($module, $current_user->id, $xyz[ $i ]) == '0') {
									$acct_focus->column_fields[ $xyz[ $i ] ] = $acct_focus->column_fields[ $xyz[ $i ] ];
								} else {
									$acct_focus->column_fields[ $xyz[ $i ] ] = '';
								}
							}
							$bill_street = str_replace (array ("\r", "\n"), array ('\r', '\n'), popup_decode_html ($acct_focus->column_fields['street']));
							$count       = counterValue ();
							$value       = '<a href="javascript:window.close();" onclick=\'set_return_address("' . $entity_id . '", "' . nl2br (decode_html ($slashes_temp_val)) . '", "' . $bill_street . '", "' . popup_decode_html ($acct_focus->column_fields['city']) . '", "' . popup_decode_html ($acct_focus->column_fields['state']) . '", "' . popup_decode_html ($acct_focus->column_fields['postalcode']) . '", "' . popup_decode_html ($acct_focus->column_fields['country']) . '","' . popup_decode_html ($acct_focus->column_fields['pobox']) . '");\'id = ' . $count . '>' . textlength_check ($temp_val) . '</a>';
						} elseif ($popuptype == "specific_campaign") {
							$slashes_temp_val = popup_from_html ($temp_val);
							$slashes_temp_val = htmlspecialchars ($slashes_temp_val, ENT_QUOTES, $default_charset);
							$count            = counterValue ();
							$value            = '<a href="javascript:window.close();" onclick=\'set_return_specific_campaign("' . $entity_id . '", "' . nl2br (decode_html ($slashes_temp_val)) . '");\'id = ' . $count . '>' . textlength_check ($temp_val) . '</a>';
						} else {
							if ($colname == "lastname") {
								$temp_val = getFullNameFromQResult ($list_result, $list_result_count, $module);
							} elseif ($module == 'Users' && $fieldname == 'last_name') {
								$temp_val = getFullNameFromQResult ($list_result, $list_result_count, $module);
							}
							$slashes_temp_val = popup_from_html ($temp_val);
							$slashes_temp_val = htmlspecialchars ($slashes_temp_val, ENT_QUOTES, $default_charset);

							$log->debug ("Exiting getValue method ...");
							if ($_REQUEST['maintab'] == 'Calendar') {
								$count = counterValue ();
								$value = '<a href="javascript:window.close();" onclick=\'set_return_todo("' . $entity_id . '", "' . nl2br (decode_html ($slashes_temp_val)) . '");\'id = ' . $count . '>' . textlength_check ($temp_val) . '</a>';
							} else {
								$value = '<a href="javascript:window.close();" onclick=\'set_return("' . $entity_id . '", "' . nl2br (decode_html ($slashes_temp_val)) . '");\'';
								if (empty($_REQUEST['forfield']) && $focus->popup_type != 'detailview') {
									$count = counterValue ();
									$value .= " id='$count' ";
								}
								$value .= '>' . textlength_check ($temp_val) . '</a>';
							}
						}
					} else {
						if ($module == "Calendar") {
							$actvity_type = $adb->query_result ($list_result, $list_result_count, 'activitytype');
							$actvity_type = ($actvity_type != '') ? $actvity_type : $adb->query_result ($list_result, $list_result_count, 'type');
							if ($actvity_type == "Task") {
								$count = counterValue ();
								$value = '<a href="index.php?action=DetailView&module=' . $module . '&record=' . $entity_id . '&activity_mode=Task&parenttab=' . $tabname . '" id = ' . $count . '>' . textlength_check ($temp_val) . '</a>';
							} else {
								$count = counterValue ();
								$value = '<a href="index.php?action=DetailView&module=' . $module . '&record=' . $entity_id . '&activity_mode=Events&parenttab=' . $tabname . '" id = ' . $count . '>' . textlength_check ($temp_val) . '</a>';
							}
						} elseif (($module == "Users" && $colname == "last_name")) {
							$temp_val = getFullNameFromQResult ($list_result, $list_result_count, $module);
							$value    = '<a href="index.php?action=DetailView&module=' . $module . '&record=' . $entity_id . '&parenttab=' . $tabname . '">' . textlength_check ($temp_val) . '</a>';
						} else {
							$count = counterValue ();
							$value = '<a href="index.php?action=DetailView&module=' . $module . '&record=' . $entity_id . '&parenttab=' . $tabname . '" id = ' . $count . '>' . textlength_check ($temp_val) . '</a>';
						}
					}
				} elseif ($module == 'Calendar' && ($fieldname == 'time_start' ||
													$fieldname == 'time_end')
				) {
					$dateField = 'date_start';
					if ($fieldname == 'time_end') {
						$dateField = 'due_date';
					}
					$type = $adb->query_result ($list_result, $list_result_count, 'activitytype');
					if (empty($type)) {
						$type = $adb->query_result ($list_result, $list_result_count, 'type');
					}
					if ($type == 'Task' && $fieldname == 'time_end') {
						$value = '--';
					} else {
						$date_val = $adb->query_result ($list_result, $list_result_count, $dateField);
						$date     = new DateTimeField($date_val . ' ' . $temp_val);
						$value    = $date->getDisplayTime ();
						$value    = textlength_check ($value);
					}
				} else {
					$value = $temp_val;
					$value = textlength_check ($value);
				}
			}

			// Mike Crowe Mod --------------------------------------------------------Make right justified and vtiger_currency value
			if (in_array ($uitype, array (71, 72, 7, 9, 90))) {
				$value = '<span align="right">' . $value . '</div>';
			}
			$log->debug ("Exiting getValue method ...");
		}
		if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
			$value = str_ireplace ('index.php?', 'index.php?platdb=' . $_REQUEST['platdb'] . '&', $value);
		}
		return $value;
	}

	/** Function to get the list query for a module
	 *
	 * @param $module -- module name:: Type string
	 * @param $where -- where:: Type string
	 * @returns $query -- query:: Type query
	 */
	function getListQuery ($module, $where = '') {
		global $log;
		$log->debug ("Entering getListQuery(" . $module . "," . $where . ") method ...");

		global $current_user;
		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');
		require ('user_privileges/sharing_privileges.php');

		$tab_id      = getTabid ($module);
		$userNameSql = getSqlForNameInDisplayFormat (array (
			'first_name' => 'vtiger_users.first_name', 'last_name' =>
				'vtiger_users.last_name',
		), 'Users');
		switch ($module) {
			Case "Documents":
				$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,vtiger_crmentity.crmid, vtiger_crmentity.modifiedtime,
			vtiger_crmentity.smownerid,vtiger_attachmentsfolder.*,vtiger_notes.*
			FROM vtiger_notes
			INNER JOIN vtiger_crmentity
			ON vtiger_crmentity.crmid = vtiger_notes.notesid
			LEFT JOIN vtiger_groups
			ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users
			ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_attachmentsfolder
			ON vtiger_notes.folderid = vtiger_attachmentsfolder.folderid";
				$query .= getNonAdminAccessControlQuery ($module, $current_user);
				$query .= "WHERE vtiger_crmentity.deleted = 0 " . $where;
				break;
			Case "Calendar":
				$query = "SELECT
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
								LEFT JOIN vtiger_groups vtiger_groups2 ON vtiger_crmentity.modifiedby = vtiger_groups2.groupid";

				//added to fix #5135
				if (isset($_REQUEST['from_homepage']) && ($_REQUEST['from_homepage'] ==
														  "upcoming_activities" || $_REQUEST['from_homepage'] == "pending_activities")
				) {
					$query .= " LEFT OUTER JOIN vtiger_recurringevents
							ON vtiger_recurringevents.activityid=vtiger_activity.activityid";
				}
				//end

				$query .= getNonAdminAccessControlQuery ($module, $current_user);
				$query .= " WHERE vtiger_crmentity.deleted = 0 AND activitytype != 'Emails' " . $where;
				break;
			Case "Users":
				$query = "SELECT id,user_name,first_name,last_name,email1,phone_mobile,phone_work,is_admin,status,email2,
					vtiger_user2role.roleid AS roleid,vtiger_role.depth AS depth
					FROM vtiger_users
					INNER JOIN vtiger_user2role ON vtiger_users.id = vtiger_user2role.userid
					INNER JOIN vtiger_role ON vtiger_user2role.roleid = vtiger_role.roleid
					WHERE deleted=0 " . $where;
				break;
			default:
				// vtlib customization: Include the module file
				$focus = CRMEntity::getInstance ($module);
				$query = $focus->getListQuery ($module, $where);
			// END
		}

		if ($module != 'Users') {
			$query = listQueryNonAdminChange ($query, $module);
		}
		$log->debug ("Exiting getListQuery method ...");
		return $query;
	}

	/** Function to get alphabetical search links
	 * Param $module - module name
	 * Param $action - action
	 * Param $fieldname - vtiger_field name
	 * Param $query - query
	 * Param $type - search type
	 * Param $popuptype - popup type
	 * Param $recordid - record id
	 * Param $return_module - return module
	 * Param $append_url - url string to be appended
	 * Param $viewid - custom view id
	 * Param $groupid - group id
	 * Returns an string value
	 */
	function AlphabeticalSearch ($module, $action, $fieldname, $query, $type, $popuptype = '', $recordid = '', $return_module = '', $append_url = '', $viewid = '', $groupid = '') {
		global $log;
		$log->debug ("Entering AlphabeticalSearch(" . $module . "," . $action . "," . $fieldname . "," . $query . "," . $type . "," . $popuptype . "," . $recordid . "," . $return_module . "," . $append_url . "," . $viewid . "," . $groupid . ") method ...");
		if ($type == 'advanced') {
			$flag = '&advanced=true';
		}

		if ($popuptype != '') {
			$popuptypevalue = "&popuptype=" . $popuptype;
		}

		if ($recordid != '') {
			$returnvalue = '&recordid=' . $recordid;
		}
		if ($return_module != '') {
			$returnvalue .= '&return_module=' . $return_module;
		}

		// vtlib Customization : For uitype 10 popup during paging
		if ($_REQUEST['form'] == 'vtlibPopupView') {
			$returnvalue .= '&form=vtlibPopupView&forfield=' . vtlib_purify ($_REQUEST['forfield']) . '&srcmodule=' . vtlib_purify ($_REQUEST['srcmodule']) . '&forrecord=' . vtlib_purify ($_REQUEST['forrecord']);
		}
		// END

		for ($var = 'A', $i = 1; $i <= 26; $i++, $var++) // Mike Crowe Mod --------------------------------------------------------added groupid to url
		{
			$list .= '<td class="searchAlph" id="alpha_' . $i . '" align="center" onClick=\'alphabetic("' . $module . '","gname=' . $groupid . '&query=' . $query . '&search_field=' . $fieldname . '&searchtype=BasicSearch&operator=s&type=alpbt&search_text=' . $var . $flag . $popuptypevalue . $returnvalue . $append_url . '","alpha_' . $i . '")\'>' . $var . '</td>';
		}

		$log->debug ("Exiting AlphabeticalSearch method ...");
		return $list;
	}

	/* * Function to get parent name for a given parent id
 * Param $module - module name
* Param $list_result- result set
* Param $rset - result set index
* Returns an string value
*/

	//used in home page listTop vtiger_files
	function getRelatedTo ($module, $list_result, $rset) {
		global $adb, $log, $app_strings;
		$tabname = getParentTab ();
		if ($module == "Documents") {
			$notesid   = $adb->query_result ($list_result, $rset, "notesid");
			$action    = "DetailView";
			$evt_query = "SELECT vtiger_senotesrel.crmid, vtiger_crmentity.setype
				FROM vtiger_senotesrel
				INNER JOIN vtiger_crmentity
				ON  vtiger_senotesrel.crmid = vtiger_crmentity.crmid
				WHERE vtiger_senotesrel.notesid = ?";
			$params    = array ($notesid);
		} else {
			$activity_id = $adb->query_result ($list_result, $rset, "activityid");
			$action      = "DetailView";
			$evt_query   = "SELECT vtiger_seactivityrel.crmid, vtiger_crmentity.setype
				FROM vtiger_seactivityrel
				INNER JOIN vtiger_crmentity
				ON  vtiger_seactivityrel.crmid = vtiger_crmentity.crmid
				WHERE vtiger_seactivityrel.activityid=?";
			$params      = array ($activity_id);
		}
		//added by raju to change the related to in emails inot multiple if email is for more than one contact
		$evt_result = $adb->pquery ($evt_query, $params);
		$numrows    = $adb->num_rows ($evt_result);

		$parent_module = $adb->query_result ($evt_result, 0, 'setype');
		$parent_id     = $adb->query_result ($evt_result, 0, 'crmid');

		if ($numrows > 1) {
			$parent_module = 'Multiple';
			$parent_name   = $app_strings['LBL_MULTIPLE'];
		}
		//Raju -- Ends
		$action = "DetailView";
		//added by rdhital for better emails - Raju
		if ($parent_module == 'Multiple') {
			$parent_value = $parent_name;
		} else {
			$parent_value = $module_icon . "<a href='index.php?module=" . $parent_module . "&action=" . $action . "&record=" . $parent_id . "&parenttab=" . $tabname . "'>" . textlength_check ($parent_name) . "</a>";
		}
		//code added by raju ends
		$log->debug ("Exiting getRelatedTo method ...");
		return $parent_value;
	}

	/* * Function to get the table headers for a listview
 * Param $navigation_arrray - navigation values in array
* Param $url_qry - url string
* Param $module - module name
* Param $action- action file name
* Param $viewid - view id
* Returns an string value
*/

	function getTableHeaderNavigation ($navigation_array, $url_qry, $module = '', $action_val = 'index', $viewid = '') {
		global $log, $app_strings;
		$log->debug ("Entering getTableHeaderNavigation(" . $navigation_array . "," . $url_qry . "," . $module . "," . $action_val . "," . $viewid . ") method ...");
		global $theme, $current_user;
		$theme_path = "themes/" . $theme . "/";
		$image_path = $theme_path . "images/";
		if ($module == 'Documents') {
			$output = '<td class="mailSubHeader" width="100%" align="center">';
		} else {
			$output = '<td align="right" style="padding: 5px;">';
		}
		$tabname = getParentTab ();

		$url_string = '';

		// vtlib Customization : For uitype 10 popup during paging
		if ($_REQUEST['form'] == 'vtlibPopupView') {
			$url_string .= '&form=vtlibPopupView&forfield=' . vtlib_purify ($_REQUEST['forfield']) . '&srcmodule=' . vtlib_purify ($_REQUEST['srcmodule']) . '&forrecord=' . vtlib_purify ($_REQUEST['forrecord']);
		}
		// END

		if ($module == 'Calendar' && $action_val == 'index') {
			if ($_REQUEST['view'] == '') {
				if ($current_user->activity_view == "This Year") {
					$mysel = 'year';
				} else if ($current_user->activity_view == "This Month") {
					$mysel = 'month';
				} else if ($current_user->activity_view == "This Week") {
					$mysel = 'week';
				} else {
					$mysel = 'day';
				}
			}
			$data_value = date ('Y-m-d H:i:s');
			preg_match ('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $data_value, $value);
			$date_data = Array (
				'day'   => $value[3],
				'month' => $value[2],
				'year'  => $value[1],
				'hour'  => $value[4],
				'min'   => $value[5],
			);
			$tab_type  = ($_REQUEST['subtab'] == '') ? 'event' : vtlib_purify ($_REQUEST['subtab']);
			$url_string .= isset($_REQUEST['view']) ? "&view=" . vtlib_purify ($_REQUEST['view']) : "&view=" . $mysel;
			$url_string .= isset($_REQUEST['subtab']) ? "&subtab=" . vtlib_purify ($_REQUEST['subtab']) : '';
			$url_string .= isset($_REQUEST['viewOption']) ? "&viewOption=" . vtlib_purify ($_REQUEST['viewOption']) : '&viewOption=listview';
			$url_string .= isset($_REQUEST['day']) ? "&day=" . vtlib_purify ($_REQUEST['day']) : '&day=' . $date_data['day'];
			$url_string .= isset($_REQUEST['week']) ? "&week=" . vtlib_purify ($_REQUEST['week']) : '';
			$url_string .= isset($_REQUEST['month']) ? "&month=" . vtlib_purify ($_REQUEST['month']) : '&month=' . $date_data['month'];
			$url_string .= isset($_REQUEST['year']) ? "&year=" . vtlib_purify ($_REQUEST['year']) : "&year=" . $date_data['year'];
			$url_string .= isset($_REQUEST['n_type']) ? "&n_type=" . vtlib_purify ($_REQUEST['n_type']) : '';
			$url_string .= isset($_REQUEST['search_option']) ? "&search_option=" . vtlib_purify ($_REQUEST['search_option']) : '';
		}
		if ($module == 'Calendar' && $action_val != 'index') //added for the All link from the homepage -- ticket 5211
		{
			$url_string .= isset($_REQUEST['from_homepage']) ? "&from_homepage=" . vtlib_purify ($_REQUEST['from_homepage']) : '';
		}

		if (($navigation_array['prev']) != 0) {
			if ($module == 'Calendar' && $action_val == 'index') {
				//$output .= '<a href="index.php?module=Calendar&action=index&start=1'.$url_string.'" alt="'.$app_strings['LBL_FIRST'].'" title="'.$app_strings['LBL_FIRST'].'"><img src="themes/images/start.gif" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="cal_navigation(\'' . $tab_type . '\',\'' . $url_string . '\',\'&start=1\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><img src="' . vtiger_imageurl ('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				//$output .= '<a href="index.php?module=Calendar&action=index&start='.$navigation_array['prev'].$url_string.'" alt="'.$app_strings['LNK_LIST_PREVIOUS'].'"title="'.$app_strings['LNK_LIST_PREVIOUS'].'"><img src="themes/images/previous.gif" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="cal_navigation(\'' . $tab_type . '\',\'' . $url_string . '\',\'&start=' . $navigation_array['prev'] . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><img src="' . vtiger_imageurl ('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			} else if ($action_val == "FindDuplicate") {
				$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=1' . $url_string . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><img src="' . vtiger_imageurl ('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['prev'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><img src="' . vtiger_imageurl ('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			} elseif ($action_val == 'UnifiedSearch') {
				$output .= '<a href="javascript:;" onClick="getUnifiedSearchEntries_js(\'' . $search_tag . '\',\'' . $module . '\',\'parenttab=' . $tabname . '&start=1' . $url_string . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><img src="' . vtiger_imageurl ('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="getUnifiedSearchEntries_js(\'' . $search_tag . '\',\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['prev'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><img src="' . vtiger_imageurl ('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			} elseif ($module == 'Documents') {
				$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=1' . $url_string . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><img src="' . vtiger_imageurl ('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['prev'] . $url_string . '&folderid=' . $action_val . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><img src="' . vtiger_imageurl ('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			} else {
				$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=1' . $url_string . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><img src="' . vtiger_imageurl ('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['prev'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><img src="' . vtiger_imageurl ('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			}
		} else {
			$output .= '<img src="' . vtiger_imageurl ('start_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
			$output .= '<img src="' . vtiger_imageurl ('previous_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		}

		if ($module == 'Calendar' && $action_val == 'index') {
			$jsNavigate = "cal_navigation('$tab_type','$url_string','&start='+this.value);";
		} else if ($action_val == "FindDuplicate") {
			$jsNavigate = "getDuplicateListViewEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string');";
		} elseif ($action_val == 'UnifiedSearch') {
			$jsNavigate = "getUnifiedSearchEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string');";
		} elseif ($module == 'Documents') {
			$jsNavigate = "getListViewEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string&folderid=$action_val');";
		} else {
			$jsNavigate = "getListViewEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string');";
		}
		if ($module == 'Documents') {
			$url = '&folderid=' . $action_val;
		} else {
			$url = '';
		}
		$jsHandler = "return VT_disableFormSubmit(event);";
		$output .= "<input class='small' name='pagenum' type='text' value='{$navigation_array['current']}'
	style='width: 3em;margin-right: 0.7em;' onchange=\"$jsNavigate\"
	onkeypress=\"$jsHandler\">";
		$output .= "<span name='" . $module . "_listViewCountContainerName' class='small' style='white-space: nowrap;'>";
		$output .= $app_strings['LBL_LIST_OF'] . ' ' . $navigation_array['verylast'] . '</span>';

		if (($navigation_array['next']) != 0) {
			if ($module == 'Calendar' && $action_val == 'index') {
				//$output .= '<a href="index.php?module=Calendar&action=index&start='.$navigation_array['next'].$url_string.'" alt="'.$app_strings['LNK_LIST_NEXT'].'" title="'.$app_strings['LNK_LIST_NEXT'].'"><img src="themes/images/next.gif" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="cal_navigation(\'' . $tab_type . '\',\'' . $url_string . '\',\'&start=' . $navigation_array['next'] . '\');" alt="' . $app_strings['LNK_LIST_NEXT'] . '" title="' . $app_strings['LNK_LIST_NEXT'] . '"><img src="' . vtiger_imageurl ('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				//$output .= '<a href="index.php?module=Calendar&action=index&start='.$navigation_array['verylast'].$url_string.'" alt="'.$app_strings['LBL_LAST'].'" title="'.$app_strings['LBL_LAST'].'"><img src="themes/images/end.gif" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="cal_navigation(\'' . $tab_type . '\',\'' . $url_string . '\',\'&start=' . $navigation_array['verylast'] . '\');" alt="' . $app_strings['LBL_LAST'] . '" title="' . $app_strings['LBL_LAST'] . '"><img src="' . vtiger_imageurl ('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			} else if ($action_val == "FindDuplicate") {
				$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['next'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_NEXT'] . '" title="' . $app_strings['LNK_LIST_NEXT'] . '"><img src="' . vtiger_imageurl ('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="getDuplicateListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['verylast'] . $url_string . '\');" alt="' . $app_strings['LBL_LAST'] . '" title="' . $app_strings['LBL_LAST'] . '"><img src="' . vtiger_imageurl ('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			} elseif ($action_val == 'UnifiedSearch') {
				$output .= '<a href="javascript:;" onClick="getUnifiedSearchEntries_js(\'' . $search_tag . '\',\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['next'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_NEXT'] . '" title="' . $app_strings['LNK_LIST_NEXT'] . '"><img src="' . vtiger_imageurl ('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="getUnifiedSearchEntries_js(\'' . $search_tag . '\',\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['verylast'] . $url_string . '\');" alt="' . $app_strings['LBL_LAST'] . '" title="' . $app_strings['LBL_LAST'] . '"><img src="themes/images/end.gif" border="0" align="absmiddle"></a>&nbsp;';
			} elseif ($module == 'Documents') {
				$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['next'] . $url_string . '&folderid=' . $action_val . '\');" alt="' . $app_strings['LNK_LIST_NEXT'] . '" title="' . $app_strings['LNK_LIST_NEXT'] . '"><img src="' . vtiger_imageurl ('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['verylast'] . $url_string . '&folderid=' . $action_val . '\');" alt="' . $app_strings['LBL_LAST'] . '" title="' . $app_strings['LBL_LAST'] . '"><img src="' . vtiger_imageurl ('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			} else {
				$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['next'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_NEXT'] . '" title="' . $app_strings['LNK_LIST_NEXT'] . '"><img src="' . vtiger_imageurl ('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
				$output .= '<a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['verylast'] . $url_string . '\');" alt="' . $app_strings['LBL_LAST'] . '" title="' . $app_strings['LBL_LAST'] . '"><img src="' . vtiger_imageurl ('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
			}
		} else {
			$output .= '<img src="' . vtiger_imageurl ('next_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
			$output .= '<img src="' . vtiger_imageurl ('end_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		}
		$output .= '</td>';
		$log->debug ("Exiting getTableHeaderNavigation method ...");
		if ($navigation_array['first'] == '') {
			return;
		} else {
			return $output;
		}
	}

	/* * This function stores the variables in session sent in list view url string.
 * Param $lv_array - list view session array
* Param $noofrows - no of rows
* Param $max_ent - maximum entires
* Param $module - module name
* Param $related - related module
* Return type void.
*/

	function setSessionVar ($lv_array, $noofrows, $max_ent, $module = '', $related = '') {
		$start = '';
		if ($noofrows >= 1) {
			$lv_array['start'] = 1;
			$start             = 1;
		} elseif ($related != '' && $noofrows == 0) {
			$lv_array['start'] = 1;
			$start             = 1;
		} else {
			$lv_array['start'] = 0;
			$start             = 0;
		}

		if (isset($_REQUEST['start']) && $_REQUEST['start'] != '') {
			$lv_array['start'] = ListViewSession::getRequestStartPage ();
			$start             = ListViewSession::getRequestStartPage ();
		} elseif ($_SESSION['rlvs'][ $module ][ $related ]['start'] != '') {

			if ($related != '') {
				$lv_array['start'] = $_SESSION['rlvs'][ $module ][ $related ]['start'];
				$start             = $_SESSION['rlvs'][ $module ][ $related ]['start'];
			}
		}
		if (isset($_REQUEST['viewname']) && $_REQUEST['viewname'] != '') {
			$lv_array['viewname'] = vtlib_purify ($_REQUEST['viewname']);
		}

		if ($related == '') {
			$_SESSION['lvs'][ $_REQUEST['module'] ] = $lv_array;
		} else {
			$_SESSION['rlvs'][ $module ][ $related ] = $lv_array;
		}

		if ($start < ceil ($noofrows / $max_ent) && $start != '') {
			$start = ceil ($noofrows / $max_ent);
			if ($related == '') {
				$_SESSION['lvs'][ $currentModule ]['start'] = $start;
			}
		}
	}


	/**
	 * Function to get the table headers for related listview
	 *
	 * @param array $navigation_array
	 * @param string$url_qry
	 * @param string $module
	 * @param string $related_module
	 * @param integer $recordid
	 * @param boolean $isCard
	 *
	 * @return string|void
	 */
	function getRelatedTableHeaderNavigation ($navigation_array, $url_qry, $module, $related_module, $recordid, $isCard = false) {
		global $log, $app_strings, $adb, $theme;
		$action_val = isset($action_val) ? $action_val : '';
		$viewid = isset($viewid) ? $viewid : '';
		//$log->debug ("Entering getTableHeaderNavigation(" . (is_array($navigation_array) ? 'Array' : $navigation_array) . "," . $url_qry . "," . $module . "," . $action_val . "," . $viewid . ") method ...");

		$relatedListRow = getRelatedListFormattedHeader ($adb, $module, $related_module);
		$header         = $relatedListRow ['label'];
		$actions        = $relatedListRow ['actions'];
		$functionName   = $relatedListRow ['name'];

		$urldata = "module=$module&action={$module}Ajax&file=DetailViewAjax&record={$recordid}&" .
				   "ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$relatedListRow['relation_id']}" .
				   "&actions={$actions}&{$url_qry}";

		$formattedHeader = str_replace (' ', '', $header);
		$target          = 'tbl_' . $module . '_' . $formattedHeader;
		$imagesuffix     = $module . '_' . $formattedHeader;

		$output = '<td align="right" style="padding="5px;">';
		if ((($navigation_array['prev']) != 0) || (($navigation_array['next']) != 0)) {
			$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=1\',\'' . $target . '\',\'' . $imagesuffix . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><i class="fa fa-angle-double-left" aria-hidden="true"></i></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['prev'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><i class="fa fa-angle-left" aria-hidden="true"></i></a>&nbsp;';
		}

		if (!$isCard) {
			$jsHandler = 'return VT_disableFormSubmit(event);';
			$output .= "<input class='small' name='pagenum' type='text' value='{$navigation_array['current']}' 
			style='width: 3em;margin-right: 0.7em;' onchange=\"loadRelatedListBlock('{$urldata}&start='+this.value+'','{$target}','{$imagesuffix}');\" 
			onkeypress=\"$jsHandler\">";
			$output .= "<span name='listViewCountContainerName' class='small' style='white-space: nowrap;'>";
			$computeCount = $_REQUEST['withCount'];
			$listComputerPage = PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false);
			if ((($listComputerPage === true || (boolean)$computeCount == true) && !$isCard)
			) {
				$output .= $app_strings['LBL_LIST_OF'] . ' ' . $navigation_array['verylast'];
			} else {
				$output .= "<img src='" . vtiger_imageurl('windowRefresh.gif', $theme) . "' alt='" . $app_strings['LBL_HOME_COUNT'] . "'
				onclick=\"loadRelatedListBlock('{$urldata}&withCount=true&start={$navigation_array['current']}','{$target}','{$imagesuffix}');\"
				align='absmiddle' name='" . $module . "_listViewCountRefreshIcon'/>
				<img name='" . $module . "_listViewCountContainerBusy' src='" . vtiger_imageurl('vtbusy.gif', $theme) . "' style='display: none;'
				align='absmiddle' alt='" . $app_strings['LBL_LOADING'] . "'>";
			}
			$output .= '</span>';
		} else {
			$output .= '&nbsp;';
		}

		if ((($navigation_array['next']) != 0) || (($navigation_array['prev']) != 0)) {
			$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['next'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');"><i class="fa fa-angle-right" aria-hidden="true"></i></a>&nbsp;';
			$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['verylast'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');"><i class="fa fa-angle-double-right" aria-hidden="true"></i></a>&nbsp;';
		}
		$output .= '</td>';
		$log->debug ("Exiting getTableHeaderNavigation method ...");
		if ($navigation_array['first'] == '') {
			return;
		} else {
			return $output;
		}
	}

	/**
	 * @param PearDatabase $adb
	 * @param string $module
	 * @param string $relatedModule
	 *
	 * @return array|null
	 */
	function getRelatedListFormattedHeader ($adb, $module, $relatedModule) {
		$relatedTabId      = getTabid ($relatedModule);
		$tabId             = getTabid ($module);
		$relatedListResult = $adb->pquery ('SELECT * FROM vtiger_relatedlists WHERE tabid=? AND related_tabid=?', array ($tabId, $relatedTabId));
		if (empty($relatedListResult)) {
			return null;
		}
		return $adb->fetch_row ($relatedListResult);
	}

	/**    Function to get the Edit link details for ListView and RelatedListView
	 *
	 * @param string $module - module name
	 * @param int $entity_id - record id
	 * @param string $relatedlist - string "relatedlist" or may be empty. if empty means ListView else relatedlist
	 * @param string $returnset - may be empty in case of ListView. For relatedlists, return_module, return_action and return_id values will be passed like &return_module=Accounts&return_action=CallRelatedList&return_id=10
	 *    return string    $edit_link    - url string which cotains the editlink details (module, action, record, etc.,) like index.php?module=Accounts&action=EditView&record=10
	 */
	function getListViewEditLink ($module, $entity_id, $relatedlist, $returnset, $result, $count) {
		global $adb;
		$return_action = "index";
		$edit_link     = "index.php?module=$module&action=EditView&record=$entity_id";
		$tabname       = getParentTab ();
		//Added to fix 4600
		$url = getBasic_Advance_SearchURL ();

		//This is relatedlist listview
		if ($relatedlist == 'relatedlist') {
			$edit_link .= $returnset;
		} else {
			if ($module == 'Calendar') {
				$return_action = "ListView";
				$actvity_type  = $adb->query_result ($result, $count, 'type');
				if ($actvity_type == 'Task') {
					$edit_link .= '&activity_mode=Task';
				} else {
					$edit_link .= '&activity_mode=Events';
				}
			}
			$edit_link .= "&return_module=$module&return_action=$return_action";
		}

		$edit_link .= "&parenttab=" . $tabname . $url;
		//Appending view name while editing from ListView
		$edit_link .= "&return_viewname=" . $_SESSION['lvs'][ $module ]["viewname"];

		if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
			$edit_link .= "&platdb=" . vtlib_purify ($_REQUEST['platdb']);
		}

		if ($module == 'Emails') {
			$edit_link = 'javascript:;" onclick="OpenCompose(\'' . $entity_id . '\',\'edit\');';
		}
		return $edit_link;
	}

	/**    Function to get the Del link details for ListView and RelatedListView
	 *
	 * @param string $module - module name
	 * @param int $entity_id - record id
	 * @param string $relatedlist - string "relatedlist" or may be empty. if empty means ListView else relatedlist
	 * @param string $returnset - may be empty in case of ListView. For relatedlists, return_module, return_action and return_id values will be passed like &return_module=Accounts&return_action=CallRelatedList&return_id=10
	 *    return string    $del_link    - url string which cotains the editlink details (module, action, record, etc.,) like index.php?module=Accounts&action=Delete&record=10
	 */
	function getListViewDeleteLink ($module, $entity_id, $relatedlist, $returnset) {
		$tabname        = getParentTab ();
		$current_module = vtlib_purify ($_REQUEST['module']);
		$viewname       = $_SESSION['lvs'][ $current_module ]['viewname'];

		//Added to fix 4600
		$url = getBasic_Advance_SearchURL ();

		if ($module == "Calendar") {
			$return_action = "ListView";
		} else {
			$return_action = "index";
		}

		//This is added to avoid the del link in Product related list for the following modules
		$avoid_del_links = Array ("PurchaseOrder", "SalesOrder", "Quotes", "Invoice");
		$del_link        = "index.php?module=$module&action=Delete&record=$entity_id";

		//This is added for relatedlist listview
		if ($relatedlist == 'relatedlist') {
			$del_link .= $returnset;
		} else {
			$del_link .= "&return_module=$module&return_action=$return_action";
		}

		$del_link .= "&parenttab=" . $tabname . "&return_viewname=" . $viewname . $url;

		if (isset($_REQUEST['platdb']) && !empty($_REQUEST['platdb'])) {
			$del_link .= "&platdb=" . vtlib_purify ($_REQUEST['platdb']);
		}

		// vtlib customization: override default delete link for custom modules
		$requestModule  = vtlib_purify ($_REQUEST['module']);
		$requestRecord  = vtlib_purify ($_REQUEST['record']);
		$requestAction  = vtlib_purify ($_REQUEST['action']);
		$parenttab      = vtlib_purify ($_REQUEST['parenttab']);
		$isCustomModule = vtlib_isCustomModule ($requestModule);
		if ($requestAction == $requestModule . "Ajax") {
			$requestAction = vtlib_purify ($_REQUEST['file']);
		}
		if ($isCustomModule && !in_array ($requestAction, Array ('index', 'ListView'))) {
			$del_link = "index.php?module=$requestModule&action=updateRelations&parentid=$requestRecord";
			$del_link .= "&destination_module=$module&idlist=$entity_id&mode=delete&parenttab=$parenttab";
		}
		// END

		return $del_link;
	}

	/* Function to get the Entity Id of a given Entity Name */

	function getEntityId ($module, $entityName) {
		global $log, $adb;
		$log->info ("in getEntityId " . $entityName);

		$query         = "SELECT fieldname,tablename,entityidfield FROM vtiger_entityname WHERE modulename = ?";
		$result        = $adb->pquery ($query, array ($module));
		$fieldsname    = $adb->query_result ($result, 0, 'fieldname');
		$tablename     = $adb->query_result ($result, 0, 'tablename');
		$entityidfield = $adb->query_result ($result, 0, 'entityidfield');
		if (!(strpos ($fieldsname, ',') === false)) {
			$fieldlists = explode (',', $fieldsname);
			$fieldsname = "concat(";
			$fieldsname = $fieldsname . implode (",' ',", $fieldlists);
			$fieldsname = $fieldsname . ")";
		}

		if ($entityName != '') {
			$sql    = "select $entityidfield from $tablename INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $tablename.$entityidfield " .
					  " WHERE vtiger_crmentity.deleted = 0 and $fieldsname=?";
			$result = $adb->pquery ($sql, array ($entityName));
			if ($adb->num_rows ($result) > 0) {
				$entityId = $adb->query_result ($result, 0, $entityidfield);
			}
		}
		if (!empty($entityId)) {
			return $entityId;
		} else {
			return 0;
		}
	}

	function decode_html ($str) {
		global $default_charset;
		// Direct Popup action or Ajax Popup action should be treated the same.
		if ($_REQUEST['action'] == 'Popup' || $_REQUEST['file'] == 'Popup') {
			return html_entity_decode ($str);
		} else {
			return html_entity_decode ($str, ENT_QUOTES, $default_charset);
		}
	}

	/**
	 * Alternative decoding function which coverts irrespective of $_REQUEST values.
	 * Useful incase of Popup (Listview etc...) where if decode_html will not work as expected
	 */
	function decode_html_force ($str) {
		global $default_charset;
		return html_entity_decode ($str, ENT_QUOTES, $default_charset);
	}

	function popup_decode_html ($str) {
		global $default_charset;
		$slashes_str = popup_from_html ($str);
		$slashes_str = htmlspecialchars ($slashes_str, ENT_QUOTES, $default_charset);
		return decode_html (br2nl ($slashes_str));
	}

	//function added to check the text length in the listview.
	function textlength_check ($field_val, $enableTooltip = true) {
		global $listview_max_textlength, $default_charset, $demoMode;
		$isTruncated = false;
		if ($listview_max_textlength && $listview_max_textlength > 0) {
			$temp_val = preg_replace ("/(<\/?)(\w+)([^>]*>)/i", "", $field_val);
			if (function_exists ('mb_strlen')) {
				if (mb_strlen ($temp_val) > $listview_max_textlength) {
					$temp_val = mb_substr (preg_replace ("/(<\/?)(\w+)([^>]*>)/i", "", $field_val), 0, $listview_max_textlength, $default_charset) . '...';
					$isTruncated = true;
				}
			} elseif (strlen ($field_val) > $listview_max_textlength) {
				$temp_val = substr (preg_replace ("/(<\/?)(\w+)([^>]*>)/i", "", $field_val), 0, $listview_max_textlength) . '...';
				$isTruncated = true;
			}
		} else {
			$temp_val = $field_val;
		}
		if ($demoMode) {
			$temp_val = '<span style="' . $demoModeStyle . '">' . $temp_val . '</span>';
		}
		
		// Si el texto fue truncado y el tooltip está habilitado, envolver en span con clase protip
		if ($isTruncated && $enableTooltip) {
			$fullText = preg_replace ("/(<\/?)(\w+)([^>]*>)/i", "", $field_val);
			// Limitar el texto completo a 1500 caracteres para el tooltip
			if (function_exists ('mb_strlen') && mb_strlen ($fullText) > 1500) {
				$fullText = mb_substr ($fullText, 0, 1500, $default_charset) . '...';
			} elseif (strlen ($fullText) > 1500) {
				$fullText = substr ($fullText, 0, 1500) . '...';
			}
			// Reemplazar saltos de línea por espacios para evitar problemas en el atributo HTML
			$fullText = str_replace(array("\r\n", "\r", "\n"), ' ', $fullText);
			// Escapar comillas y caracteres especiales para el atributo HTML
			$fullText = htmlspecialchars ($fullText, ENT_QUOTES, $default_charset);
			$temp_val = '<span class="protip" data-pt-title="' . $fullText . '">' . $temp_val . '</span>';
		}

		return $temp_val;
	}

	/** Function to get permitted fields of current user of a particular module to find duplicate records --Pavani */
	function getMergeFields ($module, $str) {
		global $adb, $current_user;
		$tabid = getTabid ($module);
		if ($str == "available_fields") {
			$result = getFieldsResultForMerge ($tabid);
		} else { //if($str == fileds_to_merge)
			$sql    = "SELECT * FROM vtiger_user2mergefields WHERE tabid=? AND userid=? AND visible=1";
			$result = $adb->pquery ($sql, array ($tabid, $current_user->id));
		}

		$num_rows = $adb->num_rows ($result);

		$user_profileid = fetchUserProfileId ($current_user->id);
		$permitted_list = getProfile2FieldPermissionList ($module, $user_profileid);

		$sql_def_org        = "SELECT fieldid FROM vtiger_def_org_field WHERE tabid=? AND visible=0";
		$result_def_org     = $adb->pquery ($sql_def_org, array ($tabid));
		$num_rows_org       = $adb->num_rows ($result_def_org);
		$permitted_org_list = Array ();
		for ($i = 0; $i < $num_rows_org; $i++) {
			$permitted_org_list[ $i ] = $adb->query_result ($result_def_org, $i, "fieldid");
		}

		$local_user = clone $current_user;
		require ('user_privileges/user_privileges.php');
		for ($i = 0; $i < $num_rows; $i++) {
			$field_id = $adb->query_result ($result, $i, "fieldid");
			foreach ($permitted_list as $field => $data) {
				if ($data[4] == $field_id and $data[1] == 0) {
					if ($is_admin == 'true' || (in_array ($field_id, $permitted_org_list))) {
						$field = "<option value=\"" . $field_id . "\">" . getTranslatedString ($data[0], $module) . "</option>";
						$fields .= $field;
						break;
					}
				}
			}
		}
		return $fields;
	}

	/**
	 * this function accepts a modulename and a fieldname and returns the first related module for it
	 * it expects the uitype of the field to be 10
	 *
	 * @param string $module - the modulename
	 * @param string $fieldname - the field name
	 *
	 * @return string $data - the first related module
	 */
	function getFirstModule ($module, $fieldname) {
		global $adb;
		$sql    = "SELECT fieldid, uitype FROM vtiger_field WHERE tabid=? AND fieldname=?";
		$result = $adb->pquery ($sql, array (getTabid ($module), $fieldname));

		if ($adb->num_rows ($result) > 0) {
			$uitype = $adb->query_result ($result, 0, "uitype");

			if ($uitype == 10) {
				$fieldid = $adb->query_result ($result, 0, "fieldid");
				$sql     = "SELECT * FROM vtiger_fieldmodulerel WHERE fieldid=?";
				$result  = $adb->pquery ($sql, array ($fieldid));
				$count   = $adb->num_rows ($result);

				if ($count > 0) {
					$data = $adb->query_result ($result, 0, "relmodule");
				}
			}
		}
		return $data;
	}

	function VT_getSimpleNavigationValues ($start, $size, $total) {
		$prev = $start - 1;
		if ($prev < 0) {
			$prev = 0;
		}
		if ($total === null) {
			return array (
				'start' => $start, 'first' => $start, 'current' => $start, 'end' => $start, 'end_val' => $size, 'allflag' => 'All',
				'prev'  => $prev, 'next' => $start + 1, 'verylast' => 'last',
			);
		}
		if (empty($total)) {
			$lastPage = 1;
		} else {
			$lastPage = ceil ($total / $size);
		}

		$next = $start + 1;
		if ($next > $lastPage) {
			$next = 0;
		}
		return array (
			'start' => $start, 'first' => $start, 'current' => $start, 'end' => $start, 'end_val' => $size, 'allflag' => 'All',
			'prev'  => $prev, 'next' => $next, 'verylast' => $lastPage,
		);
	}

	/* * Function to get the simplified table headers for a listview
 * Param $navigation_arrray - navigation values in array
* Param $url_qry - url string
* Param $module - module name
* Param $action- action file name
* Param $viewid - view id
* Returns an string value
*/

	function getTableHeaderSimpleNavigation ($navigation_array, $url_qry, $module = '', $action_val = 'index', $viewid = '') {
		global $log, $app_strings;
		global $theme, $current_user;
		$theme_path = "themes/" . $theme . "/";
		$image_path = $theme_path . "images/";
		if ($module == 'Documents') {
			// $output = '<td class="mailSubHeader" width="40%" align="right">';
		} else {
			// $output = '<td align="right" style="padding: 5px;">';
		}
		$output     = '<ul class="pagination pull-right">';
		$tabname    = getParentTab ();
		$search_tag = $_REQUEST['search_tag'];
		$url_string = '';

		// vtlib Customization : For uitype 10 popup during paging
		if ($_REQUEST['form'] == 'vtlibPopupView') {
			$url_string .= '&form=vtlibPopupView&forfield=' . vtlib_purify ($_REQUEST['forfield']) . '&srcmodule=' . vtlib_purify ($_REQUEST['srcmodule']) . '&forrecord=' . vtlib_purify ($_REQUEST['forrecord']);
		}
		// END

		if ($module == 'Calendar' && $action_val == 'index') {
			if ($_REQUEST['view'] == '') {
				if ($current_user->activity_view == "This Year") {
					$mysel = 'year';
				} else if ($current_user->activity_view == "This Month") {
					$mysel = 'month';
				} else if ($current_user->activity_view == "This Week") {
					$mysel = 'week';
				} else {
					$mysel = 'day';
				}
			}
			$data_value = date ('Y-m-d H:i:s');
			preg_match ('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $data_value, $value);
			$date_data = Array (
				'day'   => $value[3],
				'month' => $value[2],
				'year'  => $value[1],
				'hour'  => $value[4],
				'min'   => $value[5],
			);
			$tab_type  = ($_REQUEST['subtab'] == '') ? 'event' : vtlib_purify ($_REQUEST['subtab']);
			$url_string .= isset($_REQUEST['view']) ? "&view=" . vtlib_purify ($_REQUEST['view']) : "&view=" . $mysel;
			$url_string .= isset($_REQUEST['subtab']) ? "&subtab=" . vtlib_purify ($_REQUEST['subtab']) : '';
			$url_string .= isset($_REQUEST['viewOption']) ? "&viewOption=" . vtlib_purify ($_REQUEST['viewOption']) : '&viewOption=listview';
			$url_string .= isset($_REQUEST['day']) ? "&day=" . vtlib_purify ($_REQUEST['day']) : '&day=' . $date_data['day'];
			$url_string .= isset($_REQUEST['week']) ? "&week=" . vtlib_purify ($_REQUEST['week']) : '';
			$url_string .= isset($_REQUEST['month']) ? "&month=" . vtlib_purify ($_REQUEST['month']) : '&month=' . $date_data['month'];
			$url_string .= isset($_REQUEST['year']) ? "&year=" . vtlib_purify ($_REQUEST['year']) : "&year=" . $date_data['year'];
			$url_string .= isset($_REQUEST['n_type']) ? "&n_type=" . vtlib_purify ($_REQUEST['n_type']) : '';
			$url_string .= isset($_REQUEST['search_option']) ? "&search_option=" . vtlib_purify ($_REQUEST['search_option']) : '';
		}
		if ($module == 'Calendar' && $action_val != 'index') //added for the All link from the homepage -- ticket 5211
		{
			$url_string .= isset($_REQUEST['from_homepage']) ? "&from_homepage=" . vtlib_purify ($_REQUEST['from_homepage']) : '';
		}

		if (($navigation_array['prev']) != 0) {
			if ($module == 'Calendar' && $action_val == 'index') {
				$output .= '<li><a href="javascript:;" onClick="cal_navigation(\'' . $tab_type . '\',\'' . $url_string . '\',\'&start=1\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><i class="fa fa-step-backward"></i></a></li>';
				$output .= '<li><a href="javascript:;" onClick="cal_navigation(\'' . $tab_type . '\',\'' . $url_string . '\',\'&start=' . $navigation_array['prev'] . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><i class="fa fa-chevron-left"></i></a></li>';
			} else if ($action_val == "FindDuplicate") {
				$output .= '<li><a href="javascript:;" onClick="getDuplicateListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=1' . $url_string . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><i class="fa fa-step-backward"></i></a></li>';
				$output .= '<li><a href="javascript:;" onClick="getDuplicateListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['prev'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><i class="fa fa-chevron-left"></i></a></li>';
			} elseif ($action_val == 'UnifiedSearch') {
				$output .= '<li><a href="javascript:;" onClick="getUnifiedSearchEntries_js(\'' . $search_tag . '\',\'' . $module . '\',\'parenttab=' . $tabname . '&start=1' . $url_string . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><i class="fa fa-step-backward"></i></a></li>';
				$output .= '<li><a href="javascript:;" onClick="getUnifiedSearchEntries_js(\'' . $search_tag . '\',\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['prev'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><i class="fa fa-chevron-left"></i></a></li>';
			} elseif ($module == 'Documents') {
				$output .= '<li><a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=1' . $url_string . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><i class="fa fa-step-backward"></i></a></li>';
				$output .= '<li><a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['prev'] . $url_string . '&folderid=' . $action_val . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><i class="fa fa-chevron-left"></i></a></li>';
			} else {
				$output .= '<li><a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=1' . $url_string . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><i class="fa fa-step-backward"></i></a></li>';
				$output .= '<li><a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['prev'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><i class="fa fa-chevron-left"></i></a></li>';
			}
		} else {
			// $output .= '<img src="' . vtiger_imageurl('start_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
			// $output .= '<img src="' . vtiger_imageurl('previous_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
			$output .= '<li class="disabled"><a href="javascript:void(0);"><i class="fa fa-step-backward"></i></a></li>';
			$output .= '<li class="disabled"><a href="javascript:void(0);"><i class="fa fa-chevron-left"></i></a></li>';
		}
		if ($module == 'Calendar' && $action_val == 'index') {
			$jsNavigate = "cal_navigation('$tab_type','$url_string','&start='+this.value);";
		} else if ($action_val == "FindDuplicate") {
			$jsNavigate = "getDuplicateListViewEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string');";
		} elseif ($action_val == 'UnifiedSearch') {
			$jsNavigate = "getUnifiedSearchEntries_js('$search_tag','$module','parenttab=$tabname&start='+this.value+'$url_string');";
		} elseif ($module == 'Documents') {
			$jsNavigate = "getListViewEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string&folderid=$action_val');";
		} else {
			$jsNavigate = "getListViewEntries_js('$module','parenttab=$tabname&start='+this.value+'$url_string');";
		}
		if ($module == 'Documents' && $action_val != 'UnifiedSearch') {
			$url = '&folderid=' . $action_val;
		} else {
			$url = '';
		}
		$jsHandler = "return VT_disableFormSubmit(event);";
		/*$output .= "<input class='small' name='pagenum' type='text' value='{$navigation_array['current']}'
		style='width: 3em;margin-right: 0.7em;' onchange=\"$jsNavigate\"
	onkeypress=\"$jsHandler\">";*/
		//$output .= "<span name='" . $module . "_listViewCountContainerName' >";
		$output .= '
			<li>
			<span class="pagination-search">
			<input type="text" class="form-control" id="pagenum" name="pagenum" value="' . $navigation_array['current'] . '" onchange="' . $jsNavigate . '" onkeypress="' . $jsHandler . '">

					';
		if (PerformancePrefs::getBoolean ('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true) {
			$output .= $app_strings['LBL_LIST_OF'] . ' ' . $navigation_array['verylast'];
		} else {
			$output .= "<img src='" . vtiger_imageurl ('windowRefresh.gif', $theme) . "' alt='" . $app_strings['LBL_HOME_COUNT'] . "'
				onclick='getListViewCount(\"" . $module . "\",this,this.parentNode,\"" . $url . "\")'
						align='absmiddle' name='" . $module . "_listViewCountRefreshIcon'/>
								<img name='" . $module . "_listViewCountContainerBusy' src='" . vtiger_imageurl ('vtbusy.gif', $theme) . "' style='display: none;'
										align='absmiddle' alt='" . $app_strings['LBL_LOADING'] . "'>";
		}
		$output .= '</span></li>';

		if (($navigation_array['next']) != 0) {
			if ($module == 'Calendar' && $action_val == 'index') {
				$output .= '<li><a href="javascript:;" onClick="cal_navigation(\'' . $tab_type . '\',\'' . $url_string . '\',\'&start=' . $navigation_array['next'] . '\');" alt="' . $app_strings['LNK_LIST_NEXT'] . '" title="' . $app_strings['LNK_LIST_NEXT'] . '"><i class="fa fa-chevron-right"></i></a></li>';
				$output .= '<li><a href="javascript:;" onClick="cal_navigation(\'' . $tab_type . '\',\'' . $url_string . '\',\'&start=' . $navigation_array['verylast'] . '\');" alt="' . $app_strings['LBL_LAST'] . '" title="' . $app_strings['LBL_LAST'] . '"><i class="fa fa-step-forward"></i></a></li>';
			} else if ($action_val == "FindDuplicate") {
				$output .= '<li><a href="javascript:;" onClick="getDuplicateListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['next'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_NEXT'] . '" title="' . $app_strings['LNK_LIST_NEXT'] . '"><i class="fa fa-chevron-right"></i></a></li>';
				$output .= '<li><a href="javascript:;" onClick="getDuplicateListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['verylast'] . $url_string . '\');" alt="' . $app_strings['LBL_LAST'] . '" title="' . $app_strings['LBL_LAST'] . '"><i class="fa fa-step-forward"></i></a></li>';
			} elseif ($action_val == 'UnifiedSearch') {
				$output .= '<li><a href="javascript:;" onClick="getUnifiedSearchEntries_js(\'' . $search_tag . '\',\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['next'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_NEXT'] . '" title="' . $app_strings['LNK_LIST_NEXT'] . '"><i class="fa fa-chevron-right"></i></a></li>';
				$output .= '<li><a href="javascript:;" onClick="getUnifiedSearchEntries_js(\'' . $search_tag . '\',\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['verylast'] . $url_string . '\');" alt="' . $app_strings['LBL_LAST'] . '" title="' . $app_strings['LBL_LAST'] . '"><i class="fa fa-step-forward"></i></a></li>';
			} elseif ($module == 'Documents') {
				$output .= '<li><a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['next'] . $url_string . '&folderid=' . $action_val . '\');" alt="' . $app_strings['LNK_LIST_NEXT'] . '" title="' . $app_strings['LNK_LIST_NEXT'] . '"><i class="fa fa-chevron-right"></i></a></li>';
				$output .= '<li><a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['verylast'] . $url_string . '&folderid=' . $action_val . '\');" alt="' . $app_strings['LBL_LAST'] . '" title="' . $app_strings['LBL_LAST'] . '"><i class="fa fa-step-forward"></i></a></li>';
			} else {
				$output .= '<li><a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['next'] . $url_string . '\');" alt="' . $app_strings['LNK_LIST_NEXT'] . '" title="' . $app_strings['LNK_LIST_NEXT'] . '"><i class="fa fa-chevron-right"></i></a></li>';
				$output .= '<li><a href="javascript:;" onClick="getListViewEntries_js(\'' . $module . '\',\'parenttab=' . $tabname . '&start=' . $navigation_array['verylast'] . $url_string . '\');" alt="' . $app_strings['LBL_LAST'] . '" title="' . $app_strings['LBL_LAST'] . '"><i class="fa fa-step-forward"></i></a></li>';
			}
		} else {
			// $output .= '<li><img src="' . vtiger_imageurl('next_disabled.gif', $theme) . '" border="0" align="absmiddle"></li>';
			// $output .= '<li><img src="' . vtiger_imageurl('end_disabled.gif', $theme) . '" border="0" align="absmiddle"></li>';
			$output .= '<li class="disabled"><a href="javascript:void(0);"><i class="fa fa-chevron-right"></i></a></li>';
			$output .= '<li class="disabled"><a href="javascript:void(0);"><i class="fa fa-step-forward"></i></a></li>';
		}
		$output .= '</ul>';

		if ($navigation_array['first'] == '') {
			return;
		} else {
			return $output;
		}
	}

	function getRecordRangeMessage ($listResult, $limitStartRecord, $totalRows = '') {
		global $adb, $app_strings;
		$numRows            = $adb->num_rows ($listResult);
		$recordListRangeMsg = '';
		if ($numRows > 0) {
			$recordListRangeMsg = $app_strings['LBL_SHOWING'] . ' ' . $app_strings['LBL_RECORDS'] .
								  ' ' . ($limitStartRecord + 1) . ' - ' . ($limitStartRecord + $numRows);
			if (PerformancePrefs::getBoolean ('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true) {
				$recordListRangeMsg .= ' ' . $app_strings['LBL_LIST_OF'] . " $totalRows";
			}
		}
		return $recordListRangeMsg;
	}

	function listQueryNonAdminChange ($query, $module, $scope = '') {
		$instance = CRMEntity::getInstance ($module);
		return $instance->listQueryNonAdminChange ($query, $scope);
	}

	function html_strlen ($str) {
		$chars = preg_split ('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		return count ($chars);
	}

	function html_substr ($str, $start, $length = null) {
		if ($length === 0) {
			return "";
		}
		//check if we can simply use the built-in functions
		if (strpos ($str, '&') === false) { //No entities. Use built-in functions
			if ($length === null) {
				return substr ($str, $start);
			} else {
				return substr ($str, $start, $length);
			}
		}

		// create our array of characters and html entities
		$chars       = preg_split ('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
		$html_length = count ($chars);
		// check if we can predict the return value and save some processing time
		if (($html_length === 0) or ($start >= $html_length) or (isset($length) and ($length <= -$html_length))) {
			return "";
		}

		//calculate start position
		if ($start >= 0) {
			$real_start = $chars[ $start ][1];
		} else { //start'th character from the end of string
			$start      = max ($start, -$html_length);
			$real_start = $chars[ $html_length + $start ][1];
		}
		if (!isset($length)) // no $length argument passed, return all remaining characters
		{
			return substr ($str, $real_start);
		} else if ($length > 0) { // copy $length chars
			if ($start + $length >= $html_length) { // return all remaining characters
				return substr ($str, $real_start);
			} else { //return $length characters
				return substr ($str, $real_start, $chars[ max ($start, 0) + $length ][1] - $real_start);
			}
		} else { //negative $length. Omit $length characters from end
			return substr ($str, $real_start, $chars[ $html_length + $length ][1] - $real_start);
		}
	}

	function counterValue () {
		static $counter = 0;
		$counter = $counter + 1;
		return $counter;
	}

	function buildFilterSearch ($viewid, $module) {
		global $adb;
		$lstFiltros   = '';
		$lstTablas    = '';
		$lstTipoDato  = '';
		$lstCondition = '';
		$lstColumnas  = '';
		$bufferSalida = '';

		$sql = "SELECT columnname FROM vtiger_cvcolumnlist WHERE cvid = " . $viewid;

		$result = $adb->query ($sql);

		if ($result) {
			$bufferSalida = '
				function callFilterSearc() {
				';
			while ($row = $adb->fetchByAssoc ($result)) {

				if (!empty($lstFiltros)) {
					$lstFiltros .= ',';
				}
				if (!empty($lstTablas)) {
					$lstTablas .= ',';
				}
				if (!empty($lstTipoDato)) {
					$lstTipoDato .= ',';
				}
				if (!empty($lstCondition)) {
					$lstCondition .= ',';
				}
				if (!empty($lstColumnas)) {
					$lstColumnas .= ',';
				}
				$valor = $row['columnname'];

				$list = explode (':', $valor);

				if ($list[1] == 'related_to') {
					$condition = 'bwt';
				}

				$lstFiltros .= "'" . $list[1] . "'";
				$lstTablas .= "'" . $list[0] . "'";
				$lstTipoDato .= "'" . $list[4] . "'";
				$lstColumnas .= "'" . $valor . "'";
			}

			$bufferSalida .= '

				lstColumnas = Array(' . $lstColumnas . ');
						lstCampos = Array(' . $lstFiltros . ');
								lstTablas = Array(' . $lstTablas . ');
										lstTipoDato = Array(' . $lstTipoDato . ');

												/*Aca voy a ir cargando el array lstCondition, con los valores correctos. Dependiendo del tipo de comodin que se ingreso.*/
												lstCondition = new Array();
												valores = new Array();
												var marcador;


												for(i=0;i<lstCampos.length;i++){

												eval("ctrl = document.getElementById(\'fld_"+lstCampos[i]+"\');")
												if (ctrl)
												str = ctrl.value;
												else
												str = \'\';

												pos=str.indexOf("*");
												if(pos>=0){

												strlen=str.length;
												primercar=str.substring(0,1);
												ultimocar=str.substring(strlen-1,strlen);

												if(primercar=="*" & ultimocar=="*"){
												str=str.substring(1,strlen-1);
												marcador=\'*\'+str+\'*\';
												lstCondition[i] = \'cts\';
	}

												if(primercar=="*" & ultimocar!="*"){
												str=str.substring(1,strlen);
												marcador=\'*\'+str;
												lstCondition[i] = \'ewt\';
	}

												if(ultimocar=="*" & primercar!="*"){
												str=str.substring(0,strlen-1);
												marcador=str+\'*\';
												lstCondition[i] = \'bwt\';
	}

												if(i==(lstCampos.length-1)){

												if(ultimocar=="*" & primercar!="*"){//-02-2010*
												str=str.substring(0,strlen-1);
												marcador=str+\'*\';
												lstCondition[i] = \'grteq\';
	}

	}

												document.getElementById(""+lstCampos[i]+"").value=str;
	}
												else{
												lstCondition[i] = \'cts\';
												marcador=str;
	}

												valores[i]=marcador;
	}


												j = 0;
												sConsulta = \'\';

												for(i=0;i < lstCampos.length;i++) {
												eval("ctrl = document.getElementById(\'fld_"+lstCampos[i]+"\');")

												if (ctrl && ctrl.value != \'\') {
												if (sConsulta && sConsulta.value != \'\')
												sConsulta+= \',\';

												if (j == 0)
												sConsulta = \'&advft_criteria=[null,\';
												sConsulta+= \'{"groupid":"1","columnname":"\'+lstColumnas[i]+\'","comparator":"c","value":"\'+encodeURIComponent(ctrl.value)+\'","columncondition":"and"}\';
												j++;
	}
	}

												sConsulta += \']&advft_criteria_groups=[null,{"groupcondition":""}]&searchtype=advance\';

												if (sConsulta==\'\') {
												window.location = \'index.php?action=ListView&module=' . $module . '&parenttab=Support\';
														return false;
	}

														sConsulta+= \'&parenttab=Support&matchtype=all&search_cnt=\'+j+\'&searchtype=advance&\';
														new Ajax.Request(
														\'index.php\',
														{queue: {position:  \'end\', scope: \'command\'},
														method: \'post\',
														postBody:sConsulta+\'query=true&file=index&module=' . $module . '&action=' . $module . 'Ajax&ajax=true&search=true\',
																onComplete: function(response) {
																$("status").style.display="none";
																result = response.responseText.split(\'&#&#&#\');
																$("ListViewContents").innerHTML= result[2];


																/*Este For lo que hace es levantar los valores de los diferentes criterios de busqueda ingresados*/

																for(i=0;i<lstCampos.length;i++){
																eval("ctrl = document.getElementById(\'fld_"+lstCampos[i]+"\');")
																if (ctrl)
																ctrl.value = valores[i];
	}

																var scriptTags = $("ListViewContents").getElementsByTagName("script");
																for(var i = 0; i< scriptTags.length; i++){
																var scriptTag = scriptTags[i];
																eval(scriptTag.innerHTML);
	}

																if(result[1] != \'\')
																alert(result[1]);
																$(\'basicsearchcolumns\').innerHTML = \'\';
	}
	}
																);
																return false;
	}';

			$bufferSalida .= '
				function borrar_campos() {
				lstCampos = Array(' . $lstFiltros . ');

						for(i=0;i < lstCampos.length;i++) {
						eval("ctrl = document.getElementById(\'fld_"+lstCampos[i]+"\');")

						if (ctrl)
						ctrl.value = \'\';
	}
	}

						function realizarBusquedaFiltros(evn) {
						if (teclaEvento(evn)== 13) {
						callFilterSearc();
	}
	}
						';
		}
		return $bufferSalida;
	}

	function getListValues ($fieldname, $module) {
		global $adb;
		$realfieldname = getRealFieldName ($fieldname, $module);
		$sql           = "SELECT " . $realfieldname . " FROM vtiger_" . $realfieldname;
		$result        = $adb->query ($sql);
		$numrows       = $adb->num_rows ($result);
		$idfieldname   = "fld_$fieldname";
		$filtro        = "<select name=\"$fieldname\" id=\"$idfieldname\" class=\"small\" $fsJS>" .
						 "<option value=\"\">--Seleccione--</option>";
		for ($i = 0; $i < $numrows; $i++) {
			$selected = "";
			$temp_val = decode_html ($adb->query_result ($result, $i, $realfieldname));
			if ($temp_val == $oldvalue) {
				$selected = "selected";
			}
			$value = ($current_module_strings[ $temp_val ] != '') ? $current_module_strings[ $temp_val ] : (($app_strings[ $temp_val ] != '') ? ($app_strings[ $temp_val ]) : $temp_val);
			$filtro .= "<option value=\"$value\" $selected>$value</option>";
		}
		$filtro .= "</select>";
		return $filtro;
	}

	function getRelatedValues ($fieldname, $module) {
		global $adb;
		$sql    = "SELECT fieldid FROM vtiger_field A INNER JOIN vtiger_tab B
			ON (A.tabid = B.tabid  AND B.name = '" . $module . "')
					WHERE columnname = '" . $fieldname . "'";
		$result = $adb->query ($sql);

		if ($result) {
			$row = $adb->fetchByAssoc ($result);

			$sql = "SELECT tablename, fieldname, entityidfield FROM vtiger_entityname INNER JOIN vtiger_fieldmodulerel ON (vtiger_entityname.modulename = vtiger_fieldmodulerel.relmodule)
				WHERE vtiger_fieldmodulerel.fieldid	= " . $row['fieldid'] . " ORDER BY sequence";

			$result2 = $adb->query ($sql);

			if ($result2) {
				$row2 = $adb->fetchByAssoc ($result2);

				$sqlQuery = "SELECT CONCAT(" . $row2['fieldname'] . ") as " . $fieldname . " FROM " . $row2['tablename'] . " INNER JOIN vtiger_crmentity ON (" . $row2['tablename'] . "." . $row2['entityidfield'] . " = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0) ORDER BY 1 ASC";
			}
		}

		$result = $adb->query ($sqlQuery);

		$numrows     = $adb->num_rows ($result);
		$idfieldname = "fld_$fieldname";
		$filtro      = "<select name=\"$fieldname\" id=\"$idfieldname\" class=\"small\" $fsJS>" .
					   "<option value=\"\">--Seleccione--</option>";
		for ($i = 0; $i < $numrows; $i++) {
			$selected = "";
			$temp_val = decode_html ($adb->query_result ($result, $i, $fieldname));
			if ($temp_val == $oldvalue) {
				$selected = "selected";
			}
			$value = ($current_module_strings[ $temp_val ] != '') ? $current_module_strings[ $temp_val ] : (($app_strings[ $temp_val ] != '') ? ($app_strings[ $temp_val ]) : $temp_val);
			$filtro .= "<option value=\"$value\" $selected>$value</option>";
		}
		$filtro .= "</select>";
		return $filtro;
	}

	function getFiltersValues ($fieldname, $module) {
		global $adb;
		global $theme;
		global $app_strings, $current_language, $current_user;
		$theme_path             = "themes/" . $theme . "/";
		$image_path             = $theme_path . "images/";
		$current_module_strings = return_module_language ($current_language, $module);

		$sufijoCampo = '';
		$uitype      = getUITypeField ($fieldname, $module);

		if ($uitype == 1 || $uitype == 2 || $uitype == 4 || $uitype == 17 || $uitype == 11 || $uitype == 55 || $uitype == 255 || $uitype == 13 || $uitype == 22) {
			$filtro = "<input type=\"text\" name=\"$fieldname$sufijoCampo\" id=\"fld_$fieldname\" OnKeyUp=\"realizarBusquedaFiltros(event);\" style=\"width:80%\"/>";
		} elseif ($uitype == 23 || $uitype == 5 || $uitype == 6) {
			$date_format = $current_user->date_format;
			$date_format = str_replace ('yyyy', '%Y', $date_format);
			$date_format = str_replace ('mm', '%m', $date_format);
			$date_format = str_replace ('dd', '%d', $date_format);
			$filtro      = '
				<input name="' . $fieldname . '" id="fld_' . $fieldname . '" type="text" style="border:1px solid #bababa;" size="11" maxlength="10" value="">
						<img src="' . $image_path . '/btnL3Calendar.gif" id="jscal_trigger_' . $fieldname . '">
								<script type="text/javascript" id=\'massedit_calendar_' . $fieldname . '\'>
										Calendar.setup ({
										inputField : "fld_' . $fieldname . '", ifFormat : "' . $date_format . '", showsTime : false, button : "jscal_trigger_' . $fieldname . '", singleClick : true, step : 1
	})
												</script>
												';
		} elseif ($uitype == 10 || $uitype == 404) {
			$filtro = getRelatedValues ($fieldname, $module);
		} elseif ($uitype == 53) {
			$valores = get_user_array (false);
			$filtro  = "<select name=\"$fieldname$sufijoCampo\" id=\"fld_$fieldname\" class=\"small\">" .
					   "<option value=\"\">--Seleccione--</option>";
			foreach ($valores as $userdate => $user) {
				if ($user == 'dpenesi') {
					$filtro .= "<option value=\"$user\" selected>$user</option>";
				}

				$filtro .= "<option value=\"$user\">$user</option>";
			}
			$filtro .= "</select>";
		} elseif ($uitype == 15) {
			$filtro = getListValues ($fieldname, $module);
		}

		return $filtro;
	}

	function getUITypeField ($fieldname, $module) {
		global $adb;

		$sql = "SELECT uitype FROM vtiger_field A INNER JOIN vtiger_tab B
			ON (A.tabid = B.tabid  AND B.name = '" . $module . "')
					WHERE columnname = '" . $fieldname . "'";

		$result = $adb->query ($sql);
		return $adb->query_result ($result, $i, "uitype");
	}

	function getRealFieldName ($fieldname, $module) {
		global $adb;

		$sql = "SELECT fieldname FROM vtiger_field A INNER JOIN vtiger_tab B
			ON (A.tabid = B.tabid  AND B.name = '" . $module . "')
					WHERE columnname = '" . $fieldname . "'";

		$result = $adb->query ($sql);
		return $adb->query_result ($result, $i, "fieldname");
	}

	function getRelatedEntities ($id, $module, $relmodule) {
		global $adb;
		$focus = CRMEntity::getInstance ($module);

		$query = $focus->get_related_list ($id, getTabid ($module), getTabid ($relmodule), false, true);

		$result = $adb->query ($query);
		$value  = '';

		if ($result) {
			while ($row = $adb->fetchByAssoc ($result)) {
				if ($row['crmid'] != '') {
					$relfocus     = CRMEntity::getInstance ($relmodule);
					$relfocus->id = $row['crmid'];
					$relfocus->retrieve_entity_info ($row['crmid'], $relmodule);

					if (!empty($value)) {
						$value .= ", ";
					}
					$values = getEntityName ($relmodule, $row['crmid']);
					$value .= '<a href="index.php?module=' . $relmodule . '&action=DetailView&record=' . $row['crmid'] . '">' . $values[ $row['crmid'] ] . '</a>';
				}
			}
		}
		return $value;
	}

	function getRelateListButtonKanban ($module, $entity_id) {
		global $mod_strings, $modalEdit, $callbackEditModal, $currentModule, $clientView, $demoMode, $adb, $current_user, $app_strings, $plat;
		$relatedlist = isPresentRelatedLists ($module);

		if (isPermitted ($module, "Delete", "") == 'yes') {
			$del_link = getListViewDeleteLink ($module, $entity_id, null, null);
			if ($del_link != "") {
				$actionLinkInfo = "<a href='javascript:confirmdelete(\"" . addslashes (urlencode ($del_link . "&modeview=viewkanban")) . "\")' >
											<i class='fa fa-trash-o'></i>&nbsp;Eliminar</a>
									</a>";
			}
		}

		$del_link = "index.php?module=" . $module . "&action=Delete&record=" . $entity_id . "&return_module=" . $module . "&return_action=index&parenttab=&return_viewname=" . $_REQUEST['viewname'] . "&modeview=viewlist";
		if (is_array ($relatedlist)) {
			$bufferSalida = '<div class="btn-group">
				<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" style="padding: 0px 5px; background: #FFFFFF important! font-size: 10px; background-color:#CCD0C1;border-color:#84867A;color:#FFFFFF;"">
				<i class="fa">&nbsp;...&nbsp;</i><span class="caret"></span> 
				</button>
				<ul class="dropdown-menu" role="menu" style="left: -120px;">';
			foreach ($relatedlist as $k => $r) {
				$bufferSalida .= '<li><a href="index.php?action=CallRelatedList&module=' . $module . '&record=' . $entity_id . '&parenttab=&selected_header=' . $r . '&relation_id=' . $k . '&platdb="><small><i class="fa fa-plus"></i>&nbsp;' . getTranslatedString ($r) . '</small></a></li>';
			}
			$bufferSalida .= '<li><div style="float:left; text-aling:center;padding-left:35px;">
					<a id="winnPotential" href="javascript:void(0)" tagModule= "' . $module . '" onclick="changeStatePotential(\'Closed Won\',' . $entity_id . ', \'lostPotential\', \'winnPotential\');" class="btn btn-success pull-right" style="margin-right:5px;">
						<span class="fa"></span> ' . $app_strings["LBL_WINN_BUTTON_LABEL"] . '
					</a><br/><br/>
					<a id="lostPotential" href="javascript:void(0)" tagModule= "' . $module . '" onclick="changeStatePotential(\'Closed Lost\',' . $entity_id . ', \'winnPotential\', \'lostPotential\');" class="btn btn-danger pull-right" style="margin-right:5px;">
						<span class="fa"></span>' . $app_strings["LBL_LOST_BUTTON_LABEL"] . '
					</a><br/><br/>
					' . $actionLinkInfo . '
					</div>
					</li>
				</ul>
				</div>';
		}
		// echo "<pre>".print_r($relatedlist,true)."</pre>";
		return $bufferSalida;
	}

?>

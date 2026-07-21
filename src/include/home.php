<?php
	/*********************************************************************************
	 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
	 * ("License"); You may not use this file except in compliance with the License
	 * The Original Code is:  vtiger CRM Open Source
	 * The Initial Developer of the Original Code is vtiger.
	 * Portions created by vtiger are Copyright (C) vtiger.
	 * All Rights Reserved.
	 *
	 ********************************************************************************/
	global $current_user;
	require_once ('include/utils/ListViewUtils.php');
	require_once ('modules/CustomView/CustomView.php');
	require_once ('include/DatabaseUtil.php');
	require_once ('include/utils/CommonUtils.php');
	$local_user = clone $current_user;
	require ('user_privileges/user_privileges.php');

	class Homestuff {
		var    $userid;
		var    $dashdetails   = array ();
		var    $reportdetails = array ();
		public $selreportcharttype;
		public $selreport;
		public $stufftype;
		public $stufftitle;

		/**
		 * this is the constructor for the class
		 */
		function Homestuff () {
		}

		/**
		 * this function adds a new widget information to the database
		 */
		function addStuff () {
			global $adb;
			global $current_user;
			global $current_language;
			$dashbd_strings = return_module_language ($current_language, "Dashboard");
			$stuffid        = $adb->getUniqueId ('vtiger_homestuff');
			$queryseq       = "SELECT max(stuffsequence)+1 AS seq FROM vtiger_homestuff";
			$sequence       = $adb->query_result ($adb->pquery ($queryseq, array ()), 0, 'seq');
			if ($this->defaulttitle != "") {
				$this->stufftitle = $this->defaulttitle;
			}
			$query  = "INSERT INTO vtiger_homestuff(stuffid, stuffsequence, stufftype, userid, visible, stufftitle) VALUES(?, ?, ?, ?, ?, ?)";
			$params = array ($stuffid, $sequence, $this->stufftype, $current_user->id, 0, $this->stufftitle);
			$result = $adb->pquery ($query, $params);
			if (!$result) {
				return false;
			}

			if ($this->stufftype == "Module") {
				$fieldarray = explode (",", $this->fieldvalue);
				$querymod   = "INSERT INTO vtiger_homemodule(stuffid, modulename, maxentries, customviewid, setype) VALUES(?, ?, ?, ?, ?)";
				$params     = array ($stuffid, $this->selmodule, $this->maxentries, $this->selFiltername, $this->selmodule);
				$result     = $adb->pquery ($querymod, $params);
				if (!$result) {
					return false;
				}

				for ($q = 0; $q < sizeof ($fieldarray); $q++) {
					$queryfld = "INSERT INTO vtiger_homemoduleflds VALUES(? ,?);";
					$params   = array ($stuffid, $fieldarray[ $q ]);
					$result   = $adb->pquery ($queryfld, $params);
				}

				if (!$result) {
					return false;
				}
			} else if ($this->stufftype == "RSS") {
				$queryrss  = "INSERT INTO vtiger_homerss VALUES(?,?,?)";
				$params    = array ($stuffid, $this->txtRss, $this->maxentries);
				$resultrss = $adb->pquery ($queryrss, $params);
				if (!$resultrss) {
					return false;
				}
			} else if ($this->stufftype == "DashBoard") {
				$querydb  = "INSERT INTO vtiger_homedashbd VALUES(?,?,?)";
				$params   = array ($stuffid, $this->seldashbd, $this->seldashtype);
				$resultdb = $adb->pquery ($querydb, $params);
				if (!$resultdb) {
					return false;
				}
			} else if ($this->stufftype == "Default") {
				$querydef  = "INSERT INTO vtiger_homedefault VALUES(?, ?)";
				$params    = array ($stuffid, $this->defaultvalue);
				$resultdef = $adb->pquery ($querydef, $params);
				if (!$resultdef) {
					return false;
				}
			} else if ($this->stufftype == 'Notebook') {
				$userid = $current_user->id;
				$query  = "INSERT INTO vtiger_notebook_contents VALUES(?,?,?)";
				$params = array ($userid, $stuffid, '');
				$result = $adb->pquery ($query, $params);
				if (!$result) {
					return false;
				}
			} else if ($this->stufftype == 'URL') {
				$userid = $current_user->id;
				$query  = "INSERT INTO vtiger_homewidget_url VALUES(?, ?)";
				$result = $adb->pquery ($query, array ($stuffid, $this->txtURL));
				if (!$result) {
					return false;
				}
			} else if ($this->stufftype == "ReportCharts") {
				$querydb  = "INSERT INTO vtiger_homereportchart VALUES(?,?,?)";
				$params   = array ($stuffid, $this->selreport, $this->selreportcharttype);
				$resultdb = $adb->pquery ($querydb, $params);
				if (!$resultdb) {
					return false;
				}
			}
			return "loadAddedDiv($stuffid,'" . $this->stufftype . "')";
		}

		/**
		 * this function returns the information about a widget in an array
		 * @return array(stuffid=>"id", stufftype=>"type", stufftitle=>"title")
		 */
		function getHomePageFrame () {
			global $adb;
			global $current_user;
			$querystuff  = "SELECT vtiger_homestuff.stuffid,stufftype,stufftitle,setype FROM vtiger_homestuff
						LEFT JOIN vtiger_homedefault ON vtiger_homedefault.stuffid=vtiger_homestuff.stuffid
						WHERE visible=0 AND userid=? ORDER BY stuffsequence DESC";
			$resultstuff = $adb->pquery ($querystuff, array ($current_user->id));
			for ($i = 0; $i < $adb->num_rows ($resultstuff); $i++) {
				$modulename = $adb->query_result ($resultstuff, $i, 'setype');
				$stuffid    = $adb->query_result ($resultstuff, $i, 'stuffid');
				$stufftype  = $adb->query_result ($resultstuff, $i, 'stufftype');
				if (!empty($modulename) && $modulename != 'NULL') {
					if (!vtlib_isModuleActive ($modulename)) {
						continue;
					}
				} elseif ($stufftype == 'Module') {
					//check for setype in vtiger_homemodule table and hide if module is de-activated
					$sql           = "SELECT setype FROM vtiger_homemodule WHERE stuffid=?";
					$result_setype = $adb->pquery ($sql, array ($stuffid));
					if ($adb->num_rows ($result_setype) > 0) {
						$module_name = $adb->query_result ($result_setype, 0, "setype");
					}
					if (!empty($module_name) && $module_name != 'NULL') {
						if (!vtlib_isModuleActive ($module_name)) {
							continue;
						}
					}
				} elseif ($stufftype == 'DashBoard') {
					if (!vtlib_isModuleActive ('Dashboard')) {
						continue;
					}
				} elseif (!empty($stufftype) && $stufftype == 'RSS') {
					if (!vtlib_isModuleActive ('Rss')) {
						continue;
					}
				} elseif ($stufftype == 'ReportCharts') {
					if (vtlib_isModuleActive ('Reports') === false) {
						continue;
					} else {
						require_once ('modules/Reports/CustomReportUtils.php');
						$query        = "SELECT * FROM vtiger_homereportchart WHERE stuffid=?";
						$result       = $adb->pquery ($query, array ($stuffid));
						$reportId     = $adb->query_result ($result, 0, 'reportid');
						$reportQuery  = CustomReportUtils::getCustomReportsQuery ($reportId);
						$reportResult = $adb->query ($reportQuery);
						$num_rows     = $adb->num_rows ($reportResult);
						if ($num_rows <= 0) {
							continue;
						}
					}
				}

				$nontrans_stufftitle = $adb->query_result ($resultstuff, $i, 'stufftitle');
				$trans_stufftitle    = getTranslatedString ($nontrans_stufftitle);
				$stufftitle          = decode_html ($trans_stufftitle);
				if (strlen ($stufftitle) > 100) {
					$stuff_title = substr ($stufftitle, 0, 97) . "...";
				} else {
					$stuff_title = $stufftitle;
				}

				if ($stufftype == 'Default' && $nontrans_stufftitle != 'Home Page Dashboard' && $nontrans_stufftitle != 'Tag Cloud') {
					if ($modulename != 'NULL') {
						if (isPermitted ($modulename, 'index') == "yes") {
							$homeval[] = Array ('Stuffid' => $stuffid, 'Stufftype' => $stufftype, 'Stufftitle' => $stuff_title);
						}
					} else {
						$homeval[] = Array ('Stuffid' => $stuffid, 'Stufftype' => $stufftype, 'Stufftitle' => $stuff_title);
					}
				} else if ($stufftype == 'Tag Cloud') {
					$homeval[] = Array ('Stuffid' => $stuffid, 'Stufftype' => $stufftype, 'Stufftitle' => $stuff_title);
				} else if ($modulename != 'NULL') {
					if (isPermitted ($modulename, 'index') == "yes") {
						$homeval[] = Array ('Stuffid' => $stuffid, 'Stufftype' => $stufftype, 'Stufftitle' => $stuff_title);
					}
				} else {
					$homeval[] = Array ('Stuffid' => $stuffid, 'Stufftype' => $stufftype, 'Stufftitle' => $stuff_title);
				}
			}
			$homeframe = $homeval;
			return $homeframe;
		}

		/**
		 * this function returns information about the given widget in an array format
		 * @return array(stuffid=>"id", stufftype=>"type", stufftitle=>"title")
		 */
		function getSelectedStuff ($sid, $stuffType) {
			global $adb;
			global $current_user;
			$querystuff  = "SELECT stufftitle FROM vtiger_homestuff WHERE visible=0 AND stuffid=?";
			$resultstuff = $adb->pquery ($querystuff, array ($sid));
			$homeval     = Array ('Stuffid' => $sid, 'Stufftype' => $stuffType, 'Stufftitle' => $adb->query_result ($resultstuff, 0, 'stufftitle'));
			return $homeval;
		}

		/**
		 * this function only returns the widget contents for a given widget
		 */
		function getHomePageStuff ($sid, $stuffType) {
			global $adb;
			global $current_user;
			$header = Array ();
			if ($stuffType == "Module") {
				$details = $this->getModuleFilters ($sid);
			} else if ($stuffType == "RSS") {
				$details = $this->getRssDetails ($sid);
			} else if ($stuffType == "DashBoard" && vtlib_isModuleActive ("Dashboard")) {
				$details = $this->getDashDetails ($sid);
			} else if ($stuffType == "Default") {
				$details = $this->getDefaultDetails ($sid, '');
			} else if ($stuffType == "ReportCharts" && vtlib_isModuleActive ("Reports")) {
				$details = $this->getReportChartDetails ($sid);
			}
			return $details;
		}

		/**
		 * this function returns the widget information for an module type widget
		 */
		private function getModuleFilters ($sid) {
			global $adb, $current_user;
			$querycvid        = "SELECT vtiger_homemoduleflds.fieldname,vtiger_homemodule.* FROM vtiger_homemoduleflds
					LEFT JOIN vtiger_homemodule ON vtiger_homemodule.stuffid=vtiger_homemoduleflds.stuffid
					WHERE vtiger_homemoduleflds.stuffid=?";
			$resultcvid       = $adb->pquery ($querycvid, array ($sid));
			$modname          = $adb->query_result ($resultcvid, 0, "modulename");
			$cvid             = $adb->query_result ($resultcvid, 0, "customviewid");
			$maxval           = $adb->query_result ($resultcvid, 0, "maxentries");
			$column_count     = $adb->num_rows ($resultcvid);
			$cvid_check_query = $adb->pquery ("SELECT * FROM vtiger_customview WHERE cvid = ?", array ($cvid));
			if (isPermitted ($modname, 'index') == "yes") {
				if ($adb->num_rows ($cvid_check_query) > 0) {
					$focus = CRMEntity::getInstance ($modname);

					$oCustomView = new CustomView($modname);
					if ($modname == "Calendar") {
						$listquery = getListQuery ($modname);
						if (trim ($listquery) == '') {
							$listquery = $focus->getListQuery ($modname);
						}
						$query = $oCustomView->getModifiedCvListQuery ($cvid, $listquery, $modname);
					} else {
						$queryGenerator = new QueryGenerator($modname, $current_user);
						$queryGenerator->initForCustomViewById ($cvid);
						$customViewFields = $queryGenerator->getCustomViewFields ();
						$fields           = $queryGenerator->getFields ();
						$newFields        = array_diff ($fields, $customViewFields);
						for ($l = 0; $l < $column_count; $l++) {
							$customViewColumnInfo = $adb->query_result ($resultcvid, $l, "fieldname");
							$details              = explode (':', $customViewColumnInfo);
							$newFields[]          = $details[2];
						}
						$queryGenerator->setFields ($newFields);
						$query = $queryGenerator->getQuery ();
					}
					$count_result     = $adb->query (mkCountQuery ($query));
					$noofrows         = $adb->query_result ($count_result, 0, "count");
					$navigation_array = getNavigationValues (1, $noofrows, $maxval);

					//To get the current language file
					global $current_language, $app_strings;
					$fieldmod_strings = return_module_language ($current_language, $modname);

					if ($modname == "Calendar") {
						$query .= "AND vtiger_activity.activitytype NOT IN ('Emails')";
					}

					if ($adb->dbType == "pgsql") {
						$list_result = $adb->query ($query . " OFFSET 0 LIMIT " . $maxval);
					} else {
						$list_result = $adb->query ($query . " LIMIT 0," . $maxval);
					}

					if ($modname == "Calendar") {
						for ($l = 0; $l < $column_count; $l++) {
							$fieldinfo = $adb->query_result ($resultcvid, $l, "fieldname");
							list($tabname, $colname, $fldname, $fieldmodlabel) = explode (":", $fieldinfo);

							$fieldheader = explode ("_", $fieldmodlabel, 2);
							$fldlabel    = $fieldheader[1];
							$pos         = strpos ($fldlabel, "_");
							if ($pos == true) {
								$fldlabel = str_replace ("_", " ", $fldlabel);
							}
							$field_label = isset($app_strings[ $fldlabel ]) ? $app_strings[ $fldlabel ] : (isset($fieldmod_strings[ $fldlabel ]) ? $fieldmod_strings[ $fldlabel ] : $fldlabel);
							$cv_presence = $adb->pquery ("SELECT * FROM vtiger_cvcolumnlist WHERE cvid = ? AND columnname LIKE '%" . $fldname . "%'", array ($cvid));
							if ($is_admin == false) {
								$fld_permission = getFieldVisibilityPermission ($modname, $current_user->id, $fldname);
							}
							if ($fld_permission == 0 && $adb->num_rows ($cv_presence)) {
								$field_query = $adb->pquery ("SELECT fieldlabel FROM vtiger_field WHERE fieldname = ? AND tablename = ? AND vtiger_field.presence IN (0,2)", array ($fldname, $tabname));
								$field_label = $adb->query_result ($field_query, 0, 'fieldlabel');
								$header[]    = $field_label;
							}
							$fieldcolumns[ $fldlabel ] = Array ($tabname => $colname);
						}
						$listview_entries = getListViewEntries ($focus, $modname, $list_result, $navigation_array, "", "", "EditView", "Delete", $oCustomView, 'HomePage', $fieldcolumns);
					} else {
						$controller = new ListViewController($adb, $current_user, $queryGenerator);
						$controller->setHeaderSorting (false);
						$header           = $controller->getListViewHeader ($focus, $modname, '', '', '', true);
						$listview_entries = $controller->getListViewEntries ($focus, $modname, $list_result, $navigation_array, true);
					}
					$return_value = Array ('ModuleName' => $modname, 'cvid' => $cvid, 'Maxentries' => $maxval, 'Header' => $header, 'Entries' => $listview_entries);
					if (sizeof ($header) != 0) {
						return $return_value;
					} else {
						return array ('Entries' => "Fields not found in Selected Filter");
					}
				} else {
					return array ('Entries' => "<font color='red'>Filter You have Selected is Not Found</font>");
				}
			} else {
				return array ('Entries' => "<font color='red'>Permission Denied</font>");
			}
		}

		/**
		 * this function gets the detailed information about a rss widget
		 */
		private function getRssDetails ($rid) {
			global $mod_strings;
			if (isPermitted ('Rss', 'index') == "yes") {
				require_once ('modules/Rss/Rss.php');
				global $adb;
				$qry    = "SELECT * FROM vtiger_homerss WHERE stuffid=?";
				$res    = $adb->pquery ($qry, array ($rid));
				$url    = $adb->query_result ($res, 0, "url");
				$maxval = $adb->query_result ($res, 0, "maxentries");
				$oRss   = new vtigerRSS();
				if ($oRss->setRSSUrl ($url)) {
					$rss_html = $oRss->getListViewHomeRSSHtml ($maxval);
				} else {
					$rss_html = "<strong>" . $mod_strings['LBL_ERROR_MSG'] . "</strong>";
				}
				$return_value = Array ('Maxentries' => $maxval, 'Entries' => $rss_html);
			} else {
				return array ('Entries' => "<font color='red'>Not Accessible</font>");
			}
			return $return_value;
		}

		/**
		 * this function gets the detailed information of the dashboard widget
		 */
		function getDashDetails ($did, $chart = '') {
			global $adb;
			$qry                       = "SELECT * FROM vtiger_homedashbd WHERE stuffid=?";
			$result                    = $adb->pquery ($qry, array ($did));
			$type                      = $adb->query_result ($result, 0, "dashbdname");
			$charttype                 = $adb->query_result ($result, 0, "dashbdtype");
			$dash                      = Array ('DashType' => $type, 'Chart' => $charttype);
			$this->dashdetails[ $did ] = $dash;
			$from_page                 = 'HomePage';
			if ($chart == '') {
				return $this->getdisplayChart ($type, $charttype, $from_page);
			} else {
				return $dash;
			}
		}

		/**
		 * this function returns detailed information of the homepage big dashboard
		 */
		private function getdisplayChart ($type, $Chart_Type, $from_page) {
			return null;
		}

		function getReportChartDetails ($stuffId, $skipChart = '') {
			global $adb;
			$qry                             = "SELECT * FROM vtiger_homereportchart WHERE stuffid=?";
			$result                          = $adb->pquery ($qry, array ($stuffId));
			$reportId                        = $adb->query_result ($result, 0, "reportid");
			$chartType                       = $adb->query_result ($result, 0, "reportcharttype");
			$reportDetails                   = Array ('ReportId' => $reportId, 'Chart' => $chartType);
			$this->reportdetails[ $stuffId ] = $reportDetails;
			if ($skipChart == '') {
				return $this->getDisplayReportChart ($reportId, $chartType);
			} else {
				return $reportDetails;
			}
		}

		function getDisplayReportChart ($reportId, $chartType) {
			require_once ('modules/Reports/CustomReportUtils.php');
			return CustomReportUtils::getReportChart ($reportId, $chartType);
		}

		/**
		 *
		 */
		private function getDefaultDetails ($dfid, $calCnt) {
			global $adb;
			$qry      = "SELECT * FROM vtiger_homedefault WHERE stuffid=?";
			$result   = $adb->pquery ($qry, array ($dfid));
			$maxval   = $adb->query_result ($result, 0, "maxentries");
			$hometype = $adb->query_result ($result, 0, "hometype");

			if ($hometype == "CVLVT") {
				include_once ("modules/CustomView/ListViewTop.php");
				$home_values = getKeyMetrics ($maxval, $calCnt);
			} elseif ($hometype == 'UA' && vtlib_isModuleActive ("Calendar")) {
				require_once "modules/Home/HomeUtils.php";
				$home_values = homepage_getUpcomingActivities ($maxval, $calCnt);
			} elseif ($hometype == 'PA' && vtlib_isModuleActive ("Calendar")) {
				require_once "modules/Home/HomeUtils.php";
				$home_values = homepage_getPendingActivities ($maxval, $calCnt);
			}

			if ($calCnt == 'calculateCnt') {
				return $home_values;
			}
			$return_value = Array ();
			if (count ($home_values) > 0) {
				$return_value = Array ('Maxentries' => $maxval, 'Details' => $home_values);
			}
			return $return_value;
		}

		/**
		 * this function returns the notebook contents from the database
		 *
		 * @param integer $notebookid - the notebookid
		 *
		 * @return - contents of the notebook for a user
		 */
		function getNotebookContents ($notebookid) {
			global $adb, $current_user;

			$sql    = "SELECT * FROM vtiger_notebook_contents WHERE notebookid=? AND userid=?";
			$result = $adb->pquery ($sql, array ($notebookid, $current_user->id));

			$contents = "";
			if ($adb->num_rows ($result) > 0) {
				$contents = vtlib_purify ($adb->query_result ($result, 0, "contents"));
			}
			return $contents;
		}

		/**
		 * this function returns the URL for a given widget id from the database
		 *
		 * @param integer $widgetid - the notebookid
		 *
		 * @return $url - the url for the widget
		 */
		function getWidgetURL ($widgetid) {
			global $adb, $current_user;

			$sql    = "SELECT * FROM vtiger_homewidget_url WHERE widgetid=?";
			$result = $adb->pquery ($sql, array ($widgetid));

			$url = "";
			if ($adb->num_rows ($result) > 0) {
				$url = $adb->query_result ($result, 0, "url");
			}
			return $url;
		}
	}

	/**
	 * this function returns the tasks allocated to different groups
	 */
	function getGroupTaskLists ($maxval, $calCnt) {
		//get all the group relation tasks
		global $current_user;
		global $adb;
		global $log;
		global $app_strings;
		$userid   = $current_user->id;
		$groupids = explode (",", fetchUserGroupids ($userid));

		//Check for permission before constructing the query.
		if (count ($groupids) > 0 && isPermitted ('Calendar', 'index') == "yes") {
			$query  = '';
			$params = array ();
			if (vtlib_isModuleActive ("Calendar") && isPermitted ('Calendar', 'index') == "yes") {
				if ($query != '') {
					$query .= " union all ";
				}
				//Get the activities assigned to group
				$query .= "SELECT vtiger_activity.activityid AS id,vtiger_activity.subject AS name,vtiger_groups.groupname AS groupname,'Activities' AS Type FROM vtiger_activity INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid INNER JOIN vtiger_groups ON vtiger_crmentity.smownerid=vtiger_groups.groupid WHERE  vtiger_crmentity.deleted=0 AND ((vtiger_activity.eventstatus !='held'AND (vtiger_activity.status IS NULL OR vtiger_activity.status ='')) OR (vtiger_activity.status !='completed' AND (vtiger_activity.eventstatus IS NULL OR vtiger_activity.eventstatus=''))) AND vtiger_activity.activityid > 0";
				if (count ($groupids) > 0) {
					$query .= " and vtiger_groups.groupid in (" . generateQuestionMarks ($groupids) . ")";
					array_push ($params, $groupids);
				}
				$query .= " LIMIT $maxval";
			}

			if (vtlib_isModuleActive ("Documents") && isPermitted ('Documents', 'index') == 'yes') {
				if ($query != '') {
					$query .= " union all ";
				}
				//Get the Purchase Order assigned to group
				$query .= "SELECT vtiger_notes.notesid AS id,vtiger_notes.title AS name,vtiger_groups.groupname AS groupname, 'Documents' AS Type FROM vtiger_notes INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_notes.notesid INNER JOIN  vtiger_groups ON vtiger_crmentity.smownerid =vtiger_groups.groupid WHERE vtiger_crmentity.deleted=0 AND vtiger_notes.notesid > 0";
				if (count ($groupids) > 0) {
					$query .= " and vtiger_groups.groupid in (" . generateQuestionMarks ($groupids) . ")";
					array_push ($params, $groupids);
				}
				$query .= " LIMIT $maxval";
			}

			$log->info ("Here is the where clause for the list view: $query");
			$result = $adb->pquery ($query, $params) or die("Couldn't get the group listing");

			$title    = array ();
			$title[]  = 'myGroupAllocation.gif';
			$title[]  = $app_strings['LBL_GROUP_ALLOCATION_TITLE'];
			$title[]  = 'home_mygrp';
			$header   = array ();
			$header[] = $app_strings['LBL_ENTITY_NAME'];
			$header[] = $app_strings['LBL_GROUP_NAME'];
			$header[] = $app_strings['LBL_ENTITY_TYPE'];

			if (count ($groupids) > 0) {
				$i = 1;
				while ($row = $adb->fetch_array ($result)) {
					$value       = array ();
					$row["type"] = trim ($row["type"]);
					if ($row["type"] == "Tickets") {
						$list = '<a href=index.php?module=HelpDesk';
						$list .= '&action=DetailView&record=' . $row["id"] . '>' . $row["name"] . '</a>';
					} elseif ($row["type"] == "Activities") {
						$row["type"] = 'Calendar';
						$acti_type   = getActivityType ($row["id"]);
						$list        = '<a href=index.php?module=' . $row["type"];
						if ($acti_type == 'Task') {
							$list .= '&activity_mode=Task';
						} elseif ($acti_type == 'Call' || $acti_type == 'Meeting') {
							$list .= '&activity_mode=Events';
						}
						$list .= '&action=DetailView&record=' . $row["id"] . '>' . $row["name"] . '</a>';
					} else {
						$list = '<a href=index.php?module=' . $row["type"];
						$list .= '&action=DetailView&record=' . $row["id"] . '>' . $row["name"] . '</a>';
					}

					$value[]               = $list;
					$value[]               = $row["groupname"];
					$value[]               = $row["type"];
					$entries[ $row["id"] ] = $value;
					$i++;
				}
			}

			$values = Array ('Title' => $title, 'Header' => $header, 'Entries' => $entries);
			if (count ($entries) > 0) {
				return $values;
			}
		}
	}

?>

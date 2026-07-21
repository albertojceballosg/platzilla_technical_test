<?php
	global $theme;
	$theme_path = 'themes/' . $theme . '/';
	$image_path = $theme_path . 'images/';
	require_once ('modules/CustomView/CustomView.php');
	require_once ('config.php');
	require_once ('modules/Reports/Reports.php');
	require_once ('include/logging.php');
	require_once ('modules/Reports/ReportRun.php');
	require_once ('include/utils/utils.php');
	require_once ('Smarty_setup.php');

	global $adb;
	global $mod_strings;
	global $app_strings;

	$reportid   = vtlib_purify ($_REQUEST['record']);
	$folderid   = vtlib_purify ($_REQUEST['folderid']);
	$now_action = vtlib_purify ($_REQUEST['action']);

	$sql       = 'SELECT * FROM vtiger_report WHERE reportid=?';
	$res       = $adb->pquery ($sql, array ($reportid));
	$Report_ID = $adb->query_result ($res, 0, 'reportid');
	if (empty($folderid)) {
		$folderid = $adb->query_result ($res, 0, 'folderid');
	}
	$reporttype = $adb->query_result ($res, 0, 'reporttype');
	$showCharts = false;
	if ($reporttype == 'summary') {
		$showCharts = true;
	}
	//END Customization
	$numOfRows = $adb->num_rows ($res);

	if ($numOfRows > 0) {
		global $primarymodule;
		global $secondarymodule;
		global $orderbylistsql;
		global $orderbylistcolumns;
		global $ogReport;
		// added to fix the ticket #5117
		global $current_user;
		global $currentModule;
		global $default_charset;
		$reports_array = array ();
		$local_user    = clone $current_user;
		$module_field  = '';
		require ('user_privileges/user_privileges.php');

		$ogReport          = new Reports($reportid);
		$primarymodule     = $ogReport->primodule;
		$restrictedmodules = array ();
		if ($ogReport->secmodule != '') {
			$rep_modules = explode (':', $ogReport->secmodule);
		} else {
			$rep_modules = array ();
		}

		array_push ($rep_modules, $primarymodule);
		$modules_permitted        = true;
		$modules_export_permitted = true;
		foreach ($rep_modules as $mod) {
			if (isPermitted ($mod, 'index') != 'yes' || vtlib_isModuleActive ($mod) == false) {
				$modules_permitted   = false;
				$restrictedmodules[] = $mod;
			}
			if (isPermitted ("$mod", 'Export', '') != 'yes') {
				$modules_export_permitted = false;
			}
		}

		if (isPermitted ($primarymodule, 'index') == 'yes' && $modules_permitted == true) {
			$oReportRun   = new ReportRun($reportid);
			$fieldDetails = '';

			require_once ('include/Zend/Json.php');
			/** @noinspection PhpUndefinedClassInspection */
			$json = new Zend_Json();

			$advft_criteria = $_REQUEST['advft_criteria'];
			if (!empty($advft_criteria)) {
				$advft_criteria = $json->decode ($advft_criteria);
			}
			$advft_criteria_groups = $_REQUEST['advft_criteria_groups'];
			if (!empty($advft_criteria_groups)) {
				$advft_criteria_groups = $json->decode ($advft_criteria_groups);
			}

			if ($_REQUEST['submode'] == 'saveCriteria') {
				updateAdvancedCriteria ($reportid, $advft_criteria, $advft_criteria_groups);
			}

			$filtersql        = $oReportRun->runTimeAdvFilter ($advft_criteria, $advft_criteria_groups);
			$list_report_form = new vtigerCRM_Smarty;
			//Monolithic phase 6 changes
			$list_report_form->assign ('SHOWCHARTS', false);
			//Monolithic Changes Ends

			// Performance Optimization: Direct output of the report result
			if ($_REQUEST['submode'] == 'generateReport' && empty($advft_criteria)) {
				$filtersql = '';
			}
			$sshtml    = array ();
			$totalhtml = '';
			$list_report_form->assign ('DIRECT_OUTPUT', true);
			$list_report_form->assign_by_ref ('__REPORT_RUN_INSTANCE', $oReportRun);
			$list_report_form->assign_by_ref ('__REPORT_RUN_FILTER_SQL', $filtersql);
			//Ends

			$ogReport->getPriModuleColumnsList ($ogReport->primodule);
			$ogReport->getSecModuleColumnsList ($ogReport->secmodule);
			$ogReport->getAdvancedFilterList ($reportid);

			$COLUMNS_BLOCK = getPrimaryColumnsAdvFilterHtml ($ogReport->primodule, $ogReport);
			$COLUMNS_BLOCK .= getSecondaryColumnsAdvFilterHtml ($ogReport->secmodule, $ogReport);
			$list_report_form->assign ('COLUMNS_BLOCK', $COLUMNS_BLOCK);

			$FILTER_OPTION = Reports::getAdvCriteriaHtml ();
			$list_report_form->assign ('FOPTION', $FILTER_OPTION);

			$rel_fields = $ogReport->adv_rel_fields;
			/** @noinspection PhpUndefinedClassInspection */
			$list_report_form->assign ('REL_FIELDS', Zend_Json::encode ($rel_fields));

			$list_report_form->assign ('CRITERIA_GROUPS', $ogReport->advft_criteria);

			$list_report_form->assign ('MOD', $mod_strings);
			$list_report_form->assign ('APP', $app_strings);
			$list_report_form->assign ('IMAGE_PATH', $image_path);
			$list_report_form->assign ('REPORTID', $reportid);
			$list_report_form->assign ('IS_EDITABLE', $ogReport->is_editable);

			$list_report_form->assign ('REP_FOLDERS', $ogReport->sgetRptFldr ());

			$list_report_form->assign ('REPORTNAME', htmlspecialchars ($ogReport->reportname, ENT_QUOTES, $default_charset));
			if (is_array ($sshtml)) {
				$list_report_form->assign ('REPORTHTML', $sshtml);
			} else {
				$list_report_form->assign ('ERROR_MSG', getTranslatedString ('LBL_REPORT_GENERATION_FAILED', $currentModule) . '<br>' . $sshtml);
			}
			$list_report_form->assign ('REPORTTOTHTML', $totalhtml);
			$list_report_form->assign ('FOLDERID', $folderid);
			$list_report_form->assign ('DATEFORMAT', $current_user->date_format);
			$list_report_form->assign ('JS_DATEFORMAT', parse_calendardate ($app_strings['NTC_DATE_FORMAT']));
			if ($modules_export_permitted == true) {
				$list_report_form->assign ('EXPORT_PERMITTED', 'YES');
			} else {
				$list_report_form->assign ('EXPORT_PERMITTED', 'NO');
			}
			$rep_in_fldr    = $ogReport->sgetRptsforFldr ($folderid);
			$countRepInFldr = count ($rep_in_fldr);
			for ($i = 0; $i < $countRepInFldr; $i++) {
				$rep_id                   = $rep_in_fldr[ $i ]['reportid'];
				$rep_name                 = $rep_in_fldr[ $i ]['reportname'];
				$reports_array[ $rep_id ] = $rep_name;
			}
			$list_report_form->assign ('REPINFOLDER', $reports_array);
			if ($_REQUEST['mode'] != 'ajax') {
				require ('modules/Vtiger/header.php');

				$customlink_params = array ('MODULE' => $currentModule, 'ACTION' => vtlib_purify ($_REQUEST['action']));
				$reportLinks       = Vtiger_Link::getAllByType (getTabid ($currentModule), array ('REPORT_LINK'), $customlink_params, null, $reportid);

				$list_report_form->assign ('REPORT_LINKS', $reportLinks['REPORT_LINK']);
				$list_report_form->assign ('MODULE', $currentModule);
				$filterFields = array ();
				foreach ($rel_fields as $relField) {
					foreach ($relField as $myField) {
						$dummy                       = explode ('::', $myField);
						$filterFields [ $dummy [0] ] = $dummy [1];
					}
				}
				$list_report_form->assign ('AVAILABLE_FILTER_FIELDS', $filterFields);
				$list_report_form->display ('ReportRun.tpl');
			} else {
				$list_report_form->display ('ReportRunContents.tpl');
			}
		} else {
			if ($_REQUEST['mode'] != 'ajax') {
				require ('modules/Vtiger/header.php');
			}
			// @codingStandardsIgnoreStart
			echo "<table border='0' cellpadding='5' cellspacing='0' width='100%'><tr><td align='center'>";
			echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 80%; position: relative; z-index: 10000000;'>";
			echo "<table border='0' cellpadding='5' cellspacing='0' width='98%'>";
			echo '<tbody><tr>';
			/** @noinspection HtmlUnknownTarget */
			echo "<td rowspan='2' width='11%'><img src='themes/images/denied.gif'></td>";
			echo "<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>" . $mod_strings['LBL_NO_ACCESS'] . ' : ' . implode (',', $restrictedmodules) . ' </span></td>';
			echo '</tr>';
			echo '<tr>';
			echo "<td class='small' align='right' nowrap='nowrap'>";
			echo "<a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br>								   		     </td>";
			echo '</tr>';
			echo '</tbody></table>';
			echo '</div>';
			echo '</td></tr></table>';
			// @codingStandardsIgnoreEnd
		}
	} else {
		// @codingStandardsIgnoreStart
		echo "<link rel='stylesheet' type='text/css' href='themes/" . $theme . "/style.css'>";
		echo "<table border='0' cellpadding='5' cellspacing='0' width='100%'><tr><td align='center'>";
		echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 80%; position: relative; z-index: 10000000;'>";

		echo "<table border='0' cellpadding='5' cellspacing='0' width='98%'>";
		echo '<tbody><tr>';
		/** @noinspection HtmlUnknownTarget */
		echo "<td rowspan='2' width='11%'><img src='themes/images/denied.gif'></td>";
		echo "<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>$mod_strings[LBL_REPORT_DELETED]</span></td>";
		echo '</tr>';
		echo '<tr>';
		echo "<td class='small' align='right' nowrap='nowrap'>";
		echo "<a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br>								   		     </td>";
		echo '</tr>';
		echo '</tbody></table>';
		echo '</div>';
		echo '</td></tr></table>';
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Function to get the StdfilterHTML strings for the given  primary module
	 *  @ param $module : Type String
	 *  @ param $selected : Type String(optional)
	 *  This Generates the HTML Combo strings for the standard filter for the given reports module
	 *  This Returns a HTML sring
	 *
	 * @param $module
	 * @param string $selected
	 *
	 * @return string
	 */
	function getPrimaryStdFilterHtml ($module, $selected = '') {
		global $ogReport;
		global $current_language;
		$shtml                 = '';
		$ogReport->oCustomView = new CustomView();
		$result                = $ogReport->oCustomView->getStdCriteriaByModule ($module);
		$mod_strings           = return_module_language ($current_language, $module);
		if (isset($result)) {
			foreach ($result as $key => $value) {
				if (isset($mod_strings[ $value ])) {
					if ($key == $selected) {
						$shtml .= '<option selected value="' . $key . '">' . getTranslatedString ($module, $module) . ' - ' . $mod_strings[ $value ] . '</option>';
					} else {
						$shtml .= '<option value="' . $key . '">' . getTranslatedString ($module, $module) . ' - ' . $mod_strings[ $value ] . '</option>';
					}
				} else {
					if ($key == $selected) {
						$shtml .= '<option selected value="' . $key . '">' . getTranslatedString ($module, $module) . ' - ' . $value . '</option>';
					} else {
						$shtml .= '<option value="' . $key . '">' . getTranslatedString ($module, $module) . ' - ' . $value . '</option>';
					}
				}
			}
		}

		return $shtml;
	}

	/**
	 * Function to get the StdfilterHTML strings for the given secondary module
	 *  @ param $module : Type String
	 *  @ param $selected : Type String(optional)
	 *  This Generates the HTML Combo strings for the standard filter for the given reports module
	 *  This Returns a HTML sring
	 *
	 * @param $module
	 * @param string $selected
	 *
	 * @return string
	 */
	function getSecondaryStdFilterHtml ($module, $selected = '') {
		global $ogReport;
		global $current_language;
		$shtml                 = '';
		$ogReport->oCustomView = new CustomView();

		if ($module != '') {
			$secmodule      = explode (':', $module);
			$countSecModule = count ($secmodule);
			for ($i = 0; $i < $countSecModule; $i++) {
				$result      = $ogReport->oCustomView->getStdCriteriaByModule ($secmodule[ $i ]);
				$mod_strings = return_module_language ($current_language, $secmodule[ $i ]);
				if (isset($result)) {
					$shtml .= auxGetSecondaryStdFilterHtml ($result, $mod_strings, $selected, $shtml, $secmodule, $i);
				}
			}
		}
		return $shtml;
	}

	function auxGetSecondaryStdFilterHtml ($auxResult, $auxModStrings, $auxSelected, $auxShtml, $auxSecmodule, $auxI) {
		foreach ($auxResult as $key => $value) {
			if (isset($auxModStrings[ $value ])) {
				if ($key == $auxSelected) {
					$auxShtml .= '<option selected value="' . $key . '">' . getTranslatedString ($auxSecmodule[ $auxI ], $auxSecmodule[ $auxI ]) . ' - ' . $auxModStrings[ $value ] . '</option>';
				} else {
					$auxShtml .= '<option value="' . $key . '">' . getTranslatedString ($auxSecmodule[ $auxI ], $auxSecmodule[ $auxI ]) . ' - ' . $auxModStrings[ $value ] . '</option>';
				}
			} else {
				if ($key == $auxSelected) {
					$auxShtml .= '<option selected value="' . $key . '">' . getTranslatedString ($auxSecmodule[ $auxI ], $auxSecmodule[ $auxI ]) . ' - ' . $value . '</option>';
				} else {
					$auxShtml .= '<option value="' . $key . '">' . getTranslatedString ($auxSecmodule[ $auxI ], $auxSecmodule[ $auxI ]) . ' - ' . $value . '</option>';
				}
			}
		}
		return $auxShtml;
	}

	function getPrimaryColumnsAdvFilterHtml ($module, $ogReport, $selected = '') {
		global $app_list_strings;
		global $current_language;
		$shtml        = '';
		$mod_strings  = return_module_language ($current_language, $module);
		$block_listed = array ();
		foreach ($ogReport->module_list[ $module ] as $value) {
			if (isset($ogReport->pri_module_columnslist[ $module ][ $value ]) && !$block_listed[ $value ]) {
				$block_listed[ $value ] = true;
				$shtml .= '<optgroup label="' . $app_list_strings['moduleList'][ $module ] . ' ' . getTranslatedString ($value) . '" class="select" style="border:none">';
				foreach ($ogReport->pri_module_columnslist[ $module ][ $value ] as $field => $fieldlabel) {
					if (isset($mod_strings[ $fieldlabel ])) {
						//fix for ticket 5191
						$selected = decode_html ($selected);
						$field    = decode_html ($field);
						//fix ends
						if ($selected == $field) {
							$shtml .= '<option selected value="' . $field . '">' . $mod_strings[ $fieldlabel ] . '</option>';
						} else {
							$shtml .= '<option value="' . $field . '">' . $mod_strings[ $fieldlabel ] . '</option>';
						}
					} else {
						if ($selected == $field) {
							$shtml .= '<option selected value="' . $field . '">' . $fieldlabel . '</option>';
						} else {
							$shtml .= '<option value="' . $field . '">' . $fieldlabel . '</option>';
						}
					}
				}
			}
		}
		return $shtml;
	}

	function getSecondaryColumnsAdvFilterHtml ($module, $ogReport, $selected = '') {
		$shtml = '';
		global $current_language;

		if ($module != '') {
			$secmodule      = explode (':', $module);
			$countSecModule = count ($secmodule);
			for ($i = 0; $i < $countSecModule; $i++) {
				$mod_strings = return_module_language ($current_language, $secmodule[ $i ]);
				if (vtlib_isModuleActive ($secmodule[ $i ])) {
					foreach ($ogReport->module_list[ $secmodule[ $i ] ] as $value) {
						$shtml .= auxGetSecondaryColumnsAdvFilterHtml ($ogReport, $secmodule, $mod_strings, $i, $value, $selected);
					}
				}
			}
		}
		return $shtml;
	}

	function auxGetSecondaryColumnsAdvFilterHtml ($auxOgReport, $auxSecmodule, $auxMod_strings, $auxI, $auxValue, $auxSelected) {
		global $app_list_strings;
		$block_listed = array ();
		$auxShtml = '';
		if (isset($auxOgReport->sec_module_columnslist[ $auxSecmodule[ $auxI ] ][ $auxValue ]) && !$block_listed[ $auxValue ]) {
			$block_listed[ $auxValue ] = true;
			$auxShtml .= '<optgroup label="' . $app_list_strings['moduleList'][ $auxSecmodule[ $auxI ] ] . ' ' . getTranslatedString ($auxValue) . '" class="select" style="border:none">';
			foreach ($auxOgReport->sec_module_columnslist[ $auxSecmodule[ $auxI ] ][ $auxValue ] as $field => $fieldlabel) {
				if (isset($auxMod_strings[ $fieldlabel ])) {
					if ($auxSelected == $field) {
						$auxShtml .= '<option selected value="' . $field . '">' . $auxMod_strings[ $fieldlabel ] . '</option>';
					} else {
						$auxShtml .= '<option value="' . $field . '">' . $auxMod_strings[ $fieldlabel ] . '</option>';
					}
				} else {
					if ($auxSelected == $field) {
						$auxShtml .= '<option selected value="' . $field . '">' . $fieldlabel . '</option>';
					} else {
						$auxShtml .= '<option value="' . $field . '">' . $fieldlabel . '</option>';
					}
				}
			}
		}
		return $auxShtml;
	}

	function getAdvCriteriaHtml ($adv_filter_options, $selected = '') {
		$shtml = '';

		foreach ($adv_filter_options as $key => $value) {
			if ($selected == $key) {
				$shtml .= '<option selected value="' . $key . '">' . $value . '</option>';
			} else {
				$shtml .= '<option value="' . $key . '">' . $value . '</option>';
			}
		}

		return $shtml;
	}

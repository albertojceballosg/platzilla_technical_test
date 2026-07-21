<?php
require_once('include/database/PearDatabase.php');
require_once('data/CRMEntity.php');
require_once('include/utils/UserInfoUtil.php');
require_once 'modules/Reports/ReportUtils.php';
global $calpath;
global $app_strings,$mod_strings;
global $app_list_strings;
global $modules;
global $blocks;
global $adv_filter_options;
global $log;

global $report_modules;
global $related_modules;
global $old_related_modules;

$adv_filter_options = array(
	'e' => 'equals',
	'n' => 'not equal to',
	's' => 'starts with',
	'ew' => 'ends with',
	'c' => 'contains',
	'k' => 'does not contain',
	'l' => 'less than',
	'g' => 'greater than',
	'm' => 'less or equal',
	'h' => 'greater or equal',
	'bw' => 'between',
	'a' => 'after',
	'b' => 'before',
);

$old_related_modules = array(
	'Calendar' => array('Leads','Accounts','Contacts','Potentials'),
);

$related_modules =array();

// @codingStandardsIgnoreStart
/**
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Reports extends CRMEntity {
	// @codingStandardsIgnoreEnd

	/**
	 * @var string
	 */
	public $srptfldridjs;

	public $column_fields = array();

	public $sort_fields = array();
	public $sort_values = array();

	public $id;
	public $mode;
	public $mcount;

	public $startdate;
	public $enddate;

	public $ascdescorder;

	public $stdselectedfilter;
	public $stdselectedcolumn;

	public $primodule;
	public $secmodule;
	public $columnssummary;
	public $is_editable;
	public $reporttype;
	public $reportname;
	public $reportdescription;
	public $folderid;
	public $module_blocks;

	public $pri_module_columnslist;
	public $sec_module_columnslist;

	public $advft_criteria;
	public $adv_rel_fields = array();

	public $module_list = array();
	public $related_modules;
	public $module_id;
	public $secondarymodule;
	public $primarymodule;

	/**
	 * Function to set primodule,secmodule,reporttype,reportname,reportdescription,folderid for given vtiger_reportid
	 * This function accepts the vtiger_reportid as argument
	 * It sets primodule,secmodule,reporttype,reportname,reportdescription,folderid for the given vtiger_reportid

	 * @param string $reportid
	 */
	public function __construct ($reportid = '') {
		global $adb;
		global $is_admin;
		global $current_user;
		$current_user_parent_role_seq = '';
		$user_group_query = '';
		$this->initListOfModules();
		if ($reportid != '') {
			// Lookup information in cache first
			$cachedInfo = VTCacheUtils::lookupReport_Info($current_user->id, $reportid);
			$subordinate_users = VTCacheUtils::lookupReport_SubordinateUsers($reportid);

			if ($cachedInfo === false) {
				$ssql = 'SELECT vtiger_reportmodules.*,vtiger_report.* FROM vtiger_report INNER JOIN vtiger_reportmodules ON vtiger_report.reportid = vtiger_reportmodules.reportmodulesid';
				$ssql .= ' where vtiger_report.reportid = ?';
				$params = array($reportid);

				require_once('include/utils/GetUserGroups.php');
				$userGroups = new GetUserGroups();
				$userGroups->getAllUserGroups($current_user->id);
				$user_groups = $userGroups->user_groups;
				if (!empty($user_groups) && $is_admin == false) {
					$user_group_query = ' (shareid IN (' . generateQuestionMarks($user_groups) . ") AND setype='groups') OR";
					array_push($params, $user_groups);
				}

				$non_admin_query = " vtiger_report.reportid IN (SELECT reportid from vtiger_reportsharing WHERE $user_group_query (shareid=? AND setype='users'))";
				if ($is_admin == false) {
					$ssql .= ' and ( (' . $non_admin_query . ") or vtiger_report.sharingtype='Public' or vtiger_report.owner = ? or vtiger_report.owner in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
					array_push($params, $current_user->id);
					array_push($params, $current_user->id);
				}

				$query = $adb->pquery("SELECT userid FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'", array());
				$subordinate_users = array();
				$numRows = $adb->num_rows($query);
				for ($i = 0; $i < $numRows; $i++) {
					$subordinate_users[] = $adb->query_result($query, $i, 'userid');
				}

				// Update subordinate user information for re-use
				VTCacheUtils::updateReport_SubordinateUsers($reportid, $subordinate_users);

				$result = $adb->pquery($ssql, $params);
				if ($result && $adb->num_rows($result)) {
					$reportmodulesrow = $adb->fetch_array($result);

					// Update information in cache now
					VTCacheUtils::updateReport_Info(
						$current_user->id,
						$reportid,
						$reportmodulesrow['primarymodule'],
						$reportmodulesrow['secondarymodules'],
						$reportmodulesrow['reporttype'],
						$reportmodulesrow['reportname'],
						$reportmodulesrow['description'],
						$reportmodulesrow['folderid'],
						$reportmodulesrow['owner']
					);
				}

				// Re-look at cache to maintain code-consistency below
				$cachedInfo = VTCacheUtils::lookupReport_Info($current_user->id, $reportid);
			}

			if ($cachedInfo) {
				$this->primodule = $cachedInfo['primarymodule'];
				$this->secmodule = $cachedInfo['secondarymodules'];
				$this->reporttype = $cachedInfo['reporttype'];
				$this->reportname = decode_html($cachedInfo['reportname']);
				$this->reportdescription = decode_html($cachedInfo['description']);
				$this->folderid = $cachedInfo['folderid'];
				if ($is_admin == true || in_array($cachedInfo['owner'], $subordinate_users) || $cachedInfo['owner'] == $current_user->id) {
					$this->is_editable = 'true';
				} else {
					$this->is_editable = 'false';
				}
			} else {
				self::auxConstruct();
			}
		}
	}

	public function auxConstruct () {
		global $app_strings;
		if ($_REQUEST['mode'] != 'ajax') {
			require('modules/Vtiger/header.php');
		}
		echo "<table border='0' cellpadding='5' cellspacing='0' width='100%'><tr><td align='center'>";
		echo "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 80%; position: relative; z-index: 10000000;'>";
		echo "<table border='0' cellpadding='5' cellspacing='0' width='98%'>";
		echo '<tbody><tr>';
		/** @noinspection HtmlUnknownTarget */
		echo "<td rowspan='2' width='11%'><img src='themes/images/denied.gif'></td>";
		echo "<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>You are not allowed to View this Report </span></td>";
		echo '</tr>';
		echo '<tr>';
		echo "<td class='small' align='right' nowrap='nowrap'>";
		echo "<a href='javascript:window.history.back();'>$app_strings[LBL_GO_BACK]</a><br>								   		     </td>";
		echo '</tr>';
		echo '</tbody></table>';
		echo '</div>';
		echo '</td></tr></table>';
	}

	/**
	 * Update the module list for listing columns for report creation.

	 * @param $module
	 */
	public function updateModuleList ($module) {
		global $adb;
		$blockid_list = array();
		if (!isset($module)) {
			return;
		}
		require_once('include/utils/utils.php');
		$tabid = getTabid($module);
		if ($module == 'Calendar') {
			$tabid = array(9, 16);
		}
		$sql = 'SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid IN (' . generateQuestionMarks($tabid) . ')';
		$res = $adb->pquery($sql, array($tabid));
		$noOfRows = $adb->num_rows($res);
		if ($noOfRows <= 0) {
			return;
		}
		for ($index = 0; $index < $noOfRows; ++$index) {
			$blockid = $adb->query_result($res, $index, 'blockid');
			if (in_array($blockid, $this->module_list[$module])) {
				continue;
			}
			$blockid_list[] = $blockid;
			$blocklabel = $adb->query_result($res, $index, 'blocklabel');
			$this->module_list[$module][$blocklabel] = $blockid;
		}
	}

	/**
	 * Initializes the module list for listing columns for report creation.
	 */
	public function initListOfModules () {
		global $adb;
		$restricted_modules = array('Emails', 'Events', 'Webmails');
		$restricted_blocks = array('LBL_IMAGE_INFORMATION', 'LBL_COMMENTS', 'LBL_COMMENT_INFORMATION');

		$this->module_id = array();
		$this->module_list = array();

		// Prefetch module info to check active or not and also get list of tabs
		$modulerows = vtlib_prefetchModuleActiveInfo(false);

		$cachedInfo = VTCacheUtils::lookupReport_ListofModuleInfos();

		if ($cachedInfo !== false) {
			$this->module_list = $cachedInfo['module_list'];
			$this->related_modules = $cachedInfo['related_modules'];
		} else {
			if ($modulerows) {
				foreach ($modulerows as $resultrow) {
					if ($resultrow['presence'] == '1') {
						continue;
						// skip disabled modules
					}
					if ($resultrow['isentitytype'] != '1') {
						continue;
						// skip extension modules
					}
					if (in_array($resultrow['name'], $restricted_modules)) { // skip restricted modules
						continue;
					}
					if ($resultrow['name'] != 'Calendar') {
						$this->module_id[$resultrow['tabid']] = $resultrow['name'];
					} else {
						$this->module_id[9] = $resultrow['name'];
						$this->module_id[16] = $resultrow['name'];
					}
					$this->module_list[$resultrow['name']] = array();
				}

				$moduleids = array_keys($this->module_id);
				$reportblocks = $adb->pquery(
					'SELECT blockid, blocklabel, tabid FROM vtiger_blocks WHERE tabid IN (' . generateQuestionMarks($moduleids) . ')',
					array($moduleids)
				);
				$this->module_list = self::auxOneInitListOfModules($restricted_blocks, $reportblocks);


				$relatedmodules = $adb->pquery(
					'SELECT vtiger_tab.name, vtiger_relatedlists.tabid FROM vtiger_tab
                    INNER JOIN vtiger_relatedlists ON vtiger_tab.tabid=vtiger_relatedlists.related_tabid
                    INNER JOIN vtiger_module_report ON vtiger_relatedlists.related_tabid = vtiger_module_report.tabid
                    WHERE vtiger_tab.isentitytype=1
                    AND vtiger_tab.name NOT IN(' . generateQuestionMarks($restricted_modules) . ")
					AND vtiger_tab.presence = 0 AND vtiger_relatedlists.label!='Activity History'
                    AND vtiger_module_report.reportavailable = 1
					UNION
                    SELECT module, vtiger_tab.tabid FROM vtiger_tab
                    INNER JOIN vtiger_module_report ON vtiger_tab.tabid = vtiger_module_report.tabid
                    INNER JOIN vtiger_fieldmodulerel ON vtiger_tab.name = vtiger_fieldmodulerel.module
                    WHERE vtiger_tab.isentitytype = 1
					AND vtiger_tab.name NOT IN(" . generateQuestionMarks($restricted_modules) . ')
                    AND vtiger_tab.presence = 0
                    AND vtiger_module_report.reportavailable = 1',
					array($restricted_modules, $restricted_modules)
				);
				$this->related_modules = self::auxTwoInitListOfModules($relatedmodules);

				// Put the information in cache for re-use
				VTCacheUtils::updateReport_ListofModuleInfos($this->module_list, $this->related_modules);
			}
		}
	}

	public function auxOneInitListOfModules ($restricted_blocks, $reportblocks) {
		global $adb;
		$prev_block_label = '';
		if ($adb->num_rows($reportblocks)) {
			while ($resultrow = $adb->fetch_array($reportblocks)) {
				$blockid = $resultrow['blockid'];
				$blocklabel = $resultrow['blocklabel'];
				$module = $this->module_id[$resultrow['tabid']];

				if (in_array($blocklabel, $restricted_blocks) || in_array($blockid, $this->module_list[$module]) || isset($this->module_list[$module][getTranslatedString($blocklabel, $module)])) {
					continue;
				}

				if (!empty($blocklabel)) {
					if ($module == 'Calendar' && $blocklabel == 'LBL_CUSTOM_INFORMATION') {
						$this->module_list[$module][$blockid] = getTranslatedString($blocklabel, $module);
					} else {
						$this->module_list[$module][$blockid] = getTranslatedString($blocklabel, $module);
					}
					$prev_block_label = $blocklabel;
				} else {
					$this->module_list[$module][$blockid] = getTranslatedString($prev_block_label, $module);
				}
			}
		}
		return $this->module_list;
	}

	public function auxTwoInitListOfModules ($relatedmodules) {
		global $old_related_modules;
		global $adb;
		if ($adb->num_rows($relatedmodules)) {
			while ($resultrow = $adb->fetch_array($relatedmodules)) {
				$module = $this->module_id[$resultrow['tabid']];

				if (!isset($this->related_modules[$module])) {
					$this->related_modules[$module] = array();
				}

				if ($module != $resultrow['name']) {
					$this->related_modules[$module][] = $resultrow['name'];
					// [Corrección Platzilla 2025-07-09] Evitar duplicados inmediatos en la carga de módulos relacionados
					$this->related_modules[$module] = array_unique($this->related_modules[$module]);
				}

				// To achieve Backward Compatability with Report relations
				if (isset($old_related_modules[$module])) {
					$rel_mod = array();
					foreach ($old_related_modules[$module] as $name) {
						if (vtlib_isModuleActive($name) && isPermitted($name, 'index', '')) {
							$rel_mod[] = $name;
						}
					}
					if (!empty($rel_mod)) {
						$this->related_modules[$module] = array_merge($this->related_modules[$module], $rel_mod);
						$this->related_modules[$module] = array_unique($this->related_modules[$module]);
					}
				}
			}
		}
		return $this->related_modules;
	}

	/**
	 * Function to get the Listview of Reports
	 * This function accepts no argument
	 * This generate the Reports view page and returns a string
	 * contains HTML

	 * @param string $mode

	 * @return array
	 */
	public function sgetRptFldr ($mode = '') {
		global $adb, $log, $mod_strings;
		$returndata = array();
		$sql = "SELECT * FROM vtiger_reportfolder WHERE state = 'SAVED' ORDER BY folderid";
		$result = $adb->pquery($sql, array());
		$reportfldrow = $adb->fetch_array($result);
		if ($mode != '') {
			// Fetch detials of all reports of folder at once
			$reportsInAllFolders = $this->sgetRptsforFldr(false);

			do {
				if ($reportfldrow['state'] == $mode) {
					$details = array();
					$details['state'] = $reportfldrow['state'];
					$details['id'] = $reportfldrow['folderid'];
					$details['name'] = ($mod_strings[$reportfldrow['foldername']] == '') ? $reportfldrow['foldername'] : $mod_strings[$reportfldrow['foldername']];
					$details['description'] = $reportfldrow['description'];
					$details['fname'] = popup_decode_html($details['name']);
					$details['fdescription'] = popup_decode_html($reportfldrow['description']);
					$details['details'] = $reportsInAllFolders[$reportfldrow['folderid']];
					$returndata[] = $details;
				}
			} while ($reportfldrow = $adb->fetch_array($result));
		} else {
			do {
				$details = array();
				$details['state'] = $reportfldrow['state'];
				$details['id'] = $reportfldrow['folderid'];
				$details['name'] = ($mod_strings[$reportfldrow['foldername']] == '') ? $reportfldrow['foldername'] : $mod_strings[$reportfldrow['foldername']];
				$details['description'] = $reportfldrow['description'];
				$details['fname'] = popup_decode_html($details['name']);
				$details['fdescription'] = popup_decode_html($reportfldrow['description']);
				$returndata[] = $details;
			} while ($reportfldrow = $adb->fetch_array($result));
		}

		$log->info('Reports :: ListView->Successfully returned vtiger_report folder HTML');
		return $returndata;
	}

	/**
	 * Filtrar reportes para menu comercial

	 * @param string $mode

	 * @return array
	 */
	public function sgetRptFldrComercial ($mode = '') {
		global $adb, $log, $mod_strings;
		$returndata = array();
		$sql = 'SELECT * FROM vtiger_reportfolder ORDER BY folderid';
		$result = $adb->pquery($sql, array());
		$reportfldrow = $adb->fetch_array($result);
		if ($mode != '') {
			// Fetch detials of all reports of folder at once
			$reportsInAllFolders = $this->sgetRptsforFldr(false);

			do {
				// Desglose menu comercial de reportes.
				if ($reportfldrow['state'] == $mode && ($reportfldrow['foldername'] == 'Account and Contact Reports' || $reportfldrow['foldername'] == 'Lead Reports' || $reportfldrow['foldername'] == 'Potential Reports' || $reportfldrow['foldername'] == 'Campaign Reports')) {
					$details = array();
					$details['state'] = $reportfldrow['state'];
					$details['id'] = $reportfldrow['folderid'];
					$details['name'] = ($mod_strings[$reportfldrow['foldername']] == '') ? $reportfldrow['foldername'] : $mod_strings[$reportfldrow['foldername']];
					$details['description'] = $reportfldrow['description'];
					$details['fname'] = popup_decode_html($details['name']);
					$details['fdescription'] = popup_decode_html($reportfldrow['description']);
					$details['details'] = $reportsInAllFolders[$reportfldrow['folderid']];
					$returndata[] = $details;
				}
			} while ($reportfldrow = $adb->fetch_array($result));
		} else {
			do {
				if ($reportfldrow['foldername'] == 'Account and Contact Reports' || $reportfldrow['foldername'] == 'Lead Reports' || $reportfldrow['foldername'] == 'Potential Reports' || $reportfldrow['foldername'] == 'Campaign Reports') {
					$details = array();
					$details['state'] = $reportfldrow['state'];
					$details['id'] = $reportfldrow['folderid'];
					$details['name'] = ($mod_strings[$reportfldrow['foldername']] == '') ? $reportfldrow['foldername'] : $mod_strings[$reportfldrow['foldername']];
					$details['description'] = $reportfldrow['description'];
					$details['fname'] = popup_decode_html($details['name']);
					$details['fdescription'] = popup_decode_html($reportfldrow['description']);
					$returndata[] = $details;
				}
			} while ($reportfldrow = $adb->fetch_array($result));
		}

		$log->info('Reports :: ListView->Successfully returned vtiger_report folder HTML');
		return $returndata;
	}

	/**
	 * Filtar reportes para menu postventa

	 * @param string $mode

	 * @return array
	 */
	public function sgetRptFldrPostventa ($mode = '') {
		global $adb, $log, $mod_strings;
		$returndata = array();
		$sql = 'SELECT * FROM vtiger_reportfolder ORDER BY folderid';
		$result = $adb->pquery($sql, array());
		$reportfldrow = $adb->fetch_array($result);
		if ($mode != '') {
			// Fetch detials of all reports of folder at once
			$reportsInAllFolders = $this->sgetRptsforFldr(false);

			do {
				// Desglose menu comercial de reportes.
				if ($reportfldrow['state'] == $mode && ($reportfldrow['foldername'] == 'Product Reports' || $reportfldrow['foldername'] == 'Quote Reports' || $reportfldrow['foldername'] == 'PurchaseOrder Reports' || $reportfldrow['foldername'] == 'SalesOrder Reports')) {
					$details = array();
					$details['state'] = $reportfldrow['state'];
					$details['id'] = $reportfldrow['folderid'];
					$details['name'] = ($mod_strings[$reportfldrow['foldername']] == '') ? $reportfldrow['foldername'] : $mod_strings[$reportfldrow['foldername']];
					$details['description'] = $reportfldrow['description'];
					$details['fname'] = popup_decode_html($details['name']);
					$details['fdescription'] = popup_decode_html($reportfldrow['description']);
					$details['details'] = $reportsInAllFolders[$reportfldrow['folderid']];
					$returndata[] = $details;
				}
			} while ($reportfldrow = $adb->fetch_array($result));
		} else {
			do {
				if ($reportfldrow['foldername'] == 'Account and Contact Reports' || $reportfldrow['foldername'] == 'Lead Reports' || $reportfldrow['foldername'] == 'Potential Reports' || $reportfldrow['foldername'] == 'Campaign Reports') {
					$details = array();
					$details['state'] = $reportfldrow['state'];
					$details['id'] = $reportfldrow['folderid'];
					$details['name'] = ($mod_strings[$reportfldrow['foldername']] == '') ? $reportfldrow['foldername'] : $mod_strings[$reportfldrow['foldername']];
					$details['description'] = $reportfldrow['description'];
					$details['fname'] = popup_decode_html($details['name']);
					$details['fdescription'] = popup_decode_html($reportfldrow['description']);
					$returndata[] = $details;
				}
			} while ($reportfldrow = $adb->fetch_array($result));
		}

		$log->info('Reports :: ListView->Successfully returned vtiger_report folder HTML');
		return $returndata;
	}

	/**
	 * Function to get the Reports inside each modules
	 * This function accepts the folderid
	 * This Generates the Reports under each Reports module
	 * This Returns a HTML sring

	 * @param $rpt_fldr_id

	 * @return array|mixed
	 */
	public function sgetRptsforFldr ($rpt_fldr_id) {
		global $adb;
		global $log;
		global $current_user;
		global $is_admin;
		$user_group_query = '';
		$current_user_parent_role_seq = '';

		require_once('include/utils/UserInfoUtil.php');

		$sql = 'SELECT vtiger_report.*, vtiger_reportmodules.*, vtiger_reportfolder.folderid FROM vtiger_report INNER JOIN vtiger_reportfolder ON vtiger_reportfolder.folderid = vtiger_report.folderid';
		$sql .= ' inner join vtiger_reportmodules on vtiger_reportmodules.reportmodulesid = vtiger_report.reportid';

		$params = array();

		// If information is required only for specific report folder?
		if ($rpt_fldr_id !== false) {
			$sql .= ' where vtiger_reportfolder.folderid=?';
			$params[] = $rpt_fldr_id;
		}
		require_once('include/utils/GetUserGroups.php');
		$userGroups = new GetUserGroups();
		$userGroups->getAllUserGroups($current_user->id);
		$user_groups = $userGroups->user_groups;
		if (!empty($user_groups) && $is_admin == false) {
			$user_group_query = ' (shareid IN (' . generateQuestionMarks($user_groups) . ") AND setype='groups') OR";
			array_push($params, $user_groups);
		}

		$non_admin_query = " vtiger_report.reportid IN (SELECT reportid from vtiger_reportsharing WHERE $user_group_query (shareid=? AND setype='users'))";
		if ($is_admin == false) {
			$sql .= ' and ( (' . $non_admin_query . ") or vtiger_report.sharingtype='Public' or vtiger_report.owner = ? or vtiger_report.owner in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
			array_push($params, $current_user->id);
			array_push($params, $current_user->id);
		}
		$query = $adb->pquery("SELECT userid FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id=vtiger_user2role.userid INNER JOIN vtiger_role ON vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole LIKE '" . $current_user_parent_role_seq . "::%'", array());
		$subordinate_users = array();
		$numRows = $adb->num_rows($query);
		for ($i = 0; $i < $numRows; $i++) {
			$subordinate_users[] = $adb->query_result($query, $i, 'userid');
		}
		$result = $adb->pquery($sql, $params);

		$report = $adb->fetch_array($result);
		$returndata = self::auxsGetRptsforFldr($report, $subordinate_users, $result, $rpt_fldr_id);

		$log->info('Reports :: ListView->Successfully returned vtiger_report details HTML');
		return $returndata;
	}

	public function auxsGetRptsforFldr ($auxReport, $auxSubordinateUsers, $auxResult, $auxRptFldrId) {
		global $is_admin;
		global $adb;
		global $current_user;
		$returndata = array();
		if (count($auxReport) > 0) {
			do {
				$report_details = array();
				$report_details ['customizable'] = $auxReport['customizable'];
				$report_details ['reportid'] = $auxReport['reportid'];
				$report_details ['primarymodule'] = $auxReport['primarymodule'];
				$report_details ['secondarymodules'] = $auxReport['secondarymodules'];
				$report_details ['state'] = $auxReport['state'];
				$report_details ['description'] = $auxReport['description'];
				$report_details ['reportname'] = $auxReport['reportname'];
				$report_details ['sharingtype'] = $auxReport['sharingtype'];
				if ($is_admin == true || in_array($auxReport['owner'], $auxSubordinateUsers) || $auxReport['owner'] == $current_user->id) {
					$report_details ['editable'] = 'true';
				} else {
					$report_details['editable'] = 'false';
				}

				if (isPermitted($auxReport['primarymodule'], 'index') == 'yes') {
					$returndata [$auxReport['folderid']][] = $report_details;
				}
			} while ($auxReport = $adb->fetch_array($auxResult));
		}

		if ($auxRptFldrId !== false) {
			$returndata = $returndata[$auxRptFldrId];
		}
		return $returndata;
	}

	/**
	 * Function to get the array of ids
	 * This function forms the array for the ExpandCollapse
	 * Javascript
	 * It returns the array of ids
	 * array('1RptFldr','2RptFldr',........,'9RptFldr','10RptFldr')
	 */
	public function sgetJsRptFldr () {
		$srptfldr_js = 'var ReportListarray=new array(' . $this->srptfldridjs . ')
            setExpandCollapse()';
		return $srptfldr_js;
	}

	/**
	 * Function to set the Primary module vtiger_fields for the given Report
	 * This function sets the primary module columns for the given Report
	 * It accepts the Primary module as the argument and set the vtiger_fields of the module
	 * to the varialbe pri_module_columnslist and returns true if sucess

	 * @param $module

	 * @return boolean
	 */
	public function getPriModuleColumnsList ($module) {
		$ret_module_list = array();
		foreach ($this->module_list[$module] as $key => $value) {
			$temp = $this->getColumnsListbyBlock($module, $key);
			if (!empty($ret_module_list[$module][$value])) {
				if (!empty($temp)) {
					$ret_module_list[$module][$value] = array_merge($ret_module_list[$module][$value], $temp);
				}
			} else {
				$ret_module_list[$module][$value] = $this->getColumnsListbyBlock($module, $key);
			}
		}
		$this->pri_module_columnslist = $ret_module_list;
		return true;
	}

	/**
	 * Function to set the Secondary module fileds for the given Report
	 * This function sets the secondary module columns for the given module
	 * It accepts the module as the argument and set the vtiger_fields of the module
	 * to the varialbe sec_module_columnslist and returns true if sucess

	 * @param $module

	 * @return boolean
	 */
	public function getSecModuleColumnsList ($module) {
		if ($module != '') {
			$secmodule = explode(':', $module);
			$countSecModule = count($secmodule);
			for ($i = 0; $i < $countSecModule; $i++) {
				if ($this->module_list[$secmodule[$i]]) {
					$this->sec_module_columnslist[$secmodule[$i]] = $this->getModuleFieldList(
						$secmodule[$i]
					);
					if ($this->module_list[$secmodule[$i]] == 'Calendar') {
						if ($this->module_list['Events']) {
							$this->sec_module_columnslist['Events'] = $this->getModuleFieldList('Events');
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 *
	 * @param string $module
	 * @param object $blockIdList
	 * @param array $currentFieldList
	 * @return array
	 */
	public function getBlockFieldList ($module, $blockIdList, $currentFieldList) {
		if (!empty($currentFieldList)) {
			$temp = $this->getColumnsListbyBlock($module, $blockIdList);
			if (!empty($temp)) {
				$currentFieldList = array_merge($currentFieldList, $temp);
			}
		} else {
			$currentFieldList = $this->getColumnsListbyBlock($module, $blockIdList);
		}
		return $currentFieldList;
	}

	public function getModuleFieldList ($module) {
		$ret_module_list = array();
		foreach ($this->module_list[$module] as $key => $value) {
			$ret_module_list[$module][$value] = $this->getBlockFieldList($module, $key, $ret_module_list[$module][$value]);
		}
		return $ret_module_list[$module];
	}

	/**
	 * Function to get vtiger_fields for the given module and block
	 * This function gets the vtiger_fields for the given module
	 * It accepts the module and the block as arguments and
	 * returns the array column lists
	 * array module_columnlist[ vtiger_fieldtablename:fieldcolname:module_fieldlabel1:fieldname:fieldtypeofdata]=fieldlabel

	 * @param $module
	 * @param $block

	 * @return array
	 */
	public function getColumnsListbyBlock($module, $block) {
		global $adb;
		global $log;
		$module_columnlist = array();


		// Security Check
		$functionResults = self::auxOneGetColumnsListbyBlock($module, $block);
		$sql = $functionResults[0];
		$params = $functionResults[1];


		$result = $adb->pquery($sql, $params);
		$noofrows = $adb->num_rows($result);
		for($i=0; $i<$noofrows; $i++) {
			$fieldtablename = $adb->query_result($result,$i,'tablename');
			$fieldcolname = $adb->query_result($result,$i,'columnname');
			$fieldname = $adb->query_result($result,$i,'fieldname');
			$fieldtype = $adb->query_result($result,$i,'typeofdata');
			$uitype = $adb->query_result($result,$i,'uitype');
			$fieldtype = explode('~',$fieldtype);
			$fieldtypeofdata = $fieldtype[0];

			//Here we Changing the displaytype of the field. So that its criteria will be displayed correctly in Reports Advance Filter.
			$fieldtypeofdata=ChangeTypeOfData_Filter($fieldtablename,$fieldcolname,$fieldtypeofdata);

			if($uitype == 68 || $uitype == 59) {
				$fieldtypeofdata = 'V';
			}
			if($fieldtablename == 'vtiger_crmentity') {
				$fieldtablename = $fieldtablename.$module;
			}
			if($fieldname == 'assigned_user_id') {
				$fieldtablename = 'vtiger_users'.$module;
				$fieldcolname = 'user_name';
			}
			if($fieldname == 'assigned_user_id1') {
				$fieldtablename = 'vtiger_usersRel1';
				$fieldcolname = 'user_name';
			}

			$fieldlabel = $adb->query_result($result,$i,'fieldlabel');
			$fieldlabelOne = str_replace(' ','_',$fieldlabel);
			$optionvalue = $fieldtablename.':'.$fieldcolname.':'.$module.'_'.$fieldlabelOne.':'.$fieldname.':'.$fieldtypeofdata;
			$this->adv_rel_fields[$fieldtypeofdata][] = '$'.$module.'#'.$fieldname.'$::'.getTranslatedString($module,$module).' '.getTranslatedString($fieldlabel,$module);
			// added to escape attachments fields in Reports as we have multiple attachments
			if($module != 'HelpDesk' || $fieldname !='filename') {
				$module_columnlist[$optionvalue] = $fieldlabel;
			}
		}
		$module_columnlist = self::auxTwoGetColumnsListbyBlock($module, $block, $module_columnlist);
		$log->info('Reports :: FieldColumns->Successfully returned ColumnslistbyBlock'.$module.$block);
		return $module_columnlist;
	}

	public function auxOneGetColumnsListbyBlock($module, $block) {
		global $is_admin;
		if(is_string($block)) {
			$block = explode(',', $block);
		}

		$tabid = getTabid($module);
		if ($module == 'Calendar') {
			$tabid = array('9','16');
		}
		$params = array($tabid, $block);
		if($is_admin == true) {
			$sql = 'select * from vtiger_field where vtiger_field.tabid in ('. generateQuestionMarks($tabid) .') and vtiger_field.block in ('. generateQuestionMarks($block) .') and vtiger_field.displaytype in (1,2,3) and vtiger_field.presence in (0,2) ';

			// fix for Ticket #4016
			if($module == 'Calendar') {
				$sql.=' group by vtiger_field.fieldlabel order by sequence';
			} else {
				$sql.=' order by sequence';
			}
		} else {
			$profileList = getCurrentUserProfileList();
			$sql = 'select * from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid in ('. generateQuestionMarks($tabid) .')  and vtiger_field.block in ('. generateQuestionMarks($block) .') and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)';
			if (count($profileList) > 0) {
				$sql .= ' and vtiger_profile2field.profileid in ('. generateQuestionMarks($profileList) .')';
				array_push($params, $profileList);
			}

			//fix for Ticket #4016
			if($module == 'Calendar') {
				$sql.=' group by vtiger_field.fieldlabel order by sequence';
			} else {
				$sql.=' group by vtiger_field.fieldid order by sequence';
			}
		}
		$functionResult = array($sql, $params);
		return $functionResult;
	}

	public function auxTwoGetColumnsListbyBlock($module, $block, $module_columnlist) {
		return $module_columnlist;
	}

	/**
	 * Function to set the standard filter vtiger_fields for the given vtiger_report
	 * This function gets the standard filter vtiger_fields for the given vtiger_report
	 * and set the values to the corresponding variables
	 * It accepts the repordid as argument

	 * @param $reportid
	 */
	public function getSelectedStandardCriteria ($reportid) {
		global $adb;
		$sSQL = 'SELECT vtiger_reportdatefilter.* FROM vtiger_reportdatefilter INNER JOIN vtiger_report ON vtiger_report.reportid = vtiger_reportdatefilter.datefilterid WHERE vtiger_report.reportid=?';
		$result = $adb->pquery($sSQL, array($reportid));
		$selectedstdfilter = $adb->fetch_array($result);

		$this->stdselectedcolumn = $selectedstdfilter['datecolumnname'];
		$this->stdselectedfilter = $selectedstdfilter['datefilter'];

		if ($selectedstdfilter['datefilter'] == 'custom') {
			if ($selectedstdfilter['startdate'] != '0000-00-00') {
				/** @noinspection PhpParamsInspection */
				$startDateTime = new DateTimeField($selectedstdfilter['startdate'] . ' ' . date('H:i:s'));
				$this->startdate = $startDateTime->getDisplayDate();
			}
			if ($selectedstdfilter['enddate'] != '0000-00-00') {
				/** @noinspection PhpParamsInspection */
				$endDateTime = new DateTimeField($selectedstdfilter['enddate'] . ' ' . date('H:i:s'));
				$this->enddate = $endDateTime->getDisplayDate();
			}
		}
	}

	/**
	 * Function to get the combo values for the standard filter
	 * This function get the combo values for the standard filter for the given vtiger_report
	 * and return a HTML string

	 * @param string $selecteddatefilter

	 * @return string
	 */
	public function getSelectedStdFilterCriteria ($selecteddatefilter = '') {
		global $mod_strings;
		$sshtml = '';

		$datefiltervalue = array(
			'custom',
			'prevfy',
			'thisfy',
			'nextfy',
			'prevfq',
			'thisfq',
			'nextfq',
			'yesterday',
			'today',
			'tomorrow',
			'lastweek',
			'thisweek',
			'nextweek',
			'lastmonth',
			'thismonth',
			'nextmonth',
			'last7days',
			'last30days',
			'last60days',
			'last90days',
			'last120days',
			'next30days',
			'next60days',
			'next90days',
			'next120days',
		);

		$datefilterdisplay = array(
			'Custom',
			'Previous FY',
			'Current FY',
			'Next FY',
			'Previous FQ',
			'Current FQ',
			'Next FQ',
			'Yesterday',
			'Today',
			'Tomorrow',
			'Last Week',
			'Current Week',
			'Next Week',
			'Last Month',
			'Current Month',
			'Next Month',
			'Last 7 Days',
			'Last 30 Days',
			'Last 60 Days',
			'Last 90 Days',
			'Last 120 Days',
			'Next 7 Days',
			'Next 30 Days',
			'Next 60 Days',
			'Next 90 Days',
			'Next 120 Days',
		);

		$countDateFilterValue = count($datefiltervalue);
		for ($i = 0; $i < $countDateFilterValue; $i++) {
			if ($selecteddatefilter == $datefiltervalue[$i]) {
				$sshtml .= "<option selected value='" . $datefiltervalue[$i] . "'>" . $mod_strings[$datefilterdisplay[$i]] . '</option>';
			} else {
				$sshtml .= "<option value='" . $datefiltervalue[$i] . "'>" . $mod_strings[$datefilterdisplay[$i]] . '</option>';
			}
		}

		return $sshtml;
	}

	/**
	 * Function to get the selected standard filter columns
	 * This function returns the selected standard filter criteria
	 * which is selected for vtiger_reports as an array
	 * array stdcriteria_list[fieldtablename:fieldcolname:module_fieldlabel1]=fieldlabel

	 * @param $module

	 * @return array
	 */
	public function getStdCriteriaByModule($module) {
		global $adb;
		global $log;
		global $is_admin;
		$blockids = array();
		$stdcriteria_list = array();

		$tabid = getTabid($module);
		foreach ($this->module_list[$module] as $blockid) {
			$blockids[] = $blockid;
		}
		$blockids = implode(',', $blockids);

		$params = array($tabid, $blockids);
		if ($is_admin == true) {
			// uitype 6 and 23 added for start_date,EndDate,Expected Close Date
			$sql = 'SELECT * FROM vtiger_field WHERE vtiger_field.tabid=? AND (vtiger_field.uitype =5 OR vtiger_field.uitype = 6 OR vtiger_field.uitype = 23 OR vtiger_field.displaytype=2) AND vtiger_field.block IN (' . generateQuestionMarks($blockids) . ') AND vtiger_field.presence IN (0,2) ORDER BY vtiger_field.sequence';
		} else {
			$profileList = getCurrentUserProfileList();
			$sql = 'SELECT * FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid  WHERE vtiger_field.tabid=? AND (vtiger_field.uitype =5 OR vtiger_field.displaytype=2) AND vtiger_profile2field.visible=0 AND vtiger_def_org_field.visible=0 AND vtiger_field.block IN (' . generateQuestionMarks($blockids) . ') AND vtiger_field.presence IN (0,2)';
			if (count($profileList) > 0) {
				$sql .= ' and vtiger_profile2field.profileid in (' . generateQuestionMarks($profileList) . ')';
				array_push($params, $profileList);
			}
			$sql .= ' order by vtiger_field.sequence';
		}

		$result = $adb->pquery($sql, $params);

		while ($criteriatyperow = $adb->fetch_array($result)) {
			$fieldtablename = $criteriatyperow['tablename'];
			$fieldcolname = $criteriatyperow['columnname'];
			$fieldlabel = $criteriatyperow['fieldlabel'];

			if ($fieldtablename == 'vtiger_crmentity') {
				$fieldtablename = $fieldtablename . $module;
			}
			$fieldlabelOne = str_replace(' ', '_', $fieldlabel);
			$optionvalue = $fieldtablename . ':' . $fieldcolname . ':' . $module . '_' . $fieldlabelOne;
			$stdcriteria_list[$optionvalue] = $fieldlabel;
		}

		$log->info('Reports :: StdfilterColumns->Successfully returned Stdfilter for' . $module);
		return $stdcriteria_list;
	}

	/**
	 * Function to form a javascript to determine the start date and end date for a standard filter
	 * This function is to form a javascript to determine
	 * the start date and End date from the value selected in the combo lists
	 */
	public function getCriteriaJs() {
		/** @noinspection PhpParamsInspection */
		$nFqStartDateTime = new DateTimeField(date('Y-m-d H:i:s'));
		/** @noinspection PhpParamsInspection */
		$nFqEndDateTime = new DateTimeField(date('Y-m-d H:i:s'));
		/** @noinspection PhpParamsInspection */
		$pFqStartDateTime = new DateTimeField(date('Y-m-d H:i:s'));
		/** @noinspection PhpParamsInspection */
		$pFqEndDateTime = new DateTimeField(date('Y-m-d H:i:s'));
		/** @noinspection PhpParamsInspection */
		$cFqStartDateTime = new DateTimeField(date('Y-m-d H:i:s'));
		/** @noinspection PhpParamsInspection */
		$cFqEndDateTime = new DateTimeField(date('Y-m-d H:i:s'));
		if (date('m') <= 3) {
			$cFq = date('Y-m-d', mktime(0, 0, 0, '01', '01', date('Y')));
			/** @noinspection PhpParamsInspection */
			$cFqStartDateTime = new DateTimeField($cFq . ' ' . date('H:i:s'));
			$cFqOne = date('Y-m-d', mktime(0, 0, 0, '03', '31', date('Y')));
			/** @noinspection PhpParamsInspection */
			$cFqEndDateTime = new DateTimeField($cFqOne . ' ' . date('H:i:s'));
			$nFq = date('Y-m-d', mktime(0, 0, 0, '04', '01', date('Y')));
			/** @noinspection PhpParamsInspection */
			$nFqStartDateTime = new DateTimeField($nFq . ' ' . date('H:i:s'));
			$nFqOne = date('Y-m-d', mktime(0, 0, 0, '06', '30', date('Y')));
			/** @noinspection PhpParamsInspection */
			$nFqEndDateTime = new DateTimeField($nFqOne . ' ' . date('H:i:s'));
			$pFq = date('Y-m-d', mktime(0, 0, 0, '10', '01', (date('Y') - 1)));
			/** @noinspection PhpParamsInspection */
			$pFqStartDateTime = new DateTimeField($pFq . ' ' . date('H:i:s'));
			$pFqOne = date('Y-m-d', mktime(0, 0, 0, '12', '31', (date('Y') - 1)));
			/** @noinspection PhpParamsInspection */
			$pFqEndDateTime = new DateTimeField($pFqOne . ' ' . date('H:i:s'));
		} else if (date('m') > 3 && date('m') <= 6) {
			$pFq = date('Y-m-d', mktime(0, 0, 0, '01', '01', date('Y')));
			/** @noinspection PhpParamsInspection */
			$pFqStartDateTime = new DateTimeField($pFq . ' ' . date('H:i:s'));
			$pFqOne = date('Y-m-d', mktime(0, 0, 0, '03', '31', date('Y')));
			/** @noinspection PhpParamsInspection */
			$pFqEndDateTime = new DateTimeField($pFqOne . ' ' . date('H:i:s'));
			$cFq = date('Y-m-d', mktime(0, 0, 0, '04', '01', date('Y')));
			/** @noinspection PhpParamsInspection */
			$cFqStartDateTime = new DateTimeField($cFq . ' ' . date('H:i:s'));
			$cFqOne = date('Y-m-d', mktime(0, 0, 0, '06', '30', date('Y')));
			/** @noinspection PhpParamsInspection */
			$cFqEndDateTime = new DateTimeField($cFqOne . ' ' . date('H:i:s'));
			$nFq = date('Y-m-d', mktime(0, 0, 0, '07', '01', date('Y')));
			/** @noinspection PhpParamsInspection */
			$nFqStartDateTime = new DateTimeField($nFq . ' ' . date('H:i:s'));
			$nFqOne = date('Y-m-d', mktime(0, 0, 0, '09', '30', date('Y')));
			/** @noinspection PhpParamsInspection */
			$nFqEndDateTime = new DateTimeField($nFqOne . ' ' . date('H:i:s'));
		} else if (date('m') > 6 && date('m') <= 9) {
			$nFq = date('Y-m-d', mktime(0, 0, 0, '10', '01', date('Y')));
			/** @noinspection PhpParamsInspection */
			$nFqStartDateTime = new DateTimeField($nFq . ' ' . date('H:i:s'));
			$nFqOne = date('Y-m-d', mktime(0, 0, 0, '12', '31', date('Y')));
			/** @noinspection PhpParamsInspection */
			$nFqEndDateTime = new DateTimeField($nFqOne . ' ' . date('H:i:s'));
			$pFq = date('Y-m-d', mktime(0, 0, 0, '04', '01', date('Y')));
			/** @noinspection PhpParamsInspection */
			$pFqStartDateTime = new DateTimeField($pFq . ' ' . date('H:i:s'));
			$pFqOne = date('Y-m-d', mktime(0, 0, 0, '06', '30', date('Y')));
			/** @noinspection PhpParamsInspection */
			$pFqEndDateTime = new DateTimeField($pFqOne . ' ' . date('H:i:s'));
			$cFq = date('Y-m-d', mktime(0, 0, 0, '07', '01', date('Y')));
			/** @noinspection PhpParamsInspection */
			$cFqStartDateTime = new DateTimeField($cFq . ' ' . date('H:i:s'));
			$cFqOne = date('Y-m-d', mktime(0, 0, 0, '09', '30', date('Y')));
			/** @noinspection PhpParamsInspection */
			$cFqEndDateTime = new DateTimeField($cFqOne . ' ' . date('H:i:s'));
		} else if (date('m') > 9 && date('m') <= 12) {
			$nFq = date('Y-m-d', mktime(0, 0, 0, '01', '01', (date('Y') + 1)));
			/** @noinspection PhpParamsInspection */
			$nFqStartDateTime = new DateTimeField($nFq . ' ' . date('H:i:s'));
			$nFqOne = date('Y-m-d', mktime(0, 0, 0, '03', '31', (date('Y') + 1)));
			/** @noinspection PhpParamsInspection */
			$nFqEndDateTime = new DateTimeField($nFqOne . ' ' . date('H:i:s'));
			$pFq = date('Y-m-d', mktime(0, 0, 0, '07', '01', date('Y')));
			/** @noinspection PhpParamsInspection */
			$pFqStartDateTime = new DateTimeField($pFq . ' ' . date('H:i:s'));
			$pFqOne = date('Y-m-d', mktime(0, 0, 0, '09', '30', date('Y')));
			/** @noinspection PhpParamsInspection */
			$pFqEndDateTime = new DateTimeField($pFqOne . ' ' . date('H:i:s'));
			$cFq = date('Y-m-d', mktime(0, 0, 0, '10', '01', date('Y')));
			/** @noinspection PhpParamsInspection */
			$cFqStartDateTime = new DateTimeField($cFq . ' ' . date('H:i:s'));
			$cFqOne = date('Y-m-d', mktime(0, 0, 0, '12', '31', date('Y')));
			/** @noinspection PhpParamsInspection */
			$cFqEndDateTime = new DateTimeField($cFqOne . ' ' . date('H:i:s'));
		}
		$sjsStr = self::auxGetCriteriaJs($nFqStartDateTime, $nFqEndDateTime, $pFqStartDateTime, $pFqEndDateTime, $cFqStartDateTime, $cFqEndDateTime);
		return $sjsStr;
	}

	/**
	 * Auxiliary function to getCriteriaJs

	 * @param $nFqStartDateTime
	 * @param $nFqEndDateTime
	 * @param $pFqStartDateTime
	 * @param $pFqEndDateTime
	 * @param $cFqStartDateTime
	 * @param $cFqEndDateTime

	 * @return string

	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function auxGetCriteriaJs($nFqStartDateTime, $nFqEndDateTime, $pFqStartDateTime, $pFqEndDateTime, $cFqStartDateTime, $cFqEndDateTime) {
		$nextFyOne = date('Y-m-t', mktime(0, 0, 0, '12', date('d'), (date('Y') + 1)));
		/** @noinspection PhpParamsInspection */
		$nextFYEndDateTime = new DateTimeField($nextFyOne . ' ' . date('H:i:s'));
		$nextFyZero = date('Y-m-d', mktime(0, 0, 0, '01', '01', (date('Y') + 1)));
		/** @noinspection PhpParamsInspection */
		$nextFYStartDateTime = new DateTimeField($nextFyZero . ' ' . date('H:i:s'));
		$lastFyOne = date('Y-m-t', mktime(0, 0, 0, '12', date('d'), (date('Y') - 1)));
		/** @noinspection PhpParamsInspection */
		$lastFYEndDateTime = new DateTimeField($lastFyOne . ' ' . date('H:i:s'));
		$lastFyZero = date('Y-m-d', mktime(0, 0, 0, '01', '01', (date('Y') - 1)));
		/** @noinspection PhpParamsInspection */
		$lastFYStartDateTime = new DateTimeField($lastFyZero . ' ' . date('H:i:s'));
		$currentFyOne = date('Y-m-t', mktime(0, 0, 0, '12', date('d'), date('Y')));
		/** @noinspection PhpParamsInspection */
		$currentFYEndDateTime = new DateTimeField($currentFyOne . ' ' . date('H:i:s'));
		$currentFyZero = date('Y-m-d', mktime(0, 0, 0, '01', '01', date('Y')));
		/** @noinspection PhpParamsInspection */
		$currentFYStartDateTime = new DateTimeField($currentFyZero . ' ' . date('H:i:s'));
		$lastOneHundredTwentydays = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') - 119), date('Y')));
		/** @noinspection PhpParamsInspection */
		$lastOneHundredTwentyDaysDateTime = new DateTimeField($lastOneHundredTwentydays . ' ' . date('H:i:s'));
		$lastNinetydays = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') - 89), date('Y')));
		/** @noinspection PhpParamsInspection */
		$lastNinetyDaysDateTime = new DateTimeField($lastNinetydays . ' ' . date('H:i:s'));
		$lastSixtydays = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') - 59), date('Y')));
		/** @noinspection PhpParamsInspection */
		$lastSixtyDaysDateTime = new DateTimeField($lastSixtydays . ' ' . date('H:i:s'));
		$lastThirtydays = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') - 29), date('Y')));
		/** @noinspection PhpParamsInspection */
		$lastThirtyDaysDateTime = new DateTimeField($lastThirtydays . ' ' . date('H:i:s'));
		$lastSevendays = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') - 6), date('Y')));
		/** @noinspection PhpParamsInspection */
		$lastSevenDaysDateTime = new DateTimeField($lastSevendays . ' ' . date('H:i:s'));
		$nextOneHundredTwentydays = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') + 119), date('Y')));
		/** @noinspection PhpParamsInspection */
		$nextOneHundredTwentyDaysDateTime = new DateTimeField($nextOneHundredTwentydays . ' ' . date('H:i:s'));
		$nextNinetydays = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') + 89), date('Y')));
		/** @noinspection PhpParamsInspection */
		$nextNinetyDaysDateTime = new DateTimeField($nextNinetydays . ' ' . date('H:i:s'));
		$nextSixtydays = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') + 59), date('Y')));
		/** @noinspection PhpParamsInspection */
		$nextSixtyDaysDateTime = new DateTimeField($nextSixtydays . ' ' . date('H:i:s'));
		$nextThirtydays = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') + 29), date('Y')));
		/** @noinspection PhpParamsInspection */
		$nextThirtyDaysDateTime = new DateTimeField($nextThirtydays . ' ' . date('H:i:s'));
		$nextSevendays = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') + 6), date('Y')));
		/** @noinspection PhpParamsInspection */
		$nextSevenDaysDateTime = new DateTimeField($nextSevendays . ' ' . date('H:i:s'));
		$nextmonthOne = date('Y-m-t', strtotime('+1 Month'));
		/** @noinspection PhpParamsInspection */
		$nextMonthEndDateTime = new DateTimeField($nextmonthOne . ' ' . date('H:i:s'));
		$nextmonthZero = date('Y-m-d', mktime(0, 0, 0, (date('m') + 1), '01', date('Y')));
		/** @noinspection PhpParamsInspection */
		$nextMonthStartDateTime = new DateTimeField($nextmonthZero . ' ' . date('H:i:s'));
		$lastmonthOne = date('Y-m-t', strtotime('-1 Month'));
		/** @noinspection PhpParamsInspection */
		$lastMonthEndDateTime = new DateTimeField($lastmonthOne . ' ' . date('H:i:s'));
		$lastmonthZero = date('Y-m-d', mktime(0, 0, 0, (date('m') - 1), '01', date('Y')));
		/** @noinspection PhpParamsInspection */
		$lastMonthStartDateTime = new DateTimeField($lastmonthZero . ' ' . date('H:i:s'));
		$currentmonthOne = date('Y-m-t');
		/** @noinspection PhpParamsInspection */
		$currentMonthEndDateTime = new DateTimeField($currentmonthOne . ' ' . date('H:i:s'));
		$currentmonthZero = date('Y-m-d', mktime(0, 0, 0, date('m'), '01', date('Y')));
		/** @noinspection PhpParamsInspection */
		$currentMonthStartDateTime = new DateTimeField($currentmonthZero . ' ' . date('H:i:s'));
		/** @noinspection PhpParamsInspection */
		$nextweekOne = date('Y-m-d', strtotime('+1 week Saturday'));
		/** @noinspection PhpParamsInspection */
		$nextWeekEndDateTime = new DateTimeField($nextweekOne . ' ' . date('H:i:s'));
		$nextweekZero = date('Y-m-d', strtotime('this Sunday'));
		/** @noinspection PhpParamsInspection */
		$nextWeekStartDateTime = new DateTimeField($nextweekZero . ' ' . date('H:i:s'));
		/** @noinspection PhpParamsInspection */
		$lastweekOne = date('Y-m-d', strtotime('-1 week Saturday'));
		/** @noinspection PhpParamsInspection */
		$lastWeekEndDateTime = new DateTimeField($lastweekOne . ' ' . date('H:i:s'));
		$lastweekZero = date('Y-m-d', strtotime('-2 week Sunday'));
		/** @noinspection PhpParamsInspection */
		$lastWeekStartDateTime = new DateTimeField($lastweekZero . ' ' . date('H:i:s'));
		$thisweekOne = date('Y-m-d', strtotime('this Saturday'));
		/** @noinspection PhpParamsInspection */
		$thisWeekEndDateTime = new DateTimeField($thisweekOne . ' ' . date('H:i:s'));
		$thisweekZero = date('Y-m-d', strtotime('-1 week Sunday'));
		/** @noinspection PhpParamsInspection */
		$thisWeekStartDateTime = new DateTimeField($thisweekZero . ' ' . date('H:i:s'));
		$tomorrow = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') + 1), date('Y')));
		/** @noinspection PhpParamsInspection */
		$tomorrowDateTime = new DateTimeField($tomorrow . ' ' . date('H:i:s'));
		/** @noinspection PhpParamsInspection */
		$todayDateTime = new DateTimeField(date('Y-m-d H:i:s'));
		$yesterday = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') - 1), date('Y')));
		/** @noinspection PhpParamsInspection */
		$yesterdayDateTime = new DateTimeField($yesterday . ' ' . date('H:i:s'));
		/** @noinspection PhpUndefinedMethodInspection */
		$sjsStr = '<script language="JavaScript" type="text/javaScript">
			function showDateRange( type ) {
				if ( type !== "custom" ) {
					document.NewReport.startdate.readOnly=true;
					document.NewReport.enddate.readOnly=true;
					getObj("jscal_trigger_date_start").style.visibility="hidden";
					getObj("jscal_trigger_date_end").style.visibility="hidden";
				} else {
					document.NewReport.startdate.readOnly=false;
					document.NewReport.enddate.readOnly=false;
					getObj("jscal_trigger_date_start").style.visibility="visible";
					getObj("jscal_trigger_date_end").style.visibility="visible";
				}
				if( type === "today" ) {
					document.NewReport.startdate.value = "' . $todayDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $todayDateTime->getDisplayDate() . '";

				} else if( type === "yesterday" ) {
					document.NewReport.startdate.value = "' . $yesterdayDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $yesterdayDateTime->getDisplayDate() . '";

				} else if( type === "tomorrow" ) {
					document.NewReport.startdate.value = "' . $tomorrowDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $tomorrowDateTime->getDisplayDate() . '";

				} else if( type === "thisweek" ) {
					document.NewReport.startdate.value = "' . $thisWeekStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $thisWeekEndDateTime->getDisplayDate() . '";

				} else if( type === "lastweek" ) {
					document.NewReport.startdate.value = "' . $lastWeekStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $lastWeekEndDateTime->getDisplayDate() . '";

				} else if( type === "nextweek" ) {
					document.NewReport.startdate.value = "' . $nextWeekStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $nextWeekEndDateTime->getDisplayDate() . '";

				} else if( type === "thismonth" ) {
					document.NewReport.startdate.value = "' . $currentMonthStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $currentMonthEndDateTime->getDisplayDate() . '";

				} else if( type === "lastmonth" ) {
					document.NewReport.startdate.value = "' . $lastMonthStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $lastMonthEndDateTime->getDisplayDate() . '";

				} else if( type === "nextmonth" ) {
					document.NewReport.startdate.value = "' . $nextMonthStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $nextMonthEndDateTime->getDisplayDate() . '";

				} else if( type === "next7days" ) {
					document.NewReport.startdate.value = "' . $todayDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $nextSevenDaysDateTime->getDisplayDate() . '";

				} else if( type === "next30days" ) {
					document.NewReport.startdate.value = "' . $todayDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $nextThirtyDaysDateTime->getDisplayDate() . '";

				} else if( type === "next60days" ) {
					document.NewReport.startdate.value = "' . $todayDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $nextSixtyDaysDateTime->getDisplayDate() . '";

				} else if( type === "next90days" ) {
					document.NewReport.startdate.value = "' . $todayDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $nextNinetyDaysDateTime->getDisplayDate() . '";

				} else if( type === "next120days" ) {
					document.NewReport.startdate.value = "' . $todayDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $nextOneHundredTwentyDaysDateTime->getDisplayDate() . '";

				} else if( type === "last7days" ) {
					document.NewReport.startdate.value = "' . $lastSevenDaysDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value =  "' . $todayDateTime->getDisplayDate() . '";

				} else if( type === "last30days" ) {
					document.NewReport.startdate.value = "' . $lastThirtyDaysDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $todayDateTime->getDisplayDate() . '";

				} else if( type === "last60days" ) {
					document.NewReport.startdate.value = "' . $lastSixtyDaysDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $todayDateTime->getDisplayDate() . '";

				} else if( type === "last90days" ) {
					document.NewReport.startdate.value = "' . $lastNinetyDaysDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $todayDateTime->getDisplayDate() . '";

				} else if( type === "last120days" ) {
					document.NewReport.startdate.value = "' . $lastOneHundredTwentyDaysDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $todayDateTime->getDisplayDate() . '";

				} else if( type === "thisfy" ) {
					document.NewReport.startdate.value = "' . $currentFYStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $currentFYEndDateTime->getDisplayDate() . '";

				} else if( type === "prevfy" ) {
					document.NewReport.startdate.value = "' . $lastFYStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $lastFYEndDateTime->getDisplayDate() . '";

				} else if( type === "nextfy" ) {
					document.NewReport.startdate.value = "' . $nextFYStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $nextFYEndDateTime->getDisplayDate() . '";

				} else if( type === "nextfq" ) {
					document.NewReport.startdate.value = "' . $nFqStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $nFqEndDateTime->getDisplayDate() . '";

				} else if( type === "prevfq" ) {
					document.NewReport.startdate.value = "' . $pFqStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $pFqEndDateTime->getDisplayDate() . '";

				} else if( type === "thisfq" ) {
					document.NewReport.startdate.value = "' . $cFqStartDateTime->getDisplayDate() . '";
					document.NewReport.enddate.value = "' . $cFqEndDateTime->getDisplayDate() . '";

				} else {
					document.NewReport.startdate.value = "";
					document.NewReport.enddate.value = "";
				}
			}
		</script>';
		return $sjsStr;
	}

	public function getaccesfield($module) {
		global $adb;
		$access_fields = array();

		$profileList = getCurrentUserProfileList();
		$query = 'SELECT vtiger_field.fieldname FROM vtiger_field INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid';
		$params = array();
		if ($module == 'Calendar') {
			$query .= ' WHERE vtiger_field.tabid in (9,16) AND vtiger_field.displaytype IN (1,2,3) AND vtiger_profile2field.visible=0 AND vtiger_def_org_field.visible=0 AND vtiger_field.presence IN (0,2)';
			if (count($profileList) > 0) {
				$query .= ' AND vtiger_profile2field.profileid IN (' . generateQuestionMarks($profileList) . ')';
				array_push($params, $profileList);
			}
			$query .= ' GROUP BY vtiger_field.fieldid ORDER BY block,sequence';
		} else {
			array_push($params, $this->primodule, $this->secmodule);
			$query .= ' WHERE vtiger_field.tabid IN (SELECT tabid from vtiger_tab where vtiger_tab.name IN (?,?)) AND vtiger_field.displaytype IN (1,2,3) AND vtiger_profile2field.visible=0 AND vtiger_def_org_field.visible=0 AND vtiger_field.presence IN (0,2)';
			if (count($profileList) > 0) {
				$query .= ' AND vtiger_profile2field.profileid IN (' . generateQuestionMarks($profileList) . ')';
				array_push($params, $profileList);
			}
			$query .= ' GROUP BY vtiger_field.fieldid ORDER BY block,sequence';
		}
		$result = $adb->pquery($query, $params);


		while ($collistrow = $adb->fetch_array($result)) {
			$access_fields[] = $collistrow['fieldname'];
		}
		return $access_fields;
	}

	/**
	 * Function to set the order of grouping and to find the columns responsible
	 * to the grouping
	 * This function accepts the vtiger_reportid as variable,sets the variable ascdescorder[] to the sort order and
	 * returns the array array_list which has the column responsible for the grouping
	 * array array_list[0]=columnname

	 * @param $reportid

	 * @return array
	 */
	public function getSelctedSortingColumns($reportid) {
		global $adb;
		global $log;
		$array_list = array();

		$sreportsortsql = 'SELECT vtiger_reportsortcol.* FROM vtiger_report';
		$sreportsortsql .= ' INNER JOIN vtiger_reportsortcol ON vtiger_report.reportid = vtiger_reportsortcol.reportid';
		$sreportsortsql .= ' WHERE vtiger_report.reportid =? ORDER BY vtiger_reportsortcol.sortcolid';

		$result = $adb->pquery($sreportsortsql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldcolname = $adb->query_result($result, $i, 'columnname');
			$sort_values = $adb->query_result($result, $i, 'sortorder');
			$this->ascdescorder[] = $sort_values;
			$array_list[] = $fieldcolname;
		}

		$log->info('Reports :: Successfully returned getSelctedSortingColumns');
		return $array_list;
	}

	/**
	 * Function to get the selected columns list for a selected vtiger_report
	 * This function accepts the vtiger_reportid as the argument and get the selected columns
	 * for the given vtiger_reportid and it forms a combo lists and returns
	 * HTML of the combo values

	 * @param $reportid

	 * @return string
	 */
	public function getSelectedColumnsList($reportid) {
		global $adb;
		global $log;
		global $is_admin;
		$shtml = '';

		$ssql = 'SELECT vtiger_selectcolumn.* FROM vtiger_report INNER JOIN vtiger_selectquery ON vtiger_selectquery.queryid = vtiger_report.queryid';
		$ssql .= ' LEFT JOIN vtiger_selectcolumn ON vtiger_selectcolumn.queryid = vtiger_selectquery.queryid';
		$ssql .= ' WHERE vtiger_report.reportid = ?';
		$ssql .= ' ORDER BY vtiger_selectcolumn.columnindex';
		$result = $adb->pquery($ssql, array($reportid));
		$permitted_fields = array();

		$selected_mod = explode(':', $this->secmodule);
		array_push($selected_mod, $this->primodule);

		while ($columnslistrow = $adb->fetch_array($result)) {
			$fieldcolname = $columnslistrow['columnname'];

			$selmod_field_disabled = true;
			foreach ($selected_mod as $smod) {
				if ((stripos($fieldcolname, ':' . $smod . '_') > -1) && vtlib_isModuleActive($smod)) {
					$selmod_field_disabled = false;
					break;
				}
			}
			if ($selmod_field_disabled == false) {
				list($colname, $module_field, $fieldname) = explode(':', $fieldcolname);
				list($module) = explode('_', $module_field);
				if (count($permitted_fields) == 0 && $is_admin == false) {
					$permitted_fields = $this->getaccesfield($module);
				}
				$fieldlabel = trim(str_replace($module, ' ', $module_field));
				$mod_arr = explode('_', $fieldlabel);
				$mod = ($mod_arr[0] == '') ? $module : $mod_arr[0];
				$fieldlabel = trim(str_replace('_', ' ', $fieldlabel));
				// modified code to support i18n issue
				// module
				$mod_lbl = getTranslatedString($mod, $module);
				// fieldlabel
				$fld_lbl = getTranslatedString($fieldlabel, $module);
				$fieldlabel = $mod_lbl . ' ' . $fld_lbl;
				if (CheckFieldPermission($fieldname, $mod) != 'true' && $colname != 'crmid') {
					$shtml .= '<option value="' . $fieldcolname . "\" disabled = 'true'>" . $fieldlabel . '</option>';
				} else {
					$shtml .= '<option value="' . $fieldcolname . '">' . $fieldlabel . '</option>';
				}
			}
			// end
		}
		$log->info('ReportRun :: Successfully returned getQueryColumnsList' . $reportid);
		return $shtml;
	}

	public function getAdvancedFilterList($reportid) {
		global $adb;
		global $log;
		global $current_user;
		$field = '';

		$advft_criteria = array();

		$sql = 'SELECT * FROM vtiger_relcriteria_grouping WHERE queryid = ? ORDER BY groupid';
		$groupsresult = $adb->pquery($sql, array($reportid));

		$i = 1;
		$j = 0;
		while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
			$groupId = $relcriteriagroup['groupid'];
			$groupCondition = $relcriteriagroup['group_condition'];

			$ssql = 'SELECT vtiger_relcriteria.* FROM vtiger_report
						INNER JOIN vtiger_relcriteria ON vtiger_relcriteria.queryid = vtiger_report.queryid
						LEFT JOIN vtiger_relcriteria_grouping ON vtiger_relcriteria.queryid = vtiger_relcriteria_grouping.queryid
								AND vtiger_relcriteria.groupid = vtiger_relcriteria_grouping.groupid';
			$ssql .= ' where vtiger_report.reportid = ? AND vtiger_relcriteria.groupid = ? order by vtiger_relcriteria.columnindex';


			$result = $adb->pquery($ssql, array($reportid, $groupId));
			$noOfColumns = $adb->num_rows($result);
			if ($noOfColumns <= 0) {
				continue;
			}

			global $default_charset;
			while ($relcriteriarow = $adb->fetch_array($result)) {
				$criteria = array();
				$criteria['columnname'] = html_entity_decode($relcriteriarow['columnname'], null, $default_charset);
				$criteria['comparator'] = $relcriteriarow['comparator'];
				$advfilterval = $relcriteriarow['value'];
				$col = explode(':', $relcriteriarow['columnname']);

				$moduleFieldLabel = $col[2];

				list($module, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
				$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
				$fieldType = null;

				if (!empty($fieldInfo)) {
					$field = WebserviceField::fromarray($adb, $fieldInfo);
					$fieldType = $field->getFieldDataType();
				}
				if ($fieldType == 'currency') {
					if ($field->getUIType() == '71') {
						if ($_REQUEST['action'] != 'CreateXL') {
							$advfilterval = CurrencyField::convertToUserFormat($advfilterval, $current_user);
						}
					} else if ($field->getUIType() == '72') {
						$advfilterval = CurrencyField::convertToUserFormat($advfilterval, $current_user, true);
					}
				}

				$temp_val = explode(',', $relcriteriarow['value']);
				$advfilterval = self::auxGetAdvancedFilterList($advfilterval, $col, $temp_val);

				$criteria['value'] = decode_html($advfilterval);
				$criteria['column_condition'] = $relcriteriarow['column_condition'];

				$advft_criteria[$i]['columns'][$j] = $criteria;
				$advft_criteria[$i]['condition'] = $groupCondition;
				$j++;
			}
			$i++;
		}
		// Clear the condition (and/or) for last group, if any.
		if (!empty($advft_criteria[($i - 1)]['condition'])) {
			$advft_criteria[($i - 1)]['condition'] = '';
		}
		$this->advft_criteria = $advft_criteria;
		$log->info('Reports :: Successfully returned getAdvancedFilterList');
		return true;
	}

	public function auxGetAdvancedFilterList($auxAdvFilterVal, $auxCol, $auxTemVal) {
		if ($auxCol[4] == 'D' || ($auxCol[4] == 'T' && $auxCol[1] != 'time_start' && $auxCol[1] != 'time_end') || ($auxCol[4] == 'DT')) {
			$val = array();
			$countTempVal = count($auxTemVal);
			for ($x = 0; $x < $countTempVal; $x++) {
				if ($auxCol[4] == 'D') {
					/** @noinspection PhpParamsInspection */
					$date = new DateTimeField(trim($auxTemVal[$x]));
					$val[$x] = $date->getDisplayDate();
				} else if ($auxCol[4] == 'DT') {
					/** @noinspection PhpParamsInspection */
					$date = new DateTimeField(trim($auxTemVal[$x]));
					$val[$x] = $date->getDisplayDateTimeValue();
				} else {
					/** @noinspection PhpParamsInspection */
					$date = new DateTimeField(trim($auxTemVal[$x]));
					$val[$x] = $date->getDisplayTime();
				}
			}
			$auxAdvFilterVal = implode(',', $val);
		}
		return $auxAdvFilterVal;
	}

	// advanced filter

	/**
	 * Function to get the list of vtiger_report folders when Save and run  the vtiger_report
	 * This function gets the vtiger_report folders from database and form
	 * a combo values of the folders and return
	 * HTML of the combo values
	 */
	public function sgetRptFldrSaveReport () {
		global $adb;
		global $log;
		$shtml = '';

		$sql = 'SELECT * FROM vtiger_reportfolder ORDER BY folderid';
		$result = $adb->pquery($sql, array());
		$reportfldrow = $adb->fetch_array($result);
		do {
			$shtml .= "<option value='" . $reportfldrow['folderid'] . "'>" . $reportfldrow['foldername'] . '</option>';
		} while ($reportfldrow = $adb->fetch_array($result));

		$log->info('Reports :: Successfully returned sgetRptFldrSaveReport');
		return $shtml;
	}

	/**
	 * Function to get the column to total vtiger_fields in Reports
	 * This function gets columns to total vtiger_field
	 * and generated the html for that vtiger_fields
	 * It returns the HTML of the vtiger_fields along with the check boxes

	 * @param $primarymodule
	 * @param $secondarymodule

	 * @return array
	 */
	public function sgetColumntoTotal($primarymodule, $secondarymodule) {
		$options = array();
		$options [] = $this->sgetColumnstoTotalHtml($primarymodule);
		if (!empty($secondarymodule)) {
			$countSecondaryModule = count($secondarymodule);
			for ($i = 0; $i < $countSecondaryModule; $i++) {
				$options [] = $this->sgetColumnstoTotalHtml($secondarymodule[$i]);
			}
		}
		return $options;
	}

	/**
	 * Function to get the selected columns of total vtiger_fields in Reports
	 * This function gets selected columns of total vtiger_field
	 * and generated the html for that vtiger_fields
	 * It returns the HTML of the vtiger_fields along with the check boxes

	 * @param $primarymodule
	 * @param $secondarymodule
	 * @param $reportid

	 * @return array
	 */
	public function sgetColumntoTotalSelected($primarymodule, $secondarymodule, $reportid) {
		global $adb;
		global $log;
		$options = array();
		if ($reportid != '') {
			$ssql = 'SELECT vtiger_reportsummary.* FROM vtiger_reportsummary INNER JOIN vtiger_report ON vtiger_report.reportid = vtiger_reportsummary.reportsummaryid WHERE vtiger_report.reportid=?';
			$result = $adb->pquery($ssql, array($reportid));
			if ($result) {
				$reportsummaryrow = $adb->fetch_array($result);

				do {
					$this->columnssummary[] = $reportsummaryrow['columnname'];
				} while ($reportsummaryrow = $adb->fetch_array($result));
			}
		}
		$options [] = $this->sgetColumnstoTotalHtml($primarymodule);
		if ($secondarymodule != '') {
			$secondarymodule = explode(':', $secondarymodule);
			$countSecondaryModule = count($secondarymodule);
			for ($i = 0; $i < $countSecondaryModule; $i++) {
				$options [] = $this->sgetColumnstoTotalHtml($secondarymodule[$i]);
			}
		}

		$log->info('Reports :: Successfully returned sgetColumntoTotalSelected');
		return $options;
	}

	/**
	 * Function to form the HTML for columns to total
	 * This function formulates the HTML format of the
	 * vtiger_fields along with four checkboxes
	 * It returns the HTML of the vtiger_fields along with the check boxes

	 * @param $module

	 * @return array
	 */
	public function sgetColumnstoTotalHtml($module) {
		global $adb;
		global $log;
		global $is_admin;
		$tabid = getTabid($module);
		$escapedchars = array('_SUM', '_AVG', '_MIN', '_MAX');
		$sparams = array($tabid);
		if ($is_admin == true) {
			$ssql = 'SELECT * FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_field.uitype != 50 AND vtiger_field.tabid=? AND vtiger_field.displaytype IN (1,2,3) AND vtiger_field.presence IN (0,2) ';
		} else {
			$profileList = getCurrentUserProfileList();
			$ssql = 'SELECT * FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid  WHERE vtiger_field.uitype != 50 AND vtiger_field.tabid=? AND vtiger_field.displaytype IN (1,2,3) AND vtiger_def_org_field.visible=0 AND vtiger_profile2field.visible=0 AND vtiger_field.presence IN (0,2)';
			if (count($profileList) > 0) {
				$ssql .= ' and vtiger_profile2field.profileid in (' . generateQuestionMarks($profileList) . ')';
				array_push($sparams, $profileList);
			}
		}
		$ssql = self::auxOneSgetColumnstoTotalHtml($ssql, $tabid);
		$ssql .= ' order by sequence';
		$result = $adb->pquery($ssql, $sparams);
		$columntototalrow = $adb->fetch_array($result);
		$options_list = array();
		do {
			$typeofdata = explode('~', $columntototalrow['typeofdata']);
			if (in_array($typeofdata[0], array('NN', 'N', 'I'))) {
				$options = array();
				if (isset($this->columnssummary)) {
					$selectedColumnOne = '';

					$countColumnSummary = count($this->columnssummary);
					for ($i = 0; $i < $countColumnSummary; $i++) {
						$selectedcolumnarray = explode(':', $this->columnssummary[$i]);
						$selectedcolumn = $selectedcolumnarray[1] . ':' . $selectedcolumnarray[2] . ':' . str_replace($escapedchars, '', $selectedcolumnarray[3]);

						if ($selectedcolumn != $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . str_replace(' ', '_', $columntototalrow['fieldlabel'])) {
							/** @noinspection PhpIllegalStringOffsetInspection */
							$selectedColumnOne[$selectedcolumnarray[4]] = $this->columnssummary[$i];
						}
					}
					if (isset($_REQUEST['record']) && $_REQUEST['record'] != '') {
						$options['label'][] = getTranslatedString($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' -' . getTranslatedString($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
					}

					$columntototalrow['fieldlabel'] = str_replace(' ', '_', $columntototalrow['fieldlabel']);
					$options [] = getTranslatedString($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' - ' . getTranslatedString($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
					$options = self::auxTwoSgetColumnstoTotalHtml($selectedColumnOne, $columntototalrow);
				} else {
					$options [] = getTranslatedString($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' - ' . getTranslatedString($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
					$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_SUM:2" type="checkbox" value="">';
					$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_AVG:3" type="checkbox" value="" >';
					$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_MIN:4"type="checkbox" value="" >';
					$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_MAX:5" type="checkbox" value="" >';
				}
				$options_list [] = $options;
			}
		} while ($columntototalrow = $adb->fetch_array($result));

		$log->info('Reports :: Successfully returned sgetColumnstoTotalHtml');
		return $options_list;
	}

	public function auxOneSgetColumnstoTotalHtml($ssql, $tabid) {
		switch ($tabid) {
			case 2:
			// Potentials
			// ie. Campaign name will not displayed in Potential's report calcullation
			$ssql .= " and vtiger_field.fieldname not in ('campaignid')";
				break;
			case 4:
			// Contacts
			$ssql .= " and vtiger_field.fieldname not in ('account_id')";
				break;
			case 6:
			// Accounts
			$ssql .= " and vtiger_field.fieldname not in ('account_id')";
				break;
			case 9:
			// Calendar
			$ssql .= " and vtiger_field.fieldname not in ('parent_id','contact_id')";
				break;
			case 13:
			// Trouble tickets(HelpDesk)
			$ssql .= " and vtiger_field.fieldname not in ('parent_id','product_id')";
				break;
			case 14:
			// Products
			$ssql .= " and vtiger_field.fieldname not in ('vendor_id','product_id')";
				break;
			default:
			$ssql = self::auxOneOneSgetColumnstoTotalHtml($ssql, $tabid);
				break;
		}
		return $ssql;
	}

	public function auxOneOneSgetColumnstoTotalHtml($ssql, $tabid) {
		switch ($tabid) {
			case 20:
			// Quotes
			$ssql .= " and vtiger_field.fieldname not in ('potential_id','assigned_user_id1','account_id','currency_id')";
				break;
			case 21:
			// Purchase Order
			$ssql .= " and vtiger_field.fieldname not in ('contact_id','vendor_id','currency_id')";
				break;
			case 22:
			// SalesOrder
			$ssql .= " and vtiger_field.fieldname not in ('potential_id','account_id','contact_id','quote_id','currency_id')";
				break;
			case 23:
			// Invoice
			$ssql .= " and vtiger_field.fieldname not in ('salesorder_id','contact_id','account_id','currency_id')";
				break;
			case 26:
			// Campaigns
			$ssql .= " and vtiger_field.fieldname not in ('product_id')";
				break;
			default:
			// empty
				break;
		}
		return $ssql;
	}

	public function auxTwoSgetColumnstoTotalHtml($selectedColumnOne, $columntototalrow) {
		if ($selectedColumnOne[2] == 'cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_SUM:2') {
			$options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_SUM:2" type="checkbox" value="">';
		} else {
			$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_SUM:2" type="checkbox" value="">';
		}
		if ($selectedColumnOne[3] == 'cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_AVG:3') {
			$options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_AVG:3" type="checkbox" value="">';
		} else {
			$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_AVG:3" type="checkbox" value="">';
		}

		if ($selectedColumnOne[4] == 'cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_MIN:4') {
			$options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_MIN:4" type="checkbox" value="">';
		} else {
			$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_MIN:4" type="checkbox" value="">';
		}

		if ($selectedColumnOne[5] == 'cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_MAX:5') {
			$options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_MAX:5" type="checkbox" value="">';
		} else {
			$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '_MAX:5" type="checkbox" value="">';
		}
		return $options;
	}

	/**
	 * Function to get the  advanced filter criteria for an option
	 * This function accepts The option in the advenced filter as an argument
	 * This generate filter criteria for the advanced filter
	 * It returns a HTML string of combo values

	 * @param string $selected

	 * @return string
	 */
	public static function getAdvCriteriaHtml($selected = '') {
		global $adv_filter_options;
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

}

/**
 * Function to get the primary module list in vtiger_reports
 * This function generates the list of primary modules in vtiger_reports
 * and returns an array of permitted modules

 * @param $focus

 * @return array
 */
function getReportsModuleList($focus) {
	global $adb;
	$modules = array();


	foreach($focus->module_list as $key => $key) {
		$consult='SELECT * FROM vtiger_module_report AS mr INNER JOIN vtiger_tab AS t ON (mr.tabid = t.tabid) AND t.presence <> -1 WHERE t.name = ? AND mr.reportavailable = 1';
		$result=$adb->pquery($consult,array($key));
		if($adb->num_rows($result)>0) {
			if(isPermitted($key,'index') == 'yes') {
				$modules [$key] = getTranslatedString($key,$key);
			}
		}
	}

	asort($modules);
	return $modules;
}

/**
 * Function to get the Related module list in vtiger_reports
 * This function generates the list of secondary modules in vtiger_reports
 * and returns the related module as an array

 * @param $module
 * @param $focus

 * @return array
 */
function getReportRelatedModules($module, $focus) {
	$optionhtml = array();
	if(vtlib_isModuleActive($module)) {
		if(!empty($focus->related_modules[$module])) {
			foreach($focus->related_modules[$module] as $rel_modules) {
				if(isPermitted($rel_modules,'index') == 'yes') {
					$optionhtml []= $rel_modules;
				}
			}
		}
	}
	return $optionhtml;
}

function updateAdvancedCriteria($reportid, $advft_criteria, $advft_criteria_groups) {
	global $adb;

	$idelrelcriteriasql = 'DELETE FROM vtiger_relcriteria WHERE queryid=?';
	$adb->pquery($idelrelcriteriasql, array($reportid));

	$idelrelcriteriagroupsql = 'DELETE FROM vtiger_relcriteria_grouping WHERE queryid=?';
	$adb->pquery($idelrelcriteriagroupsql, array($reportid));

	if(empty($advft_criteria)) {
		return;
	}

	foreach($advft_criteria as $column_index => $column_condition) {
		if(empty($column_condition)) {
			continue;
		}

		$adv_filter_column = $column_condition['columnname'];
		$adv_filter_comparator = $column_condition['comparator'];
		$adv_filter_value = $column_condition['value'];
		$adv_filter_column_condition = $column_condition['columncondition'];
		$adv_filter_groupid = $column_condition['groupid'];

		$column_info = explode(':',$adv_filter_column);
		$moduleFieldLabel = $column_info[2];

		list($module, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
		$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
		$adv_filter_value = auxOneUpdateAdvancedCriteria($adv_filter_value, $fieldInfo);


		$temp_val = explode(',',$adv_filter_value);
		if(($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' )) {
			$countTempVal = count($temp_val);
			$val = auxTwoUpdateAdvancedCriteria($countTempVal, $temp_val, $column_info);
			$adv_filter_value = implode(',',$val);
		}


		$irelcriteriasql = 'INSERT INTO vtiger_relcriteria(QUERYID,COLUMNINDEX,COLUMNNAME,COMPARATOR,VALUE,GROUPID,COLUMN_CONDITION) VALUES (?,?,?,?,?,?,?)';
		$adb->pquery($irelcriteriasql, array($reportid, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition));

		// Update the condition expression for the group to which the condition column belongs
		$groupConditionExpression = '';
		if(!empty($advft_criteria_groups[$adv_filter_groupid]['conditionexpression'])) {
			$groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]['conditionexpression'];
		}
		$groupConditionExpression = $groupConditionExpression .' '. $column_index .' '. $adv_filter_column_condition;
		$advft_criteria_groups[$adv_filter_groupid]['conditionexpression'] = $groupConditionExpression;
	}

	auxThreeUpdateAdvancedCriteria($advft_criteria_groups, $reportid);
}

function auxOneUpdateAdvancedCriteria($adv_filter_value, $fieldInfo) {
	global $adb;
	$fieldType = null;
	$field = '';
	if(!empty($fieldInfo)) {
		$field = WebserviceField::fromarray($adb, $fieldInfo);
		$fieldType = $field->getFieldDataType();
	}
	if($fieldType == 'currency') {
		// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
		if($field->getUIType() == '72') {
			$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value, null, true);
		} else {
			$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value);
		}
	}
	return $adv_filter_value;
}

function auxTwoUpdateAdvancedCriteria($countTempVal, $temp_val, $column_info) {
	$val = array();
	for($x=0; $x < $countTempVal; $x++) {
		if(trim($temp_val[$x]) != '') {
			/** @noinspection PhpParamsInspection */
			$date = new DateTimeField(trim($temp_val[$x]));
			if($column_info[4] == 'D') {
				/** @noinspection PhpParamsInspection */
				$val[$x] = DateTimeField::convertToUserFormat(
					trim($temp_val[$x])
				);
			} else if($column_info[4] == 'DT') {
				$val[$x] = $date->getDBInsertDateTimeValue();
			} else {
				$val[$x] = $date->getDBInsertTimeValue();
			}
		}
	}
	return $val;
}

function auxThreeUpdateAdvancedCriteria($advft_criteria_groups, $reportid) {
	global $adb;
	foreach($advft_criteria_groups as $group_index => $group_condition_info) {
		if(empty($group_condition_info)) {
			continue;
		}
		if(empty($group_condition_info['conditionexpression'])) {
			continue; // Case when the group doesn't have any column criteria
		}

		$irelcriteriagroupsql = 'INSERT INTO vtiger_relcriteria_grouping(GROUPID,QUERYID,GROUP_CONDITION,CONDITION_EXPRESSION) VALUES (?,?,?,?)';
		$adb->pquery($irelcriteriagroupsql, array($group_index, $reportid, $group_condition_info['groupcondition'], $group_condition_info['conditionexpression']));
	}
}

<?php
	require_once('Smarty_setup.php');
	require_once('data/Tracker.php');
	require_once('include/logging.php');
	require_once('include/utils/utils.php');
	require_once('modules/Reports/Reports.php');

	global $app_strings;
	global $app_list_strings;
	global $mod_strings;
	global $current_language;
	$current_module_strings = return_module_language($current_language, 'Reports');
	global $list_max_entries_per_page;
	global $urlPrefix;

	/** @noinspection PhpUndefinedClassInspection */
	$log = LoggerManager::getLogger('report_type');
	global $currentModule;
	global $image_path;
	global $theme;
	global $current_user;
	$recordid = '';

	$report_std_filter = new vtigerCRM_Smarty;
	$report_std_filter->assign('MOD', $mod_strings);
	$report_std_filter->assign('APP', $app_strings);
	$report_std_filter->assign('IMAGE_PATH',$image_path);
	$report_std_filter->assign('DATEFORMAT',$current_user->date_format);
	$report_std_filter->assign('JS_DATEFORMAT',parse_calendardate($app_strings['NTC_DATE_FORMAT']));

	$roleid = $current_user->column_fields['roleid'];
	$user_array = getAllUserName();
	$userIdStr = '';
	$userNameStr = '';
	$m=0;
	foreach($user_array as $userid => $username){
	if($userid!=$current_user->id) {
		if($m!=0) {
			$userIdStr .= ',';
			$userNameStr .= ',';
		}
		$userIdStr .="'".$userid."'";
		$userNameStr .="'.escape_single_quotes(decode_html($username)).'";
		$m++;
	}
	}

	$user_groups = getAllGroupName();
	$groupIdStr = '';
	$groupNameStr = '';
	$l=0;
	foreach($user_groups as $grpid => $groupname){
	if($l!=0) {
		$groupIdStr .= ',';
		$groupNameStr .= ',';
	}
	$groupIdStr .= "'".$grpid."'";
	$groupNameStr .= "'.escape_single_quotes(decode_html($groupname)).'";
	$l++;
	}
	if(isset($_REQUEST['record']) && $_REQUEST['record']!='') {
	$reportid = vtlib_purify($_REQUEST['record']);
	$visiblecriteria=getVisibleCriteria($recordid);
	$report_std_filter->assign('VISIBLECRITERIA', $visiblecriteria);
	$member = getShareInfo($recordid);
	$report_std_filter->assign('MEMBER', $member);
	} else {
	$visiblecriteria=getVisibleCriteria();
	$report_std_filter->assign('VISIBLECRITERIA', $visiblecriteria);
	}
	$report_std_filter->assign('GROUPNAMESTR', $groupNameStr);
	$report_std_filter->assign('USERNAMESTR', $userNameStr);
	$report_std_filter->assign('GROUPIDSTR', $groupIdStr);
	$report_std_filter->assign('USERIDSTR', $userIdStr);

	$report_std_filter->display('ReportSharing.tpl');

	/**
	 * Function to get visible criteria for a report
	 *  This function accepts The reportid as an argument
	 *  It returns a array of selected option of sharing along with other options

	 * @param string $recordid

	 * @return array
	 */
	function getVisibleCriteria($recordid = '') {
	global $adb;
	$filter = array();
	$selcriteria = '';
	if($recordid!='') {
		$result = $adb->pquery('SELECT sharingtype FROM vtiger_report WHERE reportid=?',array($recordid));
		$selcriteria=$adb->query_result($result,0,'sharingtype');
	}
	if($selcriteria == '') {
		$selcriteria = 'Public';
	}
	$filter_result = $adb->query('SELECT * FROM vtiger_reportfilters');
	$numrows = $adb->num_rows($filter_result);
	for($j=0; $j<$numrows; $j++) {
		$filtername = $adb->query_result($filter_result,$j,'name');
		if($filtername == 'Private') {
			$FilterKey='Private';
			$FilterValue=getTranslatedString('PRIVATE_FILTER');
		} else if($filtername=='Shared') {
			$FilterKey='Shared';
			$FilterValue=getTranslatedString('SHARE_FILTER');
		} else {
			$FilterKey='Public';
			$FilterValue=getTranslatedString('PUBLIC_FILTER');
		}
		if($FilterKey == $selcriteria) {
			$shtml['value'] = $FilterKey;
			$shtml['text'] = $FilterValue;
			$shtml['selected'] = 'selected';
		} else {
			$shtml['value'] = $FilterKey;
			$shtml['text'] = $FilterValue;
			$shtml['selected'] = '';
		}
		$filter[] = $shtml;
	}
	return $filter;
	}

	function getShareInfo($recordid = '') {
	global $adb;
	$member_data = array();
	$member_query = $adb->pquery("SELECT vtiger_reportsharing.setype,vtiger_users.id,vtiger_users.user_name FROM vtiger_reportsharing INNER JOIN vtiger_users ON vtiger_users.id = vtiger_reportsharing.shareid WHERE vtiger_reportsharing.setype='users' AND vtiger_reportsharing.reportid = ?",array($recordid));
	$noofrows = $adb->num_rows($member_query);
	if ($noofrows > 0) {
		for ($i=0; $i<$noofrows; $i++) {
			$userid = $adb->query_result($member_query,$i,'id');
			$username = $adb->query_result($member_query,$i,'user_name');
			$setype = $adb->query_result($member_query,$i,'setype');
			$member_data[] = array('id' => $setype.'::'.$userid,'name' => $setype.'::'.$username);
		}
	}

	$member_query = $adb->pquery("SELECT vtiger_reportsharing.setype,vtiger_groups.groupid,vtiger_groups.groupname FROM vtiger_reportsharing INNER JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_reportsharing.shareid WHERE vtiger_reportsharing.setype='groups' AND vtiger_reportsharing.reportid = ?",array($recordid));
	$noofrows = $adb->num_rows($member_query);
	if ($noofrows > 0) {
		for ($i=0; $i<$noofrows; $i++) {
			$grpid = $adb->query_result($member_query,$i,'groupid');
			$grpname = $adb->query_result($member_query,$i,'groupname');
			$setype = $adb->query_result($member_query,$i,'setype');
			$member_data[] = array('id' => $setype.'::'.$grpid,'name' => $setype.'::'.$grpname);
		}
	}
	return $member_data;
	}

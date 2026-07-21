<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *******************************************************************************/

require_once('config.php');
require_once('include/utils/utils.php');
global $current_user;
global $adb;
$db = PearDatabase::getInstance();
if (!empty($HTTP_SERVER_VARS['SERVER_SOFTWARE']) && strstr($HTTP_SERVER_VARS['SERVER_SOFTWARE'], 'Apache/2')){
	header ('Cache-Control: no-cache, pre-check=0, post-check=0, max-age=0');
}else{
	header ('Cache-Control: private, pre-check=0, post-check=0, max-age=0');
}

header ('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Content-Type: text/xml');

echo ("<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n");
echo ("  <rss version=\"2.0\">\n");
echo ("	<channel>\n");
echo ("	  <title>vtigerCRM Tickets</title>\n");
echo ("	  <link>".$site_URL."/index.php?module=Home&action=home_rss</link>\n");
echo ("	  <description>test</description>\n");
echo ("	  <managingEditor></managingEditor>\n");
echo ("	  <webMaster>".$current_user->user_name."</webMaster>\n");
echo ("	  <lastBuildDate>" . gmdate('D, d M Y H:i:s', time()) . " GMT</lastBuildDate>\n");
echo ("	  <generator>vtigerCRM</generator>\n");

//retrieving notifications******************************
//<<<<<<<<<<<<<<<< start of owner notify>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
$query = "select vtiger_crmentity.setype,vtiger_crmentity.crmid,vtiger_crmentity.smcreatorid,vtiger_crmentity.modifiedtime from vtiger_crmentity inner join vtiger_ownernotify on vtiger_crmentity.crmid=vtiger_ownernotify.crmid";

$result = $adb->pquery($query, array());
for($i=0;$i<$adb->num_rows($result);$i++){
    $mod_notify[$i] = $adb->fetch_array($result);

	if($mod_notify[$i]['setype']=='Calendar'){
		$tempquery='select subject from vtiger_activity where vtiger_activityid=?';
		$tempresult=$adb->pquery($tempquery, array($mod_notify[$i]['crmid']));
		$Activity_subject=$adb->fetch_array($tempresult);
		$notify_values[$i]=$Activity_subject['subject'];
	}
	//<<<<<<<<<<<<<<<< end of owner notify>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>


	// Variable reassignment and reformatting for author
	$author_id = $db->query_result($result,$i,'smcreatorid');
	$entry_author = getUserFullName($author_id);
	$entry_author = htmlspecialchars ($entry_author);

	$entry_link = $site_URL."/index.php?modules=".$mod_notify[$i]['setype']."&amp;action=DetailView&amp;record=".$mod_notify[$i]['crmid'];
	$entry_link = htmlspecialchars($entry_link);
	$entry_time = $db->query_result($result,$i,'modifiedtime');

	echo ("	  <item>\n");
	echo ("	    <title>".$mod_notify[$i]['setype']."</title>\n");
	echo ("	    <link>".$entry_link."</link>\n");
	echo ("	    <description>".$notify_values[$i]."</description>\n");
	echo ("	    <author>".$entry_author."</author>\n");
	echo ("	    <pubDate>".$entry_time."</pubDate>\n");
	echo ("	  </item>\n");
}
echo ("	</channel>\n");
echo ("  </rss>\n");
?>

<?php



echo "<pre>HOLAAA</pre>";


/*


global $currentModule,$adb,$theme,$mod_strings;

$record = $_REQUEST['record'];



echo "<pre>".print_r($mod_strings,true)."</pre>";


require_once('Smarty_setup.php');
include_once('include/utils/jQueryUtils.php');
require_once('include/CustomFieldUtil.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/utils/utils.php');
require_once('include/utils/PanelUtils.php');


$smarty=new vtigerCRM_Smarty;

$queryRecord = "SELECT *,(select tablabel from vtiger_tab tab where tab.name = notif.module limit 1) as tablabel FROM `vtiger_notifymanager` notif where notif.notifyid = ? ";


$notificacion = array();
$result = $adb->pquery($queryRecord,array($record),true);
while ($row = $adb->fetchByAssoc($result)) {
	$notificacion = $row;
}


echo "<pre>".print_r($notificacion,true)."</pre>";

*/
?>
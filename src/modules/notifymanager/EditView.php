<?php

global $currentModule,$adb,$theme,$mod_strings;


require_once('Smarty_setup.php');
include_once('include/utils/jQueryUtils.php');
require_once('include/CustomFieldUtil.php');
require_once('include/utils/PlatformUtils.class.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/utils/utils.php');
require_once('include/utils/PanelUtils.php');
require_once('modules/notifymanager/notifymanagerutils.php');



$record = $_REQUEST['record'];
$queryRecord = "SELECT *,(select tablabel from vtiger_tab tab where tab.name = notif.module limit 1) as tablabel FROM `vtiger_notifymanager` notif where notif.notifyid = ? ";

$notificacion = array();
$result = $adb->pquery($queryRecord,array($record),true);
while ($row = $adb->fetchByAssoc($result)) {
	$moduleNames = explode('#', $row['module']);
	$row['modules'] = $moduleNames;
	$notificacion = $row;
}

$modulosDeCampos 	= PlatformUtils::getVisibleEntityModulesData ($adb);
$vistasDisponibles 	= getVistasDisponibles();

$smarty=new vtigerCRM_Smarty;

$smarty->assign("MODULE",$currentModule);
$smarty->assign("MOD",$mod_strings);
$smarty->assign("APP",$app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("NOTIFICACION", $notificacion);
$smarty->assign("MODULOSDECAMPOS", $modulosDeCampos);
$smarty->assign("VISTASDISPONIBLES", $vistasDisponibles);


//echo "<pre>".print_r($notificacion,true)."</pre>";
$smarty->display('modules/notifymanager/editarNotificacion.tpl');

?>
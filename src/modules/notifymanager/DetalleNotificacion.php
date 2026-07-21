<?php

global $currentModule,$adb,$theme,$mod_strings;

require_once('Smarty_setup.php');
include_once('include/utils/jQueryUtils.php');
require_once('include/CustomFieldUtil.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/utils/utils.php');
require_once('include/utils/PanelUtils.php');


$record = $_REQUEST['record'];
$queryRecord = "SELECT *,(select tablabel from vtiger_tab tab where tab.name = notif.module limit 1) as tablabel FROM `vtiger_notifymanager` notif where notif.notifyid = ? ";


$notificacion = array();
$result = $adb->pquery($queryRecord,array($record),true);
while ($row = $adb->fetchByAssoc($result)) {
	
	if ($row['tablabel'] == ''){
		$titulo = '';
		$moduleNames = explode('#', $row['module']);
		foreach ($moduleNames as $keyName => $valName) {
			$queryTitulosDeModulos = "select tablabel from vtiger_tab tab where tab.name = ? ";
			$result2 = $adb->pquery($queryTitulosDeModulos,array($valName),true);
			while ($row2 = $adb->fetchByAssoc($result2)) {
				$titulo.= $row2['tablabel'].', ';
			}

		}
		$row['tablabel'] = substr($titulo, 0,(strlen($titulo)-2));
	}

	$notificacion = $row;
	
}

$smarty=new vtigerCRM_Smarty;

$smarty->assign("MODULE",$currentModule);
$smarty->assign("MOD",$mod_strings);
$smarty->assign("APP",$app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("NOTIFICACION", $notificacion);

$smarty->display('modules/notifymanager/detalleNotificacion.tpl');

?>
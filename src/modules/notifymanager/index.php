<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
global $currentModule,$adb,$theme,$mod_strings;

require_once('Smarty_setup.php');
include_once('include/utils/jQueryUtils.php');
require_once('include/CustomFieldUtil.php');
require_once('include/utils/UserInfoUtil.php');
require_once('include/utils/utils.php');
require_once('include/utils/PanelUtils.php');

$smarty=new vtigerCRM_Smarty;

$queryLista = "SELECT *,(select tablabel from vtiger_tab tab where tab.name = notif.module limit 1) as tablabel FROM `vtiger_notifymanager` notif ";

$LIMIT = '';
$TOTALxPAGE = 25;
$TOTALTOTAL = 0;
$TOTALPAGES = 1;
$query2 = $adb->pquery($queryLista,array());
$TOTALTOTAL = $adb->num_rows($query2);
if ($TOTALTOTAL > $TOTALxPAGE) {
		$TOTALPAGES = ceil($TOTALTOTAL/$TOTALxPAGE);
		$NROINICIAL = ($PAGEACTUAL - 1) * $TOTALxPAGE;
		$LIMIT = "LIMIT $NROINICIAL,$TOTALxPAGE";
}

$notificaciones = array();
$result = $adb->pquery("$queryLista $LIMIT",array(),true);


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

	$notificaciones[] = $row;
	
}


$smarty->assign("MODULE",$currentModule);
$smarty->assign("MOD",$mod_strings);
$smarty->assign("APP",$app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("NOTIFICACIONES", $notificaciones);


$smarty->display('modules/notifymanager/index.tpl');


?>

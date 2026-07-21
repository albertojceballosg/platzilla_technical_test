<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
/* [TT11207] Ajustes Página Tours Platzilla - 08/07/16 - Johana Romero */

require_once('include/database/PearDatabase.php');
require_once('modules/Home/HomeUtils.php');
require_once('Smarty_setup.php');

global $mod_strings;
global $app_strings;
global $app_list_strings;

global $adb, $theme, $current_user, $platPrincipal;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", return_module_language($current_language,'Home'));
$smarty->assign("THEME", $theme);
$smarty->assign("APP", $app_strings);
$smarty->assign("APLICACIONES", getAplicaciones()); 

if ($_REQUEST['mode'] == 'divmodulos') {    
    $modulos = getModulos('',$_REQUEST['page']);      
    $smarty->assign("MODULOS", $modulos);       

    $smarty->display("Home/modulos.tpl");
}else{
    $smarty->assign("MODULOS", getModulos('',null));    
    $smarty->display("Home/tour.tpl");
}
?>
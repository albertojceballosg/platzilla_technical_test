<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once('modules/Documents/Documents.php');
require_once('include/logging.php');
require_once('include/ListView/ListView.php');
require_once('include/utils/utils.php');
require_once('modules/CustomView/CustomView.php');
require_once('include/database/Postgres8.php');
require_once('modules/Documents/ComunesDocuments.php');


global $app_strings,$mod_strings,$list_max_entries_per_page,$adb;

global $currentModule,$image_path,$theme;


// Carpetas
if ( $_REQUEST['folderid'] ){
	$folders = getParentFolderForEdit($_REQUEST['folderid']);
	$folderPadre = getCarpetaPadreID($_REQUEST['folderid']);
	$folderActual = $_REQUEST['folderid'];
}else{
	$folders = getParentFolders();
	$folderPadre = 0;
	$folderActual = 0;
}



// Perfiles
$profiles = getProfilesforFolders();

//echo "profileToFolder <pre>".print_r($folders,true)."</pre>";
//echo "profiles <pre>".print_r($profiles,true)."</pre>";
//echo "FOLDERPADRE <pre>".print_r($folderPadre,true)."</pre>";


$smarty->assign("FOLDERS", $folders);
$smarty->assign("PROFILES", $profiles);
$smarty->assign("FOLDERPADRE", $folderPadre);
$smarty->assign("FOLDERACTUAL", $folderActual);



$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH",$image_path);
$smarty->assign("MODULE",$currentModule);
$smarty->assign("SINGLE_MOD",getTranslatedString('SINGLE_'.$currentModule, $currentModule));
$smarty->assign("BUTTONS",$other_text);
$smarty->assign("CATEGORY",$category);
$smarty->assign('MAX_RECORDS', $list_max_entries_per_page);


$smarty->display("modules/Documents/profileToFolder.tpl");

?>
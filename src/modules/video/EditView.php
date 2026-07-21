<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
global $app_strings, $mod_strings, $current_language, $currentModule, $theme, $adb, $CHECK_MOBILE;
global $list_max_entries_per_page,$clientView;

$adbBak = clone $adb;
	
require_once('Smarty_setup.php');
require_once('include/ListView/ListView.php');
require_once('modules/CustomView/CustomView.php');
require_once('include/DatabaseUtil.php');

function getExtension($str) {
	$i = strrpos($str,".");
	if (!$i) { return ""; }
	$l = strlen($str) - $i;
	$ext = substr($str,$i+1,$l);
	return $ext;
}

$torep=array('a','e','i','o','u');
$tocut=array('á','é','í','ó','ú');
$rupdate="";

$location=str_replace($repdevi,'',$_SERVER['QUERY_STRING']);
$location=str_replace($redvi,'',$location);
$location=str_replace($rupdate,'',$location);


$smarty = new vtigerCRM_Smarty();
$smarty->assign('CUSTOM_MODULE', $focus->IsCustomModule);

$smarty->assign('MAX_RECORDS', $list_max_entries_per_page);
$smarty->assign('MOD', $mod_strings);
$smarty->assign('APP', $app_strings);
$smarty->assign('MODULE', $currentModule);
$smarty->assign('SINGLE_MOD', getTranslatedString('SINGLE_'.$currentModule));
$smarty->assign('CATEGORY', $category);
$smarty->assign('BUTTONS', $list_buttons);
$smarty->assign('CHECK', $tool_buttons);
$smarty->assign('THEME', $theme);
$smarty->assign('IMAGE_PATH', "themes/$theme/images/");

if(isset($_REQUEST['record'])){
	
	$smarty->assign('RECORD', $_REQUEST['record']);

	$sql="select a.*,b.tablabel from vtiger_videos a 
				left join vtiger_tab b on b.tabid=a.tabid 
				where idvideo=".$_REQUEST['record']."
				order by b.tablabel,a.idvideo";
	$q=$adb->query($sql);
	$video=$adb->fetchByAssoc($q);
	$video['ext']=getExtension($video['file']);
}
$smarty->assign('VIDEO', $video);

$sql="select a.*,b.tablabel from vtiger_videos a 
			left join vtiger_tab b on b.tabid=a.tabid
			order by b.tablabel,a.idvideo";
$q=$adb->query($sql);
while($r=$adb->fetchByAssoc($q)){
	$r['ext']=getExtension($r['file']);
	$videos[]=$r;
}

$smarty->assign('VIDEOS', $videos);

$sql="select * from vtiger_tab where presence=0 order by name";
$q=$adb->query($sql);
while($r=$adb->fetchByAssoc($q)){
	$modulos[]=$r;
}
$smarty->assign('MODULOS', $modulos);
$smarty->assign('post_max_size', ini_get("post_max_size"));
$smarty->assign('upload_max_filesize', ini_get("upload_max_filesize"));

$smarty->display('modules/'.$currentModule.'/EditView.tpl');

?>
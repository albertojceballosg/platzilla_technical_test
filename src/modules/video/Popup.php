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

$sql="select a.*,b.tablabel from vtiger_videos a 
			left join vtiger_tab b on b.tabid=a.tabid
			order by b.tablabel,a.idvideo";
$q=$adb->query($sql);
while($r=$adb->fetchByAssoc($q)){
	$breaks=array("\r\n","\r","\n");
	$r['description']=str_ireplace($breaks," ", $r['description']);
	$r['description']=strip_tags($r['description']);
	$r['ext']=getExtension($r['file']);
	$videos[]=$r;
}

$smarty->assign('VIDEOS', $videos);
$smarty->display('modules/'.$currentModule.'/Popup.tpl');
?>


<div align="center">

<?php

/**********
Sql connect
***********/
include('../../include/conexion_time.php');
$serverPath=$_SERVER['SERVER_NAME'].str_replace("index.php","",$_SERVER['PHP_SELF']);
//This function reads the extension of the file to ensure that it is an video file
function getExtension($str) {
	$i = strrpos($str,".");
	if (!$i) { return ""; }
	$l = strlen($str) - $i;
	$ext = substr($str,$i+1,$l);
	return $ext;
}



$torep=array('a','e','i','o','u');
$tocut=array('á','é','í','ó','ú');
//echo $narch='Auditoría Programada PT.flv';
//echo "<br />";
//$nnarch=str_replace($tocut,$torep,$narch);
//echo $nnarch;
//exit();
$rupdate="";


	
/*		
?>


<div id="ListViewContents" align="center" style="width:90%">

<table border="0" cellspacing="0" cellpadding="5" width="100%" class="tableHeading">
<tbody>
<tr>
	<td class="big"><strong>Video Lists</strong></td>
	<td class="small" align="right">&nbsp;</td>
</tr>
</tbody>
</table>
</form>

<br />
	<table border="0" cellpadding="5" width="100%" class="listTable">
    	<tr>
        	<td class="colHeader small">#</td>
            <td class="colHeader small">Video</td>
            <td class="colHeader small">Description</td>
            <td class="colHeader small">Module</td>
            <td class="colHeader small" align="center">Play</td>
            <td class="colHeader small" align="center">Enlace</td>
        </tr>
<?php
	$sql="select a.*,b.tablabel from vtiger_videos a 
			left join vtiger_tab b on b.tabid=a.tabid
			order by b.tablabel,a.idvideo";
	$q=mysql_query($sql);
	$i=1;
	while($r=mysql_fetch_array($q)){
		$breaks=array("\r\n","\r","\n");
		$r['description']=str_ireplace($breaks," ", $r['description']);
		$r['description']=strip_tags($r['description']);
?>
        <tr>
        	<td class="listTableRow small"><?=$i?></td>
            <td class="listTableRow small"><a href="javascript:void(0)" onclick="setOpenervalue('<?=$r['description']?$r['description']:$r['file']?>','<?=$r['idvideo']?>')"><?=$r['file']?></a></td>
            <td class="listTableRow small"><?=$r['description']?></td>
            <td class="listTableRow small"><?=$r['tablabel']?></td>
            <td class="listTableRow small" align="center"><a href="javascript:void(0)" onclick="openUVideo('<?=$r['idvideo']?>')"><img src="themes/images/next.gif" /></a></td>
            <td class="listTableRow small" align="left">http://<?=$serverPath?>index.php?module=video&action=play&idv=<?=$r['idvideo']?></td>
        </tr>
<?php
		$i++;
	}
?>
    </table>
</div>

<script type="text/javascript" language="javascript">
function setOpenervalue(desc,idvideo){
	window.opener.document.EditView.videoid_display.value=desc;
	window.opener.document.EditView.videoid.value=idvideo;
	window.close();
}
function openUVideo(idv){
	var left = (screen.width/2)-(650/2);
	var top = (screen.height/2)-(400/2);
	//window.open('https://<?=$serverPath?>modules/video/player/index.php?idv='+idv,'Video','width=650,height=400,top='+top+',left='+left);
	window.open('http://<?=$serverPath?>index.php?module=video&action=play&idv='+idv,'Video','width=650,height=400,top='+top+',left='+left);
}


</script>
*/
?>
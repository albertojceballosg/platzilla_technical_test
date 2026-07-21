<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the 
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Accounts/Save.php,v 1.7 2005/03/15 09:55:31 shaw Exp $
 * Description:  Saves an Account record and then redirects the browser to the 
 * defined return URL.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

global $mod_strings,$current_user,$adb;

function getExtension($str) {
	$i = strrpos($str,".");
	if (!$i) { return ""; }
	$l = strlen($str) - $i;
	$ext = substr($str,$i+1,$l);
	return $ext;
}
// die(json_encode(array('status'=>'fail')));
define ("MAX_SIZE","1000000"); //100MB aprox

if(!empty($_FILES)){
	$extensions=array('mpeg','mpg','flv','mov','wmv','mp3','mp4',);
	$video_filename = str_replace($tocut,$torep,stripslashes(utf8_decode($_FILES['file']['name'])));
	$video_extension = getExtension($video_filename);
	$video_extension = strtolower($video_extension);
	if (!in_array($video_extension,$extensions)){
		die('Unknown extension::');
		$errors=1;
	}else{
		$size=filesize($_FILES['file']['tmp_name']);
		if ($size > MAX_SIZE*1024){
			die('exceeded the size limit::');
			$errors=1;
		}
		//asigna un nombre unico al video
		$video_name=$video_filename;
		$newname="storage/video_uploads/".$video_name;
		$copied = copy(utf8_decode($_FILES['file']['tmp_name']), $newname);
		//echo $copied;
		if (!$copied){
			echo '<h1>Copy unsuccessful!</h1>';
			$errors=1;
		}
		$sql="insert into vtiger_videos (tabid,file,description) 
					values ('','".$video_name."','')";
		$q=$adb->query($sql);
		die('success::'.$adb->getLastInsertID());
	}
	exit;
}elseif(isset($_REQUEST['titulo']) && ((isset($_REQUEST['imgid']) && $_REQUEST['imgid']!=0) || isset($_REQUEST['record']))){
	
	existeCampoTabla('titulo','vtiger_videos','ALTER TABLE `vtiger_videos` ADD COLUMN `titulo` VARCHAR(250) NULL;');
	
	$titulo=vtlib_purify($_REQUEST['titulo']);
	$description=vtlib_purify($_REQUEST['description']);
	$idvideo=$_REQUEST['imgid']?$_REQUEST['imgid']:$_REQUEST['record'];
	$sql="update vtiger_videos set 
					titulo='".$titulo."',
					description='".$description."'
					where idvideo=".$idvideo;
	$adb->query($sql);

	header("Location: index.php?action=index&module=video");
}elseif($_REQUEST['imgid']==0){
	header("Location: index.php?module=video&action=EditView&return_action=DetailView&er=1");
}

exit;
?>
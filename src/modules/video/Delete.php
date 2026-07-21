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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/modules/Accounts/Delete.php,v 1.5 2005/03/10 09:28:34 shaw Exp $
 * Description:  Deletes an Account record and then redirects the browser to the 
 * defined return URL.
 ********************************************************************************/

global $currentModule,$adb;

if(!isset($_REQUEST['record'])){
	$response=json_encode(array('fail'=>$mod_strings['ERR_DELETE_RECORD']));
	die($response);
}

$sql="select file from vtiger_videos where idvideo=".$_REQUEST['record'];
$q=$adb->query($sql);
$r=$adb->fetchByAssoc($q);
$file=$r['file'];
if(file_exists("storage/video_uploads/".$file)){
	unlink("storage/video_uploads/".$file);
}
$sql="delete from vtiger_videos where idvideo=".$_REQUEST['record'];
$q=$adb->query($sql);

$response=json_encode(array('success'=>'Registro eliminado','record'=>$_REQUEST['record']));
die($response);
?>
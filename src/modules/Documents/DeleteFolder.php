<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
require_once('modules/Documents/Documents.php');
require_once('modules/Documents/ComunesDocuments.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');

global $adb;
global $current_user;

$process = 0;


if(isset($_REQUEST['folderid']) && $_REQUEST['folderid']!= '' && $_REQUEST['folderid'] > 0){


	$folderId = $_REQUEST['folderid'];


	if($current_user->is_admin != 'on'){

		$queryPermission = "select * from vtiger_attachmentsfolder where (folderid=? and folderid != 1) and createdby ='".$current_user->id."'";
		$resultPermission = $adb->pquery($queryPermission,array($folderId));

		if($adb->num_rows($resultPermission) <= 0){

				$permissionFolder = getPermissionFolderbyProfile(fetchUserProfileId($current_user->id),$folderId);

			if($permissionFolder['delete_act'] != 1){

				echo 'NOT_PERMITTED';
				die;

			}else{

				$process = 1;

			}


		}else{

			$process = 1;


		}

	}else if($current_user->is_admin == 'on'){


		$process = 1;
	}


	if($process == 1){

		$query = "select notesid from vtiger_notes INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_notes.notesid WHERE vtiger_notes.folderid = ? and vtiger_crmentity.deleted = 0";
			$result = $adb->pquery($query,array($folderId));
			
			if($adb->num_rows($result) > 0)
			{
				echo 'FAILURE_FILE';
				die;
			
			}else{

				$sql="delete from vtiger_attachmentsfolder where (folderid=? and folderid != 1)";
				$adb->pquery($sql,array($folderId));

				//header("Location: index.php?action=DocumentsAjax&file=ListView&mode=ajax&module=Documents");
				//exit;
				
				echo 'SUCCESS';
				die;

			}



	}
	

}else{

	echo 'FAILURE';
	die;

}




?>
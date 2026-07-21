<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

require_once('include/utils/utils.php');
require_once('include/logging.php');
global $log;
global $current_user, $upload_badext;
$vtigerpath = $_SERVER['REQUEST_URI'];
$vtigerpath = str_replace("/index.php?module=uploads&action=add2db", "", $vtigerpath);

$crmid = $_REQUEST['return_id'];
$log->debug("DEBUG In add2db.php");

	if(isset($_REQUEST['filename_hidden'])) {
		$file = $_REQUEST['filename_hidden'];
	} else {
		$file = $_FILES['filename']['name'];
	}
	$binFile = sanitizeUploadFileName($file, $upload_badext);
	$_FILES["filename"]["name"] = $binFile;

	//decide the file path where we should upload the file in the server
	$upload_filepath = decideFilePath();

	$current_id = $adb->getUniqueID("vtiger_crmentity");

	if(move_uploaded_file($_FILES["filename"]["tmp_name"],$upload_filepath.$current_id."_".$_FILES["filename"]["name"]))
	{
		$filename = ltrim(basename(" ".$binFile)); //allowed filename like UTF-8 characters
		$filetype= $_FILES['filename']['type'];
		$filesize = $_FILES['filename']['size'];

		if($filesize != 0)
		{
			$desc = $_REQUEST['txtDescription'];
			$subject = $_REQUEST['uploadsubject'];
			$date_var = $adb->formatDate(date('Y-m-d H:i:s'), true);
			$current_date = getdate();
			$current_date = $adb->formatDate(date('Y-m-d H:i:s'), true);
			$query = "insert into vtiger_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?,?,?,?,?,?,?)";
			$params = array($current_id, $current_user->id, $current_user->id, $_REQUEST['return_module'].' Attachment', $desc, $date_var, $current_date);
			$result = $adb->pquery($query, $params);

			# Added by DG 26 Oct 2005
			# Attachments added to contacts are also added to their accounts
			$log->debug("DEBUG return_module: ".$_REQUEST['return_module']);
			$sql = "insert into vtiger_attachments(attachmentsid, name, description, type,path,subject) values(?,?,?,?,?,?)";
			$params = array($current_id, $filename, $desc, $filetype, $upload_filepath, $subject);
			$result = $adb->pquery($sql, $params);


			$sql1 = "insert into vtiger_seattachmentsrel values(?,?)";
			$params1 = array($crmid, $current_id);
			$result = $adb->pquery($sql1, $params1);
			echo '<script>window.opener.location.href = window.opener.location.href;self.close();</script>';
		}
		else
		{
			$errormessage = "<font color='red'><B>Error Message<ul>
				<li><font color='red'>Invalid file OR</font>
				<li><font color='red'>File has no data</font>
				</ul></B></font> <br>" ;
			header("Location: index.php?module=uploads&action=uploadsAjax&msg=true&file=upload&errormessage=".$errormessage);
		}
	}
	else
	{
		$errorCode =  $_FILES['binFile']['error'];
		$errormessage = "";

		if($errorCode == 4)
		{
			$errormessage = "<B><font color='red'>Kindly give a valid file for upload!</font></B> <br>" ;
		}
		else if($errorCode == 2)
		{
			$errormessage = "<B><font color='red'>Sorry, the uploaded file exceeds the maximum filesize limit. Please try a file smaller than $upload_maxsize bytes</font></B> <br>";
		}
		else if($errorCode == 6)
		{
			$errormessage = "<B>Please configure <font color='red'>upload_tmp_dir</font> variable in php.ini file.</B> <br>" ;
		}
		else if($errorCode == 3 || $errorcode == '')
		{
			$errormessage = "<b><font color='red'>Problems in file upload. Please try again!</font></b><br>";
		}

		if($errormessage != '')
		{
			echo $errormessage;
			include("upload.php");
		}
	}

?>
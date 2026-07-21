<?php
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/
session_start();
/** Function to  return a string with backslashes stripped off
 * @param $value -- value:: Type string
 * @returns $value -- value:: Type string array
 */
include_once('include/utils/utils.php');

function stripslashes_checkstrings($value){
	if(is_string($value)){
		return stripslashes($value);
	}
	return $value;
}

if(get_magic_quotes_gpc() == 1){
	$_REQUEST = array_map("stripslashes_checkstrings", $_REQUEST);
	$_POST = array_map("stripslashes_checkstrings", $_POST);
	$_GET = array_map("stripslashes_checkstrings", $_GET);
}

include("include.php");
include("version.php");
global $default_language;
setPortalCurrentLanguage();
$default_language = getPortalCurrentLanguage();
require_once("language/".$default_language.".lang.php");


if($_REQUEST['param'] == 'forgot_password')
{
	global $client;
	global $default_charset, $default_language;
	$default_language = getPortalCurrentLanguage();
	include("language/$default_language.lang.php");

	$email = $_REQUEST['email_id'];
	
	$params = array(Array(
		'email' => "$email",
		'LBL_LOGIN_DETAILS_BEGIN'=>$mod_strings['LBL_LOGIN_DETAILS_BEGIN'],
		'LBL_LOGIN_DETAILS_END'=>$mod_strings['LBL_LOGIN_DETAILS_END'],
		'LBL_USERNAME'=>$mod_strings['LBL_USERNAME'],
		'LBL_PASSWORD'=>$mod_strings['LBL_PASSWORD'],
		'LBL_SUBJECT_PORTAL_LOGIN_DETAILS'=>$mod_strings['LBL_SUBJECT_PORTAL_LOGIN_DETAILS'],
		'LBL_ALTBODY_BEGIN'=>$mod_strings['LBL_ALTBODY_BEGIN'],
		'LBL_ALTBODY_END'=>$mod_strings['LBL_ALTBODY_END'],
		'LBL_GIVE_MAILID'=>$mod_strings['LBL_GIVE_MAILID'],
		'LBL_CHECK_MAILID'=>$mod_strings['LBL_CHECK_MAILID'],
		'LBL_LOGIN_REVOKED'=>$mod_strings['LBL_LOGIN_REVOKED'],
		'LBL_MAIL_COULDNOT_SENT'=>$mod_strings['LBL_MAIL_COULDNOT_SENT'],
		'LBL_MAIL_SENT'=>$mod_strings['LBL_MAIL_SENT'],
	));
	// $params = Array(Array('id' => "$customerid", 'sessionid'=>"$sessionid", 'flag'=>"logout"));
	$result = $client->call('update_login_details', $params);
	
	$result = $client->call('send_mail_for_password', $params);
	$_REQUEST['mail_send_message'] = $result;
	require_once("supportpage.php");
}
elseif($_REQUEST['logout'] == 'true')
{
	$customerid = $_SESSION['customer_id'];
	$sessionid = $_SESSION['customer_sessionid'];

	$params = Array(Array('id' => "$customerid", 'sessionid'=>"$sessionid", 'flag'=>"logout"));
	$result = $client->call('update_login_details', $params);

	session_unregister('customer_id');
	session_unregister('customer_name');
	session_unregister('last_login');
	session_unregister('support_start_date');
	session_unregister('support_end_date');
	session_unregister('__permitted_modules');
	session_unregister('customer_account_id');
	session_destroy();
	include("login.php"); //Desactivamos que al desloguear se dirija a login.php para que redirecciones a la pagina de time
	// header("Location: http://www.timemanagement.es");
}
else
{
	$module = '';
	$action = 'login.php';
	$isAjax = ($_REQUEST['ajax'] == 'true');

	if($_SESSION['customer_id'] != '')
	{
		$customerid = $_SESSION['customer_id'];
		$sessionid = $_SESSION['customer_sessionid'];

		// Set customer account id
		if(isset($_SESSION['customer_account_id'])) {
			$account_id = $_SESSION['customer_account_id'];
		} else {
			$params = Array('id'=>$customerid);
			$account_id = $client->call('get_check_account_id', $params, $Server_Path, $Server_Path);
			$_SESSION['customer_account_id'] = $account_id;
		}
		// End
		$is_logged = 1;

		//Added to download attachments


		if($_REQUEST['downloadfile'] == 'true')
		{
			$filename = $_REQUEST['filename'];
			$fileType = $_REQUEST['filetype'];
			//$fileid = $_REQUEST['fileid'];
			$filesize = $_REQUEST['filesize'];

			//Added for enhancement from Rosa Weber

			if($_REQUEST['module'] == 'Invoice' || $_REQUEST['module'] == 'Quotes')
			{
				$id=$_REQUEST['id'];
				$block = $_REQUEST['module'];

				$params = array('id' => "$id", 'block'=>"$block", 'contactid'=>"$customerid",'sessionid'=>"$sessionid");
				$fileContent = $client->call('get_pdf', $params, $Server_Path, $Server_Path);
				$fileType ='application/pdf';
				$fileContent = $fileContent[0];
				$filesize = strlen(base64_decode($fileContent));
				$filename = "$block.pdf";
			}
			else if($_REQUEST['module'] == 'Documents')
			{
				$id=$_REQUEST['id'];
				$folderid = $_REQUEST['folderid'];
				$block = $_REQUEST['module'];
				$params = array('id' => "$id", 'folderid'=> "$folderid",'block'=>"$block", 'contactid'=>"$customerid",'sessionid'=>"$sessionid");
				$result = $client->call('get_filecontent_detail', $params, $Server_Path, $Server_Path);
				$fileType=$result[0]['filetype'];
				$filesize=$result[0]['filesize'];
				$filename=html_entity_decode($result[0]['filename']);
				$fileContent=$result[0]['filecontents'];
			}
			else  {
				$ticketid = $_REQUEST['ticketid'];
				$fileid = $_REQUEST['fileid'];
				//we have to get the content by passing the customerid, fileid and filename
				$customerid = $_SESSION['customer_id'];
				$sessionid = $_SESSION['customer_sessionid'];
				$params = array(Array('id'=>$customerid,'fileid'=>$fileid,'filename'=>$filename,'sessionid'=>$sessionid,'ticketid'=>$ticketid));
				$fileContent = $client->call('get_filecontent', $params, $Server_Path, $Server_Path);
				$fileContent = $fileContent[0];
				$filesize = strlen(base64_decode($fileContent));
			}
			// : End

			//we have to get the content by passing the customerid, fileid and filename
			$customerid = $_SESSION['customer_id'];
			$sessionid = $_SESSION['customer_sessionid'];

			header("Content-type: $fileType");
			header("Content-length: $filesize");
			header("Cache-Control: private");
			header("Content-Disposition: attachment; filename=$filename");
			header("Content-Description: PHP Generated Data");
			echo base64_decode($fileContent);
			exit;
		}
		if($_REQUEST['module'] != '' && $_REQUEST['action'] != '')
		{
			$customerid = $_SESSION['customer_id'];

			$permission = array();
			// Look if we have the information already
			if(isset($_SESSION['__permitted_modules'])) {
				$permission = $_SESSION['__permitted_modules'];
			} else {
				// Get the information from server
				$params = array($customerid);
				$permission = $client->call('get_modules',$params,$Server_path,$Server_path);
				// Store for futher re-use
				$_SESSION['__permitted_modules'] = $permission;
			}
			$isPermitted = false;
			for($i=0;$i<count($permission);$i++){
				if($permission[$i] == $_REQUEST['module']) {
					$isPermitted = true;
					break;
				}
			}
			if($isPermitted == true) {
				$module = $_REQUEST['module']."/";
				$action = $_REQUEST['action'].".php";
			}
			if($isPermitted == false || ($module == '' && $action == '')){

				echo '#NOT AUTHORISED#';
				exit();
			}


		}
		elseif($_REQUEST['action'] != '' && $_REQUEST['module'] == '')
		{
			$action = $_REQUEST['action'].".php";
		}
		elseif($_SESSION['customer_id'] != '')
		{
			$permission = array();
			// Look if we have the information already
			if(isset($_SESSION['__permitted_modules'])) {
				$permission = $_SESSION['__permitted_modules'];
			} else {
				// Get the information from server
				$params = array();
				$permission = $client->call('get_modules',$params,$Server_path,$Server_path);
				// Store for futher re-use
				$_SESSION['__permitted_modules'] = $permission;
			}
			$module = $permission[0];
			$action = "index.php";
		}
	}
	$filename = $module.$action;

	if($is_logged == 1)
	{
		include("HelpDesk/Utils.php");
		global $default_charset, $default_language;
		$default_language = getPortalCurrentLanguage();
		include("language/$default_language.lang.php");
		header('Content-Type: text/html; charset='.$default_charset);

		if(!$isAjax) {
			include("header.html");
		}

		// Hide non-permitted tabs if not Ajax Request
		if(!$isAjax) {
			
			echo '<script type="text/javascript">';
			
			// Look if we have the information already
			$tabArray = array();
			if(isset($_SESSION['__permitted_modules'])) {
				$tabArray = $_SESSION['__permitted_modules'];
			} else {
				// Get the information from server
				$params = array();
				$tabArray = $client->call('get_modules',$params,$Server_path,$Server_path);
				// Store for futher re-use
				$_SESSION['__permitted_modules'] = $tabArray;
			}
				
			$module = $_REQUEST['module'];
			foreach($tabArray as $key => $tabName) {
				if(strcmp(rtrim($module,"/"),$tabName) == 0) {
		?>
				document.getElementById("<?php echo $tabName;?>").className = "dvtSelectedCell";
		<?php
				}
				else {
		?>
				document.getElementById("<?php echo $tabName;?>").className = "dvtUnSelectedCell";
		<?php
				}
			}
			echo '</script>';
		}
		?>

		<?php
		if(is_file($filename)) {
			checkFileAccess($filename);
			include($filename);
		} else if($_SESSION['customer_id'] != ''){
			$permission = array();
			// Look if we have the information already
			if(isset($_SESSION['__permitted_modules'])) {
				$permission = $_SESSION['__permitted_modules'];
				// Store for further re-use
				$_SESSION['__permitted_modules'] = $permission;
			} else {
				// Get the information from server
				$params = array();
				$permission = $client->call('get_modules',$params,$Server_path,$Server_path);
			}
			$module = $permission[0];

			checkFileAccess("$module/index.php");
			include("$module/index.php");
		}
		if(!$isAjax) {
			include("footer.html");
		}
	}
	else {
		header("Location: login.php?module=".$_REQUEST['module']."&action=".$_REQUEST['action']."&recordid=".$_REQUEST['recordid']."&ticketid=".$_REQUEST['ticketid']."&fun=".$_REQUEST['fun']);
	}

}

?>
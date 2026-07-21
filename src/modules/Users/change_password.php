<?php
/*+********************************************************************************
  * The contents of this file are subject to the vtiger CRM Public License Version 1.0
  * ("License"); You may not use this file except in compliance with the License
  * The Original Code is:  vtiger CRM Open Source
  * The Initial Developer of the Original Code is vtiger.
  * Portions created by vtiger are Copyright (C) vtiger.
  * All Rights Reserved.
  *********************************************************************************/

//we don't want the parent module's string file, but rather the string file specifc to this subpanel
global $current_language;

$current_module_strings = return_module_language($current_language, 'Users');
$pass1=$_REQUEST['passUser'];
$pass2=$_REQUEST['passUserConfirm'];
$emailUser=$_REQUEST['emailUser'];
$DEFAULT_PASSWORD_CRYPT_TYPE = (version_compare(PHP_VERSION, '5.3.0') >= 0)?
                'PHP5.3MD5': 'MD5';


include_once('vtlib/Vtiger/Language.php');
require_once('modules/Users/Users.php');
include_once("include/phpmailer/class.phpmailer.php");

// Retrieve username and password from the session if possible.
$focus = new Users();



$current_module_strings['VLD_ERROR'] = base64_decode('UGxlYXNlIHJlcGxhY2UgdGhlIFN1Z2FyQ1JNIGxvZ29zLg==');

// Retrieve username and password from the session if possible.


require_once('Smarty_setup.php');
require_once("data/Tracker.php");
require_once("include/utils/utils.php");
require_once 'vtigerversion.php';

global $currentModule, $moduleList, $adb, $vtiger_current_version,$site_URL;
$image_path="include/images/";

$app_strings = return_application_language('es_es');

$smarty=new vtigerCRM_Smarty;
$smarty->assign("APP", $app_strings);

if(isset($app_strings['LBL_CHARSET'])) {
	$smarty->assign("LBL_CHARSET", $app_strings['LBL_CHARSET']);
} else {
	$smarty->assign("LBL_CHARSET", $default_charset);
}

$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("PRINT_URL", "phprint.php?jt=".session_id().$GLOBALS['request_string']);
$smarty->assign("VTIGER_VERSION", $vtiger_current_version);

if($pass1==$pass2){
	echo $emailUser;
	echo $pass1;
$fo=$focus->change_password1($emailUser,$pass1);
if($fo){
	echo "ejecuto la consulta";
}
}








$smarty->display('modal_password.tpl');


?>
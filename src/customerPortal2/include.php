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
@include_once 'config.inc.php';
require_once("PortalConfig.php");
require_once('nusoap/lib/nusoap.php');

global $Server_Path;
global $client;
global $conex;

$_SESSION['plat'] = 'time';
$client = new soapclient2($Server_Path."/vtigerservice.php?service=customerportal&plat_customer=time", false, $proxy_host, $proxy_port, $proxy_username, $proxy_password);

//We have to overwrite the character set which was set in nusoap/lib/nusoap.php file (line 87)
$client->soap_defencoding = $default_charset;

//Se crea una conexion de bd del customer
if (isset($_REQUEST['plat_customer']))
	$_SESSION['plat'] = $_REQUEST['plat_customer'];
if (isset($_SESSION['plat'])) {
	$dbconfig['db_name'] = 'pg_crm_'.$_SESSION['plat'];
	$dbconfig['db_username'] = 'usr_'.$_SESSION['plat'];
	$dbconfig['db_password'] = md5('usr_'.$_SESSION['plat']);
}
$conex = mysql_connect($dbconfig['db_server'].':'.$dbconfig['db_port'],$dbconfig['db_username'],$dbconfig['db_password'],true);
mysql_select_db($dbconfig['db_name'],$conex);

?>
<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
global $currentModule;

$plat = '';
if (isset($_SESSION['plat']))
	$plat = $_SESSION['plat'].'/';

// checkFileAccessForInclusion($plat."modules/$currentModule/ListView.php");
// include_once($plat."modules/$currentModule/ListView.php");


checkFileAccessForInclusion("modules/$currentModule/ListView.php");
include_once("modules/$currentModule/ListView.php");

?>

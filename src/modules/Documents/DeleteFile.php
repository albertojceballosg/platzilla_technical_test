<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
 
global $adb;
global $current_user;

$sql1 = 'SELECT attachmentsid FROM vtiger_attachments WHERE attachmentsid = ?';
$res1 = $adb->pquery($sql1, array($_REQUEST['recordid']));
if ($adb->num_rows($res1) > 0) {
	
	$sql4 = 'DELETE FROM vtiger_attachments WHERE attachmentsid=?';
	$adb->pquery($sql4, array($_REQUEST['recordid']));
}
echo "SUCCESS";

?>
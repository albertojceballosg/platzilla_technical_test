<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

require_once('modules/Users/Users.php');
require_once('include/QueryGenerator/QueryGenerator.php');
require_once('include/utils/utils.php');

$idlist = vtlib_purify($_REQUEST['idlist']);
$viewid = vtlib_purify($_REQUEST['viewname']);
$module = vtlib_purify($_REQUEST['module']);
$related_module = vtlib_purify($_REQUEST['related_module']);

global $adb;

if(vtlib_purify($_REQUEST['mode'])!='relatedlist') {
	$result = getSelectAllQuery($_REQUEST,$module);
}
$numRows = $adb->num_rows($result);
echo $numRows;
?>

<?php

global $currentModule, $adb;

$record = $_REQUEST['record'];

$queryDelete = "DELETE FROM vtiger_notifymanager WHERE notifyid = ?";
$adb->pquery($queryDelete,array($record));

header("Location: index.php?module=$currentModule&action=index");

?>
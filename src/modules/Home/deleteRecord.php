<?php

require_once ('include/utils/CommonUtils.php');
require_once ('include/utils/VtlibUtils.php');
require_once ('include/database/PearDatabase.php');

global $currentModule;

$id  = isset ($_REQUEST ['id']) ? vtlib_purify ($_POST['id']) : null;

$db  = PearDatabase::getInstance();
$sql = $db->pquery('UPDATE vtiger_audit_trial SET action = "delete" WHERE recordid = ?', array($id));

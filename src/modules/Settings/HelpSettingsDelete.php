<?php

require_once('data/CRMEntity.php');
global $adb;

$record = $_REQUEST['record'];

$sql = 'DELETE FROM vtiger_ayudaconf WHERE id_ayuda = ?';
$params = array($record);

if ($adb->pquery($sql, $params)) {
	$sqlPlatziTipsDelete = 'DELETE FROM vtiger_ayudaconf_platzitips WHERE id_ayuda = ?';
	$adb->pquery($sqlPlatziTipsDelete, array($record));

	$sqlTutorialesDelete = 'DELETE FROM vtiger_ayudaconf_tutoriales WHERE id_ayuda = ?';
	$adb->pquery($sqlTutorialesDelete, array($record));

	$sqlQuestionsDelete = 'DELETE FROM vtiger_ayudaconf_preguntasf WHERE id_ayuda = ?';
	$adb->pquery($sqlQuestionsDelete, array($record));

	header('Location: index.php?module=Settings&action=HelpSettingsListView');
}

?>

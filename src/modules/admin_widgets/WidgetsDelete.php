<?php
require_once('data/CRMEntity.php');
global $mod_strings,$app_strings,$app_list_strings,$theme,$adb,$log, $current_user;

$record = vtlib_purify($_REQUEST['record']);

$sql = 'DELETE FROM vtiger_widgets WHERE widgetid = ?';
$params = array($record);

if ($adb->pquery($sql, $params)) {
	header('Location: index.php?module=admin_widgets&action=index');
} else {
	$_SESSION['error_borrado'] = 'El widget no ha podido ser eliminado! Intente nuevamente!';
	header('Location: index.php?module=admin_widgets&action=DetailWidgets&record='.$record);
}

?>

<?php
global $adb;

$sql = "SELECT template FROM vtiger_proyectos INNER JOIN vtiger_crmentity ON (proyectosid = crmid AND deleted = 0) WHERE proyectosid = ?";

$proyectosid = vtlib_purify($_REQUEST['proyectosid']);

$result = $adb->pquery($sql,array($proyectosid));

if ($result && $adb->num_rows($result) == 1)
	echo $adb->query_result($result,0,'template');
else
	echo 0;
die();

?>

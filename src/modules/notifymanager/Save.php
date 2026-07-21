<?php

global $currentModule, $adb;

$textoNotificacion 	= $_REQUEST['textoNotificacionTextarea'];
$title 				= $_REQUEST['title'];
$description 		= $_REQUEST['description'];
$modulo 			= $_REQUEST['modulo'];
$view 				= $_REQUEST['view'];
$active 			= $_REQUEST['active'];
$mode 				= $_REQUEST['mode'];

$modulo = implode('#', $modulo);


if ($mode == "Creacion"){

	$querySave = "INSERT INTO vtiger_notifymanager (title, description,design,module,action,active) VALUES (?,?,?,?,?,?)";
	$adb->pquery($querySave,array($title,$description,$textoNotificacion,$modulo,$view,$active));

	$notificacionId = $adb->getLastInsertID();

	header("Location: index.php?module=$currentModule&action=index");

}else{

	$record = $_REQUEST['record'];

	$queryUpdate = "UPDATE vtiger_notifymanager SET title =? ,description =? ,design =?,module =?,action =?,active =? WHERE notifyid = ?";
	$adb->pquery($queryUpdate,array($title,$description,$textoNotificacion,$modulo,$view,$active,$record));

	header("Location: index.php?module=$currentModule&action=DetalleNotificacion&record=$record");

}



?>
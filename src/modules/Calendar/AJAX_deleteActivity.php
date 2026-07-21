<?php
include_once('include/utils/utils.php');
include_once('include/utils/comunesTareas.php');
	
	
global $conex;
global $adb;

function deleteActivity($activityid){

	global $adb;

	$sql = "SELECT crmid FROM vtiger_seactivityrel WHERE activityid = ?";

	$result = $adb->pquery($sql, array($activityid));

	if ($result) {
		$row = $adb->fetchByAssoc($result);
		if ($row) {
			$sql = "UPDATE vtiger_crmentity SET deleted = 1 WHERE crmid = ?";

			$adb->pquery($sql,array($row['crmid']));
		}
	}

	$sql = "UPDATE vtiger_crmentity SET deleted = 1 WHERE crmid = ?";
	$result = $adb->pquery($sql, array($activityid));
	return true;
}



$return['success']=false;
$return['error'] = 'Error desconocido';
$tipo = tipoUsuario($_SESSION["authenticated_user_id"]);
if (!empty($_REQUEST['t']) and ($tipo == 'H2' or $tipo == 'H26')){
	$activityid = (int)$_REQUEST['t'];
	$return['t'] = $activityid;
	$booleano = deleteActivity($activityid);
	if ($booleano == true){
		$return['success']=true;
		$return['error'] = '';
	} else {
		$return['error'] = 'No se pudo eliminar la actividad';
	}
} else {
	$return['error'] = 'No tiene permisos para eliminar esta actividad (tipo: ' . $tipo . ')';
}
echo json_encode($return);
die();
?>
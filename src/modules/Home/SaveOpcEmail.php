<?php
	require_once('Smarty_setup.php');
	$smarty = new vtigerCRM_Smarty;

	/* [ TT11375 ] Notificaciones para “Mi Cuenta en Platzilla” - Pedidos Información Johana Romero 11/10/2016 */
	global $adb, $platPrincipal;
	$opciones = explode(",",$_REQUEST['opciones']);
	$opciones_no = explode(",",$_REQUEST['notCheck']);
	/*$sql = "SELECT DATABASE() as db";
	$result = $adb->pquery($sql,array());
	echo "la bd es ".$adb->query_result($result,0,'db')."<br>";*/

	$adbPrincipal = conectaPlataformaHija($platPrincipal);
	if (!empty ($_SESSION ['platInstancia'])){
		if($opciones[0] != ''){
			$sql_delete = "DELETE FROM vtiger_emanager_events2instance WHERE instancecode = ?";
			$adbPrincipal->pquery($sql_delete,array($_SESSION['plat']));

			foreach ($opciones as $value) {
				$sql_insert = "INSERT INTO vtiger_emanager_events2instance(eventid, instancecode) VALUES (?,?)";
				$adbPrincipal->pquery($sql_insert,array($value,$_SESSION['plat']));
			}
		}else{
			$sql_delete = "DELETE FROM vtiger_emanager_events2instance WHERE instancecode = ?";
			$adbPrincipal->pquery($sql_delete,array($_SESSION['plat']));
		}
	}else{
		$sql_update = "UPDATE vtiger_emailmanager_events SET pordefecto = ? WHERE eventid = ?";

		foreach ($opciones as $value) {
			$adbPrincipal->pquery($sql_update,array(1,$value));

			$sql_exists = "SELECT IF( EXISTS(
						             SELECT *
						             FROM vtiger_emanager_events2instance
						             WHERE eventid =  ?), 1, 0)";
			$result_exists = $adbPrincipal->pquery($sql_exists,array($value));
			if ($result_exists->fields[0] == '1'){
				$sql_delete = "DELETE FROM vtiger_emanager_events2instance WHERE eventid = ?";
				$adbPrincipal->pquery($sql_delete,array($value));
			}
		}

		foreach ($opciones_no as $value) {
			$adbPrincipal->pquery($sql_update,array(0,$value));
		}

	}

?>

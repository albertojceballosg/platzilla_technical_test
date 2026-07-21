<?php
	require_once('Smarty_setup.php');
	include_once("modules/Calendar/funciones_panel_jefe_desarrollo.php");
	include_once('include/utils/comunesTareas.php');
	
	global $currentModule;

	$userId = $_SESSION['authenticated_user_id'];
	$mesActual = date( 'm');
	$anoActual = date( 'Y');
	$diaActual = date( 'd');
	$hoy = date('Y-m-d');
	$hora= date('H');

	/*
	if($hora>=0 and $hora<5) {
		
	}
	*/
	$bHabil = false;
	while (!$bHabil) {
		$hoy = date( "Y-m-d", strtotime( "-1 day", strtotime( $hoy ) ) );
		
		$dia = date("w",strtotime($hoy));
		
		if ($dia != 0 && $dia != 6 && !esDiaFestivo($hoy))
			$bHabil = true;
	}
	
	//$tipo = tipoUsuario($_SESSION["authenticated_user_id"]);
	$tipo = 'H2';
	if (!($tipo=='H2' or $tipo=='H8')){
		echo '<html><body>No tiene los permisos para ver esta pagina

		<input  class="crmbutton small delete" type="button" value="Cerrar" onClick="window.close()"></body></html>

		';
		exit();
	}
	elseif (!empty($_REQUEST['selectedVendor'])) {
		$vendorId = $_REQUEST['selectedVendor'];
		if (esSubordinadoDe($vendorId,$userId,$tipo) == FALSE){


			echo '<html><body>No tiene los permisos para ver esta Los Datos de este Usuario

			<input  class="crmbutton small delete" type="button" value="Cerrar" onClick="window.close()"></body></html>

			';
			exit();
		}

	}
	
if (isset($_POST['ticketid'])){
	procesarDatosOTs($_POST,$hoy);
}
if (!empty($vendorId)){
	$panelVendor = $vendorId;
}
else{
	$panelVendor = 0;
}
$panelH8 = panelH8($userId,$hoy,$panelVendor,$tipo);

$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);

$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("MODULE", $currentModule);



if (isset($vendorId) && $vendorId != '') {
	$ticketsACerrar = obtenerTicketsACerrar ($vendorId,$hoy);

	if (is_array(($ticketsACerrar))) {
		$datosTicket = array();
		foreach ($ticketsACerrar as $key => $value) {
			$datosTicket[] = obtenerDatosOT($value,$hoy);
			$ticketId = $value;
		} 
	}
	
	$arregloNotas = array();
	for($i=1;$i<=10;$i+=0.5){
		$arregloNotas[] = $i;
	}
	$smarty->assign("VENDORID", $vendorId);
	$smarty->assign("OTS", $datosTicket);
	
	$smarty->assign("NOTAS", $arregloNotas);
	
	
}// Fin del foreach de los tickets 
$sql="select * from vtiger_organizationdetails";
$result = $adb->pquery($sql, array());
$organization_logo = decode_html($adb->query_result($result,0,'logoname'));
$smarty->assign("LOGO",$organization_logo);

$smarty->assign("PANELH8", $panelH8);
$smarty->display('modules/'.$currentModule.'/panel_jefe_desarrollo.tpl');
?>

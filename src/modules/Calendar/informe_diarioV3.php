<?php
require_once('Smarty_setup.php');
include_once("modules/Calendar/funciones_panel_jefe_desarrollo.php");
include_once('include/utils/comunesTareas.php');

$rol=substr($_REQUEST['CRM'],2,-2);


if(!(isset($_SESSION["authenticated_user_id"]) && (isset($_SESSION["app_unique_key"]) ))){
	header("index.php?action=Login&module=Users");
	exit();
}

$criterio = '';

if($rol=="H19" or $rol=="H8" or $rol=="H26" or $rol=="H28" or $rol=="H2" or $rol=="H22") {
	if($rol=="H2"){
		$criterio='';
	}else if($rol=='H28'){ 
		$criterio='-*Se cumple Fecha estimada Finalizaci&oacute;n:\n-*Comentarios del desarrollo:\n';
	}else if($rol=='H8'){ 
		$criterio='-*Detalles T&eacute;cnicos:\n-*Producci&oacute;n[si/no]:\n-*Se cumple Fecha estimada Finalizaci&oacute;n:\n-*Comentarios del desarrollo:\n';
	}else if($rol=='H26'){ 		
		$criterio='-*Detalles T&eacute;cnicos:\n-*Producci&oacute;n[si/no]:\n-*Se cumple Fecha estimada Finalizaci&oacute;n:\n';		
	}else if($rol=='H22'){ 		
	   $criterio='-*Se cumple Fecha estimada Finalizaci&oacute;n:\n-*Comentarios del desarrollo:\n';
	}
}

$datosVendedor = obtenerIDdesarrolladorFromUserID($_SESSION["authenticated_user_id"]);
$vendorId = $datosVendedor['vendorid'];
$vendorName = $datosVendedor['vendorname'];
$ots = obtenerOrdenTrabajoAbiertas($vendorId,'',obtenerValorVariable('TIPO_OT_GESTION','HelpDesk'));
if (obtenerValorVariable('TIPO_OT_GESTION','HelpDesk') != '')
	$ots_coordinacion = obtenerOrdenTrabajoAbiertas($vendorId,obtenerValorVariable('TIPO_OT_GESTION','HelpDesk'),'',count($ots));
$ots_cerradas = obtenerOrdenTrabajoCerradas($vendorId);
$primerLogueo = obtenerHoraPrimerLogueo($vendorId);
$comboHoras = escribeComboHoras(0,true,'onChange="actualizarHorasTrabajadasYHorasRegistradas()"');
$horas = array();
for($i=0;$i<=18;$i+=0.25) {
	$horas[] = number_format($i,2);
}
$accountList = obtenerValoresFiltro('parent_id',true);
$horaActual = date('Y,m,d,H,i,s');


$smarty = new vtigerCRM_Smarty;
$smarty->assign("MOD", $mod_strings);
$smarty->assign("APP", $app_strings);

$smarty->assign("THEME", $theme);
$smarty->assign("IMAGE_PATH", $image_path);
$smarty->assign("MODULE", $currentModule);

$smarty->assign("VENDOR_NAME",$vendorName);
$smarty->assign("VENDORID",$vendorId);
$smarty->assign("COUNT_TICKETS",count($tickets));
$smarty->assign("OTS",$ots);
$smarty->assign("OTS_COORDINACION",$ots_coordinacion);
$smarty->assign("COMBO_HORAS",$comboHoras);
$smarty->assign("HORAS",$horas);
$smarty->assign("PRIMER_LOGUEO",$primerLogueo);
$smarty->assign("HORA_ACTUAL",$horaActual);

$smarty->assign("OTS_CERRADAS",$ots_cerradas);


$smarty->display('modules/'.$currentModule.'/informe_diario.tpl');
die();
?>
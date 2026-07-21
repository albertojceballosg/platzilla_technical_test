<?php

@include_once("PortalConfig.php");
@include_once("include.php");
@include_once("include/utils/utils.php");
global $default_language;

setPortalCurrentLanguage();
$default_language = getPortalCurrentLanguage();
include("language/$default_language.lang.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Zona Clientes, Time Management</title>
<link href="css/style.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="favicon.ico" />
<script language="javascript" type="text/javascript" src="js/prototype.js"></script>
<script language="javascript" type="text/javascript" src="js/general.js"></script>
<script>
function fnMySettings(){

		params = "last_login=support_start_date=2011-06-27&support_end_date=2012-06-27";

		window.open("MySettings.php?"+params,"MySetttings","menubar=no,location=no,resizable=no,scrollbars=no,status=no,width=500,height=350,left=550,top=200");

}
</script>
</head>


<body>

<table  width="100%" border="0" cellspacing="0" cellpadding="0" class="innerTab">

   <tr>

    <th align="left" width="30%"><img src="images/logotm.png"></th>

       </tr>
</table></body></html>

<table width="100%"  height="30" align="center">

<tr><td>&nbsp;</td></tr>

</table>

<table width="100%"  align="center">
<tr ><td align="left" >&nbsp;</td><td style="font-size:18px;">TUTORIALES</td><td>&nbsp;</td></tr>
<tr class="detailedViewHeader"><td align="left" ><b>Videos</b></td><td>&nbsp;</td><td>&nbsp;</td></tr>

<?php
	if ($_REQUEST['module'] == 'HelpDesk') {
?>
<tr><td>&nbsp;</td><td align="left"><a href="ver_video.php?v=Crear_una_Incidencia_o_Peticion_en_Custom_Portal"> -Crear Petici&oacute;n</a></td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td align="left"><a href="ver_video.php?v=Cuadro_de_Incidencias_y_Peticiones_Time"> -Listado de Peticiones</a></td><td>&nbsp;</td></tr>
<?php
	} elseif ($_REQUEST['module'] == 'Invoice') {
?>
<tr><td>&nbsp;</td><td align="left"><a href="ver_video.php?v=Descargar_una_Factura_en_Custom_Portal">- Facturas</a></td><td>&nbsp;</td></tr>
<?php
	} elseif ($_REQUEST['module'] == 'ServiceContracts') {
?>
<tr><td>&nbsp;</td><td align="left"><a href="ver_video.php?v=Contratos_Custom_Portal">- Contratos</a></td><td>&nbsp;</td></tr>
<?php
	} elseif ($_REQUEST['module'] == 'GraficoCustomerPortal') {
?>
<tr><td>&nbsp;</td><td align="left"><a href="ver_video.php?v=Consumo_de_Horas_Custom_Portal">- Consumo hora contrato</a></td><td>&nbsp;</td></tr>
<?php
	}
?>
</table>




{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * Edited by Timemanagement.
   * Developer EV - 2015.05.26
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />

	<title>{$MODULE_NAME|@getTranslatedString:$MODULE_NAME} - {$USER} - {$APP.LBL_BROWSER_TITLE}</title>

	<!-- bootstrap -->
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/bootstrap/bootstrap.min.css" />

	<!-- libraries -->
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/font-awesome.css" />
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nanoscroller.css" />
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/nifty-component.css"  />

	<!-- global styles -->
	<link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/compiled/theme_styles.css" />


	<!-- this page specific styles -->
    <link rel="stylesheet" href="themes/{$THEME}/css/libs/fullcalendar.css" type="text/css" />
    <link rel="stylesheet" href="themes/{$THEME}/css/libs/fullcalendar.print.css" type="text/css" media="print" />
    <link rel="stylesheet" href="themes/{$THEME}/css/compiled/calendar.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/morris.css" type="text/css" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/daterangepicker.css" type="text/css" />
	<link rel="stylesheet" href="themes/{$THEME}/css/libs/jquery-jvectormap-1.2.2.css" type="text/css" />

	<!-- Favicon -->
	<link rel="apple-touch-icon" sizes="57x57" href="favicon/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="favicon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="favicon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="favicon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="favicon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="favicon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="favicon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="favicon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="favicon/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="favicon/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
<link rel="manifest" href="favicon/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">

	<!-- google font libraries -->
	<link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>

	<!--[if lt IE 9]>
		<script src="themes/{$THEME}/js/html5shiv.js"></script>
		<script src="themes/{$THEME}/js/respond.min.js"></script>
	<![endif]-->

	<!-- global scripts -->

	<script src="themes/{$THEME}/js/jquery.js"></script>
	<script src="themes/{$THEME}/js/bootstrap.js"></script>
	<script src="themes/{$THEME}/js/jquery.nanoscroller.min.js"></script>

	<!-- theme scripts -->
	<script src="themes/{$THEME}/js/scripts.js"></script>
	<script src="themes/{$THEME}/js/pace.min.js"></script>


	<!-- Scripts -->
	<!-- header-vtiger crm name & RSS -->
	<script language="JavaScript" type="text/javascript" src="include/js/json.js"></script>
	<script language="JavaScript" type="text/javascript" src="include/js/general.js?v={$VERSION}"></script>
	<!-- vtlib customization: Javascript hook -->
	<script language="JavaScript" type="text/javascript" src="include/js/vtlib.js?v={$VERSION}"></script>
	<!-- END -->
	<script language="JavaScript" type="text/javascript" id="_current_language_" src="include/js/{php} echo $_SESSION['authenticated_user_language'];{/php}.lang.js?{php} echo $_SESSION['vtiger_version'];{/php}"></script>
	<script language="javascript" type="text/javascript" src="include/scriptaculous/prototype.compatible.js"></script>
	{literal}
	<script language="javascript">
ventanaX = 640;
ventanaY = 550; self.resizeTo(ventanaX,ventanaY);
</script>
<script language="JavaScript">
				function refrescaCasosTesting(parentid) {
					url = 'funcion=obtenerCasosTesting&ajax=1&accountid='+parentid;
					new Ajax.Request(
						'creaRegistro.php',
						{queue: {position: 'end', scope: 'command'},
							method: 'post',
							postBody: url,
							onComplete: function(response)
									{
										document.getElementById('casosTestingTD').innerHTML = response.responseText;
									}
						}
					);
				}

                function ocultarRelacionado(){
					if(document.getElementById("fld_parent_id").value==""){
						document.getElementById("fld_relacionado").style.display="";
						document.getElementById("filaRelacionado").style.display="";
					}
					else{
						document.getElementById("fld_relacionado").style.display="none";
						document.getElementById("filaRelacionado").style.display="none";
						refrescaCasosTesting(document.getElementById("fld_parent_id").value);
					}
				}

                function ocultarCuenta(){
                if(document.getElementById("fld_relacionado").value==""){

                document.getElementById("filaCuenta").style.display="";
                document.getElementById("fld_parent_id").style.display="";
                }
 else{

                document.getElementById("filaCuenta").style.display="none";
                document.getElementById("fld_parent_id").style.display="none";
                }}

function validar(form) { //verifica que haya llenado los campos

if (!form.titulot.value) {
alert("El campo Titulo es obligatorio");
return (false)
}
else if (!form.fld_smownerid3.value) {
alert("El campo Asignado a es obligatorio");
return (false)
}
else if (!form.fld_tarea.value) {
alert("El campo Tarea es obligatorio");
return (false)
}
else if (!form.cometariod.value) {
alert("El campo Comentario de Desarrollador es obligatorio");
return (false)
} else {
return (true)
}
}
function validarForm(form){
    var respuesta=true;
   var ti=document.getElementById('titulo').value;
   var cu=document.getElementById('fld_parent_id').value;
   var descri=document.getElementById('form[descripcion]').value;
   if(ti=='' || cu=='' || descri==''){
       alert("Debe completar todos los campos requeridos!");
       respuesta=false;
   }
	if (respuesta) {
		jQuery(form).find('input').attr('disabled', false);
		jQuery(form).find('select').attr('disabled', false);
		jQuery('#enviar').hide();
	}
   return respuesta;
}
</script>
{/literal}
</head>

<body class="theme-blue pace-done">

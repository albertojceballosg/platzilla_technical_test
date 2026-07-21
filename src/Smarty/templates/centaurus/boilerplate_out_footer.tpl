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

{* PLANTILLA BASE PARA PÁGINAS DE CONTENIDO NO LOGUEADO*}

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	{*Tílulo de la página o contenido de la etiqueta title*}
	{block name="title"}
	<title>Platzilla Management</title>
	{/block}

	<!-- bootstrap -->
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/bootstrap/bootstrap.min.css" />


	<!-- libraries -->
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/font-awesome.css" />
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/nanoscroller.css" />

	<!-- global styles -->
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/theme_styles.css" />
	<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/theme_custom.css?v=1.1" />

	<!-- this page specific styles -->

	<!-- google font libraries -->
	<!--link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'-->
	<link href='https://fonts.googleapis.com/css?family=Raleway:800|Open+Sans:400,700,700italic,300,300italic,400italic,800' rel='stylesheet' type='text/css'>
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">


	<!-- Favicon -->
	{include file="base/include/favicon.tpl"}

	{*Se especifican los archivos css necesarios*}
	{block name="css"}
	{/block}

	<!-- global scripts -->
	<!--script src="themes/centaurus/js/demo-skin-changer.js"></script--> <!-- only for demo -->

	<script src="themes/centaurus/js/jquery.js"></script>
	<script src="themes/centaurus/js/bootstrap.js"></script>
	<script src="themes/centaurus/js/jquery.nanoscroller.min.js"></script>

	<!-- theme scripts -->
	<script src="themes/centaurus/js/scripts.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	<!-- this page specific inline scripts -->
	<script>
	function loginGoogle() {ldelim}
	jQuery("input[name='action']").val('loginGoogle');
	document.DetailView.submit();
	{rdelim}
	</script>

	{*Se especifican los archivos js necesarios*}
	{block name="js"}
	{/block}

	<script type="text/javascript">
		var f_localStorage = f_localStorage || null;
		if (f_localStorage) {
			jQuery (document).ready (f_localStorage.f_toggleMode);
		}
	</script>

</head>
<body id="login-page">
	{block name="body"}
	{/block}

	{block name="footer"}
	<footer id="footer-bar" class="footer-blue site-footer">
		<p id="footer-copyright" class="col-xs-12">
			<span>Todos los derechos reservados</span> -
			<span><a href="/politica-de-privacidad.html" target="_blank" style="color: white; text-decoration: underline;">Política de privacidad</a></span> -
			<span><a href="/politica-de-cookies.html" target="_blank" style="color: white; text-decoration: underline;">Política de cookies</a></span> -
			<span><a href="/terminos-de-servicio.html" target="_blank" style="color: white; text-decoration: underline;">Términos de servicio</a></span>
		</p>
	</footer>
	{/block}


	{block name="scripts"}
	{/block}
</body>
</html>





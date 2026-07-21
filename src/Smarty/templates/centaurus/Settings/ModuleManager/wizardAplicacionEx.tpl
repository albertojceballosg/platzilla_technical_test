<script>
	var bDomainValid = false;
	var bDomainMail = false;
	
	

	function validarDatos() {ldelim}
		alerta = '';
		var lstFields = ["company","domaincode","email","password","password2"];
		var lstLabels = ["{$MOD.LBL_ORGANIZATION_NAME}","{$MOD.LBL_ORGANIZATION_DOMAIN}","{$MOD.LBL_MAIL_ORGANIZATION}","{$MOD.LBL_PASSWORD}","{$MOD.LBL_PASSWORD2}"];
		
		if (jQuery("input[name='action']").val() == 'googleRegister')
			return true;
		
		for(i=0;i < lstFields.length;i++) {ldelim}
			field = jQuery('#'+lstFields[i]);
			if (field.val() == '') {ldelim}
				alerta+= lstLabels[i]+"\n";
			{rdelim}
		{rdelim}
		
		if (alerta != '') {ldelim}
			alert('Campos faltantes:\n{$MOD.LBL_MANDATORY_FIELD_MISSING}' + "\n" + alerta);
			return false;
		{rdelim}
		
		if (jQuery('#password').val() != jQuery('#password2').val()) {ldelim}
			alert('Las contraseñas no son iguales');
			return false;
		{rdelim}
		
		return true;
	{rdelim}
	
	function activaBtnCrear() {ldelim}
		if(jQuery('#terms_cond').prop('checked') && bDomainValid && bDomainMail) 
			jQuery("#btnCrear").prop('disabled', false);
		else
			jQuery("#btnCrear").prop('disabled', true);
	{rdelim}
	
	function registroGoogle() {ldelim}
		jQuery("input[name='action']").val('googleRegister');
		document.createApp.submit();
	{rdelim}
	
	function checkDomain() {ldelim}
		domaincode = jQuery("input[name='domaincode']").val();
		if (domaincode != '') {ldelim}
			jQuery.ajax({ldelim}
						url:"{if $REQUEST_URI eq '1'}platzilla/{/if}index.php",
						data: {ldelim} module: "Users", action: "checkDomain", Ajax:true, domaincode: domaincode {rdelim}
						{rdelim})
				.done(function(result) {ldelim}
					if (result == 'SUCCESS') {ldelim}
						jQuery("#domainalert").hide();
						bDomainValid = true;
						activaBtnCrear();
					{rdelim}
					else {ldelim}
						jQuery("#domainalert").show();
						bDomainValid = false;
						activaBtnCrear();
					{rdelim}
					// show the notification
					
					
					
			{rdelim});
		{rdelim}
	{rdelim}
	
	function checkMail() {ldelim}
		email = jQuery("input[name='email']").val();
		if (email != '') {ldelim}
			jQuery.ajax({ldelim}
						url:"{if $REQUEST_URI eq '1'}platzilla/{/if}index.php",
						data: {ldelim} module: "Users", action: "checkMail", Ajax:true, email: email {rdelim}
						{rdelim})
				.done(function(result) {ldelim}
					if (result == 'SUCCESS') {ldelim}
						jQuery("#correoalert").hide();
						bDomainMail = true;
						activaBtnCrear();
					{rdelim}
					else {ldelim}
						jQuery("#correoalert").show();
						bDomainMail = false;
						activaBtnCrear();
					{rdelim}
					// show the notification
					
					
					
			{rdelim});
		{rdelim}
	{rdelim}
	
	{if $REQUEST_URI eq '1'}
	jQuery(document).ready(function () {ldelim}
		jQuery('#createApp').attr('action', '/platzilla/index.php');
	{rdelim});
	{/if}
</script>
<!-- 
	Template: wizardPlataforma.tpl
	Objetivo: Presentar el dialogo donde se indica el nombre código de la plataforma
	Fecha: 2013-05-14
	Desarrollador: Leonardo Castillo Lacruz (LCL)
	
-->

{literal}

<style type="text/css">


#content-wrapper{
	 background-image: url('http://testwp.timemanagement.es/wp-content/uploads/2015/09/Landingpage-CRM-mock-up-05.jpg');
	  background-repeat: no-repeat;
	  background-size: 100% auto;
	 background-color: #e7ebee; 
}





#header-left{
	//border: 1px solid #000;
	padding-top: 60px;
	min-height: 320px;
}

#header-left h1{
	color:#fff;
	font-size: 22px;
	font-weight: 200;
	margin:0px;
	padding: 0px;
	text-align: center;
}
#header-left h2{
	color:#fff;
	font-size: 14px;
	margin:0px;
	padding: 10px;
	text-align: center;
}
#header-left h3{
	color:#fff;
	font-size: 14px;
	margin:0px;
	padding: 10px;
	text-align: center;
	border: 0px;
}

#header-left .bloque-ingreso{
	text-align:center;
	margin-top: 15px;
} 

.titulo-principal h1{
	//border: 1px solid #ff00c3;
	margin-top: 80px;
	margin-bottom: 5px;
	text-align: center;
	color: #1ABC9C;
}

.titulo-principal2 h1{
	//border: 1px solid #ff00c3;
	margin-top: 10px;
	text-align: center;
	color: #1ABC9C;
}

.objetivos{
	margin-top: 20px;
}

.infographic-box{
	min-height: 110px;
}

.cuadro-crm{
	height: 256px;
	padding: 0px;
	border-radius: 3px;
}

.p-cuadro-crm{

	height: 100%;
	text-align: center;
	background-color: #1ABC9C;
	border-radius: 3px;
}


iframe.ytb-embed {
max-width: 96% !important;
display: block;
margin: 0px auto;
max-height: 250px;
}

#formApp{
	background: #fff;
	border-radius: 5px;
	padding: 3px;
}


#articulos{

margin-left:8px;
}

#articulos a:link,
#articulos a:visited,
#articulos a:hover{
	text-decoration: none;
	color:#344644;
}

#articulos img{
width:100%;	
//min-height: 150px;
//max-height: 160px;

}
#articulos h2{
	font-weight: 100;
	margin: 0 8px;
}

#articulos p{
	margin: 0 8px 10px;
}

#footer-bar{
	display: none;
}

.main-box.clearfix.paquetes{
	margin-left:8px;
}

.infographic-box.tranquilo{

	background-color: aliceblue;

}

.infographic-box.tranquilo i{

	font-size: 1.6em;
  display: block;
  float: left;
  margin-right: 15px;
  margin-top: 15px;
  width: 40px;
  height: 40px;
  line-height: 40px;
  text-align: center;
  border-radius: 50%;
  background-clip: padding-box;
  /* stops bg color from leaking outside the border: */
  color: #fff;	
}

p.ptranquilo{
	margin-top: 4%;
}

.objetivos p.space-top{
	padding-top: 3%;
}



</style>
{/literal}


<div class="row">
	<div class="col-lg-12 col-md-12 col-xs-12" style="border: 0px solid #ff00c3;">
		<div class="row">
			<div class="col-lg-6 col-md-6 col-xs-6" id="header-left">
				<h1>Vende MÁS y organiza TODO</h1>
				<h2>¡Comienza a Probar!</h2>
				<h3>Los primeros 15 días son gratis</h3>
				<div style="text-align:center">
					<form method="post" action="index.php"  ENCTYPE="multipart/form-data" name="createApp" id="createApp" onsubmit="return validarDatos();">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="crearAplicacion" />

					<button class="btn btn-danger" style="width:100px" id="btngoogle" type="button" onclick="registroGoogle()"><i class="fa fa-google-plus"></i> Google+</button>
					<!-- <button class="btn btn-primary" style="width:100px" id="btnfacebook" type="button" onclick="registroFacebook()"><i class="fa fa-facebook-square"></i> Facebook</button>-->
					<button class="btn btn-default" style="width:100px" id="btnTradicional" type="button" onclick="jQuery('#formApp').toggle();jQuery('#formApp2').hide()">Tradicional</button>
				</div>






	<div style="display:none" id="formApp">
	<div class="modal-header">
		<span style="font-size:130%">Para comenzar, solo necesitamos algunos datos:</span>
		<button class="md-close close" type="button" onclick="jQuery('#formApp').hide();">x</button>
	</div>
	<table class="table">
		<tr style="display:none">
			<td>Todas las aplicaciones</td>
			<td>
				<div class="checkbox-nice">
					<input type="checkbox" id="all_aplications" name="all_aplications" value="1" checked="checked" onclick="moduleobj.enableDisbleApp(parseInt(jQuery('#all_aplications').prop('checked')*1))">
					<label for="all_aplications">&nbsp;</label>
				</div>
			</td>
		</tr>
		<tr>
			<td>{$MOD.LBL_ORGANIZATION_NAME}</td>
			<td>
				<input class="form-control" type="text" id="company" name="company" maxlength='100' value="{$NOMBRE_ORGANIZACION}"></input>
			</td>
		</tr>
		<tr>
			<td>{$MOD.LBL_ORGANIZATION_DOMAIN}</td>
			<td>
				<input class="form-control" type="text" id="domaincode" name="domaincode" maxlength='100' value="{$DOMINIO_ORGANIZACION}" onblur="checkDomain();"/>
			</td>
		</tr>
		<tr id="domainalert" style="display:none">
		<td colspan="2">
			<div class="alert alert-danger">
												<i class="fa fa-times-circle fa-fw fa-lg"></i>
												<strong>¡El código identificador del sistema/plataforma especificado, ya fue asignado a otro usuario!</strong><br/>Por favor intente con otro código identificador, no debe contener más de 12 caracteres y tampoco debe utilizar caracteres especiales (Ej: testsistema1).</a>.
											</div>
		</td>
		</tr>
		<tr>
			<td>{$MOD.LBL_MAIL_ORGANIZATION}</td>
			<td>
				<input class="form-control" type="text" id="email" name="email" maxlength='100' value="{$MAIL_ORGANIZATION}" onblur="checkMail();"></input>
			</td>
		</tr>
		<tr id="correoalert" style="display:none">
		<td colspan="2">
			<div class="alert alert-danger">
												<i class="fa fa-times-circle fa-fw fa-lg"></i>
												<strong>¡El correo ya se encuentra registrado en CRM Fácil!</strong> Por favor intente con otro.</a>.
											</div>
		</td>
		</tr>
		<tr>
			<td>{$MOD.LBL_PASSWORD_ADMIN}</td>
			<td>
				<input class="form-control" type="password" id="password" name="password" maxlength='100' value=""></input>
			</td>
		</tr>
		<tr>
			<td>{$MOD.LBL_PASSWORD_ADMIN2}</td>
			<td>
				<input class="form-control" type="password" id="password2" name="password2" maxlength='100' value=""></input>
			</td>
		</tr>
		<!--
		<tr>
			<td>{$MOD.LBL_ORGANIZATION_PHONE}</td>
			<td>
				<input class="form-control" type="text" id="phone" name="phone" maxlength='100' value="{$PHONE}"></input>
			</td>
		</tr>
		<tr>
			<td>{$MOD.LBL_ORGANIZATION_LOGO}</td>
			<td>
				<input name="logo" id="logo" value="" tabindex="" style="" type="file">
			</td>
		</tr>
		-->
		<tr>
			<td colspan="2">
				<p style=" padding-top: 20px; ">
					<input class="checkbox-m" name="terms_cond" id="terms_cond" type="checkbox" style=" margin-top: -4px;" onclick="activaBtnCrear()"> 
					<a href="#" onclick='return window.open("ViewTermsConditions.php","Términos","resizable=1,scrollbars=1");'>Acepto Términos y Condiciones</a>
				</p>
				<p style="text-align:center">
				<button class="btn btn-primary" style="width:200px" id="btnCrear" disabled="disabled" type="submit">{$MOD.LBL_CREAR_ORGANIZATION}</button>
				</p>
			</td>
		</tr>
	</table>
</div>
</form>
<br/>
<div id="formApp2">

</div>










				<div class="bloque-ingreso">
					<h3>¿Ya tienes cuenta con nosotros?</h3>
						<button class="btn btn-primary" style="width:200px" id="btnfacebook" type="button" onclick="window.location = '/platzilla'">Ingresa a CRM-Fácil</button>
				</div>
			</div>
		</div>
	<div>
<div>

<div class="row">
	<div class="col-lg-12 col-md-12 col-xs-12 titulo-principal">
		<h1>Bienvenido a la ERA FÁCIL de la gestión basada en cliente</h1>
	</div>
<div>

<div class="row col-lg-12 col-md-12 col-xs-12 objetivos">
	<div class="col-lg-6 col-md-6 col-xs-6">
		<div class="main-box infographic-box">
			<i class="fa fa-briefcase red-bg"></i>
			<p class="space-top" style="margin-left:75px;">Logra más cierres, al gestionar mejor a tus equipos de marketing y venta. Nuestros gráficos e informes te ayudarán a saber qué sucede</p>
		</div>
	</div>

	<div class="col-lg-6 col-md-6 col-xs-6">
		<div class="main-box infographic-box">
			<i class="fa fa-check-square-o emerald-bg"></i>
			<p class="space-top" style="margin-left:75px;">Ten a la mano información de prospectos, oportunidades de venta, contactos, proyectos, emails y más</p>
		</div>
	</div>

	<div class="col-lg-6 col-md-6 col-xs-6">
		<div class="main-box infographic-box">
			<i class="fa fa-flash green-bg"></i>
			<p class="space-top" style="margin-left:75px;">En unos cuantos clics, puedes agregar otras soluciones tecnológicas, a medida de que realmente los necesites</p>
		</div>
	</div>

	<div class="col-lg-6 col-md-6 col-xs-6">
		<div class="main-box infographic-box">
			<i class="fa fa-mortar-board yellow-bg"></i>
			<p class="" style="margin-left:75px;">Para que evoluciones en tus actividades de negocio y gestión, tenemos cursos de formación, guías, videos y otros contenidos creados bajo la filosofía “Fácil”</p>
		</div>
	</div>
<div>

<div class="row">
	<div class="col-lg-12 col-md-12 col-xs-12 titulo-principal2">
		<h1>Completo y simple. La mejor opción entre CRMs</h1>
	<div>
<div>


<div class="row col-lg-12 col-md-12 col-xs-12 objetivos">
	

	<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12" style="">
		<div class="main-box small-graph-box red-bg cuadro-crm">
			<a href="#" style="">
			<p class="p-cuadro-crm">
			<img src="http://testwp.timemanagement.es/wp-content/uploads/2015/09/Banner-Landingpage-CRM-01.png" alt="" style="height: 100%;"></p>
			</a>
		</div>
	</div>


	<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12" style="">
		<div class="main-box small-graph-box emerald-bg pricing-package" style="height: 256px;background-color: white !important;">
			<ul class="package-features">
				<li class="has-feature">Organización y seguimiento de campañas y prospectos
				</li>
				<li class="has-feature">Gestión de oportunidades, negociaciones y nuevos clientes
				</li>
				<li class="has-feature">Administración de eventos y tareas
				</li>
				<li class="has-feature">Gestión de Clientes, productos y servicios
				</li>
				<li class="has-feature">Facturación y postventa
				</li>
				<li class="has-feature">Análisis de información
				</li>
			</ul>
		</div>
	</div>

	<div class="col-md-6 col-lg-6 col-xs-12 hidden-sm">
		<iframe class="ytb-embed" width="560" height="315" src="https://www.youtube.com/embed/L3WuL5IxYHo" frameborder="0" allowfullscreen></iframe>
	</div>

<div>





<div class="row">
	<div class="col-lg-12 col-md-12 col-xs-12 titulo-principal2">
		<h1>En CRM-Fácil existe un plan para cualquier tamaño de empresa </h1>
		<br>
	<div>
<div>





<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix paquetes">

			<div class="main-box-body clearfix">
				<div class="col-md-3 col-sm-6 col-xs-12 pricing-package">
					<div class="pricing-package-inner">
						<div class="package-header">
							<span class="stars center-block">
								<i class="fa fa-star"></i>
							</span>
							<h3>Básico</h3>
						</div>
						<div class="package-content">
							<div class="package-price">12€</div>
								<ul class="package-top-features">
									<li>/Usuario/mes<br>(pagado anualmente)	</li>
									<li>15€ /usuario/mes<br>(pagado mensualmente) </li>
								</ul>

								<ul class="package-features">
									<li class="has-feature">Envío de emails masivos (500 por día)</li>
									<li class="has-feature">25.000 registros</li>
									<li class="has-feature">1 GB de almacenamiento</li>
									<li class="has-feature">Límite de 5.000 filas para importación</li>
									<li class="has-feature">Soporte por email</li>
								</ul>
							</div>
						</div>
					</div>

					<div class="col-md-3 col-sm-6 col-xs-12 pricing-package">
						<div class="pricing-package-inner">
							<div class="package-header green-bg">
								<span class="stars center-block">
									<i class="fa fa-star"></i>
									<i class="fa fa-star"></i>
								</span>
								<h3>Plus</h3>
							</div>
							<div class="package-content">
								<div class="package-price">29€</div>
									<ul class="package-top-features">
										<li>/Usuario/mes<br>(pagado anualmente)</li>
										<li>35€ /usuario/mes<br>(pagado mensualmente)</li>
									</ul>
									<ul class="package-features">
										<li class="has-feature">Envío de emails masivos (2.500 por día)</li>
										<li class="has-feature">100.000 registros</li>
										<li class="has-feature">10 GB de almacenamiento</li>
										<li class="has-feature">Límite de 25.000 filas para importación</li>
										<li class="has-feature">Soporte por email prioritario</li>
									</ul>
								</div>
							</div>
						</div>
						
						<div class="col-md-3 col-sm-6 col-xs-12 pricing-package">
							<div class="pricing-package-inner">
								<div class="pricing-star" style="">Más<br>popular</div>
								<div class="package-header yellow-bg">
									<span class="stars center-block">
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
									</span>
									<h3>Profesional</h3>
								</div>
								<div class="package-content">
								<div class="package-price">49€</div>
								<ul class="package-top-features">
									<li>/Usuario/mes<br>(pagado anualmente)</li>
									<li>55€ /usuario/mes<br>(pagado mensualmente)</li>
								</ul>
								<ul class="package-features">
									<li class="has-feature">Envío de emails masivos (5.000 por día)</li>
									<li class="has-feature">250.000 registros</li>
									<li class="has-feature">100 GB de almacenamiento</li>
									<li class="has-feature">Límite de 50.000 filas para importación</li>
									<li class="has-feature">Soporte por email prioritario</li>
								</ul>
							</div>
						</div>
					</div>
					
					<div class="col-md-3 col-sm-6 col-xs-12 pricing-package">
						<div class="pricing-package-inner">
							<div class="package-header purple-bg">
								<span class="stars center-block">
									<i class="fa fa-star"></i>
									<i class="fa fa-star"></i>
									<i class="fa fa-star"></i>
									<i class="fa fa-star"></i>
								</span>
								<h3>Premium</h3>
							</div>
							<div class="package-content">
								<div class="package-price">99€</div>
								<ul class="package-top-features">
									<li>/Usuario/mes<br>(pagado anualmente)</li>
									<li>129€ /usuario/mes<br>(pagado mensualmente)</li>
								</ul>
								<ul class="package-features">
									<li class="has-feature">Envío de emails masivos (10.000 por día)</li>
									<li class="has-feature">Registros ilimitados</li>
									<li class="has-feature">250 GB de almacenamiento</li>
									<li class="has-feature">Límite de 50.000 filas para importación</li>
									<li class="has-feature">Soporte por email prioritario</li>
								</ul>
							</div>
						</div>
  					</div>
					
					<div class="col-lg-6 col-sm-6 col-xs-12" style="margin: 0 auto 0 25% !important;">
  						<div class="main-box infographic-box tranquilo">
  							<i class="fa fa-2x fa-info emerald-bg"></i>
  							<p class="ptranquilo"><strong>¡Tranquilo!</strong> No te pediremos datos de tu tarjeta de crédito</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>


	<div class="row"  style="border:0px solid #ff00c3">
		<div class="col-lg-12 col-md-12 col-xs-12 titulo-principal2">
			<h1>¿Necesitas ayuda para tomar esta decisión? </h1>
			<br>
		</div>
	</div>







<div class="row" id="articulos"  style="border:0px solid #000">
	<div class="col-lg-3 col-md-3 col-sm-6" style="">
		<div class="main-box clearfix profile-box-menu">
			<a href="https://drive.google.com/file/d/0B8YFOUjX23tzVU5lb0g3WXJBaE0/view?usp=sharing">
				<div class="main-box-body clearfix">
					<img src="themes/images/descarga-ebook.jpg" />
					<!--div style="width: auto;background-image: url(/wp-content/uploads/2015/09/Banners-remarketing-21.jpg);
					    background-size: cover;
					    border-radius: 3px 3px 0 0;
					    background-position: top center;
					    background-repeat: no-repeat;
					    position: relative;
					" class="photo-box">
					</div-->
					<div class="text-center">
						<h2>Descarga Ebook</h2>
						<br>
						<p>¿Cómo y cuándo usar un CRM?</p>
					</div>
				</div>
			</a>
		</div>
	</div>


	<div class="col-lg-3 col-md-3 col-sm-6" style="">
		<div class="main-box clearfix profile-box-menu">
			<a href="https://drive.google.com/file/d/0B8YFOUjX23tzckJSWmNUR0ZsRTQ/view?usp=sharing">
				<div class="main-box-body clearfix">
					<img src="themes/images/descarga-presentacion.jpg" />
					<div class="text-center">
						<h2>Descarga Presentación</h2>
						<br>
						<p>Convence a tu jefe de usar un CRM</p>
					</div>
				</div>
			</a>
		</div>
	</div>

	<div class="col-lg-3 col-md-3 col-sm-6" style="">
		<div class="main-box clearfix profile-box-menu">
			<a href="/pagina-de-experiencias/">
				<div class="main-box-body clearfix">
					<img src="themes/images/experiencias.jpg" />
					<div class="text-center">
						<h2>Aprende de otras empresas</h2>
						<br>
						<p>Sección Experiencias</p>
					</div>
				</div>
			</a>
		</div>
	</div>

	<div class="col-lg-3 col-md-3 col-sm-6" style="">
		<div class="main-box clearfix profile-box-menu">
			<a href="/pagina-de-ayudas/">
				<div class="main-box-body clearfix">
					<img src="themes/images/como-funciona.jpg" />
					<div class="text-center">
						<h2>¿Cómo funciona?</h2>
						<br>
						<p>Mira nuestras video ayudas</p>
					</div>
				</div>
			</a>
		</div>
	</div>

</div>

</div>
	


<div class="text-center" style="margin-top:58px;">
	<img alt="" src="/test/logo/platzilla-logo2.png" style="width: 256px;height:auto;">
	<div class="row">
		<div class="col-lg-12 col-md-12 col-xs-12 titulo-principal2">
			<h1>Vende MÁS y organiza TODO</h1>
		<div>
	<div>

	<br>
	<p style="margin: 0 8px;">Contacto info@gestionar-facil.com</p><br><p style="margin: 0 8px;">©2015 gestionar-facil.com<br>
		Todos los Derechos Reservados<br>
		Calle de Alfonso XII, 36, 28014, Madrid<br>
		España
	</p>
	<br>
</div>



</div>

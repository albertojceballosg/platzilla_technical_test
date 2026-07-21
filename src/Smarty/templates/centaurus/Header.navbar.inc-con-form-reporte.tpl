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
{literal}
<style>
.labellink{
  color: blue;
  cursor: pointer;
}

.labellink:hover{
  text-decoration: underline;
}

#image{
  display:none;
}
</style>

<script>
function myFunction(){
    var x = document.getElementById("image");
    var txt = "";
	jQuery("#btnEnviarFB").prop('disabled', false);
	jQuery("#msgFB").hide();
    if ('files' in x) {
        if (x.files.length == 0) {
            txt = "Select one or more files.";
        } else {
            for (var i = 0; i < x.files.length; i++) {
				var file = x.files[i];
                if ('size' in file) {
                   if (file.size/1024/1024 > 3) {
						jQuery("#msgFB").show();
						jQuery("#btnEnviarFB").prop('disabled', true);
					}
                }
            }
        }
    }
    document.getElementById("demo").innerHTML = txt;
}

function validateFB() {
	if (jQuery('#subject2').val() != '' && jQuery('#textoMensaje2').val() != '') {
		return true;
	}
	alert('Debe indicar su pregunta y una breve descripción de su reporte');
	return false;
}
</script>
{/literal}
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;top:100px;">
<div class="modal-dialog" style="width:480px">
<div class="modal-content">
<div class="modal-header" style="text-align:center">
<button class="md-close close" onclick="jQuery('#myModal2').removeClass('in');jQuery('#myModal2').hide();window.location='index.php?module=Home&action=CustomerView';">x</button>
<h1 class="modal-title">¡Gracias por el Feedback!</h1>
</div>
<form name="Llamada" action="index.php" method="post" accept-charset="utf-8" enctype="multipart/form-data" onsubmit="return validateFB();">
<input type="hidden" name="action" value="notify"></input>
<input type="hidden" name="module" value="Home"></input>

<div class="modal-body">
<div class="form-group">
	<label for="subject">Pregunta</label>
	<input type="text" class="form-control" id="subject2" name="subject" placeholder="Describa brevemente tu sugerencia o problema"/>
</div>
<div class="form-group" style="margin-bottom:3px">
	<label for="detalles">Detalles</label>
	<textarea class="form-control" id="textoMensaje2" name="textoMensaje" rows="4" placeholder="Escribe aquí los detalles. Por favor sé lo más especifico posible"></textarea>
</div>
<div class="form-group">
	<label for="image" class="labellink">Adjuntar toma de pantalla</label>
	<input type="file" id="image" name="file[]" onchange="myFunction()"/>
	<div id="msgFB" style="display:none">
		<div class="alert alert-danger">
			<i class="fa fa-times-circle fa-fw fa-lg"></i>
			No puede subir archivos mayores a 3MB.
		</div>
	</div>
</div>
<div class="row">
	<div class="form-group col-lg-6">
	<label for="exampleInputEmail1">{$APP.LBL_FIRST_NAME}</label>
	<input type="text" id="contactname" name="contactname" class="form-control" value="{$CURRENT_USER_NAME}" readonly="readonly">
	</div>
	<div class="form-group col-lg-6">
	<label for="exampleInputEmail1">{$APP.LBL_EMAIL}</label>
	<input type="text" id="contactemail" name="contactemail" class="form-control" value="{$CURRENT_USER_MAIL}" readonly="readonly">
	</div>
</div>
<button type="submit" class="btn btn-primary" id="btnEnviarFB">Enviar</button>
</div>
</form>
</div>
</div>
</div>

{if $validateMail eq true}
<div class="md-modal md-effect-1" id="modal-2">
<div class="md-content">
<div class="modal-header">
<button class="md-close close" onclick="jQuery('#modal-2').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});">x</button>
<h2 class="modal-title">{$APP.LBL_VALIDATE_EMAIL}</h2>
</div>
<div class="modal-body" id="validateMailBody">

<div class="row" id="validationEmail">
	<div class="form-group col-lg-12">
	<label>{$APP.LBL_VALIDATE_EMAIL_TEXT}</label>
	<button class="btn btn-primary" type="button" onclick="sendValidationEmail()">{$APP.LBL_CODE_INSERT}</button>
	&nbsp;
	<button class="btn btn-default" type="button" onclick="jQuery('#modal-2').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});">{$APP.LBL_CODE_LATER}</button>
	</div>
</div>

<div class="row" id="validationForm" style="display:none">
	<div class="form-group col-lg-12">
	<label>{$APP.LBL_NUMBER_VALIDATION}</label>
	<input type="text" id="numbervalidation" name="numbervalidation" class="form-control">
	<button class="btn btn-primary" type="button" onclick="sendValidationCode()">{$APP.LBL_VALIDATE_CODE}</button>
	</div>
</div>
</div>
</div>
</div>
<div class="md-overlay"></div><!-- the overlay element -->
<script>
	jQuery(document).ready(function () {ldelim}
		jQuery('#modal-2').addClass('md-show');
		jQuery('.md-overlay').css({ldelim}opacity: 1.0, visibility: "visible"{rdelim});
		jQuery('.md-overlay').addClass('md-show');
	{rdelim});

	function sendValidationEmail() {ldelim}
		jQuery.ajax({ldelim}
					url:"index.php",
					data: {ldelim} module: "Users", action: "UsersAjax", file: "validateMail" {rdelim}
					{rdelim})
			.done(function() {ldelim}
				jQuery('#validationEmail').hide();
				jQuery('#validationForm').show();

		{rdelim});
	{rdelim}
	function sendValidationCode() {ldelim}
		jQuery.ajax({ldelim}
					url:"index.php",
					data: {ldelim} module: "Users", action: "UsersAjax", file: "validateCode", code: jQuery('#numbervalidation').val() {rdelim}
					{rdelim})
			.done(function(result) {ldelim}
				if (result == 'SUCCESS') {ldelim}
					alert('Validación correcta');
					jQuery('#modal-2').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});
				{rdelim} else {ldelim}
					alert('El código está errado. Intenta de nuevo.');
				{rdelim}
		{rdelim});
	{rdelim}

</script>
{/if}
{if $validateMail neq true}
{if $smarty.get.module eq 'Home'}
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: none;top:100px;">
<div class="modal-dialog" style="width:400px">
<div class="modal-content">
<div class="modal-header" style="text-align:center">
<button class="md-close close" onclick="jQuery('#myModal').removeClass('in');jQuery('#myModal').hide();">x</button>
<h4 class="modal-title">{$APP.LBL_HAVE_QUESTIONS}</h4>
</div>
<div class="modal-body" style="text-align:center">
{$APP.LBL_WE_CAN_HELP_YOU}
<br/><br/>
<button type="button" class="md-trigger btn btn-default mrg-b-lg" data-modal="modal-10" style="width:300px;">{$APP.LBL_PROGRAME_CALL_BACK}</button>
<br/>
<br/>
<button type="button" class="btn btn-primary" style="width:300px;" onclick="$zopim.livechat.window.toggle();">{$APP.LBL_CHAT_WITH_US}</button>
<br/>
<br/>
</div>
</div>
</div>
</div>
<script>
	function activaAyuda(){ldelim}
		jQuery('#myModal').show();
		jQuery('#myModal').addClass('in');
	{rdelim}
	{if $SESSIONTIME > 0}
	setTimeout(activaAyuda, {$SESSIONTIME});
	{/if}
</script>
{/if}
{/if}
<div class="md-modal md-effect-1" id="modal-10" style="width:600px;height:400px;">
<div class="md-content">
<div class="modal-header">
<button class="md-close close" onclick="jQuery('#modal-10').removeClass('md-show');jQuery('.md-overlay').css({ldelim}opacity: 0.0, visibility: 'hidden'{rdelim});">x</button>
<h4 class="modal-title">{$APP.CALLME_BACK}</h4>
</div>
<div class="modal-body">

<form name="Llamada" action="modules/Webforms/capture.php" method="post" accept-charset="utf-8">
	<input type="hidden" name="publicid" value="f9a231694d23d76e0919c185e8a6a079"></input>
	<input type="hidden" name="name" value="Llamada"></input>
	<input type="hidden" name="plat" value="marketing"></input>
	<div class="row">
	<div class="form-group col-lg-6">
	<label for="exampleInputEmail1">{$APP.LBL_FIRST_NAME}</label>
	<input type="text" id="firstname2" name="firstname2" class="form-control">
	</div>
	<div class="form-group col-lg-6">
	<label for="exampleInputEmail1">{$APP.LBL_LAST_NAME}</label>
	<input type="text" id="lastname2" name="lastname2" class="form-control">
	</div>
	</div>
	<div class="row">
	<div class="form-group col-lg-6">
	<label for="exampleInputEmail1">{$APP.LBL_COMPANY}</label>
	<input type="text" id="company2" name="company2" class="form-control">
	</div>
	<div class="form-group col-lg-6">
	<label for="exampleInputEmail1">{$APP.LBL_EMAIL}</label>
	<input type="text" id="email2" name="email2" class="form-control">
	</div>
	</div>
	<div class="row">
	<div class="form-group col-lg-6">
	<label for="exampleInputEmail1">{$APP.LBL_PHONE}</label>
	<input type="text" id="phone" name="phone" class="form-control">
	</div>
	<div class="form-group col-lg-6">
	<label for="exampleInputEmail1">{$APP.LBL_COUNTRY}</label>
	<select name="country" id="country" tabindex="" class="form-control" onchange="if (window.onchange_country) onchange_country(this);">
		<option>España</option>
		<option>Afganistán</option>
		<option>Albania</option>
		<option>Alemania</option>
		<option>Andorra</option>
		<option>Angola</option>
		<option>Antigua y Barbuda</option>
		<option>Arabia Saudita</option>
		<option>Argelia</option>
		<option>Argentina</option>
		<option>Armenia</option>
		<option>Australia</option>
		<option>Austria</option>
		<option>Azerbaiyán</option>
		<option>Bahamas</option>
		<option>Bangladés</option>
		<option>Barbados</option>
		<option>Baréin</option>
		<option>Bélgica</option>
		<option>Belice</option>
		<option>Benín</option>
		<option>Bielorrusia</option>
		<option>Birmania</option>
		<option>Bolivia</option>
		<option>Bosnia y Herzegovina</option>
		<option>Botsuana</option>
		<option>Brasil</option>
		<option>Brunéi</option>
		<option>Bulgaria</option>
		<option>Burkina Faso</option>
		<option>Burundi</option>
		<option>Bután</option>
		<option>Cabo Verde</option>
		<option>Camboya</option>
		<option>Camerún</option>
		<option>Canadá</option>
		<option>Catar</option>
		<option>Chad</option>
		<option>Chile</option>
		<option>China</option>
		<option>Chipre</option>
		<option>Ciudad del Vaticano</option>
		<option>Colombia</option>
		<option>Comoras</option>
		<option>Corea del Norte</option>
		<option>Corea del Sur</option>
		<option>Costa de Marfil</option>
		<option>Costa Rica</option>
		<option>Croacia</option>
		<option>Cuba</option>
		<option>Dinamarca</option>
		<option>Dominica</option>
		<option>Ecuador</option>
		<option>Egipto</option>
		<option>El Salvador</option>
		<option>Emiratos Árabes Unidos</option>
		<option>Eritrea</option>
		<option>Eslovaquia</option>
		<option>Eslovenia</option>
		<option>Estados Unidos</option>
		<option>Estonia</option>
		<option>Etiopía</option>
		<option>Filipinas</option>
		<option>Finlandia</option>
		<option>Fiyi</option>
		<option>Francia</option>
		<option>Gabón</option>
		<option>Gambia</option>
		<option>Georgia</option>
		<option>Ghana</option>
		<option>Granada</option>
		<option>Grecia</option>
		<option>Guatemala</option>
		<option>Guyana</option>
		<option>Guinea</option>
		<option>Guinea ecuatorial</option>
		<option>Guinea-Bisáu</option>
		<option>Haití</option>
		<option>Honduras</option>
		<option>Hungría</option>
		<option>India</option>
		<option>Indonesia</option>
		<option>Irak</option>
		<option>Irán</option>
		<option>Irlanda</option>
		<option>Islandia</option>
		<option>Islas Marshall</option>
		<option>Islas Salomón</option>
		<option>Israel</option>
		<option>Italia</option>
		<option>Jamaica</option>
		<option>Japón</option>
		<option>Jordania</option>
		<option>Kazajistán</option>
		<option>Kenia</option>
		<option>Kirguistán</option>
		<option>Kiribati</option>
		<option>Kuwait</option>
		<option>Laos</option>
		<option>Lesoto</option>
		<option>Letonia</option>
		<option>Líbano</option>
		<option>Liberia</option>
		<option>Libia</option>
		<option>Liechtenstein</option>
		<option>Lituania</option>
		<option>Luxemburgo</option>
		<option>Madagascar</option>
		<option>Malasia</option>
		<option>Malaui</option>
		<option>Maldivas</option>
		<option>Malí</option>
		<option>Malta</option>
		<option>Marruecos</option>
		<option>Mauricio</option>
		<option>Mauritania</option>
		<option>México</option>
		<option>Micronesia</option>
		<option>Moldavia</option>
		<option>Mónaco</option>
		<option>Mongolia</option>
		<option>Montenegro</option>
		<option>Mozambique</option>
		<option>Namibia</option>
		<option>Nauru</option>
		<option>Nepal</option>
		<option>Nicaragua</option>
		<option>Níger</option>
		<option>Nigeria</option>
		<option>Noruega</option>
		<option>Nueva Zelanda</option>
		<option>Omán</option>
		<option>Países Bajos</option>
		<option>Pakistán</option>
		<option>Palaos</option>
		<option>Panamá</option>
		<option>Papúa Nueva Guinea</option>
		<option>Paraguay</option>
		<option>Perú</option>
		<option>Polonia</option>
		<option>Portugal</option>
		<option>Reino Unido</option>
		<option>República Centroafricana</option>
		<option>República Checa</option>
		<option>República de Macedonia</option>
		<option>República del Congo</option>
		<option>República Democrática del Congo</option>
		<option>República Dominicana</option>
		<option>República Sudafricana</option>
		<option>Ruanda</option>
		<option>Rumanía</option>
		<option>Rusia</option>
		<option>Samoa</option>
		<option>San Cristóbal y Nieves</option>
		<option>San Marino</option>
		<option>San Vicente y las Granadinas</option>
		<option>Santa Lucía</option>
		<option>Santo Tomé y Príncipe</option>
		<option>Senegal</option>
		<option>Serbia</option>
		<option>Seychelles</option>
		<option>Sierra Leona</option>
		<option>Singapur</option>
		<option>Siria</option>
		<option>Somalia</option>
		<option>Sri Lanka</option>
		<option>Suazilandia</option>
		<option>Sudán</option>
		<option>Sudán del Sur</option>
		<option>Suecia</option>
		<option>Suiza</option>
		<option>Surinam</option>
		<option>Tailandia</option>
		<option>Tanzania</option>
		<option>Tayikistán</option>
		<option>Timor Oriental</option>
		<option>Togo</option>
		<option>Tonga</option>
		<option>Trinidad y Tobago</option>
		<option>Túnez</option>
		<option>Turkmenistán</option>
		<option>Turquía</option>
		<option>Tuvalu</option>
		<option>Ucrania</option>
		<option>Uganda</option>
		<option>Uruguay</option>
		<option>Uzbekistán</option>
		<option>Vanuatu</option>
		<option>Venezuela</option>
		<option>Vietnam</option>
		<option>Yemen</option>
		<option>Yibuti</option>
		<option>Zambia</option>
		<option>Zimbabue</option>
  	</select>

	</div>
	</div>
	<div class="row">
	<div class="form-group col-lg-6">
	<label for="exampleInputEmail1">{$APP.LBL_HORAS_DISPONIBLES}</label>
	<select name="description" id="description" tabindex="" class="form-control" onchange="if (window.onchange_description) onchange_description(this);">
		<option>8 - 10</option>
		<option>10 - 12</option>
		<option>12 - 14</option>
		<option>14 - 16</option>
		<option>16 - 18</option>
		<option>18 - 20</option>
	</select>
	</div>
	</div>
	<div class="row">
	<div class="form-group col-lg-6">
		<button class="btn btn-primary" type="submit">Enviar</button>
	</div>
	</div>
</form>
</div>
</div>
</div>

<header class="navbar" id="header-navbar" {if $ES_INSTANCIA neq 1} style="background-color: #4c5763"  {/if}>
	<div class="container">
		<div>
		{if $BRIEFING neq 'true'}
			<a href="index.php" id="logo" class="navbar-brand" {if $ES_INSTANCIA neq 1} style="background-color: #4c5763"  {/if} >
		{else}
			<a href="../index.php" id="logo" class="navbar-brand" {if $ES_INSTANCIA neq 1} style="background-color: #4c5763"  {/if}>
		{/if}
			<img src="/test/logo/platzilla-logo.png" border=0 class="logo-menu">
		</a>
		</div>
		<div class="clearfix">
		<button class="navbar-toggle" data-target=".navbar-ex1-collapse" data-toggle="collapse" type="button">
			<span class="sr-only">Toggle navigation</span>
			<span class="fa fa-bars"></span>
		</button>

		<div class="nav-no-collapse navbar-left pull-left hidden-sm hidden-xs">
			<ul class="nav navbar-nav pull-left">
				<li>
					<a class="btn" id="make-small-nav">
						<i class="fa fa-bars"></i>
					</a>
				</li>
				<li>
					<a class="btn" id="status" onclick="javascript:void(0)" style="display:none;">
						<i class="fa fa-spinner fa-spin"></i> Aguarde un momento por favor...
					</a>
				</li>
			</ul>
		</div>

		<div class="nav-no-collapse pull-right" id="header-nav">
			<ul class="nav navbar-nav pull-right">
				{if $BRIEFING neq 'true'}
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<i class="fa fa-plus"></i>
					</a>
					{$MENU_QUICKCREATE}
				</li>

				{if $SHOWMAIL eq 1}
				<li>
					<a class="btn dropdown-toggle" href="index.php?module=webmail&action=index">
						<i class="fa fa-envelope-o"></i> {$SHOWMAIL}
						<span class="count">1</span>
					</a>
				</li>
				{/if}

				<li class="mobile-search">
					<a class="btn">
						<i class="fa fa-search"></i>
					</a>

					<div class="drowdown-search">
						<form role="search" name="UnifiedSearch" method="get" action="index.php" style="margin:0px" onsubmit="VtigerJS_DialogBox.block();">
							<div class="form-group col-lg-6">
								<input type="hidden" name="action" value="UnifiedSearch" style="margin:0px">
								<input type="hidden" name="module" value="Home" style="margin:0px">
								<input type="hidden" name="parenttab" value="Settings" style="margin:0px">
								<input type="hidden" name="search_onlyin" value="--USESELECTED--" style="margin:0px">
								<input type="text" name="query_string" class="form-control" placeholder="{$APP.LBL_SEARCH}">
								<i class="fa fa-search nav-search-icon"></i>
							</div>
						</form>
					</div>

				</li>

				<li>
					<a href="index.php?module=Calendar&action=index" class="btn">
						<i class="fa fa-calendar"></i>
					</a>
				</li>

				<li>
					<a href="index.php?module=graficosgenerales&action=index" class="btn">
						<i class="fa fa-bar-chart-o"></i>
					</a>
				</li>
				{*
				{if $smarty.get.module eq 'Settings' && $smarty.get.action eq 'customer'}
				<li>
					<a href="javascript:void(0)" class="btn dropdown-toggle" alt="{"Notificaciones"|getTranslatedString}" title="{"Notificaciones"|getTranslatedString}" onclick="document.getElementById('iframeDot').src=document.getElementById('iframeDot').src.split('?')[0]+'?module=notificaciones&action=index'">
						<i class="fa fa-warning"></i>
						<span class="count">8</span>
					</a>
				</li>
				{elseif $NOTIFICATIONS_PERMITTED === 'yes'}
				<li>
					<a href="javascript:void(0)" class="btn dropdown-toggle" alt="{"Notificaciones"|getTranslatedString}" title="{"Notificaciones"|getTranslatedString}" onclick="goToNotifications({$PENDING_NOTIFICATIONS.platform}, {$PENDING_NOTIFICATIONS.timemanagement})">
						<i class="fa fa-warning"></i>
						{if $PENDING_NOTIFICATIONS.total}
							<span class="count">{$PENDING_NOTIFICATIONS.total}</span>
						{/if}
					</a>
				</li>
				{/if}
				*}
				{*
				{foreach item=data key=key from=$CUSTOM_ICONS}
					<li>
						<a href="{$data.linkurl}" class="btn" alt="{$data.linklabel}" title="{$data.linklabel}" >
							<i class="fa {$data.linkicon}"></i>
						</a>
					</li>
				{/foreach}*}
				<li class="dropdown profile-dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						{if $CURRENT_USER_IMAGE}
							<img src="{$CURRENT_USER_IMAGE}" alt=""/>
						{else}
							<img src="themes/centaurus/img/photo.png" alt=""/>
						{/if}


						<span class="hidden-xs">{$USER}</span>
						<i class="fa fa-chevron-circle-down"></i>
					</a>
					<ul class="dropdown-menu">
						{if !$CLOSE_HOME_ICON}
						<!--li><a href="index.php?module=Users&action=DetailView&record={$CURRENT_USER_ID}&modechk=prefview"><i class="fa fa-user"></i>{$APP.LBL_MY_PREFERENCES}</a></li-->
						{/if}

						<!--[ TT11219 ] Migrar el módulo de crear aplicaciones del settings a un nuevo módulo
							DM 19/07/2016 -->
						{$ADMIN_LINK}



						<li><a href="index.php?module=Home&action=CustomerView"><i class="fa fa-files-o"></i>{$APP.LBL_MY_INVOICES}</a></li>
					<!--	<li id="help"><a style="cursor: pointer;"><i class="fa fa-comments"></i>Ayúdanos a mejorar</a></li>-->
						<!--<li><a href="index.php?module=webmail&action=index"><i class="fa fa-envelope-o"></i>{$APP.LBL_EMAIL}</a></li>
						<li>
							<div id="config-tool" style="position: initial;">
							<div id="config-tool-options">
								Color de tema
							<ul id="skin-colors" class="clearfix">
								<li>
									<a class="skin-changer" data-skin="" data-toggle="tooltip" title="" style="background-color: #34495e;" data-original-title="Default">
									</a>
								</li>
								<li>
									<a class="skin-changer active" data-skin="theme-white" data-toggle="tooltip" title="" style="background-color: #2ecc71;" data-original-title="White/Green">
									</a>
								</li>
								<li>
									<a class="skin-changer blue-gradient" data-skin="theme-blue-gradient" data-toggle="tooltip" title="" data-original-title="Gradient">
									</a>
								</li>
								<li>
									<a class="skin-changer" data-skin="theme-turquoise" data-toggle="tooltip" title="" style="background-color: #1abc9c;" data-original-title="Green Sea">
									</a>
								</li>
								<li>
									<a class="skin-changer" data-skin="theme-amethyst" data-toggle="tooltip" title="" style="background-color: #9b59b6;" data-original-title="Amethyst">
									</a>
								</li>
								<li>
									<a class="skin-changer" data-skin="theme-blue" data-toggle="tooltip" title="" style="background-color: #2980b9;" data-original-title="Blue">
									</a>
								</li>
								<li>
									<a class="skin-changer" data-skin="theme-red" data-toggle="tooltip" title="" style="background-color: #e74c3c;" data-original-title="Red">
									</a>
								</li>
								<li>
									<a class="skin-changer" data-skin="theme-whbl" data-toggle="tooltip" title="" style="background-color: #3498db;" data-original-title="White/Blue">
									</a>
								</li>
							</ul>
							</div>
							</div>
						</li>-->
						<li role="separator" class="divider"></li>
						<li><a href="index.php?module=Users&action=Logout"><i class="fa fa-power-off"></i>{$APP.LBL_LOGOUT}</a></li>
					</ul>
				</li>
				{else}
					{if $smarty.get.module eq 'Users' && $smarty.get.action eq 'signin' || $smarty.get.action eq 'pricing'}
					{if $BRIEFING eq '1'}
					<li>
						<a target="blog" href="/caracteristicas-del-crm-facil/">
							<span style="font-size:120%">Características</span>
						</a>
					</li>
					<li>
						<a target="blog" href="/platzilla/module-Users-action-pricing-parenttab-">
							<span style="font-size:120%">Planes y Precios</span>
						</a>
					</li>
					<li>
						<a target="blog" href="/pagina-de-experiencias/">
							<span style="font-size:120%">Experiencias</span>
						</a>
					</li>
					<li>
						<a target="blog" href="/quienes-somos/">
							<span style="font-size:120%">Sobre Nosotros</span>
						</a>
					</li>
					{if $smarty.get.action neq 'pricing'}
					<li>
						<a href="#">
							<span class="glyphicon glyphicon-earphone"></span>&nbsp;
							<span style="font-size:120%" data-modal="modal-10" class="md-trigger mrg-b-lg">{$APP.LBL_PROGRAME_CALL_BACK}</span>
						</a>
					</li>
					{else}
						<li>
						<a href="/platzilla/module-Users-action-signin">
							<span style="font-size:120%">Prueba CRM-Fácil</span>
						</a>
						</li>
					{/if}
					{/if}
					{else}
					<li>
						<a href="/platzilla/module-Users-action-signin">
							<i class="fa fa-user"></i>
						</a>
					</li>

					{if $smarty.get.module eq 'Users' && $smarty.get.action eq 'pricing'}
					{else}
					<li>
						<a href="/platzilla/module-Users-action-signin">
							<span style="font-size:120%">{$APP.LBL_TEST_EMPRESA_FACIL}</span>
						</a>
					</li>
					{/if}
					<li>
						<a href="/platzilla">
							<i class="fa fa-sign-in"></i>
						</a>
					</li>
					{/if}

				{/if}
			</ul>
		</div>
		</div>
	</div>
</header>

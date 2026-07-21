{extends file="base/BaseForm.tpl"}

{block name="header-logo"}
<a href="http://www.platzilla.com"><img alt="" src="themes/centaurus/img/logo-platzilla-vert.png"></a>
{/block}

{block name="box-title"}
<h1>Datos de acceso</h1>
<p class="title-description">Definamos tus datos de acceso</p>
{/block}

{block name="box-form"}
<div class="input-group col-xs-12">
	<input type="email" class="form-control login-form" id="usuarioEmail" name="usuarioEmail" placeholder="Email">
	<div class="login-error">
		<p class="social-text" style="text-align:justify" id="error_email"></p>
	</div>
</div>
<div class="input-group col-xs-12">
	<input type="password" class="form-control login-form" id="clave" name="clave" placeholder="Clave">
	<div class="login-error">
		<p class="social-text" style="text-align:justify" id="error_password"></p>
	</div>
</div>
<div class="input-group col-xs-12">
  <input id="claveConfirm" name="claveConfirm" class="form-control login-form" placeholder="Confirmación de contraseña" type="password" >
  <div class="login-error">
		<p class="social-text" style="text-align:justify" id="error_password_confirm"></p>
	</div>
</div>
{* Para agregar el checkbox de Politicas de Privacidad segun la RPGD - AV 20180530 *}
<div class="input-group col-xs-12">
	<div class="checkbox-nice">
		<input id="RGPDstep2" type="checkbox" value="check">
		<label for="RGPDstep2">
			Al usar este formulario accedes al almacenamiento y gesti&oacute;n de tus datos por parte de esta web, de conformidad con nuestra <a href="/politica-de-privacidad.html" target="blank" rel="nofollow">Pol&iacute;tica de privacidad</a>
		</label>
		<div class="login-error">
			<p class="social-text" style="text-align:justify" id="error_RGPDstep2"></p>
		</div>
	</div>
</div>
{* Para agregar el checkbox de Politicas de Privacidad segun la RPGD - AV 20180530 *}
<div class="row">
	<div class="col-xs-12">
		<button type="button" class="btn btn-success col-xs-12 nextBtn">
			Continuar
			<span class="fa fa-arrow-right"></span> 
		</button>
	</div>
</div>
{/block}                   

{block name="box-content"}                    
{/block}

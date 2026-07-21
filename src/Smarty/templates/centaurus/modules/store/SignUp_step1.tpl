{extends file="base/BaseForm.tpl"}
{block name="header-logo"}
{strip}
<a href="http://www.platzilla.com"><img alt="" src="themes/centaurus/img/logo-platzilla-vert.png"></a>
{/strip}
{/block}
{block name="box-title"}
{strip}
<h1>Comencemos</h1>
<p class="title-description">Háblanos un poco sobre ti</p>
{/strip}
{/block}
{block name="box-form"}
{strip}
<div class="input-group col-xs-12">
{foreach $APPLICATIONS as $application}
	<input type="hidden" name="applicationcodes[]" value="{$application.app_code}" />
{/foreach}
	<input type="text" class="form-control login-form" id="name" name="name" placeholder="Nombre">
	<div class="login-error">
		<p class="social-text" style="text-align:justify" id="error_name"></p>
	</div>
	<input type="hidden" class="form-control" id="isdemo" name="isdemo" value="1">
	<input type="hidden" class="form-control" id="usersCounterHidden" name="usersCounterHidden" value="1">
</div>
<div class="input-group col-xs-12">
	<input type="text" class="form-control login-form" id="lastname" name="lastname" placeholder="Apellido">
	<div class="login-error">
		<p class="social-text" style="text-align:justify" id="error_lastname"></p>
	</div>
</div>
<div class="input-group col-xs-12">
	<input type="text" class="form-control login-form" id="company" name="company" placeholder="Empresa">
</div>
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
	<input id="claveConfirm" name="claveConfirm" class="form-control login-form" placeholder="Confirmación de contraseña" type="password">
	<div class="login-error">
		<p class="social-text" style="text-align:justify" id="error_password_confirm"></p>
	</div>
</div>
<div class="input-group col-xs-12">
	<div class="checkbox-nice">
		<input id="RGPDstep2" type="checkbox" value="check">
		<label for="RGPDstep2">
			Al usar este formulario accedes al almacenamiento y gesti&oacute;n de tus datos por parte de esta web, de conformidad con nuestra
			<a href="/politica-de-privacidad.html" target="blank" rel="nofollow">Pol&iacute;tica de privacidad</a>
		</label>
		<div class="login-error">
			<p class="social-text" style="text-align:justify" id="error_RGPDstep2"></p>
		</div>
	</div>
</div>
<div class="input-group col-xs-12">
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<div class="g-recaptcha" data-sitekey="6LeHHKMaAAAAAIQG9wiEruq30LBmpAZsTEnVcoqB" data-callback="recaptchaCallback" data-expired-callback="recaptchaCallbackExpired"></div>
	<div class="login">
		<p class="social-text" style="text-align:justify" id="error_grecaptcha"></p>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<button type="submit" class="btn btn-success col-xs-12 nextBtn" id="submitBtn" disabled="disabled">
			<span>Continuar </span> <span class="fa fa-arrow-right"></span>
		</button>
	</div>
</div>
<script type="text/javascript">
	var errorgrecaptcha = jQuery ('#error_grecaptcha');
	errorgrecaptcha.html ('El botón "Continuar" se habilitará una vez se verifique que Ud. no es un robot');
	function recaptchaCallback(){
		jQuery ('#submitBtn').prop('disabled', false);
		errorgrecaptcha.html ('Se ha verificado que Ud. no es un robot, el botón "Continuar" está habilitado');
	}
	function recaptchaCallbackExpired(){
		jQuery ('#submitBtn').prop('disabled', true);
		errorgrecaptcha.html ('La verificación ha caducado. El botón "Continuar" se habilitará una vez se verifique que Ud. no es un robot');
	}
</script>
{/strip}
{/block}
{block name="box-content"}{/block}
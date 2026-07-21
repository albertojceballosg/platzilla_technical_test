{extends file="base/BaseForm.tpl"}
{block name="header-logo"}
{strip}
<img alt="" style="width: 80%; height: 80% " src="themes/centaurus/img/three-logos.jpeg">
	{*logo-platzilla-vert.png*}
{/strip}
{/block}
{block name="box-title"}
{strip}
<h1>Selecciona el contenido que deseas recibir periódicamente.</h1>
	{* <p class="title-description">Artículos, cursos y mas...</p> *}
{/strip}
{/block}
{block name="box-form"}
{strip}
	<div class="row" style="">
        {math equation= rand() assign='idDesLink'}
		<div class="col-md-12">
			<table class="table table-hover">
                {foreach $AD_QUEUES as $adQues}
                {assign var='queueId' value=$adQues->getId ()}
                {assign var='queueName' value=$adQues->getName ()}
                {assign var='period' value=$adQues->getPeriod ()}
                {assign var='status' value=$adQues->getStatus ()}
                {assign var='description' value=$adQues->getDescription ()}
				<tr>
					<td width="90%">
						<h6 style="display:inline-block;vertical-align: middle; margin:0">{$queueName}</h6></td>
					<td width="10%">
						<div class="checkbox" style="margin: 0">
							<label>
								<input type="checkbox" name="adQueueIds[]" value="{$queueId}">
							</label>
						</div>
					</td>
				</tr>
                {/foreach}
			</table>
			<span  id="error-queue" class="help-block" style="color: red"></span>
		</div>
	<div class="input-group col-xs-12">
		<input type="email" class="form-control login-form" id="usuarioEmail" name="usuarioEmail" placeholder="Email">
		<div class="login-error">
			<span  id="error_email" class="help-block" style="color: red"></span>
		</div>
	</div>
<div class="input-group col-xs-12">
	<script src='https://www.google.com/recaptcha/api.js'></script>
	<div class="g-recaptcha" data-sitekey="6LeHHKMaAAAAAIQG9wiEruq30LBmpAZsTEnVcoqB" data-callback="recaptchaCallback" data-expired-callback="recaptchaCallbackExpired"></div>
	<div class="login">
		<p class="social-text" style="text-align:justify" id="error_grecaptcha"></p>
	</div>
</div>
	</div>
<div class="row">
	<div class="col-xs-12">
		<button type="button" class="btn btn-success col-xs-12 nextBtn" id="submitBtn" disabled="disabled" onclick="BulletinBoardUtils.createFormativeInstance ()">
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
	<script type="text/javascript" src="https://player.vimeo.com/api/player.js"></script>
{/strip}
{/block}
{block name="box-content"}{/block}
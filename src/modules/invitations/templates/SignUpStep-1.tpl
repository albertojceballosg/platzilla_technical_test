{extends file="base/BaseForm.tpl"}

{block name="header-logo"}
<a href="http://www.platzilla.com"><img alt="" src="themes/centaurus/img/logo-platzilla-vert.png"></a>
{/block}

{block name="box-title"}
<h1>Comencemos</h1>
<p class="title-description">Háblanos un poco sobre ti</p>
{/block}

{block name="box-form"}
<div class="input-group col-xs-12">
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
<div class="row">
	<div class="col-xs-12">
		<button type="button" class="btn btn-success col-xs-12 nextBtn" data-current-step="step-1" data-next-step="step-2">
			Continuar
			<span class="fa fa-arrow-right"></span>
		</button>
	</div>
</div>
{/block}

{block name="box-content"}
{/block}

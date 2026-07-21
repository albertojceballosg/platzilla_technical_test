{extends file="base/box-frame.tpl"}
{block name="title"}
	<title>Recuperar contraseña - Platzilla Management</title>
{/block}
{block name="header-logo"}
	<a href="http://www.platzilla.com"><img alt="" src="themes/centaurus/img/logo-platzilla-vert.png"></a>
{/block}
{block name="box-title"}
	<h1 class="title-large">¿Olvidaste tu contraseña?</h1>
	<p class="title-description">Suministra tu nueva contraseña y nos encargaremos del resto</p>
{/block}
{block name="box-form"}
	<form action="index.php" method="post" onsubmit="return validateForm ();">
		<input type="hidden" name="module" value="Users" />
		<input type="hidden" name="action" value="reset_password" />
		<input type="hidden" name="Ajax" value="true" />
		<input type="hidden" name="email" value="{$INSTANCE.administrator}" />
		<input type="hidden" name="token" value="{$TOKEN}" />
		<div class="input-group col-xs-12">
			<label for="email">Tu dirección de correo electrónico</label>
			<input type="email" id="email" value="{$INSTANCE.administrator}" class="form-control login-form" disabled="disabled" />
		</div>
		<div class="input-group col-xs-12">
			<label for="password">Tu nueva contraseña</label>
			<input type="password" id="password" name="password" class="form-control login-form" />
		</div>
		<div class="input-group col-xs-12">
			<label for="repeatpassword">Repite tu nueva contraseña</label>
			<input type="password" id="repeatpassword" name="repeatpassword" class="form-control login-form" />
		</div>
		<div class="row">
			<div class="col-xs-12">
				<input type="submit" class="btn btn-success col-xs-12" value="Recuperar contraseña">
			</div>
		</div>
	</form>
	<script type="text/javascript">
		function validateForm () {
			var password = document.getElementById ('password').value,
				repeatpassword = document.getElementById ('repeatpassword').value;

			if ((password === undefined) || (password === null) || (password.trim () === '')) {
				alert ('Introduce tu contraseña');
				return false;
			}
			if ((repeatpassword === undefined) || (repeatpassword === null) || (repeatpassword.trim () === '')) {
				alert ('Repite tu contraseña');
				return false;
			}
			if ((password !== repeatpassword)) {
				alert ('Las contraseñas no coinciden');
				return false;
			}
			return true;
		}
	</script>
{/block}
{block name="box-content"}
	<div class="row">
		<div class="col-xs-12 text-center">
			<p class="first">
				<a class="social-text first social-text-link " href="index.php">Regresar</a>
			</p>
		</div>
	</div>
{/block}
{block name="footer"}
	{include file="base/Footer.tpl"}
{/block}


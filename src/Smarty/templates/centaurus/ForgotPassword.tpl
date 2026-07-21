{extends file="base/box-frame.tpl"}
{block name="title"}
	<title>Recuperar contraseña - Platzilla Management</title>
{/block}
{block name="header-logo"}
	<a href="http://www.platzilla.com"><img alt="" src="themes/centaurus/img/logo-platzilla-vert.png"></a>
{/block}
{block name="box-title"}
	<h1 class="title-large">¿Olvidaste tu contraseña?</h1>
	<p class="title-description">Escribe tu dirección de correo y te enviaremos instrucciones por correo.</p>
{/block}
{block name="box-form"}
	<form role="form" action="index.php" method="post" name="DetailView" id="form">
		<input type="hidden" name="module" value="Users" />
		<input type="hidden" name="action" value="reset_password" />
		<input type="hidden" name="Ajax" value="true" />
		<div class="input-group col-xs-12">
			<input name="email" class="form-control login-form" placeholder="Tu dirección de correo" type="text">
		</div>
		<div class="row">
			<div class="col-xs-12">
				<input type="submit" class="btn btn-success col-xs-12" value="Recuperar contraseña">
			</div>
		</div>
	</form>
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


{strip}
{extends file="base/boilerplate.tpl"}
{block name="title"}Invitación a compartir contenido{/block}
{block name="css"}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/box-frame.css" />
<link rel="stylesheet" href="themes/centaurus/css/compiled/store.css" />
<link rel="stylesheet" href="themes/centaurus/css/compiled/section/calculator.css" />
<link rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css" />
{/block}
{block name="body"}
<div class="row invitation">
	<div class="col-xs-12">
		<form action="index.php" method="post" role="form" onsubmit="alert ('WIP'); return false;">
			<input type="hidden" name="module" value="store" />
			<input type="hidden" name="action" value="invitation" />
			<input type="hidden" name="process" value="CreateInstance" />
			<input type="hidden" name="token" value="{$TOKEN}" />
			<div id="login-box">
				<header id="login-header" class="main-box-header clearfix">
					<div id="login-logo">
						<a href="http://www.platzilla.com"><img src="themes/centaurus/img/logo-platzilla-vert.png" class="img-responsive" alt="Platzilla" /></a>
					</div>
					<div style="background: white;">
						<hr class="linea">
					</div>
				</header>
				<div id="login-box-inner">
					<h2>Invitación a compartir</h2>
					<p>Te han invitado a compartir contenidos de Platzilla</p>
					<p>Para poder aceptar estos registros debes tener una cuenta. Puedes hacerlo tú mismo en la <a href="index.php">página de registro</a> y luego volver a esta página o indícanos los siguientes datos y lo hacemos por tí:</p>
					<div class="form-group">
						<label for="user-name">Usuario</label>
						<input type="text" id="user-name" name="username" value="{$USER_NAME}" class="form-control" readonly="readonly" />
					</div>
					<div class="form-group">
						<label for="first-name">Tu nombre <span class="required">*</span></label>
						<input type="text" id="first-name" name="firstname" class="form-control" />
					</div>
					<div class="form-group">
						<label for="last-name">Tus apellidos <span class="required">*</span></label>
						<input type="text" id="last-name" name="lastname" class="form-control" />
					</div>
					<div class="form-group">
						<label for="company-name">Tu empresa <span class="required">*</span></label>
						<input type="text" id="company-name" name="companyname" class="form-control" />
					</div>
					<div class="form-group">
						<label for="application-code">La aplicación inicial <span class="required">*</span></label>
						<select name="applicationcode" class="form-control" title="">
{if (!empty ($AVAILABLE_APPLICATIONS))}
	{foreach $AVAILABLE_APPLICATIONS as $application}
							<option value="{$application->getCode ()}">{$application->getName ()}</option>
	{/foreach}
{/if}
						</select>
					</div>
					<button type="submit" class="btn btn-success nextBtn">De acuerdo! Crear mi cuenta <span class="fa fa-arrow-right"></span></button>
				</div>
			</div>
		</form>
	</div>
</div>
{/block}
{/strip}
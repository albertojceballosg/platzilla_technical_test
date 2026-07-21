{strip}
{extends file="base/boilerplate.tpl"}
{block name="title"}Invitación a compartir contenido{/block}
{block name="css"}
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/box-frame.css" />
<link rel="stylesheet" href="themes/centaurus/css/compiled/store.css">
<link rel="stylesheet" href="themes/centaurus/css/compiled/section/calculator.css">
<link rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css" />
{/block}
{block name="body"}
<div class="row invitation">
	<div class="col-xs-12">
		<form action="index.php" method="post" role="form" onsubmit="alert ('WIP'); return false;">
			<input type="hidden" name="module" value="store" />
			<input type="hidden" name="action" value="invitation" />
			<input type="hidden" name="process" value="InstallApplication" />
			<input type="hidden" name="token" value="{$TOKEN}" />
			<div id="login-box">
				<header class="main-box-header clearfix" id="login-header">
					<div id="login-logo">
						<a href="http://www.platzilla.com"><img src="themes/centaurus/img/logo-platzilla-vert.png" class="img-responsive" alt="Platzilla" /></a>
					</div>
					<div style="background: white;">
						<hr class="linea">
					</div>
				</header>
				<div id="login-box-inner">
					<h2>Invitación a compartir</h2>
					<p>Te han invitado a compartir registros del módulo <strong>{$MODULE_LABEL}</strong></p>
					<p>Para poder aceptar estos registros debes tener instalada una aplicación de la cual forma parte.</p>
					<p>Puedes hacerlo tú mismo en la <a href="index.php?module=store&action=index">Zona de Aplicaciones</a> y luego volver a esta página o podemos hacerlo por tí.</p>
					<p>Si quieres que lo hagamos por tí, indícanos cuál de estas aplicaciones te gustaría instalar:</p>
					<select name="applicationcode" class="form-control" title="">
{if (!empty ($AVAILABLE_APPLICATIONS))}
	{foreach $AVAILABLE_APPLICATIONS as $application}
						<option value="{$application->getCode ()}">{$application->getName ()}</option>
	{/foreach}
{/if}
					</select>
					<button type="submit" class="btn btn-success nextBtn">De acuerdo! Hazlo por mí <span class="fa fa-arrow-right"></span></button>
				</div>
			</div>
		</form>
	</div>
</div>
{/block}
{/strip}
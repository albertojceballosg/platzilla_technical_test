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
			<input type="hidden" name="process" value="ShareRecords" />
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
					<p>Para poder empezar a compartir, debes aceptar haciendo clic en el siguiente botón</p>
					<button type="submit" class="btn btn-success nextBtn">De acuerdo! Acepto los registros <span class="fa fa-arrow-right"></span></button>
				</div>
			</div>
		</form>
	</div>
</div>
{/block}
{/strip}
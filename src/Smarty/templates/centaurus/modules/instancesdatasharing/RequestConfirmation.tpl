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
		<form action="index.php" method="post" role="form">
			<input type="hidden" name="module" value="store" />
			<input type="hidden" name="action" value="invitation" />
			<input type="hidden" name="Ajax" value="true" />
			<input type="hidden" name="id" value="{$TOKEN}" />
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
					<p>{$SENDER_FULL_NAME} te ha invitado a compartir contenidos ({$MODULE_LABEL}) de <a href="//www.platzilla.com">Platzilla</a> para trabajar juntos.</p>
{if (!empty ($COMMENTS))}
					<p>Adicionalmente, {$SENDER_FULL_NAME} te dejó dicho: <i>{$COMMENTS}</i></p>
{/if}
					<p>Debes indicar que estás de acuerdo en compartir los registros haciendo click en el siguiente botón</p>
					<button type="submit" class="btn btn-success nextBtn">De acuerdo! Acepto los contenidos <span class="fa fa-arrow-right"></span></button>
				</div>
			</div>
		</form>
	</div>
</div>
{/block}
{/strip}
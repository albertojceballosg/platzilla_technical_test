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
		<form action="index.php" method="post" role="form">
			<input type="hidden" name="module" value="store" />
			<input type="hidden" name="action" value="invitation" />
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
					<p>Para poder aceptar estos registros debes instalar una aplicación que te permita gestionar esos contenidos. Si quieres que lo hagamos por tí, por favor sigue los siguientes pasos:</p>
					<div class="form-group col-xs-12">
						<label for="application-code">Elige una aplicación para acceder a los contenidos <span class="required">*</span></label>
						<select name="applicationcode" class="form-control" title="">
{if (!empty ($AVAILABLE_APPLICATIONS))}
	{foreach $AVAILABLE_APPLICATIONS as $application}
							<option value="{$application->getCode ()}">{$application->getName ()}</option>
	{/foreach}
{/if}
						</select>
					</div>
					<button type="submit" class="btn btn-success nextBtn">De acuerdo! Hazlo por mí <span class="fa fa-arrow-right"></span></button>
				</div>
			</div>
		</form>
	</div>
</div>
{/block}
{/strip}
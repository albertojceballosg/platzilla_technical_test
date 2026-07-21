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
				<form action="index.php" method="post" role="form" onsubmit="return DataSharingUtils.validateRequest (this);">
					<input type="hidden" name="module" value="store" />
					<input type="hidden" name="action" value="invitation" />
					<input type="hidden" name="id" value="{$TOKEN}" />
					<p>{$SENDER_FULL_NAME} te ha invitado a compartir contenidos ({$MODULE_LABEL}) de su sistema de gestión <a href="//www.platzilla.com">Platzilla</a> para trabajar juntos.</p>
{if (!empty ($COMMENTS))}
					<p>Adicionalmente, {$SENDER_FULL_NAME} te dejó dicho: <i>{$COMMENTS}</i></p>
{/if}
					<p>Para poder aceptar esta información debes tener una cuenta en el sistema. Por favor sigue los siguientes pasos para poder recibir la información:</p>
					<div class="form-group col-xs-12">
						<label for="user-name">Tu usuario</label>
						<input type="text" id="user-name" value="{$USER_NAME}" class="form-control" readonly="readonly" />
					</div>
					<div class="form-group col-xs-12 col-md-6">
						<label for="password">Tu contraseña <span class="required">*</span></label>
						<input type="password" id="password" name="password" class="form-control" />
					</div>
					<div class="form-group col-xs-12 col-md-6">
						<label for="repeated-password">Repite tu contraseña <span class="required">*</span></label>
						<input type="password" id="repeated-password" name="repeatedpassword" class="form-control" />
					</div>
					<div class="form-group col-xs-12 col-md-6">
						<label for="first-name">Tu nombre <span class="required">*</span></label>
						<input type="text" id="first-name" name="firstname" class="form-control" />
					</div>
					<div class="form-group col-xs-12 col-md-6">
						<label for="last-name">Tu(s) apellido(s) <span class="required">*</span></label>
						<input type="text" id="last-name" name="lastname" class="form-control" />
					</div>
					<div class="form-group col-xs-12">
						<label for="company-name">Tu empresa</label>
						<input type="text" id="company-name" name="companyname" class="form-control" />
					</div>
					<div class="form-group col-xs-12" style="display: none">
						<label for="application-code">Elige una aplicación para acceder a los contenidos <span class="required">*</span></label>
						<select id="application-code" name="applicationcode" class="form-control">
{if (!empty ($AVAILABLE_APPLICATIONS))}
	{foreach $AVAILABLE_APPLICATIONS as $application}
							<option value="{$application->getCode ()}">{$application->getName ()}</option>
	{/foreach}
{/if}
						</select>
					</div>
					<button type="submit" class="btn btn-success nextBtn">De acuerdo! Crear mi cuenta <span class="fa fa-arrow-right"></span></button>
				</form>
				<div id="mensaje" class="message-container" style="display: none; text-align: center; width: 100%;">
					<p style="text-align: left;">Estamos preparando tu cuenta y tu software. Por favor espera unos instantes y por favor no cierres esta ventana. En seguida podrás disfrutar tu Platzilla y de los contenidos que te compartió {$SENDER_FULL_NAME}</p>
					<img src="themes/images/loading.gif" class="img-responsive" />
				</div>
			</div>
		</div>
	</div>
</div>
{/block}
{block name="scripts"}
<script type="text/javascript" src="modules/instancesdatasharing/data-sharing.js"></script>
{/block}
{/strip}
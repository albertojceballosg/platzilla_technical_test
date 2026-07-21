{strip}
{assign var='emailAddress' value=$CURRENT_USER_EMAIL_ADDRESS}
{assign var='incomingFolderName' value=null}
{assign var='outgoingFolderName' value=null}
{assign var='authenticationMethod' value=Platzilla\MailManager\Type\AuthenticationMethod::PASSWORD_CLEAR_TEXT}
{assign var='hostName' value=null}
{assign var='port' value=null}
{assign var='securityType' value=Platzilla\MailManager\Type\SecurityType::PLAIN}
{assign var='service' value=Platzilla\MailManager\Type\ServiceType::IMAP}
{assign var='userNameType' value=Platzilla\MailManager\Type\UserNameType::EMAIL_ADDRESS}
<form method="post" action="index.php" onsubmit="return WebmailUtils.validateEmailAccount ();">
	<input type="hidden" name="module" value="webmail" />
	<input type="hidden" name="action" value="SaveAccount" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module=webmail&action=AccountListView">Cuenta de correo</a></h1>
			<div class="pull-right">
				<a href="index.php?module=webmail&action=AccountListView" class="btn btn-warning">Cancelar</a>
			</div>
		</div>
	</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="main-box">
		<header class="main-box-header clearfix"><h2 class="pull-left">Cuenta de correo</h2></header>
		<div id="account-settings-container" class="main-box-body">
			<div id="email-address-section" class="row section">
				<div class="col-xs-12 col-md-9">
					<label for="email-address">Indica tu dirección de correo <span class="required">*</span></label>
					<input type="email" id="email-address" name="emailaddress" value="{$emailAddress}" class="form-control" maxlength="255" />
				</div>
				<div class="col-xs-12 col-md-3 button-container">
					<button type="button" class="btn btn-default" onclick="WebmailUtils.getMailServerSettings ();">
						<span><i class="fa fa-play"></i> Obtener configuración</span>
					</button>
					<i class="fa fa-lg"></i>
				</div>
			</div>
			<div id="server-settings-section" class="row section" style="display: none;">
				<input type="hidden" id="incoming-username-type" name="incomingusernametype" value="{$userNameType}" />
				<input type="hidden" id="incoming-service" name="incomingservice" value="{$service}" />
				<input type="hidden" id="incoming-access-token" name="incomingaccesstoken" />
				<label class="col-xs-12">Indica la configuración de tu proveedor de correo</label>
				<div class="col-xs-12 col-md-3">
					<label for="incoming-host-name">Servidor <span class="required">*</span></label>
					<input type="text" id="incoming-host-name" name="incominghostname" value="{$hostName}" class="form-control" />
				</div>
				<div class="col-xs-12 col-md-2">
					<label for="incoming-port">Puerto <span class="required">*</span></label>
					<input type="number" id="incoming-port" name="incomingport" class="form-control" value="{$port}" min="1" max="65535" />
				</div>
				<div class="col-xs-12 col-md-2">
					<label for="incoming-security-type">Seguridad <span class="required">*</span></label>
					<select id="incoming-security-type" name="incomingsecuritytype" class="form-control">
						<option value="plain"{if ($securityType == 'plain')} selected="selected"{/if}>Ninguna</option>
						<option value="ssl"{if ($securityType == 'ssl')} selected="selected"{/if}>SSL/TLS</option>
						<option value="starttls"{if ($securityType == 'starttls')} selected="selected"{/if}>STARTTLS</option>
					</select>
				</div>
				<div class="col-xs-12 col-md-2">
					<label for="incoming-authentication-method">Autenticación <span class="required">*</span></label>
					<select id="incoming-authentication-method" name="incomingauthenticationmethod" class="form-control">
						<option value="password-cleartext"{if ($authenticationMethod == 'password-cleartext')} selected="selected"{/if}>Contraseña plana</option>
						<option value="oauth2"{if ($authenticationMethod == 'oauth2')} selected="selected"{/if}>OAuth2</option>
					</select>
				</div>
				<div class="col-xs-12 col-md-3 button-container">
					<button type="button" class="btn btn-default" onclick="WebmailUtils.testMailServerSettings ();">
						<span><i class="fa fa-play"></i> Probar configuración</span>
					</button>
					<i class="fa fa-lg"></i>
				</div>
			</div>
			<div id="access-token-section" class="row section" style="display: none;">
				<div class="col-xs-12 col-md-9 email-password-stuff" style="display: none;">
					<label for="email-password">Indica la contraseña <span class="required">*</span></label>
					<input type="password" id="email-password" name="incomingpassword" class="form-control" maxlength="255" />
				</div>
				<div class="col-xs-12 col-md-3 button-container email-password-stuff" style="display: none;">
					<button type="button" class="btn btn-default" onclick="WebmailUtils.getAvailableFolders ();">
						<span><i class="fa fa-play"></i> Conectar</span>
					</button>
					<i class="fa fa-lg"></i>
				</div>
				<div class="col-xs-12 text-center oauth2-token-stuff" style="display: none;">
					<p>Tu proveedor de correo requiere que nos autorices para acceder a tus bandejas de mensajes.</p>
					<button type="button" class="btn btn-default" onclick="WebmailUtils.getOauth2Token ();">Vamos a ello</button>
				</div>
			</div>
			<div id="folders-section" class="row section" style="display: none;">
				<h4 class="col-xs-12">Carpetas</h4>
				<div class="col-xs-12 col-md-6">
					<label for="incoming-folder-name">Correos entrantes <span class="required">*</span></label>
					<select id="incoming-folder-name" name="incomingfoldername" class="form-control"{if (empty ($AVAILABLE_FOLDERS))} disabled="disabled"{/if} onchange="WebmailUtils.enableSaveButton ();">
{if (!empty ($AVAILABLE_FOLDERS))}
						<option value="">Selecciona la carpeta</option>
	{foreach $AVAILABLE_FOLDERS as $availableFolder}
						<option value="{$availableFolder}"{if ($availableFolder == $incomingFolderName)} selected="selected"{/if}>{$availableFolder}</option>
	{/foreach}
{/if}
					</select>
				</div>
				<div class="col-xs-12 col-md-6">
					<label for="outgoing-folder-name">Correos salientes <span class="required">*</span></label>
					<select id="outgoing-folder-name" name="outgoingfoldername" class="form-control"{if (empty ($AVAILABLE_FOLDERS))} disabled="disabled"{/if} onchange="WebmailUtils.enableSaveButton ();">
{if (!empty ($AVAILABLE_FOLDERS))}
						<option value="">Selecciona la carpeta</option>
	{foreach $AVAILABLE_FOLDERS as $availableFolder}
						<option value="{$availableFolder}"{if ($availableFolder == $outgoingFolderName)} selected="selected"{/if}>{$availableFolder}</option>
	{/foreach}
{/if}
					</select>
				</div>
			</div>
			<div class="action-bar text-center" style="display: none;">
				<button type="submit" id="btn-save" class="btn btn-info" disabled="disabled">Guardar</button>
				<a href="index.php?module=webmail&action=AccountEditView&reset=true" type="button" class="btn btn-warning">Empezar de nuevo</a>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="webmail/program/js/common.min.js"></script>
<script type="text/javascript" src="modules/webmail/webmail-utils.js?v=1.0.5"></script>
{/strip}
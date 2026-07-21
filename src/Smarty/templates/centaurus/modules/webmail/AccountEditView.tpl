{strip}
{if (isset ($ACCOUNT))}
	{assign var='provider' value=$ACCOUNT->getProvider ()}
	{assign var='accessToken' value=$ACCOUNT->getAccessToken ()}
	{if (!empty ($accessToken))}
		{assign var='accessTokenData' value=base64_encode(json_encode($ACCOUNT->getAccessToken ()->jsonSerialize ()))}
	{else}
		{assign var='accessTokenData' value=null}
	{/if}
	{assign var='emailAddress' value=$ACCOUNT->getEmailAddress ()}
	{assign var='incomingFolderName' value=$ACCOUNT->getIncomingFolderName ()}
	{assign var='outgoingFolderName' value=$ACCOUNT->getOutgoingFolderName ()}
	{assign var='incomingAuthenticationMethod' value=$provider->getIncomingAuthenticationMethod ()}
	{assign var='incomingHostName' value=$provider->getIncomingHostName ()}
	{assign var='incomingPort' value=$provider->getIncomingPort ()}
	{assign var='incomingSecurityType' value=$provider->getIncomingSecurityType ()}
	{assign var='incomingService' value=$provider->getIncomingService ()}
	{assign var='incomingUserNameType' value=$provider->getIncomingUserNameType ()}
	{assign var='outgoingAuthenticationMethod' value=$provider->getOutgoingAuthenticationMethod ()}
	{assign var='outgoingHostName' value=$provider->getOutgoingHostName ()}
	{assign var='outgoingPort' value=$provider->getOutgoingPort ()}
	{assign var='outgoingSecurityType' value=$provider->getOutgoingSecurityType ()}
	{assign var='outgoingService' value=$provider->getOutgoingService ()}
	{assign var='outgoingUserNameType' value=$provider->getOutgoingUserNameType ()}
{else}
	{assign var='accessToken' value=null}
	{assign var='accessTokenData' value=null}
	{assign var='emailAddress' value=$CURRENT_USER_EMAIL_ADDRESS}
	{assign var='incomingFolderName' value=null}
	{assign var='outgoingFolderName' value=null}
	{assign var='incomingAuthenticationMethod' value=null}
	{assign var='incomingHostName' value=null}
	{assign var='incomingPort' value=null}
	{assign var='incomingSecurityType' value=null}
	{assign var='incomingService' value=null}
	{assign var='incomingUserNameType' value=null}
	{assign var='outgoingAuthenticationMethod' value=null}
	{assign var='outgoingHostName' value=null}
	{assign var='outgoingPort' value=null}
	{assign var='outgoingSecurityType' value=null}
	{assign var='outgoingService' value=null}
	{assign var='outgoingUserNameType' value=null}
{/if}
<form method="post" action="index.php" onsubmit="return WebmailUtils.validateEmailAccount ();">
	<input type="hidden" name="module" value="webmail" />
	<input type="hidden" name="action" value="SaveAccount" />
	<input type="hidden" name="return_action" value="{$RETURN_ACTION}" />
	<input type="hidden" name="return_module" value="{$RETURN_MODULE}" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module={$RETURN_MODULE}&action={$RETURN_ACTION}">Cuenta de correo</a></h1>
			<div class="pull-right">
				<a href="index.php?module={$RETURN_MODULE}&action={$RETURN_ACTION}" class="btn btn-warning">Cancelar</a>
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
					<input type="email" id="email-address" name="emailaddress" value="{$emailAddress}" class="form-control" maxlength="255"{if (isset ($ACCOUNT))} readonly="readonly"{/if} />
				</div>
{if (!isset ($ACCOUNT))}
				<div class="col-xs-12 col-md-3 button-container">
					<button type="button" class="btn btn-default" onclick="WebmailUtils.getMailServerSettings ();">
						<span><i class="fa fa-play"></i> Obtener configuración</span>
					</button>
					<i class="fa fa-lg"></i>
				</div>
{/if}
			</div>
			<div id="server-settings-section" class="section" style="display: none;">
				<input type="hidden" id="access-token" name="accesstoken" value="{$accessTokenData}" />
				<input type="hidden" id="incoming-username-type" name="incomingusernametype" value="{$incomingUserNameType}" />
				<input type="hidden" id="incoming-service" name="incomingservice" value="{$incomingService}" />
				<input type="hidden" id="outgoing-username-type" name="outgoingusernametype" value="{$outgoingUserNameType}" />
				<input type="hidden" id="outgoing-service" name="outgoingservice" value="{$outgoingService}" />
				<div class="row">
					<label class="col-xs-12">Indica la configuración de tu servidor de correo entrante</label>
					<div class="col-xs-12 col-md-3">
						<label for="incoming-host-name">Servidor <span class="required">*</span></label>
						<input type="text" id="incoming-host-name" name="incominghostname" value="{$incomingHostName}" class="form-control" />
					</div>
					<div class="col-xs-12 col-md-2">
						<label for="incoming-port">Puerto <span class="required">*</span></label>
						<input type="number" id="incoming-port" name="incomingport" class="form-control" value="{$incomingPort}" min="1" max="65535" />
					</div>
					<div class="col-xs-12 col-md-2">
						<label for="incoming-security-type">Seguridad <span class="required">*</span></label>
						<select id="incoming-security-type" name="incomingsecuritytype" class="form-control">
							<option value="plain"{if ($incomingSecurityType == 'plain')} selected="selected"{/if}>Ninguna</option>
							<option value="ssl"{if ($incomingSecurityType == 'ssl')} selected="selected"{/if}>SSL/TLS</option>
							<option value="starttls"{if ($incomingSecurityType == 'starttls')} selected="selected"{/if}>STARTTLS</option>
						</select>
					</div>
					<div class="col-xs-12 col-md-2">
						<label for="incoming-authentication-method">Autenticación <span class="required">*</span></label>
						<select id="incoming-authentication-method" name="incomingauthenticationmethod" class="form-control">
							<option value="password-cleartext"{if ($incomingAuthenticationMethod == 'password-cleartext')} selected="selected"{/if}>Contraseña plana</option>
							<option value="oauth2"{if ($incomingAuthenticationMethod == 'oauth2')} selected="selected"{/if}>OAuth2</option>
						</select>
					</div>
				</div>
				<div class="row">
					<label class="col-xs-12">Indica la configuración de tu servidor de correo saliente</label>
					<div class="col-xs-12 col-md-3">
						<label for="outgoing-host-name">Servidor <span class="required">*</span></label>
						<input type="text" id="outgoing-host-name" name="outgoinghostname" value="{$outgoingHostName}" class="form-control" />
					</div>
					<div class="col-xs-12 col-md-2">
						<label for="outgoing-port">Puerto <span class="required">*</span></label>
						<input type="number" id="outgoing-port" name="outgoingport" class="form-control" value="{$outgoingPort}" min="1" max="65535" />
					</div>
					<div class="col-xs-12 col-md-2">
						<label for="outgoing-security-type">Seguridad <span class="required">*</span></label>
						<select id="outgoing-security-type" name="outgoingsecuritytype" class="form-control">
							<option value="plain"{if ($outgoingSecurityType == 'plain')} selected="selected"{/if}>Ninguna</option>
							<option value="ssl"{if ($outgoingSecurityType == 'ssl')} selected="selected"{/if}>SSL/TLS</option>
							<option value="starttls"{if ($outgoingSecurityType == 'starttls')} selected="selected"{/if}>STARTTLS</option>
						</select>
					</div>
					<div class="col-xs-12 col-md-2">
						<label for="outgoing-authentication-method">Autenticación <span class="required">*</span></label>
						<select id="outgoing-authentication-method" name="outgoingauthenticationmethod" class="form-control">
							<option value="password-cleartext"{if ($outgoingAuthenticationMethod == 'password-cleartext')} selected="selected"{/if}>Contraseña plana</option>
							<option value="oauth2"{if ($outgoingAuthenticationMethod == 'oauth2')} selected="selected"{/if}>OAuth2</option>
						</select>
					</div>
					<div class="col-xs-12 col-md-3 button-container">
						<button type="button" class="btn btn-default" onclick="WebmailUtils.testMailServerSettings ();">
							<span><i class="fa fa-play"></i> Probar configuración</span>
						</button>
						<i class="fa fa-lg"></i>
					</div>
				</div>
			</div>
			<div id="access-token-section" class="row section"{if (!isset ($ACCOUNT))} style="display: none;"{/if}>
				<div class="col-xs-12 col-md-9 email-password-stuff"{if ($incomingAuthenticationMethod == 'oauth2')} style="display: none;"{/if}>
					<label for="email-password">Indica la contraseña <span class="required">*</span></label>
					<input type="password" id="email-password" name="password" class="form-control" maxlength="255" />
				</div>
				<div class="col-xs-12 col-md-3 button-container email-password-stuff"{if ($incomingAuthenticationMethod == 'oauth2')} style="display: none;"{/if}>
					<button type="button" class="btn btn-default" onclick="WebmailUtils.getAvailableFolders ();">
						<span><i class="fa fa-play"></i> Conectar</span>
					</button>
					<i class="fa fa-lg"></i>
				</div>
				<div class="alert alert-info text-center oauth2-token-stuff with-token"{if ($incomingAuthenticationMethod != 'oauth2') || (empty ($accessToken))} style="display: none;"{/if}>
					Ya tenemos la autorización para acceder a tus bandejas de mensajes
				</div>
				<div class="alert alert-warning text-center oauth2-token-stuff without-token"{if ($incomingAuthenticationMethod != 'oauth2') || (!empty ($accessToken))} style="display: none;"{/if}>
					<p>Tu proveedor de correo requiere que nos autorices para acceder a tus bandejas de mensajes.</p>
					<button type="button" class="btn btn-warning" onclick="WebmailUtils.getOauth2Token ();">Vamos a ello</button>
				</div>
			</div>
			<div id="folders-section" class="row section"{if (!isset ($ACCOUNT)) || (($incomingAuthenticationMethod == 'oauth2') && (empty ($accessToken)))} style="display: none;"{/if}>
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
			<div class="action-bar text-center"{if (!isset ($ACCOUNT)) || (($incomingAuthenticationMethod == 'oauth2') && (empty ($accessToken)))} style="display: none;"{/if}>
				<button type="submit" id="btn-save" class="btn btn-info" disabled="disabled">Guardar</button>
				<a href="index.php?module=webmail&action=AccountEditView&reset=true" type="button" class="btn btn-warning">Empezar de nuevo</a>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="webmail/program/js/common.min.js"></script>
<script type="text/javascript" src="modules/webmail/webmail-utils.js?v=1.0.6"></script>
{/strip}
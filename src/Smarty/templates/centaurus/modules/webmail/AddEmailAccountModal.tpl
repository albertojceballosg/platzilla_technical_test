{strip}
<script type="text/html" id="email-account-modal-template">
<div id="email-account-modal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form method="post" action="index.php" onsubmit="WebmailUtils.saveEmailAccount (this); return false;">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Asociar cuenta de correo electrónico</h4>
				</div>
				<div class="modal-body">
					<div id="credentials-section" class="row section">
						<h4 class="col-xs-12">Datos de acceso</h4>
						<div class="col-xs-12 col-md-5">
							<label for="email-address">Dirección de correo <span class="required">*</span></label>
							<input type="email" id="email-address" class="form-control" maxlength="255" />
						</div>
						<div class="col-xs-12 col-md-5">
							<label for="email-password">Contraseña <span class="required">*</span></label>
							<input type="password" id="email-password" class="form-control" maxlength="255" />
						</div>
						<div class="col-xs-12 col-md-2 button-container">
							<button type="button" class="btn btn-default" onclick="WebmailUtils.getMailServerSettings ();"><i class="fa fa-play"></i></button>
							<i class="fa"></i>
						</div>
					</div>
					<div id="server-settings-section" class="row section" style="display: none;">
						<h4 class="col-xs-12">Servidor de correo</h4>
						<div class="col-xs-12 col-md-3">
							<label for="incoming-host-name">Servidor <span class="required">*</span></label>
							<input type="text" id="incoming-host-name" name="incominghostname" class="form-control" />
						</div>
						<div class="col-xs-12 col-md-2">
							<label for="incoming-port">Puerto <span class="required">*</span></label>
							<input type="number" id="incoming-port" name="incomingport" class="form-control" min="1" max="65535" />
						</div>
						<div class="col-xs-12 col-md-2">
							<label for="incoming-security-type">Seguridad <span class="required">*</span></label>
							<select id="incoming-security-type" name="incomingsecuritytype" class="form-control">
								<option value="plain">Ninguna</option>
								<option value="ssl">SSL/TLS</option>
								<option value="starttls">STARTTLS</option>
							</select>
						</div>
						<div class="col-xs-12 col-md-3">
							<label for="incoming-authentication-method">Autenticación <span class="required">*</span></label>
							<select id="incoming-authentication-method" name="incomingauthenticationmethod" class="form-control">
								<option value="password-cleartext">Contraseña plana</option>
								<option value="password-encrypted">Contraseña encriptada</option>
								<option value="oauth2">OAuth2</option>
							</select>
						</div>
						<div class="col-xs-12 col-md-2 button-container">
							<button type="button" class="btn btn-default" onclick="WebmailUtils.getAvailableFolders ();"><i class="fa fa-play"></i></button>
							<i class="fa"></i>
						</div>
					</div>
					<div id="folders-section" class="row section" style="display: none;">
						<h4 class="col-xs-12">Carpetas</h4>
						<div class="col-xs-12 col-md-6">
							<label for="incoming-folder-name">Correos entrantes <span class="required">*</span></label>
							<select id="incoming-folder-name" name="incomingfoldername" class="form-control" disabled="disabled" onchange="WebmailUtils.enableSaveButton ();"></select>
						</div>
						<div class="col-xs-12 col-md-6">
							<label for="outgoing-folder-name">Correos salientes <span class="required">*</span></label>
							<select id="outgoing-folder-name" name="outgoingfoldername" class="form-control" disabled="disabled" onchange="WebmailUtils.enableSaveButton ();"></select>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" id="btn-save" class="btn btn-primary" disabled="disabled">Guardar</button>
				</div>
			</form>
		</div>
	</div>
</div>
</script>
{/strip}
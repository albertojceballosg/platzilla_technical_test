{strip}
<style type="text/css">
{literal}
	label {
		font-size: 1.11em;
		font-weight: 300;
	}
	.btn {
		margin-left: 5px;
	}
	.main-box > .main-box-header {
		padding-bottom: 20px;
		padding-top: 20px;
	}
{/literal}
</style>
<form method="post" action="index.php" name="EditView" onsubmit="return WebmailUtils.validateEmailProvider (this);">
	<input type="hidden" name="module" value="webmail" />
	<input type="hidden" name="action" value="SaveProvider" />
	<input type="hidden" name="incomingprotocol" value="{if (isset ($PROVIDER))}{$PROVIDER.incomingprotocol}{else}IMAP{/if}" />
	<input type="hidden" name="outgoingprotocol" value="{if (isset ($PROVIDER))}{$PROVIDER.outgoingprotocol}{else}SMTP{/if}" />
	<div class="row">
		<div class="col-xs-12">
			<h1 class="pull-left"><a href="index.php?module=webmail&action=ProviderListView">Proveedor de webmail</a></h1>
			<div class="action-bar pull-right">
				<button type="submit" class="btn btn-info">Guardar</button>
				<a href="index.php?module=webmail&action=ProviderListView&parenttab=Settings" class="btn btn-warning">Cancelar</a>
			</div>
		</div>
	</div>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2>Información general</h2>

				</header>
				<div class="main-box-body">
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="name">Nombre <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="name" name="name" value="{if (isset ($PROVIDER))}{$PROVIDER.name}{/if}" maxlength="50" class="form-control"{if (isset ($PROVIDER)) && (isset ($PROVIDER_NAME))} readonly="readonly"{/if} />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="label">Título <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="label" name="label" value="{if (isset ($PROVIDER))}{$PROVIDER.label}{/if}" maxlength="50" class="form-control" />
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2>Servidor de correos entrantes</h2>
				</header>
				<div class="main-box-body">
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="incominghostname">Servidor <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="incominghostname" name="incominghostname" value="{if (isset ($PROVIDER))}{$PROVIDER.incominghostname}{/if}" maxlength="50" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="incomingport">Puerto <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="number" id="incomingport" name="incomingport" value="{if (isset ($PROVIDER))}{$PROVIDER.incomingport}{/if}" class="form-control" min="1" max="65535" />
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="incomingsecuritytype">Seguridad <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="incomingsecuritytype" name="incomingsecuritytype" class="form-control">
										<option value=""{if (!isset ($PROVIDER)) || (empty ($PROVIDER.incomingsecuritytype))} selected="selected"{/if}>Ninguna</option>
										<option value="ssl"{if (isset ($PROVIDER)) && (strtolower ($PROVIDER.incomingsecuritytype) == 'ssl')} selected="selected"{/if}>{$MOD['ssl']}</option>
										<option value="tls"{if (isset ($PROVIDER)) && (strtolower ($PROVIDER.incomingsecuritytype) == 'tls')} selected="selected"{/if}>{$MOD['tls']}</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="incomingauthenticationmethod">Autenticación <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="incomingauthenticationmethod" name="incomingauthenticationmethod" class="form-control">
										<option value="plain">{$MOD['plain']}</option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="main-box-header clearfix">
					<h2>Servidor de correos salientes</h2>
				</header>
				<div class="main-box-body">
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="outgoinghostname">Servidor <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="text" id="outgoinghostname" name="outgoinghostname" value="{if (isset ($PROVIDER))}{$PROVIDER.outgoinghostname}{/if}" maxlength="50" class="form-control" />
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="outgoingport">Puerto <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<input type="number" id="outgoingport" name="outgoingport" value="{if (isset ($PROVIDER))}{$PROVIDER.outgoingport}{/if}" class="form-control" min="1" max="65535" />
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="outgoingsecuritytype">Seguridad <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="outgoingsecuritytype" name="outgoingsecuritytype" class="form-control">
										<option value=""{if (!isset ($PROVIDER)) || (empty ($PROVIDER.outgoingsecuritytype))} selected="selected"{/if}>Ninguna</option>
										<option value="ssl"{if (isset ($PROVIDER)) && (strtolower ($PROVIDER.outgoingsecuritytype) == 'ssl')} selected="selected"{/if}>{$MOD['ssl']}</option>
										<option value="tls"{if (isset ($PROVIDER)) && (strtolower ($PROVIDER.outgoingsecuritytype) == 'tls')} selected="selected"{/if}>{$MOD['tls']}</option>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="col-md-4">
								<div class="label-input">
									<label for="outgoingauthenticationmethod">Autenticación <span class="required">*</span></label>
								</div>
							</div>
							<div class="form-group col-md-8 field-container">
								<div class="input-group" style="width: 100%;">
									<select id="outgoingauthenticationmethod" name="outgoingauthenticationmethod" class="form-control">
										<option value="plain">{$MOD['plain']}</option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript" src="modules/webmail/webmail.js"></script>
{/strip}
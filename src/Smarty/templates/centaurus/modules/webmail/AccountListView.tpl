{strip}
<div id="email-box" class="clearfix" style="padding-bottom: 20px;">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-envelope-o red-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&action=index&parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li class="active">CUENTAS DE CORREO</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Configurar cuentas de correo del usuario</td>
		</tr>
		</tbody>
	</table>
{if (!empty ($MESSAGE))}
	<div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
		<strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
{/if}
	<div class="main-box clearfix">
		<header class="main-box-header clearfix text-right">
			<div class="pull-right">
				<a href="index.php?module=webmail&action=AccountEditView&parenttab=Settings" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Asociar cuenta</a>&nbsp;
				<a href="index.php?module=emailmanager&action=index&parenttab=Settings" class="btn btn-info">Gestor de correos</a>
			</div>
		</header>
		<div class="main-box-body clearfix">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-email-address">Cuenta</th>
						<th class="col-provider">Datos del proveedor</th>
						<th class="col-folder-names">Carpetas</th>
						<th class="col-actions">Acciones</th>
					</tr>
					</thead>
					<tbody>
{if ($DATA.totalRecords > 0) }
	{foreach $DATA.records as $account}
		{assign var='provider' value=$account->getProvider ()}
					<tr>
						<td class="col-email-address">
							<a href="index.php?module=webmail&action=AccountEditView&emailaddress={$account->getEmailAddress ()}&parenttab=Settings">{$account->getEmailAddress ()}</a>
						</td>
						<td class="col-provider">
							<div class="col-incoming-provider">
								<p style="margin-bottom: 0;">Entrada:</p>
								<p style="margin-bottom: 0;">Protocolo: {strtoupper ($provider->getIncomingService ())}</p>
								<p style="margin-bottom: 0;">Servidor: {$provider->getIncomingHostName ()}</p>
								<p style="margin-bottom: 0;">Port: {$provider->getIncomingPort ()}</p>
								<p style="margin-bottom: 0;">Seguridad: {strtoupper ($provider->getIncomingSecurityType ())}</p>
							</div>
							<div class="col-outgoing-provider">
								<p style="margin-bottom: 0;">Salida:</p>
								<p style="margin-bottom: 0;">Protocolo: {strtoupper ($provider->getOutgoingService ())}</p>
								<p style="margin-bottom: 0;">Servidor: {$provider->getOutgoingHostName ()}</p>
								<p style="margin-bottom: 0;">Port: {$provider->getOutgoingPort ()}</p>
								<p style="margin-bottom: 0;">Seguridad: {strtoupper ($provider->getOutgoingSecurityType ())}</p>
							</div>
						</td>
						<td class="col-folder-names">
							<p style="margin-bottom: 0;">Recibidos: {$account->getIncomingFolderName ()}</p>
							<p style="margin-bottom: 0;">Enviados: {$account->getOutgoingFolderName ()}</p>
						</td>
						<td class="col-actions">
							<form action="index.php" class="form-inline" method="post" onclick="return confirm ('¿Estás seguro que quieres eliminar la cuenta {$account->getEmailAddress ()}?');">
								<input type="hidden" name="module" value="webmail" />
								<input type="hidden" name="action" value="DeleteAccount" />
								<input type="hidden" name="emailaddress" value="{$account->getEmailAddress ()}" />
								<button type="submit" class="btn btn-danger btn-icon" title="Eliminar"><i class="fa fa-trash-o"></i></button>
							</form>
						</td>
					</tr>
	{/foreach}
{else}
					<tr class="lvtColData">
						<td colspan="5" class="text-center">No hay cuentas de correo registradas</td>
					</tr>
{/if}
					</tbody>
				</table>
			</div>
{if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1)}
			<ul class="pagination pull-right">
				<li{if ($DATA.page == 1) } class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=webmail&action=AccountListView&parenttab=Settings&page=1{/if}"><i class="fa fa-step-backward"></i></a>
				</li>
				<li{if ($DATA.page == 1)} class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=webmail&action=AccountListView&parenttab=Settings&page={$DATA.page - 1}{/if}"><i class="fa fa-chevron-left"></i></a>
				</li>
				{for $i=1 to $DATA.totalPages}
					<li{if ($i == $DATA.page)} class="active"{/if}>
						<a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=webmail&action=AccountListView&parenttab=Settings&page={$i}{/if}">{$i}</a>
					</li>
				{/for}
				<li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=webmail&action=AccountListView&parenttab=Settings&page={$DATA.page + 1}{/if}"><i class="fa fa-chevron-right"></i></a>
				</li>
				<li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=webmail&action=AccountListView&parenttab=Settings&page={$DATA.totalPages}{/if}"><i class="fa fa-step-forward"></i></a>
				</li>
			</ul>
{/if}
		</div>
	</div>
</div>
{/strip}
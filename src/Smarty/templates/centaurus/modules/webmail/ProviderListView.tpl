{strip}
<style type="text/css">
	.filters .btn {
		margin-left: 5px;
	}
	.col-configuration {
		width: 30em;
	}
	.col-configuration p {
		margin: 0;
	}
	.col-actions {
		width: 12em;
	}
	.action {
		display:    inline-block;
		list-style: none;
	}
	.action .btn {
		font-size:   14px;
		height:      27px;
		line-height: 27px;
		margin:      0 5px 0 0;
		padding:     0;
		text-align:  center;
		width:       27px;
	}
</style>
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;">
					<i class="fa fa-cloud purple-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li class="active">WEBMAIL</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Gestión de proveedores de correo</td>
		</tr>
		</tbody>
	</table>
{if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
	<div class="row">
		<div class="alert {if (isset ($IS_ERROR)) && ($IS_ERROR)}alert-danger{else}alert-success{/if}">
			<strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
		</div>
	</div>
{/if}
	<div class="main-box clearfix">
		<header class="main-box-header clearfix filters">
			<div class="col-xs-6">
				<form name="filters" action="index.php" method="GET" class="form-inline">
					<input type="hidden" name="module" value="webmail" />
					<input type="hidden" name="action" value="ProviderListView" />
					<div class="form-group">
						<input type="text" name="keyword" value="{$SEARCH_KEYWORD}" class="form-control" placeholder="Palabras clave">
					</div>
					<input type="submit" value="Buscar" class="btn btn-primary">
				</form>
			</div>
			<div class="col-xs-6 text-right">
				<a href="index.php?module=webmail&action=ProviderEditView" class="btn btn-primary">
					<i class="fa fa-plus-circle"></i> Registrar proveedor
				</a>
			</div>
		</header>
		<div class="main-box-body clearfix" id="ListViewContents">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-name"><b>Nombre</b></th>
						<th class="col-configuration"><b>Servidor de entrada</b></th>
						<th class="col-configuration"><b>Servidor de salida</b></th>
						<th class="col-actions">Acciones</th>
					</tr>
					</thead>
					<tbody>
{if ($DATA.totalRecords > 0) }
	{foreach $DATA.records as $provider}
					<tr class="lvtColData">
						<td class="col-name">{$provider.label} ({$provider.name})</td>
						<td class="col-configuration">
							<p>Protocolo: {$provider.incomingprotocol}</p>
							<p>Servidor: {$provider.incominghostname}</p>
							<p>Puerto: {$provider.incomingport}</p>
							<p>Seguridad: {if (!empty ($provider.incomingsecuritytype))}{$MOD[$provider.incomingsecuritytype|lower]}{else}Ninguna{/if}</p>
							<p>Autenticación: {if (!empty ($provider.incomingauthenticationmethod))}{$MOD[$provider.incomingauthenticationmethod|lower]}{else}Ninguno{/if}</p>
						</td>
						<td class="col-configuration">
							<p>Protocolo: {$provider.outgoingprotocol}</p>
							<p>Servidor: {$provider.outgoinghostname}</p>
							<p>Puerto: {$provider.outgoingport}</p>
							<p>Seguridad: {if (!empty ($provider.outgoingsecuritytype))}{$MOD[$provider.outgoingsecuritytype|lower]}{else}Ninguna{/if}</p>
							<p>Autenticación: {if (!empty ($provider.outgoingauthenticationmethod))}{$MOD[$provider.outgoingauthenticationmethod|lower]}{else}Ninguno{/if}</p>
						</td>
						<td class="col-actions">
							<ul class="actions">
								<li class="action">
									<a href="index.php?module=webmail&action=ProviderEditView&name={$provider.name}" class="btn btn-primary" title="Editar">
										<i class="fa fa-pencil"></i>
									</a>
								</li>
								<li class="action">
									<form method="post" action="index.php" onsubmit="return WebmailUtils.deleteProvider ('{$provider.name}');">
										<input type="hidden" name="module" value="webmail" />
										<input type="hidden" name="action" value="DeleteProvider" />
										<input type="hidden" name="name" value="{$provider.name}" />
										<input type="hidden" name="Ajax" value="true" />
										<button class="btn btn-danger" type="submit" title="Eliminar">
											<i class="fa fa-trash-o"></i>
										</button>
									</form>
								</li>
							</ul>
						</td>
					</tr>
	{/foreach}
{else}
					<tr class="lvtColData">
						<td colspan="3" class="text-center">No hay proveedores registradas</td>
					</tr>
{/if}
					</tbody>
				</table>
			</div>
{if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1) }
			<ul class="pagination pull-right">
				<li{if ($DATA.page == 1) } class="disabled"{/if}>
					<a href="{if ($DATA.page == 1) }javascript:;{else}index.php?module=webmail&action=ProviderListView&page=1{/if}">
						<i class="fa fa-step-backward"></i>
					</a>
				</li>
				<li{if ($DATA.page == 1)} class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=webmail&action=ProviderListView&page={$DATA.page - 1}{/if}">
						<i class="fa fa-chevron-left"></i>
					</a>
				</li>
{for $i=1 to $DATA.totalPages}
				<li{if ($i == $DATA.page)} class="active"{/if}>
					<a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=webmail&action=ProviderListView&page={$i}{/if}">
						{$i}
					</a>
				</li>
{/for}
				<li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=webmail&action=ProviderListView&page={$DATA.page + 1}{/if}">
						<i class="fa fa-chevron-right"></i>
					</a>
				</li>
				<li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=webmail&action=ProviderListView&page={$DATA.totalPages}{/if}">
						<i class="fa fa-step-forward"></i>
					</a>
				</li>
			</ul>
{/if}
		</div>
	</div>
</div>
<script type="text/javascript" src="modules/webmail/webmail.js"></script>
{/strip}
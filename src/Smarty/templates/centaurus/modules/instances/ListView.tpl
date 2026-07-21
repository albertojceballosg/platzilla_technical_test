{strip}
<div id="email-box" class="clearfix" style="padding-bottom: 20px;">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-cogs red-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a></li>
					<li class="active">ADMINISTRADOR DE INSTANCIAS</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Instancias de clientes existentes en Platzilla</td>
		</tr>
		</tbody>
	</table>
{if (!empty ($MESSAGE))}
	<div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
		<strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
{/if}
	<div class="main-box clearfix">
		<header class="main-box-header clearfix">
			<div class="col-xs-12 col-md-6">
				<form name="filters" action="index.php" method="GET" class="form-inline">
					<input type="hidden" name="module" value="instances" />
					<input type="hidden" name="action" value="ListView" />
					<input type="hidden" name="parenttab" value="Settings" />
					<select name="fieldname" class="form-control" title="">
						<option value="code"{if ($FIELD_NAME == 'code')} selected="selected"{/if}>Código de instancia</option>
						<option value="name"{if ($FIELD_NAME == 'name')} selected="selected"{/if}>Empresa</option>
						<option value="administrator"{if ($FIELD_NAME == 'administrator')} selected="selected"{/if}>Email</option>
						<option value="registrationdate"{if ($FIELD_NAME == 'registrationdate')} selected="selected"{/if}>Fecha de registro</option>
						<option value="source"{if ($FIELD_NAME == 'source')} selected="selected"{/if}>Origen</option>
						<option value="status"{if ($FIELD_NAME == 'status')} selected="selected"{/if}>Status</option>
					</select>
					<input type="text" name="keyword" value="{$KEYWORD}" class="form-control" placeholder="Palabras clave" style="margin: 0 5px;" />
					<button type="submit" class="btn btn-primary">Buscar</button>
				</form>
			</div>
		</header>
		<div class="main-box-body clearfix" id="ListViewContents">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-label"><strong>Empresa / Código</strong></th>
						<th class="col-registration-date"><strong>Fecha de registro</strong></th>
						<th class="col-summary"><strong>Detalles</strong></th>
						<th class="col-source"><strong>Origen</strong></th>
						<th class="col-status"><strong>Status</strong></th>
						<th class="col-actions">Acciones</th>
					</tr>
					</thead>
					<tbody>
{if ($DATA.totalRecords > 0) }
	{foreach $DATA.records as $instance}
		{assign var='administrator' value=$instance->getAdministrator ()}
		{assign var='billingPlan' value=$instance->getBillingPlan ()}
		{assign var='status' value=$instance->getStatus ()}
					<tr>
						<td class="col-label">
		{if (!empty ($administrator))}
							<a href="index.php?module=instances&action=DetailsView&record={$instance->getId ()}&code={$instance->getCode ()}&parenttab=Settings">{$instance->getCode ()}</a>
		{else}
							{$instance->getCode ()}
		{/if}
							<p style="margin-bottom: 0;">{$instance->getName ()}</p>
							<p style="font-size: 0.85em; font-style: italic; margin-bottom: 0;">{if (isset ($administrator))}{$administrator->getUserName ()}{else}(La base de datos no existe){/if}</p>
						</td>
						<td class="col-registration-date">{$instance->getRegistrationDate ()->format ('d/m/Y')}</td>
						<td class="col-summary">
							<p style="margin-bottom: 0;">Plan: {$billingPlan->getName ()}</p>
		{if (!empty ($administrator))}
			{assign var='usageDays' value=floor($instance->getUsageTime () / 86400)}
			{assign var='usageHours' value=floor($instance->getUsageTime () / 3600 % 24)}
			{assign var='usageMinutes' value=floor($instance->getUsageTime () / 60 % 60)}
			{assign var='usageSeconds' value=floor($instance->getUsageTime () % 60)}
							<p style="margin-bottom: 0;">Usuarios: {count ($instance->getUsers ()) + 1} / {if ($billingPlan->getTotalUsers () != -1)}{$billingPlan->getTotalUsers ()}{else}&#x221e;{/if}</p>
							<p style="margin-bottom: 0;">Aplicaciones: {count ($instance->getApplications ())} / {if ($TOTAL_APPLICATIONS != -1)}{$TOTAL_APPLICATIONS}{else}&#x221e;{/if}</p>
							<p style="margin-bottom: 0;">Registros: {$instance->getTotalRecords ()}</p>
							<p style="margin-bottom: 0;">Tiempo: {$usageDays}d {$usageHours}h {$usageMinutes}m {$usageSeconds}s</p>
		{/if}
						</td>
						<td class="col-source">{$instance->getSource ()}</td>
						<td class="col-status"><span class="label label-{if ($status == 'verified')}success{else}info{/if}">{if (isset ($MOD[$status]))}{$MOD[$status]}{else}{$status}{/if}</span></td>
						<td class="col-actions">
							<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres actualizar la instancia {$instance->getCode ()}?');" style="display: inline;">
								<input type="hidden" name="module" value="instances" />
								<input type="hidden" name="action" value="Update" />
								<input type="hidden" name="code" value="{$instance->getCode ()}" />
								<input type="hidden" name="Ajax" value="true" />
								<button type="submit" class="btn btn-info btn-icon" title="Actualizar"><i class="fa fa-refresh"></i></button>
							</form>
							<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres eliminar la instancia {$instance->getCode ()}?');" style="display: inline;">
								<input type="hidden" name="module" value="instances" />
								<input type="hidden" name="action" value="Delete" />
								<input type="hidden" name="code" value="{$instance->getCode ()}" />
								<input type="hidden" name="Ajax" value="true" />
								<button type="submit" class="btn btn-danger btn-icon" title="Eliminar"><i class="fa fa-trash-o"></i></button>
							</form>
							<form action="index.php" method="post" onclick="return confirm ('{if $instance->isPattern () eq 1}Eliminar como patrón{else}Convertir en patrón{/if} a la instancia {$instance->getCode ()} ?');" style="display: inline;">
								<input type="hidden" name="module" value="instances" />
								<input type="hidden" name="action" value="TogglePatternAttribute" />
								<input type="hidden" name="page" value="{$PAGE}" />
								<input type="hidden" name="code" value="{$instance->getCode ()}" />
								<input type="hidden" name="Ajax" value="true" />
								<button type="submit" class="btn btn-{if ($instance->isPattern ())}success{else}primary{/if} btn-icon" title="{if ($instance->isPattern ())}Eliminar como patrón{else}Convertir en patrón{/if}"><i class="fa fa-tasks" aria-hidden="true"></i></button>
							</form>
							<button type="button" class="btn btn-warning btn-icon" title="Acceder a la instancia" onclick="InstancesUtils.createAccessToken ('{$instance->getCode ()}');"><i class="fa fa-user" aria-hidden="true"></i></button>
						</td>
					</tr>
	{/foreach}
{else}
					<tr class="lvtColData">
						<td colspan="6" class="text-center">No hay instancias registradas</td>
					</tr>
{/if}
					</tbody>
				</table>
			</div>
{if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1) }
	{if (!empty ($KEYWORD))}
		{assign var='keywordUrlPart' value="&keyword=$KEYWORD"}
	{else}
		{assign var='keywordUrlPart' value=''}
	{/if}
	{if (!empty ($FIELD_NAME))}
		{assign var='fieldNameUrlPart' value="&fieldname=$FIELD_NAME"}
	{else}
		{assign var='fieldNameUrlPart' value=''}
	{/if}
			<div id="pager-dv" class="text-center selection-stuff">
				<ul id="pager" class="pagination">{$PAGINATOR}</ul>
			</div>
{/if}
		</div>
	</div>
</div>
<script type="text/html" id="create-access-token-modal-template">
<div id="create-access-token-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Acceder a instancia</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<label for="create-access-token-modal-url" class="col-xs-12">Utiliza este URL en una ventana de otro navegador</label>
					<input type="text" id="create-access-token-modal-url" value="" readonly="readonly" class="form-control col-xs-12" />
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
</script>
<script type="text/javascript" src="modules/instances/instances.js"></script>
{/strip}
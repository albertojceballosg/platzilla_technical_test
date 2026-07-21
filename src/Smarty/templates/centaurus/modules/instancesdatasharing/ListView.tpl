{strip}
<style type="text/css">
	.btn.btn-icon {
		font-size:   14px;
		height:      27px;
		line-height: 27px;
		margin:      0 5px 0 0;
		padding:     0;
		text-align:  center;
		width:       27px;
	}
</style>
<div id="email-box" class="clearfix" style="padding-bottom: 20px;">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-exchange green-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a></li>
					<li class="active">ADMINISTRADOR DE DATOS COMPARTIDOS</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Configurar mecanismos para compartir con otros usuarios de Platzilla</td>
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
					<input type="hidden" name="module" value="instancesdatasharing" />
					<input type="hidden" name="action" value="ListView" />
					<input type="hidden" name="parenttab" value="Settings" />
					<div class="form-group">
						<input type="text" name="keyword" value="{$SEARCH_KEYWORD}" class="form-control" placeholder="Palabras clave">
					</div>
					<input type="submit" value="Buscar" class="btn btn-primary">
				</form>
			</div>
			<div class="col-xs-12 col-md-6 text-right">
				<a href="index.php?module=instancesdatasharing&action=RuleEditView&parenttab=Settings" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Crear regla</a>
			</div>
		</header>
		<div class="main-box-body clearfix" id="ListViewContents">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-name"><b>Nombre</b></th>
						<th class="col-modulename"><b>Módulo</b></th>
						<th class="col-status"><b>Status</b></th>
						<th class="col-actions">Acciones</th>
					</tr>
					</thead>
					<tbody>
{if ($DATA.totalRecords > 0) }
	{foreach $DATA.records as $rule}
		{assign var='status' value=$rule->getStatus ()}
		{assign var='moduleLabel' value=$rule->getModuleName ()}
		{foreach $AVAILABLE_MODULES as $availableModule}
			{if ($rule->getModuleName () == $availableModule->getName ())}
				{assign var='moduleLabel' value=$availableModule->getLabel ()}
				{break}
			{/if}
		{/foreach}
					<tr>
						<td class="col-name">
							<a href="index.php?module=instancesdatasharing&action=RuleEditView&record={$rule->getId ()}&parenttab=Settings">{$rule->getName ()}</a>
						</td>
						<td class="col-modulename">{$moduleLabel}</td>
						<td class="col-status"><span class="label label-{if ($status == DataSharingRule::STATUS_ACTIVE)}success{else}warning{/if}">{if (isset ($MOD[$status]))}{$MOD[$status]}{else}{$status}{/if}</span></td>
						<td class="col-actions">
							<form action="index.php" method="post" onclick="return confirm ('¿Estás seguro que quieres eliminar la regla seleccionada?');" style="display: inline;">
								<input type="hidden" name="module" value="instancesdatasharing" />
								<input type="hidden" name="action" value="DeleteRule" />
								<input type="hidden" name="record" value="{$rule->getId ()}" />
								<input type="hidden" name="Ajax" value="true" />
								<button type="submit" class="btn btn-danger btn-icon" title="Eliminar"><i class="fa fa-trash-o"></i></button>
							</form>
						</td>
					</tr>
	{/foreach}
{else}
						<tr class="lvtColData">
							<td colspan="4" class="text-center">No hay reglas registradas</td>
						</tr>
{/if}
					</tbody>
				</table>
			</div>
{if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1) }
	{if (!empty ($SEARCH_KEYWORD))}
		{assign var='keywordUrlPart' value="&keyword=$SEARCH_KEYWORD"}
	{else}
		{assign var='keywordUrlPart' value=''}
	{/if}
			<ul class="pagination pull-right">
				<li{if ($DATA.page == 1) } class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=instancesdatasharing&action=ListView&parenttab=Settings{$keywordUrlPart}&page=1{/if}"><i class="fa fa-step-backward"></i></a>
				</li>
				<li{if ($DATA.page == 1)} class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=instancesdatasharing&action=ListView&parenttab=Settings{$keywordUrlPart}&page={$DATA.page - 1}{/if}"><i class="fa fa-chevron-left"></i></a>
				</li>
				{for $i=1 to $DATA.totalPages}
					<li{if ($i == $DATA.page)} class="active"{/if}>
						<a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=instancesdatasharing&action=ListView&parenttab=Settings{$keywordUrlPart}&page={$i}{/if}">{$i}</a>
					</li>
				{/for}
				<li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=instancesdatasharing&action=ListView&parenttab=Settings{$keywordUrlPart}&page={$DATA.page + 1}{/if}"><i class="fa fa-chevron-right"></i></a>
				</li>
				<li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=instancesdatasharing&action=ListView&parenttab=Settings{$keywordUrlPart}&page={$DATA.totalPages}{/if}"><i class="fa fa-step-forward"></i></a>
				</li>
			</ul>
{/if}
		</div>
	</div>
</div>
{/strip}
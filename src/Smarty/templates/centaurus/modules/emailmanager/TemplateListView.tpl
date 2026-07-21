{strip}
<style type="text/css">
	.filters .btn {
		margin-left: 5px;
	}
	.col-name {
		width: 15em;
	}
	.col-language {
		width: 8em;
	}
	.col-default {
		width: 9em;
	}
	.col-attachments {
		width: 5em;
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
	var {
		border:          1px solid #CCCCCC;
		border-radius:   4px;
		display:         inline-block;
		margin-bottom:   0;
		padding:         2px 4px;
		text-align:      center;
		vertical-align:  middle;
		white-space:     nowrap;
		text-decoration: none;
	}
	var:before {
		content: '<';
	}
	var:after {
		content: '>';
	}
</style>
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;">
					<i class="fa fa-envelope-o emerald-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li>
						<a href="index.php?module=emailmanager&amp;action=index&amp;parenttab=Settings">{$MOD.ModuleName}</a>
					</li>
					<li class="active">PLANTILLAS</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Gestión de plantillas para el envío de correo</td>
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
					<input type="hidden" name="module" value="emailmanager" />
					<input type="hidden" name="action" value="TemplateListView" />
					<input type="hidden" name="parenttab" value="Settings" />
					<div class="form-group">
						<input type="text" name="keyword" value="{$SEARCH_KEYWORD}" class="form-control" placeholder="Palabras clave">
					</div>
					<input type="submit" value="Buscar" class="btn btn-primary">
				</form>
			</div>
			<div class="col-xs-6 text-right">
				<a href="index.php?module=emailmanager&action=TemplateEditView&parenttab=Settings" class="btn btn-primary"><i class="fa fa-plus-circle"></i> Crear plantilla</a>
				<a href="index.php?module=emailmanager&action=EmailHistoryListView&parenttab=Settings" class="btn btn-success">Ir a historial</a>
			</div>
		</header>
		<div class="main-box-body clearfix" id="ListViewContents">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-name"><b>Nombre</b></th>
						<th class="col-language"><b>Lenguaje</b></th>
						<th class="col-subject"><b>Asunto</b></th>
						<th class="col-default"><b>Encabezado</b></th>
						<th class="col-default"><b>Pie de página</b></th>
						<th class="col-attachments"><b>Anexos</b></th>
						<th class="col-actions">Acciones</th>
					</tr>
					</thead>
					<tbody>
{if ($DATA.totalRecords > 0) }
	{foreach $DATA.records as $template}
					<tr class="lvtColData">
						<td class="col-name">{$template.templatename}</td>
						<td class="col-language">{if (!empty ($MOD[$template.language]))}{$MOD[$template.language]}{/if}</td>
						<td class="col-subject">{$template.subject}</td>
						<td class="col-default text-center">{if ($template.adddefaultheader)}Sí{else}No{/if}</td>
						<td class="col-default text-center">{if ($template.adddefaultfooter)}Sí{else}No{/if}</td>
						<td class="col-attachments text-center">{if (!empty ($template.attachments))}<span class="label label-default">{$template.attachments|json_decode|count}</span>{/if}</td>
						<td class="col-actions">
							<ul class="actions">
								<li class="action">
									<a href="index.php?module=emailmanager&action=EmailHistoryListView&record={$template.templateid}&parenttab=Settings&criteria[templatename]={$template.templatename|urlencode}-{$template.language|urlencode}" class="btn btn-default" title="Historial de envíos"><i class="fa fa-search"></i></a>
								</li>
								<li class="action">
									<a href="index.php?module=emailmanager&action=TemplateEditView&record={$template.templateid}&parenttab=Settings" class="btn btn-primary" title="Editar"><i class="fa fa-pencil"></i></a>
								</li>
								<li class="action">
									<a href="index.php?module=emailmanager&action=DuplicateTemplate&record={$template.templateid}&parenttab=Settings" class="btn btn-primary" title="Duplicar"><i class="fa fa-copy"></i></a>
								</li>
		{if ($template.scope == 'USER')}
								<li class="action">
									<form method="post" action="index.php" onsubmit="return EmailManagerUtils.deleteTemplate ('{$template.templatename}');">
										<input type="hidden" name="module" value="emailmanager" />
										<input type="hidden" name="action" value="DeleteTemplate" />
										<input type="hidden" name="record" value="{$template.templateid}" />
										<input type="hidden" name="Ajax" value="true" />
										<button class="btn btn-danger" type="submit" title="Eliminar">
											<i class="fa fa-trash-o"></i>
										</button>
									</form>
								</li>
		{/if}
							</ul>
						</td>
					</tr>
	{/foreach}
{else}
					<tr class="lvtColData">
						<td colspan="7" class="text-center">No hay plantillas registradas</td>
					</tr>
{/if}
					</tbody>
				</table>
			</div>
{if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1) }
			<ul class="pagination pull-right">
				<li{if ($DATA.page == 1) } class="disabled"{/if}>
					<a href="{if ($DATA.page == 1) }javascript:;{else}index.php?module=emailmanager&action=TemplateListView&parenttab=Settings&page=1{/if}">
						<i class="fa fa-step-backward"></i>
					</a>
				</li>
				<li{if ($DATA.page == 1)} class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=emailmanager&action=TemplateListView&parenttab=Settings&page={$DATA.page - 1}{/if}">
						<i class="fa fa-chevron-left"></i>
					</a>
				</li>
{for $i=1 to $DATA.totalPages}
				<li{if ($i == $DATA.page)} class="active"{/if}>
					<a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=emailmanager&action=TemplateListView&parenttab=Settings&page={$i}{/if}">
						{$i}
					</a>
				</li>
{/for}
				<li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=emailmanager&action=TemplateListView&parenttab=Settings&page={$DATA.page + 1}{/if}">
						<i class="fa fa-chevron-right"></i>
					</a>
				</li>
				<li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=emailmanager&action=TemplateListView&parenttab=Settings&page={$DATA.totalPages}{/if}">
						<i class="fa fa-step-forward"></i>
					</a>
				</li>
			</ul>
{/if}
		</div>
	</div>
</div>
<script type="text/javascript" src="modules/emailmanager/emailmanager.js"></script>
{/strip}
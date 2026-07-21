{strip}
<link rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" type="text/css" />
<style type="text/css">
	.filters .form-control {
		width: 100%;
	}
	.filters .btn {
		margin-top: 25px;
	}
	.col-id {
		width: 4em;
	}
	.col-date {
		width: 7em;
	}
	.col-templatename {
		width: 15em;
	}
	.col-to {
		width: 20em;
	}
	.col-status {
		width: 10em;
	}
	.col-attachments {
		width: 5em;
	}
	.col-actions {
		width: 7em;
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
	#email-viewer > .modal-dialog {
		width: 90%;
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
					<li class="active">HISTORIAL DE ENVÍOS</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">Registro de correos enviados</td>
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
			<div class="col-xs-9">
				<form name="filters" action="index.php" method="GET" class="form-inline">
					<input type="hidden" name="module" value="emailmanager" />
					<input type="hidden" name="action" value="EmailHistoryListView" />
					<input type="hidden" name="parenttab" value="Settings" />
					<div class="col-xs-2 form-group">
						<label for="criteria-date">Fecha</label>
						<input type="text" id="criteria-date" name="criteria[date]" value="{if (isset ($CRITERIA.date))}{$CRITERIA.date}{/if}" class="form-control date-field" />
					</div>
					<div class="col-xs-3 form-group">
						<label for="criteria-templatename">Plantilla</label>
						<select id="criteria-templatename" name="criteria[templatename]" class="form-control">
							<option value=""></option>
{foreach $AVAILABLE_TEMPLATES as $template}
							<option value="{$template.templatename}-{$template.language}"{if (isset ($CRITERIA.templatename)) && ($CRITERIA.templatename == "{$template.templatename}-{$template.language}" )} selected="selected"{/if}>{$template.templatename} ({$template.language|upper})</option>
{/foreach}
						</select>
					</div>
					<div class="col-xs-3 form-group">
						<label for="criteria-email">Correo electrónico</label>
						<input type="text" id="criteria-email" name="criteria[email]" value="{if (isset ($CRITERIA.email))}{$CRITERIA.email}{/if}" class="form-control" />
					</div>
					<div class="col-xs-3 form-group">
						<label for="criteria-status">Estado</label>
						<select id="criteria-status" name="criteria[status]" class="form-control">
							<option value=""></option>
{foreach $AVAILABLE_STATUSES as $status}
							<option value="{$status}"{if (isset ($CRITERIA.status)) && ($CRITERIA.status == $status)} selected="selected"{/if}>{$MOD[$status]}</option>
{/foreach}
						</select>
					</div>
					<div class="col-xs-1 form-group">
						<input type="submit" value="Buscar" class="btn btn-primary" />
					</div>
				</form>
			</div>
			<div class="col-xs-3 text-right">
				<a href="index.php?module=emailmanager&action=TemplateListView&parenttab=Settings" class="btn btn-success">Ir a plantillas</a>&nbsp;
				<a href="index.php?module=webmail&action=AccountListView&parenttab=Settings" title="Configurar cuentas de correo del usuario" class="btn btn-info">Cuentas de correo</a>
			</div>
		</header>
		<div class="main-box-body clearfix" id="ListViewContents">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-id"><b>ID</b></th>
						<th class="col-date"><b>Fecha</b></th>
						<th class="col-templatename"><b>Plantilla</b></th>
						<th class="col-to"><b>Destinatarios</b></th>
						<th class="col-subject"><b>Asunto</b></th>
						<th class="col-attachments"><b>Anexos</b></th>
						<th class="col-status"><b>Estado</b></th>
						<th class="col-actions">Acciones</th>
					</tr>
					</thead>
					<tbody>
{if ($DATA.totalRecords > 0) }
	{foreach $DATA.records as $email}
					<tr class="lvtColData">
						<td class="col-id text-right">{$email.emailid}</td>
						<td class="col-date">{$email.createdon|date_format: 'd/m/Y h:i:s a'}</td>
						<td class="col-templatename">{$email.templatename} ({$email.language|upper})</td>
						<td class="col-to">{join ('<br />', explode (',', $email.to))}</td>
						<td class="col-subject">{$email.subject}</td>
						<td class="col-attachments text-center">{if (!empty ($email.attachments))}<span class="label label-default">{$email.attachments|count}</span>{/if}</td>
						<td class="col-status">
							<span class="label label-{if (empty ($email.status))}default{elseif ($email.status == 'SENT')}success{else}danger{/if}">{if (!empty ($email.status))}{$MOD[$email.status]}{else}{$MOD['NOT SENT']}{/if}</span>
						</td>
						<td class="col-actions">
							<ul class="actions">
								<li class="action">
									<a href="index.php?module=emailmanager&action=EmailHistoryDetailView&record={$email.emailid}&parenttab=Settings&Ajax=true" class="btn btn-default" title="Ver detalles" data-remote="false" data-toggle="modal" data-target="#email-viewer">
										<i class="fa fa-search"></i>
									</a>
								</li>
							</ul>
						</td>
					</tr>
	{/foreach}
{else}
					<tr class="lvtColData">
						<td colspan="8" class="text-center">No hay correos enviados</td>
					</tr>
{/if}
					</tbody>
				</table>
			</div>
{if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1) }
			<ul class="pagination pull-right">
				<li{if ($DATA.page == 1) } class="disabled"{/if}>
					<a href="{if ($DATA.page == 1) }javascript:;{else}index.php?module=emailmanager&action=EmailHistoryListView&parenttab=Settings&page=1{/if}">
						<i class="fa fa-step-backward"></i>
					</a>
				</li>
				<li{if ($DATA.page == 1)} class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=emailmanager&action=EmailHistoryListView&parenttab=Settings&page={$DATA.page - 1}{/if}">
						<i class="fa fa-chevron-left"></i>
					</a>
				</li>
{for $i=1 to $DATA.totalPages}
				<li{if ($i == $DATA.page)} class="active"{/if}>
					<a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=emailmanager&action=EmailHistoryListView&parenttab=Settings&page={$i}{/if}">
						{$i}
					</a>
				</li>
{/for}
				<li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=emailmanager&action=EmailHistoryListView&parenttab=Settings&page={$DATA.page + 1}{/if}">
						<i class="fa fa-chevron-right"></i>
					</a>
				</li>
				<li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=emailmanager&action=EmailHistoryListView&parenttab=Settings&page={$DATA.totalPages}{/if}">
						<i class="fa fa-step-forward"></i>
					</a>
				</li>
			</ul>
{/if}
		</div>
	</div>
</div>
<div class="modal fade" id="email-viewer" tabindex="-1" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal-title">Detalles del correo</h4>
			</div>
			<div class="modal-body">
				<div class="text-center">Cargando...</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>
<script type="text/javascript" src="modules/emailmanager/emailmanager.js"></script>
{/strip}
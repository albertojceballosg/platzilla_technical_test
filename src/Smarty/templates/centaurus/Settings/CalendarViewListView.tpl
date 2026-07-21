{strip}
<style type="text/css">
	.col-modulename {
		width: 15em;
	}
	.col-field {
		width: 15em;
	}
	.col-actions {
		width: 10em;
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
					<i class="fa fa-calendar red-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li class="active">{$MOD.LBL_CALENDAR_VIEW|upper}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.LBL_CALENDAR_VIEW_DESCRIPTION}</td>
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
		<header class="main-box-header clearfix">
			<div class="col-xs-6">
				<form name="filters" action="index.php" method="GET" class="form-inline">
					<input type="hidden" name="module" value="Settings" />
					<input type="hidden" name="action" value="CalendarViewListView" />
					<input type="hidden" name="parenttab" value="Settings" />
					<div class="form-group">
						<input type="text" name="keyword" value="{$SEARCH_KEYWORD}" class="form-control" placeholder="Palabras clave">
					</div>
					<input type="submit" value="Buscar" class="btn btn-primary">
				</form>
			</div>
			<div class="col-xs-6 text-right">
				<a href="index.php?module=Settings&action=CalendarViewEditView&parenttab=Settings" class="btn btn-primary">
					<i class="fa fa-plus-circle"></i> Crear vista
				</a>
			</div>
		</header>
		<div class="main-box-body clearfix" id="ListViewContents">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead>
					<tr>
						<th class="col-label"><b>Nombre</b></th>
						<th class="col-modulename"><b>Módulo</b></th>
						<th class="col-field"><b>Campo título</b></th>
						<th class="col-field"><b>Campo desde</b></th>
						<th class="col-field"><b>Campo hasta</b></th>
						<th class="col-actions">Acciones</th>
					</tr>
					</thead>
					<tbody>
{if ($DATA.totalRecords > 0) }
	{foreach $DATA.records as $view}
					<tr class="lvtColData">
						<td class="col-label">{$view.label}</td>
						<td class="col-modulename">{$view.modulelabel|getTranslatedString: $view.modulelabel}</td>
						<td class="col-field">{$view.titlefieldlabel|getTranslatedString: $view.titlemodulename}{if ($view.titlemodulename != $view.modulename)} ({$view.titlemodulelabel|getTranslatedString: $view.titlemodulename}){/if}</td>
						<td class="col-field">{$view.fromfieldlabel|getTranslatedString: $view.frommodulename}{if ($view.frommodulename != $view.modulename)} ({$view.frommodulelabel|getTranslatedString: $view.frommodulename}){/if}</td>
						<td class="col-field">{if (!empty ($view.tofieldname))}{$view.tofieldlabel|getTranslatedString: $view.tomodulename}{if ($view.tomodulename != $view.modulename)} ({$view.tomodulelabel|getTranslatedString: $view.tomodulename}){/if}{/if}</td>
						<td class="col-actions">
							<ul class="actions">
					{if $view.setdefault eq 0}
						<li class="action">
							<form method="post" action="index.php" onsubmit="return CalendarUtils.deleteView ('{$view.label}');">
								<input type="hidden" name="module" value="Settings" />
								<input type="hidden" name="action" value="CalendarViewDefaultView" />
								<input type="hidden" name="record" value="{$view.calendarviewid}" />
								<input type="hidden" name="Ajax" value="true" />
								<button class="btn btn-warning" type="submit" title="Marcar como vista por defecto">
									<i class="fa fa-times"></i>
								</button>
							</form>
						</li>
					{else}
						<li class="action">
						<span class="btn btn-success" title="Vista por defecto">
							<i class="fa fa-check-square-o"></i>
						</span>
						</li>
					{/if}
								<li class="action">
									<a href="index.php?module=Settings&action=CalendarViewEditView&record={$view.calendarviewid}&parenttab=Settings" class="btn btn-primary" title="Editar">
										<i class="fa fa-pencil"></i>
									</a>
								</li>
								<li class="action">
									<form method="post" action="index.php" onsubmit="return CalendarUtils.deleteView ('{$view.label}');">
										<input type="hidden" name="module" value="Settings" />
										<input type="hidden" name="action" value="CalendarViewDeleteView" />
										<input type="hidden" name="record" value="{$view.calendarviewid}" />
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
						<td colspan="6" class="text-center">No hay vistas registradas</td>
					</tr>
{/if}
					</tbody>
				</table>
			</div>
{if ($DATA.totalRecords > 0) && ($DATA.totalPages > 1) }
			<ul class="pagination pull-right">
				<li{if ($DATA.page == 1) } class="disabled"{/if}>
					<a href="{if ($DATA.page == 1) }javascript:;{else}index.php?module=Settings&action=CalendarViewListView&parenttab=Settings&page=1{/if}">
						<i class="fa fa-step-backward"></i>
					</a>
				</li>
				<li{if ($DATA.page == 1)} class="disabled"{/if}>
					<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=Settings&action=CalendarViewListView&parenttab=Settings&page={$DATA.page - 1}{/if}">
						<i class="fa fa-chevron-left"></i>
					</a>
				</li>
	{for $i=1 to $DATA.totalPages}
				<li{if ($i == $DATA.page)} class="active"{/if}>
					<a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=Settings&action=CalendarViewListView&parenttab=Settings&page={$i}{/if}">
						{$i}
					</a>
				</li>
	{/for}
				<li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=Settings&action=CalendarViewListView&parenttab=Settings&page={$DATA.page + 1}{/if}">
						<i class="fa fa-chevron-right"></i>
					</a>
				</li>
				<li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
					<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=Settings&action=CalendarViewListView&parenttab=Settings&page={$DATA.totalPages}{/if}">
						<i class="fa fa-step-forward"></i>
					</a>
				</li>
			</ul>
{/if}
		</div>
	</div>
</div>
<script type="text/javascript" src="modules/Settings/calendar-view.js"></script>
{/strip}
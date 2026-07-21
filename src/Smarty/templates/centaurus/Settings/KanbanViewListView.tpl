{strip}
<style type="text/css">
	.col-modulename {
		width: 15em;
	}
	.col-field {
		width: 15em;
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
</style>
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;">
					<i class="fa fa-th yellow-bg"></i>
				</div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li>
						<a href="index.php?module=Settings&action=index&parenttab=Settings">CONFIGURACIÓN</a>
					</li>
					<li class="active">{$MOD.LBL_KANBAN_VIEW|upper}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.LBL_KANBAN_VIEW_DESCRIPTION}</td>
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
		<div class="row">
			<div class="col-lg-12 col-md-12 col-xs-12" style="margin-bottom: 12px">
				<ul class="nav nav-tabs nav-justified">
					<li {if $SELECTED_TAB eq 'gantt-task'}class="active"{/if}>
						<a data-toggle="tab"
						   href="#gantt-task">Gantt de tareas</a>
					</li>
					<li {if $SELECTED_TAB eq 'kanban-task'}class="active"{/if}>
						<a data-toggle="tab"
						   href="#kanban-task">Kanbam de tareas</a>
					</li>
					<li {if $SELECTED_TAB eq 'kanban-record'}class="active"{/if}>
						<a data-toggle="tab"
						   href="#kanban-record">Kanban de Modulos</a>
					</li>
				</ul>
			</div>
		</div>
		<div class="tab-content tab-content-body clearfix">
            {* kanban task *}
			<div class="tab-pane fade {if $SELECTED_TAB eq 'kanban-record'}active in{/if}" id="kanban-record">
				<header class="main-box-header clearfix">
					<div class="col-xs-6">
						<form name="filters" action="index.php" method="GET" class="form-inline">
							<input type="hidden" name="module" value="Settings" />
							<input type="hidden" name="action" value="KanbanViewListView" />
							<input type="hidden" name="parenttab" value="Settings" />
							<div class="form-group">
								<input type="text" name="keyword" value="{$SEARCH_KEYWORD}" class="form-control" placeholder="Palabras clave">
							</div>
							<input type="submit" value="Buscar" class="btn btn-primary">
						</form>
					</div>
					<div class="col-xs-6 text-right">
						<a href="index.php?module=Settings&action=KanbanViewEditView&parenttab=Settings" class="btn btn-primary">
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
								<th class="col-label"><b>Aplicación</b></th>
								<th class="col-modulename"><b>Módulo</b></th>
								<th class="col-field"><b>Campo</b></th>
								<th class="col-actions">Acciones</th>
							</tr>
							</thead>
							<tbody>
		{if ($DATA.totalRecords > 0) }
			{foreach $DATA.records as $view}
							<tr class="lvtColData">
								<td class="col-label">{$view.label}</td>
								<td class="col-label">{$view.aplicationName}</td>
								<td class="col-modulename">{$view.modulelabel|getTranslatedString: $view.modulelabel}</td>
								<td class="col-field">{$view.titlefieldlabel|getTranslatedString: $view.titlemodulename}{if ($view.titlemodulename != $view.modulename)}{/if}</td>
								<td class="col-actions">
									<ul class="actions">
										<li class="action">
											<a href="index.php?module=Settings&action=KanbanViewEditView&record={$view.kanbanviewid}&parenttab=Settings" class="btn btn-primary" title="Editar">
												<i class="fa fa-pencil"></i>
											</a>
										</li>
										<li class="action">
											<form method="post" action="index.php" onsubmit="return KanbanUtils.deleteView ('{$view.label}');">
												<input type="hidden" name="module" value="Settings" />
												<input type="hidden" name="action" value="KanbanViewDeleteView" />
												<input type="hidden" name="record" value="{$view.kanbanviewid}" />
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
							<a href="{if ($DATA.page == 1) }javascript:;{else}index.php?module=Settings&action=KanbanViewListView&parenttab=Settings&page=1{/if}">
								<i class="fa fa-step-backward"></i>
							</a>
						</li>
						<li{if ($DATA.page == 1)} class="disabled"{/if}>
							<a href="{if ($DATA.page == 1)}javascript:;{else}index.php?module=Settings&action=KanbanViewListView&parenttab=Settings&page={$DATA.page - 1}{/if}">
								<i class="fa fa-chevron-left"></i>
							</a>
						</li>
			{for $i=1 to $DATA.totalPages}
						<li{if ($i == $DATA.page)} class="active"{/if}>
							<a href="{if ($i == $DATA.page)}javascript:;{else}index.php?module=Settings&action=KanbanViewListView&parenttab=Settings&page={$i}{/if}">
								{$i}
							</a>
						</li>
			{/for}
						<li{if ($DATA.page == $DATA.totalPages)} class="disabled"{/if}>
							<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=Settings&action=KanbanViewListView&parenttab=Settings&page={$DATA.page + 1}{/if}">
								<i class="fa fa-chevron-right"></i>
							</a>
						</li>
						<li{if $DATA.page == $DATA.totalPages} class="disabled"{/if}>
							<a href="{if ($DATA.page == $DATA.totalPages)}javascript:;{else}index.php?module=Settings&action=KanbanViewListView&parenttab=Settings&page={$DATA.totalPages}{/if}">
								<i class="fa fa-step-forward"></i>
							</a>
						</li>
					</ul>
		{/if}
				</div>
			</div>
			{* kanban task *}
			<div class="tab-pane fade {if $SELECTED_TAB  eq 'kanban-task'}active in{/if}" id="kanban-task">
				<header class="main-box-header clearfix">
					<div class="col-xs-6">&nbsp;
					</div>
					<div class="col-xs-6 text-right">
						<a href="index.php?module=Settings&action=KanbanTaskEditView&parenttab=Settings" class="btn btn-primary">
							<i class="fa fa-plus-circle"></i> Activar kanban de tareas
						</a>
					</div>
				</header>
				<div class="main-box-body clearfix" id="ListViewContents">
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
							<tr>
								<th class="col-label" style="width: 40%"><b>Mödulo</b></th>
								<th class="col-label" style="width: 15%"><b>Vista de detalle</b></th>
								<th class="col-modulename" style="width: 15%"><b>Vista de lista</b></th>
								<th class="col-actions" style="width: 30%;float: right">Acciones</th>
							</tr>
							</thead>
							<tbody>
							{if $KANBAN_TASKS neq NULL}
								{foreach  $KANBAN_TASKS as $kanbanTask}
								<tr>
									<td class="text-left">{$kanbanTask['tablabel']}</td>
									<td>{if $kanbanTask['detail_view'] eq  '1'}<span class="label label-success">Visible</span>
										{else}
										<span class="label label-danger">Oculta</span>
										{/if}
									</td>
									<td>{if $kanbanTask['list_view'] eq  '1'}<span class="label label-success">Visible</span>
                                        {else}
											<span class="label label-danger">Oculta</span>
                                        {/if}
									</td>
									<td>
										<ul class="actions" style="float: right">
											<li class="action">
												<a href="index.php?module=Settings&action=KanbanTaskEditView&record={$kanbanTask['kanbantasksid']}&parenttab=Settings" class="btn btn-primary" title="Editar">
													<i class="fa fa-pencil"></i>
												</a>
											</li>
											<li class="action">
												<form method="post" action="index.php" onsubmit="return KanbanUtils.deleteView ('{$kanbanTask['tablabel']}');">
													<input type="hidden" name="module" value="Settings" />
													<input type="hidden" name="action" value="KanbanTaskDelete" />
													<input type="hidden" name="record" value="{$kanbanTask['kanbantasksid']}" />
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
								<tr>
									<td colspan="4">
										<div class="alert alert-info">No se han activiado vistas kanbas de tareas</div>
									</td>
								</tr>
							{/if}

							</tbody>
						</table>
					</div>
				</div>
			</div>
			{* Gantttask *}
			<div class="tab-pane fade {if $SELECTED_TAB  eq 'gantt-task'}active in{/if}" id="gantt-task">
				<header class="main-box-header clearfix">
					<div class="col-xs-6">&nbsp;
					</div>
					<div class="col-xs-6 text-right">
						<a href="index.php?module=Settings&action=GanttTaskEditView&parenttab=Settings" class="btn btn-primary">
							<i class="fa fa-plus-circle"></i> Activar gantt de tareas
						</a>
					</div>
				</header>
				<div class="main-box-body clearfix" id="ListViewContents">
					<div class="table-responsive">
						<table class="table table-striped table-hover">
							<thead>
							<tr>
								<th class="col-label" style="width: 40%"><b>Mödulo</b></th>
								<th class="col-label" style="width: 15%"><b>Vista de detalle</b></th>
								<th class="col-modulename" style="width: 15%"><b>Vista de lista</b></th>
								<th class="col-actions" style="width: 30%;float: right">Acciones</th>
							</tr>
							</thead>
							<tbody>
                            {if $GANTT_TASKS neq NULL}
                                {foreach  $GANTT_TASKS as $ganttTask}
									<tr>
										<td class="text-left">{$ganttTask['tablabel']}</td>
										<td>{if $ganttTask['detail_view'] eq  '1'}<span class="label label-success">Visible</span>
                                            {else}
												<span class="label label-danger">Oculta</span>
                                            {/if}
										</td>
										<td>{if $ganttTask['list_view'] eq  '1'}<span class="label label-success">Visible</span>
                                            {else}
												<span class="label label-danger">Oculta</span>
                                            {/if}
										</td>
										<td>
											<ul class="actions" style="float: right">
												<li class="action">
													<a href="index.php?module=Settings&action=GanttTaskEditView&record={$ganttTask['gantttasksid']}&parenttab=Settings" class="btn btn-primary" title="Editar">
														<i class="fa fa-pencil"></i>
													</a>
												</li>
												<li class="action">
													<form method="post" action="index.php" onsubmit="return KanbanUtils.deleteView ('{$ganttTask['tablabel']}');">
														<input type="hidden" name="module" value="Settings" />
														<input type="hidden" name="action" value="GanttTaskDelete" />
														<input type="hidden" name="record" value="{$ganttTask['gantttasksid']}" />
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
								<tr>
									<td colspan="4">
										<div class="alert alert-info">No se han activiado vistas gantt de tareas</div>
									</td>
								</tr>
                            {/if}

							</tbody>
						</table>
					</div>
				</div>
			</div>
            {* /Gantttask *}
		</div>
	</div>
</div>
<script type="text/javascript" src="modules/Settings/kanban-view.js"></script>
{/strip}
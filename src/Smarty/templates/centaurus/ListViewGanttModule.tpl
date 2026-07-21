{*<!--
/*********************************************************************************
 * Template: ListViewGanttModule.tpl
 * Descripción: Vista Gantt para ListView de módulos
 * Fecha: 2025-11-25
 * Reutiliza el template GanttDiagram.tpl con los datos de múltiples registros
 *********************************************************************************/
-->*}
<div class="container-fluid base-list-container">
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<div class="main-box-header clearfix">
					<div class="row" style="padding-top: 0!important; margin-top: -5px!important;">
						{* Botonera de cambio de vista (lista, kanban, métricas, calendario, etc.) *}
						<div class="form-group col-md-6 list-view-filter"
							 style="{if isset($IS_HOME_TAB)}display: none; {/if}margin-bottom: 0;">
							<div class="btn-group btn-control pull-left" style="margin-left: 10px">
								{* LIST-VIEW *}
								<a data-toggle="tab" href="#ListViewContents" class="btn btn-default"
								   style=" font-size: 15px!important;"
								   onclick="ListViewTabUtils.activeListTab(event)"
								   data-toggle="tab" title="Listado de registros"><i
											class="fa fa-list-ul"></i></a>
								{* LIST-VIEW-KANBAN-VIEW *}
								{if $STATUS_BUTTONS['kanban'] && false}
									<a data-toggle="tab" href="#LIST-VIEW-KANBAN-VIEW" class="btn btn-default" style=" font-size: 15px!important;" title="Vista kanban"
									   onclick="ListViewTabUtils.activeKanbanTab (event)"
									   data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>
									{*
									<a data-toggle="tab" href="#LIST-VIEW-KANBAN-VIEW" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
									   title="Vista kanban"
									   onclick="ListViewTabUtils.activeKanbanTab (event)"
									   data-toggle="tab"><i class="fa fa-trello" aria-hidden="true"></i></a>*}
								{/if}
								{* LIST-VIEW-BOX-SCORE *}
								{if $STATUS_BUTTONS.boxscore}
									<a data-toggle="tab" href="#LIST-VIEW-BOX-SCORE" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
									   title="Indicadores de gestión"
									   onclick="ListViewTabUtils.activeBoxScoreTab (event)"
									   data-toggle="tab"><i class="fa fa-heart-o"></i></a>
								{/if}
								{* LIST-VIEW-GRAPHIC *}
								{if $STATUS_BUTTONS.graphic}
									<a data-toggle="tab" href="#LIST-VIEW-GRAPHIC" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.3em; margin-right:0.05em;margin-left:0.05em;"
									   title="Gráficos"
									   onclick="ListViewTabUtils.activeGraphicTab (event)"
									   data-toggle="tab"><i class="fa fa-bar-chart-o"></i></a>
								{/if}
								{* LIST-VIEW-REPORT *}
								{if $STATUS_BUTTONS.report}
									<a data-toggle="tab" href="#LIST-VIEW-REPORT" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.3em; margin-right:0.05em;margin-left:0.05em;"
									   title="Informes"
									   onclick="ListViewTabUtils.activeReportTab (event)"
									   data-toggle="tab"><i class="fa fa-file" aria-hidden="true"></i></a>
								{/if}
								{* LIST-VIEW-CALENDAR *}
								{if $STATUS_BUTTONS.calendar}
									<a data-toggle="tab" href="#LIST-VIEW-CALENDAR" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
									   title="Vista calendario"
									   onclick="ListViewTabUtils.activeCalendarTab (event)"
									   data-toggle="tab"><i class="fa fa-calendar"></i></a>
								{/if}
								{* LIST-VIEW-KANBAN-TASK-VIEW *}
								{if $STATUS_BUTTONS.task}
									<a data-toggle="tab" href="#LIST-VIEW-KANBAN-TASK-VIEW" class="btn btn-default" style=" font-size: 15px!important;vertical-align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
									   title="Vista kanban de tareas"
									   onclick="ListViewTabUtils.activeKanbanTaskTab (event)"
									   data-toggle="tab"><i class="bi bi-kanban-fill"></i></a>
								{/if}
								{* Botón de Vista Gantt activa (siempre visible en esta cabecera) *}
								<button type="button" class="btn btn-primary" style=" font-size: 15px!important;vertical-align:middle; height: 2.2em; margin-right:0.05em;margin-left:0.05em;"
											title="Vista Gantt">
									<span class="glyphicon glyphicon-indent-left"></span>
								</button>
								{* Embudo y lista de vistas Gantt, en la misma línea que los botones de vistas *}
								{if $GANTT_MODULE_VIEWS && count($GANTT_MODULE_VIEWS) >= 1}
									<div class="input-group" style="margin-left: 1px">
										<div class="input-group-btn">
											<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" style="height: 34px;margin-left:0.2em">
												<i class="fa fa-filter">&nbsp;</i><span class="caret"></span></button>
											<ul class="dropdown-menu" role="menu">
												<li class="disabled">
													<a href="#" onclick="return false;" title="En construcción">Crear vista Gantt</a>
												</li>
												<li class="disabled">
													<a href="#" onclick="return false;" title="En construcción">Editar vista Gantt</a>
												</li>
											</ul>
										</div>
										<select id="gantt-module-view-selector"
											name="gantt_view_id"
											class="form-control"
											data-module="{$MODULE}"
											title="Seleccionar vista Gantt"
											style="width:auto; max-width:260px; min-width:160px; display:inline-block;">
											{foreach from=$GANTT_MODULE_VIEWS item=view}
											<option value="{$view.ganttviewid}"
													{if $view.ganttviewid eq $CURRENT_VIEW_ID}selected="selected"{/if}>
												{$view.viewname}
												{if $view.is_default eq 1} (Por defecto){/if}
											</option>
											{/foreach}
										</select>
									</div>
								{/if}
							</div>
						</div>
						<div class="col-md-2" id="GANTT-MODULE-LOADING" style="padding-right: 0">&nbsp;</div>
						<div class="col-md-4" style="padding-right: 0">
							<div style="display: inline-block; float: right; padding-right: 5px">
								<h4 style="font-weight: bold; color: #cccccc; display: inline-block; margin: 0; vertical-align: middle;">
									{if $GANTT_CONFIG && $GANTT_CONFIG.viewname}
										{$GANTT_CONFIG.viewname}
									{else}
										Vista Gantt
									{/if}
								</h4>
							</div>
						</div>
						<div class="col-md-12" style="margin-top: 1.5em;">
							{* Incluir el diagrama Gantt reutilizando el template existente *}
							{if $TASKS_GANTT}
								{include file="GanttDiagram.tpl" 
										 TASKS_GANTT=$TASKS_GANTT 
										 RELATED_MODULE=$MODULE 
										 idGanttDiagram=$idGanttDiagram}
							{else}
								<div class="alert alert-warning" style="margin: 20px; padding: 30px; text-align: center;">
									<i class="fa fa-calendar-times-o fa-3x" style="margin-bottom: 15px; display: block; color: #8a6d3b;"></i>
									<h4 style="margin-bottom: 10px;"><strong>No hay registros para mostrar</strong></h4>
									<p>La vista Gantt seleccionada no contiene registros que cumplan con los filtros configurados.</p>
									<p style="margin-top: 15px; font-size: 12px; color: #666;">
										Prueba seleccionando otra vista o ajustando los filtros de la lista.
									</p>
								</div>
							{/if}
							
							{* Script para inicializar eventos *}
							<script type="text/javascript">
								jQuery(document).ready(function() {
									// Inicializar eventos específicos del Gantt de ListView
									if (typeof ListViewGanttModuleUtils !== 'undefined') {
										ListViewGanttModuleUtils.initializeGanttEvents();
									}
									
									// Evento para cambio de vista
									jQuery('#gantt-module-view-selector').off('change').on('change', function() {
										var module = jQuery(this).data('module');
										var viewId = jQuery(this).val();
										ListViewGanttModuleUtils.changeGanttView(module, viewId);
									});
								});
							</script>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

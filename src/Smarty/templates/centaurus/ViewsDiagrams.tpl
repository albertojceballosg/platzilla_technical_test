{math equation= rand() assign= "idGanttDetailView"}
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
<link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css"/>
<style>
    {literal}
    #main-{/literal}{$idGanttDetailView}{literal} .form-control {
        display: inline-block;
        border: 1px solid #dee2e6 !important;
        width: 92% !important;
        margin-right: 0.1em !important;
    }

    .add_button {
        margin: 10px 0px 10px 0px;
    }

    .badge {
        padding: 0.4em !important;
        vertical-align: top !important;
    }

    .badge small {
        vertical-align: center !important;
    }

    .car-task {
        padding: 1.2em;
        margin-bottom: 1.2em;
        background-color: white;
    }

    .completed_item {
        text-decoration: line-through;
    }

    .text_holder {
        max-width: 100%;
        word-wrap: break-word;
    }

    #main-{/literal}{$idGanttDetailView}{literal} {
        margin-top: 0;
        border-radius: 5px;
        width: 100%;
    }

    .flex-container {
        padding: 0;
        margin: 0;
        list-style: none;
        -ms-box-orient: horizontal;
        display: -webkit-box;
        display: -moz-box;
        display: -ms-flexbox;
        display: -moz-flex;
        display: -webkit-flex;
        display: flex;
    }

    .nowrap {
        -webkit-flex-wrap: nowrap;
        flex-wrap: nowrap;
    }

    .wrap {
        -webkit-flex-wrap: wrap;
        flex-wrap: wrap;
    }

    .flex-start {
        justify-content: flex-start;
    }

    .flex-end {
        justify-content: flex-end;
    }

    .space-evenly {
        justify-content: space-evenly;
    }

    .space-between {
        justify-content: space-between;
    }

    .flex-item {
        padding: 5px;
        width: 100px;
        height: 100px;
        margin: 10px;
        line-height: 100px;
    }

    .items-align-baseline {
        align-items: baseline;
    }

    .items-align-star {
        align-items: flex-start;
    }

    .item-date {
        font-size: small;
        font-style: italic;
    }

    .list-form {
        display: none;
    }

    .list-btn-header {
        text-align: center;
        font-weight: bold;
        font-size: small;
        background-color: #F6F6F6;
        margin-top: -5px;
        margin-bottom: -9px;
        padding-bottom: 0.3em;
    }

    .task-group-header {
        font-weight: bold;
        border-bottom: none !important;
        margin: 0.4em 0 !important;
    }

    .input-group-addon {
        color: #555555;
        background-color: # #eeeeee;
        border-color: #cccccc !important;
    }

    }
    {/literal}
</style>
<link rel="stylesheet" type="text/css" href="themes/centaurus/css/libs/ekko-lightbox.min.css"/>
<div class="col-md-12" id="main-{$idGanttDetailView}">
    <div class="row" style="background-color: transparent!important;">
        <div class="col-md-12">
            <h1 style="margin-left: -3px;font-weight: bold">Vista de tareas</h1>
        </div>
    </div>
    <div class="main-box no-header clearfix">
        {if $AVAILABLE_MODULES neq NULL}{/if}
        <form class="form-inline" role="form" id="form-views-diagrams-{$idGanttDetailView}" method="post" style="margin-bottom: 1px;padding-bottom: 1px">
            <input type="hidden" name="module" value="views_diagrams">
            {*<input type="hidden" name="flmodule" value="{$FLMODULE}">*}
            <input type="hidden" name="record" value="">
            <input type="hidden" name="inviteesid"  id="inviteesid-{$idGanttDetailView}"  value="{$USERS_ID_LIST}">
            <input type="hidden" name="action" value="index">
            <div id="module-gannt-{$idGanttDetailView}" name="flmodule" style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 4px; margin-left: 10px;">
                <span id="loading-{$idGanttDetailView}" class="hide">
                    <div class="loading-bars" style="display: inline-flex; align-items: flex-end; height: 20px; gap: 2px; vertical-align: middle;">
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.1s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.2s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.3s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.4s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.5s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.6s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.7s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.8s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #b3d9f2 0%, #4a9fd8 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 0.9s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #1e5a8e 0%, #2874b5 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 1.0s;"></div>
                        <div class="bar" style="width: 6px; background: linear-gradient(to top, #0d3d5c 0%, #1e5a8e 100%); animation: loading-bar 1.2s ease-in-out infinite; animation-delay: 1.1s;"></div>
                    </div>
                    <style>
                        @keyframes loading-bar {
                            0%, 100% { height: 5px; }
                            50% { height: 20px; }
                        }
                    </style>
                </span>
                <div class="btn-group">
                    <button id="btn-group-user-{$idGanttDetailView}" type="button"
                            class="btn btn-primary dropdown-toggle"
                            title="Seleccionar usuarios"
                            data-toggle="dropdown">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        &nbsp;Filtrar por usuario&nbsp;
                        <span class="caret"></span>
                    </button>
                    <ul id="vies-diagram-user-{$idGanttDetailView}" class="dropdown-menu scroll-user-menu" role="menu">
                        {if $AVAILABLE_USERS|count gt 1}
                            {foreach $AVAILABLE_USERS as $id => $user}
                                <li {if (in_array($id, $USERS))}class="active" {/if}>
                                    <a href="#" title="{$user['name']}" rel="{{$id}}"
                                       onclick="ViewsDiagramsUtls.selectedDiagramUser (event, this, '{$idGanttDetailView}')">
                                        <img class="img-circle" style="width: 60%; height: 60%"
                                             data-src="{$user['avatar']}" alt="{$user['name']}"
                                             src="{$user['avatar']}">
                                    </a>
                                </li>
                            {/foreach}
                        {else}
                            <li class="list-btn-header" title="Usuarios invitados">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                <small>No se han encontrado usuarios!</small>
                            </li>
                        {/if}
                    </ul>
                </div>
                <div class="input-group" style="width: auto;">
                    <div class="input-group-addon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    <select id="period-dates-{$idGanttDetailView}"
                            name="periodtask"
                            class="form-control" title="Filtrar tareas por período de tiempo"
                            style="width: auto; min-width: 180px;"
                            onchange="toggleCustomDateRange{$idGanttDetailView}(this.value)">
                        {if $PERIOD_DATES neq NULL}
                            <option value="" {if $PERIOD_SELECTED eq ''}selected{/if}>Rango personalizado</option>
                            {foreach $PERIOD_DATES as $period => $perioName}
                                {if $period eq 'custom'}{continue}{/if}
                                <option value="{$period}" {if $period eq $PERIOD_SELECTED}selected{/if}>{$perioName}</option>
                            {/foreach}
                        {/if}
                    </select>
                </div>
                <div id="custom-date-start-{$idGanttDetailView}" class="input-group" style="width: auto; display: {if $PERIOD_SELECTED eq '' or $PERIOD_SELECTED eq 'custom'}inline-flex{else}none{/if};">
                    <div class="input-group-addon">
                        <i class="fa fa-calendar-o"></i>
                    </div>
                    <input type="text" 
                           id="custom-start-date-{$idGanttDetailView}"
                           name="custom_start_date"
                           class="form-control"
                           placeholder="Inicio"
                           title="Fecha de inicio"
                           style="width: 110px;"
                           value="{$CUSTOM_START_DATE|default:''}"
                           autocomplete="off">
                </div>
                <div id="custom-date-end-{$idGanttDetailView}" class="input-group" style="width: auto; display: {if $PERIOD_SELECTED eq '' or $PERIOD_SELECTED eq 'custom'}inline-flex{else}none{/if};">
                    <div class="input-group-addon">
                        <i class="fa fa-calendar-o"></i>
                    </div>
                    <input type="text" 
                           id="custom-end-date-{$idGanttDetailView}"
                           name="custom_end_date"
                           class="form-control"
                           placeholder="Fin"
                           title="Fecha de fin"
                           style="width: 110px;"
                           value="{$CUSTOM_END_DATE|default:''}"
                           autocomplete="off">
                </div>
                <button name="submitSearch" id="submitSearch" class="btn btn-primary" title="Buscar tareas" type="submit">
                    <i class="fa fa-search" aria-hidden="true"></i> Buscar
                </button>
            </div>
        </form>
        <span id="help-user-{$idGanttDetailView}" class="help-block" style="color: red; display: inline-block!important;margin: 0 0.9em 1em 0.9em"><b>Usuario(s):</b>&nbsp;{$USERS_NAME}</span>
        <ul class="nav nav-tabs" id="task-tabs-{$idGanttDetailView}">
            <li class="active">
                <a data-toggle="tab" href="#gantt-task-tab-{$idGanttDetailView}">Gantt de tareas</a>
            </li>
            <li class="">
                <a data-toggle="tab" href="#kanban-task-tab-{$idGanttDetailView}">Kanban de tareas</a>
            </li>
        </ul>
    </div>
    <div id="ganttHome" class="main-box no-header clearfix" style="padding-top: 0; margin-top: 0;">
        <div class="tab-content" style="padding: 0!important;">
            <div id="gantt-task-tab-{$idGanttDetailView}" class="tab-pane fade in active">
                <div class="card rounded car-task" style="margin-bottom: 2.5px!important; padding-top: 1em!important;">
                    {if $TASKS_GANTT neq NULL}
                        {include file="GanttDiagram.tpl"}
                    {else}
                        <div class="alert alert-info">No hay tareas!</div>
                    {/if}
                </div>
            </div>
            <div id="kanban-task-tab-{$idGanttDetailView}" class="tab-pane fade ">
                <div class="card rounded car-task" style="margin-bottom: 2.5px!important;">
                    {if $KANBAN_BLOCKS neq NULL}
                        {include file="KanbanDiagram.tpl"}
                    {else}
                        <div class="alert alert-info">No hay tareas! en el periodo</div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="modules/views_diagrams/views-diagrams-utils.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-lightbox.js"></script>
<script type="text/javascript" src="include/js/ekko-lightbox.min.js"></script>
<!-- Datepicker JS y localización español para filtros de fecha personalizados -->
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript">
    // Función para mostrar/ocultar campos de fecha personalizada
    function toggleCustomDateRange{$idGanttDetailView}(value) {
        var customDateStart = jQuery('#custom-date-start-{$idGanttDetailView}');
        var customDateEnd = jQuery('#custom-date-end-{$idGanttDetailView}');
        
        if (value === '' || value === 'custom') {
            customDateStart.css('display', 'inline-flex');
            customDateEnd.css('display', 'inline-flex');
        } else {
            customDateStart.css('display', 'none');
            customDateEnd.css('display', 'none');
            // Limpiar los campos cuando se selecciona un período predefinido
            jQuery('#custom-start-date-{$idGanttDetailView}').val('');
            jQuery('#custom-end-date-{$idGanttDetailView}').val('');
        }
    }

    jQuery(document).ready(function () {
        // Inicializar datepickers con bootstrap-datepicker (el que usa el sistema)
        jQuery('#custom-start-date-{$idGanttDetailView}').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true,
            language: 'es',
            weekStart: 1,
            orientation: 'bottom auto'
        });

        jQuery('#custom-end-date-{$idGanttDetailView}').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true,
            language: 'es',
            weekStart: 1,
            orientation: 'bottom auto'
        });

        // Validar que la fecha de inicio no sea mayor que la fecha de fin
        jQuery('#custom-start-date-{$idGanttDetailView}').on('changeDate', function(e) {
            var startDate = e.date;
            jQuery('#custom-end-date-{$idGanttDetailView}').datepicker('setStartDate', startDate);
        });

        jQuery('#custom-end-date-{$idGanttDetailView}').on('changeDate', function(e) {
            var endDate = e.date;
            jQuery('#custom-start-date-{$idGanttDetailView}').datepicker('setEndDate', endDate);
        });

        // Validación antes de enviar el formulario
        jQuery('#form-views-diagrams-{$idGanttDetailView}').on('submit', function(e) {
            var periodSelect = jQuery('#period-dates-{$idGanttDetailView}').val();
            var startDate = jQuery('#custom-start-date-{$idGanttDetailView}').val();
            var endDate = jQuery('#custom-end-date-{$idGanttDetailView}').val();

            // Si se seleccionó "Rango personalizado", validar que ambas fechas estén completas
            if ((periodSelect === '' || periodSelect === 'custom') && (!startDate || !endDate)) {
                alert('Por favor, seleccione ambas fechas (inicio y fin) para el rango personalizado.');
                e.preventDefault();
                return false;
            }
        });

        jQuery(document).on('click', '[data-toggle="lightbox"]', function (event) {
            event.preventDefault();
            jQuery(this).ekkoLightbox();
        });
    })
</script>

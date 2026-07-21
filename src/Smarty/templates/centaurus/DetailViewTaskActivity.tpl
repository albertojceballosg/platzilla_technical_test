{math equation= rand() assign= "idTaskDetailView"}

{* Incluir TaskViewModal.js para funcionalidad de modal de tareas *}
<script type="text/javascript" src="modules/Calendar/TaskViewModal.js"></script>

{* Establecer formato numérico para JavaScript *}
<script type="text/javascript">
    window.gUserNumberFormat = '{$USER_NUMBER_FORMAT|default:'AMERICAN_FORMAT'}';
</script>

<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" />
<link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css" />
<style>
    {literal}
        #main-{/literal}{$idTaskDetailView}{literal} .form-control {
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
        }

        .completed_item {
            text-decoration: line-through;
        }

        .text_holder {
            max-width: 100%;
            word-wrap: break-word;
        }

        #main-{/literal}{$idTaskDetailView}{literal} {
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
            background-color: #eeeeee;
            border-color: #cccccc !important;
        }

        .task-desc-content {
            overflow-x: auto;
            word-break: break-word;
            max-width: 100%;
        }

        .task-desc-content table {
            width: 100% !important;
            table-layout: fixed;
            word-break: break-word;
        }

        .task-desc-content img,
        .task-desc-content figure {
            max-width: 100% !important;
            height: auto !important;
        }

        .task-desc-content ul,
        .task-desc-content ol {
            margin: 0.25em 0 0 0;
            padding-left: 1.5em;
        }

        .task-desc-content li {
            word-break: break-word;
            margin-bottom: 2px;
        }

    {/literal}
</style>
<section class="">
    <div class="container" id="main-{$idTaskDetailView}">
        <div class="card rounded car-task" style="margin-bottom: 2px!important;padding 0.25em 1.2em!important;">
            <ul class="nav nav-tabs" id="task-tabs-{$idTaskDetailView}">
                {if $FLMODULE neq 'orden_de_trabajo' }
                    <li class="active">
                        <a id="main-tab-task-{$idTaskDetailView}" data-toggle="tab" href="#task-tab-{$idTaskDetailView}">
                            {if $FLMODULE neq 'orden_de_trabajo'}Acciones{else}Tareas{/if}
                        </a>
                    </li>
                {/if}
                {if $HAS_GANTT}
                    <li class="{if $FLMODULE eq 'orden_de_trabajo'}active{/if}">
                        <a data-toggle="tab" href="#gantt-task-tab-{$idTaskDetailView}">Gantt de tareas</a>
                    </li>
                {/if}
                {if $HAS_KANBAN}
                    <li class="{if $FLMODULE eq 'orden_de_trabajo' && !$HAS_GANTT}active{/if}">
                        <a data-toggle="tab" href="#kanban-task-tab-{$idTaskDetailView}">Kanban</a>
                    </li>
                {/if}
            </ul>
        </div>
        <div class="tab-content" style="padding: 0!important;">
            {if $FLMODULE neq 'orden_de_trabajo'}
                <div id="task-tab-{$idTaskDetailView}" class="tab-pane fade in active">
                    {* Create task *}
                    <div class="card rounded car-task" style="margin-bottom: 2.5px!important;">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="col-md-6">
                                <p class="text-left" style="margin-bottom: 0">
                                    {if $FLMODULE neq 'orden_de_trabajo'}Acción:{else}Tarea:{/if}
                                </p>
                            </div>
                            <div class="col-md-6" style="margin-right: 0;padding-right: 0;">
                                <button type="button" class="btn btn-warning btn-sm open-precreated-task-modal"
                                    data-taskid="{$idTaskDetailView}" data-module="{$FLMODULE}"
                                    style="margin-bottom: 2px; float:right;">
                                    Seleccionar el modelo de tarea&nbsp;{if $FLMODULE neq 'orden_de_trabajo'}/ acción{/if}
                                </button>
                            </div>
                        </div>
                        {* Create task *}
                        <form class="form-inline" role="form" id="main_input_box-{$idTaskDetailView}">
                            <input type="hidden" name="relatedcrmids[]" value="{$ID}">
                            <input type="hidden" name="record" value="">
                            <input type="hidden" name="module" value="Calendar">
                            <input type="hidden" name="formodule" value="{$FLMODULE}">
                            <input type="hidden" name="function" value="TASK_FROM_MODULE">
                            <input type="hidden" name="assigned_user_id" value="{$CURRENT_USER_ID}">
                            <input type="hidden" id="user-name-{$idTaskDetailView}" value="{$CURRENT_USER_NAME}">
                            <input type="hidden" name="planned_task" value="PLANNED_AND_RECORDED">
                            <input type="hidden" name="show_in_matrix" value="YES">
                            <input type="hidden" name="action" value="Save">
                            <input type="hidden" name="eventstatus" id="eventstatus" value="Planned">
                            <input type="hidden" id="today-{$idTaskDetailView}" value="{$TODAY}">
                            <input type="hidden" id="tomorrow-{$idTaskDetailView}" value="{$TOMORROW}">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <input type="text" class="form-control col-lg-12 col-md-12 col-sm-12"
                                    id="taskname-{$idTaskDetailView}" name="taskname"
                                    onchange="DetailViewTabUtils.readyToSave('{$idTaskDetailView}')"
                                    placeholder="¿Que necesitas hacer? Título; descripción" style="width: 100%!important;">
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 0.25em">
                                <div class="input-group">
                                    <span class="input-group-addon border"><i class="fa fa-bars"></i></span>
                                    <select id="activitytype-{$idTaskDetailView}"
                                        onchange="DetailViewTabUtils.selectedActivityTypes (this, '{$idTaskDetailView}')"
                                        name="activitytype" class="form-control">
                                        {foreach $AVAILABLE_ACTIVITY_TYPES as $activityType => $activityLabel}
                                            <option value="{$activityType}" {if ($ACTIVITYDATA.activitytype == $activityType)}
                                                selected="selected" {/if}>{$activityLabel}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                {* activity type  *}
                                <div class="input-group extended-task-{$idTaskDetailView}">
                                    <span class="input-group-addon border"><i class="fa fa-calendar"></i></span>
                                    <input style="margin-right: 0.1em!important;"
                                        class="col-md-3 form-control datepickerDate-{$idTaskDetailView}"
                                        id="date_start-{$idTaskDetailView}" name="date_start"
                                        placeholder="Realizar a partir de" type="text" value="{$TODAY}">
                                </div>
                                <div class="input-group" style="margin-left: 0.1em!important;">
                                    <span class="input-group-addon border"><i class="fa fa-calendar"></i></span>
                                    <input class="form-control col-md-3 datepickerDate-{$idTaskDetailView}"
                                        id="due-date-{$idTaskDetailView}" name="due_date"
                                        onchange="DetailViewTabUtils.readyToSave('{$idTaskDetailView}')"
                                        placeholder="“Realizar antes de" type="text" value="{$TOMORROW}">
                                </div>
                                {*time_start *}
                                <div class="input-group hide">
                                    <span class="input-group-addon">Inicia a las</span>
                                    <input type="time" class="form-control" name="time_start" placeholder="Hora de inicio"
                                        value="09:00:00" id="start_time-{$idTaskDetailView}">
                                </div>
                                {* hide button*}
                                <div class="input-group hide">
                                    <button type="button" data-action="EXTENDED" class="btn btn-primary btn-xs"
                                        onclick="DetailViewTabUtils.setExtendedTask (this, '{$idTaskDetailView}')"><i
                                            class="fa fa-plus" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                            {* estimated time*}
                            <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 0.25em">
                                <div class="input-group">
                                    <span class="input-group-addon">Unidades planificadas</span>
                                    <input type="text" class="form-control" name="estimated_time"
                                        placeholder="Duración estimada" value="{$DEFAULT_ESTIMATED_TIME}"
                                        id="estimated_time-{$idTaskDetailView}" data-number-format="decimal"
                                        data-decimals="1">
                                </div>
                                <div class="input-group" style="margin-left: 0.25em;">
                                    <span class="input-group-addon">Unidad</span>
                                    {if $AVAILABLE_ESTIMATED_TIME_UNITS neq NULL}
                                        <select name="estimated_time_unit" class="form-control">
                                            {foreach $AVAILABLE_ESTIMATED_TIME_UNITS as $unitKey => $unitLabel}
                                                <option value="{$unitKey}"
                                                    {if $unitKey eq $DEFAULT_ESTIMATED_TIME_UNIT}selected{/if}>
                                                    {$unitLabel}</option>
                                            {/foreach}
                                        </select>
                                    {else}
                                        <select name="estimated_time_unit" class="form-control">
                                            <option value="Hora">Hora</option>
                                            <option value="Día">Día</option>
                                            <option value="Semana">Semana</option>
                                            <option value="Mes">Mes</option>
                                        </select>
                                    {/if}
                                </div>
                                <div class="input-group" style="margin-left: 0.25em;">
                                    <span class="input-group-addon">Costo estimado</span>
                                    <input type="text" class="form-control" name="estimated_cost"
                                        data-number-format="decimal" data-default-value="0" data-decimals="2"
                                        placeholder="0.00" value="{$DEFAULT_ESTIMATED_COST}"
                                        id="estimated_cost-{$idTaskDetailView}">
                                </div>
                            </div>
                            {* estimated time*}
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                {* User invitees*}
                                {if $AVAILABLE_USERS neq NULL}
                                    {* ... *}
                                    <div class="btn-group" style="margin-left: 0.125em!important;">
                                        <button id="btn-group-user-{$idTaskDetailView}" type="button"
                                            class="btn btn-default dropdown-toggle" title="asignar tarea"
                                            style="font-size: 15px!important;margin-left: 0.1em" data-toggle="dropdown">
                                            <i style="color: #cccccc" class="fa fa-user" aria-hidden="true"></i>&nbsp;
                                            <span class="caret"></span>
                                        </button>
                                        <ul id="detailview-task-user-{$idTaskDetailView}" class="dropdown-menu scroll-user-menu"
                                            role="menu">
                                            <li class="list-btn-header" title="Usuarios invitados">
                                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                <small>Usuarios invitados</small>
                                            </li>
                                            <li class="divider"></li>
                                            {if $AVAILABLE_USERS|count gt 0}
                                                {foreach $AVAILABLE_USERS as $id => $user}
                                                    <li>
                                                        <a href="#" title="{$user['name']}" rel="{{$id}}"
                                                            onclick="DetailViewTabUtils.selectedUser (event, this, '{$idTaskDetailView}')">
                                                            <img class="img-circle" style="width: 36%; height: 36%"
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
                                {/if}
                                {* Importance*}
                                {if $AVAILABLE_IMPORTANCE neq NULL}
                                    <div class="btn-group">
                                        <button id="btn-group-importance-{$idTaskDetailView}" type="button"
                                            class="btn btn-default dropdown-toggle dropdown-toggle"
                                            title="Importancia de la tarea" style="font-size: 15px!important;margin-left: 0.1em"
                                            data-toggle="dropdown">
                                            <i class="fa  fa-exclamation-triangle" aria-hidden="true"></i>
                                            <span class="caret"></span>
                                        </button>
                                        <ul id="detailview-task-importance-{$idTaskDetailView}"
                                            class="dropdown-menu scroll-user-menu" role="menu">
                                            <li class="list-btn-header" title="Importancia">
                                                <i class="fa fa-info-circle" aria-hidden="true" style="padding-right: 0"></i>
                                                <small>Importancia</small>
                                            </li>
                                            <li class="divider"></li>
                                            {foreach $AVAILABLE_IMPORTANCE as $key => $importance}
                                                <li>
                                                    <a href="#" title="{$importance}" rel="{$key}"
                                                        onclick="DetailViewTabUtils.selectedImportance (event, this, '{$idTaskDetailView}')">
                                                        {if $importance eq 'Baja'}Estándar{else}{$importance}{/if}
                                                    </a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                {/if}
                                {* Priorities *}
                                {if $AVAILABLE_TASK_PRIORITIES neq NULL}
                                    <div class="btn-group">
                                        <button id="btn-group-priority-{$idTaskDetailView}" type="button"
                                            class="btn btn-default dropdown-toggle dropdown-toggle"
                                            title="Prioridad de la tarea" style="font-size: 15px!important;margin-left: 0.1em"
                                            data-toggle="dropdown">
                                            <i class="fa fa-sort" aria-hidden="true"></i>
                                            <span class="caret"></span>
                                        </button>
                                        <ul id="detailview-task-priority-{$idTaskDetailView}"
                                            class="dropdown-menu scroll-user-menu" role="menu">
                                            <li class="list-btn-header" title="Prioridad">
                                                <i class="fa fa-info-circle" aria-hidden="true" style="padding-right: 0"></i>
                                                <small>Prioridad</small>
                                            </li>
                                            <li class="divider"></li>
                                            {foreach $AVAILABLE_TASK_PRIORITIES as $priority}
                                                {if $priority eq 'Medio'}{continue}{/if}
                                                <li>
                                                    <a href="#" title="{$priority}" rel="{$priority}"
                                                        onclick="DetailViewTabUtils.selectedPriority (event, this, '{$idTaskDetailView}')">
                                                        {if $priority eq 'Alto'}Alta{else}Básica{/if}
                                                    </a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                {/if}
                                {* Statues *}
                                {if $AVAILABLE_EVENT_STATUSES neq NULL && false}
                                    <div class="btn-group">
                                        <button id="btn-group-task-status-{$idTaskDetailView}" type="button"
                                            class="btn btn-default dropdown-toggle" title="Estado de la tarea"
                                            style="font-size: 15px!important;margin-left: 0.1em" data-toggle="dropdown">
                                            <i class="fa fa-exchange" aria-hidden="true"></i>
                                            <span class="caret"></span>
                                        </button>
                                        <ul id="detailview-task-status-{$idTaskDetailView}"
                                            class="dropdown-menu scroll-user-menu" role="menu">
                                            <li class="list-btn-header" title="Estado de la tarea">
                                                <i class="fa fa-info-circle" aria-hidden="true" style="padding-right: 0"></i>
                                                <small>Estado</small>
                                            </li>
                                            <li class="divider"></li>
                                            {foreach $AVAILABLE_EVENT_STATUSES as $eventStatus => $eventStatusLabel}
                                                <li>
                                                    <a href="#" title="{$eventStatusLabel}" rel="{$eventStatus}"
                                                        onclick="DetailViewTabUtils.selectedStatus (event, this, '{$idTaskDetailView}')">
                                                        {$eventStatusLabel}
                                                    </a>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                {/if}
                                {* task Group*}
                                {if $CATEGORIES neq NULL}
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button id="btn-group-task-categories-{$idTaskDetailView}" type="button"
                                                class="btn btn-default dropdown-toggle"
                                                style="height: 2.4em!important;margin-left: 0.1em"
                                                title="Ubicación de la tareas" data-toggle="dropdown">
                                                <i style="color: #cccccc" class="fa fa-tasks" aria-hidden="true"></i>&nbsp;
                                                <span class="caret"></span>
                                            </button>
                                            <ul id="detailview-task-categories-{$idTaskDetailView}" class="dropdown-menu"
                                                role="menu">
                                                <li class="list-btn-header" title="Ubicación de la tarea">
                                                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                    <small>Ubicación</small>
                                                </li>
                                                <li class="divider"></li>
                                                <li>
                                                    <a href="#" title="Crear un nuevo grupo" rel="0"
                                                        onclick="DetailViewTabUtils.setCategory (event, this, '{$idTaskDetailView}')">
                                                        Crear grupo
                                                    </a>
                                                </li>
                                                <li class="divider"></li>
                                                {foreach $CATEGORIES as $id => $name}
                                                    <li>
                                                        <a href="#" title="{$name}" rel="{$id}"
                                                            onclick="DetailViewTabUtils.setCategory (event, this, '{$idTaskDetailView}')">
                                                            {$name}
                                                        </a>
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        </div>
                                        <input id="categoryname-{$idTaskDetailView}" name="categoryname" type="text"
                                            class="form-control hide">
                                    </div>
                                {/if}
                                {* task Group*}
                                <input type="button" value="Guardar" id="task-create-btn-{$idTaskDetailView}"
                                    style="font-size: 15px!important;;margin-left: 0.1em"
                                    onclick="DetailViewTabUtils.createTask (this, '{$idTaskDetailView}', 'CREATE')"
                                    class="btn btn-default add_button">
                            </div>
                            <small id="help-user-{$idTaskDetailView}" style="margin-top:0; color: red"></small><small
                                id="help-priority-{$idTaskDetailView}" style="margin-top:0; color: red"></small><small
                                id="help-status-{$idTaskDetailView}" style="margin-top:0; color: red"></small><small
                                id="help-group-{$idTaskDetailView}" style="margin-top:0; color: red"></small>
                        </form>
                    </div>
                    {* List - edit  task *}
                    {if $TASKS_VIEW_DATA neq NULL}
                        {foreach $TASKS_VIEW_DATA as $group => $tasks} 
                            <div id="tasks-group-{$tasks[0]['categoryid']}" class="card rounded car-task"
                                style="margin-bottom: 1em!important;">
                                <a href="#" title="Ocultar tareas"
                                    onclick="DetailViewTabUtils.taskGroupStatus(event, this, '{$tasks[0]['categoryid']}')"
                                    rel="{$tasks[0]['categoryid']}" data-status="visible">
                                    <h3 class="task-group-header">
                                        <i class="fa fa-tasks" aria-hidden="true"></i>&nbsp;{$group}
                                    </h3>
                                </a>
                                <ol class="list-group list-tasks" id="list-tasks-group-{$tasks[0]['categoryid']}">
                                    {foreach $tasks as $task}
                                        {*$task|var_dump*}
                                        {math equation= rand() assign= "idTaskDetail"}
                                        <li id="task-view-{$idTaskDetail}" data-crmid="{$task['crmid']}" class="list-group-item">
                                            <div class="flex-container nowrap space-between items-align-star">
                                                <div class="flex-container wrap flex-start items-align-baseline" style="max-width: 60%">
                                                    <div style="width: 100%; display: inline">
                                                        <h4 id="subject-{$idTaskDetail}" {if $task['progress'] eq 100}
                                                                class="completed_item"
                                                            {/if}style="{*width: 100%;*} margin: 0;font-weight: bold">{$task['subject']}
                                                            {if !empty($task['priority']) && false}
                                                                <span id="priorityname-{$idTaskDetail}" class="badge btn-default"
                                                                    style="padding: 0.35em">&nbsp;{$task['priority']}
                                                                    &nbsp;</span>
                                                            {/if}
                                                            {if !empty($task['eventstatus'])  && false}
                                                                <span id="statusname-{$idTaskDetail}" class="badge btn-default"
                                                                    style="padding: 0.35em">&nbsp;{$MOD[$task['eventstatus']]}
                                                                    &nbsp;</span>
                                                            {/if}
                                                        </h4>
                                                    </div>
                                                    <div style="width: 100%; display: inline; vertical-align: center;margin-bottom:0.5em;">
                                                        {if !empty($task['priority'])}
                                                            <span id="priorityname-{$idTaskDetail}"
                                                                class="badge btn-default">&nbsp;<small>Prioridad:&nbsp;{$task['priority']}
                                                                </small>&nbsp;</span>
                                                        {/if}
                                                        {if !empty($task['eventstatus'])}
                                                            <span id="statusname-{$idTaskDetail}"
                                                                class="badge btn-default">&nbsp;<small>Estado:&nbsp;{$AVAILABLE_EVENT_STATUSES[$task['eventstatus']]}
                                                                </small>&nbsp;</span>
                                                        {/if}
                                                        {if !empty($task['importance'])}
                                                            <span id="importance-{$idTaskDetail}"
                                                                class="badge btn-default">&nbsp;<small>Importancia:&nbsp;{$AVAILABLE_IMPORTANCE[$task['importance']]}
                                                                </small>&nbsp;</span>
                                                        {/if}
                                                        <span id="activite-type-{$idTaskDetail}" class="badge btn-default">&nbsp;<small>
                                                                {$AVAILABLE_ACTIVITY_TYPES[$task['activitytype']]}
                                                            </small>&nbsp;</span>
                                                        {if !empty($task['combined_condition'])}
                                                            {* Determinar el color de fondo según la condición (multi-idioma) *}
                                                            {assign var="situacion_bg_color" value="#ffffff"}
                                                            {assign var="situacion_text_color" value="#000000"}
                                                            {assign var="situacion_display" value=$task['combined_condition']}

                                                            {* Si es una clave de traducción (PICK_ACTIVITY_*), traducirla *}
                                                            {if $task['combined_condition']|substr:0:14 eq 'PICK_ACTIVITY_'}
                                                                {assign var="situacion_display" value=$task['combined_condition']|@getTranslatedString:'Calendar'}
                                                            {/if}

                                                            {* Determinar color según clave (mismos colores que TaskViewModal.tpl) *}
                                                            {if $task['combined_condition'] eq 'PICK_ACTIVITY_DELAYED_OVER_BUDGET'}
                                                                {assign var="situacion_bg_color" value="#D32F2F"}
                                                                {assign var="situacion_text_color" value="#FFFFFF"}
                                                            {elseif $task['combined_condition'] eq 'PICK_ACTIVITY_DELAYED_ON_BUDGET'}
                                                                {assign var="situacion_bg_color" value="#F57C00"}
                                                                {assign var="situacion_text_color" value="#FFFFFF"}
                                                            {elseif $task['combined_condition'] eq 'PICK_ACTIVITY_ON_TIME_OVER_BUDGET'}
                                                                {assign var="situacion_bg_color" value="#7B1FA2"}
                                                                {assign var="situacion_text_color" value="#FFFFFF"}
                                                            {elseif $task['combined_condition'] eq 'PICK_ACTIVITY_ON_TIME_ON_BUDGET'}
                                                                {assign var="situacion_bg_color" value="#388E3C"}
                                                                {assign var="situacion_text_color" value="#FFFFFF"}
                                                            {/if}
                                                            <span class="badge"
                                                                style="font-size: 0.8em; font-weight: normal; padding: 4px 8px; background-color: {$situacion_bg_color}; color: {$situacion_text_color};">
                                                                <!--
                                                                <i class="fa fa-info-circle"></i>-->{$situacion_display|escape:'html'}
                                                            </span>
                                                        {/if}
                                                    </div>
                                                    <div id="date_start-dt-{$idTaskDetail}" class="item-date"
                                                        style="margin-right: 0.5em; margin-bottom:1em;">
                                                        <i class="fa fa-calendar"></i>&nbsp;{$task['str_date_start']}
                                                    </div>
                                                    <small style="margin-right: 0.5em">-</small>
                                                    <div id="due_date-dt-{$idTaskDetail}" class="item-date" style="margin-right: 0.5em">
                                                        {$task['due_date']}</div>
                                                    {if !empty($task['estimated_time'])}
                                                        <div id="time_estimated-dt-{$idTaskDetail}" class="item-date"
                                                            style="margin-right: 0.5em">
                                                            {$task['estimated_time']}&nbsp;{$task['estimated_time_unit']|default:'Hora'}
                                                        </div>
                                                    {/if}
                                                    {if !empty($task['estimated_cost'])}
                                                        <div id="cost_estimated-dt-{$idTaskDetail}" class="item-date"
                                                            style="margin-right: 0.5em"><i
                                                                class="fa fa-dollar"></i>&nbsp;{$task['estimated_cost']}</div>
                                                    {/if}
                                                    {*time_start *}

                                                    <div id="username-dt-{$idTaskDetail}" class="item-date">&nbsp;<i class="fa fa-user"
                                                            aria-hidden="true"></i>
                                                        {$task['assigned_user_id']}&nbsp;{$task['invitee']['userName']}</div>
                                                </div>
                                                <div class="btn-group">
                                                    {if $task['how_to'] neq NULL}
                                                        <a class="btn btn-info " data-width="950" data-toggle="lightbox" data-parent=""
                                                            data-gallery="remoteload" data-title="¡Aprende como!"
                                                            href="index.php?module={$FLMODULE}&action=AjaxDetailViewUtils&record={$task['how_to']}&function=GET-HOW-TO&Ajax=true"
                                                            title="¡Aprende como!"><i class="bi bi-question-square"></i></a>
                                                    {/if}
                                                    <button type="button" class="btn btn-info"
                                                        onclick="if (window.WorkTaskActivityModal && typeof window.WorkTaskActivityModal.openView === 'function') { window.WorkTaskActivityModal.openView({$task['crmid']}); } else { alert('Error: El modal de tareas no está disponible. Por favor, recargue la página.'); } return false;"
                                                        title="Ver información detallada de la tarea">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="edit btn btn-primary"
                                                        onclick="DetailViewTabUtils.editTask ('{$idTaskDetail}')">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    {if $task['eventstatus'] eq 'Planned'}
                                                        <button type="button" class="delete btn btn-danger"
                                                            onclick="DetailViewTabUtils.deleteTaskRow('{$idTaskDetail}', '{$task['crmid']}')">
                                                            <i class="fa fa-trash-o"></i>
                                                        </button>
                                                    {/if}
                                                    <button type="button" class="edit btn btn-success"
                                                        onclick="DetailViewTabUtils.setCompleted ('{$idTaskDetail}')">
                                                        <i class="fa fa-check-square" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="description-{$idTaskDetail}" class="task-desc-content" style="margin-left: 0;margin-top: 0.25em;">
                                                {$task['description']}
                                            </div>
                                        </li>
                                        <li id="task-form-{$idTaskDetail}" class="list-group-item list-form">
                                            <form id="main_input_box-{$idTaskDetail}">
                                                <input type="hidden" name="relatedcrmids[]" value="{$ID}">
                                                <input type="hidden" name="record" value="{$task['crmid']}">
                                                <input type="hidden" name="module" value="Calendar">
                                                <input type="hidden" name="formodule" value="{$FLMODULE}">
                                                <input type="hidden" name="mode" value="edit">
                                                <input type="hidden" name="planned_task" value="PLANNED_AND_RECORDED">
                                                <input type="hidden" name="action" value="Save">
                                                <input type="hidden" name="function" value="TASK_FROM_MODULE">
                                                <input type="hidden" id="user-name-{$idTaskDetail}" value="{$CURRENT_USER_NAME}">
                                                <input type="hidden" name="assigned_user_id" value="{$CURRENT_USER_ID}">
                                                <input type="hidden" id="taskpriority-{$idTaskDetail}" value="{$task['priority']}">
                                                <input type="hidden" id="eventstatus-{$idTaskDetail}" value="{$task['eventstatus']}">
                                                <input type="hidden" id="taskImport-{$idTaskDetail}" value="{$task['importance']}">
                                                <input type="hidden" id="categoryid-{$idTaskDetail}" value="{$task['categoryid']}">
                                                {* Grupo one *}
                                                <div class="flex-container nowrap space-between items-align-star">
                                                    <div class="flex-container wrap flex-start items-align-baseline">
                                                        <h4 style="width: 100%;font-weight: bold">
                                                            <input class="form-control" name="subject" type="text"
                                                                value="{$task['subject']}">
                                                        </h4>
                                                        <div class="item-date" style="margin-right: 0.5em">
                                                            {* Activity Type *}
                                                            <div class="input-group">
                                                                <span class="input-group-addon border"><i class="fa fa-bars"></i></span>
                                                                <select id="activitytype-{$idTaskDetailView}" name="activitytype"
                                                                    onchange="DetailViewTabUtils.selectedActivityTypes (this, '{$idTaskDetail}')"
                                                                    class="form-control">
                                                                    {foreach $AVAILABLE_ACTIVITY_TYPES as $activityType => $activityLabel}
                                                                        <option value="{$activityType}"
                                                                            {if ($activityType == $task['activitytype'])}
                                                                            selected="selected" {/if}>{$activityLabel}</option>
                                                                    {/foreach}
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="item-date" style="margin-right: 0.5em">
                                                            {* Start day *}
                                                            <div class="input-group">
                                                                <span class="input-group-addon border"><i
                                                                        class="fa fa-calendar"></i></span>
                                                                <input class="form-control datepickerDate-{$idTaskDetailView}"
                                                                    name="date_start" id="start_day-{$idTaskDetail}"
                                                                    placeholder="Realizar a partir de" type="text"
                                                                    value="{$task['date_start']}">
                                                            </div>
                                                        </div>
                                                        {* due_date *}
                                                        <div class="item-date" style="margin-right: 0.5em">
                                                            <!-- {$task['activitytype']}-->
                                                            <div
                                                                class="input-group {if ($task['activitytype'] eq 'Call') || ($task['activitytype'] eq 'Meeting')}hide{/if}">
                                                                <span class="input-group-addon border"><i
                                                                        class="fa fa-calendar"></i></span>
                                                                <input class="form-control datepickerDate-{$idTaskDetailView}"
                                                                    name="due_date" id="due-date-{$idTaskDetail}"
                                                                    placeholder="Realizar antes de" type="text"
                                                                    value="{$task['due_date']}">
                                                            </div>
                                                        </div>
                                                        {*time_start *}
                                                        <div
                                                            class="input-group {if ($task['activitytype'] eq 'Assignment') || $task['activitytype'] eq 'Activity'}hide{/if}">
                                                            <span class="input-group-addon">Inicia a las</span>
                                                            <input type="time" class="form-control" name="time_start"
                                                                placeholder="Hora de inicio" value="{$task['time_start']}"
                                                                id="start_time-{$idTaskDetail}">
                                                        </div>

                                                    </div>

                                                    <span id="help-{$idTaskDetail}" class="help-block" style="color: red"></span>
                                                    {* btn-group *}
                                                    <div class="btn-group">
                                                        <button type="button" title="Actualizar tarea" class="edit btn btn-primary"
                                                            data-id-main="{$idTaskDetailView}"
                                                            onclick="DetailViewTabUtils.createTask (this, '{$idTaskDetail}', 'UPDATE')">
                                                            <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                                                        </button>
                                                        <button id="task-cancel-edit-{$idTaskDetail}" type="button"
                                                            title="Cancelar edición" class="edit btn btn-danger"
                                                            onclick="DetailViewTabUtils.cancelEditTask()">
                                                            <i class="fa fa-times" aria-hidden="true"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="flex-container nowrap items-align-star" style="margin-top: 0.5em">
                                                    {* estimated time*}
                                                    <div class="item-date" style="margin-right: 0.5em">
                                                        {* estimated time*}
                                                        <div class="input-group">
                                                            <span class="input-group-addon">Unidades planificadas</span>
                                                            <input type="text" class="form-control" name="estimated_time"
                                                                placeholder="Duración estimada"
                                                                onkeydown="DetailViewTabUtils.normalizeEstimatedTime (this, event, '');"
                                                                value="{$task['estimated_time']}" id="estimated_time-{$idTaskDetail}">
                                                        </div>
                                                        {* estimated time*}
                                                    </div>
                                                    <div class="item-date" style="margin-right: 0.5em">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">Unidad</span>
                                                            {if $AVAILABLE_ESTIMATED_TIME_UNITS neq NULL}
                                                                <select name="estimated_time_unit" class="form-control"
                                                                    id="estimated_time_unit-{$idTaskDetail}">
                                                                    {foreach $AVAILABLE_ESTIMATED_TIME_UNITS as $unitKey => $unitLabel}
                                                                        <option value="{$unitKey}"
                                                                            {if $unitKey eq $task['estimated_time_unit']}selected{/if}>
                                                                            {$unitLabel}
                                                                        </option>
                                                                    {/foreach}
                                                                </select>
                                                            {else}
                                                                <select name="estimated_time_unit" class="form-control"
                                                                    id="estimated_time_unit-{$idTaskDetail}">
                                                                    <option value="Hora"
                                                                        {if $task['estimated_time_unit'] eq 'Hora'}selected{/if}>Hora
                                                                    </option>
                                                                    <option value="Día"
                                                                        {if $task['estimated_time_unit'] eq 'Día'}selected{/if}>Día</option>
                                                                    <option value="Semana"
                                                                        {if $task['estimated_time_unit'] eq 'Semana'}selected{/if}>Semana
                                                                    </option>
                                                                    <option value="Mes"
                                                                        {if $task['estimated_time_unit'] eq 'Mes'}selected{/if}>Mes</option>
                                                                </select>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                    {* estimated cost*}
                                                    <div class="item-date" style="margin-right: 0.5em">
                                                        <div class="input-group">
                                                            <span class="input-group-addon">Costo estimado</span>
                                                            <input type="text" class="form-control" name="estimated_cost"
                                                                data-number-format="decimal" data-default-value="0" data-decimals="2"
                                                                placeholder="0.00" value="{$task['estimated_cost']|default:'0.00'}"
                                                                id="estimated_cost-{$idTaskDetail}">
                                                        </div>
                                                    </div>
                                                    {* estimated cost*}
                                                    {* show_in_matrix *}
                                                    <div class="item-data" style="margin-right: 0.5em">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input type="checkbox" name="show_in_matrix"
                                                                    {if $task['show_in_matrix'] eq 'YES'}checked{/if} value="YES">
                                                                ¿Mostrar en Matriz diaria?
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex-container nowrap space-between items-align-star"
                                                    style="margin-top: 0.5em">
                                                    <div class="flex-container wrap flex-start items-align-baseline">
                                                        {* User assigned*}
                                                        {if $AVAILABLE_USERS neq NULL}
                                                            <div class="btn-group">
                                                                <button id="btn-group-user-{$idTaskDetail}" type="button"
                                                                    class="btn {if $task['invitee']['userId']|count ge 1}btn-primary{else}btn-default{/if} dropdown-toggle"
                                                                    title="asignar tarea" style="font-size: 15px!important"
                                                                    data-toggle="dropdown">
                                                                    <i class="fa {if $task['invitee']['userId']|count gt 1}fa-users{else}fa-user{/if}"
                                                                        aria-hidden="true"></i>&nbsp;
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul id="detailview-task-user-{$idTaskDetail}"
                                                                    class="dropdown-menu scroll-user-menu" role="menu">
                                                                    <li class="list-btn-header" title="Usuarios invitados">
                                                                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                                        <small>Usuarios invitados</small>
                                                                    </li>
                                                                    <li class="divider"></li>
                                                                    {if $AVAILABLE_USERS|count gt 0}
                                                                        {foreach $AVAILABLE_USERS as $id => $user}
                                                                            <li {if (in_array($id, $task['invitee']['userId']))}class="active"
                                                                                {/if}>
                                                                                <a href="#" title="{$user['name']}" rel="{{$id}}"
                                                                                    onclick="DetailViewTabUtils.selectedUser (event, this, '{$idTaskDetail}')">
                                                                                    <img class="img-circle" style="width: 36%; height: 36%"
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
                                                        {/if}
                                                        {* Importance*}
                                                        {if $AVAILABLE_IMPORTANCE neq NULL}
                                                            <div class="btn-group">
                                                                <button id="btn-group-importance-{$idTaskDetail}" type="button"
                                                                    class="btn btn-primary dropdown-toggle dropdown-toggle"
                                                                    title="Importancia de la tarea"
                                                                    style="font-size: 15px!important;margin-left: 0.1em"
                                                                    data-toggle="dropdown">
                                                                    <i class="fa
                                                        {if $task['importance'] eq 'HIGH'}
                                                        fa-arrow-up
                                                        {elseif $task['importance'] eq 'LOW'}
                                                        fa-arrow-down
                                                        {else}
                                                        fa-exclamation-triangle {/if}" aria-hidden="true"></i>
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul id="detailview-task-importance-{$idTaskDetail}"
                                                                    class="dropdown-menu scroll-user-menu" role="menu">
                                                                    <li class="list-btn-header" title="Importancia">
                                                                        <i class="fa fa-info-circle" aria-hidden="true"
                                                                            style="padding-right: 0"></i>
                                                                        <small>Importancia</small>
                                                                    </li>
                                                                    <li class="divider"></li>
                                                                    {foreach $AVAILABLE_IMPORTANCE as $key => $importance}
                                                                        <li {if $task['importance'] eq $key}class="active" {/if}>
                                                                            <a href="#" title="{$importance}" rel="{$key}"
                                                                                onclick="DetailViewTabUtils.selectedImportance (event, this, '{$idTaskDetail}')">
                                                                                {$importance}
                                                                            </a>
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {* PRIORITIES *}
                                                        {if $AVAILABLE_TASK_PRIORITIES neq NULL}
                                                            <div class="btn-group">
                                                                <button id="btn-group-priority-{$idTaskDetail}" type="button"
                                                                    class="btn btn-primary dropdown-toggle"
                                                                    title="Prioridad" style="font-size: 15px!important;margin-left: 0.2em"
                                                                    data-toggle="dropdown">
                                                                    <i class="fa
                                                        {if $task['priority'] eq 'Alto'}
                                                        fa-arrow-up
                                                        {elseif $task['priority'] eq 'Bajo'}
                                                        fa-arrow-down
                                                        {else}
                                                        fa-sort
                                                        {/if}" aria-hidden="true"></i>
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul id="detailview-task-priority-{$idTaskDetail}"
                                                                    class="dropdown-menu scroll-user-menu" role="menu">
                                                                    <li class="list-btn-header" title="Prioridad">
                                                                        <i class="fa fa-info-circle" aria-hidden="true"
                                                                            style="padding-right: 0"></i>
                                                                        <small>Prioridad</small>
                                                                    </li>
                                                                    <li class="divider"></li>
                                                                    {foreach $AVAILABLE_TASK_PRIORITIES as $priority}
                                                                        {if $priority eq 'Medio'}{continue}{/if}
                                                                        <li {if $task['priority'] eq $priority}class="active" {/if}>
                                                                            <a href="#" title="{$priority}" rel="{$priority}"
                                                                                onclick="DetailViewTabUtils.selectedPriority (event, this, '{$idTaskDetail}')">
                                                                                {$priority}
                                                                            </a>
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {* STATUSES *}
                                                        {if $AVAILABLE_EVENT_STATUSES neq NULL}
                                                            <div class="btn-group">
                                                                <button id="btn-group-task-status-{$idTaskDetail}" type="button"
                                                                    class="btn btn-primary dropdown-toggle" title="Estado de la tarea"
                                                                    style="font-size: 15px!important;margin-left: 0.2em"
                                                                    data-toggle="dropdown">
                                                                    <i class="fa
                                                        {if $task['eventstatus'] eq 'Held'}
                                                        fa-check
                                                        {elseif $task['eventstatus'] eq 'Not Held'}
                                                        fa-cogs
                                                        {elseif $task['eventstatus'] eq 'Planned'}
                                                        fa-calendar-o
                                                        {else}
                                                        fa-exchange{/if}" aria-hidden="true"></i>
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul id="detailview-task-status-{$idTaskDetail}"
                                                                    class="dropdown-menu scroll-user-menu" role="menu">
                                                                    <li class="list-btn-header" title="Estado de la tarea">
                                                                        <i class="fa fa-info-circle" aria-hidden="true"
                                                                            style="padding-right: 0"></i>
                                                                        <small>Estado</small>
                                                                    </li>
                                                                    <li class="divider"></li>
                                                                    {foreach $AVAILABLE_EVENT_STATUSES as $eventStatus => $eventStatusLabel}
                                                                        <li {if $task['eventstatus'] eq $eventStatus}class="active" {/if}>
                                                                            <a href="#" title="{$eventStatusLabel}" rel="{$eventStatus}"
                                                                                onclick="DetailViewTabUtils.selectedStatus (event, this, '{$idTaskDetail}', '{$task['eventstatus']}')">
                                                                                {$eventStatusLabel}
                                                                            </a>
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {* task Group*}
                                                        {if $CATEGORIES neq NULL}
                                                            <div class="btn-group">
                                                                <button id="btn-group-task-categories-{$idTaskDetail}" type="button"
                                                                    class="btn btn-primary dropdown-toggle"
                                                                    style="height: 2.4em!important;margin-left: 0.1em"
                                                                    title="Ubicación de la tareas" data-toggle="dropdown">
                                                                    <i class="fa fa-tasks" aria-hidden="true"></i>&nbsp;
                                                                    <span class="caret"></span>
                                                                </button>
                                                                <ul id="detailview-task-categories-{$idTaskDetail}" class="dropdown-menu"
                                                                    role="menu">
                                                                    <li class="list-btn-header" title="Ubicación de la tarea">
                                                                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                                        <small>Ubicación</small>
                                                                    </li>
                                                                    <li class="divider"></li>
                                                                    {foreach $CATEGORIES as $id => $name}
                                                                        <li {if $task['categoryid'] eq $id}class="active" {/if}>
                                                                            <a href="#" title="{$name}" rel="{$id}"
                                                                                onclick="DetailViewTabUtils.setCategory (event, this, '{$idTaskDetail}')">
                                                                                {$name}
                                                                            </a>
                                                                        </li>
                                                                    {/foreach}
                                                                </ul>
                                                            </div>
                                                        {/if}
                                                        {* task Group*}
                                                    </div>
                                                    {* grupo -2 *}
                                                </div>
                                                <div class="" style="margin-left: 0;margin-top: 10px;">
                                                    <small id="help-user-{$idTaskDetail}" style="margin-top:0; color: red"></small>
                                                    <textarea name="description" class="form-control"
                                                        style="width: 100%">{$task['description']}</textarea>
                                                </div>
                                            </form>
                                        </li>
                                    {/foreach}
                                </ol>
                            </div>
                        {/foreach}
                    {/if}
                </div>
            {/if}
            {if $HAS_GANTT}
                <div id="gantt-task-tab-{$idTaskDetailView}"
                    class="tab-pane fade{if $FLMODULE eq 'orden_de_trabajo'} in active{/if}">
                    <div class="card rounded car-task" style="margin-bottom: 2.5px!important;">
                        {if $TASKS_GANTT neq NULL}
                            {include file="GanttDiagram.tpl"}
                        {else}
                            <div class="alert alert-info">No hay tareas!</div>
                        {/if}
                    </div>
                </div>
            {/if}
            {if $HAS_KANBAN}
                <div id="kanban-task-tab-{$idTaskDetailView}"
                    class="tab-pane fade{if $FLMODULE eq 'orden_de_trabajo' && !$HAS_GANTT} in active{/if}">
                    <div class="card rounded car-task" style="margin-bottom: 2.5px!important;">
                        {if $KANBAN_BLOCKS neq NULL}
                            {include file="KanbanDiagram.tpl"}
                        {else}
                            <div class="alert alert-info">No hay kanban!</div>
                        {/if}
                    </div>
                </div>
            {/if}
        </div>
    </div>
</section>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/daterangepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/morris.js"></script>
<script type="text/javascript" src="modules/preloaded_tasks/precreated-task-utils.js"></script>
{include file='base/TaskRowTemplate.tpl'}
{include file="base/TaskGroupTemplate.tpl"}
<script type="text/javascript">
    //PreCreatedTasksUtils.init('{$FLMODULE}','{$idTaskDetailView}');
    {literal}
        jQuery('#main-{/literal}{$idTaskDetailView}{literal}').find ('.datepickerDate-{/literal}{$idTaskDetailView}{literal}').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
    {/literal}
    {literal}
        jQuery ('.task-timer-class-{/literal}{$idTaskDetailView}{literal}').timepicker ({
        minuteStep: 5,
            showSeconds: true,
            showMeridian: false,
            disableFocus: false,
            showWidget: true
        }).focus(function() {
            jQuery(this).next().trigger('click');
        });
    {/literal}
    {literal}
        jQuery ('#timepickerEnd-{/literal}{$idTaskDetailView}{literal}').timepicker ({
        minuteStep: 5,
            showSeconds: true,
            showMeridian: false,
            disableFocus: false,
            showWidget: true
        }).focus(function() {
            jQuery(this).next().trigger('click');
        });
    {/literal}
    {literal}
        (function($) {
            var $container  = $('#main-{/literal}{$idTaskDetailView}{literal}');
            var $navTabs    = $container.find('#task-tabs-{/literal}{$idTaskDetailView}{literal}');
            var $tabContent = $container.children('.tab-content');

            $navTabs.find('a[data-toggle="tab"]').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Evita que Bootstrap también procese este click
                var target = $(this).attr('href');
                $navTabs.find('li').removeClass('active');
                $(this).closest('li').addClass('active');
                $tabContent.children('.tab-pane').removeClass('in active');
                $tabContent.children(target).addClass('active in');
            });
        }(jQuery));
    {/literal}
</script>
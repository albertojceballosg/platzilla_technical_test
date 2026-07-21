<style>
    /* Usar un selector más específico para aislar completamente los estilos */
    #activity-modal-{$idCalendar} .dropdown-menu.show,
    .modal.fade#activity-modal-{$idCalendar} .dropdown-menu.show {
    display: block !important;
    }

    #activity-modal-{$idCalendar} .dropdown-toggle.active,
    .modal.fade#activity-modal-{$idCalendar} .dropdown-toggle.active {
    background-color: #e6e6e6 !important;
    border-color: #adadad !important;
    }

    /* Uso de !important para asegurar que los estilos no se filtren */
    #activity-modal-{$idCalendar} .input-group-addon.border {
    border: 1px solid #ccc !important;
    }

    #activity-modal-{$idCalendar} li.active {
    background-color: #0165a8 !important;
    }

    /* Estilo para botones con selección activa */
    #activity-modal-{$idCalendar} .btn-selected {
    background-color: #0165a8 !important;
    color: white !important;
    border-color: #014a7a !important;
    }

    #activity-modal-{$idCalendar} .btn-selected:hover,
    #activity-modal-{$idCalendar} .btn-selected:focus {
    background-color: #014a7a !important;
    color: white !important;
    }

    /* Scroll para dropdowns con muchos elementos */
    #activity-modal-{$idCalendar} .scroll-user-menu {
    max-height: 300px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    }

    #activity-modal-{$idCalendar} .dropdown-menu {
    max-height: 300px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    }

    #activity-modal-{$idCalendar} .form-control:focus {
    border-color: #66afe9 !important;
    outline: 0 !important;
    box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075),
    0 0 8px rgba(102, 175, 233, .6) !important;
    }

    #activity-modal-{$idCalendar} .activity-inline-row {
    display: flex;
    gap: 1em;
    flex-wrap: wrap;
    align-items: flex-start;
    }

    #activity-modal-{$idCalendar} .activity-inline-field {
    display: inline-flex;
    flex: 0 0 auto;
    }

    #activity-modal-{$idCalendar} .activity-inline-field .input-group,
    #activity-modal-{$idCalendar} .activity-inline-field select.form-control {
    width: auto;
    }

    #activity-modal-{$idCalendar} .activity-inline-field .form-control {
    width: auto;
    min-width: 6em;
    }

    #activity-modal-{$idCalendar} .activity-inline-field--module select.form-control {
    min-width: 12em;
    }

    /* Evitar que los estilos se filtren fuera */
    body:not(#activity-modal-{$idCalendar}) .dropdown-toggle.active {
    /* Restaurar comportamiento normal fuera de la modal */
    display: initial !important;
    visibility: visible !important;
    opacity: 1 !important;
    }
</style>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="col-md-6">
            <p class="text-left" style="margin-bottom: 0; vertical-align: center">
                {$ACTIVITY_TYPE|default:'Accion'}: </p>
        </div>
        <div class="col-md-6" style="margin-right: 0;padding-right: 0;">&nbsp;</div>
    </div>
    <form class="form-inline" role="form" id="main_input_box-{$idCalendar}">
        <input type="hidden" name="record" value="">
        <input type="hidden" name="module" value="Calendar">
        <input type="hidden" id="formodule-{$idCalendar}" name="formodule" value="">
        <input type="hidden" name="function" value="TASK_FROM_MODULE">
        <input type="hidden" name="assigned_user_id" value="{$CURRENT_USER_ID}">
        <input type="hidden" id="user-name-{$idCalendar}" value="{$CURRENT_USER_NAME}">
        <input type="hidden" name="planned_task" value="PLANNED_AND_RECORDED">
        <input type="hidden" name="show_in_matrix" value="YES">
        <input type="hidden" name="action" value="Save">
        <input type="hidden" name="Ajax" value="true">
        <input type="hidden" name="eventstatus" id="eventstatus" value="Planned">
        <input type="hidden" id="today-{$idCalendar}" value="{$TODAY}">
        <input type="hidden" id="tomorrow-{$idCalendar}" value="{$TOMORROW}">

        <input type="hidden" id="inviteesid-{$idCalendar}" name="inviteesid" value="">
        <input type="hidden" id="taskImport-{$idCalendar}" name="taskImport" value="">
        <input type="hidden" id="taskpriority-{$idCalendar}" name="taskpriority" value="">
        <input type="hidden" id="categoryid-{$idCalendar}" name="categoryid" value="">


        <div class="col-lg-12 col-md-12 col-sm-12">
            <input type="text" class="form-control col-lg-12 col-md-12 col-sm-12" id="taskname-{$idCalendar}"
                name="subject" placeholder="¿Que necesitas hacer?" style="width: 100%!important;">
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 2px">
            <textarea class="form-control col-lg-12 col-md-12 col-sm-12" id="task_description-{$idCalendar}"
                name="description" rows="4" placeholder="Descripción de la actividad"
                style="width: 100%!important;"></textarea>
        </div>
        {* Activity Type *}
        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 0.25em">
            <div class="col-lg-3 col-md-3 col-sm-3 input-group">
                <span class="input-group-addon border"><i class="fa fa-bars"></i></span>
                <select id="activitytype-{$idCalendar}" name="activitytype" class="form-control">
                    {foreach $AVAILABLE_ACTIVITY_TYPES as $activityType => $activityLabel}
                        <option value="{$activityType}" {if $activityType eq 'Assignment'}selected="selected" {/if}>
                            {$activityLabel}</option>
                    {/foreach}
                </select>
            </div>

            <div class="col-lg-3 col-md-3 col-sm-3 input-group">
                <span class="input-group-addon border"><i class="fa fa-calendar"></i></span>
                <input type="text" class="form-control datepickerDate-{$idCalendar}"
                    style="margin-right: 2px!important;" id="date_start-{$idCalendar}" name="date_start"
                    placeholder="Realizar a partir de" autocomplete="off" readonly="readonly" value="">
            </div>

            <div class="col-lg-3 col-md-3 col-sm-3 input-group activity-date-{$idCalendar}">
                <span class="input-group-addon border"><i class="fa fa-calendar"></i></span>
                <input type="text" class="form-control datepickerDate-{$idCalendar}" id="due_date-{$idCalendar}"
                    name="due_date" placeholder="Realizar antes de" autocomplete="off" readonly="readonly" value="">
            </div>

            <div class="col-lg-3 col-md-3 col-sm-3 input-group activity-time-{$idCalendar}">
                <span class="input-group-addon border"><i class="fa fa-clock-o"></i></span>
                <input type="time" class="form-control" name="time_start" placeholder="Hora de inicio" value="09:00:00"
                    id="start_time-{$idCalendar}">
            </div>

        </div>
        {* Estimated Time *}
        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 0.25em">
            <div class="activity-inline-row">
                <div class="activity-inline-field activity-inline-field--hours">
                    <div class="input-group">
                        <span class="input-group-addon">Nro. Unidades</span>
                        <input type="text" class="form-control" name="estimated_time" data-number-format="decimal"
                            data-default-value="0.5" data-decimals="2" placeholder="Nro de unidades estimadas" value="0.5"
                            id="estimated_time-{$idCalendar}">
                    </div>
                </div>
                <div class="activity-inline-field">
                    <div class="input-group">
                        <span class="input-group-addon">Unidad</span>
                        {if $AVAILABLE_ESTIMATED_TIME_UNITS neq NULL}
                            <select name="estimated_time_unit" class="form-control" id="estimated_time_unit-{$idCalendar}">
                                {foreach $AVAILABLE_ESTIMATED_TIME_UNITS as $unitKey => $unitLabel}
                                    <option value="{$unitKey}" {if $unitKey eq $DEFAULT_ESTIMATED_TIME_UNIT}selected{/if}>
                                        {$unitLabel}
                                    </option>
                                {/foreach}
                            </select>
                        {else}
                            <select name="estimated_time_unit" class="form-control" id="estimated_time_unit-{$idCalendar}">
                                <option value="Hora" selected>Hora</option>
                                <option value="Día">Día</option>
                                <option value="Semana">Semana</option>
                                <option value="Mes">Mes</option>
                            </select>
                        {/if}
                    </div>
                </div>
                <div class="activity-inline-field activity-inline-field--cost">
                    <div class="input-group">
                        <span class="input-group-addon">Costo estimado</span>
                        <input type="text" class="form-control" name="estimated_cost" data-number-format="decimal"
                            data-default-value="0" data-decimals="2" id="estimated_cost-{$idCalendar}"
                            placeholder="0.00" min="0" step="0.01" value="0.00">
                    </div>
                </div>
                <div class="activity-inline-field activity-inline-field--module">
                    {* MODULES  *}
                    {if $AVAILABLE_MODULES neq NULL}
                        <select id="reported_task_module-{$idCalendar}" data-current-module="daily_report"
                            data-field-id="module_related_record-{$idCalendar}"
                            data-display-field-id="selected_record_display-{$idCalendar}" data-referenced-module=""
                            data-title="" onchange="" class="form-control">
                            <option value="">Módulo relacionado</option>
                            {foreach $AVAILABLE_MODULES as $avaModule}
                                {if $avaModule['status'] eq 'HIDDEN'}{continue}{/if}
                                <option value="{$avaModule['name']}@{$avaModule['tabid']}">{$avaModule['tablabel']}</option>
                            {/foreach}
                        </select>
                    {else}
                        <span class="text-muted" style="display: block; margin-top: 8px;">Sin módulos disponibles</span>
                    {/if}
                </div>
                {* Display del registro seleccionado *}
                <div class="activity-inline-field activity-inline-field--selected-record"
                    style="flex: 1; min-width: 150px;">
                    {if $AVAILABLE_MODULES neq NULL}
                        <span id="selected_record_display-{$idCalendar}" class="selected-record-label form-control-static"
                            style="display: inline-block; padding: 6px 12px; font-weight: 500; color: #337ab7; 
                                     background-color: #f5f5f5; border-radius: 4px; min-height: 34px; 
                                     font-size: 0.9em; line-height: 1.4; word-wrap: break-word; white-space: normal;">
                        </span>
                    {/if}
                </div>
            </div>
            {if $AVAILABLE_MODULES neq NULL}
                <input type="hidden" id="module_related_record-{$idCalendar}" name="relatedcrmids" value=""
                    class="for-filter module-reference">
            {/if}
        </div>
        {* Users impotance priority category *}
        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 6px">
            {* User invitees*}
            {if $AVAILABLE_USERS neq NULL}
                <div class="btn-group dropup" style="margin-left: 0.125em!important;">
                    <button id="btn-group-user-{$idCalendar}" type="button" class="btn btn-default dropdown-toggle"
                        title="asignar tarea" style="font-size: 15px!important;margin-left: 0.1em" data-toggle="dropdown"
                        data-default-label="Usuarios">
                        <i style="color: #cccccc" class="fa fa-user" aria-hidden="true"></i>&nbsp;
                        <span class="btn-label">Usuarios</span>
                        <span class="caret"></span>
                    </button>
                    <ul id="detailview-task-user-{$idCalendar}" class="dropdown-menu scroll-user-menu" role="menu">
                        <li class="list-btn-header" title="Usuarios invitados">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <small>Usuarios invitados</small>
                        </li>
                        <li class="divider"></li>
                        {if $AVAILABLE_USERS|count gt 0}
                            {foreach $AVAILABLE_USERS as $id => $user}
                                <li>
                                    <a href="#" title="{$user['name']}" rel="{$id}">
                                        <img class="img-circle"
                                            style="width: 24px; height: 24px; max-width: 24px; max-height: 24px;"
                                            data-src="{$user['avatar']}" alt="{$user['name']}" src="{$user['avatar']}">
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
                <div class="btn-group dropup">
                    <button id="btn-group-importance-{$idCalendar}" type="button"
                        class="btn btn-default dropdown-toggle dropdown-toggle" title="Importancia de la tarea"
                        style="font-size: 13px!important;margin-left: 0.1em" data-toggle="dropdown"
                        data-default-label="Importancia">
                        <i class="fa  fa-exclamation-triangle" aria-hidden="true"></i>
                        <span class="btn-label">Importancia</span>
                        <span class="caret"></span>
                    </button>
                    <ul id="detailview-task-importance-{$idCalendar}" class="dropdown-menu scroll-user-menu" role="menu">
                        <li class="list-btn-header" title="Importancia">
                            <i class="fa fa-info-circle" aria-hidden="true" style="padding-right: 0"></i>
                            <small>Importancia</small>
                        </li>
                        <li class="divider"></li>
                        {foreach $AVAILABLE_IMPORTANCE as $key => $importance}
                            <li>
                                <a href="#" title="{$importance}" rel="{$key}">
                                    {if $importance eq 'Baja'}Estándar{else}{$importance}{/if}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
            {* Priorities *}
            {if $AVAILABLE_TASK_PRIORITIES neq NULL}
                <div class="btn-group dropup">
                    <button id="btn-group-priority-{$idCalendar}" type="button"
                        class="btn btn-default dropdown-toggle dropdown-toggle" title="Prioridad de la tarea"
                        style="font-size: 13px!important;margin-left: 0.1em" data-toggle="dropdown"
                        data-default-label="Prioridad">
                        <i class="fa fa-sort" aria-hidden="true"></i>
                        <span class="btn-label">Prioridad</span>
                        <span class="caret"></span>
                    </button>
                    <ul id="detailview-task-priority-{$idCalendar}" class="dropdown-menu scroll-user-menu" role="menu">
                        <li class="list-btn-header" title="Prioridad">
                            <i class="fa fa-info-circle" aria-hidden="true" style="padding-right: 0"></i>
                            <small>Prioridad</small>
                        </li>
                        <li class="divider"></li>
                        {foreach $AVAILABLE_TASK_PRIORITIES as $priority}
                            {if $priority eq 'Medio'}{continue}{/if}
                            <li>
                                <a href="#" title="{$priority}" rel="{$priority}">
                                    {if $priority eq 'Alto'}Alta{else}Básica{/if}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
            {* task Group*}
            {if $CATEGORIES neq NULL}
                <div class="input-group">
                    <div class="input-group-btn dropup">
                        <button id="btn-group-task-categories-{$idCalendar}" type="button"
                            class="btn btn-default dropdown-toggle" style="font-size: 13px!important;height: 2.4em!important;margin-left: 0.1em"
                            title="Ubicación de la tareas" data-toggle="dropdown" data-default-label="Ubicación">
                            <i style="color: #cccccc" class="fa fa-tasks" aria-hidden="true"></i>&nbsp;
                            <span class="btn-label">Ubicación</span>
                            <span class="caret"></span>
                        </button>
                        <ul id="detailview-task-categories-{$idCalendar}" class="dropdown-menu" role="menu">
                            <li class="list-btn-header" title="Ubicación de la tarea">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                <small>Ubicación</small>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="#" title="Crear un nuevo grupo" rel="0">
                                    Crear grupo
                                </a>
                            </li>
                            <li class="divider"></li>
                            {foreach $CATEGORIES as $id => $name}
                                <li>
                                    <a href="#" title="{$name}" rel="{$id}">
                                        {$name}
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                    <input id="categoryname-{$idCalendar}" name="categoryname" type="text" class="form-control hide">
                </div>
            {/if}
            {* task Group*}
        </div>
    </form>
    <div class="col-lg-12 col-md-12 col-sm-12" style="float: left: margin-top: 6px;">
        <small id="help-user-{$idCalendar}" style="margin-top:0; color: red">
        </small><small id="help-priority-{$idCalendar}" style="margin-top:0; color: red"></small>
        <small id="help-status-{$idCalendar}" style="margin-top:0; color: red">
        </small><small id="help-group-{$idCalendar}" style="margin-top:0; color: red"></small>
    </div>
</div>
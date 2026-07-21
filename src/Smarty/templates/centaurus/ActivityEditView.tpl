{math equation= rand() assign= "idEditView"}
<style>
    #ActivityEditView .dropdown-menu.show,
    .modal.fade #ActivityEditView .dropdown-menu.show {
        display: block !important;
    }
    #ActivityEditView .dropdown-toggle.active,
    .modal.fade #ActivityEditView .dropdown-toggle.active {
        background-color: #e6e6e6 !important;
        border-color: #adadad !important;
    }
    #ActivityEditView .input-group-addon.border {
        border: 1px solid #ccc !important;
    }
    #ActivityEditView li.active {
        background-color: #0165a8 !important;
    }
    #ActivityEditView .btn-selected {
        background-color: #0165a8 !important;
        color: white !important;
        border-color: #014a7a !important;
    }
    #ActivityEditView .btn-selected:hover,
    #ActivityEditView .btn-selected:focus {
        background-color: #014a7a !important;
        color: white !important;
    }
    #ActivityEditView .scroll-user-menu {
        max-height: 300px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    #ActivityEditView .dropdown-menu {
        max-height: 300px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }
    #ActivityEditView .form-control:focus {
        border-color: #66afe9 !important;
        outline: 0 !important;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6) !important;
    }
    #ActivityEditView .activity-inline-row {
        display: flex !important;
        gap: 0.75em;
        flex-wrap: nowrap;
        align-items: flex-start;
        width: 100%;
    }
    #ActivityEditView .activity-inline-field {
        display: inline-flex;
        flex: 0 0 auto;
    }
    #ActivityEditView .activity-inline-field .input-group,
    #ActivityEditView .activity-inline-field select.form-control {
        width: auto;
    }
    #ActivityEditView .activity-inline-field .input-group {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
    }
    #ActivityEditView .activity-inline-field .form-control {
        width: auto;
        min-width: 5em;
    }
    #ActivityEditView .activity-inline-field .input-group-addon {
        white-space: nowrap;
        overflow: visible;
        padding: 6px 10px;
    }
    #ActivityEditView .activity-inline-field .input-group .fieldlabelcss,
    #ActivityEditView .activity-inline-field .input-group .fieldlabelcss1 {
        display: inline-block;
        width: auto !important;
        margin-top: 0 !important;
        margin-right: 0;
        white-space: nowrap;
        padding: 9px 10px;
        font-size: 14px;
        font-weight: 400;
        line-height: 1;
        color: #555;
        text-align: center;
        background-color: #eee;
        border: 1px solid #ccc;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        vertical-align: middle;
    }
    #ActivityEditView .activity-inline-field .input-group .fieldlabelcss + .form-control,
    #ActivityEditView .activity-inline-field .input-group .fieldlabelcss1 + .form-control,
    #ActivityEditView .activity-inline-field .input-group .fieldlabelcss + .selected-record-label,
    #ActivityEditView .activity-inline-field .input-group .fieldlabelcss1 + .selected-record-label {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-left: 0 !important;
    }
    #ActivityEditView .activity-inline-field--hours .form-control {
        min-width: 4em;
    }
    #ActivityEditView .activity-inline-field--unit select.form-control {
        min-width: 5em;
    }
    #ActivityEditView .activity-inline-field--cost .form-control {
        min-width: 5em;
    }
    #ActivityEditView .activity-inline-field--module {
        flex: 0 0 auto;
        max-width: 35%;
    }
    #ActivityEditView .activity-inline-field--module select.form-control {
        min-width: 6em;
        max-width: 100%;
    }
    #ActivityEditView .activity-inline-field--selected-record {
        min-width: 120px !important;
        max-width: 60%;
        flex: 1 1 auto;
    }
    #ActivityEditView .activity-inline-field--selected-record #ActivityEditView_field.input-group,
    #ActivityEditView .activity-inline-field--selected-record .input-group {
        min-width: auto !important;
        width: 100%;
    }
    #ActivityEditView .activity-inline-field--selected-record .fieldlabelcss1 {
        width: auto !important;
        min-width: auto !important;
        margin-top: 0 !important;
        margin-right: 0;
        white-space: nowrap;
        padding: 9px 10px;
        font-size: 14px;
        font-weight: 400;
        line-height: 1;
        color: #555;
        text-align: center;
        background-color: #eee;
        border: 1px solid #ccc;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        vertical-align: middle;
    }
    #ActivityEditView .activity-inline-field--selected-record .selected-record-label {
        width: auto !important;
        min-width: auto !important;
        max-width: 100%;
        margin-top: 0 !important;
        display: inline-block !important;
        visibility: visible !important;
        color: #337ab7 !important;
        background-color: #f5f5f5 !important;
        padding: 9px 12px !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        border-top-right-radius: 4px !important;
        border-bottom-right-radius: 4px !important;
    }
</style>

<div class="row" id="ActivityEditView">
    <div class="col-lg-12 col-md-12 col-sm-12" style="margin-bottom: 0.3em; margin-top:0.3em;width:98.0vw;">
        <div class="col-md-6">
            <p class="text-left" style="margin-bottom: 0; vertical-align: center; font-size: 1.2em; font-weight:500;margin-top:0.0em;">
                {$MOD.LBL_TASK_INFORMATION|default:'Editar Tarea'}
            </p>
        </div>
        <div class="col-md-6 text-right" style="margin-bottom: 0.5em;width:25vw; margin-top:-0.3em;">
            {if $FROM_WORK neq NULL}
                <button type="button" class="btn btn-primary" onclick="ActivityUtils.saveTask(this, '{$idEditView}');">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
                <button type="button" id="close-{$idEditView}" class="btn btn-warning" data-dismiss="modal" aria-hidden="true">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
            {else}
                <button type="submit" class="btn btn-primary">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
                <button type="button" class="btn btn-warning" onclick="window.history.back()">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
            {/if}
        </div>
    </div>

    <form class="form-inline" role="form" id="EditView-{$idEditView}" name="EditView" data-localizador="ActivityEditViewForm" method="POST" action="index.php" {if $FROM_WORK eq NULL}onsubmit="return ActivityUtils.validateForm(this, '{$idEditView}');"{/if}>
        <input type="hidden" name="record" value="{$ID}">
        <input type="hidden" name="module" value="{$MODULE}">
        <input type="hidden" name="action" value="Save">
        <input type="hidden" name="mode" value="{$MODE}">
        <input type="hidden" name="createmode" value="{$CREATEMODE}">
        <input type="hidden" name="visibility" value="Public">
        <input type="hidden" id="formodule-{$idEditView}" name="formodule" value="">
        <input type="hidden" name="pagenumber" value="{$smarty.request.start|@vtlib_purify}">
        <input type="hidden" id="today-{$idEditView}" value="{$ACTIVITYDATA.date_start|default:$smarty.now|date_format:'%Y-%m-%d'}">
        <input type="hidden" id="tomorrow-{$idEditView}" value="{$ACTIVITYDATA.due_date|default:$smarty.now|date_format:'%Y-%m-%d'}">
        <input type="hidden" id="inviteesid-{$idEditView}" name="inviteesid" value="">
        <input type="hidden" id="taskImport-{$idEditView}" name="taskImport" value="{$ACTIVITYDATA.importance|default:''}">
        <input type="hidden" id="taskpriority-{$idEditView}" name="taskpriority" value="{$ACTIVITYDATA.taskpriority|default:''}">
        <input type="hidden" id="categoryid-{$idEditView}" name="categoryid" value="{$ACTIVITYDATA.categoryid|default:''}">
        <input type="hidden" id="user-name-{$idEditView}" value="{$CURRENTUSERID}">
        <input type="hidden" id="current-user-id-{$idEditView}" value="{$CURRENTUSERID}">
        <input type="hidden" name="time_end" value="{$ACTIVITYDATA.time_end|default:'09:00:00'}">
        {if $FROM_WORK eq NULL}
            <input type="hidden" name="show_in_matrix" value="{$ACTIVITYDATA.show_in_matrix|default:'NO'}">
        {/if}
        <input type="hidden" name="planned_task" value="{$ACTIVITYDATA.planned_task|default:'PLANNED_AND_RECORDED'}">

        {if $FROM_WORK neq NULL}
            <input type="hidden" name="show_in_matrix" value="YES">
            <input type="hidden" name="function" value="TASK_FROM_MODULE">
            <input type="hidden" name="Ajax" value="true">
        {/if}
        {if isset($RETURN_ACTION)}
            <input type="hidden" name="return_action" value="{$RETURN_ACTION}">
        {/if}
        {if isset($RETURN_ID)}
            <input type="hidden" name="return_id" value="{$RETURN_ID}">
        {/if}
        {if isset($RETURN_MODULE)}
            <input type="hidden" name="return_module" value="{$RETURN_MODULE}">
        {/if}
        {if isset($RETURN_VIEWNAME)}
            <input type="hidden" name="return_viewname" value="{$RETURN_VIEWNAME}">
        {/if}
        {if isset($PARENT_ID)}
            <input type="hidden" name="parent_id" value="{$PARENT_ID}">
        {/if}
        {if isset($CONTACT_ID)}
            <input type="hidden" name="contact_id" value="{$CONTACT_ID}">
        {/if}

        <div class="col-lg-12 col-md-12 col-sm-12">
            <input type="text" class="form-control col-lg-12 col-md-12 col-sm-12" id="taskname-{$idEditView}"
                name="subject" placeholder="{$MOD.LBL_EVENTNAME}" style="width: 100%!important;"
                value="{$ACTIVITYDATA.subject|escape:'html'}">
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 2px">
            <textarea class="form-control col-lg-12 col-md-12 col-sm-12" id="task_description-{$idEditView}"
                name="description" rows="4" placeholder="{$MOD.LBL_APP_DESCRIPTION}"
                style="width: 100%!important;">{$ACTIVITYDATA.description|escape:'html'}</textarea>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 0.25em">
            <div class="col-lg-3 col-md-3 col-sm-3 input-group" style="width:20vw; margin-right:1em;">
                <span class="input-group-addon border" style="width:3em;"><i class="fa fa-bars"></i></span>
                <select id="activitytype-{$idEditView}" name="activitytype" class="form-control" >
                    {foreach $AVAILABLE_ACTIVITY_TYPES as $activityType => $activityLabel}
                        <option value="{$activityType}" {if $ACTIVITYDATA.activitytype eq $activityType}selected="selected"{/if}>
                            {$activityLabel}</option>
                    {/foreach}
                </select>
            </div>

            <div class="col-lg-3 col-md-3 col-sm-3 input-group" style="width:15vw; margin-right:1em;">
                <span class="input-group-addon border" style="width:3em;"><i class="fa fa-calendar"></i></span>
                <input type="text" class="form-control datepickerDate-{$idEditView}"
                    style="margin-right: 2px!important;" id="date_start-{$idEditView}" name="date_start"
                    placeholder="Realizar a partir de" autocomplete="off" readonly="readonly"
                    value="{$ACTIVITYDATA.date_start}">
            </div>

            <div class="col-lg-3 col-md-3 col-sm-3 input-group activity-date-{$idEditView}" style="width:15vw; margin-right:1em;">
                <span class="input-group-addon border" style="width:3em;"><i class="fa fa-calendar"></i></span>
                <input type="text" class="form-control datepickerDate-{$idEditView}" id="due_date-{$idEditView}"
                    name="due_date" placeholder="Realizar antes de" autocomplete="off" readonly="readonly"
                    value="{$ACTIVITYDATA.due_date}">
            </div>

            <div class="col-lg-3 col-md-3 col-sm-3 input-group activity-time-{$idEditView}" style="width:12vw; margin-right:1em;">
                <span class="input-group-addon border" style="width:3em;"><i class="fa fa-clock-o"></i></span>
                <input type="time" class="form-control" name="time_start" placeholder="Hora de inicio"
                    value="{$ACTIVITYDATA.time_start|default:'09:00:00'}" id="start_time-{$idEditView}">
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 0.25em; margin-right:1em;width:30vw;">
            <div class="col-lg-6 col-md-6 col-sm-6 input-group" style="width:13vw;margin-right:1em;">
                <span class="input-group-addon border" style="width:3em;"><i class="fa fa-flag"></i></span>
                <select id="eventstatus-{$idEditView}-select" name="eventstatus" class="form-control" style="width:12vw;">
                    {foreach $AVAILABLE_EVENT_STATUSES as $eventStatus => $eventStatusLabel}
                        <option value="{$eventStatus}" {if $ACTIVITYDATA.eventstatus eq $eventStatus}selected="selected"{/if}>
                            {$eventStatusLabel}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 input-group" style="margin-top: 0.25em; width:12vw;">
                <span class="input-group-addon border" style="width:3em;"><i class="fa fa-user"></i></span>
                <select id="assigned_user_id-select-{$idEditView}" name="assigned_user_id" class="form-control">
                    {foreach $AVAILABLE_USERS as $userId => $userFullName}
                        <option value="{$userId}" {if $ACTIVITYDATA.assigned_user_id eq $userId}selected="selected"{/if}>
                            {$userFullName}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 0.25em;" id="sec_2">
            <div class="activity-inline-row">
                <div class="activity-inline-field activity-inline-field--unit" style="margin-right:1em;">
                    <div class="input-group">
                        <span class="fieldlabelcss">Unidad</span>
                        <select name="estimated_time_unit" class="form-control" id="estimated_time_unit-{$idEditView}" style="min-width:6em;">
                            <option value="Hora" {if $ACTIVITYDATA.estimated_time_unit eq 'Hora'}selected{/if}>Hora</option>
                            <option value="Día" {if $ACTIVITYDATA.estimated_time_unit eq 'Día'}selected{/if}>Día</option>
                            <option value="Semana" {if $ACTIVITYDATA.estimated_time_unit eq 'Semana'}selected{/if}>Semana</option>
                            <option value="Mes" {if $ACTIVITYDATA.estimated_time_unit eq 'Mes'}selected{/if}>Mes</option>
                        </select>
                    </div>
                </div>
                <div class="activity-inline-field activity-inline-field--hours" style="margin-right:1em;">
                    <div class="input-group">
                        <span class="fieldlabelcss">Nro.Unidades</span>
                        <input type="text" class="form-control" name="estimated_time" data-number-format="decimal"
                            data-default-value="0.5" data-decimals="2" placeholder="Nro.Unidades"
                            value="{$ACTIVITYDATA.estimated_time|default:'0.5'}" id="estimated_time-{$idEditView}" style="min-width:5em;">
                    </div>
                </div>
                <div class="activity-inline-field activity-inline-field--cost" style="margin-right:1em;">
                    <div class="input-group">
                        <span class="fieldlabelcss">Costo estimado</span>
                        <input type="text" class="form-control" name="estimated_cost" data-number-format="decimal"
                            data-default-value="0" data-decimals="2" id="estimated_cost-{$idEditView}"
                            placeholder="0.00" min="0" step="0.01" value="{$ACTIVITYDATA.estimated_cost|default:'0.00'}" style="min-width:6em;">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 0.25em;" id="sec_3">
            <div class="activity-inline-row">
                <div class="activity-inline-field activity-inline-field--module" style="margin-right:1em;">
                    <div class="input-group">
                        <span class="fieldlabelcss">Módulo relacionado</span>
                        {if $AVAILABLE_MODULES neq NULL}
                            <select id="reported_task_module-{$idEditView}" data-current-module="orden_de_trabajo"
                                data-current-entity-id="{$ID}"
                                data-field-id="module_related_record-{$idEditView}"
                                data-display-field-id="selected_record_display-{$idEditView}" data-referenced-module=""
                                data-title="" data-multiple-selection="false" class="form-control" style="min-width:10em;">
                                <option value="">Módulo relacionado</option>
                                {foreach $AVAILABLE_MODULES as $avaModule}
                                    {if $avaModule['status'] eq 'HIDDEN'}{continue}{/if}
                                    {assign var="relatedModuleName" value=""}
                                    {if !empty($RELATED) && is_array($RELATED) && isset($RELATED[0].modulename)}
                                        {assign var="relatedModuleName" value=$RELATED[0].modulename}
                                    {/if}
                                    <option value="{$avaModule['name']}@{$avaModule['tabid']}" {if $relatedModuleName eq $avaModule['name']}selected="selected"{/if}>
                                        {$avaModule['tablabel']}</option>
                                {/foreach}
                            </select>
                        {else}
                            <span class="text-muted" style="display: block; margin-top: 8px;">Sin módulos disponibles</span>
                        {/if}
                    </div>
                </div>
                <div class="activity-inline-field activity-inline-field--selected-record" style="flex: 1; min-width: 120px;">
                    <div class="input-group" id="ActivityEditView_field">
                        <span class="fieldlabelcss1">Registro relacionado</span>
                        {if $AVAILABLE_MODULES neq NULL}
                            <span id="selected_record_display-{$idEditView}" class="selected-record-label form-control-static"
                                style="display: inline-block; padding: 6px 12px; font-weight: 500; color: #337ab7; 
                                         background-color: #f5f5f5; border-radius: 4px; min-height: 34px; 
                                         font-size: 0.9em; line-height: 1.4; word-wrap: break-word; white-space: normal;">
                                {if !empty($RELATED) && is_array($RELATED)}{$RELATED[0].label_entity|strip_tags}{/if}
                            </span>
                        {/if}
                    </div>
                </div>
            </div>
            {if $AVAILABLE_MODULES neq NULL}
                <input type="hidden" id="module_related_record-{$idEditView}" name="relatedcrmids" value="{if !empty($RELATED) && is_array($RELATED)}{$RELATED[0].crmid}{/if}" class="for-filter module-reference">
            {/if}
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12" style="margin-top: 1em">
            {if $AVAILABLE_IMPORTANCE neq NULL}
                <div class="btn-group dropup">
                    <button id="btn-group-importance-{$idEditView}" type="button"
                        class="btn {if !empty($ACTIVITYDATA.importance)}btn-primary{else}btn-default{/if} dropdown-toggle dropdown-toggle" title="Importancia de la tarea"
                        style="font-size: 13px!important;margin-left: 0.1em;padding-left:1em;padding-rigth:1em;" data-toggle="dropdown"
                        data-default-label="Importancia">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;
                        <span class="btn-label">
                            {if !empty($ACTIVITYDATA.importance)}
                                {foreach $AVAILABLE_IMPORTANCE as $key => $importance}
                                    {if $ACTIVITYDATA.importance eq $key}
                                        {if $importance eq 'Baja'}Estándar{else}{$importance}{/if}
                                    {/if}
                                {/foreach}
                            {else}Importancia{/if}
                        </span>&nbsp;
                        <span class="caret"></span>
                    </button>
                    <ul id="detailview-task-importance-{$idEditView}" class="dropdown-menu scroll-user-menu" role="menu">
                        <li class="list-btn-header" title="Importancia">
                            <i class="fa fa-info-circle" aria-hidden="true" style="padding-right: 0"></i>
                            <small>Importancia</small>
                        </li>
                        <li class="divider"></li>
                        {foreach $AVAILABLE_IMPORTANCE as $key => $importance}
                            <li class="{if $ACTIVITYDATA.importance eq $key}active{/if}">
                                <a href="#" title="{$importance}" rel="{$key}">
                                    {if $importance eq 'Baja'}Estándar{else}{$importance}{/if}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
            {if $AVAILABLE_TASK_PRIORITIES neq NULL}
                <div class="btn-group dropup">
                    <button id="btn-group-priority-{$idEditView}" type="button"
                        class="btn {if !empty($ACTIVITYDATA.taskpriority)}btn-primary{else}btn-default{/if} dropdown-toggle dropdown-toggle" title="Prioridad de la tarea"
                        style="font-size: 13px!important;margin-left: 0.1em;padding-left:1em;padding-rigth:1em" data-toggle="dropdown"
                        data-default-label="Prioridad">
                        <i class="fa fa-sort" aria-hidden="true"></i>&nbsp;
                        <span class="btn-label">
                            {if !empty($ACTIVITYDATA.taskpriority)}
                                {foreach $AVAILABLE_TASK_PRIORITIES as $priority}
                                    {if $ACTIVITYDATA.taskpriority eq $priority}
                                        {if $priority eq 'Alto'}Alta{else}Básica{/if}
                                    {/if}
                                {/foreach}
                            {else}Prioridad{/if}
                        </span>&nbsp;
                        <span class="caret"></span>
                    </button>
                    <ul id="detailview-task-priority-{$idEditView}" class="dropdown-menu scroll-user-menu" role="menu">
                        <li class="list-btn-header" title="Prioridad">
                            <i class="fa fa-info-circle" aria-hidden="true" style="padding-right: 0"></i>
                            <small>Prioridad</small>
                        </li>
                        <li class="divider"></li>
                        {foreach $AVAILABLE_TASK_PRIORITIES as $priority}
                            {if $priority eq 'Medio'}{continue}{/if}
                            <li class="{if $ACTIVITYDATA.taskpriority eq $priority}active{/if}">
                                <a href="#" title="{$priority}" rel="{$priority}">
                                    {if $priority eq 'Alto'}Alta{else}Básica{/if}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
            {if $CATEGORIES neq NULL}
                <div class="input-group">
                    <div class="input-group-btn dropup">
                        <button id="btn-group-task-categories-{$idEditView}" type="button"
                            class="btn {if !empty($ACTIVITYDATA.categoryid)}btn-primary{else}btn-default{/if} dropdown-toggle" style="font-size: 13px!important;margin-left: 0.1em; padding-left:1em;padding-right:1em;height:2.3em !important;"
                            title="Ubicación de la tareas" data-toggle="dropdown" data-default-label="Ubicación">
                            <i style="color: #cccccc" class="fa fa-tasks" aria-hidden="true"></i>&nbsp;
                            <span class="btn-label">
                                {if !empty($ACTIVITYDATA.categoryid)}
                                    {if $ACTIVITYDATA.categoryid eq '0'}Crear grupo{else}
                                        {foreach $CATEGORIES as $id => $name}
                                            {if $ACTIVITYDATA.categoryid eq $id}{$name}{/if}
                                        {/foreach}
                                    {/if}
                                {else}Ubicación{/if}
                            </span>&nbsp;
                            <span class="caret"></span>
                        </button>
                        <ul id="detailview-task-categories-{$idEditView}" class="dropdown-menu" role="menu">
                            <li class="list-btn-header" title="Ubicación de la tarea">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                <small>Ubicación</small>
                            </li>
                            <li class="divider"></li>
                            <li {if $ACTIVITYDATA.categoryid eq '0'}active{/if}>
                                <a href="#" title="Crear un nuevo grupo" rel="0">
                                    Crear grupo
                                </a>
                            </li>
                            <li class="divider"></li>
                            {foreach $CATEGORIES as $id => $name}
                                <li class="{if $ACTIVITYDATA.categoryid eq $id}active{/if}">
                                    <a href="#" title="{$name}" rel="{$id}">
                                        {$name}
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                    <input id="categoryname-{$idEditView}" name="category_name" type="text" class="form-control hide">
                </div>
            {/if}
        </div>
    </form>
    <div class="col-lg-12 col-md-12 col-sm-12" style="float: left; margin-top: 6px;">
        <small id="help-user-{$idEditView}" style="margin-top:0; color: red"></small>
        <small id="help-priority-{$idEditView}" style="margin-top:0; color: red"></small>
        <small id="help-status-{$idEditView}" style="margin-top:0; color: red"></small>
        <small id="help-group-{$idEditView}" style="margin-top:0; color: red"></small>
    </div>
</div>

<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/{$THEME}/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript">
    (function(jQuery) {
        var id = '{$idEditView}';

        jQuery('#date_start-' + id).datepicker({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
        jQuery('#due_date-' + id).datepicker({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
        if (!jQuery('#due_date-' + id).val()) {
            var startDate = jQuery('#date_start-' + id).datepicker('getDate');
            if (startDate) {
                var dueDate = new Date(startDate);
                dueDate.setDate(dueDate.getDate() + 1);
                jQuery('#due_date-' + id).datepicker('setDate', dueDate);
            }
        }
        jQuery('#start_time-' + id).timepicker({
            minuteStep: 5, showSeconds: true, showMeridian: false, disableFocus: false, showWidget: true
        });

        jQuery('#reported_task_module-' + id).on('change', function () {
            var module = jQuery(this),
                record = jQuery('#module_related_record-' + id),
                forModule = jQuery('#formodule-' + id),
                dummy, label;
            if (module.val() !== '') {
                dummy = module.val().split('@');
                module.attr('data-referenced-module', dummy[0]);
                label = module.find('option:selected').text();
                module.attr('data-title', label);
                forModule.val(dummy[0]);
                record.val('');
                jQuery('#selected_record_display-' + id).text('').attr('title', '');
                if (window.RelatedModuleModalUtils && typeof window.RelatedModuleModalUtils.openModal === 'function') {
                    RelatedModuleModalUtils.openModal(this);
                }
            } else {
                module.attr('data-referenced-module', '');
                module.attr('data-title', '');
                record.val('');
                forModule.val('');
                jQuery('#selected_record_display-' + id).text('').attr('title', '');
            }
        });

        jQuery(document).on('relatedModuleRecordSelected.activityEditView', function (event, title, displayFieldId, dataFieldId, recordValue) {
            if (displayFieldId === 'selected_record_display-' + id && recordValue) {
                jQuery('#selected_record_display-' + id).text(recordValue).attr('title', recordValue);
            }
        });

        function sortSelectedCategoryFirst() {
            var list = jQuery('#detailview-task-categories-' + id);
            var selected = list.find('li.active').first();
            if (selected.length) {
                var header = list.find('li.list-btn-header').first();
                var divider = header.next('li.divider');
                if (header.length && divider.length) {
                    selected.insertAfter(divider);
                }
            }
        }
        sortSelectedCategoryFirst();
        jQuery('#detailview-task-categories-' + id).closest('.input-group-btn').on('show.bs.dropdown', function() {
            sortSelectedCategoryFirst();
        });

        /* Dropdown handlers: marcar activo y actualizar hidden fields */
        jQuery('#detailview-task-importance-' + id + ' li a').on('click', function(e) {
            e.preventDefault();
            jQuery('#detailview-task-importance-' + id + ' li').removeClass('active');
            jQuery(this).closest('li').addClass('active');
            jQuery('#taskImport-' + id).val(jQuery(this).attr('rel'));
            var btn = jQuery('#btn-group-importance-' + id);
            btn.removeClass('btn-default').addClass('btn-primary');
            btn.find('.btn-label').text(jQuery(this).text().trim());
        });

        jQuery('#detailview-task-priority-' + id + ' li a').on('click', function(e) {
            e.preventDefault();
            jQuery('#detailview-task-priority-' + id + ' li').removeClass('active');
            jQuery(this).closest('li').addClass('active');
            jQuery('#taskpriority-' + id).val(jQuery(this).attr('rel'));
            var btn = jQuery('#btn-group-priority-' + id);
            btn.removeClass('btn-default').addClass('btn-primary');
            btn.find('.btn-label').text(jQuery(this).text().trim());
        });

        jQuery('#detailview-task-categories-' + id + ' li a').on('click', function(e) {
            e.preventDefault();
            jQuery('#detailview-task-categories-' + id + ' li').removeClass('active');
            jQuery(this).closest('li').addClass('active');
            var rel = jQuery(this).attr('rel');
            jQuery('#categoryid-' + id).val(rel);
            var btn = jQuery('#btn-group-task-categories-' + id);
            btn.removeClass('btn-default').addClass('btn-primary');
            btn.find('.btn-label').text(jQuery(this).text().trim());
            if (rel === '0') {
                jQuery('#categoryname-' + id).removeClass('hide').focus();
                jQuery('#categoryname-' + id).val('');
            } else {
                jQuery('#categoryname-' + id).addClass('hide');
            }
        });

    }(jQuery));
</script>


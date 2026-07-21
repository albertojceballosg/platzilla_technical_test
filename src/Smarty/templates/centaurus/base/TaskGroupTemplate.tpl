<script type="text/html" id="task-group-template-{$idTaskDetailView}">
    <div id="tasks-group-__CATEGORYID__" class="card rounded car-task" style="margin-bottom: 2.5px!important;">
        <a href="#" title="Ocultar tareas" onclick="DetailViewTabUtils.taskGroupStatus(event, this, '__CATEGORYID__')"
            rel="__CATEGORYID__" data-status="visible">
            <h3 class="task-group-header">
                <i class="fa fa-tasks" aria-hidden="true"></i>&nbsp;__CATEGORYNAME__
            </h3>
        </a>
        <ol class="list-group list-tasks" id="list-tasks-group-__CATEGORYID__">
            <li id="task-view-__ID__" class="list-group-item">
                <div class="flex-container nowrap space-between items-align-star">
                    <div class="flex-container wrap flex-start items-align-baseline" style="max-width: 60%">
                        <h4 id="subject-__ID__" style="width: 100%; margin: 0;font-weight: bold">__SUBJECT__
                            {*<span id="priorityname-__ID__" class="badge btn-default" style="padding: 0.35em">&nbsp;__PRIORYTT__&nbsp;</span>
                            <span id="statusname-__ID__" class="badge  btn-default" style="padding: 0.35em">&nbsp;__STATUS__&nbsp;</span> *}
                        </h4>
                        <div style="width: 100%; display: inline; vertical-align: center">
                            <span id="priorityname__ID__"
                                class="badge btn-default">&nbsp;<small>Prioridad:&nbsp;__PRIORYTT__
                                </small>&nbsp;</span>
                            <span id="statusname__ID__" class="badge btn-default">&nbsp;<small>Estado:&nbsp;__STATUS__
                                </small>&nbsp;</span>
                            <span id="importance__ID__"
                                class="badge btn-default">&nbsp;<small>Importancia:&nbsp;__IMPORTANCE__
                                </small>&nbsp;</span>
                        </div>
                        <div id="date_start-__ID__" class="item-date" style="margin-right: 0.5em">
                            <i class="fa fa-calendar"></i>&nbsp;__START_DATE__
                        </div>
                        <small style="margin-right: 0.5em">-</small>
                        <div id="due_date-__ID__" class="item-date" style="margin-right: 0.5em">__DUE_DATE__</div>
                        <div id="time_estimated-__ID__" class="item-date" style="margin-right: 0.5em">
                            __ESTIMATED_TIME__&nbsp;Horas</div>
                        <div id="cost_estimated-__ID__" class="item-date" style="margin-right: 0.5em"><i
                                class="fa fa-dollar"></i>&nbsp;__ESTIMATED_COST__</div>
                        <div id="username-__ID__" class="item-date">&nbsp;<i class="fa fa-user"
                                aria-hidden="true"></i>&nbsp;__USER__</div>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="edit btn btn-primary"
                            onclick="DetailViewTabUtils.editTask ('__ID__')">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button type="button" class="delete btn btn-danger"
                            onclick="DetailViewTabUtils.deleteTaskRow ('__ID__')">
                            <i class="fa fa-trash-o"></i>
                        </button>
                        <button type="button" class="edit btn btn-success"
                            onclick="DetailViewTabUtils.setCompleted ('__ID__')">
                            <i class="fa fa-check-square" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <div id="description-__ID__" class="" style="margin-left: 0;margin-top: 10px;">__DESCRIPTION__</div>
            </li>
            <li id="task-form-__ID__" class="list-group-item list-form">
                <form id="main_input_box-__ID__">
                    <input type="hidden" name="relatedcrmids[]" value="{$ID}">
                    <input type="hidden" name="record" value="__ID__">
                    <input type="hidden" name="module" value="Calendar">
                    <input type="hidden" name="flmodule" value="{$FLMODULE}">
                    <input type="hidden" name="action" value="Save">
                    <input type="hidden" name="mode" value="edit">
                    <input type="hidden" name="function" value="TASK_FROM_MODULE">
                    <input type="hidden" name="assigned_user_id" value="{$CURRENT_USER_ID}">
                    <input type="hidden" id="user-name-__ID__" value="{$CURRENT_USER_NAME}">
                    <input type="hidden" name="planned_task" value="PLANNED_AND_RECORDED">
                    <div class="flex-container nowrap space-between items-align-star">
                        <div class="" style="margin-right: 0.5em; min-width: 85%">
                            <h4 style="width: 100%;font-weight: bold">
                                <input class="form-control" name="subject" type="text" value="__SUBJECT__">
                            </h4>
                        </div>
                        <div class="flex-container nowrap space-between items-align-star" style="margin-top: 0.5em">
                            <span id="help-__ID__" class="help-block" style="color: red"></span>

                            <div class="btn-group">
                                <button type="button" title="Actualizar tarea" class="edit btn btn-primary" data-id-main="{$idTaskDetailView}"  onclick="DetailViewTabUtils.createTask (this, '__ID__', 'UPDATE')">
                                    <i class="fa fa-paper-plane-o" aria-hidden="true"></i>
                                </button>
                                <button type="button" title="Cancelar edición" class="edit btn btn-danger"
                                    onclick="DetailViewTabUtils.cancelEditTask()">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex-container nowrap space-between items-align-star">
                        <div class="flex-container wrap flex-start space-between items-align-baseline">
                            <div class="item-date" style="margin-right: 0.5em">
                                {* Activity Type *}
                                <div class="input-group">
                                    <span class="input-group-addon border"><i class="fa fa-bars"></i></span>
                                    <select id="activitytype-__ID__" name="activitytype" class="form-control">
                                        {foreach $AVAILABLE_ACTIVITY_TYPES as $activityType => $activityLabel}
                                            <option value="{$activityType}">{$activityLabel}
                                            </option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="item-date" style="margin-right: 0.5em">
                                {* Start day *}
                                <div class="input-group">
                                    <span class="input-group-addon border"><i class="fa fa-calendar"></i></span>
                                    <input class="form-control datepickerDate-{$idTaskDetailView}" name="date_start"
                                        placeholder="Fecha de inicio" type="text" 
                                        value="__START_DATE__">
                                </div>
                            </div>
                            {* due day *}
                            <div class="item-date" style="margin-right: 0.5em">
                                <div class="input-group">
                                    <span class="input-group-addon border"><i class="fa fa-calendar"></i></span>
                                    <input class="form-control datepickerDate-{$idTaskDetailView}" name="due_date"
                                        placeholder="Fecha de inicio" type="text" 
                                        value="__DUE_DATE__">
                                </div>
                            </div>



                            
                        </div> {* 1*}
                    </div>
                    <div class="flex-container nowrap space-between items-align-star">
                        <div class="flex-container nowrap items-align-star" style="margin-top: 0.5em">
                            {* Time end *}
                            <div class="" style="margin-right: 0.5em">
                                {* estimated time*}
                                <div class="input-group">
                                    <span class="input-group-addon">Horas</span>
                                    <input type="text" class="form-control" name="estimated_time"
                                        onkeydown="DetailViewTabUtils.normalizeEstimatedTime (this, event, '');"
                                        placeholder="Duración estimada" value="__ESTIMATED_TIME__"
                                        id="estimated_time-__ID__">
                                </div>
                                {* estimated time*}
                            </div>
                            <div class="" style="margin-right: 0.5em">
                                {* estimated cost*}
                                <div class="input-group">
                                    <span class="input-group-addon">Costo estimado</span>
                                    <input type="text" class="form-control" name="estimated_cost"
                                        data-number-format="decimal" data-default-value="0" data-decimals="2"
                                        placeholder="0.00" value="__ESTIMATED_COST__" id="estimated_cost-__ID__">
                                </div>
                                {* estimated cost*}
                            </div>
                            <div class="{*item-data*}" style="margin-right: 0.5em">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="show_in_matrix" {if $task['show_in_matrix'] eq 'YES'}checked{/if} value="YES">
                                        ¿Mostrar en Matriz diaria?
                                    </label>
                                </div>
                            </div>



                            
                        </div> {* 2*}
                    </div>
                    <div class="flex-container nowrap space-between items-align-star">
                        <div class="flex-container nowrap space-between items-align-star" style="margin-top: 0.5em">
                            {* User assigned*}
                            {if $AVAILABLE_USERS neq NULL}
                                <div class="btn-group">
                                    <button id="btn-group-user-__ID__" type="button" class="btn btn-default dropdown-toggle"
                                        title="asignar tarea" style="font-size: 15px!important" data-toggle="dropdown">
                                        <i class="fa fa-user" aria-hidden="true"></i>&nbsp;
                                        <span class="caret"></span>
                                    </button>
                                    <ul id="detailview-task-user-__ID__" class="dropdown-menu scroll-user-menu" role="menu">
                                        <li class="list-btn-header" title="Usuarios">
                                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                                            <small>Usuaros</small>
                                        </li>
                                        <li class="divider"></li>
                                        {if $AVAILABLE_USERS|count gt 0}
                                            {foreach $AVAILABLE_USERS as $id => $user}
                                                <li>
                                                    <a href="#" title="{$user['name']}" rel="{{$id}}" 
                                                        onclick="DetailViewTabUtils.selectedUser (event, this, '__ID__')">
                                                        <img class="img-circle" style="width: 36%; height: 36%" data-src="{$user['avatar']}"  alt="{$user['name']}"
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
                            {* Importance *}
                            {if $AVAILABLE_IMPORTANCE neq NULL}
                                <div class="btn-group">
                                    <button id="btn-group-importance-__ID__" type="button"
                                        class="btn btn-primary dropdown-toggle dropdown-toggle"
                                        title="Importancia de la tarea" style="font-size: 15px!important;margin-left: 0.1em"
                                        data-toggle="dropdown">
                                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                        <span class="caret"></span>
                                    </button>
                                    <ul id="detailview-task-importance-__ID__" class="dropdown-menu scroll-user-menu"
                                        role="menu">
                                        <li class="list-btn-header" title="Importancia">
                                            <i class="fa fa-info-circle" aria-hidden="true" style="padding-right: 0"></i>
                                            <small>Importancia</small>
                                        </li>
                                        <li class="divider"></li>
                                        {foreach $AVAILABLE_IMPORTANCE as $key => $importance}
                                            <li>
                                                <a href="#" title="{$importance}" rel="{$key}" 
                                                    onclick="DetailViewTabUtils.selectedImportance (event, this, '__ID__')">
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
                                    <button id="btn-group-priority-__ID__" type="button"
                                        class="btn btn-primary dropdown-toggle dropdown-toggle" title="Prioridad"
                                        style="font-size: 15px!important;margin-left: 0.2em" data-toggle="dropdown">
                                        <i class="fa fa-sort" aria-hidden="true"></i>
                                        <span class="caret"></span>
                                    </button>
                                    <ul id="detailview-task-priority-__ID__" class="dropdown-menu scroll-user-menu"
                                        role="menu">
                                        <li class="list-btn-header" title="Prioridad">
                                            <i class="fa fa-info-circle" aria-hidden="true" style="padding-right: 0"></i>
                                            <small>Prioridad</small>
                                        </li>
                                        <li class="divider"></li>
                                        {foreach $AVAILABLE_TASK_PRIORITIES as $priority}
                                            {if $priority eq 'Medio'}{continue}{/if}
                                            <li>
                                                <a href="#" title="{$priority}" rel="{$priority}" 
                                                    onclick="DetailViewTabUtils.selectedPriority (event, this, '__ID__')">
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
                                    <button id="btn-group-task-status-__ID__" type="button"
                                        class="btn btn-primary dropdown-toggle" title="Estado de la tarea"
                                        style="font-size: 15px!important;margin-left: 0.2em" data-toggle="dropdown">
                                        <i class="fa fa-exchange" aria-hidden="true"></i>
                                        <span class="caret"></span>
                                    </button>
                                    <ul id="detailview-task-status-__ID__" class="dropdown-menu scroll-user-menu"
                                        role="menu">
                                        <li class="list-btn-header" title="Estado de la tarea">
                                            <i class="fa fa-info-circle" aria-hidden="true" style="padding-right: 0"></i>
                                            <small>Estado</small>
                                        </li>
                                        <li class="divider"></li>
                                        {foreach $AVAILABLE_EVENT_STATUSES as $eventStatus => $eventStatusLabel}
                                            <li>
                                                <a href="#" title="{$eventStatusLabel}"  rel="{$eventStatus}" 
                                                    onclick="DetailViewTabUtils.selectedStatus (event, this, '__ID__')">
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
                                    <button id="btn-group-task-categories-__ID__" type="button"
                                        class="btn btn-primary dropdown-toggle"
                                        style="height: 2.4em!important;margin-left: 0.1em" title="Ubicación de la tareas"
                                        data-toggle="dropdown">
                                        <i class="fa fa-tasks" aria-hidden="true"></i>&nbsp;
                                        <span class="caret"></span>
                                    </button>
                                    <ul id="detailview-task-categories-__ID__" class="dropdown-menu" role="menu">
                                        <li class="list-btn-header" title="Ubicación de la tarea">
                                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                                            <small>Ubicación</small>
                                        </li>
                                        <li class="divider"></li>
                                        {foreach $CATEGORIES as $id => $name}
                                            <li>
                                                <a href="#" title="{$name}" rel="{$id}" 
                                                    onclick="DetailViewTabUtils.setCategory (event, this, '__ID__')">
                                                    {$name}
                                                </a>
                                            </li>
                                        {/foreach}
                                    </ul>
                                </div>
                            {/if}
                            {* task Group*}
                        </div>

                    </div>
                    <div class="" style="margin-left: 0;margin-top: 10px;">
                        <textarea name="description" class="form-control" style="width: 100%">__DESCRIPTION__</textarea>
                    </div>
                </form>
            </li>
        </ol>
    </div>
</script>
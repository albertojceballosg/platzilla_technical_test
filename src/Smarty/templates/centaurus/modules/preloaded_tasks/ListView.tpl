{strip}
    {math equation= rand() assign= "idPrecreatedTask"}
    <div id="email-box" class="clearfix" style="padding-bottom: 20px;">
    <table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
        <tbody>
        <tr>
            <td rowspan="2" valign="top">
                <div class="infographic-box" style="width: 30px; padding: 0;"><i
                            class="fa fa-cogs yellow-bg"></i>
                </div>
            </td>
            <td class="heading2" valign="bottom">
                <ol class="breadcrumb">
                    <li>
                        <a href="index.php?module=Settings&action=index&parenttab=Settings">CONFIGURACIÓN</a>
                    </li>
                    <li  title="{{$MOD['LBL_PRECREATED_TASK_DESCRIPTION']}}"  class="active" style="text-transform: uppercase">{$MOD['LBL_PRECREATED_TASK']}</li>
                </ol>
            </td>
        </tr>
        <tr>
            <td class="small" valign="top">{$MOD['LBL_COURSE_DESCRIPTION']}</td>
        </tr>
        </tbody>
    </table>
    {if (!empty ($MESSAGE))}
        <div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
            <strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
        </div>
    {/if}
    <div class="main-box clearfix">
        <ul class="nav nav-tabs">
            <li {if ($SELECTED_TAB eq 'TASK')} class="active"{/if}>
                <a data-toggle="tab" href="#precreated-task-tab">Tareas precreadas</a>
            </li>
            <li {if ($SELECTED_TAB eq 'AREA_TASK')} class="active"{/if}>
                <a data-toggle="tab" href="#area-task-tab">Áreas</a>
            </li>
        </ul>
        <div class="main-box-body clearfix">
            <div class="tab-content">
                {* Courses *}
                <div id="precreated-task-tab" class="tab-pane fade in{if ($SELECTED_TAB eq 'TASK')} active{/if}">
                    <header class="main-box-header clearfix text-right">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="pull-left">
                                    <div id="search-task-{$idPrecreatedTask}" class="row">
                                        <div class="col-xs-6">
                                    <select class="form-control" name="forarea" id="forarea" title="Módulos"
                                            onchange="PreCreatedTasksUtils.filterModules(this, '{$idPrecreatedTask}')">
                                        <option value="" selected>Todas modulos</option>
                                        {foreach $AVAILABLE_MODULES as $module}
                                            <option value="{$module->getName()}">{$module->getLabel()}</option>
                                        {/foreach}
                                    </select>
                                        </div>
                                        <div class="col-xs-6">
                                    <select class="form-control" name="forserie" id="forserie" title="Areas"
                                            onchange="PreCreatedTasksUtils.filterArea(this, '{$idPrecreatedTask}')">
                                        <option value="" selected>Todos las areas</option>
                                        {foreach $AREA_TASK as $area}
                                            <option value="{$area->getCodeArea()}"> {$area->getAreaName()}</option>
                                        {/foreach}
                                    </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="pull-right">
                                    <a href="index.php?module=preloaded_tasks&action=EditView" target="_self"
                                       class="btn btn-primary"><i
                                                class="fa fa-plus-circle"></i> Crear tarea</a>
                                </div>
                            </div>
                        </div>

                    </header>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-from">Tarea</th>
                                <th class="col-to" style="width: 20%">Modulo</th>
                                <th class="col-to" style="width: 20%;">Área</th>
                                <th class="col-to" style="width: 6%">Estado</th>
                                <th class="col-actions" style="width: 14%;text-align: center">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="task-panel-table-{$idPrecreatedTask}">
                            {if ($TASK_LIST neq NULL) }
                                {foreach $TASK_LIST as $task}
                                    {assign var='taskId' value=$task->getId ()}
                                    {assign var='taskAreaName' value=$task->getAreaName ()}
                                    {assign var='taskTabName' value=$task->getTabName ()}
                                    {assign var='taskModuleName' value=$task->getModuleName ()}
                                    {assign var='taskCodeArea' value=$task->getCodeArea ()}
                                    {assign var='taskTaskDescription' value=$task->getTaskDescription ()}
                                    {assign var='taskTaskName' value=$task->getTaskName ()}
                                    {assign var='taskStatus' value=$task->getStatus ()}
                                    <tr id="task-row-{$taskTabName}-{$taskCodeArea}-{$taskId}-{$idPrecreatedTask}">
                                        <td class="col-from">{$taskTaskName}</td>
                                        <td class="col-to">{$taskModuleName}</td>
                                        <td class="col-to">{$taskAreaName}</td>
                                        <td id="task-status-{$taskId}" class="col-to">{$MOD[$taskStatus]}</td>
                                        <td class="col-actions" style="width: 18%;text-align: center!important;">
                                            <div class="btn-group">
                                                <a href="index.php?module=preloaded_tasks&action=EditView&record={$taskId}"
                                                   target="_self" class="btn btn-primary btn-sm"
                                                   title="Editar tarea"><i class="fa fa-pencil-square-o" aria-hidden="true">
                                                    </i></a>
                                                <button type="button" class="btn btn-sm btn-warning"
                                                        title="{if $taskStatus eq 'ENABLED'}Desactivar{else}Activar{/if} tarea"
                                                        data-record="{$taskId}"
                                                        data-status="{$taskStatus}"
                                                        onclick="PreCreatedTasksUtils.changeStatusTask (this, '{$idPrecreatedTask}')">
                                                    {if $taskStatus eq 'ENABLED'}
                                                        <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                                    {else}
                                                        <i class="fa fa-square-o" aria-hidden="true"></i>
                                                    {/if}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-sm btn-danger"
                                                        data-record="{$taskId}"
                                                        onclick="PreCreatedTasksUtils.deleteTask(this, '{$idPrecreatedTask}')"
                                                        title="Eliminar"><i class="fa fa-trash-o"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="7" class="text-center">No hay Tareas creadas</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                {* Courses Categories *}
                <div id="area-task-tab"
                     class="tab-pane fade in{if ($SELECTED_TAB eq 'AREA_TASK')} active{/if}">&nbsp;
                    <header class="main-box-header clearfix text-right">
                        <div class="pull-right">
                            <a href="index.php?module=preloaded_tasks&action=EditViewArea" target="_self"
                               class="btn btn-primary"><i
                                        class="fa fa-plus-circle"></i> Crear área</a>
                        </div>
                    </header>
                    {* Table Categories *}
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th class="col-from"  style="width: 15%">Código</th>
                                <th class="col-to">Aéra</th>
                                <th class="col-to" style="width: 10%">Estado</th>
                                <th class="col-actions" style="width: 10%;text-align: center">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="how-use-table">
                            {if ($AREA_TASK neq NULL) }
                                {foreach $AREA_TASK as $area}
                                    {assign var='areaId' value=$area->getId ()}
                                    {assign var='areaName' value=$area->getAreaName ()}
                                    {assign var='areaCodeArea' value=$area->getCodeArea ()}
                                    {assign var='areaStatus' value=$area->getStatus ()}
                                    <tr id="row-area-{$areaId}">
                                        <td class="col-title">
                                            {$areaCodeArea}
                                        </td>
                                        <td class="col-title">
                                            {$areaName}
                                        </td>
                                        <td id="area-status-{$areaId}" class="col-to">{$MOD[$areaStatus]}</td>
                                        <td class="col-actions" style="width: 10%;text-align: center">
                                            <div class="btn-group">
                                                <a href="index.php?module=preloaded_tasks&action=EditViewArea&record={$areaId}"
                                                   target="_self" class="btn btn-primary btn-sm"
                                                   title="Editar tarea"><i class="fa fa-pencil-square-o" aria-hidden="true">
                                                    </i></a>
                                                <button type="button" class="btn btn-sm btn-warning"
                                                        data-record="{$areaId}"
                                                        data-status="{$areaStatus}"
                                                        onclick="PreCreatedTasksUtils.changeStatusArea (this, '{$idPrecreatedTask}')"
                                                        title="{if $areaStatus eq 'ENABLED'}Desactivar{else}Activar{/if} área">
                                                    {if $areaStatus eq 'ENABLED'}
                                                        <i class="fa fa-check-square-o" aria-hidden="true"></i>
                                                    {else}
                                                        <i class="fa fa-square-o" aria-hidden="true"></i>
                                                    {/if}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr class="lvtColData">
                                    <td colspan="3" class="text-center">No hay Áreas disponibles</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                {* Courses Series *}
            </div>
        </div>
    </div>
    <script type="text/javascript" src="modules/preloaded_tasks/precreated-task-utils.js"></script>
{/strip}
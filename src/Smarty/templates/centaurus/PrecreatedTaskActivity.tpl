<div class="modal fade" id="precreated-task-{$idTaskDetailView}" tabindex="-1" role="dialog"
    aria-labelledby="precreated-task-Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="precreated-task-Label">Modelo de
                    tareas&nbsp;{if $FLMODULE neq 'orden_de_trabajo'}/ acción{/if}<span id="field-name"></span></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card rounded car-task">
                            <header class="main-box-header clearfix text-right">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="pull-left">
                                            <div id="search-task-{$idTaskDetailView}" class="row">
                                                <div class="col-xs-6">
                                                    <select class="form-control border" name="formodule"
                                                        id="formodule-{$idTaskDetailView}" title="Módulos"
                                                        onchange="PreCreatedTasksUtils.filterModules(this, '{$idTaskDetailView}')">
                                                        <option value="" selected>Todos los modulos</option>
                                                        {foreach $AVAILABLE_MODULES as $module}
                                                            <option value="{$module.name}">{$module.tablabel}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                                <div class="col-xs-6">
                                                    <select class="form-control border" name="forserie" id="forserie"
                                                        title="Areas"
                                                        onchange="PreCreatedTasksUtils.filterArea(this, '{$idTaskDetailView}')">
                                                        <option value="" selected>Todos las areas</option>
                                                        {foreach $AREA_TASK as $area}
                                                            <option value="{$area->getCodeArea()}"> {$area->getAreaName()}
                                                            </option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="pull-right">&nbsp;</div>
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
                                        </tr>
                                    </thead>
                                    <tbody id="task-panel-table-{$idTaskDetailView}">
                                        {if ($TASK_LIST neq NULL)}
                                            {foreach $TASK_LIST as $taskList}
                                                {assign var='taskId' value=$taskList->getId ()}
                                                {assign var='taskAreaName' value=$taskList->getAreaName()}
                                                {assign var='taskTabName' value=$taskList->getTabName()}
                                                {assign var='taskModuleName' value=$taskList->getModuleName()}
                                                {assign var='taskCodeArea' value=$taskList->getCodeArea()}
                                                {assign var='taskTaskDescription' value=$taskList->getTaskDescription()}
                                                {assign var='taskTaskName' value=$taskList->getTaskName()}
                                                {assign var='taskStatus' value=$taskList->getStatus()}
                                                <tr id="task-row-{$taskTabName}-{$taskCodeArea}-{$taskId}-{$idTaskDetailView}">
                                                    <td class="col-from">
                                                        <a href="#" title="Seleccionar esta taera"
                                                            onclick="PreCreatedTasksUtils.selectPreTask (event, '{$taskId}', '{$idTaskDetailView}')">
                                                            {$taskTaskName}
                                                        </a>
                                                    </td>
                                                    <input type="hidden" id="task-name-{$taskId}" value="{$taskTaskName}">
                                                    <input type="hidden" id="task-des-{$taskId}" value="{$taskTaskDescription}">
                                                    <td class="col-to">{$taskModuleName}</td>
                                                    <td class="col-to">{$taskAreaName}</td>
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
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="row-{$idTaskDetailView}" value="0">
            </div>
        </div>
    </div>
</div>
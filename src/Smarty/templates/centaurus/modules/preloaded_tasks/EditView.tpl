{strip}
    {if ($TASK neq NULL)}
        {assign var='taskId' value=$TASK->getId ()}
        {assign var='taskAreaName' value=$TASK->getAreaName ()}
        {assign var='taskTabName' value=$TASK->getTabName ()}
        {assign var='taskModuleName' value=$TASK->getModuleName ()}
        {assign var='taskCodeArea' value=$TASK->getCodeArea ()}
        {assign var='taskTaskDescription' value=$TASK->getTaskDescription ()}
        {assign var='taskTaskName' value=$TASK->getTaskName ()}
        {assign var='status' value=$TASK->getStatus ()}
    {else}
        {assign var='taskId' value=null}
        {assign var='taskAreaName' value=null}
        {assign var='taskTabName' value=null}
        {assign var='taskModuleName' value=null}
        {assign var='taskCodeArea' value=null}
        {assign var='taskTaskDescription' value=null}
        {assign var='taskTaskName' value=null}
        {assign var='taskStatus' value=null}
    {/if}
    <style>
        .row-course {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px
        }

        .justify-content-center {
            -webkit-box-pack: center !important;
            -ms-flex-pack: center !important;
            justify-content: center !important
        }

        .no-gutters > .col,
        .no-gutters > [class*=col-] {
            padding-right: 1px;
            padding-left: 1px;
        }
    </style>
    <form class="form-horizontal" name="company-sector-form" role="form" method="post" action="index.php">
        <input type="hidden" name="module" value="preloaded_tasks"/>
        <input type="hidden" name="action" value="SaveTask"/>
        <input type="hidden" name="tab" value="TASK"/>
        <input type="hidden" name="record" value="{$taskId}"/>
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=preloaded_tasks&action=index&parenttab=">{$MOD['LBL_PRECREATED_TASK']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    <a href="index.php?module=preloaded_tasks&action=ListView&parenttab=preloaded_tasks"
                       class="btn btn-warning"
                       style="margin-left: 5px;">Cancelar</a>
                </div>
            </div>
        </div>
        {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
            <div class="row">
                <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                    <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
                </div>
            </div>
        {/if}
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Tareas predefinidas: Información general</h2>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            {* module *}
                            <div class="form-group">
                                <label for="sector_name" class="col-md-3 control-label">Módulo:</label>
                                <div id="ts-div-tabname" class="col-md-7">
                                    <select class="form-control" name="tabname" id="tabname" title="Módulos">
                                        <option value="" selected>Seleccionar...</option>
                                        {foreach $AVAILABLE_MODULES as $module}
                                            <option value="{$module->getName()}" {if $module->getName() eq $taskTabName}selected{/if}>{$module->getLabel()}</option>
                                        {/foreach}
                                    </select>
                                    <span id="ts-sp-tabname" class="help-block"></span>
                                </div>
                            </div>
                            {* area *}
                            <div class="form-group">
                                <label for="sector_name" class="col-md-3 control-label">Área:</label>
                                <div id="ts-div-area" class="col-md-7">
                                    <select class="form-control" name="area" id="area" title="Areas">
                                        <option value="" selected>Seleccionar...</option>
                                        {foreach $AREA_TASK as $area}
                                            <option value="{$area->getCodeArea()}" {if $area->getCodeArea() eq $taskCodeArea}selected{/if}> {$area->getAreaName()}</option>
                                        {/foreach}
                                    </select>
                                    <span id="ts-area" class="help-block"></span>
                                </div>
                            </div>
                            {* Sector Name *}
                            <div class="form-group">
                                <label for="sector_name" class="col-md-3 control-label">Tarea:</label>
                                <div id="ts-div-task_title" class="col-md-7">
                                    <input type="text" class="form-control" id="task_title" name="task_title"
                                           value="{$taskTaskName}"
                                           title="Título de la tarea"
                                           placeholder="Definir Cateroría">
                                    <span id="ts-task_title" class="help-block"></span>
                                </div>
                            </div>
                            {* Sector description *}
                            <div class="form-group">
                                <label for="sector_name" class="col-md-3 control-label">Descripción:</label>
                                <div id="ts-div-task_descripcion" class="col-md-7">
                                    <textarea id="task_descripcion" name="task_descripcion"
                                              placeholder="Breve descripción de la tarea"
                                              class="form-control test-description">{$taskTaskDescription}</textarea>
                                    <span id="ts-task_descripcion" class="help-block"></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="sector_description" class="col-md-3 control-label">Estado:</label>
                                <div id="ts-div-description" class="col-md-7">
                                    <select class="form-control" name="status_view" id="status_view">
                                        <option value="ENABLED" {if $status eq 'ENABLED'} selected=""{/if}> Activo</option>
                                        <option value="DISABLED" {if $status eq 'DISABLED'} selected=""{/if}> No activo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
{/strip}
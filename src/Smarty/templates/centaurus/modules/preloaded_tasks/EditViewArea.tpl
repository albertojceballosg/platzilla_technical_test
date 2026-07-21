{strip}
    {if ($AREA_TASK neq NULL)}
        {assign var='areaId' value=$AREA_TASK->getId ()}
        {assign var='areaAreaName' value=$AREA_TASK->getAreaName ()}
        {assign var='areaCodeArea' value=$AREA_TASK->getCodeArea ()}
        {assign var='status' value=$AREA_TASK->getStatus ()}
    {else}
        {assign var='areaId' value=null}
        {assign var='areaAreaName' value=null}
        {assign var='areaCodeArea' value=null}
        {assign var='areaStatus' value=null}
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
        <input type="hidden" name="action" value="SaveArea"/>
        <input type="hidden" name="tab" value="AREA_TASK"/>
        <input type="hidden" name="record" value="{$taskId}"/>
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=preloaded_tasks&action=index&tab=AREA_TASK">{$MOD['LBL_PRECREATED_TASK']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    <a href="index.php?module=preloaded_tasks&action=ListView&tab=AREA_TASK"
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
                        <h2 class="pull-left">Tareas predefindas: Áreas</h2>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            {* Sector Name *}
                            <div class="form-group">
                                <label for="sector_name" class="col-md-3 control-label">Código de área:</label>
                                <div id="ts-div-code_area" class="col-md-7">
                                    <input type="text" class="form-control" id="code_area" name="code_area"
                                           oninput="this.value = this.value.toUpperCase()"
                                           value="{$areaCodeArea}"
                                           title="Código del area"
                                           {if $areaId neq NULL}readonly {/if}
                                           placeholder="Definir código del área">
                                    <span id="ts-code_area" class="help-block"></span>
                                </div>
                            </div>
                            {* Sector description *}
                            <div class="form-group">
                                <label for="sector_name" class="col-md-3 control-label">Nombre de área:</label>
                                <div id="ts-div-name_area" class="col-md-7">
                                    <input type="text" class="form-control" id="name_area" name="name_area"
                                           value="{$areaAreaName}"
                                           title="Código del area"
                                           placeholder="Definir nombre del área">
                                    <span id="ts-name_area" class="help-block"></span>
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
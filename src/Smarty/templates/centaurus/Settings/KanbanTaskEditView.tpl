{strip}
    {if ($KANBAN_TASK neq NULL)}
        {assign var='viewId' value=$KANBAN_TASK['kanbantasksid']}
        {assign var='detailView' value=$KANBAN_TASK['detail_view']}
        {assign var='listView' value=$KANBAN_TASK['list_view']}
        {assign var='moduleName' value=$KANBAN_TASK['tabname']}
    {else}
        {assign var='viewId' value=null}
        {assign var='detailView' value=null}
        {assign var='listView' value=null}
        {assign var='moduleName' value=null}
    {/if}
    <link rel="stylesheet" href="include/colorpicker/css/colorpicker.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="themes/{$THEME}/css/libs/datepicker.css"/>
    <style type="text/css">
        {literal}
        .required {
            color: #FF0000;
        }

        label {
            font-size: inherit;
            font-weight: 300;
        }

        .color {
            border: 1px solid #DDDDDD;
            border-radius: 3px;
            cursor: pointer;
            height: 34px;
        }

        .field-container > label > .form-radio {
            margin-bottom: 0;
            margin-top: 0;
            padding-bottom: 0;
            padding-top: 0;
        }

        .col-constraints > .form-control {
            display: inline-block;
            margin-right: 5px;
            width: auto;
        }

        .col-constraints > .glue[disabled="disabled"] {
            display: none;
        }

        .col-actions {
            text-align: center;
            width: 80px;
        }

        .btn.btn-icon {
            font-size: 12px;
            line-height: 1.5;
            padding: 3px 7px;
        }

        .main-box > .main-box-header {
            padding: 20px;
        }

        .action-bar .btn {
            margin-left: 5px;
        }

        {/literal}
    </style>
    <div class="row">
        <div class="col-xs-12">
            <h1>
                <a href="index.php?module=Settings&action=KanbanViewListView&parenttab=Settings&tab=kanban-task">
                    Vistas de Kanban de tareas
                </a>
            </h1>
        </div>
    </div>
    {if (isset ($MESSAGE)) && (!empty ($MESSAGE))}
        <div class="row">
            <div class="alert alert-{if (isset ($IS_ERROR)) && ($IS_ERROR)}danger{else}success{/if}">
                <strong>{if (isset ($IS_ERROR)) && ($IS_ERROR)}Error:{else}Listo!{/if}</strong> {$MESSAGE}
            </div>
        </div>
    {/if}
    <form method="post" action="index.php" id="KambanTaskForm" name="KanbanTask">
        <input type="hidden" name="module" value="Settings"/>
        <input type="hidden" name="action" value="KanbanTaskSaveView"/>
        <input type="hidden" name="record" id="record" value="{$viewId}"/>
        <input type="hidden" name="Ajax" value="true">
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Información general</h2>
                        <div class="action-bar pull-right">
                            <button type="submit" class="btn btn-info">Guardar</button>
                            <a href="index.php?module=Settings&action=KanbanViewListView&parenttab=Settings&tab=kanban-task"
                               class="btn btn-warning">Cancelar</a>
                        </div>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 text-right">
                                        <label for="fromfieldname">Módulo <span class="required">*</span></label>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <select class="form-control" id="tabname" name="tabname" title="Modules">
                                            {if isset($AVAILABLE_MODULES)}
                                                <option value="">Seleccione ...</option>
                                                {foreach $AVAILABLE_MODULES as $row}
                                                    <option value="{$row->getName()}" {if $moduleName eq $row->getName()}selected{/if}>{$row->getLabel()}</option>
                                                {/foreach}
                                            {else}
                                                <option value="">Seleccione ...</option>
                                            {/if}
                                        </select>
                                    </div>
                                </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            &nbsp;
                                        </div>
                                        <div class="form-group col-md-8 field-container">
                                            &nbsp;
                                        </div>
                                    </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 text-right">
                                        <label for="fromfieldname">Vista de Lista</label>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <select class="form-control" id="listview" name="listview"
                                                title="Field">
                                            <option value="1" {if $listView eq 1}selected{/if}>Visible</option>
                                            <option value="0" {if $listView eq '0'}selected{/if}>Oculta</option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-4">
                                            &nbsp;
                                        </div>
                                        <div class="form-group col-md-8 field-container">
                                            &nbsp;
                                        </div>
                                    </div>
                            </div>
                            <div class="col-md-6">
                                {* Aplicación *}
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="label-input">
                                            <label for="appname">Vista de detalle</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <select class="form-control"  id="detailview" name="detailview"
                                                title="Field">
                                            <option value="1" {if $detailView eq 1}selected{/if}>Visible</option>
                                            <option value="0" {if $detailView eq '0'}selected{/if}>Oculta</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
{/strip}

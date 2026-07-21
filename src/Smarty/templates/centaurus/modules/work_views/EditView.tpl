{strip}
    {if ($WORK_VIEW neq NULL)}
        {assign var='viewId' value=$WORK_VIEW->getId()}
        {assign var='view' value=$WORK_VIEW->getView()}
        {assign var='statusView' value=$WORK_VIEW->getViewStatus()}
    {else}
        {assign var='viewId' value=null}
        {assign var='statusView' value=null}
        {assign var='view' value=null}
    {/if}
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
                <a href="index.php?module=work_views&action=index&parenttab=Settings">
                    {$MOD.LBL_work_views|upper}
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
    <form method="post" action="index.php" id="WorkViewsForm" name="WorkViews">
        <input type="hidden" name="module" value="work_views"/>
        <input type="hidden" name="action" value="Save"/>
        <input type="hidden" name="record" id="record" value="{$viewId}"/>
        <input type="hidden" name="Ajax" value="true">
        <div class="row">
            <div class="col-xs-12">
                <div class="main-box">
                    <header class="main-box-header clearfix">
                        <h2 class="pull-left">Información general</h2>
                        <div class="action-bar pull-right">
                            <button type="submit" class="btn btn-info">Guardar</button>
                            <a href="index.php?module=work_views&action=index&parenttab=Settings"
                               class="btn btn-warning">Cancelar</a>
                        </div>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-4 text-right">
                                        <label for="fromfieldname">Vista <span class="required">*</span></label>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <select class="form-control" id="view" name="view" title="Vista de trabajos">
                                            {if isset($AVAILABLE_VIEWS)}
                                                <option value="" {if $viewId neq NULL}disabled=""{/if}>Seleccione ...</option>
                                                {foreach $AVAILABLE_VIEWS as $key => $row}
                                                    <option value="{$key}"{if $view eq $key}selected{/if}
                                                            {if $viewId neq NULL && $viewId neq $key}disabled=""{/if}>{$row}</option>
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
                                        <label for="fromfieldname">Estado de la vista <span class="required">*</span></label>
                                    </div>
                                    <div class="form-group col-md-8 field-container">
                                        <select class="form-control" id="statusview" name="statusview"
                                                title="Field">
                                            {if isset($AVAILABLE_VIEWS_STATUS)}
                                                <option value="">Seleccione ...</option>
                                                {foreach $AVAILABLE_VIEWS_STATUS as $key => $row}
                                                    <option value="{$key}" {if $statusView eq $key}selected{/if}>{$row}</option>
                                                {/foreach}
                                            {else}
                                                <option value="">Seleccione ...</option>
                                            {/if}
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

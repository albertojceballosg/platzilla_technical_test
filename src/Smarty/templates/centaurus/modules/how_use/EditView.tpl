{strip}
    {if ($HOW_USE neq NULL)}
        {assign var='id' value=$HOW_USE->getId()}
        {assign var='howUseName' value=$HOW_USE->getHowUseName()}
        {assign var='description' value=$HOW_USE->getDescription()}
        {assign var='name' value=$HOW_USE->getName()}
        {assign var='tabName' value=$HOW_USE->getTabName()}
        {assign var='status' value=$HOW_USE->getStatus()}
        {assign var='isDefault' value=$HOW_USE->isDefault()}
        {assign var='viewName' value=$HOW_USE->getDefaultView()->getMasterView()->getViewName()}
        {assign var='howUseView' value=$HOW_USE->getHowUseView()}
    {else}
        {assign var='id' value=null}
        {assign var='howUseName' value=null}
        {assign var='description' value=null}
        {assign var='name' value=null}
        {assign var='tabName' value=null}
        {assign var='status' value=null}
        {assign var='isDefault' value=false}
        {assign var='viewName' value=null}
    {/if}
    <style>
        .row-how-use {
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
    <link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="modules/News/News.css"/>
    <form class="form-horizontal" name="howToUse-form" role="form" method="post" action="index.php"
          onsubmit="return HowToUseUtils.validateForm (this);">
        <input type="hidden" name="module" value="how_use"/>
        <input type="hidden" name="action" value="Save"/>
        <input type="hidden" name="record" value="{$id}"/>
        <input type="hidden" name="howUseName" value="{$howUseName}"/>
        <input type="hidden" name="return_action" value="{$RETURN_ACTION}"/>
        <input type="hidden" name="return_module" value="{$RETURN_MODULE}"/>
       {* <input type="hidden" name="Ajax" value="true"/> *}
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pull-left">
                    <a href="index.php?module=how_use&action=ListView&parenttab=Settings">{$MOD['how_use']}</a>
                </h1>
                <div class="action-bar pull-right">
                    <button type="submit" class="btn btn-info">Guardar</button>
                    <a href="index.php?module=how_use&action=ListView&parenttab=Settings" class="btn btn-warning"
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
                        <h2 class="pull-left">Información general</h2>
                    </header>
                    <div class="main-box-body">
                        <div class="row">
                            {* how to use Name *}
                            <div class="form-group">
                                <label for="how_to_use_name" class="col-md-3 control-label">Nombre:</label>
                                <div id="hu-div-name" class="col-md-7">
                                    <input type="text" class="form-control" id="how_to_use_name" name="name"
                                           value="{$name}"
                                           title="El nombre del modo"
                                           placeholder="Nombre del modo de uso">
                                    <span id="hu-name" class="help-block"></span>
                                </div>
                            </div>
                            {* how to use description *}
                            <div class="form-group">
                                <label for="how_to_use_description" class="col-md-3 control-label">Descripción:</label>
                                <div id="hu-div-description" class="col-md-7">
                                    <textarea class="form-control" name="description" id="how_to_use_description"
                                              rows="3"
                                              placeholder="Breve descripción del modo de uso">{$description}</textarea>
                                    <span id="hu-description" class="help-block"></span>
                                </div>
                            </div>
                            {* how to use tabname *}
                            <div class="form-group">
                                <label for="how_to_use_module" class="col-md-3 control-label">Módulo:</label>
                                <div id="hu-div-formodule" class="col-md-7">
                                    <select class="form-control" name="formodule" id="formodule" title="El módulo"
                                            onchange="HowToUseUtils.selectedModule (this)">
                                        {foreach $AVAILABLE_MODULES as $module}
                                            <option value="{$module->getName()}"
                                                    {if $module->getName() eq $tabName}selected{/if} > {$module->getLabel()}</option>
                                        {/foreach}
                                    </select>
                                    <span id="hu-formodule" class="help-block"></span>
                                </div>
                            </div>
                            {* tab by default *}
                            <div class="form-group">
                                <label for="master_view" class="col-md-3 control-label">Pestaña por defecto:</label>
                                <div id="hu-div-mainmaster" class="col-md-7">
                                    <select class="form-control" name="mainmaster" id="master_view"
                                            title="La pestaña por defecto">
                                        {foreach $AVAILABLE_VIEW as $view}
                                            <option value="{$view->getViewName()}"
                                                    {if $view->getViewName() eq $viewName}selected{/if} > {$view->getName()}</option>
                                        {/foreach}
                                    </select>
                                    <span id="hu-mainmaster" class="help-block"></span>
                                </div>
                            </div>
                            {* isDefault *}
                            <div class="form-group">
                                <label for="master_view" class="col-md-3 control-label"></label>
                                <div class="col-md-7">
                                    <div class="checkbox">
                                        <label>
                                            <input name="isdefault" type="checkbox"{if $isDefault}checked{/if} value="1">
                                            ¿Modo por defecto del módulo?
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {* status *}
                            <div class="form-group">
                                <label for="master_view" class="col-md-3 control-label">Estado:</label>
                                <div class="col-md-7">
                                    <select class="form-control" name="status" id="status_view">
                                        {foreach $AVAILABLE_STATUS as $key => $value}
                                            <option value="{$key}"
                                                    {if $key eq $status}selected{/if} > {$value}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <header class="main-box-header clearfix">
                            <h2 class="pull-left" style="margin-left: -20px">Vistas relacionadas</h2>
                        </header>
                        <div class="row-how-use justify-content-center" id="views-rows">
                            {* row titles *}
                            <div class="col-md-11" style="margin-top: 20px; margin-left: 20px">
                                <div class="row-how-use justify-content-center">
                                    <div class="col-md-3"><span
                                                style="text-align: center; font-size: small">Pestaña</span></div>
                                    <div class="col-md-4"><span
                                                style="text-align: center; font-size: small">Filtros</span></div>
                                    <div class="col-md-1"></div>
                                    <div class="col-md-3"><span style="text-align: center; font-size: small">Filtro por defecto</span>
                                    </div>
                                    <div class="col-md-1"><span
                                                style="text-align: center; font-size: small">Acción</span></div>
                                </div>
                            </div>
                            {* /row titles *}
                            {* row number 0 *}
                            <div class="col-md-11" style="margin-top: 5px;margin-left: 15px">
                                <div id="how-use-row-0" class="row-how-use justify-content-center how-use-row">
                                    <div id="hu-div-listviewtab-0" class="col-md-3">
                                        <select class="form-control" name="listviewtab[]" title="La pestaña por defecto"
                                                onchange="HowToUseUtils.selectedTab(this)">
                                            <option value="" selected>Seleccionar</option>
                                            {foreach $AVAILABLE_VIEW as $view}
                                                <option value="{$view->getViewName()}">
                                                    {$view->getName()}</option>
                                            {/foreach}
                                        </select>
                                        <span id="hu-listviewtab-0" class="help-block"></span>
                                    </div>
                                    <div id="hu-div-views-0" class="col-md-4">
                                        <select multiple class="form-control" name="views[0][]"
                                                title="Seleccionar al menos un filtro"></select>
                                        <span id="hu-view" class="help-block"></span>
                                    </div>
                                    <div class="col-md-1" style="text-align: center">
                                        <button type="button" class="btn btn-success btn-icon"
                                                onclick="HowToUseUtils.selectedViews(this)"
                                                title="Seleccionar vistas">
                                            <i class="fa fa-sign-out" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    <div id="hu-div-defaultvie-0" class="col-md-3">
                                        <select class="form-control" name="defaultview[]"
                                                title="Seleccionar al menos un filtro">
                                        </select>
                                        <span id="hu-defaultview" class="help-block"></span>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-icon hidden"
                                                onclick="HowToUseUtils.deleteRowView(this)"
                                                title="Eliminar">
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 20px">
                            <div class="col-md-12">
                                <div class="center-block" style="text-align: center">
                                    <button type="button" class="btn btn-success btn-icon"
                                            onclick="HowToUseUtils.addRowView(this)"
                                            title="Incluir vistas">
                                        <i class="fa fa-plus" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript" src="include/ckeditor/ckeditor.js"></script>
    <script type="text/javascript" src="modules/how_use/how-use-utils.js"></script>
    <script type="text/html" id="how-use-view-template">
        <div class="col-md-11" style="margin-top: 20px; margin-left: 15px">
            <div id="how-use-row-__ID__" class="row-how-use justify-content-center how-use-row">
                <div id="hu-div-listviewtab-__ID__" class="col-md-3">
                    <select class="form-control" name="listviewtab[]" onchange="HowToUseUtils.selectedTab(this)"
                            title="La pestaña por defecto">
                        <option value="" selected>Seleccionar</option>
                        {foreach $AVAILABLE_VIEW as $view}
                            <option value="{$view->getViewName()}">
                                {$view->getName()}</option>
                        {/foreach}
                    </select>
                    <span id="hu-listviewtab-__ID__" class="help-block"></span>
                </div>
                <div id="hu-div-views-__ID__" class="col-md-4">
                    <select multiple class="form-control" name="views[__ID__][]"
                            title="Seleccionar al menos un filtro"></select>
                    <span id="hu-views-__ID__" class="help-block"></span>
                </div>
                <div class="col-md-1" style="text-align: center">
                    <button type="button" class="btn btn-success btn-icon"
                            onclick="HowToUseUtils.selectedViews(this)"
                            title="Seleccionar vistas">
                        <i class="fa fa-sign-out" aria-hidden="true"></i>
                    </button>
                </div>
                <div id="hu-div-defaultview-__ID__" class="col-md-3">
                    <select class="form-control" name="defaultview[]" title="Seleccionar al menos un filtro">
                    </select>
                    <span id="hu-defaultview-__ID__" class="help-block"></span>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-icon"
                            onclick="HowToUseUtils.deleteRowView(this)"
                            title="Eliminar">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </div>
    </script>
    {if $VIEW_ROW neq NULL}
        <script type="text/javascript">
            {literal}
            HowToUseUtils.reload({/literal}{$VIEW_ROW|json_encode}{literal});
            {/literal}
        </script>

    {/if}
{/strip}
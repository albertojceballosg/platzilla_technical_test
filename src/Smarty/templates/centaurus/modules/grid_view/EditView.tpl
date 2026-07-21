{strip}
    {if $GRID_VIEW neq NULL}
        {assign var='gridViewName' value=$GRID_VIEW->getGridViewName ()}
        {assign var='gridViewId' value=$GRID_VIEW->getId ()}
        {assign var='gridViewLabel' value=$GRID_VIEW->getLabel ()}
        {assign var='moduleName' value=$GRID_VIEW->getTabName ()}
        {assign var='gridViewBoxes' value=$GRID_VIEW->getGridViewBox ()}
        {assign var='gridViewStatus' value=$GRID_VIEW->getStatus ()}
        {assign var='gridPosition' value=$GRID_VIEW->getPosition()}
        {assign var='totalBoxes' value=$GRID_VIEW->getGridViewBox ()|count}
        {math equation= "x - y" x= 6 y= $totalBoxes assign='diff'}
    {else}
        {assign var='gridViewName' value=null}
        {assign var='gridViewId' value=null}
        {assign var='gridViewLabel' value=null}
        {assign var='moduleName' value=null}
        {assign var='gridViewBoxes' value=null}
        {assign var='gridViewStatus' value=null}
        {assign var='gridPosition' value=null}
    {/if}
    <link rel="stylesheet" type="text/css" href="modules/grid_view/grid-view.css"/>
    <div class="row">
        <div class="col-xs-12">
            <h1>
                <a href="index.php?module=grid_view&action=ListView&parenttab=Settings">Vistas cuadricula</a>
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
    <div class="container-fluid base-list-container">
    {* cuadricula *}
    <form method="post" action="index.php" id="GridViewForm" name="GridView" onsubmit="return GridViewUtils.validateForm (this);">
    <input type="hidden" name="module" value="grid_view"/>
    <input type="hidden" name="action" value="SaveGridView"/>
    <input type="hidden" name="record" id="record" value="{$gridViewId}"/>
    <input type="hidden" name="gridviewname" id="gridviewname" value="{$gridViewName}"/>
    <div class="row">
    <div class="col-xs-12 col-md-12" style="margin-top: 12px">
    <header class="main-box-header clearfix">
        <h2 class="pull-left">Información general</h2>
        <div class="action-bar pull-right">
            <button type="submit" class="btn btn-info">Guardar</button>&nbsp;
            <a href="index.php?module=grid_view&action=ListView&parenttab=Settings"
               class="btn btn-warning">Cancelar</a>
        </div>
    </header>
    </div>
    </div>
    <div class="row-grid-view justify-content-center">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4">
                    <div class="label-input">
                        <label for="label">Nombre <span class="required">*</span></label>
                    </div>
                </div>
                <div class="form-group col-md-6 field-container">
                    <div id="gv-div-label" class="input-group" style="width: 100%;">
                        <input type="text" title="El nombre de la vista" id="label"{if $gridViewName eq 'DEFAULT_VIEW'} disabled {/if}name="label" value="{$gridViewLabel}" maxlength="50"
                               class="form-control"/>
                        <span id="gv-label" class="help-block"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-4">
                    <div class="label-input">
                        <label for="appname">Módulo <span class="required">*</span></label>
                    </div>
                </div>
                <div id="gv-div-modulename" class="form-group col-md-6 field-container">
                    <select id="modulename" title="El módulo" name="modulename" class="form-control">
                        <option value="{if $gridViewName eq 'DEFAULT_VIEW'}Home{else}""{/if}">{if $gridViewName eq 'DEFAULT_VIEW'}Portada{else}Seleccione ...{/if}</option>
                        {foreach $AVAILABLE_MODULES as $module}
                            <option value="{$module->getName()}"{if $moduleName eq $module->getName()} selected="selected" {/if} {if $gridViewName eq 'DEFAULT_VIEW'} disabled {/if} >{$module->getLabel()}</option>
                        {/foreach}
                    </select>
                    <span id="gv-modulename" class="help-block"></span>
                </div>
            </div>
        </div>
    </div>
        <div class="row-grid-view justify-content-center">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-4">
                        <div class="label-input">
                            <label for="appname">Estado<span class="required">*</span></label>
                        </div>
                    </div>
                    <div id="gv-div-viewstatus" class="form-group col-md-6 field-container">
                        <select id="viewstatus" title="El estado" name="viewstatus" class="form-control">
                            <option value="ENABLED"{if $gridViewStatus eq 'ENABLED'} selected="selected" {/if}>Activa</option>
                            <option value="DISABLED"{if $gridViewStatus eq 'DISABLED'} selected="selected" {/if}{if $gridViewName eq 'DEFAULT_VIEW'} disabled {/if}>Inactiva</option>
                        </select>
                        <span id="gv-viewstatus" class="help-block"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-4">
                        <div class="label-input">
                            <label for="viewsposition">Posición<span class="required">*</span></label>
                        </div>
                    </div>
                <div id="gv-div-viewsposition" class="form-group col-md-6 field-container">
                    <select id="viewsposition" title="La posición" name="position" class="form-control">
                        {foreach $AVAILABLE_POSITION as $key => $position}
                            <option value="{$key}"{if $gridPosition eq $key} selected="selected" {/if}>{$position}</option>
                        {/foreach}
                    </select>
                    <span id="gv-viewsposition" class="help-block"></span>
                </div>
                </div>
            </div>

        </div>
        <div class="row border-top">
            <div class="col-xs-12 col-md-12" style="margin-top: 12px">
                <header class="main-box-header clearfix">
                    <h2 class="pull-left">Información de cuadricula</h2>
                </header>
            </div>
        </div>
    <div class="row-grid-view justify-content-center">
        <div class="col-xs-4 col-md-3">
            <div class="row-grid-view">
                <div id="draggble-boxes"  class="list-group">
                    <h4 style="text-align: center">Cuadriculas</h4>
                    {foreach $AVAILABLE_BOXES as $boxView}
                        <a    href="#" rel="{$boxView->getName()}" draggable="true"
                           class="list-group-item col-xs-4 col-md-3 border border-info box-element">
                            {$boxView->getLabel()}
                        </a>
                    {/foreach}
                </div>
            </div>

        </div>
        <div class="col-xs-12 col-md-8">
            <h4 style="text-align: center">Vista cuadriculas</h4>
            <span style="text-align: center; color: red"   id="gv-girdviewbox" class="help-block"></span>
            {if $gridViewBoxes eq NULL}
            <div id="draggble-grid" class="row-grid-view justify-content-center">
                <div class="col-xs-4 col-md-3 border border-info grid-element"></div>
                <div class="col-xs-4 col-md-3 border border-info grid-element"></div>
                <div class="col-xs-4 col-md-3 border  border-info grid-element"></div>
                <div class="col-xs-4 col-md-3 border  border-info grid-element"></div>
                <div class="col-xs-4 col-md-3 border  border-info grid-element"></div>
                <div class="col-xs-4 col-md-3 border  border-info grid-element"></div>
            </div>
            {else}
                <div id="draggble-grid" class="row-grid-view justify-content-center">
                    {foreach $gridViewBoxes as $box}
                    <div class="col-xs-4 col-md-3 border border-info grid-element">
                        <div style="margin-top: 4px">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <br>{$box->getBoxContenet()->getLabel ()}
                            <input type="hidden" name="gridbox[]"  value="{$box->getBoxType ()}">
                        </div>
                    </div>
                    {/foreach}
                        {section name=viewBoxes start=0 loop=$diff max=6}
                            <div class="col-xs-4 col-md-3 border  border-info grid-element"></div>
                        {/section}

                </div>
            {/if}
        </div>
    </div>
    </form>
    </div>
    {* cuadricula *}
    <script type="text/javascript" src="themes/centaurus/js/modernizr.js"></script>
    <script type="text/javascript" src="modules/grid_view/grid-view.js"></script>
    <script type="text/html" id="simple-template">
        <div>
        <div style="margin-top: 4px">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <br>
            __BOX_NAME__
            <input type="hidden" name="gridbox[]"  value="__VALUE__">
        </div>
        </div>
    </script>
{/strip}